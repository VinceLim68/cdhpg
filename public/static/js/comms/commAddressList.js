function ActiveMyquery(){
	$('#myquery').modal('show');
}
jQuery(function($) {
	$('.modal').draggable();			//设置模态框可移动
	$(".modal").css("overflow", "hidden");//禁止模态对话框的半透明背景滚动
	
	$('#mydialogg .modal-body').on('click','#sendmodify',function(event){
		$.ajax({
			url:ajaxUpdateCommAddressRecord,
			type:'POST',
			data:$('#modifyCommAddressForm').serialize(),
			success: function(msg){
				$('#mydialogg ').modal('hide');
				$('#modal-title').html('信息')
				if(msg==1){
                    $('#mydialogg  div.modal-body').html('记录修改成功'); 
            	}else{
            		$('#mydialogg  div.modal-body').html('记录未成功修改'); 
            	}
				$('#mydialogg ').modal('show');
//				window.location.href = myself;
				window.location.reload();
   		   	},	
		});
	})
	
	$("#search_form").on("submit", function(event) {
		//关联首页签的小区搜索框，可以跳转另一个小区
		event.preventDefault();
	    $.ajax({
	    	url:getcommname,
	    	data:$.param({'from':'commAddressList',}) + '&' + $(this).serialize(),
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
})