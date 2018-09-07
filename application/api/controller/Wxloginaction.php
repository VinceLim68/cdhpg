<?php
namespace app\api\controller;

class Wxloginaction{
    
    public function wxLoginAction(){
        $JSCODE = input('code');
        $AppID = 'wx1a480f3d8834457c';
        $AppSecret = '2b74d798fa59c148d1c3335f45953159';
        $url = "https://api.weixin.qq.com/sns/jscode2session?appid={$AppID}&secret={$AppSecret}&js_code={$JSCODE}&grant_type=authorization_code";
        $result = $this->httpGet($url);
        //返回｛openid,session_key
        dump($result);
        $openid = $result.openid;
        return $openid;
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
    
    
}