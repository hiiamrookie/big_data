<?php
include(dirname(dirname(__FILE__)) . '/inc/my_session.php');
include(dirname(dirname(__FILE__)) . '/inc/model_require.php');
include(dirname(dirname(__FILE__)) . '/inc/require_file.php');
include(dirname(dirname(__FILE__)) . '/user_auth.php');
header('Content-type: text/html; charset=utf-8');

//校验vcode
$vcode = $uid . User::SALT_VALUE;
$is_ajax = TRUE;
include(dirname(dirname(__FILE__)) . '/validate_vcode.php');

switch (strval(Security_Util::my_post('action'))) {
case 'cancelHedge':
	echo cancelHedge();
	break;
case 'get_category_by_supplierid':
	echo get_category_by_supplierid();
	break;
case 'get_industry_by_suppliershortid':
	echo get_industry_by_suppliershortid();
	break;
default:
	echo INVALIDATION_VISIT;
}

function cancelHedge() {
	$hedge = new Finance_Hedge(array('id' => Security_Util::my_post('id')));
	$result = $hedge->cancel_finance_hedge();
	if ($result['status'] === 'success') {
		return 1;
	} else {
		return $result['message'];
	}
}

function get_category_by_supplierid() {
	return Supplier::getCategoryBySupplierID(Security_Util::my_post('sid'));
}

function get_industry_by_suppliershortid() {
	return Supplier_Short::getIndustryBySupplierShortID(
			Security_Util::my_post('sid'));
}
