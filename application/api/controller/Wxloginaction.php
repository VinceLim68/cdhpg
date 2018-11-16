<?php
namespace app\api\controller;

use app\evalu\model\UserModel;
use think\Log;

class Wxloginaction{
    
    protected $AppID = 'wx1a480f3d8834457c';
    protected $AppSecret = '2b74d798fa59c148d1c3335f45953159';
    
    public function getOpenid(){
        //取openid
        $JSCODE = input('code');
        $url = "https://api.weixin.qq.com/sns/jscode2session?appid={$this->AppID}&secret={$this->AppSecret}&js_code={$JSCODE}&grant_type=authorization_code";
        $result = $this->httpGet($url);
        return $result;
    }
    
    private function httpGet($url) {
        $curl = curl_init();
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($curl, CURLOPT_TIMEOUT, 500);
        curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, false);
        curl_setopt($curl, CURLOPT_URL, $url);
    
        $res = curl_exec($curl);
        curl_close($curl);
    
        return $res;
    }
    
    public function returnAsskey()
    {
        //从微信处获得token
        $url = "https://api.weixin.qq.com/cgi-bin/token?grant_type=client_credential&appid={$this->AppID}&secret={$this->AppSecret}";
        $ass_key = json_decode($this->httpGet($url));
        //         返回样式：
        // object(stdClass)[5]
        //         public 'access_token' => string '13_VnRFb6W3iwwlosTPMU4eMlyC12LBVdvL_GbwBW1dedS8LvP4iXCabqeFhk8BzNKxNvd2tL7_0jIpNhBczf1oz6itDf8Hpjrk7U2CFf3uIhBoPaN0iZMY66-vCUdlXL0IaxrM6XxrftKGYd-iJJUhABAWWH' (length=157)
        //         public 'expires_in' => int 7200

        return $ass_key;
    }
    
    public function getToken($nickname)
    {
        //获得token控制器，
        //先从数据库里读，如果没有或过期，就从微信处获得，并存储在数据库中
        $wxUser = new UserModel();
        $userInfo = $wxUser->where('user_name',$nickname)->find();
//         dump(strtotime($userInfo->time_out));
        if(!$userInfo){
            return '没有此用户';
        }else{
            $now = time();
            if(is_null($userInfo->token) 
                or $userInfo->time_out == '未设置' 
                or (strtotime($userInfo->time_out) - 900) <= $now )     //模型层会自动把$userInfo->time_out转成date类型，这里需要把它再转回时间戳
            {
                //null判断,字符串用is_null,数字用'未设置'
                //如果没有token,或者没有有效时间，或者过期，则重新取token
//                 dump(is_null($userInfo->token)) ;
//                 dump($userInfo->time_out == '未设置' );
//                 dump((strtotime($userInfo->time_out) - 900) <= $now);
//                 echo '重新取';
                $asskey = $this->returnAsskey();
                //存入表中
                $wxUser->save([
                    'token'  => $asskey->access_token,
                    'time_out' => $asskey->expires_in + $now,
                ],['user_id' => $userInfo['user_id']]);
                //赋值token
                $token[] = $asskey->access_token;
            }else{
                //表中有有效的token,直接取
//                 echo '直接取';
                $token[] = $userInfo->token;
            }
            $token[] = $userInfo->openid;
        }
//         返回值：$token[0]=access_token;$token[1]=openid;
        return $token;
    }
    
    
    private function postCurl($url,$data,$type)
    {
        if($type == 'json'){
            $data = json_encode($data);//对数组进行json编码
            $header= array("Content-type: application/json;charset=UTF-8","Accept: application/json","Cache-Control: no-cache", "Pragma: no-cache");
        }
        $curl = curl_init();
        curl_setopt($curl,CURLOPT_URL,$url);
        curl_setopt($curl,CURLOPT_POST,1);
        curl_setopt($curl,CURLOPT_SSL_VERIFYPEER,false);
        curl_setopt($curl,CURLOPT_SSL_VERIFYHOST,false);
        if(!empty($data)){
            curl_setopt($curl,CURLOPT_POSTFIELDS,$data);
        }
        curl_setopt($curl,CURLOPT_RETURNTRANSFER,1);
        curl_setopt($curl,CURLOPT_HTTPHEADER,$header);
        $res = curl_exec($curl);
        if(curl_errno($curl)){
            Log::ERROR('Error+'.curl_error($curl));
            echo 'Error+'.curl_error($curl);
        }
        curl_close($curl);
        return $res;
    }
    
    //发送模板消息
    public function sendTemplateMessage($wxinfo,$B){
//         发送微信模板消息
//             $wxinfo传进来是一个json对象
//             public 'nickname' => string '大叔' (length=6)
//             public 'machine' => string 'iphone' (length=6)
//             public 'comm' => string '瑞景' (length=6)
//             public 'lx' => string '12345' (length=5)
//             public 'lx2' => string 'abcde' (length=5)
//             public 'price' => int 0
//             dump($wxinfo->nickname);
        if(isset($wxinfo->lx2) and isset($wxinfo->lx)){
            
            $token = $this->getToken($wxinfo->nickname);
            //使用统一的服务消息
            $url = "https://api.weixin.qq.com/cgi-bin/message/wxopen/template/uniform_send?access_token={$token[0]}";
//             //使用模板消息
//             $url = "https://api.weixin.qq.com/cgi-bin/message/wxopen/template/send?access_token={$token[0]}";
            $TEMPLATE_ID = "VAIP7p4SYk1qvzPcvYEblfMIi7gu02cUrtUtGWGSLKM";
            
            $message = array(
              "touser"=>$wxinfo->lx,        //这是参数传递过来的openid
              "weapp_template_msg"=>array(
                  "template_id"=>$TEMPLATE_ID,
                  "page"=>"pages/search/search",
                  "form_id"=>$wxinfo->lx2,
                  "data"=>array(
                      "keyword1"=>array(
                          "value"=>$B['mortgagePrice'].'元/㎡',
                          "color"=>"#ff6666"
                      ),
                      "keyword2"=>array(
                          "value"=>$B['comm']['comm_name'],
                          "color"=>"#173177"
                      ),
                      "keyword3"=>array(
                          "value"=>"免费",
                          "color"=>"#888888"
                      ) ,
                      "keyword4"=>array(
                          "value"=>"此价格对应：面积".$B['avg_area'].'㎡,'
                          .$B['avg_floor_index'].'层/共'.$B['avg_total_floor'].'层,建成于'.$B['avg_builded_year'].'年。本估价三日内有效。',
                          "color"=>"#888888"
                      ),
                      "keyword5"=>array(
                          "value"=>"欢迎点击试用‘大叔询价’小程序"
                      )
                  ),
                  "emphasis_keyword"=>"keyword1.DATA"
                ), 
                "mp_template_msg"=>array(),
            );
            
//             $message = array(
//               "touser"=>$wxinfo->lx,        //这是参数传递过来的openid
// //               "touser"=>'oBO2o5eRfB6nVaFcJB1JVbP9fS_0',
//               "template_id"=>$TEMPLATE_ID,
//               "page"=>"pages/search/search",
//               "form_id"=>$wxinfo->lx2,
//               "data"=>array(
//                   "keyword1"=>array(
//                       "value"=>$B['mortgagePrice'].'元/㎡',
//                       "color"=>"#ff6666"
//                   ),
//                   "keyword2"=>array(
//                       "value"=>$B['comm']['comm_name'],
//                       "color"=>"#173177"
//                   ),
//                   "keyword3"=>array(
// //                       "value"=>date("Y-m-d H:i:s",time()),
// //                       "color"=>"#888888"
//                       "value"=>"免费",
//                       "color"=>"#888888"
//                   ) ,
//                   "keyword4"=>array(
//                       "value"=>"此价格对应：面积".$B['avg_area'].'㎡,'
//                       .$B['avg_floor_index'].'层/共'.$B['avg_total_floor'].'层,建成于'.$B['avg_builded_year'].'年。本估价三日内有效。',
//                       "color"=>"#888888"
//                   ),
//                   "keyword5"=>array(
//                       "value"=>"欢迎点击试用‘大叔询价’小程序"
//                   )
//               ),
//               "emphasis_keyword"=>"keyword1.DATA"
//             );
            $res = $this->postCurl($url,$message,'json');//将data数组转换为json数据
            if($res){
                Log::record(json_encode($res),'error');
                return json_encode(array('state'=>4,'msg'=>$res));
            }else{
                return json_encode(array('state'=>5,'msg'=>$res));
            }
        }else{
            Log::record(json_encode($wxinfo).'没有formid','error');
            return json_encode(array('state'=>3,'msg'=>'没有formid'));
        }
    }
    
    
    
    
}