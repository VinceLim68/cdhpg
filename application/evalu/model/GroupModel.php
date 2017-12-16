<?php
namespace app\evalu\model;

use think\Model;

class GroupModel extends Model
{
    protected $pk = 'id';
    protected $table = 'group';
    protected $resultSetType = 'collection';
    
    public function getStatusAttr($value)
    {
        $status = [0=>'禁用',1=>'正常'];
        return $status[$value];
    }
}

