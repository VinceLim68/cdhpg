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
// use app\phone\model\TCaseCfgModel;
use app\evalu\logic\CreatExcelLogic;
// use app\phone\model\CPGRecordModel;
use app\evalu\model\CommRelateModel;
use app\phone\model\EasyPGXjModel;
use app\phone\model\EasyCjalkModel;
use app\evalu\model\Comm;
use app\phone\model\GeneralLayoutModel;
use app\evalu\model\CommaddressModel;
use app\evalu\logic\LoginLogic;
use app\api\controller\Wxloginaction;
use app\evalu\model\SalesNowModel;

//手机端的询价系统
class Index extends Common {
    
    public function index() {
        //手机询价界面
        $auth = $this->getAuth();
        $this->assign('auth',$auth);
        return $this->fetch();
    }
    
    
    //把输入的小区名称转化成相应的小区编号，如果有多个匹配小区id，则返回页面手工进行选择
    //得到小区id后，再跳转到getCommChild进行小区内的子分类选择
    public function getCommName(){
        if (request()->isPost() or request()->isGet()) {
            $input = input();
//             halt($input);
            $result = $this->validate($input,'GetCommNameValidate');
            if (true !== $result) {
                // 验证失败 输出错误信息
                $this->error($result);
                exit ();
            } else {
//                 $input = input();
//                 halt($input);
                //把传来的参数解码一下，这里设计的函数round_decode会自动辨别有没有编码
                foreach ($input as $key=>$value){
                    $input[$key]=round_decode($value);
                }
                
                $return = $this->getCommNameAction($input);
                switch ($return['status']) {
                    case 'macth no comm and insert error record failed':
                        $this->error($return['message']);
                        break;
                    case 'macth no comm':
                        $this->error($return['message']);
                        break;
                    case 'macth many comms':
                        $this->assign ([
                            'fields'=>$return['commArr'],
                            'price'=>isset($input['price'])?$input['price']:0,
                            'input'=>base_encode(json_encode($input)),
                        ]);
                        return $this->fetch();
                        break;
                    default:            //匹配到唯一，或者直接匹配到地址
//                         halt($input);
                        if(!input('price')){
                            $this->redirect('getCommChild', ['comm_id' => $return['commnames'][0]['comm_id'],'input'=>base_encode(json_encode($input))]);
                        }else{
                            $this->redirect('getCommChild', ['comm_id' => $return['commnames'][0]['comm_id'],'input'=>base_encode(json_encode($input)),'price'=>$input['price']]);
                        };
                        break;
                }
            }
        }
    }
    
    //通过微信来询价，基本上就是重写一次
    public function getCommNameByWX(){
        $input = input();
        //微信传递过来的，都是经过两次encodeURI的数据，要解码一下
        //传递过来的参数nickname,machine,comm,lx(openid),lx2(formId) 
        foreach ($input as $key=>$value){
            $input[$key]=round_decode($value);
        }
        if (LoginLogic::isWeixin() and  isset($input['nickname']) and '' != trim($input['nickname'])) {
            $result = $this->validate(input(),'GetCommNameValidate');
            if (true !== $result) {
                // 验证失败 输出错误信息
                $this->error($result);
                exit ();
            } else {
                $return = $this->getCommNameAction($input);
                switch ($return['status']) {
                    case 'macth no comm and insert error record failed':
                        $this->error($return['message']);
                        break;
                    case 'macth no comm':
                        $this->error($return['message']);
                        break;
                    case 'macth many comms':
                        $this->assign ([
                            'fields'=>$return['commArr'],
                            'price'=>isset($input['price'])?$input['price']:0,
                            'input'=>base_encode(json_encode($input)),
                        ]);
                        return $this->fetch('getCommName');
                        break;
                    default:            //匹配到唯一，或者直接匹配到地址
                        if(!input('price')){
                            $this->redirect('getCommChild', ['comm_id' => $return['commnames'][0]['comm_id'],'input'=>base_encode(json_encode($input))]);
                        }else{
                            $this->redirect('getCommChild', ['comm_id' => $return['commnames'][0]['comm_id'],'input'=>base_encode(json_encode($input)),'price'=>$input['price']]);
                        };
                        break;
                }
            }
        }
    }
    
