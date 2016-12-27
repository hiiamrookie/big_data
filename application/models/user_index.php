<?php
class User_Index extends User {
	private $result = array();
	private $executive_count = 0;
	private $contract_count = 0;

	public function __construct($ismobile = FALSE,$token=NULL) {
		parent::__construct($ismobile,$token);
		$this->_get_pending_items();
	}

	private function _get_pending_items() {
		if ($this->getUid() !== NULL) {
			$result = array();
			$all_executies = array();
			$all_contracts = array();

			//2015.12.16 OA移动版添加
			$mobile_executies = array();

			$process = Process::getInstance();
			$dep_process = Dep_Process::getInstance();
			$dep = Dep::getInstance();
			$dep_role = Permission_Dep::getInstance();

			$process = $process['step'];

			//执行单
			$executives = $this->db
					->get_results(
							'SELECT a.*,FROM_UNIXTIME(a.time) AS tt,b.cusname FROM executive a , contract_cus b WHERE a.cid = b.cid AND a.isok=0 ORDER BY time DESC');

			if ($executives !== NULL) {
				foreach ($executives as $executive) {
					$is_ok = FALSE;
					$is_mobile_ok = FALSE;

					$pcid = $executive->pcid;
					$step = $executive->step;
					$pmcode = $process[$pcid][$step]['content'][2];
					$__pid = $executive->pid;

					if ($pmcode === EXECUTIVE_MODULE) { //权限为执行单发起人，特殊处理
						if (intval($executive->user)
								=== intval($this->getUid())) {
							$is_ok = TRUE;
						}
					} else if ($pmcode === EXECUTIVE_IN_CHARGE) { //执行单项目负责人
						if (intval($executive->principal)
								=== intval($this->getUid())) {
							$is_ok = TRUE;
							$is_mobile_ok = TRUE;
						}
					} else if ($pmcode === DEP_SUPPORT) { //部门支持
						$support = $executive->support;
						if (!empty($support)) {
							$support = explode('|', $support);

							foreach ($support as $s) {
								$s = explode('^', $s);
								$row = $this->db
										->get_row(
												'SELECT pcid,step FROM executive_dep WHERE id='
														. intval($s[1])
														. ' AND isok=0');

								if ($row !== NULL) {
									$_pcid = intval($row->pcid);
									$_step = intval($row->step);

									if ($_pcid !== 0) {
										$status = $dep[$s[0]][0] . '|'
												. $dep_process[$_pcid][$_step][0];
									} else {
										$status = $dep[$s[0]][0];
									}

									if ($_step === 0
											&& in_array(
													User::_get_dep_tf_id(
															$dep_role[$s[0]]),
													$this->getPermissions(),
													TRUE)
											|| in_array(
													$dep_process[$_pcid][$_step][2],
													$this->getPermissions(),
													TRUE)) {
										$all_executies[] = array(
												'usertype' => 'dep',
												'pmcode' => $pmcode,
												'isalter' => $executive
														->isalter,
												'pid' => $executive->pid,
												'name' => $executive->name,
												'amount' => $executive->amount,
												'isyg' => $executive->isyg,
												'allcost' => $executive
														->allcost,
												'status' => $status,
												'depid' => $s[0],
												'cusname' => $executive
														->cusname,
												'id' => $executive->id,
												'user' => $executive->user);

										$mobile_executies[] = array(
												'id' => $executive->id,
												'pid' => $executive->pid,
												'isalter' => $executive
														->isalter,
												'cusname' => $executive
														->cusname,
												'name' => $executive->name,
												'amount' => $executive->amount,
												'allcost' => $executive
														->allcost,
												'isyg' => $executive->isyg,
												'pmcode' => $pmcode,
												'depid' => $s[0]);
									}
								}
							}
						}
					} else if (in_array($pmcode, $this->getPermissions())) { //有权限
						$is_ok = TRUE;
						$is_mobile_ok = TRUE;
					}

					if ($is_ok) {
						//符合要求的记录
						$all_executies[] = array('usertype' => 'other',
								'pmcode' => $pmcode,
								'isalter' => $executive->isalter,
								'pid' => $executive->pid,
								'name' => $executive->name,
								'amount' => $executive->amount,
								'isyg' => $executive->isyg,
								'allcost' => $executive->allcost,
								'status' => $process[$pcid][$step]['content'][0],
								'cusname' => $executive->cusname,
								'step' => $step, 'id' => $executive->id,
								'user' => $executive->user);
					}

					if ($is_mobile_ok) {
						$mobile_executies[] = array('id' => $executive->id,
								'pid' => $executive->pid,
								'isalter' => $executive->isalter,
								'cusname' => $executive->cusname,
								'name' => $executive->name,
								'amount' => $executive->amount,
								'allcost' => $executive->allcost,
								'isyg' => $executive->isyg);
					}
				}
			}
			$result['executive'] = $all_executies;
			$this->executive_count = count($all_executies);

			$result['mobile_executive'] = $mobile_executies;

			//合同
			$contracts = $this->db
					->get_results(
							'SELECT *,FROM_UNIXTIME(time) AS tt FROM contract_cus WHERE isok=0 ORDER BY time DESC');

			if ($contracts !== NULL) {
				foreach ($contracts as $contract) {
					$pcid = $contract->pcid;
					$step = $contract->step;
					$pmcode = $process[$pcid][$step]['content'][2];

					if (in_array($pmcode, $this->getPermissions())) { //有权限
						$all_contracts[] = array(
								'contractstatus' => $contract->contractstatus,
								'cid' => $contract->cid,
								'time' => $contract->tt,
								'type' => $contract->type,
								'contractname' => $contract->contractname,
								'cusname' => $contract->cusname,
								'contractamount' => $contract->contractamount,
								'status' => $process[$pcid][$step]['content'][0],
								'step' => $step);
					}
				}
			}
			$result['contract'] = $all_contracts;
			$this->contract_count = count($all_contracts);
			$this->result = $result;
		}
	}

