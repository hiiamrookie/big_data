<?php
include (dirname ( dirname ( dirname ( __FILE__ ) ) ) . '/inc/my_session.php');
include (dirname ( dirname ( dirname ( __FILE__ ) ) ) . '/inc/model_require.php');
include (dirname ( dirname ( dirname ( __FILE__ ) ) ) . '/inc/require_file.php');
include (dirname ( dirname ( dirname ( __FILE__ ) ) ) . '/user_auth.php');
header ( 'Content-type: text/html; charset=utf-8' );

//校验vcode
$vcode = $uid . User::SALT_VALUE;
$is_ajax = TRUE;
include (dirname ( dirname ( dirname ( __FILE__ ) ) ) . '/validate_vcode.php');

switch (strval ( Security_Util::my_post ( 'action' ) )) {
	case 'search_invoice_executive' :
		echo search_invoice_executive ();
		break;
	case 'simpleReject':
		echo simpleReject();
		break;
	default :
		echo INVALIDATION_VISIT;
}

function search_invoice_executive() {
	$invoice_ajax = new Invoice_Ajax ( Security_Util::my_post ( 'billtype' ), Security_Util::my_post ( 'search' ) );
	return $invoice_ajax->search_invoice_executive ();
}

function simpleReject(){
	$invoice = new Invoice(NULL, array('id'=>Security_Util::my_post('id'),'audit_remark'=>'财务部驳回'));
	$result = $invoice->invoice_reject();
	if($result['status'] === 'success'){
		return 1;
	}
	return $result['message'] ;
}