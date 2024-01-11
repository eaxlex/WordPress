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

class Api {

	private static $init = null;

	public static function init() {
		if ( self::$init == null ) {
			self::$init = new self();
		}
		return self::$init;
	}

	private static $mURL = 'https://t.themarketer.com/api/v1/';
	// private static $mURL = "https://eaxdev.ga/mktr/EventsTrap/";
	private static $bURL = 'https://eaxdev.ga/mktr/BugTrap/';

	private static $timeOut = null;

	private static $cURL = null;

	private static $params  = null;
	private static $lastUrl = null;

	private static $info        = null;
	private static $exec        = null;
	private static $requestType = null;
	/** @noinspection PhpUnused */
	public static function send( $name, $data = array(), $post = true ) {
		return self::REST( self::$mURL . $name, $data, $post );
	}

	/** @noinspection PhpUnused */
	public static function debug( $data = array(), $post = true ) {
		return self::REST( self::$bURL, $data, $post );
	}

	/** @noinspection PhpUnused */
	public static function getParam() {
		return self::$params;
	}

	/** @noinspection PhpUnused */
	public static function getUrl() {
		return self::$lastUrl;
	}

	/** @noinspection PhpUnused */
	public static function getStatus() {
		return wp_remote_retrieve_response_code( self::$exec );
	}

	/** @noinspection PhpUnused */
	public static function getInfo() {
		return wp_remote_retrieve_headers( self::$exec );
	}

	/** @noinspection PhpUnused */
	public static function getContent() {
		return wp_remote_retrieve_body( self::$exec );
	}

	public static function getBody() {
		return wp_remote_retrieve_body( self::$exec );
	}

	public static function REST( $url, $data = array(), $post = true ) {
		try {
			if ( empty( Config::getRestKey() ) ) {
				return false;
			}

			if ( self::$timeOut == null ) {
				self::$timeOut = 1;
			}

			self::$params = array_merge(
				array(
					'k' => Config::getRestKey(),
					'u' => Config::getCustomerId(),
				),
				$data
			);

			self::$requestType = $post;

			if ( self::$requestType ) {

				self::$lastUrl = $url;
				self::$exec    = wp_remote_post(
					self::$lastUrl,
					array(
						'method'  => 'POST',
						'timeout' => self::$timeOut,
						'body'    => self::$params,
					)
				);
			} else {
				self::$lastUrl = $url; // .'?'. http_build_query(self::$params);
				self::$exec    = wp_remote_get(
					self::$lastUrl,
					array(
						'method'  => 'GET',
						'timeout' => self::$timeOut,
						'body'    => self::$params,
					)
				);
			}
		} catch ( \Exception $e ) {
		}
		return self::init();
	}
}
