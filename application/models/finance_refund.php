<?php
class Finance_Refund extends User {
	private $id;
	private $customer_name_select;
	private $customer_name;
	private $bank_name_select;
	private $bank_name;
	private $bank_account_select;
	private $bank_account;
	private $refundment_amount;
	private $refundment_date;
	private $refundment_type;
	private $refundment_reason;
	private $refundment_dids;
	private $refund_pids = array();

	private $has_finance_refund_permission = FALSE;

	private $page;
	private $all_count;
	private $page_count;
	const LIMIT = 50;

	private $pid;
	private $itemaudit;
	private $remark;

	private $auditall;
	private $auditremarkall;

	public function __construct($fields = array()) {
		parent::__construct();
		if (in_array($this->getUsername(),
				$GLOBALS['manager_finance_permission'], TRUE)
				|| intval($this->getBelong_dep()) === 2) {
			$this->has_finance_refund_permission = TRUE;
		}
		if (!empty($fields)) {
			foreach ($this as $key => $value) {
				if ($fields[$key] !== NULL
						&& !in_array($key,
								array('has_finance_refund_permission '), TRUE)) {
					$this->$key = $fields[$key];
				}
			}
		}
	}

	private function _get_list_data($ismy = FALSE) {
		$this->all_count = intval(
				$this->db
						->get_var(
								'SELECT COUNT(*) FROM finance_refundment_person_apply WHERE '
										. ($ismy ? 'user=' . $this->getUid()
												: 'isok=0')));
		$this->page_count = ceil($this->all_count / self::LIMIT);
		$start = self::LIMIT * intval($this->page) - self::LIMIT;
		if ($start < 0) {
			$start = 0;
		}
		$results = array();
		$lists = $this->db
				->get_results(
						'SELECT a.id,a.refundment_amount,a.refundment_date,a.refundment_type,a.step,a.isok,a.isalter,a.refund_id,b.customer_name FROM finance_refundment_person_apply a LEFT JOIN finance_customer_bankinfo b ON a.customer_bankinfo_id=b.id WHERE '
								. ($ismy ? 'a.user=' . $this->getUid()
										: 'a.isok=0 OR a.isok=1 AND a.step=2')
								. ' ORDER BY id DESC LIMIT ' . $start . ','
								. self::LIMIT);
		if ($lists !== NULL) {
			foreach ($lists as $list) {
				$results[] = array('id' => $list->id,
						'refundment_amount' => $list->refundment_amount,
						'refundment_date' => $list->refundment_date,
						'refundment_type' => $list->refundment_type,
						'step' => $list->step, 'isok' => $list->isok,
						'customer_name' => $list->customer_name,
						'refund_id' => $list->refund_id,
						'isalter' => $list->isalter);
			}
		}
		return $results;
	}

	private static function _get_refund_type($type) {
		$s = '';
		switch (intval($type)) {
		case 1:
			$s = '合同款';
			break;
		case 2:
			$s = '保证金';
			break;
		}
		return $s;
	}

	private static function _get_refund_status($step, $isok, $ismy) {
		$s = '';
		if ($ismy && intval($isok) === 2) {
			return '审核驳回';
		} else if (intval($step) === 2 && intval($isok) === 1) {
			return '审核通过';
		} else {
			switch (intval($step)) {
			case 0:
				$s = '等待linda审核';
				break;
			case 1:
				$s = '等待alex审核';
				break;
			}
		}
		return $s;
	}

	public function get_finance_apply_html() {
		$buf = file_get_contents(
				TEMPLATE_PATH . 'finance/refund/finance_refund_apply.tpl');
		return str_replace(
				array('[LEFT]', '[TOP]', '[VCODE]', '[CUSTOMERNAMESELECT]',
						'[VALIDATE_TYPE]', '[VALIDATE_SIZE]', '[BASE_URL]'),
				array($this->get_left_html(), $this->get_top_html(),
						$this->get_vcode(),
						Customer_Bankinfo::get_customer_list(),
						implode(',', $GLOBALS['defined_upload_validate_type']),
						UPLOAD_FILE_MAX_SIZE / (1024 * 1024), BASE_URL), $buf);
	}

	private static function _get_refund_id_show($refund_id, $isalter) {
		if (intval($isalter) === 0) {
			return '<font color="#66cc00">【新】</font> ' . $refund_id;
		} else {
			return '<font color="#cc6600">【变' . $isalter . '】</font> '
					. $refund_id;
		}
	}

	private function _get_refund_list_html($ismy = FALSE) {
		$datas = $this->_get_list_data($ismy);
		$result = '';
		if (!empty($datas)) {
			foreach ($datas as $data) {
				$result .= '<tr><td>'
						. self::_get_refund_id_show($data['refund_id'],
								$data['isalter']) . '</td><td>'
						. $data['customer_name']
						. '</td><td><font color="#ff9933"><b>'
						. Format_Util::my_money_format('%.2n',
								$data['refundment_amount'])
						. '</b></font></td><td>' . $data['refundment_date']
						. '</td><td>'
						. self::_get_refund_type($data['refundment_type'])
						. '</td><td><font color="#ff6600"><b>'
						. self::_get_refund_status($data['step'],
								$data['isok'], $ismy) . '</b></font></td><td>'
						. $this
								->_get_action($data['id'], $data['isok'],
										$ismy, $data['refund_id'])
						. '</td></tr>';
			}
		}
		return $result;
	}

	private function _get_action($id, $isok, $ismy, $refund_id) {
		//var_dump($id, $isok, $ismy,$refund_id);
		if ($ismy) {
			if (intval($isok) === 2) {
				$row = $this->db
						->get_row(
								'SELECT id FROM finance_refundment_person_apply WHERE refund_id="'
										. $refund_id
										. '" ORDER BY isalter DESC LIMIT 1');
				if ($row !== NULL) {
					if (intval($row->id) === intval($id)) {
						return '<a href="' . BASE_URL
								. 'finance/refund/?o=edit&id=' . intval($id)
								. '">修改</a>';
					}
				}
			}
		} else {
			if (intval($isok) === 0) {
				return '<a href="' . BASE_URL . 'finance/refund/?o=audit&id='
						. intval($id) . '">审核</a>';
			} else {
				return '<a href="' . BASE_URL . 'finance/refund/?o=print&id='
						. intval($id)
						. '"  target="_blank">打印</a>&nbsp;|&nbsp;<a href="'
						. BASE_URL . 'finance/refund/?o=gd&id=' . intval($id)
						. '">归档</a>';
			}
		}
	}

	private function _get_apply_counts() {
		return $this->page . '	/' . $this->page_count . ' 页 &nbsp;&nbsp;';
	}

	private function _get_pagination($is_prev, $action) {
		return '<a href="' . BASE_URL . 'finance/refund/?o=' . $action
				. '&page='
				. ($is_prev ? intval($this->page) - 1 : intval($this->page) + 1)
				. '">' . ($is_prev ? '上一页' : '下一页') . '</a>';
	}

