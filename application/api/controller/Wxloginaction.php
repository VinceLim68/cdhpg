<?php
namespace app\api\controller;

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
        $url = 'https://api.weixin.qq.com/cgi-bin/token?grant_type=client_credential&appid={$this->AppID}&secret={$this->AppSecret}';
        $ass_key = $this->httpGet($url);
        $a1 = $ass_key->access_token;
        return $a1;
    }
    
    public function getToken()
    {
        
        //获得token控制器，
        //先从数据库里读，如果没有或过期，就从微信处获得，并存储在数据库中
        return '123';
        
        
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
            echo 'Error+'.curl_error($curl);
        }
        curl_close($curl);
        return $res;
    }
    
    public function sendTemplateMessage($wxinfo){
//             传进来是一个json对象
//             public 'nickname' => string '大叔' (length=6)
//             public 'machine' => string 'iphone' (length=6)
//             public 'comm' => string '瑞景' (length=6)
//             public 'lx' => string '12345' (length=5)
//             public 'lx2' => string 'abcde' (length=5)
//             public 'price' => int 0
//             dump($wxinfo->nickname);
//         dump($wxinfo);
        $url = "https://api.weixin.qq.com/cgi-bin/message/wxopen/template/send?access_token=ACCESS_TOKEN";
        return ;
    }
    
    
    
    
}