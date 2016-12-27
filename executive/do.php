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
case 'gd':
	echo executive_gd(TRUE);
	break;
case 'cancel':
	echo executive_gd(FALSE);
	break;
case 'check_customer':
	echo check_customer();
	break;
case 'getcategory':
	echo getcategory();
	break;
case 'close':
	echo executive_close(TRUE);
	break;
case 'open':
	echo executive_close(FALSE);
	break;
case 'getTaxRate':
	echo getTaxRate();
	break;
}

function executive_gd($isgd) {
	$executive = new Executive(Security_Util::my_post('id'));
	$executive->setGd($isgd);
	$result = $executive->gd_executive();
	//return $result['message'];
	return $result['status'] === 'success' ? 1 : $result['message'];
}

function check_customer() {
	$cid_all = explode('-', Security_Util::my_post('cid'));
	$dao = new Dao_Impl();
	$customer_id = $dao->db
			->get_var(
					'SELECT customer_id FROM v_cid_customer WHERE cid="'
							. strtoupper($cid_all[0]) . '"');
	if ($customer_id === NULL) {
		return '该客户暂时未购买保险额度，无法创建执行单，请联系财务部Alex';
	} else {
		$cus = new Customer(array('customer_id' => intval($customer_id)));
		$remainder = $cus->compute_remainder_safety();
		unset($cus);
		if ($remainder <= 0) {
			return '该客户保险额度已满，无法创建执行单，请联系财务部Alex';
		}
	}
	return 1;
}

function getcategory() {
	return Supplier::get_supplier_category(FALSE,
			Security_Util::my_post('supplier'));
}

function executive_close($isclose){
	$exe_close = new Executive_Close(Security_Util::my_post('id'));
	$result =  $exe_close->closeExecutive($isclose);
	unset($exe_close);
	return $result['status'] === 'success' ? 1 : $result ['message'];
}

function getTaxRate(){
	$dao  = new Dao_Impl();
	$billtype = $dao->db->get_var('SELECT billtype FROM contract_cus WHERE cid="' . Security_Util::my_post('cid') . '"');
	$dao->db->disconnect();
	switch(intval($billtype)){
		case 1:
			return GG_TAX_RATE;
		case 2:
			return FW_TAX_RATE;
		default:
			return 0;
	} 
}