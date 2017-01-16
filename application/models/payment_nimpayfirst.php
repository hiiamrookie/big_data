<?php
class Payment_Nimpayfirst extends User {
	private $custom_name;
	private $starttime;
	private $endtime;
	private $paymentype;
	private $media_name;
	private $paytime;
	private $payamount;
	private $type;

	//发邮件相关
	private $pids;
	private $to_email;
	private $cc_email;

	private $errors = array();

	private $has_nimpayfirst_permission = FALSE;

	public function __construct($fields = array()) {
		parent::__construct();
		if (!empty($fields)) {
			foreach ($this as $key => $value) {
				if ($fields[$key] !== NULL
						&& !in_array($key, array('has_nimpayfirst_permission'),
								TRUE)) {
					$this->$key = $fields[$key];
				}
			}
		}
		if (in_array($this->getUsername(),
				$GLOBALS['manager_finance_permission'], TRUE)
				|| intval($this->getBelong_dep()) === 2) {
			$this->has_nimpayfirst_permission = TRUE;
		}
	}

	public function get_nimpayfirst_list_html() {
		if ($this->has_nimpayfirst_permission) {
			$buf = file_get_contents(
					TEMPLATE_PATH
							. 'finance/payment/payment_nimpayfirst_list.tpl');
			return str_replace(
					array('[LEFT]', '[TOP]', '[VCODE]', '[BASE_URL]'),
					array($this->get_left_html(), $this->get_top_html(),
							$this->get_vcode(), BASE_URL), $buf);
		} else {
			User::no_permission();
		}
	}

	public function get_nimpayfirst_html() {
		if ($this->has_nimpayfirst_permission) {
			$sumamount = $this->db
					->get_var(
							'SELECT SUM(payfirst_amount) FROM finance_payment_payfirst WHERE status=1');
			$collection_amount = $this->db
					->get_var(
							'SELECT SUM(collection_amount) FROM finance_payment_collection');
			$buf = file_get_contents(
					TEMPLATE_PATH . 'finance/payment/payment_nimpayfirst.tpl');
			return str_replace(
					array('[LEFT]', '[TOP]', '[VCODE]', '[SUMAMOUNT]',
							'[SUMUNBACKAMOUNT]', '[BASE_URL]'),
					array($this->get_left_html(), $this->get_top_html(),
							$this->get_vcode(),
							Format_Util::my_money_format('%.2n', $sumamount),
							Format_Util::my_money_format('%.2n',
									$sumamount - $collection_amount), BASE_URL),
					$buf);
		} else {
			User::no_permission();
		}
	}

