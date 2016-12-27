<?php
class Finance_Settle_Account extends User {
	private $has_finance_settle_account_permission = FALSE;

	public function __construct() {
		parent::__construct();
		if (in_array($this->getUsername(),
				$GLOBALS['manager_finance_permission'], TRUE)
				|| intval($this->getBelong_dep()) === 2) {
			$this->has_finance_settle_account_permission = TRUE;
		}
	}

	public function getIndexHtml() {
		if ($this->has_finance_settle_account_permission) {
			$settle_account = $this->db
					->get_row(
							'SELECT a.settle_account_date,b.username,b.realname FROM (SELECT settle_account_date,uid FROM finance_settle_account ORDER BY settle_account_date DESC LIMIT 1) a LEFT JOIN users b ON a.uid=b.uid');
			$dt = '无';
			$user = '无';
			if ($settle_account !== NULL) {
				$dt = $settle_account->settle_account_date;
				$user = $settle_account->realname . ' （'
						. $settle_account->username . '）';
			}
			return str_replace(
					array('[LEFT]', '[TOP]', '[VCODE]', '[SETTLEACCOUNTDATE]','[SAUSER]',
							'[BASE_URL]'),
					array($this->get_left_html(), $this->get_top_html(),
							$this->get_vcode(), $dt, $user,BASE_URL),
					file_get_contents(
							TEMPLATE_PATH
									. 'finance/finance_settle_account.tpl'));
		} else {
			User::no_permission();
		}
	}

	public function getRSettleAccountResult() {
		if ($this->has_finance_settle_account_permission) {
			$result = $this->db
					->query(
							'INSERT INTO finance_settle_account(settle_account_date,uid) VALUES(now(),'
									. $this->getUid() . ')');
			return array('status' => $result !== FALSE ? 'success' : 'error',
					'message' => $result !== FALSE ? '结帐日期设置成功' : '结帐日期设置失败');
		}
		return array('status' => 'error', 'message' => NO_RIGHT_TO_DO_THIS);
	}
}
