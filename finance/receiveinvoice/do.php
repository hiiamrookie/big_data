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
case 'search_executive':
	echo search_executive();
	break;
case 'search_payment_apply':
	echo search_payment_apply();
	break;
default:
	echo INVALIDATION_VISIT;
}

function search_executive() {
	$invoice = new Finance_Receive_Invoice(
			array('type' => Security_Util::my_post('type'),
					'page' => intval(Security_Util::my_post('page')) === 0 ? 1
							: intval(Security_Util::my_post('page')),
					'search' => Security_Util::my_post('search'),
					'cusname' => Security_Util::my_post('cusname'),
					'medianame' => Security_Util::my_post('medianame')));
	return $invoice->get_search_executive_html();
}

function search_payment_apply() {
	$invoice = new Finance_Receive_Invoice(
			array(
					'page' => intval(Security_Util::my_post('page')) === 0 ? 1
							: intval(Security_Util::my_post('page')),
					'medianame' => Security_Util::my_post('search_medianame'),
					'paymentdate' => Security_Util::my_post(
							'search_paymentdate'),
					'payment_plan' => Security_Util::my_post(
							'search_payment_plan'),
					'payment_real' => Security_Util::my_post(
							'search_payment_real')));
	return $invoice->get_search_payment_apply_html();
}

