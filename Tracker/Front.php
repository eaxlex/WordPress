<?php
/**
 * Plugin Name:             TheMarketer WP
 * Plugin URI:              https://themarketer.com/integrations/wordpress
 * Description:             TheMarketer - WordPress Version
 * Version:                 1.0.0
 * Author:                  themarketer.com
 * Author URI:              https://themarketer.com
 * Text Domain:             mktr-wp
 * License:                 GPL2
 * License URI:             https://www.gnu.org/licenses/gpl-2.0.html
 *
 * @package mktr-wp
 */

namespace MktrWp\Tracker;

class Front {

	private static $init = null;

	public static $Page = false;

	public static $RemoveCartEvent = true;
	public static $saveOrderEvent  = true;

	private static $eventName = null;
	private static $eventData = array();

	public const eventsName = array(
		'__sm__view_homepage' => 'HomePage',
		'__sm__search'        => 'Search',
		'__sm__set_email'     => 'setEmail'
	);

	public const eventsSchema = array(
		'HomePage' => null,
		'Search'   => array(
			'search_term' => 'search_term',
		),
		'setEmail' => array(
			'email_address' => 'email_address',
			'firstname'     => 'firstname',
			'lastname'      => 'lastname',
			'phone' => 'phone'
		),
	);

	public const observerGetEvents = array(
		'setEmail' => array( true, '__sm__set_email' )
	);

	public static function init() {
		if ( self::$init == null ) {
			self::$init = new self();
		}
		return self::$init;
	}

	public static function load() {
		if ( Config::getStatus() === 1 && ! empty( Config::getKey() ) ) {
			add_action( 'template_redirect', array( self::init(), 'routeCheck' ) );
			add_action( 'wp_login', array( self::init(), 'registerOrLogIn' ), 10, 2 );
			add_action( 'user_register', array( self::init(), 'registerOrLogIn' ), 10, 2 );

			if ( (int) Config::getValue( 'google_status' ) == 1 ) {
				add_action( 'wp_head', array( self::init(), 'google_head' ) );
				add_action( 'wp_footer', array( self::init(), 'google_body' ) );
			}
			add_action( 'wp_head', array( self::init(), 'loader' ) );
			add_action( 'wp_footer', array( self::init(), 'loadEvents' ) );

			add_action( 'mailpoet_segment_subscribed', array( self::init(), 'mailpoet_subscription' ), 10, 2 );
		}
	}


	public function mailpoet_subscription( $subscriber ) {
		$email = $subscriber->getSubscriber()->email;

		if ( ! empty( $email ) ) {
			self::emailAndPhone( $email );
		}
	}

	public function registerOrLogIn( $user_login, $user = null ) {
		$email = is_array( $user ) ? sanitize_email( $user['user_email'] ) : $user->user_email;
		self::emailAndPhone( $email );
	}

	public static function emailAndPhone( $email ) {
		$send = self::getEmail( $email );

		self::$eventName = 'setEmail';
		self::$eventData = $send;

		self::SessionSet();
	}


	private static function SessionSet( $key = null ) {
		$add = Session::get( self::$eventName );

		if ( $key === null ) {
			$n = '';

			for ( $i = 0; $i < 5; ++$i ) {
				$n .= random_int( 0, 9 );
			}

			$add[ time() . $n ] = self::$eventData;
		} else {
			$add[ $key ] = self::$eventData;
		}

		Session::set( self::$eventName, $add );
	}

	public static function getEmail( $email = null, $user = null ) {
		if ( $user == null ) {
			$user = get_user_by( 'email', $email );
		}
		if ( $user != null ) {
			$send = array(
				'email_address' => $user->user_email,
			);

			if ( ! empty( $user->first_name ) ) {
				$send['firstname'] = $user->first_name;
			}

			if ( ! empty( $user->last_name ) ) {
				$send['lastname'] = $user->last_name;
			}
		} else {
			$send = array(
				'email_address' => $email,
			);
		}

		return $send;
	}

	public function routeCheck() {
		self::$Page = get_query_var( Config::$name, false );

		if ( self::$Page === false ) {
			$p    = array();
			$path = parse_url( sanitize_text_field( $_SERVER['REQUEST_URI'] ), PHP_URL_PATH );
			preg_match( '/([^\/]+)\/([^\/]+)\/([^\/]+)/i', $path, $p );

			$ch = array(
				Config::$name => false,
				'api'         => false,
			);

			unset( $p[0] );
			foreach ( $p as $v ) {
				if ( ! empty( $v ) ) {
					if ( $ch[ Config::$name ] && $ch['api'] ) {
						self::$Page = $v;
					} elseif ( $v === Config::$name ) {
						$ch[ Config::$name ] = true;
					} elseif ( $v === 'api' ) {
						$ch['api'] = true;
					}
				}
			}
		}

		if ( self::$Page !== false ) {
			Route::checkPage( self::$Page );
		}
	}


	public static function google_head() {
		$status = Config::getValue( 'google_status' );
		$key    = Config::getValue( 'google_tagCode' );

		if ( ! empty( $status ) && ! empty( $key ) ) {
			echo "<!-- Google Tag Manager -->
<script>(function(w,d,s,l,i){w[l]=w[l]||[];w[l].push({'gtm.start':
            new Date().getTime(),event:'gtm.js'});var f=d.getElementsByTagName(s)[0],
        j=d.createElement(s),dl=l!='dataLayer'?'&l='+l:'';j.async=true;j.src=
            'https://www.googletagmanager.com/gtm.js?id='+i+dl;f.parentNode.insertBefore(j,f);
    })(window,document,'script','dataLayer','" . esc_js( $key ) . "');</script>
<!-- End Google Tag Manager -->";
		}
	}

