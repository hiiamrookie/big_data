<?php
class Dep_Permission_List extends User {
	private $has_setup_permission = FALSE;

	/**
	 * @return the $has_setup_permission
	 */
	public function getHas_setup_permission() {
		return $this->has_setup_permission;
	}

	public function __construct() {
		parent::__construct();
		if (in_array($this->getUsername(), $GLOBALS['manager_setup_permission'], TRUE)) {
			$this->has_setup_permission = TRUE;
		}
	}

	private function _get_dep_permission_data() {
		$results = array();
		$permissions = $this->db
				->get_results(
						'SELECT id,name,des,dep FROM permissions_dep WHERE islive=1 ORDER BY id DESC');
		if ($permissions !== NULL) {
			foreach ($permissions as $permissio) {
				$results[] = array('id' => $permissio->id,
						'name' => $permissio->name, 'des' => $permissio->des,
						'dep' => $permissio->dep);
			}
		}
		return $results;
	}

	private function _get_list_html() {
		$dep_permission = '';
		$datas = $this->_get_dep_permission_data();
		$depinfo = Dep::getInstance();
		foreach ($datas as $key => $data) {
			if (!empty($depinfo[$data['dep']])) {
				$dep_permission .= '<tr><td>' . ($key + 1) . '</td><td>'
						. $this
								->get_depname($depinfo[$data['dep']][1],
										$depinfo[$data['dep']][0])
						. '</td><td>' . $data['name'] . '</td><td>'
						. $data['des'] . '</td><td><a href="' . BASE_URL
						. 'system/?o=deppermissionedit&id='
						. intval($data['id'])
						. '">修改</a> <a href="javascript:del('
						. intval($data['id']) . ')">删除</a></td></tr>';
			}
		}
		unset($datas);
		unset($depinfo);
		return $dep_permission;
	}

	public function get_dep_permission_list_html() {
		if ($this->getHas_setup_permission()) {
			$buf = file_get_contents(
					TEMPLATE_PATH . 'system/deppermission_list.tpl');
			return str_replace(
					array('[LEFT]', '[TOP]', '[VCODE]', '[DEPPERMISSION_LIST]',
							'[BASE_URL]'),
					array($this->get_left_html(), $this->get_top_html(),
							$this->get_vcode(), $this->_get_list_html(),
							BASE_URL), $buf);
		} else {
			return User::no_permission();
		}
	}
}