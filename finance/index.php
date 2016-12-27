<?php
include(dirname(dirname(__FILE__)) . '/inc/my_session.php');
include(dirname(dirname(__FILE__)) . '/inc/model_require.php');
include(dirname(dirname(__FILE__)) . '/inc/require_file.php');
include(dirname(dirname(__FILE__)) . '/user_auth.php');
header('Content-type: text/html; charset=utf-8');

switch (Security_Util::my_get('o')) {
case 'report':
	report();
	break;
case 'tjall':
	tjall();
	break;
case 'nim_bankinfo':
	nim_bankinfo();
	break;
case 'nim_bankinfolist':
	nim_bankinfolist();
	break;
case 'custominfosearch':
	custominfosearch();
	break;
case 'pidinfosearch':
	pidinfosearch();
	break;
case 'hedge':
	hedge();
	break;
case 'hedge_list':
	hedge_list();
	break;
case 'hedge_confirm':
	hedge_confirm();
	break;
case 'settle_account':
	settle_account();
	break;
case 'meida_short':
	meida_short();
	break;
case 'meida_short_list':
	meida_short_list();
	break;
case 'meida_short_export':
	meida_short_export();
	break;
case 'meida_short_edit':
	meida_short_edit();
	break;
case 'setting_rebate':
	setting_rebate();
	break;
case 'rebate_list':
	rebate_list();
	break;
case 'rebate_rate_edit':
	rebate_rate_edit();
	break;
case 'rebate_export':
	rebate_rate_export();
	break;
default:
	User::no_permission();
}

function tjall() {
	$ftj = new Finance_Tj();
	echo $ftj->get_tj_all();
	unset($ftj);
}

function report() {
	$report = new Finance_Report();
	echo $report->get_report_html();
	unset($report);
}

function nim_bankinfo() {
	$nimbank = new Nim_BankInfo();
	echo $nimbank->get_add_nim_bankinfo_html();
	unset($nimbank);
}

function nim_bankinfolist() {
	$nimbank = new Nim_BankInfo(
			array(
					'page' => intval(Security_Util::my_get('page')) === 0 ? 1
							: intval(Security_Util::my_get('page'))));
	echo $nimbank->get_nim_bankinfo_list_html();
	unset($nimbank);
}

function custominfosearch() {
	$finance = new Finance_Info_Search();
	echo $finance->get_custom_info_search_html();
	unset($finance);
}

function pidinfosearch() {
	$finance = new Finance_Info_Search();
	echo $finance->get_pid_info_search_html();
	unset($finance);
}

function hedge() {
	$hedge = new Finance_Hedge();
	echo $hedge->get_finance_hedge_html();
	unset($hedge);
}

function hedge_list() {
	$hedge = new Finance_Hedge(
			array(
					'page' => intval(Security_Util::my_get('page')) === 0 ? 1
							: intval(Security_Util::my_get('page'))));
	echo $hedge->get_finance_hedge_list_html();
	unset($hedge);
}

function hedge_confirm() {
	$hedge = new Finance_Hedge(array('id' => Security_Util::my_get('id')));
	echo $hedge->get_finance_hedge_confirm_html();
	unset($hedge);
}

function settle_account() {
	$settle_account = new Finance_Settle_Account();
	echo $settle_account->getIndexHtml();
	unset($settle_account);
}

function meida_short() {
	$ms = new Supplier_Short();
	echo $ms->getIndexHtml();
	unset($ms);
}

function meida_short_list() {
	$ms = new Supplier_Short(
			array(
					'page' => intval(Security_Util::my_get('page')) === 0 ? 1
							: intval(Security_Util::my_get('page')),
					'search' => Security_Util::my_get('search')));
	echo $ms->getSupplierShortListHtml();
	unset($ms);
}

function meida_short_edit(){
	$ms = new Supplier_Short(array('id'=>Security_Util::my_get('id')));
	echo $ms->getSupplierShortUpdateHtml();
	unset($ms);
}

function meida_short_export(){
	$ms = new Supplier_Short();
	echo $ms->getExportHtml();
	unset($ms);
}

function setting_rebate(){
	$sr = new Setting_Rebate();
	echo $sr->getIndexHtml();
	unset($sr);
}

function rebate_list(){
	$sr = new Setting_Rebate(array(
					'page' => intval(Security_Util::my_get('page')) === 0 ? 1
							: intval(Security_Util::my_get('page'))));
	echo $sr->getRebateRateListHtml();
	unset($sr);
}

function rebate_rate_edit(){
	$sr = new Setting_Rebate(array('id'=>Security_Util::my_get('id')));
	echo $sr->getRebateRateUpdateHtml();
	unset($sr);
}

function rebate_rate_export(){
	$sr = new Setting_Rebate();
	echo $sr->getExportHtml();
	unset($sr);
}
