<?php
include(dirname(dirname(__FILE__)) . '/inc/my_session.php');
include(dirname(dirname(__FILE__)) . '/inc/model_require.php');
include(dirname(dirname(__FILE__)) . '/inc/require_file.php');
include(dirname(dirname(__FILE__)) . '/user_auth.php');
header('Content-type: text/html; charset=utf-8');

switch (Security_Util::my_get('o')) {
case 'profit':
	profit();
	break;
case 'effect':
	effect();
	break;
case 'trend':
	trend();
	break;
default:
	User::no_permission();
}

//项目盈利分析
function profit() {
	$executive_list = new Analyze_Data(
			array('search' => Security_Util::my_get('search')));
	echo $executive_list->get_profit_html();
	unset($executive_list);
}

//投放效果分析
function effect() {
	$executive_list = new Analyze_Data(
			array('search' => Security_Util::my_get('search'),
				  'endtime' => Security_Util::my_get('endtime'),
				  'starttime' => Security_Util::my_get('starttime'),
				  'range' => Security_Util::my_get('range')
				  )
			);
	echo $executive_list->get_effect_html();
	unset($executive_list);
}


//投放趋势分析
function trend() {
	$executive_list = new Analyze_Data(
			array('search' => Security_Util::my_get('search')));
	echo $executive_list->get_report_data_html();
	unset($executive_list);
}
