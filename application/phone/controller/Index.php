<?php
namespace app\phone\controller;
use think\Controller;
use app\evalu\logic\MatchLogic;
use think\Db;
use app\evalu\logic\PriceLogic;
use app\evalu\controller\Common;
use app\evalu\model\ErrorCommModel;
use app\phone\model\QueryRecordsModel;
use app\evalu\model\SalesModel;
use app\phone\model\TEnquiryModel;
use app\phone\model\TCaseCfgModel;
use app\evalu\logic\CreatExcelLogic;
use app\phone\model\CPGRecordModel;

class Index extends Common {
    
    public function index() {
        //手机询价界面
        return $this->fetch();
    }
    
    public function getCommName(){
        //把输入的小区名称转化成相应的小区编号
        if (request()->isPost()) {
//             halt(input());
            $result = $this->validate ( input ( 'param.' ), [
                'comm' => 'require|max:25|min:2',
                'price'=>   'number|between:100,12000000',
            ],
            [
                'comm.require' => '请问您要查询哪个小区？',
                'comm.max'     => '名称最多不能超过25个字符',
                'comm.min'     => '名称最少要两个字',
                'price.number'  =>'成交价请输入数字',
                'price.between'  =>'认真一点，把你的成交价填进去',
            ] );
            	
            if (true !== $result) {
                // 验证失败 输出错误信息
                $this->error ( $result );
                exit ();
            } else {
                //1查询数据,把comm存入session中
                session('user.comm',input('comm'));
                $commnames = MatchLogic::matchSearch(input('param.comm'));
//                 halt($commnames);
                if(!$commnames){
                    //2如果没有查到，记录到miss_comm表中去
                    try{
                        $errorcomm = ErrorCommModel::create([
                            'memo'     =>  '没有小区名',
                            'user_id'       =>  session('user.user_id'),
                            'user_name'     =>  session('user.user_name'),
                            'type'          =>  1,
                            'comm_name'     =>  input('param.comm'),
                        ]);
                    }catch(\Exception $e){
                        $this->error('没有查询到叫"'.input('param.comm').'"的地方');
                    }
                    $this->error('没有查询到叫"'.input('param.comm').'"的地方');
                }elseif(count($commnames)>1){
                //4如果查到多个，列表展示，让用户手动挑选后，再转入统计模块
                    $commArr = [];      //取出完整的数据
                    foreach ($commnames as $comm){
                        $commArr[] = Db::table('comm')->where('comm_id',$comm['comm_id'])->find();
                    }
                    $this->assign ( 'fields', $commArr );
                    $this->assign('price',input('price'));
                    return $this->fetch();
                }else{
                //3如果查到一个，转入统计模块
                    if(!input('price')){
                        $this->redirect('getPrice', ['comm_id' => $commnames[0]['comm_id']]);
                    }else{
                        $this->redirect('getPrice', ['comm_id' => $commnames[0]['comm_id'],'price'=>input('price')]);
                    }
                }
            }
        }
        
    }

    public function getPrice($comm_id ='',$price = 0){
        //通过小区编号求取相应的报价参数
        //1.如何有当月的报价记录，就直接读取
        //2.如果没有，就查询挂牌数据库进行计算，并把计算结果写入查询记录中去
        //或者如果有成交记录，也可以重新计算，并把成交记录记入成交表中去
        if(input('comm_id')){
            $comm_id = input('comm_id');
        }
//         halt(input());
//         halt($comm_id);
        $result = SalesModel::getRecordsByCommid($comm_id);
        $getComm = Db::table('comm')->where('comm_id',$comm_id)->find();
        session('comm.comm_id',$comm_id);
        session('comm.comm_name',$getComm['comm_name']);
        if(count($result[1])>0){
            $PL = new PriceLogic($result);
            $getPrice_result = $PL->getStatic($getComm,$price);
            $getPrice_result['emplorers'] = config('emplorers');
            $getPrice_result['use'] = config('use');
            $getPrice_result['elevator'] = config('elevator');
            $getPrice_result['structuer'] = config('structuer');
            
            $this->assign('B',$getPrice_result);
            //===================把离散值过大的数据记录error_comm,以备改进=================================
            if($getPrice_result['std_r'] > config('std_r_limit')){
                if($getPrice_result['comm']['comm_name']){
                    //如何没有小区，就不记录了
                    $errorcomm = ErrorCommModel::create([
                        'memo'          =>  $getPrice_result['std_r'],
                        'user_id'       =>  session('user.user_id'),
                        'user_name'     =>  session('user.user_name'),
                        'type'          =>  2,
                        'comm_name'     =>  $getPrice_result['comm']['comm_name'],
                        'comm_id'       =>  $getPrice_result['comm']['comm_id'],
                    ]);
                }
            }
            //===================登记查询记录===============================================
            QueryRecordsModel::insert_record($getPrice_result);
        }else{
            $errorcomm = ErrorCommModel::create([
                'memo'          =>  '没数据',
                'user_id'       =>  session('user.user_id'),
                'user_name'     =>  session('user.user_name'),
                'type'          =>  3,
                'comm_name'     =>  $getComm['comm_name'],
                'comm_id'       =>  $getComm['comm_id'],
            ]);
            $this->error('没查询到数据');
        }
        return $this->fetch();

    }
    
