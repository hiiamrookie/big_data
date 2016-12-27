<?php
class Finance_Report extends User {
	private $starttime;
	private $endtime;
	private $version;
	private $has_finance_report_permission = FALSE;

	private static $title_fixed = array('结帐日期', '签约日期', '执行单号', '客户名称', '所属公司',
			'发票类型', 'compaign', '所属部门', '执行日期', '应收时间', '执行总金额', '当月执行金额',
			'执行总成本', '当月执行成本', '增值税及附加', '应得返点', '利润小计', '开票总金额', '收款总金额',
			'发票号码', '是否后补', '流转天数', '备注');

	const TITLE_START_ROW = 4;

	public function __construct($fields = array()) {
		parent::__construct();
		if (in_array($this->getUsername(),
				$GLOBALS['manager_finance_permission'], TRUE)
				|| intval($this->getBelong_dep()) === 2) {
			$this->has_finance_report_permission = TRUE;
		}
		if (!empty($fields)) {
			foreach ($this as $key => $value) {
				if ($fields[$key] !== NULL
						&& !in_array($key,
								array('has_finance_report_permission'), TRUE)) {
					$this->$key = $fields[$key];
				}
			}
		}
	}

	public function get_report_html() {
		if ($this->has_finance_report_permission) {
			$buf = file_get_contents(
					TEMPLATE_PATH . 'finance/finance_report.tpl');
			return str_replace(
					array('[LEFT]', '[TOP]', '[VCODE]', '[BASE_URL]'),
					array($this->get_left_html(), $this->get_top_html(),
							$this->get_vcode(), BASE_URL), $buf);
		} else {
			return User::no_permission();
		}
	}

	private static function _get_company($company) {
		switch ($company) {
		case 3:
			return '新网迈';
			break;
		case 1:
			return '网迈广告';
			break;
		default:
			return '';
		}
	}

	private static function _get_billtype($billtype) {
		if ($billtype === 2) {
			return '服务发票';
		}
		return '广告发票';
	}

	private static function _get_time_range($starttime, $endtime) {
		return $starttime . '~' . $endtime;
	}

	private static function _get_remind_days($createtime, $oktime) {
		return intval(($oktime - $createtime) / 3600 / 24) . '天';
	}

	private static function _get_rebate($amount, $rebate_rate) {
		$rebate = 0;
		if (!empty($amount) && !empty($rebate_rate)) {
			$rebate = $amount * $rebate_rate / 100;
		}
		return round($rebate, 2);
	}

	private static function _is_executive_add_after_start($isalter, $createtime,
			$starttime) {
		if ($createtime > $starttime && $isalter === 0) {
			return '后补';
		}
		return '';
	}
	
	private static function _get_month($starttime,$endtime){
		$start = explode('-', $starttime);
		$end = explode('-', $endtime);
		$start_month = $start[0] . '-' . $start[1];
		$start_day = $start[2];
		
		$end_month = $end[0] . '-' . $end[1];
		$end_day = $end[2];
		
		
	}
	
