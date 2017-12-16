<?php
namespace app\evalu\controller;

use think\Controller;
use think\Request;

class Common extends Controller
{
	public function __construct(Request $request = NULL)
	{
		parent::__construct($request);
		//执行登录验证
		if(!session('user.user_id'))
		{
// 			注意这里传递了参数，但是用url方式，接收时要使用$_GET
		    $this->redirect('evalu/login/login',['modulestr' => $request->module()]);
		}
	}
}