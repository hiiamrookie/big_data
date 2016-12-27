<?php
class Supplier extends User {
	private $id;
	private $infoid;
	private $recover;
	private $has_supplier_permission = FALSE;
	private $errors = array();

	private static $instance = NULL;

	private $rebate;
	private $in_invoice_tax_rate;
	private $supplier_name;
	private $url;
	private $deduction;
	private $supplier_type;
	private $dids;

	private $audit_result;
	private $remark;

	private $category;
	private $isagent2;

	private $parentid;

	private $supplier_id;
	private $industry_name;
	private $industry_id;

	private $starttime;
	private $endtime;

	private $category_name;
	private $category_id;

	private $exportype;

	public static function getSupplierInstance($force_flush = FALSE) {
		$supplier_cache_filename = md5('getSupplierInstance');
		$supplier_cache = new FileCache(CACHE_TIME, CACHE_PATH);
		$supplier_cache_file = $supplier_cache->get($supplier_cache_filename);
		if ($force_flush || $supplier_cache_file === FALSE) {
			//读取数据库
			$dao = new Dao_Impl();
			$suppliers = $dao->db
					->get_results(
							'SELECT id,supplier_name FROM new_supplier WHERE isok=1');

			if ($suppliers !== NULL) {
				$datas = array();
				foreach ($suppliers as $supplier) {
					$datas[$supplier->id] = $supplier->supplier_name;
				}
				$supplier_cache->set($supplier_cache_filename, $datas);
			}
		}

		return $supplier_cache->get($supplier_cache_filename);
	}

