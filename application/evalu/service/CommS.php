<?php

namespace app\evalu\service;

use think\Model;

class CommS extends Model {
	/**
	 * 小区表的服务层：功能小模块
	 */
	public static function matchSearchComm($list, $search) {
		/**
		 * 在搜索框中输入一个小区名，从comm表中匹配出相应的记录
		 * $list:从comm里查出的记录
		 * $search:搜索框中输入的字符串
		 */
		$pickitem = array ();
		foreach ( $list as $item ) {
			// 先把关键字按,分开
			$keywords = explode ( ",", $item ['keywords'] );
			foreach ( $keywords as $kw ) {
				// 如果有辅助字的，需要忽略辅助字
				$k = explode ( "/", $kw );
				if (strlen ( $k [0] ) > strlen ( $search )) {
					$pos = strpos ( $k [0], $search );
				} else {
					$pos = strpos ( $search, $k [0] );
				}
				if ($pos !== false) {
					// 如果找到，push进pickitem，后面同一个记录的关键字不需要再判断了
					$pickitem [] = $item;
					break;
				}
			}
		}
		/* dump($pickitem); */
		return $pickitem;
	}
	public static function getCommsArr() {
		/**
		 * 把小区名按关键字拆分后形成一个数组
		 */
		$comms = self::field ( "comm_id,comm_name,pri_level,keywords" )->select ();
		$comms_arr = array ();
		foreach ( $comms as $comm ) {
			$kws = explode ( ",", $comm ['keywords'] );
			foreach ( $kws as $kw ) {
				$temp = array ();
				$temp [] = $comm ['comm_id'];
				$temp [] = $kw;
				$comms_arr [] = $temp;
			}
		}
		return $comms_arr;
	}
}