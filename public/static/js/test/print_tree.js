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
//	$(".note").on("dblclick",function(){
	$("#todoList").on("dblclick",".note",function(){
		var html = '<input type="text" value="'+$(this).text().replace(/^\s+|\s+$/g,"") +'">';
		html += '<span class="ok glyphicon glyphicon-ok" aria-hidden="true"></span>';
		html += '<span class="ok glyphicon glyphicon-remove" aria-hidden="true"></span>';
		$(this).html(html);
	})
	
	//点击打勾，保存修改文本
	$("#todoList").on('click','.glyphicon-ok',function(){
		var inputtext = $(this).prev("input").val();
		inputhtml = inputtext + '<span class="right glyphicon glyphicon-remove-circle" aria-hidden="true" title="删除"></span>'
		inputhtml += '<span class="right glyphicon glyphicon-plus-sign" aria-hidden="true" title="增加下级摘要"></span>'
		inputhtml += '<span class="right glyphicon glyphicon-plus" aria-hidden="true" title="增加同级摘要"></span>'
		$(this).parents('li').attr('itemName',inputtext);	//改变页面里的相应属性
		$(this).parent().html(inputhtml);		//用输入内容替换原来的值,这个要最后操作，否则this都找不到了，定位元素会失败
		//还需要一个回写到数据库中的函数
	})
	
	//点击取消，恢复原来文本
	$("#todoList").on('click','.glyphicon-remove',function(){
		//如果是新增的条目(itemid为空)，则直接移除本<li>
		var oriid = $(this).parents('li').attr('itemId');
		if (oriid == ''){
			$(this).parents('li').remove()
		}else{
			var oritext = $(this).parents('li').attr('itemName');
			oritext += '<span class="right glyphicon glyphicon-remove-circle" aria-hidden="true" title="删除"></span>'
			oritext += '<span class="right glyphicon glyphicon-plus-sign" aria-hidden="true" title="增加下级摘要"></span>'
			oritext += '<span class="right glyphicon glyphicon-plus" aria-hidden="true" title="增加同级摘要"></span>'
			$(this).parent().html(oritext);		//用输入内容替换原来的值
			
		}
	})
	
	//生成一个<li>条目的html代码
	function addItemHtml(itemcount,itempid,fontweight){
		var html = '<li style="display:block" itemId="" itemPid="' + itempid;
		html += '" itemCount="' + itemcount;
		html += '" itemName = "">';
		html += '<p style="margin-left:' + (itemcount*20-20) +'px;font-weight:' + fontweight+';';
		html += 'font-size:' + (itemcount*-2+26) + 'px;">';
		html += '<span class="arrow glyphicon ' + (itemcount==1?'glyphicon-menu-up':'glyphicon-menu-down') + ' aria-hidden="true"></span>'
		html += '<span class="note">'
		html += '<input type="text" value="">';
		html += '<span class="ok glyphicon glyphicon-ok" aria-hidden="true"></span>';
		html += '<span class="ok glyphicon glyphicon-remove" aria-hidden="true"></span>';
		html += '</span>'
		html += '</p>'
		html += '</li>'
		return html
	}
	
	//增加同级摘要（就在之后追加）
	$("#todoList").on('click','.glyphicon-plus',function(){
		//先取得本处的li节点，一会在这个之后追加
		var node = $(this).parents('li');
		var itemcount = node.attr('itemCount');		//增加同级，则级别一样，父节点也一样
		var itempid = node.attr('itemPid');
		var fontweight = itemcount<=2 ? 'bold' : 'normal';
		
		var html = addItemHtml(itemcount,itempid,fontweight)
		
		node.after(html);
		node.next().find('input').focus();
	})
	
	//增加次级摘要（就在之后追加）
	$("#todoList").on('click','.glyphicon-plus-sign',function(){
		//先取得本处的li节点
		var node = $(this).parents('li');
		var itemcount = node.attr('itemCount')*1 + 1;		//增加同级，则级别一样，父节点也一样
		var itempid = node.attr('itemid');				//新item的父节点是本 级id
		var fontweight = itemcount<=2 ? 'bold' : 'normal';
		
		var html = addItemHtml(itemcount,itempid,fontweight)
		
		node.after(html);
		node.next().find('input').focus();
		//注意这里的itemid是空的，要补充进去
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
