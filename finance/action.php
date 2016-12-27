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
case 'addreceivables':
	$result = addreceivables();
	break;
case 'invoice_apply':
	$result = invoice_apply();
	break;
case 'invoice_print':
	$result = invoice_do('print');
	break;
case 'invoice_reject':
	$result = invoice_do('reject');
	break;
case 'invoice_make':
	$result = invoice_do('invoice');
	break;
case 'invoice_leader_confirm':
	$result = invoice_do('leader_confirm');
	break;
case 'invoice_leader_reject':
	$result = invoice_do('leader_reject');
	break;
case 'invoice_normal_export':
	$result = invoice_export('normal');
	break;
case 'invoice_export':
	$result = invoice_export();
	break;
case 'invoice_gd_update':
	$result = invoice_do('gd_update');
	break;
case 'invoice_myupdate':
	$result = invoice_myupdate();
	break;
case 'receivables_normal_export':
	$result = receivables_export('normal');
	break;
case 'receivables_export':
	$result = receivables_export();
	break;
case 'receivables_import':
	$result = receivables_import();
	break;
case 'update_receivables':
	$result = update_receivables();
	break;
case 'payment_person_apply':
case 'payment_person_apply_temp':
case 'continue_payment_apply_temp':
case 'continue_payment_apply_apply':
case 'edit_payment_apply_apply':
	$result = payment_person_do($action);
	break;
case 'payment_deposit_person_apply':
case 'edit_payment_deposit_person_apply':
	$result = payment_deposit_person_do($action);
	break;
case 'payment_media_apply_user_input':
	$result = payment_media_apply_user_input();
	break;
case 'payment_media_deposit_apply_user_input':
	$result = payment_media_deposit_apply_user_input();
	break;
case 'receive_invoice_import':
	$result = receive_invoice_import();
	break;
case 'invoice_import':
	$result = invoice_import();
	break;
case 'receive_invoice_source_edit':
	$result = receive_invoice_do('source_update');
	break;
case 'receive_invoice_source_add':
	$result = receive_invoice_do('source_add');
	break;
case 'supplier_import':
	$result = supplier_import();
	break;
case 'update_supplier':
//$result = update_supplier();
	$result = new_update_supplier();
	break;
case 'finance_report':
	set_time_limit(0);
	ini_set('memory_limit', '-1');
	error_reporting(0);
	finance_report();
	//$result = TRUE;
	break;
case 'supplier_short_export':
	supplier_short_export();
	break;
case 'deposit_apply':
	$result = deposit_apply();
	break;
case 'deposit_leaader_audit_pass':
	$result = deposit_leader_audit();
	break;
case 'deposit_leaader_audit_reject':
	$result = deposit_leader_audit(TRUE);
	break;
case 'deposit_invoice_leader_confirm':
	$result = deposit_invoice_leader_audit();
	break;
case 'deposit_invoice_leader_reject':
	$result = deposit_invoice_leader_audit(TRUE);
	break;
case 'deposit_edit':
	$result = deposit_edit();
	break;
case 'deposit_invoice_apply':
	$result = deposit_invoice('apply');
	break;
case 'deposit_invoice_update':
	$result = deposit_invoice('update');
	break;
case 'deposit_invoice_print':
	$result = deposit_invoice_do('print');
	break;
case 'deposit_invoice_reject':
	$result = deposit_invoice_do('reject');
	break;
case 'deposit_invoice_make':
	$result = deposit_invoice_do('invoice');
	break;
case 'deposit_invoice_gdupdate':
	$result = deposit_invoice_do('gd_update');
	break;
case 'adddepositreceivables':
	$result = adddepositreceivables();
	break;
case 'update_deposit_receivables':
	$result = update_deposit_receivables();
	break;
case 'deposit_receivables_export':
	$result = deposit_receivables_export();
	break;
case 'deposit_receivables_import':
	$result = deposit_receivables_import();
	break;
case 'deposit_invoice_export':
	$result = deposit_invoice_export();
	break;
case 'deposit_invoice_import':
	$result = deposit_invoice_import();
	break;
case 'supplier_apply':
	$result = new_supplier_apply();
	break;
case 'supplier_reaudit':
	$result = new_supplier_reaudit();
	break;
case 'supplier_audit':
	$result = new_supplier_audit();
	break;
case 'supplier_industry':
	$result = new_supplier_industry();
	break;
case 'supplier_category':
	$result = supplier_category();
	break;
case 'supplier_industry_update':
	$result = new_supplier_industry_update();
	break;
case 'supplier_category_update':
	$result = supplier_category_update();
	break;
case 'nim_bankinfo_add':
	$result = nim_bankinfo_add();
	break;
case 'customer_refund_apply':
	$result = customer_refund_do('apply');
	break;
case 'customer_refund_edit':
	$result = customer_refund_do('edit');
	break;
case 'refund_apply_allaudit':
	$result = refund_apply_allaudit();
	break;
case 'refund_apply_gd':
	$result = refund_apply_gd();
	break;
case 'payment_media_apply':
	$result = payment_media_do('apply');
	break;
case 'payment_media_edit':
	$result = payment_media_do('edit');
	break;
case 'payment_media_deposit_apply':
	$result = payment_media_deposit_do('apply');
	break;
case 'payment_media_deposit_edit':
	$result = payment_media_deposit_do('edit');
	break;
case 'payment_media_audit':
	$result = payment_media_audit();
	break;
case 'payment_media_deposit_audit':
	$result = payment_media_deposit_audit();
	break;
case 'audit_full_payment_person':
case 'leader_audit_full_payment_person':
	$result = audit_full_payment_person();
	break;
case 'audit_full_payment_media':
	$result = audit_full_payment_media();
	break;
case 'audit_full_payment_media_deposit':
	$result = audit_full_payment_media_deposit();
	break;
case 'audit_full_payment_person_deposit':
	$result = audit_full_payment_person_deposit();
	break;
case 'payment_person_gd':
	$result = payment_person_gd();
	break;
case 'payment_media_gd':
	$result = payment_media_gd();
	break;
case 'payment_media_deposit_gd':
	$result = payment_media_deposit_gd();
	break;
case 'payment_deposit_person_gd':
	$result = payment_deposit_person_gd();
	break;
case 'receive_invoice_source_fix':
	$result = receive_invoice_source_fix();
	break;
case 'receive_invoice_source_share':
	$result = receive_invoice_source_share();
	break;
case 'virtual_invoice_share':
	$result = virtual_invoice_share();
	break;
case 'receive_invoice_source_payment_share':
	$result = receive_invoice_source_payment_share();
	break;
case 'virtual_invoice_payment_share':
	$result = virtual_invoice_payment_share();
	break;
case 'update_virtual_invoice_payment_share':
	$result = virtual_invoice_payment_share('payment_update');
	break;
case 'update_receive_invoice_source_pid_share':
	$result = receive_invoice_source_share('update');
	break;
case 'update_receive_invoice_source_payment_share':
	$result = receive_invoice_source_payment_share('payment_update');
	break;
case 'meida_refund_add':
	$result = meida_refund_add();
	break;
case 'finance_hedge':
	$result = finance_hedge();
	break;
case 'finance_hedge_confirm':
	$result = finance_hedge_confirm();
	break;
case 'sendPayfirstRemindEmail':
	$result = sendPayfirstRemindEmail();
	break;
case 'finance_payment_pid_transfer':
	$result = finance_payment_pid_transfer();
	break;
case 'payment_deposit_2_deposit';
	$result = payment_deposit_2_deposit();
	break;
case 'payment_deposit_2_pid':
	$result = payment_deposit_2_pid();
	break;
case 'rebate_recover_need_invoice':
	$result = rebate_recover_need_invoice();
	break;
case 'rebate_no_need_invoice':
	$result = rebate_no_need_invoice();
	break;
case 'rebate_invoice_apply':
	$result = rebate_invoice_apply();
	break;
case 'update_rebate_invoice_apply':
	$result = rebate_invoice_apply('update');
	break;
case 'rebate_invoice_pass':
	$result = rebate_invoice_audit();
	break;
case 'rebate_invoice_reject':
	$result = rebate_invoice_audit('reject');
	break;
case 'rebate_invoice_gd':
	$result = rebate_invoice_gd();
	break;
case 'rebate_invoice_collection_gd':
	$result = rebate_invoice_gd('collection');
	break;
case 'rebate_invoice_receive2pay':
	$result = rebate_invoice_receive2pay();
	break;
case 'rebate_invoice_pay2receive':
	$result = rebate_invoice_pay2receive();
	break;
case 'settle_account':
	$result = settle_account();
	break;
case 'add_media_short':
	$result = do_media_short('add');
	break;
case 'update_media_short':
	$result = do_media_short('update');
	break;
case 'add_rebate':
	$result = do_media_rebate('add');
	break;
case 'update_rebate':
	$result = do_media_rebate('update');
	break;
case 'reabte_rate_export':
	reabte_rate_export();
	break;
case 'supplier_type_export':
	supplier_type_export();
	break;
case 'exportUnAuditedInvoice':
	exportUnAuditedInvoice();
	break;
default:
	User::no_permission();
}

