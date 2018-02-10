<?php

namespace app\evalu\controller;

use app\evalu\model\Comm;
use think\Db;
use app\evalu\model\CommRelateModel;
use app\evalu\model\SalesModel;
use app\evalu\logic\PriceLogic;
use app\evalu\logic\MatchLogic;
use app\evalu\model\CommhistorypriceModel;

class Comms extends Common {
	protected $db;
	protected function _initialize() {
		parent::_initialize ();
		$this->db = new Comm ();
		if(!session('user.user_id'))
		{
		    // 			注意这里传递了参数，但是用url方式，接收时要使用$_GET
		    $this->redirect('evalu/login/login',['modulestr' => request()->module()]);
		}
		$controller = request()->controller();
		$module = request()->module();
		$act = strtolower($module.'/'.$controller);       //使用模块+控制器来验证
		$auth = new \Auth();
// 		halt($act);
		if(!$auth->check($act,session('user.user_id'))){
		    $this->error(session('user.user_name').':'.$act.'你不能对小区数据进行操作');
		}
	}
	
	/*
	 * 展示小区列表，它加载页面，页面初始化加载js,会调用getComms()
	 */
	public function commsList() {
		return $this->fetch ();
	}
	
	/*
	 * 测试
	 */
	public function test() {
// 		//$pattern = '/(.*市)?(.*区)?(.*[路道街巷里园])(\d+号(之[三二一四五六七八九十]*)?)(\d+)(室|单元)?/';
// 		//测试用正则取地址里的路名、栋号等
// 		$pattern = '/(.*市)?(.*区)?(\D*)(\d+号)(之[三二一四五六七八九十]*)?(\d+)(室|单元)?/';
// // 		$pattern = '/(.*u5e02)?(.*u533a)?(.*[u8defu9053u8857u5df7u91cc])(\d+u53f7(u4e4b[u4e09u4e8cu4e00u56dbu4e94u516du4e03u516bu4e5du5341u96f60]*)?)(\d+(u5ba4|u5355u5143))?/';
// 		$string = '湖里区梧桐里32号之十二202室住宅房地产抵押价值估价';
// 		$match = [];
// 		$result = preg_match($pattern,$string,$match);
// 		dump($match);
	   
	    $item = (new CommhistorypriceModel())->isDuplicate('12100131');
	    dump($item);
        
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
	
	public function ajaxGetCommName(){
	    if (request()->isGet()) {
//              dump(input());
	        $result = $this->validate ( input (), [
	            'commName' => 'require|max:25|min:2',
	        ],
	            [
	                'commName.require' => '请问您要查询哪个小区？',
	                'commName.max'     => '名称最多不能超过25个字符',
	                'commName.min'     => '名称最少要两个字',
	            ] );
	         
	        if (true !== $result) {
	            // 验证失败 输出错误信息
	            return $result;
	        } else {
	            //找出匹配的记录，每条记录的内容 是：comm_id,comm_name,pri_level,keywords
	            $commnames = MatchLogic::matchSearch(input('commName'));
                if(!$commnames){
                    //如果没有查到
                    return ('没有查询到叫"'.input('param.commName').'"的地方');
                }elseif(count($commnames)>1){
                    //4如果查到多个，列表展示，让用户手动挑选后，再转入统计模块
                    $commArr = [];      //取出完整的数据
                    $mystr = '<table class="table table-striped">';
                    $mystr .= '<tr><th>小区</th><th>ID</th><th>区块</th><th>版块</th><th>地址</th></tr>';
                    foreach ($commnames as $comm){
                        $v = Db::table('comm')->where('comm_id',$comm['comm_id'])->find();
                        $mystr .= '<tr>';
                        $mystr .= '<td><a href="'.url("handle_comm").'?community_id='.$v['comm_id'].'">'.$v['comm_name'].'</a></td>';
                        $mystr .= '<td>'.$v['comm_id'].'</td>';
                        $mystr .= '<td>'.$v['region'].'</td>';
                        $mystr .= '<td>'.$v['block'].'</td>';
                        $mystr .= '<td>'.$v['comm_addr'].'</td>';
                        $mystr .= '</tr>';
                        $commArr[] = $v;
                    }

                    $mystr .= '</table>';
                    return $mystr;
                }else{
                    //3如果只查到一个，直接返回comm_id
                    return $commnames[0];

                }
	        }
	   }
	}
	
	public function usagesplit(){
	    //小区根据功能拆分
	    //取近30天离散度最大的小区
	    $commid = Db::table('error_comm')
	       ->where("create_time", ">= time", strtotime('-30 day'))
	       ->where("type",2)
	       ->order("memo desc")
	       ->find();
	    $id = $commid['comm_id'];
	    //跳转到拆分模块去
	    
	    $this->redirect('handle_comm?community_id='.$id);
	    
	}
	
	
	public function handle_comm(){
	    //处理拆分小区的模块
        //如果是从异常记录跳转，这里传过来2个参数:community_id,commName
	    $data = input();
// 	    dump($data);
	    
        $commrelate = new CommRelateModel();
        if(isset($data['action']) and $data['action']==1){
            //如果是增加关联规则，先保存
            if(!$commrelate->where('community_id',$data['community_id'])->where('usage',$data['usage'])->find()){
                //如果没有相同community_id和用途的记录，才能追加
                // 过滤post数组中的非数据表字段数据
                $see = $commrelate->data($data)->allowField(true)->save();
                //dump($see);
            }
        }
	    
	    $rela_list = $commrelate->where('community_id',$data['community_id'])->select()->toArray();
	    if(!empty($rela_list) and !isset($data['usage']) and !isset($data['rela_comm_id'])){
	        //如果有数据，而且传递过来的数据非关联规则，则把查询出来的把第一条关联规则赋给$data
	        $data = $rela_list[0];
	    }
        $this->assign('rela_list',$rela_list);
	    //通过id找小区相关信息
	    $getComm = Db::table('comm')->where('comm_id',$data['community_id'])->find();
	    $rela_comms = $this->db->where('block_id',$getComm['block_id'])->select()->toArray();
	    $this->assign('rela_comms',$rela_comms);
	    //如果有关联小区，也取出来
	    if(isset($data['rela_comm_id']) and $data['rela_comm_id']>999){
	        $getComm['rela_comm'] = Db::table('comm')->where('comm_id',$data['rela_comm_id'])->value('comm_name');
	    }else{
	        $getComm['rela_comm'] = '';
	    }
	    $getComm['rela_ratio'] = isset($data['rela_ratio']) ? $data['rela_ratio'] : 1;
	    $getComm['usage'] = isset($data['usage'])? $data['usage']:'';
	    
	    
	    $result = SalesModel::getRecordsByCommid($data);
	    
	    if(count($result[1])>0){
	        //如果能查询出数据
	        $PL = new PriceLogic($result);
	        $getPrice_result = $PL->getStatic($getComm);
	        $this->assign('B',$getPrice_result);
	        $this->assign('result',$result);
	    }
	    
	    //取挂牌数据
	    if(!isset($data['where']) or trim($data['where'])==''){
	        $data['where'] = 'community_id = '.$data['community_id'];
	    }else{
	        $data['where'] .= ' AND community_id = '.$data['community_id'];
	    }
// 	    $data = $this->datahandle($data);
// 	    $saleslist = $this->getSales($data);
	    $data = action('Sales/datahandle',  ['data' => $data]);
	    $saleslist = action('Sales/getSalesByArray',  ['data' => $data]);
	    
	    $fields = Db::query('SHOW COLUMNS FROM for_sale_property');
	    $title = ['序号','标题','小区','名称','单价','总层','建成'];
	    $this->assign('saleslist',$saleslist);
	    $this->assign('title',$title);
	    $this->assign('fields',$fields);
	    $this->assign('data',$data);
// 	    dump($data);
	    
	    return $this->fetch();
	        
	}
	
	public function ajaxGetSaleslist(){
	    $data = input();
	    $data1 = action('Sales/datahandle',  ['data' => $data]);
	    $saleslist = action('Sales/getSalesByArray',  ['data' => $data1]);
        $response['page'] = $saleslist->render();
        $response['total'] = $saleslist->total();
	    $liststring = '';
	    foreach ($saleslist as $v){
	        $liststring .= '<tr>';
	        $liststring .= '<td>'.$v['id'].'</td>';
	        $liststring .= '<td>'.$v['title'].'</td>';
	        $liststring .= '<td>'.$v['community_id'].'</td>';
	        $liststring .= '<td>'.$v['community_name'].'</td>';
	        $liststring .= '<td>'.$v['price'].'</td>';
	        $liststring .= '<td>'.$v['total_floor'].'</td>';
	        $liststring .= '<td>'.$v['builded_year'].'</td>';
	        $liststring .= '</tr>';
	    }
	    $response['items'] = $liststring;
	    return $response;
	}
	
// 	private function datahandle($data){
// 	    //对输入的数组进行处理，赋默认值
// 	    $replace = array('“'=>'"');
// 	    $replace += array('”' => '"');
// 	    $replace += array("'" => '"');
// 	    $replace += array("‘" => '"');
// 	    $replace += array("’" => '"');
// 	    if(!isset($data['where']) or trim($data['where'])==''){
// 	        $data['where'] = '';
// 	    }else{
// 	        $data['where'] = strtr($data['where'],$replace);
// 	    }
// 	    if(!isset($data['order']) or trim($data['order'])==''){
// 	        $data['order'] = 'price';
// 	    }else{
// 	        $data['order'] = strtr($data['order'],$replace);
// 	    }
// 	    if(!isset($data['set']) or trim($data['set'])==''){
// 	        $data['set'] = '';
// 	    }
// 	    return $data;
// 	}
	
// 	private function getSales($data){
	     
// 	    if('' !== $data['set']){
// 	        //修改记录
// 	        $sqlstr = 'UPDATE for_sale_property SET '.$data['set'].' WHERE '.$data['where'];
// 	        $data['num'] = Db::execute($sqlstr);
// 	    }
// 	    //查询记录,无论是否修改，都需要查询
// 	    //echo ($data['order']);
// 	    $sales = Db::table('for_sale_property')->field('id,title,community_id,community_name,price,total_floor,builded_year')
// 	    ->where($data['where'] )
// 	    ->order($data['order'])
// 	    ->paginate(100,false,[
// 	        'query'=>[
// 	            'where'=>  $data['where'],
// 	            'order'=>  $data['order'],
// 	            'set'=>  $data['set'],
// 	            'community_id' =>  $data['community_id'],
// 	        ],
// 	    ]);
	    
// 	    return $sales;
// 	}
	
// 	public function chooseChild($rela_list){
// 	    $this->assign('rela_list',$rela_list);
// 	    return $this->fetch('chooseChild');
// 	}
	
	public function ajaxGetRelaById(){
	    //以ajax方式来生成一个关联规则的页面
	    //如果传过来的是关联规则的id
	    $isADD = false;         //是否增加记录
	    if(null !== input('id')){
	        //如果带着关联规则的id过来，就是修改
    	    $myid = input('id');
    	    $rela = (new CommRelateModel())->find($myid)->toArray();
    	    $title = '序号：<span>'.$rela['id'].'</span>   ('.$rela['create_time'].')';
	    }elseif (null !== input('community_id')){
	        //如果没有关联id,但是有小区id,属于增加
	        $isADD = TRUE;
	        $rela['community_id'] = input('community_id');
	        $rela['id'] = '';
	        $rela['create_time'] = '';
	        $rela['usage'] = '';
	        $rela['rela_comm_id'] = '';
	        $rela['rela_ratio'] = 1;
	        $rela['rela_weight'] = 1;
	        $rela['where'] = '';
	        $rela['memo'] = '';
	        $title = '新增';
	    }

	    $getComm = $this->db->where('comm_id',$rela['community_id'])->find();//->toArray();
	    $rela_comms = $this->db->where('block_id',$getComm->block_id)->select()->toArray();
	    $fields = Db::query('SHOW COLUMNS FROM for_sale_property');
	    $usage_list = (new CommRelateModel())->getDistUsage();
        
        $mystr = '';
        
        $mystr .= '<div class="form-group"><label class="col-md-2 control-label left">小区</label>';
        $mystr .= '<div class="col-md-5 right"><input type="text" name="community_id" value="'.$rela['community_id'].'"></div>';
        $mystr .= '<input type="hidden" name="action" value="'.$isADD.'">';
        $mystr .= '<div class="col-md-4 left"><p class="form-control-static">'.$getComm->comm_name.'</p></div></div>';
        
        $mystr .= '<div class="form-group"><label class="col-md-2 control-label left">分类功能</label>';
        $mystr .= '<div class="col-md-5 right"><input type="text" name="usage" value="'.$rela['usage'].'"></div>';
        $mystr .= '<div class="col-md-4 left">';
        $mystr .= '<select id="usage_select">';
        foreach ($usage_list as $usage_item){
            $mystr .= '<option value = "'.$usage_item['usage'].'">'.$usage_item['usage'].'</option>';
        }
        $mystr .= '</select>';
        $mystr .= '</div></div>';
        
        $mystr .= '<div class="form-group"><label class="col-md-2 control-label left">分类说明</label>';
        $mystr .= '<div class="col-md-9 right"><input type="text" name="memo" value="'.$rela['memo'].'"></div></div>';
        
        $mystr .= '<div class="form-group"><label class="col-md-2 control-label left">关联小区</label>';
        $mystr .= '<div class="col-md-5 right"><input type="text" name="rela_comm_id" value="'.$rela['rela_comm_id'].'"></div>';
        $mystr .= '<div class="col-md-4 left">';
        $mystr .= '<select id="rela_c">';
    	foreach ($rela_comms as $rela_comm){
            $mystr .= '<option value = "'.$rela_comm["comm_id"].'">'.$rela_comm["comm_name"].'</option>';
        }
        $mystr .= '</select>';
        $mystr .= '</div></div>';
        
        $mystr .= '<div class="form-group"><label class="col-md-2 control-label left">关联系数</label>';
        $mystr .= '<div class="col-md-5 right"><input type="text" name="rela_ratio" value="'.$rela['rela_ratio'].'"></div>';
        $mystr .= '<div class="col-md-2 left"><p class="form-control-static">权重</p></div>';//'<label class="col-md-2 control-label left">权重</label>';
        $mystr .= '<div class="col-md-2 right"><input type="text" name="rela_weight" value="'.$rela['rela_weight'].'"></div></div>';
        
        $mystr .= '<div class="form-group"><label class="col-md-2 control-label left">过滤条件</label>';
        $mystr .= '<div class="col-md-5 left"><select id="sele_field">';
        foreach ($fields as $field){
            $mystr .= '<option value = "'.$field['Field'].'">'.$field['Field'].'</option>';
        }
        $mystr .= '</select></div></div>';
        
        $mystr .= '<div class="form-group"><div class="col-md-9 col-md-offset-2">
            <textarea class="form-control" rows="2" name="where" >'.$rela['where'].'</textarea></div>';
        $mystr .= '</div>';
        $mystr .= '<div class="form-group">';
        
        if(!$isADD){
            $mystr .= '<div class="col-md-3 col-xs-3 " ><button type="button" style="margin-left:12px;" class="btn btn-success" id="modi_rela">修改关联规则</button></div>';
            $mystr .= '<div class="col-md-3 col-xs-3 "><button type="button" style="margin-left:10px;" class="btn btn-success" id="del_rela">删除关联规则</button></div>';
            $mystr .= '<div class="col-md-3 col-xs-3 "><button type="submit" style="margin-left:10px;" class="btn btn-success" id="refresh_data">更新数据</button></div>';
        }else{
            $mystr .= '<div class="col-md-3 col-xs-3 "><button type="submit" style="margin-left:10px;" class="btn btn-success" id="refresh_data">增加规则</button></div>';
        }
        $mystr .= '<div class="col-md-3 col-xs-3 "><button type="button" class="btn btn-success" data-dismiss="modal">取消</button></div>';
        $mystr .= '</div>';
        
        
        
        $res[] = $title;
        $res[] = $mystr;
	    return $res;
	}
	
	public function modifyRelation(){
	    $data = input();
// 	    halt($data);
	    
	    $commrelate = new CommRelateModel();
	    // 过滤post数组中的非数据表字段数据
	    $res = $commrelate->allowField(true)->save($data,['id' => $data['rela_id']]);
	    return $res;
	}
	
	public function delRelation(){
	    $data = input();
	    $commRelate = new CommRelateModel();
	    $rela = $commRelate->find($data['rela_id'])->toArray();
	    $res['num'] = CommRelateModel::destroy($data['rela_id']);
	    $relation = $commRelate->where('community_id',$rela['community_id'])->select();
// 	    halt($data);
	    $mystr = '<tr><th>序号</th><th>小区ID</th><th>功能</th><th>关联小区ID</th><th>关联系数</th><th>权重</th><th>过滤条件</th><th>设立时间</th></tr>';
	    foreach ($relation as $v){
	        $mystr .= '<tr>
            	    <td>'.$v["id"].'</td>
            	    <td>'.$v["community_id"].'</td>
            	    <td>'.$v["usage"].'</td>
            	    <td>'.$v["rela_comm_id"].'</td>
            	    <td>'.$v["rela_ratio"].'</td>
            	    <td>'.$v["rela_weight"].'</td>
            	    <td>'.$v["where"].'</td>
            	    <td>'.$v["create_time"].'</td>
            	    </tr>';
	    }
        $res['str'] = $mystr;
        //halt($res);
	    return $res;
	}
	
	public function calPriceIndex(){
	    //批量生成历史价格指数
	    ignore_user_abort(true); // 后台运行
	    error_reporting(0);
	    set_time_limit(0);
	    
	    $buffer = ini_get('output_buffering');
	    echo str_repeat(' ',$buffer+1);
	    ob_end_flush();

	    $comms = Comm::with('commrelate')->select()->toArray();
	    //有用的字段是comm_id，关联中的rela_comm_id，where，rela_ratio，rela_weight,usage,rela_id(这个rela的id)
	    $datas = [];       //存放需要计算基价的列表（小区，及小区内的功能拆分）
	    foreach ($comms as $comm){
	        $data = [];
            $data['community_id'] = $comm['comm_id'];
            $datas[] = $data;           //每个小区都要计算，当作“未分类基价”
            if(!empty($comm['commrelate'])){
                //如果小区还有关联规则，再对每个小区规则进行计算，但“未分类基价”不用重复了
	            foreach ($comm['commrelate'] as $relationship){
	                if($relationship['usage'] != '未分类基价'){
	                    //未分类基价是每个小区都要计算的，所以这里不再重复计算
    	                $data = [];
                        $data['community_id'] = $comm['comm_id'];
    	                $data['rela_comm_id'] = $relationship['rela_comm_id'];
    	                $data['where'] = $relationship['where'];
    	                $data['rela_ratio'] = $relationship['rela_ratio'];
    	                $data['rela_weight'] = $relationship['rela_weight'];
    	                $data['usage'] = $relationship['usage'];
    	                $data['rela_id'] = $relationship['id'];
    	                $datas[] = $data;
	                }
	            }
            }
	    }
	    //批量生成价格指数
	    $i = 0;
	    foreach ($datas as $item){
            $i += 1;
            echo '-------------------------'.$i.'-------------------------</br>';
            $priceIndex = new CommhistorypriceModel;
            if($priceIndex->isDuplicate($item['community_id'])){
                //判断是否已经计算过当月基价
                echo '====== 数据重复,不再重复  =====';
            }else{
                $getPrice_result = $this->cal($item);
                (new CommhistorypriceModel($getPrice_result))->allowField(true)->save();
                dump($getPrice_result);
            }
            flush();
	    }
	    ignore_user_abort(false); // 解除后台运行
	}
	
	private function cal($item){
	    $result = SalesModel::getRecordsByCommid($item);
	    if($result[1]){
	        //如果有数据就计算价格指数
    	    $getPrice_result = (new PriceLogic($result))->calPriceIndex();
    	    $result = array_merge ( $item,$getPrice_result);
	    }else{
	        $result = $item;
	    }
	    return $result;
	}
	
	public function managePriceIndex(){
	    //基价管理，基价的搜索、排序（按个数、按偏离度、按价格、按涨跌幅）->field('id,usage,mortgagePrice,dealPrice,len,ori_len,std_r,create_time')

	    $data = input();
// 	    dump($data);
	    $data = action('Sales/datahandle',  ['data' => $data]);
	    //原来是用price做为order的默认值，这里要改成community_id
	    if($data['order'] == 'price'){
	        $data['order'] = 'community_id';
	    }
	    $order = $data['order'].' '.$data['sort'];
	    if('' !== $data['set'] and '' != $data['where']){
	        //修改记录
	        $sqlstr = 'UPDATE commhistoryprice SET '.$data['set'].' WHERE '.$data['where'];
	        $data['num'] = Db::execute($sqlstr);
	    }
	    
// 	        dump($data);
	    //查询记录,无论是否修改，都需要查询
	    if( isset($data['block_id']) and ('' == $data['where']) ){
	        //如果给了区块id，就只查询区块id,如果有where值，就清除区块查询
    	    $list = Db::view('commhistoryprice','id,community_id,usage,create_time,median,mean,mortgagePrice,dealPrice,len,ori_len,std_r')
    	    ->view('comm','comm_name,block,comm_addr,block_id','comm.comm_id=commhistoryprice.community_id')
    	    ->where('block_id',$data['block_id'])
    	    ->order($order)
    	    ->paginate(100,false,[
    	        'query'=>[
    	            'block_id'=> $data['block_id'],
    	            'order'=>  $data['order'],
    	        ],
    	    ]);
	    }else{
	        //否则正常查询
    	    $HPrice = new CommhistorypriceModel();
    	    $list = $HPrice->with('comm')
    	    ->where($data['where'] )
    	    ->order($order)
    	    ->paginate(100,false,[
    	        'query'=>[
    	            'where'=>  $data['where'],
    	            'order'=>  $data['order'],
    	            'set'=>  $data['set'],
    	        ],
    	    ]);
	    }
	    $title = ['序号','小区','区块','分类','均价','抵押价值','二手价值','数据量','原始数据','标准差','计算时间'];
	    $fields = Db::query('SHOW COLUMNS FROM commhistoryprice');
	    $this->assign('title',$title);
	    $this->assign('list',$list);
	    $this->assign('fields',$fields);
	    $this->assign('data',$data);
	    return $this->fetch();
	    
	}
	
// 	public function getPriceIndexByBlock(){
// 	    dump(input('block_id'));
// 	    $this->redirect('managePriceIndex', ['block_id' => input('block_id')]);
// // 	    dump($list);
// 	}
	
	public function ajaxGetCommById(){
	    //通过ajax取得鼠标点击小区的详细信息
	    $record = (new CommhistorypriceModel())->with('comm')->find(input('ID'))->toArray();
	    $html = '<ul class="list-group">';
	    foreach ($record['comm'] as $k => $v){
	        $html .= '<li class="list-group-item col-md-6">'.$k.' : '.$v.'</li>';
	    }
	    $html .= '</ul>';
	    return $html;
	}
	public function ajaxGetRelationById(){
	    //通过ajax取得鼠标点击的详细关联规则信息
	    $record = (new CommhistorypriceModel())->with('relation')->find(input('ID'))->toArray();
	    $html = '<ul class="list-group">';
	    if($record['relation']){
    	    foreach ($record['relation'] as $k => $v){
    	        $html .= '<li class="list-group-item col-md-6">'.$k.' : '.$v.'</li>';
    	    }
	    }else{
	        $html .= '<li class="list-group-item col-md-12">没有关联规则</li>';
	    }
	    $html .= '</ul>';
	    return $html;
	}
	
	public function ajaxGetPriceIndexById(){
	    //通过ajax取得鼠标点击小区基价的详细信息
	    $HPrice = new CommhistorypriceModel();
	    $record = $HPrice->find(input('ID'))->toArray();
	    $html = '<ul class="list-group">';
	    
	    foreach ($record as $k => $v){
            $html .= '<li class="list-group-item col-md-6">'.$k.' : '.$v.'</li>';
	    }
	    $html .= '</ul>';
	    return $html;
	}
	
	public function ajaxGetScatter(){
	    //ajax动态修改散点图
	    $data = input();
	    if('' == trim($data['times'])){
	        $data['times'] = 0;
	    }
	    $item = explode('_',$data['this_btn']);
	    $result = SalesModel::getRecordsByCommid($data);
	     
	    if(count($result[1])>0){
	        //如果能查询出数据
	        $PL = new PriceLogic($result);
// 	        $dots = $PL->scatter($item[2], $item[1],$data['times']);
	        
	        //=======================
            $PL->arr = $PL->firstClearData();
            $PL->price = array_column ($PL->arr, 'price' );
            $PL->arr = $PL->secondClearData();
            if( 'area' == $item[1] ){
                $actionitem = 'area';
            }elseif('floor' == $item[1]){
                $actionitem = 'total_floor';
            }elseif('builded' == $item[1]){
                $actionitem = 'builded_year';
            }
	        $dots = $PL->scatter('price', $actionitem ,$data['times']);

	        //=========================
	        $html = '<svg xmlns="http://www.w3.org/2000/svg" version="1.1" width="100%" height="100%">';
	        foreach( $dots['A_line'] as $vo){
	           $html .= '<line x1="'.$vo['x0'].'%" y1="'.$vo['y0'].'%" x2="'.$vo['x1'].'%" y2="'.$vo['y1'].'%"  class="scatter_line" />';    
	           $html .= '<text x="'.$vo['t_x'].'%" y="'.$vo['t_y'].'%" class="'; 
	           if($vo['t_x'] == 0){
	               $html .= 'scatter_text_y';
	           }else{
	               $html .= 'scatter_text_x';
	           };
	           $html .= ' scatter_text" >'.$vo['val'].'</text>';
	        }
	        foreach($dots['A'] as $vo){
	            $html .= '<circle cx="'.$vo['x'].'%" cy="'.$vo['y'].'%" class = "scatter_circle" r="1%"/>';
	        }
            $html .= '<line x1="'.$dots['axes'][0]['x0'].'%" y1="'.$dots['axes'][0]['y0'].'%" x2="'.$dots['axes'][0]['x1'].'%" y2="'.$dots['axes'][0]['y1'].'%"  class="scatter_axes" />';    
            $html .= '<text x="'.$dots['axes'][0]['t_x'].'%" y="'.$dots['axes'][0]['t_y'].'%"  class="XAxesText">'.$dots['axes'][0]['val'].'</text>';    
            $html .= '<line x1="'.$dots['axes'][1]['x0'].'%" y1="'.$dots['axes'][1]['y0'].'%" x2="'.$dots['axes'][1]['x1'].'%" y2="'.$dots['axes'][1]['y1'].'%"  class="scatter_axes" />';    
            $html .= '<text x="'.$dots['axes'][1]['t_x'].'%" y="'.$dots['axes'][1]['t_y'].'%" class="YAxesText">'.$dots['axes'][1]['val'].'</text></svg>';    
	        
            return $html;
	    }
	    
	}
}