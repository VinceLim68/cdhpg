<?php
namespace app\evalu\controller;

use think\Db;
use app\evalu\model\CommaddressModel;
use app\evalu\logic\MatchLogic;
use think\Exception;
use think\File;
use app\phone\model\GeneralLayoutModel;
use app\evalu\model\SalesModel;
use app\evalu\model\UserModel;
use think\Controller;
use think\Session;
use app\evalu\logic\LoginLogic;
use think\Log;

class Test extends Controller
{
    // 做一个测试模块,项目完成后删除
    public static $treeList = array();

    protected function _initialize()
    {
        parent::_initialize();
    }

    public function connectToEasyPG()
    {
        // 联系新数据库
        // $myconnect = Db::connect('EasyPG');
        // $newid = Db::connect('EasyPG')->query('select NewId() as newid');
        // dump($newid[0]['newid']);
        // $res = Db::connect('EasyPG')->table('lx_test1')->insert([
        // 'id' => $newid[0]['newid'],
        // 'lx_id' => 1,
        // 'lx1' => '测试插入lx1 ',
        // 'lx2' => '测试插入lx2 ',
        // ]);
        // dump($res);
        
        // 1 KID varchar 36 NO 系统编号 P KID
        // 2 ModuleName varchar 50 YES ModuleName
        // 12 InputName varchar 30 YES 制单人
        // 13 InputDate datetime YES 制单日期
        // 14 InputKID varchar 36 YES 制单人KID
        
        // 17 CheckName varchar 30 YES 审核人
        // 18 CheckDate datetime YES 审核日期
        
        // 24 CorpKID varchar 36 YES 公司名称 //这个必须
        // 25 Xjdate datetime YES 询价日期
        // 26 Xjdhcd varchar 36 YES 询价单号
        // 27 xjrkid varchar 36 YES 询价人ID
        // 28 Xjrname varchar 36 YES 询价人
        // 29 Xjxqname varchar 60 YES 小区名称
        // 30 Xjxqaddr varchar 200 YES 地址
        // 31 Xjkhtel varchar 60 YES 客户电话
        // 32 Xjtype varchar 20 YES 记录来源 //客户询价
        // 33 Xjwylx varchar 36 YES 物业类型
        // 34 Xjzlcs int YES 总层数
        // 35 Xjlcs varchar 20 YES 楼层
        // 36 Xjyt varchar 36 YES 用途
        // 37 Xjxqjcnf varchar 36 YES 建成年份
        // 38 Xjxqjzjg varchar 36 YES 建筑结构
        // 39 Xjynxqdt varchar 36 YES 是否带梯
        // 40 Xjremark varchar 200 YES 询价说明
        // 41 Xjgjmd varchar 36 YES 估价目的
        // 42 Xjfccjdj varchar 36 YES 成交单价
        // 43 Xjfccjzj varchar 36 YES 成交总价
        // 44 Xjfcmj numeric 16 2 YES 面积
        // 45 bankname varchar 100 YES 拟使用银行
        // 46 Xjbjdjms varchar 36 YES 报价单价
        // 47 Xjbjzjems varchar 36 YES 报价总价
        // 49 Xjyjrkid varchar 36 YES 应价人ID
        // 50 Xjyjrname varchar 36 YES 应价人
        // *51 Xjjhyjtime datetime YES 拟应价时间
        // 52 Xjyjtime datetime YES 应价时间
        // 53 Xjyjremark varchar 300 YES 应价说明
        // 54 Mark varchar 300 YES 备注
        // *55 xjfytbz varchar 1 YES 询价费已提
        // *56 xjkcxbz varchar 1 YES 询价记录可查询标志
        // 57 GJxmglkKID varchar 36 YES 项目管理KID
        // * 58 ywqxbz varchar 1 YES 业务取消标志
        // **59 ywqxDate datetime YES 业务取消日期
        // *60 xjqwdj numeric 18 2 YES 业务期望单价
        // $EasyPGXj = new EasyPGXjModel();
        // dump($EasyPGXj->getUserKID('林晓'));
        // $k = array_slice($get1,-1);
        // $str = ($k[0]['Xjdhcd']);
        // dump((int)substr($str,-4));
        dump(getUID());
    }
    
