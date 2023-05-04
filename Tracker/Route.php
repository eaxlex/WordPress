<?php
/**
 * @copyright   Copyright (c) 2023 TheMarketer.com
 * @project     TheMarketer.com
 * @website     https://themarketer.com/
 * @author      Alexandru Buzica (EAX LEX S.R.L.) <b.alex@eax.ro>
 * @license     http://opensource.org/licenses/osl-3.0.php - Open Software License (OSL 3.0)
 * @docs        https://themarketer.com/resources/api
 */

namespace WpMktr\Tracker;

use WpMktr\Tracker\Routes\clearEvents;
use WpMktr\Tracker\Routes\loadEvents;
use WpMktr\Tracker\Routes\setEmail;

class Route
{
    private static $init = null;

    private static $allMethods = null;

    public static function init() {
        if (self::$init == null) {
            self::$init = new self();
        }
        return self::$init;
    }

    public static function checkPage($p)
    {
        if (self::$allMethods == null)
        {
            foreach (get_class_methods(self::init()) as $value) {
                self::$allMethods[strtolower($value)] = $value;
            }
        }

        $p = strtolower($p);

        if(isset(self::$allMethods[$p]))
        {
            $page = self::$allMethods[$p];

            $run = self::$page();

            echo $run->execute();
            
            header("Content-type: application/javascript; charset=utf-8");
            header("HTTP/1.1 200 OK");
            http_response_code(201);
            header("Status: 200 All rosy");
            Session::save();
            exit();
        }
    }

    /* Pages */
    /** @noinspection PhpUnused */
    public static function loadEvents()
    {
        return loadEvents::init();
    }

    /** @noinspection PhpUnused */
    public static function clearEvents() {
        return clearEvents::init();
    }

    public static function setEmail()
    {
        return setEmail::init();
    }
}