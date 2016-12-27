<?php
class Supplier_Short extends User {
	private $id;
	private $media_short;
	private $has_supplier_short_permission = FALSE;
	private $errors = array();
	private $page;
	private $all_count;
	private $search;
	private $page_count;
	const LIMIT = 50;

	public function __construct($fields = array()) {
		parent::__construct();
		if (in_array($this->getUsername(),
				$GLOBALS['manager_finance_permission'], TRUE)
				|| intval($this->getBelong_dep()) === 2) {
			$this->has_supplier_short_permission = TRUE;
		}
		if (!empty($fields)) {
			foreach ($this as $key => $value) {
				if ($fields[$key] !== NULL
						&& !in_array($key,
								array('has_supplier_short_permission', 'errors'),
								TRUE)) {
					$this->$key = $fields[$key];
				}
			}
		}
	}

	public function getIndexHtml() {
		if ($this->has_supplier_short_permission) {
			return str_replace(
					array('[LEFT]', '[TOP]', '[VCODE]', '[BASE_URL]'),
					array($this->get_left_html(), $this->get_top_html(),
							$this->get_vcode(), BASE_URL),
					file_get_contents(
							TEMPLATE_PATH
									. 'finance/finance_supplier_short.tpl'));
		} else {
			User::no_permission();
		}
	}

	public function getExportHtml() {
		if ($this->has_supplier_short_permission) {
			return str_replace(
					array('[LEFT]', '[TOP]', '[VCODE]', '[BASE_URL]'),
					array($this->get_left_html(), $this->get_top_html(),
							$this->get_vcode(), BASE_URL),
					file_get_contents(
							TEMPLATE_PATH
									. 'finance/finance_supplier_short_export.tpl'));
		} else {
			User::no_permission();
		}
	}

