<?php
class Finance_Receivables extends User {
	private $id;
	private $none1 = 'none';
	private $none2 = 'none';
	private $has_finance_receivables_permission = FALSE;
	private $view_normal_receivables = FALSE;

	private $type;
	private $page;
	private $search;

	private $date;
	private $amount;
	private $payer;
	private $pids_array = array();
	private $errors = array();

	private $finance_receivables_id;
	private $tt;
	private $pid;
	private $cusname;
	private $isok;
	private $content;

	private $update_id;

	const EXE_LIMIT = 15;
	
	private $djuser;

	/**
	 * @return the $has_finance_receivables_permission
	 */
	public function getHas_finance_receivables_permission() {
		return $this->has_finance_receivables_permission;
	}

	/**
	 * @return the $none1
	 */
	public function getNone1() {
		return $this->has_finance_receivables_permission ? 'none' : '';
	}

	/**
	 * @return the $none2
	 */
	public function getNone2() {
		return $this->has_finance_receivables_permission ? '' : 'none';
	}

	public function __construct($fields = array()) {
		parent::__construct();
		if (!empty($fields)) {
			foreach ($this as $key => $value) {
				if ($fields[$key] !== NULL
						&& !in_array($key,
								array('has_finance_receivables_permission',
										'view_normal_receivables'), TRUE)) {
					$this->$key = $fields[$key];
				}
			}
		}

		if (in_array($this->getUsername(),
				$GLOBALS['manager_finance_permission'], TRUE)
				|| intval($this->getBelong_dep()) === 2) {
			$this->has_finance_receivables_permission = TRUE;
		}

		if (intval($this->finance_receivables_id) > 0) {
			$row = $this->db
					->get_row(
							'SELECT a.pid,FROM_UNIXTIME(b.time) AS tt,c.cusname,a.amount,b.payer,c.city,c.dep,b.date,a.isok,d.username,d.realname FROM  finance_receivables a LEFT JOIN finance_receivables_list b ON a.receivables_list=b.id LEFT JOIN executive_alllist_view c ON a.pid=c.pid LEFT JOIN users d ON a.user=d.uid WHERE a.id='
									. intval($this->finance_receivables_id));
			if ($row !== NULL) {
				if ($this->getHas_receivables_search_permission()) {
					$receivables_array = $this->getReceivables_array();

					$citys = City::getInstance();
					$deps = Dep::getInstance();
					$dep = array();
					foreach ($deps as $key => $value) {
						$dep[$key] = $value[0];
					}

					foreach ($receivables_array as $key => $value) {
						$value = str_replace(array('（', '）', '(', ')'),
								array('', '', '', ''), $value);
						$value = explode(' ', $value);

						if (count($value) === 2) {
							//查询分公司
							if (array_search($value[1], $citys) !== FALSE
									&& intval(array_search($value[1], $citys))
											=== intval($row->city)) {
								$this->view_normal_receivables = TRUE;
								break;
							}
						} else if (count($value) === 3) {
							//查询某部门
							if (array_search($value[2], $dep) !== FALSE
									&& intval(array_search($value[2], $dep))
											=== intval($row->dep)) {
								$this->view_normal_receivables = TRUE;
								break;
							}
						}
					}
				}
			}

			$this->tt = $row->tt;
			$this->pid = $row->pid;
			$this->cusname = $row->cusname;
			$this->amount = $row->amount;
			$this->payer = $row->payer;
			$this->date = $row->date;
			$this->isok = $row->isok;
			$this->djuser = $row->realname . ' (' . $row->username . ')';
		} else if (intval($this->id) > 0) {
			$row = $this->db
					->get_row(
							'SELECT * FROM finance_receivables_list WHERE id='
									. intval($this->id) . ' AND isok=1');
			if ($row !== NULL) {
				$this->date = $row->date;
				$this->amount = $row->amount;
				$this->payer = $row->payer;
				$this->content = $row->content;
			} else {
				$this->id = NULL;
			}
		}
	}

	public function cancel_receivable() {
		if ($this->getHas_finance_receivables_permission()) {
			$result = $this->_cancel();
			return $result['message'];
		} else {
			return NO_RIGHT_TO_DO_THIS;
		}
	}

