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

use MktrWp\Tracker\Front;
use MktrWp\Tracker\Session;

class loadEvents {

	private static $init = null;

	public static function init() {
		if ( self::$init == null ) {
			self::$init = new self();
		}
		return self::$init;
	}

	public static function execute() {
		$lines = array();
		foreach ( Front::observerGetEvents as $event => $Name ) {
			if ( ! $Name[0] ) {
				$eventData = Session::get( $event );
				if ( ! empty( $eventData ) ) {
					foreach ( $eventData as $value ) {
						$lines[] = 'dataLayer.push(' . Front::getEvent( $Name[1], $value ) . ');';
					}
				}
				Session::set( $event, array() );
			}
		}

		// $lines[] = "console.log(1);";
		// $lines[] = json_encode($eventData1);
		return implode( PHP_EOL, $lines );
	}
}
