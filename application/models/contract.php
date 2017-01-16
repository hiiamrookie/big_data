<?php
class Contract extends User {
	private $contract_id;
	private $type;
	private $type1;
	private $contractname;
	private $cusname;
	private $cuscontact;
	private $execution;
	private $city;
	private $dep;
	private $team;
	private $contactperson;
	private $contactcontent;
	private $signdate;
	private $starttime;
	private $endtime;
	private $monitoringsystem;
	private $contractamount;
	private $billtype;
	private $rebateproportion;
	private $bzjpaymentmethod;
	private $contractamountpayment;
	private $specialcaluse;
	private $remark;
	private $dids;
	private $contractstatus;
	private $contractstatusreason;
	private $process;
	private $isfmkcid;
	private $fmkcid;
	private $cid;
	private $customertype;
	private $customer_id;

	private $daili_array = array();
	private $media_tf_array = array();
	private $service_array = array();
	private $bzj_array = array();
	private $errors = array();

	private $logs = array();

	private $daili_count = ',';
	private $var_daili_count = 1;
	private $media_count = ',';
	private $var_media_count = 1;
	private $service_count = ',';
	private $var_service_count = 1;
	private $baozhengjin_count = ',';
	private $var_baozhengjin_count = 1;

	private $audit_pass;

	private $has_contract_permission = FALSE;

	private $has_cancel_contract_permission = FALSE;

	/**
	 * @return the $has_contract_permission
	 */
	public function getHas_contract_permission() {
		return $this->has_contract_permission;
	}

	/**
	 * @return the $cid
	 */
	public function getCid() {
		return $this->cid;
	}

	/**
	 * @return the $has_cancel_contract_permission
	 */
	public function getHas_cancel_contract_permission() {
		return $this->has_cancel_contract_permission;
	}

	public function __construct($fields = array(), $is_check = FALSE) {
		parent::__construct();
		if (!empty($fields)) {
			foreach ($this as $key => $value) {
				if ($fields[$key] !== NULL
						&& !in_array($key,
								array('has_contract_permission',
										'has_cancel_contract_permission'),
								TRUE)) {
					$this->$key = $fields[$key];
				}
			}
		}

		if ($this->getHas_check_contract_permission()
				|| $this
						->get_relation_contract_permission(
								intval($this->getBelong_city()),
								intval($this->getBelong_dep()),
								intval($this->getBelong_team()))) {
			$this->has_contract_permission = TRUE;
		} else if ($is_check && !$this->getHas_check_contract_permission()
				&& !$this
						->get_relation_contract_permission(
								intval($this->getBelong_city()),
								intval($this->getBelong_dep()),
								intval($this->getBelong_team()))) {
			if ($this->cid !== NULL) {
				$rows = $this->db
						->get_results(
								'SELECT id FROM executive WHERE cid="'
										. $this->cid . '" AND user='
										. intval($this->getUid()));
				if ($rows !== NULL) {
					$this->has_contract_permission = TRUE;
				} else {
					$row = $this->db
							->get_row(
									'SELECT pcid,step,contactperson,view FROM contract_cus WHERE cid="'
											. $this->cid . '"');
					if ($row !== NULL) {
						$contactperson = $row->contactperson;
						$view = $row->view;
						if (intval($contactperson) === intval($this->getUid())
								|| strpos($view, $this->getUsername())
										!== FALSE) {
							$this->has_contract_permission = TRUE;
						} else {
							$process = Process::getInstance();
							$process = $process['step'];
							$pmcode = $process[$row->pcid][$row->step]['content'][2];
							if (in_array($pmcode, $this->getPermissions())) {
								$this->has_contract_permission = TRUE;
							}
						}
					}
				}
			}
		}

		if (in_array($this->getUsername(),
				$GLOBALS['cancel_contract_permission'], TRUE)) {
			$this->has_cancel_contract_permission = TRUE;
		}

	}

	public function get_contract_process_html($pcid = NULL) {
		$process = Process::getInstance();
		$mod_process = $process['module'][2]; //合同流程
		$step_process = $process['step']; //流程步骤
		$result = '';
		$i = 0;
		foreach ($mod_process as $mprocesse) {
			$content = '';
			$tmp = $step_process[$mprocesse['id']];
			foreach ($tmp as $key => $t) {
				$content .= ($key !== 0 ? ' -> ' : '') . $t['content'][0];
			}
			$result .= '<li><input class="validate[required]" type="radio" name="process" value="'
					. $mprocesse['id'] . '" class="checkbox" '
					. ($pcid !== NULL
							&& intval($pcid) === intval($mprocesse['id']) ? 'checked="checked"'
							: '') . '><span style="display:none">' . $content
					. '</span><label>' . $mprocesse['name'] . '</label></li>';
			$i++;
		}
		return $result;
	}

	private function _get_contract_by_type($type) {
		return $this->db
				->get_results(
						'SELECT cid,contractcontent FROM contract_cus WHERE type='
								. intval($type) . ' ORDER BY cid');
	}

	/**
	 * 
	 * 得到框架合同的合同号名称list
	 */
	public function get_kuangja_contract_select_html($cid = NULL) {
		$contracts = $this->_get_contract_by_type(1);
		$s = '<option value="">请选择</option>';
		if ($contracts !== NULL) {
			foreach ($contracts as $contract) {
				$s .= '<option value="' . $contract->cid . '" '
						. ($cid == $contract->cid ? 'selected="selected"' : '')
						. ' >' . $contract->cid . ' '
						. $contract->contractcontent . '</option>';
			}
		}
		return $s;
	}

