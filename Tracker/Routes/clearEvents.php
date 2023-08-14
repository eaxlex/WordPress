<?php
/**
 * @copyright   Copyright (c) 2023 TheMarketer.com
 * @project     TheMarketer.com
 * @website     https://themarketer.com/
 * @author      Alexandru Buzica (EAX LEX S.R.L.) <b.alex@eax.ro>
 * @license     https://opensource.org/licenses/osl-3.0.php - Open Software License (OSL 3.0)
 * @docs        https://themarketer.com/resources/api
 */

namespace MktrWp\Tracker\Routes;

use MktrWp\Tracker\Session;

class clearEvents
{
    private static $init = null;

    public static function init()
    {
        if (self::$init == null) {
            self::$init = new self();
        }
        return self::$init;
    }

    public static function execute()
    {
        $eventData = Session::get("ClearMktr");

        if (!empty($eventData)) {
            foreach ($eventData as $key => $value) {
                $eventData1 = Session::get($key);
                foreach ($value as $value1) { unset($eventData1[$value1]); }
                Session::set($key, $eventData1);
            }

            Session::set("ClearMktr", array());
        }

		$r = "console.log(2);";
        
        return "";
    }
}
