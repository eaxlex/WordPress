<?php
/**
 * @copyright   Copyright (c) 2023 TheMarketer.com
 * @project     TheMarketer.com
 * @website     https://themarketer.com/
 * @author      Alexandru Buzica (EAX LEX S.R.L.) <b.alex@eax.ro>
 * @license     https://opensource.org/licenses/osl-3.0.php - Open Software License (OSL 3.0)
 * @docs        https://themarketer.com/resources/api
 */

namespace WpMktr\Tracker;

class Events
{
    private static $init = null;
    private static $shName = null;
    private static $data = array();

    private static $assets = array();

    public const actions = [
        "is_home" => "__sm__view_homepage",
        "is_search" => "__sm__search"
    ];

    public const observerGetEvents = [
        "setEmail"=> [true, "__sm__set_email"],
        "setPhone"=> [false, "__sm__set_phone"]
    ];

    public const eventsName = [
        "__sm__view_homepage" =>"HomePage",
        "__sm__search" => "Search",
        "__sm__set_email" => "setEmail",
        "__sm__set_phone" => "setPhone"
    ];

    public const eventsSchema = [
        "HomePage" => null,
        "Search" => [
            "search_term" => "search_term"
        ],

        "setPhone" => [
            "phone" => "phone"
        ],

        "setEmail" => [
            "email_address" => "email_address",
            "firstname" => "firstname",
            "lastname" => "lastname"
        ]
    ];
    /**
     * @var array
     */
    private static $bMultiCat;

    public static function init()
    {
        if (self::$init == null) {
            self::$init = new self();
        }
        return self::$init;
    }

    public static function loader()
    {
        $lines = array();

        $key = Config::getKey();

        $lines[] = vsprintf(Config::loader, array( $key ));

        $lines[] = 'window.MktrDebug = function () { if (typeof dataLayer != undefined) { for (let i of dataLayer) { console.log("Mktr","Google",i); } } };';
        $lines[] = '';
        $wh =  array(Config::space, implode(Config::space, $lines));
        $rep = array("%space%","%implode%");
        /** @noinspection BadExpressionStatementJS */
        /** @noinspection JSUnresolvedVariable */
        echo ent2ncr(str_replace($rep, $wh, '<!-- Mktr Script Start -->%space%<script type="text/javascript">%space%%implode%%space%</script>%space%<!-- Mktr Script END -->'));
    }

    public function loadEvents()
    {
        $loadJS = $lines = array();

        foreach (self::actions as $key=>$value) {
            if ($key() || $key === 'is_home' && is_front_page()) {
                $lines[] = "dataLayer.push(".self::getEvent($value)->toJson().");";
                break;
            }
        }

        $clear = WC()->session->get("ClearMktr");

        if ($clear === null) {
            $clear = array();
        }

        foreach (self::observerGetEvents as $event=>$Name) {
            $eventData = WC()->session->get($event);
            if (!empty($eventData)) {
                foreach ($eventData as $key=>$value) {
                    $lines[] = "dataLayer.push(".self::getEvent($Name[1], $value)->toJson().");";
                    if (!$Name[0]) {
                        $clear[$event][$key] = $key;
                    }
                }

                if ($Name[0]) {
                    //WC()->session->set($event, array());
                    $loadJS[$event] = true;
                } /** @noinspection PhpStatementHasEmptyBodyInspection */ else {
                    // $clear[$event][$key] = "clear";
                    // WC()->session->set($event, array());
                }
            }
        }

        $baseURL = Config::getBaseURL();

        foreach ($loadJS as $k=>$v) {
            $lines[] = '(function(){ let add = document.createElement("script"); add.async = true; add.src = "'.esc_js($baseURL).'mktr/api/'.esc_js($k).'/"; let s = document.getElementsByTagName("script")[0]; s.parentNode.insertBefore(add,s); })();';
        }

        if (!empty($clear)) {
            WC()->session->set("ClearMktr", $clear);

            $lines[] = '(function(){ let add = document.createElement("script"); add.async = true; add.src = "'.esc_js($baseURL).'mktr/api/clearEvents/"; let s = document.getElementsByTagName("script")[0]; s.parentNode.insertBefore(add,s); })();';
        }

        $lines[] = 'setTimeout(window.MktrDebug, 1000);';

        $wh =  array(Config::space, implode(Config::space, $lines));
        $rep = array("%space%","%implode%");
        /** @noinspection BadExpressionStatementJS */
        /** @noinspection JSUnresolvedVariable */
        echo ent2ncr(str_replace($rep, $wh, '<!-- Mktr Script Start -->%space%<script type="text/javascript">%space%%implode%%space%</script>%space%<!-- Mktr Script END -->'));
    }

    public static function build()
    {
        foreach (self::$assets as $key=>$val) {
            self::$data[$key] = $val;
        }
    }

    public static function schemaValidate($array, $schema)
    {
        $newOut = [];

        foreach ($array as $key=>$val) {
            if (isset($schema[$key])) {
                if (is_array($val)) {
                    $newOut[$schema[$key]["@key"]] = self::schemaValidate($val, $schema[$key]["@schema"]);
                } else {
                    $newOut[$schema[$key]] = $val;
                }
            } elseif (is_array($val)) {
                $newOut[] = self::schemaValidate($val, $schema);
            }
        }

        return $newOut;
    }

    public static function getEvent($Name, $eventData = [])
    {
        if (empty(self::eventsName[$Name])) {
            return false;
        }

        self::$shName = self::eventsName[$Name];

        self::$data = array(
            "event" => $Name
        );

        self::$assets = array();

        switch (self::$shName) {
            case "Search":
                self::$assets['search_term'] = get_search_query(true);
                break;
            default:
                self::$assets = $eventData;
        }

        self::$assets = self::schemaValidate(self::$assets, self::eventsSchema[self::$shName]);

        self::build();

        return self::init();
    }
    
    public static function toJson()
    {
        return json_encode((self::$data === null ? array() : self::$data), JSON_UNESCAPED_SLASHES);
    }
}
