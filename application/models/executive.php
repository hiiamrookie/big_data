<?php
class Executive extends User {
	private $executive_id;
	private $exetype;
	private $cid;
	private $execompany;
	private $projectname;
	private $dids;
	private $principal;
	private $actor;
	private $starttime;
	private $endtime;
	private $remark;
	private $process;
	private $pid;
	private $contrast;
	private $dep;
	private $user;
	private $gd = FALSE;
	private $audit_pass;
	private $rejectstep;
	private $rejectdepids = array ();
	private $support_array = array ();
	private $paycount_array = array ();
	private $costcount_array = array ();
	private $costpaycount_array = array ();
	private $servercf_array = array ();
	private $errors = array ();
	private $altersupport_array = array ();
	private $executive_records = array ();
	private $cy_json;
	private $is2agent;
	private $agentcusname;
	private $customertype;
	private $outsourcing_type;
	private $cy_amount_array = array ();
	public function getDep() {
		return $this->dep;
	}
	
	/**
	 *
	 * @param field_type $gd        	
	 */
	public function setGd($gd) {
		$this->gd = $gd;
	}
	
	/**
	 *
	 * @return the $executive_id
	 */
	public function getExecutive_id() {
		return $this->executive_id;
	}
	
	/**
	 *
	 * @return the $pid
	 */
	public function getPid() {
		return $this->pid;
	}
	
