<?php
class Tec_Project_List extends User {
	private $page;
	private $all_count;
	private $page_count;

	private $tec_projects = array();
	const LIMIT = 50;

	private $has_tec_project_permission = FALSE;

	public function __construct($fields) {
		parent::__construct();
		if (intval($this->getBelong_dep()) === 6) {
			//技术部
			$this->has_tec_project_permission = TRUE;
		}
		if (!empty($fields)) {
			foreach ($this as $key => $value) {
				if ($fields[$key] !== NULL
						&& !in_array($key, array('has_tec_project_permission'),
								TRUE)) {
					$this->$key = $fields[$key];
				}
			}

			$this->_get_list_datas();
		}
	}

	private function _get_list_datas() {
		if ($this->has_tec_project_permission) {
			$this->all_count = intval(
					$this->db->get_var('SELECT COUNT(*) FROM tec_project'));
			$this->page_count = ceil($this->all_count / self::LIMIT);
			$start = self::LIMIT * intval($this->page) - self::LIMIT;
			if ($start < 0) {
				$start = 0;
			}

			$results = array();
			$res = $this->db
					->get_results(
							'SELECT id,project_id,isalter,addtime,pid,project_name,project_type,isresponse,isok,userid FROM tec_project ORDER BY addtime DESC LIMIT '
									. $start . ',' . self::LIMIT);
			if ($res !== NULL) {
				foreach ($res as $val) {
					$results[] = array('id' => $val->id,
							'project_id' => $val->project_id,
							'isalter' => $val->isalter,
							'addtime' => $val->addtime, 'pid' => $val->pid,
							'project_name' => $val->project_name,
							'project_type' => $val->project_type,
							'isresponse' => $val->isresponse,
							'isok' => $val->isok, 'userid' => $val->userid);
				}
			}

			$this->tec_projects = $results;
			unset($results);
		}
	}

	public static function get_tec_project_version($isalter) {
		if ($isalter === 0) {
			return '<font color="#66cc00">【新】</font>';
		} else {
			return '<font color="#cc6600">【变' . $isalter . '】</font>';
		}
	}

	public static function get_tec_project_type($type) {
		if ($type === 1) {
			return '客户项目';
		} else if ($type === 2) {
			return '内部项目';
		}
		return '';
	}

	private static function _get_tec_project_status($isok) {
		if ($isok === 1) {
			return '正常';
		} else if ($isok === -1) {
			return '已撤销';
		}
		return '';
	}

	public static function get_tec_project_response($isresponse) {
		if ($isresponse === 0) {
			return '<font color="#ff6600"><b>未响应</b></font>';
		} else if ($isresponse === 1) {
			return '<font color="#66cc00"><b>已响应</b></font>';
		}
		return '';
	}

	private function _get_tec_project_action($id, $isresponse, $user,
			$project_id) {
		$action = array();
		$row = $this->db
				->get_row(
						'SELECT id FROM tec_project WHERE project_id="'
								. $project_id
								. '" AND isok=1 ORDER BY id DESC LIMIT 1');
		if ($row !== NULL) {
			$tmp_id = intval($row->id);
		} else {
			$tmp_id = 0;
		}

		if ($user === intval($this->getUid())) {
			if ($tmp_id === $id) {
				$action[] = '<a href="' . BASE_URL . 'tec/?o=projectedit&id='
						. $id . '">修改</a>';
			}
		}
		if ($isresponse === 0 && $tmp_id === $id) {
			$action[] = '<a href="' . BASE_URL . 'tec/?o=projectresponse&id='
					. $id . '&project_id=' . $project_id . '">响应</a>';
		}
		return implode('&nbsp;|&nbsp;', $action);
	}

	private static function _get_tec_project_name_link($id, $project_id, $name) {
		return '<a href="' . BASE_URL . 'tec/?o=projectinfo&id=' . $id
				. '&project_id=' . $project_id . '"><b>' . $name . '</b></a>';
	}

	private function _get_list_html() {
		$s = '';
		$tec_projects = $this->tec_projects;
		foreach ($tec_projects as $tec) {
			$s .= '<tr><td>'
					. self::get_tec_project_version(intval($tec['isalter']))
					. '</td><td>' . $tec['addtime'] . '</td><td>' . $tec['pid']
					. '</td><td>'
					. self::_get_tec_project_name_link(intval($tec['id']),
							$tec['project_id'], $tec['project_name'])
					. '</td><td>'
					. self::get_tec_project_type(intval($tec['project_type']))
					. '</td><td>'
					. self::_get_tec_project_status(intval($tec['isok']))
					. '</td><td>'
					. self::get_tec_project_response(
							intval($tec['isresponse'])) . '</td><td>'
					. $this
							->_get_tec_project_action(intval($tec['id']),
									intval($tec['isresponse']),
									intval($tec['userid']), $tec['project_id'])
					. '</td></tr>';
		}
		return $s;
	}

	private function _getPrev() {
		if (intval($this->page) === 1) {
			return '';
		} else {
			return $this->_get_pagination(TRUE);
		}
	}

	private function _getNext() {
		if (intval($this->page) >= intval($this->page_count)) {
			return '';
		} else {
			return $this->_get_pagination(FALSE);
		}
	}

	private function _get_pagination($is_prev) {
		return '<a href="' . BASE_URL . 'tec/?o=projectlist&page='
				. ($is_prev ? intval($this->page) - 1 : intval($this->page) + 1)
				. '">' . ($is_prev ? '上一页' : '下一页') . '</a>';
	}

	private function _get_tec_project_counts() {
		return $this->page . '	/' . $this->page_count . ' 页 &nbsp;&nbsp;';
	}

	public function get_tec_project_list_html() {
		if ($this->has_tec_project_permission) {
			$buf = file_get_contents(TEMPLATE_PATH . 'tec/tec_project_list.tpl');
			return str_replace(
					array('[LEFT]', '[TOP]', '[VCODE]', '[PROJECTLIST]',
							'[ALLCOUNTS]', '[COUNTS]', '[PREV]', '[NEXT]',
							'[BASE_URL]'),
					array($this->get_left_html(), $this->get_top_html(),
							$this->get_vcode(), $this->_get_list_html(),
							$this->all_count, $this->_get_tec_project_counts(),
							$this->_getPrev(), $this->_getNext(), BASE_URL),
					$buf);
		}
		User::no_permission();
	}
}
