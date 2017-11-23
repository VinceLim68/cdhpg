<?php

namespace app\index\controller;

use think\View;

class Test {
	public function index() {
		$view = new View ();
		return $view->fetch ( 'index' );
	}
	public function hello() {
		return 'hello,test';
	}
}