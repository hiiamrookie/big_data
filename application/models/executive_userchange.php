<?php
class Executive_Userchange extends User {
	private $search;
	private $name;
	private $principal;
	private $user;
	private $dep;
	private $team;
	private $pid;

	public function __construct($search) {
		parent::__construct();
		$this->search = $search;
		if ($this->search !== NULL) {
			$row = $this->db
					->get_row(
							'SELECT name,principal,user,dep,team FROM executive WHERE pid="'
									. $this->search
									. '" ORDER BY time DESC LIMIT 1');
			if ($row !== NULL) {
				$this->name = $row->name;
				$this->principal = $row->principal;
				$this->user = $row->user;
				$this->dep = $row->dep;
				$this->team = $row->team;
				$this->pid = $this->search;
			}
		}
	}

	private function _get_user_select_html($is_user) {
		$s = '<select name="' . ($is_user ? 'user' : 'principal') . '" id="'
				. ($is_user ? 'user' : 'principal')
				. '" class="validate[required] select">';
		if ($is_user && $this->user !== NULL
				|| !$is_user && $this->principal !== NULL) {
			if (intval($this->team) > 0) {
				$team = new Team(intval($this->team));
				$s .= $team
						->get_users_select_html_by_team(
								intval(
										$is_user ? $this->user
												: $this->principal));
				unset($team);
			} else if (intval($this->dep) > 0) {
				$dep = new Dep(intval($this->dep));
				$s .= $dep
						->get_users_select_html_by_dep(
								intval(
										$is_user ? $this->user
												: $this->principal));
				unset($dep);
			}
		} else {
			$s .= '<option value="">请选择人员</option>';
		}
		$s .= '</select>';
		return $s;
	}

	public function get_user_change_html() {
		if ($this->getHas_manager_executive_permission()) {
			$buf = file_get_contents(
					TEMPLATE_PATH . 'executive/executive_userchange.tpl');
			return str_replace(
					array('[LEFT]', '[TOP]', '[VCODE]', '[SEARCH]', '[PID]',
							'[NAME]', '[USERS]', '[PRINCIPAL]', '[BASE_URL]','[CITYS]'),
					array($this->get_left_html(), $this->get_top_html(),
							$this->get_vcode(), $this->search, $this->pid,
							$this->name, $this->_get_user_select_html(TRUE),
							$this->_get_user_select_html(FALSE), BASE_URL,City::get_city_select_html(FALSE)),
					$buf);
		} else {
			return User::no_permission();
		}
	}
}
