<?php
include(dirname(__FILE__) . '/inc/my_session.php');
include(dirname(__FILE__) . '/inc/model_require.php');
include(dirname(__FILE__) . '/inc/require_file.php');
include(dirname(__FILE__) . '/user_auth.php');
header('Content-type: text/html; charset=utf-8');

//校验vcode
$vcode = $uid . User::SALT_VALUE;
$is_ajax = TRUE;
include(dirname(__FILE__) . '/validate_vcode.php');

switch (strval(Security_Util::my_post('action'))) {
case 'get_deps_by_city':
	echo get_deps_select_by_city();
	break;
case 'get_teams_by_dep':
	echo get_teams_by_dep();
	break;
case 'get_user_by_dep_team':
	echo get_user_by_dep_team();
	break;
case 'get_payment_person_apply_pidinfo_search':
	echo get_payment_person_apply_pidinfo_search();
	break;
case 'get_payment_person_apply_pidinfo':
	echo get_payment_person_apply_pidinfo();
	break;
case 'get_payment_deposit_apply_cidinfo_search':
	echo get_payment_deposit_apply_cidinfo_search();
	break;
case 'get_payment_deposit_apply_cidinfo':
	echo get_payment_deposit_apply_cidinfo();
	break;
case 'get_payment_media_deposit_apply_cidinfo_search':
	echo get_payment_media_deposit_apply_cidinfo_search();
	break;
case 'get_receive_invoice_pidinfo_search':
	echo get_receive_invoice_pidinfo_search();
	break;
case 'get_receive_invoice_payment_search':
	echo get_receive_invoice_payment_search();
	break;
case 'get_virtual_invoice_payment_search':
	echo get_virtual_invoice_payment_search();
	break;
case 'get_reabte_invoice_pidinfo_search':
	echo get_reabte_invoice_pidinfo_search();
	break;
case 'get_reabte_invoice_pidinfo':
	echo get_reabte_invoice_pidinfo();
	break;
case 'selectPaymentList':
	echo selectPaymentList();
	break;
default:
	echo '';
}

function get_deps_select_by_city() {
	$cityid = Security_Util::my_post('cityid');
	$result = '';
	if (intval($cityid) > 0) {
		$deps = Dep::get_dep_by_city(intval($cityid));
		if (!empty($deps)) {
			foreach ($deps as $dep) {
				$result .= '<option value="' . $dep['dep_id'] . '">'
						. $dep['dep_name'] . '</option>';
			}
		}
	}
	return $result;
}

function get_teams_by_dep() {
	$depid = Security_Util::my_post('depid');
	$result = '';
	if (intval($depid) > 0) {
		$teams = Team::get_team_by_dep(intval($depid));
		if (!empty($teams)) {
			foreach ($teams as $team) {
				$result .= '<option value="' . $team['team_id'] . '">'
						. $team['team_name'] . '</option>';
			}
		}
	}
	return $result;
}

function get_user_by_dep_team() {
	$depid = intval(Security_Util::my_post('depid'));
	$teamid = intval(Security_Util::my_post('teamid'));
	if ($teamid > 0) {
		$team = new Team($teamid);
		return $team->get_users_select_html_by_team();
	} else {
		$dep = new Dep($depid);
		return $dep->get_users_select_html_by_dep();
	}
}

function get_upload_files($dids, $can_edit = FALSE, $attacheid = 'dids') {
	$s = '';
	$dao = new Dao_Impl();
	if (!empty($dids)) {
		$dids = explode('^', $dids);

		foreach ($dids as $did) {
			if (!empty($did)) {
				$row = $dao->db
						->get_row(
								'SELECT realname,size FROM uploadfile WHERE id='
										. intval($did));
				if (!empty($row)) {
					$s .= '<div><a href="' . BASE_URL . 'download.php?did='
							. intval($did) . '" target="_blank">'
							. $row->realname . '</a>';
					if ($can_edit) {
						$s .= ' &nbsp;(' . $row->size . ')&nbsp;<img src="'
								. BASE_URL
								. 'images/close.png" onclick="up_del(this,'
								. intval($did) . ',\'' . $attacheid . '\')"/>';
					}
					$s .= '</div>';
				}
			}
		}
	}
	return $s;
}

function get_virtual_invoice_payment_search() {
	$pids = explode(',', Security_Util::my_post('pids'));
	$apply_id = Security_Util::my_post('apply_id');
	$type = Security_Util::my_post('type');
	$isVirtual = $type === 'virtual';

	$subsql = array();
	foreach ($pids as $pid) {
		if (!empty($pid)) {
			$pid = explode('_', $pid);
			if ($pid[1] === 'p') {
				//个人申请
				$subsql[] = 'SELECT a.*,\'p\' AS ptype FROM finance_payment_person_apply_list a WHERE a.apply_id='
						. $pid[0] . ' AND (a.isok=0 OR a.isok=1)';
			} else if ($pid[1] === 'm') {
				//媒体批量申请
				$subsql[] = 'SELECT a.*,\'m\' AS ptype FROM finance_payment_media_apply_list a WHERE apply_id='
						. $pid[0] . ' AND (a.isok=0 OR a.isok=1)';
			}
		}
	}

	$dao = new Dao_Impl();

	$results = $dao->db->get_results(implode(' UNION ALL ', $subsql));
	$rows = array();
	$rows['total'] = count($results);

	$exist_data = array();
	$apply_id = intval($apply_id);
	if (!empty($apply_id)) {
		$row = $dao->db
				->get_row(
						'SELECT pid_list_ids FROM finance_receiveinvoice_source_pid WHERE id='
								. $apply_id . ' AND isok=1');
		if ($row !== NULL) {
			$pid_list_ids = substr($row->pid_list_ids, 1,
					strlen($row->pid_list_ids) - 2);

			$pid_list_ids = $dao->db
					->get_results(
							'SELECT pid,paycostid,amount,tax,sum_amount,apply_id,apply_list_id,apply_type FROM finance_receiveinvoice_pid_list WHERE id IN('
									. str_replace('^', ',', $pid_list_ids)
									. ')');

			if ($pid_list_ids !== NULL) {
				foreach ($pid_list_ids as $key => $pid_list_id) {
					$exist_data[$pid_list_id->apply_list_id . '_'
							. $pid_list_id->apply_id . '_'
							. $pid_list_id->apply_type] = array(
							'amount' => $pid_list_id->amount,
							'tax' => $pid_list_id->tax,
							'sum_amount' => $pid_list_id->sum_amount);
				}
			}
		}
	}

	foreach ($results as $result) {
		$key = $result->id . '_' . $result->apply_id . '_' . $result->ptype;
		$rows['rows'][] = array('x' => $key, 'a' => 'a', 'b' => 'b',
				'c' => 'c', 'd' => 'd', 'e' => 'e', 'f' => 'f', 'g' => 'g',
				'h' => 'h', 'i' => 'i', 'j' => 'j', 'k' => 'k', 'l' => 'l',
				'm' => 'm', 'n' => 'n', 'o' => 'o',
				'p' => '<input type="text" name="amount_' . $key
						. '" id="amount_' . $key
						. '" style="height:20px;" readonly value="'
						. ($exist_data[$key] !== NULL ? $exist_data[$key]['amount']
								: '') . '">',
				'q' => '<input type="text" name="tax_' . $key . '" id="tax_'
						. $key . '" style="height:20px;" readonly value="'
						. ($exist_data[$key] !== NULL ? $exist_data[$key]['tax']
								: '') . '">',
				'r' => '<input type="text" name="sumamount_' . $key
						. '" id="sumamount_' . $key
						. '" style="height:20px;" onblur="javascript:countTax(this,'
						. ($isVirtual ? 1 : 0) . ');" value="'
						. ($exist_data[$key] !== NULL ? $exist_data[$key]['sum_amount']
								: '') . '">', 's' => 's', 't' => 't',
				'u' => 'u', 'v' => 'v', 'w' => 'w', 'y' => 'y');

	}

	return json_encode($rows);
}

