<?php
include (dirname ( dirname ( __FILE__ ) ) . '/inc/my_session.php');
include (dirname ( dirname ( __FILE__ ) ) . '/inc/model_require.php');
include (dirname ( dirname ( __FILE__ ) ) . '/inc/require_file.php');
include (dirname ( dirname ( __FILE__ ) ) . '/user_auth.php');
header ( 'Content-type: text/html; charset=utf-8' );

//校验vcode
$vcode = $uid . User::SALT_VALUE;
include (dirname ( dirname ( __FILE__ ) ) . '/validate_vcode.php');

$result = FALSE;
switch (strval ( Security_Util::my_post ( 'action' ) )) {
	case 'permission_add' :
		$result = permission_do ( 'add' );
		break;
	case 'permission_update' :
		$result = permission_do ( 'update' );
		break;
	case 'deppermission_add' :
		$result = deppermission_do ( 'add' );
		break;
	case 'deppermission_update' :
		$result = deppermission_do ( 'update' );
		break;
}

if ($result !== FALSE) {
	if ($result ['status'] === 'error') {
		Js_Util::my_show_error_message ( $result ['message'] );
	} else if ($result ['status'] === 'success') {
		Js_Util::my_show_success_message ( $result ['message'] );
	}
} else {
	Js_Util::my_show_error_message ();
}

function permission_do($action) {
	$fields = array ('module' => Security_Util::my_post ( 'module' ), 'name' => Security_Util::my_post ( 'name' ), 'des' => Security_Util::my_post ( 'des' ) );
	if ($action === 'update') {
		$fields ['permission_id'] = Security_Util::my_post ( 'permission_id' );
	}
	$permission = new Permission ( NULL, $fields );
	if (! $permission->getHas_setup_permission ()) {
		return array ('status' => 'error', 'message' => NO_RIGHT_TO_DO_THIS );
	} else {
		switch ($action) {
			case 'add' :
				return $permission->add_permission ();
				break;
			case 'update' :
				return $permission->update_permission ();
				break;
			default :
				return FALSE;
		}
	}
}

function deppermission_do($action) {
	$fields = array ('name' => Security_Util::my_post ( 'name' ), 'des' => Security_Util::my_post ( 'des' ), 'dep' => Security_Util::my_post ( 'dep' ) );
	if ($action === 'update') {
		$fields ['permission_id'] = Security_Util::my_post ( 'permission_id' );
	}
	$permission = new Permission_Dep ( NULL, $fields );
	if (! $permission->getHas_setup_permission ()) {
		return array ('status' => 'error', 'message' => NO_RIGHT_TO_DO_THIS );
	} else {
		switch ($action) {
			case 'add' :
				return $permission->add_deppermission ();
				break;
			case 'update' :
				return $permission->update_deppermission ();
				break;
			default :
				return FALSE;
		}
	}
}