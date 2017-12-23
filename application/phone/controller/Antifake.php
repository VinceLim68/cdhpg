<?php
namespace app\phone\controller;
use think\Controller;
use app\phone\model\CPGRecordModel;

class Antifake extends Controller {
    
    public function index() {
        //手机查询防伪
        $reportid = input('id');
//         halt($reportid);
        $dbDB = new CPGRecordModel();
        $resu = $dbDB->field('customer,RAddress,RMoney,ZID')->where('ZID',$reportid)->find();
//          $resu = $dbDB->where('ZID',$reportid)->find(); 
//          halt($resu);
        $this->assign('res',$resu);
        return $this->fetch();
    }
}