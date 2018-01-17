<?php
namespace app\evalu\logic;

class PriceLogic
{
    /*
     * 用于实现求取询价结果的各种功能
     * $arr2 = array_column($arr, 'name');
     * 
     */
    private $price = [];
    private $arr = [];
    private $date;
//     private $arr_cleared = [];
    
    public function __construct($collection){
//         halt($collection);
        //按价格给数据排序
        array_multisort(array_column ( $collection[1], 'price' ), SORT_ASC, $collection[1]);
        $this->arr = $collection[1];
        $this->price = array_column ($this->arr, 'price' );
        $this->date = $collection[0];
    }
    public function getStatic($getComm,$price=0){
        //获取数据分析的结果
        //1 初步清洗偏离值
        $length = count ($this->arr);
        if (0 == $length) {
            return '没有原始数据，无法分析';
        }else{
            $this->arr = $this->firstClearData();
            //在清洗的基础上，得到price数组
            if(0 == count($this->arr)){
                return '第一次清洗后没有数据了，无法分析';
            }else{
                $this->price = array_column ($this->arr, 'price' );
                //第二次清洗，把超过标准差的数据再次清除
                $this->arr = $this->secondClearData();
                if(0 == count($this->arr)){
                    return '第二次清洗后没有数据了，无法分析';
                }else{
                    $this->price = array_column ($this->arr, 'price' );
                    //计算一些数学统计数据
                    $result = $this->math();
                    //再计算标准差、平均值等
                    $result = array_merge ( $result, $this->std_mean()); 
                    //计算基价的内涵数据
                    $result ['avg_area'] = $this->getAvg ( 'area' ); // 分别平均面积、平均楼层、平均总楼层、平均建成年份写入数组
                    $result ['avg_total_floor'] = $this->getAvg (  'total_floor' );
                    $result ['avg_floor_index'] = $this->getAvg ( 'floor_index' );
                    $result ['avg_builded_year'] = $this->getAvg ('builded_year' );
                    $result ['mortgageRatio'] = $this->martgageRatio($result);
                    $result ['mortgagePrice'] = $this->mortgagePrice ( $result );
                    $result ['dealPricePosition'] = $this->dealPricePosition($result['mean']);
                    $result ['dealPrice'] = $this->getValByPosition($result ['dealPricePosition']);
                    
                    //清洗后的有效数据数量
                    $result ['len'] = count($this->arr); 
                    //原始的数据数量
                    $result['ori_len'] = $length;
                    $result['from_date'] = date('Y-m-d',$this->date );
                    $result['comm'] = $getComm;
                    $result['price'] = $price;
                    $result['priceByDeal'] = 0;
                    
                    //这里是处理有输入成交价时
                    if($price > 0){
                        if($price > $result['max'] or $price < $result['min']*0.9){
                            $result['priceByDeal'] = -1;           //-1表示异常，0表示没有提供成交价
                        }else{
                            $result['priceByDeal'] = $this->dealPrice($price, $result);
                        }
                    }
                    //覆盖率
                    $result['coverage'] = round($result['len']/$result['ori_len']*100,2);
                    //标准差系数
                    $result['std_r'] = round($result['std']/$result['mean']*100,2);
                    
                    //计算盒须图
                    $result = $this->plotBox($result);
                    //计算直方图的数据
                    $result['barChart'] = $this->barChart(config('barChart_num'));
                    //============================以下是计算直方图==============================
                    $result['x_unit'] = ($result['X'] - 10)/max($result['barChart']);
                    $result['y_unit'] = (100 - $result['Y_padding'])/config('barChart_num');
                    //============================以下是散点图==============================
                    $result['area_price_scatter'] = $this->scatter('price', 'area');
                    $result['floor_price_scatter'] = $this->scatter('price', 'total_floor');
                    $result['builded_year_price_scatter'] = $this->scatter('price', 'builded_year');
                    return $result;
                }
            }
        }
    }
    
   
    private function scatter($Xitem,$Yitem){
        //===========================计算散点图==================================
//         Xitem表示X轴的参数，$Yitem表示Y轴的参数
//         $result_arr = $PL->getArr();
        $XY_scatter['Xmin'] = 10000000;
        $XY_scatter['Xmax'] = 0;
        $XY_scatter['Ymin'] = 10000000;
        $XY_scatter['Ymax'] = 0;
        
        //计算最大值最小值
        foreach ($this->arr as $item)
        {
            if ($item[$Xitem] > 0 and $item [$Yitem] > 0) {
                $area_price [] = array($Xitem=>$item[$Xitem],$Yitem=>$item [$Yitem]);
                if($item[$Xitem] > $XY_scatter['Xmax']){
                    $XY_scatter['Xmax'] = $item[$Xitem];
                }
                if($item[$Xitem] < $XY_scatter['Xmin']){
                    $XY_scatter['Xmin'] = $item[$Xitem];
                }
                if($item[$Yitem] > $XY_scatter['Ymax']){
                    $XY_scatter['Ymax'] = $item[$Yitem];
                }
                if($item[$Yitem] < $XY_scatter['Ymin']){
                    $XY_scatter['Ymin'] = $item[$Yitem];
                }
            }
        }
        //这是X轴的最大和最小值
        $scatter_extend_r = config('scatter_extend_r');
        $XY_scatter['X0'] = floor($XY_scatter['Xmin']*(1-$scatter_extend_r)/1000)*1000;
        $XY_scatter['X5'] = ceil($XY_scatter['Xmax']*(1+$scatter_extend_r)/1000)*1000;
        $scatter_X_left = config('scatter_X_left');
        $XY_scatter['Xunit'] = (100 - $scatter_X_left)/($XY_scatter['X5']-$XY_scatter['X0']);
        
        //这是Y轴的最大和最小值
        if($Yitem == 'area'){
            $XY_scatter['Y0'] = floor($XY_scatter['Ymin']*(1-$scatter_extend_r)/10)*10;
            $XY_scatter['Y5'] = ceil($XY_scatter['Ymax']*(1+$scatter_extend_r)/10)*10;
            $Yname = '(面积 平方米)';
        }elseif ($Yitem == 'total_floor' ){
            $XY_scatter['Y0'] = $XY_scatter['Ymin']-1;
            $XY_scatter['Y5'] = $XY_scatter['Ymax']+1;
            $Yname = '(总楼层)';
        }elseif ($Yitem == 'builded_year'){
            $XY_scatter['Y0'] = $XY_scatter['Ymin']-1;
            $XY_scatter['Y5'] = $XY_scatter['Ymax']+1;
            $Yname = '(建成年份)';
        }
        $scatter_Y_top = config('scatter_Y_top');
        $XY_scatter['Yunit'] = (100 - $scatter_Y_top)/($XY_scatter['Y5']-$XY_scatter['Y0']);
        
        //这是散点
        foreach ($area_price as $A_item){
            $x = ($A_item[$Xitem]-$XY_scatter['X0'] )*$XY_scatter['Xunit'] + $scatter_X_left;
            $y = ($A_item[$Yitem]-$XY_scatter['Y0'] )*$XY_scatter['Yunit'] + $scatter_Y_top;
            $A[] = array('x'=>$x,'y'=>$y);
        }
        //这是纵向的Y轴
        for ($i=0; $i<=5; $i++) {
            $x0 = $scatter_X_left + (100 - $scatter_X_left)/5*$i;
            $x1 = $x0;
            $y0 = $scatter_Y_top ;
            $y1 = 100;
            $value = $XY_scatter['X0']+($XY_scatter['X5']-$XY_scatter['X0'])/5*$i;
            $A_line[] = array('x0'=>$x0,'x1'=>$x1,'y0'=>$y0,'y1'=>$y1,'val'=>$value,'t_x'=>$x0,'t_y'=>$y0-1);
            //'t_x','t_y'是文本显示的位置
        }

        //X轴
        for ($i=0; $i<=5; $i++) {
            $x0 = $scatter_X_left;
            $x1 = 100;
            $y0 = $scatter_Y_top + (100 - $scatter_Y_top)/5*$i;
            $y1 = $y0;
            $value = $XY_scatter['Y0']+($XY_scatter['Y5']-$XY_scatter['Y0'])/5*$i;
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
            'val'=>$Yname,
            't_x'=>$scatter_X_left+1,
            't_y'=>100-1
        );
        

        $scatter['A'] = $A;
        $scatter['A_line'] = $A_line;
        $scatter['axes'] = $axes;
//         halt($scatter);
        return $scatter;
    }
    
