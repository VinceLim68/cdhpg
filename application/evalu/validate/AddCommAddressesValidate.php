<?php
namespace app\evalu\validate;

use think\Validate;

class AddCommAddressesValidate extends Validate
{
	protected $rule = [
		'road'	      =>	'require',
		'doorplate'		=>	'require|regex:^(\d*-)?(\d+)号?(之[三二一四五六七八九十]*)?',
		'buildYear'		=>	'date',
	];
	
	protected $message = [
	    'doorplate.regex'  =>  '门牌格式不对，加个“号”试试',
	];
}