if ($result !== FALSE) {
	if ($result['status'] === 'error') {
		Js_Util::my_show_error_message($result['message']);
	} else if ($result['status'] === 'success') {
		$url = NULL;
		if (in_array($action,
				array('invoice_print', 'invoice_reject', 'invoice_make'), TRUE)) {
			$url = BASE_URL . 'finance/invoice/?o=invoicelist';
		} else if (in_array($action,
				array('invoice_leader_confirm', 'invoice_leader_reject',
						'deposit_leaader_audit_pass',
						'deposit_leaader_audit_reject',
						'deposit_invoice_leader_confirm',
						'deposit_invoice_leader_reject',
						'deposit_invoice_update', 'supplier_audit',
						'leader_audit_full_payment_person'), TRUE)) {
			$url = BASE_URL;
		} else if (in_array($action, array('deposit_edit'), TRUE)) {
			$url = BASE_URL . 'finance/deposit/?o=my_deposit_list';
		} else if (in_array($action,
				array('deposit_invoice_reject', 'deposit_invoice_print',
						'deposit_invoice_make'), TRUE)) {
			$url = BASE_URL . 'finance/deposit/?o=deposit_invoicelist';
		} else if (in_array($action, array('supplier_reaudit'), TRUE)) {
			$url = BASE_URL . 'finance/supplier/?o=mylist';
		} else if ($action === 'refund_apply_allaudit') {
			$url = BASE_URL . 'finance/refund/?o=manager';
		} else if ($action === 'customer_refund_edit') {
			$url = BASE_URL . 'finance/refund/?o=mylist';
		} else if ($action === 'payment_media_audit') {
			$url = BASE_URL . 'finance/payment/?o=media_manager';
		} else if ($action === 'payment_media_deposit_audit') {
			$url = BASE_URL . 'finance/payment/?o=media_deposit_manager';
		} else if (in_array($action,
				array('continue_payment_apply_apply', 'payment_person_apply',
						'payment_person_apply_temp',
						'continue_payment_apply_temp',
						'edit_payment_apply_apply'), TRUE)) {
			$url = BASE_URL . 'finance/payment/?o=payment_apply_mylist';
		} else if (in_array($action,
				array('audit_full_payment_person_deposit',
						'payment_deposit_person_gd'), TRUE)) {
			$url = BASE_URL . 'finance/payment/?o=person_deposit_apply_manager';
		} else if (in_array($action,
				array('receive_invoice_source_payment_share',
						'receive_invoice_source_share'), TRUE)) {
			$url = BASE_URL . 'finance/receiveinvoice/?o=receiveinvoicelist';
		} else if ($action === 'update_receive_invoice_source_pid_share') {
			$url = BASE_URL . 'finance/receiveinvoice/?o=pidsharelist';
		} else if ($action === 'update_receive_invoice_source_payment_share') {
			$url = BASE_URL . 'finance/receiveinvoice/?o=paymentsharelist';
		} else if ($action === 'audit_full_payment_person') {
			$url = BASE_URL . 'finance/payment/?o=person_apply_manager';
		} else if ($action === 'audit_full_payment_media') {
			$url = BASE_URL . 'finance/payment/?o=media_manager';
		} else if ($action === 'audit_full_payment_media_deposit') {
			$url = BASE_URL . 'finance/payment/?o=media_deposit_manager';
		} else if ($action === 'edit_payment_deposit_person_apply') {
			$url = BASE_URL . 'finance/payment/?o=payment_apply_deposit_mylist';
		} else if ($action === 'payment_media_apply_user_input') {
			$url = BASE_URL . 'finance/payment/?o=media_apply_user_assignlist';
		} else if (in_array($action,
				array('rebate_invoice_reject', 'rebate_invoice_pass'))) {
			$url = BASE_URL . 'finance/rebate/?o=apply_manager';
		} else if (in_array($action,
				array('rebate_invoice_receive2pay',
						'rebate_invoice_pay2receive'))) {
			$url = BASE_URL . 'finance/rebate/?o=rebate_transfer_list';
		}//else if(in_array($action, array('rebate_invoice_gd','rebate_invoice_collection_gd'))){
		//	$url = BASE_URL . 'finance/rebate/?o=apply_manager';
		//}
		Js_Util::my_show_success_message($result['message'], $url);
	}
} else {
	Js_Util::my_show_error_message();
}

function addreceivables() {
	$fields = array('date' => Security_Util::my_post('date'),
			'amount' => Security_Util::my_post('amount'),
			'payer' => Security_Util::my_post('payer'));
	$pids = Security_Util::my_post('pids');
	if (empty($pids)) {
		return array('status' => 'error', 'message' => '没有分配收款额');
	} else {
		$pids = explode(',', $pids);
		$pids_array = array();
		foreach ($pids as $pid) {
			if (!empty($pid)) {
				$pids_array[$pid] = Security_Util::my_post('amount1_' . $pid);
			}
		}
		$fields['pids_array'] = $pids_array;
		$finance = new Finance_Receivables($fields);
		if ($finance->getHas_finance_receivables_permission()) {
			return $finance->add_receivables();
		} else {
			return array('status' => 'error', 'message' => NO_RIGHT_TO_DO_THIS);
		}
	}
}

function update_receivables() {
	$fields = array('date' => Security_Util::my_post('date'),
			'amount' => Security_Util::my_post('amount'),
			'payer' => Security_Util::my_post('payer'),
			'update_id' => Security_Util::my_post('update_id'));
	$pids = Security_Util::my_post('pids');
	if (empty($pids)) {
		return array('status' => 'error', 'message' => '没有分配收款额');
	} else {
		$pids = explode(',', $pids);
		$pids_array = array();
		foreach ($pids as $pid) {
			if (!empty($pid)) {
				$pids_array[$pid] = Security_Util::my_post('amount1_' . $pid);
			}
		}
		$fields['pids_array'] = $pids_array;
		$finance = new Finance_Receivables($fields);
		if ($finance->getHas_finance_receivables_permission()) {
			return $finance->update_receivables();
		} else {
			return array('status' => 'error', 'message' => NO_RIGHT_TO_DO_THIS);
		}
	}
}

function deposit_invoice($action) {
	$pids = Security_Util::my_post('pids');
	if (empty($pids)) {
		return array('status' => 'error', 'message' => '没有选择关联保证金');
	} else {
		$fields = array(
				'invoicecontent' => Security_Util::my_post('invoicecontent'),
				'title' => Security_Util::my_post('title'),
				'content' => Security_Util::my_post('cont'));
		$pids = explode(',', $pids);
		$pids_array = array();
		foreach ($pids as $pid) {
			if (!empty($pid)) {
				$pids_array[$pid] = array(
						'amount' => Security_Util::my_post('amount_' . $pid),
						'cusname' => Security_Util::my_post('cusname_' . $pid),
						'oldamount' => Security_Util::my_post(
								'oldamount_' . $pid));
			}
		}
		$fields['pids_array'] = $pids_array;
		$invoice_type = Security_Util::my_post('type');
		$fields['invoice_type'] = $invoice_type;
		if (intval($invoice_type) === 2) {
			//增票
			$fields['d1'] = Security_Util::my_post('d1');
			$fields['d2'] = Security_Util::my_post('d2');
			$fields['d3'] = Security_Util::my_post('d3');
		}

		if ($action === 'update') {
			$fields['id'] = Security_Util::my_post('invoiceid');
		}
		$deposit_invoice = new Deposit_Invoice($fields);
		switch ($action) {
		case 'apply':
			return $deposit_invoice->invoice_apply();
		case 'update':
			return $deposit_invoice->invoice_update();
		}
	}
}

function invoice($action) {
	$pids = Security_Util::my_post('pids');
	if (empty($pids)) {
		return array('status' => 'error', 'message' => '没有选择关联执行单');
	} else {
		$fields = array('process' => Security_Util::my_post('process'),
				'invoicecontent' => Security_Util::my_post('invoicecontent'),
				'title' => Security_Util::my_post('title'),
				'content' => Security_Util::my_post('cont'));
		$pids = explode(',', $pids);
		$pids_array = array();
		foreach ($pids as $pid) {
			if (!empty($pid)) {
				$pids_array[$pid] = array(
						'amount' => Security_Util::my_post('amount_' . $pid),
						'company' => Security_Util::my_post('company_' . $pid),
						'oldamount' => Security_Util::my_post(
								'oldamount_' . $pid));
			}
		}
		$fields['pids_array'] = $pids_array;
		$invoice_type = Security_Util::my_post('type');
		$fields['invoice_type'] = $invoice_type;
		if (intval($invoice_type) !== 1) {
			//增票
			$fields['d1'] = Security_Util::my_post('d1');
			$fields['d2'] = Security_Util::my_post('d2');
			$fields['d3'] = Security_Util::my_post('d3');
		}

		if ($action === 'myupdate') {
			$fields['id'] = Security_Util::my_post('invoiceid');
		}
		$invoice = new Invoice(NULL, $fields);
		if ($action === 'apply') {
			return $invoice->invoice_apply();
		} else if ($action === 'myupdate') {
			return $invoice->invoice_myupdate();
		}
	}
}

function invoice_apply() {
	return invoice('apply');
}

function invoice_myupdate() {
	return invoice('myupdate');
}

function invoice_do($action) {
	$fields = array('id' => Security_Util::my_post('invoiceid'));
	if ($action === 'print' || $action === 'reject'
			|| $action === 'leader_confirm' || $action === 'leader_reject') {
		$fields['audit_remark'] = Security_Util::my_post('audit_remark');
	} else if ($action === 'invoice' || $action === 'gd_update') {
		$fields['date'] = Security_Util::my_post('date');
		$fields['number'] = Security_Util::my_post('number');
	} else {
		return array('status' => 'error', 'message' => '操作有误');
	}

	$invoice = new Invoice(NULL, $fields);
	if ($invoice->getHas_invoice_permission()) {
		switch ($action) {
		case 'print':
			return $invoice->invoice_print();
		case 'reject':
			return $invoice->invoice_reject();
		case 'invoice':
			return $invoice->invoice_make();
		case 'gd_update':
			return $invoice->invoice_gd_update();
		}
	} else if ($invoice->getHas_leader_audit_invoice_permission()) {
		switch ($action) {
		case 'leader_confirm':
			return $invoice->invoice_leader_confirm();
		case 'leader_reject':
			return $invoice->invoice_leader_reject();
		}
	} else {
		return array('status' => 'error', 'message' => NO_RIGHT_TO_DO_THIS);
	}
}

function invoice_export($type = 'finance') {
	$selinvoice = Security_Util::my_checkbox_post('selinvoice');
	//$s = iconv('utf-8', 'gb2312', '执行单号') . ',' . iconv('utf-8', 'gb2312', '客户名称') . ',' . iconv('utf-8', 'gb2312', '执行总金额') . ',' . iconv('utf-8', 'gb2312', '开票金额') . ',' . iconv('utf-8', 'gb2312', '发票号码') . ',' . iconv('utf-8', 'gb2312', '开票日期')  . "\n";
	$s = '执行单号,客户名称,执行总金额,开票金额,发票号码,开票日期';
	if ($type === 'finance') {
		$s .= ',归档人员';
	}
	$s .= "\n";
	$dao = new Dao_Impl();
	if (!empty($selinvoice)) {
		foreach ($selinvoice as $value) {
			$invoice = new Invoice($value);
			if ($invoice->getView_normalinvoice() && $type === 'normal'
					|| $invoice->getHas_invoice_permission()
							&& $type === 'finance') {
				$pids = explode('|', $invoice->getPids());
				foreach ($pids as $key => $pidinfo) {
					$pidinfo = explode('^', $pidinfo);
					$row = $dao->db
							->get_row(
									'SELECT a.amount,b.cusname FROM executive a LEFT JOIN contract_cus b ON a.cid=b.cid WHERE a.pid="'
											. $pidinfo[0]
											. '" ORDER BY isalter DESC LIMIT 1');
					//$s .= $pidinfo[0] . ',' . iconv('utf-8', 'gb2312', $row->cusname) . ',' .  $row->amount  . ',' . $pidinfo[1] . ',';
					$s .= $pidinfo[0] . ',' . $row->cusname . ','
							. $row->amount . ',' . $pidinfo[1] . ',';
					if ($key === 0) {
						$s .= $invoice->getNumber() . ',' . $invoice->getDate();
					} else {
						$s .= ',';
					}

					if ($type === 'finance') {
						if ($key === 0) {
							$s .= ',' . $invoice->getGdrealname();
						} else {
							$s .= ',';
						}
					}
					$s .= "\n";
				}
			}
			unset($invoice);
		}
	}

	header(
			'Content-type:application/vnd.ms-excel;'
					. (BASE_URL === 'http://oa.nimads.com/' ? ''
							: 'charset=GB2312'));
	header('Content-Disposition:attachment;filename=invoice_export.csv');
	if (BASE_URL === 'http://oa.nimads.com/') {
		echo $s;
	} else {
		echo iconv('UTF-8', 'GB2312', $s);
	}
}