    public function dispute(){
       //在查询结果中，如果有争议可以进行记录
       //1、先看数据是否合法
       $result = $this->validate ( input ( 'param.' ), [
           'myprice'=>   'number',
       ],
       [
           'myprice.number'  =>'建议评估价只能输入数字',
       ] );
        
       if (true !== $result) {
           // 验证失败 输出错误信息
           return ['h'=>'请注意','b'=>$result];
       } else {
           if(input('myprice')<input('my_min')*0.8 or input('myprice')>input('my_max')){
               return ['h'=>'抱歉','b'=>'感谢您的参与，但是您提供的参考价格未被接受'];
           }
           return  QueryRecordsModel::update_dispute(input());
       }
    }
    
    public function test(){
//         try {
//             $hostname='192.168.1.3';
//             $port=1433;//端口
//             $dbname="Evalue";//库名
//             $username="sa";//用户
//             $pw="sa";//密码
//             $dbDB = new CPGRecordModel();
//             $dbDB = new \PDO("sqlsrv:Server=$hostname;Database=$dbname",$username,$pw);
//             $dbh= new \PDO("dblib:host=$hostname:$port;dbname=$dbname","$username","$pw");
//         } catch (PDOException $e) {
//             echo"Failed to get DB handle: ".$e->getMessage() ."n";
//             exit;
//         }
//         echo'connent MSSQL succeed';
//         $resu = $dbh->where('Rid',17121880)->find();
//         $stmt = $dbh->prepare("select * from users");
//         $stmt->execute();
        
//         while ($row = $stmt->fetch()) {
//             print_r($row);
//         }
        
//         unset($dbh);
//         unset($stmt);
//         $hostname = '192.168.1.3';
//         $dbname = 'Evalue';
//         $usename = 'sa';
//         $pass = 'sa';
//         $dbDB = new \PDO("sqlsrv:Server=$hostname;Database=$dbname",$usename,$pass);
//             phpinfo();
        $reportid = input('id');
        $dbDB = new CPGRecordModel();
        $resu = $dbDB->field('RName,RAddress,RMoney,ZID')->where('ZID',$reportid)->find();
//         halt($resu); 
        $this->assign('res',$resu);
        return $this->fetch();
    }
    
    public function insertquery(){
        //插入询价记录
        $result = $this->validate(input(),'InsertQueryValidate');
        if(true !== $result){
            // 验证失败 输出错误信息
            return ['status'=>'输入不规范','msg'=> $result];
        };
        $data = input();
        $data['Enquiry_Source'] = '估价师报价';
        $data['Enquiry_Date'] = date ( "Y-m-d");
        $data['PA_YearBuilt'] = date ('Y-m-d', strtotime($data['PA_YearBuilt'].'-1-1'));
        if(!isset($enq)){
            $enq = new TEnquiryModel();
        }
        //不同估价师，同一小区，同一用途，在一段时间内不允许报相同的价;但管理员不受此限
        if(session('user.user_name') != 'admin'){
//             $findResult2 = $enq->where('Enquiry_CellName',$data['Enquiry_CellName'])
//                         ->where('Apprsal_Use',$data['Apprsal_Use'])
//                         ->where('Enquiry_Date','> time',date ( "Y-m-d", strtotime ( "-30 day" ) ))
//                         ->where('Apprsal_Up',$data['Apprsal_Up'])
//                         ->find();
//             $findResult2 = $enq->findEnqueryByCommAndDate($data);
            if($enq->findEnqueryByCommAndDate($data)){
                return ['status'=>'报价雷同','msg'=> '在过去的一个月中已经有估价师对同一小区、同一用途作过相同报价，不再重复记录、'];
            }
            
        }
        //同一估价师，同一小区，同一用途，在一段时间内不允许重复报价
//         $findResult = $enq->where('OfferPeople',$data['OfferPeople'])
//                     ->where('Enquiry_CellName',$data['Enquiry_CellName'])
//                     ->where('Apprsal_Use',$data['Apprsal_Use'])
//                     ->where('Enquiry_Date','> time',date ( "Y-m-d", strtotime ( "-30 day" ) ))
//                     ->find();
        //插入记录
//         if(!$findResult){
        if(!$enq->findEnqueryByOfferAndDateAndComm($data)){
            $insertEnguery = $enq->data($data)->save();
            $num = $enq->getCount($data);
            return ['status'=>'登记成功','msg'=> '已将询价记录成功记入数据库中,本月'.$data['OfferPeople'].'已报价'.$num.'条'];
        }else{
            return ['status'=>'重复数据','msg'=> '您在过去的一个月中已经对同一小区、同一用途作过报价，不再重复记录、'];
        }
    }

    public function getHistory(){
        /*
         * 得到历史的询价记录和案例
         */
        if(!isset($enq)){
            $enq = new TEnquiryModel();
        }
        $historyEnquery = $enq->getEnqueryByCommAndDate();
        return $historyEnquery;
//         halt($historyEnquery);

        
    }
    
    public function getCase(){
        if(!isset($case)){
            $case = new TCaseCfgModel();
        }
        $cases = $case->getCaseByNameAndDate();
        return $cases;
        
    }
    public function creatExcel(){
        //生成excel文件
//         halt(input());
        return CreatExcelLogic::creatExcel();
    }
}