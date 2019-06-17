function buildAddCommAddressForm(res){
//	alert(res['floors']==null);
//	alert(res);
//	alert(res['aaa'] != undefined );
//	alert(res['comm_id'] != undefined );
//	console.log(res);
//	console.log(res[0]!=undefined);
	var jsonstring = '<form style="margin: 1%;" id="addCommAddressForm">';
	jsonstring += '<div class="container-fluid" >';
	
	jsonstring += '<div class="row">';
	jsonstring += '<div class="col-md-2 col-xs-3 hide-sm" style="margin-top: 4px;">';
	jsonstring += '<div style="float: right;">版块：</div>';
	jsonstring += '</div>';
	jsonstring += '<div class="col-md-2 col-xs-9 hide-sm" style="margin-top: 4px;">'+ res['commInfo']['block']+'</div>';
	jsonstring += '<div class="col-md-2 col-xs-3  " style="margin-top: 4px;">';
	jsonstring += '<div style="float: right;">关键字：</div>';
	jsonstring += '</div>';
	jsonstring += '<div class="col-md-6 col-xs-9 hide-sm" style="margin-top: 4px;">'+ res['commInfo']['keywords']+'</div>';
	jsonstring += '</div>';
	
	jsonstring += '<div class="row">';
	jsonstring += '<div class="col-md-2 col-xs-3 hide-sm" style="margin-top: 4px;">';
	jsonstring += '<div style="float: right;">小区ID：</div>';
	jsonstring += '</div>';
	jsonstring += '<div class="col-md-2 col-xs-9 hide-sm" style="margin-top: 4px;">'+ res['commInfo']['comm_id']+'</div>';
	jsonstring += '<input type="hidden" name="comm_id" value="'+ res['commInfo']['comm_id']+'">';
	jsonstring += '<div class="col-md-2 col-xs-3 " style="margin-top: 4px;">';
	jsonstring += '<div style="float: right;">小区：</div>';
	jsonstring += '</div>';
	jsonstring += '<div class="col-md-2 col-xs-9">';
	jsonstring += '<input type="text" style="width:100%" name="comm_name" value="' + res['commInfo']['comm_name']+'" placeholder="小区名">';
	jsonstring += '</div>';
	jsonstring += '<div class="col-md-2 col-xs-3  " style="margin-top: 4px;">';
	jsonstring += '<div style="float: right;">城市：</div>';
	jsonstring += '</div>';
	jsonstring += '<div class="col-md-2 col-xs-9">';
	jsonstring += '<input type="text" style="width:100%" name="city" value="';
	if(res[0]!= undefined){
		jsonstring += (res[0]['city']==''?'厦门':res[0]['city'])+'" placeholder="城市">';		
	}else{
		jsonstring += '厦门'+'" placeholder="城市">';
	}
	jsonstring += '</div>	';
	jsonstring += '</div>';
	
	jsonstring += '<div class="row">';
	jsonstring += '<div style="margin-top: 4px;" class="col-md-2 col-xs-3" >';
	jsonstring += '<div style="float: right;">道路：</div>';
	jsonstring += '</div>';
	jsonstring += '<div class="col-md-2 col-xs-9">';
	jsonstring += '<input type="text"  style="width:100%" name="road" value="'+ (res[0]==undefined ?'':res[0]['road'])+'"placeholder="道路名">';
	jsonstring += '</div>';
	jsonstring += '<div style="margin-top: 4px;" class="col-md-2 col-xs-3 ">';
	jsonstring += '<div style="float: right;">从：</div>';
	jsonstring += '</div>';
	jsonstring += '<div class="col-md-2 col-xs-9">';
	jsonstring += '<input type="text" name="doorplate" style="width:100%" value="'+ (res[0]==undefined ?'':res[0]['doorplate'])+'" placeholder="门牌">';
	jsonstring += '</div>';
	jsonstring += '<div style="margin-top: 4px;" class="col-md-2 col-xs-3 ">';
	jsonstring += '<div style="float: right;">到：</div>';
	jsonstring += '</div>';
	jsonstring += '<div class="col-md-2 col-xs-9">';
	jsonstring += '<input type="text" name="doorplate2" style="width:100%;" placeholder="门牌截止">';
	jsonstring += '</div>';
	jsonstring += '</div>';
	

	jsonstring += '<div class="row">';
	jsonstring += '<div style="margin-top: 4px;" class="col-md-2 col-xs-3 ">';
	jsonstring += '<div style="float: right;">总层：</div>';
	jsonstring += '</div>';
	jsonstring += '<div class="col-md-2 col-xs-9">';
	if(res[0]!= undefined){
		jsonstring += '<input type="text" style="width:100%" name="floors"  value="'+(res['floors'] == null ? 0 : res['floors']) +'" placeholder="总层数">';
	}else{
		jsonstring += '<input type="text" style="width:100%" name="floors"  value="0" placeholder="总层数">';
	}
	jsonstring += '</div>';
	jsonstring += '<div class="col-md-2 col-xs-3  " style="margin-top: 4px;">';
	jsonstring += '<div style="float: right;">用途：</div>';
	jsonstring += '</div>';
	jsonstring += '<div class="col-md-2 col-xs-9">';
	if(res[0]!= undefined){
		jsonstring += '<input type="text"  style="width:100%"  list="typelist" name="type" value="'+(res['type'] == null  ? '住宅' : res['type'])+'" placeholder="物业类型">';
	}else{
		jsonstring += '<input type="text"  style="width:100%"  list="typelist" name="type" value="住宅" placeholder="物业类型">';
	}
	if(res['typelist'] != undefined ){
		jsonstring += '<datalist id="typelist">';
		for(var item in res['typelist']){
			jsonstring += '<option value="' +res['typelist'][item]+ '">';
		}
	  	jsonstring += '</datalist>';
	}
	jsonstring += '</div>';
	jsonstring += '<div class="col-md-2 col-xs-3 " style="margin-top: 4px;">';
	jsonstring += '<div style="float: right;">区块：</div>';
	jsonstring += '</div>';
	jsonstring += '<div class="col-md-2 col-xs-9">';
	jsonstring += '<input type="text"  style="width:100%" name="region" list="regionlist" value="'+ res['commInfo']['region']+'" placeholder="区块">';
	if(res['regionlist'] != undefined ){
		jsonstring += '<datalist id="regionlist">';
		for(var item in res['regionlist']){
			jsonstring += '<option value="' +res['regionlist'][item]+ '">';
		}
	  	jsonstring += '</datalist>';
	}
	jsonstring += '</div>';
	jsonstring += '</div>';
	
	
	jsonstring += '<div class="row">';
	jsonstring += '<div class="col-md-2 col-xs-3  " style="margin-top: 4px;">';
	jsonstring += '<div style="float: right;">电梯数：</div>';
	jsonstring += '</div>';
	jsonstring += '<div class="col-md-2 col-xs-9">';
	jsonstring += '<input type="text" style="width:100%" name="elevator" value="'+ (res[0]==undefined ?'':res[0]['elevator'])+'"placeholder="电梯数量">';
	jsonstring += '</div>';			
	jsonstring += '<div class="col-md-2 col-xs-3 " style="margin-top: 4px;">';
	jsonstring += '<div style="float: right;">结构：</div>';
	jsonstring += '</div>';
	jsonstring += '<div class="col-md-2 col-xs-9">';
	if(res[0]!= undefined){
		jsonstring += '<input type="text" style="width:100%" name="structure" list="structurelist" value="'+(res['structure'] == null ? '钢混结构' : res['structure'])+'" placeholder="建筑结构">';
	}else{
		jsonstring += '<input type="text" style="width:100%" name="structure" list="structurelist" value="" placeholder="建筑结构">';
	}
	if(res['structurelist'] != undefined ){
		jsonstring += '<datalist id="structurelist">';
		for(var item in res['structurelist']){
			jsonstring += '<option value="' +res['structurelist'][item]+ '">';
		}
	  	jsonstring += '</datalist>';
	}
	jsonstring += '</div>';
	jsonstring += '<div class="col-md-2 col-xs-3 " style="margin-top: 4px;">';
	jsonstring += '<div style="float: right;">建成：</div>';
	jsonstring += '</div>';
	jsonstring += '<div class="col-md-2 col-xs-9">';
	jsonstring += '<input type="text" style="width:100%" name="buildYear" value="'+ (res[0]==undefined ?'':res[0]['buildYear'])+'" placeholder="建成年份">';
	jsonstring += '</div>';
	jsonstring += '</div>';
	
	
	jsonstring += '<div class="row">';
	jsonstring += '<div style="margin-top: 4px;" class="col-md-2 col-xs-3 ">';
	jsonstring += '<div style="float: right;">楼牌：</div>';
	jsonstring += '</div>';
	jsonstring += '<div class="col-md-5 col-xs-9">';
	jsonstring += '<label class="radio-inline"><input type="radio" checked name="doortype" value="连续">连续</label>';
	jsonstring += '<label class="radio-inline"><input type="radio" name="doortype" value="单数">单数</label>';
	jsonstring += '<label class="radio-inline"><input type="radio" name="doortype" value="双数">双数</label>';
	jsonstring += '</div>';
	jsonstring += '<div class="col-md-3 col-xs-10 col-xs-offset-2 col-md-offset-1">';
	jsonstring += '<label class="checkbox-inline"><input type="checkbox" name="iscover">&nbsp;&nbsp; 覆盖原记录</label>';
	jsonstring += '</div>';
	jsonstring += '</div>';
	
	jsonstring += '</div>';
	
//	jsonstring += '</form><button class="btn btn-success btn-block" id="addcommaddresses"'
//		+ '>增加小区地址' + res.length + '</button>';
//	console.log(res);
	return jsonstring;
}