	public function getMobilePendingExecutive() {
		$result = $this->result;
		$mobile_executive = $result['mobile_executive'];

		$r = array();
		foreach ($mobile_executive as $me) {
			$tmp = array();
			foreach ($me as $key => $value) {
				$tmp[$key] = urlencode($value);
			}
			$r[] = $tmp;
		}
		return urldecode(
				json_encode(
						array('status' => 'success',
								'message' => urlencode('获取成功'),
								'pending' => count($r),
								'executive' => array('count' => count($r),
										'datas' => $r,'token'=>$this->getToken()))));
	}

	/**
	 * @return the $executive_count
	 */
	public function getExecutive_count() {
		return $this->executive_count;
	}

	/**
	 * @return the $contract_count
	 */
	public function getContract_count() {
		return $this->contract_count;
	}

	private static function _get_executive_type($isalter) {
		return $isalter !== 0 ? '<font color="#cc6600">【变' . $isalter
						. '】</font>' : '<font color="#66cc00">【新】</font>';
	}

	private static function _get_executive_name($id, $pid, $name) {
		return '<a href="' . BASE_URL . 'executive/?o=info&id=' . $id . '&pid='
				. $pid . '" target="_blank"><b>' . $name . '</b></a>';
	}

	private static function _get_executive_amount($amount) {
		return '<font color="#ff9933"><b>'
				. Format_Util::my_money_format('%.2n', $amount) . '</b></font>';
	}

	private static function _get_executive_allcost($isyg, $allcost) {
		return '<font color="' . ($isyg > 0 ? '#0000FF' : '#ff9933') . '"><b>'
				. Format_Util::my_money_format('%.2n', $allcost)
				. '</b></font>';
	}

	private static function _get_contract_action($cid, $step) {
		return $step === 0 ? '<a href="' . BASE_URL
						. 'contract_cus/?o=edit&cid=' . $cid . '">修改</a>'
				: '<a href="' . BASE_URL . 'contract_cus/?o=audit&cid=' . $cid
						. '">审核</a>';
	}

	private static function _get_executive_status($isdep, $status) {
		if ($isdep) {
			$status = explode('|', $status);
			if (count($status) === 2) {
				return '<font color="#ff6600">等待支持部门 ' . $status[0] . ' <b>'
						. $status[1] . '</b> 审核</font>';
			} else {
				return '<font color="#ff6600">等待支持部门 ' . $status[0]
						. ' <b>TF</b> 审核</font>';
			}
		} else {
			return '<font color="#ff6600">等待 ' . $status . ' 审核</font>';
		}
	}

