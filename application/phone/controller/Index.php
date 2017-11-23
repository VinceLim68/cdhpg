<?php
namespace app\phone\controller;
use think\Controller;

class Index extends Controller {
    
    public function index() {
        //echo 111;
        return $this->fetch();
    }
}