function get_receive_invoice_payment_search() {
	$pids = explode(',', Security_Util::my_post('pids'));
	$apply_id = Security_Util::my_post('apply_id');
	$type = Security_Util::my_post('type');
	$isVirtual = $type === 'virtual';

	$subsql = array();
	foreach ($pids as $pid) {
		if (!empty($pid)) {
			$pid = explode('_', $pid);
			if ($pid[1] === 'p') {
				//个人申请
				$subsql[] = 'SELECT a.*,\'p\' AS ptype FROM finance_payment_person_apply_list a WHERE a.apply_id='
						. $pid[0] . ' AND (a.isok=0 OR a.isok=1)';
			} else if ($pid[1] === 'm') {
				//媒体批量申请
				$subsql[] = 'SELECT a.*,\'m\' AS ptype FROM finance_payment_media_apply_list a WHERE apply_id='
						. $pid[0] . ' AND (a.isok=0 OR a.isok=1)';
			}
		}
	}

	$dao = new Dao_Impl();

	$results = $dao->db->get_results(implode(' UNION ALL ', $subsql));
	//var_dump(implode(' UNION ALL ', $subsql));
	$rows = array();
	$rows['total'] = count($results);

	$exist_data = array();
	$apply_id = intval($apply_id);
	if (!empty($apply_id)) {
		$row = $dao->db
				->get_row(
						'SELECT pid_list_ids FROM finance_receiveinvoice_source_pid WHERE id='
								. $apply_id . ' AND isok=1');
		if ($row !== NULL) {
			$pid_list_ids = substr($row->pid_list_ids, 1,
					strlen($row->pid_list_ids) - 2);

			$pid_list_ids = $dao->db
					->get_results(
							'SELECT pid,paycostid,amount,tax,sum_amount,apply_id,apply_list_id,apply_type FROM finance_receiveinvoice_pid_list WHERE id IN('
									. str_replace('^', ',', $pid_list_ids)
									. ')');

			if ($pid_list_ids !== NULL) {
				foreach ($pid_list_ids as $key => $pid_list_id) {
					$exist_data[$pid_list_id->apply_list_id . '_'
							. $pid_list_id->apply_id . '_'
							. $pid_list_id->apply_type] = array(
							'amount' => $pid_list_id->amount,
							'tax' => $pid_list_id->tax,
							'sum_amount' => $pid_list_id->sum_amount);
				}
			}
		}
	}

	$ec = array();
	$rs = $dao->db
			->get_results(
					'SELECT a.pid,a.name AS projectname,b.* FROM v_last_executive a LEFT JOIN contract_cus b ON a.cid=b.cid');
	if ($rs !== NULL) {
		foreach ($rs as $rrs) {
			$ec[$rrs->pid] = array('cid' => $rrs->cid,
					'contractamount' => $rrs->contractamount,
					'cusname' => $rrs->cusname,
					'projectname' => $rrs->projectname);
		}
	}

	$pc = array();
	$rc = $dao->db
			->get_results('SELECT id,payname,payamount FROM executive_paycost');
	if ($rc !== NULL) {
		foreach ($rc as $rrc) {
			$pc[$rrc->id] = array('payamount' => $rrc->payamount,
					'payname' => $rrc->payname);
		}
	}

	$pays = getGDAmountByPaycostid();

	foreach ($results as $result) {
		$key = $result->id . '_' . $result->apply_id . '_' . $result->ptype;
		if (!$isVirtual) {
			$rows['rows'][] = array('x' => $key,
					'a' => $ec[$result->pid]['cusname'],
					'b' => $ec[$result->pid]['cid'],
					'c' => $ec[$result->pid]['contractamount'], 'd' => '',
					'e' => $result->pid, 'f' => $ec[$result->pid]['cusname'],
					'g' => $ec[$result->pid]['projectname'],
					'h' => $pc[$result->paycostid]['payamount'],
					'i' => $pc[$result->paycostid]['payamount']
							- (empty($pays[$result->paycostid]) ? 0
									: $pays[$result->paycostid]),
					'j' => empty($pays[$result->paycostid]) ? 0
							: $pays[$result->paycostid], 'k' => '0',
					'l' => '0', 'm' => '0', 'n' => '', 'o' => '0',
					'p' => '<input type="text" name="amount_' . $key
							. '" id="amount_' . $key
							. '" style="height:20px;" readonly value="'
							. ($exist_data[$key] !== NULL ? $exist_data[$key]['amount']
									: '') . '">',
					'q' => '<input type="text" name="tax_' . $key
							. '" id="tax_' . $key
							. '" style="height:20px;" readonly value="'
							. ($exist_data[$key] !== NULL ? $exist_data[$key]['tax']
									: '') . '">',
					'r' => '<input type="text" name="sumamount_' . $key
							. '" id="sumamount_' . $key
							. '" style="height:20px;" onblur="javascript:countTax(this,'
							. ($isVirtual ? 1 : 0) . ');" value="'
							. ($exist_data[$key] !== NULL ? $exist_data[$key]['sum_amount']
									: $result->payment_amount) . '">',
					's' => '0', 't' => '', 'u' => '', 'v' => '', 'w' => '0',
					'y' => '0');
		} else {
			$rows['rows'][] = array('x' => $key, 'a' => $result->pid,
					'b' => $ec[$result->pid]['cusname'],
					'c' => $pc[$result->paycostid]['payname'],
					'd' => $pc[$result->paycostid]['payamount'],
					'e' => $pc[$result->paycostid]['payamount']
							- (empty($pays[$result->paycostid]) ? 0
									: $pays[$result->paycostid]),
					'f' => empty($pays[$result->paycostid]) ? 0
							: $pays[$result->paycostid], 'g' => 0, 'aa' => '0',
					'bb' => '0', 'h' => '', 'i' => 0,
					'j' => '<input type="text" name="amount_' . $key
							. '" id="amount_' . $key
							. '" style="height:20px;" readonly value="'
							. ($exist_data[$key] !== NULL ? $exist_data[$key]['amount']
									: '') . '">',
					'k' => '<input type="text" name="tax_' . $key
							. '" id="tax_' . $key
							. '" style="height:20px;" readonly value="'
							. ($exist_data[$key] !== NULL ? $exist_data[$key]['tax']
									: $result->payment_amount) . '">',
					'l' => '<input type="text" name="sumamount_' . $key
							. '" id="sumamount_' . $key
							. '" style="height:20px;" onblur="javascript:countTax(this,'
							. ($isVirtual ? 1 : 0) . ');" value="'
							. ($exist_data[$key] !== NULL ? $exist_data[$key]['sum_amount']
									: '') . '">', 'cc' => 0, 'm' => '0',
					'p' => '0',
					'q' => '<input type="text" name="taxrate_' . $key
							. '" id="taxrate_' . $key
							. '" style="height:20px;" onblur="javascript:countTax(this,'
							. ($isVirtual ? 1 : 0) . ');">', 
					//'r' => '<input type="text" name="sumamount_' . $key
					//		. '" id="sumamount_' . $key
					//		. '" style="height:20px;" onblur="javascript:countTax(this,'
					//		. ($isVirtual ? 1 : 0) . ');" value="'
					//		. ($exist_data[$key] !== NULL ? $exist_data[$key]['sum_amount']
					//				: '') . '">', 
					's' => '0',);
		}

	}

	return json_encode($rows);
}

function _getPidCusname() {
	$dao = new Dao_Impl();
	$datas = array();
	$results = $dao->db
			->get_results(
					'SELECT a.pid,b.cusname FROM v_last_executive a LEFT JOIN contract_cus b ON a.cid=b.cid');
	if ($results !== NULL) {
		foreach ($results as $result) {
			$datas[$result->pid] = $result->cusname;
		}
	}
	return $datas;
}

function _getPaymentAndReceiveInvoice($pid = NULL, $isEqulas = TRUE) {
	$dao = new Dao_Impl();
	$subsql = '';
	if (!empty($pid)) {
		if ($isEqulas) {
			$subsql .= ' AND pid="' . $pid . '"';
		} else {
			$subsql .= ' AND pid LIKE "%' . $pid . '%"';
		}
	}
	$results = $dao->db
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
							. $subsql . ' GROUP BY pid
	) b
	ON a.pid=b.pid');
	$pay_and_receiveinvoice = array();
	if ($results !== NULL) {
		foreach ($results as $result) {
			$tpid = !empty($result->apid) ? $result->apid : $result->bpid;
			$pay_and_receiveinvoice[$tpid] = array(
					'pay_amount' => $result->pay_amount,
					'receive_invoice_amount' => $result->receive_invoice_amount);
		}
	}
	return $pay_and_receiveinvoice;
}

