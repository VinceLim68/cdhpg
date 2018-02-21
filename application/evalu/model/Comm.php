<?php

namespace app\evalu\model;

use think\Model;
use think\Db;

class Comm extends Model {
	/**
	 * 操作表：小区信息
	 */
	protected $pk = 'Id';
	protected $table = 'comm';
	protected $resultSetType = 'collection'; // 这个设置可以很快把返回数据集转成array
	protected $field = true; // 忽略非数据表字段而不报错
	
	public function getPriLevelAttr($value) {
		$pri_level = [ 
				0 => '小区级',
				1 => '区块级' 
		];
		return $pri_level [$value];
	}
	
	public function commrelate()
	{  //定义与关联规则字段的一对多关系：一个小区对应一个、或者没有、或者多个规则
	    //因为我在模型名的后面加上了Model，所以这里的表名也要加上_model
	    return $this->hasMany('comm_relate_model','community_id','comm_id');
	}
	/*
	 * public function setPriLevelAttr($value)
	 * {
	 * $pri_level = array('小区级'=>0,'区块级'=>1);
	 * return $pri_level[$value];
	 * }
	 */
	
    // 	    删除一个小区，要相应删除所有的关联记录，包括：
	public function delWithRelation($community_id){
	    // 	    删除当前库中的挂牌记录，把community_id改成0
	    Db::table('for_sale_property')->where('community_id',$community_id)->update(['community_id'=>0]);
	    // 	    删除历史库中的挂牌记录，把community_id改成0
	    Db::table('allsales')->where('community_id',$community_id)->update(['community_id'=>0]);
	    // 	    删除关联规则表中的相关记录：根据community_id 删除
	    Db::table('comm_relate')->where('community_id',$community_id)->delete();
	    // 	    删除小区地址表中的相关记录：把comm_id改成0
	    Db::table('commaddress')->where('comm_id',$community_id)->update(['comm_id'=>0]);
	    // 	    删除错误信息里的相关记录：根据comm_id删除
	    Db::table('error_comm')->where('comm_id',$community_id)->delete();
	    // 	    删除历史基价里的相关记录：根据community_id删除
	    Db::table('commhistoryprice')->where('community_id',$community_id)->delete();
	    // 	    删除小区名称：根据community_id 删除
	    Db::table('comm')->where('comm_id',$community_id)->delete();
	}
	
	public static function getCommsArr() {
		/**
		 * 把小区名按关键字拆分后，按级别形成小区和道路两个数组
		 */
// 		$comms = self::field ( "comm_id,pri_level,keywords" )->select ()->toArray();
		$comms = self::field ( "comm_id,pri_level,keywords,comm_name" )->select ()->toArray();
		$comms_arr = array();
		$roads_arr = array();
		foreach ( $comms as $comm ) {
			$kws = explode ( ",", $comm ['keywords'] );
			foreach ( $kws as $kw ) {
				$temp = array ();
				$temp ['id'] = $comm ['comm_id'];
				$temp ['keyword'] = $kw;
				$temp ['comm_name'] = $comm['comm_name'];
				if ($comm['pri_level'] == '小区级') {
					$comms_arr [] = $temp;
				}else{
					$roads_arr [] = $temp;
				}
			}
		}
		$block_arr = array();
		$block_arr['comms'] = $comms_arr;
		$block_arr['roads'] = $roads_arr;
		return $block_arr;
		/* $comms = self::field ( "comm_id,pri_level,keywords" )->where('pri_level','1')->select ()->toArray();
		return $comms; */
	} 
	public static function getAll() {
		$comms = self::field ( "comm_id,comm_name,pri_level,keywords" )->select ();
		return $comms;
	}
	public static function getRegions() {
		$regions = self::field ( 'region' )->distinct ( true )->select ()->toArray ();
		return $regions;
	}
	public static function getBlockByRegion($reg) {
		$blocks = self::field ( 'block' )->distinct ( true )->where ( 'region', $reg )->select ()->toArray ();
		return $blocks;
	}
}