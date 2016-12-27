<?php
class Payment_Person_Apply_Deposit extends User {
	private $process_id;
	private $process_deps;
	private $process_step;
	private $cid;
	private $cusname;
	private $page;
	private $all_count;

	private $id;
	private $type;
	private $media_name;
	private $bank_name_select;
	private $bank_name;
	private $bank_account_select;
	private $bank_account;
	private $payment_amount_plan;
	private $payment_date;
	private $is_nim_pay_first;
	private $rebate_amount;
	private $is_rebate_deduction;
	private $is_deposit_deduction;
	private $is_person_loan_deduction;
	private $person_loan_amount;
	private $is_contract_deduction;
	private $remark;
	private $errors = array();
	private $payment_list = array();

	private $listid;
	private $auditsel;
	private $auditresaon;
	private $auditvalue;

	const LIMIT = 50;

	private $gdvalues_array;
	private $gdpaymentdate;
	private $gdpaymentamount;
	private $gdpaymenttype;
	private $gdpaymentbank;
	private $payment_id;

	private $deposit_list;

	public function __construct($fields = array()) {
		parent::__construct();
		if (!empty($fields)) {
			foreach ($this as $key => $value) {
				if ($fields[$key] !== NULL) {
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
			if ($finance_process['name'] === '个人申请支付媒体保证金流程') {
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
							. $content
							. '</span><label>个人申请支付媒体保证金流程</label></li>';
					$i++;
				}
			}
		}
		return $result;
	}

	public function get_payment_person_apply_deposit_html() {
		$buf = file_get_contents(
				TEMPLATE_PATH
						. 'finance/payment/payment_person_apply_deposit.tpl');
		return str_replace(
				array('[LEFT]', '[TOP]', '[MEDIANAMESELECT]', '[PROCESSLIST]',
						'[VCODE]', '[BASE_URL]'),
				array($this->get_left_html(), $this->get_top_html(),
						Payment_Media_Info::get_media_list(),
						$this->_get_process_list(), $this->get_vcode(),
						BASE_URL), $buf);
	}

	public function search_contract() {
		$s = '<table width="100%" class="sbd1"><tr><td>合同号</td><td>客户名</td><td>项目名称</td><td>已收客户保证金金额</td><td>已付媒体保证金金额</td><td>上次付媒体保证金时间</td><td></td></tr>';
		$where = array();
		if ($this->cid !== NULL && $this->cid !== '') {
			$where[] = 'a.cid LIKE "%' . $this->cid . '%"';
		}

		if ($this->cusname !== NULL && $this->cusname !== '') {
			$where[] = 'a.cusname LIKE "%' . $this->cusname . '%"';
		}

		$limit = 10;
		$allcount = intval(
				$this->db
						->get_var(
								'SELECT COUNT(*) FROM contract_cus a WHERE a.isok=1'
										. (!empty($where) ? ' AND '
														. implode(' AND ',
																$where) : '')));
		$page_count = ceil($allcount / $limit);
		$start = $limit * intval($this->page) - $limit;
		if ($start < 0) {
			$start = 0;
		}

		$results = array();
		$contracts = $this->db
				->get_results(
						'SELECT a.cid,a.cusname,a.contractcontent,b.receivablesamount,c.paymentamount,c.gd_time FROM contract_cus a LEFT JOIN (SELECT SUM(amount) AS receivablesamount ,cid from finance_deposit_receivables where isok=1 GROUP BY cid) b ON a.cid=b.cid LEFT JOIN (SELECT SUM(gd_amount) AS paymentamount,cid,gd_time FROM (SELECT * FROM finance_payment_deposit_gd WHERE isok=1 ORDER BY cid,gd_time DESC) x GROUP BY cid) c ON a.cid=c.cid WHERE a.isok=1'
								. (!empty($where) ? ' AND '
												. implode(' AND ', $where) : '')
								. ' LIMIT ' . $start . ',' . $limit);

		if ($contracts !== NULL) {
			foreach ($contracts as $contract) {
				$results[] = array('cid' => $contract->cid,
						'cusname' => $contract->cusname,
						'contractcontent' => $contract->contractcontent,
						'receivablesamount' => !empty(
								$contract->receivablesamount) ? $contract
										->receivablesamount : 0,
						'paymentamount' => !empty($contract->paymentamount) ? $contract
										->paymentamount : 0,'last_gd_time'=>!empty($contract->gd_time) ? $contract->gd_time : '');
			}
		}

		if (!empty($results)) {
			foreach ($results as $result) {
				$s .= '<tr><td>' . $result['cid'] . '</td><td>'
						. $result['cusname'] . '</td><td>'
						. $result['contractcontent'] . '</td><td>'
						. $result['receivablesamount'] . '</td><td>'
						. $result['paymentamount']
						. '</td><td>' . $result['last_gd_time'] .'</td><td><input type="button" value="展开" class="btn" onclick="javascript:openit(\''
						. $result['cid'] . '\')"></td></tr>';

				//select b.pid,b.cid,b.costpaymentinfoids  from (select max(isalter) as isalter,pid from executive group by pid) a left join executive b on (a.isalter=b.isalter and a.pid=b.pid) where b.cid='11BJ010001' and b.costpaymentinfoids is not null and b.costpaymentinfoids<>''
				$costpaymentinfoids = array();
				$exe_dep = array();
				//$rows1 = $this->db
				//		->get_results(
				//				'SELECT b.pid,b.cid,b.costpaymentinfoids  FROM (SELECT MAX(isalter) AS isalter,pid FROM executive GROUP BY pid) a LEFT JOIN executive b ON (a.isalter=b.isalter AND a.pid=b.pid) WHERE b.cid="'
				//						. $result['cid']
				//						. '" AND b.costpaymentinfoids IS NOT NULL AND b.costpaymentinfoids<>\'\'');
				$rows1 = $this->db
						->get_results(
								'SELECT b.pid,b.cid,b.costpaymentinfoids,b.support  FROM (SELECT MAX(isalter) AS isalter,pid FROM executive GROUP BY pid) a LEFT JOIN executive b ON (a.isalter=b.isalter AND a.pid=b.pid) WHERE b.cid="'
										. $result['cid'] . '"');
										//var_dump('SELECT b.pid,b.cid,b.costpaymentinfoids,b.support  FROM (SELECT MAX(isalter) AS isalter,pid FROM executive GROUP BY pid) a LEFT JOIN executive b ON (a.isalter=b.isalter AND a.pid=b.pid) WHERE b.cid="'
										//. $result['cid'] . '"');
				if ($rows1 !== NULL) {
					foreach ($rows1 as $row1) {
						if(!empty($row1->costpaymentinfoids)){
							$cpids = explode('^', $row1->costpaymentinfoids);
							foreach ($cpids as $cpid) {
								if (!empty($cpid)) {
									$costpaymentinfoids[] = $cpid;
								}
							}
						}
						
						if(!empty($row1->support)){
							$sids = explode('|', $row1->support);
							if(!empty($sids)){
								foreach ($sids as $sid){
									$sid = explode('^', $sid);
									if(!in_array($sid[1], $exe_dep,TRUE)){
										$exe_dep[] = $sid[1];
									}
								}
							}
						}
					}
					//var_dump($exe_dep);
					if(!empty($exe_dep)){
						$rs = $this->db->get_results('SELECT costpaymentinfoids FROM executive_dep WHERE id IN(' . implode(',', $exe_dep) . ')');
						//var_dump('SELECT costpaymentinfoids FROM executive_dep WHERE id IN(' . implode(',', $exe_dep) . ')');
						if(!empty($rs)){
							foreach ($rs as $rss){
								$rss_results = explode('^', $rss->costpaymentinfoids);
								foreach ($rss_results as $rss_result){
									if(!empty($rss_result)){
										$costpaymentinfoids[] = $rss_result;
									}
								}
							}
						}
					}
				}
				if (!empty($costpaymentinfoids)) {
					$rows2 = $this->db
							->get_results(
									'SELECT id,payname,category,payamount FROM executive_paycost WHERE id IN ('
											. implode(',', $costpaymentinfoids)
											. ')');
					$ss = '';
					if ($rows2 !== NULL) {
						$media = array();
						foreach ($rows2 as $row2) {
							if (!in_array(
									$row2->payname . '-_-!' . $row2->category,
									$media, TRUE)) {
								$media[] = $row2->payname . '-_-!'
										. $row2->category;
							}
						}

						if (!empty($media)) {
							foreach ($media as $m) {
								$mm = explode('-_-!', $m);
								$ss .= '<tr><td width="5%"><input type="checkbox" name="checkcid" value="'
										. $result['cid'] . '-_-!' . $m
										. '"></td><td style="font-weight:bold;width:35%;">媒体名：'
										. $mm[0] . '&nbsp;&nbsp;' . $mm[1]
										. '</td></tr>';
							}
						}
					}
					$s .= '<tr id="tr_' . $result['cid']
							. '"><td colspan="8"><table width="100%">' . $ss
							. '</table></td></tr>';
				}
			}
			$pageinfo = '<tr><td colspan="8"><div id="pageinfo">'
					. intval($this->page) . ' / ' . $page_count . ' 页 &nbsp;'
					. self::_getPrev($this->page, TRUE) . '&nbsp;'
					. self::_getNext($this->page, $page_count, TRUE)
					. '&nbsp; <input id="movepid" type="button" value="选 择" onclick="javascript:pidmove();" class="btn"/></div></td></tr>';
			$s .= $pageinfo;
			$s .= '</table><script>$(\'[id^=tr_]\').hide();</script>';
		} else {
			$s .= '<tr><td colspan="8"><font color="red"><b>没有找到相关内容!</b></font></td></tr>';
		}
		return $s;
	}

	private static function _getPrev($page, $isSearch) {
		if (intval($page) === 1) {
			return '';
		} else {
			return self::_get_pagination(intval($page) - 1, TRUE, $isSearch);
		}
	}

	private static function _getNext($page, $page_count, $isSearch) {
		if (intval($page) >= intval($page_count)) {
			return '';
		} else {
			return self::_get_pagination(intval($page) + 1, FALSE, $isSearch);
		}
	}

	private static function _get_pagination($page, $is_prev, $isSearch) {
		if ($isSearch) {
			return '<a href="javascript:void(0)" onclick="dosearch(' . $page
					. ');">' . ($is_prev ? '上一页' : '下一页') . '</a>';
		} else {
			return '<a href="' . BASE_URL
					. 'finance/payment/?o=person_deposit_apply_manager&page='
					. $page . '">' . ($is_prev ? '上一页' : '下一页') . '</a>';
		}
	}

	private function validate_form_value($action) {
		$errors = array();

		if (in_array($action,
				array('add', 'edit', 'cancel', 'audit_item',
						'audit_fullpayment_person',
						'payment_person_deposit_gd'), TRUE)) {
			if ($action === 'payment_person_deposit_gd') {
				//id
				if (!self::validate_id(intval($this->id))) {
					$errors[] = '所选付款申请有误';
				}

				$gdvalues_array = $this->gdvalues_array;
				$sum_gd = 0;
				foreach ($gdvalues_array as $key => $gd) {
					if (!self::validate_money($gd['gdamount'])) {
						$errors[] = '第' . ($key + 1) . '条所选归档数据金额有误';
					} else {
						$sum_gd += $gd['gdamount'];
					}
				}

				if (strtotime($this->gdpaymentdate) === FALSE) {
					$errors[] = '归档时间有误';
				}

				if (doubleval($this->gdpaymentamount) !== doubleval($sum_gd)) {
					$errors[] = '归档总金额与所选归档数据之和不匹配';
				}

				if (!in_array(intval($this->gdpaymenttype), array(1, 2), TRUE)) {
					$errors[] = '付款方式选择有误';
				}

				if (intval($this->gdpaymenttype) === 2
						&& !self::validate_id(intval($this->gdpaymentbank))) {
					$errors[] = '选择银行转账时，银行选择有误';
				}

			} else if ($action === 'audit_fullpayment_person') {
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
			} else if ($action === 'cancel') {
				if (!in_array($this->type, array('temp', 'untemp'), TRUE)) {
					$errors[] = '类型选择有误';
				}

				if (!self::validate_id(intval($this->id))) {
					$errors[] = '付款申请选择有误';
				}
			} else {
				if ($action === 'edit') {
					if (!self::validate_id(intval($this->id))) {
						$errors[] = '付款申请选择有误';
					}
				}

				//媒体名称
				if (!self::validate_field_not_null($this->media_name)
						|| !self::validate_field_not_empty($this->media_name)) {
					$errors[] = '媒体名称不能为空';
				} else if (self::validate_field_not_empty($this->media_name)
						&& !self::validate_field_max_length($this->media_name,
								255)) {
					$errors[] = '媒体名称最多255个字符';
				}

				//开户行
				if ((!self::validate_field_not_null($this->bank_name_select)
						|| !self::validate_field_not_empty(
								$this->bank_name_select))
						&& (!self::validate_field_not_null($this->bank_name)
								|| !self::validate_field_not_empty(
										$this->bank_name))) {
					$errors[] = '请选择已有的开户行或者输入新的开户行';
				}

				if (self::validate_field_not_empty($this->bank_name)
						&& !self::validate_field_max_length($this->bank_name,
								255)) {
					$errors[] = '开户行最多255个字符';
				}

				//银行账户
				if ((!self::validate_field_not_null($this->bank_account_select)
						|| !self::validate_field_not_empty(
								$this->bank_account_select))
						&& (!self::validate_field_not_null($this->bank_account)
								|| !self::validate_field_not_empty(
										$this->bank_account))) {
					$errors[] = '请选择已有的银行帐号或者输入新的银行帐号';
				}

				if (self::validate_field_not_empty($this->bank_account)
						&& !self::validate_field_max_length(
								$this->bank_account, 255)) {
					$errors[] = '银行账户最多255个字符';
				}

				//应付金额
				if (!self::validate_money($this->payment_amount_plan, FALSE)) {
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
				if (self::validate_field_not_empty($this->is_nim_pay_first)
						&& intval($this->is_nim_pay_first) !== 1) {
					$errors[] = '是否垫付选择有误';
				}

				//返点抵扣
				if (self::validate_field_not_empty($this->is_rebate_deduction)
						&& intval($this->is_rebate_deduction) !== 1) {
					$errors[] = '返点抵扣选择有误';
				}

				//返点金额
				if (self::validate_field_not_empty($this->rebate_amount)
						&& !self::validate_money($this->rebate_amount)) {
					$errors[] = '返点金额不是有效金额值';
				}

				//保证金抵扣
				if (self::validate_field_not_empty($this->is_deposit_deduction)
						&& intval($this->is_deposit_deduction) !== 1) {
					$errors[] = '保证金抵扣选择有误';
				}

				//个人借款抵扣
				if (self::validate_field_not_empty(
						$this->is_person_loan_deduction)
						&& intval($this->is_person_loan_deduction) !== 1) {
					$errors[] = '个人借款抵扣选择有误';
				}

				//备注
				if (self::validate_field_not_empty($this->remark)
						&& !self::validate_field_max_length($this->remark, 1000)) {
					$errors[] = '备注信息最多1000个字符';
				}

				//合同款抵扣
				if (self::validate_field_not_empty($this->is_contract_deduction)
						&& intval($this->is_contract_deduction) !== 1) {
					$errors[] = '合同款抵扣选择有误';
				}

				$payment_list = $this->payment_list;
				if (!empty($payment_list)) {
					foreach ($payment_list as $key => $paylist) {
						//申请金额付款类型
						//if (!in_array(intval($paylist['payment_type']),
						//		array(1, 2), TRUE)) {
						//	$errors[] = '第' . ($key + 1) . '行媒体数据【申请金额付款类型】选择有误';
						//} else {
						//if (intval($paylist['payment_type']) === 2
						//		&& !self::validate_money(
						//			$paylist['payment_amount'])) {
						if (!self::validate_money($paylist['payment_amount'])) {
							$errors[] = '第' . ($key + 1)
									. '行媒体数据【申请付款金额】非有效金额值';
						}
						//}
						//}

						//返点抵扣
						if (!empty($paylist['rebate_deduction_amount'])
								&& !self::validate_money(
										$paylist['rebate_deduction_amount'])) {
							$errors[] = '第' . ($key + 1) . '行媒体数据【返点抵扣】非有效金额值';
						}

						if (self::validate_field_not_empty(
								$paylist['rebate_deduction_dids'])
								&& !self::validate_field_max_length(
										$paylist['rebate_deduction_dids'],
										1000)) {
							$errors[] = '第' . ($key + 1) . '行媒体数据【返点抵扣】附件过多';
						}

						//个人借款抵扣
						if (self::validate_field_not_empty(
								$paylist['person_loan_user'])
								&& !self::validate_field_max_length(
										$paylist['person_loan_user'], 500)) {
							$errors[] = '第' . ($key + 1)
									. '行媒体数据【个人借款抵扣】还款人最多500个字符';
						}

						if (self::validate_field_not_empty(
								$paylist['person_loan_amount'])
								&& !self::validate_money(
										$paylist['person_loan_amount'])) {
							$errors[] = '第' . ($key + 1)
									. '行媒体数据【个人借款抵扣】金额非有效金额值';
						}

						//是否垫付
						if (self::validate_field_not_empty(
								$paylist['is_nim_pay_first'])
								&& intval($paylist['is_nim_pay_first']) !== 1) {
							$errors[] = '第' . ($key + 1) . '行媒体数据【是否垫付】选择有误';
						}

						if (self::validate_field_not_empty(
								$paylist['nim_pay_first_amount'])
								&& !self::validate_money(
										$paylist['nim_pay_first_amount'])) {
							$errors[] = '第' . ($key + 1)
									. '行媒体数据【是否垫付】金额非有效金额值';
						}

						if (self::validate_field_not_empty(
								$paylist['nim_pay_first_dids'])
								&& !self::validate_field_max_length(
										$paylist['nim_pay_first_dids'], 1000)) {
							$errors[] = '第' . ($key + 1) . '行媒体数据【是否垫付】附件过多';
						}

					}
				} else if (empty($payment_list)
						&& ($this->action === 'payment_person_apply')) {
					$errors[] = '所选媒体数据不能为空';
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

	public function edit_payment_deposit_person_apply() {
		if ($this->validate_form_value('edit')) {
			$success = TRUE;
			$error = '';
			$this->db->query('BEGIN');

			//检查付款申请是否存在
			$row = $this->db
					->get_row(
							'SELECT payment_id FROM finance_payment_person_deposit_apply WHERE id='
									. intval($this->id) . ' AND user='
									. $this->getUid());
			if ($row !== NULL) {
				$payment_id = $row->payment_id;

				//媒体信息
				$media_info_id = $this->_do_media_info();
				if ($media_info_id['status'] === 'error') {
					$success = FALSE;
					$error = '媒体信息出错';
				}

				if ($success) {
					$payment_amount_plan = $this->payment_amount_plan;
					$rebate_amount = intval($this->is_rebate_deduction) === 1 ? !empty(
									$this->rebate_amount) ? $this
											->rebate_amount : 0 : 0;
					$person_loan_amount = intval(
							$this->is_person_loan_deduction) === 1 ? !empty(
									$this->person_loan_amount) ? $this
											->person_loan_amount : 0 : 0;
					$deposit_amount = 0;
					//删除 原来的
					$delete = $this->db
							->query(
									'UPDATE finance_payment_deposit_deduction SET isok=-1 WHERE apply_id='
											. intval($this->id)
											. ' AND apply_type=1 AND payment_type=2');
					if ($delete === FALSE) {
						$success = FALSE;
						$error = '保证金抵扣更新失败';
					}

					$deposit_deduction_sql = array();
					if (intval($this->is_deposit_deduction) === 1
							&& !empty($this->deposit_list)) {
						foreach ($this->deposit_list as $val) {

							$deposit_amount += $val['amount'];
							//finance_payment_deposit_deduction
							$deposit_deduction_sql[] = 'INSERT INTO finance_payment_deposit_deduction(apply_id,deposit_gd_id,deduction_amount,addtime,isok,apply_type,payment_type) SELECT '
									. intval($this->id) . ',id,'
									. $val['amount']
									. ',now(),1,1,2 FROM finance_payment_deposit_gd WHERE id='
									. $val['id']
									. ' AND isok=1 AND apply_type='
									. ($val['dtype'] === 'p' ? 1 : 2);
						}
					}

					$contract_amount = 0;
					$payment_amount_real = $payment_amount_plan
							- $rebate_amount - $person_loan_amount
							- $deposit_amount - $contract_amount;
					$result = $this->db
							->query(
									'UPDATE finance_payment_person_deposit_apply SET media_info_id='
											. $media_info_id['message']
											. ',payment_amount_plan='
											. $payment_amount_plan
											. ',payment_date="'
											. $this->payment_date
											. '",is_nim_pay_first='
											. intval($this->is_nim_pay_first)
											. ',is_rebate_deduction='
											. intval($this->is_rebate_deduction)
											. ',rebate_amount='
											. $rebate_amount
											. ',is_deposit_deduction='
											. intval(
													$this->is_deposit_deduction)
											. ',is_person_loan_deduction='
											. intval(
													$this
															->is_person_loan_deduction)
											. ',person_loan_amount='
											. $person_loan_amount
											. ',is_contract_deduction='
											. intval(
													$this
															->is_contract_deduction)
											. ',payment_amount_real='
											. $payment_amount_real
											. ',remark="' . $this->remark
											. '",isok=0,addtime=now(),step=0 WHERE id='
											. intval($this->id));

					if ($result === FALSE) {
						$success = FALSE;
						$error = '更新付款申请出错，错误代码1';
					}
				}

				if ($success) {
					$updte_result = $this->db
							->query(
									'UPDATE finance_payment_person_deposit_apply_list SET isok=-1 WHERE apply_id='
											. intval($this->id));
					if ($update_result === FALSE) {
						$success = FALSE;
						$error = '更新付款申请出错，错误代码2';
					} else {

						$other = $this
								->_do_rebate_and_nimpayfirst(intval($this->id));
						if ($other['status'] === 'error') {
							$success = FALSE;
							$error = $other['message'];
						}

						/*
						$paylists = $this->payment_list;
						$sbusql = array();
						$rebate_array = array();
						$payfirst_array = array();
						foreach ($paylists as $paylist) {
						    $sbusql[] = '(' . $this->id . ',"'
						            . $paylist['cid'] . '","'
						            . $paylist['media_name'] . '","'
						            . $paylist['media_category'] . '",'
						            . $paylist['payment_amount'] . ','
						            . $paylist['rebate_deduction_amount']
						            . ',"' . $paylist['rebate_deduction_dids']
						            . '"," ' . $paylist['person_loan_user']
						            . '",' . $paylist['person_loan_amount']
						            . ','
						            . intval($paylist['is_nim_pay_first'])
						            . ',' . $paylist['nim_pay_first_amount']
						            . ',"' . $paylist['nim_pay_fitst_dids']
						            . '",0,"' . $paylist['remark'] . '")';
						
						    if (!empty($paylist['rebate_deduction_amount'])) {
						        //返点入返点表
						        $rebate_array[] = '(' . $this->id . ',1,'
						                . $paylist['rebate_deduction_amount']
						                . ',2,1)';
						    }
						
						    if (intval($paylist['is_nim_pay_first']) === 1
						            && !empty($paylist['nim_pay_first_amount'])) {
						        //垫付入垫付表
						        $payfirst_array[] = '(' . $this->id . ',1,'
						                . $paylist['nim_pay_first_amount']
						                . ',2,1)';
						    }
						}
						
						if (!empty($sbusql)) {
						    $insert_result = $this->db
						            ->query(
						                    'INSERT INTO finance_payment_person_deposit_apply_list(apply_id,cid,media_name,media_category,payment_amount,rebate_deduction_amount,rebate_deduction_dids,person_loan_user,person_loan_amount,is_nim_pay_first,nim_pay_first_amount,nim_pay_first_dids,isok,remark) VALUES'
						                            . implode(',', $sbusql));
						    if ($insert_result === FALSE) {
						        $success = FALSE;
						        $error = '更新付款申请出错，错误代码3';
						    }
						} else {
						    $success = FALSE;
						    $error = '更新付款申请出错，错误代码4';
						}
						
						//返点
						if ($success && !empty($rebate_array)) {
						    $rebate_result = $this->db
						            ->query(
						                    'INSERT INTO finance_payment_rebate(apply_id,payment_type,rebate_amount,amount_type,status) VALUES'
						                            . implode(',',
						                                    $rebate_array));
						    if ($insert_result === FALSE) {
						        $success = FALSE;
						        $error = '更新付款申请出错，错误代码5';
						    }
						}
						
						//垫付
						if ($success && !empty($payfirst_array)) {
						    $payfirst_result = $this->db
						            ->query(
						                    'INSERT INTO finance_payment_payfirst(apply_id,payment_type,payfirst_amount,amount_type,status) VALUES'
						                            . implode(',',
						                                    $payfirst_array));
						    if ($insert_result === FALSE) {
						        $success = FALSE;
						        $error = '更新付款申请出错，错误代码6';
						    }
						}
						 */

						if ($success && !empty($deposit_deduction_sql)) {
							//撤销原有记录
							$update_result = $this->db
									->query(
											'UPDATE finance_payment_deposit_deduction SET isok=-1 WHERE apply_id='
													. intval($this->id)
													. ' AND apply_type=1 AND payment_type=2');
							if ($update_result === FALSE) {
								$success = FALSE;
								$error = '修改付款申请失败，错误代码11';
							} else {
								foreach ($deposit_deduction_sql as $sql) {
									$insert_result = $this->db->query($sql);
									if ($insert_result === FALSE) {
										$success = FALSE;
										$error = '修改付款申请失败，错误代码5';
										break;
									}
								}
							}
						}
					}

				}

				if ($success) {
					//记录日志
					$result = $this
							->_log($payment_id, '发起人',
									'<font color=\'#99cc00\'>修改个人保证金付款申请</font>');
					if ($result === FALSE) {
						$success = FALSE;
						$error = '新建个人付款申请失败，错误代码4';
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
					'message' => $success ? '修改保证金付款申请成功' : $error);
		}

		return array('status' => 'error', 'message' => $this->errors);
	}

	//列表及返点及垫付
	private function _do_rebate_and_nimpayfirst($apply_id) {
		$paylists = $this->payment_list;
		$success = TRUE;
		$error = '';
		foreach ($paylists as $paylist) {
			$list_result = $this->db
					->query(
							'INSERT INTO finance_payment_person_deposit_apply_list(apply_id,cid,media_name,media_category,payment_amount,rebate_deduction_amount,rebate_deduction_dids,person_loan_user,person_loan_amount,is_nim_pay_first,nim_pay_first_amount,nim_pay_first_dids,isok,remark) VALUE('
									. $apply_id . ',"' . $paylist['cid']
									. '","' . $paylist['media_name'] . '","'
									. $paylist['media_category'] . '",'
									. (empty($paylist['payment_amount']) ? 0
											: $paylist['payment_amount']) . ','
									. (empty(
											$paylist['rebate_deduction_amount']) ? 0
											: $paylist['rebate_deduction_amount'])
									. ',"' . $paylist['rebate_deduction_dids']
									. '"," ' . $paylist['person_loan_user']
									. '",'
									. (empty($paylist['person_loan_amount']) ? 0
											: $paylist['person_loan_amount'])
									. ','
									. intval($paylist['is_nim_pay_first'])
									. ','
									. (empty($paylist['nim_pay_first_amount']) ? 0
											: $paylist['nim_pay_first_amount'])
									. ',"' . $paylist['nim_pay_fitst_dids']
									. '",0,"' . $paylist['remark'] . '")');
			if ($list_result === FALSE) {
				$success = FALSE;
				$error = '记录合同信息有误';
				break;
			} else {
				$list_id = $this->db->insert_id;

				if (!empty($paylist['rebate_deduction_amount'])) {
					$rebate_result = $this->db
							->query(
									'INSERT INTO finance_payment_rebate(apply_id,list_id,payment_type,rebate_amount,amount_type,status) VALUE('
											. $apply_id . ',' . $list_id
											. ',1,'
											. $paylist['rebate_deduction_amount']
											. ',2,1)');
					if ($rebate_result === FALSE) {
						$success = FALSE;
						$error = '记录返点信息有误';
						break;
					}
				}

				if (intval($paylist['is_nim_pay_first']) === 1
						&& !empty($paylist['nim_pay_first_amount'])) {
					$nim_pay_first_result = $this->db
							->query(
									'INSERT INTO finance_payment_payfirst(apply_id,list_id,payment_type,payfirst_amount,amount_type,status,addtime) VALUE('
											. $apply_id . ',' . $list_id
											. ',1,'
											. $paylist['nim_pay_first_amount']
											. ',2,1,now())');
					if ($nim_pay_first_result === FALSE) {
						$success = FALSE;
						$error = '记录垫付信息有误';
						break;
					}
				}
			}
		}
		return array('status' => $success ? 'success' : 'error',
				'message' => $success ? '成功' : $error);
	}

	public function add_payment_deposit_person_apply() {
		if ($this->validate_form_value('add')) {
			$success = TRUE;
			$error = '';
			$this->db->query('BEGIN');

			$payment_id = $this
					->getSequence(
							date('y', time()) . $this->getCity_show()
									. date('m', time()) . 'PDP');

			if ($payment_id === FALSE) {
				$success = FALSE;
				$error = '生成付款单号出错';
			} else {
				//媒体信息
				$do_media_info = $this->_do_media_info();
				if ($do_media_info['status'] === 'error') {
					$success = FALSE;
					$error = $do_media_info['message'];
				} else {
					$media_info_id = $do_media_info['message'];

					$payment_amount_plan = $this->payment_amount_plan;
					$rebate_amount = intval($this->is_rebate_deduction) === 1 ? (empty(
									$this->rebate_amount) ? 0
									: $this->rebate_amount) : 0;
					$person_loan_amount = intval(
							$this->is_person_loan_deduction) === 1 ? (empty(
									$this->person_loan_amount) ? 0
									: $this->person_loan_amount) : 0;
					$deposit_amount = 0;
					$deposit_deduction_sql = array();
					//var_dump(intval($this->is_deposit_deduction));
					//var_dump($this->deposit_list);
					if (intval($this->is_deposit_deduction) === 1
							&& !empty($this->deposit_list)) {
						foreach ($this->deposit_list as $val) {

							$deposit_amount += $val['amount'];
							//finance_payment_deposit_deduction
							$deposit_deduction_sql[] = 'INSERT INTO finance_payment_deposit_deduction(apply_id,deposit_gd_id,deduction_amount,addtime,isok,apply_type,payment_type) SELECT "APPLYID",id,'
									. $val['amount']
									. ',now(),1,1,2 FROM finance_payment_deposit_gd WHERE id='
									. $val['id']
									. ' AND isok=1 AND apply_type='
									. ($val['dtype'] === 'p' ? 1 : 2);
						}
					}

					$insert_result = $this->db
							->query(
									'INSERT INTO finance_payment_person_deposit_apply(payment_id,media_info_id,payment_amount_plan,payment_date,is_nim_pay_first,is_rebate_deduction,rebate_amount,is_deposit_deduction,is_person_loan_deduction,person_loan_amount,is_contract_deduction,payment_amount_real,remark,isalter,isok,user,addtime,step,pcid) VALUE("'
											. $payment_id . '",'
											. $media_info_id . ','
											. $payment_amount_plan . ' ,"'
											. $this->payment_date . '",'
											. intval($this->is_nim_pay_first)
											. ','
											. intval($this->is_rebate_deduction)
											. ',' . $rebate_amount . ','
											. intval(
													$this->is_deposit_deduction)
											. ','
											. intval(
													$this
															->is_person_loan_deduction)
											. ',' . $person_loan_amount . ','
											. intval(
													$this
															->is_contract_deduction)
											. ','
											. ($payment_amount_plan
													- $rebate_amount
													- $person_loan_amount
													- $deposit_amount) . ',"'
											. $this->remark . '",0,0,'
											. $this->getUid() . ',now(),0,'
											. $this->process_id . ')');

					if ($insert_result === FALSE) {
						$success = FALSE;
						$error = '新建付款申请失败，错误代码1';
					} else {
						$apply_id = $this->db->insert_id;

						$other = $this->_do_rebate_and_nimpayfirst($apply_id);
						if ($other['status'] === 'error') {
							$success = FALSE;
							$error = $other['message'];
						}

						/*
						$paylists = $this->payment_list;
						$sbusql = array();
						
						$rebate_array = array();
						$payfirst_array = array();
						foreach ($paylists as $paylist) {
						    $sbusql[] = '(' . $apply_id . ',"'
						            . $paylist['cid'] . '","'
						            . $paylist['media_name'] . '","'
						            . $paylist['media_category'] . '",'
						            . $paylist['payment_amount'] . ','
						            . $paylist['rebate_deduction_amount']
						            . ',"' . $paylist['rebate_deduction_dids']
						            . '"," ' . $paylist['person_loan_user']
						            . '",' . $paylist['person_loan_amount']
						            . ','
						            . intval($paylist['is_nim_pay_first'])
						            . ',' . $paylist['nim_pay_first_amount']
						            . ',"' . $paylist['nim_pay_fitst_dids']
						            . '",0,"' . $paylist['remark'] . '")';
						
						    if (!empty($paylist['rebate_deduction_amount'])) {
						        //返点入返点表
						        $rebate_array[] = '(' . $apply_id . ',1,'
						                . $paylist['rebate_deduction_amount']
						                . ',2,1)';
						    }
						
						    if (intval($paylist['is_nim_pay_first']) === 1
						            && !empty($paylist['nim_pay_first_amount'])) {
						        //垫付入垫付表
						        $payfirst_array[] = '(' . $apply_id . ',1,'
						                . $paylist['nim_pay_first_amount']
						                . ',2,1)';
						    }
						}
						
						if (!empty($sbusql)) {
						    $insert_result = $this->db
						            ->query(
						                    'INSERT INTO finance_payment_person_deposit_apply_list(apply_id,cid,media_name,media_category,payment_amount,rebate_deduction_amount,rebate_deduction_dids,person_loan_user,person_loan_amount,is_nim_pay_first,nim_pay_first_amount,nim_pay_first_dids,isok,remark) VALUES'
						                            . implode(',', $sbusql));
						    if ($insert_result === FALSE) {
						        $success = FALSE;
						        $error = '新建付款申请失败，错误代码2';
						    }
						} else {
						    $success = FALSE;
						    $error = '新建付款申请失败，错误代码3';
						}
						 */
					}

					//保证金抵扣
					if ($success && !empty($deposit_deduction_sql)) {
						foreach ($deposit_deduction_sql as $sql) {
							//var_dump(str_replace('"APPLYID"', $apply_id, $sql));
							$insert_result = $this->db
									->query(
											str_replace('"APPLYID"', $apply_id,
													$sql));
							if ($insert_result === FALSE) {
								$success = FALSE;
								$error = '添加保证金抵扣记录失败';
								break;
							}
						}
					}

					/*
					//返点
					if ($success && !empty($rebate_array)) {
					    $rebate_result = $this->db
					            ->query(
					                    'INSERT INTO finance_payment_rebate(apply_id,payment_type,rebate_amount,amount_type,status) VALUES'
					                            . implode(',', $rebate_array));
					    if ($insert_result === FALSE) {
					        $success = FALSE;
					        $error = '新建付款申请失败，错误代码3';
					    }
					}
					
					//垫付
					if ($success && !empty($payfirst_array)) {
					    $payfirst_result = $this->db
					            ->query(
					                    'INSERT INTO finance_payment_payfirst(apply_id,payment_type,payfirst_amount,amount_type,status) VALUES'
					                            . implode(',', $payfirst_array));
					    if ($insert_result === FALSE) {
					        $success = FALSE;
					        $error = '新建付款申请失败，错误代码4';
					    }
					}
					 */
				}
			}

			if ($success) {
				//记录日志
				$result = $this
						->_log($payment_id, '发起人',
								'<font color=\'#99cc00\'>新建个人保证金付款申请</font>');
				if ($result === FALSE) {
					$success = FALSE;
					$error = '新建个人付款申请失败，错误代码4';
				}
			}
			//$success=FALSE;
			if ($success) {
				$this->db->query('COMMIT');
			} else {
				$this->db->query('ROLLBACK');
			}
			return array('status' => $success ? 'success' : 'error',
					'message' => $success ? '新建付款申请成功' : $error);
		}
		return array('status' => 'error', 'message' => $this->errors);
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

	private function _get_manager_list() {
		$this->all_count = intval(
				$this->db
						->get_var(
								'SELECT COUNT(*) FROM finance_payment_person_deposit_apply'));
		$this->page_count = ceil($this->all_count / self::LIMIT);
		$start = self::LIMIT * intval($this->page) - self::LIMIT;
		if ($start < 0) {
			$start = 0;
		}
		$results = array();
		$lists = $this->db
				->get_results(
						'SELECT a.id,a.media_info_id,a.addtime,a.payment_date,a.payment_amount_plan,a.payment_amount_real,a.user,a.isalter,a.isok,a.step,a.is_gd,b.media_name,c.username,c.realname,d.depname FROM finance_payment_person_deposit_apply a LEFT JOIN finance_payment_media_info b ON a.media_info_id=b.id LEFT JOIN users c ON a.user=c.uid LEFT JOIN hr_department d ON c.dep=d.id ORDER BY payment_date DESC LIMIT '
								. $start . ',' . self::LIMIT);
		if ($lists !== NULL) {
			foreach ($lists as $list) {
				$results[] = array('id' => $list->id,
						'media_info_id' => $list->media_info_id,
						'addtime' => $list->addtime,
						'payment_date' => $list->payment_date,
						'payment_amount_plan' => $list->payment_amount_plan,
						'payment_amount_real' => $list->payment_amount_real,
						'isalter' => $list->isalter, 'isok' => $list->isok,
						'step' => $list->step,
						'media_name' => $list->media_name,
						'username' => $list->username,
						'realname' => $list->realname,
						'depname' => $list->depname, 'is_gd' => $list->is_gd);
			}
		}
		return $results;
	}

	private function _get_person_payment_manager_status($apply_id, $isok, $isgd) {
		$status = $this->_get_status_arrays($apply_id);
		//var_dump($status);

		//开始判断
		if (intval($isok) === 0) {
			if ($status['count'] === count($status['waitings'])) {
				//未开始审核
				return '等待审核';
			} else if (count($status['waitings']) > 0
					&& count($status['waitings']) < $status['count']) {
				//审核中
				return '审核中';
			}
		} else if (intval($isok) === 1) {
			$gd = intval($isgd) === 1 ? '已归档' : '待归档';
			if (count($status['auditpass']) + count($status['cancels'])
					=== $status['count'] && count($status['cancels']) > 0) {
				//审核通过，但有部分撤销
				return '审核通过（部分撤销），' . $gd;
			} else {
				//审核通过
				return '审核通过，' . $gd;
			}
		} else if (intval($isok) === 2) {
			//审核驳回
			return '审核驳回';
		} else if (intval($isok) === -1) {
			//审核撤销
			return '已撤销';
		} else if (intval($isok) === 3) {
			//申请撤销
			return '申请撤销';
		}
		return '';
	}

	private function _get_status_arrays($apply_id) {
		$cancels = array();
		$waitings = array();
		$auditpass = array();
		$auditreject = array();
		$count = 0;
		$results = $this->db
				->get_results(
						'SELECT id,isok FROM finance_payment_person_deposit_apply_list WHERE apply_id='
								. intval($apply_id));
		if ($results !== NULL) {
			$count = count($results);
			foreach ($results as $result) {
				switch (intval($result->isok)) {
				case 0:
					$waitings[] = $result->id;
					break;
				case 1:
					$auditpass[] = $result->id;
					break;
				case 2:
					$auditreject[] = $result->id;
					break;
				case -1:
					$cancels[] = $result->id;
					break;
				}
			}
		}
		return array('count' => $count, 'waitings' => $waitings,
				'auditpass' => $auditpass, 'auditreject' => $auditreject,
				'cancels' => $cancels);
	}

	private function _get_person_payment_manager_action($apply_id, $isok, $isgd) {
		$status = $this->_get_status_arrays($apply_id);

		//开始判断
		if (intval($isok) === 0) {
			if ($status['count'] === count($status['waitings'])) {
				//未开始审核
				return '<a href="' . BASE_URL
						. 'finance/payment/?o=person_deposit_apply_manager_audit&id='
						. intval($apply_id) . '">审核</a>';
			} else if (count($status['waitings']) > 0
					&& count($status['waitings']) < $status['count']) {
				//审核中
				return '<a href="' . BASE_URL
						. 'finance/payment/?o=person_deposit_apply_manager_audit&id='
						. intval($apply_id) . '">审核</a>';
			}
		} else if (intval($isok) === 1) {
			if (intval($isgd) === 1) {
				return '<a href="' . BASE_URL . 'finance/payment/?o=print&id='
						. intval($apply_id) . '&type=pd">打印</a>';
			} else {
				if (count($status['auditpass']) + count($status['cancels'])
						=== $status['count']) {
					//审核通过，但有部分撤销
					return '<a href="' . BASE_URL
							. 'finance/payment/?o=person_deposit_apply_manager_gd&id='
							. intval($apply_id)
							. '">归档</a>&nbsp;|&nbsp;<a href="' . BASE_URL
							. 'finance/payment/?o=print&id='
							. intval($apply_id) . '&type=pd">打印</a>';
				} else {
					//审核通过
					return '<a href="' . BASE_URL
							. 'finance/payment/?o=person_deposit_apply_manager_gd&id='
							. intval($apply_id)
							. '">归档</a>&nbsp;|&nbsp;<a href="' . BASE_URL
							. 'finance/payment/?o=print&id='
							. intval($apply_id) . '&type=pd">打印</a>';
				}
			}
		} else if (intval($isok) === 2) {
			//审核驳回
			return '';
		} else if (intval($isok) === -1) {
			//审核撤销
			return '';
		} else if (intval($isok) === 3) {
			//发起撤销
			return '<a href="' . BASE_URL
					. 'finance/payment/?o=person_deposit_apply_manager_cancel&id='
					. intval($apply_id) . '">审核撤销</a>';
		}
		return '';
	}

	private function _get_payment_person_deposit_apply_list() {
		$datas = $this->_get_manager_list();
		$result = '';
		if (!empty($datas)) {
			foreach ($datas as $data) {
				$result .= '<tr><td>' . $data['depname'] . '</td><td>'
						. $data['addtime'] . '</td><td>'
						. $data['payment_date'] . '</td><td>'
						. $data['media_name'] . '</td><td>'
						. $data['payment_amount_plan'] . '</td><td>'
						. $data['payment_amount_real'] . '</td><td>'
						. $data['realname'] . '（' . $data['username']
						. '）</td><td>'
						. $this
								->_get_person_payment_manager_status(
										$data['id'], $data['isok'],
										$data['is_gd']) . '</td><td>'
						. $this
								->_get_person_payment_manager_action(
										$data['id'], $data['isok'],
										$data['is_gd']) . '</td></tr>';
			}
		} else {
			$result .= '<tr><td colspan="9"><font color="red"><b>没有相关数据！</b></font></td></tr>';
		}
		return $result;
	}

	private function _get_apply_counts() {
		return $this->page . '	/' . $this->page_count . ' 页 &nbsp;&nbsp;';
	}

	public function get_payment_person_deposit_apply_list_html() {
		if (in_array($this->getUsername(),
				$GLOBALS['manager_finance_permission'], TRUE)
				|| intval($this->getBelong_dep()) === 2) {
			$buf = file_get_contents(
					TEMPLATE_PATH
							. 'finance/payment/payment_person_deposit_list.tpl');
			return str_replace(
					array('[LEFT]', '[TOP]', '[PAYMENTLIST]', '[ALLCOUNTS]',
							'[COUNTS]', '[PREV]', '[NEXT]', '[VCODE]',
							'[BASE_URL]'),
					array($this->get_left_html(), $this->get_top_html(),
							$this->_get_payment_person_deposit_apply_list(),
							$this->all_count, $this->_get_apply_counts(),
							self::_getPrev($this->page, FALSE),
							self::_getNext($this->page, $this->page_count,
									FALSE), $this->get_vcode(), BASE_URL),
					$buf);
		}
		return User::no_permission();
	}

	function get_edit_payment_deposit_apply_html() {
		$id = Security_Util::my_get('id');
		if (self::validate_id(intval($id))) {
			$row = $this->db
					->get_row(
							'SELECT media_info_id,payment_amount_plan,payment_date,is_nim_pay_first,is_rebate_deduction,rebate_amount,is_deposit_deduction,is_person_loan_deduction,person_loan_amount,is_contract_deduction,remark,user FROM finance_payment_person_deposit_apply WHERE id='
									. intval($id) . ' AND user='
									. $this->getUid());
			if ($row === NULL) {
				User::no_object('没有该付款申请或非创建者');
			} else {
				$media_name = '';
				$account_bank = '';
				$account = '';
				$media = $this->db
						->get_row(
								'SELECT media_name,account_bank,account FROM finance_payment_media_info WHERE id='
										. intval($row->media_info_id)
										. ' AND isok=1');
				if ($media !== NULL) {
					$media_name = $media->media_name;
					$account_bank = $media->account_bank;
					$account = $media->account;
				}

				$apply_list = $this->db
						->get_results(
								'SELECT cid,media_name,media_category,payment_amount,rebate_deduction_amount,rebate_deduction_dids,person_loan_user,person_loan_amount,is_nim_pay_first,nim_pay_first_amount,nim_pay_first_dids FROM finance_payment_person_deposit_apply_list WHERE apply_id='
										. intval($id));
				$cids = array();
				if ($apply_list !== NULL) {
					foreach ($apply_list as $app) {
						$cids[] = $app->cid . '-_-!' . $app->media_name
								. '-_-!' . $app->media_category;
					}
					$cids = ',' . implode(',', $cids) . ',';
				} else {
					$cids = ',';
				}

				$payment_amount_plan = empty($row->payment_amount_plan) ? 0
						: $row->payment_amount_plan;
				$rebate_amount = intval($row->is_rebate_deduction) === 1 ? (empty(
								$row->rebate_amount) ? 0 : $row->rebate_amount)
						: 0;
				$person_loan_amount = intval($row->is_person_loan_deduction)
						=== 1 ? (empty($row->person_loan_amount) ? 0
								: $row->person_loan_amount) : 0;
				$deposit_amount = 0;

				//查找保证金抵扣
				$deposit_deductiuon = array();
				if (intval($row->is_deposit_deduction) === 1) {
					$results = $this->db
							->get_results(
									'SELECT deposit_gd_id,apply_type FROM finance_payment_deposit_deduction WHERE apply_id='
											. intval($id)
											. ' AND isok=1 AND payment_type=2 AND apply_type=1');
					if ($results !== NULL) {
						foreach ($results as $result) {
							$deposit_deductiuon[] = $result->deposit_gd_id
									. '_'
									. (intval($result->apply_type) === 1 ? 'p'
											: 'm');
						}
					}
				}
				$deposit_deductiuon = empty($deposit_deductiuon) ? ''
						: ',' . implode(',', $deposit_deductiuon) . ',';

				$buf = file_get_contents(
						TEMPLATE_PATH
								. 'finance/payment/payment_person_apply_deposit_edit.tpl');
				return str_replace(
						array('[LEFT]', '[TOP]', '[BANKLIST]', '[ACCOUNTLIST]',
								'[PAYMENTAMOUNTPLAN]', '[PAYMENTDATE]',
								'[ISNIMPAYFIRST]', '[ISREBATEDEDUCTION]',
								'[REBATEAMOUNT]', '[REBATERATE]',
								'[ISDEPOSITDEDUCTION]',
								'[ISPERSONLOANDEDUCTION]',
								'[PERSONLOANAMOUNT]', '[ISCONTRACTDEDUCTION]',
								'[REMARK]', '[PIDS]', '[APPLYID]',
								'[PAYMENTREAL]', '[VCODE]', '[PROCESSLIST]',
								'[DEPOSITDEDUCTION]', '[MEDIANAME]',
								'[BASE_URL]'),
						array($this->get_left_html(), $this->get_top_html(),
								Payment_Media_Info::get_bank_list($media_name,
										$account_bank),
								Payment_Media_Info::get_bank_acount_list(
										$media_name, $account_bank, $account),
								$row->payment_amount_plan, $row->payment_date,
								(intval($row->is_nim_pay_first) === 0 ? ''
										: 'checked'),
								(intval($row->is_rebate_deduction) === 0 ? ''
										: 'checked'), $row->rebate_amount,
								$row->rebate_rate,
								(intval($row->is_deposit_deduction) === 0 ? ''
										: 'checked'),
								(intval($row->is_person_loan_deduction) === 0 ? ''
										: 'checked'), $row->person_loan_amount,
								(intval($row->is_contract_deduction) === 0 ? ''
										: 'checked'), $row->remark, $cids, $id,
								sprintf("%.2f",
										($payment_amount_plan - $rebate_amount
												- $person_loan_amount
												- $deposit_amount)),
								$this->get_vcode(), $this->_get_process_list(),
								$deposit_deductiuon, $media_name, BASE_URL),
						$buf);
			}
		} else {
			User::no_object('没有该付款申请');
		}
	}

	public function cancel_deposit_payment_apply() {
		if ($this->validate_form_value('cancel')) {
			$success = TRUE;
			$error = '';
			$this->db->query('BEGIN');

			//判断是否是自己创建的
			$row = $this->db
					->get_row(
							'SELECT id'
									. ($this->type === 'temp' ? ''
											: ',isok,payment_id')
									. ' FROM finance_payment_person_deposit_apply WHERE id='
									. intval($this->id) . ' AND user='
									. $this->getUid() . ' FOR UPDATE');
			if ($row === NULL) {
				$success = FALSE;
				$error = '该付款申请不存在或者非自己创建';
			}

			$r = 1;

			if ($success) {
				//if ($this->type === 'temp') {
				//作废草稿，直接删除记录
				//	$delete_result = $this->db
				//			->query(
				//					'DELETE FROM finance_payment_rebate_temp WHERE apply_id='
				//								. intval($this->id));
				//		if ($delete_result === FALSE) {
				//			$success = FALSE;
				//			$error = '作废付款申请草稿失败，错误代码1';
				//		}

				//		if ($success) {
				//			$delete_result = $this->db
				//					->query(
				//							'DELETE FROM finance_payment_payfirst_temp WHERE apply_id='
				//									. intval($this->id));
				//			if ($delete_result === FALSE) {
				//				$success = FALSE;
				//				$error = '作废付款申请草稿失败，错误代码2';
				//			}
				//		}

				//	if ($success) {
				//		$delete_result = $this->db
				//			->query(
				//						'DELETE FROM finance_payment_person_apply_list_temp WHERE apply_id='
				//							. intval($this->id));
				//	if ($delete_result === FALSE) {
				//		$success = FALSE;
				//			$error = '作废付款申请草稿失败，错误代码3';
				//			}
				//}

				//	if ($success) {
				//		$delete_result = $this->db
				//				->query(
				//						'DELETE FROM finance_payment_person_apply_temp WHERE id='
				//								. intval($this->id));
				//	if ($delete_result === FALSE) {
				//			$success = FALSE;
				//			$error = '作废付款申请草稿失败，错误代码4';
				//		}
				//	}
				//} else {

				//作废正式申请，如果未审核状态，可设置标志位-1，不然需记录作废申请，由linda审核通过
				$status = $this->_get_status_arrays($this->id);
				$isok = intval($row->isok);
				if ($isok === -1) {
					//已作废
					$success = FALSE;
					$error = '付款申请已作废，不可再次作废';
				} else if ($isok === 0
						&& $status['count'] === count($status['waitings'])) {
					$update_result = $this->db
							->query(
									'UPDATE finance_payment_person_deposit_apply SET isok=-1 WHERE id='
											. intval($this->id));
					if ($update_result === FALSE) {
						$success = FALSE;
						$error = '作废付款申请失败，错误代码1';
					} else {
						$update_result = $this->db
								->query(
										'UPDATE finance_payment_person_deposit_apply_list SET isok=-1 WHERE apply_id='
												. intval($this->id));
						if ($update_result === FALSE) {
							$success = FALSE;
							$error = '作废付款申请失败，错误代码2';
						}

						if ($success) {
							$update_result = $this->db
									->query(
											'UPDATE finance_payment_payfirst SET status=-1 WHERE apply_id='
													. intval($this->id)
													. ' AND amount_type=2 AND payment_type=1');
							if ($update_result === FALSE) {
								$success = FALSE;
								$error = '作废付款申请失败，错误代码3';
							}
						}

						if ($success) {
							$update_result = $this->db
									->query(
											'UPDATE finance_payment_rebate SET status=-1 WHERE apply_id='
													. intval($this->id)
													. ' AND amount_type=2 AND payment_type=1');
							if ($update_result === FALSE) {
								$success = FALSE;
								$error = '作废付款申请失败，错误代码4';
							}
						}
					}

					if ($success) {
						$r = 2;
						$type = '<font color=\'#99cc00\'>作废付款申请</font>';
					}
				} else {
					//记录作废申请 finance_payment_cancel_log
					$update_result = $this->db
							->query(
									'UPDATE finance_payment_person_deposit_apply SET isok=3 WHERE id='
											. intval($this->id));
					if ($update_result === FALSE) {
						$success = FALSE;
						$error = '作废付款申请失败，错误代码3';
					} else {
						$insert_result = $this->db
								->query(
										'INSERT INTO finance_payment_cancel_log(apply_id,cancel_apply_time,answer,payment_type,amount_type) VALUE('
												. intval($this->id)
												. ',now(),0,1,2)');
						if ($insert_result === FALSE) {
							$success = FALSE;
							$error = '作废付款申请失败，错误代码4';
						}
					}

					if ($success) {
						$r = 3;
						$type = '<font color=\'#99cc00\'>作废付款申请，等待审核</font>';
					}
				}
				//}

				if ($success && $this->type !== 'temp') {
					$result = $this->_log($row->payment_id, '发起人', $type);
					if ($result === FALSE) {
						$success = FALSE;
						$error = '作废付款申请失败，错误代码5';
					}
				}
			}

			if ($success) {
				$this->db->query('COMMIT');
			} else {
				$this->db->query('ROLLBACK');
			}
			return $success ? $r : $error;
		}
		return implode("\n", $this->errors);
	}

	public static function isYesOrNo($val) {
		return intval($val) === 1 ? '是' : '否';
	}

	private static function _isAuditHtml($audit = TRUE) {
		if ($audit) {
			return '<th data-options="field:\'w\',width:450" rowspan="2" >审核</th>';
		}
		return '';
	}

	private static function _isCKHtml($isck = TRUE) {
		if ($isck) {
			return '<th data-options="field:\'y\',checkbox:true" rowspan="2" ></th><th data-options="field:\'z\',width:150" rowspan="2" >归档金额</th>';
		}
		return '';
	}

	private function _get_payment_person_info($id, $isAudit = TRUE,
			$isCK = FALSE) {
		$info = '';
		$row = $this->db
				->get_row(
						'SELECT a.payment_amount_plan,a.payment_date,a.is_nim_pay_first,a.is_rebate_deduction,a.rebate_amount,a.is_deposit_deduction,a.is_person_loan_deduction,a.person_loan_amount,a.remark,a.isok,a.payment_amount_real,a.payment_id,a.is_contract_deduction,b.media_name,b.account_bank,b.account FROM finance_payment_person_deposit_apply a LEFT JOIN finance_payment_media_info b ON a.media_info_id=b.id WHERE a.id='
								. intval($id));
		if ($row !== NULL) {
			if (intval($row->isok) === 0 && $isAudit
					|| intval($row->isok) === 1 && !$isAudit) {
				$info = str_replace(
						array('[MEDIANAME]', '[BANKNAME]', '[ACCOUNTNAME]',
								'[PAYMENTAMOUNTPLAN]', '[PAYMENTDATE]',
								'[REBATEAMOUNT]', '[PERSONLOANAMOUNT]',
								'[PAYMENTREAL]', '[REMARK]', '[ISNIMPAYFIRST]',
								'[ISREBATEDEDUCTION]', '[ISDEPOSITDEDUCTION]',
								'[ISPERSONLOANDEDUCTION]', '[ISAUDIT]',
								'[ISCK]', '[PAYMENTID]',
								'[ISCONTRACTDEDUCTION]'),
						array($row->media_name, $row->account_bank,
								$row->account, $row->payment_amount_plan,
								$row->payment_date, $row->rebate_amount,
								$row->person_loan_amount,
								$row->payment_amount_real,
								Format_Util::format_html($row->remark),
								self::isYesOrNo($row->is_nim_pay_first),
								self::isYesOrNo($row->is_rebate_deduction),
								self::isYesOrNo($row->is_deposit_deduction),
								self::isYesOrNo($row->is_person_loan_deduction),
								self::_isAuditHtml($isAudit),
								self::_isCKHtml($isCK), $row->payment_id,
								self::isYesOrNo($row->is_contract_deduction)),
						file_get_contents(
								TEMPLATE_PATH
										. 'finance/payment/payment_person_deposit_info.tpl'));
				return $info;
			} else {
				User::no_permission();
			}
		} else {
			User::no_object('没有该个人付款申请');
		}

	}

	public function get_person_deposit_apply_manager_audit_html() {
		$id = Security_Util::my_get('id');

		$row = $this->db
				->get_row(
						'SELECT is_deposit_deduction FROM finance_payment_person_deposit_apply WHERE id='
								. intval($id));
		if ($row !== NULL) {
			//查找保证金抵扣
			$deposit_deductiuon = array();
			if (intval($row->is_deposit_deduction) === 1) {
				$results = $this->db
						->get_results(
								'SELECT deposit_gd_id,apply_type FROM finance_payment_deposit_deduction WHERE apply_id='
										. intval($id)
										. ' AND isok=1 AND payment_type=1 AND apply_type=1');
				if ($results !== NULL) {
					foreach ($results as $result) {
						$deposit_deductiuon[] = $result->deposit_gd_id . '_'
								. (intval($result->apply_type) === 1 ? 'p' : 'm');
					}
				}
			}
			$deposit_deductiuon = empty($deposit_deductiuon) ? ''
					: ',' . implode(',', $deposit_deductiuon) . ',';
			$buf = file_get_contents(
					TEMPLATE_PATH
							. 'finance/payment/payment_person_deposit_apply_audit.tpl');
			return str_replace(
					array('[LEFT]', '[TOP]', '[VCODE]', '[PAYMENTPERSONINFO]',
							'[APPLYID]', '[DEPOSITDEDUCTION]', '[BASE_URL]'),
					array($this->get_left_html(), $this->get_top_html(),
							$this->get_vcode(),
							$this->_get_payment_person_info($id), $id,
							$deposit_deductiuon, BASE_URL), $buf);
		}
		return User::no_object('没有该付款申请');
	}

	public function audit_item() {
		if ($this->validate_form_value('audit_item')) {
			$success = TRUE;
			$error = '';
			$this->db->query('BEGIN');

			$row = $this->db
					->get_row(
							'SELECT step,payment_id FROM finance_payment_person_deposit_apply WHERE id='
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
									'UPDATE finance_payment_person_deposit_apply_list SET isok='
											. intval($this->auditsel)
											. ',remark="' . $this->auditresaon
											. '" WHERE id='
											. intval($this->listid));
					if ($update_result === FALSE) {
						$success = FALSE;
						$error = '审核个人付款申请失败，错误代码1';
					} else {
						$results = $this->db
								->get_results(
										'SELECT isok FROM finance_payment_person_deposit_apply_list WHERE apply_id='
												. intval($this->id));
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
									$sql = 'UPDATE finance_payment_person_deposit_apply SET step=step+1'
											. (intval($row->step) + 1
													=== count(
															$this->process_step)
															- 1 ? ',isok=1' : '')
											. ' WHERE id=' . intval($this->id);
									$type = '<font color=\'#99cc00\'>'
											. $auditname . ' 确认</font>';
								} else if (intval($this->auditsel) === 2) {
									$sql = 'UPDATE finance_payment_person_deposit_apply SET step=0,isok=2 WHERE id='
											. intval($this->id);
									$type = '<font color=\'#ff9900\'>驳回至 发起人</font>';
								}
								$update_result = $this->db->query($sql);
								if ($update_result === FALSE) {
									$success = FALSE;
									$error = '审核个人付款申请失败，错误代码2';
								}
							} else {
								$type = '<font color=\'#99cc00\'>' . $auditname
										. (intval($this->auditsel) === 1 ? '确认'
												: '驳回') . ' 条目</font>';
							}
						} else {
							$success = FALSE;
							$error = '审核个人付款申请失败，错误代码3';
						}
						if ($success) {
							$update_result = $this
									->_log($row->payment_id, $auditname, $type,
											'');
							if ($update_result === FALSE) {
								$success = FALSE;
								$error = '审核个人付款申请记录日志失败';
							}
						}
					}
				} else {
					$success = FALSE;
					$error = NO_RIGHT_TO_DO_THIS;
				}

			} else {
				$success = FALSE;
				$error = '没有该个人付款申请';
			}

			if ($success) {
				$this->db->query('COMMIT');
			} else {
				$this->db->query('ROLLBACK');
			}
			return array('status' => $success ? 'success' : 'error',
					'message' => $success ? '审核个人付款申请成功' : $error);
		}

		return array('status' => 'error', 'message' => $this->errors);
	}

	public function audit_full_payment_person_deposit() {
		if ($this->validate_form_value('audit_fullpayment_person')) {
			$success = TRUE;
			$error = '';
			$this->db->query('BEGIN');

			$row = $this->db
					->get_row(
							'SELECT step,payment_id FROM finance_payment_person_deposit_apply WHERE id='
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

					if ($this->auditvalue === 'pass') {
						$sql = 'UPDATE finance_payment_person_deposit_apply SET step=step+1'
								. (intval($row->step) + 1
										=== (count($this->process_step) - 1) ? ',isok=1'
										: '') . ' WHERE id='
								. intval($this->id);
					} else if ($this->auditvalue === 'reject') {
						$sql = 'UPDATE finance_payment_person_deposit_apply SET step=0,isok=2 WHERE id='
								. intval($this->id);
					}
					//var_dump($sql);
					$update_result = $this->db->query($sql);
					if ($update_result === FALSE) {
						$success = FALSE;
						$error = '审核个人付款申请失败，错误代码1';
					} else {
						if ($this->auditvalue === 'pass'
								&& intval($row->step) + 1
										=== (count($this->process_step) - 1)) {
							$sql = 'UPDATE finance_payment_person_deposit_apply_list SET isok=1 WHERE apply_id='
									. intval($this->id) . ' AND isok=0';
						} else if ($this->auditvalue === 'reject') {
							$sql = 'UPDATE finance_payment_person_deposit_apply_list SET isok=2 WHERE apply_id='
									. intval($this->id) . ' AND isok=0';
							;
						} else {
							$sql = '';
						}
						//var_dump($sql);
						if (!empty($sql)) {
							$update_result = $this->db->query($sql);
							if ($update_result === FALSE) {
								$success = FALSE;
								$error = '审核个人付款申请失败，错误代码2';
							}
						}

						if ($success) {
							if ($this->auditvalue === 'pass') {
								$type = '<font color=\'#99cc00\'>' . $auditname
										. ' 确认</font>';
							} else if ($this->auditvalue === 'reject') {
								$type = '<font color=\'#ff9900\'>驳回至 发起人</font>';
							}
							$update_result = $this
									->_log($row->payment_id, $auditname, $type,
											$this->remark);
							if ($update_result === FALSE) {
								$success = FALSE;
								$error = '驳回个人付款申请记录日志失败';
							}
						}
					}
				} else {
					$success = FALSE;
					$error = NO_RIGHT_TO_DO_THIS;
				}

			} else {
				$success = FALSE;
				$error = '没有该个人付款申请';
			}

			if ($success) {
				$this->db->query('COMMIT');
			} else {
				$this->db->query('ROLLBACK');
			}
			return array('status' => $success ? 'success' : 'error',
					'message' => $success ? '审核个人付款申请成功' : $error);
		}

		return array('status' => 'error', 'message' => $this->errors);
	}

	public function get_person_deposit_apply_manager_gd_html() {
		if (in_array($this->getUsername(),
				$GLOBALS['manager_finance_permission'], TRUE)
				|| intval($this->getBelong_dep()) === 2) {
			$id = Security_Util::my_get('id');
			$buf = file_get_contents(
					TEMPLATE_PATH
							. 'finance/payment/payment_person_deposit_apply_gd.tpl');
			return str_replace(
					array('[LEFT]', '[TOP]', '[VCODE]', '[PAYMENTPERSONINFO]',
							'[NIMBANKS]', '[APPLYID]', '[BASE_URL]'),
					array($this->get_left_html(), $this->get_top_html(),
							$this->get_vcode(),
							$this->_get_payment_person_info($id, FALSE, TRUE),
							Nim_BankInfo::get_bank_account_list(), $id,
							BASE_URL), $buf);
		}
		return User::no_permission();
	}

	public function payment_person_deposit_gd() {
		if ($this->validate_form_value('payment_person_deposit_gd')) {
			$success = TRUE;
			$error = '';
			$this->db->query('BEGIN');

			$gdvalues_array = $this->gdvalues_array;
			foreach ($gdvalues_array as $gd) {
				$item = explode('_', $gd['item']);
				$insert_result = $this->db
						->query(
								'INSERT INTO finance_payment_deposit_gd(apply_id,payment_id,list_id,cid,media_name,media_category,gd_time,gd_amount,payment_type,payment_bank,isok,apply_type) SELECT '
										. $this->id . ',"' . $this->payment_id
										. '",' . $item[2] . ',"' . $item[0]
										. '",media_name,media_category,"'
										. $this->gdpaymentdate . '",'
										. $gd['gdamount'] . ','
										. $this->gdpaymenttype . ','
										. ($this->gdpaymentbank === '' ? 'NULL'
												: $this->gdpaymentbank)
										. ',1,1 FROM finance_payment_person_deposit_apply_list WHERE id='
										. $item[2]);

				if ($insert_result === FALSE) {
					$success = FALSE;
					$error = '个人付款申请归档失败，错误代码1';
					break;
				}
			}

			if ($success) {
				//查看是否都已归档
				$row = $this->db
						->get_row(
								'SELECT a.gd_amount_done,a.payment_id,a.apply_id,a.apply_type,b.payment_amount_real
FROM
(
SELECT SUM(gd_amount) AS gd_amount_done,payment_id,apply_id,apply_type FROM finance_payment_deposit_gd WHERE apply_id=4 AND apply_type=1 AND isok=1
) a
LEFT JOIN finance_payment_person_deposit_apply b
ON a.apply_id=b.id');
				if ($row !== NULL) {
					if (doubleval($row->gd_amount_done)
							=== doubleval($row->payment_amount_real)) {
						$update_result = $this->db
								->query(
										'UPDATE finance_payment_person_deposit_apply SET is_gd=1 WHERE id='
												. intval($this->id));
						if ($update_result === FALSE) {
							$success = FALSE;
							$error = '个人付款申请归档失败，错误代码2';
						}
					}
				}
			}

			if ($success) {
				$insert_result = $this
						->_log($this->payment_id, $this->getRealname(),
								'<font color=\'#99cc00\'>归档</font>');
				if ($insert_result === FALSE) {
					$success = FALSE;
					$error = '个人付款申请归档记录日志失败';
				}
			}

			if ($success) {
				$this->db->query('COMMIT');
			} else {
				$this->db->query('ROLLBACK');
			}
			return array('status' => $success ? 'success' : 'error',
					'message' => $success ? '归档个人付款申请成功' : $error);
		}
		return array('status' => 'error', 'message' => $this->errors);
	}
}
