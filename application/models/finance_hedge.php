<?php
class Finance_Hedge extends User {
	private $id;
	private $receive = array();
	private $pay = array();
	private $errors = array();
	private $has_finance_hedge_permission = FALSE;

	private $page;
	private $all_count;
	private $page_count;
	const LIMIT = 50;

	public function __construct($fields = array()) {
		parent::__construct();
		if (!empty($fields)) {
			foreach ($this as $key => $value) {
				if ($fields[$key] !== NULL
						&& !in_array($key,
								array('has_finance_hedge_permission'), TRUE)) {
					$this->$key = $fields[$key];
				}
			}
		}
		if (in_array($this->getUsername(),
				$GLOBALS['finanace_hedge_permission'], TRUE)) {
			$this->has_finance_hedge_permission = TRUE;
		}
	}

	public function get_finance_hedge_html() {
		if ($this->has_finance_hedge_permission) {
			$buf = file_get_contents(
					TEMPLATE_PATH . 'finance/finance_hedge.tpl');
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
		if (in_array($action, array('add', 'confirm', 'cancel'), TRUE)) {
			if ($action === 'confirm' || $action === 'cancel') {
				if (!self::validate_id(intval($this->id))) {
					$errors[] = '收付对冲记录选择有误';
				}
			}

			if ($action !== 'cancel') {
				$receive = $this->receive;
				$sum_receive = 0;
				if (empty($receive)) {
					$errors[] = '收付对冲收记录不能为空';
				} else {
					foreach ($receive as $key => $value) {
						if (!self::validate_money($value)) {
							$errors[] = '执行单' . $key . '对应的收款金额不是一个有效的金额值';
						} else {
							$sum_receive += $value;
						}
					}
				}

				$pay = $this->pay;
				$sum_pay = 0;
				if (empty($pay)) {
					$errors[] = '收付对冲付记录不能为空';
				} else {
					foreach ($pay as $key => $value) {
						if (!self::validate_money($value)) {
							$errors[] = '执行单' . $key . '对应的付款金额不是一个有效的金额值';
						} else {
							$sum_pay += $value;
						}
					}
				}
			}
		} else {
			$errors[] = '无权限操作';
		}

		if (empty($errors)) {
			return TRUE;
		}
		$this->errors = $errors;
		unset($errors);
		return FALSE;
	}

	public function add_finance_hedge() {
		if ($this->has_finance_hedge_permission) {
			if ($this->validate_form_value('add')) {
				$success = TRUE;
				$error = '';
				$this->db->query('BEGIN');

				$pay = $this->pay;
				$receive = $this->receive;

				$subsql = array();
				$sumamount = 0;
				foreach ($pay as $key => $value) {
					$subsql[] = '("hedge_id","' . $key . '",' . $value
							. ',2,0)';
					$sumamount += $value;
				}
				foreach ($receive as $key => $value) {
					$subsql[] = '("hedge_id","' . $key . '",' . $value
							. ',1,0)';
				}

				$result = $this->db
						->query(
								'INSERT INTO finance_cash_hedge(amount,isok,step,addtime) VALUE('
										. $sumamount . ',0,1,now())');
				if ($result === FALSE) {
					$success = FALSE;
					$error = '记录收付对冲失败，错误代码1';
				} else {
					$hedge_id = $this->db->insert_id;
					$subsql = implode(',', $subsql);
					$subsql = str_replace('"hedge_id"', $hedge_id, $subsql);
					$result = $this->db
							->query(
									'INSERT INTO finance_cash_hedge_list(hedge_id,pid,amount,hedge_type,isok) VALUES '
											. $subsql);
					if ($result === FALSE) {
						$success = FALSE;
						$error = '记录收付对冲失败，错误代码2';
					}
				}

				if ($success) {
					$this->db->query('COMMIT');
				} else {
					$this->db->query('ROLLBACK');
				}
				return array('status' => $success ? 'success' : 'error',
						'message' => $success ? '记录收付对冲成功' : $error);

			}
			return array('status' => 'error', 'message' => $this->errors);
		}
		return array('status' => 'error', 'message' => NO_RIGHT_TO_DO_THIS);
	}

	private function _get_list_data() {
		$this->all_count = intval(
				$this->db->get_var('SELECT COUNT(*) FROM finance_cash_hedge'));
		$this->page_count = ceil($this->all_count / self::LIMIT);
		$start = self::LIMIT * intval($this->page) - self::LIMIT;
		if ($start < 0) {
			$start = 0;
		}

		$results = array();
		$lists = $this->db
				->get_results(
						'SELECT id,amount,isok,step,addtime FROM finance_cash_hedge ORDER BY addtime DESC LIMIT '
								. $start . ',' . self::LIMIT);
		if ($lists !== NULL) {
			foreach ($lists as $list) {
				$results[] = array('id' => $list->id,
						'amount' => $list->amount, 'isok' => $list->isok,
						'step' => $list->step, 'addtime' => $list->addtime);
			}
		}
		return $results;
	}

	private static function _get_status($isok, $step) {
		switch ($isok) {
		case -1:
			return '<font color="red"><b>撤销</b></font>';
		case 1:
			return '<font color="green"><b>已确认</b></font>';
		}
		if ($step === 1) {
			return '<font color="#ff6600"><b>等待alex确认</b></font>';
		}
	}

	private static function _get_action($id, $isok) {
		$s = array();
		if (in_array($isok, array(0, 1))) {
			if ($isok === 0) {
				$s[] = '<a href="' . BASE_URL . 'finance/?o=hedge_confirm&id='
						. $id . '">确认</a>';
			}
			$s[] = '<a href="javascript:cancel(' . $id . ')">撤销</a>';
		}

		return implode('&nbsp;|&nbsp;', $s);
	}

	private function _get_list_html() {
		$datas = $this->_get_list_data();
		$result = '';
		if (!empty($datas)) {
			foreach ($datas as $data) {
				$result .= '<tr><td>' . $data['addtime'] . '</td><td>'
						. $data['amount'] . '</td><td>'
						. self::_get_status(intval($data['isok']),
								intval($data['step'])) . '</td><td>'
						. self::_get_action(intval($data['id']),
								intval($data['isok'])) . '</td></tr>';
			}
		}
		return $result;
	}

	private function get_counts() {
		return $this->page . '	/' . $this->page_count . ' 页 &nbsp;&nbsp;';
	}

	private function _get_pagination($is_prev) {
		return '<a href="' . BASE_URL . 'finance/?o=hedge_list&page='
				. ($is_prev ? intval($this->page) - 1 : intval($this->page) + 1)
				. '">' . ($is_prev ? '上一页' : '下一页') . '</a>';
	}

	public function getPrev() {
		if (intval($this->page) === 1) {
			return '';
		} else {
			return $this->_get_pagination(TRUE);
		}
	}

	public function getNext() {
		if (intval($this->page) >= intval($this->page_count)) {
			return '';
		} else {
			return $this->_get_pagination(FALSE);
		}
	}

	public function get_finance_hedge_list_html() {
		if ($this->has_finance_hedge_permission) {
			$buf = file_get_contents(
					TEMPLATE_PATH . 'finance/finance_hedge_list.tpl');
			return str_replace(
					array('[LEFT]', '[TOP]', '[HEDGELIST]', '[ALLCOUNTS]',
							'[COUNTS]', '[NEXT]', '[PREV]', '[VCODE]',
							'[BASE_URL]'),
					array($this->get_left_html(), $this->get_top_html(),
							$this->_get_list_html(), $this->all_count,
							$this->get_counts(), $this->getNext(),
							$this->getPrev(), $this->get_vcode(), BASE_URL),
					$buf);
		} else {
			return User::no_permission();
		}
	}

	public function get_finance_hedge_confirm_html() {
		if ($this->has_finance_hedge_permission) {
			$results = $this->db
					->get_results(
							'SELECT pid,amount,hedge_type FROM finance_cash_hedge_list WHERE hedge_id='
									. intval($this->id) . ' AND isok<>-1');
			$receive_pids = array();
			$pay_pids = array();
			if ($results !== NULL) {
				foreach ($results as $result) {
					if (intval($result->hedge_type) === 1) {
						$receive_pids[$result->pid] = $result->amount;
					} else if (intval($result->hedge_type) === 2) {
						$pay_pids[$result->pid] = $result->amount;
					}
				}
			}
			$receive_keys = array_keys($receive_pids);
			$pay_keys = array_keys($pay_pids);
			if (empty($receive_keys)) {
				$receive_keys = '';
			} else {
				$receive_keys = ',' . implode(',', $receive_keys) . ',';
			}
			if (empty($pay_keys)) {
				$pay_keys = '';
			} else {
				$pay_keys = ',' . implode(',', $pay_keys) . ',';
			}
			if ($results !== NULL) {
				$buf = file_get_contents(
						TEMPLATE_PATH . 'finance/finance_hedge_confirm.tpl');
				return str_replace(
						array('[ID]', '[RECEIVEPIDS]', '[PAYPIDS]', '[VCODE]',
								'[LEFT]', '[TOP]', '[BASE_URL]'),
						array(intval($this->id), $receive_keys, $pay_keys,
								$this->get_vcode(), $this->get_left_html(),
								$this->get_top_html(), BASE_URL), $buf);
			} else {
				return User::no_object('没有该收付对冲记录');
			}

		} else {
			User::no_permission();
		}
	}

	public function confirm_finance_hedge() {
		if ($this->has_finance_hedge_permission) {
			if ($this->validate_form_value('confirm')) {
				$success = TRUE;
				$error = '';
				$this->db->query('BEGIN');

				$row = $this->db
						->get_row(
								'SELECT isok FROM finance_cash_hedge WHERE id='
										. intval($this->id) . ' FOR UPDATE');
				if ($row !== NULL) {
					if (intval($row->isok) === 0) {
						$result = $this->db
								->query(
										'DELETE FROM finance_cash_hedge_list WHERE hedge_id='
												. intval($this->id)
												. ' AND isok=0');
						if ($result === FALSE) {
							$success = FALSE;
							$error = '确认收付对冲记录失败，错误代码1';
						} else {
							$pay = $this->pay;
							$receive = $this->receive;

							$subsql = array();
							$sumamount = 0;
							foreach ($pay as $key => $value) {
								$subsql[] = '(' . intval($this->id) . ',"'
										. $key . '",' . $value . ',2,1)';
								$sumamount += $value;
							}
							foreach ($receive as $key => $value) {
								$subsql[] = '(' . intval($this->id) . ',"'
										. $key . '",' . $value . ',1,1)';
							}

							$result = $this->db
									->query(
											'INSERT INTO finance_cash_hedge_list(hedge_id,pid,amount,hedge_type,isok) VALUES '
													. $subsql);
							if ($result === FALSE) {
								$success = FALSE;
								$error = '确认收付对冲记录失败，错误代码2';
							} else {
								$result = $this->db
										->query(
												'UPDATE finance_cash_hedge SET amount='
														. $sumamount
														. ',isok=1,step=2 WHERE id='
														. intval($this->id));
								if ($result === FALSE) {
									$success = FALSE;
									$error = '确认收付对冲记录失败，错误代码3';
								}
							}
						}
					} else {
						$success = FALSE;
						$error = '该收付对冲记录状态非需确认状态';
					}
				} else {
					$success = FALSE;
					$error = '没有该收付对冲记录';
				}

				if ($success) {
					$this->db->query('COMMIT');
				} else {
					$this->db->query('ROLLBACK');
				}
				return array('status' => $success ? 'success' : 'error',
						'message' => $success ? '确认收付对冲成功' : $error);
			}
			return array('status' => 'error', 'message' => $this->errors);
		}
		return array('status' => 'error', 'message' => NO_RIGHT_TO_DO_THIS);
	}

	public function cancel_finance_hedge() {
		if ($this->has_finance_hedge_permission) {
			if ($this->validate_form_value('cancel')) {
				$success = TRUE;
				$error = '';
				$this->db->query('BEGIN');

				$row = $this->db
						->get_row(
								'SELECT isok FROM finance_cash_hedge WHERE id='
										. intval($this->id) . ' FOR UPDATE');
				if ($row !== NULL) {
					if (intval($row->isok) !== -1) {
						$result = $this->db
								->query(
										'UPDATE finance_cash_hedge SET isok=-1 WHERE id='
												. intval($this->id));
						if ($result === FALSE) {
							$success = FALSE;
							$error = '撤销收付对冲记录失败，错误代码1';
						} else {
							$result = $this->db
									->query(
											'UPDATE finance_cash_hedge_list SET isok=-1 WHERE hedge_id='
													. intval($this->id));
							if ($result === FALSE) {
								$success = FALSE;
								$error = '撤销收付对冲记录失败，错误代码2';
							}
						}
					} else {
						$success = FALSE;
						$error = '该收付对冲记录状态已是撤销';
					}
				} else {
					$success = FALSE;
					$error = '没有该收付对冲记录';
				}

				if ($success) {
					$this->db->query('COMMIT');
				} else {
					$this->db->query('ROLLBACK');
				}
				return array('status' => $success ? 'success' : 'error',
						'message' => $success ? '撤销收付对冲成功' : $error);
			}
			return array('status' => 'error', 'message' => $this->errors);
		}
		return array('status' => 'error', 'message' => NO_RIGHT_TO_DO_THIS);
	}
}
