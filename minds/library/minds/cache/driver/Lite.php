<?php
/**
 * Created by IntelliJ IDEA.
 * User: momo
 * Date: 2017/2/26
 * Time: 上午11:01
 */

namespace minds\cache\driver;

use minds\cache\Driver;
use minds\utils\IO;

class Lite extends Driver
{
    protected $config = [
        'prefix' => '',
        'path' => '',
        'expire' => 0, // 等于 10*365*24*3600（10年）
    ];

    /**
     * 构造函数
     * @param array $config
     */
    public function __construct($config = [])
    {
        if (!empty($config)) {
            $this->config = array_merge($this->config, $config);
        }
        if (substr($this->config['path'], -1) != DS) {
            $this->config['path'] .= DS;
        }
    }

    /**
     * 缓存是否存在
     * @param string $name 缓存变量名
     * @return bool
     */
    public function has($name)
    {
        return $this->get($name) ? true : false;
    }

    /**
     * 读取缓存
     * @param string $name 缓存变量名
     * @param mixed $default 默认值
     * @return mixed
     */
    public function get($name, $default = false)
    {
        $filename = $this->getCacheKey($name);
        if (is_file($filename)) {
            // 判断是否过期
            $mtime = filemtime($filename);
            if ($mtime < $_SERVER['REQUEST_TIME']) {
                // 清除已经过期的文件
                IO::unlink($filename);
                return $default;
            }
            return include $filename;
        } else {
            return $default;
        }
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
        if (is_null($expire)) {
            $expire = $this->config['expire'];
        }
        // 模拟永久
        if (0 === $expire) {
            $expire = 10 * 365 * 24 * 3600;
        }
        $filename = $this->getCacheKey($name);
        if ($this->tag && !is_file($filename)) {
            $first = true;
        }
        $ret = file_put_contents($filename, ("<?php return " . var_export($value, true) . ";"));
        // 通过设置修改时间实现有效期
        if ($ret) {
            isset($first) && $this->setTagItem($filename);
            touch($filename, $_SERVER['REQUEST_TIME'] + $expire);
        }
        return $ret;
    }

    /**
     * 删除缓存
     * @param string $name 缓存变量名
     * @return bool
     */
    public function del($name)
    {
        return IO::unlink($this->getCacheKey($name));
    }

    /**
     * 自增缓存（数值缓存）
     * @param string $name 缓存变量名
     * @param int $step 步长
     * @return false|int
     */
    public function inc($name, $step = 1)
    {
        if ($this->has($name)) {
            $value = $this->get($name) + $step;
        } else {
            $value = $step;
        }
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
        if ($this->has($name)) {
            $value = $this->get($name) - $step;
        } else {
            $value = $step;
        }
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
            // 指定标签清除
            $keys = $this->getTagItem($tag);
            foreach ($keys as $key) {
                unlink($key);
            }
            $this->del('tag_' . md5($tag));
            return true;
        }
        array_map('IO::unlink', glob($this->config['path'] . ($this->config['prefix'] ? $this->config['prefix'] . DS : '') . '*' . EXT));
    }

    /**
     * 获取实际的缓存标识
     * @param string $name 缓存变量名
     * @return string
     */
    protected function getCacheKey($name)
    {
        return $this->config['path'] . $this->config['prefix'] . md5($name) . EXT;
    }
}