	private function _get_pagination($is_prev) {
		$param = '&page='
				. ($is_prev ? intval($this->page) - 1 : intval($this->page) + 1)
				. '&search=' . $this->search;
		return '<a href="' . BASE_URL . 'finance/?o=meida_short_list' . $param
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

	public function getSupplierShortListHtml() {
		if ($this->has_supplier_short_permission) {
			$this->all_count = intval(
					$this->db
							->get_var(
									'SELECT COUNT(*) FROM finance_supplier_short'
											. (!empty($this->search) ? ' WHERE media_short LIKE "%'
															. $this->search
															. '%"' : '')));
			$this->page_count = ceil($this->all_count / self::LIMIT);
			$start = self::LIMIT * intval($this->page) - self::LIMIT;
			if ($start < 0) {
				$start = 0;
			}

			$s = '';
			$results = $this->db
					->get_results(
							'SELECT id,media_short,addtime FROM finance_supplier_short'
									. (!empty($this->search) ? ' WHERE media_short LIKE "%'
													. $this->search . '%"' : '')
									. ' ORDER BY addtime DESC  LIMIT ' . $start
									. ',' . self::LIMIT);
			if ($results !== NULL) {
				foreach ($results as $key => $result) {
					$s .= '<tr><td>'
							. (($this->page - 1) * self::LIMIT + $key + 1)
							. '</td><td>' . $result->media_short . '</td><td>'
							. $result->addtime . '</td><td><a href="'
							. BASE_URL . 'finance/?o=meida_short_edit&id='
							. $result->id . '">修改</a></td></tr>';
				}
			}

			return str_replace(
					array('[LEFT]', '[TOP]', '[VCODE]', '[SUPPLIERSHORTLIST]',
							'[PREV]', '[NEXT]', '[ALLCOUNTS]', '[COUNTS]',
							'[SEARCH]', '[BASE_URL]'),
					array($this->get_left_html(), $this->get_top_html(),
							$this->get_vcode(), $s, $this->_getPrev(),
							$this->_getNext(), $this->all_count,
							$this->page . '	/' . $this->page_count
									. ' 页 &nbsp;&nbsp;', $this->search,
							BASE_URL),
					file_get_contents(
							TEMPLATE_PATH
									. 'finance/finance_supplier_short_list.tpl'));
		} else {
			User::no_permission();
		}
	}

	public function getSupplierShortUpdateHtml() {
		if ($this->has_supplier_short_permission) {
			$row = $this->db
					->get_row(
							'SELECT id,media_short FROM finance_supplier_short WHERE id='
									. (int) ($this->id));
			if ($row !== NULL) {
				return str_replace(
						array('[LEFT]', '[TOP]', '[VCODE]', '[MEDIASHORT]',
								'[ID]', '[BASE_URL]'),
						array($this->get_left_html(), $this->get_top_html(),
								$this->get_vcode(), $row->media_short,
								$row->id, BASE_URL),
						file_get_contents(
								TEMPLATE_PATH
										. 'finance/finance_supplier_short_edit.tpl'));
			} else {
				User::no_object('没有该媒体简称');
			}

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
					$errors[] = '媒体简称选择有误';
				}
			}

			//media short
			if (empty($this->media_short)) {
				$errors[] = '媒体简称不能为空';
			} else if (!self::validate_field_max_length($this->media_short, 255)) {
				$errors[] = '媒体简称最多255个字符';
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

	public function getDoMediaShort($action) {
		if ($this->has_supplier_short_permission) {
			if ($this->validate_form_value($action)) {
				$success = TRUE;
				$error = '';

				$this->db->query('BEGIN');

				$sql = 'SELECT id FROM finance_supplier_short WHERE media_short="'
						. $this->media_short . '"';
				if ($action === 'update') {
					$sql . ' AND id<>' . (int) ($this->id);
				}
				$sql .= ' FOR UPDATE';
				$getid = $this->db->get_var($sql);
				if ($getid > 0) {
					$success = FALSE;
					$error = '媒体简称【' . $this->media_short . '】已存在';
				} else {
					if ($action === 'add') {
						$result = $this->db
								->query(
										'INSERT INTO finance_supplier_short(media_short,addtime,userid) VALUES("'
												. $this->media_short
												. '",now(),' . $this->getUid()
												. ')');
					} else {
						$result = $this->db
								->query(
										'UPDATE finance_supplier_short SET media_short="'
												. $this->media_short
												. '" WHERE id='
												. (int) ($this->id));
					}
					if ($result === FALSE) {
						$success = FALSE;
						$error = ($action === 'add' ? '新增' : '更新') . '媒体简称失败';
					}
				}

				if ($success) {
					$this->db->query('COMMIT');
					self::getInstance(TRUE);
				} else {
					$this->db->query('ROLLBACK');
				}
				return array('status' => $success ? 'success' : 'error',
						'message' => $success ? ($action === 'add' ? '新增' : '更新')
										. '媒体简称成功' : $error);
			}
			return array('status' => 'error', 'message' => $this->errors);
		}
		return array('status' => 'error', 'message' => NO_RIGHT_TO_DO_THIS);
	}

	public static function getInstance($force_flush = FALSE) {
		$supplier_cache_filename = md5('supplier_short_cache_filename');
		$supplier_cache = new FileCache(CACHE_TIME, CACHE_PATH);
		$supplier_cache_file = $supplier_cache->get($supplier_cache_filename);
		if ($force_flush || $supplier_cache_file === FALSE) {
			//读取数据库
			$dao = new Dao_Impl();

			$suppliers = $dao->db
					->get_results(
							'SELECT id,media_short FROM finance_supplier_short');

			if ($suppliers !== NULL) {
				$datas = array();
				foreach ($suppliers as $supplier) {
					$datas[$supplier->id] = $supplier->media_short;
				}
				$supplier_cache->set($supplier_cache_filename, $datas);
			}
		}

		return $supplier_cache->get($supplier_cache_filename);
	}

	public static function getSupplierShortSelect() {
		$shorts = self::getInstance();
		$arr = array();
		foreach ($shorts as $id => $short) {
			$arr[] = array('id' => $id, 'name' => urlencode($short));
		}
		return urldecode(json_encode($arr));
	}

	public static function getIndustrySelect() {
		$ins = self::getIndustries();
		$arr = array();
		foreach ($ins as $key => $val) {
			$t = array();
			foreach ($val as $v) {
				$t[] = array('id' => $v['id'], 'name' => urlencode($v['name']));
			}
			$arr[] = array('short_id' => $key, 'industrys' => $t);
		}
		return urldecode(json_encode($arr));
	}

	public static function getIndustryBySupplierShortID($short_id) {
		$ca = self::getIndustries();
		$datas = array();
		if (!empty($ca[$short_id])) {
			foreach ($ca[$short_id] as $v) {
				$datas['industry'][$v['id']] = urlencode($v['name']);
			}
		} else {
			$datas['industry'] = '';
		}
		return urldecode(json_encode($datas));
	}

	public static function getIndustries() {
		$categories = md5('Supplier_Short::getIndustries');
		$categories_cache = new FileCache(CACHE_TIME, CACHE_PATH);
		$categories_cache_file = $categories_cache->get($categories);

		if ($categories_cache_file === FALSE) {
			//读取数据库
			$dao = new Dao_Impl();
			$cats = $dao->db
					->get_results(
							'SELECT id,supplier_short_id,industry_name FROM new_supplier_industry WHERE isok=1');
			if ($cats !== NULL) {
				$datas = array();
				foreach ($cats as $cat) {
					$datas[$cat->supplier_short_id][] = array(
							'id' => $cat->id, 'name' => $cat->industry_name);
				}
				$categories_cache->set($categories, $datas);
			}
			$dao->db->disconnect();
		}
		return $categories_cache->get($categories);
	}

	public function exportSupplierShort() {
		if ($this->has_supplier_short_permission) {
			$results = $this->db
					->get_results(
							'SELECT media_short FROM finance_supplier_short');
			if ($results !== NULL) {
				//开始生成excel
				$objPHPExcel = new PHPExcel();
				PHPExcel_Settings::setCacheStorageMethod(
						PHPExcel_CachedObjectStorageFactory::cache_in_memory_serialized);

				$objPHPExcel->setActiveSheetIndex(0);
				$objPHPExcel->getActiveSheet()->setTitle('媒体简称');

				foreach ($results as $key => $result) {
					$objPHPExcel->getActiveSheet()
							->setCellValueByColumnAndRow(0, $key + 1,
									$result->media_short);
				}

				//生成
				$objPHPExcel->setActiveSheetIndex(0);
				header('Content-Type: application/vnd.ms-excel');
				header(
						'Content-Disposition: attachment;filename="' . urlencode('媒体简称') . '.xls"');
				header('Cache-Control: max-age=0');

				$objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel,
						'Excel5');
				$objWriter->save('php://output');
				$objPHPExcel->disconnectWorksheets();
				unset($objPHPExcel);
			}else{
				return User::no_object('没有符合要求的媒体简称');
			}
		}else{
			return User::no_permission();
		}
	}

}