	private function _getPrev($action) {
		if (intval($this->page) === 1) {
			return '';
		} else {
			return $this->_get_pagination(TRUE, $action);
		}
	}

	private function _getNext($action) {
		if (intval($this->page) >= intval($this->page_count)) {
			return '';
		} else {
			return $this->_get_pagination(FALSE, $action);
		}
	}

	public function get_finance_manager_html() {
		if ($this->has_finance_refund_permission) {
			$buf = file_get_contents(
					TEMPLATE_PATH . 'finance/refund/finance_refund_manager.tpl');
			return str_replace(
					array('[LEFT]', '[TOP]', '[REFUNDLIST]', '[ALLCOUNTS]',
							'[COUNTS]', '[NEXT]', '[PREV]', '[BASE_URL]'),
					array($this->get_left_html(), $this->get_top_html(),
							$this->_get_refund_list_html(), $this->all_count,
							$this->_get_apply_counts(),
							$this->_getNext('manager'),
							$this->_getPrev('manager'), BASE_URL), $buf);
		} else {
			User::no_permission();
		}
	}

	public function get_finance_refund_mylist_html() {
		//if ($this->has_finance_refund_permission) {
		$buf = file_get_contents(
				TEMPLATE_PATH . 'finance/refund/finance_refund_mylist.tpl');
		return str_replace(
				array('[LEFT]', '[TOP]', '[REFUNDLIST]', '[ALLCOUNTS]',
						'[COUNTS]', '[NEXT]', '[PREV]', '[BASE_URL]'),
				array($this->get_left_html(), $this->get_top_html(),
						$this->_get_refund_list_html(TRUE), $this->all_count,
						$this->_get_apply_counts(), $this->_getNext('mylist'),
						$this->_getPrev('mylist'), BASE_URL), $buf);
		//} else {
		//	User::no_permission();
		//}
	}

	public function search_receivable() {
		$refundment_type = Security_Util::my_post('refundment_type');
		$search = Security_Util::my_post('search');
		$cusname = Security_Util::my_post('cusname');
		$subsql = array();
		if (intval($refundment_type) === 1) {
			//合同款
			if (!empty($search)) {
				$subsql[] = ' a.pid LIKE "%' . $search . '%" ';
			}
			if (!empty($cusname)) {
				$subsql[] = ' b.cusname LIKE "%' . $cusname . '%" ';
			}
			$sql = 'SELECT m.*,n.invoice_amount FROM (SELECT SUM(amount) AS amount,pid,cid,cusname FROM (SELECT a.*,b.cusname FROM (SELECT pid,amount,SUBSTRING_INDEX(pid,\'-\',1) AS cid FROM finance_receivables WHERE isok=1) a LEFT JOIN contract_cus b ON a.cid=b.cid WHERE 1=1 '
					. (!empty($subsql) ? ' AND ' . implode(' AND ', $subsql)
							: '')
					. ')z GROUP BY pid ) m LEFT JOIN (SELECT SUM(amount) AS invoice_amount ,pid FROM finance_invoice WHERE isok=1 GROUP BY pid) n ON m.pid=n.pid';

		} else if (intval($refundment_type) === 2) {
			//保证金
			if (!empty($search)) {
				$subsql[] = ' a.cid LIKE "%' . $search . '%" ';
			}
			if (!empty($cusname)) {
				$subsql[] = ' b.cusname LIKE "%' . $cusname . '%" ';
			}
			$sql = 'SELECT m.*,n.invoice_amount FROM (SELECT SUM(amount) AS amount,cid,cusname FROM (SELECT a.*,b.cusname FROM (SELECT cid,amount FROM finance_deposit_receivables WHERE isok=1) a LEFT JOIN contract_cus b ON a.cid=b.cid WHERE 1=1 '
					. (!empty($subsql) ? ' AND ' . implode(' AND ', $subsql)
							: '')
					. ')z GROUP BY cid ) m LEFT JOIN (SELECT SUM(amount) AS invoice_amount,cid FROM finance_deposit_invoice WHERE isok=1 GROUP BY cid) n ON m.cid=n.cid';
		}
		//var_dump($sql);
		$s = '<table width="100%"><tr><td></td><td>'
				. (intval($refundment_type) === 1 ? '执行单号' : '合同号')
				. '</td><td>客户名称</td><td>已收款金额</td><td>已开票金额</td></tr>';
		$results = $this->db->get_results($sql);
		if ($results !== NULL) {
			foreach ($results as $result) {
				$pcid = (intval($refundment_type) === 1 ? $result->pid
						: $result->cid);
				$s .= '<tr><td><input type="checkbox" name="selpcid" value="'
						. $pcid . '"></td><td id="pid_' . $pcid . '">' . $pcid
						. '</td><td id="cusname_' . $pcid . '">'
						. $result->cusname . '</td><td id="amount_' . $pcid
						. '">' . $result->amount
						. '</td><td><span id="invoice_amount_' . $pcid . '">'
						. $result->invoice_amount . '</span>&nbsp;'
						. (!empty($result->invoice_amount) ? '<input type="button" value="展开" class="btn" onclick="javascript:openit(\''
										. $pcid . '\');">' : '') . '</td></tr>';
				if (!empty($result->invoice_amount)) {
					$s .= '<tr id="trr_' . $pcid
							. '"><td colspan="4">&nbsp;</td><td><table width="100%"><tr><td>开票日期</td><td>发票号码</td><td>开票金额</td></tr>';
					if (intval($refundment_type) === 1) {
						//已开合同票
						$aa = $this->db
								->get_results(
										'SELECT a.amount,b.number,b.date FROM finance_invoice a LEFT JOIN finance_invoice_list b ON a.invoice_list_id=b.id WHERE a.pid="'
												. $pcid
												. '" AND b.isok=1 AND b.print=1');
					} else {
						//已开保证金票
						$aa = $this->db
								->get_results(
										'SELECT a.amount,b.number,b.date FROM finance_deposit_invoice a LEFT JOIN finance_deposit_invoice_list b ON a.invoice_list_id=b.id WHERE a.cid="'
												. $pcid
												. '" AND b.isok=1 AND b.print=1');
					}
					if ($aa !== NULL) {
						foreach ($aa as $a) {
							$s .= '<tr><td>' . $a->date . '</td><td>'
									. $a->number
									. '</td><td><font color="#ff9933"><b>'
									. Format_Util::my_money_format('%.2n',
											$a->amount)
									. '</b></font></td></tr>';

						}
						$s .= '</table></td></tr>';
					}
				}
			}
		} else {
			$s .= '<tr><td colspan="5">没有搜索结果</td></tr>';
		}
		$s .= '</table><script>$(\'[id^=trr_]\').hide();</script>';
		return $s;
	}

