<?php
class Invoice extends User {
	private $has_invoice_permission = FALSE;
	private $view_myinvoice = FALSE;
	private $view_normalinvoice = FALSE;
	private $search;
	private $billtype;

	private $invoice_type;
	private $pids_array;
	private $invoicecontent;
	private $process;
	private $title;
	private $d1;
	private $d2;
	private $d3;

	private $errors = array();

	private $id;
	private $amount;
	private $type;
	private $content;
	private $company;
	private $time;
	private $date;
	private $number;
	private $auditmsg;
	private $isok;
	private $pids;
	private $pidinfo;
	private $remark;

	private $p = 0;

	private $userinfo;
	private $username;
	private $realname;
	private $dep;

	private $audit_remark;

	private $has_leader_audit_invoice_permission = FALSE;

	private $gdusername;
	private $gdrealname;

	private $step;

	public function getD1() {
		return $this->d1 !== NULL ? $this->d1 : '';
	}

	public function getD2() {
		return $this->d2 !== NULL ? $this->d2 : '';
	}

	public function getD3() {
		return $this->d3 !== NULL ? $this->d3 : '';
	}

	public function getPids() {
		return $this->pids;
	}

	public function getHidden_pids() {
		$pids = $this->pids;
		if (empty($pids)) {
			return ',';
		} else {
			$res = array();
			$pids = explode('|', $pids);
			foreach ($pids as $pid) {
				$pid = explode('^', $pid);
				$res[] = $pid[0];
			}
			if (!empty($res)) {
				return ',' . implode(',', $res) . ',';
			} else {
				return ',';
			}
		}
	}

	/**
	 * @return the $has_leader_audit_invoice_permission
	 */
	public function getHas_leader_audit_invoice_permission() {
		return $this->has_leader_audit_invoice_permission;
	}

	/**
	 * @return the $userinfo
	 */
	public function getUserinfo() {
		$dep = Dep::getInstance();
		$my_dep = $dep[$this->dep];
		if ($my_dep === NULL) {
			$my_dep = '';
		} else {
			$my_dep = $this->get_depname($my_dep[1], $my_dep[0]);
		}
		return sprintf('%s %s %s', $this->username, $this->realname, $my_dep);
	}

	/**
	 * @return the $pidinfo
	 */
	public function getPidinfo() {
		$pids = $this->pids;
		$s = '';
		if (!empty($pids)) {
			$pids = explode('|', $pids);
			foreach ($pids as $pid) {
				$pid = explode('^', $pid);
				$s .= $pid[0] . ': <font color="#ff9933"><b>'
						. Format_Util::my_money_format('%.2n', $pid[1])
						. '</b></font> 元<br>';
			}
		}
		return $s;
	}

	public function get_invoice_type() {
		$pids = $this->pids;
		$s = '';
		if (!empty($pids)) {
			$pids = explode('|', $pids);
			$pids = reset($pids);
			
			$cid = explode('-', $pids);
			$cid = reset($cid);
			$billtype = $this->db
					->get_var(
							'SELECT billtype FROM contract_cus WHERE cid="'
									. $cid . '"');
			switch (intval($billtype)) {
			case 1:
				$s = '广告业';
				break;
			case 2:
				$s = '服务业';
				break;
			}
		}
		return $s;
	}

	public function get_others() {
		if (intval($this->isok) === 1) {
			$date = $this->date;
			$number = $this->number;
			return <<<EOF
			<tr>
		          <td style="font-weight:bold">开票日期</td>
		          <td>$date</td>
		        </tr>
		        <tr>
		          <td style="font-weight:bold">开票号码</td>
		          <td>$number</td>
		        </tr>
EOF;
		} else {
			return '';
		}
	}
	/**
	 * @return the $date
	 */
	public function getDate() {
		return $this->date;
	}

	/**
	 * @return the $number
	 */
	public function getNumber() {
		return $this->number;
	}

	/**
	 * @return the $auditmsg
	 */
	public function getAuditmsg() {
		if (intval($this->isok) === 0) {
			return '<font color="#ff6600"><b>等待审核</b></font><br>'
					. $this->auditmsg;
		} else if (intval($this->isok) === 1) {
			return '<font color="#66cc00"><b>已生效</b></font><br>'
					. $this->auditmsg;
		} else {
			return '<font color="red"><b>已驳回<br>驳回理由：' . $this->auditmsg
					. '</b></font>';
		}
	}

	/**
	 * @return the $amount
	 */
	public function getAmount() {
		return Format_Util::my_money_format('%.2n', $this->amount);
	}

	/**
	 * @return the $type
	 */
	public function getType() {
		if (intval($this->type) === 1) {
			return '普票';
		} else {
			return '增票<br><br><div>纳税人识别号码： ' . $this->d1 . '<div>地址、电话： '
					. $this->d2 . '<div>开户行及账号： ' . $this->d3;
		}
	}

	public function getType_radio1() {
		if (intval($this->type) === 1) {
			return 'checked="checked"';
		} else {
			return '';
		}
	}

	public function getType_radio2() {
		if (intval($this->type) === 2) {
			return 'checked="checked"';
		} else {
			return '';
		}
	}