    //封装匹配小区名称功能，把不规范的名称转为规范的名称
    //     返回值：
    //      1、直接匹配地址成功
    //      2、没有查询到小区名称，且无法记录错误信息
    //      3、没有查询到小区名称，记录了错误信息
    //      4、匹配到多个小区名称，返回所有匹配的小区表中的记录
    //      5、匹配到一个小区名称
    public function getCommNameAction($input){
        //1把comm存入session中
        session('user.comm',$input['comm']);
        
        //先匹配地址
        $address = MatchLogic::matchIDByAddress($input['comm']);

//         地址匹配成功
        if(isset($address['comm_id']) and $address['comm_id']>999 ){
            $commnames[] = (new Comm())->field ( "comm_id,comm_name,pri_level,keywords" )
            ->where('comm_id', $address['comm_id'])
            ->find()
            ->toArray();
            session('user.comm',$commnames[0]['comm_name']);
            $return['status']='match address success';
            $return['commnames']=$commnames;
        }else{
//              地址未匹配成功，进行小区名称匹配
//              返回$pickitem数组，每个元素中包含comm_id,comm_name,pri_level,keywords
            $commnames = MatchLogic::matchSearch($input['comm']);
            if(!$commnames){
                //如果还是没有查到，记录到miss_comm表中去
                try{
                    $errorcomm = ErrorCommModel::create([
                        'memo'     =>  '没有小区名',
                        'user_id'       =>  session('user.user_id'),
                        'user_name'     =>  session('user.user_name'),
                        'type'          =>  1,
                        'comm_name'     =>  $input['comm'],
                    ]);
                }catch(\Exception $e){
                    $return['status'] = 'macth no comm and insert error record failed';
                    $return['message'] = '没有查询到叫"'.$input['comm'].'"的地方,追加错误信息时未成功';
                    return $return;
                }
                $return['status'] = 'macth no comm';
                $return['message'] = '没有查询到叫"'.$input['comm'].'"的地方';
            }elseif(count($commnames)>1){
                //如果匹配到多个小区名称，列表展示，让用户手动挑选后，再转入子功能分类进行选择
                $commArr = [];      //取出完整的数据
                foreach ($commnames as $comm){
                    $commArr[] = Db::table('comm')->where('comm_id',$comm['comm_id'])->find();
                }
                $return['status'] = 'macth many comms';
                $return['commArr'] = $commArr;
            }else{
                //只匹配到一个小区名称
                $return['status'] = 'macth one comm';
                $return['commnames'] = $commnames;
            }
        }
        
        return $return;
    }

    //处理小区的子分类，如果有子分类进行选择，如果没有则跳转
    public function getCommChild($comm_id ='',$price = 0){
        $commrelate = new CommRelateModel;
        $list = $commrelate->where('community_id',$comm_id)->select()->toArray();
        $my = [];
        $input = input();
//         halt($input);
        if(empty($list)){
            //没有关联规则就跳转
            $my['community_id'] = $comm_id;
            $my['price'] = $price;
            $my += $input;
            $this->redirect('getPrice', $my);
        }else{
            //如果有关联规则则要进行选择，需要关联其他小区查询
            
            //先把原始数据当作一个选项
            $ori_item = [];
            $ori_item['community_id'] = $comm_id;
            $ori_item['price'] = $price;
            $ori_item['usage'] = base_encode('原始数据');
            $my[] = $ori_item;
            
            //再取出关联规则
            foreach ($list as $item1){
                $item['rela_id'] = $item1['id'];        //把关联规则的id保存下来
                $item['community_id'] = $item1['community_id'];
                $item['usage'] = base_encode($item1['usage']);
                $item['price'] = $price;
                $item += $input;
                $my[] = $item;
            }
//             dump($my);
            $this->assign('fields',$my);
            return $this->fetch();
        }
    }
    
