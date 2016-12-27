<?php
class Permission_list extends User {
	private $has_setup_permission = FALSE;

	/**
	 * @return the $has_setup_permission
	 */
	public function getHas_setup_permission() {
		return $this->has_setup_permission;
	}

	public function __construct() {
		parent::__construct();
		if (in_array($this->getUsername(),
				$GLOBALS['manager_setup_permission'], TRUE)) {
			$this->has_setup_permission = TRUE;
		}
	}

	private function _get_permission_data() {
		$results = array();
		$permissions = $this->db
				->get_results(
						'SELECT a.id,a.name,a.des,b.modulename FROM permissions a,sys_module b WHERE a.module=b.id AND a.islive=1 ORDER BY b.modulename,a.id DESC');
		if ($permissions !== NULL) {
			foreach ($permissions as $permission) {
				$results[] = array('id' => $permission->id,
						'name' => $permission->name, 'des' => $permission->des,
						'modulename' => $permission->modulename);
			}
		}
		return $results;
	}

	private function _get_list_html() {
		$permission = '';
		$datas = $this->_get_permission_data();
		foreach ($datas as $key => $data) {
			$permission .= '<tr><td>' . ($key + 1) . '</td><td>'
					. $data['modulename'] . '</td><td>' . $data['name']
					. '</td><td>' . $data['des'] . '</td><td><a href="'
					. BASE_URL . 'system/?o=permissionedit&id='
					. intval($data['id']) . '">修改</a> <a href="javascript:del('
					. intval($data['id']) . ')">删除</a></td></tr>';
		}
		unset($datas);
		return $permission;
	}

	public function get_permission_list_html() {
		if ($this->getHas_setup_permission()) {
			$buf = file_get_contents(
					TEMPLATE_PATH . 'system/permission_list.tpl');
			return str_replace(
					array('[LEFT]', '[TOP]', '[VCODE]', '[PERMISSION_LIST]',
							'[BASE_URL]'),
					array($this->get_left_html(), $this->get_top_html(),
							$this->get_vcode(), $this->_get_list_html(),
							BASE_URL), $buf);
		} else {
			return User::no_permission();
		}
	}
}