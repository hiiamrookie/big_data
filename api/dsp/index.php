<?php
include (dirname ( dirname ( dirname ( __FILE__ ) ) ) . '/inc/my_session.php');
include (dirname ( dirname ( dirname ( __FILE__ ) ) ) . '/inc/model_require.php');
include (dirname ( dirname ( dirname ( __FILE__ ) ) ) . '/inc/require_file.php');
header ( 'Content-type: text/html; charset=utf-8' );

$auth_name = Security_Util::my_post ( 'auth_name' );
$auth_code = Security_Util::my_post ( 'auth_code' );
$dsp_data = Security_Util::my_post ( 'dsp_data' );


$auth_name = 'test3';
$auth_code = 'dc051845019258a2e9b9187661313405';

$a = array (
		'dsp_platform' => 'tencent',
		'data' => array (
				array (
						'dsp_order' => '订单1',
						'dsp_adv' => '广告1',
						'dsp_creative' => '创意1',
						'dsp_website' => 'baidu.com',
						'dsp_industry_1' => '一级行业1',
						'dsp_industry_2' => '二级行业1',
						'schedule_date' => '2017-01-10',
						'times_data' => array (
								'0' => array (
										'dsp_cost' => 100,
										'dsp_impressions' => 1000,
										'dsp_click' => 10
								),
								'15' => array (
										'dsp_cost' => 200,
										'dsp_impressions' => 2000,
										'dsp_click' => 20
								)
						)
				),
				array (
						'dsp_order' => '订单1',
						'dsp_adv' => '广告1',
						'dsp_creative' => '创意1',
						'dsp_website' => 'sina.com',
						'dsp_industry_1' => '一级行业1',
						'dsp_industry_2' => '二级行业1',
						'schedule_date' => '2017-01-10',
						'times_data' => array (
								'6' => array (
										'dsp_cost' => 100,
										'dsp_impressions' => 99,
										'dsp_click' => 80
								),
								'9' => array (
										'dsp_cost' => 123,
										'dsp_impressions' => 456,
										'dsp_click' => 345
								),
								'20' => array (
										'dsp_cost' => 1111,
										'dsp_impressions' => 3333,
										'dsp_click' => 2222
								)
						)
				),
				array (
						'dsp_order' => '订单1',
						'dsp_adv' => '广告1',
						'dsp_creative' => '创意3',
						'dsp_website' => 'baidu.com',
						'dsp_industry_1' => '一级行业1',
						'dsp_industry_2' => '二级行业1',
						'schedule_date' => '2017-01-10',
						'times_data' => array (
								'11' => array (
										'dsp_cost' => 800,
										'dsp_impressions' => 899,
										'dsp_click' => 880
								),
								'12' => array (
										'dsp_cost' => 723,
										'dsp_impressions' => 756,
										'dsp_click' => 745
								),
								'14' => array (
										'dsp_cost' => 3000,
										'dsp_impressions' => 60000,
										'dsp_click' => 4000
								)
						)
				)
		)
);
	
$dsp_data = json_encode ( $a, JSON_UNESCAPED_UNICODE );


$api = new Api_Dsp_Data ( $auth_name, $auth_code );
echo $api->getPostDspDataResult ( $dsp_data );
unset ( $api );