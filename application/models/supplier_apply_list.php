<?php
class Supplier_Apply_List extends User {
	private $search;
	private $page;
	private $all_count;
	private $page_count;
	private $datas = array();

	const LIMIT = 50;

	public function __construct($fields = array()) {
		parent::__construct();
		if (!empty($fields)) {
			foreach ($this as $key => $value) {
				if ($fields[$key] !== NULL) {
					$this->$key = $fields[$key];
				}
			}
		}

		$where_sql = array();
		if (self::validate_field_not_null($this->search)
				&& self::validate_field_not_empty($this->search)) {
			$where_sql[] = ' supplier_name LIKE "%' . $this->search . '%" ';
		}

		$query = 'SELECT COUNT(*) FROM new_supplier_apply WHERE apply_userid='
				. $this->getUid();
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
		$query = 'SELECT id,supplier_name,url,deduction,in_invoice_tax_rate,supplier_type,isok,addtime,step FROM new_supplier_apply WHERE apply_userid='
				. $this->getUid();
		if (!empty($where_sql)) {
			$query .= ' AND ' . implode(' AND ', $where_sql);
		}
		$query .= ' ORDER BY addtime DESC LIMIT ' . $start . ',' . self::LIMIT;
		$suppliers = $this->db->get_results($query);

		if ($suppliers !== NULL) {
			foreach ($suppliers as $supplier) {
				$results[] = array('id' => $supplier->id,
						'supplier_name' => $supplier->supplier_name,
						'url' => $supplier->url,
						'deduction' => $supplier->deduction,
						'in_invoice_tax_rate' => $supplier->in_invoice_tax_rate,
						'supplier_type' => $supplier->supplier_type,
						'isok' => $supplier->isok,
						'addtime' => $supplier->addtime,'step'=>$supplier->step);
			}
		}
		$this->datas = $results;
		unset($suppliers);
	}

	public static function get_deduction($deduction) {
		switch ($deduction) {
		case 0:
			return '无';
			break;
		case 1:
			return '有';
			break;
		}
		return '';
	}

	public static function get_supplier_type($type) {
		switch ($type) {
		case 1:
			return '媒体';
			break;
		case 2:
			return '外包';
			break;
		}
		return '';
	}

	public static function get_supplier_status($isok,$step) {
		switch ($isok) {
		case 1:
			return '<font color="green">通过</font>';
			break;
		case -1:
			return '<font color="red">驳回</font>';
			break;
		}
		//if($step === 0 || $step === 1){
		if($step === 0){
			return '<font color="#ff6600">等待 财务部 审核</font>';
		}//else if($step === 1){
		//	return '<font color="#ff6600">等待 财务部 Kate 审核</font>';
		//}
	}

	private static function _get_action($id, $isok) {
		$s = '<a href="' . BASE_URL . 'finance/supplier/?o=view_myapply&id='
				. $id . '">查看</a>';
		if ($isok === -1) {
			$s .= '&nbsp;|&nbsp;<a href="' . BASE_URL
					. 'finance/supplier/?o=edit_myapply&id=' . $id . '">修改</a>';
		}
		return $s;
	}

	private function _get_list_html() {
		$datas = $this->datas;
		$s = '';
		if (!empty($datas)) {
			foreach ($datas as $key => $data) {
				$s .= '<tr><td>' . (($this->page - 1) * self::LIMIT + $key + 1)
						. '</td><td>' . $data['addtime'] . '</td><td>'
						. $data['supplier_name'] . '</td><td>' . $data['url']
						. '</td><td>'
						. self::get_deduction(intval($data['deduction']))
						. '</td><td>' . $data['in_invoice_tax_rate']
						. '</td><td>'
						. self::get_supplier_type(
								intval($data['supplier_type'])) . '</td><td>'
						. self::get_supplier_status(intval($data['isok']),intval($data['step']))
						. '</td><td>'
						. self::_get_action(intval($data['id']),
								intval($data['isok'])) . '</td></tr>';
			}
		} else {
			$s = '<tr><td colspan="9"><font color="red">没有符合条件的数据!</font></td></tr>';
		}
		unset($datas);
		return $s;
	}

	private function _get_supplier_counts() {
		return $this->page . '	/' . $this->page_count . ' 页 &nbsp;&nbsp;';
	}

	private function _get_pagination($is_prev) {
		return '<a href="' . BASE_URL . 'finance/supplier/?o=mylist&search='
				. $this->search . '&page='
				. ($is_prev ? intval($this->page) - 1 : intval($this->page) + 1)
				. '">' . ($is_prev ? '上一页' : '下一页') . '</a>';
	}

	private function _getNext() {
		if (intval($this->page) >= intval($this->page_count)) {
			return '';
		} else {
			return $this->_get_pagination(FALSE);
		}
	}

	private function _getPrev() {
		if (intval($this->page) === 1) {
			return '';
		} else {
			return $this->_get_pagination(TRUE);
		}
	}

	public function get_supplier_apply_list_html() {
		$buf = file_get_contents(
				TEMPLATE_PATH . 'finance/supplier/supplier_mylist.tpl');
		return str_replace(
				array('[LEFT]', '[TOP]', '[VCODE]', '[SUPPLIERLIST]',
						'[ALLCOUNTS]', '[COUNTS]', '[NEXT]', '[PREV]',
						'[SEARCH]', '[BASE_URL]'),
				array($this->get_left_html(), $this->get_top_html(),
						$this->get_vcode(), $this->_get_list_html(),
						$this->all_count, $this->_get_supplier_counts(),
						$this->_getNext(), $this->_getPrev(), $this->search,
						BASE_URL), $buf);
	}

	public static function get_remark($isok, $remark, $username, $realname) {
		if ($isok === 0) {
			return '';
		}
		return '<tr><td style="font-weight:bold;width:100px">审核留言</td><td>'
				. Format_Util::format_html($remark)
				. '</td></tr><tr><td style="font-weight:bold;width:100px">审核人</td><td>'
				. $realname . '（' . $username . '）</td></tr>';
	}
}
