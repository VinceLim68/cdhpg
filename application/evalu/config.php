<?php
//这是给手机端的询价配置参数，计算询价，放在这里不合适，试验一下模块配置
return [
    
    'min_base_records'              =>  100,            //单次查询最少的挂牌记录,才能做询价分析
    'how_long_before_to_start_query'=>  6,              //从几个月前开始查询
    'select_records_per_time'       =>  300000,         //每次查询，以30万为单位递增
    'select_more_months_per_time'   =>  3,               //每次查询增加的月数
    'std_times'                     =>  1.5,            //设定清洗数据时标准差的倍数
    'max_sale'                      =>  63000,          //最大的挂牌价
    'max_evaluation'                =>  50000,          //最大挂牌价对应的评估价   79%
    'max_position'                  =>  3,              //最大挂牌价对应的二手房评估价所在的位置，3表示从最小值往上3%
    'min_sale'                      =>  15000,           //最小的挂牌价
    'min_evaluation'                =>  12750,           //最小挂牌价对应的评估价 这个是90%
    'min_position'                  =>  18,             //最小挂牌价对应的二手房评估价所在位置，25表示
    'deal_max_position'             =>  25,              //二手房最大价值不得超过25%的挂牌价,最小是0，不用设置
    'deal_discount'                 =>  0.9,            //当有成交价时，先给成交价打个折
    'X'                             => 75,              //竖向的盒须图，在左侧留出%给直方图用
    'box_width'                     =>5,                //盒须图本身的宽度        
    'Y_padding'                     =>5,                //相当于top_padding
    'barChart_num'                  =>15,               //直方图的数量
    'std_r_limit'                   =>10,               //判断标准差系数是否异常的标准
    'scatter_extend_r'              =>0.00,                //散点图扩展百分比。5表示在X、Y轴都扩展5%
    'scatter_X_left'                =>  5,              //  散点图在X轴左侧留的百分比，用于显示Y轴的标数
    'scatter_Y_top'                 =>  5,              //  散点图在Y轴上方留的百分比，用于显示X轴的标数
    
    //apprsal_cdh数据库配置
    'db_apprsal_cdh' => [
        // 数据库类型
	    'type'        => 'mysql',
	    // 服务器地址
	    'hostname'    => '192.168.1.207',
	    // 数据库名
	    'database'    => 'apprsal_cdh',
	    // 数据库用户名
	    'username'    => 'root',
	    // 数据库密码
	    'password'    => 'root',
	    // 数据库编码默认采用utf8
	    'charset'     => 'utf8',
	    // 数据库调试模式
	    'debug'       => true,
    ],
    'historyDays'     =>  180,                          //查询历史报价和案例的天数
    'no_enquery_again'  =>  30,                         //30天内不允许重复报价
    
];