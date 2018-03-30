<?php

namespace app\evalu\controller;

use think\Db;
use app\phone\model\EasyPGXjModel;

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
	
}