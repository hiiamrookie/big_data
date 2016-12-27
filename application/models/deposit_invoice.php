<?php
class Deposit_Invoice extends User {
	private $has_invoice_permission = FALSE;
	private $pids_array = array();
	private $d1;
	private $d2;
	private $d3;
	private $invoice_type;
	private $invoicecontent;
	private $title;
	private $content;
	private $id;
	private $audit_remark;
	private $p;

	private $number;
	private $date;

	private $errors = array();

	public function __construct($fields = array()) {
		parent::__construct();
		if (!empty($fields)) {
			foreach ($this as $key => $value) {
				if ($fields[$key] !== NULL
						&& !in_array($key, array('has_invoice_permission'),
								TRUE)) {
					$this->$key = $fields[$key];
				}
			}
		}
		if (in_array($this->getUsername(),
				$GLOBALS['manager_finance_permission'], TRUE)
				|| intval($this->getBelong_dep()) === 2) {
			$this->has_invoice_permission = TRUE;
		}
	}

	public function get_deposit_invoice_apply_html() {
		$buf = file_get_contents(
				TEMPLATE_PATH . 'finance/deposit/deposit_invoice_apply.tpl');
		return str_replace(array('[LEFT]', '[TOP]', '[VCODE]', '[BASE_URL]'),
				array($this->get_left_html(), $this->get_top_html(),
						$this->get_vcode(), BASE_URL), $buf);
	}

