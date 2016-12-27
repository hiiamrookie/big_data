<?php
class Supplier_List extends User {
	private $search;
	private $page;
	private $all_count;
	private $page_count;
	private $datas = array();
	private $has_supplier_permission = FALSE;

	const LIMIT = 50;

	public function __construct($fields = array()) {
		parent::__construct();
		if (!empty($fields)) {
			foreach ($this as $key => $value) {
				if ($fields[$key] !== NULL
						&& !in_array($key, array('has_supplier_permission'),
								TRUE)) {
					$this->$key = $fields[$key];
				}
			}
		}

		if (in_array($this->getUsername(),
				$GLOBALS['manager_finance_permission'], TRUE)
				|| intval($this->getBelong_dep()) === 2) {
			$this->has_supplier_permission = TRUE;

			$where_sql = array();
			if (self::validate_field_not_null($this->search)
					&& self::validate_field_not_empty($this->search)) {
				$where_sql[] = ' supplier_name LIKE "%' . $this->search . '%" ';
			}

			$query = 'SELECT COUNT(*) FROM new_supplier_info a LEFT JOIN new_supplier b ON a.supplier_id=b.id WHERE 1=1 ';
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
			$sql = 'SELECT a.id,a.supplier_id,a.url,a.in_invoice_tax_rate,a.addtime,b.supplier_name AS supplier_name,a.isok FROM new_supplier_info a LEFT JOIN new_supplier b ON a.supplier_id=b.id WHERE 1=1';
			if (!empty($where_sql)) {
				$sql .= ' AND ' . implode(' AND ', $where_sql);
			}
			$sql .= ' ORDER BY supplier_name DESC LIMIT ' . $start . ','
					. self::LIMIT;
			$suppliers = $this->db->get_results($sql);
			if ($suppliers !== NULL) {
				foreach ($suppliers as $supplier) {
					$results[] = array('id' => $supplier->id,
							'supplier_id' => $supplier->supplier_id,
							'supplier_name' => $supplier->supplier_name,
							'url' => $supplier->url,
							'in_invoice_tax_rate' => $supplier
									->in_invoice_tax_rate,
							'isok' => $supplier->isok,
							'addtime' => $supplier->addtime);
				}
			}
			$this->datas = $results;
			unset($suppliers);
		}
	}

	private static function _get_action($id, $isok) {
		if ($isok === -1) {
			return '<a href="javascript:cancel(' . $id . ',1);">恢复</a>';
		}
		return '<a href="' . BASE_URL
				. 'finance/supplier/?o=supplieredit&id=' . $id
				. '">修改</a> | <a href="javascript:cancel(' . $id
				. ',0);">撤销</a>';
	}

	private static function _get_status($isok) {
		return $isok === -1 ? '<font color="red">已撤销</a>'
				: '<font color="green">正常</font>';
	}

	private function _get_list_html() {
		$datas = $this->datas;
		$s = '';
		if (!empty($datas)) {
			foreach ($datas as $key => $data) {
				$s .= '<tr><td>' . (($this->page - 1) * self::LIMIT + $key + 1)
						. '</td><td>' . $data['supplier_name'] . '</td><td>'
						. $data['url'] . '</td><td>'
						. $data['in_invoice_tax_rate'] . '</td><td>'
						. $data['addtime'] . '</td><td>'
						. self::_get_status(intval($data['isok']))
						. '</td><td>'
						. self::_get_action(intval($data['supplier_id']),
								intval($data['isok'])) . '</td></tr>';
			}
		} else {
			$s = '<tr><td colspan="7"><font color="red">没有符合条件的数据!</font></td></tr>';
		}
		unset($datas);
		return $s;
	}

	private function _get_supplier_counts() {
		return $this->page . '	/' . $this->page_count . ' 页 &nbsp;&nbsp;';
	}

	private function _get_pagination($is_prev) {
		return '<a href="' . BASE_URL
				. 'finance/supplier/?o=supplierlist&search=' . $this->search
				. '&page='
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

	public function get_supplier_list_html() {
		if ($this->has_supplier_permission) {
			$buf = file_get_contents(
					TEMPLATE_PATH . 'finance/supplier/supplier_list.tpl');
			return str_replace(
					array('[LEFT]', '[TOP]', '[VCODE]', '[SUPPLIERLIST]',
							'[ALLCOUNTS]', '[COUNTS]', '[NEXT]', '[PREV]',
							'[SEARCH]', '[BASE_URL]'),
					array($this->get_left_html(), $this->get_top_html(),
							$this->get_vcode(), $this->_get_list_html(),
							$this->all_count, $this->_get_supplier_counts(),
							$this->_getNext(), $this->_getPrev(),
							$this->search, BASE_URL), $buf);
		} else {
			return User::no_permission();
		}
	}
}
