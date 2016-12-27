<?php
class Dep_Process_List extends User {
	private $has_process_permission = FALSE;

	/**
	 * @return the $has_process_permission
	 */
	public function getHas_process_permission() {
		return $this->has_process_permission;
	}
	
	public function __construct() {
		parent::__construct();
		if (in_array($this->getUsername(), $GLOBALS['manager_setup_permission'], TRUE)) {
			$this->has_process_permission = TRUE;
		}
	}

	private function _get_dep_process_data() {
		$results = array();
		$dep_processes = $this->db
				->get_results(
						'SELECT id,name,des,dep FROM process_dep WHERE islive=1 ORDER BY id DESC');
		if ($dep_processes !== NULL) {
			foreach ($dep_processes as $dep_processe) {
				$results[] = array('id' => $dep_processe->id,
						'name' => $dep_processe->name,
						'des' => $dep_processe->des,
						'dep' => $dep_processe->dep);
			}
		}
		return $results;
	}

	private function _get_list_html() {
		$dep_process = '';
		$datas = $this->_get_dep_process_data();
		$depinfo = Dep::getInstance();
		$count = 0;
		foreach ($datas as $key => $data) {
			if ($depinfo[$data['dep']] !== NULL) {
				$count++;
				$dep_process .= '<tr><td>' . $count . '</td><td>'
						. $this
								->get_depname($depinfo[$data['dep']][1],
										$depinfo[$data['dep']][0])
						. '</td><td>' . $data['name'] . '</td><td>'
						. $data['des'] . '</td><td><a href="' . BASE_URL
						. 'manage/?o=depprocessedit&id='
						. intval($data['id'])
						. '">修改</a> <a href="javascript:del('
						. intval($data['id']) . ')">删除</a></td></tr>';
			}
		}
		unset($datas);
		unset($depinfo);
		return $dep_process;
	}

	public function get_dep_process_list_html() {
		if ($this->getHas_process_permission()) {
			$buf = file_get_contents(
					TEMPLATE_PATH . 'manage/depprocess_list.tpl');
			return str_replace(
					array('[LEFT]', '[TOP]', '[DEPPROCESS_LIST]', '[VCODE]',
							'[BASE_URL]'),
					array($this->get_left_html(), $this->get_top_html(),
							$this->_get_list_html(), $this->get_vcode(),
							BASE_URL), $buf);
		} else {
			return User::no_permission();
		}
	}
}