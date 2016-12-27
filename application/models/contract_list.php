<?php
class Contract_List extends User {
	private $page;
	private $starttime;
	private $endtime;
	private $search;
	private $city;
	private $dep;
	private $team;

	private $all_count;
	private $page_count;

	private $is_check = FALSE;
	private $is_manager = FALSE;

	private $contracts = array();
	private $process_array = array();

	const LIMIT = 50;

	private $cids = array();
	private $userlist;
	private $errors = array();
	/**
	 * @return the $page
	 */
	public function getPage() {
		return $this->page;
	}

	/**
	 * @return the $starttime
	 */
	public function getStarttime() {
		return $this->starttime;
	}

	/**
	 * @return the $endtime
	 */
	public function getEndtime() {
		return $this->endtime;
	}

	/**
	 * @return the $search
	 */
	public function getSearch() {
		return $this->search;
	}

	/**
	 * @return the $all_count
	 */
	public function getAll_count() {
		return $this->all_count;
	}

	public function __construct($fields, $is_check = FALSE, $is_manager = FALSE) {
		parent::__construct();
		if (!empty($fields)) {
			foreach ($this as $key => $value) {
				if ($fields[$key] !== NULL
						&& !in_array($key, array('is_check', 'is_manager'),
								TRUE)) {
					$this->$key = $fields[$key];
				}
			}
			$this->_get_contract_list_datas($is_check, $is_manager);
			$this->process_array = Process::getInstance();
			$this->is_check = $is_check;
			$this->is_manager = $is_manager;
		}
	}

