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
	case 'executive_add' :
		$result = executive_do ( 'add' );
		break;
	case 'executive_audit' :
		$result = executive_do ( 'audit' );
		break;
	case 'executive_dep_edit' :
		$result = executive_do ( 'dep_edit' );
		break;
	case 'executive_dep_audit' :
		$result = executive_do ( 'dep_audit' );
		break;
	case 'executive_alter' :
		$result = executive_do ( 'alter' );
		break;
	case 'executive_update' :
		$result = executive_do ( 'update' );
		break;
	case 'executive_userchange' :
		$result = executive_do ( 'userchange' );
		break;
	case 'executive_userchange_all' :
		$result = executive_do ( 'userchange_all' );
		break;
	case 'executive_cy' :
		$result = executive_cy ();
		break;
	case 'executive_alter_cy' :
		$result = executive_cy ( TRUE );
		break;
	case 'executive_complete_cy' :
		$result = executive_complete_cy ();
		break;
	case 'media_schedule_import' :
		$result = media_schedule_import ();
		break;
}

if ($result !== FALSE) {
	if ($result ['status'] === 'error') {
		Js_Util::my_show_error_message ( $result ['message'] );
	} else if ($result ['status'] === 'success') {
		if (strval ( Security_Util::my_post ( 'action' ) ) === 'executive_alter') {
			Js_Util::my_show_success_message ( $result ['message'], BASE_URL . 'executive/?o=mylist' );
		} else if (in_array ( Security_Util::my_post ( 'action' ), array (
				'executive_userchange',
				'executive_userchange_all' 
		), TRUE )) {
			Js_Util::my_show_success_message ( $result ['message'], BASE_URL . 'executive/?o=userchange' );
		} else {
			Js_Util::my_show_success_message ( $result ['message'], (in_array ( strval ( Security_Util::my_post ( 'action' ) ), array (
					'executive_audit',
					'executive_dep_edit',
					'executive_dep_audit',
					'executive_update',
					'executive_complete_cy' 
			), TRUE ) ? BASE_URL : NULL) );
		}
	}
} else {
	Js_Util::my_show_error_message ();
}
function executive_cy($exe_isalter = FALSE) {
	$isaltershow = intval ( Security_Util::my_post ( 'isaltershow' ) );
	
	$copycostpaycount = Security_Util::my_post ( 'copycostpaycount' );
	$copycostpaycount = explode ( ',', $copycostpaycount );
	$all = array ();
	$dates = '';
	
	$hiddenstarttime = Security_Util::my_post ( 'hiddenstarttime' );
	$hiddenendtime = Security_Util::my_post ( 'hiddenendtime' );
	if (empty ( $hiddenstarttime ) || empty ( $hiddenendtime )) {
		echo '<script>';
		echo 'parent.show_message("请先输入“项目实际执行日期”");';
		echo '</script>';
		exit ();
	}
	
	$start = date ( 'Y-m', strtotime ( $hiddenstarttime ) );
	$end = date ( 'Y-m', strtotime ( $hiddenendtime ) );
	
	foreach ( $copycostpaycount as $ccc ) {
		if (! empty ( $ccc )) {
			$cycount = Security_Util::my_post ( 'cycount_' . $ccc );
			$cycount = explode ( ',', $cycount );
			$cyitem = array ();
			$dateError = FALSE;
			$dateDuplicate = FALSE;
			$d = array ();
			$cost = 0;
			$quote = 0;
			foreach ( $cycount as $cy ) {
				if (! empty ( $cy )) {
					$date = Security_Util::my_post ( 'cyym_' . $ccc . '_' . $cy );
					if ($isaltershow === 0 && ! $exe_isalter && (strtotime ( $date ) < strtotime ( $start ) || strtotime ( $date ) > strtotime ( $end ))) {
						$dateError = TRUE;
						break;
					}
					
					if (in_array ( $date, $d, TRUE )) {
						$dateDuplicate = TRUE;
						break;
					} else {
						$d [] = $date;
					}
					
					$cyitem [$ccc] [] = array (
							'date' => Security_Util::my_post ( 'cyym_' . $ccc . '_' . $cy ),
							'cost' => Security_Util::my_post ( 'cost_amount_' . $ccc . '_' . $cy ) 
					);
					$cost += Security_Util::my_post ( 'cost_amount_' . $ccc . '_' . $cy );
				}
			}
			
			if ($dateError) {
				echo '<script>';
				echo 'parent.show_message("项目执行日期为' . $start . '至' . $end . '，拆月数据必须在该日期范围内");';
				echo '</script>';
				exit ();
			} else if ($dateDuplicate) {
				echo '<script>';
				echo 'parent.show_message("一个供应商中拆月日期有重复");';
				echo '</script>';
				exit ();
			}
			
			$all [$ccc] = array (
					'supplier' => Security_Util::my_post ( 'supplier_' . $ccc ),
					'type' => Security_Util::my_post ( 'deliverytype_' . $ccc ),
					'short_id' => Security_Util::my_post ( 'supplier_short_' . $ccc ),
					'items' => $cyitem,
					'cost' => $cost,
					'industry_id' => Security_Util::my_post ( 'industry_' . $ccc ) 
			);
		}
	}
	
	if (count ( $all ) === 0) {
		$all = '';
	} else {
		$all = base64_encode ( json_encode ( $all ) );
	}
	echo '<script>';
	echo 'parent.cy_json("' . $all . '");';
	echo 'parent.close_pop();';
	echo '</script>';
}
function executive_do($action) {
	if ($action === 'audit') {
		$fields = array (
				'audit_pass' => Security_Util::my_post ( 'audit_pass' ),
				'remark' => Security_Util::my_post ( 'remark' ),
				'rejectstep' => Security_Util::my_post ( 'rejectstep' ),
				'rejectdepids' => Security_Util::my_checkbox_post ( 'rejectdep' ) 
		);
	} else {
		if (! in_array ( $action, array (
				'dep_edit',
				'dep_audit',
				'userchange',
				'userchange_all' 
		), TRUE )) {
			// 合同约定付款时间
			$paycount = Security_Util::my_post ( 'paycount' );
			$paycount = explode ( ',', $paycount );
			$paycount_array = array ();
			foreach ( $paycount as $pc ) {
				if (! empty ( $pc )) {
					$paycount_array [] = array (
							'time' => Security_Util::my_post ( 'paytime_' . $pc ),
							'amount' => Security_Util::my_post ( 'payamount_' . $pc ),
							'remark' => Security_Util::my_post ( 'payremark_' . $pc ) 
					);
				}
			}
			
			// 执行金额拆月
			$cy_amount = Security_Util::my_post ( 'cy_amount' );
			$cy_amount = explode ( ',', $cy_amount );
			$cy_amount_array = array ();
			foreach ( $cy_amount as $cya ) {
				if (! empty ( $cya )) {
					$cy_amount_array [] = array (
							'time' => Security_Util::my_post ( 'amountcytime_' . $cya ),
							'amount' => Security_Util::my_post ( 'amountcy_' . $cya ) 
					);
				}
			}
		}
		
		if (! in_array ( $action, array (
				'dep_audit',
				'userchange',
				'userchange_all' 
		), TRUE )) {
			// 服务金额拆分
			$servicecf = Security_Util::my_post ( 'servicecf' );
			$servicecf = explode ( ',', $servicecf );
			$servicecf_array = array ();
			foreach ( $servicecf as $scf ) {
				if (! empty ( $scf )) {
					$servicecf_array [] = array (
							'type' => Security_Util::my_post ( 'servercf_type_' . $scf ),
							'amount' => Security_Util::my_post ( 'servercf_amount_' . $scf ),
							'remark' => Security_Util::my_post ( 'servercf_remark_' . $scf ) 
					);
				}
			}
			
			// 成本明细
			$costcount = Security_Util::my_post ( 'costcount' );
			$costcount = explode ( ',', $costcount );
			$costcount_array = array ();
			foreach ( $costcount as $cc ) {
				if (! empty ( $cc )) {
					$costcount_array [] = array (
							'type' => Security_Util::my_post ( 'costtype_' . $cc ),
							'amount' => Security_Util::my_post ( 'costamount_' . $cc ),
							'name' => Security_Util::my_post ( 'costname_' . $cc ),
							'yg' => Security_Util::my_post ( 'costyg_' . $cc ) 
					);
				}
			}
			
			// 成本支付明细
			$costpaycount = Security_Util::my_post ( 'costpaycount' );
			$costpaycount = explode ( ',', $costpaycount );
			$costpaycount_array = array ();
			
			$costpaycount_category_array = array ();
			
			foreach ( $costpaycount as $cpc ) {
				if (! empty ( $cpc )) {
					// $costpaycount_array[] = array(
					$costpaycount_array [$cpc] = array (
							'name' => Security_Util::my_post ( 'costpay_' . $cpc ),
							'time' => Security_Util::my_post ( 'costpaytime_' . $cpc ),
							'amount' => Security_Util::my_post ( 'costpayamount_' . $cpc ),
							'type' => Security_Util::my_post ( 'costpaytype_' . $cpc ) 
					);
				}
			}
		}
		
		if ($action === 'dep_edit') {
			$fields = array (
					'actor' => Security_Util::my_post ( 'actor' ),
					'dids' => Security_Util::my_post ( 'dids' ),
					'process' => Security_Util::my_post ( 'process' ),
					'remark' => Security_Util::my_post ( 'edit_remark' ),
					'costcount_array' => $costcount_array,
					'costpaycount_array' => $costpaycount_array,
					'dep' => Security_Util::my_post ( 'dep' ),
					'cy_json' => Security_Util::my_post ( 'cy_json' ),
					'outsourcing_type' => Security_Util::my_post ( 'outsourcing_type' ) 
			) // 执行单外包类型
;
		} else if ($action === 'dep_audit') {
			$fields = array (
					'dep' => Security_Util::my_post ( 'dep' ),
					'audit_pass' => Security_Util::my_post ( 'audit_pass' ),
					'remark' => Security_Util::my_post ( 'remark' ) 
			);
		} else if ($action === 'userchange') {
			$fields = array (
					'user' => Security_Util::my_post ( 'user' ),
					'principal' => Security_Util::my_post ( 'principal' ) 
			);
		} else if ($action === 'userchange_all') {
			$fields = array (
					'user' => Security_Util::my_post ( 'type1users' ),
					'principal' => Security_Util::my_post ( 'type1principal' ) 
			);
		} else {
			$fields = array (
					'exetype' => Security_Util::my_post ( 'exetype' ),
					'cid' => Security_Util::my_post ( 'cid' ),
					'execompany' => Security_Util::my_post ( 'execompany' ),
					'projectname' => Security_Util::my_post ( 'projectname' ),
					'dids' => Security_Util::my_post ( 'dids' ),
					'principal' => Security_Util::my_post ( 'principal' ),
					'actor' => Security_Util::my_post ( 'actor' ),
					'starttime' => Security_Util::my_post ( 'starttime' ),
					'endtime' => Security_Util::my_post ( 'endtime' ),
					'support_array' => Security_Util::my_checkbox_post ( 'support' ),
					'process' => Security_Util::my_post ( 'process' ),
					'remark' => Security_Util::my_post ( 'remark' ),
					'paycount_array' => $paycount_array,
					'costcount_array' => $costcount_array,
					'costpaycount_array' => $costpaycount_array,
					'servercf_array' => $servicecf_array,
					'altersupport_array' => Security_Util::my_checkbox_post ( 'altersupport' ),
					'cy_json' => Security_Util::my_post ( 'cy_json' ),
					'is2agent' => Security_Util::my_post ( 'is2agent' ),
					'agentcusname' => Security_Util::my_post ( 'agentcusname' ),
					'customertype' => Security_Util::my_post ( 'customertype' ),
					'cy_amount_array' => $cy_amount_array 
			);
		}
	}
	
	if ($action === 'update' || $action === 'audit' || $action === 'dep_audit' || $action === 'dep_edit') {
		$fields ['executive_id'] = Security_Util::my_post ( 'executive_id' );
	}
	
	if (in_array ( $action, array (
			'audit',
			'dep_edit',
			'dep_audit',
			'alter',
			'update',
			'userchange',
			'userchange_all' 
	), TRUE )) {
		$fields ['pid'] = Security_Util::my_post ( 'pid' );
	}
	
	$executive = new Executive ( NULL, $fields );
	switch ($action) {
		case 'add' :
			return $executive->add_executive ();
			break;
		case 'update' :
			return $executive->update_executive ();
			break;
		case 'audit' :
			return $executive->audit_executive ();
			break;
		case 'dep_edit' :
			return $executive->dep_edit_executive ();
			break;
		case 'dep_audit' :
			return $executive->dep_audit_executive ();
			break;
		case 'alter' :
			return $executive->alter_executive ();
			break;
		case 'userchange' :
			return $executive->userchange_executive ();
			break;
		case 'userchange_all' :
			return $executive->userchange_executive_all ();
			break;
		default :
			return FALSE;
	}
}
function executive_complete_cy() {
	// 成本支付明细
	$costpaycount = Security_Util::my_post ( 'costpaycount' );
	$costpaycount = explode ( ',', $costpaycount );
	$costpaycount_array = array ();
	foreach ( $costpaycount as $cpc ) {
		if (! empty ( $cpc )) {
			// 供应商产品分类
			$costpaycategory = Security_Util::my_post ( 'costpaycategory_' . $cpc );
			$costpaycategory = explode ( '-', $costpaycategory );
			
			$costpaycount_array [] = array (
					'name' => Security_Util::my_post ( 'costpay_' . $cpc ),
					'time' => Security_Util::my_post ( 'costpaytime_' . $cpc ),
					'amount' => Security_Util::my_post ( 'costpayamount_' . $cpc ),
					'type' => Security_Util::my_post ( 'costpaytype_' . $cpc ) 
			)
			// 'category' => ($costpaycategory[0] === 'no' ? ''
			// : $costpaycategory[0]),
			// 'isagent2' => $costpaycategory[1]
			;
		}
	}
	
	$fields = array (
			'executive_id' => Security_Util::my_post ( 'executive_id' ),
			'pid' => Security_Util::my_post ( 'pid' ),
			'costpaycount_array' => $costpaycount_array,
			'cy_json' => Security_Util::my_post ( 'cy_json' ) 
	);
	$executive = new Executive ( NULL, $fields );
	return $executive->executive_complete_cy ();
}
function media_schedule_import() {
	$final_file_path = UPLOAD_FILE_PATH . '/' . date ( 'Ym' ) . '/';
	if (! is_dir ( $final_file_path )) {
		mkdir ( $final_file_path );
	}
	$upload_result = Upload_Util::upload ( 'upfile', UPLOAD_FILE_MAX_SIZE, $final_file_path, TRUE, $GLOBALS ['defined_upload_execel_validate_type'], $GLOBALS ['defined_upload_execel_validate_mime'] );
	if ($upload_result !== NULL) {
		$upload_result = json_decode ( $upload_result );
		if ($upload_result->status === 'error') {
			return array (
					'status' => 'error',
					'message' => $upload_result->message 
			);
		}
		
		$message = $upload_result->message;
		
		$media_schedule = new Executive_Media_Schedule ();
		return $media_schedule->import_media_schedule ( Security_Util::my_post ( 'pid' ), $message->file_name );
	}
	return array (
			'status' => 'error',
			'message' => '必须选择文件上传' 
	);
}
