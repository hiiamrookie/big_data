<?php
class Finance_Report extends User {
	private $starttime;
	private $endtime;
	private $has_finance_report_permission = FALSE;
	private $anyValue = FALSE;
	private static $title_fixed = array (
			'序号',
			'签约日期',
			'执行单号',
			'客户',
			'所属公司',
			'所属发票',
			'直客',
			'4A',
			'Compaign',
			'所属地区/部门/团队',
			'项目管理-媒体',
			'执行单类型',
			'投放时间',
			'应收时间',
			'执行总金额',
			'当月执行金额',
			'执行总成本',
			'当月执行成本',
			'其他费用',
			'营业税及附加',
			'应得返点',
			'利润小计',
			'备注',
			'当月执行成本明细',
			'返点比例',
			'供应商进票税率',
			'供应商名称',
			'投放媒体简称',
			'供应商产品分类',
			'供应商客户行业分类' 
	);
	const TITLE_START_ROW = 3;
	public function __construct($fields = array()) {
		parent::__construct ();
		if (in_array ( $this->getUsername (), $GLOBALS ['manager_finance_permission'], TRUE ) || intval ( $this->getBelong_dep () ) === 2) {
			$this->has_finance_report_permission = TRUE;
		}
		if (! empty ( $fields )) {
			foreach ( $this as $key => $value ) {
				if ($fields [$key] !== NULL && ! in_array ( $key, array (
						'has_finance_report_permission' 
				), TRUE )) {
					$this->$key = $fields [$key];
				}
			}
		}
		
		$sql_mode = $this->db->get_var ( 'SELECT @@GLOBAL.sql_mode' );
		if (strpos ( $sql_mode, 'ONLY_FULL_GROUP_BY' ) !== FALSE) {
			$anyValue = TRUE;
		}
	}
	public function get_report_html() {
		if ($this->has_finance_report_permission) {
			$s = '';
			$results = $this->db->get_results ( 'SELECT id,settle_account_date FROM finance_settle_account ORDER BY settle_account_date DESC' );
			if ($results !== NULL) {
				foreach ( $results as $result ) {
					$s .= '<tr><td width="5%">&nbsp;&nbsp;<input type="checkbox" name="seldate[]" value="' . strtotime ( $result->settle_account_date ) . '"></td><td>' . $result->settle_account_date . '</td></tr>';
				}
			}
			$buf = file_get_contents ( TEMPLATE_PATH . 'finance/finance_report.tpl' );
			return str_replace ( array (
					'[SETTLEACCOUNTDATES]',
					'[LEFT]',
					'[TOP]',
					'[VCODE]',
					'[BASE_URL]' 
			), array (
					$s,
					$this->get_left_html (),
					$this->get_top_html (),
					$this->get_vcode (),
					BASE_URL 
			), $buf );
		} else {
			return User::no_permission ();
		}
	}
	private static function _getCompany($company) {
		switch (( int ) $company) {
			case 3 :
				return '新网迈';
				break;
			case 1 :
				return '网迈广告';
				break;
			default :
				return '';
		}
	}
	private static function _getPidType($type) {
		switch (( int ) $type) {
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
	private static function _getBilltype($billtype) {
		if (( int ) $billtype === 2) {
			return '服务发票';
		}
		return '广告发票';
	}
	private static function _getCidCustomType($type1) {
		if (( int ) $type1 === 0) {
			return '直客';
		}
		return '代理商';
	}
	private static function _getDepInfo($depInfo, $teamInfo, $belong_dep, $belong_team) {
		$_dep = $depInfo [$belong_dep];
		$_team = $teamInfo ['team'] [$belong_team];
		if ($_dep === NULL) {
			$_dep = '';
		} else {
			$_dep = $_dep [1] . $_dep [0];
		}
		
		if ($_team !== NULL) {
			$_team = $_team ['teamname'];
		}
		return sprintf ( '%s %s', $_dep, $_team );
	}
	private static function _getTimeRange($starttime, $endtime) {
		return $starttime . '/' . $endtime;
	}
	private function _getAmountCY($executive_ids) {
		$cys = array ();
		$results = $this->db->get_results ( 'SELECT executive_id,pid,ym,quote_amount FROM executive_amount_cy WHERE executive_id IN (' . implode ( ',', $executive_ids ) . ')' );
		if ($results !== NULL) {
			foreach ( $results as $result ) {
				$cys ['cy_sum'] [$result->ym] [$result->executive_id] += $result->quote_amount;
			}
		}
		return $cys;
	}
	private function _getCostCY($executive_ids) {
		$cys = array ();
		$results = $this->db->get_results ( 'SELECT a.*,b.supplier_name,c.media_short,d.category_name,e.industry_name,f.isalter
FROM
(
SELECT executive_id,pid,ym,cost_amount,is_support,support_dep,supplier_id,supplier_short_id,category_id,industry_id from executive_cy WHERE executive_id IN (' . implode ( ',', $executive_ids ) . ') 
) a
LEFT JOIN new_supplier b
ON a.supplier_id=b.id
LEFT JOIN finance_supplier_short c
ON a.supplier_short_id=c.id
LEFT JOIN new_supplier_category d
ON a.category_id=d.id
LEFT JOIN new_supplier_industry e
ON a.industry_id=e.id
LEFT JOIN executive f
ON a.executive_id = f.id
ORDER BY a.ym,a.executive_id,a.supplier_id,a.supplier_short_id,a.category_id,a.industry_id,a.is_support,a.support_dep' );
		
		if ($results !== NULL) {
			foreach ( $results as $result ) {
				$tmp_nKey_id = $result->supplier_id . '|' . $result->supplier_short_id . '|' . $result->category_id . '|' . $result->industry_id . '|' . $result->is_support . '|' . $result->support_dep;
				
				$cys ['cy'] [$result->ym] [$result->pid] [$result->isalter] [$tmp_nKey_id] [] = array (
						'id' => $result->executive_id,
						'pid' => $result->pid,
						'cost_amount' => $result->cost_amount,
						'is_support' => $result->is_support,
						'support_dep' => $result->support_dep,
						'supplier_id' => $result->supplier_id,
						'supplier_short_id' => $result->supplier_short_id,
						'category_id' => $result->category_id,
						'industry_id' => $result->industry_id,
						'supplier_name' => $result->supplier_name,
						'media_short' => $result->media_short,
						'category_name' => $result->category_name,
						'industry_name' => $result->industry_name,
						'isalter' => $result->isalter 
				);
				
				$cys ['cy_sum'] [$result->ym] [$result->executive_id] += $result->cost_amount;
				
				if (empty ( $cys ['exe_id'] [$result->pid] [$result->isalter] )) {
					$cys ['exe_id'] [$result->pid] [$result->isalter] = $result->executive_id;
				}
			}
			
			foreach ( $cys ['cy'] as $ym => $ym_objs ) {
				foreach ( $ym_objs as $pid => $pid_objs ) {
					foreach ( $pid_objs as $isalter => $cost_objs ) {
						foreach ( $cost_objs as $nKey => $cost_obj ) {
							if (! empty ( $cys ['cy'] [$ym] [$pid] [$isalter + 1] )) {
								if (empty ( $cys ['cy'] [$ym] [$pid] [$isalter + 1] [$nKey] )) {
									$cys ['cy'] [$ym] [$pid] [$isalter + 1] [$nKey] [] = array (
											'id' => $cys ['exe_id'] [$pid] [$isalter + 1],
											'pid' => $pid,
											'cost_amount' => 0,
											'is_support' => $cost_obj [0] ['is_support'],
											'support_dep' => $cost_obj [0] ['support_dep'],
											'supplier_id' => $cost_obj [0] ['supplier_id'],
											'supplier_short_id' => $cost_obj [0] ['supplier_short_id'],
											'category_id' => $cost_obj [0] ['category_id'],
											'industry_id' => $cost_obj [0] ['industry_id'],
											'supplier_name' => $cost_obj [0] ['supplier_name'],
											'media_short' => $cost_obj [0] ['media_short'],
											'category_name' => $cost_obj [0] ['category_name'],
											'industry_name' => $cost_obj [0] ['industry_name'],
											'isalter' => $isalter + 1 
									);
								}
							}
						}
					}
				}
			}
		}
		return $cys;
	}
	private static function _getCampaign($campaign, $isalter) {
		$version = '';
		if ($isalter === 0) {
			$version = '【新】';
		} else {
			$version = '【变' . $isalter . '】';
		}
		return $campaign . ' ' . $version;
	}
	private function _getPids() {
		$pids = array ();
		$cids = array ();
		$tmp_cids = array ();
		$tmp_paytimes = array ();
		
		if ($anyValue) {
			$results = $this->db->get_results ( 'SELECT b.id,b.pid,b.cid,b.city,b.dep,b.team,b.name,b.type,b.amount,b.allcost,b.starttime,b.endtime,b.company,b.time,b.paytimeinfoids,b.isalter,FROM_UNIXTIME(b.oktime) AS oktimeshow FROM
(
SELECT ANY_VALUE(a.id) AS id,a.pid,ANY_VALUE(a.cid) AS cid,ANY_VALUE(a.city) AS city,ANY_VALUE(a.dep) AS dep,ANY_VALUE(a.team) AS team,ANY_VALUE(a.isalter) AS isalter,ANY_VALUE(a.isok) AS isok,ANY_VALUE(a.oktime) AS oktime,ANY_VALUE(a.name) AS name,ANY_VALUE(a.type) AS type,ANY_VALUE(a.amount) AS amount,ANY_VALUE(a.allcost) AS allcost,ANY_VALUE(a.starttime) AS starttime,ANY_VALUE(a.endtime) AS endtime,ANY_VALUE(a.company) AS company,ANY_VALUE(a.time) AS time,ANY_VALUE(a.paytimeinfoids) AS paytimeinfoids
FROM (
SELECT id,pid,cid,city,dep,team,isalter,isok,oktime,name,type,amount,allcost,starttime,endtime,company,time,paytimeinfoids FROM executive WHERE isok=1 AND oktime>=' . $this->starttime . ' AND oktime<=' . $this->endtime . ' ORDER BY pid,isalter DESC
) a GROUP BY pid
) b' );
		} else {
			$results = $this->db->get_results ( 'SELECT b.id,b.pid,b.cid,b.city,b.dep,b.team,b.name,b.type,b.amount,b.allcost,b.starttime,b.endtime,b.company,b.time,b.paytimeinfoids,b.isalter,FROM_UNIXTIME(b.oktime) AS oktimeshow FROM 
(
SELECT a.id,a.pid,a.cid,a.city,a.dep,a.team,a.isalter,a.isok,a.oktime,a.name,a.type,a.amount,a.allcost,a.starttime,a.endtime,a.company,a.time,a.paytimeinfoids
FROM (
SELECT id,pid,cid,city,dep,team,isalter,isok,oktime,name,type,amount,allcost,starttime,endtime,company,time,paytimeinfoids FROM executive WHERE isok=1 AND oktime>=' . $this->starttime . ' AND oktime<=' . $this->endtime . ' ORDER BY pid,isalter DESC
) a GROUP BY pid
) b' );
		}
		
		if ($results !== NULL) {
			foreach ( $results as $result ) {
				if (intval ( $result->isalter ) !== 0) {
					// 有变更的执行单，查找以前版本
					$prev_results = $this->db->get_results ( 'SELECT id,pid,cid,city,dep,team,isalter,isok,oktime,name,type,amount,allcost,starttime,endtime,company,time,paytimeinfoids,FROM_UNIXTIME(oktime) AS oktimeshow FROM executive WHERE pid="' . $result->pid . '" AND isalter<' . $result->isalter . ' AND isok=1 ORDER BY isalter DESC' );
					if ($prev_results !== NULL) {
						$getOne = FALSE;
						foreach ( $prev_results as $prev_result ) {
							$oktime = $prev_result->oktime;
							if ($oktime >= $this->starttime || ! $getOne && $oktime < $this->starttime) {
								
								$pids [$prev_result->id] = array (
										'executive_id' => $prev_result->id,
										'pid' => $prev_result->pid,
										'cid' => $prev_result->cid,
										'city' => $prev_result->city,
										'dep' => $prev_result->dep,
										'team' => $prev_result->team,
										'name' => $prev_result->name,
										'type' => $prev_result->type,
										'amount' => $prev_result->amount,
										'allcost' => $prev_result->allcost,
										'starttime' => $prev_result->starttime,
										'endtime' => $prev_result->endtime,
										'company' => $prev_result->company,
										'time' => $prev_result->time,
										'isalter' => $prev_result->isalter,
										'oktimeshow' => $prev_result->oktimeshow,
										'in_range' => ($oktime >= $this->starttime) 
								);
								
								if (! in_array ( $prev_result->cid, $tmp_cids, TRUE )) {
									$tmp_cids [] = $prev_result->cid;
								}
								
								if (! $getOne && $oktime < $this->starttime) {
									$getOne = TRUE;
								}
								
								$prev_paytimeinfoids = $prev_result->paytimeinfoids;
								if (! empty ( $prev_paytimeinfoids )) {
									$prev_paytimeinfoids = explode ( '^', $prev_paytimeinfoids );
									foreach ( $prev_paytimeinfoids as $prev_paytimeinfoid ) {
										if (! empty ( $prev_paytimeinfoid )) {
											$tmp_paytimes ['relate'] [$prev_result->id] [] = ( int ) $prev_paytimeinfoid;
											$tmp_paytimes ['infoid'] [] = $prev_paytimeinfoid;
										}
									}
								}
							}
						}
					}
				}
				
				$pids [$result->id] = array (
						'executive_id' => $result->id,
						'pid' => $result->pid,
						'cid' => $result->cid,
						'city' => $result->city,
						'dep' => $result->dep,
						'team' => $result->team,
						'name' => $result->name,
						'type' => $result->type,
						'amount' => $result->amount,
						'allcost' => $result->allcost,
						'starttime' => $result->starttime,
						'endtime' => $result->endtime,
						'company' => $result->company,
						'time' => $result->time,
						'isalter' => $result->isalter,
						'oktimeshow' => $result->oktimeshow,
						'in_range' => TRUE 
				);
				
				if (! in_array ( $result->cid, $tmp_cids, TRUE )) {
					$tmp_cids [] = $result->cid;
				}
				
				$paytimeinfoids = $result->paytimeinfoids;
				if (! empty ( $paytimeinfoids )) {
					$paytimeinfoids = explode ( '^', $paytimeinfoids );
					foreach ( $paytimeinfoids as $paytimeinfoid ) {
						if (! empty ( $paytimeinfoid )) {
							$tmp_paytimes ['relate'] [$result->id] [] = ( int ) $paytimeinfoid;
							$tmp_paytimes ['infoid'] [] = $paytimeinfoid;
						}
					}
				}
			}
			
			// 获得cid信息
			if (! empty ( $tmp_cids )) {
				$results = $this->db->get_results ( 'SELECT cid,cusname,type1,billtype FROM contract_cus WHERE cid IN ("' . implode ( '","', $tmp_cids ) . '")' );
				if ($results !== NULL) {
					foreach ( $results as $result ) {
						$cids [$result->cid] = array (
								'cusname' => $result->cusname,
								'type1' => $result->type1,
								'billtype' => $result->billtype 
						);
					}
				}
			}
			
			// 获得paytime信息
			if (! empty ( $tmp_paytimes )) {
				$results = $this->db->get_results ( 'SELECT id,paytime FROM executive_paytime WHERE id IN (' . implode ( ',', $tmp_paytimes ['infoid'] ) . ')' );
				if ($results !== NULL) {
					foreach ( $results as $result ) {
						foreach ( $tmp_paytimes ['relate'] as $key => $value ) {
							if (in_array ( ( int ) ($result->id), $value, TRUE )) {
								$paytimes [$key] [] = $result->paytime;
							}
						}
					}
				}
			}
		}
		
		return empty ( $pids ) ? $pids : array (
				'pids' => $pids,
				'cids' => $cids,
				'paytimes' => $paytimes,
				'executive_ids' => array_keys ( $pids ) 
		);
	}
	private function _getSheetData() {
		$data = array ();
		$costs = array ();
		$amounts = array ();
		
		$pids = $this->_getPids ();
		if (! empty ( $pids )) {
			$costs = $this->_getCostCY ( $pids ['executive_ids'] );
			$amounts = $this->_getAmountCY ( $pids ['executive_ids'] );
			$data ['pids'] = $pids;
		}
		
		if (! empty ( $costs )) {
			$costs_cy = $costs ['cy'];
			$costs_sum = $costs ['cy_sum'];
			foreach ( $costs_cy as $month => $cc ) {
				foreach ( $cc as $pid => $isalters ) {
					foreach ( $isalters as $isalter => $cost ) {
						foreach ( $cost as $nkey => $cost_array ) {
							if (count ( $cost_array ) > 1) {
								$arr = array ();
								$cost_amount = 0;
								$exe_id = 0;
								foreach ( $cost_array as $k => $ca ) {
									if ($k === 0) {
										$exe_id = $ca ['id'];
										$arr ['id'] = $ca ['id'];
										$arr ['pid'] = $ca ['pid'];
										$arr ['is_support'] = $ca ['is_support'];
										$arr ['support_dep'] = $ca ['support_dep'];
										$arr ['supplier_id'] = $ca ['supplier_id'];
										$arr ['supplier_short_id'] = $ca ['supplier_short_id'];
										$arr ['category_id'] = $ca ['category_id'];
										$arr ['industry_id'] = $ca ['industry_id'];
										$arr ['supplier_name'] = $ca ['supplier_name'];
										$arr ['media_short'] = $ca ['media_short'];
										$arr ['category_name'] = $ca ['category_name'];
										$arr ['industry_name'] = $ca ['industry_name'];
										$arr ['isalter'] = $ca ['isalter'];
									}
									$cost_amount += $ca ['cost_amount'];
								}
								$arr ['cost_amount'] = $cost_amount;
								$data ['cost_cy'] [$month] [$exe_id] [$nkey] = $arr;
							} else if (count ( $cost_array ) === 1) {
								$data ['cost_cy'] [$month] [$cost_array [0] ['id']] [$nkey] = $cost_array [0];
							}
						}
					}
				}
			}
			foreach ( $costs_sum as $month => $cs ) {
				foreach ( $cs as $exe_id => $cost_amount ) {
					$data ['cost_cy_sum'] [$month] [$exe_id] = $cost_amount;
				}
			}
		}
		
		if (! empty ( $amounts )) {
			foreach ( $amounts ['cy_sum'] as $month => $amount ) {
				foreach ( $amount as $exe_id => $quote_amount ) {
					$data ['amount_cy_sum'] [$month] [$exe_id] = $quote_amount;
				}
			}
		}
		return $data;
	}
	private function _getSupplierInfo() {
		$datas = array ();
		$results = $this->db->get_results ( 'SELECT supplier_id,in_invoice_tax_rate FROM new_supplier_info WHERE isok=1' );
		if ($results !== NULL) {
			foreach ( $results as $result ) {
				$datas [$result->supplier_id] = array (
						'in_invoice_tax_rate' => $result->in_invoice_tax_rate 
				);
			}
		}
		return $datas;
	}
	public function get_finance_report() {
		if ($this->has_finance_report_permission) {
			$data = $this->_getSheetData ();
			
			if (! empty ( $data )) {
				$months = array_unique ( array_merge ( array_keys ( $data ['cost_cy'] ), array_keys ( $data ['amount_cy_sum'] ) ) );
				sort ( $months );
				
				// 返点比例
				$rebates = Setting_Rebate::getRebateInstance ();
				
				// 部门信息
				$dep = Dep::getInstance ();
				$team = Team::getInstance ();
				
				// 结帐日期
				$settleDate = date ( 'Y-m', $this->endtime );
				
				// 开始生成excel
				$objPHPExcel = new PHPExcel ();
				PHPExcel_Settings::setCacheStorageMethod ( PHPExcel_CachedObjectStorageFactory::cache_in_memory_serialized );
				
				// 执行单信息
				$pids = $data ['pids'];
				// 供应商成本拆月
				$data_cost_cy = $data ['cost_cy'];
				// 成本拆月总和
				$data_cost_cy_sum = $data ['cost_cy_sum'];
				// 金额拆月总和
				$data_amount_cy_sum = $data ['amount_cy_sum'];
				
				$ative_sheet = 0;
				$line = 0;
				
				// 供应商信息，目前只用到进票税率
				$supplier_info = $this->_getSupplierInfo ();
				
				foreach ( $months as $key => $month ) {
					
					// 需要重新计算差值的数据
					$data_recounts = array ();
					
					if ($key === 0) {
						$objPHPExcel->setActiveSheetIndex ( $key );
						$objPHPExcel->getActiveSheet ()->setTitle ( $settleDate === NULL ? $month : $settleDate );
					}
					
					if ($settleDate === NULL) {
						if ($key !== 0) {
							$objPHPExcel->addSheet ( new PHPExcel_Worksheet ( $objPHPExcel, $month ) );
							$line = 0;
							$ative_sheet ++;
						}
					} else {
						if (strtotime ( $month ) > strtotime ( $settleDate )) {
							$objPHPExcel->addSheet ( new PHPExcel_Worksheet ( $objPHPExcel, $month ) );
							$line = 0;
							$ative_sheet ++;
						}
					}
					
					$objPHPExcel->setActiveSheetIndex ( $ative_sheet );
					
					$objPHPExcel->getActiveSheet ()->setCellValueByColumnAndRow ( 0, 1, '媒体投放量汇总' );
					
					// 标题
					foreach ( self::$title_fixed as $k => $v ) {
						$objPHPExcel->getActiveSheet ()->setCellValueByColumnAndRow ( $k, self::TITLE_START_ROW, $v );
					}
					
					$now_pid_isalter = '';
					foreach ( $data_cost_cy [$month] as $executive_id => $contents ) {
						// 当前执行单号
						
						foreach ( $contents as $nKey => $recs ) {
							$now_line = $line + self::TITLE_START_ROW + 1;
							$rebid = $recs ['supplier_id'] . '|' . $recs ['supplier_short_id'] . '|' . $recs ['category_id'] . '|' . $recs ['industry_id'];
							
							// 序号
							$objPHPExcel->getActiveSheet ()->setCellValueByColumnAndRow ( 0, $now_line, $month );
							
							// 签约日期
							$objPHPExcel->getActiveSheet ()->setCellValueByColumnAndRow ( 1, $now_line, date ( 'Y-m-d H:i:s', $pids ['pids'] [$executive_id] ['time'] ) );
							
							// 执行单号
							$objPHPExcel->getActiveSheet ()->setCellValueByColumnAndRow ( 2, $now_line, $pids ['pids'] [$executive_id] ['pid'] );
							
							// 客户
							$objPHPExcel->getActiveSheet ()->setCellValueByColumnAndRow ( 3, $now_line, $pids ['cids'] [$pids ['pids'] [$executive_id] ['cid']] ['cusname'] );
							
							// 所属公司
							$objPHPExcel->getActiveSheet ()->setCellValueByColumnAndRow ( 4, $now_line, self::_getCompany ( $pids ['pids'] [$executive_id] ['company'] ) );
							
							// 所属发票
							$objPHPExcel->getActiveSheet ()->setCellValueByColumnAndRow ( 5, $now_line, self::_getBilltype ( $pids ['cids'] [$pids ['pids'] [$executive_id] ['cid']] ['billtype'] ) );
							
							// 直客
							$objPHPExcel->getActiveSheet ()->setCellValueByColumnAndRow ( 6, $now_line, self::_getCidCustomType ( $pids ['cids'] [$pids ['pids'] [$executive_id] ['cid']] ['type1'] ) );
							
							// 4A（留空）
							$objPHPExcel->getActiveSheet ()->setCellValueByColumnAndRow ( 7, $now_line, '' );
							
							// campaign
							$objPHPExcel->getActiveSheet ()->setCellValueByColumnAndRow ( 8, $now_line, self::_getCampaign ( $pids ['pids'] [$executive_id] ['name'], intval ( $pids ['pids'] [$executive_id] ['isalter'] ) ) );
							
							// 项目管理-客户
							$objPHPExcel->getActiveSheet ()->setCellValueByColumnAndRow ( 9, $now_line, self::_getDepInfo ( $dep, $team, $pids ['pids'] [$executive_id] ['dep'], $pids ['pids'] [$executive_id] ['team'] ) );
							
							// 项目管理-媒体（留空）
							$objPHPExcel->getActiveSheet ()->setCellValueByColumnAndRow ( 10, $now_line, '' );
							
							// 执行单类型
							$objPHPExcel->getActiveSheet ()->setCellValueByColumnAndRow ( 11, $now_line, self::_getPidType ( $pids ['pids'] [$executive_id] ['type'] ) );
							
							// 投放时间
							$objPHPExcel->getActiveSheet ()->setCellValueByColumnAndRow ( 12, $now_line, self::_getTimeRange ( $pids ['pids'] [$executive_id] ['starttime'], $pids ['pids'] [$executive_id] ['endtime'] ) );
							
							// 应收时间
							$objPHPExcel->getActiveSheet ()->setCellValueByColumnAndRow ( 13, $now_line, implode ( ',', $pids ['paytimes'] [$executive_id] ) );
							
							if ($now_pid_isalter !== ($pids ['pids'] [$executive_id] ['pid'] . '_' . $pids ['pids'] [$executive_id] ['isalter'])) {
								// 执行总金额
								$objPHPExcel->getActiveSheet ()->setCellValueByColumnAndRow ( 14, $now_line, $pids ['pids'] [$executive_id] ['amount'] );
								$objPHPExcel->getActiveSheet ()->getStyleByColumnAndRow ( 14, $now_line )->getNumberFormat ()->setFormatCode ( PHPExcel_Style_NumberFormat::FORMAT_NUMBER_COMMA_SEPARATED3 );
								
								// $data_recounts [$pids ['pids'] [$executive_id] ['pid']] [$pids ['pids'] [$executive_id] ['isalter']] [$rebid . '|' . $recs ['is_support'] . '|' . $recs ['support_dep']] ['amount'] = array (
								// 'row' => $now_line,
								// 'col' => 14,
								// 'value' => $pids ['pids'] [$executive_id] ['amount']
								// );
								
								// 当月执行金额
								$now_month_amount = $data_amount_cy_sum [$month] [$executive_id];
								if (empty ( $now_month_amount )) {
									$now_month_amount = 0;
								}
								$objPHPExcel->getActiveSheet ()->setCellValueByColumnAndRow ( 15, $now_line, $now_month_amount );
								$objPHPExcel->getActiveSheet ()->getStyleByColumnAndRow ( 15, $now_line )->getNumberFormat ()->setFormatCode ( PHPExcel_Style_NumberFormat::FORMAT_NUMBER_COMMA_SEPARATED3 );
								
								$data_recounts [$pids ['pids'] [$executive_id] ['pid']] [$pids ['pids'] [$executive_id] ['isalter']] ['sum'] ['now_month_amount'] = array (
										'row' => $now_line,
										'col' => 15,
										'value' => $now_month_amount 
								);
								
								// 执行总成本
								$objPHPExcel->getActiveSheet ()->setCellValueByColumnAndRow ( 16, $now_line, $pids ['pids'] [$executive_id] ['allcost'] );
								$objPHPExcel->getActiveSheet ()->getStyleByColumnAndRow ( 16, $now_line )->getNumberFormat ()->setFormatCode ( PHPExcel_Style_NumberFormat::FORMAT_NUMBER_COMMA_SEPARATED3 );
								
								// $data_recounts [$pids ['pids'] [$executive_id] ['pid']] [$pids ['pids'] [$executive_id] ['isalter']] [$rebid . '|' . $recs ['is_support'] . '|' . $recs ['support_dep']] ['allcost'] = array (
								// 'row' => $now_line,
								// 'col' => 16,
								// 'value' => $pids ['pids'] [$executive_id] ['allcost']
								// );
								
								// 当月执行成本
								$now_month_cost = $data_cost_cy_sum [$month] [$executive_id];
								if (empty ( $now_month_cost )) {
									$now_month_cost = 0;
								}
								$objPHPExcel->getActiveSheet ()->setCellValueByColumnAndRow ( 17, $now_line, $now_month_cost );
								$objPHPExcel->getActiveSheet ()->getStyleByColumnAndRow ( 17, $now_line )->getNumberFormat ()->setFormatCode ( PHPExcel_Style_NumberFormat::FORMAT_NUMBER_COMMA_SEPARATED3 );
								
								$data_recounts [$pids ['pids'] [$executive_id] ['pid']] [$pids ['pids'] [$executive_id] ['isalter']] ['sum'] ['now_month_cost'] = array (
										'row' => $now_line,
										'col' => 17,
										'value' => $now_month_cost 
								);
								
								$now_pid_isalter = $pids ['pids'] [$executive_id] ['pid'] . '_' . $pids ['pids'] [$executive_id] ['isalter'];
							}
							
							// 其他费用（留空）
							$objPHPExcel->getActiveSheet ()->setCellValueByColumnAndRow ( 18, $now_line, '' );
							
							// 营业税及附加
							$objPHPExcel->getActiveSheet ()->setCellValue ( 'T' . $now_line, '=IF(F' . $now_line . '="广告发票",(P' . $now_line . '-X' . $now_line . '+U' . $now_line . ')*0.0996,IF(F' . $now_line . '="服务发票",(P' . $now_line . '-X' . $now_line . '+U' . $now_line . ')*0.0678))' );
							$objPHPExcel->getActiveSheet ()->getStyle ( 'T' . $now_line )->getNumberFormat ()->setFormatCode ( PHPExcel_Style_NumberFormat::FORMAT_NUMBER_COMMA_SEPARATED3 );
							
							// 应得返点
							$objPHPExcel->getActiveSheet ()->setCellValue ( 'U' . $now_line, '=X' . $now_line . '*Y' . $now_line );
							$objPHPExcel->getActiveSheet ()->getStyle ( 'U' . $now_line )->getNumberFormat ()->setFormatCode ( PHPExcel_Style_NumberFormat::FORMAT_NUMBER_COMMA_SEPARATED3 );
							
							// 利润小计
							$objPHPExcel->getActiveSheet ()->setCellValue ( 'V' . $now_line, '=P' . $now_line . '-X' . $now_line . '-T' . $now_line . '-S' . $now_line . '+U' . $now_line );
							$objPHPExcel->getActiveSheet ()->getStyle ( 'V' . $now_line )->getNumberFormat ()->setFormatCode ( PHPExcel_Style_NumberFormat::FORMAT_NUMBER_COMMA_SEPARATED3 );
							
							// 备注（留空）
							$objPHPExcel->getActiveSheet ()->setCellValueByColumnAndRow ( 22, $now_line, '' );
							
							// 当月执行成本明细
							$objPHPExcel->getActiveSheet ()->setCellValueByColumnAndRow ( 23, $now_line, $recs ['cost_amount'] );
							$objPHPExcel->getActiveSheet ()->getStyleByColumnAndRow ( 23, $now_line )->getNumberFormat ()->setFormatCode ( PHPExcel_Style_NumberFormat::FORMAT_NUMBER_COMMA_SEPARATED3 );
							
							$data_recounts [$pids ['pids'] [$executive_id] ['pid']] [$pids ['pids'] [$executive_id] ['isalter']] [$rebid . '|' . $recs ['is_support'] . '|' . $recs ['support_dep']] ['cost_amount'] = array (
									'row' => $now_line,
									'col' => 23,
									'value' => $recs ['cost_amount'] 
							);
							
							// 返点比例
							$objPHPExcel->getActiveSheet ()->setCellValueByColumnAndRow ( 24, $now_line, ! empty ( $rebates [$rebid] ) ? ($rebates [$rebid] / 100) : 0 );
							$objPHPExcel->getActiveSheet ()->getStyleByColumnAndRow ( 24, $now_line )->getNumberFormat ()->setFormatCode ( PHPExcel_Style_NumberFormat::FORMAT_PERCENTAGE_00 );
							
							// 供应商进票税率
							$objPHPExcel->getActiveSheet ()->setCellValueByColumnAndRow ( 25, $now_line, empty ( $supplier_info [$recs ['supplier_id']] ['in_invoice_tax_rate'] ) ? '0%' : $supplier_info [$recs ['supplier_id']] ['in_invoice_tax_rate'] . '%' );
							
							// 媒体
							$objPHPExcel->getActiveSheet ()->setCellValueByColumnAndRow ( 26, $now_line, $recs ['supplier_name'] );
							
							// 媒体简称
							$objPHPExcel->getActiveSheet ()->setCellValueByColumnAndRow ( 27, $now_line, $recs ['media_short'] );
							
							// 投放产品分类
							$objPHPExcel->getActiveSheet ()->setCellValueByColumnAndRow ( 28, $now_line, $recs ['category_name'] );
							
							// 客户行业分类
							$objPHPExcel->getActiveSheet ()->setCellValueByColumnAndRow ( 29, $now_line, $recs ['industry_name'] );
							
							// 如果不在范围内的，设置背景色
							if (! ($pids ['pids'] [$executive_id] ['in_range'])) {
								$objPHPExcel->getActiveSheet ()->getStyle ( 'A' . $now_line . ':AD' . $now_line )->getFill ()->setFillType ( PHPExcel_Style_Fill::FILL_SOLID );
								$objPHPExcel->getActiveSheet ()->getStyle ( 'A' . $now_line . ':AD' . $now_line )->getFill ()->getStartColor ()->setARGB ( '#FFFF00' );
							}
							
							$line ++;
						}
					}
					
					$now_pid_isalter = '';
					foreach ( $data_amount_cy_sum [$month] as $executive_id => $contents ) {
						if ($data_cost_cy_sum [$month] [$executive_id] == NULL) {
							$now_line = $line + self::TITLE_START_ROW + 1;
							// 序号
							$objPHPExcel->getActiveSheet ()->setCellValueByColumnAndRow ( 0, $now_line, $month );
							
							// 签约日期
							$objPHPExcel->getActiveSheet ()->setCellValueByColumnAndRow ( 1, $now_line, date ( 'Y-m-d H:i:s', $pids ['pids'] [$executive_id] ['time'] ) );
							
							// 执行单号
							$objPHPExcel->getActiveSheet ()->setCellValueByColumnAndRow ( 2, $now_line, $pids ['pids'] [$executive_id] ['pid'] );
							
							// 客户
							$objPHPExcel->getActiveSheet ()->setCellValueByColumnAndRow ( 3, $now_line, $pids ['cids'] [$pids ['pids'] [$executive_id] ['cid']] ['cusname'] );
							
							// 所属公司
							$objPHPExcel->getActiveSheet ()->setCellValueByColumnAndRow ( 4, $now_line, self::_getCompany ( $pids ['pids'] [$executive_id] ['company'] ) );
							
							// 所属发票
							$objPHPExcel->getActiveSheet ()->setCellValueByColumnAndRow ( 5, $now_line, self::_getBilltype ( $pids ['cids'] [$pids ['pids'] [$executive_id] ['cid']] ['billtype'] ) );
							
							// 直客
							$objPHPExcel->getActiveSheet ()->setCellValueByColumnAndRow ( 6, $now_line, self::_getCidCustomType ( $pids ['cids'] [$pids ['pids'] [$executive_id] ['cid']] ['type1'] ) );
							
							// 4A（留空）
							$objPHPExcel->getActiveSheet ()->setCellValueByColumnAndRow ( 7, $now_line, '' );
							
							// campaign
							$objPHPExcel->getActiveSheet ()->setCellValueByColumnAndRow ( 8, $now_line, self::_getCampaign ( $pids ['pids'] [$executive_id] ['name'], intval ( $pids ['pids'] [$executive_id] ['isalter'] ) ) );
							
							// 项目管理-客户
							$objPHPExcel->getActiveSheet ()->setCellValueByColumnAndRow ( 9, $now_line, self::_getDepInfo ( $dep, $team, $pids ['pids'] [$executive_id] ['dep'], $pids ['pids'] [$executive_id] ['team'] ) );
							
							// 项目管理-媒体（留空）
							$objPHPExcel->getActiveSheet ()->setCellValueByColumnAndRow ( 10, $now_line, '' );
							
							// 执行单类型
							$objPHPExcel->getActiveSheet ()->setCellValueByColumnAndRow ( 11, $now_line, self::_getPidType ( $pids ['pids'] [$executive_id] ['type'] ) );
							
							// 投放时间
							$objPHPExcel->getActiveSheet ()->setCellValueByColumnAndRow ( 12, $now_line, self::_getTimeRange ( $pids ['pids'] [$executive_id] ['starttime'], $pids ['pids'] [$executive_id] ['endtime'] ) );
							
							// 应收时间
							$objPHPExcel->getActiveSheet ()->setCellValueByColumnAndRow ( 13, $now_line, implode ( ',', $pids ['paytimes'] [$executive_id] ) );
							
							if ($now_pid_isalter !== ($pids ['pids'] [$executive_id] ['pid'] . '_' . $pids ['pids'] [$executive_id] ['isalter'])) {
								
								// 当月执行金额
								$now_month_amount = $data_amount_cy_sum [$month] [$executive_id];
								if (empty ( $now_month_amount )) {
									$now_month_amount = 0;
								}
								$objPHPExcel->getActiveSheet ()->setCellValueByColumnAndRow ( 15, $now_line, $now_month_amount );
								$objPHPExcel->getActiveSheet ()->getStyleByColumnAndRow ( 15, $now_line )->getNumberFormat ()->setFormatCode ( PHPExcel_Style_NumberFormat::FORMAT_NUMBER_COMMA_SEPARATED3 );
								
								$data_recounts [$pids ['pids'] [$executive_id] ['pid']] [$pids ['pids'] [$executive_id] ['isalter']] ['sum0'] ['now_month_amount'] = array (
										'row' => $now_line,
										'col' => 15,
										'value' => $now_month_amount 
								);
								
								$now_pid_isalter = $pids ['pids'] [$executive_id] ['pid'] . '_' . $pids ['pids'] [$executive_id] ['isalter'];
							}
							$line ++;
						}
					}
					
					foreach ( $data_recounts as $pid => $data_recount ) {
						ksort ( $data_recount );
						$prev_isalter = NULL;
						foreach ( $data_recount as $isalter => $dr ) {
							foreach ( $dr as $pKey => $dr_array ) {
								foreach ( $dr_array as $field_name => $amount_array ) {
									// 重新计算
									$objPHPExcel->getActiveSheet ()->setCellValueByColumnAndRow ( $amount_array ['col'], $amount_array ['row'], $prev_isalter === NULL ? $amount_array ['value'] : ($amount_array ['value'] - $data_recount [$prev_isalter] [$pKey] [$field_name] ['value']) );
								}
							}
							$prev_isalter = $isalter;
						}
					}
				}
				
				$ua = $_SERVER ['HTTP_USER_AGENT'];
				
				if (preg_match ( '/MSIE/', $ua ) || preg_match ( '/Trident/', $ua )) {
					$filename = urlencode ( date ( 'YmdHis', $this->starttime ) . '~' . date ( 'YmdHis', $this->endtime ) . '_财务大表' ) . '.xls';
				} else {
					$filename = date ( 'YmdHis', $this->starttime ) . '~' . date ( 'YmdHis', $this->endtime ) . '_财务大表.xls';
				}
				
				// 生成
				$objPHPExcel->setActiveSheetIndex ( 0 );
				header ( 'Content-Type: application/vnd.ms-excel' );
				header ( 'Content-Disposition: attachment;filename="' . $filename . '"' );
				header ( 'Cache-Control: max-age=0' );
				
				$objWriter = PHPExcel_IOFactory::createWriter ( $objPHPExcel, 'Excel5' );
				$objWriter->save ( 'php://output' );
				$objPHPExcel->disconnectWorksheets ();
				unset ( $objPHPExcel );
			} else {
				return User::no_object ( '没有符合要求的执行单' );
			}
		} else {
			return User::no_permission ();
		}
	}
}
