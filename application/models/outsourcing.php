<?php
class Outsourcing extends User {
	private $executive_id;
	private $id;
	private $audit_pass;
	private $pid;
	private $remark;
	private $executive_dep_array;
	private $page;
	private $all_count;
	private $page_count;
	private $has_audit_outsourcing_permission = FALSE;
	private $datas = array();
	private $errors = array();

	private $selectdep = array();

	const LIMIT = 50;

	public function __construct($fields = array()) {
		parent::__construct();
		if (!empty($fields)) {
			foreach ($this as $key => $value) {
				if ($fields[$key] !== NULL
						&& !in_array($key,
								array('has_audit_outsourcing_permission'),
								TRUE)) {
					$this->$key = $fields[$key];
				}
			}
			if (!empty($this->executive_id)) {
				$results = $this->db
						->get_results(
								'SELECT a.executive_id,a.pid,a.outsourcing_type_id,a.step,a.support_id,a.executive_dep_id,b.process_id,c.outsourcing_process_name,c.process
FROM outsourcing_pid_type a
LEFT JOIN outsourcing_type_process b
ON a.outsourcing_type_id=b.type_id
LEFT JOIN outsourcing_process c
ON b.process_id=c.id
WHERE a.executive_id=' . intval($this->executive_id)
										. ' AND a.isok=0 AND c.isok=1');
				if ($results !== NULL) {
					$datas = array();
					foreach ($results as $result) {
						$op = json_decode($result->process);
						if ($op[$result->step]
								=== ($this->getRealname() . ' ('
										. $this->getUsername() . ')')) {
							if (!$this->has_audit_outsourcing_permission) {
								$this->has_audit_outsourcing_permission = TRUE;
							}
							$datas[$result->executive_id][$result->pid][] = array(
									'outsourcing_type_id' => $result
											->outsourcing_type_id,
									'step' => $result->step,
									'support_id' => $result->support_id,
									'executive_dep_id' => $result
											->executive_dep_id,
									'process_id' => $result->process_id,
									'outsourcing_process_name' => $result
											->outsourcing_process_name,
									'process' => $result->process);
						}
					}
					$this->datas = $datas;
				}
			} else {
				$this->has_audit_outsourcing_permission = $this
						->getHasOutsourcingAuditTab();
			}
		}
	}

