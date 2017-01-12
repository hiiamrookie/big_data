<?php
include (dirname ( dirname ( dirname ( __FILE__ ) ) ) . '/inc/my_session.php');
include (dirname ( dirname ( dirname ( __FILE__ ) ) ) . '/inc/model_require.php');
include (dirname ( dirname ( dirname ( __FILE__ ) ) ) . '/inc/require_file.php');
header ( 'Content-type: text/html; charset=utf-8' );

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
*/




$api = new Api_User_Data ( $auth_name, $auth_code );
echo $api->getPostOfflineDataResult ( $user_data );
unset ( $api );