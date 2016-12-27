<?php
class Process_List extends User {
	private $has_process_permission = FALSE;

	/**
	 * @return the $has_process_permission
	 */
	public function getHas_process_permission() {
		return $this->has_process_permission;
	}

	public function __construct() {
		parent::__construct();
		if (in_array($this->getUsername(),
				$GLOBALS['manager_setup_permission'], TRUE)) {
			$this->has_process_permission = TRUE;
		}
	}

	private function _get_process_data() {
		$results = array();
		$processes = $this->db
				->get_results(
						'SELECT id,name,des,deps FROM process WHERE islive=1 ORDER BY module,id DESC');
		if ($processes !== NULL) {
			foreach ($processes as $process) {
				$results[] = array('id' => $process->id,
						'name' => $process->name, 'des' => $process->des,
						'deps' => $process->deps);
			}
		}
		return $results;
	}

	private function _get_list_html() {
		$process = '';
		$datas = $this->_get_process_data();
		$depinfo = Dep::getInstance();

		foreach ($datas as $key => $data) {
			$deps = $data['deps'];
			$deps_str = '';
			if ($deps !== '') {
				$deps = explode('^', $deps);
				for ($i = 0, $count = count($deps); $i < $count; $i++) {
					$dep = $this
							->get_depname($depinfo[$deps[$i]][1],
									$depinfo[$deps[$i]][0]);
					if (!empty($dep)) {
						$deps_str .= $dep;
						if ($i !== ($count - 1)) {
							$deps_str .= ' ， ';
						}
					}
				}
			}
			$process .= '<tr><td>' . ($key + 1) . '</td><td>' . $data['name']
					. '</td><td>' . $data['des'] . '</td><td>' . $deps_str
					. '</td><td><a href="' . BASE_URL
					. 'manage/?o=processedit&id=' . intval($data['id'])
					. '">修改</a> <a href="javascript:del(' . intval($data['id'])
					. ')">删除</a></td></tr>';
		}
		unset($datas);
		unset($depinfo);
		return $process;
	}

	public function get_process_list_html() {
		if ($this->getHas_process_permission()) {
			$buf = file_get_contents(TEMPLATE_PATH . 'manage/process_list.tpl');
			return str_replace(
					array('[LEFT]', '[PROCESS_LIST]', '[VCODE]', '[TOP]',
							'[BASE_URL]'),
					array($this->get_left_html(), $this->_get_list_html(),
							$this->get_vcode(), $this->get_top_html(),
							BASE_URL), $buf);
		} else {
			return User::no_permission();
		}
	}
}