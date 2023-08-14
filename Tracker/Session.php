<?php
/** @noinspection SpellCheckingInspection */
/**
 * @copyright   Copyright (c) 2023 TheMarketer.com
 * @project     TheMarketer.com
 * @website     https://themarketer.com/
 * @author      Alexandru Buzica (EAX LEX S.R.L.) <b.alex@eax.ro>
 * @license     https://opensource.org/licenses/osl-3.0.php - Open Software License (OSL 3.0)
 * @docs        https://themarketer.com/resources/api
 */

namespace MktrWp\Tracker;

class Session {
    private static $init = null;
    private static $uid = null;

    private $data = array();
    private $isDirty = false;

    public static function init()
    {
        if (self::$init == null) {
            self::$init = new self();
        }
        return self::$init;
    }

    public function remove($key)
    {
        if($this->data[$key]) {
            unset($this->data[$key]);
        }
        return $this;
    }


    public static function getUid() {
        if (self::$uid === null) {
            if (!isset($_COOKIE['__sm__uid'])) {
                self::$uid = uniqid();
                setcookie('__sm__uid', self::$uid, strtotime('+365 days'), COOKIEPATH, COOKIE_DOMAIN, is_ssl(), true);
            } else {
                self::$uid = sanitize_text_field($_COOKIE['__sm__uid']);
            }
        }

        return self::$uid;
    }

    public function __construct()
    {
        $uid = self::getUid();
        $table_name = Config::tableName();
        $prep = Config::db()->prepare( "SELECT data FROM $table_name WHERE uid = %s", $uid );
        $data = Config::db()->get_var( $prep );
        
        $this->data = $data ? unserialize($data) : [];
    }

    public static function set($key, $value = null) {
        if ($value === null) {
            self::init()->remove($key);
        } else {
            self::init()->data[$key] = $value;
        }

        self::init()->isDirty = true;
    }

    public static function get($key, $default = null) {
        if (isset(self::init()->data[$key])) {
            return self::init()->data[$key];
        } else {
            return $default;
        }
    }
    
    public static function save()
    {
        if (self::init()->isDirty) {
            $uid = self::getUid();
            $table_name = Config::tableName();
            if (!empty(self::init()->data)) {
                $data = array(
                    'data' => serialize(self::init()->data),
                    'expire' => date('Y-m-d H:i:s', strtotime('+2 day'))
                );
                
                $prep = Config::db()->prepare("SELECT COUNT(*) FROM $table_name WHERE uid = %s", $uid);
                $row_count = Config::db()->get_var($prep);
    
                if ($row_count > 0) {
                    Config::db()->update($table_name, $data, array('uid' => $uid));
                } else {
                    $data['uid'] = $uid;
                    Config::db()->insert($table_name, $data);
                }
            } else {
                $prep = Config::db()->prepare("DELETE FROM $table_name WHERE uid = %s", $uid);
                Config::db()->query($prep);
            }

            self::clearIfExipire();

            self::init()->isDirty = false;
            return true;
        }
        return false;
    }

    public static function clearIfExipire()
    {
        $table_name = Config::tableName();
        $expire_at = date('Y-m-d H:i:s', time());
        $prep = Config::db()->prepare("DELETE FROM $table_name WHERE expire_at < %s", $expire_at);
        Config::db()->query($prep);
    }

    public static function clear()
    {
        self::init()->data = array();
        self::init()->isDirty = true;
    }

    public function __destruct()
    {
        if ($this->isDirty) {
            $this->save();
        }
    }

    public static function up()
    {
        $table_name = Config::tableName();

        if (Config::db()->get_var("SHOW TABLES LIKE '$table_name'") != $table_name) {
            $charset_collate = Config::db()->get_charset_collate();

            $sql = "CREATE TABLE $table_name (
                uid varchar(50) NOT NULL,
                data longtext, 
                expire datetime,
                PRIMARY KEY  (uid)
              ) $charset_collate;";

            require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );
            dbDelta( $sql );
        }
    }

    public static function down()
    {
        $table_name = Config::tableName();
        
        Config::db()->query("DROP TABLE IF EXISTS $table_name;");
    }
}