	public function get_finance_report() {
		if ($this->has_finance_report_permission) {
			$query = 'SELECT a.id,a.pid,FROM_UNIXTIME(a.time) as time,a.time AS createtime,a.isalter,a.company,a.name,a.starttime,a.endtime,a.amount,a.allcost,a.paytimeinfoids,a.costpaymentinfoids,a.support,a.oktime,a.remark,b.cusname,b.billtype,b.customertype,c.companyname,d.depname,e.customertype AS agent2_customertype 
FROM v_last_executive a
LEFT JOIN contract_cus b ON a.cid=b.cid
LEFT JOIN hr_company c ON a.city=c.id
LEFT JOIN hr_department d ON a.dep=d.id
LEFT JOIN executive_agent e ON a.pid=e.pid
WHERE  a.isok=1 AND a.allcost>0 AND a.oktime>=' . strtotime($this->starttime . ' 00:00:00') . ' AND a.oktime<=' . strtotime($this->endtime . ' 23:59:59');

					//var_dump($query);
					//exit;
			$results = $this->db->get_results($query);
			if ($results === NULL) {
				return User::no_object('没有符合要求的执行单');
			} else {

				$ok_pids = array();
				$pid_infos = array();
				$supplier_industry = array();
				foreach ($results as $result) {
					if (!in_array($result->pid, $ok_pids, TRUE)) {
						$ok_pids[] = $result->pid;
						if(!empty($result->agent2_customertype)){
							$customertype = $result->agent2_customertype;
						}else{
							$customertype = !empty($result->customertype) ? $result->customertype : 0;
						}
						$pid_infos[$result->pid] = array('id'=>$result->id,
								'time' => $result->time,
								'isalter' => $result->isalter,
								'company' => self::_get_company(
										intval($result->company)),
								'name' => $result->name,
								'starttime' => $result->starttime,
								'endtime' => $result->endtime,
								'amount' => $result->amount,
								'allcost' => $result->allcost,
								'costpaymentinfoids' => $result
										->costpaymentinfoids,
								'support' => $result->support,
								'cusname' => $result->cusname,
								'billtype' => self::_get_billtype(
										intval($result->billtype)),
								'companyname' => $result->companyname,
								'depname' => $result->depname,
								'oktime' => $result->oktime,
								'createtime' => $result->createtime,
								'paytimeinfoids' => $result->paytimeinfoids,
								'customertype' => $customertype,
								'remark'=>$result->remark);
						$supplier_industry[$result->pid] = $customertype;
					}
				}
				
				//var_dump($ok_pids);
				$industrys = $this->db->get_results('SELECT a.supplier_id,a.industry_id,a.rebate,a.starttime,a.endtime,b.in_invoice_tax_rate
FROM new_supplier_industry_rebate a
LEFT JOIN new_supplier_info b
ON a.supplier_id=b.supplier_id
WHERE a.isok=1 AND b.isok=1');
				$si = array();
				if($industrys !== NULL){
					foreach ($industrys as $industry){
						$si[$industry->industry_id][] = array('supplier_id'=>$industry->supplier_id,'in_invoice_tax_rate'=>$industry->in_invoice_tax_rate,'starttime'=>$industry->starttime,'endtime'=>$industry->endtime,'rebate'=>$industry->rebate);
					}
				}	
				
				//客户行业分类
				$supplier_industry_rate = array();	
				if (!empty($supplier_industry)) {
					foreach ($supplier_industry as $key => $value) {
						$supplier_industry_rate[$key] = $si[$value];
					}
				}
				
				//供应商信息
				$results = $this->db->get_results('SELECT id,supplier_name,parentid FROM new_supplier WHERE isok=1');
				$supplier_othername = array();
				if($results !== NULL){
					foreach ($results as $result){
						$supplier_othername[$result->supplier_name] = array('id'=>$result->id,'parentid'=>$result->parentid);
					}
				}	
				
				$invoice_infos = array();
				if (!empty($ok_pids)) {
					//开票情况
					$invoices = $this->db
							->get_results(
									'SELECT a.pid,a.amount,b.number FROM finance_invoice a LEFT JOIN finance_invoice_list b ON a.invoice_list_id=b.id WHERE a.pid IN ("'
											. implode('","', $ok_pids)
											. '") AND b.isok=1 AND b.print=1');
					if ($invoices !== NULL) {
						foreach ($invoices as $invoice) {
							$invoice_infos[$invoice->pid]['amount'] += $invoice
									->amount;
							$invoice_infos[$invoice->pid]['number'][] = $invoice
									->number;
						}
					}

					//收款情况
					$receive_infos = array();
					$receives = $this->db
							->get_results(
									'SELECT a.pid,a.amount FROM finance_receivables a LEFT JOIN finance_receivables_list b ON a.receivables_list=b.id WHERE a.pid IN ("'
											. implode('","', $ok_pids)
											. '") AND b.isok=1');
					if ($receives !== NULL) {
						foreach ($receives as $receive) {
							$receive_infos[$receive->pid]['amount'] += $receive
									->amount;
						}
					}
				}

				if (!empty($pid_infos)) {
					$pid_suppliers = array();
					$suppliers = array();
					$pid_cys = array();
					$months = array();
					$month_pids = array();
					$pid_max_paytimes = array();
					foreach ($pid_infos as $pid => $pid_info) {
						if (!empty($pid_info['paytimeinfoids'])) {
							$_paytimeinfoids = Array_Util::my_remove_array_other_value(
									explode('^', $pid_info['paytimeinfoids']),
									array(''));

							if (!empty($_paytimeinfoids)) {
								//应付日期
								$max_month = $this->db
										->get_var(
												'SELECT MAX(paytime) FROM executive_paytime WHERE id IN ('
														. implode(',',
																$_paytimeinfoids)
														. ') AND isok=1');
								if ($max_month !== NULL) {
									$pid_max_paytimes[$pid] = $max_month;
								}
							}
						}

						$results = $this->db->get_results('SELECT pid,payname,deliverytype,ym,quote_amount,finance_quote_amount,cost_amount,finance_cost_amount FROM executive_cy WHERE executive_id=' . intval($pid_info['id']) . ' ORDER BY payname,year,month');
						if($results !== NULL){
							foreach ($results as $result){
								$pn = $result->payname;
								$dt = $result->deliverytype;
								$md5 = md5($pn . '|' . $dt);
								if(!in_array($md5, $suppliers['md5'],TRUE)){
										$suppliers['md5'][] = $md5;
										$suppliers['value'][$md5] = array(
													'payname' => $pn,
													'category' => $dt);
								}
								
								if (!in_array($result->ym, $months, TRUE)) {
									$months[] = $result->ym;
								}
								if (!in_array($result->pid,
									$month_pids[$result->ym], TRUE)) {
									$month_pids[$result->ym][] = $result->pid;
								}
										
										
								if(!in_array($md5, $pid_suppliers['md5'],TRUE)){
									$pid_suppliers['md5'][] = $md5;
									$pid_suppliers['value'][$result->pid][$md5] = array(
												'name' => $pn,
												'category' => $dt);
								}
								
								$pid_cys[$result->pid][$result->ym][$pn][empty(
												$dt) ? 'no'
												: $dt] = array(
												'quote_amount' => $result
														->quote_amount,
												'finance_quote_amount' => $result
														->finance_quote_amount,
												'cost_amount' => $result
														->cost_amount,
												'finance_cost_amount' => $result
														->finance_cost_amount);
														
							}
						}
					}


					if (!empty($months)) {
						$supplier_infos = Supplier::getInstance();
						$supplier_industry_infos = Supplier::getIndustryInstance();
						
						$_supplier_infos_version = $supplier_infos['supplier'];
						
						
						
						$objPHPExcel = new PHPExcel();
						PHPExcel_Settings::setCacheStorageMethod(
								PHPExcel_CachedObjectStorageFactory::cache_in_memory_serialized);
						foreach ($months as $key => $month) {
							
							$pid_rebate_rate = array();
							
							if ($key === 0) {
								$objPHPExcel->setActiveSheetIndex($key);
								$objPHPExcel->getActiveSheet()
										->setTitle($month);
							} else {
								$objPHPExcel
										->addSheet(
												new PHPExcel_Worksheet(
														$objPHPExcel, $month));
							}
							$objPHPExcel->setActiveSheetIndex($key);
							$objPHPExcel->getActiveSheet()
									->setCellValueByColumnAndRow(0, 1,
											'项目执行明细表');

							//供应商
							$title_count = count(self::$title_fixed);
							$count = 0;

							$_suppliers = $suppliers['value'];
							foreach ($_suppliers as $sk => $sv) {
								if ($count === 0) {
									$objPHPExcel->getActiveSheet()
											->setCellValueByColumnAndRow(
													$title_count - 1, 2,
													'供应商进票税率');
									$objPHPExcel->getActiveSheet()
											->setCellValueByColumnAndRow(
													$title_count - 1, 3,
													'产品分类返点比率');
								}
								
								$objPHPExcel->getActiveSheet()
										->setCellValueByColumnAndRow(
												$count + $title_count, 1,
												$sv['payname'] . ' -- '
														. (empty($sv['category']) ? '无产品分类'
																: $sv['category']));
																
								$iitr = 0;
								$supplier_id = 0;
								foreach ($supplier_infos['supplier'] as $skey=>$siv){
									if($siv['sn'] === $sv['payname']){
										$iitr = $siv['iitr'] ;
										$supplier_id = $skey;
										break;
									}
								}
								
								$objPHPExcel->getActiveSheet()
										->setCellValueByColumnAndRow(
												$count + $title_count, 2, $iitr . '%');
												
								$category_id = 0;
								$supplier_category = $supplier_infos['supplier_category'][$supplier_id];
								if(!empty($supplier_category)){
									foreach ($supplier_category as $sc){
										if($supplier_infos['category'][$sc] === $sv['category']){
											$category_id = $sc;
											break;
										}
									}
								}
								
								$rr = 0;
								$month_first_day = date('Y-m-01',strtotime($month));
								$month_last_day = date('Y-m-d',strtotime($month_first_day . ' +1 month -1 day'));
								if($category_id > 0){
									foreach ($supplier_infos['category_rebate'][$category_id] as $screbate){
										if(strtotime($month_first_day)>=strtotime($screbate['starttime']) && strtotime($month_last_day)<=strtotime($screbate['endtime'])){
											$rr = $screbate['rebate'];
											break;
										}
									}
								}
														
								$objPHPExcel->getActiveSheet()
										->setCellValueByColumnAndRow(
												$count + $title_count, 3, $rr. '%');		

								$pid_rebate_rate[$sv['payname']][empty($sv['category']) ? 'no' : $sv['category']] = $rr;
														
								$count++;
							}

							//var_dump($pid_rebate_rate);
							
							//标题
							foreach (self::$title_fixed as $k => $v) {
								$objPHPExcel->getActiveSheet()
										->setCellValueByColumnAndRow($k,
												self::TITLE_START_ROW, $v);
							}

							//内容
							$now_month_pids = $month_pids[$month];
							if ($now_month_pids !== NULL) {
								foreach ($now_month_pids as $now_key => $now_value) {
									$now_line = $now_key
											+ self::TITLE_START_ROW + 1;
									//结帐日期
									$objPHPExcel->getActiveSheet()
											->setCellValueByColumnAndRow(0,
													$now_line,
													self::_get_time_range(
															$this->starttime,
															$this->endtime));
									//签约时间
									$objPHPExcel->getActiveSheet()
											->setCellValueByColumnAndRow(1,
													$now_line,
													$pid_infos[$now_value]['time']);
									//执行单号
									$objPHPExcel->getActiveSheet()
											->setCellValueByColumnAndRow(2,
													$now_line, $now_value);
									//客户名称
									$objPHPExcel->getActiveSheet()
											->setCellValueByColumnAndRow(3,
													$now_line,
													$pid_infos[$now_value]['cusname']);
									//所属公司
									$objPHPExcel->getActiveSheet()
											->setCellValueByColumnAndRow(4,
													$now_line,
													$pid_infos[$now_value]['company']);
									//发票类型
									$objPHPExcel->getActiveSheet()
											->setCellValueByColumnAndRow(5,
													$now_line,
													$pid_infos[$now_value]['billtype']);
									//campaign
									$objPHPExcel->getActiveSheet()
											->setCellValueByColumnAndRow(6,
													$now_line,
													$pid_infos[$now_value]['name']);
									//所属部门
									$objPHPExcel->getActiveSheet()
											->setCellValueByColumnAndRow(7,
													$now_line,
													$pid_infos[$now_value]['companyname']
															. $pid_infos[$now_value]['depname']);
									//执行日期
									$objPHPExcel->getActiveSheet()
											->setCellValueByColumnAndRow(8,
													$now_line,
													self::_get_time_range(
															$pid_infos[$now_value]['starttime'],
															$pid_infos[$now_value]['endtime']));
									//应收时间
									$objPHPExcel->getActiveSheet()
											->setCellValueByColumnAndRow(9,
													$now_line,
													$pid_max_paytimes[$now_value]);
									//执行总金额
									$objPHPExcel->getActiveSheet()
											->setCellValueByColumnAndRow(10,
													$now_line,
													$pid_infos[$now_value]['amount']);

									$now_month_quote = 0;
									$now_month_cost = 0;
									
									if ($pid_cys[$now_value][$month] !== NULL) {
										foreach ($pid_cys[$now_value][$month] as $pidcys) {
											foreach ($pidcys as $pidcy) {
												$now_month_quote += $pidcy['finance_quote_amount'];
												$now_month_cost += $pidcy['finance_cost_amount'];
											}
										}
									}
									//当月执行金额
									$objPHPExcel->getActiveSheet()
											->setCellValueByColumnAndRow(11,
													$now_line,
													$now_month_quote);
									//执行总成本
									$objPHPExcel->getActiveSheet()
											->setCellValueByColumnAndRow(12,
													$now_line,
													$pid_infos[$now_value]['allcost']);
									//当月执行成本
									$objPHPExcel->getActiveSheet()
											->setCellValueByColumnAndRow(13,
													$now_line, $now_month_cost);
									//开票总金额
									$objPHPExcel->getActiveSheet()
											->setCellValueByColumnAndRow(17,
													$now_line,
													$invoice_infos[$now_value]['amount']);
									//收款总金额
									$objPHPExcel->getActiveSheet()
											->setCellValueByColumnAndRow(18,
													$now_line,
													$receive_infos[$now_value]['amount']);
									//发票号码
									$objPHPExcel->getActiveSheet()
											->setCellValueByColumnAndRow(19,
													$now_line,
													implode(',',
															$invoice_infos[$now_value]['number']));
									//是否后补
									$objPHPExcel->getActiveSheet()
											->setCellValueByColumnAndRow(20,
													$now_line,
													self::_is_executive_add_after_start(
															intval(
																	$pid_infos[$now_value]['isalter']),
															$pid_infos[$now_value]['createtime'],
															strtotime(
																	$pid_infos[$now_value]['starttime']
																			. ' 23:59:59')));
									//流转天数
									$objPHPExcel->getActiveSheet()
											->setCellValueByColumnAndRow(21,
													$now_line,
													self::_get_remind_days(
															$pid_infos[$now_value]['createtime'],
															$pid_infos[$now_value]['oktime']));

									//备注
									$objPHPExcel->getActiveSheet()
											->setCellValueByColumnAndRow(22,
													$now_line,$pid_infos[$now_value]['remark']);
													
									//各个供应商成本
									if (!empty($pid_suppliers['value'][$now_value])) {
										$rebate = 0;
										foreach ($pid_suppliers['value'][$now_value] as $ps_key => $ps_value) {

											$gmd5 = md5(
													$ps_value['name'] . '|'
															. $ps_value['category']);
											$s_cost = $pid_cys[$now_value][$month][$ps_value['name']][(empty($ps_value['category']) ? 'no' : $ps_value['category'])]['finance_cost_amount'];
											
											$objPHPExcel->getActiveSheet()
													->setCellValueByColumnAndRow(
															array_search(
																	$gmd5,
																	$suppliers['md5'])
																	+ $title_count,
															$now_line,
															$s_cost);
															
											//如果有产品分类按照产品分类返点比例计算，没有的话按照行业分类计算
											if(empty($ps_value['category'])){
												//没有产品分类，使用行业分类
												$rebate_rate = self::_getIndustryRebate($pid_infos[$now_value]['customertype'],$month,$supplier_infos);
											}else{
												//有产品分类
												$rebate_rate = $pid_rebate_rate[$ps_value['name']][$ps_value['category']];
											}			
															
											
											/*			
											//判断是否合同中有行业分类，有的话用行业分类的比例计算
											$rebate_rate = $_supplier_infos_version[$ps_value['name']][$ps_value['category']][$ps_value['isagent2']][$this
															->version]['rebate'];
											if($supplier_industry_rate[$now_value] !== NULL){
												if($supplier_othername[$ps_value['name']] !== NULL){
													if(intval($supplier_othername[$ps_value['name']]['parentid']) === intval($supplier_industry_rate[$now_value]['supplier_id'])){
														$rebate_rate = $supplier_industry_rate[$now_value]['rebate'];
													}
												}
											}
											*/
											//var_dump($now_value . '~~' . $month . '~~~' . $ps_value['name'] . '~~' . $ps_value['category'] . '~~~' . $rebate_rate);
											$rebate += self::_get_rebate($s_cost,$rebate_rate);
										}
									}
									//应得返点
									$objPHPExcel->getActiveSheet()
											->setCellValueByColumnAndRow(15,
													$now_line, $rebate);

									//增值税及附加
									$objPHPExcel->getActiveSheet()
											->setCellValue('O' . $now_line,
													'=(L' . $now_line . '-N'
															. $now_line . '+P'
															. $now_line
															. ')*0.0978');

									//利润小计
									$objPHPExcel->getActiveSheet()
											->setCellValue('Q' . $now_line,
													'=(L' . $now_line . '-N'
															. $now_line . '+P'
															. $now_line
															. ')*0.9022');

								}
							}
						}
						$objPHPExcel->setActiveSheetIndex(0);
						header('Content-Type: application/vnd.ms-excel');
						header(
								'Content-Disposition: attachment;filename="finance_report.xls"');
						header('Cache-Control: max-age=0');

						$objWriter = PHPExcel_IOFactory::createWriter(
								$objPHPExcel, 'Excel5');
						$objWriter->save('php://output');
						$objPHPExcel->disconnectWorksheets();
						unset($objPHPExcel);
					}
				} else {
					return User::no_object('没有符合要求的执行单');
				}
			}
		} else {
			return User::no_permission();
		}
	}
	
	
	public function get_finance_report_backup() {
		if ($this->has_finance_report_permission) {
			$query = 'SELECT a.id,a.pid,FROM_UNIXTIME(a.time) as time,a.time AS createtime,a.isalter,a.company,a.name,a.starttime,a.endtime,a.amount,a.allcost,a.paytimeinfoids,a.costpaymentinfoids,a.support,a.oktime,a.remark,b.cusname,b.billtype,b.customertype,c.companyname,d.depname,e.customertype AS agent2_customertype 
FROM v_last_executive a
LEFT JOIN contract_cus b ON a.cid=b.cid
LEFT JOIN hr_company c ON a.city=c.id
LEFT JOIN hr_department d ON a.dep=d.id
LEFT JOIN executive_agent e ON a.pid=e.pid
WHERE  a.isok=1 AND a.allcost>0 AND a.oktime>=' . strtotime($this->starttime . ' 00:00:00') . ' AND a.oktime<=' . strtotime($this->endtime . ' 23:59:59');

					//var_dump($query);
					//exit;
			$results = $this->db->get_results($query);
			if ($results === NULL) {
				return User::no_object('没有符合要求的执行单');
			} else {

				$ok_pids = array();
				$pid_infos = array();
				$supplier_industry = array();
				foreach ($results as $result) {
					if (!in_array($result->pid, $ok_pids, TRUE)) {
						$ok_pids[] = $result->pid;
						if(!empty($result->agent2_customertype)){
							$customertype = $result->agent2_customertype;
						}else{
							$customertype = !empty($result->customertype) ? $result->customertype : 0;
						}
						$pid_infos[$result->pid] = array('id'=>$result->id,
								'time' => $result->time,
								'isalter' => $result->isalter,
								'company' => self::_get_company(
										intval($result->company)),
								'name' => $result->name,
								'starttime' => $result->starttime,
								'endtime' => $result->endtime,
								'amount' => $result->amount,
								'allcost' => $result->allcost,
								'costpaymentinfoids' => $result
										->costpaymentinfoids,
								'support' => $result->support,
								'cusname' => $result->cusname,
								'billtype' => self::_get_billtype(
										intval($result->billtype)),
								'companyname' => $result->companyname,
								'depname' => $result->depname,
								'oktime' => $result->oktime,
								'createtime' => $result->createtime,
								'paytimeinfoids' => $result->paytimeinfoids,
								'customertype' => $customertype,
								'remark'=>$result->remark);
						$supplier_industry[$result->pid] = $customertype;
					}
				}
				
				//var_dump($ok_pids);
				$industrys = $this->db->get_results('SELECT a.supplier_id,a.industry_id,a.rebate,a.starttime,a.endtime,b.in_invoice_tax_rate
FROM new_supplier_industry_rebate a
LEFT JOIN new_supplier_info b
ON a.supplier_id=b.supplier_id
WHERE a.isok=1 AND b.isok=1');
				$si = array();
				if($industrys !== NULL){
					foreach ($industrys as $industry){
						$si[$industry->industry_id][] = array('supplier_id'=>$industry->supplier_id,'in_invoice_tax_rate'=>$industry->in_invoice_tax_rate,'starttime'=>$industry->starttime,'endtime'=>$industry->endtime,'rebate'=>$industry->rebate);
					}
				}	
				
				//客户行业分类
				$supplier_industry_rate = array();	
				if (!empty($supplier_industry)) {
					foreach ($supplier_industry as $key => $value) {
						$supplier_industry_rate[$key] = $si[$value];
					}
				}
				
				//供应商信息
				$results = $this->db->get_results('SELECT id,supplier_name,parentid FROM new_supplier WHERE isok=1');
				$supplier_othername = array();
				if($results !== NULL){
					foreach ($results as $result){
						$supplier_othername[$result->supplier_name] = array('id'=>$result->id,'parentid'=>$result->parentid);
					}
				}
				
				/*
				if (!empty($supplier_industry)) {
					foreach ($supplier_industry as $key => $value) {
						if (intval($value) === 0) {
							$supplier_industry_rate[$key] = array('rebate' => 0,
									'in_invoice_tax_rate' => 0,
									'supplier_id' => 0);
						} else {
							$row = $this->db
									->get_row(
											'SELECT rebate,in_invoice_tax_rate,supplier_id FROM supplier_industry WHERE id='
													. intval($value)
													. ' AND isok=1');
							if ($row !== NULL) {
								$supplier_industry_rate[$key] = array(
										'rebate' => $row->rebate,
										'in_invoice_tax_rate' => $row
												->in_invoice_tax_rate,
										'supplier_id' => $row->supplier_id);
							}
						}
					}
					
					$results = $this->db->get_results('SELECT id,supplier_name,parentid FROM supplier WHERE isok=1');
					if($results !== NULL){
						foreach ($results as $result){
							$supplier_othername[$result->supplier_name] = array('id'=>$result->id,'parentid'=>$result->parentid);
						}
					}
				}
				*/
				
				//var_dump($supplier_industry_rate);
				
				
				$invoice_infos = array();
				if (!empty($ok_pids)) {
					//开票情况
					$invoices = $this->db
							->get_results(
									'SELECT a.pid,a.amount,b.number FROM finance_invoice a LEFT JOIN finance_invoice_list b ON a.invoice_list_id=b.id WHERE a.pid IN ("'
											. implode('","', $ok_pids)
											. '") AND b.isok=1 AND b.print=1');
					if ($invoices !== NULL) {
						foreach ($invoices as $invoice) {
							$invoice_infos[$invoice->pid]['amount'] += $invoice
									->amount;
							$invoice_infos[$invoice->pid]['number'][] = $invoice
									->number;
						}
					}

					//收款情况
					$receive_infos = array();
					$receives = $this->db
							->get_results(
									'SELECT a.pid,a.amount FROM finance_receivables a LEFT JOIN finance_receivables_list b ON a.receivables_list=b.id WHERE a.pid IN ("'
											. implode('","', $ok_pids)
											. '") AND b.isok=1');
					if ($receives !== NULL) {
						foreach ($receives as $receive) {
							$receive_infos[$receive->pid]['amount'] += $receive
									->amount;
						}
					}
				}

				if (!empty($pid_infos)) {
					$pid_suppliers = array();
					$suppliers = array();
					$pid_cys = array();
					$months = array();
					$month_pids = array();
					$pid_max_paytimes = array();
					foreach ($pid_infos as $pid => $pid_info) {
						if (!empty($pid_info['paytimeinfoids'])) {
							$_paytimeinfoids = Array_Util::my_remove_array_other_value(
									explode('^', $pid_info['paytimeinfoids']),
									array(''));

							if (!empty($_paytimeinfoids)) {
								//应付日期
								$max_month = $this->db
										->get_var(
												'SELECT MAX(paytime) FROM executive_paytime WHERE id IN ('
														. implode(',',
																$_paytimeinfoids)
														. ') AND isok=1');
								if ($max_month !== NULL) {
									$pid_max_paytimes[$pid] = $max_month;
								}
							}
						}

						$results = $this->db->get_results('SELECT pid,payname,deliverytype,ym,quote_amount,finance_quote_amount,cost_amount,finance_cost_amount FROM executive_cy WHERE executive_id=' . intval($pid_info['id']) . ' ORDER BY payname,year,month');
						if($results !== NULL){
							foreach ($results as $result){
								$pn = $result->payname;
								$dt = $result->deliverytype;
								$md5 = md5($pn . '|' . $dt);
								if(!in_array($md5, $suppliers['md5'],TRUE)){
										$suppliers['md5'][] = $md5;
										$suppliers['value'][$md5] = array(
													'payname' => $pn,
													'category' => $dt);
								}
								
								if (!in_array($result->ym, $months, TRUE)) {
									$months[] = $result->ym;
								}
								if (!in_array($result->pid,
									$month_pids[$result->ym], TRUE)) {
									$month_pids[$result->ym][] = $result->pid;
								}
										
										
								if(!in_array($md5, $pid_suppliers['md5'],TRUE)){
									$pid_suppliers['md5'][] = $md5;
									$pid_suppliers['value'][$result->pid][$md5] = array(
												'name' => $pn,
												'category' => $dt);
								}
								
								$pid_cys[$result->pid][$result->ym][$pn][empty(
												$dt) ? 'no'
												: $dt] = array(
												'quote_amount' => $result
														->quote_amount,
												'finance_quote_amount' => $result
														->finance_quote_amount,
												'cost_amount' => $result
														->cost_amount,
												'finance_cost_amount' => $result
														->finance_cost_amount);
														
							}
						}
						
						
						/*
						if (!empty($pid_info['costpaymentinfoids']) || !empty($pid_info['support'])) {
							$_costpaymentinfoids = array();
							if(!empty($pid_info['support'])){
								$supports = explode('|', $pid_info['support']);
								foreach ($supports as $support){
									if(!empty($support)){
										$support = explode('^', $support);
										$dep_cost = $this->db->get_var('SELECT costpaymentinfoids FROM executive_dep WHERE id=' . intval($support[1]));
										if(!empty($dep_cost)){
											$dep_cost = explode('^', $dep_cost);
											foreach ($dep_cost as $dc){
												if(!empty($dc)){
													$_costpaymentinfoids[] = $dc;
												}
											}
										}
									}
								}
							}
							
							if(!empty($pid_info['costpaymentinfoids'])){
								$costpaymentinfoids = explode('^', $pid_info['costpaymentinfoids']);
								foreach ($costpaymentinfoids as $costpaymentinfoid){
									if(!empty($costpaymentinfoid)){
										$_costpaymentinfoids[] = $costpaymentinfoid;
									}
								}
							}

							if (!empty($_costpaymentinfoids)) {
								//供应商
								$_paynames = $this->db
										->get_results(
												'SELECT payname,payamount,category,isagent2 FROM executive_paycost WHERE id IN ('
														. implode(',',
																$_costpaymentinfoids)
														. ')');
								if ($_paynames !== NULL) {
									foreach ($_paynames as $_payname) {
										$pn = $_payname->payname;
										$ct = empty($_payname->category) ? 'no'
												: $_payname->category;
										$ia2 = $_payname->isagent2;
										$kk = md5($pn . '|' . $ct . '|' . $ia2);

										if (!in_array($kk, $suppliers['md5'],
												TRUE)) {
											$suppliers['md5'][] = $kk;
											$suppliers['value'][$kk] = array(
													'payname' => $pn,
													'category' => $ct,
													'isagent2' => $ia2);
										}
										$pid_suppliers[$pid][] = array(
												'name' => $pn,
												'amount' => $_payname
														->payamount,
												'category' => $ct,
												'isagent2' => $ia2);
									}
								}

								//拆月
								$_cys = $this->db
										->get_results(
												'SELECT a.pid,a.payname,a.ym,a.quote_amount,a.finance_quote_amount,a.cost_amount,a.finance_cost_amount,b.category,b.isagent2 FROM executive_cy a LEFT JOIN executive_paycost b ON a.paycost_id=b.id WHERE a.paycost_id IN ('
														. implode(',',
																$_costpaymentinfoids)
														. ') ORDER BY a.payname,a.year,a.month');
								if ($_cys !== NULL) {
									foreach ($_cys as $_cy) {
										if (!in_array($_cy->ym, $months, TRUE)) {
											$months[] = $_cy->ym;
										}
										if (!in_array($pid,
												$month_pids[$_cy->ym], TRUE)) {
											$month_pids[$_cy->ym][] = $pid;
										}
										$pid_cys[$pid][$_cy->ym][$_cy->payname][empty(
												$_cy->category) ? 'no'
												: $_cy->category][$_cy
												->isagent2] = array(
												'quote_amount' => $_cy
														->quote_amount,
												'finance_quote_amount' => $_cy
														->finance_quote_amount,
												'cost_amount' => $_cy
														->cost_amount,
												'finance_cost_amount' => $_cy
														->finance_cost_amount);
									}
								}
							}
						}
						*/
					}


					if (!empty($months)) {
						$supplier_infos = Supplier::getInstance();
						$supplier_industry_infos = Supplier::getIndustryInstance();
						
						$_supplier_infos_version = $supplier_infos['supplier'];
						
						
						
						$objPHPExcel = new PHPExcel();
						PHPExcel_Settings::setCacheStorageMethod(
								PHPExcel_CachedObjectStorageFactory::cache_in_memory_serialized);
						foreach ($months as $key => $month) {
							
							$pid_rebate_rate = array();
							
							if ($key === 0) {
								$objPHPExcel->setActiveSheetIndex($key);
								$objPHPExcel->getActiveSheet()
										->setTitle($month);
							} else {
								$objPHPExcel
										->addSheet(
												new PHPExcel_Worksheet(
														$objPHPExcel, $month));
							}
							$objPHPExcel->setActiveSheetIndex($key);
							$objPHPExcel->getActiveSheet()
									->setCellValueByColumnAndRow(0, 1,
											'项目执行明细表');

							//供应商
							$title_count = count(self::$title_fixed);
							$count = 0;

							$_suppliers = $suppliers['value'];
							foreach ($_suppliers as $sk => $sv) {
								if ($count === 0) {
									$objPHPExcel->getActiveSheet()
											->setCellValueByColumnAndRow(
													$title_count - 1, 2,
													'供应商进票税率');
									$objPHPExcel->getActiveSheet()
											->setCellValueByColumnAndRow(
													$title_count - 1, 3,
													'产品分类返点比率');
								}
								
								$objPHPExcel->getActiveSheet()
										->setCellValueByColumnAndRow(
												$count + $title_count, 1,
												$sv['payname'] . ' -- '
														. (empty($sv['category']) ? '无产品分类'
																: $sv['category']));
																
								$iitr = 0;
								$supplier_id = 0;
								foreach ($supplier_infos['supplier'] as $skey=>$siv){
									if($siv['sn'] === $sv['payname']){
										$iitr = $siv['iitr'] ;
										$supplier_id = $skey;
										break;
									}
								}
								
								$objPHPExcel->getActiveSheet()
										->setCellValueByColumnAndRow(
												$count + $title_count, 2, $iitr . '%');
												
								$category_id = 0;
								$supplier_category = $supplier_infos['supplier_category'][$supplier_id];
								if(!empty($supplier_category)){
									foreach ($supplier_category as $sc){
										if($supplier_infos['category'][$sc] === $sv['category']){
											$category_id = $sc;
											break;
										}
									}
								}
								
								$rr = 0;
								$month_first_day = date('Y-m-01',strtotime($month));
								$month_last_day = date('Y-m-d',strtotime($month_first_day . ' +1 month -1 day'));
								if($category_id > 0){
									foreach ($supplier_infos['category_rebate'][$category_id] as $screbate){
										if(strtotime($month_first_day)>=strtotime($screbate['starttime']) && strtotime($month_last_day)<=strtotime($screbate['endtime'])){
											$rr = $screbate['rebate'];
											break;
										}
									}
								}
														
								$objPHPExcel->getActiveSheet()
										->setCellValueByColumnAndRow(
												$count + $title_count, 3, $rr. '%');		

								$pid_rebate_rate[$sv['payname']][empty($sv['category']) ? 'no' : $sv['category']] = $rr;
														
								$count++;
							}

							//var_dump($pid_rebate_rate);
							
							//标题
							foreach (self::$title_fixed as $k => $v) {
								$objPHPExcel->getActiveSheet()
										->setCellValueByColumnAndRow($k,
												self::TITLE_START_ROW, $v);
							}

							//内容
							$now_month_pids = $month_pids[$month];
							if ($now_month_pids !== NULL) {
								foreach ($now_month_pids as $now_key => $now_value) {
									$now_line = $now_key
											+ self::TITLE_START_ROW + 1;
									//结帐日期
									$objPHPExcel->getActiveSheet()
											->setCellValueByColumnAndRow(0,
													$now_line,
													self::_get_time_range(
															$this->starttime,
															$this->endtime));
									//签约时间
									$objPHPExcel->getActiveSheet()
											->setCellValueByColumnAndRow(1,
													$now_line,
													$pid_infos[$now_value]['time']);
									//执行单号
									$objPHPExcel->getActiveSheet()
											->setCellValueByColumnAndRow(2,
													$now_line, $now_value);
									//客户名称
									$objPHPExcel->getActiveSheet()
											->setCellValueByColumnAndRow(3,
													$now_line,
													$pid_infos[$now_value]['cusname']);
									//所属公司
									$objPHPExcel->getActiveSheet()
											->setCellValueByColumnAndRow(4,
													$now_line,
													$pid_infos[$now_value]['company']);
									//发票类型
									$objPHPExcel->getActiveSheet()
											->setCellValueByColumnAndRow(5,
													$now_line,
													$pid_infos[$now_value]['billtype']);
									//campaign
									$objPHPExcel->getActiveSheet()
											->setCellValueByColumnAndRow(6,
													$now_line,
													$pid_infos[$now_value]['name']);
									//所属部门
									$objPHPExcel->getActiveSheet()
											->setCellValueByColumnAndRow(7,
													$now_line,
													$pid_infos[$now_value]['companyname']
															. $pid_infos[$now_value]['depname']);
									//执行日期
									$objPHPExcel->getActiveSheet()
											->setCellValueByColumnAndRow(8,
													$now_line,
													self::_get_time_range(
															$pid_infos[$now_value]['starttime'],
															$pid_infos[$now_value]['endtime']));
									//应收时间
									$objPHPExcel->getActiveSheet()
											->setCellValueByColumnAndRow(9,
													$now_line,
													$pid_max_paytimes[$now_value]);
									//执行总金额
									$objPHPExcel->getActiveSheet()
											->setCellValueByColumnAndRow(10,
													$now_line,
													$pid_infos[$now_value]['amount']);

									$now_month_quote = 0;
									$now_month_cost = 0;
									
									if ($pid_cys[$now_value][$month] !== NULL) {
										foreach ($pid_cys[$now_value][$month] as $pidcys) {
											foreach ($pidcys as $pidcy) {
												$now_month_quote += $pidcy['finance_quote_amount'];
												$now_month_cost += $pidcy['finance_cost_amount'];
											}
										}
									}
									//当月执行金额
									$objPHPExcel->getActiveSheet()
											->setCellValueByColumnAndRow(11,
													$now_line,
													$now_month_quote);
									//执行总成本
									$objPHPExcel->getActiveSheet()
											->setCellValueByColumnAndRow(12,
													$now_line,
													$pid_infos[$now_value]['allcost']);
									//当月执行成本
									$objPHPExcel->getActiveSheet()
											->setCellValueByColumnAndRow(13,
													$now_line, $now_month_cost);
									//开票总金额
									$objPHPExcel->getActiveSheet()
											->setCellValueByColumnAndRow(17,
													$now_line,
													$invoice_infos[$now_value]['amount']);
									//收款总金额
									$objPHPExcel->getActiveSheet()
											->setCellValueByColumnAndRow(18,
													$now_line,
													$receive_infos[$now_value]['amount']);
									//发票号码
									$objPHPExcel->getActiveSheet()
											->setCellValueByColumnAndRow(19,
													$now_line,
													implode(',',
															$invoice_infos[$now_value]['number']));
									//是否后补
									$objPHPExcel->getActiveSheet()
											->setCellValueByColumnAndRow(20,
													$now_line,
													self::_is_executive_add_after_start(
															intval(
																	$pid_infos[$now_value]['isalter']),
															$pid_infos[$now_value]['createtime'],
															strtotime(
																	$pid_infos[$now_value]['starttime']
																			. ' 23:59:59')));
									//流转天数
									$objPHPExcel->getActiveSheet()
											->setCellValueByColumnAndRow(21,
													$now_line,
													self::_get_remind_days(
															$pid_infos[$now_value]['createtime'],
															$pid_infos[$now_value]['oktime']));

									//备注
									$objPHPExcel->getActiveSheet()
											->setCellValueByColumnAndRow(22,
													$now_line,$pid_infos[$now_value]['remark']);
													
									//各个供应商成本
									if (!empty($pid_suppliers['value'][$now_value])) {
										$rebate = 0;
										foreach ($pid_suppliers['value'][$now_value] as $ps_key => $ps_value) {

											$gmd5 = md5(
													$ps_value['name'] . '|'
															. $ps_value['category']);
											$s_cost = $pid_cys[$now_value][$month][$ps_value['name']][(empty($ps_value['category']) ? 'no' : $ps_value['category'])]['finance_cost_amount'];
											
											$objPHPExcel->getActiveSheet()
													->setCellValueByColumnAndRow(
															array_search(
																	$gmd5,
																	$suppliers['md5'])
																	+ $title_count,
															$now_line,
															$s_cost);
															
											//如果有产品分类按照产品分类返点比例计算，没有的话按照行业分类计算
											if(empty($ps_value['category'])){
												//没有产品分类，使用行业分类
												$rebate_rate = self::_getIndustryRebate($pid_infos[$now_value]['customertype'],$month,$supplier_infos);
											}else{
												//有产品分类
												$rebate_rate = $pid_rebate_rate[$ps_value['name']][$ps_value['category']];
											}			
															
											
											/*			
											//判断是否合同中有行业分类，有的话用行业分类的比例计算
											$rebate_rate = $_supplier_infos_version[$ps_value['name']][$ps_value['category']][$ps_value['isagent2']][$this
															->version]['rebate'];
											if($supplier_industry_rate[$now_value] !== NULL){
												if($supplier_othername[$ps_value['name']] !== NULL){
													if(intval($supplier_othername[$ps_value['name']]['parentid']) === intval($supplier_industry_rate[$now_value]['supplier_id'])){
														$rebate_rate = $supplier_industry_rate[$now_value]['rebate'];
													}
												}
											}
											*/
											//var_dump($now_value . '~~' . $month . '~~~' . $ps_value['name'] . '~~' . $ps_value['category'] . '~~~' . $rebate_rate);
											$rebate += self::_get_rebate($s_cost,$rebate_rate);
										}
									}
									//应得返点
									$objPHPExcel->getActiveSheet()
											->setCellValueByColumnAndRow(15,
													$now_line, $rebate);

									//增值税及附加
									$objPHPExcel->getActiveSheet()
											->setCellValue('O' . $now_line,
													'=(L' . $now_line . '-N'
															. $now_line . '+P'
															. $now_line
															. ')*0.0978');

									//利润小计
									$objPHPExcel->getActiveSheet()
											->setCellValue('Q' . $now_line,
													'=(L' . $now_line . '-N'
															. $now_line . '+P'
															. $now_line
															. ')*0.9022');

								}
							}
						}
						$objPHPExcel->setActiveSheetIndex(0);
						header('Content-Type: application/vnd.ms-excel');
						header(
								'Content-Disposition: attachment;filename="finance_report.xls"');
						header('Cache-Control: max-age=0');

						$objWriter = PHPExcel_IOFactory::createWriter(
								$objPHPExcel, 'Excel5');
						$objWriter->save('php://output');
						$objPHPExcel->disconnectWorksheets();
						unset($objPHPExcel);
					}
				} else {
					return User::no_object('没有符合要求的执行单');
				}
			}
		} else {
			return User::no_permission();
		}
	}


	private static function _getIndustryRebate($customertype,$month,$supplier_infos){
		$rr = 0;
		$month_first_day = date('Y-m-01',strtotime($month));
		$month_last_day = date('Y-m-d',strtotime($month_first_day . ' +1 month -1 day'));
							
		foreach ($supplier_infos['industry_rebate'][$customertype] as $screbate){
			if(strtotime($month_first_day)>=strtotime($screbate['starttime']) && strtotime($month_last_day)<=strtotime($screbate['endtime'])){
				$rr = $screbate['rebate'];
				break;
			}
		}
		return $rr;						
	}
}
