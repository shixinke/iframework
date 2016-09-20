<?php
namespace core;
class Router
{
    private static $_instance;
    protected $_routerMap = array();
    private function __construct()
    {

    }

    public static function getInstance()
    {
        if (!self::$_instance || !(self::$_instance instanceof self)) {
            self::$_instance = new self();
        }
        return self::$_instance;
    }


    public function route()
    {
        $this->parseUri();
        $controllerFile = CONTROLLER_PATH.DS.$this->_routerMap['controller'].'.php';
        if (!is_file($controllerFile)) {
            error_page(404, '控制器文件'.$controllerFile.'不存在');
        }
        try {
            $className = ucfirst($this->_routerMap['controller']);
            $namespaceClassName = 'controllers\\'.$className;
            if (class_exists($namespaceClassName)) {
                $obj = new $namespaceClassName();
                $method = $this->_routerMap['action'].'Action';
                if (method_exists($obj, $method)) {
                    $obj->$method();
                } else {
                    error_page(404, '方法'.$className.'::'.$method.'不存在');
                }
            } else {
                error_page(404, '类{$className}不存在');
            }
        } catch(Exception $e) {
            error_page(500, $e->getMessage());
        }
    }

    public function parseUri()
    {
        if (isset($_SERVER['REQUEST_URI'])) {
            if (strpos($_SERVER['REQUEST_URI'], $_SERVER['SCRIPT_NAME']) === false) {
                $this->_routerMap['uri'] = '/';
                $this->_routerMap['controller'] = 'Index';
                $this->_routerMap['action'] = 'index';
            } else {
                $this->_routerMap['uri'] = str_replace($_SERVER['SCRIPT_NAME'], '', $_SERVER['REQUEST_URI']);
                $urlArr = explode('/', trim($this->_routerMap['uri'], '/'));
                $count = count($urlArr);
                if ($count >=2) {
                    $this->_routerMap['controller'] = ucfirst(array_shift($urlArr));
                    $this->_routerMap['action'] = array_shift($urlArr);
                    for($i = 0; $i < $count-2; $i +=2) {
                        if (isset($urlArr[$i+1])) {
                            $_GET[$urlArr[$i]] = $urlArr[$i+1];
                        }
                    }
                    unset($i);
                } else{
                    $this->_routerMap['controller'] = isset($urlArr[0]) ? ucfirst($urlArr[0]) : 'index';
                    $this->_routerMap['action'] = 'index';
                }
                unset($urlArr);
                unset($count);
            }
        }
    }

    public function getRouteMap()
    {
        return $this->_routerMap;
    }

    private function __clone()
    {

    }
}