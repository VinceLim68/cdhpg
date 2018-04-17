<?php

namespace app\evalu\model;

use think\Model;
use think\Db;

class CommaddressModel extends Model {
	/**
	 * 操作表：小区-地址信息
	 */
	protected $pk = 'id';
	protected $table = 'commaddress';
	protected $resultSetType = 'collection'; // 这个设置可以很快把返回数据集转成array
	protected $field = true; // 忽略非数据表字段而不报错
	
	public function getBuildyearAttr($value)
	{
	    return date( 'Y', strtotime($value) );
	}
	
	public function setBuildyearAttr($value)
	{
	    return date('Y-m-d',$value);
	}
	
	public function getListByFormdata($data){
	    //通过页面输入的参数来按页查询数据
// 	    if('' !== $data['set']){
// 	        //修改记录
// 	        $sqlstr = 'UPDATE commaddress SET '.$data['set'].' WHERE '.$data['where'];
// 	        $data['num'] = Db::execute($sqlstr);
// 	    }
	    //查询记录,无论是否修改，都需要查询

	    $sales = $this->field('
                id,
                comm_id,
                comm_name,
                city,
                region,
                road,
                doorplate,
                type,
                buildYear,
                floors,
                elevator,
                structure
	           ')
        	    ->where($data['where'] )
        	    ->order($data['neworder'])
        	    ->paginate(100,false,[
        	        'query'=>[
        	            'where'=>  $data['where'],
        	            'order'=>  $data['order'],
        	            'set'=>  $data['set'],
//         	            'community_id' =>  isset($data['community_id']) ? $data['community_id'] : '',
        	        ],
        	    ]);
// 	    $sales['num'] = $data['num'];
	    return $sales;
	}
	
	public function findById($id){
	    //通过id查记录
	    return $this->where('id',$id)->find()->toArray();
	}
}