    private function plotBox($getPrice_result){
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
        $getPrice_result['Qmax'] = 99;
        $getPrice_result['Q0'] = ($getPrice_result['Q0v']-$getPrice_result['min'])*$unit + $getPrice_result['Y_padding'];
        $getPrice_result['Q1'] = ($getPrice_result['v25']-$getPrice_result['min'])*$unit + $getPrice_result['Y_padding'];
        $getPrice_result['Q2'] = ($getPrice_result['median']-$getPrice_result['min'])*$unit + $getPrice_result['Y_padding'];
        $getPrice_result['Q3'] = ($getPrice_result['v75']-$getPrice_result['min'])*$unit + $getPrice_result['Y_padding'];
        $getPrice_result['Q4'] = ($getPrice_result['Q4v']-$getPrice_result['min'])*$unit + $getPrice_result['Y_padding'];
        if($getPrice_result['Q4'] >= 99){
            $getPrice_result['Q4'] = 99;
        }
        return $getPrice_result;
    }
    
    private function getValByPosition($position) {
        /*
         * 根据指定位置，取出$this->price的值,前提是$arr已经排过序了
         * $position = 20，表示在20%位置上的数据
         */

        // 取指定位置的数，0是第一位
        $length = count($this->price);
        $posi = round(($length -1) * $position / 100,0);
        return array_slice ($this->price, $posi, 1 )[0];
    }
    
