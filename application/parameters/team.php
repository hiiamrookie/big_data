<?php
class Team extends User {
	private static $instance = NULL;
	private $team_id;
	private $city;
	private $teamname;
	private $dep;
	private $errors = array();

	private $has_hr_permission = FALSE;

	/**
	 * @return the $has_hr_permission
	 */
	public function getHas_hr_permission() {
		return $this->has_hr_permission;
	}

	public function __construct($team_id = NULL, $fields = array()) {
		parent::__construct();
		if (in_array($this->getUsername(),
				$GLOBALS['manager_setup_permission'], TRUE)) {
			$this->has_hr_permission = TRUE;
		}
		if ($team_id !== NULL) {
			if (!is_int($team_id)) {
				$team_id = intval($team_id);
			}
			if (self::validate_id($team_id)) {
				$team = $this->db
						->get_row(
								'SELECT a.dep,a.teamname,b.cityid FROM hr_team a, hr_department b  WHERE a.dep=b.id AND a.id='
										. intval($team_id));
				if ($team !== NULL) {
					$this->team_id = intval($team_id);
					$this->dep = intval($team->dep);
					$this->city = intval($team->cityid);
					$this->teamname = $team->teamname;
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
	 * @return the $team_id
	 */
	public function getTeam_id() {
		return $this->team_id;
	}

	/**
	 * @return the $teamname
	 */
	public function getTeamname() {
		return $this->teamname;
	}

	public static function getInstance($force_flush = FALSE) {
		if (self::$instance === NULL || $force_flush) {
			$team_cache_filename = md5('team_cache_filename');
			$team_cache = new FileCache(CACHE_TIME, CACHE_PATH);
			$team_cache_file = $team_cache->get($team_cache_filename);
			if ($team_cache_file === FALSE || $force_flush) {
				//读取数据库
				$dao = new Dao_Impl();
				$teams = $dao->db
						->get_results(
								'SELECT id,dep,teamname FROM hr_team WHERE islive=1');
				if ($teams !== NULL) {
					$datas = array();
					foreach ($teams as $team) {
						$datas['team'][$team->id] = array('dep' => $team->dep,
								'teamname' => $team->teamname);
						$datas['dep'][$team->dep][] = $team->id;
					}
					$team_cache->set($team_cache_filename, $datas);
				}
			}
			self::$instance = $team_cache->get($team_cache_filename);
		}
		return self::$instance;
	}

	public static function get_team_by_dep($dep, $force_flush = FALSE) {
		self::getInstance($force_flush);
		$instance = self::$instance;
		$results = array();
		$instance = $instance['team'];
		if ($instance !== NULL) {
			foreach ($instance as $team_id => $team_value) {
				if (intval($dep) === intval($team_value['dep'])) {
					$results[] = array('team_id' => $team_id,
							'team_name' => $team_value['teamname']);
				}
			}
		}
		return $results;
	}

	public function del_team() {
		if ($this->team_id !== NULL) {
			$update_result = $this->db
					->query(
							'UPDATE hr_team SET islive=0 WHERE id='
									. $this->team_id);
			if ($update_result > 0) {
				self::getInstance(TRUE);
				return array('status' => 'success', 'message' => '删除团队成功');
			}
			return array('status' => 'success', 'message' => '删除团队失败');
		}
		return array('status' => 'error', 'message' => '团队选择有误');
	}

	public static function get_team_select_html_by_dep($dep,
			$force_flush = FALSE, $team = NULL) {
		$datas = self::get_team_by_dep($dep, $force_flush);
		$result = '<option value="">请选择团队</option>';

		if ($datas !== NULL) {
			foreach ($datas as $data) {
				$result .= '<option value="' . $data['team_id'] . '" '
						. ($team !== NULL
								&& intval($data['team_id']) === intval($team) ? 'selected="selected"'
								: '') . '>' . $data['team_name'] . '</option>';
			}
		}
		return $result;
	}

	public function get_city_select_html() {
		return City::get_city_select_html(FALSE, $this->city);
	}

	public function get_dep_select_html() {
		return Dep::get_dep_select_html_by_city($this->city, FALSE, $this->dep);
	}

	private function validate_form_value($action) {
		$errors = array();
		if (in_array($action, array('add_team', 'update_team'), TRUE)) {
			if (!in_array($this->getUsername(),
					$GLOBALS['manager_setup_permission'], TRUE)) {
				$errors[] = '无权限操作';
			} else {
				if ($action === 'update_team') {
					$team_id = intval($this->team_id);
					if (!self::validate_id($team_id)) {
						$errors[] = '团队选择有误';
					}
				}

				$dep = intval($this->dep);
				if (!self::validate_id($dep)) {
					$errors[] = '部门选择有误';
				}

				$teamname = $this->teamname;
				if (!self::validate_field_not_empty($teamname)
						|| !self::validate_field_not_null($teamname)) {
					$errors[] = '团队名称不能为空';
				} else if (!self::validate_field_max_length($teamname, 200)) {
					$errors[] = '团队名称长度最多200个字符';
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

	public function add_team() {
		if ($this->validate_form_value('add_team')) {
			$query = Sql_Util::get_insert('hr_team',
					array('dep' => intval($this->dep),
							'teamname' => $this->teamname, 'time' => time()));
			if ($query['status'] === 'success') {
				$insert_result = $this->db->query($query['sql']);
				if ($insert_result === FALSE || $insert_result === 0) {
					//插入失败
					return array('status' => 'error', 'message' => '新建团队信息出错');
				}
				self::getInstance(TRUE);
				return array('status' => 'success', 'message' => '新建团队信息成功');
			} else {
				//SQL错误
				return array('status' => 'error', 'message' => '系统内部错误');
			}
		}
		return array('status' => 'error', 'message' => $this->errors);
	}

	public function update_team() {
		if ($this->validate_form_value('update_team')) {
			$query = Sql_Util::get_update('hr_team',
					array('dep' => intval($this->dep),
							'teamname' => $this->teamname),
					array('id' => array('=', intval($this->team_id))), 'AND');
			if ($query['status'] === 'success') {
				$update_result = $this->db->query($query['sql']);
				if ($update_result === FALSE || $update_result === 0) {
					//插入失败
					return array('status' => 'error', 'message' => '更新团队信息出错');
				}
				self::getInstance(TRUE);
				return array('status' => 'success', 'message' => '更新团队信息成功');
			} else {
				//SQL错误
				return array('status' => 'error', 'message' => '系统内部错误');
			}
		}
		return array('status' => 'error', 'message' => $this->errors);
	}

	private function _get_users_by_team() {
		if (intval($this->team_id) > 0) {
			return $this->db
					->get_results(
							'SELECT uid,realname,username FROM users WHERE team='
									. intval($this->team_id) . ' AND islive=1');
		}
		return NULL;
	}

	public function get_users_select_html_by_team($uid = NULL) {
		$result = '<option value="">请选择人员</option>';
		$users = $this->_get_users_by_team();
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

	public function get_add_team_html() {
		if ($this->getHas_hr_permission()) {
			$buf = file_get_contents(TEMPLATE_PATH . 'hr/team_add.tpl');
			return str_replace(
					array('[LEFT]', '[TOP]', '[VCODE]', '[CITYS]', '[BASE_URL]'),
					array($this->get_left_html(), $this->get_top_html(),
							$this->get_vcode(), $this->get_city_select_html(),
							BASE_URL), $buf);
		} else {
			return User::no_permission();
		}
	}

	public function get_edit_team_html() {
		if ($this->getHas_hr_permission()) {
			$buf = file_get_contents(TEMPLATE_PATH . 'hr/team_edit.tpl');
			return str_replace(
					array('[LEFT]', '[TOP]', '[VCODE]', '[CITYS]', '[DEPS]',
							'[NAME]', '[TEAMID]', '[BASE_URL]'),
					array($this->get_left_html(), $this->get_top_html(),
							$this->get_vcode(), $this->get_city_select_html(),
							$this->get_dep_select_html(), $this->teamname,
							$this->team_id, BASE_URL), $buf);
		} else {
			return User::no_permission();
		}
	}
}
