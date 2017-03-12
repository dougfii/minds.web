<?php
/**
 * Created by IntelliJ IDEA.
 * User: momo
 * Date: 2017/2/25
 * Time: 下午8:14
 */

namespace minds\config;


abstract class Driver
{
    /**
     * 解析配置文件或内容
     * @param $config 配置文件路径或内容
     * @return mixed
     */
    abstract public function parse($config);
}