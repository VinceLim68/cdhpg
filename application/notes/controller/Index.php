<?php
namespace app\notes\controller;

use think\Controller;
use app\notes\model\ItemsModel;
use app\notes\model\NotesModel;

class Index extends Controller {
    
    public static $treeList = array();

    //增加或修改条目
    public function item(){
        
        $input = input();
        $orders = json_decode($input['orders']);
//         dump($input);
//         dump($orders);
        $itemmodel = new ItemsModel();
        if($input['id'] == ''){
            //如果没有id传过来，说明要新建记录
            //同级兄弟节点要修改一下排序
            foreach ($orders as $k=>$v){
                $itemmodel->where('id',$k)->update(['order' => $v]);
            }
            $itemmodel->allowField(true)->data($input)->save();
        }else{
            //如果有id传过来，则修改记录
            $itemmodel->allowField(true)->save($input,['id' => $input['id']]);
        }
        
        //返回id值
        return $itemmodel->id;
    }
    

    //书斋首界面
    public function Index(){
        $items = (new ItemsModel())->select()->toArray();
        //先按pid，再按order，进行双重排序
        array_multisort(array_column($items, 'pid'), SORT_ASC,array_column($items, 'order'),SORT_ASC, $items);
        self::$treeList = array();
        $items = $this->tree($items);
        $this->assign('items', $items);
        return $this->fetch();
    }
    
    //生成无限分级的数组
    public function tree(&$data, $pid = 0, $count = 1)
    {
        foreach ($data as $key => $value) {
            if ($value['pid'] == $pid) {
                $value['Count'] = $count;
                self::$treeList[] = $value;
                unset($data[$key]);
                $this->tree($data, $value['id'], $count + 1);
            }
        }
        return self::$treeList;
    }

    //某本书的笔记首界面
    public function Notes(){
        $input = input();
        $notes = (new NotesModel())->where('itemid',$input['itemid'])->select()->toArray();
        array_multisort(array_column($notes, 'pid'), SORT_ASC,array_column($notes, 'order'),SORT_ASC, $notes);
        self::$treeList = array();
        $notes = $this->tree($notes);
        $this->assign([
            'item'  => $input,
            'notes' => $notes,
        ]);
//         dump($input);
        return $this->fetch(); 
    }
}