    // 匹配地址
    public function getAddress()
    {
        ignore_user_abort(true); // 后台运行
        error_reporting(0);
        set_time_limit(0);
        
        $buffer = ini_get('output_buffering');
        echo str_repeat(' ', $buffer + 1);
        ob_end_flush();
        $CA = new CommaddressModel(); // CA=CommAddress
                                      // $pattern = '/(.*市)?(.*区)?(\D*)(\d*)(-\d+)?号(之[三二一四五六七八九十]*)?(\D*)?(\d+)?(室|单元|号车位)?/';
        $CA->field('id,comm_name as community_name,address as title')->chunk(100, function ($adds) {
            foreach ($adds as $add) {
                echo $add['id'] . $add['title'] . '</br>';
                $id = MatchLogic::matchID($add);
                echo $id . '</br>';
                // $pattern = '/(.*市)?(.*区)?(\D*)(\d+号)(之[三二一四五六七八九十]*)?(\d+)(室|单元)?/';
                $pattern = config('pattern');
                // $pattern = '/(.*市)?(.*区)?(\D*)(\d*)(-\d+)?号(之[三二一四五六七八九十]*)?(\D*)?(\d+)?(室|单元|号车位)?/';
                $result = preg_match($pattern, $add['title'], $match);
                dump($match);
                if ($result == 1) {
                    try {
                        Db::table('commaddress')->where('id', $add['id'])
                            ->update([
                            'city' => $match[1],
                            'region' => $match[2],
                            'road' => $match[3],
                            'comm_id' => $id,
                            'doorplate' => $match[4] . $match[5] . '号' . $match[6]
                        ]);
                    } catch (Exception $e) {
                        echo '=======================error===================</br>';
                        echo $add . ',';
                        echo $add['id'] . '</br>';
                    }
                }
            }
        });
        ignore_user_abort(false); // 解除后台运行
    }

    public function getCommIDByAddress()
    {
        // 测试解析地址
        $address = '莲前东路894号';
        $id = MatchLogic::matchIDByAddress($address);
        dump($id);
    }

    public function copyCommAddress()
    {
        // 把commaddress复制一遍，把重复的地址去除
        ignore_user_abort(true); // 后台运行
        error_reporting(0);
        set_time_limit(0);
        $buffer = ini_get('output_buffering');
        echo str_repeat(' ', $buffer + 1);
        ob_end_flush();
        // $CA = new CommaddressModel(); //CA=CommAddress
        // $adds = Db::table('commaddress_copy')->limit(50)->select();
        // foreach ($adds as $add) {
        // try {
        // Db::table('commaddress')->insert($add);
        // }catch (Exception $e){
        // echo '=======================error===================</br>';
        // // echo $e.'</br>';
        // dump($e->getData()['PDO Error Info']['Driver Error Code']);
        // dump($add);
        // $e->getMessage;
        // $e->
        // }
        // };
        Db::table('commaddress_copy')->chunk(100, function ($adds) {
            foreach ($adds as $add) {
                try {
                    Db::table('commaddress')->insert($add);
                } catch (Exception $e) {
                    // echo '=======================error===================</br>';
                    if ($e->getData()['PDO Error Info']['Driver Error Code'] == 1062) {
                        // 如果重复，看看有没有新数据
                        $map['comm_id'] = $add['comm_id'];
                        if ($add['region'] != '') {
                            $map['region'] = $add['region'];
                        }
                        if ($add['road'] != '') {
                            $map['road'] = $add['road'];
                        }
                        if ($add['doorplate'] != '') {
                            $map['doorplate'] = $add['doorplate'];
                        }
                        $old = Db::table('commaddress')->where($map)
                            ->find();
                        $oldstr = $old['buildYear'] . $old['floors'] . $old['elevator'] . $old['structure'];
                        $nowstr = $add['buildYear'] . $add['floors'] . $add['elevator'] . $add['structure'];
                        if (strlen($nowstr) > strlen($oldstr)) {
                            Db::table('commaddress')->where('id', $old['id'])
                                ->update([
                                'buildYear' => $add['buildYear'],
                                'floors' => $add['floors'],
                                'elevator' => $add['elevator'],
                                'structure' => $add['structure']
                            ]);
                            echo '====old===' . $oldstr;
                            echo '====now===' . $oldstr;
                            echo '=======================updata===================</br>';
                        }
                        if ($old['comm_id'] == null and $add['comm_id'] != null) {
                            Db::table('commaddress')->where('id', $old['id'])
                                ->update([
                                'comm_id' => $add['comm_id']
                            ]);
                        }
                    } else {
                        dump($e->getMessage());
                    }
                    // dump($add);
                }
            }
        });
        ignore_user_abort(false); // 解除后台运行
    }
    
