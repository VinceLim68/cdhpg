<?php

namespace app\evalu\model;

use think\Model;

class SalesModel extends Model {
	protected $pk = 'id';
	protected $table = 'for_sale_property';
	protected $resultSetType = 'collection'; // 这个设置可以很快把返回数据集转成array
	protected $field = true; // 忽略非数据表字段而不报错
	
	static public function getRecordsByCommid($data) {
	    if(isset($data['rela_ratio'])){
    	    $fields = 'id,floor_index,total_floor,price*'.$data['rela_ratio'].' as price,area,builded_year';
	    }else{
    	    $fields = 'id,floor_index,total_floor,price,area,builded_year';
	    }
        return self::getById($data, $fields);
	}
	
	static public function getForExcel($comm_id){
	    return self::getById($comm_id, 'title,community_name,price,area,total_price,spatial_arrangement,
	        floor_index,total_floor,builded_year,advantage,details_url');
	}
	
	static private function getById($data,$fields){
	    //查询范围在最新的30万个记录中，但如果查询结果数量少于100个，则扩大30万个记录
	    //传入的参数,起作用的是：$data['rela_comm_id'],$data['community_id'],
	    //$data['where'],$data['rela_ratio'],$data['rela_weight'];
	    $sele_times = 1;                                                    //计数：查询次数
	    // 	    $select_records_per_time = config('select_records_per_time');       //每次增加的查询范围
	    $more_months = config('select_more_months_per_time');
	    $before_months = config('how_long_before_to_start_query');         //第一次查6个月的记录,'-6 month'
	    if(is_array($data)){
	        //有关联小区就用关联小区的数据，否则取自己的
	        $comm_id = (isset($data['rela_comm_id']) and $data['rela_comm_id']!=0) ? $data['rela_comm_id'] : $data['community_id'];
	        $where = isset($data['where']) ? $data['where'] : ' 1=1 ';
	        $rela_ratio = isset($data['rela_ratio']) ? $data['rela_ratio'] : 1;
	        $rela_weight = isset($data['rela_weight']) ? $data['rela_weight'] : 1;
	    }else{
	        //这部分在getForExcel时有用
	        $comm_id = $data;
	        $where = ' 1=1 ';
	        $rela_ratio = 1;
	        $rela_weight = 1;
	    }
// 	    halt($data);
	    $result = self::field($fields)
    	    ->where('community_id',$comm_id)
    	    ->where("first_acquisition_time", "> time", strtotime('-'.$before_months.' month'))
    	    ->where($where)
//     	    ->fetchSql(true)
    	    ->select()
    	    ->toArray();
	     
	    $res[] = strtotime('-'.$before_months.' month');
	    $res[] = $result;
	    return $res;
	}
    
	//软删除community_id(置为0）时，避免重复数据的一个方法
    static public function updateWithoutduplicate($id,$updateArr){
        //输入$id值和一个用于修改记录的数组
        if(isset($updateArr['community_id']) and $updateArr['community_id']<= 999){
            //如果修改涉及改变community_id，经常会发生重复的情况
            $flag = true;   //一个是否循环的标识位
            while ($updateArr['community_id'] <= 999 and $flag){
                //循环增加id值，保证不重复
                try{
                    $result = self::where('id',$id)->update($updateArr);
                    break;          //跳出循环
                }catch(\Exception $e){
                    if($e->getCode()==10501){
                        $updateArr['community_id'] = $updateArr['community_id'] + 1;
                    }else{
                        //非重复原因不用循环
                        $flag = false;
                        $result = 'Message: </br>' .$e->getMessage();
                    }
                }
            }
        }else{
            try{
                $result = self::where('id',$id)->update($updateArr);
            }catch(\Exception $e){
                $result = 'Message: </br>' .$e->getMessage();
            }
        }
        return $result;
    }
}
