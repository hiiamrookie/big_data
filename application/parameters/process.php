<?php
class Process extends User {
	private $process_id;
	private $deps;
	private $module;
	private $name;
	private $des;
	private $content;
	private $deps_val;
	private $content_val;
	private $errors = array();
	private $content_count;
	private $varc;

	private $has_process_permission = FALSE;

	/**
	 * @return the $has_process_permission
	 */
	public function getHas_process_permission() {
		return $this->has_process_permission;
	}

	/**
	 * @return the $process_id
	 */
	public function getProcess_id() {
		return $this->process_id;
	}

	/**
	 * @return the $content_count
	 */
	public function getContent_count() {
		return $this->content_count;
	}

	/**
	 * @return the $varc
	 */
	public function getVarc() {
		return $this->varc;
	}

	/**
	 * @return the $name
	 */
	public function getName() {
		return $this->name;
	}

	/**
	 * @return the $des
	 */
	public function getDes() {
		return $this->des;
	}

	public function __construct($process_id = NULL, $fields = array()) {
		parent::__construct();
		if (in_array($this->getUsername(),
				$GLOBALS['manager_setup_permission'], TRUE)) {
			$this->has_process_permission = TRUE;
		}

		if ($process_id !== NULL) {
			if (!is_int($process_id)) {
				$process_id = intval($process_id);
			}
			if (self::validate_id($process_id)) {
				$process = $this->db
						->get_row(
								'SELECT deps,module,name,des,content FROM process WHERE id='
										. $process_id . ' AND islive=1');
				if ($process !== NULL) {
					$this->process_id = intval($process_id);
					$this->deps = $process->deps;
					$this->module = intval($process->module);
					$this->name = $process->name;
					$this->des = $process->des;
					$this->content = $process->content;
				}
			}
		} else if (!empty($fields)) {
			foreach ($this as $key => $value) {
				if ($fields[$key] !== NULL
						&& !in_array($key, array('has_process_permission'),
								TRUE)) {
					$this->$key = $fields[$key];
				}
			}
		}
	}

	private function validate_form_value($action) {
		$errors = array();
		if ($action === 'update') {
			$process_id = intval($this->process_id);
			if (!self::validate_id($process_id)) {
				$errors[] = '流程选择有误';
			}
		}

		$module_id = intval($this->module);
		if (!self::validate_id($module_id)) {
			$errors[] = '模块选择有误';
		}

		$name = $this->name;
		if (!self::validate_field_not_empty($name)
				|| !self::validate_field_not_null($name)) {
			$errors[] = '名称不能为空';
		} else if (!self::validate_field_max_length($name, 50)) {
			$errors[] = '名称长度最多50个字符';
		}

		$des = $this->des;
		if (self::validate_field_not_empty($des)
				&& !self::validate_field_max_length($des, 500)) {
			$errors[] = '描述长度最多500个字符';
		}

		$deps_val = $this->deps_val;
		if (!self::validate_field_not_empty($deps_val)) {
			$errors[] = '至少选择一个应用部门';
		} else {
			$pass_check = TRUE;
			foreach ($deps_val as $deps) {
				$deps = intval($deps);
				if (!self::validate_id($deps)) {
					$errors[] = '应用部门选择有误';
					$pass_check = FALSE;
					break;
				}
			}

			if ($pass_check) {
				$this->deps = implode('^', $deps_val);
			}
		}

		$content_val = $this->content_val;
		if (!self::validate_field_not_empty($content_val)) {
			$errors[] = '至少选择一个流程内容';
		} else {
			$this->content = implode('_', $content_val);
		}

		if (empty($errors)) {
			return TRUE;
		}
		$this->errors = $errors;
		unset($errors);
		return FALSE;
	}

	public static function getInstance($force_flush = FALSE) {
		$process_cache_filename = md5('process_cache_filename');
		$process_cache = new FileCache(CACHE_TIME, CACHE_PATH);
		$process_cache_file = $process_cache->get($process_cache_filename);
		if ($process_cache_file === FALSE || $force_flush) {
			//读取数据库
			$dao = new Dao_Impl();
			$process = $dao->db
					->get_results(
							'SELECT id,module,name,deps,content FROM process WHERE islive=1');
			if ($process !== NULL) {
				$datas = array();
				foreach ($process as $p) {
					$content = $p->content;
					if (!empty($content)) {
						$content = explode('_', $content);
						$datas['module'][$p->module][] = array('id' => $p->id,
								'name' => $p->name, 'deps' => $p->deps);
						foreach ($content as $c) {
							$datas['step'][$p->id][] = array(
									'content' => explode('^', $c));
						}
					}
				}
				$process_cache->set($process_cache_filename, $datas);
			}
		}
		$process_cache_file = $process_cache->get($process_cache_filename);
		return $process_cache_file;
	}

