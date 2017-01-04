<?php
include(dirname(dirname(__FILE__)) . '/inc/my_session.php');
include(dirname(dirname(__FILE__)) . '/inc/model_require.php');
include(dirname(dirname(__FILE__)) . '/inc/require_file.php');
include(dirname(dirname(__FILE__)) . '/user_auth.php');
header('Content-type: text/html; charset=utf-8');

switch (Security_Util::my_get('o')) {
case 'data':
	data();
	break;
case 'rebate':
	rebate();
	break;
case 'profit':
	profit();
	break;
case 'import':
	import();
	break;
case 'export':
	export();
	break;
default:
	User::no_permission();
}

//执行成本明细
function data() {
	$executive_list = new Report_Data(
			array('search' => Security_Util::my_get('search')));
	echo $executive_list->get_report_data_html();
	unset($executive_list);
}

//应得返点
function rebate() {
	$executive_list = new Report_Data(
			array('search' => Security_Util::my_get('search')));
	echo $executive_list->get_deserve_rebate_html();
	unset($executive_list);
}

//利润
function profit() {
	$executive_list = new Report_Data(
			array('search' => Security_Util::my_get('search')));
	echo $executive_list->get_profit_append_html();
	unset($executive_list);
}

//数据导入
function import(){
	$data_ie = new Data_Import_Export();
	echo $data_ie->get_data_import_html();
	unset($data_ie);
}

function export(){
	$data_ie = new Data_Import_Export();
	echo $data_ie->get_data_export_html();
	unset($data_ie);
}

