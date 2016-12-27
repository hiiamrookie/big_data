<?php
class Finance_Tj extends User {
	private $has_finance_tj_permission = FALSE;

	/**
	 * @return the $has_finance_tj_permission
	 */
	public function getHas_finance_tj_permission() {
		return $this->has_finance_tj_permission;
	}

	public function __construct() {
		parent::__construct();
		if (in_array($this->getUsername(),
				$GLOBALS['manager_finance_tj_permission'], TRUE)) {
			$this->has_finance_tj_permission = TRUE;
		}
	}

	public function get_tj_all() {
		if ($this->getHas_finance_tj_permission()) {
			$buf = file_get_contents(TEMPLATE_PATH . 'finance/finance_tj.tpl');
			return str_replace(
					array('[LEFT]', '[TOP]', '[CITYS]', '[VCODE]', '[BASE_URL]'),
					array($this->get_left_html(), $this->get_top_html(),
							City::get_city_select_html(FALSE),
							$this->get_vcode(), BASE_URL), $buf);
		} else {
			return User::no_permission();
		}
	}
}
