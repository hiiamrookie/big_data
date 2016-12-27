<?php
include(dirname(dirname(__FILE__)) . '/inc/my_session.php');
include(dirname(dirname(__FILE__)) . '/inc/model_require.php');
include(dirname(dirname(__FILE__)) . '/inc/require_file.php');
header('Content-type: text/html; charset=utf-8');

$user_login = new User_Login(Security_Util::my_request('username'),
		Security_Util::my_request('password'), TRUE);
$result = $user_login->getResult();
echo urldecode(
		json_encode(
				array('status' => $result['status'],
						'message' => urlencode($result['message']),'userid'=>$result['userid'],
						'token' => $result['token'])));
