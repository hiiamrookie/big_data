<?php
class Outsourcing_Type extends User {
	private $has_outsourcing_type_permission = FALSE;
	private $errors = array();

	private $id;
	private $outsourcing_typename;
	private $remark;
	private $outsourcing_process;
	private $max_amount;

	private $page;
	private $all_count;
	private $page_count;

	const LIMIT = 50;

	public function __construct($fields = array()) {
		parent::__construct();
		if (in_array($this->getUsername(),
				$GLOBALS['manager_outsourcing_process_permission'], TRUE)) {
			$this->has_outsourcing_type_permission = TRUE;
			if (!empty($fields)) {
				foreach ($this as $key => $value) {
					if ($fields[$key] !== NULL
							&& !in_array($key,
									array('has_outsourcing_type_permission'),
									TRUE)) {
						$this->$key = $fields[$key];
					}
				}
			}
		}
	}

	private static function getInstance($force_flush = FALSE) {
		$outsourcing_type_cache_filename = md5(
				'outsourcing_type_cache_filename');
		$outsourcing_type_cache = new FileCache(CACHE_TIME, CACHE_PATH);
		$outsourcing_type_cache_file = $outsourcing_type_cache
				->get($outsourcing_type_cache_filename);
		if ($outsourcing_type_cache_file === FALSE || $force_flush) {
			//读取数据库
			$dao = new Dao_Impl();
			$outsourcing_type = $dao->db
					->get_results(
							'SELECT id,outsourcing_typename FROM outsourcing_type WHERE isok=1');
			if ($outsourcing_type !== NULL) {
				$datas = array();
				foreach ($outsourcing_type as $o) {
					$datas[$o->id] = $o->outsourcing_typename;
				}
				$outsourcing_type_cache
						->set($outsourcing_type_cache_filename, $datas);
			}
		}
		$outsourcing_type_cache_file = $outsourcing_type_cache
				->get($outsourcing_type_cache_filename);
		return $outsourcing_type_cache_file;
	}

	public static function getOutsourcingTypeSelect($outsourcing_type_id = NULL) {
		$s = '';
		$types = self::getInstance();
		if (!empty($types)) {
			foreach ($types as $key => $type) {
				$s .= '<option value="' . $key . '"' . ($outsourcing_type_id !== NULL && intval($outsourcing_type_id) === intval($key) ? 'selected' : '') . '>' . $type . '</option>';
			}
		}

		return $s;
	}

	private static function _getOutsourcingProcessSelect($process_id = NULL) {
		$s = '<option value="">请选择外包流程</option>';
		$process = Outsourcing_Process::getInstance();
		if (!empty($process)) {
			foreach ($process as $key => $p) {
				$s .= '<option value="' . $key . '" '
						. ($process_id !== NULL
								&& intval($process_id) === intval($key) ? 'selected'
								: '') . '>' . $p['name'] . '</option>';
			}
		}
		return $s;
	}

	public function getAddOutsourcingTypeHtml() {
		if ($this->has_outsourcing_type_permission) {
			$buf = file_get_contents(
					TEMPLATE_PATH . 'outsourcing/outsourcing_type_add.tpl');
			return str_replace(
					array('[LEFT]', '[TOP]', '[VCODE]', '[OUTSOURCINGPROCESS]',
							'[BASE_URL]'),
					array($this->get_left_html(), $this->get_top_html(),
							$this->get_vcode(),
							self::_getOutsourcingProcessSelect(), BASE_URL),
					$buf);
		}
		return User::no_permission();
	}

