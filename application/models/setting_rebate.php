<?php
class Setting_Rebate extends User {
	private $id;
	private $supplier_id;
	private $supplier_short_id;
	private $category_id;
	private $industry_id;
	private $rebate;
	private $errors = array();
	private $has_setting_rebate_permission = FALSE;

	private $page;
	private $all_count;
	private $page_count;
	const LIMIT = 50;

	public function __construct($fields = array()) {
		parent::__construct();
		if (in_array($this->getUsername(),
				$GLOBALS['manager_finance_permission'], TRUE)
				|| intval($this->getBelong_dep()) === 2) {
			$this->has_setting_rebate_permission = TRUE;
		}
		if (!empty($fields)) {
			foreach ($this as $key => $value) {
				if ($fields[$key] !== NULL
						&& !in_array($key,
								array('has_setting_rebate_permission', 'errors'),
								TRUE)) {
					$this->$key = $fields[$key];
				}
			}
		}
	}

	public static function getRebateInstance($force_flush = FALSE) {
		$reabte_rate_cache_filename = md5('reabte_rate_cache');
		$reabte_rate_cache = new FileCache(CACHE_TIME, CACHE_PATH);
		$reabte_rate_cache_file = $reabte_rate_cache
				->get($reabte_rate_cache_filename);

		if ($force_flush || $reabte_rate_cache_file === FALSE) {
			//读取数据库
			$dao = new Dao_Impl();
			$rebates = $dao->db
					->get_results(
							'SELECT supplier_id,supplier_short_id,category_id,industry_id,rebate FROM finance_rebate_rate');

			if ($rebates !== NULL) {
				$datas = array();
				foreach ($rebates as $rebate) {
					$id = $rebate->supplier_id . '|'
							. $rebate->supplier_short_id . '|'
							. $rebate->category_id . '|' . $rebate->industry_id;
					$datas[$id] = $rebate->rebate;
				}
				$reabte_rate_cache->set($reabte_rate_cache_filename, $datas);
			}
			$dao->db->disconnect();
		}
		return $reabte_rate_cache->get($reabte_rate_cache_filename);
	}

	public function getIndexHtml() {
		if ($this->has_setting_rebate_permission) {

			//供应商名称列表
			$suppliers = '<option value="0"></option>';
			$results = $this->db
					->get_results(
							'SELECT id,supplier_name FROM new_supplier WHERE isok=1');
			if ($results !== NULL) {
				foreach ($results as $result) {
					$suppliers .= '<option value="' . $result->id . '">'
							. $result->supplier_name . '</option>';
				}
			}

			//媒体简称列表
			$mediashort = '<option value="0"></option>';
			$results = $this->db
					->get_results(
							'SELECT id,media_short FROM finance_supplier_short');
			if ($results !== NULL) {
				foreach ($results as $result) {
					$mediashort .= '<option value="' . $result->id . '">'
							. $result->media_short . '</option>';
				}
			}

			return str_replace(
					array('[LEFT]', '[TOP]', '[VCODE]', '[SUPPLIERNAMES]',
							'[SUPPLIERSHORTNAMES]', '[BASE_URL]'),
					array($this->get_left_html(), $this->get_top_html(),
							$this->get_vcode(), $suppliers, $mediashort,
							BASE_URL),
					file_get_contents(
							TEMPLATE_PATH
									. 'finance/finance_setting_rebate.tpl'));
		} else {
			User::no_permission();
		}
	}

