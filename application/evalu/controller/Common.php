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
			//$data = db('user')->find(1);
			//halt($data);
			$this->redirect('evalu/login/login');
		}
	}
}