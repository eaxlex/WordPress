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

return array(
	'root'     => array(
		'name'           => 'wpmktr/tracker',
		'pretty_version' => '1',
		'version'        => '1.0.0',
		'reference'      => null,
		'type'           => 'wordpress-module',
		'install_path'   => __DIR__ . '/../../',
		'aliases'        => array(),
		'dev'            => true,
	),
	'versions' => array(
		'wpmktr/tracker' => array(
			'pretty_version'  => '1',
			'version'         => '1.0.0',
			'reference'       => null,
			'type'            => 'wordpress-module',
			'install_path'    => __DIR__ . '/../../',
			'aliases'         => array(),
			'dev_requirement' => false,
		),
	),
);
