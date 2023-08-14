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

use MktrWp\Tracker\Api;
use MktrWp\Tracker\Session;

class setEmail
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
        if (!function_exists( 'is_plugin_active' ) ) {
            require_once ABSPATH . 'wp-admin/includes/plugin.php';
        }

        $em = Session::get('setEmail');

        $allGood = true;
        $plug = 'mailpoet/mailpoet.php';
        $active = is_plugin_active($plug);

        if ($active) {
            foreach ($em as $val) {
                $info = array(
                    "email" => $val['email_address']
                );

                $status = \MailPoet\Models\Subscriber::findOne($val['email_address'])->status;

                if ($status === \MailPoet\Models\Subscriber::STATUS_SUBSCRIBED)
                {
                    $name = array();

                    if (!empty($val['firstname']))
                    {
                        $name[] = $val['firstname'];
                    }

                    if (!empty($val['lastname']))
                    {
                        $name[] = $val['lastname'];
                    }

                    if (empty($name))
                    {
                        $info["name"] = explode("@", $val['email_address'])[0];
                    } else {
                        $info["name"] = implode(" ", $name);
                    }

                    $user = get_user_by('email', $val['email_address']);
                    $phone = get_user_meta($user->ID, 'billing_phone', true);

                    if (!empty($phone)) {
                        $info["phone"] = $phone;
                    }

                    Api::send("add_subscriber", $info);
                } else {
                    Api::send("remove_subscriber", $info);
                }

                if (Api::getStatus() != 200) {
                    $allGood = false;
                }
            }
        }


        if ($allGood)
        {
            Session::set('setPhone', array());
            Session::set('setEmail', array());
        }

        return 'console.log('.(int)$allGood.');';
    }
}