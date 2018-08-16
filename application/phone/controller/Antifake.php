<?php
namespace app\phone\controller;
use think\Controller;
use app\phone\model\CPGRecordModel;
use app\evalu\logic\LoginLogic;
use app\phone\model\AntiModel;
use app\report\model\EasyPGGjxmdetailModel;
use app\phone\model\EasyGjxmglkModel;

class Antifake extends Controller {
    
    public function index() {
        //手机查询防伪
        $reportid = input('id');
        $ip = LoginLogic::getIP();      //取查询人的ip
        $resu = (new CPGRecordModel())->field('customer,RAddress,RMoney,ZID')->where('ZID',$reportid)->find();
        if(!$resu){
            //如果在旧系统里没有查询到，就转到新系统里查询
            $resu = (new EasyGjxmglkModel())
                        ->alias('a')
                        ->join('PG_SE_Gjxmbgk w','a.KID = w.KID')
                        ->field('BgCD as ZID,Wtf as customer,PgAmt*10000 as RMoney,ProjectCovert as RAddress')
                        ->where('BgCD',$reportid)
                        ->find()->toArray();
        } 
        //登记查询情况
        $anti = new AntiModel();
        $anti->data([
            'ip'    =>  $ip,
            'zid'   =>  $resu['ZID']
        ])->save();
        $this->assign('res',$resu);
        $this->assign('time',$anti->create_time);
        return $this->fetch();
    }
    
    public function propaganda(){
        //这是一个宣传的页面
        return $this->fetch();
    }
    
    //查询报告的详细信息,查询页面
    public function myitem(){
        return $this->fetch();
    }
    
    //查询报告的详细信息,处理查询
    public function selectItems(){
        $input = input();
        $map_old = [];
        $map_new = [];
        if('' != trim($input['address'])){
            $map_old['RAddress|pa_locatedregion'] = ['like','%'.trim($input['address']).'%'];
            $map_new['concat(fwlp,fwdy)|XqName'] = ['like','%'.trim($input['address']).'%'];
        }
        if('' != trim($input['name'])){
            $map_old['RName|customer'] = ['like','%'.trim($input['name']).'%'];
            $map_new['Wtf|cqr'] = ['like','%'.trim($input['name']).'%'];
        }
        if('' != trim($input['zid'])){
            $map_old['ZID'] = ['like','%'.trim($input['zid']).'%'];
            $map_new['BgCD'] = ['like','%'.trim($input['zid']).'%'];
        }
        if('' != trim($input['from'])){
            $map_old['CustomerFrom|getreportmethod|e1.name'] = ['like','%'.trim($input['from']).'%'];
            $map_old['Ywly|g.xmjl'] = ['like','%'.trim($input['from']).'%'];
        }
        $resu_old = (new CPGRecordModel())
            ->alias('a')
            ->join('B_Employee e1','a.ywid = e1.EmpID','LEFT')
            ->join('B_Employee e2','a.qzid1 = e2.EmpID','LEFT')
            ->join('B_Employee e3','a.qzid2 = e3.EmpID','LEFT')
            ->join('B_Employee e4','a.PGid1 = e4.EmpID','LEFT')
            ->join('C_SendRecord e5','a.SendID = e5.SendID','LEFT')
            ->field(['RName' => 'cqr',
                'RAddress',
                'RMoney',           //在旧系统里，这是报告的评估总价
                'ZID',
                'RArea',
                'a.CustomerFrom',
                'a.customer' => 'Wtf',
                'a.getreportmethod',
                'efee1' => 'pgdj',
                'efor1' => 'yt',
                'pa_locatedregion',
                "e2.name+','+e3.name" => "gjs",
                'e4.name' => 'zgr',
                'e1.name' => 'ywy',
                'e5.bank',
                'PhotoName'])
            ->where($map_old)->select()->toArray();
        $resu_new = (new EasyPGGjxmdetailModel())
            ->alias('d')
            ->join('PG_SE_Gjxmglk g','d.KID = g.KID','LEFT')
            ->field(['Wtf',
                "concat(fwlp,fwdy)" => "RAddress",
                'g.PgAmt' => 'RMoney',      //报告的评估总价
                'BgCD' => 'ZID',
                'Pgzmj' => 'RArea',         //报告总面积
                'Ywly' => 'CustomerFrom',
                'cqr',
                'pgdj',
                'yt',
                'XqName' => 'pa_locatedregion',
                'jzmj' => 'detail_area',
                'd.pgAmt' => 'detail_total_price',      //估价对象的评估总价
                "concat(g.gjs1,',',g.gjs2)" => "gjs",
                "concat(g.zgr1,',',g.zgr2)" => 'zgr',
                'g.xmjl' => 'ywy',                      //项目经理
                'ywyname',
                'g.bankname' => 'bank',
                "concat(mxkcgjs1,',',mxkcgjs2)" => 'PhotoName'])
            ->where($map_new)
            ->select()->toArray();
        $resu = array_merge($resu_old,$resu_new);
        
        //用正则取出报告编号，组成一个sort字段
        $pattern = '/(^\D+)(\d{10})(.*)/';
        foreach ($resu as $k=>$v){
            preg_match($pattern, $v['ZID'],$match);
            $resu[$k]['sort'] = $match[2];
        }
        
        //把记录排倒序
        array_multisort(array_column($resu, 'sort'),SORT_DESC,$resu);
        $this->assign([
            'result'  => $resu,
        ]);
        return $this->fetch();
        
    }
    
    /* EasyGjxmglkModel
     * 
     *  PG_SE_Gjxmglk'：
         *  BgCD	varchar	30		YES	报告编号
         *  Wtf	varchar	50		YES	委托方
         *  bankname	varchar	100		YES	拟使用银行
         *  Ywly	varchar	50		YES	业务来源
         *  PgAmt	numeric	18	2	YES	评估总价
         *  ywyname	varchar	36		YES	业务员
            Xmjl	varchar	50		YES	项目经理
            gjs1	varchar	30		YES	签字估价师1
            gjs2	varchar	30		YES	签字估价师2
            zgr1	varchar	30		YES	撰稿人1
            zgr2	varchar	30		YES	撰稿人2
            Pgzmj	numeric	16	2	YES	总面积
            Pgdyjzamt	numeric	16	2	YES	抵押价值合计
        PG_SE_Gjxmbgk：
            ProjectCovert	varchar	500		YES	项目名称封面格式
        PG_SE_Gjxmdetail：
            dz	varchar	200		YES	地址
            cqr	varchar	100		YES	产权人
            jcnf	varchar	30		YES	建成年份
            jzmj	numeric	18	2	YES	建筑面积
            yt	varchar	36		YES	用途
            pgdj	numeric	18	6	YES	评估单价
            pgAmt	numeric	18	6	YES	评估总价
            zcs	varchar	50		YES	总层数
            GjxmglkKID	varchar	36		YES	项目KID
    CPGRecordModel
        RName   产权人
        RAddress    地址
        RArea       建筑面积
        zid     报告编号
        CustomerFrom    客户来源
        customer    委托方
        getreportmethod 取报告人联系方式
        efee1   单价1
        efor1   用途1
        RMoney  评估总价
        pa_locatedregion    小区名称
        qzid1
        qzid2       签字估价师
        PGid1       撰稿人
        
        B_Employee
            EmpID
            Name
        
    */
}