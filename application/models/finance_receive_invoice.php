<?php
class Finance_Receive_Invoice extends User {
	private $sourceid;
	private $taxpayer_number;
	private $media_name;
	private $invoice_content;
	private $invoice_number;
	private $tax_rate;
	private $amount;
	private $invoice_date;
	private $belong_month;
	private $has_finance_receive_invoice_permission = FALSE;
	private $errors = array();
	private $actype;
	private $ids;

	private $type;
	private $page;
	private $search;
	private $cusname;
	private $medianame;

	private $paymentdate;
	private $payment_plan;
	private $payment_real;

	const EXE_LIMIT = 10;

	private $pids_array = array();
	private $pids_sumamount = array();
	private $itemids_array = array();

	private $shareid;
	private $id;

	public function __construct($fields = array()) {
		parent::__construct();
		if (!empty($fields)) {
			foreach ($this as $key => $value) {
				if ($fields[$key] !== NULL
						&& !in_array($key,
								array('has_finance_receive_invoice_permission'),
								TRUE)) {
					$this->$key = $fields[$key];
				}
			}
		}

		if (in_array($this->getUsername(),
				$GLOBALS['manager_finance_permission'], TRUE)
				|| intval($this->getBelong_dep()) === 2) {
			$this->has_finance_receive_invoice_permission = TRUE;
		}
	}