function get_receive_invoice_pidinfo_search() {
	$pids = explode(',', Security_Util::my_post('pids'));
	$action = Security_Util::my_post('type');
	$apply_id = Security_Util::my_post('apply_id');
	$type = Security_Util::my_post('type');
	$isVirtual = $type === 'virtual';

	$dao = new Dao_Impl();
	$results = array();
	$costids = array();
	foreach ($pids as $pid) {
		if ($pid !== '') {
			$pid = explode('_', $pid);
			$costids[$pid[1]] = $pid[0];
		}
	}

	$keys = array_keys($costids);
	$values = array_unique(array_values($costids));
	$cost_array = array();
	$pid_array = array();
	if (!empty($keys)) {
		$cost_array = $dao->db
				->get_results(
						'SELECT id,payname,payamount FROM executive_paycost WHERE id IN ('
								. implode(',', $keys) . ')');
	}

	$exist_data = array();
	$apply_id = intval($apply_id);
	if (!empty($apply_id)) {
		$row = $dao->db
				->get_row(
						'SELECT pid_list_ids FROM finance_receiveinvoice_source_pid WHERE id='
								. $apply_id . ' AND isok=1');
		if ($row !== NULL) {
			$pid_list_ids = substr($row->pid_list_ids, 1,
					strlen($row->pid_list_ids) - 2);

			$pid_list_ids = $dao->db
					->get_results(
							'SELECT pid,paycostid,amount,tax,sum_amount FROM finance_receiveinvoice_pid_list WHERE id IN('
									. str_replace('^', ',', $pid_list_ids)
									. ')');
			if ($pid_list_ids !== NULL) {
				foreach ($pid_list_ids as $key => $pid_list_id) {
					$exist_data[$pid_list_id->pid . '_'
							. $pid_list_id->paycostid] = array(
							'amount' => $pid_list_id->amount,
							'tax' => $pid_list_id->tax,
							'sum_amount' => $pid_list_id->sum_amount);
				}
			}
		}
	}

	$rows = array();
	$rows['total'] = count($cost_array);

	$payments = getGDAmountByPaycostid();

	$pidcusname = _getPidCusname();

	$pr = _getPaymentAndReceiveInvoice();

	foreach ($cost_array as $ca) {
		$itempid = $costids[$ca->id];
		$itemcostid = $ca->id;
		$key = $itempid . '_' . $itemcostid;
		$rows['rows'][] = array('x' => $key, 'a' => $itempid,
				'b' => $pidcusname[$itempid], 'c' => $ca->payname,
				'd' => $ca->payamount,
				'e' => $ca->payamount
						- (empty($payments[$itemcostid]) ? 0
								: $payments[$itemcostid]),
				'f' => empty($payments[$itemcostid]) ? 0
						: $payments[$itemcostid], 'g' => '0', 'h' => '',
				'i' => '0',
				'j' => '<input type="text" name="amount_' . $key
						. '" id="amount_' . $key
						. '" style="height:20px;" readonly value="'
						. ($exist_data[$key] !== NULL ? $exist_data[$key]['amount']
								: '') . '">',
				'k' => '<input type="text" name="tax_' . $key . '" id="tax_'
						. $key . '" style="height:20px;" readonly value="'
						. ($exist_data[$key] !== NULL ? $exist_data[$key]['tax']
								: '') . '">',
				'l' => '<input type="text" name="sumamount_' . $key
						. '" id="sumamount_' . $key
						. '" style="height:20px;" onblur="javascript:countTax(this,'
						. ($isVirtual ? 1 : 0) . ');" value="'
						. ($exist_data[$key] !== NULL ? $exist_data[$key]['sum_amount']
								: '') . '">', 'm' => '', 'n' => '', 'o' => '',
				'p' => '',
				'q' => $isVirtual ? '<input type="text" name="taxrate_' . $key
								. '" id="taxrate_' . $key
								. '" style="height:20px;" onblur="javascript:countTax(this,'
								. ($isVirtual ? 1 : 0) . ');">' : '',
				'r' => '',
				's' => $pr['pay_amount'] - $pr['receive_invoice_amount']);

	}

	return json_encode($rows);
}

function getReceiveAmount($pid = NULL) {
	$dao = new Dao_Impl();
	$resuts = $dao->db
			->get_results(
					'SELECT SUM(amount) AS receive_amount,pid FROM finance_receivables WHERE 1=1 '
							. ($pid !== NULL ? ' AND pid="' . $pid . '"' : '')
							. ' AND  isok<>-1'
							. ($pid === NULL ? ' GROUP BY pid' : ''));
	$datas = array();
	if ($resuts !== NULL) {
		foreach ($resuts as $result) {
			$datas[$result->pid] = $result->receive_amount;
		}
	}
	return $datas;
}

function getInvoice($pid = NULL) {
	$dao = new Dao_Impl();
	$resuts = $dao->db
			->get_results(
					'SELECT SUM(amount) AS invoice_amount,pid FROM finance_invoice WHERE 1=1 '
							. ($pid !== NULL ? ' AND pid="' . $pid . '"' : '')
							. ' AND  isok<>-1'
							. ($pid === NULL ? ' GROUP BY pid' : ''));
	$datas = array();
	if ($resuts !== NULL) {
		foreach ($resuts as $result) {
			$datas[$result->pid] = $result->invoice_amount;
		}
	}
	return $datas;
}