	private function validate_form_value($action) {
		$errors = array();
		if (in_array($action,
				array('apply', 'audit_refund_item', 'edit',
						'refund_apply_allaudit', 'refund_apply_gd'), TRUE)) {
			if ($action === 'refund_apply_gd') {
				if (!self::validate_id(intval($this->id))) {
					$errors[] = '退款申请选择有误';
				}

				if (!self::validate_money($this->refundment_amount)) {
					$errors[] = '退款金额不是一个有效的金额值';
				}

				if (empty($this->refundment_date)) {
					$errors[] = '退款时间不能为空';
				} else if (strtotime($this->refundment_date) === FALSE) {
					$errors[] = '退款时间不是一个有效的日期值';
				}
			} else if ($action === 'audit_refund_item') {
				if (!in_array(intval($this->itemaudit), array(1, 2), TRUE)) {
					$errors[] = '审核条目选择有误';
				}

				if (!empty($this->remark)
						&& !self::validate_field_max_length($this->remark, 500)) {
					$errors[] = '审核意见最多500个字符';
				}

				if (!self::validate_id(intval($this->id))) {
					$errors[] = '审核条目选择有误';
				}
			} else if ($action === 'refund_apply_allaudit') {
				if (!self::validate_id(intval($this->id))) {
					$errors[] = '退款申请选择有误';
				}

				if (!in_array(intval($this->auditall), array(1, 2), TRUE)) {
					$errors[] = '审核结果选择有误';
				}

				if (!empty($this->auditremarkall)) {
					if (!self::validate_field_max_length(
							$this->auditremarkall, 500)) {
						$errors[] = '审核意见最多500个字符';
					}
				}
			} else {
				if ($action === 'edit') {
					if (!self::validate_id(intval($this->id))) {
						$errors[] = '退款申请选择有误';
					}
				}

				if (empty($this->customer_name_select)
						&& empty($this->customer_name)) {
					$errors[] = '选择或者输入一个客户名称';
				}

				if (empty($this->customer_name_select)
						&& !empty($this->customer_name)
						&& !self::validate_field_max_length(
								$this->customer_name, 255)) {
					$errors[] = '客户名称最多255个字符';
				}

				if (empty($this->bank_name_select) && empty($this->bank_name)) {
					$errors[] = '选择或者输入一个开户行名称';
				}

				if (empty($this->bank_name_select) && !empty($this->bank_name)
						&& !self::validate_field_max_length($this->bank_name,
								255)) {
					$errors[] = '开户行名称最多255个字符';
				}

				if (empty($this->bank_account_select)
						&& empty($this->bank_account)) {
					$errors[] = '选择或者输入一个账户名称';
				}

				if (empty($this->bank_account_select)
						&& !empty($this->bank_account)
						&& !self::validate_field_max_length(
								$this->bank_account, 255)) {
					$errors[] = '账户名称最多255个字符';
				}

				if (!self::validate_money($this->refundment_amount)) {
					$errors[] = '退款金额不是一个有效的金额值';
				}

				if (empty($this->refundment_date)) {
					$errors[] = '退款时间不能为空';
				} else if (strtotime($this->refundment_date) === FALSE) {
					$errors[] = '退款时间不是一个有效的日期值';
				}

				if (!in_array(intval($this->refundment_type), array(1, 2), TRUE)) {
					$errors[] = '退款类型选择有误';
				}

				if ($this->refundment_reason === NULL
						|| empty($this->refundment_reason)) {
					$errors[] = '退款理由不能为空';
				} else if (!self::validate_field_max_length(
						$this->refundment_reason, 500)) {
					$errors[] = '退款理由最多500个字符';
				}

				if (!empty($this->refundment_dids)
						&& !self::validate_field_max_length(
								$this->refundment_dids, 1000)) {
					$errors[] = '终止协议文件过多';
				}

				$refund_pids = $this->refund_pids;
				if (empty($refund_pids)) {
					$errors[] = '退款条目不能为空';
				} else {
					$count = 0;
					foreach ($refund_pids as $key => $refund_pid) {
						if (intval($this->refundment_type) === 1
								&& !self::validate_field_max_length($key, 100)) {
							$errors[] = '第' . ($count + 1) . '条执行单条目选择有误';
						} else if (intval($this->refundment_type) === 2
								&& !self::validate_field_max_length($key, 50)) {
							$errors[] = '第' . ($count + 1) . '条合同条目选择有误';
						}

						if (!self::validate_money(abs($refund_pid))) {
							$errors[] = '第' . ($count + 1) . '条退款金额输入有误';
						}
						$count++;
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

	public function customer_refund_edit() {
		if ($this->validate_form_value('edit')) {
			$success = TRUE;
			$error = '';
			$this->db->query('BEGIN');

			//查找上一版本
			$refund_id = '';
			$isalter = 0;
			$row = $this->db
					->get_row(
							'SELECT isalter,refund_id FROM finance_refundment_person_apply WHERE id='
									. intval($this->id) . ' FOR UPDATE');
			if ($row !== NULL) {
				$refund_id = $row->refund_id;
				$isalter = $row->isalter;
			} else {
				$success = FALSE;
				$error = '需要修改的退款申请不存在';
			}
			if ($success) {
				//检查客户银行信息是否有
				$customer = !empty($this->customer_name_select) ? $this
								->customer_name_select : $this->customer_name;
				$bank = !empty($this->bank_name_select) ? $this
								->bank_name_select : $this->bank_name;
				$account = !empty($this->bank_account_select) ? $this
								->bank_account_select : $this->bank_account;

				$row = $this->db
						->get_row(
								'SELECT id FROM finance_customer_bankinfo WHERE customer_name="'
										. $customer . '" AND bank_name="'
										. $bank . '" AND bank_account="'
										. $account . '" FOR UPDATE');
				if ($row == NULL) {
					$insert_result = $this->db
							->query(
									'INSERT INTO finance_customer_bankinfo(customer_name,bank_name,bank_account) VALUE("'
											. $customer . '","' . $bank . '","'
											. $account . '")');
					if ($insert_result === FALSE) {
						$success = FALSE;
						$error = '记录客户银行信息出错';
					} else {
						$customer_bankinfo_id = $this->db->insert_id;
					}
				} else {
					$customer_bankinfo_id = $row->id;
				}

				if ($success) {
					//记录主记录
					$insert_result = $this->db
							->query(
									'INSERT INTO finance_refundment_person_apply(refund_id,customer_bankinfo_id,refundment_amount,refundment_date,refundment_type,refundment_reason,refundment_dids,addtime,step,isok,user,isalter) VALUE("'
											. $refund_id . '",'
											. $customer_bankinfo_id . ','
											. $this->refundment_amount . ',"'
											. $this->refundment_date . '",'
											. intval($this->refundment_type)
											. ',"' . $this->refundment_reason
											. '","' . $this->refundment_dids
											. '",now(),0,0,' . $this->getUid()
											. ',' . (intval($isalter) + 1)
											. ')');
					if ($insert_result === FALSE) {
						$success = FALSE;
						$error = '修改退款申请信息出错，错误代码1';
					} else {
						$apply_id = $this->db->insert_id;
						//记录执行单号及金额
						$refund_pids = $this->refund_pids;
						$subsql = array();
						if (!empty($refund_pids)) {
							foreach ($refund_pids as $key => $refund_pid) {
								$subsql[] = '(' . $apply_id . ',"' . $refund_id
										. '","' . $key . '",'
										. abs($refund_pid) . ',0)';
							}
						}
						if (!empty($subsql)) {
							$insert_result = $this->db
									->query(
											'INSERT INTO finance_refundment_person_apply_list(apply_id,refund_id,'
													. (intval(
															$this
																	->refundment_type)
															=== 1 ? 'pid'
															: 'cid')
													. ',refund_amount,isok) VALUES'
													. implode(',', $subsql));
							if ($insert_result === FALSE) {
								$success = FALSE;
								$error = '修改退款申请信息出错，错误代码2';
							}
						} else {
							$success = FALSE;
							$error = '退款数据有误';
						}
					}
				}
			}

			if ($success) {
				$this->db->query('COMMIT');
			} else {
				$this->db->query('ROLLBACK');
			}
			return array('status' => $success ? 'success' : 'error',
					'message' => $success ? '修改退款申请成功' : $error);
		}
		return array('status' => 'error', 'message' => $this->errors);
	}

	public function customer_refund_apply() {
		if ($this->validate_form_value('apply')) {
			$success = TRUE;
			$error = '';
			$this->db->query('BEGIN');

			//生成退款单号
			$refundid = $this
					->getSequence(
							date('y', time()) . $this->getCity_show()
									. date('m', time()) . 'CR');

			if ($refundid === FALSE) {
				$success = FALSE;
				$error = '生成单号出错';
			} else {
				//检查客户银行信息是否有
				$customer = !empty($this->customer_name_select) ? $this
								->customer_name_select : $this->customer_name;
				$bank = !empty($this->bank_name_select) ? $this
								->bank_name_select : $this->bank_name;
				$account = !empty($this->bank_account_select) ? $this
								->bank_account_select : $this->bank_account;

				$row = $this->db
						->get_row(
								'SELECT id FROM finance_customer_bankinfo WHERE customer_name="'
										. $customer . '" AND bank_name="'
										. $bank . '" AND bank_account="'
										. $account . '" FOR UPDATE');
				if ($row == NULL) {
					$insert_result = $this->db
							->query(
									'INSERT INTO finance_customer_bankinfo(customer_name,bank_name,bank_account) VALUE("'
											. $customer . '","' . $bank . '","'
											. $account . '")');
					if ($insert_result === FALSE) {
						$success = FALSE;
						$error = '记录客户银行信息出错';
					} else {
						$customer_bankinfo_id = $this->db->insert_id;
					}
				} else {
					$customer_bankinfo_id = $row->id;
				}

				if ($success) {
					//记录主记录
					$insert_result = $this->db
							->query(
									'INSERT INTO finance_refundment_person_apply(refund_id,customer_bankinfo_id,refundment_amount,refundment_date,refundment_type,refundment_reason,refundment_dids,addtime,step,isok,user,isalter) VALUE("'
											. $refundid . '",'
											. $customer_bankinfo_id . ','
											. $this->refundment_amount . ',"'
											. $this->refundment_date . '",'
											. intval($this->refundment_type)
											. ',"' . $this->refundment_reason
											. '","' . $this->refundment_dids
											. '",now(),0,0,' . $this->getUid()
											. ',0)');
					if ($insert_result === FALSE) {
						$success = FALSE;
						$error = '记录退款申请信息出错，错误代码1';
					} else {
						$apply_id = $this->db->insert_id;
						//记录执行单号及金额
						$refund_pids = $this->refund_pids;
						$subsql = array();
						if (!empty($refund_pids)) {
							foreach ($refund_pids as $key => $refund_pid) {
								$subsql[] = '(' . $apply_id . ',"' . $refundid
										. '","' . $key . '",'
										. abs($refund_pid) . ',0)';
							}
						}
						if (!empty($subsql)) {
							$insert_result = $this->db
									->query(
											'INSERT INTO finance_refundment_person_apply_list(apply_id,refund_id,'
													. (intval(
															$this
																	->refundment_type)
															=== 1 ? 'pid'
															: 'cid')
													. ',refund_amount,isok) VALUES'
													. implode(',', $subsql));
							if ($insert_result === FALSE) {
								$success = FALSE;
								$error = '记录退款申请信息出错，错误代码2';
							}
						} else {
							$success = FALSE;
							$error = '退款数据有误';
						}
					}
				}
			}

			if ($success) {
				$this->db->query('COMMIT');
			} else {
				$this->db->query('ROLLBACK');
			}
			return array('status' => $success ? 'success' : 'error',
					'message' => $success ? '提交退款申请成功' : $error);
		}
		return array('status' => 'error', 'message' => $this->errors);
	}

	private static function _get_item_status($isok) {
		switch (intval($isok)) {
		case 1:
			return '审核通过';
		case 2:
			return '驳回';
		default:
			return '审核中';
		}
	}

	public function get_finance_refund_edit_html() {
		//判断是否是自己的申请，并且是被驳回的
		$rows = $this->db
				->get_results(
						'SELECT a.pid,a.cid,a.refund_amount,a.isok,a.remark,b.refundment_amount,b.refundment_date,b.refundment_type,b.refundment_reason,b.refundment_dids,b.step,b.isok AS allisok,b.refund_id,b.isalter,c.realname,c.username,d.customer_name,d.bank_name,d.bank_account FROM finance_refundment_person_apply_list a,finance_refundment_person_apply b,users c,finance_customer_bankinfo d WHERE b.id='
								. intval($this->id)
								. ' AND b.isok=2 AND a.apply_id=b.id AND b.user=c.uid AND b.customer_bankinfo_id=d.id');

		if ($rows !== NULL) {
			$refund_id = '';
			$isalter = '';
			$customer_name = '';
			$bank_name = '';
			$bank_account = '';
			$refundment_amount = '';
			$refundment_date = '';
			$refundment_type = '';
			$refundment_reason = '';
			$refundment_dids = '';
			$itemlist = '';
			$pids_array = array();
			foreach ($rows as $key => $row) {
				$pcid = '';
				if ($key === 0) {
					$customer_name = $row->customer_name;
					$bank_name = $row->bank_name;
					$bank_account = $row->bank_account;
					$refundment_amount = $row->refundment_amount;
					$refundment_date = $row->refundment_date;
					$refundment_type = $row->refundment_type;
					$refundment_reason = $row->refundment_reason;
					$refundment_dids = $row->refundment_dids;
					$refund_id = $row->refund_id;
					$isalter = $row->isalter;
				}

				if (intval($refundment_type) === 1) {
					$pcid = $row->pid;
					$sql = 'SELECT m.*,n.invoice_amount FROM (SELECT SUM(amount) AS amount,pid,cid,cusname FROM (SELECT a.*,b.cusname FROM (SELECT pid,amount,SUBSTRING_INDEX(pid,\'-\',1) AS cid FROM finance_receivables WHERE isok=1) a LEFT JOIN contract_cus b ON a.cid=b.cid WHERE  a.pid="'
							. $pcid
							. '" )z GROUP BY pid ) m LEFT JOIN (SELECT SUM(amount) AS invoice_amount ,pid FROM finance_invoice WHERE isok=1 GROUP BY pid) n ON m.pid=n.pid';
				} else {
					$pcid = $row->cid;
					$sql = 'SELECT SUM(amount) AS amount,cid,cusname FROM (SELECT a.*,b.cusname FROM (SELECT cid,amount FROM finance_deposit_receivables WHERE isok=1) a LEFT JOIN contract_cus b ON a.cid=b.cid WHERE a.cid="'
							. $pcid . '" )z GROUP BY cid ';
				}
				$pids_array[] = $pcid;
				$rrow = $this->db->get_row($sql);
				$itemlist .= '<tr id="addtr_' . $pcid . '"><td width="10px;">'
						. (intval($row->isok) === 2 ? '<img src="' . BASE_URL
										. 'images/close.png" onclick="pidmove(\''
										. $pcid . '\');"/>' : '') . '</td><td>'
						. $pcid . '</td><td>' . $rrow->cusname . '</td><td>'
						. $rrow->amount . '</td><td>' . $rrow->invoice_amount
						. '</td><td>'
						. (intval($row->isok) === 2 ? '<input type="text" class="validate[required,max[0],min[-'
										. $rrow->amount
										. ']" style="height:20px;" name="refund_'
										. $pcid . '" value="-'
										. $row->refund_amount . '">'
								: '-' . $row->refund_amount) . '</td><td>'
						. self::_get_item_status($row->isok) . '</td><td>'
						. (intval($row->isok) === 2 ? $row->remark : '')
						. '</td></tr>';
			}
			$buf = file_get_contents(
					TEMPLATE_PATH . 'finance/refund/finance_refund_edit.tpl');
			return str_replace(
					array('[LEFT]', '[TOP]', '[VCODE]', '[CUSTOMERNAMESELECT]',
							'[BANKNAMESELECT]', '[ACCOUNTSELECT]',
							'[VALIDATE_TYPE]', '[VALIDATE_SIZE]',
							'[REFUNDAMOUNT]', '[REFUNDDATE]', '[REFUNDREASON]',
							'[DIDSVALUE]', '[DIDS]', '[REFUNDTYPE]',
							'[ITEMSLIST]', '[PIDS]', '[ID]', '[REFUNDID]',
							'[BASE_URL]'),
					array($this->get_left_html(), $this->get_top_html(),
							$this->get_vcode(),
							Customer_Bankinfo::get_customer_list($customer_name),
							Customer_Bankinfo::get_bank_list($customer_name,
									$bank_name),
							Customer_Bankinfo::get_bank_acount_list(
									$customer_name, $bank_name, $bank_account),
							implode(',',
									$GLOBALS['defined_upload_validate_type']),
							UPLOAD_FILE_MAX_SIZE / (1024 * 1024),
							$refundment_amount, $refundment_date,
							$refundment_reason, $refundment_dids,
							$this
									->get_upload_files($refundment_dids, TRUE,
											'refundment_dids'),
							$refundment_type, $itemlist,
							',' . implode(',', $pids_array) . ',',
							intval($this->id),
							self::_get_refund_id_show($refund_id, $isalter),
							BASE_URL), $buf);
		} else {
			return User::no_permission();
		}
	}

	public function get_finance_refund_print_html() {
		if (in_array($this->getUsername(),
				$GLOBALS['person_refund_apply_audit_permission'], TRUE)) {

			$rows = $this->db
					->get_results(
							'SELECT a.pid,a.cid,a.refund_amount,a.isok,b.refundment_amount,b.refundment_date,b.refundment_type,b.refundment_reason,b.refundment_dids,b.step,b.isok AS allisok,b.refund_id,b.isalter,c.realname,c.username,d.customer_name,d.bank_name,d.bank_account FROM finance_refundment_person_apply_list a,finance_refundment_person_apply b,users c,finance_customer_bankinfo d WHERE b.id='
									. intval($this->id)
									. ' AND a.apply_id=b.id AND b.user=c.uid AND b.customer_bankinfo_id=d.id');
			if ($rows !== NULL) {
				$refund_id = '';
				$customer_name = '';
				$bank_name = '';
				$bank_account = '';
				$refundment_amount = '';
				$refundment_date = '';
				$refundment_type = '';
				$refundment_reason = '';
				$refundment_dids = '';
				$isalter = '';
				$table = '<tr><td width="100px;">'
						. (intval($refundment_type) === 1 ? '执行单号' : '合同号')
						. '</td><td>到款情况</td><td>开票情况</td><td>退款金额</td><td>客户名称</td></tr>';
				foreach ($rows as $key => $row) {
					if ($key === 0) {
						$customer_name = $row->customer_name;
						$bank_name = $row->bank_name;
						$bank_account = $row->bank_account;
						$refundment_amount = $row->refundment_amount;
						$refundment_date = $row->refundment_date;
						$refundment_type = $row->refundment_type;
						$refundment_reason = $row->refundment_reason;
						$refundment_dids = $row->refundment_dids;
						$refund_id = $row->refund_id;
						$isalter = $row->isalter;
					}
					if (intval($refundment_type) === 1) {
						$pcid = $row->pid;
						$sql = 'SELECT m.*,n.invoice_amount FROM (SELECT SUM(amount) AS amount,pid,cid,cusname FROM (SELECT a.*,b.cusname FROM (SELECT pid,amount,SUBSTRING_INDEX(pid,\'-\',1) AS cid FROM finance_receivables WHERE isok=1) a LEFT JOIN contract_cus b ON a.cid=b.cid WHERE 1=1  AND a.pid="'
								. $pcid
								. '")z GROUP BY pid ) m LEFT JOIN (SELECT SUM(amount) AS invoice_amount ,pid FROM finance_invoice WHERE isok=1 GROUP BY pid) n ON m.pid=n.pid';

					} else {
						$pcid = $row->cid;
						$sql = 'SELECT m.*,n.invoice_amount FROM (SELECT SUM(amount) AS amount,cid,cusname FROM (SELECT a.*,b.cusname FROM (SELECT cid,amount FROM finance_deposit_receivables WHERE isok=1) a LEFT JOIN contract_cus b ON a.cid=b.cid WHERE 1=1 AND a.cid="'
								. $pcid
								. '" )z GROUP BY cid ) m LEFT JOIN (SELECT SUM(amount) AS invoice_amount,cid FROM finance_deposit_invoice WHERE isok=1 GROUP BY cid) n ON m.cid=n.cid';

					}
					//var_dump($sql);
					$info = $this->db->get_row($sql);
					$table .= '<tr><td>' . $pcid . '</td><td>' . $info->amount
							. '</td><td>' . $info->invoice_amount . '</td><td>'
							. $row->refund_amount . '</td><td>'
							. $info->cusname . '</td></tr>';
				}

				$refunddone = '';
				$dones = $this->db
						->get_results(
								'SELECT refund_date,refund_amount FROM finance_refundment_person_apply_gd WHERE apply_id='
										. intval($this->id)
										. ' AND refund_id="' . $refund_id . '"');

				if ($dones !== NULL) {
					foreach ($dones as $done) {
						$refunddone .= '<tr><td>' . $done->refund_date
								. '</td><td>' . $done->refund_amount
								. '</td></tr>';
					}
				} else {
					$refunddone .= '<tr><td colspan="2"><font color="red">无退款记录</font></td></tr>';
				}

				$buf = file_get_contents(
						TEMPLATE_PATH
								. 'finance/refund/finance_refund_apply_print.tpl');
				return str_replace(
						array('[LEFT]', '[TOP]', '[VCODE]', '[CUSTOMERNAME]',
								'[BANKNAME]', '[BANKACCOUNT]',
								'[REFUNDAMOUNT]', '[REFUNDDATE]',
								'[REFUNDTYPE]', '[REFUNDREASON]',
								'[REFUNDDIDS]', '[REFUNDITEMS]', '[ID]',
								'[REFUNDID]', '[REFUNDDONE]', '[BASE_URL]'),
						array($this->get_left_html(), $this->get_top_html(),
								$this->get_vcode(), $customer_name, $bank_name,
								$bank_account, $refundment_amount,
								$refundment_date,
								self::_get_refund_type($refundment_type),
								Format_Util::format_html($refundment_reason),
								$this->get_upload_files($refundment_dids),
								$table, intval($this->id),
								self::_get_refund_id_show($refund_id, $isalter),
								$refunddone, BASE_URL), $buf);
			} else {
				return User::no_object('没有该退款申请');
			}

		} else {
			return User::no_permission();
		}
	}

	public function get_finance_refund_gd_html() {
		if (in_array($this->getUsername(),
				$GLOBALS['person_refund_apply_audit_permission'], TRUE)) {

			$rows = $this->db
					->get_results(
							'SELECT a.pid,a.cid,a.refund_amount,a.isok,b.refundment_amount,b.refundment_date,b.refundment_type,b.refundment_reason,b.refundment_dids,b.step,b.isok AS allisok,b.refund_id,b.isalter,c.realname,c.username,d.customer_name,d.bank_name,d.bank_account FROM finance_refundment_person_apply_list a,finance_refundment_person_apply b,users c,finance_customer_bankinfo d WHERE b.id='
									. intval($this->id)
									. ' AND a.apply_id=b.id AND b.user=c.uid AND b.customer_bankinfo_id=d.id');
			if ($rows !== NULL) {
				$refund_id = '';
				$customer_name = '';
				$bank_name = '';
				$bank_account = '';
				$refundment_amount = '';
				$refundment_date = '';
				$refundment_type = '';
				$refundment_reason = '';
				$refundment_dids = '';
				$isalter = '';
				$table = '<tr><td width="100px;">'
						. (intval($refundment_type) === 1 ? '执行单号' : '合同号')
						. '</td><td>到款情况</td><td>开票情况</td><td>退款金额</td><td>客户名称</td></tr>';
				foreach ($rows as $key => $row) {
					if ($key === 0) {
						$customer_name = $row->customer_name;
						$bank_name = $row->bank_name;
						$bank_account = $row->bank_account;
						$refundment_amount = $row->refundment_amount;
						$refundment_date = $row->refundment_date;
						$refundment_type = $row->refundment_type;
						$refundment_reason = $row->refundment_reason;
						$refundment_dids = $row->refundment_dids;
						$refund_id = $row->refund_id;
						$isalter = $row->isalter;
					}
					if (intval($refundment_type) === 1) {
						$pcid = $row->pid;
						$sql = 'SELECT m.*,n.invoice_amount FROM (SELECT SUM(amount) AS amount,pid,cid,cusname FROM (SELECT a.*,b.cusname FROM (SELECT pid,amount,SUBSTRING_INDEX(pid,\'-\',1) AS cid FROM finance_receivables WHERE isok=1) a LEFT JOIN contract_cus b ON a.cid=b.cid WHERE 1=1  AND a.pid="'
								. $pcid
								. '")z GROUP BY pid ) m LEFT JOIN (SELECT SUM(amount) AS invoice_amount ,pid FROM finance_invoice WHERE isok=1 GROUP BY pid) n ON m.pid=n.pid';

					} else {
						$pcid = $row->cid;
						$sql = 'SELECT m.*,n.invoice_amount FROM (SELECT SUM(amount) AS amount,cid,cusname FROM (SELECT a.*,b.cusname FROM (SELECT cid,amount FROM finance_deposit_receivables WHERE isok=1) a LEFT JOIN contract_cus b ON a.cid=b.cid WHERE 1=1 AND a.cid="'
								. $pcid
								. '" )z GROUP BY cid ) m LEFT JOIN (SELECT SUM(amount) AS invoice_amount,cid FROM finance_deposit_invoice WHERE isok=1 GROUP BY cid) n ON m.cid=n.cid';

					}
					//var_dump($sql);
					$info = $this->db->get_row($sql);
					$table .= '<tr><td>' . $pcid . '</td><td>' . $info->amount
							. '</td><td>' . $info->invoice_amount . '</td><td>'
							. $row->refund_amount . '</td><td>'
							. $info->cusname . '</td></tr>';
				}

				$refunddone = '';
				$dones = $this->db
						->get_results(
								'SELECT refund_date,refund_amount FROM finance_refundment_person_apply_gd WHERE apply_id='
										. intval($this->id)
										. ' AND refund_id="' . $refund_id . '"');

				if ($dones !== NULL) {
					foreach ($dones as $done) {
						$refunddone .= '<tr><td>' . $done->refund_date
								. '</td><td>' . $done->refund_amount
								. '</td></tr>';
					}
				} else {
					$refunddone .= '<tr><td colspan="2"><font color="red">无退款记录</font></td></tr>';
				}

				$buf = file_get_contents(
						TEMPLATE_PATH
								. 'finance/refund/finance_refund_apply_gd.tpl');
				return str_replace(
						array('[LEFT]', '[TOP]', '[VCODE]', '[CUSTOMERNAME]',
								'[BANKNAME]', '[BANKACCOUNT]',
								'[REFUNDAMOUNT]', '[REFUNDDATE]',
								'[REFUNDTYPE]', '[REFUNDREASON]',
								'[REFUNDDIDS]', '[REFUNDITEMS]', '[ID]',
								'[REFUNDID]', '[REFUNDDONE]', '[BASE_URL]'),
						array($this->get_left_html(), $this->get_top_html(),
								$this->get_vcode(), $customer_name, $bank_name,
								$bank_account, $refundment_amount,
								$refundment_date,
								self::_get_refund_type($refundment_type),
								Format_Util::format_html($refundment_reason),
								$this->get_upload_files($refundment_dids),
								$table, intval($this->id),
								self::_get_refund_id_show($refund_id, $isalter),
								$refunddone, BASE_URL), $buf);
			} else {
				return User::no_object('没有该退款申请');
			}

		} else {
			return User::no_permission();
		}
	}

	public function get_finance_refund_audit_html() {
		if (in_array($this->getUsername(),
				$GLOBALS['person_refund_apply_audit_permission'], TRUE)) {

			$row = $this->db
					->get_row(
							'SELECT step FROM finance_refundment_person_apply WHERE id='
									. intval($this->id));
			if ($row !== NULL) {
				if ($this->getUsername() === 'linda'
						&& intval($row->step) !== 0
						|| $this->getUsername() === 'alex.hu'
								&& intval($row->step) !== 1) {
					return User::no_permission('非审核阶段');
				} else {
					$rows = $this->db
							->get_results(
									'SELECT a.pid,a.cid,a.refund_amount,a.isok,b.refundment_amount,b.refundment_date,b.refundment_type,b.refundment_reason,b.refundment_dids,b.step,b.isok AS allisok,b.refund_id,b.isalter,c.realname,c.username,d.customer_name,d.bank_name,d.bank_account FROM finance_refundment_person_apply_list a,finance_refundment_person_apply b,users c,finance_customer_bankinfo d WHERE b.id='
											. intval($this->id)
											. ' AND a.apply_id=b.id AND b.user=c.uid AND b.customer_bankinfo_id=d.id');
					if ($rows !== NULL) {
						$refund_id = '';
						$customer_name = '';
						$bank_name = '';
						$bank_account = '';
						$refundment_amount = '';
						$refundment_date = '';
						$refundment_type = '';
						$refundment_reason = '';
						$refundment_dids = '';
						$isalter = '';
						$table = '<tr><td width="100px;">'
								. (intval($refundment_type) === 1 ? '执行单号'
										: '合同号')
								. '</td><td>到款情况</td><td>开票情况</td><td>退款金额</td><td>客户名称</td><td>操作</td></tr>';
						foreach ($rows as $key => $row) {
							if ($key === 0) {
								$customer_name = $row->customer_name;
								$bank_name = $row->bank_name;
								$bank_account = $row->bank_account;
								$refundment_amount = $row->refundment_amount;
								$refundment_date = $row->refundment_date;
								$refundment_type = $row->refundment_type;
								$refundment_reason = $row->refundment_reason;
								$refundment_dids = $row->refundment_dids;
								$refund_id = $row->refund_id;
								$isalter = $row->isalter;
							}
							if (intval($refundment_type) === 1) {
								$pcid = $row->pid;
								$sql = 'SELECT m.*,n.invoice_amount FROM (SELECT SUM(amount) AS amount,pid,cid,cusname FROM (SELECT a.*,b.cusname FROM (SELECT pid,amount,SUBSTRING_INDEX(pid,\'-\',1) AS cid FROM finance_receivables WHERE isok=1) a LEFT JOIN contract_cus b ON a.cid=b.cid WHERE 1=1  AND a.pid="'
										. $pcid
										. '")z GROUP BY pid ) m LEFT JOIN (SELECT SUM(amount) AS invoice_amount ,pid FROM finance_invoice WHERE isok=1 GROUP BY pid) n ON m.pid=n.pid';

							} else {
								$pcid = $row->cid;
								$sql = 'SELECT m.*,n.invoice_amount FROM (SELECT SUM(amount) AS amount,cid,cusname FROM (SELECT a.*,b.cusname FROM (SELECT cid,amount FROM finance_deposit_receivables WHERE isok=1) a LEFT JOIN contract_cus b ON a.cid=b.cid WHERE 1=1 AND a.cid="'
										. $pcid
										. '" )z GROUP BY cid ) m LEFT JOIN (SELECT SUM(amount) AS invoice_amount,cid FROM finance_deposit_invoice WHERE isok=1 GROUP BY cid) n ON m.cid=n.cid';

							}
							//var_dump($sql);
							$info = $this->db->get_row($sql);
							$table .= '<tr><td>' . $pcid . '</td><td>'
									. $info->amount . '</td><td>'
									. $info->invoice_amount . '</td><td>'
									. $row->refund_amount . '</td><td>'
									. $info->cusname
									. '</td><td width="300px;">';
							if (!in_array(intval($row->isok), array(1, 2), TRUE)) {
								$table .= '<input type="radio" name="itemaudit_'
										. $pcid
										. '" value="1" checked>&nbsp;通过&nbsp;&nbsp;<input type="radio" name="itemaudit_'
										. $pcid
										. '" value="2">&nbsp;驳回&nbsp;&nbsp;<input type="text" style="height:20px;" id="remark_'
										. $pcid
										. '">&nbsp;<input type="button" value="提 交" class="btn" onclick="javascript:sub(\''
										. $pcid . '\',' . intval($this->id)
										. ')">';
							} else {
								$table .= intval($row->isok) === 1 ? '审核通过'
										: '审核驳回';
							}
							$table .= '</td></tr>';
						}

						$buf = file_get_contents(
								TEMPLATE_PATH
										. 'finance/refund/finance_refund_apply_audit.tpl');
						return str_replace(
								array('[LEFT]', '[TOP]', '[VCODE]',
										'[CUSTOMERNAME]', '[BANKNAME]',
										'[BANKACCOUNT]', '[REFUNDAMOUNT]',
										'[REFUNDDATE]', '[REFUNDTYPE]',
										'[REFUNDREASON]', '[REFUNDDIDS]',
										'[REFUNDITEMS]', '[ID]', '[REFUNDID]',
										'[BASE_URL]'),
								array($this->get_left_html(),
										$this->get_top_html(),
										$this->get_vcode(), $customer_name,
										$bank_name, $bank_account,
										$refundment_amount, $refundment_date,
										self::_get_refund_type($refundment_type),
										Format_Util::format_html(
												$refundment_reason),
										$this
												->get_upload_files(
														$refundment_dids),
										$table, intval($this->id),
										self::_get_refund_id_show($refund_id,
												$isalter), BASE_URL), $buf);
					} else {
						return User::no_object('没有该退款申请');
					}
				}
			} else {
				return User::no_object('没有该退款申请');
			}
		} else {
			return User::no_permission();
		}
	}

	public function audit_refund_item() {
		if ($this->validate_form_value('audit_refund_item')) {
			$success = TRUE;
			$error = '';
			$auditall = FALSE;
			$this->db->query('BEGIN');

			$update_result = $this->db
					->query(
							'UPDATE finance_refundment_person_apply_list SET isok='
									. intval($this->itemaudit) . ',remark="'
									. $this->remark . '" WHERE apply_id='
									. intval($this->id) . ' AND '
									. (strpos($this->pid, '_') === FALSE ? 'cid='
											: 'pid=') . '"' . $this->pid . '"');
			if ($update_result === FALSE) {
				$success = FALSE;
				$error = '审核退款申请失败，错误代码1';
			} else {
				//检查审核结果
				$results = $this->db
						->get_results(
								'SELECT isok FROM finance_refundment_person_apply_list WHERE apply_id='
										. intval($this->id));
				if ($results !== NULL) {
					$allcount = count($results);
					$audits = array();
					$pass = TRUE;
					foreach ($results as $result) {
						if (in_array(intval($result->isok), array(1, 2), TRUE)) {
							if ($pass && intval($result->isok) === 2) {
								$pass = FALSE;
							}
							$audits[] = intval($result->isok);
						}
					}

					if ($allcount === count($audits)) {
						//全部审核完毕

						if ($this->getUsername() === 'linda') {
							if ($pass) {
								$sql = 'UPDATE finance_refundment_person_apply SET step=1 WHERE id='
										. intval($this->id);
							} else {
								$sql = 'UPDATE finance_refundment_person_apply SET isok=2 WHERE id='
										. intval($this->id);
							}
							$update_result = $this->db->query($sql);
							if ($update_result === FALSE) {
								$success = FALSE;
								$error = '审核退款申请失败，错误代码2';
							} else {
								if ($pass) {
									$update_result = $this->db
											->query(
													'UPDATE finance_refundment_person_apply_list SET isok=0,remark="" WHERE apply_id='
															. intval($this->id));
									if ($update_result === FALSE) {
										$success = FALSE;
										$error = '审核退款申请失败，错误代码3';
									}
								}
							}

						} else if ($this->getUsername() === 'alex.hu') {
							if ($pass) {
								$sql = 'UPDATE finance_refundment_person_apply SET step=2,isok=1 WHERE id='
										. intval($this->id);
							} else {
								$sql = 'UPDATE finance_refundment_person_apply SET step=2,isok=2 WHERE id='
										. intval($this->id);
							}
							$update_result = $this->db->query($sql);
							if ($update_result === FALSE) {
								$success = FALSE;
								$error = '审核退款申请失败，错误代码2';
							}
						}

						$auditall = TRUE;
					}
				}
			}

			if ($success) {
				$this->db->query('COMMIT');
			} else {
				$this->db->query('ROLLBACK');
			}
			return array('status' => $success ? 'success' : 'error',
					'message' => $success ? ($auditall ? 1 : 2) : $error);
		}
		return array('status' => 'error',
				'message' => implode("\n", $this->errors));
	}

	public function refund_apply_allaudit() {
		if ($this->validate_form_value('refund_apply_allaudit')) {
			$success = TRUE;
			$error = '';
			$this->db->query('BEGIN');

			if ($this->getUsername() === 'linda') {
				$update_result = $this->db
						->query(
								'UPDATE finance_refundment_person_apply SET '
										. (intval($this->auditall) === 1 ? 'step=1'
												: 'isok=2,auditremarkall="'
														. $this->auditremarkall
														. '"') . ' WHERE id='
										. intval($this->id));
				if ($update_result === FALSE) {
					$success = FALSE;
					$error = '审核退款申请失败，错误代码1';
				} else {
					if (intval($this->auditall) === 2) {
						$update_result = $this->db
								->query(
										'UPDATE finance_refundment_person_apply_list SET isok=2 WHERE apply_id='
												. intval($this->id));
						if ($update_result === FALSE) {
							$success = FALSE;
							$error = '审核退款申请失败，错误代码2';
						}
					}
				}
			} else if ($this->getUsername() === 'alex.hu') {
				$update_result = $this->db
						->query(
								'UPDATE finance_refundment_person_apply SET '
										. (intval($this->auditall) === 1 ? 'step=2,isok=1'
												: 'step=2,isok=2,auditremarkall="'
														. $this->auditremarkall
														. '"') . ' WHERE id='
										. intval($this->id));
				if ($update_result === FALSE) {
					$success = FALSE;
					$error = '审核退款申请失败，错误代码3';
				} else {
					$update_result = $this->db
							->query(
									'UPDATE finance_refundment_person_apply_list SET isok='
											. intval($this->auditall)
											. ' WHERE apply_id='
											. intval($this->id));
					if ($update_result === FALSE) {
						$success = FALSE;
						$error = '审核退款申请失败，错误代码4';
					}
				}
			}

			if ($success) {
				$this->db->query('COMMIT');
			} else {
				$this->db->query('ROLLBACK');
			}
			return array('status' => $success ? 'success' : 'error',
					'message' => $success ? '审核退款申请成功' : $error);
		}
		return array('status' => 'error',
				'message' => implode("\n", $this->errors));
	}

	public function refund_apply_gd() {
		if ($this->validate_form_value('refund_apply_gd')) {
			$success = TRUE;
			$error = '';
			$this->db->query('BEGIN');

			if (in_array($this->getUsername(),
					$GLOBALS['person_refund_apply_audit_permission'], TRUE)) {
				//检查退款申请是否可以归档
				$row = $this->db
						->get_row(
								'SELECT isok FROM finance_refundment_person_apply WHERE id='
										. intval($this->id) . ' FOR UPDATE');
				if ($row !== NULL) {
					if (intval($row->isok) === 1) {
						$insert_result = $this->db
								->query(
										'INSERT INTO finance_refundment_person_apply_gd(apply_id,refund_id,refund_amount,refund_date,addtime) SELECT '
												. intval($this->id)
												. ',refund_id,'
												. $this->refundment_amount
												. ',"' . $this->refundment_date
												. '",now() FROM finance_refundment_person_apply WHERE id='
												. intval($this->id));
						if ($insert_result === FALSE) {
							$success = FALSE;
							$error = '退款归档失败';
						}
					} else {
						$success = FALSE;
						$error = '退款申请状态无法归档';
					}
				} else {
					$success = FALSE;
					$error = '没有该退款申请';
				}
			} else {
				$success = FALSE;
				$error = NO_RIGHT_TO_DO_THIS;
			}

			if ($success) {
				$this->db->query('COMMIT');
			} else {
				$this->db->query('ROLLBACK');
			}
			return array('status' => $success ? 'success' : 'error',
					'message' => $success ? '退款归档成功' : $error);
		}
		return array('status' => 'error',
				'message' => implode("\n", $this->errors));
	}
}
