<?php
class Payment_Person_Apply extends User {
	private $page;
	private $search;
	private $cusname;
	private $projectname;
	private $medianame;

	private $media_name;
	private $bank_name;
	private $bank_name_select;
	private $bank_account;
	private $bank_account_select;
	private $payment_amount_plan;
	private $payment_date;
	private $is_nim_pay_first;
	private $nim_pay_first_amount;
	private $is_rebate_deduction;
	private $rebate_amount;
	private $rebate_rate;
	private $is_deposit_deduction;
	private $is_person_loan_deduction;
	private $person_loan_amount;
	private $remark;

	private $payment_pids = array();
	private $errors = array();

	private $payment_list = array();
	private $deposit_list = array();
	private $action;

	private $type;
	private $id;

	const EXE_LIMIT = 10;

	private $process_id;
	private $process_deps;
	private $process_step;

	private $all_count;
	private $page_count;
	const LIMIT = 50;

	private $auditvalue;
	private $listid;
	private $auditsel;
	private $auditresaon;

	private $gdvalues_array;
	private $gdpaymentdate;
	private $gdpaymentamount;
	private $gdpaymenttype;
	private $gdpaymentbank;
	private $payment_id;

	private $cusname_number;
	private $pid;

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
			if ($finance_process['name'] === '个人付款申请流程') {
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
							. $content . '</span><label>个人付款申请流程</label></li>';
					$i++;
				}
			}
		}
		return $result;
	}

	public function get_payment_person_apply_html() {
		$buf = file_get_contents(
				TEMPLATE_PATH . 'finance/payment/payment_person_apply.tpl');
		return str_replace(
				array('[LEFT]', '[TOP]', '[PROCESSLIST]', '[VCODE]',
						'[BASE_URL]'),
				array($this->get_left_html(), $this->get_top_html(),
						$this->_get_process_list(), $this->get_vcode(),
						BASE_URL), $buf);
	}

	private function _getReceiveAmount($pid = NULL) {
		$resuts = $this->db
				->get_results(
						'SELECT SUM(amount) AS receive_amount,pid FROM finance_receivables WHERE 1=1 '
								. ($pid !== NULL ? ' AND pid="' . $pid . '"'
										: '') . ' AND  isok<>-1'
								. ($pid === NULL ? ' GROUP BY pid' : ''));
		$datas = array();
		if ($resuts !== NULL) {
			foreach ($resuts as $result) {
				$datas[$result->pid] = $result->receive_amount;
			}
		}
		return $datas;
	}

	private function _getReveiveInvoice($pid = NULL) {
		$results = $this->db
				->get_results(
						'SELECT SUM(sum_amount) AS receive_invoice,pid FROM finance_receiveinvoice_pid_list WHERE '
								. ($pid !== NULL ? ' pid="' . $pid . '" AND '
										: '') . ' isok=1'
								. ($pid === NULL ? ' GROUP BY pid' : ''));
		$datas = array();
		if ($results !== NULL) {
			foreach ($results as $result) {
				$datas[$result->pid] = $result->receive_invoice;
			}
		}
		return $datas;
	}

	private function _getGDAmountByPaycostid() {
		$results = $this->db
				->get_results(
						'SELECT SUM(gd_amount) AS gd_amount,paycostid FROM finance_payment_gd GROUP BY paycostid');
		$datas = array();
		if ($results !== NULL) {
			foreach ($results as $result) {
				$datas[$result->paycostid] = $result->gd_amount;
			}
		}
		return $datas;
	}

	public function get_search_pid_payment_html() {
		$s = '<table width="100%" class="sbd1"><tr><td>执行单号</td><td>客户名称</td><td>媒体名称</td><td>执行成本</td><td>收票情况</td><td>付款情况</td><td>已付款未到票</td><td>最后付款时间</td><td></td></tr>';
		$results = array();
		$exes = $this->db
				->get_results(
						'SELECT m.*,n.cid,n.allcost,n.costpaymentinfoids,o.cusname,x.payment
FROM 
(
SELECT a.*
FROM(
SELECT pid,gd_time FROM finance_payment_gd '
								. (!empty($this->pid) ? ' WHERE pid LIKE "%'
												. $this->pid . '%"' : '')
								. ' ORDER BY pid,gd_time DESC
) a GROUP BY a.pid
) m
LEFT JOIN v_last_executive n
ON m.pid=n.pid 
LEFT JOIN contract_cus o
ON n.cid=o.cid
LEFT JOIN 
(
SELECT SUM(gd_amount) AS payment,pid FROM finance_payment_gd GROUP BY pid
) x
ON m.pid=x.pid'
								. (!empty($this->cusname) ? ' WHERE o.cusname LIKE "%'
												. $this->cusname . '%"' : ''));

		$gd_amount = $this->_getGDAmountByPaycostid();

		foreach ($exes as $val) {
			$costpaymentinfoids = $val->costpaymentinfoids;
			if (!empty($costpaymentinfoids)) {
				$cost_array = array();
				$sum_cost = 0;
				$costpaymentinfoids = explode('^', $costpaymentinfoids);
				$costpaymentinfoids = Array_Util::my_remove_array_other_value(
						$costpaymentinfoids, array(''));

				$costpays = $this->db
						->get_results(
								'SELECT id,payname,payamount FROM executive_paycost WHERE id IN ('
										. implode(',', $costpaymentinfoids)
										. ')');
				if ($costpays !== NULL) {
					foreach ($costpays as $costpay) {
						if ($this->medianame !== NULL
								&& $this->medianame !== '') {
							if (strpos($costpay->payname, $this->medianame)
									!== FALSE) {
								$cost_array[] = array('id' => $costpay->id,
										'payname' => $costpay->payname,
										'payamount' => $costpay->payamount,
										'gd_amount' => empty(
												$gd_amount[$costpay->id]) ? 0
												: $gd_amount[$costpay->id]);
								$sum_cost += $costpay->payamount;
							}
						} else {
							$cost_array[] = array('id' => $costpay->id,
									'payname' => $costpay->payname,
									'payamount' => $costpay->payamount,
									'gd_amount' => empty(
											$gd_amount[$costpay->id]) ? 0
											: $gd_amount[$costpay->id]);
						}
					}
					if ($this->medianame !== NULL && $this->medianame !== '') {
						if (!empty($cost_array)) {
							$results[] = array('pid' => $val->pid,
									'allcost' => $sum_cost,
									'cusname' => $val->cusname,
									'payment' => $val->payment,
									'cost_array' => $cost_array,
									'last_gd_time' => $val->gd_time);
						}
					} else {
						$results[] = array('pid' => $val->pid,
								'allcost' => $val->allcost,
								'cusname' => $val->cusname,
								'payment' => $val->payment,
								'cost_array' => $cost_array,
								'last_gd_time' => $val->gd_time);
					}
				}
			}
		}

		if (!empty($results)) {
			$page_count = ceil(count($results) / self::EXE_LIMIT);
			$start = self::EXE_LIMIT * intval($this->page) - self::EXE_LIMIT;
			if ($start < 0) {
				$start = 0;
			}

			$results = array_slice($results, $start, self::EXE_LIMIT);

			//已收票
			$receive_invoice = $this->_getReveiveInvoice();

			foreach ($results as $result) {
				$now_receive_invoice = !empty($receive_invoice[$result['pid']]) ? $receive_invoice[$result['pid']]
						: 0;
				$s .= '<tr><td id="pid_' . $result['pid'] . '">'
						. $result['pid'] . '</td><td id="cus_' . $result['pid']
						. '">' . $result['cusname']
						. '</td><td></td>
						<td id="cost_' . $result['pid']
						. '" style="color:#ff9933;font-weight:bold;">'
						. Format_Util::my_money_format('%.2n',
								$result['allcost']) . '</td><td>'
						. $now_receive_invoice . '</td><td id="payment_'
						. $result['pid']
						. '"  style="color:green;font-weight:bold;">'
						. Format_Util::my_money_format('%.2n',
								$result['payment'])
						. '</td><td  style="color:red;font-weight:bold;">'
						. Format_Util::my_money_format('%.2n',
								$result['payment'] - $now_receive_invoice)
						. '</td><td>' . $result['last_gd_time']
						. '</td><td><input type="button" value="展开" class="btn" onclick="javascript:openit(\''
						. $result['pid'] . '\')"></td></tr>';
				$ss = '';
				foreach ($result['cost_array'] as $ca) {
					$ss .= '<tr><td width="5%"><input type="checkbox" value="'
							. $result['pid'] . '_' . $ca['id']
							. '" name="abc"></td><td style="font-weight:bold;width:35%;">媒体名：'
							. $ca['payname']
							. '</td><td style="color:#ff9933;font-weight:bold;width:20%;">执行成本：'
							. $ca['payamount']
							. '</td><td style="color:green;font-weight:bold;width:20%;">已支付情况：'
							. $ca['gd_amount']
							. ' </td><td style="color:red;font-weight:bold;width:20%;">已执行未付款金额：'
							. ($ca['payamount'] - $ca['gd_amount'])
							. '</td></tr>';
				}
				$s .= '<tr id="tr_' . $result['pid']
						. '"><td colspan="9"><table width="100%">' . $ss
						. '</table></td></tr>';
			}
			$pageinfo = '<tr><td colspan="9"><div id="pageinfo">'
					. intval($this->page) . ' / ' . $page_count . ' 页 &nbsp;'
					. self::_getPrev($this->page) . '&nbsp;'
					. self::_getNext($this->page, $page_count)
					. '&nbsp; <input id="movepid" type="button" value="选 择" onclick="javascript:pidmove();" class="btn"/></div></td></tr>';
			$s .= $pageinfo;
		} else {
			$s .= '<tr><td colspan="9"><font color="red"><b>没有找到相关内容!</b></font></td></tr>';
		}

		$s .= '</table><script>$(\'[id^=tr_]\').hide();</script>';
		return $s;
	}

	private function _getAppliedAmount() {
		$datas = array();
		$results = $this->db
				->get_results(
						'SELECT SUM(amount) AS amount,pid,paycostid
FROM
(
SELECT SUM(payment_amount) AS amount,pid,paycostid FROM finance_payment_person_apply_list WHERE isok<>-1 GROUP BY pid,paycostid
UNION ALL
SELECT SUM(payment_amount) AS amount,pid,paycostid FROM finance_payment_media_apply_list WHERE isok<>-1 GROUP BY pid,paycostid
) a
GROUP BY pid,paycostid');
		if ($results !== NULL) {
			foreach ($results as $result) {
				$datas[$result->pid][$result->paycostid] = $result->amount;
			}
		}
		return $datas;
	}

	public function get_search_executive_html() {
		$s = '<table width="100%" class="sbd1"><tr><td style="font-weight:bold;">执行单号</td><td style="font-weight:bold;">客户名称</td><td style="font-weight:bold;">项目名称</td><td style="font-weight:bold;">客户到款情况</td><td style="font-weight:bold;">执行成本</td><td style="font-weight:bold;">已支付情况</td><td style="font-weight:bold;">已执行未付款金额</td><td></td></tr>';

		$exe_where = array('allcost>0');

		if ($this->search !== NULL && $this->search !== '') {
			$exe_where[] = 'pid LIKE "%' . $this->search . '%"';
		}
		if ($this->projectname !== NULL && $this->projectname !== '') {
			$exe_where[] = 'name LIKE "%' . $this->projectname . '%"';
		}

		$results = array();
		$exes = $this->db
				->get_results(
						'SELECT b.pid,b.allcost,b.name,b.costpaymentinfoids,c.cusname,x.payment 
FROM 
(
SELECT cid,pid,allcost,name,costpaymentinfoids FROM v_last_executive WHERE '
								. implode(' AND ', $exe_where)
								. '
) b
LEFT JOIN contract_cus c ON b.cid=c.cid 
LEFT JOIN (SELECT SUM(gd_amount) AS payment,pid FROM finance_payment_gd GROUP BY pid) x ON b.pid=x.pid'
								. (!empty($this->cusname) ? ' WHERE c.cusname LIKE "%'
												. $this->cusname . '%"' : ''));

		$gd_amount = $this->_getGDAmountByPaycostid();
		foreach ($exes as $val) {
			$costpaymentinfoids = $val->costpaymentinfoids;
			if (!empty($costpaymentinfoids)) {
				$cost_array = array();
				$sum_cost = 0;
				$costpaymentinfoids = explode('^', $costpaymentinfoids);
				$costpaymentinfoids = Array_Util::my_remove_array_other_value(
						$costpaymentinfoids, array(''));

				$costpays = $this->db
						->get_results(
								'SELECT id,payname,payamount FROM executive_paycost WHERE id IN ('
										. implode(',', $costpaymentinfoids)
										. ')');

				if ($costpays !== NULL) {
					foreach ($costpays as $costpay) {
						if ($this->medianame !== NULL
								&& $this->medianame !== '') {
							if (strpos($costpay->payname, $this->medianame)
									!== FALSE) {
								$cost_array[] = array('id' => $costpay->id,
										'payname' => $costpay->payname,
										'payamount' => $costpay->payamount,
										'gd_amount' => empty(
												$gd_amount[$costpay->id]) ? 0
												: $gd_amount[$costpay->id]);
								$sum_cost += $costpay->payamount;
							}
						} else {
							$cost_array[] = array('id' => $costpay->id,
									'payname' => $costpay->payname,
									'payamount' => $costpay->payamount,
									'gd_amount' => empty(
											$gd_amount[$costpay->id]) ? 0
											: $gd_amount[$costpay->id]);
						}
					}

					if ($this->medianame !== NULL && $this->medianame !== '') {
						if (!empty($cost_array)) {
							$results[] = array('pid' => $val->pid,
									'allcost' => $sum_cost,
									'name' => $val->name,
									'cusname' => $val->cusname,
									'payment' => $val->payment,
									'cost_array' => $cost_array);
						}
					} else {
						$results[] = array('pid' => $val->pid,
								'allcost' => $val->allcost,
								'name' => $val->name,
								'cusname' => $val->cusname,
								'payment' => $val->payment,
								'cost_array' => $cost_array);
					}
				}
			}
		}
		//var_dump($results);
		if (!empty($results)) {
			$page_count = ceil(count($results) / self::EXE_LIMIT);
			$start = self::EXE_LIMIT * intval($this->page) - self::EXE_LIMIT;
			if ($start < 0) {
				$start = 0;
			}

			$results = array_slice($results, $start, self::EXE_LIMIT);

			//已收款
			$receive_amount = $this->_getReceiveAmount();

			//已申请
			$applied_amount = $this->_getAppliedAmount();

			foreach ($results as $result) {
				$s .= '<tr><td id="pid_' . $result['pid'] . '">'
						. $result['pid'] . '</td><td id="cus_' . $result['pid']
						. '">' . $result['cusname'] . '</td><td>'
						. $result['name'] . '</td><td>'
						. (empty($receive_amount[$result['pid']]) ? 0
								: $receive_amount[$result['pid']])
						. '</td>
						<td id="cost_' . $result['pid']
						. '" style="color:#ff9933;font-weight:bold;">'
						. Format_Util::my_money_format('%.2n',
								$result['allcost']) . '</td><td id="payment_'
						. $result['pid']
						. '"  style="color:green;font-weight:bold;">'
						. Format_Util::my_money_format('%.2n',
								$result['payment'])
						. '</td><td  style="color:red;font-weight:bold;">'
						. Format_Util::my_money_format('%.2n',
								$result['allcost'] - $result['payment'])
						. '</td><td><input type="button" value="展开" class="btn" onclick="javascript:openit(\''
						. $result['pid'] . '\')"></td></tr>';
				if (!empty($result['cost_array'])) {
					$ss = '<tr><td width="5%"></td><td style="font-weight:bold;width:20%;">供应商名称</td><td style="font-weight:bold;width:22%;">执行成本</td><td style="font-weight:bold;width:18%;">已申请付款金额</td><td style="font-weight:bold;width:18%;">已支付情况</td><td style="font-weight:bold;width:20%;">已执行未付款金额</td></tr>';
					foreach ($result['cost_array'] as $ca) {
						$applied = empty(
								$applied_amount[$result['pid']][$ca['id']]) ? 0
								: $applied_amount[$result['pid']][$ca['id']];
						$ss .= '<tr><td width="5%"><input type="checkbox" value="'
								. $result['pid'] . '_' . $ca['id']
								. '" name="abc" '
								. '></td><td style="font-weight:bold;width:20%;">'
								. $ca['payname']
								. '</td><td style="color:#ff9933;font-weight:bold;width:20%;">'
								. Format_Util::my_money_format('%.2n',
										$ca['payamount'])
								. ($applied >= $ca['payamount'] ? '<p/><b><font color="red">已申请满额，无剩余可申请正数金额</font></b>'
										: '')
								. '</td><td style="color:green;font-weight:bold;width:20%;">'
								. Format_Util::my_money_format('%.2n', $applied)
								. '</td><td style="color:green;font-weight:bold;width:20%;">'
								. Format_Util::my_money_format('%.2n',
										$ca['gd_amount'])
								. ' </td><td style="color:red;font-weight:bold;width:20%;">'
								. Format_Util::my_money_format('%.2n',
										($ca['payamount'] - $ca['gd_amount']))
								. '</td></tr>';
					}
					$s .= '<tr id="tr_' . $result['pid']
							. '"><td colspan="8"><table width="100%">' . $ss
							. '</table></td></tr>';
				}
			}
			$pageinfo = '<tr><td colspan="8"><div id="pageinfo">'
					. intval($this->page) . ' / ' . $page_count . ' 页 &nbsp;'
					. self::_getPrev($this->page) . '&nbsp;'
					. self::_getNext($this->page, $page_count)
					. '&nbsp; <input id="movepid" type="button" value="选 择" onclick="javascript:pidmove();" class="btn" style="cursor:pointer"/></div></td></tr>';
			$s .= $pageinfo;
		} else {
			$s .= '<tr><td colspan="8"><font color="red"><b>没有找到相关内容!</b></font></td></tr>';
		}

		$s .= '</table><script>$(\'[id^=tr_]\').hide();</script>';
		return $s;
	}

	private static function _getPrev($page, $ismanager = FALSE) {
		if (intval($page) === 1) {
			return '';
		} else {
			return self::_get_pagination(intval($page) - 1, TRUE, $ismanager);
		}
	}

	private static function _getNext($page, $page_count, $ismanager = FALSE) {
		if (intval($page) >= intval($page_count)) {
			return '';
		} else {
			return self::_get_pagination(intval($page) + 1, FALSE, $ismanager);
		}
	}

	private static function _get_pagination($page, $is_prev, $ismanager = FALSE) {
		if ($ismanager) {
			return '<a href="' . BASE_URL
					. 'finance/payment/?o=person_apply_manager&page=' . $page
					. '">' . ($is_prev ? '上一页' : '下一页') . '</a>';
		} else {
			return '<a href="javascript:void(0)" onclick="dosearch(' . $page
					. ');">' . ($is_prev ? '上一页' : '下一页') . '</a>';
		}

	}

	private function validate_form_value($action) {
		$errors = array();
		if (in_array($action,
				array('add', 'cancel', 'continue_payment_apply_temp',
						'continue_payment_apply_apply',
						'edit_payment_apply_apply', 'audit_fullpayment_person',
						'payment_person_gd', 'audit_item', 'edit_payfirst'),
				TRUE)) {
			if ($action === 'edit_payfirst') {
				//listid
				if (!self::validate_id(intval($this->listid))) {
					$errors[] = '条目选择有误';
				}

				//is_nim_pay_first
				if (!in_array($this->is_nim_pay_first,
						array('checked', 'undefined'), TRUE)) {
					$errors[] = '是否垫付选择有误';
				}

				//nim_pay_first_amount
				if (!self::validate_money($this->nim_pay_first_amount)) {
					$errors[] = '付款金额不是有效金额值';
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
			} else if ($action === 'payment_person_gd') {
				if (!self::validate_id(intval($this->id))) {
					$errors[] = '付款申请选择有误';
				}

				if (empty($this->gdpaymentdate)) {
					$errors[] = '付款日期不能为空';
				} else {
					if (strtotime($this->gdpaymentdate) === FALSE) {
						$errors[] = '付款日期不是有效日期值';
					}
				}

				if (!self::validate_money($this->gdpaymentamount)) {
					$errors[] = '付款金额不是有效金额值';
				}

				if (!in_array(intval($this->gdpaymenttype), array(1, 2), TRUE)) {
					$errors[] = '付款方式选择有误';
				}

				if (intval($this->gdpaymenttype) === 2
						&& !self::validate_id(intval($this->gdpaymentbank))) {
					$errors[] = '付款方式银行必须选择';
				}

				foreach ($this->gdvalues_array as $v) {
					if (!self::validate_money($v['gdamount'])) {
						$errors[] = '归档金额输入有误';
						break;
					}
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

			} else if ($action === 'cancel') {
				//id
				if (!self::validate_id(intval($this->id))) {
					$errors[] = '所选付款申请有误';
				}

				//type
				if (!in_array($this->type, array('temp', 'untemp'), TRUE)) {
					$errors[] = '所选付款申请类型有误';
				}
			} else {
				//媒体名称
				if (!self::validate_field_not_null($this->media_name)
						|| !self::validate_field_not_empty($this->media_name)) {
					$errors[] = '媒体名称不能为空';
				} else if (!self::validate_field_max_length($this->media_name,
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
				if (!self::validate_invoice_money($this->payment_amount_plan,
						FALSE)) {
					$errors[] = '应付金额不是一个有效的金额值';
				}

				//付款时间
				if ($this->action === 'payment_person_apply'
						|| $this->action === 'continue_payment_apply_apply'
						|| $this->action === 'edit_payment_apply_apply') {
					if (!self::validate_field_not_empty($this->payment_date)
							|| !self::validate_field_not_null(
									$this->payment_date)) {
						$errors[] = '付款时间不能为空';
					} else if (strtotime($this->payment_date) === FALSE) {
						$errors[] = '付款时间不是一个有效的时间值';
					} else if (strtotime($this->payment_date)
							< strtotime(date('Y-m-d', time()))) {
						$errors[] = '付款时间必须晚于等于今天';
					}
				} else {
					if (self::validate_field_not_empty($this->payment_date)) {
						if (strtotime($this->payment_date) === FALSE) {
							$errors[] = '付款时间不是一个有效的时间值';
						} else if (strtotime($this->payment_date)
								< strtotime(date('Y-m-d', time()))) {
							$errors[] = '付款时间必须晚于等于今天';
						}
					}
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

				//返点比例
				if (self::validate_field_not_null($this->rebate_rate)
						&& self::validate_field_not_empty($this->rebate_rate)) {
					if (!Validate_Util::my_is_float($this->rebate_rate)) {
						$errors[] = '返点率不是有效的数值';
					} else if ($this->rebate_rate > 100
							|| $this->rebate_rate < 0) {
						$errors[] = '返点率不是有效的数值';
					}
				} else {
					$this->rebate_rate = 0;
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

				$payment_list = $this->payment_list;
				if (!empty($payment_list)) {
					foreach ($payment_list as $key => $paylist) {
						//申请金额付款类型
						if (!in_array(intval($paylist['payment_type']),
								array(1, 2), TRUE)) {
							$errors[] = '第' . ($key + 1)
									. '行媒体数据【申请金额付款类型】选择有误';
						} else {
							if (intval($paylist['payment_type']) === 2
									&& !self::validate_invoice_money(
											$paylist['payment_amount'])) {
								$errors[] = '第' . ($key + 1)
										. '行媒体数据【申请付款金额】非有效金额值';
							}
						}

						//返点抵扣
						if (!self::validate_money(
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
						&& ($this->action === 'payment_person_apply'
								|| $this->action
										=== 'continue_payment_apply_apply'
								|| $this->action === 'edit_payment_apply_apply')) {
					$errors[] = '所选媒体数据不能为空';
				}

				$deposit_list = $this->deposit_list;
				//var_dump($deposit_list);
				foreach ($deposit_list as $dl) {
					if (!self::validate_id(intval($dl['id']))) {
						$errors[] = '保证金抵扣记录选择有误';
					}

					if (!in_array($dl['dtype'], array('p', 'm'), TRUE)) {
						$errors[] = '保证金抵扣类型选择有误';
					}

					if (!self::validate_money($dl['amount'])) {
						$errors[] = '保证金抵扣金额选择有误';
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

	private function _do_media_info() {
		$success = TRUE;
		//检查付款媒体信息是否已有
		$media_name = $this->media_name;
		$account_bank = !empty($this->bank_name_select) ? $this
						->bank_name_select : $this->bank_name;
		$account = !empty($this->bank_account_select) ? $this
						->bank_account_select : $this->bank_account;

		$media_info_id = 0;
		$row = $this->db
				->get_row(
						'SELECT id,isok FROM finance_payment_media_info WHERE media_name="'
								. $media_name . '" AND account_bank="'
								. $account_bank . '" AND account="' . $account
								. '" FOR UPDATE');
		if ($row == NULL) {
			$insert_result = $this->db
					->query(
							'INSERT INTO finance_payment_media_info(media_name,account_bank,account,isok) VALUE("'
									. $media_name . '","' . $account_bank
									. '","' . $account . '",1)');
			if ($insert_result === FALSE) {
				$success = FALSE;
			} else {
				$media_info_id = $this->db->insert_id;
				Payment_Media_Info::getInstance(TRUE);
			}
		} else {
			$media_info_id = $row->id;
		}
		return $success ? $media_info_id : $success;
	}

	public function edit_payment_person_apply() {
		if ($this->validate_form_value($this->action)) {
			$success = TRUE;
			$error = '';
			$this->db->query('BEGIN');

			//检查是否是自己创建的
			$checkrow = $this->db
					->get_row(
							'SELECT id'
									. ($this->action
											=== 'edit_payment_apply_apply' ? ',payment_id'
											: '') . ' FROM '
									. ($this->action
											=== 'edit_payment_apply_apply' ? 'finance_payment_person_apply'
											: 'finance_payment_person_apply_temp')
									. ' WHERE id=' . intval($this->id)
									. ' AND user=' . $this->getUid());
			if ($checkrow === NULL) {
				$success = FALSE;
				$error = '所选付款申请有误';
			} else {

				//媒体信息
				$media_info_id = $this->_do_media_info();
				if ($media_info_id === FALSE) {
					$success = FALSE;
					$error = '媒体信息出错';
				}

				if ($success) {
					if ($this->action === 'continue_payment_apply_temp') {
						//继续草稿
						$update_result = $this->db
								->query(
										'UPDATE finance_payment_person_apply_temp SET media_info_id='
												. $media_info_id
												. ',payment_amount_plan='
												. $this->payment_amount_plan
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
												. (intval(
														$this
																->is_rebate_deduction)
														=== 1 ? (empty(
																$this
																		->rebate_amount) ? 0
																: $this
																		->rebate_amount)
														: 0) . ',rebate_rate='
												. $this->rebate_rate
												. ',is_deposit_deduction='
												. intval(
														$this
																->is_deposit_deduction)
												. ',is_person_loan_deduction='
												. intval(
														$this
																->is_person_loan_deduction)
												. ',person_loan_amount='
												. (intval(
														$this
																->is_person_loan_deduction)
														=== 1 ? (empty(
																$this
																		->person_loan_amount) ? 0
																: $this
																		->person_loan_amount)
														: 0)
												. ',payment_amount_real=0,remark="'
												. $this->remark . '" WHERE id='
												. intval($this->id));
						if ($update_result === FALSE) {
							$success = FALSE;
							$error = '修改付款申请信息出错，错误代码1';
						} else {
							$delete_result = $this->db
									->query(
											'DELETE FROM finance_payment_person_apply_list_temp WHERE apply_id='
													. intval($this->id));
							if ($delete_result === FALSE) {
								$success = FALSE;
								$error = '修改付款申请信息出错，错误代码2';
							} else {
								$paylists = $this->payment_list;
								$sbusql = array();
								$rebate_array = array();
								$payfirst_array = array();
								foreach ($paylists as $paylist) {
									$sbusql[] = '(' . $this->id . ',"'
											. $paylist['pid'] . '",'
											. $paylist['paycostid'] . ','
											. (empty($paylist['payment_amount']) ? 0
													: $paylist['payment_amount'])
											. ',' . $paylist['payment_type']
											. ','
											. (empty(
													$paylist['rebate_deduction_amount']) ? 0
													: $paylist['rebate_deduction_amount'])
											. ',"'
											. $paylist['rebate_deduction_dids']
											. '","'
											. $paylist['person_loan_user']
											. '",'
											. (empty(
													$paylist['person_loan_amount']) ? 0
													: $paylist['person_loan_amount'])
											. ','
											. (intval(
													$paylist['is_nim_pay_first'])
													=== 1 ? 1 : 0) . ','
											. (empty(
													$paylist['nim_pay_first_amount'])
													|| intval(
															$paylist['is_nim_pay_first'])
															=== 0 ? 0
													: $paylist['nim_pay_first_amount'])
											. ',"'
											. $paylist['nim_pay_first_dids']
											. '")';

									if (!empty(
											$paylist['rebate_deduction_amount'])) {
										//返点入返点表
										$rebate_array[] = '('
												. intval($this->id) . ',1,'
												. $paylist['rebate_deduction_amount']
												. ',1)';
									}

									if (intval($paylist['is_nim_pay_first'])
											=== 1
											&& !empty(
													$paylist['nim_pay_first_amount'])) {
										//垫付入垫付表
										$payfirst_array[] = '('
												. intval($this->id) . ',1,'
												. $paylist['nim_pay_first_amount']
												. ',1)';
									}

								}
								if (!empty($sbusql)) {
									$insert_result = $this->db
											->query(
													'INSERT INTO finance_payment_person_apply_list_temp(apply_id,pid,paycostid,payment_amount,payment_type,rebate_deduction_amount,rebate_deduction_dids,person_loan_user,person_loan_amount,is_nim_pay_first,nim_pay_first_amount,nim_pay_first_dids) VALUES'
															. implode(',',
																	$sbusql));
									if ($insert_result === FALSE) {
										$success = FALSE;
										$error = '修改付款申请信息出错，错误代码3';
									}
								}

								//返点
								if ($success && !empty($rebate_array)) {
									$rebate_result = $this->db
											->query(
													'INSERT INTO finance_payment_rebate_temp(apply_id,payment_type,rebate_amount,amount_type) VALUES'
															. implode(',',
																	$rebate_array));
									if ($insert_result === FALSE) {
										$success = FALSE;
										$error = '修改付款申请失败，错误代码4';
									}
								}

								//垫付
								if ($success && !empty($payfirst_array)) {
									$payfirst_result = $this->db
											->query(
													'INSERT INTO finance_payment_payfirst_temp(apply_id,payment_type,payfirst_amount,amount_type) VALUES'
															. implode(',',
																	$payfirst_array));
									if ($insert_result === FALSE) {
										$success = FALSE;
										$error = '修改付款申请失败，错误代码5';
									}
								}

							}
						}

					} else if ($this->action === 'continue_payment_apply_apply') {
						$payment_id = $this
								->getSequence(
										date('y', time())
												. $this->getCity_show()
												. date('m', time()) . 'PCP');

						if ($payment_id === FALSE) {
							$success = FALSE;
							$error = '生成付款单号出错';
						} else {
							//草稿变正式提交
							$insert_result = $this->db
									->query(
											'INSERT INTO finance_payment_person_apply(media_info_id,payment_amount_plan,payment_date,is_nim_pay_first,is_rebate_deduction,rebate_amount,rebate_rate,is_deposit_deduction,is_person_loan_deduction,person_loan_amount,payment_amount_real,remark,isalter,isok,user,addtime,pcid,payment_id,step) VALUE('
													. $media_info_id . ','
													. $this
															->payment_amount_plan
													. ',"'
													. $this->payment_date
													. '",'
													. intval(
															$this
																	->is_nim_pay_first)
													. ','
													. intval(
															$this
																	->is_rebate_deduction)
													. ','
													. (intval(
															$this
																	->is_rebate_deduction)
															=== 1 ? (empty(
																	$this
																			->rebate_amount) ? 0
																	: $this
																			->rebate_amount)
															: 0) . ','
													. $this->rebate_rate . ','
													. intval(
															$this
																	->is_deposit_deduction)
													. ','
													. intval(
															$this
																	->is_person_loan_deduction)
													. ','
													. (intval(
															$this
																	->is_person_loan_deduction)
															=== 1 ? (empty(
																	$this
																			->person_loan_amount) ? 0
																	: $this
																			->person_loan_amount)
															: 0) . ',0,"'
													. $this->remark . '",0,0,'
													. $this->getUid()
													. ',now(),'
													. $this->process_id . ',"'
													. $payment_id . '",0)');
							if ($insert_result === FALSE) {
								$success = FALSE;
								$error = '修改付款申请失败，错误代码1';
							} else {
								$apply_id = $this->db->insert_id;
								//finance_payment_person_apply_list 表
								$paylists = $this->payment_list;
								$sbusql = array();
								foreach ($paylists as $paylist) {
									$sbusql[] = '(' . $apply_id . ',"'
											. $paylist['pid'] . '",'
											. $paylist['paycostid'] . ','
											. (empty($paylist['payment_amount']) ? 0
													: $paylist['payment_amount'])
											. ',' . $paylist['payment_type']
											. ','
											. (empty(
													$paylist['rebate_deduction_amount']) ? 0
													: $paylist['rebate_deduction_amount'])
											. ',"'
											. $paylist['rebate_deduction_dids']
											. '","'
											. $paylist['person_loan_user']
											. '",'
											. (empty(
													$paylist['person_loan_amount']) ? 0
													: $paylist['person_loan_amount'])
											. ','
											. (intval(
													$paylist['is_nim_pay_first'])
													=== 1 ? 1 : 0) . ','
											. (empty(
													$paylist['nim_pay_first_amount'])
													|| intval(
															$paylist['is_nim_pay_first'])
															=== 0 ? 0
													: $paylist['nim_pay_first_amount'])
											. ',"'
											. $paylist['nim_pay_first_dids']
											. '",0)';

									if (!empty(
											$paylist['rebate_deduction_amount'])) {
										//返点入返点表
										$rebate_array[] = '(' . $apply_id
												. ',1,'
												. $paylist['rebate_deduction_amount']
												. ',1,1)';
									}

									if (intval($paylist['is_nim_pay_first'])
											=== 1
											&& !empty(
													$paylist['nim_pay_first_amount'])) {
										//垫付入垫付表
										$payfirst_array[] = '(' . $apply_id
												. ',1,'
												. $paylist['nim_pay_first_amount']
												. ',1,1)';
									}
								}
								if (!empty($sbusql)) {
									$insert_result = $this->db
											->query(
													'INSERT INTO finance_payment_person_apply_list(apply_id,pid,paycostid,payment_amount,payment_type,rebate_deduction_amount,rebate_deduction_dids,person_loan_user,person_loan_amount,is_nim_pay_first,nim_pay_first_amount,nim_pay_first_dids,isok) VALUES'
															. implode(',',
																	$sbusql));
									if ($insert_result === FALSE) {
										$success = FALSE;
										$error = '修改付款申请失败，错误代码2';
									} else {
										//返点
										if ($success && !empty($rebate_array)) {
											$rebate_result = $this->db
													->query(
															'INSERT INTO finance_payment_rebate(apply_id,payment_type,rebate_amount,amount_type,status) VALUES'
																	. implode(
																			',',
																			$rebate_array));
											if ($insert_result === FALSE) {
												$success = FALSE;
												$error = '修改付款申请失败，错误代码6';
											}
										}

										//垫付
										if ($success && !empty($payfirst_array)) {
											$payfirst_result = $this->db
													->query(
															'INSERT INTO finance_payment_payfirst(apply_id,payment_type,payfirst_amount,amount_type,status) VALUES'
																	. implode(
																			',',
																			$payfirst_array));
											if ($insert_result === FALSE) {
												$success = FALSE;
												$error = '修改付款申请失败，错误代码7';
											}
										}
									}
								} else {
									$success = FALSE;
									$error = '修改付款申请失败，错误代码3';
								}

								if ($success) {
									//删除草稿
									$delete_result = $this->db
											->query(
													'DELETE FROM finance_payment_person_apply_temp WHERE id='
															. intval($this->id));
									if ($delete_result === FALSE) {
										$success = FALSE;
										$error = '修改付款申请失败，错误代码4';
									} else {
										$delete_result = $this->db
												->query(
														'DELETE FROM finance_payment_person_apply_list_temp WHERE apply_id='
																. intval(
																		$this
																				->id));
										if ($delete_result === FALSE) {
											$success = FALSE;
											$error = '修改付款申请失败，错误代码5';
										} else {
											$delete_result = $this->db
													->query(
															'DELETE FROM finance_payment_payfirst_temp WHERE apply_id='
																	. intval(
																			$this
																					->id));
											if ($delete_result === FALSE) {
												$success = FALSE;
												$error = '修改付款申请失败，错误代码8';
											} else {
												$delete_result = $this->db
														->query(
																'DELETE FROM finance_payment_rebate_temp WHERE apply_id='
																		. intval(
																				$this
																						->id));
												if ($delete_result === FALSE) {
													$success = FALSE;
													$error = '修改付款申请失败，错误代码9';
												}
											}
										}
									}
								}
							}
						}

						if ($success) {
							$type = '<font color=\'#99cc00\'>付款申请草稿转正式申请</font>';
						}

					} else if ($this->action === 'edit_payment_apply_apply') {
						$payment_id = $checkrow->payment_id;
						$payment_amount_plan = $this->payment_amount_plan;
						$rebate_amount = intval($this->is_rebate_deduction)
								=== 1 ? !empty($this->rebate_amount) ? $this
												->rebate_amount : 0 : 0;
						$person_loan_amount = intval(
								$this->is_person_loan_deduction) === 1 ? !empty(
										$this->person_loan_amount) ? $this
												->person_loan_amount : 0 : 0;

						$deposit_amount = 0;
						$deposit_deduction_sql = array();

						//删除 原来的
						$delete = $this->db
								->query(
										'UPDATE finance_payment_deposit_deduction SET isok=-1 WHERE apply_id='
												. intval($this->id)
												. ' AND apply_type=1 AND payment_type=1');
						if ($delete === FALSE) {
							$success = FALSE;
							$error = '保证金抵扣更新失败';
						}

						if ($success) {
							if (intval($this->is_deposit_deduction) === 1
									&& !empty($this->deposit_list)) {
								foreach ($this->deposit_list as $val) {

									$deposit_amount += $val['amount'];
									//finance_payment_deposit_deduction
									$deposit_deduction_sql[] = 'INSERT INTO finance_payment_deposit_deduction(apply_id,deposit_gd_id,deduction_amount,addtime,isok,apply_type,payment_type) SELECT '
											. intval($this->id) . ',id,'
											. $val['amount']
											. ',now(),1,1,1 FROM finance_payment_deposit_gd WHERE id='
											. $val['id']
											. ' AND isok=1 AND apply_type='
											. ($val['dtype'] === 'p' ? 1 : 2);
								}
							}

							//正式的修改
							$update_result = $this->db
									->query(
											'UPDATE finance_payment_person_apply SET media_info_id='
													. $media_info_id
													. ',payment_amount_plan='
													. $payment_amount_plan
													. ',payment_date="'
													. $this->payment_date
													. '",is_nim_pay_first='
													. intval(
															$this
																	->is_nim_pay_first)
													. ',is_rebate_deduction='
													. intval(
															$this
																	->is_rebate_deduction)
													. ',rebate_amount='
													. $rebate_amount
													. ',rebate_rate='
													. $this->rebate_rate
													. ',is_deposit_deduction='
													. intval(
															$this
																	->is_deposit_deduction)
													. ',is_person_loan_deduction='
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
													. ',remark="'
													. $this->remark
													. '",isok=0,addtime=now(),step=0 WHERE id='
													. intval($this->id));

							if ($update_result === FALSE) {
								$success = FALSE;
								$error = '修改付款申请失败，错误代码6';
							} else {
								$update_result = $this->db
										->query(
												'UPDATE finance_payment_person_apply_list SET isok=-1 WHERE apply_id='
														. intval($this->id));
								if ($update_result === FALSE) {
									$success = FALSE;
									$error = '修改付款申请失败，错误代码7';
								} else {

									$other = $this
											->_do_rebate_and_nimpayfirst(
													intval($this->id));
									if ($other['status'] === 'error') {
										$success = FALSE;
										$error = $other['message'];
									}
									/*
									$paylists = $this->payment_list;
									$sbusql = array();
									foreach ($paylists as $paylist) {
									    $sbusql[] = '(' . $this->id . ',"'
									            . $paylist['pid'] . '",'
									            . $paylist['paycostid'] . ','
									            . (empty($paylist['payment_amount']) ? 0
									                    : $paylist['payment_amount'])
									            . ',' . $paylist['payment_type']
									            . ','
									            . (empty(
									                    $paylist['rebate_deduction_amount']) ? 0
									                    : $paylist['rebate_deduction_amount'])
									            . ',"'
									            . $paylist['rebate_deduction_dids']
									            . '","'
									            . $paylist['person_loan_user']
									            . '",'
									            . (empty(
									                    $paylist['person_loan_amount']) ? 0
									                    : $paylist['person_loan_amount'])
									            . ','
									            . (intval(
									                    $paylist['is_nim_pay_first'])
									                    === 1 ? 1 : 0) . ','
									            . (empty(
									                    $paylist['nim_pay_first_amount']) ? 0
									                    : $paylist['nim_pay_first_amount'])
									            . ',"'
									            . $paylist['nim_pay_first_dids']
									            . '",0)';
									}
									if (!empty($sbusql)) {
									    $insert_result = $this->db
									            ->query(
									                    'INSERT INTO finance_payment_person_apply_list(apply_id,pid,paycostid,payment_amount,payment_type,rebate_deduction_amount,rebate_deduction_dids,person_loan_user,person_loan_amount,is_nim_pay_first,nim_pay_first_amount,nim_pay_first_dids,isok) VALUES'
									                            . implode(',',
									                                    $sbusql));
									    if ($insert_result === FALSE) {
									        $success = FALSE;
									        $error = '修改付款申请信息出错，错误代码8';
									    }
									} else {
									    $success = FALSE;
									    $error = '修改付款申请信息出错，错误代码9';
									}
									 */
								}
							}

							if ($success && !empty($deposit_deduction_sql)) {
								//撤销原有记录
								$update_result = $this->db
										->query(
												'UPDATE finance_payment_deposit_deduction SET isok=-1 WHERE apply_id='
														. intval($this->id)
														. ' AND apply_type=1 AND payment_type=1');
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

							if ($success) {
								$type = '<font color=\'#99cc00\'>修改付款申请</font>';
							}
						} else {
							$success = FALSE;
							$error = '操作有误';
						}
					}
				}

				if ($success && $this->action !== 'continue_payment_apply_temp') {
					$result = $this->_log($payment_id, '发起人', $type);
					if ($result === FALSE) {
						$success = FALSE;
						$error = '修改付款申请失败，错误代码10';
					}
				}
			}

			if ($success) {
				$this->db->query('COMMIT');
			} else {
				$this->db->query('ROLLBACK');
			}
			return array('status' => $success ? 'success' : 'error',
					'message' => $success ? '修改付款申请成功' : $error);
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
							'INSERT INTO finance_payment_person_apply_list(apply_id,pid,paycostid,payment_amount,payment_type,rebate_deduction_amount,rebate_deduction_dids,person_loan_user,person_loan_amount,is_nim_pay_first,nim_pay_first_amount,nim_pay_first_dids,isok) VALUE('
									. $apply_id . ',"' . $paylist['pid'] . '",'
									. $paylist['paycostid'] . ','
									. (empty($paylist['payment_amount']) ? 0
											: $paylist['payment_amount']) . ','
									. $paylist['payment_type'] . ','
									. (empty(
											$paylist['rebate_deduction_amount']) ? 0
											: $paylist['rebate_deduction_amount'])
									. ',"' . $paylist['rebate_deduction_dids']
									. '","' . $paylist['person_loan_user']
									. '",'
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
			if ($list_result === FALSE) {
				$success = FALSE;
				$error = '记录执行单信息有误';
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
											. ',1,1)');
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
											. ',1,1,now())');
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

	public function add_payment_person_apply() {
		if ($this->validate_form_value('add')) {
			$success = TRUE;
			$error = '';
			$this->db->query('BEGIN');

			//生成付款单号
			if ($this->action === 'payment_person_apply') {

				$payment_id = $this
						->getSequence(
								date('y', time()) . $this->getCity_show()
										. date('m', time()) . 'PCP');
			}

			if ($payment_id === FALSE
					&& $this->action === 'payment_person_apply') {
				$success = FALSE;
				$error = '生成付款单号出错';
			} else {
				//媒体信息
				$media_info_id = $this->_do_media_info();
				if ($media_info_id === FALSE) {
					$success = FALSE;
					$error = '媒体信息出错';
				} else {
					$table1_name = $this->action === 'payment_person_apply' ? 'finance_payment_person_apply'
							: 'finance_payment_person_apply_temp';

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
					if (intval($this->is_deposit_deduction) === 1
							&& !empty($this->deposit_list)) {
						foreach ($this->deposit_list as $val) {

							$deposit_amount += $val['amount'];
							//finance_payment_deposit_deduction
							$deposit_deduction_sql[] = 'INSERT INTO finance_payment_deposit_deduction(apply_id,deposit_gd_id,deduction_amount,addtime,isok,apply_type,payment_type) SELECT "APPLYID",id,'
									. $val['amount']
									. ',now(),1,1,1 FROM finance_payment_deposit_gd WHERE id='
									. $val['id']
									. ' AND isok=1 AND apply_type='
									. ($val['dtype'] === 'p' ? 1 : 2);
						}
					}

					//finance_payment_person_apply表			
					$insert_result = $this->db
							->query(
									'INSERT INTO ' . $table1_name
											. '(media_info_id,payment_amount_plan,payment_date,is_nim_pay_first,is_rebate_deduction,rebate_amount,rebate_rate,is_deposit_deduction,is_person_loan_deduction,person_loan_amount,payment_amount_real,remark,'
											. ($this->action
													=== 'payment_person_apply' ? 'isalter,isok,pcid,payment_id,step,'
													: '')
											. 'user,addtime) VALUE('
											. $media_info_id . ','
											. $payment_amount_plan . ','
											. (empty($this->payment_date) ? 'null'
													: '"' . $this->payment_date
															. '"') . ','
											. intval($this->is_nim_pay_first)
											. ','
											. intval($this->is_rebate_deduction)
											. ',' . $rebate_amount . ','
											. $this->rebate_rate . ','
											. intval(
													$this->is_deposit_deduction)
											. ','
											. intval(
													$this
															->is_person_loan_deduction)
											. ',' . $person_loan_amount . ','
											. ($payment_amount_plan
													- $rebate_amount
													- $person_loan_amount
													- $deposit_amount) . ',"'
											. $this->remark . '",'
											. ($this->action
													=== 'payment_person_apply' ? '0,0,'
															. $this->process_id
															. ',"'
															. $payment_id
															. '",0' : '') . ','
											. $this->getUid() . ',now())');

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
					}

					//保证金抵扣
					if ($success && !empty($deposit_deduction_sql)) {
						foreach ($deposit_deduction_sql as $sql) {
							$insert_result = $this->db
									->query(
											str_replace('"APPLYID"', $apply_id,
													$sql));
							if ($insert_result === FALSE) {
								$success = FALSE;
								$error = '新建付款申请失败，错误代码5';
								break;
							}
						}
					}
				}
			}

			if ($success && $this->action === 'payment_person_apply') {
				//记录日志
				$result = $this
						->_log($payment_id, '发起人',
								'<font color=\'#99cc00\'>新建个人付款申请</font>');
				if ($result === FALSE) {
					$success = FALSE;
					$error = '新建个人付款申请失败，错误代码4';
				}
			}

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

	public function get_edit_payment_person_apply_html($o) {
		$id = Security_Util::my_get('id');
		if (self::validate_id(intval($id))) {
			$row = $this->db
					->get_row(
							'SELECT media_info_id,payment_amount_plan,payment_date,is_nim_pay_first,is_rebate_deduction,rebate_amount,rebate_rate,is_deposit_deduction,is_person_loan_deduction,person_loan_amount,payment_amount_real,remark,user FROM '
									. ($o === 'edit_payment_apply' ? 'finance_payment_person_apply'
											: 'finance_payment_person_apply_temp')
									. ' WHERE id=' . intval($id) . ' AND user='
									. $this->getUid());
			if ($row === NULL) {
				User::no_object('没有该付款申请或非创建者');
			} else {
				//是否已归档完毕
				$done_gd = $this->_getPersonPaymentGD(intval($id));
				if (doubleval($done_gd)
						=== doubleval($row->payment_amount_real)) {
					User::no_object('该付款申请已归档完毕，不可修改');
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
									'SELECT pid,paycostid,payment_amount,payment_type,rebate_deduction_amount,rebate_deduction_dids,person_loan_user,person_loan_amount,is_nim_pay_first,nim_pay_first_amount,nim_pay_first_dids FROM '
											. ($o === 'edit_payment_apply' ? 'finance_payment_person_apply_list'
													: 'finance_payment_person_apply_list_temp')
											. ' WHERE apply_id=' . intval($id)
											. ' AND isok<>-1');
					$pids = array();
					if ($apply_list !== NULL) {
						foreach ($apply_list as $app) {
							$pids[] = $app->pid . '_' . $app->paycostid;
						}
						$pids = ',' . implode(',', $pids) . ',';
					} else {
						$pids = ',';
					}

					//$payment_amount_plan = empty($row->payment_amount_plan) ? 0
					//		: $row->payment_amount_plan;
					//$rebate_amount = intval($row->is_rebate_deduction) === 1 ? (empty(
					//				$row->rebate_amount) ? 0 : $row->rebate_amount)
					//		: 0;
					//$person_loan_amount = intval($row->is_person_loan_deduction)
					//		=== 1 ? (empty($row->person_loan_amount) ? 0
					//				: $row->person_loan_amount) : 0;
					//$deposit_amount = 0;

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
									. 'finance/payment/payment_person_apply_edit.tpl');
					return str_replace(
							array('[LEFT]', '[TOP]', '[EDITTYPE]',
									'[MEDIANAME]', '[BANKLIST]',
									'[ACCOUNTLIST]', '[PAYMENTAMOUNTPLAN]',
									'[PAYMENTDATE]', '[ISNIMPAYFIRST]',
									'[ISREBATEDEDUCTION]', '[REBATEAMOUNT]',
									'[REBATERATE]', '[ISDEPOSITDEDUCTION]',
									'[ISPERSONLOANDEDUCTION]',
									'[PERSONLOANAMOUNT]', '[REMARK]',
									'[HASSAVEBTN]', '[PIDS]', '[EDITACTION]',
									'[APPLYID]', '[PAYMENTREAL]', '[VCODE]',
									'[DEPOSITDEDUCTION]', '[BASE_URL]'),
							array($this->get_left_html(),
									$this->get_top_html(),
									($o === 'edit_payment_apply' ? '变更付款申请'
											: '继续填写付款申请'), $media_name,
									Payment_Media_Info::get_bank_list(
											$media_name, $account_bank),
									Payment_Media_Info::get_bank_acount_list(
											$media_name, $account_bank,
											$account),
									$row->payment_amount_plan,
									$row->payment_date,
									(intval($row->is_nim_pay_first) === 0 ? ''
											: 'checked'),
									(intval($row->is_rebate_deduction) === 0 ? ''
											: 'checked'), $row->rebate_amount,
									$row->rebate_rate,
									(intval($row->is_deposit_deduction) === 0 ? ''
											: 'checked'),
									(intval($row->is_person_loan_deduction)
											=== 0 ? '' : 'checked'),
									$row->person_loan_amount, $row->remark,
									($o === 'edit_payment_apply' ? '$("#save").hide();'
											: ''), $pids, $o, $id,
									sprintf("%.2f", $row->payment_amount_real),
									$this->get_vcode(), $deposit_deductiuon,
									BASE_URL), $buf);
				}
			}
		} else {
			User::no_object('没有该付款申请');
		}
	}

	public function cancel_payment_apply() {
		if ($this->validate_form_value('cancel')) {
			$success = TRUE;
			$error = '';
			$this->db->query('BEGIN');

			//判断是否是自己创建的
			$row = $this->db
					->get_row(
							'SELECT id'
									. ($this->type === 'temp' ? ''
											: ',isok,payment_id,payment_amount_real')
									. ' FROM finance_payment_person_apply'
									. ($this->type === 'temp' ? '_temp' : '')
									. ' WHERE id=' . intval($this->id)
									. ' AND user=' . $this->getUid()
									. ' FOR UPDATE');
			if ($row === NULL) {
				$success = FALSE;
				$error = '该付款申请不存在或者非自己创建';
			}

			$r = 1;

			if ($success) {
				if ($this->type === 'temp') {
					//作废草稿，直接删除记录
					$delete_result = $this->db
							->query(
									'DELETE FROM finance_payment_rebate_temp WHERE apply_id='
											. intval($this->id));
					if ($delete_result === FALSE) {
						$success = FALSE;
						$error = '作废付款申请草稿失败，错误代码1';
					}

					if ($success) {
						$delete_result = $this->db
								->query(
										'DELETE FROM finance_payment_payfirst_temp WHERE apply_id='
												. intval($this->id));
						if ($delete_result === FALSE) {
							$success = FALSE;
							$error = '作废付款申请草稿失败，错误代码2';
						}
					}

					if ($success) {
						$delete_result = $this->db
								->query(
										'DELETE FROM finance_payment_person_apply_list_temp WHERE apply_id='
												. intval($this->id));
						if ($delete_result === FALSE) {
							$success = FALSE;
							$error = '作废付款申请草稿失败，错误代码3';
						}
					}

					if ($success) {
						$delete_result = $this->db
								->query(
										'DELETE FROM finance_payment_person_apply_temp WHERE id='
												. intval($this->id));
						if ($delete_result === FALSE) {
							$success = FALSE;
							$error = '作废付款申请草稿失败，错误代码4';
						}
					}
				} else {
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
										'UPDATE finance_payment_person_apply SET isok=-1 WHERE id='
												. intval($this->id));
						if ($update_result === FALSE) {
							$success = FALSE;
							$error = '作废付款申请失败，错误代码1';
						} else {
							$update_result = $this->db
									->query(
											'UPDATE finance_payment_person_apply_list SET isok=-1 WHERE apply_id='
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
														. ' AND amount_type=1 AND payment_type=1');
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
														. ' AND amount_type=1 AND payment_type=1');
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
						$gds = $this->_getPersonPaymentGD(intval($this->id));
						if (doubleval($gds)
								=== doubleval($row->payment_amount_real)) {
							$success = FALSE;
							$error = '付款申请已全部归档，不可作废';
						} else {

							$update_result = $this->db
									->query(
											'UPDATE finance_payment_person_apply SET isok=3 WHERE id='
													. intval($this->id));
							if ($update_result === FALSE) {
								$success = FALSE;
								$error = '作废付款申请失败，错误代码3';
							} else {
								$insert_result = $this->db
										->query(
												'INSERT INTO finance_payment_person_cancel_log(apply_id,cancel_apply_time,answer,payment_type,amount_type) VALUE('
														. intval($this->id)
														. ',now(),0,1,1)');

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
					}
				}

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

	private function _get_manager_list() {
		$this->all_count = intval(
				$this->db
						->get_var(
								'SELECT COUNT(*) FROM finance_payment_person_apply'));
		$this->page_count = ceil($this->all_count / self::LIMIT);
		$start = self::LIMIT * intval($this->page) - self::LIMIT;
		if ($start < 0) {
			$start = 0;
		}
		$results = array();
		$lists = $this->db
				->get_results(
						'SELECT a.id,a.media_info_id,a.addtime,a.payment_date,a.payment_amount_plan,a.payment_amount_real,a.user,a.isalter,a.isok,a.step,b.media_name,c.username,c.realname,d.depname FROM finance_payment_person_apply a LEFT JOIN finance_payment_media_info b ON a.media_info_id=b.id LEFT JOIN users c ON a.user=c.uid LEFT JOIN hr_department d ON c.dep=d.id ORDER BY payment_date DESC LIMIT '
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
						'depname' => $list->depname);
			}
		}
		return $results;
	}

	private function _get_status_arrays($apply_id) {
		$cancels = array();
		$waitings = array();
		$auditpass = array();
		$auditreject = array();
		$count = 0;
		$results = $this->db
				->get_results(
						'SELECT id,isok FROM finance_payment_person_apply_list WHERE apply_id='
								. intval($apply_id) . ' AND isok<>-1');
		//var_dump($results);
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
				case -2:
					$cancels[] = $result->id;
					break;
				}
			}
		}
		return array('count' => $count, 'waitings' => $waitings,
				'auditpass' => $auditpass, 'auditreject' => $auditreject,
				'cancels' => $cancels);
	}

	private function _get_person_payment_manager_status($apply_id, $isok) {
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
			if (count($status['auditpass']) === $status['count']) {
				//审核通过
				return '审核通过';
			} else {
				//审核通过，但有部分撤销
				return '审核通过（部分撤销）';
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

	private function _get_person_payment_manager_action($apply_id, $isok,
			$amount_real, $done_gd) {
		$status = $this->_get_status_arrays($apply_id);

		//开始判断
		if (intval($isok) === 0) {
			if ($status['count'] === count($status['waitings'])) {
				//未开始审核
				return '<a href="' . BASE_URL
						. 'finance/payment/?o=person_apply_manager_audit&id='
						. intval($apply_id) . '">审核</a>';
			} else if (count($status['waitings']) > 0
					&& count($status['waitings']) < $status['count']) {
				//审核中
				return '<a href="' . BASE_URL
						. 'finance/payment/?o=person_apply_manager_audit&id='
						. intval($apply_id) . '">审核</a>';
			}
		} else if (intval($isok) === 1) {
			$gd = '';
			if (doubleval($amount_real) !== doubleval($done_gd)) {
				$gd = '<a href="' . BASE_URL
						. 'finance/payment/?o=person_apply_manager_gd&id='
						. intval($apply_id) . '">归档</a>&nbsp;|&nbsp;';
			}

			if (count($status['auditpass']) + count($status['cancels'])
					=== $status['count']) {
				//审核通过，但有部分撤销
				return $gd . '<a href="' . BASE_URL
						. 'finance/payment/?o=print&id=' . intval($apply_id)
						. '&type=pc">打印</a>';
			} else {
				//审核通过
				return $gd . '<a href="' . BASE_URL
						. 'finance/payment/?o=print&id=' . intval($apply_id)
						. '&type=pc">打印</a>';
			}
		} else if (intval($isok) === 2) {
			//审核驳回
			return '';
		} else if (intval($isok) === -1) {
			//审核撤销
			return '';
		} else if (intval($isok) === 3) {
			//申请撤销
			return '<a href="' . BASE_URL
					. 'finance/payment/?o=person_apply_manager_cancel&id='
					. intval($apply_id) . '">审核撤销</a>';
		}
		return '';
	}

	private function _getPersonPaymentGD($apply_id = 0) {
		$datas = array();
		$results = $this->db
				->get_results(
						'SELECT SUM(gd_amount) AS amount,apply_id FROM finance_payment_gd WHERE apply_type=1'
								. ($apply_id > 0 ? ' AND apply_id=' . $apply_id
										: ' GROUP BY apply_id'));
		if ($results !== NULL) {
			foreach ($results as $result) {
				$datas[$result->apply_id] = $result->amount;
			}
		}
		return $apply_id > 0 ? $datas[$apply_id] : $datas;
	}

	public function _get_payment_person_apply_list() {
		$datas = $this->_get_manager_list();
		$gds = $this->_getPersonPaymentGD();

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
										$data['id'], $data['isok'])
						. '</td><td>'
						. $this
								->_get_person_payment_manager_action(
										$data['id'], $data['isok'],
										$data['payment_amount_real'],
										(empty($gds[$data['id']]) ? 0
												: $gds[$data['id']]))
						. '</td></tr>';
			}
		} else {
			$result .= '<tr><td colspan="9"><font color="red"><b>没有相关数据！</b></font></td></tr>';
		}
		return $result;
	}

	private function _get_apply_counts() {
		return $this->page . '	/' . $this->page_count . ' 页 &nbsp;&nbsp;';
	}

	public function get_payment_person_apply_list_html() {
		if (in_array($this->getUsername(),
				$GLOBALS['manager_finance_permission'], TRUE)
				|| intval($this->getBelong_dep()) === 2) {
			$buf = file_get_contents(
					TEMPLATE_PATH . 'finance/payment/payment_person_list.tpl');
			return str_replace(
					array('[LEFT]', '[TOP]', '[PAYMENTLIST]', '[ALLCOUNTS]',
							'[COUNTS]', '[PREV]', '[NEXT]', '[VCODE]',
							'[BASE_URL]'),
					array($this->get_left_html(), $this->get_top_html(),
							$this->_get_payment_person_apply_list(),
							$this->all_count, $this->_get_apply_counts(),
							self::_getPrev($this->page, TRUE),
							self::_getNext($this->page, $this->page_count, TRUE),
							$this->get_vcode(), BASE_URL), $buf);
		}
		return User::no_permission();
	}

	public static function isYesOrNo($val) {
		return intval($val) === 1 ? '是' : '否';
	}

	public static function getPaymentType($payment_type) {
		return intval($payment_type) === 1 ? '全付' : '支付部分';
	}

	private static function _isAuditHtml($audit = TRUE, $isFinance = TRUE) {
		if ($audit && $isFinance) {
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
			$isCK = FALSE, $isFinance = TRUE) {
		$info = '';
		$row = $this->db
				->get_row(
						'SELECT a.payment_amount_plan,a.payment_date,a.is_nim_pay_first,a.is_rebate_deduction,a.rebate_amount,a.rebate_rate,a.is_deposit_deduction,a.is_person_loan_deduction,a.person_loan_amount,a.remark,a.isok,a.payment_amount_real,a.payment_id,b.media_name,b.account_bank,b.account FROM finance_payment_person_apply a LEFT JOIN finance_payment_media_info b ON a.media_info_id=b.id WHERE a.id='
								. intval($id));
		if ($row !== NULL) {
			if (intval($row->isok) === 0 && $isAudit
					|| intval($row->isok) === 1 && !$isAudit) {
				$info = str_replace(
						array('[MEDIANAME]', '[BANKNAME]', '[ACCOUNTNAME]',
								'[PAYMENTAMOUNTPLAN]', '[PAYMENTDATE]',
								'[REBATEAMOUNT]', '[REBATERATE]',
								'[PERSONLOANAMOUNT]', '[PAYMENTREAL]',
								'[REMARK]', '[ISNIMPAYFIRST]',
								'[ISREBATEDEDUCTION]', '[ISDEPOSITDEDUCTION]',
								'[ISPERSONLOANDEDUCTION]', '[ISAUDIT]',
								'[ISCK]', '[PAYMENTID]'),
						array($row->media_name, $row->account_bank,
								$row->account, $row->payment_amount_plan,
								$row->payment_date, $row->rebate_amount,
								$row->rebate_rate, $row->person_loan_amount,
								$row->payment_amount_real,
								Format_Util::format_html($row->remark),
								self::isYesOrNo($row->is_nim_pay_first),
								self::isYesOrNo($row->is_rebate_deduction),
								self::isYesOrNo($row->is_deposit_deduction),
								self::isYesOrNo($row->is_person_loan_deduction),
								self::_isAuditHtml($isAudit, $isFinance),
								self::_isCKHtml($isCK), $row->payment_id),
						file_get_contents(
								TEMPLATE_PATH
										. 'finance/payment/payment_person_info.tpl'));
				return $info;
			} else {
				User::no_permission();
			}
		} else {
			User::no_object('没有该个人付款申请');
		}

	}

	private function _log($payment_id, $auditname, $type, $content = '') {
		$insert_result = $this->db
				->query(
						'INSERT INTO finance_payment_person_apply_log(payment_id,auditname,time,uid,content,type) VALUE("'
								. $payment_id . '","' . $auditname . '",'
								. time() . ',' . $this->getUid() . ',"'
								. $content . '","' . $type . '")');
		return $insert_result !== FALSE;
	}

	public function get_person_apply_manager_gd_html() {
		if (in_array($this->getUsername(),
				$GLOBALS['manager_finance_permission'], TRUE)
				|| intval($this->getBelong_dep()) === 2) {
			$id = Security_Util::my_get('id');

			$row = $this->db
					->get_row(
							'SELECT is_deposit_deduction FROM finance_payment_person_apply WHERE id='
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
								. 'finance/payment/payment_person_apply_gd.tpl');
				return str_replace(
						array('[LEFT]', '[TOP]', '[VCODE]',
								'[PAYMENTPERSONINFO]', '[NIMBANKS]',
								'[APPLYID]', '[DEPOSITDEDUCTION]',
								'[BASE_URL]'),
						array($this->get_left_html(), $this->get_top_html(),
								$this->get_vcode(),
								$this
										->_get_payment_person_info($id, FALSE,
												TRUE, TRUE),
								Nim_BankInfo::get_bank_account_list(), $id,
								$deposit_deductiuon, BASE_URL), $buf);
			} else {
				User::no_object('没有该付款申请');
			}
		}
		return User::no_permission();
	}

	public function get_person_apply_manager_audit_html() {
		$isFinance = FALSE;
		if (in_array($this->getUsername(),
				$GLOBALS['manager_finance_permission'])
				|| intval($this->getBelong_dep()) === 2) {
			$isFinance = TRUE;
		}
		$id = Security_Util::my_get('id');
		$row = $this->db
				->get_row(
						'SELECT is_deposit_deduction FROM finance_payment_person_apply WHERE id='
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
							. 'finance/payment/payment_person_apply_audit.tpl');
			return str_replace(
					array('[LEFT]', '[TOP]', '[VCODE]', '[PAYMENTPERSONINFO]',
							'[APPLYID]', '[DEPOSITDEDUCTION]', '[FINANCETAB]',
							'[ISLEADER]', '[BASE_URL]'),
					array($this->get_left_html(), $this->get_top_html(),
							$this->get_vcode(),
							$this
									->_get_payment_person_info($id, TRUE,
											FALSE, $isFinance), $id,
							$deposit_deductiuon,
							($isFinance ? '<li><a href="' . BASE_URL
											. 'finance/payment/?o=person_apply_manager">个人付款申请列表</a></li>'
									: ''), ($isFinance ? '' : 'leader_'),
							BASE_URL), $buf);
		}
		return User::no_object('没有该付款申请');
	}

	public function audit_full_payment_person() {
		if ($this->validate_form_value('audit_fullpayment_person')) {
			$success = TRUE;
			$error = '';
			$this->db->query('BEGIN');

			$row = $this->db
					->get_row(
							'SELECT step,payment_id FROM finance_payment_person_apply WHERE id='
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
						$sql = 'UPDATE finance_payment_person_apply SET step=step+1'
								. (intval($row->step) + 1
										=== count($this->process_step) - 1 ? ',isok=1'
										: '') . ' WHERE id='
								. intval($this->id);
					} else if ($this->auditvalue === 'reject') {
						$sql = 'UPDATE finance_payment_person_apply SET step=0,isok=2 WHERE id='
								. intval($this->id);
					}

					$update_result = $this->db->query($sql);
					if ($update_result === FALSE) {
						$success = FALSE;
						$error = '审核个人付款申请失败，错误代码1';
					} else {
						$sql = NULL;
						if ($this->auditvalue === 'pass'
								&& intval($row->step) + 1
										=== count($this->process_step) - 1) {
							$sql = 'UPDATE finance_payment_person_apply_list SET isok=1 WHERE apply_id='
									. intval($this->id) . ' AND isok=0';
						} else if ($this->auditvalue === 'reject') {
							$sql = 'UPDATE finance_payment_person_apply_list SET isok=2 WHERE apply_id='
									. intval($this->id) . ' AND isok=0';
						}
						if ($sql !== NULL) {
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

	public function editPPNimPayFirst() {

		if ($this->validate_form_value('edit_payfirst')) {
			$success = TRUE;
			$error = '';
			$this->db->query('BEGIN');

			$row = $this->db
					->get_row(
							'SELECT step,payment_id FROM finance_payment_person_apply WHERE id='
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
					if ($this->is_nim_pay_first === 'checked') {
						$update = 'is_nim_pay_first=1,nim_pay_first_amount='
								. $this->nim_pay_first_amount;
					} else {
						$update = 'is_nim_pay_first=0,nim_pay_first_amount=NULL';
					}
					$update_result = $this->db
							->query(
									'UPDATE finance_payment_person_apply_list SET '
											. $update . ' WHERE id='
											. intval($this->listid));
					if ($update_result === FALSE) {
						$success = FALSE;
						$error = '更新垫付信息出错，错误代码1';
					} else {
						$update_result = $this->db
								->query(
										'UPDATE finance_payment_payfirst SET status=-1 WHERE apply_id='
												. intval($this->id)
												. ' AND list_id='
												. intval($this->listid)
												. ' AND payment_type=1 AND amount_type=1');
						if ($update_result === FALSE) {
							$success = FALSE;
							$error = '更新垫付信息出错，错误代码2';
						}

						if ($success && $this->is_nim_pay_first === 'checked') {
							$update_result = $this->db
									->query(
											'INSERT INTO finance_payment_payfirst(apply_id,list_id,payment_type,payfirst_amount,amount_type,status,addtime) VALUE('
													. intval($this->id) . ','
													. intval($this->listid)
													. ',1,'
													. $this
															->nim_pay_first_amount
													. ',1,1,now())');
							if ($update_result === FALSE) {
								$success = FALSE;
								$error = '更新垫付信息出错，错误代码3';
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
					'message' => $success ? '修改垫付信息成功' : $error);
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
							'SELECT step,payment_id FROM finance_payment_person_apply WHERE id='
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
									'UPDATE finance_payment_person_apply_list SET isok='
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
										'SELECT isok FROM finance_payment_person_apply_list WHERE apply_id='
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
									$sql = 'UPDATE finance_payment_person_apply SET step=step+1'
											. (intval($row->step) + 1
													=== count(
															$this->process_step)
															- 1 ? ',isok=1' : '')
											. ' WHERE id=' . intval($this->id);
									$type = '<font color=\'#99cc00\'>'
											. $auditname . ' 确认</font>';
								} else if (intval($this->auditsel) === 2) {
									$sql = 'UPDATE finance_payment_person_apply SET step=0,isok=2 WHERE id='
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

	public function payment_person_gd() {
		if ($this->validate_form_value('payment_person_gd')) {
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
								: $this->gdpaymentbank) . ',1)';
			}
			if (!empty($sql)) {
				$insert_result = $this->db
						->query(
								'INSERT INTO finance_payment_gd(apply_id,payment_id,pid,paycostid,gd_time,gd_amount,payment_type,payment_bank,apply_type) VALUES'
										. implode(',', $sql));
				if ($insert_result === FALSE) {
					$success = FALSE;
					$error = '个人付款申请归档失败';
				} else {
					$insert_result = $this
							->_log($this->payment_id, $this->getRealname(),
									'<font color=\'#99cc00\'>归档</font>');
					if ($insert_result === FALSE) {
						$success = FALSE;
						$error = '个人付款申请归档记录日志失败';
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
					'message' => $success ? '归档个人付款申请成功' : $error);
		}
		return array('status' => 'error', 'message' => $this->errors);
	}
}
