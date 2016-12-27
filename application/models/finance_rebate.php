<?php
class Finance_Rebate extends User {
	private $id;
	private $media_name;
	private $media_payment_type;
	private $media_rebate_rate;
	private $relation_type;
	private $invoice_type;
	private $d1;
	private $d2;
	private $d3;
	private $title;
	private $content;
	private $remark;
	private $pid_array = array();

	private $item_rebate = array();
	private $errors = array();
	private $has_finance_rebate_permission = FALSE;

	private $page;
	private $all_count;
	private $page_count;

	const LIMIT = 50;

	private $auditmsg;

	private $date;
	private $amount;
	private $number;
	private $bank;

	private $ttype;
	private $isthesame;

	public function __construct($fields = array()) {
		parent::__construct();
		if (in_array($this->getUsername(),
				$GLOBALS['manager_finance_permission'], TRUE)
				|| intval($this->getBelong_dep()) === 2) {
			$this->has_finance_rebate_permission = TRUE;
		}
		if (!empty($fields)) {
			foreach ($this as $key => $value) {
				if ($fields[$key] !== NULL
						&& !in_array($key,
								array('has_finance_rebate_permission '), TRUE)) {
					$this->$key = $fields[$key];
				}
			}
		}
	}

	public function getRebateInvoiceHtml() {
		if ($this->has_finance_rebate_permission) {
			$buf = file_get_contents(
					TEMPLATE_PATH . 'finance/rebate/rebate_invoice_manager.tpl');
			return str_replace(
					array('[LEFT]', '[TOP]', '[VCODE]', '[BASE_URL]'),
					array($this->get_left_html(), $this->get_top_html(),
							$this->get_vcode(), BASE_URL), $buf);
		}
		return User::no_permission();
	}

	public function getRebateInvoiceNoCollectionHtml() {
		if ($this->has_finance_rebate_permission) {
			$buf = file_get_contents(
					TEMPLATE_PATH
							. 'finance/rebate/rebate_invoice_nocollection.tpl');
			return str_replace(
					array('[LEFT]', '[TOP]', '[VCODE]', '[BASE_URL]'),
					array($this->get_left_html(), $this->get_top_html(),
							$this->get_vcode(), BASE_URL), $buf);
		}
		return User::no_permission();
	}

	public function getRebateQueryHtml() {
		if ($this->has_finance_rebate_permission) {
			$buf = file_get_contents(
					TEMPLATE_PATH . 'finance/rebate/rebate_query.tpl');
			return str_replace(
					array('[LEFT]', '[TOP]', '[VCODE]', '[BASE_URL]'),
					array($this->get_left_html(), $this->get_top_html(),
							$this->get_vcode(), BASE_URL), $buf);
		}
		return User::no_permission();
	}

	private static function _getPrev($page, $action) {
		if (intval($page) === 1) {
			return '';
		} else {
			return self::_get_pagination(intval($page) - 1, TRUE, $action);
		}
	}

	private static function _getNext($page, $page_count, $action) {
		if (intval($page) >= intval($page_count)) {
			return '';
		} else {
			return self::_get_pagination(intval($page) + 1, FALSE, $action);
		}
	}

	private static function _get_pagination($page, $is_prev, $action) {
		return '<a href="' . BASE_URL . 'finance/rebate/?o=' . $action
				. '&page=' . $page . '">' . ($is_prev ? '上一页' : '下一页') . '</a>';
	}

	private function _get_datas($ismy) {
		$this->all_count = intval(
				$this->db
						->get_var(
								'SELECT COUNT(*) FROM finance_rebate_invoice WHERE '
										. ($ismy ? ' user=' . $this->getUid()
												: '1=1')));
		$this->page_count = ceil($this->all_count / self::LIMIT);
		$start = self::LIMIT * intval($this->page) - self::LIMIT;
		if ($start < 0) {
			$start = 0;
		}
		$results = array();
		$lists = $this->db
				->get_results(
						'SELECT a.id,a.addtime,a.media_name,a.amount,a.invoice_type,a.media_payment_type,a.title,a.isok,b.realname,b.username FROM finance_rebate_invoice a LEFT JOIN users b ON a.user=b.uid WHERE  '
								. ($ismy ? ' a.user=' . $this->getUid() : '1=1')
								. ' LIMIT ' . $start . ',' . self::LIMIT);

		if ($lists !== NULL) {
			foreach ($lists as $list) {
				$results[] = array('id' => $list->id,
						'addtime' => $list->addtime,
						'media_name' => $list->media_name,
						'amount' => $list->amount,
						'invoice_type' => $list->invoice_type,
						'title' => $list->title, 'isok' => $list->isok,
						'user' => $list->realname . '（' . $list->username . '）',
						'media_payment_type' => $list->media_payment_type);
			}
		}
		return $results;
	}

	private static function _get_status($isok) {
		switch (intval($isok)) {
		case 0:
			return '<font color="#ff6600"><b>等待财务部审核</b></font>';
		case 1:
			return '<font color="green"><b>已通过</b></font>';
		case 2:
			return '<font color="red"><b>已驳回</b></font>';
		}
	}

	private function _get_action($id, $isok, $ismy, $media_payment_type) {
		$s = array();
		$s[] = '<a href="' . BASE_URL
				. 'finance/rebate/?o=view_apply_invoice&id=' . intval($id)
				. '">查看</a>';
		if ($ismy) {
			if (intval($isok) !== 1) {
				$s[] = '<a href="' . BASE_URL
						. 'finance/rebate/?o=edit_apply_invoice&id='
						. intval($id) . '">修改</a>';
			} else {
				$s[] = '<a href="javascript:void(0);" onclick="javascript:'
						. (intval($media_payment_type) === 1 ? 'transfer(\'receive2pay\','
										. intval($id) . ')'
								: 'transfer(\'pay2receive\',' . intval($id)
										. ')') . ';">'
						. (intval($media_payment_type) === 1 ? '转应付' : '转应收')
						. '</a>';
			}
		}

		if ($this->has_finance_rebate_permission && !$ismy) {
			if (intval($isok) === 0) {
				$s[] = '<a href="' . BASE_URL
						. 'finance/rebate/?o=audit_apply_invoice&id='
						. intval($id) . '">审核</a>';
			} else if (intval($isok) === 1) {
				$s[] = '<a href="' . BASE_URL
						. 'finance/rebate/?o=gd_apply_invoice&id='
						. intval($id) . '">归档</a>';
			}
		}
		return implode('&nbsp;|&nbsp;', $s);
	}

