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

//生成唯一码，36位
function getUID(){
    $charid = strtoupper(md5(uniqid(mt_rand(), true)));
    $uuid = substr($charid, 0, 8).'-'.substr($charid, 8, 4).'-'
        .substr($charid,12, 4).'-'.substr($charid,16, 4).'-'
            .substr($charid,20,12);
    return $uuid;
    
}