<?php
class Permission extends User {
	private static $instance = NULL;
	private $permission_id;
	private $name;
	private $des;
	private $module;
	private $errors = array();

	private $has_setup_permission = FALSE;

	/**
	 * @return the $has_setup_permission
	 */
	public function getHas_setup_permission() {
		return $this->has_setup_permission;
	}

	/**
	 * @return the $permission_id
	 */
	public function getPermission_id() {
		return $this->permission_id;
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

	public function __construct($permission_id = NULL, $fields = array()) {
		parent::__construct();
		if (in_array($this->getUsername(),
				$GLOBALS['manager_setup_permission'], TRUE)) {
			$this->has_setup_permission = TRUE;
		}
		if ($permission_id !== NULL) {
			if (!is_int($permission_id)) {
				$permission_id = intval($permission_id);
			}
			if (self::validate_id($permission_id)) {
				$permission = $this->db
						->get_row(
								'SELECT name,des,module FROM permissions WHERE id='
										. $permission_id . ' AND islive=1');
				if ($permission !== NULL) {
					$this->permission_id = intval($permission_id);
					$this->name = $permission->name;
					$this->des = $permission->des;
					$this->module = intval($permission->module);
				}
			}
		} else if (!empty($fields)) {
			foreach ($this as $key => $value) {
				if ($fields[$key] !== NULL
						&& !in_array($key, array('has_setup_permission'), TRUE)) {
					$this->$key = $fields[$key];
				}
			}
		}
	}

	public static function getInstance($force_flush = FALSE) {
		if (self::$instance === NULL || $force_flush) {
			$permission_cache_filename = md5('permission_cache_filename');
			$permission_cache = new FileCache(CACHE_TIME, CACHE_PATH);
			$permission_cache_file = $permission_cache
					->get($permission_cache_filename);

			if ($permission_cache_file === FALSE || $force_flush) {
				//读取数据库
				$dao = new Dao_Impl();
				$permissions = $dao->db
						->get_results(
								'SELECT id,name,des,module FROM permissions WHERE islive=1');
				if ($permissions !== NULL) {
					$datas = array();
					foreach ($permissions as $permission) {
						$datas[$permission->module][] = array(
								'permission_id' => $permission->id,
								'permission_name' => $permission->name,
								'permission_des' => $permission->des);
					}
					$permission_cache->set($permission_cache_filename, $datas);
				}
			}
			self::$instance = $permission_cache
					->get($permission_cache_filename);
		}
		return self::$instance;
	}

	public function del_permission() {
		if ($this->permission_id !== NULL) {
			$update_result = $this->db
					->query(
							'UPDATE permissions SET islive=0 WHERE id='
									. $this->permission_id);
			if ($update_result > 0) {
				self::getInstance(TRUE);
				return array('status' => 'success', 'message' => '删除模块角色权限成功');
			}
			return array('status' => 'success', 'message' => '删除模块角色权限失败');
		}
		return array('status' => 'error', 'message' => '模块角色权限选择有误');
	}

	public function get_module_html() {
		return Module::get_module_html(FALSE, $this->module);
	}

	private function validate_form_value($action) {
		$errors = array();
		if ($action === 'update') {
			$permission_id = intval($this->permission_id);
			if (!self::validate_id($permission_id)) {
				$errors[] = '模块角色权限选择有误';
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
		} else if (!self::validate_field_max_length($name, 200)) {
			$errors[] = '名称长度最多200个字符';
		}

		$des = $this->des;
		if (self::validate_field_not_empty($des)
				&& !self::validate_field_max_length($des, 200)) {
			$errors[] = '描述长度最多200个字符';
		}

		if (empty($errors)) {
			return TRUE;
		}
		$this->errors = $errors;
		unset($errors);
		return FALSE;
	}

	public function add_permission() {
		if ($this->validate_form_value('add')) {
			//通过验证
			$query = Sql_Util::get_insert('permissions',
					array('module' => intval($this->module),
							'name' => $this->name,
							'des' => !empty($this->des) ? $this->des : '',
							'time' => $_SERVER['REQUEST_TIME']));
			if ($query['status'] === 'success') {
				$insert_result = $this->db->query($query['sql']);
				if ($insert_result === FALSE || $insert_result === 0) {
					//插入失败
					return array('status' => 'error', 'message' => '新建模块角色权限出错');
				}
				self::getInstance(TRUE);
				return array('status' => 'success', 'message' => '新建模块角色权限成功');
			} else {
				//SQL错误
				return array('status' => 'error', 'message' => '系统内部错误');
			}
		}
		return array('status' => 'error', 'message' => $this->errors);
	}

	public function update_permission() {
		if ($this->validate_form_value('update')) {
			$query = Sql_Util::get_update('permissions',
					array('module' => intval($this->module),
							'name' => $this->name,
							'des' => !empty($this->des) ? $this->des : ''),
					array('id' => array('=', $this->permission_id)), 'AND');
			if ($query['status'] === 'success') {
				$update_result = $this->db->query($query['sql']);
				if ($update_result === FALSE || $update_result === 0) {
					//更新失败
					return array('status' => 'error', 'message' => '更新模块角色权限出错');
				}
				self::getInstance(TRUE);
				return array('status' => 'success', 'message' => '更新模块角色权限成功');
			} else {
				//SQL错误
				return array('status' => 'error', 'message' => '系统内部错误');
			}
		}
		return array('status' => 'error', 'message' => $this->errors);
	}

	public function get_permission_add_html() {
		if ($this->getHas_setup_permission()) {
			$buf = file_get_contents(
					TEMPLATE_PATH . 'system/permission_add.tpl');
			return str_replace(
					array('[LEFT]', '[TOP]', '[VCODE]', '[MODULELIST]',
							'[BASE_URL]'),
					array($this->get_left_html(), $this->get_top_html(),
							$this->get_vcode(), $this->get_module_html(),
							BASE_URL), $buf);
		} else {
			return User::no_permission();
		}
	}

	public function get_permission_edit_html() {
		if ($this->getHas_setup_permission()) {
			$buf = file_get_contents(
					TEMPLATE_PATH . 'system/permission_edit.tpl');
			return str_replace(
					array('[LEFT]', '[TOP]', '[MODULELIST]', '[NAME]', '[DES]',
							'[VCODE]', '[PERMISSION_ID]', '[BASE_URL]'),
					array($this->get_left_html(), $this->get_top_html(),
							$this->get_module_html(), $this->name, $this->des,
							$this->get_vcode(), $this->permission_id, BASE_URL),
					$buf);
		} else {
			return User::no_permission();
		}
	}
}