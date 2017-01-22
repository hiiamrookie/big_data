<?php
include (dirname ( dirname ( __FILE__ ) ) . '/inc/my_session.php');
include (dirname ( dirname ( __FILE__ ) ) . '/inc/model_require.php');
include (dirname ( dirname ( __FILE__ ) ) . '/inc/require_file.php');
include (dirname ( dirname ( __FILE__ ) ) . '/user_auth.php');
header ( 'Content-type: text/html; charset=utf-8' );

// 校验vcode
$vcode = $uid . User::SALT_VALUE;
include (dirname ( dirname ( __FILE__ ) ) . '/validate_vcode.php');

$result = FALSE;
$action = strval ( Security_Util::my_post ( 'action' ) );
switch ($action) {
	case 'project_add' :
		$result = project_do ( 'add' );
		break;
	case 'project_audit' :
		$result = project_do ( 'audit' );
		break;
	case 'project_update' :
		$result = project_do ( 'update' );
		break;
}

if ($result !== FALSE) {
	if ($result ['status'] === 'error') {
		Js_Util::my_show_error_message ( $result ['message'] );
	} else if ($result ['status'] === 'success') {
		$url = NULL;
		if ($action === 'project_audit') {
			$url = BASE_URL;
		} else if ($action === 'project_update') {
			$url = BASE_URL . 'project/?o=mylist';
		}
		Js_Util::my_show_success_message ( $result ['message'], $url );
	}
} else {
	Js_Util::my_show_error_message ();
}
function project_do($action) {
	if ($action === 'add') {
		$fields = array (
				'projectname' => Security_Util::my_post ( 'projectname' ),
				'remark' => Security_Util::my_post ( 'remark' ) 
		);
	} else if ($action === 'audit') {
		$fields = array (
				'id' => Security_Util::my_post ( 'id' ),
				'audit_pass' => Security_Util::my_post ( 'audit_pass' ),
				'reason' => Security_Util::my_post ( 'reason' ) 
		);
	} else if ($action === 'update') {
		$fields = array (
				'id' => Security_Util::my_post ( 'id' ),
				'projectname' => Security_Util::my_post ( 'projectname' ),
				'remark' => Security_Util::my_post ( 'remark' ) 
		);
	}
	$project = new Project ( $fields );
	if ($action === 'add') {
		return $project->getAddProjectResult ();
	} else if ($action === 'audit') {
		return $project->getAuditProjectResult ();
	} else if ($action === 'update') {
		return $project->getUpdateProjectResult ();
	}
}