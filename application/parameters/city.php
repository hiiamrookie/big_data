<?php
class City extends User {
	private static $instance = NULL;
	private $companyname;
	private $city_id;
	private $errors = array();

	private $has_hr_permission = FALSE;

	/**
	 * @return the $has_hr_permission
	 */
	public function getHas_hr_permission() {
		return $this->has_hr_permission;
	}

	public static function getInstance($force_flush = FALSE) {
		if (self::$instance === NULL || $force_flush) {
			$city_cache_filename = md5('city_cache_filename');
			$city_cache = new FileCache(CACHE_TIME, CACHE_PATH);
			$city_cache_file = $city_cache->get($city_cache_filename);
			if ($city_cache_file === FALSE || $force_flush) {
				//读取数据库
				$dao = new Dao_Impl();
				$citys = $dao->db
						->get_results(
								'SELECT id,companyname FROM hr_company WHERE islive=1');
				if ($citys !== NULL) {
					$datas = array();
					foreach ($citys as $city) {
						$datas[$city->id] = $city->companyname;
					}
					$city_cache->set($city_cache_filename, $datas);
				}
			}
			self::$instance = $city_cache->get($city_cache_filename);
		}
		return self::$instance;
	}

	public function __construct($city_id = NULL, $fields = array()) {
		parent::__construct();
		if (in_array($this->getUsername(),
				$GLOBALS['manager_setup_permission'], TRUE)) {
			$this->has_hr_permission = TRUE;
		}
		if ($city_id !== NULL) {
			if (!is_int($city_id)) {
				$city_id = intval($city_id);
			}
			if (self::validate_id($city_id)) {
				$city = $this->db
						->get_row(
								'SELECT companyname FROM hr_company WHERE id='
										. intval($city_id));
				if ($city !== NULL) {
					$this->city_id = intval($city_id);
					$this->companyname = $city->companyname;
				}
			}
		} else if (!empty($fields)) {
			foreach ($this as $key => $value) {
				if ($fields[$key] !== NULL
						&& !in_array($key, array('has_hr_permission'), TRUE)) {
					$this->$key = $fields[$key];
				}
			}
		}
	}

	/**
	 * @return the $city_id
	 */
	public function getCity_id() {
		return $this->city_id;
	}

	/**
	 * @return the $companyname
	 */
	public function getCompanyname() {
		return $this->companyname;
	}

	public static function get_city_select_html($force_flush, $city = NULL) {
		self::getInstance($force_flush);
		$instance = self::$instance;
		$result = '<option value="">请选择分公司</option>';
		if ($instance !== NULL) {
			foreach ($instance as $city_id => $city_name) {
				$result .= '<option value="' . $city_id . '" '
						. ($city !== NULL && intval($city_id) === intval($city) ? 'selected="selected"'
								: '') . '>' . $city_name . '</option>';
			}
		}
		return $result;
	}

	private function validate_form_value($action) {
		$errors = array();
		if (in_array($action, array('add_company', 'update_company'), TRUE)) {
			if (!in_array($this->getUsername(),
					$GLOBALS['manager_setup_permission'], TRUE)) {
				$errors[] = '无权限操作';
			} else {
				if ($action === 'update_company') {
					$city_id = intval($this->city_id);
					if (!self::validate_id($city_id)) {
						$errors[] = '公司选择有误';
					}
				}

				$companyname = $this->companyname;
				if (!self::validate_field_not_empty($companyname)
						|| !self::validate_field_not_null($companyname)) {
					$errors[] = '名称不能为空';
				} else if (!self::validate_field_max_length($companyname, 20)) {
					$errors[] = '名称长度最多20个字符';
				} else {
					$query = 'SELECT COUNT(*) FROM hr_company WHERE companyname="'
							. $companyname . '" AND islive=1';
					if ($action === 'update_company') {
						$query .= ' AND id!=' . intval($this->city_id);
					}
					$count = $this->db->get_var($query);
					if (intval($count) > 0) {
						$errors[] = '公司名已被使用';
					}
				}
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

	public function add_company() {
		if ($this->validate_form_value('add_company')) {
			$query = Sql_Util::get_insert('hr_company',
					array('companyname' => $this->companyname,
							'time' => $_SERVER['REQUEST_TIME']));
			if ($query['status'] === 'success') {
				$insert_result = $this->db->query($query['sql']);
				if ($insert_result === FALSE || $insert_result === 0) {
					//插入失败
					return array('status' => 'error', 'message' => '新建公司信息出错');
				}
				self::getInstance(TRUE);
				return array('status' => 'success', 'message' => '新建公司信息成功');
			} else {
				//SQL错误
				return array('status' => 'error', 'message' => '系统内部错误');
			}
		}
		return array('status' => 'error', 'message' => $this->errors);
	}

	public function update_company() {
		if ($this->validate_form_value('update_company')) {
			$query = Sql_Util::get_update('hr_company',
					array('companyname' => $this->companyname),
					array('id' => array('=', intval($this->city_id))), 'AND');
			if ($query['status'] === 'success') {
				$update_result = $this->db->query($query['sql']);
				if ($update_result === FALSE || $update_result === 0) {
					//更新失败
					return array('status' => 'error', 'message' => '更新公司信息出错');
				}
				self::getInstance(TRUE);
				return array('status' => 'success', 'message' => '更新公司信息成功');
			} else {
				//SQL错误
				return array('status' => 'error', 'message' => '系统内部错误');
			}
		}
		return array('status' => 'error', 'message' => $this->errors);
	}

	private function _get_users_by_city() {
		if (intval($this->city_id) > 0) {
			return $this->db
					->get_results(
							'SELECT uid,realname,username FROM users WHERE city='
									. intval($this->city_id) . ' AND islive=1');
		}
		return NULL;
	}

	public function get_users_select_html_by_city() {
		$result = '<option value="">请选择人员</option>';
		$users = $this->_get_users_by_city();
		if ($users !== NULL) {
			foreach ($users as $user) {
				$result .= '<option value="' . $user->uid . '">'
						. $user->realname . '(' . $user->username
						. ')</option>';
			}
		}
		return $result;
	}

	public function get_add_company_html() {
		if ($this->getHas_hr_permission()) {
			$buf = file_get_contents(TEMPLATE_PATH . 'hr/company_add.tpl');
			return str_replace(
					array('[LEFT]', '[TOP]', '[VCODE]', '[BASE_URL]'),
					array($this->get_left_html(), $this->get_top_html(),
							$this->get_vcode(), BASE_URL), $buf);
		} else {
			return User::no_permission();
		}
	}

	public function get_edit_company_html() {
		if ($this->getHas_hr_permission()) {
			$buf = file_get_contents(TEMPLATE_PATH . 'hr/company_edit.tpl');
			return str_replace(
					array('[LEFT]', '[TOP]', '[VCODE]', '[NAME]', '[CITYID]',
							'[BASE_URL]'),
					array($this->get_left_html(), $this->get_top_html(),
							$this->get_vcode(), $this->companyname,
							$this->city_id, BASE_URL), $buf);
		} else {
			return User::no_permission();
		}
	}
}