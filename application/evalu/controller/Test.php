<?php

namespace app\evalu\controller;

use think\Db;
use app\phone\model\EasyPGXjModel;
use app\evalu\model\CommaddressModel;
use app\evalu\logic\MatchLogic;
use think\Exception;

class Test extends Common {
    //做一个测试模块,项目完成后删除
    
	protected function _initialize() {
		parent::_initialize ();
	}
	
	public function connectToEasyPG(){
	    //联系新数据库
//         $myconnect = Db::connect('EasyPG');
//         $newid = Db::connect('EasyPG')->query('select NewId() as newid');
//         dump($newid[0]['newid']);
//         $res = Db::connect('EasyPG')->table('lx_test1')->insert([
//             'id' => $newid[0]['newid'],
//             'lx_id' => 1,
//             'lx1' => '测试插入lx1 ',
//             'lx2' => '测试插入lx2 ',
//         ]);
//         dump($res);

//         1	KID	varchar	36		NO	系统编号		P	KID
//         2	ModuleName	varchar	50		YES	ModuleName
//         12	InputName	varchar	30		YES	制单人
//         13	InputDate	datetime			YES	制单日期
//         14	InputKID	varchar	36		YES	制单人KID
        
//         17	CheckName	varchar	30		YES	审核人
//         18	CheckDate	datetime			YES	审核日期
        
//         24	CorpKID	varchar	36		YES   公司名称        //这个必须
//         25	Xjdate	datetime			YES	询价日期
//         26	Xjdhcd	varchar	36		YES	询价单号
//         27	xjrkid	varchar	36		YES	询价人ID
//         28	Xjrname	varchar	36		YES	询价人
//         29	Xjxqname	varchar	60		YES	小区名称
//         30	Xjxqaddr	varchar	200		YES	地址
//         31	Xjkhtel	varchar	60		YES	客户电话
//         32	Xjtype	varchar	20		YES	记录来源           //客户询价
//         33	Xjwylx	varchar	36		YES	物业类型
//         34	Xjzlcs	int			YES	总层数
//         35	Xjlcs	varchar	20		YES	楼层
//         36	Xjyt	varchar	36		YES	用途
//         37	Xjxqjcnf	varchar	36		YES	建成年份
//         38	Xjxqjzjg	varchar	36		YES	建筑结构
//         39	Xjynxqdt	varchar	36		YES	是否带梯
//         40	Xjremark	varchar	200		YES	询价说明
//         41	Xjgjmd	varchar	36		YES	估价目的
//         42	Xjfccjdj	varchar	36		YES	成交单价
//         43	Xjfccjzj	varchar	36		YES	成交总价
//         44	Xjfcmj	numeric	16	2	YES	面积
//         45	bankname	varchar	100		YES	拟使用银行
//         46	Xjbjdjms	varchar	36		YES	报价单价
//         47	Xjbjzjems	varchar	36		YES	报价总价
//         49	Xjyjrkid	varchar	36		YES	应价人ID
//         50	Xjyjrname	varchar	36		YES	应价人
//         *51	Xjjhyjtime	datetime			YES	拟应价时间
//         52	Xjyjtime	datetime			YES	应价时间
//         53	Xjyjremark	varchar	300		YES	应价说明
//         54	Mark	varchar	300		YES	备注
//         *55	xjfytbz	varchar	1		YES	询价费已提
//         *56	xjkcxbz	varchar	1		YES	询价记录可查询标志
//         57	GJxmglkKID	varchar	36		YES	项目管理KID
//        * 58	ywqxbz	varchar	1		YES	业务取消标志
//         **59	ywqxDate	datetime			YES	业务取消日期
//         *60	xjqwdj	numeric	18	2	YES	业务期望单价
//         $EasyPGXj = new EasyPGXjModel();
//         dump($EasyPGXj->getUserKID('林晓'));
//         $k = array_slice($get1,-1);
//         $str = ($k[0]['Xjdhcd']);
//         dump((int)substr($str,-4));
dump(getUID());

        
	    
	}