	public function search_payfirst() {
		if ($this->type === 'customer') {
			//执行单已收合同款
			$sum_receive_amount = array();
			$results = $this->db
					->get_results(
							'SELECT SUM(amount) AS amount,pid FROM finance_receivables WHERE isok=1 GROUP BY pid');
			if ($results !== NULL) {
				foreach ($results as $result) {
					$sum_receive_amount[$result->pid] = $result->amount;
				}
			}

			//合同已收保证金情况
			$sum_receive_deposit_amount = array();
			$results = $this->db
					->get_results(
							'SELECT SUM(amount) AS amount,cid FROM finance_deposit_receivables WHERE isok=1 GROUP BY cid');
			if ($results !== NULL) {
				foreach ($results as $result) {
					$sum_receive_deposit_amount[$result->cid] = $result->amount;
				}
			}

			$where = array();
			if (!empty($this->starttime)) {
				$where[] = 'm.addtime >="' . $this->starttime . ' 00:00:00"';
			}
			if (!empty($this->endtime)) {
				$where[] = 'm.addtime <="' . $this->endtime . ' 23:59:59"';
			}
			if (!empty($this->custom_name)) {
				$where[] = 'n.cusname LIKE "%' . $this->custom_name . '%"';
			}
			$s = '<table width="100%"><tr><td>客户名称</td><td>已垫款总额</td><td>已回款总额</td><td width="100px">&nbsp;</td></tr>';
			$results = $this->db
					->get_results(
							'SELECT m.*,n.cusname
FROM 
(
SELECT a.payfirst_amount,b.pid,(SELECT SUBSTRING_INDEX(b.pid,"-",1)) AS cid ,a.addtime
FROM finance_payment_payfirst a
LEFT JOIN finance_payment_person_apply_list b
ON a.apply_id=b.apply_id AND a.list_id=b.id
WHERE a.payment_type=1 AND a.amount_type=1 AND a.status=1
) m
LEFT JOIN contract_cus n
ON m.cid=n.cid
WHERE 1=1' . (!empty($where) ? ' AND ' . implode(' AND ', $where) : ''));
			$data = array();
			if ($results !== NULL) {
				foreach ($results as $result) {
					if (!in_array($result->pid,
							$data['cusname_pid'][$result->cusname], TRUE)) {
						$data['cusname_pid'][$result->cusname][] = $result->pid;
					}
					if (!in_array($result->cid,
							$data['cusname_cid'][$result->cusname], TRUE)) {
						$data['cusname_cid'][$result->cusname][] = $result->cid;
					}
					if (empty($data['payfirst_amount'][$result->cusname])) {
						$data['payfirst_amount'][$result->cusname] = $result
								->payfirst_amount;
					} else {
						$data['payfirst_amount'][$result->cusname] += $result
								->payfirst_amount;
					}
				}
			}

			$payfirst_amount = $data['payfirst_amount'];
			if (count(array_keys($payfirst_amount)) > 0) {
				foreach (array_keys($payfirst_amount) as $value) {
					$sum = 0;
					$cusname_pids = $data['cusname_pid'][$value];
					foreach ($cusname_pids as $cusname_pid) {
						$sum += $sum_receive_amount[$cusname_pid];
					}
					$cusname_cids = $data['cusname_cid'][$value];
					foreach ($cusname_cids as $cusname_cid) {
						$sum += $sum_receive_deposit_amount[$cusname_cid];
					}
					//$s .= '<tr><td>' . $value . '</td><td>' . $payfirst_amount[$value] . '</td><td>' . $sum . '</td><td><input type="button" class="btn" value="展开" onclick="javascript:openitit(\'' . $value . '\');"/>&nbsp;<input type="button" class="btn" value="打印"/></td></tr>';
					$s .= '<tr><td>' . $value . '</td><td>'
							. $payfirst_amount[$value] . '</td><td>' . $sum
							. '</td><td><input type="button" class="btn" value="展开" onclick="javascript:openitit(\''
							. $value . '\');"/></td></tr>';
				}
			} else {
				$s .= '<tr><td colspan="4"><font color="red"><b>没有搜索结果！</b></font></td></tr>';
			}

			$s .= '</table>';

		} else if ($this->type === 'apply') {
			//个人申请合同款
			$sql1 = 'SELECT a.apply_id,a.list_id,a.payfirst_amount,b.payment_amount_real,b.payment_date,b.addtime,c.media_name,\'pc\' AS stype FROM
finance_payment_payfirst a
LEFT JOIN finance_payment_person_apply b
ON a.apply_id=b.id
LEFT JOIN finance_payment_media_info c
ON b.media_info_id=c.id
WHERE a.payment_type=1 AND a.amount_type=1 AND a.status=1';

			//个人申请保证金
			$sql2 = 'SELECT a.apply_id,a.list_id,a.payfirst_amount,b.payment_amount_real,b.payment_date,b.addtime,c.media_name,\'pd\' AS stype FROM
finance_payment_payfirst a
LEFT JOIN finance_payment_person_deposit_apply b
ON a.apply_id=b.id
LEFT JOIN finance_payment_media_info c
ON b.media_info_id=c.id
WHERE a.payment_type=1 AND a.amount_type=2 AND a.status=1';

			//媒体批量申请合同款
			$sql3 = 'SELECT a.apply_id,a.list_id,a.payfirst_amount,b.payment_amount_real,b.payment_date,b.addtime,c.media_name,\'mc\' AS stype FROM
finance_payment_payfirst a
LEFT JOIN finance_payment_media_apply b
ON a.apply_id=b.id
LEFT JOIN finance_payment_media_info c
ON b.media_info_id=c.id
WHERE a.payment_type=2 AND a.amount_type=1 AND a.status=1';

			//媒体批量申请保证金
			$sql4 = 'SELECT a.apply_id,a.list_id,a.payfirst_amount,b.payment_amount_real,b.payment_date,b.addtime,c.media_name,\'md\' AS stype FROM
finance_payment_payfirst a
LEFT JOIN finance_payment_media_deposit_apply b
ON a.apply_id=b.id
LEFT JOIN finance_payment_media_info c
ON b.media_info_id=c.id
WHERE a.payment_type=2 AND a.amount_type=2 AND a.status=1';

			$where = array();
			if (!empty($this->media_name)) {
				$where[] = 'z.media_name LIKE "%' . $this->media_name . '%"';
			}
			if (!empty($this->paytime)) {
				$where[] = 'z.payment_date="' . $this->paytime . '"';
			}
			if (!empty($this->payamount)) {
				$where[] = 'z.payment_amount_real=' . $this->payamount;
			}
			if (intval($this->paymentype) === 1) {
				//合同款
				$sql = 'SELECT SUM(payfirst_amount) AS payfirst_amount,z.apply_id,z.payment_date,z.addtime,z.media_name,z.payment_amount_real,z.stype FROM ('
						. $sql1 . ' UNION ALL ' . $sql3 . ') z'
						. (!empty($where) ? ' WHERE '
										. implode(' AND ', $where) : '')
						. ' GROUP BY apply_id,stype';
			} else if (intval($this->paymentype) === 2) {
				//保证金
				$sql = 'SELECT SUM(payfirst_amount) AS payfirst_amount,z.apply_id,z.payment_date,z.addtime,z.media_name,z.payment_amount_real,z.stype FROM ('
						. $sql2 . ' UNION ALL ' . $sql4 . ') z'
						. (!empty($where) ? ' WHERE '
										. implode(' AND ', $where) : '')
						. ' GROUP BY apply_id,stype';
			}
			//var_dump($sql);
			$s = '<table width="100%"><tr><td>申请付款时间</td><td>约定付款时间</td><td>媒体名称</td><td>'
					. (intval($this->paymentype) === 1 ? '合同' : '保证金')
					. '付款金额</td><td>垫付金额</td><td>&nbsp;</td></tr>';
			$results = $this->db->get_results($sql);
			if ($results !== NULL) {
				foreach ($results as $result) {
					$s .= '<tr><td>' . $result->addtime . '</td><td>'
							. $result->payment_date . '</td><td>'
							. $result->media_name . '</td><td>'
							. $result->payment_amount_real . '</td><td>'
							. $result->payfirst_amount
							. '</td><td><input type="button" class="btn" value="展开" onclick="javascript:openit('
							. $result->apply_id . ',\'' . $result->stype
							. '\')"/></td>';
				}
			} else {
				$s .= '<tr><td colspan="6"><font color="red"><b>没有搜索结果！</b></font></td></tr>';
			}
			$s .= '</table>';
		}
		return $s;
	}

	private function validate_form_value($action) {
		$errors = array();
		if (in_array($action, array('send_remind_email'), TRUE)) {
			if (!self::validate_field_not_empty($this->pids)
					|| !self::validate_field_not_null($this->pids)) {
				$errors[] = '所选执行单不能为空';
			}

			$to_email = $this->to_email;
			if (!self::validate_field_not_empty($to_email)
					|| !self::validate_field_not_null($to_email)) {
				$errors[] = '收件人邮件地址不能为空';
			} else {
				$to_email = explode(',', $to_email);
				foreach ($to_email as $email) {
					if (!Validate_Util::my_is_email($email, TRUE)) {
						$errors[] = $email . ' 不是有效邮件地址';
					}
				}
			}

			$cc_email = $this->cc_email;
			if (!empty($cc_email)) {
				$cc_email = explode(',', $cc_email);
				foreach ($cc_email as $email) {
					if (!Validate_Util::my_is_email($email, TRUE)) {
						$errors[] = $email . ' 不是有效邮件地址';
					}
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

	public function sendPayfirstRemindEmail() {
		if ($this->has_nimpayfirst_permission) {
			if ($this->validate_form_value('send_remind_email')) {
				$success = TRUE;
				$error = '';
				//$this->db->query('BEGIN');

				$body = '以下执行单垫付款请尽早回款：' . $this->pids;
				$mail = new PHPMailer();
				$body = eregi_replace('[\]', '', $body); //对邮件内容进行必要的过滤
				$mail->CharSet = 'UTF-8'; //设定邮件编码，默认ISO-8859-1，如果发中文此项必须设置，否则乱码
				$mail->IsSMTP(); // 设定使用SMTP服务
				$mail->SMTPDebug = 1; // 启用SMTP调试功能
				// 1 = errors and messages
				// 2 = messages only
				$mail->SMTPAuth = true; // 启用 SMTP 验证功能
				//$mail->SMTPSecure = "ssl";          // 安全协议
				$mail->Host = 'smtp.nimdigital.com'; // SMTP 服务器
				$mail->Port = 25; // SMTP服务器的端口号
				$mail->Username = 'info@nimdigital.com'; // SMTP服务器用户名
				$mail->Password = 'nimdigital.com'; // SMTP服务器密码
				$mail->SetFrom('info@nimdigital.com', '大数据系统');
				$mail->AddReplyTo('info@nimdigital.com', '大数据系统');
				$mail->Subject = '执行单垫款回款提醒';
				$mail->MsgHTML($body);

				$to_email = $this->to_email;
				$cc_email = $this->cc_email;
				$mail->AddAddress($this->to_email, '');
				if (!empty($this->cc_email)) {
					$mail->AddCC($this->cc_email);
				}

				if (!$mail->Send()) {
					$success = FALSE;
					$error = '发送提醒邮件失败';

				}

				//if ($success) {
				//	$this->db->query('COMMIT');
				//} else {
				//	$this->db->query('ROLLBACK');
				//}

				return array('status' => $success ? 'success' : 'error',
						'message' => $success ? '发送提醒邮件成功' : $error);
			}
			return array('status' => 'error', 'message' => $this->errors);
		}
		return array('status' => 'error', 'message' => NO_RIGHT_TO_DO_THIS);
	}
}
