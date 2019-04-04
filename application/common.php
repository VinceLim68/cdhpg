<?php
// +----------------------------------------------------------------------
// | ThinkPHP [ WE CAN DO IT JUST THINK ]
// +----------------------------------------------------------------------
// | Copyright (c) 2006-2016 http://thinkphp.cn All rights reserved.
// +----------------------------------------------------------------------
// | Licensed ( http://www.apache.org/licenses/LICENSE-2.0 )
// +----------------------------------------------------------------------
// | Author: 流年 <liu21st@gmail.com>
// +----------------------------------------------------------------------

// 应用公共文件

//利用url传参，特殊字符如‘ '，'>'等需要处理一下，引自http://www.jb51.net/article/17436.htm
function base_encode($str) {
    $src  = array("/","+","=");
    $dist = array("_a","_b","_c");
    $old  = base64_encode($str);
    $new  = str_replace($src,$dist,$old);
    return $new;
}

function base_decode($str) {
    $src = array("_a","_b","_c");
    $dist  = array("/","+","=");
    $old  = str_replace($src,$dist,$str);
    $new = base64_decode($old);
    return $new;
}

//循环解码直至成功,并且清除表情符号
function round_decode($string){
    //如果有两个以上%说明还需要解码
    while(substr_count($string,"%") > 2  
        or strpos($string,'%2520') !== false 
        or strpos($string,'%20') !== false){
        $string = urldecode($string);
    }
    return filter_Emoji($string);
}

//生成唯一码，36位
function getUID(){
    $charid = strtoupper(md5(uniqid(mt_rand(), true)));
    $uuid = substr($charid, 0, 8).'-'.substr($charid, 8, 4).'-'
        .substr($charid,12, 4).'-'.substr($charid,16, 4).'-'
            .substr($charid,20,12);
    return $uuid;
    
}

//过滤表情符号
function filter_Emoji($str)
{
//     $str = preg_replace_callback(    //执行一个正则表达式搜索并且使用一个回调进行替换
//         '/./u',
//         function (array $match) {
//             return strlen($match[0]) >= 4 ? '' : $match[0];
//         },
//         $str);
//     return $str;
    
    $clean_text = "";
    // Match Emoticons
    $regexEmoticons = '/[\x{1F600}-\x{1F64F}]/u';
    $clean_text = preg_replace($regexEmoticons, '', $str);
    // Match Miscellaneous Symbols and Pictographs
    $regexSymbols = '/[\x{1F300}-\x{1F5FF}]/u';
    $clean_text = preg_replace($regexSymbols, '', $clean_text);
    // Match Transport And Map Symbols
    $regexTransport = '/[\x{1F680}-\x{1F6FF}]/u';
    $clean_text = preg_replace($regexTransport, '', $clean_text);
    // Match Miscellaneous Symbols
    $regexMisc = '/[\x{2600}-\x{26FF}]/u';
    $clean_text = preg_replace($regexMisc, '', $clean_text);
    // Match Dingbats
    $regexDingbats = '/[\x{2700}-\x{27BF}]/u';
    $clean_text = preg_replace($regexDingbats, '', $clean_text);
    
    return $clean_text;

}

//无限分级