	public function getEditOutsourcingTypeHtml() {
		if ($this->has_outsourcing_type_permission) {
			$row = $this->db
					->get_row(
							'SELECT a.*,b.process_id
FROM outsourcing_type a
LEFT JOIN outsourcing_type_process b
ON a.id=b.type_id
WHERE a.id=' . intval($this->id));
			if ($row !== NULL) {
				$buf = file_get_contents(
						TEMPLATE_PATH . 'outsourcing/outsourcing_type_edit.tpl');
				return str_replace(
						array('[LEFT]', '[TOP]', '[VCODE]',
								'[OUTSOURCINGPROCESS]',
								'[OUTSOURCINGTYPENAME]', '[REMARK]', '[ID]',
								'[MAXAMOUNT]', '[MAXAMOUNTREADONLY]',
								'[ISMAXAMOUNT]', '[BASE_URL]'),
						array($this->get_left_html(), $this->get_top_html(),
								$this->get_vcode(),
								self::_getOutsourcingProcessSelect(
										$row->process_id),
								$row->outsourcing_typename, $row->remark,
								$row->id,
								($row->max_amount == 0 ? '' : $row->max_amount),
								($row->max_amount == 0 ? 'readonly' : ''),
								($row->max_amount == 0 ? 'checked' : ''),
								BASE_URL), $buf);
			}
			return User::no_object('没有该执行单外包类型');
		}
		return User::no_permission();
	}

	private function validate_form_value($action) {
		$errors = array();
		if (in_array($action, array('add', 'update'), TRUE)) {
			if ($action === 'update') {
				if (!self::validate_id(intval($this->id))) {
					$errors[] = '外包类型选择有误';
				}
			}

			if (!self::validate_field_not_empty($this->outsourcing_typename)
					|| !self::validate_field_not_null(
							$this->outsourcing_typename)) {
				$errors[] = '外包类型名称不能为空';
			} else if (!self::validate_field_max_length(
					$this->outsourcing_typename, 255)) {
				$errors[] = '外包类型名称长度最多255个字符';
			}

			if (!self::validate_money($this->max_amount)) {
				$errors[] = '最大金额限制设置有误';
			}

			if (self::validate_field_not_empty($this->remark)
					&& !self::validate_field_max_length($this->remark, 500)) {
				$errors[] = '备注长度最多500个字符';
			}

			if (self::validate_field_not_empty($this->outsourcing_process)
					&& !self::validate_id($this->outsourcing_process)) {
				$errors[] = '关联外包审核流程选择有误';
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

	public function addOutsourcingType() {
		if ($this->has_outsourcing_type_permission) {
			if ($this->validate_form_value('add')) {
				$success = TRUE;
				$error = '';
				$this->db->query('BEGIN');

				//检查类型名称是否唯一
				$row = $this->db
						->get_row(
								'SELECT id FROM outsourcing_type WHERE outsourcing_typename="'
										. $this->outsourcing_typename
										. '" FOR UPDATE');
				if ($row === NULL) {
					$insert_result = $this->db
							->query(
									'INSERT INTO outsourcing_type(outsourcing_typename,remark,addtime,isok,max_amount) VALUE("'
											. $this->outsourcing_typename
											. '",'
											. (empty($this->remark) ? 'NULL'
													: '"' . $this->remark . '"')
											. ',now(),1,' . $this->max_amount
											. ')');
					if ($insert_result === FALSE) {
						$success = FALSE;
						$error = '新建执行单外包类型失败';
					} else {
						if (!empty($this->outsourcing_process)) {
							$type_id = $this->db->insert_id;
							//关联流程
							$row = $this->db
									->get_row(
											'SELECT id FROM outsourcing_process WHERE id='
													. intval(
															$this
																	->outsourcing_process)
													. ' AND isok=1 FOR UPDATE');
							if ($row === NULL) {
								$success = FALSE;
								$error = '关联流程选择有误';
							} else {
								$insert_result = $this->db
										->query(
												'INSERT INTO outsourcing_type_process(type_id,process_id) VALUE('
														. $type_id . ','
														. intval(
																$this
																		->outsourcing_process)
														. ')');
								if ($insert_result === FALSE) {
									$success = FALSE;
									$error = '执行单外包类型关联流程失败';
								}
							}
						}
					}
				} else {
					$success = FALSE;
					$error = '系统中已有同名的执行单外包类型，请重新输入名称';
				}

				if ($success) {
					$this->db->query('COMMIT');
				} else {
					$this->db->query('ROLLBACK');
				}
				return array('status' => $success ? 'success' : 'error',
						'message' => $success ? '新建执行单外包类型成功' : $error);
			}
			return array('status' => 'error', 'message' => $this->errors);
		}
		return array('status' => 'error', 'message' => NO_RIGHT_TO_DO_THIS);
	}

	public function updateOutsourcingType() {
		if ($this->has_outsourcing_type_permission) {
			if ($this->validate_form_value('update')) {
				$success = TRUE;
				$error = '';
				$this->db->query('BEGIN');

				//检查类型名称是否唯一
				$row = $this->db
						->get_row(
								'SELECT id FROM outsourcing_type WHERE outsourcing_typename="'
										. $this->outsourcing_typename
										. '" AND id<>' . intval($this->id)
										. ' FOR UPDATE');
				if ($row === NULL) {
					$update_result = $this->db
							->query(
									'UPDATE outsourcing_type SET outsourcing_typename="'
											. $this->outsourcing_typename
											. '",remark='
											. (!empty($this->remark) ? '"'
															. $this->remark
															. '"' : 'NULL')
											. ',max_amount='
											. $this->max_amount . ' WHERE id='
											. intval($this->id));
					if ($update_result === FALSE) {
						$success = FALSE;
						$error = '更新执行单外包类型失败';
					} else {
						//删除老的关联
						$delete_result = $this->db
								->query(
										'DELETE FROM outsourcing_type_process WHERE type_id='
												. intval($this->id));
						if ($delete_result === FALSE) {
							$success = FALSE;
							$error = '更新执行单外包流程失败';
						} else {
							if (!empty($this->outsourcing_process)) {
								//关联流程
								$row = $this->db
										->get_row(
												'SELECT id FROM outsourcing_process WHERE id='
														. intval(
																$this
																		->outsourcing_process)
														. ' AND isok=1 FOR UPDATE');
								if ($row === NULL) {
									$success = FALSE;
									$error = '关联流程选择有误';
								} else {
									$insert_result = $this->db
											->query(
													'INSERT INTO outsourcing_type_process(type_id,process_id) VALUE('
															. intval($this->id)
															. ','
															. intval(
																	$this
																			->outsourcing_process)
															. ')');
									if ($insert_result === FALSE) {
										$success = FALSE;
										$error = '执行单外包类型关联流程失败';
									}
								}
							}
						}
					}
				} else {
					$success = FALSE;
					$error = '系统中已有同名的执行单外包类型，请重新输入名称';
				}

				if ($success) {
					$this->db->query('COMMIT');
				} else {
					$this->db->query('ROLLBACK');
				}
				return array('status' => $success ? 'success' : 'error',
						'message' => $success ? '更新执行单外包类型成功' : $error);
			}
			return array('status' => 'error', 'message' => $this->errors);
		}
		return array('status' => 'error', 'message' => NO_RIGHT_TO_DO_THIS);
	}

	private function _get_pagination($is_prev) {
		return '<a href="' . BASE_URL . 'outsourcing/?o=typelist&page='
				. ($is_prev ? intval($this->page) - 1 : intval($this->page) + 1)
				. '">' . ($is_prev ? '上一页' : '下一页') . '</a>';
	}

	private function _getNext() {
		if (intval($this->page) >= intval($this->page_count)) {
			return '';
		} else {
			return $this->_get_pagination(FALSE);
		}
	}

	private function _getPrev() {
		if (intval($this->page) === 1) {
			return '';
		} else {
			return $this->_get_pagination(TRUE);
		}
	}

	private function _get_outsourcing_type_counts() {
		return $this->page . '	/' . $this->page_count . ' 页 &nbsp;&nbsp;';
	}

	private static function _get_action($id, $isok) {
		$s = '';
		$s .= '<a href="' . BASE_URL . 'outsourcing/?o=editype&id=' . $id
				. '">修改</a>';
		return $s;
	}

	private function _get_outsourcing_type_list_html() {
		$this->all_count = intval(
				$this->db->get_var('SELECT COUNT(*) FROM outsourcing_type'));
		$this->page_count = ceil($this->all_count / self::LIMIT);
		$start = self::LIMIT * intval($this->page) - self::LIMIT;
		if ($start < 0) {
			$start = 0;
		}

		$results = $this->db
				->get_results(
						'SELECT a.*,c.outsourcing_process_name
FROM outsourcing_type a
LEFT JOIN outsourcing_type_process b
ON a.id=b.type_id
LEFT JOIN outsourcing_process c
ON b.process_id=c.id ORDER BY addtime DESC LIMIT ' . $start . ',' . self::LIMIT);
		$s = '';
		if ($results !== NULL) {
			foreach ($results as $key => $result) {
				$s .= '<tr><td>' . (($this->page - 1) * self::LIMIT + $key + 1)
						. '</td><td>' . $result->outsourcing_typename
						. '</td><td>'
						. ($result->max_amount == 0 ? '无限制'
								: Format_Util::my_money_format('%.2n',
										$result->max_amount)) . '</td><td>'
						. $result->remark . '</td><td>'
						. $result->outsourcing_process_name . '</td><td>'
						. $result->addtime . '</td><td>'
						. self::_get_action($result->id, $result->isok)
						. '</td></tr>';
			}
		}
		return $s;
	}

	public function getOutsourcingTypeListHtml() {
		if ($this->has_outsourcing_type_permission) {
			$buf = file_get_contents(
					TEMPLATE_PATH . 'outsourcing/outsourcing_type_list.tpl');
			return str_replace(
					array('[LEFT]', '[TOP]', '[VCODE]', '[EXECUTIVELIST]',
							'[ALLCOUNTS]', '[COUNTS]', '[NEXT]', '[PREV]',
							'[BASE_URL]'),
					array($this->get_left_html(), $this->get_top_html(),
							$this->get_vcode(),
							$this->_get_outsourcing_type_list_html(),
							$this->all_count,
							$this->_get_outsourcing_type_counts(),
							$this->_getNext(), $this->_getPrev(), BASE_URL),
					$buf);
		}
		return User::no_permission();
	}
}
