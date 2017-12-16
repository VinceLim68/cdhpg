<?php

namespace app\evalu\controller;

use think\Db;
use app\evalu\model\GroupAccessModel;
use app\evalu\model\GroupModel;
use app\evalu\model\RuleModel;
use app\evalu\model\UserModel;

class Rules extends Common {
	protected $db;
	
	protected function _initialize() {
		parent::_initialize ();
	}
	/**
	 * 用户列表
	 */
	public function user(){
	    $data=(new UserModel())->paginate(20);
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
// 	        halt($id);
	        // 获取用户数据
	        $user_data=(new UserModel())->find($id);
// 	        halt($user_data);
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

}