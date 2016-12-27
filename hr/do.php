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
	case 'cancel_user' :
		echo cancel_user ();
		break;
	case 'change_pwd' :
		echo change_pwd ();
		break;
	case 'del_dep' :
		echo del_dep ();
		break;
	case 'del_team' :
		echo del_team ();
		break;
}

function cancel_user() {
	$other_user = new Other_User ();
	if ($other_user->getHas_hr_permission ()) {
		$other_user->setOther_user_id ( Security_Util::my_post ( 'uid' ) );
		$result = $other_user->cancel_user ();
		return $result ['message'];
	} else {
		return NO_RIGHT_TO_DO_THIS;
	}
}

function change_pwd() {
	$other_user = new Other_User ();
	if ($other_user->getHas_hr_permission ()) {
		$other_user->setOther_user_id ( Security_Util::my_post ( 'uid' ) );
		$result = $other_user->change_pwd ();
		return $result ['message'];
	} else {
		return NO_RIGHT_TO_DO_THIS;
	}
}

function del_dep() {
	$dep = new Dep ( Security_Util::my_post ( 'id' ) );
	if ($dep->getHas_hr_permission ()) {
		$result = $dep->del_dep ();
		return $result ['message'];
	} else {
		return NO_RIGHT_TO_DO_THIS;
	}
}

function del_team() {
	$team = new Team ( Security_Util::my_post ( 'id' ) );
	if ($team->getHas_hr_permission ()) {
		$result = $team->del_team ();
		return $result ['message'];
	} else {
		return NO_RIGHT_TO_DO_THIS;
	}
}