	/**
	 *
	 * @return the $contrast
	 */
	public function getContrast() {
		return $this->contrast;
	}
	public function __construct($executive_id = NULL, $fields = array(), $ismobile = FALSE, $token = NULL) {
		parent::__construct ( $ismobile, $token );
		if ($executive_id !== NULL) {
			
			if (! is_int ( $executive_id )) {
				$executive_id = intval ( $executive_id );
			}
			if (self::validate_id ( $executive_id )) {
				$this->executive_id = $executive_id;
			}
		} else if (! empty ( $fields )) {
			foreach ( $this as $key => $value ) {
				if ($fields [$key] !== NULL && ! in_array ( $key, array (
						'gd' 
				), TRUE )) {
					$this->$key = $fields [$key];
				}
			}
		}
	}
	private function _getMobileAmountCY($id, $pid) {
		$s = array ();
		$results = $this->db->get_results ( 'SELECT ym,quote_amount FROM executive_amount_cy WHERE executive_id=' . $id . ' AND pid="' . $pid . '"' );
		if ($results !== NULL) {
			foreach ( $results as $result ) {
				$s [] = array (
						'ym' => $result->ym,
						'amount' => Format_Util::my_money_format ( '%.2n', $result->quote_amount ) 
				);
			}
		}
		return $s;
	}
	private function _getMobileSupportCostCY($id, $supports) {
		$s = array ();
		$supports = explode ( '|', $supports );
		foreach ( $supports as $support ) {
			if (! empty ( $support )) {
				$support = explode ( '^', $support );
				$s [] = $this->_getMobileCostCY ( $id, $support [0] );
			}
		}
		return $s;
	}
	private function _getMobileCostCY($id, $dep = 0) {
		$s = array ();
		$cs = array ();
		$results = $this->db->get_results ( 'SELECT b.supplier_name,c.category_name,d.media_short,a.*,e.industry_name
FROM
(
SELECT id,ym,cost_amount,quote_amount,finance_cost_amount,finance_quote_amount,supplier_id,supplier_short_id,category_id,industry_id FROM executive_cy WHERE executive_id=' . intval ( $id ) . (intval ( $dep ) > 0 ? ' AND is_support=1 AND support_dep=' . intval ( $dep ) : ' AND is_support=0 AND support_dep=0') . ') a
LEFT JOIN new_supplier b
ON a.supplier_id=b.id
LEFT JOIN new_supplier_category c
ON a.category_id=c.id
LEFT JOIN finance_supplier_short d
ON a.supplier_short_id=d.id
LEFT JOIN new_supplier_industry e
ON a.industry_id=e.id AND a.supplier_short_id=e.supplier_short_id
ORDER BY b.supplier_name,c.category_name,a.ym' );
		if ($results !== NULL) {
			$tmp = array ();
			foreach ( $results as $result ) {
				$k = $result->supplier_id . '_' . $result->supplier_short_id . '_' . $result->category_id . '_' . $result->industry_id;
				$tmp ['cy'] [$k] [] = array (
						'ym' => $result->ym,
						'cost' => Format_Util::my_money_format ( '%.2n', $result->cost_amount ) 
				);
				if (empty ( $tmp ['name'] [$k] )) {
					$tmp ['name'] [$k] = array (
							'supplier_name' => urlencode ( $result->supplier_name ),
							'media_short' => empty ( $result->media_short ) ? '' : urlencode ( $result->media_short ),
							'category_name' => empty ( $result->category_name ) ? '' : urlencode ( $result->category_name ),
							'industry_name' => empty ( $result->industry_name ) ? '' : urlencode ( $result->industry_name ) 
					);
				}
			}
			
			foreach ( $tmp ['name'] as $key => $value ) {
				$cs [] = array (
						'supplier_name' => $value ['supplier_name'],
						'media_short' => $value ['media_short'],
						'category_name' => $value ['category_name'],
						'industry_name' => $value ['industry_name'],
						'cy' => $tmp ['cy'] [$key] 
				);
			}
			
			$depname = '';
			if (intval ( $dep ) !== 0) {
				$depname = $this->db->get_var ( 'SELECT depname FROM hr_department WHERE id=' . intval ( $dep ) );
				if (empty ( $depname )) {
					$depname = '';
				}
			}
			$s = array (
					'dep' => intval ( $dep ),
					'dep_name' => urlencode ( $depname ),
					'dep_cost_cy' => $cs 
			);
		}
		return $s;
	}
	public function getMobileAuditExecutive() {
		$success = TRUE;
		$error = '';
		$support_audit = FALSE;
		$only_reject = FALSE;
		$row = $this->db->get_row ( 'SELECT a.step,a.pcid,b.content,a.isalter,a.principal FROM executive a,process b WHERE a.id=' . intval ( $this->executive_id ) . ' AND a.pcid=b.id AND a.isok<>-1 AND b.islive=1' );
		
		if ($row === NULL) {
			$success = FALSE;
			$error = '没有该执行单或状态不可审核';
		} else {
			$content = explode ( '_', $row->content );
			$content = explode ( '^', $content [$row->step] );
			
			if ($content [2] === 'DEP') {
				// 支持部门审核
				/*
				 * $dep_role = Permission_Dep::getInstance();
				 * $hasright = FALSE;
				 * $dephasroles = $dep_role[intval($this->dep)];
				 * foreach ($dephasroles as $dephasrole) {
				 * if (in_array('dep' . $dephasrole['permission_id'],
				 * $this->getPermissions(), TRUE)) {
				 * $hasright = TRUE;
				 * break;
				 * }
				 * }
				 */
				$hasright = $this->_get_dep_audit ( $this->executive_id, $this->dep );
				if (! $hasright) {
					$success = FALSE;
					$error = NO_RIGHT_TO_DO_THIS;
				}
				$support_audit = TRUE;
			} else {
				// 非支持部门审核
				if (! in_array ( $content [2], $this->getPermissions (), TRUE )) {
					if ($content [0] === '项目负责人') {
						if (intval ( $this->getUid () ) !== intval ( $row->principal )) {
							$success = FALSE;
							$error = NO_RIGHT_TO_DO_THIS;
						}
					} else {
						$success = FALSE;
						$error = NO_RIGHT_TO_DO_THIS;
					}
				}
			}
		}
		
		$info = array ();
		if ($success) {
			$this->_get_executive_records ();
			$datas = $this->executive_records;
			
			$dep = Dep::getInstance ();
			$process = Process::getInstance ();
			$process_dep = Dep_Process::getInstance ();
			
			if (! empty ( $datas )) {
				$info ['id'] = $datas ['new']->id;
				$info ['pid'] = $datas ['new']->pid;
				$info ['project_name'] = urlencode ( $datas ['new']->name );
				$info ['create_time'] = $datas ['new']->tt;
				$info ['executive_type'] = urlencode ( self::_get_executive_typename ( intval ( $datas ['new']->type ) ) );
				$info ['belong_dep'] = urlencode ( $this->get_user_city_info ( $datas ['new']->city, $datas ['new']->dep, $datas ['new']->team ) );
				$info ['belong_contract'] = urlencode ( self::_get_contract_url ( $datas ['new']->cid, $datas ['new']->contractname, TRUE ) );
				$info ['cusname'] = urlencode ( $datas ['new']->cusname );
				$info ['company'] = urlencode ( self::get_companyname ( $datas ['new']->company ) );
				$info ['principal'] = urlencode ( self::_get_user ( $datas ['new']->realname, $datas ['new']->username ) );
				$info ['actor'] = urlencode ( $datas ['new']->actor );
				$info ['executive_time'] = self::_get_executive_time ( intval ( $datas ['new']->isalter ), $datas ['new']->time, $datas ['new']->starttime, $datas ['new']->endtime, TRUE );
				$info ['allamount'] = Format_Util::my_money_format ( '%.2n', $datas ['new']->amount );
				$info ['allcost'] = Format_Util::my_money_format ( '%.2n', $datas ['new']->allcost );
				$info ['user'] = urlencode ( self::_get_user ( $datas ['new']->urealname, $datas ['new']->uusername ) );
				$info ['support'] = $this->_get_support_dep ( $dep, $datas ['new']->support, TRUE );
				$info ['process_list'] = self::_get_process_content ( intval ( $datas ['new']->pcid ), intval ( $datas ['new']->isok ), intval ( $datas ['new']->step ), 0, $process, $process_dep, TRUE );
				$info ['paytime_info'] = $this->_get_paytimeinfo ( $datas ['new']->paytimeinfoids, $datas ['new']->cid, $datas ['new']->time, TRUE );
				$info ['cost_info'] = $this->_get_costinfo ( $datas ['new']->costinfoids, TRUE );
				$info ['costpayment_info'] = $this->_get_costpaymentinfo ( $datas ['new']->costpaymentinfoids, TRUE );
				$info ['remark'] = urlencode ( Format_Util::format_html ( $datas ['new']->remark ) );
				$info ['dids'] = $this->get_upload_files ( $datas ['new']->dids, FALSE, 'dids', TRUE );
				
				// 执行单金额拆月
				$info ['amount_cy'] = $this->_getMobileAmountCY ( $datas ['new']->id, $datas ['new']->pid );
				
				// 执行单发起部门的成本拆月
				$info ['base_cost_cy'] = $this->_getMobileCostCY ( $datas ['new']->id );
				
				// 支持部门信息
				$depinfo_new = $this->_get_dep_info ( $datas ['new']->support );
				$new_deps = array ();
				foreach ( $depinfo_new as $depnew ) {
					$new_deps [intval ( $depnew ['id'] )] = $depnew ['row'];
				}
				$new_deps_id = array_keys ( $new_deps );
				
				$sups = array ();
				$reject_dep = array (
						array (
								'reject_depid' => 0,
								'reject_depname' => urlencode ( '发起人' ) 
						) 
				);
				foreach ( $new_deps_id as $new_dep ) {
					$sups [] = array (
							'support_name' => urlencode ( $this->get_depname ( $dep [$new_dep] [1], $dep [$new_dep] [0] ) ),
							'support_actor' => urlencode ( $new_deps [$new_dep]->actor ),
							'support_remark' => urlencode ( Format_Util::format_html ( $new_deps [$new_dep]->remark ) ),
							'support_cost_info' => $this->_get_costinfo ( $new_deps [$new_dep]->costinfoids, TRUE ),
							'support_costpayment_info' => $this->_get_costpaymentinfo ( $new_deps [$new_dep]->costpaymentinfoids, TRUE ),
							'support_process_list' => $this->_get_process_content ( intval ( $new_deps [$new_dep]->pcid ), intval ( $new_deps [$new_dep]->isok ), intval ( $new_deps [$new_dep]->step ), intval ( $new_dep ), $process, $process_dep, TRUE ) 
					);
				}
				
				$info ['support_info'] = $sups;
				
				// 支持部门成本拆月
				$info ['support_cost_cy'] = $this->_getMobileSupportCostCY ( $datas ['new']->id, $datas ['new']->support );
				
				// 可驳回的步骤
				$process_content = $process ['step'] [$datas ['new']->pcid];
				$DEP_step = Process::get_DEP_key ( $process_content );
				if ($DEP_step !== NULL && $DEP_step < intval ( $datas ['new']->step ) && ! empty ( $datas ['new']->support )) {
					// 可驳回到支持部门
					foreach ( $new_deps_id as $new_dep ) {
						$reject_dep [] = array (
								'reject_depid' => $new_dep,
								'reject_depname' => urlencode ( $this->get_depname ( $dep [$new_dep] [1], $dep [$new_dep] [0] ) ) 
						);
					}
				}
				$info ['reject_deps'] = $reject_dep;
				
				// 如果是支持部门审核，检查是不是tf，如果是tf，只可以驳回
				if ($support_audit) {
					$depinfoid = 0;
					foreach ( explode ( '|', $datas ['new']->support ) as $sin ) {
						$sin = explode ( '^', $sin );
						if (intval ( $this->dep ) === intval ( $sin [0] )) {
							$depinfoid = intval ( $sin [1] );
							break;
						}
					}
					if ($depinfoid !== 0) {
						$deprow = $this->db->get_row ( 'SELECT * FROM executive_dep WHERE id=' . $depinfoid );
						if (intval ( $deprow->step ) === 0) {
							// tf
							$only_reject = TRUE;
						}
					}
				}
				$info ['only_reject'] = intval ( $only_reject );
			} else {
				$success = FALSE;
				$error = '没有该执行单或状态不可审核';
			}
		}
		$result = array (
				'status' => $success ? 'success' : 'error',
				'message' => urlencode ( ($success ? '获取成功' : $error) ),
				'token' => $this->getToken (),
				'pid' => $this->getPid (),
				'id' => $this->getExecutive_id (),
				'dep' => $this->getDep () 
		);
		if (! empty ( $info )) {
			$result ['info'] = $info;
		}
		return urldecode ( json_encode ( $result ) );
	}
	public function get_support_dep($has_selected = array(), $belong_dep = NULL) {
		return Dep::get_support_dep_checkbox_html ( FALSE, $has_selected, $belong_dep );
	}
	public function get_process_list($dep = NULL, $pcid = NULL) {
		$process = Process::getInstance ();
		$exe_processes = $process ['module'] [1]; // 执行单流程
		$step_process = $process ['step']; // 流程步骤
		$result = '';
		$i = 0;
		$use_dep = $dep === NULL ? $this->getBelong_dep () : $dep;
		foreach ( $exe_processes as $exe_processe ) {
			$deps = explode ( '^', $exe_processe ['deps'] );
			if (in_array ( $use_dep, $deps )) {
				$content = '';
				$tmp = $step_process [$exe_processe ['id']];
				foreach ( $tmp as $key => $t ) {
					$content .= ($key !== 0 ? ' -> ' : '') . $t ['content'] [0];
				}
				$result .= '<li><input type="radio" name="process" value="' . $exe_processe ['id'] . '" class="checkbox" ' . ($i === 0 && $pcid === NULL || $pcid !== NULL && intval ( $pcid ) === intval ( $exe_processe ['id'] ) ? 'checked="checked"' : '') . '><span style="display:none">' . $content . '</span><label>' . $exe_processe ['name'] . '</label></li>';
				
				$i ++;
			}
		}
		return $result;
	}
	private function validate_form_value($action) {
		$errors = array ();
		if (in_array ( $action, array (
				'add',
				'update',
				'gd',
				'audit',
				'dep_edit',
				'dep_audit',
				'alter',
				'userchange',
				'complete_cy',
				'userchange_all' 
		) )) {
			if (in_array ( $action, array (
					'gd',
					'userchange',
					'userchange_all' 
			), TRUE ) && ! in_array ( $this->getUsername (), $GLOBALS ['executive_manager_array'], TRUE )) {
				$errors [] = '无权限操作';
			} else {
				if ($action === 'gd') {
					$executive_id = intval ( $this->executive_id );
					if (! self::validate_id ( $executive_id )) {
						$errors [] = '执行单选择有误';
					}
				} else if ($action === 'audit' || $action === 'dep_audit') {
					$audit_pass = intval ( $this->audit_pass );
					if (! in_array ( $audit_pass, array (
							0,
							1 
					) )) {
						$errors [] = '执行单审核操作有误';
					}
					
					if ($audit_pass === 0 && (! self::validate_field_not_empty ( $this->remark ) || ! self::validate_field_not_null ( $this->remark ))) {
						$errors [] = '执行单审核驳回的理由不能为空';
					}
					
					if (! self::validate_field_not_empty ( $this->pid ) || ! self::validate_field_not_null ( $this->pid )) {
						$errors [] = '执行单号不能为空';
					}
				} else if ($action === 'complete_cy') {
					$costpaycount_array = $this->costpaycount_array;
					$costpay_sum = 0;
					foreach ( $costpaycount_array as $key => $costpaycount ) {
						if (! self::validate_date ( $costpaycount ['time'] )) {
							$errors [] = '第' . ($key + 1) . '条【成本支付明细】记录的时间值非有效时间 ';
						}
						if (! self::validate_money ( $costpaycount ['amount'] )) {
							$errors [] = '第' . ($key + 1) . '条【成本支付明细】记录的金额值非有效金额 ';
						} else {
							$costpay_sum += $costpaycount ['amount'];
						}
						if (! in_array ( intval ( $costpaycount ['type'] ), array (
								1,
								2 
						), TRUE )) {
							$errors [] = '第' . ($key + 1) . '条【成本支付明细】记录的收票类型值非有效值 ';
						}
					}
					
					if (! self::validate_field_not_empty ( $this->pid ) || ! self::validate_field_not_null ( $this->pid )) {
						$errors [] = '执行单号不能为空';
					}
					
					$executive_id = intval ( $this->executive_id );
					if (! self::validate_id ( $executive_id )) {
						$errors [] = '执行单选择有误';
					}
					
					// TODO 计算总量
				} else {
					if ($action !== 'dep_edit' && $action !== 'userchange' && $action !== 'userchange_all') {
						if (! in_array ( intval ( $this->exetype ), array (
								1,
								2,
								3 
						), TRUE )) {
							$errors [] = '执行单类型选择有误';
						}
						
						if ($action !== 'alter' && $action !== 'update') {
							if (! self::validate_field_not_empty ( $this->cid ) || ! self::validate_field_not_null ( $this->cid )) {
								$errors [] = '所属合同不能为空';
							} else if (strpos ( $this->cid, '-' ) === FALSE || strpos ( $this->cid, '-' ) >= 50) {
								$errors [] = '合同号有误';
							}
						}
						
						if (! self::validate_field_not_empty ( $this->projectname ) || ! self::validate_field_not_null ( $this->projectname )) {
							$errors [] = '执行单名称不能为空';
						} else if (! self::validate_field_max_length ( $this->projectname, 200 )) {
							$errors [] = '执行单名称长度最多200个字符';
						}
						
						if (intval ( $this->is2agent ) === 1) {
							// 选中2代
							if (empty ( $this->agentcusname )) {
								$errors [] = '代理客户名称不能为空';
							} else if (! self::validate_field_max_length ( $this->agentcusname, 255 )) {
								$errors [] = '代理客户名称长度最多255个字符';
							}
							
							if (! empty ( $this->customertype ) && ! self::validate_id ( intval ( $this->customertype ) )) {
								$errors [] = '客户名行业分类选择有误';
							}
						}
						
						if (! in_array ( intval ( $this->execompany ), array (
								1,
								3 
						), TRUE )) {
							$errors [] = '所属公司选择有误';
						}
						
						if (! self::validate_id ( intval ( $this->principal ) )) {
							$errors [] = '项目负责人选择有误';
						}
						
						if (! self::validate_field_not_empty ( $this->starttime ) || ! self::validate_field_not_null ( $this->starttime )) {
							$errors [] = '项目开始时间不能为空';
						} else if (strtotime ( $this->starttime ) === FALSE) {
							$errors [] = '项目开始时间不是一个有效的时间值';
						}
						
						if (! self::validate_field_not_empty ( $this->endtime ) || ! self::validate_field_not_null ( $this->endtime )) {
							$errors [] = '项目结束时间不能为空';
						} else if (strtotime ( $this->endtime ) === FALSE) {
							$errors [] = '项目结束时间不是一个有效的时间值';
						} else if (strtotime ( $this->endtime ) - strtotime ( $this->starttime ) < 0) {
							$errors [] = '项目开始时间必须早于结束时间';
						}
						
						if (self::validate_field_not_empty ( $this->support_array )) {
							$supports = $this->support_array;
							foreach ( $supports as $support ) {
								if (! self::validate_id ( intval ( $support ) )) {
									$errors [] = '支持部门选择有误';
									break;
								} else if ($action === 'add' && intval ( $support ) === 7) {
									$errors [] = '新建执行单时不再可选“社会化营销中心”作为支持部门';
									break;
								}
							}
						}
						
						$paycount_array = $this->paycount_array;
						$paycount_sum = 0;
						foreach ( $paycount_array as $key => $paycount ) {
							if (! self::validate_date ( $paycount ['time'] )) {
								$errors [] = '第' . ($key + 1) . '条【合同约定付款时间】记录的时间值非有效时间 ';
							}
							if (! self::validate_money ( $paycount ['amount'] )) {
								$errors [] = '第' . ($key + 1) . '条【合同约定付款时间】记录的金额值非有效金额 ';
							} else {
								$paycount_sum += $paycount ['amount'];
							}
						}
					}
					
					if ($action !== 'userchange' && $action !== 'userchange_all') {
						if ($action !== 'dep_edit') {
							if (! self::validate_field_not_empty ( $this->dids ) || ! self::validate_field_not_null ( $this->dids ) || $this->dids === '^') {
								$errors [] = '执行单附件不能为空';
							} else if (! String_Util::start_with ( $this->dids, '^' ) || ! String_Util::end_with ( $this->dids, '^' )) {
								$errors [] = '执行单附件有误';
							} else if (! self::validate_field_max_length ( $this->dids, 500 )) {
								$errors [] = '执行单附件选择过多';
							} else {
								$dids = $this->dids;
								$this->dids = substr ( $dids, 1, strlen ( $dids ) - 2 );
							}
						} else {
							if (self::validate_field_not_empty ( $this->dids ) && self::validate_field_not_null ( $this->dids ) && $this->dids !== '^') {
								if (! String_Util::start_with ( $this->dids, '^' ) || ! String_Util::end_with ( $this->dids, '^' )) {
									$errors [] = '执行单附件有误';
								} else if (! self::validate_field_max_length ( $this->dids, 500 )) {
									$errors [] = '执行单附件选择过多';
								} else {
									$dids = $this->dids;
									$this->dids = substr ( $dids, 1, strlen ( $dids ) - 2 );
								}
							} else {
								$this->dids = '';
							}
						}
						
						if (! self::validate_field_not_empty ( $this->actor ) || ! self::validate_field_not_null ( $this->actor )) {
							$errors [] = '项目执行人不能为空';
						} else if (! self::validate_field_max_length ( $this->actor, 500 )) {
							$errors [] = '项目执行人选择过多';
						}
						
						if (self::validate_field_not_empty ( $this->remark ) && ! self::validate_field_max_length ( $this->remark, 1000 )) {
							$errors [] = '备注长度最多1000个字符';
						}
						
						if (! self::validate_field_not_empty ( $this->process ) || ! self::validate_field_not_null ( $this->process )) {
							$errors [] = '流程选择不能为空';
						} else if (! self::validate_id ( intval ( $this->process ) )) {
							$errors [] = '流程选择有误';
						}
						
						$servercf_array = $this->servercf_array;
						//var_dump($servercf_array);
						foreach ( $servercf_array as $key => $servercf ) {
							if (! in_array ( intval ( $servercf ['type'] ), array_keys ( $GLOBALS ['defined_servicecf_type'] ), TRUE ) && !in_array(intval($servercf['type']), array_keys($GLOBALS ['defined_ggcf_type']),TRUE)) {
								$errors [] = '第' . ($key + 1) . '条【税前、税费金额拆分】记录的类型值非有效类型 ';
							}
							if (! self::validate_money ( $servercf ['amount'] )) {
								$errors [] = '第' . ($key + 1) . '条【税前、税费金额拆分】记录的金额值非有效金额 ';
							}
						}
						
						$costcount_array = $this->costcount_array;
						$costcount_sum = 0;
						foreach ( $costcount_array as $key => $costcount ) {
							if (empty ( $costcount ['name'] )) {
								$errors [] = '第' . ($key + 1) . '条【成本明细】记录的收款方全称不能为空';
							}
							if (! in_array ( intval ( $costcount ['type'] ), array_keys ( $GLOBALS ['defined_executive_cost_type'] ), TRUE )) {
								$errors [] = '第' . ($key + 1) . '条【成本明细】记录的类型值非有效类型 ';
							}
							if (! self::validate_money ( $costcount ['amount'] )) {
								$errors [] = '第' . ($key + 1) . '条【成本明细】记录的金额值非有效金额 ';
							} else {
								$costcount_sum += $costcount ['amount'];
							}
							if (! in_array ( intval ( $costcount ['yg'] ), array (
									0,
									1 
							), TRUE )) {
								$errors [] = '第' . ($key + 1) . '条【成本明细】记录的是否预估值非有效值 ';
							}
						}
						
						$costpaycount_array = $this->costpaycount_array;
						$costpay_sum = 0;
						$supplier_amount = array ();
						$n = 0;
						foreach ( $costpaycount_array as $costpaycount ) {
							if (empty ( $costpaycount ['name'] )) {
								$errors [] = '第' . ($n + 1) . '条【成本支付明细】记录的收款方全称不能为空';
							}
							if (! self::validate_date ( $costpaycount ['time'] )) {
								$errors [] = '第' . ($n + 1) . '条【成本支付明细】记录的时间值非有效时间 ';
							}
							if (! self::validate_money ( $costpaycount ['amount'] )) {
								$errors [] = '第' . ($n + 1) . '条【成本支付明细】记录的金额值非有效金额 ';
							} else {
								$costpay_sum += $costpaycount ['amount'];
								if (array_key_exists ( $costpaycount ['name'], $supplier_amount )) {
									$supplier_amount [$costpaycount ['name']] += $costpaycount ['amount'];
								} else {
									$supplier_amount [$costpaycount ['name']] = $costpaycount ['amount'];
								}
							}
							if (! in_array ( intval ( $costpaycount ['type'] ), array (
									1,
									2 
							), TRUE )) {
								$errors [] = '第' . ($n + 1) . '条【成本支付明细】记录的收票类型值非有效值 ';
							}
							$n ++;
						}
						
						if (round ( $costcount_sum, 2 ) !== round ( $costpay_sum, 2 )) {
							$errors [] = '成本明细金额与成本支付明细金额不一致';
						}
					}
					
					if (in_array ( $action, array (
							'dep_edit',
							'alter',
							'userchange' 
					), TRUE )) {
						if (! self::validate_field_not_empty ( $this->pid ) || ! self::validate_field_not_null ( $this->pid )) {
							$errors [] = '执行单号不能为空';
						}
						
						if ($action === 'userchange') {
							if (! self::validate_id ( intval ( $this->principal ) )) {
								$errors [] = '项目负责人选择有误';
							}
							
							if (! self::validate_id ( intval ( $this->user ) )) {
								$errors [] = '项目发起人选择有误';
							}
						}
					}
					
					if ($action === 'userchange_all') {
						if (! self::validate_id ( intval ( $this->principal ) )) {
							$errors [] = '变更人员选择有误';
						}
						
						if (! self::validate_id ( intval ( $this->user ) )) {
							$errors [] = '原来人员选择有误';
						}
						
						if (intval ( $this->principal ) === intval ( $this->user )) {
							$errors [] = '原来人员与变更人员不可相同';
						}
					}
					
					if ($action === 'dep_edit') {
						if (! empty ( $this->outsourcing_type ) && ! self::validate_id ( $this->outsourcing_type )) {
							$errors [] = '执行单外包类新新选择有误';
						}
					}
					
					// 校验拆月数据
					if (in_array ( $action, array (
							'add',
							'update',
							'dep_edit',
							'alter' 
					), TRUE ) && CY_ON) {
						$cy_json = $this->cy_json;
						// var_dump($cy_json);
						
						$cy_json = json_decode ( base64_decode ( $cy_json ) );
						// $all_quote = 0;
						$all_cost = 0;
						$cy_json = ( array ) $cy_json;
						
						foreach ( $cy_json as $cy ) {
							$cy = ( array ) $cy;
							// $cy_items = $cy['items'];
							// foreach ($cy_items as $ccy) {
							// $all_cost += $ccy->cost;
							// }
							$all_cost += $cy ['cost'];
							
							if (! empty ( $supplier_amount )) {
								if (! in_array ( $cy ['supplier'], array_keys ( $supplier_amount ), TRUE )) {
									$errors [] = '拆月中选取的供应商【' . $cy ['supplier'] . '】在成本支付明细中不存在';
								}
							}
						}
						
						if (round ( $costpay_sum, 2 ) !== round ( $all_cost, 2 )) {
							// $errors[] = round($costpay_sum, 2) . '==执行单执行成本拆月数据不正确==' .round($all_cost, 2) ;
							$errors [] = '执行成本拆月数据不正确';
						}
					}
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
	public function add_executive() {
		if ($this->validate_form_value ( 'add' )) {
			// 检验成本拆月是否在执行期内
			$start = date ( 'Y-m', strtotime ( $this->starttime ) );
			$end = date ( 'Y-m', strtotime ( $this->endtime ) );
			$check_cy_json = json_decode ( base64_decode ( $this->cy_json ) );
			$check_cy_json = ( array ) $check_cy_json;
			if (! empty ( $check_cy_json )) {
				foreach ( $check_cy_json as $cy ) {
					$cy = ( array ) $cy;
					$cyitems = $cy ['items'];
					$cyitems = ( array ) $cyitems;
					foreach ( $cyitems as $cyitem ) {
						foreach ( $cyitem as $cyitemem ) {
							if (strtotime ( $cyitemem->date ) < strtotime ( $start ) || strtotime ( $cyitemem->date ) > strtotime ( $end )) {
								return array (
										'status' => 'error',
										'message' => '项目执行日期为' . $start . '至' . $end . '，成本拆月数据必须在该日期范围内' 
								);
							}
						}
					}
				}
			}
			
			$success = TRUE;
			$error = '';
			$this->db->query ( 'BEGIN' );
			// 检查合同号
			$cid = $this->cid;
			$cid = explode ( '-', $cid );
			$row = $this->db->get_row ( 'SELECT billtype FROM contract_cus WHERE cid="' . strtoupper ( $cid [0] ) . '" AND isexecutive=1' );
			if ($row === NULL) {
				$success = FALSE;
				$error = '合同号不存在或者还未通过审核！请咨询合同管理人员';
			} else {
				if (CUSTOMER_SAFETY_ON) {
					$cusrow = $this->db->get_row ( 'SELECT customer_id FROM v_cid_customer WHERE cid="' . strtoupper ( $cid [0] ) . '"' );
					if ($cusrow === NULL) {
						$success = FALSE;
						$error = '该客户暂时未购买保险额度，无法创建执行单，请联系财务部Alex';
					}
				}
				
				if ($success) {
					$paycount_array = $this->paycount_array;
					$paytime_amount = 0;
					
					if (! empty ( $paycount_array )) {
						foreach ( $paycount_array as $paycount ) {
							$paytime_amount += $paycount ['amount'];
						}
					}
					$paytime_amount = round ( $paytime_amount, 2 );
					
					if ($paytime_amount > 0 && CUSTOMER_SAFETY_ON && intval ( $this->execompany ) === 3) { // 只计算新网迈
					                                                                                       // 校验系统客户保险额度
						$cus = new Customer ( array (
								'customer_id' => intval ( $cusrow->customer_id ) 
						) );
						$remainder = $cus->compute_remainder_safety ();
						unset ( $cus );
						
						if ($remainder <= 0) {
							$success = FALSE;
							$error = '该客户保险额度已满，无法创建执行单，请联系财务部Alex';
						} else {
							if ($paytime_amount > $remainder) {
								$success = FALSE;
								$error = '执行单金额大于该客户剩余保险额度，无法创建执行单，请联系财务部Alex';
							}
						}
					}
					
					if ($success) {
						$billtype = $row->billtype;
						$servercfs = $this->servercf_array;
						$servicecf_amount = 0;
						
						if($paytime_amount > 0){
							//2016-07-18 都要拆税费
							if (empty ( $servercfs )) {
								$success = FALSE;
								$error = '税前、税费金额需要拆分';
							} else {
								$rax_rate = 1;
								if(intval ( $billtype ) === 2){
									$rax_rate = 1 + FW_TAX_RATE;
								}else if(intval ( $billtype ) === 1){
									$rax_rate = 1 + GG_TAX_RATE;
								}
								//var_dump($rax_rate);
								foreach ( $servercfs as $servercf ) {
									$servicecf_amount += $servercf ['amount'];
								}
								//var_dump($servicecf_amount);
								//var_dump(round ( $paytime_amount / $rax_rate, 2 ) - $servicecf_amount );
								
								if (round ( abs ( round ( $paytime_amount / $rax_rate, 2 ) - $servicecf_amount ), 2 ) > 0.01) {
									$success = FALSE;
									$error = '合同约定付款金额与服务金额不一致';
								}
							}
						}
						
						/*
						if (intval ( $billtype ) === 2 && $paytime_amount > 0) {
							if (empty ( $servercfs )) {
								$success = FALSE;
								$error = '该合同为服务合同，必须进行付款金额的拆分';
							} else {
								foreach ( $servercfs as $servercf ) {
									$servicecf_amount += $servercf ['amount'];
								}
								
								if (round ( abs ( round ( $paytime_amount / 1.0683, 2 ) - $servicecf_amount ), 2 ) > 0.01) {
									$success = FALSE;
									$error = '合同约定付款金额与服务金额不一致';
								}
							}
						}
						*/
					}
					
					// TODO
					if ($success) {
						// 执行金额拆月校验
						if ($paytime_amount > 0) {
							$cy_amount_array = $this->cy_amount_array;
							$cy_amount = 0;
							$time_isok = TRUE;
							$dup_isok = TRUE;
							$d = array ();
							if (! empty ( $cy_amount_array )) {
								foreach ( $cy_amount_array as $caa ) {
									$cy_amount += $caa ['amount'];
									if (in_array ( $caa ['time'], $d, TRUE )) {
										$dup_isok = FALSE;
										break;
									} else {
										$d [] = $caa ['time'];
									}
									
									if (strtotime ( $caa ['time'] ) < strtotime ( date ( 'Y-m', strtotime ( $this->starttime ) ) ) || strtotime ( $caa ['time'] ) > strtotime ( date ( 'Y-m', strtotime ( $this->endtime ) ) )) {
										$time_isok = FALSE;
										break;
									}
								}
							}
							
							$cy_amount = round ( $cy_amount, 2 );
							
							if (! $time_isok) {
								$success = FALSE;
								$error = '执行金额拆月日期应该在项目执行期内';
							} else if (! $dup_isok) {
								$success = FALSE;
								$error = '执行金额拆月日期有重复';
							} else if ($cy_amount !== $paytime_amount) {
								$success = FALSE;
								$error = '执行金额拆月总和与合同约定付款金额不一致';
							}
						}
					}
					
					if ($success) {
						// 合同约定付款时间
						$paytime_ids = array ();
						if (! empty ( $paycount_array )) {
							foreach ( $paycount_array as $paycount ) {
								$insert = $this->db->query ( 'INSERT INTO executive_paytime(pid,paytime,amount,billtype,remark,time) VALUES("","' . $paycount ['time'] . '",' . $paycount ['amount'] . ',"' . $billtype . '","' . $paycount ['remark'] . '",' . time () . ')' );
								if ($insert === FALSE) {
									$success = FALSE;
									$error = '写入合同约定付款时间记录失败';
									break;
								} else {
									$paytime_ids [] = $this->db->insert_id;
								}
							}
						}
						
						// 成本明细
						if ($success) {
							$costcount_array = $this->costcount_array;
							$costcount_ids = array ();
							$costcount_amount = 0;
							$yg = 0;
							if (! empty ( $costcount_array )) {
								foreach ( $costcount_array as $costcount ) {
									$supplier_id = 0;
									if (NEW_SUPPLIER_ON) {
										// 判断选取的供应商是否存在在
										$supplier_id = $this->db->get_var ( 'SELECT id FROM new_supplier WHERE supplier_name="' . $costcount ['name'] . '" AND isok=1' );
										if ($supplier_id <= 0) {
											$success = FALSE;
											$error = '供应商【' . $costcount ['name'] . '】不存在或已撤销';
											break;
										}
									}
									
									$insert = $this->db->query ( 'INSERT INTO executive_costinfo(type,amount,name,yg,time,supplier_id) VALUES("' . $costcount ['type'] . '",' . $costcount ['amount'] . ',"' . $costcount ['name'] . '","' . $costcount ['yg'] . '",' . time () . ',' . $supplier_id . ')' );
									if ($insert === FALSE) {
										$success = FALSE;
										$error = '写入成本明细记录失败';
										break;
									} else {
										$costcount_ids [] = $this->db->insert_id;
										$costcount_amount += $costcount ['amount'];
										$yg += $costcount ['yg'];
									}
								}
							}
						}
						
						// 成本支付明细
						if ($success) {
							$costpaycount_array = $this->costpaycount_array;
							$costpaycount_ids = array ();
							$costpaycount_amount = 0;
							$costpayid_rid = array ();
							// $cy_cost = array();
							if (! empty ( $costpaycount_array )) {
								foreach ( $costpaycount_array as $kk => $costpaycount ) {
									$supplier_id2 = 0;
									if (NEW_SUPPLIER_ON) {
										// 判断选取的供应商是否存在在
										$supplier_id2 = $this->db->get_var ( 'SELECT id FROM new_supplier WHERE supplier_name="' . $costpaycount ['name'] . '" AND isok=1' );
										if ($supplier_id2 <= 0) {
											$success = FALSE;
											$error = '供应商【' . $costpaycount ['name'] . '】不存在或已撤销';
											break;
										}
									}
									
									$insert = $this->db->query ( 'INSERT INTO executive_paycost(payname,paytime,payamount,paytype,time,supplier_id) VALUES("' . $costpaycount ['name'] . '","' . $costpaycount ['time'] . '",' . $costpaycount ['amount'] . ',"' . $costpaycount ['type'] . '",' . time () . ',' . $supplier_id2 . ')' );
									if ($insert === FALSE) {
										$success = FALSE;
										$error = '写入成本支付明细记录失败';
										break;
									} else {
										$cpid = $this->db->insert_id;
										$costpaycount_ids [] = $cpid;
										$costpaycount_amount += $costpaycount ['amount'];
										$costpayid_rid [$kk] = $cpid;
									}
								}
							}
						}
						
						if ($success) {
							$pid = $this->db->get_var ( 'SELECT pid FROM executive WHERE cid="' . strtoupper ( $cid [0] ) . '" ORDER BY pid DESC LIMIT 1' );
							if ($pid !== NULL) {
								$pid = explode ( '-', $pid );
								$pid = sprintf ( '%s-%03d', strtoupper ( $cid [0] ), intval ( $pid [1] ) + 1 );
							} else {
								$pid = strtoupper ( $cid [0] ) . '-001';
							}
							
							$supports = $this->support_array;
							$support_ids = array ();
							if (! empty ( $supports )) {
								foreach ( $supports as $support ) {
									$insert = $this->db->query ( 'INSERT INTO executive_dep(pid,dep) VALUES("' . $pid . '",' . $support . ')' );
									if ($insert === FALSE) {
										$success = FALSE;
										$error = '写入支持部门记录失败';
										break;
									} else {
										$support_ids [] = $support . '^' . $this->db->insert_id;
									}
								}
							}
						}
						
						if ($success) {
							$server = array ();
							if (! empty ( $servercfs )) {
								foreach ( $servercfs as $servercf ) {
									$server [] = $servercf ['type'] . '^' . $servercf ['amount'] . '^' . $servercf ['remark'];
								}
							}
							
							$insert = $this->db->query ( 'INSERT INTO executive(pid,cid,city,dep,team,type,name,company,dids,principal,actor,starttime,endtime,paytimeinfoids,amount,allcost,isyg,costinfoids,cost,costpaymentinfoids,costpayment,remark,support,time,user,step,pcid,audittime,servicecf) VALUES("' . $pid . '","' . strtoupper ( $cid [0] ) . '",' . $this->getBelong_city () . ',' . $this->getBelong_dep () . ',' . $this->getBelong_team () . ',' . $this->exetype . ',"' . $this->projectname . '",' . $this->execompany . ',"' . $this->dids . '",' . $this->principal . ',"' . $this->actor . '","' . $this->starttime . '","' . $this->endtime . '","' . implode ( '^', $paytime_ids ) . '",' . $paytime_amount . ',' . $costcount_amount . ',' . ($yg > 0 ? 1 : 0) . ',"' . implode ( '^', $costcount_ids ) . '",' . $costcount_amount . ',"' . implode ( '^', $costpaycount_ids ) . '",' . $costpaycount_amount . ',"' . $this->remark . '","' . implode ( '|', $support_ids ) . '",' . time () . ',' . $this->getUid () . ',1,' . $this->process . ',' . time () . ',' . (! empty ( $server ) ? '"' . implode ( '|', $server ) . '"' : 'NULL') . ')' );
							if ($insert === FALSE) {
								$success = FALSE;
								$error = '写入执行单记录失败';
							} else {
								$executive_id = $this->db->insert_id;
								if (CY_ON) {
									// 拆月数据
									// 执行金额拆月
									$cysql = array ();
									foreach ( $this->cy_amount_array as $carray ) {
										$ym = $carray ['time'];
										$cysql [] = '(' . $executive_id . ',"' . $pid . '",' . reset ( explode ( '-', $ym ) ) . ',' . end ( explode ( '-', $ym ) ) . ',"' . $ym . '",' . $carray ['amount'] . ',' . $carray ['amount'] . ')';
									}
									if (! empty ( $cysql )) {
										$insert_result = $this->db->query ( 'INSERT INTO executive_amount_cy(executive_id,pid,year,month,ym,quote_amount,finance_quote_amount) VALUES' . implode ( ',', $cysql ) );
										if ($insert_result === FALSE) {
											$success = FALSE;
											$error = '新建执行金额拆月数据失败';
										}
									}
									
									if ($success) {
										$cy_json = json_decode ( base64_decode ( $this->cy_json ) );
										$cy_json = ( array ) $cy_json;
										if (! empty ( $cy_json )) {
											$count = 0;
											$sql = array ();
											
											foreach ( $cy_json as $cy ) {
												$cy = ( array ) $cy;
												$cyitems = $cy ['items'];
												$cyitems = ( array ) $cyitems;
												foreach ( $cyitems as $kkk => $cyitem ) {
													foreach ( $cyitem as $cyitemem ) {
														$sid = 0;
														$caid = 0;
														if (NEW_SUPPLIER_ON) {
															$sggs = Supplier::getSupplierInstance ();
															foreach ( $sggs as $key => $value ) {
																if ($value === $cy ['supplier']) {
																	$sid = $key;
																	break;
																}
															}
															
															if ($sid <= 0) {
																$success = FALSE;
																$error = '供应商【' . $cy ['supplier'] . '】不存在';
																break 3;
															}
															
															$sg = Supplier::getInstance ();
															/*
															 * $sg_s = $sg['supplier'];
															 *
															 * foreach ($sg_s as $k => $v) {
															 * if ($v['sn']
															 * === $cy['supplier']) {
															 * $sid = $k;
															 * break;
															 * }
															 * }
															 */
															
															if ($sid > 0) {
																$sg_c = $sg ['supplier_category'] [$sid];
																foreach ( $sg ['supplier_category'] [$sid] as $vv ) {
																	if ($sg ['category'] [$vv] === $cy ['type']) {
																		$caid = $vv;
																		break;
																	}
																}
															}
														}
														$sql [] = '(' . $executive_id . ',"' . $pid . '","' . $cy ['supplier'] . '","' . $cy ['type'] . '",' . reset ( explode ( '-', $cyitemem->date ) ) . ',' . end ( explode ( '-', $cyitemem->date ) ) . ',"' . $cyitemem->date . '",0,0,' . $cyitemem->cost . ',' . $cyitemem->cost . ',' . (empty ( $cy ['short_id'] ) ? 0 : $cy ['short_id']) . ',' . $sid . ',' . $caid . ',' . (empty ( $cy ['industry_id'] ) ? 0 : $cy ['industry_id']) . ',' . (empty ( $costpayid_rid [$kkk] ) ? 0 : $costpayid_rid [$kkk]) . ')';
													}
												}
												$count ++;
											}
											
											if ($success) {
												if (! empty ( $sql )) {
													$insert_result = $this->db->query ( 'INSERT INTO executive_cy(executive_id,pid,payname,deliverytype,year,month,ym,quote_amount,finance_quote_amount,cost_amount,finance_cost_amount,supplier_short_id,supplier_id,category_id,industry_id,paycost_id) VALUES ' . implode ( ',', $sql ) );
													if ($insert_result === FALSE) {
														$success = FALSE;
														$error = '新建执行单拆月数据失败';
													}
												}
											}
										}
									}
								}
								
								if ($success) {
									$insert = $this->do_executive_log ( $pid, '', '执行单发起人', '<font color=\'#99cc00\'>新建执行单</font>' );
									if ($insert ['status'] === 'error') {
										$success = FALSE;
										$error = '新建执行单记录日志失败';
									}
								}
							}
						}
					}
				}
			}
			
			if ($success) {
				$this->db->query ( 'COMMIT' );
			} else {
				$this->db->query ( 'ROLLBACK' );
			}
			return array (
					'status' => $success ? 'success' : 'error',
					'message' => $success ? '您已创建执行单，请等待审核' : $error 
			);
		}
		return array (
				'status' => 'error',
				'message' => $this->errors 
		);
	}
	private function _get_support_allcost_isyg($support_str) {
		$allcost = 0;
		$isyg = 0;
		$support = User::get_support_array ( $support_str );
		if (! empty ( $support )) {
			foreach ( $support as $key => $value ) {
				$row = $this->db->get_row ( 'SELECT cost,costinfoids FROM executive_dep WHERE id=' . intval ( $value ) );
				if ($row !== NULL) {
					$cost = $row->cost;
					$cost_info_id = $row->costinfoids;
				} else {
					$cost = 0;
					$cost_info_id = NULL;
				}
				
				if ($cost_info_id !== NULL) {
					$ids = explode ( '^', $cost_info_id );
					foreach ( $ids as $id ) {
						$ygrow = $this->db->get_row ( 'SELECT yg FROM executive_costinfo WHERE id=' . intval ( $id ) );
						if ($ygrow !== NULL) {
							if (intval ( $ygrow->yg ) > 0) {
								$isyg = 1;
								break;
							}
						}
					}
				}
				$allcost += $cost;
			}
		}
		return array (
				'allcost' => $allcost,
				'isyg' => $isyg 
		);
	}
	private function _get_paytime_infos($contract_billtype) {
		// 合同约定付款日期
		$success = TRUE;
		$paycount_arrays = $this->paycount_array;
		$paytimeinfoids = array ();
		$amount = 0;
		if (! empty ( $paycount_arrays )) {
			foreach ( $paycount_arrays as $paycount_array ) {
				$insert_result = $this->db->query ( 'INSERT INTO executive_paytime(pid,paytime,amount,billtype,remark,time) VALUE("","' . $paycount_array ['time'] . '",' . $paycount_array ['amount'] . ',"' . $contract_billtype . '","' . $paycount_array ['remark'] . '",' . time () . ')' );
				if ($insert_result === FALSE) {
					$success = FALSE;
					break;
				} else {
					$paytimeinfoids [] = $this->db->insert_id;
					$amount += $paycount_array ['amount'];
				}
			}
		}
		if ($success) {
			return array (
					'status' => 'success',
					'message' => '新增合同约定付款日期记录成功',
					'paytimeinfoids' => $paytimeinfoids,
					'paytime_allamount' => $amount 
			);
		} else {
			return array (
					'status' => 'error',
					'message' => '新增合同约定付款日期记录失败' 
			);
		}
	}
	private function _get_cost_infos() {
		// 成本明细
		$success = TRUE;
		$costcount_arrays = $this->costcount_array;
		$costinfoids = array ();
		$cost = 0;
		$cost_yg = 0;
		
		$err = '';
		
		if (! empty ( $costcount_arrays )) {
			foreach ( $costcount_arrays as $costcount_array ) {
				$supplier_id = 0;
				if (NEW_SUPPLIER_ON) {
					// 判断选取的供应商是否存在在
					$supplier_id = $this->db->get_var ( 'SELECT id FROM new_supplier WHERE supplier_name="' . $costcount_array ['name'] . '" AND isok=1' );
					
					if ($supplier_id <= 0) {
						$success = FALSE;
						$err = '供应商【' . $costcount_array ['name'] . '】不存在';
						break;
					}
				}
				
				$insert_result = $this->db->query ( 'INSERT INTO executive_costinfo(type,amount,name,yg,time,supplier_id) VALUE("' . $costcount_array ['type'] . '",' . $costcount_array ['amount'] . ',"' . $costcount_array ['name'] . '","' . $costcount_array ['yg'] . '",' . time () . ',' . $supplier_id . ')' );
				if ($insert_result === FALSE) {
					$success = FALSE;
					$err = '记录成本明细失败';
					break;
				} else {
					$costinfoids [] = $this->db->insert_id;
					$cost += $costcount_array ['amount'];
					if ($cost_yg === 0 && $costcount_array ['yg'] > 0) {
						$cost_yg = 1;
					}
				}
			}
		}
		if ($success) {
			return array (
					'status' => 'success',
					'message' => '新增成本明细记录成功',
					'costinfoids' => $costinfoids,
					'cost' => $cost,
					'cost_yg' => $cost_yg 
			);
		} else {
			return array (
					'status' => 'error',
					'message' => $err 
			);
		}
	}
	private function _get_cost_payment_infos() {
		// 成本支付明细
		$success = TRUE;
		$costpaycount_arrays = $this->costpaycount_array;
		$costpaymentinfoids = array ();
		$payname = array ();
		$costpayment = 0;
		$costpayid_rid = array ();
		if (! empty ( $costpaycount_arrays )) {
			foreach ( $costpaycount_arrays as $kkk => $costpaycount_array ) {
				$supplier_id = 0;
				if (NEW_SUPPLIER_ON) {
					// 判断选取的供应商是否存在在
					$supplier_id = $this->db->get_var ( 'SELECT id FROM new_supplier WHERE supplier_name="' . $costpaycount_array ['name'] . '" AND isok=1' );
					if ($supplier_id <= 0) {
						$success = FALSE;
						break;
					}
				}
				$insert_result = $this->db->query ( 'INSERT INTO executive_paycost(payname,paytime,payamount,paytype,time,supplier_id) VALUE("' . $costpaycount_array ['name'] . '","' . $costpaycount_array ['time'] . '",' . $costpaycount_array ['amount'] . ',"' . $costpaycount_array ['type'] . '",' . time () . ',' . $supplier_id . ')' );
				if ($insert_result === FALSE) {
					$success = FALSE;
					break;
				} else {
					$id = $this->db->insert_id;
					$costpaymentinfoids [] = $id;
					$costpayment += $costpaycount_array ['amount'];
					$payname [] = $costpaycount_array ['name'];
					$costpayid_rid [$kkk] = $id;
				}
			}
		}
		if ($success) {
			return array (
					'status' => 'success',
					'message' => '新增成本支付明细记录成功',
					'costpaymentinfoids' => $costpaymentinfoids,
					'costpayment' => $costpayment,
					'costpayname' => $payname,
					'costpayid_rid' => $costpayid_rid 
			);
		} else {
			return array (
					'status' => 'error',
					'message' => '新增成本支付明细记录失败' 
			);
		}
	}
	
	// private function _do_executive_log($pid, $content, $auditname, $type)
	public function do_executive_log($pid, $content, $auditname, $type) {
		$insert_result = $this->db->query ( 'INSERT INTO executive_log(pid,content,time,uid,auditname,step,type) VALUE("' . $pid . '","' . $content . '",' . time () . ',' . $this->getUid () . ',"' . $auditname . '",0,"' . $type . '")' );
		if ($insert_result !== FALSE) {
			return array (
					'status' => 'success',
					'message' => '日志记录成功',
					'msgid' => $this->db->insert_id 
			);
		} else {
			return array (
					'status' => 'error',
					'message' => '日志记录失败' 
			);
		}
	}
	private function do_executive_audit_time($pid_id) {
		$update_result = $this->db->query ( 'UPDATE executive SET audittime=' . time () . ' WHERE id=' . intval ( $pid_id ) );
		if ($update_result !== FALSE) {
			return array (
					'status' => 'success',
					'message' => '更新审核时间成功' 
			);
		} else {
			return array (
					'status' => 'error',
					'message' => '更新审核时间失败' 
			);
		}
	}
	private function _get_finance_cy($executive_id, $isdep = FALSE, $dep = NULL) {
		$result = array ();
		$datas = $this->db->get_results ( 'SELECT payname,deliverytype,ym,finance_quote_amount,finance_cost_amount FROM executive_cy WHERE executive_id=' . intval ( $executive_id ) . ($isdep && $dep !== NULL ? ' AND is_support=1 AND support_dep=' . intval ( $dep ) : ' AND is_support=0 AND support_dep=0') . ' ORDER BY payname,year,month' );
		if ($datas !== NULL) {
			foreach ( $datas as $data ) {
				$result [$data->payname . '_' . $data->deliverytype] [$data->ym] = array (
						'quote' => $data->finance_quote_amount,
						'cost' => $data->finance_cost_amount 
				);
			}
		}
		return $result;
	}
	private static function _get_finance_cy_amount($ym, $old_amount, $new_amount) {
		if (strtotime ( $ym ) === FALSE) {
			return NULL;
		}
		if ($old_amount === NULL) {
			$old_amount = 0;
		}
		if (strtotime ( date ( 'Y-m', time () ) ) > strtotime ( $ym )) {
			return array (
					'show' => $old_amount,
					'difference' => $new_amount - $old_amount 
			);
		} else {
			return array (
					'show' => $new_amount,
					'difference' => 0 
			);
		}
	}
	private function _getPidPaymentApply($pid) {
		$applied = array ();
		// 判断是否已归档
		$results = $this->db->get_results ( 'SELECT paycostid,gd_amount FROM finance_payment_gd WHERE pid="' . $pid . '"' );
		if ($results !== NULL) {
			foreach ( $results as $result ) {
				$applied ['gd'] [$result->paycostid] [] = $result->gd_amount;
			}
		} else {
			// 判断付款申请
			$results = $this->db->get_results ( 'SELECT a.paycostid,a.payment_amount FROM finance_payment_person_apply_list a LEFT JOIN finance_payment_person_apply b ON a.apply_id=b.id WHERE a.pid="' . $pid . '" AND a.isok<>-1 AND b.isok<>-1
UNION ALL
SELECT a.paycostid,a.payment_amount FROM finance_payment_media_apply_list a LEFT JOIN finance_payment_media_apply b ON a.apply_id=b.id WHERE a.pid="' . $pid . '" AND a.isok<>-1 AND b.isok<>-1' );
			if ($results !== NULL) {
				foreach ( $results as $result ) {
					$applied ['apply'] [$result->paycostid] [] = $result->payment_amount;
				}
			}
		}
		return $applied;
	}
	public function update_executive() {
		if ($this->validate_form_value ( 'update' )) {
			$success = TRUE;
			$error = '';
			$this->db->query ( 'BEGIN' );
			
			$row = $this->db->get_row ( 'SELECT support,id,is_closed,isalter FROM executive WHERE pid="' . $this->pid . '" AND isok=0 ORDER BY id DESC' );
			if ($row === NULL) {
				$success = FALSE;
				$error = '执行单号有误';
			} else {
				if (intval ( $row->is_closed ) === 1) {
					$success = FALSE;
					$error = '执行单已关闭，无法修改，详情请联系财务部';
				}
			}
			
			if ($success) {
				
				$supports = array ();
				
				$pid_arr = explode ( '-', $this->pid );
				$crow = $this->db->get_row ( 'SELECT billtype FROM contract_cus WHERE cid="' . strtoupper ( $pid_arr [0] ) . '"' );
				if ($crow === NULL) {
					$success = FALSE;
					$error = '所属合同不存在';
				} else {
					$payamount = 0;
					$paycount_arrays = $this->paycount_array;
					foreach ( $paycount_arrays as $paycount_array ) {
						$payamount += $paycount_array ['amount'];
					}
					// $payamount = round($payamount / 1.0683, 2);
					$payamount = round ( $payamount, 2 );
					
					if($payamount > 0){
						if (empty ( $this->servercf_array )) {
							$success = FALSE;
							$error = '税前、税费金额需要拆分';
						} else {
							$tax_rate = 1;
							if(intval ( $crow->billtype ) === 2){
								$tax_rate = 1 + FW_TAX_RATE;
							}else if(intval ( $crow->billtype ) === 1){
								$tax_rate = 1 + GG_TAX_RATE;
							}
							
							$servicecfamount = 0;
								
							$servercf_arrays = $this->servercf_array;
							foreach ( $servercf_arrays as $servercf_array ) {
								$servicecfamount += $servercf_array ['amount'];
							}
								
							if (round ( abs ( round ( $payamount / $tax_rate, 2 ) - $servicecfamount ), 2 ) > 0.01) {
								$success = FALSE;
								$error = '合同约定付款金额与服务金额不一致！';
							}
						}
					}
					
					/*
					if (intval ( $crow->billtype ) === 2 && $payamount > 0) {
						if (empty ( $this->servercf_array )) {
							$success = FALSE;
							$error = '该合同为服务合同，必须进行付款金额的拆分！';
						} else {
							$servicecfamount = 0;
							
							$servercf_arrays = $this->servercf_array;
							foreach ( $servercf_arrays as $servercf_array ) {
								$servicecfamount += $servercf_array ['amount'];
							}
							
							if (round ( abs ( round ( $payamount / 1.0683, 2 ) - $servicecfamount ), 2 ) > 0.01) {
								$success = FALSE;
								$error = '合同约定付款金额与服务金额不一致！';
							}
						}
					}
					*/
					
					if ($success && $payamount > 0) {
						// 检验开票金额
						$inv = new Invoice ();
						$sum_inv = $inv->getSumPidInvoice ( $this->pid );
						if ($payamount < round ( $sum_inv, 2 )) {
							$success = FALSE;
							$error = '该执行单开票金额已大于执行金额，请调整开票金额并由财务部审核通过后，才可以修改该执行单';
						}
						unset ( $inv );
					}
					
					if ($success && CUSTOMER_SAFETY_ON) {
						$cusrow = $this->db->get_row ( 'SELECT customer_id FROM v_cid_customer WHERE cid="' . strtoupper ( $pid_arr [0] ) . '"' );
						if ($cusrow === NULL) {
							$success = FALSE;
							$error = '该客户暂时未购买保险额度，无法修改执行单，请联系财务部Alex';
						} else {
							if ($paytime_amount > 0 && intval ( $this->execompany ) === 3) {
								// 校验系统客户保险额度
								$cus = new Customer ( array (
										'customer_id' => intval ( $cusrow->customer_id ) 
								) );
								$remainder = $cus->compute_remainder_safety ( $this->pid );
								unset ( $cus );
								
								if ($payamount > $remainder) {
									$success = FALSE;
									$error = '执行单金额大于该客户剩余保险额度，无法修改执行单，请联系财务部Alex';
								}
							}
						}
					}
					
					// TODO
					if ($success && $payamount > 0) {
						$cy_amount_array = $this->cy_amount_array;
						$cy_amount = 0;
						$time_isok = TRUE;
						if (! empty ( $cy_amount_array )) {
							foreach ( $cy_amount_array as $caa ) {
								$cy_amount += $caa ['amount'];
								if ($time_isok && intval ( $row->isalter ) === 0) {
									if (strtotime ( $caa ['time'] ) < strtotime ( date ( 'Y-m', strtotime ( $this->starttime ) ) ) || strtotime ( $caa ['time'] ) > strtotime ( date ( 'Y-m', strtotime ( $this->endtime ) ) )) {
										$time_isok = FALSE;
									}
								}
							}
						}
						
						$cy_amount = round ( $cy_amount, 2 );
						if ($cy_amount !== $payamount) {
							$success = FALSE;
							$error = '执行金额拆月总和与合同约定付款金额不一致';
						} else if (! $time_isok) {
							$success = FALSE;
							$error = '执行金额拆月日期应该在项目执行期内';
						}
					}
					
					if ($success) {
						// $row = $this->db
						// ->get_row(
						// 'SELECT support,id,is_closed FROM executive WHERE pid="'
						// . $this->pid
						// . '" AND isok=0 ORDER BY id DESC');
						// if ($row === NULL) {
						// $success = FALSE;
						// $error = '执行单号有误';
						// } else {
						// if (intval($row->is_closed) === 1) {
						// $success = FALSE;
						// $error = '执行单已关闭，无法修改，详情请联系财务部';
						// } else {
						// 得到之前的支持部门成本总和 是否含有预估
						$old_support_result = $this->_get_support_allcost_isyg ( $row->support );
						$allcost = $old_support_result ['allcost'];
						$isyg = $old_support_result ['isyg'];
						
						$pid_id = $row->id;
						$_oldsupport = User::get_support_array ( $row->support );
						$_oldsupportdep = array_keys ( $_oldsupport );
						
						// 合同约定付款时间
						$paytime = $this->_get_paytime_infos ( $crow->billtype );
						if ($paytime ['status'] === 'error') {
							$success = FALSE;
							$error = '执行单修改失败，错误代码1';
						} else {
							// 成本明细
							$cost_info = $this->_get_cost_infos ();
							if ($cost_info ['status'] === 'error') {
								$success = FALSE;
								$error = $cost_info ['message'];
							} else {
								$allcost += $cost_info ['cost'];
								$isyg = $isyg + $cost_info ['cost_yg'] > 0 ? 1 : 0;
								
								// 成本支付明细
								$cost_payment = $this->_get_cost_payment_infos ();
								if ($cost_payment ['status'] === 'error') {
									$success = FALSE;
									$error = '执行单修改失败，错误代码3';
								} else {
									// 分配支持部门入表，得到支持部门内容ID
									$support = $this->support_array;
									$new_supports = array ();
									foreach ( $support as $sp ) {
										if (in_array ( $sp, $_oldsupportdep )) {
											// 已有的支持部门
											$new_supports [] = sprintf ( '%s^%s', $sp, $_oldsupport [$sp] );
										} else {
											// 新的支持部门
											$insert_result = $this->db->query ( 'INSERT INTO executive_dep(pid,dep) VALUE("' . $this->pid . '",' . $sp . ')' );
											if ($insert_result === FALSE) {
												$success = FALSE;
												$error = '执行单修改失败，错误代码4';
												break;
											} else {
												$new_supports [] = sprintf ( '%s^%s', $sp, $this->db->insert_id );
											}
										}
									}
									
									if ($success) {
										// 拆月
										// 执行金额拆月
										$cysql = array ();
										foreach ( $this->cy_amount_array as $carray ) {
											$ym = $carray ['time'];
											$cysql [] = '(' . $row->id . ',"' . $this->pid . '",' . reset ( explode ( '-', $ym ) ) . ',' . end ( explode ( '-', $ym ) ) . ',"' . $ym . '",' . $carray ['amount'] . ',' . $carray ['amount'] . ')';
										}
										
										$delete_result = $this->db->query ( 'DELETE FROM executive_amount_cy WHERE executive_id=' . $row->id . '  AND pid="' . $this->pid . '"' );
										if ($delete_result === FALSE) {
											$success = FALSE;
											$error = '删除执行金额拆月数据失败';
										} else {
											if (! empty ( $cysql )) {
												
												$insert_result = $this->db->query ( 'INSERT INTO executive_amount_cy(executive_id,pid,year,month,ym,quote_amount,finance_quote_amount) VALUES' . implode ( ',', $cysql ) );
												if ($insert_result === FALSE) {
													$success = FALSE;
													$error = '新建执行金额拆月数据失败';
												}
											}
										}
										
										if ($success) {
											
											$cy_json = json_decode ( base64_decode ( $this->cy_json ) );
											$cy_json = ( array ) $cy_json;
											if (! empty ( $cy_json )) {
												$count = 0;
												$sql = array ();
												
												$costpayid_rid = $cost_payment ['costpayid_rid'];
												// var_dump($costpayid_rid);
												// var_dump($cy_json);
												
												$start = date ( 'Y-m', strtotime ( $this->starttime ) );
												$end = date ( 'Y-m', strtotime ( $this->endtime ) );
												
												foreach ( $cy_json as $cy ) {
													$cy = ( array ) $cy;
													$cyitems = $cy ['items'];
													$cyitems = ( array ) $cyitems;
													foreach ( $cyitems as $kkk => $cyitem ) {
														foreach ( $cyitem as $cyitemem ) {
															$sid = 0;
															$caid = 0;
															if (NEW_SUPPLIER_ON) {
																if (intval ( $row->isalter ) === 0 && (strtotime ( $cyitemem->date ) < strtotime ( $start ) || strtotime ( $cyitemem->date ) > strtotime ( $end ))) {
																	$success = FALSE;
																	$error = '项目执行日期为' . $start . '至' . $end . '，成本拆月数据必须在该日期范围内';
																	break 3;
																}
																
																$sggs = Supplier::getSupplierInstance ();
																foreach ( $sggs as $key => $value ) {
																	if ($value === $cy ['supplier']) {
																		$sid = $key;
																		break;
																	}
																}
																
																if ($sid <= 0) {
																	$success = FALSE;
																	$error = '供应商【' . $cy ['supplier'] . '】不存在';
																	break 3;
																}
																$sg = Supplier::getInstance ();
																/*
																 * $sg_s = $sg['supplier'];
																 *
																 * foreach ($sg_s as $k => $v) {
																 * if ($v['sn']
																 * === $cy['supplier']) {
																 * $sid = $k;
																 * break;
																 * }
																 * }
																 */
																
																if ($sid > 0) {
																	$sg_c = $sg ['supplier_category'] [$sid];
																	foreach ( $sg ['supplier_category'] [$sid] as $vv ) {
																		if ($sg ['category'] [$vv] === $cy ['type']) {
																			$caid = $vv;
																			break;
																		}
																	}
																}
															}
															
															$sql [] = '(' . intval ( $row->id ) . ',"' . $this->pid . '","' . $cy ['supplier'] . '","' . $cy ['type'] . '",' . reset ( explode ( '-', $cyitemem->date ) ) . ',' . end ( explode ( '-', $cyitemem->date ) ) . ',"' . $cyitemem->date . '",0,0,' . $cyitemem->cost . ',' . $cyitemem->cost . ',' . (empty ( $cy ['short_id'] ) ? 0 : $cy ['short_id']) . ',' . $sid . ',' . $caid . ',' . (empty ( $cy ['industry_id'] ) ? 0 : $cy ['industry_id']) . ',' . (empty ( $costpayid_rid [$kkk] ) ? 0 : $costpayid_rid [$kkk]) . ')';
														}
													}
													$count ++;
												}
												// var_dump($sql);
												if ($success) {
													if (! empty ( $sql )) {
														
														// 删除
														$insert_result = $this->db->query ( 'DELETE FROM executive_cy WHERE executive_id=' . intval ( $row->id ) . ' AND is_support=0 AND support_dep=0' );
														if ($insert_result === FALSE) {
															$success = FALSE;
															$error = '修改执行单拆月数据失败，错误代码1';
														} else {
															// var_dump($sql);
															$insert_result = $this->db->query ( 'INSERT INTO executive_cy(executive_id,pid,payname,deliverytype,year,month,ym,quote_amount,finance_quote_amount,cost_amount,finance_cost_amount,supplier_short_id,supplier_id,category_id,industry_id,paycost_id) VALUES ' . implode ( ',', $sql ) );
															if ($insert_result === FALSE) {
																$success = FALSE;
																$error = '修改执行单拆月数据失败';
															}
														}
													}
												}
											} else {
												// 删除
												$insert_result = $this->db->query ( 'DELETE FROM executive_cy WHERE executive_id=' . intval ( $row->id ) . ' AND is_support=0 AND support_dep=0' );
												if ($insert_result === FALSE) {
													$success = FALSE;
													$error = '修改执行单拆月数据失败，错误代码3';
												}
											}
										}
									}
									
									if ($success) {
										$update_result = $this->db->query ( 'UPDATE executive SET type=' . $this->exetype . ',name="' . $this->projectname . '",company=' . $this->execompany . ',dids="' . $this->dids . '",principal=' . $this->principal . ',actor="' . $this->actor . '",starttime="' . $this->starttime . '",endtime="' . $this->endtime . '",paytimeinfoids="' . implode ( '^', $paytime ['paytimeinfoids'] ) . '",servicecf="' . User::combine_array ( $this->servercf_array ) . '",amount="' . $paytime ['paytime_allamount'] . '",allcost="' . $allcost . '",isyg=' . $isyg . ',costinfoids="' . implode ( '^', $cost_info ['costinfoids'] ) . '",cost="' . $cost_info ['cost'] . '",costpaymentinfoids="' . implode ( '^', $cost_payment ['costpaymentinfoids'] ) . '",costpayment="' . $cost_payment ['costpayment'] . '",remark="' . $this->remark . '",support="' . implode ( '|', $new_supports ) . '",user=' . $this->getUid () . ',step=1,pcid=' . $this->process . ' WHERE id=' . intval ( $row->id ) );
										if ($update_result === FALSE) {
											$success = FALSE;
											$error = '执行单修改失败，错误代码5';
										} else {
											if (intval ( $this->is2agent ) === 1) {
												$update_result = $this->db->query ( 'DELETE FROM executive_agent WHERE executive_id=' . intval ( $row->id ) . ' AND pid="' . $this->pid . '"' );
												if ($update_result === FALSE) {
													$success = FALSE;
													$error = '执行单修改失败，错误代码6';
												} else {
													if (intval ( $this->is2agent ) === 1) {
														$update_result = $this->db->query ( 'INSERT INTO executive_agent(executive_id,pid,cusname,customertype) VALUE(' . intval ( $row->id ) . ',"' . $this->pid . '","' . $this->agentcusname . '",' . intval ( $this->customertype ) . ')' );
														if ($update_result === FALSE) {
															$success = FALSE;
															$error = '执行单修改失败，错误代码7';
														}
													}
												}
											}
											
											if ($success) {
												$log = $this->do_executive_log ( $this->pid, '', '执行单发起人', '<font color=\'#99cc00\'>修改执行单</font>' );
												if ($log ['status'] === 'error') {
													$success = FALSE;
													$error = '执行单修改失败，错误代码6';
												} else {
													$audit_result = $this->do_executive_audit_time ( $pid_id );
													if ($audit_result ['status'] === 'error') {
														$success = FALSE;
														$error = '执行单修改失败，错误代码7';
													}
												}
											}
										}
									}
								}
							}
						}
						// }
						// }
					}
				}
			}
			
			if ($success) {
				$this->db->query ( 'COMMIT' );
			} else {
				$this->db->query ( 'ROLLBACK' );
			}
			return array (
					'status' => $success ? 'success' : 'error',
					'message' => $success ? '修改执行单成功' : $error 
			);
		}
		return array (
				'status' => 'error',
				'message' => $this->errors 
		);
	}
	private function _get_executive_records() {
		$results = array ();
		if ($this->pid !== NULL) {
			$executive_records = $this->db->get_results ( 'SELECT a.*,FROM_UNIXTIME(a.time) AS tt,b.contractname,b.cusname,c.realname,c.username,d.realname AS urealname,d.username AS uusername FROM executive a LEFT JOIN contract_cus b ON a.cid = b.cid LEFT JOIN users c ON a.principal = c.uid LEFT JOIN users d ON a.user=d.uid WHERE a.pid="' . $this->pid . '" AND a.isok<>-1 ORDER BY a.id' );
			
			if ($executive_records !== NULL) {
				if (count ( $executive_records ) === 1) {
					$results ['old'] = $executive_records [0];
					$results ['new'] = $executive_records [0];
				} else {
					for($i = count ( $executive_records ) - 1; $i >= 0; $i --) {
						if (intval ( $executive_records [$i]->id ) === intval ( $this->executive_id )) {
							$results ['old'] = $executive_records [($i - 1)];
							$results ['new'] = $executive_records [$i];
						}
					}
				}
			}
		}
		$this->executive_records = $results;
	}
	private function _get_dep_info($support) {
		$depinfo = array ();
		if (! empty ( $support )) {
			$support = explode ( '|', $support );
			foreach ( $support as $sp ) {
				$sp = explode ( '^', $sp );
				$depinfoid = end ( $sp );
				$depinfo [] = array (
						'id' => reset ( $sp ),
						'row' => $this->db->get_row ( 'SELECT * FROM executive_dep WHERE id=' . intval ( $depinfoid ) ) 
				);
			}
		}
		return $depinfo;
	}
	private static function _get_executive_time($isalter, $createtime, $starttime, $endtime, $ismobile = FALSE) {
		$s = sprintf ( '%s / %s', $starttime, $endtime );
		// if ($isalter === 0 && $createtime > strtotime($starttime . ' 23:59:59')) {
		if ($createtime > strtotime ( $starttime . ' 23:59:59' ) && ! $ismobile) {
			$s .= '&nbsp;&nbsp;<font color="red"><b>后补</b></font>';
		}
		return $s;
	}
	public function get_executive_info($datas = NULL, $now_dep = NULL, $iscy = FALSE) {
		// 执行单
		if ($datas === NULL) {
			$this->_get_executive_records ();
			$datas = $this->executive_records;
		}
		
		if ($iscy) {
			$this->contrast = 0;
		} else {
			if ($datas ['old']->id !== $datas ['new']->id && $datas ['old']->id !== NULL) {
				$this->contrast = 1;
			} else {
				$this->contrast = 0;
			}
		}
		
		$pidinfo_buf = file_get_contents ( TEMPLATE_PATH . 'executive/pidinfo' . ($iscy ? '_cy' : (intval ( $this->contrast ) === 1 ? '_double' : '')) . '.tpl' );
		$tmp_searchs = array (
				'PROCESSLIST',
				'TIME',
				'PID',
				'NAME',
				'CITYINFO',
				'DEPNAME',
				'TYPE',
				'CID',
				'CUSNAME',
				'COMPANY',
				'DIDS',
				'PRINCIPAL',
				'ACTOR',
				'EXECUTIME',
				'ALLAMOUNT',
				'ALLCOST',
				'PAYTIMEINFO',
				'SERVICECFINFO',
				'REMARK',
				'USER',
				'SUPPORT',
				'COST',
				'COSTINFO',
				'COSTPAYMENT',
				'COSTPAYMENTINFO',
				'CYAMOUNTINFO',
				'CYCOSTINFO',
				'CYDATAGRID',
				'AMOUNTCY' 
		);
		$search = array ();
		foreach ( $tmp_searchs as $tmp_search ) {
			$search [] = $tmp_search;
			if (intval ( $this->contrast ) === 1) {
				$search [] = $tmp_search . '1';
			}
		}
		
		$replace = array ();
		$dep = Dep::getInstance ();
		$process = Process::getInstance ();
		$process_dep = Dep_Process::getInstance ();
		$has_support = FALSE;
		
		$search = array_map ( array (
				__CLASS__,
				'_generate_search_field' 
		), $search );
		foreach ( $search as $s ) {
			switch ($s) {
				case '[TIME]' :
					$replace [] = $datas ['new']->tt;
					break;
				case '[TIME1]' :
					$replace [] = $datas ['old']->tt;
					break;
				case '[PID]' :
					$replace [] = $datas ['new']->pid;
					break;
				case '[PID1]' :
					$replace [] = $datas ['old']->pid;
					break;
				case '[CITYINFO]' :
					$replace [] = $this->get_user_city_info ( $datas ['new']->city, $datas ['new']->dep, $datas ['new']->team );
					break;
				case '[CITYINFO1]' :
					$replace [] = $this->get_user_city_info ( $datas ['old']->city, $datas ['old']->dep, $datas ['old']->team );
					break;
				case '[DEPNAME]' :
					$replace [] = $this->get_depname ( $dep [$datas ['new']->dep] [1], $dep [$datas ['new']->dep] [0] );
					break;
				case '[DEPNAME1]' :
					$replace [] = $this->get_depname ( $dep [$datas ['old']->dep] [1], $dep [$datas ['old']->dep] [0] );
					break;
				case '[TYPE]' :
					$replace [] = self::_get_executive_typename ( intval ( $datas ['new']->type ) );
					break;
				case '[TYPE1]' :
					$replace [] = self::_get_executive_typename ( intval ( $datas ['old']->type ) );
					break;
				case '[CID]' :
					$replace [] = self::_get_contract_url ( $datas ['new']->cid, $datas ['new']->contractname );
					break;
				case '[CID1]' :
					$replace [] = self::_get_contract_url ( $datas ['old']->cid, $datas ['old']->contractname );
					break;
				case '[CUSNAME]' :
					$replace [] = $datas ['new']->cusname;
					break;
				case '[CUSNAME1]' :
					$replace [] = $datas ['old']->cusname;
					break;
				case '[COMPANY]' :
					$replace [] = self::get_companyname ( intval ( $datas ['new']->company ) );
					break;
				case '[COMPANY1]' :
					$replace [] = self::get_companyname ( intval ( $datas ['old']->company ) );
					break;
				case '[NAME]' :
					$replace [] = self::_get_executive_name_link ( intval ( $datas ['new']->isalter ), intval ( $datas ['new']->id ), $datas ['new']->pid, $datas ['new']->name, intval ( $this->contrast ) === 1 );
					break;
				case '[NAME1]' :
					$replace [] = self::_get_executive_name_link ( intval ( $datas ['old']->isalter ), intval ( $datas ['old']->id ), $datas ['old']->pid, $datas ['old']->name, intval ( $this->contrast ) === 1 );
					break;
				case '[DIDS]' :
					$replace [] = $this->get_upload_files ( $datas ['new']->dids );
					break;
				case '[DIDS1]' :
					$replace [] = $this->get_upload_files ( $datas ['old']->dids );
					break;
				case '[PRINCIPAL]' :
					$replace [] = self::_get_user ( $datas ['new']->realname, $datas ['new']->username );
					break;
				case '[PRINCIPAL1]' :
					$replace [] = self::_get_user ( $datas ['old']->realname, $datas ['old']->username );
					break;
				case '[ACTOR]' :
					$replace [] = $datas ['new']->actor;
					break;
				case '[ACTOR1]' :
					$replace [] = $datas ['old']->actor;
					break;
				case '[EXECUTIME]' :
					$replace [] = self::_get_executive_time ( intval ( $datas ['new']->isalter ), $datas ['new']->time, $datas ['new']->starttime, $datas ['new']->endtime );
					break;
				case '[EXECUTIME1]' :
					$replace [] = self::_get_executive_time ( intval ( $datas ['old']->isalter ), $datas ['old']->time, $datas ['old']->starttime, $datas ['old']->endtime );
					break;
				case '[PAYTIMEINFO]' :
					$replace [] = $this->_get_paytimeinfo ( $datas ['new']->paytimeinfoids, $datas ['new']->cid, $datas ['new']->time );
					break;
				case '[PAYTIMEINFO1]' :
					$replace [] = $this->_get_paytimeinfo ( $datas ['old']->paytimeinfoids, $datas ['old']->cid, $datas ['old']->time );
					break;
				case '[AMOUNTCY]' :
					$replace [] = $this->_get_amount_cy_info ( intval ( $datas ['new']->id ), $datas ['new']->pid );
					break;
				case '[AMOUNTCY1]' :
					$replace [] = $this->_get_amount_cy_info ( intval ( $datas ['old']->id ), $datas ['old']->pid );
					break;
				case '[SERVICECFINFO]' :
					$replace [] = $this->_get_servicecf_info ( $datas ['new']->amount, intval ( $datas ['new']->user ), intval ( $datas ['new']->principal ), $datas ['new']->servicecf );
					break;
				case '[SERVICECFINFO1]' :
					$replace [] = $this->_get_servicecf_info ( $datas ['old']->amount, intval ( $datas ['old']->user ), intval ( $datas ['old']->principal ), $datas ['old']->servicecf );
					break;
				case '[ALLAMOUNT]' :
					$replace [] = Format_Util::my_money_format ( '%.2n', $datas ['new']->amount );
					break;
				case '[ALLAMOUNT1]' :
					$replace [] = Format_Util::my_money_format ( '%.2n', $datas ['old']->amount );
					break;
				case '[ALLCOST]' :
					$replace [] = Format_Util::my_money_format ( '%.2n', $datas ['new']->allcost );
					break;
				case '[ALLCOST1]' :
					$replace [] = Format_Util::my_money_format ( '%.2n', $datas ['old']->allcost );
					break;
				case '[COSTINFO]' :
					$replace [] = $this->_get_costinfo ( $datas ['new']->costinfoids );
					break;
				case '[COSTINFO1]' :
					$replace [] = $this->_get_costinfo ( $datas ['old']->costinfoids );
					break;
				case '[COST]' :
					$replace [] = Format_Util::my_money_format ( '%.2n', $datas ['new']->cost );
					break;
				case '[COST1]' :
					$replace [] = Format_Util::my_money_format ( '%.2n', $datas ['old']->cost );
					break;
				case '[COSTPAYMENTINFO]' :
					$replace [] = $iscy ? $this->_get_edit_costpayment_info ( $datas ['new']->id, $datas ['new']->costpaymentinfoids ) : $this->_get_costpaymentinfo ( $datas ['new']->costpaymentinfoids );
					break;
				case '[COSTPAYMENTINFO1]' :
					$replace [] = $this->_get_costpaymentinfo ( $datas ['old']->costpaymentinfoids );
					break;
				case '[COSTPAYMENT]' :
					$replace [] = Format_Util::my_money_format ( '%.2n', $datas ['new']->costpayment );
					break;
				case '[COSTPAYMENT1]' :
					$replace [] = Format_Util::my_money_format ( '%.2n', $datas ['old']->costpayment );
					break;
				case '[REMARK]' :
					$replace [] = Format_Util::format_html ( $datas ['new']->remark );
					break;
				case '[REMARK1]' :
					$replace [] = Format_Util::format_html ( $datas ['old']->remark );
					break;
				case '[SUPPORT]' :
					$replace [] = $this->_get_support_dep ( $dep, $datas ['new']->support );
					$has_support = ! empty ( $datas ['new']->support );
					break;
				case '[SUPPORT1]' :
					$replace [] = $this->_get_support_dep ( $dep, $datas ['old']->support );
					break;
				case '[USER]' :
					$replace [] = self::_get_user ( $datas ['new']->urealname, $datas ['new']->uusername );
					break;
				case '[USER1]' :
					$replace [] = self::_get_user ( $datas ['old']->urealname, $datas ['old']->uusername );
					break;
				case '[PROCESSLIST]' :
					$replace [] = self::_get_process_content ( intval ( $datas ['new']->pcid ), intval ( $datas ['new']->isok ), intval ( $datas ['new']->step ), 0, $process, $process_dep );
					break;
				case '[PROCESSLIST1]' :
					$replace [] = self::_get_process_content ( intval ( $datas ['old']->pcid ), intval ( $datas ['old']->isok ), intval ( $datas ['old']->step ), 0, $process, $process_dep );
					break;
				case '[CYAMOUNTINFO]' :
					$replace [] = $this->_get_amount_cy ( $datas ['new']->id );
					break;
				case '[CYAMOUNTINFO1]' :
					$replace [] = $this->_get_amount_cy ( $datas ['old']->id );
					break;
				case '[CYCOSTINFO]' :
					$replace [] = $this->_get_amount_cy ( $datas ['new']->id, FALSE );
					break;
				case '[CYCOSTINFO1]' :
					$replace [] = $this->_get_amount_cy ( $datas ['old']->id, FALSE );
					break;
				case '[CYDATAGRID]' :
					$replace [] = $this->_getCYDatagridTable ( $datas ['new']->id );
					break;
				case '[CYDATAGRID1]' :
					$replace [] = $this->_getCYDatagridTable ( $datas ['old']->id );
					break;
			}
		}
		
		$pidinfo_buf = str_replace ( $search, $replace, $pidinfo_buf );
		
		$depinfo = '';
		$depinfo_new = $this->_get_dep_info ( $datas ['new']->support );
		$depinfo_old = $this->_get_dep_info ( $datas ['old']->support );
		
		$new_deps = array ();
		$old_deps = array ();
		foreach ( $depinfo_new as $depnew ) {
			$new_deps [intval ( $depnew ['id'] )] = $depnew ['row'];
		}
		foreach ( $depinfo_old as $depold ) {
			$old_deps [intval ( $depold ['id'] )] = $depold ['row'];
		}
		
		$new_deps_id = array_keys ( $new_deps );
		$old_deps_id = array_keys ( $old_deps );
		
		$depinfo_buf = file_get_contents ( TEMPLATE_PATH . 'executive/depinfo' . (intval ( $this->contrast ) === 1 ? '_double' : '') . '.tpl' );
		
		foreach ( $new_deps_id as $new_dep ) {
			$tmp = str_replace ( array (
					'[DEPNAME]',
					'[DEP_ACTOR]',
					'[DEP_REMARK]',
					'[DEP_DIDS]',
					'[DEP_COSTINFO]',
					'[DEP_COST]',
					'[DEP_COSTPAYMENTINFO]',
					'[DEP_COSTPAYMENT]',
					'[DEP_PROCESSLIST]',
					'[DEPCYDATAGRID]' 
			), 
					// '[DEP_CYCOSTINFO]'
					array (
							$this->get_depname ( $dep [$new_dep] [1], $dep [$new_dep] [0] ),
							$new_deps [$new_dep]->actor,
							Format_Util::format_html ( $new_deps [$new_dep]->remark ),
							$this->get_upload_files ( $new_deps [$new_dep]->dids ),
							$this->_get_costinfo ( $new_deps [$new_dep]->costinfoids ),
							Format_Util::my_money_format ( '%.2n', $new_deps [$new_dep]->cost ),
							$this->_get_costpaymentinfo ( $new_deps [$new_dep]->costpaymentinfoids ),
							Format_Util::my_money_format ( '%.2n', $new_deps [$new_dep]->costpayment ),
							$this->_get_process_content ( intval ( $new_deps [$new_dep]->pcid ), intval ( $new_deps [$new_dep]->isok ), intval ( $new_deps [$new_dep]->step ), intval ( $new_dep ), $process, $process_dep ),
							$this->_getCYDatagridTable ( $datas ['new']->id, $new_dep ) 
					), 
					// $this->_get_amount_cy($new_deps[$new_dep]->costpaymentinfoids, FALSE)
					$depinfo_buf );
			if (intval ( $this->contrast ) === 1) {
				$tmp = str_replace ( array (
						'[DEP_ACTOR1]',
						'[DEP_REMARK1]',
						'[DEP_DIDS1]',
						'[DEP_COSTINFO1]',
						'[DEP_COST1]',
						'[DEP_COSTPAYMENTINFO1]',
						'[DEP_COSTPAYMENT1]',
						'[DEP_PROCESSLIST1]',
						'[DEPCYDATAGRID1]' 
				), 
						// '[DEP_CYCOSTINFO1]'
						array (
								in_array ( $new_dep, $old_deps_id, TRUE ) ? $old_deps [$new_dep]->actor : '',
								in_array ( $new_dep, $old_deps_id, TRUE ) ? Format_Util::format_html ( $old_deps [$new_dep]->remark ) : '',
								in_array ( $new_dep, $old_deps_id, TRUE ) ? $this->get_upload_files ( $old_deps [$new_dep]->dids ) : '',
								in_array ( $new_dep, $old_deps_id, TRUE ) ? $this->_get_costinfo ( $old_deps [$new_dep]->costinfoids ) : '',
								Format_Util::my_money_format ( '%.2n', in_array ( $new_dep, $old_deps_id, TRUE ) ? $old_deps [$new_dep]->cost : 0 ),
								in_array ( $new_dep, $old_deps_id, TRUE ) ? $this->_get_costpaymentinfo ( $old_deps [$new_dep]->costpaymentinfoids ) : '',
								Format_Util::my_money_format ( '%.2n', in_array ( $new_dep, $old_deps_id, TRUE ) ? $old_deps [$new_dep]->costpayment : 0 ),
								(in_array ( $new_dep, $old_deps_id, TRUE ) ? $this->_get_process_content ( intval ( $old_deps [$new_dep]->pcid ), intval ( $old_deps [$new_dep]->isok ), intval ( $old_deps [$new_dep]->step ), intval ( $new_dep ), $process, $process_dep ) : ''),
								in_array ( $new_dep, $old_deps_id, TRUE ) ? $this->_getCYDatagridTable ( $datas ['old']->id, $new_dep ) : '' 
						), 
						// $this->_get_amount_cy($old_deps[$new_dep]->costpaymentinfoids, FALSE)
						$tmp );
				if (in_array ( $new_dep, $old_deps_id, TRUE )) {
					$old_deps_id = Array_Util::my_remove_array_other_value ( $old_deps_id, array (
							$new_dep 
					) );
				}
			}
			$depinfo .= $tmp;
		}
		
		if (! empty ( $old_deps_id ) && intval ( $this->contrast ) === 1) {
			foreach ( $old_deps_id as $old_dep ) {
				$tmp = str_replace ( array (
						'[DEPNAME]',
						'[DEP_ACTOR]',
						'[DEP_REMARK]',
						'[DEP_DIDS]',
						'[DEP_COSTINFO]',
						'[DEP_COST]',
						'[DEP_COSTPAYMENTINFO]',
						'[DEP_COSTPAYMENT]',
						'[DEP_PROCESSLIST]' 
				), array (
						$this->get_depname ( $dep [$old_dep] [1], $dep [$old_dep] [0] ),
						'',
						'',
						'',
						'',
						Format_Util::my_money_format ( '%.2n', 0 ),
						'',
						Format_Util::my_money_format ( '%.2n', 0 ),
						'' 
				), $depinfo_buf );
				$tmp = str_replace ( array (
						'[DEP_ACTOR1]',
						'[DEP_REMARK1]',
						'[DEP_DIDS1]',
						'[DEP_COSTINFO1]',
						'[DEP_COST1]',
						'[DEP_COSTPAYMENTINFO1]',
						'[DEP_COSTPAYMENT1]',
						'[DEP_PROCESSLIST1]' 
				), array (
						$old_deps [$old_dep]->actor,
						Format_Util::format_html ( $old_deps [$old_dep]->remark ),
						$this->get_upload_files ( $old_deps [$old_dep]->dids ),
						$this->_get_costinfo ( $old_deps [$old_dep]->costinfoids ),
						Format_Util::my_money_format ( '%.2n', $old_deps [$old_dep]->cost ),
						$this->_get_costpaymentinfo ( $old_deps [$old_dep]->costpaymentinfoids ),
						Format_Util::my_money_format ( '%.2n', $old_deps [$old_dep]->costpayment ),
						$this->_get_process_content ( intval ( $old_deps [$old_dep]->pcid ), intval ( $old_deps [$old_dep]->isok ), intval ( $old_deps [$old_dep]->step ), intval ( $old_dep ), $process, $process_dep ) 
				), $tmp );
				$depinfo .= $tmp;
			}
		}
		
		return $pidinfo_buf . $depinfo . $this->_get_invoice_info ( ( int ) ($datas ['new']->user) );
	}
	private function _getCYDataTable($executive_id, $dep = 0) {
		// $s = '<table border="0">';
		$s = '';
		$datas = array ();
		$results = $this->db->get_results ( 'SELECT b.supplier_name,c.category_name,d.media_short,a.*,e.industry_name
FROM
(
SELECT ym,cost_amount,quote_amount,finance_cost_amount,finance_quote_amount,supplier_id,supplier_short_id,category_id,industry_id FROM executive_cy WHERE executive_id=' . $executive_id . ($dep > 0 ? ' AND is_support=1 AND support_dep=' . $dep : ' AND is_support=0 AND support_dep=0') . ') a
LEFT JOIN new_supplier b
ON a.supplier_id=b.id
LEFT JOIN new_supplier_category c
ON a.category_id=c.id
LEFT JOIN finance_supplier_short d
ON a.supplier_short_id=d.id
LEFT JOIN new_supplier_industry e
ON a.industry_id=e.id AND a.supplier_short_id=e.supplier_short_id
ORDER BY b.supplier_name,c.category_name,a.ym' );
		if ($results !== NULL) {
			foreach ( $results as $result ) {
				$datas [$result->supplier_name] [(empty ( $result->media_short ) ? 'no' : $result->media_short)] [(empty ( $result->industry_name ) ? 'no' : $result->industry_name)] [(empty ( $result->category_name ) ? 'no' : $result->category_name)] [] = array (
						'ym' => $result->ym,
						'cost_amount' => $result->cost_amount 
				);
			}
		}
		
		if (! empty ( $datas )) {
			foreach ( $datas as $sn => $snv ) {
				// $s .= '<tr><td><b>供应商名称</b></td><td>' . $sn . '</td></tr>';
				$s .= '<b>供应商名称</b>：' . $sn . '</br>';
				foreach ( $snv as $ms => $msv ) {
					// $s .= '<tr><td><b>媒体</b></td><td>' . ($ms === 'no' ? '' : $ms)
					// . '</td></tr>';
					$s .= '<b>媒体</b>：' . ($ms === 'no' ? '' : $ms) . '</br>';
					foreach ( $msv as $im => $imv ) {
						// $s .= '<tr><td><b>客户行业分类</b></td><td>'
						// . ($im === 'no' ? '' : $im) . '</td></tr>';
						$s .= '<b>客户行业分类</b>：' . ($im === 'no' ? '' : $im) . '</br>';
						foreach ( $imv as $cn => $cnv ) {
							// $s .= '<tr><td><b>投放类型</b></td><td>'
							// . ($cn === 'no' ? '' : $cn) . '</td></tr>';
							$s .= '<b>投放类型</b>：' . ($cn === 'no' ? '' : $cn) . '</br>';
							// $s .= '<tr><td><b>时间</b></td><td><b>金额</b></td></tr>';
							$s .= '<b>时间</b>&nbsp;&nbsp;<b>金额</b></br>';
							foreach ( $cnv as $value ) {
								// $s .= '<tr><td>' . $value['ym'] . '</td><td><b><font color="#ff6600">'
								// . Format_Util::my_money_format('%.2n',
								// $value['cost_amount'])
								// . '</font></b></td></tr>';
								$s .= $value ['ym'] . '&nbsp;<b><font color="#ff6600">' . Format_Util::my_money_format ( '%.2n', $value ['cost_amount'] ) . '</font></b></br>';
							}
							// $s .= '<tr><td colspan="2">&nbsp;</td></tr>';
							$s .= '</br>';
						}
					}
				}
			}
		}
		// $s .= '</table>';
		return $s;
	}
	private function _getCYDatagridTable($executive_id, $dep = 0) {
		$s = '';
		
		$count = $this->db->get_var ( 'SELECT COUNT(*) FROM executive_cy WHERE executive_id=' . $executive_id . ($dep > 0 ? ' AND is_support=1 AND support_dep=' . $dep : ' AND is_support=0 AND support_dep=0') );
		
		if ($count > 0) {
			$yms = $this->db->get_results ( 'SELECT DISTINCT(ym) FROM executive_cy WHERE executive_id=' . $executive_id . ($dep > 0 ? ' AND is_support=1 AND support_dep=' . $dep : ' AND is_support=0 AND support_dep=0') . ' ORDER BY ym' );
			if ($yms !== NULL) {
				$s .= '<table class="easyui-datagrid" id="dg' . $executive_id . '_' . $dep . '" style="width:100%" data-options="autoRowHeight:true,striped:true,singleSelect:true,url:\'' . BASE_URL . 'get_data.php?action=getExecutiveCYByID&executive_id=' . $executive_id . '&dep=' . $dep . '\',showFooter:true">';
				$s .= '<thead>';
				$s .= '<tr>';
				$s .= '<th data-options="field:\'a\',width:200" rowspan="2">供应商名称</th><th data-options="field:\'b\',width:200" rowspan="2">媒体</th><th data-options="field:\'d\',width:150" rowspan="2">客户行业分类</th><th data-options="field:\'c\',width:150" rowspan="2">投放类型</th>';
				foreach ( $yms as $ym ) {
					// $s .= '<th colspan="'
					// . (in_array($this->getUsername(),
					// $GLOBALS['view_executive_finance_permission'],
					// TRUE) ? 4 : 2) . '">' . $ym->ym . '</th>';
					$s .= '<th>' . $ym->ym . '</th>';
				}
				$s .= '</tr>';
				$s .= '<tr>';
				foreach ( $yms as $ym ) {
					$s .= '<th data-options="field:\'cost_' . $ym->ym . '\',width:100,align:\'right\'">执行成本</th>';
					/*
					 * $s .= '<th data-options="field:\'quote_'
					 * . $ym->ym
					 * . '\',width:100,align:\'right\'">执行金额</th>';
					 * if (in_array($this->getUsername(),
					 * $GLOBALS['view_executive_finance_permission'],
					 * TRUE)) {
					 * $s .= '<th data-options="field:\'fcost_' . $ym->ym
					 * . '\',width:100,align:\'right\'">执行成本（财务）</th><th data-options="field:\'fquote_'
					 * . $ym->ym
					 * . '\',width:100,align:\'right\'">执行金额（财务）</th>';
					 * }
					 */
				}
				$s .= '</tr>';
				$s .= '</thead>';
				$s .= '</table>';
			}
		}
		return $s;
	}
	private function _get_amount_cy($executive_id, $is_quote = TRUE) {
		$s = '';
		if (! empty ( $executive_id )) {
			$results = $this->db->get_results ( 'SELECT payname,deliverytype,ym,' . ($is_quote ? 'quote_amount' : 'cost_amount') . ' AS amount,' . ($is_quote ? 'finance_quote_amount' : 'finance_cost_amount') . ' AS finance_amount FROM executive_cy WHERE executive_id=' . $executive_id . ' ORDER BY payname,year,month' );
			$arr = array ();
			if ($results !== NULL) {
				foreach ( $results as $result ) {
					$arr [$result->payname] [$result->deliverytype] [] = array (
							'ym' => $result->ym,
							'amount' => $result->amount,
							'finance_amount' => $result->finance_amount 
					);
				}
			}
			if (! empty ( $arr )) {
				$s = '<table class="sbd3" width="100%"><tr><td><b>供应商名称</b></td><td><b>投放类型</b></td><td><b>拆月</b></td><td><b>金额(元)</b></td>';
				if (in_array ( $this->getUsername (), $GLOBALS ['view_executive_finance_permission'], TRUE )) {
					$s .= '<td><b>金额(元)&nbsp;财务查看</b></td>';
				}
				$s .= '</tr>';
				$sum = 0;
				foreach ( $arr as $key => $value ) {
					foreach ( $value as $k => $v ) {
						$vvcount = count ( $v );
						foreach ( $v as $kk => $vv ) {
							$s .= '';
							$s .= '<tr>' . ($kk === 0 ? '<td rowspan="' . $vvcount . '">' . $key . '</td><td rowspan="' . $vvcount . '">' . $k . '</td>' : '') . '<td>' . $vv ['ym'] . '</td><td><font color="#ff9933">' . Format_Util::my_money_format ( '%.2n', $vv ['amount'] ) . '</font></td>';
							if (in_array ( $this->getUsername (), $GLOBALS ['view_executive_finance_permission'], TRUE )) {
								$s .= '<td><font color="red">' . Format_Util::my_money_format ( '%.2n', $vv ['finance_amount'] ) . '</font></td>';
							}
							$s .= '</tr>';
							$sum += $vv ['amount'];
						}
					}
				}
				$s .= '<tr><td colspan="2">&nbsp;</td><td><b>合计：</b></td><td' . (in_array ( $this->getUsername (), $GLOBALS ['view_executive_finance_permission'], TRUE ) ? ' colspan="2"' : '') . '><font color="#ff9933">' . Format_Util::my_money_format ( '%.2n', $sum ) . '</font></td></tr>';
				$s .= '</table>';
			}
		}
		return $s;
	}
	
	/*
	 * private function _get_amount_cy($costpaymentinfoids, $is_quote = TRUE)
	 * {
	 * $s = '';
	 * $costpaymentinfoids = explode('^', $costpaymentinfoids);
	 * $costpaymentinfoids = Array_Util::my_remove_array_other_value($costpaymentinfoids, array(
	 * ''
	 * ));
	 * if (!empty($costpaymentinfoids)) {
	 * $results = $this->db->get_results('SELECT payname,deliverytype,ym,' . ($is_quote ? 'quote_amount' : 'cost_amount') . ' AS amount,' . ($is_quote ? 'finance_quote_amount' : 'finance_cost_amount') . ' AS finance_amount FROM executive_cy WHERE paycost_id IN (' . implode(',', $costpaymentinfoids) . ') ORDER BY payname,year,month');
	 * $arr = array();
	 * if ($results !== NULL) {
	 * foreach ($results as $result) {
	 * $arr[$result->payname][$result->deliverytype][] = array(
	 * 'ym' => $result->ym,
	 * 'amount' => $result->amount,
	 * 'finance_amount' => $result->finance_amount
	 * );
	 * }
	 * }
	 * if (!empty($arr)) {
	 * $s = '<table class="sbd3" width="100%"><tr><td><b>供应商名称</b></td><td><b>投放类型</b></td><td><b>拆月</b></td><td><b>金额(元)</b></td>';
	 * if (in_array($this->getUsername(), $GLOBALS['view_executive_finance_permission'], TRUE)) {
	 * $s .= '<td><b>金额(元)&nbsp;财务查看</b></td>';
	 * }
	 * $s .= '</tr>';
	 * $sum = 0;
	 * foreach ($arr as $key => $value) {
	 * foreach ($value as $k => $v) {
	 * $vvcount = count($v);
	 * foreach ($v as $kk => $vv) {
	 * $s .= '';
	 * $s .= '<tr>' . ($kk === 0 ? '<td rowspan="' . $vvcount . '">' . $key . '</td><td rowspan="' . $vvcount . '">' . $k . '</td>' : '') . '<td>' . $vv['ym'] . '</td><td><font color="#ff9933">' . Format_Util::my_money_format('%.2n', $vv['amount']) . '</font></td>';
	 * if (in_array($this->getUsername(), $GLOBALS['view_executive_finance_permission'], TRUE)) {
	 * $s .= '<td><font color="red">' . Format_Util::my_money_format('%.2n', $vv['finance_amount']) . '</font></td>';
	 * }
	 * $s .= '</tr>';
	 * $sum += $vv['amount'];
	 * }
	 * }
	 * }
	 * $s .= '<tr><td colspan="2">&nbsp;</td><td><b>合计：</b></td><td' . (in_array($this->getUsername(), $GLOBALS['view_executive_finance_permission'], TRUE) ? ' colspan="2"' : '') . '><font color="#ff9933">' . Format_Util::my_money_format('%.2n', $sum) . '</font></td></tr>';
	 * $s .= '</table>';
	 * }
	 * }
	 * return $s;
	 * }
	 */
	private function _get_invoice_info($uid) {
		$s = '';
		if ($this->getUid () === $uid || $this->getHas_check_executive_permission () || in_array ( $this->getUsername (), $GLOBALS ['manager_finance_permission'], TRUE ) || intval ( $this->getBelong_dep () ) === 2 || $this->get_relation_executive_permission ( intval ( $this->getBelong_city () ), intval ( $this->getBelong_dep () ), intval ( $this->getBelong_team () ) ) > 0) {
			$s .= '<br/><table width="100%" cellpadding="0" cellspacing="0" border="0" class="sbd2"><tr><td width="33%"><strong>开票日期</strong></td><td width="33%"><strong>发票号码</strong></td><td><strong>开票金额</strong></td></tr>';
			$results = $this->db->get_results ( 'SELECT a.amount,b.number,b.date FROM finance_invoice a LEFT JOIN finance_invoice_list b ON a.invoice_list_id=b.id WHERE a.pid="' . $this->pid . '" AND b.isok=1 AND b.print=1' );
			if ($results !== NULL) {
				foreach ( $results as $result ) {
					$s .= '<tr><td>' . $result->date . '</td><td>' . $result->number . '</td><td><font color="#ff9933"><b>' . Format_Util::my_money_format ( '%.2n', $result->amount ) . '</b></font></td></tr>';
				}
			} else {
				$s .= '<tr><td colspan="3"><font color="red">无开票记录！</font></td></tr>';
			}
			$s .= '</table>';
		}
		return $s;
	}
	private function _get_receivables_info() {
		$s = '';
		if ($this->getHas_check_executive_permission () || in_array ( $this->getUsername (), $GLOBALS ['manager_finance_permission'], TRUE ) || intval ( $this->getBelong_dep () ) === 2 || $this->get_relation_executive_permission ( intval ( $this->getBelong_city () ), intval ( $this->getBelong_dep () ), intval ( $this->getBelong_team () ) ) > 0) {
			$s .= '<br/><table width="100%" cellpadding="0" cellspacing="0" border="0" class="sbd2"><tr><td width="33%"><strong>收款日期</strong></td><td width="33%"><strong>付款人名称</strong></td><td><strong>收款金额</strong></td></tr>';
			$results = $this->db->get_results ( 'SELECT a.amount,b.payer,b.date FROM finance_receivables a LEFT JOIN finance_receivables_list b ON a.receivables_list=b.id WHERE a.pid="' . $this->pid . '" AND b.isok=1' );
			if ($results !== NULL) {
				foreach ( $results as $result ) {
					$s .= '<tr><td>' . $result->date . '</td><td>' . $result->payer . '</td><td><font color="#ff9933"><b>' . Format_Util::my_money_format ( '%.2n', $result->amount ) . '</b></font></td></tr>';
				}
			} else {
				$s .= '<tr><td colspan="3"><font color="red">无收款记录！</font></td></tr>';
			}
			$s .= '</table>';
		}
		return $s;
	}
	private static function _generate_search_field($search) {
		return '[' . $search . ']';
	}
	private static function _get_executive_typename($typeid) {
		switch ($typeid) {
			case 1 :
				return '普通';
				break;
			case 2 :
				return '预充值';
				break;
			case 3 :
				return '结算';
				break;
			default :
				return '';
		}
	}
	private static function _get_contract_url($cid, $contractname, $ismobile = FALSE) {
		if ($ismobile) {
			return $contractname;
		}
		return '<a href="' . BASE_URL . 'contract_cus/?o=info&cid=' . $cid . '">' . $contractname . '</a>';
	}
	public static function get_companyname($id) {
		switch ($id) {
			case 1 :
				return '网迈广告';
				break;
			case 2 :
				return '网迈文化';
				break;
			default :
				return '新网迈';
		}
	}
	private static function _get_user($realname, $username) {
		$s = '';
		if (! empty ( $realname )) {
			$s .= $realname;
		}
		if (! empty ( $username )) {
			// if (!empty($realname)) {
			// $s .= ' ';
			// }
			$s .= '(' . $username . ')';
		}
		return $s;
	}
	private function _get_amount_cy_info($id, $pid) {
		$s = '';
		$results = $this->db->get_results ( 'SELECT ym,quote_amount FROM executive_amount_cy WHERE executive_id=' . $id . ' AND pid="' . $pid . '"' );
		if ($results !== NULL) {
			foreach ( $results as $result ) {
				$s .= sprintf ( '<div>时间：%s  金额：<span style="color:#ff6600">%s</span></div>', $result->ym, Format_Util::my_money_format ( '%.2n', $result->quote_amount ) );
			}
		}
		return $s;
	}
	private function _get_paytimeinfo($paytimeinfoids, $cid, $time, $ismobile = FALSE) {
		$paytimeinfoids = explode ( '^', $paytimeinfoids );
		$result = 1;
		// 以2012年12月17日为分界线
		if ($time < 1355673600) {
			$result = 0;
		}
		$s = $ismobile ? array () : '';
		
		foreach ( $paytimeinfoids as $paytimeinfoid ) {
			if (intval ( $paytimeinfoid ) > 0) {
				$row = $this->db->get_row ( 'SELECT paytime,amount,billtype,remark FROM executive_paytime WHERE id=' . intval ( $paytimeinfoid ) );
				if ($row !== NULL) {
					if ($result === 0) {
						$tmps = intval ( $row->billtype ) === 1 ? '广告' : '服务';
					} else {
						$bt = $this->db->get_row ( 'SELECT billtype FROM contract_cus WHERE cid="' . $cid . '"' );
						if ($bt === NULL) {
							$tmps = '广告';
						} else {
							if (intval ( $bt->billtype ) === 2) {
								$tmps = '服务';
							} else {
								$tmps = '广告';
							}
						}
					}
				}
			}
			if ($ismobile) {
				$s [] = array (
						'date' => empty ( $row->paytime ) ? '' : $row->paytime,
						'amount' => Format_Util::my_money_format ( '%.2n', $row->amount ),
						'invoice_type' => urlencode ( $tmps ),
						'remark' => urlencode ( $row->remark ) 
				);
			} else {
				$s .= sprintf ( '<div>日期：%s  金额：<span style="color:#ff6600">%s</span> 发票类型：【%s】 备注：【%s】</div>', $row->paytime, Format_Util::my_money_format ( '%.2n', $row->amount ), $tmps, $row->remark );
			}
		}
		return $s;
	}
	private function _get_servicecf_info($amount, $user, $principal, $servicecf) {
		if (in_array ( $this->getUid (), array (
				$user,
				$principal 
		), TRUE ) || in_array ( 'sys172', $this->getPermissions () )) {
			if (empty ( $servicecf )) {
				return '';
			} else {
				$allamount = 0;
				$s = '';
				$servicecf = explode ( '|', $servicecf );
				foreach ( $servicecf as $scf ) {
					$scf = explode ( '^', $scf );
					$s .= '<div><span style="display:inline-block; width:110px">' . (empty($GLOBALS ['defined_servicecf_type'] [$scf [0]]) ? '无' : $GLOBALS ['defined_servicecf_type'] [$scf [0]]) . '</span><span>' . Format_Util::my_money_format ( '%.2n', $scf [1] ) . ' 元</span> &nbsp;&nbsp; 备注：【' . $scf [2] . '】</div>';
					$allamount += $scf [1];
				}
				$tax = $amount - $allamount;
				$s = '<div>税前合计： <font color="#ff9933"><b>' . Format_Util::my_money_format ( '%.2n', $allamount ) . '</b></font> 元 &nbsp;&nbsp;  税费：<font color="#0033CC"><b>' . Format_Util::my_money_format ( '%.2n', $tax ) . '</b></font> 元</div> ' . $s;
				return $s;
			}
		} else {
			return '<font color="#FF0000">您当前没有权限查阅！</font>';
		}
	}
	private function _get_costinfo($costinfoids, $ismobile = FALSE) {
		if (empty ( $costinfoids )) {
			return $ismobile ? array () : '';
		}
		$s = $ismobile ? array () : '';
		$costinfoids = explode ( '^', $costinfoids );
		$i = 0;
		foreach ( $costinfoids as $costinfoid ) {
			if (intval ( $costinfoid ) > 0) {
				$row = $this->db->get_row ( 'SELECT type,name,amount,yg,id FROM executive_costinfo WHERE id=' . intval ( $costinfoid ) );
				if ($row !== NULL) {
					$i ++;
					$tmps = intval ( $row->yg ) === 0 ? '' : ($ismobile ? '预估' : '<font color="#0000FF">预估</font>');
					$tmps1 = $GLOBALS ['defined_executive_cost_type'] [strval ( $row->type )];
					if ($tmps1 === NULL) {
						$tmps1 = '';
					}
					if ($ismobile) {
						$s [] = array (
								'cost_type' => urlencode ( $tmps1 ),
								'supplier_name' => urlencode ( $row->name ),
								'cost_amount' => Format_Util::my_money_format ( '%.2n', $row->amount ),
								'is_yg' => urlencode ( $tmps ) 
						);
					} else {
						$s .= sprintf ( '<div>%d. 成本类型：%s 收款方名称：%s 成本金额：<span style="color:#ff6600">%s</span>  %s</div>', $i, $tmps1, $row->name, Format_Util::my_money_format ( '%.2n', $row->amount ), $tmps );
					}
				}
			}
		}
		return $s;
	}
	private function _get_costpaymentinfo($costpaymentinfoids, $ismobile = FALSE) {
		if (empty ( $costpaymentinfoids )) {
			return $ismobile ? array () : '';
		}
		$s = $ismobile ? array () : '';
		$costpaymentinfoids = explode ( '^', $costpaymentinfoids );
		$i = 0;
		foreach ( $costpaymentinfoids as $costpaymentinfoid ) {
			if (intval ( $costpaymentinfoid ) > 0) {
				$row = $this->db->get_row ( 'SELECT payname,paytime,payamount,paytype,id,costinfoid FROM executive_paycost WHERE id=' . intval ( $costpaymentinfoid ) );
				if ($row !== NULL) {
					$i ++;
					$tmps = intval ( $row->paytype ) === 1 ? '广告' : '服务';
					if ($ismobile) {
						$s [] = array (
								'supplier_name' => urlencode ( $row->payname ),
								'paytime' => $row->paytime,
								'invoice_type' => urlencode ( $tmps ),
								'amount' => Format_Util::my_money_format ( '%.2n', $row->payamount ) 
						);
					} else {
						$s .= sprintf ( '<div>%d. 收款方名称：%s 收款时间：%s 发票类型：【%s】 金额：<span style="color:#ff6600">%s</span></div>', $i, $row->payname, $row->paytime, $tmps, Format_Util::my_money_format ( '%.2n', $row->payamount ) );
					}
				}
			}
		}
		return $s;
	}
	private function _get_support_dep($dep, $support, $ismobile = FALSE) {
		if (empty ( $support )) {
			return '';
		}
		
		// $s = '';
		$s = array ();
		$support = explode ( '|', $support );
		foreach ( $support as $sp ) {
			$sp = explode ( '^', $sp );
			if ($ismobile) {
				$s [] = urlencode ( $this->get_depname ( $dep [$sp [0]] [1], $dep [$sp [0]] [0] ) );
			} else {
				$s [] = $this->get_depname ( $dep [$sp [0]] [1], $dep [$sp [0]] [0] );
			}
		}
		return $ismobile ? $s : implode ( '&nbsp;&nbsp;', $s );
	}
	private static function _get_process_content($pcid, $isok, $step, $dep, $process, $process_dep, $ismobile = FALSE) {
		if ($pcid === 0) {
			return $ismobile ? array (
					array (
							'p_status' => 'undone',
							'p_name' => urlencode ( '未选择流程' ) 
					) 
			) : '<font color="red">未选择流程</font>';
		} elseif ($isok === 1) {
			return $ismobile ? array (
					array (
							'p_status' => 'done',
							'p_name' => urlencode ( '已完成' ) 
					) 
			) : '<font color="green">已完成</font>';
		} else if ($step < 0) {
			return $ismobile ? array (
					array (
							'p_status' => 'undone',
							'p_name' => urlencode ( '未知流程' ) 
					) 
			) : '';
		}
		
		$list = array ();
		$row = $dep === 0 ? $process ['step'] [$pcid] : $process_dep [$pcid];
		if ($isok === 1) {
			foreach ( $row as $r ) {
				if ($ismobile) {
					$list [] = array (
							'p_status' => 'done',
							'p_name' => urlencode ( $dep === 0 ? $r ['content'] [0] : $r [0] ) 
					);
				} else {
					$list [] = $dep === 0 ? '<font color="green">' . $r ['content'] [0] . '</font>' : '<font color="green">' . $r [0] . '</font>';
				}
			}
		} else {
			foreach ( $row as $key => $r ) {
				if ($ismobile) {
					$list [] = array (
							'p_status' => $key < $step ? 'done' : 'undone',
							'p_name' => urlencode ( $dep === 0 ? $r ['content'] [0] : $r [0] ) 
					);
				} else {
					$list [] = $dep === 0 ? '<font color="' . ($key < $step ? 'green' : 'red') . '">' . $r ['content'] [0] . '</font>' : '<font color="' . ($key < $step ? 'green' : 'red') . '">' . $r [0] . '</font>';
				}
			}
		}
		return $ismobile ? $list : implode ( ' -> ', $list );
	}
	private static function _get_executive_name_link($isalter, $id, $pid, $name, $contrast) {
		$s = $isalter > 0 ? sprintf ( '%s &nbsp;&nbsp;<font color="#cc6600">【变更%d】</font> &nbsp;&nbsp;', $name, $isalter ) : $name;
		if ($isalter > 0 && ! $contrast) {
			$s .= '<a href="' . BASE_URL . 'executive/?o=info&id=' . $id . '&pid=' . $pid . '&d=1">对比</a>';
		}
		return $s;
	}
	public function get_log_list() {
		$s = '';
		if ($this->pid !== NULL) {
			$results = $this->db->get_results ( 'SELECT FROM_UNIXTIME(a.time) AS tt,a.auditname,b.realname,a.type,a.time,a.content FROM executive_log a LEFT JOIN users b on b.uid=a.uid where a.pid="' . $this->pid . '" ORDER BY a.time' );
			if ($results !== NULL) {
				$nowtime = NULL;
				foreach ( $results as $key => $result ) {
					$s = '<tr><td>' . $result->tt . '</td><td>' . $result->auditname . '</td><td>' . $result->realname . '</td><td>' . $result->type . '</td><td>' . self::_get_waiting_time ( ($key === 0 ? 0 : $result->time - ($nowtime === NULL ? 0 : $nowtime)) ) . '</td><td>' . $result->content . '</td></tr>' . $s;
					$nowtime = $result->time;
				}
			} else {
				$s = '<tr><td colspan="6"><font color="red">没有对应的流转状态！</font></td></tr>';
			}
		}
		$buf = file_get_contents ( TEMPLATE_PATH . 'executive/loglist.tpl' );
		return str_replace ( '[LOGS]', $s, $buf );
	}
	private static function _get_waiting_time($timediff) {
		$days = intval ( $timediff / 86400 );
		$remain = $timediff % 86400;
		$hours = intval ( $remain / 3600 );
		$remain = $remain % 3600;
		$mins = intval ( $remain / 60 );
		$secs = $remain % 60;
		
		if ($secs == 0) {
			return '0 秒';
		} else if ($mins == 0) {
			return sprintf ( '%s秒', $secs );
		} else if ($hours == 0) {
			return sprintf ( '%s分%s秒', $mins, $secs );
		} else if ($days == 0) {
			return sprintf ( '%s小时%s分%s秒', $hours, $mins, $secs );
		} else {
			return sprintf ( '%s天%s小时%s分%s秒', $days, $hours, $mins, $secs );
		}
	}
	private function can_cancel($id) {
		// 如果有发票金额>0就无法撤销
		// 2014.5.22+如果是变更的版本撤销，无需校验发票金额，原始执行单，必须校验开票金额
		$row = $this->db->get_row ( 'SELECT pid,isalter FROM executive WHERE id=' . $id );
		if ($row !== NULL) {
			$isalter = intval ( $row->isalter );
			if ($isalter === 0) {
				$sum = new Invoice ();
				$amount = $sum->getSumPidInvoice ( $row->pid );
				unset ( $sum );
				if ($amount > 0) {
					return FALSE;
				} else {
					return TRUE;
				}
			} else {
				return TRUE;
			}
		} else {
			return FALSE;
		}
	}
	public function gd_executive() {
		if (! $this->getHas_manager_executive_permission ()) {
			return array (
					'status' => 'error',
					'message' => NO_RIGHT_TO_DO_THIS 
			);
		}
		if ($this->validate_form_value ( 'gd' )) {
			if ($this->gd) {
				// 归档
				$query = 'UPDATE executive SET gdtime=' . time () . ' WHERE id=' . intval ( $this->executive_id );
			} else {
				// 撤销
				if (! $this->can_cancel ( intval ( $this->executive_id ) )) {
					return array (
							'status' => 'error',
							'message' => '该执行单不存在或该执行单开票金额>0，无法撤销' 
					);
				} else {
					$query = 'UPDATE executive SET isok=-1 WHERE id=' . intval ( $this->executive_id );
				}
			}
			$update_result = $this->db->query ( $query );
			if ($update_result === FALSE) {
				return array (
						'status' => 'error',
						'message' => ($this->gd ? '归档失败' : '撤销归档失败') 
				);
			}
			return array (
					'status' => 'success',
					'message' => ($this->gd ? '归档成功' : '撤销归档成功') 
			);
		}
		return array (
				'status' => 'error',
				'message' => $this->errors 
		);
	}
	public function get_executive_reject_step($iscy = FALSE) {
		$executive_records = $this->executive_records;
		$executive_records = $executive_records ['new'];
		$process = Process::getInstance ();
		$dep = Dep::getInstance ();
		
		$process_content = $process ['step'] [$executive_records->pcid];
		$DEP_step = Process::get_DEP_key ( $process_content );
		$tmps = $iscy ? '' : '<input name="rejectstep" type="radio" value="0" checked="checked"/>驳回至发起人 &nbsp;&nbsp;&nbsp;&nbsp;';
		if ($DEP_step !== NULL && $DEP_step < intval ( $executive_records->step ) && ! empty ( $executive_records->support )) {
			$tmps1 = '';
			$support = $executive_records->support;
			$support = explode ( '|', $support );
			foreach ( $support as $s ) {
				$s = explode ( '^', $s );
				$depid = reset ( $s );
				$tmps1 .= sprintf ( '<input name="rejectdep[]" type="checkbox" value="%s"/> %s &nbsp;&nbsp;', $depid, $this->get_depname ( $dep [$depid] [1], $dep [$depid] [0] ) );
			}
			$tmps .= '<input  name="rejectstep" type="radio" value="DEP"' . ($iscy ? ' checked="checked"' : '') . '/>驳回至支持部门 &nbsp;&nbsp;';
			if ($tmps1 !== '') {
				$tmps .= sprintf ( '<span id="rejectdeplist">【 %s 】</span>', $tmps1 );
			}
		}
		return $tmps;
	}
	public function audit_executive() {
		$row = $this->db->get_row ( 'SELECT a.step,a.pcid,b.content,a.isalter,a.principal FROM executive a,process b WHERE a.id=' . intval ( $this->executive_id ) . ' AND a.pcid=b.id AND a.isok<>-1 AND b.islive=1' );
		if ($row === NULL) {
			return array (
					'status' => 'error',
					'message' => '没有符合条件的记录' 
			);
		}
		$content = explode ( '_', $row->content );
		$content = explode ( '^', $content [$row->step] );
		
		if (! in_array ( $content [2], $this->getPermissions (), TRUE )) {
			if ($content [0] === '项目负责人') {
				if (intval ( $this->getUid () ) !== intval ( $row->principal )) {
					return array (
							'status' => 'error',
							'message' => NO_RIGHT_TO_DO_THIS 
					);
				}
			} else {
				if ($content [2] !== 'DEP') {
					return array (
							'status' => 'error',
							'message' => NO_RIGHT_TO_DO_THIS 
					);
				}
			}
		}
		
		if ($this->validate_form_value ( 'audit' )) {
			$success = TRUE;
			$error = '';
			$process = Process::getInstance ();
			
			$this->_get_executive_records ();
			$datas = $this->executive_records;
			$datas = $datas ['new'];
			
			$this->db->query ( 'BEGIN' );
			
			if (( int ) ($datas->is_closed) === 1) {
				$success = FALSE;
				$error = '执行单已关闭，无法审核，详情请联系财务部';
			} else {
				$process = $process ['step'] [$datas->pcid];
				
				if (intval ( $this->audit_pass ) === 1) {
					// 审核确认
					$log_result = $this->do_executive_log ( $datas->pid, $this->remark, $process [$datas->step] ['content'] [0], '<font color=\'#66cc00\'>审核确认</font>' );
					if ($log_result ['status'] === 'success') {
						$msgid = $log_result ['msgid'];
					} else {
						$success = FALSE;
						$error = '审核执行单记录日志操作失败';
					}
					
					if ($success) {
						$update_result = $this->db->query ( 'UPDATE executive SET audittime=' . time () . ' WHERE id=' . intval ( $datas->id ) );
						if ($update_result === FALSE) {
							$success = FALSE;
							$error = '审核执行单记录时间操作失败';
						} else {
							// 所有步骤已经完成
							if ((intval ( $datas->step ) + 1) === count ( $process )) {
								$query = 'UPDATE executive SET step=step+1,isok=1,msgid=' . $msgid . ',oktime=' . time () . ' WHERE id=' . intval ( $datas->id );
							} else if ($process [intval ( $datas->step ) + 1] ['content'] [2] === 'DEP' && (empty ( $datas->support ) || $this->check_is_all_dep_ok ( $datas->support ))) {
								$query = 'UPDATE executive SET step=step+2,msgid=' . $msgid . ' WHERE id=' . intval ( $datas->id );
							} else {
								$query = 'UPDATE executive SET step=step+1,msgid=' . $msgid . ' WHERE id=' . intval ( $datas->id );
							}
							$update_result = $this->db->query ( $query );
							if ($update_result === FALSE) {
								$success = FALSE;
								$error = '审核执行单更新状态操作失败';
							}
						}
					}
				} else {
					// 审核驳回
					$DEP_step = Process::get_DEP_key ( $process );
					
					$reject_type = '<font color=\'#ff9900\'>驳回 至 执行单发起人</font>';
					if ($this->rejectstep === 'DEP') {
						$reject_type = '<font color=\'#ff9900\'>驳回 至 支持部门</font>';
					}
					
					$log_result = $this->do_executive_log ( $datas->pid, $this->remark, $process [$datas->step] ['content'] [0], $reject_type );
					if ($log_result ['status'] === 'success') {
						$msgid = $log_result ['msgid'];
					} else {
						$success = FALSE;
						$error = '审核执行单记录日志操作失败';
					}
					
					if ($success) {
						if ($this->rejectstep === 'DEP') {
							$query = 'UPDATE executive SET audittime=' . time () . ',step=' . $DEP_step . ',msgid=' . $msgid . ' WHERE id=' . intval ( $datas->id );
						} else {
							$query = 'UPDATE executive SET audittime=' . time () . ',step=0,msgid=' . $msgid . ' WHERE id=' . intval ( $datas->id );
						}
						
						$update_result = $this->db->query ( $query );
						if ($update_result === FALSE) {
							$success = FALSE;
							$error = '审核执行单状态操作失败';
						}
						
						if ($success) {
							if ($this->rejectstep === 'DEP') {
								$rejectdepids = $this->rejectdepids;
								
								if (! empty ( $datas->support )) {
									$support = explode ( '|', $datas->support );
									foreach ( $support as $s ) {
										$s = explode ( '^', $s );
										if (in_array ( reset ( $s ), $rejectdepids )) {
											$update_result = $this->db->query ( 'UPDATE executive_dep SET step=0,isok=0,msgid=' . $msgid . ' WHERE id=' . end ( $s ) );
											if ($update_result === FALSE) {
												$success = FALSE;
												$error = '更改驳回的支持部门状态操作失败';
												break;
											}
										}
									}
								}
							} else {
								if ($DEP_step != 0 && intval ( $datas->step ) >= $DEP_step) {
									if (! empty ( $datas->support )) {
										$support = explode ( '|', $datas->support );
										foreach ( $support as $s ) {
											$s = explode ( '^', $s );
											$update_result = $this->db->query ( 'UPDATE executive_dep SET step=0,isok=0,msgid=' . $msgid . ' WHERE id=' . end ( $s ) );
											if ($update_result === FALSE) {
												$success = FALSE;
												$error = '更改驳回的支持部门状态操作失败';
												break;
											}
										}
									}
								}
							}
						}
					}
				}
			}
			
			if ($success) {
				$this->db->query ( 'COMMIT' );
			} else {
				$this->db->query ( 'ROLLBACK' );
			}
			
			return array (
					'status' => $success ? 'success' : 'error',
					'message' => $success ? '执行单审核' . (intval ( $this->audit_pass ) === 1 ? '确认' : '驳回') . '成功' : $error 
			);
		}
		return array (
				'status' => 'error',
				'message' => $this->errors 
		);
	}
	public function check_is_all_dep_ok($support) {
		$result = 0;
		$_supports = explode ( '|', $support );
		foreach ( $_supports as $_support ) {
			$depinfoid = end ( explode ( '^', $_support ) );
			$row = $this->db->get_row ( 'SELECT isok FROM executive_dep WHERE id=' . intval ( $depinfoid ) );
			if ($row !== NULL) {
				$result += intval ( $row->isok );
			}
		}
		return $result === count ( $_supports );
	}
	private function _get_dep_audit($exexid, $depid) {
		$has_role = FALSE;
		// 支持部门审核步骤
		$support = $this->db->get_var ( 'SELECT support FROM executive WHERE id=' . $exexid . ' AND isok=0' );
		
		$dep_step = 0;
		$dep_pcid = 0;
		if (! empty ( $support )) {
			$support = explode ( '|', $support );
			foreach ( $support as $su ) {
				$su = explode ( '^', $su );
				// var_dump((int)($su[0]));
				// var_dump((int)$depid);
				if (( int ) ($su [0]) === ( int ) $depid) {
					$row = $this->db->get_row ( 'SELECT step,pcid FROM executive_dep WHERE id=' . $su [1] . ' AND isok=0' );
					// var_dump('SELECT step,pcid FROM executive_dep WHERE id=' . $su[1] . ' AND isok=0');
					if ($row !== NULL) {
						$dep_step = ( int ) ($row->step);
						$dep_pcid = ( int ) ($row->pcid);
					}
					break;
				}
			}
		}
		// var_dump($dep_step);
		// var_dump($dep_pcid);
		// 支持部门审核流程
		
		// if($dep_pcid > 0){
		$dep_process = Dep_Process::getInstance ();
		$dep_role = Permission_Dep::getInstance ();
		/*
		 * if($this->executive_id === '21755'){
		 * var_dump($dep_step);
		 * var_dump($dep_pcid);
		 * var_dump($dep_process[$dep_pcid][$dep_step][2]);
		 * var_dump(in_array($dep_process[$dep_pcid][$dep_step][2],$this->getPermissions(),TRUE));
		 * }
		 */
		
		if ($dep_step === 0 && in_array ( User::_get_dep_tf_id ( $dep_role [$depid] ), $this->getPermissions (), TRUE ) || in_array ( $dep_process [$dep_pcid] [$dep_step] [2], $this->getPermissions (), TRUE )) {
			$has_role = TRUE;
		}
		// }
		return $has_role;
	}
	
	// TODO
	public function get_executive_dep_audit() {
		/*
		 * if (intval($this->dep) !== intval($this->getBelong_dep())) {
		 * $dep_role = Permission_Dep::getInstance();
		 * $hasright = FALSE;
		 * $dephasroles = $dep_role[intval($this->dep)];
		 * foreach ($dephasroles as $dephasrole) {
		 * if (in_array('dep' . $dephasrole['permission_id'],
		 * $this->getPermissions(), TRUE)) {
		 * $hasright = TRUE;
		 * break;
		 * }
		 * }
		 * if (!$hasright) {
		 * return User::no_permission();
		 * }
		 * }
		 */
		$has_role = $this->_get_dep_audit ( $this->executive_id, $this->dep );
		if (! $has_role) {
			return User::no_permission ();
		}
		$this->_get_executive_records ();
		$datas = $this->executive_records;
		$data_new = $datas ['new'];
		$support = explode ( '|', $data_new->support );
		
		$my_dep = Dep::getInstance ();
		
		$depinfoid = 0;
		foreach ( $support as $s ) {
			$s = explode ( '^', $s );
			if (intval ( $this->dep ) === intval ( $s [0] )) {
				$depinfoid = intval ( $s [1] );
			}
		}
		
		if ($depinfoid !== 0) {
			$row = $this->db->get_row ( 'SELECT * FROM executive_dep WHERE id=' . $depinfoid );
			if (intval ( $row->step ) === 0) {
				$my_dep = $my_dep [$this->dep];
				$_dep = new Dep ( $this->dep );
				$_dep_process = new Dep_Process ( NULL, array (
						'dep' => $this->dep 
				) );
				$executive_log = new Executive_log ( $row->msgid );
				$msg = $executive_log->getMessage ();
				
				$did_value = $row->dids;
				if (! empty ( $did_value )) {
					$did_value = '^' . $did_value . '^';
				} else {
					$did_value = '^';
				}
				
				// 检查执行单外包类型
				$outsourcing_type_id = $this->db->get_var ( 'SELECT outsourcing_type_id FROM outsourcing_pid_type WHERE executive_id=' . $data_new->id . ' AND pid="' . $this->pid . '" AND support_id=' . intval ( $this->dep ) );
				
				$buf = file_get_contents ( TEMPLATE_PATH . 'executive/executive_dep_edit.tpl' );
				$buf = str_replace ( array (
						'[DEPNAME]',
						'[ACTORLIST]',
						'[DEP_ACTOR]',
						'[DEP_REMARK]',
						'[DEP_DIDS]',
						'[DEP_COSTINFO]',
						'[DEP_COST]',
						'[DEP_COSTPAYMENTINFO]',
						'[DEP_COSTPAYMENT]',
						'[DEP_PROCESSLIST]',
						'[MSG]',
						'[NONE]',
						'[VALIDATE_TYPE]',
						'[VALIDATE_SIZE]',
						'[EXECUTIVEID]',
						'[DIDSVALUE]',
						'[CYOVERLAY]',
						'[SUPPLIERS]',
						'[CYON]',
						'[VCODE]',
						'[ACTOR_SHOW]',
						'[SUPPLIERCATEGORYS]',
						'[EXESOURCINGTYPESELECT]',
						'[SUPPLIERSHORTS]',
						'[SUPPLIERINDUSTRYS]' 
				), array (
						$this->get_depname ( $my_dep [1], $my_dep [0] ),
						$_dep->get_users_select_html_by_dep (),
						$row->actor,
						$row->remark,
						self::get_upload_files ( $row->dids, TRUE ),
						$this->_get_edit_costinfo ( $row->costinfoids ),
						$row->cost,
						$this->_get_edit_costpayment_info ( $datas ['new']->id, $row->costpaymentinfoids, intval ( $this->dep ) ),
						$row->costpayment,
						$_dep_process->get_dep_process_html_by_dep (),
						$msg,
						$msg === '' ? 'none' : '',
						implode ( ',', $GLOBALS ['defined_upload_validate_type'] ),
						UPLOAD_FILE_MAX_SIZE / (1024 * 1024),
						$this->executive_id,
						$did_value,
						$this->_get_cy_overlay ( $datas ['new']->id, $this->pid, $datas ['new']->isok, $datas ['new']->isalter, intval ( $this->dep ) ),
						$this->_getCYSuppliers ( $datas ['new']->id, intval ( $this->dep ) ),
						(CY_ON ? 1 : 0),
						$this->get_vcode (),
						self::_get_actor_show ( $row->actor ),
						Supplier::getCategorysSelect (),
						Outsourcing_Type::getOutsourcingTypeSelect ( $outsourcing_type_id ),
						Supplier_Short::getSupplierShortSelect (),
						Supplier_Short::getIndustrySelect () 
				), $buf );
				if (! empty ( $row->actor )) {
					$buf .= '<script>actor_array = ["' . implode ( '","', explode ( ',', $row->actor ) ) . '"];</script>';
				}
			} else {
				$buf = file_get_contents ( TEMPLATE_PATH . 'executive/executive_dep_audit.tpl' );
			}
			$buf = str_replace ( array (
					'[PIDINFO]',
					'[LEFT]',
					'[TOP]',
					'[LOGLIST]',
					'[VCODE]',
					'[PID]',
					'[DEP]',
					'[CHECKDIFFERENT]',
					'[EXECUTIVEID]',
					'[BASE_URL]' 
			), array (
					$this->get_executive_info ( $datas, $this->dep ),
					$this->get_left_html (),
					$this->get_top_html (),
					$this->get_log_list (),
					$this->get_vcode (),
					$data_new->pid,
					intval ( $this->dep ),
					(intval ( $datas ['new']->id ) !== intval ( $datas ['old']->id ) ? 'checkdifferent();' : ''),
					$this->executive_id,
					BASE_URL 
			), $buf );
		}
		return $buf;
	}
	public function dep_edit_executive() {
		if ($this->validate_form_value ( 'dep_edit' )) {
			$success = TRUE;
			$error = '';
			$this->db->query ( 'BEGIN' );
			
			$results = $this->db->get_results ( 'SELECT id,cost,support,is_closed,isalter,starttime,endtime FROM executive WHERE pid="' . $this->pid . '" ORDER BY id' );
			if ($results === NULL) {
				$success = FALSE;
				$error = '执行单号有误';
			} else {
				$row = $results [count ( $results ) - 1];
				
				if (( int ) ($row->is_closed) === 1) {
					$success = FALSE;
					$error = '执行单已关闭，无法填写，详情请联系财务部';
				} else {
					$support = explode ( '|', $row->support );
					$depinfoid = 0;
					
					foreach ( $support as $s ) {
						$s = explode ( '^', $s );
						if (intval ( $this->dep ) === intval ( $s [0] )) {
							$depinfoid = intval ( $s [1] );
							break;
						}
					}
					
					// $finance_cy = $this
					// ->_get_finance_cy($row->id, TRUE,
					// intval($this->dep));
					
					// 成本明细
					$costcount_array = $this->costcount_array;
					$costcount_ids = array ();
					$costcount_amount = 0;
					$yg = 0;
					if (! empty ( $costcount_array )) {
						foreach ( $costcount_array as $costcount ) {
							$supplier_id = 0;
							if (NEW_SUPPLIER_ON) {
								// 判断选取的供应商是否存在在
								$supplier_id = $this->db->get_var ( 'SELECT id FROM new_supplier WHERE supplier_name="' . $costcount ['name'] . '" AND isok=1' );
								if ($supplier_id <= 0) {
									$success = FALSE;
									$error = '供应商【' . $costcount ['name'] . '】不存在或已撤销';
									break;
								}
							}
							
							$insert = $this->db->query ( 'INSERT INTO executive_costinfo(type,amount,name,yg,time,supplier_id) VALUES("' . $costcount ['type'] . '",' . $costcount ['amount'] . ',"' . $costcount ['name'] . '","' . $costcount ['yg'] . '",' . time () . ',' . $supplier_id . ')' );
							if ($insert === FALSE) {
								$success = FALSE;
								$error = '写入成本明细记录失败';
								break;
							} else {
								$costcount_ids [] = $this->db->insert_id;
								$costcount_amount += $costcount ['amount'];
								$yg += $costcount ['yg'];
							}
						}
					}
					
					// 成本支付明细
					if ($success) {
						$costpaycount_array = $this->costpaycount_array;
						$costpaycount_ids = array ();
						$costpaycount_amount = 0;
						$cy_cost = array ();
						$suppliers = array ();
						$paycost_id = array ();
						if (! empty ( $costpaycount_array )) {
							foreach ( $costpaycount_array as $kkk => $costpaycount ) {
								if (! in_array ( $costpaycount ['name'], $suppliers, TRUE )) {
									$suppliers [] = $costpaycount ['name'];
								}
								
								$supplier_id2 = 0;
								if (NEW_SUPPLIER_ON) {
									// 判断选取的供应商是否存在在
									$supplier_id2 = $this->db->get_var ( 'SELECT id FROM new_supplier WHERE supplier_name="' . $costpaycount ['name'] . '" AND isok=1' );
									if ($supplier_id2 <= 0) {
										$success = FALSE;
										$error = '供应商【' . $costpaycount ['name'] . '】不存在或已撤销';
										break;
									}
								}
								
								$insert = $this->db->query ( 'INSERT INTO executive_paycost(payname,paytime,payamount,paytype,time,supplier_id) VALUES("' . $costpaycount ['name'] . '","' . $costpaycount ['time'] . '",' . $costpaycount ['amount'] . ',"' . $costpaycount ['type'] . '",' . time () . ',' . $supplier_id2 . ')' );
								if ($insert === FALSE) {
									$success = FALSE;
									$error = '写入成本支付明细记录失败';
									break;
								} else {
									$idd = $this->db->insert_id;
									$costpaycount_ids [] = $idd;
									$costpaycount_amount += $costpaycount ['amount'];
									$cy_cost [] = array (
											'id' => $idd,
											'payname' => $costpaycount ['name'] 
									);
									$paycost_id [$kkk] = $idd;
								}
							}
						}
					}
					// var_dump($paycost_id);
					// var_dump($cy_cost);
					
					if ($success) {
						$update = $this->db->query ( 'UPDATE executive_dep SET dids="' . $this->dids . '",actor="' . $this->actor . '",costinfoids="' . implode ( '^', $costcount_ids ) . '",cost=' . $costcount_amount . ',costpaymentinfoids="' . implode ( '^', $costpaycount_ids ) . '",costpayment=' . $costpaycount_amount . ',remark="' . $this->remark . '",step=1,pcid=' . intval ( $this->process ) . ' WHERE id=' . $depinfoid );
						if ($update === FALSE) {
							$success = FALSE;
							$error = '更新支持部门记录失败';
						}
					}
					
					if ($success) {
						// 计算总成本
						$allcost = 0;
						$yg = 0;
						$support = explode ( '|', $row->support );
						foreach ( $support as $s ) {
							$s = explode ( '^', $s );
							$s = end ( $s );
							$ed = $this->db->get_row ( 'SELECT cost,costinfoids FROM executive_dep WHERE id=' . intval ( $s ) );
							if ($ed !== NULL) {
								$cost = $ed->cost;
								$cost_info_id = $ed->costinfoids;
							} else {
								$cost = 0;
								$cost_info_id = NULL;
							}
							
							if ($cost_info_id !== NULL) {
								$ids = explode ( '^', $cost_info_id );
								foreach ( $ids as $id ) {
									$ygrow = $this->db->get_row ( 'SELECT yg FROM executive_costinfo WHERE id=' . intval ( $id ) );
									if ($ygrow !== NULL) {
										$yg += $ygrow->yg;
									}
								}
							}
							$allcost += $cost;
						}
						
						$allcost += $row->cost;
						
						// 更改主执行单是否预估
						$update_result = $this->db->query ( 'UPDATE executive SET allcost=' . $allcost . ',isyg=isyg + ' . $yg . ' WHERE id=' . intval ( $row->id ) );
						if ($update_result === FALSE) {
							$success = FALSE;
							$error = '更新执行单记录失败';
						}
					}
					
					if ($success) {
						// 拆月数据
						
						$cy_json = json_decode ( base64_decode ( $this->cy_json ) );
						$cy_json = ( array ) $cy_json;
						if (! empty ( $cy_json )) {
							$count = 0;
							$sql = array ();
							
							$start = date ( 'Y-m', strtotime ( $row->starttime ) );
							$end = date ( 'Y-m', strtotime ( $row->endtime ) );
							
							foreach ( $cy_json as $cy ) {
								$cy = ( array ) $cy;
								$cyitems = $cy ['items'];
								$cyitems = ( array ) $cyitems;
								foreach ( $cyitems as $kk => $cyitem ) {
									foreach ( $cyitem as $cyitemem ) {
										$sid = 0;
										$caid = 0;
										
										if (intval ( $row->isalter ) === 0 && (strtotime ( $cyitemem->date ) < strtotime ( $start ) || strtotime ( $cyitemem->date ) > strtotime ( $end ))) {
											$success = FALSE;
											$error = '项目执行日期为' . $start . '至' . $end . '，成本拆月数据必须在该日期范围内';
											break 3;
										}
										
										if (NEW_SUPPLIER_ON) {
											$sggs = Supplier::getSupplierInstance ();
											foreach ( $sggs as $key => $value ) {
												if ($value === $cy ['supplier']) {
													$sid = $key;
													break;
												}
											}
											
											if ($sid <= 0) {
												$success = FALSE;
												$error = '供应商【' . $cy ['supplier'] . '】不存在';
												break 3;
											}
											
											$sg = Supplier::getInstance ();
											/*
											 * $sg_s = $sg['supplier'];
											 *
											 * foreach ($sg_s as $k => $v) {
											 * if ($v['sn'] === $cy['supplier']) {
											 * $sid = $k;
											 * break;
											 * }
											 * }
											 */
											
											if ($sid > 0) {
												$sg_c = $sg ['supplier_category'] [$sid];
												foreach ( $sg_c as $vv ) {
													if ($sg ['category'] [$vv] === $cy ['type']) {
														$caid = $vv;
														break;
													}
												}
											}
										}
										
										$cyitem_date = explode ( '-', $cyitemem->date );
										$cyitem_date_y = reset ( $cyitem_date );
										$cyitem_date_m = end ( $cyitem_date );
										$sql [] = '(' . intval ( $row->id ) . ',"' . $this->pid . '","' . $cy ['supplier'] . '","' . $cy ['type'] . '",' . $cyitem_date_y . ',' . $cyitem_date_m . ',"' . $cyitemem->date . '",' . 0 . ',' . 0 . ',' . $cyitemem->cost . ',' . $cyitemem->cost . ',1,' . intval ( $this->dep ) . ',' . (empty ( $cy ['short_id'] ) ? 0 : $cy ['short_id']) . ',' . $sid . ',' . $caid . ',' . (empty ( $cy ['industry_id'] ) ? 0 : $cy ['industry_id']) . ',' . (empty ( $paycost_id [$kk] ) ? 0 : $paycost_id [$kk]) . ')';
									}
								}
								$count ++;
							}
							// var_dump($sql);
							if ($success) {
								if (! empty ( $sql )) {
									// var_dump($sql);
									
									// 删除
									$insert_result = $this->db->query ( 'DELETE FROM executive_cy WHERE executive_id=' . intval ( $row->id ) . ' AND is_support=1 AND support_dep=' . intval ( $this->dep ) );
									if ($insert_result === FALSE) {
										$success = FALSE;
										$error = '记录支持部门拆月数据失败，错误代码1';
									} else {
										$insert_result = $this->db->query ( 'INSERT INTO executive_cy(executive_id,pid,payname,deliverytype,year,month,ym,quote_amount,finance_quote_amount,cost_amount,finance_cost_amount,is_support,support_dep,supplier_short_id,supplier_id,category_id,industry_id,paycost_id) VALUES ' . implode ( ',', $sql ) );
										if ($insert_result === FALSE) {
											$success = FALSE;
											$error = '记录支持部门拆月数据失败，错误代码2 ';
										}
									}
								}
							}
						} else {
							// 删除
							$insert_result = $this->db->query ( 'DELETE FROM executive_cy WHERE executive_id=' . intval ( $row->id ) . ' AND is_support=1 AND support_dep=' . intval ( $this->dep ) );
							if ($insert_result === FALSE) {
								$success = FALSE;
								$error = '记录支持部门拆月数据失败，错误代码3';
							}
						}
					}
					
					/*
					 * //执行单外包类型
					 * //检查有没有外包
					 * if ($success && !empty($suppliers)) {
					 * $has_outsourcing = FALSE;
					 * $su = $this->db
					 * ->get_results(
					 * 'SELECT a.supplier_id,a.supplier_type,b.supplier_name FROM new_supplier_info a,new_supplier b
					 * WHERE b.supplier_name IN ("' . implode('","', $suppliers)
					 * . '") AND a.supplier_id=b.id AND a.isok=1 AND b.isok=1');
					 * if ($su !== NULL) {
					 * foreach ($su as $suu) {
					 * if (intval($suu->supplier_type) === 2) {
					 * //是外包
					 * $has_outsourcing = TRUE;
					 * break;
					 * }
					 * }
					 * }
					 *
					 * if ($has_outsourcing) {
					 * if (empty($this->outsourcing_type)) {
					 * $success = FALSE;
					 * $error = '供应商已选择外包，执行单外包类型不能为空';
					 * } else {
					 * $insert_result = $this->db
					 * ->query(
					 * 'DELETE FROM outsourcing_pid_type WHERE executive_id='
					 * . $row->id
					 * . ' AND pid="'
					 * . $this->pid
					 * . '" AND support_id='
					 * . $this->dep
					 * . ' AND executive_dep_id='
					 * . $depinfoid);
					 * if ($insert_result === FALSE) {
					 * $success = FALSE;
					 * $error = '执行单外包类型关联失败，错误代码1';
					 * } else {
					 * $insert_result = $this->db
					 * ->query(
					 * 'INSERT INTO outsourcing_pid_type(executive_id,pid,outsourcing_type_id,support_id,executive_dep_id) VALUE('
					 * . $row->id . ',"'
					 * . $this->pid . '",'
					 * . intval(
					 * $this
					 * ->outsourcing_type)
					 * . ',' . $this->dep
					 * . ',' . $depinfoid
					 * . ')');
					 * if ($insert_result === FALSE) {
					 * $success = FALSE;
					 * $error = '执行单外包类型关联失败，错误代码2';
					 * }
					 * }
					 * }
					 * }
					 * }
					 */
					if ($success) {
						$dep = Dep::getInstance ();
						$dep = $dep [$this->dep];
						
						$log_result = $this->do_executive_log ( $this->pid, '', $dep [1] . $dep [0] . ' TF', '<font color=\'#99cc00\'>填写支持部门内容</font>' );
						if ($log_result ['status'] === 'error') {
							$success = FALSE;
							$error = '更新支持部门记录日志操作失败';
						}
					}
					
					if ($success) {
						$update_result = $this->db->query ( 'UPDATE executive_dep SET audittime=' . time () . ' WHERE id=' . $depinfoid );
						if ($update_result === FALSE) {
							$success = FALSE;
							$error = '更新支持部门记录时间操作失败';
						}
					}
				}
			}
			
			if ($success) {
				$this->db->query ( 'COMMIT' );
			} else {
				$this->db->query ( 'ROLLBACK' );
			}
			
			return array (
					'status' => $success ? 'success' : 'error',
					'message' => $success ? '支持部门信息提交成功' : $error 
			);
		}
		return array (
				'status' => 'error',
				'message' => $this->errors 
		);
	}
	public function dep_audit_executive($ismobile = FALSE) {
		/*
		 * if (intval($this->dep) !== intval($this->getBelong_dep())) {
		 * $dep_role = Permission_Dep::getInstance();
		 * $hasright = FALSE;
		 * $dephasroles = $dep_role[intval($this->dep)];
		 * foreach ($dephasroles as $dephasrole) {
		 * if (in_array('dep' . $dephasrole['permission_id'],
		 * $this->getPermissions(), TRUE)) {
		 * $hasright = TRUE;
		 * break;
		 * }
		 * }
		 * if (!$hasright) {
		 * return array('status' => 'error',
		 * 'message' => NO_RIGHT_TO_DO_THIS);
		 * }
		 * }
		 */
		$has_role = $this->_get_dep_audit ( $this->executive_id, $this->dep );
		if (! $has_role) {
			return array (
					'status' => 'error',
					'message' => NO_RIGHT_TO_DO_THIS 
			);
		}
		
		if ($this->validate_form_value ( 'dep_audit' )) {
			$success = TRUE;
			$error = '';
			$this->db->query ( 'BEGIN' );
			
			$results = $this->db->get_results ( 'SELECT id,cost,step,support,is_closed FROM executive WHERE pid="' . $this->pid . '" ORDER BY id' );
			if ($results === NULL) {
				$success = FALSE;
				$error = '执行单号有误';
			} else {
				$row = $results [count ( $results ) - 1];
				
				if (( int ) ($row->is_closed) === 1) {
					$success = FALSE;
					$error = '执行单已关闭，无法审核，详情请联系财务部';
				} else {
					$supports = explode ( '|', $row->support );
					$depinfoid = 0;
					
					foreach ( $supports as $s ) {
						$s = explode ( '^', $s );
						if (intval ( $this->dep ) === intval ( $s [0] )) {
							$depinfoid = intval ( $s [1] );
							break;
						}
					}
					
					if ($depinfoid > 0) {
						$dep = $this->db->get_row ( 'SELECT step,pcid FROM executive_dep WHERE id=' . $depinfoid );
						if ($dep !== NULL) {
							$step = $dep->step;
							$dep_process = Dep_Process::getInstance ();
							$dep_process = $dep_process [$dep->pcid];
							
							if ($ismobile && $step === '0') {
								$this->audit_pass = 0;
							}
							
							if (intval ( $this->audit_pass ) === 1) {
								// 审核确认
								$log_result = $this->do_executive_log ( $this->pid, $this->remark, $dep_process [$step] [0], '<font color=\'#66cc00\'>审核确认</font>' );
								if ($log_result ['status'] === 'success') {
									$msgid = $log_result ['msgid'];
								} else {
									$success = FALSE;
									$error = '支持部门审核记录日志操作失败';
								}
								
								if ($success) {
									$update_result = $this->db->query ( 'UPDATE executive_dep SET audittime=' . time () . ' WHERE id=' . $depinfoid );
									if ($update_result === FALSE) {
										$success = FALSE;
										$error = '支持部门审核操作失败';
									}
								}
								
								if ($success) {
									
									$outsourcing_need_audit = FALSE;
									$exeoutid = $this->db->get_var ( 'SELECT id FROM outsourcing_pid_type WHERE executive_id=' . $row->id . ' AND pid="' . $this->pid . '" AND support_id=' . $this->dep . ' AND isok=-1' );
									if ($exeoutid > 0) {
										// 有外包 ，需要审核核
										$outsourcing_need_audit = TRUE;
										$update_result = $this->db->query ( 'UPDATE outsourcing_pid_type SET isok=0 WHERE id=' . $exeoutid );
										if ($update_result === FALSE) {
											$success = FALSE;
											$error = '更新执行单外包审核状态出错';
										}
									}
									
									if ($success) {
										if (intval ( $step ) + 1 === count ( $dep_process )) {
											$update_result = $this->db->query ( 'UPDATE executive_dep SET step=step+1' . ($outsourcing_need_audit ? '' : ',isok=1') . ',msgid=' . $msgid . ' WHERE id=' . $depinfoid );
											if ($update_result === FALSE) {
												$success = FALSE;
												$error = '支持部门审核步骤更新失败';
											} else {
												// 支持部门都确认了，执行单进一步
												if ($this->check_is_all_dep_ok ( $row->support )) {
													$update_result = $this->db->query ( 'UPDATE executive SET step=step+1,msgid=0 WHERE pid="' . $this->pid . '" ORDER BY id DESC LIMIT 1' );
													if ($update_result === FALSE) {
														$success = FALSE;
														$error = '更新执行单状态失败';
													}
												}
											}
										} else {
											$update_result = $this->db->query ( 'UPDATE executive_dep SET step=step+1,msgid=' . $msgid . ' WHERE id=' . $depinfoid );
											if ($update_result === FALSE) {
												$success = FALSE;
												$error = '支持部门审核步骤更新失败';
											}
										}
									}
								}
							} else {
								// 审核驳回
								$log_result = $this->do_executive_log ( $this->pid, $this->remark, $dep_process [$step] [0], (intval ( $step ) === 0 ? '<font color=\'#ff9900\'>驳回 至 发起人</font>' : '<font color=\'#ff9900\'>驳回 至 TF</font>') );
								if ($log_result ['status'] === 'success') {
									$msgid = $log_result ['msgid'];
								} else {
									$success = FALSE;
									$error = '支持部门审核驳回记录日志操作失败';
								}
								
								if ($success) {
									$update_result = $this->db->query ( 'UPDATE executive_dep SET audittime=' . time () . ' WHERE id=' . $depinfoid );
									if ($update_result === FALSE) {
										$success = FALSE;
										$error = '支持部门审核驳回操作失败';
									}
								}
								
								if ($success) {
									if (intval ( $step ) === 0) {
										$update_result = $this->db->query ( 'UPDATE executive SET step=0,msgid=' . $msgid . ' WHERE pid="' . $this->pid . '" ORDER BY id DESC LIMIT 1' );
										if ($update_result === FALSE) {
											$success = FALSE;
											$error = '支持部门审核执行单驳回状态更新失败';
										}
										
										if ($success) {
											// 所有支持部门回复到初始状态
											$supports = explode ( '|', $row->support );
											foreach ( $supports as $s ) {
												$s = explode ( '^', $s );
												$update_result = $this->db->query ( 'UPDATE executive_dep SET step=0 WHERE id=' . intval ( $s [1] ) );
												if ($update_result === FALSE) {
													$success = FALSE;
													$error = '支持部门回复到初始状态操作失败';
													break;
												}
											}
										}
									} else {
										$update_result = $this->db->query ( 'UPDATE executive_dep SET step=0,msgid=' . $msgid . ' WHERE id=' . $depinfoid );
										if ($update_result === FALSE) {
											$success = FALSE;
											$error = '支持部门审核驳回状态更新失败';
										}
									}
								}
							}
						} else {
							$success = FALSE;
							$error = '该支持部门不存在';
						}
					} else {
						$success = FALSE;
						$error = '该支持部门不存在';
					}
				}
			}
			
			if ($success) {
				$this->db->query ( 'COMMIT' );
			} else {
				$this->db->query ( 'ROLLBACK' );
			}
			
			return array (
					'status' => $success ? 'success' : 'error',
					'message' => $success ? '支持部门审核成功' : $error 
			);
		}
		return array (
				'status' => 'error',
				'message' => $this->errors 
		);
	}
	
	// TODO
	public function get_executive_alter() {
		if ($this->pid !== NULL) {
			$pidinfo = array ();
			$results = $this->db->get_results ( 'SELECT a.*,FROM_UNIXTIME(a.time) AS tt,b.contractname,b.cusname,b.contractamount,b.contractcontent,b.billtype FROM executive a LEFT JOIN contract_cus b ON a.cid=b.cid WHERE a.pid="' . $this->pid . '" AND a.isok!=-1 ORDER BY a.id' );
			if ($results !== NULL) {
				foreach ( $results as $result ) {
					$pidinfo [] = $result;
				}
			}
			if (empty ( $pidinfo )) {
				return User::no_object ( '没有该执行单' );
			}
			$row = end ( $pidinfo );
			if (intval ( $row->user ) !== intval ( $this->getUid () )) {
				return User::no_permission ();
			}
			$buf = file_get_contents ( TEMPLATE_PATH . 'executive/executive_alter.tpl' );
			
			$type1 = 'checked="checked"';
			$type2 = '';
			$type3 = '';
			if (intval ( $row->type ) === 2) {
				$type1 = '';
				$type2 = 'checked="checked"';
				$type3 = '';
			} else if (intval ( $row->type ) === 3) {
				$type1 = '';
				$type2 = '';
				$type3 = 'checked="checked"';
			}
			
			$company3 = 'checked="checked"';
			$company1 = '';
			if (intval ( $row->company ) === 1) {
				$company3 = '';
				$company1 = 'checked="checked"';
			}
			
			$did_value = $row->dids;
			if (! empty ( $did_value )) {
				$did_value = '^' . $did_value . '^';
			} else {
				$did_value = '^';
			}
			
			$team = $row->team;
			$dep = $row->dep;
			$principal = '<option value="">请选择人员</option>';
			$actorlist = '<option value="">请选择人员</option>';
			if (intval ( $team ) > 0) {
				$team_obj = new Team ( $team );
				$principal = $team_obj->get_users_select_html_by_team ( $row->principal );
				$actorlist = $team_obj->get_users_select_html_by_team ();
			} else if (intval ( $dep ) > 0) {
				$dep_obj = new Dep ( $dep );
				$principal = $dep_obj->get_users_select_html_by_dep ( $row->principal );
				$actorlist = $dep_obj->get_users_select_html_by_dep ();
			}
			
			$servicecf = $this->_get_edit_servicecf ( $row->servicecf ,$row->billtype);
			
			if (intval ( $row->isok ) === 1) { // 如果isok=1 即表示重新变更，否则为变更修改
				$altersupportdep = $this->_get_exist_supportdep ( $row->support, '' );
				$supportdep = $this->_get_alter_supportdep ( $row->support, $row->dep, '' );
			} else {
				$row1 = $pidinfo [count ( $pidinfo ) - 2];
				$altersupportdep = $this->_get_exist_supportdep ( $row1->support, $row->support );
				$supportdep = $this->_get_alter_supportdep ( $row1->support, $row->dep, $row->support );
			}
			
			$agentrow = $this->db->get_row ( 'SELECT cusname,customertype FROM executive_agent WHERE executive_id=' . $row->id . ' AND pid="' . $this->pid . '"' );
			
			$tax_rate_select = array();
			foreach ($GLOBALS['defined_servicecf_type'] as $key=>$value){
				$tax_rate_select[] = array('id' => $key, 'name' => urlencode($value));
			}
			$tax_rate_select = urldecode(json_encode($tax_rate_select));
				
			$tax_rate = 1;
			if(intval($row->billtype) === 2){
				$tax_rate = FW_TAX_RATE;
			}else if(intval($row->billtype) === 1){
				$tax_rate = GG_TAX_RATE;
			}
			
			$search = array (
					'[LEFT]',
					'[TOP]',
					'[PID]',
					'[CITYINFO]',
					'[TYPE1]',
					'[TYPE2]',
					'[TYPE3]',
					'[COMPANY3]',
					'[COMPANY1]',
					'[NAME]',
					'[DIDS]',
					'[DIDSVALUE]',
					'[PRINCIPAL]',
					'[ACTORLIST]',
					'[ACTOR]',
					'[STARTTIME]',
					'[ENDTIME]',
					'[PAYTIMEINFO]',
					'[AMOUNT]',
					'[COSTINFO]',
					'[COST]',
					'[COSTPAYMENTINFO]',
					'[COSTPAYMENT]',
					'[SERVICECFLIST]',
					'[ALLSERVICECFAMOUNT]',
					'[TAXMOUNT]',
					'[REMARK]',
					'[ALTERSUPPORTDEP]',
					'[SUPPORTDEP]',
					'[PROCESSLIST]',
					'[LOGLIST]',
					'[VCODE]',
					'[VALIDATE_TYPE]',
					'[VALIDATE_SIZE]',
					'[ACTOR_SHOW]',
					'[CYOVERLAY]',
					'[SUPPLIERS]',
					'[CYON]',
					'[CYAMOUNTLIST]',
					'[SUPPLIERCATEGORYS]',
					'[SUPPLIERSHORTS]',
					'[SUPPLIERINDUSTRYS]',
					'[CYAMOUNT]',
					'[TAXRATESELECT]',
					'[TAXRATE]',
					'[TAXRATESHOW]',
					'[BASE_URL]' 
			);
			$replace = array (
					$this->get_left_html (),
					$this->get_top_html (),
					$this->pid,
					$this->get_user_city_info ( $row->city, $row->dep, $row->team ),
					$type1,
					$type2,
					$type3,
					$company3,
					$company1,
					$row->name,
					$this->get_upload_files ( $row->dids, TRUE ),
					$did_value,
					$principal,
					$actorlist,
					$row->actor,
					$row->starttime,
					$row->endtime,
					$this->_get_edit_paytimeinfo ( $row->paytimeinfoids ),
					$row->amount,
					$this->_get_edit_costinfo ( $row->costinfoids ),
					$row->cost,
					$this->_get_edit_costpayment_info ( $row->id, $row->costpaymentinfoids ),
					$row->costpayment,
					$servicecf [0],
					$servicecf [1],
					$servicecf [2],
					$row->remark,
					$altersupportdep,
					$supportdep,
					$this->get_process_list ( $row->dep, $row->pcid ),
					$this->get_log_list (),
					$this->get_vcode (),
					implode ( ',', $GLOBALS ['defined_upload_validate_type'] ),
					UPLOAD_FILE_MAX_SIZE / (1024 * 1024),
					self::_get_actor_show ( $row->actor ),
					$this->_get_cy_overlay ( $row->id, $this->pid, $row->isok, $row->isalter, 0, TRUE ),
					$this->_getCYSuppliers ( $row->id ),
					(CY_ON ? 1 : 0),
					$this->_get_edit_cy_amount ( $row->id, $this->pid, $row->isok, $row->isalter ),
					Supplier::getCategorysSelect (),
					Supplier_Short::getSupplierShortSelect (),
					Supplier_Short::getIndustrySelect (),
					$this->_get_cyamount ( $row->id, $this->pid ),
					$tax_rate_select,
					$tax_rate,
					$tax_rate * 100,
					BASE_URL 
			);
			return str_replace ( $search, $replace, $buf );
		}
		return User::no_object ( '没有该执行单' );
	}
	public function get_executive_edit() {
		if ($this->pid !== NULL) {
			$row = $this->db->get_row ( 'SELECT * FROM executive WHERE pid="' . $this->pid . '" AND isok=0 ORDER BY id DESC LIMIT 1' );
			if ($row === NULL) {
				return User::no_object ( '没有该执行单' );
			} else {
				if (intval ( $this->getUid () ) !== intval ( $row->user )) {
					return User::no_object ( '你没有权限修改该执行单' );
				} else {
					$buf = file_get_contents ( TEMPLATE_PATH . 'executive/executive_edit.tpl' );
					$type1 = 'checked="checked"';
					$type2 = '';
					$type3 = '';
					if (intval ( $row->type ) === 2) {
						$type1 = '';
						$type2 = 'checked="checked"';
						$type3 = '';
					} else if (intval ( $row->type ) === 2) {
						$type1 = '';
						$type2 = '';
						$type3 = 'checked="checked"';
					}
					
					$company3 = 'checked="checked"';
					$company1 = '';
					if (intval ( $row->company ) === 1) {
						$company3 = '';
						$company1 = 'checked="checked"';
					}
					
					$did_value = $row->dids;
					if (! empty ( $did_value )) {
						$did_value = '^' . $did_value . '^';
					} else {
						$did_value = '^';
					}
					
					$team = $row->team;
					$dep = $row->dep;
					$principal = '<option value="">请选择人员</option>';
					$actorlist = '<option value="">请选择人员</option>';
					if (intval ( $team ) > 0) {
						$team_obj = new Team ( $team );
						$principal = $team_obj->get_users_select_html_by_team ( $row->principal );
						$actorlist = $team_obj->get_users_select_html_by_team ();
					} else if (intval ( $dep ) > 0) {
						$dep_obj = new Dep ( $dep );
						$principal = $dep_obj->get_users_select_html_by_dep ( $row->principal );
						$actorlist = $dep_obj->get_users_select_html_by_dep ();
					}
					
					$billtype = $this->db->get_var('SELECT billtype FROM contract_cus WHERE cid="' . $row->cid . '"');
					$servicecf = $this->_get_edit_servicecf ( $row->servicecf ,$billtype);
					
					$support = array_keys ( User::get_support_array ( $row->support ) );
					
					$agentrow = $this->db->get_row ( 'SELECT cusname,customertype FROM executive_agent WHERE executive_id=' . $row->id . ' AND pid="' . $this->pid . '"' );
					
					$tax_rate_select = array();
					foreach ($GLOBALS['defined_servicecf_type'] as $key=>$value){
						$tax_rate_select[] = array('id' => $key, 'name' => urlencode($value));
					}
					$tax_rate_select = urldecode(json_encode($tax_rate_select));
					
					$tax_rate = 1;
					if(intval($billtype) === 2){
						$tax_rate = FW_TAX_RATE;
					}else if(intval($billtype) === 1){
						$tax_rate = GG_TAX_RATE;
					}
					
					$search = array (
							'[LEFT]',
							'[TOP]',
							'[PID]',
							'[CITYINFO]',
							'[TYPE1]',
							'[TYPE2]',
							'[TYPE3]',
							'[COMPANY3]',
							'[COMPANY1]',
							'[NAME]',
							'[DIDS]',
							'[DIDSVALUE]',
							'[PRINCIPAL]',
							'[ACTORLIST]',
							'[ACTOR]',
							'[STARTTIME]',
							'[ENDTIME]',
							'[PAYTIMEINFO]',
							'[AMOUNT]',
							'[COSTINFO]',
							'[COST]',
							'[COSTPAYMENTINFO]',
							'[COSTPAYMENT]',
							'[SERVICECFLIST]',
							'[ALLSERVICECFAMOUNT]',
							'[TAXMOUNT]',
							'[REMARK]',
							'[SUPPORTDEP]',
							'[PROCESSLIST]',
							'[LOGLIST]',
							'[VCODE]',
							'[VALIDATE_TYPE]',
							'[VALIDATE_SIZE]',
							'[MSG]',
							'[ACTOR_SHOW]',
							'[CYOVERLAY]',
							'[SUPPLIERS]',
							'[CYON]',
							'[VCODE]',
							'[SUPPLIERCATEGORYS]',
							'[CYAMOUNTLIST]',
							'[SUPPLIERSHORTS]',
							'[SUPPLIERINDUSTRYS]',
							'[CYAMOUNT]',
							'[TAXRATESELECT]',
							'[TAXRATE]',
							'[TAXRATESHOW]',
							'[BASE_URL]' 
					);
					$replace = array (
							$this->get_left_html (),
							$this->get_top_html (),
							$this->pid,
							$this->get_user_city_info ( $row->city, $row->dep, $row->team ),
							$type1,
							$type2,
							$type3,
							$company3,
							$company1,
							$row->name,
							$this->get_upload_files ( $row->dids, TRUE ),
							$did_value,
							$principal,
							$actorlist,
							$row->actor,
							$row->starttime,
							$row->endtime,
							$this->_get_edit_paytimeinfo ( $row->paytimeinfoids ),
							$row->amount,
							$this->_get_edit_costinfo ( $row->costinfoids ),
							$row->cost,
							$this->_get_edit_costpayment_info ( $row->id, $row->costpaymentinfoids ),
							$row->costpayment,
							$servicecf [0],
							$servicecf [1],
							$servicecf [2],
							$row->remark,
							$this->get_support_dep ( $support, $row->dep ),
							$this->get_process_list ( $row->dep, $row->pcid ),
							$this->get_log_list (),
							$this->get_vcode (),
							implode ( ',', $GLOBALS ['defined_upload_validate_type'] ),
							UPLOAD_FILE_MAX_SIZE / (1024 * 1024),
							$this->_get_log ( $row->msgid ),
							self::_get_actor_show ( $row->actor ),
							$this->_get_cy_overlay ( $row->id, $this->pid, $row->isok, $row->isalter ),
							$this->_getCYSuppliers ( $row->id ),
							(CY_ON ? 1 : 0),
							$this->get_vcode (),
							Supplier::getCategorysSelect (),
							$this->_get_edit_cy_amount ( $row->id, $this->pid, $row->isok, $row->isalter ),
							Supplier_Short::getSupplierShortSelect (),
							Supplier_Short::getIndustrySelect (),
							$this->_get_cyamount ( $row->id, $this->pid ),
							$tax_rate_select,
							$tax_rate,
							$tax_rate * 100,
							BASE_URL 
					);
					return str_replace ( $search, $replace, $buf );
				}
			}
		}
		return User::no_object ( '没有该执行单' );
	}
	private function _get_cyamount($executive_id, $pid) {
		$amount = $this->db->get_var ( 'SELECT SUM(quote_amount) FROM executive_amount_cy WHERE executive_id=' . $executive_id . ' AND pid="' . $pid . '"' );
		return empty ( $amount ) ? 0 : $amount;
	}
	private function _get_edit_cy_amount($id, $pid, $isok, $isalter) {
		$s = '<input type="hidden" name="cy_amount" id="cy_amount" value="[CYAMOUNTCOUNT]"/><div id="cyamountlist">';
		
		$count = 1;
		$count_array = array ();
		
		$results = $this->db->get_results ( 'SELECT ym,quote_amount FROM executive_amount_cy WHERE executive_id=' . $id . ' AND pid="' . $pid . '"' );
		if ($results !== NULL) {
			
			// 获取最后结账日期
			$settle_account_date = $this->db->get_var ( 'SELECT MAX(settle_account_date) FROM finance_settle_account' );
			$last_time = 0;
			$settle_month = '';
			// $wdate_min_month = '';
			if (! empty ( $settle_account_date )) {
				// 有结账日期
				$settle_month = date ( 'Y-m', strtotime ( $settle_account_date ) );
				$last_time = strtotime ( $settle_month );
				// $wdate_min_month = date('Y-m',strtotime($settle_month . ' +1 month'));
			}
			
			foreach ( $results as $result ) {
				$canot_edit = FALSE;
				if (strtotime ( $result->ym ) <= $last_time && (( int ) $isok === 1 || ( int ) $isalter > 0)) {
					$canot_edit = TRUE;
				}
				$s .= '<div>';
				$s .= '时间：<input type="text" width="100" class="validate[required] Wdate" name="amountcytime_' . $count . '" id="amountcytime_' . $count . '" ' . ($canot_edit ? '' : 'onclick="WdatePicker({dateFmt:\'yyyy-MM\'});"') . ' readonly="readonly" value="' . $result->ym . '" ' . ($canot_edit ? 'style="background-color:#CCCCCC"' : '') . '> &nbsp;&nbsp;';
				$s .= '金额：<input type="text" onblur="tjamountcy(this)" style="width:100px; text-align:right;height:20px;' . ($canot_edit ? 'background-color:#CCCCCC;' : '') . '" class="validate[required,custom[cyMoney]]" name="amountcy_' . $count . '" id="amountcy_' . $count . '" value="' . $result->quote_amount . '" ' . ($canot_edit ? 'readonly="readonly"' : '') . '> 元&nbsp;&nbsp;';
				if (! $canot_edit) {
					$s .= '<img src="' . BASE_URL . '/images/close.png" onclick="delamountcy(this,\'' . $count . '\')" width="17" height="17" />';
				}
				$s .= '<br /></div>';
				$count_array [] = $count;
				$count ++;
			}
		}
		$s .= '</div><script>var cy_amount_count = ' . $count . ';</script>';
		return str_replace ( array (
				'[CYAMOUNTCOUNT]' 
		), array (
				empty ( $count_array ) ? ',' : ',' . implode ( ',', $count_array ) . ',' 
		), $s );
	}
	private static function _get_count_hidden($count, $executive_id, $dep) {
		if ($count > 0) {
			$s = ',';
			for($i = 0; $i < $count; $i ++) {
				$s .= ($i + 1) . ',';
			}
			return $s;
		} else {
			$dao = new Dao_Impl ();
			$costpaymentinfoids = '';
			if ($dep === 0) {
				$costpaymentinfoids = $dao->db->get_var ( 'SELECT costpaymentinfoids FROM executive WHERE id=' . $executive_id );
			} else {
				$support = $dao->db->get_var ( 'SELECT support FROM executive WHERE id=' . $executive_id );
				$support = explode ( '|', $support );
				$depid = 0;
				foreach ( $support as $supp ) {
					$supp = explode ( '^', $supp );
					if (( int ) ($supp [0]) === ( int ) $dep) {
						$depid = $supp [1];
						break;
					}
				}
				if ($depid > 0) {
					$costpaymentinfoids = $dao->db->get_var ( 'SELECT costpaymentinfoids FROM executive_dep WHERE id=' . $depid );
				}
			}
			$dao->db->disconnect ();
			$s = ',';
			$c = 0;
			$costpaymentinfoids = explode ( '^', $costpaymentinfoids );
			foreach ( $costpaymentinfoids as $v ) {
				if (! empty ( $v )) {
					$c ++;
				}
			}
			for($i = 0; $i < $c; $i ++) {
				$s .= ($i + 1) . ',';
			}
			return $s;
		}
	}
	private function _getCYSuppliers($executive_id, $dep = 0) {
		$arr = array ();
		$results = $this->db->get_results ( 'SELECT DISTINCT(payname) FROM executive_cy WHERE executive_id=' . $executive_id . ($dep > 0 ? ' AND is_support=1 AND support_dep=' . $dep : ' AND is_support=0 AND support_dep=0') );
		
		if ($results !== NULL) {
			foreach ( $results as $result ) {
				$arr [] = urlencode ( $result->payname );
			}
		}
		return urldecode ( json_encode ( $arr ) );
	}
	private static function _getSupplierCategorySelect($supplier, $category) {
		$s = '<option value="">请选择投放类型</option>';
		$x = Supplier::getCategorysSelect ();
		$x = json_decode ( $x );
		foreach ( $x as $xx ) {
			if ($xx->s === $supplier) {
				foreach ( $xx->d as $c ) {
					$s .= '<option value="' . $c . '"' . ($c === $category ? 'selected' : '') . '>' . $c . '</option>>';
				}
			}
		}
		return $s;
	}
	private static function _getIndustrySelect($short_id, $industry_id, &$req) {
		$s = '<option value="">请选择客户行业分类</option>';
		if ($short_id > 0) {
			$ins = Supplier_Short::getIndustries ();
			foreach ( $ins as $key => $val ) {
				if (( int ) $key === ( int ) $short_id) {
					$req = TRUE;
					foreach ( $val as $v ) {
						$s .= '<option value="' . $v ['id'] . '"' . (( int ) ($v ['id']) === ( int ) $industry_id ? 'selected="selected"' : '') . '>' . $v ['name'] . '</option>';
					}
					break;
				}
			}
		}
		return $s;
	}
	private static function _getSupplierShortSelect($supplier_short_id) {
		$s = '<option value="">请选择投放媒体</option>';
		$x = Supplier_Short::getSupplierShortSelect ();
		$x = json_decode ( $x );
		foreach ( $x as $xx ) {
			$s .= '<option value="' . $xx->id . '"' . (strval ( $supplier_short_id ) === strval ( $xx->id ) ? 'selected' : '') . '>' . $xx->name . '</option>';
		}
		return $s;
	}
	private function _get_cy_overlay($executive_id, $pid, $isok, $isalter, $dep = 0, $exe_alter = FALSE) {
		$boa = 0;
		$oldid = $this->db->get_var ( 'SELECT  id FROM executive WHERE pid="' . $pid . '" AND isok<>-1 AND id<>' . $executive_id . ' ORDER BY id DESC LIMIT 1' );
		if ($oldid > 0) {
			$boa = $this->db->get_var ( 'SELECT SUM(cost_amount) FROM executive_cy WHERE executive_id=' . $oldid . ' AND pid="' . $pid . '"' . ($dep !== 0 ? ' AND is_support=1 AND support_dep=' . $dep : '') );
		}
		
		$s = '<div class="scbox">';
		
		$cy_json = $this->_get_cy_json ( $executive_id, $dep );
		if ($cy_json !== '') {
			$s .= '<script>';
			$s .= '$("#cy_json").val("' . $cy_json . '");';
			$s .= '</script>';
		}
		if ($cy_json !== base64_encode ( json_encode ( '' ) ) && $cy_json !== '') {
			$cy_json = json_decode ( base64_decode ( $cy_json ) );
			$cy_json = ( array ) $cy_json;
			
			// 获取最后结账日期
			$settle_account_date = $this->db->get_var ( 'SELECT MAX(settle_account_date) FROM finance_settle_account' );
			$last_time = 0;
			$settle_month = '';
			// $wdate_min_month = '';
			if (! empty ( $settle_account_date )) {
				// 有结账日期
				$settle_month = date ( 'Y-m', strtotime ( $settle_account_date ) );
				$last_time = strtotime ( $settle_month );
				// $wdate_min_month = date('Y-m',strtotime($settle_month . ' +1 month'));
			}
			
			if (! empty ( $cy_json )) {
				$co = 0;
				$s .= '<div>&nbsp;原总执行成本：<span id="boa" style="color:red;font-weight:bold;">0</span>&nbsp;元&nbsp;现总执行成本：<span id="coa" style="color:red;font-weight:bold;">0</span>&nbsp;元</div>';
				$s .= '<div>&nbsp;<font color="red"><b>(*)</b></font>：指实际投放媒体，例如“Baidu 百度”，“QQ 腾讯” </div>';
				$s .= '<div>&nbsp;<font color="red"><b>(**)</b></font>：指投放的产品或投放类型，例如“百度_阿拉丁”，“百度_Hao123”， “QQ Live 腾讯视频_OMD”，“QQ 广点通”</div>';
				foreach ( $cy_json as $key => $cy ) {
					
					$coa = 0;
					$cy = ( array ) $cy;
					$cyitems = $cy ['items'];
					$cyitems = ( array ) $cyitems;
					$cyitems = ( array ) (reset ( $cyitems ));
					
					$req = FALSE;
					$s .= '<table cellpadding="0" cellspacing="0" width="100%" border="0" class="sbd2" id="table_' . $key . '">';
					$s .= '<tr>';
					$s .= '<td width="150" style="font-weight:bold" rowspan="3" id="supplier_' . $key . '">' . $cy ['supplier'] . '</td>';
					$s .= '<td>媒体&nbsp;<font color="red"><b>(*)</b></font></td><td><select name="supplier_short_' . $key . '" id="supplier_short_' . $key . '" class="select">' . self::_getSupplierShortSelect ( $cy ['short_id'] ) . '</select>&nbsp;<select name="industry_' . $key . '" id="industry_' . $key . '" class="select">' . self::_getIndustrySelect ( $cy ['short_id'], $cy ['industry_id'], $req ) . '</select>&nbsp;<input type="button" class="longbtn" value="&nbsp;添加拆月数据&nbsp;" id="addcy_' . $key . '" onclick="javascript:x(this);"></tr>';
					$s .= '<tr><td>投放类型<font color="red"><b>(**)</b></font></td><td><input type="hidden" name="supplier_' . $key . '" value="' . $cy ['supplier'] . '"><select name="deliverytype_' . $key . '" id="deliverytype_' . $key . '" class="select">' . self::_getSupplierCategorySelect ( $cy ['supplier'], $cy ['type'] ) . '</select>';
					$s .= '<input type="hidden" value="' . self::_get_count_hidden ( count ( $cyitems ), $executive_id, $dep ) . '" id="cycount_' . $key . '" name="cycount_' . $key . '">&nbsp;执行成本小计：<span id="coa_' . $key . '" style="color:red;font-weight:bold;">0</span>&nbsp;元</td></tr>';
					$first = array_slice ( $cyitems, 0, 1 );
					$others = array_slice ( $cyitems, 1 );
					
					$canot_edit = FALSE;
					if (strtotime ( $first [0]->date ) <= $last_time && (( int ) $isok === 1 || ( int ) $isalter > 0)) {
						$canot_edit = TRUE;
					}
					
					$s .= '<tr><td>&nbsp;</td><td>时间：';
					
					$s .= '<input type="text" id="cyym_' . $key . '_1" name="cyym_' . $key . '_1" class="validate[required] Wdate" style="width:100px;' . ($canot_edit ? 'background-color:#CCCCCC"' : '') . '" ' . ($canot_edit ? '' : 'onclick="WdatePicker({dateFmt:\'yyyy-MM\'});"') . ' readonly="readonly" value="' . $first [0]->date . '">';
					
					$s .= '&nbsp;&nbsp;执行成本：<input type="text" id="cost_amount_' . $key . '_1" name="cost_amount_' . $key . '_1" style="width:100px; text-align:right;height:20px;' . ($canot_edit ? 'background-color:#CCCCCC"' : '') . '" class="validate[required,custom[cyMoney]]" value="' . $first [0]->cost . '" ' . ($canot_edit ? 'readonly="readonly"' : '') . '>&nbsp;元';
					$s .= '<div id="cylist_' . $key . '">';
					$co += $first [0]->cost;
					$coa += $first [0]->cost;
					
					foreach ( $others as $k => $other ) {
						$canot_edit = FALSE;
						if (strtotime ( $other->date ) <= $last_time && (( int ) $isok === 1 || ( int ) $isalter > 0)) {
							$canot_edit = TRUE;
						}
						$s .= '<div>时间：';
						$s .= '<input type="text" id="cyym_' . $key . '_' . ($k + 2) . '" name="cyym_' . $key . '_' . ($k + 2) . '" class="validate[required] Wdate" style="width:100px;' . ($canot_edit ? 'background-color:#CCCCCC"' : '') . '" ' . ($canot_edit ? '' : 'onclick="WdatePicker({dateFmt:\'yyyy-MM\'});"') . ' readonly="readonly" value="' . $other->date . '">';
						$s .= '&nbsp;&nbsp;执行成本：<input type="text" id="cost_amount_' . $key . '_' . ($k + 2) . '" name="cost_amount_' . $key . '_' . ($k + 2) . '" style="width:100px;text-align:right;height:20px;' . ($canot_edit ? 'background-color:#CCCCCC"' : '') . '" class="validate[required,custom[cyMoney]]" value="' . $other->cost . '" ' . ($canot_edit ? 'readonly="readonly"' : '') . '>&nbsp;元&nbsp;&nbsp;' . ($canot_edit ? '' : '<img width="17" height="17" onclick="delcy(this,' . $key . ',' . ($k + 2) . ')" src="' . BASE_URL . '/images/close.png">') . '<br></div>';
						$co += $other->cost;
						$coa += $other->cost;
					}
					
					$s .= '</div></td>';
					$s .= '</tr>';
					$s .= '</table><br/>';
					$s .= '<script>var _cy_' . $key . '=' . count ( $cyitems ) . ';';
					$s .= '$("#coa_' . $key . '").html(' . $coa . ');';
					if ($cy ['short_id'] > 0 && $req) {
						$s .= '$("#industry_' . $key . '").addClass("validate[required]");';
					}
					$s .= '</script>';
				}
				$s .= '<script>';
				$s .= '$("#coa").html(' . $co . ');';
				$s .= '$("#boa").html(' . $boa . ');';
				$s .= '</script>';
				$s .= '<div class="btn_div" id="cybtn_sub"><input type="submit" name="cybtn" value="确 定" class="btn_sub"/></div>';
			}
		}
		$s .= '</div>';
		$row = $this->db->get_row ( 'SELECT starttime,endtime,isalter FROM executive WHERE id=' . $executive_id );
		$s .= '<input type="hidden" name="hiddenstarttime" id="hiddenstarttime" value="' . $row->starttime . '"/><input type="hidden" name="hiddenendtime" id="hiddenendtime" value="' . $row->endtime . '"/><input type="hidden" name="copycostpaycount" id="copycostpaycount" value="' . self::_get_count_hidden ( count ( $cy_json ), $executive_id, $dep ) . '"/><input type="hidden" name="action" value="' . ($exe_alter ? 'executive_alter_cy' : 'executive_cy') . '"/><input type="hidden" name="vcode" value="' . $this->get_vcode () . '"/><input type="hidden" name="isaltershow" value="' . $row->isalter . '"/><img src="' . BASE_URL . 'images/none.gif" class="close" onclick="close_pop();"/>';
		return $s;
	}
	private static function _get_actor_show($actor) {
		// $s = '';
		$v = array ();
		$last = '';
		if (! empty ( $actor )) {
			$actor = explode ( ',', $actor );
			$actor_count = count ( $actor );
			foreach ( $actor as $key => $act ) {
				// if ($key !== 0) {
				// $s .= ' ';
				// }
				if ($key === ($actor_count - 1)) {
					$last = $act;
				}
				$v [] = '"' . $act . '"';
				// $s .= $act . '<img src="' . BASE_URL
				// . 'images/close.png" onclick="actor_del(' . $key
				// . ')"/>';
			}
		}
		if (empty ( $v )) {
			$v = 'new Array();';
		} else {
			$v = '[' . implode ( ',', $v ) . '];';
		}
		// return '<script>var actor_array = ' . $v
		// . '$("select[@name=actorlist] option[text=\'' . $last
		// . '\']").attr("selected", true);</script>' . $s;
		return '<script>var actor_array = ' . $v . '</script>';
	}
	private function _get_log($msgid) {
		$s = '';
		$row = $this->db->get_row ( 'SELECT a.auditname,a.content,b.realname,b.username FROM executive_log a LEFT JOIN users b ON a.uid=b.uid WHERE a.id=' . intval ( $msgid ) );
		if ($row !== NULL) {
			$s = $row->auditname . ' ' . $row->realname . ' (' . $row->username . ') 驳回 : ' . $row->content;
		}
		return $s;
	}
	private function _get_edit_paytimeinfo($paytimeinfoids) {
		$ids = explode ( '^', $paytimeinfoids );
		$s = '<input type="hidden" name="paycount" id="paycount" value="[PAYCOUNT]"/><br /><div id="paytimelist">';
		
		$count = 1;
		$count_array = array ();
		foreach ( $ids as $id ) {
			if (intval ( $id ) > 0) {
				$row = $this->db->get_row ( 'SELECT paytime,amount,remark FROM executive_paytime WHERE id=' . intval ( $id ) );
				if ($row !== NULL) {
					$s .= '<div>';
					$s .= sprintf ( '时间：<input type="text" onclick="WdatePicker()" width="100" class="validate[required] Wdate" name="paytime_' . $count . '" id="paytime_' . $count . '" value="%s" > &nbsp;&nbsp;', $row->paytime );
					$s .= sprintf ( '金额：<input type="text" onblur="tjamount(this)" style="width:100px; text-align:right;height:20px;" class="validate[required,custom[money]]" name="payamount_' . $count . '" id="payamount_' . $count . '" value="%s"> 元&nbsp;&nbsp;', $row->amount );
					$s .= sprintf ( '备注：<input type="text" width="150" style="height:20px;" name="payremark_' . $count . '" id="payremark_' . $count . '" value="%s">&nbsp;&nbsp;', $row->remark );
					$s .= '<img src="' . BASE_URL . 'images/close.png" onclick="delpaytime(this,\'' . $count . '\')" width="17" height="17" /><br /></div>';
					$count_array [] = $count;
					$count ++;
				}
			}
		}
		$s .= '</div><script>var pay_count = ' . $count . ';</script>';
		return str_replace ( array (
				'[PAYCOUNT]' 
		), array (
				empty ( $count_array ) ? ',' : ',' . implode ( ',', $count_array ) . ',' 
		), $s );
	}
	private function _get_edit_costinfo($costinfoids) {
		$ids = explode ( '^', $costinfoids );
		$s = '<input type="hidden" name="costcount" id="costcount" value="[COSTCOUNT]"/><br /><div id="costinfolist">';
		$count = 1;
		$count_array = array ();
		foreach ( $ids as $id ) {
			if (intval ( $id ) > 0) {
				$row = $this->db->get_row ( 'SELECT type,name,amount,yg,id FROM executive_costinfo WHERE id=' . intval ( $id ) );
				if ($row !== NULL) {
					$s .= '<div>';
					$s .= '<select name="costtype_' . $count . '" class="select">';
					$s .= '<option value="1" ' . (intval ( $row->type ) === 1 ? 'selected="selected"' : '') . '>媒介成本</option>';
					$s .= '<option value="2" ' . (intval ( $row->type ) === 2 ? 'selected="selected"' : '') . '>硬件成本</option>';
					$s .= '<option value="3" ' . (intval ( $row->type ) === 3 ? 'selected="selected"' : '') . '>搜索成本</option>';
					$s .= '<option value="4" ' . (intval ( $row->type ) === 4 ? 'selected="selected"' : '') . '>效果成本</option>';
					$s .= '<option value="6" ' . (intval ( $row->type ) === 6 ? 'selected="selected"' : '') . '>媒体公关成本（个人）</option>';
					$s .= '<option value="7" ' . (intval ( $row->type ) === 7 ? 'selected="selected"' : '') . '>客户返点</option>';
					$s .= '<option value="8" ' . (intval ( $row->type ) === 8 ? 'selected="selected"' : '') . '>外包成本（公司）</option>';
					$s .= '<option value="9" ' . (intval ( $row->type ) === 9 ? 'selected="selected"' : '') . '>媒体公关成本（公司）</option></select>&nbsp;&nbsp;';
					$s .= sprintf ( '金额：<input type="text" onblur="tjcost(this)" style="width:100px; text-align:right;height:20px;" class="validate[required,custom[money]] rb3" name="costamount_' . $count . '" id="costamount_' . $count . '" value="%s"> 元&nbsp;&nbsp;', $row->amount );
					$s .= sprintf ( '收款方全称：<input type="text" name="costname_' . $count . '" id="costname_' . $count . '" class="validate[required] text_new" value="%s" style="height:20px;width:300px;" onfocus="javascript:getSupplierName(this);">&nbsp;&nbsp;', $row->name );
					$s .= '预估：<select name="costyg_' . $count . '" class="select">';
					$s .= '<option value="0" ' . (intval ( $row->yg ) === 0 ? 'selected="selected"' : '') . '>否</option>';
					$s .= '<option value="1" ' . (intval ( $row->yg ) === 1 ? 'selected="selected"' : '') . '>是</option></select>&nbsp;&nbsp;';
					$s .= '<img src="../images/close.png" onclick="delcostinfo(this,\'' . $count . '\')" width="17" height="17" /><br /></div>';
					
					$count_array [] = $count;
					$count ++;
				}
			}
		}
		$s .= '</div><script>var cost_count = ' . $count . ';</script>';
		return str_replace ( array (
				'[COSTCOUNT]' 
		), array (
				empty ( $count_array ) ? ',' : ',' . implode ( ',', $count_array ) . ',' 
		), $s );
	}
	private function _get_cy_json($executive_id, $dep = 0) {
		$s = '';
		$arr = array ();
		if (! empty ( $executive_id )) {
			$results = $this->db->get_results ( 'SELECT b.supplier_name,c.category_name,d.media_short,a.*
FROM
(
SELECT id,ym,cost_amount,quote_amount,finance_cost_amount,finance_quote_amount,supplier_id,supplier_short_id,category_id,industry_id,paycost_id FROM executive_cy WHERE executive_id=' . $executive_id . ($dep > 0 ? ' AND is_support=1 AND support_dep=' . $dep : ' AND is_support=0 AND support_dep=0') . ') a
LEFT JOIN new_supplier b
ON a.supplier_id=b.id
LEFT JOIN new_supplier_category c
ON a.category_id=c.id
LEFT JOIN finance_supplier_short d
ON a.supplier_short_id=d.id
ORDER BY a.paycost_id,a.id,a.ym' ); // ORDER BY b.supplier_name,c.category_name,a.ym
			
			if ($results !== NULL) {
				$items = array ();
				$info = array ();
				$cost = array ();
				$quote = array ();
				foreach ( $results as $key => $value ) {
					/*
					 * $items[$value->supplier_name . '_' . $value->category_name][] = array(
					 * 'date' => $value->ym,
					 * 'quote' => $value->quote_amount,
					 * 'cost' => $value->cost_amount);
					 * $info[$value->supplier_name . '_' . $value->category_name] = array(
					 * 'supplier' => $value->supplier_name,
					 * 'deliverytype' => $value->category_name,
					 * 'supplier_short_id' => $value->supplier_short_id,
					 * 'industry_id' => $value->industry_id);
					 * $cost[$value->supplier_name . '_' . $value->category_name] += $value
					 * ->cost_amount;
					 * $quote[$value->supplier_name . '_' . $value->category_name] += $value
					 * ->quote_amount;
					 */
					$k = $value->supplier_id . '_' . $value->supplier_short_id . '_' . $value->category_id . '_' . $value->industry_id . '_' . (empty ( $value->paycost_id ) ? 0 : $value->paycost_id);
					$items [$k] [] = array (
							'date' => $value->ym,
							'quote' => $value->quote_amount,
							'cost' => $value->cost_amount 
					);
					$info [$k] = array (
							'supplier' => $value->supplier_name,
							'deliverytype' => $value->category_name,
							'supplier_short_id' => $value->supplier_short_id,
							'industry_id' => $value->industry_id 
					);
					$cost [$k] += $value->cost_amount;
					// $quote[$value->supplier_id . '_' . $value->supplier_short_id . '_' . $value->category_id. '_' . $value->industry_id] += $value
					// ->quote_amount;
				}
				
				$count = 1;
				foreach ( $info as $key => $v ) {
					$arr [$count] = array (
							'supplier' => $v ['supplier'],
							'type' => $v ['deliverytype'],
							'short_id' => $v ['supplier_short_id'],
							'industry_id' => $v ['industry_id'],
							'items' => array (
									$count => $items [$key] 
							),
							'cost' => $cost [$key] 
					);
					$count ++;
				}
			}
			
			if (! empty ( $arr )) {
				$s = $arr;
				$s = base64_encode ( json_encode ( $s ) );
			}
		}
		
		return $s;
	}
	private function _get_edit_costpayment_info($executive_id, $costpaymentinfoids, $dep = 0) {
		$ids = array ();
		if (! empty ( $costpaymentinfoids )) {
			$ids = explode ( '^', $costpaymentinfoids );
			$ids = Array_Util::my_remove_array_other_value ( $ids, array (
					NULL,
					'' 
			) );
		}
		
		/*
		 * $s = '<input type="hidden" name="costpaycount" id="costpaycount" value="[COSTPAYCOUNT]"/><input type="hidden" name="cy_json" id="cy_json" value="'
		 * . $this->_get_cy_json($executive_id, $dep)
		 * . '"/><br /><div id="costpaymentinfolist">';
		 */
		$s = '<input type="hidden" name="costpaycount" id="costpaycount" value="[COSTPAYCOUNT]"/><input type="hidden" name="cy_json" id="cy_json"/><br /><div id="costpaymentinfolist">';
		$count = 1;
		$count_array = array ();
		
		$results = NULL;
		if (! empty ( $ids )) {
			$results = $this->db->get_results ( 'SELECT payname,paytime,payamount,paytype,id,costinfoid FROM executive_paycost WHERE id IN (' . implode ( ',', $ids ) . ') ORDER BY id' );
		}
		
		if ($results !== NULL) {
			foreach ( $results as $result ) {
				$s .= '<div>';
				// if (CY_ON) {
				$s .= '收款方全称：<input type="text" style="width:300px;height:20px;" class="validate[required] text_new" name="costpay_' . $count . '" id="costpay_' . $count . '" onfocus="javascript:getSupplierName(this);" value="' . $result->payname . '">&nbsp;&nbsp;';
				// } else {
				// $s .= '收款方全称：<input type="text" style="width:100px;" class="validate[required]" name="costpay_'
				// . $count . '" id="costpay_' . $count . '" value="'
				// . $result->payname . '">&nbsp;&nbsp;';
				// }
				$s .= sprintf ( '时间：<input type="text" onclick="WdatePicker();" style="width:100px;" class="validate[required] text Wdate" name="costpaytime_' . $count . '" id="costpaytime_' . $count . '" value="%s">&nbsp;&nbsp;', $result->paytime );
				$s .= sprintf ( '金额：<input type="text" onblur="tjcostpayment(this)" style="width:100px; text-align:right;height:20px;" class="validate[required,custom[money]]" name="costpayamount_' . $count . '" id="costpayamount_' . $count . '" value="%s"> 元&nbsp;&nbsp;', $result->payamount );
				$s .= '收票类型：<select class="select" name="costpaytype_' . $count . '">';
				$s .= '<option value="1"' . (intval ( $result->paytype ) === 1 ? ' selected="selected"' : '') . '>广告</option>';
				$s .= '<option value="2"' . (intval ( $result->paytype ) === 2 ? ' selected="selected"' : '') . '>服务</option></select>&nbsp;&nbsp;';
				$s .= '<img src="' . BASE_URL . 'images/close.png" onclick="delcostpayment(this,\'' . $count . '\')" width="17" height="17" /><br /></div>';
				
				$count_array [] = $count;
				$count ++;
			}
		}
		$s .= '</div><script>var cost_pay_count = ' . $count . ';</script>';
		
		$s = str_replace ( array (
				'[COSTPAYCOUNT]' 
		), array (
				empty ( $count_array ) ? ',' : ',' . implode ( ',', $count_array ) . ',' 
		), $s );
		return $s;
	}
	private function _get_edit_servicecf($servicecf,$billtype) {
		$s = '<input type="hidden" name="servicecf" id="servicecf" value="[SERVICECFCOUNT]"/><br/><div id="servicecflist">';
		$count = 1;
		$allamount = 0;
		$tax = 0;
		$count_array = array ();
		
		$billtype = intval($billtype);
		if (! empty ( $servicecf )) {
			$servicecf = explode ( '|', $servicecf );
			foreach ( $servicecf as $scf ) {
				$scf = explode ( '^', $scf );
				$s .= '<div><select name="servercf_type_' . $count . '" class="select">';
				
				if($billtype === 2){
					foreach ($GLOBALS['defined_servicecf_type'] as $key=>$value){
						$s  .= '<option value="' . $key . '" ' . (intval ( $scf [0] ) === intval($key) ? 'selected="selected"' : '') . '>' . $value . '</option>';
					}
				}else if($billtype === 1){
					$s .= '<option value="0">无</option>';
				}
				/*
				$s .= '<option value="1" ' . (intval ( $scf [0] ) === 1 ? 'selected="selected"' : '') . '>策略费用</option>';
				$s .= '<option value="2" ' . (intval ( $scf [0] ) === 2 ? 'selected="selected"' : '') . '>创意/制作费用</option>';
				$s .= '<option value="3" ' . (intval ( $scf [0] ) === 3 ? 'selected="selected"' : '') . '>SMC(Social)费用</option>';
				$s .= '<option value="4" ' . (intval ( $scf [0] ) === 4 ? 'selected="selected"' : '') . '>SEO费用</option>';
				$s .= '<option value="5" ' . (intval ( $scf [0] ) === 5 ? 'selected="selected"' : '') . '>SEM费用</option>';
				$s .= '<option value="7" ' . (intval ( $scf [0] ) === 7 ? 'selected="selected"' : '') . '>IDC费用</option>';
				$s .= '<option value="8" ' . (intval ( $scf [0] ) === 8 ? 'selected="selected"' : '') . '>技术开发费用</option>';
				$s .= '<option value="9" ' . (intval ( $scf [0] ) === 9 ? 'selected="selected"' : '') . '>系统监测费用</option>';
				$s .= '<option value="10" ' . (intval ( $scf [0] ) === 10 ? 'selected="selected"' : '') . '>媒介服务费用</option>';
				$s .= '<option value="11" ' . (intval ( $scf [0] ) === 11 ? 'selected="selected"' : '') . '>税金费用</option>';
				$s .= '<option value="13" ' . (intval ( $scf [0] ) === 13 ? 'selected="selected"' : '') . '>客户服务费用</option>';
				$s .= '<option value="14" ' . (intval ( $scf [0] ) === 14 ? 'selected="selected"' : '') . '>公关费用</option>';
				$s .= '<option value="12" ' . (intval ( $scf [0] ) === 12 ? 'selected="selected"' : '') . '>其他费用</option>';
				$s .= '<option value="15" ' . (intval ( $scf [0] ) === 15 ? 'selected="selected"' : '') . '>媒介费用</option>';
				*/
				$s .= '</select>&nbsp;&nbsp;';
				$s .= sprintf ( '金额：<input name="servercf_amount_' . $count . '" id="servercf_amount_' . $count . '" type="text" style="width:100px; text-align:right;height:20px;" class="validate[required,custom[money]] rb3" onblur="tjservicecf(this)" value="%s"/> 元&nbsp;&nbsp;', $scf [1] );
				$s .= sprintf ( '备注：<input name="servercf_remark_' . $count . '" id="servercf_remark_' . $count . '" type="text" width="150" style="height:20px;" value="%s">&nbsp;&nbsp;', $scf [2] );
				$s .= '<img src="../images/close.png" onclick="delservicecf(this,\'' . $count . '\')" width="17" height="17" /></div>';
				$allamount += $scf [1];
				
				$count_array [] = $count;
				$count ++;
			}
		}
		
		$s .= '</div><script>var service_cf_count = ' . $count . ';</script>';
		$s = str_replace ( array (
				'[SERVICECFCOUNT]' 
		), array (
				empty ( $count_array ) ? ',' : ',' . implode ( ',', $count_array ) . ',' 
		), $s );
		$allamount = number_format ( $allamount, 2, '.', '' );
		$tax_rate = 1;
		if($billtype === 2){
			$tax_rate = FW_TAX_RATE;
		}else if($billtype === 1){
			$tax_rate = GG_TAX_RATE;
		}
		$tax = number_format ( $allamount * $tax_rate, 2, '.', '' );
		return array (
				$s,
				$allamount,
				$tax 
		);
	}
	private function _get_exist_supportdep($support, $newsupport) {
		$dep = Dep::getInstance ();
		
		$s = '';
		if (empty ( $support )) {
			return $s;
		}
		
		$_newsupport = array ();
		if (! empty ( $newsupport )) {
			$_tmps = explode ( '|', $newsupport );
			foreach ( $_tmps as $_tmp ) {
				$_newsupport [] = intval ( reset ( explode ( '^', $_tmp ) ) );
			}
		}
		
		$_supports = explode ( '|', $support );
		foreach ( $_supports as $_support ) {
			$_support = explode ( '^', $_support );
			$depid = reset ( $_support );
			$tmpdep = $dep [$depid];
			$s .= '<li><input name="altersupport[]" type="checkbox" class="checkbox" value="' . $depid . '" ' . (in_array ( intval ( $depid ), $_newsupport, TRUE ) ? 'checked="checked" disabled="disabled"' : '') . '/><label>' . $tmpdep [1] . $tmpdep [0] . '</label></li>';
		}
		
		return empty ( $s ) ? '无' : $s;
	}
	private function _get_alter_supportdep($support, $dep, $newsupport) {
		$_support = array ();
		$_newsupport = array ();
		$s = '';
		$citys = City::getInstance ();
		$deps = Dep::getInstance ();
		$new_deps = array ();
		foreach ( $deps as $did => $dp ) {
			if (intval ( $dp [3] ) === 1) {
				$new_deps [$dp [2]] [] = array (
						'id' => $did,
						'depname' => $dp [0] 
				);
			}
		}
		
		if (! empty ( $support )) {
			$_tmps = explode ( '|', $support );
			foreach ( $_tmps as $_tmp ) {
				$_tmp = explode ( '^', $_tmp );
				$_support [] = intval ( reset ( $_tmp ) );
			}
		}
		
		if (! empty ( $newsupport )) {
			$_tmps = explode ( '|', $newsupport );
			foreach ( $_tmps as $_tmp ) {
				$_tmp = explode ( '^', $_tmp );
				$_newsupport [] = intval ( reset ( $_tmp ) );
			}
		}
		
		if (! empty ( $citys )) {
			foreach ( $citys as $cityid => $cityname ) {
				$s .= sprintf ( '<div>%s:', $cityname );
				$tmpdeps = $new_deps [$cityid];
				foreach ( $tmpdeps as $tmpdep ) {
					if (intval ( $dep ) !== intval ( $tmpdep ['id'] ) && ! in_array ( intval ( $tmpdep ['id'] ), $_support, TRUE )) {
						$s .= ' <input name="support[]" type="checkbox" class="checkbox" value="' . intval ( $tmpdep ['id'] ) . '" ' . (in_array ( intval ( $tmpdep ['id'] ), $_newsupport, TRUE ) ? 'checked="checked"' : '') . '/>' . $tmpdep ['depname'] . ' ';
					}
				}
				$s .= '</div>';
			}
		}
		
		return $s;
	}
	public function alter_executive() {
		if ($this->validate_form_value ( 'alter' )) {
			$success = TRUE;
			$error = '';
			$this->db->query ( 'BEGIN' );
			$supports = array ();
			
			$pid_arr = explode ( '-', $this->pid );
			$crow = $this->db->get_row ( 'SELECT billtype FROM contract_cus WHERE cid="' . strtoupper ( $pid_arr [0] ) . '"' );
			if ($crow === NULL) {
				$success = FALSE;
				$error = '所属合同不存在';
			} else {
				$payamount = 0;
				$paycount_arrays = $this->paycount_array;
				foreach ( $paycount_arrays as $paycount_array ) {
					$payamount += $paycount_array ['amount'];
				}
				// $payamount = round($payamount / 1.0683, 2);
				$payamount = round ( $payamount, 2 );
				
				if($payamount > 0){
					if (empty ( $this->servercf_array )) {
						$success = FALSE;
						$error = '税前、税费金额需要拆分';
					} else {
						$tax_rate = 1;
						if(intval ( $crow->billtype ) === 2){
							$tax_rate = 1 + FW_TAX_RATE;
						}else if(intval ( $crow->billtype ) === 1){
							$tax_rate = 1 + GG_TAX_RATE;
						}
						
						$servicecfamount = 0;
						
						$servercf_arrays = $this->servercf_array;
						foreach ( $servercf_arrays as $servercf_array ) {
							$servicecfamount += $servercf_array ['amount'];
						}
						
						if (round ( abs ( round ( $payamount / $tax_rate, 2 ) - $servicecfamount ), 2 ) > 0.01) {
							$success = FALSE;
							$error = '合同约定付款金额与服务金额不一致！';
						}
					}
				}
				
				/*
				if (intval ( $crow->billtype ) === 2 && $payamount > 0) {
					if (empty ( $this->servercf_array )) {
						$success = FALSE;
						$error = '该合同为服务合同，必须进行付款金额的拆分！';
					} else {
						$servicecfamount = 0;
						
						$servercf_arrays = $this->servercf_array;
						foreach ( $servercf_arrays as $servercf_array ) {
							$servicecfamount += $servercf_array ['amount'];
						}
						
						if (round ( abs ( round ( $payamount / 1.0683, 2 ) - $servicecfamount ), 2 ) > 0.01) {
							$success = FALSE;
							$error = '合同约定付款金额与服务金额不一致！';
						}
					}
				}
				*/
				
				if ($success && $payamount > 0) {
					$cy_amount_array = $this->cy_amount_array;
					$cy_amount = 0;
					// $time_isok = TRUE;
					if (! empty ( $cy_amount_array )) {
						foreach ( $cy_amount_array as $caa ) {
							$cy_amount += $caa ['amount'];
							/*
							 * if ($time_isok) {
							 * if (strtotime($caa['time'])
							 * < strtotime(
							 * date('Y-m',
							 * strtotime(
							 * $this
							 * ->starttime)))
							 * || strtotime($caa['time'])
							 * > strtotime(
							 * date('Y-m',
							 * strtotime(
							 * $this
							 * ->endtime)))) {
							 * $time_isok = FALSE;
							 * }
							 * }
							 */
						}
					}
					
					$cy_amount = round ( $cy_amount, 2 );
					if ($cy_amount !== $payamount) {
						$success = FALSE;
						$error = '执行金额拆月总和与合同约定付款金额不一致';
					} // else if (!$time_isok) {
						  // $success = FALSE;
						  // $error = '拆月日期应该在项目执行期内';
						  // }
				}
				
				if ($success && $payamount > 0) {
					// 检验开票金额
					$inv = new Invoice ();
					$sum_inv = $inv->getSumPidInvoice ( $this->pid );
					if ($payamount < round ( $sum_inv, 2 )) {
						$success = FALSE;
						$error = '该执行单开票金额已大于执行金额，请调整开票金额并由财务部审核通过后，才可以修改该执行单';
					}
					unset ( $inv );
				}
				
				if ($success && CUSTOMER_SAFETY_ON) {
					$cusrow = $this->db->get_row ( 'SELECT customer_id FROM v_cid_customer WHERE cid="' . strtoupper ( $pid_arr [0] ) . '"' );
					if ($cusrow === NULL) {
						$success = FALSE;
						$error = '该客户暂时未购买保险额度，无法变更执行单，请联系财务部Alex';
					} else {
						if ($paytime_amount > 0 && intval ( $this->execompany ) === 3) { // 只计算新网迈
						                                                                 // 校验系统客户保险额度
							$cus = new Customer ( array (
									'customer_id' => intval ( $cusrow->customer_id ) 
							) );
							$remainder = $cus->compute_remainder_safety ( $this->pid );
							unset ( $cus );
							
							if ($payamount > $remainder) {
								$success = FALSE;
								$error = '执行单金额大于该客户剩余保险额度，无法变更执行单，请联系财务部Alex';
							}
						}
					}
				}
				
				if ($success) {
					$pidinfo = array ();
					$results = $this->db->get_results ( 'SELECT a.*,FROM_UNIXTIME(a.time) AS tt,b.contractname,b.cusname,b.contractamount,b.contractcontent FROM executive a LEFT JOIN contract_cus b ON a.cid=b.cid WHERE a.pid="' . $this->pid . '" AND a.isok!=-1 ORDER BY a.id' );
					if ($results !== NULL) {
						foreach ( $results as $result ) {
							$pidinfo [] = $result;
						}
					}
					if (empty ( $pidinfo )) {
						$success = FALSE;
						$error = '执行单号有误';
					} else {
						$row = end ( $pidinfo );
						
						if (( int ) ($row->is_closed) === 1) {
							$success = FALSE;
							$error = '执行单已关闭，无法变更，详情请联系财务部';
						} else {
							$altersupport = $this->altersupport_array;
							// var_dump($altersupport);
							
							// $finance_cy = $this->_get_finance_cy($row->id);
							
							if (intval ( $row->isok ) === 1) {
								$_support = User::get_support_array ( $row->support );
								$fields_str = 'pid,dep,actor,costinfoids,cost,costpaymentinfoids,costpayment,cycostinfoids,remark,dids,nsid,time,step,user,isalter,isok,msgid,pcid,audittime,statusname';
								
								foreach ( $_support as $depid => $depinfoid ) {
									$insert_result = $this->db->query ( 'INSERT INTO executive_dep(' . $fields_str . ') SELECT ' . $fields_str . ' FROM executive_dep WHERE id=' . intval ( $depinfoid ) );
									if ($insert_result === FALSE) {
										$success = FALSE;
										$error = '执行单变更失败，错误代码1';
										break;
									} else {
										$id = $this->db->insert_id;
										if (in_array ( $depid, $altersupport )) {
											$update_result = $this->db->query ( 'UPDATE executive_dep SET step=0,isok=0 where id=' . intval ( $id ) );
											if ($update_result === FALSE) {
												$success = FALSE;
												$error = '执行单变更失败，错误代码2';
												break;
											}
										}
									}
									$supports [] = sprintf ( '%s^%s', $depid, $id );
								}
								
								if ($success) {
									// 分配新增加支持部门入表，得到支持部门内容ID
									$support = $this->support_array;
									if (! empty ( $support )) {
										foreach ( $support as $s ) {
											$insert_result = $this->db->query ( 'INSERT INTO executive_dep(pid,dep) VALUE("' . $this->pid . '",' . $s . ')' );
											if ($insert_result === FALSE) {
												$success = FALSE;
												$error = '执行单变更失败，错误代码3';
												break;
											} else {
												$supports [] = sprintf ( '%s^%s', $s, $this->db->insert_id );
											}
										}
									}
								}
							} else {
								$_newsupport = User::get_support_array ( $row->support );
								// var_dump($_newsupport);
								foreach ( $_newsupport as $key => $value ) {
									$update_result = $this->db->query ( 'UPDATE executive_dep SET step=0,isok=0 WHERE id=' . intval ( $value ) );
									if ($update_result === FALSE) {
										$success = FALSE;
										$error = '执行单变更失败，错误代码4';
										break;
									} else {
										$supports [] = sprintf ( '%s^%s', $key, $value );
									}
								}
								
								if ($success) {
									// 分配新增加支持部门入表，得到支持部门内容ID
									$support = $this->support_array;
									// var_dump($support);
									if (! empty ( $support )) {
										foreach ( $support as $s ) {
											if (! array_key_exists ( $s, $_newsupport )) {
												// $id = $_newsupport[$s];
												// } else {
												$insert_result = $this->db->query ( 'INSERT INTO executive_dep(pid,dep) VALUE("' . $this->pid . '",' . $s . ')' );
												if ($insert_result === FALSE) {
													$success = FALSE;
													$error = '执行单变更失败，错误代码5';
													break;
												} else {
													$id = $this->db->insert_id;
												}
												$supports [] = sprintf ( '%s^%s', $s, $id );
											}
										}
									}
									// var_dump($supports);
								}
							}
							
							if ($success) {
								$allcost = 0;
								$sup_yg = 0;
								$isyg = 0;
								foreach ( $supports as $sup ) {
									$ed = $this->db->get_row ( 'SELECT cost,costinfoids FROM executive_dep WHERE id=' . intval ( end ( explode ( '^', $sup ) ) ) );
									if ($ed !== NULL) {
										$cost = $ed->cost;
										$cost_info_id = $ed->costinfoids;
									} else {
										$cost = 0;
										$cost_info_id = NULL;
									}
									
									if ($cost_info_id !== NULL) {
										$ids = explode ( '^', $cost_info_id );
										foreach ( $ids as $id ) {
											$ygrow = $this->db->get_row ( 'SELECT yg FROM executive_costinfo WHERE id=' . intval ( $id ) );
											if ($ygrow !== NULL) {
												$sup_yg += $ygrow->yg;
											}
										}
									}
									$allcost += $cost;
								}
								if ($sup_yg > 0 && $isyg === 0) {
									$isyg = 1;
								}
								
								// 合同约定付款日期
								$paycount_arrays = $this->paycount_array;
								$paytimeinfoids = array ();
								$amount = 0;
								if (! empty ( $paycount_arrays )) {
									foreach ( $paycount_arrays as $paycount_array ) {
										$insert_result = $this->db->query ( 'INSERT INTO executive_paytime(pid,paytime,amount,billtype,remark,time) VALUE("","' . $paycount_array ['time'] . '",' . $paycount_array ['amount'] . ',"' . $crow->billtype . '","' . $paycount_array ['remark'] . '",' . time () . ')' );
										if ($insert_result === FALSE) {
											$success = FALSE;
											$error = '执行单变更失败，错误代码6';
											break;
										} else {
											$paytimeinfoids [] = $this->db->insert_id;
											$amount += $paycount_array ['amount'];
										}
									}
								}
								
								if ($success) {
									// 成本明细
									$costcount_arrays = $this->costcount_array;
									$costinfoids = array ();
									$cost = 0;
									$cost_yg = 0;
									if (! empty ( $costcount_arrays )) {
										foreach ( $costcount_arrays as $costcount_array ) {
											$supplier_id = 0;
											if (NEW_SUPPLIER_ON) {
												// 判断选取的供应商是否存在在
												$supplier_id = $this->db->get_var ( 'SELECT id FROM new_supplier WHERE supplier_name="' . $costcount_array ['name'] . '" AND isok=1' );
												if ($supplier_id <= 0) {
													$success = FALSE;
													$error = '供应商【' . $costcount_array ['name'] . '】不存在或已撤销';
													break;
												}
											}
											$insert_result = $this->db->query ( 'INSERT INTO executive_costinfo(type,amount,name,yg,time,supplier_id) VALUE("' . $costcount_array ['type'] . '",' . $costcount_array ['amount'] . ',"' . $costcount_array ['name'] . '","' . $costcount_array ['yg'] . '",' . time () . ',' . $supplier_id . ')' );
											if ($insert_result === FALSE) {
												$success = FALSE;
												$error = '执行单变更失败，错误代码7';
												break;
											} else {
												$costinfoids [] = $this->db->insert_id;
												$cost += $costcount_array ['amount'];
												if ($cost_yg === 0 && $costcount_array ['yg'] > 0) {
													$cost_yg = 1;
												}
											}
										}
									}
									if ($cost_yg > 0 && $isyg === 0) {
										$isyg = 1;
									}
									
									$allcost += $cost;
									
									if ($success) {
										// 成本支付明细
										$costpaycount_arrays = $this->costpaycount_array;
										$costpaymentinfoids = array ();
										$cost_payment = array ();
										$costpayment = 0;
										$paycost_id = array ();
										if (! empty ( $costpaycount_arrays )) {
											foreach ( $costpaycount_arrays as $kkk => $costpaycount_array ) {
												$supplier_id2 = 0;
												if (NEW_SUPPLIER_ON) {
													// 判断选取的供应商是否存在在
													$supplier_id2 = $this->db->get_var ( 'SELECT id FROM new_supplier WHERE supplier_name="' . $costpaycount_array ['name'] . '" AND isok=1' );
													if ($supplier_id2 <= 0) {
														$success = FALSE;
														$error = '供应商【' . $costpaycount_array ['name'] . '】不存在或已撤销';
														break;
													}
												}
												
												$insert_result = $this->db->query ( 'INSERT INTO executive_paycost(payname,paytime,payamount,paytype,time,supplier_id) VALUE("' . $costpaycount_array ['name'] . '","' . $costpaycount_array ['time'] . '",' . $costpaycount_array ['amount'] . ',"' . $costpaycount_array ['type'] . '",' . time () . ',' . $supplier_id2 . ')' );
												if ($insert_result === FALSE) {
													$success = FALSE;
													$error = '执行单变更失败，错误代码8';
													break;
												} else {
													$idd = $this->db->insert_id;
													$costpaymentinfoids [] = $idd;
													$costpayment += $costpaycount_array ['amount'];
													$cost_payment ['costpaymentinfoids'] [] = $this->db->insert_id;
													$cost_payment ['costpayname'] [] = $costpaycount_array ['name'];
													$paycost_id [$kkk] = $idd;
												}
											}
										}
										
										if ($success) {
											if (intval ( $row->isok ) === 1) {
												$sql = 'INSERT INTO executive(pid,cid,city,dep,team,type,name,company,dids,principal,actor,starttime,endtime,paytimeinfoids,servicecf,amount,allcost,isyg,costinfoids,cost,costpaymentinfoids,costpayment,remark,support,time,user,step,pcid,isalter) VALUE("' . $this->pid . '","' . strtoupper ( $pid_arr [0] ) . '",' . $row->city . ',' . $row->dep . ',' . $row->team . ',' . $this->exetype . ',"' . $this->projectname . '",' . $this->execompany . ',"' . $this->dids . '",' . $this->principal . ',"' . $this->actor . '","' . $this->starttime . '","' . $this->endtime . '","' . implode ( '^', $paytimeinfoids ) . '","' . User::combine_array ( $this->servercf_array ) . '","' . $amount . '","' . $allcost . '",' . $isyg . ',"' . implode ( '^', $costinfoids ) . '","' . $cost . '","' . implode ( '^', $costpaymentinfoids ) . '","' . $costpayment . '","' . $this->remark . '","' . implode ( '|', $supports ) . '",' . time () . ',' . $this->getUid () . ',1,' . $this->process . ',' . count ( $pidinfo ) . ')';
											} else {
												$sql = 'UPDATE executive SET type=' . $this->exetype . ',name="' . $this->projectname . '",company=' . $this->execompany . ',dids="' . $this->dids . '",principal=' . $this->principal . ',actor="' . $this->actor . '",starttime="' . $this->starttime . '",endtime="' . $this->endtime . '",paytimeinfoids="' . implode ( '^', $paytimeinfoids ) . '",servicecf="' . User::combine_array ( $this->servercf_array ) . '",amount="' . $amount . '",allcost="' . $allcost . '",isyg=' . $isyg . ',costinfoids="' . implode ( '^', $costinfoids ) . '",cost="' . $cost . '",costpaymentinfoids="' . implode ( '^', $costpaymentinfoids ) . '",costpayment="' . $costpayment . '",remark="' . $this->remark . '",support="' . implode ( '|', $supports ) . '",user=' . $this->getUid () . ',step=1,pcid=' . $this->process . ' WHERE id=' . intval ( $row->id );
											}
											$sql_result = $this->db->query ( $sql );
											
											if ($sql_result === FALSE) {
												$success = FALSE;
												$error = '执行单变更失败，错误代码9';
											} else {
												if (intval ( $row->isok ) === 1) {
													$pid_id = $this->db->insert_id;
												} else {
													$pid_id = $row->id;
												}
												
												// 执行金额拆月
												if (intval ( $row->isok ) !== 1) {
													$delete_result = $this->db->query ( 'DELETE FROM executive_amount_cy WHERE executive_id=' . $pid_id . ' AND  pid="' . $this->pid . '"' );
													if ($delete_result === FALSE) {
														$success = FALSE;
														$error = '变更执行金额拆月数据失败，错误代码1';
													}
												}
												if ($success) {
													$cysql = array ();
													foreach ( $this->cy_amount_array as $carray ) {
														$ym = $carray ['time'];
														$cysql [] = '(' . $pid_id . ',"' . $this->pid . '",' . reset ( explode ( '-', $ym ) ) . ',' . end ( explode ( '-', $ym ) ) . ',"' . $ym . '",' . $carray ['amount'] . ',' . $carray ['amount'] . ')';
													}
													if (! empty ( $cysql )) {
														$insert_result = $this->db->query ( 'INSERT INTO executive_amount_cy(executive_id,pid,year,month,ym,quote_amount,finance_quote_amount) VALUES' . implode ( ',', $cysql ) );
														if ($insert_result === FALSE) {
															$success = FALSE;
															$error = '变更执行金额拆月数据失败，错误代码2';
														}
													}
												}
												
												if ($success) {
													// 拆月数据
													$cy_json = json_decode ( base64_decode ( $this->cy_json ) );
													$cy_json = ( array ) $cy_json;
													
													if (! empty ( $cy_json )) {
														$sql = array ();
														
														foreach ( $cy_json as $cy ) {
															$cy = ( array ) $cy;
															$cyitems = $cy ['items'];
															$cyitems = ( array ) $cyitems;
															foreach ( $cyitems as $kk => $cyitem ) {
																foreach ( $cyitem as $cyitemem ) {
																	$sid = 0;
																	$caid = 0;
																	if (NEW_SUPPLIER_ON) {
																		$sggs = Supplier::getSupplierInstance ();
																		foreach ( $sggs as $key => $value ) {
																			if ($value === $cy ['supplier']) {
																				$sid = $key;
																				break;
																			}
																		}
																		
																		if ($sid <= 0) {
																			$success = FALSE;
																			$error = '供应商【' . $cy ['supplier'] . '】不存在';
																			break 3;
																		}
																		
																		$sg = Supplier::getInstance ();
																		// $sg_s = $sg['supplier'];
																		
																		/*
																		 * foreach ($sg_s as $k => $v) {
																		 * if ($v['sn']
																		 * === $cy['supplier']) {
																		 * $sid = $k;
																		 * break;
																		 * }
																		 * }
																		 */
																		
																		if ($sid > 0) {
																			$sg_c = $sg ['supplier_category'] [$sid];
																			foreach ( $sg ['supplier_category'] [$sid] as $vv ) {
																				if ($sg ['category'] [$vv] === $cy ['type']) {
																					$caid = $vv;
																					break;
																				}
																			}
																		}
																	}
																	
																	$sql [] = '(' . $pid_id . ',"' . $this->pid . '","' . $cy ['supplier'] . '","' . $cy ['type'] . '",' . reset ( explode ( '-', $cyitemem->date ) ) . ',' . end ( explode ( '-', $cyitemem->date ) ) . ',"' . $cyitemem->date . '",0,0,' . $cyitemem->cost . ',' . $cyitemem->cost . ',' . (empty ( $cy ['short_id'] ) ? 0 : $cy ['short_id']) . ',' . $sid . ',' . $caid . ',' . (empty ( $cy ['industry_id'] ) ? 0 : $cy ['industry_id']) . ',' . (empty ( $paycost_id [$kk] ) ? 0 : $paycost_id [$kk]) . ')';
																}
															}
														}
														// var_dump($sql);
														
														if ($success) {
															if (intval ( $row->isok ) !== 1) {
																// 删除
																$insert_result = $this->db->query ( 'DELETE FROM executive_cy WHERE executive_id=' . $pid_id . ' AND pid="' . $this->pid . '" AND is_support=0 AND support_dep=0' );
																
																if ($insert_result === FALSE) {
																	$success = FALSE;
																	$error = '记录拆月数据失败，错误代码1';
																}
															}
														}
														
														if ($success) {
															if (! empty ( $sql )) {
																
																// if ($success) {
																// var_dump($sql);
																
																$insert_result = $this->db->query ( 'INSERT INTO executive_cy(executive_id,pid,payname,deliverytype,year,month,ym,quote_amount,finance_quote_amount,cost_amount,finance_cost_amount,supplier_short_id,supplier_id,category_id,industry_id,paycost_id) VALUES ' . implode ( ',', $sql ) );
																
																if ($insert_result === FALSE) {
																	$success = FALSE;
																	$error = '记录拆月数据失败，错误代码2';
																}
																// }
															}
														}
													}
												}
												
												if ($success) {
													if (intval ( $row->isok ) === 1) {
														// 支持部门成本拆月
														foreach ( $_support as $depid => $depinfoid ) {
															$insert_result = $this->db->query ( 'INSERT INTO executive_cy(executive_id,pid,payname,deliverytype,year,month,ym,quote_amount,finance_quote_amount,cost_amount,finance_cost_amount,paycost_id,is_support,support_dep,supplier_short_id,supplier_id,category_id,industry_id) SELECT ' . $pid_id . ',pid,payname,deliverytype,year,month,ym,quote_amount,finance_quote_amount,cost_amount,finance_cost_amount,paycost_id,is_support,support_dep,supplier_short_id,supplier_id,category_id,industry_id FROM executive_cy WHERE executive_id=' . $row->id . ' AND pid="' . $this->pid . '" AND is_support=1 AND support_dep=' . $depid );
															
															if ($insert_result === FALSE) {
																$success = FALSE;
																$error = '记录支持部门成本拆月数据失败';
																break;
															}
														}
													}
												}
												
												if ($success) {
													$insert_result = $this->do_executive_log ( $this->pid, '', '执行单发起人', '<font color=\'#99cc00\'>变更执行单</font>' );
													if ($insert_result ['status'] === 'error') {
														$success = FALSE;
														$error = '执行单变更失败，错误代码10';
													} else {
														if (intval ( $row->isok ) === 1) {
															$update_result = $this->db->query ( 'UPDATE executive SET audittime=' . time () . ' WHERE id=' . intval ( $pid_id ) );
															if ($update_result === FALSE) {
																$success = FALSE;
																$error = '执行单变更失败，错误代码11';
															}
														}
													}
												}
											}
										}
									}
								}
							}
						}
					}
				}
			}
			
			if ($success) {
				$this->db->query ( 'COMMIT' );
			} else {
				$this->db->query ( 'ROLLBACK' );
			}
			return array (
					'status' => $success ? 'success' : 'error',
					'message' => $success ? '变更执行单成功' : $error 
			);
		}
		return array (
				'status' => 'error',
				'message' => $this->errors 
		);
	}
	public function userchange_executive_all() {
		if (! $this->getHas_manager_executive_permission ()) {
			return array (
					'status' => 'error',
					'message' => NO_RIGHT_TO_DO_THIS 
			);
		}
		if ($this->validate_form_value ( 'userchange_all' )) {
			$success = TRUE;
			$error = '';
			$this->db->query ( 'BEGIN' );
			
			$result1 = $this->db->query ( 'UPDATE executive SET user=' . intval ( $this->principal ) . ' WHERE user=' . intval ( $this->user ) );
			$result2 = $this->db->query ( 'UPDATE executive SET principal=' . intval ( $this->principal ) . ' WHERE principal=' . intval ( $this->user ) );
			$result3 = $this->db->query ( 'UPDATE finance_invoice SET user=' . intval ( $this->principal ) . ' WHERE user=' . intval ( $this->user ) );
			$result4 = $this->db->query ( 'UPDATE finance_invoice_list SET user=' . intval ( $this->principal ) . ' WHERE user=' . intval ( $this->user ) );
			$result5 = $this->db->query ( 'UPDATE contract_cus SET contactperson=' . intval ( $this->principal ) . ' WHERE contactperson=' . intval ( $this->user ) );
			
			if ($result1 === FALSE || $result2 === FALSE || $result3 === FALSE || $result4 === FALSE || $result5 === FALSE) {
				$success = FALSE;
				$error = '执行单人员变更失败';
			}
			
			if ($success) {
				$this->db->query ( 'COMMIT' );
			} else {
				$this->db->query ( 'ROLLBACK' );
			}
			
			return array (
					'status' => $success ? 'success' : 'error',
					'message' => $success ? '执行单人员变更成功' : $error 
			);
		}
		return array (
				'status' => 'error',
				'message' => $this->errors 
		);
	}
	public function userchange_executive() {
		if (! $this->getHas_manager_executive_permission ()) {
			return array (
					'status' => 'error',
					'message' => NO_RIGHT_TO_DO_THIS 
			);
		}
		if ($this->validate_form_value ( 'userchange' )) {
			$success = TRUE;
			$error = '';
			$this->db->query ( 'BEGIN' );
			
			$row = $this->db->get_row ( 'SELECT user FROM executive WHERE pid="' . $this->pid . '" ORDER BY time DESC LIMIT 1 FOR UPDATE' );
			if ($row !== NULL) {
				$update_result = $this->db->query ( 'UPDATE executive SET user=' . intval ( $this->user ) . ',principal=' . intval ( $this->principal ) . ' WHERE pid="' . $this->pid . '" ORDER BY time DESC LIMIT 1' );
				if ($update_result === FALSE) {
					$success = FALSE;
					$error = '执行单人员变更失败，错误代码1';
				}
				
				if ($success && intval ( $row->user ) !== intval ( $this->user )) {
					$islive = $this->db->get_var ( 'SELECT islive FROM  users WHERE uid=' . intval ( $row->user ) . ' FOR UPDATE' );
					if ($islive === '-1') {
						// 开票
						$update_result = $this->db->query ( 'UPDATE finance_invoice_list SET user=' . intval ( $this->user ) . ' WHERE user=' . intval ( $row->user ) . ' AND isok<>-1' );
						if ($update_result === FALSE) {
							$success = FALSE;
							$error = '执行单人员变更失败，错误代码2';
						} else {
							$update_result = $this->db->query ( 'UPDATE finance_invoice SET user=' . intval ( $this->user ) . ' WHERE user=' . intval ( $row->user ) . ' AND isok<>-1' );
							if ($update_result === FALSE) {
								$success = FALSE;
								$error = '执行单人员变更失败，错误代码3';
							}
						}
					}
				}
			} else {
				$success = FALSE;
				$error = '没有该执行单';
			}
			
			if ($success) {
				$this->db->query ( 'COMMIT' );
			} else {
				$this->db->query ( 'ROLLBACK' );
			}
			
			return array (
					'status' => $success ? 'success' : 'error',
					'message' => $success ? '执行单人员变更成功' : $error 
			);
		}
		return array (
				'status' => 'error',
				'message' => $this->errors 
		);
	}
	public function get_executive_add_html() {
		$buf = file_get_contents ( TEMPLATE_PATH . 'executive/executive_add.tpl' );
		$mates = $this->get_user_mates_select_html ();
		$tax_rate_select = array();
		foreach ($GLOBALS['defined_servicecf_type'] as $key=>$value){
			$tax_rate_select[] = array('id' => $key, 'name' => urlencode($value));
		}
		$tax_rate_select = urldecode(json_encode($tax_rate_select));
		return str_replace ( array (
				'[LEFT]',
				'[TOP]',
				'[VCODE]',
				'[CITYINFO]',
				'[PRINCIPAL]',
				'[ACTOR]',
				'[SUPPORTDEP]',
				'[PROCESSLIST]',
				'[VALIDATE_TYPE]',
				'[VALIDATE_SIZE]',
				'[CUSTOMERSAFETYON]',
				'[TOKEN]',
				'[SUPPLIERCATEGORYS]',
				'[CYON]',
				'[SUPPLIERSHORTS]',
				'[SUPPLIERINDUSTRYS]',
				'[TAXRATESELECT]',
				'[BASE_URL]' 
		), array (
				$this->get_left_html (),
				$this->get_top_html (),
				$this->get_vcode (),
				$this->get_user_city_info (),
				$mates,
				$mates,
				$this->get_support_dep ( array (), $this->getBelong_dep () ),
				$this->get_process_list (),
				implode ( ',', $GLOBALS ['defined_upload_validate_type'] ),
				UPLOAD_FILE_MAX_SIZE / (1024 * 1024),
				(CUSTOMER_SAFETY_ON ? 1 : 0),
				$this->get_token (),
				Supplier::getCategorysSelect (),
				(CY_ON ? 1 : 0),
				Supplier_Short::getSupplierShortSelect (),
				Supplier_Short::getIndustrySelect (),
				$tax_rate_select,
				BASE_URL 
		), $buf );
	}
	public function get_executive_info_html() {
		$buf = file_get_contents ( TEMPLATE_PATH . 'executive/executive_info.tpl' );
		return str_replace ( array (
				'[LEFT]',
				'[TOP]',
				'[VCODE]',
				'[PIDINFO]',
				'[ID]',
				'[PID]',
				'[D]',
				'[LOGLIST]',
				'[CHECKDIFFERENT]',
				'[CYON]',
				'[BASE_URL]' 
		), array (
				$this->get_left_html (),
				$this->get_top_html (),
				$this->get_vcode (),
				$this->get_executive_info (),
				$this->executive_id,
				$this->pid,
				$this->contrast,
				$this->get_log_list (),
				(intval ( $this->contrast ) === 1 ? 'checkdifferent();' : ''),
				(CY_ON ? 1 : 0),
				BASE_URL 
		), $buf );
	}
	public function get_executive_print_html() {
		if (! $this->getHas_check_executive_permission ()) {
			return User::no_permission ();
		}
		$buf = file_get_contents ( TEMPLATE_PATH . 'executive/executive_print.tpl' );
		return str_replace ( array (
				'[PIDINFO]',
				'[CYON]',
				'[BASE_URL]' 
		), array (
				$this->get_executive_info (),
				(CY_ON ? 1 : 0),
				BASE_URL 
		), $buf );
	}
	public function get_executive_audit_html() {
		$row = $this->db->get_row ( 'SELECT a.step,a.pcid,b.content,a.isalter,a.principal FROM executive a,process b WHERE a.id=' . intval ( $this->executive_id ) . ' AND a.pcid=b.id AND a.isok<>-1 AND b.islive=1' );
		if ($row === NULL) {
			return User::no_object ( '没有符合条件的记录' );
		}
		$content = explode ( '_', $row->content );
		$content = explode ( '^', $content [$row->step] );
		
		if (! in_array ( $content [2], $this->getPermissions (), TRUE )) {
			if ($content [0] === '项目负责人') {
				if (intval ( $this->getUid () ) !== intval ( $row->principal )) {
					return User::no_permission ();
				}
			} else {
				return User::no_permission ();
			}
		}
		$buf = file_get_contents ( TEMPLATE_PATH . 'executive/executive_audit.tpl' );
		return str_replace ( array (
				'[LEFT]',
				'[TOP]',
				'[VCODE]',
				'[PIDINFO]',
				'[REJECTSTEP]',
				'[LOGLIST]',
				'[PID]',
				'[EXEID]',
				'[CHECKDIFFERENT]',
				'[BASE_URL]' 
		), array (
				$this->get_left_html (),
				$this->get_top_html (),
				$this->get_vcode (),
				$this->get_executive_info (),
				$this->get_executive_reject_step (),
				$this->get_log_list (),
				$this->pid,
				$this->executive_id,
				(intval ( $row->isalter ) !== 0 ? 'checkdifferent();' : ''),
				BASE_URL 
		), $buf );
	}
	public function get_executive_cy_html() {
		$row = $this->db->get_row ( 'SELECT a.step,b.content,a.isalter,a.user,a.costpaymentinfoids,a.isok FROM executive a,process b WHERE a.id=' . intval ( $this->executive_id ) . ' AND a.pcid=b.id AND a.isok<>-1 AND b.islive=1' );
		if ($row === NULL) {
			return User::no_object ( '没有符合条件的记录' );
		} else if (intval ( $row->step ) > 0 && intval ( $row->user ) !== intval ( $this->getUid () )) {
			return User::no_permission ();
		}
		
		$buf = file_get_contents ( TEMPLATE_PATH . 'executive/executive_cy.tpl' );
		return str_replace ( array (
				'[LEFT]',
				'[TOP]',
				'[VCODE]',
				'[PIDINFO]',
				'[REJECTSTEP]',
				'[LOGLIST]',
				'[PID]',
				'[EXEID]',
				'[SUPPLIERS]',
				'[CYOVERLAY]',
				'[CYON]',
				'[BASE_URL]' 
		), array (
				$this->get_left_html (),
				$this->get_top_html (),
				$this->get_vcode (),
				$this->get_executive_info ( NULL, NULL, TRUE ),
				$this->get_executive_reject_step ( TRUE ),
				$this->get_log_list (),
				$this->pid,
				$this->executive_id,
				$this->_getCYSuppliers ( $this->executive_id ),
				$this->_get_cy_overlay ( $this->executive_id, $this->pid, $row->isok, $row->isalter ),
				(CY_ON ? 1 : 0),
				BASE_URL 
		), $buf );
	}
	public function executive_complete_cy() {
		$row = $this->db->get_row ( 'SELECT user,step FROM executive where id=' . intval ( $this->executive_id ) );
		if ($row === NULL) {
			return array (
					'status' => 'error',
					'message' => '没有该执行单' 
			);
		} else {
			if (intval ( $row->step ) > 0 && intval ( $row->user ) !== intval ( $this->getUid () )) {
				return array (
						'status' => 'error',
						'message' => NO_RIGHT_TO_DO_THIS 
				);
			}
		}
		
		if ($this->validate_form_value ( 'complete_cy' )) {
			$success = TRUE;
			$error = '';
			$this->db->query ( 'BEGIN' );
			
			// 成本支付明细
			$cost_payment = $this->_get_cost_payment_infos ();
			
			if ($cost_payment ['status'] === 'error') {
				$success = FALSE;
				$error = '填写完成拆月数据失败，错误代码1';
			} else {
				// 拆月
				$finance_cy = $this->_get_finance_cy ( intval ( $this->executive_id ) );
				
				$cy_json = json_decode ( base64_decode ( $this->cy_json ) );
				$cy_json = ( array ) $cy_json;
				if (! empty ( $cy_json )) {
					$count = 0;
					$sql = array ();
					
					$paycost_ids = $cost_payment ['costpaymentinfoids'];
					$paycost_names = $cost_payment ['costpayname'];
					
					foreach ( $cy_json as $k => $cy ) {
						$cy = ( array ) $cy;
						$cyitems = $cy ['items'];
						
						$difference_quote = 0;
						$difference_cost = 0;
						
						foreach ( $cyitems as $cyitem ) {
							// $finance_cy_quote = self::_get_finance_cy_amount($cyitem->date, $finance_cy[$paycost_names[$k - 1]][$cyitem->date]['quote'], $cyitem->quote);
							// $finance_cy_cost = self::_get_finance_cy_amount($cyitem->date, $finance_cy[$paycost_names[$k - 1]][$cyitem->date]['cost'], $cyitem->cost);
							
							$finance_cy_quote = self::_get_finance_cy_amount ( $cyitem->date, $finance_cy [$cy ['supplier'] . '_' . $cy ['type']] [$cyitem->date] ['quote'], $cyitem->quote );
							$finance_cy_cost = self::_get_finance_cy_amount ( $cyitem->date, $finance_cy [$cy ['supplier'] . '_' . $cy ['type']] [$cyitem->date] ['cost'], $cyitem->cost );
							
							if (date ( 'Y-m', time () ) === $cyitem->date) {
								$finance_quote = $finance_cy_quote ['show'] + $difference_quote;
								$finance_cost = $finance_cy_cost ['show'] + $difference_cost;
							} else {
								$finance_quote = $finance_cy_quote ['show'];
								$finance_cost = $finance_cy_cost ['show'];
								$difference_quote += $finance_cy_quote ['difference'];
								$difference_cost += $finance_cy_cost ['difference'];
							}
							$sql [] = '(' . intval ( $this->executive_id ) . ',"' . $this->pid . '","' . $cy ['supplier'] . '","' . $cy ['type'] . '",' . reset ( explode ( '-', $cyitem->date ) ) . ',' . end ( explode ( '-', $cyitem->date ) ) . ',"' . $cyitem->date . '",' . $cyitem->quote . ',' . $finance_quote . ',' . $cyitem->cost . ',' . $finance_cost . ')';
						}
						$count ++;
					}
					// var_dump($sql);
					if (! empty ( $sql )) {
						
						$insert_result = $this->db->query ( 'DELETE FROM executive_cy WHERE executive_id=' . intval ( $this->executive_id ) . ' AND is_support=0 AND support_dep=0' );
						if ($insert_result === FALSE) {
							$success = FALSE;
							$error = '填写完成拆月数据失败，错误代码5';
						} else {
							$insert_result = $this->db->query ( 'INSERT INTO executive_cy(executive_id,pid,payname,deliverytype,year,month,ym,quote_amount,finance_quote_amount,cost_amount,finance_cost_amount) VALUES ' . implode ( ',', $sql ) );
							if ($insert_result === FALSE) {
								$success = FALSE;
								$error = '填写完成拆月数据失败，错误代码2';
							}
						}
					}
				}
				
				if ($success) {
					$update_result = $this->db->query ( 'UPDATE executive SET step=step+1,costpaymentinfoids="' . implode ( '^', $cost_payment ['costpaymentinfoids'] ) . '",costpayment=' . $cost_payment ['costpayment'] . ' WHERE id=' . intval ( $this->executive_id ) );
					if ($update_result === FALSE) {
						$success = FALSE;
						$error = '填写完成拆月数据失败，错误代码3';
					} else {
						$log = $this->do_executive_log ( $this->pid, '', '执行单发起人', '<font color=\'#99cc00\'>填写拆月数据</font>' );
						if ($log ['status'] === 'error') {
							$success = FALSE;
							$error = '填写完成拆月数据失败，错误代码4';
						}
					}
				}
			}
			
			if ($success) {
				$this->db->query ( 'COMMIT' );
			} else {
				$this->db->query ( 'ROLLBACK' );
			}
			return array (
					'status' => $success ? 'success' : 'error',
					'message' => $success ? '填写完成拆月数据成功' : $error 
			);
		}
		return array (
				'status' => 'error',
				'message' => $this->errors 
		);
	}
}