function receivables_export($type = 'finance') {
	$selinvoice = Security_Util::my_checkbox_post('selinvoice');
	$s = '收款日期,执行单号,客户名称,收款金额,付款人名称,状态';
	if ($type === 'finance') {
		$s .= ',登记人';
	}
	$s .= "\n";
	if (!empty($selinvoice)) {
		foreach ($selinvoice as $value) {
			$finance_receivables = new Finance_Receivables(
					array('finance_receivables_id' => $value));
			if ($finance_receivables->getView_normal_receivables()
					&& $type === 'normal'
					|| $finance_receivables
							->getHas_finance_receivables_permission()
							&& $type === 'finance') {
				$s .= $finance_receivables->getDate() . ','
						. $finance_receivables->getPid() . ','
						. $finance_receivables->getCusname() . ','
						. $finance_receivables->getAmount() . ','
						. $finance_receivables->getPayer() . ','
						. $finance_receivables->getIsok();
				if ($type === 'finance') {
					$s .= ',' . $finance_receivables->getDjuser();
				}
				$s .= "\n";
			}
			unset($finance_receivables);
		}
	}

	header(
			'Content-type:application/vnd.ms-excel;'
					. (BASE_URL === 'http://oa.nimads.com/' ? ''
							: 'charset=GB2312'));
	header('Content-Disposition:attachment;filename=receivables_export.csv');
	if (BASE_URL === 'http://oa.nimads.com/') {
		echo $s;
	} else {
		echo iconv('UTF-8', 'GB2312', $s);
	}
}

function receivables_import() {
	$final_file_path = UPLOAD_FILE_PATH . '/' . date('Ym') . '/';
	if (!is_dir($final_file_path)) {
		mkdir($final_file_path);
	}
	$upload_result = Upload_Util::upload('upfile', UPLOAD_FILE_MAX_SIZE,
			$final_file_path, TRUE,
			$GLOBALS['defined_upload_execel_validate_type'],
			$GLOBALS['defined_upload_execel_validate_mime']);

	if ($upload_result !== NULL) {
		$upload_result = json_decode($upload_result);
		if ($upload_result->status === 'error') {
			return array('status' => 'error',
					'message' => $upload_result->message);
		} else {
			$message = $upload_result->message;
			$finance_receivables = new Finance_Receivables();
			return $finance_receivables
					->import_receivables(UPLOAD_FILE_PATH . $message->file_name);
		}
	} else {
		return array('status' => 'error', 'message' => '必须选择文件上传');
	}
}

function payment_media_deposit_apply_user_input() {
	$payment_list = array();
	$cids = Security_Util::my_post('pids');
	if (strpos($cids, ',') !== FALSE) {
		$cids = explode(',', $cids);
		foreach ($cids as $cid) {
			if (!empty($cid)) {
				$payment_list[] = array('cid' => $cid,
						'payment_type' => Security_Util::my_post(
								'payment_type_' . $cid),
						'payment_amount' => Security_Util::my_post(
								'payment_amount_' . $cid),
						'person_loan_user' => Security_Util::my_post(
								'person_loan_user_' . $cid),
						'person_loan_amount' => Security_Util::my_post(
								'person_loan_amount_' . $cid),
						'is_nim_pay_first' => Security_Util::my_post(
								'is_nim_pay_first_' . $cid),
						'nim_pay_first_amount' => Security_Util::my_post(
								'nim_pay_first_amount_' . $cid),
						'nim_pay_first_dids' => Security_Util::my_post(
								'nim_pay_first_dids_' . $cid));
			}
		}
		//var_dump($payment_list);
		$itemids = Security_Util::my_post('itemids');
		$itemids = explode(',', $itemids);
		$itemids = Array_Util::my_remove_array_other_value($itemids, array(''));
		$user_input = new Payment_Media_Deposit_Apply_User_Input(
				array('id' => Security_Util::my_post('id'),
						'assignid' => Security_Util::my_post('assignid'),
						'itemids' => $itemids, 'payment_list' => $payment_list));
		return $user_input->getUserInputResult();
	} else {
		return array('status' => 'error', 'message' => '合同选择不能为空');
	}
}

function payment_media_apply_user_input() {
	$payment_list = array();
	$pids = Security_Util::my_post('pids');
	if (strpos($pids, ',') !== FALSE) {
		$pids = explode(',', $pids);
		foreach ($pids as $pid) {
			if (!empty($pid)) {
				$tmppid = explode('_', $pid);
				$payment_list[] = array('pid' => $tmppid[0],
						'paycostid' => $tmppid[1],
						'payment_type' => Security_Util::my_post(
								'payment_type_' . $pid),
						'payment_amount' => Security_Util::my_post(
								'payment_amount_' . $pid),
						'rebate_deduction_amount' => Security_Util::my_post(
								'rebate_deduction_amount_' . $pid),
						'rebate_deduction_dids' => Security_Util::my_post(
								'rebate_deduction_dids_' . $pid),
						'person_loan_user' => Security_Util::my_post(
								'person_loan_user_' . $pid),
						'person_loan_amount' => Security_Util::my_post(
								'person_loan_amount_' . $pid),
						'is_nim_pay_first' => Security_Util::my_post(
								'is_nim_pay_first_' . $pid),
						'nim_pay_first_amount' => Security_Util::my_post(
								'nim_pay_first_amount_' . $pid),
						'nim_pay_first_dids' => Security_Util::my_post(
								'nim_pay_first_dids_' . $pid));
			}
		}
	}

	$itemids = Security_Util::my_post('itemids');
	$itemids = explode(',', $itemids);
	$itemids = Array_Util::my_remove_array_other_value($itemids, array(''));
	$user_input = new Payment_Media_Apply_User_Input(
			array('id' => Security_Util::my_post('id'),
					'assignid' => Security_Util::my_post('assignid'),
					'itemids' => $itemids, 'payment_list' => $payment_list));
	return $user_input->getUserInputResult();
}

function payment_deposit_person_do($action) {
	$payment_list = array();
	$pids = Security_Util::my_post('pids');
	if (strpos($pids, ',') !== FALSE) {
		$pids = explode(',', $pids);
		foreach ($pids as $pid) {
			if (!empty($pid)) {
				$tmppid = explode('-_-!', $pid);
				$payment_list[] = array('cid' => $tmppid[0],
						'media_name' => $tmppid[1], 'category' => $tmppid[2],
						'payment_amount' => Security_Util::my_post(
								'payment_amount_' . $pid),
						'rebate_deduction_amount' => Security_Util::my_post(
								'rebate_deduction_amount_' . $pid),
						'rebate_deduction_dids' => Security_Util::my_post(
								'rebate_deduction_dids_' . $pid),
						'person_loan_user' => Security_Util::my_post(
								'person_loan_user_' . $pid),
						'person_loan_amount' => Security_Util::my_post(
								'person_loan_amount_' . $pid),
						'is_nim_pay_first' => Security_Util::my_post(
								'is_nim_pay_first_' . $pid),
						'nim_pay_first_amount' => Security_Util::my_post(
								'nim_pay_first_amount_' . $pid),
						'nim_pay_first_dids' => Security_Util::my_post(
								'nim_pay_first_dids_' . $pid));
			}
		}
	}

	//如果勾选保证金抵扣
	if (intval(Security_Util::my_post('is_deposit_deduction')) === 1) {
		$deposit_deductiuon = Security_Util::my_post('deposit_deductiuon');
		$deposit_deductiuon = explode(',', $deposit_deductiuon);
		//var_dump($deposit_deductiuon);
		foreach ($deposit_deductiuon as $dd) {
			if (!empty($dd)) {
				$dd = explode('_', $dd);
				//归档记录
				$deposit_list[] = array('id' => $dd[0], 'dtype' => $dd[1],
						'amount' => abs(
								Security_Util::my_post(
										'ddeduction_' . $dd[0] . '_' . $dd[1])));
			}
		}
	}
	//var_dump($deposit_deductiuon);

	$fields = array('action' => $action,
			'media_name' => Security_Util::my_post('media_name'),
			'bank_name' => Security_Util::my_post('bank_name'),
			'bank_name_select' => Security_Util::my_post('bank_name_select'),
			'bank_account' => Security_Util::my_post('bank_account'),
			'bank_account_select' => Security_Util::my_post(
					'bank_account_select'),
			'payment_date' => Security_Util::my_post('payment_date'),
			'payment_amount_plan' => Security_Util::my_post(
					'payment_amount_plan'),
			'is_nim_pay_first' => Security_Util::my_post('is_nim_pay_first'),
			'is_rebate_deduction' => Security_Util::my_post(
					'is_rebate_deduction'),
			'rebate_amount' => Security_Util::my_post('rebate_amount'),
			'is_deposit_deduction' => Security_Util::my_post(
					'is_deposit_deduction'),
			'is_person_loan_deduction' => Security_Util::my_post(
					'is_person_loan_deduction'),
			'person_loan_amount' => Security_Util::my_post('person_loan_amount'),
			'remark' => Security_Util::my_post('remark'),
			'payment_list' => $payment_list, 'deposit_list' => $deposit_list,
			'is_contract_deduction' => Security_Util::my_post(
					'is_contract_deduction'));
	if ($action === 'edit_payment_deposit_person_apply') {
		$fields['id'] = Security_Util::my_post('id');
	}

	$payment_deposit_person = new Payment_Person_Apply_Deposit($fields);
	switch ($action) {
	case 'payment_deposit_person_apply':
		return $payment_deposit_person->add_payment_deposit_person_apply();
	case 'edit_payment_deposit_person_apply':
		return $payment_deposit_person->edit_payment_deposit_person_apply();
	}
	return array('status' => 'error', 'message' => NO_RIGHT_TO_DO_THIS);
}

