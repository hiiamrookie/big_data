<?php
class Payment_Pid_Edit_Transfer extends User {
	private $receive_array;
	private $pay_array;
	private $errors = array();
	private $has_payment_pid_editransfer_permission = FALSE;

	public function __construct($fields = array()) {
		parent::__construct();
		if (!empty($fields)) {
			foreach ($this as $key => $value) {
				if ($fields[$key] !== NULL
						&& !in_array($key,
								array('has_payment_pid_editransfer_permission'),
								TRUE)) {
					$this->$key = $fields[$key];
				}
			}
		}
		if (in_array($this->getUsername(),
				$GLOBALS['manager_finance_permission'], TRUE)
				|| intval($this->getBelong_dep()) === 2) {
			$this->has_payment_pid_editransfer_permission = TRUE;
		}
	}

	public function get_payment_pid_edit_html() {
		if ($this->has_payment_pid_editransfer_permission) {
			$buf = file_get_contents(
					TEMPLATE_PATH . 'finance/payment/payment_pidedit.tpl');
			return str_replace(array('[LEFT]', '[TOP]', '[VCODE]','[BASE_URL]'),
					array($this->get_left_html(), $this->get_top_html(),$this->get_vcode(),
							BASE_URL), $buf);
		} else {
			User::no_permission();
		}
	}

	public function get_payment_pid_transfer_html() {
		if ($this->has_payment_pid_editransfer_permission) {
			$buf = file_get_contents(
					TEMPLATE_PATH . 'finance/payment/payment_pidtransfer.tpl');
			return str_replace(
					array('[VCODE]', '[LEFT]', '[TOP]', '[BASE_URL]'),
					array($this->get_vcode(), $this->get_left_html(),
							$this->get_top_html(), BASE_URL), $buf);
		} else {
			User::no_permission();
		}
	}

	private function validate_form_value($action) {
		$errors = array();
		if (in_array($action, array('edit', 'transfer'), TRUE)) {
			if ($action === 'transfer') {
				$sum_receive = 0;
				$sum_pay = 0;
				if (!self::validate_field_not_empty($this->receive_array)
						|| !self::validate_field_not_null($this->receive_array)) {
					$errors[] = '转入数据不能为空';
				} else {
					foreach ($this->receive_array as $val) {
						$sum_receive += $val['amount'];
					}
				}

				if (!self::validate_field_not_empty($this->pay_array)
						|| !self::validate_field_not_null($this->pay_array)) {
					$errors[] = '转出数据不能为空';
				} else {
					foreach ($this->pay_array as $val) {
						$sum_pay += $val['amount'];
					}
				}

				if ($sum_receive !== $sum_pay) {
					$errors[] = '转入转出出数据必须匹配';
				}

			} else if ($action === 'edit') {

			}
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

	public function getPaymentPidTransferResult() {
		if ($this->has_payment_pid_editransfer_permission) {
			if ($this->validate_form_value('transfer')) {
				$success = TRUE;
				$error = '';
				$this->db->query('BEGIN');

				$subsql = array();
				foreach ($this->receive_array as $r) {
					$pid = explode('_', $r['pid']);
					$subsql[] = '("' . $pid[0] . '",'
							. (empty($pid[1]) ? 0 : $pid[1]) . ','
							. $r['amount'] . ',2)';
				}

				foreach ($this->pay_array as $r) {
					$pid = explode('_', $r['pid']);
					$subsql[] = '("' . $pid[0] . '",'
							. (empty($pid[1]) ? 0 : $pid[1]) . ','
							. $r['amount'] . ',1)';
				}

				if (!empty($subsql)) {
					$result = $this->db
							->query(
									'INSERT INTO finance_payment_pid_transfer(pid,media_id,amount,type) VALUES'
											. implode(',', $subsql));
					if ($result === FALSE) {
						$success = FALSE;
						$error = '执行单付款转移失败';
					}
				} else {
					$success = FALSE;
					$error = '执行单付款转移选择不能为空';
				}

				if ($success) {
					$this->db->query('COMMIT');
				} else {
					$this->db->query('ROLLBACK');
				}

				return array('status' => $success ? 'success' : 'error',
						'message' => $success ? '执行单付款转移成功' : $error);
			}
			return array('status' => 'error', 'message' => $this->errors);
		}
		return array('status' => 'error', 'message' => NO_RIGHT_TO_DO_THIS);
	}
}
