<?php
class Media_Cost extends User {
	private $filename;
	private $pid;
	private $executive_id;
	private $media = array();
	private $errors = array();
	private $name_passed = FALSE;
	private static $validate_names = array('媒介计划采购成本核算表');

	public function __construct($upload_message, $pid = NULL, $executive_id = NULL) {
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
			}
		}
	}

	public function check_format() {
		$errors = array();
		$infos = array();
		$media = array();
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
			$media['item_title'] = $infos[1][0];
		}

		//项目名称：不能为空并且最多255个字符
		if (!self::validate_field_not_empty($infos[3][1])
				|| !self::validate_field_not_null($infos[3][1])) {
			$errors[] = '第3行，第2列，【项目名称】不能为空';
		} else if (!self::validate_field_max_length($infos[3][1], 255)) {
			$errors[] = '第3行，第2列，【项目名称】最多255个字符';
		} else {
			$media['item_name'] = $infos[3][1];
		}

		//项目期限：不能为空并且最多255个字符
		if (!self::validate_field_not_empty($infos[4][1])
				|| !self::validate_field_not_null($infos[4][1])) {
			$errors[] = '第4行，第2列，【项目期限】不能为空';
		} else if (!self::validate_field_max_length($infos[4][1], 255)) {
			$errors[] = '第4行，第2列，【项目期限】最多255个字符';
		} else {
			$media['item_deadline'] = $infos[4][1];
		}

		//统计时间：不能为空并且最多255个字符
		if (!self::validate_field_not_empty($infos[5][1])
				|| !self::validate_field_not_null($infos[5][1])) {
			$errors[] = '第5行，第2列，【统计时间】不能为空';
		} else if (!self::validate_field_max_length($infos[5][1], 255)) {
			$errors[] = '第5行，第2列，【统计时间】最多255个字符';
		} else {
			$media['statistics_time'] = $infos[5][1];
		}

		//执行单号：可为空，但不空时最多255个字符
		if (self::validate_field_not_empty($infos[6][1])) {
			if ($infos[6][1] !== $this->pid) {
				$errors[] = '第6行，第2列，【执行单号】与实际执行单号不一致';
			} else if (!self::validate_field_max_length($infos[6][1], 50)) {
				$errors[] = '第6行，第2列，【执行单号】最多50个字符';
			} else {
				$media['pid'] = $infos[6][1];
			}
		}

		//客户进款时间：不能为空并且最多255个字符
		if (!self::validate_field_not_empty($infos[7][1])
				|| !self::validate_field_not_null($infos[7][1])) {
			$errors[] = '第7行，第2列，【客户进款时间】不能为空';
		} else if (!self::validate_field_max_length($infos[7][1], 255)) {
			$errors[] = '第7行，第2列，【客户进款时间】最多255个字符';
		} else {
			$media['customer_taking_time'] = $infos[7][1];
		}

		//第10行开始循环数据校验
		for ($x = 10; $x <= $sum_rows_count; $x++) {
			if ($infos[$x][0] === '总计') {
				//校验结束
				break;
			}
			$items = array();

			//媒体选择
			if (!self::validate_field_not_empty($infos[$x][0])
					|| !self::validate_field_not_null($infos[$x][0])) {
				$errors[] = '第' . $x . '行，第1列，【媒体选择】不能为空';
			} else if (!self::validate_field_max_length($infos[$x][0], 255)) {
				$errors[] = '第' . $x . '行，第1列，【媒体选择】最多255个字符';
			} else {
				$items['media_choice'] = $infos[$x][0];
			}

			//媒体全称
			if (!self::validate_field_not_empty($infos[$x][1])
					|| !self::validate_field_not_null($infos[$x][1])) {
				$errors[] = '第' . $x . '行，第2列，【媒体全称】不能为空';
			} else if (!self::validate_field_max_length($infos[$x][1], 100)) {
				$errors[] = '第' . $x . '行，第2列，【媒体全称】最多100个字符';
			} else {
				$items['media_name'] = $infos[$x][1];
			}

			//媒体网址
			if (!self::validate_field_not_empty($infos[$x][2])
					|| !self::validate_field_not_null($infos[$x][2])) {
				$errors[] = '第' . $x . '行，第3列，【媒体网址】不能为空';
			} else if (!self::validate_field_max_length($infos[$x][2], 1024)) {
				$errors[] = '第' . $x . '行，第3列，【媒体网址】最多1024个字符';
			} else {
				$items['media_url'] = $infos[$x][2];
			}

			//投放类型
			if (!self::validate_field_not_empty($infos[$x][3])
					|| !self::validate_field_not_null($infos[$x][3])) {
				$errors[] = '第' . $x . '行，第4列，【投放类型】不能为空';
			} else if (!self::validate_field_max_length($infos[$x][3], 255)) {
				$errors[] = '第' . $x . '行，第4列，【投放类型】最多255个字符';
			} else {
				$items['media_puton_type'] = $infos[$x][3];
			}

			//账户信息
			if (!self::validate_field_not_empty($infos[$x][4])
					|| !self::validate_field_not_null($infos[$x][4])) {
				$errors[] = '第' . $x . '行，第5列，【账户信息】不能为空';
			} else if (!self::validate_field_max_length($infos[$x][4], 1000)) {
				$errors[] = '第' . $x . '行，第5列，【账户信息】最多1000个字符';
			} else {
				$items['media_account_info'] = $infos[$x][4];
			}

			//媒体投放时间
			if (!self::validate_field_not_empty($infos[$x][5])
					|| !self::validate_field_not_null($infos[$x][5])) {
				$errors[] = '第' . $x . '行，第6列，【媒体投放时间】不能为空';
			} else if (!self::validate_field_max_length($infos[$x][5], 255)) {
				$errors[] = '第' . $x . '行，第6列，【媒体投放时间】最多255个字符';
			} else {
				$items['media_puton_time'] = $infos[$x][5];
			}

			//媒体付款时间
			if (!self::validate_field_not_empty($infos[$x][6])
					|| !self::validate_field_not_null($infos[$x][6])) {
				$errors[] = '第' . $x . '行，第7列，【媒体付款时间】不能为空';
			} else if (!self::validate_date_int(
					PHPExcel_Shared_Date::ExcelToPHP($infos[$x][6]))) {
				$errors[] = '第' . $x . '行，第7列，【媒体付款时间】不是有效的时间值';
			} else {
				$items['media_paytime'] = PHPExcel_Shared_Date::ExcelToPHP(
						$infos[$x][6]);
			}

			//媒体成本（合计）
			if (!self::validate_field_not_null($infos[$x][7])) {
				$errors[] = '第' . $x . '行，第8列，【媒体成本（合计）】不能为空';
			} else if (!self::validate_money($infos[$x][7])) {
				$errors[] = '第' . $x . '行，第8列，【媒体成本（合计）】不是有效的金额值';
			} else {
				$items['cost'] = $infos[$x][7];
			}

			//客户报价（合计）
			if (!self::validate_field_not_null($infos[$x][8])) {
				$errors[] = '第' . $x . '行，第9列，【客户报价（合计）】不能为空';
			} else if (!self::validate_money($infos[$x][8])) {
				$errors[] = '第' . $x . '行，第9列，【客户报价（合计）】不是有效的金额值';
			} else {
				$items['quote'] = $infos[$x][8];
			}

			$cy_quote = array();
			$cy_cost = array();
			$k = 0;
			for ($y = 9; $y < ($sum_cols_count - 1); $y++) {
				if (!self::validate_field_not_null($infos[$x][$y])) {
					$errors[] = '第' . $x . '行，第' . ($y + 1) . '列，【'
							. $infos[9][$y] . '】不能为空';
				} else if (!self::validate_money($infos[$x][$y])) {
					$errors[] = '第' . $x . '行，第' . ($y + 1) . '列，【'
							. $infos[9][$y] . '】不是有效的金额值';
				} else {
					$ym = Outsource_Cost::get_year_month($infos[9][$y]);
					if ($ym['status'] === 'error') {
						$errors[] = '第9行，第' . ($y + 1) . '列，' . $ym['message'];
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
				$errors[] = '第' . $x . '行，【媒体成本（合计）】与拆月数据之和不符';
			} else if (floatval($items['quote'])
					!== floatval(array_sum($items['cy_quote']))) {
				$errors[] = '第' . $x . '行，【客户报价（合计）】与拆月数据之和不符';
			}

			$media['items'][] = $items;
		}

		if (!empty($errors)) {
			$this->errors = $errors;
			unset($media);
			unset($errors);
			return FALSE;
		}

		$this->media = $media;
		return TRUE;
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

		if (!empty($this->media)) {
			$this->db->query('BEGIN');

			$media = $this->media;
			$insert_result = $this->db
					->query(
							'INSERT INTO media_purchase_cost_item(item_title,item_name,item_deadline,statistics_time,executive_id,pid,customer_taking_time,adduser,addtime,filename) VALUE("'
									. $media['item_title'] . '","'
									. $media['item_name'] . '","'
									. $media['item_deadline'] . '","'
									. $media['statistics_time'] . '",'
									. intval($this->executive_id) . ',"'
									. $this->pid . '","'
									. $media['customer_taking_time'] . '",'
									. $this->getUid() . ','
									. $_SERVER['REQUEST_TIME'] . ',"'
									. str_replace(UPLOAD_FILE_PATH, '',
											$this->filename) . '")');

			if ($insert_result === FALSE) {
				$error = '导入媒介计划采购成本信息出错，错误代码1';
				$success = FALSE;
			} else {
				$item_id = intval($this->db->insert_id);
				$items = $media['items'];
				foreach ($items as $item) {
					$insert_result = $this->db
							->query(
									'INSERT INTO media_purchase_cost_content(media_item_id,media_choice,media_name,media_url,media_puton_type,media_account_info,media_puton_time,media_paytime,cost,quote) VALUE('
											. $item_id . ',"'
											. $item['media_choice'] . '","'
											. $item['media_name'] . '","'
											. $item['media_url'] . '","'
											. $item['media_puton_type'] . '","'
											. $item['media_account_info']
											. '","' . $item['media_puton_time']
											. '",' . $item['media_paytime']
											. ',' . $item['cost'] . ','
											. $item['quote'] . ')');

					if ($insert_result === FALSE) {
						$error = '导入媒介计划采购成本信息出错，错误代码2';
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
											'INSERT INTO media_purchase_cost_cy(media_item_id,media_content_id,amount_type,year,month,ym,amount) VALUES'
													. implode(',', $sql));
							if ($insert_result === FALSE) {
								$error = '导入媒介计划采购成本信息出错，错误代码3';
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
											'INSERT INTO media_purchase_cost_cy(media_item_id,media_content_id,amount_type,year,month,ym,amount) VALUES'
													. implode(',', $sql2));
							if ($insert_result === FALSE) {
								$error = '导入媒介计划采购成本信息出错，错误代码4';
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
					'message' => $success ? '导入媒介计划采购成本信息成功' : $error);
		}
		return array('status' => 'error', 'message' => '媒介计划采购成本信息不能为空');
	}
}
