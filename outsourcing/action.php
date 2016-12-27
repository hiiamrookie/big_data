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
$action = strval(Security_Util::my_post('action'));
switch ($action) {
case 'add_outsoourcing_type':
	$result = outsourcing_type_do('add');
	break;
case 'update_outsoourcing_type':
	$result = outsourcing_type_do('update');
	break;
case 'add_outsoourcing_process':
	$result = outsourcing_process_do('add');
	break;
case 'update_outsoourcing_process':
	$result = outsourcing_process_do('update');
	break;
case 'audit_outsourcing':
	$result = outsourcing_audit();
	break;
}

if ($result !== FALSE) {
	if ($result['status'] === 'error') {
		Js_Util::my_show_error_message($result['message']);
	} else if ($result['status'] === 'success') {
		$url = NULL;
		if ($action === 'update_outsoourcing_type') {
			$url = BASE_URL . 'outsourcing/?o=typelist';
		} else if ($action === 'update_outsoourcing_process') {
			$url = BASE_URL . 'outsourcing/?o=processlist';
		}
		Js_Util::my_show_success_message($result['message'], $url);
	}
} else {
	Js_Util::my_show_error_message();
}

function outsourcing_type_do($action) {
	$fields = array();
	if ($action === 'add' || $action === 'update') {
		$fields['outsourcing_typename'] = Security_Util::my_post(
				'outsourcing_typename');
		$fields['remark'] = Security_Util::my_post('remark');
		$fields['outsourcing_process'] = Security_Util::my_post(
				'outsourcing_process');
		$is_max_amount = Security_Util::my_post('is_max_amount');
		$max_amount = 0;
		if (!(!empty($is_max_amount) && intval($is_max_amount) === 1)) {
			$max_amount = Security_Util::my_post('max_amount');
		}
		$fields['max_amount'] = $max_amount;

		if ($action === 'update') {
			$fields['id'] = Security_Util::my_post('id');
		}
	}
	$outsourcing_type = new Outsourcing_Type($fields);
	switch ($action) {
	case 'add':
		return $outsourcing_type->addOutsourcingType();
	case 'update':
		return $outsourcing_type->updateOutsourcingType();
	}
}

function outsourcing_process_do($action) {
	$fields = array();
	if ($action === 'add' || $action === 'update') {
		$contents = Security_Util::my_post('contents');
		$contents = explode(',', $contents);
		$auditerds = array();
		foreach ($contents as $content) {
			if (!empty($content)) {
				$auditerds[] = urlencode(
						Security_Util::my_post('auditer_' . $content));
			}
		}
		$fields['outsourcing_process_name'] = Security_Util::my_post(
				'outsourcing_process_name');
		$fields['remark'] = Security_Util::my_post('remark');
		$fields['process'] = $auditerds;

		if ($action === 'update') {
			$fields['id'] = Security_Util::my_post('id');
		}
	}
	$outsourcing_process = new Outsourcing_Process($fields);
	switch ($action) {
	case 'add':
		return $outsourcing_process->addOutsourcingProcess();
	case 'update':
		return $outsourcing_process->updateOutsourcingProcess();
	}
}

function outsourcing_audit() {
	$outsourcing = new Outsourcing(
			array('audit_pass' => Security_Util::my_post('audit_pass'),
					'id' => Security_Util::my_post('id'),
					'pid' => Security_Util::my_post('pid'),
					'remark' => Security_Util::my_post('remark'),
					'executive_dep_array' => Security_Util::my_post(
							'executive_dep_array'),
					'selectdep' => Security_Util::my_checkbox_post('selectdep')));
	return $outsourcing->auditOutsourcing();
}
