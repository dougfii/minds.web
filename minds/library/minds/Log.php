<?php
/**
 * Created by IntelliJ IDEA.
 * User: momo
 * Date: 2017/2/20
 * Time: 上午1:16
 */

namespace minds;

use minds\exception\ClassNotFoundException;

class Log
{
    const ALL = '';
    const LOG = 'log';
    const ERROR = 'error';
    const INFO = 'info';
    const SQL = 'sql';
    const NOTICE = 'notice';
    const ALERT = 'alert';
    const DEBUG = 'debug';

    const TYPE = ['log', 'error', 'info', 'sql', 'notice', 'alert', 'debug']; // 日志类型

    protected static $logs = []; // 日志信息
    protected static $config = []; // 配置参数
    protected static $driver;

    public static function init(array $config = [])
    {
        $driver = isset($config['driver']) ? $config['driver'] : 'File';
        $class = false !== strpos($driver, '\\') ? $driver : '\\minds\\log\\driver\\' . ucwords($driver);
        self::$config = $config;
        if (class_exists($class)) {
            self::$driver = new $class($config);
        } else {
            throw new ClassNotFoundException('class not exists:' . $class, $class);
        }

        App::$debug && Log::log('[ LOG ] INIT ' . $driver, Log::INFO);
    }

    /**
     * 记录调试信息
     * @param mixed $msg 调试信息
     * @param string $type 日志类型
     */
    public static function log($msg, $type = Log::LOG)
    {
        self::$logs[$type][] = $msg;
        if (IS_CLI && count(self::$logs[$type]) > 100) {
            self::save();
        }
    }

    /**
     * 获取日志信息
     * @param string $type 信息类型
     * @return array
     */
    public static function get($type = Log::ALL)
    {
        return $type ? self::$logs[$type] : self::$logs;
    }

    public static function save()
    {
    }

    public static function __callStatic($name, $arguments)
    {
        if (in_array($name, self::TYPE)) {
            array_push($arguments, $name);
            return call_user_func_array('\\minds\\Log::log', $arguments);
        }
    }
}