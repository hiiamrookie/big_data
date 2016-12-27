<?php
//var_dump($vcode);
//var_dump(Security_Util::my_post('vcode'));
if (!crumb::verifyCrumb($vcode, Security_Util::my_post('vcode'))) {
	header('Content-type: text/html; charset=utf-8');
	$back_url = $_SERVER['HTTP_REFERER'];
	if (empty($back_url)) {
		$back_url = BASE_URL;
	}
	if ($is_ajax) {
		echo INVALIDATION_VISIT;
	} else {
		Js_Util::my_js_alert(INVALIDATION_VISIT, $back_url);
	}
	exit();
}
