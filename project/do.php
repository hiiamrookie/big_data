<?php
include(dirname(dirname(__FILE__)) . '/inc/my_session.php');
include(dirname(dirname(__FILE__)) . '/inc/model_require.php');
include(dirname(dirname(__FILE__)) . '/inc/require_file.php');
include(dirname(dirname(__FILE__)) . '/user_auth.php');
header('Content-type: text/html; charset=utf-8');

//校验vcode
$vcode = $uid . User::SALT_VALUE;
$is_ajax = TRUE;
include(dirname(dirname(__FILE__)) . '/validate_vcode.php');

switch (strval(Security_Util::my_post('action'))) {
case 'cancel':
	echo cancel_project();
	break;
}

function cancel_project() {
	$p = new Project(array('id'=>Security_Util::my_post('id')));
	$result = $p->getCancelProjectResult();
	return $result['status'] === 'success' ? 1 : $result['message'];
}
