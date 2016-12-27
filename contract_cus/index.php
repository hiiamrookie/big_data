<?php
include(dirname(dirname(__FILE__)) . '/inc/my_session.php');
include(dirname(dirname(__FILE__)) . '/inc/model_require.php');
include(dirname(dirname(__FILE__)) . '/inc/require_file.php');
include(dirname(dirname(__FILE__)) . '/user_auth.php');
header('Content-type: text/html; charset=utf-8');

switch (Security_Util::my_get('o')) {
case 'getcid':
	getcid();
	break;
case 'add':
	contract_add();
	break;
case 'mylist':
	contract_mylist();
	break;
case 'info':
	contract_info();
	break;
case 'list':
	contract_list();
	break;
case 'manage':
	contract_manage();
	break;
case 'edit':
	contract_edit();
	break;
case 'audit':
	contract_audit();
	break;
}

function getcid() {
	$q = Security_Util::my_get('q');
	$dao = new Dao_Impl();
	$s = '';
	$results = $dao->db
			->get_results(
					'SELECT cid,contractcontent,isok,isexecutive,cusname FROM contract_cus WHERE isok=1 AND cid LIKE "%'
							. $q . '%" OR contractcontent LIKE "%' . $q . '%"');
	if ($results !== NULL) {
		foreach ($results as $result) {
			$s .= $result->cid . '-' . $result->contractcontent . "\n";
		}
	}
	echo $s;
}

function contract_add() {
	$contract = new Contract();
	echo $contract->get_contract_add_html();
	unset($contract);
}

function contract_mylist() {
	$contract_list = new Contract_List(
			array(
					'page' => intval(Security_Util::my_get('page')) === 0 ? 1
							: intval(Security_Util::my_get('page')),
					'starttime' => Security_Util::my_get('starttime'),
					'endtime' => Security_Util::my_get('endtime'),
					'search' => Security_Util::my_get('search')));
	echo $contract_list->get_contract_mylist_html();
	unset($contract_list);
}

function contract_info() {
	$contract = new Contract(
			array('cid' => Security_Util::my_get('cid')), TRUE);
	echo $contract->get_contract_info_html();
	unset($contract);
}

function contract_list() {
	$contract_list = new Contract_List(
			array(
					'page' => intval(Security_Util::my_get('page')) === 0 ? 1
							: intval(Security_Util::my_get('page')),
					'starttime' => Security_Util::my_get('starttime'),
					'endtime' => Security_Util::my_get('endtime'),
					'search' => Security_Util::my_get('search'),
					'city' => Security_Util::my_get('city'),
					'dep' => Security_Util::my_get('dep'),
					'team' => Security_Util::my_get('team')), TRUE, FALSE);
	echo $contract_list->get_check_contract_html();
	unset($contract_list);
}

function contract_manage() {
	$contract_list = new Contract_List(
			array(
					'page' => intval(Security_Util::my_get('page')) === 0 ? 1
							: intval(Security_Util::my_get('page')),
					'starttime' => Security_Util::my_get('starttime'),
					'endtime' => Security_Util::my_get('endtime'),
					'search' => Security_Util::my_get('search'),
					'city' => Security_Util::my_get('city'),
					'dep' => Security_Util::my_get('dep'),
					'team' => Security_Util::my_get('team')), FALSE, TRUE);
	echo $contract_list->get_manage_contract_html();
	unset($contract_list);
}

function contract_edit() {
	$contract = new Contract(array('cid' => Security_Util::my_get('cid')));
	echo $contract->get_contract_edit_html();
	unset($contract);
}

function contract_audit() {
	$contract = new Contract(
			array('cid' => Security_Util::my_get('cid')), TRUE);
	echo $contract->get_contract_audit_html();
	unset($contract);
}
