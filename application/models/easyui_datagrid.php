<?php
class Easyui_Datagrid extends User {
	private $page;
	private $rows;
	private $sort;
	private $order;
	private $qs = array();

	public function __construct($fields = array()) {
		parent::__construct();
		if (!empty($fields)) {
			foreach ($this as $key => $value) {
				if ($fields[$key] !== NULL) {
					$this->$key = $fields[$key];
				}
			}
		}

		if (!(Validate_Util::my_is_int($this->page) && $this->page > 0)) {
			$this->page = 1;
		}
		if (!(Validate_Util::my_is_int($this->rows) && $this->rows > 0)) {
			$this->rows = 10;
		}
		if (!in_array($this->order, array('asc', 'desc'), TRUE)) {
			$this->order = 'desc';
		}
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

	private function _getInvoice($pid = NULL) {
		$resuts = $this->db
				->get_results(
						'SELECT SUM(amount) AS invoice_amount,pid FROM finance_invoice WHERE 1=1 '
								. ($pid !== NULL ? ' AND pid="' . $pid . '"'
										: '') . ' AND  isok<>-1'
								. ($pid === NULL ? ' GROUP BY pid' : ''));
		$datas = array();
		if ($resuts !== NULL) {
			foreach ($resuts as $result) {
				$datas[$result->pid] = $result->invoice_amount;
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

	public function getPidFinanceInfo() {
		$sql = 'SELECT COUNT(*) FROM 
(
SELECT MAX(isalter) AS isalter,pid FROM executive WHERE isok<>-1 GROUP BY pid
) a 
LEFT JOIN executive b
ON a.pid=b.pid AND a.isalter=b.isalter
LEFT JOIN contract_cus c 
ON b.cid=c.cid WHERE 1=1';
		$qs = $this->qs;
		if (!empty($qs['pid'])) {
			$sql .= ' AND a.pid LIKE "%' . $qs['pid'] . '%"';
		}
		if (!empty($qs['projectname'])) {
			$sql .= ' AND b.name LIKE "%' . $qs['projectname'] . '%"';
		}
		if (!empty($qs['cusname'])) {
			$sql .= ' AND c.cusname LIKE "%' . $qs['cusname'] . '%"';
		}

		$total = $this->db->get_var($sql);
		$datas = array();
		$start = $this->rows * $this->page - $this->rows;
		if ($start < 0) {
			$start = 0;
		}

		$results = $this->db
				->get_results(
						str_replace('COUNT(*)',
								'b.id,b.pid,b.name,b.amount,b.allcost,b.costpaymentinfoids,c.cusname',
								$sql . ' LIMIT ' . $start . ',' . $this->rows));
		$datas = array();

		//已收款
		$receive_amount = $this->_getReceiveAmount();

		//已开票
		$invoice = $this->_getInvoice();

		//if (empty($qs['medianame'])) {
		//不区分媒体，查整体
		if ($results !== NULL) {
			$ri = $this->_getPaymentAndReceiveInvoice();

			foreach ($results as $result) {
				//var_dump($result->costpaymentinfoids);
				$costpaymentinfoids = explode('^', $result->costpaymentinfoids);
				$costpaymentinfoids = Array_Util::my_remove_array_other_value(
						$costpaymentinfoids, array(''));
				if (!empty($costpaymentinfoids)) {
					$costpays = $this->db
							->get_results(
									'SELECT id,payname,payamount FROM executive_paycost WHERE id IN ('
											. implode(',', $costpaymentinfoids)
											. ')');
				}

				$payname = array();
				if ($costpays !== NULL) {
					foreach ($costpays as $costpay) {
						$payname[] = $costpay->payname;
					}
				}

				$datas[] = array('a' => $result->pid, 'b' => $result->name,
						'c' => $result->cusname, 'd' => $result->amount,
						'e' => $result->allcost, 'f' => implode('，', $payname),
						'g' => empty($receive_amount[$result->pid]) ? 0
								: $receive_amount[$result->pid],
						'h' => empty($invoice[$result->pid]) ? 0
								: $invoice[$result->pid],
						'i' => empty($ri[$result->pid]['pay_amount']) ? 0
								: $ri[$result->pid]['pay_amount'],
						'j' => empty(
								$ri[$result->pid]['receive_invoice_amount']) ? 0
								: $ri[$result->pid]['receive_invoice_amount']);
			}
		}
		//}

		return urldecode(
				json_encode(array('total' => $total, 'rows' => $datas)));
	}

	public function getCustomPidInfoSearch() {
		$qs = $this->qs;
		$sql = 'SELECT COUNT(*) FROM 
(
SELECT MAX(isalter) AS isalter,pid FROM executive WHERE isok<>-1 GROUP BY pid
) a 
LEFT JOIN executive b
ON a.pid=b.pid AND a.isalter=b.isalter
LEFT JOIN contract_cus c 
ON b.cid=c.cid WHERE c.cusname="' . $qs['cusname'] . '"';
		$total = $this->db->get_var($sql);
		$datas = array();
		$start = $this->rows * $this->page - $this->rows;
		if ($start < 0) {
			$start = 0;
		}

		$results = $this->db
				->get_results(
						'SELECT b.id,b.pid,b.name,b.amount,b.allcost,b.costpaymentinfoids,c.cusname FROM 
(
SELECT MAX(isalter) AS isalter,pid FROM executive WHERE isok<>-1 GROUP BY pid
) a 
LEFT JOIN executive b
ON a.pid=b.pid AND a.isalter=b.isalter
LEFT JOIN contract_cus c 
ON b.cid=c.cid WHERE c.cusname="' . $qs['cusname'] . '" LIMIT ' . $start . ','
								. $this->rows);

		if ($results !== NULL) {
			//已收款
			$receive_amount = $this->_getReceiveAmount();

			//已开票
			$invoice = $this->_getInvoice();

			//已付款已收票
			$ri = $this->_getPaymentAndReceiveInvoice();

			foreach ($results as $result) {

				$costpaymentinfoids = explode('^', $result->costpaymentinfoids);
				$costpaymentinfoids = Array_Util::my_remove_array_other_value(
						$costpaymentinfoids, array(''));
				if (!empty($costpaymentinfoids)) {
					$costpays = $this->db
							->get_results(
									'SELECT id,payname,payamount FROM executive_paycost WHERE id IN ('
											. implode(',', $costpaymentinfoids)
											. ')');
				}

				$payname = array();
				if ($costpays !== NULL) {
					foreach ($costpays as $costpay) {
						$payname[] = $costpay->payname;
					}
				}

				$datas[] = array('a' => $result->pid, 'b' => $result->name,
						'c' => $result->amount, 'd' => $result->allcost,
						'e' => implode('，', $payname),
						'f' => empty($receive_amount[$result->pid]) ? 0
								: $receive_amount[$result->pid],
						'g' => empty($invoice[$result->pid]) ? 0
								: $invoice[$result->pid],
						'h' => empty($ri[$result->pid]['pay_amount']) ? 0
								: $ri[$result->pid]['pay_amount'],
						'i' => empty(
								$ri[$result->pid]['receive_invoice_amount']) ? 0
								: $ri[$result->pid]['receive_invoice_amount']);
			}
		}
		return urldecode(
				json_encode(array('total' => $total, 'rows' => $datas)));
	}

	public function getCustomInfoSearch() {
		$sql = 'SELECT DISTINCT(c.cusname) FROM 
(
SELECT MAX(isalter) AS isalter,pid FROM executive WHERE isok<>-1 GROUP BY pid
) a 
LEFT JOIN executive b
ON a.pid=b.pid AND a.isalter=b.isalter
LEFT JOIN contract_cus c 
ON b.cid=c.cid WHERE 1=1';
		$qs = $this->qs;
		if (!empty($qs['pid'])) {
			$sql .= ' AND a.pid LIKE "%' . $qs['pid'] . '%"';
		}
		if (!empty($qs['projectname'])) {
			$sql .= ' AND b.name LIKE "%' . $qs['projectname'] . '%"';
		}
		if (!empty($qs['cusname'])) {
			$sql .= ' AND c.cusname LIKE "%' . $qs['cusname'] . '%"';
		}
		//var_dump($sql);
		$results = $this->db->get_results($sql);
		$datas = array();
		$start = $this->rows * $this->page - $this->rows;
		if ($start < 0) {
			$start = 0;
		}
		$sql2 = $sql .= ' LIMIT ' . $start . ',' . $this->rows;
		$results2 = $this->db->get_results($sql2);
		if ($results2 !== NULL) {
			foreach ($results2 as $result) {
				$datas[] = array('aa' => $result->cusname,
						'bb' => '<input type="button" value="展开" class="btn" onclick="javascript:opencusname(\''
								. $result->cusname . '\')"/>');
			}
		}
		return urldecode(
				json_encode(array('total' => count($results), 'rows' => $datas)));
	}

	public function getDepositPaymentGD($isPersonApply = TRUE) {
		$start = $this->rows * $this->page - $this->rows;
		if ($start < 0) {
			$start = 0;
		}
		if (!in_array($this->sort,
				array('gd_time', 'gd_amount', 'payment_type', 'payment_bank'),
				TRUE)) {
			$this->sort = 'gd_time';
		}
		if (!in_array($this->order, array('asc', 'desc'), TRUE)) {
			$this->order = 'desc';
		}
		$qs = $this->qs;

		$total = $this->db
				->get_var(
						'SELECT COUNT(*) FROM finance_payment_deposit_gd WHERE apply_id='
								. $qs['apply_id'] . ' AND apply_type='
								. ($isPersonApply ? 1 : 2));
		$results = $this->db
				->get_results(
						'SELECT a.gd_time,a.gd_amount,a.payment_type,b.bank_name FROM finance_payment_deposit_gd a LEFT JOIN finance_nim_bankinfo b ON a.payment_bank=b.id WHERE apply_id='
								. $qs['apply_id'] . ' AND apply_type='
								. ($isPersonApply ? 1 : 2) . ' ORDER BY '
								. $this->sort . ' ' . $this->order . ' LIMIT '
								. $start . ',' . $this->rows);
		if ($results !== NULL) {
			foreach ($results as $result) {
				$datas[] = array('paymentdate' => $result->gd_time,
						'paymentamount' => $result->gd_amount,
						'paymentype' => urlencode(
								intval($result->payment_type) === 1 ? '现金'
										: '转账'),
						'paymentbank' => urlencode($result->bank_name));
			}
		}
		return urldecode(
				json_encode(array('total' => $total, 'rows' => $datas)));
	}

	public function getPaymentGD($isPersonApply = TRUE) {
		//if (!(Validate_Util::my_is_int($this->page) && $this->page > 0)) {
		//	$this->page = 1;
		//}
		//if (!(Validate_Util::my_is_int($this->rows) && $this->rows > 0)) {
		//	$this->rows = 10;
		//}
		$start = $this->rows * $this->page - $this->rows;
		if ($start < 0) {
			$start = 0;
		}
		if (!in_array($this->sort,
				array('gd_time', 'gd_amount', 'payment_type', 'payment_bank'),
				TRUE)) {
			$this->sort = 'gd_time';
		}
		if (!in_array($this->order, array('asc', 'desc'), TRUE)) {
			$this->order = 'desc';
		}
		$qs = $this->qs;
		$total = $this->db
				->get_var(
						'SELECT COUNT(*) FROM finance_payment_gd WHERE apply_id='
								. $qs['apply_id'] . ' AND apply_type='
								. ($isPersonApply ? 1 : 2));

		$results = $this->db
				->get_results(
						'SELECT a.gd_time,a.gd_amount,a.payment_type,b.bank_name FROM finance_payment_gd a LEFT JOIN finance_nim_bankinfo b ON a.payment_bank=b.id WHERE apply_id='
								. $qs['apply_id'] . ' AND apply_type='
								. ($isPersonApply ? 1 : 2) . ' ORDER BY '
								. $this->sort . ' ' . $this->order . ' LIMIT '
								. $start . ',' . $this->rows);
		$datas = array();
		if ($results !== NULL) {
			foreach ($results as $result) {
				$datas[] = array('paymentdate' => $result->gd_time,
						'paymentamount' => $result->gd_amount,
						'paymentype' => urlencode(
								intval($result->payment_type) === 1 ? '现金'
										: '转账'),
						'paymentbank' => urlencode($result->bank_name));
			}
		}
		return urldecode(
				json_encode(array('total' => $total, 'rows' => $datas)));
	}

	public function getHedgeByPids() {
		$qs = $this->qs;
		$search_action = $qs['search_action'];
		if (in_array($search_action,
				array('getHedgeReceiveByPids', 'getHedgePayByPids'), TRUE)) {
			$pids = $qs['pids'];
			//查找已有的分配情况
			$hedges_data = array();
			$results = $this->db
					->get_results(
							'SELECT pid,amount,hedge_type FROM finance_cash_hedge_list WHERE hedge_id='
									. intval($qs['id']) . ' AND isok<>-1');
			if ($results !== NULL) {
				foreach ($results as $result) {
					$hedges_data[$result->hedge_type][$result->pid] = $result
							->amount;
				}
			}

			if ($search_action === 'getHedgeReceiveByPids') {
				//已收
				$results = $this->db
						->get_results(
								'SELECT a.pid AS apid,a.amount AS receive_amount,b.pid AS bpid,b.amount AS invoice_amount FROM 
(
SELECT SUM(amount) AS amount ,pid from finance_receivables WHERE isok<>-1 AND pid IN ("'
										. implode('","', $pids)
										. '") GROUP BY pid
) a 
LEFT JOIN 
(
SELECT SUM(amount) AS amount ,pid FROM finance_invoice WHERE isok<>-1 AND pid IN ("'
										. implode('","', $pids)
										. '") GROUP BY pid
) b
ON a.pid=b.pid
UNION
SELECT a.pid AS apid,a.amount AS receive_amount,b.pid AS bpid,b.amount AS invoice_amount FROM 
(
SELECT SUM(amount) AS amount ,pid from finance_receivables WHERE isok<>-1 AND pid IN ("'
										. implode('","', $pids)
										. '") GROUP BY pid
) a 
RIGHT JOIN 
(
SELECT SUM(amount) AS amount ,pid FROM finance_invoice WHERE isok<>-1 AND pid IN ("'
										. implode('","', $pids)
										. '") GROUP BY pid
) b
ON a.pid=b.pid');

				$receive_and_invoice = array();
				if ($results !== NULL) {
					foreach ($results as $result) {
						$pid = !empty($result->apid) ? $result->apid
								: $result->bpid;
						$receive_and_invoice[$pid] = array(
								'receive_amount' => $result->receive_amount,
								'invoice_amount' => $result->invoice_amount);
					}
				}

				$res = $this->db
						->get_results(
								'SELECT b.pid,b.cid,c.cusname,b.name,b.amount FROM 
(SELECT MAX(isalter) AS isalter,pid FROM executive WHERE pid IN ("'
										. implode('","', $pids)
										. '") GROUP BY pid)a 
LEFT JOIN executive b 
ON a.isalter=b.isalter AND a.pid=b.pid
LEFT JOIN contract_cus c
ON b.cid=c.cid');

				$data = array();
				$data['total'] = count($res);
				$rows = array();

				foreach ($res as $re) {
					$ee = empty(
							$receive_and_invoice[$re->pid]['receive_amount']) ? 0
							: $receive_and_invoice[$re->pid]['receive_amount'];
					$rows[] = array('xx' => $re->pid, 'aa' => $re->pid,
							'bb' => urlencode($re->cusname),
							'cc' => urlencode($re->name), 'dd' => $re->amount,
							'ee' => $ee,
							'ff' => empty(
									$receive_and_invoice[$re->pid]['invoice_amount']) ? 0
									: $receive_and_invoice[$re->pid]['invoice_amount'],
							'gg' => '<input type="text" name="receive_'
									. $re->pid . '" id="receive_' . $re->pid
									. '" style="height:20px;" class="validate[required,max['
									. $ee . ']]" value="'
									. ($hedges_data[1][$re->pid]) . '"/>');
				}
				$data['rows'] = $rows;
				return urldecode(json_encode($data));
			} else {
				//已付
				$results = $this->db
						->get_results(
								'SELECT a.pid AS apid,a.amount AS pay_amount,b.pid AS bpid,b.amount AS receive_invoice_amount FROM
(
SELECT SUM(gd_amount) AS amount,pid FROM finance_payment_gd WHERE 1=1 AND pid IN ("'
										. implode('","', $pids)
										. '") GROUP BY pid
) a
LEFT JOIN
(
SELECT SUM(sum_amount) AS amount,pid FROM finance_receiveinvoice_pid_list WHERE isok<>-1 AND pid IN ("'
										. implode('","', $pids)
										. '") GROUP BY pid
) b
ON a.pid=b.pid
UNION
SELECT a.pid AS apid,a.amount AS pay_amount,b.pid AS bpid,b.amount AS receive_invoice_amount FROM
(
SELECT SUM(gd_amount) AS amount,pid FROM finance_payment_gd WHERE 1=1 AND pid IN ("'
										. implode('","', $pids)
										. '") GROUP BY pid
) a
RIGHT JOIN
(
SELECT SUM(sum_amount) AS amount,pid FROM finance_receiveinvoice_pid_list WHERE isok<>-1 AND pid IN ("'
										. implode('","', $pids)
										. '") GROUP BY pid
) b
ON a.pid=b.pid');
				$pay_and_receiveinvoice = array();
				if ($results !== NULL) {
					foreach ($results as $result) {
						$pid = !empty($result->apid) ? $result->apid
								: $result->bpid;
						$pay_and_receiveinvoice[$pid] = array(
								'pay_amount' => $result->pay_amount,
								'receive_invoice_amount' => $result
										->receive_invoice_amount);
					}
				}

				$res = $this->db
						->get_results(
								'SELECT b.pid,b.cid,c.cusname,b.name,b.amount FROM 
(SELECT MAX(isalter) AS isalter,pid FROM executive WHERE pid IN ("'
										. implode('","', $pids)
										. '") GROUP BY pid)a 
LEFT JOIN executive b 
ON a.isalter=b.isalter AND a.pid=b.pid
LEFT JOIN contract_cus c
ON b.cid=c.cid');

				$data = array();
				$data['total'] = count($res);
				$rows = array();

				foreach ($res as $re) {
					$e = empty($pay_and_receiveinvoice[$re->pid]['pay_amount']) ? 0
							: $pay_and_receiveinvoice[$re->pid]['pay_amount'];
					$f = empty(
							$pay_and_receiveinvoice[$re->pid]['receive_invoice_amount']) ? 0
							: $pay_and_receiveinvoice[$re->pid]['receive_invoice_amount'];
					$rows[] = array('xxx' => $re->pid, 'aaa' => $re->pid,
							'bbb' => urlencode($re->cusname),
							'ccc' => urlencode($re->name),
							'ggg' => '<input type="text" name="pay_' . $re->pid
									. '" id="pay_' . $re->pid
									. '" style="height:20px;" class="validate[required,max['
									. $e . ']]" value="'
									. ($hedges_data[2][$re->pid]) . '"/>',
							'ddd' => $re->amount, 'eee' => $e, 'fff' => $f,
							'hhh' => '', 'iii' => $re->amount - $e,
							'jjj' => $e - $f);
				}
				$data['rows'] = $rows;
				return urldecode(json_encode($data));
			}

		}
		return json_encode(array('total' => 0, 'rows' => array()));
	}

	public function getPayFirstByPaymentApply() {
		$qs = $this->qs;

		$base_sql = 'SELECT b.pid,b.cid,c.cusname,d.realname,d.username,b.name,b.amount,f.depname,g.companyname
FROM 
(SELECT MAX(isalter) AS isalter,pid FROM executive WHERE isok<>-1 GROUP BY pid) a
LEFT JOIN executive b
ON a.isalter=b.isalter AND a.pid=b.pid
LEFT JOIN contract_cus c
ON b.cid=c.cid
LEFT JOIN users d
ON b.principal=d.uid
LEFT JOIN users e
ON b.user=e.uid
LEFT JOIN hr_department f
ON e.dep=f.id
LEFT JOIN hr_company g
ON f.cityid=g.id';

		if ($qs['stype'] === 'pc') {
			//个人申请合同付款
			$sql = 'SELECT n.*,m.nim_pay_first_amount,m.payment_amount
FROM finance_payment_person_apply_list m
LEFT JOIN 
(' . $base_sql . ') n
ON m.pid=n.pid 
WHERE m.apply_id=' . intval($qs['apply_id']);

		} else if ($qs['stype'] === 'pd') {
			//个人申请保证金
			$sql = 'SELECT n.*,m.nim_pay_first_amount,m.payment_amount
FROM finance_payment_person_deposit_apply_list m
LEFT JOIN 
(' . $base_sql . ') n
ON c.pid=c.pid 
WHERE m.apply_id=' . intval($qs['apply_id']);

		} else if ($qs['stype'] === 'mc') {
			//媒体批量申请合同款
			$sql = 'SELECT n.*,m.nim_pay_first_amount,m.payment_amount
FROM finance_payment_media_apply_list m
LEFT JOIN 
(' . $base_sql . ') n
ON m.pid=n.pid 
WHERE m.apply_id=' . intval($qs['apply_id']);

		} else if ($qs['stype'] === 'md') {
			//媒体批量申请保证金
		}

		$receive_and_invoice = $this->_getReceivablesAndInvoice();
		$pay_and_receiveinvoice = $this->_getPaymentAndReceiveInvoice();
		$results = $this->db->get_results($sql);
		$data = array();
		$rows = array();
		$pids = array();
		$data['total'] = count($results);
		if ($results !== NULL) {
			foreach ($results as $result) {
				$g = empty($receive_and_invoice[$result->pid]['receive_amount']) ? 0
						: $receive_and_invoice[$result->pid]['receive_amount'];
				$h = empty($receive_and_invoice[$result->pid]['invoice_amount']) ? 0
						: $receive_and_invoice[$result->pid]['invoice_amount'];
				$j = empty($pay_and_receiveinvoice[$result->pid]['pay_amount']) ? 0
						: $pay_and_receiveinvoice[$result->pid]['pay_amount'];
				$pids[] = $result->pid;
				$rows[] = array('x' => $result->pid, 'a' => $result->pid,
						'b' => urlencode($result->cusname),
						'c' => urlencode(
								$result->companyname . ' ' . $result->depname),
						'd' => urlencode(
								$result->realname . ' （' . $result->username
										. '）'),
						'e' => urlencode($result->name),
						'f' => $result->amount, 'g' => $g, 'h' => $h,
						'i' => $result->amount - $g, 'j' => $j,
						'k' => $result->payment_amount,
						'l' => $result->nim_pay_first_amount,
						'm' => $result->amount - $g,
						'n' => min(
								array($result->nim_pay_first_amount,
										abs($result->amount - $g))),
						'o' => '<input type="button" value="更新" class="btn"/>',);
			}
		}
		$data['rows'] = $rows;
		//$data['pids'] = implode(',', $pids);
		return urldecode(json_encode($data));
	}

	private function _getPaymentAndReceiveInvoice($pid = NULL, $isEqulas = TRUE) {
		$subsql = '';
		if (!empty($pid)) {
			if ($isEqulas) {
				$subsql .= ' AND pid="' . $pid . '"';
			} else {
				$subsql .= ' AND pid LIKE "%' . $pid . '%"';
			}
		}
		$results = $this->db
				->get_results(
						'SELECT a.pid AS apid,a.amount AS pay_amount,b.pid AS bpid,b.amount AS receive_invoice_amount FROM
	(
	SELECT SUM(gd_amount) AS amount,pid FROM finance_payment_gd WHERE 1=1'
								. $subsql
								. ' GROUP BY pid
	) a
	LEFT JOIN
	(
	SELECT SUM(sum_amount) AS amount,pid FROM finance_receiveinvoice_pid_list WHERE isok<>-1 '
								. $subsql
								. ' GROUP BY pid
	) b
	ON a.pid=b.pid
	UNION
	SELECT a.pid AS apid,a.amount AS pay_amount,b.pid AS bpid,b.amount AS receive_invoice_amount FROM
	(
	SELECT SUM(gd_amount) AS amount,pid FROM finance_payment_gd WHERE 1=1'
								. $subsql
								. ' GROUP BY pid
	) a
	RIGHT JOIN
	(
	SELECT SUM(sum_amount) AS amount,pid FROM finance_receiveinvoice_pid_list WHERE isok<>-1 '
								. $subsql
								. ' GROUP BY pid
	) b
	ON a.pid=b.pid');
		$pay_and_receiveinvoice = array();
		if ($results !== NULL) {
			foreach ($results as $result) {
				$tpid = !empty($result->apid) ? $result->apid : $result->bpid;
				$pay_and_receiveinvoice[$tpid] = array(
						'pay_amount' => $result->pay_amount,
						'receive_invoice_amount' => $result
								->receive_invoice_amount);
			}
		}
		return $pay_and_receiveinvoice;
	}

	private function _getReceivablesAndInvoice($pid = NULL, $isEqulas = TRUE) {
		$subql = '';
		if (!empty($pid)) {
			if ($isEqulas) {
				//等于
				$subql .= ' AND pid="' . $pid . '"';
			} else {
				//LIKE
				$subql .= ' AND pid LIKE "%' . $pid . '%"';
			}
		}
		$results = $this->db
				->get_results(
						'SELECT a.pid AS apid,a.amount AS receive_amount,b.pid AS bpid,b.amount AS invoice_amount FROM 
		(
		SELECT SUM(amount) AS amount ,pid from finance_receivables WHERE isok<>-1'
								. $subql
								. ' GROUP BY pid
		) a 
		LEFT JOIN 
		(
		SELECT SUM(amount) AS amount ,pid FROM finance_invoice WHERE isok<>-1'
								. $subql
								. ' GROUP BY pid
		) b
		ON a.pid=b.pid
		UNION
		SELECT a.pid AS apid,a.amount AS receive_amount,b.pid AS bpid,b.amount AS invoice_amount FROM 
		(
		SELECT SUM(amount) AS amount ,pid from finance_receivables WHERE isok<>-1'
								. $subql
								. ' GROUP BY pid
		) a 
		RIGHT JOIN 
		(
		SELECT SUM(amount) AS amount ,pid FROM finance_invoice WHERE isok<>-1'
								. $subql
								. ' GROUP BY pid
		) b
		ON a.pid=b.pid');
		$receive_and_invoice = array();
		if ($results !== NULL) {
			foreach ($results as $result) {
				$pid = !empty($result->apid) ? $result->apid : $result->bpid;
				$receive_and_invoice[$pid] = array(
						'receive_amount' => $result->receive_amount,
						'invoice_amount' => $result->invoice_amount);
			}
		}
		return $receive_and_invoice;
	}

	public function getHedgeSearch() {
		$start = $this->rows * $this->page - $this->rows;
		if ($start < 0) {
			$start = 0;
		}
		$qs = $this->qs;
		$search_action = $qs['search_action'];
		if (in_array($search_action, array('getHedgeReceive', 'getHedgePay'),
				TRUE)) {
			if ($search_action === 'getHedgeReceive') {
				//已收
				$results = $this->db
						->get_results(
								'SELECT a.pid AS apid,a.amount AS receive_amount,b.pid AS bpid,b.amount AS invoice_amount FROM 
(
SELECT SUM(amount) AS amount ,pid from finance_receivables WHERE isok<>-1'
										. (!empty($qs['pid']) ? ' AND pid LIKE "%'
														. $qs['pid'] . '%"' : '')
										. ' GROUP BY pid
) a 
LEFT JOIN 
(
SELECT SUM(amount) AS amount ,pid FROM finance_invoice WHERE isok<>-1'
										. (!empty($qs['pid']) ? ' AND pid LIKE "%'
														. $qs['pid'] . '%"' : '')
										. ' GROUP BY pid
) b
ON a.pid=b.pid
UNION
SELECT a.pid AS apid,a.amount AS receive_amount,b.pid AS bpid,b.amount AS invoice_amount FROM 
(
SELECT SUM(amount) AS amount ,pid from finance_receivables WHERE isok<>-1'
										. (!empty($qs['pid']) ? ' AND pid LIKE "%'
														. $qs['pid'] . '%"' : '')
										. ' GROUP BY pid
) a 
RIGHT JOIN 
(
SELECT SUM(amount) AS amount ,pid FROM finance_invoice WHERE isok<>-1'
										. (!empty($qs['pid']) ? ' AND pid LIKE "%'
														. $qs['pid'] . '%"' : '')
										. ' GROUP BY pid
) b
ON a.pid=b.pid');
				$receive_and_invoice = array();
				if ($results !== NULL) {
					foreach ($results as $result) {
						$pid = !empty($result->apid) ? $result->apid
								: $result->bpid;
						$receive_and_invoice[$pid] = array(
								'receive_amount' => $result->receive_amount,
								'invoice_amount' => $result->invoice_amount);
					}
				}

				$wheresql = array();
				if (!empty($qs['projectname'])) {
					$wheresql[] = 'b.name LIKE "%' . $qs['projectname'] . '%"';
				}
				if (!empty($qs['cusname'])) {
					$wheresql[] = 'c.cusname LIKE "%' . $qs['cusname'] . '%"';
				}
				$res = $this->db
						->get_results(
								'SELECT b.pid,b.cid,c.cusname,b.name,b.amount FROM 
(SELECT MAX(isalter) AS isalter,pid FROM executive '
										. (!empty($qs['pid']) ? 'WHERE pid LIKE "%'
														. $qs['pid'] . '%"' : '')
										. ' GROUP BY pid)a 
LEFT JOIN executive b 
ON a.isalter=b.isalter AND a.pid=b.pid
LEFT JOIN contract_cus c
ON b.cid=c.cid'
										. (!empty($wheresql) ? ' WHERE '
														. implode(' AND ',
																$wheresql) : '')
										. ' LIMIT ' . $start . ','
										. $this->rows);

				$data = array();
				$data['total'] = intval(
						$this->db
								->get_var(
										'SELECT COUNT(*) FROM 
(SELECT MAX(isalter) AS isalter,pid FROM executive '
												. (!empty($qs['pid']) ? 'WHERE pid LIKE "%'
																. $qs['pid']
																. '%"' : '')
												. ' GROUP BY pid)a 
LEFT JOIN executive b 
ON a.isalter=b.isalter AND a.pid=b.pid
LEFT JOIN contract_cus c
ON b.cid=c.cid'
												. (!empty($wheresql) ? ' WHERE '
																. implode(
																		' AND ',
																		$wheresql)
														: '')));
				$rows = array();

				foreach ($res as $re) {
					$rows[] = array('x' => $re->pid, 'a' => $re->pid,
							'b' => urlencode($re->cusname),
							'c' => urlencode($re->name), 'd' => $re->amount,
							'e' => empty(
									$receive_and_invoice[$re->pid]['receive_amount']) ? 0
									: $receive_and_invoice[$re->pid]['receive_amount'],
							'f' => empty(
									$receive_and_invoice[$re->pid]['invoice_amount']) ? 0
									: $receive_and_invoice[$re->pid]['invoice_amount']);
				}
				$data['rows'] = $rows;
				return urldecode(json_encode($data));

			} else {
				//已付
				$results = $this->db
						->get_results(
								'SELECT a.pid AS apid,a.amount AS pay_amount,b.pid AS bpid,b.amount AS receive_invoice_amount FROM
(
SELECT SUM(gd_amount) AS amount,pid FROM finance_payment_gd WHERE 1=1'
										. (!empty($qs['pid']) ? ' AND pid LIKE "%'
														. $qs['pid'] . '%"' : '')
										. ' GROUP BY pid
) a
LEFT JOIN
(
SELECT SUM(sum_amount) AS amount,pid FROM finance_receiveinvoice_pid_list WHERE isok<>-1 '
										. (!empty($qs['pid']) ? ' AND pid LIKE "%'
														. $qs['pid'] . '%"' : '')
										. ' GROUP BY pid
) b
ON a.pid=b.pid
UNION
SELECT a.pid AS apid,a.amount AS pay_amount,b.pid AS bpid,b.amount AS receive_invoice_amount FROM
(
SELECT SUM(gd_amount) AS amount,pid FROM finance_payment_gd WHERE 1=1'
										. (!empty($qs['pid']) ? ' AND pid LIKE "%'
														. $qs['pid'] . '%"' : '')
										. ' GROUP BY pid
) a
RIGHT JOIN
(
SELECT SUM(sum_amount) AS amount,pid FROM finance_receiveinvoice_pid_list WHERE isok<>-1 '
										. (!empty($qs['pid']) ? ' AND pid LIKE "%'
														. $qs['pid'] . '%"' : '')
										. ' GROUP BY pid
) b
ON a.pid=b.pid');
				$pay_and_receiveinvoice = array();
				if ($results !== NULL) {
					foreach ($results as $result) {
						$pid = !empty($result->apid) ? $result->apid
								: $result->bpid;
						$pay_and_receiveinvoice[$pid] = array(
								'pay_amount' => $result->pay_amount,
								'receive_invoice_amount' => $result
										->receive_invoice_amount);
					}
				}

				$wheresql = array();
				if (!empty($qs['projectname'])) {
					$wheresql[] = 'b.name LIKE "%' . $qs['projectname'] . '%"';
				}
				if (!empty($qs['cusname'])) {
					$wheresql[] = 'c.cusname LIKE "%' . $qs['cusname'] . '%"';
				}
				$res = $this->db
						->get_results(
								'SELECT b.pid,b.cid,c.cusname,b.name,b.amount FROM 
(SELECT MAX(isalter) AS isalter,pid FROM executive '
										. (!empty($qs['pid']) ? 'WHERE pid LIKE "%'
														. $qs['pid'] . '%"' : '')
										. ' GROUP BY pid)a 
LEFT JOIN executive b 
ON a.isalter=b.isalter AND a.pid=b.pid
LEFT JOIN contract_cus c
ON b.cid=c.cid'
										. (!empty($wheresql) ? ' WHERE '
														. implode(' AND ',
																$wheresql) : '')
										. ' LIMIT ' . $start . ','
										. $this->rows);

				$data = array();
				$data['total'] = intval(
						$this->db
								->get_var(
										'SELECT COUNT(*) FROM 
(SELECT MAX(isalter) AS isalter,pid FROM executive '
												. (!empty($qs['pid']) ? 'WHERE pid LIKE "%'
																. $qs['pid']
																. '%"' : '')
												. ' GROUP BY pid)a 
LEFT JOIN executive b 
ON a.isalter=b.isalter AND a.pid=b.pid
LEFT JOIN contract_cus c
ON b.cid=c.cid'
												. (!empty($wheresql) ? ' WHERE '
																. implode(
																		' AND ',
																		$wheresql)
														: '')));
				$rows = array();

				foreach ($res as $re) {
					$e = empty($pay_and_receiveinvoice[$re->pid]['pay_amount']) ? 0
							: $pay_and_receiveinvoice[$re->pid]['pay_amount'];
					$f = empty(
							$pay_and_receiveinvoice[$re->pid]['receive_invoice_amount']) ? 0
							: $pay_and_receiveinvoice[$re->pid]['receive_invoice_amount'];
					$rows[] = array('x' => $re->pid, 'a' => $re->pid,
							'b' => urlencode($re->cusname),
							'c' => urlencode($re->name), 'g' => '',
							'd' => $re->amount, 'e' => $e, 'f' => $f,
							'h' => $re->amount - $e, 'i' => $e - $f);
				}
				$data['rows'] = $rows;
				return urldecode(json_encode($data));
			}

		}
		return json_encode(array('total' => 0, 'rows' => array()));
	}

	/*
	public function searchPidPayment(){
	    $start = $this->rows * $this->page - $this->rows;
	    if ($start < 0) {
	        $start = 0;
	    }
	    $qs = $this->qs;
	    
	    $this->db->get_results('SELECT m.*,n.cid,n.allcost,n.costpaymentinfoids,o.cusname
	FROM 
	(
	SELECT a.*
	FROM(
	SELECT pid,gd_time FROM finance_payment_gd ' . (!empty($qs['pid']) ? ' WHERE pid LIKE "%' . $qs['pid'] . '%"' : '') . ' ORDER BY pid,gd_time DESC
	) a GROUP BY a.pid
	) m
	LEFT JOIN v_last_executive n
	ON m.pid=n.pid 
	LEFT JOIN contract_cus o
	ON n.cid=o.cid ' . (!empty($qs['pid']) ? ' WHERE o.cusname LIKE "%' . $qs['cusname'] . '%"' : ''));
	    
	    return json_encode(array('total' => 0, 'rows' => array()));
	}
	 */

	public function getPidInfo() {
		$qs = $this->qs;
		$datas = array();

		$sql = 'SELECT a.pid,b.costpaymentinfoids,c.cusname FROM
(
SELECT MAX(isalter) AS isalter,pid FROM executive WHERE isok<>-1 '
				. (!empty($qs['pid']) ? ' AND pid LIKE "%' . $qs['pid'] . '%"'
						: '')
				. ' GROUP BY pid
) a
LEFT JOIN
executive b
ON a.pid=b.pid AND a.isalter=b.isalter
LEFT JOIN contract_cus c
ON b.cid=c.cid
WHERE b.allcost>0 '
				. (!empty($qs['cid']) ? ' AND b.cid LIKE "%' . $qs['cid']
								. '%"' : '')
				. (!empty($qs['cusname']) ? ' AND c.cusname LIKE "%'
								. $qs['cusname'] . '%"' : '');

		$results = $this->db->get_results($sql);
		$costids = array();
		$costinfos = array();
		$costgdinfos = array();
		if ($results !== NULL) {
			foreach ($results as $result) {
				$costpaymentinfoids = explode('^', $result->costpaymentinfoids);
				foreach ($costpaymentinfoids as $costpaymentinfoid) {
					if (!empty($costpaymentinfoid)) {
						$costids['costid'][] = $costpaymentinfoid;
						//$costids['pid_costid'][$result->pid][] = $costpaymentinfoids;
						$costids['pid_costid'][] = array(
								'pid' => $result->pid,
								'costpaymentinfoid' => $costpaymentinfoid,
								'cusname' => $result->cusname);
					}
				}
			}

			if (!empty($costids)) {
				//获得媒体数据
				$media_costs = $this->db
						->get_results(
								'SELECT id,payamount,payname,category,isagent2 FROM executive_paycost WHERE id IN ('
										. implode(',', $costids['costid'])
										. ')'
										. (!empty($qs['medianame']) ? ' AND payment LIKE "%'
														. $qs['medianame']
														. '%"' : ''));
				if ($media_costs !== NULL) {
					foreach ($media_costs as $mc) {
						$costinfos[$mc->id] = array('media' => $mc->payname,
								'payamount' => $mc->payamount);
					}
				}

				//获得归档金额
				$gds = $this->db
						->get_results(
								'SELECT SUM(gd_amount) AS amount,paycostid,pid FROM finance_payment_gd GROUP BY pid,paycostid');
				if ($gds !== NULL) {
					foreach ($gds as $gd) {
						$costgdinfos[$gd->pid . '_' . $gd->paycostid] = $gd
								->amount;
					}
				}

				foreach ($costids['pid_costid'] as $val) {
					$datas[] = array(
							'x' => $val['pid'] . '_'
									. $val['costpaymentinfoid'],
							'a' => $val['pid'],
							'b' => urlencode($val['cusname']),
							'c' => urlencode(
									$costinfos[$val['costpaymentinfoid']]['media']),
							'd' => $costinfos[$val['costpaymentinfoid']]['payamount'],
							'f' => $costgdinfos[$val['pid'] . '_'
									. $val['costpaymentinfoid']]);
				}
			}
		}

		$start = $this->rows * $this->page - $this->rows;
		if ($start < 0) {
			$start = 0;
		}
		$tmp = array();
		if (!empty($datas)) {
			$tmp = array_slice($datas, $start, $this->rows);
		}
		return urldecode(
				json_encode(array('total' => count($datas), 'rows' => $tmp)));
	}

	public function getPaidDepositInfo() {
		$qs = $this->qs;
		$datas = array();
		$where = array();
		if (!empty($qs['cid'])) {
			$where[] = 'a.cid LIKE "%' . $qs['cid'] . '%"';
		}
		if (!empty($qs['cusname'])) {
			$where[] = 'd.cusname LIKE "%' . $qs['cusname'] . '%"';
		}
		if (!empty($qs['medianame'])) {
			$where[] = 'c.media_name LIKE "%' . $qs['medianame'] . '%"';
		}

		$sql = 'SELECT a.id,a.apply_id,a.payment_id,a.list_id,a.cid,a.gd_amount,c.media_name,d.cusname,\'p\' AS ptype
FROM finance_payment_deposit_gd a 
LEFT JOIN finance_payment_person_deposit_apply b
ON a.apply_id=b.id
LEFT JOIN finance_payment_media_info c
ON b.media_info_id=c.id
LEFT JOIN contract_cus d
ON a.cid=d.cid
WHERE a.isok=1 AND a.apply_type=1 '
				. (!empty($where) ? ' AND ' . implode(' AND ', $where) : '')
				. '
UNION ALL
SELECT a.id,a.apply_id,a.payment_id,a.list_id,a.cid,a.gd_amount,c.media_name,d.cusname,\'p\' AS ptype
FROM finance_payment_deposit_gd a 
LEFT JOIN finance_payment_media_deposit_apply b
ON a.apply_id=b.id
LEFT JOIN finance_payment_media_info c
ON b.media_info_id=c.id
LEFT JOIN contract_cus d
ON a.cid=d.cid
WHERE a.isok=1 AND a.apply_type=2 '
				. (!empty($where) ? ' AND ' . implode(' AND ', $where) : '');

		$start = $this->rows * $this->page - $this->rows;
		if ($start < 0) {
			$start = 0;
		}

		$total = $this->db->get_var('SELECT COUNT(*) FROM (' . $sql . ') z');
		$results = $this->db
				->get_results(
						'SELECT * FROM (' . $sql . ') z LIMIT ' . $start . ','
								. $this->rows);
		if ($results !== NULL) {
			foreach ($results as $result) {
				$datas[] = array('x' => $result->id, 'a' => $result->cid,
						'b' => urlencode($result->cusname),
						'c' => urlencode($result->media_name),
						'd' => $result->gd_amount,);
			}
		}
		return urldecode(
				json_encode(array('total' => count($results), 'rows' => $datas)));
	}

	public function getPaidPidFinanceInfo() {
		$qs = $this->qs;
		$datas = array();
		$results = $this->db
				->get_results(
						'SELECT n.*,o.cusname,o.allcost,o.amount FROM
(
SELECT SUM(m.gd_amount) AS gd_amount,m.pid,m.media_name,m.mid FROM
(
SELECT a.pid,a.gd_amount,c.media_name,c.id AS mid FROM finance_payment_gd a
LEFT JOIN finance_payment_person_apply b
ON a.apply_id=b.id
LEFT JOIN finance_payment_media_info c
ON b.media_info_id=c.id
WHERE a.apply_type=1 '
								. (!empty($qs['medianame']) ? ' AND c.media_name LIKE "%'
												. $qs['medianame'] . '%"' : '')
								. '
UNION ALL
SELECT a.pid,a.gd_amount,c.media_name,c.id AS mid FROM finance_payment_gd a
LEFT JOIN finance_payment_media_apply b
ON a.apply_id=b.id
LEFT JOIN finance_payment_media_info c
ON b.media_info_id=c.id
WHERE a.apply_type=2 '
								. (!empty($qs['medianame']) ? ' AND c.media_name LIKE "%'
												. $qs['medianame'] . '%"' : '')
								. '
) m
GROUP BY m.pid,m.mid
) n
LEFT JOIN 
(
SELECT aa.pid,bb.cid,bb.allcost,bb.amount,cc.cusname FROM
(
SELECT MAX(isalter) AS isalter,pid FROM executive WHERE isok<>-1 '
								. (!empty($qs['pid']) ? ' AND pid LIKE "%'
												. $qs['pid'] . '%"' : '')
								. ' GROUP BY pid
) aa
LEFT JOIN executive bb
ON aa.pid=bb.pid AND aa.isalter=bb.isalter
LEFT JOIN contract_cus cc
ON bb.cid=cc.cid WHERE 1=1 '
								. (!empty($qs['cusname']) ? ' AND cc.cusname LIKE "%'
												. $qs['cusname'] . '%"' : '')
								. '
) o
ON n.pid=o.pid');

		if ($results !== NULL) {
			foreach ($results as $result) {
				$receive_invoice = 0;
				$datas[] = array('x' => $result->pid . '_' . $result->mid,
						'a' => $result->pid, 'b' => $result->cusname,
						'c' => $result->media_name, 'd' => $result->allcost,
						'e' => $result->gd_amount,
						'f' => $result->gd_amount - $receive_invoice,
						'g' => $result->amount - $result->gd_amount,
						'h' => $receive_invoice,);
			}
		}
		return urldecode(
				json_encode(array('total' => count($results), 'rows' => $datas)));
	}

	public function getDepositDeduction() {
		$qs = $this->qs;
		$datas = array();
		$results = $this->db
				->get_results(
						'SELECT a.deposit_gd_id,a.deduction_amount,b.cid,b.gd_amount,c.cusname,e.media_name AS media,\'p\' AS ptype  FROM 
finance_payment_deposit_deduction a
LEFT JOIN 
finance_payment_deposit_gd b
ON a.deposit_gd_id=b.id
LEFT JOIN contract_cus c
ON b.cid=c.cid
LEFT JOIN finance_payment_person_deposit_apply d
ON b.apply_id=d.id
LEFT JOIN finance_payment_media_info e
ON d.media_info_id=e.id
WHERE a.apply_id=' . $qs['apply_id']
								. ' AND a.isok=1 AND a.apply_type=1 AND a.payment_type='
								. $qs['payment_type']
								. '
UNION ALL	
SELECT a.deposit_gd_id,a.deduction_amount,b.cid,b.gd_amount,c.cusname,e.media_name AS media,\'m\' AS ptype  FROM 
finance_payment_deposit_deduction a
LEFT JOIN 
finance_payment_deposit_gd b
ON a.deposit_gd_id=b.id
LEFT JOIN contract_cus c
ON b.cid=c.cid
LEFT JOIN finance_payment_media_deposit_apply d
ON b.apply_id=d.id
LEFT JOIN finance_payment_media_info e
ON d.media_info_id=e.id
WHERE a.apply_id=' . $qs['apply_id']
								. ' AND a.isok=1  AND a.apply_type=2 AND a.payment_type='
								. $qs['payment_type']);
		$isshow = intval($qs['isshow']) === 1;
		if ($results !== NULL) {
			foreach ($results as $result) {
				$key = $result->deposit_gd_id . '_' . $result->ptype;
				$datas[] = array('dck' => $key, 'dcid' => $result->cid,
						'dcusname' => urlencode($result->cusname),
						'dmedia' => urlencode($result->media),
						'dgd_amount' => $result->gd_amount,
						'ddeduction' => $isshow ? $result->deduction_amount
								: '<input type="text" style="height:20px;" value="-'
										. $result->deduction_amount
										. '" name="ddeduction_' . $key
										. '" id="ddeduction_' . $key
										. '" class="validate[required,max[0],min[-'
										. $result->deduction_amount
										. ']]" onblur="javascript:depositonblur(this);"/>');
			}
		}
		return urldecode(
				json_encode(array('total' => count($results), 'rows' => $datas)));
	}

	public function getContractPayment() {
		$start = $this->rows * $this->page - $this->rows;
		if ($start < 0) {
			$start = 0;
		}
		$qs = $this->qs;
		$where = array();
		if (!empty($qs['searchpaymentdate'])) {
			$where[] = 'b.payment_date="' . $qs['searchpaymentdate'] . '"';
		}
		if (!empty($qs['searchmedianame'])) {
			$where[] = 'c.media_name LIKE "%' . $qs['searchmedianame'] . '%"';
		}

		$sql = 'SELECT b.id,b.addtime,b.payment_date,b.payment_amount_plan,c.media_name,d.realname,d.username,\'pc\' AS ptype
FROM
(
SELECT apply_id FROM finance_payment_gd WHERE apply_type=1 GROUP BY apply_id
) a
LEFT JOIN finance_payment_person_apply b
ON a.apply_id=b.id
LEFT JOIN finance_payment_media_info c
ON b.media_info_id=c.id
LEFT JOIN users d
ON b.user=d.uid' . (!empty($where) ? ' WHERE ' . implode(' AND ', $where) : '')
				. '
UNION ALL
SELECT b.id,b.addtime,b.payment_date,b.payment_amount_plan,c.media_name,d.realname,d.username,\'mc\' AS ptype
FROM
(
SELECT apply_id FROM finance_payment_gd WHERE apply_type=2 GROUP BY apply_id
) a
LEFT JOIN finance_payment_media_apply b
ON a.apply_id=b.id
LEFT JOIN finance_payment_media_info c
ON b.media_info_id=c.id
LEFT JOIN users d
ON b.user=d.uid' . (!empty($where) ? ' WHERE ' . implode(' AND ', $where) : '');

		$total = $this->db->get_var('SELECT COUNT(*) FROM (' . $sql . ') z');
		$results = $this->db
				->get_results(
						'SELECT * FROM (' . $sql . ') z  LIMIT ' . $start . ','
								. $this->rows);
		$datas = array();
		if ($results !== NULL) {
			foreach ($results as $result) {
				$datas[] = array('ck' => $result->id . '_' . $result->ptype,
						'a' => $result->addtime, 'b' => $result->payment_date,
						'c' => urlencode($result->media_name),
						'd' => $result->payment_amount_plan,
						'e' => urlencode(
								$result->realname . '（' . $result->username
										. '）'));
			}
		}
		return urldecode(
				json_encode(array('total' => $total, 'rows' => $datas)));
	}

	public function getDepositPayment() {
		$start = $this->rows * $this->page - $this->rows;
		if ($start < 0) {
			$start = 0;
		}
		if (!in_array($this->sort,
				array('cid', 'cusname', 'media', 'gd_amount'), TRUE)) {
			$this->sort = 'cid';
		}
		if (!in_array($this->order, array('asc', 'desc'), TRUE)) {
			$this->order = 'desc';
		}
		$qs = $this->qs;

		$where = array();
		if (!empty($qs['searchmedianame'])) {
			$where[] = 'c.media_name LIKE "%' . $qs['searchmedianame'] . '%"';
		}
		if (!empty($qs['searchcusname'])) {
			$where[] = 'd.cusname LIKE "%' . $qs['searchcusname'] . '%"';
		}
		if (!empty($qs['searchcid'])) {
			$where[] = 'a.cid LIKE "%' . $qs['searchcid'] . '%"';
		}

		$sql = 'SELECT a.id,a.apply_id,a.payment_id,a.cid,a.media_name,a.media_category,a.gd_amount,c.media_name AS media,d.cusname,\'p\' AS ptype
FROM finance_payment_deposit_gd a
LEFT JOIN 
finance_payment_person_deposit_apply b
ON a.apply_id=b.id
LEFT JOIN
finance_payment_media_info c
ON b.media_info_id=c.id
LEFT JOIN
contract_cus d
ON a.cid=d.cid
WHERE a.isok=1 AND a.apply_type=1 '
				. (!empty($where) ? ' AND ' . implode(' AND ', $where) : '')
				. '
UNION ALL
SELECT a.id,a.apply_id,a.payment_id,a.cid,a.media_name,a.media_category,a.gd_amount,c.media_name AS media,d.cusname,\'m\' AS ptype
FROM finance_payment_deposit_gd a
LEFT JOIN 
finance_payment_media_deposit_apply b
ON a.apply_id=b.id
LEFT JOIN
finance_payment_media_info c
ON b.media_info_id=c.id
LEFT JOIN
contract_cus d
ON a.cid=d.cid
WHERE a.isok=1 AND a.apply_type=2 '
				. (!empty($where) ? ' AND ' . implode(' AND ', $where) : '');

		$total = $this->db->get_var('SELECT COUNT(*) FROM (' . $sql . ') z');
		$results = $this->db
				->get_results(
						'SELECT * FROM (' . $sql . ') z ORDER BY '
								. $this->sort . ' ' . $this->order . ' LIMIT '
								. $start . ',' . $this->rows);
		$datas = array();
		if ($results !== NULL) {
			foreach ($results as $result) {
				$datas[] = array('ck' => $result->id . '_' . $result->ptype,
						'cid' => $result->cid,
						'cusname' => urlencode($result->cusname),
						'media' => urlencode($result->media),
						'gd_amount' => $result->gd_amount);
			}
		}
		return urldecode(
				json_encode(array('total' => $total, 'rows' => $datas)));
	}

	public function getReceiveInvoiceSource() {
		$qs = $this->qs;
		$total = intval(
				$this->db
						->get_var(
								'SELECT COUNT(*) FROM finance_receiveinvoice_source WHERE id IN ('
										. $qs['ids'] . ')'));
		$results = $this->db
				->get_results(
						'SELECT * FROM finance_receiveinvoice_source WHERE id IN ('
								. $qs['ids'] . ')');
		$datas = array();
		if ($results !== NULL) {
			foreach ($results as $result) {
				$datas[] = array('a' => urlencode($result->media_name),
						'b' => urlencode($result->invoice_number),
						'c' => urlencode($result->invoice_content),
						'd' => urlencode($result->tax_rate),
						'e' => urlencode($result->amount),
						'f' => urlencode($result->tax),
						'g' => urlencode($result->sum_amount),
						'h' => urlencode($result->invoice_date),
						'i' => urlencode($result->belong_month));
			}
		}

		return urldecode(
				json_encode(array('total' => $total, 'rows' => $datas)));
	}

	public function getPaymentApply() {
		$start = $this->rows * $this->page - $this->rows;
		if ($start < 0) {
			$start = 0;
		}

		$qs = $this->qs;
		$total = 0;
		$datas = array();
		$where = array();

		if (intval($qs['type']) === 1) {
			if (!empty($qs['payment_date'])) {
				$where[] = 'a.payment_date="' . $qs['payment_date'] . '"';
			}
			if (!empty($qs['medianame'])) {
				$where[] = 'b.media_name LIKE "%' . $qs['medianame'] . '%"';
			}

			$total = intval(
					$this->db
							->get_var(
									'SELECT COUNT(*)
	FROM finance_payment_person_apply a 
	LEFT JOIN finance_payment_media_info b
	ON a.media_info_id=b.id
	WHERE a.isok=1 AND a.is_rebate_deduction=1 AND a.rebate_amount>0'
											. (!empty($where) ? ' AND '
															. implode(' AND ',
																	$where) : '')));

			$results = $this->db
					->get_results(
							'SELECT a.id,a.payment_date,a.payment_amount_real,a.is_rebate_deduction,a.rebate_amount,a.payment_date,b.media_name 
	FROM finance_payment_person_apply a 
	LEFT JOIN finance_payment_media_info b
	ON a.media_info_id=b.id
	WHERE a.isok=1 AND a.is_rebate_deduction=1 AND a.rebate_amount>0'
									. (!empty($where) ? ' AND '
													. implode(' AND ', $where)
											: '') . ' LIMIT ' . $start . ','
									. $this->rows);

		} else if (intval($qs['type']) === 2) {
			if (!empty($qs['payment_date'])) {
				$where[] = 'c.payment_date="' . $qs['payment_date'] . '"';
			}
			if (!empty($qs['medianame'])) {
				$where[] = 'd.media_name LIKE "%' . $qs['medianame'] . '%"';
			}
			$base_sql = 'SELECT DISTINCT(c.id),c.payment_date,c.payment_amount_plan,c.payment_amount_real,c.is_rebate_deduction,c.rebate_amount,d.media_name
FROM finance_payment_rebate_status a
LEFT JOIN finance_payment_rebate b
ON a.rebate_id=b.id
LEFT JOIN finance_payment_person_apply c
ON b.apply_id=c.id
LEFT JOIN finance_payment_media_info d
ON c.media_info_id=d.id
WHERE a.status=1 AND b.payment_type=1 AND b.amount_type=1'
					. (!empty($where) ? ' AND ' . implode(' AND ', $where) : '');

			$total = intval(
					$this->db
							->get_var(
									'SELECT COUNT(*) FROM (' . $base_sql
											. ') z'));
			$results = $this->db
					->get_results(
							'SELECT * FROM (' . $base_sql . ')  z LIMIT '
									. $start . ',' . $this->rows);
		}

		if ($results !== NULL) {
			foreach ($results as $result) {
				$datas[] = array(
						//'x'=>$result->id,
						'a' => urlencode($result->media_name),
						'b' => $result->payment_date,
						'c' => $result->payment_amount_real,
						'd' => $result->rebate_amount,
						'e' => '<input type="button" class="btn" value="展开" onclick="javascript:showDebate('
								. $result->id . ')">');
			}
		}

		return urldecode(
				json_encode(array('total' => $total, 'rows' => $datas)));
	}

	public function getPaymentRebateItems() {
		$qs = $this->qs;
		$results = $this->db
				->get_results(
						'SELECT a.*,b.name,c.cusname  ,d.payname,d.category,d.isagent2,d.payamount
FROM 
finance_payment_person_apply_list a
LEFT JOIN v_last_executive b
ON a.pid=b.pid
LEFT JOIN contract_cus c
ON b.cid=c.cid
LEFT JOIN executive_paycost d
ON a.paycostid=d.id
WHERE a.apply_id=' . intval($qs['apply_id'])
								. ' AND a.isok=1 AND a.rebate_deduction_amount>0');

		$datas = array();
		if ($results !== NULL) {

			$item_payment_array = array();
			$item_payment = $this->db
					->get_results(
							'SELECT SUM(gd_amount) AS gd_amount,pid,paycostid FROM finance_payment_gd GROUP BY pid,paycostid');
			if ($item_payment !== NULL) {
				foreach ($item_payment as $ip) {
					$item_payment_array[$ip->pid][$ip->paycostid] = $ip
							->gd_amount;
				}
			}

			//无需开票（待分配 已分配）

			//需开票（待开票，已开票）

			foreach ($results as $result) {
				$paid_item_amount = empty(
						$item_payment_array[$result->pid][$result->paycostid]) ? 0
						: $item_payment_array[$result->pid][$result->paycostid];
				$key = $result->apply_id . '_' . $result->id;
				$datas[] = array('xx' => $key, 'aa' => $result->pid,
						'bb' => $result->cusname, 'cc' => $result->name,
						'dd' => $result->payname, 'ee' => 'xx',
						'ff' => $result->payamount,
						'gg' => $result->payamount - $paid_item_amount,
						'hh' => 'xx', 'ii' => 'xx', 'jj' => 'xx', 'kk' => 'xx',
						'll' => 'xx', 'mm' => 'xx', 'nn' => 'xx',
						'oo' => '<input type="text" style="height:20px;" id="rebate_p_'
								. $key . '" name="rebate_p_' . $key
								. '" value="'
								. $result->rebate_deduction_amount . '">');
			}
		}
		return urldecode(
				json_encode(array('total' => count($datas), 'rows' => $datas)));
	}

	public function getMediaDepositPaymentAssignInfos() {
		$qs = $this->qs;
		//获得被分配的人员信息
		$users = array();
		$datas = array();
		$item_users = array();
		$list = array();
		$results = $this->db
				->get_results(
						'SELECT a.payment_media_apply_id,a.payment_id,a.userid,a.isfinished,b.realname,b.username,b.city,b.dep,b.team,c.companyname,d.depname,e.teamname
FROM finance_payment_media_deposit_apply_user a
LEFT JOIN users b
ON a.userid=b.uid
LEFT JOIN hr_company c
ON b.city=c.id
LEFT JOIN hr_department d
ON b.dep=d.id
LEFT JOIN hr_team e
ON b.team=e.id
WHERE a.payment_media_apply_id=' . intval($qs['apply_id']) . ' AND a.isok=1');
		if ($results !== NULL) {
			foreach ($results as $result) {
				//部门信息
				if ($users['department'][$result->city . '_' . $result->dep
						. '_' . $result->team] === NULL) {
					$users['department'][$result->city . '_' . $result->dep
							. '_' . $result->team] = $result->companyname . ' '
							. $result->depname . ' ' . $result->teamname;
				}

				//人员信息
				$users['user'][$result->userid] = array(
						'depid' => $result->city . '_' . $result->dep . '_'
								. $result->team,
						'name' => $result->realname . '（' . $result->username
								. '）');
			}
		}

		//finance_payment_media_deposit_apply_items_users
		$results = $this->db
				->get_results(
						'SELECT a.item_id,a.list_id,a.user_id ,b.sqje
FROM finance_payment_media_deposit_apply_items_users a
LEFT JOIN finance_payment_media_deposit_apply_items b
ON a.item_id=b.id
WHERE a.payment_media_apply_id=' . intval($qs['apply_id']) . ' AND a.isok=1');

		if ($results !== NULL) {
			foreach ($results as $result) {
				if (!in_array($result->item_id,
						$item_users['item_user'][$result->user_id], TRUE)) {
					$item_users['item_user'][$result->user_id][] = $result
							->item_id;
				}
				if (!in_array($result->list_id,
						$item_users['list_user'][$result->user_id], TRUE)) {
					$item_users['list_user'][$result->user_id][] = $result
							->list_id;
				}
				if ($item_users['sqje'][$result->item_id] === NULL) {
					$item_users['sqje'][$result->item_id] = $result->htfke;
				}
			}
		}

		//finance_payment_media_deposit_apply_list
		$results = $this->db
				->get_results(
						'SELECT id,isok FROM finance_payment_media_deposit_apply_list WHERE apply_id='
								. intval($qs['apply_id']) . ' AND isok<>-1');
		if ($results !== NULL) {
			foreach ($results as $result) {
				if (intval($result->isok) === 0) {
					$list['undo'][] = $result->id;
				} else if (intval($result->isok) === 1) {
					$list['passed'][] = $result->id;
				} else if (intval($result->isok) === 2) {
					$list['rejected'][] = $result->id;
				}
			}
		}

		if ($users['user'] !== NULL) {
			foreach ($users['user'] as $uid => $userinfo) {
				$sum_htfke = 0;
				foreach ($item_users['item_user'][$uid] as $v) {
					$sum_htfke += $item_users['sqje'][$v];
				}

				$undo = 0;
				$passed = 0;
				$rejected = 0;
				foreach ($item_users['list_user'][$uid] as $vv) {
					if (in_array($vv, $list['undo'], TRUE)) {
						$undo += 1;
					} else if (in_array($vv, $list['passed'], TRUE)) {
						$passed += 1;
					} else if (in_array($vv, $list['rejected'], TRUE)) {
						$rejected += 1;
					}
				}
				$datas[] = array(
						'a' => urlencode(
								$users['department'][$userinfo['depid']]),
						'b' => $sum_htfke, 'c' => urlencode($userinfo['name']),
						'd' => urlencode(
								'已分配 ' . count($item_users['item_user'][$uid])
										. ' 条对账单信息'),
						'e' => urlencode(
								'未审核 ' . $undo . ' 条，审核通过 ' . $passed
										. ' 条，审核驳回 ' . $rejected . ' 条'),
						'f' => intval($qs['apply_id']), 'g' => $uid);
			}
		}
		return urldecode(
				json_encode(array('total' => count($datas), 'rows' => $datas)));
	}

	public function getMediaPaymentAssignInfos() {
		$qs = $this->qs;
		//获得被分配的人员信息
		$users = array();
		$datas = array();
		$item_users = array();
		$list = array();
		$results = $this->db
				->get_results(
						'SELECT a.payment_media_apply_id,a.payment_id,a.userid,a.isfinished,b.realname,b.username,b.city,b.dep,b.team,c.companyname,d.depname,e.teamname
FROM finance_payment_media_apply_user a
LEFT JOIN users b
ON a.userid=b.uid
LEFT JOIN hr_company c
ON b.city=c.id
LEFT JOIN hr_department d
ON b.dep=d.id
LEFT JOIN hr_team e
ON b.team=e.id
WHERE a.payment_media_apply_id=' . intval($qs['apply_id']) . ' AND a.isok=1');
		if ($results !== NULL) {
			foreach ($results as $result) {
				//部门信息
				if ($users['department'][$result->city . '_' . $result->dep
						. '_' . $result->team] === NULL) {
					$users['department'][$result->city . '_' . $result->dep
							. '_' . $result->team] = $result->companyname . ' '
							. $result->depname . ' ' . $result->teamname;
				}

				//人员信息
				$users['user'][$result->userid] = array(
						'depid' => $result->city . '_' . $result->dep . '_'
								. $result->team,
						'name' => $result->realname . '（' . $result->username
								. '）');
			}
		}

		//finance_payment_media_apply_items_users
		$results = $this->db
				->get_results(
						'SELECT a.item_id,a.list_id,a.user_id ,b.htfke
FROM finance_payment_media_apply_items_users a
LEFT JOIN finance_payment_media_apply_items b
ON a.item_id=b.id
WHERE a.payment_media_apply_id=' . intval($qs['apply_id']) . ' AND a.isok=1');
		if ($results !== NULL) {
			foreach ($results as $result) {
				if (!in_array($result->item_id,
						$item_users['item_user'][$result->user_id], TRUE)) {
					$item_users['item_user'][$result->user_id][] = $result
							->item_id;
				}
				if (!in_array($result->list_id,
						$item_users['list_user'][$result->user_id], TRUE)) {
					$item_users['list_user'][$result->user_id][] = $result
							->list_id;
				}
				if ($item_users['htfke'][$result->item_id] === NULL) {
					$item_users['htfke'][$result->item_id] = $result->htfke;
				}
			}
		}

		//finance_payment_media_apply_list
		$results = $this->db
				->get_results(
						'SELECT id,isok FROM finance_payment_media_apply_list WHERE apply_id='
								. intval($qs['apply_id']) . ' AND isok<>-1');
		if ($results !== NULL) {
			foreach ($results as $result) {
				if (intval($result->isok) === 0) {
					$list['undo'][] = $result->id;
				} else if (intval($result->isok) === 1) {
					$list['passed'][] = $result->id;
				} else if (intval($result->isok) === 2) {
					$list['rejected'][] = $result->id;
				}
			}
		}

		if ($users['user'] !== NULL) {
			foreach ($users['user'] as $uid => $userinfo) {
				$sum_htfke = 0;
				foreach ($item_users['item_user'][$uid] as $v) {
					$sum_htfke += $item_users['htfke'][$v];
				}

				$undo = 0;
				$passed = 0;
				$rejected = 0;
				foreach ($item_users['list_user'][$uid] as $vv) {
					if (in_array($vv, $list['undo'], TRUE)) {
						$undo += 1;
					} else if (in_array($vv, $list['passed'], TRUE)) {
						$passed += 1;
					} else if (in_array($vv, $list['rejected'], TRUE)) {
						$rejected += 1;
					}
				}
				$datas[] = array(
						'a' => urlencode(
								$users['department'][$userinfo['depid']]),
						'b' => $sum_htfke, 'c' => urlencode($userinfo['name']),
						'd' => urlencode(
								'已分配 ' . count($item_users['item_user'][$uid])
										. ' 条对账单信息'),
						'e' => urlencode(
								'未审核 ' . $undo . ' 条，审核通过 ' . $passed
										. ' 条，审核驳回 ' . $rejected . ' 条'),
						'f' => intval($qs['apply_id']), 'g' => $uid);
			}
		}
		return urldecode(
				json_encode(array('total' => count($datas), 'rows' => $datas)));
	}

	function getMediaDepositPaymentUserAssignedPid() {
		$qs = $this->qs;
		$lists = $this->db
				->get_results(
						'SELECT list_id FROM finance_payment_media_deposit_apply_items_users WHERE payment_media_apply_id='
								. intval($qs['apply_id']) . ' AND user_id='
								. intval($qs['uid'])
								. ' AND isok=1 GROUP BY list_id');
		$datas = array();
		$ids = array();
		if ($lists !== NULL) {
			foreach ($lists as $list) {
				$ids[] = $list->list_id;
			}
		}

		$ea_array = array();
		$keys = array();
		$values = array();
		$cost_array = array();
		$pid_array = array();
		$results = array();
		if (!empty($ids)) {
			$itemresults = $this->db
					->get_results(
							'SELECT * FROM finance_payment_media_deposit_apply_list WHERE apply_id='
									. intval($qs['apply_id']) . ' AND id IN( '
									. implode(',', $ids) . ') AND isok<>-1');
			if ($itemresults !== NULL) {
				foreach ($itemresults as $itemresult) {
					$keys[] = $itemresult->cid;
					$ea_array[$itemresult->cid] = array(
							'listid' => $itemresult->id,
							'cid' => $itemresult->cid,
							'payment_amount' => $itemresult->payment_amount,
							'payment_type' => $itemresult->payment_type,
							'person_loan_user' => $itemresult->person_loan_user,
							'person_loan_amount' => $itemresult
									->person_loan_amount,
							'is_nim_pay_first' => $itemresult->is_nim_pay_first,
							'nim_pay_first_amount' => $itemresult
									->nim_pay_first_amount,
							'nim_pay_first_dids' => $itemresult
									->nim_pay_first_dids,
							'isok' => $itemresult->isok);
				}
			}
		}

		if (!empty($keys)) {
			$cr = $this->db
					->get_results(
							'SELECT cid,cusname FROM contract_cus WHERE cid IN ("'
									. implode('","', $keys) . '")');
			if ($cr !== NULL) {
				foreach ($cr as $ccr) {
					$cost_array[$ccr->cid] = $ccr->cusname;
				}
			}
		}

		//已收客户保证金
		$reveive_deposits = $this->_getReceiveDeposit();

		//已付媒体保证金
		$payment_deposits = $this->_getPaymentDeposit();

		foreach ($ea_array as $vcid => $val) {
			$receive = empty($reveive_deposits[$vcid]) ? 0
					: $reveive_deposits[$vcid];

			$datas[] = array('a' => $vcid, 'b' => $cost_array[$vcid],
					'c' => empty($reveive_deposits[$vcid]) ? 0
							: $reveive_deposits[$vcid],
					'd' => empty($payment_deposits[$vcid]) ? 0
							: $payment_deposits[$vcid],
					'e' => (intval($ea_array[$vcid]['payment_type']) === 1 ? '全付'
							: '支付部分') . '&nbsp;'
							. $ea_array[$vcid]['payment_amount'],
					'f' => '还款人&nbsp;' . $ea_array[$vcid]['person_loan_user']
							. '&nbsp;&nbsp;金额&nbsp;'
							. $ea_array[$vcid]['person_loan_amount'],
					'g' => intval($ea_array[$vcid]['is_nim_pay_first']) === 1 ? '是&nbsp;'
									. $ea_array[$vcid]['nim_pay_first_amount']
							: '否',
					'w' => self::_get_list_status(intval($qs['apply_id']),
							intval($ea_array[$vcid]['listid']),
							intval($ea_array[$vcid]['isok'])));

		}

		return urldecode(
				json_encode(
						array('total' => count($itemresults), 'rows' => $datas)));
	}

	function getMediaPaymentUserAssignedPid() {
		$qs = $this->qs;
		$lists = $this->db
				->get_results(
						'SELECT list_id FROM finance_payment_media_apply_items_users WHERE payment_media_apply_id='
								. intval($qs['apply_id']) . ' AND user_id='
								. intval($qs['uid'])
								. ' AND isok=1 GROUP BY list_id');
		$datas = array();
		$ids = array();
		if ($lists !== NULL) {
			foreach ($lists as $list) {
				$ids[] = $list->list_id;
			}
		}

		$ea_array = array();
		$keys = array();
		$values = array();
		$cost_array = array();
		$pid_array = array();
		$results = array();
		if (!empty($ids)) {
			$itemresults = $this->db
					->get_results(
							'SELECT * FROM finance_payment_media_apply_list WHERE apply_id='
									. intval($qs['apply_id']) . ' AND id IN( '
									. implode(',', $ids)
									. ') AND isok<>-1 ORDER BY isok');
			if ($itemresults !== NULL) {
				foreach ($itemresults as $itemresult) {
					$keys[] = $itemresult->paycostid;
					if (!in_array($itemresult->pid, $values, TRUE)) {
						$values[] = $itemresult->pid;
					}
					$ea_array[$itemresult->pid][$itemresult->paycostid] = array(
							'listid' => $itemresult->id,
							'pid' => $itemresult->pid,
							'paycostid' => $itemresult->paycostid,
							'payment_amount' => $itemresult->payment_amount,
							'payment_type' => $itemresult->payment_type,
							'rebate_deduction_amount' => $itemresult
									->rebate_deduction_amount,
							'rebate_deduction_dids' => $itemresult
									->rebate_deduction_dids,
							'person_loan_user' => $itemresult->person_loan_user,
							'person_loan_amount' => $itemresult
									->person_loan_amount,
							'is_nim_pay_first' => $itemresult->is_nim_pay_first,
							'nim_pay_first_amount' => $itemresult
									->nim_pay_first_amount,
							'nim_pay_first_dids' => $itemresult
									->nim_pay_first_dids,
							'isok' => $itemresult->isok);
				}
			}
		}

		if (!empty($keys)) {
			$cr = $this->db
					->get_results(
							'SELECT id,payname,payamount FROM executive_paycost WHERE id IN ('
									. implode(',', $keys) . ')');
			if ($cr !== NULL) {
				foreach ($cr as $ccr) {
					$cost_array[$ccr->id] = array('payname' => $ccr->payname,
							'payamount' => $ccr->payamount);
				}
			}
		}

		if (!empty($values)) {
			foreach ($values as $val) {
				$pid_array[] = 'SELECT b.pid,b.allcost,b.amount,b.name,b.costpaymentinfoids,c.cusname,x.payment FROM 
v_last_executive b 
LEFT JOIN contract_cus c ON b.cid=c.cid 
LEFT JOIN (SELECT SUM(gd_amount) AS payment,pid FROM finance_payment_gd GROUP BY pid) x ON b.pid=x.pid 
WHERE b.pid="' . $val . '"';
			}
			$pid_array = $this->db
					->get_results(implode(' UNION ALL ', $pid_array));
			foreach ($pid_array as $pa) {
				$results[$pa->pid] = array('allcost' => $pa->allcost,
						'amount' => $pa->amount, 'name' => $pa->name,
						'costpaymentinfoids' => $pa->costpaymentinfoids,
						'cusname' => $pa->cusname, 'payment' => $pa->payment);
			}
		}

		//已收款
		$receive_amount = $this->_getReceiveAmount();

		//已开票
		$invoice = $this->_getInvoice();

		//归档金额
		$gd_amount = $this->_getGDAmountByPaycostid();

		foreach ($ea_array as $vpid => $vpaycostid) {
			$receive = empty($receive_amount[$vpid]) ? 0
					: $receive_amount[$vpid];

			foreach ($vpaycostid as $pcid => $listval) {
				$paid_amount = empty($gd_amount[$pcid]) ? 0 : $gd_amount[$pcid];
				//已执行未付成本
				$done_notpayment_amount = $cost_array[$pcid]['payamount']
						- $paid_amount;

				$datas[] = array('a' => $vpid,
						'b' => $results[$vpid]['cusname'],
						'c' => $results[$vpid]['name'],
						'd' => $results[$vpid]['amount'], 'e' => $receive,
						'f' => empty($invoice[$vpid]) ? 0 : $invoice[$vpid],
						'g' => $results[$vpid]['amount'] - $receive,
						'h' => $cost_array[$pcid]['payname'],
						'i' => $cost_array[$pcid]['payamount'],
						'j' => $done_notpayment_amount,
						'k' => $ea_array[$vpid][$pcid]['payment_amount'],
						'l' => $paid_amount,
						'm' => empty(
								$ea_array[$vpid][$pcid]['rebate_deduction_amount']) ? 0
								: $ea_array[$vpid][$pcid]['rebate_deduction_amount'],
						'n' => 'n', 'o' => 'o', 'p' => 'p', 'q' => 'q',
						'r' => 'r', 's' => 's',
						't' => '还款人&nbsp;'
								. $ea_array[$vpid][$pcid]['person_loan_user']
								. '&nbsp;&nbsp;金额&nbsp;'
								. $ea_array[$vpid][$pcid]['person_loan_amount'],
						'u' => $done_notpayment_amount,
						'v' => intval(
								$ea_array[$vpid][$pcid]['is_nim_pay_first'])
								=== 1 ? '是&nbsp;'
										. $ea_array[$vpid][$pcid]['nim_pay_first_amount']
								: '否',
						'w' => self::_get_list_status(intval($qs['apply_id']),
								intval($ea_array[$vpid][$pcid]['listid']),
								intval($ea_array[$vpid][$pcid]['isok'])));
			}
		}

		return urldecode(
				json_encode(
						array('total' => count($itemresults), 'rows' => $datas)));
	}

	private static function _get_list_status($apply_id, $listid, $isok) {
		switch ($isok) {
		case 0:
			return '<input type="radio" name="auditsel_' . $listid
					. '" value="1" checked>&nbsp;通过&nbsp;&nbsp;<input type="radio" name="auditsel_'
					. $listid
					. '" value="2">&nbsp;驳回&nbsp;&nbsp;驳回原因&nbsp;<input type="text" style="height:20px;" id="auditresaon_'
					. $listid . '" name="auditresaon_' . $listid
					. '">&nbsp;<input type="button" class="btn" value="提交" onclick="javascript:auditem(\''
					. $listid . '\')">';
		case 1:
			return '审核通过';
		case 2:
			return '驳回';
		}
	}

	//已收客户保证金
	private function _getReceiveDeposit($cid = NULL) {
		$datas = array();
		$results = $this->db
				->get_results(
						'SELECT SUM(amount) AS receive_deposit_amount,cid FROM finance_deposit_receivables WHERE isok=1'
								. ($cid !== NULL ? ' AND cid="' . $cid . '"'
										: ' GROUP BY cid'));
		if ($results !== NULL) {
			foreach ($results as $result) {
				$datas[$result->cid] = $result->receive_deposit_amount;
			}
		}
		return $cid === NULL ? $datas : $datas[$cid];
	}

	//已付媒体保证金
	private function _getPaymentDeposit($cid = NULL) {
		$datas = array();
		$results = $this->db
				->get_results(
						'SELECT SUM(gd_amount) AS payment_deposit_amount,cid FROM finance_payment_deposit_gd WHERE  isok=1'
								. ($cid !== NULL ? ' AND cid="' . $cid . '"'
										: ' GROUP BY cid'));
		if ($results !== NULL) {
			foreach ($results as $result) {
				$datas[$result->cid] = $result->payment_deposit_amount;
			}
		}
		return $cid === NULL ? $datas : $datas[$cid];
	}

	public function getMediaDepositPaymentAssignedPid() {
		$qs = $this->qs;
		$lists = $this->db
				->get_results(
						'SELECT list_id FROM finance_payment_media_deposit_apply_items_users WHERE payment_media_apply_id='
								. intval($qs['apply_id']) . ' AND user_id='
								. $this->getUid()
								. ' AND isok=1 GROUP BY list_id');
		$datas = array();
		$ids = array();
		if ($lists !== NULL) {
			foreach ($lists as $list) {
				$ids[] = $list->list_id;
			}
		}

		$ea_array = array();
		$keys = array();
		$values = array();
		$cost_array = array();
		$pid_array = array();
		$results = array();
		if (!empty($ids)) {
			$itemresults = $this->db
					->get_results(
							'SELECT * FROM finance_payment_media_deposit_apply_list WHERE apply_id='
									. intval($qs['apply_id']) . ' AND id IN( '
									. implode(',', $ids) . ') AND isok<>-1');
			if ($itemresults !== NULL) {
				foreach ($itemresults as $itemresult) {
					$keys[] = $itemresult->cid;
					$ea_array[$itemresult->cid] = array(
							'cid' => $itemresult->cid,
							'payment_amount' => $itemresult->payment_amount,
							'payment_type' => $itemresult->payment_type,
							'person_loan_user' => $itemresult->person_loan_user,
							'person_loan_amount' => $itemresult
									->person_loan_amount,
							'is_nim_pay_first' => $itemresult->is_nim_pay_first,
							'nim_pay_first_amount' => $itemresult
									->nim_pay_first_amount,
							'nim_pay_first_dids' => $itemresult
									->nim_pay_first_dids);
				}
			}
		}

		if (!empty($keys)) {
			$cr = $this->db
					->get_results(
							'SELECT cid,cusname FROM contract_cus WHERE cid IN ("'
									. implode('","', $keys) . '")');
			if ($cr !== NULL) {
				foreach ($cr as $ccr) {
					$cost_array[$ccr->cid] = $ccr->cusname;
				}
			}
		}

		//已收客户保证金
		$reveive_deposits = $this->_getReceiveDeposit();

		//已付媒体保证金
		$payment_deposits = $this->_getPaymentDeposit();

		foreach ($ea_array as $vcid => $val) {
			$receive = empty($reveive_deposits[$vcid]) ? 0
					: $reveive_deposits[$vcid];

			$datas[] = array('a' => $vcid, 'b' => $cost_array[$vcid],
					'c' => empty($reveive_deposits[$vcid]) ? 0
							: $reveive_deposits[$vcid],
					'd' => empty($payment_deposits[$vcid]) ? 0
							: $payment_deposits[$vcid],
					'e' => (intval($ea_array[$vcid]['payment_type']) === 1 ? '全付'
							: '支付部分') . '&nbsp;'
							. $ea_array[$vcid]['payment_amount'],
					'f' => '还款人&nbsp;' . $ea_array[$vcid]['person_loan_user']
							. '&nbsp;&nbsp;金额&nbsp;'
							. $ea_array[$vcid]['person_loan_amount'],
					'g' => intval($ea_array[$vcid]['is_nim_pay_first']) === 1 ? '是&nbsp;'
									. $ea_array[$vcid]['nim_pay_first_amount']
							: '否');

		}

		return urldecode(
				json_encode(
						array('total' => count($itemresults), 'rows' => $datas)));
	}

	public function getMediaPaymentAssignedPid() {
		$qs = $this->qs;
		$lists = $this->db
				->get_results(
						'SELECT list_id FROM finance_payment_media_apply_items_users WHERE payment_media_apply_id='
								. intval($qs['apply_id']) . ' AND user_id='
								. $this->getUid()
								. ' AND isok=1 GROUP BY list_id');
		$datas = array();
		$ids = array();
		if ($lists !== NULL) {
			foreach ($lists as $list) {
				$ids[] = $list->list_id;
			}
		}

		$ea_array = array();
		$keys = array();
		$values = array();
		$cost_array = array();
		$pid_array = array();
		$results = array();
		if (!empty($ids)) {
			$itemresults = $this->db
					->get_results(
							'SELECT * FROM finance_payment_media_apply_list WHERE apply_id='
									. intval($qs['apply_id']) . ' AND id IN( '
									. implode(',', $ids) . ') AND isok<>-1');
			if ($itemresults !== NULL) {
				foreach ($itemresults as $itemresult) {
					$keys[] = $itemresult->paycostid;
					if (!in_array($itemresult->pid, $values, TRUE)) {
						$values[] = $itemresult->pid;
					}
					$ea_array[$itemresult->pid][$itemresult->paycostid] = array(
							'pid' => $itemresult->pid,
							'paycostid' => $itemresult->paycostid,
							'payment_amount' => $itemresult->payment_amount,
							'payment_type' => $itemresult->payment_type,
							'rebate_deduction_amount' => $itemresult
									->rebate_deduction_amount,
							'rebate_deduction_dids' => $itemresult
									->rebate_deduction_dids,
							'person_loan_user' => $itemresult->person_loan_user,
							'person_loan_amount' => $itemresult
									->person_loan_amount,
							'is_nim_pay_first' => $itemresult->is_nim_pay_first,
							'nim_pay_first_amount' => $itemresult
									->nim_pay_first_amount,
							'nim_pay_first_dids' => $itemresult
									->nim_pay_first_dids);
				}
			}
		}

		if (!empty($keys)) {
			$cr = $this->db
					->get_results(
							'SELECT id,payname,payamount FROM executive_paycost WHERE id IN ('
									. implode(',', $keys) . ')');
			if ($cr !== NULL) {
				foreach ($cr as $ccr) {
					$cost_array[$ccr->id] = array('payname' => $ccr->payname,
							'payamount' => $ccr->payamount);
				}
			}
		}

		if (!empty($values)) {
			foreach ($values as $val) {
				$pid_array[] = 'SELECT b.pid,b.allcost,b.amount,b.name,b.costpaymentinfoids,c.cusname,x.payment FROM 
v_last_executive b 
LEFT JOIN contract_cus c ON b.cid=c.cid 
LEFT JOIN (SELECT SUM(gd_amount) AS payment,pid FROM finance_payment_gd GROUP BY pid) x ON b.pid=x.pid 
WHERE b.pid="' . $val . '"';
			}
			$pid_array = $this->db
					->get_results(implode(' UNION ALL ', $pid_array));
			foreach ($pid_array as $pa) {
				$results[$pa->pid] = array('allcost' => $pa->allcost,
						'amount' => $pa->amount, 'name' => $pa->name,
						'costpaymentinfoids' => $pa->costpaymentinfoids,
						'cusname' => $pa->cusname, 'payment' => $pa->payment);
			}
		}

		//已收款
		$receive_amount = $this->_getReceiveAmount();

		//已开票
		$invoice = $this->_getInvoice();

		//归档金额
		$gd_amount = $this->_getGDAmountByPaycostid();

		foreach ($ea_array as $vpid => $vpaycostid) {
			$receive = empty($receive_amount[$vpid]) ? 0
					: $receive_amount[$vpid];

			foreach ($vpaycostid as $pcid => $listval) {
				$paid_amount = empty($gd_amount[$pcid]) ? 0 : $gd_amount[$pcid];
				//已执行未付成本
				$done_notpayment_amount = $cost_array[$pcid]['payamount']
						- $paid_amount;

				$datas[] = array('a' => $vpid,
						'b' => $results[$vpid]['cusname'],
						'c' => $results[$vpid]['name'],
						'd' => $results[$vpid]['amount'], 'e' => $receive,
						'f' => empty($invoice[$vpid]) ? 0 : $invoice[$vpid],
						'g' => $results[$vpid]['amount'] - $receive,
						'h' => $cost_array[$pcid]['payname'],
						'i' => $cost_array[$pcid]['payamount'],
						'j' => $done_notpayment_amount,
						'k' => $ea_array[$vpid][$pcid]['payment_amount'],
						'l' => $paid_amount,
						'm' => empty(
								$ea_array[$vpid][$pcid]['rebate_deduction_amount']) ? 0
								: $ea_array[$vpid][$pcid]['rebate_deduction_amount'],
						'n' => 'n', 'o' => 'o', 'p' => 'p', 'q' => 'q',
						'r' => 'r', 's' => 's',
						't' => '还款人&nbsp;'
								. $ea_array[$vpid][$pcid]['person_loan_user']
								. '&nbsp;&nbsp;金额&nbsp;'
								. $ea_array[$vpid][$pcid]['person_loan_amount'],
						'u' => $done_notpayment_amount,
						'v' => intval(
								$ea_array[$vpid][$pcid]['is_nim_pay_first'])
								=== 1 ? '是&nbsp;'
										. $ea_array[$vpid][$pcid]['nim_pay_first_amount']
								: '否');
			}
		}

		return urldecode(
				json_encode(
						array('total' => count($itemresults), 'rows' => $datas)));
	}

	public function getMediaDepositPaymentItemsUserAssigned() {
		$qs = $this->qs;
		$results = $this->db
				->get_results(
						'SELECT item_id FROM finance_payment_media_deposit_apply_items_users WHERE payment_media_apply_id='
								. intval($qs['apply_id']) . ' AND user_id='
								. intval($qs['uid'])
								. ' AND isok=1 GROUP BY item_id');
		$datas = array();
		$ids = array();
		if ($results !== NULL) {
			foreach ($results as $result) {
				$ids[] = $result->item_id;
			}
		}

		if (!empty($ids)) {
			$itemresults = $this->db
					->get_results(
							'SELECT * FROM finance_payment_media_deposit_apply_items WHERE apply_id='
									. intval($qs['apply_id']) . ' AND id IN( '
									. implode(',', $ids) . ') AND isok=1');
			if ($itemresults !== NULL) {
				foreach ($itemresults as $result) {
					$datas[] = array('aa' => urlencode($result->ggz),
							'bb' => urlencode($result->mthth),
							'cc' => urlencode($result->sqje),
							'dd' => urlencode($result->htfkrq),
							'ee' => urlencode($result->kjje),
							'ff' => urlencode($result->kjkssj));
				}
			}
		}
		return urldecode(
				json_encode(array('total' => count($results), 'rows' => $datas)));
	}

	public function getMediaPaymentItemsUserAssigned() {
		$qs = $this->qs;
		$results = $this->db
				->get_results(
						'SELECT item_id FROM finance_payment_media_apply_items_users WHERE payment_media_apply_id='
								. intval($qs['apply_id']) . ' AND user_id='
								. intval($qs['uid'])
								. ' AND isok=1 GROUP BY item_id');
		$datas = array();
		$ids = array();
		if ($results !== NULL) {
			foreach ($results as $result) {
				$ids[] = $result->item_id;
			}
		}

		if (!empty($ids)) {
			$itemresults = $this->db
					->get_results(
							'SELECT * FROM finance_payment_media_apply_items WHERE payment_media_apply_id='
									. intval($qs['apply_id']) . ' AND id IN( '
									. implode(',', $ids) . ') AND isok=1');
			if ($itemresults !== NULL) {
				foreach ($itemresults as $result) {
					$datas[] = array('aa' => urlencode($result->ggz),
							'bb' => urlencode($result->cp),
							'cc' => urlencode($result->htfke),
							'dd' => urlencode($result->czrq),
							'ee' => urlencode($result->hth));
				}
			}
		}
		return urldecode(
				json_encode(array('total' => count($results), 'rows' => $datas)));
	}

	public function getMediaDepositPaymentItems() {
		$qs = $this->qs;
		$results = $this->db
				->get_results(
						'SELECT item_id FROM finance_payment_media_deposit_apply_items_users WHERE payment_media_apply_id='
								. intval($qs['apply_id']) . ' AND user_id='
								. $this->getUid()
								. ' AND isok=1 GROUP BY item_id');
		$datas = array();
		$ids = array();
		if ($results !== NULL) {
			foreach ($results as $result) {
				$ids[] = $result->item_id;
			}
		}

		if (!empty($ids)) {
			$itemresults = $this->db
					->get_results(
							'SELECT * FROM finance_payment_media_deposit_apply_items WHERE apply_id='
									. intval($qs['apply_id']) . ' AND id IN( '
									. implode(',', $ids) . ') AND isok=1');
			if ($itemresults !== NULL) {
				foreach ($itemresults as $result) {
					$datas[] = array('aa' => urlencode($result->ggz),
							'bb' => urlencode($result->mthth),
							'cc' => urlencode($result->sqje),
							'dd' => urlencode($result->htfkrq),
							'ee' => urlencode($result->kjje),
							'ff' => urlencode($result->kjkssj));
				}
			}
		}

		return urldecode(
				json_encode(array('total' => count($results), 'rows' => $datas)));
	}

	public function getMediaPaymentItems() {
		$qs = $this->qs;
		$results = $this->db
				->get_results(
						'SELECT item_id FROM finance_payment_media_apply_items_users WHERE payment_media_apply_id='
								. intval($qs['apply_id']) . ' AND user_id='
								. $this->getUid()
								. ' AND isok=1 GROUP BY item_id');
		$datas = array();
		$ids = array();
		if ($results !== NULL) {
			foreach ($results as $result) {
				$ids[] = $result->item_id;
			}
		}

		if (!empty($ids)) {
			$itemresults = $this->db
					->get_results(
							'SELECT * FROM finance_payment_media_apply_items WHERE payment_media_apply_id='
									. intval($qs['apply_id']) . ' AND id IN( '
									. implode(',', $ids) . ') AND isok=1');
			if ($itemresults !== NULL) {
				foreach ($itemresults as $result) {
					$datas[] = array('aa' => urlencode($result->ggz),
							'bb' => urlencode($result->cp),
							'cc' => urlencode($result->htfke),
							'dd' => urlencode($result->czrq),
							'ee' => urlencode($result->hth));
				}
			}
		}

		return urldecode(
				json_encode(array('total' => count($results), 'rows' => $datas)));
	}

	public function getSumRebateInvoiceNoCollection() {
		/*
		$results = $this->db->get_results('SELECT a.rebate_invoice_id,a.gdtype,a.amount,b.media_name 
		FROM 
		finance_rebate_invoice_gd a
		LEFT JOIN 
		finance_rebate_invoice b
		ON a.rebate_invoice_id=b.id
		WHERE b.isok=1');
		$rec = array();
		if($results !== NULL){
		    foreach ($results as $result){
		        if($rec[$result->media_name][$result->gdtype] === NULL){
		            $rec[$result->media_name][$result->gdtype]  = $result->amount;
		        }else{
		            $rec[$result->media_name][$result->gdtype]  += $result->amount;
		        }
		    }
		}
		 */

		$results = $this->db
				->get_results(
						'SELECT SUM(amount) AS amount,rebate_invoice_id,gdtype,media_name
FROM
(
SELECT a.rebate_invoice_id,a.gdtype,a.amount,b.media_name 
FROM 
finance_rebate_invoice_gd a
LEFT JOIN 
finance_rebate_invoice b
ON a.rebate_invoice_id=b.id
WHERE b.isok=1
) m GROUP BY m.rebate_invoice_id,m.gdtype');
		$tmp = array();
		$datas = array();
		if ($results !== NULL) {
			foreach ($results as $result) {
				$tmp['r'][$result->rebate_invoice_id][$result->gdtype] = $result
						->amount;
				if ($tmp['n'][$result->rebate_invoice_id] === NULL) {
					$tmp['n'][$result->rebate_invoice_id] = $result->media_name;
				}
			}
		}

		foreach ($tmp['r'] as $key => $value) {
			if ($value[1] > $value[2]) {
				$datas[] = array('a' => urlencode($tmp['n'][$key]),
						'b' => $value[1] - $value[2],
						'c' => '<input type="button" class="btn" value="展开" onclick="javascript:openit('
								. $key . ')">');
			}
		}
		return urldecode(
				json_encode(array('total' => count($datas), 'rows' => $datas)));
	}

	public function getRebateInvoiceNoCollectionByInvoiceID() {
		$qs = $this->qs;
		$datas = array();
		$results = $this->db
				->get_results(
						'SELECT a.amount,a.date,a.number,c.realname,c.username,m.collection
FROM 
finance_rebate_invoice_gd a
LEFT JOIN finance_rebate_invoice b
ON a.rebate_invoice_id=b.id
LEFT JOIN users c
ON b.user=c.uid
LEFT JOIN
(
SELECT SUM(amount) AS collection,rebate_invoice_id AS c_id FROM finance_rebate_invoice_gd WHERE rebate_invoice_id='
								. $qs['invoice_id']
								. ' AND gdtype=2
) m
ON a.rebate_invoice_id=m.c_id
WHERE a.rebate_invoice_id=' . $qs['invoice_id']
								. ' AND a.gdtype=1 ORDER BY a.date');
		if ($results !== NULL) {
			foreach ($results as $result) {
				$datas[] = array('aa' => $result->amount,
						'bb' => $result->date,
						'cc' => urlencode(
								$result->realname . '（' . $result->username
										. '）'), 'dd' => $result->number,
						'ee' => $result->amount - $result->collection);
			}
		}
		return urldecode(
				json_encode(array('total' => count($datas), 'rows' => $datas)));
	}

	public function getRebateInvoiceGDInfo() {
		$qs = $this->qs;
		$datas = array();
		$results = $this->db
				->get_results(
						'SELECT a.*,b.bank_name,b.bank_account FROM finance_rebate_invoice_gd a LEFT JOIN finance_nim_bankinfo b ON a.bank=b.id WHERE a.rebate_invoice_id='
								. intval($qs['id']) . ' AND a.gdtype='
								. intval($qs['type']));
		if ($results !== NULL) {
			foreach ($results as $result) {
				$datas[] = array('a' . $qs['type'] => $result->date,
						'b' . $qs['type'] => $result->amount,
						'c' . $qs['type'] => $qs['type'] === '2' ? $result
										->bank_name . '---'
										. $result->bank_account
								: $result->number);
			}
		}

		return urldecode(
				json_encode(array('total' => count($datas), 'rows' => $datas)));
	}

	public function searchRebateInvoiceApplyPid() {
		$start = $this->rows * $this->page - $this->rows;
		if ($start < 0) {
			$start = 0;
		}
		$qs = $this->qs;
		$datas = array();
		if ($qs['type'] === '1') {
			$total = intval(
					$this->db
							->get_var(
									'SELECT COUNT(*) FROM v_last_executive WHERE pid LIKE "%'
											. $qs['pid'] . '%"'));
			$results = $this->db
					->get_results(
							'SELECT pid,name FROM v_last_executive WHERE pid LIKE "%'
									. $qs['pid'] . '%" LIMIT ' . $start . ','
									. $this->rows);
			if ($results !== NULL) {
				foreach ($results as $result) {
					$datas[] = array('xx' => $result->pid,
							'aa' => $result->pid,
							'bb' => urlencode($result->name), 'type' => 1);
				}
			}
		} else if ($qs['type'] === '2') {
			$total = intval(
					$this->db
							->get_var(
									'SELECT COUNT(*) FROM v_last_contract WHERE cusname LIKE "%'
											. $qs['cusname'] . '%"'));
			$results = $this->db
					->get_results(
							'SELECT cid,cusname,contractname FROM v_last_contract WHERE cusname LIKE "%'
									. $qs['cusname'] . '%" LIMIT ' . $start
									. ',' . $this->rows);
			if ($results !== NULL) {
				foreach ($results as $result) {
					$datas[] = array('xx' => $result->cid,
							'aa' => $result->cusname, 'bb' => $result->cid,
							'cc' => urlencode($result->contractname),
							'type' => 2);
				}
			}
		} else if ($qs['type'] === '3') {
			$total = intval(
					$this->db
							->get_var(
									'SELECT COUNT(*) FROM
(
SELECT a.id,b.media_name FROM finance_payment_person_apply a LEFT JOIN finance_payment_media_info b ON a.media_info_id=b.id WHERE b.media_name LIKE "%'
											. $qs['medianame']
											. '%" AND a.isok=1
UNION ALL
SELECT a.id,b.media_name FROM finance_payment_media_apply a LEFT JOIN finance_payment_media_info b ON a.media_info_id=b.id WHERE b.media_name LIKE "%'
											. $qs['medianame']
											. '%" AND a.isok=1
) z'));
			$results = $this->db
					->get_results(
							'SELECT * FROM
(
SELECT a.id,b.media_name,payment_amount_plan,payment_date,payment_amount_real,\'p\' AS ptype FROM finance_payment_person_apply a LEFT JOIN finance_payment_media_info b ON a.media_info_id=b.id WHERE b.media_name LIKE "%'
									. $qs['medianame']
									. '%" AND a.isok=1
UNION ALL
SELECT a.id,b.media_name,payment_amount_plan,payment_date,payment_amount_real,\'m\' AS ptype FROM finance_payment_media_apply a LEFT JOIN finance_payment_media_info b ON a.media_info_id=b.id WHERE b.media_name LIKE "%'
									. $qs['medianame']
									. '%" AND a.isok=1
) z LIMIT ' . $start . ',' . $this->rows);
			if ($results !== NULL) {
				foreach ($results as $result) {
					$datas[] = array(
							'xx' => $result->id . '_' . $result->ptype,
							'aa' => urlencode($result->media_name),
							'bb' => $result->payment_date,
							'cc' => $result->payment_amount_plan,
							'dd' => $result->payment_amount_real, 'type' => 3);
				}
			}
		} else if ($qs['type'] === '4') {
			$where = array();
			if ($qs['starttime'] !== '') {
				$where[] = 'starttime>="' . $qs['starttime'] . '"';
			}
			if ($qs['endtime'] !== '') {
				$where[] = 'endtime<="' . $qs['endtime'] . '"';
			}
			if ($qs['medianame'] === '') {
				$total = intval(
						$this->db
								->get_var(
										'SELECT COUNT(*) FROM v_last_executive WHERE '
												. implode(' AND ', $where)));
				$results = $this->db
						->get_results(
								'SELECT pid,name,starttime,endtime FROM v_last_executive WHERE '
										. implode(' AND ', $where));
				if ($results !== NULL) {
					foreach ($results as $result) {
						$datas[] = array('xx' => $result->pid,
								'aa' => $result->pid,
								'bb' => urlencode($result->name),
								'cc' => $result->starttime,
								'dd' => $result->endtime, 'type' => 4);
					}
				}
			} else {
				$results = $this->db
						->get_results(
								'SELECT pid,name,starttime,endtime,costpaymentinfoids FROM v_last_executive WHERE costpaymentinfoids<>\'\' AND costpaymentinfoids IS NOT NULL '
										. (!empty($where) ? ' AND '
														. implode(' AND ',
																$where) : ''));
				if ($results !== NULL) {
					foreach ($results as $result) {
						$costpaymentinfoids = explode('^',
								$result->costpaymentinfoids);
						$costpaymentinfoids = Array_Util::my_remove_array_other_value(
								$costpaymentinfoids, array(''));
						$count = intval(
								$this->db
										->get_var(
												'SELECT COUNT(*) FROM executive_paycost WHERE id IN('
														. implode(',',
																$costpaymentinfoids)
														. ') AND payname LIKE "%'
														. $qs['medianame']
														. '%"'));
						if ($count > 0) {
							$datas[] = array('xx' => $result->pid,
									'aa' => $result->pid,
									'bb' => urlencode($result->name),
									'cc' => $result->starttime,
									'dd' => $result->endtime, 'type' => 4);
						}
					}
				}

				$total = count($datas);
				$datas = array_slice($datas, $start, $this->rows);
			}
		}

		return urldecode(
				json_encode(array('total' => $total, 'rows' => $datas)));
	}

	public function getPaymentApplyInRebateTransfer() {
		$qs = $this->qs;
		$where = array();
		if (!empty($qs['searchmedianame'])) {
			$where[] = 'b.media_name LIKE "%' . $qs['searchmedianame'] . '%"';
		}
		if (!empty($qs['searchpaydate'])) {
			$where[] = 'a.payment_date="' . $qs['searchpaydate'] . '"';
		}
		if (!empty($qs['searchpayplan'])) {
			$where[] = 'a.payment_amount_plan=' . $qs['searchpayplan'];
		}
		if (!empty($qs['searchpayreal'])) {
			$where[] = 'a.payment_amount_real=' . $qs['searchpayreal'];
		}

		$total = 0;
		$datas = array();
		$results = $this->db
				->get_results(
						'SELECT a.id,a.payment_date,a.payment_amount_plan,a.payment_amount_real,a.is_rebate_deduction,a.rebate_amount,b.media_name,"p" AS ptype
FROM finance_payment_person_apply a
LEFT JOIN finance_payment_media_info b
ON a.media_info_id=b.id
WHERE a.isok=1 ' . (!empty($where) ? ' AND ' . implode(' AND ', $where) : '')
								. '
UNION ALL
SELECT a.id,a.payment_date,a.payment_amount_plan,a.payment_amount_real,a.is_rebate_deduction,a.rebate_amount,b.media_name,"m" AS ptype
FROM finance_payment_media_apply a
LEFT JOIN finance_payment_media_info b
ON a.media_info_id=b.id
WHERE a.isok=1 ' . (!empty($where) ? ' AND ' . implode(' AND ', $where) : ''));
		if ($results !== NULL) {
			foreach ($results as $result) {
				$newwhere = array();
				if (!empty($qs['searchpid'])) {
					$newwhere[] = 'a.pid LIKE "%' . $qs['searchpid'] . '%"';
				}
				if (!empty($qs['searchcusname'])) {
					$newwhere[] = 'c.cusname LIKE "%' . $qs['searchcusname']
							. '%"';
				}

				$count = intval(
						$this->db
								->get_var(
										'SELECT COUNT(*) FROM '
												. ($result->ptype === 'p' ? 'finance_payment_person_apply_list'
														: 'finance_payment_media_apply_list')
												. ' a
LEFT JOIN v_last_executive b
ON a.pid=b.pid
LEFT JOIN contract_cus c
ON b.cid=c.cid
WHERE a.apply_id=' . $result->id
												. (!empty($newwhere) ? ' AND '
																. implode(
																		' AND ',
																		$newwhere)
														: '')));
				if ($count > 0) {
					$datas[] = array(
							//'xx'=>$result->id . '_' . $result->ptype,
							'aa' => $result->media_name,
							'bb' => $result->payment_date,
							'cc' => $result->payment_amount_plan,
							'dd' => $result->payment_amount_real,
							'ee' => intval($result->is_rebate_deduction) === 1 ? $result
											->rebate_amount : 0,
							'ff' => '<input type="button" value="展开" class="btn" onclick="javascript:add(\''
									. $result->id . '_' . $result->ptype
									. '\')"/>');
				}
			}
		}

		if (!empty($datas)) {
			$start = $this->rows * $this->page - $this->rows;
			if ($start < 0) {
				$start = 0;
			}
			$total = count($datas);
			$datas = array_slice($datas, $start, $this->rows);
		}
		return urldecode(
				json_encode(array('total' => $total, 'rows' => $datas)));
	}

	private function _getCustomerDeposit($cid = NULL) {
		$datas = array();
		$results = $this->db
				->get_results(
						'SELECT cid,amount FROM finance_deposit WHERE '
								. ($cid !== NULL ? 'cid="' . $cid . '"' : '1=1')
								. ' AND isok=1');
		if ($results !== NULL) {
			foreach ($results as $result) {
				$datas[$result->cid] = $result->amount;
			}
		}
		return $cid === NULL ? $datas : $datas[$cid];
	}

	public function getCustomerDepositPaymentNimpayfirst() {
		$qs = $this->qs;
		$total = 0;
		$datas = array();

		$results = $this->db
				->get_results(
						'SELECT a.apply_id,a.list_id,a.payfirst_amount,b.cid,b.media_name AS oa_media_name,b.media_category,c.payment_date,c.payment_amount_real,
e.media_name,f.cusname
FROM finance_payment_payfirst a
LEFT JOIN finance_payment_person_deposit_apply_list b
ON a.apply_id=b.apply_id AND a.list_id=b.id
LEFT JOIN finance_payment_person_deposit_apply c
ON a.apply_id=c.id
LEFT JOIN finance_payment_media_info e
ON c.media_info_id=e.id
LEFT JOIN contract_cus f
ON b.cid=f.cid
WHERE f.cusname="' . $qs['cusname']
								. '" AND a.amount_type=2 AND a.payment_type=1 AND a.status=1 AND b.isok=1
UNION ALL 
SELECT a.apply_id,a.list_id,a.payfirst_amount,b.cid,b.media_name AS oa_media_name,b.media_category,c.payment_date,c.payment_amount_real,
e.media_name,f.cusname
FROM finance_payment_payfirst a
LEFT JOIN finance_payment_media_deposit_apply_list b
ON a.apply_id=b.apply_id AND a.list_id=b.id
LEFT JOIN finance_payment_media_deposit_apply c
ON a.apply_id=c.id
LEFT JOIN finance_payment_media_info e
ON c.media_info_id=e.id
LEFT JOIN contract_cus f
ON b.cid=f.cid
WHERE f.cusname="' . $qs['cusname']
								. '" AND a.amount_type=2 AND a.payment_type=2 AND a.status=1 AND b.isok=1');
		if ($results !== NULL) {
			//保证金合计
			$deposits = $this->_getCustomerDeposit();

			//已收客户保证金
			$receive_deposits = $this->_getReceiveDeposit();

			//已付媒体保证金
			$payment_deposit = $this->_getPaymentDeposit();

			foreach ($results as $result) {
				$cid_deposit = empty($deposits[$result->cid]) ? 0
						: $deposits[$result->cid];
				$receive_deposit = empty($receive_deposits[$result->cid]) ? 0
						: $receive_deposits[$result->cid];
				$payment = empty($payment_deposit[$result->cid]) ? 0
						: $payment_deposit[$result->cid];
				$datas = array('aaa' => $result->cid, 'bbb' => $cid_deposit,
						'ccc' => $receive_deposit, 'ddd' => $payment,
						'eee' => $result->payment_amount_real,
						'fff' => $result->payfirst_amount,
						'ggg' => $cid_deposit - $receive_deposit,
						'hhh' => $payment - $receive_deposit > 0 ? $payment
										- $receive_deposit : 0,);
			}
		}

		return urldecode(
				json_encode(array('total' => $total, 'rows' => $datas)));
	}

	public function getCustomerContractPaymentNimpayfirst() {
		$qs = $this->qs;
		$total = 0;
		$datas = array();

		$results = $this->db
				->get_results(
						'SELECT a.apply_id,a.list_id,a.payfirst_amount,b.pid,b.paycostid,c.payment_date,d.name,d.amount,e.media_name,f.cusname
FROM finance_payment_payfirst a
LEFT JOIN finance_payment_person_apply_list b
ON a.apply_id=b.apply_id AND a.list_id=b.id
LEFT JOIN finance_payment_person_apply c
ON a.apply_id=c.id
LEFT JOIN v_last_executive d
ON b.pid=d.pid
LEFT JOIN finance_payment_media_info e
ON c.media_info_id=e.id
LEFT JOIN contract_cus f
ON d.cid=f.cid
WHERE f.cusname="' . $qs['cusname']
								. '" AND a.amount_type=1 AND a.payment_type=1 AND a.status=1 AND b.isok=1
UNION ALL 
SELECT a.apply_id,a.list_id,a.payfirst_amount,b.pid,b.paycostid,c.payment_date,d.name,d.amount,e.media_name,f.cusname
FROM finance_payment_payfirst a
LEFT JOIN finance_payment_media_apply_list b
ON a.apply_id=b.apply_id AND a.list_id=b.id
LEFT JOIN finance_payment_media_apply c
ON a.apply_id=c.id
LEFT JOIN v_last_executive d
ON b.pid=d.pid
LEFT JOIN finance_payment_media_info e
ON c.media_info_id=e.id
LEFT JOIN contract_cus f
ON d.cid=f.cid
WHERE f.cusname="' . $qs['cusname']
								. '" AND a.amount_type=1 AND a.payment_type=2 AND a.status=1 AND b.isok=1');
		if ($results !== NULL) {

			//已收款
			$receives = $this->_getReceiveAmount();

			//已开票
			$invoices = $this->_getInvoice();

			//已付款
			$gd_amount = $this->_getGDAmountByPaycostid();

			foreach ($results as $result) {
				$paycost_gd = empty($gd_amount[$result->paycostid]) ? 0
						: $gd_amount[$result->paycostid];
				$pid_receive = empty($receives[$result->pid]) ? 0
						: $receives[$result->pid];
				$datas[] = array('aa' => $result->pid,
						'bb' => urlencode($result->name),
						'cc' => $result->amount, 'dd' => $pid_receive,
						'ee' => empty($invoices[$result->pid]) ? 0
								: $invoices[$result->pid],
						'ff' => $paycost_gd - $pid_receive,
						'gg' => $paycost_gd,
						'hh' => urlencode($result->media_name),
						'ii' => $result->payment_date,
						'jj' => $result->payfirst_amount,
						'kk' => $paycost_gd - $pid_receive,
						'll' => $paycost_gd - $pid_receive > 0 ? $paycost_gd
										- $pid_receive : 0, 'mm' => '');
			}
		}

		return urldecode(
				json_encode(array('total' => $total, 'rows' => $datas)));
	}

	public function getRebateQuery() {
		$start = $this->rows * $this->page - $this->rows;
		if ($start < 0) {
			$start = 0;
		}
		$qs = $this->qs;
		$datas = array();
		$total = 0;
		$where = array();
		if (!empty($qs['medianame'])) {
			$where[] = 'c.media_name LIKE "%' . $qs['medianame'] . '%"';
		}
		if (!empty($qs['depname'])) {
			$where[] = 'f.depname LIKE "%' . $qs['depname'] . '%"';
		}
		if (!empty($qs['startdate'])) {
			$where[] = 'i.starttime>="' . $qs['startdate'] . '"';
		}
		if (!empty($qs['enddate'])) {
			$where[] = 'i.enddate<="' . $qs['enddate'] . '"';
		}
		if (!empty($qs['cusname'])) {
			$where[] = 'j.cusname LIKE "%' . $qs['cusname'] . '%"';
		}

		$base_sql = 'SELECT a.id,a.apply_id,a.list_id,a.rebate_amount,c.media_name,d.uid,e.companyname,f.depname,g.teamname,h.pid,h.paycostid,i.starttime,i.endtime,j.cusname,"[PTYPE]" AS ptype
FROM 
finance_payment_rebate a
LEFT JOIN [PAYMENTTABLE] b
ON a.apply_id=b.id
LEFT JOIN finance_payment_media_info c
ON b.media_info_id=c.id
LEFT JOIN users d
ON b.user=d.uid
LEFT JOIN hr_company e
ON d.city=e.id
LEFT JOIN hr_department f
ON d.dep=f.id
LEFT JOIN hr_team g
ON d.team=g.id
LEFT JOIN [PAYMENTLISTTABLE] h
ON a.apply_id=h.apply_id AND a.list_id=h.id
LEFT JOIN v_last_executive i
ON h.pid=i.pid
LEFT JOIN contract_cus j
ON i.cid=j.cid
WHERE a.payment_type=[PAYMENTTYPE] AND a.amount_type=[AMOUNTTYPE] AND a.status=1'
				. (!empty($where) ? ' AND ' . implode(' AND ', $where) : '');

		$sqls[] = str_replace(
				array('[PTYPE]', '[PAYMENTTABLE]', '[PAYMENTLISTTABLE]',
						'[PAYMENTTYPE]', '[AMOUNTTYPE]'),
				array('pc', 'finance_payment_person_apply',
						'finance_payment_person_apply_list', '1', '1'),
				$base_sql);
		//$sqls[] = str_replace(array('[PTYPE]','[PAYMENTTABLE]','[PAYMENTLISTTABLE]','[PAYMENTTYPE]','[AMOUNTTYPE]'), array('pd','finance_payment_person_deposit_apply','finance_payment_person_deposit_apply_list','1','2'), $base_sql);
		$sqls[] = str_replace(
				array('[PTYPE]', '[PAYMENTTABLE]', '[PAYMENTLISTTABLE]',
						'[PAYMENTTYPE]', '[AMOUNTTYPE]'),
				array('mc', 'finance_payment_media_apply',
						'finance_payment_media_apply_list', '2', '1'),
				$base_sql);
		//$sqls[] = str_replace(array('[PTYPE]','[PAYMENTTABLE]','[PAYMENTLISTTABLE]','[PAYMENTTYPE]','[AMOUNTTYPE]'), array('md','finance_payment_media_deposit_apply','finance_payment_media_deposit_apply_list','2','2'), $base_sql);

		$sqls = implode(' UNION ALL ', $sqls);
		//var_dump($sqls);
		$total = intval(
				$this->db->get_var('SELECT COUNT(*) FROM (' . $sqls . ') z'));
		$results = $this->db
				->get_results(
						'SELECT * FROM (' . $sqls . ') z LIMIT ' . $start . ','
								. $this->rows);

		$rebate_info = array();
		$d = $this->db
				->get_results(
						'SELECT rebate_id,rebate_amount,status FROM finance_payment_rebate_status');
		if ($d !== NULL) {
			foreach ($d as $dd) {
				$rebate_info[$dd->rebate_id][$dd->status] += $dd->reabte_amount;
			}
		}

		if ($results !== NULL) {
			foreach ($results as $result) {
				$dfp = $rebate_info[$result->id][3]
						+ $rebate_info[$result->id][4];
				$datas[] = array(
						'a' => $result->starttime . ' - ' . $result->endtime,
						'b' => urlencode($result->cusname),
						'c' => urlencode($result->media_name),
						'd' => urlencode(
								$result->companyname . ' ' . $result->depname
										. ' ' . $result->teamname),
						'e' => empty($rebate_info[$result->id][3]) ? 0
								: $rebate_info[$result->id][3],
						'f' => empty($rebate_info[$result->id][2]) ? 0
								: $rebate_info[$result->id][2],
						'g' => empty($dfp) ? 0 : $dfp);
			}
		}

		return urldecode(
				json_encode(array('total' => $total, 'rows' => $datas)));
	}

	public function getPaymentListInRebateTransfer() {
		$qs = $this->qs;
		$itemid = $qs['itemid'];
		$itemid = explode('_', $itemid);
		$sql = '';
		$total = 0;
		$datas = array();
		if ($itemid[1] === 'p') {
			$sql = 'SELECT a.id,a.pid,a.paycostid,b.name,c.cusname,d.payamount,"p" AS ptype
FROM 
finance_payment_person_apply_list a
LEFT JOIN v_last_executive b
ON a.pid=b.pid
LEFT JOIN contract_cus c
ON b.cid=c.cid
LEFT JOIN executive_paycost d
ON a.paycostid=d.id
WHERE a.apply_id=' . $itemid[0] . ' AND a.isok=1';
		} else if ($itemid[1] === 'm') {
			$sql = 'SELECT a.id,a.pid,a.paycostid,b.name,c.cusname,d.payamount,"m" AS ptype
FROM 
finance_payment_media_apply_list a
LEFT JOIN v_last_executive b
ON a.pid=b.pid
LEFT JOIN contract_cus c
ON b.cid=c.cid
LEFT JOIN executive_paycost d
ON a.paycostid=d.id
WHERE a.apply_id=' . $itemid[0] . ' AND a.isok=1';
		}

		$pids = array();
		if ($sql !== '') {
			$results = $this->db->get_results($sql);
			if ($results !== NULL) {
				foreach ($results as $result) {
					$pids[] = $result->pid . '_' . $result->paycostid;
					$datas[] = array('aaa' => $result->pid,
							'bbb' => urlencode($result->cusname),
							'ccc' => urlencode($result->name), 'ddd' => '',
							'eee' => '', 'fff' => '',
							'ggg' => $result->payamount, 'hhh' => '',
							'iii' => '', 'jjj' => '', 'kkk' => '', 'lll' => '',
							'mmm' => '',
							'nnn' => '<input type="text" class="validate[required,custom[invoiceMoney]]" name="amount_'
									. $result->pid . '_' . $result->paycostid
									. '" id="amount_' . $result->pid . '_'
									. $result->paycostid
									. '" style="height:20px;">',);
				}
			}

			$total = count($datas);
		}

		return urldecode(
				json_encode(
						array('total' => $total, 'rows' => $datas,
								'pids' => !empty($pids) ? ','
												. implode(',', $pids) . ',' : '')));
	}

	private function _getUserPermission() {
		$datas = array();
		$results = $this->db
				->get_results(
						'SELECT uid,username,realname,permissions FROM users WHERE  islive=1 AND (permissions IS NOT NULL OR permissions<>"")');
		if ($results !== NULL) {
			foreach ($results as $result) {
				$permissions = explode('^', $result->permissions);
				foreach ($permissions as $permission) {
					if (!empty($permission)) {
						$datas[$permission][] = $result->realname . '（'
								. $result->username . '）';
					}
				}
			}
		}
		return $datas;
	}

	public function getPayFirstByDepartment() {
		$qs = $this->qs;
		$total = 0;
		$datas = array();

		$results = $this->db
				->get_results(
						'SELECT m.*,n.amount,c.cusname,u.city,u.dep,u.team,uu.username,uu.realname
FROM 
(
SELECT a.apply_id,a.list_id,a.payfirst_amount,b.pid,b.paycostid,b.payment_amount
FROM finance_payment_payfirst a
LEFT JOIN finance_payment_person_apply_list b
ON a.apply_id=b.apply_id AND a.list_id=b.id
WHERE a.payment_type=1 AND a.amount_type=1 AND a.status=1 AND b.isok=1

UNION ALL

SELECT a.apply_id,a.list_id,a.payfirst_amount,b.pid,b.paycostid,b.payment_amount
FROM finance_payment_payfirst a
LEFT JOIN finance_payment_media_apply_list b
ON a.apply_id=b.apply_id AND a.list_id=b.id
WHERE a.payment_type=2 AND a.amount_type=1 AND a.status=1 AND b.isok=1
) m
LEFT JOIN v_last_executive n
ON m.pid=n.pid
LEFT JOIN contract_cus c
ON n.cid=c.cid
LEFT JOIN users u
ON n.user=u.uid
LEFT JOIN users uu
ON n.principal=uu.uid WHERE u.city=' . intval($qs['city']) . ' AND u.dep='
								. intval($qs['dep']) . ' AND u.team='
								. intval($qs['team']));
		if ($results !== NULL) {

			//已收款
			$receives = $this->_getReceiveAmount();

			//已开票
			$invoices = $this->_getInvoice();

			//已付款
			$pay_recinvoice = $this->_getPaymentAndReceiveInvoice();

			$total = count($results);
			foreach ($results as $result) {
				$datas[] = array('aa' => $result->pid,
						'bb' => urlencode($result->cusname),
						'cc' => urlencode(
								$result->realname . '（' . $result->username
										. '）'), 'dd' => $result->amount,
						'ee' => empty($receives[$result->pid]) ? 0
								: $receives[$result->pid],
						'ff' => empty($invoices[$result->pid]) ? 0
								: $invoices[$result->pid],
						'gg' => $result->amount
								- (empty($receives[$result->pid]) ? 0
										: $receives[$result->pid]),
						'hh' => empty(
								$pay_recinvoice[$result->pid]['pay_amount']) ? 0
								: $pay_recinvoice[$result->pid]['pay_amount'],
						'ii' => $result->payment_amount,
						'jj' => $result->payfirst_amount,
						'kk' => $result->amount
								- (empty($receives[$result->pid]) ? 0
										: $receives[$result->pid]),
						'll' => $result->payfirst_amount
								- (empty($receives[$result->pid]) ? 0
										: $receives[$result->pid]));
			}
		}

		return urldecode(
				json_encode(array('total' => $total, 'rows' => $datas)));
	}

	public function getDepartNimpayfirst() {
		$total = 0;
		$datas = array();

		$results = $this->db
				->get_results(
						'SELECT m.*,o.companyname,p.depname,q.teamname,u.city,u.dep,u.team
FROM
(
SELECT a.apply_id,a.list_id,a.payfirst_amount,b.pid,b.paycostid
FROM finance_payment_payfirst a
LEFT JOIN finance_payment_person_apply_list b
ON a.apply_id=b.apply_id AND a.list_id=b.id
WHERE a.payment_type=1 AND a.amount_type=1 AND a.status=1 AND b.isok=1

UNION ALL

SELECT a.apply_id,a.list_id,a.payfirst_amount,b.pid,b.paycostid
FROM finance_payment_payfirst a
LEFT JOIN finance_payment_media_apply_list b
ON a.apply_id=b.apply_id AND a.list_id=b.id
WHERE a.payment_type=2 AND a.amount_type=1 AND a.status=1 AND b.isok=1
) m 
LEFT JOIN v_last_executive n
ON m.pid=n.pid
LEFT JOIN users u
ON n.user=u.uid
LEFT JOIN hr_company o
ON u.city=o.id
LEFT JOIN hr_department p
ON u.dep=p.id
LEFT JOIN hr_team q
ON u.team=q.id');
		$tmp = array();
		if ($results !== NULL) {
			$receives = $this->_getReceiveAmount();

			$up = $this->_getUserPermission();

			$dep_leader = array();
			$_dep_permission = Permission_Dep::getInstance();
			foreach ($_dep_permission as $dep => $v) {
				foreach ($v as $vv) {
					if (strpos($vv['permission_name'], 'leader') !== FALSE) {
						if (intval($dep) !== 3) {
							$dep_leader[$dep][0] = 'dep' . $vv['permission_id'];
						} else {
							$dep_leader[3][3] = 'dep89';
							$dep_leader[3][6] = 'dep90';
							$dep_leader[3][7] = 'dep91';
							$dep_leader[3][8] = 'dep109';
						}
					}
				}
			}

			foreach ($results as $result) {
				$tmp['payfirst'][$result->city . '_' . $result->dep . '_'
						. $result->team] += $result->payfirst_amount;
				$tmp['collection'][$result->city . '_' . $result->dep . '_'
						. $result->team] += (empty($receives[$result->pid]) ? 0
						: $receives[$result->pid]);
				if (empty(
						$tmp['depname'][$result->city . '_' . $result->dep
								. '_' . $result->team])) {
					$tmp['depname'][$result->city . '_' . $result->dep . '_'
							. $result->team] = $result->companyname . ' '
							. $result->depname . ' ' . $result->teamname;
				}
			}
		}

		if (!empty($tmp)) {
			$total = count(array_keys($tmp['payfirst']));
			foreach ($tmp['payfirst'] as $key => $value) {
				$dep = explode('_', $key);
				$paid = $value;
				$col = $tmp['collection'][$key];
				$ups = $up[$dep_leader[$dep[1]][$dep[2]]];
				$datas[] = array('a' => urlencode($tmp['depname'][$key]),
						'b' => urlencode(implode('，', $ups)),
						'c' => $paid - $col,
						'd' => '<input type="button" value="展开" class="btn" onclick="javascript:openit(\''
								. $key . '\')"/>',);
			}
		}
		return urldecode(
				json_encode(array('total' => $total, 'rows' => $datas)));
	}

	public function getExecutiveCYByID() {
		$total = 0;
		$datas = array();
		$footers = array();
		$sum = array();
		$qs = $this->qs;
		$results = $this->db
				->get_results(
						'SELECT b.supplier_name,c.category_name,d.media_short,a.*,e.industry_name
FROM
(
SELECT id,ym,cost_amount,quote_amount,finance_cost_amount,finance_quote_amount,supplier_id,supplier_short_id,category_id,industry_id FROM executive_cy WHERE executive_id='
								. intval($qs['executive_id'])
								. (intval($qs['dep']) > 0 ? ' AND is_support=1 AND support_dep='
												. intval($qs['dep'])
										: ' AND is_support=0 AND support_dep=0')
								. ') a
LEFT JOIN new_supplier b
ON a.supplier_id=b.id
LEFT JOIN new_supplier_category c
ON a.category_id=c.id
LEFT JOIN finance_supplier_short d
ON a.supplier_short_id=d.id
LEFT JOIN new_supplier_industry e
ON a.industry_id=e.id AND a.supplier_short_id=e.supplier_short_id
ORDER BY b.supplier_name,c.category_name,a.ym');

		if ($results !== NULL) {
			foreach ($results as $result) {
				$cost_key = 'cost_' . $result->ym;
				//$quote_key = 'quote_' . $result->ym;

				/*
				$datas[$result->supplier_name . '_' . $result->category_name]['a'] = urlencode(
						$result->supplier_name);
				$datas[$result->supplier_name . '_' . $result->category_name]['b'] = urlencode(
						$result->media_short);
				$datas[$result->supplier_name . '_' . $result->category_name]['c'] = urlencode(
						$result->category_name);
				$datas[$result->supplier_name . '_' . $result->category_name]['d'] = urlencode(
						$result->industry_name);
				$datas[$result->supplier_name . '_' . $result->category_name][$cost_key] = Format_Util::my_money_format(
						'%.2n', $result->cost_amount);
				$datas[$result->supplier_name . '_' . $result->category_name][$quote_key] = Format_Util::my_money_format(
						'%.2n', $result->quote_amount);
				*/
				$datas[$result->supplier_id . '_' . $result->supplier_short_id . '_' . $result->category_id . '_' . $result->industry_id]['a'] = urlencode(
						$result->supplier_name);
				$datas[$result->supplier_id . '_' . $result->supplier_short_id . '_' . $result->category_id . '_' . $result->industry_id]['b'] = urlencode(
						$result->media_short);
				$datas[$result->supplier_id . '_' . $result->supplier_short_id . '_' . $result->category_id . '_' . $result->industry_id]['c'] = urlencode(
						$result->category_name);
				$datas[$result->supplier_id . '_' . $result->supplier_short_id . '_' . $result->category_id . '_' . $result->industry_id]['d'] = urlencode(
						$result->industry_name);
				$datas[$result->supplier_id . '_' . $result->supplier_short_id . '_' . $result->category_id . '_' . $result->industry_id][$cost_key]  += $result->cost_amount;
				//$datas[$result->supplier_id . '_' . $result->supplier_short_id . '_' . $result->category_id . '_' . $result->industry_id][$quote_key] = Format_Util::my_money_format(
				//		'%.2n', $result->quote_amount);
				
				/*
				if (in_array($this->getUsername(),
						$GLOBALS['view_executive_finance_permission'], TRUE)) {
					$fcost_key = 'fcost_' . $result->ym;
					$fquote_key = 'fquote_' . $result->ym;
					$datas[$result->supplier_id . '_' . $result->supplier_short_id . '_' . $result->category_id . '_' . $result->industry_id][$fcost_key] = Format_Util::my_money_format(
							'%.2n', $result->finance_cost_amount);
					$datas[$result->supplier_id . '_' . $result->supplier_short_id . '_' . $result->category_id . '_' . $result->industry_id][$fquote_key] = Format_Util::my_money_format(
							'%.2n', $result->finance_quote_amount);
					$sum[$fcost_key] += $result->finance_cost_amount;
					$sum[$fquote_key] += $result->finance_quote_amount;
				}
				*/
				$sum[$cost_key] += $result->cost_amount;
				//$sum[$quote_key] += $result->quote_amount;
			}

			$footers['b'] = urlencode('小计');
			foreach ($sum as $key => $value) {
				$footers[$key] = Format_Util::my_money_format('%.2n', $value);
			}
			
			foreach ($datas as $key=>$value){
				foreach ($value as $k=>$v){
					if(strpos($k, 'cost_') !== FALSE){
						$datas[$key][$k] = Format_Util::my_money_format('%.2n', $v );
					}
				}
			}
			$total = count($datas);
		}
		return urldecode(
				json_encode(
						array('total' => $total,
								'rows' => array_values($datas),
								'footer' => array($footers))));
	}

	public function getOutsourcingByID() {
		$total = 0;
		$datas = array();
		$qs = $this->qs;

		$results = $this->db
				->get_results(
						'SELECT a.id,a.payname,a.payamount FROM executive_paycost a LEFT JOIN new_supplier b ON a.payname=b.supplier_name WHERE b.id='
								. $qs['id']);
		$sql = array();
		$outs = array();
		$exedeps = array();
		if ($results !== NULL) {
			foreach ($results as $result) {
				$sql[] = 'SELECT id,pid,dep,costpaymentinfoids FROM executive_dep WHERE costpaymentinfoids="'
						. $result->id . '" OR costpaymentinfoids LIKE "'
						. $result->id . '^%" OR costpaymentinfoids LIKE "%^'
						. $result->id . '"';
				$outs[$result->id] = $result->payamount;
			}
		}

		if (!empty($sql)) {
			$results = $this->db->get_results(join(' UNION ALL ', $sql));
			if ($results !== NULL) {
				foreach ($results as $result) {
					$exedeps[$result->pid][$result->dep . '^' . $result->id] = array(
							'costpaymentinfoids' => $result->costpaymentinfoids);
				}
			}
			//var_dump($exedeps);

			$results = $this->db
					->get_results(
							'SELECT pid,name,support FROM  v_last_executive WHERE pid IN ("'
									. implode('","', array_keys($exedeps))
									. '")');
			if ($results !== NULL) {
				foreach ($results as $result) {
					$support = $result->support;
					$tmps = $exedeps[$result->pid];
					$amount = 0;
					foreach ($tmps as $key => $value) {
						if ($support === $key
								|| strpos($support, $key . '|') !== FALSE
								|| strpos($support, '|' . $key) !== FALSE) {
							$value = explode('^', $value['costpaymentinfoids']);

							foreach ($value as $v) {
								if (!empty($v)) {
									$amount += $outs[$v];
								}
							}
						}
					}
					$datas[] = array('a' => $result->pid, 'b' => $result->name,
							'c' => $amount);
				}
			}
		}
		return urldecode(
				json_encode(array('total' => $total, 'rows' => $datas)));
	}
}
