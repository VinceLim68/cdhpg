function modal(){
	$('#myquery').modal('show');
}
function getUrlParam(url){
	//这个是从url中把参数拆分开来
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
	$("#pages").on("click", ".pagination>li > a", function(t){
		t.preventDefault();
		//var totalPage = {$saleslist->lastPage()};	//取总页数
		//var totalPage = getUrlParam($('.pagination > li').eq(-2).find('a').eq(0).attr('href')).page;	//取总页数
		//var totalPage = $('.pagination > li').eq(-2).text();	//取总页数
		var myurl = $(this).attr("href");
		//alert(myurl);
		var urlparam = getUrlParam(myurl);
		//var path = getPath(myurl);
		//var cur_page = parseInt(urlparam.page);
 		$.ajax({
			url:'ajaxGetSaleslist',
     		data:		urlparam,
     		beforeSend:function(){
     			$('#mydialogg div.modal-body').html('正在查询，请稍侯......');
      			$('#mydialogg').modal('show');
     		},
     		success:function(response){
   				
				//alert(pagestring);
				$('#mydialogg').modal('hide');
				//$("ul.pagination").html(pagestring);
				$("#pages").html(response['page']);
				$("#salestable").html(response['items']);
				$(".text-muted").html('共'+response['total']+'条记录');
			}
 		
		})
	});
	$("#myform").on("submit", function(event) {
		  event.preventDefault();
		  $.ajax({
			  url:'ajaxGetSaleslist',
			  data:$(this).serialize(),
			  beforeSend:function(){
	     			$('#mydialogg div.modal-body').html('正在查询，请稍侯......');
	     			$('#myquery').modal('hide');
	      			$('#mydialogg').modal('show');
	     		},
			  success:function(response){
				  $('#mydialogg').modal('hide');
					$("#pages").html(response['page']);
					$("#salestable").html(response['items']);
					$(".text-muted").html('共'+response['total']+'条记录');
				  //$("#salestable").html(response);
			  },
		  })
		  //alert('');
	});
	//利用下拉列表，协助生成where查询
	jQuery("#wherefield").on("change", 		
		function(){
			var where = $("input[name='where']").val();
			var thisval =  $("#wherefield").val() + ' = ""';
			if('' == where){
				where = thisval;
			}else{
				where += ' AND ' + thisval
			}
			$("input[name='where']").val(where);
	});
	
	//利用下拉列表，协助生成order查询
	jQuery("#orderfield").on("change", 		
		function(){
			var thisval =  $("#orderfield").val() ;
			$("input[name='order']").val(thisval);
	});
	
	//利用下拉列表，协助生成update查询
	jQuery("#setfield").on("change", 		
		function(){
			var set = $("input[name='set']").val();
			var thisval =  $("#setfield").val()+ ' = ""';
			if('' == set){
				set = thisval ;
			}else{
				set += ' ,' + thisval ;
			}
			$("input[name='set']").val(set);
	});
	
	//重置按钮
	jQuery("#reset").on("click", 		
		function(){
		$("input[name='set']").val('');
		$("input[name='order']").val('') ;
		$("input[name='where']").val('');
	});

})