;$(function(){
	//var lastsel2;
	$("#list").jqGrid({
		url:'{:url('')}',  //请求数据的url地址
		datatype: "json",  //请求的数据类型
		colNames:['编号','小区名称','区域','片区','小区编号', '片区编号','关键词','地址','优先'], //数据列名称（数组）
		colModel:[ //数据列各参数信息设置
			{name:'ID',index:'ID', width:40,align:'center', title:false},
			{name:'comm_name',editable:true,index:'comm_name', width:80, title:false},
			{name:'region',editable:true,index:'region',align:'center', width:40, title:false},
			{name:'block',editable:true,index:'block', align:'center',width:60, title:false},
			{name:'comm_id',editable:true,index:'comm_id',align:'center', width:70},
			{name:'block_id',editable:true,index:'block_id',align:'center', width:40},
			{name:'keywords',editable:true,index:'keywords', width:200},
			{name:'comm_addr',editable:true,index:'comm_addr', width:200},
			{name:'pri_level',editable:true,index:'pri_level', width:30,align:'center'}	
		],
		rowNum:30, //每页显示记录数
		rowList:[10,20,30], //分页选项，可以下拉选择每页显示记录数
		pager: '#pager',  //表格数据关联的分页条，html元素
		autowidth: true, //自动匹配宽度
		height:500,   //设置高度
		gridview:true, //加速显示
		viewrecords: true,  //显示总记录数
		//multiselect: true,  //可多选，出现多选框
		//multiselectWidth: 25, //设置多选列宽度
		sortable:true,  //可以排序
		sortname: 'ID',  //排序字段名
		sortorder: "desc", //排序方式：倒序，本例中设置默认按id倒序排序

		loadComplete:function(data){ //完成服务器请求后，回调函数
			if(data.records==0){ //如果没有记录返回，追加提示信息，删除按钮不可用
				$("p").appendTo($("#list")).addClass("nodata").html('找不到相关数据！');
				$("#del_btn").attr("disabled",true);
			}else{ //否则，删除提示，删除按钮可用
				$("p.nodata").remove();
				$("#del_btn").removeAttr("disabled");
			}
		}
	 });

	
	//从数据库中取出片区的数据，放到hidden字段里去
	$.get('ediGrid.php',{
			action:'block',
		},function(response,status,xhr){
			$("#data_hide").html(response);
	}); 
	
	//从数据库中取出区域数据，放到select中去
	$.get('ediGrid.php',{
			action:'region',
		},function(response,status,xhr){
			var $s = "<option value=''></option>" + response;
			$("#search_region").html($s);		//根据选定的区域，添加该区域的片区列表
	}); 
	
	//当区域字段发生变化时，加载相应的片区选项
	$("#search_region").change(function(){
		var re = $("#search_region").val();
		if(re !== ""){
			$("#search_block").html($("#data_hide datalist[name='"+re+"']").html());
		}else{
			$("#search_block").html();
		};
	});
	
	
	$("#del_btn").click(function() {
		var gr = $("#list").jqGrid('getGridParam', 'selrow');
		if (gr != null){
			var rowData = $("#list").getRowData(gr);
			if(confirm("您是否确认删除？")){  
				$.ajax({  
					type: "GET",  
					url: "do.php?action=del",  
					data: "id="+rowData.ID,  
					beforeSend: function() {  
						$().message("正在请求...");  
					},  
					error:function(){  
						$().message("请求失败...");  
					},  
					success: function(msg){  
						if(msg!=0){  
							$("#list").jqGrid('delRowData',gr);
							$().message("已成功删除!");  
						}else{  
							$().message("操作失败！");  
						}  
					}  
				});  
			}  

		}else{
			alert("Please Select Row");
		}
			
				// 'editGridRow', gr, {
				// height : 300,
				// reloadAfterSubmit : false
			// });
		
			
	});
	
	//修改记录
	$("#edi_btn").click(function(){
		//得到所在行数据
		var id = $("#list").jqGrid("getGridParam","selrow");		//id返回的是该行记录在当前页面上是第几条记录
		
		if (id != null){
			var rowData = $("#list").getRowData(id);
			$.fancybox({
				'type':'ajax',
				'href':'ediGrid0.html',
				onComplete:function(){
					//把待修改的数据内容传递到表单去
					$("#region_list option:contains(" + rowData.region + ")").attr("selected","selected");	//修改记录：把原有的区域值设为当前选项
					$('#block_hidden').val(rowData.block);			//传递片区值到ediGrid0.html文件，在ediGrid0.html文件中实现”把原有的片区值设为当前选项“的功能。这个方法不太理想
					$('#id_hidden').val(rowData.ID);
					refresh_block();
					//如果直接使用以下语句来实现把原有的片区值设为当前选项，总不成功。估计是因为异步加载顺序的原因
					//$("#block_list option[value='"+rowData.block+"']").attr("selected",true);
					$('#comm_name').val(rowData.comm_name);
					$('#keywords').val(rowData.keywords);
					$('#comm_addr').val(rowData.comm_addr);
					$('#pri_level').val(rowData.pri_level);
					$('#block_id').val(rowData.block_id);
					$('#comm_id').val(rowData.comm_id);
					
				},
			}); 
		}else{
			alert("Please Select Row");
		}
		  

		
	});	
	
	//查找记录
	$("#find_btn").click(function() { 
		//如果把取到的值赋给一个变量，再传给jqGrid，中文会有乱码问题，直接来反而没有
			// var keywords = escape($("#search_keywords").val()); 
			// var region = escape($("#search_region").val()); 
			// var block = escape($("#search_block").val()); 
			// var address = escape($("#search_address").val()); 
		$("#list").jqGrid('setGridParam', { 
			url: "do.php?action=list", 
			postData: {
				'keywords': $("#search_keywords").val(), 
				'region': $("#search_region").val(),
				'block': $("#search_block").val()==null ? "" : $("#search_block").val(),		//避免block传null时，查询失败
				'address':$("#search_address").val(),
				}, 
			page: 1 
		}).trigger("reloadGrid"); 
		//清空所有查询字段内的内容
		$("#search_keywords").attr("value","");
		$("#search_region").attr("value","");
		$("#search_block").attr("value","");
		$("#search_address").attr("value","");
	}); 
	
	//增加记录
	$("#add_btn").click(function(){
		$.fancybox({
			'type':'ajax',
			'href':'addGrid.html'
		});
	});	
	

});