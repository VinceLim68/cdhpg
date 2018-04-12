<?php

namespace app\phone\model;

use think\Model;
use think\Db;

//成交案例库
class EasyCjalkModel extends Model {
	protected $pk = 'KID';
	protected $table = 'PG_SE_Cjalk';
	// 设置当前模型的数据库连接
	protected $connection =  'EasyPG';
	protected $resultSetType = 'collection'; // 这个设置可以很快把返回数据集转成array
	protected $field = true; // 忽略非数据表字段而不报错
	
	//自定义初始化
    protected function initialize()
	{
	    //需要调用`Model`的`initialize`方法
	    parent::initialize();
	    //TODO:自定义的初始化
	}

	public function getCaseByNameAndDate(){
	    $records = $this->field([
	        'AlName'=>'Case_Name',
	        'dz'=>'Case_Located',
	        'yt'=>'Case_Type',
	        'cjdj'=>'Case_TrxPrice',
	        'jcnf'=>'Case_Cmpl_Years',
	        'recdate'=>'Case_TrxDate',
	        'InputName'=>'Opertor'
	    ])
	    ->where('AlName','like','%'.session('user.comm').'%')
	    ->order('Case_TrxDate desc')
	    ->where('recdate','> time',date('Y-m-d',strtotime('-'.config('historyDays').' day')))
	    ->select()->toArray();
	    return $records;
	}
	
}