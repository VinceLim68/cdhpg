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
	
	var my_area_price = echarts.init(document.getElementById('echarts_area_price'));
	var my_floor_price = echarts.init(document.getElementById('echarts_floor_price'));
	var my_builded_year_price = echarts.init(document.getElementById('echarts_builded_year_price'));
       
	$('a[data-toggle = "tab"]').on('shown.bs.tab',function(e){
		if(e.target.attributes['type'] != undefined ){
			var type = e.target.attributes['type'].value;
			//console.log(type);
			option = setEcharts(type);
			if(type=="area"){
				mycharts = my_area_price;
			}else if(type=="total_floor"){
				mycharts = my_floor_price;
			}else if(type=="builded_year"){
				mycharts = my_builded_year_price;
			}else{
				return;
			}
			//console.log(option);
			mycharts.resize();
			mycharts.setOption(option,true);
		}
	})
	
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
		//关联首页签的小区搜索框，可以跳转另一个小区
		event.preventDefault();
	    $.ajax({
	    	url:getcommname,
	    	data:$(this).serialize(),
	    	success:function(response){
	    		//console.log(response);
				if('object' === typeof(response)){
					var html = '<img src="' + waitingImg + '" /><span style="margin-left:10px;">正在跳转' + response.comm_name + '中......</span>';
	     			$('#mydialogg div.modal-body').html(html);
	      			$('#mydialogg').modal('show');
					window.location.href= myself + "?community_id=" + response.comm_id;
				}else{
					$("#mydialogg div.modal-body").html(response);
				}
				$('#mydialogg').modal('show');
			  },
	    })
		
	});
	
	$('.tab-pane .scatter_btn').on('click',function(event){
		//散点图的过滤按钮
		var tab_pane = $(this).parent().parent().parent().parent();
		var thisinput = $(this).prev().val();
		$.ajax({
			url:getscatter,
			data:{
				community_id 	: $('#hidefeild').attr('community_id'),
				rela_comm_id 	: $('#hidefeild').attr('rela_comm_id'),
				rela_ratio 		: $('#hidefeild').attr('rela_ratio'),
				where 			: $('#hidefeild').attr('where'),
				rela_weight 	: $('#hidefeild').attr('rela_weight'),
				this_btn		: tab_pane.prop('id'),
				times			: thisinput,
			},
			success:function(list){
//				console.log(list);
				var str = tab_pane.prop('id');
				if(str.indexOf("area")!=-1){
					var type = 'area';
					var myecharttab = my_area_price;
				}else if(str.indexOf("floor")!=-1){
					var type = 'total_floor';
					var myecharttab = my_floor_price;
				}else if(str.indexOf("build")!=-1){
					var type = 'builded_year';
					var myecharttab = my_builded_year_price;
				}else{
					return;
				}
				var echarts_arr = [];
				for (var x in list)
			    {
			    	var item =[];
			    	item.push(list[x]['price']);
			    	item.push(list[x][type]);
			    	echarts_arr.push(item);
			    }
				option = getOption(type,echarts_arr)
				myecharttab.setOption(option,true);
				
//				var name = "#" + tab_pane.prop('id') + ' .panel-heading';
//				$(name).html(response);
			},
			
		});
	})
	
	jQuery("body").on("change","#sele_field", function(){
		//这个是由操作动态生成的展示关联规则详细内容的页面中用到的js
		var where = $("textarea[name='where']").val();
		var thisval =  $("#sele_field").val() + ' = ""';
		if('' == where){
			where = thisval;
		}else{
			where += ' AND ' + thisval;
		}
		$("textarea[name='where']").val(where);
	});
	
	//	修改过滤公式时，自动转化成memo
	jQuery("body").on("dblclick","input[name='memo']",function(){
		var replacedict = {
				' ':'',
				'%':'',
				'"':'”',
				"title":'标题', 
				"price":'单价', 
				"id":'序号', 
				"community_id":'小区编号', 
				"community_name":'小区名称', 
				"spatial_arrangement":'户型',
				"floor_index":'楼层',
				"total_floor":'总楼层',
				"builded_year":'建成年份',
				"area":'面积',
				"total_price":'总价',
				"advantage":'优势',
				">=":'不小于',
				"<=":'不大于',
				"like":'包含',
				">":'大于',
				"<":'小于',
				"and":'，并且',
				"or":'，或者',
				"=":'等于',
				'not':'不'
					};
		
	      
		var where = $("textarea[name='where']").val();
		for (var item in replacedict) {
			where = where.replace(new RegExp(item,'g'),replacedict[item]);
		}
		$("input[name='memo']").val(where);
//		alert(where);
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
//				alert(response);
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
	
	//增加关联规则
	$('#relaform').on('click','#add_a_relationship',function(){
		var id = $('#reladialogg .modal-header h4 small span').text();
		//alert(id);
		$.ajax({
			url:ajaxAddARelationship,
			data: $.param({'rela_id':id,}) + '&' + $('#relaform').serialize(),
			dataType: "json",
			beforeSend:function(){
				$('#reladialogg').modal('hide');
			},
			success:function(response){
				//alert(response);
				if(response == 0){
					var html = '增加失败。或者该小区已经定义相同子功能的关联规则，一个子功能只能有一个定义规则。';
				}else{
					var html = '<img src="' + waitingImg + '" /><span style="margin-left:10px;">修改成功,正在刷新页面...</span>';
				}
				$('#mydialogg div.modal-body').html(html);
				$('#mydialogg').modal('show');
				if(response != 0){
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
	
	//利用下拉小区名称列表，获取小区id，
	jQuery("body").on("change","#rela_c", 		
			function(){
		var thisval =  $("#rela_c").val();
		$("input[name='rela_comm_id']").val(thisval);
	});
	
	jQuery("body").on("change","#usage_select", 		
			function(){
		var thisval =  $("#usage_select").val();
		$("input[name='usage']").val(thisval);
	});
	
	
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

	$('#goblock').on("click",function(){
		var blockid = $('#rela h3 small').attr('blockid');
		window.location.href= goblodk + "?block_id=" + blockid;
	});
	
	
	$('#pricehistory').on("click",function(){
 		var community_id = $('#hidefeild').attr('community_id');
 		var usage = $('#hidefeild').attr('usage');
 		showPriceIndexInModal(community_id,usage);
	});
	
	function showPriceIndexInModal(community_id,usage){
		//var thiscomm = $('#hidefeild').attr('comm_name');
		$.ajax({
 			url:getpricehistory,
       	 	data:{
     			community_id:community_id,
     			usage:usage,
     		},
     		success:function(response){ 
     			//console.log(response);
     			setModalPriceHistoryEcharts(response);
     			if(response.price.length != 0){
     				if(usage==''){
     					$('#Echarts h4.modal-title').html(thiscomm+'房价走势图');
     				}else{
     					$('#Echarts h4.modal-title').html(thiscomm+'('+usage+')房价走势图');
     				}
     			}else{
         			$('#Echarts h4.modal-title').html(thiscomm+'房价数据不完整，无法展示');
     			}
     			$('#Echarts').modal('show');
	        }  
 		});
	}
	
	$('#calculatePriceIndex').on("click",function(){
		var community_id = $('#hidefeild').attr('community_id');
		var usage = $('#hidefeild').attr('usage');
		$.ajax({
			url: CalculatePriceIndexOfWholePeriodByCommID,
			data: {
				community_id: community_id,
				usage:usage,
			},
			success: function() {
				//重算基价后，直接弹出
				showPriceIndexInModal(community_id,usage);
			}
		});
	});
	
	
	$('#origin').on('click',function(){
		var community_id = $('#hidefeild').attr('community_id');
//		alert('');
		window.location.href= myself + "?community_id=" + community_id;
	})
})