	private static function _get_executive_action($isdep, $step, $isalter, $pid,
			$id, $depid = NULL, $user, $uid, $status) {
		if ($isdep) {
			return '<a href="' . BASE_URL . 'executive/?o=auditdep&id=' . $id
					. '&pid=' . $pid . '&dep=' . $depid . '">审核</a>';
		} else {
			if ($step === 0) {
				if ($isalter !== 0) {
					return '<a href="' . BASE_URL . 'executive/?o=alter&pid='
							. $pid . '">变更</a>';
				} else {
					return '<a href="' . BASE_URL . 'executive/?o=edit&pid='
							. $pid . '">修改</a>';
				}
			} else {
				if (intval($user) === intval($uid) && $status === '执行单发起人') {
					return '<a href="' . BASE_URL . 'executive/?o=cy&id=' . $id
							. '&pid=' . $pid . '">填写拆月数据</a>';
				} else {
					return '<a href="' . BASE_URL . 'executive/?o=audit&id='
							. $id . '&pid=' . $pid . '">审核</a>';
				}
			}
		}
	}

	private static function _get_contract_gd($contract_status) {
		return $contract_status === 1 ? '<font color="#009900">【是】</font>'
				: '<font color="red">【否】</font>';
	}

	private static function _get_contract_type($contract_type) {
		return $contract_type === 1 ? '框架' : '单笔';
	}

	private static function _get_contract_name($cid, $contractname) {
		return '<a href="' . BASE_URL . 'contract_cus/?o=info&cid=' . $cid
				. '" target="_blank"><b>' . $contractname . '</b></a>';
	}

	private static function _get_contract_status($status) {
		return '<font color="#ff6600">等待 ' . $status . ' 审核</font>';
	}

	public function get_executives_list_html() {
		$result = '';
		$executives = $this->result;
		$executives = $executives['executive'];

		if (!empty($executives)) {
			foreach ($executives as $executive) {
				$result .= '<tr><td>'
						. self::_get_executive_type(
								intval($executive['isalter']))
						. '</td><td><b><font size="2">' . $executive['pid']
						. '</font></b></td><td>' . $executive['cusname']
						. '</td><td>'
						. self::_get_executive_name($executive['id'],
								$executive['pid'], $executive['name'])
						. '</td><td>'
						. self::_get_executive_amount($executive['amount'])
						. '</td><td>'
						. self::_get_executive_allcost(
								intval($executive['isyg']),
								$executive['allcost']) . '</td><td>'
						. self::_get_executive_status(
								$executive['usertype'] === 'dep',
								$executive['status']) . '</td><td>'
						. self::_get_executive_action(
								$executive['usertype'] === 'dep',
								intval($executive['step']),
								intval($executive['isalter']),
								$executive['pid'], intval($executive['id']),
								$executive['depid'], $executive['user'],
								$this->getUid(), $executive['status'])
						. '</td></tr>';
			}
		} else {
			$result = '<tr><td colspan="8"><font color="red">当前没有待处理执行单！</font></td></tr>';
		}
		return $result;
	}

	public function get_contract_list_html() {
		$result = '';
		$contracts = $this->result;
		$contracts = $contracts['contract'];

		if (!empty($contracts)) {
			foreach ($contracts as $contract) {
				$result .= '<tr><td>'
						. self::_get_contract_gd(
								intval($contract['contractstatus']))
						. '</td><td><b><font size="2">' . $contract['cid']
						. '</font></b></td><td>' . $contract['time']
						. '</td><td>'
						. self::_get_contract_type(intval($contract['type']))
						. '</td><td>'
						. self::_get_contract_name($contract['cid'],
								$contract['contractname']) . '</td><td>'
						. $contract['cusname']
						. '</td><td><font color="#ff9933"><b>'
						. Format_Util::my_money_format('%.2n',
								$contract['contractamount'])
						. '</b></font></td><td>'
						. self::_get_contract_status($contract['status'])
						. '</td><td>'
						. self::_get_contract_action($contract['cid'],
								intval($contract['step'])) . '</td></tr>';
			}
		} else {
			$result = '<tr><td colspan="9"><font color="red">当前没有待处理合同！</font></td></tr>';
		}
		return $result;
	}

