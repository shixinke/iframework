<?php
namespace controllers;
use core\App;
use \core\Controller;
class Blog extends Controller
{
    public function __construct()
    {

    }

    public function readAction()
    {
        $model = new \core\Model();
        dump($model->delete('province', array('province_code'=>'CHE')));
        dump($model->getLastSql());
        $this->assign(array('title'=>'标题', 'content'=>'内容'));
        $this->display();
    }
}