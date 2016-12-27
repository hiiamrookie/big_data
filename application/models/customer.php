<?php
class Customer extends User {
	private $customer_name;
	private $safety;
	private $tmpsafety;
	private $tmpsafety_deadline;
	private $customer_id;
	private $search;
	private $errors = array();
	private $cusnames_array = array();

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

	public function get_customer_add_html() {
		if ($this->getHas_manager_customer_safety_permission()) {
			$buf = file_get_contents(TEMPLATE_PATH . 'manage/customer_add.tpl');
			return str_replace(
					array('[LEFT]', '[TOP]', '[VCODE]', '[BASE_URL]'),
					array($this->get_left_html(), $this->get_top_html(),
							$this->get_vcode(), BASE_URL), $buf);
		}
		return User::no_permission();
	}

	public function get_customer_import_html() {
		if ($this->getHas_manager_customer_safety_permission()) {
			$buf = file_get_contents(
					TEMPLATE_PATH . 'manage/customer_import.tpl');
			return str_replace(
					array('[LEFT]', '[TOP]', '[VCODE]', '[VALIDATEFILE]',
							'[MAXFILESIZE]', '[BASE_URL]'),
					array($this->get_left_html(), $this->get_top_html(),
							$this->get_vcode(),
							implode(',',
									$GLOBALS['defined_upload_execel_validate_type']),
							UPLOAD_FILE_MAX_SIZE / (1024 * 1024), BASE_URL),
					$buf);
		}
		return User::no_permission();
	}