	public function get_invoice_tab() {
		if ($this->getHas_invoice_tab()) {
			return '<li><a>待处理部门开票申请 (<span>' . count($this->getInvoices())
					. '</span>)</a></li>';
		}
		return '';
	}

	public function get_contract_payment_tab() {
		if ($this->getHas_contract_payment_tab()) {
			return '<li><a>待处理部门合同款付款申请 (<span>'
					. count($this->getContractPayments()) . '</span>)</a></li>';
		}
		return '';
	}

	public function getOutsourcingAuditTab() {
		if ($this->getHasOutsourcingAuditTab()) {
			return '<li><a>待审核执行单外包信息 (<span>'
					. count($this->getOutsourcingAudit()) . '</span>)</a></li>';
		}
		return '';
	}

	public function get_deposit_tab() {
		if ($this->getHas_deposit_tab()) {
			return '<li><a>待处理部门保证金相关申请 (<span>'
					. (count($this->getDeposits())
							+ count($this->getDeposit_invoices()))
					. '</span>)</a></li>';
		}
		return '';
	}

	public function get_supplier_apply_audit_tab() {
		if ($this->getHas_supplier_apply_audit_tab()) {
			return '<li><a>待处理供应商申请 (<span>'
					. (count($this->getSuplier_apply())) . '</span>)</a></li>';
		}
		return '';
	}

