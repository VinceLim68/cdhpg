<?php

namespace app\evalu\controller;

use app\evalu\model\Comm;
use think\Db;

class Comms extends Common {
	protected $db;
	protected function _initialize() {
		parent::_initialize ();
		$this->db = new Comm ();
	}
	
	/*
	 * 展示小区列表，它加载页面，页面初始化加载js,会调用getComms()
	 */
	public function commsList() {
		return $this->fetch ();
	}
	
	/*
	 * 测试:原始的ace的jgrid页面
	 */
	public function testlist() {
		return $this->fetch ();
	}
	
	/**
	 * 用jqgrid获取的小区列表
	 */
	public function getComms() {
		$page = input ( 'page' ); // 第几页
		$limit = input ( 'rows' ); // 每页几条记录
		$sidx = input ( 'sidx' ); // 排序字段
		$sord = input ( 'sord' ); // 正序还是倒序
		
		if (! $sidx)
			$sidx = 1;
		$outputs = array ();
		
		$where = '1=1';
		
		if (input ( 'keywords' )) {
			$keywords = input ( 'keywords' );
			$where .= " and (keywords like '%" . $keywords . "%' or comm_name like '%" . $keywords . "%')";
		}
		;
		if (input ( 'region' )) {
			$region = input ( 'region' );
			$where .= " and region like '%" . $region . "%'";
		}
		;
		if (input ( 'block' )) {
			$block = input ( 'block' );
			$where .= " and block like '%" . $block . "%'";
		}
		;
		if (input ( 'address' )) {
			$address = input ( 'address' );
			$where .= " and comm_addr like '%" . $address . "%'";
		}
		;
		
		$sql_count = "SELECT COUNT(*) AS count FROM comm where " . $where;
		$records = $this->db->query ( $sql_count );
		$total = ceil ( $records [0] ['count'] / $limit );
		$list = $this->db->limit ( $limit )->page ( $page )->where ( $where )->order ( [ 
				'Id' => $sord 
		] )->select ()->toArray ();
		/*
		 * 返回值：total总页数,page当前页码,records总记录数,
		 * rows数据集,id每条记录的唯一id,cell具体每条记录的内容
		 */
		$outputs ['total'] = $total;
		$outputs ['page'] = $page;
		$outputs ['records'] = $records [0] ['count'];
		$outputs ['rows'] = $list;
		
		return $outputs;
	}
	
	/*
	 * 增加、修改、删除小区记录
	 */
	public function editComm() {
		$vars = input ( '' );
		if ($vars ['oper'] == 'add') { // 追加记录
		  // 1.判断传入的小区名称是否已经存在，或者在关键字中存在。
			$where = " (keywords like '%" . $vars ['comm_name'] . "%' or comm_name like '%" . $vars ['comm_name'] . "%')";
			$sql_count = "SELECT COUNT(*) AS count FROM comm where " . $where;
			$records = $this->db->query ( $sql_count );
			$count = $records [0] ['count'];
			if ($count >= 1) {
				return 1;
			} else {
				// 2.判断传入的区块名称是否已经存在，如果存在，取出相应的block_id，如果不存在，需要生成新的block_id
				$b_id = $this->db->field ( 'block_id' )->where ( 'block', $vars ['block'] )->where ( 'region', $vars ['region'] )->find ();
				if ($b_id) {
					$vars ['block_id'] = $b_id ['block_id'];
				} else {
					$vars ['block_id'] = $this->get_newblockid ( $vars ['region'] );
				}
				// 3.取得新的comm_id
				$vars ['comm_id'] = $this->get_newcommkid ( $vars ['block_id'] );
				// 4.保存新记录
				$this->db->allowField ( true )->data ( $vars )->save ();
			}
		} elseif ($vars ['oper'] == 'edit') { // 修改记录
			$this->db->allowField ( true )->update ( $vars );
		} elseif ($vars ['oper'] == 'del') { // 删除记录
			$del_id = explode ( ',', $vars ['id'] );
			foreach ( $del_id as $myid ) {
// 				echo $myid;
				$comm_id = $this->db->field('comm_id')->where ( 'Id', $myid )->find();
				$this->db->where ( 'Id', $myid )->delete ();
				//删除之后还要把挂牌信息里相关的commid改成0
				Db::table('for_sale_property')->where('community_id',$comm_id['comm_id'])->update(['community_id'=>0]);
			}
		}
	}
	
	/**
	 * 小区列表
	 */
	public function index() {
		// $list = Db::table('comm')->paginate(100);
		$list = $this->db->paginate ( 100 );
		$this->assign ( 'list', $list );
		return $this->fetch ();
	}
	public function matchid() {
		/*
		 * $comm = $this->db->select();
		 * halt($comm);
		 */
		$this->db->getCommsArr ();
	}
	
	/*
	 * 获取片区region列表
	 */
	public function getRegion() {
		$regions = $this->db->getRegions ();
		$regi = "";
		foreach ( $regions as $region ) {
			foreach ( $region as $key => $value ) {
				$regi .= '<option value="' . $value . '">' . $value;
			}
			;
		}
		;
		// 这里如果用return,tp5会自动加上转义字符，形成如
		// <option value="\"海沧\"" role="option">海沧</option>之类的
		// 这些加到$("#ABC").html($regi)去没问题
		// 但如果直接使用就错了
		// 用echo可以原文返回
		echo $regi;
		// return $regi;
	}
	
	/*
	 * 根据不同的片区名获得板块列表
	 */
	public function getBlock() {
		$region = input ( 'reg' );
		$region = str_replace ( '\"', '', $region );
		
		$blocks = $this->db->getBlockByRegion ( $region );
		$blos = "";
		foreach ( $blocks as $block ) {
			foreach ( $block as $k => $v ) {
				$blos .= '<option value="' . $v . '" >';
			}
			;
		}
		;
		return $blos;
	}
	private function get_newblockid($region) {
		/* 根据指定的region，取出新的block的id值 */
		// 1、先把指定region的所有block的id取出放到一个数组中去
		$new_blocks_id = $this->db->distinct ( true )->field ( 'block_id' )->where ( 'region', $region )->select ()->toArray ();
		// 2、取出block_id的前两位，代表region的id
		$head = substr ( $new_blocks_id [0] ['block_id'], 0, 2 ) * 100;
		// 3、循环生成新的id
		$i = 1;
		while ( 1 ) {
			$newid ['block_id'] = $head + $i; // $head+后两位，待验证是否未被使用
			if (! in_array ( $newid, $new_blocks_id )) {
				break; // 如果没被占用，则返回，这就是新的block_id
			}
			;
			++ $i;
		}
		;
		return $newid ['block_id'];
	}
	private function get_newcommkid($block_id) {
		/* 根据block_id生成新的comm_id */
		// 先把指定block的所有comm的id取出放到一个数组中去
		$new_comms_id = $this->db->field ( 'comm_id' )->where ( 'block_id', $block_id )->select ()->column ( 'comm_id' );
		// var_dump($new_comms_id);
		if (count ( $new_comms_id ) > 0) {
			// 循环生成新的id
			$i = 1;
			while ( 1 ) {
				$newid = $block_id * 1000 + $i; // $head+后两位，待验证是否未被使用
				if (! in_array ( $newid, $new_comms_id )) {
					break; // 如果没被占用，则返回，这就是新的block_id
				}
				;
				++ $i;
			}
			;
		} else {
			// 如果是一个新区块，没有查到过去有相同的区块小区，则从1号开始编写
			$newid = $block_id * 1000 + 1;
		}
		return $newid;
	}
}