	public function select_type() {
		$s = '';
		if (in_array(intval($this->type), array(1, 2), TRUE)) {
			$s .= 'showzp(' . intval($this->type) . ');';
		}
		$s .= '$(\'input:radio[name="billtype"]\').each(function(){
			if($(this).val()==' . $this->billtype
				. '){
				$(this).attr("checked",true);
			}
			$(this).attr("disabled",true);
		});';
		return $s;
	}

	/**
	 * @return the $title
	 */
	public function getTitle() {
		return $this->title;
	}

	/**
	 * @return the $content
	 */
	public function getContent() {
		return $this->content;
	}

	public function getRemark() {
		return Format_Util::format_html($this->remark);
	}

	public function getSourceRemark() {
		return $this->remark;
	}

	/**
	 * @return the $has_invoice_permission
	 */
	public function getHas_invoice_permission() {
		return $this->has_invoice_permission;
	}

	/**
	 * @return the $company
	 */
	public function getCompany() {
		return $this->company;
	}

	/**
	 * @return the $time
	 */
	public function getTime() {
		return $this->time;
	}

	public function getNone1() {
		return intval($this->p) === 1 ? 'none' : '';
	}

	public function getNone2() {
		return intval($this->p) === 1 ? '' : 'none';
	}

	/**
	 * @return the $id
	 */
	public function getId() {
		return $this->id;
	}

	/**
	 * @return the $view_myinvoice
	 */
	public function getView_myinvoice() {
		return $this->view_myinvoice;
	}

	public function __construct($id = NULL, $fields = array()) {
		parent::__construct();
		if (!empty($fields) && !self::validate_id($id)) {
			foreach ($this as $key => $value) {
				if ($fields[$key] !== NULL
						&& !in_array($key,
								array('has_leader_audit_invoice_permission',
										'has_invoice_permission',
										'view_myinvoice', 'view_normalinvoice'),
								TRUE)) {
					$this->$key = $fields[$key];
				}
			}
		}
		
		if (in_array($this->getUsername(),
				$GLOBALS['manager_finance_permission'], TRUE)
				|| intval($this->getBelong_dep()) === 2) {
					$this->has_invoice_permission = TRUE;
				}
				
				if ($this->getHas_invoice_tab()) {
			$this->has_leader_audit_invoice_permission = TRUE;
		} 

		if (self::validate_id($id)) {
			$row = $this->db
					->get_row(
							'SELECT a.*,FROM_UNIXTIME(a.time) AS tt,b.username,b.realname,b.city,b.dep,c.username AS gdusername,c.realname AS gdrealname FROM finance_invoice_list a LEFT JOIN users b ON a.user=b.uid LEFT JOIN users c ON a.gduser=c.uid WHERE a.id='
									. $id);

			if ($row !== NULL) {
				if (intval($row->user) === intval($this->getUid())) {
					$this->view_myinvoice = TRUE;
				} 
				if ($this->getHas_invoice_search_permission()) {
					$invoice_array = $this->getInvoice_array();

					$citys = City::getInstance();
					$deps = Dep::getInstance();
					$dep = array();
					foreach ($deps as $key => $value) {
						$dep[$key] = $value[0];
					}

					foreach ($invoice_array as $key => $value) {
						$value = str_replace(array('（', '）', '(', ')'),
								array('', '', '', ''), $value);
						$value = explode(' ', $value);

						if (count($value) === 2) {
							//查询分公司
							if (array_search($value[1], $citys) !== FALSE
									&& intval(array_search($value[1], $citys))
											=== intval($row->city)) {
								$this->view_normalinvoice = TRUE;
								break;
							}
						} else if (count($value) === 3) {
							//查询某部门
							if (array_search($value[2], $dep) !== FALSE
									&& intval(array_search($value[2], $dep))
											=== intval($row->dep)) {
								$this->view_normalinvoice = TRUE;
								break;
							}
						}
					}
				}

				$this->id = $row->id;
				$this->amount = $row->amount;
				$this->type = $row->type;
				$this->title = $row->title;
				$this->content = $row->content;
				$this->remark = $row->remark;
				$this->d1 = $row->d1;
				$this->d2 = $row->d2;
				$this->d3 = $row->d3;
				$this->company = $row->company;
				$this->time = $row->tt;
				$this->date = $row->date;
				$this->number = $row->number;
				$this->auditmsg = $row->auditmsg;
				$this->isok = $row->isok;
				$this->pids = $row->pids;
				$this->username = $row->username;
				$this->realname = $row->realname;
				$this->dep = $row->dep;
				$this->p = $row->print;
				$this->gdusername = $row->gdusername;
				$this->gdrealname = $row->gdrealname;
				$this->step = $row->step;
			}
		}
	}

	/**
	 * @return the $view_normalinvoice
	 */
	public function getView_normalinvoice() {
		return $this->view_normalinvoice;
	}

	public function get_invoice_process_list() {
		$process = Process::getInstance();
		$finance_processes = $process['module'][7]; //财务模块流程
		$step_process = $process['step']; //流程步骤

		$result = '';
		$i = 0;
		foreach ($finance_processes as $finance_processe) {
			if (strpos($finance_processe['name'], '开票') !== FALSE) {
				$content = '';
				$tmp = $step_process[$finance_processe['id']];
				foreach ($tmp as $key => $t) {
					$content .= ($key !== 0 ? ' -> ' : '') . $t['content'][0];
				}
				$result .= '<li><input type="radio" name="process" value="'
						. $finance_processe['id'] . '" class="checkbox" '
						. ($i === 0 ? 'checked="checked"' : '')
						. '><span style="display:none">' . $content
						. '</span><label>' . $finance_processe['name']
						. '</label></li>';
				$i++;
			}
		}
		return $result;
	}

	public function get_process_list($dep = NULL, $pcid = NULL) {
		$process = Process::getInstance();
		$finance_processes = $process['module'][7]; //财务模块流程
		$step_process = $process['step']; //流程步骤

		$result = '';
		$i = 0;
		$use_dep = $dep === NULL ? $this->getBelong_dep() : $dep;
		foreach ($finance_processes as $finance_processe) {
			$deps = explode('^', $finance_processe['deps']);
			if (in_array($use_dep, $deps)) {
				$content = '';
				$tmp = $step_process[$finance_processe['id']];
				foreach ($tmp as $key => $t) {
					$content .= ($key !== 0 ? ' -> ' : '') . $t['content'][0];
				}
				$result .= '<li><input type="radio" name="process" value="'
						. $finance_processe['id'] . '" class="checkbox" '
						. ($i === 0 && $pcid === NULL
								|| $pcid !== NULL
										&& intval($pcid)
												=== intval(
														$finance_processe['id']) ? 'checked="checked"'
								: '') . '><span style="display:none">'
						. $content . '</span><label>'
						. $finance_processe['name'] . '</label></li>';

				$i++;
			}
		}

		if ($result === '') {
			$result = '<font color=red>警告！您所在部门当前没有设定流程，请联系系统管理员！</font>';
		}

		return $result;
	}

	private function validate_form_value($action) {
		$errors = array();
		if (in_array($action,
				array('add', 'print', 'reject', 'invoice', 'leader_confirm',
						'leader_reject', 'gd_update', 'myupdate'), TRUE)) {
			if (in_array($action,
					array('print', 'reject', 'invoice', 'leader_confirm',
							'leader_reject', 'gd_update'), TRUE)) {
				if (!self::validate_id(intval($this->id))) {
					$errors[] = '开票记录选择有误';
				}

				if ($action === 'invoice' || $action === 'gd_update') {
					if (!self::validate_field_not_empty($this->date)
							|| !self::validate_field_not_null($this->date)) {
						$errors[] = '开票日期不能为空';
					} else if (strtotime($this->date) === FALSE) {
						$errors[] = '开票日期不是一个有效的时间值';
					}

					if (!self::validate_field_not_empty($this->number)
							|| !self::validate_field_not_null($this->number)) {
						$errors[] = '开票号码不能为空';
					} else if (!self::validate_field_max_length($this->number,
							500)) {
						$errors[] = '开票号码长度最多500个字符';
					}
				} else if ($action !== 'print') {
					if (self::validate_field_not_empty($this->audit_remark)
							&& !self::validate_field_max_length(
									$this->audit_remark, 200)) {
						$errors[] = '审核意见长度最多200个字符';
					}
				}
			} else {
				$pids_array = $this->pids_array;
				if (empty($pids_array)) {
					$errors[] = '没有选择关联执行单';
				} else {
					$count = 0;
					$now_company = '';
					foreach ($pids_array as $key => $value) {
						if (!self::validate_invoice_money($value['amount'])) {
							$errors[] = '执行单' . $key . '的开票金额有误';
						}
						if ($value['company'] !== $now_company && $count !== 0) {
							$errors[] = '执行单' . $key . '的所属公司与其他不同';
						} else {
							$now_company = $value['company'];
						}
						if ($value['oldamount'] < $value['amount']) {
							$errors[] = '执行单' . $key . '的开票金额最多'
									. $value['oldamount'] . '元';
						}
						$count++;
					}
				}

				if (!self::validate_field_not_empty($this->title)
						|| !self::validate_field_not_null($this->title)) {
					$errors[] = '开具普票时开票抬头不能为空';
				} else if (!self::validate_field_max_length($this->title, 200)) {
					$errors[] = '开票抬头长度最多200个字符';
				}

				if (!self::validate_field_not_empty($this->content)
						|| !self::validate_field_not_null($this->content)) {
					$errors[] = '开具内容不能为空';
				} else if (!self::validate_field_max_length($this->content, 200)) {
					$errors[] = '开具内容长度最多200个字符';
				}

				if (intval($this->invoice_type) === 2) {
					if (!self::validate_field_not_empty($this->d1)
							|| !self::validate_field_not_null($this->d1)) {
						$errors[] = '开具增票时纳税人识别号不能为空';
					} else if (!self::validate_field_max_length($this->d1, 200)) {
						$errors[] = '纳税人识别号长度最多200个字符';
					}

					if (!self::validate_field_not_empty($this->d2)
							|| !self::validate_field_not_null($this->d2)) {
						$errors[] = '开具增票时地址、电话不能为空';
					} else if (!self::validate_field_max_length($this->d2, 200)) {
						$errors[] = '地址、电话长度最多200个字符';
					}

					if (!self::validate_field_not_empty($this->d3)
							|| !self::validate_field_not_null($this->d3)) {
						$errors[] = '开具增票时开户行及账号不能为空';
					} else if (!self::validate_field_max_length($this->d3, 200)) {
						$errors[] = '开户行及账号长度最多200个字符';
					}
				}

				if (self::validate_field_not_empty($this->invoicecontent)
						&& !self::validate_field_max_length(
								$this->invoicecontent, 500)) {
					$errors[] = '备注长度最多500个字符';
				}

				if (!self::validate_field_not_empty($this->process)
						|| !self::validate_field_not_null($this->process)) {
					$errors[] = '流程选择不能为空';
				} else if (!self::validate_id(intval($this->process))) {
					$errors[] = '流程选择有误';
				}

				if ($action === 'myupdate'
						&& !self::validate_id(intval($this->id))) {
					$errors[] = '开票信息选择有误';
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

	public function getSumPidInvoice($pid) {
		$sum = 0;
		//正数+已归档状态的负数 计入额度
		$results = $this->db
				->get_results(
						'SELECT invoice_list_id,amount FROM finance_invoice WHERE pid="'
								. $pid . '" AND isok=1');
		if ($results !== NULL) {
			foreach ($results as $result) {
				if ($result->amount >= 0) {
					//正数，计入额度
					$sum += $result->amount;
				} else {
					//负数检查开票状态
					$row = $this->db
							->get_row(
									'SELECT date,number FROM finance_invoice_list WHERE id='
											. $result->invoice_list_id
											. ' AND isok=1');
					if ($row !== NULL) {
						if (!empty($row->date) && !empty($row->number)) {
							//已归档
							$sum += $result->amount;
						}
					}
				}
			}
		}
		return $sum;
	}

	public function invoice_apply() {
		if ($this->validate_form_value('add')) {
			$success = TRUE;
			$error = '';
			$this->db->query('BEGIN');

			$pids_array = $this->pids_array;
			$ids = array();
			$content = array();
			$all_amount = 0;
			$company = '';
			$pid_amount = array();

			foreach ($pids_array as $pid => $value) {
				$done_amount = $this->getSumPidInvoice($pid);
				if ($done_amount <= 0 && $value['amount'] < 0) {
					//申请过正数才可以申请负数
					$success = FALSE;
					$error = '执行单' . $pid . '还未申请过正数金额的发票，不可申请负数金额的发票';
					break;
				}

				$plan_amount = $this->db
						->get_var(
								'SELECT amount FROM executive WHERE pid="'
										. $pid
										. '" AND isok<>-1 ORDER BY isalter DESC LIMIT 1');

				$done = round($done_amount + floatval($value['amount']),2);
				if($done < 0){
					$success = FALSE;
					$error = '执行单' . $pid . '的申请开票总金额不可为负数';
					break;
				}
				
				$plan= round(floatval($plan_amount),2);
				if ($done > $plan) {	
					$success = FALSE;
					$error = '执行单' . $pid . '的申请开票总金额大于执行单金额';
					break;
				}
			}

			if ($success) {
				foreach ($pids_array as $pid => $value) {
					if ($company === '') {
						$company = Executive::get_companyname(
								intval($value['company']));
					}
					$content[] = $pid . '^' . $value['amount'];
					$pid_amount[$pid] = $value['amount'];
					$all_amount += $value['amount'];
					$insert_result = $this->db
							->query(
									'INSERT INTO finance_invoice(pid,amount,user,time) VALUE("'
											. $pid . '","' . $value['amount']
											. '",' . $this->getUid() . ','
											. $_SERVER['REQUEST_TIME'] . ')');
					if ($insert_result !== FALSE) {
						$ids[] = $this->db->insert_id;
					} else {
						$success = FALSE;
						$error = '申请开票失败，错误代码1';
						break;
					}
				}
			}

			/*
			if ($success) {
			    if (!empty($pid_amount)) {
			        foreach ($pid_amount as $_pid => $_amount) {
			            $done_amount = $this->getSumPidInvoice($_pid);
			            if($done_amount <= 0 && $_amount < 0){
			                //申请过正数才可以申请负数
			                $success = FALSE;
			                $error = '执行单' . $_pid . '还未申请过正数金额的发票，不可申请负数金额的发票';
			                break;
			            }
			            
			            $plan_amount = $this->db
			                    ->get_var(
			                            'SELECT amount FROM executive WHERE pid="'
			                                    . $_pid
			                                    . '" AND isok<>-1 ORDER BY isalter DESC LIMIT 1');
			            if (($done_amount + $_amount ) > $plan_amount) {
			                $success = FALSE;
			                $error = '执行单' . $_pid . '的申请开票总金额大于执行单金额';
			                break;
			            }
			        }
			    }
			}
			 */

			if ($success) {
				$insert_result = $this->db
						->query(
								'INSERT INTO finance_invoice_list(invoice_ids,pids,amount,type,d1,d2,d3,title,content,remark,company,user,time,pcid,step) VALUE("'
										. implode('^', $ids) . '","'
										. implode('|', $content) . '","'
										. $all_amount . '","'
										. $this->invoice_type . '","'
										. (intval($this->invoice_type) === 2 ? $this
														->d1 : '') . '","'
										. (intval($this->invoice_type) === 2 ? $this
														->d2 : '') . '","'
										. (intval($this->invoice_type) === 2 ? $this
														->d3 : '') . '","'
										. $this->title . '","' . $this->content
										. '","' . $this->invoicecontent . '","'
										. $company . '",' . $this->getUid()
										. ',' . $_SERVER['REQUEST_TIME'] . ',"'
										. $this->process . '",1)');
				if ($insert_result === FALSE) {
					$success = FALSE;
					$error = '申请开票失败，错误代码2';
				} else {
					$insert_id = $this->db->insert_id;
				}
			}

			if ($success && $insert_id > 0) {
				$update_result = $this->db
						->query(
								'UPDATE finance_invoice SET invoice_list_id='
										. $insert_id . ' WHERE id IN ('
										. implode(',', $ids) . ')');
				if ($update_result === FALSE) {
					$success = FALSE;
					$error = '申请开票失败，错误代码3';
				}
			}

			if ($success) {
				$this->db->query('COMMIT');
			} else {
				$this->db->query('ROLLBACK');
			}

			return array('status' => $success ? 'success' : 'error',
					'message' => $success ? '申请开票成功' : $error);
		}
		return array('status' => 'error', 'message' => $this->errors);
	}

	public function invoice_myupdate() {
		if ($this->validate_form_value('myupdate')) {
			$success = TRUE;
			$error = '';
			$this->db->query('BEGIN');

			$delete_result = $this->db
					->query(
							'DELETE FROM finance_invoice WHERE invoice_list_id='
									. intval($this->id));
			if ($delete_result !== FALSE) {

				$pids_array = $this->pids_array;
				$ids = array();
				$content = array();
				$all_amount = 0;
				$company = '';
				$pid_amount = array();

				foreach ($pids_array as $pid => $value) {
					$done_amount = $this->getSumPidInvoice($pid);

					if ($done_amount <= 0 && $value['amount'] < 0) {
						//申请过正数才可以申请负数
						$success = FALSE;
						$error = '执行单' . $pid . '还未申请过正数金额的发票，不可申请负数金额的发票';
						break;
					}

					$plan_amount = $this->db
							->get_var(
									'SELECT amount FROM executive WHERE pid="'
											. $pid
											. '" AND isok<>-1 ORDER BY isalter DESC LIMIT 1');
					$done = round($done_amount + floatval($value['amount']),2);
					$plan= round(floatval($plan_amount),2);
					if ($done > $plan) {
						$success = FALSE;
						$error = '执行单' . $pid . '的申请开票总金额大于执行单金额';
						break;
					}
				}

				if ($success) {
					foreach ($pids_array as $pid => $value) {
						if ($company === '') {
							$company = Executive::get_companyname(
									intval($value['company']));
						}
						$content[] = $pid . '^' . $value['amount'];
						$pid_amount[$pid] = $value['amount'];
						$all_amount += $value['amount'];
						$insert_result = $this->db
								->query(
										'INSERT INTO finance_invoice(invoice_list_id,pid,amount,user,time) VALUE('
												. intval($this->id) . ',"'
												. $pid . '","'
												. $value['amount'] . '",'
												. $this->getUid() . ','
												. $_SERVER['REQUEST_TIME']
												. ')');
						if ($insert_result !== FALSE) {
							$ids[] = $this->db->insert_id;
						} else {
							$success = FALSE;
							$error = '修改开票信息失败，错误代码2';
							break;
						}
					}
				}

				/*
				    if ($success) {
				        if (!empty($pid_amount)) {
				            foreach ($pid_amount as $_pid => $_amount) {
				                $done_amount = $this->getSumPidInvoice($_pid);
				
				                if ($done_amount <= 0 && $_amount < 0) {
				                    //申请过正数才可以申请负数
				                    $success = FALSE;
				                    $error = '执行单' . $_pid
				                            . '还未申请过正数金额的发票，不可申请负数金额的发票';
				                    break;
				                }
				
				                $plan_amount = $this->db
				                        ->get_var(
				                                'SELECT amount FROM executive WHERE pid="'
				                                        . $_pid
				                                        . '" AND isok<>-1 ORDER BY isalter DESC LIMIT 1');
				                if ($done_amount > $plan_amount) {
				                    $success = FALSE;
				                    $error = '执行单' . $_pid . '的申请开票总金额大于执行单金额';
				                    break;
				                }
				            }
				        }
				    }
				 */

				if ($success) {
					$update_result = $this->db
							->query(
									'UPDATE finance_invoice_list SET invoice_ids="'
											. implode('^', $ids) . '",pids="'
											. implode('|', $content)
											. '",amount=' . $all_amount
											. ',type=' . $this->invoice_type
											. ',d1="'
											. (intval($this->invoice_type)
													=== 2 ? $this->d1 : '')
											. '",d2="'
											. (intval($this->invoice_type)
													=== 2 ? $this->d2 : '')
											. '",d3="'
											. (intval($this->invoice_type)
													=== 2 ? $this->d3 : '')
											. '",title="' . $this->title
											. '",content="' . $this->content
											. '",remark="'
											. $this->invoicecontent
											. '",company="' . $company
											. '",user="' . $this->getUid()
											. '",time='
											. $_SERVER['REQUEST_TIME']
											. ',step=1,isok=0,print=0,auditmsg="" WHERE id='
											. intval($this->id));
					if ($update_result === FALSE) {
						$success = FALSE;
						$error = '修改开票信息失败，错误代码3';
					}
				}

			} else {
				$success = FALSE;
				$error = '修改开票信息失败，错误代码1';
			}

			if ($success) {
				$this->db->query('COMMIT');
			} else {
				$this->db->query('ROLLBACK');
			}

			return array('status' => $success ? 'success' : 'error',
					'message' => $success ? '修改开票信息成功' : $error);
		}
		return array('status' => 'error', 'message' => $this->errors);
	}

	public function invoice_print() {
		if ($this->validate_form_value('print')) {
			$update_result = $this->db
					->query(
							'UPDATE finance_invoice_list SET auditmsg="'
									. $this->audit_remark
									. '",print=1 WHERE id=' . intval($this->id));
			if ($update_result === FALSE) {
				return array('status' => 'error', 'message' => '确认打印失败');
			}
			return array('status' => 'success', 'message' => '确认打印成功');
		}
		return array('status' => 'error', 'message' => $this->errors);
	}

	public function invoice_reject() {
		if ($this->validate_form_value('reject')) {
			$success = TRUE;
			$error = '';
			$this->db->query('BEGIN');

			$update_result = $this->db
					->query(
							'UPDATE finance_invoice_list SET auditmsg="'
									. $this->audit_remark
									. '",isok=-1 WHERE id=' . intval($this->id));
			if ($update_result === FALSE) {
				$success = FALSE;
				$error = '审核驳回失败，错误代码1';
			} else {
				$update_result = $this->db
						->query(
								'UPDATE finance_invoice SET isok=-1 WHERE invoice_list_id='
										. intval($this->id));
				if ($update_result === FALSE) {
					$success = FALSE;
					$error = '审核驳回失败，错误代码2';
				}
			}

			if ($success) {
				$this->db->query('COMMIT');
			} else {
				$this->db->query('ROLLBACK');
			}

			return array('status' => $success ? 'success' : 'error',
					'message' => $success ? '审核驳回成功' : $error);
		}
		return array('status' => 'error', 'message' => $this->errors);
	}

	public function invoice_make() {
		if ($this->validate_form_value('invoice')) {
			$update_result = $this->db
					->query(
							'UPDATE finance_invoice_list SET date="'
									. $this->date . '",number="'
									. $this->number . '",isok=1,gduser='
									. $this->getUid() . ',gdtime='
									. $_SERVER['REQUEST_TIME'] . ' WHERE id='
									. intval($this->id));
			if ($update_result === FALSE) {
				return array('status' => 'error', 'message' => '确认开票失败');
			}
			return array('status' => 'success', 'message' => '确认开票成功');
		}
		return array('status' => 'error', 'message' => $this->errors);
	}

	public function invoice_gd_update() {
		if ($this->validate_form_value('gd_update')) {
			$update_result = $this->db
					->query(
							'UPDATE finance_invoice_list SET date="'
									. $this->date . '",number="'
									. $this->number . '" WHERE id='
									. intval($this->id));
			if ($update_result === FALSE) {
				return array('status' => 'error', 'message' => '更新开票信息失败');
			}
			return array('status' => 'success', 'message' => '更新开票信息成功');
		}
		return array('status' => 'error', 'message' => $this->errors);
	}

	public function invoice_leader_confirm() {
		if ($this->validate_form_value('leader_confirm')) {
			$success = TRUE;
			$error = '';
			$this->db->query('BEGIN');

			$update_result = $this->db
					->query(
							'UPDATE finance_invoice_list SET step=2 WHERE id='
									. intval($this->id));
			if ($update_result === FALSE) {
				$success = FALSE;
				$error = '审核确认失败';
			}

			if ($success) {
				$this->db->query('COMMIT');
			} else {
				$this->db->query('ROLLBACK');
			}

			return array('status' => $success ? 'success' : 'error',
					'message' => $success ? '审核确认成功' : $error);
		}
		return array('status' => 'error', 'message' => $this->errors);
	}

	public function invoice_leader_reject() {
		if ($this->validate_form_value('leader_reject')) {
			$success = TRUE;
			$error = '';
			$this->db->query('BEGIN');

			$update_result = $this->db
					->query(
							'UPDATE finance_invoice_list SET auditmsg="'
									. $this->audit_remark
									. '",isok=-1 WHERE id=' . intval($this->id));
			if ($update_result === FALSE) {
				$success = FALSE;
				$error = '审核驳回失败，错误代码1';
			} else {
				$update_result = $this->db
						->query(
								'UPDATE finance_invoice SET isok=-1 WHERE invoice_list_id='
										. intval($this->id));
				if ($update_result === FALSE) {
					$success = FALSE;
					$error = '审核驳回失败，错误代码2';
				}
			}

			if ($success) {
				$this->db->query('COMMIT');
			} else {
				$this->db->query('ROLLBACK');
			}

			return array('status' => $success ? 'success' : 'error',
					'message' => $success ? '审核驳回成功' : $error);
		}
		return array('status' => 'error', 'message' => $this->errors);
	}

	public function getExist_pids() {
		$pids = $this->pids;
		$s = '';
		$billtype = 1;
		if (!empty($pids)) {
			$pids = explode('|', $pids);
			foreach ($pids as $key => $pid) {
				$pid = explode('^', $pid);
				$row = $this->db
						->get_row(
								'SELECT a.name,a.company,a.amount,a.cid,b.billtype FROM executive a LEFT JOIN contract_cus b ON a.cid=b.cid WHERE a.pid="'
										. $pid[0]
										. '" ORDER BY a.id DESC LIMIT 1');
				if ($key === 0) {
					$this->billtype = intval($row->billtype);
				}

				$sum_in = new Invoice();
				$amount = $sum_in->getSumPidInvoice($pid[0]);
				unset($sum_in);

				$s .= '<div><img src="' . BASE_URL
						. 'images/close.png" onclick="removepid(this,\''
						. $pid[0]
						. '\')" width="12" height="12" />
					  	&nbsp;<span id="pid" style="display:inline-block;width:90px;text-align:left">'
						. $pid[0] . '</span>
					  	<span title="' . $row->name
						. '" style="display:inline-block;width:140px;text-align:left">'
						. String_Util::cut_str($row->name, 10, 0, 'UTF-8',
								'...')
						. '</span>
					  	<span id="company" style="display:inline-block;width:80px;text-align:center">【'
						. Executive::get_companyname(intval($row->company))
						. '】</span>
					  	&nbsp;&nbsp;<font color="#ff9933">已开票: </font>
					  	<span style="display:inline-block;width:80px;"><font color="#ff9933"><b>'
						. Format_Util::my_money_format('%.2n', $amount)
						. '</b></font></span>
					  	&nbsp;&nbsp;&nbsp;&nbsp;<font color="blue">未开票: </font>
					  	<input type="hidden" name="company_' . $pid[0]
						. '" value="' . $row->company
						. '"/><input type="text" class="validate[required,custom[invoiceMoney],max['
						. round($row->amount - $amount, 2)
						. ']] text" style="width:80px;text-align:right " name="amount_'
						. $pid[0] . '" id="amount_' . $pid[0]
						. '" onblur="getallamount();" value="' . $pid[1]
						. '" /><input type="hidden" name="oldamount_' . $pid[0]
						. '" id="oldamount_' . $pid[0] . '" value="'
						. round($row->amount - $amount, 2) . '"></div>';
			}
		}
		return $s;
	}

	public function get_invoice_apply_html() {
		$buf = file_get_contents(
				TEMPLATE_PATH . 'finance/invoice/invoice_apply.tpl');
		return str_replace(
				array('[LEFT]', '[TOP]', '[VCODE]', '[PROCESSLIST]',
						'[INVOICETAB]', '[BASE_URL]'),
				array($this->get_left_html(), $this->get_top_html(),
						$this->get_vcode(), $this->get_invoice_process_list(),
						$this->get_invoice_tab(), BASE_URL), $buf);
	}

	public function get_invoice_view_html() {
		if ($this->getHas_invoice_permission()) {
			$buf = file_get_contents(
					TEMPLATE_PATH . 'finance/invoice/invoice_view.tpl');
			return str_replace(
					array('[LEFT]', '[TOP]', '[TIME]', '[AMOUNT]', '[TYPE]',
							'[TITLE]', '[CONTENT]', '[REMARK]', '[COMPANY]',
							'[PIDINFO]', '[DATE]', '[NUMBER]', '[AUDITMSG]',
							'[INVOICETYPE]', '[BASE_URL]'),
					array($this->get_left_html(), $this->get_top_html(),
							$this->time, $this->getAmount(), $this->getType(),
							$this->title, $this->content, $this->getRemark(),
							$this->company, $this->getPidinfo(), $this->date,
							$this->number, $this->getAuditmsg(),
							$this->get_invoice_type(), BASE_URL), $buf);
		} else {
			return User::no_permission();
		}
	}

	public function get_invoice_myview_html() {
		if ($this->getView_myinvoice()) {
			$buf = file_get_contents(
					TEMPLATE_PATH . 'finance/invoice/invoice_myview.tpl');
			return str_replace(
					array('[LEFT]', '[TOP]', '[TIME]', '[AMOUNT]', '[TYPE]',
							'[TITLE]', '[CONTENT]', '[REMARK]', '[COMPANY]',
							'[PIDINFO]', '[DATE]', '[NUMBER]', '[AUDITMSG]',
							'[INVOICETYPE]', '[BASE_URL]'),
					array($this->get_left_html(), $this->get_top_html(),
							$this->time, $this->getAmount(), $this->getType(),
							$this->title, $this->content, $this->getRemark(),
							$this->company, $this->getPidinfo(), $this->date,
							$this->number, $this->getAuditmsg(),
							$this->get_invoice_type(), BASE_URL), $buf);
		} else {
			return User::no_permission();
		}
	}

	public function get_invoice_normalview_html() {
		if ($this->getView_normalinvoice()) {
			$buf = file_get_contents(
					TEMPLATE_PATH . 'finance/invoice/invoice_myview.tpl');
			return str_replace(
					array('[LEFT]', '[TOP]', '[TIME]', '[AMOUNT]', '[TYPE]',
							'[TITLE]', '[CONTENT]', '[REMARK]', '[COMPANY]',
							'[PIDINFO]', '[DATE]', '[NUMBER]', '[AUDITMSG]',
							'[INVOICETYPE]', '[BASE_URL]'),
					array($this->get_left_html(), $this->get_top_html(),
							$this->time, $this->getAmount(), $this->getType(),
							$this->title, $this->content, $this->getRemark(),
							$this->company, $this->getPidinfo(), $this->date,
							$this->number, $this->getAuditmsg(),
							$this->get_invoice_type(), BASE_URL), $buf);
		} else {
			return User::no_permission();
		}
	}

	public function get_invoice_audit_html() {
		if ($this->getHas_invoice_permission()) {
			$buf = file_get_contents(
					TEMPLATE_PATH . 'finance/invoice/invoice_audit.tpl');
			return str_replace(
					array('[LEFT]', '[TOP]', '[VCODE]', '[TIME]', '[AMOUNT]',
							'[TYPE]', '[TITLE]', '[CONTENT]', '[REMARK]',
							'[COMPANY]', '[PIDINFO]', '[DATE]', '[NUMBER]',
							'[AUDITMSG]', '[NONE1]', '[NONE2]', '[USERINFO]',
							'[ID]', '[INVOICETYPE]', '[BASE_URL]'),
					array($this->get_left_html(), $this->get_top_html(),
							$this->get_vcode(), $this->time,
							$this->getAmount(), $this->getType(), $this->title,
							$this->content, $this->getRemark(), $this->company,
							$this->getPidinfo(), $this->date, $this->number,
							$this->getAuditmsg(), $this->getNone1(),
							$this->getNone2(), $this->getUserinfo(), $this->id,
							$this->get_invoice_type(), BASE_URL), $buf);
		} else {
			return User::no_permission();
		}
	}

	public function get_invoice_leader_audit_html() {
		if ($this->getHas_leader_audit_invoice_permission()) {
			$buf = file_get_contents(
					TEMPLATE_PATH . 'finance/invoice/invoice_leader_audit.tpl');
			return str_replace(
					array('[LEFT]', '[TOP]', '[VCODE]', '[TIME]', '[AMOUNT]',
							'[TYPE]', '[TITLE]', '[CONTENT]', '[REMARK]',
							'[COMPANY]', '[PIDINFO]', '[DATE]', '[NUMBER]',
							'[AUDITMSG]', '[USERINFO]', '[ID]', '[BASE_URL]'),
					array($this->get_left_html(), $this->get_top_html(),
							$this->get_vcode(), $this->time,
							$this->getAmount(), $this->getType(), $this->title,
							$this->content, $this->getRemark(), $this->company,
							$this->getPidinfo(), $this->date, $this->number,
							$this->getAuditmsg(), $this->getUserinfo(),
							$this->id, BASE_URL), $buf);
		} else {
			return User::no_permission();
		}
	}

	public function get_invoice_print_html() {
		if ($this->getHas_invoice_permission()) {
			$buf = file_get_contents(
					TEMPLATE_PATH . 'finance/invoice/invoice_print.tpl');
			return str_replace(
					array('[TIME]', '[AMOUNT]', '[TYPE]', '[TITLE]',
							'[CONTENT]', '[REMARK]', '[COMPANY]', '[PIDINFO]',
							'[USERINFO]', '[INVOICETYPE]', '[OTHERS]'),
					array($this->time, $this->getAmount(), $this->getType(),
							$this->title, $this->content, $this->getRemark(),
							$this->company, $this->getPidinfo(),
							$this->getUserinfo(), $this->get_invoice_type(),
							$this->get_others()), $buf);
		} else {
			return User::no_permission();
		}
	}

	public function get_invoice_edit_html() {
		if ($this->getHas_invoice_permission()) {
			$buf = file_get_contents(
					TEMPLATE_PATH . 'finance/invoice/invoice_edit.tpl');
			return str_replace(
					array('[LEFT]', '[TOP]', '[TIME]', '[AMOUNT]', '[TYPE]',
							'[TITLE]', '[CONTENT]', '[REMARK]', '[COMPANY]',
							'[PIDINFO]', '[DATE]', '[NUMBER]', '[AUDITMSG]',
							'[INVOICETYPE]', '[VCODE]', '[ID]', '[BASE_URL]'),
					array($this->get_left_html(), $this->get_top_html(),
							$this->time, $this->getAmount(), $this->getType(),
							$this->title, $this->content, $this->getRemark(),
							$this->company, $this->getPidinfo(), $this->date,
							$this->number, $this->getAuditmsg(),
							$this->get_invoice_type(), $this->get_vcode(),
							$this->id, BASE_URL), $buf);
		} else {
			return User::no_permission();
		}
	}

	public function get_invoice_myedit_html() {
		if ($this->getView_myinvoice()) {
			$buf = file_get_contents(
					TEMPLATE_PATH . 'finance/invoice/invoice_myedit.tpl');
			return str_replace(
					array('[LEFT]', '[TOP]', '[AMOUNT]', '[TITLE]',
							'[CONTENT]', '[REMARK]', '[VCODE]',
							'[PROCESSLIST]', '[D1]', '[D2]', '[D3]',
							'[TYPERADIO1]', '[TYPERADIO2]', '[EXISTPIDS]',
							'[SELECTTYPE]', '[PIDS]', '[ID]', '[BASE_URL]'),
					array($this->get_left_html(), $this->get_top_html(),
							$this->getAmount(), $this->title, $this->content,
							$this->remark, $this->get_vcode(),
							$this->get_invoice_process_list(), $this->getD1(),
							$this->getD2(), $this->getD3(),
							$this->getType_radio1(), $this->getType_radio2(),
							$this->getExist_pids(), $this->select_type(),
							$this->getHidden_pids(), $this->id, BASE_URL),
					$buf);
		} else {
			return User::no_permission();
		}
	}

	public function getIsok() {
		return $this->isok;
	}

	public function get_invoice_normal_print_html() {
		if ($this->getView_normalinvoice()) {
			if (intval($this->isok) === 1
					|| (intval($this->step) === 2
							&& intval($this->getBelong_dep()) === 8)) {
				$buf = file_get_contents(
						TEMPLATE_PATH . 'finance/invoice/invoice_print.tpl');
				return str_replace(
						array('[TIME]', '[AMOUNT]', '[TYPE]', '[TITLE]',
								'[CONTENT]', '[REMARK]', '[COMPANY]',
								'[PIDINFO]', '[USERINFO]', '[INVOICETYPE]',
								'[OTHERS]'),
						array($this->time, $this->getAmount(),
								$this->getType(), $this->title, $this->content,
								$this->getRemark(), $this->company,
								$this->getPidinfo(), $this->getUserinfo(),
								$this->get_invoice_type(), $this->get_others()),
						$buf);
			} else {
				return User::no_object('该发票状态不可打印');
			}
		} else {
			return User::no_permission();
		}
	}

	public function get_import_invoice_html() {
		if ($this->has_invoice_permission) {
			$buf = file_get_contents(
					TEMPLATE_PATH . 'finance/invoice/invoice_import.tpl');
			return str_replace(
					array('[LEFT]', '[TOP]', '[VCODE]', '[VALIDATEFILE]',
							'[MAXFILESIZE]', '[BASE_URL]'),
					array($this->get_left_html(), $this->get_top_html(),
							$this->get_vcode(),
							implode(',',
									$GLOBALS['defined_upload_execel_validate_type']),
							UPLOAD_FILE_MAX_SIZE / (1024 * 1024), BASE_URL),
					$buf);
		} else {
			return User::no_permission();
		}
	}

	public function getGdusername() {
		return $this->gdusername;
	}

	public function getGdrealname() {
		return $this->gdrealname;
	}

	private function _check_format($line, $infos, &$errors) {
		$isok = TRUE;
		//第一列 执行单号
		if (!self::validate_field_not_null($infos[0])
				|| !self::validate_field_not_empty($infos[0])) {
			$errors[] = '第' . $line . '行，第1列【执行单号】不能为空';
			$isok = $isok ? FALSE : $isok;
		} else if (!self::validate_field_max_length($infos[0], 100)) {
			$errors[] = '第' . $line . '行，第1列【执行单号】长度最多100个字符';
			$isok = $isok ? FALSE : $isok;
		}

		//第二列 发票金额
		if (!self::validate_field_not_null($infos[1])) {
			$errors[] = '第' . $line . '行，第2列【发票金额】不能为空';
			$isok = $isok ? FALSE : $isok;
		} else if (!self::validate_invoice_money($infos[1])) {
			$errors[] = '第' . $line . '行，第2列【发票金额】不是有效的金额';
			$isok = $isok ? FALSE : $isok;
		}

		//第三列 发票号码
		if (!self::validate_field_not_null($infos[2])
				|| !self::validate_field_not_empty($infos[2])) {
			$errors[] = '第' . $line . '行，第3列【发票号码】不能为空';
			$isok = $isok ? FALSE : $isok;
		} else if (!self::validate_field_max_length($infos[2], 500)) {
			$errors[] = '第' . $line . '行，第3列【发票号码】长度最多500个字符';
			$isok = $isok ? FALSE : $isok;
		}

		//第四列 开票日期
		if (!self::validate_field_not_null($infos[3])) {
			$errors[] = '第' . $line . '行，第4列【开票日期】不能为空';
			$isok = $isok ? FALSE : $isok;
		} else if (!self::validate_date_int(
				PHPExcel_Shared_Date::ExcelToPHP($infos[3]))) {
			$errors[] = '第' . $line . '行，第4列【开票日期】不是有效的时间值';
			$isok = $isok ? FALSE : $isok;
		}

		//第五列 增票/普票
		if (!in_array($infos[4], array('增票', '普票'), TRUE)) {
			$errors[] = '第' . $line . '行，第5列【增票/普票】输入有误，请输入“增票”或者“普票”';
			$isok = $isok ? FALSE : $isok;
		}

		//第六列 开票抬头
		if (!self::validate_field_not_null($infos[5])
				|| !self::validate_field_not_empty($infos[5])) {
			$errors[] = '第' . $line . '行，第6列【开票抬头】不能为空';
			$isok = $isok ? FALSE : $isok;
		} else if (!self::validate_field_max_length($infos[5], 200)) {
			$errors[] = '第' . $line . '行，第6列【开票抬头】长度最多200个字符';
			$isok = $isok ? FALSE : $isok;
		}

		return $isok;
	}

	public function import_invoice($file) {
		if (!$this->has_invoice_permission) {
			return array('status' => 'error', 'message' => NO_RIGHT_TO_DO_THIS);
		}
		if (file_exists($file)) {
			$PHPExcel = new PHPExcel();
			if (strtolower(pathinfo($file, PATHINFO_EXTENSION)) === 'xls') {
				$PHPReader = new PHPExcel_Reader_Excel5();
			} else if (strtolower(pathinfo($file, PATHINFO_EXTENSION))
					=== 'xlsx') {
				$PHPReader = new PHPExcel_Reader_Excel2007();
			}

			$PHPExcel = $PHPReader->load($file);
			$PHPExcel->setActiveSheetIndex(0);
			$sheet = $PHPExcel->getActiveSheet();
			$sum_cols_count = PHPExcel_Cell::columnIndexFromString(
					$sheet->getHighestColumn());
			$sum_rows_count = $sheet->getHighestRow();
			$errors = array();
			//应该是6列，行数>1
			if ($sum_cols_count !== 6) {
				$errors[] = '上传文件的列数非有效';
			} else if ($sum_rows_count <= 1) {
				$errors[] = '上传文件的行数非有效';
			} else {

				for ($i = 2; $i <= $sum_rows_count; $i++) {
					for ($j = 0; $j < $sum_cols_count; $j++) {
						$infos[$i][$j] = $sheet->getCellByColumnAndRow($j, $i)
								->getCalculatedValue();
						$infos[$i][$j] = $infos[$i][$j] === NULL ? NULL
								: trim($infos[$i][$j]);
					}
					$this->db->query('BEGIN');
					$isok = $this->_check_format($i, $infos[$i], $errors);

					if ($isok) {
						$db_ok = TRUE;

						$row = $this->db
								->get_row(
										'SELECT a.company,a.amount,b.invoice FROM executive a LEFT JOIN (SELECT SUM(amount) AS invoice,pid FROM finance_invoice WHERE pid="'
												. $infos[$i][0]
												. '" AND isok=1) b ON a.pid=b.pid WHERE a.pid="'
												. $infos[$i][0]
												. '" AND a.isok<>-1 ORDER BY a.isalter DESC LIMIT 1');
						if ($row === NULL) {
							$db_ok = FALSE;
							$errors[] = '第' . $i . '行记录执行单号不存在';
						} else {
							$amount = $row->amount;
							$invoice = $row->invoice;
							if ($invoice + $infos[$i][1] > $amount) {
								$db_ok = FALSE;
								$errors[] = '第' . $i . '行记录开票金额大于该执行单还可开票余额';
							} else {
								$company = $row->company;
								if (intval($company) === 3) {
									$company = '新网迈';
								} else if (intval($company) === 1) {
									$company = '网迈广告';
								} else {
									$company = '';
								}

								$result1 = $this->db
										->query(
												'INSERT INTO finance_invoice(pid,amount,user,time) VALUE("'
														. $infos[$i][0] . '",'
														. $infos[$i][1] . ','
														. $this->getUid() . ','
														. $_SERVER['REQUEST_TIME']
														. ')');
								if ($result1 === FALSE) {
									$db_ok = FALSE;
								} else {
									$id = $this->db->insert_id;

									$result2 = $this->db
											->query(
													'INSERT INTO finance_invoice_list(invoice_ids,pids,amount,type,d1,d2,d3,title,content,remark,company,user,time,pcid,step,gduser,gdtime,isok,print,number,date) VALUE("'
															. $id . '","'
															. $infos[$i][0]
															. '^'
															. $infos[$i][1]
															. '","'
															. $infos[$i][1]
															. '","'
															. ($infos[$i][4]
																	=== '增票' ? 2
																	: 1)
															. '","","","","'
															. $infos[$i][5]
															. '","","","'
															. $company . '",'
															. $this->getUid()
															. ','
															. $_SERVER['REQUEST_TIME']
															. ',"56",2,'
															. $this->getUid()
															. ','
															. $_SERVER['REQUEST_TIME']
															. ',1,1,"'
															. $infos[$i][2]
															. '","'
															. date('Y-m-d',
																	PHPExcel_Shared_Date::ExcelToPHP(
																			$infos[$i][3]))
															. '")');
									if ($result2 === FALSE) {
										$db_ok = FALSE;
									} else {
										$list_id = $this->db->insert_id;

										$result3 = $this->db
												->query(
														'UPDATE finance_invoice SET invoice_list_id='
																. $list_id
																. ' WHERE id='
																. $id);
										if ($result3 === FALSE) {
											$db_ok = FALSE;
										}
									}
								}
							}
						}

						if ($db_ok) {
							$this->db->query('COMMIT');
						} else {
							$errors[] = '第' . $i . '行记录导入失败';
							$this->db->query('ROLLBACK');
						}
					} else {
						$this->db->query('COMMIT');
					}
				}
			}

			return array('status' => 'success',
					'message' => empty($errors) ? '导入成功' : $errors);
		} else {
			return array('status' => 'error', 'message' => '上传的文件不存在');
		}
	}
}
