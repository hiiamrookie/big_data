<?php
include(dirname(dirname(dirname(__FILE__))) . '/inc/my_session.php');
include(dirname(dirname(dirname(__FILE__))) . '/inc/model_require.php');
include(dirname(dirname(dirname(__FILE__))) . '/inc/require_file.php');
include(dirname(dirname(dirname(__FILE__))) . '/user_auth.php');
header('Content-type: text/html; charset=utf-8');

switch (Security_Util::my_get('o')) {
case 'apply':
	deposit_apply();
	break;
case 'my_deposit_list':
	deposit_list();
	break;
case 'deposit_invoice_apply':
	deposit_invoice_apply();
	break;
case 'my_deposit_invoice_list':
	deposit_invoice_list();
	break;
case 'leader_audit':
	leader_audit();
	break;
case 'edit':
	deposit_edit();
	break;
case 'getdeposit':
	getdeposit();
	break;
case 'deposit_invoice_view':
	deposit_invoice_view();
	break;
case 'deposit_invoice_edit':
	deposit_invoice_edit();
	break;
case 'deposit_invoicelist':
	deposit_invoicelist();
	break;
case 'audit':
	deposit_invoice_audit();
	break;
case 'print':
	deposit_invoice_print();
	break;
case 'view':
	deposit_invoice_view(FALSE);
	break;
case 'gdupdate':
	deposit_invoice_gdupdate();
	break;
case 'deposit_receivables':
	deposit_receivables();
	break;
case 'deposit_receivableslist':
	deposit_receivables_list();
	break;
case 'deposit_receivables_edit':
	deposit_receivables_edit();
	break;
case 'deposit_receivables_search':
	deposit_receivables_search();
	break;
case 'deposit_receivables_import':
	deposit_receivables_import();
	break;
case 'deposit_invoice_search':
	deposit_invoice_search();
	break;
case 'deposit_invoice_import':
	deposit_invoice_import();
	break;
default:
	User::no_permission();
}

function deposit_apply() {
	$deposit = new Deposit();
	echo $deposit->get_deposit_apply_html();
	unset($deposit);
}

function deposit_list() {
	$deposit_list = new Deposit_List(
			array(
					'page' => intval(Security_Util::my_get('page')) === 0 ? 1
							: intval(Security_Util::my_get('page'))));
	echo $deposit_list->get_deposit_list_html();
	unset($deposit);
}

function deposit_invoice_apply() {
	$deposit_invoice = new Deposit_Invoice();
	echo $deposit_invoice->get_deposit_invoice_apply_html();
	unset($deposit_invoice);
}

function deposit_invoice_list() {
	$deposit_invoice_list = new Deposit_Invoice_List(
			array(
					'page' => intval(Security_Util::my_get('page')) === 0 ? 1
							: intval(Security_Util::my_get('page'))), TRUE);
	echo $deposit_invoice_list->get_deposit_invoice_list_html();
	unset($deposit_invoice_list);
}

function leader_audit() {
	if (intval(Security_Util::my_get('type')) === 1) {
		//保证金leader审核
		$deposit = new Deposit(array('id' => Security_Util::my_get('id')));
		echo $deposit->get_deposit_leader_audit_html();
		unset($deposit);
	} else if (intval(Security_Util::my_get('type')) === 2) {
		//保证金开票leader审核
		$deposit_invoice = new Deposit_Invoice(
				array('id' => Security_Util::my_get('id')));
		echo $deposit_invoice->get_deposit_invoice_leader_audit_html();
		unset($deposit_invoice);
	} else {
		User::no_permission();
	}
}

function deposit_edit() {
	$deposit = new Deposit(array('id' => Security_Util::my_get('id')));
	echo $deposit->get_deposit_edit_html();
	unset($deposit);
}

function getdeposit() {
	$deposits = new Deposit_Ajax(array('q' => Security_Util::my_get('q')));
	echo $deposits->get_deposit_names();
	unset($deposits);
}

function deposit_invoice_view($ismy = TRUE) {
	$deposit_invoice = new Deposit_Invoice(
			array('id' => Security_Util::my_get('id')));
	echo $deposit_invoice->get_deposit_invoice_view_html($ismy);
	unset($deposit_invoice);
}

function deposit_invoice_edit() {
	$deposit_invoice = new Deposit_Invoice(
			array('id' => Security_Util::my_get('id')));
	echo $deposit_invoice->get_deposit_invoice_edit_html();
	unset($deposit_invoice);
}

function deposit_invoicelist() {
	$deposit_invoice = new Deposit_Invoice_List(
			array(
					'd' => !in_array(intval(Security_Util::my_get('d')),
							array(1, 2, 3), TRUE) ? 1
							: Security_Util::my_get('d'),
					'search' => Security_Util::my_get('search'),
					'page' => intval(Security_Util::my_get('page')) === 0 ? 1
							: intval(Security_Util::my_get('page'))), FALSE);
	echo $deposit_invoice->get_deposit_invoice_list_html();
	unset($invoice);
}

function deposit_invoice_audit() {
	$invoice = new Deposit_Invoice(array('id' => Security_Util::my_get('id')));
	echo $invoice->get_invoice_audit_html();
	unset($invoice);
}

function deposit_invoice_print() {
	$invoice = new Deposit_Invoice(array('id' => Security_Util::my_get('id')));
	echo $invoice->get_deposit_invoice_print_html();
	unset($invoice);
}

function deposit_invoice_gdupdate() {
	$deposit_invoice = new Deposit_Invoice(
			array('id' => Security_Util::my_get('id')));
	echo $deposit_invoice->get_deposit_invoice_gdupdate_html();
	unset($deposit_invoice);
}

function deposit_receivables() {
	$deposit = new Deposit_Receivables();
	echo $deposit->get_add_deposit_receivables_html();
	unset($deposit);
}

function deposit_receivables_list(){
	$deposit_list = new Deposit_Receivables_List(
			array(
					'page' => intval(Security_Util::my_get('page')) === 0 ? 1
							: intval(Security_Util::my_get('page')),
					'search' => Security_Util::my_get('search')));
	echo $deposit_list->get_deposit_receivables_list_html();
	unset($deposit_list);
}

function deposit_receivables_edit(){
	$deposit = new Deposit_Receivables(array('id'=>Security_Util::my_get('id')));
	echo $deposit->get_edit_deposit_receivables_html();
	unset($deposit);
}

function deposit_receivables_search(){
	$deposit_search = new Deposit_Receivables_Search(
			Security_Util::my_get('starttime'),
			Security_Util::my_get('endtime'), Security_Util::my_get('search'));
	echo $deposit_search->get_deposit_receivables_search_html();
	unset($deposit_search);
}

function deposit_receivables_import(){
	$deposit = new Deposit_Receivables();
	echo $deposit->get_import_deposit_receivables_html();
	unset($deposit);
}

function deposit_invoice_search(){
	$invoice_search = new Deposit_Invoice_Search(Security_Util::my_get('starttime'),
			Security_Util::my_get('endtime'), Security_Util::my_get('search'));
	echo $invoice_search->get_deposit_invoice_search_html();
	unset($invoice_search);
}

function deposit_invoice_import(){
	$invoice = new Deposit_Invoice();
	echo $invoice->get_import_deposit_invoice_html();
	unset($invoice);
}