	private function _get_contract_list_datas($is_check = FALSE,
			$is_manager = FALSE) {
		$has_permission = $this->getHas_check_contract_permission();
		$has_manager_permission = $this->getHas_manager_contract_permission();

		$city = $this->city;
		$dep = $this->dep;
		$team = $this->team;
		if (intval($city) === 0 && intval($dep) === 0 && intval($team) === 0
				&& !$has_permission) {
			$city = $this->getBelong_city();
			$dep = $this->getBelong_dep();
			$team = $this->getBelong_team();
		}

		if ($is_check && !$has_permission
				&& !$this
						->get_relation_contract_permission(intval($city),
								intval($dep), intval($team))) {
			$this->all_count = 0;
			$this->page = 1;
			$this->page_count = 0;
			return;
		} else if ($is_manager && !$has_manager_permission
				&& !$this
						->get_zjl_contract_permission(intval($city),
								intval($dep))) {
			$this->all_count = 0;
			$this->page = 1;
			$this->page_count = 0;
			return;
		}

		$where_sql = array();
		if (!$is_check && !$is_manager) {
			$where_sql[] = ' (contactperson="' . $this->getUid()
					. '" OR view LIKE "%(' . $this->getUsername() . ')%")';
		}

		if (strtotime($this->starttime) !== FALSE) {
			$where_sql[] = ' time>=' . strtotime($this->starttime) . ' ';
		}

		if (strtotime($this->endtime) !== FALSE) {
			$where_sql[] = ' time<' . (strtotime($this->endtime) + 86400) . ' ';
		}

		if ($is_check || $is_manager) {
			$city = $this->city;
			$dep = $this->dep;
			$team = $this->team;
			if (intval($city) === 0 && intval($dep) === 0
					&& intval($team) === 0
					&& !in_array('sys44', $this->getPermissions(), TRUE)) {
				$city = $this->getBelong_city();
				$dep = $this->getBelong_dep();
				$team = $this->getBelong_team();
			}

			if (intval($city) !== 0 && intval($dep) === 0
					&& intval($team) === 0) {
				$where_sql[] = ' city=' . intval($city) . ' ';
			} else if (intval($city) !== 0 && intval($dep) !== 0
					&& intval($team) === 0) {
				$where_sql[] = ' dep=' . intval($dep) . ' ';
			} else if (intval($city) !== 0 && intval($dep) !== 0
					&& intval($team) !== 0) {
				$where_sql[] = ' dep=' . intval($dep) . ' ';
				$where_sql[] = ' team=' . intval($team) . ' ';
			}
		}

		if (self::validate_field_not_null($this->search)
				&& self::validate_field_not_empty($this->search)) {
			if (!$is_check && !$is_manager) {
				$where_sql[] = ' (cid LIKE "%' . $this->search
						. '%" OR contractname LIKE "%' . $this->search
						. '%" OR cusname LIKE "%' . $this->search . '%") ';
			} else if ($is_check && !$is_manager) {
				$where_sql[] = ' (cid LIKE "%' . $this->search
						. '%" OR fmkcid LIKE "%' . $this->search
						. '%" OR contractcontent LIKE "%' . $this->search
						. '%" OR cusname LIKE "%' . $this->search . '%") ';
			} else if (!$is_check && $is_manager) {
				$where_sql[] = ' (cid LIKE "%' . $this->search
						. '%" OR contractname LIKE "%' . $this->search . '%") ';
			}
		}

		$query = 'SELECT COUNT(*) FROM contract_cus WHERE 1=1 ';
		if (!empty($where_sql)) {
			$query .= ' AND ' . implode('AND', $where_sql);
		}

		$this->all_count = intval($this->db->get_var($query));
		$this->page_count = ceil($this->all_count / self::LIMIT);
		$start = self::LIMIT * intval($this->page) - self::LIMIT;
		if ($start < 0) {
			$start = 0;
		}

		$results = array();
		$sql = 'SELECT *,FROM_UNIXTIME(time) AS tt FROM contract_cus WHERE 1=1 ';
		if (!empty($where_sql)) {
			$sql .= ' AND ' . implode('AND', $where_sql);
		}
		$sql .= ' ORDER BY time DESC LIMIT ' . $start . ',' . self::LIMIT;
		$contracts = $this->db->get_results($sql);
		if ($contracts !== NULL) {
			foreach ($contracts as $contract) {
				$results[] = array(
						'contractstatus' => $contract->contractstatus,
						'cid' => $contract->cid, 'tt' => $contract->tt,
						'time' => $contract->time, 'type' => $contract->type,
						'fmkcid' => $contract->fmkcid,
						'contractname' => $contract->contractname,
						'cusname' => $contract->cusname,
						'contractamount' => $contract->contractamount,
						'isok' => $contract->isok, 'step' => $contract->step,
						'oktime' => $contract->oktime,
						'pcid' => $contract->pcid,
						'contractcontent' => $contract->contractcontent,
						'view' => $contract->view);
			}
		}
		$this->contracts = $results;
		unset($results);
	}

