<?php
include(dirname(dirname(dirname(__FILE__))) . '/inc/my_session.php');
include(dirname(dirname(dirname(__FILE__))) . '/inc/model_require.php');
include(dirname(dirname(dirname(__FILE__))) . '/inc/require_file.php');
include(dirname(dirname(dirname(__FILE__))) . '/user_auth.php');
header('Content-type: text/html; charset=utf-8');

switch (Security_Util::my_get('o')) {
case 'receivableslist':
	receivables_list();
	break;
case 'receivables':
	receivables();
	break;
case 'receivables_normal_search':
	receivables_normal_search();
	break;
case 'receivables_search':
	receivables_search();
	break;
case 'receivables_import':
	receivables_import();
	break;
case 'receivables_edit':
	receivables_edit();
	break;
default:
	User::no_permission();
}

function receivables_list() {
	$finance_list = new Finance_Receivables_List(
			array(
					'page' => intval(Security_Util::my_get('page')) === 0 ? 1
							: intval(Security_Util::my_get('page')),
					'search' => Security_Util::my_get('search')));
	echo $finance_list->get_receivables_list_html();
	unset($finance_list);
}

function receivables() {
	$finance = new Finance_Receivables();
	echo $finance->get_add_receivables_html();
	unset($finance);
}

function receivables_normal_search() {
	$finance_normal_search = new Finance_Receivables_Normal_Search(
			Security_Util::my_get('starttime'),
			Security_Util::my_get('endtime'), Security_Util::my_get('search'));
	echo $finance_normal_search->get_receivables_normal_search_html();
	unset($finance_normal_search);
}

function receivables_search() {
	$finance_search = new Finance_Receivables_Search(
			Security_Util::my_get('starttime'),
			Security_Util::my_get('endtime'), Security_Util::my_get('search'));
	echo $finance_search->get_receivables_search_html();
	unset($finance_search);
}

function receivables_import(){
	$finance = new Finance_Receivables();
	echo $finance->get_import_receivables_html();
	unset($finance);
}

function receivables_edit(){
	$finance = new Finance_Receivables(array('id'=>Security_Util::my_get('id')));
	echo $finance->get_edit_receivables_html();
	unset($finance);
}