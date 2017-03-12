<?php
/**
 * Created by IntelliJ IDEA.
 * User: momo
 * Date: 2017/2/26
 * Time: 上午1:11
 */

namespace minds\config\driver;

use minds\config\Driver;

class Json extends Driver
{
    /**
     * 解析配置文件或内容
     * @param string $config 配置文件路径或内容
     * @return mixed
     */
    public function parse($config)
    {
        if (is_file($config)) {
            $config = file_get_contents($config);
        }
        return json_decode($config, true);
    }
}