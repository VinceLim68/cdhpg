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
	
    public function getEnqueryByCommAndDate(){
        $records = $this->field('Enquiry_CellName,Enquiry_Date,Apprsal_Use,OfferPeople,Apprsal_Up,Remark,Enquiry_PmName')
                ->order('Enquiry_Date desc')
                ->where('Enquiry_Date','>= time',date('Y-m-d',strtotime('-'.config('historyDays').' day')))
                ->where('Enquiry_CellName','like','%'.session('user.comm').'%')
                ->select()->toArray();
        $html = '';
        foreach ($records as $rec){
            $html .= '<tr><td class="font-small">'.$rec['Enquiry_PmName'].'('.date ( 'Y-m-d', strtotime ( $rec['Enquiry_Date']) ).')';
            $html .= '------'.$rec['Enquiry_CellName'].'-'.$rec['Apprsal_Use'];
            $html .= '</br>'.$rec['OfferPeople'].'------'.$rec['Apprsal_Up'].',备注:'.$rec['Remark'].'</td></tr>';
        }
        return $html;
    }
    
    public function findEnqueryByCommAndDate($data){
        $find = $this->where('Enquiry_CellName',$data['Enquiry_CellName'])
                        ->order('Enquiry_Date desc')
                        ->where('Enquiry_Date','>= time',date ( "Y-m-d", strtotime ( "-".config('no_enquery_again')." day" ) ))
                        ->where('Apprsal_Use',$data['Apprsal_Use'])
                        ->where('Apprsal_Up',$data['Apprsal_Up'])
                        ->find();
        return $find;
    }
    
    public function findEnqueryByOfferAndDateAndComm($data){
        if(session('user.user_name') == 'admin'){
            $data['OfferPeople'] = '林晓';
        }
        return $this->where('OfferPeople',$data['OfferPeople'])
                    ->order('Enquiry_Date desc')
                    ->where('Enquiry_Date','>= time',date ( "Y-m-d", strtotime ( "-".config('no_enquery_again')." day" )))
                    ->where('Enquiry_CellName',$data['Enquiry_CellName'])
                    ->where('Apprsal_Use',$data['Apprsal_Use'])
                    ->find();
    }
    
    public function getCount($data){
        $day = date('Y-m-01', strtotime(date("Y-m-d")));
        if(session('user.user_name') == 'admin'){
            $data['OfferPeople'] = '林晓';
        }
        $res = $this->order('Enquiry_Date desc')
                ->where('Enquiry_Date','>= time',$day)
                ->where('OfferPeople',$data['OfferPeople'])
                ->count();
//         $sql_count = "SELECT COUNT(Enquiry_CellName) FROM t_enquiry WHERE Enquiry_Date >= '$day' AND OfferPeople = '林晓' ";
//         $res_count = $this->mysqli->query($sql_count);
//         $res = $res_count->fetch_all();
//         return $res[0][0];
        return $res;
    }
	
}
