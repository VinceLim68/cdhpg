<?php

namespace app\evalu\controller;


use app\phone\model\AntiModel;

class Lists extends Common {
	protected function _initialize() {
		parent::_initialize ();
	}
	
	public function anti_list(){
	    $mydb = new AntiModel();
	    $where = '1=1';
	    $data = input();
	    if(!isset($data['search_days']) or $data['search_days']==''){
	        $data['search_days'] = 30;
	    }
	    if(!isset($data['search_type'])){
	        $data['search_type'] = 'all';
	    }
	    if(input ( 'search_type' ) and input('search_type')!='all'){
	        $where .= ' and type = "'.input ( 'search_type' ).'"';
	    }
	    //         dump(input());
	    $list = $mydb
    	    ->where("create_time", ">= time", strtotime('-'.input('search_days').' day'))
    	    ->where($where)
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
	    $this->assign('title',$title);
	    $this->assign('list',$list);
	    $this->assign('data',$data);
	    return $this->fetch();
	}

}