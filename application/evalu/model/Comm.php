<?php

namespace app\evalu\model;

use think\Model;

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
	
	/*
	 * public function setPriLevelAttr($value)
	 * {
	 * $pri_level = array('小区级'=>0,'区块级'=>1);
	 * return $pri_level[$value];
	 * }
	 */
	public static function getCommsArr() {
		/**
		 * 把小区名按关键字拆分后，按级别形成小区和道路两个数组
		 */
		$comms = self::field ( "comm_id,pri_level,keywords" )->select ()->toArray();
		$comms_arr = array();
		$roads_arr = array();
		foreach ( $comms as $comm ) {
			$kws = explode ( ",", $comm ['keywords'] );
			foreach ( $kws as $kw ) {
				$temp = array ();
				$temp ['id'] = $comm ['comm_id'];
				$temp ['keyword'] = $kw;
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