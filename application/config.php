<?php
// +----------------------------------------------------------------------
// | ThinkPHP [ WE CAN DO IT JUST THINK ]
// +----------------------------------------------------------------------
// | Copyright (c) 2006~2016 http://thinkphp.cn All rights reserved.
// +----------------------------------------------------------------------
// | Licensed ( http://www.apache.org/licenses/LICENSE-2.0 )
// +----------------------------------------------------------------------
// | Author: liu21st <liu21st@gmail.com>
// +----------------------------------------------------------------------
return [
		// +----------------------------------------------------------------------
		// | 应用设置
		// +----------------------------------------------------------------------
		
		// 应用命名空间
		'app_namespace' => 'app',
		// 应用调试模式
		'app_debug' => true,
		// 应用Trace
		'app_trace' => TRUE,
		// 应用模式状态
		'app_status' => '',
		// 是否支持多模块
		'app_multi_module' => true,
		// 入口自动绑定模块
		'auto_bind_module' => false,
		// 注册的根命名空间
		'root_namespace' => [ ],
		// 扩展函数文件
		'extra_file_list' => [ 
				THINK_PATH . 'helper' . EXT 
		],
		// 默认输出类型
		'default_return_type' => 'html',
		// 默认AJAX 数据返回格式,可选json xml ...
		'default_ajax_return' => 'json',
		// 默认JSONP格式返回的处理方法
		'default_jsonp_handler' => 'jsonpReturn',
		// 默认JSONP处理方法
		'var_jsonp_handler' => 'callback',
		// 默认时区
		'default_timezone' => 'PRC',
		// 是否开启多语言
		'lang_switch_on' => false,
		// 默认全局过滤方法 用逗号分隔多个
		'default_filter' => '',
		// 默认语言
		'default_lang' => 'zh-cn',
		// 应用类库后缀
		'class_suffix' => false,
		// 控制器类后缀
		'controller_suffix' => false,
		// 是否开启模板编译缓存,设为false则每次都会重新编译
		'tpl_cache' => false,
		
		// +----------------------------------------------------------------------
		// | 模块设置
		// +----------------------------------------------------------------------
		
		// 默认模块名
		'default_module' => 'index',
		// 禁止访问模块
		'deny_module_list' => [ 
				'common' 
		],
		// 默认控制器名
		'default_controller' => 'Index',
		// 默认操作名
		'default_action' => 'index',
		// 默认验证器
		'default_validate' => '',
		// 默认的空控制器名
		'empty_controller' => 'Error',
		// 操作方法后缀
		'action_suffix' => '',
		// 自动搜索控制器
		'controller_auto_search' => false,
		
		// +----------------------------------------------------------------------
		// | URL设置
		// +----------------------------------------------------------------------
		
		// PATHINFO变量名 用于兼容模式
		'var_pathinfo' => 's',
		// 兼容PATH_INFO获取
		'pathinfo_fetch' => [ 
				'ORIG_PATH_INFO',
				'REDIRECT_PATH_INFO',
				'REDIRECT_URL' 
		],
		// pathinfo分隔符
		'pathinfo_depr' => '/',
		// URL伪静态后缀
		'url_html_suffix' => 'html',
		// URL普通方式参数 用于自动生成
		'url_common_param' => false,
		// URL参数方式 0 按名称成对解析 1 按顺序解析
		'url_param_type' => 0,
		// 是否开启路由
		'url_route_on' => true,
		// 路由使用完整匹配
		'route_complete_match' => false,
		// 路由配置文件（支持配置多个）
		'route_config_file' => [ 
				'route' 
		],
		// 是否强制使用路由
		'url_route_must' => false,
		// 域名部署
		'url_domain_deploy' => false,
		// 域名根，如thinkphp.cn
		'url_domain_root' => '',
		// 是否自动转换URL中的控制器和操作名
		'url_convert' => true,
		// 默认的访问控制器层
		'url_controller_layer' => 'controller',
		// 表单请求类型伪装变量
		'var_method' => '_method',
		// 表单ajax伪装变量
		'var_ajax' => '_ajax',
		// 表单pjax伪装变量
		'var_pjax' => '_pjax',
		// 是否开启请求缓存 true自动缓存 支持设置请求缓存规则
		'request_cache' => false,
		// 请求缓存有效期
		'request_cache_expire' => null,
		// 全局请求缓存排除规则
		'request_cache_except' => [ ],
		
		// +----------------------------------------------------------------------
		// | 模板设置
		// +----------------------------------------------------------------------
		
		'template' => [
				// 模板引擎类型 支持 php think 支持扩展
				'type' => 'Think',
				// 模板路径
				'view_path' => '',
				// 模板后缀
				'view_suffix' => 'html',
				// 模板文件名分隔符
				'view_depr' => DS,
				// 模板引擎普通标签开始标记
				'tpl_begin' => '{',
				// 模板引擎普通标签结束标记
				'tpl_end' => '}',
				// 标签库标签开始标记
				'taglib_begin' => '{',
				// 标签库标签结束标记
				'taglib_end' => '}' 
		],
		
		// 视图输出字符串内容替换
		'view_replace_str' => [ ],
		// 默认跳转页面对应的模板文件
		'dispatch_success_tmpl' => THINK_PATH . 'tpl' . DS . 'dispatch_jump.tpl',
		'dispatch_error_tmpl' => THINK_PATH . 'tpl' . DS . 'dispatch_jump.tpl',
		
		// +----------------------------------------------------------------------
		// | 异常及错误设置
		// +----------------------------------------------------------------------
		
		// 异常页面的模板文件
		'exception_tmpl' => THINK_PATH . 'tpl' . DS . 'think_exception.tpl',
		
		// 错误显示信息,非调试模式有效
		'error_message' => '页面错误！请稍后再试～',
		// 显示错误信息
		'show_error_msg' => false,
		// 异常处理handle类 留空使用 \think\exception\Handle
		'exception_handle' => '',
		
		// +----------------------------------------------------------------------
		// | 日志设置
		// +----------------------------------------------------------------------
		
		'log' => [
				// 日志记录方式，内置 file socket 支持扩展
				'type' => 'File',
				// 日志保存目录
				'path' => LOG_PATH,
				// 日志记录级别
				'level' => [ ] 
		],
		
		// +----------------------------------------------------------------------
		// | Trace设置 开启 app_trace 后 有效
		// +----------------------------------------------------------------------
		'trace' => [
				// 内置Html Console 支持扩展
				'type' => 'Html' 
		],
		
		// +----------------------------------------------------------------------
		// | 缓存设置
		// +----------------------------------------------------------------------
		
		'cache' => [
				// 驱动方式
				'type' => 'File',
				// 缓存保存目录
				'path' => CACHE_PATH,
				// 缓存前缀
				'prefix' => '',
				// 缓存有效期 0表示永久缓存
				'expire' => 0 
		],
		
		// +----------------------------------------------------------------------
		// | 会话设置
		// +----------------------------------------------------------------------
		
		'session' => [ 
				'id' => '',
				// SESSION_ID的提交变量,解决flash上传跨域
				'var_session_id' => '',
				'expire' => 86400,
				
				// SESSION 前缀
				'prefix' => 'think',
				// 驱动方式 支持redis memcache memcached
				'type' => '',
				// 是否自动开启 SESSION
				'auto_start' => true 
		],
		
		// +----------------------------------------------------------------------
		// | Cookie设置
		// +----------------------------------------------------------------------
		'cookie' => [
				// cookie 名称前缀
				'prefix' => '',
				// cookie 保存时间
				'expire' => 0,
				// cookie 保存路径
				'path' => '/',
				// cookie 有效域名
				'domain' => '',
				// cookie 启用安全传输
				'secure' => false,
				// httponly设置
				'httponly' => '',
				// 是否使用 setcookie
				'setcookie' => true 
		],
		
		// 分页配置
		'paginate' => [ 
				'type' => 'bootstrap',
				'var_page' => 'page',
				'list_rows' => 15 
		],
		
		// 验证码
		'captcha' => [ 
				'fontSize' => 20,
				'codeSet' => '1234567890',
				'imageH' => 45,
				'imageW' => 150,
				'length' => 2 
		]
		,
		
		// 服务器上的数据库配置
		'remote_dbconfig' => [
				// 数据库类型
				'type' => 'mysql',
				// 服务器地址
				'hostname' => 'office.xmcdhpg.cn',
				// 数据库名
				'database' => 'property_info',
				// 数据库用户名
				'username' => 'root',
				// 数据库密码
				'password' => 'root',
				// 数据库编码默认采用utf8
				'charset' => 'utf8' 
		],
    
    //以下是自己配置的信息

    'min_base_records'              =>  100,            //单次查询最少的挂牌记录,才能做询价分析
    'how_long_before_to_start_query'=>  3,              //从几个月前开始查询
    'select_records_per_time'       =>  300000,         //每次查询，以30万为单位递增
    'select_more_months_per_time'   =>  3,               //每次查询增加的月数
    'std_times'                     =>  1.5,            //设定清洗数据时标准差的倍数
    'max_sale'                      =>  63000,          //最大的挂牌价
//     'max_evaluation'                =>  50000,          //最大挂牌价对应的评估价   79%
    'max_evaluation'                =>  48000,          //最大挂牌价对应的评估价   2018/4/13调整
    'max_position'                  =>  3,              //最大挂牌价对应的二手房评估价所在的位置，3表示从最小值往上3%
    'min_sale'                      =>  15000,           //最小的挂牌价
    'min_evaluation'                =>  12750,           //最小挂牌价对应的评估价 这个是90%
    'min_position'                  =>  18,             //最小挂牌价对应的二手房评估价所在位置，25表示
    'deal_max_position'             =>  25,              //二手房最大价值不得超过25%的挂牌价,最小是0，不用设置
    'deal_discount'                 =>  0.9,            //当有成交价时，先给成交价打个折
    'X'                             =>  75,              //竖向的盒须图，在左侧留出%给直方图用
    'box_width'                     =>  5,                //盒须图本身的宽度
    'Y_padding'                     =>  5,                //相当于top_padding
    'barChart_num'                  =>  15,               //直方图的数量
    'std_r_limit'                   =>  10,               //判断标准差系数是否异常的标准
    'scatter_extend_r'              =>  0.00,                //散点图扩展百分比。5表示在X、Y轴都扩展5%
    'scatter_X_left'                =>  5,              //  散点图在X轴左侧留的百分比，用于显示Y轴的标数
    'scatter_Y_top'                 =>  5,              //  散点图在Y轴上方留的百分比，用于显示X轴的标数
    'whisker_times'                 =>  5,              //清理散点图时使用的比例，超过盒长倍数视为异常值予以清理，一般是1.5，但有时会误清，所以改用3
    
    'emplorers'         =>    ['林晓','王亿彬','贾琴',"廖亚香","黄燕翔","金忠","泉州银行","招商银行","云估价",
                                "陈锦钦","陈丽华","吴木兰","项争","陈志艳","陈玉炜","黄清江","王梓瀛","陈军勇",
                                "朱黎英","邱宏达","公司外部" ],
    'use'               =>   ['住宅','办公','店面','车位','商场','独栋别墅','联排别墅','叠加别墅',"双拼别墅","工业",
                                "土地","其他" ],
    'elevator'          =>  ['带电梯','无电梯'],
    'structuer'         =>  ['钢混结构','砖混结构','钢结构'],
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
    
    'EasyPG'    =>  [
            // 数据库类型mssql,sqlserver
            'type'        => 'Sqlsrv',
            // 服务器地址
            'hostname'    => '192.168.1.250',
            // 数据库名
            'database'    => 'EasyPG',
            // 数据库用户名
            'username'    => 'sa',
            // 数据库密码
            'password'    => 'siwing',
            // 数据库编码默认采用utf8
            'charset'     => 'utf8',
            // 数据库调试模式
            'debug'       => true,
        ],
        'historyDays'     =>  180,                          //查询历史报价和案例的天数
        'no_enquery_again'  =>  30,                         //30天内不允许重复报价
        //这是解析地址的正则
        "pattern" => '/(.*市)?(.*区)?(\D*)(\d*)(-\d+)?号(之[三二一四五六七八九十]*)?(\D*)?(\d+)?(室|单元|号车位)?/',
    
    
		 
];