function payment_person_do($action) {
	$payment_list = array();
	$deposit_list = array();

	$pids = Security_Util::my_post('pids');
	$deposit_deductiuon = Security_Util::my_post('deposit_deductiuon');

	if (strpos($pids, ',') !== FALSE) {
		$pids = explode(',', $pids);
		foreach ($pids as $pid) {
			if (!empty($pid)) {
				$tmppid = explode('_', $pid);
				$payment_list[] = array('pid' => $tmppid[0],
						'paycostid' => $tmppid[1],
						'payment_type' => Security_Util::my_post(
								'payment_type_' . $pid),
						'payment_amount' => Security_Util::my_post(
								'payment_amount_' . $pid),
						'rebate_deduction_amount' => Security_Util::my_post(
								'rebate_deduction_amount_' . $pid),
						'rebate_deduction_dids' => Security_Util::my_post(
								'rebate_deduction_dids_' . $pid),
						'person_loan_user' => Security_Util::my_post(
								'person_loan_user_' . $pid),
						'person_loan_amount' => Security_Util::my_post(
								'person_loan_amount_' . $pid),
						'is_nim_pay_first' => Security_Util::my_post(
								'is_nim_pay_first_' . $pid),
						'nim_pay_first_amount' => Security_Util::my_post(
								'nim_pay_first_amount_' . $pid),
						'nim_pay_first_dids' => Security_Util::my_post(
								'nim_pay_first_dids_' . $pid));
			}
		}
	}

	//如果勾选保证金抵扣
	if (intval(Security_Util::my_post('is_deposit_deduction')) === 1) {
		$deposit_deductiuon = explode(',', $deposit_deductiuon);
		//var_dump($deposit_deductiuon);
		foreach ($deposit_deductiuon as $dd) {
			if (!empty($dd)) {
				$dd = explode('_', $dd);
				//归档记录
				$deposit_list[] = array('id' => $dd[0], 'dtype' => $dd[1],
						'amount' => abs(
								Security_Util::my_post(
										'ddeduction_' . $dd[0] . '_' . $dd[1])));
			}
		}
	}

	$fields = array('action' => $action,
			'media_name' => Security_Util::my_post('media_name'),
			'bank_name' => Security_Util::my_post('bank_name'),
			'bank_name_select' => Security_Util::my_post('bank_name_select'),
			'bank_account' => Security_Util::my_post('bank_account'),
			'bank_account_select' => Security_Util::my_post(
					'bank_account_select'),
			'payment_date' => Security_Util::my_post('payment_date'),
			'payment_amount_plan' => Security_Util::my_post(
					'payment_amount_plan'),
			'is_nim_pay_first' => Security_Util::my_post('is_nim_pay_first'),
			'is_rebate_deduction' => Security_Util::my_post(
					'is_rebate_deduction'),
			'rebate_amount' => Security_Util::my_post('rebate_amount'),
			'rebate_rate' => Security_Util::my_post('rebate_rate'),
			'is_deposit_deduction' => Security_Util::my_post(
					'is_deposit_deduction'),
			'is_person_loan_deduction' => Security_Util::my_post(
					'is_person_loan_deduction'),
			'person_loan_amount' => Security_Util::my_post('person_loan_amount'),
			'remark' => Security_Util::my_post('remark'),
			'payment_list' => $payment_list, 'deposit_list' => $deposit_list);

	switch ($action) {
	case 'payment_person_apply':
	case 'payment_person_apply_temp':
		$payment_person = new Payment_Person_Apply($fields);
		return $payment_person->add_payment_person_apply();
	case 'continue_payment_apply_temp':
	case 'continue_payment_apply_apply':
	case 'edit_payment_apply_apply':
		$fields['id'] = Security_Util::my_post('id');
		$payment_person = new Payment_Person_Apply($fields);
		return $payment_person->edit_payment_person_apply();
	}
	return array('status' => 'error', 'message' => NO_RIGHT_TO_DO_THIS);
}

function receive_invoice_import() {
	$final_file_path = UPLOAD_FILE_PATH . '/' . date('Ym') . '/';
	if (!is_dir($final_file_path)) {
		mkdir($final_file_path);
	}
	$upload_result = Upload_Util::upload('upfile', UPLOAD_FILE_MAX_SIZE,
			$final_file_path, TRUE,
			$GLOBALS['defined_upload_execel_validate_type'],
			$GLOBALS['defined_upload_execel_validate_mime']);

	if ($upload_result !== NULL) {
		$upload_result = json_decode($upload_result);
		if ($upload_result->status === 'error') {
			return array('status' => 'error',
					'message' => $upload_result->message);
		} else {
			$message = $upload_result->message;
			$invoice = new Finance_Receive_Invoice();
			return $invoice
					->import_receive_invoice(
							UPLOAD_FILE_PATH . $message->file_name);
		}
	} else {
		return array('status' => 'error', 'message' => '必须选择文件上传');
	}
}

function invoice_import() {
	$final_file_path = UPLOAD_FILE_PATH . '/' . date('Ym') . '/';
	if (!is_dir($final_file_path)) {
		mkdir($final_file_path);
	}
	$upload_result = Upload_Util::upload('upfile', UPLOAD_FILE_MAX_SIZE,
			$final_file_path, TRUE,
			$GLOBALS['defined_upload_execel_validate_type'],
			$GLOBALS['defined_upload_execel_validate_mime']);

	if ($upload_result !== NULL) {
		$upload_result = json_decode($upload_result);
		if ($upload_result->status === 'error') {
			return array('status' => 'error',
					'message' => $upload_result->message);
		} else {
			$message = $upload_result->message;
			$invoice = new Invoice();
			return $invoice
					->import_invoice(UPLOAD_FILE_PATH . $message->file_name);
		}
	} else {
		return array('status' => 'error', 'message' => '必须选择文件上传');
	}
}

function receive_invoice_do($action) {
	$fields = array(
			'taxpayer_number' => Security_Util::my_post('taxpayer_number'),
			'media_name' => Security_Util::my_post('media_name'),
			'invoice_content' => Security_Util::my_post('invoice_content'),
			'invoice_number' => Security_Util::my_post('invoice_number'),
			'tax_rate' => Security_Util::my_post('tax_rate'),
			'amount' => Security_Util::my_post('amount'),
			'invoice_date' => Security_Util::my_post('invoice_date'),
			'belong_month' => Security_Util::my_post('belong_month'));
	if ($action === 'source_update') {
		$fields['sourceid'] = Security_Util::my_post('sourceid');
	}

	$invoice = new Finance_Receive_Invoice($fields);
	switch ($action) {
	case 'source_update':
		return $invoice->update_receive_invoice_source();
		break;
	case 'source_add':
		return $invoice->add_receive_invoice_source();
		break;
	default:
		return FALSE;
	}
}

function supplier_import() {
	$final_file_path = UPLOAD_FILE_PATH . '/' . date('Ym') . '/';
	if (!is_dir($final_file_path)) {
		mkdir($final_file_path);
	}
	$upload_result = Upload_Util::upload('upfile', UPLOAD_FILE_MAX_SIZE,
			$final_file_path, TRUE,
			$GLOBALS['defined_upload_execel_validate_type'],
			$GLOBALS['defined_upload_execel_validate_mime']);

	if ($upload_result !== NULL) {
		$upload_result = json_decode($upload_result);
		if ($upload_result->status === 'error') {
			return array('status' => 'error',
					'message' => $upload_result->message);
		} else {
			$message = $upload_result->message;
			$supplier = new Supplier();
			return $supplier
					->import_supplier(UPLOAD_FILE_PATH . $message->file_name);
		}
	} else {
		return array('status' => 'error', 'message' => '必须选择文件上传');
	}
}

function update_supplier() {
	$supplier = new Supplier(
			array('infoid' => Security_Util::my_post('info_id'),
					'rebate' => Security_Util::my_post('rebate'),
					'in_invoice_tax_rate' => Security_Util::my_post(
							'in_invoice_tax_rate'),
					'deduction' => Security_Util::my_post('deduction'),
					'supplier_type' => Security_Util::my_post('supplier_type'),
					'parentid' => Security_Util::my_post('parentid')));
	return $supplier->update_supplier();
}

function new_update_supplier() {
	$supplier = new Supplier(
			array('id' => Security_Util::my_post('id'),
					'supplier_name' => Security_Util::my_post('supplier_name'),
					'url' => Security_Util::my_post('url'),
					'in_invoice_tax_rate' => Security_Util::my_post(
							'in_invoice_tax_rate'),
					'deduction' => Security_Util::my_post('deduction'),
					'supplier_type' => Security_Util::my_post('supplier_type'),
					'parentid' => Security_Util::my_post('parentid')));
	return $supplier->new_update_supplier();
}

function finance_report() {
	$seldate = Security_Util::my_checkbox_post('seldate');
	if(count($seldate) !== 2){
		User::no_object('请选择2个时间点来确定报表生成条件');
		exit;
	}
	$finance_report = new Finance_Report(
			array('starttime' => min($seldate),
					'endtime' => max($seldate)
			));
	$finance_report->get_finance_report();
}

function supplier_short_export(){
	$ss = new Supplier_Short();
	$ss->exportSupplierShort();
}

function deposit_apply() {
	$deposit = new Deposit(
			array('cid' => Security_Util::my_post('cid'),
					'amount' => Security_Util::my_post('amount')));
	return $deposit->add_deposit();
}

function deposit_leader_audit($reject = FALSE) {
	$deposit = new Deposit(
			array('id' => Security_Util::my_post('depositid'),
					'reject' => $reject,
					'auditmsg' => Security_Util::my_post('auditmsg')));
	return $deposit->audit_deposit();
}

function deposit_edit() {
	$deposit = new Deposit(
			array('id' => Security_Util::my_post('depositid'),
					'amount' => Security_Util::my_post('amount')));
	return $deposit->update_deposit();
}

function deposit_invoice_leader_audit($reject = FALSE) {
	$fields = array('id' => Security_Util::my_post('invoiceid'));
	if ($reject) {
		$fields['audit_remark'] = Security_Util::my_post('audit_remark');
	}

	$deposit_invoice = new Deposit_Invoice($fields);
	if ($deposit_invoice->getHas_deposit_tab()) {
		if (!$reject) {
			return $deposit_invoice->invoice_leader_confirm();
		} else {
			return $deposit_invoice->invoice_leader_reject();
		}
	} else {
		return array('status' => 'error', 'message' => NO_RIGHT_TO_DO_THIS);
	}
}

function deposit_invoice_do($action) {
	$fields = array('id' => Security_Util::my_post('invoiceid'));
	if ($action === 'print' || $action === 'reject') {
		$fields['audit_remark'] = Security_Util::my_post('audit_remark');
	} else if ($action === 'invoice' || $action === 'gd_update') {
		$fields['date'] = Security_Util::my_post('date');
		$fields['number'] = Security_Util::my_post('number');
	} else {
		return array('status' => 'error', 'message' => '操作有误');
	}

	$deposit_invoice = new Deposit_Invoice($fields);
	if ($deposit_invoice->getHas_invoice_permission()) {
		switch ($action) {
		case 'print':
			return $deposit_invoice->deposit_invoice_print();
		case 'reject':
			return $deposit_invoice->deposit_invoice_reject();
		case 'invoice':
			return $deposit_invoice->deposit_invoice_make();
		case 'gd_update':
			return $deposit_invoice->deposit_invoice_gd_update();
		}
	} else {
		return array('status' => 'error', 'message' => NO_RIGHT_TO_DO_THIS);
	}
}

function adddepositreceivables() {
	$fields = array('date' => Security_Util::my_post('date'),
			'amount' => Security_Util::my_post('amount'),
			'payer' => Security_Util::my_post('payer'));
	$pids = Security_Util::my_post('pids');
	if (empty($pids)) {
		return array('status' => 'error', 'message' => '没有分配收款额');
	} else {
		$pids = explode(',', $pids);
		$pids_array = array();
		foreach ($pids as $pid) {
			if (!empty($pid)) {
				$pids_array[$pid] = Security_Util::my_post('amount1_' . $pid);
			}
		}
		$fields['pids_array'] = $pids_array;
		$deposit = new Deposit_Receivables($fields);
		if ($deposit->getHas_deposit_receivables_permission()) {
			return $deposit->add_deposit_receivables();
		} else {
			return array('status' => 'error', 'message' => NO_RIGHT_TO_DO_THIS);
		}
	}
}