	private function validate_form_value($action) {
		$errors = array();
		if (in_array($action, array('add', 'update', 'audit', 'cancel'), TRUE)) {

			if ($action === 'cancel') {
				if (!self::validate_field_not_empty($this->cid)
						|| !self::validate_field_not_null($this->cid)) {
					$errors[] = '合同号不能为空';
				}
			} else if ($action === 'audit') {
				if (!in_array(intval($this->audit_pass), array(0, 1), TRUE)) {
					$errors[] = '客户审核选择有误';
				}

				if (intval($this->audit_pass) === 0
						&& (!self::validate_field_not_empty($this->remark)
								|| !self::validate_field_not_null($this->remark))) {
					$errors[] = '驳回信息必须输入';
				}
			} else {
				//客户合同类型
				if (!in_array(intval($this->type), array(1, 2), TRUE)) {
					$errors[] = '客户合同类型选择有误';
				}

				if (intval($this->type) === 2 && $this->isfmkcid
						&& intval($this->fmkcid) <= 0) {
					$errors[] = '请选择框架客户合同';
				}

				//直客 / 代理商
				if (!in_array(intval($this->type1), array(1, 2), TRUE)) {
					$errors[] = '直客或代理商类型选择有误';
				}

				if (intval($this->type1) === 2) {
					//代理商
					$dailis = $this->daili_array;
					if (!empty($dailis)) {
						foreach ($dailis as $key => $daili) {
							if (empty($daili['daili'])) {
								$errors[] = '第' . ($key + 1) . '个代理商信息必须输入';
							}
							if (empty($daili['ggz'])) {
								$errors[] = '第' . ($key + 1) . '个广告主信息必须输入';
							}
						}
					}
				}

				//合同名称
				if (!self::validate_field_not_empty($this->contractname)
						|| !self::validate_field_not_null($this->contractname)) {
					$errors[] = '合同名称不能为空';
				} else if (!self::validate_field_max_length(
						$this->contractname, 200)) {
					$errors[] = '合同名称长度最多200个字符';
				}

				//客户名称
				if (self::validate_field_not_empty($this->cusname)
						&& !self::validate_field_max_length($this->cusname, 200)) {
					$errors[] = '客户名称长度最多200个字符';
				}


				//客户联系方式
				if (self::validate_field_not_empty($this->cuscontact)
						&& !self::validate_field_max_length($this->cuscontact,
								500)) {
					$errors[] = '客户联系方式长度最多500个字符';
				}

				//约定执行确认方式
				if (self::validate_field_not_empty($this->execution)
						&& !self::validate_field_max_length($this->execution,
								200)) {
					$errors[] = '约定执行确认方式长度最多200个字符';
				}

				//地区
				if (!in_array(intval($this->city), array(1, 2, 3), TRUE)) {
					$errors[] = '地区选择有误';
				}

				//部门
				if (intval($this->dep) <= 0) {
					$errors[] = '部门选择有误';
				}

				//联系人
				if (intval($this->contactperson) <= 0) {
					$errors[] = '联系人不能为空';
				}

				//项目名称
				if (self::validate_field_not_empty($this->contactcontent)
						&& !self::validate_field_max_length(
								$this->contactcontent, 500)) {
					$errors[] = '项目名称长度最多500个字符';
				}

				//合同签订日期
				if (!self::validate_field_not_empty($this->signdate)
						|| !self::validate_field_not_null($this->signdate)) {
					$errors[] = '合同签订日期不能为空';
				} else if (strtotime($this->signdate) === FALSE) {
					$errors[] = '合同签订日期不是一个有效的时间值';
				}

				//合同执行日期
				if (!self::validate_field_not_empty($this->starttime)
						|| !self::validate_field_not_null($this->starttime)) {
					$errors[] = '合同执行开始日期不能为空';
				} else if (strtotime($this->starttime) === FALSE) {
					$errors[] = '合同执行开始日期不是一个有效的时间值';
				}

				if (!self::validate_field_not_empty($this->endtime)
						|| !self::validate_field_not_null($this->endtime)) {
					$errors[] = '合同执行结束日期不能为空';
				} else if (strtotime($this->endtime) === FALSE) {
					$errors[] = '合同执行结束日期不是一个有效的时间值';
				} else if (strtotime($this->endtime)
						- strtotime($this->starttime) < 0) {
					$errors[] = '合同执行开始日期必须早于结束日期';
				}

				//监测系统
				if (self::validate_field_not_empty($this->monitoringsystem)
						&& !self::validate_field_max_length(
								$this->monitoringsystem, 500)) {
					$errors[] = '监测系统名称长度最多500个字符';
				}

				//合同金额
				//				if (! self::validate_field_not_empty ( $this->contractamount ) || ! self::validate_field_not_null ( $this->contractamount )) {
				//					$errors [] = '合同金额不能为空';
				//				} else if (! self::validate_money ( $this->contractamount )) {
				//					$errors [] = '合同金额不是有效的金额数值';
				//				}

				if (!self::validate_field_not_null($this->contractamount)) {
					$errors[] = '合同金额不能为空';
				} else if (!self::validate_money($this->contractamount)) {
					$errors[] = '合同金额不是有效的金额数值';
				}

				//开票类型
				if (!in_array(intval($this->billtype), array(1, 2), TRUE)) {
					$errors[] = '开票类型选择有误';
				}

				//合同金额拆分	媒体投放
				$media_tf_array = $this->media_tf_array;
				if (!empty($media_tf_array)) {
					foreach ($media_tf_array as $key => $media) {
						if (empty($media['media'])) {
							$errors[] = '第' . ($key + 1) . '个媒体信息必须输入';
						}
						//						if (empty ( $media ['amount'] )) {
						//							$errors [] = '第' . ($key + 1) . '个金额信息必须输入';
						//						}
						//if (empty ( $media ['advformat'] )) {
						//	$errors [] = '第' . ($key + 1) . '个广告形式及优惠政策必须输入';
						//}
					}
				}

				//合同金额拆分	服务内容
				$service_array = $this->service_array;
				if (!empty($service_array)) {
					foreach ($service_array as $key => $service) {
						if (intval($service['cftype']) <= 0) {
							$errors[] = '第' . ($key + 1) . '个服务费用类型选择有误';
						}

						//						if (empty ( $service ['serviceamount'] )) {
						//							$errors [] = '第' . ($key + 1) . '个服务费用金额信息必须输入';
						//						}
					}
				}

				//返点比例
				if ($this->rebateproportion === '') {
					$this->rebateproportion = 0;
				}
				if ($this->rebateproportion < 0
						|| $this->rebateproportion > 100) {
					$errors[] = '返点比例输入有误';
				}

				//保证金支付
				$bzj_array = $this->bzj_array;
				if (!empty($bzj_array)) {
					foreach ($bzj_array as $kry => $bzj) {
						if (empty($bzj['media'])) {
							$errors[] = '第' . ($key + 1) . '个保证金媒体信息必须输入';
						}
						if ($bzj['bl'] < 0 || $bzj['bl'] > 100) {
							$errors[] = '第' . ($key + 1) . '个保证金比例输入有误';
						}
						if (!self::validate_money($bzj['amount'])) {
							$errors[] = '第' . ($key + 1) . '个保证金金额输入有误';
						}
					}
				}

				//合同状态
				if (!in_array(intval($this->contractstatus), array(1, 2), TRUE)) {
					$errors[] = '合同状态选择有误';
				}

				//合同状态理由
				if (self::validate_field_not_empty($this->contractstatusreason)
						&& !self::validate_field_max_length(
								$this->contractstatusreason, 500)) {
					$errors[] = '合同状态理由长度最多500个字符';
				}

				//应用流程
				if (intval($this->process) <= 0) {
					$errors[] = '应用流程选择有误';
				}

				//附件
				if (!empty($this->dids) && $this->dids !== '^') {
					if (!String_Util::start_with($this->dids, '^')
							|| !String_Util::end_with($this->dids, '^')) {
						$errors[] = '合同附件有误';
					} else if (!self::validate_field_max_length($this->dids,
							500)) {
						$errors[] = '合同附件选择过多';
					} else {
						$dids = $this->dids;
						$this->dids = substr($dids, 1, strlen($dids) - 2);
					}
				} else {
					$this->dids = '';
				}
			}

			if (($action === 'add' || $action === 'update')
					&& CUSTOMER_SAFETY_ON) {
				if (!self::validate_id(intval($this->customer_id))) {
					$errors[] = '系统客户名称选择有误';
				}
			}

			if ($action === 'update' || $action === 'audit') {
				if (!self::validate_field_not_empty($this->cid)
						|| !self::validate_field_not_null($this->cid)) {
					$errors[] = '合同单号不能为空';
				}
			}
		} else {
			$errors[] = '无权限操作';
		}

		if (empty($errors)) {
			return TRUE;
		}
		$this->errors = $errors;
		unset($errors);
		return FALSE;
	}

