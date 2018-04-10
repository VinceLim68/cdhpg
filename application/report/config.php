<?php
return [
    //这是用于报告系统的默认值
    'default_values'     =>  [
        'vacancy_rate'              =>      5,      //空置率%
        'a_year_interest_rate'      =>      1.5,     //一年期存款利率%
        'risk_compensation_rate'    =>      1.5,    //投资风险补偿率%
        'management_compensation_rate'    =>      1.25,    //管理负担补偿率%
        'lack_liquidity_compensastion_rate'    =>      2,    //缺乏流动性补偿率%
        'easy_access_to_financing_rate'    =>      -1,    //易于获得融资的优惠率%
        'income_tax_deduction_rate'    =>      -0.75,    //所得税抵扣的优惠率%
        'insurance_rate'            =>      0.2,    //保险费率%
        'maintenance_rate'          =>      1,      //维修费率%
        'management_rate'           =>      2,      //管理费率%
        'VAT_start_value'           =>      30000,  //增值税起征点
        'increase_years'           =>      5,       //递增年数
        'increase_rate'           =>      3.5,       //递增百分比
        'base_land_price_increase'           =>      1,       //基准地价上涨系数
    ],
    'default_options'   =>  [
        'property_type'         =>      [
            '住宅','商业','办公','工业仓储','车位车库','其他用房',           
        ],                                          //物业类型
        'authority_type'        =>      [
            '个人','小规模纳税人','一般纳税人'
        ],                                          //权属人类型
        'location_type'         =>      [
            '市区','县镇','其他'                         //区域类型
        ],
        'building_struction'    =>      [
            '钢混','砖混','钢'                           //建筑结构类型
        ],
        
    ]
];