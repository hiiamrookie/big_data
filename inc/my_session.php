<?php
//HTTPOnly设置
if ('1' !== ini_get('session.cookie_httponly')) {
	ini_set('session.cookie_httponly', '1');
}
//只使用cookie
if ('1' !== ini_get('session.use_only_cookies')) {
	ini_set('session.use_only_cookies', '1');
}
//不使用URL传递session_id
if ('0' !== ini_get('session.use_trans_sid')) {
	ini_set('session.use_trans_sid', '0');
}
session_start();
if (!defined('ua_seed')) {
	define('ua_seed', '@ranranba');
}
$hua = isset($_SERVER['HTTP_USER_AGENT']) ? $_SERVER['HTTP_USER_AGENT'] : 'no_http_user_agent';
if (!isset($_SESSION['user_agent'])) {
	$_SESSION['user_agent'] = md5(
			$hua . ua_seed . session_id());
} else {
	if ($_SESSION['user_agent']
			!== md5($hua . ua_seed . session_id())) {
				exit();
			}
}

//删除原来的cookie漏洞源头
if (isset($_COOKIE['uid'])) {
	setcookie('uid', '', time() - 3600, '/');
}

//anti cc
include(dirname(__FILE__) . '/anti_cc.php');