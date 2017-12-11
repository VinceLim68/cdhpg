<?php

namespace app\phone\model;

use think\Model;

class TCaseCfgModel extends Model {
	protected $pk = 'Id';
	protected $table = 't_case_cfg';
	// 设置当前模型的数据库连接
	protected $connection = 'db_apprsal_cdh';
	protected $resultSetType = 'collection'; // 这个设置可以很快把返回数据集转成array
	protected $field = true; // 忽略非数据表字段而不报错
// 	protected $autoWriteTimestamp = true;		//自动转化时间戳
	protected $updateTime = false;
	
	public function getCaseByNameAndDate(){
	    
	    $records = $this->field('Case_Name,Case_Located,Case_Type,Case_TrxPrice,Case_Cmpl_Years,Case_TrxDate,Opertor')
    	    ->where('Case_Name','like','%'.session('user.comm').'%')
    	    ->order('Case_TrxDate desc')
    	    ->where('Case_TrxDate','> time',date('Y-m-d',strtotime('-'.config('historyDays').' day')))
    	    ->select()->toArray();
// 	    halt($records);
	    $html = '';
	    foreach ($records as $rec){
	        $html .= '<tr><td class="font-small">'.$rec['Case_Name'].'-'.$rec['Case_Type'].':';
	        $html .= '------'.$rec['Case_Located'].'(建成：'.date ( 'Y', strtotime ( $rec['Case_Cmpl_Years']) ).'年)';
	        $html .= '</br>成交价:'.round($rec['Case_TrxPrice']).'(成交日期:'.date ( 'Y-m-d', strtotime ( $rec['Case_TrxDate']) ).')------'.$rec['Opertor'].'</td></tr>';
	    }
	    //         halt($html);
	    return $html;
	}
	
}
