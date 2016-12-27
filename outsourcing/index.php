<?php
include(dirname(dirname(__FILE__)) . '/inc/my_session.php');
include(dirname(dirname(__FILE__)) . '/inc/model_require.php');
include(dirname(dirname(__FILE__)) . '/inc/require_file.php');
include(dirname(dirname(__FILE__)) . '/user_auth.php');
header('Content-type: text/html; charset=utf-8');

switch (Security_Util::my_get('o')) {
case 'addtype':
	outsourcing_type_do('add');
	break;
case 'editype':
	outsourcing_type_do('edit');
	break;
case 'typelist':
	outsourcing_type_list();
	break;
case 'addprocess':
	outsourcing_process_do('add');
	break;
case 'editprocess':
	outsourcing_process_do('edit');
	break;
case 'processlist':
	outsourcing_process_list();
	break;
case 'auditoutsourcing':
	outsourcing_audit();
	break;
case 'auditoutsourcinglists':
	outsourcing_audit_list();
	break;
case 'outsourcinginfo':
	outsourcing_info();
	break;
default:
	User::no_permission();
}

function outsourcing_type_do($action) {
	$field = array();
	if ($action === 'edit') {
		$field['id'] = Security_Util::my_get('id');
	}
	$outsourcing_type = new Outsourcing_Type($field);
	switch ($action) {
	case 'add':
		echo $outsourcing_type->getAddOutsourcingTypeHtml();
		break;
	case 'edit':
		echo $outsourcing_type->getEditOutsourcingTypeHtml();
		break;
	}
	unset($outsourcing_type);
}

function outsourcing_process_do($action) {
	$field = array();
	if ($action === 'edit') {
		$field['id'] = Security_Util::my_get('id');
	}
	$outsourcing_process = new Outsourcing_Process($field);
	switch ($action) {
	case 'add':
		echo $outsourcing_process->getAddOutsourcingProcessHtml();
		break;
	case 'edit':
		echo $outsourcing_process->getEditOutsourcingProcessHtml();
		break;
	}
	unset($outsourcing_process);
}

function outsourcing_type_list() {
	$outsourcing_type = new Outsourcing_Type(
			array(
					'page' => intval(Security_Util::my_get('page')) === 0 ? 1
							: intval(Security_Util::my_get('page'))));
	echo $outsourcing_type->getOutsourcingTypeListHtml();
	unset($outsourcing_type);
}

function outsourcing_process_list() {
	$outsourcing_process = new Outsourcing_Process(
			array(
					'page' => intval(Security_Util::my_get('page')) === 0 ? 1
							: intval(Security_Util::my_get('page'))));
	echo $outsourcing_process->getOutsourcingProcessListHtml();
	unset($outsourcing_process);
}

function outsourcing_audit() {
	$outsourcing = new Outsourcing(
			array('executive_id' => intval(Security_Util::my_get('id'))));
	echo $outsourcing->getAuditOutsourcingHtml();
	unset($outsourcing);
}

function outsourcing_audit_list(){
	$outsourcing = new Outsourcing(
			array(
					'page' => intval(Security_Util::my_get('page')) === 0 ? 1
							: intval(Security_Util::my_get('page'))));
	echo $outsourcing->getOutsourcingAuditListHtml();
	unset($outsourcing);
}

function outsourcing_info(){
	$outsourcing = new Outsourcing(array('id'=>Security_Util::my_get('id')));
	echo $outsourcing->getOutsourcingInfoHtml();
	unset($outsourcing);
}