	private function _cancel() {
		if (self::validate_id(intval($this->id))) {
			$success = TRUE;
			$error = '';
			$this->db->query('BEGIN');

			$update_result = $this->db
					->query(
							'UPDATE finance_receivables_list SET isok=-1 WHERE id='
									. intval($this->id));
			if ($update_result === FALSE) {
				$success = FALSE;
				$error = '撤销收款记录失败，错误代码1';
			} else {
				$update_result = $this->db
						->query(
								'UPDATE finance_receivables SET isok=-1 WHERE receivables_list='
										. intval($this->id));
				if ($update_result === FALSE) {
					$success = FALSE;
					$error = '撤销收款记录失败，错误代码2';
				}
			}

			if ($success) {
				$this->db->query('COMMIT');
			} else {
				$this->db->query('ROLLBACK');
			}
			return array('status' => $success ? 'success' : 'error',
					'message' => $success ? '撤销收款记录成功' : $error);
		}
		return array('status' => 'error', 'message' => '收款记录选择有误');
	}

	private function _search_executive() {
		$s = '';
		if (intval($this->type) === 1) {
			$sql = 'SELECT COUNT(*) FROM (SELECT DISTINCT(a.pid) FROM finance_invoice a LEFT JOIN finance_invoice_list b ON a.invoice_list_id=b.id WHERE b.number LIKE "%'
					. $this->search . '%") z';
		} else {
			$sql = 'SELECT COUNT(*) FROM (SELECT * FROM executive_for_finance WHERE (cusname LIKE "%'
					. $this->search . '%" OR pid LIKE "%' . $this->search
					. '%" ) ORDER BY isalter DESC ) z GROUP BY z.pid';
		}

		$all_count = intval($this->db->get_var($sql));
		$page_count = ceil($all_count / self::EXE_LIMIT);
		$start = self::EXE_LIMIT * intval($this->page) - self::EXE_LIMIT;
		if ($start < 0) {
			$start = 0;
		}

		if ($all_count > 0) {
			if (intval($this->type) === 1) {
				$sql = 'SELECT m.pid,m.amount,MAX(m.isalter) AS isalter,m.cusname FROM (SELECT z.pid,y.amount,y.isalter,x.cusname FROM (SELECT DISTINCT(a.pid) FROM finance_invoice a LEFT JOIN finance_invoice_list b ON a.invoice_list_id=b.id WHERE b.number LIKE "%'
						. $this->search
						. '%") z ,executive y,contract_cus x WHERE z.pid=y.pid AND y.cid=x.cid AND y.isok<>-1 ORDER BY z.pid,y.isalter DESC) m GROUP BY m.pid LIMIT '
						. $start . ',' . self::EXE_LIMIT;
			} else {
				$sql = 'SELECT * FROM (SELECT * FROM executive_for_finance WHERE (cusname LIKE "%'
						. $this->search . '%" OR pid LIKE "%' . $this->search
						. '%" ) ORDER BY isalter DESC ) z GROUP BY z.pid ORDER BY z.time DESC LIMIT '
						. $start . ',' . self::EXE_LIMIT;
			}
			//var_dump($sql);
			$exes = $this->db->get_results($sql);
			$s .= '<div><span style="display:inline-block;width:60px;">&nbsp;&nbsp;</span><span style="display:inline-block;width:90px;"><b>执行单号</b></span>&nbsp;&nbsp;<span style="display:inline-block;width:300px;"><b>客户名称</b></span>&nbsp;&nbsp;<span style="display:inline-block;width:80px;"><b>总金额</b></span>&nbsp;&nbsp;<span style="display:inline-block;width:80px;"><b>已收账款</b></span>&nbsp;&nbsp;<span style="display:inline-block;width:80px;"><b>应收账款</b></span>&nbsp;&nbsp;<span style="display:inline-block;width:80px;"><b>已开票总金额</b></span></div>';
			foreach ($exes as $key => $exe) {
				$receivable_amount = $this->_get_receivables_amount($exe->pid);
				$done_invoice = $this->_get_invoice_amount($exe->pid);
				$s .= '<div style="height:21px;">&nbsp;<input type="checkbox" value="'
						. $exe->pid
						. '"/>
					  &nbsp;<span style="display:inline-block;width:90px;" id="pid_'
						. $exe->pid . '">' . $exe->pid
						. '</span>
					  &nbsp;<span style="display:inline-block;width:300px;" id="cus_'
						. $exe->pid . '">' . $exe->cusname
						. '</span>
					  &nbsp;<span style="display:inline-block;width:80px;text-align:right;color:#ff9933">'
						. Format_Util::my_money_format('%.2n',$exe->amount)
						. '</span>
					  &nbsp;<span style="display:inline-block;width:80px;text-align:right;color:#009900">'
						. Format_Util::my_money_format('%.2n',
								$receivable_amount)
						. '</span>
					  &nbsp;<span style="display:inline-block;width:80px;text-align:right;color:red" id="rece_'
						. $exe->pid . '">'
						. Format_Util::my_money_format('%.2n',
								$exe->amount - $receivable_amount)
						. '</span>
					  &nbsp;<span style="display:inline-block;width:100px;text-align:right;color:blue">'
						. Format_Util::my_money_format('%.2n', $done_invoice)
						. '</span>';
				if ($done_invoice > 0) {
					$s .= '&nbsp;<input type="button" class="btn" value="展 开" onclick="javascript:opendetail('
							. $key . ');">';
				}
				$s .= '</div>';

				$details = $this->db
						->get_results(
								'SELECT a.amount,b.number,b.date FROM finance_invoice a LEFT JOIN finance_invoice_list b ON a.invoice_list_id=b.id WHERE a.pid="'
										. $exe->pid . '" AND a.isok=1');
				if ($details !== NULL) {
					$s .= '<div id="det_' . $key
							. '" style="display:none;width:850px;"><div style="margin-left:700px;">';
					$s .= '<table><tr><td><b>开票金额</b></td><td><b>开票号码</b></td><td><b>开票时间</b></td></tr>';
					foreach ($details as $detail) {
						$s .= '<tr><td>'
								. Format_Util::my_money_format('%.2n',
										$detail->amount) . '</td><td>'
								. $detail->number . '</td><td>' . $detail->date . '</td></tr>';
					}
					$s .= '</table></div>';
					$s .= '</div>';
				}
			}
			$pageinfo = '<div id="pageinfo">' . intval($this->page) . ' / '
					. $page_count . ' 页 &nbsp;'
					. self::_getPrev($this->type, $this->page) . '&nbsp;'
					. self::_getNext($this->type, $this->page, $page_count)
					. '&nbsp; <input id="movepid" type="button" value="选 择" onclick="javascript:pidmove();" class="btn"/></div>';
			$s .= $pageinfo;
		} else {
			$s = '<div style="color:red"><b>&nbsp;没有找到相关内容!</b></div>';
		}
		return $s;
	}

