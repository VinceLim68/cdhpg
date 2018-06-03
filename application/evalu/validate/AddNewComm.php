<?php
namespace app\evalu\validate;

use think\Validate;

class AddNewComm extends Validate
{
	protected $rule = [
		'comm_name'	      =>	'require',
		'region'		=>	'require',
		'block'		=>	'require',
		'keywords'		=>	'require',
		'pri_level'		=>	'require',
	];
	
	protected $message = [
	    'comm_name.require'  =>  '必须有小区的名称',
	    'region.require'  =>  '必须有区域的名称',
	    'block.require'  =>  '必须有片区的名称',
	    'keywords.require'  =>  '必须有关键字',
	    'pri_level.require'  =>  '必须设置小区名称的级别',
	];
}