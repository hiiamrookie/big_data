<?php
header('Content-type: text/html; charset=utf-8');
//$uri = "http://localhost/oa_epsilon/mobile/login.php";
//$uri = 'http://oatest.nimads.com/mobile/executive.php?action=pending&token=79071ea781f9ca7ef8be0fe88187a4f0';
$uri = "http://localhost/oa_test/mobile/executive.php?action=getAuditInfo&id=22006&pid=15SH010001-085&token=d22f83ba39fc1da22893a69bd787d7b0";
//$uri = "http://oatest.nimads.com/mobile/executive.php";
// 参数数组



/*
$data = array(
	'action'=>'audit',
	'token'=>'79071ea781f9ca7ef8be0fe88187a4f0',
	'executive_id'=>17016,
	'pid'=>'15SH010001-006',
	'audit_pass'=>0,
	'remark'=>'重新测试fghgf',
	//'rejectstep'=>'0',
	//'rejectdep'=>array(4,8),
);
*/
$data = array();


$ch = curl_init ();
// print_r($ch);
curl_setopt ( $ch, CURLOPT_URL, $uri );
curl_setopt ( $ch, CURLOPT_POST, 1 );
curl_setopt ( $ch, CURLOPT_HEADER, 0 );
curl_setopt ( $ch, CURLOPT_RETURNTRANSFER, 1 );
curl_setopt ( $ch, CURLOPT_POSTFIELDS, http_build_query($data) );
$return = curl_exec ( $ch );
curl_close ( $ch );
 
print_r($return);