	public function add_contract() {
		if ($this->validate_form_value('add')) {
			$success = TRUE;
			$error = '';

			$toname = '';
			$contact_person = $this->db
					->get_row(
							'SELECT email,realname FROM users WHERE uid='
									. intval($this->contactperson));
			if ($contact_person === NULL) {
				return array('status' => 'error', 'message' => '联系人选择有误');
			} else {
				$contact_person_email = $contact_person->email;
				$toname = $contact_person->realname;
				if (empty($contact_person_email)
						|| !Validate_Util::my_is_email($contact_person_email,
								FALSE)) {
					return array('status' => 'error', 'message' => '联系人邮件地址有误');
				}
			}

			$this->db->query('BEGIN');

			if (CUSTOMER_SAFETY_ON) {
				//关联系统客户
				$customer_id = $this->db
						->get_var(
								'SELECT customer_id FROM v_customer_cusname WHERE cusname="'
										. $this->cusname . '"');
				if ($customer_id !== NULL
						&& intval($customer_id) !== intval($this->customer_id)) {
					//关联选择有误
					$success = FALSE;
					$error = '系统客户名称选择有误';
				} else if ($customer_id === NULL) {
					$insert_result = $this->db
							->query(
									'INSERT INTO customer_cusname(customer_id,cusname) VALUE('
											. intval($this->customer_id) . ',"'
											. $this->cusname . '")');
					if ($insert_result === FALSE) {
						$success = FALSE;
						$error = '关联系统客户失败';
					}
				}
			}

			if ($success) {
				$citycode = '';
				switch (intval($this->city)) {
				case 1:
					$citycode = 'SH';
					break;
				case 2:
					$citycode = 'BJ';
					break;
				case 3:
					$citycode = 'GZ';
					break;
				}
				$year = substr(date('Y', time()), 2, 2);
				$month = date('m', time());
				$cid = $this->db
						->get_var(
								'SELECT cid FROM contract_cus WHERE cid LIKE "'
										. substr(date('Y', time()), 2, 2)
										. $citycode
										. '%" ORDER BY cid DESC LIMIT 1');
				if ($cid !== NULL) {
					$cid = sprintf('%02d%s%02d%04d', $year, $citycode, $month,
							intval(substr($cid, 6)) + 1);
				} else {
					$cid = sprintf('%02d%s%02d0001', $year, $citycode, $month);
				}

				//代理商
				$dailis = $this->daili_array;
				$daili_tmp = array();
				if (!empty($dailis)) {
					foreach ($dailis as $daili) {
						$daili_tmp[] = $daili['daili'] . '^' . $daili['ggz'];
					}
				}
				$dailis = implode('|', $daili_tmp);

				//金额拆分 媒体
				$medias = $this->media_tf_array;
				$media_tmp = array();
				if (!empty($medias)) {
					foreach ($medias as $media) {
						$media_tmp[] = $media['media'] . '^' . $media['amount']
								. '^' . $media['advformat'];
					}
				}
				$medias = implode('|', $media_tmp);

				//金额拆分 服务
				$services = $this->service_array;
				$service_tmp = array();
				if (!empty($services)) {
					foreach ($services as $service) {
						$service_tmp[] = $service['cftype'] . '^'
								. $service['serviceamount'];
					}
				}
				$services = implode('|', $service_tmp);

				//保证金
				$bzjs = $this->bzj_array;
				$bzj_tmp = array();
				if (!empty($bzjs)) {
					foreach ($bzjs as $bzj) {
						$bzj_tmp[] = $bzj['media'] . '^' . $bzj['bl'] . '^'
								. $bzj['amount'];
					}
				}
				$bzjs = implode('|', $bzj_tmp);

				$insert = Sql_Util::get_insert('contract_cus',
						array('cid' => $cid, 'type' => intval($this->type),
								'type1' => intval($this->type1),
								'contractname' => $this->contractname,
								'cusname' => $this->cusname,
								'cuscontact' => $this->cuscontact,
								'execution' => $this->execution,
								'city' => intval($this->city),
								'dep' => intval($this->dep),
								'team' => intval($this->team),
								'contactperson' => intval($this->contactperson),
								'contractcontent' => $this->contactcontent,
								'signdate' => $this->signdate,
								'starttime' => $this->starttime,
								'endtime' => $this->endtime,
								'monitoringsystem' => $this->monitoringsystem,
								'contractamount' => $this->contractamount,
								'billtype' => intval($this->billtype),
								'rebateproportion' => $this->rebateproportion,
								'bzjpaymentmethod' => $this->bzjpaymentmethod,
								'contractamountpayment' => $this
										->contractamountpayment,
								'specialcaluse' => $this->specialcaluse,
								'remark' => $this->remark,
								'contractstatus' => intval(
										$this->contractstatus),
								'contractstatusreason' => $this
										->contractstatusreason,
								'fmkcid' => $this->fmkcid,
								'dailiinfo' => $dailis, 'cfinfo1' => $medias,
								'cfinfo2' => $services, 'bzjinfo' => $bzjs,
								'dids' => $this->dids,
								'userid' => $this->getUid(),
								'pcid' => intval($this->process),
								'time' => time(),
								'customertype' => intval($this->customertype)));

				if ($insert['status'] === 'success') {
					$insert_result = $this->db->query($insert['sql']);
					if ($insert_result === FALSE) {
						$success = FALSE;
						$error = '新建合同失败';
					} else {
						//LOG日志
						$insert = Sql_Util::get_insert('contract_cus_log',
								array('cid' => $cid, 'content' => '',
										'time' => time(),
										'uid' => $this->getUid(),
										'auditname' => '客户合同录入',
										'type' => '<font color="#66cc00">新建客户合同</font>'));
						if ($insert['status'] === 'success') {
							$insert_result = $this->db->query($insert['sql']);
							if ($insert_result === FALSE) {
								$success = FALSE;
								$error = '新建合同记录日志失败';
							}
						} else {
							$success = FALSE;
							$error = '新建合同记录日志失败，内部错误2，请联系管理员';
						}
					}
				} else {
					$success = FALSE;
					$error = '新建合同失败，内部错误1，请联系管理员';
				}
			}

			if ($success) {
				$this->db->query('COMMIT');

				//发邮件给联系人
				$subject = sprintf('合同号 %s 开号提醒!', $cid);
				if (!empty($this->fmkcid)) {
					$body = sprintf(
							'<font color=red><b>%s</b></font> 的合同号为<font color=red><b>%s</b></font>，请知晓，谢谢！',
							$this->contactcontent, $cid);
				} else {
					$fmkcidcontent = $this->db
							->get_var(
									'SELECT content FROM contract_cus WHERE cid="'
											. $this->fmkcid . '"');
					$body = sprintf(
							'<font color=red><b> %s </b></font> 的合同号为 <font color=red><b>%s</b></font> <br><br> 关联 <font color=red><b>%s</b></font> 的 <font color=red><b>%s</b></font> 的框架合同 <br><br>请知晓，谢谢！ ',
							$this->contactcontent, $cid, $this->fmkcid,
							$fmkcidcontent);
				}

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
				//$mail->Password = 'nimdigital.com'; // SMTP服务器密码
				$mail->Password = '@@nim.com1';
				$mail->SetFrom('info@nimdigital.com', '大数据系统');
				$mail->AddReplyTo('info@nimdigital.com', '大数据系统');
				$mail->Subject = $subject;
				$mail->MsgHTML($body);
				$mail->AddAddress($contact_person_email, $toname);

				$msg = '';
				if ($mail->Send()) {
					$msg = '新建合同成功';
					//return array('status' => 'success', 'message' => '新建合同成功');
				} else {
					$msg = '新建合同成功，但给联系人发送邮件失败，请联系系统管理员，错误：' . $mail->ErrorInfo;
					//return array('status' => 'success',
					//		'message' => '新建合同成功，但给联系人发送邮件失败，请联系系统管理员，错误：' . $mail->ErrorInfo);
				}
				$mail->SmtpClose();
				unset($mail);
				return array('status' => 'success', 'message' => $msg);
			} else {
				$this->db->query('ROLLBACK');
				return array('status' => 'error', 'message' => $error);
			}
		}
		return array('status' => 'error', 'message' => $this->errors);
	}

