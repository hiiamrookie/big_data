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
	case 'changepwd' :
		$result = changepwd ();
		break;
	case 'user_add' :
		$result = user_do ( 'add' );
		break;
	case 'user_update' :
		$result = user_do ( 'update' );
		break;
	case 'company_add' :
		$result = company_do ( 'add' );
		break;
	case 'company_update' :
		$result = company_do ( 'update' );
		break;
	case 'dep_add' :
		$result = dep_do ( 'add' );
		break;
	case 'dep_update' :
		$result = dep_do ( 'update' );
		break;
	case 'team_add' :
		$result = team_do ( 'add' );
		break;
	case 'team_update' :
		$result = team_do ( 'update' );
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

function changepwd() {
	$user = new User ();
	$user->setOldpwd ( Security_Util::my_post ( 'oldpwd' ) );
	$user->setPwd ( Security_Util::my_post ( 'pwd' ) );
	$user->setRepwd ( Security_Util::my_post ( 'repwd' ) );
	return $user->user_do ( 'changepwd' );
}

function user_do($action) {
	$fields = array ('other_username' => Security_Util::my_post ( 'username' ), 'other_realname' => Security_Util::my_post ( 'realname' ), 'sex' => Security_Util::my_post ( 'sex' ), 'mobile' => Security_Util::my_post ( 'mobile' ), 'email' => Security_Util::my_post ( 'email' ), 'city' => Security_Util::my_post ( 'city' ), 'dep' => Security_Util::my_post ( 'dep' ), 'team' => Security_Util::my_post ( 'team' ), 'allpermissions' => Security_Util::my_checkbox_post ( 'permissions' ) );
	if ($action === 'update') {
		$fields ['other_user_id'] = Security_Util::my_post ( 'other_user_id' );
	}
	$other_user = new Other_User ( NULL, $fields );
	if (! $other_user->getHas_hr_permission ()) {
		return array ('status' => 'error', 'message' => NO_RIGHT_TO_DO_THIS );
	} else {
		switch ($action) {
			case 'add' :
				return $other_user->add_user ();
				break;
			case 'update' :
				return $other_user->update_user ();
				break;
			default :
				return FALSE;
		}
	}
}

function company_do($action) {
	$fields = array ('companyname' => Security_Util::my_post ( 'name' ) );
	if ($action === 'update') {
		$fields ['city_id'] = Security_Util::my_post ( 'city_id' );
	}
	$city = new City ( NULL, $fields );
	if (! $city->getHas_hr_permission ()) {
		return array ('status' => 'error', 'message' => NO_RIGHT_TO_DO_THIS );
	} else {
		switch ($action) {
			case 'add' :
				return $city->add_company ();
				break;
			case 'update' :
				return $city->update_company ();
				break;
			default :
				return FALSE;
		}
	}
}

function dep_do($action) {
	$fields = array ('city' => Security_Util::my_post ( 'city' ), 'depname' => Security_Util::my_post ( 'name' ), 'issupport' => Security_Util::my_post ( 'issupport' ) );
	if ($action === 'update') {
		$fields ['dep_id'] = Security_Util::my_post ( 'dep_id' );
	}
	$dep = new Dep ( NULL, $fields );
	if (! $dep->getHas_hr_permission ()) {
		return array ('status' => 'error', 'message' => NO_RIGHT_TO_DO_THIS );
	} else {
		switch ($action) {
			case 'add' :
				return $dep->add_dep ();
				break;
			case 'update' :
				return $dep->update_dep ();
				break;
			default :
				return FALSE;
		}
	}
}

function team_do($action) {
	$fields = array ('dep' => Security_Util::my_post ( 'dep' ), 'teamname' => Security_Util::my_post ( 'name' ) );
	if ($action === 'update') {
		$fields ['team_id'] = Security_Util::my_post ( 'team_id' );
	}
	$team = new Team ( NULL, $fields );
	if (! $team->getHas_hr_permission ()) {
		return array ('status' => 'error', 'message' => NO_RIGHT_TO_DO_THIS );
	} else {
		switch ($action) {
			case 'add' :
				return $team->add_team ();
				break;
			case 'update' :
				return $team->update_team ();
				break;
			default :
				return FALSE;
		}
	}
}