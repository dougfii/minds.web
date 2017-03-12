<?php
/**
 * Created by IntelliJ IDEA.
 * User: momo
 * Date: 2017/2/26
 * Time: 上午1:01
 */

namespace minds\config\driver;

use minds\config\Driver;

class Ini extends Driver
{
    /**
     * 解析配置文件或内容
     * @param string $config 配置文件路径或内容
     * @return mixed
     */
    public function parse($config)
    {
        return is_file($config) ? parse_ini_file($config, true) : parse_ini_string($config, true);
    }
}