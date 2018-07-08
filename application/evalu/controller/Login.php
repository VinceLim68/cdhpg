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
	    //把从哪里登录的模块记下来，从phone登录，返回phone，从evalu登录，返回evalu
        $this->assign('mod',input('modulestr'));
        
		if(request()->isPost()){
// 		    halt(input('post.'));
			$res = (new UserModel())->login(input('post.'));
 			if($res['valid'])
			{
				//登录成功
                if('phone' == input('mod')){
                    $this->redirect('phone/index/index');
                }elseif ('evalu' == input('mod')){
			       $this->redirect('evalu/sales/salesList'); 
                }
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
		cookie('lxtoken',null);
		$this->redirect('phone/index/index');
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
			//注册成功，返回原来想登录的模块
// 			dump(input('post.'));
		    if('phone' == input('mod')){
		        $this->redirect('phone/index/index');
		    }elseif ('evalu' == input('mod')){
		        $this->redirect('evalu/sales/salesList');
		    
		    }
		}else
		{
			//注册失败
			$this->error($res['msg']);exit;
		}
	}
	
}