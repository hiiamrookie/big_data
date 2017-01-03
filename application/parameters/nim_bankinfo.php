<?php
class Nim_BankInfo extends User {
	private $has_nim_bankinfo_permission = FALSE;

	private $bank_name;
	private $bank_name_select;
	private $bank_account;
	private $status;
	private $is_default;
	private $errors = array();

	private $page;
	private $all_count;
	private $page_count;
	const LIMIT = 50;

	private $id;

	public static function getInstance($force_flush = FALSE) {
		$nim_bankinfo_cache_filename = md5('nim_bankinfo_cache_filename');
		$nim_bankinfo_cache = new FileCache(CACHE_TIME, CACHE_PATH);
		$nim_bankinfo_cache_file = $nim_bankinfo_cache
				->get($nim_bankinfo_cache_filename);

		if ($force_flush || $nim_bankinfo_cache_file === FALSE) {
			//读取数据库
			$dao = new Dao_Impl();
			$banks = $dao->db
					->get_results(
							//		'SELECT DISTINCT(bank_name) FROM finance_nim_bankinfo WHERE status=1 ORDER BY is_default DESC');
							'SELECT id,bank_name,bank_account FROM finance_nim_bankinfo WHERE status=1 ORDER BY is_default DESC,bank_name,bank_account');
			if ($banks !== NULL) {
				$datas = array();
				foreach ($banks as $bank) {
					if (!in_array($bank->bank_name, $datas['bankname'], TRUE)) {
						$datas['bankname'][] = $bank->bank_name;
					}
					$datas['infos'][] = array('id' => $bank->id,
							'bank_name' => $bank->bank_name,
							'bank_account' => $bank->bank_account);
				}
				$nim_bankinfo_cache->set($nim_bankinfo_cache_filename, $datas);
			}
		}
		return $nim_bankinfo_cache->get($nim_bankinfo_cache_filename);
	}

	private function _get_list_data() {
		$query = 'SELECT COUNT(*) FROM finance_nim_bankinfo';
		$this->all_count = intval($this->db->get_var($query));
		$this->page_count = ceil($this->all_count / self::LIMIT);
		$start = self::LIMIT * intval($this->page) - self::LIMIT;
		if ($start < 0) {
			$start = 0;
		}

		$results = array();
		$lists = $this->db
				->get_results(
						'SELECT id,bank_name,bank_account,is_default,status FROM finance_nim_bankinfo LIMIT '
								. $start . ',' . self::LIMIT);
		if ($lists !== NULL) {
			foreach ($lists as $list) {
				$results[] = array('id' => $list->id,
						'bank_name' => $list->bank_name,
						'bank_account' => $list->bank_account,
						'is_default' => $list->is_default,
						'status' => $list->status);
			}
		}
		return $results;
	}

	public static function get_bank_list($bankname = NULL) {
		$s = '';
		$banks = self::getInstance();
		if ($banks !== NULL) {
			$banks = $banks['bankname'];
			if(is_array($banks)){
				foreach ($banks as $bank) {
					$s .= '<option value="' . $bank . '" '
							. ($bankname !== NULL && $bankname === $bank ? 'selected'
									: '') . '>' . $bank . '</option>';
				}
			}
		}
		return $s;
	}

	public static function get_bank_account_list($id) {
		$s = '';
		$banks = self::getInstance();
		if ($banks !== NULL) {
			$banks = $banks['infos'];
			foreach ($banks as $bank) {
				$s .= '<option value="' . $bank['id'] . '" '
						. ($id !== NULL && intval($id) === intval($bank['id']) ? 'selected'
								: '') . '>' . $bank['bank_name'] . '---'
						. $bank['bank_account'] . '</option>';
			}
		}
		return $s;
	}

	public function __construct($fields = array()) {
		parent::__construct();
		if (!empty($fields)) {
			foreach ($this as $key => $value) {
				if ($fields[$key] !== NULL
						&& !in_array($key,
								array('has_nim_bankinfo_permission'), TRUE)) {
					$this->$key = $fields[$key];
				}
			}
		}
		if (in_array($this->getUsername(),
				$GLOBALS['manager_finance_permission'], TRUE)
				|| intval($this->getBelong_dep()) === 2) {
			$this->has_nim_bankinfo_permission = TRUE;
		}
	}

	public function get_add_nim_bankinfo_html() {
		if ($this->has_nim_bankinfo_permission) {
			$buf = file_get_contents(
					TEMPLATE_PATH . 'finance/finance_nim_bankinfo.tpl');
			return str_replace(
					array('[LEFT]', '[TOP]', '[BANKLIST]', '[VCODE]',
							'[BASE_URL]'),
					array($this->get_left_html(), $this->get_top_html(),
							Nim_BankInfo::get_bank_list(), $this->get_vcode(),
							BASE_URL), $buf);
		} else {
			return User::no_permission();
		}
	}

