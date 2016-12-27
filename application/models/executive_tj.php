<?php
class Executive_Tj extends User {
	private $month;
	private $datas = array();

	public function __construct($month = NULL) {
		parent::__construct();
		if (strtotime($month) === FALSE) {
			$month = date('Y-m');
		}
		$this->month = $month;
		$results = $this->db
				->get_results(
						'SELECT FROM_UNIXTIME(time,"%Y-%m-%d") AS exedate,COUNT(*) AS execount FROM executive WHERE FROM_UNIXTIME(time,"%Y-%m") = "'
								. $this->month
								. '" GROUP BY FROM_UNIXTIME(time,"%Y-%m-%d")');
		$datas = array();
		if ($results !== NULL) {
			foreach ($results as $result) {
				$datas[] = array('date' => $result->exedate,
						'count' => $result->execount);
			}
		}
		$this->datas = $datas;
	}

	private static function _get_executive_tj_show($date) {
		$w = date('w', strtotime($date));
		switch ($w) {
		case '0':
			$w = '天';
			break;
		case '1':
			$w = '一';
			break;
		case '2':
			$w = '二';
			break;
		case '3':
			$w = '三';
			break;
		case '4':
			$w = '四';
			break;
		case '5':
			$w = '五';
			break;
		case '6':
			$w = '六';
			break;
		}

		return sprintf('%s 星期%s', $date, $w);
	}

	private function _get_tj_html() {
		$datas = $this->datas;
		$s = '';
		if (!empty($datas)) {
			foreach ($datas as $data) {
				$s .= '<tr><td>' . self::_get_executive_tj_show($data['date'])
						. '</td><td>' . $data['count']
						. '</td><td>&nbsp;</td></tr>';
			}
		}
		return $s;
	}

	private function _get_tj_allcount() {
		$datas = $this->datas;
		$s = 0;
		if (!empty($datas)) {
			foreach ($datas as $data) {
				$s += intval($data['count']);
			}
		}
		return $s;
	}

	public function get_executive_tj_html() {
		if ($this->getHas_manager_executive_permission()) {
			$buf = file_get_contents(
					TEMPLATE_PATH . 'executive/executive_tj.tpl');
			return str_replace(
					array('[LEFT]', '[TOP]', '[VCODE]', '[MONTH]',
							'[EXECUTIVETJ]', '[ALLCOUNTS]', '[BASE_URL]'),
					array($this->get_left_html(), $this->get_top_html(),
							$this->get_vcode(), $this->month,
							$this->_get_tj_html(), $this->_get_tj_allcount(),
							BASE_URL), $buf);
		} else {
			return User::no_permission();
		}
	}
}