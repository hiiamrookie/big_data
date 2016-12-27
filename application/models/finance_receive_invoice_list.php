<?php
class Finance_Receive_Invoice_List extends User {
	private $starttime;
	private $endtime;
	private $search;
	private $page;
	private $all_count;
	private $page_count;
	private $datas = array();
	private $has_finance_receive_invoice_permission = FALSE;

	const LIMIT = 50;

	public function __construct($fields = array()) {
		parent::__construct();
		if (!empty($fields)) {
			foreach ($this as $key => $value) {
				if ($fields[$key] !== NULL
						&& !in_array($key,
								array('has_finance_receive_invoice_permission'),
								TRUE)) {
					$this->$key = $fields[$key];
				}
			}
		}
		if (in_array($this->getUsername(),
				$GLOBALS['manager_finance_permission'], TRUE)
				|| intval($this->getBelong_dep()) === 2) {
			$this->has_finance_receive_invoice_permission = TRUE;
		}

	}

	private static function _get_action($id, $tmpid,$shared) {
		//var_dump($shared);
		$s = '';
		if(in_array($id, $shared,TRUE)){
			$s = '<font color="red"><b>已分配</b></font>&nbsp;|&nbsp;';
		}
		$s .= '<a href="' . BASE_URL
				. 'finance/receiveinvoice/?o=receiveinvoiceedit&id=' . $id
				. '">修改</a>';
		if ($tmpid !== NULL) {
			$s .= '&nbsp;|&nbsp;<a href="' . BASE_URL
					. 'finance/receiveinvoice/?o=receiveinvoicefix&id=' . $id
					. '">判断</a>';
		}
		return $s;
	}

	private static function _show_invoice_number($invoice_number, $tmpid) {
		if ($tmpid === NULL) {
			return $invoice_number;
		} else {
			return $invoice_number
					. '&nbsp;<font color="red"><b>有差异</b></font>';
		}
	}

	private function _isReceiveInvoiceShared(){
		$done = array();
		$results = $this->db->get_results('SELECT source_ids FROM finance_receiveinvoice_source_pid WHERE isok=1');
		if($results !== NULL){
			foreach ($results as $result){
				$ids = explode('^', $result->source_ids);
				foreach ($ids as $id){
					if(!empty($id)){
						if(!in_array($id, $done,TRUE)){
							$done[] = intval($id);
						}
					}
				}
			}
		}
		return $done;
	}
	
	private function _get_list_html() {
		$where_sql = array();
		if (strtotime($this->starttime) !== FALSE) {
			$where_sql[] = ' a.invoice_date>="' . $this->starttime . '" ';
		}
		if (strtotime($this->endtime) !== FALSE) {
			$where_sql[] = ' a.invoice_date<="' . $this->endtime . '" ';
		}
		if (self::validate_field_not_null($this->search)
				&& self::validate_field_not_empty($this->search)) {
			$where_sql[] = ' (a.media_name LIKE "%' . $this->search
					. '%" OR a.invoice_number LIKE "%' . $this->search . '%") ';
		}

		$query = 'SELECT COUNT(*) FROM finance_receiveinvoice_source a WHERE 1=1 ';
		if (!empty($where_sql)) {
			$query .= ' AND ' . implode(' AND ', $where_sql);
		}

		$this->all_count = intval($this->db->get_var($query));
		$this->page_count = ceil($this->all_count / self::LIMIT);
		$start = self::LIMIT * intval($this->page) - self::LIMIT;
		if ($start < 0) {
			$start = 0;
		}

		$results = array();
		$sql = 'SELECT a.id,a.media_name,a.invoice_number,a.invoice_content,a.tax_rate,a.amount,a.tax,a.sum_amount,a.invoice_date,b.id AS tmpid FROM finance_receiveinvoice_source a LEFT JOIN finance_receiveinvoice_source_temp b ON a.invoice_number=b.invoice_number AND a.sum_amount=b.sum_amount WHERE 1=1 ';
		if (!empty($where_sql)) {
			$sql .= ' AND ' . implode(' AND ', $where_sql);
		}
		$sql .= ' ORDER BY a.invoice_date DESC LIMIT ' . $start . ','
				. self::LIMIT;
		$invoice_source = $this->db->get_results($sql);
		if ($invoice_source !== NULL) {
			foreach ($invoice_source as $ins) {
				$results[] = array('id' => $ins->id,
						'media_name' => $ins->media_name,
						'invoice_number' => $ins->invoice_number,
						'invoice_content' => $ins->invoice_content,
						'tax_rate' => $ins->tax_rate, 'amount' => $ins->amount,
						'tax' => $ins->tax, 'sum_amount' => $ins->sum_amount,
						'invoice_date' => $ins->invoice_date,
						'tmpid' => $ins->tmpid);
			}
		}
		$this->datas = $results;
		unset($results);

		$datas = $this->datas;
		$s = '';
		if (!empty($datas)) {
			$dones = $this->_isReceiveInvoiceShared();
			
			foreach ($datas as $key => $data) {
				$s .= '<tr><td><input type="checkbox" name="sourceselect" value="'
						. $data['id'] . '"></td><td>' . $data['media_name']
						. '</td><td>'
						. self::_show_invoice_number($data['invoice_number'],
								$data['tmpid']) . '</td><td>'
						. $data['invoice_content'] . '</td><td>'
						. $data['tax_rate'] . '</td><td>'
						. Format_Util::my_money_format('%.2n', $data['amount'])
						. '</td><td>'
						. Format_Util::my_money_format('%.2n', $data['tax'])
						. '</td><td>'
						. Format_Util::my_money_format('%.2n',
								$data['sum_amount']) . '</td><td>'
						. $data['invoice_date'] . '</td><td>'
						. self::_get_action(intval($data['id']), $data['tmpid'],$dones)
						. '</td></tr>';
			}
		} else {
			$s = '<tr><td colspan="9"><font color="red">没有符合条件的数据!</font></td></tr>';
		}
		unset($datas);
		return $s;
	}