	private function validate_form_value($action) {
		$errors = array();
		if (in_array($action,
				array('add', 'print', 'reject', 'invoice', 'leader_confirm',
						'leader_reject', 'gd_update', 'myupdate'), TRUE)) {
			if (in_array($action,
					array('print', 'reject', 'invoice', 'leader_confirm',
							'leader_reject', 'gd_update'), TRUE)) {
				if (!self::validate_id(intval($this->id))) {
					$errors[] = '开票记录选择有误';
				}

				if ($action === 'invoice' || $action === 'gd_update') {
					if (!self::validate_field_not_empty($this->date)
							|| !self::validate_field_not_null($this->date)) {
						$errors[] = '开票日期不能为空';
					} else if (strtotime($this->date) === FALSE) {
						$errors[] = '开票日期不是一个有效的时间值';
					}

					if (!self::validate_field_not_empty($this->number)
							|| !self::validate_field_not_null($this->number)) {
						$errors[] = '开票号码不能为空';
					} else if (!self::validate_field_max_length($this->number,
							500)) {
						$errors[] = '开票号码长度最多500个字符';
					}
				} else if ($action !== 'print') {
					if (self::validate_field_not_empty($this->audit_remark)
							&& !self::validate_field_max_length(
									$this->audit_remark, 200)) {
						$errors[] = '审核意见长度最多200个字符';
					}
				}
			} else {
				$pids_array = $this->pids_array;
				if (empty($pids_array)) {
					$errors[] = '没有选择关联保证金';
				} else {
					$count = 0;
					$now_cusname = '';
					foreach ($pids_array as $key => $value) {
						if (!self::validate_invoice_money($value['amount'])) {
							$errors[] = '保证金' . $key . '的开票金额有误';
						}
						if ($value['cusname'] !== $now_cusname && $count !== 0) {
							$errors[] = '保证金' . $key . '的客户名称与其他不同';
						} else {
							$now_cusname = $value['cusname'];
						}
						if ($value['oldamount'] < $value['amount']) {
							$errors[] = '执行单' . $key . '的开票金额最多'
									. $value['oldamount'] . '元';
						}
						$count++;
					}
				}

				if (!self::validate_field_not_empty($this->title)
						|| !self::validate_field_not_null($this->title)) {
					$errors[] = '开具普票时开票抬头不能为空';
				} else if (!self::validate_field_max_length($this->title, 200)) {
					$errors[] = '开票抬头长度最多200个字符';
				}

				if (!self::validate_field_not_empty($this->content)
						|| !self::validate_field_not_null($this->content)) {
					$errors[] = '开具内容不能为空';
				} else if (!self::validate_field_max_length($this->content, 200)) {
					$errors[] = '开具内容长度最多200个字符';
				}

				if (!in_array(intval($this->invoice_type), array(0, 1, 2), TRUE)) {
					$errors[] = '开票类型有误';
				}

				if (intval($this->invoice_type) === 2) {
					if (!self::validate_field_not_empty($this->d1)
							|| !self::validate_field_not_null($this->d1)) {
						$errors[] = '开具增票时纳税人识别号不能为空';
					} else if (!self::validate_field_max_length($this->d1, 200)) {
						$errors[] = '纳税人识别号长度最多200个字符';
					}

					if (!self::validate_field_not_empty($this->d2)
							|| !self::validate_field_not_null($this->d2)) {
						$errors[] = '开具增票时地址、电话不能为空';
					} else if (!self::validate_field_max_length($this->d2, 200)) {
						$errors[] = '地址、电话长度最多200个字符';
					}

					if (!self::validate_field_not_empty($this->d3)
							|| !self::validate_field_not_null($this->d3)) {
						$errors[] = '开具增票时开户行及账号不能为空';
					} else if (!self::validate_field_max_length($this->d3, 200)) {
						$errors[] = '开户行及账号长度最多200个字符';
					}
				}

				if (self::validate_field_not_empty($this->invoicecontent)
						&& !self::validate_field_max_length(
								$this->invoicecontent, 500)) {
					$errors[] = '备注长度最多500个字符';
				}

				/*
				if (!self::validate_field_not_empty($this->process)
				        || !self::validate_field_not_null($this->process)) {
				    $errors[] = '流程选择不能为空';
				} else if (!self::validate_id(intval($this->process))) {
				    $errors[] = '流程选择有误';
				}
				 */

				if ($action === 'myupdate'
						&& !self::validate_id(intval($this->id))) {
					$errors[] = '开票信息选择有误';
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

	public function invoice_update() {
		if ($this->validate_form_value('myupdate')) {
			$success = TRUE;
			$error = '';
			$this->db->query('BEGIN');

			$delete_result = $this->db
					->query(
							'DELETE FROM finance_deposit_invoice WHERE invoice_list_id='
									. intval($this->id));
			if ($delete_result !== FALSE) {
				$pids_array = $this->pids_array;
				$ids = array();
				$content = array();
				$all_amount = 0;
				//$company = '';
				foreach ($pids_array as $pid => $value) {
					//if ($company === '') {
					//	$company = Executive::get_companyname(
					//			intval($value['company']));
					//}
					$content[] = $pid . '^' . $value['amount'];
					$all_amount += $value['amount'];
					$insert_result = $this->db
							->query(
									'INSERT INTO finance_deposit_invoice(invoice_list_id,cid,amount,user,time,cusname) VALUE('
											. intval($this->id) . ',"' . $pid
											. '","' . $value['amount'] . '",'
											. $this->getUid() . ','
											. $_SERVER['REQUEST_TIME'] . ',"'
											. $value['cusname'] . '")');
					if ($insert_result !== FALSE) {
						$ids[] = $this->db->insert_id;
					} else {
						$success = FALSE;
						$error = '修改开票信息失败，错误代码2';
						break;
					}
				}

				if ($success) {
					$update_result = $this->db
							->query(
									'UPDATE finance_deposit_invoice_list SET invoice_ids="'
											. implode('^', $ids) . '",cids="'
											. implode('|', $content)
											. '",amount=' . $all_amount
											. ',type=' . $this->invoice_type
											. ',d1="'
											. (intval($this->invoice_type)
													=== 2 ? $this->d1 : '')
											. '",d2="'
											. (intval($this->invoice_type)
													=== 2 ? $this->d2 : '')
											. '",d3="'
											. (intval($this->invoice_type)
													=== 2 ? $this->d3 : '')
											. '",title="' . $this->title
											. '",content="' . $this->content
											. '",remark="'
											. $this->invoicecontent
											. '",user="' . $this->getUid()
											. '",time='
											. $_SERVER['REQUEST_TIME']
											. ',step=1,isok=0,print=0,auditmsg="" WHERE id='
											. intval($this->id));
					if ($update_result === FALSE) {
						$success = FALSE;
						$error = '修改开票信息失败，错误代码3';
					}
				}

			} else {
				$success = FALSE;
				$error = '修改开票信息失败，错误代码1';
			}

			if ($success) {
				$this->db->query('COMMIT');
			} else {
				$this->db->query('ROLLBACK');
			}

			return array('status' => $success ? 'success' : 'error',
					'message' => $success ? '修改开票信息成功' : $error);
		}
		return array('status' => 'error', 'message' => $this->errors);
	}

	public function invoice_apply() {
		if ($this->validate_form_value('add')) {
			$success = TRUE;
			$error = '';
			$this->db->query('BEGIN');

			$pids_array = $this->pids_array;
			$ids = array();
			$content = array();
			$all_amount = 0;
			foreach ($pids_array as $pid => $value) {
				$content[] = $pid . '^' . $value['amount'];
				$all_amount += $value['amount'];
				$insert_result = $this->db
						->query(
								'INSERT INTO finance_deposit_invoice(cid,cusname,amount,user,time) VALUE("'
										. $pid . '","' . $value['cusname']
										. '","' . $value['amount'] . '",'
										. $this->getUid() . ','
										. $_SERVER['REQUEST_TIME'] . ')');
				if ($insert_result !== FALSE) {
					$ids[] = $this->db->insert_id;
				} else {
					$success = FALSE;
					$error = '申请开票失败，错误代码1';
					break;
				}
			}

			if ($success) {
				$insert_result = $this->db
						->query(
								'INSERT INTO finance_deposit_invoice_list(invoice_ids,cids,amount,type,d1,d2,d3,title,content,remark,user,time,step) VALUE("'
										. implode('^', $ids) . '","'
										. implode('|', $content) . '","'
										. $all_amount . '","'
										. $this->invoice_type . '","'
										. (intval($this->invoice_type) === 2 ? $this
														->d1 : '') . '","'
										. (intval($this->invoice_type) === 2 ? $this
														->d2 : '') . '","'
										. (intval($this->invoice_type) === 2 ? $this
														->d3 : '') . '","'
										. $this->title . '","' . $this->content
										. '","' . $this->invoicecontent . '",'
										. $this->getUid() . ','
										. $_SERVER['REQUEST_TIME'] . ',1)');
				if ($insert_result === FALSE) {
					$success = FALSE;
					$error = '申请开票失败，错误代码2';
				} else {
					$insert_id = $this->db->insert_id;
				}
			}

			if ($success && $insert_id > 0) {
				$update_result = $this->db
						->query(
								'UPDATE finance_deposit_invoice SET invoice_list_id='
										. $insert_id . ' WHERE id IN ('
										. implode(',', $ids) . ')');
				if ($update_result === FALSE) {
					$success = FALSE;
					$error = '申请开票失败，错误代码3';
				}
			}

			if ($success) {
				$this->db->query('COMMIT');
			} else {
				$this->db->query('ROLLBACK');
			}

			return array('status' => $success ? 'success' : 'error',
					'message' => $success ? '申请开票成功' : $error);
		}
		return array('status' => 'error', 'message' => $this->errors);
	}

	public function get_deposit_invoice_leader_audit_html() {
		if ($this->getHas_deposit_tab()) {
			$row = $this->db
					->get_row(
							'SELECT a.*,b.username,b.realname FROM finance_deposit_invoice_list a LEFT JOIN users b ON a.user=b.uid WHERE a.id='
									. intval($this->id)
									. ' AND a.isok=0 AND a.step=1 AND (b.dep='
									. intval($this->getBelong_dep())
									. ' AND b.team='
									. intval($this->getBelong_team()) . ')');
			if ($row !== NULL) {
				$buf = file_get_contents(
						TEMPLATE_PATH
								. 'finance/deposit/deposit_invoice_leader_audit.tpl');
				return str_replace(
						array('[LEFT]', '[TOP]', '[VCODE]', '[TIME]',
								'[AMOUNT]', '[TYPE]', '[TITLE]', '[CONTENT]',
								'[REMARK]', '[PIDINFO]', '[USERINFO]', '[ID]',
								'[BASE_URL]'),
						array($this->get_left_html(), $this->get_top_html(),
								$this->get_vcode(),
								date('Y-m-d H:i:s', $row->time), $row->amount,
								self::_getType(intval($row->type), $row->d1,
										$row->d2, $row->d3), $row->title,
								$row->content, self::_getRemark($row->remark),
								self::_get_cidinfo($row->cids),
								$row->realname . '(' . $row->username . ')',
								$this->id, BASE_URL), $buf);
			} else {
				return User::no_object('没有该保证金开票申请');
			}
		} else {
			return User::no_permission();
		}
	}

	private static function _get_cidinfo($cids) {
		$s = '';
		$cids = explode('|', $cids);
		foreach ($cids as $cid) {
			$cid = explode('^', $cid);
			$s .= $cid[0] . ': <font color="#ff9933"><b>' . $cid[1]
					. '</b></font> 元' . "\n";
		}
		return $s;
	}

	private static function _getType($type, $d1, $d2, $d3) {
		$s = '';
		if ($type === 0) {
			$s = '收据';
		} else if ($type === 1) {
			$s = '普票';
		} else if ($type === 2) {
			$s = '增票<br><br><div>纳税人识别号码： ' . $d1 . '<div>地址、电话： ' . $d2
					. '<div>开户行及账号： ' . $d3;

		}
		return $s;
	}

	private static function _getRemark($remark) {
		return Format_Util::format_html($remark);
	}

	private function _leader_audit_deposit_invoice($reject = FALSE) {
		if ($this
				->validate_form_value(
						$reject ? 'leader_reject' : 'leader_confirm')) {
			$success = TRUE;
			$error = '';
			$this->db->query('BEGIN');

			if (!$reject) {
				$update_result = $this->db
						->query(
								'UPDATE finance_deposit_invoice_list SET step=2 WHERE id='
										. intval($this->id));
			} else {
				$update_result = $this->db
						->query(
								'UPDATE finance_deposit_invoice_list SET auditmsg="'
										. $this->audit_remark
										. '",isok=-1 WHERE id='
										. intval($this->id));
			}

			if ($update_result === FALSE) {
				$success = FALSE;
				$error = $reject ? '审核驳回失败，错误代码1' : '审核确认失败，错误代码1';
			}

			if ($success && $reject) {
				$update_result = $this->db
						->query(
								'UPDATE finance_deposit_invoice SET isok=-1 WHERE invoice_list_id='
										. intval($this->id));
				if ($update_result === FALSE) {
					$success = FALSE;
					$error = '审核驳回失败，错误代码2';
				}
			}

			if ($success) {
				$this->db->query('COMMIT');
			} else {
				$this->db->query('ROLLBACK');
			}

			return array('status' => $success ? 'success' : 'error',
					'message' => $success ? '审核' . ($reject ? '驳回' : '确认')
									. '成功' : $error);
		}
		return array('status' => 'error', 'message' => $this->errors);
	}

	public function invoice_leader_confirm() {
		return $this->_leader_audit_deposit_invoice();
	}

	public function invoice_leader_reject() {
		return $this->_leader_audit_deposit_invoice(TRUE);
	}

	private function _get_invoice_type($cids) {
		$s = '';
		if (!empty($cids)) {
			$cids = reset(explode('|', $cids));
			$billtype = $this->db
					->get_var(
							'SELECT billtype FROM contract_cus WHERE cid="'
									. reset(explode('^', $cids)) . '"');
			switch (intval($billtype)) {
			case 1:
				$s = '广告业';
				break;
			case 2:
				$s = '服务业';
				break;
			}
		}
		return $s;
	}

	private static function _get_auditmsg($isok, $auditmsg) {
		if (intval($isok) === 0) {
			return '<font color="#ff6600"><b>等待审核</b></font><br>';
		} else if (intval($isok) === 1) {
			return '<font color="#66cc00"><b>已生效</b></font><br>';
		} else {
			return '<font color="red"><b>已驳回<br>驳回理由：<br>'
					. Format_Util::format_html($auditmsg) . '</b></font>';
		}
	}

	public function get_deposit_invoice_view_html($ismy = TRUE) {
		$query = 'SELECT * FROM finance_deposit_invoice_list WHERE id='
				. intval($this->id);
		if (!in_array($this->getUsername(),
				$GLOBALS['manager_finance_permission'], TRUE)
				&& intval($this->getBelong_dep()) !== 2) {
			$query .= ' AND user=' . intval($this->getUid());
		}
		$row = $this->db->get_row($query);
		if ($row !== NULL) {
			$buf = file_get_contents(
					TEMPLATE_PATH . 'finance/deposit/deposit_invoice_view.tpl');
			return str_replace(
					array('[LEFT]', '[TOP]', '[TIME]', '[AMOUNT]', '[TYPE]',
							'[TITLE]', '[CONTENT]', '[REMARK]', '[PIDINFO]',
							'[DATE]', '[NUMBER]', '[AUDITMSG]',
							'[INVOICETYPE]', '[OTHERTABS]', '[BASE_URL]'),
					array($this->get_left_html(), $this->get_top_html(),
							date('Y-m-d H:i:s', $row->time),
							Format_Util::my_money_format('%.2n', $row->amount),
							self::_getType(intval($row->type), $row->d1,
									$row->d2, $row->d3), $row->title,
							$row->content,
							Format_Util::format_html($row->remark),
							self::_get_cidinfo($row->cids), $row->date,
							$row->number,
							self::_get_auditmsg(intval($row->isok),
									$row->auditmsg),
							$this->_get_invoice_type($row->cids),
							$this->_get_other_tabs($ismy), BASE_URL), $buf);
		} else {
			return User::no_permission();
		}
	}

	private function _get_other_tabs($ismy) {
		if ($ismy) {
			return '<li><a href="' . BASE_URL
					. 'finance/deposit/?o=my_deposit_invoice_list">已申请保证金票据列表</a></li>';
		} else {
			return '<li><a href="' . BASE_URL
					. 'finance/deposit/?o=deposit_invoicelist&d=2">当月已审核</a></li>';
		}
	}

	private static function _getType_radio0($type) {
		if ($type === 0) {
			return 'checked="checked"';
		} else {
			return '';
		}
	}

	private static function _getType_radio1($type) {
		if ($type === 1) {
			return 'checked="checked"';
		} else {
			return '';
		}
	}

	private static function _getType_radio2($type) {
		if ($type === 2) {
			return 'checked="checked"';
		} else {
			return '';
		}
	}

	private function _getExist_cids($cids) {
		$s = '';
		$billtype = 1;
		if (!empty($cids)) {
			$cids = explode('|', $cids);
			foreach ($cids as $key => $cid) {
				$cid = explode('^', $cid);
				$row = $this->db
						->get_row(
								'SELECT a.cusname,a.amount,b.billtype FROM finance_deposit a LEFT JOIN contract_cus b ON a.cid=b.cid WHERE a.cid="'
										. $cid[0] . '"');
				if ($key === 0) {
					$this->billtype = intval($row->billtype);
				}
				$amount = $this->db
						->get_var(
								'SELECT SUM(amount) FROM finance_deposit_invoice WHERE cid="'
										. $cid[0] . '" AND isok=1');
				if ($amount === NULL) {
					$amount = 0;
				}

				$s .= '<div><img src="' . BASE_URL
						. 'images/close.png" onclick="removepid(this,\''
						. $cid[0]
						. '\')" width="12" height="12" />
					  	&nbsp;<span id="pid" style="display:inline-block;width:90px;text-align:left">'
						. $cid[0] . '</span>
					  	<span title="' . $row->cusname
						. '" style="display:inline-block;width:140px;text-align:left">'
						. String_Util::cut_str($row->cusname, 10, 0, 'UTF-8',
								'...')
						. '</span><input type="hidden" name="cusname_'
						. $cid[0] . '" value="' . $row->cusname
						. '">
					  	&nbsp;&nbsp;<font color="#ff9933">已开票: </font>
					  	<span style="display:inline-block;width:80px;"><font color="#ff9933"><b>'
						. Format_Util::my_money_format('%.2n', $amount)
						. '</b></font></span>
					  	&nbsp;&nbsp;&nbsp;&nbsp;<font color="blue">未开票: </font>
					  	<input type="text" class="validate[required,custom[invoiceMoney],max['
						. round($row->amount - $amount, 2)
						. ']] text" style="width:80px;text-align:right " name="amount_'
						. $cid[0] . '" id="amount_' . $cid[0]
						. '" onblur="getallamount();" value="'
						. round($cid[1], 2)
						. '" /><input type="hidden" name="oldamount_' . $cid[0]
						. '" id="oldamount_' . $cid[0] . '" value="'
						. round($row->amount - $amount, 2) . '"></div>';
			}
		}
		return $s;
	}

	private function _select_type($type) {
		$s = '';
		if (in_array($type, array(0, 1, 2), TRUE)) {
			$s .= 'showzp(' . $type . ');';
		}
		$s .= '$(\'input:radio[name="billtype"]\').each(function(){
			if($(this).val()==' . $this->billtype
				. '){
				$(this).attr("checked",true);
			}
			$(this).attr("disabled",true);
		});';
		return $s;
	}

	private function _getHidden_cids($cids) {
		if (empty($cids)) {
			return ',';
		} else {
			$res = array();
			$cids = explode('|', $cids);
			foreach ($cids as $cid) {
				$cid = explode('^', $cid);
				$res[] = $cid[0];
			}
			if (!empty($res)) {
				return ',' . implode(',', $res) . ',';
			} else {
				return ',';
			}
		}
	}

	public function get_deposit_invoice_edit_html() {
		$query = 'SELECT * FROM finance_deposit_invoice_list WHERE id='
				. intval($this->id) . ' AND user=' . intval($this->getUid())
				. ' AND isok=-1';
		$row = $this->db->get_row($query);
		if ($row !== NULL) {
			$buf = file_get_contents(
					TEMPLATE_PATH . 'finance/deposit/deposit_invoice_edit.tpl');
			return str_replace(
					array('[LEFT]', '[TOP]', '[AMOUNT]', '[TITLE]',
							'[CONTENT]', '[REMARK]', '[VCODE]', '[D1]', '[D2]',
							'[D3]', '[TYPERADIO0]', '[TYPERADIO1]',
							'[TYPERADIO2]', '[EXISTPIDS]', '[SELECTTYPE]',
							'[PIDS]', '[ID]', '[BASE_URL]'),
					array($this->get_left_html(), $this->get_top_html(),
							Format_Util::my_money_format('%.2n', $row->amount),
							$row->title, $row->content, $row->remark,
							$this->get_vcode(), $row->d1, $row->d2, $row->d3,
							self::_getType_radio0(intval($row->type)),
							self::_getType_radio1(intval($row->type)),
							self::_getType_radio2(intval($row->type)),
							$this->_getExist_cids($row->cids),
							$this->_select_type(intval($row->type)),
							self::_getHidden_cids($row->cids), $this->id,
							BASE_URL), $buf);
		} else {
			return User::no_permission();
		}
	}

	private function _getNone1($print) {
		return $print === 1 ? 'none' : '';
	}

	private function _getNone2($print) {
		return $print === 1 ? '' : 'none';
	}

	private function _getUserinfo($_dep, $username, $realname) {
		$dep = Dep::getInstance();
		$my_dep = $dep[$_dep];
		if ($my_dep === NULL) {
			$my_dep = '';
		} else {
			$my_dep = $this->get_depname($my_dep[1], $my_dep[0]);
		}
		return sprintf('%s %s %s', $username, $realname, $my_dep);
	}

	public function get_invoice_audit_html() {
		if ($this->has_invoice_permission) {
			$row = $this->db
					->get_row(
							'SELECT a.*,b.dep,b.realname,b.username FROM finance_deposit_invoice_list a LEFT JOIN users b ON a.user=b.uid WHERE a.id='
									. intval($this->id)
									. ' AND a.isok=0 AND (a.print=0 OR a.print=1) AND a.step=2');
			if ($row !== NULL) {
				$buf = file_get_contents(
						TEMPLATE_PATH
								. 'finance/deposit/deposit_invoice_audit.tpl');
				return str_replace(
						array('[LEFT]', '[TOP]', '[VCODE]', '[TIME]',
								'[AMOUNT]', '[TYPE]', '[TITLE]', '[CONTENT]',
								'[REMARK]', '[PIDINFO]', '[DATE]', '[NUMBER]',
								'[AUDITMSG]', '[NONE1]', '[NONE2]',
								'[USERINFO]', '[ID]', '[INVOICETYPE]',
								'[BASE_URL]'),
						array($this->get_left_html(), $this->get_top_html(),
								$this->get_vcode(),
								date('Y-m-d H:i:s', $row->time),
								Format_Util::my_money_format('%.2n',
										$row->amount),
								self::_getType(intval($row->type), $row->d1,
										$row->d2, $row->d3), $row->title,
								$row->content, self::_getRemark($row->remark),
								self::_get_cidinfo($row->cids), $row->date,
								$row->number,
								self::_get_auditmsg(intval($row->isok),
										$row->auditmsg),
								$this->_getNone1(intval($row->print)),
								$this->_getNone2(intval($row->print)),
								$this
										->_getUserinfo($row->dep,
												$row->username, $row->realname),
								$this->id,
								$this->_get_invoice_type($row->cids), BASE_URL),
						$buf);
			} else {
				return User::no_object('没有该保证金开票申请');
			}
		} else {
			return User::no_permission();
		}
	}

	public function getHas_invoice_permission() {
		return $this->has_invoice_permission;
	}

	public function deposit_invoice_print() {
		if ($this->validate_form_value('print')) {
			$update_result = $this->db
					->query(
							'UPDATE finance_deposit_invoice_list SET auditmsg="'
									. $this->audit_remark
									. '",print=1 WHERE id=' . intval($this->id));
			if ($update_result === FALSE) {
				return array('status' => 'error', 'message' => '确认打印失败');
			}
			return array('status' => 'success', 'message' => '确认打印成功');
		}
		return array('status' => 'error', 'message' => $this->errors);
	}

	public function deposit_invoice_reject() {
		if ($this->validate_form_value('reject')) {
			$success = TRUE;
			$error = '';
			$this->db->query('BEGIN');

			$update_result = $this->db
					->query(
							'UPDATE finance_deposit_invoice_list SET auditmsg="'
									. $this->audit_remark
									. '",isok=-1 WHERE id=' . intval($this->id));
			if ($update_result === FALSE) {
				$success = FALSE;
				$error = '审核驳回失败，错误代码1';
			} else {
				$update_result = $this->db
						->query(
								'UPDATE finance_deposit_invoice SET isok=-1 WHERE invoice_list_id='
										. intval($this->id));
				if ($update_result === FALSE) {
					$success = FALSE;
					$error = '审核驳回失败，错误代码2';
				}
			}

			if ($success) {
				$this->db->query('COMMIT');
			} else {
				$this->db->query('ROLLBACK');
			}

			return array('status' => $success ? 'success' : 'error',
					'message' => $success ? '审核驳回成功' : $error);
		}
		return array('status' => 'error', 'message' => $this->errors);
	}

	public function deposit_invoice_make() {
		if ($this->validate_form_value('invoice')) {
			$update_result = $this->db
					->query(
							'UPDATE finance_deposit_invoice_list SET date="'
									. $this->date . '",number="'
									. $this->number . '",isok=1,gduser='
									. $this->getUid() . ',gdtime='
									. $_SERVER['REQUEST_TIME'] . ' WHERE id='
									. intval($this->id));
			if ($update_result === FALSE) {
				return array('status' => 'error', 'message' => '确认开票失败');
			}
			return array('status' => 'success', 'message' => '确认开票成功');
		}
		return array('status' => 'error', 'message' => $this->errors);
	}

	public function deposit_invoice_gd_update() {
		if ($this->validate_form_value('gd_update')) {
			$update_result = $this->db
					->query(
							'UPDATE finance_deposit_invoice_list SET date="'
									. $this->date . '",number="'
									. $this->number . '" WHERE id='
									. intval($this->id));
			if ($update_result === FALSE) {
				return array('status' => 'error', 'message' => '更新开票信息失败');
			}
			return array('status' => 'success', 'message' => '更新开票信息成功');
		}
		return array('status' => 'error', 'message' => $this->errors);
	}

	private static function _get_others($isok, $date, $number) {
		if ($isok === 1) {
			return <<<EOF
			<tr>
		          <td style="font-weight:bold">开票日期</td>
		          <td>$date</td>
		        </tr>
		        <tr>
		          <td style="font-weight:bold">开票号码</td>
		          <td>$number</td>
		        </tr>
EOF;
		} else {
			return '';
		}
	}

	public function get_deposit_invoice_print_html() {
		if ($this->has_invoice_permission) {
			$row = $this->db
					->get_row(
							'SELECT a.*,b.dep,b.realname,b.username FROM finance_deposit_invoice_list a LEFT JOIN users b ON a.user=b.uid WHERE a.id='
									. intval($this->id));
			$buf = file_get_contents(
					TEMPLATE_PATH . 'finance/deposit/deposit_invoice_print.tpl');
			return str_replace(
					array('[TIME]', '[AMOUNT]', '[TYPE]', '[TITLE]',
							'[CONTENT]', '[REMARK]', '[PIDINFO]', '[USERINFO]',
							'[INVOICETYPE]', '[OTHERS]'),
					array(date('Y-m-d H:i:s', $row->time),
							Format_Util::my_money_format('%.2n', $row->amount),
							self::_getType(intval($row->type), $row->d1,
									$row->d2, $row->d3), $row->title,
							$row->content, self::_getRemark($row->remark),
							self::_get_cidinfo($row->cids),
							$this
									->_getUserinfo($row->dep, $row->username,
											$row->realname),
							$this->_get_invoice_type($row->cids),
							self::_get_others(intval($row->isok), $row->date,
									$row->number)), $buf);
		} else {
			return User::no_permission();
		}
	}

	function get_deposit_invoice_gdupdate_html() {
		if ($this->has_invoice_permission) {
			$row = $this->db
					->get_row(
							'SELECT a.*,b.dep,b.realname,b.username FROM finance_deposit_invoice_list a LEFT JOIN users b ON a.user=b.uid WHERE a.id='
									. intval($this->id));
			if ($row !== NULL) {
				$buf = file_get_contents(
						TEMPLATE_PATH
								. 'finance/deposit/deposit_invoice_gdupdate.tpl');
				return str_replace(
						array('[LEFT]', '[TOP]', '[VCODE]', '[TIME]',
								'[AMOUNT]', '[TYPE]', '[TITLE]', '[CONTENT]',
								'[REMARK]', '[PIDINFO]', '[DATE]', '[NUMBER]',
								'[AUDITMSG]', '[NONE1]', '[NONE2]',
								'[USERINFO]', '[ID]', '[INVOICETYPE]',
								'[BASE_URL]'),
						array($this->get_left_html(), $this->get_top_html(),
								$this->get_vcode(),
								date('Y-m-d H:i:s', $row->time),
								Format_Util::my_money_format('%.2n',
										$row->amount),
								self::_getType(intval($row->type), $row->d1,
										$row->d2, $row->d3), $row->title,
								$row->content, self::_getRemark($row->remark),
								self::_get_cidinfo($row->cids), $row->date,
								$row->number,
								self::_get_auditmsg(intval($row->isok),
										$row->auditmsg),
								$this->_getNone1(intval($row->print)),
								$this->_getNone2(intval($row->print)),
								$this
										->_getUserinfo($row->dep,
												$row->username, $row->realname),
								$this->id,
								$this->_get_invoice_type($row->cids), BASE_URL),
						$buf);
			} else {
				return User::no_object('没有该保证金开票申请');
			}
		} else {
			return User::no_permission();
		}
	}

	public function get_import_deposit_invoice_html() {
		if ($this->has_invoice_permission) {
			$buf = file_get_contents(
					TEMPLATE_PATH
							. 'finance/deposit/deposit_invoice_import.tpl');
			return str_replace(
					array('[LEFT]', '[TOP]', '[VCODE]', '[VALIDATEFILE]',
							'[MAXFILESIZE]', '[BASE_URL]'),
					array($this->get_left_html(), $this->get_top_html(),
							$this->get_vcode(),
							implode(',',
									$GLOBALS['defined_upload_execel_validate_type']),
							UPLOAD_FILE_MAX_SIZE / (1024 * 1024), BASE_URL),
					$buf);
		} else {
			return User::no_permission();
		}
	}

private function _check_format($line, $infos, &$errors) {
		$isok = TRUE;
		//第一列 合同号
		if (!self::validate_field_not_null($infos[0])
				|| !self::validate_field_not_empty($infos[0])) {
			$errors[] = '第' . $line . '行，第1列【执行单号】不能为空';
			$isok = $isok ? FALSE : $isok;
		} else if (!self::validate_field_max_length($infos[0], 100)) {
			$errors[] = '第' . $line . '行，第1列【执行单号】长度最多100个字符';
			$isok = $isok ? FALSE : $isok;
		}

		//第二列 发票金额
		if (!self::validate_field_not_null($infos[1])) {
			$errors[] = '第' . $line . '行，第2列【发票金额】不能为空';
			$isok = $isok ? FALSE : $isok;
		} else if (!self::validate_invoice_money($infos[1])) {
			$errors[] = '第' . $line . '行，第2列【发票金额】不是有效的金额';
			$isok = $isok ? FALSE : $isok;
		}

		//第三列 发票号码
		if (!self::validate_field_not_null($infos[2])
				|| !self::validate_field_not_empty($infos[2])) {
			$errors[] = '第' . $line . '行，第3列【发票号码】不能为空';
			$isok = $isok ? FALSE : $isok;
		} else if (!self::validate_field_max_length($infos[2], 500)) {
			$errors[] = '第' . $line . '行，第3列【发票号码】长度最多500个字符';
			$isok = $isok ? FALSE : $isok;
		}

		//第四列 开票日期
		if (!self::validate_field_not_null($infos[3])) {
			$errors[] = '第' . $line . '行，第4列【开票日期】不能为空';
			$isok = $isok ? FALSE : $isok;
		} else if (!self::validate_date_int(
				PHPExcel_Shared_Date::ExcelToPHP($infos[3]))) {
			$errors[] = '第' . $line . '行，第4列【开票日期】不是有效的时间值';
			$isok = $isok ? FALSE : $isok;
		}

		//第五列 收据/增票/普票
		if (!in_array($infos[4], array('收据','增票', '普票'), TRUE)) {
			$errors[] = '第' . $line . '行，第5列【收据/增票/普票】输入有误，请输“收据”或者“增票”或者“普票”';
			$isok = $isok ? FALSE : $isok;
		}

		//第六列 开票抬头
		if (!self::validate_field_not_null($infos[5])
				|| !self::validate_field_not_empty($infos[5])) {
			$errors[] = '第' . $line . '行，第6列【开票抬头】不能为空';
			$isok = $isok ? FALSE : $isok;
		} else if (!self::validate_field_max_length($infos[5], 200)) {
			$errors[] = '第' . $line . '行，第6列【开票抬头】长度最多200个字符';
			$isok = $isok ? FALSE : $isok;
		}

		return $isok;
	}
	
	public function import_invoice($file) {
		if (!$this->has_invoice_permission) {
			return array('status' => 'error', 'message' => NO_RIGHT_TO_DO_THIS);
		}
		if (file_exists($file)) {
			$PHPExcel = new PHPExcel();
			if (strtolower(pathinfo($file, PATHINFO_EXTENSION)) === 'xls') {
				$PHPReader = new PHPExcel_Reader_Excel5();
			} else if (strtolower(pathinfo($file, PATHINFO_EXTENSION))
					=== 'xlsx') {
				$PHPReader = new PHPExcel_Reader_Excel2007();
			}

			$PHPExcel = $PHPReader->load($file);
			$PHPExcel->setActiveSheetIndex(0);
			$sheet = $PHPExcel->getActiveSheet();
			$sum_cols_count = PHPExcel_Cell::columnIndexFromString(
					$sheet->getHighestColumn());
			$sum_rows_count = $sheet->getHighestRow();
			$errors = array();
			//应该是6列，行数>1
			if ($sum_cols_count !== 6) {
				$errors[] = '上传文件的列数非有效';
			} else if ($sum_rows_count <= 1) {
				$errors[] = '上传文件的行数非有效';
			} else {

				for ($i = 2; $i <= $sum_rows_count; $i++) {
					for ($j = 0; $j < $sum_cols_count; $j++) {
						$infos[$i][$j] = $sheet->getCellByColumnAndRow($j, $i)
								->getCalculatedValue();
						$infos[$i][$j] = $infos[$i][$j] === NULL ? NULL
								: trim($infos[$i][$j]);
					}
					$this->db->query('BEGIN');
					$isok = $this->_check_format($i, $infos[$i], $errors);

					if ($isok) {
						$db_ok = TRUE;
						$row = $this->db->get_row('SELECT a.cid,a.cusname,a.amount,b.invoice FROM finance_deposit a LEFT JOIN (SELECT SUM(amount) AS invoice,cid FROM finance_deposit_invoice WHERE cid="'
												. $infos[$i][0]
												. '" AND isok=1) b ON a.cid=b.cid WHERE a.cid="' . $infos[$i][0] . '" AND a.isok>=0');
						if ($row === NULL) {
							$db_ok = FALSE;
							$errors[] = '第' . $i . '行合同号保证金不存在';
						} else {
							$amount = $row->amount;
							$invoice = $row->invoice;
							if ($invoice + $infos[$i][1] > $amount) {
								$db_ok = FALSE;
								$errors[] = '第' . $i . '行记录开票金额大于该保证金还可开票余额';
							} else {

								$result1 = $this->db
										->query(
												'INSERT INTO finance_deposit_invoice(cid,amount,user,time,cusname) VALUE("'
														. $infos[$i][0] . '",'
														. $infos[$i][1] . ','
														. $this->getUid() . ','
														. $_SERVER['REQUEST_TIME'] . ',"' . $row->cusname
														. '")');
								if ($result1 === FALSE) {
									$db_ok = FALSE;
								} else {
									$id = $this->db->insert_id;
									$result2 = $this->db
											->query(
													'INSERT INTO finance_deposit_invoice_list(invoice_ids,cids,amount,type,d1,d2,d3,title,content,remark,user,time,step,gduser,gdtime,isok,print,number,date) VALUE("'
															. $id . '","'
															. $infos[$i][0]
															. '^'
															. $infos[$i][1]
															. '","'
															. $infos[$i][1]
															. '","'
															. ($infos[$i][4]
																	=== '增票' ? 2
																	: ($infos[$i][4] === '普票' ? 1 : 0))
															. '","","","","'
															. $infos[$i][5]
															. '","","",'
															. $this->getUid()
															. ','
															. $_SERVER['REQUEST_TIME']
															. ',2,'
															. $this->getUid()
															. ','
															. $_SERVER['REQUEST_TIME']
															. ',1,1,"'
															. $infos[$i][2]
															. '","'
															. date('Y-m-d',
																	PHPExcel_Shared_Date::ExcelToPHP(
																			$infos[$i][3]))
															. '")');
									if ($result2 === FALSE) {
										$db_ok = FALSE;
									} else {
										$list_id = $this->db->insert_id;

										$result3 = $this->db
												->query(
														'UPDATE finance_deposit_invoice SET invoice_list_id='
																. $list_id
																. ' WHERE id='
																. $id);
										if ($result3 === FALSE) {
											$db_ok = FALSE;
										}
									}
								}
							}
						}

						if ($db_ok) {
							$this->db->query('COMMIT');
						} else {
							$errors[] = '第' . $i . '行记录导入失败';
							$this->db->query('ROLLBACK');
						}
					} else {
						$this->db->query('COMMIT');
					}
				}
			}

			return array('status' => 'success',
					'message' => empty($errors) ? '导入成功' : $errors);
		} else {
			return array('status' => 'error', 'message' => '上传的文件不存在');
		}
	}
}
