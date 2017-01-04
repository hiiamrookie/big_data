<?php
class Team_List extends User {
	private $has_hr_permission = FALSE;

	/**
	 * @return the $has_hr_permission
	 */
	public function getHas_hr_permission() {
		return $this->has_hr_permission;
	}

	public function __construct() {
		parent::__construct();
		if (in_array($this->getUsername(),
				$GLOBALS['manager_setup_permission'], TRUE)) {
			$this->has_hr_permission = TRUE;
		}
	}

	private function _get_team_data() {
		$result = array();
		$teams = $this->db
				->get_results(
						'SELECT a.id,c.companyname,b.depname,a.teamname FROM hr_team a, hr_department b,hr_company c WHERE b.id=a.dep AND b.cityid=c.id AND a.islive=1');
		if ($teams !== NULL) {
			foreach ($teams as $team) {
				$result[] = array('id' => $team->id,
						'companyname' => $team->companyname,
						'depname' => $team->depname,
						'teamname' => $team->teamname);
			}
		}
		return $result;
	}

	private function _get_list_html() {
		$result = '';
		$datas = $this->_get_team_data();
		if (!empty($datas)) {
			foreach ($datas as $key => $data) {
				$result .= '<tr><td>' . ($key + 1) . '</td><td>'
						. $data['companyname'] . '</td><td>' . $data['depname']
						. '</td><td>' . $data['teamname']
						. '</td><td><a href="' . BASE_URL
						. 'hr/?o=editteam&id=' . intval($data['id'])
						. '">修改</a> | <a href="javascript:del('
						. intval($data['id']) . ')">删除</a></td></tr>';
			}
		}
		unset($datas);
		return $result;
	}

	public function get_team_list_html() {
		if ($this->getHas_hr_permission()) {
			$buf = file_get_contents(TEMPLATE_PATH . 'hr/team_list.tpl');
			return str_replace(
					array('[LEFT]', '[TOP]', '[VCODE]', '[TEAMLIST]',
							'[BASE_URL]'),
					array($this->get_left_html(), $this->get_top_html(),
							$this->get_vcode(), $this->_get_list_html(),
							BASE_URL), $buf);
		} else {
			return User::no_permission();
		}
	}
}