    // 测试正则
    public function test_reg()
    {
        // $pattern = '/^(\d+)?\.\d{4}$/';
        // $pattern = '/^(\d{4})-(\d{2})-(\d{2}) \d{2}:\d{2}:\d{2}.\d{3}$/';
        // $string = '2066-06-05 00:00:00.000';
        // $pattern = config('pattern');
        // $pattern = '/^(\d*)-?(\d+)?号(之[三二一四五六七八九十]*)?/';
        // $pattern = '/(^\D+)(\d{10})(.*)/';
        // $string = '漳2018050023';
        // if(preg_match($pattern,$string,$match)){
        // dump($match);
        // }
        // $pattern = '/(%).?/';
        $string = '%E9%BE%99%E5%B1%B1%E5%B1%B1%E5%BA%84';
        // if(preg_match($pattern,$string,$match)){
        // dump($match);
        // }
        echo substr_count($string, "%") < 3;
        // $result = preg_match($pattern,$string,$match);
        // dump($match);
    }

    public function test_strtodate()
    {
        $str = '1970';
        echo $str . '</br>';
        echo strtotime($str) . '</br>';
        echo date('Y-m-d', $str);
    }
    
    // 上传图片
    public function uploadImage()
    {
        $files = request()->file('image');
        $layout = new GeneralLayoutModel();
        $getdata['comm_id'] = 1102005;
        $imgs = $layout->where('comm_id', $getdata['comm_id'])->select();
        $this->assign('data', $imgs);
        if ($files) {
            foreach ($files as $file) {
                // 移动到框架应用根目录/public/layout/ 目录下
                $info = $file->validate([
                    'size' => 10240000,
                    'ext' => 'jpg,png,gif,jpeg'
                ])
                    ->rule('uniqid')
                    -> // 这是取消日期子目录
move(ROOT_PATH . 'public' . DS . 'layout', '');
                if ($info) {
                    // 成功上传后 获取上传信息
                    // 输出 20160820/42a79759f284b767dfcb2a0197904287.jpg
                    $savename = $info->getSaveName();
                    $savename = iconv("GB2312", "UTF-8", $savename);
                    if (! $layout->where('comm_id', $getdata['comm_id'])
                        ->where('img_url', $savename)
                        ->find()) {
                        $layout->comm_id = $getdata['comm_id'];
                        $layout->img_url = $savename;
                        $layout->save();
                    } // 如果数据库里没有记录，则追加，否则跳过
                } else {
                    // 上传失败获取错误信息
                    echo $file->getError();
                }
            }
        }
        return $this->fetch();
    }
    
    // 把2014转化为date类型
    public function mystrtodate()
    {
        $date = '67'; // 这里可以任意格式，因为strtotime函数很强大
        $is_date = strtotime($date) ? strtotime($date) : false;
        if ($is_date === false) {
            exit('日期格式非法');
        } else {
            echo date('Y-m-d', $is_date); // 只要提交的是合法的日期，这里都统一成2014-11-11格式
        }
        echo '</br>';
        // $date = "2011";
        // $year=((int)substr($date,0,4));//取得年份
        if (strlen($date) <= 4) {
            $date = $date . '-01-01';
        }
        // $month=((int)substr($date,5,2));//取得月份
        // $day=((int)substr($date,8,2));//取得几号
        // $_date = date("Y-m-d",mktime(0,0,0,$month,$day,$year));
        echo date("Y-m-d", strtotime($date));
    }
    
    // 使用echarts
    public function area_price_echarts()
    {
        $sales = new SalesModel();
        $list = $sales->field('price,area')
            ->where('community_id', '1001001')
            ->select()
            ->toArray();
        // dump($list);
        $this->assign([
            'list' => $list
        ]);
        return $this->fetch();
    }

    public function html5_localstage()
    {
        return $this->fetch();
    }
    
    // 测试自动跳转页面(进入页面去取localstorage)
    public function auto_jump()
    {
        // $str="我是密码";
        $this->assign([
            'mod' => request()->module()
        ]);
        return $this->fetch();
    }
    
    // ajax返回uid
    public function ajax_getUid()
    {
        return getUID();
    }
    
