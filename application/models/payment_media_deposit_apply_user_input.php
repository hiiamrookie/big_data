<?php
class Payment_Media_Deposit_Apply_User_Input extends User {
	private $id;
	private $assignid;
	private $payment_list = array();
	private $itemids = array();
	private $errors = array();

	public function __construct($fields = array()) {
		parent::__construct();
		if (!empty($fields)) {
			foreach ($this as $key => $value) {
				if ($fields[$key] !== NULL) {
					$this->$key = $fields[$key];
				}
			}
		}
	}

	private function _get_statement_show($apply_id) {
		$s = '<table width="100%" class="sbd1">';
		$s .= '<tr><td></td><td><b>广告主</b></td><td><b>媒体合同号</b></td><td><b>本次申请保证金金额</b></td><td><b>合同付款日期</b></td><td><b>框架金额</b></td><td><b>框架开始日期</b></td><td><b>操作</b>&nbsp;&nbsp;<input type="button" value="分配" class="btn" id="fpbtn"></td></tr>';
		$results = $this->db
				->get_results(
						'SELECT id,ggz,mthth,sqje,htfkrq,kjje,kjkssj FROM finance_payment_media_deposit_apply_items WHERE apply_id='
								. intval($apply_id) . ' AND isok=1');
		if ($results !== NULL) {
			foreach ($results as $result) {
				$s .= '<tr id="statetr_' . $result->id
						. '"><td><input type="checkbox" name="itemselect" value="'
						. $result->id . '"></td><td id="ggz_' . $result->id
						. '">' . $result->ggz . '</td><td id="mthth_'
						. $result->id . '">' . $result->mthth
						. '</td><td id="sqje_' . $result->id . '">'
						. $result->sqje . '</td><td id="htfkrq_' . $result->id
						. '">' . $result->htfkrq . '</td><td id="kjje_'
						. $result->id . '">' . $result->kjje
						. '</td><td id="kjkssj_' . $result->id . '">'
						. $result->kjkssj
						. '</td><td><input type="button" value="跳过" class="btn" onclick="javascript:pass_statement('
						. $result->id . ');"></td></tr>';
			}
		} else {
			$s .= '<tr><td colspan="8" align="center"><font color="red"><b>没有记录</b></font></td></tr>';
		}
		$s .= '</table>';
		return $s;
	}

	public function get_payment_media_deposit_apply_user_input_html() {
		$id = Security_Util::my_get('id');
		$row = $this->db
				->get_row(
						'SELECT a.userid,b.payment_date,b.id,b.payment_apply_deadline,c.media_name FROM finance_payment_media_deposit_apply_user a LEFT JOIN finance_payment_media_deposit_apply b ON a.payment_media_apply_id=b.id LEFT JOIN finance_payment_media_info c ON b.media_info_id=c.id WHERE a.id='
								. intval($id) . ' AND a.userid='
								. $this->getUid());
		if ($row !== NULL) {
			$buf = file_get_contents(
					TEMPLATE_PATH
							. 'finance/payment/payment_media_deposit_apply_user_input.tpl');
			return str_replace(
					array('[LEFT]', '[TOP]', '[MEDIANAME]', '[PAYMENTDATE]',
							'[PAYMENTDEADLINE]', '[ITEMLIST]', '[VCODE]',
							'[ID]', '[ASSIGNID]', '[BASE_URL]'),
					array($this->get_left_html(), $this->get_top_html(),
							$row->media_name, $row->payment_date,
							$row->payment_apply_deadline,
							$this->_get_statement_show($row->id),
							$this->get_vcode(), $row->id, intval($id),
							BASE_URL), $buf);
		} else {
			return User::no_permission();
		}
	}