    //核心的查询功能，根据comm_id求出评估价
    public function getPrice(){
        //$comm_id ='',$price = 0
        //通过小区编号求取相应的报价参数
        //1.如何有当月的报价记录，就直接读取
        //2.如果没有，就查询挂牌数据库进行计算，并把计算结果写入查询记录中去
        //或者如果有成交记录，也可以重新计算，并把成交记录记入成交表中去
        $data = input();
//         halt($data);
//         halt(json_decode(base_decode($data['input']))->is_now);
        if(isset($data['usage'])){$data['usage'] = base_decode($data['usage']);};
        if(!isset($data['price'])){$data['price'] = 0;};
        
        if(isset($data['rela_id'])){
            //如果有传过来关联规则的id,取出具体内容
            $rela_data = (new CommRelateModel())->where('id',$data['rela_id'])->find()->toArray();
            $data = array_merge($rela_data,$data);
        }
        
        //通过id找小区名称，写入session中，以便传到前端
        $getComm = Db::table('comm')->where('comm_id',$data['community_id'])->find();
        if(isset($data['rela_comm_id']) and $data['rela_comm_id']>999){
            //如果有关联小区，取出关联小区的名称
            $getComm['rela_comm'] = Db::table('comm')->where('comm_id',$data['rela_comm_id'])->value('comm_name');
        }else{
            $getComm['rela_comm'] = '';
        }
        $getComm['rela_ratio'] = isset($data['rela_ratio']) ? $data['rela_ratio'] : 1;
        $getComm['usage'] = isset($data['usage'])? $data['usage']:'';
        session('comm.comm_id',$data['community_id']);
        session('comm.comm_name',$getComm['comm_name']);
//         dump($data);
        if(isset(json_decode(base_decode($data['input']))->is_now)){
            $result = SalesNowModel::getRecordsByCommid($data);
        }else{
            $result = SalesModel::getRecordsByCommid($data);
        }
        
//         $result = SalesModel::getRecordsByCommid($comm_id);
        
        
        
        if(count($result[1])>0){
            //如果能查询出数据
            $PL = new PriceLogic($result);
            $getPrice_result = $PL->getStatic($getComm,$data['price']);
            $getPrice_result['emplorers'] = config('emplorers');
            $getPrice_result['use'] = config('use');
            $getPrice_result['elevator'] = config('elevator');
            $getPrice_result['structuer'] = config('structuer');
            
            $this->assign([
                'B'         =>      $getPrice_result,
                'comm_info' =>      $data,
            ]);
//             dump($getPrice_result['area_price_scatter']);
//             dump($getPrice_result['comm']);
            //===================登记查询记录===============================================
            $ins = QueryRecordsModel::insert_record($getPrice_result);      //返回插入的id,如果是重复数据没有插入，则返回0
            //===================把离散值过大的数据记录error_comm,以备改进=========================
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
                };
                //把数据量偏少的记录下来
                if($getPrice_result['ori_len'] < config('min_base_records')){
                    if($getPrice_result['comm']['comm_name']){
                        $errorcomm = ErrorCommModel::create([
                            'memo'          =>  $getPrice_result['ori_len'],
                            'user_id'       =>  session('user.user_id'),
                            'user_name'     =>  session('user.user_name'),
                            'type'          =>  4,
                            'comm_name'     =>  $getPrice_result['comm']['comm_name'],
                            'comm_id'       =>  $getPrice_result['comm']['comm_id'],
                            'query_id'      =>  $ins,
                        ]);
                    }
                };
                //自动插入我的询价记录
                $this->autoInsertQueryIntoEasyPG($getPrice_result);
            }
            //===================还要分配一下权限===============================================
            //得到用户的权限
            $auth = $this->getAuth();
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
        
