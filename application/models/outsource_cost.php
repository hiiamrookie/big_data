<?php
class Outsource_Cost extends User {
	private $filename;
	private $pid;
	private $executive_id;
	private $outsource = array();
	private $errors = array();
	private $has_quote = FALSE;
	private $name_passed = FALSE;
	private static $validate_names = array('外包成本核算表');

	public function getName_passed() {
		return $this->name_passed;
	}

	public function __construct($upload_message, $pid = NULL,
			$executive_id = NULL) {
		if (file_exists(UPLOAD_FILE_PATH . $upload_message->file_name)) {
			$base_name = explode('_',
					pathinfo($upload_message->file_realname, PATHINFO_BASENAME));
			foreach (self::$validate_names as $vname) {
				if (strpos(reset($base_name), $vname) !== FALSE) {
					$this->name_passed = TRUE;
					break;
				}
			}
			if ($this->name_passed && $pid !== NULL && $executive_id !== NULL) {
				parent::__construct();
				$this->pid = $pid;
				$this->executive_id = $executive_id;
				$this->filename = UPLOAD_FILE_PATH . $upload_message->file_name;
				if (strpos(reset($base_name), '含报价') !== FALSE) {
					$this->has_quote = TRUE;
				}
			}
		}
	}

	public function check_format() {
		$errors = array();
		$infos = array();
		$outsource = array();
		if ($this->filename === NULL || !file_exists($this->filename)) {
			$errors[] = '文件不存在';
			$this->errors = $errors;
			return FALSE;
		}
		$PHPExcel = new PHPExcel();
		if (strtolower(pathinfo($this->filename, PATHINFO_EXTENSION)) === 'xls') {
			$PHPReader = new PHPExcel_Reader_Excel5();
		} else if (strtolower(pathinfo($this->filename, PATHINFO_EXTENSION))
				=== 'xlsx') {
			$PHPReader = new PHPExcel_Reader_Excel2007();
		}

		$PHPExcel = $PHPReader->load($this->filename);
		$PHPExcel->setActiveSheetIndex(0);
		$sheet = $PHPExcel->getActiveSheet();
		$sum_cols_count = PHPExcel_Cell::columnIndexFromString(
				$sheet->getHighestColumn());
		$sum_rows_count = $sheet->getHighestRow();

		for ($i = 1; $i <= $sum_rows_count; $i++) {
			for ($j = 0; $j < $sum_cols_count; $j++) {
				$infos[$i][$j] = String_Util::my_trim(
						$sheet->getCellByColumnAndRow($j, $i)
								->getCalculatedValue());
			}
		}

		//文件标题：不能为空并且最多255个字符
		if (!self::validate_field_not_empty($infos[1][0])
				|| !self::validate_field_not_null($infos[1][0])) {
			$errors[] = '第1行，第1列，【文件标题】不能为空';
		} else if (!self::validate_field_max_length($infos[1][0], 255)) {
			$errors[] = '第1行，第1列，【文件标题】最多255个字符';
		} else {
			$outsource['item_title'] = $infos[1][0];
		}

		//项目名称：不能为空并且最多255个字符
		if (!self::validate_field_not_empty($infos[3][1])
				|| !self::validate_field_not_null($infos[3][1])) {
			$errors[] = '第3行，第2列，【项目名称】不能为空';
		} else if (!self::validate_field_max_length($infos[3][1], 255)) {
			$errors[] = '第3行，第2列，【项目名称】最多255个字符';
		} else {
			$outsource['item_name'] = $infos[3][1];
		}

		//项目期限：不能为空并且最多255个字符
		if (!self::validate_field_not_empty($infos[4][1])
				|| !self::validate_field_not_null($infos[4][1])) {
			$errors[] = '第4行，第2列，【项目期限】不能为空';
		} else if (!self::validate_field_max_length($infos[4][1], 255)) {
			$errors[] = '第4行，第2列，【项目期限】最多255个字符';
		} else {
			$outsource['item_deadline'] = $infos[4][1];
		}

		//统计时间：不能为空并且最多255个字符
		if (!self::validate_field_not_empty($infos[5][1])
				|| !self::validate_field_not_null($infos[5][1])) {
			$errors[] = '第5行，第2列，【统计时间】不能为空';
		} else if (!self::validate_field_max_length($infos[5][1], 255)) {
			$errors[] = '第5行，第2列，【统计时间】最多255个字符';
		} else {
			$outsource['statistics_time'] = $infos[5][1];
		}

		//执行单号：可为空，但不空时最多255个字符
		if (self::validate_field_not_empty($infos[6][1])) {
			if ($infos[6][1] !== $this->pid) {
				$errors[] = '第6行，第2列，【执行单号】与实际执行单号不一致';
			} else if (!self::validate_field_max_length($infos[6][1], 50)) {
				$errors[] = '第6行，第2列，【执行单号】最多50个字符';
			} else {
				$outsource['pid'] = $infos[6][1];
			}
		}

		//第9行开始循环数据校验
		for ($x = 9; $x <= $sum_rows_count; $x++) {
			if ($infos[$x][0] === '总计') {
				//校验结束
				break;
			}
			$items = array();
			//外包全称
			if (!self::validate_field_not_empty($infos[$x][0])
					|| !self::validate_field_not_null($infos[$x][0])) {
				$errors[] = '第' . $x . '行，第1列，【外包全称】不能为空';
			} else if (!self::validate_field_max_length($infos[$x][0], 100)) {
				$errors[] = '第' . $x . '行，第1列，【外包全称】最多100个字符';
			} else {
				$items['outsource_name'] = $infos[$x][0];
			}

			//外包付款时间
			if (!self::validate_field_not_empty($infos[$x][1])
					|| !self::validate_field_not_null($infos[$x][1])) {
				$errors[] = '第' . $x . '行，第2列，【外包付款时间】不能为空';
			} else if (!self::validate_date_int(
					PHPExcel_Shared_Date::ExcelToPHP($infos[$x][1]))) {
				$errors[] = '第' . $x . '行，第2列，【外包付款时间】不是有效的时间值';
			} else {
				$items['outsource_paytime'] = PHPExcel_Shared_Date::ExcelToPHP(
						$infos[$x][1]);
			}

			//工作内容
			if (!self::validate_field_not_empty($infos[$x][2])
					|| !self::validate_field_not_null($infos[$x][2])) {
				$errors[] = '第' . $x . '行，第3列，【工作内容】不能为空';
			} else if (!self::validate_field_max_length($infos[$x][2], 5000)) {
				$errors[] = '第' . $x . '行，第3列，【工作内容】最多5000个字符';
			} else {
				$items['work_content'] = $infos[$x][2];
			}

			//是否预估
			if (!in_array($infos[$x][3], array('是', '否'), TRUE)) {
				$errors[] = '第' . $x . '行，第4列，【是否预估】输入有误，请输入“是”或者“否”';
			} else {
				$items['isyg'] = $infos[$x][3];
			}

			//数量
			if (!self::validate_field_not_null($infos[$x][4])) {
				$errors[] = '第' . $x . '行，第5列，【数量】不能为空';
			} else if (!self::validate_money($infos[$x][4])) {
				$errors[] = '第' . $x . '行，第5列，【数量】不是有效的金额值';
			} else {
				$items['number'] = $infos[$x][4];
			}

			//单价
			if (!self::validate_field_not_null($infos[$x][5])) {
				$errors[] = '第' . $x . '行，第6列，【单价】不能为空';
			} else if (!self::validate_money($infos[$x][5])) {
				$errors[] = '第' . $x . '行，第6列，【单价】不是有效的金额值';
			} else {
				$items['unit_price'] = $infos[$x][5];
			}

			//成本（合计）
			if (!self::validate_field_not_null($infos[$x][6])) {
				$errors[] = '第' . $x . '行，第7列，【成本（合计）】不能为空';
			} else if (!self::validate_money($infos[$x][6])) {
				$errors[] = '第' . $x . '行，第7列，【成本（合计）】不是有效的金额值';
			} else {
				$items['cost'] = $infos[$x][6];
			}

			//报价（合计）
			$items['quote'] = 0;
			if ($this->has_quote) {
				if (!self::validate_field_not_null($infos[$x][7])) {
					$errors[] = '第' . $x . '行，第8列，【报价（合计）】不能为空';
				} else if (!self::validate_money($infos[$x][7])) {
					$errors[] = '第' . $x . '行，第8列，【报价（合计）】不是有效的金额值';
				} else {
					$items['quote'] = $infos[$x][7];
				}
			}

			$cy_quote = array();
			$cy_cost = array();
			$k = 0;
			for ($y = ($this->has_quote ? 8 : 7); $y < ($sum_cols_count - 1); $y++) {
				if (!self::validate_field_not_null($infos[$x][$y])) {
					$errors[] = '第' . $x . '行，第' . ($y + 1) . '列，【'
							. $infos[8][$y] . '】不能为空';
				} else if (!self::validate_money($infos[$x][$y])) {
					$errors[] = '第' . $x . '行，第' . ($y + 1) . '列，【'
							. $infos[8][$y] . '】不是有效的金额值';
				} else {
					$ym = self::get_year_month($infos[8][$y]);
					if ($ym['status'] === 'error') {
						$errors[] = '第8行，第' . ($y + 1) . '列，' . $ym['message'];
					} else if ($ym['message']['type'] === 'cost') {
						$cy_cost[$ym['message']['ym']] = $infos[$x][$y];
					} else if ($ym['message']['type'] === 'quote') {
						$cy_quote[$ym['message']['ym']] = $infos[$x][$y];
					}
				}
				$k++;
			}
			$items['cy_quote'] = $cy_quote;
			$items['cy_cost'] = $cy_cost;

			//校验合计与拆月数据总合是否相同
			if (floatval($items['cost'])
					!== floatval(array_sum($items['cy_cost']))) {
				$errors[] = '第' . $x . '行，【成本（合计）】与拆月数据之和不符';
			} else if (floatval($items['quote'])
					!== floatval(array_sum($items['cy_quote']))) {
				$errors[] = '第' . $x . '行，【报价（合计）】与拆月数据之和不符';
			}

			//备注
			if (self::validate_field_not_null($infos[$x][$sum_cols_count - 1])
					&& !self::validate_field_max_length(
							$infos[$x][$sum_cols_count - 1], 1000)) {
				$errors[] = '第' . $x . '行，第' . $sum_cols_count
						. '列，【备注】最多1000个字符';
			} else {
				$items['remark'] = $infos[$x][$sum_cols_count - 1];
			}

			$outsource['items'][] = $items;
		}

		if (!empty($errors)) {
			$this->errors = $errors;
			unset($outsource);
			unset($errors);
			return FALSE;
		}

		$this->outsource = $outsource;
		return TRUE;
	}

