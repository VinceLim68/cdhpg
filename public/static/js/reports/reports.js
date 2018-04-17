jQuery(function($) {
//	function form_refresh(object){
//		//刷新页面
//		for(var Key in object){
//			$("textarea[name='"+ Key +"']").val(object[Key]);
//			$("input[name='"+ Key +"']").val(object[Key]);
//		}
//	};
//    $("input[name='username']").val()
	$("form input,form select").on('change',function(){
		form_default();
		//当与税收有关的内容发生变动时，重新获得税率
//		$.ajax({
//			url:getRentTaxRate,
//			data:$('#income_value_process').serializeArray(),
//			type:'post',
//			dataType:"json",
//			success:function(response){  
//				form_refresh(response);
//	        }  
//		});
	})
	
	function form_default(){
		
		//自动计算
		var monthly_rent = $("input[name='monthly_rent']").val();
		var rent_loss = monthly_rent*12*$("input[name='vacancy_rate']").val()/100;
		var other_income = monthly_rent*1*$("input[name='a_year_interest_rate']").val()*(1-$("input[name='vacancy_rate']").val()/100)/100;
		other_income = myround(other_income);
		var vacancy_rate = $("input[name='vacancy_rate']").val();
		var property_type = $("select[name='property_type']").val();
		var authority_type = $("select[name='authority_type']").val();
		var VAT_start_value = $("input[name='VAT_start_value']").val();
		var area = $("input[name='area']").val();
		var location_type = $("select[name='location_type']").val();

		$("input[name='rent_loss']").val(rent_loss);
		$("textarea[name='vacancy_rate_desc']").val('根据估价人员对类似'+
				property_type+'物业的空置水平调查，一般年均空置率为'+ vacancy_rate +'%。');
		$("textarea[name='rent_loss_desc']").val('由于有押金，其它租金损失基本不会发生，故本次估价年空置与租金损失='
				+ monthly_rent +'×12×' + vacancy_rate + '% = ' + rent_loss + '元/平方米。');
		
		$("input[name='other_income']").val(other_income);
		$("textarea[name='other_income_desc']").val('其他收入主要是押金产生的利息，根据租赁市场一般惯例，押金为一个月的房租，利率取一年期银行存款利率'
				+ $("input[name='a_year_interest_rate']").val() +'%，但要考虑在空置时期没有押金收入。故其他收入=月租金×1×（1-空置率）×一年存款利率 = '
				+ monthly_rent +'×1×（1-' + vacancy_rate + '%）× ' + $("input[name='a_year_interest_rate']").val()
				+ '% = '+ other_income +'元/平方米。');
		var annual_income = myround(monthly_rent*12 - rent_loss + other_income);
		$("input[name='annual_income']").val(annual_income);
		
		//增值税率
		var $rent = myround(monthly_rent*area/1.05);
	    if(authority_type=='个人'){            //如何是个人
	    	if($rent <= VAT_start_value){        //月租金小于起征点,不区分住宅与否
	    		$("input[name='value_added_tax_rate']").val('0');
	    		var value_added_tax_rate = $("input[name='value_added_tax_rate']").val();
            	$("textarea[name='value_added_tax_rate_desc']").val('根据国家及当地政府有关增值税征收规定，私房出租增值税起征点为'+
            			VAT_start_value+'元，本估价对象的不含税收入为'+$rent+'元，小于增值税起征点，属于免征范围。');
	    	}else{
	    		if(property_type == '住宅'){       //是住宅
	    			$("input[name='value_added_tax_rate']").val('1.5');
	            	var value_added_tax_rate = $("input[name='value_added_tax_rate']").val();
//	                $value_added_tax = $input['monthly_rent']/1.05 * $input['value_added_tax_rate']/100;
	            	$("textarea[name='value_added_tax_rate_desc']").val('根据国家及当地政府有关增值税征收规定，个人出租住房类增值税起征点为'
	            			+VAT_start_value+'元，本估价对象的不含税收入为'+$rent+'元，其增值税减按'
		   	                +value_added_tax_rate+'%征收率：增值税 ＝ 年有效毛收入/（1+5%） × 增值税率 = '
		   	                +annual_income+'/(1+5%)×'+value_added_tax_rate+'%。');
	    		}else{
	    			$("input[name='value_added_tax_rate']").val('5');
	    			var value_added_tax_rate = $("input[name='value_added_tax_rate']").val();
	    			$("textarea[name='value_added_tax_rate_desc']").val('本估价对象的不含税收入为'+$rent+
	    					'元，需要缴纳增值税。根据国家及当地政府有关增值税征收规定，个人出租非住房类增值税按'+value_added_tax_rate
	    					+'%征收率：增值税 ＝ 月租金收入/（1+5%） × 增值税率 = '
	    					+annual_income+'/(1+5%)×'+value_added_tax_rate+'%。');
	    		}
	    	}
        }else{			//企业的增值税
        	var Have_time = $("select[name='Have_time']").val();
        	if(authority_type=='一般纳税人' && Have_time=='2016年5月1日后'){ 
        		$("input[name='value_added_tax_rate']").val('11');
    			$("textarea[name='value_added_tax_rate_desc']").val('一般纳税人出租其2016年5月1日后取得的不动产，适用一般计税方法计税，'
    					+'适用税率11%。应预缴税款＝含税销售额÷（1+11%）×3%='+annual_income+'/(1+11%)×3%。');
        	}else{
        		$("input[name='value_added_tax_rate']").val('5');
    			$("textarea[name='value_added_tax_rate_desc']").val('一般纳税人出租其2016年4月30日前取得的不动产，或小规模纳税人'
    					+'出租不动产，按照5%的征收率计算应纳税额。应预缴税款＝含税销售额 ÷（1+5%）×5%='+annual_income+' ÷（1+5%）×5%。');
        	}
        	var value_added_tax_rate = $("input[name='value_added_tax_rate']").val();
        };
        
        //增值税附加
	    if(location_type=='市区'){
	    	$("input[name='addition_rate']").val('12');
	        $("textarea[name='addition_rate_desc']").val('增值税附加以增值税为征收基数，包括城建税、教育费附加、地方教育附加，在'
	           +location_type+'，城建税税率为7%，教育费附加3%，地方教育附加2%，增值税附加 = 增值税 ×(7%+3%+2%)= 增值税 ×'
	           +$("input[name='addition_rate']").val()+'%。');
	    }else if (location_type=='县镇'){
	    	$("input[name='addition_rate']").val('10');
	        $("textarea[name='addition_rate_desc']").val('增值税附加以增值税为征收基数，包括城建税、教育费附加、地方教育附加，在'
	           +location_type+'，城建税税率为5%，教育费附加3%，地方教育附加2%，增值税附加 = 增值税 ×(5%+3%+2%)= 增值税 ×'
	           +$("input[name='addition_rate']").val()+'%。');
	    }else{
	    	$("input[name='addition_rate']").val('6');
	        $("textarea[name='addition_rate_desc']").val('增值税附加以增值税为征收基数，包括城建税、教育费附加、地方教育附加，在'
	           +location_type+'，城建税税率为1%，教育费附加3%，地方教育附加2%，增值税附加 = 增值税 ×(1%+3%+2%)= 增值税 ×'
	           +$("input[name='addition_rate']").val()+'%。');
	    };
	    var addition_rate = $("input[name='addition_rate']").val();
	    
	    
	    //增值税及说明
	    if(authority_type=='一般纳税人' && Have_time=='2016年5月1日后'){
	    	var value_added_tax_and_additional = myround(annual_income/1.11*0.03*(1+addition_rate/100));
	    	$("input[name='value_added_tax_and_additional']").val(value_added_tax_and_additional);
	    	$("textarea[name='value_added_tax_and_additional_desc']").val('增值税 ＝ 年有效毛收入/（1+11%） ×'+
	    			'3%×（1+增值税附加）='+annual_income+'/（1+11%） ×3%×（1+'+addition_rate+'%）='
	    			+value_added_tax_and_additional+'元/平方米。');
	    }else{
	    	var value_added_tax_and_additional = myround(annual_income/1.05*value_added_tax_rate/100*(1+addition_rate/100));
	    	$("input[name='value_added_tax_and_additional']").val(value_added_tax_and_additional);
	    	$("textarea[name='value_added_tax_and_additional_desc']").val('增值税 ＝ 年有效毛收入/（1+5%） ×'+
	    			value_added_tax_rate+'%×（1+增值税附加）='+annual_income+'/（1+5%） ×'+
	    			value_added_tax_rate+'%×（1+'+addition_rate+'%）='+value_added_tax_and_additional+'元/平方米。');
	    }
	    
	    //房产税
	    if(property_type == '住宅'){
	    	//无论企业个人
	    	var property_tax = myround(0.04*annual_income/1.05);
	    	$("input[name='property_tax']").val(property_tax);
	    	$("textarea[name='property_tax_desc']").val('个人出租住房，企事业单位、社会团体以及其他组织按市场价格向个人出租用于居住的住房，减按4%的税率征收房产税：房产税＝年有效毛收入/(1+5%)×4% = '
	    			+annual_income+'/(1+5%)×4% ='+property_tax+'元/平方米。');
	    }else{
	    	var property_tax = myround(0.12*annual_income/1.05);
	    	$("input[name='property_tax']").val(property_tax);
	    	$("textarea[name='property_tax_desc']").val('出租非住房类，根据税务规定，房产税税率为12%：房产税＝年有效毛收入/(1+5%)×12%='
	    			+annual_income+'/(1+5%)×12% ='+property_tax+'元/平方米。');
	    };
	    
	    //所得税
	    if(authority_type=='个人'){            //如何是个人缴纳所得税
	    	if(property_type == '住宅'){
	    		var income_tax = myround(annual_income/1.05*0.05*0.1);
	    		$("input[name='income_tax']").val(income_tax);
	    		$("textarea[name='income_tax_desc']").val('个人出租住房时，应纳个人所得税=增值税不含税收入×5%×税率 '+
	    				'，根据有关规定，其所得减按10％的税率征收，所得税='+annual_income+'/(1+5%)×5%×10% ='
	    				+income_tax+'元/平方米。');
	    	}else{
	    		var income_tax = myround(annual_income/1.05*0.05*0.2);
	    		$("input[name='income_tax']").val(income_tax);
	    		$("textarea[name='income_tax_desc']").val('个人出租其他房屋时，应纳个人所得税=增值税不含税收入×5%×税率 '+
	    				'，根据有关规定，其所得按照20%税率征收个人所得税，所得税='+annual_income+'/(1+5%)×5%×20% ='
	    				+income_tax+'元/平方米。');
	    	}
	    }else{
	    	var income_tax = 0;
    		$("input[name='income_tax']").val(income_tax);
    		$("textarea[name='income_tax_desc']").val('因企业出租房屋所得与其他收入合并计算所得税，各企业构成相差较大，'+
    				'理论上不动产的价值不会因为不同的持有者而不同，为保证估价测算的客观性，在这里中暂不考虑企业所得税对价值的影响。');
	    };
	    
	    //其他零星税收
	    if(authority_type=='个人' && property_type == '住宅'){
	    	var little_tax = 0;
	    	$("input[name='little_tax']").val(little_tax);
	    	$("textarea[name='little_tax_desc']").val('其他零星税收主要包括土地使用税、印花税等，对个人出租住房均予免征。');
	    }else{
	    	var little_tax = myround(annual_income/1.05*0.01);
	    	$("input[name='little_tax']").val(little_tax);
	    	$("textarea[name='little_tax_desc']").val('其他零星税收主要包括土地使用税、印花税等，其中：土地使用税 ＝ 每平方米土地年税额÷12×占地面积（土地使用税根据所处地段年税额每平方米4至25元），'
	    			+'印花税 ＝ 租赁合同总金额×0.01%,这类税收金额较小，统一按不含税收入的1%计取。');
	    };
	    
	    //其他税收合计
	    var other_tax = myround(parseFloat(little_tax) + parseFloat(income_tax) + parseFloat(property_tax));
    	$("input[name='other_tax']").val(other_tax);
    	$("textarea[name='other_tax_desc']").val('其他税收 = 房产税 + 所得税 + 其他零星税收='+property_tax+'+'
    			+income_tax+'+'+little_tax+'='+other_tax+'元/平方米。');
	    
    	//重置成本
    	var building_cost = parseFloat($("input[name='building_cost']").val());
    	var b_low = (building_cost*0.9/100).toFloor(0)*100;
    	var b_high = (building_cost*1.1/100).toCeil(0)*100;
    	$("textarea[name='building_cost_desc']").val('根据当地建筑工程造价指标资料，同类房屋重置价在'+
    			b_low+'~'+b_high+'元/㎡范围内，根据估价对象实际情况，本次估价取'+building_cost+'元/㎡作为重置成本。');
    	//保险费率
    	$("textarea[name='insurance_rate_desc']").val('根据保险公司相关的收费标准，保险费按建筑物重置价格'+$("input[name='insurance_rate']").val()+'%计。');
    	//保险费
    	var insurance = myround(building_cost*$("input[name='insurance_rate']").val()/100);
    	$("input[name='insurance']").val(insurance);
    	$("textarea[name='insurance_desc']").val('保险费＝重置价×保险费率＝'+building_cost+'×'
    			+$("input[name='insurance_rate']").val()+'%='+insurance+'元/平方米。');
    	//管理费率
    	$("textarea[name='management_rate_desc']").val('管理费按正常水平及市场惯例取房地产年有效毛收入的'+$("input[name='management_rate']").val()+'%计取。');
    	//管理费
    	var management_expense = myround(annual_income*$("input[name='management_rate']").val()/100);
    	$("input[name='management_expense']").val(management_expense);
    	$("textarea[name='management_expense_desc']").val('管理费=年有效毛收入(含税）×管理费率='
    			+annual_income+'×'+ $("input[name='management_rate']").val()+'%='+management_expense+'元/平方米。');
    	//维修费率
    	$("textarea[name='maintenance_rate_desc']").val('按正常水平及市场惯例，并结合估价对象实际情况，维修费取房屋重置价的'+$("input[name='maintenance_rate']").val()+'%。');
    	//维修费
    	var maintenance_cost = myround(building_cost*$("input[name='maintenance_rate']").val()/100);
    	$("input[name='maintenance_cost']").val(maintenance_cost);
    	$("textarea[name='maintenance_cost_desc']").val('维修费=房屋重置价×维修费率='
    			+building_cost+'×'+$("input[name='maintenance_rate']").val()+'%='+maintenance_cost+'元/平方米。');
    	
	    //年运营费用
    	var annual_cost = myround(maintenance_cost + management_expense + insurance 
    		+ other_tax + value_added_tax_and_additional);
    	$("input[name='annual_cost']").val(annual_cost);
    	//年净收益
    	var A = myround(annual_income - annual_cost);
    	$("input[name='annual_net_income']").val(A);
    	//报酬率Y
    	var rate_of_return = $("input[name='a_year_interest_rate']").val()/1 + $("input[name='risk_compensation_rate']").val()/1
    			+$("input[name='management_compensation_rate']").val()/1 + $("input[name='lack_liquidity_compensastion_rate']").val()/1
    			+$("input[name='easy_access_to_financing_rate']").val()/1 + $("input[name='income_tax_deduction_rate']").val()/1;
    	var Y = rate_of_return/100;
    	$("input[name='rate_of_return']").val(rate_of_return);
    	$("textarea[name='rate_of_return_desc']").val('本次估价采用累加法求取报酬率，无风险报酬率取价值时点银行一年期存款基准利率；'
    			+'风险报酬率是指承担额外的风险所要求的补偿，具体是:风险报酬率=投资风险补偿率+管理负担补偿率+缺乏流动性补偿率-易于获得融资的优惠率-所得税抵扣的优惠率，'
    			+'具体计算见《报酬率求取表》');
    	
    	//收益年期
    	//自动填写：根据土地开始时间填土地结束日期
    	//获取法定最高年限
    	var max_years;
    	if(property_type=="住宅" || property_type=="车位车库"){
    		max_years = 70;
    	}else if(property_type=="商业"){
    		max_years = 40;
    	}else{
    		max_years = 50;
    	}
    	if($("input[name='end_land_period']").val()=='' && $("input[name='begin_land_period']").val()!==''){
    		$("input[name='end_land_period']").val(addDate( $("input[name='begin_land_period']").val(),max_years));
    	}
    	//土地剩余年限
    	var foruse_land = getTime2Time($("input[name='end_land_period']").val(),$("input[name='date_of_value']").val());
    	//根据结构取建筑物的寿命
    	var build_usage_period ;
    	if($("select[name='building_struction']").val()=='钢混'){
    		build_usage_period = 60;
    	};
    	if($("select[name='building_struction']").val()=='砖混'){
    		build_usage_period = 50;
    	};

    	
    	t_desc = "根据估价对象有关权属资料记载，估价对象土地使用权期限自"+formatDate($("input[name='begin_land_period']").val())
	    	+"起至"+formatDate($("input[name='end_land_period']").val())+"止，价值时点（"+formatDate($("input[name='date_of_value']").val())
	    	+"）土地使用期限还剩余"+foruse_land+"年；";
    	//建筑物剩余年限
    	if($("input[name='newness_rate']").val()>0){
    		//如果有成新率，优先按成新率计算建筑物剩余经济寿命
    		var foruse_build = build_usage_period * $("input[name='newness_rate']").val()/100;
    		t_desc += "根据估价对象的建成年份和结构，并结合估价人员的现场查勘，确定估价对象的成新率为" + $("input[name='newness_rate']").val()
    			+ "%，";
    	}else{
    		var foruse_build = getTime2Time(parseInt($("input[name='builded_year']").val()) + build_usage_period,
    				$("input[name='date_of_value']").val());
    		t_desc += "估价对象建筑物建成于"+formatDate($("input[name='builded_year']").val(),2)
    			+ "，"+$("select[name='building_struction']").val()+"结构非生产用房的经济耐用年限为"
    			+ build_usage_period +"年，";
    	};
    	//收益期
    	var N = Math.min(foruse_land,foruse_build)
    	$("input[name='income_period']").val(N);
    	t_desc += "价值时点还剩余"+ foruse_build +"年。根据孰短原则，收益期取" +N + "年。";
    	
    	if(foruse_build > foruse_land){
    		t_desc += "由于无法获知土地出让合同约定建筑物收回时室无偿或有偿，根据抵押估价谨慎原则及本次估价假设，收益期结束后建筑物无偿收回，故不计建筑物残余价值。";
    	}
    	
    	$("textarea[name='income_period_desc']").val(t_desc);
    	
    	//递增率
    	var increase_rate = myround(parseFloat($("input[name='increase_rate']").val()));
    	var g = increase_rate/100;
    	//递增年限
    	var m = $("input[name='increase_years']").val();
    	$("textarea[name='increase_years_desc']").val("根据对当地类似房地产租赁市场近年来的租金行情走势分析，"
    			+"并对年净收益进行分析，同类物业年净收益逐年递增为"+ myround((increase_rate-1))+"%~"+(increase_rate+1)+"%。结合估价对象的实际情况和估价人员分析，"
    			+"并采用长期趋势法预期未来，设定本次估价中，估价对象在价值时点未来"+ m 
    			+"年内年净收益取逐年递增"+increase_rate+"%，之后净收益保持不变。");
    	
    	//剩余土地使用权价值
    	if(foruse_land - N > 0){
    		if($("input[name='base_land_price']").val() == 0){
    			$("textarea[name='remaining_land_value_desc']").val("需要输入基准地价！！！");
    			$("input[name='income_value']").val("缺基准地价");
    			$("#result").html("缺基准地价");
			}else{
				var land = myround($("input[name='base_land_price']").val()*$("input[name='base_land_price_increase']").val()*
						(foruse_land/max_years-N/max_years));
				$("input[name='remaining_land_value']").val(land);
				$("textarea[name='remaining_land_value_desc']").val("估价对象在价值时点，剩余土地使用年限"
						+foruse_land+"年，收益期为"+N+"年。根据厦府（2017）413号《厦门市人民政府关于印发厦门市城镇土地基准地价和厦门市地价征收管理若干规定的通知》(2018年2月1日起施行)，"
						+"“土地使用年期修正系数用于按不同用途实际使用年限与法定土地使用权最高年限折算，土地使用年限修正系数＝实际土地使用年期/法定土地使用权出让最高年限”，"
						+"估价对象所在区域的基准地价为"+$("input[name='base_land_price']").val()+"元/平方米，"
						+"经估价人员对本市本类用地市场价格调查，结合本市地价动态监测的相关数据，综合确定期日修正系数为"+$("input[name='base_land_price_increase']").val()
						+"，则：收益期届满后土地使用权剩余年限价值＝V"+foruse_land+"-V"+N+"="+$("input[name='base_land_price']").val()
						+'×'+$("input[name='base_land_price_increase']").val()+"×("+foruse_land+"/"+max_years+"-"
						+N+"/"+max_years+")="+land+"元/平方米");
			}
    	}else{
    		$("textarea[name='remaining_land_value_desc']").val("收益期满时土地使用权也同时到期，故其剩余土地使用权价值为0。");
    	};
    	
    	//收益价值
    	var income_value = A/(Y-g)*(1-Math.pow(((1+g)/(1+Y)),m))+
    		A*Math.pow(1+g,m)/Y/Math.pow(1+Y,m)*(1-1/Math.pow(1+Y,N-m))
    		+ land;
    	income_value = myround(income_value);
    	if(!isNaN(income_value)){
    		$("input[name='income_value']").val(income_value);
    		$("#result").html(income_value);
    	}
    	
	};
	
	$("#report_No").on('change',function(){
		//报告编号改变后，自动查询数据库并修改option
		var patt = /20\d{2}(0|1)\d{5}/;		//判断是否是报告编号的正则
		var No = $("#report_No").val();
		var isNo = patt.test(No);
		var option="";
		$.ajax({
		    type: "post",
		    data: {No:No,},
		    url:ajaxGetGjxmDetails,
		    datatype:'json',
		    beforeSend: function () {
		        if(!isNo){
		        	alert('输入的报告编号格式不正确，要重新输入！需要输入类似DY2018010001');
		        	return false;
		        }
		    },
		    success: function (data) {
		        for (item in data){
		        	console.log(data[item].fwlp);
		        	option += '<option value="'+data[item].GjxmdetailKID +'">'+data[item].fwlp+','
		        			+data[item].yt+'</option>';
		        	
		        };
		        $("#select_gjdx").empty();
		        $('#select_gjdx').append(option);
		    },
		});
	})
	
	$("#import_data").on('click',function(){
		//导入数据
		var uid = $("#select_gjdx").val();
		$.ajax({
		    type: "post",
		    data: {uid:uid,},
		    url: ajaxGetGjxmDetailsDatas,
		    datatype:'json',
		    success: function (data) {
		    	//console.log(data[0].jzjg);
		    	if(data.length == 0){
		    		alert('没有查询到数组')
		    	}else{
		    		//var checkText=jQuery("#select_id").find("option:selected").text(); 获取选择项
		    		$("input[name='area']").val(data[0].jzmj);
		    		$("input[name='begin_land_period']").val(data[0].tdksrq);
		    		$("input[name='end_land_period']").val(data[0].tdjsrq);
		    		$("input[name='builded_year']").val(data[0].jcnf);
		    		$("input[name='date_of_value']").val(data[0].gjdate);
		    		$("select[name='building_struction']").val(data[0].jzjg);
		    		$("select[name='property_type']").val(data[0].pgyt);
		    		$("select[name='authority_type']").val(data[0].qsrlx);
		    		$("select[name='location_type']").val(data[0].szqy);
		    		$("select[name='over_2_year']").val(data[0].IsTwoY);
		    		var cost = data[0].zcs *100;
		    		cost = Math.max(Math.min(cost,3500),900);
		    		$("input[name='building_cost']").val(cost);
		    		$("input[name='monthly_rent']").val(data[0].datavalue);
		    		$("input[name='gjxmdetailID']").val(uid);
		    		form_default();
		    	};
		    },
		});
	});
	
	$("#import_income_data").on('click',function(){
		//导入数据
		var uid = $("#select_gjdx").val();
		$.ajax({
			type: "post",
			data: {uid:uid,},
			url: ajaxGetLXIncomeFiniteData,
			datatype:'json',
			success: function (object) {
				console.log(object);
				for(var Key in object){
					$("textarea[name='"+ Key +"']").val(object[Key]);
					$("input[name='"+ Key +"']").val(object[Key]);
					$("select[name='"+ Key +"']").val(object[Key]);
				}
			},
		});
	})
	
	$("#work_done").on('click',function(){
		$.ajax({
			type:'post',
			data:$('#income_value_process').serializeArray(),
			dataType:'json',
			url:ajaxSaveIncomeValueProcess,
			success:function(rep){
				
			}
		
			
		});
	});
	
	function myround(num){
		var newnum = parseFloat(num).toRound(2);
		return(newnum);
	};
	
	//计算两个时间相差多少年
	function getTime2Time($time1, $time2)
	{
	    var time1 = arguments[0], time2 = arguments[1];
	    time1 = Date.parse(time1)/1000;
	    time2 = Date.parse(time2)/1000;
	    var time_ = time1 - time2;
	    time_ = time_/(3600*24)/365.25;
	    return time_.toRound(0);		//取整
	};
	//增加年数
	function addDate(date, years) {
        if (years == undefined || years == '') {
        	years = 1;
        }
        var date = new Date(date);
        date.setFullYear(date.getFullYear() + years);
        date.setDate(date.getDate() - 1);
        var month = date.getMonth() + 1;
        var day = date.getDate();
        return date.getFullYear() + '/' + (month) + '/' + (day);
    };
    
    //转化年月日格式
    function formatDate(date,type){
    	var type = arguments[1] ? arguments[1] : 1;
    	da = new Date(date);
        var year = da.getFullYear()+'年';
        var month = da.getMonth()+1+'月';
        var date = da.getDate()+'日';
        if(type==1){
        	return year+month+date;
        }else{
        	return year;
        }
    };

	
//	$("input[name='builded_year']").on('click',function(){
//		var t1 = $("input[name='begin_land_period']").val();
//		var t2 = $("input[name='end_land_period']").val();
//		alert(getTime2Time(t2, t1));
//	});
	
	
	//去尾法
	Number.prototype.toFloor = function (num) {
	return Math.floor(this * Math.pow(10, num)) / Math.pow(10, num);
	};

	//进一法
	Number.prototype.toCeil = function (num) {
	return Math.ceil(this * Math.pow(10, num)) / Math.pow(10, num);
	};

	//四舍五入法
	Number.prototype.toRound = function (num) {
	return Math.round(this * Math.pow(10, num)) / Math.pow(10, num);
	};
	
	//鼠标进入后textarea自动展开
	$('textarea').on('focusin',function(){
		this.rows = 3;
	});
	$('textarea').on('focusout',function(){
		this.rows = 1;
	});
})