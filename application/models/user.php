<?php
class User extends Dao_Impl {
	private $uid;
	private $username;
	private $realname;
	private $permissions = array ();
	private $errors = array ();
	private $oldpwd;
	private $pwd;
	private $repwd;
	private $belong_city;
	private $belong_dep;
	private $belong_team;
	const SALT_VALUE = '|@ranranba';
	private $has_check_executive_permission = FALSE;
	private $has_manager_executive_permission = FALSE;
	private $has_check_contract_permission = FALSE;
	private $has_manager_contract_permission = FALSE;
	private $has_check_relation_executive_permission = FALSE;
	private $has_check_relation_contract_permission = FALSE;
	private $has_manager_relation_executive_permission = FALSE;
	private $has_manager_relation_contract_permission = FALSE;
	private $all_pending_item_count = 0;
	private $has_invoice_tab = FALSE;
	private $invoices = array ();
	private $has_invoice_search_permission = FALSE;
	private $invoice_array = array ();
	private $receivables_array = array ();
	private $has_receivables_search_permission = FALSE;
	private $has_manager_customer_safety_permission = FALSE;
	private $has_deposit_tab = FALSE;
	private $deposits = array ();
	private $deposit_invoices = array ();
	private $has_supplier_apply_audit_tab = FALSE;
	private $suplier_apply = array ();
	private $city_show;
	private $payment_media_step_process = array ();
	private $has_payment_person_audit_tab = FALSE;
	private $has_payment_person_deposit_audit_tab = FALSE;
	private $contract_payment_person = array ();
	private $has_outsourcing_audit_tab = FALSE;
	private $outsourcing_audits = array ();
	private $token;
	public function getToken() {
		return $this->token;
	}
	
	// private $payment_apply_array = array();
	
	// public function getPaymentApplyArray(){
	// return $this->payment_apply_array;
	// }
	/**
	 *
	 * @return the $invoice_array
	 */
	public function getInvoice_array() {
		return $this->invoice_array;
	}
	
	/**
	 *
	 * @return the $has_invoice_tab
	 */
	public function getHas_invoice_tab() {
		return $this->has_invoice_tab;
	}
	public function getHas_contract_payment_tab() {
		return $this->has_payment_person_audit_tab;
	}
	public function getContractPayments() {
		return $this->contract_payment_person;
	}
	public function getHasOutsourcingAuditTab() {
		return $this->has_outsourcing_audit_tab;
	}
	public function getOutsourcingAudit() {
		return $this->outsourcing_audits;
	}
	/**
	 *
	 * @return the $invoices
	 */
	public function getInvoices() {
		return $this->invoices;
	}
	
	/**
	 *
	 * @return the $has_check_contract_permission
	 */
	public function getHas_check_contract_permission() {
		return $this->has_check_contract_permission;
	}
	
	/**
	 *
	 * @return the $has_manager_contract_permission
	 */
	public function getHas_manager_contract_permission() {
		return $this->has_manager_contract_permission;
	}
	
	/**
	 *
	 * @return the $has_manager_executive_permission
	 */
	public function getHas_manager_executive_permission() {
		return $this->has_manager_executive_permission;
	}
	
	/**
	 *
	 * @return the $has_check_executive_permission
	 */
	public function getHas_check_executive_permission() {
		return $this->has_check_executive_permission;
	}
	
	/**
	 *
	 * @return the $permissions
	 */
	public function getPermissions() {
		return $this->permissions;
	}
	
	/**
	 *
	 * @return the $belong_city
	 */
	public function getBelong_city() {
		return $this->belong_city;
	}
	
	/**
	 *
	 * @return the $belong_team
	 */
	public function getBelong_team() {
		return $this->belong_team;
	}
	
	/**
	 *
	 * @return the $belong_dep
	 */
	public function getBelong_dep() {
		return $this->belong_dep;
	}
	
	/**
	 *
	 * @return the $uid
	 */
	public function getUid() {
		return $this->uid;
	}
	
	/**
	 *
	 * @param field_type $oldpwd        	
	 */
	public function setOldpwd($oldpwd) {
		$this->oldpwd = self::validate_field_not_null ( $oldpwd ) && self::validate_field_not_empty ( $oldpwd ) ? String_Util::my_md5 ( $oldpwd, 1 ) : NULL;
	}
	
	/**
	 *
	 * @param field_type $pwd        	
	 */
	public function setPwd($pwd) {
		$this->pwd = self::validate_field_not_null ( $pwd ) && self::validate_field_not_empty ( $pwd ) ? String_Util::my_md5 ( $pwd, 1 ) : NULL;
	}
	
	/**
	 *
	 * @param field_type $repwd        	
	 */
	public function setRepwd($repwd) {
		$this->repwd = self::validate_field_not_null ( $repwd ) && self::validate_field_not_empty ( $repwd ) ? String_Util::my_md5 ( $repwd, 1 ) : NULL;
	}
	
	/**
	 *
	 * @return the $username
	 */
	public function getUsername() {
		return $this->username;
	}
	
	/**
	 *
	 * @return the $realname
	 */
	public function getRealname() {
		return $this->realname;
	}
	
