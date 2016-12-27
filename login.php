<?php
include (dirname ( __FILE__ ) . '/inc/my_session.php');
include (dirname ( __FILE__ ) . '/inc/model_require.php');
include (dirname ( __FILE__ ) . '/inc/require_file.php');
header ( 'Content-type: text/html; charset=utf-8' );

$buf = file_get_contents ( TEMPLATE_PATH . 'login.tpl' );
echo str_replace ( array (
		'[BASE_URL]',
		'[VCODE]',
		'[QRCODE]' 
), array (
		BASE_URL,
		crumb::issueCrumb ( session_id () . User::SALT_VALUE ),
		(BASE_URL === 'http://oa.nimads.com/' ? '1460342434.png' : '1460342240.png') 
), $buf );
exit ();