	public function get_contract_info() {
		if ($this->cid !== NULL) {
			$query = 'SELECT a.*,b.username,b.realname,c.customer_name FROM contract_cus a LEFT JOIN users b ON a.contactperson = b.uid LEFT JOIN v_customer_cusname c ON a.cusname=c.cusname WHERE a.cid="'
					. $this->cid . '"';
			if (!$this->has_contract_permission
					&& !$this->getHas_check_contract_permission()
					&& !$this
							->get_relation_contract_permission(
									intval($this->getBelong_city()),
									intval($this->getBelong_dep()),
									intval($this->getBelong_team()))) {
				$query .= ' AND (a.contactperson="' . $this->getUid()
						. '" OR a.view LIKE "%(' . $this->getUsername()
						. ')%")';
			}
			$row = $this->db->get_row($query);
			if ($row !== NULL) {
				$buf = file_get_contents(
						TEMPLATE_PATH . 'contract/infopage.tpl');
				$this->logs = $this->db
						->get_results(
								'SELECT FROM_UNIXTIME(a.time) AS time,b.realname,a.type,a.content FROM contract_cus_log a LEFT JOIN users b ON b.uid = a.uid WHERE a.cid="'
										. $this->cid . '" ORDER BY a.time DESC');
				return str_replace(
						array('[TYPE]', '[TYPE1]', '[CONTRACTNAME]',
								'[CUSNAME]', '[CUSCONTACT]', '[EXECUTION]',
								'[DEPINFO]', '[CONTACTPERSON]',
								'[CONTRACTCONTENT]', '[SIGNDATE]',
								'[EXECUTIVETIME]', '[MONITORINGSYSTEM]',
								'[CONTRACTAMOUNT]', '[BILLTYPE]',
								'[REBATEPROPORTION]', '[BZJPAYMENTMETHOD]',
								'[CONTRACTAMOUNTPAYMENT]', '[SPECIALCALUSE]',
								'[REMARK]', '[UPLOADFILES]', '[MTINFO]',
								'[FWINFO]', '[BZJINFO]', '[CONTRACTSTATUS]',
								'[CUSTOMERTYPE]', '[CUSTOMERNAME]'),
						array($this->_get_contract_type($row),
								self::_get_contract_type1($row),
								$row->contractname, $row->cusname,
								$row->cuscontact, $row->execution,
								$this
										->get_user_city_info($row->city,
												$row->dep, $row->team),
								self::_get_contract_person($row),
								$row->contractcontent, $row->signdate,
								self::_get_contract_start_end_time($row),
								$row->monitoringsystem,
								Format_Util::my_money_format('%.2n',
										$row->contractamount),
								self::_get_contract_billtype($row),
								$row->rebateproportion,
								Format_Util::format_html($row->bzjpaymentmethod),
								Format_Util::format_html(
										$row->contractamountpayment),
								Format_Util::format_html($row->specialcaluse),
								Format_Util::format_html($row->remark),
								$this->get_upload_files($row->dids),
								self::_get_contract_mt_info($row),
								self::_get_contract_fw_info($row),
								self::_get_contract_bzj_info($row),
								self::_get_contract_status($row),
								'',
								$row->customer_name), $buf);
			}
		}
		return NULL;
	}

	private function _get_contract_type($row) {
		if (intval($row->type) === 1) {
			$typename = '框架';
			$results = $this->db
					->get_results(
							'SELECT cid FROM contract_cus WHERE fmkcid="'
									. $row->cid . '"');
			if ($results !== NULL) {
				$typename .= '<br><br>下属单笔合同：';
				foreach ($results as $result) {
					$typename .= '<br><a href="' . BASE_URL
							. 'contract_cus/?o=info&cid=' . $result->cid
							. '" target="_blank"><b>' . $result->cid
							. '</b></a>';
				}
			}
		} else {
			$typename = '单笔';
			if (!empty($row->fmkcid)) {
				$typename .= '&nbsp;&nbsp;所属框架合同：<a href="' . BASE_URL
						. 'contract_cus/?o=info&cid=' . $row->fmkcid
						. '" target="_blank"><b>' . $row->fmkcid . '</b></a>';
			}
		}
		return $typename;
	}

	private static function _get_contract_type1($row) {
		if (intval($row->type1) === 1) {
			return '直客';
		} else {
			$dailiinfo = $row->dailiinfo;
			$s = '代理商';
			if (!empty($dailiinfo)) {
				$s .= '<br/>';
				$dailiinfo = explode('|', $dailiinfo);
				foreach ($dailiinfo as $daili) {
					$daili = explode('^', $daili);
					$s .= '<div>代理商名称：' . $daili[0] . ' &nbsp;&nbsp;广告主: '
							. $daili[1] . '</div>';
				}
			}
			return $s;
		}
	}

	private function _get_contract_edit_dailiinfo($row) {
		$dailiinfo = $row->dailiinfo;
		$s = '';
		$daili_count = array();
		if (!empty($dailiinfo)) {
			$dailiinfo = explode('|', $dailiinfo);
			foreach ($dailiinfo as $key => $daili) {
				$daili_count[] = $key + 1;
				$daili = explode('^', $daili);
				$s .= '<span><br /> 代理商：<input name="dailishang_' . ($key + 1)
						. '" id="dailishang_' . ($key + 1)
						. '"  type="text" style="width:200px;height:20px;" class="validate[required]" value="'
						. $daili[0]
						. '" /> &nbsp; 广告主： <input name="guanggaozhu_'
						. ($key + 1)
						. '" type="text" style="width:100px;height:20px;" class="validate[required]" value="'
						. $daili[1] . '" /> &nbsp;<img src="' . BASE_URL
						. 'images/close.png" onclick="del_daili(this,'
						. ($key + 1) . ')" width="17" height="17" /></span>';
			}
		}
		if (!empty($daili_count)) {
			$this->daili_count = ',' . implode(',', $daili_count) . ',';
			$this->var_daili_count = count($daili_count) + 1;
		}
		return $s;
	}

	private static function _get_contract_person($row) {
		return $row->realname . ' (' . $row->username . ')';
	}