function update_deposit_receivables() {
	$fields = array('date' => Security_Util::my_post('date'),
			'amount' => Security_Util::my_post('amount'),
			'payer' => Security_Util::my_post('payer'),
			'update_id' => Security_Util::my_post('update_id'));
	$pids = Security_Util::my_post('pids');
	if (empty($pids)) {
		return array('status' => 'error', 'message' => '没有分配收款额');
	} else {
		$pids = explode(',', $pids);
		$pids_array = array();
		foreach ($pids as $pid) {
			if (!empty($pid)) {
				$pids_array[$pid] = Security_Util::my_post('amount1_' . $pid);
			}
		}
		$fields['pids_array'] = $pids_array;
		$deposit = new Deposit_Receivables($fields);
		if ($deposit->getHas_deposit_receivables_permission()) {
			return $deposit->update_deposit_receivables();
		} else {
			return array('status' => 'error', 'message' => NO_RIGHT_TO_DO_THIS);
		}
	}
}

function deposit_receivables_export($type = 'finance') {
	$selinvoice = Security_Util::my_checkbox_post('selinvoice');
	$s = '收款日期,合同号,客户名称,收款金额,付款人名称,状态';
	if ($type === 'finance') {
		$s .= ',登记人';
	}
	$s .= "\n";
	if (!empty($selinvoice)) {
		foreach ($selinvoice as $value) {
			$deposit_receivables = new Deposit_Receivables(
					array('finance_receivables_id' => $value));
			if ($deposit_receivables->getHas_deposit_receivables_permission()
					&& $type === 'finance') {
				$s .= $deposit_receivables->getDate() . ','
						. $deposit_receivables->getCid() . ','
						. $deposit_receivables->getCusname() . ','
						. $deposit_receivables->getAmount() . ','
						. $deposit_receivables->getPayer() . ','
						. $deposit_receivables->getIsok();
				if ($type === 'finance') {
					$s .= ',' . $deposit_receivables->getDjuser();
				}
				$s .= "\n";
			}
			unset($finance_receivables);
		}
	}

	header(
			'Content-type:application/vnd.ms-excel;'
					. (BASE_URL === 'http://oa.nimads.com/' ? ''
							: 'charset=GB2312'));
	header('Content-Disposition:attachment;filename=receivables_export.csv');
	if (BASE_URL === 'http://oa.nimads.com/') {
		echo $s;
	} else {
		echo iconv('UTF-8', 'GB2312', $s);
	}
}

function deposit_receivables_import() {
	$final_file_path = UPLOAD_FILE_PATH . '/' . date('Ym') . '/';
	if (!is_dir($final_file_path)) {
		mkdir($final_file_path);
	}
	$upload_result = Upload_Util::upload('upfile', UPLOAD_FILE_MAX_SIZE,
			$final_file_path, TRUE,
			$GLOBALS['defined_upload_execel_validate_type'],
			$GLOBALS['defined_upload_execel_validate_mime']);

	if ($upload_result !== NULL) {
		$upload_result = json_decode($upload_result);
		if ($upload_result->status === 'error') {
			return array('status' => 'error',
					'message' => $upload_result->message);
		} else {
			$message = $upload_result->message;
			$deposit_receivables = new Deposit_Receivables();
			return $deposit_receivables
					->import_receivables(UPLOAD_FILE_PATH . $message->file_name);
		}
	} else {
		return array('status' => 'error', 'message' => '必须选择文件上传');
	}
}

function deposit_invoice_export($type = 'finance') {
	$selinvoice = Security_Util::my_checkbox_post('selinvoice');
	$s = '合同号,客户名称,保证金总金额,开票金额,发票号码,开票日期';
	if ($type === 'finance') {
		$s .= ',归档人员';
	}
	$s .= "\n";
	$dao = new Dao_Impl();
	if (!empty($selinvoice)) {
		$invoice = new Deposit_Invoice();
		if ($invoice->getHas_invoice_permission() && $type === 'finance') {
			$results = $dao->db
					->get_results(
							'SELECT a.cid,a.amount AS doneamount,b.cusname,b.amount,c.number,c.date,d.realname FROM finance_deposit_invoice a LEFT JOIN finance_deposit b ON a.cid=b.cid LEFT JOIN finance_deposit_invoice_list c ON a.invoice_list_id=c.id LEFT JOIN users d ON c.gduser=d.uid WHERE c.id IN ('
									. implode(',', $selinvoice)
									. ') ORDER BY c.number,a.id');
			if ($results !== NULL) {
				$now_number = '';
				foreach ($results as $key => $result) {
					$s .= $result->cid . ',' . $result->cusname . ','
							. $result->amount . ',' . $result->doneamount . ',';
					if ($now_number !== $result->number) {
						$s .= $result->number . ',' . $result->date;
						if ($type === 'finance') {
							$s .= ',' . $result->realname;
						}
						$now_number = $result->number;
					} else {
						$s .= ',';
						if ($type === 'finance') {
							$s .= ',';
						}
					}
					$s .= "\n";
				}
			}
		}
		unset($invoice);
	}

	header(
			'Content-type:application/vnd.ms-excel;'
					. (BASE_URL === 'http://oa.nimads.com/' ? ''
							: 'charset=GB2312'));
	header('Content-Disposition:attachment;filename=invoice_export.csv');
	if (BASE_URL === 'http://oa.nimads.com/') {
		echo $s;
	} else {
		echo iconv('UTF-8', 'GB2312', $s);
	}
}

function deposit_invoice_import() {
	$final_file_path = UPLOAD_FILE_PATH . '/' . date('Ym') . '/';
	if (!is_dir($final_file_path)) {
		mkdir($final_file_path);
	}
	$upload_result = Upload_Util::upload('upfile', UPLOAD_FILE_MAX_SIZE,
			$final_file_path, TRUE,
			$GLOBALS['defined_upload_execel_validate_type'],
			$GLOBALS['defined_upload_execel_validate_mime']);

	if ($upload_result !== NULL) {
		$upload_result = json_decode($upload_result);
		if ($upload_result->status === 'error') {
			return array('status' => 'error',
					'message' => $upload_result->message);
		} else {
			$message = $upload_result->message;
			$invoice = new Deposit_Invoice();
			return $invoice
					->import_invoice(UPLOAD_FILE_PATH . $message->file_name);
		}
	} else {
		return array('status' => 'error', 'message' => '必须选择文件上传');
	}
}

function new_supplier_apply() {
	$supplier_apply = new Supplier(
			array('supplier_name' => Security_Util::my_post('supplier_name'),
					'url' => Security_Util::my_post('url'),
					'deduction' => Security_Util::my_post('deduction'),
					'in_invoice_tax_rate' => Security_Util::my_post(
							'in_invoice_tax_rate'),
					'supplier_type' => Security_Util::my_post('supplier_type'),
					'dids' => Security_Util::my_post('dids')));
	return $supplier_apply->new_supplier_apply();
}

function supplier_apply() {
	$supplier_apply = new Supplier(
			array('supplier_name' => Security_Util::my_post('supplier_name'),
					'url' => Security_Util::my_post('url'),
					'deduction' => Security_Util::my_post('deduction'),
					'in_invoice_tax_rate' => Security_Util::my_post(
							'in_invoice_tax_rate'),
					'supplier_type' => Security_Util::my_post('supplier_type'),
					'dids' => Security_Util::my_post('dids'),
					'rebate' => Security_Util::my_post('rebate'),
					'category' => Security_Util::my_post('category'),
					'isagent2' => Security_Util::my_post('isagent2')));
	return $supplier_apply->supplier_apply();
}

function supplier_reaudit() {
	$supplier_apply = new Supplier(
			array('supplier_name' => Security_Util::my_post('supplier_name'),
					'url' => Security_Util::my_post('url'),
					'deduction' => Security_Util::my_post('deduction'),
					'in_invoice_tax_rate' => Security_Util::my_post(
							'in_invoice_tax_rate'),
					'supplier_type' => Security_Util::my_post('supplier_type'),
					'dids' => Security_Util::my_post('dids'),
					'rebate' => Security_Util::my_post('rebate'),
					'id' => Security_Util::my_post('id'),
					'category' => Security_Util::my_post('category'),
					'isagent2' => Security_Util::my_post('isagent2')));
	return $supplier_apply->supplier_reaudit();
}

function new_supplier_reaudit() {
	$supplier_apply = new Supplier(
			array('supplier_name' => Security_Util::my_post('supplier_name'),
					'url' => Security_Util::my_post('url'),
					'deduction' => Security_Util::my_post('deduction'),
					'in_invoice_tax_rate' => Security_Util::my_post(
							'in_invoice_tax_rate'),
					'supplier_type' => Security_Util::my_post('supplier_type'),
					'dids' => Security_Util::my_post('dids'),
					'id' => Security_Util::my_post('id')));
	return $supplier_apply->new_supplier_reaudit();
}

function supplier_audit() {
	$supplier_apply = new Supplier(
			array('id' => Security_Util::my_post('id'),
					'audit_result' => Security_Util::my_post('audit_result'),
					'remark' => Security_Util::my_post('remark'),
					'parentid' => Security_Util::my_post('parentid')));
	if ($supplier_apply->getHas_supplier_apply_audit_tab()) {
		return $supplier_apply->audit_supplier_apply();
	}
	return array('status' => 'error', 'message' => NO_RIGHT_TO_DO_THIS);
}

function new_supplier_audit() {
	$supplier_apply = new Supplier(
			array('id' => Security_Util::my_post('id'),
					'audit_result' => Security_Util::my_post('audit_result'),
					'remark' => Security_Util::my_post('remark'),
					'parentid' => Security_Util::my_post('parentid')));
	if ($supplier_apply->getHas_supplier_apply_audit_tab()) {
		return $supplier_apply->new_audit_supplier_apply();
	}
	return array('status' => 'error', 'message' => NO_RIGHT_TO_DO_THIS);
}

function new_supplier_industry() {
	$supplier = new Supplier(
			array('supplier_name' => Security_Util::my_post('supplier_name'),
					'industry_name' => Security_Util::my_post('industry_name')));
	return $supplier->new_supplier_industy();
}

function supplier_category() {
	$supplier = new Supplier(
			array('supplier_name' => Security_Util::my_post('supplier_name'),
					'category_name' => Security_Util::my_post('category_name')));
	return $supplier->supplier_category();
}

function new_supplier_industry_update() {
	$supplier = new Supplier(
			array('industry_id' => Security_Util::my_post('industry_id'),
					'industry_name' => Security_Util::my_post('industry_name')));
	return $supplier->new_industry_update();
}

function supplier_category_update() {
	$supplier = new Supplier(
			array('category_id' => Security_Util::my_post('category_id'),
					'category_name' => Security_Util::my_post('category_name')));
	return $supplier->category_update();
}

function nim_bankinfo_add() {
	$nimbank = new Nim_BankInfo(
			array(
					'bank_name_select' => Security_Util::my_post(
							'bank_name_select'),
					'bank_name' => Security_Util::my_post('bank_name'),
					'bank_account' => Security_Util::my_post('bank_account'),
					'is_default' => Security_Util::my_post('is_default'),
					'status' => Security_Util::my_post('status')));
	return $nimbank->nim_bankinfo_add();
}

