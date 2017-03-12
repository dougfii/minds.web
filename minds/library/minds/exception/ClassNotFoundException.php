<?php
/**
 * Created by IntelliJ IDEA.
 * User: momo
 * Date: 2017/2/19
 * Time: 下午9:38
 */

namespace minds\exception;

class ClassNotFoundException extends \RuntimeException
{
    protected $class;

    public function __construct($message, $class)
    {
        $this->message = $message;
        $this->class = $class;
    }

    /**
     * 获取类名
     * @access public
     * @return string
     */
    public function getClass()
    {
        return $this->class;
    }
}