	private static function _getPrev($type, $page) {
		if (intval($page) === 1) {
			return '';
		} else {
			return self::_get_pagination(intval($type), intval($page) - 1, TRUE);
		}
	}

	private static function _getNext($type, $page, $page_count) {
		if (intval($page) >= intval($page_count)) {
			return '';
		} else {
			return self::_get_pagination(intval($type), intval($page) + 1,
					FALSE);
		}
	}

	private static function _get_pagination($type, $page, $is_prev) {
		return '<a href="javascript:void(0)" onclick="dosearch(' . $type . ','
				. $page . ');">' . ($is_prev ? '上一页' : '下一页') . '</a>';
	}

	private function _get_receivables_amount($pid) {
		$amount = $this->db
				->get_var(
						'SELECT SUM(amount) FROM finance_receivables WHERE pid="'
								. $pid . '" AND isok=1');
		if ($amount === NULL) {
			return 0;
		}
		return $amount;
	}

	private function _get_invoice_amount($pid) {
		$sum = new Invoice($pid);
		$amount = $sum->getSumPidInvoice($pid);
		unset($sum);
		return $amount;
	}

	private function validate_form_value($action) {
		$errors = array();
		if (in_array($action, array('add', 'update'), TRUE)) {
			if ($action === 'update') {
				if (!self::validate_id(intval($this->update_id))) {
					$errors[] = '收款记录有误';
				}
			}

			if (!self::validate_field_not_empty($this->date)
					|| !self::validate_field_not_null($this->date)) {
				$errors[] = '收款日期不能为空';
			} else if (strtotime($this->date) === FALSE) {
				$errors[] = '收款日期不是一个有效的时间值';
			}

			if (!self::validate_field_not_empty($this->amount)
					|| !self::validate_field_not_null($this->amount)) {
				$errors[] = '收款金额不能为空';
			} else if (!self::validate_money($this->amount)) {
				$errors[] = '收款金额不是有效的金额数值';
			}

			if (!self::validate_field_not_empty($this->payer)
					|| !self::validate_field_not_null($this->payer)) {
				$errors[] = '付款人名称不能为空';
			} else if (!self::validate_field_max_length($this->payer, 50)) {
				$errors[] = '付款人名称最多50个字符';
			}

			$pids_array = $this->pids_array;
			if (empty($pids_array)) {
				$errors[] = '没有分配收款额';
			} else {
				$allval = 0;
				foreach ($pids_array as $key => $value) {
					if (!self::validate_invoice_money($value)) {
						$errors[] = '执行单' . $key . '的分配收款额有误';
					} else {
						$allval += $value;
					}
				}

				if ($allval != $this->amount) {
					$errors[] = '收款金额必须等于各执行单收款的总合';
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

	private function _check_pid_amount($pid, $amount) {
		$row = $this->db
				->get_row(
						'SELECT isok,amount FROM executive WHERE pid="' . $pid
								. '" ORDER BY isalter DESC LIMIT 1 FOR UPDATE');
		if ($row === NULL) {
			//没有该执行单
			return FALSE;
		} else {
			if (intval($row->isok) === -1) {
				//该执行单被撤销
				return FALSE;
			} else {
				$done_rece = $this->db
						->get_var(
								'SELECT SUM(amount) FROM finance_receivables WHERE pid="'
										. $pid . '" AND isok=1 FOR UPDATE');
				if ($amount > ($row->amount - $done_rece)) {
					//收款金额大过额定值
					return FALSE;
				}
			}
		}
		return TRUE;
	}

	public function add_receivables() {
		if ($this->validate_form_value('add')) {
			$success = TRUE;
			$error = '';
			$this->db->query('BEGIN');

			$pids_array = $this->pids_array;
			$ids = array();
			$content = array();

			$check_all = TRUE;
			foreach ($pids_array as $pid => $amount) {
				$check_result = $this->_check_pid_amount($pid, $amount);
				if (!$check_result) {
					$check_all = FALSE;
					break;
				}
			}

			if (!$check_all) {
				$success = FALSE;
				$error = '新增收款记录失败，执行单及金额有误';
			}

			if ($success) {
				foreach ($pids_array as $pid => $amount) {
					$content[] = $pid . '^' . $amount;
					$insert_result = $this->db
							->query(
									'INSERT INTO finance_receivables(pid,amount,user,time) VALUE("'
											. $pid . '","' . $amount . '",'
											. $this->getUid() . ','
											. $_SERVER['REQUEST_TIME'] . ')');
					if ($insert_result !== FALSE) {
						$ids[] = $this->db->insert_id;
					} else {
						$success = FALSE;
						$error = '新增收款记录失败，错误代码1';
						break;
					}
				}
			}

			if ($success && !empty($ids)) {
				$insert_result = $this->db
						->query(
								'INSERT INTO finance_receivables_list(date,amount,receivables_ids,content,user,time,payer) VALUE("'
										. $this->date . '","' . $this->amount
										. '","' . implode('^', $ids) . '","'
										. implode('|', $content) . '",'
										. $this->getUid() . ','
										. $_SERVER['REQUEST_TIME'] . ',"'
										. $this->payer . '")');
				if ($insert_result === FALSE) {
					$success = FALSE;
					$error = '新增收款记录失败，错误代码2';
				} else {
					$insert_id = $this->db->insert_id;
				}
			}

			if ($success && $insert_id > 0) {
				$update_result = $this->db
						->query(
								'UPDATE finance_receivables SET receivables_list='
										. $insert_id . ' WHERE id IN ('
										. implode(',', $ids) . ')');
				if ($update_result === FALSE) {
					$success = FALSE;
					$error = '新增收款记录失败，错误代码3';
				}
			}

			if ($success) {
				$this->db->query('COMMIT');
			} else {
				$this->db->query('ROLLBACK');
			}

			return array('status' => $success ? 'success' : 'error',
					'message' => $success ? '新增收款记录成功' : $error);
		}
		return array('status' => 'error', 'message' => $this->errors);
	}

	public function get_search_executive_html() {
		if ($this->getHas_finance_receivables_permission()) {
			return $this->_search_executive();
		} else {
			return NO_RIGHT_TO_DO_THIS;
		}
	}

	public function get_add_receivables_html() {
		if ($this->getHas_finance_receivables_permission()) {
			$buf = file_get_contents(
					TEMPLATE_PATH
							. 'finance/receivables/finance_receivables.tpl');
			return str_replace(
					array('[LEFT]', '[TOP]', '[VCODE]', '[NONE1]', '[NONE2]',
							'[BASE_URL]'),
					array($this->get_left_html(), $this->get_top_html(),
							$this->get_vcode(), $this->getNone1(),
							$this->getNone2(), BASE_URL), $buf);
		} else {
			return User::no_permission();
		}
	}

	public function getView_normal_receivables() {
		return $this->view_normal_receivables;
	}

	public function getTt() {
		return $this->tt;
	}

	public function getPid() {
		return $this->pid;
	}

	public function getCusname() {
		return $this->cusname;
	}

	public function getAmount() {
		return $this->amount;
	}

	public function getPayer() {
		return $this->payer;
	}

	public function getDate() {
		return $this->date;
	}

	public function get_import_receivables_html() {
		if ($this->getHas_finance_receivables_permission()) {
			$buf = file_get_contents(
					TEMPLATE_PATH
							. 'finance/receivables/finance_receivables_import.tpl');
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
		//第一列 收款日期
		if (!self::validate_field_not_null($infos[0])) {
			$errors[] = '第' . $line . '行，第1列【收款日期】不能为空';
			$isok = $isok ? FALSE : $isok;
		} else if (!self::validate_date_int(
				PHPExcel_Shared_Date::ExcelToPHP($infos[0]))) {
			$errors[] = '第' . $line . '行，第1列【收款日期】不是有效的时间值';
			$isok = $isok ? FALSE : $isok;
		}

		//第二列 收款金额
		if (!self::validate_invoice_money($infos[1])) {
			$errors[] = '第' . $line . '行，第2列【收款金额】不是有效的金额值';
			$isok = $isok ? FALSE : $isok;
		}

		//第三列 付款人名称
		if (!self::validate_field_not_null($infos[2])
				|| !self::validate_field_not_empty($infos[2])) {
			$errors[] = '第' . $line . '行，第3列【付款人名称】不能为空';
			$isok = $isok ? FALSE : $isok;
		} else if (!self::validate_field_max_length($infos[2], 50)) {
			$errors[] = '第' . $line . '行，第3列【付款人名称】最多50个字符';
			$isok = $isok ? FALSE : $isok;
		}

		//第四列 执行单号
		if (!$this->_check_pid_amount($infos[3], $infos[1])) {
			$errors[] = '第' . $line . '行，执行单状态或金额有误';
			$isok = $isok ? FALSE : $isok;
		}

		return $isok;
	}

	public function import_receivables($file) {
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
			//应该是4列，行数>1
			if ($sum_cols_count !== 4) {
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
						$result1 = $this->db
								->query(
										'INSERT INTO finance_receivables(pid,amount,user,time) VALUE("'
												. $infos[$i][3] . '","'
												. $infos[$i][1] . '",'
												. $this->getUid() . ','
												. $_SERVER['REQUEST_TIME']
												. ')');
						if ($result1 === FALSE) {
							$db_ok = FALSE;
						} else {
							$ids = $this->db->insert_id;
							$result2 = $this->db
									->query(
											'INSERT INTO finance_receivables_list(date,amount,receivables_ids,content,user,time,payer,isexport) VALUE("'
													. date('Y-m-d',
															PHPExcel_Shared_Date::ExcelToPHP(
																	$infos[$i][0]))
													. '","' . $infos[$i][1]
													. '","' . $ids . '","'
													. $infos[$i][3] . '^'
													. $infos[$i][1] . '",'
													. $this->getUid() . ','
													. $_SERVER['REQUEST_TIME']
													. ',"' . $infos[$i][2]
													. '",1)');
							if ($result2 === FALSE) {
								$db_ok = FALSE;
							} else {
								$list_id = $this->db->insert_id;
								$result3 = $this->db
										->query(
												'UPDATE finance_receivables SET receivables_list='
														. $list_id
														. ' WHERE id=' . $ids);
								if ($result3 === FALSE) {
									$db_ok = FALSE;
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

	public function getIsok() {
		return intval($this->isok) === -1 ? '撤销' : '生效';
	}

	private function _get_pid_list() {
		$s = '';
		if (!empty($this->content)) {
			$contents = explode('|', $this->content);
			foreach ($contents as $content) {
				$content = explode('^', $content);
				$row = $this->db
						->get_row(
								'SELECT a.pid,a.amount,b.cusname FROM executive a LEFT JOIN contract_cus b ON a.cid=b.cid WHERE a.pid="'
										. $content[0]
										. '" ORDER BY a.isalter DESC LIMIT 1');
				if ($row !== NULL) {
					$receivable_amount = $this
							->_get_receivables_amount($row->pid);
					$tmps = '<td>' . $row->pid . '</td><td>' . $row->cusname
							. '</td><td><font color="red">'
							. Format_Util::my_money_format('%.2n',
									$row->amount - $receivable_amount)
							. '</font></td>';
					$s .= '<tr><td><img src="' . BASE_URL
							. 'images/close.png" onclick="removepid(this,\''
							. $content[0]
							. '\')" width="12" height="12" />&nbsp;<input type="text" onblur="javascript:checksum();" class="validate[required,custom[invoiceMoney]] text" style="width:100px; height:12px" name="amount1_'
							. $content[0] . '" id="amount1_' . $content[0]
							. '" value="' . $content[1] . '"/></td>' . $tmps
							. '</tr>';
				}
			}
		}

		return $s;
	}

	private function _get_pids() {
		if (!empty($this->content)) {
			$arr = array();
			$contents = explode('|', $this->content);
			foreach ($contents as $content) {
				$arr[] = reset(explode('^', $content));
			}
			if (!empty($arr)) {
				return ',' . implode(',', $arr) . ',';
			}
			return ',';
		}
		return ',';
	}

	public function get_edit_receivables_html() {
		if ($this->id !== NULL) {
			if ($this->getHas_finance_receivables_permission()) {
				$buf = file_get_contents(
						TEMPLATE_PATH
								. 'finance/receivables/finance_receivables_edit.tpl');
				return str_replace(
						array('[LEFT]', '[TOP]', '[VCODE]', '[NONE1]',
								'[NONE2]', '[DATE]', '[AMOUNT]', '[PAYER]',
								'[PIDLIST]', '[PIDS]', '[UPDATEID]',
								'[BASE_URL]'),
						array($this->get_left_html(), $this->get_top_html(),
								$this->get_vcode(), $this->getNone1(),
								$this->getNone2(), $this->date, $this->amount,
								$this->payer, $this->_get_pid_list(),
								$this->_get_pids(), $this->id, BASE_URL), $buf);
			} else {
				return User::no_permission();
			}
		} else {
			return User::no_object('没有该收款记录');
		}
	}

	public function update_receivables() {
		if ($this->validate_form_value('update')) {
			$success = TRUE;
			$error = '';
			$this->db->query('BEGIN');

			//删除旧记录
			$delete_result = $this->db
					->query(
							'DELETE FROM finance_receivables WHERE receivables_list='
									. intval($this->update_id));
			if ($delete_result !== FALSE) {
				$pids_array = $this->pids_array;
				$ids = array();
				$content = array();

				$check_all = TRUE;
				foreach ($pids_array as $pid => $amount) {
					$check_result = $this->_check_pid_amount($pid, $amount);
					if (!$check_result) {
						$check_all = FALSE;
						break;
					}
				}

				if (!$check_all) {
					$success = FALSE;
					$error = '更新收款记录失败，执行单及金额有误';
				}

				if ($success) {
					foreach ($pids_array as $pid => $amount) {
						$content[] = $pid . '^' . $amount;
						$insert_result = $this->db
								->query(
										'INSERT INTO finance_receivables(pid,receivables_list,amount,user,time) VALUE("'
												. $pid . '","'
												. intval($this->update_id)
												. '","' . $amount . '",'
												. $this->getUid() . ','
												. $_SERVER['REQUEST_TIME']
												. ')');
						if ($insert_result !== FALSE) {
							$ids[] = $this->db->insert_id;
						} else {
							$success = FALSE;
							$error = '更新收款记录失败，错误代码2';
							break;
						}
					}
				}

				if ($success && !empty($ids)) {
					$update_result = $this->db
							->query(
									'UPDATE finance_receivables_list SET date="'
											. $this->date . '",amount="'
											. $this->amount
											. '",receivables_ids="'
											. implode('^', $ids)
											. '",content="'
											. implode('|', $content)
											. '",time="'
											. $_SERVER['REQUEST_TIME']
											. '",payer="' . $this->payer
											. '" WHERE id='
											. intval($this->update_id));
					if ($update_result === FALSE) {
						$success = FALSE;
						$error = '更新收款记录失败，错误代码3';
					}
				}
			} else {
				$success = FALSE;
				$error = '更新收款记录失败，错误代码1';
			}

			if ($success) {
				$this->db->query('COMMIT');
			} else {
				$this->db->query('ROLLBACK');
			}

			return array('status' => $success ? 'success' : 'error',
					'message' => $success ? '更新收款记录成功' : $error);

		}

		return array('status' => 'error', 'message' => $this->errors);
	}

	public function getDjuser()
	{
	    return $this->djuser;
	}
}