	private static function _isyg($val) {
		if (!in_array($val, array('是', '否'), TRUE)) {
			return 0;
		}
		return $val === '是' ? 1 : 0;
	}

	public static function get_year_month($title) {
		$title = explode('(',
				str_replace(array('（', '）', ')'), array('(', '', ''), $title));
		if (count($title) !== 2) {
			return array('status' => 'error', 'message' => '标题格式有误');
		} else if (!in_array($title[0], array('成本', '报价'), TRUE)) {
			return array('status' => 'error', 'message' => '成本或报价标识有误');
		}
		$ym = str_replace(array('年', '月', '/'), array('-', '', '-'), $title[1]);
		return array('status' => 'success',
				'message' => array(
						'type' => ($title[0] === '成本' ? 'cost' : 'quote'),
						'ym' => $ym));
	}

	public function import() {
		$error = '';
		$success = TRUE;
		if ($this->filename === NULL) {
			return array('status' => 'error', 'message' => '文件不存在');
		}
		if (!$this->check_format()) {
			return array('status' => 'error', 'message' => $this->errors);
		}

		if (!empty($this->outsource)) {
			$this->db->query('BEGIN');

			$outsource = $this->outsource;
			$insert_result = $this->db
					->query(
							'INSERT INTO outsource_item(item_title,item_name,item_deadline,statistics_time,executive_id,pid,adduser,addtime,filename,has_quote) VALUE("'
									. $outsource['item_title'] . '","'
									. $outsource['item_name'] . '","'
									. $outsource['item_deadline'] . '","'
									. $outsource['statistics_time'] . '",'
									. intval($this->executive_id) . ',"'
									. $this->pid . '",' . $this->getUid() . ','
									. $_SERVER['REQUEST_TIME'] . ',"'
									. str_replace(UPLOAD_FILE_PATH, '',
											$this->filename) . '",'
									. ($this->has_quote ? 1 : 0) . ')');
			if ($insert_result === FALSE) {
				$error = '导入外包信息出错，错误代码1';
				$success = FALSE;
			} else {
				$item_id = intval($this->db->insert_id);
				$items = $outsource['items'];
				foreach ($items as $item) {
					$insert_result = $this->db
							->query(
									'INSERT INTO outsource_content(outsource_item_id,outsource_name,outsource_paytime,work_content,isyg,number,unit_price,cost,quote,remark) VALUE('
											. $item_id . ',"'
											. $item['outsource_name'] . '",'
											. $item['outsource_paytime'] . ',"'
											. $item['work_content'] . '",'
											. self::_isyg($item['isyg']) . ','
											. $item['number'] . ','
											. $item['unit_price'] . ','
											. $item['cost'] . ','
											. $item['quote'] . ',"'
											. $item['remark'] . '")');
					if ($insert_result === FALSE) {
						$error = '导入外包信息出错，错误代码2';
						$success = FALSE;
						break;
					} else {
						$content_id = intval($this->db->insert_id);
						$_cy_quotes = $item['cy_quote'];
						$_cy_costs = $item['cy_cost'];
						$sql = array();
						foreach ($_cy_quotes as $key => $_cy_quote) {
							$sql[] = '(' . $item_id . ',' . $content_id . ',1,'
									. intval(reset(explode('-', $key))) . ','
									. intval(end(explode('-', $key))) . ',"'
									. $key . '",' . $_cy_quote . ')';
						}
						if (!empty($sql)) {
							$insert_result = $this->db
									->query(
											'INSERT INTO outsource_cy(outsource_item_id,outsource_content_id,amount_type,year,month,ym,amount) VALUES'
													. implode(',', $sql));
							if ($insert_result === FALSE) {
								$error = '导入外包信息出错，错误代码3';
								$success = FALSE;
								break;
							}
						}

						$sql2 = array();
						foreach ($_cy_costs as $key => $_cy_cost) {
							$sql2[] = '(' . $item_id . ',' . $content_id
									. ',0,' . intval(reset(explode('-', $key)))
									. ',' . intval(end(explode('-', $key)))
									. ',"' . $key . '",' . $_cy_cost . ')';
						}
						if (!empty($sql2)) {
							$insert_result = $this->db
									->query(
											'INSERT INTO outsource_cy(outsource_item_id,outsource_content_id,amount_type,year,month,ym,amount) VALUES'
													. implode(',', $sql2));
							if ($insert_result === FALSE) {
								$error = '导入外包信息出错，错误代码4';
								$success = FALSE;
								break;
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
					'message' => $success ? '导入外包信息成功' : $error);
		}
		return array('status' => 'error', 'message' => '外包信息不能为空');
	}
}
