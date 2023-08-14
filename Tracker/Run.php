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

class Run
{
    private static $init = null;

    public function __construct()
    {
	    add_action( 'activate_' . MKTR_WP_BASE, [$this, 'Install']);
	    add_action( 'deactivate_' . MKTR_WP_BASE, [$this, 'unInstall']);

        if (is_admin()) {
            Admin::load();
        } else {
            Session::getUid();
            Front::load();
        }
    }

    public static function init()
    {
        if (self::$init == null) {
            self::$init = new self();
        }
        return self::$init;
    }

    public function Install()
    {
        Session::up();
    }
    
    public function unInstall()
    {
        Session::down();
    }
}