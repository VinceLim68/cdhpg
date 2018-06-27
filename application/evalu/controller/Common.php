<?php
namespace app\evalu\controller;

use think\Controller;
use think\Request;
use app\evalu\model\UserModel;
use think\Session;
// use think\Auth;

class Common extends Controller
{
	public function __construct(Request $request = NULL)
	{
		parent::__construct($request);
		//执行登录验证
		if(!session('user.user_id') )
		{
		    //没有session，再取token.设置的token为“lxtoken”
// 		    halt('进来了，没session');
		    if(cookie('lxtoken')){
		        $token = cookie('lxtoken');
// 		        halt($token);
		        $res = $this->checkToken($token);//这里会更新token的有效时间
// 		        halt($res);
		    }else{
		        $res = 90003;
		    }
		    if($res >= 90002){
                // 			注意这里传递了参数，但是用url方式，接收时要使用$_GET
    		    $this->redirect('evalu/login/login',['modulestr' => $request->module()]);
		    }
		}
		$controller = request()->controller();
		$action = request()->action();
		$module = request()->module();
		$act = strtolower($module.'/'.$controller.'/'.$action);       //$controller . '/' . $action
		$auth = new \Auth();
 		if(!$auth->check($act,session('user.user_id'))){
		    $this->error(session('user.user_name').':'.$act.'你没有权限访问');
		} 
	}
	
	//用于检验 token 是否存在, 并且更新 token
	public function checkToken($token)
	{
	    $user = new UserModel();
	    $res = $user->field('time_out,user_id,user_name')->where('token', $token)->find();
// 	   halt($res->toarray());
	    if (!empty($res)) {
	        //dump(time() - $res[0]['time_out']);
	        if (time() - $res['time_out'] > 0) {
	            return 90003; //token长时间未使用而过期，需重新登陆
	        }
	        $new_time_out = time() + config('token_expire'); //604800是七天
	        $update = $user->isUpdate(true)
    	        ->where('token', $token)
    	        ->update(['time_out' => $new_time_out]);
	        if ($update) {
    	        session('user.user_id',$res['user_id']);
    	        session('user.user_name',$res['user_name']);
	            return 90001; //token验证成功，time_out刷新成功，可以获取接口信息
	        }
	    }
	
	    return 90002; //token错误验证失败
	}
	
	//创建 token
	public function makeToken()
	{
	
	    $str = md5(uniqid(md5(microtime(true)), true)); //生成一个不会重复的字符串
	    $str = sha1($str); //加密
	    return $str;
	}
	
	//这是测试用的，要删
	public function test(){
// 	    $time = time();
// 	    echo date("Y-m-d H:i:s",$time);
//         $phoneToken = $this->makeToken();
//         echo strlen($phoneToken);
//         cookie('phoneToken',$phoneToken);
// 	    cookie('phoneToken', null);
//         echo cookie('phoneToken')===null;
//         Session::delete('user.user_id');
// 		Session::delete('user.user_name'); 
        echo session('user.user_id');
        echo session('user.user_name');
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
	        $auth=new \Auth();
	        return $auth->check($rule,$uid,$relation)?$t:$f;
	    }
	}
}