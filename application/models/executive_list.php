<?php
class Executive_List extends User {
	private $page;
	private $starttime;
	private $endtime;
	private $search;

	private $city;
	private $dep;
	private $team;

	private $gd;

	private $is_check = FALSE;
	private $is_manager = FALSE;
	private $all_count;
	private $page_count;
	private $executives = array();
	private $process_array = array();

	const LIMIT = 50;

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
			$this->_get_executive_list_datas($is_check, $is_manager);
			$this->process_array = Process::getInstance();
			$this->is_check = $is_check;
			$this->is_manager = $is_manager;
		}
	}

	private function _get_executive_list_datas($is_check = FALSE,
			$is_manager = FALSE) {
		$has_permission = $this->getHas_check_executive_permission();
		$has_manager_permission = $this->getHas_manager_executive_permission();

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
				&& $this
						->get_relation_executive_permission(intval($city),
								intval($dep), intval($team)) === 0) {
			$this->all_count = 0;
			$this->page = 1;
			$this->page_count = 0;
			return;
		} else if ($is_manager && !$has_manager_permission) {
			$this->all_count = 0;
			$this->page = 1;
			$this->page_count = 0;
			return;
		}

		$where_sql = array();
		if (!$is_check && !$is_manager) {
			$where_sql[] = ' (a.user=' . intval($this->getUid())
					. ' OR a.principal=' . intval($this->getUid()) . ') ';
		}

		if (strtotime($this->starttime) !== FALSE) {
			$where_sql[] = ' a.time>=' . strtotime($this->starttime) . ' ';
		}

		if (strtotime($this->endtime) !== FALSE) {
			$where_sql[] = ' a.time<' . (strtotime($this->endtime) + 86400)
					. ' ';
		}

		if ($is_check || $is_manager) {

			if (intval($city) !== 0 && intval($dep) === 0
					&& intval($team) === 0) {
				$where_sql[] = ' a.city=' . intval($city) . ' ';
			} else if (intval($city) !== 0 && intval($dep) !== 0
					&& intval($team) === 0) {
				//$where_sql [] = ' a.city=' . intval ( $city ) . ' AND (a.dep=' . intval ( $dep ) . ' OR a.support LIKE "' . intval ( $dep ) . '^%" OR a.support LIKE "%|' . intval ( $dep ) . '^%") ';
				$where_sql[] = ' (a.city=' . intval($city) . ' AND a.dep='
						. intval($dep) . ' OR a.support LIKE "' . intval($dep)
						. '^%" OR a.support LIKE "%|' . intval($dep) . '^%") ';
			} else if (intval($city) !== 0 && intval($dep) !== 0
					&& intval($team) !== 0) {
				$where_sql[] = ' a.city=' . intval($city) . ' AND a.dep='
						. intval($dep) . ' AND a.team=' . intval($team) . ' ';
			}

			if ($is_manager && intval($this->gd) === 1) {
				$where_sql[] = ' a.gdtime=0 ';
			}
		}

		if (self::validate_field_not_null($this->search)
				&& self::validate_field_not_empty($this->search)) {
			$where_sql[] = ' (a.pid LIKE "%' . $this->search
					. '%" OR a.name LIKE "%' . $this->search
					. '%" OR b.cusname LIKE "%' . $this->search . '%") ';
		}

		$query = 'SELECT COUNT(*) FROM executive a, contract_cus b ,users c WHERE a.cid=b.cid AND a.user=c.uid ';
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
		$sql = 'SELECT a.time,a.oktime,a.isalter,a.pid,a.cid,a.id,a.name,a.amount,a.allcost,a.isyg,a.isok,a.pcid,a.step,a.user,a.gdtime,FROM_UNIXTIME(a.time) AS tt,a.is_closed,b.cusname,c.username,c.realname FROM executive a, contract_cus b,users c WHERE a.cid=b.cid AND a.user=c.uid ';
		if (!empty($where_sql)) {
			$sql .= ' AND ' . implode('AND', $where_sql);
		}
		$sql .= ' ORDER BY a.time DESC LIMIT ' . $start . ',' . self::LIMIT;

		$executives = $this->db->get_results($sql);
		if ($executives !== NULL) {
			foreach ($executives as $executive) {
				$results[] = array('time' => $executive->time,
						'oktime' => $executive->oktime,
						'isalter' => $executive->isalter,
						'pid' => $executive->pid, 'cid' => $executive->cid,
						'id' => $executive->id, 'name' => $executive->name,
						'amount' => $executive->amount,
						'allcost' => $executive->allcost,
						'isyg' => $executive->isyg, 'isok' => $executive->isok,
						'pcid' => $executive->pcid, 'step' => $executive->step,
						'user' => $executive->user,
						'gdtime' => $executive->gdtime, 'tt' => $executive->tt,
						'cusname' => $executive->cusname,
						'username' => $executive->realname . ' ('
								. $executive->username . ')','is_closed'=>$executive->is_closed);
			}
		}
		$this->executives = $results;
		unset($results);
	}

	public function get_executive_list_html() {
		$has_permission = $this->getHas_check_executive_permission();
		$has_manager_permission = $this->getHas_manager_executive_permission();
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
				&& $this
						->get_relation_executive_permission(intval($city),
								intval($dep), intval($team)) === 0
				|| $this->is_manager && !$has_manager_permission) {
			return '<td colspan="10"><font color="#FF0000"><b>您当前没有权限查看 ！</b></font></td>';
		}
		$datas = $this->executives;
		$result = '';
		if (!empty($datas)) {
			foreach ($datas as $data) {
				$result .= '<tr><td>'
						. self::_get_executive_type(intval($data['isalter']))
						. '</td><td>' . $data['pid'] . '</td><td>'
						. $data['cusname'] . '</td><td>'
						. self::_get_executive_name_link(intval($data['id']),
								$data['pid'], $data['name']) . '</td><td>'
						. self::_get_executive_amount($data['amount'])
						. '</td><td>'
						. self::_get_executive_cost($data['allcost'],
								intval($data['isyg'])) . '</td><td>'
						. $this
								->_get_executive_status(intval($data['isok']),
										$data['pcid'], $data['step'])
						. '</td><td>'
						. self::get_remind_days($data['time'], $data['oktime'])
						. '</td><td>' . substr($data['tt'], 0, 10)
						. '</td><td>'
						. ($this->is_check ? $data['username']
								: ($this->is_manager ? self::_get_executive_manage_action(
												$data['gdtime'],
												intval($data['id']),
												intval($data['isok']),(int)($data['is_closed']))
										: $this
												->_get_executive_action(
														intval($data['user']),
														$data['pid'],
														intval($data['id']))))
						. '</td></tr>';
			}
		}
		unset($datas);
		return $result;
	}

	private static function _get_executive_type($isalter) {
		if ($isalter === 0) {
			return '<font color="#66cc00">【新】</font>';
		} else {
			return '<font color="#cc6600">【变' . $isalter . '】</font>';
		}
	}

	private static function _get_executive_name_link($id, $pid, $name) {
		return '<a href="' . BASE_URL . 'executive/?o=info&id=' . $id . '&pid='
				. $pid . '" target="_blank"><b>' . $name . '</b></a>';
	}

	private static function _get_executive_amount($amount) {
		return '<font color="#ff9933"><b>'
				. Format_Util::my_money_format('%.2n', $amount) . '</b></font>';
	}

	private static function _get_executive_cost($cost, $isyg) {
		return '<font color="' . ($isyg > 0 ? '#0000FF' : '#ff9933') . '"><b>'
				. Format_Util::my_money_format('%.2n', $cost) . '</b></font>';
	}

	private function _get_executive_status($isok, $pcid, $step) {
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

	private function _get_executive_action($user, $pid, $id) {
		$action = '';
		if ($user === $this->getUid()) {
			$row = $this->db
					->get_row(
							'SELECT id FROM executive WHERE pid="' . $pid
									. '" AND isok=1 ORDER BY id DESC LIMIT 1');
			if ($row !== NULL) {
				$tmp_id = intval($row->id);
			} else {
				$tmp_id = 0;
			}
			if ($tmp_id === $id) {
				$action = '<a href="' . BASE_URL . 'executive/?o=alter&pid='
						. $pid . '">变更</a>';
			}
		}
		return $action;
	}

	private static function _get_executive_manage_action($gdtime, $id, $isok,
			$is_closed) {
		if ($isok === -1) {
			return '';
		}
		if (empty($gdtime)) {
			$tmps = sprintf(
					'<span><a href="javascript:void(0)" onclick="gd(%d,this);">归档</a></span>',
					$id);
		} else {
			$tmps = sprintf('<font color="#66cc00">已归档</font>');
		}
		if ($isok === 0) {
			$tmps .= sprintf(
					'&nbsp;<span>| &nbsp;<a href="javascript:void(0)" onclick="cancel(%d,this)">撤销</a></span>',
					$id);
		}
		if ($is_closed === 0) {
			$tmps .= sprintf(
					'&nbsp;<span>| &nbsp;<a href="javascript:void(0)" onclick="closea(%d,this)">关闭</a></span>',
					$id);
		} else {
			$tmps .= sprintf(
					'&nbsp;<span>| &nbsp;<a href="javascript:void(0)" onclick="opena(%d,this)">打开</a></span>',
					$id);
		}
		return $tmps;
	}

	public function get_executive_counts() {
		return $this->page . '	/' . $this->page_count . ' 页 &nbsp;&nbsp;';
	}

	private function _get_pagination($is_prev) {
		$param = '&starttime=' . $this->starttime . '&endtime='
				. $this->endtime . '&page='
				. ($is_prev ? intval($this->page) - 1 : intval($this->page) + 1)
				. '&search=' . $this->search;
		if ($this->is_check || $this->is_manager) {
			$param .= '&city=' . intval($this->city) . '&dep='
					. intval($this->dep) . '&team=' . intval($this->team);
			if ($this->is_manager) {
				$param .= '&gd=' . $this->gd;
			}
		}
		return '<a href="' . BASE_URL . 'executive?o='
				. ($this->is_check ? 'alllist'
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

	public function get_city_select_html() {
		return City::get_city_select_html(FALSE, $this->city);
	}

	public function get_dep_select_html() {
		return Dep::get_dep_select_html_by_city($this->city, FALSE, $this->dep);
	}

	public function get_team_select_html() {
		return Team::get_team_select_html_by_dep($this->dep, FALSE, $this->team);
	}

	public function get_gd_checked() {
		if (intval($this->gd) === 1) {
			return 'checked="checked"';
		} else {
			return '';
		}
	}

	public function get_executive_mylist_html() {
		$buf = file_get_contents(
				TEMPLATE_PATH . 'executive/executive_mylist.tpl');
		return str_replace(
				array('[LEFT]', '[TOP]', '[VCODE]', '[EXECUTIVELIST]',
						'[ALLCOUNTS]', '[COUNTS]', '[NEXT]', '[PREV]',
						'[STARTTIME]', '[ENDTIME]', '[SEARCH]', '[BASE_URL]'),
				array($this->get_left_html(), $this->get_top_html(),
						$this->get_vcode(), $this->get_executive_list_html(),
						$this->all_count, $this->get_executive_counts(),
						$this->getNext(), $this->getPrev(), $this->starttime,
						$this->endtime, $this->search, BASE_URL), $buf);
	}

	public function get_executive_alllist_html() {
		$buf = file_get_contents(
				TEMPLATE_PATH . 'executive/executive_alllist.tpl');
		return str_replace(
				array('[LEFT]', '[TOP]', '[VCODE]', '[CHECKEXECUTIVELIST]',
						'[ALLCOUNTS]', '[COUNTS]', '[NEXT]', '[PREV]',
						'[STARTTIME]', '[ENDTIME]', '[SEARCH]', '[CITYS]',
						'[DEPS]', '[TEAMS]', '[BASE_URL]'),
				array($this->get_left_html(), $this->get_top_html(),
						$this->get_vcode(), $this->get_executive_list_html(),
						$this->all_count, $this->get_executive_counts(),
						$this->getNext(), $this->getPrev(), $this->starttime,
						$this->endtime, $this->search,
						$this->get_city_select_html(),
						$this->get_dep_select_html(),
						$this->get_team_select_html(), BASE_URL), $buf);
	}

	public function get_executive_manage_html() {
		if ($this->getHas_manager_executive_permission()) {
			$buf = file_get_contents(
					TEMPLATE_PATH . 'executive/executive_manage.tpl');
			return str_replace(
					array('[LEFT]', '[TOP]', '[VCODE]',
							'[MANAGEEXECUTIVELIST]', '[ALLCOUNTS]', '[COUNTS]',
							'[NEXT]', '[PREV]', '[STARTTIME]', '[ENDTIME]',
							'[SEARCH]', '[CITYS]', '[DEPS]', '[TEAMS]',
							'[CHECK]', '[BASE_URL]'),
					array($this->get_left_html(), $this->get_top_html(),
							$this->get_vcode(),
							$this->get_executive_list_html(), $this->all_count,
							$this->get_executive_counts(), $this->getNext(),
							$this->getPrev(), $this->starttime, $this->endtime,
							$this->search, $this->get_city_select_html(),
							$this->get_dep_select_html(),
							$this->get_team_select_html(),
							$this->get_gd_checked(), BASE_URL), $buf);
		} else {
			return User::no_permission();
		}
	}
}
