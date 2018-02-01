function ActiveMyquery(){
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
	
	$("#rela_table ").on('click','td', function(t){
		var rela_id =  $(this).parent().find('td').eq(0).html();
		$.ajax({
			url: getrelation,
			data:{
				id:rela_id,
			},
     		success:function(response){
     			$('#relaform').html(response[1]);
     			$('#reladialogg .modal-title small').html(response[0]);
     			$('#reladialogg').modal('show');
			}
		})
	});
	
	$("#pages").on("click", ".pagination>li > a", function(t){
		t.preventDefault();
		var myurl = $(this).attr("href");
		var urlparam = getUrlParam(myurl);
 		$.ajax({
			url:getsalelist,
     		data:		urlparam,
     		beforeSend:function(){
     			//alert(waitingImg);
     			var html = '<img src="' + waitingImg + '" /><span style="margin-left:10px;">查询中，请稍侯......</span>';
     			$('#mydialogg div.modal-body').html(html);
      			$('#mydialogg').modal('show');
     		},
     		success:function(response){
				$('#mydialogg').modal('hide');
				$("#pages").html(response['page']);
				$("#salestable").html(response['items']);
				$(".text-muted").html('共'+response['total']+'条记录');
			    $('#salestable td').contextMenu('salelistmenu', menuobj);
			}
 		
		})
	});
	$("#myform").on("submit", function(event) {
		  event.preventDefault();
		  $.ajax({
			  url:getsalelist,
			  data:$(this).serialize(),
			  beforeSend:function(){
				  var html = '<img src="' + waitingImg + '" /><span style="margin-left:10px;">查询中，请稍侯......</span>';
	     			$('#mydialogg div.modal-body').html(html);
	     			$('#myquery').modal('hide');
	      			$('#mydialogg').modal('show');
	     		},
			  success:function(response){
				  $('#mydialogg').modal('hide');
					$("#pages").html(response['page']);
					$("#salestable").html(response['items']);
					$(".text-muted").html('共'+response['total']+'条记录');
					$('#salestable td').contextMenu('salelistmenu', menuobj);
				  //$("#salestable").html(response);
			  },
		  })
		  //alert('');
	});
	$("#search_form").on("submit", function(event) {
		event.preventDefault();
//		alert('');
//		var id = $("#search_form input[name='community_id']").val();  
//		var name = $("#search_form input[name='commName']").val();  
//		if( id=='' && name == '') {  
//			$('#mydialogg div.modal-body').html('数据不能为空,关闭窗口继续');
//			$('#mydialogg').modal('show');
//	        return false;  
//	    } ;
	    $.ajax({
	    	url:getcommname,
	    	data:$(this).serialize(),
	    	success:function(response){
//	    		alert(typeof(response));
	    		console.log(response);
				if('object' === typeof(response)){
					var html = '<img src="' + waitingImg + '" /><span style="margin-left:10px;">正在跳转' + response.comm_name + '中......</span>';
	     			$('#mydialogg div.modal-body').html(html);
	      			$('#mydialogg').modal('show');
					//$("#mydialogg div.modal-body").html(response.comm_id);
					window.location.href= myself + "?community_id=" + response.comm_id;
				}else{
					$("#mydialogg div.modal-body").html(response);
				}
				$('#mydialogg').modal('show');
			  },
	    })
	    //$("#search_form").submit();
		
	});
//	//利用下拉列表，协助生成where查询
//	jQuery("body").on("change",".wherefield", 		
//		function(){
//			alert('');
//			var where = $("textarea[name='where']").val();
//			var thisval =  $(".wherefield").val() + ' = ""';
//			if('' == where){
//				where = thisval;
//			}else{
//				where += ' AND ' + thisval;
//			}
//			$("textarea[name='where']").val(where);
//	});
	
	jQuery("body").on("change","#sele_field", 		
			function(){
		var where = $("textarea[name='where']").val();
		var thisval =  $("#sele_field").val() + ' = ""';
		if('' == where){
			where = thisval;
		}else{
			where += ' AND ' + thisval;
		}
		$("textarea[name='where']").val(where);
	});
	
	//修改关联规则
	$('#relaform').on('click','#modi_rela',function(){
		var id = $('#reladialogg .modal-header h4 small span').text();
		//alert(id);
		$.ajax({
			url:modiurl,
			data: $.param({'rela_id':id,}) + '&' + $('#relaform').serialize(),
			dataType: "json",
			beforeSend:function(){
				$('#reladialogg').modal('hide');
			},
			success:function(response){
				//alert(response);
				if(response){
					var html = '<img src="' + waitingImg + '" /><span style="margin-left:10px;">修改成功,正在刷新页面...</span>';
				}else{
					var html = '<span style="margin-left:10px;">修改失败</span>';
				}
				$('#mydialogg div.modal-body').html(html);
				$('#mydialogg').modal('show');
				if(response){
					$('#relaform').submit();
				}
			},
		});
	});
	
	//删除关联规则
	$('#relaform').on('click','#del_rela',function(){
		var id = $('#reladialogg .modal-header h4 small span').text();
		//alert(id);
		$.ajax({
			url:delurl,
			data: {
				rela_id:id,
			},
			beforeSend:function(){
				$('#reladialogg').modal('hide');
			},
			success:function(response){
				//alert(response);
				if(response['num']){
					var html = '<img src="' + waitingImg + '" /><span style="margin-left:10px;">删除成功,正在刷新页面...</span>';
				}else{
					var html = '<span style="margin-left:10px;">删除失败</span>';
				}
				$('#mydialogg div.modal-body').html(html);
				$('#mydialogg').modal('show');
				if(response['num']){
					$('#rela_table').html(response['str']);
				};
				$('#mydialogg').modal('hide');
				
			},
		});
	});
	
	//提交关联规则表单时，提示等候
//	$('#relaform').on('submit',wait('正在刷新页面中，请稍侯......',true))
//	
//	//定义一个提示等候的方法
//	function wait(message,iswaiting){
//		var html = '';
//		if(iswaiting){
//			html += '<img src="' + waitingImg + '" />';
//		}
//		html += '<span style="margin-left:10px;">' + message + '</span>';
//		$('#relaform').modal('hide');
//		$('#mydialogg div.modal-body').html(html);
//		$('#mydialogg').modal('show');
//	}
	
	//利用下拉小区名称列表，获取小区id，
	jQuery("body").on("change","#rela_c", 		
			function(){
		var thisval =  $("#rela_c").val();
		$("input[name='rela_comm_id']").val(thisval);
	});
	
//	//利用下拉列表，协助生成order查询
//	jQuery("#orderfield").on("change", 		
//		function(){
//			var thisval =  $("#orderfield").val() ;
//			$("input[name='order']").val(thisval);
//	});
	
//	//利用下拉列表，协助生成update查询
//	jQuery("#setfield").on("change", 		
//		function(){
//			var set = $("input[name='set']").val();
//			var thisval =  $("#setfield").val()+ ' = ""';
//			if('' == set){
//				set = thisval ;
//			}else{
//				set += ' ,' + thisval ;
//			}
//			$("input[name='set']").val(set);
//	});
	
	//重置按钮
//	jQuery("#reset").on("click", 		
//		function(){
//		$("input[name='set']").val('');
//		$("input[name='order']").val('') ;
//		$("textarea[name='where']").val('');
//	});
	
	jQuery('#add_rela').on("click",function(){
		var comm = $('#myquery input[name="community_id"]').val();
		$.ajax({
			url: getrelation,
			data:{
				community_id:comm,
			},
     		success:function(response){
     			$('#relaform').html(response[1]);
     			$('#reladialogg .modal-title small').html(response[0]);
     			$('#reladialogg').modal('show');
			}
		})
	});
	
	$('#del_err').on("click",function(){
		var id = $('#myquery input[name="community_id"]').val();
       	$.ajax({
     		url:del_err_comm,
     		data:{
     			ID:id,
     		},
     		success:function(response){  
      			response = '<p>成功删除'+response+'条记录</p>';
      			$('#mydialogg div.modal-body').html(response);
      			$('#mydialogg h4.modal-title').html(thiscomm+'异常记录清理结果');
      			$('#mydialogg').modal('show');
		        }  
     	});
	});

})