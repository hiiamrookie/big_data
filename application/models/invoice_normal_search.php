<?php
class Invoice_Normal_Search extends User {
	private $starttime;
	private $endtime;
	private $search;
	/**
	 * @return the $starttime
	 */
	public function getStarttime() {
		return $this->starttime;
	}

	/**
	 * @return the $endtime
	 */
	public function getEndtime() {
		return $this->endtime;
	}

	public function __construct($starttime = NULL, $endtime = NULL,
			$search = NULL) {
		parent::__construct();
		$this->starttime = $starttime;
		$this->endtime = $endtime;
		$this->search = $search;
	}

	public function _get_list_html() {
		$s = '';
		if ($this->getHas_invoice_search_permission()) {
			$invoice_array = $this->getInvoice_array();

			$citys = City::getInstance();
			$deps = Dep::getInstance();
			$dep = array();
			foreach ($deps as $key => $value) {
				$dep[$key] = $value[0];
			}

			$querys = array();
			$basic_query = 'SELECT a.*,FROM_UNIXTIME(a.time) AS tt,b.username,b.realname FROM finance_invoice_list a LEFT JOIN users b ON a.user=b.uid WHERE 1=1';
			foreach ($invoice_array as $key => $value) {
				$value = str_replace(array('（', '）', '(', ')'),
						array('', '', '', ''), $value);
				$value = explode(' ', $value);
				$query = $basic_query;
				if (count($value) === 2) {
					//查询分公司
					if (array_search($value[1], $citys) !== FALSE) {
						$query .= ' AND b.city='
								. intval(array_search($value[1], $citys));
					}
				} else if (count($value) === 3) {
					//查询某部门
					if (array_search($value[2], $dep) !== FALSE) {
						$query .= ' AND b.dep='
								. intval(array_search($value[2], $dep));
					}
				}

				if (strtotime($this->starttime) !== FALSE) {
					$query .= ' AND a.time>='
							. strtotime($this->starttime . ' 00:00:00');
				}
				if (strtotime($this->endtime) !== FALSE) {
					$query .= ' AND a.time<='
							. strtotime($this->endtime . ' 23:59:59');
				}
				if ($this->search !== NULL && $this->search !== '') {
					$query .= ' AND (a.title LIKE "%' . $this->search
							. '%" OR a.number LIKE "%' . $this->search . '%" OR a.remark LIKE "%' . $this->search . '%")';
				}
				$querys[] = $query;
			}
			$querys = implode(' UNION ', $querys) . ' ORDER BY a.time DESC';
			$results = $this->db->get_results($querys);
			if ($results !== NULL) {
				foreach ($results as $key => $result) {
					$s .= '<tr><td><input type="checkbox" class="validate[minCheckbox[1]]" name="selinvoice[]" value="'
							. $result->id
							. '" onclick="javascript:check_select_all(this);"></td><td>'
							. $result->tt . '</td><td>' . $result->title
							. '</td><td>' . $result->remark . '</td><td><font color="#ff9933"><b>'
							. Format_Util::my_money_format('%.2n',
									$result->amount) . '</b></font></td><td>'
							. Invoice_List::get_invoice_type(
									intval($result->type)) . '</td><td>'
							. $result->company . '</td><td>'
							. $result->realname . ' (' . $result->username
							. ')' . '</td><td>'
							. Invoice_List::_get_invoice_status(
									intval($result->isok),
									intval($result->print),
									intval($result->step)) . '</td><td>'
							. self::_get_invoice_action(intval($result->id),
									intval($result->isok),
									intval($result->step),
									intval($this->getBelong_dep()))
							. '</td></tr>';
				}
			} else {
				$s = '<tr><td colspan="9"><font color="red">当前没有相应开票记录！</font></td></tr>';
			}
		}
		return $s;
	}

	private static function _get_invoice_action($id, $isok, $step, $belongdep) {
		$s = '<a href="' . BASE_URL . 'finance/invoice/?o=normalview&id=' . $id
				. '">查看</a>';
		if ($isok === 1 || ($step === 2 && $belongdep === 8)) {
			$s .= '&nbsp;<a href="' . BASE_URL
					. 'finance/invoice/?o=normalprint&id=' . $id
					. '" target="_blank">打印</a>';
		}
		return $s;
	}

	public function get_invoice_search_html() {
		if ($this->getHas_invoice_search_permission()) {
			$buf = file_get_contents(
					TEMPLATE_PATH . 'finance/invoice/invoice_normal_search.tpl');
			return str_replace(
					array('[LEFT]', '[TOP]', '[VCODE]', '[INVOICELIST]',
							'[STARTTIME]', '[ENDTIME]', '[SEARCH]',
							'[BASE_URL]'),
					array($this->get_left_html(), $this->get_top_html(),
							$this->get_vcode(), $this->_get_list_html(),
							$this->starttime, $this->endtime, $this->search,
							BASE_URL), $buf);
		} else {
			return User::no_permission();
		}
	}
}
