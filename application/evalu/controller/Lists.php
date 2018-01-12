<?php

namespace app\evalu\controller;


use app\phone\model\AntiModel;

class Lists extends Common {
	protected function _initialize() {
		parent::_initialize ();
	}
	
	public function anti_list(){
	    $mydb = new AntiModel();
	    $data = input();
	    if(!isset($data['search_days']) or $data['search_days']==''){
	        $data['search_days'] = 30;
	    }
	    if(!isset($data['search_type'])){
	        $data['search_type'] = 'all';
	    }
	    if(input ( 'search_type' ) and input('search_type')=='ip'){
	        $list = $mydb
               ->field('id,ip,count(id) as times,create_time,zid')
    	        ->where("create_time", ">= time", strtotime('-'.input('search_days').' day'))
    	        ->group('ip')
    	        ->order('create_time desc')
    	        ->paginate(30,false,[
    	            'query'=>[
    	                'search_days'=>  input('search_days'),
    	                'search_type'=>  input('search_type'),],
    	            'type'     => 'bootstrap',
    	            'var_page' => 'page',
    	        ]);
	        //         halt($list);
	        $title = ['序号','ip','次数','时间','查询报告'];
	    }else{
    	    $list = $mydb
        	    ->where("create_time", ">= time", strtotime('-'.input('search_days').' day'))
        	    ->order('create_time desc')
        	    ->paginate(30,false,[
        	        'query'=>[
        	            'search_days'=>  input('search_days'),
        	            'search_type'=>  input('search_type'),],
        	        'type'     => 'bootstrap',
        	        'var_page' => 'page',
        	    ]);
    	    //         halt($list);
    	    $title = ['序号','ip','时间','查询报告'];
	    }
	    //         dump(input());
	    $this->assign('title',$title);
	    $this->assign('list',$list);
	    $this->assign('data',$data);
	    return $this->fetch();
	}
	
	public function count_used_times(){
	    //统计防伪记录的使用情况
	    $mydb = new AntiModel();
	    // 获取本日的查询数量
	    $d_count = $mydb->whereTime('create_time', 'd')->count();;
	    // 获取本周的查询数量
	    $w_count = $mydb->whereTime('create_time', 'w')->count();
	    // 获取本月的查询数量
	    $m_count = $mydb->whereTime('create_time', 'm')->count();
	    // 获取今年的查询数量
	    $y_count = $mydb->whereTime('create_time', 'y') ->count();
	    $m3_count = $mydb->whereTime('create_time','-3 month')->count();
	    $html = '<p>今天查询'.$d_count.'条记录</p>';
	    $html .= '<p>本周查询'.$w_count.'条记录</p>';
	    $html .= '<p>本月查询'.$m_count.'条记录</p>';
	    $html .= '<p>本季查询'.$m3_count.'条记录</p>';
	    $html .= '<p>本年查询'.$y_count.'条记录</p>';
	    return $html;
	}
	
	

}