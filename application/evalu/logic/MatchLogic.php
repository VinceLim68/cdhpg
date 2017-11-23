<?php
namespace app\evalu\logic;

use app\evalu\model\Comm;
use think\Cache;
use think\Db;

class MatchLogic {
	/*
	 * 这是通过小区名称来匹配出小区id的对象,原本想使用单例设计，但发现thinkphp5每执行一次，都会重新加载，没意义
	 * 现在改成写入缓存来提高性能
	 */
	private static $comms = [];
	
	//返回小区名称列表
	static public function getComms(){
		if (empty(self::$comms)){		
			if(!Cache::get('commNames')){
				Cache::set('commNames',Comm::getCommsArr(),7200);
				echo '从数据库中查询';
			}
			self::$comms = Cache::get('commNames');
		}

	}
	


	
	//静态变量保存全局实例
// 	private static $_instance = null;
	
	//私有构造函数，防止外界实例化对象
	//private function __construct() {
// 	public  function __construct() {
// 		//得到小区列表。$comms['comms']是小区名称,$comms['roads']是道路名称
// 		self::$comms = Comm::getCommsArr();
// 		echo 'build $comms';
// 	}
	
// 	//私有克隆函数，防止外办克隆对象
// 	private function __clone() {
// 	}
	
// 	//静态方法，单例统一访问入口
// 	static public function getInstance() {
// 		if (is_null ( self::$_instance ) || isset ( self::$_instance )) {
// 			self::$_iself::mul_handletance = new self ();
// 		}
// 		return self::$_instance;
// 	}
	
	static public function getId($commName,$title='',$type = 'comms'){
		//匹配单个小区的方法，这里可能匹配出多个id

		$find = array(".","。","．");
		$replace = array("");
		$commName = strtoupper(str_replace($find,$replace,$commName));
		
		// 		 储存匹配出来的id列表，可能不止匹配一个id值，每个元素包含（开始位置，关键字，id)
		$matchId = array();		
		self::getComms();
		//foreach ($comms[$type] as $commItem){
		foreach (self::$comms[$type] as $commItem){

			// 			$commItem['keyword']中可能通过'/'带着辅助字，先拆分开来，真正的key是它的第一个元素
			$key = explode ( "/", $commItem ['keyword'] );

			// 			在小区名称中查找关键字
			$start = stripos($commName,$key[0]);
			// 			必须使用 !== false 的方式，否则会把位置0 当成没找到
			if($start !== FALSE) {
				$len = count($key);
				if($len>1){
					$temp = $commName . $title;
					for( $j = 1; $j < $len; $j++ ) {
						if (stripos($temp, strtoupper($key[$j])) !== FALSE ){
							$t = array($start,$key[0],$commItem['id']);
							$matchId[] = $t;
							break;
						}
					}
				}else{
					$t = array($start,$key[0],$commItem['id']);
					$matchId[] = $t;
				}
			}
		}
		return $matchId;
		
	}
	
	static public function matchID($data){
		//这是最后匹配id的控制器，前面getid可能匹配出多个id,或者没有匹配出id,在这里进行进一步的处理
		$getid = self::getId($data['community_name'],$data['title'],"comms");
		$id = 0;
		if(count($getid) == 1) {
			#如果匹配到唯一id
			$id = $getid[0][2];
		}elseif(count($getid) == 0) {
			#如果没匹配到comm，就看看按road是否能匹配
			$getroad = self::getId($data['community_name'],$data['title'],"roads");
			if(count($getroad)==1){
				#匹配到唯一road
				$id = $getroad[0][2];
			}elseif (count($getroad)==0){
				#如果连road也没匹配成功，空在那里
				$id = 0;
			}elseif (count($getroad) > 1){
				#如果匹配到不止一个road,进行处理
				$id = self::mul_handle($getroad);
			}
		}elseif (count($getid) > 1){
			#如果comm匹配到不止一个，进行处理
			$id = self::mul_handle($getid);
		}
		return $id; 			//这里可能有三种情况，1、成功匹配：返回id；2、多个区配：返回匹配的个数;3、匹配失败：0
	}
	
	static private function mul_handle($ids){
		//当匹配到多个记录时进行判断
		//处理的原则是以起始字段在前的为准，如果起始字段相同，则以字符串长的为准， 如果起始与字串长度都一样，则人工判断
        $flag = False;                            #标志位，如果能解析出唯一id,则标志位设成ture
        
        // $ids[0]起始位置，$ids[1]匹配成功的关键字，$ids[2]comm_id，先按照$ids[0]排序
        $sortby = array();
        foreach ($ids as $iditem) {
        	$sortby[] = $iditem[0];
        }
        array_multisort($sortby, SORT_ASC, $ids);

		//起始位置最小的getid
		$first = $ids[0];
		$total = count($ids);
		for ($i = 1; $i < $total; $i++) {
			if($ids[$i][0] > $first[0]){
				//如果第二个匹配到的关键字起始位置大于第一个，就以第一个为准，不用再匹配了
				$flag = false;
				break;
			}else{
				if (strlen($ids[$i][1]) > strlen($first[1])){
					//如果有并列第一,则关键字串长的优先
					$flag = false;
					$first = $ids[$i];
					break;
				}else {
					//关键字起始位置、关键字串长相同的，有的时候是找同一个小区id
					if($ids[$i][2]==$first[2]){
						$flag = false;
						break;
					}else{
						//实在不行，标志位设成ture,要人工判断一下
						$flag = True;
					}
				}
			}
		}
		if($flag){
			return $total;			//返回匹配了几个小区的数量
		}else{
			return $first[2];		//返回匹配成功的id
		}

	}
	
	public static function matchIDs($matchlist){
		/*
		 * 为了提高性能，把批量匹配小区id的功能放入本模块，减少频繁调用，看看能否提高性能
		 */
		$num = 0;
		foreach($matchlist as $matchitem){
			$matchitem['community_id'] = self::matchID($matchitem);
			if($matchitem['community_id']>999){
				//匹配成功，计数
				$num += 1;
				Db::table('for_sale_property')->where('id',$matchitem['id'])->setField('community_id', $matchitem['community_id']);
// 				$this->db->where('id',$matchitem['id'])->setField('community_id', $matchitem['community_id']);
			}
		}
		return $num;
	}


}