	public function getAuditOutsourcingHtml() {
		if ($this->has_audit_outsourcing_permission) {
			$datas = $this->datas;
			$datas = $datas[$this->executive_id];

			$pid = '';
			$has_supports = array();
			$executive_dep_array = array();
			foreach ($datas as $k => $data) {
				if ($pid === '') {
					$pid = $k;
				}
				foreach ($data as $d) {
					$has_supports[] = $d['support_id'];
					$executive_dep_array[$d['support_id']][] = $d['executive_dep_id'];
				}
			}

			$row = $this->db
					->get_row(
							'SELECT pid,name,FROM_UNIXTIME(time) AS time,support FROM executive WHERE id='
									. intval($this->executive_id));
			$sum_cost = 0;
			$pay_array = array();
			//??
			if ($row !== NULL) {
				$support = $row->support;
				$pay_array = array();
				if (!empty($support)) {
					$support = explode('|', $support);
					foreach ($support as $ss) {
						$ss = explode('^', $ss);
						if (in_array($ss[0], $has_supports, TRUE)) {

							$costpaymentinfoids = $this->db
									->get_row(
											'SELECT a.costpaymentinfoids,b.depname,b.id FROM executive_dep a LEFT JOIN hr_department b ON a.dep=b.id WHERE a.id='
													. intval($ss[1]));

							if ($costpaymentinfoids !== NULL) {
								$depname = $costpaymentinfoids->depname;
								$costinfos = $costpaymentinfoids
										->costpaymentinfoids;
								$dep_id = $costpaymentinfoids->id;

								$costinfos = explode('^', $costinfos);
								$costinfos = Array_Util::my_remove_array_other_value(
										$costinfos, array('', NULL));

								$pays = $this->db
										->get_results(
												'SELECT a.payname,a.payamount,b.id FROM executive_paycost a LEFT JOIN new_supplier b
ON a.payname=b.supplier_name
LEFT JOIN new_supplier_info c
ON b.id=c.supplier_id WHERE a.id IN ('
														. implode(',',
																$costinfos)
														. ') AND c.supplier_type=2');

								if ($pays !== NULL) {
									foreach ($pays as $pay) {
										$pay_array[$dep_id . '_' . $depname][$pay
												->id . '_' . $pay->payname] += $pay
												->payamount;
										$sum_cost += $pay->payamount;
									}
								}
							}
						}
					}
				}

				$outsourcing_list = '';
				if (!empty($pay_array)) {
					$count = 1;
					foreach ($pay_array as $dep => $pa) {
						$dep = explode('_', $dep);
						foreach ($pa as $key => $val) {
							$key = explode('_', $key);
							$outsourcing_list .= '<tr><td>'
									. ($count === 1 ? '<input type="checkbox" name="selectdep[]" value="'
													. $dep[0] . '">&nbsp;'
													. $dep[1] : '')
									. '</td><td><a href="' . BASE_URL
									. 'outsourcing/?o=outsourcinginfo&id='
									. $key[0] . '" target="_blank">' . $key[1]
									. '</a></td><td>' . $val . '</td></tr>';
							$count++;
						}
					}
				}
				//var_dump($pay_array);

				$buf = file_get_contents(
						TEMPLATE_PATH . 'outsourcing/outsourcing_audit.tpl');
				return str_replace(
						array('[LEFT]', '[TOP]', '[VCODE]', '[PID]', '[NAME]',
								'[SUMCOST]', '[OUTSOURCINGLIST]', '[ID]',
								'[PID]', '[EXECUTIVEDEP]', '[BASE_URL]'),
						array($this->get_left_html(), $this->get_top_html(),
								$this->get_vcode(), $row->pid, $row->name,
								$sum_cost, $outsourcing_list,
								$this->executive_id, $pid,
								!empty($executive_dep_array) ? json_encode(
												$executive_dep_array) : '',
								BASE_URL), $buf);

			}
			return User::no_object('没有该执行单');

		}
		return User::no_permission();
	}

	public function getOutsourcingAuditListHtml() {
		if ($this->has_audit_outsourcing_permission) {
			$lists = $this->getOutsourcingAudit();
			$datas = array();
			if (!empty($lists)) {
				foreach ($lists as $list) {
					$row = $this->db
							->get_row(
									'SELECT pid,name,FROM_UNIXTIME(time) AS time,support FROM executive WHERE id='
											. intval($list['executive_id']));
					$support = $row->support;
					$pay_array = array();
					if (!empty($support)) {
						//??
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

					$datas[] = array('executive_id' => $list['executive_id'],
							'pid' => $row->pid, 'name' => $row->name,
							'time' => $row->time, 'pay_array' => $pay_array);
				}
			}

			$this->all_count = count($datas);
			$this->page_count = ceil($this->all_count / self::LIMIT);
			$start = self::LIMIT * intval($this->page) - self::LIMIT;
			if ($start < 0) {
				$start = 0;
			}

			$datas = array_slice($datas, $start, self::LIMIT);
			$outsourcing_audit_list = '';
			if (!empty($datas)) {
				foreach ($datas as $data) {
					$outsourcing_audit_list .= '<tr><td>' . $data['pid']
							. '</td>
						<td>' . $data['name'] . '</td>
				       	<td>' . $data['time'] . '</td>
				       	<td>'
							. implode('<br/>', array_keys($data['pay_array']))
							. '</td>
				      	<td>'
							. implode('<br/>', array_values($data['pay_array']))
							. '</td>
				      	<td><a href="' . BASE_URL
							. 'outsourcing/?o=auditoutsourcing&id='
							. intval($data['executive_id'])
							. '">审核</a></td></tr>';
				}
			}

			$counts = $this->page . '	/' . $this->page_count
					. ' 页 &nbsp;&nbsp;';

			$buf = file_get_contents(
					TEMPLATE_PATH . 'outsourcing/outsourcing_audit_list.tpl');
			return str_replace(
					array('[LEFT]', '[TOP]', '[VCODE]',
							'[OUTSOURCINGAUDITLIST]', '[ALLCOUNTS]',
							'[COUNTS]', '[NEXT]', '[PREV]', '[BASE_URL]'),
					array($this->get_left_html(), $this->get_top_html(),
							$this->get_vcode(), $outsourcing_audit_list,
							$this->all_count, $counts, $this->_getNext(),
							$this->_getPrev(), BASE_URL), $buf);
		}
		return User::no_permission();
	}

	private function _get_pagination($is_prev) {
		return '<a href="' . BASE_URL
				. 'outsourcing/?o=auditoutsourcinglists&page='
				. ($is_prev ? intval($this->page) - 1 : intval($this->page) + 1)
				. '">' . ($is_prev ? '上一页' : '下一页') . '</a>';
	}

	private function _getPrev() {
		if (intval($this->page) === 1) {
			return '';
		} else {
			return $this->_get_pagination(TRUE);
		}
	}

	private function _getNext() {
		if (intval($this->page) >= intval($this->page_count)) {
			return '';
		} else {
			return $this->_get_pagination(FALSE);
		}
	}

	private function validate_form_value($action) {
		$errors = array();
		if (in_array($action, array('audit'), TRUE)) {
			if (!self::validate_id(intval($this->id))) {
				$errors[] = '执行单选择有误';
			}

			if (!in_array(intval($this->audit_pass), array(0, 1), TRUE)) {
				$errors[] = '审核状态选择有误';
			}

			if (intval($this->audit_pass) === 0) {
				//驳回的话需要填写审核留言
				if (empty($this->remark)) {
					$errors[] = '如驳回，审核留言不能为空';
				}
			}

			if (!empty($this->remark)) {
				if (!self::validate_field_max_length($this->remark, 500)) {
					$errors[] = '审核留言最多500个字符';
				}
			}

			if (!empty($this->selectdep)) {
				foreach ($this->selectdep as $selectdep) {
					if (!self::validate_id(intval($selectdep))) {
						$errors[] = '支持部门选择有误';
						break;
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

	public function auditOutsourcing() {
		if ($this->has_audit_outsourcing_permission) {
			if ($this->validate_form_value('audit')) {
				$success = TRUE;
				$error = '';
				$this->db->query('BEGIN');

				$executive_dep_array = $this->executive_dep_array;
				if (!empty($executive_dep_array)) {
					$executive_dep_array = str_replace('&quot;', '"',
							$executive_dep_array);
					$executive_dep_array = (array) (json_decode(
							$executive_dep_array));
					//var_dump($executive_dep_array);
				}

				//如果$this->selectdep为空则整体驳回或者通过，不然就选择的支持部门通过或者驳回
				if (intval($this->audit_pass) === 0) {
					//驳回
					$update_result = $this->db
							->query(
									'UPDATE outsourcing_pid_type SET  isok=-2 WHERE executive_id='
											. intval($this->id)
											. (!empty($this->selectdep) ? ' AND support_id IN ('
															. implode(',',
																	$this
																			->selectdep)
															. ')' : ''));
					if ($update_result === FALSE) {
						$success = FALSE;
						$error = '驳回执行单外包出错，错误代码1';
					} else {
						//驳回到支持部门tf
						$exe = new Executive();
						$log_result = $exe
								->do_executive_log($this->pid, $this->remark,
										$this->getRealname(),
										'<font color=\'#ff9900\'>外包审核 驳回 至 支持部门TF</font>');
						if ($log_result['status'] === 'success') {
							$msgid = $log_result['msgid'];
						} else {
							$success = FALSE;
							$error = '驳回执行单外包记录日志操作失败';
						}

						if ($success) {
							$depinfoids = array();
							if (!empty($executive_dep_array)) {
								foreach ($executive_dep_array as $kk => $eda) {
									if (!empty($this->selectdep)) {
										if (in_array($kk, $this->selectdep)) {
											foreach ($eda as $depinfoid) {
												$depinfoids[] = $depinfoid;
											}
										}
									} else {
										foreach ($eda as $depinfoid) {
											$depinfoids[] = $depinfoid;
										}
									}
								}
							}
							if (!empty($depinfoids)) {
								$update_result = $this->db
										->query(
												'UPDATE executive_dep SET step=0,msgid='
														. $msgid
														. ',audittime='
														. time()
														. ' WHERE id IN ('
														. implode(',',
																$depinfoids)
														. ')');
								if ($update_result === FALSE) {
									$success = FALSE;
									$error = '支持部门审核驳回操作失败';
								}
							}
						}
					}
				} else {
					//通过

					$results = $this->db
							->get_results(
									'SELECT a.id,a.executive_id,a.pid,a.outsourcing_type_id,a.step,a.support_id,a.executive_dep_id,b.process_id,c.outsourcing_process_name,c.process
FROM outsourcing_pid_type a
LEFT JOIN outsourcing_type_process b
ON a.outsourcing_type_id=b.type_id
LEFT JOIN outsourcing_process c
ON b.process_id=c.id
WHERE a.executive_id=' . intval($this->id)
											. (!empty($this->selectdep) ? ' AND support_id IN ('
															. implode(',',
																	$this
																			->selectdep)
															. ')' : '')
											. ' AND a.isok=0 AND c.isok=1 FOR UPDATE');

					if ($results !== NULL) {
						$all_ok = TRUE;
						$exe_deps = array();
						foreach ($results as $result) {
							$step = $result->step;
							$process = json_decode($result->process);
							$all_steps = count($process);
							$my_step = array_keys($process,
									$this->getRealname() . ' ('
											. $this->getUsername() . ')', TRUE);
							if (!empty($my_step)) {
								$my_step = $my_step[0] + 1;
								//更新outsourcing_pid_type的step，如果是审核都完成了，状态变化，并且支持部门的审核流程走完

								//var_dump($all_steps);
								//var_dump($my_step);
								if ($all_steps === $my_step) {
									$exe_deps[] = $result->executive_dep_id;
								} else {
									if ($all_ok) {
										$all_ok = FALSE;
									}
								}

								$update_result = $this->db
										->query(
												'UPDATE outsourcing_pid_type SET step=step+1'
														. ($all_steps
																=== $my_step ? ',isok=1'
																: '')
														. ' WHERE id='
														. intval($result->id));
								if ($update_result === FALSE) {
									$success = FALSE;
									$error = '通过执行单外包出错，错误代码1';
								}
							}
						}

						if ($all_ok && !empty($exe_deps)) {
							$update_result = $this->db
									->query(
											'UPDATE executive_dep SET isok=1 WHERE id IN ('
													. implode(',', $exe_deps)
													. ')');
							if ($update_result === FALSE) {
								$success = FALSE;
								$error = '通过执行单外包出错，错误代码2';
							} else {
								//支持部门都确认了，执行单进一步	
								if ($exe
										->check_is_all_dep_ok(
												$this->db
														->get_var(
																'SELECT support FROM executive WHERE id='
																		. intval(
																				$this
																						->id)))) {
									$update_result = $this->db
											->query(
													'UPDATE executive SET step=step+1,msgid=0 WHERE id='
															. intval($this->id));
									if ($update_result === FALSE) {
										$success = FALSE;
										$error = '更新执行单状态失败';
									}
								}
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
						'message' => $success ? '审核执行单外包成功' : $error);
			}
			return array('status' => 'error', 'message' => $this->errors);
		}
		return array('status' => 'error', 'message' => NO_RIGHT_TO_DO_THIS);
	}

	public function getOutsourcingInfoHtml() {
		if ($this->has_audit_outsourcing_permission) {
			$row = $this->db
					->get_row(
							'SELECT a.supplier_name FROM new_supplier a LEFT JOIN new_supplier_info b ON a.id=b.supplier_id WHERE a.id='
									. intval($this->id)
									. ' AND a.isok=1 AND b.supplier_type=2');
			if ($row !== NULL) {

				$buf = file_get_contents(
						TEMPLATE_PATH . 'outsourcing/outsourcing_info.tpl');
				return str_replace(
						array('[LEFT]', '[TOP]', '[VCODE]', '[SUPPLIERNAME]',
								'[ID]', '[BASE_URL]'),
						array($this->get_left_html(), $this->get_top_html(),
								$this->get_vcode(), $row->supplier_name,
								intval($this->id), BASE_URL), $buf);
			}
			return User::no_object('没有该外包信息');
		}
		return User::no_permission();
	}
}
