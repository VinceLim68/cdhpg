<?php

namespace app\evalu\validate;

use think\Validate;

class Rule extends Validate {
	protected $rule = [ 
		'title' => 'require' ,
	    'name' =>  'require',
	    'type' =>  'require',
	    'pid' =>  'require',
	]
	;
	protected $message = [ 
			'title.require' => '请输入权限名称',
			'name.require' => '请输入权限的具体内容',
			'type.require' => '请输入权限类型：项目、模块还是操作？',
			'pid.require' => '请输入权限的上一级节点',
			
	];
}