	public static function google_body() {
		$status = Config::getValue( 'google_status' );
		$key    = Config::getValue( 'google_tagCode' );
		if ( ! empty( $status ) && ! empty( $key ) ) {
			echo '<!-- Google Tag Manager (noscript) -->
        <noscript><iframe src="https://www.googletagmanager.com/ns.html?id=' . esc_js( $key ) . '" height="0" width="0" style="display:none;visibility:hidden"></iframe></noscript>
        <!-- End Google Tag Manager (noscript) -->';
		}
	}

	public static function loader() {
		$lines = array();

		$key = Config::getKey();

		$lines[] = vsprintf( Config::loader, array( $key ) );

		$lines[] = 'window.MktrDebug = function () { if (typeof dataLayer != undefined) { for (let i of dataLayer) { console.log("Mktr","Google",i); } } };';
		$lines[] = '';
		// $wh =  array(Config::space, implode(Config::space, $lines));
		// $rep = array("%space%","%implode%");
		/** @noinspection BadExpressionStatementJS */
		/** @noinspection JSUnresolvedVariable */
		// echo ent2ncr(str_replace($rep, $wh, '<!-- Mktr Script Start -->%space%<script type="text/javascript">%space%%implode%%space%</script>%space%<!-- Mktr Script END -->'));
		wp_register_script( 'mktr_loader', '' );
		wp_enqueue_script( 'mktr_loader' );
		wp_add_inline_script( 'mktr_loader', implode( Config::space, $lines ) );
	}

	public static function schemaValidate( $array, $schema ) {
		$newOut = array();

		foreach ( $array as $key => $val ) {
			if ( isset( $schema[ $key ] ) ) {
				if ( is_array( $val ) ) {
					$newOut[ $schema[ $key ]['@key'] ] = self::schemaValidate( $val, $schema[ $key ]['@schema'] );
				} else {
					$newOut[ $schema[ $key ] ] = $val;
				}
			} elseif ( is_array( $val ) ) {
				$newOut[] = self::schemaValidate( $val, $schema );
			}
		}

		return $newOut;
	}

	public static function getEvent( $Name, $eventData = array() ) {
		if ( empty( self::eventsName[ $Name ] ) ) {
			return false;
		}

		$shName = self::eventsName[ $Name ];

		$data = array(
			'event' => $Name,
		);

		$assets = array();

		switch ( $shName ) {
			case 'Search':
				$assets['search_term'] = get_search_query( true );
				break;
			default:
				$assets = $eventData;
		}

		$assets = self::schemaValidate( $assets, self::eventsSchema[ $shName ] );

		foreach ( $assets as $key => $val ) {
			$data[ $key ] = $val;
		}

		return json_encode( ( $data === null ? array() : $data ), JSON_UNESCAPED_SLASHES );
	}
	public function loadEvents() {

		$loadJS = $lines = array();
		$action = array(
			'is_home'   => '__sm__view_homepage',
			'is_search' => '__sm__search',
		);

		foreach ( $action as $key => $value ) {
			if ( $key() || $key === 'is_home' && is_front_page() ) {
				$lines[] = 'dataLayer.push(' . self::getEvent( $value ) . ');';
				break;
			}
		}

		$clear = Session::get( 'ClearMktr' );

		if ( $clear === null ) {
			$clear = array();
		}

		foreach ( self::observerGetEvents as $event => $Name ) {
			$eventData = Session::get( $event );
			if ( ! empty( $eventData ) ) {
				foreach ( $eventData as $key => $value ) {
					$lines[] = 'dataLayer.push(' . self::getEvent( $Name[1], $value ) . ');';
					if ( ! $Name[0] ) {
						$clear[ $event ][ $key ] = $key;
					}
				}

				if ( $Name[0] ) {
					$loadJS[ $event ] = true;
				}
			}
		}

		$baseURL = Config::getBaseURL();

		foreach ( $loadJS as $k => $v ) {
			$lines[] = '(function(){ let add = document.createElement("script"); add.async = true; add.src = "' . esc_js( $baseURL ) . 'mktr/api/' . esc_js( $k ) . '/"; let s = document.getElementsByTagName("script")[0]; s.parentNode.insertBefore(add,s); })();';
		}

		if ( ! empty( $clear ) ) {
			Session::set( 'ClearMktr', $clear );
			$lines[] = '(function(){ let add = document.createElement("script"); add.async = true; add.src = "' . esc_js( $baseURL ) . 'mktr/api/clearEvents/"; let s = document.getElementsByTagName("script")[0]; s.parentNode.insertBefore(add,s); })();';
		}

		$lines[] = 'setTimeout(window.MktrDebug, 1000);';

		// $wh =  array(Config::space, implode(Config::space, $lines));
		// echo ent2ncr(str_replace($rep, $wh, '<!-- Mktr Script Start -->%space%<script type="text/javascript">%space%%implode%%space%</script>%space%<!-- Mktr Script END -->'));
		// $rep = array("%space%","%implode%");
		/** @noinspection BadExpressionStatementJS */
		/** @noinspection JSUnresolvedVariable */
		wp_register_script( 'mktr_loadEvents', '' );
		wp_enqueue_script( 'mktr_loadEvents' );
		wp_add_inline_script( 'mktr_loadEvents', implode( Config::space, $lines ) );
	}
}