    // ajax验证登录
    public function ajax_login_verify()
    {
        $data = input();
        // halt($data);
        // 验证用户名和密码，如果成功，会自动写入session
        $res = (new UserModel())->login($data);
        return $res;
    }
    
    // 只删除session,用于测试localstorage自动登录功能
    public function del_session()
    {
        Session::delete('user.user_id');
        Session::delete('user.user_name');
        echo '删除成功';
    }
    
    // 获试获取pathinfo中的参数
    public function get_url_info()
    {
        $input = input();
        $controller = request()->controller();
        $action = request()->action();
        $module = request()->module();
        // $this->redirect('evalu/login/auto_jump',[
        // 'controller'=>$controller,
        // 'module'=>$module,
        // 'action'=>$action,
        // 'input'=>$input,
        // ]);
        $query_str = "";
        // dump($input);
        foreach ($input as $key => $value) {
            echo $key, ': ', $value, '<br />';
            $query_str .= $key . "=" . $value . "&";
        }
        echo ($query_str);
        $this->assign([
            'controller' => $controller,
            'module' => $module,
            'action' => $action,
            'input' => $query_str
        ]);
        return $this->fetch();
    }
    
    // 测试匹配小区id功能
    public function getCommID()
    {
        $commName = '保利李白的院子';
        $title = ' 	a:漳州-天柱山高速路口旁 高层,全新未入住,4500每平中式精装,看房有钥匙。单价6万8';
        $getid = MatchLogic::getId($commName, $title, 'comms');
        // echo gettype($getid);
        return $getid;
        // return $getid[0];
    }
    
    // 获得小区的最终id(唯一）
    public function matchID()
    {
        $data = [
            "title" => ' 	a:漳州-天柱山高速路口旁 高层,全新未入住,4500每平中式精装,看房有钥匙。单价6万8',
            "community_name" => '保利李白的院子'
        ];
        $data['community_id'] = MatchLogic::matchID($data);
        return $data['community_id'];
    }
    
    // 是否是微信过来的请求
    public function isWX()
    {
        if (LoginLogic::isWeixin()) {
            return '是微信';
        } else {
            return '不是微信';
        }
    }
    
    // 显示session
    public function listSesstion()
    {
        echo Session::get('user.user_id') . "\n";
        echo "\n";
        echo Session::get('user.user_name');
    }
    
    // 看看微信传递参数
    public function showinput()
    {
        $input = input();
        dump($input);
    }
    
    // url参数在javascript用encodeURI两次,在php里用urldecode解码看看
    public function showinput1()
    {
        $input = input();
        foreach ($input as $key => $value) {
            $input[$key] = urldecode($value);
            echo ($input[$key]);
        }
        return dump($input);
    }
    
    // 跳转错误信息
    public function jump_errorpage()
    {
        return $this->fetch();
    }

    public function decodeURL_round()
    {
        $string = "%25EF%25BC%25A1%25E9%2599%2588%25E5%25BF%2597%25E7%2587%2595%25EF%25BC%2588%25E6%2588%25BF%25E4%25BA%25A7%2520%25E8%25B5%2584%25E4%25BA%25A7%25E8%25AF%2584%25E4%25BC%25B0%25EF%25BC%2589";
        echo strlen($string) . '</br>';
        while (substr_count($string, "%") > 2 or strpos($string, '%2520') !== false or strpos($string, '%20') !== false) {
            $string = urldecode($string);
        }
        echo $string . '</br>';
        echo strlen($string);
    }
    
    // 时间函数测试
    public function time()
    {
        $now = time();
        echo $now;
        echo "\r\n ";
        echo $now + 7200;
        echo date('Y-m-d H:i:s', $now + 7200);
    }
    
    // 测试日志
    public function test_log()
    {
        // Log::write('测试日志信息，这是警告级别，并且实时写入','notice');
        // log::write('write now','info');
        Log::info('日志信息2');
        Log::record('错误信息2', 'error');
        trace('trace日志信息2', 'info');
    }
    
    // 循环解码直至成功,并且清除表情符号
    public function round_decode($string)
    {
        // 如果有两个以上%说明还需要解码
        while (substr_count($string, "%") > 2 or strpos($string, '%2520') !== false or strpos($string, '%20') !== false) {
            $string = urldecode($string);
        }
        return filter_Emoji($string);
    }

