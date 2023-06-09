<?php
/**
 * Plugin Name:             TheMarketer WP
 * Plugin URI:              https://themarketer.com/integrations/wordpress
 * Description:             TheMarketer - WordPress Version
 * Version:                 1.0.0
 * Author:                  themarketer.com
 * Author URI:              https://themarketer.com
 * Text Domain:             wp-mktr
 * License:                 GPL2
 * License URI:             https://www.gnu.org/licenses/gpl-2.0.html
 *
 * @package wp-mktr
 */


defined('ABSPATH') OR exit('No direct script access allowed');

if (!defined('WP_MKTR'))
{
    define('WP_MKTR', __FILE__);
}

if (!defined('WP_MKTR_DIR'))
{
    define('WP_MKTR_DIR', dirname(__FILE__));
}


if (!defined('WP_MKTR_BASE'))
{
    define('WP_MKTR_BASE', plugin_basename(WP_MKTR));
}

require_once WP_MKTR_DIR . '/vendor/autoload.php';

use WpMktr\Tracker\Run;

/** @noinspection PhpFullyQualifiedNameUsageInspection */
Run::init();
