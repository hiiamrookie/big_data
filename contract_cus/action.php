<?php
include (dirname ( dirname ( __FILE__ ) ) . '/inc/my_session.php');
include (dirname ( dirname ( __FILE__ ) ) . '/inc/model_require.php');
include (dirname ( dirname ( __FILE__ ) ) . '/inc/require_file.php');
include (dirname ( dirname ( __FILE__ ) ) . '/user_auth.php');
header ( 'Content-type: text/html; charset=utf-8' );

$vcode = $uid . User::SALT_VALUE;
include (dirname ( dirname ( __FILE__ ) ) . '/validate_vcode.php');

$result = FALSE;
$action = strval ( Security_Util::my_post ( 'action' ) );
switch ($action) {
	case 'contract_add' :
		$result = contract_do ( 'add' );
		break;
	case 'contract_manage' :
		$result = contract_manage ();
		break;
	case 'contract_update' :
		$result = contract_do ( 'update' );
		break;
	case 'contract_audit' :
		$result = contract_do ( 'audit' );
		break;
}

if ($result !== FALSE) {
	if ($result ['status'] === 'error') {
		Js_Util::my_show_error_message ( $result ['message'] );
	} else if ($result ['status'] === 'success') {
		$url = $_SERVER ['HTTP_REFERER'];
		if ($action === 'contract_audit') {
			$url = BASE_URL;
		} else if (in_array ( $action, array ('contract_add', 'contract_update' ), TRUE )) {
			$url = BASE_URL . 'contract_cus/?o=list';
		}
		Js_Util::my_show_success_message ( $result ['message'], $url );
	}
} else {
	Js_Util::my_show_error_message ();
}

function contract_do($action) {
	if ($action === 'audit') {
		$fields = array ('audit_pass' => Security_Util::my_post ( 'audit_pass' ), 'remark' => Security_Util::my_post ( 'remark' ) );
	} else {
		$fields = array ('type' => Security_Util::my_post ( 'type' ), 'contractname' => Security_Util::my_post ( 'name' ), 'cusname' => Security_Util::my_post ( 'cusname' ), 'cuscontact' => Security_Util::my_post ( 'cuscontact' ), 'execution' => Security_Util::my_post ( 'execution' ), 'city' => Security_Util::my_post ( 'city' ), 'dep' => Security_Util::my_post ( 'dep' ), 'team' => Security_Util::my_post ( 'team' ), 'contactperson' => Security_Util::my_post ( 'contactperson' ), 'contactcontent' => Security_Util::my_post ( 'contactcontent' ), 'signdate' => Security_Util::my_post ( 'signdate' ), 'starttime' => Security_Util::my_post ( 'starttime' ), 'endtime' => Security_Util::my_post ( 'endtime' ), 'monitoringsystem' => Security_Util::my_post ( 'monitoringsystem' ), 'contractamount' => Security_Util::my_post ( 'contractamount' ), 'billtype' => Security_Util::my_post ( 'billtype' ), 'rebateproportion' => Security_Util::my_post ( 'rebateproportion' ), 'bzjpaymentmethod' => Security_Util::my_post ( 'bzjpaymentmethod' ), 'contractamountpayment' => Security_Util::my_post ( 'contractamountpayment' ), 'specialcaluse' => Security_Util::my_post ( 'specialcaluse' ), 'remark' => Security_Util::my_post ( 'remark' ), 'dids' => Security_Util::my_post ( 'dids' ), 'contractstatus' => Security_Util::my_post ( 'contractstatus' ), 'contractstatusreason' => Security_Util::my_post ( 'contractstatusreason' ), 'process' => Security_Util::my_post ( 'process' ), 'customertype' => Security_Util::my_post ( 'customertype' ) );
		if (intval ( Security_Util::my_post ( 'type' ) ) === 2 && Security_Util::my_post ( 'isfmkcid' )) {
			$fields ['isfmkcid'] = TRUE;
			$fields ['fmkcid'] = Security_Util::my_post ( 'fmkcid' );
		}
		
		//立项
		$fields ['project_id'] = Security_Util::my_post ( 'project_id' );
		
		//直客 / 代理商
		$type1 = Security_Util::my_post ( 'type1' );
		$fields ['type1'] = $type1;
		if (intval ( $type1 ) === 2) {
			$dailicount = Security_Util::my_post ( 'dailicount' );
			$dailicount = explode ( ',', $dailicount );
			$daili_array = array ();
			foreach ( $dailicount as $dl ) {
				if (! empty ( $dl )) {
					$daili_array [] = array ('daili' => Security_Util::my_post ( 'dailishang_' . $dl ), 'ggz' => Security_Util::my_post ( 'guanggaozhu_' . $dl ) );
				}
			}
			if(!empty($daili_array)){
				$fields ['daili_array'] = $daili_array;
			}	
		}
		
		//合同金额拆分	媒体投放
		$mediatfcount = Security_Util::my_post ( 'mediatfcount' );
		$mediatfcount = explode ( ',', $mediatfcount );
		$media_tf_array = array ();
		foreach ( $mediatfcount as $mc ) {
			if (! empty ( $mc )) {
				$media_tf_array [] = array ('media' => Security_Util::my_post ( 'media_' . $mc ), 'amount' => Security_Util::my_post ( 'mediaamount_' . $mc ), 'advformat' => Security_Util::my_post ( 'advformat_' . $mc ) );
			}
		}
		$fields ['media_tf_array'] = $media_tf_array;
		
		//服务内容
		$servicecount = Security_Util::my_post ( 'servicecount' );
		$servicecount = explode ( ',', $servicecount );
		$service_array = array ();
		foreach ( $servicecount as $sc ) {
			if (! empty ( $sc )) {
				$service_array [] = array ('cftype' => Security_Util::my_post ( 'cftype_' . $sc ), 'serviceamount' => Security_Util::my_post ( 'serviceamount_' . $sc ) );
			}
		}
		$fields ['service_array'] = $service_array;
		
		//保证金
		$baozhengjincount = Security_Util::my_post ( 'baozhengjincount' );
		$baozhengjincount = explode ( ',', $baozhengjincount );
		$bzj_array = array ();
		foreach ( $baozhengjincount as $bc ) {
			if (! empty ( $bc )) {
				$bzj_array [] = array ('media' => Security_Util::my_post ( 'bzjname_' . $bc ), 'bl' => Security_Util::my_post ( 'bzjbl_' . $bc ), 'amount' => Security_Util::my_post ( 'bzjamount_' . $bc ) );
			}
		}
		$fields ['bzj_array'] = $bzj_array;
	}
	
	if($action === 'add' || $action === 'update'){
		$fields['customer_id'] = Security_Util::my_post('customer');
	}
	
	if ($action === 'update' || $action === 'audit') {
		$fields ['cid'] = Security_Util::my_post ( 'cid' );
	}
	
	$contract = new Contract ( $fields );
	switch ($action) {
		case 'add' :
			return $contract->add_contract ();
			break;
		case 'update' :
			return $contract->update_contract ();
			break;
		case 'audit' :
			return $contract->audit_contract ();
			break;
	}
}

function contract_manage() {
	$fields = array ('cids' => Security_Util::my_checkbox_post ( 'cids' ), 'userlist' => Security_Util::my_post ( 'userlist' ) );
	$contract_list = new Contract_List ( $fields );
	return $contract_list->manage_contract ();
}