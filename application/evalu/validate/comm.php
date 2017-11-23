<?php

namespace app\evalu\validate;

use think\Validate;

class comm extends Validate {
	/* 其实这样设置是不对的，因为searchfor 并不是comm表中的字段 */
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