function customer_refund_do($action) {
	$refund_pids = array();
	$pids = Security_Util::my_post('pids');
	if (!empty($pids)) {
		$pids = explode(',', $pids);
		foreach ($pids as $pid) {
			if ($pid !== "") {
				$refund_pids[$pid] = Security_Util::my_post('refund_' . $pid);
			}
		}
	}

	$fields = array(
			'customer_name_select' => Security_Util::my_post(
					'customer_name_select'),
			'customer_name' => Security_Util::my_post('customer_name'),
			'bank_name_select' => Security_Util::my_post('bank_name_select'),
			'bank_name' => Security_Util::my_post('bank_name'),
			'bank_account_select' => Security_Util::my_post(
					'bank_account_select'),
			'bank_account' => Security_Util::my_post('bank_account'),
			'refundment_amount' => Security_Util::my_post('refundment_amount'),
			'refundment_date' => Security_Util::my_post('refundment_date'),
			'refundment_type' => Security_Util::my_post('refundment_type'),
			'refundment_dids' => Security_Util::my_post('refundment_dids'),
			'refundment_reason' => Security_Util::my_post('refundment_reason'),
			'refund_pids' => $refund_pids);

	if ($action === 'edit') {
		$fields['id'] = Security_Util::my_post('id');
	}

	$refund = new Finance_Refund($fields);
	switch ($action) {
	case 'apply':
		return $refund->customer_refund_apply();
	case 'edit':
		return $refund->customer_refund_edit();
	}
}

function refund_apply_allaudit() {
	$refund = new Finance_Refund(
			array('id' => Security_Util::my_post('id'),
					'auditall' => Security_Util::my_post('auditall'),
					'auditremarkall' => Security_Util::my_post('auditremarkall')));
	return $refund->refund_apply_allaudit();
}

function refund_apply_gd() {
	$refund = new Finance_Refund(
			array('id' => Security_Util::my_post('id'),
					'refundment_date' => Security_Util::my_post('refund_date'),
					'refundment_amount' => Security_Util::my_post(
							'refund_amount')));
	return $refund->refund_apply_gd();
}

function payment_media_deposit_do($action) {
	$fields = array('media_name' => Security_Util::my_post('media_name'),
			'bank_name_select' => Security_Util::my_post('bank_name_select'),
			'bank_name' => Security_Util::my_post('bank_name'),
			'bank_account_select' => Security_Util::my_post(
					'bank_account_select'),
			'bank_account' => Security_Util::my_post('bank_account'),
			'payment_amount_plan' => Security_Util::my_post(
					'payment_amount_plan'),
			'payment_date' => Security_Util::my_post('payment_date'),
			'is_nim_pay_first' => Security_Util::my_post('is_nim_pay_first'),
			'is_rebate_deduction' => Security_Util::my_post(
					'is_rebate_deduction'),
			'rebate_amount' => Security_Util::my_post('rebate_amount'),
			'rebate_dids' => Security_Util::my_post('rebate_dids'),
			'is_deposit_deduction' => Security_Util::my_post(
					'is_deposit_deduction'),
			'deposit_dids' => Security_Util::my_post('deposit_dids'),
			'is_person_loan_deduction' => Security_Util::my_post(
					'is_person_loan_deduction'),
			'person_loan_amount' => Security_Util::my_post('person_loan_amount'),
			'remark' => Security_Util::my_post('remark'),
			'payment_apply_deadline' => Security_Util::my_post(
					'payment_apply_deadline'),
			'statement' => Security_Util::my_post('statement'),
			'users' => Security_Util::my_post('users'),
			'pcid' => Security_Util::my_post('process'));

	if ($action === 'edit') {
		$fields['id'] = Security_Util::my_post('id');
		$fields['statement_del'] = Security_Util::my_post('statement_del');
	}
	$payment_media = new Payment_Media_Deposit_Apply($fields);
	switch ($action) {
	case 'apply':
		return $payment_media->payment_media_deposit_apply();
	case 'edit':
		return $payment_media->payment_media_deposit_edit();
	}
}

function payment_media_do($action) {
	$fields = array('media_name' => Security_Util::my_post('media_name'),
			'bank_name_select' => Security_Util::my_post('bank_name_select'),
			'bank_name' => Security_Util::my_post('bank_name'),
			'bank_account_select' => Security_Util::my_post(
					'bank_account_select'),
			'bank_account' => Security_Util::my_post('bank_account'),
			'payment_amount_plan' => Security_Util::my_post(
					'payment_amount_plan'),
			'payment_date' => Security_Util::my_post('payment_date'),
			'is_nim_pay_first' => Security_Util::my_post('is_nim_pay_first'),
			'is_rebate_deduction' => Security_Util::my_post(
					'is_rebate_deduction'),
			'rebate_amount' => Security_Util::my_post('rebate_amount'),
			'rebate_dids' => Security_Util::my_post('rebate_dids'),
			'is_deposit_deduction' => Security_Util::my_post(
					'is_deposit_deduction'),
			'deposit_dids' => Security_Util::my_post('deposit_dids'),
			'is_person_loan_deduction' => Security_Util::my_post(
					'is_person_loan_deduction'),
			'person_loan_amount' => Security_Util::my_post('person_loan_amount'),
			'remark' => Security_Util::my_post('remark'),
			'payment_apply_deadline' => Security_Util::my_post(
					'payment_apply_deadline'),
			'statement' => Security_Util::my_post('statement'),
			'users' => Security_Util::my_post('users'),
			'pcid' => Security_Util::my_post('process'));

	if ($action === 'edit') {
		$fields['id'] = Security_Util::my_post('id');
		$fields['statement_del'] = Security_Util::my_post('statement_del');
	}

	$payment_media = new Payment_Media_Apply($fields);
	switch ($action) {
	case 'apply':
		return $payment_media->payment_media_apply();
	case 'edit':
		return $payment_media->payment_media_edit();
	}
}

function payment_media_deposit_audit() {
	$audit_result = Security_Util::my_post('audit_result');
	if (intval($audit_result) === 1) {
		//审核通过
		$fields = array('id' => Security_Util::my_post('id'),
				'audit_result' => Security_Util::my_post('audit_result'),
				'audit_content' => Security_Util::my_post('audit_content'),
				'media_name' => Security_Util::my_post('media_name'),
				'bank_name_select' => Security_Util::my_post('bank_name_select'),
				'bank_name' => Security_Util::my_post('bank_name'),
				'bank_account_select' => Security_Util::my_post(
						'bank_account_select'),
				'bank_account' => Security_Util::my_post('bank_account'),
				'payment_amount_plan' => Security_Util::my_post(
						'payment_amount_plan'),
				'payment_date' => Security_Util::my_post('payment_date'),
				'is_nim_pay_first' => Security_Util::my_post('is_nim_pay_first'),
				'is_rebate_deduction' => Security_Util::my_post(
						'is_rebate_deduction'),
				'rebate_amount' => Security_Util::my_post('rebate_amount'),
				'rebate_dids' => Security_Util::my_post('rebate_dids'),
				'is_deposit_deduction' => Security_Util::my_post(
						'is_deposit_deduction'),
				'deposit_dids' => Security_Util::my_post('deposit_dids'),
				'is_person_loan_deduction' => Security_Util::my_post(
						'is_person_loan_deduction'),
				'person_loan_amount' => Security_Util::my_post(
						'person_loan_amount'),
				'remark' => Security_Util::my_post('remark'),
				'payment_apply_deadline' => Security_Util::my_post(
						'payment_apply_deadline'),
				'statement' => Security_Util::my_post('statement'),
				'users' => Security_Util::my_post('users'),
				'statement_del' => Security_Util::my_post('statement_del'));
		$payment_media = new Payment_Media_Deposit_Apply($fields);
		return $payment_media->payment_media_audit_pass();
	} else {
		//审核驳回
		$payment_media = new Payment_Media_Deposit_Apply(
				array('id' => Security_Util::my_post('id'),
						'audit_result' => Security_Util::my_post('audit_result'),
						'audit_content' => Security_Util::my_post(
								'audit_content'),
						'reject_step' => Security_Util::my_post('reject_step')));
		return $payment_media->payment_media_audit_reject();
	}
}

function payment_media_audit() {
	$audit_result = Security_Util::my_post('audit_result');
	if (intval($audit_result) === 1) {
		//审核通过
		$fields = array('id' => Security_Util::my_post('id'),
				'audit_result' => Security_Util::my_post('audit_result'),
				'audit_content' => Security_Util::my_post('audit_content'),
				'media_name' => Security_Util::my_post('media_name'),
				'bank_name_select' => Security_Util::my_post('bank_name_select'),
				'bank_name' => Security_Util::my_post('bank_name'),
				'bank_account_select' => Security_Util::my_post(
						'bank_account_select'),
				'bank_account' => Security_Util::my_post('bank_account'),
				'payment_amount_plan' => Security_Util::my_post(
						'payment_amount_plan'),
				'payment_date' => Security_Util::my_post('payment_date'),
				'is_nim_pay_first' => Security_Util::my_post('is_nim_pay_first'),
				'is_rebate_deduction' => Security_Util::my_post(
						'is_rebate_deduction'),
				'rebate_amount' => Security_Util::my_post('rebate_amount'),
				'rebate_dids' => Security_Util::my_post('rebate_dids'),
				'is_deposit_deduction' => Security_Util::my_post(
						'is_deposit_deduction'),
				'deposit_dids' => Security_Util::my_post('deposit_dids'),
				'is_person_loan_deduction' => Security_Util::my_post(
						'is_person_loan_deduction'),
				'person_loan_amount' => Security_Util::my_post(
						'person_loan_amount'),
				'remark' => Security_Util::my_post('remark'),
				'payment_apply_deadline' => Security_Util::my_post(
						'payment_apply_deadline'),
				'statement' => Security_Util::my_post('statement'),
				'users' => Security_Util::my_post('users'),
				'statement_del' => Security_Util::my_post('statement_del'));
		$payment_media = new Payment_Media_Apply($fields);
		return $payment_media->payment_media_audit_pass();
	} else {
		//审核驳回
		$payment_media = new Payment_Media_Apply(
				array('id' => Security_Util::my_post('id'),
						'audit_result' => Security_Util::my_post('audit_result'),
						'audit_content' => Security_Util::my_post(
								'audit_content'),
						'reject_step' => Security_Util::my_post('reject_step')));
		return $payment_media->payment_media_audit_reject();
	}
}

function audit_full_payment_media() {
	$payment_person = new Payment_Media_Apply(
			array('auditvalue' => Security_Util::my_post('auditvalue'),
					'id' => Security_Util::my_post('id'),
					'remark' => Security_Util::my_post('remark'),
					'uid' => Security_Util::my_post('uid')));
	return $payment_person->audit_full_payment_media();
}

function audit_full_payment_media_deposit() {
	$payment_person = new Payment_Media_Deposit_Apply(
			array('auditvalue' => Security_Util::my_post('auditvalue'),
					'id' => Security_Util::my_post('id'),
					'remark' => Security_Util::my_post('remark'),
					'uid' => Security_Util::my_post('uid')));
	return $payment_person->audit_full_payment_media_deposit();
}

