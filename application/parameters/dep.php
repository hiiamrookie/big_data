<?php
class Dep extends User {
	private static $instance = NULL;
	private $dep_id;
	private $city;
	private $depname;
	private $issupport;
	private $errors = array();

	private $has_hr_permission = FALSE;

	/**
	 * @return the $has_hr_permission
	 */
	public function getHas_hr_permission() {
		return $this->has_hr_permission;
	}

	/**
	 * @return the $depname
	 */
	public function getDepname() {
		return $this->depname;
	}

	public function getS1() {
		return intval($this->issupport) === 0 ? 'checked="checked"' : '';
	}

	public function getS2() {
		return intval($this->issupport) === 1 ? 'checked="checked"' : '';
	}

	/**
	 * @return the $dep_id
	 */
	public function getDep_id() {
		return $this->dep_id;
	}

	public function __construct($dep_id = NULL, $fields = array()) {
		parent::__construct();
		if (in_array($this->getUsername(),
				$GLOBALS['manager_setup_permission'], TRUE)) {
			$this->has_hr_permission = TRUE;
		}
		if ($dep_id !== NULL) {
			if (!is_int($dep_id)) {
				$dep_id = intval($dep_id);
			}
			if (self::validate_id($dep_id)) {
				$dep = $this->db
						->get_row(
								'SELECT depname,cityid,issupport FROM hr_department WHERE id='
										. intval($dep_id));
				if ($dep !== NULL) {
					$this->dep_id = intval($dep_id);
					$this->city = intval($dep->cityid);
					$this->depname = $dep->depname;
					$this->issupport = intval($dep->issupport);
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
	 * @return the $city
	 */
	public function getCity() {
		return $this->city;
	}

	public function del_dep() {
		if ($this->dep_id !== NULL) {
			$update_result = $this->db
					->query(
							'UPDATE hr_department SET islive=0 WHERE id='
									. $this->dep_id);
			if ($update_result > 0) {
				self::getInstance(TRUE);
				return array('status' => 'success', 'message' => '删除部门成功');
			}
			return array('status' => 'success', 'message' => '删除部门失败');
		}
		return array('status' => 'error', 'message' => '部门选择有误');
	}

	public static function getInstance($force_flush = FALSE) {
		if (self::$instance === NULL || $force_flush) {
			$dep_cache_filename = md5('dep_cache_filename');
			$dep_cache = new FileCache(CACHE_TIME, CACHE_PATH);
			$dep_cache_file = $dep_cache->get($dep_cache_filename);

			if ($dep_cache_file === FALSE || $force_flush) {
				//读取数据库
				$dao = new Dao_Impl();
				$deps = $dao->db
						->get_results(
								'SELECT a.id,a.depname,a.cityid,a.issupport,b.companyname FROM hr_department a,hr_company b WHERE a.cityid=b.id AND a.islive=1 ORDER BY b.companyname');
				if ($deps !== NULL) {
					$datas = array();
					foreach ($deps as $dep) {
						$datas[$dep->id] = array($dep->depname,
								$dep->companyname, $dep->cityid,
								$dep->issupport);
					}
					$dep_cache->set($dep_cache_filename, $datas);
				}
			}

			self::$instance = $dep_cache->get($dep_cache_filename);
		}
		return self::$instance;
	}

	public static function get_dep_by_city($city, $force_flush = FALSE) {
		self::getInstance($force_flush);
		$instance = self::$instance;
		$results = array();
		if ($instance !== NULL) {
			foreach ($instance as $dep_id => $dep_value) {
				if (intval($city) === intval($dep_value[2])) {
					$results[] = array('dep_id' => $dep_id,
							'dep_name' => $dep_value[0]);
				}
			}
		}
		return $results;
	}

	public static function get_dep_select_html_by_city($city,
			$force_flush = FALSE, $dep = NULL) {
		$datas = self::get_dep_by_city($city, $force_flush);
		$result = '<option value="">请选择部门</option>';

		if ($datas !== NULL) {
			foreach ($datas as $data) {
				$result .= '<option value="' . $data['dep_id'] . '" '
						. ($dep !== NULL
								&& intval($data['dep_id']) === intval($dep) ? 'selected="selected"'
								: '') . '>' . $data['dep_name'] . '</option>';
			}
		}
		return $result;
	}

	public static function get_dep_select_html($force_flush = FALSE,
			$is_new = TRUE, $dep = NULL) {
		self::getInstance($force_flush);
		$instance = self::$instance;
		$result = $is_new ? '<option value="">请选择</option>' : '';

		if ($instance !== NULL) {
			foreach ($instance as $dep_id => $dep_value) {
				$result .= '<option value="' . $dep_id . '" '
						. ($dep !== NULL && intval($dep_id) === intval($dep) ? 'selected="selected"'
								: '') . '>' . $dep_value[0] . ' ('
						. $dep_value[1] . ')</option>';
			}
		}
		return $result;
	}

	public static function get_dep_checkbox_html($force_flush = FALSE,
			$has_selected = array()) {
		self::getInstance($force_flush);
		$instance = self::$instance;

		$result = '';
		$tmp_deps = array();
		foreach ($instance as $depid => $dep) {
			$tmp_deps[$dep[1]][] = array($depid, $dep[0]);
		}

		foreach ($tmp_deps as $city => $tmp_dep) {
			$result .= '<div>' . $city . ':';
			foreach ($tmp_dep as $td) {
				$result .= ' <input name="deps[]" type="checkbox" class="validate[minCheckbox[1]] checkbox" value="'
						. $td[0] . '" '
						. (in_array($td[0], $has_selected) ? 'checked="checked"'
								: '') . '/> ' . $td[1];
			}
			$result .= '</div>';
		}
		return $result;
	}

	private function validate_form_value($action) {
		$errors = array();
		if (in_array($action, array('add_dep', 'update_dep'), TRUE)) {
			if (!in_array($this->getUsername(),
					$GLOBALS['manager_setup_permission'], TRUE)) {
				$errors[] = '无权限操作';
			} else {
				if ($action === 'update_dep') {
					$dep_id = intval($this->dep_id);
					if (!self::validate_id($dep_id)) {
						$errors[] = '部门选择有误';
					}
				}

				$city = intval($this->city);
				if (!self::validate_id($city)) {
					$errors[] = '公司选择有误';
				}

				$depname = $this->depname;
				if (!self::validate_field_not_empty($depname)
						|| !self::validate_field_not_null($depname)) {
					$errors[] = '部门名称不能为空';
				} else if (!self::validate_field_max_length($depname, 50)) {
					$errors[] = '部门名称长度最多50个字符';
				}

				$issupport = $this->issupport;
				if (!in_array(intval($issupport), array(0, 1), TRUE)) {
					$errors[] = '是否支持部门选择有误';
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

	public function add_dep() {
		if ($this->validate_form_value('add_dep')) {
			$query = Sql_Util::get_insert('hr_department',
					array('depname' => $this->depname,
							'cityid' => intval($this->city),
							'issupport' => intval($this->issupport),
							'time' => $_SERVER['REQUEST_TIME']));
			if ($query['status'] === 'success') {
				$insert_result = $this->db->query($query['sql']);
				if ($insert_result === FALSE || $insert_result === 0) {
					//插入失败
					return array('status' => 'error', 'message' => '新建部门信息出错');
				}
				self::getInstance(TRUE);
				return array('status' => 'success', 'message' => '新建部门信息成功');
			} else {
				//SQL错误
				return array('status' => 'error', 'message' => '系统内部错误');
			}
		}
		return array('status' => 'error', 'message' => $this->errors);
	}

	public function update_dep() {
		if ($this->validate_form_value('update_dep')) {
			$query = Sql_Util::get_update('hr_department',
					array('depname' => $this->depname,
							'cityid' => intval($this->city),
							'issupport' => intval($this->issupport)),
					array('id' => array('=', intval($this->dep_id))), 'AND');
			if ($query['status'] === 'success') {
				$update_result = $this->db->query($query['sql']);
				if ($update_result === FALSE || $update_result === 0) {
					//更新失败
					return array('status' => 'error', 'message' => '更新部门信息出错');
				}
				self::getInstance(TRUE);
				return array('status' => 'success', 'message' => '更新部门信息成功');
			} else {
				//SQL错误
				return array('status' => 'error', 'message' => '系统内部错误');
			}
		}
		return array('status' => 'error', 'message' => $this->errors);
	}

	public function get_city_select_html() {
		return City::get_city_select_html(FALSE, $this->city);
	}

	public static function get_support_dep_checkbox_html($force_flush = FALSE,
			$has_selected = array(), $belong_dep = NULL) {
		self::getInstance($force_flush);
		$instance = self::$instance;

		$result = '';
		$tmp_deps = array();
		foreach ($instance as $depid => $dep) {
			if (intval($dep[3]) === 1) {
				if ($belong_dep === NULL
						|| $belong_dep !== NULL
								&& intval($depid) !== intval($belong_dep)) {
					$tmp_deps[$dep[1]][] = array($depid, $dep[0]);
				}
			}
		}

		foreach ($tmp_deps as $city => $tmp_dep) {
			$result .= '<div>' . $city . ':';
			foreach ($tmp_dep as $td) {
				if (empty($has_selected)) {
					//去掉SMC
					$result .= ' <input name="support[]" type="checkbox" class="checkbox" value="'
							. $td[0] . '" '
							. (intval($td[0]) === 7 ? 'disabled="disabled"' : '')
							. '/> ' . $td[1];
				} else {
					$result .= ' <input name="support[]" type="checkbox" class="checkbox" value="'
							. $td[0] . '" '
							. (in_array($td[0], $has_selected) ? 'checked="checked"'
									: '') . '/> ' . $td[1];
				}
			}
			$result .= '</div>';
		}
		return $result;
	}

	private function _get_users_by_dep() {
		if (intval($this->dep_id) > 0) {
			return $this->db
					->get_results(
							'SELECT uid,realname,username FROM users WHERE dep='
									. intval($this->dep_id) . ' AND islive=1');
		}
		return NULL;
	}

	public function get_users_select_html_by_dep($uid = NULL) {
		$result = '<option value="">请选择人员</option>';
		$users = $this->_get_users_by_dep();
		if ($users !== NULL) {
			foreach ($users as $user) {
				$result .= '<option value="' . $user->uid . '" '
						. ($uid !== NULL && intval($uid) === intval($user->uid) ? 'selected="selected"'
								: '') . '>' . $user->realname . '('
						. $user->username . ')</option>';
			}
		}
		return $result;
	}

	public function get_add_department_html() {
		if ($this->getHas_hr_permission()) {
			$buf = file_get_contents(TEMPLATE_PATH . 'hr/department_add.tpl');
			return str_replace(
					array('[LEFT]', '[TOP]', '[VCODE]', '[CITYS]', '[BASE_URL]'),
					array($this->get_left_html(), $this->get_top_html(),
							$this->get_vcode(), $this->get_city_select_html(),
							BASE_URL), $buf);
		} else {
			return User::no_permission();
		}
	}

	public function get_edit_department_html() {
		if ($this->getHas_hr_permission()) {
			$buf = file_get_contents(TEMPLATE_PATH . 'hr/department_edit.tpl');
			return str_replace(
					array('[LEFT]', '[TOP]', '[VCODE]', '[CITYS]', '[NAME]',
							'[S1]', '[S2]', '[DEPID]', '[BASE_URL]'),
					array($this->get_left_html(), $this->get_top_html(),
							$this->get_vcode(), $this->get_city_select_html(),
							$this->depname, $this->getS1(), $this->getS2(),
							$this->dep_id, BASE_URL), $buf);
		} else {
			return User::no_permission();
		}
	}
}
