<?php
namespace app\evalu\validate;

use think\Validate;

class UserValidate extends Validate
{
	protected $rule = [
			'user_name'	=>	'require|length:2,10',
			'pass'		=>	'require',
// 			'code'		=>	'require|captcha',
	];
	
	protected $message = [
			'user_name.require'	=>	'请输入用户名',
	        'user_name.length'	=>	'用户名长度不正确',
			'pass.require'		=>	'请输入密码',
// 			'code.require'		=>	'请输入验证码',
// 			'code.captcha'		=>	'验证码不正确',
	];
}