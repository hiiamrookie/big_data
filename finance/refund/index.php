<?php
include(dirname(dirname(dirname(__FILE__))) . '/inc/my_session.php');
include(dirname(dirname(dirname(__FILE__))) . '/inc/model_require.php');
include(dirname(dirname(dirname(__FILE__))) . '/inc/require_file.php');
include(dirname(dirname(dirname(__FILE__))) . '/user_auth.php');
header('Content-type: text/html; charset=utf-8');

switch (Security_Util::my_get('o')) {
case 'apply':
	refund_apply();
	break;
case 'manager':
	refund_manager();
	break;
case 'audit':
	refund_audit();
	break;
case 'mylist':
	refund_mylist();
	break;
case 'edit':
	refund_edit();
	break;
case 'print':
	refund_print();
	break;
case 'gd':
	refund_gd();
	break;
case 'mediarefund':
	refund_media_add();
	break;
case 'mediarefundlist':
	refund_mediare_list();
	break;
default:
	User::no_done();
}

function refund_apply() {
	$refund_apply = new Finance_Refund();
	echo $refund_apply->get_finance_apply_html();
	unset($refund_apply);
}

function refund_manager() {
	$refund_apply = new Finance_Refund(
			array(
					'page' => intval(Security_Util::my_get('page')) === 0 ? 1
							: intval(Security_Util::my_get('page'))));
	echo $refund_apply->get_finance_manager_html();
	unset($refund_apply);
}

function refund_audit() {
	$refund_apply = new Finance_Refund(
			array('id' => Security_Util::my_get('id')));
	echo $refund_apply->get_finance_refund_audit_html();
	unset($refund_apply);
}

function refund_mylist() {
	$refund_apply = new Finance_Refund(
			array(
					'page' => intval(Security_Util::my_get('page')) === 0 ? 1
							: intval(Security_Util::my_get('page'))));
	echo $refund_apply->get_finance_refund_mylist_html();
	unset($refund_apply);
}

function refund_edit() {
	$refund_apply = new Finance_Refund(
			array('id' => Security_Util::my_get('id')));
	echo $refund_apply->get_finance_refund_edit_html();
	unset($refund_apply);
}

function refund_print() {
	$refund_apply = new Finance_Refund(
			array('id' => Security_Util::my_get('id')));
	echo $refund_apply->get_finance_refund_print_html();
	unset($refund_apply);
}

function refund_gd() {
	$refund_apply = new Finance_Refund(
			array('id' => Security_Util::my_get('id')));
	echo $refund_apply->get_finance_refund_gd_html();
	unset($refund_apply);
}

function refund_media_add() {
	$refund_media = new Finance_Refund_Media();
	echo $refund_media->get_finance_refund_media_add_html();
	unset($refund_media);
}

function refund_mediare_list() {
	$refund_media = new Finance_Refund_Media(
			array(
					'page' => intval(Security_Util::my_get('page')) === 0 ? 1
							: intval(Security_Util::my_get('page'))));
	echo $refund_media->get_finance_refund_media_list_html();
	unset($refund_media);
}
