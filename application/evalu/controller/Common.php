<?php
namespace app\evalu\controller;

use think\Controller;
use think\Request;
use think\Auth;

class Common extends Controller
{
	public function __construct(Request $request = NULL)
	{
		parent::__construct($request);
		//执行登录验证
		$auth = new Auth();
		if(!session('user.user_id'))
		{
// 			注意这里传递了参数，但是用url方式，接收时要使用$_GET
		    $this->redirect('evalu/login/login',['modulestr' => $request->module()]);
		}
		//对功能模块进行限制，小区和挂牌数据列表非管理员不让看
		if(session('user.user_name') != 'admin' or session('user.user_name') != '林晓' ){
		    if($request->module()=='evalu'){
		        if($request->controller()=='Sales' or $request->controller()=='Comms'){
		            $this->error(session('user.user_name').'你没有操作权限');
		        }
		    }
		}

	}
	
	/*
	 $rule,要验证的规则名称；
	 $uid,用户的id；
	 $relation，规则组合方式，默认为‘or’，以上三个参数都是根据Auth的check（）函数来的，
	 $t,符合规则后，执行的代码
	 $f，不符合规则的，执行代码，默认为抛出字符串‘没有权限’
	 在模板中调用 {:authcheck('adminmenu',$uid,'or','<a href="/Home/Admin/index">管理中心</a>','')}
	 这时侯有权限的才会有<a href="/Home/Admin/index">管理中心</a>代码
	 */
	function authcheck($rule,$uid,$relation='or',$t,$f='没有权限'){
	    //判断当前用户UID是否在定义的超级管理员参数里
	    if(in_array($uid,config('administrator'))){
	        return $t;    //如果是，则直接返回真值，不需要进行权限验证
	    }else{
	        //如果不是，则进行权限验证；
	        import('ORG.Util.Auth');
	        $auth=new Auth();
	        return $auth->check($rule,$uid,$relation)?$t:$f;
	    }
	}
}