	private static function _get_contract_start_end_time($row) {
		return $row->starttime . ' / ' . $row->endtime;
	}

	private static function _get_contract_billtype($row) {
		if (intval($row->billtype) === 2) {
			return '服务';
		}
		return '广告';
	}

	private static function _get_contract_mt_info($row) {
		$s = '';
		$cfinfo1 = $row->cfinfo1;
		if (!empty($cfinfo1)) {
			$cfinfo1 = explode('|', $cfinfo1);
			foreach ($cfinfo1 as $cfinfo) {
				$cfinfo = explode('^', $cfinfo);
				$s .= '<div>媒体：' . $cfinfo[0] . ' 金额：' . $cfinfo[1]
						. ' 广告形式及优惠政策: ' . $cfinfo[2] . '</div>';
			}
		}
		return $s;
	}

	private function _get_contract_edit_mt_info($row) {
		$s = '';
		$cfinfo1 = $row->cfinfo1;
		$media_count = array();
		if (!empty($cfinfo1)) {
			$cfinfo1 = explode('|', $cfinfo1);
			foreach ($cfinfo1 as $key => $cfinfo) {
				$media_count[] = $key + 1;
				$cfinfo = explode('^', $cfinfo);
				$s .= '<div>媒体：<input name="media_' . ($key + 1)
						. '" id="media_' . ($key + 1)
						. '" type="text" style="width:100px;height:20px;" class="validate[required]" value="'
						. $cfinfo[0]
						. '" />&nbsp;&nbsp;金额：<input name="mediaamount_'
						. ($key + 1) . '" id="mediaamount_' . ($key + 1)
						. '" type="text" style="width:80px;height:20px;" value="'
						. $cfinfo[1]
						. '" />&nbsp;&nbsp;广告形式及优惠政策：<input name="advformat_'
						. ($key + 1) . '" id="advformat_' . ($key + 1)
						. '" type="text" style="width:300px;height:20px;" value="'
						. $cfinfo[2] . '" />&nbsp;&nbsp;<img src="' . BASE_URL
						. 'images/close.png" onclick="del_media(this,'
						. ($key + 1) . ')" width="17" height="17" /></div>';
			}
		}
		if (!empty($media_count)) {
			$this->media_count = ',' . implode(',', $media_count) . ',';
			$this->var_media_count = count($media_count) + 1;
		}
		return $s;
	}

	private static function _get_contract_fw_info($row) {
		$s = '';
		$cfinfo2 = $row->cfinfo2;
		if (!empty($cfinfo2)) {
			$cfinfo2 = explode('|', $cfinfo2);
			foreach ($cfinfo2 as $cfinfo) {
				$cfinfo = explode('^', $cfinfo);
				$s .= '<div>' . $GLOBALS['defined_fw_amount_type'][$cfinfo[0]]
						. ' 金额：' . $cfinfo[1] . ' </div>';
			}
		}
		return $s;
	}

	private function _get_contract_edit_fw_info($row) {
		$s = '';
		$cfinfo2 = $row->cfinfo2;
		$service_count = array();
		if (!empty($cfinfo2)) {
			$cfinfo2 = explode('|', $cfinfo2);
			foreach ($cfinfo2 as $key => $cfinfo) {
				$service_count[] = $key + 1;
				$cfinfo = explode('^', $cfinfo);
				$s .= '<div><select class="select" name="cftype_' . ($key + 1)
						. '" id="cftype_' . ($key + 1) . '">'
						. self::get_fw_amount_options($cfinfo[0])
						. '</select>&nbsp;&nbsp;金额：<input name="serviceamount_'
						. ($key + 1) . '" id="serviceamount_' . ($key + 1)
						. '" type="text" width="100" style="height:20px;" value="'
						. $cfinfo[1] . '" />&nbsp;&nbsp;<img src="' . BASE_URL
						. 'images/close.png" onclick="del_service(this,'
						. ($key + 1) . ')" width="17" height="17" /></div>';
			}
		}
		if (!empty($service_count)) {
			$this->service_count = ',' . implode(',', $service_count) . ',';
			$this->var_service_count = count($service_count) + 1;
		}
		return $s;
	}

	private static function _get_contract_bzj_info($row) {
		$s = '';
		$bzjinfo = $row->bzjinfo;
		if (!empty($bzjinfo)) {
			$bzjinfo = explode('|', $bzjinfo);
			foreach ($bzjinfo as $bzj) {
				$bzj = explode('^', $bzj);
				$je = $bzj[2];
				$je = str_replace(array('￥', ','), array('', ''), $je);
				$s .= '<div>媒体：' . $bzj[0] . ' 比例：' . $bzj[1]
						. ' % 金额: <font color="#ff9933"><b>'
						. Format_Util::my_money_format('%.2n',
								!empty($je) ? $je : 0) . '</b></font></div>';
			}
		}
		return $s;
	}

	private function _get_contract_edit_bzj_info($row) {
		$s = '';
		$bzjinfo = $row->bzjinfo;
		$bzj_count = array();
		if (!empty($bzjinfo)) {
			$bzjinfo = explode('|', $bzjinfo);
			foreach ($bzjinfo as $key => $bzj) {
				$bzj_count[] = $key + 1;
				$bzj = explode('^', $bzj);
				$s .= '<div>媒体：<input name="bzjname_' . ($key + 1)
						. '" id="bzjname_' . ($key + 1)
						. '" type="text" style="width:100px" class="validate[required] text" value="'
						. $bzj[0] . '" />&nbsp;比例：<input name="bzjbl_'
						. ($key + 1) . '" id="bzjbl_' . ($key + 1)
						. '"  type="text" style="width:30px" class="validate[required,custom[number],min[0],max[100]] text" value="'
						. $bzj[1]
						. '" /> <b>%</b>&nbsp;金额：<input name="bzjamount_'
						. ($key + 1) . '" id="bzjamount_' . ($key + 1)
						. '" type="text" class="validate[required,custom[number]] text" style="width:100px; text-align:right" value="'
						. $bzj[2] . '" />  <label>元</label>&nbsp;<img src="'
						. BASE_URL . 'images/close.png" onclick="del_bzj(this,'
						. ($key + 1)
						. ')" width="17" height="17" /><br /></div>';
			}
		}
		if (!empty($bzj_count)) {
			$this->baozhengjin_count = ',' . implode(',', $bzj_count) . ',';
			$this->var_baozhengjin_count = count($bzj_count) + 1;
		}
		return $s;
	}

	private static function _get_contract_status($row) {
		if (intval($row->contractstatus) === 1) {
			return '<font color="#00FF00">已归档</font>';
		}
		return '<font color="#FF0000"><b>未归档</b> &nbsp;&nbsp; 原由: '
				. $row->contractstatusreason . ' </font>';
	}

	public function get_contract_log_list() {
		$buf = file_get_contents(TEMPLATE_PATH . 'contract/loglist.tpl');
		$s = '';
		if (!empty($this->logs)) {
			$logs = $this->logs;
			foreach ($logs as $log) {
				$s .= '<tr><td>' . $log->time . '</td><td>' . $log->realname
						. '</td><td>' . $log->type . '</td><td>'
						. $log->content . '</td></tr>';
			}
		} else {
			$s = '<tr><td colspan="4"><font color="red">没有流转记录</font></td></tr>';
		}
		return str_replace('[LOGS]', $s, $buf);
	}

