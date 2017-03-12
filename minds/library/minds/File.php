<?php
/**
 * Created by IntelliJ IDEA.
 * User: momo
 * Date: 2017/2/21
 * Time: 13:15
 */

namespace minds;

use SplFileObject;

class File extends SplFileObject
{
    private $error = ''; // 错误信息
    protected $filename; // 当前完整文件名
    protected $saveName; // 上传文件名
    protected $rule = 'date'; // 文件上传命名规则
    protected $validate = []; // 文件上传验证规则
    protected $isTest; // 单元测试
    protected $info; // 上传文件信息
    protected $hash = []; // 文件hash信息

    /**
     * File constructor.
     * @param $filename
     */
    public function __construct($filename, $mode = 'r')
    {
        parent::__construct($filename, $mode);
        $this->filename = $this->getRealPath() ?: $this->getPathname();
    }

    /**
     * 获取上传文件信息
     * @param string $name
     * @return array|string
     */
    public function getInfo($name = '')
    {
        return isset($this->info[$name]) ? $this->info[$name] : $this->info;
    }

    /**
     * 设置上传文件信息
     * @param array $info 上传文件信息
     * @return $this
     */
    public function setInfo($info)
    {
        $this->info = $info;
        return $this;
    }

    /**
     * 获取上传文件的保存文件名称
     * @return string
     */
    public function getSaveName()
    {
        return $this->saveName;
    }

    /**
     * 设置上传文件的保存文件名
     * @param string $saveName
     * @return $this
     */
    public function setSaveName($saveName)
    {
        $this->saveName = $saveName;
        return $this;
    }

    /**
     * 设置上传文件的验证规则
     * @param array $validate 验证规则
     * @return $this
     */
    public function validate($validate = [])
    {
        $this->validate = $validate;
        return $this;
    }

    /**
     * 是否测试
     * @param bool $test 是否测试
     * @return $this
     */
    public function isTest($test = false)
    {
        $this->isTest = $test;
        return $this;
    }

    /**
     * 获取错误信息
     * @return string
     */
    public function getError()
    {
        return $this->error;
    }

    /**
     * 获取文件哈希值
     * @param string $type
     * @return string
     */
    public function hash($type = 'sha1')
    {
        if (!isset($this->hash[$type])) {
            $this->hash[$type] = hash_file($type, $this->filename);
        }
        return $this->hash[$type];
    }

    public function __call($name, $arguments)
    {
        return $this->hash($name);
    }
}