	public function get_import_receive_invoice_html() {
		if ($this->has_finance_receive_invoice_permission) {
			$buf = file_get_contents(
					TEMPLATE_PATH
							. 'finance/receiveinvoice/finance_receive_invoice_import.tpl');
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
		//第一列 纳税人识别号
		if (!self::validate_field_not_null($infos[0])
				|| !self::validate_field_not_empty($infos[0])) {
			$errors[] = '第' . $line . '行，第1列【纳税人识别号】不能为空';
			$isok = $isok ? FALSE : $isok;
		} else if (!Validate_Util::my_is_numeric($infos[0])) {
			$errors[] = '第' . $line . '行，第1列【纳税人识别号】不是有效的纳税人识别号';
			$isok = $isok ? FALSE : $isok;
		} else if (!self::validate_field_max_length($infos[0], 255)) {
			$errors[] = '第' . $line . '行，第1列【纳税人识别号】长度最多255个字符';
			$isok = $isok ? FALSE : $isok;
		}

		//第二列 媒体名称
		if (!self::validate_field_not_null($infos[1])) {
			$errors[] = '第' . $line . '行，第2列【媒体名称】不能为空';
			$isok = $isok ? FALSE : $isok;
		} else if (!self::validate_field_max_length($infos[1], 255)) {
			$errors[] = '第' . $line . '行，第2列【媒体名称】长度最多255个字符';
			$isok = $isok ? FALSE : $isok;
		}

		//第三列 发票内容
		/*
		if (!self::validate_field_not_null($infos[2])
		        || !self::validate_field_not_empty($infos[2])) {
		    $errors[] = '第' . $line . '行，第3列【发票内容】不能为空';
		    $isok = $isok ? FALSE : $isok;
		} else if (!self::validate_field_max_length($infos[2], 255)) {
		    $errors[] = '第' . $line . '行，第3列【发票内容】长度最多255个字符';
		    $isok = $isok ? FALSE : $isok;
		}
		 */
		if (!in_array($infos[2], array('广告', '服务'), TRUE)) {
			$errors[] = '第' . $line . '行，第3列【发票内容】只可以选择“广告”或者“服务”';
			$isok = $isok ? FALSE : $isok;
		}

		//第四列 凭证号码
		if (!self::validate_field_not_null($infos[3])
				|| !self::validate_field_not_empty($infos[3])) {
			$errors[] = '第' . $line . '行，第4列【凭证号码】不能为空';
			$isok = $isok ? FALSE : $isok;
		} else if (!Validate_Util::my_is_numeric($infos[3])) {
			$errors[] = '第' . $line . '行，第4列【凭证号码】不是有效的凭证号码';
			$isok = $isok ? FALSE : $isok;
		} else if (!self::validate_field_max_length($infos[3], 255)) {
			$errors[] = '第' . $line . '行，第4列【凭证号码】长度最多255个字符';
			$isok = $isok ? FALSE : $isok;
		} else {
			$id = $this->db
					->get_var(
							'SELECT id FROM finance_receiveinvoice_source WHERE invoice_number="'
									. $infos[3] . '"');
			if ($id > 0) {
				$errors[] = '第' . $line . '行，第4列【凭证号码】已存在';
				$isok = $isok ? FALSE : $isok;
			}
		}

		//第五列 税率
		if (!self::validate_field_not_null($infos[4])) {
			$errors[] = '第' . $line . '行，第5列【税率】不能为空';
			$isok = $isok ? FALSE : $isok;
		} else if (!Validate_Util::my_is_float($infos[4])) {
			$errors[] = '第' . $line . '行，第5列【税率】不是有效的数值';
			$isok = $isok ? FALSE : $isok;
		} else if ($infos[4] > 100 || $infos[4] < 0) {
			$errors[] = '第' . $line . '行，第5列【税率】不是有效的数值';
			$isok = $isok ? FALSE : $isok;
		}

		//第六列 成本
		if (!self::validate_field_not_null($infos[5])) {
			$errors[] = '第' . $line . '行，第6列【成本】不能为空';
			$isok = $isok ? FALSE : $isok;
		} else if (!self::validate_money($infos[5])) {
			$errors[] = '第' . $line . '行，第6列【成本】不是有效的金额';
			$isok = $isok ? FALSE : $isok;
		}

		//第七列 进项
		if (!self::validate_field_not_null($infos[6])) {
			$errors[] = '第' . $line . '行，第7列【进项】不能为空';
			$isok = $isok ? FALSE : $isok;
		} else if (!self::validate_money($infos[6])) {
			$errors[] = '第' . $line . '行，第7列【进项】不是有效的金额';
			$isok = $isok ? FALSE : $isok;
		} else if (round($infos[4] * $infos[5] / 100, 2)
				!== floatval($infos[6])) {
			$errors[] = '第' . $line . '行，第7列【进项】不是有效的金额';
			$isok = $isok ? FALSE : $isok;
		}

		//第八列 价税合计金额
		if (!self::validate_field_not_null($infos[7])) {
			$errors[] = '第' . $line . '行，第8列【价税合计金额】不能为空';
			$isok = $isok ? FALSE : $isok;
		} else if (!self::validate_money($infos[7])) {
			$errors[] = '第' . $line . '行，第8列【价税合计金额】不是有效的金额';
			$isok = $isok ? FALSE : $isok;
		} else if (round(($infos[4] / 100 + 1) * $infos[5], 2)
				!== floatval($infos[7])) {
			$errors[] = '第' . $line . '行，第8列【价税合计金额】不是有效的金额';
			$isok = $isok ? FALSE : $isok;
		}

		//第九列 发票日期
		if (!self::validate_field_not_null($infos[8])) {
			$errors[] = '第' . $line . '行，第9列【发票日期】不能为空';
			$isok = $isok ? FALSE : $isok;
		} else if (!self::validate_date_int(
				PHPExcel_Shared_Date::ExcelToPHP($infos[8]))) {
			$errors[] = '第' . $line . '行，第9列【发票日期】不是有效的时间值';
			$isok = $isok ? FALSE : $isok;
		}

		//第十列 所属月份
		if (!self::validate_field_not_null($infos[9])) {
			$errors[] = '第' . $line . '行，第10列【所属月份】不能为空';
			$isok = $isok ? FALSE : $isok;
		} else if (!self::validate_month_int(
				PHPExcel_Shared_Date::ExcelToPHP($infos[9]))) {
			$errors[] = '第' . $line . '行，第10列【所属月份】不是有效的时间值';
			$isok = $isok ? FALSE : $isok;
		}

		return $isok;
	}

	function import_receive_invoice($file) {
		if (!$this->has_finance_receive_invoice_permission) {
			return array('status' => 'error', 'message' => NO_RIGHT_TO_DO_THIS);
		}
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
			//应该是10列，行数>1
			if ($sum_cols_count !== 10) {
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
						//检查发票号码+价税合计金额是否已有
						$row = $this->db
								->get_row(
										'SELECT * FROM finance_receiveinvoice_source WHERE invoice_number="'
												. $infos[$i][3]
												. '"  FOR UPDATE');
						if ($row === NULL) {
							//新增
							$result1 = $this->db
									->query(
											'INSERT INTO finance_receiveinvoice_source(taxpayer_number,media_name,invoice_content,invoice_number,tax_rate,amount,tax,sum_amount,invoice_date,belong_month,adduser,addtime) VALUE("'
													. $infos[$i][0] . '","'
													. $infos[$i][1] . '","'
													. $infos[$i][2] . '","'
													. $infos[$i][3] . '",'
													. $infos[$i][4] . ','
													. $infos[$i][5] . ','
													. $infos[$i][6] . ','
													. $infos[$i][7] . ',"'
													. date('Y-m-d',
															PHPExcel_Shared_Date::ExcelToPHP(
																	$infos[$i][8]))
													. '","'
													. date('Y-m',
															PHPExcel_Shared_Date::ExcelToPHP(
																	$infos[$i][9]))
													. '",' . $this->getUid()
													. ',"'
													. date('Y-m-d H:i:s',
															$_SERVER['REQUEST_TIME'])
													. '")');
							if ($result1 === FALSE) {
								$db_ok = FALSE;
							}

							if ($db_ok) {
								$this->db->query('COMMIT');
							} else {
								$errors[] = '第' . $i . '行记录导入失败';
								$this->db->query('ROLLBACK');
							}
						} else {
							//发票号码已有，判断价税合计
							$sql = '';
							if ($row->sum_amount === $infos[$i][7]) {
								if (!($row->taxpayer_number === $infos[$i][0]
										&& $row->media_name === $infos[$i][1]
										&& $row->invoice_content
												=== $infos[$i][2]
										&& $row->tax_rate === $infos[$i][4]
										&& $row->amount === $infos[$i][5]
										&& $row->tax === $infos[$i][6]
										&& $row->invoice_date
												=== date('Y-m-d',
														PHPExcel_Shared_Date::ExcelToPHP(
																$infos[$i][8]))
										&& $row->belong_month
												=== date('Y-m',
														PHPExcel_Shared_Date::ExcelToPHP(
																$infos[$i][9])))) {
									$sql = 'REPLACE INTO finance_receiveinvoice_source_temp(taxpayer_number,media_name,invoice_content,invoice_number,tax_rate,amount,tax,sum_amount,invoice_date,belong_month,adduser,addtime) VALUE("'
											. $infos[$i][0] . '","'
											. $infos[$i][1] . '","'
											. $infos[$i][2] . '","'
											. $infos[$i][3] . '",'
											. $infos[$i][4] . ','
											. $infos[$i][5] . ','
											. $infos[$i][6] . ','
											. $infos[$i][7] . ',"'
											. date('Y-m-d',
													PHPExcel_Shared_Date::ExcelToPHP(
															$infos[$i][8]))
											. '","'
											. date('Y-m',
													PHPExcel_Shared_Date::ExcelToPHP(
															$infos[$i][9]))
											. '",' . $this->getUid() . ',"'
											. date('Y-m-d H:i:s',
													$_SERVER['REQUEST_TIME'])
											. '")';
								}
							} else {
								//价税合计不相等
								$sql = 'UPDATE finance_receiveinvoice_source SET taxpayer_number="'
										. $infos[$i][0] . '",media_name="'
										. $infos[$i][1] . '",invoice_content="'
										. $infos[$i][2] . '",tax_rate='
										. $infos[$i][4] . ',amount='
										. $infos[$i][5] . ',tax='
										. $infos[$i][6] . ',sum_amount='
										. $infos[$i][7] . ',invoice_date="'
										. date('Y-m-d',
												PHPExcel_Shared_Date::ExcelToPHP(
														$infos[$i][8]))
										. '",belong_month="'
										. date('Y-m',
												PHPExcel_Shared_Date::ExcelToPHP(
														$infos[$i][9]))
										. '" WHERE invoice_number="'
										. $row->invoice_number . '"';
							}

							if ($sql !== '') {
								$result1 = $this->db->query($sql);
								if ($result1 === FALSE) {
									$db_ok = FALSE;
								}

								if ($db_ok) {
									$this->db->query('COMMIT');
								} else {
									$errors[] = '第' . $i . '行记录导入失败';
									$this->db->query('ROLLBACK');
								}
							} else {
								$this->db->query('ROLLBACK');
							}
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

	public function get_fix_receive_invoice_source_html() {
		if ($this->has_finance_receive_invoice_permission) {
			if ($this->sourceid !== NULL) {
				$row = $this->db
						->get_row(
								'SELECT taxpayer_number,media_name,invoice_content,invoice_number,tax_rate,amount,tax,sum_amount,invoice_date,belong_month FROM finance_receiveinvoice_source WHERE id='
										. intval($this->sourceid)
										. ' AND isok=1');

				if ($row !== NULL) {
					$row1 = $this->db
							->get_row(
									'SELECT taxpayer_number,media_name,invoice_content,invoice_number,tax_rate,amount,tax,sum_amount,invoice_date,belong_month FROM finance_receiveinvoice_source_temp WHERE invoice_number="'
											. $row->invoice_number . '"');
					$buf = file_get_contents(
							TEMPLATE_PATH
									. 'finance/receiveinvoice/finance_receive_invoice_source_fix.tpl');
					return str_replace(
							array('[LEFT]', '[TOP]', '[VCODE]',
									'[TAXPAYERNUMBER]', '[MEDIANAME]',
									'[INVOICECONTENT]', '[INVOICENUMBER]',
									'[TAXRATE]', '[AMOUNT]', '[TAX]',
									'[SUMAMOUNT]', '[INVOICEDATE]',
									'[BELONGMONTH]', '[SOURCEID]',
									'[TAXPAYERNUMBER1]', '[MEDIANAME1]',
									'[INVOICECONTENT1]', '[INVOICENUMBER1]',
									'[TAXRATE1]', '[AMOUNT1]', '[TAX1]',
									'[SUMAMOUNT1]', '[INVOICEDATE1]',
									'[BELONGMONTH1]', '[BASE_URL]'),
							array($this->get_left_html(),
									$this->get_top_html(), $this->get_vcode(),
									$row->taxpayer_number, $row->media_name,
									$row->invoice_content,
									$row->invoice_number, $row->tax_rate,
									$row->amount,
									Format_Util::my_money_format('%.2n',
											$row->tax),
									Format_Util::my_money_format('%.2n',
											$row->sum_amount),
									$row->invoice_date, $row->belong_month,
									intval($this->sourceid),
									$row1->taxpayer_number, $row1->media_name,
									$row1->invoice_content,
									$row1->invoice_number, $row1->tax_rate,
									$row1->amount,
									Format_Util::my_money_format('%.2n',
											$row1->tax),
									Format_Util::my_money_format('%.2n',
											$row1->sum_amount),
									$row1->invoice_date, $row1->belong_month,
									BASE_URL), $buf);
				} else {
					return User::no_object('没有该收票信息');
				}
			} else {
				return User::no_object('没有该收票信息');
			}
		} else {
			return User::no_permission();
		}
	}

	public function get_edit_receive_invoice_source_html() {
		if ($this->has_finance_receive_invoice_permission) {
			if ($this->sourceid !== NULL) {
				$row = $this->db
						->get_row(
								'SELECT taxpayer_number,media_name,invoice_content,invoice_number,tax_rate,amount,tax,sum_amount,invoice_date,belong_month FROM finance_receiveinvoice_source WHERE id='
										. intval($this->sourceid)
										. ' AND isok=1');

				if ($row !== NULL) {
					$buf = file_get_contents(
							TEMPLATE_PATH
									. 'finance/receiveinvoice/finance_receive_invoice_source_edit.tpl');
					return str_replace(
							array('[LEFT]', '[TOP]', '[VCODE]',
									'[TAXPAYERNUMBER]', '[MEDIANAME]',
									'[INVOICECONTENT]', '[INVOICENUMBER]',
									'[TAXRATE]', '[AMOUNT]', '[TAX]',
									'[SUMAMOUNT]', '[INVOICEDATE]',
									'[BELONGMONTH]', '[SOURCEID]',
									'[BASE_URL]'),
							array($this->get_left_html(),
									$this->get_top_html(), $this->get_vcode(),
									$row->taxpayer_number, $row->media_name,
									$row->invoice_content,
									$row->invoice_number, $row->tax_rate,
									$row->amount,
									Format_Util::my_money_format('%.2n',
											$row->tax),
									Format_Util::my_money_format('%.2n',
											$row->sum_amount),
									$row->invoice_date, $row->belong_month,
									intval($this->sourceid), BASE_URL), $buf);
				} else {
					return User::no_object('没有该收票信息');
				}
			} else {
				return User::no_object('没有该收票信息');
			}
		} else {
			return User::no_permission();
		}
	}

	private function validate_form_value($action) {
		$errors = array();
		if (!in_array($action,
				array('source_add', 'source_update', 'source_fix_update',
						'source_fix_delete', 'share', 'payment_share',
						'update', 'payment_update'), TRUE)) {
			$errors[] = NO_RIGHT_TO_DO_THIS;
		} else {
			if (in_array($action,
					array('share', 'payment_share', 'update', 'payment_update'),
					TRUE)) {
				if ($action === 'update' || $action === 'payment_update') {
					if (!self::validate_id(intval($this->id))) {
						$errors[] = '分配信息选择有误';
					}
				}

				if (!empty($this->ids)) {
					$ids = explode(',', $this->ids);
					foreach ($ids as $id) {
						if (!self::validate_id(intval($id))) {
							$errors[] = '收票对账单记录选择有误';
							break;
						}
					}
				} else {
					$errors[] = '收票对账单不能为空';
				}

				$pids_sumamount = $action === 'share' || $action === 'update' ? $this
								->pids_sumamount : $this->itemids_array;
				if (!empty($pids_sumamount)) {
					foreach ($pids_sumamount as $ps) {
						if (!self::validate_money($ps['amount'])) {
							$errors[] = '成本不是有效的金额值';
							break;
						}

						if (!self::validate_money($ps['tax'])) {
							$errors[] = '进项不是有效的金额值';
							break;
						}

						if (!self::validate_money($ps['sumamount'])) {
							$errors[] = '价税合计不是有效的金额值';
							break;
						}
					}
				} else {
					$errors[] = '分配项目不能为空';
				}

			} else if ($action === 'source_fix_update'
					|| $action === 'source_fix_delete') {
				if (!self::validate_id(intval($this->sourceid))) {
					$errors[] = '收票记录选择有误';
				}
			} else {
				//纳税人识别号
				if (!self::validate_field_not_null($this->taxpayer_number)
						|| !self::validate_field_not_empty(
								$this->taxpayer_number)) {
					$errors[] = '纳税人识别号不能为空';
				} else if (!Validate_Util::my_is_numeric($this->taxpayer_number)) {
					$errors[] = '纳税人识别号不是有效的纳税人识别号';
				} else if (!self::validate_field_max_length(
						$this->taxpayer_number, 255)) {
					$errors[] = '纳税人识别号长度最多255个字符';
				}

				//媒体名称
				if (!self::validate_field_not_null($this->media_name)
						|| !self::validate_field_not_empty($this->media_name)) {
					$errors[] = '媒体名称不能为空';
				} else if (!self::validate_field_max_length($this->media_name,
						255)) {
					$errors[] = '媒体名称长度最多255个字符';
				}

				//发票内容
				/*
				if (!self::validate_field_not_null($this->invoice_content)
				        || !self::validate_field_not_empty($this->invoice_content)) {
				    $errors[] = '发票内容不能为空';
				} else if (!self::validate_field_max_length(
				        $this->invoice_content, 255)) {
				    $errors[] = '发票内容长度最多255个字符';
				}
				 */
				if (!in_array(intval($this->invoice_content), array(1, 2), TRUE)) {
					$errors[] = '发票内容选择有误';
				}

				//凭证号码
				if (!self::validate_field_not_null($this->invoice_number)
						|| !self::validate_field_not_empty(
								$this->invoice_number)) {
					$errors[] = '凭证号码不能为空';
				} else if (!Validate_Util::my_is_numeric($this->invoice_number)) {
					$errors[] = '凭证号码不是有效的凭证号码';
				} else if (!self::validate_field_max_length(
						$this->invoice_number, 255)) {
					$errors[] = '凭证号码长度最多255个字符';
				} else {
					$query = 'SELECT id FROM finance_receiveinvoice_source WHERE invoice_number="'
							. $this->invoice_number . '"';

					if ($action === 'source_update') {
						$query .= ' AND id<>' . intval($this->sourceid);
					}
					if ($this->db->get_var($query) > 0) {
						$errors[] = '凭证号码已存在';
					}
				}

				//税率
				if (!self::validate_field_not_null($this->tax_rate)) {
					$errors[] = '税率不能为空';
				} else if (!Validate_Util::my_is_float($this->tax_rate)) {
					$errors[] = '税率不是有效的数值';
				} else if ($this->tax_rate > 100 || $this->tax_rate < 0) {
					$errors[] = '税率不是有效的数值';
				}

				//成本
				if (!self::validate_field_not_null($this->amount)) {
					$errors[] = '成本不能为空';
				} else if (!self::validate_money($this->amount)) {
					$errors[] = '成本不是有效的金额';
				}

				//发票日期
				if (!self::validate_field_not_null($this->invoice_date)) {
					$errors[] = '发票日期不能为空';
				} else if (strtotime($this->invoice_date) === FALSE) {
					$errors[] = '发票日期不是有效的时间值';
				}

				//所属月份
				if (!self::validate_field_not_null($this->belong_month)) {
					$errors[] = '所属月份不能为空';
				} else if (strtotime($this->belong_month) === FALSE) {
					$errors[] = '所属月份不是有效的时间值';
				}

				if ($action === 'source_update') {
					$row = $this->db
							->get_row(
									'SELECT id FROM finance_receiveinvoice_source WHERE id='
											. intval($this->sourceid));
					if ($row === NULL) {
						$errors[] = '该收票信息不存在';
					} else {
						unset($row);
					}
				}
			}
		}

		if (empty($errors)) {
			return TRUE;
		}
		$this->errors = $errors;
		unset($errors);
		return FALSE;
	}

	public function add_receive_invoice_source() {
		if ($this->validate_form_value('source_add')) {
			$success = TRUE;
			$error = '';
			$this->db->query('BEGIN');

			$tax = round(($this->amount * $this->tax_rate / 100), 2);

			$result = $this->db
					->query(
							'INSERT INTO finance_receiveinvoice_source(taxpayer_number,media_name,invoice_content,invoice_number,tax_rate,amount,tax,sum_amount,invoice_date,belong_month,adduser,addtime,isok) VALUE("'
									. $this->taxpayer_number . '","'
									. $this->media_name . '","'
									. (intval($this->invoice_content) === 1 ? '广告'
											: '服务') . '","'
									. $this->invoice_number . '",'
									. $this->tax_rate . ',' . $this->amount
									. ',' . $tax . ','
									. round(
											($this->amount
													* ($this->tax_rate + 100)
													/ 100), 2) . ',"'
									. $this->invoice_date . '","'
									. $this->belong_month . '",'
									. $this->getUid() . ',"'
									. date('Y-m-d H:i:s',
											$_SERVER['REQUEST_TIME']) . '",1)');
			if ($result === FALSE) {
				$success = FALSE;
				$error = '新增收票信息失败';
			}

			if ($success) {
				$this->db->query('COMMIT');
			} else {
				$this->db->query('ROLLBACK');
			}

			return array('status' => $success ? 'success' : 'error',
					'message' => $success ? '新增收票信息成功' : $error);
		}
		return array('status' => 'error', 'message' => $this->errors);
	}

	public function update_receive_invoice_source() {
		if ($this->validate_form_value('source_update')) {
			$success = TRUE;
			$error = '';
			$this->db->query('BEGIN');

			$tax = round(($this->amount * $this->tax_rate / 100), 2);

			$result = $this->db
					->query(
							'UPDATE finance_receiveinvoice_source SET taxpayer_number="'
									. $this->taxpayer_number . '",media_name="'
									. $this->media_name . '",invoice_content="'
									. (intval($this->invoice_content) === 1 ? '广告'
											: '服务') . '",invoice_number="'
									. $this->invoice_number . '",tax_rate='
									. $this->tax_rate . ',amount='
									. $this->amount . ',tax=' . $tax
									. ',sum_amount=' . ($this->amount + $tax)
									. ',invoice_date="' . $this->invoice_date
									. '",belong_month="' . $this->belong_month
									. '" WHERE id=' . intval($this->sourceid));
			if ($result === FALSE) {
				$success = FALSE;
				$error = '修改收票信息失败';
			}

			if ($success) {
				$this->db->query('COMMIT');
			} else {
				$this->db->query('ROLLBACK');
			}

			return array('status' => $success ? 'success' : 'error',
					'message' => $success ? '修改收票信息成功' : $error);
		}
		return array('status' => 'error', 'message' => $this->errors);
	}

	public function get_add_receive_invoice_source_html() {
		if ($this->has_finance_receive_invoice_permission) {
			$buf = file_get_contents(
					TEMPLATE_PATH
							. 'finance/receiveinvoice/finance_receive_invoice_source_add.tpl');
			return str_replace(
					array('[LEFT]', '[TOP]', '[VCODE]', '[BASE_URL]'),
					array($this->get_left_html(), $this->get_top_html(),
							$this->get_vcode(), BASE_URL), $buf);

		} else {
			return User::no_permission();
		}
	}

	public function get_receive_invoice_edit_html() {
		if ($this->has_finance_receive_invoice_permission) {
			$buf = file_get_contents(
					TEMPLATE_PATH
							. 'finance/receiveinvoice/finance_receive_invoice_edit.tpl');
			return str_replace(
					array('[LEFT]', '[TOP]', '[VCODE]', '[BASE_URL]'),
					array($this->get_left_html(), $this->get_top_html(),
							$this->get_vcode(), BASE_URL), $buf);

		} else {
			return User::no_permission();
		}
	}

	public function get_receive_invoice_transfer_html() {
		if ($this->has_finance_receive_invoice_permission) {
			$buf = file_get_contents(
					TEMPLATE_PATH
							. 'finance/receiveinvoice/finance_receive_invoice_transfer.tpl');
			return str_replace(
					array('[LEFT]', '[TOP]', '[VCODE]', '[BASE_URL]'),
					array($this->get_left_html(), $this->get_top_html(),
							$this->get_vcode(), BASE_URL), $buf);

		} else {
			return User::no_permission();
		}
	}

	public function receive_invoice_source_fix() {
		$action = $this->actype === 'update' ? 'source_fix_update'
				: 'source_fix_delete';
		if ($this->validate_form_value($action)) {
			$success = TRUE;
			$error = '';
			$this->db->query('BEGIN');

			$row = $this->db
					->get_row(
							'SELECT invoice_number FROM finance_receiveinvoice_source WHERE id='
									. intval($this->sourceid) . ' FOR UPDATE');
			if ($row !== NULL) {
				if ($action === 'source_fix_update') {
					//覆盖现有数据
					$new = $this->db
							->get_row(
									'SELECT * FROM finance_receiveinvoice_source_temp WHERE invoice_number="'
											. $row->invoice_number . '"');
					if ($new !== NULL) {
						$result = $this->db
								->query(
										'UPDATE finance_receiveinvoice_source SET taxpayer_number="'
												. $new->taxpayer_number
												. '",media_name="'
												. $new->media_name
												. '",invoice_content="'
												. $new->invoice_content
												. '",tax_rate='
												. $new->tax_rate . ',amount='
												. $new->amount . ',tax='
												. $new->tax . ',sum_amount='
												. $new->sum_amount
												. ',invoice_date="'
												. $new->invoice_date
												. '",belong_month="'
												. $new->belong_month
												. ' WHERE invoice_number="'
												. $row->invoice_number . '"');
						if ($result === FALSE) {
							$success = FALSE;
							$error = '覆盖现有数据出错';
						}
					} else {
						$success = FALSE;
						$error = '获取最新数据出错';
					}
				}

				$result = $this->db
						->query(
								'DELETE FROM finance_receiveinvoice_source_temp WHERE invoice_number="'
										. $row->invoice_number . '"');
				if ($result === FALSE) {
					$success = FALSE;
					$error = '删除最新数据出错';
				}
			} else {
				$success = FALSE;
				$error = '收票信息不存在';
			}

			if ($success) {
				$this->db->query('COMMIT');
			} else {
				$this->db->query('ROLLBACK');
			}

			return array('status' => $success ? 'success' : 'error',
					'message' => $success ? '修改收票信息成功' : $error);
		}
		return array('status' => 'error', 'message' => $this->errors);
	}

	public function get_receive_invoice_share_html() {
		if ($this->has_finance_receive_invoice_permission) {
			$results = $this->db
					->get_results(
							'SELECT DISTINCT(tax_rate) FROM finance_receiveinvoice_source WHERE id IN ('
									. $this->ids . ')');
			if (count($results) === 1) {
				$buf = file_get_contents(
						TEMPLATE_PATH
								. 'finance/receiveinvoice/finance_receive_invoice_source_share.tpl');
				return str_replace(
						array('[LEFT]', '[TOP]', '[VCODE]', '[SOURCEIDS]',
								'[TAXRATE]', '[BASE_URL]'),
						array($this->get_left_html(), $this->get_top_html(),
								$this->get_vcode(), $this->ids,
								$results[0]->tax_rate, BASE_URL), $buf);
			} else {
				return User::no_permission('所选收票对账单税率必须一致');
			}
		} else {
			return User::no_permission();
		}
	}

	public function get_receive_invoice_payment_share_html() {
		if ($this->has_finance_receive_invoice_permission) {
			$results = $this->db
					->get_results(
							'SELECT DISTINCT(tax_rate) FROM finance_receiveinvoice_source WHERE id IN ('
									. $this->ids . ')');
			if (count($results) === 1) {
				$buf = file_get_contents(
						TEMPLATE_PATH
								. 'finance/receiveinvoice/finance_receive_invoice_source_payment_share.tpl');
				return str_replace(
						array('[LEFT]', '[TOP]', '[VCODE]', '[SOURCEIDS]',
								'[TAXRATE]', '[BASE_URL]'),
						array($this->get_left_html(), $this->get_top_html(),
								$this->get_vcode(), $this->ids,
								$results[0]->tax_rate, BASE_URL), $buf);
			} else {
				return User::no_permission('所选收票对账单税率必须一致');
			}
		} else {
			return User::no_permission();
		}
	}

	private static function _getPrev($page, $type) {
		if (intval($page) === 1) {
			return '';
		} else {
			return self::_get_pagination(intval($page) - 1, TRUE, $type);
		}
	}

	private static function _getNext($page, $page_count, $type) {
		if (intval($page) >= intval($page_count)) {
			return '';
		} else {
			return self::_get_pagination(intval($page) + 1, FALSE, $type);
		}
	}

	private static function _get_pagination($page, $is_prev, $type) {
		if ($type !== NULL) {
			return '<a href="javascript:void(0)" onclick="dosearch(' . $type
					. ',' . $page . ');">' . ($is_prev ? '上一页' : '下一页')
					. '</a>';
		} else {
			return '<a href="javascript:void(0)" onclick="dosearch(' . $page
					. ');">' . ($is_prev ? '上一页' : '下一页') . '</a>';
		}
	}

	public function get_search_payment_apply_html() {
		$s = '<table width="100%" class="sbd1"><tr><td></td><td>媒体名称</td><td>付款时间</td><td>应付款金额</td><td>实付款金额</td></tr>';
		$where = array();
		if ($this->medianame !== NULL && $this->medianame !== '') {
			$where[] = ' b.media_name LIKE "%' . $this->medianame . '%"';
		}

		if ($this->paymentdate !== NULL && $this->paymentdate !== '') {
			$where[] = ' a.payment_date="' . $this->paymentdate . '"';
		}

		if ($this->payment_plan !== NULL && $this->payment_plan !== '') {
			$where[] = ' a.payment_amount_plan="' . $this->payment_plan . '"';
		}

		if ($this->payment_real !== NULL && $this->payment_real !== '') {
			$where[] = ' a.payment_amount_real="' . $this->payment_real . '"';
		}

		$base_sql = 'SELECT a.id,a.payment_id,a.payment_amount_plan,a.payment_amount_real,a.payment_date,b.media_name,\'p\' AS atype FROM finance_payment_person_apply a LEFT JOIN finance_payment_media_info b ON a.media_info_id=b.id WHERE 1=1 '
				. (!empty($where) ? ' AND ' . implode(' AND ', $where) : '')
				. ' UNION ALL SELECT a.id,a.payment_id,a.payment_amount_plan,a.payment_amount_real,a.payment_date,b.media_name,\'m\' AS atype FROM finance_payment_media_apply a LEFT JOIN finance_payment_media_info b ON a.media_info_id=b.id WHERE 1=1 '
				. (!empty($where) ? ' AND ' . implode(' AND ', $where) : '');

		$allcount = $this->db
				->get_var('SELECT COUNT(*) FROM (' . $base_sql . ') z');
		$page_count = ceil($allcount / self::EXE_LIMIT);
		$start = self::EXE_LIMIT * intval($this->page) - self::EXE_LIMIT;
		if ($start < 0) {
			$start = 0;
		}

		$results = $this->db
				->get_results(
						'SELECT * FROM (' . $base_sql . ') z LIMIT ' . $start
								. ',' . self::EXE_LIMIT);
		if ($results !== NULL) {
			foreach ($results as $result) {
				$s .= '<tr><td width="5"><input type="checkbox" name="paymentselect" value="'
						. $result->id . '_' . $result->atype . '"></td><td>'
						. $result->media_name . '</td><td>'
						. $result->payment_date . '</td><td>'
						. $result->payment_amount_plan . '</td><td>'
						. $result->payment_amount_real . '</td></tr>';
			}
			$pageinfo = '<tr><td colspan="5"><div id="pageinfo">'
					. intval($this->page) . ' / ' . $page_count . ' 页 &nbsp;'
					. self::_getPrev($this->page, NULL) . '&nbsp;'
					. self::_getNext($this->page, $page_count, NULL)
					. '&nbsp; <input id="movepid" type="button" value="选 择" onclick="javascript:pidmove();" class="btn"/></div></td></tr>';
			$s .= $pageinfo;
		} else {
			$s .= '<tr colspan="5"><font color="red"><b>没有找到相关内容!</b></font></tr>';
		}
		$s .= '</table>';
		return $s;
	}

	public function get_search_executive_html() {
		$s = '<table width="100%" class="sbd1"><tr><td>执行单号</td><td>客户名称</td><td>执行成本</td><td>收票情况</td><td>付款情况</td><td>已付款未到票</td><td>最后付款时间</td><td></td></tr>';

		$exe_where = array();
		if ($this->search !== NULL && $this->search !== '') {
			$exe_where[] = 'pid LIKE "%' . $this->search . '%"';
		}

		if (empty($exe_where)) {
			$exe_where[] = '1=1';
		}

		$paid_noinvoice = array();
		if (intval($this->type) === 2) {
			//搜索已付款未到票
			$results = $this->db
					->get_results(
							'SELECT a.pid,a.paycostid,a.gd_amount,b.receive_invoice_amount
FROM
(SELECT SUM(gd_amount) AS gd_amount,pid,paycostid FROM finance_payment_gd '
									. ($this->search !== NULL
											&& $this->search !== '' ? ' WHERE pid LIKE "%'
													. $this->search . '%"' : '')
									. ' GROUP BY pid ) a
LEFT JOIN
(SELECT SUM(sum_amount) AS receive_invoice_amount,pid,paycostid FROM finance_receiveinvoice_pid_list WHERE isok=1 GROUP BY pid ) b
ON a.pid=b.pid AND a.paycostid=b.paycostid
UNION
SELECT b.pid,b.paycostid,a.gd_amount,b.receive_invoice_amount
FROM
(SELECT SUM(gd_amount) AS gd_amount,pid,paycostid FROM finance_payment_gd '
									. ($this->search !== NULL
											&& $this->search !== '' ? ' WHERE pid LIKE "%'
													. $this->search . '%"' : '')
									. ' GROUP BY pid ) a
RIGHT JOIN
(SELECT SUM(sum_amount) AS receive_invoice_amount,pid,paycostid FROM finance_receiveinvoice_pid_list WHERE isok=1 GROUP BY pid ) b
ON a.pid=b.pid AND a.paycostid=b.paycostid');
			if ($results !== NULL) {
				foreach ($results as $result) {
					if (doubleval($result->gd_amount)
							!== doubleval($result->receive_invoice_amount)) {
						$paid_noinvoice[] = $result->pid;
					}
				}
			}
		}

		$results = array();
		$exes = $this->db
				->get_results(
						'SELECT b.pid,b.allcost,b.name,b.costpaymentinfoids,c.cusname,x.payment,x.gd_time FROM (SELECT MAX(isalter) AS isalter,pid FROM executive WHERE '
								. implode(' AND ', $exe_where)
								. ' GROUP BY pid) z LEFT JOIN executive b ON (z.isalter=b.isalter AND z.pid=b.pid) LEFT JOIN contract_cus c ON b.cid=c.cid LEFT JOIN (SELECT SUM(gd_amount) AS payment,pid,gd_time FROM (SELECT * FROM finance_payment_gd ORDER BY pid,gd_time DESC) t GROUP BY t.pid) x ON z.pid=x.pid WHERE b.isok<>-1 AND b.allcost >0'
								. ($this->cusname !== NULL
										&& $this->cusname !== '' ? ' AND c.cusname LIKE "%'
												. $this->cusname . '%"' : '')
								. (intval($this->type) === 2
										&& !empty($paid_noinvoice) ? ' AND b.pid IN ("'
												. implode('","',
														$paid_noinvoice) . '")'
										: ''));
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
										'payamount' => $costpay->payamount);
								$sum_cost += $costpay->payamount;
							}
						} else {
							$cost_array[] = array('id' => $costpay->id,
									'payname' => $costpay->payname,
									'payamount' => $costpay->payamount);
						}
					}

					if ($this->medianame !== NULL && $this->medianame !== '') {
						if (!empty($cost_array)) {
							$results[] = array('pid' => $val->pid,
									'allcost' => $sum_cost,
									'name' => $val->name,
									'cusname' => $val->cusname,
									'payment' => $val->payment,
									'cost_array' => $cost_array,
									'gd_time' => $val->gd_time);
						}
					} else {
						$results[] = array('pid' => $val->pid,
								'allcost' => $val->allcost,
								'name' => $val->name,
								'cusname' => $val->cusname,
								'payment' => $val->payment,
								'cost_array' => $cost_array,
								'gd_time' => $val->gd_time);
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

			foreach ($results as $result) {
				$s .= '<tr><td id="pid_' . $result['pid'] . '">'
						. $result['pid'] . '</td><td id="cus_' . $result['pid']
						. '">' . $result['cusname'] . '</td><td id="cost_'
						. $result['pid']
						. '" style="color:#ff9933;font-weight:bold;">'
						. Format_Util::my_money_format('%.2n',
								$result['allcost'])
						. '</td><td></td><td id="payment_' . $result['pid']
						. '"  style="color:green;font-weight:bold;">'
						. Format_Util::my_money_format('%.2n',
								$result['payment']) . '</td><td></td><td>'
						. $result['gd_time']
						. '</td><td><input type="button" value="展开" class="btn" onclick="javascript:openit(\''
						. $result['pid'] . '\')"></td></tr>';
				$ss = '';
				foreach ($result['cost_array'] as $ca) {
					$ss .= '<tr><td width="5%"><input type="checkbox" value="'
							. $result['pid'] . '_' . $ca['id']
							. '"></td><td style="font-weight:bold;width:35%;">媒体名：'
							. $ca['payname']
							. '</td><td style="color:#ff9933;font-weight:bold;width:20%;">执行成本：'
							. $ca['payamount']
							. '</td><td style="color:green;font-weight:bold;width:20%;">已支付情况：0</td><td style="color:red;font-weight:bold;width:20%;">已执行未付款金额：0</td></tr>';
				}
				$s .= '<tr id="tr_' . $result['pid']
						. '"><td colspan="8"><table width="100%">' . $ss
						. '</table></td></tr>';
			}
			$pageinfo = '<tr><td colspan="8"><div id="pageinfo">'
					. intval($this->page) . ' / ' . $page_count . ' 页 &nbsp;'
					. self::_getPrev($this->page, $this->type) . '&nbsp;'
					. self::_getNext($this->page, $page_count, $this->type)
					. '&nbsp; <input id="movepid" type="button" value="选 择" onclick="javascript:pidmove();" class="btn"/></div></td></tr>';
			$s .= $pageinfo;
		} else {
			$s .= '<tr><td colspan="8"><font color="red"><b>没有找到相关内容!</b></font></td></tr>';
		}
		$s .= '</table><script>$(\'[id^=tr_]\').hide();</script>';
		return $s;
	}

	public function receive_invoice_source_payment_share(
			$action = 'payment_share') {
		if ($this->has_finance_receive_invoice_permission) {
			if ($this->validate_form_value($action)) {
				$success = TRUE;
				$error = '';
				$this->db->query('BEGIN');

				if ($action === 'payment_update') {
					$row = $this->db
							->get_row(
									'SELECT pid_list_ids FROM finance_receiveinvoice_source_pid WHERE id='
											. intval($this->id) . ' AND isok=1');
					if ($row === NULL) {
						$success = TRUE;
						$error = '没有该分配信息或状态无法更新';
					} else {
						$pid_list_ids = substr($row->pid_list_ids, 1,
								strlen($row->pid_list_ids) - 2);
						$result = $this->db
								->query(
										'UPDATE finance_receiveinvoice_pid_list SET isok=-1 WHERE id IN('
												. str_replace('^', ',',
														$pid_list_ids) . ')');
						if ($result === FALSE) {
							$success = FALSE;
							$error = '分配付款申请失败，错误代码3';
						} else {
							$result = $this->db
									->query(
											'UPDATE finance_receiveinvoice_source_pid SET isok=-1 WHERE id='
													. intval($this->id));
							if ($result === FALSE) {
								$success = FALSE;
								$error = '分配付款申请失败，错误代码4';
							}
						}
					}
				}

				if ($success) {
					$itemids_array = $this->itemids_array;
					$pid_list_array = array();
					foreach ($itemids_array as $key => $value) {
						$key = explode('_', $key);
						if ($key[2] === 'p') {
							//个人申请
							$sql = 'INSERT INTO finance_receiveinvoice_pid_list(pid,paycostid,amount,tax,sum_amount,isok,apply_id,apply_list_id,apply_type) SELECT pid,paycostid,'
									. $value['amount'] . ',' . $value['tax']
									. ',' . $value['sumamount'] . ',1,'
									. $key[1] . ',' . $key[0] . ',"' . $key[2]
									. '" FROM finance_payment_person_apply_list WHERE id='
									. $key[0];
						} else if ($key[2] === 'm') {
							//批量申请
							$sql = 'INSERT INTO finance_receiveinvoice_pid_list(pid,paycostid,amount,tax,sum_amount,isok,apply_id,apply_list_id,apply_type) SELECT pid,paycostid,'
									. $value['amount'] . ',' . $value['tax']
									. ',' . $value['sumamount'] . ',1,'
									. $key[1] . ',' . $key[0] . ',"' . $key[2]
									. '" FROM finance_payment_media_apply_list WHERE id='
									. $key[0];
						}

						$result = $this->db->query($sql);
						if ($result === FALSE) {
							$success = FALSE;
							$error = '分配付款申请失败，错误代码1';
							break;
						} else {
							$pid_list_array[] = $this->db->insert_id;
						}
					}

					if ($success) {
						$source_ids = array();
						$ids = explode(',', $this->ids);
						foreach ($ids as $id) {
							if (!empty($id)) {
								$source_ids[] = $id;
							}
						}

						$result = $this->db
								->query(
										'INSERT INTO finance_receiveinvoice_source_pid(source_ids,pid_list_ids,sharetype,isok,addtime) VALUE("^'
												. implode('^', $source_ids)
												. '^","^'
												. implode('^', $pid_list_array)
												. '^",2,1,now())');
						if ($result === FALSE) {
							$success = FALSE;
							$error = '分配付款申请失败，错误代码2';
						}
					}
				}

				if ($success) {
					$this->db->query('COMMIT');
				} else {
					$this->db->query('ROLLBACK');
				}
				return array('status' => $success ? 'success' : 'error',
						'message' => $success ? '分配付款申请成功' : $error);
			}
			return array('status' => 'error', 'message' => $this->errors);
		}
		return array('status' => 'error', 'message' => NO_RIGHT_TO_DO_THIS);
	}

	public function receive_invoice_source_share($action = 'share') {
		if ($this->has_finance_receive_invoice_permission) {
			if ($this->validate_form_value($action)) {
				$success = TRUE;
				$error = '';
				$this->db->query('BEGIN');

				if ($action === 'update') {
					$row = $this->db
							->get_row(
									'SELECT pid_list_ids FROM finance_receiveinvoice_source_pid WHERE id='
											. intval($this->id) . ' AND isok=1');
					if ($row === NULL) {
						$success = TRUE;
						$error = '没有该分配信息或状态无法更新';
					} else {
						$pid_list_ids = substr($row->pid_list_ids, 1,
								strlen($row->pid_list_ids) - 2);
						$result = $this->db
								->query(
										'UPDATE finance_receiveinvoice_pid_list SET isok=-1 WHERE id IN('
												. str_replace('^', ',',
														$pid_list_ids) . ')');
						if ($result === FALSE) {
							$success = FALSE;
							$error = '分配执行单失败，错误代码3';
						} else {
							$result = $this->db
									->query(
											'UPDATE finance_receiveinvoice_source_pid SET isok=-1 WHERE id='
													. intval($this->id));
							if ($result === FALSE) {
								$success = FALSE;
								$error = '分配执行单失败，错误代码4';
							}
						}
					}
				}

				if ($success) {
					$pids_array = $this->pids_array;
					$pids_sumamount = $this->pids_sumamount;
					$pid_list_array = array();
					foreach ($pids_array as $pa) {
						$amount = $pids_sumamount[$pa]['amount'];
						$tax = $pids_sumamount[$pa]['tax'];
						$sumamount = $pids_sumamount[$pa]['sumamount'];
						$pa = explode('_', $pa);
						$result = $this->db
								->query(
										'INSERT INTO finance_receiveinvoice_pid_list(pid,paycostid,amount,tax,sum_amount,isok) VALUE("'
												. $pa[0] . '",' . $pa[1] . ','
												. $amount . ',' . $tax . ','
												. $sumamount . ',1)');
						if ($result === FALSE) {
							$success = FALSE;
							$error = '分配执行单失败，错误代码1';
							break;
						} else {
							//$pid_list_id = $this->db->insert_id;
							$pid_list_array[] = $this->db->insert_id;
						}
					}

					if ($success) {
						$source_ids = array();
						$ids = explode(',', $this->ids);
						foreach ($ids as $id) {
							if (!empty($id)) {
								$source_ids[] = $id;
							}
						}

						$result = $this->db
								->query(
										'INSERT INTO finance_receiveinvoice_source_pid(source_ids,pid_list_ids,sharetype,isok,addtime) VALUE("^'
												. implode('^', $source_ids)
												. '^","^'
												. implode('^', $pid_list_array)
												. '^",1,1,now())');
						if ($result === FALSE) {
							$success = FALSE;
							$error = '分配执行单失败，错误代码2';
						}
					}
				}

				if ($success) {
					$this->db->query('COMMIT');
				} else {
					$this->db->query('ROLLBACK');
				}
				return array('status' => $success ? 'success' : 'error',
						'message' => $success ? '分配执行单成功' : $error);

			} else {
				return array('status' => 'error', 'message' => $this->errors);
			}
		} else {
			return array('status' => 'error', 'message' => NO_RIGHT_TO_DO_THIS);
		}
	}

	public function get_edit_receive_invoice_pid_share_html() {
		if ($this->has_finance_receive_invoice_permission) {
			$row = $this->db
					->get_row(
							'SELECT source_ids,pid_list_ids FROM finance_receiveinvoice_source_pid WHERE id='
									. intval($this->shareid)
									. ' AND sharetype=1 AND isok=1');
			if ($row !== NULL) {
				$source_ids = substr($row->source_ids, 1,
						strlen($row->source_ids) - 2);
				$pid_list_ids = substr($row->pid_list_ids, 1,
						strlen($row->pid_list_ids) - 2);

				$pid_list_ids = $this->db
						->get_results(
								'SELECT pid,paycostid,amount,tax FROM finance_receiveinvoice_pid_list WHERE id IN('
										. str_replace('^', ',', $pid_list_ids)
										. ')');
				$pids = array();
				$tax_rate = 0;
				if ($pid_list_ids !== NULL) {
					foreach ($pid_list_ids as $key => $pid_list_id) {
						$pids[] = $pid_list_id->pid . '_'
								. $pid_list_id->paycostid;
						if ($key === 0) {
							$tax_rate = round(
									$pid_list_id->tax * 100
											/ $pid_list_id->amount, 2);
						}
					}
				}
				$pids = empty($pids) ? '' : ',' . implode(',', $pids) . ',';

				$buf = file_get_contents(
						TEMPLATE_PATH
								. 'finance/receiveinvoice/finance_receive_invoice_pid_share.tpl');
				return str_replace(
						array('[LEFT]', '[TOP]', '[VCODE]', '[SOURCEIDS]',
								'[PIDS]', '[ID]', '[TAXRATE]', '[BASE_URL]'),
						array($this->get_left_html(), $this->get_top_html(),
								$this->get_vcode(),
								str_replace('^', ',', $source_ids), $pids,
								intval($this->shareid), $tax_rate, BASE_URL),
						$buf);
			} else {
				return User::no_object('没有该收票分配信息');
			}
		} else {
			return User::no_permission();
		}
	}

	public function get_edit_receive_invoice_payment_share_html() {
		if ($this->has_finance_receive_invoice_permission) {
			$row = $this->db
					->get_row(
							'SELECT source_ids,pid_list_ids FROM finance_receiveinvoice_source_pid WHERE id='
									. intval($this->shareid)
									. ' AND sharetype=2 AND isok=1');
			if ($row !== NULL) {
				$source_ids = substr($row->source_ids, 1,
						strlen($row->source_ids) - 2);
				$pid_list_ids = substr($row->pid_list_ids, 1,
						strlen($row->pid_list_ids) - 2);

				$pid_list_ids = $this->db
						->get_results(
								'SELECT pid,paycostid,amount,tax,apply_id,apply_list_id,apply_type FROM finance_receiveinvoice_pid_list WHERE id IN('
										. str_replace('^', ',', $pid_list_ids)
										. ')');
				$pids = array();
				$list_id = array();
				$tax_rate = 0;
				if ($pid_list_ids !== NULL) {
					foreach ($pid_list_ids as $key => $pid_list_id) {
						if (!in_array(
								$pid_list_id->apply_id . '_'
										. $pid_list_id->apply_type, $pids,
								TRUE)) {
							$pids[] = $pid_list_id->apply_id . '_'
									. $pid_list_id->apply_type;
						}

						$list_id[] = $pid_list_id->apply_list_id . '_'
								. $pid_list_id->apply_id . '_'
								. $pid_list_id->apply_type;

						if ($key === 0) {
							$tax_rate = round(
									$pid_list_id->tax * 100
											/ $pid_list_id->amount, 2);
						}
					}
				}

				$pids = empty($pids) ? '' : ',' . implode(',', $pids) . ',';
				$list_id = empty($list_id) ? ''
						: ',' . implode(',', $list_id) . ',';
				$buf = file_get_contents(
						TEMPLATE_PATH
								. 'finance/receiveinvoice/finance_receive_invoice_payment_share.tpl');
				return str_replace(
						array('[LEFT]', '[TOP]', '[VCODE]', '[SOURCEIDS]',
								'[PIDS]', '[ID]', '[TAXRATE]', '[ITEMIDS]',
								'[ID]', '[BASE_URL]'),
						array($this->get_left_html(), $this->get_top_html(),
								$this->get_vcode(),
								str_replace('^', ',', $source_ids), $pids,
								intval($this->shareid), $tax_rate, $list_id,
								intval($this->shareid), BASE_URL), $buf);
			} else {
				return User::no_object('没有该收票分配信息');
			}
		} else {
			return User::no_permission();
		}
	}
}
