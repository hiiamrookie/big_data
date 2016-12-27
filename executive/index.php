<?php
include(dirname(dirname(__FILE__)) . '/inc/my_session.php');
include(dirname(dirname(__FILE__)) . '/inc/model_require.php');
include(dirname(dirname(__FILE__)) . '/inc/require_file.php');
include(dirname(dirname(__FILE__)) . '/user_auth.php');
header('Content-type: text/html; charset=utf-8');

switch (Security_Util::my_get('o')) {
case 'add':
	executive_add();
	break;
case 'edit':
	executive_edit();
	break;
case 'mylist':
	executive_mylist();
	break;
case 'info':
	executive_info();
	break;
case 'print':
	executive_print();
	break;
case 'alllist':
	executive_alllist();
	break;
case 'manage':
	executive_manage();
	break;
case 'tj':
	executive_tj();
	break;
case 'audit':
	executive_audit();
	break;
case 'auditdep':
	executive_dep_audit();
	break;
case 'alter':
	executive_alter();
	break;
case 'getpidname':
	get_pidname();
	break;
case 'getokpidname':
	get_pidname(TRUE);
	break;
case 'userchange':
	user_change();
	break;
case 'cy':
	executive_cy();
	break;
case 'getpidname_bytec':
	getpidname_bytec();
	break;
default:
	User::no_permission();
}

function executive_add() {
	$executive = new Executive();
	echo $executive->get_executive_add_html();
	unset($executive);
}

function executive_edit() {
	$executive = new Executive(NULL,
			array('pid' => Security_Util::my_get('pid')));
	echo $executive->get_executive_edit();
	unset($executive);
}

function executive_mylist() {
	$executive_list = new Executive_List(
			array(
					'page' => intval(Security_Util::my_get('page')) === 0 ? 1
							: intval(Security_Util::my_get('page')),
					'starttime' => Security_Util::my_get('starttime'),
					'endtime' => Security_Util::my_get('endtime'),
					'search' => Security_Util::my_get('search')));
	echo $executive_list->get_executive_mylist_html();
	unset($executive_list);
}

function executive_info() {
	$executive = new Executive(NULL,
			array('pid' => Security_Util::my_get('pid'),
					'executive_id' => Security_Util::my_get('id'),
					'contrast' => Security_Util::my_get('d')));
	echo $executive->get_executive_info_html();
	unset($executive);
}

function executive_print() {
	$executive = new Executive(NULL,
			array('pid' => Security_Util::my_get('pid'),
					'executive_id' => Security_Util::my_get('id'),
					'contrast' => Security_Util::my_get('d')));
	echo $executive->get_executive_print_html();
	unset($executive);
}

function executive_alllist() {
	$executive_list = new Executive_List(
			array(
					'page' => intval(Security_Util::my_get('page')) === 0 ? 1
							: intval(Security_Util::my_get('page')),
					'starttime' => Security_Util::my_get('starttime'),
					'endtime' => Security_Util::my_get('endtime'),
					'search' => Security_Util::my_get('search'),
					'city' => Security_Util::my_get('city'),
					'dep' => Security_Util::my_get('dep'),
					'team' => Security_Util::my_get('team')), TRUE);
	echo $executive_list->get_executive_alllist_html();
	unset($executive_list);
}

function executive_manage() {
	$executive_list = new Executive_List(
			array(
					'page' => intval(Security_Util::my_get('page')) === 0 ? 1
							: intval(Security_Util::my_get('page')),
					'starttime' => Security_Util::my_get('starttime'),
					'endtime' => Security_Util::my_get('endtime'),
					'search' => Security_Util::my_get('search'),
					'city' => Security_Util::my_get('city'),
					'dep' => Security_Util::my_get('dep'),
					'team' => Security_Util::my_get('team'),
					'gd' => Security_Util::my_get('gd')), FALSE, TRUE);
	echo $executive_list->get_executive_manage_html();
	unset($executive_list);
}

function executive_tj() {
	$executive_tj = new Executive_Tj(Security_Util::my_get('month'));
	echo $executive_tj->get_executive_tj_html();
	unset($executive_tj);
}

function executive_audit() {
	$executive = new Executive(NULL,
			array('pid' => Security_Util::my_get('pid'),
					'executive_id' => Security_Util::my_get('id')));
	echo $executive->get_executive_audit_html();
	unset($executive);
}

function executive_dep_audit() {
	$executive = new Executive(NULL,
			array('pid' => Security_Util::my_get('pid'),
					'dep' => Security_Util::my_get('dep'),
					'executive_id' => Security_Util::my_get('id')));
	echo $executive->get_executive_dep_audit();
	unset($executive);
}

function executive_alter() {
	$executive = new Executive(NULL,
			array('pid' => Security_Util::my_get('pid')));
	echo $executive->get_executive_alter();
	unset($executive);
}

function get_pidname($checkok = FALSE) {
	$executive_list = new Executive_Ajax(Security_Util::my_get('q'));
	echo $executive_list->get_pid_names($checkok);
	unset($executive_list);
}

function user_change() {
	$executive_userchange = new Executive_Userchange(
			Security_Util::my_get('search'));
	echo $executive_userchange->get_user_change_html();
	unset($executive_userchange);
}

function executive_cy() {
	$executive = new Executive(NULL,
			array('pid' => Security_Util::my_get('pid'),
					'executive_id' => Security_Util::my_get('id')));
	echo $executive->get_executive_cy_html();
	unset($executive);
}

function getpidname_bytec() {
	$executive_list = new Executive_Ajax(Security_Util::my_get('q'));
	echo $executive_list->get_pid_names_bytec();
}
