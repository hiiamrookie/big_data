<?php
class Outsourcing_Process extends User {
	private $has_outsourcing_process_permission = FALSE;

	private $id;
	private $outsourcing_process_name;
	private $remark;
	private $process;

	private $errors = array();

	private $page;
	private $all_count;
	private $page_count;

	const LIMIT = 50;

	private $varc;
	private $content_count;

	public function __construct($fields = array()) {
		parent::__construct();
		if (in_array($this->getUsername(),
				$GLOBALS['manager_outsourcing_process_permission'], TRUE)) {
			$this->has_outsourcing_process_permission = TRUE;
			if (!empty($fields)) {
				foreach ($this as $key => $value) {
					if ($fields[$key] !== NULL
							&& !in_array($key,
									array('has_outsourcing_process_permission'),
									TRUE)) {
						$this->$key = $fields[$key];
					}
				}
			}
		}
	}

	public static function getInstance($force_flush = FALSE) {
		$process_cache_filename = md5('outsourcing_process_cache_filename');
		$process_cache = new FileCache(CACHE_TIME, CACHE_PATH);
		$process_cache_file = $process_cache->get($process_cache_filename);
		if ($process_cache_file === FALSE || $force_flush) {
			//读取数据库
			$dao = new Dao_Impl();
			$process = $dao->db
					->get_results(
							'SELECT id,outsourcing_process_name,process FROM outsourcing_process WHERE isok=1');
			if ($process !== NULL) {
				$datas = array();
				foreach ($process as $p) {
					$datas[$p->id] = array(
							'name' => $p->outsourcing_process_name,
							'process' => $p->process);
				}
				$process_cache->set($process_cache_filename, $datas);
			}
		}
		$process_cache_file = $process_cache->get($process_cache_filename);
		return $process_cache_file;
	}

	public function getAddOutsourcingProcessHtml() {
		if ($this->has_outsourcing_process_permission) {
			$buf = file_get_contents(
					TEMPLATE_PATH . 'outsourcing/outsourcing_process_add.tpl');
			return str_replace(
					array('[LEFT]', '[TOP]', '[VCODE]', '[BASE_URL]'),
					array($this->get_left_html(), $this->get_top_html(),
							$this->get_vcode(), BASE_URL), $buf);
		}
		return User::no_permission();
	}

	private function _get_process_content_html($process) {
		$result = '';
		$tmp_content = '<div>审核人：<input type="text" class="validate[required]" name="auditer_[COUNT]" id="auditer_[COUNT]" style="height:20px;width:200px;" value="[AUDITER]"/> &nbsp;&nbsp;<img src="'
				. BASE_URL
				. 'images/close.png" onclick="del(this,\'[COUNT]\')" width="17" height="17" /><br /></div>';
		$content_count = ',';
		if (!empty($process)) {
			$process = json_decode($process);
			$count = 1;
			foreach ($process as $p) {
				$result .= str_replace(array('[AUDITER]', '[COUNT]'),
						array($p, $count), $tmp_content);
				$content_count .= $count . ',';
				$count++;
			}
		}
		$this->content_count = $content_count;
		$this->varc = $count;
		return $result;

		/*
		$tmp_content = '<div>名称：<input type="text" class="validate[required]" value="[NAME]" name="pname_[COUNT]" style="height:20px;"/> &nbsp;&nbsp;权限：<select onchange="showpermission(this)" class="select" style="width:200px">'
		        . Module::get_module_html(FALSE)
		        . Dep::get_dep_select_html(FALSE, FALSE)
		        . '</select>&nbsp;&nbsp;<select onchange="setpermission(this)" class="select" style="width:200px"><option value="">请选择</option></select>&nbsp;&nbsp;<input type="text" class="validate[required]"  readonly="readonly" style="width:150px;height:20px;" value="[PERMISSION]" name="content_[COUNT]"> &nbsp;&nbsp;<img src="'
		        . BASE_URL
		        . 'images/close.png" onclick="del(this,\'[COUNT]\')" width="17" height="17" /><br /></div>';
		$content_count = ',';
		if ($this->content !== NULL) {
		    $contents = $this->content;
		    $contents = explode('_', $contents);
		    $count = 1;
		    foreach ($contents as $content) {
		        $content = explode('^', $content);
		        $result .= str_replace(
		                array('[NAME]', '[PERMISSION]', '[COUNT]'),
		                array($content[0], $content[1] . '^' . $content[2],
		                        $count), $tmp_content);
		        $content_count .= $count . ',';
		        $count++;
		    }
		}
		$this->content_count = $content_count;
		$this->varc = $count;
		return $result;
		 */
	}

