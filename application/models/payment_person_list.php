<?php
class Payment_Person_List extends User {
	public function __construct() {
		parent::__construct();
	}

	public function get_payment_person_list_html() {
		$buf = file_get_contents(
				TEMPLATE_PATH . 'finance/payment/payment_person_list.tpl');
		return str_replace(array('[LEFT]', '[TOP]', '[VCODE]', '[BASE_URL]'),
				array($this->get_left_html(), $this->get_top_html(),
						$this->get_vcode(), BASE_URL), $buf);
	}
}
