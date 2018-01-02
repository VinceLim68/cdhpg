<?php

namespace app\evalu\controller;

use think\Db;
use app\evalu\model\SalesModel;
use app\evalu\logic\MatchLogic;

class Sales extends Common {
	protected $db;
	protected $remoteDB;
	protected $matchNum;		//匹配成功的数量
	//protected $matchObj;
	
	protected function _initialize() {
		parent::_initialize ();
		$this->db = new SalesModel ();
		$this->remoteDB = Db::connect('remote_dbconfig');
		//$this->matchObj = new MatchLogic();
		//$this->matchObj->getCommsArr();
		//echo count($this->s);
		//echo '靠，这个也会每次都执行？';
		$controller = request()->controller();
		$module = request()->module();
		$act = strtolower($module.'/'.$controller);       //使用模块+控制器来验证
		$auth = new \Auth();
		// 		halt($act);
		if(!$auth->check($act,session('user.user_id'))){
		    $this->error(session('user.user_name').':'.$act.'你不能对挂牌数据进行操作');
		}
		
	}
	
	public function index() {
	/*
	 * 开始使用的直接用bootstrap展示挂牌信息
	 */
		// 取没有小区id的记录，用empty('community')就好，但不知如何用
		// $list = Db::table ( 'for_sale_property' )->where ( 'community_id', NULL )
		// ->whereor ( 'community_id', 0 )->paginate ( 100 );
		// 测试一下速度
		$list = $this->db->field ( 'details_url', true )->where ( '1=1' )->order ( [ 
				'id' => 'asc' 
		] )->paginate ( 500 );
		// var_dump($list);
		$this->assign ( 'list', $list );
		return $this->fetch ();
	}
	
	public function salesList() {
	/*
	 * 使用JGrid展示挂牌信息
	 */
		return $this->fetch ();
	}
	
	public function getSales() {
	/*
	 * 给jqgrid提供的挂牌信息
	 */
		$page = input ( 'page' ); // 第几页
		$limit = input ( 'rows' ); // 每页几条记录
		$sidx = input ( 'sidx' ); // 排序字段
		$sord = input ( 'sord' ); // 正序还是倒序，这两个字段是对数据表点击操作时自动产生的
		$action = input('action');//决定要取出什么数据
		$sordid = 'desc';//默认情况下用id倒序排列
		
		if (input ( 'keywords' )) {
			$keywords = input ( 'keywords' );
			$where = "community_name like '%" . $keywords . "%'";
		}else{
			$where = '1=1';
		}
		;
		
		if (! $sidx) {
			$sidx = 1;
		}
		
		$outputs = array ();
		
		if (!$action or $action=='all'){
			$list = $this->db->field ( 'details_url', true )->limit ( $limit )->page ( $page )
				->where ( $where )->order ( [ 'id' => $sordid ] )->select ()->toArray ();
		}elseif ($action == 'nomatch'){
			$list = $this->db->field ( 'details_url', true )->limit ( $limit )->page ( $page )
				->where ( 'community_id IS NULL or community_id = 0' )->where ( $where )
				->order ( [ 'id' => $sordid ] )->select ()->toArray ();
/* 			$list = $this->db->field ( 'details_url', true )->limit ( $limit )->page ( $page )
				->where ( $where )->where ( 'community_id', NULL )->whereor ( 'community_id', 0 )
				->order ( [ 'id' => $sordid ] )->select ()->toArray (); */
		}elseif ($action == 'mulmatch'){
			$list = $this->db->field ( 'details_url', true )->limit ( $limit )->page ( $page )
				->where ( $where )->where ( 'community_id', '>' ,0)->where ( 'community_id', '<' ,100)
				->order ( [ 'id' => $sordid ] )->select ()->toArray ();
		}
		$outputs['sql'] = $this->db->getLastSql();
		/*
		 * 返回值：total总页数,page当前页码,records总记录数,
		 * rows数据集,id每条记录的唯一id,cell具体每条记录的内容
		 */
		if (input ( 'dontCount' ) and input ( 'records' )) {
			// echo '不去读数据库';
			$outputs ['readfrommysql'] = 'false';
			$outputs ['records'] = input ( 'records' );
		} else {
			$outputs ['readfrommysql'] = 'true';
			if (!$action or $action=='all'){
				$outputs ['records'] = $this->db->where ( $where )->count('id');
			}elseif ($action == 'nomatch'){
				$outputs ['records'] = $this->db->where ( 'community_id IS NULL or community_id = 0' )
					->where ( $where )->count();
			}elseif ($action == 'mulmatch'){
				$outputs ['records'] = $this->db->where ( $where )->where ( 'community_id', '>' ,0)
				->where ( 'community_id', '<' ,100)->count();
			}
		}
		$outputs['lastsql'] = $this->db->getLastSql();
		// $total = ceil ( $records / $limit );
		$outputs ['page'] = $page;
		$outputs ['total'] = ceil ( $outputs ['records'] / $limit );
		$outputs ['rows'] = $list;
		
		return $outputs;
	}
	
	public function getUrlById(){
		/*
		 * 通过传入的id查询相应的details_url
		 */
		$byId = input('ID');
		$url = $this->db->field('details_url')->where('id',$byId)->find();
		//echo $url;
		return $url['details_url'];
	}
	
