<?php
namespace core;
class Controller
{
    protected $controllerName;
    protected $actionName;

    public function assign($name, $value = null)
    {
        $this->view->assign($name, $value);
    }

    public function fetch($tpl = null)
    {
        if ($tpl == null || $tpl == '') {

        }
    }

    public function display($tpl = null, $data = array())
    {
        if ($tpl == null || $tpl == '') {
            $tpl = lcfirst($this->getControllerName()).DS.$this->getActionName().'.php';
        }
        $tpl = VIEWS_PATH.DS.$tpl;
        $this->view->display($tpl, $data);
    }

    public function ajaxReturn()
    {

    }

    public function getView()
    {
        return \core\View::getInstance();
    }

    public function getControllerName()
    {
        return App::getRouter()->getRouteMap()['controller'];
    }

    public function getActionName()
    {
        return App::getRouter()->getRouteMap()['action'];
    }

    public function __get($name)
    {
        if ($name == 'view') {
            return $this->getView();
        }
    }
}