	private function _get_invoice_counts() {
		return $this->page . '	/' . $this->page_count . ' 页 &nbsp;&nbsp;';
	}

	private function _get_pagination($is_prev, $action = 'receiveinvoicelist') {
		if ($action === 'receiveinvoicelist') {
			return '<a href="' . BASE_URL
					. 'finance/receiveinvoice/?o=receiveinvoicelist&starttime='
					. $this->starttime . '&endtime=' . $this->endtime
					. '&search=' . $this->search . '&page='
					. ($is_prev ? intval($this->page) - 1
							: intval($this->page) + 1) . '">'
					. ($is_prev ? '上一页' : '下一页') . '</a>';
		} else {
			return '<a href="' . BASE_URL
					. 'finance/receiveinvoice/?o=$action&page='
					. ($is_prev ? intval($this->page) - 1
							: intval($this->page) + 1) . '">'
					. ($is_prev ? '上一页' : '下一页') . '</a>';
		}
	}

	private function _getNext($action = 'receiveinvoicelist') {
		if (intval($this->page) >= intval($this->page_count)) {
			return '';
		} else {
			return $this->_get_pagination(FALSE, $action);
		}
	}

	private function _getPrev($action = 'receiveinvoicelist') {
		if (intval($this->page) === 1) {
			return '';
		} else {
			return $this->_get_pagination(TRUE, $action);
		}
	}

	public function get_receive_invoice_list_html() {
		if ($this->has_finance_receive_invoice_permission) {
			$buf = file_get_contents(
					TEMPLATE_PATH
							. 'finance/receiveinvoice/finance_receive_invoice_list.tpl');
			return str_replace(
					array('[LEFT]', '[TOP]', '[VCODE]', '[RECEIVEINVOICELIST]',
							'[ALLCOUNTS]', '[COUNTS]', '[NEXT]', '[PREV]',
							'[STARTTIME]', '[ENDTIME]', '[SEARCH]',
							'[BASE_URL]'),
					array($this->get_left_html(), $this->get_top_html(),
							$this->get_vcode(), $this->_get_list_html(),
							$this->all_count, $this->_get_invoice_counts(),
							$this->_getNext(), $this->_getPrev(),
							$this->starttime, $this->endtime, $this->search,
							BASE_URL), $buf);
		} else {
			return User::no_permission();
		}
	}

