<?php
/**
 * Created by IntelliJ IDEA.
 * User: momo
 * Date: 2017/2/24
 * Time: 上午1:19
 */

namespace minds\cache\driver;

use minds\cache\Driver;
use minds\utils\IO;

class File extends Driver
{
    protected $config = [
        'expire' => 0,
        'subdir' => true,
        'prefix' => '',
        'path' => CACHE_PATH,
        'compress' => false,
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

        IO::mkdir($this->config['path']); // 创建缓存目录
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
        if (!is_file($filename)) {
            return $default;
        }

        $content = file_get_contents($filename);
        if (false !== $content) {
            $expire = (int)substr($content, 8, 12);
            if (0 != $expire && $_SERVER['REQUEST_TIME'] > filemtime($filename) + $expire) {
                IO::unlink($filename); // 缓存过期删除缓存文件
                return $default;
            }
            $content = substr($content, 20, -3);
            if ($this->config['compress'] && function_exists('gzcompress')) {

                $content = gzuncompress($content); // 启用数据压缩
            }
            $content = unserialize($content);
            return $content;
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
        $filename = $this->getCacheKey($name);
        if ($this->tag && !is_file($filename)) {
            $first = true;
        }
        $data = serialize($value);
        if ($this->config['compress'] && function_exists('gzcompress')) {
            $data = gzcompress($data, 3); // 数据压缩
        }
        $data = "<?php\n//" . sprintf('%012d', $expire) . $data . "\n?>";
        $result = file_put_contents($filename, $data);
        if ($result) {
            isset($first) && $this->setTagItem($filename);
            clearstatcache();
            return true;
        } else {
            return false;
        }
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
                IO::unlink($key);
            }
            $this->del('tag_' . md5($tag));
            return true;
        }

        $files = (array)glob($this->config['path'] . ($this->config['prefix'] ? $this->config['prefix'] . DS : '') . '*');
        foreach ($files as $path) {
            if (is_dir($path)) {
                array_map('IO::unlink', glob($path . '/*.php'));
            } else {
                IO::unlink($path);
            }
        }
        return true;
    }
}