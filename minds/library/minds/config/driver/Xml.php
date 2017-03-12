<?php
/**
 * Created by IntelliJ IDEA.
 * User: momo
 * Date: 2017/2/26
 * Time: 上午1:16
 */

namespace minds\config\driver;

use minds\config\Driver;

class Xml extends Driver
{
    /**
     * 解析配置文件或内容
     * @param string $config 配置文件路径或内容
     * @return mixed
     */
    public function parse($config)
    {
        $result = (array)(is_file($config) ? simplexml_load_file($config) : simplexml_load_string($config));
        foreach ($result as $key => $val) {
            if (is_object($val)) {
                $result[$key] = (array)$val;
            }
        }
        return $result;
    }
}