<?php

class Tree {
    static public $treelist = [];
    
    static public function creat($data,$pid=0) {
       foreach ($data as $k=>$v) {
           if($v['pid']==$pid){
               self::$treelist[] = $v;
               unset($data[$k]);
               self::creat($data,$v['id']);
           }
       }
       return self::$treelist;
    }
}