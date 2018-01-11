<?php
namespace app\phone\controller;
use think\Controller;
use app\phone\model\CPGRecordModel;
use app\evalu\logic\LoginLogic;
use app\phone\model\AntiModel;

class Antifake extends Controller {
    
    public function index() {
        //手机查询防伪
        $reportid = input('id');
        $ip = LoginLogic::getIP();
//         $dbDB = ;
        $resu = (new CPGRecordModel())->field('customer,RAddress,RMoney,ZID')->where('ZID',$reportid)->find();
//         dump($resu);
        $anti = new AntiModel();
        $anti->data([
            'ip'    =>  $ip,
            'zid'   =>  $resu['ZID']
        ])->save();
//         dump($anti->create_time);
        $this->assign('res',$resu);
        $this->assign('time',$anti->create_time);
        return $this->fetch();
    }
    
    public function propaganda(){
        return $this->fetch();
    }
}