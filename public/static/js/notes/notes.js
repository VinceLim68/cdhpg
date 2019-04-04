jQuery(function($) {
	
	//打勾保存
	$('#essay').on('click','button.ok',function(){
		var inputtext = $(this).parents('.input-group').children('input').val();
		var pid = $(this).parents('li.item').attr('itempid');
		var id = $(this).parents('li.item').attr('itemId');
		var order = $(this).parents('li.item').attr('itemOrder')*1;
		var thisorder = order;
		//var html = addInputGroupItemHtml(inputtext);
		var node = $(this).parents('li.item');
		var brotherNode = "li[itemPid='" + pid + "']";
		alert(brotherNode);
		
		//把所有之后的兄弟节点的序号改一下
		var orders = {};
		node.nextAll(brotherNode).each(function(){
			order += 1;
			//把新的序号存在对象里，还要进数据库去改
			orders[$(this).attr('itemId')] = order;
			$(this).attr('itemOrder',order);
		});
		console.log(orders);
	})
});

