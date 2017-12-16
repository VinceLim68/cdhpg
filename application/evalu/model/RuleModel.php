<?php
namespace app\evalu\model;

use think\Model;

class RuleModel extends Model
{
    protected $pk = 'id';
    protected $table = 'rule';
    protected $resultSetType = 'collection';
    
    public function getStatusAttr($value)
    {
        $status = [0=>'禁用',1=>'正常'];
        return $status[$value];
    }
    
    public function getTypeAttr($value)
    {
        $type = [1=>'项目',2=>'模块',3=>'操作'];
        return $type[$value];
    }
    
    /**
     * 删除数据
     * @param	array	$map	where语句数组形式
     * @return	boolean			操作是否成功
     */
    public function deleteData($map){
        $count=$this
        ->where(array('pid'=>$map['id']))
        ->count();
        if($count!=0){
            return false;
        }
        $result=$this->where($map)->delete();
        return $result;
    }
}

