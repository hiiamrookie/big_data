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
case 'search_cusname':
	echo search_cusname();
	break;
case 'search_deposit':
	echo search_deposit();
	break;
case 'search_deposit_for_receivables':
	echo search_deposit_for_receivables();
	break;
case 'cancel_deposit_receivable':
	echo cancel_deposit_receivable();
	break;
default:
	echo INVALIDATION_VISIT;
}

function search_cusname() {
	$contracts = Contract::getInstance();
	return $contracts[Security_Util::my_post('cid')];
}

function search_deposit() {
	$deposit_ajax = new Deposit_Ajax(
			array('billtype' => Security_Util::my_post('billtype'),
					'q' => Security_Util::my_post('search')));
	return $deposit_ajax->search_deposit();
}

function search_deposit_for_receivables(){
	$deposit = new Deposit_Receivables(
			array('type' => Security_Util::my_post('type'),
					'page' => intval(Security_Util::my_post('page')) === 0 ? 1
							: intval(Security_Util::my_post('page')),
					'search' => Security_Util::my_post('search')));
	return $deposit->get_search_deposit_html();
}

function cancel_deposit_receivable(){
	$deposit = new Deposit_Receivables(
			array('id' => Security_Util::my_post('id')));
	return $deposit->cancel_deposit_receivable();
}