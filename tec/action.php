<?php
include(dirname(dirname(__FILE__)) . '/inc/my_session.php');
include(dirname(dirname(__FILE__)) . '/inc/model_require.php');
include(dirname(dirname(__FILE__)) . '/inc/require_file.php');
include(dirname(dirname(__FILE__)) . '/user_auth.php');
header('Content-type: text/html; charset=utf-8');

//校验vcode
$vcode = $uid . User::SALT_VALUE;
include(dirname(dirname(__FILE__)) . '/validate_vcode.php');

$result = FALSE;

switch (strval(Security_Util::my_post('action'))) {
case 'project_add':
	$result = project_do('add');
	break;
case 'project_edit':
	$result = project_do('update');
	break;
case 'project_response':
	$result = project_response();
	break;
}

if ($result !== FALSE) {
	if ($result['status'] === 'error') {
		Js_Util::my_show_error_message($result['message']);
	} else if ($result['status'] === 'success') {
		Js_Util::my_show_success_message($result['message'],
				(in_array(strval(Security_Util::my_post('action')),
						array('project_add', 'project_edit','project_response'), TRUE) ? BASE_URL
								. 'tec/?o=projectlist' : NULL));
	}
} else {
	Js_Util::my_show_error_message();
}

function project_do($action) {
	//需求
	$prequirement = Security_Util::my_post('prequirement');
	$prequirement = explode(',', $prequirement);
	$prequirement_array = array();
	foreach ($prequirement as $pr) {
		if (!empty($pr)) {
			$prequirement_array[] = array(
					'requirement' => Security_Util::my_post(
							'prequirement_' . $pr));
		}
	}

	$fields = array('pid' => Security_Util::my_post('pid'),
			'project_name' => Security_Util::my_post('project_name'),
			'project_type' => Security_Util::my_post('project_type'),
			'cycle' => Security_Util::my_post('cycle'),
			'traffic' => Security_Util::my_post('traffic'),
			'dids' => Security_Util::my_post('dids'),
			'project_background' => Security_Util::my_post('project_background'),
			'prequirement_array' => $prequirement_array);
	if ($action === 'update') {
		$fields['project_id'] = Security_Util::my_post('project_id');
		$fields['tpid'] = Security_Util::my_post('tpid');
	}
	$tec = new Tec_Project($fields);
	switch ($action) {
	case 'add':
		return $tec->add_tec_project();
		break;
	case 'update':
		return $tec->update_tec_project();
		break;
	default:
		return FALSE;
	}
}

function project_response() {
	$requirementids = Security_Util::my_post('requirementids');
	$requirementids = explode(',', $requirementids);
	$requirementids_array = array();
	foreach ($requirementids as $requirementid) {
		if (!empty($requirementid)) {
			$requirementids_array[$requirementid] = array(
					'response' => Security_Util::my_post(
							'response_' . $requirementid),
					'remark' => Security_Util::my_post(
							'remark_' . $requirementid));
		}
	}
	$fields = array('id'=>Security_Util::my_post('id'),'requirementids_array' => $requirementids_array);
	$tec_response = new Tec_Project_Response($fields);
	return $tec_response->response_tec_project();
}
