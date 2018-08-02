<?php

namespace app\report\controller;

//use think\View;
//use app\evalu\controller\Common;
use think\Db;
use think\Controller;
use app\report\model\EasyPGGjxmdetailModel;
use app\report\model\EasyPGLxIncomeFiniteModel;

class Index extends Controller {
	public function index() {
		// '这是报告系统的入口';
// 		$sql = 'select COLUMN_NAME,COLUMN_COMMENT from information_schema.columns where table_name = "increment_of_finite_year_income_method"  ';
// 		$result = Db::query($sql);
// 		$result = Db::table('increment_of_finite_year_income_method')
// 		          ->field('COLUMN_NAME')
// 		          ->select();
// 		dump($result);
//         $Gjxmdetail = new EasyPGGjxmdetailModel();

        
//         halt($result);
        $options = config('default_options');
        $values = config('default_values');
        $parameter = [
            ['caption'=>'物业类型','name'=>'property_type','class'=>'edit','options'=>$options['property_type']],
            ['caption'=>'建筑面积','name'=>'area','class'=>'edit','memo'=>'元/平方米','readonly'=>false],
            ['caption'=>'价值时点','name'=>'date_of_value','class'=>'edit','placeholder'=>'年/月/日','readonly'=>false],
            ['caption'=>'土地开始','name'=>'begin_land_period','class'=>'edit','placeholder'=>'年/月/日','readonly'=>false],
            ['caption'=>'土地结束','name'=>'end_land_period','class'=>'edit','placeholder'=>'年/月/日','readonly'=>false],
            ['caption'=>'建成年份','name'=>'builded_year','class'=>'edit','placeholder'=>'例如：1995','readonly'=>false],
            ['caption'=>'建筑结构','name'=>'building_struction','class'=>'edit','options'=>$options['building_struction']],
            ['caption'=>'成新率','name'=>'newness_rate','class'=>'edit','placeholder'=>'例如：70','memo'=>'%'],
            ['caption'=>'权属人','name'=>'authority_type','class'=>'edit','options'=>$options['authority_type']],
            ['caption'=>'所在区域','name'=>'location_type','class'=>'edit','options'=>$options['location_type']],
            ['caption'=>'满2年否','name'=>'over_2_year','class'=>'edit','options'=>['是','否']],
            ['caption'=>'基准地价','name'=>'base_land_price','memo'=>'元/平方米'],
            ['caption'=>'上涨系数','name'=>'base_land_price_increase','placeholder'=>'例如：1.01','value'=>$values['base_land_price_increase']],
            ['caption'=>'取得时间','name'=>'Have_time','class'=>'edit','options'=>['2016年4月30日前','2016年5月1日后']],
            ['caption'=>'起征点','name'=>'VAT_start_value','class'=>'tax','placeholder'=>'增值税起征点','value'=>$values['VAT_start_value'],'memo'=>'元/月','readonly'=>false],
        ];
        
        $reward = [
            ['caption'=>'一年利率','name'=>'a_year_interest_rate','class'=>'edit','memo'=>'%','value'=>$values['a_year_interest_rate']],
            ['caption'=>'风险补偿','name'=>'risk_compensation_rate','class'=>'edit','memo'=>'%','value'=>$values['risk_compensation_rate']],
            ['caption'=>'管理补偿','name'=>'management_compensation_rate','class'=>'edit','memo'=>'%','value'=>$values['management_compensation_rate']],
            ['caption'=>'流动补偿','name'=>'lack_liquidity_compensastion_rate','class'=>'edit','memo'=>'%','value'=>$values['lack_liquidity_compensastion_rate']],
            ['caption'=>'易于融资','name'=>'easy_access_to_financing_rate','class'=>'edit','memo'=>'%','value'=>$values['easy_access_to_financing_rate']],
            ['caption'=>'税收抵扣','name'=>'income_tax_deduction_rate','class'=>'edit','memo'=>'%','value'=>$values['income_tax_deduction_rate']],
        ];
        
        $fields = [
            ['serial'=>'(一)','caption'=>'年有效毛收入','name'=>'annual_income',
               'memo'=>'*年有效毛收入=月租金*12-空置和租金损失+其他收入','readonly'=>false,],
            ['serial'=>'(1)','caption'=>'月租金(元/月/平方米)','class'=>'level2','name'=>'monthly_rent',
               'memo'=>'*根据上文已求取的市场租金(设定为含税收入)',],
            ['serial'=>'(2)','caption'=>'空置和租金损失','class'=>'level2','name'=>'rent_loss',
               'memo'=>'rent_loss_desc','readonly'=>false,],
            ['serial'=>'','caption'=>'空置率(%)','class'=>'level3','name'=>'vacancy_rate',
               'memo'=>'vacancy_rate_desc','value'=>$values['vacancy_rate']],
            ['serial'=>'(3)','caption'=>'其他收入','class'=>'level2','name'=>'other_income',
               'memo'=>'other_income_desc','readonly'=>false,],
            ['serial'=>'','caption'=>'一年期存款利率(%)','class'=>'level3','name'=>'a_year_interest_rate',
               'memo'=>'*','value'=>$values['a_year_interest_rate']],
            ['serial'=>'(二)','caption'=>'年运营费用','name'=>'annual_cost',
               'memo'=>'*年运营费用 =增值税及附加+其他税收+保险费+管理费+维修费','readonly'=>false,],
            ['serial'=>'(1)','caption'=>'增值税及附加','class'=>'level2','name'=>'value_added_tax_and_additional',
                'memo'=>'value_added_tax_and_additional_desc','readonly'=>false,],
            ['serial'=>'','caption'=>'增值税率(%)','class'=>'level3','name'=>'value_added_tax_rate',
                'memo'=>'value_added_tax_rate_desc','readonly'=>false,],            
            ['serial'=>'','caption'=>'增值税附加(%)','class'=>'level3','name'=>'addition_rate',
                'memo'=>'addition_rate_desc','readonly'=>false,],            
            ['serial'=>'(2)','caption'=>'其他税收','class'=>'level2','name'=>'other_tax',
                'memo'=>'other_tax_desc','readonly'=>false,],
            ['serial'=>'','caption'=>'房产税','class'=>'level3','name'=>'property_tax',
                'memo'=>'property_tax_desc','readonly'=>false,],
            ['serial'=>'','caption'=>'所得税','class'=>'level3','name'=>'income_tax',
                'memo'=>'income_tax_desc','readonly'=>false,],
            ['serial'=>'','caption'=>'其他零星税收','class'=>'level3','name'=>'little_tax',
                'memo'=>'little_tax_desc','readonly'=>false,],
            ['serial'=>'(3)','caption'=>'保险费','class'=>'level2','name'=>'insurance',
                'memo'=>'insurance_desc','readonly'=>false,],
            ['serial'=>'','caption'=>'重置成本(元/平方米)','class'=>'level3','name'=>'building_cost',
                'memo'=>'building_cost_desc',],
            ['serial'=>'','caption'=>'保险费率(%)','class'=>'level3','name'=>'insurance_rate',
                'memo'=>'insurance_rate_desc','value'=>$values['insurance_rate']],
            ['serial'=>'(4)','caption'=>'管理费','class'=>'level2','name'=>'management_expense',
                'memo'=>'management_expense_desc','readonly'=>false,],
            ['serial'=>'','caption'=>'管理费率(%)','class'=>'level3','name'=>'management_rate',
                'memo'=>'management_rate_desc','value'=>$values['management_rate']],
            ['serial'=>'(5)','caption'=>'维修费','class'=>'level2','name'=>'maintenance_cost',
                'memo'=>'maintenance_cost_desc','readonly'=>false,],
            ['serial'=>'','caption'=>'维修费率(%)','class'=>'level3','name'=>'maintenance_rate',
                'memo'=>'maintenance_rate_desc','value'=>$values['maintenance_rate']],
            ['serial'=>'(三)','caption'=>'第1年净收益A','name'=>'annual_net_income',
                'memo'=>'*第1年净收益 = 年有效毛收入 - 年运营费用','readonly'=>false,],
            ['serial'=>'(四)','caption'=>'递增期t(年)','name'=>'increase_years',
                'memo'=>'increase_years_desc','value'=>$values['increase_years']],
            ['serial'=>'(五)','caption'=>'递增期内每年的上涨率g(%)','name'=>'increase_rate',
                'memo'=>'*','value'=>$values['increase_rate']],
            ['serial'=>'(六)','caption'=>'房地产报酬率Y(%)','name'=>'rate_of_return',
                'memo'=>'rate_of_return_desc','readonly'=>false,],
            ['serial'=>'(七)','caption'=>'收益期N(年)','name'=>'income_period',
                'memo'=>'income_period_desc','readonly'=>false,],
            ['serial'=>'(八)','caption'=>'收益期结束后剩余土地使用权价值','name'=>'remaining_land_value',
                'memo'=>'remaining_land_value_desc','readonly'=>false,],
/*             ['serial'=>'(九)','caption'=>'收益价格','name'=>'income_value',
                'memo'=>'*收益公式','readonly'=>false,], */
            ['serial'=>'(九)','caption'=>'收益价格','name'=>'income_value',
                'memo'=>'reason_formula','readonly'=>false,], 
//             reason_formula
        ];
        $this->assign([
            'fields'=>$fields,
            'parameter'=>$parameter,
            'reward'=>$reward,
        ]);
        return $this->fetch();
	}
	
