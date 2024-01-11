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

namespace MktrWp\Tracker\Routes;

use MktrWp\Tracker\Session;

class clearEvents {

	private static $init = null;

	public static function init() {
		if ( self::$init == null ) {
			self::$init = new self();
		}
		return self::$init;
	}

	public static function execute() {
		$eventData = Session::get( 'ClearMktr' );

		if ( ! empty( $eventData ) ) {
			foreach ( $eventData as $key => $value ) {
				$eventData1 = Session::get( $key );
				foreach ( $value as $value1 ) {
					unset( $eventData1[ $value1 ] ); }
				Session::set( $key, $eventData1 );
			}

			Session::set( 'ClearMktr', array() );
		}

		$r = 'console.log(2);';

		return '';
	}
}
