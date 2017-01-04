<?php
include (dirname ( dirname ( __FILE__ ) ) . '/inc/my_session.php');
include (dirname ( dirname ( __FILE__ ) ) . '/inc/model_require.php');
include (dirname ( dirname ( __FILE__ ) ) . '/inc/require_file.php');
include (dirname ( dirname ( __FILE__ ) ) . '/user_auth.php');
header ( 'Content-type: text/html; charset=utf-8' );

// 校验vcode
$vcode = $uid . User::SALT_VALUE;
include (dirname ( dirname ( __FILE__ ) ) . '/validate_vcode.php');

$result = FALSE;
switch (strval ( Security_Util::my_post ( 'action' ) )) {
	case 'data_import' :
		$result = data_import ();
		break;
}

if ($result !== FALSE) {
	if ($result ['status'] === 'error') {
		Js_Util::my_show_error_message ( $result ['message'] );
	} else if ($result ['status'] === 'success') {
		Js_Util::my_show_success_message ( $result ['message'] );
	}
} else {
	Js_Util::my_show_error_message ();
}
function data_import() {
	$final_file_path = UPLOAD_FILE_PATH . '/' . date ( 'Ym' ) . '/';
	if (! is_dir ( $final_file_path )) {
		mkdir ( $final_file_path );
	}
	$upload_result = Upload_Util::upload ( 'upfile', UPLOAD_FILE_MAX_SIZE, $final_file_path, TRUE, $GLOBALS ['defined_upload_execel_validate_type'], $GLOBALS ['defined_upload_execel_validate_mime'] );
	// var_dump($upload_result);
	if ($upload_result !== NULL) {
		$upload_result = json_decode ( $upload_result );
		if ($upload_result->status === 'error') {
			return array (
					'status' => 'error',
					'message' => $upload_result->message 
			);
		} else {
			$message = $upload_result->message;
			
			$data = new Data_Import_Export ();
			return $data->import ( UPLOAD_FILE_PATH . $message->file_name );
		}
	} else {
		return array (
				'status' => 'error',
				'message' => '必须选择文件上传' 
		);
	}
}