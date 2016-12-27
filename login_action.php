<?php
include(dirname(__FILE__) . '/inc/my_session.php');
include(dirname(__FILE__) . '/inc/model_require.php');
include(dirname(__FILE__) . '/inc/require_file.php');
header('Content-type: text/html; charset=utf-8');

//校验vcode
$vcode = session_id() . User::SALT_VALUE;
include(dirname(__FILE__) . '/validate_vcode.php');

$user_login = new User_Login(Security_Util::my_post('username'),
		Security_Util::my_post('password'));
$result = $user_login->getResult();

if ($result['status'] === 'error') {
	Js_Util::my_show_error_message($result['message']);
} else if ($result['status'] === 'success') {
	$referer = $_SERVER['HTTP_REFERER'];
	$goto = explode('?goto=', $referer);

	if (String_Util::start_with($referer, BASE_URL . 'login.php')
			&& $goto[1] !== NULL
			&& String_Util::start_with(urldecode($goto[1]), BASE_URL)) {
		Js_Util::my_js_redirect(urldecode($goto[1]));
	} else {
		Js_Util::my_js_redirect(BASE_URL);
	}
}