function buildCommAddressList(res){
	//console.log(res);
	var html = '<div class="table-responsive"><table class="table table-striped table-condensed">';
	var thislength = 0
    html += '<thead>';
    html += '<tr class="info">';
    //html += '<th>#</th>';
    html += '<th>路</th>';
    html += '<th>门牌</th>';
    html += '<th>建成</th>';
    html += '<th>总层</th>';
    html += '<th>用途</th>';
    html += '</tr>';
    html += '</thead>';
    html += '<tbody>';
    for (item in res)
    {
    	//console.log(item);
    	if(res[item].road != undefined){
    		html += '<tr>';
    		//html += '<th scope="row">1</th>';
    		html += '<td>' + res[item].road + '</td>';
    		html += '<td>' + res[item].doorplate + '</td>';
    		html += '<td>' + res[item].buildYear + '</td>';
    		html += '<td>' + res[item].floors + '</td>';
    		html += '<td>' + res[item].type + '</td>';
    		html += '</tr>';
    		thislength += 1;
    	}
    }
//    console.log(thislength);
    html += '</tbody>';
    html += '</table></div>';
    html = '</form><button class="btn btn-success btn-block" id="addcommaddresses"'
		+ '>增加小区地址(' + thislength + ')</button>' + html;
    return html;
}