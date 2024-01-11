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

use MktrWp\Tracker\Routes\clearEvents;
use MktrWp\Tracker\Routes\loadEvents;
use MktrWp\Tracker\Routes\setEmail;

class Route {

	private static $init = null;

	private static $allMethods = null;

	public static function init() {
		if ( self::$init == null ) {
			self::$init = new self();
		}
		return self::$init;
	}

	public static function checkPage( $p ) {
		if ( self::$allMethods == null ) {
			foreach ( get_class_methods( self::init() ) as $value ) {
				self::$allMethods[ strtolower( $value ) ] = $value;
			}
		}

		$p = strtolower( $p );

		if ( isset( self::$allMethods[ $p ] ) ) {
			$page = self::$allMethods[ $p ];

			$run = self::$page();

			echo esc_js( $run->execute() );

			header( 'Content-type: application/javascript; charset=utf-8' );
			header( 'HTTP/1.1 200 OK' );
			http_response_code( 201 );
			header( 'Status: 200 All rosy' );
			Session::save();
			exit();
		}
	}

	/* Pages */
	/** @noinspection PhpUnused */
	public static function loadEvents() {
		return loadEvents::init();
	}

	/** @noinspection PhpUnused */
	public static function clearEvents() {
		return clearEvents::init();
	}

	public static function setEmail() {
		return setEmail::init();
	}
}