function audit_full_payment_person() {
	$payment_person = new Payment_Person_Apply(
			array('auditvalue' => Security_Util::my_post('auditvalue'),
					'id' => Security_Util::my_post('id'),
					'remark' => Security_Util::my_post('remark')));
	return $payment_person->audit_full_payment_person();
}

function audit_full_payment_person_deposit() {
	$payment_person = new Payment_Person_Apply_Deposit(
			array('auditvalue' => Security_Util::my_post('auditvalue'),
					'id' => Security_Util::my_post('id'),
					'remark' => Security_Util::my_post('remark')));
	return $payment_person->audit_full_payment_person_deposit();
}

function payment_deposit_person_gd() {
	$gdvalues = Security_Util::my_post('gdvalues');
	$gdvalues_array = array();
	if (!empty($gdvalues)) {
		$gdvalues = explode(',', $gdvalues);
		foreach ($gdvalues as $gdvalue) {
			if (!empty($gdvalue)) {
				//$gdvalue = explode('_', $gdvalue);
				if (count(explode('_', $gdvalue)) === 3) {
					$gdvalues_array[] = array('item' => $gdvalue,
							'gdamount' => Security_Util::my_post(
									'gdamount_' . $gdvalue));
				} else {
					return array('status' => 'error', 'message' => '归档数据选择有误');
				}
			}
		}
		$fields = array('id' => Security_Util::my_post('id'),
				'payment_id' => Security_Util::my_post('payment_id'),
				'gdvalues_array' => $gdvalues_array,
				'gdpaymentdate' => Security_Util::my_post('paymentdate'),
				'gdpaymentamount' => Security_Util::my_post('paymentamount'),
				'gdpaymenttype' => Security_Util::my_post('paymenttype'),
				'gdpaymentbank' => intval(Security_Util::my_post('paymenttype'))
						=== 2 ? Security_Util::my_post('paymentbank') : '');

		$payment = new Payment_Person_Apply_Deposit($fields);
		return $payment->payment_person_deposit_gd();
	} else {
		return array('status' => 'error', 'message' => '请选择需要归档的记录');
	}
}

function payment_media_deposit_gd() {
	$gdvalues = Security_Util::my_post('gdvalues');
	$gdvalues_array = array();
	if (!empty($gdvalues)) {
		$gdvalues = explode(',', $gdvalues);
		foreach ($gdvalues as $gdvalue) {
			$gdvalue = explode('_', $gdvalue);
			if (count($gdvalue) === 4) {
				$gdvalues_array[] = array(
						'item' => $gdvalue[0] . '_' . $gdvalue[1] . '_'
								. $gdvalue[2] . '_' . $gdvalue[3],
						'gdamount' => Security_Util::my_post(
								'gdamount_' . $gdvalue[1] . '_' . $gdvalue[2]));
			} else {
				return array('status' => 'error', 'message' => '归档数据选择有误');
			}
		}
		$fields = array('id' => Security_Util::my_post('id'),
				'payment_id' => Security_Util::my_post('payment_id'),
				'gdvalues_array' => $gdvalues_array,
				'gdpaymentdate' => Security_Util::my_post('paymentdate'),
				'gdpaymentamount' => Security_Util::my_post('paymentamount'),
				'gdpaymenttype' => Security_Util::my_post('paymenttype'),
				'gdpaymentbank' => intval(Security_Util::my_post('paymenttype'))
						=== 2 ? Security_Util::my_post('paymentbank') : '');

		$payment = new Payment_Media_Deposit_Apply($fields);
		return $payment->payment_media_deposit_gd();
	} else {
		return array('status' => 'error', 'message' => '请选择需要归档的记录');
	}
}

function payment_media_gd() {
	$gdvalues = Security_Util::my_post('gdvalues');
	$gdvalues_array = array();
	if (!empty($gdvalues)) {
		$gdvalues = explode(',', $gdvalues);
		foreach ($gdvalues as $gdvalue) {
			$gdvalue = explode('_', $gdvalue);
			if (count($gdvalue) === 3) {
				$gdvalues_array[] = array(
						'item' => $gdvalue[0] . '_' . $gdvalue[1] . '_'
								. $gdvalue[2],
						'gdamount' => Security_Util::my_post(
								'gdamount_' . $gdvalue[1] . '_' . $gdvalue[2]));
			} else {
				return array('status' => 'error', 'message' => '归档数据选择有误');
			}
		}
		$fields = array('id' => Security_Util::my_post('id'),
				'payment_id' => Security_Util::my_post('payment_id'),
				'gdvalues_array' => $gdvalues_array,
				'gdpaymentdate' => Security_Util::my_post('paymentdate'),
				'gdpaymentamount' => Security_Util::my_post('paymentamount'),
				'gdpaymenttype' => Security_Util::my_post('paymenttype'),
				'gdpaymentbank' => intval(Security_Util::my_post('paymenttype'))
						=== 2 ? Security_Util::my_post('paymentbank') : '');

		$payment = new Payment_Media_Apply($fields);
		return $payment->payment_media_gd();
	} else {
		return array('status' => 'error', 'message' => '请选择需要归档的记录');
	}
}

function payment_person_gd() {
	$gdvalues = Security_Util::my_post('gdvalues');
	$gdvalues_array = array();
	if (!empty($gdvalues)) {
		$gdvalues = explode(',', $gdvalues);
		foreach ($gdvalues as $gdvalue) {
			$gdvalue = explode('_', $gdvalue);
			if (count($gdvalue) === 3) {
				$gdvalues_array[] = array(
						'item' => $gdvalue[0] . '_' . $gdvalue[1] . '_'
								. $gdvalue[2],
						'gdamount' => Security_Util::my_post(
								'gdamount_' . $gdvalue[1] . '_' . $gdvalue[2]));
			} else {
				return array('status' => 'error', 'message' => '归档数据选择有误');
			}
		}
		$fields = array('id' => Security_Util::my_post('id'),
				'payment_id' => Security_Util::my_post('payment_id'),
				'gdvalues_array' => $gdvalues_array,
				'gdpaymentdate' => Security_Util::my_post('paymentdate'),
				'gdpaymentamount' => Security_Util::my_post('paymentamount'),
				'gdpaymenttype' => Security_Util::my_post('paymenttype'),
				'gdpaymentbank' => intval(Security_Util::my_post('paymenttype'))
						=== 2 ? Security_Util::my_post('paymentbank') : '');

		$payment = new Payment_Person_Apply($fields);
		return $payment->payment_person_gd();
	} else {
		return array('status' => 'error', 'message' => '请选择需要归档的记录');
	}

}

function receive_invoice_source_fix() {
	$invoice = new Finance_Receive_Invoice(
			array('sourceid' => Security_Util::my_post('sourceid'),
					'actype' => Security_Util::my_post('actype')));
	return $invoice->receive_invoice_source_fix();
}

function virtual_invoice_payment_share($action = 'payment_share') {
	$itemids = Security_Util::my_post('itemids');
	$itemids = explode(',', $itemids);
	$itemids_array = array();
	foreach ($itemids as $itemid) {
		if (!empty($itemid)) {
			$itemids_array[$itemid] = array(
					'amount' => Security_Util::my_post('amount_' . $itemid),
					'tax' => Security_Util::my_post('tax_' . $itemid),
					'sumamount' => Security_Util::my_post(
							'sumamount_' . $itemid),
					'tax_rate' => Security_Util::my_post('taxrate_' . $itemid));
		}
	}

	$fields = array('itemids_array' => $itemids_array);
	if ($action === 'payment_update') {
		$fields['id'] = Security_Util::my_post('id');
	}
	$invoice = new Virtual_Invoice($fields);
	return $invoice->virtual_invoice_payment_share($action);
}

function receive_invoice_source_payment_share($action = 'payment_share') {
	$itemids = Security_Util::my_post('itemids');
	$itemids = explode(',', $itemids);
	$itemids_array = array();
	foreach ($itemids as $itemid) {
		if (!empty($itemid)) {
			$itemids_array[$itemid] = array(
					'amount' => Security_Util::my_post('amount_' . $itemid),
					'tax' => Security_Util::my_post('tax_' . $itemid),
					'sumamount' => Security_Util::my_post(
							'sumamount_' . $itemid));
		}
	}

	$fields = array('ids' => Security_Util::my_post('ids'),
			'itemids_array' => $itemids_array);
	if ($action === 'payment_update') {
		$fields['id'] = Security_Util::my_post('id');
	}
	$invoice = new Finance_Receive_Invoice($fields);
	return $invoice->receive_invoice_source_payment_share($action);
}

function virtual_invoice_share($action = 'share') {
	$pids = Security_Util::my_post('pids');
	$pids = explode(',', $pids);
	$pids_array = array();
	$pids_sumamount = array();
	foreach ($pids as $pid) {
		if (!empty($pid)) {
			$pids_array[] = $pid;
		}
	}
	if (empty($pids_array)) {
		return array('status' => 'error', 'message' => '分配执行单有误');
	} else {
		foreach ($pids_array as $pa) {
			$pids_sumamount[$pa] = array(
					'amount' => Security_Util::my_post('amount_' . $pa),
					'tax' => Security_Util::my_post('tax_' . $pa),
					'sumamount' => Security_Util::my_post('sumamount_' . $pa),
					'tax_rate' => Security_Util::my_post('taxrate_' . $pa));
		}
	}
	$fields = array('pids_array' => $pids_array,
			'pids_sumamount' => $pids_sumamount);
	if ($action === 'update') {
		$fields['id'] = Security_Util::my_post('id');
	}
	$invoice = new Virtual_Invoice($fields);
	return $invoice->virtual_invoice_share_pid($action);
}

function receive_invoice_source_share($action = 'share') {
	$pids = Security_Util::my_post('pids');
	$pids = explode(',', $pids);
	$pids_array = array();
	$pids_sumamount = array();
	foreach ($pids as $pid) {
		if (!empty($pid)) {
			$pids_array[] = $pid;
		}
	}
	if (empty($pids_array)) {
		return array('status' => 'error', 'message' => '分配执行单有误');
	} else {
		foreach ($pids_array as $pa) {
			$pids_sumamount[$pa] = array(
					'amount' => Security_Util::my_post('amount_' . $pa),
					'tax' => Security_Util::my_post('tax_' . $pa),
					'sumamount' => Security_Util::my_post('sumamount_' . $pa));
		}
	}
	$fields = array('ids' => Security_Util::my_post('ids'),
			'pids_array' => $pids_array, 'pids_sumamount' => $pids_sumamount);
	if ($action === 'update') {
		$fields['id'] = Security_Util::my_post('id');
	}
	$invoice = new Finance_Receive_Invoice($fields);
	return $invoice->receive_invoice_source_share($action);
}

