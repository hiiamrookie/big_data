<?php
class Dep_List extends User {
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

	private function _get_dep_data() {
		$result = array();
		$deps = $this->db
				->get_results(
						'SELECT a.id,a.depname,b.companyname,a.issupport FROM hr_department a, hr_company b WHERE a.cityid=b.id AND a.islive=1 ORDER BY a.cityid,a.issupport');
		if ($deps !== NULL) {
			foreach ($deps as $dep) {
				$result[] = array('id' => $dep->id, 'depname' => $dep->depname,
						'companyname' => $dep->companyname,
						'issupport' => $dep->issupport);
			}
		}
		return $result;
	}

	private function _get_list_html() {
		$result = '';
		$datas = $this->_get_dep_data();
		if (!empty($datas)) {
			foreach ($datas as $key => $data) {
				$result .= '<tr><td>' . ($key + 1) . '</td><td>'
						. $data['companyname'] . '</td><td>'
						. $data['depname'] . '</td><td>'
						. (intval($data['issupport']) === 0 ? '<font color="#990000">否</a>'
								: '<font color="#006600">是</a>')
						. '</td><td><a href="' . BASE_URL
						. 'hr/?o=editdepartment&id=' . intval($data['id'])
						. '">修改</a> | <a href="javascript:del('
						. intval($data['id']) . ')">删除</a></td></tr>';
			}
		}
		unset($datas);
		return $result;
	}

	public function get_department_list_html() {
		if ($this->getHas_hr_permission()) {
			$buf = file_get_contents(TEMPLATE_PATH . 'hr/department_list.tpl');
			return str_replace(
					array('[LEFT]', '[TOP]', '[VCODE]', '[DEPLIST]',
							'[BASE_URL]'),
					array($this->get_left_html(), $this->get_top_html(),
							$this->get_vcode(), $this->_get_list_html(),
							BASE_URL), $buf);
		} else {
			return User::no_permission();
		}
	}
}