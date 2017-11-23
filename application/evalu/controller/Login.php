<?php
namespace app\evalu\controller;

use think\Controller;
use think\Session;
use app\evalu\model\UserModel;

class Login extends Controller
{
	/*
	 * 登录
	 */
	public function login(){
		if(request()->isPost()){
			$res = (new UserModel())->login(input('post.'));
 			if($res['valid'])
			{
				//登录成功
				//$this->success($res['msg'],'evalu/sales/salesList');exit;
				$this->redirect('evalu/sales/salesList');
			}else 
			{
				//登录失败
				$this->error($res['msg']);exit;
			} 
		}
		return $this->fetch();
	}
	
	/*
	 * 登出
	 */
	public function logout()
	{
		Session::delete('user.user_id');
		Session::delete('user.user_name');
		$this->redirect('evalu/login/login');
	}
	
	/*
	 * 注册
	 */
	public function signup()
	{
// 		dump(input('post.'));
		$res = (new UserModel())->signup(input('post.'));
		if($res['valid'])
		{
			//注册成功，去登录界面
			$this->success($res['msg'],'login');exit;
		}else
		{
			//注册失败
			$this->error($res['msg']);exit;
		}
	}
	
}