function getGDAmountByPaycostid() {
	$dao = new Dao_Impl();
	$results = $dao->db
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

function getDoneApply() {
	$dao = new Dao_Impl();
	$results = $dao->db
			->get_results(
					'SELECT SUM(amount) AS amount,pid,paycostid
FROM
(
SELECT SUM(payment_amount) AS amount,pid,paycostid FROM finance_payment_person_apply_list WHERE isok<>-1 GROUP BY pid,paycostid
UNION ALL
SELECT SUM(payment_amount) AS amount,pid,paycostid FROM finance_payment_media_apply_list WHERE isok<>-1 GROUP BY pid,paycostid
) a
GROUP BY pid,paycostid');
	$datas = array();
	if ($results !== NULL) {
		foreach ($results as $result) {
			$datas[$result->pid][$result->paycostid] = $result->amount;
		}
	}
	return $datas;
}

function getRealInvoice() {
	$dao = new Dao_Impl();
	$results = $dao->db
			->get_results(
					'SELECT pid,paycostid,sum_amount FROM finance_receiveinvoice_pid_list WHERE isok=1');
	$pid_vi = array();
	if ($results !== NULL) {
		foreach ($results as $result) {
			$pid_vi[$result->pid . '_' . $result->paycostid] += $result
					->sum_amount;
		}
	}
	return $pid_vi;
}

function getVirtualInvoice() {
	$dao = new Dao_Impl();
	$results = $dao->db
			->get_results(
					'SELECT pid,paycostid,sum_amount FROM finance_receiveinvoice_virtual_invoice_pid_list WHERE isok=1');
	$pid_vi = array();
	if ($results !== NULL) {
		foreach ($results as $result) {
			$pid_vi[$result->pid . '_' . $result->paycostid] += $result
					->sum_amount;
		}
	}
	return $pid_vi;
}

function getRebate($type,$isContractAmount=TRUE) {
	$dao = new Dao_Impl();
	$pid_rebate = array();
	
	if($isContractAmount){
		//合同款
		$results = $dao->db
				->get_results(
						'SELECT a.id,a.rebate_amount,b.pid,b.paycostid
	FROM finance_payment_rebate a
	LEFT JOIN finance_payment_person_apply_list b
	ON a.apply_id=b.apply_id AND a.list_id=b.id
	WHERE a.payment_type=1 AND a.amount_type=1 AND a.status=1 AND b.isok=1
	UNION ALL
	SELECT a.id,a.rebate_amount,b.pid,b.paycostid
	FROM finance_payment_rebate a
	LEFT JOIN finance_payment_media_apply_list b
	ON a.apply_id=b.apply_id AND a.list_id=b.id
	WHERE a.payment_type=2 AND a.amount_type=1 AND a.status=1 AND b.isok=1');
		
		if ($results !== NULL) {
			foreach ($results as $result) {
				$key = $result->pid . '_' . $result->paycostid;
				$pid_rebate[$result->id] = $key;
				//$pid_rebate[$result->pid][$result->paycostid] += $result->rebate_amount;
			}
		}
	}else{
		//保证金
		$results = $dao->db->get_results('SELECT a.id,a.rebate_amount,b.cid,b.media_name,b.media_category
	FROM finance_payment_rebate a
	LEFT JOIN finance_payment_person_deposit_apply_list b
	ON a.apply_id=b.apply_id AND a.list_id=b.id
	WHERE a.payment_type=1 AND a.amount_type=2 AND a.status=1 AND b.isok=1
	UNION ALL
	SELECT a.id,a.rebate_amount,b.cid,\'\' AS media_name,\'\' AS media_category
	FROM finance_payment_rebate a
	LEFT JOIN finance_payment_media_deposit_apply_list b
	ON a.apply_id=b.apply_id AND a.list_id=b.id
	WHERE a.payment_type=2 AND a.amount_type=2 AND a.status=1 AND b.isok=1');
		
		if ($results !== NULL) {
			foreach ($results as $result) {
				$key = $result->cid . '-_-!' . $result->media_name . '-_-!' . $result->media_category;
				$pid_rebate[$result->id] = $key;	
			}
		}
	}

	//reabte
	$rebates = array();
	$results = $dao->db
			->get_results(
					'SELECT rebate_id,rebate_amount FROM finance_payment_rebate_status WHERE status='
							. intval($type));
	if ($results !== NULL) {
		foreach ($results as $result) {
			$rebates[$pid_rebate[$result->rebate_id]] += $result->rebate_amount;
		}
	}
	return $rebates;
}

function get_payment_person_apply_pidinfo_search() {
	$pids = explode(',', Security_Util::my_post('pids'));
	$action = Security_Util::my_post('type');
	$apply_id = Security_Util::my_post('apply_id');

	$dao = new Dao_Impl();
	$results = array();
	$costids = array();
	foreach ($pids as $pid) {
		if ($pid !== '') {
			$pid = explode('_', $pid);
			$costids[$pid[1]] = $pid[0];
		}
	}

	$keys = array_keys($costids);
	$values = array_unique(array_values($costids));
	$cost_array = array();
	$pid_array = array();
	if (!empty($keys)) {
		$cost_array = $dao->db
				->get_results(
						'SELECT id,payname,payamount FROM executive_paycost WHERE id IN ('
								. implode(',', $keys) . ')');
	}

	if (!empty($values)) {
		foreach ($values as $val) {
			$pid_array[] = 'SELECT b.pid,b.allcost,b.amount,b.name,b.costpaymentinfoids,c.cusname,x.payment FROM 
(SELECT MAX(isalter) AS isalter,pid FROM executive WHERE pid="' . $val
					. '" GROUP BY pid) z 
LEFT JOIN executive b ON (z.isalter=b.isalter AND z.pid=b.pid) 
LEFT JOIN contract_cus c ON b.cid=c.cid 
LEFT JOIN (SELECT SUM(gd_amount) AS payment,pid FROM finance_payment_gd GROUP BY pid) x ON z.pid=x.pid WHERE b.isok<>-1 AND b.allcost >0';
		}
		$pid_array = $dao->db->get_results(implode(' UNION ALL ', $pid_array));
		foreach ($pid_array as $pa) {
			$results[$pa->pid] = array('allcost' => $pa->allcost,
					'amount' => $pa->amount, 'name' => $pa->name,
					'costpaymentinfoids' => $pa->costpaymentinfoids,
					'cusname' => $pa->cusname, 'payment' => $pa->payment);
		}
	}

	$ea_array = array();
	if (!empty($apply_id)) {
		$exit_array = $dao->db
				->get_results(
						'SELECT pid,paycostid,payment_amount,payment_type,rebate_deduction_amount,rebate_deduction_dids,person_loan_user,person_loan_amount,is_nim_pay_first,nim_pay_first_amount,nim_pay_first_dids FROM '
								. ($action === 'edit_payment_apply' ? 'finance_payment_person_apply_list'
										: 'finance_payment_person_apply_list_temp')
								. ' WHERE apply_id=' . intval($apply_id));
		if ($exit_array !== NULL) {
			foreach ($exit_array as $ea) {
				$ea_array[$ea->pid][$ea->paycostid] = array(
						'payment_amount' => $ea->payment_amount,
						'payment_type' => $ea->payment_type,
						'rebate_deduction_amount' => $ea
								->rebate_deduction_amount,
						'rebate_deduction_dids' => $ea->rebate_deduction_dids,
						'person_loan_user' => $ea->person_loan_user,
						'person_loan_amount' => $ea->person_loan_amount,
						'is_nim_pay_first' => $ea->is_nim_pay_first,
						'nim_pay_first_amount' => $ea->nim_pay_first_amount,
						'nim_pay_first_dids' => $ea->nim_pay_first_dids);
			}
		}
	}

	$rows = array();
	$rows['total'] = count($cost_array);

	//已收款
	$receive_amount = getReceiveAmount();

	//已开票
	$invoice = getInvoice();

	//归档金额
	$gd_amount = getGDAmountByPaycostid();

	//待开票返点
	$r1 = getRebate(1);

	//已开票返点
	$r2 = getRebate(2);

	//待分配返点
	$r3 = getRebate(3);

	//已分配返点
	$r4 = getRebate(4);

	//虚拟发票
	$vi = getVirtualInvoice();

	//真实发票
	$ri = getRealInvoice();

	//已申请金额
	$done_apply = getDoneApply();

	foreach ($cost_array as $ca) {
		//pid
		$pid_v = $costids[$ca->id];

		//paycostid
		$paycostid_v = $ca->id;

		//key
		$key = $pid_v . '_' . $paycostid_v;

		$amount = $results[$pid_v]['amount'];
		$receive = empty($receive_amount[$pid_v]) ? 0 : $receive_amount[$pid_v];
		$paid_amount = empty($gd_amount[$paycostid_v]) ? 0
				: $gd_amount[$paycostid_v];

		//已执行未付成本
		$done_notpayment_amount = $ca->payamount - $paid_amount;

		//已申请支付成本
		$done_apply_amount = empty($done_apply[$pid_v][$paycostid_v]) ? 0
				: $done_apply[$pid_v][$paycostid_v];

		//剩余可申请
		$can_apply = $ca->payamount - $done_apply_amount;

		//实际支付
		$amount_real = !empty($apply_id) ? ($ea_array[$pid_v][$paycostid_v]['payment_amount']
						- $ea_array[$pid_v][$paycostid_v]['rebate_deduction_amount']
						- $ea_array[$pid_v][$paycostid_v]['person_loan_amount'])
				: 0;

		$rows['rows'][] = array('a' => $pid_v,
				'b' => $results[$pid_v]['cusname'],
				'c' => $results[$pid_v]['name'], 'd' => $amount,
				'e' => $receive,
				'f' => empty($invoice[$pid_v]) ? 0 : $invoice[$pid_v],
				'g' => '<span id="u_' . $key . '">' . ($amount - $receive)
						. '</span>', 'h' => $ca->payname,
				'i' => $ca->payamount, 'j' => $done_notpayment_amount,
				'k' => '<input type="radio" checked name="payment_type_' . $key
						. '" value="1" '
						. ($ea_array[$pid_v][$paycostid_v]['payment_type']
								!== NULL
								&& intval(
										$ea_array[$pid_v][$paycostid_v]['payment_type'])
										=== 1 ? 'checked' : '')
						. '>&nbsp;全付&nbsp;<input type="radio" name="payment_type_'
						. $key . '" value="2" '
						. ($ea_array[$pid_v][$paycostid_v]['payment_type']
								!== NULL
								&& intval(
										$ea_array[$pid_v][$paycostid_v]['payment_type'])
										=== 2 ? 'checked' : '')
						. '>&nbsp;支付部分&nbsp;<input type="text" style="height:20px;" class="validate[optional,'
						. ($can_apply >= 0 ? 'max[' . $can_apply . '],min[0]'
								: 'max[0],min[' . $can_apply . ']')
						. ']" id="payment_amount_' . $key
						. '" name="payment_amount_' . $key . '" value="'
						. (empty($apply_id) ? $can_apply
								: $ea_array[$pid_v][$paycostid_v]['payment_amount'])
						. '" onblur="javascript:countItemAmount(this);countPaymentPlan();countRebateByRate();">',
				'l' => $paid_amount,
				'm' => '<input type="text" style="height:20px;" class="validate[optional,custom[money]]" id="rebate_deduction_amount_'
						. $key . '" name="rebate_deduction_amount_' . $key
						. '" value="'
						. (empty($apply_id) ? 0
								: $ea_array[$pid_v][$paycostid_v]['rebate_deduction_amount'])
						. '" onblur="javascript:countItemAmount(this);countRebate();">&nbsp;<input type="file" name="rebate_upfile_'
						. $key . '" id="rebate_upfile_' . $key
						. '" size="10" style="width:200px;">&nbsp;<input type="button" value="提交" class="btn" onclick="up_uploadfile(this,\'rebate_deduction_dids_'
						. $key . '\',0,0);">'
						. get_upload_files(
								$ea_array[$pid_v][$paycostid_v]['rebate_deduction_dids'],
								TRUE, 'rebate_deduction_dids_' . $key)
						. '<input type="hidden" name="rebate_deduction_dids_'
						. $key . '" id="rebate_deduction_dids_' . $key
						. '" size="50" value="'
						. $ea_array[$pid_v][$paycostid_v]['rebate_deduction_dids']
						. '"/>', 'n' => empty($r1[$key]) ? 0 : $r1[$key],
				'o' => empty($r2[$key]) ? 0 : $r2[$key],
				'p' => (empty($r3[$key]) ? 0 : $r3[$key])
						+ (empty($r4[$key]) ? 0 : $r4[$key]),
				'q' => empty($vi[$key]) ? 0 : $vi[$key],
				'r' => empty($ri[$key]) ? 0 : $ri[$key],
				's' => $paid_amount - (empty($ri[$key]) ? 0 : $ri[$key]),
				't' => '还款人&nbsp;<input type="text" style="height:20px;" name="person_loan_user_'
						. $key . '" value="'
						. (empty($apply_id) ? ''
								: $ea_array[$pid_v][$paycostid_v]['person_loan_user'])
						. '">&nbsp;&nbsp;金额&nbsp;<input type="text" style="height:20px;" class="validate[optional,custom[money]]" id="person_loan_amount_'
						. $key . '" name="person_loan_amount_' . $key
						. '" value="'
						. (empty($apply_id) ? 0
								: $ea_array[$pid_v][$paycostid_v]['person_loan_amount'])
						. '" onblur="javascript:countItemAmount(this);countPersonLoan();">',
				'u' => (empty($apply_id) ? $done_notpayment_amount
						: $amount_real),
				'v' => '<input type="checkbox" id="is_nim_pay_first_' . $key
						. '" name="is_nim_pay_first_' . $key . '" value="1" '
						. ($ea_array[$pid_v][$paycostid_v]['is_nim_pay_first']
								!== NULL
								&& intval(
										$ea_array[$pid_v][$paycostid_v]['is_nim_pay_first'])
										=== 1 ? 'checked' : '')
						. ' onclick="javascript:doNimPayFirst(this);">&nbsp;是&nbsp;<input type="text" style="height:20px;" class="validate[optional,max['
						. $ca->payamount . ']]" id="nim_pay_first_amount_'
						. $key . '" name="nim_pay_first_amount_' . $key
						. '" value="'
						. (empty($apply_id) ? 0
								: $ea_array[$pid_v][$paycostid_v]['nim_pay_first_amount'])
						. '">&nbsp;<input type="file" size="10" name="nim_pay_first_upfile_'
						. $key . '" id="nim_pay_first_upfile_' . $key
						. '" style="width:200px;">&nbsp;<input type="button" value="提交" class="btn" onclick="up_uploadfile(this,\'nim_pay_first_dids_'
						. $key . '\',0,0);" class="btn">'
						. get_upload_files(
								$ea_array[$pid_v][$paycostid_v]['nim_pay_first_dids'],
								TRUE, 'nim_pay_first_dids_' . $key)
						. '<input type="hidden" name="nim_pay_first_dids_'
						. $key . '" id="nim_pay_first_dids_' . $key
						. '" size="50" value="'
						. $ea_array[$pid_v][$paycostid_v]['nim_pay_first_dids']
						. '"/>', 'x' => $key);

	}
	return json_encode($rows);

}

function get_payment_deposit_apply_cidinfo() {
	$id = Security_Util::my_post('id');
	$dao = new Dao_Impl();

	$results = $dao->db
			->get_results(
					'SELECT a.*,b.cusname
FROM 
(
SELECT * FROM finance_payment_person_deposit_apply_list WHERE apply_id='
							. intval($id)
							. ' AND isok<>-1) a
LEFT JOIN contract_cus b
ON a.cid=b.cid');

	$datas = $dao->db
			->get_results(
					'SELECT a.receivablesamount,a.cid,b.paymentamount,b.gd_amount from (
		SELECT SUM(amount) AS receivablesamount ,cid from finance_deposit_receivables where isok=1 GROUP BY cid
		) a 
		LEFT JOIN (	
		SELECT SUM(gd_amount) AS paymentamount,cid,gd_amount FROM (SELECT gd_amount,cid FROM finance_payment_deposit_gd WHERE isok=1 ORDER BY gd_time DESC ) c GROUP BY cid
		) b ON a.cid=b.cid');
	$cid_infos = array();
	if ($datas !== NULL) {
		foreach ($datas as $data) {
			$cid_infos[$data->cid] = array(
					'receivablesamount' => $data->receivablesamount,
					'paymentamount' => $data->paymentamount,
					'last_gd_amount' => $data->gd_amount);
		}
	}

	$rows = array();
	$rows['total'] = count($results);
	foreach ($results as $result) {
		$key = $result->cid . '_' . $result->apply_id . '_' . $result->id;
		$rows['rows'][] = array('a' => $result->cid, 'b' => $result->cusname,
				'c' => !empty($cid_infos[$result->cid]['receivablesamount']) ? $cid_infos[$result
								->cid]['receivablesamount'] : 0,
				'd' => !empty($cid_infos[$result->cid]['last_gd_amount']) ? $cid_infos[$result
								->cid]['last_gd_amount'] : 0,
				'e' => !empty($cid_infos[$result->cid]['paymentamount']) ? $cid_infos[$result
								->cid]['paymentamount'] : 0,
				'f' => $result->payment_amount,
				'g' => $result->rebate_deduction_amount . '&nbsp;'
						. get_upload_files($result->rebate_deduction_dids,
								FALSE, ''), 'h' => 'h', 'i' => 'i', 'j' => 'j',
				'k' => '还款人&nbsp;' . $result->person_loan_user . '&nbsp;金额'
						. $result->person_loan_amount,
				//'l'=>'',
				'm' => Payment_Person_Apply::isYesOrNo(
						$result->is_nim_pay_first) . '&nbsp;'
						. $result->nim_pay_first_amount . '&nbsp;'
						. get_upload_files($result->nim_pay_first_dids, FALSE,
								''),
				'w' => intval($result->isok) === 1 ? '通过'
						: intval($result->isok) === 2 ? '驳回'
								: '<span id="auditres_' . $result->id
										. '"><input type="radio" name="auditsel_'
										. $key
										. '" value="1" checked>&nbsp;通过&nbsp;&nbsp;<input type="radio" name="auditsel_'
										. $key
										. '" value="2">&nbsp;驳回&nbsp;&nbsp;驳回原因&nbsp;<input type="text" style="height:20px;" id="auditresaon_'
										. $key . '" name="auditresaon_' . $key
										. '">&nbsp;<input type="button" class="btn" value="提交" onclick="javascript:auditem(\''
										. $result->id . '\',\'' . $key
										. '\')"></span>', 'y' => $key,
				'z' => '<input type="text" name="gdamount_' . $key . '">');
	}
	return json_encode($rows);
}

function get_payment_person_apply_pidinfo() {
	$id = Security_Util::my_post('id');
	$isfinance = Security_Util::my_post('isfinance');
	$type = Security_Util::my_post('type');//type=audit 可以修改是否垫付信息
	$dao = new Dao_Impl();

	//已收款
	$receive_amount = getReceiveAmount();

	//已开票
	$invoice = getInvoice();
	//??

	//归档金额
	$gd_amount = getGDAmountByPaycostid();

	/**
	 * SELECT a.*,b.allcost,b.name FROM (SELECT pid,paycostid,payment_amount,payment_type,rebate_deduction_amount,rebate_deduction_dids,person_loan_user,person_loan_amount,is_nim_pay_first,nim_pay_first_amount,nim_pay_first_dids FROM finance_payment_person_apply_list WHERE apply_id=10) a LEFT JOIN (SELECT n.pid,n.allcost,n.name FROM (SELECT MAX(isalter) AS isalter,pid FROM executive GROUP BY pid) m INNER JOIN executive n ON m.pid=n.pid AND m.isalter=n.isalter) b ON a.pid=b.pid
	 */
	$results = $dao->db
			->get_results(
					'SELECT a.*,b.allcost,b.amount,b.name,c.cusname,d.payname,d.payamount FROM (SELECT id,pid,paycostid,payment_amount,payment_type,rebate_deduction_amount,rebate_deduction_dids,person_loan_user,person_loan_amount,is_nim_pay_first,nim_pay_first_amount,nim_pay_first_dids,isok FROM finance_payment_person_apply_list WHERE apply_id='
							. intval($id)
							. ' AND isok<>-1) a LEFT JOIN (SELECT n.pid,n.allcost,n.amount,n.name,n.cid FROM (SELECT MAX(isalter) AS isalter,pid FROM executive GROUP BY pid) m INNER JOIN executive n ON m.pid=n.pid AND m.isalter=n.isalter) b ON a.pid=b.pid LEFT JOIN contract_cus c ON b.cid=c.cid INNER JOIN executive_paycost d ON a.paycostid=d.id');
	$rows = array();
	$rows['total'] = count($results);

	foreach ($results as $result) {
		$receive = empty($receive_amount[$result->pid]) ? 0
				: $receive_amount[$result->pid];
		$paid_amount = empty($gd_amount[$result->paycostid]) ? 0
				: $gd_amount[$result->paycostid];
		$rows['rows'][] = array('a' => $result->pid, 'b' => $result->cusname,
				'c' => $result->name, 'd' => $result->amount, 'e' => $receive,
				'f' => empty($invoice[$result->pid]) ? 0
						: $invoice[$result->pid],
				'g' => '<span id="u_' . $result->pid . '_' . $result->paycostid
						. '">' . ($result->amount - $receive) . '</span>',
				'h' => $result->payname, 'i' => $result->payamount,
				'j' => $result->payamount - $paid_amount,
				'k' => Payment_Person_Apply::getPaymentType(
						$result->payment_type) . '&nbsp;'
						. $result->payment_amount, 'l' => $paid_amount,
				'm' => $result->rebate_deduction_amount . '&nbsp;'
						. get_upload_files($result->rebate_deduction_dids,
								FALSE, ''), 'n' => 'N', 'o' => 'O', 'p' => 'P',
				'q' => 'Q', 'r' => 'R', 's' => 'S',
				't' => '还款人&nbsp;' . $result->person_loan_user
						. '&nbsp;金额&nbsp;' . $result->person_loan_amount,
				'u' => 'U',
				'v' => ($type === 'audit' ? '<input type="checkbox" name="is_nim_pay_first_'
								. $result->pid . '_' . $result->paycostid
								. '" id="is_nim_pay_first_' . $result->pid
								. '_' . $result->paycostid
								. '" value="1" onclick="javascript:doNimPayFirst(this);"'
								. (intval($result->is_nim_pay_first) === 1 ? 'checked'
										: '')
								. '>&nbsp;是&nbsp;<input type="text" style="height:20px;width:60px;" name="nim_pay_first_amount_'
								. $result->pid . '_' . $result->paycostid
								. '" id="nim_pay_first_amount_' . $result->pid
								. '_' . $result->paycostid . '" value="'
								. $result->nim_pay_first_amount . '" />&nbsp;<input type="button" class="btn" style="cursor:pointer" value="提交" onclick="setNimPayfirst(\'' . $result->id . '\',\'' . $result->pid . '_' . $result->paycostid . '\')"/>'
						: Payment_Person_Apply::isYesOrNo(
								$result->is_nim_pay_first) . '&nbsp;'
								. $result->nim_pay_first_amount) . '&nbsp;'
						. get_upload_files($result->nim_pay_first_dids, FALSE,
								''),
				'w' => intval($result->isok) === 1 ? '通过'
						: (intval($result->isok) === 2 ? '驳回'
								: '<span id="auditres_' . $result->id
										. '"><input type="radio" name="auditsel_'
										. $result->pid . '_'
										. $result->paycostid
										. '" value="1" checked>&nbsp;通过&nbsp;&nbsp;<input type="radio" name="auditsel_'
										. $result->pid . '_'
										. $result->paycostid
										. '" value="2">&nbsp;驳回&nbsp;&nbsp;驳回原因&nbsp;<input type="text" id="auditresaon_'
										. $result->pid . '_'
										. $result->paycostid
										. '" name="auditresaon_' . $result->pid
										. '_' . $result->paycostid
										. '">&nbsp;<input type="button" class="btn" style="cursor:pointer" value="提交" onclick="javascript:auditem(\''
										. $result->id . '\',\'' . $result->pid
										. '_' . $result->paycostid
										. '\')"></span>'),
				'y' => $result->id . '_' . $result->pid . '_'
						. $result->paycostid,
				'z' => '<input type="text" style="height:20px;" name="gdamount_'
						. $result->pid . '_' . $result->paycostid . '" value="'
						. ($result->payment_amount - $paid_amount)
						. '" class="validate[optional,min[0.01],max['
						. ($result->payment_amount - $paid_amount) . ']]">');
	}
	return json_encode($rows);
}

//已收客户保证金
function _getReceiveDeposit($cid = NULL) {
	$dao = new Dao_Impl();
	$datas = array();
	$results = $dao->db
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
function _getPaymentDeposit($cid = NULL) {
	$dao = new Dao_Impl();
	$datas = array();
	$results = $dao->db
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

function get_payment_media_deposit_apply_cidinfo_search() {
	$cids = explode(',', Security_Util::my_post('cids'));
	$dao = new Dao_Impl();
	$cidinfo = array();
	foreach ($cids as $cid) {
		if (!empty($cid)) {
			$cidinfo[] = $cid;
		}
	}
	$rows = array();
	if (!empty($cidinfo)) {
		$results = $dao->db
				->get_results(
						'SELECT cid,cusname FROM contract_cus WHERE cid IN ("'
								. implode('","', $cidinfo) . '") AND isok=1');
		if ($results !== NULL) {

			//已收客户保证金
			$reveive_deposits = _getReceiveDeposit();

			//已付媒体保证金
			$payment_deposits = _getPaymentDeposit();

			foreach ($results as $result) {
				$rows['rows'][] = array('x' => $result->cid,
						'a' => $result->cid,
						'b' => urlencode($result->cusname),
						'c' => empty($reveive_deposits[$result->cid]) ? 0
								: $reveive_deposits[$result->cid],
						'd' => empty($payment_deposits[$result->cid]) ? 0
								: $payment_deposits[$result->cid],
						'e' => '<input type="radio" checked name="payment_type_'
								. $result->cid
								. '" value="1">&nbsp;全付&nbsp;<input type="radio" name="payment_type_'
								. $result->cid
								. '"  value="2">&nbsp;支付部分&nbsp;<input type="text" style="height:20px;" id="payment_amount_'
								. $result->cid . '" name="payment_amount_'
								. $result->cid . '">',
						'f' => '还款人&nbsp;<input type="text" style="height:20px;" name="person_loan_user_'
								. $result->cid
								. '" >&nbsp;金额<input type="text" id="person_loan_amount_'
								. $result->cid . '" name="person_loan_amount_'
								. $result->cid . '" style="height:20px;">',
						'g' => '<input type="checkbox" name="is_nim_pay_first_'
								. $result->cid
								. '" value="1" >&nbsp;是&nbsp;<input type="text"style="height:20px;"  class="" id="nim_pay_first_amount_'
								. $result->cid
								. '" name="nim_pay_first_amount_'
								. $result->cid
								. '" >&nbsp;<input type="file" size="10" name="nim_pay_first_upfile_'
								. $result->cid . '" id="nim_pay_first_upfile_'
								. $result->cid
								. '" style="width:200px;">&nbsp;<input type="button" value="提交" class="btn" onclick="up_uploadfile(this,\'nim_pay_first_dids_'
								. $result->cid
								. '\',0,0);" class="btn"><input type="hidden" name="nim_pay_first_dids_'
								. $result->cid . '" id="nim_pay_first_dids_'
								. $result->cid . '" size="50"/>', 'h' => '');
			}
		}
	}
	$rows['total'] = count($results);

	return urldecode(json_encode($rows));
}

function get_payment_deposit_apply_cidinfo_search() {
	$pids = explode(',', Security_Util::my_post('pids'));
	$action = Security_Util::my_post('type');
	$apply_id = Security_Util::my_post('apply_id');
	
	$dao = new Dao_Impl();

	$cids = array();
	if ($pids !== NULL) {
		foreach ($pids as $pid) {
			if (!empty($pid)) {
				$pid = explode('-_-!', $pid);
				if(!in_array($pid[0], $cids['cid'],TRUE)){
					$cids['cid'][] = $pid[0];
				}
				//$cids['cid'][] = $pid[0];
				$cids['info'][] = array('cid' => $pid[0],
						'media_name' => $pid[1], 'category' => $pid[2]);
			}
		}
	}
	
	//查找cid对应的cusname
	$cus = array();
	if(!empty($cids['cid'])){
		$results = $dao->db->get_results('SELECT cid,cusname FROM contract_cus WHERE cid IN ("' . implode('","', $cids['cid']) . '")');
		if(!empty($results)){
			foreach ($results as $result){
				$cus[$result->cid] = $result->cusname;
			}
		}
	}

	
	$cidinfo = array();
	if (!empty($cids)) {
		$res = $dao->db
				->get_results(
						'SELECT a.cid,a.cusname,a.contractcontent,b.receivablesamount,c.paymentamount,c.gd_amount,c.media_name,c.media_category FROM contract_cus a 
		LEFT JOIN (
		SELECT SUM(amount) AS receivablesamount ,cid from finance_deposit_receivables where isok=1 GROUP BY cid
		) b ON a.cid=b.cid
		LEFT JOIN (	
		SELECT SUM(gd_amount) AS paymentamount,cid,gd_amount,media_name,media_category FROM (SELECT gd_amount,cid,media_name,media_category FROM finance_payment_deposit_gd WHERE isok=1 ORDER BY cid,media_name,media_category,gd_time DESC )  cc GROUP BY cid,media_name,media_category
		) c ON a.cid=c.cid
		WHERE a.isok=1 AND a.cid IN("' . implode('","', $cids['cid']) . '")');

		foreach ($res as $re) {
			$cidinfo[$re->cid][$re->media_name][$re->media_category] = array('cusname' => $re->cusname,
					'contractcontent' => $re->contractcontent,
					'receivablesamount' => $re->receivablesamount,
					'paymentamount' => $re->paymentamount,
					'lastgdamount' => $re->gd_amount);
		}
	}

	$ea_array = array();
	if (!empty($apply_id)) {
		$exit_array = $dao->db
				->get_results(
						'SELECT cid,media_name,media_category,payment_amount,rebate_deduction_amount,rebate_deduction_dids,person_loan_user,person_loan_amount,is_nim_pay_first,nim_pay_first_amount,nim_pay_first_dids FROM finance_payment_person_deposit_apply_list WHERE apply_id='
								. intval($apply_id));
		if ($exit_array !== NULL) {
			foreach ($exit_array as $ea) {
				$ea_array[$ea->cid . '-_-!' . $ea->media_name . '-_-!'
						. $ea->media_category] = array(
						'payment_amount' => $ea->payment_amount,
						'rebate_deduction_amount' => $ea
								->rebate_deduction_amount,
						'rebate_deduction_dids' => $ea->rebate_deduction_dids,
						'person_loan_user' => $ea->person_loan_user,
						'person_loan_amount' => $ea->person_loan_amount,
						'is_nim_pay_first' => $ea->is_nim_pay_first,
						'nim_pay_first_amount' => $ea->nim_pay_first_amount,
						'nim_pay_first_dids' => $ea->nim_pay_first_dids);
			}
		}
	}

	$rows = array();
	$results = $cids['info'];
	$rows['total'] = count($results);
	
	//??
	//待开票返点
	$r1 = getRebate(1,FALSE);

	//已开票返点
	$r2 = getRebate(2,FALSE);

	//待分配返点
	$r3 = getRebate(3,FALSE);

	//已分配返点
	$r4 = getRebate(4,FALSE);
	//??

	foreach ($results as $result) {
		$key = $result['cid'] . '-_-!' . $result['media_name'] . '-_-!'
				. $result['category'];
		$receivablesamount = empty($cidinfo[$result['cid']][$result['media_name']][$result['category']]['receivablesamount']) ? 0
						: $cidinfo[$result['cid']][$result['media_name']][$result['category']]['receivablesamount'];
		$paysum = empty($cidinfo[$result['cid']][$result['media_name']][$result['category']]['paymentamount']) ? 0
						: $cidinfo[$result['cid']][$result['media_name']][$result['category']]['paymentamount'];
						
		//实际支付
		$amount_real = !empty($apply_id) ? ($ea_array[$key]['payment_amount']
						- $ea_array[$key]['rebate_deduction_amount']
						- $ea_array[$key]['person_loan_amount'])
				: 0;
				
		$rows['rows'][] = array('a' => $result['cid'],
				//'b' => $cidinfo[$result['cid']][$result['media_name']][$result['category']]['cusname'],
				'b'=>$cus[$result['cid']],
				'c' => $receivablesamount,
				'd' => empty($cidinfo[$result['cid']][$result['media_name']][$result['category']]['lastgdamount']) ? 0
						: $cidinfo[$result['cid']][$result['media_name']][$result['category']]['lastgdamount'],
				'e' => $paysum,
				'f' => '<input type="text" class="validate[required,custom[money]]" id="payment_amount_'
						. $key . '" name="payment_amount_' . $key . '" value="'
						. (empty($apply_id) ? 0
								: empty($ea_array[$key]['payment_amount']) ? ''
										: $ea_array[$key]['payment_amount'])
						. '" onblur="javascript:countItemAmount(this,\'' . $key
						. '\');countPaymentPlan();" style="height:20px;">',
				'g' => '<input type="text" class="validate[optional,custom[money]]" id="rebate_deduction_amount_'
						. $key . '" name="rebate_deduction_amount_' . $key
						. '" value="'
						. (empty($apply_id) ? 0
								: empty(
										$ea_array[$key]['rebate_deduction_amount']) ? ''
										: $ea_array[$key]['rebate_deduction_amount'])
						. '" onblur="javascript:countItemAmount(this,\'' . $key
						. '\');countRebate();" style="height:20px;">&nbsp;<input type="file" name="rebate_upfile_'
						. $key . '" id="rebate_upfile_' . $key
						. '" size="10" style="width:200px;">&nbsp;<input type="button" value="提交" class="btn" onclick="up_uploadfile(this,\'rebate_deduction_dids_'
						. $key . '\',0,0);">'
						. get_upload_files(
								$ea_array[$key]['rebate_deduction_dids'], TRUE,
								'rebate_deduction_dids_' . $key)
						. '<input type="hidden" name="rebate_deduction_dids_'
						. $key . '" id="rebate_deduction_dids_' . $key
						. '" size="50" value="'
						. $ea_array[$key]['rebate_deduction_dids'] . '"/>',
				'h' => empty($r1[$key]) ? 0 : $r1[$key], 'i' => empty($r2[$key]) ? 0 : $r2[$key], 'j' => (empty($r3[$key]) ? 0 : $r3[$key])
						+ (empty($r4[$key]) ? 0 : $r4[$key]),
				'k' => '还款人&nbsp;<input type="text" style="height:20px;" name="person_loan_user_'
						. $key . '" value="'
						. (empty($ea_array[$key]['person_loan_user']) ? ''
								: $ea_array[$key]['person_loan_user'])
						. '">&nbsp;金额<input type="text" class="validate[optional,custom[money]]" id="person_loan_amount_'
						. $key . '" name="person_loan_amount_' . $key
						. '" value="'
						. (empty($apply_id) ? 0
								: empty($ea_array[$key]['person_loan_amount']) ? ''
										: $ea_array[$key]['person_loan_amount'])
						. '" onblur="javascript:countItemAmount(this,\'' . $key
						. '\');countPersonLoan();" style="height:20px;">',
				'l' => '<span id="l_' . $key . '">' . $amount_real . '</span>',
				'm' => '<input type="checkbox" name="is_nim_pay_first_' . $key
						. '" id="is_nim_pay_first_' . $key . '" value="1" '
						. ($ea_array[$key]['is_nim_pay_first'] !== NULL
								&& intval($ea_array[$key]['is_nim_pay_first'])
										=== 1 ? 'checked' : '')
						. ' onclick="javascript:doNimPayFirst(this,' . ($receivablesamount - $paysum) . ');">&nbsp;是&nbsp;<input type="text"style="height:20px;"  class="" id="nim_pay_first_amount_'
						. $key . '" name="nim_pay_first_amount_' . $key
						. '" value="'
						. (empty($ea_array[$key]['nim_pay_first_amount']) ? ''
								: $ea_array[$key]['nim_pay_first_amount'])
						. '">&nbsp;<input type="file" size="10" name="nim_pay_first_upfile_'
						. $key . '" id="nim_pay_first_upfile_' . $key
						. '" style="width:200px;">&nbsp;<input type="button" value="提交" class="btn" onclick="up_uploadfile(this,\'nim_pay_first_dids_'
						. $key . '\',0,0);" class="btn">'
						. get_upload_files(
								$ea_array[$key]['nim_pay_first_dids'], TRUE,
								'nim_pay_first_dids_' . $key)
						. '<input type="hidden" name="nim_pay_first_dids_'
						. $key . '" id="nim_pay_first_dids_' . $key
						. '" size="50" value="'
						. $ea_array[$key]['nim_pay_first_dids'] . '"/>',
				'x' => $key, 'aa' => $result['media_name']);
	}
	return json_encode($rows);
}

function get_reabte_invoice_pidinfo() {
	$rows = array();
	$invoice_amount = array();
	$dao = new Dao_Impl();
	$rebate_invoice_id = Security_Util::my_post('rebate_invoice_id');
	if (!empty($rebate_invoice_id)) {
		$results = $dao->db
				->get_results(
						'SELECT * FROM finance_rebate_invoice_pid WHERE rebate_invoice_id='
								. intval($rebate_invoice_id) . ' AND isok=1');
		if ($results !== NULL) {
			foreach ($results as $result) {
				$invoice_amount[$result->pid . '_' . $result->paycostid] = $result
						->amount;
			}
		}
	}

	$pids = explode(',', Security_Util::my_post('pids'));
	$payment = getGDAmountByPaycostid();
	foreach ($pids as $pid) {
		if (!empty($pid)) {
			$pid = explode('_', $pid);
			$key = $pid[0] . '_' . $pid[1];

			$exe = $dao->db
					->get_row(
							'SELECT a.name,b.cusname FROM v_last_executive a LEFT JOIN contract_cus b ON a.cid=b.cid WHERE a.pid="'
									. $pid[0] . '"');
			$pc = $dao->db
					->get_row(
							'SELECT payname,payamount FROM executive_paycost WHERE id='
									. intval($pid[1]));
			$rows['rows'][] = array('x' => $pid[0] . '_' . $pid[1],
					'a' => $pid[0], 'b' => $exe->cusname, 'c' => $exe->name,
					'd' => !empty($rebate_invoice_id) ? $pc->payname : $pid[3],
					'e' => !empty($pc->payamount) ? $pc->payamount : 0,
					'f' => empty($payment[$paycostid]) ? 0
							: $payment[$paycostid],
					'g' => _getReceiveInvoice($pid[1]),
					'h' => _getRebateInvoiceGD($pid[1]),
					'i' => !empty($rebate_invoice_id) ? '<input type="text" value="'
									. ($invoice_amount[$key] !== NULL ? $invoice_amount[$key]
											: 0) . '" name="amount_' . $key
									. '" id="amount_' . $key
									. '" class="validate[required,custom[invoiceMoney]]" style="height:20px;" onblur="javascript:countInvoiceAmount();">'
							: $pid[2],);
		}
	}
	$rows['total'] = count($pids);
	return json_encode($rows);
}

function _getRebateInvoiceGD($paycostid) {
	$dao = new Dao_Impl();
	$done = $dao->db
			->get_var(
					'SELECT SUM(amount) AS amout FROM finance_rebate_invoice_gd
WHERE rebate_invoice_id IN(SELECT rebate_invoice_id FROM finance_rebate_invoice_pid WHERE paycostid='
							. $paycostid . ' AND isok=1) AND gdtype=1');
	return empty($done) ? 0 : $done;
}

function _getReceiveInvoice($paycostid) {
	$dao = new Dao_Impl();
	$done = $dao->db
			->get_var(
					'SELECT SUM(sum_amount) AS amount FROM finance_receiveinvoice_pid_list WHERE paycostid='
							. $paycostid . ' AND isok=1');
	return empty($done) ? 0 : $done;
}

function get_reabte_invoice_pidinfo_search() {
	$rows = array();
	$selectitem = Security_Util::my_post('selectitem');
	$dao = new Dao_Impl();
	$selectitem = explode(',', $selectitem);
	$pids = array();
	foreach ($selectitem as $select) {
		if (!empty($select)) {
			$select = explode('^', $select);
			if ($select[1] === '1') {
				//执行单号
				if (!in_array($select[0], $pids, TRUE)) {
					$pids[] = $select[0];
				}
			} else if ($select[1] === '2') {
				//合同号
				$results = $dao->db
						->get_results(
								'SELECT pid FROM v_last_executive WHERE cid="'
										. $select[0] . '"');
				if ($results !== NULL) {
					foreach ($results as $result) {
						if (!in_array($result->pid, $pids, TRUE)) {
							$pids[] = $result->pid;
						}
					}
				}
			} else if ($select[1] === '3') {
				//付款申请ID
				$tmp = explode('_', $select[0]);
				$results = $dao->db
						->get_results(
								'SELECT pid FROM '
										. ($tmp[1] === 'p' ? 'finance_payment_person_apply_list'
												: 'finance_payment_media_apply_list')
										. ' WHERE apply_id="' . $tmp[0]
										. '" AND isok=1');
				if ($results !== NULL) {
					foreach ($results as $result) {
						if (!in_array($result->pid, $pids, TRUE)) {
							$pids[] = $result->pid;
						}
					}
				}
			} else if ($select[1] === '4') {
				//执行单号
				if (!in_array($select[0], $pids, TRUE)) {
					$pids[] = $select[0];
				}
			}
		}
	}

	if (!empty($pids)) {
		$results = $dao->db
				->get_results(
						'SELECT a.pid,a.costpaymentinfoids,a.name,b.cusname FROM v_last_executive a LEFT JOIN contract_cus b ON a.cid=b.cid WHERE a.pid IN ("'
								. implode('","', $pids) . '")');
		$total = 0;
		$pid = array();
		if ($results !== NULL) {

			$payment = getGDAmountByPaycostid();

			foreach ($results as $result) {
				if (!empty($result->costpaymentinfoids)) {
					$costpaymentinfoids = explode('^',
							$result->costpaymentinfoids);
					$costpaymentinfoids = Array_Util::my_remove_array_other_value(
							$costpaymentinfoids, array('', NULL));
					if (!empty($costpaymentinfoids)) {
						foreach ($costpaymentinfoids as $paycostid) {
							$key = $result->pid . '_' . $paycostid;
							$pid[] = $key;
							$pc = $dao->db
									->get_row(
											'SELECT payname,payamount FROM executive_paycost WHERE id='
													. intval($paycostid));
							$rows['rows'][] = array('x' => $key,
									'a' => $result->pid,
									'b' => $result->cusname,
									'c' => $result->name, 'd' => $pc->payname,
									'e' => $pc->payamount,
									'f' => empty($payment[$paycostid]) ? 0
											: $payment[$paycostid],
									'g' => _getReceiveInvoice($paycostid),
									'h' => _getRebateInvoiceGD($paycostid),
									'i' => '<input type="text" name="amount_'
											. $key . '" id="amount_' . $key
											. '" class="validate[required,custom[invoiceMoney]]" style="height:20px;" onblur="javascript:countInvoiceAmount();">',);
							$total++;
						}
						$rows['pids'] = implode(',', $pid);
					}
				}
			}
		} else {
			$rows['rows'][] = array();
		}
		$rows['total'] = $total;
	}

	return json_encode($rows);
}

function selectPaymentList(){
	$rows = array();
	$applys = Security_Util::my_post('applys');
	$applys = explode(',', $applys);
	$apply_ids = array();
	foreach ($applys as $apply){
		if(!empty($apply)){
			$apply = explode('_', $apply);
			$apply_ids["$apply[1]"][] = $apply[0];
		}
	}

	$sql = array();
	if(!empty($apply_ids['pc'])){
		$sql[] = 'SELECT a.id AS gid,a.cid,a.payment_id,a.gd_amount,b.*,c.cusname,d.name,d.amount,e.payamount,f.payname,f.payamount AS mediacost,\'pc\' AS ptype
FROM
(
SELECT id,apply_id,payment_id,pid,paycostid,gd_amount,SUBSTRING_INDEX(pid,"-",1) AS cid
FROM finance_payment_gd a
WHERE apply_id IN (' . implode(',', $apply_ids['pc']) . ') AND apply_type=1
) a
LEFT JOIN finance_payment_person_apply_list b
ON a.apply_id=b.apply_id AND a.pid=b.pid AND a.paycostid=b.paycostid
LEFT JOIN contract_cus c 
ON a.cid=c.cid
LEFT JOIN v_last_executive d 
ON a.pid=d.pid
LEFT JOIN executive_paycost e 
ON a.paycostid=e.id
LEFT JOIN executive_paycost f
ON a.paycostid=f.id
WHERE b.isok=1';
	}
	
	if(!empty($apply_ids['mc'])){
		$sql[] = 'SELECT a.id AS gid,a.cid,a.payment_id,a.gd_amount,b.*,c.cusname,d.name,d.amount,e.payamount,f.payname,f.payamount AS mediacost,\'mc\' AS ptype
FROM
(
SELECT id,apply_id,payment_id,pid,paycostid,gd_amount,SUBSTRING_INDEX(pid,"-",1) AS cid
FROM finance_payment_gd
WHERE apply_id IN (' . implode(',', $apply_ids['mc']) . ') AND apply_type=2
) a
LEFT JOIN finance_payment_media_apply_list b
ON a.apply_id=b.apply_id AND a.pid=b.pid AND a.paycostid=b.paycostid
LEFT JOIN contract_cus c 
ON a.cid=c.cid
LEFT JOIN v_last_executive d 
ON a.pid=d.pid
LEFT JOIN executive_paycost e 
ON a.paycostid=e.id
LEFT JOIN executive_paycost f
ON a.paycostid=f.id
WHERE b.isok=1';
	}
	
	if(!empty($sql)){
		$dao = new Dao_Impl();
		$results = $dao->db->get_results(implode(' UNION ALL ', $sql));
		if($results !== NULL){
			
			//已收款
			$receive_amount = getReceiveAmount();
			
			//已开票
			$invoice = getInvoice();
			
			//归档金额
			$gd_amount = getGDAmountByPaycostid();
			
			$rows['total'] = count($results);
			foreach ($results as $result) {
				$receive = empty($receive_amount[$result->pid]) ? 0 : $receive_amount[$result->pid];
				
				$paid_amount = empty($gd_amount[$result->paycostid]) ? 0 : $gd_amount[$result->paycostid];
				
				$mediacost = empty($result->mediacost) ? 0 : $result->mediacost;
				
				$rows['rows'][] = array(
					'x'=>$result->gid . '_' . $result->ptype,
					'a'=>$result->pid,
					'b'=>$result->cusname,
					'c'=>$result->name,
					'd'=>$result->amount,
					'e'=>$receive,
					'f'=>empty($invoice[$result->pid]) ? 0 : $invoice[$result->pid],
					'g'=>$result->amount - $receive,
					'h'=>$result->payname,
					'i'=>$mediacost,
					'j'=>$mediacost - $paid_amount,
					'k'=>$result->payment_amount,
					'l'=>$paid_amount,
					'm'=>$result->rebate_deduction_amount,
					'n'=>'n',
					'o'=>'o',
					'p'=>'p',
					'q'=>'q',
					'r'=>'r',
					's'=>'s',
					't'=>'还款人&nbsp;' . $result->person_loan_user . '&nbsp;金额&nbsp;' . $result->person_loan_amount,
					'u'=>'u',
					'v'=>'v',
					'w'=>'<input type="text" name="cdeduction_' . $result->gid . '_' . $result->ptype . '" id="cdeduction_' . $result->gid . '_' . $result->ptype . '"/>'
				);
			}
		}
	}
	
	
	
	
	return json_encode($rows);
}