    public function genTree5($items)
    {
        echo "<pre>";
        // print_r($items[1]['son']);
        foreach ($items as $item) {
            print_r($item);
            print_r($item['pid']);
            $items[$item['pid']]['son'][$item['id']] = &$items[$item['id']];
        }
        return isset($items[0]['son']) ? $items[0]['son'] : array();
    }

    /**
     * 将数据格式化成树形结构
     * 
     * @author Xuefen.Tong
     * @param array $items            
     * @return array
     */
    public function genTree9($items)
    {
        $tree = array(); // 格式化好的树
        foreach ($items as $item) {
            if (isset($items[$item['pid']]))
                $items[$item['pid']]['son'][] = &$items[$item['id']];
            else
                $tree[] = &$items[$item['id']];
        }
        return $tree;
    }

    public function tree(&$data, $pid = 0, $count = 1)
    {
        // $treeList = [];
        foreach ($data as $key => $value) {
            if ($value['pid'] == $pid) {
                $value['Count'] = $count;
                self::$treeList[] = $value;
                unset($data[$key]);
                $this->tree($data, $value['id'], $count + 1);
            }
        }
        return self::$treeList;
    }

    public function print_tree()
    {
        $items = array(
            1 => array(
                'id' => 1,
                'pid' => 0,
                'name' => '江西省'
            ),
            2 => array(
                'id' => 2,
                'pid' => 0,
                'name' => '黑龙江省'
            ),
            3 => array(
                'id' => 3,
                'pid' => 1,
                'name' => '南昌市'
            ),
            4 => array(
                'id' => 4,
                'pid' => 2,
                'name' => '哈尔滨市'
            ),
            5 => array(
                'id' => 5,
                'pid' => 2,
                'name' => '鸡西市'
            ),
            6 => array(
                'id' => 6,
                'pid' => 4,
                'name' => '香坊区'
            ),
            7 => array(
                'id' => 7,
                'pid' => 4,
                'name' => '南岗区'
            ),
            8 => array(
                'id' => 8,
                'pid' => 6,
                'name' => '和兴路'
            ),
            9 => array(
                'id' => 9,
                'pid' => 7,
                'name' => '西大直街'
            ),
            10 => array(
                'id' => 10,
                'pid' => 8,
                'name' => '东北林业大学'
            ),
            11 => array(
                'id' => 11,
                'pid' => 9,
                'name' => '哈尔滨工业大学'
            ),
            12 => array(
                'id' => 12,
                'pid' => 8,
                'name' => '哈尔滨师范大学'
            ),
            13 => array(
                'id' => 13,
                'pid' => 1,
                'name' => '赣州市'
            ),
            14 => array(
                'id' => 14,
                'pid' => 13,
                'name' => '赣县'
            ),
            15 => array(
                'id' => 15,
                'pid' => 13,
                'name' => '于都县'
            ),
            16 => array(
                'id' => 16,
                'pid' => 14,
                'name' => '茅店镇'
            ),
            17 => array(
                'id' => 17,
                'pid' => 14,
                'name' => '大田乡'
            ),
            18 => array(
                'id' => 18,
                'pid' => 16,
                'name' => '义源村'
            ),
            19 => array(
                'id' => 19,
                'pid' => 16,
                'name' => '上坝村'
            ),
            20 => array(
                'id' => 20,
                'pid' => 1,
                'name' => '测试1'
            ),
            21 => array(
                'id' => 21,
                'pid' => 20,
                'name' => '测试2'
            ),
            22 => array(
                'id' => 22,
                'pid' => 8,
                'name' => '东北林业大学2'
            )
        );
        // $this->genTree5($items);
        array_multisort(array_column($items, 'pid'), SORT_DESC, $items);
        $items = $this->tree($items);
        $this->assign('items', $items);
        // echo "<pre>";
        // print_r($items);
        return $this->fetch();
        // print_r($this->tree($items));
        // print_r($this->genTree5($items));
        // print_r($this->genTree9($items));
    }
    
    // 测试一个可折叠菜单的界面
    public function menu()
    {
        $this->fetch();
    }

    //测试跳转
    public function redirct(){
        $this->error('程序出错了！！！','evalu/login/login');
        
    }

    //测试子查询
    public function childsql(){
        $road = '日圆三里';
        $res = Db::table('comm')
            ->where('comm_id','IN',function($query) use($road){
                $query
                ->table('commaddress')
                ->where('road',$road)
                ->where('comm_id','<>','')
                ->group('comm_id')
                ->field('comm_id');
        })
        ->select();
        dump($res);
    }

}