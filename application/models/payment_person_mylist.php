<?php
class Payment_Person_Mylist extends User {
	private $page;
	private $all_count;
	private $page_count;
	const LIMIT = 50;

	private $apply_lists = array();

	public function __construct($fields = array()) {
		parent::__construct();
		if (!empty($fields)) {
			foreach ($this as $key => $value) {
				if ($fields[$key] !== NULL) {
					$this->$key = $fields[$key];
				}
			}
		}
		$this->_get_list_data();
	}

	private function _get_list_data() {
		$query = 'SELECT COUNT(*) FROM (SELECT id FROM finance_payment_person_apply WHERE user='
				. $this->getUid()
				. ' UNION ALL SELECT id FROM finance_payment_person_apply_temp WHERE user='
				. $this->getUid() . ') z';
		$this->all_count = intval($this->db->get_var($query));
		$this->page_count = ceil($this->all_count / self::LIMIT);
		$start = self::LIMIT * intval($this->page) - self::LIMIT;
		if ($start < 0) {
			$start = 0;
		}

		$results = array();
		$lists = $this->db
				->get_results(
						'SELECT a.*,b.media_name FROM (SELECT id,media_info_id,payment_date,payment_amount_plan,payment_amount_real,isok,"untemp" AS ttype,addtime,payment_id FROM finance_payment_person_apply WHERE user='
								. $this->getUid()
								. ' UNION ALL SELECT id,media_info_id,payment_date,payment_amount_plan,payment_amount_real,0 AS isok,"temp" AS ttype,addtime,"" AS payment_id FROM finance_payment_person_apply_temp WHERE user='
								. $this->getUid()
								. ') a LEFT JOIN finance_payment_media_info b ON a.media_info_id=b.id LIMIT '
								. $start . ',' . self::LIMIT);
		if ($lists !== NULL) {
			foreach ($lists as $list) {
				$results[] = array('id' => $list->id,
						'media_info_id' => $list->media_info_id,
						'payment_date' => $list->payment_date,
						'payment_amount_plan' => $list->payment_amount_plan,
						'payment_amount_real' => $list->payment_amount_real,
						'isok' => $list->isok, 'ttype' => $list->ttype,
						'addtime' => $list->addtime,
						'media_name' => $list->media_name,
						'payment_id' => $list->payment_id);
			}
		}
		$this->apply_lists = $results;
		unset($results);
	}

	private static function _get_status($ttype, $isok) {
		if ($ttype === 'temp') {
			return '草稿';
		} else {
			switch (intval($isok)) {
			case -1:
				return '已作废';
			case 0:
				return '未审核';
			case 1:
				return '审核通过';
			case 2:
				return '驳回';
			case 3:
				return '发起撤销申请，等待审核';
			}
		}
		return '';
	}

	private static function _get_action($ttype, $isok, $id, $amount_real,
			$done_gd) {
		$s = '';
		if ($ttype === 'temp') {
			//草稿
			return '<a href="' . BASE_URL
					. 'finance/payment/?o=continue_payment_apply&id='
					. intval($id)
					. '">继续填写</a>&nbsp;|&nbsp;<a href="javascript:cancel(\'temp\','
					. intval($id) . ');">作废</a>';
		} else {
			if (!in_array(intval($isok), array(-1, 3), TRUE)
					&& doubleval($amount_real) !== doubleval($done_gd)) {
				return '<a href="' . BASE_URL
						. 'finance/payment/?o=edit_payment_apply&id='
						. intval($id)
						. '">变更</a>&nbsp;|&nbsp;<a href="javascript:cancel(\'untemp\','
						. intval($id) . ');">作废</a>';
			}
			return '';
		}
	}

	private function _getPersonPaymentGD() {
		$datas = array();
		$results = $this->db
				->get_results(
						'SELECT SUM(gd_amount) AS amount,apply_id FROM finance_payment_gd WHERE apply_type=1 GROUP BY apply_id');
		if ($results !== NULL) {
			foreach ($results as $result) {
				$datas[$result->apply_id] = $result->amount;
			}
		}
		return $datas;
	}

	private function get_person_apply_list_html() {
		$datas = $this->apply_lists;
		$gds = $this->_getPersonPaymentGD();
		$result = '';
		if (!empty($datas)) {
			foreach ($datas as $data) {
				$result .= '<tr><td>' . $data['payment_id'] . '</td><td>'
						. $data['addtime'] . '</td><td>' . $data['media_name']
						. '</td><td>' . $data['payment_date'] . '</td><td>'
						. Format_Util::my_money_format('%.2n',
								$data['payment_amount_plan']) . '</td><td>'
						. Format_Util::my_money_format('%.2n',
								$data['payment_amount_real']) . '</td><td>'
						. self::_get_status($data['ttype'], $data['isok'])
						. '</td><td>'
						. self::_get_action($data['ttype'], $data['isok'],
								$data['id'], $data['payment_amount_real'],
								(empty($gds[$data['id']]) ? 0
										: $gds[$data['id']])) . '</td></tr>';
			}
		}
		return $result;
	}

	public function get_payment_person_mylist_html() {
		$buf = file_get_contents(
				TEMPLATE_PATH . 'finance/payment/payment_person_mylist.tpl');
		return str_replace(
				array('[LEFT]', '[TOP]', '[PERSONAPPLYLIST]', '[ALLCOUNTS]',
						'[COUNTS]', '[NEXT]', '[PREV]', '[VCODE]',
						'[BASE_URL]'),
				array($this->get_left_html(), $this->get_top_html(),
						$this->get_person_apply_list_html(), $this->all_count,
						$this->get_apply_counts(), $this->getNext(),
						$this->getPrev(), $this->get_vcode(), BASE_URL), $buf);
	}

	private function get_apply_counts() {
		return $this->page . '	/' . $this->page_count . ' 页 &nbsp;&nbsp;';
	}

	private function _get_pagination($is_prev) {
		return '<a href="' . BASE_URL
				. 'finance/payment/?o=payment_apply_mylist&page='
				. ($is_prev ? intval($this->page) - 1 : intval($this->page) + 1)
				. '">' . ($is_prev ? '上一页' : '下一页') . '</a>';
	}

	public function getPrev() {
		if (intval($this->page) === 1) {
			return '';
		} else {
			return $this->_get_pagination(TRUE);
		}
	}

	public function getNext() {
		if (intval($this->page) >= intval($this->page_count)) {
			return '';
		} else {
			return $this->_get_pagination(FALSE);
		}
	}
}
