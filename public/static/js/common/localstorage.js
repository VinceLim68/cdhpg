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
			   alert("系统错误");
	       }
	   })
	}else{
	 	//alert(getMachineID);
	};
}

//ajax验证用户名和密码
function login_handle(user_name,pass,machineID,type){
	$.ajax({
        url: ajax_login_verify_url,
        data: {
        	user_name: user_name,
            pass: pass,
            matchineID:machineID,
            type:type,
        },
        success: function(data) {
        	//console.log(data);
            if(data.valid == 1){
            	//alert('login success!')
            	if((undefined == getUser) || ("" == getUser) || (null == getUser)){
            		//如果localstorage中未设置，则把用户名和密码存入localstorage,
            		//机器码用MachineID_handle（）已经建立了，不必存。
            		storage["user"] = user_name;
            		storage["password"] = pass;
            	}
            	origin_url = '/'+module+'/'+controller+'/'+action;
            	//alert(origin_url);
            	if(('/evalu/login/login' == origin_url) || ('///' == origin_url)){
            		//如果起始就是登录模块，则跳到询价链接
            		window.location.href = phone_url;
            	}else{
            		//否则跳转原链接
            		window.location.href = origin_url;			//	跳回原链接
            	}
//                if('phone' == mod){
//                	window.location.href = phone_url;
//                }else if ('evalu' == mod){
//                	window.location.href = evalu_url;
//                }else{
//                	window.location.href = phone_url;
//                }
            }else{
            	//如果验证不通过，应该返回登录页面
            	alert(data.msg);
            	window.location.href = Jumpurl;
            };
        },
        error: function() {
            alert("系统错误");
        }
    });
}


