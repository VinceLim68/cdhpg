//需要在引用的页面中先写入以下语句
//<script type="text/javascript">
//	var Jumpurl = "{:url('login/login')}";
//	var getUIDurl = "{:url('test/ajax_getUid')}";
//	var ajax_login_verify_url = "{:url('test/ajax_login_verify')}";
//	var phone_url = "{:url('phone/index/index')}";
//	var evalu_url = "{:url('evalu/sales/salesList')}";
//</script>

var storage = window.localStorage;
var getUser = storage["user"];
var getPwd = storage["password"];
var getMachineID = storage["UID"];


//处理机器码的函数
function MachineID_handle(){
	 if ((undefined == getMachineID) || ("" == getMachineID) || (null == getMachineID)){
		 //如果没有机器码，设置一个
		$.ajax({
		   url:getUIDurl,
		   success: function(data) {
			  	 //得到一个机器码，保存起来
			   storage["UID"] = data;
			   getMachineID = storage["UID"];
		   },
		   error: function() {
			   alert("系统错误，未能完成设置");
	       }
	   })
	}else{
	 	//alert(getMachineID);
	};
}

//ajax验证用户名和密码
function login_handle(user_name,pass,machineID,type){
	//alert('跳回原页，原页是：' + origin_url + ',real_url是：' + real_url);
	//跳回原页，原页是：///,real_url是：/evalu/login/login.html
	//跳回原页，原页是：/phone/Index/index,real_url是：/evalu/login/login.html
	$.ajax({
        url: ajax_login_verify_url,
        data: {
        	user_name: user_name,
            pass: pass,
            matchineID:machineID,
            type:type,
        },
        async:false,
        success: function(data) {
        	//console.log(data);
            if(data.valid == 1){
            	if((undefined == getUser) || ("" == getUser) || (null == getUser)){
            		//如果localstorage中未设置，则把用户名和密码存入localstorage,
            		//机器码用MachineID_handle（）已经建立了，不必存。
            		storage["user"] = user_name;
            		storage["password"] = pass;
            	}
            	
//            	alert(origin_url);
//            	alert(real_url);
            	if(('/evalu/login/login' == origin_url) || ('///' == origin_url) ||(real_url.indexOf("/evalu/login/login") != -1)){
            		//如果起始就是登录模块，则跳到询价链接
            		//alert(origin_url);
            		//alert('当前页面是登录页面：'+origin_url);
            		window.location.href = phone_url;
            	}else{
            		//否则跳转原链接
            		//alert('跳转')
            		//alert('跳回原页，原页是：'+origin_url+',real_url是：'+real_url);
            		window.location.href = real_url;			//	跳回原链接
            	}
            }else{
            	//如果验证不通过，应该返回登录页面
            	alert(data.msg);
            	window.location.href = Jumpurl;
            };
        },
        error: function() {
            alert("系统错误，未能完成登录验证");
        }
    });
}


