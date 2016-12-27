<?php
class Deposit_Invoice_List extends User {
	private $page;
	private $all_count;
	private $page_count;
	private $prev;
	private $next;
	private $has_finance_deposit_permission = FALSE;
	const LIMIT = 50;
	private $deposit_invoices = array();
	private $d;
	private $search;
	private $ismy = FALSE;

	public function __construct($fields, $ismy) {
		parent::__construct();
		if (in_array($this->getUsername(),
				$GLOBALS['manager_finance_permission'], TRUE)
				|| intval($this->getBelong_dep()) === 2) {
			$this->has_finance_deposit_permission = TRUE;
		}
		foreach ($this as $key => $value) {
			if ($fields[$key] !== NULL
					&& !in_array($key, array('has_finance_deposit_permission'),
							TRUE)) {
				$this->$key = $fields[$key];
			}
		}
		$this->ismy = $ismy;
		$this->_get_deposit_invoice_data();
	}

	private function _get_deposit_invoice_data() {
		$this_month_start = date('Y-m-01', $_SERVER['REQUEST_TIME']);
		$this_month_end = date('Y-m-d',
				strtotime($this_month_start . ' + 1 month -1 day'));
		$query = 'SELECT COUNT(*) FROM finance_deposit_invoice_list WHERE 1=1';
		if ($this->ismy) {
			$query .= ' AND user=' . intval($this->getUid());
		} else {
			if (intval($this->d) === 2) {
				$query .= ' AND isok!=0 AND a.time>='
						. strtotime($this_month_start . ' 00:00:00')
						. ' AND a.time<='
						. strtotime($this_month_end . ' 23:59:59');
			} else if (intval($this->d) === 3) {
				$query .= ' AND isok=0 AND print=1';
			} else {
				$query .= ' AND isok=0 AND step=2 AND print=0';
			}
		}
		if ($this->search !== NULL && $this->search !== ''
				&& intval($this->d) === 2 && !$this->ismy) {
			//开票抬头或开票号码
			$query .= ' AND (title LIKE "%' . $this->search
					. '%" OR number LIKE "%' . $this->search . '%")';
		}

		$this->all_count = intval($this->db->get_var($query));
		$this->page_count = ceil($this->all_count / self::LIMIT);
		$start = self::LIMIT * intval($this->page) - self::LIMIT;
		if ($start < 0) {
			$start = 0;
		}

		$results = array();
		$query = 'SELECT a.*,b.username,b.realname,c.realname AS gdrealname,c.username AS gdusername FROM finance_deposit_invoice_list a LEFT JOIN users b ON a.user=b.uid LEFT JOIN users c ON a.gduser=c.uid WHERE 1=1';
		if ($this->ismy) {
			$query .= ' AND a.user=' . intval($this->getUid());
		} else {
			if (intval($this->d) === 2) {
				$query .= ' AND isok!=0 AND a.time>='
						. strtotime($this_month_start . ' 00:00:00')
						. ' AND a.time<='
						. strtotime($this_month_end . ' 23:59:59');
			} else if (intval($this->d) === 3) {
				$query .= ' AND isok=0 AND print=1';
			} else {
				$query .= ' AND isok=0 AND step=2 AND print=0';
			}
		}
		if ($this->search !== NULL && $this->search !== ''
				&& intval($this->d) === 2 && !$this->ismy) {
			//开票抬头或开票号码
			$query .= ' AND (title LIKE "%' . $this->search
					. '%" OR number LIKE "%' . $this->search . '%")';
		}
		$query .= ' ORDER BY a.time DESC LIMIT ' . $start . ',' . self::LIMIT;
		//var_dump($query);
		$deposit_invoices = $this->db->get_results($query);
		if ($deposit_invoices !== NULL) {
			foreach ($deposit_invoices as $deposit_invoice) {
				$results[] = array('id' => $deposit_invoice->id,
						'title' => $deposit_invoice->title,
						'cids' => $deposit_invoice->cids,
						'amount' => $deposit_invoice->amount,
						'type' => $deposit_invoice->type,
						'number' => $deposit_invoice->number,
						'isok' => $deposit_invoice->isok,
						'step' => $deposit_invoice->step,
						'time' => $deposit_invoice->time,
						'print' => $deposit_invoice->print,
						'gduser'=>$deposit_invoice->gdrealname === NULL ? '' : $deposit_invoice->gdrealname . '(' . $deposit_invoice->gdusername . ')');
			}
			$this->deposit_invoices = $results;
			unset($results);
		}
	}

