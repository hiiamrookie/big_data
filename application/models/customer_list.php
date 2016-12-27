<?php
class Customer_List extends User {
	private $page;
	private $all_count;
	private $page_count;
	private $results = array();
	const LIMIT = 50;

	public function __construct($fields) {
		parent::__construct();
		foreach ($this as $key => $value) {
			if ($fields[$key] !== NULL) {
				$this->$key = $fields[$key];
			}
		}
		$this->_get_customer_list_data();
	}

	private function _get_customer_list_data() {
		$this->all_count = intval(
				$this->db
						->get_var(
								'SELECT COUNT(*) FROM (SELECT customer_id FROM v_customer_cusname GROUP BY customer_id) z'));
		$this->page_count = ceil($this->all_count / self::LIMIT);
		$start = self::LIMIT * intval($this->page) - self::LIMIT;
		if ($start < 0) {
			$start = 0;
		}
		/*
		$results = array();
		$customers = $this->db
		        ->get_results(
		                'SELECT customer_id,COUNT(cusname) AS count,customer_name,safety FROM v_customer_cusname GROUP BY customer_id LIMIT '
		                        . $start . ',' . self::LIMIT);
		if ($customers !== NULL) {
		    foreach ($customers as $customer) {
		        $used = 0;
		        if(intval($customer->count)  !== 0 ){
		            $cus = new Customer(array('customer_id'=>$customer->customer_id));
		            $used = $cus->compute_used_safety();
		            unset($cus);
		        }
		        $results[] = array('id' => $customer->customer_id,
		                'name' => $customer->customer_name,
		                'safety' => $customer->safety,
		                'count' => $customer->count,
		                'used'=>$used['used']);
		    }
		}
		$this->results = $results;
		 */
		$results = array();
		$customers = $this->db
				->get_results(
						'SELECT a.*,b.* FROM (
							SELECT customer_id,COUNT(cusname) AS count,customer_name,safety,tmpsafety,tmpsafety_deadline FROM v_customer_cusname GROUP BY customer_id ORDER  BY NULL) a
							LEFT JOIN (SELECT SUM(amount) AS amount,SUM(receivable) AS receivable,customer_id AS custid FROM v_final_data GROUP BY customer_id ORDER BY NULL) b
							ON a.customer_id=b.custid LIMIT ' . $start . ','
								. self::LIMIT);
		if ($customers !== NULL) {
			foreach ($customers as $customer) {
				$results[] = array('id' => $customer->customer_id,
						'name' => $customer->customer_name,
						'safety' => $customer->safety,
						'tmpsafety' => $customer->tmpsafety,
						'tmpsafety_deadline' => $customer->tmpsafety_deadline,
						'count' => $customer->count,
						'used' => $customer->amount - $customer->receivable);
			}
		}
		$this->results = $results;
	}

	private static function _get_customer_relation($count) {
		if ($count === 0) {
			return '未关联任何OA客户';
		}
		return '已关联<b><font color="red">' . $count . '</font></b>个OA客户';
	}

	private function _get_list_html() {
		$s = '';
		if (!empty($this->results)) {
			$results = $this->results;
			foreach ($results as $result) {
				$tmp = Format_Util::my_money_format('%.2n',
						$result['tmpsafety']);
				if(!empty($result['tmpsafety_deadline']) ){
					if(strtotime($result['tmpsafety_deadline'] . ' 23:59:59')<time()){
						$tmp .= '&nbsp;<font color="red">已过期</font>';
					}else{
						$tmp .= '&nbsp;<font color="#0000FF">（' . $result['tmpsafety_deadline'] . '）</font>';
					}
				}
				$s .= '<tr><td>' . $result['name'] . '</td><td>'
						. Format_Util::my_money_format('%.2n',
								$result['safety'])
						. '</td><td>' . $tmp
						. '</td><td>'
						. self::_get_customer_relation(intval($result['count']))
						. '</td><td>'
						. Format_Util::my_money_format('%.2n', $result['used'])
						. '</td><td>'
						. self::_get_action(intval($result['id']))
						. '</td></tr>';
			}
		}
		return $s;
	}

	private static function _get_action($id) {
		return '<a href="' . BASE_URL . 'manage/?o=customerrelate&customer_id='
				. $id . '">关联</a>&nbsp;|&nbsp;<a href="' . BASE_URL
				. 'manage/?o=customeredit&customer_id=' . $id . '">修改</a>';
	}

	private function _get_customer_counts() {
		return $this->page . '	/' . $this->page_count . ' 页 &nbsp;&nbsp;';
	}

	private function _get_pagination($is_prev) {
		return '<a href="' . BASE_URL . 'manage?o=customerlist&page='
				. ($is_prev ? intval($this->page) - 1 : intval($this->page) + 1)
				. '">' . ($is_prev ? '上一页' : '下一页') . '</a>';
	}

	private function _get_prev() {
		if (intval($this->page) === 1) {
			return '';
		} else {
			return $this->_get_pagination(TRUE);
		}
	}

	private function _get_next() {
		if (intval($this->page) >= intval($this->page_count)) {
			return '';
		} else {
			return $this->_get_pagination(FALSE);
		}
	}

	public function get_customer_list_html() {
		if ($this->getHas_manager_customer_safety_permission()) {
			$buf = file_get_contents(TEMPLATE_PATH . 'manage/customer_list.tpl');
			return str_replace(
					array('[LEFT]', '[TOP]', '[VCODE]', '[CUSTOMERLIST]',
							'[ALLCOUNTS]', '[COUNTS]', '[NEXT]', '[PREV]',
							'[BASE_URL]'),
					array($this->get_left_html(), $this->get_top_html(),
							$this->get_vcode(), $this->_get_list_html(),
							$this->all_count, $this->_get_customer_counts(),
							$this->_get_next(), $this->_get_prev(), BASE_URL),
					$buf);
		}
		return User::no_permission();
	}
}
