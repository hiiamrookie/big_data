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
case 'rebate_invoice_apply_transfer':
	echo rebate_invoice_apply_transfer();
	break;
default:
	echo INVALIDATION_VISIT;
}

function rebate_invoice_apply_transfer() {
	$rebate = new Finance_Rebate(
			array('ttype' => Security_Util::my_post('type'),
					'id' => Security_Util::my_post('id')));
	$result = $rebate->getRebateInvoiceApplyTransferResult();
	if ($result['status'] === 'success') {
		return 1;
	}
	return $result['message'];
}