	public static function get_fw_amount_options($fw = NULL) {
		$s = '';
		foreach ($GLOBALS['defined_fw_amount_type'] as $key => $value) {
			$s .= '<option value="' . $key . '"'
					. ($fw !== NULL && intval($fw) === intval($key) ? 'selected="selected"'
							: '') . '>' . $value . '</option>';
		}
		return $s;
	}

	private static function _get_user_by_dep_team($depid, $teamid,
			$contractperson) {
		if ($teamid > 0) {
			$team = new Team($teamid);
			return $team->get_users_select_html_by_team($contractperson);
		} else {
			$dep = new Dep($depid);
			return $dep->get_users_select_html_by_dep($contractperson);
		}
	}

	public function get_edit_page() {
		if ($this->cid !== NULL) {
			if ($this->getHas_manager_contract_permission()) {
				$row = $this->db
						->get_row(
								'SELECT a.*,b.customer_id FROM contract_cus a LEFT JOIN v_customer_cusname b ON a.cusname=b.cusname WHERE a.cid="'
										. $this->cid . '"');
				if ($row !== NULL) {
					$buf = file_get_contents(
							TEMPLATE_PATH . 'contract/editpage.tpl');
					$this->logs = $this->db
							->get_results(
									'SELECT FROM_UNIXTIME(a.time) AS time,b.realname,a.type,a.content FROM contract_cus_log a LEFT JOIN users b ON b.uid = a.uid WHERE a.cid="'
											. $this->cid
											. '" ORDER BY a.time DESC');
					return str_replace(
							array('[TYPE1_1]', '[TYPE1_2]', '[SHOWFMKCID]',
									'[SHWOTYPE2]', '[ISFMKCID]', '[FMKCID]',
									'[FMKCIDLIST]', '[TYPE2_1]', '[TYPE2_2]',
									'[SHOWDAILIINFO]', '[DAILIINFO]',
									'[CONTRACTNAME]', '[CUSNAME]',
									'[CUSCONTACT]', '[EXECUTION]', '[CITYS]',
									'[DEPS]', '[TEAMS]', '[CONTACTPERSON]',
									'[CONTRACTCONTENT]', '[SIGNDATE]',
									'[STARTTIME]', '[ENDTIME]',
									'[MONITIORINGSYSTEM]', '[CONTRACTAMOUNT]',
									'[SELECT_1]', '[SELECT_2]',
									'[REBATEPROPORTION]', '[BZJPAYMENTMETHOD]',
									'[CONTRACTAMOUNTPAYMENT]',
									'[SPECIALCALUSE]', '[REMARK]', '[GD1]',
									'[GD2]', '[SHOWFILEREASON]',
									'[FILEREASON]', '[PROCESSLIST]',
									'[UPLOADFILES]', '[MTINFO]', '[FWINFO]',
									'[BZJINFO]', '[DAILICOUNT]',
									'[VARDAILICOUNT]', '[MEDIATFCOUNT]',
									'[VARMEDIACOUNT]', '[SERVICECOUNT]',
									'[VARSERVICECOUNT]', '[FWAMOUNTOPTIONS]',
									'[BAOZHENGJINCOUNT]',
									'[VARBAOZHENGJINCOUNT]', '[DIDS]',
									'[CUSTOMERTYPE]', '[CUSTOMERSELECT]'),
							array(
									intval($row->type) === 1 ? 'checked="checked"'
											: '',
									intval($row->type) === 1 ? ''
											: 'checked="checked"',
									intval($row->type) === 1 ? 'style="display:none"'
											: '',
									intval($row->type) === 1 ? ''
											: 'style="display:none"',
									!empty($row->fmkcid) ? 'checked="checked"'
											: '',
									!empty($row->fmkcid) ? ''
											: 'style="display:none"',
									$this
											->get_kuangja_contract_select_html(
													$row->fmkcid),
									intval($row->type1) === 1 ? 'checked="checked"'
											: '',
									intval($row->type1) === 1 ? ''
											: 'checked="checked"',
									intval($row->type1) === 1 ? 'style="display:none"'
											: '',
									intval($row->type1) === 1 ? ''
											: $this
													->_get_contract_edit_dailiinfo(
															$row),
									$row->contractname, $row->cusname,
									$row->cuscontact, $row->execution,
									City::get_city_select_html(FALSE,
											$row->city),
									Dep::get_dep_select_html_by_city(
											$row->city, FALSE, $row->dep),
									Team::get_team_select_html_by_dep(
											$row->dep, FALSE, $row->team),
									self::_get_user_by_dep_team($row->dep,
											$row->team, $row->contactperson),
									$row->contractcontent, $row->signdate,
									$row->starttime, $row->endtime,
									$row->monitoringsystem,
									$row->contractamount,
									intval($row->billtype) === 1 ? 'selected="selected"'
											: '',
									intval($row->billtype) === 1 ? ''
											: 'selected="selected"',
									$row->rebateproportion,
									$row->bzjpaymentmethod,
									$row->contractamountpayment,
									$row->specialcaluse, $row->remark,
									intval($row->contractstatus) === 1 ? 'checked="checked"'
											: '',
									intval($row->contractstatus) === 1 ? ''
											: 'checked="checked"',
									intval($row->contractstatus) === 1 ? 'style="display:none"'
											: '', $row->contractstatusreason,
									$this
											->get_contract_process_html(
													$row->pcid),
									$this->get_upload_files($row->dids, TRUE),
									$this->_get_contract_edit_mt_info($row),
									$this->_get_contract_edit_fw_info($row),
									$this->_get_contract_edit_bzj_info($row),
									$this->daili_count, $this->var_daili_count,
									$this->media_count, $this->var_media_count,
									$this->service_count,
									$this->var_service_count,
									Contract::get_fw_amount_options(),
									$this->baozhengjin_count,
									$this->var_baozhengjin_count,
									!empty($row->dids) ? '^' . $row->dids . '^'
											: '^',
									'',
									$this
											->_get_customer_select(
													$row->customer_id)), $buf);
				}
			}
		}
		return NULL;
	}

