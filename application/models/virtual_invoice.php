<?php
class Virtual_Invoice extends User {
	private $id;
	private $pids_array;
	private $pids_sumamount;
	private $itemids_array;
	private $has_finance_virtual_invoice_permission = FALSE;

	public function __construct($fields = array()) {
		parent::__construct();
		if (!empty($fields)) {
			foreach ($this as $key => $value) {
				if ($fields[$key] !== NULL
						&& !in_array($key,
								array('has_finance_virtual_invoice_permission'),
								TRUE)) {
					$this->$key = $fields[$key];
				}
			}
		}
		if (in_array($this->getUsername(),
				$GLOBALS['manager_finance_permission'], TRUE)
				|| intval($this->getBelong_dep()) === 2) {
			$this->has_finance_virtual_invoice_permission = TRUE;
		}
	}

	public function getVirtualInvoiceShareHtml() {
		if ($this->has_finance_virtual_invoice_permission) {
			$buf = file_get_contents(
					TEMPLATE_PATH
							. 'finance/receiveinvoice/finance_receive_invoice_virtual_invoice_share.tpl');
			return str_replace(
					array('[LEFT]', '[TOP]', '[VCODE]', '[BASE_URL]'),
					array($this->get_left_html(), $this->get_top_html(),
							$this->get_vcode(), BASE_URL), $buf);
		} else {
			return User::no_permission();
		}
	}

	public function getVirtualInvoiceSharePaymentHtml() {
		if ($this->has_finance_virtual_invoice_permission) {
			$buf = file_get_contents(
					TEMPLATE_PATH
							. 'finance/receiveinvoice/finance_receive_invoice_virtual_invoice_share_payment.tpl');
			return str_replace(
					array('[LEFT]', '[TOP]', '[VCODE]', '[BASE_URL]'),
					array($this->get_left_html(), $this->get_top_html(),
							$this->get_vcode(), BASE_URL), $buf);
		} else {
			return User::no_permission();
		}
	}

	private function validate_form_value($action) {
		$errors = array();
		if (!in_array($action,
				array('share', 'payment_share', 'update', 'payment_update'),
				TRUE)) {
			$errors[] = NO_RIGHT_TO_DO_THIS;
		} else {

			if ($action === 'update' || $action === 'payment_update') {
				if (!self::validate_id(intval($this->id))) {
					$errors[] = '分配信息选择有误';
				}
			}

			$pids_sumamount = $this->pids_sumamount;
			if (!empty($pids_sumamount)) {
				foreach ($pids_sumamount as $ps) {
					if (!self::validate_money($ps['amount'])) {
						$errors[] = '成本不是有效的金额值';
						break;
					}

					if (!self::validate_money($ps['tax'])) {
						$errors[] = '进项不是有效的金额值';
						break;
					}

					if (!self::validate_money($ps['sumamount'])) {
						$errors[] = '价税合计不是有效的金额值';
						break;
					}

					if (!(Validate_Util::my_is_float($ps['taxrate'])
							&& $ps['taxrate'] > 0 && $ps['taxrate'] < 100)) {
						$errors[] = '税率不是有效的百分比';
						break;
					}
				}
			} else {
				$errors[] = '分配项目不能为空';
			}

		}

		if (empty($errors)) {
			return TRUE;
		}
		$this->errors = $errors;
		unset($errors);
		return FALSE;
	}

	public function virtual_invoice_share_pid() {
		if ($this->has_finance_virtual_invoice_permission) {
			if ($this->validate_form_value($action)) {
				$success = TRUE;
				$error = '';
				$this->db->query('BEGIN');

				if ($action === 'update') {
					$row = $this->db
							->get_row(
									'SELECT invoice_number FROM finance_receiveinvoice_virtual_invoice WHERE id='
											. intval($this->id) . ' AND isok=1');
					if ($row !== NULL) {
						$invoice_number = $row->invoice_number;

						$result = $this->db
								->query(
										'UPDATE finance_receiveinvoice_virtual_invoice_pid_list SET isok=-1 WHERE virtual_invoice_id='
												. intval($this->id));
						if ($result === FALSE) {
							$success = FALSE;
							$error = '分配执行单失败，错误代码3';
						}
					} else {
						$success = FALSE;
						$error = '没有该虚拟发票';
					}
				} else {
					$invoice_number = $this
							->getSequence(
									date('y', time()) . $this->getCity_show()
											. date('m', time()) . 'VI');
				}

				if ($success) {
					$pids_array = $this->pids_array;
					$pids_sumamount = $this->pids_sumamount;
					$sum_amount;
					$sum_tax;

					$subsql = array();

					foreach ($pids_array as $pa) {
						$amount = $pids_sumamount[$pa]['amount'];
						$tax = $pids_sumamount[$pa]['tax'];
						$sumamount = $pids_sumamount[$pa]['sumamount'];
						$tax_rate = $pids_sumamount[$pa]['taxrate'];

						$sum_amount += $amount;
						$sum_tax += $tax;

						$pa = explode('_', $pa);
						$subsql[] = '("[virtual_invoice_id]","' . $pa[0] . '",'
								. $pa[1] . ',' . $amount . ',' . $tax_rate
								. ',' . $tax . ',' . $sumamount . ',1)';
					}

					//finance_receiveinvoice_virtual_invoice
					if ($action === 'share') {
						$result = $this->db
								->query(
										'INSERT INTO finance_receiveinvoice_virtual_invoice(invoice_number,amount,tax,sum_amount,isok,addtime) VALUE("'
												. $invoice_number . '",'
												. $sum_amount . ',' . $sum_tax
												. ','
												. ($sum_amount + $sum_tax)
												. ',1,now())');
					} else {
						$result = $this->db
								->query(
										'UPDATE finance_receiveinvoice_virtual_invoice SET amount='
												. $sum_amount . ',tax='
												. $sum_tax . ',sum_amount='
												. ($sum_amount + $sum_tax)
												. ' WHERE id='
												. intval($this->id));
					}

					if ($result === FALSE) {
						$success = FALSE;
						$error = '分配执行单失败，错误代码1';
					} else {
						$virtual_invoice_id = $action === 'share' ? $this->db
										->insert_id : intval($this->id);
						$result = $this->db
								->query(
										'INSERT INTO finance_receiveinvoice_virtual_invoice_pid_list(virtual_invoice_id,pid,paycostid,amount,tax_rate,tax,sum_amount,isok) VALUES'
												. str_replace(
														'"[virtual_invoice_id]"',
														$virtual_invoice_id,
														implode(',', $subsql)));
						if ($result === FALSE) {
							$success = FALSE;
							$error = '分配执行单失败，错误代码2';
						}
					}
				}

				if ($success) {
					$this->db->query('COMMIT');
				} else {
					$this->db->query('ROLLBACK');
				}
				return array('status' => $success ? 'success' : 'error',
						'message' => $success ? '分配执行单成功' : $error);

			} else {
				return array('status' => 'error', 'message' => $this->errors);
			}
		} else {
			return array('status' => 'error', 'message' => NO_RIGHT_TO_DO_THIS);
		}
	}
	
