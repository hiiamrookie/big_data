<?php
include(dirname(dirname(dirname(__FILE__))) . '/inc/my_session.php');
include(dirname(dirname(dirname(__FILE__))) . '/inc/model_require.php');
include(dirname(dirname(dirname(__FILE__))) . '/inc/require_file.php');
include(dirname(dirname(dirname(__FILE__))) . '/user_auth.php');
header('Content-type: text/html; charset=utf-8');

switch (Security_Util::my_get('o')) {
case 'apply':
	invoice_apply();
	break;
case 'invoicelist':
	invoice_list();
	break;
case 'view':
	invoice_view();
	break;
case 'myview':
	invoice_myview();
	break;
case 'audit':
	invoice_audit();
	break;
case 'mylist':
	invoice_mylist();
	break;
case 'leader_audit':
	invoice_leader_audit();
	break;
case 'print':
	invoice_print();
	break;
case 'invoice_search': //财务部使用
	invoice_search();
	break;
case 'insearch': //赋予权限的使用
	insearch();
	break;
case 'normalview':
	normal_view();
	break;
case 'edit':
	invoice_edit();
	break;
case 'myedit':
	invoice_myedit();
	break;
case 'normalprint':
	invoice_normalprint();
	break;
case 'invoice_import':
	invoice_import();
	break;
default:
	User::no_permission();
}

function invoice_apply() {
	$invoice = new Invoice();
	echo $invoice->get_invoice_apply_html();
	unset($invoice);
}

function invoice_list() {
	$invoice = new Invoice_List(
			array(
					'd' => !in_array(intval(Security_Util::my_get('d')),
							array(1, 2, 3), TRUE) ? 1
							: Security_Util::my_get('d'),
					'search' => Security_Util::my_get('search')));
	echo $invoice->get_invoice_list_html();
	unset($invoice);
}

function invoice_view() {
	$invoice = new Invoice(Security_Util::my_get('id'));
	echo $invoice->get_invoice_view_html();
	unset($invoice);
}

function invoice_myview() {
	$invoice = new Invoice(Security_Util::my_get('id'));
	echo $invoice->get_invoice_myview_html();
	unset($invoice);
}

function normal_view() {
	$invoice = new Invoice(Security_Util::my_get('id'));
	echo $invoice->get_invoice_normalview_html();
	unset($invoice);
}

function invoice_audit() {
	$invoice = new Invoice(Security_Util::my_get('id'));
	echo $invoice->get_invoice_audit_html();
	unset($invoice);
}

function invoice_mylist() {
	$invoice = new Invoice_List(array(), TRUE);
	echo $invoice->get_invoice_mylist_html();
	unset($invoice);
}

function invoice_leader_audit() {
	$invoice = new Invoice(Security_Util::my_get('id'));
	echo $invoice->get_invoice_leader_audit_html();
	unset($invoice);
}

function invoice_print() {
	$invoice = new Invoice(Security_Util::my_get('id'));
	echo $invoice->get_invoice_print_html();
	unset($invoice);
}

function invoice_search() {
	$invoice_search = new Invoice_Search(Security_Util::my_get('starttime'),
			Security_Util::my_get('endtime'), Security_Util::my_get('search'));
	echo $invoice_search->get_invoice_search_html();
	unset($invoice_search);
}

function insearch() {
	$invoice_normal_search = new Invoice_Normal_Search(
			Security_Util::my_get('starttime'),
			Security_Util::my_get('endtime'), Security_Util::my_get('search'));
	echo $invoice_normal_search->get_invoice_search_html();
	unset($invoice_normal_search);
}

function invoice_edit() {
	$invoice = new Invoice(Security_Util::my_get('id'));
	echo $invoice->get_invoice_edit_html();
	unset($invoice);
}

function invoice_myedit() {
	$invoice = new Invoice(Security_Util::my_get('id'));
	echo $invoice->get_invoice_myedit_html();
	unset($invoice);
}

function invoice_normalprint() {
	$invoice = new Invoice(Security_Util::my_get('id'));
	echo $invoice->get_invoice_normal_print_html();
	unset($invoice);
}

function invoice_import(){
	$invoice = new Invoice();
	echo $invoice->get_import_invoice_html();
	unset($invoice);
}