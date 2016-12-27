<?php
include (dirname ( dirname ( __FILE__ ) ) . '/inc/my_session.php');
include (dirname ( dirname ( __FILE__ ) ) . '/inc/model_require.php');
include (dirname ( dirname ( __FILE__ ) ) . '/inc/require_file.php');
include (dirname ( dirname ( __FILE__ ) ) . '/user_auth.php');
header ( 'Content-type: text/html; charset=utf-8' );

//校验vcode
$vcode = $uid . User::SALT_VALUE;
$is_ajax = TRUE;
include (dirname ( dirname ( __FILE__ ) ) . '/validate_vcode.php');

switch (strval ( Security_Util::my_post ( 'action' ) )) {
	case 'cancel_contract' :
		echo cancel_contract ();
		break;
	case 'select_customer':
		echo select_customer();
		break;
}

function cancel_contract(){
	$contract = new Contract(array('cid'=>Security_Util::my_post('cid')));
	if($contract->getHas_cancel_contract_permission()){
		$result = $contract->cancel_contract();
		return $result ['message'];
	}else{
		return NO_RIGHT_TO_DO_THIS;
	}
}

function select_customer(){
	$customer = new Customer();
	return $customer->get_cusname_belong(Security_Util::my_post('cusname'));
}