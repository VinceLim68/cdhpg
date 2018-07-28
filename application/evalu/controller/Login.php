<?php
namespace app\evalu\controller;

use think\Controller;
use think\Session;
use app\evalu\model\UserModel;
use app\evalu\model\LoginRecordsModel;
use app\evalu\logic\LoginLogic;

class Login extends Controller
{
	/*
	 * 登录
	 */
	public function login(){
	    //把从哪里登录的模块记下来，从phone登录，返回phone，从evalu登录，返回evalu
//         $this->assign('mod',input('modulestr'));
	    $this->assign([
	        'controller'=>input('controller'),
	        'module'=>input('module'),
	        'action'=>input('action'),
// 	        'mod'          =>  input('mod'),
// 	        'origin_url'   =>  input('origin_url'),
	    ]);
//         $this->assign([
//             'mod'  => request()->module(),
//         ]);
        
        //         输入参数
        //         'user_name' => string '林晓' (length=6)
        //         'pass' => string '6656' (length=4)
        //         'matchineID' => 
// 		if(request()->isPost()){
// 			$res = (new UserModel())->login(input('post.'));
//  			if($res['valid'])
// 			{
// 				//登录成功
//                 if('phone' == input('mod')){
//                     $this->redirect('phone/index/index');
//                 }elseif ('evalu' == input('mod')){
// 			       $this->redirect('evalu/sales/salesList'); 
//                 }else{
//                     $this->redirect('phone/index/index');
//                 }
// 			}else 
// 			{
// 				//登录失败
// 				$this->error($res['msg']);exit;
// 			} 
// 		}
		return $this->fetch();
	}
	
	/*
	 * 登出
	 */
	public function logout()
	{
		Session::delete('user.user_id');
		Session::delete('user.user_name');
// 		cookie('lxtoken',null);
        return $this->fetch();
// 		$this->redirect('phone/index/index');
	}
	
	/*
	 * 注册
	 */
	public function signup()
	{
	    $data = input();
		$res = (new UserModel())->signup($data);
		
		return $res;
	}
	
	//ajax返回uid
	public function ajax_getUid(){
	    return getUID();
	}
	
	//ajax验证登录
	public function ajax_login_verify(){
	    $data = input();
	    
	    //把用户名和密码解密
// 	    $data['user_name'] = base_decode($data['user_name']);
// 	    $data['pass'] = base_decode($data['pass']);
	    
	    //验证用户名和密码，如果成功，会自动写入session
	    $res = (new UserModel())->login($data);
	    //登录成功，记入登录记录
// 	    if($res['valid']){
// 	        $ip = LoginLogic::getIP();
// 	        $isPhone = LoginLogic::isMobile()?'手机':'非手机';
// 	        LoginRecordsModel::create([
// 		        'user_name'	=>	$data['user_name'],
// 		        'login_ip'	=>	$ip,
// 		        'machine'   =>  $data['matchineID'],
//                 'type'      =>  '通过localstorage登录',
// 		        'isphone'   =>  $isPhone,
// 		    ]);
// 	    }
	    
	    return $res;
	}
	

	//自动跳转页面(进入页面去取localstorage)
	public function auto_jump(){
// 	    dump(input());
        //传递的参数还是加密状态，使用时才解密
	    $this->assign([
	        'controller'=>input('controller'),
	        'module'=>input('module'),
	        'action'=>input('action'),
	    ]);
	    return $this->fetch();
	}
}