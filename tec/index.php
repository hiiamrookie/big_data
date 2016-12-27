<?php
include(dirname(dirname(__FILE__)) . '/inc/my_session.php');
include(dirname(dirname(__FILE__)) . '/inc/model_require.php');
include(dirname(dirname(__FILE__)) . '/inc/require_file.php');
include(dirname(dirname(__FILE__)) . '/user_auth.php');
header('Content-type: text/html; charset=utf-8');

switch (Security_Util::my_get('o')) {
case 'projectlist':
	project_list();
	break;
case 'projectadd':
	project_add();
	break;
case 'projectedit':
	project_edit();
	break;
case 'projectresponse':
	project_response();
	break;
case 'projectinfo':
	project_info();
	break;
default:
	User::no_permission();
}

function project_list() {
	$tec_project_list = new Tec_Project_List(
			array(
					'page' => intval(Security_Util::my_get('page')) === 0 ? 1
							: intval(Security_Util::my_get('page'))));
	echo $tec_project_list->get_tec_project_list_html();
	unset($tec_project_list);
}

function project_add() {
	$tec_project = new Tec_Project();
	echo $tec_project->get_tec_project_add_html();
	unset($tec_project);
}

function project_edit() {
	$tec_project = new Tec_Project(
			array('id' => intval(Security_Util::my_get('id'))));
	echo $tec_project->get_tec_project_edit_html();
	unset($tec_project);
}

function project_response(){
	$tec_project = new Tec_Project(
			array('id' => intval(Security_Util::my_get('id')),'tpid'=>Security_Util::my_get('project_id')));
	echo $tec_project->get_tec_project_response_html();
	unset($tec_project);
}

function project_info(){
	$tec_project = new Tec_Project(
			array('id' => intval(Security_Util::my_get('id')),'tpid'=>Security_Util::my_get('project_id')));
	echo $tec_project->get_tec_project_info_html();
	unset($tec_project);
}