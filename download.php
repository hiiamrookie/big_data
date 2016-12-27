<?php
/*
require(dirname(__FILE__) . '/inc/my_session.php');
require(dirname(__FILE__) . '/inc/model_require.php');
define('BASE_URL', 'http://oa.nimads.com/');
define('DB_USER', 'root');
define('DB_PASSWORD', '');
define('DB_NAME', 'oa_new');
define('DB_HOST', 'localhost');
define('UPLOAD_FILE_FOLDER', 'pp');
define('UPLOAD_FILE_PATH', dirname(__FILE__) . '/' . UPLOAD_FILE_FOLDER);
require(dirname(__FILE__) . '/system_config/database/ezSQLcore.php');
require(dirname(__FILE__) . '/system_config/database/mysql/ezSQL_mysql.php');
require(dirname(__FILE__) . '/util/security_util.php');
require(dirname(__FILE__) . '/util/string_util.php');
require(dirname(__FILE__) . '/util/js_util.php');
require(dirname(__FILE__) . '/util/session_util.php');
require(dirname(__FILE__) . '/util/server_util.php');
require(dirname(__FILE__) . '/util/sql_util.php');
require(dirname(__FILE__) . '/lib/crumb.php');
require(dirname(__FILE__) . '/user_auth.php');
header('Content-type: text/html; charset=utf-8');
 */

include(dirname(__FILE__) . '/inc/my_session.php');
include(dirname(__FILE__) . '/inc/model_require.php');
include(dirname(__FILE__) . '/inc/require_file.php');
include(dirname(__FILE__) . '/user_auth.php');
header('Content-type: text/html; charset=utf-8');

$dao = new Dao_Impl();
$row = $dao->db
		->get_row(
				'SELECT filename FROM uploadfile WHERE id='
						. Security_Util::my_get('did'));
if ($row === NULL) {
	Js_Util::my_js_alert('文件不存在');
	exit();
}

$insert_result = $dao->db
		->query(
				'INSERT INTO downloadlog(filename,time,ip,uid) VALUES("'
						. $row->filename . '",' . $_SERVER['REQUEST_TIME']
						. ',"' . Server_Util::get_ip() . '",' . $uid . ')');

if ($insert_result === FALSE) {
	Js_Util::my_js_alert('系统内部错误，请联系系统管理员');
	exit();
}

if (String_Util::start_with($row->filename, '/')) {
	$filename = sprintf('%s%s', UPLOAD_FILE_PATH, $row->filename);
} else {
	$filename = sprintf('%s/%s', UPLOAD_FILE_PATH, $row->filename);
}

if (ini_get('zlib.output_compression')) {
	ini_set('zlib.output_compression', 'Off');
}
$ext = strtolower(end(explode('.', $filename)));
switch ($ext) {
case 'jpg':
case 'jpeg':
	$mime = ' image/jpeg';
	break;
case 'png':
	$mime = 'image/png';
	break;
case 'gif':
	$mime = 'image/gif';
	break;
case 'htm':
case 'txt':
case 'html':
	$mime = 'text/html';
	break;
default:
	$mime = 'application/force-download';
}

if (file_exists($filename)) {
	if (strpos($_SERVER['HTTP_USER_AGENT'], 'MSIE') !== FALSE) {
		header('Content-Type: ' . $mime);
		header(
				'Content-Disposition: attachment; filename="'
						. basename($filename) . '"');
		header('Expires: 0');
		header('Cache-Control: must-revalidate, post-check=0, pre-check=0');
		header('Content-Transfer-Encoding: binary');
		header('Pragma: public');
		header('Content-Length: ' . filesize($filename));
	} else {
		header('Content-Type: ' . $mime);
		header(
				'Content-Disposition: attachment; filename="'
						. basename($filename) . '"');
		header('Content-Transfer-Encoding: binary');
		header('Expires: 0');
		header('Pragma: no-cache');
		header('Content-Length: ' . filesize($filename));
	}
	ob_clean();
	flush();
	$data = readfile($filename);
	exit;
} else {
	Js_Util::my_js_alert('文件不存在');
	exit;
}
