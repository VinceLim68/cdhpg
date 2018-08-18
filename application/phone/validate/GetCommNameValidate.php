<?php
namespace app\phone\validate;

use think\Validate;

class GetCommNameValidate extends Validate
{
    protected $rule = [
        'comm' => 'require|max:70|min:2',
        'price'=>   'number|between:100,12000000',
    ];
    
    protected $message = [
        'comm.require' => '请问您要查询哪个小区？',
        'comm.max'     => '名称最多不能超过70个字符',
        'comm.min'     => '名称最少要两个字',
        'price.number'  =>'成交价请输入数字',
        'price.between'  =>'认真一点，把你的成交价填进去',
    ];    
}