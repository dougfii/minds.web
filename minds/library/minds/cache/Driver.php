<?php

/**
 * Created by IntelliJ IDEA.
 * User: momo
 * Date: 2017/2/23
 * Time: 上午1:34
 */

namespace minds\cache;

abstract class Driver
{
    protected $handler = null;
    protected $config = [];
    protected $tag;

    /**
     * 缓存是否存在
     * @param string $name 缓存变量名
     * @return bool
     */
    abstract public function has($name);

    /**
     * 读取缓存
     * @param string $name 缓存变量名
     * @param mixed $default 默认值
     * @return mixed
     */
    abstract public function get($name, $default = false);

    /**
     * 写入缓存
     * @param string $name 缓存变量名
     * @param mixed $value 缓存数据
     * @param int $expire 过期时间 秒 0为永久
     * @return bool
     */
    abstract public function set($name, $value, $expire = null);

    /**
     * 删除缓存
     * @param string $name 缓存变量名
     * @return bool
     */
    abstract public function del($name);

    /**
     * 自增缓存（数值缓存）
     * @param string $name 缓存变量名
     * @param int $step 步长
     * @return false|int
     */
    abstract public function inc($name, $step = 1);

    /**
     * 自减缓存（数值缓存）
     * @param string $name 缓存变量名
     * @param int $step 步长
     * @return false|int
     */
    abstract public function dec($name, $step = 1);

    /**
     * 清除缓存
     * @param string $tag 标签名
     * @return boolean
     */
    abstract public function clear($tag = null);

    /**
     * 读取缓存并删除
     * @param string $name 缓存变量名
     * @return mixed
     */
    public function pop($name)
    {
        $result = $this->get($name, false);
        if ($result) {
            $this->del($name);
            return $result;
        } else {
            return;
        }
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
        if (!$this->has($name)) {
            if ($value instanceof \Closure) {
                $value = call_user_func($value);
            }
            return $this->set($name, $value, $expire);
        }
        return false;
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
        if ($this->has($name)) {
            if ($value instanceof \Closure) {
                $value = call_user_func($value);
            }
            return $this->set($name, $value, $expire);
        }
        return false;
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
        if ($this->has($name)) {
            $val = $this->get($name);
            if (is_string($value) && is_string($val)) {
                return $this->set($name, $value . $val, $expire);
            } elseif (is_array($value) && is_array($val)) {
                return $this->set($name, array_merge($value, $val), $expire);
            }
        } else {
            return $this->set($name, $value, $expire);
        }
        return false;
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
        if ($this->has($name)) {
            $val = $this->get($name);
            if (is_string($value) && is_string($val)) {
                return $this->set($name, $val . $value, $expire);
            } elseif (is_array($value) && is_array($val)) {
                return $this->set($name, array_merge($val, $value), $expire);
            }
        } else {
            return $this->set($name, $value, $expire);
        }
        return false;
    }

    /**
     * 缓存标签
     * @access public
     * @param string $name 标签名
     * @param string|array $keys 缓存标识
     * @param bool $overlay 是否覆盖
     * @return $this
     */
    public function tag($name, $keys = null, $overlay = false)
    {
        if (is_null($keys)) {
            $this->tag = $name;
        } else {
            $key = 'tag_' . md5($name);
            if (is_string($keys)) {
                $keys = explode(',', $keys);
            }
            $keys = array_map([$this, 'getCacheKey'], $keys);
            if ($overlay) {
                $value = $keys;
            } else {
                $value = array_unique(array_merge($this->getTagItem($name), $keys));
            }
            $this->set($key, implode(',', $value));
        }
        return $this;
    }

    /**
     * 更新标签
     * @param string $name 缓存标识
     * @return void
     */
    protected function setTagItem($name)
    {
        if ($this->tag) {
            $key = 'tag_' . md5($this->tag);
            $this->tag = null;
            if ($this->has($key)) {
                $value = $this->get($key);
                $value .= ',' . $name;
            } else {
                $value = $name;
            }
            $this->set($key, $value);
        }
    }

    /**
     * 获取标签包含的缓存标识
     * @param string $tag 缓存标签
     * @return array
     */
    protected function getTagItem($tag)
    {
        $key = 'tag_' . md5($tag);
        $value = $this->get($key);
        if ($value) {
            return explode(',', $value);
        }
        return [];
    }

    /**
     * 获取实际的缓存标识
     * @param string $name 缓存变量名
     * @return string
     */
    protected function getCacheKey($name)
    {
        return $this->config['prefix'] . $name;
    }

    /**
     * 返回句柄对象，可执行其它高级方法
     * @return object
     */
    public function handler()
    {
        return $this->handler;
    }
}