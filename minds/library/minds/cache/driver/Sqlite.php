<?php
/**
 * Created by IntelliJ IDEA.
 * User: momo
 * Date: 2017/2/26
 * Time: 下午10:00
 */

namespace minds\cache\driver;

use minds\cache\Driver;

class Sqlite extends Driver
{
    protected $config = [
        'db' => ':memory:',
        'table' => 'sharedmemory',
        'prefix' => '',
        'expire' => 0,
        'persistent' => false,
    ];

    /**
     * 构造函数
     * @param array $config
     */
    public function __construct($config = [])
    {
        if (!extension_loaded('sqlite')) {
            throw new \BadFunctionCallException('not support: sqlite');
        }
        if (!empty($config)) {
            $this->config = array_merge($this->config, $config);
        }
        $func = $this->config['persistent'] ? 'sqlite_popen' : 'sqlite_open';
        $this->handler = $func($this->config['db']);
    }

    /**
     * 缓存是否存在
     * @param string $name 缓存变量名
     * @return bool
     */
    public function has($name)
    {
        $name = $this->getCacheKey($name);
        $sql = 'SELECT value FROM ' . $this->config['table'] . ' WHERE var=\'' . $name . '\' AND (expire=0 OR expire >' . $_SERVER['REQUEST_TIME'] . ') LIMIT 1';
        $result = sqlite_query($this->handler, $sql);
        return sqlite_num_rows($result);
    }

    /**
     * 读取缓存
     * @param string $name 缓存变量名
     * @param mixed $default 默认值
     * @return mixed
     */
    public function get($name, $default = false)
    {
        $name = $this->getCacheKey($name);
        $sql = 'SELECT value FROM ' . $this->config['table'] . ' WHERE var=\'' . $name . '\' AND (expire=0 OR expire >' . $_SERVER['REQUEST_TIME'] . ') LIMIT 1';
        $result = sqlite_query($this->handler, $sql);
        if (sqlite_num_rows($result)) {
            $content = sqlite_fetch_single($result);
            if (function_exists('gzcompress')) {
                $content = gzuncompress($content); // 启用数据压缩
            }
            return unserialize($content);
        }
        return $default;
    }

    /**
     * 写入缓存
     * @param string $name 缓存变量名
     * @param mixed $value 缓存数据
     * @param int $expire 过期时间 秒 0为永久
     * @return bool
     */
    public function set($name, $value, $expire = null)
    {
        $name = $this->getCacheKey($name);
        $value = sqlite_escape_string(serialize($value));
        if (is_null($expire)) {
            $expire = $this->config['expire'];
        }
        $expire = (0 == $expire) ? 0 : ($_SERVER['REQUEST_TIME'] + $expire); // 缓存有效期为0表示永久缓存
        if (function_exists('gzcompress')) {
            $value = gzcompress($value, 3); // 数据压缩
        }
        if ($this->tag) {
            $tag = $this->tag;
            $this->tag = null;
        } else {
            $tag = '';
        }
        $sql = 'REPLACE INTO ' . $this->config['table'] . ' (var, value, expire, tag) VALUES (\'' . $name . '\', \'' . $value . '\', \'' . $expire . '\', \'' . $tag . '\')';
        return sqlite_query($this->handler, $sql) ? true : false;
    }

    /**
     * 删除缓存
     * @param string $name 缓存变量名
     * @return bool
     */
    public function del($name)
    {
        $name = $this->getCacheKey($name);
        $sql = 'DELETE FROM ' . $this->config['table'] . ' WHERE var=\'' . $name . '\'';
        sqlite_query($this->handler, $sql);
        return true;
    }

    /**
     * 自增缓存（数值缓存）
     * @param string $name 缓存变量名
     * @param int $step 步长
     * @return false|int
     */
    public function inc($name, $step = 1)
    {
        $value = $this->has($name) ? $this->get($name) + $step : $step;
        return $this->set($name, $value, 0) ? $value : false;
    }

    /**
     * 自减缓存（数值缓存）
     * @param string $name 缓存变量名
     * @param int $step 步长
     * @return false|int
     */
    public function dec($name, $step = 1)
    {
        $value = $this->has($name) ? $this->get($name) - $step : $step;
        return $this->set($name, $value, 0) ? $value : false;
    }

    /**
     * 清除缓存
     * @param string $tag 标签名
     * @return boolean
     */
    public function clear($tag = null)
    {
        if ($tag) {
            $name = sqlite_escape_string($tag);
            $sql = 'DELETE FROM ' . $this->config['table'] . ' WHERE tag=\'' . $name . '\'';
            sqlite_query($this->handler, $sql);
            return true;
        }
        $sql = 'DELETE FROM ' . $this->config['table'];
        sqlite_query($this->handler, $sql);
        return true;
    }

    /**
     * 获取实际的缓存标识
     * @param string $name 缓存变量名
     * @return string
     */
    protected function getCacheKey($name)
    {
        return $this->config['prefix'] . sqlite_escape_string($name);
    }
}