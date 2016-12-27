<?php
class Executive_Ajax extends User {
	private $q;

	public function __construct($q) {
		parent::__construct();
		$this->q = $q;
	}

	public function get_pid_names($checkok = FALSE) {
		$has_finance_permission = in_array($this->getUsername(),
				$GLOBALS['manager_finance_permission'], TRUE)
				|| intval($this->getBelong_dep()) === 2 ? TRUE : FALSE;
		$where_sql = array();

		if ($has_finance_permission
				|| $this->getHas_check_executive_permission()) {
			$where_sql[] = ' pid LIKE "%' . $this->q . '%" ';
		} else {
			$res = $this
					->get_relation_executive_permission(
							intval($this->getBelong_city()),
							intval($this->getBelong_dep()),
							intval($this->getBelong_team()));
			if ($res > 0) {
				$where_sql[] = ' pid LIKE "%' . $this->q . '%" ';
				if (intval($this->getBelong_city()) !== 0 && $res === 1) {
					$where_sql[] = ' city='
							. intval($this->getBelong_city()) . ' ';
				} else if (intval($this->getBelong_city()) !== 0
						&& intval($this->getBelong_dep()) !== 0 && $res === 2) {
					$where_sql[] = ' dep=' . intval($this->getBelong_dep())
							. ' ';
				} else if (intval($this->getBelong_city()) !== 0
						&& intval($this->getBelong_dep()) !== 0
						&& intval($this->getBelong_team()) !== 0 && $res === 3) {
					$where_sql[] = ' team='
							. intval($this->getBelong_team()) . ' ';
				}
			} else {
				$where_sql[] = ' pid LIKE "%' . $this->q . '%" AND user='
						. intval($this->getUid());
			}
		}
		$where_sql[] = ' isok<>-1';
		$s = '';
		if (!empty($where_sql)) {
			$qu = 'SELECT DISTINCT(pid) FROM executive WHERE'
					. implode('AND', $where_sql);
			if ($checkok) {
				$qu .= ' AND isok=1';
			}
			$results = $this->db->get_results($qu);
			if ($results !== NULL) {
				foreach ($results as $result) {
					$s .= $result->pid . "\n";
				}
			}
		}
		return $s;
	}

	public function get_pid_names_bytec() {
		$where_sql = array();

		if (intval($this->getBelong_dep()) === 6) {
			$where_sql[] = ' pid LIKE "%' . $this->q . '%" AND isok<>-1';
		}
		$s = '';
		if (!empty($where_sql)) {
			$results = $this->db
					->get_results(
							'SELECT DISTINCT(pid),name FROM executive WHERE'
									. implode('AND', $where_sql));
			if ($results !== NULL) {
				foreach ($results as $result) {
					$s .= $result->pid . '~' . $result->name . "\n";
				}
			}
		}
		return $s;
	}
}
