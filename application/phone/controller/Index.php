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

class Index extends Common {
    
    public function index() {
        //手机询价界面
        return $this->fetch();
    }
    
    public function getCommName(){
        //把输入的小区名称转化成相应的小区编号
        if (request()->isPost()) {
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
                //1查询数据
                $commnames = MatchLogic::matchSearch(input('param.comm'));
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
        $result = SalesModel::getRecordsByCommid($comm_id);
        $getComm = Db::table('comm')->where('comm_id',$comm_id)->find();
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
//         $request = Request::instance();
//         echo "当前模块名称是" . $request->module();
//         返回控制器名
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
        //同一估价师，同一小区，同一用途，在一段时间内不允许重复报价
        //不同估价师，同一小区，同一用途，在一段时间内不允许报相同的价;
        return ['status'=>'成功','msg'=> $data];
    }
    
}