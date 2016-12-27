<?php
include(dirname(dirname(__FILE__)) . '/inc/my_session.php');
include(dirname(dirname(__FILE__)) . '/inc/model_require.php');
include(dirname(dirname(__FILE__)) . '/inc/require_file.php');
include(dirname(__FILE__) . '/auth.php');
header('Content-type: text/html; charset=utf-8');

$action = Security_Util::my_request('action');

switch ($action) {
case 'pending':
	pending($token);
	break;
case 'getAuditInfo':
	getAuditInfo($token);
	break;
case 'audit':
	audit($token);
	break;
default:
	default_return();
}

//默认返回
function default_return() {
	echo urldecode(
			json_encode(
					array('status' => 'error',
							'message' => urlencode(NO_RIGHT_TO_DO_THIS))));
}

//待办事项
function pending($token) {
	$user = new User_Index(TRUE, $token);
	echo $user->getMobilePendingExecutive();
}

//待审核执行单详情
function getAuditInfo($token) {
	$fields = array('pid' => Security_Util::my_request('pid'),
			'executive_id' => Security_Util::my_request('id'));
	$dep = Security_Util::my_request('dep');
	if (!empty($dep)) {
		$fields['dep'] = $dep;
	}
	$exe = new Executive(NULL, $fields, TRUE, $token);
	echo $exe->getMobileAuditExecutive();
}

//审核执行单
function audit($token) {
	$fields = array(
			'executive_id' => Security_Util::my_request('executive_id'),
			'pid' => Security_Util::my_request('pid'),
			'audit_pass' => Security_Util::my_request('audit_pass'));
	$remark = Security_Util::my_request('remark');
	$dep = Security_Util::my_request('dep');
	$rejectstep = Security_Util::my_request('rejectstep');
	$rejectdepids = $_REQUEST['rejectdep'];
	if (!empty($remark)) {
		$fields['remark'] = $remark;
	}
	if (!empty($dep)) {
		$fields['dep'] = $dep;
	}
	if (!empty($rejectstep)) {
		$fields['rejectstep'] = $rejectstep;
	}
	if (!empty($rejectdepids)) {
		$fields['rejectdepids'] = explode(',', $rejectdepids);
	}
	
	$executive = new Executive(NULL, $fields, TRUE, $token);
	if (!empty($dep)) {
		//部门审核
		$re = $executive->dep_audit_executive(TRUE);
	} else {
		$re = $executive->audit_executive();
	}

	echo urldecode(
			json_encode(
					array('status' => $re['status'],
							'message' => is_array($re['message']) ? urlencode(implode(',', $re['message'])) : urlencode($re['message']),
							'token' => $token,
							'executive_id' => Security_Util::my_request(
									'executive_id'),
							'pid' => Security_Util::my_request('pid'),
							'audit_pass' => Security_Util::my_request(
									'audit_pass'),
							'remark' => urlencode(
									Security_Util::my_request('remark')),
							'dep' => Security_Util::my_request('dep'),
							'rejectstep' => Security_Util::my_request(
									'rejectstep'),
							'rejectdepids' => $_REQUEST[
									'rejectdep'])));
}
