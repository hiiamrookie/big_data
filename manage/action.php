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
case 'process_add':
	$result = process_do('add');
	break;
case 'process_update':
	$result = process_do('update');
	break;
case 'dep_process_add':
	$result = dep_process_do('add');
	break;
case 'dep_process_update':
	$result = dep_process_do('update');
	break;
case 'customer_add':
	$result = customer_do('add');
	break;
case 'customer_update':
	$result = customer_do('update');
	break;
case 'customer_relate':
	$result = customer_do('relate');
	break;
case 'customer_import':
	$result = customer_import();
	break;
}

if ($result !== FALSE) {
	if ($result['status'] === 'error') {
		Js_Util::my_show_error_message($result['message']);
	} else if ($result['status'] === 'success') {
		Js_Util::my_show_success_message($result['message']);
	}
} else {
	Js_Util::my_show_error_message();
}

function process_do($action) {
	$contents = Security_Util::my_post('contents');
	$contents = explode(',', $contents);
	$content_val = array();
	foreach ($contents as $content) {
		$pname = Security_Util::my_post('pname_' . $content);
		$pcontent = Security_Util::my_post('content_' . $content);
		if (!empty($pname) && !empty($pcontent)) {
			$content_val[] = $pname . '^' . $pcontent;
		}
	}

	$fields = array('module' => Security_Util::my_post('module'),
			'name' => Security_Util::my_post('name'),
			'des' => Security_Util::my_post('des'),
			'deps_val' => Security_Util::my_checkbox_post('deps'),
			'content_val' => $content_val);
	if ($action === 'update') {
		$fields['process_id'] = Security_Util::my_post('process_id');
	}
	$process = new Process(NULL, $fields);
	if (!$process->getHas_process_permission()) {
		return array('status' => 'error', 'message' => NO_RIGHT_TO_DO_THIS);
	} else {
		switch ($action) {
		case 'add':
			return $process->add_process();
			break;
		case 'update':
			return $process->update_process();
			break;
		default:
			return FALSE;
		}
	}
}

function dep_process_do($action) {
	$contents = Security_Util::my_post('contents');
	$contents = explode(',', $contents);
	$content_val = array();
	foreach ($contents as $content) {
		$pname = Security_Util::my_post('pname_' . $content);
		$pcontent = Security_Util::my_post('content_' . $content);
		if (!empty($pname) && !empty($pcontent)) {
			$content_val[] = $pname . '^' . $pcontent;
		}
	}
	$fields = array('name' => Security_Util::my_post('name'),
			'des' => Security_Util::my_post('des'),
			'dep' => Security_Util::my_post('dep'),
			'content_val' => $content_val);
	if ($action === 'update') {
		$fields['process_id'] = Security_Util::my_post('process_id');
	}
	$process = new Dep_Process(NULL, $fields);
	if (!$process->getHas_process_permission()) {
		return array('status' => 'error', 'message' => NO_RIGHT_TO_DO_THIS);
	} else {
		switch ($action) {
		case 'add':
			return $process->add_dep_process();
			break;
		case 'update':
			return $process->update_dep_process();
			break;
		default:
			return FALSE;
		}
	}
}

function customer_do($action) {
	if ($action !== 'relate') {
		$fields = array(
				'customer_name' => Security_Util::my_post('customer_name'),
				'safety' => Security_Util::my_post('safety'),
				'tmpsafety' => Security_Util::my_post('tmpsafety'),
				'tmpsafety_deadline' => Security_Util::my_post(
						'tmpsafety_deadline'));
	} else {
		$fields = array(
				'cusnames_array' => Security_Util::my_checkbox_post('cusname'));
	}

	if (in_array($action, array('update', 'relate'), TRUE)) {
		$fields['customer_id'] = Security_Util::my_post('customer_id');
	}
	$customer = new Customer($fields);
	if (!$customer->getHas_manager_customer_safety_permission()) {
		return array('status' => 'error', 'message' => NO_RIGHT_TO_DO_THIS);
	} else {
		switch ($action) {
		case 'add':
			return $customer->add_customer();
			break;
		case 'update':
			return $customer->update_customer();
			break;
		case 'relate':
			return $customer->relate_customer();
			break;
		default:
			return FALSE;
		}
	}
}

function customer_import() {
	$final_file_path = UPLOAD_FILE_PATH . '/' . date('Ym') . '/';
	if (!is_dir($final_file_path)) {
		mkdir($final_file_path);
	}
	$upload_result = Upload_Util::upload('upfile', UPLOAD_FILE_MAX_SIZE,
			$final_file_path, TRUE,
			$GLOBALS['defined_upload_execel_validate_type'],
			$GLOBALS['defined_upload_execel_validate_mime']);
	//var_dump($upload_result);
	if ($upload_result !== NULL) {
		$upload_result = json_decode($upload_result);
		if ($upload_result->status === 'error') {
			return array('status' => 'error',
					'message' => $upload_result->message);
		} else {
			$message = $upload_result->message;
			$customer = new Customer();
			return $customer
					->import_customer(UPLOAD_FILE_PATH . $message->file_name);
		}
	} else {
		return array('status' => 'error', 'message' => '必须选择文件上传');
	}
}
