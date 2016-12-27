<?php
class Finance_Receivables_Normal_Search extends User {
	private $starttime;
	private $endtime;
	private $search;

	public function __construct($starttime = NULL, $endtime = NULL,
			$search = NULL) {
		parent::__construct();
		$this->starttime = $starttime;
		$this->endtime = $endtime;
		$this->search = $search;
	}

	private function _get_list_html() {
		$s = '';
		if ($this->getHas_receivables_search_permission()) {
			$receivables_array = $this->getReceivables_array();

			$citys = City::getInstance();
			$deps = Dep::getInstance();
			$dep = array();
			foreach ($deps as $key => $value) {
				$dep[$key] = $value[0];
			}

			$querys = array();
			$basic_query = 'SELECT a.id,a.pid,FROM_UNIXTIME(b.time) AS tt,a.amount,b.payer,b.date,d.cusname FROM finance_receivables a LEFT JOIN finance_receivables_list b ON a.receivables_list=b.id LEFT JOIN(SELECT pid,city,dep,cid FROM executive GROUP BY pid) c ON a.pid=c.pid LEFT JOIN contract_cus d ON c.cid=d.cid WHERE 1=1';
			foreach ($receivables_array as $key => $value) {
				$value = str_replace(array('（', '）', '(', ')'),
						array('', '', '', ''), $value);
				$value = explode(' ', $value);
				$query = $basic_query;
				if (count($value) === 2) {
					//查询分公司
					if (array_search($value[1], $citys) !== FALSE) {
						$query .= ' AND c.city='
								. intval(array_search($value[1], $citys));
					}
				} else if (count($value) === 3) {
					//查询某部门
					if (array_search($value[2], $dep) !== FALSE) {
						$query .= ' AND c.dep='
								. intval(array_search($value[2], $dep));
					}
				}

				if (strtotime($this->starttime) !== FALSE) {
					$query .= ' AND b.date>="'
							. $this->starttime . '"';
				}
				if (strtotime($this->endtime) !== FALSE) {
					$query .= ' AND b.date<="'
							. $this->endtime . '"';
				}
				if ($this->search !== NULL && $this->search !== '') {
					$query .= ' AND (a.pid LIKE "%' . $this->search
							. '%" OR b.payer LIKE "%' . $this->search
							. '%" OR c.cusname LIKE "%' . $this->search . '%")';
				}
				$querys[] = $query;
			}

			$querys = implode(' UNION ', $querys);
			$results = $this->db->get_results($querys);
			if ($results !== NULL) {
				foreach ($results as $key => $result) {
					$s .= '<tr><td><input type="checkbox" class="validate[minCheckbox[1]]" name="selinvoice[]" value="'
							. $result->id
							. '" onclick="javascript:check_select_all(this);"</td><td>'
							. $result->date . '</td><td>' . $result->pid
							. '</td><td>' . $result->cusname
							. '</td><td><font color="#ff9933"><b>'
							. Format_Util::my_money_format('%.2n',
									$result->amount) . '</b></font></td><td>'
							. $result->payer . '</td></tr>';
				}
			} else {
				$s = '<tr><td colspan="6"><font color="red">当前没有相应收款记录！</font></td></tr>';
			}
		}
		return $s;
	}

	public function get_receivables_normal_search_html() {
		if ($this->getHas_receivables_search_permission()) {
			$buf = file_get_contents(
					TEMPLATE_PATH
							. 'finance/receivables/finance_receivables_normal_search.tpl');
			return str_replace(
					array('[LEFT]', '[TOP]', '[VCODE]', '[RECEIVABLESLIST]',
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