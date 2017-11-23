<?php

namespace app\evalu\logic;

use think\Model;
use app\evalu\model\Comm;
use app\evalu\service\CommS;

class CommL extends Model {
	/**
	 * comm的业务逻辑层：调用小功能模块来完成业务
	 */
	public static function macthSearch($search) {
		/**
		 * 返回匹配的小区名
		 */
		$list = Comm::getAll ();
		$pick = CommS::matchSearchComm ( $list, $search );
		return $pick;
	}
}