	private static function _get_deposit_invoice_status($isok, $print, $step) {
		if ($isok === 0) {
			if ($step === 1) {
				return '<font color="#ff6600"><b>等待部门leader审核</b></font>';
			} else if ($step === 2) {
				if ($print === 0) {
					return '<font color="#ff6600"><b>等待财务部审核打印</b></font>';
				} else if ($print === 1) {
					return '<font color="#ff6600"><b>等待记录发票号码</b></font>';
				}
			}
		} else if ($isok === 1) {
			return '<font color="#66cc00"><b>生效</b></font>';
		} else {
			return '<font color="red"><b>驳回</b></font>';
		}
	}

	private static function _get_deposit_invoice_action($id, $isok) {
		$s = '<a href="' . BASE_URL
				. 'finance/deposit/?o=deposit_invoice_view&id=' . $id
				. '">查看</a>';
		if ($isok === -1) {
			//可修改
			$s .= '&nbsp;&nbsp;<a href="' . BASE_URL
					. 'finance/deposit/?o=deposit_invoice_edit&id=' . $id
					. '">修改</a>';
		}
		return $s;
	}

	public static function get_invoice_action($id, $d, $print, $isok) {
		if ($d === 1 || $d === 3) {
			$s = '<a href="' . BASE_URL . 'finance/deposit/?o=audit&p='
					. $print . '&id=' . $id . '">'
					. ($print === 1 ? '归档' : '审核') . '</a>';
			if ($d === 3) {
				$s .= '&nbsp;<a href="' . BASE_URL
						. 'finance/deposit/?o=print&id=' . $id
						. '" target="_blank">打印</a>';
			}
			return $s;
		}
		$s = '<a href="' . BASE_URL . 'finance/deposit/?o=view&id=' . $id
				. '">查看</a>';
		if ($isok === 1) {
			$s .= '&nbsp;<a href="' . BASE_URL . 'finance/deposit/?o=print&id='
					. $id . '" target="_blank">打印</a>&nbsp;<a href="'
					. BASE_URL . 'finance/deposit/?o=gdupdate&id=' . $id
					. '">修改</a>';
		}
		return $s;
	}

	public static function get_deposit_invoice_type($type) {
		if ($type === 0) {
			return '收据';
		} else if ($type === 1) {
			return '普票';
		} else if ($type === 2) {
			return '<font color="#0000FF">增票</font>';
		}
		return '';
	}

	public function _get_list_html() {
		$deposit_invoices = $this->deposit_invoices;
		$result = '';
		if (!empty($deposit_invoices)) {
			foreach ($deposit_invoices as $key => $deposit_invoice) {
				$result .= '<tr><td>' . ($key + 1) . '</td><td>'
						. date('Y-m-d H:i:s', $deposit_invoice['time'])
						. '</td><td>' . $deposit_invoice['title']
						. '</td><td><font color="#ff9933"><b>'
						. Format_Util::my_money_format('%.2n',
								$deposit_invoice['amount'])
						. '</b></font></td><td>'
						. self::get_deposit_invoice_type(
								intval($deposit_invoice['type'])) . '</td>' . (!$this->ismy && intval($this->d) === 2 ? '<td>' . $deposit_invoice['gduser'] . '</td>' : '') . '<td>'
						. self::_get_deposit_invoice_status(
								intval($deposit_invoice['isok']),
								intval($deposit_invoice['print']),
								intval($deposit_invoice['step'])) . '</td><td>'
						. ($this->ismy ? self::_get_deposit_invoice_action(
										intval($deposit_invoice['id']),
										intval($deposit_invoice['isok']))
								: self::get_invoice_action(
										intval($deposit_invoice['id']),
										intval($this->d),
										intval($deposit_invoice['print']),
										intval($deposit_invoice['isok'])))
						. '</td></tr>';
			}
		} else {
			$result .= '<tr><td colspan="'
					. (intval($this->d) === 2 ? '8' : '7')
					. '"><font color="red">当前没有相应开票记录！</font></td></tr>';
		}
		return $result;
	}

