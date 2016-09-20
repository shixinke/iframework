<?php
namespace core;
class View
{
    private static $_instance;
    protected $data = array();

    private function __construct()
    {

    }

    public function assign($name, $value = null)
    {
        if (is_array($name)) {
            $this->data = array_merge($this->data, $name);
        } else {
            $this->data[$name] = $value;
        }
    }

    public static function getInstance()
    {
        if (!self::$_instance || !(self::$_instance instanceof self)) {
            self::$_instance = new self();
        }
        return self::$_instance;
    }

    public function display($tpl, $data = array())
    {
        echo $this->fetch($tpl, $data);
    }

    public function fetch($tpl, $data)
    {
        if (!empty($data)) {
            $this->data = array_merge($this->data, $data);
        }
        if (!empty($this->data)) {
            extract($this->data);
        }
        if (App::inFileMap($tpl)) {
            return true;
        }
        if (is_file($tpl)) {
            $content = require_once $tpl;
            App::addFileMap($tpl);
            return $content;
        } else {
            error_page(404, '模板文件'.$tpl.'不存在');
        }
    }
}