	public function getEditOutsourcingProcessHtml() {
		if ($this->has_outsourcing_process_permission) {
			$row = $this->db
					->get_row(
							'SELECT * FROM outsourcing_process WHERE id='
									. intval($this->id));
			if ($row !== NULL) {
				$buf = file_get_contents(
						TEMPLATE_PATH
								. 'outsourcing/outsourcing_process_edit.tpl');
				return str_replace(
						array('[LEFT]', '[TOP]', '[VCODE]',
								'[OUTSOURCINGPROCESSNAME]', '[REMARK]',
								'[PROCESSCONTENTLIST]', '[VARC]', '[CONTENTS]',
								'[ID]', '[BASE_URL]'),
						array($this->get_left_html(), $this->get_top_html(),
								$this->get_vcode(),
								$row->outsourcing_process_name, $row->remark,
								$this->_get_process_content_html($row->process),
								$this->varc, $this->content_count,
								intval($this->id), BASE_URL), $buf);
			}
			return User::no_object('没有该执行单外包审核流程');
		}
		return User::no_permission();
	}

	private function validate_form_value($action) {
		$errors = array();
		if (in_array($action, array('add', 'update'), TRUE)) {
			if ($action === 'update') {
				if (!self::validate_id(intval($this->id))) {
					$errors[] = '外包审核流程选择有误';
				}
			}

			if (!self::validate_field_not_empty($this->outsourcing_process_name)
					|| !self::validate_field_not_null(
							$this->outsourcing_process_name)) {
				$errors[] = '外包审核流程名称不能为空';
			} else if (!self::validate_field_max_length(
					$this->outsourcing_process_name, 255)) {
				$errors[] = '外包审核流程名称长度最多255个字符';
			}

			if (self::validate_field_not_empty($this->remark)
					&& !self::validate_field_max_length($this->remark, 500)) {
				$errors[] = '备注长度最多500个字符';
			}

			if (empty($this->process)) {
				$errors[] = '审核流程不能为空';
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

	public function addOutsourcingProcess() {
		if ($this->has_outsourcing_process_permission) {
			if ($this->validate_form_value('add')) {
				$success = TRUE;
				$error = '';
				$this->db->query('BEGIN');

				//检查类型名称是否唯一
				$row = $this->db
						->get_row(
								'SELECT id FROM outsourcing_process WHERE outsourcing_process_name="'
										. $this->outsourcing_process_name
										. '" FOR UPDATE');
				if ($row === NULL) {
					$insert_result = $this->db
							->query(
									'INSERT INTO outsourcing_process(outsourcing_process_name,process,remark,addtime,isok) VALUE("'
											. $this->outsourcing_process_name
											. '",\''
											. urldecode(
													json_encode($this->process))
											. '\','
											. (empty($this->remark) ? 'NULL'
													: '"' . $this->remark . '"')
											. ',now(),1)');
					if ($insert_result === FALSE) {
						$success = FALSE;
						$error = '新建执行单外包审核流程失败';
					}
				} else {
					$success = FALSE;
					$error = '系统中已有同名的执行单外包审核流程，请重新输入名称';
				}

				if ($success) {
					$this->db->query('COMMIT');
					self::getInstance(TRUE);
				} else {
					$this->db->query('ROLLBACK');
				}
				return array('status' => $success ? 'success' : 'error',
						'message' => $success ? '新建执行单外包审核流程成功' : $error);
			}
			return array('status' => 'error', 'message' => $this->errors);
		}
		return array('status' => 'error', 'message' => NO_RIGHT_TO_DO_THIS);

	}

	public function updateOutsourcingProcess() {
		if ($this->has_outsourcing_process_permission) {
			if ($this->validate_form_value('update')) {
				$success = TRUE;
				$error = '';
				$this->db->query('BEGIN');

				//检查类型名称是否唯一
				$row = $this->db
						->get_row(
								'SELECT id FROM outsourcing_process WHERE outsourcing_process_name="'
										. $this->outsourcing_process_name
										. '" AND id<>' . intval($this->id)
										. ' FOR UPDATE');
				if ($row === NULL) {
					$update_result = $this->db
							->query(
									'UPDATE outsourcing_process SET outsourcing_process_name="'
											. $this->outsourcing_process_name
											. '",process=\''
											. urldecode(
													json_encode($this->process))
											. '\',remark='
											. (!empty($this->remark) ? '"'
															. $this->remark
															. '"' : 'NULL')
											. ' WHERE id=' . intval($this->id));
					if ($update_result === FALSE) {
						$success = FALSE;
						$error = '更新执行单外包审核流程失败';
					}
				} else {
					$success = FALSE;
					$error = '系统中已有同名的执行单外包审核流程，请重新输入名称';
				}

				if ($success) {
					$this->db->query('COMMIT');
					self::getInstance(TRUE);
				} else {
					$this->db->query('ROLLBACK');
				}
				return array('status' => $success ? 'success' : 'error',
						'message' => $success ? '更新执行单外包审核流程成功' : $error);
			}
			return array('status' => 'error', 'message' => $this->errors);
		}
		return array('status' => 'error', 'message' => NO_RIGHT_TO_DO_THIS);
	}

	private function _get_outsourcing_process_list_html() {
		$this->all_count = intval(
				$this->db->get_var('SELECT COUNT(*) FROM outsourcing_process'));
		$this->page_count = ceil($this->all_count / self::LIMIT);
		$start = self::LIMIT * intval($this->page) - self::LIMIT;
		if ($start < 0) {
			$start = 0;
		}

		$results = $this->db
				->get_results(
						'SELECT * FROM outsourcing_process ORDER BY addtime DESC LIMIT '
								. $start . ',' . self::LIMIT);
		$s = '';
		if ($results !== NULL) {
			foreach ($results as $key => $result) {
				$s .= '<tr><td>' . (($this->page - 1) * self::LIMIT + $key + 1)
						. '</td><td>' . $result->outsourcing_process_name
						. '</td><td>'
						. (is_null(json_decode($result->process)) ? ($result
										->process)
								: implode(' -&gt; ',
										json_decode($result->process)))
						. '</td><td>' . $result->remark . '</td><td>'
						. $result->addtime . '</td><td>'
						. self::_get_action($result->id, $result->isok)
						. '</td></tr>';
			}
		}
		return $s;
	}

	private function _get_outsourcing_process_counts() {
		return $this->page . '	/' . $this->page_count . ' 页 &nbsp;&nbsp;';
	}

	private function _get_pagination($is_prev) {
		return '<a href="' . BASE_URL . 'outsourcing/?o=processlist&page='
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

	private static function _get_action($id, $isok) {
		$s = '';
		$s .= '<a href="' . BASE_URL . 'outsourcing/?o=editprocess&id=' . $id
				. '">修改</a>';
		return $s;
	}

	public function getOutsourcingProcessListHtml() {
		if ($this->has_outsourcing_process_permission) {
			$buf = file_get_contents(
					TEMPLATE_PATH . 'outsourcing/outsourcing_process_list.tpl');
			return str_replace(
					array('[LEFT]', '[TOP]', '[VCODE]', '[EXECUTIVELIST]',
							'[ALLCOUNTS]', '[COUNTS]', '[NEXT]', '[PREV]',
							'[BASE_URL]'),
					array($this->get_left_html(), $this->get_top_html(),
							$this->get_vcode(),
							$this->_get_outsourcing_process_list_html(),
							$this->all_count,
							$this->_get_outsourcing_process_counts(),
							$this->_getNext(), $this->_getPrev(), BASE_URL),
					$buf);
		}
		return User::no_permission();
	}
}
