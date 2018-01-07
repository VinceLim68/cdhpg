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
	
	public function getPriceTypeAttr($value)
	{
	    $status = [1=>'抵押',2=>'二手'];
	    return $status[$value];
	}
	
	static public function insert_record($getPrice_result){
	    /*
	     * 添加记录，增加了判断重复的功能：同一个用户，同一个小区，2小时内不再记录
	     */
	    $res = self::field('id')
                    ->where('user_id',session('user.user_id'))
            	    ->where('comm_id',$getPrice_result['comm']['comm_id'])
            	    ->where("create_time", "> time", strtotime("-2 hours"))
            	    ->find();
	    //halt($res->id);
	    //没找到才追加
	    if(!$res){
	        $ins = self::create([
	                    'user_id'       =>  session('user.user_id'),
	                    'user_name'     =>  session('user.user_name'),
	                    'comm_name'     =>  $getPrice_result['comm']['comm_name'],
	                    'comm_id'       =>  $getPrice_result['comm']['comm_id'],
	                    'block'         =>  $getPrice_result['comm']['block']  ,
	                    'price'         =>  $getPrice_result['priceByDeal']> 0 ? $getPrice_result['priceByDeal']:$getPrice_result['mortgagePrice'],
	                    'price_type'    =>  $getPrice_result['priceByDeal']> 0 ? 2:1,
	                    'dealprice'     =>  $getPrice_result['price'],
	                ]);
	        return $ins->id;
	    }else{
	        return 0;
	    }
	    
	}
	
	static public function update_dispute($data){
	    /*
	     * 更新争议价格，一般是先查询，再进行争议，所以先找到刚才查询的记录，再作更新
	     */
	    $res = self::field('id')
	    ->where('user_id',session('user.user_id'))
	    ->where('comm_id',$data['comm_id'])
	    ->where("create_time", "> time", strtotime("-2 hours"))
	    ->find();
	    //找到了进行更新
	    if($res){
	        self::where('id', $res->id)->update(['dispute'  => $data['myprice']]);
	        return ['h'=>'感谢','b'=>'您的宝贵意见已经被记录，我们会认真考虑您的建议'];
	    }else{
	        return ['h'=>'糟糕','b'=>'忘了您刚才说的是哪个小区了'];
	    }
	}
}
