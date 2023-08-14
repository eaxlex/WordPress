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

class Admin
{
    private static $init = null;

    private static $notice = array();

    public static function init()
    {
        if (self::$init == null) {
            self::$init = new self();
        }
        return self::$init;
    }

    public static function load()
    {
        add_filter('plugin_action_links_'.Config::getPluginBase(), array(self::init(), 'action_links'));
        add_filter('plugin_row_meta', array(self::init(), 'extra_links'), 10, 2);
        add_action('admin_menu', array(self::init(), 'menu'));
        add_action('admin_notices', array(self::init(), 'notice'));
        add_action('mailpoet_segment_subscribed', array(self::init(), 'mailpoet_subscription'), 10, 2);
        add_action('mailpoet_subscription_before_subscribe', array(self::init(), 'mailpoet_bf_subscription'), 10, 3);
    }


    public function mailpoet_subscription($subscriber) {
        $email = $subscriber->getSubscriber()->email;
        
        if (!empty($email)) {
            Front::emailAndPhone($email);
            Session::save();
        }

    }
    public function mailpoet_bf_subscription($subscriber, $segmentIds, $form) {
        $email = $subscriber['email'] ?? $subscriber->email;

        if (!empty($email)) {
            Front::emailAndPhone($email);
        }
    }

    public static function addNotice($notice)
    {
        self::$notice[] = is_array($notice) ? $notice : array('message' => $notice);
        return self::init();
    }

    public static function notice()
    {
        if (!empty(self::$notice)) {
            $out = array();
            foreach (self::$notice as $value) {
                $out[] = '<div class="notice notice-'.
                    (isset($value['type']) ? $value['type'] : 'success').
                    ' is-dismissible"><p>'.esc_html($value['message']).'</p></div>';
            }

            echo esc_html(implode(PHP_EOL, $out));
        }
    }

    public static function action_links($links)
    {
        return array_merge(
            array(
                'settings' => '<a href="'. admin_url('admin.php?page=mktr_wp_tracker') . '" target="_blank"> Settings</a>'
            ),
            $links
        );
    }

    public static function extra_links($links, $file)
    {
        if (Config::getPluginBase() !== $file) {
            return $links;
        }

        foreach ($links as $k=>$v) {
            $links[$k] = str_replace('">', '" target="_blank">', $v);
        }

        return $links;
    }

    public static function menu()
    {
        add_menu_page(
            'TheMarketer',
            'TheMarketer',
            'manage_options',
            'mktr_wp_tracker',
            array(self::init(), 'tracker'),
            Config::getSVG(),
            2
        );
    }

    public static function tracker()
    {
        Form::initProcess();
        Form::formFields(
            array(
                'tit-set' => array(
                    'title'     => '<img style="height:20px;padding:0px;vertical-align: middle;" src="'.Config::getSVG().'" alt="TheMarketer"> Main Settings',
                    'type'      => 'title',
                ),
                'status' => array(
                    'title'     => 'Status',
                    'type'      => 'select'
                ),
                /* Account Settings */
                'tracking_key' => array(
                    'title'     => 'Tracking API Key *',
                    'type'      => 'text',
                    'holder'    => 'Your Tracking API Key.'
                ),
                'rest_key' => array(
                    'title'     => 'REST API Key *',
                    'type'      => 'text',
                    'holder'    => 'Your REST API Key.'
                ),
                'customer_id' => array(
                    'title'     => 'Customer ID *',
                    'type'      => 'text',
                    'holder'    => 'Your Customer ID.'
                ),
                'push_status' => array(
                    'title'     => 'Push Notification',
                    'type'      => 'select'
                ),
                /* Attribute Settings */
                'tit-set2' => array(
                    'title'     => '<img style="height:20px;padding:0px;vertical-align: middle;" src="'.Config::getSVG().'" alt="TheMarketer"> Google Settings',
                    'type'      => 'title',
                ),
                'google_status' => array(
                    'title'     => 'Status',
                    'type'      => 'select'
                ),
                'google_tagCode' => array(
                    'title'     => 'Tag CODE *',
                    'type'      => 'text',
                    'holder'    => 'Tag CODE'
                ),
            )
        );

        echo ent2ncr(Form::getForm());
    }
}
