<?php
namespace app\evalu\controller;

use think\Controller;
use think\Request;
use app\evalu\model\UserModel;
use think\Session;
use app\evalu\logic\LoginLogic;
use app\evalu\model\LoginRecordsModel;
// use think\Auth;

class Common extends Controller
{
	public function __construct(Request $request = NULL)
	{
		parent::__construct($request);
		//执行登录验证
		$controller = request()->controller();
		$action = request()->action();
		$module = request()->module();
		$act = strtolower($module.'/'.$controller.'/'.$action);
// 		$url = $request->url();
// 		$url = base_encode(ltrim(rtrim($url,'.html'),'/'));
//         $url = base_encode($url);
		
// 		halt($act) ;
		$isPhone = LoginLogic::isMobile()?'手机':'非手机';

		if(!session('user.user_id') )
		{
	        $input = input();
		    //如果是微信并且有微信名,LoginLogic::isWeixin() and 
// 		    dump($input);
		    if(isset($input['nickname']) and '' != trim($input['nickname'])){
		        //只要进到这里，无论有没有用户信息，都会登录了。
		        $nickname = trim($input['nickname']);
		        $input['nickname'] = $nickname;
		        $user = new UserModel;
		        $user->loginByWeiXin($input);
		    }else{
    		    //去页面找看看有没有localstorage
    		    //先把传入的参数连接成字符串，以便传递
    		    $query_str = "";
    		    foreach ($input as $key => $value){
    		        $query_str .= $key."=".$value."&";
    		    }
    		    $this->redirect('evalu/login/auto_jump',[
    		        'controller'=>$controller,
    		        'module'=>$module,
    		        'action'=>$action,
    		        'input'=> $query_str,
    		    ]);
		    }
// 		    //没有session，再取token.设置的token为“lxtoken”
// 		    if(cookie('lxtoken')){
// 		        $token = cookie('lxtoken');
// 		        $res = $this->checkToken($token);//这里会更新token的有效时间
// 		    }else{
// 		        $ip = LoginLogic::getIP();
// 		        $machine = LoginLogic::getMachine();
// 		        LoginRecordsModel::create([
// 		            'user_name'	=>	'无session',
// 		            'login_ip'	=>	$ip,
// 		            'machine'     =>  $machine,
// 		            'type'     =>  '客户端无token，需要重新登录',
// 		            'isphone'     =>  $isPhone,
// 		        ]);
// 		        $res = 90003;
// 		    }
// 		    if($res >= 90002){
//                 // 			注意这里传递了参数，但是用url方式，接收时要使用$_GET
//     		    $this->redirect('evalu/login/login',['modulestr' => $request->module()]);
// 		    }
		}
		$auth = new \Auth();
 		if($isPhone=='非手机'){
 		    //只有经过授权才能使用桌面系统
    		if(!$auth->check('onDesktop', session('user.user_id'))){
    		    $this->error('程序出错了！！！如需要合作开发或者业务联系，请找18006006153林先生！！');
    		}
		} 
 		if(!$auth->check($act,session('user.user_id'))){
		    $this->error(session('user.user_name').':'.$act.'你没有权限访问');
		} 
	}

	

	
// 	//用于检验 token 是否存在, 并且更新 token
// 	public function checkToken($token)
// 	{
// 	    $user = new UserModel();
// 	    $res = $user->field('time_out,user_id,user_name')->where('token', $token)->find();
// 	    $ip = LoginLogic::getIP();
// 	    $machine = LoginLogic::getMachine();
// 	    $isPhone = LoginLogic::isMobile()?'手机':'非手机';
// 	    //token能找到
// 	    if (!empty($res)) {
// 	        $time = strtotime($res['time_out']);
	        
// 	        //token过期;
// 	        if (time() - $time > 0) {
// 	            LoginRecordsModel::create([
// 	                'user_name'	=>	$res['user_name'],
// 	                'login_ip'	=>	$ip,
// 	                'machine'     =>  $machine,
// 	                'type'     =>  'token过期,待登录：'.$res['time_out'],
// 	                'isphone'     =>  $isPhone,
// 	            ]);
// 	            return 90003; //token长时间未使用而过期，需重新登陆
// 	        }else{
//     	        $new_time_out = time() + config('token_expire'); //604800是七天
//     	        $update = $user->isUpdate(true)
//         	        ->where('token', $token)
//         	        ->update(['time_out' => $new_time_out]);
//     	        session('user.user_id',$res['user_id']);
//     	        session('user.user_name',$res['user_name']);
//     	        //把cookie再存一次
//     	        cookie('lxtoken',$token);
//     	        if ($update) {
//         	        LoginRecordsModel::create([
//         	            'user_name'	=>	$res['user_name'],
//         	            'login_ip'	=>	$ip,
//         	            'machine'     =>  $machine,
//         	            'type'     =>  '免登录,更新token有效期'.date("Y-m-d H:i:s",$new_time_out),
//         	            'isphone'     =>  $isPhone,
//         	        ]);
//     	            return 90001; //token验证成功，time_out刷新成功，可以获取接口信息
//     	        }else{
//     	            LoginRecordsModel::create([
//     	                'user_name'	=>	$res['user_name'],
//     	                'login_ip'	=>	$ip,
//     	                'machine'     =>  $machine,
//     	                'type'     =>  '免登录,但没更新成功',
//     	                'isphone'     =>  $isPhone,
//     	            ]);
//     	        }
// 	        }
// 	    }else{
// 	        LoginRecordsModel::create([
// 	            'user_name'	=>	'无session',
// 	            'login_ip'	=>	$ip,
// 	            'machine'     =>  $machine,
// 	            'type'     =>  '未找到匹配的token',
// 	            'isphone'     =>  $isPhone,
// 	        ]);
//     	    return 90002; //token错误验证失败
// 	    }
	
// 	}
	
// 	//创建 token
// 	public function makeToken()
// 	{
// 	    $str = md5(uniqid(md5(microtime(true)), true)); //生成一个不会重复的字符串
// 	    $str = sha1($str); //加密
// 	    return $str;
// 	}
	
// 	//这是测试用的，要删
// 	public function del_test(){
// // 	    $time = time();
// // 	    echo date("Y-m-d H:i:s",$time);
// //         $phoneToken = $this->makeToken();
// //         echo strlen($phoneToken);
// //         cookie('phoneToken',$phoneToken);
// // 	    cookie('phoneToken', null);
// //         echo cookie('phoneToken')===null;
// //         Session::delete('user.user_id');
// // 		Session::delete('user.user_name'); 
// //         echo session('user.user_id');
// //         echo session('user.user_name');
// //         $result = $this->isMobile();
// //         echo LoginLogic::getMachine();
// // 	    $token = cookie('lxtoken');
// // 	    $user = new UserModel();
// // 	    $res = $user->field('time_out,user_id,user_name')->where('token', $token)->find();
// // 	    echo $res['time_out'].'</br>';
// // 	    $time = strtotime($res['time_out']);
// // 	    echo $time.'</br>';
// // 	    echo time().'</br>';
	    
	    
// // 	    if($time-time()>0){
// // 	        echo '超过';
// // 	    }else{
// // 	        echo '没超过';
// // 	    }
// 	    Session::delete('user.user_id');
// 	    Session::delete('user.user_name');
// 	    echo cookie('lxtoken');
// 	}
	
	
	//这是抄来的程序，先放在这里备用
	/*$rule,要验证的规则名称；
	 $uid,用户的id；
	 $relation，规则组合方式，默认为‘or’，以上三个参数都是根据Auth的check（）函数来的，
	 $t,符合规则后，执行的代码
	 $f，不符合规则的，执行代码，默认为抛出字符串‘没有权限’
	 在模板中调用 {:authcheck('adminmenu',$uid,'or','<a href="/Home/Admin/index">管理中心</a>','')}
	 这时侯有权限的才会有<a href="/Home/Admin/index">管理中心</a>代码
	function authcheck($rule,$uid,$relation='or',$t,$f='没有权限'){
	    //判断当前用户UID是否在定义的超级管理员参数里
	    if(in_array($uid,config('administrator'))){
	        return $t;    //如果是，则直接返回真值，不需要进行权限验证
	    }else{
	        //如果不是，则进行权限验证；
	        $auth=new \Auth();
	        return $auth->check($rule,$uid,$relation)?$t:$f;
	    }
	} */
}