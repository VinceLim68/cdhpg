<?php
namespace app\phone\controller;
use think\Controller;
use app\evalu\logic\MatchLogic;
use think\Db;
use app\evalu\logic\PriceLogic;
use app\evalu\model\MissCommModel;
use app\evalu\controller\Common;

class Index extends Common {
    
    public function index() {
        //手机询价界面
        return $this->fetch();
    }
    
    public function getCommName(){
        //把输入的小区名称转化成相应的小区编号
        if (request()->isGet()) {
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
                        $misscomm = MissCommModel::create([
                            'miss_comm'  =>  input('param.comm'),
                            'user' =>  'test'
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
        //查询范围在最新的30万个记录中，但如果查询结果数量少于100个，则扩大30万个记录
        $sele_times = 1;                                                    //计数：查询次数
        $records_num = 0;                                                   //累计：获得记录数
        $min_base_records = config('min_base_records');                     //最低记录数
        $select_records_per_time = config('select_records_per_time');       //每次增加的查询范围
        while ($records_num < $min_base_records) {
            $scope = Db::table('for_sale_property')
                ->field('first_acquisition_time')
                ->order('first_acquisition_time desc')
                ->limit($sele_times*$select_records_per_time,1)
                ->select();
            $result = Db::table('for_sale_property')
                ->field('id,floor_index,total_floor,price,area,builded_year')
                ->where('community_id',$comm_id)
                ->where("first_acquisition_time", "> time", $scope[0]['first_acquisition_time'])
                ->select();
//             $result = Db::table('for_sale_property')
//                 ->field('id,floor_index,total_floor,price,area,builded_year')
//                 ->where('community_id',$comm_id)
//                 ->where("first_acquisition_time", "> time", strtotime("-6 month "))
//                 ->select();
            $records_num = count($result);
            $sele_times += 1;
        }
        $PL = new PriceLogic($result);
        $getPrice_result = $PL->getStatic();
//         $getPrice_result['from_date'] = date("Y-m-d",strtotime("-6 month "));
        $getPrice_result['from_date'] = $scope[0]['first_acquisition_time'] ;
        $getPrice_result['ori_len'] = $records_num;
        $getPrice_result['comm'] = Db::table('comm')->where('comm_id',$comm_id)->find();
        $getPrice_result['price'] = $price;
        $getPrice_result['priceByDeal'] = 0;
        //覆盖率
        $getPrice_result['coverage'] = round($getPrice_result['len']/$getPrice_result['ori_len']*100,2);
        //标准差系数
        $getPrice_result['std_r'] = round($getPrice_result['std']/$getPrice_result['mean']*100,2);
        //这里是处理有输入成交价时
        if($price > 0){
            if($price > $getPrice_result['max'] or $price < $getPrice_result['min']){
                $getPrice_result['priceByDeal'] = -1;           //-1表示异常，0表示没有提供成交价
            }else{
                $getPrice_result['priceByDeal'] = $PL->dealPrice($price, $getPrice_result);
            }
        }
        //==============================以下是计算盒须图====================================
        $getPrice_result['X']= config('X');
        $getPrice_result['box_width']= config('box_width');
        $getPrice_result['Y_padding']= config('Y_padding');

        $getPrice_result['Q0v'] = max(
            $getPrice_result['min'],
            $getPrice_result['v25']-($getPrice_result['v75']-$getPrice_result['v25'])*1.5
            );
        $getPrice_result['Q4v'] = min(
            $getPrice_result['max'],
            $getPrice_result['v75']+($getPrice_result['v75']-$getPrice_result['v25'])*1.5
            );
        $unit = (100-$getPrice_result['Y_padding'])/($getPrice_result['max']-$getPrice_result['min']);
        $getPrice_result['Qmin'] = $getPrice_result['Y_padding'];
        //         $getPrice_result['Qmin'] = 1;
        $getPrice_result['Qmax'] = 99;
        //         $getPrice_result['Q0'] = ($getPrice_result['Q0v']-$getPrice_result['min'])*$unit;
        $getPrice_result['Q0'] = ($getPrice_result['Q0v']-$getPrice_result['min'])*$unit + $getPrice_result['Y_padding'];
        //         if($getPrice_result['Q0'] == 0){
        //             $getPrice_result['Q0'] = 1;
        //         }
        $getPrice_result['Q1'] = ($getPrice_result['v25']-$getPrice_result['min'])*$unit + $getPrice_result['Y_padding'];
        $getPrice_result['Q2'] = ($getPrice_result['median']-$getPrice_result['min'])*$unit + $getPrice_result['Y_padding'];
        $getPrice_result['Q3'] = ($getPrice_result['v75']-$getPrice_result['min'])*$unit + $getPrice_result['Y_padding'];
        $getPrice_result['Q4'] = ($getPrice_result['Q4v']-$getPrice_result['min'])*$unit + $getPrice_result['Y_padding'];
        if($getPrice_result['Q4'] >= 99){
            $getPrice_result['Q4'] = 99;
        }
        
        //============================以下是计算直方图==============================
        $getPrice_result['x_unit'] = ($getPrice_result['X'] - 10)/max($getPrice_result['barChart']);
        $getPrice_result['y_unit'] = (100 - $getPrice_result['Y_padding'])/config('barChart_num');
        $this->assign('B',$getPrice_result);
        return $this->fetch();

    }
    
    public function dispute(){
        //在查询结果中，如果有争议可以进行记录
        $arr = [1,2,3,4,5,6,7,8,9,10,11];
         PriceLogic::test_array_multisort();
    }
    
    public function test(){
//         $request = Request::instance();
//         echo "当前模块名称是" . $request->module();
//         返回控制器名
    }
    
    public function boxPlot(){
        $getPrice_result =array(
            'median' => 49863,
              'min' => 44640,
              'max' =>  54347,
              'v75' =>  51145,
              'v25' =>  48101,
//             'Y' => 40,                  //盒须图起始的Y的位置，即padding-top,单位是%
//             'box_heigh' =>30,
                'X'     => 75,
                'box_width' =>5,
                'Y_padding' =>5,            //相当于top_padding
        );
        $getPrice_result['Q0v'] = max(
            $getPrice_result['min'],
            $getPrice_result['v25']-($getPrice_result['v75']-$getPrice_result['v25'])*1.5
            );
        $getPrice_result['Q4v'] = min(
            $getPrice_result['max'],
            $getPrice_result['v75']+($getPrice_result['v75']-$getPrice_result['v25'])*1.5
            );
        $unit = (100-$getPrice_result['Y_padding'])/($getPrice_result['max']-$getPrice_result['min']);
        $getPrice_result['Qmin'] = $getPrice_result['Y_padding'];
//         $getPrice_result['Qmin'] = 1;
        $getPrice_result['Qmax'] = 99;
//         $getPrice_result['Q0'] = ($getPrice_result['Q0v']-$getPrice_result['min'])*$unit;
        $getPrice_result['Q0'] = ($getPrice_result['Q0v']-$getPrice_result['min'])*$unit + $getPrice_result['Y_padding'];
//         if($getPrice_result['Q0'] == 0){
//             $getPrice_result['Q0'] = 1;
//         }
        $getPrice_result['Q1'] = ($getPrice_result['v25']-$getPrice_result['min'])*$unit + $getPrice_result['Y_padding'];
        $getPrice_result['Q2'] = ($getPrice_result['median']-$getPrice_result['min'])*$unit + $getPrice_result['Y_padding'];
        $getPrice_result['Q3'] = ($getPrice_result['v75']-$getPrice_result['min'])*$unit + $getPrice_result['Y_padding'];
        $getPrice_result['Q4'] = ($getPrice_result['Q4v']-$getPrice_result['min'])*$unit + $getPrice_result['Y_padding'];
        if($getPrice_result['Q4'] >= 99){
            $getPrice_result['Q4'] = 99;
        }
        $this->assign('B',$getPrice_result);
        //dump($getPrice_result);
        return $this->fetch();
    }
}