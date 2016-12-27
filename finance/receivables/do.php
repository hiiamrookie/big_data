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
case 'cancel_receivable':
	echo cancel_receivable();
	break;
case 'search_executive':
	echo search_executive();
	break;
default:
	echo INVALIDATION_VISIT;
}

function cancel_receivable() {
	$finance = new Finance_Receivables(
			array('id' => Security_Util::my_post('id')));
	return $finance->cancel_receivable();
}

function search_executive() {
	$finance = new Finance_Receivables(
			array('type' => Security_Util::my_post('type'),
					'page' => intval(Security_Util::my_post('page')) === 0 ? 1
							: intval(Security_Util::my_post('page')),
					'search' => Security_Util::my_post('search')));
	return $finance->get_search_executive_html();
}