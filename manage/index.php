<?php
include(dirname(dirname(__FILE__)) . '/inc/my_session.php');
include(dirname(dirname(__FILE__)) . '/inc/model_require.php');
include(dirname(dirname(__FILE__)) . '/inc/require_file.php');
include(dirname(dirname(__FILE__)) . '/user_auth.php');
header('Content-type: text/html; charset=utf-8');

switch (Security_Util::my_get('o')) {
case 'processlist':
	process_list();
	break;
case 'processadd':
	process_add();
	break;
case 'processedit':
	process_edit();
	break;
case 'depprocesslist':
	dep_process_list();
	break;
case 'depprocessadd':
	dep_process_add();
	break;
case 'depprocessedit':
	dep_process_edit();
	break;
case 'customeradd':
	customer_add();
	break;
case 'customerlist':
	customer_list();
	break;
case 'customeredit':
	customer_edit();
	break;
case 'customerrelate':
	customer_relate();
	break;
case 'customerimport':
	customer_import();
	break;
default:
	User::no_permission();
}

function process_list() {
	$process_list = new Process_List();
	echo $process_list->get_process_list_html();
	unset($process_list);
}

function process_add() {
	$process = new Process();
	echo $process->get_process_add_html();
	unset($process);
}

function process_edit() {
	$process = new Process(
			Validate_Util::my_is_int(Security_Util::my_get('id')) ? intval(
							Security_Util::my_get('id')) : 0);
	echo $process->get_process_edit_html();
	unset($process);
}

function dep_process_list() {
	$dep_process = new Dep_Process_List();
	echo $dep_process->get_dep_process_list_html();
	unset($dep_process);
}

function dep_process_add() {
	$process = new Dep_Process();
	echo $process->get_dep_process_add_html();
	unset($process);
}

function dep_process_edit() {
	$dep_process = new Dep_Process(
			Validate_Util::my_is_int(Security_Util::my_get('id')) ? intval(
							Security_Util::my_get('id')) : 0);
	echo $dep_process->get_dep_process_edit_html();
	unset($dep_process);
}

function customer_add() {
	$customer = new Customer();
	echo $customer->get_customer_add_html();
	unset($customer);
}

function customer_list() {
	$customer_list = new Customer_List(
			array(
					'page' => intval(Security_Util::my_get('page')) === 0 ? 1
							: intval(Security_Util::my_get('page'))));
	echo $customer_list->get_customer_list_html();
	unset($customer_list);
}

function customer_edit() {
	$customer = new Customer(
			array('customer_id' => Security_Util::my_get('customer_id')));
	echo $customer->get_customer_edit_html();
	unset($customer);
}

function customer_relate() {
	$customer = new Customer(
			array('customer_id' => Security_Util::my_get('customer_id')));
	echo $customer->get_customer_relate_html();
	unset($customer);
}

function customer_import(){
	$customer = new Customer();
	echo $customer->get_customer_import_html();
	unset($customer);
}
