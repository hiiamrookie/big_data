<?php
class Payment_Media_Apply extends User {
	private $media_name;
	private $bank_name_select;
	private $bank_name;
	private $bank_account_select;
	private $bank_account;
	private $payment_amount_plan;
	private $payment_date;
	private $is_nim_pay_first;
	private $is_rebate_deduction;
	private $rebate_amount;
	private $rebate_dids;
	private $is_deposit_deduction;
	private $deposit_dids;
	private $is_person_loan_deduction;
	private $person_loan_amount;
	private $remark;
	private $payment_apply_deadline;
	private $statement;
	private $users;
	private $pcid;
	private $has_payment_media_apply = FALSE;

	private $process_id;
	private $process_deps;
	private $process_step;

	private $errors = array();

	private $page;
	private $all_count;
	private $page_count;
	const LIMIT = 50;

	private $id;
	private $statement_del;

	private $audit_result;
	private $audit_content;
	private $reject_step;

	private $gdpaymentdate;
	private $gdpaymentamount;
	private $gdpaymenttype;
	private $gdpaymentbank;
	private $payment_id;

	private $listid;
	private $auditsel;
	private $auditresaon;
	private $auditvalue;
	private $uid;

	public function __construct($fields = array()) {
		parent::__construct();
		if (!empty($fields)) {
			foreach ($this as $key => $value) {
				if ($fields[$key] !== NULL
						&& !in_array($key, array('has_payment_media_apply'),
								TRUE)) {
					$this->$key = $fields[$key];
				}
			}
		}
		//获得媒体付款申请流程相关信息
		$process = Process::getInstance();
		$finance_processes = $process['module'][7];
		//根据流程名找到流程
		$process_id = 0;
		$deps = array();
		foreach ($finance_processes as $finance_process) {
			if ($finance_process['name'] === '媒体付款申请流程') {
				$process_id = intval($finance_process['id']);
				$deps = explode('^', $finance_process['deps']);
				break;
			}
		}
		if ($process_id > 0) {
			$step_process = $process['step'][$process_id];
		}
		$this->process_id = $process_id;
		$this->process_deps = $deps;
		$this->process_step = $step_process;

		if (in_array($this->getUsername(),
				$GLOBALS['manager_finance_permission'])
				|| intval($this->getBelong_dep()) === 2) {
			$this->has_payment_media_apply = TRUE;
		}
	}

	private function _get_permission_by_stepname($stepname) {
		$process_step = $this->process_step;
		if ($process_step !== NULL) {
			foreach ($process_step as $content) {
				$content = $content['content'];
				if ($content[0] === $stepname) {
					return $content[2];
				}
			}
		}
		return NULL;
	}

	private function _get_process_list($dep = NULL, $pcid = NULL) {
		$result = '';
		if ($this->process_id > 0) {
			//找到流程，获取该流程步骤
			$step_process = $this->process_step;

			if ($this->process_step !== NULL) {
				$i = 0;
				$use_dep = $dep === NULL ? $this->getBelong_dep() : $dep;
				if (in_array($use_dep, $this->process_deps, TRUE)) {
					$content = '';
					foreach ($step_process as $key => $t) {
						$content .= ($key !== 0 ? ' -> ' : '')
								. $t['content'][0];
					}

					$result .= '<li><input type="radio" name="process" value="'
							. $this->process_id . '" class="checkbox" '
							. ($i === 0 && $pcid === NULL
									|| $pcid !== NULL
											&& intval($pcid)
													=== $this->process_id ? 'checked="checked"'
									: '') . '><span style="display:none">'
							. $content . '</span><label>媒体付款申请流程</label></li>';
					$i++;
				}
			}
		}
		return $result;
	}

	private function _get_process_content($isok, $step) {

		$list = array();
		$step_process = $this->process_step;

		if ($step_process !== NULL) {
			if (intval($isok) === 1) {
				foreach ($step_process as $sp) {
					$list[] = '<font color="green">' . $sp['content'][0]
							. '</font>';
				}
			} else {
				foreach ($step_process as $key => $sp) {
					$list[] = '<font color="'
							. ($key <= intval($step) ? 'green' : 'red') . '">'
							. $sp['content'][0] . '</font>';
				}
			}
		}

		return '<b>' . implode(' -> ', $list) . '</b>';
	}

	public function get_payment_media_apply_html() {
		$start_permission = $this->_get_permission_by_stepname('发起人');
		if (in_array($start_permission, $this->getPermissions(), TRUE)) {
			$buf = file_get_contents(
					TEMPLATE_PATH . 'finance/payment/payment_media_apply.tpl');
			return str_replace(
					array('[LEFT]', '[TOP]', '[MEDIANAMESELECT]',
							'[VALIDATE_TYPE]', '[VALIDATE_EXCEL_TYPE]',
							'[VALIDATE_SIZE]', '[PROCESSLIST]', '[VCODE]',
							'[BASE_URL]'),
					array($this->get_left_html(), $this->get_top_html(),
							Payment_Media_Info::get_media_list(),
							implode(',',
									$GLOBALS['defined_upload_validate_type']),
							implode(',',
									$GLOBALS['defined_upload_execel_validate_type']),
							UPLOAD_FILE_MAX_SIZE / (1024 * 1024),
							$this->_get_process_list(), $this->get_vcode(),
							BASE_URL), $buf);
		}
		return User::no_permission();
	}

	private static function _get_is_checked($value) {
		return intval($value) === 1 ? 'checked' : '';
	}

	private function _log($payment_id, $auditname, $type, $content = '') {
		$insert_result = $this->db
				->query(
						'INSERT INTO finance_payment_media_apply_log(payment_id,auditname,time,uid,content,type) VALUE("'
								. $payment_id . '","' . $auditname . '",'
								. time() . ',' . $this->getUid() . ',"'
								. $content . '","' . $type . '")');
		return $insert_result !== FALSE;
	}

	public function get_payment_media_manager_html() {
		$start_permission = $this->_get_permission_by_stepname('员工填写');
		if (!in_array($start_permission, $this->getPermissions(), TRUE)) {
			$buf = file_get_contents(
					TEMPLATE_PATH
							. 'finance/payment/payment_media_manager_list.tpl');
			return str_replace(
					array('[LEFT]', '[TOP]', ' [PAYMENTMEDIALIST]',
							'[ALLCOUNTS]', '[COUNTS]', '[NEXT]', '[PREV]',
							'[VCODE]', '[BASE_URL]'),
					array($this->get_left_html(), $this->get_top_html(),
							$this->_get_media_payment_list_html(FALSE, TRUE),
							$this->all_count, $this->_get_apply_counts(),
							$this->_getNext('media_manager'),
							$this->_getPrev('media_manager'),
							$this->get_vcode(), BASE_URL), $buf);
		}
		return User::no_permission();
	}

