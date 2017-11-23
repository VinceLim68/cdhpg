<?php

namespace app\evalu\validate;

use think\Validate;

class Sales extends Validate {
	protected $rule = [ 
			'searchfor' => 'require|max:25|min:2' 
	]
	;
	protected $message = [ 
			'searchfor.require' => '必须输入小区名',
			'searchfor.max' => '小区名最长25个字符',
			'searchfor.min' => '小区名最少2个字符' 
	];
}