	public function ajaxGetGjxmDetails(){
	    //输入报告编号，查出报告下的所有估价对象
	    $input = input();//
// 	    $input['No'] = "DY2018030076";
	    //$test = "DY2018030076";GjxmdetailKID,fwlp,yt
        $result = Db::connect('EasyPG')->query("Select fwlp,GjxmdetailKID,yt                
                            from PG_SE_Gjxmdetail this (nolock)
                                 left join PG_SE_Gjxmglk glk (nolock) on glk.kid=this.kid
                                 left join PG_SE_Gjxmbgk Gjxmbgk (nolock) on  Gjxmbgk.kid=this.kid
                            where bgcd=?",[$input['No']]);
        return $result;
	}
	
	public function ajaxGetGjxmDetailsDatas(){
	    //从估价对象的GjxmdetailKID，查取相应的具体数据
	    $input = input();
	    $result = Db::connect('EasyPG')->query("Select jzmj,tdksrq,tdjsrq,jcnf,gjdate,szqy,qsrlx,pgyt,IsTwoY,jzjg,zcs
                            from PG_SE_Gjxmdetail this (nolock)
                                 left join PG_SE_Gjxmglk glk (nolock) on glk.kid=this.kid
                            where GjxmdetailKID =?",[$input['uid']]);
	    $result1 = Db::connect('EasyPG')->query("select datavalue from v_data  where tablename like '%收益法%'
                        	    and fieldscaption like '%月租金%'
                        	    and GjxmdetailKID =?",[$input['uid']]);

        if(count($result)!=0){
    	    $result[0]['tdksrq'] = date_format(date_create($result[0]['tdksrq']),"Y/m/d");//土地开始日期
    	    $result[0]['tdjsrq'] = date_format(date_create($result[0]['tdjsrq']),"Y/m/d");
    	    $result[0]['gjdate'] = date_format(date_create($result[0]['gjdate']),"Y/m/d");//价值时点
    	    if($result[0]['jzjg']=='混合结构' or $result[0]['jzjg']=='砖混结构'){
    	        $result[0]['jzjg']='砖混';
    	    }elseif ($result[0]['jzjg']=='钢混结构') {
    	        $result[0]['jzjg']='钢混';
    	    }elseif ($result[0]['jzjg']=='钢结构') {
    	        $result[0]['jzjg']='钢';
    	    };
    	    if($result[0]['pgyt']=='工业' ){
    	        $result[0]['pgyt']='工业仓储';
    	    }elseif ($result[0]['pgyt']=='车位' ){
    	        $result[0]['pgyt']='车位车库';
    	    }elseif ($result[0]['pgyt']=='办公' or $result[0]['pgyt']=='住宅' 
    	        or $result[0]['pgyt']=='商业'){//什么也不做
    	    }else{
    	        $result[0]['pgyt']='其他用房';
    	    };
    	    if(count($result1)!=0){
        	    $result[0]['datavalue'] = $result1[0]['datavalue'];
    	    }
        }
        return $result;
	}
	
	public function ajaxSaveIncomeValueProcess(){
	    $input = input();
	    $save = new EasyPGLxIncomeFiniteModel();
	    $find = $save->where('gjxmdetailID',$input['gjxmdetailID'])->value('id');
// 	    dump($find);
// 	    halt($input);
	    if($find){
    	    $result = $save->allowField(true)->save($input,['gjxmdetailID' =>$input['gjxmdetailID']]);
	    }else{
    	    $result = $save->allowField(true)->save($input);
//     	    halt($result);
	    };
	    return $result;
	}
	
	public function ajaxGetLXIncomeFiniteData(){
	    $input = input();
	    //取出数据
	    $getselfdata = (new EasyPGLxIncomeFiniteModel())->where('gjxmdetailID',$input['uid'])->find();
        if($getselfdata){
            $getselfdata = $getselfdata->toArray();
            //正则：取数字和取日期
            $pattern_num = '/^(\d+)?\.\d{4}$/';
            $pattern_date = '/^(\d{4})-(\d{2})-(\d{2}) \d{2}:\d{2}:\d{2}.\d{3}$/';
    	    foreach ($getselfdata as $key=>$value ){
        	    //数字转为两位小数
    	        if( preg_match($pattern_num,$value,$match0)){
        	        $getselfdata[$key] = floatval($value);
        	    };
        	    //日期转化为“2018/03/03”
    	        if( preg_match($pattern_date,$value,$match)){
        	        $getselfdata[$key] = $match[1].'/'.$match[2].'/'.$match[3];
        	    }
    	    }
    	    return $getselfdata;
        }else{
            return 0;
        }
	}
}