    private function firstClearData() {
        /* 
         * 初步清洗，使用盒须图原理去除偏离过大的值 
         * 直接作用于$this->arr
         */
        $clear ['v75'] = $this->getValByPosition ( 75 );
        $clear ['v25'] = $this->getValByPosition ( 25 );
        $clear_min = $clear ['v25'] - ($clear ['v75'] - $clear ['v25']) * 1.5;
        $clear_max = $clear ['v75'] + ($clear ['v75'] - $clear ['v25']) * 1.5;
        $new_arr = array ();
        foreach ($this->arr as $v ) {
            if ($v ['price'] >= $clear_min and $v ['price'] <= $clear_max) { // 这是二维数组用的
                $new_arr [] = $v;
            }
        }
        return $new_arr;
    }

    private function std_mean(){
        /*
         * 传进一个一维数组，求其平均值、标准差
         */
        $length = count($this->price);
        $res ['mean'] = round ( array_sum ( $this->price ) / $length, 0 );
        $count = 0;
        foreach ( $this->price as $v ) {
            $count += pow ( $res ['mean'] - $v, 2 );
        }
        $res ['std'] = round ( sqrt ( $count / $length ), 0 );
        if ($length > 1) {
            $res ['std'] = round ( sqrt ( $count / ($length - 1) ), 0 ); // 2016.7.29修改一下标准差算法
        } else {
            $res ['std'] = round ( sqrt ( $count / ($length) ), 0 ); // 2016.8.12防止只有一个数据出现除0错误
        }
        return $res;
    }
    
    private function secondClearData(){
        /*
         * 做第二次清洗，把设定标准差范围内的数据都清洗掉
         * 直接作用于$this->arr
         */
        $SCOPE = config('std_times');
        $res = $this->std_mean ();
        $new_arr = array ();
        $max = $res ['mean'] + $SCOPE * $res ['std'];
        $min = $res ['mean'] - $SCOPE * $res ['std'];
        foreach ( $this->arr as $v ) {
            if ($v ['price'] >= $min and $v ['price'] <= $max) {
                $new_arr [] = $v;
            }
        }
        return $new_arr;
    }
    
