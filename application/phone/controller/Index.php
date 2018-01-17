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
use app\evalu\model\CommRelateModel;

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
                        $this->redirect('getCommChild', ['comm_id' => $commnames[0]['comm_id']]);
                    }else{
                        $this->redirect('getCommChild', ['comm_id' => $commnames[0]['comm_id'],'price'=>input('price')]);
                    }
//                      if(!input('price')){
//                         $this->redirect('getPrice', ['comm_id' => $commnames[0]['comm_id']]);
//                     }else{
//                         $this->redirect('getPrice', ['comm_id' => $commnames[0]['comm_id'],'price'=>input('price')]);
//                     } 
                }
            }
        }
        
    }
    

    
    public function getCommChild($comm_id ='',$price = 0){
        //取得小区的子分类
        $commrelate = new CommRelateModel;
        $list = $commrelate->where('community_id',$comm_id)->select()->toArray();
        $my = [];
        if(empty($list)){
            $my['community_id'] = $comm_id;
            $my['price'] = $price;
            $this->redirect('getPrice', $my);
        }else{
            //如果需要关联其他小区查询
            //如果有两种以上数据需要手动选择
            if(count($list)>=2){
                foreach ($list as $item){
                    $item['where'] = base_encode($item['where']);
                    $item['create_time'] = base_encode($item['create_time']);
                    $item['usage'] = base_encode($item['usage']);
                    $item['price'] = $price;
                    $my[] = $item;
                }
                $this->assign('fields',$my);
                return $this->fetch();
            }else{
            //否则直接传递
                $item = $list[0];
                $item['where'] = base_encode($item['where']);
                $item['create_time'] = base_encode($item['create_time']);
                $item['usage'] = base_encode($item['usage']);
                $item['price'] = $price;
                $this->redirect('getPrice', $item);
//                 halt($my);
            }
        }
    }
    public function getPrice(){
        //$comm_id ='',$price = 0
        //通过小区编号求取相应的报价参数
        //1.如何有当月的报价记录，就直接读取
        //2.如果没有，就查询挂牌数据库进行计算，并把计算结果写入查询记录中去
        //或者如果有成交记录，也可以重新计算，并把成交记录记入成交表中去
//         halt(input());
        $data = input();
//         halt($data);
        if(isset($data['where'])){$data['where'] = base_decode($data['where']);};
        if(isset($data['create_time'])){$data['create_time'] = base_decode($data['create_time']);};
        if(isset($data['usage'])){$data['usage'] = base_decode($data['usage']);};
        if(!isset($data['price'])){$data['price'] = 0;};
        //$comm_id = isset($data['rela_comm_id']) ? $data['rela_comm_id'] : $data['community_id'];
        //通过id找小区名称，写入session中，以便传到前端
        $getComm = Db::table('comm')->where('comm_id',$data['community_id'])->find();
        if(isset($data['rela_comm_id']) and $data['rela_comm_id']>999){
            //如果有关联小区，取出关联小区
            $getComm['rela_comm'] = Db::table('comm')->where('comm_id',$data['rela_comm_id'])->value('comm_name');
        }else{
            $getComm['rela_comm'] = '';
        }
        $getComm['rela_ratio'] = isset($data['rela_ratio']) ? $data['rela_ratio'] : 1;
        $getComm['usage'] = isset($data['usage'])? $data['usage']:'';
        session('comm.comm_id',$data['community_id']);
        session('comm.comm_name',$getComm['comm_name']);

        $result = SalesModel::getRecordsByCommid($data);
//         $result = SalesModel::getRecordsByCommid($comm_id);
        
        
        
        if(count($result[1])>0){
            //如果能查询出数据
//             halt($result);
            $PL = new PriceLogic($result);
            $getPrice_result = $PL->getStatic($getComm,$data['price']);
            $getPrice_result['emplorers'] = config('emplorers');
            $getPrice_result['use'] = config('use');
            $getPrice_result['elevator'] = config('elevator');
            $getPrice_result['structuer'] = config('structuer');
            
            $this->assign('B',$getPrice_result);
            //===================登记查询记录===============================================
            $ins = QueryRecordsModel::insert_record($getPrice_result);      //返回插入的id,如果是重复数据没有插入，则返回0
            //===================把离散值过大的数据记录error_comm,以备改进=================================
            if($ins != 0){
                //如果有追加查询记录，再考虑是否把偏离值大的记录下来
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
                            'query_id'      =>  $ins,
                        ]);
                    }
                }
            }
            //===================还要分配一下权限===============================================
            //得到用户的权限
            $thisAuth = new \Auth();
            $auth['history'] = $thisAuth->check('phone/index/gethistory', session('user.user_id'));
            $auth['case'] = $thisAuth->check('phone/index/getcase', session('user.user_id'));
            $auth['insert'] = $thisAuth->check('phone/index/insertquery', session('user.user_id'));
            $auth['excel'] = $thisAuth->check('phone/index/createxcel', session('user.user_id'));
            $auth['look'] = $thisAuth->check('phone/index/look', session('user.user_id'));
            $auth['admin'] = $thisAuth->check('isadmin', session('user.user_id'));
            $auth['dispute'] = $thisAuth->check('phone/index/dispute', session('user.user_id'));
