<?php
class Finance_Info_Search extends User {
	private $has_finance_info_search_permission = FALSE;

	public function __construct() {
		parent::__construct();
		if (in_array($this->getUsername(),
				$GLOBALS['manager_finance_permission'], TRUE)
				|| intval($this->getBelong_dep()) === 2) {
			$this->has_finance_info_search_permission = TRUE;
		}
	}

	public function get_custom_info_search_html() {
		if ($this->has_finance_info_search_permission) {
			$buf = file_get_contents(
					TEMPLATE_PATH . 'finance/finance_custom_info_search.tpl');
			return str_replace(
					array('[LEFT]', '[TOP]', '[VCODE]', '[BASE_URL]'),
					array($this->get_left_html(), $this->get_top_html(),
							$this->get_vcode(), BASE_URL), $buf);
		} else {
			User::no_permission();
		}
	}

	public function get_pid_info_search_html() {
		if ($this->has_finance_info_search_permission) {
			$buf = file_get_contents(
					TEMPLATE_PATH . 'finance/finance_pid_info_search.tpl');
			return str_replace(
					array('[LEFT]', '[TOP]', '[VCODE]', '[BASE_URL]'),
					array($this->get_left_html(), $this->get_top_html(),
							$this->get_vcode(), BASE_URL), $buf);
		} else {
			User::no_permission();
		}
	}
}
