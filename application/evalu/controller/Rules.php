<?php

namespace app\evalu\controller;

use think\Db;
use app\evalu\model\GroupAccessModel;
use app\evalu\model\GroupModel;
use app\evalu\model\RuleModel;
use app\evalu\model\UserModel;
use app\phone\model\QueryRecordsModel;

class Rules extends Common {
	protected $db;
	
	protected function _initialize() {
		parent::_initialize ();
	}
	/**
	 * 用户列表
	 */
	public function user(){

	    $data=(new UserModel())->order('register_date desc')->paginate(20);
	    $group = new GroupAccessModel();
	    //关联角色
	    foreach ($data as $d){
            //找出用户所有的角色
	        $ga = $group->alias('ga')
	               ->join('group g' ,'ga.group_id=g.id','left')
	               ->where('uid',$d['user_id'])->select()->toArray();
	        //多个角色生成一个string
            $temp = '';
	        foreach ($ga as $t){
	            //重复的角色过滤掉
	            if(!strpos($temp, $t['title'])){
	                $temp .= $t['title'].',';
	            }
	        }
	        //把最后一个,去掉
	        if(strlen($temp)>0){
	            $temp = rtrim($temp, ",");
	        }
	        $d['title'] = $temp;
	    }
//         halt($data);
	    $assign=array(
	        'data'=>$data
	    );
	    $this->assign($assign);
	    return $this->fetch();
	}

	/**
	 * 修改用户
	 */
	public function edit_user(){
	    if(request()->isPost()){
	        $data=input('post.');
	        // 组合where数组条件
	        $uid=$data['user_id'];
// 	        $map=array(
// 	            'user_id'=>$uid,
// 	        );
	        // 修改权限
	        $GA = new GroupAccessModel();
	        $GA ->where(array('uid'=>$uid))->delete();
	        foreach ($data['group_ids'] as $k => $v) {
	            $group=array(
	                'uid'=>$uid,
	                'group_id'=>$v
	            );
	            $GA ->insert($group);
	        }
	        $data=array_filter($data);
	        // 如果修改密码则md5
	        if (!empty($data['pass'])) {
	            $data['pass']=md5($data['password']);
	        }
	        $U = new UserModel();
	        $result = $U->allowField(true)->save($data,['user_id'=>$uid]);
	        if($result){
	            // 操作成功
	            $this->success('编辑成功',url('user'));
	        }else{
	            $error_word=$U->getError();
	            if (empty($error_word)) {
	                $this->success('编辑成功',url('edit_user',array('id'=>$uid)));
	            }else{
	                // 操作失败
	                $this->error($error_word);
	            }
	
	        }
	    }else{
	        $id=input('user_id');
	        // 获取用户数据
	        $user_data=(new UserModel())->find($id);
	        // 获取已加入用户组
	        $group_data=(new GroupAccessModel())
	        ->where(array('uid'=>$id))
	        ->column('group_id');
	        // 全部用户组
	        $data=(new GroupModel())->select();
	        $assign=array(
	            'data'=>$data,
	            'user_data'=>$user_data,
	            'group_data'=>$group_data
	        );
	        $this->assign($assign);
	        return $this->fetch();
	    }
	}
    /**
     * 用户组/角色列表
     */
    public function group(){
        $data=(new GroupModel())->select();
        $assign=array(
            'data'=>$data
        );
        $this->assign($assign);
        return $this->fetch();
    }
    
    /**
     * 添加用户组、角色
     */
    public function add_group(){
        $data=input('post.');
        $result = $this->validate($data,'Group');
        if(true !== $result){
            // 验证失败 输出错误信息
           $this->error($result);
        }
        $result=(new GroupModel(input('post.')))->save();
        if ($result) {
            $this->success('添加成功',url('group'));
        }else{
            $this->error('添加失败');
        }
    }
    
    /**
     * 删除用户组
     */
    public function delete_group(){
        $id=input('id');
        $map=array(
            'id'=>$id
        );
        $result=(new GroupModel())->destroy($map);
        if ($result) {
            $this->success('删除成功',url('group'));
        }else{
            $this->error('删除失败');
        }
    }
    
