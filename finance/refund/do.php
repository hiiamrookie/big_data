<?php
include(dirname(dirname(dirname(__FILE__))) . '/inc/my_session.php');
include(dirname(dirname(dirname(__FILE__))) . '/inc/model_require.php');
include(dirname(dirname(dirname(__FILE__))) . '/inc/require_file.php');
include(dirname(dirname(dirname(__FILE__))) . '/user_auth.php');
header('Content-type: text/html; charset=utf-8');

//校验vcode
$vcode = $uid . User::SALT_VALUE;
$is_ajax = TRUE;
include(dirname(dirname(dirname(__FILE__))) . '/validate_vcode.php');

switch (strval(Security_Util::my_post('action'))) {
case 'search_customer_bank':
	echo search_customer_bank();
	break;
case 'search_customer_bank_account':
	echo search_customer_bank_account();
	break;
case 'search_receivable':
	echo search_receivable();
	break;
case 'audit_refund':
	echo audit_refund();
	break;
case 'search_payment':
	echo search_payment();
	break;
case 'cancel_media_refund':
	echo cancel_media_refund();
	break;
default:
	echo INVALIDATION_VISIT;
}

function search_customer_bank() {
	return Customer_Bankinfo::get_bank_list(
			Security_Util::my_post('customer_name'));
}

function search_customer_bank_account() {
	return Customer_Bankinfo::get_bank_acount_list(
			Security_Util::my_post('customer_name'),
			Security_Util::my_post('bank_name'));
}

function search_receivable() {
	$refund = new Finance_Refund();
	return $refund->search_receivable();
}

function audit_refund() {
	$pid = Security_Util::my_post('pid');
	$applyid = Security_Util::my_post('applyid');
	$itemaudit = Security_Util::my_post('itemaudit');
	$remark = Security_Util::my_post('remark');
	//return $pid . '===' . $applyid . '===' . $itemaudit . '===' . $remark;
	$refund = new Finance_Refund(
			array('pid' => Security_Util::my_post('pid'),
					'id' => Security_Util::my_post('applyid'),
					'itemaudit' => Security_Util::my_post('itemaudit'),
					'remark' => Security_Util::my_post('remark')));
	$result = $refund->audit_refund_item();
	return $result['message'];
}

function search_payment() {
	$refund = new Finance_Refund_Media();
	return $refund->search_payment();
}

function cancel_media_refund() {
	$refund = new Finance_Refund_Media(
			array('id' => Security_Util::my_post('id')));
	$result = $refund->cancel_media_refund();
	return $result['status'] === 'success' ? 1 : $result['message'];
}