	private function _get_deposit_invoice_counts() {
		return $this->page . '	/' . $this->page_count . ' 页 &nbsp;&nbsp;';
	}

	private function _get_pagination($is_prev) {
		if($this->ismy){
			return '<a href="' . BASE_URL
				. 'finance/deposit/?o=my_deposit_invoice_list&page='
				. ($is_prev ? intval($this->page) - 1 : intval($this->page) + 1)
				. '">' . ($is_prev ? '上一页' : '下一页') . '</a>';
		}else{
			return '<a href="' . BASE_URL
				. 'finance/deposit/?o=deposit_invoicelist&page='
				. ($is_prev ? intval($this->page) - 1 : intval($this->page) + 1)
				. '&d=' . $this->d . '">' . ($is_prev ? '上一页' : '下一页') . '</a>';
		}
	}

	public function _getPrev() {
		if (intval($this->page) === 1) {
			return '';
		} else {
			return $this->_get_pagination(TRUE);
		}
	}

	private function _getNext() {
		if (intval($this->page) >= intval($this->page_count)) {
			return '';
		} else {
			return $this->_get_pagination(FALSE);
		}
	}

	private function _get_search_bar() {
		if (intval($this->d) === 2) {
			return '<table width="100%" class="tabin">
        	<tr>
            	<td>&nbsp;&nbsp;关键字：<input type="text" style="height:20px;" name="search" id="search" value="'
					. $this->search
					. '"/>&nbsp;&nbsp;<input type="button" class="btn" value="搜 索" id="searchbtn"/></td>
            </tr>
        </table>';
		}
		return '';
	}

	private static function _get_list_title($d) {
		$s = '<tr>
          <th width="50">编号</th>
          <th style="width:150px">申请时间</th>
          <th>开票抬头</th>
          <th>开票金额</th>
          <th>开票类型</th>';
		if ($d === 2) {
			$s .= '<th>归档人</th>';
		}
		$s .= '<th>状态</th>
          <th>操作</th>
        </tr>';
		return $s;
	}

	private function _getOn1() {
		return !in_array(intval($this->d), array(2, 3), TRUE) ? 'class="on"'
				: '';
	}

	private function _getOn2() {
		return intval($this->d) === 2 ? 'class="on"' : '';
	}

	private function _getOn3() {
		return intval($this->d) === 3 ? 'class="on"' : '';
	}

	public function get_deposit_invoice_list_html() {
		$buf = file_get_contents(
				TEMPLATE_PATH
						. (!$this->ismy ? 'finance/deposit/deposit_invoice_manage_list.tpl'
								: 'finance/deposit/deposit_invoice_list.tpl'));
		$search = array('[LEFT]', '[TOP]', '[VCODE]', '[DEPOSITLIST]',
				'[BASE_URL]');
		$replace = array($this->get_left_html(), $this->get_top_html(),
				$this->get_vcode(), $this->_get_list_html(), BASE_URL);
		if (!$this->ismy) {
			$search[] = '[SEARCHBAR]';
			$search[] = '[LISTTITLE]';
			$search[] = '[ON1]';
			$search[] = '[ON2]';
			$search[] = '[ON3]';
			$replace[] = $this->_get_search_bar();
			$replace[] = self::_get_list_title(intval($this->d));
			$replace[] = $this->_getOn1();
			$replace[] = $this->_getOn2();
			$replace[] = $this->_getOn3();
		} else {
			$search[] = '[ALLCOUNTS]';
			$search[] = '[COUNTS]';
			$search[] = '[NEXT]';
			$search[] = '[PREV]';
			$replace[] = $this->all_count;
			$replace[] = $this->_get_deposit_invoice_counts();
			$replace[] = $this->_getNext();
			$replace[] = $this->_getPrev();
		}

		//var_dump($search);
		//var_dump($replace);
		return str_replace($search, $replace, $buf);
	}
}
