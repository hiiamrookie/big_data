<?php
class Supplier_Industry_List extends User {
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

			$this->all_count = intval(
					$this->db
							->get_var(
									'SELECT COUNT(*)
FROM
(
SELECT * FROM new_supplier_industry
) a
LEFT JOIN finance_supplier_short b
ON a.supplier_short_id=b.id
WHERE 1=1 '
											. (!empty($this->search) ? ' AND a.industry_name LIKE "%'
															. $this->search
															. '%"' : '')));
			$this->page_count = ceil($this->all_count / self::LIMIT);
			$start = self::LIMIT * intval($this->page) - self::LIMIT;
			if ($start < 0) {
				$start = 0;
			}

			$results = array();
			$industry = $this->db
					->get_results(
							'SELECT a.id,a.industry_name,a.supplier_short_id,a.isok,b.media_short
FROM
(
SELECT * FROM new_supplier_industry
) a
LEFT JOIN finance_supplier_short b
ON a.supplier_short_id=b.id
WHERE 1=1 '
									. (!empty($this->search) ? ' AND a.industry_name LIKE "%'
													. $this->search . '%"' : '')
									. '
ORDER BY media_short,industry_name LIMIT ' . $start . ','
									. self::LIMIT);
			if ($industry !== NULL) {
				foreach ($industry as $ind) {
					$results[] = array('id' => $ind->id,
							'supplier_short_id' => $ind->supplier_short_id,
							'industry_id' => $ind->id,
							'media_short' => $ind->media_short,
							'industry_name' => $ind->industry_name,
							'isok' => $ind->isok);
				}
			}
			$this->datas = $results;
			unset($industry);
		}
	}

	private static function _get_action($id, $isok) {
		if ($isok === -1) {
			return '<a href="javascript:cancel(' . $id . ',1);">恢复</a>';
		}
		return '<a href="' . BASE_URL
				. 'finance/supplier/?o=supplierindustryedit&id=' . $id
				. '">修改</a> | <a href="javascript:cancel(' . $id
				. ',-1);">撤销</a>';
	}

	private static function _get_status($isok) {
		return $isok === -1 ? '<font color="red"><b>已撤销</b></a>'
				: '<font color="green"><b>正常</b></font>';
	}

	private function _get_list_html() {
		$datas = $this->datas;
		$s = '';
		if (!empty($datas)) {
			foreach ($datas as $key => $data) {
				$s .= '<tr><td>' . (($this->page - 1) * self::LIMIT + $key + 1)
						. '</td><td>' . $data['industry_name'] . '</td><td>'
						. $data['media_short'] . '</td><td>'
						. self::_get_status(intval($data['isok']))
						. '</td><td>'
						. self::_get_action(intval($data['id']),
								intval($data['isok'])) . '</td></tr>';
			}
		} else {
			$s = '<tr><td colspan="5"><font color="red">没有符合条件的数据!</font></td></tr>';
		}
		unset($datas);
		return $s;
	}

	private function _get_supplier_counts() {
		return $this->page . '	/' . $this->page_count . ' 页 &nbsp;&nbsp;';
	}

	private function _get_pagination($is_prev) {
		return '<a href="' . BASE_URL
				. 'finance/supplier/?o=supplierindustrylist&search='
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

	public function get_supplier_industry_list_html() {
		if ($this->has_supplier_permission) {
			$buf = file_get_contents(
					TEMPLATE_PATH
							. 'finance/supplier/supplier_industry_list.tpl');
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
