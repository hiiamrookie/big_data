<?php
include(dirname(__FILE__) . '/inc/my_session.php');
include(dirname(__FILE__) . '/inc/model_require.php');
include(dirname(__FILE__) . '/inc/require_file.php');
include(dirname(__FILE__) . '/user_auth.php');
header('Content-type: text/html; charset=utf-8');

$type = strval(Security_Util::my_get('type'));
$filename = '';
switch ($type) {
case 'receivables':
	$filename = TEMPLATE_PATH . 'receivables_template.xlsx';
	break;
case 'customer':
	$filename = TEMPLATE_PATH . 'customer_template.xlsx';
	break;
case 'receive_invoice':
	$filename = TEMPLATE_PATH . 'receive_invoice_template.xlsx';
	break;
case 'invoice':
	$filename = TEMPLATE_PATH . 'invoice_template.xlsx';
	break;
case 'supplier';
	$filename = TEMPLATE_PATH . 'supplier.xlsx';
	break;
case 'deposit_receivables':
	$filename = TEMPLATE_PATH . 'deposit_receivables_template.xlsx';
	break;
case 'deposit_invoice':
	$filename = TEMPLATE_PATH . 'deposit_invoice_template.xlsx';
	break;
case 'payment_media_statement':
	$filename = TEMPLATE_PATH . 'payment_media_statement_template.xlsx';
	break;
case 'payment_media_deposit_statement':
	$filename = TEMPLATE_PATH . 'payment_deposit_media_statement_template.xlsx';
	break;
}

if ($filename !== '') {
	if (strpos($_SERVER['HTTP_USER_AGENT'], 'MSIE') !== FALSE) {
		header(
				'Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
		header(
				'Content-Disposition: attachment; filename="'
						. basename($filename) . '"');
		header('Expires: 0');
		header('Cache-Control: must-revalidate, post-check=0, pre-check=0');
		header('Content-Transfer-Encoding: binary');
		header('Pragma: public');
		header('Content-Length: ' . filesize($filename));
	} else {
		header(
				'Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
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
	exit();
}