	public function update_contract() {
		if ($this->validate_form_value('update')) {
			$success = TRUE;
			$error = '';

			$this->db->query('BEGIN');

			//关联系统客户
			if (CUSTOMER_SAFETY_ON) {
				$customer_id = $this->db
						->get_var(
								'SELECT customer_id FROM v_customer_cusname WHERE cusname="'
										. $this->cusname . '"');
				if ($customer_id !== NULL
						&& intval($customer_id) !== intval($this->customer_id)) {
					//关联选择有误
					$success = FALSE;
					$error = '系统客户名称选择有误';
				} else if ($customer_id === NULL) {
					$insert_result = $this->db
							->query(
									'INSERT INTO customer_cusname(customer_id,cusname) VALUE('
											. intval($this->customer_id) . ',"'
											. $this->cusname . '")');
					if ($insert_result === FALSE) {
						$success = FALSE;
						$error = '关联系统客户失败';
					}
				}
			}

			if ($success) {
				//代理商
				$dailis = $this->daili_array;
				$daili_tmp = array();
				if (!empty($dailis)) {
					foreach ($dailis as $daili) {
						$daili_tmp[] = $daili['daili'] . '^' . $daili['ggz'];
					}
				}
				$dailis = implode('|', $daili_tmp);

				//金额拆分 媒体
				$medias = $this->media_tf_array;
				$media_tmp = array();
				if (!empty($medias)) {
					foreach ($medias as $media) {
						$media_tmp[] = $media['media'] . '^' . $media['amount']
								. '^' . $media['advformat'];
					}
				}
				$medias = implode('|', $media_tmp);

				//金额拆分 服务
				$services = $this->service_array;
				$service_tmp = array();
				if (!empty($services)) {
					foreach ($services as $service) {
						$service_tmp[] = $service['cftype'] . '^'
								. $service['serviceamount'];
					}
				}
				$services = implode('|', $service_tmp);

				//保证金
				$bzjs = $this->bzj_array;
				$bzj_tmp = array();
				if (!empty($bzjs)) {
					foreach ($bzjs as $bzj) {
						$bzj_tmp[] = $bzj['media'] . '^' . $bzj['bl'] . '^'
								. $bzj['amount'];
					}
				}
				$bzjs = implode('|', $bzj_tmp);
				$update = Sql_Util::get_update('contract_cus',
						array('type' => intval($this->type),
								'type1' => intval($this->type1),
								'contractname' => $this->contractname,
								'cusname' => $this->cusname,
								'cuscontact' => $this->cuscontact,
								'execution' => $this->execution,
								'city' => intval($this->city),
								'dep' => intval($this->dep),
								'team' => intval($this->team),
								'contactperson' => intval($this->contactperson),
								'contractcontent' => $this->contactcontent,
								'signdate' => $this->signdate,
								'starttime' => $this->starttime,
								'endtime' => $this->endtime,
								'monitoringsystem' => $this->monitoringsystem,
								'contractamount' => $this->contractamount,
								'billtype' => intval($this->billtype),
								'rebateproportion' => $this->rebateproportion,
								'bzjpaymentmethod' => $this->bzjpaymentmethod,
								'contractamountpayment' => $this
										->contractamountpayment,
								'specialcaluse' => $this->specialcaluse,
								'remark' => $this->remark,
								'contractstatus' => intval(
										$this->contractstatus),
								'contractstatusreason' => $this
										->contractstatusreason,
								'fmkcid' => $this->fmkcid,
								'dailiinfo' => $dailis, 'cfinfo1' => $medias,
								'cfinfo2' => $services, 'bzjinfo' => $bzjs,
								'dids' => $this->dids, 'step' => 1,
								'isok' => 0, 'pcid' => intval($this->process),
								'customertype' => intval($this->customertype)),
						array('cid' => array('=', $this->cid)), 'AND');

				if ($update['status'] === 'success') {
					$update_result = $this->db->query($update['sql']);
					if ($update_result === FALSE) {
						$success = FALSE;
						$error = '更新合同失败';
					} else {
						//LOG日志
						$insert = Sql_Util::get_insert('contract_cus_log',
								array('cid' => $this->cid, 'content' => '',
										'time' => time(),
										'uid' => $this->getUid(),
										'auditname' => '客户合同录入',
										'type' => '<font color="#66cc00">修改客户合同</font>'));
						if ($insert['status'] === 'success') {
							$insert_result = $this->db->query($insert['sql']);
							if ($insert_result === FALSE) {
								$success = FALSE;
								$error = '更新合同记录日志失败';
							}
						} else {
							$success = FALSE;
							$error = '更新合同记录日志失败，内部错误2，请联系管理员';
						}
					}
				} else {
					$success = FALSE;
					$error = '更新合同失败，内部错误1，请联系管理员';
				}
			}

			if ($success) {
				$this->db->query('COMMIT');
			} else {
				$this->db->query('ROLLBACK');
			}
			return array('status' => $success ? 'success' : 'error',
					'message' => $success ? '更新合同成功' : $error);
		}
		return array('status' => 'error', 'message' => $this->errors);
	}

	public function audit_contract() {
		if ($this->validate_form_value('audit')) {
			$success = TRUE;
			$error = '';
			$process = Process::getInstance();

			$row = $this->db
					->get_row(
							'SELECT * FROM contract_cus WHERE cid="'
									. $this->cid . '"');
			if ($row !== NULL) {
				$this->db->query('BEGIN');

				$process = $process['step'][$row->pcid];

				if (intval($this->audit_pass) === 1) {
					//审核通过
					$insert = Sql_Util::get_insert('contract_cus_log',
							array('cid' => $this->cid,
									'content' => $this->remark,
									'time' => time(), 'uid' => $this->getUid(),
									'auditname' => $process[$row->step]['content'][0],
									'type' => '<font color="#66cc00">审核确认</font>'));
					if ($insert['status'] === 'success') {
						$insert_result = $this->db->query($insert['sql']);
						if ($insert_result === FALSE) {
							$success = FALSE;
							$error = '审核合同记录日志失败';
						} else {
							$msgid = $this->db->insert_id;
						}
					} else {
						$success = FALSE;
						$error = '审核合同记录日志失败，内部错误2，请联系管理员';
					}

					if ($success) {
						if ((intval($row->step) + 1) === count($process)) {
							$update_result = $this->db
									->query(
											'UPDATE contract_cus SET isok=1,isexecutive=1 WHERE cid="'
													. $this->cid . '"');
							if ($update_result === FALSE) {
								$success = FALSE;
								$error = '审核合同更新状态失败1';
							} else {
								if (intval($row->isexecutive) === 0) {
									$update_result = $this->db
											->query(
													'UPDATE contract_cus SET oktime='
															. time()
															. ' WHERE cid="'
															. $this->cid . '"');
									if ($update_result === FALSE) {
										$success = FALSE;
										$error = '审核合同更新状态失败2';
									}
								}
							}
						}

						if ($success) {
							$update_result = $this->db
									->query(
											'UPDATE contract_cus SET step=step+1,msgid='
													. $msgid . ' WHERE cid="'
													. $this->cid . '"');
							if ($update_result === FALSE) {
								$success = FALSE;
								$error = '审核合同更新状态失败3';
							}
						}
					}
				} else {
					//审核驳回
					$insert = Sql_Util::get_insert('contract_cus_log',
							array('cid' => $this->cid,
									'content' => $this->remark,
									'time' => time(), 'uid' => $this->getUid(),
									'auditname' => $process[$row->step]['content'][0],
									'type' => '<font color="#66cc00">审核驳回</font>'));
					if ($insert['status'] === 'success') {
						$insert_result = $this->db->query($insert['sql']);
						if ($insert_result === FALSE) {
							$success = FALSE;
							$error = '审核合同记录日志失败';
						} else {
							$msgid = $this->db->insert_id;
						}
					} else {
						$success = FALSE;
						$error = '审核合同记录日志失败，内部错误2，请联系管理员';
					}

					if ($success) {
						$update_result = $this->db
								->query(
										'UPDATE contract_cus SET step=0,msgid='
												. $msgid . ' WHERE cid="'
												. $this->cid . '"');
						if ($update_result === FALSE) {
							$success = FALSE;
							$error = '驳回合同更新状态失败';
						}
					}
				}

				if ($success) {
					$this->db->query('COMMIT');
				} else {
					$this->db->query('ROLLBACK');
				}

				return array('status' => $success ? 'success' : 'error',
						'message' => $success ? '审核合同成功' : $error);
			}
			return array('status' => 'error', 'message' => '合同不存在');
		}
		return array('status' => 'error', 'message' => $this->errors);
	}

