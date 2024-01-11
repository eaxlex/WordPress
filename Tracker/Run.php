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

class Run {

	private static $init = null;

	public function __construct() {
		add_action( 'activate_' . MKTR_WP_BASE, array( $this, 'Install' ) );
		add_action( 'deactivate_' . MKTR_WP_BASE, array( $this, 'unInstall' ) );

		if ( is_admin() ) {
			Admin::load();
		} else {
			Session::getUid();
			Front::load();
		}
	}

	public static function init() {
		if ( self::$init == null ) {
			self::$init = new self();
		}
		return self::$init;
	}

	public function Install() {
		Session::up();
	}

	public function unInstall() {
		Session::down();
	}
}