	public function get_contract_list_html() {
		$has_permission = $this->getHas_check_contract_permission();
		$has_manager_permission = $this->getHas_manager_contract_permission();

		$city = $this->city;
		$dep = $this->dep;
		$team = $this->team;
		if (intval($city) === 0 && intval($dep) === 0 && intval($team) === 0
				&& !$has_permission) {
			$city = $this->getBelong_city();
			$dep = $this->getBelong_dep();
			$team = $this->getBelong_team();
		}

		if ($this->is_check && !$has_permission
				&& !$this
						->get_relation_contract_permission(intval($city),
								intval($dep), intval($team))
				|| $this->is_manager && !$has_manager_permission
						&& !$this
								->get_zjl_contract_permission(intval($city),
										intval($dep))) {
			return '<td colspan="10"><font color="#FF0000"><b>您当前没有权限查看 ！</b></font></td>';
		}
		$s = '';
		$contracts = $this->contracts;
		if (!empty($contracts)) {
			foreach ($contracts as $key => $contract) {
				if (!($this->is_check) && !($this->is_manager)) {
					$s .= '<tr><td>'
							. self::_get_contract_gd_status(
									intval($contract['contractstatus']))
							. '</td><td><b><font size="2">'
							. self::_get_cid_link($contract['cid'])
							. '</font></b></td><td>'
							. self::_get_contract_type(
									intval($contract['type'])) . '</td><td>'
							. $contract['contractname'] . '</td><td>'
							. $contract['cusname']
							. '</td><td><font color="#ff9933"><b>'
							. Format_Util::my_money_format('%.2n',
									$contract['contractamount'])
							. '</b></font></td><td>'
							. self::_get_contract_status(
									intval($contract['isok']),
									intval($contract['pcid']),
									intval($contract['step'])) . '</td><td>'
							. self::get_remind_days($contract['time'],
									$contract['oktime']) . '</td><td>'
							. $contract['tt'] . '</td></tr>';
				} else if ($this->is_check && !($this->is_manager)) {
					$s .= '<tr><td>'
							. self::_get_contract_type(
									intval($contract['type']))
							. '</td><td style="text-align:left">'
							. self::_get_cid_link($contract['cid']) . ' &nbsp;'
							. self::_get_fmkcid($contract['fmkcid'])
							. '</td><td>' . $contract['contractcontent']
							. '</td><td>' . $contract['cusname']
							. '</td><td><font color="#ff9933"><b>'
							. Format_Util::my_money_format('%.2n',
									$contract['contractamount'])
							. '</b></font></td><td>'
							. self::_get_contract_status(
									intval($contract['isok']),
									intval($contract['pcid']),
									intval($contract['step'])) . '</td><td>'
							. self::get_remind_days($contract['time'],
									$contract['oktime']) . '</td><td>'
							. self::_get_contract_gd_status(
									intval($contract['contractstatus']))
							. '</td><td>' . $contract['tt'] . '</td><td>'
							. $this
									->_get_contract_action($contract['cid'],
											intval($contract['step']),
											intval($contract['isok']),
											intval($contract['pcid']))
							. '</td></tr>';
				} else if (!($this->is_check) && $this->is_manager) {
					$s .= '<tr><td><input type="checkbox" value="'
							. $contract['cid']
							. '" name="cids[]" class="validate[minCheckbox[1]]" id="cids_'
							. $key . '"></td><td><b><font size="2">'
							. $contract['cid'] . '</font></b></td><td>'
							. $contract['contractname'] . '</td><td>'
							. $contract['cusname'] . '</td><td>'
							. $contract['view'] . '</td></tr>';
				}
			}
		}
		unset($contracts);
		return $s;
	}

	private static function _get_contract_gd_status($contractstatus) {
		if ($contractstatus === 1) {
			return '<font color="#009900">【是】</font>';
		} else {
			return '<font color="red">【否】</font>';
		}
	}

	private static function _get_cid_link($cid) {
		return '<a href="' . BASE_URL . 'contract_cus/?o=info&cid=' . $cid
				. '" target="_blank"><b>' . $cid . '</b></a>';
	}

	private static function _get_contract_type($type) {
		if ($type === 1) {
			return '<font color="#990000"><b>框</b></font>';
		} else {
			return '<font color="#009900"><b>单</b></font>';
		}
	}

	private function _get_contract_status($isok, $pcid, $step) {
		$process = $this->process_array;
		if ($isok === 1) {
			return '<font color="#66cc00"><b>已确认</b></font>';
		} else if ($isok === -1) {
			return '<font color="red"><b>已撤销</b></font>';
		} else {
			return '<font color="#ff6600"><b>等待 '
					. $process['step'][$pcid][$step]['content'][0]
					. ' 审核</b></font>';
		}
	}

	public function get_contract_counts() {
		return $this->page . '	/' . $this->page_count . ' 页 &nbsp;&nbsp;';
	}

