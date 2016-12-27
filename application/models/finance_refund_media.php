<?php
class Finance_Refund_Media extends User {
	private $id;
	private $media_name;
	private $receivables_type;
	private $nimbank_id;
	private $refund_amount;
	private $refund_type;
	private $refund_date;
	private $remark;
	private $page;
	private $all_count;
	private $page_count;

	private $has_finance_refund_permission = FALSE;
	private $errors = array();
	private $pids_array = array();
	const LIMIT = 50;

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

	public function get_finance_refund_media_add_html() {
		if ($this->has_finance_refund_permission) {
			$buf = file_get_contents(
					TEMPLATE_PATH
							. 'finance/refund/finance_refund_media_add.tpl');
			return str_replace(
					array('[LEFT]', '[TOP]', '[VCODE]', '[NIMBANKSELECT]',
							'[BASE_URL]'),
					array($this->get_left_html(), $this->get_top_html(),
							$this->get_vcode(),
							Nim_BankInfo::get_bank_account_list(), BASE_URL),
					$buf);
		}
		return User::no_permission();
	}

	private function validate_form_value($action) {
		$errors = array();
		if (in_array($action, array('add', 'cancel'), TRUE)) {
			if ($action === 'cancel') {
				if (!self::validate_id(intval($this->id))) {
					$errors[] = '媒体退款记录选择有误';
				}
			} else {
				//媒体名称，不能为空，最多255个字符
				if (!self::validate_field_not_empty($this->media_name)
						|| !self::validate_field_not_null($this->media_name)) {
					$errors[] = '媒体名称不能为空';
				} else if (!self::validate_field_max_length($this->media_name,
						255)) {
					$errors[] = '媒体名称最多255个字符';
				}

				//收款方式
				if (!in_array(intval($this->receivables_type), array(1, 2),
						TRUE)) {
					$errors[] = '收款方式选择有误';
				}

				//银行
				if (intval($this->receivables_type) === 2
						&& !self::validate_id(intval($this->nimbank_id))) {
					$errors[] = '如选择转账则银行信息必须选择';
				}

				//退款金额
				if (!self::validate_field_not_empty($this->refund_amount)
						|| !self::validate_field_not_null($this->refund_amount)) {
					$errors[] = '退款金额不能为空';
				} else if (!self::validate_money($this->refund_amount)) {
					$errors[] = '退款金额不是有效的金额数值';
				}

				//退款类型
				if (!in_array(intval($this->refund_type), array(1, 2), TRUE)) {
					$errors[] = '退款类型选择有误';
				}

				//退款日期
				if (!self::validate_field_not_empty($this->refund_date)
						|| !self::validate_field_not_null($this->refund_date)) {
					$errors[] = '退款日期不能为空';
				} else if (strtotime($this->refund_date) === FALSE) {
					$errors[] = '退款日期不是一个有效的时间值';
				}

				//备注
				if (!empty($this->remark)
						&& !self::validate_field_max_length($this->remark, 500)) {
					$errors[] = '备注最多500个字符';
				}

				$pids_array = $this->pids_array;
				if (empty($pids_array)) {
					$errors[] = '没有分配退款额';
				} else {
					$allval = 0;
					foreach ($pids_array as $key => $value) {
						if (!self::validate_invoice_money($value)) {
							$errors[] = $key . '的分配退款额有误';
						} else {
							$allval += $value;
						}
					}

					if ($allval != $this->refund_amount) {
						$errors[] = '退款金额必须等于各单退款的总合';
					}
				}
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

	public function add_media_refund() {
		if ($this->validate_form_value('add')) {
			$success = TRUE;
			$error = '';
			$this->db->query('BEGIN');

			//生成退款单号
			$refundid = $this
					->getSequence(
							date('y', time()) . $this->getCity_show()
									. date('m', time()) . 'MR');

			if ($refundid === FALSE) {
				$success = FALSE;
				$error = '生成单号出错';
			}

			if ($success) {
				$result = $this->db
						->query(
								'INSERT INTO finance_refundment_media(media_name,receivables_type,nimbank_id,refund_amount,refund_type,refund_date,remark,user,isok,refund_id) VALUE("'
										. $this->media_name . '",'
										. intval($this->receivables_type) . ','
										. (intval($this->receivables_type)
												=== 2 ? intval(
														$this->nimbank_id)
												: 'NULL') . ','
										. $this->refund_amount . ','
										. intval($this->refund_type) . ',"'
										. $this->refund_date . '","'
										. $this->remark . '",'
										. $this->getUid() . ',1,"' . $refundid
										. '")');
				if ($result === FALSE) {
					$success = FALSE;
					$error = '新增媒体退款记录失败，错误代码1';
				} else {
					$record_id = $this->db->insert_id;
					$subsql = array();
					$pids_array = $this->pids_array;
					foreach ($pids_array as $key => $pa) {
						$subsql[] = '(' . $record_id . ',"' . $key . '",' . $pa
								. ',"' . $refundid . '",1)';
					}
					if (!empty($subsql)) {
						$result = $this->db
								->query(
										'INSERT INTO finance_refundment_media_list(record_id,'
												. (intval($this->refund_type)
														=== 1 ? 'pid' : 'cid')
												. ',refund_amount,refund_id,isok) VALUES'
												. implode(',', $subsql));
						if ($result === FALSE) {
							$success = FALSE;
							$error = '新增媒体退款记录失败，错误代码3';
						}
					} else {
						$success = FALSE;
						$error = '新增媒体退款记录失败，错误代码2';
					}
				}
			}

			if ($success) {
				$this->db->query('COMMIT');
			} else {
				$this->db->query('ROLLBACK');
			}

			return array('status' => $success ? 'success' : 'error',
					'message' => $success ? '新增媒体退款记录成功' : $error);
		}
		return array('status' => 'error', 'message' => $this->errors);
	}

	public function search_payment() {
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
			$sql = 'SELECT m.*,n.invoice_amount FROM (SELECT SUM(amount) AS amount,pid,cid,cusname FROM (SELECT a.*,b.cusname FROM (SELECT pid,gd_amount AS amount,SUBSTRING_INDEX(pid,\'-\',1) AS cid FROM finance_payment_gd) a LEFT JOIN contract_cus b ON a.cid=b.cid WHERE 1=1 '
					. (!empty($subsql) ? ' AND ' . implode(' AND ', $subsql)
							: '')
					. ')z GROUP BY pid ) m LEFT JOIN (SELECT SUM(receiveinvoice_amount) AS invoice_amount ,pid FROM finance_receiveinvoice_payment WHERE isok=1 GROUP BY pid) n ON m.pid=n.pid';

		} else if (intval($refundment_type) === 2) {
			//保证金
			/*
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
			 */
		}
		//var_dump($sql);
		$s = '<table width="100%"><tr><td></td><td>'
				. (intval($refundment_type) === 1 ? '执行单号' : '合同号')
				. '</td><td>客户名称</td><td>已付款金额</td><td>已收票金额</td></tr>';
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

	private function _get_list_data() {
		$this->all_count = intval(
				$this->db
						->get_var(
								'SELECT COUNT(*) FROM finance_refundment_media'));
		$this->page_count = ceil($this->all_count / self::LIMIT);
		$start = self::LIMIT * intval($this->page) - self::LIMIT;
		if ($start < 0) {
			$start = 0;
		}
		$results = array();
		$lists = $this->db
				->get_results(
						'SELECT a.id,a.media_name,a.receivables_type,a.refund_amount,a.refund_type,a.refund_date,a.refund_id,a.isok,b.bank_name,b.bank_account FROM finance_refundment_media a LEFT JOIN finance_nim_bankinfo b ON a.nimbank_id=b.id ORDER BY id DESC LIMIT '
								. $start . ',' . self::LIMIT);
		if ($lists !== NULL) {
			foreach ($lists as $list) {
				$results[] = array('id' => $list->id,
						'media_name' => $list->media_name,
						'receivables_type' => $list->receivables_type,
						'refund_amount' => $list->refund_amount,
						'refund_type' => $list->refund_type,
						'refund_date' => $list->refund_date,
						'bank_name' => $list->bank_name,
						'bank_account' => $list->bank_account,
						'refund_id' => $list->refund_id, 'isok' => $list->isok);
			}
		}
		return $results;
	}

	private static function _get_status($isok) {
		return $isok === -1 ? '<font color="red"><b>撤销</b></font>'
				: '<font color="green"><b>正常</b></font>';
	}

	private static function _get_action($id, $isok) {
		$s = '';
		if ($isok === 1) {
			$s .= '<a href="javascript:cancel(' . $id . ')">撤销</a>';
		}
		return $s;
	}

	private static function _get_refund_type($type) {
		$s = '';
		switch ($type) {
		case 1:
			$s = '合同款';
			break;
		case 2:
			$s = '保证金';
			break;
		}
		return $s;
	}

	private function _get_refund_list_html() {
		$datas = $this->_get_list_data();
		$result = '';
		if (!empty($datas)) {
			foreach ($datas as $data) {
				$result .= '<tr><td>' . $data['refund_id'] . '</td><td>'
						. $data['media_name'] . '</td><td>'
						. $data['refund_amount'] . '</td><td>'
						. $data['refund_date'] . '</td><td>'
						. self::_get_refund_type(intval($data['refund_type']))
						. '</td><td>'
						. self::_get_status(intval($data['isok']))
						. '</td><td>'
						. self::_get_action($data['id'], intval($data['isok']))
						. '</td></tr>';
			}
		}
		return $result;
	}

	private function _get_refund_counts() {
		return $this->page . '	/' . $this->page_count . ' 页 &nbsp;&nbsp;';
	}

	private function _get_pagination($is_prev) {
		return '<a href="' . BASE_URL
				. 'finance/refund/?o=mediarefundlist&page='
				. ($is_prev ? intval($this->page) - 1 : intval($this->page) + 1)
				. '">' . ($is_prev ? '上一页' : '下一页') . '</a>';
	}

	private function _getNext() {
		if (intval($this->page) >= intval($this->page_count)) {
			return '';
		} else {
			return $this->_get_pagination(FALSE);
		}
	}

	private function _getPrev() {
		if (intval($this->page) === 1) {
			return '';
		} else {
			return $this->_get_pagination(TRUE);
		}
	}

	public function get_finance_refund_media_list_html() {
		if ($this->has_finance_refund_permission) {
			$buf = file_get_contents(
					TEMPLATE_PATH
							. 'finance/refund/finance_refund_media_list.tpl');
			return str_replace(
					array('[LEFT]', '[TOP]', '[REFUNDLIST]', '[ALLCOUNTS]',
							'[COUNTS]', '[NEXT]', '[PREV]', '[VCODE]',
							'[BASE_URL]'),
					array($this->get_left_html(), $this->get_top_html(),
							$this->_get_refund_list_html(), $this->all_count,
							$this->_get_refund_counts(), $this->_getNext(),
							$this->_getPrev(), $this->get_vcode(), BASE_URL),
					$buf);
		} else {
			User::no_permission();
		}
	}

	function cancel_media_refund() {
		if ($this->validate_form_value('cancel')) {
			$success = TRUE;
			$error = '';
			$this->db->query('BEGIN');

			$result = $this->db
					->query(
							'UPDATE finance_refundment_media SET isok=-1 WHERE id='
									. intval($this->id));
			if ($result === FALSE) {
				$success = FALSE;
				$error = '撤销媒体退款记录失败，错误代码1';
			} else {
				$result = $this->db
						->query(
								'UPDATE finance_refundment_media_list SET isok=-1 WHERE record_id='
										. intval($this->id));
				if ($result === FALSE) {
					$success = FALSE;
					$error = '撤销媒体退款记录失败，错误代码2';
				}
			}

			if ($success) {
				$this->db->query('COMMIT');
			} else {
				$this->db->query('ROLLBACK');
			}

			return array('status' => $success ? 'success' : 'error',
					'message' => $success ? '撤销媒体退款记录成功' : $error);
		}
		return array('status' => 'error', 'message' => $this->errors);
	}
}
