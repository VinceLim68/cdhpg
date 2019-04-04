jQuery(function($) {
	
	//点击图标折叠目录
	$(".arrow").on("click",function(){
		var thisid = $(this).parent().parent().attr('itemId');
		//向上向下图标切换一下
		if($(this).hasClass("glyphicon-menu-up")){
			$(this).removeClass();
			$(this).addClass("arrow glyphicon glyphicon-menu-down");
		}else{
			$(this).removeClass();
			$(this).addClass("arrow glyphicon glyphicon-menu-up");
			
		}
		//把下级菜单显示或隐藏起来
		$("li[itemPid = "+thisid+"]").toggle();

	})
	
	//双击修改文本内容
	$("#todoList").on("dblclick",".note",function(){
		var text = $(this).text().replace(/^\s+|\s+$/g,"");
		var html = addInputGroupInputHtml(text);
		$(this).parents('.input-group').html(html);
		
	})
	
	//生成一个输入框的html
	function addInputGroupInputHtml(text){
		var html = '<input type="text" class="form-control" placeholder="Search for..." value="'+ text+'">'
		html += '<span class="input-group-btn">'
		html += '<button class="btn btn-default cancle" type="button"><span class="glyphicon glyphicon-remove" aria-hidden="true"></span></button>'
		html += '<button class="btn btn-default ok" type="button"><span class="glyphicon glyphicon-ok" aria-hidden="true"></span></button>'
		html += '</span>' 
		return html;
	}
	
	//点击打勾，保存修改文本
	$('#todoList').on('click','button.ok',function(){
		var inputtext = $(this).parents('.input-group').children('input').val();
		var pid = $(this).parents('li.item').attr('itempid');
		var id = $(this).parents('li.item').attr('itemId');
		var order = $(this).parents('li.item').attr('itemOrder')*1;
		var thisorder = order;
		var html = addInputGroupItemHtml(inputtext);
		var node = $(this).parents('li.item');
		var brotherNode = "li[itemPid='" + pid + "']";
		
		//把所有之后的兄弟节点的序号改一下
		var orders = {};
		node.nextAll(brotherNode).each(function(){
//			console.log($(this).attr('itemId'))
			order += 1;
			//把新的序号存在对象里，还要进数据库去改
			orders[$(this).attr('itemId')] = order;
			$(this).attr('itemOrder',order);
		});
//		console.log(JSON.stringify(orders));
		$(this).parents('.input-group').html(html);		//用输入内容替换原来的值,这个要最后操作，否则this都找不到了，定位元素会失败
		$.ajax({
			url: ajaxItem,
			type: "POST",
			data: {
				pid:pid,
				userid:'1',
				item:inputtext,
				id:id,
				order:thisorder,
				orders:JSON.stringify(orders),
			},
			success:function(response){
				//response返回id
				node.attr('itemname',inputtext);		//改变页面里的相应属性
				node.attr('itemId',response);			//对于新增的节点，还需要写进id
			},
		});
	})
	
	
	//点击取消，恢复原来文本
	$("#todoList").on('click','button.cancle',function(){
		//如果是新增的条目(itemid为空)，则直接移除本<li>
		var oriid = $(this).parents('li').attr('itemId');
		if (oriid == ''){
			$(this).parents('li').remove()
		}else{
			var oritext = $(this).parents('li').attr('itemName');
			var html = addInputGroupItemHtml(oritext)
			$(this).parents('.input-group').html(html);		//用输入内容替换原来的值
			
		}
	})
	
	//生成一个<input-group>里面的html
	function addInputGroupItemHtml(item){
	    var html = '<span  class="note">' + item + '</span>'
	    html += '<div class="input-group-btn">'
    	html += '<button type="button" class="btn btn-default dropdown-toggle" data-toggle="dropdown"'
    	html += ' aria-haspopup="true" aria-expanded="false"><span class="glyphicon glyphicon-option-vertical"></span></button>'
		html += '<ul class="dropdown-menu dropdown-menu-right">'
		html += '<li><a href="#">增加同级</a></li>'
		html += '<li><a href="#">增加下级</a></li>'
		html += '<li><a href="#">删除</a></li>'
		html += '<li role="separator" class="divider"></li>'
		html += '<li><a href="' + gotoNotes + '">进入</a></li>'
		html += '</ul>'
		html += '</div>'
		return html
	}
	
	//生成一个<li>条目的html代码
	function addLiItemHtml(itemcount,itempid,itemorder){
		var html = '<li class="item" style="display:block" itemId="" itemPid="' + itempid;
		html += '" itemCount="' + itemcount;
		html += '" itemName = "" itemOrder = "' + itemorder + '">';

		html += '<div class="col-lg-12" style="margin-left:' + (itemcount*20-20) +'px">';
		html += '<span	class="arrow glyphicon ' + (itemcount==1?'glyphicon-menu-up':'glyphicon-menu-down') + ' aria-hidden="true"></span>'
		html += '<div class="row">';
		html += '<div class="input-group">';
		html += addInputGroupInputHtml('')	;
				
		html += '</div></div></div></li>';
		return html;
	}
	
	//增加同级摘要（就在之后追加）
	$("#todoList").on('click','.addSameClass',function(){
		//先取得本处的li节点，一会在这个之后追加
		var node = $(this).parents('li.item');
		var itemcount = node.attr('itemCount');		//增加同级，则级别一样，父节点也一样
		var itempid = node.attr('itemPid');
		var itemid = node.attr('itemid');
		var itemorder = node.attr('itemOrder')*1 + 1;
		var html = addLiItemHtml(itemcount,itempid,itemorder);

		
		var findnext = "li[itemPid='" + itempid + "']";
		var findchild = "li[itemPid='" + itemid + "']";
		if (node.nextAll(findnext).length > 0){
			//如果有下一个同级兄弟节点，则插在其之前
			var nextnode = node.nextAll(findnext).eq(0);
			//把子节点隐藏起来
			node.nextAll().each(function(){
				if($(this).attr('itemId') == nextnode.attr('itemId')){
					return false;
				}
				$(this).hide();
			})
			nextnode.before(html)
			nextnode.prev().find('input').focus();
		}else if(node.nextAll(findchild).length > 0){
			//如果没有下一个兄弟节点（本节点是最后一个了），但有子节点，则插在所有子节点之后
			var lastchildnode = node.nextAll(findchild).last();
			
			//把子节点隐藏起来
			node.nextAll().each(function(){
				$(this).hide();
				if($(this).attr('itemId') == lastchildnode.attr('itemId')){
					return false;
				}
			})
			lastchildnode.after(html);
			lastchildnode.next().find('input').focus();

		}else{
			//即没有兄弟节点，也没有子节点，就直接在其后加
			node.after(html);
			node.next().find('input').focus();
		}

	})
	
	
	//增加次级摘要（就在之后追加）
	$("#todoList").on('click','.addChildClass',function(){
		//先取得本处的li节点
		var node = $(this).parents('li.item');
		var itemcount = node.attr('itemCount')*1 + 1;		//增加同级，则级别一样，父节点也一样
		var itempid = node.attr('itemid');				//新item的父节点是本 级id
		var itemorder = 1;
		
		var html = addLiItemHtml(itemcount,itempid,itemorder);
		
		node.after(html);
		node.next().find('input').focus();
		//注意这里的itemid是空的，要补充进去
	})
	
	/*
	 *功能： 模拟form表单的提交
	 *参数： URL 跳转地址 PARAMTERS 参数
	 */
	function Post(URL, PARAMTERS) {
		//创建form表单
		var temp_form = document.createElement("form");
		temp_form.action = URL;
		//如需打开新窗口，form的target属性要设置为'_blank'
		temp_form.target = "_self";
		temp_form.method = "post";
		temp_form.style.display = "none";
		//添加参数
		for (var item in PARAMTERS) {
			var opt = document.createElement("textarea");
			opt.name = PARAMTERS[item].name;
			opt.value = PARAMTERS[item].value;
			temp_form.appendChild(opt);
		}
		document.body.appendChild(temp_form);
		//提交数据
		temp_form.submit();
	}
	
	//跳转到笔记页面
	$("#todoList").on('click','.goIntoNotes',function(){
		var parames = new Array();
		var node = $(this).parents('li.item');
		var itemname = node.attr('itemname');		//增加同级，则级别一样，父节点也一样
		var itemid = node.attr('itemid');				//新item的父节点是本 级id
		parames.push({ name: "itemid", value:itemid });
		parames.push({ name: "itemname", value: itemname});
		Post(gotoNotes, parames);
		return false;
	})
	

	
	//这是一个测试keycode用的，可以删除
//	$("#todoList").on('keypress','input',function(e){
//		var keynum;
//		var keychar;
//		keynum = window.event ? e.keyCode : e.which;
//		keychar = String.fromCharCode(keynum);
//		alert(keynum + ' : ' +keychar);
//	})
	
	
	
});
//var todo = new Vue({
//    el: "#todo",
//    data: {
//        title: '',
//        willDo: [],
//        doneList: [],
//    },
//    beforeMount:function(){
//        // 加载localstorage
//        var storage=window.localStorage;
//        if(storage.getItem("todo") !== null){
//            this.willDo = JSON.parse(storage.getItem("todo"));
//        }
//        if(storage.getItem("done") !== null){
//            this.doneList = JSON.parse(storage.getItem("done"));
//        }
//    },
//    methods: {
//        addTodo: function() {
//            if (this.title === '') { return ''; }
//            this.willDo.unshift({ title: this.title, done: false });
//            this.title = '';
//            this.setLocalStorage();
//        },
//        deleteWillTodo: function(index) {
//            this.willDo.splice(index, 1);
//            this.setLocalStorage();
//        },
//        deleteDoneTodo: function(index) {
//            this.doneList.splice(index, 1);
//            this.setLocalStorage();
//        },
//        checkedTodo:function(index){
//            this.willDo[index].done = true;
//            this.doneList.unshift(this.willDo[index]);
//            this.willDo.splice(index,1);
//            this.setLocalStorage();
//        },
//        checkedDone:function(index){
//            this.doneList[index].done = false;
//            this.willDo.unshift(this.doneList[index]);
//            this.doneList.splice(index,1);
//            this.setLocalStorage();
//        },
//        setLocalStorage:function(){
//            // 存储localstorage
//            var storage=window.localStorage;
//            storage.setItem("todo",JSON.stringify(this.willDo));
//            storage.setItem("done",JSON.stringify(this.doneList));
//        }
//    }
//});
