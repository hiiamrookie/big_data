<?php
class Other_User extends User {
	private $errors = array();
	private $other_user_id;
	private $other_user_permission = array();

	private $other_username;
	private $other_realname;
	private $sex;
	private $mobile;
	private $email;
	private $city;
	private $dep;
	private $team;
	private $allpermissions;

	private $has_hr_permission = FALSE;

	/**
	 * @return the $has_hr_permission
	 */
	public function getHas_hr_permission() {
		return $this->has_hr_permission;
	}

	public function getSex1() {
		return intval($this->sex) === 1 ? 'checked="checked"' : '';
	}

	public function getSex2() {
		return intval($this->sex) === 2 ? 'checked="checked"' : '';
	}

	/**
	 * @param field_type $other_user_id
	 */
	public function setOther_user_id($other_user_id) {
		$this->other_user_id = $other_user_id;
	}

	public function __construct($other_user_id = NULL, $fields = array()) {
		parent::__construct();
		if (in_array($this->getUsername(),
				$GLOBALS['manager_setup_permission'], TRUE)) {
			$this->has_hr_permission = TRUE;
		}
		if ($other_user_id !== NULL) {
			if (!is_int($other_user_id)) {
				$other_user_id = intval($other_user_id);
				if (self::validate_id($other_user_id)) {
					$this->other_user_id = $other_user_id;

					//获得用户相应权限
					$query = Sql_Util::get_query('users',
							array('username', 'realname', 'email', 'mobile',
									'permissions', 'city', 'dep', 'team',
									'sex'),
							array('uid' => array('=', $this->other_user_id)),
							'AND');
					if ($query['status'] === 'success') {
						$row = $this->db->get_row($query['sql']);
						if ($row !== NULL) {
							$permissions = $row->permissions;
							if ($permissions !== '') {
								$this->other_user_permission = explode('^',
										$permissions);
							}

							$this->other_username = $row->username;
							$this->other_realname = $row->realname;
							$this->email = $row->email;
							$this->mobile = $row->mobile;
							$this->city = $row->city;
							$this->dep = $row->dep;
							$this->team = $row->team;
							$this->sex = $row->sex;

						} else {
							$this->other_user_id = NULL;
						}
					}
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
	 * @return the $other_user_id
	 */
	public function getOther_user_id() {
		return $this->other_user_id;
	}

	/**
	 * @return the $other_username
	 */
	public function getOther_username() {
		return $this->other_username;
	}

	/**
	 * @return the $other_realname
	 */
	public function getOther_realname() {
		return $this->other_realname;
	}

	/**
	 * @return the $mobile
	 */
	public function getMobile() {
		return $this->mobile;
	}

	/**
	 * @return the $email
	 */
	public function getEmail() {
		return $this->email;
	}

	private function validate_form_value($action) {
		if (in_array($action,
				array('cancel_user', 'change_pwd', 'add_user', 'update_user'),
				TRUE)) {
			if (!in_array($this->getUsername(),
					$GLOBALS['manager_setup_permission'], TRUE)) {
				$errors[] = '无权限操作';
			} else {
				if (in_array($action,
						array('cancel_user', 'change_pwd', 'update_user'),
						TRUE)) {
					//用户ID
					$other_user_id = intval($this->other_user_id);
					if (!self::validate_id($other_user_id)) {
						$errors[] = '用户选择有误';
					}
				}

				if (in_array($action, array('add_user', 'update_user'), TRUE)) {
					//用户名
					$username = $this->other_username;
					if (!self::validate_field_not_empty($username)
							|| !self::validate_field_not_null($username)) {
						$errors[] = '用户名不能为空';
					} else if (!self::validate_field_max_length($username, 100)) {
						$errors[] = '用户名长度最多100个字符';
					} else {
						$query = 'SELECT COUNT(*) FROM users WHERE username="'
								. $username . '" AND islive=1';
						if ($action === 'update_user') {
							$query .= ' AND uid!='
									. intval($this->other_user_id);
						}
						$count = $this->db->get_var($query);
						if (intval($count) > 0) {
							$errors[] = '用户名已被使用';
						}
					}

					//真实姓名
					$realname = $this->other_realname;
					if (!self::validate_field_not_empty($realname)
							|| !self::validate_field_not_null($realname)) {
						$errors[] = '真实姓名不能为空';
					} else if (!self::validate_field_max_length($realname, 100)) {
						$errors[] = '真实姓名长度最多100个字符';
					}

					//邮件地址
					$email = $this->email;
					if (!self::validate_field_not_empty($email)
							|| !self::validate_field_not_null($email)) {
						$errors[] = '电子邮件地址不能为空';
					} else if (!self::validate_field_max_length($email, 200)) {
						$errors[] = '电子邮件地址长度最多200个字符';
					} else if (!Validate_Util::my_is_email($email, FALSE)) {
						$errors[] = '电子邮件地址有误';
					}

					//性别
					$sex = intval($this->sex);
					if (!in_array($sex, array(1, 2), TRUE)) {
						$errors[] = '性别选择有误';
					}

					//手机
					$mobile = $this->mobile;
					if (!self::validate_field_not_empty($mobile)
							|| !self::validate_field_not_null($mobile)) {
						$errors[] = '手机号码不能为空';
					} else if (!Validate_Util::my_is_numeric($mobile)) {
						$errors[] = '手机号码输入有误';
					}

					//分公司
					$city = intval($this->city);
					if (!self::validate_id($city)) {
						$errors[] = '分公司选择有误';
					}

					//部门
					$dep = intval($this->dep);
					if (!self::validate_id($dep)) {
						$errors[] = '部门选择有误';
					}

					//团队
					$team = $this->team;
					if (self::validate_field_not_empty($team)
							&& !self::validate_id(intval($team))) {
						$errors[] = '团队选择有误';
					}
					//权限
					$permissions = $this->allpermissions;
					if (self::validate_field_not_empty($team)
							&& !self::validate_field_max_length(
									implode('^', $permissions), 1000)) {
						$errors[] = '权限数量选择有误';
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

	public function cancel_user() {
		if ($this->validate_form_value('cancel_user')) {
			$update_result = $this->db
					->query(
							'UPDATE users SET islive=-1 WHERE uid='
									. intval($this->other_user_id));
			if ($update_result > 0) {
				//成功
				return array('status' => 'success', 'message' => '注销用户成功');
			}
			return array('status' => 'error', 'message' => '注销用户失败');
		}
		return array('status' => 'error', 'message' => $this->errors);
	}

	public function change_pwd() {
		if ($this->validate_form_value('change_pwd')) {
			$update_result = $this->db
					->query(
							'UPDATE users SET password="'
									. String_Util::my_md5('11111111', 1)
									. '" WHERE uid='
									. intval($this->other_user_id));
			if ($update_result > 0) {
				//成功
				return array('status' => 'success', 'message' => '重置用户密码成功');
			}
			return array('status' => 'error', 'message' => '重置用户密码失败');
		}
		return array('status' => 'error', 'message' => $this->errors);
	}

	public function get_permission_list_html() {
		$permissions = Permission::getInstance();
		$modules = Module::getInstance();
		$dep_permissions = Permission_Dep::getInstance();
		$deps = Dep::getInstance();

		$result = '';
		//权限
		foreach ($permissions as $module_id => $permission_value) {
			if ($modules[$module_id] !== NULL) {
				$result .= '<table class="sbd1" cellpadding="0" cellspacing="0"><tr><td colspan="6"><b>'
						. $modules[$module_id]['modulename'] . '</b></td></tr>';
				foreach ($permission_value as $key => $value) {
					if ($key === 0) {
						$result .= '<tr>';
					}
					if ($key % 6 === 0 && $key !== 0) {
						$result .= '</tr><tr>';
					}
					$result .= '<td><input type="checkbox" class="checkbox" name="permissions[]" value="sys'
							. $value['permission_id'] . '" '
							. (in_array('sys' . $value['permission_id'],
									$this->other_user_permission, TRUE) ? 'checked="checked"'
									: '') . '/> ' . $value['permission_name']
							. ' &nbsp;&nbsp;</td>';
				}
				$result .= '</table><br>';
			}
		}

		//部门权限
		$result .= '<table class="sbd1" cellpadding="0" cellspacing="0"><tr><td colspan="2"><b>部门权限</b></td></tr>';
		foreach ($dep_permissions as $dep_id => $dep_permission_value) {
			$result .= '<tr><td><b>' . $deps[$dep_id][1] . $deps[$dep_id][0]
					. '</b></td><td>';
			foreach ($dep_permission_value as $key => $value) {
				$result .= '<input type="checkbox" class="checkbox" name="permissions[]" value="dep'
						. $value['permission_id'] . '" '
						. (in_array('dep' . $value['permission_id'],
								$this->other_user_permission, TRUE) ? 'checked="checked"'
								: '') . '/> ' . $value['permission_name']
						. ' &nbsp;&nbsp;';
			}
			$result .= '</td></tr>';
		}

		return $result;
	}

	public function add_user() {
		if ($this->validate_form_value('add_user')) {
			$query = Sql_Util::get_insert('users',
					array('username' => $this->other_username,
							'password' => String_Util::my_md5($this->mobile, 1),
							'realname' => $this->other_realname,
							'email' => $this->email,
							'sex' => intval($this->sex),
							'mobile' => $this->mobile,
							'city' => intval($this->city),
							'dep' => intval($this->dep),
							'team' => intval($this->team),
							'permissions' => self::validate_field_not_empty(
									$this->allpermissions) ? implode('^',
											$this->allpermissions) : ''));
			if ($query['status'] === 'success') {
				$insert_result = $this->db->query($query['sql']);
				if ($insert_result === FALSE || $insert_result === 0) {
					//插入失败
					return array('status' => 'error', 'message' => '新建用户信息出错');
				}
				return array('status' => 'success', 'message' => '新建用户信息成功');
			} else {
				//SQL错误
				return array('status' => 'error', 'message' => '系统内部错误');
			}
		}
		return array('status' => 'error', 'message' => $this->errors);
	}

	public function update_user() {
		if ($this->validate_form_value('update_user')) {
			$query = Sql_Util::get_update('users',
					array('username' => $this->other_username,
							'realname' => $this->other_realname,
							'email' => $this->email, 'sex' => $this->sex,
							'mobile' => $this->mobile,
							'city' => intval($this->city),
							'dep' => intval($this->dep),
							'team' => intval($this->team),
							'permissions' => implode('^', $this->allpermissions)),
					array('uid' => array('=', intval($this->other_user_id))),
					'AND');
			if ($query['status'] === 'success') {
				$update_result = $this->db->query($query['sql']);
				if ($update_result === FALSE || $update_result === 0) {
					//更新失败
					return array('status' => 'error', 'message' => '更新用户信息出错');
				}
				return array('status' => 'success', 'message' => '更新用户信息成功');
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

	public function get_dep_select_html() {
		return Dep::get_dep_select_html_by_city($this->city, FALSE, $this->dep);
	}

	public function get_team_select_html() {
		return Team::get_team_select_html_by_dep($this->dep, FALSE, $this->team);
	}

	public function get_add_user_html() {
		if ($this->getHas_hr_permission()) {
			$buf = file_get_contents(TEMPLATE_PATH . 'hr/user_add.tpl');
			return str_replace(
					array('[LEFT]', '[TOP]', '[VCODE]', '[CITYS]',
							'[PERMISSIONS]', '[BASE_URL]'),
					array($this->get_left_html(), $this->get_top_html(),
							$this->get_vcode(), $this->get_city_select_html(),
							$this->get_permission_list_html(), BASE_URL), $buf);
		} else {
			return User::no_permission();
		}
	}

	public function get_edit_user_html() {
		if ($this->getHas_hr_permission()) {
			$buf = file_get_contents(TEMPLATE_PATH . 'hr/user_edit.tpl');
			return str_replace(
					array('[LEFT]', '[TOP]', '[VCODE]', '[CITYS]',
							'[PERMISSIONS]', '[USERNAME]', '[REALNAME]',
							'[MOBILE]', '[EMAIL]', '[DEPS]', '[TEAMS]',
							'[SEX1]', '[SEX2]', '[USERID]', '[BASE_URL]'),
					array($this->get_left_html(), $this->get_top_html(),
							$this->get_vcode(), $this->get_city_select_html(),
							$this->get_permission_list_html(),
							$this->other_username, $this->other_realname,
							$this->mobile, $this->email,
							$this->get_dep_select_html(),
							$this->get_team_select_html(), $this->getSex1(),
							$this->getSex2(), $this->other_user_id, BASE_URL),
					$buf);
		} else {
			return User::no_permission();
		}
	}
}