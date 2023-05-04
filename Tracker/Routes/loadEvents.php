<?php
/**
 * @copyright   Copyright (c) 2023 TheMarketer.com
 * @project     TheMarketer.com
 * @website     https://themarketer.com/
 * @author      Alexandru Buzica (EAX LEX S.R.L.) <b.alex@eax.ro>
 * @license     https://opensource.org/licenses/osl-3.0.php - Open Software License (OSL 3.0)
 * @docs        https://themarketer.com/resources/api
 */

namespace WpMktr\Tracker\Routes;

use WpMktr\Tracker\Front;
use WpMktr\Tracker\Session;

class loadEvents
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
        $lines = [];
        foreach (Front::observerGetEvents as $event=>$Name)
        {
            if (!$Name[0]) {
                $eventData = Session::get($event);
                if (!empty($eventData))
                {
                    foreach ($eventData as $value)
                    {
                        $lines[] = "dataLayer.push(".Front::getEvent($Name[1], $value).");";
                    }
                }
                Session::set($event, array());
            }
        }

        // $lines[] = "console.log(1);";
		// $lines[] = json_encode($eventData1);
        return implode(PHP_EOL, $lines);
    }
}