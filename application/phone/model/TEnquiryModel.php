<?php

namespace app\phone\model;

use think\Model;

class TEnquiryModel extends Model {
	protected $pk = 'id';
	protected $table = 't_enquiry';
	// 设置当前模型的数据库连接
	protected $connection = 'db_apprsal_cdh';
	protected $resultSetType = 'collection'; // 这个设置可以很快把返回数据集转成array
	protected $field = true; // 忽略非数据表字段而不报错
// 	protected $autoWriteTimestamp = true;		//自动转化时间戳
	protected $updateTime = false;
	
    public function getEnqueryByNameAndDate(){
        $records = $this->field('Enquiry_CellName,Enquiry_Date,Apprsal_Use,OfferPeople,Apprsal_Up,Remark,Enquiry_PmName')
                ->where('Enquiry_CellName','like','%'.session('user.comm').'%')
                ->order('Enquiry_Date desc')
                ->where('Enquiry_Date','> time',date('Y-m-d',strtotime('-'.config('historyDays').' day')))
                ->select()->toArray();
        $html = '';
        foreach ($records as $rec){
            $html .= '<tr><td class="font-small">'.$rec['Enquiry_PmName'].'('.date ( 'Y-m-d', strtotime ( $rec['Enquiry_Date']) ).')';
            $html .= '------'.$rec['Enquiry_CellName'].'-'.$rec['Apprsal_Use'];
            $html .= '</br>'.$rec['OfferPeople'].'------'.$rec['Apprsal_Up'].',备注:'.$rec['Remark'].'</td></tr>';
        }
//         halt($html);
        return $html;
    }
	
}
