<?php
namespace app\evalu\model;

use think\Model;

class GroupAccessModel extends Model
{
    protected $pk = 'uid';
    protected $table = 'group_access';
    protected $resultSetType = 'collection';
    
    /**
     * 获取管理员权限列表
     */
    public function getAllData(){
        $data=$this
        ->field('u.id,u.username,u.email,aga.group_id,ag.title')
        ->alias('aga')
        ->join('__USERS__ u ON aga.uid=u.id','RIGHT')
        ->join('__AUTH_GROUP__ ag ON aga.group_id=ag.id','LEFT')
        ->select();
        // 获取第一条数据
        $first=$data[0];
        $first['title']=array();
        $user_data[$first['id']]=$first;
        // 组合数组
        foreach ($data as $k => $v) {
            foreach ($user_data as $m => $n) {
                $uids=array_map(function($a){return $a['id'];}, $user_data);
                if (!in_array($v['id'], $uids)) {
                    $v['title']=array();
                    $user_data[$v['id']]=$v;
                }
            }
        }
        // 组合管理员title数组
        foreach ($user_data as $k => $v) {
            foreach ($data as $m => $n) {
                if ($n['id']==$k) {
                    $user_data[$k]['title'][]=$n['title'];
                }
            }
            $user_data[$k]['title']=implode('、', $user_data[$k]['title']);
        }
        // 管理组title数组用顿号连接
        return $user_data;
    
    }
}