	//匹配地址
	public function getAddress(){
	    ignore_user_abort(true); // 后台运行
	    error_reporting(0);
	    set_time_limit(0);
	     
	    $buffer = ini_get('output_buffering');
	    echo str_repeat(' ',$buffer+1);
	    ob_end_flush();
	    $CA = new CommaddressModel();          //CA=CommAddress
// 	    $pattern = '/(.*市)?(.*区)?(\D*)(\d*)(-\d+)?号(之[三二一四五六七八九十]*)?(\D*)?(\d+)?(室|单元|号车位)?/';
	    $CA->field('id,comm_name as community_name,address as title')->chunk(100, function($adds) {
	        foreach ($adds as $add) {
                echo $add['id'].$add['title'].'</br>';
                $id = MatchLogic::matchID($add);
                echo $id.'</br>';
//                 $pattern = '/(.*市)?(.*区)?(\D*)(\d+号)(之[三二一四五六七八九十]*)?(\d+)(室|单元)?/';
                $pattern = config('pattern');
//                 $pattern = '/(.*市)?(.*区)?(\D*)(\d*)(-\d+)?号(之[三二一四五六七八九十]*)?(\D*)?(\d+)?(室|单元|号车位)?/';
                $result = preg_match($pattern,$add['title'],$match);
                dump($match);
                if($result == 1){
                    try {
                        Db::table('commaddress')->where('id', $add['id'])->update([
                            'city' => $match[1],
                            'region'=>$match[2],
                            'road'=>$match[3],
                            'comm_id'=>$id,
                            'doorplate'=>$match[4].$match[5].'号'.$match[6],
                        ]);
                    }catch (Exception $e){
                        echo '=======================error===================</br>';
                        echo $add.',';
                        echo $add['id'].'</br>';
                    }
                }
	        }
	    });
        ignore_user_abort(false); // 解除后台运行
	}
	
	public function getCommIDByAddress(){
	    //测试解析地址
	    $address = '海沧区马青路5896号102室住宅房';
	    $id = MatchLogic::matchIDByAddress($address);
	    dump($id);
	}
	
	public function copyCommAddress(){
	    //把commaddress复制一遍，把重复的地址去除
	    ignore_user_abort(true); // 后台运行
	    error_reporting(0);
	    set_time_limit(0);
	    $buffer = ini_get('output_buffering');
	    echo str_repeat(' ',$buffer+1);
	    ob_end_flush();
// 	    $CA = new CommaddressModel();          //CA=CommAddress
// 	    $adds = Db::table('commaddress_copy')->limit(50)->select();
// 	    foreach ($adds as $add) {
// 	        try {
// 	            Db::table('commaddress')->insert($add);
// 	        }catch (Exception $e){
// 	            echo '=======================error===================</br>';
// 	            //                     echo $e.'</br>';
// 	            dump($e->getData()['PDO Error Info']['Driver Error Code']);
// 	            dump($add);
// 	            $e->getMessage;
// 	            $e->
// 	        }
// 	    };
	    Db::table('commaddress_copy')->chunk(100, function($adds) {
	        foreach ($adds as $add) {
                try {
                    Db::table('commaddress')->insert($add);
                }catch (Exception $e){
//                     echo '=======================error===================</br>';
                    if($e->getData()['PDO Error Info']['Driver Error Code']== 1062){
                        //如果重复，看看有没有新数据
                        $map['comm_id']=$add['comm_id'];
                        if($add['region']!= ''){
                            $map['region'] = $add['region'];
                        }
                        if($add['road']!= ''){
                            $map['road'] = $add['road'];
                        }
                        if($add['doorplate']!= ''){
                            $map['doorplate'] = $add['doorplate'];
                        }
                        $old = Db::table('commaddress')->where($map)->find();
                        $oldstr = $old['buildYear'].$old['floors'].$old['elevator'].$old['structure'];
                        $nowstr = $add['buildYear'].$add['floors'].$add['elevator'].$add['structure'];
                        if(strlen($nowstr) > strlen($oldstr)){
                            Db::table('commaddress')->where('id',$old['id'])->update([
                                'buildYear' => $add['buildYear'],
                                'floors' => $add['floors'],
                                'elevator' => $add['elevator'],
                                'structure' => $add['structure'],
                            ]);
                            echo '====old==='.$oldstr;
                            echo '====now==='.$oldstr;
                            echo '=======================updata===================</br>';
                        }
                        if($old['comm_id']==null and $add['comm_id']!=null){
                            Db::table('commaddress')->where('id',$old['id'])->update(['comm_id'=>$add['comm_id']]);
                        }
                    }else{
                        dump($e->getMessage());
                    }
//                     dump($add);
                }
	        }
	    }); 
        ignore_user_abort(false); // 解除后台运行
	}

	//测试正则
	public function test_reg(){
// 	    $pattern = '/^(\d+)?\.\d{4}$/';
	    $pattern = '/^(\d{4})-(\d{2})-(\d{2}) \d{2}:\d{2}:\d{2}.\d{3}$/';
	    
	    $string = '2066-06-05 00:00:00.000';
	    $result = preg_match($pattern,$string,$match);
	    dump($match);
	}
}