	public function del_process() {
		if ($this->process_id !== NULL) {
			$update_result = $this->db
					->query(
							'UPDATE process SET islive=0 WHERE id='
									. $this->process_id);
			if ($update_result > 0) {
				self::getInstance(TRUE);
				return array('status' => 'success', 'message' => '删除流程成功');
			}
			return array('status' => 'success', 'message' => '删除流程失败');
		}
		return array('status' => 'error', 'message' => '流程选择有误');
	}

	public function get_support_dep_html() {
		return Dep::get_dep_checkbox_html(FALSE,
				$this->deps !== NULL ? explode('^', $this->deps) : array());
	}

	public function get_module_html() {
		return Module::get_module_html(FALSE, $this->module);
	}

	public function get_dep_html() {
		return Dep::get_dep_select_html(FALSE, FALSE);
	}

	public function get_process_content_html() {
		$result = '';
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
	}

	public function add_process() {
		if ($this->validate_form_value('add')) {
			//通过验证
			$query = Sql_Util::get_insert('process',
					array('module' => intval($this->module),
							'name' => $this->name,
							'des' => !empty($this->des) ? $this->des : '',
							'deps' => $this->deps, 'content' => $this->content,
							'time' => $_SERVER['REQUEST_TIME']));
			if ($query['status'] === 'success') {
				$insert_result = $this->db->query($query['sql']);
				if ($insert_result === FALSE || $insert_result === 0) {
					//插入失败
					return array('status' => 'error', 'message' => '新建流程出错');
				}
				self::getInstance(TRUE);
				return array('status' => 'success', 'message' => '新建流程成功');
			} else {
				//SQL错误
				return array('status' => 'error', 'message' => '系统内部错误');
			}
		}
		return array('status' => 'error', 'message' => $this->errors);
	}

	public function update_process() {
		if ($this->validate_form_value('update')) {
			//通过验证
			$update = Sql_Util::get_update('process',
					array('module' => intval($this->module),
							'name' => $this->name,
							'des' => !empty($this->des) ? $this->des : '',
							'deps' => $this->deps, 'content' => $this->content),
					array('id' => array('=', intval($this->process_id))),
					'AND');
			if ($update['status'] === 'success') {
				$update_result = $this->db->query($update['sql']);
				if ($update_result === FALSE || $update_result === 0) {
					//更新失败
					return array('status' => 'error', 'message' => '更新流程出错');
				}
				self::getInstance(TRUE);
				return array('status' => 'success', 'message' => '更新流程成功');
			} else {
				//SQL错误
				return array('status' => 'error', 'message' => '系统内部错误');
			}
		}
		return array('status' => 'error', 'message' => $this->errors);
	}

	public static function get_DEP_key($processes) {
		foreach ($processes as $key => $process) {
			$process = $process['content'];
			if ($process[2] === 'DEP') {
				return $key;
			}
		}
		return NULL;
	}

	public function get_process_add_html() {
		if ($this->getHas_process_permission()) {
			$buf = file_get_contents(TEMPLATE_PATH . 'manage/process_add.tpl');
			return str_replace(
					array('[LEFT]', '[TOP]', '[SUPPORTDEP]', '[MODULELIST]',
							'[ALLDEPLIST]', '[VCODE]', '[BASE_URL]'),
					array($this->get_left_html(), $this->get_top_html(),
							$this->get_support_dep_html(),
							$this->get_module_html(), $this->get_dep_html(),
							$this->get_vcode(), BASE_URL), $buf);
		} else {
			return User::no_permission();
		}
	}

	public function get_process_edit_html() {
		if ($this->getHas_process_permission()) {
			if ($this->process_id === NULL) {
				return User::no_object('没有该流程');
			} else {
				$buf = file_get_contents(
						TEMPLATE_PATH . 'manage/process_edit.tpl');
				return str_replace(
						array('[LEFT]', '[TOP]', '[SUPPORTDEP]',
								'[MODULELIST]', '[NAME]', '[DES]',
								'[PROCESSCONTENTLIST]', '[ALLDEPLIST]',
								'[VCODE]', '[CONTENTS]', '[VARC]',
								'[PROCESS_ID]', '[BASE_URL]'),
						array($this->get_left_html(), $this->get_top_html(),
								$this->get_support_dep_html(),
								$this->get_module_html(), $this->name,
								$this->des, $this->get_process_content_html(),
								$this->get_dep_html(), $this->get_vcode(),
								$this->content_count, $this->varc,
								$this->process_id, BASE_URL,), $buf);
			}
		} else {
			return User::no_permission();
		}
	}
}