	private function _get_pid_share_list_html() {
		$this->all_count = intval(
				$this->db
						->get_var(
								'SELECT COUNT(*) FROM finance_receiveinvoice_source_pid WHERE sharetype=1 AND isok=1'));
		$this->page_count = ceil($this->all_count / self::LIMIT);
		$start = self::LIMIT * intval($this->page) - self::LIMIT;
		if ($start < 0) {
			$start = 0;
		}

		$results = array();
		$s = '';
		$results = $this->db
				->get_results(
						'SELECT * FROM finance_receiveinvoice_source_pid WHERE sharetype=1 AND isok=1');
		if ($results !== NULL) {
			foreach ($results as $result) {
				$ids = array();
				$pid_list_ids = explode('^', $result->pid_list_ids);
				foreach ($pid_list_ids as $pid_list_id) {
					if (!empty($pid_list_id)) {
						$ids[] = $pid_list_id;
					}
				}

				$row = $this->db
						->get_row(
								'SELECT SUM(amount) AS amount,SUM(tax) AS tax,SUM(sum_amount) AS sum_amount FROM finance_receiveinvoice_pid_list WHERE id IN ('
										. implode(',', $ids) . ')');
				$s .= '<tr><td>' . $result->addtime . '</td><td>'
						. (!empty($row->amount) ? $row->amount : 0)
						. '</td><td>' . (!empty($row->tax) ? $row->tax : 0)
						. '</td><td>'
						. (!empty($row->sum_amount) ? $row->sum_amount : 0)
						. '</td><td><a href="' . BASE_URL
						. 'finance/receiveinvoice/?o=pidshareedit&id='
						. $result->id . '">修改</a></td></tr>';
			}
		} else {
			$s .= '<tr><td colspan="5"><font color="red">没有符合条件的数据!</font></td></tr>';
		}
		return $s;
	}

	public function get_receive_invoice_pid_share_list_html() {
		if ($this->has_finance_receive_invoice_permission) {
			$buf = file_get_contents(
					TEMPLATE_PATH
							. 'finance/receiveinvoice/finance_receive_invoice_pid_share_list.tpl');
			return str_replace(
					array('[LEFT]', '[TOP]', '[VCODE]', '[RECEIVEINVOICELIST]',
							'[ALLCOUNTS]', '[COUNTS]', '[NEXT]', '[PREV]',
							'[BASE_URL]'),
					array($this->get_left_html(), $this->get_top_html(),
							$this->get_vcode(),
							$this->_get_pid_share_list_html(),
							$this->all_count, $this->_get_invoice_counts(),
							$this->_getNext('pidsharelist'),
							$this->_getPrev('pidsharelist'), BASE_URL), $buf);
		}
		return User::no_permission();
	}

	private function _get_payment_share_list_html() {
		$this->all_count = intval(
				$this->db
						->get_var(
								'SELECT COUNT(*) FROM finance_receiveinvoice_source_pid WHERE sharetype=2 AND isok=1'));
		$this->page_count = ceil($this->all_count / self::LIMIT);
		$start = self::LIMIT * intval($this->page) - self::LIMIT;
		if ($start < 0) {
			$start = 0;
		}

		$results = array();
		$s = '';
		$results = $this->db
				->get_results(
						'SELECT * FROM finance_receiveinvoice_source_pid WHERE sharetype=2 AND isok=1');
		if ($results !== NULL) {
			foreach ($results as $result) {
				$ids = array();
				$pid_list_ids = explode('^', $result->pid_list_ids);
				foreach ($pid_list_ids as $pid_list_id) {
					if (!empty($pid_list_id)) {
						$ids[] = $pid_list_id;
					}
				}

				$row = $this->db
						->get_row(
								'SELECT SUM(amount) AS amount,SUM(tax) AS tax,SUM(sum_amount) AS sum_amount FROM finance_receiveinvoice_pid_list WHERE id IN ('
										. implode(',', $ids) . ')');
				$s .= '<tr><td>' . $result->addtime . '</td><td>'
						. (!empty($row->amount) ? $row->amount : 0)
						. '</td><td>' . (!empty($row->tax) ? $row->tax : 0)
						. '</td><td>'
						. (!empty($row->sum_amount) ? $row->sum_amount : 0)
						. '</td><td><a href="' . BASE_URL
						. 'finance/receiveinvoice/?o=paymentshareedit&id='
						. $result->id . '">修改</a></td></tr>';
			}
		} else {
			$s .= '<tr><td colspan="5"><font color="red">没有符合条件的数据!</font></td></tr>';
		}
		return $s;
	}

	public function get_receive_invoice_payment_share_list_html() {
		if ($this->has_finance_receive_invoice_permission) {
			$buf = file_get_contents(
					TEMPLATE_PATH
							. 'finance/receiveinvoice/finance_receive_invoice_payment_share_list.tpl');
			return str_replace(
					array('[LEFT]', '[TOP]', '[VCODE]', '[RECEIVEINVOICELIST]',
							'[ALLCOUNTS]', '[COUNTS]', '[NEXT]', '[PREV]',
							'[BASE_URL]'),
					array($this->get_left_html(), $this->get_top_html(),
							$this->get_vcode(),
							$this->_get_payment_share_list_html(),
							$this->all_count, $this->_get_invoice_counts(),
							$this->_getNext('paymentsharelist'),
							$this->_getPrev('paymentsharelist'), BASE_URL),
					$buf);
		}
		return User::no_permission();
	}
}
