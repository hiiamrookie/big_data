<?php
class Deposit_Invoice_Search extends User {
	private $starttime;
	private $endtime;
	private $search;
	private $page;
	private $has_invoice_permission = FALSE;

	public function __construct($starttime = NULL, $endtime = NULL,
			$search = NULL) {
		parent::__construct();
		$this->starttime = $starttime;
		$this->endtime = $endtime;
		$this->search = $search;
		if (in_array($this->getUsername(),
				$GLOBALS['manager_finance_permission'], TRUE)
				|| intval($this->getBelong_dep()) === 2) {
			$this->has_invoice_permission = TRUE;
		}
	}

	public function _get_list_html() {
		$s = '';
		if ($this->has_invoice_permission) {
			$query = 'SELECT a.*,FROM_UNIXTIME(a.gdtime) AS tt,b.username,b.realname,c.username AS gbusername,c.realname AS gdrealname FROM finance_deposit_invoice_list a LEFT JOIN users b ON a.user=b.uid LEFT JOIN users c ON a.gduser=c.uid WHERE a.isok=1';
			if (strtotime($this->starttime) !== FALSE) {
				$query .= ' AND a.date>="' . $this->starttime . '"';
			}
			if (strtotime($this->endtime) !== FALSE) {
				$query .= ' AND a.date<="' . $this->endtime . '"';
			}
			if ($this->search !== NULL && $this->search !== '') {
				$query .= ' AND (a.title LIKE "%' . $this->search
						. '%" OR a.number LIKE "%' . $this->search . '%")';
			}
			$query .= ' ORDER BY a.date DESC';
			$results = $this->db->get_results($query);
			if ($results !== NULL) {
				foreach ($results as $key => $result) {
					$s .= '<tr><td><input type="checkbox" class="validate[minCheckbox[1]]" name="selinvoice[]" value="'
							. $result->id
							. '" onclick="javascript:check_select_all(this);"></td><td>'
							. $result->date . '</td><td>' . $result->title
							. '</td><td><font color="#ff9933"><b>'
							. Format_Util::my_money_format('%.2n',
									$result->amount) . '</b></font></td><td>'
							. $result->number . '</td><td>'
							. Deposit_Invoice_List::get_deposit_invoice_type(
									intval($result->type)) . '</td><td>'
							. $result->realname . ' (' . $result->username
							. ')' . '</td><td>'
							. ($result->gbusername !== NULL
									&& $result->gdrealname !== NULL ? ($result
											->gdrealname . ' ('
											. $result->gbusername . ')') : '')
							. '</td><td>'
							. Deposit_Invoice_List::get_invoice_action(
									intval($result->id), 0,
									intval($result->print),
									intval($result->isok)) . '</td></tr>';
				}
			} else {
				$s = '<tr><td colspan="7"><font color="red">当前没有相应开票记录！</font></td></tr>';
			}
		}
		return $s;
	}

	public function get_deposit_invoice_search_html() {
		if ($this->has_invoice_permission) {
			$buf = file_get_contents(
					TEMPLATE_PATH
							. 'finance/deposit/deposit_invoice_search.tpl');
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
