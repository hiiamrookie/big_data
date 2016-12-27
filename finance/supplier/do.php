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
case 'supplier_cancel':
	echo supplier_cancel();
	break;
case 'supplier_industry_cancel':
	echo supplier_industry_cancel();
	break;
case 'supplier_category_cancel':
	echo supplier_category_cancel();
	break;
default:
	echo INVALIDATION_VISIT;
}

function supplier_cancel() {
	$supplier = new Supplier(
			array('id' => Security_Util::my_post('id'),
					'recover' => Security_Util::my_post('recover')));
	$result = $supplier->cancel();
	return $result['message'];
}

function supplier_industry_cancel() {
	$supplier = new Supplier(
			array('industry_id' => Security_Util::my_post('id'),
					'recover' => Security_Util::my_post('recover')));
	$result = $supplier->industry_cancel();
	return $result['message'];
}

function supplier_category_cancel() {
	$supplier = new Supplier(
			array('category_id' => Security_Util::my_post('id'),
					'recover' => Security_Util::my_post('recover')));
	$result = $supplier->category_cancel();
	return $result['message'];
}
