<?php
class Deposit_Receivables_Search extends User {
	private $starttime;
	private $endtime;
	private $search;
	private $has_deposit_receivables_permission = FALSE;

	public function __construct($starttime = NULL, $endtime = NULL,
			$search = NULL) {
		parent::__construct();
		$this->starttime = $starttime;
		$this->endtime = $endtime;
		$this->search = $search;
		if (in_array($this->getUsername(),
				$GLOBALS['manager_finance_permission'], TRUE)
				|| intval($this->getBelong_dep()) === 2) {
			$this->has_deposit_receivables_permission = TRUE;
		}
	}

	private function _get_list_html() {
		$s = '';
		if ($this->has_deposit_receivables_permission) {
			$query = 'SELECT a.id,a.cid,FROM_UNIXTIME(b.time) AS tt,c.cusname,a.amount,b.payer,b.date,a.isok,d.username,d.realname FROM  finance_deposit_receivables a LEFT JOIN finance_deposit_receivables_list b ON a.receivables_list=b.id LEFT JOIN contract_cus  c ON a.cid=c.cid LEFT JOIN users d ON a.user=d.uid WHERE 1=1';
			if (strtotime($this->starttime) !== FALSE) {
				$query .= ' AND b.date>="' . $this->starttime . '"';
			}
			if (strtotime($this->endtime) !== FALSE) {
				$query .= ' AND b.date<="' . $this->endtime . '"';
			}
			if ($this->search !== NULL && $this->search !== '') {
				$query .= ' AND (a.cid LIKE "%' . $this->search
						. '%" OR b.payer LIKE "%' . $this->search
						. '%" OR c.cusname LIKE "%' . $this->search . '%")';
			}

			$results = $this->db->get_results($query);
			if ($results !== NULL) {
				foreach ($results as $key => $result) {
					$s .= '<tr><td><input type="checkbox" class="validate[minCheckbox[1]]" name="selinvoice[]" value="'
							. $result->id
							. '" onclick="javascript:check_select_all(this);"</td><td>'
							. $result->date . '</td><td>' . $result->cid
							. '</td><td>' . $result->cusname
							. '</td><td><font color="#ff9933"><b>'
							. Format_Util::my_money_format('%.2n',
									$result->amount) . '</b></font></td><td>'
							. $result->payer . '</td><td>' . $result->realname
							. ' (' . $result->username . ')' . '</td><td>'
							. self::_get_status(intval($result->isok))
							. '</td></tr>';
				}
			} else {
				$s = '<tr><td colspan="7"><font color="red">当前没有相应收款记录！</font></td></tr>';
			}
		}
		return $s;
	}

	private static function _get_status($isok) {
		if ($isok === -1) {
			return '<font color="#FF0000"><b>撤销</b></font>';
		} else {
			return '<font color="#00CC00"><b>生效</b></font>';
		}
	}

	public function get_deposit_receivables_search_html() {
		if ($this->has_deposit_receivables_permission) {
			$buf = file_get_contents(
					TEMPLATE_PATH
							. 'finance/deposit/deposit_receivables_search.tpl');
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
