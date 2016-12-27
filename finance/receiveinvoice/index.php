<?php
include(dirname(dirname(dirname(__FILE__))) . '/inc/my_session.php');
include(dirname(dirname(dirname(__FILE__))) . '/inc/model_require.php');
include(dirname(dirname(dirname(__FILE__))) . '/inc/require_file.php');
include(dirname(dirname(dirname(__FILE__))) . '/user_auth.php');
header('Content-type: text/html; charset=utf-8');

//User::no_done();

switch (Security_Util::my_get('o')) {
case 'receiveinvoicelist':
	receiveinvoicelist();
	break;
case 'receiveinvoiceimport':
	receiveinvoiceimport();
	break;
case 'receiveinvoiceedit':
	receiveinvoiceedit();
	break;
case 'receiveinvoicefix':
	receiveinvoicefix();
	break;
case 'receiveinvoiceadd':
	receiveinvoiceadd();
	break;
case 'receiveinvoiceshare':
	receiveinvoiceshare();
	break;
case 'receiveinvoicepaymentshare':
	receiveinvoicepaymentshare();
	break;
case 'invoiceedit':
//转移修改模块中的发票修改
	invoiceedit();
	break;
case 'invoicetransfer':
	invoicetransfer();
	break;
case 'pidsharelist':
	pidsharelist();
	break;
case 'paymentsharelist':
	paymentsharelist();
	break;
case 'pidshareedit':
	pidshareedit();
	break;
case 'paymentshareedit':
	paymentshareedit();
	break;
case 'virtualinvoiceshare':
	virtualinvoiceshare();
	break;
case 'virtualinvoicesharepayment':
	virtualinvoicesharepayment();
	break;
default:
	User::no_permission();
}

function receiveinvoicelist() {
	$invoice_list = new Finance_Receive_Invoice_List(
			array(
					'page' => intval(Security_Util::my_get('page')) === 0 ? 1
							: intval(Security_Util::my_get('page')),
					'starttime' => Security_Util::my_get('starttime'),
					'endtime' => Security_Util::my_get('endtime'),
					'search' => Security_Util::my_get('search')));
	echo $invoice_list->get_receive_invoice_list_html();
	unset($invoice_list);
}

function receiveinvoiceimport() {
	$invoice = new Finance_Receive_Invoice();
	echo $invoice->get_import_receive_invoice_html();
	unset($invoice);
}

function receiveinvoiceedit() {
	$invoice = new Finance_Receive_Invoice(
			array('sourceid' => Security_Util::my_get('id')));
	echo $invoice->get_edit_receive_invoice_source_html();
	unset($invoice);
}

function receiveinvoicefix() {
	$invoice = new Finance_Receive_Invoice(
			array('sourceid' => Security_Util::my_get('id')));
	echo $invoice->get_fix_receive_invoice_source_html();
	unset($invoice);
}

function receiveinvoiceadd() {
	$invoice = new Finance_Receive_Invoice();
	echo $invoice->get_add_receive_invoice_source_html();
	unset($invoice);
}

function receiveinvoiceshare() {
	$invoice = new Finance_Receive_Invoice(
			array('ids' => Security_Util::my_get('ids')));
	echo $invoice->get_receive_invoice_share_html();
	unset($invoice);
}

function receiveinvoicepaymentshare() {
	$invoice = new Finance_Receive_Invoice(
			array('ids' => Security_Util::my_get('ids')));
	echo $invoice->get_receive_invoice_payment_share_html();
	unset($invoice);
}

function invoiceedit() {
	$invoice = new Finance_Receive_Invoice();
	echo $invoice->get_receive_invoice_edit_html();
	unset($invoice);
}

function invoicetransfer() {
	$invoice = new Finance_Receive_Invoice();
	echo $invoice->get_receive_invoice_transfer_html();
	unset($invoice);
}

function pidsharelist() {
	$invoice_list = new Finance_Receive_Invoice_List(
			array(
					'page' => intval(Security_Util::my_get('page')) === 0 ? 1
							: intval(Security_Util::my_get('page'))));
	echo $invoice_list->get_receive_invoice_pid_share_list_html();
	unset($invoice_list);
}

function paymentsharelist() {
	$invoice_list = new Finance_Receive_Invoice_List(
			array(
					'page' => intval(Security_Util::my_get('page')) === 0 ? 1
							: intval(Security_Util::my_get('page'))));
	echo $invoice_list->get_receive_invoice_payment_share_list_html();
	unset($invoice_list);
}

function pidshareedit() {
	$invoice = new Finance_Receive_Invoice(
			array('shareid' => Security_Util::my_get('id')));
	echo $invoice->get_edit_receive_invoice_pid_share_html();
	unset($invoice);
}

function paymentshareedit() {
	$invoice = new Finance_Receive_Invoice(
			array('shareid' => Security_Util::my_get('id')));
	echo $invoice->get_edit_receive_invoice_payment_share_html();
	unset($invoice);
}

function virtualinvoiceshare() {
	$virtual = new Virtual_Invoice();
	echo $virtual->getVirtualInvoiceShareHtml();
	unset($virtual);
}

function virtualinvoicesharepayment(){
	$virtual = new Virtual_Invoice();
	echo $virtual->getVirtualInvoiceSharePaymentHtml();
	unset($virtual);
}
