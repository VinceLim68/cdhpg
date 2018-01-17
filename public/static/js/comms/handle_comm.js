function getUrlParam(url){
	//这个是从url中把参数拆分开来
	console.log(url);
	var querys = url
	    .substring(url.indexOf('?') + 1)
	    .split('&');
	var result={};
	for(var i=0;i<querys.length;i++){
		var num = querys[i].indexOf('=');//只拆分第一个”=“号
	    var val = (querys[i].slice(num+1));//”=“后面的全作为参数值
		var temp=querys[i].split('=');
	    if(temp.length<2){
	        result[temp[0]]='';
	    }else{
	        result[temp[0]]=val;
	    }  
	}
	return(result); 
};

function formatUrl(obj){
	//把一个对象合成url参数
	var param = ''
	Object.keys(obj).forEach(function(key){

	     param += '&' + key + '=' + obj[key];

	});
	return param.substring(1);
};

function getPath(url){
	//从url中得到path
	return(url.substring(0,url.indexOf('?')));
};

jQuery(function($) {
	$('.modal').draggable();
	$(".modal").css("overflow", "hidden");//禁止模态对话框的半透明背景滚动
	$("#rela_table td").on('click',function(t){
		var html =  $(this).parent().html();
		alert(html);
	});
	$(".pagination").on("click", "li > a", function(t){
		t.preventDefault();
		//var totalPage = {$saleslist->lastPage()};	//取总页数
		//var totalPage = getUrlParam($('.pagination > li').eq(-2).find('a').eq(0).attr('href')).page;	//取总页数
		var totalPage = $('.pagination > li').eq(-2).text();	//取总页数
		var myurl = $(this).attr("href");
		//alert(myurl);
		var urlparam = getUrlParam(myurl);
		var path = getPath(myurl);
		var cur_page = parseInt(urlparam.page);
 		$.ajax({
			url:'ajaxGetSaleslist',
     		data:		urlparam,
     		success:function(response){
   				var pagestring = "";		//页签 的html代码串
     			var displaypages = 5;		//左右各显示的页签数量
   				//这是第一个<<
				if(1 == cur_page){
					//如果是第一页，禁止前翻
					pagestring += '<li class="disabled"><span>«</span></li>';
				}else{
					myparam = urlparam;
					myparam.page = parseInt(cur_page) - 1;
					pagestring += '<li><a href="'+ path +'?'+ formatUrl(myparam) + '">«</a></li>';
				};
				
     			if(totalPage <= displaypages*2+1){
     				//如果总页数比需要显示的页数少，则全部显示
   					//带页码的全部页签
     				for(var i=1;i<=totalPage;i++){
     					if(i == cur_page){
     						pagestring += '<li class="disabled"><span>'+i+'</span></li>';
     					}else{
	   						myparam = urlparam;
	   						myparam.page = i;
		     				pagestring += '<li><a href="'+path +'?'+ formatUrl(myparam)+'">'+i+'</a></li>';
   						}
   					}
   				}else{
     				//如果总页数大于显示的页数
   					//左侧
     				if(cur_page-1<=displaypages){
     					//如果不够displaypages，就全部显示
     					for(var i=1 ;i<cur_page;i++){
   							myparam = urlparam;
   	   						myparam.page = i;
   		     				pagestring += '<li><a href="'+path +'?'+ formatUrl(myparam)+'">'+i+'</a></li>';
     					}
     				}else{
     					myparam = urlparam;
   						myparam.page = 1;
     					pagestring += '<li><a href="'+path +'?'+ formatUrl(myparam)+'">1</a></li>';
     					pagestring += '<li class="disabled"><span>...</span></li>';
     					for(var i=-3;i<0;i++){
     						myparam = urlparam;
     						myparam.page = parseInt(cur_page) + i;
     						pagestring += '<li><a href="'+path +'?'+ formatUrl(myparam)+'">'+myparam.page+'</a></li>';
     					}
     				}
     				//自己，当前页
     				pagestring += '<li class="active"><span>'+ cur_page+'</span></li>';
     				//右侧
     				if(totalPage - cur_page <= displaypages){
     					//如果不够displaypages，就全部显示
     					for(var i=cur_page+1 ;i<=totalPage;i++){
   	   						myparam = urlparam;
   	   						myparam.page = i;
   		     				pagestring += '<li><a href="'+path +'?'+ formatUrl(myparam)+'">'+i+'</a></li>';
     					}
     				}else{
     					for(var i=1;i<=3;i++){
     						myparam = urlparam;
     						myparam.page = parseInt(cur_page) + i;
     						pagestring += '<li><a href="'+path +'?'+ formatUrl(myparam)+'">'+myparam.page+'</a></li>';
     					}
     					pagestring += '<li class="disabled"><span>...</span></li>';
     					myparam = urlparam;
   						myparam.page = totalPage;
     					pagestring += '<li><a href="'+path +'?'+ formatUrl(myparam)+'">'+myparam.page+'</a></li>';
     				}
     			}; 
				//最后一个>>
   				if(totalPage == cur_page){
					//如果是最后一页，禁止前翻
					pagestring += '<li class="disabled"><span>»</span></li>';
				}else{
					myparam = urlparam;
					myparam.page = parseInt(cur_page) + 1;
					pagestring += '<li><a href="'+ path +'?'+ formatUrl(myparam) + '">»</a></li>';
				};
				//alert(pagestring);
				$("ul.pagination").html(pagestring);
				$("#salestable").html(response);
			}
 		
		})
	});

})