	/**
	 *
	 * @return the $has_invoice_search_permission
	 */
	public function getHas_invoice_search_permission() {
		return $this->has_invoice_search_permission;
	}
	public function __construct($ismobile = FALSE, $token = NULL) {
		parent::__construct ();
		
		if ($ismobile) {
			if (! empty ( $token )) {
				
				$this->token = $token;
				$mobile_login = $this->db->get_row ( 'SELECT uid,time FROM mobile_login WHERE token="' . $token . '"' );
				if ($mobile_login !== NULL) {
					$last_login_time = $mobile_login->time;
					if (time () - $last_login_time <= 3600) {
						$uid = intval ( $mobile_login->uid );
					}
				}
			}
		} else {
			$usersession = Session_Util::my_session_get ( 'user' );
			if ($usersession !== NULL) {
				$usersession = json_decode ( $usersession );
				$uid = intval ( $usersession->uid );
			}
		}
		
		if (self::validate_id ( $uid )) {
			$this->uid = $uid;
			
			// 获得用户相应权限
			$row = $this->db->get_row ( 'SELECT username,realname,city,dep,team,permissions FROM users WHERE uid=' . $this->uid );
			if ($row !== NULL) {
				$this->username = $row->username;
				$this->realname = $row->realname;
				$this->belong_city = $row->city;
				$this->belong_dep = $row->dep;
				$this->belong_team = $row->team;
				$permissions = $row->permissions;
				
				if (in_array ( $this->getUsername (), $GLOBALS ['check_contract_permission'], TRUE )) {
					$this->has_check_contract_permission = TRUE;
				}
				
				if ($permissions !== '') {
					$this->permissions = explode ( '^', $permissions );
					if (in_array ( 'sys44', $this->permissions, TRUE ) && ! $this->has_check_contract_permission) {
						$this->has_check_contract_permission = TRUE;
					}
					$this->has_manager_contract_permission = in_array ( 'sys44', $this->permissions, TRUE ) ? TRUE : FALSE;
					
					// 开票查询权限
					$per = Permission::getInstance ();
					$per = $per [7]; // 财务权限
					$invoice_array = array ();
					$receivables_array = array ();
					foreach ( $per as $p ) {
						$permission_name = $p ['permission_name'];
						if (strpos ( $permission_name, '开票查询' ) !== FALSE) {
							if (in_array ( self::_add_sys ( $p ['permission_id'] ), $this->permissions, TRUE )) {
								$invoice_array [$p ['permission_id']] = $permission_name;
							}
						} else if (strpos ( $permission_name, '收款查询' ) !== FALSE) {
							if (in_array ( self::_add_sys ( $p ['permission_id'] ), $this->permissions, TRUE )) {
								$receivables_array [$p ['permission_id']] = $permission_name;
							}
						}
					}
					
					if (! empty ( $invoice_array )) {
						$this->has_invoice_search_permission = TRUE;
						$this->invoice_array = $invoice_array;
					}
					
					if (! empty ( $receivables_array )) {
						$this->has_receivables_search_permission = TRUE;
						$this->receivables_array = $receivables_array;
					}
				}
				
				$this->has_check_executive_permission = in_array ( $this->username, $GLOBALS ['executive_manager_array'], TRUE ) ? TRUE : FALSE;
				$this->has_manager_executive_permission = in_array ( $this->username, $GLOBALS ['executive_manager_array'], TRUE ) || ( int ) ($this->belong_dep) === 2 ? TRUE : FALSE;
				$this->_get_pending_items_count ();
				
				$this->has_manager_customer_safety_permission = in_array ( $this->username, $GLOBALS ['manager_setup_customer_safety_permission'], TRUE ) ? TRUE : FALSE;
				
				// $this->has_supplier_apply_audit_tab = in_array(
				// $this->username,
				// $GLOBALS['supplier_apply_check_permission'], TRUE) ? TRUE
				// : FALSE;
				
				// 所属公司代码
				switch (intval ( $this->belong_city )) {
					case 1 :
						$this->city_show = 'SH';
						break;
					case 2 :
						$this->city_show = 'BJ';
						break;
					case 3 :
						$this->city_show = 'GZ';
						break;
					default :
						$this->city_show = 'SH';
				}
				
				// 获得媒体付款申请流程相关信息
				$process = Process::getInstance ();
				$finance_processes = $process ['module'] [7];
				// 根据流程名找到流程
				$process_id = 0;
				foreach ( $finance_processes as $finance_process ) {
					if ($finance_process ['name'] === '媒体付款申请流程') {
						$process_id = intval ( $finance_process ['id'] );
						break;
					}
				}
				if ($process_id > 0) {
					$this->payment_media_step_process = $process ['step'] [$process_id];
				}
				
				/*
				 * //获得付款申请提醒
				 * //1.判断今天是星期几
				 * $w = intval(date('w',time()));
				 * if($w>=1 && $w<=4){
				 * //2.如果今天是周1～周4，检查+1 day的时间
				 * $need_date = array(date('Y-m-d',strtotime('+1 day')));
				 * }else{
				 * //周5需要查看周6，周日，下周1的时间
				 * $need_date = array(date('Y-m-d',strtotime('+1 day')),date('Y-m-d',strtotime('+2 day')),date('Y-m-d',strtotime('+3 day')));
				 * }
				 *
				 * if(in_array($this->getUsername(),$GLOBALS['manager_finance_permission'], TRUE)|| intval($this->getBelong_dep()) === 2){
				 * //财务部查看所有
				 * $sql = 'SELECT media_info_id,payment_date,payment_amount_real,\'pp\' AS ptype FROM finance_payment_person_apply WHERE isok=1 AND payment_date IN ("' . implode('","', $need_date) . '")
				 * UNION ALL
				 * SELECT media_info_id,payment_date,payment_amount_real,\'pd\' AS ptype FROM finance_payment_person_deposit_apply WHERE isok=1 AND payment_date IN ("'. implode('","', $need_date) .'")
				 * UNION ALL
				 * SELECT media_info_id,payment_date,payment_amount_real,\'mp\' AS ptype FROM finance_payment_media_apply WHERE isok=1 AND payment_date IN ("'. implode('","', $need_date) .'")
				 * UNION ALL
				 * SELECT media_info_id,payment_date,payment_amount_real,\'m的\' AS ptype FROM finance_payment_media_deposit_apply WHERE isok=1 AND payment_date IN ("'. implode('","', $need_date) .'")';
				 * }else{
				 * //发起者查看自己的
				 * $sql = 'SELECT media_info_id,payment_date,payment_amount_real,\'pp\' AS ptype FROM finance_payment_person_apply WHERE user=' . $this->uid . ' AND isok=1 AND payment_date IN ("' . implode('","', $need_date) . '")
				 * UNION ALL
				 * SELECT media_info_id,payment_date,payment_amount_real,\'pd\' AS ptype FROM finance_payment_person_deposit_apply WHERE user=' . $this->uid . ' AND isok=1 AND payment_date IN ("' . implode('","', $need_date) . '")
				 * UNION ALL
				 * SELECT a.media_info_id,a.payment_date,a.payment_amount_real,\'mp\' AS ptype FROM finance_payment_media_apply a LEFT JOIN finance_payment_media_apply_user b
				 * ON a.id=b.payment_media_apply_id WHERE b.userid=' . $this->uid . ' AND b.isok=1 AND a.payment_date IN ("' . implode('","', $need_date) . '") AND a.isok=1
				 * UNION ALL
				 * SELECT a.media_info_id,a.payment_date,a.payment_amount_real,\'md\' AS ptype FROM finance_payment_media_deposit_apply a LEFT JOIN finance_payment_media_deposit_apply_user b
				 * ON a.id=b.payment_media_apply_id WHERE b.userid=' . $this->uid . ' AND b.isok=1 AND a.payment_date IN ("' . implode('","', $need_date) . '") AND a.isok=1';
				 * }
				 * $sql = 'SELECT m.*,n.media_name FROM(' . $sql . ') m LEFT JOIN finance_payment_media_info n ON m.media_info_id=n.id';
				 * $this->payment_apply_array = $this->db->get_results($sql);
				 */
			} else {
				$this->uid = NULL;
			}
		}
	}
	private static function _add_sys($value) {
		return 'sys' . $value;
	}
	private static function _del_sys($value) {
		return str_replace ( 'sys', '', $value );
	}
	public static function _get_dep_tf_id($dep_role) {
		if (! empty ( $dep_role )) {
			foreach ( $dep_role as $key => $value ) {
				if ($value ['permission_name'] === 'tf') {
					return 'dep' . $value ['permission_id'];
					break;
				}
			}
			return 'sysdepoamiads';
		}
		return 'sysdepoamiads';
	}
	private function _get_pending_items_count() {
		$all = 0;
		if ($this->getUid () !== NULL) {
			
			$process = Process::getInstance ();
			$dep_process = Dep_Process::getInstance ();
			$dep = Dep::getInstance ();
			$dep_role = Permission_Dep::getInstance ();
			
			$_process = $process ['step'];
			
			// 执行单
			$executives = $this->db->get_results ( 'SELECT a.*,FROM_UNIXTIME(a.time) AS tt,b.cusname FROM executive a , contract_cus b WHERE a.cid = b.cid AND a.isok=0 ORDER BY time DESC' );
			
			if ($executives !== NULL) {
				foreach ( $executives as $executive ) {
					$pcid = $executive->pcid;
					$step = $executive->step;
					$pmcode = $_process [$pcid] [$step] ['content'] [2];
					$__pid = $executive->pid;
					
					if ($pmcode === EXECUTIVE_MODULE) { // 权限为执行单发起人，特殊处理
						if (intval ( $executive->user ) === intval ( $this->getUid () )) {
							$all ++;
						}
					} else if ($pmcode === EXECUTIVE_IN_CHARGE) { // 执行单项目负责人
						if (intval ( $executive->principal ) === intval ( $this->getUid () )) {
							$all ++;
						}
					} else if ($pmcode === DEP_SUPPORT) { // 部门支持
						$support = $executive->support;
						if (! empty ( $support )) {
							$support = explode ( '|', $support );
							
							foreach ( $support as $s ) {
								$s = explode ( '^', $s );
								$row = $this->db->get_row ( 'SELECT pcid,step FROM executive_dep WHERE id=' . intval ( $s [1] ) . ' AND isok=0' );
								
								if ($row !== NULL) {
									$_pcid = intval ( $row->pcid );
									$_step = intval ( $row->step );
									
									if ($_pcid !== 0) {
										$status = $dep [$s [0]] [0] . '|' . $dep_process [$_pcid] [$_step] [0];
									} else {
										$status = $dep [$s [0]] [0];
									}
									
									if ($_step === 0 && in_array ( self::_get_dep_tf_id ( $dep_role [$s [0]] ), $this->getPermissions (), TRUE ) || in_array ( $dep_process [$_pcid] [$_step] [2], $this->getPermissions (), TRUE )) {
										$all ++;
									}
								}
							}
						}
					} else if (in_array ( $pmcode, $this->getPermissions () )) { // 有权限
						$all ++;
					}
				}
			}
			
			// 合同
			$contracts = $this->db->get_results ( 'SELECT *,FROM_UNIXTIME(time) AS tt FROM contract_cus WHERE isok=0 ORDER BY time DESC' );
			
			if ($contracts !== NULL) {
				foreach ( $contracts as $contract ) {
					$pcid = $contract->pcid;
					$step = $contract->step;
					$pmcode = $_process [$pcid] [$step] ['content'] [2];
					
					if (in_array ( $pmcode, $this->getPermissions () )) { // 有权限
						$all ++;
					}
				}
			}
			
			// 如果是部门leader，则显示部门提出的开票申请，因为可能身兼多职，所以每个都要判断，而不是只判断自己部门
			$deps = array ();
			$teams = Team::getInstance ();
			
			$dep_team = $teams ['dep'];
			foreach ( $dep_role as $dep_id => $dep_r ) {
				foreach ( $dep_r as $role ) {
					if (in_array ( 'dep' . $role ['permission_id'], $this->permissions, TRUE ) && strpos ( $role ['permission_name'], 'leader' ) !== FALSE) {
						if ($dep_team [$dep_id] === NULL) {
							$deps [] = array (
									'id' => $dep_id,
									'team' => NULL 
							);
						} else {
							$v = $dep_team [$dep_id];
							$pn = String_Util::cut_str ( $role ['permission_name'], 1, 0, 'UTF-8' );
							foreach ( $v as $vv ) {
								$tn = String_Util::cut_str ( $teams ['team'] [$vv] ['teamname'], 1, 2, 'UTF-8' );
								if ($tn === $pn) {
									$deps [] = array (
											'id' => $dep_id,
											'team' => $vv 
									);
								}
							}
						}
					}
				}
			}
			
			if (! empty ( $deps )) {
				// 开票相关
				$this->has_invoice_tab = TRUE;
				$query = array ();
				foreach ( $deps as $depid ) {
					$q = 'SELECT a.*,FROM_UNIXTIME(a.time) AS tt,b.username,b.realname FROM finance_invoice_list a LEFT JOIN users b ON a.user=b.uid WHERE a.isok=0 AND a.step=1 AND  b.dep=' . intval ( $depid ['id'] );
					if ($depid ['team'] !== NULL) {
						$q .= ' AND b.team=' . intval ( $depid ['team'] );
					}
					$query [] = $q;
				}
				$query = implode ( ' UNION ', $query );
				$query .= ' ORDER BY time DESC';
				$this->invoices = $this->db->get_results ( $query );
				$all += count ( $this->invoices );
				
				// 保证金
				/*
				 * $this->has_deposit_tab = TRUE;
				 * $deposit_query = array();
				 * foreach ($deps as $depid) {
				 * $dq = 'SELECT a.id,a.cid,a.cusname,a.amount,a.addtime,b.username,b.realname,1 AS deposit_type FROM finance_deposit a LEFT JOIN users b ON a.adduser=b.uid WHERE a.isok=0 AND a.step=1 AND b.dep='
				 * . intval($depid['id']);
				 * if ($depid['team'] !== NULL) {
				 * $dq .= ' AND b.team=' . intval($depid['team']);
				 * }
				 * $deposit_query[] = $dq;
				 * }
				 * $deposit_query = implode(' UNION ', $deposit_query);
				 * $deposit_query .= ' ORDER BY a.addtime DESC';
				 * $this->deposits = $this->db->get_results($deposit_query);
				 * $all += count($this->deposits);
				 *
				 * //保证金开票
				 * $deposit_invoice_query = array();
				 * foreach ($deps as $depid) {
				 * $q = 'SELECT a.*,FROM_UNIXTIME(a.time) AS tt,b.username,b.realname,2 AS deposit_type FROM finance_deposit_invoice_list a LEFT JOIN users b ON a.user=b.uid WHERE a.isok=0 AND a.step=1 AND b.dep='
				 * . intval($depid['id']);
				 * if ($depid['team'] !== NULL) {
				 * $q .= ' AND b.team=' . intval($depid['team']);
				 * }
				 * $deposit_invoice_query[] = $q;
				 * }
				 * $deposit_invoice_query = implode(' UNION ',
				 * $deposit_invoice_query);
				 * $deposit_invoice_query .= ' ORDER BY a.time DESC';
				 * $this->deposit_invoices = $this->db
				 * ->get_results($deposit_invoice_query);
				 * $all += count($this->deposit_invoices);
				 */
			}
			
			// 审核供应商申请
			if (in_array ( $this->username, $GLOBALS ['supplier_apply_check_permission'], TRUE )) {
				$this->has_supplier_apply_audit_tab = TRUE;
				$this->suplier_apply = $this->db->get_results ( 'SELECT a.id,a.supplier_name,a.url,a.deduction,a.in_invoice_tax_rate,a.supplier_type,a.addtime,a.step,b.username,b.realname FROM new_supplier_apply a LEFT JOIN users b ON a.apply_userid=b.uid WHERE a.isok=0' );
				$all += count ( $this->suplier_apply );
			}
			
			/*
			 * //部门leader付款申请相关
			 * if (!empty($deps)) {
			 * $this->has_payment_person_audit_tab = TRUE;
			 * } else {
			 * $permission = Permission::getInstance();
			 * $permission = $permission[7];
			 * foreach ($permission as $val) {
			 * if ($val['permission_name'] === '部门leader审核'
			 * && in_array('sys' . $val['permission_id'],
			 * $this->permissions, TRUE)) {
			 * $this->has_payment_person_audit_tab = TRUE;
			 * break;
			 * }
			 * }
			 * }
			 * if ($this->has_payment_person_audit_tab) {
			 * //leader的审核步骤
			 * $leader_step = 0;
			 *
			 * //获得媒体付款申请流程相关信息
			 * $finance_processes = $process['module'][7];
			 * //根据流程名找到流程
			 * $process_id = 0;
			 * foreach ($finance_processes as $finance_process) {
			 * if ($finance_process['name'] === '个人付款申请流程') {
			 * $process_id = intval($finance_process['id']);
			 * break;
			 * }
			 * }
			 * if ($process_id > 0) {
			 * $step_process = $process['step'][$process_id];
			 * }
			 * foreach ($step_process as $stepkey => $sp) {
			 * if ($sp['content'][0] === '部门leader审核') {
			 * $leader_step = intval($stepkey);
			 * break;
			 * }
			 * }
			 *
			 * if ($leader_step > 0) {
			 * $this->contract_payment_person = $this->db
			 * ->get_results(
			 * 'SELECT a.id,a.payment_id,a.addtime,a.payment_date,a.payment_amount_plan,a.payment_amount_real,c.media_name
			 * FROM finance_payment_person_apply a
			 * LEFT JOIN users b
			 * ON a.user=b.uid
			 * LEFT JOIN finance_payment_media_info c
			 * ON a.media_info_id=c.id
			 * WHERE a.step=' . ($leader_step - 1) . ' AND a.isok=0 AND b.city='
			 * . $this->belong_city
			 * . ' AND b.dep=' . $this->belong_dep
			 * . ' AND b.team='
			 * . $this->belong_team
			 * . ' ORDER BY addtime');
			 * $all += count($this->contract_payment_person);
			 * }
			 * }
			 */
			
			/*
			 * //执行单外包审核
			 * $outsourcing_audits = array();
			 * $outsourcings = $this->db
			 * ->get_results(
			 * 'SELECT a.executive_id,a.pid,a.outsourcing_type_id,a.step,a.support_id,b.process_id,c.outsourcing_process_name,c.process
			 * FROM outsourcing_pid_type a
			 * LEFT JOIN outsourcing_type_process b
			 * ON a.outsourcing_type_id=b.type_id
			 * LEFT JOIN outsourcing_process c
			 * ON b.process_id=c.id
			 * WHERE a.isok=0 AND c.isok=1');
			 *
			 * if ($outsourcings !== NULL) {
			 * foreach ($outsourcings as $outsourcing) {
			 * $op = json_decode($outsourcing->process);
			 * if ($op[$outsourcing->step]
			 * === ($this->getRealname() . ' ('
			 * . $this->getUsername() . ')')) {
			 * $outsourcing_audits[] = array(
			 * 'executive_id' => $outsourcing->executive_id,
			 * 'pid' => $outsourcing->pid,
			 * 'outsourcing_type_id' => $outsourcing
			 * ->outsourcing_type_id,
			 * 'support_id' => $outsourcing->support_id);
			 * }
			 * }
			 * }
			 * if (!empty($outsourcing_audits)) {
			 * $this->has_outsourcing_audit_tab = TRUE;
			 * $this->outsourcing_audits = $outsourcing_audits;
			 * $all += count($outsourcing_audits);
			 * unset($outsourcing_audits);
			 * }
			 */
		}
		$this->all_pending_item_count = $all;
	}
	private function _get_left_data() {
		$result = array ();
		if ($this->uid !== NULL) {
			$permissions = $this->permissions;
			// 执行单
			$executive = $GLOBALS ['user_left_executive'];
			if (! in_array ( $this->username, $GLOBALS ['executive_manager_array'] ) && ( int ) ($this->belong_dep) !== 2) {
				$executive = Array_Util::my_remove_array_other_value ( $executive, array (
						'执行单管理' 
				) );
			}
			$result ['executive'] = $executive;
			
			// 合同
			$contact = $GLOBALS ['user_left_contract'];
			if (Array_Util::my_remove_array_other_value ( $permissions, $GLOBALS ['add_contract_permission'] ) === $permissions) {
				$contact = Array_Util::my_remove_array_other_value ( $contact, array (
						'新建客户合同' 
				) );
			}
			if (Array_Util::my_remove_array_other_value ( $permissions, $GLOBALS ['manager_contract_permission'] ) === $permissions) {
				$contact = Array_Util::my_remove_array_other_value ( $contact, array (
						'客户合同管理' 
				) );
			}
			$result ['contact'] = $contact;
			
			// 财务
			$finance = $GLOBALS ['user_left_finance'];
			if (! in_array ( $this->username, $GLOBALS ['manager_finance_tj_permission'] )) {
				$finance = Array_Util::my_remove_array_other_value ( $finance, array (
						'统计分析' 
				) );
			}
			if (! in_array ( $this->username, $GLOBALS ['manager_finance_permission'] ) && intval ( $this->belong_dep ) !== 2) {
				$finance = Array_Util::my_remove_array_other_value ( $finance, array (
						'收票管理',
						'收款管理',
						'开票管理',
						'供应商管理',
						'财务大表',
						'保证金管理',
						'修改转移',
						'垫付管理',
						'财务信息查询',
						'收付对冲',
						'退款管理',
						'银行信息管理',
						'媒体付款归档',
						'付款申请管理',
						'返点管理',
						'结帐日期设置',
						'媒体简称设置',
						'返点比例设置' 
				) );
			}
			if (! $this->getHas_receivables_search_permission ()) {
				$finance = Array_Util::my_remove_array_other_value ( $finance, array (
						'收款查询' 
				) );
			}
			
			// 媒体付款相关 start==============================
			$has_payment_media_apply = FALSE;
			$has_payment_media_manager = FALSE;
			if (! empty ( $this->payment_media_step_process )) {
				$pmsp = $this->payment_media_step_process;
				foreach ( $pmsp as $pro ) {
					$pro = $pro ['content'];
					if ($pro [0] === '发起人' && in_array ( $pro [2], $this->getPermissions (), TRUE )) {
						$has_payment_media_apply = ! $has_payment_media_apply ? TRUE : $has_payment_media_apply;
					}
					
					if (! in_array ( $pro [0], array (
							'员工填写' 
					), TRUE ) && in_array ( $pro [2], $this->getPermissions (), TRUE )) {
						$has_payment_media_manager = ! $has_payment_media_manager ? TRUE : $has_payment_media_manager;
					}
				}
			}
			
			if (! $has_payment_media_apply) {
				$finance = Array_Util::my_remove_array_other_value ( $finance, array (
						'媒体付款申请' 
				) );
			}
			
			if (! $has_payment_media_manager) {
				$finance = Array_Util::my_remove_array_other_value ( $finance, array (
						'媒体付款管理' 
				) );
			}
			// 媒体付款相关 end==============================
			
			// 付款管理
			// if (!in_array($this->username,
			// $GLOBALS['finance_payment_check_permission'])) {
			// $finance = Array_Util::my_remove_array_other_value($finance,
			// array('付款管理'));
			// }
			$result ['finance'] = $finance;
			
			// 媒体数据
			$media_data = $GLOBALS ['user_left_media_data'];
			if (! in_array ( $this->username, $GLOBALS ['manager_media_data_permission'] ) && intval ( $this->belong_dep ) !== 4) {
				$media_data = Array_Util::my_remove_array_other_value ( $media_data, array (
						'排期上传测试',
						'媒体库管理' 
				) );
			}
			$result ['media_data'] = $media_data;
			
			// 个人信息
			$result ['own'] = $GLOBALS ['user_left_own'];
			
			// 系统参数
			$setup = array ();
			if (in_array ( $this->username, $GLOBALS ['manager_setup_permission'], TRUE )) {
				$setup = $GLOBALS ['user_left_setup'];
			} else if (in_array ( $this->username, $GLOBALS ['manager_setup_customer_safety_permission'], TRUE )) {
				$setup = array (
						array_search ( '系统客户编辑', $GLOBALS ['user_left_setup'] ) => '系统客户编辑' 
				);
			}
			if (! empty ( $setup )) {
				$result ['setup'] = $setup;
			}
			
			// 会议室预定
			$result ['booking'] = $GLOBALS ['user_left_booking'];
			
			// 技术部项目记录
			if (intval ( $this->belong_dep ) === 6) {
				$result ['tec'] = $GLOBALS ['user_left_tect_project'];
			}
			
			// 外包流程管理
			if (in_array ( $this->username, $GLOBALS ['manager_outsourcing_process_permission'], TRUE ) || $this->has_outsourcing_audit_tab) {
				$result ['outsourcing'] = $GLOBALS ['outsourcing_process'];
			}
		}
		
		return $result;
	}
	private static function _get_link($links) {
		$link = BASE_URL;
		for($i = 0, $count = count ( $links ); $i < $count; $i ++) {
			if ($i !== ($count - 1) || $count === 1) {
				$link .= $links [$i] . '/';
			} else {
				$link .= '?o=' . $links [$i];
			}
		}
		return $link;
	}
	public function get_left_html() {
		$left = $this->_get_left_data ();
		$left_str = '';
		if (! empty ( $left )) {
			$step = 0;
			foreach ( $left as $key => $value ) {
				if (! empty ( $value )) {
					switch ($key) {
						case 'executive' :
							$left_str .= '<script>var menu_e = ' . $step . ';</script><h2>执行单管理</h2>';
							break;
						case 'contact' :
							$left_str .= '<script>var menu_c = ' . $step . ';</script><h2>合同管理</h2>';
							break;
						case 'finance' :
							$left_str .= '<script>var menu_f = ' . $step . ';</script><h2>财务管理 (测试)</h2>';
							break;
/*						case 'media_data' :
							$left_str .= '<script>var menu_m = ' . $step . ';</script><h2>媒体数据管理 (测试)</h2>';
							break;*/
						case 'own' :
							$left_str .= '<script>var menu_o = ' . $step . ';</script><h2>个人信息管理</h2>';
							break;
						case 'setup' :
							$left_str .= '<script>var menu_s = ' . $step . ';</script><h2>系统设置</h2>';
							break;
						/*
						case 'booking' :
							$left_str .= '<script>var menu_b = ' . $step . ';</script><h2>会议室预定</h2>';
							break;
						case 'tec' :
							$left_str .= '<script>var menu_t = ' . $step . ';</script><h2>技术部项目管理</h2>';
							break;
						case 'outsourcing' :
							$left_str .= '<script>var menu_out = ' . $step . ';</script><h2>外包流程管理</h2>';
							break;*/
					}
					
					$count = 0;
					$number = count ( $value );
					foreach ( $value as $action => $name ) {
						if ($count === 0) {
							$left_str .= '<ul>';
						}
						$left_str .= '<li><a href="' . self::_get_link ( explode ( '^', $action ) ) . '">' . $name . '</a></li>';
						if ($count === ($number - 1)) {
							$left_str .= '</ul>';
						}
						$count ++;
					}
				}
				$step ++;
			}
		}
		/*			    

		<div class="logo"><img src="[BASE_URL]images/logo.png" /></div>
		<div id="copyright">
							<h6>上海网迈广告有限公司 Copyright &copy; 2004-2011 nimads.All Rights Reserved </h6>
						</div>*/
		return <<<EOF
			<div id="side">
				
				<div class="SYS_Wdate" id="SYS_Wdate"></div>
				<div id="side_nav">
					$left_str
				</div>
			</div>
EOF;
	}
	public function get_depname($local, $depart) {
		return sprintf ( '%s%s', $local, $depart );
	}
	private function _get_top_data() {
		if ($this->uid !== NULL) {
			$dep = Dep::getInstance ();
			$team = Team::getInstance ();
			$my_dep = $dep [$this->belong_dep];
			$my_team = $team ['team'] [$this->belong_team];
			if ($my_dep === NULL) {
				$my_dep = '';
			} else {
				$my_dep = $this->get_depname ( $my_dep [1], $my_dep [0] );
			}
			
			if ($my_team !== NULL) {
				$my_team = $my_team ['teamname'];
			}
			return sprintf ( '%s %s %s %s', $this->username, $this->realname, $my_dep, $my_team );
		}
		return '';
	}
	public function get_top_html() {
		$top = $this->_get_top_data ();
		$buf = file_get_contents ( TEMPLATE_PATH . 'top.tpl' );
		$search = array (
				'[USERINFO]',
				'[ALLCOUNT]' 
		);
		$replace = array (
				$top,
				$this->all_pending_item_count 
		);
		return str_replace ( $search, $replace, $buf );
	}
	private function validate_form_value($action) {
		$errors = array ();
		if ($action === 'changepwd') {
			if (! self::validate_field_not_empty ( $this->pwd ) || ! self::validate_field_not_null ( $this->pwd )) {
				$errors [] = '新密码不能为空';
			}
			
			if (! self::validate_field_not_empty ( $this->repwd ) || ! self::validate_field_not_null ( $this->repwd )) {
				$errors [] = '重复新密码不能为空';
			}
			
			if ($this->pwd !== $this->repwd) {
				$errors [] = '新密码的两次输入必须一致';
			}
			
			if (! self::validate_field_not_empty ( $this->oldpwd ) || ! self::validate_field_not_null ( $this->oldpwd )) {
				$errors [] = '原密码不能为空';
			} else {
				$row = $this->db->get_row ( 'SELECT username FROM users WHERE uid=' . intval ( $this->uid ) . ' AND password="' . $this->oldpwd . '"' );
				if ($row === NULL) {
					$errors [] = '原密码输入有误';
				}
			}
		} else {
			$errors [] = '无权限操作';
		}
		
		if (empty ( $errors )) {
			return TRUE;
		}
		$this->errors = $errors;
		unset ( $errors );
		return FALSE;
	}
	public function user_do($action) {
		if ($this->validate_form_value ( $action )) {
			// 通过验证
			$query = Sql_Util::get_update ( 'users', array (
					'password' => $this->pwd 
			), array (
					'uid' => array (
							'=',
							$this->uid 
					) 
			), 'AND' );
			if ($query ['status'] === 'success') {
				$update_result = $this->db->query ( $query ['sql'] );
				if ($update_result === FALSE || $update_result === 0) {
					// 更新失败
					return array (
							'status' => 'error',
							'message' => '修改密码出错' 
					);
				}
				return array (
						'status' => 'success',
						'message' => '修改密码成功' 
				);
			} else {
				// SQL错误
				return array (
						'status' => 'error',
						'message' => '系统内部错误' 
				);
			}
		}
		return array (
				'status' => 'error',
				'message' => $this->errors 
		);
	}
	public function get_user_city_info($cityid = NULL, $depid = NULL, $teamid = NULL) {
		$result = array ();
		$city = City::getInstance ();
		$dep = Dep::getInstance ();
		$team = Team::getInstance ();
		
		if ($cityid !== NULL) {
			if ($city [$cityid] !== NULL) {
				$result [] = $city [$cityid];
			}
		} else if ($this->belong_city !== NULL) {
			if ($city [$this->belong_city] !== NULL) {
				$result [] = $city [$this->belong_city];
			}
		}
		
		if ($depid !== NULL) {
			if ($dep [$depid] !== NULL) {
				$result [] = $dep [$depid] [0];
			}
		} else if ($this->belong_dep !== NULL) {
			if ($dep [$this->belong_dep] !== NULL) {
				$result [] = $dep [$this->belong_dep] [0];
			}
		}
		
		if ($teamid !== NULL) {
			if ($team ['team'] [$teamid] !== NULL) {
				$result [] = $team ['team'] [$teamid] ['teamname'];
			}
		} else if ($this->belong_team !== NULL) {
			if ($team ['team'] [$this->belong_team] !== NULL) {
				$result [] = $team ['team'] [$this->belong_team] ['teamname'];
			}
		}
		return implode ( ' / ', $result );
	}
	public function get_user_mates() {
		if (intval ( $this->belong_team ) !== 0) {
			$mates = $this->db->get_results ( 'SELECT uid,realname,username FROM users WHERE team=' . intval ( $this->belong_team ) . ' AND islive=1' );
		} else if (intval ( $this->belong_dep ) !== 0) {
			$mates = $this->db->get_results ( 'SELECT uid,realname,username FROM users WHERE dep=' . intval ( $this->belong_dep ) . ' AND islive=1' );
		} else {
			$mates = array ();
		}
		
		if (! empty ( $mates )) {
			$result = array ();
			foreach ( $mates as $mate ) {
				$result [] = array (
						'user_id' => $mate->uid,
						'realname' => $mate->realname,
						'username' => $mate->username 
				);
			}
			return $result;
		} else {
			return $mates;
		}
	}
	public function get_user_mates_select_html($uid = 0) {
		$result = '<option value="">请选择人员</option>';
		$mates = $this->get_user_mates ();
		if (! empty ( $mates )) {
			foreach ( $mates as $mate ) {
				$result .= '<option value="' . $mate ['user_id'] . '" ' . (intval ( $mate ['user_id'] ) === $uid && $uid !== 0 ? 'selected="selected"' : '') . '>' . $mate ['realname'] . '(' . $mate ['username'] . ')</option>';
			}
		}
		return $result;
	}
	public function get_vcode() {
		if ($this->uid !== NULL) {
			return crumb::issueCrumb ( $this->uid . self::SALT_VALUE );
		}
		return NULL;
	}
	public function get_token() {
		if ($this->uid !== NULL) {
			$token = String_Util::my_md5 ( microtime ( TRUE ) . '|' . $this->uid . '|' . self::SALT_VALUE );
			Session_Util::my_session_set ( 'my_token', $token );
			return $token;
		}
		return NULL;
	}
	public function get_relation_executive_permission($city, $dep, $team) {
		if ($this->uid !== NULL) {
			if ($city !== 0) {
				if (in_array ( 'sys114', $this->getPermissions () ) && $city === 1 || in_array ( 'sys115', $this->getPermissions () ) && $city === 2 || in_array ( 'sys116', $this->getPermissions () ) && $city === 3) {
					return 1;
				}
				
				if ($dep !== 0 && $team === 0) {
					if (in_array ( 'sys117', $this->getPermissions () ) && $dep === 3 || in_array ( 'sys118', $this->getPermissions () ) && $dep === 4 || in_array ( 'sys119', $this->getPermissions () ) && $dep === 5 || in_array ( 'sys120', $this->getPermissions () ) && $dep === 6 || in_array ( 'sys121', $this->getPermissions () ) && $dep === 7 || in_array ( 'sys122', $this->getPermissions () ) && $dep === 8 || in_array ( 'sys123', $this->getPermissions () ) && $dep === 9 || in_array ( 'sys124', $this->getPermissions () ) && $dep === 10 || in_array ( 'sys125', $this->getPermissions () ) && $dep === 11 || in_array ( 'sys126', $this->getPermissions () ) && $dep === 12 || in_array ( 'sys127', $this->getPermissions () ) && $dep === 13 || in_array ( 'sys128', $this->getPermissions () ) && $dep === 14 || in_array ( 'sys129', $this->getPermissions () ) && $dep === 15 || in_array ( 'sys130', $this->getPermissions () ) && $dep === 16 || in_array ( 'sys131', $this->getPermissions () ) && $dep === 17 || in_array ( 'sys132', $this->getPermissions () ) && $dep === 18 || in_array ( 'sys133', $this->getPermissions () ) && $dep === 19 || in_array ( 'sys134', $this->getPermissions () ) && $dep === 20) {
						return 2;
					}
				} else if ($city !== 0 && $dep !== 0 && $team !== 0) {
					if (in_array ( 'sys135', $this->getPermissions () ) && $team === 3 || in_array ( 'sys136', $this->getPermissions () ) && $team === 6 || in_array ( 'sys137', $this->getPermissions () ) && $team === 7 || in_array ( 'sys166', $this->getPermissions () ) && $team === 8) {
						return 3;
					}
				}
			}
		}
		return 0;
	}
	public function get_relation_contract_permission($city, $dep, $team) {
		if ($this->uid !== NULL) {
			if ($city !== 0) {
				if (in_array ( 'sys142', $this->permissions ) && $city === 1 || in_array ( 'sys143', $this->permissions ) && $city === 2 || in_array ( 'sys144', $this->permissions ) && $city === 3) {
					return TRUE;
				}
				
				if ($dep !== 0 && $team === 0) {
					if (in_array ( 'sys145', $this->permissions ) && $dep === 3 || in_array ( 'sys146', $this->permissions ) && $dep === 4 || in_array ( 'sys147', $this->permissions ) && $dep === 5 || in_array ( 'sys148', $this->permissions ) && $dep === 6 || in_array ( 'sys149', $this->permissions ) && $dep === 7 || in_array ( 'sys150', $this->permissions ) && $dep === 8 || in_array ( 'sys151', $this->permissions ) && $dep === 9 || in_array ( 'sys152', $this->permissions ) && $dep === 10 || in_array ( 'sys153', $this->permissions ) && $dep === 11 || in_array ( 'sys154', $this->permissions ) && $dep === 12 || in_array ( 'sys155', $this->permissions ) && $dep === 13 || in_array ( 'sys156', $this->permissions ) && $dep === 14 || in_array ( 'sys157', $this->permissions ) && $dep === 15 || in_array ( 'sys158', $this->permissions ) && $dep === 16 || in_array ( 'sys159', $this->permissions ) && $dep === 17 || in_array ( 'sys160', $this->permissions ) && $dep === 18 || in_array ( 'sys161', $this->permissions ) && $dep === 19 || in_array ( 'sys162', $this->permissions ) && $dep === 20) {
						return TRUE;
					}
				} else if ($city !== 0 && $dep !== 0 && $team !== 0) {
					if (in_array ( 'sys163', $this->permissions ) && $team === 3 || in_array ( 'sys164', $this->permissions ) && $team === 6 || in_array ( 'sys165', $this->permissions ) && $team === 7 || in_array ( 'sys167', $this->permissions ) && $team === 8) {
						return TRUE;
					}
				}
			}
		}
		return FALSE;
	}
	public function get_zjl_contract_permission($city, $dep) {
		if ($this->uid !== NULL) {
			if (in_array ( 'dep111', $this->permissions ) && $city === 2 || in_array ( 'dep112', $this->permissions ) && $city === 3) {
				return TRUE;
			} else if (in_array ( 'dep55', $this->permissions ) && $dep === 4 || in_array ( 'dep113', $this->permissions ) && $dep === 3 || in_array ( 'dep61', $this->permissions ) && $dep === 8) {
				return TRUE;
			}
		}
		return FALSE;
	}
	private function _get_executive_permission() {
		if ($this->uid !== NULL) {
			if (in_array ( $this->username, $GLOBALS ['executive_manager_array'] )) {
				$this->has_check_executive_permission = TRUE;
				$this->has_manager_executive_permission = TRUE;
			} else {
				$city = intval ( $this->belong_city );
				$dep = intval ( $this->belong_dep );
				$team = intval ( $this->belong_team );
				if ($city !== 0 && $dep === 0 && $team === 0) {
					if (in_array ( 'sys114', $this->permissions ) && $city === 1 || in_array ( 'sys115', $this->permissions ) && $city === 2 || in_array ( 'sys116', $this->permissions ) && $city === 3) {
						$this->has_check_executive_permission = TRUE;
					}
				} else if ($city !== 0 && $dep !== 0 && $team === 0) {
					if (in_array ( 'sys117', $this->permissions ) && $dep === 3 || in_array ( 'sys118', $this->permissions ) && $dep === 4 || in_array ( 'sys119', $this->permissions ) && $dep === 5 || in_array ( 'sys120', $this->permissions ) && $dep === 6 || in_array ( 'sys121', $this->permissions ) && $dep === 7 || in_array ( 'sys122', $this->permissions ) && $dep === 8 || in_array ( 'sys123', $this->permissions ) && $dep === 9 || in_array ( 'sys124', $this->permissions ) && $dep === 10 || in_array ( 'sys125', $this->permissions ) && $dep === 11 || in_array ( 'sys126', $this->permissions ) && $dep === 12 || in_array ( 'sys127', $this->permissions ) && $dep === 13 || in_array ( 'sys128', $this->permissions ) && $dep === 14 || in_array ( 'sys129', $this->permissions ) && $dep === 15 || in_array ( 'sys130', $this->permissions ) && $dep === 16 || in_array ( 'sys131', $this->permissions ) && $dep === 17 || in_array ( 'sys132', $this->permissions ) && $dep === 18 || in_array ( 'sys133', $this->permissions ) && $dep === 19 || in_array ( 'sys134', $this->permissions ) && $dep === 20) {
						$this->has_check_executive_permission = TRUE;
					}
				} else if ($city !== 0 && $dep !== 0 && $team !== 0) {
					if (in_array ( 'sys135', $this->permissions ) && $team === 3 || in_array ( 'sys136', $this->permissions ) && $team === 6 || in_array ( 'sys137', $this->permissions ) && $team === 7 || in_array ( 'sys166', $this->permissions ) && $team === 8) {
						$this->has_check_executive_permission = TRUE;
					}
				}
			}
		}
	}
	private function _get_contract_permission() {
		if ($this->uid !== NULL) {
			if (in_array ( 'sys44', $this->permissions )) {
				$this->has_check_contract_permission = TRUE;
				$this->has_manager_contract_permission = TRUE;
			} else {
				$city = intval ( $this->belong_city );
				$dep = intval ( $this->belong_dep );
				$team = intval ( $this->belong_team );
				if ($city !== 0 && $dep === 0 && $team === 0) {
					if (in_array ( 'sys142', $this->permissions ) && $city === 1 || in_array ( 'sys143', $this->permissions ) && $city === 2 || in_array ( 'sys144', $this->permissions ) && $city === 3) {
						$this->has_check_contract_permission = TRUE;
					}
					
					if (in_array ( 'dep111', $this->permissions ) && $city === 2 || in_array ( 'dep112', $this->permissions ) && $city === 3) {
						$this->has_manager_contract_permission = TRUE;
					}
				} else if ($city !== 0 && $dep !== 0 && $team === 0) {
					if (in_array ( 'sys145', $this->permissions ) && $dep === 3 || in_array ( 'sys146', $this->permissions ) && $dep === 4 || in_array ( 'sys147', $this->permissions ) && $dep === 5 || in_array ( 'sys148', $this->permissions ) && $dep === 6 || in_array ( 'sys149', $this->permissions ) && $dep === 7 || in_array ( 'sys150', $this->permissions ) && $dep === 8 || in_array ( 'sys151', $this->permissions ) && $dep === 9 || in_array ( 'sys152', $this->permissions ) && $dep === 10 || in_array ( 'sys153', $this->permissions ) && $dep === 11 || in_array ( 'sys154', $this->permissions ) && $dep === 12 || in_array ( 'sys155', $this->permissions ) && $dep === 13 || in_array ( 'sys156', $this->permissions ) && $dep === 14 || in_array ( 'sys157', $this->permissions ) && $dep === 15 || in_array ( 'sys158', $this->permissions ) && $dep === 16 || in_array ( 'sys159', $this->permissions ) && $dep === 17 || in_array ( 'sys160', $this->permissions ) && $dep === 18 || in_array ( 'sys161', $this->permissions ) && $dep === 19 || in_array ( 'sys162', $this->permissions ) && $dep === 20) {
						$this->has_check_contract_permission = TRUE;
					}
					
					if (in_array ( 'dep55', $this->permissions ) && $dep === 4 || in_array ( 'dep113', $this->permissions ) && $dep === 3 || in_array ( 'dep61', $this->permissions ) && $dep === 8) {
						$this->has_manager_contract_permission = TRUE;
					}
				} else if ($city !== 0 && $dep !== 0 && $team !== 0) {
					if (in_array ( 'sys163', $this->permissions ) && $team === 3 || in_array ( 'sys164', $this->permissions ) && $team === 6 || in_array ( 'sys165', $this->permissions ) && $team === 7 || in_array ( 'sys167', $this->permissions ) && $team === 8) {
						$this->has_check_contract_permission = TRUE;
					}
				}
			}
		}
	}
	public static function get_remind_days($createtime, $oktime) {
		if ($oktime != 0) {
			$time = $oktime - $createtime;
		} else {
			$time = time () - $createtime;
		}
		$t = intval ( $time / 3600 / 24 );
		return '<font color="' . ($t > 7 ? 'red' : 'green') . '">' . $t . '天</font>';
	}
	public function get_upload_files($dids, $can_edit = FALSE, $attacheid = 'dids', $ismobile = FALSE) {
		$s = $ismobile ? array () : '';
		if (! empty ( $dids )) {
			$dids = explode ( '^', $dids );
			$dids = Array_Util::my_remove_array_other_value ( $dids, array (
					NULL,
					'' 
			) );
			if (! empty ( $dids )) {
				$results = $this->db->get_results ( 'SELECT id,realname,size FROM uploadfile WHERE id IN (' . implode ( ',', $dids ) . ')' );
				foreach ( $results as $result ) {
					if ($ismobile) {
						$s [] = array (
								'filename' => $result->realname,
								'file_link' => BASE_URL . 'download.php?did=' . $result->id 
						);
					} else {
						$s .= '<div><a href="' . BASE_URL . 'download.php?did=' . intval ( $result->id ) . '" target="_blank">' . $result->realname . '</a>';
						if ($can_edit) {
							$s .= ' &nbsp;(' . $result->size . ')&nbsp;<img src="' . BASE_URL . 'images/close.png" onclick="up_del(this,' . intval ( $result->id ) . ',\'' . $attacheid . '\')"/>';
						}
						$s .= '</div>';
					}
				}
			}
			
			/*
			 * foreach ($dids as $did) {
			 * if (!empty($did)) {
			 * $row = $this->db
			 * ->get_row(
			 * 'SELECT realname,size FROM uploadfile WHERE id='
			 * . intval($did));
			 * if (!empty($row)) {
			 * $s .= '<div><a href="' . BASE_URL . 'download.php?did='
			 * . intval($did) . '" target="_blank">'
			 * . $row->realname . '</a>';
			 * if ($can_edit) {
			 * $s .= ' &nbsp;(' . $row->size . ')&nbsp;<img src="'
			 * . BASE_URL
			 * . 'images/close.png" onclick="up_del(this,'
			 * . intval($did) . ',\'' . $attacheid
			 * . '\')"/>';
			 * }
			 * $s .= '</div>';
			 * }
			 * }
			 * }
			 */
		}
		return $s;
	}
	public static function no_permission($message = NULL) {
		header ( 'Content-type: text/html; charset=utf-8' );
		$back_url = $_SERVER ['HTTP_REFERER'];
		if (empty ( $back_url )) {
			$back_url = BASE_URL;
		}
		Js_Util::my_js_alert ( ($message === NULL ? NO_RIGHT_TO_DO_THIS : $message), $back_url );
		exit ();
	}
	public static function no_object($msg, $url = NULL) {
		header ( 'Content-type: text/html; charset=utf-8' );
		if ($url !== NULL && String_Util::start_with ( $url, BASE_URL )) {
			$back_url = $url;
		} else {
			$back_url = $_SERVER ['HTTP_REFERER'];
			if (empty ( $back_url )) {
				$back_url = BASE_URL;
			}
		}
		Js_Util::my_js_alert ( $msg, $back_url );
		exit ();
	}
	public static function get_support_array($support) {
		$s = array ();
		
		if (empty ( $support )) {
			return $s;
		}
		
		$_tmps = explode ( '|', $support );
		foreach ( $_tmps as $_tmp ) {
			$_tmp = explode ( '^', $_tmp );
			$s [$_tmp [0]] = $_tmp [1];
		}
		
		return $s;
	}
	public static function combine_array($array) {
		$s = '';
		$result = array ();
		foreach ( $array as $obj ) {
			$result [] = implode ( '^', $obj );
		}
		$s = implode ( '|', $result );
		return $s;
	}
	public static function no_done() {
		header ( 'Content-type: text/html; charset=utf-8' );
		$back_url = $_SERVER ['HTTP_REFERER'];
		if (empty ( $back_url )) {
			$back_url = BASE_URL;
		}
		Js_Util::my_js_alert ( '该项目暂不可用', $back_url );
		exit ();
	}
	public function get_invoice_tab() {
		if ($this->has_invoice_search_permission) {
			return '<li><a href="?o=insearch">开票信息查询</a></li>';
		}
		return '';
	}
	public function get_change_pwd_html() {
		$buf = file_get_contents ( TEMPLATE_PATH . 'hr/changepwd.tpl' );
		return str_replace ( array (
				'[LEFT]',
				'[TOP]',
				'[USERNAME]',
				'[VCODE]',
				'[BASE_URL]' 
		), array (
				$this->get_left_html (),
				$this->get_top_html (),
				sprintf ( '%s', $this->username ),
				$this->get_vcode (),
				BASE_URL 
		), $buf );
	}
	public function getHas_receivables_search_permission() {
		return $this->has_receivables_search_permission;
	}
	public function getReceivables_array() {
		return $this->receivables_array;
	}
	public function getHas_manager_customer_safety_permission() {
		return $this->has_manager_customer_safety_permission;
	}
	public function getHas_deposit_tab() {
		return $this->has_deposit_tab;
	}
	public function getDeposits() {
		return $this->deposits;
	}
	public function getDeposit_invoices() {
		return $this->deposit_invoices;
	}
	protected function get_sequence($typename) {
		if ($typename === NULL || ! is_string ( $typename )) {
			return FALSE;
		}
		$seq = $this->db->get_row ( 'SELECT seq FROM sequence WHERE typename="' . $typename . '" FOR UPDATE' );
		$now_seq = 1;
		if ($seq !== NULL) {
			$now_seq = ($seq->seq) + 1;
			$result = $this->db->query ( 'UPDATE sequence SET seq=' . $now_seq . ' WHERE typename="' . $typename . '"' );
		} else {
			$result = $this->db->query ( 'INSERT INTO sequence(typename,seq) VALUE("' . $typename . '",' . $now_seq . ')' );
		}
		return $result === FALSE ? FALSE : $now_seq;
	}
	public function getHas_supplier_apply_audit_tab() {
		return $this->has_supplier_apply_audit_tab;
	}
	public function getSuplier_apply() {
		return $this->suplier_apply;
	}
	protected function getSequence($typename) {
		$row = $this->db->get_row ( 'SELECT seq FROM sequence WHERE typename="' . $typename . '" FOR UPDATE' );
		if ($row !== NULL) {
			$nowid = intval ( $row->seq );
			$result = $this->db->query ( 'UPDATE sequence SET seq=' . ($nowid + 1) . ' WHERE typename="' . $typename . '"' );
			if ($result === FALSE) {
				return FALSE;
			} else {
				return sprintf ( $typename . '%04d', ($nowid + 1) );
			}
		} else {
			$result = $this->db->query ( 'INSERT INTO sequence(typename,seq) VALUE("' . $typename . '",1)' );
			if ($result === FALSE) {
				return FALSE;
			} else {
				return $typename . '0001';
			}
		}
	}
	public function getCity_show() {
		return $this->city_show;
	}
}