    private function math(){
        /*
         * 计算一些数学统计值
         */
		$res ['median'] = $this->getValByPosition (50 );        //中位数
		$res ['min'] = $this->getValByPosition (1 );            //最小值
		$res ['max'] = $this->getValByPosition (100 );          //最大值
		$res ['v75'] = $this->getValByPosition (75 );           //3/4位数值
		$res ['v25'] = $this->getValByPosition (25 );           //1/4位数值 
		
		return $res;
    }
    
    private function getAvg($key) {
		/*
		 * 求$this->arr中某列的平均值，可以自动略过0值
		 */
		$sum1 = 0;
		$count = 0;
		foreach ( $this->arr as $v ) {
			if ($key == 'builded_year') {
				if ($v [$key] > 1900) {
					$sum1 += $v [$key];
					$count += 1;
				}
			} else {
				if ($v [$key] > 0) {
					$sum1 += $v [$key];
					$count += 1;
				}
			}
		}
		//如果没有数据，返回0
		if($count == 0){
		    return 0;
		}else{
    		return round ( $sum1 / $count, 0 );
		}
	}
	
	private function martgageRatio($data){
	    //求取动态的评估价对挂牌价的折扣率
	    $max1 = config('max_sale');
	    $max2 = config('max_evaluation');
	    $min1 = config('min_sale');
	    $min2 = config('min_evaluation');
	    $ratio = $min2 / $min1 - ($min2 / $min1 - $max2 / $max1) / ($max1 - $min1) * ($data['mean'] - $min1);
	    return round($ratio,4);
	}
	private function mortgagePrice($data){
	    /* 传入均价，根据这个值求出评估值 */
	    
	    $new_value = round ($data['mean']  * $data['mortgageRatio'], 0 );
	    	
	    // 接下来与最小挂牌价比较一下，但这个最小挂牌价要剔除异常值
	    $low = max(($data['v25']-$data['v75'])*1.5 + $data['v25'],$data['min'] );
	    $low = floor($low*0.98/100)*100;
	    $value = min($low,$new_value);
	    return $value;
	}
	
	private function dealPricePosition($price){
	    /*
	     * 传入价格，根据这个价格的高低判断风险，来决定以哪个位置的价格为二手房评估价
	     * 当8000元/平方时，取30%的位置，而63000时，取3%的位置
	     */
	    $x1 = config('max_sale');
	    $x0 = config('min_sale');
	    $y1 = config('max_position');
	    $y0 = config('min_position');
	    $y_m = config('deal_max_position');
	    $y = $y1 + ($price - $x1) / ($x1 - $x0) * ($y1 - $y0);
	    $y = $y > 0 ? $y : 0;
	    $y = $y < $y_m ? $y : $y_m;
	    return round ( $y, 0 );
	}
	
	public function dealPrice($deal,$data){
	    //当有成交价时，取min(成交价的9折，dealprice)
	    //再取max(上面的结果,mortgagePrice)
	    $priceByDeal = min($data['dealPrice'],$deal*config('deal_discount'));
	    $priceByDeal = max($priceByDeal,$data['mortgagePrice']);
	    return $priceByDeal;
	}
	
	private function barChart($num){
	    $minOfArr = min ($this->price);
	    $eachScope = (max ($this->price) - $minOfArr) / $num;
	    $barChartArr = array ();
	    for($i = 0; $i < $num; $i ++) {
	        $barChartArr [$i] = 0; // 给数组赋值0；
	    }
	    //$total = 0;
	    foreach ($this->price as $arr ) {
	        // 当前数据在哪个维度，从0开始
	        $j = floor ( ($arr - $minOfArr) / $eachScope );
	        if ($j == $num) {
	            $j = $j - 1;
	        }
	        $barChartArr [$j] += 1;
	       // $total += 1;
	    }
	    return ($barChartArr);
	}
    
    public function getArr(){
        return $this->arr;
    }
}

