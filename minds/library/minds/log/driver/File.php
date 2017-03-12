<?php
/**
 * Created by IntelliJ IDEA.
 * User: momo
 * Date: 2017/2/20
 * Time: 下午11:01
 */

namespace minds\log\driver;


class File
{
    /**
     * 日志写入接口
     * @param array $logs 需要写入的日志信息
     * @param bool $separator 是否写入分隔符
     * @return bool
     */
    public function save($logs = [], $separator = true)
    {
        return true;
    }
}