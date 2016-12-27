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
case 'getOutsourcingProcessView':
	echo getOutsourcingProcessView();
	break;
}

function getOutsourcingProcessView() {
	$process = Outsourcing_Process::getInstance();
	$id = Security_Util::my_post('id');
	$s = '';
	if (!empty($process[$id])) {
		if (is_null(json_decode($process[$id]['process']))) {
			$s = $process[$id]['process'];
		} else {
			$s = implode(' -&gt; ', json_decode($process[$id]['process']));
		}
	}
	return $s;
}