function meida_refund_add() {
	$pids_array = array();
	$pids = explode(',', Security_Util::my_post('pids'));
	foreach ($pids as $pid) {
		if (!empty($pid)) {
			$pids_array[$pid] = abs(Security_Util::my_post('refund_' . $pid));
		}
	}
	$fields = array('media_name' => Security_Util::my_post('media_name'),
			'receivables_type' => Security_Util::my_post('receivables_type'),
			'nimbank_id' => Security_Util::my_post('nimbank_id'),
			'refund_amount' => Security_Util::my_post('refund_amount'),
			'refund_type' => Security_Util::my_post('refund_type'),
			'refund_date' => Security_Util::my_post('refund_date'),
			'remark' => Security_Util::my_post('remark'),
			'pids_array' => $pids_array);
	$refund = new Finance_Refund_Media($fields);
	return $refund->add_media_refund();
}

function finance_hedge() {
	$receive_pids = explode(',', Security_Util::my_post('receive_pids'));
	$pay_pids = explode(',', Security_Util::my_post('pay_pids'));
	$receive = array();
	$pay = array();
	foreach ($receive_pids as $receive_pid) {
		if (!empty($receive_pid)) {
			$receive[$receive_pid] = Security_Util::my_post(
					'receive_' . $receive_pid);
		}
	}
	foreach ($pay_pids as $pay_pid) {
		if (!empty($pay_pid)) {
			$pay[$pay_pid] = Security_Util::my_post('pay_' . $pay_pid);
		}
	}
	//var_dump($receive);
	//var_dump($pay);
	$hedge = new Finance_Hedge(array('receive' => $receive, 'pay' => $pay));
	return $hedge->add_finance_hedge();
}

function finance_hedge_confirm() {
	$receive_pids = explode(',', Security_Util::my_post('receive_pids'));
	$pay_pids = explode(',', Security_Util::my_post('pay_pids'));
	$receive = array();
	$pay = array();
	foreach ($receive_pids as $receive_pid) {
		if (!empty($receive_pid)) {
			$receive[$receive_pid] = Security_Util::my_post(
					'receive_' . $receive_pid);
		}
	}
	foreach ($pay_pids as $pay_pid) {
		if (!empty($pay_pid)) {
			$pay[$pay_pid] = Security_Util::my_post('pay_' . $pay_pid);
		}
	}
	$hedge = new Finance_Hedge(
			array('receive' => $receive, 'pay' => $pay,
					'id' => Security_Util::my_post('id')));
	return $hedge->confirm_finance_hedge();
}

function sendPayfirstRemindEmail() {
	$payfirst = new Payment_Nimpayfirst(
			array('pids' => Security_Util::my_post('pids'),
					'to_email' => Security_Util::my_post('to_email'),
					'cc_email' => Security_Util::my_post('cc_email')));
	return $payfirst->sendPayfirstRemindEmail();
}

function finance_payment_pid_transfer() {
	$receive_array = array();
	$pay_array = array();

	$receive_pids = explode(',', Security_Util::my_post('receive_pids'));
	$pay_pids = explode(',', Security_Util::my_post('pay_pids'));

	foreach ($receive_pids as $rp) {
		if (!empty($rp)) {
			$receive_array[] = array('pid' => $rp,
					'amount' => abs(Security_Util::my_post('receive_' . $rp)));
		}
	}

	foreach ($pay_pids as $p) {
		if (!empty($p)) {
			$pay_array[] = array('pid' => $p,
					'amount' => abs(Security_Util::my_post('pay_' . $p)));
		}
	}

	$pid_transfer = new Payment_Pid_Edit_Transfer(
			array('receive_array' => $receive_array, 'pay_array' => $pay_array));
	return $pid_transfer->getPaymentPidTransferResult();
}

function payment_deposit_2_pid() {
	$in_array = array();
	$out_array = array();

	$ins = explode(',', Security_Util::my_post('ins'));
	$outs = explode(',', Security_Util::my_post('outs'));

	foreach ($ins as $in) {
		if (!empty($in)) {
			$in = explode('_', $in);
			$in_array[] = array('in_pid' => $in[0], 'in_paycostid' => $in[1],
					'amount' => abs(
							Security_Util::my_post(
									'in_' . $in[0] . '_' . $in[1])));
		}
	}

	foreach ($outs as $out) {
		if (!empty($out)) {
			$out_array[] = array('deposit_gd_id' => $out,
					'amount' => abs(Security_Util::my_post('out_' . $out)));
		}
	}

	$transfer = new Payment_Deposit_Transfer(
			array('in_array' => $in_array, 'out_array' => $out_array));
	return $transfer->getPaymentDeposit2PidResult();
}

function payment_deposit_2_deposit() {
	$in_array = array();
	$out_array = array();

	$ins = explode(',', Security_Util::my_post('ins'));
	$outs = explode(',', Security_Util::my_post('outs'));

	foreach ($ins as $in) {
		if (!empty($in)) {
			$in_array[] = array('deposit_gd_id' => $in,
					'amount' => abs(Security_Util::my_post('in_' . $in)));
		}
	}

	foreach ($outs as $out) {
		if (!empty($out)) {
			$out_array[] = array('deposit_gd_id' => $out,
					'amount' => abs(Security_Util::my_post('out_' . $out)));
		}
	}

	$transfer = new Payment_Deposit_Transfer(
			array('in_array' => $in_array, 'out_array' => $out_array));
	return $transfer->getPaymentDeposit2DepositResult();
}

function rebate_recover_need_invoice() {
	$itemids = Security_Util::my_post('itemids');
	$itemids = explode(',', $itemids);
	$item_rebate = array();
	foreach ($itemids as $itemid) {
		if (!empty($itemid)) {
			$item_rebate[$itemid] = Security_Util::my_post(
					'rebate_p_' . $itemid);
		}
	}
	$rebate = new Finance_Rebate(array('item_rebate' => $item_rebate));
	return $rebate->getRebateRecoverNeedInvoiceResult();
}

function rebate_no_need_invoice() {
	$itemids = Security_Util::my_post('itemids');
	$itemids = explode(',', $itemids);
	$item_rebate = array();
	foreach ($itemids as $itemid) {
		if (!empty($itemid)) {
			$item_rebate[$itemid] = Security_Util::my_post(
					'rebate_p_' . $itemid);
		}
	}

	$isthesame = Security_Util::my_post('isthesame');
	$fields = array('item_rebate' => $item_rebate, 'isthesame' => $isthesame);

	if ($isthesame !== NULL && intval($isthesame) === 0) {
		$pids = Security_Util::my_post('pids');
		$pids = explode(',', $pids);
		$pid_array = array();
		foreach ($pids as $pid) {
			if (!empty($pid)) {
				$pid_array[$pid] = Security_Util::my_post('amount_' . $pid);
			}
		}
		$fields['pid_array'] = $pid_array;
	}

	$rebate = new Finance_Rebate($fields);
	return $rebate->getRebatenNoNeedInvoiceResult();
}

function rebate_invoice_apply($action = 'add') {
	$pids = Security_Util::my_post('pids');
	$pids = explode(',', $pids);
	$pid_array = array();
	foreach ($pids as $pid) {
		if (!empty($pid)) {
			$pid_array[$pid] = Security_Util::my_post('amount_' . $pid);
		}
	}
	$fields = array('media_name' => Security_Util::my_post('media_name'),
			'media_payment_type' => Security_Util::my_post('media_payment_type'),
			'media_rebate_rate' => Security_Util::my_post('media_rebate_rate'),
			'relation_type' => Security_Util::my_post('relation_type'),
			'invoice_type' => Security_Util::my_post('invoice_type'),
			'd1' => Security_Util::my_post('d1'),
			'd2' => Security_Util::my_post('d2'),
			'd3' => Security_Util::my_post('d3'),
			'title' => Security_Util::my_post('title'),
			'content' => Security_Util::my_post('content'),
			'remark' => Security_Util::my_post('remark'),
			'pid_array' => $pid_array);
	if ($action === 'update') {
		$fields['id'] = Security_Util::my_post('id');
	}
	$rebate = new Finance_Rebate($fields);
	return $rebate->getRebateInvoiceApplyResult($action);
}

function rebate_invoice_audit($action = 'pass') {
	$fields = array('id' => Security_Util::my_post('id'));
	if ($action === 'reject') {
		$fields['auditmsg'] = Security_Util::my_post('auditmsg');
	}
	$rebate = new Finance_Rebate($fields);
	return $rebate->getRebateInvoiceAuditResult($action);
}

function rebate_invoice_gd($action = 'invoice') {
	$fields = array('id' => Security_Util::my_post('id'));
	if ($action === 'invoice') {
		$fields['date'] = Security_Util::my_post('i_date');
		$fields['amount'] = Security_Util::my_post('i_amount');
		$fields['number'] = Security_Util::my_post('i_number');
	} else if ($action === 'collection') {
		$fields['date'] = Security_Util::my_post('m_date');
		$fields['amount'] = Security_Util::my_post('m_amount');
		$fields['bank'] = Security_Util::my_post('m_bank');
	}
	$rebate = new Finance_Rebate($fields);
	return $rebate->getRebateInvoiceGDResult($action);
}

function rebate_invoice_receive2pay() {
	$pids = Security_Util::my_post('pids');
	$pids = explode(',', $pids);
	$pid_array = array();
	foreach ($pids as $pid) {
		if (!empty($pid)) {
			$pid_array[$pid] = Security_Util::my_post('amount_' . $pid);
		}
	}
	$rebate = new Finance_Rebate(
			array('id' => Security_Util::my_post('id'),
					'pid_array' => $pid_array));
	return $rebate->getRebateInvoiceReceive2PayResult();
}

function rebate_invoice_pay2receive() {
	$pids = Security_Util::my_post('pids');
	$pids = explode(',', $pids);
	$pid_array = array();
	foreach ($pids as $pid) {
		if (!empty($pid)) {
			$pid_array[$pid] = Security_Util::my_post('amount_' . $pid);
		}
	}
	$rebate = new Finance_Rebate(
			array('id' => Security_Util::my_post('id'),
					'pid_array' => $pid_array));
	return $rebate->getRebateInvoicePay2ReceiveResult();
}

function settle_account() {
	$settle = new Finance_Settle_Account();
	return $settle->getRSettleAccountResult();
}

function do_media_short($action) {
	$fields = array('media_short' => Security_Util::my_post('media_short'));
	if ($action === 'update') {
		$fields['id'] = Security_Util::my_post('id');
	}
	$ms = new Supplier_Short($fields);
	return $ms->getDoMediaShort($action);
}

function do_media_rebate($action) {
	$fields = array('supplier_id' => Security_Util::my_post('supplier_name'),
			'supplier_short_id' => Security_Util::my_post('media_short'),
			'category_id' => Security_Util::my_post('categorytype'),
			'industry_id' => Security_Util::my_post('industrytype'),
			'rebate' => Security_Util::my_post('rebate'));
	if ($action === 'update') {
		$fields['id'] = Security_Util::my_post('id');
	}
	$sr = new Setting_Rebate($fields);
	return $sr->getDoSettingRebate($action);
}

function reabte_rate_export(){
	$sr = new Setting_Rebate();
	$sr->exportRebateRate();
}

function supplier_type_export(){
	$s = new Supplier(array('exportype'=>Security_Util::my_post('exportype')));
	$s->exportSupplierType();
}

function exportUnAuditedInvoice(){
	$invoice = new Invoice_List(array('d' => 1));
	$invoice->exportUnAuditedInvoice();
}
