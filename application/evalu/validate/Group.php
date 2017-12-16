<?php

namespace app\evalu\validate;

use think\Validate;

class Group extends Validate {
	protected $rule = [ 
			'title' => 'require' 
	]
	;
	protected $message = [ 
			'title.require' => '请输入用户组/角色名称',
			
	];
}