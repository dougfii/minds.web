<?php
/**
 * Created by IntelliJ IDEA.
 * User: momo
 * Date: 2017/2/26
 * Time: 下午10:20
 */

namespace minds;

class Cookie
{
    protected static $config = [
        'prefix' => '', // cookie 名称前缀
        'expire' => 0, // cookie 保存时间
        'path' => '/', // cookie 保存路径
        'domain' => '', // cookie 有效域名
        'secure' => false, // cookie 启用安全传输
        'httponly' => '', // httponly设置
        'setcookie' => true, // 是否使用 setcookie
    ];

    protected static $init;

    /**
     * Cookie初始化
     * @param array $config
     */
    public static function init(array $config = [])
    {
        if (empty($config)) {
            $config = Config::get('cookie');
        }
        self::$config = array_merge(self::$config, array_change_key_case($config));
        if (!empty(self::$config['httponly'])) {
            ini_set('session.cookie_httponly', 1);
        }
        self::$init = true;
    }

    /**
     * 设置或者获取cookie作用域（前缀）
     * @param string $prefix
     * @return string|void
     */
    public static function prefix($prefix = '')
    {
        if (empty($prefix)) {
            return self::$config['prefix'];
        }
        self::$config['prefix'] = $prefix;
    }

    /**
     * 判断Cookie数据
     * @param string $name cookie名称
     * @param null $prefix cookie前缀
     * @return bool
     */
    public static function has($name, $prefix = null)
    {
        !isset(self::$init) && self::init();
        $prefix = !is_null($prefix) ? $prefix : self::$config['prefix'];
        $name = $prefix . $name;
        return isset($_COOKIE[$name]);
    }

    /**
     * Cookie获取
     * @param string $name cookie名称
     * @param null $prefix cookie前缀
     * @return mixed
     */
    public static function get($name, $prefix = null)
    {
        !isset(self::$init) && self::init();
        $prefix = !is_null($prefix) ? $prefix : self::$config['prefix'];
        $name = $prefix . $name;
        if (isset($_COOKIE[$name])) {
            $value = $_COOKIE[$name];
            if (0 === strpos($value, 'think:')) {
                $value = substr($value, 6);
                $value = json_decode($value, true);
                array_walk_recursive($value, 'self::jsonFormatProtect', 'decode');
            }
            return $value;
        } else {
            return;
        }
    }

    /**
     * Cookie 设置、获取、删除
     * @param string $name cookie名称
     * @param string $value cookie值
     * @param mixed $config 可选参数
     */
    public static function set($name, $value = '', $config = null)
    {
        !isset(self::$init) && self::init();
        // 参数设置(会覆盖黙认设置)
        if (!is_null($config)) {
            if (is_numeric($config)) {
                $config = ['expire' => $config];
            } elseif (is_string($config)) {
                parse_str($config, $config);
            }
            $conf = array_merge(self::$config, array_change_key_case($config));
        } else {
            $conf = self::$config;
        }
        $name = $conf['prefix'] . $name;
        // 设置cookie
        if (is_array($value)) {
            array_walk_recursive($value, 'self::jsonFormatProtect', 'encode');
            $value = 'think:' . json_encode($value);
        }
        $expire = !empty($conf['expire']) ? $_SERVER['REQUEST_TIME'] + intval($conf['expire']) : 0;
        if ($conf['setcookie']) {
            setcookie($name, $value, $expire, $conf['path'], $conf['domain'], $conf['secure'], $conf['httponly']);
        }
        $_COOKIE[$name] = $value;
    }

    /**
     * 永久保存Cookie数据
     * @param string $name cookie名称
     * @param string $value cookie值
     * @param null|integer|string $config 可选参数
     */
    public static function forever($name, $value = '', $config = null)
    {
        if (is_null($config) || is_numeric($config)) {
            $config = [];
        }
        $config['expire'] = 315360000;
        self::set($name, $value, $config);
    }

    /**
     * @param string $name cookie名称
     * @param string|null $prefix cookie前缀
     */
    public static function delete($name, $prefix = null)
    {
        !isset(self::$init) && self::init();
        $config = self::$config;
        $prefix = !is_null($prefix) ? $prefix : $config['prefix'];
        $name = $prefix . $name;
        if ($config['setcookie']) {
            setcookie($name, '', $_SERVER['REQUEST_TIME'] - 3600, $config['path'], $config['domain'], $config['secure'], $config['httponly']);
        }
        // 删除指定cookie
        unset($_COOKIE[$name]);
    }

    /**
     * Cookie清空
     * @param string|null $prefix cookie前缀
     */
    public static function clear($prefix = null)
    {
        // 清除指定前缀的所有cookie
        if (empty($_COOKIE)) {
            return;
        }
        !isset(self::$init) && self::init();
        // 要删除的cookie前缀，不指定则删除config设置的指定前缀
        $config = self::$config;
        $prefix = !is_null($prefix) ? $prefix : $config['prefix'];
        if ($prefix) {
            // 如果前缀为空字符串将不作处理直接返回
            foreach ($_COOKIE as $key => $val) {
                if (0 === strpos($key, $prefix)) {
                    if ($config['setcookie']) {
                        setcookie($key, '', $_SERVER['REQUEST_TIME'] - 3600, $config['path'], $config['domain'], $config['secure'], $config['httponly']);
                    }
                    unset($_COOKIE[$key]);
                }
            }
        }
        return;
    }

    private static function jsonFormatProtect(&$val, $key, $type = 'encode')
    {
        if (!empty($val) && true !== $val) {
            $val = 'decode' == $type ? urldecode($val) : urlencode($val);
        }
    }


}