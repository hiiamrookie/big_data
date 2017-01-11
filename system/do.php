<?php
include (dirname ( dirname ( __FILE__ ) ) . '/inc/my_session.php');
include (dirname ( dirname ( __FILE__ ) ) . '/inc/model_require.php');
include (dirname ( dirname ( __FILE__ ) ) . '/inc/require_file.php');
include (dirname ( dirname ( __FILE__ ) ) . '/user_auth.php');
header ( 'Content-type: text/html; charset=utf-8' );

//校验vcode
$vcode = $uid . User::SALT_VALUE;
$is_ajax = TRUE;
include (dirname ( dirname ( __FILE__ ) ) . '/validate_vcode.php');

switch (strval ( Security_Util::my_post ( 'action' ) )) {
	case 'del_permission' :
		echo del_permission ();
		break;
	case 'del_deppermission' :
		echo del_deppermission ();
		break;
	case 'del_api_auth':
		echo del_api_auth();
		break;
}

function del_permission() {
	$permission = new Permission ( intval ( Security_Util::my_post ( 'id' ) ) );
	if ($permission->getHas_setup_permission ()) {
		$result = $permission->del_permission ();
		return $result ['message'];
	} else {
		return NO_RIGHT_TO_DO_THIS;
	}
}

function del_deppermission() {
	$dep_permission = new Permission_Dep ( intval ( Security_Util::my_post ( 'id' ) ) );
	if ($dep_permission->getHas_setup_permission ()) {
		$result = $dep_permission->del_deppermission ();
		return $result ['message'];
	} else {
		return NO_RIGHT_TO_DO_THIS;
	}
}

function del_api_auth(){
	$api = new API(array('id'=>Security_Util::my_post('id')));
	$result = $api->del_api_auth();
	return $result ['message'];
}