	public function get_nim_bankinfo_list_html() {
		if ($this->has_nim_bankinfo_permission) {
			$buf = file_get_contents(
					TEMPLATE_PATH . 'finance/finance_nim_bankinfo_list.tpl');
			return str_replace(
					array('[LEFT]', '[TOP]', '[BANKLIST]', '[ALLCOUNTS]',
							'[COUNTS]', '[NEXT]', '[PREV]', '[VCODE]',
							'[BASE_URL]'),
					array($this->get_left_html(), $this->get_top_html(),
							$this->_get_bank_list_html(), $this->all_count,
							$this->get_apply_counts(), $this->getNext(),
							$this->getPrev(), $this->get_vcode(), BASE_URL),
					$buf);
		} else {
			return User::no_permission();
		}
	}

	private function _get_bank_list_html() {
		$datas = $this->_get_list_data();
		$result = '';
		if (!empty($datas)) {
			foreach ($datas as $data) {
				$result .= '<tr><td>' . $data['bank_name'] . '</td><td>'
						. $data['bank_account'] . '</td><td>'
						. self::_get_is_default($data['is_default'])
						. '</td><td>' . self::_get_status($data['status'])
						. '</td><td>'
						. self::_get_action($data['status'],
								$data['is_default'], $data['id'])
						. '</td></tr>';
			}
		}
		return $result;
	}

	public function set_bank_online() {
		if ($this->has_nim_bankinfo_permission) {
			if ($this->validate_form_value('setonline')) {
				$success = TRUE;
				$error = '';
				$this->db->query('BEGIN');

				//检查是否银行信息存在并且可以设置状态
				$row = $this->db
						->get_row(
								'SELECT  id FROM finance_nim_bankinfo WHERE id='
										. intval($this->id) . ' AND status='
										. (intval($this->status) === 1 ? -1 : 1)
										. '  FOR UPDATE');
				if ($row === NULL) {
					$success = FALSE;
					$error = '没有该银行信息或者该银行信息状态与设置操作不符';
				} else {
					$update_result = $this->db
							->query(
									'UPDATE finance_nim_bankinfo SET status='
											. intval($this->status)
											. ' WHERE id=' . intval($this->id));
					if ($update_result === FALSE) {
						$success = FALSE;
						$error = '设置是否默认信息出错';
					}
				}

				if ($success) {
					$this->db->query('COMMIT');
					self::getInstance(TRUE);
				} else {
					$this->db->query('ROLLBACK');
				}
				return $success ? 1 : $error;
			}
			return implode("\n", $this->errors);
		}
		return NO_RIGHT_TO_DO_THIS;
	}

	public function set_default_bank() {
		if ($this->has_nim_bankinfo_permission) {
			if ($this->validate_form_value('setdefault')) {
				$success = TRUE;
				$error = '';
				$this->db->query('BEGIN');

				//检查是否银行信息存在并且可以设置状态
				$row = $this->db
						->get_row(
								'SELECT  id FROM finance_nim_bankinfo WHERE id='
										. intval($this->id)
										. ' AND is_default='
										. (intval($this->is_default) === 1 ? 0
												: 1) . '  FOR UPDATE');
				if ($row === NULL) {
					$success = FALSE;
					$error = '没有该银行信息或者该银行信息默认状态与设置操作不符';
				} else {
					if (intval($this->is_default) === 1) {
						//搜索是否已有默认信息
						$row = $this->db
								->get_row(
										'SELECT id FROM finance_nim_bankinfo WHERE is_default=1 FOR UPDATE');
						if ($row !== NULL) {
							$success = FALSE;
							$error = '已有默认的网迈银行信息，不可重复添加默认银行信息';
						}
					}

					if ($success) {
						$update_result = $this->db
								->query(
										'UPDATE finance_nim_bankinfo SET is_default='
												. intval($this->is_default)
												. ' WHERE id='
												. intval($this->id));
						if ($update_result === FALSE) {
							$success = FALSE;
							$error = '设置是否默认信息出错';
						}
					}
				}

				if ($success) {
					$this->db->query('COMMIT');
				} else {
					$this->db->query('ROLLBACK');
				}
				return $success ? 1 : $error;
			}
			return implode("\n", $this->errors);
		}
		return NO_RIGHT_TO_DO_THIS;
	}