    /**
     * 修改用户组
     */
    public function edit_group(){
        $data=input();
//         halt($data);
        $map=array(
            'id'=>$data['id']
        );
        $result = (new GroupModel())->where($map)->update($data);
//         $result=D('AuthGroup')->editData($map,$data);
        if ($result) {
            $this->success('修改成功',url('group'));
        }else{
            $this->error('修改失败');
        }
    }
    
    
    /**
     * 权限列表
     */
    public function rule(){
        $db = new RuleModel();
//         下面两句做了个子查询，把pid从序号转化成名称
//         $subsql = $db->field('title,id')->buildSql();
//         $data = $db->alias('a')->join([$subsql=> 'b'], 'a.pid = b.id','left')
//                 ->field('a.id,a.name,a.title,a.type,a.status,a.condition,a.pid,b.title as pidname')
//                 ->select();
        $data = $db->select();
        $data = \Tree::creat($data);
        $pnodes = Db::table('rule')->where('type != 3')->select();//不想被模型自动转换字段
//         $pnodes = $db->where('type != 3')->select();
        $assign=array(
            'data'=>$data,
            'pnodes'=>$pnodes,
            );
        $this->assign($assign);
        return $this->fetch();
    }
    
    /**
     * 添加权限
     */
    public function add_rule(){
        $data=input('post.');
        $result = $this->validate($data,'Rule');
        if(true !== $result){
            // 验证失败 输出错误信息
            $this->error($result);
        }
        $result=(new RuleModel($data))->save();
        if ($result) {
            $this->success('添加成功',url('rule'));
        }else{
            $this->error('添加失败');
        }
    }
    
    /**
     * 删除权限
     */
    public function delete(){
        $id=input('id');
        $map=array(
            'id'=>$id
        );
        $result=(new RuleModel())->deleteData($map);
        if ($result) {
            $this->success('删除成功',url('rule'));
        }else{
            $this->error('请先删除子权限');
        }
    }
    /**
     * 修改权限
     */
    public function edit(){
        $data=input('post.');
        $map=array(
            'id'=>$data['id']
        );
        $result=(new RuleModel())->where($map)->update($data);
        if ($result) {
            $this->success('修改成功',url('rule'));
        }else{
            $this->error('修改失败');
        }
    }
    /**
     * 分配权限
     */
    public function rule_group(){
        if(request()->isPost()){
            $data=input('post.');
//             halt($data);
            $map=array(
                'id'=>$data['id']
            );
            $data['rules']=implode(',', $data['rule_ids']);
//             halt($data);
//             $result=D('AuthGroup')->editData($map,$data);
            $group = new GroupModel();
            $result = $group->where($map)->setField('rules', $data['rules']);
            if ($result) {
                $this->success('操作成功',url('group'));
            }else{
                $this->error('操作失败');
            }
        }else{
            $id=input('id');
            // 获取用户组数据
            $group_data=(new GroupModel())->where(array('id'=>$id))->find();
            $group_data['rules']=explode(',', $group_data['rules']);
            // 获取规则数据
            $rule_data = Db::table('rule')->select();
            $rule_data = \Tree::creat($rule_data);
            $assign=array(
                'group_data'=>$group_data,
                'rule_data'=>$rule_data
            );
            $this->assign($assign);
            return $this->fetch();
        }
    
    }
    
    public function enquery_list(){
        $mydb = new QueryRecordsModel();
        $where = '1=1';
//         if(request()->isPost()){
//             if(input('search_days')){
//                 where("create_time", ">= time", strtotime('-'.search_days.' day'))
//             }
//             if(input('search_type')){
//                 if(input('search_type')=='user'){
//                     //按询价人分类统计
//                     $where .= ' and '
//                 }
//             }
//         }
        $list = $mydb->order('create_time desc')->where("create_time", ">= time", strtotime('-'.input('search_days').' day'))->paginate(50);
        $title = ['序号','查询人','小区','片区','查询结果','价格类型','成交价格','查询时间','争议价格'];
        $this->assign('title',$title);
        $this->assign('list',$list);
//         halt($list);
        return $this->fetch();
    }
    
    public function get_enquery_records(){
        //获取询价数据
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
        
//             $sql_count = "SELECT COUNT(*) AS count FROM comm where " . $where;
//             $records = $this->db->query ( $sql_count );
            $mydb = new QueryRecordsModel();
            $rec_nums = $mydb->where($where)->count('id');
            $total = ceil ( $rec_nums/ $limit );
            $list = $mydb->limit ( $limit )->page ( $page )->where ( $where )->order($sidx.' '.$sord)->select ()->toArray ();
            /*
             * 返回值：total总页数,page当前页码,records总记录数,
             * rows数据集,id每条记录的唯一id,cell具体每条记录的内容
             */
            $outputs ['total'] = $total;
            $outputs ['page'] = $page;
            $outputs ['records'] = $rec_nums;
            $outputs ['rows'] = $list;
        
            return $outputs;
    }

}