	private function _get_payment_media_items_assign($apply_id) {
		$itemssum = 0;
		$itemhasbelong = 0;

		$results = $this->db
				->get_results(
						'SELECT a.id,b.item_id 
FROM finance_payment_media_apply_items a
LEFT JOIN
(
SELECT DISTINCT(item_id) FROM finance_payment_media_apply_items_users WHERE payment_media_apply_id='
								. intval($apply_id)
								. ' AND isok=1
) b
ON a.id=b.item_id
WHERE a.payment_media_apply_id=' . intval($apply_id) . ' AND a.isok=1');

		if ($results !== NULL) {
			$itemssum = count($results);
			foreach ($results as $result) {
				if (intval($result->item_id) > 0) {
					$itemhasbelong += 1;
				}
			}
		}
		$itemnobelong = $itemssum - $itemhasbelong;
		return array('itemssum' => $itemssum,
				'itemhasbelong' => $itemhasbelong,
				'itemnobelong' => $itemnobelong);
	}

	public function get_payment_media_audit_user_assigned_html() {
		$id = Security_Util::my_get('id');
		$uid = Security_Util::my_get('uid');
		$row = $this->db
				->get_row(
						'SELECT a.*,b.media_name,b.account_bank,b.account FROM finance_payment_media_apply a LEFT JOIN finance_payment_media_info b ON a.media_info_id=b.id WHERE a.id='
								. intval($id));

		if ($row !== NULL) {
			//查看是否到流程
			$get_permission = FALSE;
			$is_first_audit = FALSE;
			$next_step = $this->process_step[intval($row->step) + 1]['content'][0];
			foreach ($this->process_step as $stepkey => $value) {
				if ((intval($row->step) + 1) === $stepkey
						&& in_array($value['content'][2],
								$this->getPermissions(), TRUE) || $next_step === '员工填写') {
					$get_permission = TRUE;
					if (intval($row->step) === 0) {
						$is_first_audit = TRUE;
					}
				}
			}

			if ($get_permission) {
				$buf = file_get_contents(
						TEMPLATE_PATH
								. 'finance/payment/payment_media_other_audit.tpl');
				return str_replace(
						array('[LEFT]', '[TOP]', '[VCODE]', '[ID]', '[UID]',
								'[PAYMENTAMOUNTPLAN]', '[PAYMENTDATE]',
								'[REBATEAMOUNT]', '[PAYMENTAPPLYDEADLINE]',
								'[PAYMENTAMOUNTREAL]', '[MEDIANAME]',
								'[ADDTIME]', '[BASE_URL]'),
						array($this->get_left_html(), $this->get_top_html(),
								$this->get_vcode(), intval($id), intval($uid),
								$row->payment_amount_plan, $row->payment_date,
								$row->rebate_amount,
								$row->payment_apply_deadline,
								$row->payment_amount_real, $row->media_name,
								$row->addtime, BASE_URL), $buf);
			} else {
				return User::no_permission();
			}
		} else {
			return User::no_object('没有该媒体付款申请');
		}
	}

	public function get_payment_media_audit_html() {
		$id = Security_Util::my_get('id');
		$row = $this->db
				->get_row(
						'SELECT a.*,b.media_name,b.account_bank,b.account FROM finance_payment_media_apply a LEFT JOIN finance_payment_media_info b ON a.media_info_id=b.id WHERE a.id='
								. intval($id));

		if ($row !== NULL) {
			//查看是否到流程
			$get_permission = FALSE;
			$is_first_audit = FALSE;
			$next_step = $this->process_step[intval($row->step) + 1]['content'][0];
			//var_dump($next_step);
			foreach ($this->process_step as $stepkey => $value) {
				if ((intval($row->step) + 1) === $stepkey
						&& in_array($value['content'][2],
								$this->getPermissions(), TRUE) || $next_step === '员工填写') {
					$get_permission = TRUE;
					if (intval($row->step) === 0) {
						$is_first_audit = TRUE;
					}
				}
			}

			if ($get_permission) {

				$results = $this->db
						->get_results(
								'SELECT a.userid,b.username,b.realname FROM finance_payment_media_apply_user a LEFT JOIN users b ON a.userid=b.uid WHERE a.payment_media_apply_id='
										. intval($id) . ' AND a.isok=1');
				$uid_array = array();
				$user_show = array();
				$js_uid_array = json_encode(array());
				$js_user_show_array = json_encode(array());
				if ($results !== NULL) {
					foreach ($results as $result) {
						$uid_array[] = $result->userid;
						$user_show[] = $result->realname . ' ('
								. $result->username . ')<img src="' . BASE_URL
								. 'images/close.png" onclick="user_del('
								. $result->userid . ')"/>';
					}
				}
				if (empty($uid_array)) {
					$uid_array = ',';
				} else {
					$js_uid_array = json_encode($uid_array);
					$uid_array = ',' . implode(',', $uid_array) . ',';
				}
				if (empty($user_show)) {
					$user_show = '';
				} else {
					foreach ($user_show as $us) {
						$tmp[] = urlencode(addslashes($us));
					}
					$js_user_show_array = urldecode(json_encode($tmp));
					$user_show = implode(',', $user_show);
				}

				if ($is_first_audit) {
					$buf = file_get_contents(
							TEMPLATE_PATH
									. 'finance/payment/payment_media_first_audit.tpl');
					$search = array('[LEFT]', '[TOP]', '[MEDIANAME]',
							'[VALIDATE_TYPE]', '[VALIDATE_EXCEL_TYPE]',
							'[VALIDATE_SIZE]', '[PROCESSLIST]', '[BANKLIST]',
							'[ACCOUNTLIST]', '[PAYMENTAMOUNTPLAN]',
							'[PAYMENTDATE]', '[REBATEAMOUNT]',
							'[PERSONLOANAMOUNT]', '[REMARK]',
							'[PAYMENTAPPLYDEADLINE]', '[ISNIMPAYFIRST]',
							'[ISREBATEDEDUCTION]', '[ISDEPOSITDEDUCTION]',
							'[ISPERSONLOANDEDUCTION]', '[REBATEDIDSVALUE]',
							'[REBATEDIDS]', '[PAYMENTAMOUNTREAL]',
							'[DEPOSITDIDSVALUE]', '[DEPOSITDIDS]',
							'[STATEMENTDIDSVALUE]', '[STATEMENTDIDS]',
							'[USERS]', '[USERLIST]', ' [JSUSERARRAY]',
							'[JSUSERSHOWARRAY]', '[ID]', '[STATESHOW]',
							'[VCODE]', '[BASE_URL]');
					$replace = array($this->get_left_html(),
							$this->get_top_html(), $row->media_name,
							implode(',',
									$GLOBALS['defined_upload_validate_type']),
							implode(',',
									$GLOBALS['defined_upload_execel_validate_type']),
							UPLOAD_FILE_MAX_SIZE / (1024 * 1024),
							$this
									->_get_process_content($row->isok,
											$row->step),
							Payment_Media_Info::get_bank_list(
									$row->media_name, $row->account_bank),
							Payment_Media_Info::get_bank_acount_list(
									$row->media_name, $row->account_bank,
									$row->account), $row->payment_amount_plan,
							$row->payment_date, $row->rebate_amount,
							$row->person_loan_amount, $row->remark,
							$row->payment_apply_deadline,
							self::_get_is_checked($row->is_nim_pay_first),
							self::_get_is_checked($row->is_rebate_deduction),
							self::_get_is_checked($row->is_deposit_deduction),
							self::_get_is_checked(
									$row->is_person_loan_deduction),
							$row->rebate_dids,
							$this
									->get_upload_files($row->rebate_dids, TRUE,
											'rebate_dids'),
							$row->payment_amount_real, $row->deposit_dids,
							$this
									->get_upload_files($row->deposit_dids,
											TRUE, 'deposit_dids'),
							$row->statement,
							'<div did="' . $row->statement . '" id="'
									. $row->statement . '_d">'
									. $this
											->get_upload_files(
													$row->statement, FALSE,
													'statement') . '</div>',
							$uid_array, $user_show, $js_uid_array,
							$js_user_show_array, intval($id),
							$this->_get_statement_show($id),
							$this->get_vcode(), BASE_URL);
				} else {
					$item_assign = $this
							->_get_payment_media_items_assign(intval($id));
					$buf = file_get_contents(
							TEMPLATE_PATH
									. 'finance/payment/payment_media_other_audit_list.tpl');
					$search = array('[LEFT]', '[TOP]', '[PAYMENTAMOUNTPLAN]',
							'[PAYMENTDATE]', '[REBATEAMOUNT]',
							'[PAYMENTAPPLYDEADLINE]', '[PAYMENTAMOUNTREAL]',
							'[MEDIANAME]', '[ADDTIME]', '[ITEMSSUM]',
							'[ITEMHASBELONGER]', '[ITEMNOBELONG]', '[VCODE]',
							'[ID]', '[BASE_URL]');
					$replace = array($this->get_left_html(),
							$this->get_top_html(), $row->payment_amount_plan,
							$row->payment_date, $row->rebate_amount,
							$row->payment_apply_deadline,
							$row->payment_amount_real, $row->media_name,
							$row->addtime, $item_assign['itemssum'],
							$item_assign['itemhasbelong'],
							$item_assign['itemnobelong'], $this->get_vcode(),
							intval($id), BASE_URL);
				}
				return str_replace($search, $replace, $buf);
				/*
				return str_replace(
				        array('[LEFT]', '[TOP]', '[MEDIANAMESELECT]',
				                '[VALIDATE_TYPE]', '[VALIDATE_EXCEL_TYPE]',
				                '[VALIDATE_SIZE]', '[PROCESSLIST]',
				                '[BANKLIST]', '[ACCOUNTLIST]',
				                '[PAYMENTAMOUNTPLAN]', '[PAYMENTDATE]',
				                '[REBATEAMOUNT]', '[PERSONLOANAMOUNT]',
				                '[REMARK]', '[PAYMENTAPPLYDEADLINE]',
				                '[ISNIMPAYFIRST]', '[ISREBATEDEDUCTION]',
				                '[ISDEPOSITDEDUCTION]',
				                '[ISPERSONLOANDEDUCTION]', '[REBATEDIDSVALUE]',
				                '[REBATEDIDS]', '[PAYMENTAMOUNTREAL]',
				                '[DEPOSITDIDSVALUE]', '[DEPOSITDIDS]',
				                '[STATEMENTDIDSVALUE]', '[STATEMENTDIDS]',
				                '[USERS]', '[USERLIST]', ' [JSUSERARRAY]',
				                '[JSUSERSHOWARRAY]', '[ID]', '[STATESHOW]',
				                '[MEDIANAME]', '[ADDTIME]', '[VCODE]',
				                '[BASE_URL]'),
				        array($this->get_left_html(), $this->get_top_html(),
				                Payment_Media_Info::get_media_list(
				                        $row->media_name),
				                implode(',',
				                        $GLOBALS['defined_upload_validate_type']),
				                implode(',',
				                        $GLOBALS['defined_upload_execel_validate_type']),
				                UPLOAD_FILE_MAX_SIZE / (1024 * 1024),
				                $this
				                        ->_get_process_content($row->isok,
				                                $row->step),
				                Payment_Media_Info::get_bank_list(
				                        $row->media_name, $row->account_bank),
				                Payment_Media_Info::get_bank_acount_list(
				                        $row->media_name, $row->account_bank,
				                        $row->account),
				                $row->payment_amount_plan, $row->payment_date,
				                $row->rebate_amount, $row->person_loan_amount,
				                $row->remark, $row->payment_apply_deadline,
				                self::_get_is_checked($row->is_nim_pay_first),
				                self::_get_is_checked($row->is_rebate_deduction),
				                self::_get_is_checked(
				                        $row->is_deposit_deduction),
				                self::_get_is_checked(
				                        $row->is_person_loan_deduction),
				                $row->rebate_dids,
				                $this
				                        ->get_upload_files($row->rebate_dids,
				                                TRUE, 'rebate_dids'),
				                $row->payment_amount_real, $row->deposit_dids,
				                $this
				                        ->get_upload_files($row->deposit_dids,
				                                TRUE, 'deposit_dids'),
				                $row->statement,
				                '<div did="' . $row->statement . '" id="'
				                        . $row->statement . '_d">'
				                        . $this
				                                ->get_upload_files(
				                                        $row->statement, FALSE,
				                                        'statement') . '</div>',
				                $uid_array, $user_show, $js_uid_array,
				                $js_user_show_array, intval($id),
				                $this->_get_statement_show($id),
				                $row->media_name, $row->addtime,
				                $this->get_vcode(), BASE_URL), $buf);
				 */

			} else {
				return User::no_permission();
			}

		} else {
			return User::no_object('没有该媒体付款申请');
		}
	}

	public function get_payment_media_edit_html() {
		$start_permission = $this->_get_permission_by_stepname('发起人');
		if (in_array($start_permission, $this->getPermissions(), TRUE)) {
			$id = Security_Util::my_get('id');
			$row = $this->db
					->get_row(
							'SELECT a.*,b.media_name,b.account_bank,b.account FROM finance_payment_media_apply a LEFT JOIN finance_payment_media_info b ON a.media_info_id=b.id WHERE a.id='
									. intval($id));
			if ($row !== NULL) {
				if (intval($row->user) === intval($this->getUid())) {
					//获得分配人
					$results = $this->db
							->get_results(
									'SELECT a.userid,b.username,b.realname FROM finance_payment_media_apply_user a LEFT JOIN users b ON a.userid=b.uid WHERE a.payment_media_apply_id='
											. intval($id) . ' AND a.isok=1');
					$uid_array = array();
					$user_show = array();
					$js_uid_array = json_encode(array());
					$js_user_show_array = json_encode(array());
					if ($results !== NULL) {
						foreach ($results as $result) {
							$uid_array[] = $result->userid;
							$user_show[] = $result->realname . ' ('
									. $result->username . ')<img src="'
									. BASE_URL
									. 'images/close.png" onclick="user_del('
									. $result->userid . ')"/>';
						}
					}
					if (empty($uid_array)) {
						$uid_array = ',';
					} else {
						$js_uid_array = json_encode($uid_array);
						$uid_array = ',' . implode(',', $uid_array) . ',';
					}
					if (empty($user_show)) {
						$user_show = '';
					} else {
						foreach ($user_show as $us) {
							$tmp[] = urlencode(addslashes($us));
						}
						$js_user_show_array = urldecode(json_encode($tmp));
						$user_show = implode(',', $user_show);
					}
					$buf = file_get_contents(
							TEMPLATE_PATH
									. 'finance/payment/payment_media_edit.tpl');
					return str_replace(
							array('[LEFT]', '[TOP]', '[MEDIANAME]',
									'[VALIDATE_TYPE]', '[VALIDATE_EXCEL_TYPE]',
									'[VALIDATE_SIZE]', '[PROCESSLIST]',
									'[BANKLIST]', '[ACCOUNTLIST]',
									'[PAYMENTAMOUNTPLAN]', '[PAYMENTDATE]',
									'[REBATEAMOUNT]', '[PERSONLOANAMOUNT]',
									'[REMARK]', '[PAYMENTAPPLYDEADLINE]',
									'[ISNIMPAYFIRST]', '[ISREBATEDEDUCTION]',
									'[ISDEPOSITDEDUCTION]',
									'[ISPERSONLOANDEDUCTION]',
									'[REBATEDIDSVALUE]', '[REBATEDIDS]',
									'[PAYMENTAMOUNTREAL]',
									'[DEPOSITDIDSVALUE]', '[DEPOSITDIDS]',
									'[STATEMENTDIDSVALUE]', '[STATEMENTDIDS]',
									'[USERS]', '[USERLIST]', ' [JSUSERARRAY]',
									'[JSUSERSHOWARRAY]', '[ID]', '[STATESHOW]',
									'[VCODE]', '[BASE_URL]'),
							array($this->get_left_html(),
									$this->get_top_html(), $row->media_name,
									implode(',',
											$GLOBALS['defined_upload_validate_type']),
									implode(',',
											$GLOBALS['defined_upload_execel_validate_type']),
									UPLOAD_FILE_MAX_SIZE / (1024 * 1024),
									$this
											->_get_process_content($row->isok,
													$row->step),
									Payment_Media_Info::get_bank_list(
											$row->media_name,
											$row->account_bank),
									Payment_Media_Info::get_bank_acount_list(
											$row->media_name,
											$row->account_bank, $row->account),
									$row->payment_amount_plan,
									$row->payment_date, $row->rebate_amount,
									$row->person_loan_amount, $row->remark,
									$row->payment_apply_deadline,
									self::_get_is_checked(
											$row->is_nim_pay_first),
									self::_get_is_checked(
											$row->is_rebate_deduction),
									self::_get_is_checked(
											$row->is_deposit_deduction),
									self::_get_is_checked(
											$row->is_person_loan_deduction),
									$row->rebate_dids,
									$this
											->get_upload_files(
													$row->rebate_dids, TRUE,
													'rebate_dids'),
									$row->payment_amount_real,
									$row->deposit_dids,
									$this
											->get_upload_files(
													$row->deposit_dids, TRUE,
													'deposit_dids'),
									$row->statement,
									'<div did="' . $row->statement . '" id="'
											. $row->statement . '_d">'
											. $this
													->get_upload_files(
															$row->statement,
															FALSE, 'statement')
											. '</div>', $uid_array, $user_show,
									$js_uid_array, $js_user_show_array,
									intval($id),
									$this->_get_statement_show($id),
									$this->get_vcode(), BASE_URL), $buf);
				} else {
					return User::no_permission('不是发起人，无法修改');
				}
			} else {
				return User::no_object('没有该媒体付款申请');
			}
		}
		return User::no_permission();
	}

	private function _get_statement_show($apply_id) {
		$s = '<table width="100%">';
		$s .= '<tr><td>广告主</td><td>产品</td><td>合同付款额</td><td>充值日期</td><td>媒体合同号</td><td>操作</td></tr>';
		$results = $this->db
				->get_results(
						'SELECT id,ggz,cp,htfke,czrq,hth FROM finance_payment_media_apply_items WHERE payment_media_apply_id='
								. intval($apply_id) . ' AND isok=1');
		if ($results !== NULL) {
			foreach ($results as $result) {
				$s .= '<tr id="statetr_' . $result->id . '"><td>'
						. $result->ggz . '</td><td>' . $result->cp
						. '</td><td>' . $result->htfke . '</td><td>'
						. $result->czrq . '</td><td>' . $result->hth
						. '</td><td><a href="javascript:delete_statement('
						. $result->id . ');">删除</a></td></tr>';
			}
		} else {
			$s .= '<tr><td colspan="10" align="center"><font color="red"><b>没有记录</b></font></td></tr>';
		}
		$s .= '</table>';
		return $s;
	}

	private function validate_form_value($action) {
		$errors = array();
		if (in_array($action,
				array('apply', 'edit', 'audit_reject', 'audit_pass',
						'audit_item', 'audit_fullpayment_media'), TRUE)) {
			$validate_form = FALSE;
			if ($action === 'audit_fullpayment_media') {
				//id
				if (!self::validate_id(intval($this->id))) {
					$errors[] = '所选付款申请有误';
				}

				//auditvalue
				if (!in_array($this->auditvalue, array('pass', 'reject'), TRUE)) {
					$errors[] = '审核结果选择有误';
				}

				//remark
				if (self::validate_field_not_empty($this->remark)
						&& !self::validate_field_max_length($this->remark, 1000)) {
					$errors[] = '审核意见最多1000个字符';
				}
			} else if ($action === 'audit_item') {
				if (!self::validate_id(intval($this->id))) {
					$errors[] = '付款申请选择有误';
				}

				if (!self::validate_id(intval($this->listid))) {
					$errors[] = '付款申请项选择有误';
				}

				if (!in_array(intval($this->auditsel), array(1, 2), TRUE)) {
					$errors[] = '审核结果选择有误';
				}

				if (!empty($this->auditresaon)
						&& !self::validate_field_max_length(
								$this->auditresaon, 1000)) {
					$errors[] = '审核意见最多1000个字符';
				}
			} else if (in_array($action, array('audit_reject', 'audit_pass'),
					TRUE)) {
				//如果是初审，验证字段信息，不然就验证审核信息
				$row = $this->db
						->get_row(
								'SELECT step FROM finance_payment_media_apply WHERE id='
										. intval($this->id));
				if ($row !== NULL) {
					if (intval($row->step) === 0 && $action === 'audit_pass') {
						$validate_form = TRUE;
					}

					if (!in_array(intval($this->audit_result), array(1, 2),
							TRUE)) {
						$errors[] = '审核结果选择有误';
					}

					if (!empty($this->audit_content)
							&& !self::validate_field_max_length(
									$this->audit_content, 1000)) {
						$errors[] = '审核留言最多1000个字符';
					}

					if ($action === 'audit_reject') {
						if (intval($this->reject_step)
								>= count($this->process_step)) {
							$errors[] = '审核驳回有误';
						}
					}

				} else {
					$errors[] = '付款申请选择有误';
				}
			}

			if (in_array($action, array('add', 'edit'), TRUE) || $validate_form) {
				if ($action === 'edit') {
					if (!self::validate_id(intval($this->id))) {
						$errors[] = '付款申请选择有误';
					}
				}

				//媒体名称
				if (empty($this->media_name)) {
					$errors[] = '媒体名称不能为空';
				} else if (!self::validate_field_max_length($this->media_name,
						255)) {
					$errors[] = '媒体名称最多255个字符';
				}

				//开户行
				if (empty($this->bank_name_select) && empty($this->bank_name)) {
					$errors[] = '请选择开户行或者输入一个新的开户行';
				} else if (!empty($this->bank_name)
						&& !self::validate_field_max_length($this->bank_name,
								255)) {
					$errors[] = '开户行最多255个字符';
				}

				//银行帐号
				if (empty($this->bank_account_select)
						&& empty($this->bank_account)) {
					$errors[] = '请选择银行帐号或者输入一个新的银行帐号';
				} else if (!empty($this->bank_account)
						&& !self::validate_field_max_length(
								$this->bank_account, 255)) {
					$errors[] = '银行帐号最多255个字符';
				}

				//应付金额
				if (!self::validate_money($this->payment_amount_plan)) {
					$errors[] = '应付金额不是一个有效的金额值';
				}

				//付款时间
				if (!self::validate_field_not_empty($this->payment_date)
						|| !self::validate_field_not_null($this->payment_date)) {
					$errors[] = '付款时间不能为空';
				} else if (strtotime($this->payment_date) === FALSE) {
					$errors[] = '付款时间不是一个有效的时间值';
				} else if (strtotime($this->payment_date)
						< strtotime(date('Y-m-d', time()))) {
					$errors[] = '付款时间必须晚于等于今天';
				}

				//是否垫付
				if (!in_array(intval($this->is_nim_pay_first), array(0, 1),
						TRUE)) {
					$errors[] = '是否垫付选择有误';
				}

				//返点抵扣
				if (!in_array(intval($this->is_rebate_deduction), array(0, 1),
						TRUE)) {
					$errors[] = '返点抵扣选择有误';
				}

				//返点金额
				if (intval($this->is_rebate_deduction) === 1
						&& !self::validate_money($this->rebate_amount)) {
					$errors[] = '返点抵扣不是有效的金额值';
				}
				//如果没勾选返点抵扣，则返点抵扣不论输入多少都为0 
				if (intval($this->is_rebate_deduction) === 0) {
					$this->rebate_amount = 0;
				}

				//返点附件
				if (!empty($this->rebate_dids)
						&& !self::validate_field_max_length(
								$this->rebate_dids, 1000)) {
					$errors[] = '返点附件过多';
				}

				//保证金抵扣
				if (!in_array(intval($this->is_deposit_deduction), array(0, 1),
						TRUE)) {
					$errors[] = '保证金抵扣选择有误';
				}

				//保证金抵扣附件
				if ((intval($this->is_deposit_deduction) === 1
						&& empty($this->deposit_dids))) {
					$errors[] = '如选择保证金抵扣，则保证金抵扣附件不能为空';
				} else if (!empty($this->deposit_dids)
						&& !self::validate_field_max_length(
								$this->deposit_dids, 1000)) {
					$errors[] = '保证金抵扣附件过多';
				}

				//个人借款抵扣
				if (!in_array(intval($this->is_person_loan_deduction),
						array(0, 1), TRUE)) {
					$errors[] = '个人借款抵扣选择有误';
				}

				//个人借款金额
				if (intval($this->is_person_loan_deduction) === 1
						&& !self::validate_money($this->person_loan_amount)) {
					$errors[] = '个人借款金额不是有效的金额值';
				}

				//如果没勾选个人借款抵扣，则个人借款抵扣不论输入多少都为0 
				if (intval($this->is_person_loan_deduction) === 0) {
					$this->person_loan_amount = 0;
				}

				//备注
				if (!empty($this->remark)
						&& !self::validate_field_max_length($this->remark, 1000)) {
					$errors[] = '备注最多1000个字符';
				}

				//最后提交时间
				if (!self::validate_field_not_empty(
						$this->payment_apply_deadline)
						|| !self::validate_field_not_null(
								$this->payment_apply_deadline)) {
					$errors[] = '最后提交时间不能为空';
				} else if (strtotime($this->payment_apply_deadline) === FALSE) {
					$errors[] = '最后提交时间不是一个有效的时间值';
				} else if (strtotime($this->payment_apply_deadline)
						> strtotime($this->payment_date)) {
					$errors[] = '最后提交时间必须早于付款时间';
				}

				//对账单
				if (!self::validate_id(intval($this->statement))) {
					$errors[] = '对账单上传有误';
				}

				//选择用户
				if (empty($this->users)) {
					$errors[] = '请至少选择一个用户来分配执行单';
				} else {
					$users = explode(',', $this->users);
					$hasrec = FALSE;
					foreach ($users as $user) {
						if ($user !== '') {
							if (!$hasrec) {
								$hasrec = TRUE;
							}
							if (!self::validate_id($user)) {
								$errors[] = '用户选择有误';
								break;
							}
						}
					}
					if (!$hasrec) {
						$errors[] = '用户选择有误';
					}
				}

				//流程ID
				if ($action === 'add') {
					if (!self::validate_id($this->pcid)) {
						$errors[] = '流程选择有误';
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

	/**
	 *获得需付款媒体的媒体ID
	 */
	private function _do_media_info() {
		$success = TRUE;
		//查找媒体信息是否已经存在
		$media_name = $this->media_name;
		$account_bank = !empty($this->bank_name_select) ? $this
						->bank_name_select : $this->bank_name;
		$account = !empty($this->bank_account_select) ? $this
						->bank_account_select : $this->bank_account;
		$media_info_id = 0;
		$row = $this->db
				->get_row(
						'SELECT id FROM finance_payment_media_info WHERE media_name="'
								. $media_name . '" AND account_bank="'
								. $account_bank . '" AND account="' . $account
								. '" AND isok=1 FOR UPDATE');
		if ($row === NULL) {
			//新增
			$insert_result = $this->db
					->query(
							'INSERT INTO finance_payment_media_info(media_name,account_bank,account,isok) VALUE("'
									. $media_name . '","' . $account_bank
									. '","' . $account . '",1)');
			if ($insert_result === FALSE) {
				$success = FALSE;
			} else {
				$media_info_id = $this->db->insert_id;
			}
		} else {
			$media_info_id = intval($row->id);
		}
		return array('status' => $success ? 'success' : 'error',
				'message' => $success ? $media_info_id : '记录媒体信息出错');
	}

	/**
	 * 
	 * 分配用户填写
	 * @param unknown_type $payment_id
	 * @param unknown_type $apply_id
	 * @param unknown_type $delete
	 */
	private function _do_payment_media_apply_user($payment_id, $apply_id = NULL,
			$delete = TRUE) {
		//finance_payment_media_apply_user
		$success = TRUE;
		//删除原来的
		if ($delete) {
			$update_result = $this->db
					->query(
							'UPDATE finance_payment_media_apply_user SET isok=-1,cancel_userid='
									. $this->getUid()
									. ',cancel_datetime=now() WHERE payment_media_apply_id='
									. intval($this->id));
			if ($update_result === FALSE) {
				$success = FALSE;
			}
		}

		if ($success) {
			//添加
			$subsql = array();
			$users = explode(',', $this->users);
			foreach ($users as $userid) {
				if ($userid !== '') {
					$subsql[] = '('
							. ($apply_id === NULL ? intval($this->id)
									: $apply_id) . ', ' . $userid . ',1,"'
							. $payment_id . '",0)';
				}
			}
			if (!empty($subsql)) {
				$insert_result = $this->db
						->query(
								'INSERT INTO finance_payment_media_apply_user(payment_media_apply_id,userid,isok,payment_id,isfinished) VALUES '
										. implode(',', $subsql));
				if ($insert_result === FALSE) {
					$success = FALSE;
				}
			} else {
				$success = FALSE;
			}
		}
		return array('status' => $success ? 'success' : 'error',
				'message' => $success ? '分配用户信息成功' : '分配用户信息出错');
	}

	/**
	 * 
	 * 处理对账单
	 * @param unknown_type $statement
	 * @param unknown_type $payment_id
	 * @param unknown_type $isnew
	 * @param unknown_type $apply_id
	 */
	private function _do_payment_media_statement($statement, $payment_id,
			$isnew, $apply_id = NULL) {
		//finance_payment_media_apply_items
		$success = TRUE;
		$error = '';

		$sum_amount = 0;
		if ($isnew) {
			//上传文件
			$import_result = $this
					->_import_statement($this->statement,
							($apply_id === NULL ? intval($this->id) : $apply_id),
							$payment_id);

			if ($import_result['status'] === 'error') {
				$success = FALSE;
				//$error = implode("\n", $import_result['message']);
				$error = $import_result['message'];
			} else {
				$sum_amount = $import_result['message'];
			}
		} else {

			if (intval($statement) !== intval($this->statement)) {
				//重新上传文件
				$update_result = $this->db
						->query(
								'UPDATE finance_payment_media_apply_items SET isok=-1,cancel_userid='
										. $this->getUid()
										. ',cancel_datetime=now() WHERE payment_media_apply_id='
										. intval($this->id));
				if ($update_result === FALSE) {
					$success = FALSE;
					$error = '媒体付款申请条目更新失败，错误代码1';
				} else {
					$import_result = $this
							->_import_statement($this->statement,
									($apply_id === NULL ? intval($this->id)
											: $apply_id), $payment_id);

					if ($import_result['status'] === 'error') {
						$success = FALSE;
						//$error = implode("\n", $import_result['message']);
						$error = $import_result['message'];
					} else {
						$sum_amount = $import_result['message'];
					}
				}
			} else if (!empty($this->statement_del)) {
				//单独的删除数据项(更新状态)
				$update_result = $this->db
						->query(
								'UPDATE finance_payment_media_apply_items SET isok=-1,cancel_userid='
										. $this->getUid()
										. ',cancel_datetime=now() WHERE id IN ('
										. $this->statement_del . ')');
				if ($update_result === FALSE) {
					$success = FALSE;
					$error = '媒体付款申请条目删除失败，错误代码1';
				} else {
					$sum_amount = $this->db
							->get_var(
									'SELECT SUM(htfke) AS htfke FROM finance_payment_media_apply_items WHERE payment_media_apply_id='
											. intval($this->id) . ' AND isok=1');
				}
			}

		}
		//var_dump($sum_amount);
		return array('status' => $success ? 'success' : 'error',
				'message' => $success ? $sum_amount : $error);
	}

	public function payment_media_edit() {
		if ($this->validate_form_value('edit')) {
			$success = TRUE;
			$error = '';
			$this->db->query('BEGIN');

			//检验是不是有该申请并且是自己新建的
			$pmrow = $this->db
					->get_row(
							'SELECT id,statement,payment_id FROM finance_payment_media_apply WHERE id='
									. intval($this->id) . ' AND user='
									. intval($this->getUid()));
			if ($pmrow !== NULL) {

				//媒体银行信息
				$do_media_info = $this->_do_media_info();
				if ($do_media_info['status'] === 'error') {
					$success = FALSE;
					$error = $do_media_info['message'];
				} else {
					$media_info_id = $do_media_info['message'];
				}

				//finance_payment_media_apply
				if ($success) {
					$payment_amount_plan = $this->payment_amount_plan;
					$rebate_amount = intval($this->is_rebate_deduction) === 1 ? (empty(
									$this->rebate_amount) ? 0
									: $this->rebate_amount) : 0;
					$person_loan_amount = intval(
							$this->is_person_loan_deduction) === 1 ? (empty(
									$this->person_loan_amount) ? 0
									: $this->person_loan_amount) : 0;
					$deposit_amount = 0;

					$real = $payment_amount_plan - $rebate_amount
							- $person_loan_amount - $deposit_amount;

					$update_result = $this->db
							->query(
									'UPDATE finance_payment_media_apply SET media_info_id='
											. $media_info_id
											. ',payment_amount_plan='
											. $payment_amount_plan
											. ',payment_date="'
											. $this->payment_date
											. '",is_nim_pay_first='
											. intval($this->is_nim_pay_first)
											. ',is_rebate_deduction='
											. intval($this->is_rebate_deduction)
											. ',rebate_amount='
											. $rebate_amount . ',rebate_dids="'
											. $this->rebate_dids
											. '",is_deposit_deduction='
											. intval(
													$this->is_deposit_deduction)
											. ',deposit_dids="'
											. $this->deposit_dids
											. '",is_person_loan_deduction='
											. intval(
													$this
															->is_person_loan_deduction)
											. ',person_loan_amount='
											. $person_loan_amount
											. ',payment_amount_real=' . $real
											. ',remark="' . $this->remark
											. '",payment_apply_deadline="'
											. $this->payment_apply_deadline
											. '",statement=' . $this->statement
											. ',isok=0,step=0 WHERE id='
											. intval($this->id));
					if ($update_result === FALSE) {
						$success = FALSE;
						$error = '修改媒体付款申请失败，错误代码1';
					}
				}

				//finance_payment_media_apply_items
				if ($success) {
					$do_statement = $this
							->_do_payment_media_statement($pmrow->statement,
									$pmrow->payment_id, FALSE);
					if ($do_statement['status'] === 'error') {
						$success = FALSE;
						$error = $do_statement['message'];
					} else {
						//更新总付款额
						$result = $this->db
								->query(
										'UPDATE finance_payment_media_apply SET payment_amount_plan='
												. $do_statement['message']
												. ',payment_amount_real='
												. ($do_statement['message']
														- $payment_amount_plan
														+ $real) . ' WHERE id='
												. intval($this->id));
						if ($result === FALSE) {
							$success = FALSE;
							$error = '更新应付款额出错';
						}
					}
				}

				//finance_payment_media_apply_user
				if ($success) {
					$do_apply_user = $this
							->_do_payment_media_apply_user($pmrow->payment_id);
					if ($do_apply_user['status'] === 'error') {
						$success = FALSE;
						$error = $do_apply_user['message'];
					}
				}

				if ($success) {
					//记录日志
					$result = $this
							->_log($pmrow->payment_id, '发起人',
									'<font color=\'#99cc00\'>修改媒体付款申请</font>');
					if ($result === FALSE) {
						$success = FALSE;
						$error = '修改媒体付款申请失败，错误代码7';
					}
				}
			} else {
				$success = FALSE;
				$error = '没有该付款申请或该付款申请非自己创建';
			}

			if ($success) {
				$this->db->query('COMMIT');
			} else {
				$this->db->query('ROLLBACK');
			}
			return array('status' => $success ? 'success' : 'error',
					'message' => $success ? '媒体付款申请修改成功' : $error);
		}

		return array('status' => 'error', 'message' => $this->errors);
	}

	public function payment_media_apply() {
		if ($this->validate_form_value('apply')) {
			$success = TRUE;
			$error = '';
			$this->db->query('BEGIN');

			//生成逻辑ID
			$payment_id = $this
					->getSequence(
							date('y', time()) . $this->getCity_show()
									. date('m', time()) . 'MCP');

			if ($payment_id === FALSE) {
				$success = FALSE;
				$error = '生成付款单号出错';
			} else {
				//媒体银行信息
				$do_media_info = $this->_do_media_info();
				if ($do_media_info['status'] === 'error') {
					$success = FALSE;
					$error = $do_media_info['message'];
				} else {
					$media_info_id = $do_media_info['message'];
				}

				if ($success) {
					$payment_amount_plan = $this->payment_amount_plan;
					$rebate_amount = intval($this->is_rebate_deduction) === 1 ? (empty(
									$this->rebate_amount) ? 0
									: $this->rebate_amount) : 0;
					$person_loan_amount = intval(
							$this->is_person_loan_deduction) === 1 ? (empty(
									$this->person_loan_amount) ? 0
									: $this->person_loan_amount) : 0;
					$deposit_amount = 0;
					$real = $payment_amount_plan - $rebate_amount
							- $person_loan_amount - $deposit_amount;
					$insert_result = $this->db
							->query(
									'INSERT INTO finance_payment_media_apply(media_info_id,payment_amount_plan,payment_date,is_nim_pay_first,is_rebate_deduction,rebate_amount,rebate_dids,is_deposit_deduction,deposit_dids,is_person_loan_deduction,person_loan_amount,payment_amount_real,remark,payment_apply_deadline,statement,isalter,isok,user,addtime,step,pcid,payment_id) VALUE('
											. $media_info_id . ','
											. $payment_amount_plan . ',"'
											. $this->payment_date . '",'
											. intval($this->is_nim_pay_first)
											. ','
											. intval($this->is_rebate_deduction)
											. ',' . $rebate_amount . ',"'
											. $this->rebate_dids . '",'
											. intval(
													$this->is_deposit_deduction)
											. ',"' . $this->deposit_dids . '",'
											. intval(
													$this
															->is_person_loan_deduction)
											. ',' . $person_loan_amount . ','
											. $real . ',"' . $this->remark
											. '","'
											. $this->payment_apply_deadline
											. '",' . $this->statement . ',0,0,'
											. $this->getUid() . ',now(),0,'
											. intval($this->pcid) . ',"'
											. $payment_id . '")');
					if ($insert_result === FALSE) {
						$success = FALSE;
						$error = '媒体付款申请失败，错误代码1';
					} else {
						$media_applyid = $this->db->insert_id;

						//导入对账单内容
						$do_statement = $this
								->_do_payment_media_statement(0, $payment_id,
										TRUE, $media_applyid);
						if ($do_statement['status'] === 'error') {
							$success = FALSE;
							$error = $do_statement['message'];
						} else {
							//更新总付款额
							$result = $this->db
									->query(
											'UPDATE finance_payment_media_apply SET payment_amount_plan='
													. $do_statement['message']
													. ',payment_amount_real='
													. ($do_statement['message']
															- $payment_amount_plan
															+ $real)
													. ' WHERE id='
													. $media_applyid);
							if ($result === FALSE) {
								$success = FALSE;
								$error = '更新应付款额出错';
							} else {
								//分配用户
								$subsql = array();
								$users = explode(',', $this->users);
								foreach ($users as $userid) {
									if ($userid !== '') {
										$subsql[] = '(' . $media_applyid . ', '
												. $userid . ',1,"'
												. $payment_id . '",0)';
									}
								}
								if (!empty($subsql)) {
									$insert_result = $this->db
											->query(
													'INSERT INTO finance_payment_media_apply_user(payment_media_apply_id,userid,isok,payment_id,isfinished) VALUES '
															. implode(',',
																	$subsql));
									if ($insert_result === FALSE) {
										$success = FALSE;
										$error = '选择用户失败';
									}
								} else {
									$success = FALSE;
									$error = '用户选择不能为空';
								}
							}

							if ($success) {
								//记录日志
								$result = $this
										->_log($payment_id, '发起人',
												'<font color=\'#99cc00\'>新建媒体付款申请</font>');
								if ($result === FALSE) {
									$success = FALSE;
									$error = '媒体付款申请失败，错误代码3';
								}
							}
						}
					}
				}
			}
			//$success = FALSE;
			if ($success) {
				$this->db->query('COMMIT');
			} else {
				$this->db->query('ROLLBACK');
			}
			return array('status' => $success ? 'success' : 'error',
					'message' => $success ? '媒体付款申请成功' : $error);
		}

		return array('status' => 'error', 'message' => $this->errors);
	}

	private function _get_list_data($ismy = FALSE) {
		$this->all_count = intval(
				$this->db
						->get_var(
								'SELECT COUNT(*) FROM finance_payment_media_apply WHERE '
										. ($ismy ? 'user=' . $this->getUid()
												: '1=1')));
		$this->page_count = ceil($this->all_count / self::LIMIT);
		$start = self::LIMIT * intval($this->page) - self::LIMIT;
		if ($start < 0) {
			$start = 0;
		}
		$results = array();
		$lists = $this->db
				->get_results(
						'SELECT a.payment_id,a.id,a.payment_date,a.payment_amount_plan,a.payment_amount_real,a.isalter,a.isok,a.step,b.media_name FROM finance_payment_media_apply a LEFT JOIN finance_payment_media_info b ON a.media_info_id=b.id WHERE '
								. ($ismy ? 'user=' . $this->getUid() : '1=1')
								. ' ORDER BY id DESC LIMIT ' . $start . ','
								. self::LIMIT);
		if ($lists !== NULL) {
			foreach ($lists as $list) {
				$results[] = array('id' => $list->id,
						'payment_date' => $list->payment_date,
						'payment_amount_plan' => $list->payment_amount_plan,
						'payment_amount_real' => $list->payment_amount_real,
						'isalter' => $list->isalter, 'isok' => $list->isok,
						'step' => $list->step,
						'media_name' => $list->media_name,
						'payment_id' => $list->payment_id);
			}
		}
		return $results;
	}

	private static function _get_payment_id($isalter) {
		if (intval($isalter) === 0) {
			return '<font color="#66cc00">【新】</font>';
		} else {
			return '<font color="#cc6600">【变' . $isalter . '】</font>';
		}
	}

	private function _get_media_payment_list_html($ismy = FALSE,
			$is_manager = FALSE) {
		$datas = $this->_get_list_data($ismy);
		$result = '';
		if (!empty($datas)) {
			foreach ($datas as $data) {
				$result .= '<tr><td>' . self::_get_payment_id($data['isalter'])
						. '</td><td>' . $data['payment_id'] . '</td><td>'
						. $data['payment_date'] . '</td><td>'
						. $data['media_name'] . '</td><td>'
						. $data['payment_amount_plan'] . '</td><td>'
						. $data['payment_amount_real']
						. '</td><td><font color="#ff6600"><b>'
						. $this->_get_status($data['step'], $data['isok'])
						. '</b></font></td><td>'
						. $this
								->_get_action($data['id'], '', $data['step'],
										$data['isok'], $ismy, $is_manager)
						. '</td></tr>';
			}
		} else {
			$result .= '<tr><td colspan="8"><font color="red"><b>没有相关数据！</b></font></td></tr>';
		}
		return $result;
	}

	private function _get_apply_counts() {
		return $this->page . '	/' . $this->page_count . ' 页 &nbsp;&nbsp;';
	}

	private function _get_pagination($is_prev, $action) {
		return '<a href="' . BASE_URL . 'finance/payment/?o=' . $action
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

	private function _get_status($step, $isok) {
		$process_step = $this->process_step;
		if (count($process_step) === (intval($step) + 1)) {
			return '审核完成';
		} else {
			if (intval($isok) === 0) {
				$next = intval($step) + 1;
				return '等待 ' . $process_step[$next]['content'][0];
			} else if (intval($isok) === 2) {
				return '驳回';
			}
		}
	}

	private function _get_action($id, $assignid, $step, $isok, $ismy = FALSE,
			$is_manager = FALSE) {
		if ($ismy) {
			return '<a href="' . BASE_URL
					. 'finance/payment/?o=editmymediapayment&id=' . intval($id)
					. '">修改</a>';
		} else {
			//如果流程到了才审核
			$s = '';
			$next_step = $this->process_step[$step + 1]['content'][0];
			if ($is_manager) {
				if (intval($isok) !== 2) {
					if (count($this->process_step) === (intval($step) + 1)) {
						return '<a href="' . BASE_URL
								. 'finance/payment/?o=media_gd&id='
								. intval($id) . '">归档</a>';
					} else {
						foreach ($this->process_step as $stepkey => $value) {
							if ((intval($step) + 1) === $stepkey
									&& in_array($value['content'][2],
											$this->getPermissions(), TRUE) || $next_step === '员工填写') {
								$s = '<a href="' . BASE_URL
										. 'finance/payment/?o=auditmediapayment&id='
										. intval($id) . '">审核</a>';
								break;
							}
						}
					}
				}

			} else {
				//$next_step = $this->process_step[$step + 1]['content'][0];
				if ($next_step === '员工填写') {
					$s = '<a href="' . BASE_URL
							. 'finance/payment/?o=payment_userinput&id='
							. intval($assignid) . '">填写</a>';
				}
			}
			return $s;
		}
	}

	public function get_payment_media_mylist_html() {
		$buf = file_get_contents(
				TEMPLATE_PATH . 'finance/payment/payment_media_list.tpl');
		return str_replace(
				array('[LEFT]', '[TOP]', ' [PAYMENTMEDIALIST]', '[ALLCOUNTS]',
						'[COUNTS]', '[NEXT]', '[PREV]', '[VCODE]',
						'[BASE_URL]'),
				array($this->get_left_html(), $this->get_top_html(),
						$this->_get_media_payment_list_html(TRUE),
						$this->all_count, $this->_get_apply_counts(),
						$this->_getNext('mymedialist'),
						$this->_getPrev('mymedialist'), $this->get_vcode(),
						BASE_URL), $buf);
	}

	private function _check_format($line, $infos) {
		$isok = TRUE;
		$errors = array();
		//第一列广告主 非必须，长度255
		if (self::validate_field_not_empty($infos[0])
				&& !self::validate_field_max_length($infos[0], 255)) {
			$errors[] = '对账单文件格式有误，第' . $line . '行，第1列【广告主】最多255个字符';
			$isok = $isok ? FALSE : $isok;
		}

		//第二列产品 非必须，长度255
		if (self::validate_field_not_empty($infos[1])
				&& !self::validate_field_max_length($infos[1], 255)) {
			$errors[] = '对账单文件格式有误，第' . $line . '行，第2列【产品】最多255个字符';
			$isok = $isok ? FALSE : $isok;
		}

		//第三列合同付款额 必须，金额值
		if (!self::validate_field_not_empty($infos[2])
				|| !self::validate_field_not_null($infos[2])) {
			$errors[] = '对账单文件格式有误，第' . $line . '行，第3列【合同付款额】不能为空';
			$isok = $isok ? FALSE : $isok;
		} else if (!self::validate_money($infos[2])) {
			$errors[] = '对账单文件格式有误，第' . $line . '行，第3列【合同付款额】不是有效金额值';
			$isok = $isok ? FALSE : $isok;
		}

		//第四列充值日期 非必须，长度255
		if (self::validate_field_not_empty($infos[3])
				&& !self::validate_field_max_length($infos[3], 255)) {
			$errors[] = '对账单文件格式有误，第' . $line . '行，第4列【充值日期】最多255个字符';
			$isok = $isok ? FALSE : $isok;
		}

		//第五列媒体合同号 非必须，长度255
		if (self::validate_field_not_empty($infos[4])
				&& !self::validate_field_max_length($infos[4], 255)) {
			$errors[] = '对账单文件格式有误，第' . $line . '行，第5列【媒体合同号】最多255个字符';
			$isok = $isok ? FALSE : $isok;
		}

		return array('isok' => $isok, 'message' => $errors);
	}

	private function _import_statement($fileid, $payment_media_apply_id,
			$payment_id) {
		//根据文件ID查找文件
		$row = $this->db
				->get_row(
						'SELECT filename FROM uploadfile WHERE id='
								. intval($fileid) . ' FOR UPDATE');
		if ($row !== NULL) {
			$filename = UPLOAD_FILE_PATH . $row->filename;
			if (file_exists($filename)) {
				//校验文件真实类型
				$file_ok = FALSE;
				foreach ($GLOBALS['defined_upload_execel_validate_type'] as $vtype) {
					if (FileTypeValidation::validation($filename, $vtype)) {
						$file_ok = TRUE;
						break;
					}
				}

				if ($file_ok) {
					//解析excel

					$PHPExcel = new PHPExcel();
					if (strtolower(pathinfo($filename, PATHINFO_EXTENSION))
							=== 'xls') {
						$PHPReader = new PHPExcel_Reader_Excel5();
					} else if (strtolower(
							pathinfo($filename, PATHINFO_EXTENSION)) === 'xlsx') {
						$PHPReader = new PHPExcel_Reader_Excel2007();
					}

					$PHPExcel = $PHPReader->load($filename);
					$PHPExcel->setActiveSheetIndex(0);
					$sheet = $PHPExcel->getActiveSheet();
					$sum_cols_count = PHPExcel_Cell::columnIndexFromString(
							$sheet->getHighestColumn());
					$sum_rows_count = $sheet->getHighestRow();

					$errors = array();

					$sum_amount = 0;

					//应该是5列，行数>1
					if ($sum_cols_count !== 5) {
						$errors[] = '上传文件的列数非有效';
					} else if ($sum_rows_count <= 1) {
						$errors[] = '上传文件的行数非有效';
					} else {
						$subsql = array();
						for ($i = 2; $i <= $sum_rows_count; $i++) {
							for ($j = 0; $j < $sum_cols_count; $j++) {
								$infos[$i][$j] = $sheet
										->getCellByColumnAndRow($j, $i)
										->getCalculatedValue();
								$infos[$i][$j] = $infos[$i][$j] === NULL ? NULL
										: trim($infos[$i][$j]);
							}

							$isok = $this->_check_format($i, $infos[$i]);
							if ($isok['isok']) {

								//检验通过
								$subsql[] = '(' . $payment_media_apply_id
										. ',"' . $infos[$i][0] . '","'
										. $infos[$i][1] . '",' . $infos[$i][2]
										. ',"'
										. (!empty($infos[$i][3])
												&& !self::validate_utf8_chinese(
														$infos[$i][3])
												&& self::validate_date_int(
														PHPExcel_Shared_Date::ExcelToPHP(
																$infos[$i][3])) ? date(
														'Y-m-d',
														PHPExcel_Shared_Date::ExcelToPHP(
																$infos[$i][3]))
												: $infos[$i][3]) . '","'
										. $infos[$i][4] . '","' . $payment_id
										. '",1)';

								$sum_amount += $infos[$i][2];

							} else {
								$errors = array_merge($errors, $isok['message']);
							}
						}
					}

					if (empty($errors)) {
						$insert_result = $this->db
								->query(
										'INSERT INTO finance_payment_media_apply_items(payment_media_apply_id,ggz,cp,htfke,czrq,hth,payment_id,isok) VALUES '
												. implode(',', $subsql));
						if ($insert_result !== FALSE) {
							return array('status' => 'success',
									'message' => $sum_amount);
						} else {
							return array('status' => 'error',
									'message' => '导入对账单失败，错误代码1');
						}
					} else {
						return array('status' => 'error', 'message' => $errors);
					}
				} else {
					return array('status' => 'error', 'message' => '文件非有效类型');
				}
			} else {
				return array('status' => 'error', 'message' => '文件不存在');
			}
		} else {
			return array('status' => 'error', 'message' => '没有该文件记录');
		}
	}

	public function payment_media_audit_pass() {
		if ($this->validate_form_value('audit_pass')) {
			$success = TRUE;
			$error = '';
			$this->db->query('BEGIN');

			$row = $this->db
					->get_row(
							'SELECT step,payment_id,statement FROM finance_payment_media_apply WHERE id='
									. intval($this->id));
			if ($row !== NULL) {
				if (intval($row->step) === 0) {
					//更新信息

					$do_media_info = $this->_do_media_info();
					if ($do_media_info['status'] === 'error') {
						$success = FALSE;
						$error = $do_media_info['message'];
					} else {
						$media_info_id = $do_media_info['message'];
					}

					//finance_payment_media_apply
					if ($success) {
						$payment_amount_plan = $this->payment_amount_plan;
						$rebate_amount = intval($this->is_rebate_deduction)
								=== 1 ? (empty($this->rebate_amount) ? 0
										: $this->rebate_amount) : 0;
						$person_loan_amount = intval(
								$this->is_person_loan_deduction) === 1 ? (empty(
										$this->person_loan_amount) ? 0
										: $this->person_loan_amount) : 0;
						$deposit_amount = 0;

						$update_result = $this->db
								->query(
										'UPDATE finance_payment_media_apply SET media_info_id='
												. $media_info_id
												. ',payment_amount_plan='
												. $payment_amount_plan
												. ',payment_date="'
												. $this->payment_date
												. '",is_nim_pay_first='
												. intval(
														$this->is_nim_pay_first)
												. ',is_rebate_deduction='
												. intval(
														$this
																->is_rebate_deduction)
												. ',rebate_amount='
												. $rebate_amount
												. ',rebate_dids="'
												. $this->rebate_dids
												. '",is_deposit_deduction='
												. intval(
														$this
																->is_deposit_deduction)
												. ',deposit_dids="'
												. $this->deposit_dids
												. '",is_person_loan_deduction='
												. intval(
														$this
																->is_person_loan_deduction)
												. ',person_loan_amount='
												. $person_loan_amount
												. ',payment_amount_real='
												. ($payment_amount_plan
														- $rebate_amount
														- $person_loan_amount
														- $deposit_amount)
												. ',remark="' . $this->remark
												. '",payment_apply_deadline="'
												. $this->payment_apply_deadline
												. '",statement='
												. $this->statement
												. ',isok=0 WHERE id='
												. intval($this->id));
						if ($update_result === FALSE) {
							$success = FALSE;
							$error = '审核媒体付款申请失败，错误代码1';
						}
					}

					//finance_payment_media_apply_items
					if ($success) {
						$do_statement = $this
								->_do_payment_media_statement($row->statement,
										$row->payment_id, FALSE);
						if ($do_statement['status'] === 'error') {
							$success = FALSE;
							$error = $do_statement['message'];
						}
					}

					//finance_payment_media_apply_user
					if ($success) {
						$do_apply_user = $this
								->_do_payment_media_apply_user($row->payment_id);
						if ($do_apply_user['status'] === 'error') {
							$success = FALSE;
							$error = '分配用户失败';
						}
					}
				}

				if ($success) {
					$update_result = $this->db
							->query(
									'UPDATE finance_payment_media_apply SET step=step+1'
											. (count($this->process_step)
													=== (intval($row->step) + 1) ? ',isok=1'
													: '') . ' WHERE id='
											. intval($this->id));
					if ($update_result === FALSE) {
						$success = FALSE;
						$error = '审核媒体付款申请失败，错误代码7';
					} else {
						//记录日志
						$pstep = $this->process_step;
						$result = $this
								->_log($row->payment_id,
										$pstep[$row->step]['content'][0],
										'<font color=\'#99cc00\'>审核确认</font>');
						if ($result === FALSE) {
							$success = FALSE;
							$error = '审核媒体付款申请失败，错误代码7';
						}
					}
				}
			} else {
				$success = FALSE;
				$error = '没有该付款申请';
			}
			if ($success) {
				$this->db->query('COMMIT');
			} else {
				$this->db->query('ROLLBACK');
			}
			return array('status' => $success ? 'success' : 'error',
					'message' => $success ? '审核媒体付款申请成功' : $error);
		}

		return array('status' => 'error', 'message' => $this->errors);
	}

	public function payment_media_audit_reject() {
		if ($this->validate_form_value('audit_reject')) {
			$success = TRUE;
			$error = '';
			$this->db->query('BEGIN');

			$row = $this->db
					->get_row(
							'SELECT step,payment_id FROM finance_payment_media_apply WHERE id='
									. intval($this->id));

			if ($row !== NULL) {
				//查看是否到流程
				$get_permission = FALSE;
				$auditname = '';
				foreach ($this->process_step as $stepkey => $value) {
					if ((intval($row->step) + 1) === $stepkey
							&& in_array($value['content'][2],
									$this->getPermissions(), TRUE)) {
						$auditname = $value['content'][0];
						$get_permission = TRUE;
						break;
					}
				}

				if ($get_permission) {
					$update_result = $this->db
							->query(
									'UPDATE finance_payment_media_apply SET isok=2,step=0 WHERE id='
											. intval($this->id));
					if ($update_result === FALSE) {
						$success = FALSE;
						$error = '驳回媒体付款申请失败';
					} else {
						$update_result = $this
								->_log($row->payment_id, $auditname,
										'<font color=\'#ff9900\'>驳回 至 '
												. $this
														->process_step[$this
																->reject_step]['content'][0]
												. '</font>',
										$this->audit_content);
						if ($update_result === FALSE) {
							$success = FALSE;
							$error = '驳回媒体付款申请记录日志失败';
						}
					}
				} else {
					$success = FALSE;
					$error = NO_RIGHT_TO_DO_THIS;
				}

			} else {
				$success = FALSE;
				$error = '没有该媒体付款申请';
			}

			if ($success) {
				$this->db->query('ROLLBACK');
			} else {
				$this->db->query('ROLLBACK');
			}
			return array('status' => $success ? 'success' : 'error',
					'message' => $success ? '审核媒体付款申请成功' : $error);
		}

		return array('status' => 'error', 'message' => $this->errors);
	}

	private function _get_assign_list_data() {
		$this->all_count = intval(
				$this->db
						->get_var(
								'SELECT COUNT(*) FROM finance_payment_media_apply_user WHERE userid='
										. $this->getUid()));
		$this->page_count = ceil($this->all_count / self::LIMIT);
		$start = self::LIMIT * intval($this->page) - self::LIMIT;
		if ($start < 0) {
			$start = 0;
		}
		$results = array();
		$lists = $this->db
				->get_results(
						'SELECT a.payment_id,a.id,a.payment_amount_plan,a.payment_date,a.payment_amount_real,a.payment_apply_deadline,a.addtime,a.isalter,a.isok,a.step,b.media_name,c.id AS assignid FROM finance_payment_media_apply a LEFT JOIN finance_payment_media_info b ON a.media_info_id=b.id LEFT JOIN finance_payment_media_apply_user c ON a.id=c.payment_media_apply_id WHERE c.userid='
								. $this->getUid()
								. ' AND c.isok=1 ORDER BY c.isfinished,a.addtime DESC LIMIT '
								. $start . ',' . self::LIMIT);
		if ($lists !== NULL) {
			foreach ($lists as $list) {
				$results[] = array('id' => $list->id,
						'payment_amount_plan' => $list->payment_amount_plan,
						'payment_date' => $list->payment_date,
						'payment_amount_real' => $list->payment_amount_real,
						'payment_apply_deadline' => $list
								->payment_apply_deadline,
						'addtime' => $list->addtime,
						'media_name' => $list->media_name,
						'payment_id' => $list->payment_id,
						'isalter' => $list->isalter, 'isok' => $list->isok,
						'step' => $list->step, 'assignid' => $list->assignid);
			}
		}
		return $results;
	}

	private function _get_media_payment_deposit_user_assign_list_html() {

	}

	private function _get_media_payment_user_assign_list_html() {
		$datas = $this->_get_assign_list_data();
		$result = '';
		if (!empty($datas)) {
			foreach ($datas as $data) {
				$result .= '<tr><td>' . self::_get_payment_id($data['isalter'])
						. '</td><td>' . $data['payment_id'] . '</td><td>'
						. $data['payment_date'] . '</td><td>'
						. $data['media_name'] . '</td><td>'
						. $data['payment_amount_plan'] . '</td><td>'
						. $data['payment_amount_real']
						. '</td><td><font color="#ff6600"><b>'
						. $this->_get_status($data['step'], $data['isok'])
						. '</b></font></td><td>'
				//. $this->_get_action($data['assignid'], $data['step'])
						. $this
								->_get_action($data['id'], $data['assignid'],
										$data['step'], $data['isok'])
						. '</td></tr>';
			}
		} else {
			$result .= '<tr><td colspan="8"><font color="red"><b>没有相关数据！</b></font></td></tr>';
		}
		return $result;
	}

	public function get_payment_media_apply_user_assign_html() {
		$buf = file_get_contents(
				TEMPLATE_PATH . 'finance/payment/payment_media_user_assign.tpl');
		return str_replace(
				array('[LEFT]', '[TOP]', ' [PAYMENTMEDIALIST]', '[ALLCOUNTS]',
						'[COUNTS]', '[NEXT]', '[PREV]', '[VCODE]',
						'[BASE_URL]'),
				array($this->get_left_html(), $this->get_top_html(),
						$this->_get_media_payment_user_assign_list_html(),
						$this->all_count, $this->_get_apply_counts(),
						$this->_getNext('mymedialist'),
						$this->_getPrev('mymedialist'), $this->get_vcode(),
						BASE_URL), $buf);
	}

	public function get_payment_media_gd_html() {
		if ($this->has_payment_media_apply) {
			$id = Security_Util::my_get('id');
			$row = $this->db
					->get_row(
							'SELECT a.*,b.media_name,b.account_bank,b.account FROM finance_payment_media_apply a LEFT JOIN finance_payment_media_info b ON a.media_info_id=b.id WHERE a.id='
									. intval($id));
			if ($row !== NULL) {
				//$item_assign = $this
				//		->_get_payment_media_items_assign(intval($id));
				$buf = file_get_contents(
						TEMPLATE_PATH . 'finance/payment/payment_media_gd.tpl');
				$search = array('[LEFT]', '[TOP]', '[PAYMENTAMOUNTPLAN]',
						'[PAYMENTDATE]', '[REBATEAMOUNT]',
						'[PAYMENTAPPLYDEADLINE]', '[PAYMENTAMOUNTREAL]',
						'[MEDIANAME]', '[ADDTIME]', '[VCODE]', '[APPLYID]',
						'[NIMBANKS]', '[BASE_URL]', '[PAYMENTID]');
				$replace = array($this->get_left_html(), $this->get_top_html(),
						$row->payment_amount_plan, $row->payment_date,
						$row->rebate_amount, $row->payment_apply_deadline,
						$row->payment_amount_real, $row->media_name,
						$row->addtime, $this->get_vcode(), intval($id),
						Nim_BankInfo::get_bank_account_list(), BASE_URL,
						$row->payment_id);
				return str_replace($search, $replace, $buf);
			} else {
				return User::no_object('没有该媒体批量付款申请');
			}

			/*
			$buf = file_get_contents(
			        TEMPLATE_PATH . 'finance/payment/payment_media_gd.tpl');
			return str_replace(
			        array('[LEFT]', '[TOP]', '[VCODE]', '[BASE_URL]'),
			        array($this->get_left_html(), $this->get_top_html(),
			                $this->get_vcode(), BASE_URL), $buf);
			 */
		}
		return User::no_permission();
	}

	public function payment_media_gd() {
		if ($this->validate_form_value('payment_media_gd')) {
			$success = TRUE;
			$error = '';
			$this->db->query('BEGIN');

			$gdvalues_array = $this->gdvalues_array;
			$sql = array();
			foreach ($gdvalues_array as $gd) {
				$item = explode('_', $gd['item']);
				$sql[] = '(' . $this->id . ',"' . $this->payment_id . '","'
						. $item[1] . '",' . $item[2] . ',"'
						. $this->gdpaymentdate . '",' . $gd['gdamount'] . ','
						. $this->gdpaymenttype . ','
						. ($this->gdpaymentbank === '' ? 'NULL'
								: $this->gdpaymentbank) . ',2)';
			}
			if (!empty($sql)) {
				$insert_result = $this->db
						->query(
								'INSERT INTO finance_payment_gd(apply_id,payment_id,pid,paycostid,gd_time,gd_amount,payment_type,payment_bank,apply_type) VALUES'
										. implode(',', $sql));
				if ($insert_result === FALSE) {
					$success = FALSE;
					$error = '媒体批量付款申请归档失败';
				} else {
					$insert_result = $this
							->_log($this->payment_id, $this->getRealname(),
									'<font color=\'#99cc00\'>归档</font>');
					if ($insert_result === FALSE) {
						$success = FALSE;
						$error = '媒体批量付款申请归档记录日志失败';
					}
				}
			} else {
				$success = FALSE;
				$error = '归档数据选择有误';
			}

			if ($success) {
				$this->db->query('COMMIT');
			} else {
				$this->db->query('ROLLBACK');
			}
			return array('status' => $success ? 'success' : 'error',
					'message' => $success ? '归档媒体批量付款申请成功' : $error);
		}
		return array('status' => 'error', 'message' => $this->errors);
	}

	public function audit_item() {
		if ($this->validate_form_value('audit_item')) {
			$success = TRUE;
			$error = '';
			$this->db->query('BEGIN');

			$row = $this->db
					->get_row(
							'SELECT step,payment_id FROM finance_payment_media_apply WHERE id='
									. intval($this->id));

			if ($row !== NULL) {
				//查看是否到流程
				$get_permission = FALSE;
				$auditname = '';
				foreach ($this->process_step as $stepkey => $value) {
					if ((intval($row->step) + 1) === $stepkey
							&& in_array($value['content'][2],
									$this->getPermissions(), TRUE)) {
						$auditname = $value['content'][0];
						$get_permission = TRUE;
						break;
					}
				}

				if ($get_permission) {
					$update_result = $this->db
							->query(
									'UPDATE finance_payment_media_apply_list SET isok='
											. intval($this->auditsel)
											. ',remark="' . $this->auditresaon
											. '" WHERE id='
											. intval($this->listid));
					if ($update_result === FALSE) {
						$success = FALSE;
						$error = '审核媒体批量付款申请失败，错误代码1';
					} else {
						$results = $this->db
								->get_results(
										'SELECT isok FROM finance_payment_media_apply_list WHERE apply_id='
												. intval($this->id)
												. ' AND isok<>-1');
						if ($results !== NULL) {
							$now = 0;
							$hasdeff = FALSE;
							foreach ($results as $key => $result) {
								if ($now !== intval($result->isok)) {
									if ($key !== 0) {
										$hasdeff = TRUE;
										break;
									}
									$now = intval($result->isok);
								}
							}
							if (!$hasdeff) {
								if (intval($this->auditsel) === 1) {
									$sql = 'UPDATE finance_payment_media_apply SET step=step+1'
											. (intval($row->step) + 1
													=== count(
															$this->process_step)
															- 1 ? ',isok=1' : '')
											. ' WHERE id=' . intval($this->id);
									$type = '<font color=\'#99cc00\'>'
											. $auditname . ' 确认</font>';

									$update_result = $this->db->query($sql);
									if ($update_result === FALSE) {
										$success = FALSE;
										$error = '审核媒体批量付款申请失败，错误代码2';
									}

								} //else if (intval($this->auditsel) === 2) {
								//$sql = 'UPDATE finance_payment_person_apply SET step=0,isok=2 WHERE id='
								//		. intval($this->id);
								//$type = '<font color=\'#ff9900\'>驳回至 发起人</font>';
								//}
								//$update_result = $this->db->query($sql);
								//if ($update_result === FALSE) {
								//	$success = FALSE;
								//	$error = '审核个人付款申请失败，错误代码2';
								//}
							} else {
								$type = '<font color=\'#99cc00\'>' . $auditname
										. (intval($this->auditsel) === 1 ? '确认'
												: '驳回') . ' 条目</font>';
							}
						} else {
							$success = FALSE;
							$error = '审核媒体批量付款申请失败，错误代码3';
						}

						if ($success) {
							$update_result = $this
									->_log($row->payment_id, $auditname, $type,
											'');
							if ($update_result === FALSE) {
								$success = FALSE;
								$error = '审核媒体批量付款申请记录日志失败';
							}
						}
					}
				} else {
					$success = FALSE;
					$error = NO_RIGHT_TO_DO_THIS;
				}

			} else {
				$success = FALSE;
				$error = '没有该媒体批量付款申请';
			}

			if ($success) {
				$this->db->query('COMMIT');
			} else {
				$this->db->query('ROLLBACK');
			}
			return array('status' => $success ? 'success' : 'error',
					'message' => $success ? '审核媒体批量付款申请成功' : $error);
		}

		return array('status' => 'error', 'message' => $this->errors);
	}

	public function audit_full_payment_media() {
		if ($this->validate_form_value('audit_fullpayment_media')) {
			$success = TRUE;
			$error = '';
			$this->db->query('BEGIN');

			$row = $this->db
					->get_row(
							'SELECT step,payment_id FROM finance_payment_media_apply WHERE id='
									. intval($this->id));

			if ($row !== NULL) {
				//查看是否到流程
				$get_permission = FALSE;
				$auditname = '';
				foreach ($this->process_step as $stepkey => $value) {
					if ((intval($row->step) + 1) === $stepkey
							&& in_array($value['content'][2],
									$this->getPermissions(), TRUE)) {
						$auditname = $value['content'][0];
						$get_permission = TRUE;
						break;
					}
				}
				if ($get_permission) {
					//finance_payment_media_apply_list
					$update_result = $this->db
							->query(
									'UPDATE finance_payment_media_apply_list SET isok='
											. ($this->auditvalue === 'pass' ? 1
													: 2) . ' WHERE apply_id='
											. intval($this->id)
											. ' AND id IN (SELECT list_id FROM finance_payment_media_apply_items_users WHERE payment_media_apply_id='
											. intval($this->id)
											. ' AND user_id='
											. intval($this->uid)
											. ' AND isok=1) AND isok=0');
					if ($update_result === FALSE) {
						$success = FALSE;
						$error = '审核媒体批量付款申请失败，错误代码1';
					}

					if ($success) {
						//全部审核通过，step+1
						$total = intval(
								$this->db
										->get_var(
												'SELECT COUNT(*) FROM finance_payment_media_apply_list WHERE apply_id='
														. intval($this->id)));
						$pass = intval(
								$this->db
										->get_var(
												'SELECT COUNT(*) FROM finance_payment_media_apply_list WHERE apply_id='
														. intval($this->id)
														. ' AND isok=1'));
						if ($total === $pass && $total > 0) {
							$update_result = $this->db
									->query(
											'UPDATE finance_payment_media_apply SET step=step+1 WHERE id='
													. intval($this->id));
							if ($update_result === FALSE) {
								$success = FALSE;
								$error = '审核媒体批量付款申请失败，错误代码2';
							}
						}
					}
				} else {
					$success = FALSE;
					$error = NO_RIGHT_TO_DO_THIS;
				}

			} else {
				$success = FALSE;
				$error = '没有该媒体批量付款申请';
			}

			if ($success) {
				$this->db->query('COMMIT');
			} else {
				$this->db->query('ROLLBACK');
			}
			return array('status' => $success ? 'success' : 'error',
					'message' => $success ? '审核媒体批量付款申请成功' : $error);
		}

		return array('status' => 'error', 'message' => $this->errors);
	}
}