	public function cancel_contract() {
		if ($this->validate_form_value('cancel')) {
			$success = TRUE;
			$error = '';

			$this->db->query('BEGIN');

			$row = $this->db
					->get_row(
							'SELECT cid FROM contract_cus WHERE cid="'
									. $this->cid . '" AND isok<>-1 FOR UPDATE');
			if ($row !== NULL) {
				$update_result = $this->db
						->query(
								'UPDATE contract_cus SET isok=-1 WHERE cid="'
										. $this->cid . '"');
				if ($update_result === FALSE || $update_result === 0) {
					$success = FALSE;
					$error = '撤销合同失败，错误代码1';
				} else {
					$insert_result = $this->db
							->query(
									'INSERT INTO contract_cus_log(cid,content,time,uid,auditname,type) VALUE("'
											. $this->cid . '","",' . time()
											. ',' . intval($this->getUid())
											. ',"合同管理员","<font color=\'#66cc00\'>撤销合同</font>")');
					if ($insert_result === FALSE) {
						$success = FALSE;
						$error = '撤销合同失败，错误代码2';
					}
				}
			} else {
				$success = FALSE;
				$error = '没有该合同';
			}

			if ($success) {
				$this->db->query('COMMIT');
			} else {
				$this->db->query('ROLLBACK');
			}

			return array('status' => $success ? 'success' : 'error',
					'message' => $success ? '撤销合同成功' : $error);
		}
		return array('status' => 'error', 'message' => $this->errors);
	}

	public function get_contract_add_html() {
		$permission = $this->getPermissions();
		if (Array_Util::my_remove_array_other_value($permission,
				$GLOBALS['add_contract_permission']) !== $permission) {
			$buf = file_get_contents(
					TEMPLATE_PATH . 'contract/contract_add.tpl');
			return str_replace(
					array('[LEFT]', '[TOP]', '[VCODE]', '[CITYS]',
							'[PROCESSLIST]', '[FMKCIDLIST]',
							'[FWAMOUNTOPTIONS]', '[VALIDATE_TYPE]',
							'[VALIDATE_SIZE]', '[CUSTOMERSELECT]',
							'[CUSTOMERTYPE]', '[BASE_URL]'),
					array($this->get_left_html(), $this->get_top_html(),
							$this->get_vcode(),
							City::get_city_select_html(FALSE),
							$this->get_contract_process_html(),
							$this->get_kuangja_contract_select_html(),
							Contract::get_fw_amount_options(),
							implode(',',
									$GLOBALS['defined_upload_validate_type']),
							UPLOAD_FILE_MAX_SIZE / (1024 * 1024),
							$this->_get_customer_select(),
							'', BASE_URL),
					$buf);
		} else {
			return User::no_permission();
		}
	}

	private function _get_customer_select($customer_id = NULL) {
		$results = $this->db
				->get_results(
						'SELECT DISTINCT(customer_id) AS customer_id,customer_name FROM v_customer_cusname');
		$s = '<span id="customershow"></span><select class="'
				. (CUSTOMER_SAFETY_ON ? 'validate[required] ' : '')
				. 'select" name="customer" id="customer"><option value="">请选择</option>';
		if ($results !== NULL) {
			foreach ($results as $result) {
				$s .= '<option value="' . $result->customer_id . '"'
						. ($customer_id !== NULL
								&& intval($customer_id)
										=== intval($result->customer_id) ? ' selected="selected"'
								: '') . '>' . $result->customer_name
						. '</option>';
			}
		}
		$s .= '</select>';
		return $s;
	}

	public function get_contract_info_html() {
		if ($this->getHas_contract_permission()) {
			$buf = file_get_contents(
					TEMPLATE_PATH . 'contract/contract_info.tpl');
			return str_replace(
					array('[LEFT]', '[TOP]', '[INFO]', '[CID]', '[LOGLIST]',
							'[BASE_URL]'),
					array($this->get_left_html(), $this->get_top_html(),
							$this->get_contract_info(), $this->cid,
							$this->get_contract_log_list(), BASE_URL), $buf);
		} else {
			return User::no_permission();
		}
	}

	public function get_contract_edit_html() {
		if (in_array('sys44', $this->getPermissions(), TRUE)) {
			$row = $this->db
					->get_row(
							'SELECT step,isok FROM contract_cus WHERE cid="'
									. $this->cid . '"');
			if ($row !== NULL) {
				if (intval($row->step) === 0 || intval($row->isok) === 1) {
					$buf = file_get_contents(
							TEMPLATE_PATH . 'contract/contract_edit.tpl');
					return str_replace(
							array('[LEFT]', '[TOP]', '[VCODE]', '[EDITPAGE]',
									'[CID]', '[LOGLIST]', '[VALIDATE_TYPE]',
									'[VALIDATE_SIZE]', '[BASE_URL]'),
							array($this->get_left_html(),
									$this->get_top_html(), $this->get_vcode(),
									$this->get_edit_page(), $this->cid,
									$this->get_contract_log_list(),
									implode(',',
											$GLOBALS['defined_upload_validate_type']),
									UPLOAD_FILE_MAX_SIZE / (1024 * 1024),
									BASE_URL), $buf);
				} else {
					return User::no_permission();
				}
			} else {
				return User::no_object('没有该合同');
			}
		} else {
			return User::no_permission();
		}
	}

	public function get_contract_audit_html() {
		if ($this->getHas_contract_permission()) {
			$buf = file_get_contents(
					TEMPLATE_PATH . 'contract/contract_audit.tpl');
			return str_replace(
					array('[LEFT]', '[TOP]', '[INFO]', '[CID]', '[LOGLIST]',
							'[VCODE]', '[BASE_URL]'),
					array($this->get_left_html(), $this->get_top_html(),
							$this->get_contract_info(), $this->cid,
							$this->get_contract_log_list(), $this->get_vcode(),
							BASE_URL), $buf);
		} else {
			return User::no_permission();
		}
	}

	private static $instance = NULL;
	public static function getInstance($force_flush = FALSE) {
		if (self::$instance === NULL || $force_flush) {
			$contract_cache_filename = md5('contract_cache_filename');
			$contract_cache = new FileCache(CACHE_TIME, CACHE_PATH);
			$contract_cache_file = $contract_cache
					->get($contract_cache_filename);
			if ($contract_cache_file === FALSE || $force_flush) {
				//读取数据库
				$dao = new Dao_Impl();
				$contracts = $dao->db
						->get_results(
								'SELECT cid,cusname FROM contract_cus WHERE isok=1');
				if ($contracts !== NULL) {
					$datas = array();
					foreach ($contracts as $contract) {
						$datas[$contract->cid] = $contract->cusname;
					}
					$contract_cache->set($contract_cache_filename, $datas);
				}
			}
			self::$instance = $contract_cache->get($contract_cache_filename);
		}
		return self::$instance;
	}
}
