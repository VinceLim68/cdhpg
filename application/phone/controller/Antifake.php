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
        $resu = $dbDB->field('RName,RAddress,RMoney,ZID')->where('ZID',$reportid)->find();
        $this->assign('res',$resu);
        return $this->fetch();
    }
}