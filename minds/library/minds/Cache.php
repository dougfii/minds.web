<?php
/**
 * Created by IntelliJ IDEA.
 * User: momo
 * Date: 2017/2/23
 * Time: 上午1:33
 */

namespace minds;

use minds\cache\Driver;
use minds\exception\ClassNotFoundException;

class Cache
{
    protected static $instance = []; // 缓存对象连接池
    protected static $handler; // 缓存对象句柄

    public static $readTimes = 0;
    public static $writeTimes = 0;

    /**
     * 连接缓存
     * @param array $config 配置
     * @param bool|string $name 缓存连接标识 true 强制重新连接
     * @return mixed
     */
    public static function connect(array $config = [], $name = false)
    {
        $driver = isset($config['driver']) ? $config['driver'] : 'File';
        if (false === $name) {
            $name = md5(serialize($name));
        }

        if (true === $name || !isset(self::$instance[$name])) {
            $class = false !== strpos($driver, '\\') ? $driver : '\\minds\\cache\\driver\\' . ucwords($driver);

            if (true === $name) {
                if (class_exists($class)) {
                    return new $class($config);
                } else {
                    throw new ClassNotFoundException('class not exists:' . $class, $class);
                }
            } else {
                self::$instance[$name] = new $class($config);
            }

            App::$debug && Log::log('[ LOG ] INIT ' . $driver, Log::INFO);
        }

        self::$handler = self::$instance[$name];

        return self::$handler;
    }

    /**
     * 自动初始化缓存
     * @param array $config 配置
     */
    public static function init(array $config = [])
    {
        if (!empty(self::$handler)) {
            if (!empty($config)) {
                self::connect($config);
            } elseif ('complex' == Config::get('cache.driver')) {
                self::connect(Config::get('cache.default'));
            } else {
                self::connect(Config::get('cache'));
            }
        }
    }

    /**
     * 切换缓存类型 需要配置 cache.type 为 complex
     * @param string $name 缓存标识
     * @return Driver
     */
    public static function store($name)
    {
        if ('complex' == Config::get('cache.driver')) {
            self::connect(Config::get('cache.' . $name), strtolower($name));
        }
        return self::$handler;
    }

    /**
     * 缓存是否存在
     * @param string $name 缓存变量名
     * @return bool
     */
    public static function has($name)
    {
        self::init();
        self::$readTimes++;
        return self::$handler->has($name);
    }

    /**
     * 读取缓存
     * @param string $name 缓存变量名
     * @param mixed $default 默认值
     * @return mixed
     */
    public static function get($name, $default = false)
    {
        self::init();
        self::$readTimes++;
        return self::$handler->get($name, $default);
    }

    /**
     * 写入缓存
     * @param string $name 缓存变量名
     * @param mixed $value 缓存数据
     * @param int $expire 过期时间 秒 0为永久
     * @return bool
     */
    public static function set($name, $value, $expire = null)
    {
        self::init();
        self::$writeTimes++;
        return self::$handler->set($name, $value, $expire);
    }

    /**
     * 删除缓存
     * @param string $name 缓存变量名
     * @return bool
     */
    public static function del($name)
    {
        self::init();
        self::$writeTimes++;
        return self::$handler->del($name);
    }


    /**
     * 自增缓存（数值缓存）
     * @param string $name 缓存变量名
     * @param int $step 步长
     * @return false|int
     */
    public static function inc($name, $step = 1)
    {
        self::init();
        self::$writeTimes++;
        return self::$handler->inc($name, $step);
    }

    /**
     * 自减缓存（数值缓存）
     * @param string $name 缓存变量名
     * @param int $step 步长
     * @return false|int
     */
    public static function dec($name, $step = 1)
    {
        self::init();
        self::$writeTimes++;
        return self::$handler->dec($name, $step);
    }

    /**
     * 清除缓存
     * @param string $tag 标签名
     * @return boolean
     */
    public static function clear($tag = null)
    {
        self::init();
        self::$writeTimes++;
        return self::$handler->clear($tag);
    }

    /**
     * 读取缓存并删除
     * @param string $name 缓存变量名
     * @return mixed
     */
    public function pop($name)
    {
        self::init();
        self::$readTimes++;
        self::$writeTimes++;
        return self::$handler->pop($name);
    }

    /**
     * 如果不存在则写入缓存
     * @param string $name 缓存变量名
     * @param mixed $value 缓存数据
     * @param int $expire 过期时间 秒 0为永久
     * @return bool
     */
    public function remember($name, $value, $expire = null)
    {
        self::init();
        self::$readTimes++;
        return self::$handler->remember($name, $value, $expire);
    }

    /**
     * 如果存在则更新缓存
     * @param string $name 缓存变量名
     * @param mixed $value 缓存数据
     * @param int $expire 过期时间 秒 0为永久
     * @return bool
     */
    public function replace($name, $value, $expire = null)
    {
        self::init();
        self::$readTimes++;
        return self::$handler->replace($name, $value, $expire);
    }

    /**
     * 尾部追加缓存数据（字符串或数组缓存）
     * @param string $name 缓存变量名
     * @param string|array $value 尾部追加的缓存数据
     * @param int $expire 过期时间 秒 0为永久
     * @return bool
     */
    public function append($name, $value, $expire = null)
    {
        self::init();
        self::$readTimes++;
        self::$writeTimes++;
        return self::$handler->append($name, $value, $expire);
    }

    /**
     * 首部追加缓存数据（字符串或数组缓存）
     * @param string $name 缓存变量名
     * @param string|array $value 首部追加的缓存数据
     * @param int $expire 过期时间 秒 0为永久
     * @return bool
     */
    public function prepend($name, $value, $expire = null)
    {
        self::init();
        self::$readTimes++;
        self::$writeTimes++;
        return self::$handler->prepend($name, $value, $expire);
    }

    /**
     * 缓存标签
     * @access public
     * @param string $name 标签名
     * @param string|array $keys 缓存标识
     * @param bool $overlay 是否覆盖
     * @return Driver
     */
    public function tag($name, $keys = null, $overlay = false)
    {
        self::init();
        return self::$handler->tag($name, $keys, $overlay);
    }
}