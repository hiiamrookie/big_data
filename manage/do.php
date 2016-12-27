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
	case 'del_process' :
		echo del_process ();
		break;
	case 'permissionlistbymodule' :
		echo get_permission_by_module ();
		break;
	case 'permissionlistbydep' :
		echo get_permission_by_dep ();
		break;
	case 'del_depprocess' :
		echo del_depprocess ();
		break;
	case 'search_cusname':
		echo search_cusname();
		break;
}

function del_process() {
	$process = new Process ( intval ( Security_Util::my_post ( 'id' ) ) );
	if (! $process->getHas_process_permission ()) {
		return NO_RIGHT_TO_DO_THIS;
	} else {
		$result = $process->del_process ();
		return $result ['message'];
	}
}

function get_permission_by_module() {
	$result = '<option value="">请选择</option>';
	if (in_array ( $GLOBALS['username'], $GLOBALS['manager_setup_permission'], TRUE )) {
		$permissions = Permission::getInstance ();
		$permissions = $permissions [intval ( Security_Util::my_post ( 'id' ) )];
		if (! empty ( $permissions )) {
			foreach ( $permissions as $permission ) {
				$result .= '<option value="sys' . $permission ['permission_id'] . '">' . $permission ['permission_name'] . '</option>';
			}
			$result .= '<option value="DEP">支持部门</option>';
		}
	}
	return $result;
}

function get_permission_by_dep() {
	$result = '<option value="">请选择</option>';
	if (in_array ( $GLOBALS['username'], $GLOBALS['manager_setup_permission'], TRUE )) {
		$permissions = Permission_Dep::getInstance ();
		$permissions = $permissions [intval ( Security_Util::my_post ( 'id' ) )];
		if (! empty ( $permissions )) {
			foreach ( $permissions as $permission ) {
				$result .= '<option value="dep' . $permission ['permission_id'] . '">' . $permission ['permission_name'] . '</option>';
			}
		}
	}
	return $result;
}

function del_depprocess() {
	$process = new Dep_Process ( intval ( Security_Util::my_post ( 'id' ) ) );
	if (! $process->getHas_process_permission ()) {
		return NO_RIGHT_TO_DO_THIS;
	} else {
		$result = $process->del_depprocess ();
		return $result ['message'];
	}
}

function search_cusname(){
	$customer = new Customer(
			array('search' => Security_Util::my_post('search')));
	return $customer->get_search_cusname_html();
}