	private function _get_rebate_invoice_list($ismy = TRUE) {
		$datas = $this->_get_datas($ismy);
		$result = '';
		if (!empty($datas)) {
			foreach ($datas as $data) {
				$result .= '<tr>'
						. ($ismy ? '' : '<td>' . $data['user'] . '</td>')
						. '<td>' . $data['addtime'] . '</td><td>'
						. $data['media_name'] . '</td><td>' . $data['amount']
						. '</td><td>'
						. (intval($data['invoice_type']) === 1 ? '普票' : '增票')
						. '</td><td>' . $data['title'] . '</td><td>'
						. self::_get_status($data['isok']) . '</td><td>'
						. $this
								->_get_action($data['id'], $data['isok'],
										$ismy, $data['media_payment_type'])
						. '</td></tr>';
			}
		} else {
			$result .= '<tr><td colspan="' . ($ismy ? 7 : 8)
					. '"><font color="red"><b>没有相关数据！</b></font></td></tr>';
		}
		return $result;
	}

	private function _get_invoice_counts() {
		return $this->page . '	/' . $this->page_count . ' 页 &nbsp;&nbsp;';
	}

	public function getRebateInvoiceListHtml() {
		$buf = file_get_contents(
				TEMPLATE_PATH . 'finance/rebate/rebate_invoice_list.tpl');
		return str_replace(
				array('[LEFT]', '[TOP]', '[PAYMENTLIST]', '[ALLCOUNTS]',
						'[COUNTS]', '[PREV]', '[NEXT]', '[VCODE]',
						'[BASE_URL]'),
				array($this->get_left_html(), $this->get_top_html(),
						$this->_get_rebate_invoice_list(FALSE),
						$this->all_count, $this->_get_invoice_counts(),
						self::_getPrev($this->page, 'apply_manager'),
						self::_getNext($this->page, $this->page_count,
								'apply_manager'), $this->get_vcode(), BASE_URL),
				$buf);
	}

	public function getRebateInvoiceMyListHtml() {
		$buf = file_get_contents(
				TEMPLATE_PATH . 'finance/rebate/rebate_invoice_mylist.tpl');
		return str_replace(
				array('[LEFT]', '[TOP]', '[PAYMENTLIST]', '[ALLCOUNTS]',
						'[COUNTS]', '[PREV]', '[NEXT]', '[VCODE]',
						'[BASE_URL]'),
				array($this->get_left_html(), $this->get_top_html(),
						$this->_get_rebate_invoice_list(), $this->all_count,
						$this->_get_invoice_counts(),
						self::_getPrev($this->page, 'apply_mylist'),
						self::_getNext($this->page, $this->page_count,
								'apply_mylist'), $this->get_vcode(), BASE_URL),
				$buf);
	}

