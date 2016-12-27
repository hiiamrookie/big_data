<?php
$usersession = Session_Util::my_session_get('user');
if ($usersession === NULL) {
	$page_now = 'http://' . $_SERVER['HTTP_HOST'] . $_SERVER['PHP_SELF'];
	if ($_SERVER['QUERY_STRING'] !== NULL && $_SERVER['QUERY_STRING'] !== '') {
		$page_now .= '?' . $_SERVER['QUERY_STRING'];
	}

	$goto = BASE_URL . 'login.php';

	if (String_Util::start_with($page_now, BASE_URL)
			&& $page_now !== BASE_URL . 'index.php') {
		$goto .= '?goto=' . urlencode($page_now);
	}

	header('Location:' . $goto);
	exit();
} else {
	$usersession = json_decode($usersession);
	$uid = intval($usersession->uid);
	$username = $usersession->username;
}
