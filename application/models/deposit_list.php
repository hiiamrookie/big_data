<?php
class Deposit_List extends User {
	private $page;
	private $all_count;
	private $page_count;
	private $prev;
	private $next;
	private $has_finance_deposit_permission = FALSE;
	const LIMIT = 50;
	private $deposits = array();

	public function __construct($fields) {
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
		$this->_get_deposit_data();
	}

	private function _get_deposit_data() {
		$query = 'SELECT COUNT(*) FROM finance_deposit WHERE 1=1';
		if (!$this->has_finance_deposit_permission) {
			$query .= ' AND adduser=' . intval($this->getUid());
		}
		$this->all_count = intval($this->db->get_var($query));
		$this->page_count = ceil($this->all_count / self::LIMIT);
		$start = self::LIMIT * intval($this->page) - self::LIMIT;
		if ($start < 0) {
			$start = 0;
		}

		$results = array();
		$query = 'SELECT a.id,a.cid,a.cusname,a.amount,a.addtime,a.isok,a.step,b.username,b.realname FROM finance_deposit a LEFT JOIN users b ON a.adduser=b.uid WHERE 1=1';
		if (!$this->has_finance_deposit_permission) {
			$query .= ' AND a.adduser=' . intval($this->getUid());
		}
		$query .= ' ORDER BY a.addtime DESC LIMIT ' . $start . ','
				. self::LIMIT;
		$deposits = $this->db->get_results($query);
		if ($deposits !== NULL) {
			foreach ($deposits as $deposit) {
				$results[] = array('id' => $deposit->id,
						'cid' => $deposit->cid, 'cusname' => $deposit->cusname,
						'amount' => $deposit->amount,
						'addtime' => date('Y-m-d H:i:s', $deposit->addtime),
						'isok' => $deposit->isok, 'step' => $deposit->step,
						'user' => $deposit->realname . '(' . $deposit->username
								. ')');
			}
			$this->deposits = $results;
			unset($results);
		}
	}

	public function _get_list_html() {
		$deposits = $this->deposits;
		$result = '';
		if (!empty($deposits)) {
			foreach ($deposits as $key => $deposit) {
				$result .= '<tr><td>' . ($key + 1) . '</td><td>'
						. $deposit['addtime'] . '</td><td>' . $deposit['cid']
						. '</td><td>' . $deposit['cusname']
						. '</td><td><font color="#ff9933"><b>'
						. Format_Util::my_money_format('%.2n',
								$deposit['amount']) . '</b></font></td><td>'
						. self::_get_deposit_status(intval($deposit['isok']),
								intval($deposit['step'])) . '</td><td>'
						. self::_get_deposit_action(intval($deposit['id']),
								intval($deposit['isok'])) . '</td></tr>';
			}
		}
		unset($deposits);
		return $result;
	}

	private static function _get_deposit_status($isok, $step) {
		if ($isok === 1) {
			return '<font color="#ff6600">已审核</font>';
		} else if ($isok === -1) {
			return '<font color="red"><b>已撤销</b></font>';
		} else if ($isok === -2) {
			return '<font color="red"><b>已驳回</b></font>';
		} else {
			return '<font color="#ff6600">等待部门leader审核</font>';
		}
	}

	private static function _get_deposit_action($id, $isok) {
		if ($isok === -2 || $isok === 1) {
			return '<a href="' . BASE_URL . 'finance/deposit/?o=edit&id=' . $id
					. '">修改</a>';
		}
		return '';
	}

	private function _get_deposit_counts() {
		return $this->page . '	/' . $this->page_count . ' 页 &nbsp;&nbsp;';
	}

	private function _get_pagination($is_prev) {
		return '<a href="' . BASE_URL
				. 'finance/deposit/?o=my_deposit_list&page='
				. ($is_prev ? intval($this->page) - 1 : intval($this->page) + 1)
				. '">' . ($is_prev ? '上一页' : '下一页') . '</a>';
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

	public function get_deposit_list_html() {
		$buf = file_get_contents(
				TEMPLATE_PATH . 'finance/deposit/deposit_list.tpl');
		return str_replace(
				array('[LEFT]', '[TOP]', '[VCODE]', '[DEPOSITLIST]',
						'[ALLCOUNTS]', '[COUNTS]', '[NEXT]', '[PREV]',
						'[BASE_URL]'),
				array($this->get_left_html(), $this->get_top_html(),
						$this->get_vcode(), $this->_get_list_html(),
						$this->all_count, $this->_get_deposit_counts(),
						$this->_getNext(), $this->_getPrev(), BASE_URL), $buf);
	}
}