	private function validate_form_value($action) {
		$errors = array();
		if (in_array($action, array('add', 'update'), TRUE)) {
			//id
			if ($action === 'update') {
				if (!self::validate_id(intval($this->id))) {
					$errors[] = '返点比例选择有误';
				}
			}

			//supplier id
			if (!Validate_Util::my_is_int(intval($this->supplier_id))) {
				$errors[] = '供应商选择有误';
			}

			//supplier short id
			if (!Validate_Util::my_is_int(intval($this->supplier_short_id))) {
				$errors[] = '媒体简称选择有误';
			}

			//category id
			if (!Validate_Util::my_is_int(intval($this->category_id))) {
				$errors[] = '产品分类选择有误';
			}

			//industry id
			if (!Validate_Util::my_is_int(intval($this->industry_id))) {
				$errors[] = '客户行业分类选择有误';
			}

			if (intval($this->industry_id) === 0
					&& intval($this->category_id) === 0
					&& intval($this->supplier_short_id) === 0
					&& intval($this->supplier_id) === 0) {
				$errors[] = '必须选择至少一种条件';
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

	public function getDoSettingRebate($action) {
		if ($this->has_setting_rebate_permission) {
			if ($this->validate_form_value($action)) {
				$success = TRUE;
				$error = '';

				$this->db->query('BEGIN');

				$sql = 'SELECT id FROM finance_rebate_rate WHERE supplier_id='
						. (int) ($this->supplier_id)
						. ' AND supplier_short_id='
						. (int) ($this->supplier_short_id)
						. ' AND category_id=' . (int) ($this->category_id)
						. ' AND industry_id=' . (int) ($this->industry_id);
				if ($action === 'update') {
					$sql .= ' AND id<>' . (int) ($this->id);
				}
				$sql .= ' FOR UPDATE';
				$getid = $this->db->get_var($sql);
				if ($getid > 0) {
					$success = FALSE;
					$error = '该返点比例已存在';
				} else {
					if ($action === 'add') {
						$result = $this->db
								->query(
										'INSERT INTO finance_rebate_rate(supplier_id,supplier_short_id,category_id,industry_id,rebate,addtime) VALUES('
												. (int) ($this->supplier_id)
												. ','
												. (int) ($this
														->supplier_short_id)
												. ','
												. (int) ($this->category_id)
												. ','
												. (int) ($this->industry_id)
												. ',' . $this->rebate
												. ',now())');
					} else {
						$result = $this->db
								->query(
										'UPDATE finance_rebate_rate SET supplier_id='
												. (int) ($this->supplier_id)
												. ',supplier_short_id='
												. (int) ($this
														->supplier_short_id)
												. ',category_id='
												. (int) ($this->category_id)
												. ',industry_id='
												. (int) ($this->industry_id)
												. ',rebate=' . $this->rebate
												. ' WHERE id='
												. (int) ($this->id));
					}
					if ($result === FALSE) {
						$success = FALSE;
						$error = ($action === 'add' ? '新增' : '更新') . '返点比例失败';
					}
				}

				if ($success) {
					$this->db->query('COMMIT');
					self::getRebateInstance(TRUE);
				} else {
					$this->db->query('ROLLBACK');
				}
				return array('status' => $success ? 'success' : 'error',
						'message' => $success ? ($action === 'add' ? '新增' : '更新')
										. '返点比例成功' : $error);
			}
			return array('status' => 'error', 'message' => $this->errors);
		}
		return array('status' => 'error', 'message' => NO_RIGHT_TO_DO_THIS);
	}

	private function _get_pagination($is_prev) {
		$param = '&page='
				. ($is_prev ? intval($this->page) - 1 : intval($this->page) + 1);
		return '<a href="' . BASE_URL . 'finance/?o=rebate_list' . $param
				. '">' . ($is_prev ? '上一页' : '下一页') . '</a>';
	}

	private function _getPrev() {
		$s = '';
		if (intval($this->page) !== 1) {

			$s = $this->_get_pagination(TRUE);
		}
		return $s;
	}

	private function _getNext() {
		$s = '';
		if (intval($this->page) >= intval($this->page_count)) {
			$s = '';
		} else {
			$s = $this->_get_pagination(FALSE);
		}
		return $s;
	}

	public function getRebateRateListHtml() {
		if ($this->has_setting_rebate_permission) {
			$this->all_count = intval(
					$this->db
							->get_var(
									'SELECT COUNT(*) FROM finance_rebate_rate'));
			$this->page_count = ceil($this->all_count / self::LIMIT);
			$start = self::LIMIT * intval($this->page) - self::LIMIT;
			if ($start < 0) {
				$start = 0;
			}

			$s = '';
			$results = $this->db
					->get_results(
							'SELECT a.id,a.rebate,b.supplier_name,c.media_short,d.category_name,e.industry_name
FROM
(
SELECT * FROM finance_rebate_rate 
) a
LEFT JOIN new_supplier b
ON a.supplier_id=b.id
LEFT JOIN finance_supplier_short c
ON a.supplier_short_id=c.id
LEFT JOIN new_supplier_category d
ON a.category_id=d.id
LEFT JOIN new_supplier_industry e
ON a.industry_id=e.id ORDER BY a.addtime DESC LIMIT ' . $start . ','
									. self::LIMIT);
			if ($results !== NULL) {
				foreach ($results as $key => $result) {
					$s .= '<tr><td>'
							. (($this->page - 1) * self::LIMIT + $key + 1)
							. '</td><td>' . $result->supplier_name
							. '</td><td>' . $result->media_short . '</td><td>'
							. $result->category_name . '</td><td>'
							. $result->industry_name . '</td><td>'
							. $result->rebate . '&nbsp;%</td><td><a href="'
							. BASE_URL . 'finance/?o=rebate_rate_edit&id='
							. $result->id . '">修改</a></td></tr>';
				}
			}

			return str_replace(
					array('[LEFT]', '[TOP]', '[VCODE]', '[PREV]', '[NEXT]',
							'[ALLCOUNTS]', '[COUNTS]', '[REBATERATELIST]',
							'[BASE_URL]'),
					array($this->get_left_html(), $this->get_top_html(),
							$this->get_vcode(), $this->_getPrev(),
							$this->_getNext(), $this->all_count,
							$this->page . '	/' . $this->page_count
									. ' 页 &nbsp;&nbsp;', $s, BASE_URL),
					file_get_contents(
							TEMPLATE_PATH
									. 'finance/finance_rebate_rate_list.tpl'));
		} else {
			User::no_permission();
		}
	}

	public function getRebateRateUpdateHtml() {
		if ($this->has_setting_rebate_permission) {
			$row = $this->db
					->get_row(
							'SELECT id,supplier_id,supplier_short_id,category_id,industry_id,rebate FROM finance_rebate_rate WHERE id='
									. (int) ($this->id));
			if ($row !== NULL) {

				//供应商名称列表
				$suppliers = '<option value="0"></option>';
				$results = $this->db
						->get_results(
								'SELECT id,supplier_name FROM new_supplier WHERE isok=1');
				if ($results !== NULL) {
					foreach ($results as $result) {
						$suppliers .= '<option value="' . $result->id . '"'
								. ($result->id === $row->supplier_id ? ' selected'
										: '') . '>' . $result->supplier_name
								. '</option>';
					}
				}

				//媒体简称列表
				$mediashort = '<option value="0"></option>';
				$results = $this->db
						->get_results(
								'SELECT id,media_short FROM finance_supplier_short');
				if ($results !== NULL) {
					foreach ($results as $result) {
						$mediashort .= '<option value="' . $result->id . '"'
								. ($result->id === $row->supplier_short_id ? ' selected'
										: '') . '>' . $result->media_short
								. '</option>';
					}
				}

				//category列表
				$categories = '<option value="0"></option>';
				if (!empty($row->supplier_id)) {
					$results = $this->db
							->get_results(
									'SELECT id,category_name FROM new_supplier_category WHERE supplier_id='
											. (int) ($row->supplier_id));
					if ($results !== NULL) {
						foreach ($results as $result) {
							$categories .= '<option value="' . $result->id
									. '"'
									. ($result->id === $row->category_id ? ' selected'
											: '') . '>'
									. $result->category_name . '</option>';
						}
					}
				}

				//industry列表
				$industries = '<option value="0"></option>';
				if (!empty($row->supplier_short_id)) {
					$results = $this->db
							->get_results(
									'SELECT id,industry_name FROM new_supplier_industry WHERE supplier_short_id='
											. (int) ($row->supplier_short_id));
					if ($results !== NULL) {
						foreach ($results as $result) {
							$industries .= '<option value="' . $result->id
									. '"'
									. ($result->id === $row->industry_id ? ' selected'
											: '') . '>'
									. $result->industry_name . '</option>';
						}
					}
				}

				return str_replace(
						array('[LEFT]', '[TOP]', '[VCODE]', '[ID]',
								'[SUPPLIERNAMES]', '[SUPPLIERSHORTNAMES]',
								'[CATEGORIES]', '[INDUSTRIES]', '[REBATE]',
								'[BASE_URL]'),
						array($this->get_left_html(), $this->get_top_html(),
								$this->get_vcode(), $row->id, $suppliers,
								$mediashort, $categories, $industries,
								$row->rebate, BASE_URL),
						file_get_contents(
								TEMPLATE_PATH
										. 'finance/finance_rebate_rate_edit.tpl'));
			} else {
				User::no_object('没有该返点比例');
			}

		} else {
			User::no_permission();
		}
	}

	public function getExportHtml() {
		if ($this->has_setting_rebate_permission) {
			return str_replace(
					array('[LEFT]', '[TOP]', '[VCODE]', '[BASE_URL]'),
					array($this->get_left_html(), $this->get_top_html(),
							$this->get_vcode(), BASE_URL),
					file_get_contents(
							TEMPLATE_PATH
									. 'finance/finance_rebate_rate_export.tpl'));
		} else {
			User::no_permission();
		}
	}

	public function exportRebateRate() {
		if ($this->has_setting_rebate_permission) {
			$results = $this->db
					->get_results(
							'SELECT b.supplier_name,c.media_short,d.category_name,e.industry_name,a.rebate 
FROM 
finance_rebate_rate a
LEFT JOIN new_supplier b
ON a.supplier_id=b.id
LEFT JOIN finance_supplier_short c
ON a.supplier_short_id=c.id
LEFT JOIN new_supplier_category d
ON a.category_id=d.id
LEFT JOIN new_supplier_industry e
ON a.industry_id=e.id');
			if ($results !== NULL) {
				//开始生成excel
				$objPHPExcel = new PHPExcel();
				PHPExcel_Settings::setCacheStorageMethod(
						PHPExcel_CachedObjectStorageFactory::cache_in_memory_serialized);

				$objPHPExcel->setActiveSheetIndex(0);
				$objPHPExcel->getActiveSheet()->setTitle('返点比例');

				foreach ($results as $key => $result) {
					if ($key === 0) {
						$objPHPExcel->getActiveSheet()
								->setCellValueByColumnAndRow(0, 1, '供应商名称');

						$objPHPExcel->getActiveSheet()
								->setCellValueByColumnAndRow(1, 1, '媒体简称');

						$objPHPExcel->getActiveSheet()
								->setCellValueByColumnAndRow(2, 1, '产品分类');

						$objPHPExcel->getActiveSheet()
								->setCellValueByColumnAndRow(3, 1, '客户行业分类');

						$objPHPExcel->getActiveSheet()
								->setCellValueByColumnAndRow(4, 1, '返点比例（%）');
					}
					
					$objPHPExcel->getActiveSheet()
							->setCellValueByColumnAndRow(0, $key + 2,
									$result->supplier_name);

					$objPHPExcel->getActiveSheet()
							->setCellValueByColumnAndRow(1, $key + 2,
									$result->media_short);

					$objPHPExcel->getActiveSheet()
							->setCellValueByColumnAndRow(2, $key + 2,
									$result->category_name);

					$objPHPExcel->getActiveSheet()
							->setCellValueByColumnAndRow(3, $key + 2,
									$result->industry_name);

					$objPHPExcel->getActiveSheet()
							->setCellValueByColumnAndRow(4, $key + 2,
									$result->rebate);
				}

				//生成
				$objPHPExcel->setActiveSheetIndex(0);
				header('Content-Type: application/vnd.ms-excel');
				header('Content-Disposition: attachment;filename="' . urlencode('返点比例') . '.xls"');
				header('Cache-Control: max-age=0');

				$objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel,
						'Excel5');
				$objWriter->save('php://output');
				$objPHPExcel->disconnectWorksheets();
				unset($objPHPExcel);
			} else {
				return User::no_object('没有符合要求的返点比例');
			}
		} else {
			return User::no_permission();
		}
	}
}