//             halt($auth);
//             $userrules = (new \Auth())->getAuthList(session('user.user_id'),1);
//             $auth['history'] = in_array('phone/index/gethistory',$userrules)?true:false;
//             $auth['case'] = in_array('phone/index/getcase',$userrules)?true:false;
//             $auth['insert'] = in_array('phone/index/insertquery',$userrules)?true:false;
//             $auth['excel'] = in_array('phone/index/createxcel',$userrules)?true:false;
//             $auth['look'] = in_array('phone/index/look',$userrules)?true:false;
//             $auth['admin'] = in_array('isadmin',$userrules)?true:false;
//             $auth['dispute'] = in_array('phone/index/dispute',$userrules)?true:false;
//             $auth['isadmin'] = (new \Auth())->check('isadmin', session('user.user_id'));
//             halt($auth['isadmin']);
            $this->assign('auth',$auth);
        }else{
            //如果未查询出数据
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
//         $commrelate = new CommRelateModel;
//         $commrelate->data([
//             'community_id'  => 1209002,
//             'rela_comm_id'  => 1209001,
//             'rela_ratio'    => 0.83,
//         ])->save();
//         $res = Db::query('SHOW COLUMNS FROM for_sale_property');
//         foreach ($res as $item){
//             dump($item['Field']);
//         }
//         halt($res);
//         $a='30456~35000';
// //         $b = preg_match('/\d+/',$a,$arr);
//         preg_match_all('/\d+/',$a,$arr);
//         dump($arr[0][0]);
//         $reportid = input('id');
//         $dbDB = new CPGRecordModel();
//         $resu = $dbDB->field('RName,RAddress,RMoney,ZID')->where('ZID',$reportid)->find();
// //         halt($resu); 
//         $this->assign('res',$resu);
//         return $this->fetch();
    }
    
    public function insertquery(){
        //插入询价记录
        $data = input();
        $result = $this->validate($data,'InsertQueryValidate');
        if(true !== $result){
            // 验证失败 输出错误信息
            return ['status'=>'输入不规范','msg'=> $result];
        };
        $data['Enquiry_Source'] = '估价师报价';
        $data['Enquiry_Date'] = date ( "Y-m-d");
        $data['PA_YearBuilt'] = date ('Y-m-d', strtotime($data['PA_YearBuilt'].'-1-1'));
        if(!isset($enq)){
            $enq = new TEnquiryModel();
        }
        //不同估价师，同一小区，同一用途，在一段时间内不允许报相同的价;但管理员不受此限
        $auth = new \Auth();
        if(!$auth->check('isadmin',session('user.user_id'))){
            //如果不是管理员
            if($enq->findEnqueryByCommAndDate($data)){
                return ['status'=>'报价雷同','msg'=> '在过去的一个月中已经有估价师对同一小区、同一用途作过相同报价，不再重复记录、'];
            }else{
                $insertEnguery = $enq->data($data)->save();
                return ['status'=>'登记成功','msg'=> '已将您的询价记录成功记入数据库中'];
            }
        }else{
            //这是管理员
            if(!$enq->findEnqueryByOfferAndDateAndComm($data)){
                $insertEnguery = $enq->data($data)->save();
                $num = $enq->getCount($data);
                return ['status'=>'登记成功','msg'=> '已将询价记录成功记入数据库中,本月'.$data['OfferPeople'].'已报价'.$num.'条'];
            }else{
                return ['status'=>'重复数据','msg'=> '您在过去的一个月中已经对同一小区、同一用途作过报价，不再重复记录、'];
            }
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