        //在这里发消息模板
        $iswx = LoginLogic::isWeixin();
        if(isset($data['input']) and  $iswx){
            $wxinfo = json_decode(base_decode($data['input']));
            $wx = new Wxloginaction();
            $wx->sendTemplateMessage($wxinfo,$getPrice_result);
            
        }
        $this->assign('iswx',$iswx);
        $this->assign('input',$data['input']);
        return $this->fetch();

    }
    
    //获得权限
    private  function  getAuth(){
        $thisAuth = new \Auth();
        $auth['history'] = $thisAuth->check('phone/index/gethistory', session('user.user_id'));
        $auth['case'] = $thisAuth->check('phone/index/getcase', session('user.user_id'));
        $auth['insert'] = $thisAuth->check('phone/index/insertquery', session('user.user_id'));
        $auth['excel'] = $thisAuth->check('phone/index/createxcel', session('user.user_id'));
        $auth['look'] = $thisAuth->check('phone/index/look', session('user.user_id'));
        $auth['admin'] = $thisAuth->check('isadmin', session('user.user_id'));
        $auth['dispute'] = $thisAuth->check('phone/index/dispute', session('user.user_id'));
        $auth['inputFiles'] = $thisAuth->check('phone/index/inputFiles', session('user.user_id'));
        $auth['inputAddress'] = $thisAuth->check('phone/index/inputAddress', session('user.user_id'));
        $auth['showSaleList'] = $thisAuth->check('phone/index/showSaleList', session('user.user_id'));
        return $auth;
    }
    
    //自动插入查询记录
    private function autoInsertQueryIntoEasyPG($B){
        $gjs = ['林晓','匡宾','李志林',"李成军","李智婕A","游加丽","吴丽敏","吴福海","陈淑华","张少芬",'admin',
            'hope','Aimee','张S FEN','阿敏','鑫贵人生','大叔','k匡','lizhilin','csh','成军','泊淼～张'];
        $name = session('user.user_name');              //取当前用户名
        if(!in_array($name,$gjs)){                      //如果非估价师
            $employers = config('emplorers');
            //根据EasyPG的询价管理库表的字段把内容转换一下
            $renameData['Xjyjrname'] = '林晓';
            $renameData['Xjxqname'] = $B['comm']['comm_name'];
            $renameData['Xjbjdjms'] = $B['mortgagePrice'];
            if(in_array($name, $employers)){
                $renameData['Xjrname'] = $name;
            }else{
                $renameData['Xjrname'] = '外部客户';
            }
            $renameData['Mark'] = $B['len']."个数据，挂牌价格".$B['min']."-".$B['max']."元/平方米";
            $renameData['Xjxqaddr'] = "自助询价结果，仅供参考";
//             $renameData['Xjxqaddr'] = "二手房提供合同后的最高价".$B['dealPrice'];
            $renameData['Xjyt'] = '住宅';
            $renameData['Xjlcs'] = $B['avg_floor_index'];
            $renameData['Xjzlcs'] = $B['avg_total_floor'];
            $renameData['Xjxqjcnf'] = $B['avg_builded_year'];
            $renameData['Xjxqjzjg'] = $B['avg_total_floor'] > 7 ? '钢混结构' : '砖混结构';
            $renameData['Xjynxqdt'] = $B['avg_total_floor'] > 8 ? '带电梯' : '无电梯';
            $renameData['Xjfcmj'] = $B['avg_area'];
            //以下几个是为了验证输入信息
            $renameData['Enquiry_CellName']	= $renameData['Xjxqname'];
            $renameData['Apprsal_Up'] = $renameData['Xjbjdjms'];
            $renameData['Enquiry_PmName'] = $renameData['Xjrname'];
            $renameData['Apprsal_Use'] = $renameData['Xjyt'] ;
            $renameData['OfferPeople'] = $renameData['Xjyjrname'];
            $result = $this->validate($renameData,'InsertQueryValidate');
            if(true !== $result){
                // 验证失败 输出错误信息
                return ['status'=>'输入不规范','msg'=> $result];
            }else{
                if(!isset($EasyPGXj)){
                    $EasyPGXj = new EasyPGXjModel();
                };
                if(!$EasyPGXj->findEnqueryByOfferAndDateAndComm($renameData)){
                    $insertEnguery = $EasyPGXj->insertRecord($renameData);
                    return ['status'=>'登记成功','msg'=> '已将询价记录成功记入数据库中'];
                }
            }
        };
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
    
    //把询价记录插入至easyPg的询价库中去
    public function insertQueryIntoEasyPG(){
        $data = input();
        $result = $this->validate($data,'InsertQueryValidate');
        if(true !== $result){
            // 验证失败 输出错误信息
            return ['status'=>'输入不规范','msg'=> $result];
        };
        //根据EasyPG的询价管理库表的字段把内容转换一下
        $renameData['Xjyjrname'] = $data['OfferPeople'];
        $renameData['Xjxqname'] = $data['Enquiry_CellName'];
        $renameData['Xjbjdjms'] = $data['Apprsal_Up'];
        if(trim($data['Enquiry_PmName'])==''){
            $renameData['Xjrname'] = '电话客户';
        }else{
            $renameData['Xjrname'] = $data['Enquiry_PmName'];
        }
        $renameData['Mark'] = $data['Remark'];
        $renameData['Xjxqaddr'] = $data['PA_Located'];
        $renameData['Xjyt'] = $data['Apprsal_Use'];
        $renameData['Xjlcs'] = $data['PA_Level'];
        $renameData['Xjzlcs'] = $data['Enquiry_Layout'];
        $renameData['Xjxqjcnf'] = $data['PA_YearBuilt'];
        $renameData['Xjxqjzjg'] = $data['PA_Structure'];
        $renameData['Xjynxqdt'] = $data['PA_Elevator'];
        $renameData['Xjfcmj'] = $data['Xjfcmj'];
        
        
//         if(!isset($enq)){
        if(!isset($EasyPGXj)){
            $EasyPGXj = new EasyPGXjModel();
        }
        //不同估价师，同一小区，同一用途，在一段时间内不允许报相同的价;但管理员不受此限
        $auth = new \Auth();
        if($auth->check('isadmin',session('user.user_id'))){
            //这是管理员
            if(!$EasyPGXj->findEnqueryByOfferAndDateAndComm($renameData)){
                $insertEnguery = $EasyPGXj->insertRecord($renameData);
                $num = $EasyPGXj->getCount($renameData);
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
        //从旧系统中查询
//         if(!isset($enq)){
//             $enq = new TEnquiryModel();
//         }
//         $historyEnquery = $enq->getEnqueryByCommAndDate();
        
        //再从新系统中查询
        $EasyXj = new EasyPGXjModel();
//         $historyEnquery1 = $EasyXj->getEnqueryByCommAndDate();
        $historyEnquery = $EasyXj->getEnqueryByCommAndDate();
        //合并两个数组，再生成html代码
//         $historyEnquery = array_merge ($historyEnquery1,$historyEnquery);
        $html = '';
        if(count($historyEnquery)==0){
            $html .= '<tr><td class="font-small">'.config('historyDays').'天内没有'.session('user.comm').'的询价记录</td></tr>';
        }else{
            foreach ($historyEnquery as $rec){
                $html .= '<tr><td class="font-small">'.$rec['OfferPeople'].'===>'.$rec['Enquiry_PmName'];
                $html .= '('.date ( 'Y-m-d', strtotime ( $rec['Enquiry_Date']) ).')';
                $html .= '</br>'.$rec['Enquiry_CellName'];
                if(trim($rec['Apprsal_Use'])!=''){
                    $html .= '-'.$rec['Apprsal_Use'];
                }
                $html .= ' : '.$rec['Apprsal_Up'];
                if(trim($rec['PA_Located'])!=''){
                    $html .= '</br>地址:'.$rec['PA_Located'];
                }
                if(trim($rec['Remark'])!=''){
                    $html .= '</br>备注:'.$rec['Remark'];
                }
                $html_append = '';
                if($rec['Xjfcmj']!=0 ){
                    $html_append .= '  面积:'.$rec['Xjfcmj'].';';
                }
                if($rec['Xjzlcs']!=0 or trim($rec['Xjlcs'])!=''){
                    $html_append .= '  楼层:';
                    if(trim($rec['Xjlcs'])!=''){
                        $html_append .= '第'.trim($rec['Xjlcs']).'层';
                    }
                    if($rec['Xjzlcs']!=0){
                        $html_append .= '/共'.$rec['Xjzlcs'].'层';
                    }
                    $html_append .= ';';
                }
                if(trim($rec['Xjxqjcnf'])!=''){
                    $html_append .= '  建成年份:'.$rec['Xjxqjcnf'].';';
                }
                if(trim($html_append)!=''){
                    $html .= '</br>'.$html_append;
                }
                
                $html .= '</td></tr>';
//                 $html .= '<tr><td class="font-small">'.$rec['Enquiry_PmName'].'('.date ( 'Y-m-d', strtotime ( $rec['Enquiry_Date']) ).')';
//                 $html .= '------'.$rec['Enquiry_CellName'].'-'.$rec['Apprsal_Use'];
//                 $html .= '</br>'.$rec['OfferPeople'].'------'.$rec['Apprsal_Up'].',备注:'.$rec['Remark'].'</td></tr>';
            }
        }
        return $html;
//         return $historyEnquery;

        
    }
    
    public function getCase(){
//         if(!isset($case)){
//             $case = new TCaseCfgModel();
//         }
//         $cases = $case->getCaseByNameAndDate();
//         return $cases;
        $case = new EasyCjalkModel();
        $cases = $case->getCaseByNameAndDate();
        $html = '';
        if(count($cases)==0){
            $html .= '<tr><td class="font-small">'.config('historyDays').'天内没有'.session('user.comm').'的成交案例</td></tr>';
        }else{
            foreach ($cases as $rec){
                $html .= '<tr><td class="font-small">'.$rec['Case_Name'].'-'.$rec['Case_Type'].':';
//                 $html .= '------'.$rec['Case_Located'].'(建成：'.date ( 'Y', strtotime ( $rec['Case_Cmpl_Years']) ).'年)';
                $html .= '------'.$rec['Case_Located'].'(建成：'.$rec['Case_Cmpl_Years'].'年)';
                $html .= '</br>成交价:'.round($rec['Case_TrxPrice']).'(成交日期:'.date ( 'Y-m-d', strtotime ( $rec['Case_TrxDate']) ).')------'.$rec['Opertor'].'</td></tr>';
            }
        }
        return $html;
        
    }
    
    public function creatExcel(){
        //生成excel文件
//         halt(input());
        return CreatExcelLogic::creatExcel();
    }

    //移动端上传、删除图片。
    public function inputFiles(){
        $param = input();
//         dump($param);
        $layout = new GeneralLayoutModel();
        $files = request()->file('images');
//             dump($files);
        if($files){
            foreach($files as $file){
                // 移动到框架应用根目录/public/layout/ 目录下
                $info = $file->validate(['size'=>9240000,'ext'=>'jpg,png,gif,jpeg'])
                            ->rule('uniqid')        //这是取消日期子目录
                            ->move(ROOT_PATH . 'public' . DS . 'layout');//
                if($info){
                    // 成功上传后 获取上传信息
                    // 输出 20160820/42a79759f284b767dfcb2a0197904287.jpg
                    $savename = $info->getSaveName();
                    $filesize = $info->getSize();
                    Db::table('general_layout')->insert([
                        'comm_id' => $param['community_id'], 
                        'img_url' => $savename,
                        'img_size'=> $filesize,
                    ]);
                }else{
                    // 上传失败获取错误信息
                    echo $file->getError();
                }
            }
        }
        if(isset($param['delfile'])){
            $tmp = explode('/', $param['delfile']);
            $filename = $tmp[count($tmp) - 1];
			$url = $_SERVER['DOCUMENT_ROOT'].$param['delfile'];  //在删除时要使用绝对路径才能在服务器里成功
			unlink($url);
            $layout->where('comm_id',$param['community_id'])
                    ->where('img_url',$filename)
                    ->delete();
        }
        $imgs = $layout
            ->alias('l')
//             ->join('comm c','c.comm_id = l.comm_id')
            ->where('l.comm_id',$param['community_id'])
            ->select();
//         dump($imgs);
        $comm_info = Db::table('comm')->where('comm_id',$param['community_id'])->find();
        $this->assign([
            'data'  =>  $imgs,
            'param' => $param,
            'comm_info'=>$comm_info,
        ]);
        return $this->fetch();
    }
    
    //移动端采集小区地址
    public function inputAddress(){
        $param = input();
//         dump($param);
        $ca = new CommaddressModel();
        $findresult = $ca->alias('a')
            ->join('comm c','c.comm_id = a.comm_id')
            ->where('a.comm_id',$param['community_id'])
            ->field('a.comm_id,city,a.region,road,doorplate,type,
                    buildYear,floors,elevator,structure,c.comm_name,block,
                    keywords')
            ->order(['road','doorplate'])
            ->select();
//         dump($findresult);
        $commInfo = (new Comm())->where('comm_id',$param['community_id'])->find()->toArray();
        if($findresult){
            $getconfigs = action('evalu/comms/getConfig');
            $findresult = $findresult->toArray();
            $findresult = array_merge($findresult,$getconfigs);
            $findresult['commInfo'] = $commInfo;
        }else{
            $findresult = 0;
        }
//         halt($findresult);
        $this->assign([
            'param' => $param,
            'findresult'=> $findresult,    //往js里传递数组，要转化成json
//             'commInfo'  =>  $commInfo,
        ]);
        return $this->fetch();
    }
   
    //移动端展示数据列表
    public function showSaleList(){
        $data = input();
        //取挂牌数据
        if(!isset($data['where']) or trim($data['where'])==''){
            $data['where'] = 'community_id = '.$data['community_id'];
        }else{
            //避免重复加入community_id = 的条件
            if(stripos($data['where'], 'community_id') === false){
                $data['where'] .= ' AND community_id = '.$data['community_id'];
            }
        }
        $data = action('evalu/Sales/datahandle',  ['data' => $data]);
//         dump($data);
        $saleslist = action('evalu/Sales/getSalesByArray',  ['data' => $data]);
         
        $fields = Db::query('SHOW COLUMNS FROM for_sale_property');
        $title = ['序号','标题','小区','名称','单价','总价','总层','建成'];
        $this->assign([
            'saleslist'=>$saleslist,
            'title'=>$title,
            'data'=>$data,
            'fields'=>$fields,
            'update'=>false,        //只要设置了updata,不管值是什么，页面上都不会显示修改的功能。不能让手机端有修改功能。
        ]);

        return $this->fetch();
    }
}