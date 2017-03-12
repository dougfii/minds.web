<?php
/**
 * Created by IntelliJ IDEA.
 * User: momo
 * Date: 2017/2/23
 * Time: 下午11:25
 */

namespace minds\utils;


class IO
{
    /**
     * 删除文件
     * @param string $filename 文件名称（全路径）
     * @return bool
     */
    public static function unlink($filename)
    {
        return is_file($filename) && unlink($filename);
    }

    /**
     * 创建目录
     * @param string $path 路径
     * @return bool
     */
    public static function mkdir($path)
    {
        return !is_dir($path) && mkdir($path, 0755, true);
    }
}