jQuery(function($) {
	var grid_selector = "#gridtable";
	var pager_selector = "#gridtable_pager";

	var parent_column = $(grid_selector).closest('[class*="col-"]');
	// resize to fit page size
	$(window).on('resize.jqGrid', function() {
		$(grid_selector).jqGrid('setGridWidth', parent_column.width());
	})

	// resize on sidebar collapse/expand
	$(document).on(
			'settings.ace.jqGrid',
			function(ev, event_name, collapsed) {
				if (event_name === 'sidebar_collapsed'
						|| event_name === 'main_container_fixed') {
					// setTimeout is for webkit only to give time for DOM
					// changes and then redraw!!!
					setTimeout(function() {
						$(grid_selector).jqGrid('setGridWidth',
								parent_column.width());
					}, 20);
				}
			})


	jQuery(grid_selector).jqGrid({
		url : 'getComms', // 请求数据的url地址 {:url('getComms')}',//
		mtype : 'POST',
		datatype : "json", // 请求的数据类型
		colModel : [ // 数据列各参数信息设置
		{
			label : '编号',
			name : 'Id',
			index : 'Id',
			width : 40,
			align : 'center',
			key : true,
			editable : true,
		}, {
			label : '小区名称',
			name : 'comm_name',
			editable : true,
			editype : 'text',
			editoptions: {
				size:40, 
				maxlength: 15,
				dataEvents:[{ 
            	   type: 'change', 
            	   fn: function(e) {
            		   	var name = $("#comm_name").val();
            			var keyw = $("#keywords").val();
            			var n = keyw.indexOf(name);		//查找，关键字中是否有小区名称
            			if (n == -1){					//如果没有
            				if (keyw != ''){
            					keyw = name + ',' + keyw;
            				}else{
            					keyw = name
            				};
            				$("#keywords").val(keyw);
            			};
            	   },
               }],},
			editrules:{ required:true},
			index : 'comm_name',
			width : 80,
			editable : true,
		}, {
			label : '区域',
			name : 'region',
			editable : true,
			editrules:{ required:true},
			edittype: 'select',
		    editoptions: { 
               dataUrl: 'getRegion',
               dataEvents:[{ 
            	   type: 'change', 
            	   fn: function(e) {
            		   var re = $("#region").val();
            		   //alert(re);
            		   $("#block").val("");
            		   ajaxGetBlock(re);
            	   },
               }],
		    },
			index : 'region',
			align : 'center',
			width : 40,
		}, {
			label : '片区',
			name : 'block',
			editrules:{ required:true},
			editable : true,
			edittype:'text',
			index : 'block',
			align : 'center',
			width : 60,
		}, {
			label : '小区编号',
			name : 'comm_id',
			index : 'comm_id',
			editoptions: {size:10, maxlength: 15},
			align : 'center',
			width : 70,
			//editable : true,
		}, {
			label : '片区编号',
			name : 'block_id',
			//editable : true,
			editoptions: {size:10, maxlength: 10},
			index : 'block_id',
			align : 'center',
			width : 70,
		}, {
			label : '关键词',
			name : 'keywords',
			editrules:{ required:true},
			editable : true,
			edittype : 'textarea',
			editoptions: {rows:"2",cols:"38"},
			index : 'keywords',
			//width : 200,
		}, {
			label : '地址',
			name : 'comm_addr',
			editable : true,
			editype : 'text',
			editoptions: {size:40, maxlength: 100},
			index : 'comm_addr',
			//width : 200,
		}, {
			label : '级别',
			name : 'pri_level',
			editrules:{ required:true},
			editable : true,
			edittype : 'select',
			//edittype : 'checkbox',
			editoptions : {
				value: "0:小区级;1:区块级",
			},
			index : 'pri_level',
			width : 40,
			align : 'center',
		} ,{
			label:'操作',
			//name : 'myac',
			//index : '',
			width : 70,
			//fixed : true,
			sortable : false,
			resize : false,
			formatter : 'actions',
			formatoptions : {
				keys : true,
				// delbutton: false,//disable delete button

				delOptions : {
					recreateForm : true,
					beforeShowForm : beforeDeleteCallback,
					left:400,
					top:100,
				},
				editformbutton:true, 
				editOptions:{
					recreateForm: true,
					beforeShowForm:beforeEditCallback,
					closeAfterEdit: true,
					editCaption: "修改小区信息",					
					left:400,
					top:100,
					//width:650,
				},
			}
		}],
		viewrecords : true,
		rowNum:50,							// 每页显示记录数
		rowList:[20,50,100],
		pager : pager_selector,
		altRows: true,
		altclass:'ui-priority-secondary',
		toppager: true,

		multiselect : true,
		postData : {
			key1 : 'key1',
		},
		width : 960,
		height : 300,
		sortable : true, // 可以排序
		sortname : 'Id', // 排序字段名
		sortorder : 'desc',
		emptyrecords: "未发现符合条件的数据，请检查搜索条件",
		loadtext: "正在加载数据...",
		
		editurl : "editComm",
		//cellsubmit: "clientArray",
		//toolbar :  [true,"top"],
		loadComplete : function() {
			var table = this;
			setTimeout(function() {
				styleCheckbox(table);
				updateActionIcons(table);
				updatePagerIcons(table);
				enableTooltips(table);
			}, 0);
		},										//这个是下面翻页按钮的样式

	/*
	 * //direction: "rtl",
	 * 
	 * //subgrid options subGrid : true, //subGridModel: [{ name : ['No','Item
	 * Name','Qty'], width : [55,200,80] }], //datatype: "xml", subGridOptions : {
	 * plusicon : "ace-icon fa fa-plus center bigger-110 blue", minusicon :
	 * "ace-icon fa fa-minus center bigger-110 blue", openicon : "ace-icon fa
	 * fa-chevron-right center orange" }, //for this example we are using local
	 * data subGridRowExpanded: function (subgridDivId, rowId) { var
	 * subgridTableId = subgridDivId + "_t"; $("#" + subgridDivId).html("<table
	 * id='" + subgridTableId + "'></table>"); $("#" + subgridTableId).jqGrid({
	 * datatype: 'local', data: subgrid_data, colNames: ['No','Item
	 * Name','Qty'], colModel: [ { name: 'id', width: 50 }, { name: 'name',
	 * width: 150 }, { name: 'qty', width: 50 } ] }); },
	 * 
	 * 
	 * 
	 * data: grid_data, datatype: "local", height: 250, colNames:[' ',
	 * 'ID','Last Sales','Name', 'Stock', 'Ship via','Notes'], colModel:[
	 * {name:'myac',index:'', width:80, fixed:true, sortable:false,
	 * resize:false, formatter:'actions', formatoptions:{ keys:true,
	 * //delbutton: false,//disable delete button
	 * 
	 * delOptions:{recreateForm: true, beforeShowForm:beforeDeleteCallback},
	 * //editformbutton:true, editOptions:{recreateForm: true,
	 * beforeShowForm:beforeEditCallback} } }, {name:'id',index:'id', width:60,
	 * sorttype:"int", editable: true}, {name:'sdate',index:'sdate',width:90,
	 * editable:true, sorttype:"date",unformat: pickDate},
	 * {name:'name',index:'name', width:150,editable:
	 * true,editoptions:{size:"20",maxlength:"30"}},
	 * {name:'stock',index:'stock', width:70, editable:
	 * true,edittype:"checkbox",editoptions: {value:"Yes:No"},unformat:
	 * aceSwitch}, {name:'ship',index:'ship', width:90, editable:
	 * true,edittype:"select",editoptions:{value:"FE:FedEx;IN:InTime;TN:TNT;AR:ARAMEX"}},
	 * {name:'note',index:'note', width:150, sortable:false,editable:
	 * true,edittype:"textarea", editoptions:{rows:"2",cols:"10"}} ],
	 * 
	 * viewrecords : true, rowNum:10, rowList:[10,20,30], pager :
	 * pager_selector, altRows: true, //toppager: true,
	 * 
	 * multiselect: true, //multikey: "ctrlKey", multiboxonly: true,
	 * 
	 * loadComplete : function() { var table = this; setTimeout(function(){
	 * styleCheckbox(table);
	 * 
	 * updateActionIcons(table); updatePagerIcons(table); enableTooltips(table); },
	 * 0); },
	 * 
	 * editurl: "./dummy.php",//nothing is saved caption: "jqGrid with inline
	 * editing"
	 * 
	 * //,autowidth: true,
	 */

	/**
	 * , grouping:true, groupingView : { groupField : ['name'], groupDataSorted :
	 * true, plusicon : 'fa fa-chevron-down bigger-110', minusicon : 'fa
	 * fa-chevron-up bigger-110' }, caption: "Grouping"
	 */

	});
	$(window).triggerHandler('resize.jqGrid');// trigger window resize to make
												// the grid get the correct size

	//通过选中的片区得到区块列表
	function getBlockByRegion(){
		var id = jQuery(grid_selector).jqGrid('getGridParam','selrow');
/*		if (id)    {
	        var ret = jQuery(grid_selector).jqGrid('getRowData',id);
	        var r = ret.region;
	        //alert("id="+ret.comm_name+" region="+ret.region+"...");
	        ajaxGetBlock(r);
	    } else { alert("请选择一行！");}*/
		//因为是从行直接进行编辑的，无论如何都能获得
		var ret = jQuery(grid_selector).jqGrid('getRowData',id);
		var r = ret.region;
		ajaxGetBlock(r);
		
	};
	
	//Ajax通过片区取片块
	function ajaxGetBlock(r){
		if(r !== ""){
			$.ajax({
				url:'getBlock',
				data:{'reg':r},
				type:'get',
				success:function(response){  
					var blocks = '<option value="">' + response;
					//alert(blocks);
					blocks = '<datalist id="form_block_list" >' + blocks + '</datalist>';
					jQuery("#block").attr("list","form_block_list");		
					jQuery("#block").html(blocks);		
		        }  
			});
		}else{
			
		};
	}
	// enable search/filter toolbar
	// jQuery(grid_selector).jqGrid('filterToolbar',{defaultSearch:true,stringResult:true})
	// jQuery(grid_selector).filterToolbar({});

	// switch element when editing inline
	function aceSwitch(cellvalue, options, cell) {
		setTimeout(function() {
			$(cell).find('input[type=checkbox]').addClass(
					'ace ace-switch ace-switch-5').after(
					'<span class="lbl"></span>');
		}, 0);
	}
	// enable datepicker
	function pickDate(cellvalue, options, cell) {
		setTimeout(function() {
			$(cell).find('input[type=text]').datepicker({
				format : 'yyyy-mm-dd',
				autoclose : true
			});
		}, 0);
	}

	// navButtons
	jQuery(grid_selector).jqGrid(
			'navGrid',
			//'#search_div',
			pager_selector,
			{ // navbar options
				cloneToTop: true,
				edit : false,
				editicon : 'ace-icon fa fa-pencil blue',
				add : true,
				addicon : 'ace-icon fa fa-plus-circle purple',
				del : true,
				delicon : 'ace-icon fa fa-trash-o red',
				search : false,
				searchicon : 'ace-icon fa fa-search orange',
				refresh : true,
				refreshicon : 'ace-icon fa fa-refresh green',
				view : true,
				viewicon : 'ace-icon fa fa-search-plus grey',
			},
			{
				// edit record form
				closeAfterEdit: true,
				left: 400,
				// width: 700,
				recreateForm : true,
				editCaption: "修改",
				beforeShowForm : beforeEditCallback,
				/*function(e) {
					var form = $(e[0]);
					form.closest('.ui-jqdialog').find('.ui-jqdialog-titlebar')
							.wrapInner('<div class="widget-header" />')
					style_edit_form(form);
				}*/
			},
			{
				// new record form
				// width: 700,
				closeAfterAdd : true,
				addCaption: "新增小区",
				recreateForm : true,
				viewPagerButtons : false,
				beforeShowForm : function(e) {
					var form = $(e[0]);
					form.closest('.ui-jqdialog').find('.ui-jqdialog-titlebar')
							.wrapInner('<div class="widget-header" />')
					style_edit_form(form);
				},
				afterSubmit : function(response, postdata)
				{
					//alert(response.Text);
/*					var props = "" ; 
					// 开始遍历 
					for ( var p in response ){ // 方法 
						if ( typeof ( response [ p ]) == " function " ){ response [ p ]() ; 
					} else { // p 为属性名称，response[p]为对应属性的值 
						props += p + " = " + response [ p ] + " ;\n " ; 
					} } // 最后显示所有的属性 
					alert ( props ) ;*/
					//alert(response.responseText);
					if(response.responseText == 1){
						jQuery(grid_selector).jqGrid('setGridParam', {
							url: "getComms", 
							postData: {
								'keywords': postdata['comm_name'], 
								}, 
							page: 1 
						}).trigger("reloadGrid");
						//return [false,"已经有重复记录了"];
					}
					return [true,"已经有重复的记录了",];
				} ,
			},
			{
				// delete record form
				recreateForm : true,
				left:400,
				top:100,
				beforeShowForm : function(e) {
					var form = $(e[0]);
					if (form.data('styled'))
						return false;

					form.closest('.ui-jqdialog').find('.ui-jqdialog-titlebar')
							.wrapInner('<div class="widget-header" />')
					style_delete_form(form);

					form.data('styled', true);
				},
				onClick : function(e) {
					// alert(1);
				}
			},
			{
				// search form
				recreateForm : true,
				afterShowSearch : function(e) {
					var form = $(e[0]);
					form.closest('.ui-jqdialog').find('.ui-jqdialog-title')
							.wrap('<div class="widget-header" />')
					style_search_form(form);
				},
				afterRedraw : function() {
					style_search_filters($(this));
				},
				multipleSearch : true,
			/**
			 * multipleGroup:true, showQuery: true
			 */
			},
			{
				// view record form
				recreateForm : true,
				beforeShowForm : function(e) {
					var form = $(e[0]);
					form.closest('.ui-jqdialog').find('.ui-jqdialog-title')
							.wrap('<div class="widget-header" />')
				}
			})

	//取出片区值
	$.get('getRegion',
		function(response,status,xhr){
			var $s = '<option value="" ></option>' + response;
			//alert($s);
			jQuery("#search_region").html($s);		//根据选定的区域，添加该区域的片区列表
	}); 
	
	//当区域字段发生变化时，加载相应的片区选项
	jQuery("#search_region").on("change", 		
		function(){
			var re = $("#search_region").val();
			jQuery("#search_block").html("");
			jQuery("#s_block").val("");
			if(re !== ""){
				$.ajax({
					url:'getBlock',
					data:{'reg':re},
					type:'post',
					success:function(response){  
						var blocks = '<option value="">' + response;
						jQuery("#search_block").html(blocks);		
			        }  
				});
			};
	});
	
	jQuery("#comms-serch").on('keydown',function(event){
		if (event.keyCode == 13){
			jQuery("#find_btn").trigger("click");
		};
	});
	

	//查找记录
	jQuery("#find_btn").click(function() {
		jQuery(grid_selector).jqGrid('setGridParam', {
			url: "getComms", 
			postData: {
				'keywords': $("#search_keywords").val(), 
				'region': $("#search_region").val(),
				'block': $("#s_block").val()==null ? "" : $("#s_block").val(),		//避免block传null时，查询失败
				'address':$("#search_address").val(),
				}, 
			page: 1 
		}).trigger("reloadGrid"); 
		//清空所有查询字段内的内容
		$("#search_keywords").val('');
		$("#search_region").val('');
		$("#search_block").val('');
		$("#search_address").val('');
		
	}); 
	
	function style_edit_form(form) {
		// enable datepicker on "sdate" field and switches for "stock" field
		form.find('input[name=sdate]').datepicker({
			format : 'yyyy-mm-dd',
			autoclose : true
		})
		//alert('这是到style-edit_form');

		form.find('input[name=stock]').addClass('ace ace-switch ace-switch-5')
				.after('<span class="lbl"></span>');
		// don't wrap inside a label element, the checkbox value won't be
		// submitted (POST'ed)
		// .addClass('ace ace-switch ace-switch-5').wrap('<label class="inline"
		// />').after('<span class="lbl"></span>');

		// update buttons classes
		var buttons = form.next().find('.EditButton .fm-button');
		buttons.addClass('btn btn-sm').find('[class*="-icon"]').hide();// ui-icon,
																		// s-icon
		buttons.eq(0).addClass('btn-primary').prepend(
				'<i class="ace-icon fa fa-check"></i>');
		buttons.eq(1).prepend('<i class="ace-icon fa fa-times"></i>')

		buttons = form.next().find('.navButton a');
		buttons.find('.ui-icon').hide();
		buttons.eq(0).append('<i class="ace-icon fa fa-chevron-left"></i>');
		buttons.eq(1).append('<i class="ace-icon fa fa-chevron-right"></i>');
	}

	function style_delete_form(form) {
		var buttons = form.next().find('.EditButton .fm-button');
		buttons.addClass('btn btn-sm btn-white btn-round').find(
				'[class*="-icon"]').hide();// ui-icon, s-icon
		buttons.eq(0).addClass('btn-danger').prepend(
				'<i class="ace-icon fa fa-trash-o"></i>');
		buttons.eq(1).addClass('btn-default').prepend(
				'<i class="ace-icon fa fa-times"></i>')
	}

	function style_search_filters(form) {
		form.find('.delete-rule').val('X');
		form.find('.add-rule').addClass('btn btn-xs btn-primary');
		form.find('.add-group').addClass('btn btn-xs btn-success');
		form.find('.delete-group').addClass('btn btn-xs btn-danger');
	}
	function style_search_form(form) {
		var dialog = form.closest('.ui-jqdialog');
		var buttons = dialog.find('.EditTable')
		buttons.find('.EditButton a[id*="_reset"]').addClass(
				'btn btn-sm btn-info').find('.ui-icon').attr('class',
				'ace-icon fa fa-retweet');
		buttons.find('.EditButton a[id*="_query"]').addClass(
				'btn btn-sm btn-inverse').find('.ui-icon').attr('class',
				'ace-icon fa fa-comment-o');
		buttons.find('.EditButton a[id*="_search"]').addClass(
				'btn btn-sm btn-purple').find('.ui-icon').attr('class',
				'ace-icon fa fa-search');
	}

	function beforeDeleteCallback(e) {
		var form = $(e[0]);
		if (form.data('styled'))
			return false;

		form.closest('.ui-jqdialog').find('.ui-jqdialog-titlebar').wrapInner(
				'<div class="widget-header" />')
		style_delete_form(form);

		form.data('styled', true);
	}

	function beforeEditCallback(e) {
		var form = $(e[0]);
/*		var id = jQuery(grid_selector).jqGrid('getGridParam','selrow');
		if (id)    {
	        var ret = jQuery(grid_selector).jqGrid('getRowData',id);
	        alert("id="+ret.comm_name+" region="+ret.region+"...");
	    } else { alert("请选择一行！");}*/
		//alert(id);
		form.closest('.ui-jqdialog').find('.ui-jqdialog-titlebar').wrapInner(
				'<div class="widget-header" />')
		style_edit_form(form);
		getBlockByRegion();
	}

	// it causes some flicker when reloading or navigating grid
	// it may be possible to have some custom formatter to do this as the grid
	// is being created to prevent this
	// or go back to default browser checkbox styles for the grid
	function styleCheckbox(table) {
		/**
		 * $(table).find('input:checkbox').addClass('ace') .wrap('<label />')
		 * .after('<span class="lbl align-top" />')
		 * 
		 * 
		 * $('.ui-jqgrid-labels th[id*="_cb"]:first-child')
		 * .find('input.cbox[type=checkbox]').addClass('ace') .wrap('<label
		 * />').after('<span class="lbl align-top" />');
		 */
	}

	// unlike navButtons icons, action icons in rows seem to be hard-coded
	// you can change them like this in here if you want
	function updateActionIcons(table) {
		/**
		 * var replacement = { 'ui-ace-icon fa fa-pencil' : 'ace-icon fa
		 * fa-pencil blue', 'ui-ace-icon fa fa-trash-o' : 'ace-icon fa
		 * fa-trash-o red', 'ui-icon-disk' : 'ace-icon fa fa-check green',
		 * 'ui-icon-cancel' : 'ace-icon fa fa-times red' };
		 * $(table).find('.ui-pg-div span.ui-icon').each(function(){ var icon =
		 * $(this); var $class = $.trim(icon.attr('class').replace('ui-icon',
		 * '')); if($class in replacement) icon.attr('class', 'ui-icon
		 * '+replacement[$class]); })
		 */
	}

	// replace icons with FontAwesome icons like above
	function updatePagerIcons(table) {
		var replacement = {
			'ui-icon-seek-first' : 'ace-icon fa fa-angle-double-left bigger-140',
			'ui-icon-seek-prev' : 'ace-icon fa fa-angle-left bigger-140',
			'ui-icon-seek-next' : 'ace-icon fa fa-angle-right bigger-140',
			'ui-icon-seek-end' : 'ace-icon fa fa-angle-double-right bigger-140'
		};
		$('.ui-pg-table:not(.navtable) > tbody > tr > .ui-pg-button > .ui-icon')
				.each(
						function() {
							var icon = $(this);
							var $class = $.trim(icon.attr('class').replace(
									'ui-icon', ''));

							if ($class in replacement)
								icon.attr('class', 'ui-icon '
										+ replacement[$class]);
						})
	}

	function enableTooltips(table) {
		$('.navtable .ui-pg-button').tooltip({
			container : 'body'
		});
		$(table).find('.ui-pg-div').tooltip({
			container : 'body'
		});
	}

	// var selr = jQuery(grid_selector).jqGrid('getGridParam','selrow');

	$(document).one('ajaxloadstart.page', function(e) {
		$.jgrid.gridDestroy(grid_selector);
		$('.ui-jqdialog').remove();
	});
	
});
