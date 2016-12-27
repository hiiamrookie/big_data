<?php
class Invoice_Ajax extends User {
	private $billtype;
	private $search;
	private $invoice_ajax_permission = FALSE;
	private $finance_permission = FALSE;

	/**
	 * @return the $invoice_ajax_permission
	 */
	public function getInvoice_ajax_permission() {
		return $this->invoice_ajax_permission;
	}

	public function __construct($billtype, $search) {
		parent::__construct();
		$this->billtype = $billtype;
		$this->search = $search;
		$this->finance_permission = in_array($this->getUsername(),
				$GLOBALS['manager_finance_permission'], TRUE)
				|| intval($this->getBelong_dep()) === 2;
	}

	private function _get_invoice_amount($pid) {
		$sum = new Invoice();
		$amount = $sum->getSumPidInvoice($pid);
		unset($sum);
		return $amount;
	}

	public function search_invoice_executive() {
		$s = '';
		if ($this->finance_permission
				|| $this->getHas_check_executive_permission()) {
			$row = $this->db
					->get_row(
							'SELECT a.name,a.company,a.amount,a.cid,b.billtype FROM executive a LEFT JOIN contract_cus b ON a.cid=b.cid WHERE a.pid="'
									. $this->search
									. '" AND a.isok<>-1 ORDER BY a.id DESC LIMIT 1');
		} else {
			$res = $this
					->get_relation_executive_permission(
							intval($this->getBelong_city()),
							intval($this->getBelong_dep()),
							intval($this->getBelong_team()));
			if ($res > 0) {
				$query = 'SELECT a.name,a.company,a.amount,a.cid,b.billtype FROM executive a LEFT JOIN contract_cus b ON a.cid=b.cid WHERE a.pid="'
						. $this->search . '" AND a.isok<>-1 ';
				if (intval($this->getBelong_city()) !== 0 && $res == 1) {
					$query .= ' AND a.city='
							. intval($this->getBelong_city());
				} else if (intval($this->getBelong_city()) !== 0
						&& intval($this->getBelong_dep()) !== 0 && $res == 2) {
					$query .= ' AND a.dep='
							. intval($this->getBelong_dep());
				} else if (intval($this->getBelong_city()) !== 0
						&& intval($this->getBelong_dep()) !== 0
						&& intval($this->getBelong_team()) !== 0 && $res === 3) {
					$query .= ' AND a.team='
							. intval($this->getBelong_team());
				}
				$query .= ' ORDER BY a.id DESC LIMIT 1';
				$row = $this->db->get_row($query);
			} else {
				$row = $this->db
						->get_row(
								'SELECT a.name,a.company,a.amount,a.cid,b.billtype FROM executive a LEFT JOIN contract_cus b ON a.cid=b.cid WHERE a.pid="'
										. $this->search . '" AND a.user='
										. intval($this->getUid())
										. ' AND a.isok<>-1 ORDER BY a.id DESC LIMIT 1');
			}
		}

		if ($row !== NULL) {
			$billtype = $row->billtype;
			if (intval($billtype) !== intval($this->billtype)) {
				$s = '1';
			} else {
				$invoice_amount = $this->_get_invoice_amount($this->search);
				$s .= '<div><img src="' . BASE_URL
						. 'images/close.png" onclick="removepid(this,\''
						. $this->search
						. '\')" width="12" height="12" />
					  	&nbsp;<span id="pid" style="display:inline-block;width:90px;text-align:left">'
						. $this->search . '</span>
					  	<span title="' . $row->name
						. '" style="display:inline-block;width:140px;text-align:left">'
						. String_Util::cut_str($row->name, 10, 0, 'UTF-8',
								'...')
						. '</span>
					  	<span id="company" style="display:inline-block;width:80px;text-align:center">【'
						. Executive::get_companyname(intval($row->company))
						. '】</span>
					  	&nbsp;&nbsp;<font color="#ff9933">已开票: </font>
					  	<span style="display:inline-block;width:80px;"><font color="#ff9933"><b>'
						. Format_Util::my_money_format('%.2n', $invoice_amount)
						. '</b></font></span>
					  	&nbsp;&nbsp;&nbsp;&nbsp;<font color="blue">未开票: </font>
					  	<input type="hidden" name="company_' . $this->search
						. '" value="' . $row->company
						. '"/><input type="text" class="validate[required,custom[invoiceMoney],max['
						. round($row->amount - $invoice_amount, 2)
						. ']] text" style="width:80px;text-align:right " name="amount_'
						. $this->search . '" id="amount_' . $this->search
						. '" onblur="getallamount();" value="'
						. round($row->amount - $invoice_amount, 2)
						. '" /><input type="hidden" name="oldamount_'
						. $this->search . '" id="oldamount_' . $this->search
						. '" value="' . round($row->amount - $invoice_amount, 2)
						. '">&nbsp;<span id="span_' . $this->search
						. '"></span></div>';
			}
		} else {
			$s = '0';
		}

		return $s;
	}
}