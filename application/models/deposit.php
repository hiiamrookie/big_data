<?php
class Deposit extends User {
	private $id;
	private $cid;
	private $amount;
	private $errors = array();
	private $reject = FALSE;
	private $auditmsg;

	public function __construct($fields = array()) {
		parent::__construct();
		if (!empty($fields)) {
			foreach ($this as $key => $value) {
				if ($fields[$key] !== NULL) {
					$this->$key = $fields[$key];
				}
			}
		}
	}

	private function validate_form_value($action) {
		$errors = array();
		if (in_array($action, array('add', 'audit', 'update'), TRUE)) {
			if ($action === 'add') {
				if (!self::validate_field_not_empty($this->cid)
						|| !self::validate_field_not_null($this->cid)) {
					$errors[] = '所属合同不能为空';
				} else if (strpos($this->cid, '-') === FALSE
						|| strpos($this->cid, '-') >= 50) {
					$errors[] = '合同号有误';
				}

				if (!self::validate_money($this->amount)) {
					$errors[] = '保证金金额值非有效金额 ';
				}
			} else if ($action === 'audit') {
				if (!self::validate_id(intval($this->id))) {
					$errors[] = '保证金记录选择有误';
				}

				if (self::validate_field_not_empty($this->auditmsg)
						&& !self::validate_field_max_length($this->auditmsg,
								500)) {
					$errors[] = '审核意见最多500个字符';
				}
			} else if ($action === 'update') {
				if (!self::validate_id(intval($this->id))) {
					$errors[] = '保证金记录选择有误';
				}

				if (!self::validate_money($this->amount)) {
					$errors[] = '保证金金额值非有效金额 ';
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

	public function get_deposit_apply_html() {
		$buf = file_get_contents(
				TEMPLATE_PATH . 'finance/deposit/deposit_apply.tpl');
		return str_replace(array('[LEFT]', '[TOP]', '[VCODE]', '[BASE_URL]'),
				array($this->get_left_html(), $this->get_top_html(),
						$this->get_vcode(), BASE_URL), $buf);
	}

	public function add_deposit() {
		if ($this->validate_form_value('add')) {
			$success = TRUE;
			$error = '';
			$this->db->query('BEGIN');

			$id = $this->db
					->get_var(
							'SELECT id FROM finance_deposit WHERE cid="'
									. reset(explode('-', $this->cid))
									. '" AND isok<>-1 FOR UPDATE');
			if ($id > 0) {
				$success = FALSE;
				$error = '该客户保证金已存在';
			} else {
				$insert_result = $this->db
						->query(
								'INSERT INTO finance_deposit(cid,cusname,amount,step,isok,adduser,addtime) SELECT cid,cusname,'
										. $this->amount . ',1,0,'
										. $this->getUid() . ',' . time()
										. ' FROM contract_cus WHERE cid="'
										. reset(explode('-', $this->cid)) . '"');
				if ($insert_result === FALSE) {
					$success = FALSE;
					$error = '新增保证金申请失败';
				}
			}

			if ($success) {
				$this->db->query('COMMIT');
			} else {
				$this->db->query('ROLLBACK');
			}
			return array('status' => !$success ? 'error' : 'success',
					'message' => $success ? '新增保证金申请成功' : $error);
		}
		return array('status' => 'error', 'message' => $this->errors);
	}

	public function audit_deposit() {
		if ($this->validate_form_value('audit')) {
			$query = 'UPDATE finance_deposit SET isok='
					. ($this->reject ? -2 : 1);
			if ($this->reject) {
				$query .= ',auditmsg="' . $this->auditmsg . '"';
			} else {
				$query .= ',step=2';
			}
			$query .= ' WHERE id=' . intval($this->id);
			$update_result = $this->db->query($query);
			if ($update_result === FALSE) {
				return array('status' => 'error', 'message' => '审核保证金失败');
			}
			return array('status' => 'success', 'message' => '审核保证金成功');
		}
		return array('status' => 'error', 'message' => $this->errors);
	}

	public function get_deposit_leader_audit_html() {
		if ($this->getHas_deposit_tab()) {
			$row = $this->db
					->get_row(
							'SELECT a.id,a.cid,a.cusname,a.amount,a.addtime,a.isok,a.step,b.username,b.realname FROM finance_deposit a LEFT JOIN users b ON a.adduser=b.uid WHERE a.id='
									. intval($this->id)
									. ' AND a.isok=0 AND a.step=1 AND (b.dep='
									. intval($this->getBelong_dep())
									. ' AND b.team='
									. intval($this->getBelong_team()) . ')');
			if ($row !== NULL) {
				$buf = file_get_contents(
						TEMPLATE_PATH . 'finance/deposit/deposit_audit.tpl');
				return str_replace(
						array('[LEFT]', '[TOP]', '[VCODE]', '[TIME]', '[CID]',
								'[CUSNAME]', '[AMOUNT]', '[USERINFO]', '[ID]',
								'[BASE_URL]'),
						array($this->get_left_html(), $this->get_top_html(),
								$this->get_vcode(),
								date('Y-m-d H:i:s', $row->addtime), $row->cid,
								$row->cusname,
								Format_Util::my_money_format('%.2n',
										$row->amount),
								$row->realname . '(' . $row->username . ')',
								intval($row->id), BASE_URL), $buf);
			} else {
				return User::no_object('没有该保证金申请');
			}
		} else {
			return User::no_permission();
		}
	}

	public function get_deposit_edit_html() {
		$row = $this->db
				->get_row(
						'SELECT id,cid,cusname,amount,isok,step,adduser,auditmsg FROM finance_deposit WHERE id='
								. intval($this->id));
		if ($row === NULL) {
			return User::no_object('没有该保证金记录');
		} else {
			if (intval($row->adduser) !== intval($this->getUid())) {
				return User::no_permission();
			} else if (intval($row->isok) !== 1 && intval($row->isok) !== -2) {
				return User::no_object('该保证金记录状态无法修改');
			} else {
				$buf = file_get_contents(
						TEMPLATE_PATH . 'finance/deposit/deposit_edit.tpl');
				return str_replace(
						array('[LEFT]', '[TOP]', '[VCODE]', '[TIME]', '[CID]',
								'[CUSNAME]', '[AMOUNT]', '[ID]',
								'[SHOWSTATUS]', '[AUDITMSG]', '[BASE_URL]'),
						array($this->get_left_html(), $this->get_top_html(),
								$this->get_vcode(),
								date('Y-m-d H:i:s', $row->addtime), $row->cid,
								$row->cusname, $row->amount, intval($row->id),
								(intval($row->isok) === -2 ? 'block' : 'none'),
								(intval($row->isok) === -2 ? Format_Util::format_html(
												$row->auditmsg) : ''),
								BASE_URL), $buf);
			}
		}
	}

	public function update_deposit() {
		if ($this->validate_form_value('update')) {
			$success = TRUE;
			$error = '';
			$this->db->query('BEGIN');

			$isok = $this->db
					->get_var(
							'SELECT isok FROM finance_deposit WHERE id='
									. intval($this->id) . ' FOR UPDATE');
			if (intval($isok) === -2 || intval($isok) === 1) {
				$query = 'UPDATE finance_deposit SET amount=' . $this->amount
						. ',isok=0,step=1';
				if (intval($isok) === -2) {
					//审核驳回后的修改
					$query .= ',auditmsg=""';
				}
				$query .= ' WHERE id=' . intval($this->id);
				$update_result = $this->db->query($query);
				if ($update_result === FALSE) {
					$success = FALSE;
					$error = '更新保证金信息失败';
				}
			} else {
				$success = FALSE;
				$error = '该保证金记录状态无法修改';
			}

			if ($success) {
				$this->db->query('COMMIT');
			} else {
				$this->db->query('ROLLBACK');
			}
			return array('status' => !$success ? 'error' : 'success',
					'message' => $success ? '更新保证金信息成功' : $error);
		}
		return array('status' => 'error', 'message' => $this->errors);
	}
}
