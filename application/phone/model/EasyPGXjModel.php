<?php

namespace app\phone\model;

use think\Model;
use think\Db;

class EasyPGXjModel extends Model {
	protected $pk = 'KID';
	protected $table = 'PG_SE_xjglk';
	// 设置当前模型的数据库连接
	protected $connection =  'EasyPG';
	protected $resultSetType = 'collection'; // 这个设置可以很快把返回数据集转成array
	protected $field = true; // 忽略非数据表字段而不报错
	
	//自定义初始化
    protected function initialize()
	{
	    //需要调用`Model`的`initialize`方法
	    parent::initialize();
	    //TODO:自定义的初始化
	}

   public function getXjdhcd(){
       //自动生成询价单号：按日期取当天最后的单号，再+1后生成单号
       $d_test = 'XJ'.date('Ymd');//
       $record = $this->field('Xjdhcd')->where('Xjdhcd','like',$d_test.'%')->order('Xjdhcd','desc')->find();
       if($record){
           $num = (int)substr($record->Xjdhcd,-4);
           $new_num = $num + 10000 + 1;
           $Xjdh = $d_test.substr($new_num,1,4);
       }else{
           $Xjdh = $d_test.'0001';
       }
       return $Xjdh;
   }
   
   public function getUserKID($username){
       //从用户名查其KID
       $KID = Db::connect('EasyPG')->table('GS_SE_SECURITY_USER')->where('UserName',$username)->value('KID');
       return $KID;
   }
   
   
   public function insertRecord($data){
       $data['ModuleName'] = '85515DEB-8BF8-44B1-A5E3-E305D8E32239';
       $data['KID'] = getUID();
       $data['CorpKID'] = 'D6C4FB26-CE9B-4B10-8F12-341E163646E3';
       $data['Xjdhcd'] = $this->getXjdhcd();
       $data['InputName'] = $data['Xjyjrname'];
       $data['CheckName'] = $data['Xjyjrname'];
       
       $data['Xjdate'] = date('Y-m-d h:i:s');
       $data['InputDate'] = $data['Xjdate'];
       $data['CheckDate'] = $data['Xjdate'];
       $data['Xjyjtime'] = $data['Xjdate'];
       
       $data['InputKID'] = $this->getUserKID($data['InputName']);
       $data['Xjyjrkid'] = $data['InputKID'];
       
       $data['xjrkid'] = $this->getUserKID($data['Xjrname']);
       $data['Xjtype'] = '客户询价';
       return $this->data($data)->allowField(true)->save();

       
       
   }
   
   public function findEnqueryByOfferAndDateAndComm($data){
       //按小区名、日期和报价估价师查询
       if(session('user.user_name') == 'admin'){
           $data['Xjyjrname'] = '林晓';
       }
       return $this->where('Xjyjrname',$data['Xjyjrname'])
       ->where('Xjyjtime','>= time',date ( "Y-m-d", strtotime ( "-".config('no_enquery_again')." day" )))
       ->where('Xjxqname',$data['Xjxqname'])
       ->where('Xjyt',$data['Xjyt'])
       ->find();
   }
   
   public function getCount($data){
       $day = date('Y-m-01', strtotime(date("Y-m-d")));
       if(session('user.user_name') == 'admin'){
           $data['Xjyjrname'] = '林晓';
       }
       $res = $this
       ->where('Xjyjtime','>= time',$day)
       ->where('Xjyjrname',$data['Xjyjrname'])
       ->count();
       return $res;
   }
   
   
   //通过小区名和日期来查询表中的询价记录
   public function getEnqueryByCommAndDate(){
       $records = $this->field([
           'Xjxqname'=>'Enquiry_CellName',
           'InputDate'=>'Enquiry_Date',
           'Xjyt'=>'Apprsal_Use',
           'InputName'=>'OfferPeople',
           'Xjbjdjms'=>'Apprsal_Up',
           'Xjyjremark'=>'Remark',
           'Xjrname'=>'Enquiry_PmName',
           'Xjxqaddr'=>'PA_Located'
           ])
       ->order('Enquiry_Date desc')         //这里可以用别名
       ->where('InputDate','>= time',date('Y-m-d',strtotime('-'.config('historyDays').' day')))         // 这里不能用别名
       ->where('Xjxqname','like','%'.session('user.comm').'%')
       ->select()//;//
       ->toArray();
//        halt($records);
       return $records;

   }
}