	private function validate_form_value($action) {
		$errors = array();
		if (in_array($action, array('add'), TRUE)) {
			if (!self::validate_id($this->id)) {
				$errors[] = '媒体付款记录有误';
			}

			if (!self::validate_id($this->assignid)) {
				$errors[] = '条目分配有误';
			}

			$itemids = $this->itemids;
			foreach ($itemids as $itemid) {
				if (!self::validate_id($itemid)) {
					$errors[] = '对账单条目选择有误';
					break;
				}
			}

			$payment_list = $this->payment_list;
			if (!empty($payment_list)) {
				foreach ($payment_list as $key => $paylist) {
					//申请金额付款类型
					if (!in_array(intval($paylist['payment_type']),
							array(1, 2), TRUE)) {
						$errors[] = '第' . ($key + 1) . '行合同数据【申请金额付款类型】选择有误';
					} else {
						if (intval($paylist['payment_type']) === 2
								&& !self::validate_money(
										$paylist['payment_amount'])) {
							$errors[] = '第' . ($key + 1)
									. '行合同数据【申请付款金额】非有效金额值';
						}
					}

					//个人借款抵扣
					if (self::validate_field_not_empty(
							$paylist['person_loan_user'])
							&& !self::validate_field_max_length(
									$paylist['person_loan_user'], 500)) {
						$errors[] = '第' . ($key + 1)
								. '行合同数据【个人借款抵扣】还款人最多500个字符';
					}

					if (self::validate_field_not_empty(
							$paylist['person_loan_amount'])
							&& !self::validate_money(
									$paylist['person_loan_amount'])) {
						$errors[] = '第' . ($key + 1) . '行合同数据【个人借款抵扣】金额非有效金额值';
					}

					//是否垫付
					if (self::validate_field_not_empty(
							$paylist['is_nim_pay_first'])
							&& intval($paylist['is_nim_pay_first']) !== 1) {
						$errors[] = '第' . ($key + 1) . '行合同数据【是否垫付】选择有误';
					}

					if (self::validate_field_not_empty(
							$paylist['nim_pay_first_amount'])
							&& !self::validate_money(
									$paylist['nim_pay_first_amount'])) {
						$errors[] = '第' . ($key + 1) . '行合同数据【是否垫付】金额非有效金额值';
					}

					if (self::validate_field_not_empty(
							$paylist['nim_pay_first_dids'])
							&& !self::validate_field_max_length(
									$paylist['nim_pay_first_dids'], 1000)) {
						$errors[] = '第' . ($key + 1) . '行合同数据【是否垫付】附件过多';
					}

				}
			} else {
				$errors[] = '所选合同数据不能为空';
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

	//列表及垫付
	private function _do_list_and_nimpayfirst($apply_id, $payment_id) {
		$paylists = $this->payment_list;
		$success = TRUE;
		$error = '';
		foreach ($paylists as $paylist) {
			$insert_result = $this->db
					->query(
							'INSERT INTO finance_payment_media_deposit_apply_list(apply_id,cid,payment_amount,payment_type,person_loan_user,person_loan_amount,is_nim_pay_first,nim_pay_first_amount,nim_pay_first_dids,isok) VALUE('
									. $apply_id . ',"' . $paylist['cid'] . '",'
									. (empty($paylist['payment_amount']) ? 0
											: $paylist['payment_amount']) . ','
									. $paylist['payment_type'] . ',"'
									. $paylist['person_loan_user'] . '",'
									. (empty($paylist['person_loan_amount']) ? 0
											: $paylist['person_loan_amount'])
									. ','
									. (intval($paylist['is_nim_pay_first'])
											=== 1 ? 1 : 0) . ','
									. (empty($paylist['nim_pay_first_amount'])
											|| intval(
													$paylist['is_nim_pay_first'])
													=== 0 ? 0
											: $paylist['nim_pay_first_amount'])
									. ',"' . $paylist['nim_pay_first_dids']
									. '",0)');
			if ($insert_result === FALSE) {
				$success = FALSE;
				$error = '记录合同信息有误';
				break;
			} else {
				$list_id = $this->db->insert_id;

				if (intval($paylist['is_nim_pay_first']) === 1
						&& !empty($paylist['nim_pay_first_amount'])) {
					$nim_pay_first_result = $this->db
							->query(
									'INSERT INTO finance_payment_payfirst(apply_id,list_id,payment_type,payfirst_amount,amount_type,status,addtime) VALUE('
											. $apply_id . ',' . $list_id
											. ',2,'
											. $paylist['nim_pay_first_amount']
											. ',2,1,now())');
					if ($nim_pay_first_result === FALSE) {
						$success = FALSE;
						$error = '记录垫付信息有误';
						break;
					}
				}

				if ($success) {
					$sbusql = array();
					foreach ($this->itemids as $val) {
						$sbusql[] = '(' . $this->id . ',"' . $payment_id . '",'
								. $val . ',' . $list_id . ',' . $this->getUid()
								. ',1)';
					}
					$insert_result = $this->db
							->query(
									'INSERT INTO finance_payment_media_deposit_apply_items_users(payment_media_apply_id,payment_id,item_id,list_id,user_id,isok) VALUES'
											. implode(',', $sbusql));
					if ($insert_result === FALSE) {
						$success = FALSE;
						$error = '记录分配信息有误';
						break;
					}
				}
			}
		}
		return array('status' => $success ? 'success' : 'error',
				'message' => $success ? '成功' : $error);
	}

	//监测是否用户都完成了
	private function _are_all_user_done($apply_id) {
		$is_done = FALSE;
		//判断条目是否都已经被分配过
		$row = $this->db
				->get_row(
						'SELECT a.plancount,b.realcount FROM
(
SELECT COUNT(*) AS plancount,apply_id FROM finance_payment_media_deposit_apply_items WHERE apply_id='
								. intval($apply_id)
								. ' AND isok=1
) a
LEFT JOIN 
(
SELECT COUNT(DISTINCT(item_id)) AS realcount,payment_media_apply_id FROM finance_payment_media_deposit_apply_items_users WHERE payment_media_apply_id='
								. intval($apply_id)
								. ' AND isok=1
) b
ON a.apply_id=b.payment_media_apply_id FOR UPDATE');
		if ($row !== NULL) {
			if (intval($row->plancount) === intval($row->realcount)) {
				//判断金额是否>=应付金额
				$amount = $this->db
						->get_row(
								'SELECT a.planamount,b.realamount FROM
(
SELECT payment_amount_plan AS planamount,id FROM finance_payment_media_deposit_apply WHERE id='
										. intval($apply_id)
										. ' AND isok<>-1
) a
LEFT JOIN
(
SELECT SUM(payment_amount) AS realamount,apply_id FROM finance_payment_media_deposit_apply_list WHERE apply_id='
										. intval($apply_id)
										. ' AND isok<>-1
) b
ON a.id=b.apply_id FOR UPDATE');

				if ($amount !== NULL) {
					if (doubleval($amount->planamount)
							<= doubleval($amount->realamount)) {
						$is_done = TRUE;
					}
				}
			}
		}

		return $is_done;
	}

	private function _log($payment_id, $auditname, $type, $content = '') {
		$insert_result = $this->db
				->query(
						'INSERT INTO finance_payment_media_deposit_apply_log(payment_id,auditname,time,uid,content,type) VALUE("'
								. $payment_id . '","' . $auditname . '",'
								. time() . ',' . $this->getUid() . ',"'
								. $content . '","' . $type . '")');
		return $insert_result !== FALSE;
	}

	public function getUserInputResult() {
		if ($this->validate_form_value('add')) {
			$success = TRUE;
			$error = '';
			$this->db->query('BEGIN');

			//检查付款申请是否存在
			$row = $this->db
					->get_row(
							'SELECT payment_id FROM finance_payment_media_deposit_apply WHERE id='
									. intval($this->id) . ' FOR UPDATE');
			if ($row !== NULL) {
				$other = $this
						->_do_list_and_nimpayfirst(intval($this->id),
								$row->payment_id);
				if ($other['status'] === 'error') {
					$success = FALSE;
					$error = $other['message'];
				}
			} else {
				$success = FALSE;
				$error = '该付款申请不存在';
			}

			if ($success) {
				if ($this->_are_all_user_done(intval($this->id))) {
					//更新付款申请step
					$result = $this->db
							->query(
									'UPDATE finance_payment_media_deposit_apply SET step=step+1 WHERE id='
											. intval($this->id));
					if ($result === FALSE) {
						$success = FALSE;
						$error = '填写失败，错误代码3';
					}
				}
			}

			if ($success) {
				//记录日志
				$result = $this
						->_log($row->payment_id, '被分配员工',
								'<font color=\'#99cc00\'>分配合同</font>');
				if ($result === FALSE) {
					$success = FALSE;
					$error = '分配合同填写记录日志失败';
				}
			}

			if ($success) {
				$this->db->query('COMMIT');
			} else {
				$this->db->query('ROLLBACK');
			}
			return array('status' => $success ? 'success' : 'error',
					'message' => $success ? '填写成功' : $error);
		}
		return array('status' => 'error', 'message' => $this->errors);
	}
	
	public function getMyMediaDepositAssignedHtml(){
	//自己是否有权限
		$row = $this->db
				->get_row(
						'SELECT id FROM finance_payment_media_deposit_apply_user WHERE payment_media_apply_id='
								. intval($this->id) . ' AND userid='
								. $this->getUid() . ' AND isok=1');
		if ($row !== NULL) {
			$buf = file_get_contents(
					TEMPLATE_PATH
							. 'finance/payment/payment_media_deposit_apply_my_assigned.tpl');
			return str_replace(
					array('[LEFT]', '[TOP]', '[VCODE]', '[ID]', '[ASSIGNID]',
							'[UID]', '[BASE_URL]'),
					array($this->get_left_html(), $this->get_top_html(),
							$this->get_vcode(), intval($this->id), $row->id,
							$this->getUid(), BASE_URL), $buf);
		} else {
			return User::no_permission();
		}
	}
}
