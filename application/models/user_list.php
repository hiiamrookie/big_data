<?php
class User_List extends User {
	private $page;
	private $city;
	private $dep;
	private $team;
	private $search;

	private $all_count;
	private $page_count;
	private $prev;
	private $next;
	private $city_array;
	private $dep_array;
	private $team_array;

	private $users = array();

	const LIMIT = 50;

	private $has_hr_permission = FALSE;

	/**
	 * @return the $has_hr_permission
	 */
	public function getHas_hr_permission() {
		return $this->has_hr_permission;
	}

	/**
	 * @return the $prev
	 */
	public function getPrev() {
		if (intval($this->page) === 1) {
			$this->prev = '';
		} else {
			$this->prev = self::_get_pagination(intval($this->city),
					intval($this->dep), intval($this->team),
					intval($this->page) - 1, $this->search, TRUE);
		}
		return $this->prev;
	}

	/**
	 * @return the $next
	 */
	public function getNext() {
		if (intval($this->page) >= intval($this->page_count)) {
			$this->next = '';
		} else {
			$this->next = self::_get_pagination(intval($this->city),
					intval($this->dep), intval($this->team),
					intval($this->page) + 1, $this->search, FALSE);
		}
		return $this->next;
	}

	/**
	 * @return the $all_count
	 */
	public function getAll_count() {
		return $this->all_count;
	}

	public function __construct($fields) {
		parent::__construct();
		if (in_array($this->getUsername(),
				$GLOBALS['manager_setup_permission'], TRUE)) {
			$this->has_hr_permission = TRUE;
		}
		if (!empty($fields)) {
			foreach ($this as $key => $value) {
				if ($fields[$key] !== NULL
						&& !in_array($key, array('has_hr_permission'), TRUE)) {
					$this->$key = $fields[$key];
				}
			}
			$this->_get_user_list_datas();
			$this->city_array = City::getInstance();
			$this->dep_array = Dep::getInstance();
			$this->team_array = Team::getInstance();
		}
	}

	private static function _get_pagination($city, $dep, $team, $page, $search,
			$is_prev) {
		return '<a href="' . BASE_URL . 'hr/?o=userlist&city=' . $city
				. '&dep=' . $dep . '&team=' . $team . '&page=' . $page
				. '&search=' . $search . '">' . ($is_prev ? '上一页' : '下一页')
				. '</a>';
	}

	private function _get_user_list_datas() {
		$where_sql = array();
		$where = '';
		if (self::validate_field_not_null($this->search)
				&& self::validate_field_not_empty($this->search)) {
			$where_sql[] = ' username LIKE "%' . $this->search
					. '%" OR realname LIKE "%' . $this->search . '%" ';
		}
		if (intval($this->city) > 0) {
			$where_sql[] = ' city=' . intval($this->city) . ' ';
		}
		if (intval($this->dep) > 0) {
			$where_sql[] = ' dep=' . intval($this->dep) . ' ';
		}
		if (intval($this->team) > 0) {
			$where_sql[] = ' team=' . intval($this->team) . ' ';
		}
		if (!empty($where_sql)) {
			$where = ' WHERE ' . implode('AND', $where_sql);
		}
		$query = 'SELECT COUNT(*) FROM users' . $where;
		$this->all_count = intval($this->db->get_var($query));

		$this->page_count = ceil($this->all_count / self::LIMIT);

		$start = self::LIMIT * intval($this->page) - self::LIMIT;
		if ($start < 0) {
			$start = 0;
		}

		$query = 'SELECT * FROM users ' . $where
				. ' ORDER BY islive DESC, uid DESC LIMIT ' . $start . ','
				. self::LIMIT;
		$users = $this->db->get_results($query);

		$results = array();
		if ($users !== NULL) {
			foreach ($users as $user) {
				$results[] = array('uid' => $user->uid,
						'username' => $user->username,
						'realname' => $user->realname, 'email' => $user->email,
						'sex' => $user->sex, 'mobile' => $user->mobile,
						'city' => $user->city, 'dep' => $user->dep,
						'team' => $user->team, 'islive' => $user->islive);
			}
		}
		$this->users = $results;
		unset($results);
	}

	private static function _get_sex($sex) {
		return $sex === 2 ? '女' : '男';
	}

	private static function _get_islive($islive) {
		return $islive === -1 ? '<font color="#663333"><b>离职</b></font>' : '';
	}

	private function _get_depinfo($city, $dep, $team) {
		$citys = $this->city_array;
		$deps = $this->dep_array;
		$teams = $this->team_array;
		return $citys[$city] . ' ' . $deps[$dep][0] . ' '
				. $teams['team'][$team]['teamname'];
	}

	private static function _get_actions($uid) {
		return '<a href="' . BASE_URL . 'hr/?o=edituser&uid=' . $uid
				. '">编辑</a> &nbsp;&nbsp;<a href="javascript:changepwd(\''
				. $uid
				. '\');">重置密码</a> &nbsp;&nbsp;<a href="javascript:canceluser(\''
				. $uid . '\');">注销</a>';
	}

	private function _get_list_html() {
		$result = '';
		$users = $this->users;
		if (!empty($users)) {
			foreach ($users as $key => $user) {
				$result .= '<tr><td>'
						. (($this->page - 1) * self::LIMIT + $key + 1)
						. '</td><td>'
						. $this
								->_get_depinfo($user['city'], $user['dep'],
										$user['team']) . '</td><td>'
						. $user['username'] . '</td><td>' . $user['realname']
						. '</td><td>' . self::_get_sex(intval($user['sex']))
						. '</td><td>' . $user['email'] . '</td><td>'
						. $user['mobile'] . '</td><td>'
						. self::_get_islive(intval($user['islive']))
						. '</td><td>'
						. self::_get_actions(intval($user['uid']))
						. '</td></tr>';
			}
		}
		unset($users);
		return $result;
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

	private function _get_users() {
		return $this->db
				->get_results(
						'SELECT uid,realname,username FROM users WHERE islive=1');
	}

	public function get_users_select_html() {
		$result = '<option value="">请选择人员</option>';
		$users = $this->_get_users();
		if ($users !== NULL) {
			foreach ($users as $user) {
				$result .= '<option value="' . $user->uid . '">'
						. $user->realname . '(' . $user->username
						. ')</option>';
			}
		}
		return $result;
	}

	public function get_user_list_html() {
		if ($this->getHas_hr_permission()) {
			$buf = file_get_contents(TEMPLATE_PATH . 'hr/user_list.tpl');
			return str_replace(
					array('[LEFT]', '[TOP]', '[VCODE]', '[USERLIST]', '[PREV]',
							'[NEXT]', '[ALLUSERCOUNT]', '[CITYS]', '[DEPS]',
							'[TEAMS]', '[BASE_URL]'),
					array($this->get_left_html(), $this->get_top_html(),
							$this->get_vcode(), $this->_get_list_html(),
							$this->getPrev(), $this->getNext(),
							$this->all_count, $this->get_city_select_html(),
							$this->get_dep_select_html(),
							$this->get_team_select_html(), BASE_URL), $buf);
		} else {
			return User::no_permission();
		}
	}
}