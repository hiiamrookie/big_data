<?php
$token = Security_Util::my_request('token');

$token_auth = FALSE;

if (!empty($token)) {
	$dao = new Dao_Impl();
	$time_row = $dao->db
			->get_row(
					'SELECT time FROM mobile_login WHERE token="' . $token
							. '"');
	if ($time_row !== NULL) {
		if (time() - $time_row->time <= 3600) {
			//正常
			$token_auth = TRUE;
		}
	}
	$dao->db->disconnect();
	unset($dao);
}

if (!$token_auth) {
	echo urldecode(
			json_encode(
					array('status' => 'error', 'message' => urlencode('请重新登录'))));
	exit;
}
