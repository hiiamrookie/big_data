<?php
class Finance_Receivables_List extends User {
	private $search;
	private $page;
	private $all_count;
	private $page_count;
	private $prev;
	private $next;
	private $receivables = array ();
	
	private $has_finance_receivables_permission = FALSE;
	
	const LIMIT = 30;
	/**
	 * @return the $search
	 */
	public function getSearch() {
		return $this->search;
	}
	
	/**
	 * @return the $has_finance_receivables_permission
	 */
	public function getHas_finance_receivables_permission() {
		return $this->has_finance_receivables_permission;
	}
	
	/**
	 * @return the $all_count
	 */
	public function getAll_count() {
		return $this->all_count;
	}
	
	public function __construct($fields) {
		parent::__construct ();
		if (in_array ( $this->getUsername (), $GLOBALS['manager_finance_permission'], TRUE ) || intval ( $this->getBelong_dep () ) === 2) {
			$this->has_finance_receivables_permission = TRUE;
		}
		foreach ( $this as $key => $value ) {
			if ($fields [$key] !== NULL && ! in_array ( $key, array ('has_finance_receivables_permission' ), TRUE )) {
				$this->$key = $fields [$key];
			}
		}
	}
	
	private function _get_finance_receivables_data() {
		$this_month_start = date('Y-m-01',$_SERVER['REQUEST_TIME']);
		$this_month_end = date('Y-m-d',strtotime($this_month_start . ' + 1 month -1 day'));
		$where = ' WHERE a.date>="' . $this_month_start . '" AND a.date<="' . $this_month_end . '"';
		if ($this->search !== NULL) {
			$where .= ' AND (a.receivables_ids LIKE "%' . $this->search . '%" OR a.amount LIKE "%' . $this->search . '%" OR a.date LIKE "%' . $this->search . '%")';
		}
		$query = 'SELECT COUNT(*) FROM finance_receivables_list a' . $where;
		$this->all_count = intval ( $this->db->get_var ( $query ) );
		$this->page_count = ceil ( $this->all_count / self::LIMIT );
		$start = self::LIMIT * intval ( $this->page ) - self::LIMIT;
		
		if ($start < 0) {
			$start = 0;
		}
		
		$query = 'SELECT a.*,FROM_UNIXTIME(a.time) AS tt,b.username,b.realname FROM finance_receivables_list a LEFT JOIN users b ON a.user=b.uid ' . $where . ' ORDER BY a.time DESC LIMIT ' . $start . ',' . self::LIMIT;
		$receivables = $this->db->get_results ( $query );
		
		$results = array ();
		if ($receivables !== NULL) {
			foreach ( $receivables as $receivable ) {
				$results [] = array ('id' => $receivable->id, 'receivables_ids' => $receivable->receivables_ids, 'amount' => $receivable->amount, 'date' => $receivable->date, 'user' => $receivable->realname . ' (' . $receivable->username . ')', 'tt' => $receivable->tt, 'isok' => $receivable->isok, 'content' => $receivable->content,'payer'=>$receivable->payer );
			}
		}
		$this->receivables = $results;
		unset ( $results );
	}
	
	private function _get_list_html() {
		$result = '';
		$this->_get_finance_receivables_data ();
		if (! empty ( $this->receivables )) {
			$receivables = $this->receivables;
			foreach ( $receivables as $key => $receivable ) {
				$content = self::_get_content ( $receivable ['content'] );
				$result .= '<tr><td>' . (self::LIMIT * ($this->page - 1) + $key + 1) . '</td><td>' . $receivable ['date'] . '</td><td><font color="#ff9933"><b>' . Format_Util::my_money_format ( '%.2n', $receivable ['amount'] ) . '</b></font></td><td title="' . $content . '">' . String_Util::cut_str ( $content, 20, 0, 'UTF-8', '...' ) . '</td><td>' . $receivable ['payer'] . '</td><td>' . $receivable ['user'] . '</td><td>' . $receivable ['tt'] . '</td><td>' . self::_get_status ( intval ( $receivable ['isok'] ) ) . '</td><td>' . self::_get_action ( intval ( $receivable ['isok'] ), intval ( $receivable ['id'] ) ) . '</td></tr>';
			}
		} else {
			$result = '<tr><td colspan=9><font color="#FF0000">没有收款记录！</font></td></tr>';
		}
		return $result;
	}
	
	public function get_receivables_list_html(){
		if($this->getHas_finance_receivables_permission()){
			$buf = file_get_contents ( TEMPLATE_PATH . 'finance/receivables/finance_receivables_list.tpl' );
			return str_replace ( array ('[LEFT]', '[TOP]', '[RECEIVABLESLIST]', '[ALLCOUNTS]', '[PREV]', '[NEXT]', '[COUNTS]', '[SEARCH]', '[VCODE]', '[BASE_URL]' ), array ($this->get_left_html (), $this->get_top_html (), $this->_get_list_html (), $this->all_count, $this->getPrev (), $this->getNext (), $this->get_page_counts (), $this->search, $this->get_vcode (), BASE_URL ), $buf );
		}else{
			return User::no_permission();
		}
	}
	
	private static function _get_status($isok) {
		if ($isok === -1) {
			return '<font color="#FF0000"><b>撤销</b></font>';
		} else {
			return '<font color="#00CC00"><b>生效</b></font>';
		}
	}
	
	private static function _get_action($isok, $id) {
		if ($isok === -1) {
			return '';
		} else {
			return '<a href="javascript:docancel(' . $id . ');">撤销</a>&nbsp;&nbsp;<a href="' . BASE_URL . 'finance/receivables/?o=receivables_edit&id=' . $id . '">修改</a>';
		}
	}
	
	private static function _get_content($content) {
		$s = '';
		$content = explode ( '|', $content );
		foreach ( $content as $value ) {
			$arr = explode ( '^', $value );
			$s .= sprintf ( '%s: %s' . "\n", $arr [0], Format_Util::my_money_format ( '%.2n', $arr [1] ) );
		}
		return $s;
	}
	
	/**
	 * @return the $prev
	 */
	public function getPrev() {
		if (intval ( $this->page ) === 1) {
			$this->prev = '';
		} else {
			$this->prev = self::_get_pagination ( intval ( $this->page ) - 1, $this->search, TRUE );
		}
		return $this->prev;
	}
	
	/**
	 * @return the $next
	 */
	public function getNext() {
		if (intval ( $this->page ) >= intval ( $this->page_count )) {
			$this->next = '';
		} else {
			$this->next = self::_get_pagination ( intval ( $this->page ) + 1, $this->search, FALSE );
		}
		return $this->next;
	}
	
	private static function _get_pagination($page, $search, $is_prev) {
		return '<a href="' . BASE_URL . 'finance/receivables/?o=receivableslist&page=' . $page . '&search=' . $search . '">' . ($is_prev ? '上一页' : '下一页') . '</a>';
	}
	
	public function get_page_counts() {
		if ($this->page !== NULL && $this->page_count !== NULL) {
			return sprintf ( '%d /%d 页 &nbsp;&nbsp;', $this->page, $this->page_count );
		}
		return '';
	}

}