	public function match(){
		//用这个来测试综合匹配id功能
		$data = [
		    "id" => input('id'),
		    "title" => input("title"),
		    "community_name" => input("commName"),
		];
		$data['community_id'] = MatchLogic::matchID($data);
		return $data['community_id'] ;
		
	}
	
	public function text_len(){
		$arr = MatchLogic::getComms();
		$len = count($arr['comms']);
		//dump($arr);
		return $len;
	}
	
	public function matchComm(){
		/*
		 * 通过传入的id来匹配小区id,只匹配当前的一条记录
		 */
		$byId = input('id');
		$commName = input('commName');
		$title = input('title');
		$idArr = MatchLogic::getId($commName,$title );
		return ($idArr);
		
	}
	
	public function matchCommIDs(){
		/*
		 * 这是批量处理，匹配出CommID
		 */
		$this->matchNum = 0;
		$total = $this->db->where ( 'community_id', NULL )->whereor ( 'community_id', 0 )->count('id');
		$this->assign('noCommidNum',$total);
		return $this->fetch();
		
	}
	
	public function matchShipment(){
		/*
		 * 每匹次的执行代码，供ajax调用
		 */
		//循环做在这里
		//为了提高效率，把循环做到逻辑层里去了
		$shipmentNum = input('shipmentNum');
		$start = input('start');
		$matchlist = $this->db->field('id,title,community_name,community_id')->where('community_id',NULL)
		->whereor('community_id','<',999)->limit($start,$shipmentNum)->select();
		$this->matchNum += MatchLogic::matchIDs($matchlist);

		return $this->matchNum;
		
	}

	public function copyRemoteDbToLocal(){
		/*
		 * 把远程的数据库考贝到本地
		 */
		
		// limit(n,m)是从第n+1个数据开始取

		$remoteTotal = $this->remoteDB->table('for_sale_property')->count('id');
		$localTotal = $this->db->count('id');
		$this->assign('remoteTotal',$remoteTotal);
		$this->assign('localTotal',$localTotal);
		return $this->fetch();
		
	}
	
	public function copyShipment(){
		/*
		 * 一批次操作远程拷贝数据，用ajax
		 */
		$shipmentNum = input('shipmentNum');
		$start = input('start');
		$copyList = $this->remoteDB->table('for_sale_property')->limit($start,$shipmentNum)->select();
		$num = $this->db->insertAll($copyList);
		return $num;
	}
	
	public function has1(){
		$temp = [];
		dump(empty($temp));
		//dump(MatchLogic::getComms());
	}
	
	public function copyCommsToRemote(){
		//$copylist = $this->db->select();
		$this->remoteDB->execute("DROP TABLE IF EXISTS `comm`");
		$this->remoteDB->execute("CREATE TABLE `comm` (
				`Id` int(15) unsigned NOT NULL AUTO_INCREMENT,
				`comm_name` varchar(15) DEFAULT NULL,
				`comm_id` int(15) DEFAULT NULL,
				`region` varchar(10) DEFAULT NULL,
				`block` varchar(10) DEFAULT NULL,
				`comm_addr` varchar(100) DEFAULT NULL,
				`pri_level` tinyint(2) DEFAULT '0',
				`block_id` int(5) DEFAULT NULL,
				`keywords` varchar(200) DEFAULT NULL,
				PRIMARY KEY (`Id`),
				UNIQUE KEY `comm_id` (`comm_id`)
				) ENGINE=InnoDB AUTO_INCREMENT=1843 DEFAULT CHARSET=utf8");
		$copylist = Db::table('comm')->select();
		//dump($copylist);
		$this->remoteDB->table('comm')->insertAll($copylist);
		return $this->redirect('comms/commslist');
	}
	
	public function queryByComm(){
        $data = input();
// 	    if(request()->isPost()){
// 	        dump(input());
// 	    }
        if(!isset($data['where']) or $data['where']==''){
            $data['where'] = '';
        }
        if(!isset($data['order'])){
            $data['order'] = 'price';
        }
        if(!isset($data['set'])){
            $data['set'] = '';
        }
        dump($data);
        if('' == $data['set']){
            //查询记录
            $list = $this->db->field('id,title,community_id,community_name,price,total_floor,builded_year')
            ->where($data['where'] )
            ->order($data['order'])
            ->paginate(30,false,[
                'query'=>[
                    'where'=>  $data['where'],
                    'order'=>  $data['order'],
                    'set'=>  $data['set'],
                ],
            ]);
        }else{
            //修改记录
        }
	    $fields = Db::query('SHOW COLUMNS FROM for_sale_property');
// 	    $list = $this->db->field('id,title,community_id,community_name,price,total_floor,builded_year')
// 	           ->paginate(30,false,[
//                     'query'=>[
//                        'where'=>  $data['where'],
//                        'order'=>  $data['order'],
//                        'set'=>  $data['set'],
//                        ],
//                 ]);
        $title = ['序号','标题','小区','名称','单价','总层','建成'];
//         halt($list);
        $this->assign('list',$list);
        $this->assign('title',$title);
        $this->assign('fields',$fields);
        $this->assign('data',$data);
	    return $this->fetch();
	}
	
	
	

}