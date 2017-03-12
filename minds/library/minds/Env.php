<?php
/**
 * Created by IntelliJ IDEA.
 * User: momo
 * Date: 2017/2/20
 * Time: 上午12:09
 */

namespace minds;

class Env
{
    /**
     * 获取环境变量值
     * @param string $name 环境变量名（支持二级 .号分割）
     * @param string $default 默认值
     * @return mixed
     */
    public static function get($name, $default = null)
    {
        $result = getenv(ENV_PREFIX . strtoupper(str_replace('.', '_', $name)));
        return $result !== false ? $result : $default;
    }

    /**
     * 加载自定义环境变量文件
     * @param string $iniFile 文件名（完全路径 INI格式）
     */
    public static function load($iniFile)
    {
        if (is_file($iniFile)) {
            $envs = parse_ini_file($iniFile, true);
            foreach ($envs as $key => $val) {
                $name = ENV_PREFIX . strtoupper($key);
                if (is_array($val)) {
                    foreach ($val as $k => $v) {
                        $item = $name . '_' . strtoupper($k);
                        putenv("{$item}=>{$v}");
                    }
                } else {
                    putenv("{$name}=>{$val}");
                }
            }
        }
    }
}