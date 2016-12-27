<?php
include(dirname(dirname(__FILE__)) . '/inc/my_session.php');
include(dirname(dirname(__FILE__)) . '/inc/model_require.php');
include(dirname(dirname(__FILE__)) . '/inc/require_file.php');
header('Content-type: text/html; charset=utf-8');

$isWechat = isWechat();
$device = device();

if ($isWechat) {
	echo '<script>';
	echo 'alert("微信内无法下载，请使用其他浏览器下载");';
	echo '</script>';
	//echo '微信内无法下载，请使用其他浏览器下载';
	exit;
} else {
	switch ($device) {
	case 'IOS':
		echo '<script>';
		echo 'alert("敬请期待");';
		echo '</script>';
		//echo '敬请期待';
		break;
	case 'Android':
		echo '<script>';
		echo 'location.href="' . BASE_URL . 'app/nimOA.apk";';
		echo '</script>';
		break;
	default:
		echo '<script>';
		echo 'alert("仅提供安卓版本和IOS版本");';
		echo '</script>';
		//echo '仅提供安卓版本和IOS版本';
		break;
	}
}

function isWechat() {
	$user_agent = isset($_SERVER['HTTP_USER_AGENT']) ? $_SERVER['HTTP_USER_AGENT']
			: '';
	if (strpos(strtolower($user_agent), 'micromessenger') === FALSE) {
		return FALSE;
	}
	return TRUE;
}

function device() {
	$hua = strtolower($_SERVER['HTTP_USER_AGENT']);
	if (strpos($hua, 'iphone') || strpos($hua, 'ipad')) {
		return 'IOS';
	} else if (strpos($hua, 'android')) {
		return 'Android';
	} else {
		return 'unknow';
	}
}
