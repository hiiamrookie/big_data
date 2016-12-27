<?php
include(dirname(dirname(dirname(__FILE__))) . '/inc/my_session.php');
include(dirname(dirname(dirname(__FILE__))) . '/inc/model_require.php');
include(dirname(dirname(dirname(__FILE__))) . '/inc/require_file.php');
include(dirname(dirname(dirname(__FILE__))) . '/user_auth.php');
header('Content-type: text/html; charset=utf-8');

switch (Security_Util::my_get('o')) {
case 'manager':
	rebate_invoice();
	break;
case 'query':
	rebate_query();
	break;
case 'receive2pay':
	rebate_receive2pay();
	break;
case 'pay2receive':
	rebate_pay2receive();
	break;
case 'apply_invoice':
	rebate_apply_invoice();
	break;
case 'apply_mylist':
	rebate_invoice_my_apply();
	break;
case 'apply_manager':
	rebate_invoice_apply_manager();
	break;
case 'invoice_nocollection':
	rebate_invoice_nocollection();
	break;
case 'rebate_transfer_list':
	rebate_transfer_list();
	break;
case 'view_apply_invoice':
	view_apply_invoice();
	break;
case 'audit_apply_invoice':
	audit_apply_invoice();
	break;
case 'gd_apply_invoice':
	gd_apply_invoice();
	break;
case 'edit_apply_invoice':
	edit_apply_invoice();
	break;
default:
	User::no_permission();
}

//返点开票页面
function rebate_invoice() {
	$rebate_invoice = new Finance_Rebate();
	echo $rebate_invoice->getRebateInvoiceHtml();
	unset($rebate_invoice);
}

//返点查询
function rebate_query() {
	$rebate_invoice = new Finance_Rebate();
	echo $rebate_invoice->getRebateQueryHtml();
	unset($rebate_invoice);
}

//应收返点转应付返点
function rebate_receive2pay() {
	$rebate_invoice = new Finance_Rebate(
			array('id' => Security_Util::my_get('id')));
	echo $rebate_invoice->getRebateReceive2PayHtml();
	unset($rebate_invoice);
}

//应付返点转应收返点
function rebate_pay2receive() {
	$rebate_invoice = new Finance_Rebate(
			array('id' => Security_Util::my_get('id')));
	echo $rebate_invoice->getRebatePay2ReceiveHtml();
	unset($rebate_invoice);
}

//普通用户申请返点开票
function rebate_apply_invoice() {
	$rebate_invoice = new Finance_Rebate();
	echo $rebate_invoice->getRebateApplyInvoiceHtml();
	unset($rebate_invoice);
}

//普通用户“我的返点开票申请列表”
function rebate_invoice_my_apply() {
	$rebate_invoice = new Finance_Rebate(
			array(
					'page' => intval(Security_Util::my_get('page')) === 0 ? 1
							: intval(Security_Util::my_get('page'))));
	echo $rebate_invoice->getRebateInvoiceMyListHtml();
	unset($rebate_invoice);
}

//财务部审核列表
function rebate_invoice_apply_manager() {
	$rebate_invoice = new Finance_Rebate(
			array(
					'page' => intval(Security_Util::my_get('page')) === 0 ? 1
							: intval(Security_Util::my_get('page'))));
	echo $rebate_invoice->getRebateInvoiceListHtml();
	unset($rebate_invoice);
}

//返点已开票未回款查询
function rebate_invoice_nocollection() {
	$rebate_invoice = new Finance_Rebate();
	echo $rebate_invoice->getRebateInvoiceNoCollectionHtml();
	unset($rebate_invoice);
}

//返点申请转移列表
function rebate_transfer_list() {
	$rebate_invoice = new Finance_Rebate(
			array(
					'page' => intval(Security_Util::my_get('page')) === 0 ? 1
							: intval(Security_Util::my_get('page'))));
	echo $rebate_invoice->getRebateTransferListHtml();
	unset($rebate_invoice);
}

//查看申请
function view_apply_invoice() {
	$rebate_invoice = new Finance_Rebate(
			array('id' => Security_Util::my_get('id')));
	echo $rebate_invoice->getRebateInvoiceByIDHtml();
	unset($rebate_invoice);
}

//审核申请
function audit_apply_invoice() {
	$rebate_invoice = new Finance_Rebate(
			array('id' => Security_Util::my_get('id')));
	echo $rebate_invoice->getRebateInvoiceAuditHtmlByID();
	unset($rebate_invoice);
}

//归档申请
function gd_apply_invoice() {
	$rebate_invoice = new Finance_Rebate(
			array('id' => Security_Util::my_get('id')));
	echo $rebate_invoice->getRebateInvoiceGDHtmlByID();
	unset($rebate_invoice);
}

//修改申请
function edit_apply_invoice() {
	$rebate_invoice = new Finance_Rebate(
			array('id' => Security_Util::my_get('id')));
	echo $rebate_invoice->getRebateInvoiceEditHtmlByID();
	unset($rebate_invoice);
}
