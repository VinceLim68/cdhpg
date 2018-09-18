<?php
namespace app\evalu\model;

use think\Model;
use think\Loader;
use app\evalu\logic\LoginLogic;
use app\evalu\controller\Common;


class UserModel extends Model
{
	protected $pk = 'user_id';
	protected $table = 'user';
	protected $autoWriteTimestamp = true;		//自动转化时间戳
	// 定义时间戳字段名
	protected $createTime = 'register_date';
	protected $updateTime = 'last_login';
	protected $resultSetType = 'collection';
	
	public function getStatusAttr($value)
	{
	    $status = [0=>'禁用',1=>'正常'];
	    return $status[$value];
	}
	
	public function getPassAttr($value)
	{
	    return md5($value);
	}
	
	public function getTimeOutAttr($value){
	    if (is_null($value)){
	        return '未设置';
	    }else{
    	    return date("Y-m-d H:i:s",$value);
	    }
	}
	
	//使用微信登录
	public function loginByWeiXin($data){
	    //使用微信登录，都会写session
	    //$data传过来只有nickname，machine,commname(登录时不用),lx(这其实是openid)
	    $ip = LoginLogic::getIP();
	    $isPhone = LoginLogic::isMobile()?'手机':'非手机';
// 	    $data['nickname'] = filter_Emoji($data['nickname']);   //清除姓名里的emoij符号
	    $userInfo = $this->where('user_name',$data['nickname'])->find();
	    if(!$userInfo){
	        //说明在数据库没找到用户,就增加一个
	        $userInfo = self::create([
	            'user_name'  =>  $data['nickname'],
                'email' =>  '微信',
                'last_ip'   =>  $ip,
                'login_times'  => 1, 
	            'openid'   =>  isset($data['lx']) ? $data['lx'] : '',
	        ]);
	        //新注册用户默认权限是普通会员
	        $group=array(
	            'uid'=>$userInfo->user_id,
	            'group_id'=>4
	        );
	        (new GroupAccessModel()) ->insert($group);
	    }else{
	        //如果已经有用户，就修改登录次数.取到openid的，就改openid
	        if(isset($data['lx']) and '' != trim($data['lx'])){
    	        $this->save([
    	            'login_times'  => $userInfo['login_times']+1,
    	            'last_ip' => $ip,
    	            'openid'   =>$data['lx'],
    	        ],['user_id' => $userInfo['user_id']]);
	        }else{
    	        $this->save([
    	            'login_times'  => $userInfo['login_times']+1,
    	            'last_ip' => $ip,
    	        ],['user_id' => $userInfo['user_id']]);
	        }
	    }
	    session('user.user_id',$userInfo['user_id']);
	    session('user.user_name',$userInfo['user_name']);
	    LoginRecordsModel::create([
	        'user_name'	=>	   $userInfo['user_name'],//?$userInfo['user_name']:$data['nickname'],
	        'login_ip'	=>	   $ip,
            'type'      =>     '微信登录',
	        'isphone'   =>     $isPhone,
	        'machine'   =>     $data['machine'],
            'openid'   =>  isset($data['lx']) ? $data['lx'] : '',
	    ]);
	    return ;
	}
	
	/*
	 * 登录
	 */
	public function login($data)
	{
		//		1.验证
		$validate = Loader::validate('UserValidate');
		$ip = LoginLogic::getIP();
// 		$machine = LoginLogic::getMachine();
		$machine = $data['matchineID'];
		$isPhone = LoginLogic::isMobile()?'手机':'非手机';
		// 		数据类型如果验证不通过
		if(!$validate->check($data)){
		    LoginRecordsModel::create([
// 		        'user_name'	=>	'待登录',
		        'user_name'	=>	$data['user_name'],
		        'login_ip'	=>	$ip,
		        'machine'     =>  $machine,
		        'type'     =>  '输入数据验证未通过',
		        'isphone'     =>  $isPhone,
		    ]);
			return ['valid'=>0,'msg'=>$validate->getError()];
		}else{
    		// 		2.比对用户名和密码
    // 		halt($data);
    		$userInfo = $this->where('user_name',$data['user_name'])->where('pass',$data['pass'])->find();
    		if(!$userInfo)
    		{
    			//说明在数据库没找到用户
    		    LoginRecordsModel::create([
    		        'user_name'	=>	$data['user_name'],
    		        'login_ip'	=>	$ip,
    		        'machine'     =>  $machine,
    		        'type'     =>  '用户名或者密码不正确',
    		        'isphone'     =>  $isPhone,
    		    ]);
    			return ['valid'=>0,'msg'=>'用户名或者密码不正确']; 
    		}else{
        		// 		3.写入session，更新数据库的最后登录时间，最后登录ip，总登录次数
        		session('user.user_id',$userInfo['user_id']);
        		session('user.user_name',$userInfo['user_name']);
//         		$token = (new Common())->makeToken();
//         		cookie('lxtoken',$token);
    
        		LoginRecordsModel::create([
        		    'user_name'	=>	$data['user_name'],
        		    'login_ip'	=>	$ip,
        		    'machine'     =>  $machine,
        		    'type'     =>  $data['type'],
        		    'isphone'     =>  $isPhone,
        		]);
        		$this->save([
        		    'login_times'  => $userInfo['login_times']+1,
        		    'last_ip' => $ip,
//         		    'token'   =>  $token,
        		    'time_out' => time() + config('token_expire'),
        		     
        		],['user_id' => $userInfo['user_id']]);
        		return ['valid'=>1,'msg'=>'登录成功'];
    		}
		    
		}
	}
	
