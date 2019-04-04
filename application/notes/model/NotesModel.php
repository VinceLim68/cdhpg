<?php
namespace app\notes\model;

use think\Model;

class NotesModel extends Model {
    //这是存放具体读书记录的表，通过Itemid与Items表关联
    protected $pk = 'id';
    protected $table = 'notes';
    protected $resultSetType = 'collection'; // 这个设置可以很快把返回数据集转成array
    protected $field = true; // 忽略非数据表字段而不报错
    protected $autoWriteTimestamp = true;
}