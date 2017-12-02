<?php
namespace app\evalu\validate;

use think\Validate;

class UserSignupValidate extends Validate
{
	protected $rule = [
			'user_name'	=>	'require|length:2,10',
			'pass'		=>	'require',
			'pass_confirm'	=>	'require|confirm:pass',
			'email'		=>	'email|require'
	];
	
	protected $message = [
			'user_name.require'	=>	'请输入用户名',
			'user_name.length'	=>	'用户名必须在2-10位',
			'pass.require'		=>	'请输入密码',
			'pass_confirm.require'		=>	'请再次输入密码',
			'email.email'		=>	'请输入正确的邮箱格式',
			'email.require'		=>	'请输入邮箱,这是以后找回密码的方式',
			'pass_confirm.confirm'		=>	'两次输入的密码不一致',
	];
}