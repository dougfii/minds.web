<?php
/**
 * Created by IntelliJ IDEA.
 * User: momo
 * Date: 2017/2/19
 * Time: 下午11:34
 */

namespace minds;


class Config
{
    private static $config = []; // 配置参数
    private static $domain = '_sys_'; // 参数作用域

    /**
     * 设定配置参数的作用域
     * @param string $domain 作用域
     */
    public static function domain($domain)
    {
        self::$domain = $domain;
        if (!isset(self::$config[$domain])) {
            self::$config[$domain] = [];
        }
    }

    /**
     * 解析配置文件或内容
     * @param string $config 配置文件路径或内容
     * @param string $driver 配置解析类型
     * @param string $name 配置名（如设置即表示二级配置）
     * @param string $domain 作用域
     * @return mixed
     */
    public static function parse($config, $driver = '', $name = '', $domain = '')
    {
        $domain = $domain ?: self::$domain;
        if (empty($driver)) {
            $driver = pathinfo($config, PATHINFO_EXTENSION);
        }
        $class = false !== strpos($driver, '\\') ? $driver : '\\minds\\config\\driver\\' . ucwords($driver);
        return self::set((new $class())->parse($config), $name, $domain);
    }

    /**
     * 加载配置文件（PHP格式）
     * @param string $file 配置文件名
     * @param string $name 配置名（如设置即表示二级配置）
     * @param string $domain 作用域
     * @return mixed
     */
    public static function load($file, $name = '', $domain = '')
    {
        $domain = $domain ?: self::$domain;
        if (!isset(self::$config[$domain])) {
            self::$config[$domain] = [];
        }
        if (is_file($file)) {
            $name = strtolower($name);
            $type = pathinfo($file, PATHINFO_EXTENSION);
            if ('php' == $type) {
                return self::set(include $file, $name, $domain);
            } elseif ('yaml' == $type && function_exists('yaml_parse_file')) {
                return self::set(yaml_parse_file($file), $name, $domain);
            } else {
                return self::parse($file, $type, $name, $domain);
            }
        } else {
            return self::$config[$domain];
        }
    }

    /**
     * 检测配置是否存在
     * @param string $name 配置参数名（支持二级配置 .号分割）
     * @param string $domain 作用域
     * @return bool
     */
    public static function has($name, $domain = '')
    {
        $domain = $domain ?: self::$domain;

        if (!strpos($name, '.')) {
            return isset(self::$config[$domain][strtolower($name)]);
        } else {
            // 二维数组设置和获取支持
            $name = explode('.', $name);
            return isset(self::$config[$domain][strtolower($name[0])][$name[1]]);
        }
    }

    /**
     * 获取配置参数 为空则获取所有配置
     * @param string $name 配置参数名（支持二级配置 .号分割）
     * @param string $domain 作用域
     * @return mixed
     */
    public static function get($name = null, $domain = '')
    {
        $domain = $domain ?: self::$domain;
        // 无参数时获取所有
        if (empty($name) && isset(self::$config[$domain])) {
            return self::$config[$domain];
        }

        if (!strpos($name, '.')) {
            $name = strtolower($name);
            return isset(self::$config[$domain][$name]) ? self::$config[$domain][$name] : null;
        } else {
            // 二维数组设置和获取支持
            $name = explode('.', $name);
            $name[0] = strtolower($name[0]);
            return isset(self::$config[$domain][$name[0]][$name[1]]) ? self::$config[$domain][$name[0]][$name[1]] : null;
        }
    }

    /**
     * 设置配置参数 name为数组则为批量设置
     * @param string|array $name 配置参数名（支持二级配置 .号分割）
     * @param mixed $value 配置值
     * @param string $domain 作用域
     * @return mixed
     */
    public static function set($name, $value = null, $domain = '')
    {
        $domain = $domain ?: self::$domain;
        if (!isset(self::$config[$domain])) {
            self::$config[$domain] = [];
        }
        if (is_string($name)) {
            if (!strpos($name, '.')) {
                self::$config[$domain][strtolower($name)] = $value;
            } else {
                // 二维数组设置和获取支持
                $name = explode('.', $name);
                self::$config[$domain][strtolower($name[0])][$name[1]] = $value;
            }
            return;
        } elseif (is_array($name)) {
            // 批量设置
            if (!empty($value)) {
                self::$config[$domain][$value] = isset(self::$config[$domain][$value]) ?
                    array_merge(self::$config[$domain][$value], $name) :
                    self::$config[$domain][$value] = $name;
                return self::$config[$domain][$value];
            } else {
                return self::$config[$domain] = array_merge(self::$config[$domain], array_change_key_case($name));
            }
        } else {
            // 为空直接返回 已有配置
            return self::$config[$domain];
        }
    }

    /**
     * 重置配置参数
     * @param string $domain
     */
    public static function reset($domain = '')
    {
        $domain = $domain ?: self::$domain;
        (true === $domain) ? self::$config = [] : self::$config[$domain] = [];
    }
}