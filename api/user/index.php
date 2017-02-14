<?php
include (dirname ( dirname ( dirname ( __FILE__ ) ) ) . '/inc/my_session.php');
include (dirname ( dirname ( dirname ( __FILE__ ) ) ) . '/inc/model_require.php');
include (dirname ( dirname ( dirname ( __FILE__ ) ) ) . '/inc/require_file.php');
header ( 'Content-type: text/html; charset=utf-8' );

$o = Security_Util::my_get('o');
//?o=media_schedule 上传媒体排期表

$auth_name = Security_Util::my_post ( 'auth_name' );
$auth_code = Security_Util::my_post ( 'auth_code' );
$user_data = Security_Util::my_post ( 'user_data' );

/*
$auth_name = 'test2';
$auth_code = '6b8ae6c537b02247ec2840f9445bce15';


$a = array(
		array(
				'md5str'=>'dc7ec6d750ec265931329f879811992b',
				'data_date'=>'2017-01-10',
				'reg_cnt'=>'1234',
				'order_cnt'=>'15',
				'order_amount'=>'234.56'
		),
		array(
				'md5str'=>'d3dd0a407b40736ef9eded52dd3578e9',
				'data_date'=>'2017-01-10',
				'reg_cnt'=>'100',
				'order_cnt'=>'125',
				'order_amount'=>'3456.78'
		)
);
	
$user_data = json_encode($a,JSON_UNESCAPED_UNICODE);




$a = array(
		array(
				'dsp_platform'=>'baidu',
				'pid'=>'15SH010001-085',
				'dsp_order'=>'订单X',
				'dsp_adv'=>'广告X',
				'dsp_creative'=>'创意X',
				'dsp_website'=>'baidu.com',
				'dsp_industry_1'=>'一级行业X',
				'dsp_industry_2'=>'二级行业X',
				'schedule_date'=>'2017-01-12',
				'budget'=>array(
						'0'=>100,
						'11'=>200,
						'22'=>300
				),
		),
		array(
				'dsp_platform'=>'baidu',
				'pid'=>'15SH010001-085',
				'dsp_order'=>'订单Y',
				'dsp_adv'=>'广告Y',
				'dsp_creative'=>'创意Y',
				'dsp_website'=>'baidu.com',
				'dsp_industry_1'=>'一级行业Y',
				'dsp_industry_2'=>'二级行业Y',
				'schedule_date'=>'2017-01-12',
				'budget'=>array(
						'10'=>1000,
						'11'=>2000,
						'12'=>3000,
						'13'=>4000,
						'14'=>5000,
						'19'=>4000,
						'20'=>3000,
						'22'=>2000,
						'23'=>1000,
				),
		)
);

$user_data = json_encode($a,JSON_UNESCAPED_UNICODE);
*/


$api = new Api_User_Data ( $auth_name, $auth_code );
if($o === 'media_schedule'){
	echo $api->getPostMediaScheduleResult ( $user_data );
}else{
	echo $api->getPostOfflineDataResult ( $user_data );
}
unset ( $api );