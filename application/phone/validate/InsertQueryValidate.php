<?php
namespace app\phone\validate;

use think\Validate;

class InsertQueryValidate extends Validate
{
    protected $rule = [
        'Enquiry_CellName'	=>	'require',
        'Apprsal_Up'		=>	'require',
        'Enquiry_PmName'		=>	'require',
        'Apprsal_Use'		=>	'require',
        'OfferPeople'		=>	'require',
    ];
    
    protected $message = [
        'Enquiry_CellName.require'	=>	'请输入小区名称',
        'Apprsal_Up.require'	=>	'请输入报价',
        'Enquiry_PmName.require'	=>	'请输入询价人',
        'Apprsal_Use.require'	=>	'请输入用途',
        'OfferPeople.require'	=>	'请输入应价人',

    ];    
}