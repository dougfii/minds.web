<?php
/**
 * Created by IntelliJ IDEA.
 * User: momo
 * Date: 2017/3/7
 * Time: 23:02
 */

namespace minds\response;

use minds\Response;

class View extends Response
{
    // 输出参数
    protected $options = [];
    protected $vars = [];
    protected $replace = [];
    protected $contentType = 'text/html';

    /**
     * 处理数据
     * @access protected
     * @param mixed $data 要处理的数据
     * @return mixed
     */
    protected function output($data)
    {
        // 渲染模板输出
        return ViewTemplate::instance(Config::get('template'), Config::get('view_replace_str'))->fetch($data, $this->vars, $this->replace);
    }

    /**
     * 获取视图变量
     * @access public
     * @param string $name 模板变量
     * @return mixed
     */
    public function getVars($name = null)
    {
        if (is_null($name)) {
            return $this->vars;
        } else {
            return isset($this->vars[$name]) ? $this->vars[$name] : null;
        }
    }

    /**
     * 模板变量赋值
     * @access public
     * @param mixed $name 变量名
     * @param mixed $value 变量值
     * @return $this
     */
    public function assign($name, $value = '')
    {
        if (is_array($name)) {
            $this->vars = array_merge($this->vars, $name);
            return $this;
        } else {
            $this->vars[$name] = $value;
        }
        return $this;
    }

    /**
     * 视图内容替换
     * @access public
     * @param string|array $content 被替换内容（支持批量替换）
     * @param string $replace 替换内容
     * @return $this
     */
    public function replace($content, $replace = '')
    {
        if (is_array($content)) {
            $this->replace = array_merge($this->replace, $content);
        } else {
            $this->replace[$content] = $replace;
        }
        return $this;
    }
}