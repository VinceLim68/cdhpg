<?php

namespace app\phone\model;

use think\Model;

class QueryRecordsModel extends Model {
	protected $pk = 'id';
	protected $table = 'query_records';
	protected $resultSetType = 'collection'; // 这个设置可以很快把返回数据集转成array
	protected $field = true; // 忽略非数据表字段而不报错
	protected $autoWriteTimestamp = true;		//自动转化时间戳
	protected $updateTime = false;
	
	static public function insert_record($getPrice_result){
	    /*
	     * 添加记录，增加了判断重复的功能：同一个用户，同一个小区，2小时内不再记录
	     */
	    //             'user_id'       =>  session('user.user_id'),
	    //             'user_name'     =>  session('user.user_name'),
	    //             'comm_name'     =>  $getPrice_result['comm']['comm_name'],
	    //             'comm_id'       =>  $getPrice_result['comm']['comm_id'],
	    //             'block'         =>  $getPrice_result['comm']['block']  ,
	    //             'price'         =>  $getPrice_result['priceByDeal']> 0 ? $getPrice_result['priceByDeal']:$getPrice_result['mortgagePrice'],
	    //             'price_type'    =>  $getPrice_result['priceByDeal']> 0 ? 2:1,
	    //             'dealprice'     =>  $getPrice_result['price'],
	    $res = self::field('id')
                    ->where('user_id',session('user.user_id'))
            	    ->where('comm_id',$getPrice_result['comm']['comm_id'])
            	    ->where("create_time", "> time", strtotime("-1 hours"))
            	    ->find();
	    //没找到才追加
	    if(!$res){
	        self::create([
	                    'user_id'       =>  session('user.user_id'),
	                    'user_name'     =>  session('user.user_name'),
	                    'comm_name'     =>  $getPrice_result['comm']['comm_name'],
	                    'comm_id'       =>  $getPrice_result['comm']['comm_id'],
	                    'block'         =>  $getPrice_result['comm']['block']  ,
	                    'price'         =>  $getPrice_result['priceByDeal']> 0 ? $getPrice_result['priceByDeal']:$getPrice_result['mortgagePrice'],
	                    'price_type'    =>  $getPrice_result['priceByDeal']> 0 ? 2:1,
	                    'dealprice'     =>  $getPrice_result['price'],
	                ]);
	    }
	    
	}
	
}
