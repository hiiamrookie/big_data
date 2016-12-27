<?php
include(dirname(dirname(__FILE__)) . '/inc/my_session.php');
include(dirname(dirname(__FILE__)) . '/inc/model_require.php');
include(dirname(dirname(__FILE__)) . '/inc/require_file.php');
include(dirname(dirname(__FILE__)) . '/user_auth.php');
header('Content-type: text/html; charset=utf-8');

switch (Security_Util::my_get('o')) {
case 'permissionlist':
	permission_list();
	break;
case 'permissionadd':
	permission_add();
	break;
case 'permissionedit':
	permission_edit();
	break;
case 'deppermissionlist':
	dep_permission_list();
	break;
case 'deppermissionadd':
	dep_permission_add();
	break;
case 'deppermissionedit':
	dep_permission_edit();
	break;
default:
	User::no_permission();
}

function permission_list() {
	$permission = new Permission_list();
	echo $permission->get_permission_list_html();
	unset($permission);
}

function permission_add() {
	$permission = new Permission();
	echo $permission->get_permission_add_html();
	unset($permission);
}

function permission_edit() {
	$permission = new Permission(
			Validate_Util::my_is_int(Security_Util::my_get('id')) ? intval(
							Security_Util::my_get('id')) : 0);
	echo $permission->get_permission_edit_html();
	unset($permission);
}

function dep_permission_list() {
	$dep_permission = new Dep_Permission_List();
	echo $dep_permission->get_dep_permission_list_html();
	unset($dep_permission);
}

function dep_permission_add() {
	$dep_permission = new Permission_Dep();
	echo $dep_permission->get_dep_permission_add_html();
	unset($dep_permission);
}

function dep_permission_edit() {
	$dep_permission = new Permission_Dep(
			Validate_Util::my_is_int(Security_Util::my_get('id')) ? intval(
							Security_Util::my_get('id')) : 0);
	echo $dep_permission->get_dep_permission_edit_html();
	unset($dep_permission);
}