	public static function getInstance($force_flush = FALSE) {
		$supplier_cache_filename = md5('supplier_cache_filename');
		$supplier_cache = new FileCache(CACHE_TIME, CACHE_PATH);
		$supplier_cache_file = $supplier_cache->get($supplier_cache_filename);
		if ($force_flush || $supplier_cache_file === FALSE) {
			//读取数据库
			$dao = new Dao_Impl();
			$suppliers = $dao->db
					->get_results(
							'SELECT a.category_name,a.id AS category_id,b.supplier_name,b.id AS supplier_id,c.in_invoice_tax_rate,c.supplier_type
FROM 
new_supplier_category a
LEFT JOIN new_supplier b
ON a.supplier_id=b.id
LEFT JOIN new_supplier_info c
ON b.id=c.supplier_id
WHERE a.isok=1 AND c.isok=1');

			if ($suppliers !== NULL) {
				$datas = array();
				foreach ($suppliers as $supplier) {
					if ($datas['supplier'][$supplier->supplier_id] === NULL) {
						$datas['supplier'][$supplier->supplier_id] = array(
								'sn' => $supplier->supplier_name,
								'iitr' => $supplier->in_invoice_tax_rate,
								'st' => $supplier->supplier_type);
					}

					if ($datas['category'][$supplier->category_id] === NULL) {
						$datas['category'][$supplier->category_id] = $supplier
								->category_name;
					}

					if ($datas['supplier_category'][$supplier->supplier_id]
							=== NULL
							|| !in_array($supplier->category_id,
									$datas['supplier_category'][$supplier
											->supplier_id], TRUE)) {
						$datas['supplier_category'][$supplier->supplier_id][] = $supplier
								->category_id;
					}

					/*
					$datas['category_rebate'][$supplier->category_id][$supplier
					        ->rebate_id] = array(
					        'rebate' => $supplier->rebate,
					        'starttime' => $supplier->starttime,
					        'endtime' => $supplier->endtime);
					 */
				}
				$supplier_cache->set($supplier_cache_filename, $datas);
			}
		}

		return $supplier_cache->get($supplier_cache_filename);
	}

	public function __construct($fields = array()) {
		parent::__construct();
		if (!empty($fields)) {
			foreach ($this as $key => $value) {
				if ($fields[$key] !== NULL
						&& !in_array($key, array('has_supplier_permission'),
								TRUE)) {
					$this->$key = $fields[$key];
				}
			}
		}

		if (in_array($this->getUsername(),
				$GLOBALS['manager_finance_permission'], TRUE)
				|| intval($this->getBelong_dep()) === 2) {
			$this->has_supplier_permission = TRUE;
		}
	}

	public function new_update_supplier() {
		if ($this->has_supplier_permission) {
			if ($this->validate_form_value('new_update')) {
				$success = TRUE;
				$error = '';
				$this->db->query('BEGIN');

				$row = $this->db
						->get_row(
								'SELECT id,supplier_name FROM new_supplier WHERE id='
										. intval($this->id)
										. ' AND isok=1 FOR UPDATE');
				if ($row === NULL) {
					$success = FALSE;
					$error = '供应商信息选择有误';
				} else {
					if ($row->supplier_name !== $this->supplier_name) {
						//检验新的supplier name是否唯一
						$nowrow = $this->db
								->get_row(
										'SELECT supplier_name FROM
(
SELECT supplier_name FROM new_supplier_apply WHERE isok<>-1 FOR UPDATE
UNION
SELECT supplier_name FROM new_supplier WHERE isok=1 FOR UPDATE
) a WHERE a.supplier_name="' . $this->supplier_name . '"');
						if ($nowrow !== NULL) {
							$success = FALSE;
							$error = '已有同名的供应商信息或已申请';
						}
					}

					if ($success) {
						//if ($row->supplier_name !== $this->supplier_name) {
						$update_result = $this->db
								->query(
										'UPDATE new_supplier SET '
												. ($row->supplier_name
														!== $this
																->supplier_name ? 'supplier_name="'
																. $this
																		->supplier_name
																. '",' : '')
												. 'parentid='
												. (empty($this->parentid) ? 0
														: intval(
																$this->parentid))
												. ' WHERE id='
												. intval($this->id));

						if ($update_result === FALSE) {
							$success = FALSE;
							$error = '修改供应商信息失败，错误代码1';
						}
						//}

						if ($success) {
							$update_result = $this->db
									->query(
											'UPDATE new_supplier_info SET url="'
													. $this->url
													. '",in_invoice_tax_rate='
													. $this
															->in_invoice_tax_rate
													. ',deduction='
													. intval($this->deduction)
													. ',supplier_type='
													. intval(
															$this
																	->supplier_type)
													. ' WHERE supplier_id='
													. intval($this->id));
							if ($update_result === FALSE) {
								$success = FALSE;
								$error = '修改供应商信息失败，错误代码2';
							}
						}
					}
				}

				if ($success) {
					$this->db->query('COMMIT');
					self::getSupplierInstance(TRUE);
				} else {
					$this->db->query('ROLLBACK');
				}
				return array('status' => $success ? 'success' : 'error',
						'message' => $success ? '更新供应商信息成功' : $error);
			}
			return array('status' => 'error', 'message' => $this->errors);
		}
		return array('status' => 'error', 'message' => NO_RIGHT_TO_DO_THIS);
	}

	public function update_supplier() {
		if (!$this->has_supplier_permission) {
			return array('status' => 'error', 'message' => NO_RIGHT_TO_DO_THIS);
		}
		if ($this->validate_form_value('update')) {
			$success = TRUE;
			$error = '';
			$this->db->query('BEGIN');
			//supplier
			$update_result = $this->db
					->query(
							'UPDATE supplier SET parentid='
									. intval($this->parentid)
									. ' WHERE id=(SELECT supplier_id FROM supplier_info WHERE id='
									. intval($this->infoid) . ')');
			if ($update_result === FALSE) {
				$success = FALSE;
				$error = '更新供应商信息失败，错误代码1';
			} else {
				//supplier info
				$update_result = $this->db
						->query(
								'UPDATE supplier_info SET rebate='
										. $this->rebate
										. ',in_invoice_tax_rate='
										. $this->in_invoice_tax_rate
										. ',deduction='
										. intval($this->deduction)
										. ',supplier_type='
										. intval($this->supplier_type)
										. ' WHERE id=' . intval($this->infoid));
				if ($update_result === FALSE) {
					$success = FALSE;
					$error = '更新供应商信息失败，错误代码2';
				}
			}

			if ($success) {
				$this->db->query('COMMIT');
				//if(intval($this->parentid) === 0){
				//	$this->_get_parent_suppliers(TRUE);
				//}
				self::getSupplierInstance(TRUE);
			} else {
				$this->db->query('ROLLBACK');
			}

			return array('status' => $success ? 'success' : 'error',
					'message' => $success ? '更新供应商信息成功' : $error);
		}
		return array('status' => 'error', 'message' => $this->errors);
	}

	public function get_edit_supplier_html() {
		if ($this->has_supplier_permission) {
			$row = $this->db
					->get_row(
							'SELECT b.supplier_id,a.supplier_name,b.url,b.in_invoice_tax_rate,b.deduction,b.supplier_type,b.dids,a.parentid FROM new_supplier a LEFT JOIN new_supplier_info b ON a.id=b.supplier_id WHERE a.id='
									. intval($this->id)
									. ' AND a.isok=1 AND b.isok=1');
			if ($row === NULL) {
				return User::no_object('没有该供应商信息');
			}
			$buf = file_get_contents(
					TEMPLATE_PATH . 'finance/supplier/supplier_edit.tpl');
			return str_replace(
					array('[LEFT]', '[TOP]', '[VCODE]', '[SUPPLIERNAME]',
							'[URL]', '[INVOICETAXTRATE]', '[ID]',
							'[DEDUCTION1]', '[DEDUCTION2]', '[SUPPLIERTYPE1]',
							'[SUPPLIERTYPE2]', '[PARENTSUPPLIERS]',
							'[SUPPLIERJS]', '[ISREQUIRED]','[DIDS]' ,'[BASE_URL]'),
					array($this->get_left_html(), $this->get_top_html(),
							$this->get_vcode(), $row->supplier_name, $row->url,
							$row->in_invoice_tax_rate, $this->id,
							self::_get_deduction_radio(0,
									intval($row->deduction)),
							self::_get_deduction_radio(1,
									intval($row->deduction)),
							self::_get_supplier_type_radio(1,
									intval($row->supplier_type)),
							self::_get_supplier_type_radio(2,
									intval($row->supplier_type)),
							$this->_get_parent_suppliers($row->supplier_id),
							'$("#parentid").val(' . $row->parentid . ');',
							'optional',$this->get_upload_files($row->dids), BASE_URL), $buf);
		} else {
			return User::no_permission();
		}
	}

	public function get_import_supplier_html() {
		if ($this->has_supplier_permission) {
			$buf = file_get_contents(
					TEMPLATE_PATH . 'finance/supplier/supplier_import.tpl');
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
		//第一列 供应商名称
		if (!self::validate_field_not_null($infos[0])
				|| !self::validate_field_not_empty($infos[0])) {
			$errors[] = '第' . $line . '行，第1列【供应商名称】不能为空';
			$isok = $isok ? FALSE : $isok;
		} else if (!self::validate_field_max_length($infos[0], 255)) {
			$errors[] = '第' . $line . '行，第1列【供应商名称】长度最多255个字符';
			$isok = $isok ? FALSE : $isok;
		}

		//第二列 网址，如果是媒体，必填，外包，选填
		//网址不必要 2015.11.12
		if (self::validate_field_not_empty($infos[1])) {
			if (!self::validate_field_max_length($infos[1], 1024)) {
				$errors[] = '第' . $line . '行，第2列【网址】最多1024个字符';
				$isok = $isok ? FALSE : $isok;
			} else if (!Validate_Util::my_is_url($infos[1])) {
				$errors[] = '第' . $line . '行，第2列【网址】不是有效的网址';
				$isok = $isok ? FALSE : $isok;
			}
		}

		//第三列，是否有抵扣联
		if (!self::validate_field_not_null($infos[2])) {
			$errors[] = '第' . $line . '行，第3列【是否有抵扣联】不能为空';
			$isok = $isok ? FALSE : $isok;
		} else if (!in_array($infos[2], array('有', '无', '是', '否'), TRUE)) {
			$errors[] = '第' . $line . '行，第3列【是否有抵扣联】输入有误';
			$isok = $isok ? FALSE : $isok;
		}

		//第四列 进票税率
		if (!self::validate_field_not_null($infos[3])) {
			$errors[] = '第' . $line . '行，第4列【进票税率】不能为空';
			$isok = $isok ? FALSE : $isok;
		} else if (!Validate_Util::my_is_float($infos[3])) {
			$errors[] = '第' . $line . '行，第4列【进票税率】不是有效的数值';
			$isok = $isok ? FALSE : $isok;
		} else if ($infos[3] > 100 || $infos[3] < 0) {
			$errors[] = '第' . $line . '行，第4列【进票税率】不是有效的数值';
			$isok = $isok ? FALSE : $isok;
		} else {
			//如果无抵扣联，进票税率默认0
			if (in_array($infos[2], array('无', '否'), TRUE) && $infos[3] != 0) {
				$errors[] = '第' . $line . '行，当无抵扣联时，第4列【进票税率】必须为0';
				$isok = $isok ? FALSE : $isok;
			} else if (in_array($infos[2], array('有', '是'), TRUE)
					&& $infos[3] == 0) {
				$errors[] = '第' . $line . '行，当有抵扣联时，第4列【进票税率】必须不为0';
				$isok = $isok ? FALSE : $isok;
			}
		}

		//第五列，供应商类型
		if (!self::validate_field_not_null($infos[4])) {
			$errors[] = '第' . $line . '行，第5列【供应商类型】不能为空';
			$isok = $isok ? FALSE : $isok;
		} else if (!in_array($infos[4], array('媒体', '外包'), TRUE)) {
			$errors[] = '第' . $line . '行，第5列【供应商类型】输入有误';
			$isok = $isok ? FALSE : $isok;
		}

		//第六列 实际供应商对应
		/*
		if (self::validate_field_not_empty($infos[5])) {
		    $row = $this->db
		            ->get_row(
		                    'SELECT id FROM new_supplier WHERE supplier_name="'
		                            . $infos[5] . '" FOR UPDATE');
		    if ($row === NULL) {
		        $errors[] = '第' . $line . '行，第6列【实际供应商对应】输入有误';
		        $isok = $isok ? FALSE : $isok;
		    }
		}
		 */

		return $isok;
	}

	public function import_supplier($file) {
		if (!$this->has_supplier_permission) {
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

			//应该是6列，行数>1
			if ($sum_cols_count !== 6) {
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

						//如果parent有，则先查出parentid;
						$parentid = 0;
						if (!empty($infos[$i][5])) {
							$id = $this->db
									->get_var(
											'SELECT id FROM new_supplier WHERE supplier_name="'
													. trim($infos[$i][5])
													. '" FOR UPDATE');
							if ($id > 0) {
								$parentid = $id;
							}
						}

						$row = $this->db
								->get_row(
										'SELECT id,parentid FROM new_supplier WHERE supplier_name="'
												. trim($infos[$i][0])
												. '" FOR UPDATE');
						if ($row === NULL) {
							//没有，新增
							$result = $this->db
									->query(
											'INSERT INTO new_supplier(supplier_name,isok,parentid) VALUE("'
													. trim($infos[$i][0])
													. '",1,' . $parentid . ')');
							if ($result === FALSE) {
								$db_ok = FALSE;
								$errors[] = '第' . $i . '行供应商信息导入失败，错误代码1';
							} else {
								$supplier_id = $this->db->insert_id;
								$result = $this->db
										->query(
												'INSERT INTO new_supplier_info(supplier_id,url,in_invoice_tax_rate,deduction,supplier_type,addtime,isok) VALUE('
														. $supplier_id . ',"'
														. trim($infos[$i][1])
														. '",'
														. trim($infos[$i][3])
														. ','
														. (in_array(
																trim(
																		$infos[$i][2]),
																array('是', '有'),
																TRUE) ? 1 : 0)
														. ','
														. (trim($infos[$i][4])
																=== '媒体' ? 1 : 2)
														. ',now(),1)');
								if ($result === FALSE) {
									$db_ok = FALSE;
									$errors[] = '第' . $i . '行供应商信息导入失败，错误代码2';
								}
							}
						} else {
							$supplier_id = $row->id;
						}

						if ($db_ok) {
							$this->db->query('COMMIT');
						} else {
							$this->db->query('ROLLBACK');
						}
					} else {
						$this->db->query('COMMIT');
					}
				}
			}
			self::getSupplierInstance(TRUE);
			return array('status' => 'success',
					'message' => empty($errors) ? '导入成功' : $errors);
		} else {
			return array('status' => 'error', 'message' => '上传的文件不存在');
		}
	}

	private function validate_form_value($action) {
		$errors = array();
		if (in_array($action,
				array('cancel', 'update', 'apply', 'reaudit', 'audit',
						'supplier_industy', 'industry_cancel',
						'industry_update', 'new_add', 'new_audit',
						'new_reaudit', 'new_update', 'new_supplier_industy',
						'new_industry_update', 'supplier_category',
						'new_category_update', 'category_cancel'), TRUE)) {
			if ($action === 'supplier_category'
					|| $action === 'new_category_update') {
				if ($action === 'supplier_category') {
					//supplier_name
					if (!self::validate_field_not_null($this->supplier_name)
							|| !self::validate_field_not_empty(
									$this->supplier_name)) {
						$errors[] = '供应商名称必须选择';
					}
				}
				//产品分类名称
				if (!self::validate_field_not_null($this->category_name)
						|| !self::validate_field_not_empty($this->category_name)) {
					$errors[] = '产品分类名称必须输入';
				} else if (!self::validate_field_max_length(
						$this->category_name, 255)) {
					$errors[] = '产品分类名称最多255个字符';
				}

				if ($action === 'new_category_update') {
					if (!self::validate_id(intval($this->category_id))) {
						$errors[] = '产品分类选择有误';
					}
				}

				/*
				//返点比例
				if (!self::validate_field_not_null($this->rebate)
				        || !self::validate_field_not_empty($this->rebate)) {
				    $errors[] = '返点比例不能为空';
				} else if (!Validate_Util::my_is_float($this->rebate)) {
				    $errors[] = '返点比例不是有效的数值';
				} else if ($this->rebate > 100 || $this->rebate < 0) {
				    $errors[] = '返点比例不是有效的数值';
				}
				
				//开始时间
				if (!self::validate_field_not_null($this->starttime)
				        || !self::validate_field_not_empty($this->starttime)) {
				    $errors[] = '开始时间不能为空';
				} else if (strtotime($this->starttime) === FALSE) {
				    $errors[] = '开始时间不是有效的时间值';
				}
				
				//结束时间
				if (!self::validate_field_not_null($this->endtime)
				        || !self::validate_field_not_empty($this->endtime)) {
				    $errors[] = '结束时间不能为空';
				} else if (strtotime($this->endtime) === FALSE) {
				    $errors[] = '结束时间不是有效的时间值';
				} else if (strtotime($this->endtime)
				        <= strtotime($this->starttime)) {
				    $errors[] = '结束时间必须晚于开始时间';
				}
				 */
			} else if ($action === 'new_supplier_industy'
					|| $action === 'new_industry_update') {
				if ($action === 'new_supplier_industy') {
					//supplier_name
					if (!self::validate_field_not_null($this->supplier_name)
							|| !self::validate_field_not_empty(
									$this->supplier_name)) {
						$errors[] = '媒体简称必须选择';
					}
				}
				//客户行业分类名称
				if (!self::validate_field_not_null($this->industry_name)
						|| !self::validate_field_not_empty($this->industry_name)) {
					$errors[] = '客户行业分类名称必须输入';
				} else if (!self::validate_field_max_length(
						$this->industry_name, 255)) {
					$errors[] = '客户行业分类名称最多255个字符';
				}

				if ($action === 'new_industry_update') {
					if (!self::validate_id(intval($this->industry_id))) {
						$errors[] = '客户行业分类选择有误';
					}
				}

			} else if ($action === 'cancel') {
				if (!self::validate_id($this->id)) {
					$errors[] = '供应商信息选择有误';
				}

				if (!in_array(intval($this->recover), array(0, 1), TRUE)) {
					$errors[] = '供应商更新状态选择有误';
				}
			} else if ($action === 'new_add' || $action === 'new_reaudit'
					|| $action === 'new_update') {
				if ($action === 'new_reaudit') {
					if (!self::validate_id($this->id)) {
						$errors[] = '供应商信息选择有误';
					}
				}

				//供应商名称
				if (!self::validate_field_not_null($this->supplier_name)
						|| !self::validate_field_not_empty($this->supplier_name)) {
					$errors[] = '供应商名称必须输入';
				} else if (!self::validate_field_max_length(
						$this->supplier_name, 255)) {
					$errors[] = '供应商名称最多255个字符';
				}

				//供应商类型
				if (!in_array(intval($this->supplier_type), array(1, 2), TRUE)) {
					$errors[] = '供应商类型选择有误';
				}

				//URL非必须
				if (!empty($this->url)) {
					if (!self::validate_field_max_length($this->url, 1024)) {
						$errors[] = '供应商网址最多1024个字符';
					} else if (!Validate_Util::my_is_url($this->url)) {
						$errors[] = '供应商网址不是一个有效的网址';
					}
				}

				//是否有抵扣联
				if (!in_array(intval($this->deduction), array(0, 1), TRUE)) {
					$errors[] = '是否有抵扣联选择有误';
				}

				//进票税率
				if (!self::validate_field_not_null($this->in_invoice_tax_rate)) {
					$errors[] = '进票税率不能为空';
				} else if (!Validate_Util::my_is_float(
						$this->in_invoice_tax_rate)) {
					$errors[] = '进票税率不是有效的数值';
				} else if ($this->in_invoice_tax_rate > 100
						|| $this->in_invoice_tax_rate < 0) {
					$errors[] = '进票税率不是有效的数值';
				} else {
					if (intval($this->deduction) === 0
							&& floatval($this->in_invoice_tax_rate)
									!== floatval(0)) {
						$errors[] = '无抵扣联，进票税率为0';
					} else if (intval($this->deduction) !== 0
							&& floatval($this->in_invoice_tax_rate)
									=== floatval(0)) {
						$errors[] = '有抵扣联，进票税率不能为0';
					}
				}

				//附件
				if ($action !== 'new_update') {
					if (self::validate_field_not_empty($this->dids)
							&& self::validate_field_not_null($this->dids)
							&& $this->dids !== '^') {
						if (!String_Util::start_with($this->dids, '^')
								|| !String_Util::end_with($this->dids, '^')) {
							$errors[] = '附件上传有误';
						} else if (!self::validate_field_max_length(
								$this->dids, 500)) {
							$errors[] = '附件选择过多';
						} else {
							$dids = $this->dids;
							$this->dids = substr($dids, 1, strlen($dids) - 2);
						}
					} else {
						$this->dids = '';
					}
				}

			} else if ($action === 'apply' || $action === 'reaudit') {
				//ID
				if ($action === 'reaudit') {
					if (!self::validate_id($this->id)) {
						$errors[] = '供应商信息选择有误';
					}
				}
				//供应商名称
				if (!self::validate_field_not_null($this->supplier_name)
						|| !self::validate_field_not_empty($this->supplier_name)) {
					$errors[] = '供应商名称必须输入';
				} else if (!self::validate_field_max_length(
						$this->supplier_name, 255)) {
					$errors[] = '供应商名称最多255个字符';
				}

				//供应商网址
				if (!self::validate_field_not_null($this->url)
						|| !self::validate_field_not_empty($this->url)) {
					$errors[] = '供应商网址必须输入';
				} else if (!self::validate_field_max_length($this->url, 1024)) {
					$errors[] = '供应商网址最多1024个字符';
				} else if (!Validate_Util::my_is_url($this->url)) {
					$errors[] = '供应商网址不是一个有效的网址';
				}

				//供应商分类
				if (self::validate_field_not_empty($this->category)
						&& !self::validate_field_max_length($this->category,
								100)) {
					$errors[] = '供应商分类最多500个字符';
				}

				//是否有抵扣联
				if (!in_array(intval($this->deduction), array(0, 1), TRUE)) {
					$errors[] = '是否有抵扣联选择有误';
				}

				//返点比例
				if (self::validate_field_not_null($this->rebate)
						&& self::validate_field_not_empty($this->rebate)) {
					if (!Validate_Util::my_is_float($this->rebate)) {
						$errors[] = '返点率不是有效的数值';
					} else if ($this->rebate > 100 || $this->rebate < 0) {
						$errors[] = '返点率不是有效的数值';
					}
				} else {
					$this->rebate = 0;
				}

				//进票税率
				if (!self::validate_field_not_null($this->in_invoice_tax_rate)
						|| !self::validate_field_not_empty(
								$this->in_invoice_tax_rate)) {
					$errors[] = '进票税率不能为空';
				} else if (!Validate_Util::my_is_float(
						$this->in_invoice_tax_rate)) {
					$errors[] = '进票税率不是有效的数值';
				} else if ($this->in_invoice_tax_rate > 100
						|| $this->in_invoice_tax_rate < 0) {
					$errors[] = '进票税率不是有效的数值';
				}

				//供应商类型
				if (!in_array(intval($this->supplier_type), array(1, 2), TRUE)) {
					$errors[] = '供应商类型选择有误';
				}

				//是否二级代理
				if (!in_array(intval($this->isagent2), array(0, 1), TRUE)) {
					$errors[] = '是否二级代理选择有误';
				}

				//附件
				if (self::validate_field_not_empty($this->dids)
						&& self::validate_field_not_null($this->dids)
						&& $this->dids !== '^') {
					if (!String_Util::start_with($this->dids, '^')
							|| !String_Util::end_with($this->dids, '^')) {
						$errors[] = '附件上传有误';
					} else if (!self::validate_field_max_length($this->dids,
							500)) {
						$errors[] = '附件选择过多';
					} else {
						$dids = $this->dids;
						$this->dids = substr($dids, 1, strlen($dids) - 2);
					}
				} else {
					$this->dids = '';
				}

			} else if ($action === 'new_audit') {
				//id
				if (!self::validate_id($this->id)) {
					$errors[] = '供应商信息选择有误';
				}

				//audit_result
				if (!in_array(intval($this->audit_result), array(-1, 1), TRUE)) {
					$errors[] = '供应商审核结果选择有误';
				}

				//remark
				if (self::validate_field_not_empty($this->remark)
						&& !self::validate_field_max_length($this->remark, 500)) {
					$errors[] = '审核留言最多500个字符';
				}

				if (!empty($this->parentid)) {
					if (!self::validate_id(intval($this->parentid))) {
						$errors[] = '实际供应商对应有误';
					}
				}

			} else if ($action === 'audit') {
				//id
				if (!self::validate_id($this->id)) {
					$errors[] = '供应商信息选择有误';
				}

				//audit_result
				if (!in_array(intval($this->audit_result), array(-1, 1), TRUE)) {
					$errors[] = '供应商审核结果选择有误';
				}

				//remark
				if (self::validate_field_not_empty($this->remark)
						&& !self::validate_field_max_length($this->remark, 500)) {
					$errors[] = '审核留言最多500个字符';
				}
			} else if ($action === 'supplier_industy'
					|| $action === 'industry_update') {
				if ($action === 'industry_update') {
					if (!self::validate_id(intval($this->industry_id))) {
						$errors[] = '客户行业分类选择有误';
					}
				}

				//supplier_id
				if (!self::validate_id($this->supplier_id)) {
					$errors[] = '供应商信息选择有误';
				}

				//客户行业分类名称
				if (!self::validate_field_not_null($this->industry_name)
						|| !self::validate_field_not_empty($this->industry_name)) {
					$errors[] = '客户行业分类名称必须输入';
				} else if (!self::validate_field_max_length(
						$this->industry_name, 255)) {
					$errors[] = '客户行业分类名称最多255个字符';
				}

				//返点比例
				if (!self::validate_field_not_null($this->rebate)
						|| !self::validate_field_not_empty($this->rebate)) {
					$errors[] = '返点比例不能为空';
				} else if (!Validate_Util::my_is_float($this->rebate)) {
					$errors[] = '返点比例不是有效的数值';
				} else if ($this->rebate > 100 || $this->rebate < 0) {
					$errors[] = '返点比例不是有效的数值';
				}

				//进票税率
				if (!self::validate_field_not_null($this->in_invoice_tax_rate)
						|| !self::validate_field_not_empty(
								$this->in_invoice_tax_rate)) {
					$errors[] = '进票税率不能为空';
				} else if (!Validate_Util::my_is_float(
						$this->in_invoice_tax_rate)) {
					$errors[] = '进票税率不是有效的数值';
				} else if ($this->in_invoice_tax_rate > 100
						|| $this->in_invoice_tax_rate < 0) {
					$errors[] = '进票税率不是有效的数值';
				}

			} else if ($action === 'industry_cancel'
					|| $action === 'category_cancel') {
				if ($action === 'industry_cancel') {
					if (!self::validate_id(intval($this->industry_id))) {
						$errors[] = '客户行业分类信息选择有误';
					}
				} else {
					if (!self::validate_id(intval($this->category_id))) {
						$errors[] = '供应商产品分类信息选择有误';
					}
				}

				if (!in_array(intval($this->recover), array(-1, 1), TRUE)) {
					$errors[] = '客户行业分类状态选择有误';
				}
			} else {
				if (!self::validate_id(intval($this->infoid))) {
					$errors[] = '供应商信息选择有误';
				} else {
					if ($action === 'update') {
						$row = $this->db
								->get_row(
										'SELECT a.id FROM supplier a LEFT JOIN supplier_info b ON a.id=b.supplier_id WHERE b.id='
												. intval($this->infoid)
												. ' AND a.isok=1 AND b.isok=1');
						if ($row === NULL) {
							$errors[] = '供应商信息选择有误';
						}

						if (!self::validate_field_not_null($this->rebate)) {
							$errors[] = '返点率不能为空';
						} else if (!Validate_Util::my_is_float($this->rebate)) {
							$errors[] = '返点率不是有效的数值';
						} else if ($this->rebate > 100 || $this->rebate < 0) {
							$errors[] = '返点率不是有效的数值';
						}

						if (!self::validate_field_not_null(
								$this->in_invoice_tax_rate)) {
							$errors[] = '进票税率不能为空';
						} else if (!Validate_Util::my_is_float(
								$this->in_invoice_tax_rate)) {
							$errors[] = '进票税率不是有效的数值';
						} else if ($this->in_invoice_tax_rate > 100
								|| $this->in_invoice_tax_rate < 0) {
							$errors[] = '进票税率不是有效的数值';
						}

						//抵扣联
						if (!in_array(intval($this->deduction), array(0, 1),
								TRUE)) {
							$errors[] = '是否有抵扣联选择有误';
						}

						//供应商类型
						if (!in_array(intval($this->supplier_type),
								array(1, 2), TRUE)) {
							$errors[] = '供应商类型选择有误';
						}
					}
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

	public function category_cancel() {
		if ($this->has_supplier_permission) {
			if ($this->validate_form_value('category_cancel')) {
				$success = TRUE;
				$error = '';
				$this->db->query('BEGIN');

				$isok = intval($this->recover) === 1 ? 1 : -1;
				$update_result = $this->db
						->query(
								'UPDATE new_supplier_category SET isok='
										. $isok . ' WHERE id='
										. intval($this->category_id));
				if ($update_result === FALSE) {
					$success = FALSE;
					$error = '更新供应商产品分类状态失败';
				}

				if ($success) {
					$this->db->query('COMMIT');
				} else {
					$this->db->query('ROLLBACK');
				}
				return array('status' => $success ? 'success' : 'error',
						'message' => $success ? '更新供应商产品分类状态成功' : $error);
			}
			return array('status' => 'error',
					'message' => implode("\n", $this->errors));
		}
		return array('status' => 'error', 'message' => NO_RIGHT_TO_DO_THIS);
	}

	public function industry_cancel() {
		if ($this->has_supplier_permission) {
			if ($this->validate_form_value('industry_cancel')) {
				$success = TRUE;
				$error = '';
				$this->db->query('BEGIN');

				$isok = intval($this->recover) === 1 ? 1 : -1;
				$update_result = $this->db
						->query(
								'UPDATE new_supplier_industry SET isok='
										. $isok . ' WHERE id='
										. intval($this->industry_id));
				if ($update_result === FALSE) {
					$success = FALSE;
					$error = '更新客户行业分类状态失败';
				}

				if ($success) {
					$this->db->query('COMMIT');
				} else {
					$this->db->query('ROLLBACK');
				}

				self::getIndustryInstance(TRUE);
				return array('status' => $success ? 'success' : 'error',
						'message' => $success ? '更新客户行业分类状态成功' : $error);
			}
			return array('status' => 'error',
					'message' => implode("\n", $this->errors));
		}
		return array('status' => 'error', 'message' => NO_RIGHT_TO_DO_THIS);
	}

	public function cancel() {
		if ($this->has_supplier_permission) {
			if ($this->validate_form_value('cancel')) {
				$success = TRUE;
				$error = '';
				$this->db->query('BEGIN');

				$isok = intval($this->recover) === 1 ? 1 : -1;

				$row = $this->db
						->get_row(
								'SELECT parentid FROM new_supplier WHERE id='
										. intval($this->id) . ' FOR UPDATE');
				if ($row !== NULL) {
					if (intval($row->parentid) === 0
							&& intval($this->recover) === 0) {
						//需要查看是否有别的供应商用了别名
						$count = $this->db
								->get_var(
										'SELECT COUNT(*) FROM new_supplier WHERE parentid='
												. intval($this->id)
												. ' AND isok=1 FOR UPDATE');
						if ($count > 0) {
							$success = FALSE;
							$error = '有其他供应商名称是该供应商的别名，不可撤销';
						}
					}
				} else {
					$success = FALSE;
					$error = '没有该供应商';
				}

				if ($success) {
					$update_result = $this->db
							->query(
									'UPDATE new_supplier SET isok=' . $isok
											. ' WHERE id=' . intval($this->id));
					if ($update_result === FALSE) {
						$success = FALSE;
						$error = '更新供应商状态失败，错误代码1';
					} else {
						$update_result = $this->db
								->query(
										'UPDATE new_supplier_info SET isok='
												. $isok . ' WHERE supplier_id='
												. intval($this->id));
						if ($update_result === FALSE) {
							$success = FALSE;
							$error = '更新供应商状态失败，错误代码2';
						}
					}
				}

				if ($success) {
					$this->db->query('COMMIT');
					self::getSupplierInstance(TRUE);
				} else {
					$this->db->query('ROLLBACK');
				}
				return array('status' => $success ? 'success' : 'error',
						'message' => $success ? '更新供应商状态成功' : $error);
			}
			return array('status' => 'error',
					'message' => implode("\n", $this->errors));
		}
		return array('status' => 'error', 'message' => NO_RIGHT_TO_DO_THIS);

	}

	public static function get_supplier_category($force_flush, $supplier,
			$selected = NULL) {
		$instance = self::getInstance($force_flush);
		$result = '<option value="">请选择产品分类</option>';
		if ($instance !== NULL) {
			$instance = $instance['category'][$supplier];
			if ($instance !== NULL) {
				foreach ($instance as $key => $value) {
					foreach ($value as $k => $v) {
						$result .= '<option '
								. ($selected !== NULL
										&& $selected
												=== $key . '-' . intval($k) ? 'selected'
										: '') . ' value="' . $key . '-'
								. intval($k) . '">'
								. ($key === 'no' ? '*无产品分类' : $key) . '('
								. (intval($k) === 0 ? '非二级代理' : '二级代理')
								. ')</option>';
					}
				}
			}
		}

		return $result;
	}

	public function get_supplier_apply_html() {
		$buf = file_get_contents(
				TEMPLATE_PATH . 'finance/supplier/supplier_apply.tpl');
		return str_replace(
				array('[LEFT]', '[TOP]', '[VCODE]', '[VALIDATE_TYPE]',
						'[VALIDATE_SIZE]', '[BASE_URL]'),
				array($this->get_left_html(), $this->get_top_html(),
						$this->get_vcode(),
						implode(',', $GLOBALS['defined_upload_validate_type']),
						UPLOAD_FILE_MAX_SIZE / (1024 * 1024), BASE_URL), $buf);
	}

	public function new_supplier_apply() {
		if ($this->validate_form_value('new_add')) {
			$success = TRUE;
			$error = '';
			$this->db->query('BEGIN');

			//检查是否有同名的
			/*
			$row = $this->db
			        ->get_row(
			                'SELECT supplier_name FROM
			(
			SELECT supplier_name FROM new_supplier_apply WHERE isok<>-1 FOR UPDATE
			UNION
			SELECT supplier_name FROM new_supplier WHERE isok=1 FOR UPDATE
			) a WHERE a.supplier_name="' . $this->supplier_name . '"');
			 */
			$rows = $this->db
					->get_results(
							'SELECT supplier_name FROM
(
SELECT supplier_name FROM new_supplier_apply WHERE supplier_name="'
									. $this->supplier_name
									. '"
UNION
SELECT supplier_name FROM new_supplier WHERE supplier_name="'
									. $this->supplier_name . '"		
) a');

			if ($rows !== NULL) {
				$success = FALSE;
				$error = '已有同名的供应商信息或已申请';
			} else {
				$result = $this->db
						->query(
								'INSERT INTO new_supplier_apply(supplier_name,url,deduction,in_invoice_tax_rate,supplier_type,dids,apply_userid,isok,addtime,step) VALUE("'
										. $this->supplier_name . '","'
										. $this->url . '",'
										. intval($this->deduction) . ','
										. $this->in_invoice_tax_rate . ','
										. intval($this->supplier_type) . ',"'
										. $this->dids . '",' . $this->getUid()
										. ',0,now(),0)');
				if ($result === FALSE) {
					$success = FALSE;
					$error = '供应商申请失败';
				}
			}

			if ($success) {
				$this->db->query('COMMIT');
			} else {
				$this->db->query('ROLLBACK');
			}

			return array('status' => $success ? 'success' : 'error',
					'message' => $success ? '提交供应商申请已成功，请等待财务部批准' : $error);
		}
		return array('status' => 'error', 'message' => $this->errors);
	}

	public function supplier_apply() {
		if ($this->validate_form_value('apply')) {
			$success = TRUE;
			$error = '';
			$this->db->query('BEGIN');

			//检查是否已有相同名字的正式或申请中的供应商(名字+ 分类+是否二级代理)
			$resuts = $this->db
					->get_results(
							'SELECT z.id AS id FROM (SELECT a.id,a.category,a.isagent2,b.supplier_name FROM supplier_info a LEFT JOIN supplier b ON a.supplier_id=b.id WHERE b.isok=1) z WHERE z.supplier_name="'
									. $this->supplier_name
									. '" AND z.category="' . $this->category
									. '" AND z.isagent2='
									. intval($this->isagent2)
									. ' FOR UPDATE UNION ALL SELECT id FROM supplier_apply WHERE supplier_name="'
									. $this->supplier_name . '" AND category="'
									. $this->category . '" AND isagent2='
									. intval($this->isagent2) . ' FOR UPDATE');

			if ($resuts !== NULL) {
				$success = FALSE;
				$error = '已有同名的供应商信息或已申请';
			} else {
				$insert_result = $this->db
						->query(
								'INSERT INTO supplier_apply(supplier_name,url,category,deduction,rebate,in_invoice_tax_rate,supplier_type,dids,apply_userid,isok,addtime,isagent2) VALUE("'
										. $this->supplier_name . '","'
										. $this->url . '","' . $this->category
										. '",' . $this->deduction . ','
										. $this->rebate . ','
										. $this->in_invoice_tax_rate . ','
										. $this->supplier_type . ',"'
										. $this->dids . '",' . $this->getUid()
										. ',0,now(),' . intval($this->isagent2)
										. ')');
				if ($insert_result === FALSE) {
					$success = FALSE;
					$error = '供应商申请失败';
				}
			}

			if ($success) {
				$this->db->query('COMMIT');
			} else {
				$this->db->query('ROLLBACK');
			}

			return array('status' => $success ? 'success' : 'error',
					'message' => $success ? '提交供应商申请已成功，请等待财务部批准' : $error);
		}
		return array('status' => 'error', 'message' => $this->errors);
	}

	public function new_supplier_reaudit() {
		if ($this->validate_form_value('new_reaudit')) {
			$success = TRUE;
			$error = '';
			$this->db->query('BEGIN');

			$row = $this->db
					->get_row(
							'SELECT id,supplier_name FROM new_supplier_apply WHERE id='
									. intval($this->id) . ' AND apply_userid='
									. $this->getUid()
									. ' AND isok=-1 FOR UPDATE');
			if ($row === NULL) {
				$success = FALSE;
				$error = '供应商信息选择有误';
			} else {
				if ($row->supplier_name !== $this->supplier_name) {
					//检验新的supplier name是否唯一
					$nowrow = $this->db
							->get_row(
									'SELECT supplier_name FROM
(
SELECT supplier_name FROM new_supplier_apply
UNION
SELECT supplier_name FROM new_supplier
) a WHERE a.supplier_name="' . $this->supplier_name . '"');
					if ($nowrow !== NULL) {
						$success = FALSE;
						$error = '已有同名的供应商信息或已申请';
					}
				}

				if ($success) {
					$update_result = $this->db
							->query(
									'UPDATE new_supplier_apply SET '
											. ($row->supplier_name
													!== $this->supplier_name ? 'supplier_name="'
															. $this
																	->supplier_name
															. '",' : '')
											. 'url="' . $this->url
											. '",in_invoice_tax_rate='
											. $this->in_invoice_tax_rate
											. ',deduction='
											. intval($this->deduction)
											. ',supplier_type='
											. intval($this->supplier_type)
											. ',dids="' . $this->dids
											. '",isok=0,remark=NULL,audittime=NULL,audit_userid=NULL,step=0 WHERE id='
											. intval($this->id));
					if ($update_result === FALSE) {
						$success = FALSE;
						$error = '修改供应商信息失败';
					}
				}
			}

			if ($success) {
				$this->db->query('COMMIT');
			} else {
				$this->db->query('ROLLBACK');
			}

			return array('status' => $success ? 'success' : 'error',
					'message' => $success ? '供应商申请已重新修改提交，请等待财务部批准' : $error);

		}
		return array('status' => 'error', 'message' => $this->errors);
	}

	public function supplier_reaudit() {
		if ($this->validate_form_value('reaudit')) {
			$success = TRUE;
			$error = '';
			$this->db->query('BEGIN');

			//检查是否是否是自己创建并且状态是驳回的供应商申请
			$row = $this->db
					->get_row(
							'SELECT id FROM supplier_apply WHERE id='
									. intval($this->id) . ' AND apply_userid='
									. $this->getUid()
									. ' AND isok=-1 FOR UPDATE');
			if ($row === NULL) {
				$success = FALSE;
				$error = '供应商信息选择有误';
			} else {

				//检查是否已有相同名字的正式或申请中的供应商
				$resuts = $this->db
						->get_results(
								'SELECT id FROM supplier WHERE supplier_name="'
										. $this->supplier_name
										. '" AND category="' . $this->category
										. '" AND isagent='
										. (intval($this->isagent2))
										. ' FOR UPDATE UNION ALL SELECT id FROM supplier_apply WHERE supplier_name="'
										. $this->supplier_name . '" AND id<>'
										. intval($this->id) . ' AND category="'
										. $this->category . '" AND isagent2='
										. intval($this->isagent2)
										. ' FOR UPDATE');
				if ($resuts !== NULL) {
					$success = FALSE;
					$error = '已有同名的供应商信息或已申请';
				} else {
					$update_result = $this->db
							->query(
									'UPDATE supplier_apply SET supplier_name="'
											. $this->supplier_name . '",url="'
											. $this->url . '",deduction='
											. $this->deduction . ',rebate='
											. $this->rebate
											. ',in_invoice_tax_rate='
											. $this->in_invoice_tax_rate
											. ',supplier_type='
											. $this->supplier_type . ',dids="'
											. $this->dids . '",category="'
											. $this->category . '",isagent2='
											. intval($this->isagent2)
											. ',isok=0,remark="",audittime=null,audit_userid=null WHERE id='
											. intval($this->id));
					if ($update_result === FALSE) {
						$success = FALSE;
						$error = '修改供应商信息失败';
					}
				}
			}

			if ($success) {
				$this->db->query('COMMIT');
			} else {
				$this->db->query('ROLLBACK');
			}

			return array('status' => $success ? 'success' : 'error',
					'message' => $success ? '供应商申请已重新修改提交，请等待财务部批准' : $error);
		}
		return array('status' => 'error', 'message' => $this->errors);
	}

	public function view_myapply_html() {
		$row = $this->db
				->get_row(
						'SELECT a.supplier_name,a.url,a.deduction,a.in_invoice_tax_rate,a.supplier_type,a.dids,a.isok,a.remark,a.step,b.username,b.realname FROM new_supplier_apply a LEFT JOIN users b ON a.audit_userid=b.uid WHERE a.id='
								. intval($this->id) . ' AND a.apply_userid='
								. $this->getUid());
		if ($row !== NULL) {
			$buf = file_get_contents(
					TEMPLATE_PATH . 'finance/supplier/supplier_view.tpl');
			return str_replace(
					array('[LEFT]', '[TOP]', '[SUPPLIERNAME]', '[URL]',
							'[DEDUCTION]', '[ININVOICETAXRATE]',
							'[SUPPLIERTYPE]', '[DIDS]', '[STATUS]', '[REMARK]',
							'[BASE_URL]'),
					array($this->get_left_html(), $this->get_top_html(),
							$row->supplier_name, $row->url,
							Supplier_Apply_List::get_deduction(
									intval($row->deduction)),
							$row->in_invoice_tax_rate,
							Supplier_Apply_List::get_supplier_type(
									intval($row->supplier_type)),
							$this->get_upload_files($row->dids),
							Supplier_Apply_List::get_supplier_status(
									intval($row->isok), intval($row->step)),
							Supplier_Apply_List::get_remark(
									intval($row->isok), $row->remark,
									$row->username, $row->realname), BASE_URL),
					$buf);
		}
		return User::no_object('没有该供应商申请或者非自己申请的供应商信息');
	}

	private static function _get_deduction_radio($value, $deduction) {
		return $value === $deduction ? 'checked' : '';
	}

	private static function _get_supplier_type_radio($value, $supplier_type) {
		return $value === $supplier_type ? 'checked' : '';
	}

	private static function _get_isagent2_radio($value, $isagent2) {
		return $value === $isagent2 ? 'checked' : '';
	}

	public function edit_myapply_html() {
		$row = $this->db
				->get_row(
						'SELECT a.supplier_name,a.url,a.deduction,a.in_invoice_tax_rate,a.supplier_type,a.dids,a.isok,a.remark,a.step,b.username,b.realname FROM new_supplier_apply a LEFT JOIN users b ON a.audit_userid=b.uid WHERE a.id='
								. intval($this->id) . ' AND a.apply_userid='
								. $this->getUid());
		if ($row !== NULL) {
			$buf = file_get_contents(
					TEMPLATE_PATH . 'finance/supplier/supplier_reaudit.tpl');
			$did_value = $row->dids;
			if (!empty($did_value)) {
				$did_value = '^' . $did_value . '^';
			} else {
				$did_value = '^';
			}
			return str_replace(
					array('[LEFT]', '[TOP]', '[SUPPLIERNAME]', '[URL]',
							'[DEDUCTION]', '[ININVOICETAXRATE]',
							'[SUPPLIERTYPE]', '[DIDS]', '[STATUS]', '[REMARK]',
							'[VCODE]', '[DEDUCTION1]', '[DEDUCTION2]',
							'[SUPPLIERTYPE1]', '[SUPPLIERTYPE2]',
							'[DIDSVALUE]', '[VALIDATE_TYPE]',
							'[VALIDATE_SIZE]', '[ID]', '[ISREQUIRED]',
							'[BASE_URL]'),
					array($this->get_left_html(), $this->get_top_html(),
							$row->supplier_name, $row->url,
							Supplier_Apply_List::get_deduction(
									intval($row->deduction)),
							$row->in_invoice_tax_rate,
							Supplier_Apply_List::get_supplier_type(
									intval($row->supplier_type)),
							$this->get_upload_files($row->dids, TRUE),
							Supplier_Apply_List::get_supplier_status(
									intval($row->isok), intval($row->step)),
							Supplier_Apply_List::get_remark(
									intval($row->isok), $row->remark,
									$row->username, $row->realname),
							$this->get_vcode(),
							self::_get_deduction_radio(0,
									intval($row->deduction)),
							self::_get_deduction_radio(1,
									intval($row->deduction)),
							self::_get_supplier_type_radio(1,
									intval($row->supplier_type)),
							self::_get_supplier_type_radio(2,
									intval($row->supplier_type)), $did_value,
							implode(',',
									$GLOBALS['defined_upload_validate_type']),
							UPLOAD_FILE_MAX_SIZE / (1024 * 1024),
							intval($this->id), 'optional', BASE_URL), $buf);
		}
		return User::no_object('没有该供应商申请或者非自己申请的供应商信息');
	}

	public function audit_apply_html() {
		if ($this->getHas_supplier_apply_audit_tab()) {
			$row = $this->db
					->get_row(
							'SELECT a.supplier_name,a.url,a.deduction,a.in_invoice_tax_rate,a.supplier_type,a.dids,a.isok,a.remark,a.step,b.username,b.realname FROM new_supplier_apply a LEFT JOIN users b ON a.apply_userid=b.uid WHERE a.id='
									. intval($this->id) . ' AND isok=0');
			if ($row !== NULL) {
				$buf = file_get_contents(
						TEMPLATE_PATH . 'finance/supplier/supplier_audit.tpl');
				return str_replace(
						array('[LEFT]', '[TOP]', '[SUPPLIERNAME]', '[URL]',
								'[DEDUCTION]', '[ININVOICETAXRATE]',
								'[SUPPLIERTYPE]', '[DIDS]', '[APPLYUSER]',
								'[ID]', '[VCODE]', '[PARENTSUPPLIERS]',
								'[BASE_URL]'),
						array($this->get_left_html(), $this->get_top_html(),
								$row->supplier_name, $row->url,
								Supplier_Apply_List::get_deduction(
										intval($row->deduction)),
								$row->in_invoice_tax_rate,
								Supplier_Apply_List::get_supplier_type(
										intval($row->supplier_type)),
								$this->get_upload_files($row->dids),
								$row->realname . '（' . $row->username . '）',
								intval($this->id), $this->get_vcode(),
								$this->_get_parent_suppliers(), BASE_URL),
						$buf);
			}
			return User::no_object('没有该供应商申请或非申请状态');
		}
		return User::no_permission();
	}

	private function _get_parent_suppliers($no_use = NULL, $selected = NULL) {
		$s = '<option value="">请选择</option>';
		//读取数据库
		$results = $this->db
				->get_results(
						'SELECT id,supplier_name,parentid FROM new_supplier WHERE parentid=0 AND isok=1');
		if ($results !== NULL) {
			foreach ($results as $result) {
				if (intval($no_use) !== intval($result->id)) {
					$s .= '<option value="' . $result->id . '" '
							. ($selected !== NULL
									&& intval($selected)
											=== intval($result->id) ? 'selected'
									: '') . '>' . $result->supplier_name
							. '</option>';
				}
			}
		}
		return $s;
	}

	public function new_audit_supplier_apply() {
		if ($this->validate_form_value('new_audit')) {
			$success = TRUE;
			$error = '';
			$this->db->query('BEGIN');

			$supplier_row = $this->db
					->get_row(
							'SELECT id,supplier_name,step,supplier_type FROM new_supplier_apply WHERE id='
									. intval($this->id)
									. ' AND isok=0 FOR UPDATE');
			if ($supplier_row === NULL) {
				$success = FALSE;
				$error = '审核供应商选择有误';
			} else {
				//如果审核通过，则检查parentid
				if (intval($this->audit_result) === 1
						&& intval($this->parentid) > 0) {
					$row = $this->db
							->get_row(
									'SELECT id FROM new_supplier WHERE id='
											. intval($this->parentid)
											. ' AND parentid=0 FOR UPDATE');
					if ($row === NULL) {
						$success = FALSE;
						$error = '实际供应商对应选择有误';
					}
				}

				if ($success) {
					$step = intval($supplier_row->step);
					if (intval($this->audit_result) === 1) {
						$supplier_type = intval($supplier_row->supplier_type);
						//if ($supplier_type === 2) {
						$sql = 'UPDATE new_supplier_apply SET step=1,isok=1,audittime=now(),audit_userid='
								. $this->getUid() . ' WHERE id='
								. intval($this->id);
						//} else {
						//	$sql = 'UPDATE new_supplier_apply SET step=step+1'
						//			. ($step === 1 ? ',isok=1,audittime=now(),audit_userid='
						//							. $this->getUid() : '')
						//			. ' WHERE id=' . intval($this->id);
						//}
					} else {
						$sql = 'UPDATE new_supplier_apply SET step=0,isok=-1,remark="'
								. $this->remark
								. '",audittime=now(),audit_userid='
								. $this->getUid() . ' WHERE id='
								. intval($this->id);
					}
					$result = $this->db->query($sql);
					if ($result === FALSE) {
						$success = FALSE;
						$error = '审核供应商信息失败，错误代码1';
					}

					if ($success && intval($this->audit_result) === 1) {
						//&& ($step === 1 || $supplier_type === 2)) {

						//入正式表
						$insert_result = $this->db
								->query(
										'INSERT INTO new_supplier(supplier_name,isok,parentid) SELECT supplier_name,1,'
												. intval($this->parentid)
												. ' FROM new_supplier_apply WHERE id='
												. intval($this->id));
						if ($insert_result === FALSE) {
							$success = FALSE;
							$error = '审核供应商信息失败，错误代码2';
						} else {
							$supplier_id = $this->db->insert_id;

							//supplier_info
							$insert_result = $this->db
									->query(
											'INSERT INTO new_supplier_info(supplier_id,url,in_invoice_tax_rate,deduction,supplier_type,dids,addtime,isok) SELECT '
													. $supplier_id
													. ',url,in_invoice_tax_rate,deduction,supplier_type,dids,now(),1 FROM new_supplier_apply WHERE id='
													. intval($this->id));
							if ($insert_result === FALSE) {
								$success = FALSE;
								$error = '审核供应商信息失败，错误代码3';
							}
						}
					}
				}

			}

			if ($success) {
				$this->db->query('COMMIT');
				self::getSupplierInstance(TRUE);
			} else {
				$this->db->query('ROLLBACK');
			}

			return array('status' => $success ? 'success' : 'error',
					'message' => $success ? '供应商申请审核成功' : $error);
		}
		return array('status' => 'error', 'message' => $this->errors);
	}

	public function audit_supplier_apply() {
		if ($this->validate_form_value('audit')) {
			$success = TRUE;
			//$has_parent_supplier = FALSE;
			$error = '';
			$this->db->query('BEGIN');

			$supplier_row = $this->db
					->get_row(
							'SELECT id,supplier_name FROM supplier_apply WHERE id='
									. intval($this->id)
									. ' AND isok=0 FOR UPDATE');
			if ($supplier_row === NULL) {
				$success = FALSE;
				$error = '审核供应商选择有误';
			} else {
				//如果审核通过，则检查parentid
				if (intval($this->audit_result) === 1
						&& intval($this->parentid) > 0) {
					$row = $this->db
							->get_row(
									'SELECT id FROM supplier WHERE id='
											. intval($this->parentid)
											. ' AND parentid=0 FOR UPDATE');
					if ($row === NULL) {
						$success = FALSE;
						$error = '实际供应商对应选择有误';
					}
				}

				if ($success) {
					$update_result = $this->db
							->query(
									'UPDATE supplier_apply SET isok='
											. intval($this->audit_result)
											. ',remark="' . $this->remark
											. '",audittime=now(),audit_userid='
											. $this->getUid() . ' WHERE id='
											. intval($this->id));
					if ($update_result === FALSE) {
						$success = FALSE;
						$error = '审核供应商信息失败，错误代码1';
					} else {
						if (intval($this->audit_result) === 1) {
							//检查名称是否已存在
							$row = $this->db
									->get_row(
											'SELECT id FROM supplier WHERE supplier_name="'
													. $supplier_row
															->supplier_name
													. '" AND isok=1');
							if ($row !== NULL) {
								$supplier_id = $row->id;
							} else {
								//进入供应商正式表
								$insert_result = $this->db
										->query(
												'INSERT INTO supplier(supplier_name,isok,parentid) SELECT supplier_name,1,'
														. intval(
																$this->parentid)
														. ' FROM supplier_apply WHERE id='
														. intval($this->id));
								if ($insert_result === FALSE) {
									$success = FALSE;
									$error = '审核供应商信息失败，错误代码2';
								} else {
									$supplier_id = $this->db->insert_id;
									//$has_parent_supplier = TRUE;
								}
							}

							if ($success) {
								$insert_result = $this->db
										->query(
												'INSERT INTO supplier_info(supplier_id,category,isagent2,url,rebate,in_invoice_tax_rate,addtime,version,isok,deduction,supplier_type) SELECT '
														. $supplier_id
														. ',category,isagent2,url,rebate,in_invoice_tax_rate,now(),1,1,deduction,supplier_type FROM supplier_apply WHERE id='
														. intval($this->id));
								if ($insert_result === FALSE) {
									$success = FALSE;
									$error = '审核供应商信息失败，错误代码3';
								}
							}
						}
					}
				}
			}

			if ($success) {
				$this->db->query('COMMIT');
				//if (intval($this->parentid) === 0 && $has_parent_supplier) {
				//缓存parentid=0
				//	$this->_get_parent_suppliers(TRUE);
				//}

				self::getSupplierInstance(TRUE);
			} else {
				$this->db->query('ROLLBACK');
			}

			return array('status' => $success ? 'success' : 'error',
					'message' => $success ? '供应商申请审核成功' : $error);
		}
		return array('status' => 'error', 'message' => $this->errors);
	}

	public function get_supplier_industry_html() {
		if ($this->has_supplier_permission) {
			$buf = file_get_contents(
					TEMPLATE_PATH . 'finance/supplier/supplier_industry.tpl');
			return str_replace(
					array('[LEFT]', '[TOP]', '[VCODE]', '[BASE_URL]'),
					array($this->get_left_html(), $this->get_top_html(),
							$this->get_vcode(), BASE_URL), $buf);
		} else {
			return User::no_permission();
		}
	}

	public function get_supplier_category_html() {
		if ($this->has_supplier_permission) {
			$buf = file_get_contents(
					TEMPLATE_PATH . 'finance/supplier/supplier_category.tpl');
			return str_replace(
					array('[LEFT]', '[TOP]', '[VCODE]', '[BASE_URL]'),
					array($this->get_left_html(), $this->get_top_html(),
							$this->get_vcode(), BASE_URL), $buf);
		} else {
			return User::no_permission();
		}
	}

	public function supplier_category() {
		if ($this->has_supplier_permission) {
			if ($this->validate_form_value('supplier_category')) {
				$success = TRUE;
				$error = '';
				$this->db->query('BEGIN');

				//检查供应商名称
				$row = $this->db
						->get_row(
								'SELECT id FROM new_supplier WHERE supplier_name="'
										. $this->supplier_name . '" AND isok=1');
				if ($row !== NULL) {
					$supplier_id = $row->id;

					//检查产品名称是否已有
					$row = $this->db
							->get_row(
									'SELECT id FROM new_supplier_category WHERE supplier_id='
											. $supplier_id
											. ' AND category_name="'
											. $this->category_name . '"');
					if ($row !== NULL) {
						$category_id = $row->id;
					} else {
						$insert_result = $this->db
								->query(
										'INSERT INTO new_supplier_category(supplier_id,category_name,isok) VALUE('
												. $supplier_id . ',"'
												. $this->category_name . '",1)');
						if ($insert_result === FALSE) {
							$success = FALSE;
							$error = '新建供应商产品分类失败，错误代码1';
						} //else {
						//	$category_id = $this->db->insert_id;
						//}
					}

					/*
					if ($success) {
					    $insert_result = $this->db
					            ->query(
					                    'INSERT INTO new_supplier_category_rebate(supplier_id,category_id,rebate,starttime,endtime,isok) VALUE('
					                            . $supplier_id . ','
					                            . $category_id . ','
					                            . $this->rebate . ',"'
					                            . $this->starttime . '","'
					                            . $this->endtime . '",1)');
					    if ($insert_result === FALSE) {
					        $success = FALSE;
					        $error = '新建供应商产品分类失败，错误代码2';
					    }
					}
					 */
				} else {
					$success = FALSE;
					$error = '没有该供应商信息';
				}

				if ($success) {
					$this->db->query('COMMIT');
				} else {
					$this->db->query('ROLLBACK');
				}
				return array('status' => $success ? 'success' : 'error',
						'message' => $success ? '新建供应商产品分类成功' : $error);
			}
			return array('status' => 'error', 'message' => $this->errors);
		}
		return array('status' => 'error', 'message' => NO_RIGHT_TO_DO_THIS);
	}

	public function new_supplier_industy() {
		if ($this->has_supplier_permission) {
			if ($this->validate_form_value('new_supplier_industy')) {
				$success = TRUE;
				$error = '';
				$this->db->query('BEGIN');

				//检查媒体简称
				$row = $this->db
						->get_row(
								'SELECT id FROM finance_supplier_short WHERE media_short="'
										. $this->supplier_name . '"');
				if ($row !== NULL) {
					$supplier_short_id = $row->id;

					//检查行业名称是否已有
					$row = $this->db
							->get_row(
									'SELECT id FROM new_supplier_industry WHERE supplier_short_id='
											. $supplier_short_id
											. ' AND industry_name="'
											. $this->industry_name . '"');
					if ($row !== NULL) {
						$industry_id = $row->id;
					} else {
						$insert_result = $this->db
								->query(
										'INSERT INTO new_supplier_industry(supplier_short_id,industry_name,isok) VALUE('
												. $supplier_short_id . ',"'
												. $this->industry_name . '",1)');
						if ($insert_result === FALSE) {
							$success = FALSE;
							$error = '新建客户行业分类失败，错误代码1';
						}
					}
				} else {
					$success = FALSE;
					$error = '没有该媒体简称信息';
				}

				if ($success) {
					$this->db->query('COMMIT');
					self::getIndustryInstance(TRUE);
				} else {
					$this->db->query('ROLLBACK');
				}
				return array('status' => $success ? 'success' : 'error',
						'message' => $success ? '新建客户行业分类成功' : $error);
			}
			return array('status' => 'error', 'message' => $this->errors);
		}
		return array('status' => 'error', 'message' => NO_RIGHT_TO_DO_THIS);
	}

	public function edit_supplier_category_html() {
		if ($this->has_supplier_permission) {
			$row = $this->db
					->get_row(
							'SELECT a.id,a.category_name,b.supplier_name
FROM new_supplier_category a
LEFT JOIN new_supplier b
ON a.supplier_id=b.id
WHERE a.id=' . intval($this->category_id) . ' AND a.isok=1');

			if ($row !== NULL) {
				$buf = file_get_contents(
						TEMPLATE_PATH
								. 'finance/supplier/supplier_category_edit.tpl');
				return str_replace(
						array('[LEFT]', '[TOP]', '[VCODE]', '[SUPPLIERNAME]',
								'[CATEGORYNAME]', '[CATEGORYID]', '[BASE_URL]'),
						array($this->get_left_html(), $this->get_top_html(),
								$this->get_vcode(), $row->supplier_name,
								$row->category_name,
								intval($this->category_id), BASE_URL), $buf);
			} else {
				User::no_object('没有该供应商产品分类或者该产品分类不可更新');
			}

		} else {
			return User::no_permission();
		}
	}

	public function edit_supplier_industry_html() {
		if ($this->has_supplier_permission) {
			$row = $this->db
					->get_row(
							'SELECT a.id,a.industry_name,b.media_short
FROM new_supplier_industry a
LEFT JOIN finance_supplier_short b
ON a.supplier_short_id=b.id
WHERE a.id=' . intval($this->industry_id) . ' AND a.isok=1');

			if ($row !== NULL) {
				$buf = file_get_contents(
						TEMPLATE_PATH
								. 'finance/supplier/supplier_industry_edit.tpl');
				return str_replace(
						array('[LEFT]', '[TOP]', '[VCODE]', '[SUPPLIERNAME]',
								'[INDUSTRYNAME]', '[INDUSTRYID]', '[BASE_URL]'),
						array($this->get_left_html(), $this->get_top_html(),
								$this->get_vcode(), $row->media_short,
								$row->industry_name,
								intval($this->industry_id), BASE_URL), $buf);
			} else {
				User::no_object('没有该客户行业分类或者该行业分类不可更新');
			}

		} else {
			return User::no_permission();
		}
	}

	public function category_update() {
		if ($this->has_supplier_permission) {
			if (self::validate_form_value('new_category_update')) {
				$success = TRUE;
				$error = '';
				$this->db->query('BEGIN');

				//检查是否有
				$exid = $this->db
						->get_var(
								'SELECT id FROM new_supplier_category WHERE category_name="'
										. $this->category_name
										. '" AND supplier_id=
(
SELECT supplier_id FROM new_supplier_category WHERE id='
										. intval($this->category_id)
										. '
) AND id<>' . intval($this->category_id));

				if ($exid > 0) {
					$success = FALSE;
					$error = '同一供应商下已有该名称的产品分类';
				} else {
					$result = $this->db
							->query(
									'UPDATE new_supplier_category SET category_name="'
											. $this->category_name
											. '" WHERE id='
											. intval($this->category_id));

					if ($result === FALSE) {
						$success = FALSE;
						$error = '更新供应商产品分类返点失败';
					}
				}

				if ($success) {
					$this->db->query('COMMIT');
					self::getInstance(TRUE);
				} else {
					$this->db->query('ROLLBACK');
				}
				return array('status' => $success ? 'success' : 'error',
						'message' => $success ? '更新供应商产品分类成功' : $error);

			}
			return array('status' => 'error', 'message' => $this->errors);
		}
		return array('status' => 'error', 'message' => NO_RIGHT_TO_DO_THIS);
	}

	public function new_industry_update() {
		if ($this->has_supplier_permission) {
			if (self::validate_form_value('new_industry_update')) {
				$success = TRUE;
				$error = '';
				$this->db->query('BEGIN');

				//检查是否有
				$exid = $this->db
						->get_var(
								'SELECT id FROM new_supplier_industry WHERE industry_name="'
										. $this->industry_name
										. '" AND supplier_short_id=
(
SELECT supplier_short_id FROM new_supplier_industry WHERE id='
										. intval($this->industry_id)
										. '
) AND id<>' . intval($this->industry_id));
				if ($exid > 0) {
					$success = FALSE;
					$error = '同一媒体简称下已有该名称的客户行业分类';
				} else {
					$result = $this->db
							->query(
									'UPDATE new_supplier_industry SET industry_name="'
											. $this->industry_name
											. '" WHERE id='
											. intval($this->industry_id));

					if ($result === FALSE) {
						$success = FALSE;
						$error = '更新客户行业分类失败';
					}
				}

				if ($success) {
					$this->db->query('COMMIT');
					self::getIndustryInstance(TRUE);
				} else {
					$this->db->query('ROLLBACK');
				}
				return array('status' => $success ? 'success' : 'error',
						'message' => $success ? '更新客户行业分类成功' : $error);

			}
			return array('status' => 'error', 'message' => $this->errors);
		}
		return array('status' => 'error', 'message' => NO_RIGHT_TO_DO_THIS);
	}

	public static function getIndustryInstance($force_flush = FALSE) {
		$supplier_industry_cache_filename = md5(
				'supplier_industry_cache_filename');
		$supplier_industry_cache = new FileCache(CACHE_TIME, CACHE_PATH);
		$supplier_industry_cache_file = $supplier_industry_cache
				->get($supplier_industry_cache_filename);
		if ($force_flush || $supplier_industry_cache_file === FALSE) {
			//读取数据库
			$dao = new Dao_Impl();
			$industrys = $dao->db
					->get_results(
							'SELECT a.industry_name,a.id AS industry_id,b.media_short,b.id AS supplier_short_id
FROM 
new_supplier_industry a
LEFT JOIN finance_supplier_short b
ON a.supplier_short_id=b.id
WHERE a.isok=1');

			if ($industrys !== NULL) {
				$datas = array();
				foreach ($industrys as $industry) {
					if ($datas['industry'][$industry->industry_id] === NULL) {
						$datas['industry'][$industry->industry_id] = $industry
								->industry_name;
					}

					if ($datas['supplier_short_industry'][$industry
							->supplier_short_id] === NULL
							|| !in_array($industry->industry_id,
									$datas['supplier_short_industry'][$industry
											->supplier_short_id], TRUE)) {
						$datas['supplier_short_industry'][$industry
								->supplier_short_id][] = $industry->industry_id;
					}

				}
				$supplier_industry_cache
						->set($supplier_industry_cache_filename, $datas);
			}
		}
		return $supplier_industry_cache->get($supplier_industry_cache_filename);
	}

	public static function getCategorysSelect() {
		$categorys = self::getInstance();
		$arr = array();
		foreach ($categorys['supplier'] as $key => $value) {
			$c = array();
			foreach ($categorys['supplier_category'][$key] as $vv) {
				$c[] = urlencode($categorys['category'][$vv]);
			}
			$arr[] = array('s' => urlencode($value['sn']), 'd' => $c);
		}
		return urldecode(json_encode($arr));
	}

	public static function getCategoriesBySupplierID($supplierID) {
		$categories = md5('getCategoriesBySupplierID_' . $supplierID);
		$categories_cache = new FileCache(CACHE_TIME, CACHE_PATH);
		$categories_cache_file = $categories_cache->get($categories);

		if ($categories_cache_file === FALSE) {
			//读取数据库
			$dao = new Dao_Impl();
			$cats = $dao->db
					->get_results(
							'SELECT id,category_name FROM new_supplier_category WHERE supplier_id='
									. $supplierID . ' AND isok=1');
			if ($cats !== NULL) {
				$datas = array();
				foreach ($cats as $cat) {
					$datas[$cat->id] = $cat->category_name;
				}
				$categories_cache->set($categories, $datas);
			}
			$dao->db->disconnect();
		}
		return $categories_cache->get($categories);
	}

	public static function getCategoryBySupplierID($supplierID) {
		//15-12-09客户行业分类不跟供应商
		$ca = self::getCategoriesBySupplierID($supplierID);
		$datas = array();
		if (!empty($ca)) {
			foreach ($ca as $k => $v) {
				$datas['category'][$k] = urlencode($v);
			}
		} else {
			$datas['category'] = '';
		}
		return urldecode(json_encode($datas));
	}

	public function getExportHtml() {
		if ($this->has_supplier_permission) {
			return str_replace(
					array('[LEFT]', '[TOP]', '[VCODE]', '[BASE_URL]'),
					array($this->get_left_html(), $this->get_top_html(),
							$this->get_vcode(), BASE_URL),
					file_get_contents(
							TEMPLATE_PATH
									. 'finance/supplier/supplier_export.tpl'));
		} else {
			User::no_permission();
		}
	}

	public function exportSupplierType() {
		if ($this->has_supplier_permission) {
			$exportype = (int) ($this->exportype);

			$filename = '';
			$titles = array();

			if ($exportype === 1) {
				//行业分类
				$results = $this->db
						->get_results(
								'SELECT a.industry_name,b.media_short
FROM 
new_supplier_industry a
LEFT JOIN finance_supplier_short b
ON a.supplier_short_id=b.id');
				$filename = '客户行业分类';
				$titles = array('客户行业分类名称', '所属媒体简称');
			} else if ($exportype === 2) {
				//产品分类
				$results = $this->db
						->get_results(
								'SELECT a.category_name,b.supplier_name
FROM 
new_supplier_category a
LEFT JOIN new_supplier b
ON a.supplier_id=b.id');
				$filename = '产品分类';
				$titles = array('产品分类名称', '所属供应商名称');
			} else {
				//供应商
				$results = $this->db
						->get_results(
								'SELECT a.supplier_name,b.url,b.in_invoice_tax_rate,b.deduction,b.supplier_type
FROM 
new_supplier a
LEFT JOIN new_supplier_info b
ON a.id=b.supplier_id');
				$filename = '供应商';
				$titles = array('供应商名称', '网址', '进票税率', '有无抵扣联', '供应商类型');
			}

			if ($results !== NULL) {
				//开始生成excel
				$objPHPExcel = new PHPExcel();
				PHPExcel_Settings::setCacheStorageMethod(
						PHPExcel_CachedObjectStorageFactory::cache_in_memory_serialized);

				$objPHPExcel->setActiveSheetIndex(0);
				$objPHPExcel->getActiveSheet()->setTitle($filename);

				foreach ($titles as $k => $v) {
					$objPHPExcel->getActiveSheet()
							->setCellValueByColumnAndRow($k, 1, $v);
				}

				foreach ($results as $key => $result) {
					if ($exportype === 1) {
						//行业分类
						$objPHPExcel->getActiveSheet()
								->setCellValueByColumnAndRow(0, $key + 2,
										$result->industry_name);

						$objPHPExcel->getActiveSheet()
								->setCellValueByColumnAndRow(1, $key + 2,
										$result->media_short);
					} else if ($exportype === 2) {
						//产品分类
						$objPHPExcel->getActiveSheet()
								->setCellValueByColumnAndRow(0, $key + 2,
										$result->category_name);

						$objPHPExcel->getActiveSheet()
								->setCellValueByColumnAndRow(1, $key + 2,
										$result->supplier_name);
					} else {
						//供应商
						$objPHPExcel->getActiveSheet()
								->setCellValueByColumnAndRow(0, $key + 2,
										$result->supplier_name);

						$objPHPExcel->getActiveSheet()
								->setCellValueByColumnAndRow(1, $key + 2,
										$result->url);

						$objPHPExcel->getActiveSheet()
								->setCellValueByColumnAndRow(2, $key + 2,
										$result->in_invoice_tax_rate);

						$objPHPExcel->getActiveSheet()
								->setCellValueByColumnAndRow(3, $key + 2,
										(int) ($result->deduction) === 0 ? '无'
												: '有');

						$objPHPExcel->getActiveSheet()
								->setCellValueByColumnAndRow(4, $key + 2,
										(int) ($result->supplier_type) === 1 ? '媒体'
												: '外包');
					}
				}

				//生成
				$objPHPExcel->setActiveSheetIndex(0);
				header('Content-Type: application/vnd.ms-excel');
				header(
						'Content-Disposition: attachment;filename="'
								. urlencode($filename) . '.xls"');
				header('Cache-Control: max-age=0');

				$objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel,
						'Excel5');
				$objWriter->save('php://output');
				$objPHPExcel->disconnectWorksheets();
				unset($objPHPExcel);
			} else {
				return User::no_object('没有符合要求的记录');
			}
		} else {
			return User::no_permission();
		}
	}
}
