<?php
namespace app\notes\model;

use think\Model;

class ItemsModel extends Model {
    //这是存放用户名、分类、书名的表
    protected $pk = 'id';
    protected $table = 'items';
    protected $resultSetType = 'collection'; // 这个设置可以很快把返回数据集转成array
    protected $field = true; // 忽略非数据表字段而不报错
    protected $autoWriteTimestamp = true;
}