	//使用ajax注册模块,重写了
	public function signup($data){

	    $validate = Loader::validate('UserSignupValidate');
		// 		如果验证不通过
		if(!$validate->check($data)){
			return ['valid'=>0,'msg'=>$validate->getError()];
		}
		try{
			$res = $this->data($data)->allowField(true)->save();//这个就是新增用户了
		}catch(\Exception $e){
			//	插入失败，错误代码是10501时，表示用户名重复
			if($e->getCode()==10501)
			{
				return ['valid'=>0,'msg'=>'用户名已被使用,再想一个吧'];;
			}else{
				dump($e);
			}
		}
		// 			直接执行登录了
		session('user.user_id',$this->user_id);
		session('user.user_name',$data['user_name']);
		// 		$token = (new Common())->makeToken();
		// 		cookie('lxtoken',$token);
		$ip = LoginLogic::getIP();
		$machine = $data['matchineID'];
		$isPhone = LoginLogic::isMobile()?'手机':'非手机';
		LoginRecordsModel::create([
		    'user_name'	=>	$data['user_name'],
		    'login_ip'	=>	$ip,
		    'machine'   =>  $machine,
		    'type'      =>  '注册登录',
		    'isphone'   =>  $isPhone,
		]);
		$this->save([
		    'login_times'  => 1,
		    'last_ip' => $ip,
		],['user_id' => $this->user_id]);
		//新注册用户默认权限是普通会员
		$group=array(
		    'uid'=>$this->user_id,
		    'group_id'=>4
		);
		(new GroupAccessModel()) ->insert($group);
		return ['valid'=>1,'msg'=>'注册成功，请登录'];
	}
	
	//注册
// 	public function signup($data)
// 	{
// 		$validate = Loader::validate('UserSignupValidate');
// 		// 		如果验证不通过
// 		if(!$validate->check($data)){
// 			return ['valid'=>0,'msg'=>$validate->getError()];
// 		}
		
// 		try{
		    
// 			$res = $this->data($data)->allowField(true)->save();
// // 			halt($data);
// 		}catch(\Exception $e){
// 			//	插入失败，错误代码是10501时，表示用户名重复
// 			if($e->getCode()==10501)
// 			{
// 				return ['valid'=>0,'msg'=>'用户名已被使用,再想一个吧'];;
// 			}else{
// 				dump($e);
// 			}
// 		}
// // 			直接执行登录了
// 		session('user.user_id',$this->user_id);
// 		session('user.user_name',$data['user_name']);
// // 		$token = (new Common())->makeToken();
// // 		cookie('lxtoken',$token);
// 		$ip = LoginLogic::getIP();
// 		$machine = LoginLogic::getMachine();
// 		LoginRecordsModel::create([
// 		    'user_name'	=>	$data['user_name'],
// 		    'login_ip'	=>	$ip,
// 		    'machine'     =>  $machine,
// 		    'type'     =>  '注册登录',
// 		]);
// 		$this->save([
// 		    'login_times'  => 1,
// 		    'last_ip' => $ip,
// 		],['user_id' => $this->user_id]);
// 		//新注册用户默认权限是普通会员
// 		$group=array(
// 		    'uid'=>$this->user_id,
// 		    'group_id'=>4
// 		);
// 		(new GroupAccessModel()) ->insert($group);
// 		return ['valid'=>1,'msg'=>'注册成功，请登录'];
			
// 	}
	
	//使用后台添加用户
	public function add_user($data){
	    try{
	        $res = $this->data($data)->allowField(true)->save();
	    }catch(\Exception $e){
	        //	插入失败，错误代码是10501时，表示用户名重复
	        if($e->getCode()==10501)
	        {
	            return ['valid'=>0,'msg'=>'用户名已被使用,再想一个吧'];
	        }else{
	            dump($e);
	            return ['valid'=>0,'msg'=>$this->getError()];
	        }
	    }
	    // 			记录ip
	    $ip = LoginLogic::getIP();
	    $this->save([
	        'login_times'  => 0,
	        'last_ip' => $ip,
	    ],['user_id' => $this->user_id]);
	    //把权限记录上去
	    $GA = new GroupAccessModel();
	    if(empty($data['group_ids'])){
	        //如果没有指定权限，就是游客
	        $group=array(
	            'uid'=>$this->user_id,
	            'group_id'=>4
	        );
	        $GA->insert($group);
	    }else{
    	    foreach ($data['group_ids'] as $k => $v) {
    	        $group=array(
    	            'uid'      =>  $this->user_id,
    	            'group_id' =>  $v
    	        );
    	        $GA ->insert($group);
    	    }
	    }
	    return ['valid'=>1,'msg'=>'添加成功'];
	    
	    
	}
	
}