	public function virtual_invoice_payment_share($action = 'payment_share'){
		if ($this->has_finance_virtual_invoice_permission) {
			if ($this->validate_form_value($action)) {
				$success = TRUE;
				$error = '';
				$this->db->query('BEGIN');

				$invoice_number = $this
							->getSequence(
									date('y', time()) . $this->getCity_show()
											. date('m', time()) . 'VI');
											
				
				
				
				
				
				
				
				
				if ($action === 'payment_update') {
					$row = $this->db
							->get_row(
									'SELECT pid_list_ids FROM finance_receiveinvoice_source_pid WHERE id='
											. intval($this->id) . ' AND isok=1');
					if ($row === NULL) {
						$success = TRUE;
						$error = '没有该分配信息或状态无法更新';
					} else {
						$pid_list_ids = substr($row->pid_list_ids, 1,
								strlen($row->pid_list_ids) - 2);
						$result = $this->db
								->query(
										'UPDATE finance_receiveinvoice_pid_list SET isok=-1 WHERE id IN('
												. str_replace('^', ',',
														$pid_list_ids) . ')');
						if ($result === FALSE) {
							$success = FALSE;
							$error = '分配付款申请失败，错误代码3';
						} else {
							$result = $this->db
									->query(
											'UPDATE finance_receiveinvoice_source_pid SET isok=-1 WHERE id='
													. intval($this->id));
							if ($result === FALSE) {
								$success = FALSE;
								$error = '分配付款申请失败，错误代码4';
							}
						}
					}
				}

				if ($success) {
					$itemids_array = $this->itemids_array;
					$pid_list_array = array();
					foreach ($itemids_array as $key => $value) {
						$key = explode('_', $key);
						if ($key[2] === 'p') {
							//个人申请
							$sql = 'INSERT INTO finance_receiveinvoice_pid_list(pid,paycostid,amount,tax,sum_amount,isok,apply_id,apply_list_id,apply_type) SELECT pid,paycostid,'
									. $value['amount'] . ',' . $value['tax']
									. ',' . $value['sumamount'] . ',1,'
									. $key[1] . ',' . $key[0] . ',"' . $key[2]
									. '" FROM finance_payment_person_apply_list WHERE id='
									. $key[0];
						} else if ($key[2] === 'm') {
							//批量申请
							$sql = 'INSERT INTO finance_receiveinvoice_pid_list(pid,paycostid,amount,tax,sum_amount,isok,apply_id,apply_list_id,apply_type) SELECT pid,paycostid,'
									. $value['amount'] . ',' . $value['tax']
									. ',' . $value['sumamount'] . ',1,'
									. $key[1] . ',' . $key[0] . ',"' . $key[2]
									. '" FROM finance_payment_media_apply_list WHERE id='
									. $key[0];
						}

						$result = $this->db->query($sql);
						if ($result === FALSE) {
							$success = FALSE;
							$error = '分配付款申请失败，错误代码1';
							break;
						} else {
							$pid_list_array[] = $this->db->insert_id;
						}
					}

					if ($success) {
						$source_ids = array();
						$ids = explode(',', $this->ids);
						foreach ($ids as $id) {
							if (!empty($id)) {
								$source_ids[] = $id;
							}
						}

						$result = $this->db
								->query(
										'INSERT INTO finance_receiveinvoice_source_pid(source_ids,pid_list_ids,sharetype,isok,addtime) VALUE("^'
												. implode('^', $source_ids)
												. '^","^'
												. implode('^', $pid_list_array)
												. '^",2,1,now())');
						if ($result === FALSE) {
							$success = FALSE;
							$error = '分配付款申请失败，错误代码2';
						}
					}
				}

				if ($success) {
					$this->db->query('COMMIT');
				} else {
					$this->db->query('ROLLBACK');
				}
				return array('status' => $success ? 'success' : 'error',
						'message' => $success ? '分配付款申请成功' : $error);
			}
			return array('status' => 'error', 'message' => $this->errors);
		}
		return array('status' => 'error', 'message' => NO_RIGHT_TO_DO_THIS);
	}
}