	private function _get_pagination($is_prev) {
		$param = '&starttime=' . $this->starttime . '&endtime='
				. $this->endtime . '&page='
				. ($is_prev ? intval($this->page) - 1
						: intval($this->page) + 1) . '&search=' . $this->search;
		if ($this->is_check || $this->is_manager) {
			$param .= '&city=' . intval($this->city) . '&dep='
					. intval($this->dep) . '&team=' . intval($this->team);
		}
		return '<a href="' . BASE_URL . 'contract_cus/?o='
				. ($this->is_check ? 'list'
						: ($this->is_manager ? 'manage' : 'mylist')) . $param
				. '">' . ($is_prev ? '上一页' : '下一页') . '</a>';
	}

	public function getPrev() {
		if (intval($this->page) === 1) {
			return '';
		} else {
			return $this->_get_pagination(TRUE);
		}
	}

	public function getNext() {
		if (intval($this->page) >= intval($this->page_count)) {
			return '';
		} else {
			return $this->_get_pagination(FALSE);
		}
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

	private static function _get_fmkcid($fmkcid) {
		if (empty($fmkcid)) {
			return NULL;
		}
		return '<font color="#990000">【<a href="' . BASE_URL
				. 'contract_cus/?o=info&cid=' . $fmkcid
				. '" target="_blank"><b><font color="#990000">' . $fmkcid
				. '</font></b></a>】</font>';
	}

	private function _get_contract_action($cid, $step, $isok, $pcid) {
		$process = $this->process_array;
		$s = '';
		if (($step === 0 || $isok === 1)
				&& in_array('sys44', $this->getPermissions())) {
			$s = '<a href="' . BASE_URL . 'contract_cus/?o=edit&cid=' . $cid
					. '">修改</a>';
		} else if ($isok === 0
				&& in_array($process['step'][$pcid][$step]['content'][2],
						$this->getPermissions())) {
			$s = '<a href="' . BASE_URL . 'contract_cus/?o=audit&cid=' . $cid
					. '">审核</a>';
		}

		if ($isok !== -1) {
			$s .= ($s === '' ? '' : ' | ')
					. '<a href="javascript:void(0);" onclick="cancelcid(\''
					. $cid . '\')">撤销</a>';
		}
		return $s;
	}

	public function get_user_list_select_html() {
		if (intval($this->city) !== 0 && intval($this->dep) === 0
				&& intval($this->team) === 0) {
			$city = new City(intval($this->city));
			return $city->get_users_select_html_by_city();
		} else if (intval($this->city) !== 0 && intval($this->dep) !== 0
				&& intval($this->team) === 0) {
			$dep = new Dep(intval($this->dep));
			return $dep->get_users_select_html_by_dep();
		} else if (intval($this->city) !== 0 && intval($this->dep) !== 0
				&& intval($this->team) !== 0) {
			$team = new Team(intval($this->team));
			return $team->get_users_select_html_by_team();
		} else {
			$user_list = new User_List(array());
			return $user_list->get_users_select_html();
		}
	}

	private function validate_form_value($action) {
		$errors = array();
		if (in_array($action, array('manage'), TRUE)) {
			$has_manager_permission = $this
					->getHas_manager_contract_permission();
			if (!$has_manager_permission) {
				$errors[] = '无权限操作';
			} else {
				$cids = $this->cids;
				if (empty($cids)) {
					$errors[] = '至少选择一个合同';
				} else {
					foreach ($cids as $cid) {
						if (empty($cid)) {
							$errors[] = '合同选择有误';
							break;
						}
					}
				}

				if (intval($this->userlist) <= 0) {
					$errors[] = '可审阅用户选择有误';
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

	public function manage_contract() {
		if ($this->validate_form_value('manage')) {
			$success = TRUE;
			$error = '';
			$this->db->query('BEGIN');
			$cids = $this->cids;
			$userlist = $this->userlist;

			$user = $this->db
					->get_row(
							'SELECT username,realname FROM users WHERE uid='
									. intval($userlist));
			if ($user !== NULL) {
				foreach ($cids as $cid) {
					$user_str = $user->realname . ' (' . $user->username . ')';
					$view = $this->db
							->get_var(
									'SELECT view FROM contract_cus WHERE cid="'
											. $cid . '" FOR UPDATE');
					if ($view !== NULL) {
						$view = explode(',', $view);
						if (!in_array($user_str, $view)) {
							$view[] = $user_str;
						}
						$user_str = implode(',', $view);
					}

					$update_result = $this->db
							->query(
									'UPDATE contract_cus SET view="'
											. $user_str . '" WHERE cid="'
											. $cid . '"');
					if ($update_result === FALSE) {
						$success = FALSE;
						$error = '设置用户审阅权限失败，请联系系统管理员';
						break;
					}
				}
			} else {
				$success = FALSE;
				$error = '可审阅的用户不存在';
			}

			if ($success) {
				$this->db->query('COMMIT');
			} else {
				$this->db->query('ROLLBACK');
			}
			return array('status' => $success ? 'success' : 'error',
					'message' => $success ? '设置用户审阅权限成功' : $error);
		}
		return array('status' => 'error', 'message' => $this->errors);
	}

	public function get_contract_mylist_html() {
		$buf = file_get_contents(TEMPLATE_PATH . 'contract/contract_mylist.tpl');
		return str_replace(
				array('[LEFT]', '[TOP]', '[VCODE]', '[CONTRACTLIST]',
						'[STARTTIME]', '[ENDTIME]', '[SEARCH]', '[ALLCOUNTS]',
						'[NEXT]', '[PREV]', '[COUNTS]', '[BASE_URL]'),
				array($this->get_left_html(), $this->get_top_html(),
						$this->get_vcode(), $this->get_contract_list_html(),
						$this->starttime, $this->endtime, $this->search,
						$this->all_count, $this->getNext(), $this->getPrev(),
						$this->get_contract_counts(), BASE_URL), $buf);
	}

	public function get_check_contract_html() {
		$buf = file_get_contents(TEMPLATE_PATH . 'contract/contract_list.tpl');
		return str_replace(
				array('[LEFT]', '[TOP]', '[VCODE]', '[CONTRACTLIST]',
						'[STARTTIME]', '[ENDTIME]', '[SEARCH]', '[ALLCOUNTS]',
						'[NEXT]', '[PREV]', '[COUNTS]', '[CITYS]', '[DEPS]',
						'[TEAMS]', '[BASE_URL]'),
				array($this->get_left_html(), $this->get_top_html(),
						$this->get_vcode(), $this->get_contract_list_html(),
						$this->starttime, $this->endtime, $this->search,
						$this->all_count, $this->getNext(), $this->getPrev(),
						$this->get_contract_counts(),
						$this->get_city_select_html(),
						$this->get_dep_select_html(),
						$this->get_team_select_html(), BASE_URL), $buf);
	}

	public function get_manage_contract_html() {
		$permission = $this->getPermissions();
		if (Array_Util::my_remove_array_other_value($permission,
				$GLOBALS['manager_contract_permission']) !== $permission) {
			$buf = file_get_contents(
					TEMPLATE_PATH . 'contract/contract_manage.tpl');
			return str_replace(
					array('[LEFT]', '[TOP]', '[VCODE]', '[CONTRACTLIST]',
							'[STARTTIME]', '[ENDTIME]', '[SEARCH]',
							'[ALLCOUNTS]', '[NEXT]', '[PREV]', '[COUNTS]',
							'[CITYS]', '[DEPS]', '[TEAMS]', '[USERLIST]',
							'[BASE_URL]'),
					array($this->get_left_html(), $this->get_top_html(),
							$this->get_vcode(),
							$this->get_contract_list_html(), $this->starttime,
							$this->endtime, $this->search, $this->all_count,
							$this->getNext(), $this->getPrev(),
							$this->get_contract_counts(),
							$this->get_city_select_html(),
							$this->get_dep_select_html(),
							$this->get_team_select_html(),
							$this->get_user_list_select_html(), BASE_URL),
					$buf);
		} else {
			return User::no_permission();
		}
	}
}