	public function nim_bankinfo_add() {
		if ($this->validate_form_value('add')) {
			$success = TRUE;
			$error = '';
			$this->db->query('BEGIN');

			$bank_name = !empty($this->bank_name_select) ? $this
							->bank_name_select : $this->bank_name;

			//检查是否有重复
			$row = $this->db
					->get_row(
							'SELECT id FROM finance_nim_bankinfo WHERE bank_name="'
									. $bank_name . '" AND bank_account="'
									. $this->bank_account . '" FOR UPDATE');
			if ($row === NULL) {
				//如果选择默认，检查是否已经有默认的银行信息
				if (intval($this->is_default) === 1) {
					$row = $this->db
							->get_row(
									'SELECT id FROM finance_nim_bankinfo WHERE is_default=1 FOR UPDATE');
					if ($row !== NULL) {
						$success = FALSE;
						$error = '已有默认的网迈银行信息，不可重复添加默认银行信息';
					}
				}

				if ($success) {
					$insert_result = $this->db
							->query(
									'INSERT INTO finance_nim_bankinfo(bank_name,bank_account,is_default,status) VALUE("'
											. $bank_name . '","'
											. $this->bank_account . '",'
											. intval($this->is_default) . ','
											. intval($this->status) . ')');
					if ($insert_result === FALSE) {
						$success = FALSE;
						$error = '新建网迈银行信息失败';
					}
				}
			} else {
				$success = FALSE;
				$error = '已有相同银行信息';
			}

			if ($success) {
				$this->db->query('COMMIT');
				if (empty($this->bank_name_select) && !empty($this->bank_name)) {
					self::getInstance(TRUE);
				}
			} else {
				$this->db->query('ROLLBACK');
			}

			return array('status' => $success ? 'success' : 'error',
					'message' => $success ? '新建网迈银行信息成功' : $error);
		}
		return array('status' => 'error', 'message' => $this->errors);
	}

	private function validate_form_value($action) {
		$errors = array();
		if (in_array($action, array('add', 'setdefault', 'setonline'), TRUE)) {
			if ($action === 'setdefault' || $action === 'setonline') {
				if ($action === 'setdefault') {
					//默认状态
					if (!in_array(intval($this->is_default), array(0, 1), TRUE)) {
						$errors[] = '设置是否默认状态有误';
					}
				} else {
					//在线状态
					if (!in_array(intval($this->status), array(-1, 1), TRUE)) {
						$errors[] = '设置是否使用状态有误';
					}
				}

				//id
				if (!self::validate_id(intval($this->id))) {
					$errors[] = '银行信息选择有误';
				}
			} else {
				//银行名称
				if ((!self::validate_field_not_null($this->bank_name_select)
						|| !self::validate_field_not_empty(
								$this->bank_name_select))
						&& (!self::validate_field_not_null($this->bank_name)
								|| !self::validate_field_not_empty(
										$this->bank_name))) {
					$errors[] = '请选择已有的银行名称或者输入新的银行名称';
				}

				//银行帐号
				if (!self::validate_field_not_empty($this->bank_account)) {
					$errors[] = '银行账号不能为空';
				} else if (!self::validate_field_max_length(
						$this->bank_account, 255)) {
					$errors[] = '银行账号最多255个字符';
				}

				//状态
				if (!in_array(intval($this->status), array(-1, 1), TRUE)) {
					$errors[] = '状态选择有误';
				}

				//是否默认
				if (self::validate_field_not_null($this->is_default)
						&& intval($this->is_default) !== 1) {
					$errors[] = '是否默认选择有误';
				}
			}
		} else {
			$errors[] = NO_RIGHT_TO_DO_THIS;
		}

		if (empty($errors)) {
			return TRUE;
		}
		$this->errors = $errors;
		unset($errors);
		return FALSE;
	}

	private static function _get_status($status) {
		switch (intval($status)) {
		case -1:
			return '不使用';
		case 1:
			return '使用';
		}
		return '';
	}

	private static function _get_is_default($is_default) {
		switch (intval($is_default)) {
		case 0:
			return '非默认';
		case 1:
			return '默认';
		}
		return '';
	}

	private static function _get_action($status, $is_default, $id) {
		$s = array();
		if (intval($is_default) === 0) {
			$s[] = '<a href="javascript:setdefault(true,' . intval($id)
					. ')">设为默认</a>';
		} else {
			$s[] = '<a href="javascript:setdefault(false,' . intval($id)
					. ')">设为非默认</a>';
		}
		if (intval($status) === -1) {
			$s[] = '<a href="javascript:setonline(true,' . intval($id)
					. ')">设为使用</a>';
		} else {
			$s[] = '<a href="javascript:setonline(false,' . intval($id)
					. ')">设为不使用</a>';
		}
		return implode('&nbsp;|&nbsp;', $s);
	}

	private function get_apply_counts() {
		return $this->page . '	/' . $this->page_count . ' 页 &nbsp;&nbsp;';
	}

	private function _get_pagination($is_prev) {
		return '<a href="' . BASE_URL . 'finance/?o=nim_bankinfolist&page='
				. ($is_prev ? intval($this->page) - 1 : intval($this->page) + 1)
				. '">' . ($is_prev ? '上一页' : '下一页') . '</a>';
	}

	public function getPrev() {
		if (intval($this->page) === 1) {
			return '';
		} else {
			return $this->_get_pagination(TRUE);
		}
	}

	public function getNext() {
		if (intval($this->page) >= intval($this->page_count)) {
			return '';
		} else {
			return $this->_get_pagination(FALSE);
		}
	}
}
