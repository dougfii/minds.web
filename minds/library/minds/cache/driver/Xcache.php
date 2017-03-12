<?php
/**
 * Created by IntelliJ IDEA.
 * User: momo
 * Date: 2017/2/26
 * Time: 下午10:13
 */

namespace minds\cache\driver;

use minds\cache\Driver;

class Xcache extends Driver
{
    protected $config = [
        'prefix' => '',
        'expire' => 0,
    ];

    /**
     * 构造函数
     * @param array $config
     */
    public function __construct($config = [])
    {
        if (!function_exists('xcache_info')) {
            throw new \BadFunctionCallException('not support: Xcache');
        }
        if (!empty($config)) {
            $this->config = array_merge($this->config, $config);
        }
    }

    /**
     * 缓存是否存在
     * @param string $name 缓存变量名
     * @return bool
     */
    public function has($name)
    {
        $key = $this->getCacheKey($name);
        return xcache_isset($key);
    }

    /**
     * 读取缓存
     * @param string $name 缓存变量名
     * @param mixed $default 默认值
     * @return mixed
     */
    public function get($name, $default = false)
    {
        $key = $this->getCacheKey($name);
        return xcache_isset($key) ? xcache_get($key) : $default;
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
        if ($this->tag && !$this->has($name)) {
            $first = true;
        }
        $key = $this->getCacheKey($name);
        if (xcache_set($key, $value, $expire)) {
            isset($first) && $this->setTagItem($key);
            return true;
        }
        return false;
    }

    /**
     * 删除缓存
     * @param string $name 缓存变量名
     * @return bool
     */
    public function del($name)
    {
        return xcache_unset($this->getCacheKey($name));
    }

    /**
     * 自增缓存（数值缓存）
     * @param string $name 缓存变量名
     * @param int $step 步长
     * @return false|int
     */
    public function inc($name, $step = 1)
    {
        $key = $this->getCacheKey($name);
        return xcache_inc($key, $step);
    }

    /**
     * 自减缓存（数值缓存）
     * @param string $name 缓存变量名
     * @param int $step 步长
     * @return false|int
     */
    public function dec($name, $step = 1)
    {
        $key = $this->getCacheKey($name);
        return xcache_dec($key, $step);
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
                xcache_unset($key);
            }
            $this->del('tag_' . md5($tag));
            return true;
        }
        return function_exists('xcache_unset_by_prefix') ? xcache_unset_by_prefix($this->config['prefix']) : false;
    }
}