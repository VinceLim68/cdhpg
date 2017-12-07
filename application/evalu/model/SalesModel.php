<?php

namespace app\evalu\model;

use think\Model;

class SalesModel extends Model {
	protected $pk = 'id';
	protected $table = 'for_sale_property';
	protected $resultSetType = 'collection'; // 这个设置可以很快把返回数据集转成array
	protected $field = true; // 忽略非数据表字段而不报错
	
	static public function getRecordsByCommid($comm_id) {
	    //查询范围在最新的30万个记录中，但如果查询结果数量少于100个，则扩大30万个记录
	    $sele_times = 1;                                                    //计数：查询次数
// 	    $select_records_per_time = config('select_records_per_time');       //每次增加的查询范围
        $more_months = config('select_more_months_per_time');
	    $before_months = config('how_long_before_to_start_query');         //第一次查6个月的记录,'-6 month'
        $result = self::field('id,floor_index,total_floor,price,area,builded_year')
            ->where('community_id',$comm_id)
            ->where("first_acquisition_time", "> time", strtotime('-'.$before_months.' month'))
            ->select()
            ->toArray();
	    $records_num = count($result);                                       //累计：获得记录数
	    $min_base_records = config('min_base_records');                     //最低记录数
	    
	    while ($records_num < $min_base_records and $before_months <= 12) {
	        $before_months += $more_months;
	        $result = self::field('id,floor_index,total_floor,price,area,builded_year')
    	        ->where('community_id',$comm_id)
    	        ->where("first_acquisition_time", "> time", strtotime('-'.$before_months.' month'))
    	        ->select()
	           ->toArray();
	        $records_num = count($result);
// 	        $scope = Db::table('for_sale_property')
// 	        ->field('first_acquisition_time')
// 	        ->order('first_acquisition_time desc')
// 	        ->limit($sele_times*$select_records_per_time,1)
// 	        ->select();
	        //判断一下是否有数据
// 	        if($scope){
// 	            $result = Db::table('for_sale_property')
// 	            ->field('id,floor_index,total_floor,price,area,builded_year')
// 	            ->where('community_id',$comm_id)
// 	            ->where("first_acquisition_time", "> time", $scope[0]['first_acquisition_time'])
// 	            ->select();
// 	            $records_num = count($result);
// 	            $sele_times += 1;
// 	        }else{
// 	            break;
// 	        }
	    }
	    
	    $res[] = strtotime('-'.$before_months.' month');
	    $res[] = $result;
	    return $res;
	}
}
