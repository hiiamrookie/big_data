<?php
class City_List extends User {
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

	private function _get_city_data() {
		$result = array();
		$citys = $this->db
				->get_results('SELECT id,companyname FROM hr_company');
		if ($citys !== NULL) {
			foreach ($citys as $city) {
				$result[] = array('id' => $city->id,
						'companyname' => $city->companyname);
			}
		}
		return $result;
	}

	private function _get_list_html() {
		$result = '';
		$datas = $this->_get_city_data();
		if (!empty($datas)) {
			foreach ($datas as $key => $data) {
				$result .= '<tr><td>' . ($key + 1) . '</td><td>'
						. $data['companyname'] . '</td><td><a href="'
						. BASE_URL . 'hr/?o=editcompany&id=' . $data['id']
						. '">修改</a></td></tr>';
			}
		}
		unset($datas);
		return $result;
	}

	public function get_city_list_html() {
		if ($this->getHas_hr_permission()) {
			$buf = file_get_contents(TEMPLATE_PATH . 'hr/company_list.tpl');
			return str_replace(
					array('[LEFT]', '[TOP]', '[VCODE]', '[COMPANYLIST]',
							'[BASE_URL]'),
					array($this->get_left_html(), $this->get_top_html(),
							$this->get_vcode(), $this->_get_list_html(),
							BASE_URL), $buf);
		} else {
			return User::no_permission();
		}
	}
}