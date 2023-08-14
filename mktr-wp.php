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


defined('ABSPATH') OR exit('No direct script access allowed');

if (!defined('MKTR_WP'))
{
    define('MKTR_WP', __FILE__);
}

if (!defined('MKTR_WP_DIR'))
{
    define('MKTR_WP_DIR', dirname(__FILE__));
}


if (!defined('MKTR_WP_BASE'))
{
    define('MKTR_WP_BASE', plugin_basename(MKTR_WP));
}

require_once MKTR_WP_DIR . '/vendor/autoload.php';

/** @noinspection PhpFullyQualifiedNameUsageInspection */
\MktrWp\Tracker\Run::init();
