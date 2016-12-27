<?php
include (dirname ( dirname ( __FILE__ ) ) . '/inc/my_session.php');
include (dirname ( dirname ( __FILE__ ) ) . '/inc/model_require.php');
include (dirname ( dirname ( __FILE__ ) ) . '/inc/require_file.php');
include (dirname ( dirname ( __FILE__ ) ) . '/user_auth.php');
header ( 'Content-type: text/html; charset=utf-8' );

User::no_done();
exit;

switch (Security_Util::my_get ( 'o' )) {
	case 'uploadfile' :
		uploadfile ();
		break;
}

function uploadfile() {
	$upload_file = new Upload_File ();
	$buf = file_get_contents ( TEMPLATE_PATH . 'upload/upload.tpl' );
	echo str_replace ( array ('[LEFT]', '[TOP]', '[VCODE]', '[BASE_URL]' ), array ($upload_file->get_left_html (), $upload_file->get_top_html (), $upload_file->get_vcode (), BASE_URL ), $buf );
}