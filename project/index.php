<?php
include (dirname ( dirname ( __FILE__ ) ) . '/inc/my_session.php');
include (dirname ( dirname ( __FILE__ ) ) . '/inc/model_require.php');
include (dirname ( dirname ( __FILE__ ) ) . '/inc/require_file.php');
include (dirname ( dirname ( __FILE__ ) ) . '/user_auth.php');
header ( 'Content-type: text/html; charset=utf-8' );

switch (Security_Util::my_get ( 'o' )) {
	case 'add' :
		project_add ();
		break;
	case 'mylist' :
		project_mylist ();
		break;
	case 'audit' :
		project_audit ();
		break;
	case 'edit' :
		project_edit ();
		break;
	default :
		User::no_permission ();
}
function project_add() {
	$project = new Project ();
	echo $project->getIndexHtml ();
	unset ( $project );
}
function project_mylist() {
	$project_list = new Project_List ( array (
			'page' => intval ( Security_Util::my_get ( 'page' ) ) === 0 ? 1 : intval ( Security_Util::my_get ( 'page' ) ) 
	) );
	echo $project_list->getMyProjectListHtml ();
	unset ( $project_list );
}
function project_audit() {
	$project = new Project ();
	echo $project->getProjectAuditHtml ( Security_Util::my_get ( 'id' ) );
	unset ( $project );
}
function project_edit() {
	$project = new Project ();
	echo $project->getProjectEditHtml ( Security_Util::my_get ( 'id' ) );
	unset ( $project );
}