	private function validate_form_value($action) {
		$errors = array();
		if (in_array($action, array('add', 'update', 'relate'), TRUE)) {
			if ($action !== 'relate') {
				if (!self::validate_field_not_empty($this->customer_name)
						|| !self::validate_field_not_null($this->customer_name)) {
					$errors[] = '客户名称不能为空';
				} else if (!self::validate_field_max_length(
						$this->customer_name, 100)) {
					$errors[] = '客户名称长度最多100个字符';
				}

				if (!self::validate_money($this->safety)) {
					$errors[] = '保险额度不是有效的金额值';
				}

				if (self::validate_field_not_empty($this->tmpsafety)
						&& !self::validate_money($this->tmpsafety)) {
					$errors[] = '临时保险额度不是有效的金额值';
				}

				if ($this->tmpsafety > 0
						&& !self::validate_date($this->tmpsafety_deadline)) {
					$errors[] = '临时保险额度截至日期不是有效的日期值';
				}
			} else {
				//if (empty($this->cusnames_array)) {
				//	$errors[] = 'OA客户必须选择';
				//}
			}

			if (in_array($action, array('update', 'relate'))
					&& !self::validate_id(intval($this->customer_id))) {
				$errors[] = '系统客户选择有误';
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

	public function add_customer() {
		if ($this->getHas_manager_customer_safety_permission()) {
			if ($this->validate_form_value('add')) {
				$success = TRUE;
				$error = '';
				$this->db->query('BEGIN');

				$id = $this->db
						->get_var(
								'SELECT id FROM customer_safety WHERE customer_name="'
										. $this->customer_name . '"');
				if ($id > 0) {
					$success = FALSE;
					$error = '该用户名已经存在';
				} else {
					$insert_result = $this->db
							->query(
									'INSERT INTO customer_safety(customer_name,safety,tmpsafety,tmpsafety_deadline,isok) VALUE("'
											. $this->customer_name . '",'
											. $this->safety . ','
											. (empty($this->tmpsafety) ? 0
													: $this->tmpsafety) . ','
											. (empty($this->tmpsafety_deadline) ? 'NULL'
													: '"'
															. $this
																	->tmpsafety_deadline
															. '"') . ',1)');
					if ($insert_result === FALSE) {
						$success = FALSE;
						$error = '新增系统客户失败';
					}
				}

				if ($success) {
					$this->db->query('COMMIT');
				} else {
					$this->db->query('ROLLBACK');
				}

				return array('status' => $success ? 'success' : 'error',
						'message' => $success ? '新增系统客户成功' : $error);
			}
			return array('status' => 'error', 'message' => $this->errors);
		}
		return User::no_permission();
	}

	public function get_customer_edit_html() {
		if ($this->getHas_manager_customer_safety_permission()) {
			$row = $this->db
					->get_row(
							'SELECT customer_name,safety,tmpsafety,tmpsafety_deadline FROM customer_safety WHERE id='
									. intval($this->customer_id)
									. ' AND isok=1');
			if ($row !== NULL) {
				$buf = file_get_contents(
						TEMPLATE_PATH . 'manage/customer_edit.tpl');
				return str_replace(
						array('[LEFT]', '[TOP]', '[VCODE]', '[CUSTOMERNAME]',
								'[SAFETY]', '[CUSTOMERID]', '[TMPSAFETY]',
								'[TMPSAFETYDEADLINE]', '[BASE_URL]'),
						array($this->get_left_html(), $this->get_top_html(),
								$this->get_vcode(), $row->customer_name,
								$row->safety, intval($this->customer_id),
								(empty($row->tmpsafety) ? '' : $row->tmpsafety),
								(empty($row->tmpsafety_deadline) ? ''
										: $row->tmpsafety_deadline), BASE_URL),
						$buf);
			} else {
				return User::no_object('没有该系统客户');
			}
		}
		return User::no_permission();
	}

	private static function _get_cusnames_checkbox($cusnames) {
		if (empty($cusnames)) {
			return '无';
		}
		$s = '<table>';
		foreach ($cusnames as $key => $cusname) {
			if ($key === 0 || $key % 4 === 0) {
				$s .= '<tr>';
			}
			$s .= '<td><input type="checkbox" name="cusname[]" value="'
					. $cusname . '" checked="checked">&nbsp;' . $cusname
					. '</td>';
			if ($key % 4 === 3) {
				$s .= '</tr>';
			}
		}
		$s .= '</table>';
		return $s;
	}

	public function get_customer_relate_html() {
		if ($this->getHas_manager_customer_safety_permission()) {
			$results = $this->db
					->get_results(
							'SELECT cusname,customer_name,safety FROM v_customer_cusname WHERE customer_id='
									. intval($this->customer_id));
			if ($results !== NULL) {
				$customer_name = '';
				$safety = 0;
				$cusnames = array();
				foreach ($results as $key => $result) {
					if ($key === 0) {
						$customer_name = $result->customer_name;
						$safety = $result->safety;
					}
					if ($result->cusname !== NULL) {
						$cusnames[] = $result->cusname;
					}
				}

				$buf = file_get_contents(
						TEMPLATE_PATH . 'manage/customer_relate.tpl');
				return str_replace(
						array('[LEFT]', '[TOP]', '[VCODE]', '[CUSTOMERNAME]',
								'[SAFETY]', '[CUSTOMERID]', '[OACUSNAMES]',
								'[BASE_URL]'),
						array($this->get_left_html(), $this->get_top_html(),
								$this->get_vcode(), $customer_name,
								Format_Util::my_money_format('%.2n', $safety),
								intval($this->customer_id),
								self::_get_cusnames_checkbox($cusnames),
								BASE_URL), $buf);
			} else {
				return User::no_object('没有该系统客户');
			}
		}
		return User::no_permission();
	}

	public function update_customer() {
		if ($this->getHas_manager_customer_safety_permission()) {
			if ($this->validate_form_value('update')) {
				$success = TRUE;
				$error = '';
				$this->db->query('BEGIN');

				$id = $this->db
						->get_var(
								'SELECT id FROM customer_safety WHERE customer_name="'
										. $this->customer_name . '" AND id<>'
										. intval($this->customer_id));
				if ($id > 0) {
					$success = FALSE;
					$error = '该系统客户名称已经存在';
				} else {
					$update_result = $this->db
							->query(
									'UPDATE customer_safety SET customer_name="'
											. $this->customer_name
											. '",safety=' . $this->safety
											. ',tmpsafety='
											. (empty($this->tmpsafety) ? 0
													: $this->tmpsafety)
											. ',tmpsafety_deadline='
											. (empty($this->tmpsafety_deadline) ? 'NULL'
													: '"'
															. $this
																	->tmpsafety_deadline
															. '"')
											. ' WHERE id='
											. intval($this->customer_id));
					if ($update_result === FALSE) {
						$success = FALSE;
						$error = '修改系统客户失败';
					}
				}

				if ($success) {
					$this->db->query('COMMIT');
				} else {
					$this->db->query('ROLLBACK');
				}

				return array('status' => $success ? 'success' : 'error',
						'message' => $success ? '修改系统客户成功' : $error);
			}
			return array('status' => 'error', 'message' => $this->errors);
		}
		return User::no_permission();
	}

	public function get_search_cusname_html() {
		if ($this->getHas_manager_customer_safety_permission()) {
			$results = $this->db
					->get_results(
							'SELECT DISTINCT(cusname) AS cusname FROM contract_cus WHERE isok=1 AND cusname LIKE "%'
									. $this->search
									. '%" AND NOT EXISTS (SELECT cusname FROM v_customer_cusname WHERE v_customer_cusname.cusname IS NOT NULL AND v_customer_cusname.cusname=contract_cus.cusname)');
			if ($results === NULL) {
				return '没有符合条件的结果';
			} else {
				$s = '<table>';
				foreach ($results as $key => $result) {
					if ($key === 0 || $key % 4 === 0) {
						$s .= '<tr>';
					}
					$s .= '<td><input type="checkbox" name="cusname[]" value="'
							. $result->cusname . '" checked="checked">&nbsp;'
							. $result->cusname . '</td>';
					if ($key % 4 === 3) {
						$s .= '</tr>';
					}
				}
				$s .= '</table>';
				return $s;
			}
		}
		return NO_RIGHT_TO_DO_THIS;
	}

	public function relate_customer() {
		if ($this->getHas_manager_customer_safety_permission()) {
			if ($this->validate_form_value('relate')) {
				$success = TRUE;
				$error = '';
				$this->db->query('BEGIN');

				$delete_result = $this->db
						->query(
								'DELETE FROM customer_cusname WHERE customer_id='
										. intval($this->customer_id));
				if ($delete_result === FALSE) {
					$success = FALSE;
					$error = '系统客户关联OA客户失败，错误代码1';
				} else {
					if (!empty($this->cusnames_array)) {
						$sub_sql = array();
						foreach ($this->cusnames_array as $cus) {
							$sub_sql[] = '(' . intval($this->customer_id)
									. ',"' . $cus . '")';
						}
						$insert_result = $this->db
								->query(
										'INSERT INTO customer_cusname(customer_id,cusname) VALUES'
												. implode(',', $sub_sql));
						if ($insert_result === FALSE) {
							$success = FALSE;
							$error = '系统客户关联OA客户失败，错误代码2';
						}
					}
				}

				if ($success) {
					$this->db->query('COMMIT');
				} else {
					$this->db->query('ROLLBACK');
				}

				return array('status' => $success ? 'success' : 'error',
						'message' => $success ? '系统客户关联OA客户成功' : $error);
			}
			return array('status' => 'error', 'message' => $this->errors);
		}
		return User::no_permission();
	}

	private function _check_format($line, $infos, &$errors) {
		$isok = TRUE;
		//第一列 系统客户名称
		$customer_id = $this->db
				->get_var(
						'SELECT customer_id FROM v_customer_cusname WHERE customer_name="'
								. $infos[0] . '"');
		if ($customer_id === NULL) {
			$errors[] = '第' . $line . '行，第1列【系统客户名称】不存在';
			$isok = $isok ? FALSE : $isok;
		}

		//第二列 OA客户名称
		$cusname = $this->db
				->get_row(
						'SELECT cusname FROM contract_cus WHERE cusname="'
								. $infos[1] . '" AND isok=1');
		if ($cusname === NULL) {
			$errors[] = '第' . $line . '行，第2列【OA客户名称】不存在';
			$isok = $isok ? FALSE : $isok;
		} else {
			$row = $this->db
					->get_row(
							'SELECT customer_id FROM v_customer_cusname WHERE cusname="'
									. $infos[1] . '"');
			if ($row !== NULL) {
				$errors[] = '第' . $line . '行，第2列【OA客户名称】已与某系统客户名称关联';
				$isok = $isok ? FALSE : $isok;
			}
		}

		return $isok;
	}

	public function import_customer($file) {
		if ($this->getHas_manager_customer_safety_permission()) {
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
				//应该是2列，行数>1
				if ($sum_cols_count !== 2) {
					$errors[] = '上传文件的列数非有效';
				} else if ($sum_rows_count <= 1) {
					$errors[] = '上传文件的行数非有效';
				} else {

					for ($i = 2; $i <= $sum_rows_count; $i++) {
						for ($j = 0; $j < $sum_cols_count; $j++) {
							$infos[$i][$j] = $sheet
									->getCellByColumnAndRow($j, $i)
									->getCalculatedValue();
							$infos[$i][$j] = $infos[$i][$j] === NULL ? NULL
									: trim($infos[$i][$j]);
						}
						$this->db->query('BEGIN');
						$isok = $this->_check_format($i, $infos[$i], $errors);
						if ($isok) {
							$db_ok = TRUE;

							$result = $this->db
									->query(
											'INSERT INTO customer_cusname(customer_id,cusname) VALUE((SELECT id FROM customer_safety WHERE customer_name="'
													. $infos[$i][0]
													. '" AND isok=1),"'
													. $infos[$i][1] . '")');
							if ($result === FALSE) {
								$db_ok = FALSE;
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
		} else {
			return User::no_permission();
		}
	}

	public function get_cusname_belong($cusname) {
		$customer_id = $this->db
				->get_var(
						'SELECT customer_id FROM customer_cusname WHERE cusname="'
								. $cusname . '"');
		if ($customer_id > 0) {
			return $customer_id;
		}
		return -1;
	}

	private static function _get_safety($amount, $receivable) {
		if ($receivable === NULL) {
			$receivable = 0;
		}
		if ($amount - $receivable >= 0) {
			return $amount - $receivable;
		} else {
			return 0;
		}
	}

	//计算剩余保险额度
	public function compute_remainder_safety($pid = NULL) {
		$result = $this->compute_used_safety($pid);
		return $result['safety'] - $result['used'];
	}

	//计算已使用保险额度
	public function compute_used_safety($pid = NULL) {
		$used = 0;
		$safety = 0;
		$query = 'SELECT pid,amount,invoice,receivable,safety,tmpsafety,tmpsafety_deadline FROM v_final_data WHERE customer_id='
				. intval($this->customer_id) . ' AND company=3';	//只计算新网迈
		if ($pid !== NULL) {
			$query .= ' AND pid<>"' . $pid . '"';
		}
		$results = $this->db->get_results($query);
		if ($results !== NULL) {
			foreach ($results as $key => $result) {
				if ($key === 0) {
					$safety = $result->safety;
					$tmpsafety = $result->tmpsafety;
					$tmpsafety_deadline = $result->tmpsafety_deadline;
					if($tmpsafety > 0 && $tmpsafety_deadline !== NULL){
						if(strtotime($tmpsafety_deadline . ' 23:59:59')>=time()){
							$safety += $tmpsafety;
						}
					}
				}
				$used += self::_get_safety($result->amount, $result->receivable);
			}
		}
		return array('safety' => $safety, 'used' => $used);
	}
}
