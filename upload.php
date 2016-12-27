<?php
include(dirname(__FILE__) . '/inc/my_session.php');
include(dirname(__FILE__) . '/inc/model_require.php');
include(dirname(__FILE__) . '/inc/require_file.php');
include(dirname(__FILE__) . '/user_auth.php');
header('Content-type: text/html; charset=utf-8');

$now_timezone = date_default_timezone_get();
if ('PRC' !== $now_timezone) {
	date_default_timezone_set('PRC');
}

$final_file_path = UPLOAD_FILE_PATH . '/' . date('Ym') . '/';
if (!is_dir($final_file_path)) {
	mkdir($final_file_path);
}

$fileid = Security_Util::my_get('fileid') !== NULL ? Security_Util::my_get(
				'fileid') : 'upfile';
$only_excel = Security_Util::my_get('only_excel');
$upload_result = Upload_Util::upload($fileid, UPLOAD_FILE_MAX_SIZE,
		$final_file_path, TRUE,
		(intval($only_excel) === 1 ? $defined_upload_execel_validate_type
				: $defined_upload_validate_type),
		(intval($only_excel) === 1 ? $defined_upload_execel_validate_mime
				: $defined_upload_validate_mime));

$str = '';
if ($upload_result !== NULL) {
	$upload_result = json_decode($upload_result);
	if ($upload_result->status === 'error') {
		$str = '0|' . $upload_result->message;
	} else {
		$message = $upload_result->message;
		$size = Format_Util::get_file_real_size($message->file_size);
		$query = 'INSERT INTO uploadfile(filename,realname,time,uid,size) VALUE("'
				. $message->file_name . '","' . $message->file_realname . '",'
				. $_SERVER['REQUEST_TIME'] . ',' . $uid . ',"' . $size . '")';

		$dao = new Dao_Impl();

		$insert_result = $dao->db->query($query);
		if ($insert_result === FALSE || $insert_result === 0) {
			//插入失败
			$str = '0|新增上传文件数据失败';
		} else {
			$str = '1|' . $dao->db->insert_id . '|' . $message->file_realname
					. '|' . $size . '|'
					. String_Util::my_md5(User::SALT_VALUE.
							$dao->db->insert_id);
		}

	}
} else {
	$str = '0|文件不能为空';
}
echo $str;
