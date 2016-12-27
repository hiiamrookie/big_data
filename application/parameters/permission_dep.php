<?php
class Permission_Dep extends User {
	private static $instance = NULL;
	private $permission_id;
	private $name;
	private $des;
	private $dep;
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

	public static function getInstance($force_flush = FALSE) {
		if (self::$instance === NULL || $force_flush) {
			$permission_dep_cache_filename = md5(
					'permission_dep_cache_filename');
			$permission_dep_cache = new FileCache(CACHE_TIME, CACHE_PATH);
			$permission_dep_cache_file = $permission_dep_cache
					->get($permission_dep_cache_filename);

			if ($permission_dep_cache_file === FALSE || $force_flush) {
				//读取数据库
				$dao = new Dao_Impl();
				$permissions_dep = $dao->db
						->get_results(
								'SELECT a.id,a.name,a.des,a.dep FROM permissions_dep a, hr_department b WHERE a.dep=b.id AND a.islive=1 AND b.islive=1');
				if ($permissions_dep !== NULL) {
					$datas = array();
					foreach ($permissions_dep as $permission) {
						$datas[$permission->dep][] = array(
								'permission_id' => $permission->id,
								'permission_name' => $permission->name,
								'permission_des' => $permission->des);
					}
					$permission_dep_cache
							->set($permission_dep_cache_filename, $datas);
				}
			}
			self::$instance = $permission_dep_cache
					->get($permission_dep_cache_filename);
		}
		return self::$instance;
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
								'SELECT name,des,dep FROM permissions_dep WHERE id='
										. $permission_id . ' AND islive=1');
				if ($permission !== NULL) {
					$this->permission_id = intval($permission_id);
					$this->name = $permission->name;
					$this->des = $permission->des;
					$this->dep = intval($permission->dep);
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

	public function del_deppermission() {
		if ($this->permission_id !== NULL) {
			$update_result = $this->db
					->query(
							'UPDATE permissions_dep SET islive=0 WHERE id='
									. $this->permission_id);
			if ($update_result > 0) {
				self::getInstance(TRUE);
				return array('status' => 'success', 'message' => '删除部门角色权限成功');
			}
			return array('status' => 'success', 'message' => '删除部门角色权限失败');
		}
		return array('status' => 'error', 'message' => '部门角色权限选择有误');
	}

	public function get_dep_html($is_new = FALSE) {
		if ($is_new) {
			return Dep::get_dep_select_html(FALSE, TRUE);
		}
		return Dep::get_dep_select_html(FALSE, TRUE, $this->dep);
	}

	private function validate_form_value($action) {
		$errors = array();
		if ($action === 'update') {
			$permission_id = intval($this->permission_id);
			if (!self::validate_id($permission_id)) {
				$errors[] = '部门角色权限选择有误';
			}
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

		$dep = intval($this->dep);
		if (!self::validate_id($dep)) {
			$errors[] = '部门选择有误';
		}

		if (empty($errors)) {
			return TRUE;
		}
		$this->errors = $errors;
		unset($errors);
		return FALSE;
	}

	public function add_deppermission() {
		if ($this->validate_form_value('add')) {
			//通过验证
			$query = Sql_Util::get_insert('permissions_dep',
					array('name' => $this->name,
							'des' => !empty($this->des) ? $this->des : '',
							'dep' => intval($this->dep),
							'time' => $_SERVER['REQUEST_TIME']));
			if ($query['status'] === 'success') {
				$insert_result = $this->db->query($query['sql']);
				if ($insert_result === FALSE || $insert_result === 0) {
					//插入失败
					return array('status' => 'error', 'message' => '新建部门角色权限出错');
				}
				self::getInstance(TRUE);
				return array('status' => 'success', 'message' => '新建部门角色权限成功');
			} else {
				//SQL错误
				return array('status' => 'error', 'message' => '系统内部错误');
			}
		}
		return array('status' => 'error', 'message' => $this->errors);
	}

	public function update_deppermission() {
		if ($this->validate_form_value('update')) {
			//通过验证
			$query = Sql_Util::get_update('permissions_dep',
					array('name' => $this->name,
							'des' => !empty($this->des) ? $this->des : '',
							'dep' => intval($this->dep)),
					array('id' => array('=', intval($this->permission_id))),
					'AND');
			if ($query['status'] === 'success') {
				$update_result = $this->db->query($query['sql']);
				if ($update_result === FALSE || $update_result === 0) {
					//更新失败
					return array('status' => 'error', 'message' => '更新部门角色权限出错');
				}
				self::getInstance(TRUE);
				return array('status' => 'success', 'message' => '更新部门角色权限成功');
			} else {
				//SQL错误
				return array('status' => 'error', 'message' => '系统内部错误');
			}
		}
		return array('status' => 'error', 'message' => $this->errors);
	}

	public function get_dep_permission_add_html() {
		if ($this->getHas_setup_permission()) {
			$buf = file_get_contents(
					TEMPLATE_PATH . 'system/deppermission_add.tpl');
			return str_replace(
					array('[LEFT]', '[TOP]', '[VCODE]', '[DEPLIST]',
							'[BASE_URL]'),
					array($this->get_left_html(), $this->get_top_html(),
							$this->get_vcode(), $this->get_dep_html(TRUE),
							BASE_URL), $buf);
		} else {
			return User::no_permission();
		}
	}

	public function get_dep_permission_edit_html() {
		if ($this->getHas_setup_permission()) {
			$buf = file_get_contents(
					TEMPLATE_PATH . 'system/deppermission_edit.tpl');
			return str_replace(
					array('[LEFT]', '[TOP]', '[DEPLIST]', '[NAME]', '[DES]',
							'[VCODE]', '[PERMISSION_ID]', '[BASE_URL]'),
					array($this->get_left_html(), $this->get_top_html(),
							$this->get_dep_html(), $this->name, $this->des,
							$this->get_vcode(), $this->permission_id, BASE_URL),
					$buf);
		} else {
			return User::no_permission();
		}
	}
}
