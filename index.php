<?php
include (dirname ( __FILE__ ) . '/inc/my_session.php');
include (dirname ( __FILE__ ) . '/inc/model_require.php');
include (dirname ( __FILE__ ) . '/inc/require_file.php');
include (dirname ( __FILE__ ) . '/user_auth.php');
header ( 'Content-type: text/html; charset=utf-8' );

if (Security_Util::my_get ( 'exit' ) === '1') {
	session_unset ();
	session_destroy ();
	Server_Util::my_server_redirect ( BASE_URL );
} else {
	index ();
}
function index() {
	$user_index = new User_Index ();
	if ($user_index->getUid () !== NULL) {
		$buf = file_get_contents ( TEMPLATE_PATH . 'index.tpl' );
		echo str_replace ( array (
				'[LEFT]',
				'[TOP]',
				'[EXECUTIVELIST]',
				'[CONTRACTLIST]',
				'[COUNT_EXECUTIVE]',
				'[COUNT_CONTRACT_CUS]',
				'[ALLCOUNT]',
				'[INVOICETAB]',
				'[INVOICELIST]',
				'[DEPOSITTAB]',
				'[DEPOSITLIST]',
				'[SUPPLIERAPPYAUDITTAB]',
				'[SUPPLIERAPPYAUDITLIST]',
				'[CONTRACTPAYMENTPERSONTAB]',
				'[CONTRACTPAYMENTPERSONLIST]',
				'[PAYMENTMESSAGE]',
				'[OUTSOURCINGAUDITTAB]',
				'[OUTSOURCINGAUDITLIST]',
				'[PROJECTTAB]',
				'[PROJECTAUDITLIST]',
				'[BASE_URL]' 
		), array (
				$user_index->get_left_html (),
				$user_index->get_top_html (),
				$user_index->get_executives_list_html (),
				$user_index->get_contract_list_html (),
				$user_index->getExecutive_count (),
				$user_index->getContract_count (),
				$user_index->getExecutive_count () + $user_index->getContract_count (),
				$user_index->get_invoice_tab (),
				$user_index->get_invoice_list (),
				$user_index->get_deposit_tab (),
				$user_index->get_deposit_list (),
				$user_index->get_supplier_apply_audit_tab (),
				$user_index->get_supplier_apply_list (),
				$user_index->get_contract_payment_tab (),
				$user_index->get_contract_payment_list (),
				$user_index->getPaymentMessage (),
				$user_index->getOutsourcingAuditTab (),
				$user_index->getOutsourcingAuditList (),
				$user_index->getProjectAuditTab (),
				$user_index->getProjectAuditList (),
				BASE_URL 
		), $buf );
	} else {
		session_unset ();
		session_destroy ();
		Js_Util::my_js_alert ( '请重新登录', BASE_URL );
	}
}
