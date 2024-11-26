<?php
/**
 * @copyright   Copyright (c) 2023 TheMarketer.com
 * @project     TheMarketer.com
 * @website     https://themarketer.com/
 * @author      Alexandru Buzica (EAX LEX S.R.L.) <b.alex@eax.ro>
 * @license     https://opensource.org/licenses/osl-3.0.php - Open Software License (OSL 3.0)
 * @docs        https://themarketer.com/resources/api
 */

namespace MktrWp\Tracker;

class Events {

	private static $init   = null;
	private static $shName = null;
	private static $data   = array();

	private static $assets = array();

	public const actions = array(
		'is_home'   => '__sm__view_homepage',
		'is_search' => '__sm__search',
	);

	public const observerGetEvents = array(
		'setEmail' => array( true, '__sm__set_email' )
	);

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
	/**
	 * @var array
	 */
	private static $bMultiCat;

	public static function init() {
		if ( self::$init == null ) {
			self::$init = new self();
		}
		return self::$init;
	}

	public static function build() {
		foreach ( self::$assets as $key => $val ) {
			self::$data[ $key ] = $val;
		}
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

		self::$shName = self::eventsName[ $Name ];

		self::$data = array(
			'event' => $Name,
		);

		self::$assets = array();

		switch ( self::$shName ) {
			case 'Search':
				self::$assets['search_term'] = get_search_query( true );
				break;
			default:
				self::$assets = $eventData;
		}

		self::$assets = self::schemaValidate( self::$assets, self::eventsSchema[ self::$shName ] );

		self::build();

		return self::init();
	}

	public static function toJson() {
		return json_encode( ( self::$data === null ? array() : self::$data ), JSON_UNESCAPED_SLASHES );
	}
}
