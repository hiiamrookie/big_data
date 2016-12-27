<?php
class Deposit_Ajax extends User {
	private $q;
	private $billtype;

	public function __construct($fields) {
		parent::__construct();
		foreach ($this as $key => $value) {
			if ($fields[$key] !== NULL) {
				$this->$key = $fields[$key];
			}
		}
	}

	public function get_deposit_names() {
		$s = '';
		$query = 'SELECT cid FROM finance_deposit WHERE 1=1';
		if ($this->q !== '') {
			$query .= ' AND cid LIKE "%' . $this->q . '%"';
		}
		$query .= ' AND (isok=0 OR isok=1)';
		$results = $this->db->get_results($query);
		if ($results !== NULL) {
			foreach ($results as $result) {
				$s .= $result->cid . "\n";
			}
		}
		return $s;
	}

	private function _get_deposit_invoice_amount($cid) {
		$amount = $this->db
				->get_var(
						'SELECT SUM(amount) FROM finance_deposit_invoice WHERE cid="'
								. $cid . '" AND isok=1');
		if ($amount === NULL) {
			return 0;
		}
		return $amount;
	}

	public function search_deposit() {
		$s = '';
		$row = $this->db
				->get_row(
						'SELECT a.*,b.billtype FROM finance_deposit a LEFT JOIN contract_cus b ON a.cid=b.cid WHERE a.cid="'
								. $this->q . '"');
		if ($row !== NULL) {
			$billtype = $row->billtype;
			if (intval($billtype) !== intval($this->billtype)) {
				$s = '1';
			} else {
				$deposit_invoice_amount = $this
						->_get_deposit_invoice_amount($this->q);
				$s .= '<div><img src="' . BASE_URL
						. 'images/close.png" onclick="removepid(this,\''
						. $this->q
						. '\')" width="12" height="12" />
					  	&nbsp;<span id="pid" style="display:inline-block;width:90px;text-align:left">'
						. $this->q . '</span>
					  	<span title="' . $row->cusname
						. '" style="display:inline-block;width:140px;text-align:left">'
						. String_Util::cut_str($row->cusname, 10, 0, 'UTF-8',
								'...')
						. '</span><input type="hidden" name="cusname_'
						. $this->q . '" value="' . $row->cusname
						. '">
					  	&nbsp;&nbsp;<font color="#ff9933">已开票: </font>
					  	<span style="display:inline-block;width:80px;"><font color="#ff9933"><b>'
						. Format_Util::my_money_format('%.2n',
								$deposit_invoice_amount)
						. '</b></font></span>
					  	&nbsp;&nbsp;&nbsp;&nbsp;<font color="blue">未开票: </font>
					  	<input type="text" class="validate[required,custom[invoiceMoney],max['
						. round($row->amount - $deposit_invoice_amount, 2)
						. ']] text" style="width:80px;text-align:right " name="amount_'
						. $this->q . '" id="amount_' . $this->q
						. '" onblur="getallamount();" value="'
						. round($row->amount - $deposit_invoice_amount, 2)
						. '" /><input type="hidden" name="oldamount_'
						. $this->q . '" id="oldamount_' . $this->q
						. '" value="'
						. round($row->amount - $deposit_invoice_amount, 2)
						. '"></div>';
			}
		} else {
			$s = '0';
		}
		return $s;
	}
}