	public function getOutsourcingAuditList() {
		$s = '';
		if ($this->getHasOutsourcingAuditTab()) {
			$s .= '<div class="undis"><div class="listform fix"><table class="etable" cellpadding="0" cellspacing="0" border="0" id="supplier_list"><thead><tr>
			<th>执行单号</th>
			<th>项目名称</th>
	       	<th>执行单发起时间</th>
	       	<th>外包名称</th>
	      	<th>外包成本</th>
	      	<th>操作</th>
			</tr></thead><tbody>';
			$lists = $this->getOutsourcingAudit();
			if (!empty($lists)) {
				foreach ($lists as $list) {
					$row = $this->db
							->get_row(
									'SELECT pid,name,FROM_UNIXTIME(time) AS time,support FROM executive WHERE id='
											. intval($list['executive_id']));
					$support = $row->support;
					$pay_array = array();
					if (!empty($support)) {
						$support = explode('|', $support);
						foreach ($support as $ss) {
							$ss = explode('^', $ss);

							if (intval($ss[0]) === intval($list['support_id'])) {
								$costpaymentinfoids = $this->db
										->get_var(
												'SELECT costpaymentinfoids FROM executive_dep WHERE id='
														. intval($ss[1]));

								if (!empty($costpaymentinfoids)) {
									$costpaymentinfoids = explode('^',
											$costpaymentinfoids);
									$costpaymentinfoids = Array_Util::my_remove_array_other_value(
											$costpaymentinfoids,
											array('', NULL));
									$pays = $this->db
											->get_results(
													'SELECT a.payname,a.payamount FROM executive_paycost a LEFT JOIN new_supplier b
ON a.payname=b.supplier_name
LEFT JOIN new_supplier_info c
ON b.id=c.supplier_id WHERE a.id IN ('
															. implode(',',
																	$costpaymentinfoids)
															. ') AND c.supplier_type=2');

									if ($pays !== NULL) {
										foreach ($pays as $pay) {
											$pay_array[$pay->payname] += $pay
													->payamount;
										}
									}
								}
								break;
							}
						}
					}

					$s .= '<tr><td>' . $row->pid . '</td><td>' . $row->name
							. '</td><td>' . $row->time . '</td><td>'
							. implode('<br/>', array_keys($pay_array))
							. '</td><td>'
							. implode('<br/>', array_values($pay_array))
							. '</td><td><a href="' . BASE_URL
							. 'outsourcing/?o=auditoutsourcing&id='
							. intval($list['executive_id'])
							. '">审核</a></td></tr>';

					//$s .= $list['executive_id'] . '~' . $list['pid'] . '~' . $list['support_id'];
				}
			} else {
				$s .= '<tr><td colspan="6"><font color="red">当前没有待审核执行单外包信息！</font></td></tr>';
			}

			$s .= '</tbody></table></div></div>';
		}
		return $s;
	}

	public function get_contract_payment_list() {
		$s = '';
		if ($this->getHas_contract_payment_tab()) {
			$s .= '<div class="undis"><div class="listform fix"><table class="etable" cellpadding="0" cellspacing="0" border="0" id="supplier_list"><thead><tr>
			<th>付款单号</th>
	       	<th>发起申请时间</th>
	       	<th>媒体名称</th>
	      	<th>约定付款时间</th>
	      	<th>应付金额</th>
	       	<th>实付金额</th>
	      	<th>操作</th>
			</tr></thead><tbody>';

			$payments = $this->getContractPayments();
			if (!empty($payments)) {
				foreach ($payments as $payment) {
					$s .= '<tr><td>' . $payment->payment_id . '</td><td>'
							. $payment->addtime . '</td><td>'
							. $payment->media_name . '</td><td>'
							. $payment->payment_date . '</td><td>'
							. $payment->payment_amount_plan . '</td><td>'
							. $payment->payment_amount_real
							. '</td><td><a href="' . BASE_URL
							. 'finance/payment/?o=person_apply_manager_audit&id='
							. $payment->id . '">审核</a></td></tr>';
				}
			} else {
				$s .= '<tr><td colspan="7"><font color="red">当前没有待处理部门合同款付款申请！</font></td></tr>';
			}

			$s .= '</tbody></table></div></div>';
		}

		return $s;
	}

	public function get_supplier_apply_list() {
		$s = '';
		if ($this->getHas_supplier_apply_audit_tab()) {

			$s .= '<div class="undis"><div class="listform fix"><table class="etable" cellpadding="0" cellspacing="0" border="0" id="supplier_list"><thead><tr>
          <th>申请时间</th>
          <th>供应商名称</th>
          <th>网址</th>
          <th>是否有抵扣联</th>
          <th>进票税率(%)</th>
          <th>供应商类型</th>
          <th>操作</th></tr></thead><tbody>';

			$suppliers = $this->getSuplier_apply();
			if (!empty($suppliers)) {
				foreach ($suppliers as $key => $supplier) {

					$audit = '';
					if (intval($supplier->step) === 0) {

						if (in_array($this->getUsername(),
								$GLOBALS['supplier_apply_check_permission'],
								TRUE)) {
							$audit = '<a href="' . BASE_URL
									. 'finance/supplier/?o=audit&id='
									. intval($supplier->id) . '">审核</a>';
						} //else {
						//	$audit = Supplier_Apply_List::get_supplier_status(
						//			intval($supplier->isok),
						//			intval($supplier->step));
						//}
					}
					/*
					else if (intval($supplier->step) === 1) {
					    if (strtolower($this->getUsername()) === 'kate.wan') {
					        $audit = '<a href="' . BASE_URL
					                . 'finance/supplier/?o=audit&id='
					                . intval($supplier->id) . '">审核</a>';
					    } else {
					        $audit = Supplier_Apply_List::get_supplier_status(
					                intval($supplier->isok),
					                intval($supplier->step));
					    }
					}
					 */

					$s .= '<tr>
          <td>' . $supplier->addtime . '</td>
          <td>' . $supplier->supplier_name . '</td>
          <td>' . $supplier->url . '</td>
          <td>'
							. Supplier_Apply_List::get_deduction(
									intval($supplier->deduction))
							. '</td>
          <td>' . $supplier->in_invoice_tax_rate . '%</td>
          <td>'
							. Supplier_Apply_List::get_supplier_type(
									intval($supplier->supplier_type))
							. '</td>
          <td>' . $audit . '</td></tr>';
				}
			} else {
				$s .= '<tr><td colspan="7"><font color="red">当前没有待处理供应商申请！</font></td></tr>';
			}

			$s .= '</tbody></table></div></div>';
		}
		return $s;
	}

	public function get_deposit_list() {
		$s = '';
		if ($this->getHas_deposit_tab()) {
			//$s = '<div class="undis"><div class="listform fix"><table class="etable" cellpadding="0" cellspacing="0" border="0" id="deposit_list"><thead><tr><th width="5%">编号</th><th>申请时间</th><th>申请类型</th><th width="10%">所属(包含)合同</th><th width="25%">客户名称</th><th width="10%">金额</th><th>申请人</th><th width="10%">操作</th></tr></thead><tbody>';
			$s .= '<div class="undis"><div class="listform fix"><table class="etable" cellpadding="0" cellspacing="0" border="0" id="deposit_list"><thead><tr><th width="5%">编号</th><th>申请时间</th><th>申请类型</th><th width="10%">金额</th><th>申请人</th><th width="10%">操作</th></tr></thead><tbody>';

			$deposits = $this->getDeposits();
			$deposit_invoices = $this->getDeposit_invoices();
			if (!(empty($deposits) && empty($deposit_invoices))) {
				foreach ($deposits as $key => $deposit) {
					$s .= '<tr><td>' . ($key + 1) . '</td><td>'
							. date('Y-m-d H:i:s', $deposit->addtime)
							. '</td><td>'
							. (intval($deposit->deposit_type) === 1 ? '保证金申请'
									: '保证金票据申请')
							. '</td><td><font color="#ff9933"><b>'
							. Format_Util::my_money_format('%.2n',
									$deposit->amount) . '</b></font></td><td>'
							. $deposit->realname . ' (' . $deposit->username
							. ')</td><td><a href="' . BASE_URL
							. 'finance/deposit/?o=leader_audit&type='
							. intval($deposit->deposit_type) . '&id='
							. intval($deposit->id) . '">审核</a></td></tr>';
				}
				$decount = count($deposits);
				foreach ($deposit_invoices as $key => $deposit_invoice) {
					$cids = $deposit_invoice->cids;
					$s .= '<tr><td>' . ($key + 1 + $decount) . '</td><td>'
							. date('Y-m-d H:i:s', $deposit_invoice->time)
							. '</td><td>'
							. (intval($deposit_invoice->deposit_type) === 1 ? '保证金申请'
									: '保证金票据申请')
							. '</td><td><font color="#ff9933"><b>'
							. Format_Util::my_money_format('%.2n',
									$deposit_invoice->amount)
							. '</b></font></td><td>'
							. $deposit_invoice->realname . ' ('
							. $deposit_invoice->username
							. ')</td><td><a href="' . BASE_URL
							. 'finance/deposit/?o=leader_audit&type='
							. intval($deposit_invoice->deposit_type) . '&id='
							. intval($deposit_invoice->id)
							. '">审核</a></td></tr>';
				}
			} else {
				$s .= '<tr><td colspan="8"><font color="red">当前没有待处理保证金相关申请！</font></td></tr>';
			}
			$s .= '</tbody></table></div></div>';
		}
		return $s;
	}

	public function get_invoice_list() {
		$s = '';
		if ($this->getHas_invoice_tab()) {
			$s .= '<div class="undis"><div class="listform fix"><table class="etable" cellpadding="0" cellspacing="0" border="0" id="invoice_list"><thead><tr><th width="5%">编号</th><th width="10%">申请时间</th><th width="25%">开票金额</th><th width="25%">开票类型</th><th width="10%">所属公司</th><th>申请人</th><th width="10%">操作</th></tr></thead><tbody>';

			$invoices = $this->getInvoices();
			if (!empty($invoices)) {
				foreach ($invoices as $key => $invoice) {
					$s .= '<tr><td>' . ($key + 1) . '</td><td>' . $invoice->tt
							. '</td><td><font color="#ff9933"><b>'
							. Format_Util::my_money_format('%.2n',
									$invoice->amount)
							. '</b></font></td><td title="'
							. Invoice_List::get_invoice_type_dd(
									intval($invoice->type), $invoice->d1,
									$invoice->d2, $invoice->d3) . '">'
							. Invoice_List::get_invoice_type(
									intval($invoice->type)) . '</td><td>'
							. $invoice->company . '</td><td>'
							. $invoice->realname . ' (' . $invoice->username
							. ')</td><td><a href="' . BASE_URL
							. 'finance/invoice/?o=leader_audit&id='
							. intval($invoice->id) . '">审核</a></td></tr>';
				}
			} else {
				$s .= '<tr><td colspan="7"><font color="red">当前没有待处理开票申请！</font></td></tr>';
			}

			$s .= '</tbody></table></div></div>';
		}
		return $s;
	}

	public function getPaymentMessage() {
		$s = '';

		//获得付款申请提醒
		//1.判断今天是星期几
		$w = intval(date('w', time()));
		if ($w >= 1 && $w <= 4) {
			//2.如果今天是周1～周4，检查+1 day的时间
			$need_date = array(date('Y-m-d', strtotime('+1 day')));
		} else {
			//周5需要查看周6，周日，下周1的时间
			$need_date = array(date('Y-m-d', strtotime('+1 day')),
					date('Y-m-d', strtotime('+2 day')),
					date('Y-m-d', strtotime('+3 day')));
		}

		if (in_array($this->getUsername(),
				$GLOBALS['manager_finance_permission'], TRUE)
				|| intval($this->getBelong_dep()) === 2) {
			//财务部查看所有
			$sql = 'SELECT media_info_id,payment_date,payment_amount_real,\'pp\' AS ptype FROM finance_payment_person_apply WHERE isok=1 AND payment_date IN ("'
					. implode('","', $need_date)
					. '")
					UNION ALL
					SELECT media_info_id,payment_date,payment_amount_real,\'pd\' AS ptype FROM finance_payment_person_deposit_apply WHERE isok=1 AND payment_date IN ("'
					. implode('","', $need_date)
					. '")
					UNION ALL
					SELECT media_info_id,payment_date,payment_amount_real,\'mp\' AS ptype FROM finance_payment_media_apply WHERE isok=1 AND payment_date IN ("'
					. implode('","', $need_date)
					. '")
					UNION ALL
					SELECT media_info_id,payment_date,payment_amount_real,\'m的\' AS ptype FROM finance_payment_media_deposit_apply WHERE isok=1 AND payment_date IN ("'
					. implode('","', $need_date) . '")';
		} else {
			//发起者查看自己的
			$sql = 'SELECT media_info_id,payment_date,payment_amount_real,\'pp\' AS ptype FROM finance_payment_person_apply WHERE user='
					. $this->uid . ' AND isok=1 AND payment_date IN ("'
					. implode('","', $need_date)
					. '")
					UNION ALL
					SELECT media_info_id,payment_date,payment_amount_real,\'pd\' AS ptype FROM finance_payment_person_deposit_apply WHERE user='
					. $this->uid . ' AND isok=1 AND payment_date IN ("'
					. implode('","', $need_date)
					. '")
					UNION ALL
					SELECT a.media_info_id,a.payment_date,a.payment_amount_real,\'mp\' AS ptype FROM finance_payment_media_apply a LEFT JOIN finance_payment_media_apply_user b
ON a.id=b.payment_media_apply_id  WHERE b.userid=' . $this->uid
					. ' AND b.isok=1 AND a.payment_date IN ("'
					. implode('","', $need_date)
					. '") AND a.isok=1
					UNION ALL
					SELECT a.media_info_id,a.payment_date,a.payment_amount_real,\'md\' AS ptype FROM finance_payment_media_deposit_apply a LEFT JOIN finance_payment_media_deposit_apply_user b
ON a.id=b.payment_media_apply_id WHERE b.userid=' . $this->uid
					. ' AND b.isok=1 AND a.payment_date IN ("'
					. implode('","', $need_date) . '") AND a.isok=1';
		}
		$sql = 'SELECT m.*,n.media_name FROM(' . $sql
				. ') m LEFT JOIN finance_payment_media_info n ON m.media_info_id=n.id';

		$paa = $this->db->get_results($sql);
		if (!empty($paa)) {
			$count = count($paa);
			$ss = '共有 <font color=\'red\'><b>' . $count
					. '</b></font> 条付款申请近期支付：<br/>';
			foreach ($paa as $val) {
				$ss .= $val->payment_date . ' 向 ' . $val->media_name
						. ' 支付  <font color=\'red\'><b>'
						. $val->payment_amount_real . '</b></font> 元<br/>';
			}

			$s = '$.messager.show({
        title:"付款提醒",
        width:400,
   	 	height:200,
        msg:"' . $ss
					. '",
        timeout:10000,
        showType:"fade"
    });';
		}
		return $s;
	}
}