	private function _get_transfer_apply_list() {
		$this->all_count = intval(
				$this->db
						->get_var(
								'SELECT COUNT(*) FROM finance_rebate_transfer_apply WHERE isok=1'));
		$this->page_count = ceil($this->all_count / self::LIMIT);
		$start = self::LIMIT * intval($this->page) - self::LIMIT;
		if ($start < 0) {
			$start = 0;
		}
		$result = '';
		$lists = $this->db
				->get_results(
						'SELECT a.goal_type,b.id,b.media_name,b.media_payment_type,c.collection_amount,c.collection_date,d.invoice_amount,d.invoice_date,d.number
FROM 
finance_rebate_transfer_apply a
LEFT JOIN
finance_rebate_invoice b
ON a.rebate_invoice_id=b.id
LEFT JOIN 
(
SELECT invoice_id,SUM(collection_amount) AS collection_amount,collection_date
FROM 
(
SELECT rebate_invoice_id AS invoice_id,amount AS collection_amount,date AS collection_date
FROM 
finance_rebate_invoice_gd 
WHERE gdtype=2 ORDER BY date DESC
) m GROUP BY m.invoice_id
) c
ON b.id=c.invoice_id
LEFT JOIN
(
SELECT invoice_id,SUM(amount) AS invoice_amount,date AS invoice_date,number
FROM
(
SELECT rebate_invoice_id AS invoice_id,number,amount,date FROM finance_rebate_invoice_gd WHERE gdtype=1 ORDER BY date DESC
) n GROUP BY n.invoice_id
) d
ON b.id=d.invoice_id
WHERE a.isok=1 ORDER BY a.apply_time DESC  LIMIT ' . $start . ',' . self::LIMIT);

		if ($lists !== NULL) {
			foreach ($lists as $list) {
				$result .= '<tr><td>' . $list->media_name . '</td><td>'
						. (intval($list->media_payment_type) === 1 ? '付现'
								: '抵应付账款') . '</td><td></td><td>'
						. $list->invoice_amount . '</td><td>'
						. $list->invoice_date . '</td><td>' . $list->number
						. '</td><td>' . $list->collection_amount . '</td><td>'
						. $list->collection_date . '</td><td><a href="'
						. BASE_URL . 'finance/rebate/?o='
						. (intval($list->goal_type) === 1 ? 'pay2receive'
								: 'receive2pay') . '&id=' . $list->id . '">'
						. (intval($list->goal_type) === 1 ? '转应收' : '转应付')
						. '</a></td></tr>';
			}
		} else {
			$result .= '<tr><td colspan="9"><font color="red"><b>没有相关数据！</b></font></td></tr>';
		}
		return $result;
	}

	public function getRebateTransferListHtml() {
		if ($this->has_finance_rebate_permission) {
			$buf = file_get_contents(
					TEMPLATE_PATH . 'finance/rebate/rebate_transfer_list.tpl');
			return str_replace(
					array('[LEFT]', '[TOP]', '[TRANSFERAPPLYLIST]',
							'[ALLCOUNTS]', '[COUNTS]', '[PREV]', '[NEXT]',
							'[VCODE]', '[BASE_URL]'),
					array($this->get_left_html(), $this->get_top_html(),
							$this->_get_transfer_apply_list(),
							$this->all_count, $this->_get_invoice_counts(),
							self::_getPrev($this->page, 'rebate_transfer_list'),
							self::_getNext($this->page, $this->page_count,
									'rebate_transfer_list'),
							$this->get_vcode(), BASE_URL), $buf);
		}
		return User::no_permission();
	}

	public function getRebateReceive2PayHtml() {
		if ($this->has_finance_rebate_permission) {
			$row = $this->db
					->get_row(
							'SELECT * FROM finance_rebate_invoice WHERE id='
									. intval($this->id)
									. ' AND media_payment_type=1');
			if ($row !== NULL) {
				$buf = file_get_contents(
						TEMPLATE_PATH
								. 'finance/rebate/rebate_invoice_receive2pay.tpl');
				return str_replace(
						array('[LEFT]', '[TOP]', '[VCODE]', '[ID]',
								'[REBATEINVOICEINFO]', '[BASE_URL]'),
						array($this->get_left_html(), $this->get_top_html(),
								$this->get_vcode(), intval($this->id),
								$this->_getRebateInvoiceInfo($row, 'view'),
								BASE_URL), $buf);
			}
			return User::no_object('没有该申请');
		}
		return User::no_permission();
	}

	public function getRebatePay2ReceiveHtml() {
		if ($this->has_finance_rebate_permission) {
			$row = $this->db
					->get_row(
							'SELECT * FROM finance_rebate_invoice WHERE id='
									. intval($this->id)
									. ' AND media_payment_type=2');
			if ($row !== NULL) {
				$buf = file_get_contents(
						TEMPLATE_PATH
								. 'finance/rebate/rebate_invoice_pay2receive.tpl');
				return str_replace(
						array('[LEFT]', '[TOP]', '[VCODE]', '[ID]',
								'[REBATEINVOICEINFO]', '[BASE_URL]'),
						array($this->get_left_html(), $this->get_top_html(),
								$this->get_vcode(), intval($this->id),
								$this->_getRebateInvoiceInfo($row, 'view'),
								BASE_URL), $buf);
			}
			return User::no_object('没有该申请');
		}
		return User::no_permission();
	}

	public function getRebateApplyInvoiceHtml() {
		$buf = file_get_contents(
				TEMPLATE_PATH . 'finance/rebate/rebate_invoice_apply.tpl');
		return str_replace(array('[LEFT]', '[TOP]', '[VCODE]', '[BASE_URL]'),
				array($this->get_left_html(), $this->get_top_html(),
						$this->get_vcode(), BASE_URL), $buf);
	}

	private function validate_form_value($action) {
		$errors = array();
		if (in_array($action,
				array('recover_need_invoice', 'no_need_invoice',
						'pass_invoice', 'reject_invoice', 'gd_invoice',
						'gd_collection', 'add_rebate_invoice_apply',
						'update_rebate_invoice_apply', 'receive2pay',
						'pay2receive', 'doreceive2pay', 'dopay2receive'), TRUE)) {
			if (in_array($action,
					array('recover_need_invoice', 'no_need_invoice'), TRUE)) {
				$item_rebate = $this->item_rebate;
				foreach ($item_rebate as $key => $value) {
					$key = explode('_', $key);
					if (!self::validate_id(
							intval($key[0])
									|| !self::validate_id(intval($key[1])))) {
						$errors[] = '记录选择有误';
						break;
					}

					if (!self::validate_money($value)) {
						$errors[] = '返点金额值非有效金额值';
						break;
					}
				}

				if ($action === 'no_need_invoice') {
					if (!in_array($this->isthesame, array('0', '1'), TRUE)) {
						$errors[] = '无需开票选择有误';
					}

					if($this->isthesame === '0'){
						$pid_array = $this->pid_array;
						if (empty($pid_array)) {
							$errors[] = '执行单选择不能为空';
						} else {
							foreach ($pid_array as $pa) {
								if (!self::validate_invoice_money($pa)) {
									$errors[] = '开票金额非有效金额值';
									break;
								}
							}
						}
					}
				}
			} else if (in_array($action,
					array('doreceive2pay', 'dopay2receive'), TRUE)) {
				if (!self::validate_id(intval($this->id))) {
					$errors[] = '开票申请选择有误';
				}

				$pid_array = $this->pid_array;
				//var_dump($pid_array);
				if (empty($pid_array)) {
					$errors[] = '执行单选择不能为空';
				} else {
					foreach ($pid_array as $pa) {
						if (!self::validate_invoice_money($pa)) {
							$errors[] = '开票金额非有效金额值';
							break;
						}
					}
				}
			} else if (in_array($action, array('receive2pay', 'pay2receive'),
					TRUE)) {
				if (!self::validate_id(intval($this->id))) {
					$errors[] = '开票申请选择有误';
				}
			} else if (in_array($action,
					array('add_rebate_invoice_apply',
							'update_rebate_invoice_apply'), TRUE)) {
				if ($action === 'update_rebate_invoice_apply') {
					if (!self::validate_id(intval($this->id))) {
						$errors[] = '开票申请选择有误';
					}
				}

				if (empty($this->media_name)) {
					$errors[] = '媒体名称不能为空';
				} else if (!self::validate_field_max_length($this->media_name,
						200)) {
					$errors[] = '媒体名称最多200个字符';
				}

				if (!in_array(intval($this->media_payment_type), array(1, 2),
						TRUE)) {
					$errors[] = '媒体支付方式选择有误';
				}

				if (empty($this->media_rebate_rate)) {
					$errors[] = '媒体返点比例不能为空';
				} else if ($this->media_rebate_rate < 0
						|| $this->media_rebate_rate > 100) {
					$errors[] = '媒体返点比例输入有误';
				}

				if (!in_array(intval($this->invoice_type), array(1, 2), TRUE)) {
					$errors[] = '开票类型选择有误';
				}

				if (intval($this->invoice_type) === 2) {
					if (empty($this->d1)) {
						$errors[] = '纳税人识别号不能为空';
					} else if (!self::validate_field_max_length($this->d1, 200)) {
						$errors[] = '纳税人识别号最多200个字符';
					}

					if (empty($this->d2)) {
						$errors[] = '地址、电话不能为空';
					} else if (!self::validate_field_max_length($this->d2, 200)) {
						$errors[] = '地址、电话最多200个字符';
					}

					if (empty($this->d3)) {
						$errors[] = '开户行及账号不能为空';
					} else if (!self::validate_field_max_length($this->d3, 200)) {
						$errors[] = '开户行及账号最多200个字符';
					}
				}

				if (empty($this->title)) {
					$errors[] = '开票抬头不能为空';
				} else if (!self::validate_field_max_length($this->title, 200)) {
					$errors[] = '开票抬头最多200个字符';
				}

				if (empty($this->content)) {
					$errors[] = '开票内容不能为空';
				} else if (!self::validate_field_max_length($this->content, 200)) {
					$errors[] = '开票内容最多200个字符';
				}

				if (!empty($this->remark)
						&& !self::validate_field_max_length($this->remark, 500)) {
					$errors[] = '备注最多500个字符';
				}

				$pid_array = $this->pid_array;
				if (empty($pid_array)) {
					$errors[] = '执行单选择不能为空';
				} else {
					foreach ($pid_array as $pa) {
						if (!self::validate_invoice_money($pa)) {
							$errors[] = '开票金额非有效金额值';
							break;
						}
					}
				}

			} else if (in_array($action, array('gd_invoice', 'gd_collection'),
					TRUE)) {
				if (!self::validate_id(intval($this->id))) {
					$errors[] = '返点开票申请选择有误';
				}

				if (empty($this->date)) {
					$errors[] = '日期不能为空';
				} else if (strtotime($this->date) === FALSE) {
					$errors[] = '日期值不是一个有效的日期值';
				}

				if ($action === 'gd_invoice'
						&& !self::validate_invoice_money($this->amount)) {
					$errors[] = '归档金额不是一个有效的金额值';
				} else if ($action === 'gd_collection'
						&& !self::validate_money($this->amount)) {
					$errors[] = '回款金额不是一个有效的金额值';
				}

				if ($action === 'gd_invoice') {
					if (empty($this->number)) {
						$errors[] = '发票号码必须输入';
					}
				} else if ($action === 'gd_collection') {
					if (!self::validate_id(intval($this->bank))) {
						$errors[] = '银行信息选择有误';
					}
				}
			} else if (in_array($action,
					array('pass_invoice', 'reject_invoice'), TRUE)) {
				if (!self::validate_id(intval($this->id))) {
					$errors[] = '返点开票申请选择有误';
				}

				if ($action === 'reject_invoice') {
					if (empty($this->auditmsg)) {
						$errors[] = '审核意见必须输入';
					} else if (!self::validate_field_max_length(
							$this->auditmsg, 200)) {
						$errors[] = '审核意见最多200个字符';
					}
				}
			} else {
				$item_rebate = $this->item_rebate;
				if (empty($item_rebate)) {
					$errors[] = '所选返点数据不能为空';
				} else {
					foreach ($item_rebate as $key => $value) {
						//if (!self::validate_id(intval($key))) {
						//	$errors[] = '所选付款条目有误';
						//	break;
						//} else {
						if (!self::validate_money($value)) {
							$errors[] = '所选返点金额有误';
							break;
						}
						//}
					}
				}
			}
		} else {
			$errors[] = '无权限操作2';
		}

		if (empty($errors)) {
			return TRUE;
		}
		$this->errors = $errors;
		unset($errors);
		return FALSE;
	}

	public function getRebatenNoNeedInvoiceResult() {
		if ($this->has_finance_rebate_permission) {
			if ($this->validate_form_value('no_need_invoice')) {
				$success = TRUE;
				$error = '';
				$this->db->query('BEGIN');

				//finance_payment_rebate_share
				$result = $this->db
						->query(
								'INSERT INTO finance_payment_rebate_share(isok) VALUE(1)');
				if ($result === FALSE) {
					$success = FALSE;
					$error = '返点无需开票失败，错误代码1';
				}

				$share_id = 0;
				if ($success) {
					$share_id = $this->db->insert_id;
				}

				if ($success) {
					$item_rebate = $this->item_rebate;
					$subsql = array();
					foreach ($item_rebate as $key => $value) {
						$key = explode('_', $key);
						$result = $this->db
								->query(
										'INSERT INTO finance_payment_rebate_status(rebate_id,rebate_amount,status) SELECT id,'
												. $value
												. ',4 FROM finance_payment_rebate WHERE apply_id='
												. $key[0] . ' AND list_id='
												. $key[1] . ' AND status=1');
						if ($result === FALSE) {
							$success = FALSE;
							$error = '返点无需开票失败，错误代码2';
							break;
						} else {
							$subsql[] = '(' . $share_id . ','
									. intval($this->db->insert_id) . ',1)'; //(rebate_share_id,rebate_status_id,isok)
						}
					}

					if (!empty($subsql)) {
						$result = $this->db
								->query(
										'INSERT INTO finance_payment_rebate_share_rebate(rebate_share_id,rebate_status_id,isok) VALUES'
												. implode(',', $subsql));
						if ($result === FALSE) {
							$success = FALSE;
							$error = '返点无需开票失败，错误代码3';
						}
					}
				}

				if ($success) {
					$subsql = array();
					if (intval($this->isthesame) === 0) {
						//不一致
						$pid_arrays = $this->pid_array;
						foreach ($pid_arrays as $pid => $amount) {
							$pid = explode('_', $pid);
							$subsql[] = '(' . $share_id . ',"' . $pid[0] . '",'
									. $pid[1] . ',' . $amount . ',1)'; //(rebate_share_id,pid,paycostid,amount,isok)
						}
						if (!empty($subsql)) {
							$result = $this->db
									->query(
											'INSERT INTO finance_payment_rebate_share_pid(rebate_share_id,pid,paycostid,amount,isok) VALUES'
													. implode(',', $subsql));
							if ($result === FALSE) {
								$success = FALSE;
								$error = '返点无需开票失败，错误代码4';
							}
						}
					} else {
						//一致
						foreach ($item_rebate as $key => $value) {
							$key = explode('_', $key);
							$result = $this->db
									->query(
											'INSERT INTO finance_payment_rebate_share_pid(rebate_share_id,pid,paycostid,amount,isok) SELECT '
													. $share_id
													. ',pid,paycostid,'
													. $value
													. ',1 FROM finance_payment_person_apply_list WHERE id='
													. $key[1] . ' AND isok=1');
							if ($result === FALSE) {
								$success = FALSE;
								$error = '返点无需开票失败，错误代码5';
								break;
							}
						}
					}
				}

				if ($success) {
					$this->db->query('COMMIT');
				} else {
					$this->db->query('ROLLBACK');
				}
				return array('status' => $success ? 'success' : 'error',
						'message' => $success ? '返点无需开票成功' : $error);
			}
			return array('status' => 'error', 'message' => $this->errors);
		}
		return array('status' => 'error', 'message' => NO_RIGHT_TO_DO_THIS);
	}

	public function getRebateRecoverNeedInvoiceResult() {
		if ($this->has_finance_rebate_permission) {
			if ($this->validate_form_value('recover_need_invoice')) {
				$success = TRUE;
				$error = '';
				$this->db->query('BEGIN');

				$item_rebate = $this->item_rebate;
				$subsql = array();
				foreach ($item_rebate as $key => $value) {
					$key = explode('_', $key);
					$result = $this->db
							->query(
									'INSERT INTO finance_payment_rebate_status(rebate_id,rebate_amount,status) SELECT id,'
											. $value
											. ',1 FROM finance_payment_rebate WHERE apply_id='
											. $key[0] . ' AND list_id='
											. $key[1] . ' AND status=1');
					if ($result === FALSE) {
						$success = FALSE;
						$error = '返点还原成待开票失败';
						break;
					}
				}

				//TODO 减少原来的

				if ($success) {
					$this->db->query('COMMIT');
				} else {
					$this->db->query('ROLLBACK');
				}
				return array('status' => $success ? 'success' : 'error',
						'message' => $success ? '返点还原成待开票成功' : $error);
			}
			return array('status' => 'error', 'message' => $this->errors);
		}
		return array('status' => 'error', 'message' => NO_RIGHT_TO_DO_THIS);
	}

	public function getRebateInvoiceApplyResult($action) {
		if ($this->validate_form_value($action . '_rebate_invoice_apply')) {
			$success = TRUE;
			$error = '';
			$this->db->query('BEGIN');

			if ($action === 'update') {
				//检验
				$row = $this->db
						->get_row(
								'SELECT invoice_id FROM finance_rebate_invoice WHERE id='
										. intval($this->id) . ' AND user='
										. $this->getUid()
										. ' AND isok<>1 FOR UPDATE');
				if ($row === NULL) {
					$success = FALSE;
					$error = '没有该返点开票申请或非自己创建或状态非可更新';
				} else {
					//更新老记录
					$result = $this->db
							->query(
									'UPDATE finance_rebate_invoice_pid SET isok=-1 WHERE rebate_invoice_id='
											. intval($this->id));
					if ($result === FALSE) {
						$success = FALSE;
						$error = '更新返点开票申请出错，错误代码1';
					}
				}
			}

			if ($success) {
				$subsql = array();
				$sum_invoice_amount = 0;
				$pid_arrays = $this->pid_array;
				foreach ($pid_arrays as $pid => $amount) {
					$pid = explode('_', $pid);
					$subsql[] = '("rebate_invoice_id","' . $pid[0] . '",'
							. $pid[1] . ',' . $amount . ',1)'; //(rebate_invoice_id,pid,paycostid,amount,isok)
					$sum_invoice_amount += $amount;
				}

				$invoice_id = $action === 'update' ? $row->invoice_id
						: $this
								->getSequence(
										date('y', time())
												. $this->getCity_show()
												. date('m', time()) . 'RI');
				if ($invoice_id !== FALSE) {
					if ($action === 'add') {
						$result = $this->db
								->query(
										'INSERT INTO finance_rebate_invoice(invoice_id,media_name,amount,invoice_type,media_payment_type,media_rebate_rate,relation_type,d1,d2,d3,title,content,remark,user,addtime,isok) VALUE("'
												. $invoice_id . '","'
												. $this->media_name . '",'
												. $sum_invoice_amount . ','
												. intval($this->invoice_type)
												. ','
												. intval(
														$this
																->media_payment_type)
												. ','
												. $this->media_rebate_rate
												. ','
												. intval($this->relation_type)
												. ','
												. (intval($this->invoice_type)
														=== 2 ? '"' . $this->d1
																. '"' : '""')
												. ','
												. (intval($this->invoice_type)
														=== 2 ? '"' . $this->d2
																. '"' : '""')
												. ','
												. (intval($this->invoice_type)
														=== 2 ? '"' . $this->d3
																. '"' : '""')
												. ',"' . $this->title . '","'
												. $this->content . '","'
												. $this->remark . '",'
												. $this->getUid() . ',now(),0)');

					} else {
						$result = $this->db
								->query(
										'UPDATE finance_rebate_invoice SET media_name="'
												. $this->media_name
												. '",amount='
												. $sum_invoice_amount
												. ',invoice_type='
												. intval($this->invoice_type)
												. ',media_payment_type='
												. intval(
														$this
																->media_payment_type)
												. ',media_rebate_rate='
												. $this->media_rebate_rate
												. ',relation_type='
												. intval($this->relation_type)
												. ',d1='
												. (intval($this->invoice_type)
														=== 2 ? '"' . $this->d1
																. '"' : '""')
												. ',d2='
												. (intval($this->invoice_type)
														=== 2 ? '"' . $this->d2
																. '"' : '""')
												. ',d3='
												. (intval($this->invoice_type)
														=== 2 ? '"' . $this->d3
																. '"' : '""')
												. ',title="' . $this->title
												. '",content="'
												. $this->content . '",remark="'
												. $this->remark
												. '",isok=0,auditmsg=null WHERE id='
												. intval($this->id));

					}

					if ($result === FALSE) {
						$success = FALSE;
						$error = '申请返点发票出错，错误代码1';
					} else {
						$rebate_invoice_id = $action === 'add' ? $this->db
										->insert_id : $this->id;
						$result = $this->db
								->query(
										'INSERT INTO finance_rebate_invoice_pid(rebate_invoice_id,pid,paycostid,amount,isok) VALUES'
												. str_replace(
														'"rebate_invoice_id"',
														$rebate_invoice_id,
														implode(',', $subsql)));
						if ($result === FALSE) {
							$success = FALSE;
							$error = '申请返点发票出错，错误代码2';
						}
					}
				} else {
					$success = FALSE;
					$error = '生成返点发票ID出错';
				}
			}
			if ($success) {
				$this->db->query('COMMIT');
			} else {
				$this->db->query('ROLLBACK');
			}
			return array('status' => $success ? 'success' : 'error',
					'message' => $success ? '返点申请开票成功，等待财务部审核' : $error);
		}
		return array('status' => 'error', 'message' => $this->errors);
	}

	public function getRebateInvoiceAuditResult($action) {
		if ($this->has_finance_rebate_permission) {
			if ($this->validate_form_value($action . '_invoice')) {
				$success = TRUE;
				$error = '';
				$this->db->query('BEGIN');

				$result = $this->db
						->query(
								'UPDATE finance_rebate_invoice SET isok='
										. ($action === 'pass' ? 1 : 2)
										. ($action === 'reject' ? ',auditmsg="'
														. $this->auditmsg . '"'
												: '') . ' WHERE id='
										. intval($this->id));
				if ($result === FALSE) {
					$success = FALSE;
					$error = '审核返点开票申请失败';
				}

				if ($success) {
					$this->db->query('COMMIT');
				} else {
					$this->db->query('ROLLBACK');
				}
				return array('status' => $success ? 'success' : 'error',
						'message' => $success ? '审核返点开票成功' : $error);
			}
			return array('status' => 'error', 'message' => $this->errors);
		}
		return array('status' => 'error', 'message' => NO_RIGHT_TO_DO_THIS);
	}

	public function getRebateInvoiceGDResult($action) {
		if ($this->has_finance_rebate_permission) {
			if ($this->validate_form_value('gd_' . $action)) {
				$success = TRUE;
				$error = '';
				$this->db->query('BEGIN');

				if ($action === 'invoice') {
					$sql = 'INSERT INTO finance_rebate_invoice_gd(rebate_invoice_id,gdtype,number,date,amount,gduser,gdtime) VALUE('
							. intval($this->id) . ',1,"' . $this->number
							. '","' . $this->date . '",' . $this->amount . ','
							. $this->getUid() . ',now())';
				} else if ($action === 'collection') {
					$sql = 'INSERT INTO finance_rebate_invoice_gd(rebate_invoice_id,gdtype,bank,date,amount,gduser,gdtime) VALUE('
							. intval($this->id) . ',2,' . intval($this->bank)
							. ',"' . $this->date . '",' . $this->amount . ','
							. $this->getUid() . ',now())';
				}

				$result = $this->db->query($sql);
				if ($result === FALSE) {
					$success = FALSE;
					$error = '返点开票归档失败';
				}

				if ($success) {
					$this->db->query('COMMIT');
				} else {
					$this->db->query('ROLLBACK');
				}
				return array('status' => $success ? 'success' : 'error',
						'message' => $success ? '返点开票归档成功' : $error);
			}
			return array('status' => 'error', 'message' => $this->errors);
		}
		return array('status' => 'error', 'message' => NO_RIGHT_TO_DO_THIS);
	}

	private static function _get_media_payment_type($type) {
		if (intval($type) === 1) {
			return '付现';
		} else if (intval($type) === 2) {
			return '抵应付账款';
		}
		return '';
	}

	/*
	private static function _get_relation_type($relation_type){
	    switch($relation_type){
	        case '1':
	            return '关联执行单';
	        case '2':
	            return '关联客户';
	        case '3':
	            return '关联付款申请';
	        case '4':
	            return '关联媒体+执行时间段';
	        case '5':
	            return '上传文件';
	        default:
	            return '';
	    }
	}
	 */

	private static function _get_invoice_type($invoice_type) {
		switch ($invoice_type) {
		case '1':
			return '普票';
		case '2':
			return '增票';
		default:
			return '';
		}
	}

	private static function _get_audit_status($isok) {
		switch ($isok) {
		case '0':
			return '<font color="#ff6600"><b>等待审核</b></font>';
		case '1':
			return '<font color="green"><b>审核通过</b></font>';
		case '2':
			return '<font color="red"><b>已驳回</b></font>';
		default:
			return '';
		}
	}

	private function _getRebateInvoiceInfo($row, $type) {
		$pids = array();
		$results = $this->db
				->get_results(
						'SELECT a.pid,a.paycostid,a.amount,b.payname FROM finance_rebate_invoice_pid a LEFT JOIN executive_paycost b ON a.paycostid=b.id WHERE a.rebate_invoice_id='
								. intval($this->id) . ' AND a.isok=1');
		if ($results !== NULL) {
			foreach ($results as $result) {
				$pids[] = $result->pid . '_' . $result->paycostid . '_'
						. $result->amount . '_' . $result->payname;
			}
		}
		$buf = file_get_contents(
				TEMPLATE_PATH . 'finance/rebate/rebate_invoice_info.tpl');
		return str_replace(
				array('[MEDIANAME]', '[MEDIAPAYMENTTPE]', '[MEDIAREBATERATE]',
						'[INVOICEAMOUNT]', '[INVOICETYPE]', '[	ZPSHOW]',
						'[D1]', '[D2]', '[D3]', '[TITLE]', '[CONTENT]',
						'[REMARK]', '[PIDS]', '[AUDITSTATUS]', '[AUDITMSG]'),
				array($row->media_name,
						self::_get_media_payment_type($row->media_payment_type),
						$row->media_rebate_rate, $row->amount,
						self::_get_invoice_type($row->invoice_type),
						($row->invoice_type === '1' ? 'none' : 'block'),
						$row->d1, $row->d2, $row->d3, $row->title,
						$row->content, Format_Util::format_html($row->remark),
						implode(',', $pids),
						self::_get_audit_status($row->isok),
						($type === 'audit' ? '<textarea name="auditmsg" id="auditmsg" rows="5" cols="50" class="validate[optional,maxSize[200]]"></textarea>'
								: Format_Util::format_html($row->auditmsg))),
				$buf);
	}

	public function getRebateInvoiceByIDHtml() {
		$sql = 'SELECT * FROM finance_rebate_invoice WHERE id='
				. intval($this->id);
		if (!$this->has_finance_rebate_permission) {
			$sql .= ' AND user=' . $this->getUid();
		}
		$row = $this->db->get_row($sql);
		if ($row !== NULL) {
			$buf = file_get_contents(
					TEMPLATE_PATH . 'finance/rebate/rebate_invoice_view.tpl');
			return str_replace(
					array('[LEFT]', '[TOP]', '[VCODE]', '[REBATEINVOICEINFO]',
							'[BASE_URL]'),
					array($this->get_left_html(), $this->get_top_html(),
							$this->get_vcode(),
							$this->_getRebateInvoiceInfo($row, 'view'),
							BASE_URL), $buf);
		}
		return User::no_object('没有该申请或者无权限查看');
	}

	public function getRebateInvoiceAuditHtmlByID() {
		if ($this->has_finance_rebate_permission) {
			$row = $this->db
					->get_row(
							'SELECT * FROM finance_rebate_invoice WHERE id='
									. intval($this->id) . ' AND isok=0');
			if ($row !== NULL) {
				$buf = file_get_contents(
						TEMPLATE_PATH
								. 'finance/rebate/rebate_invoice_audit.tpl');
				return str_replace(
						array('[LEFT]', '[TOP]', '[VCODE]',
								'[REBATEINVOICEINFO]', '[ID]', '[BASE_URL]'),
						array($this->get_left_html(), $this->get_top_html(),
								$this->get_vcode(),
								$this->_getRebateInvoiceInfo($row, 'audit'),
								intval($this->id), BASE_URL), $buf);
			}
			return User::no_object('没有该申请或非需审核状态');
		}
		return User::no_permission();
	}

	public function getRebateInvoiceGDHtmlByID() {
		if ($this->has_finance_rebate_permission) {
			$row = $this->db
					->get_row(
							'SELECT * FROM finance_rebate_invoice WHERE id='
									. intval($this->id) . ' AND isok=1');
			if ($row !== NULL) {
				$buf = file_get_contents(
						TEMPLATE_PATH . 'finance/rebate/rebate_invoice_gd.tpl');
				return str_replace(
						array('[LEFT]', '[TOP]', '[VCODE]',
								'[REBATEINVOICEINFO]', '[ID]', '[BANKLIST]',
								'[BASE_URL]'),
						array($this->get_left_html(), $this->get_top_html(),
								$this->get_vcode(),
								$this->_getRebateInvoiceInfo($row, 'gd'),
								intval($this->id),
								Nim_BankInfo::get_bank_account_list(),
								BASE_URL), $buf);
			}
			return User::no_object('没有该申请或非需归档状态');
		}
		return User::no_permission();
	}

	public function getRebateInvoiceEditHtmlByID() {
		$row = $this->db
				->get_row(
						'SELECT * FROM finance_rebate_invoice WHERE id='
								. intval($this->id) . ' AND user='
								. $this->getUid() . ' AND isok<>1');
		if ($row !== NULL) {

			$pids = array();
			$results = $this->db
					->get_results(
							'SELECT a.pid,a.paycostid,a.amount,b.payname FROM finance_rebate_invoice_pid a LEFT JOIN executive_paycost b ON a.paycostid=b.id WHERE a.rebate_invoice_id='
									. intval($this->id) . ' AND a.isok=1');
			if ($results !== NULL) {
				foreach ($results as $result) {
					$pids[] = $result->pid . '_' . $result->paycostid;
				}
			}

			$buf = file_get_contents(
					TEMPLATE_PATH . 'finance/rebate/rebate_invoice_edit.tpl');
			return str_replace(
					array('[LEFT]', '[TOP]', '[VCODE]', '[MEDIANAME]',
							'[PAYMENTTYPECHECK1]', '[PAYMENTTYPECHECK2]',
							'[MEDIAREBATERATE]', '[INVOICEAMOUNT]',
							'[INVOICETYPECHECK1]', '[INVOICETYPECHECK2]',
							'[D1]', '[D2]', '[D3]', '[ZPSHOW]', '[TITLE]',
							'[CONTENT]', '[REMARK]', '[ID]', '[PIDS]',
							'[BASE_URL]'),
					array($this->get_left_html(), $this->get_top_html(),
							$this->get_vcode(), $row->media_name,
							$row->media_payment_type === '1' ? 'checked' : '',
							$row->media_payment_type === '2' ? 'checked' : '',
							$row->media_rebate_rate, $row->amount,
							$row->invoice_type === '1' ? 'checked' : '',
							$row->invoice_type === '2' ? 'checked' : '',
							$row->d1, $row->d2, $row->d3,
							$row->invoice_type === '1' ? 'none' : 'block',
							$row->title, $row->content, $row->remark,
							intval($this->id), ',' . implode(',', $pids) . ',',
							BASE_URL), $buf);
		}
		return User::no_object('没有该申请或非可修改状态');
	}

	public function getRebateInvoiceApplyTransferResult() {
		$ttype = $this->ttype;
		if ($this->validate_form_value($ttype)) {
			$success = TRUE;
			$error = '';
			$this->db->query('BEGIN');

			$row = $this->db
					->get_row(
							'SELECT id FROM finance_rebate_invoice WHERE id='
									. intval($this->id)
									. ' AND isok=1 AND media_payment_type='
									. ($ttype === 'receive2pay' ? 1 : 2)
									. ' FOR UPDATE');
			if ($row === NULL) {
				$success = FALSE;
				$error = '没有该申请或状态非可转换';
			} else {
				//检查是否已申请
				$row = $this->db
						->get_row(
								'SELECT id FROM finance_rebate_transfer_apply WHERE rebate_invoice_id='
										. intval($this->id)
										. ' AND isok=1 FOR UPDATE');
				if ($row !== NULL) {
					$success = FALSE;
					$error = '已提交申请，请等待财务部处理';
				} else {
					$result = $this->db
							->query(
									'INSERT INTO finance_rebate_transfer_apply(rebate_invoice_id,goal_type,apply_time,isok) VALUE('
											. intval($this->id) . ','
											. ($ttype === 'receive2pay' ? 2 : 1)
											. ',now(),1)');
					if ($result === FALSE) {
						$success = FALSE;
						$error = '申请转换失败';
					}
				}
			}
			if ($success) {
				$this->db->query('COMMIT');
			} else {
				$this->db->query('ROLLBACK');
			}
			return array('status' => $success ? 'success' : 'error',
					'message' => $success ? '申请转换成功，等待财务部处理' : $error);
		}
		return array('status' => 'error', 'message' => $this->errors);
	}

	public function getRebateInvoicePay2ReceiveResult() {
		if ($this->has_finance_rebate_permission) {
			if ($this->validate_form_value('dopay2receive')) {
				$success = TRUE;
				$error = '';
				$this->db->query('BEGIN');

				//finance_rebate_invoice_pid
				$result = $this->db
						->query(
								'UPDATE finance_rebate_invoice_pid SET isok=-1 WHERE rebate_invoice_id='
										. intval($this->id));
				if ($result === FALSE) {
					$success = FALSE;
					$error = '应付转应收失败，错误代码1';
				} else {
					$subsql = array();
					$sum_invoice_amount = 0;
					$pid_arrays = $this->pid_array;
					foreach ($pid_arrays as $pid => $amount) {
						$pid = explode('_', $pid);
						$subsql[] = '(' . intval($this->id) . ',"' . $pid[0]
								. '",' . $pid[1] . ',' . $amount . ',1)';
						$sum_invoice_amount += $amount;
					}

					$result = $this->db
							->query(
									'INSERT INTO finance_rebate_invoice_pid(rebate_invoice_id,pid,paycostid,amount,isok) VALUES'
											. implode(',', $subsql));
					if ($result === FALSE) {
						$success = FALSE;
						$error = '应付转应收失败，错误代码2';
					} else {
						//finance_rebate_invoice
						$result = $this->db
								->query(
										'UPDATE finance_rebate_invoice SET amount='
												. $sum_invoice_amount
												. ',media_payment_type=1 WHERE id='
												. intval($this->id));
						if ($result === FALSE) {
							$success = FALSE;
							$error = '应付转应收失败，错误代码3';
						} else {
							//finance_rebate_transfer_apply
							$result = $this->db
									->query(
											'UPDATE finance_rebate_transfer_apply SET isok=-1 WHERE rebate_invoice_id='
													. intval($this->id)
													. ' AND isok=1');
							if ($result === FALSE) {
								$success = FALSE;
								$error = '应付转应收失败，错误代码4';
							}
						}
					}
				}

				if ($success) {
					$this->db->query('COMMIT');
				} else {
					$this->db->query('ROLLBACK');
				}
				return array('status' => $success ? 'success' : 'error',
						'message' => $success ? '应付转应收成功' : $error);
			}
			return array('status' => 'error', 'message' => $this->errors);
		}
		return array('status' => 'error', 'message' => NO_RIGHT_TO_DO_THIS);
	}

	public function getRebateInvoiceReceive2PayResult() {
		if ($this->has_finance_rebate_permission) {
			if ($this->validate_form_value('doreceive2pay')) {
				$success = TRUE;
				$error = '';
				$this->db->query('BEGIN');

				//finance_rebate_invoice_pid
				$result = $this->db
						->query(
								'UPDATE finance_rebate_invoice_pid SET isok=-1 WHERE rebate_invoice_id='
										. intval($this->id));
				if ($result === FALSE) {
					$success = FALSE;
					$error = '应收转应付失败，错误代码1';
				} else {
					$subsql = array();
					$sum_invoice_amount = 0;
					$pid_arrays = $this->pid_array;
					foreach ($pid_arrays as $pid => $amount) {
						$pid = explode('_', $pid);
						$subsql[] = '(' . intval($this->id) . ',"' . $pid[0]
								. '",' . $pid[1] . ',' . $amount . ',1)';
						$sum_invoice_amount += $amount;
					}

					$result = $this->db
							->query(
									'INSERT INTO finance_rebate_invoice_pid(rebate_invoice_id,pid,paycostid,amount,isok) VALUES'
											. implode(',', $subsql));
					if ($result === FALSE) {
						$success = FALSE;
						$error = '应收转应付失败，错误代码2';
					} else {
						//finance_rebate_invoice
						$result = $this->db
								->query(
										'UPDATE finance_rebate_invoice SET amount='
												. $sum_invoice_amount
												. ',media_payment_type=2 WHERE id='
												. intval($this->id));
						if ($result === FALSE) {
							$success = FALSE;
							$error = '应收转应付失败，错误代码3';
						} else {
							//finance_rebate_transfer_apply
							$result = $this->db
									->query(
											'UPDATE finance_rebate_transfer_apply SET isok=-1 WHERE rebate_invoice_id='
													. intval($this->id)
													. ' AND isok=1');
							if ($result === FALSE) {
								$success = FALSE;
								$error = '应收转应付失败，错误代码4';
							}
						}
					}
				}

				if ($success) {
					$this->db->query('COMMIT');
				} else {
					$this->db->query('ROLLBACK');
				}
				return array('status' => $success ? 'success' : 'error',
						'message' => $success ? '应收转应付成功' : $error);
			}
			return array('status' => 'error', 'message' => $this->errors);
		}
		return array('status' => 'error', 'message' => NO_RIGHT_TO_DO_THIS);
	}
}
