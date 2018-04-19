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
}