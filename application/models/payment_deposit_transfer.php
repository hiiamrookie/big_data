<?php
class Payment_Deposit_Transfer extends User {
	private $in_array = array();
	private $out_array = array();
	private $errors = array();
	private $has_payment_deposit_transfer_permission = FALSE;

	public function __construct($fields = array()) {
		parent::__construct();
		if (!empty($fields)) {
			foreach ($this as $key => $value) {
				if ($fields[$key] !== NULL
						&& !in_array($key,
								array('has_payment_deposit_transfer_permission'),
								TRUE)) {
					$this->$key = $fields[$key];
				}
			}
		}
		if (in_array($this->getUsername(),
				$GLOBALS['manager_finance_permission'], TRUE)
				|| intval($this->getBelong_dep()) === 2) {
			$this->has_payment_deposit_transfer_permission = TRUE;
		}
	}

	public function getDeposit2DepositHtml() {
		if ($this->has_payment_deposit_transfer_permission) {
			$buf = file_get_contents(
					TEMPLATE_PATH
							. 'finance/payment/payment_deposit_2_deposit.tpl');
			return str_replace(
					array('[LEFT]', '[TOP]', '[VCODE]', '[BASE_URL]'),
					array($this->get_left_html(), $this->get_top_html(),
							$this->get_vcode(), BASE_URL), $buf);
		} else {
			User::no_permission();
		}
	}

	public function getDeposit2PidHtml() {
		if ($this->has_payment_deposit_transfer_permission) {
			$buf = file_get_contents(
					TEMPLATE_PATH . 'finance/payment/payment_deposit_2_pid.tpl');
			return str_replace(
					array('[LEFT]', '[TOP]', '[VCODE]', '[BASE_URL]'),
					array($this->get_left_html(), $this->get_top_html(),
							$this->get_vcode(), BASE_URL), $buf);
		} else {
			User::no_permission();
		}
	}

	private function validate_form_value($action) {
		$errors = array();
		if (in_array($action, array('deposit2deposit', 'deposit2pid'), TRUE)) {
			//if ($action === 'deposit2deposit') {
			$sum_in = 0;
			$sum_out = 0;
			if (!self::validate_field_not_empty($this->in_array)
					|| !self::validate_field_not_null($this->in_array)) {
				$errors[] = '转入数据不能为空';
			} else {
				foreach ($this->in_array as $val) {
					$sum_in += $val['amount'];
				}
			}

			if (!self::validate_field_not_empty($this->out_array)
					|| !self::validate_field_not_null($this->out_array)) {
				$errors[] = '转出数据不能为空';
			} else {
				foreach ($this->out_array as $val) {
					$sum_out += $val['amount'];
				}
			}

			if ($sum_in !== $sum_out) {
				$errors[] = '转入转出数据必须匹配';
			}

			/*	
			} else if ($action === 'deposit2pid') {
			    $sum_in = 0;
			    $sum_out = 0;
			    if (!self::validate_field_not_empty($this->in_array)
			            || !self::validate_field_not_null($this->in_array)) {
			        $errors[] = '转入数据不能为空';
			    } else {
			        foreach ($this->in_array as $val) {
			            $sum_in += $val['amount'];
			        }
			    }
			
			    if (!self::validate_field_not_empty($this->out_array)
			            || !self::validate_field_not_null($this->out_array)) {
			        $errors[] = '转出数据不能为空';
			    } else {
			        foreach ($this->out_array as $val) {
			            $sum_out += $val['amount'];
			        }
			    }
			
			    if ($sum_in !== $sum_out) {
			        $errors[] = '转入转出数据必须匹配';
			    }
			}
			 */
		} else {
			$errors[] = NO_RIGHT_TO_DO_THIS;
		}

		if (empty($errors)) {
			return TRUE;
		}
		$this->errors = $errors;
		unset($errors);
		return FALSE;
	}

	public function getPaymentDeposit2DepositResult() {
		if ($this->has_payment_deposit_transfer_permission) {
			if ($this->validate_form_value('deposit2deposit')) {
				$success = TRUE;
				$error = '';
				$this->db->query('BEGIN');

				$subsql = array();
				foreach ($this->in_array as $r) {
					$subsql[] = '(' . $r['deposit_gd_id'] . ',' . $r['amount']
							. ',2,1,now(),1)';
				}

				foreach ($this->out_array as $r) {
					$subsql[] = '(' . $r['deposit_gd_id'] . ',' . $r['amount']
							. ',1,1,now(),1)';
				}

				if (!empty($subsql)) {
					$result = $this->db
							->query(
									'INSERT INTO finance_payment_deposit_transfer(deposit_gd_id,amount,type,transfer_type,addtime,isok) VALUES'
											. implode(',', $subsql));
					if ($result === FALSE) {
						$success = FALSE;
						$error = '保证金转保证金失败';
					}
				} else {
					$success = FALSE;
					$error = '保证金转移选择不能为空';
				}

				if ($success) {
					$this->db->query('COMMIT');
				} else {
					$this->db->query('ROLLBACK');
				}

				return array('status' => $success ? 'success' : 'error',
						'message' => $success ? '保证金转保证金成功' : $error);
			}
			return array('status' => 'error', 'message' => $this->errors);
		}
		return array('status' => 'error', 'message' => NO_RIGHT_TO_DO_THIS);
	}

	public function getPaymentDeposit2PidResult() {
		if ($this->has_payment_deposit_transfer_permission) {
			if ($this->validate_form_value('deposit2pid')) {
				$success = TRUE;
				$error = '';
				$this->db->query('BEGIN');

				$subsql1 = array();
				$subsql2 = array();
				foreach ($this->in_array as $r) {
					$subsql1[] = '(' . $r['amount'] . ',2,2,"' . $r['in_pid']
							. '",' . $r['in_paycostid'] . ',now(),1)';
				}

				foreach ($this->out_array as $r) {
					$subsql2[] = '(' . $r['deposit_gd_id'] . ',' . $r['amount']
							. ',1,2,now(),1)';
				}

				if (!empty($subsql1)) {
					$result1 = $this->db
							->query(
									'INSERT INTO finance_payment_deposit_transfer(amount,type,transfer_type,in_pid,in_paycostid,addtime,isok) VALUES'
											. implode(',', $subsql1));
											//var_dump('INSERT INTO finance_payment_deposit_transfer(amount,type,transfer_type,in_pid,in_paycostid,addtime,isok) VALUES'
											//. implode(',', $subsql1));
					if ($result1 === FALSE) {
						$success = FALSE;
						$error = '保证金转执行单失败，错误代码1';
					}
				} else {
					$success = FALSE;
					$error = '保证金转入选择不能为空';
				}

				if ($success) {
					if (!empty($subsql2)) {
						$result2 = $this->db
								->query(
										'INSERT INTO finance_payment_deposit_transfer(deposit_gd_id,amount,type,transfer_type,addtime,isok) VALUES'
												. implode(',', $subsql2));
						if ($result2 === FALSE) {
							$success = FALSE;
							$error = '保证金转执行单失败，错误代码2';
						}
					} else {
						$success = FALSE;
						$error = '保证金转出选择不能为空';
					}
				}

				if ($success) {
					$this->db->query('COMMIT');
				} else {
					$this->db->query('ROLLBACK');
				}

				return array('status' => $success ? 'success' : 'error',
						'message' => $success ? '保证金转执行单成功' : $error);
			}
			return array('status' => 'error', 'message' => $this->errors);
		}
		return array('status' => 'error', 'message' => NO_RIGHT_TO_DO_THIS);
	}
}
