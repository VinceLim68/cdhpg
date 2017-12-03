<?php
namespace app\phone\controller;
use think\Controller;
use app\evalu\logic\MatchLogic;
use think\Db;
use app\evalu\logic\PriceLogic;
use app\evalu\controller\Common;
use app\evalu\model\ErrorCommModel;
use app\phone\model\QueryRecordsModel;

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
                            'memo'     =>  input('param.comm'),
                            'user_id'       =>  session('user.user_id'),
                            'user_name'     =>  session('user.user_name'),
                            'type'          =>  1,
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
            //判断一下是否有数据
            if($scope){
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
            }else{
                break;
            }
        }
        
        $getComm = Db::table('comm')->where('comm_id',$comm_id)->find();
        if($scope){
            $PL = new PriceLogic($result);
            $getPrice_result = $PL->getStatic();
    //         $getPrice_result['from_date'] = date("Y-m-d",strtotime("-6 month "));
            $getPrice_result['from_date'] = $scope[0]['first_acquisition_time'] ;
            $getPrice_result['ori_len'] = $records_num;
            $getPrice_result['comm'] = $getComm;
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
            
            //===========================计算面积房价散点图==================================
            $result_arr = $PL->getArr();
            $area_price_scatter['Xmin'] = 10000000;
            $area_price_scatter['Xmax'] = 0;
            $area_price_scatter['Ymin'] = 10000000;
            $area_price_scatter['Ymax'] = 0;
            foreach ($result_arr as $item)
            {
                if ($item['price'] > 0 and $item ['area'] > 0) {
                    $area_price [] = array('price'=>$item['price'],'area'=>$item ['area']);
                    if($item['price'] > $area_price_scatter['Xmax']){
                        $area_price_scatter['Xmax'] = $item['price'];
                    }
                    if($item['price'] < $area_price_scatter['Xmin']){
                        $area_price_scatter['Xmin'] = $item['price'];
                    }
                    if($item['area'] > $area_price_scatter['Ymax']){
                        $area_price_scatter['Ymax'] = $item['area'];
                    }
                    if($item['area'] < $area_price_scatter['Ymin']){
                        $area_price_scatter['Ymin'] = $item['area'];
                    }
                }
            }
            //计算最大值最小值
            $scatter_extend_r = config('scatter_extend_r');
            $area_price_scatter['X0'] = floor($area_price_scatter['Xmin']*(1-$scatter_extend_r)/1000)*1000;
            $area_price_scatter['X5'] = ceil($area_price_scatter['Xmax']*(1+$scatter_extend_r)/1000)*1000;
            $scatter_X_left = config('scatter_X_left');
            $area_price_scatter['Xunit'] = (100 - $scatter_X_left)/($area_price_scatter['X5']-$area_price_scatter['X0']);
            $area_price_scatter['Y0'] = floor($area_price_scatter['Ymin']*(1-$scatter_extend_r)/10)*10;
            $area_price_scatter['Y5'] = ceil($area_price_scatter['Ymax']*(1+$scatter_extend_r)/10)*10;
            $scatter_Y_top = config('scatter_Y_top');
            $area_price_scatter['Yunit'] = (100 - $scatter_Y_top)/($area_price_scatter['Y5']-$area_price_scatter['Y0']);
            
            //这是散点
            foreach ($area_price as $A_item){
                $x = ($A_item['price']-$area_price_scatter['X0'] )*$area_price_scatter['Xunit'] + $scatter_X_left;
                $y = ($A_item['area']-$area_price_scatter['Y0'] )*$area_price_scatter['Yunit'] + $scatter_Y_top;
                $A[] = array('x'=>$x,'y'=>$y);
            }
            //这是纵向的Y轴
            for ($i=0; $i<=5; $i++) {
                $x0 = $scatter_X_left + (100 - $scatter_X_left)/5*$i;
                $x1 = $x0;
                $y0 = $scatter_Y_top ;
                $y1 = 100;
                $value = $area_price_scatter['X0']+($area_price_scatter['X5']-$area_price_scatter['X0'])/5*$i;
                $A_line[] = array('x0'=>$x0,'x1'=>$x1,'y0'=>$y0,'y1'=>$y1,'val'=>$value,'t_x'=>$x0,'t_y'=>$y0-1);
                //'t_x','t_y'是文本显示的位置
            }
            //X轴
            for ($i=0; $i<=5; $i++) {
                $x0 = $scatter_X_left;
                $x1 = 100;
                $y0 = $scatter_Y_top + (100 - $scatter_Y_top)/5*$i;
                $y1 = $y0;
                $value = $area_price_scatter['Y0']+($area_price_scatter['Y5']-$area_price_scatter['Y0'])/5*$i;
                $A_line[] = array('x0'=>$x0,'x1'=>$x1,'y0'=>$y0,'y1'=>$y1,'val'=>$value,'t_x'=>0,'t_y'=>$y0);
                //'t_x','t_y'是文本显示的位置
            }
            
            $axes[] = array(
                'x0'=>$scatter_X_left,
                'x1'=>100,
                'y0'=>$scatter_Y_top,
                'y1'=>$scatter_Y_top,
                'val'=>'(房价 元/平方米)',
                't_x'=>100-1,
                't_y'=>$scatter_Y_top+4
            );          //X轴线
            $axes[] = array(
                'x0'=>$scatter_X_left,
                'x1'=>$scatter_X_left,
                'y0'=>$scatter_Y_top,
                'y1'=>100,
                'val'=>'(面积 平方米)',
                't_x'=>$scatter_X_left+1,
                't_y'=>100-1
            );
            $this->assign('A',$A);
            $this->assign('AL',$A_line);
            $this->assign('AX',$axes);
            
            //===========================计算楼层房价散点图==================================
            $floor_price_scatter['Xmin'] = 10000000;
            $floor_price_scatter['Xmax'] = 0;
            $floor_price_scatter['Ymin'] = 10000000;
            $floor_price_scatter['Ymax'] = 0;
            foreach ($result_arr as $item)
            {
                if ($item['price'] > 0 and $item ['total_floor'] > 0) {
                    $floor_price [] = array('price'=>$item['price'],'total_floor'=>$item ['total_floor']);
                    if($item['price'] > $floor_price_scatter['Xmax']){
                        $floor_price_scatter['Xmax'] = $item['price'];
                    }
                    if($item['price'] < $floor_price_scatter['Xmin']){
                        $floor_price_scatter['Xmin'] = $item['price'];
                    }
                    if($item['total_floor'] > $floor_price_scatter['Ymax']){
                        $floor_price_scatter['Ymax'] = $item['total_floor'];
                    }
                    if($item['total_floor'] < $floor_price_scatter['Ymin']){
                        $floor_price_scatter['Ymin'] = $item['total_floor'];
                    }
                }
            }
            //计算最大值最小值
            $floor_price_scatter['X0'] = floor($floor_price_scatter['Xmin']*(1-$scatter_extend_r)/1000)*1000;
            $floor_price_scatter['X5'] = ceil($floor_price_scatter['Xmax']*(1+$scatter_extend_r)/1000)*1000;
            $floor_price_scatter['Xunit'] = (100 - $scatter_X_left)/($floor_price_scatter['X5']-$floor_price_scatter['X0']);
            $floor_price_scatter['Y0'] = $floor_price_scatter['Ymin']-1;
            $floor_price_scatter['Y5'] = $floor_price_scatter['Ymax']+1;
            $floor_price_scatter['Yunit'] = (100 - $scatter_Y_top)/($floor_price_scatter['Y5']-$floor_price_scatter['Y0']);
            
            //这是散点
            foreach ($floor_price as $A_item){
                $x = ($A_item['price']-$floor_price_scatter['X0'] )*$floor_price_scatter['Xunit'] + $scatter_X_left;
                $y = ($A_item['total_floor']-$floor_price_scatter['Y0'] )*$floor_price_scatter['Yunit'] + $scatter_Y_top;
                $F[] = array('x'=>$x,'y'=>$y);
            }
            //这是纵向的Y轴
            for ($i=0; $i<=5; $i++) {
                $x0 = $scatter_X_left + (100 - $scatter_X_left)/5*$i;
                $x1 = $x0;
                $y0 = $scatter_Y_top ;
                $y1 = 100;
                $value = $floor_price_scatter['X0']+($floor_price_scatter['X5']-$floor_price_scatter['X0'])/5*$i;
                $F_line[] = array('x0'=>$x0,'x1'=>$x1,'y0'=>$y0,'y1'=>$y1,'val'=>$value,'t_x'=>$x0,'t_y'=>$y0-1);
                //'t_x','t_y'是文本显示的位置
            }
            //X轴
            for ($i=0; $i<=5; $i++) {
                $x0 = $scatter_X_left;
                $x1 = 100;
                $y0 = $scatter_Y_top + (100 - $scatter_Y_top)/5*$i;
                $y1 = $y0;
                $value = $floor_price_scatter['Y0']+($floor_price_scatter['Y5']-$floor_price_scatter['Y0'])/5*$i;
                $F_line[] = array('x0'=>$x0,'x1'=>$x1,'y0'=>$y0,'y1'=>$y1,'val'=>$value,'t_x'=>0,'t_y'=>$y0);
                //'t_x','t_y'是文本显示的位置
            }
            
            $Faxes[] = array(
                'x0'=>$scatter_X_left,
                'x1'=>100,
                'y0'=>$scatter_Y_top,
                'y1'=>$scatter_Y_top,
                'val'=>'(房价 元/平方米)',
                't_x'=>100-1,
                't_y'=>$scatter_Y_top+4
            );          //X轴线
            $Faxes[] = array(
                'x0'=>$scatter_X_left,
                'x1'=>$scatter_X_left,
                'y0'=>$scatter_Y_top,
                'y1'=>100,
                'val'=>'(总楼层)',
                't_x'=>$scatter_X_left+1,
                't_y'=>100-1
            );
            $this->assign('F',$F);
            $this->assign('FL',$F_line);
            $this->assign('FX',$Faxes);
            //===================把离散值过大的数据记录error_comm,以备改进=================================
            if($getPrice_result['std_r'] > config('std_r_limit')){
                $errorcomm = ErrorCommModel::create([
                    'memo'          =>  $getPrice_result['std_r'],
                    'user_id'       =>  session('user.user_id'),
                    'user_name'     =>  session('user.user_name'),
                    'type'          =>  2,
                    'comm_name'     =>  $getPrice_result['comm']['comm_name'],
                    'comm_id'       =>  $getPrice_result['comm']['comm_id'],
                ]);
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