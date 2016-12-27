<?php
class Finance_Report extends User {
	private $starttime;
	private $endtime;
	private $has_finance_report_permission = FALSE;

	private static $title_fixed = array('序号', '签约日期', '执行单号', '客户', '所属公司',
			'所属发票', '直客', '4A', 'Compaign', '所属地区/部门/团队', '项目管理-媒体', '执行单类型',
			'投放时间', '应收时间', '执行总金额', '当月执行金额', '执行总成本', '当月执行成本', '其他费用',
			'营业税及附加', '应得返点', '利润小计', '备注', '当月执行成本明细', '返点比例', '供应商名称',
			'投放媒体简称', '供应商产品分类', '供应商客户行业分类');

	const TITLE_START_ROW = 3;

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

	private static function _getCompany($company) {
		switch ((int) $company) {
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

	private static function _getPidType($type) {
		switch ((int) $type) {
		case 1:
			return '普通';
			break;
		case 2:
			return '预充值';
			break;
		case 3:
			return '结算';
			break;
		default:
			return '';
		}
	}

	private static function _getBilltype($billtype) {
		if ((int) $billtype === 2) {
			return '服务发票';
		}
		return '广告发票';
	}

	private static function _getCidCustomType($type1) {
		if ((int) $type1 === 0) {
			return '直客';
		}
		return '代理商';
	}

	private static function _getDepInfo($depInfo, $teamInfo, $belong_dep,
			$belong_team) {
		$_dep = $depInfo[$belong_dep];
		$_team = $teamInfo['team'][$belong_team];
		if ($_dep === NULL) {
			$_dep = '';
		} else {
			$_dep = $_dep[1] . $_dep[0];
		}

		if ($_team !== NULL) {
			$_team = $_team['teamname'];
		}
		return sprintf('%s %s', $_dep, $_team);
	}

	private static function _getTimeRange($starttime, $endtime) {
		return $starttime . '/' . $endtime;
	}

	private function _getReportPids() {
		$pids = array();
		$cids = array();
		$paytimes = array();
		$results = $this->db
				->get_results(
						'SELECT b.id,b.pid,b.cid,b.city,b.dep,b.team,b.name,b.type,b.amount,b.allcost,b.starttime,b.endtime,b.company,b.time,b.paytimeinfoids FROM (SELECT a.* FROM (SELECT id,pid,cid,city,dep,team,isalter,isok,oktime,name,type,amount,allcost,starttime,endtime,company,time,paytimeinfoids FROM executive WHERE isok<>-1 ORDER BY pid,isalter DESC) a GROUP BY pid) b WHERE b.isok=1 AND b.oktime>='
								. strtotime($this->starttime . ' 00:00:00')
								. ' AND b.oktime<='
								. strtotime($this->endtime . ' 23:59:59'));
		if ($results !== NULL) {
			$tmp_cids = array();
			$tmp_paytimes = array();
			foreach ($results as $result) {
				$pids[$result->id] = array('pid' => $result->pid,
						'cid' => $result->cid, 'city' => $result->city,
						'dep' => $result->dep, 'team' => $result->team,
						'name' => $result->name, 'type' => $result->type,
						'amount' => $result->amount,
						'allcost' => $result->allcost,
						'starttime' => $result->starttime,
						'endtime' => $result->endtime,
						'company' => $result->company, 'time' => $result->time);
				if (!in_array($result->cid, $tmp_cids)) {
					$tmp_cids[] = $result->cid;
				}

				$paytimeinfoids = $result->paytimeinfoids;
				if (!empty($paytimeinfoids)) {
					$paytimeinfoids = explode('^', $paytimeinfoids);
					foreach ($paytimeinfoids as $paytimeinfoid) {
						if (!empty($paytimeinfoid)) {
							$tmp_paytimes['relate'][$result->id][] = (int) $paytimeinfoid;
							$tmp_paytimes['infoid'][] = $paytimeinfoid;
						}
					}
				}
			}

			if (!empty($tmp_cids)) {
				$results = $this->db
						->get_results(
								'SELECT cid,cusname,type1,billtype FROM contract_cus WHERE cid IN ("'
										. implode('","', $tmp_cids) . '")');
				if ($results !== NULL) {
					foreach ($results as $result) {
						$cids[$result->cid] = array(
								'cusname' => $result->cusname,
								'type1' => $result->type1,
								'billtype' => $result->billtype);
					}
				}
			}

			if (!empty($tmp_paytimes)) {
				$results = $this->db
						->get_results(
								'SELECT id,paytime FROM executive_paytime WHERE id IN ('
										. implode(',', $tmp_paytimes['infoid'])
										. ')');
				if ($results !== NULL) {
					foreach ($results as $result) {
						foreach ($tmp_paytimes['relate'] as $key => $value) {
							if (in_array((int) ($result->id), $value, TRUE)) {
								$paytimes[$key][] = $result->paytime;
							}
						}
					}
				}
			}
		}
		return array('pids' => $pids, 'cids' => $cids, 'paytimes' => $paytimes);
	}

	private function _getAmountCY($executive_ids) {
		$cys = array();
		$results = $this->db
				->get_results(
						'SELECT executive_id,pid,ym,quote_amount FROM executive_amount_cy WHERE executive_id IN ('
								. implode(',', $executive_ids) . ')');
		if ($results !== NULL) {
			foreach ($results as $result) {
				$cys[$result->ym][$result->executive_id] += $result
						->quote_amount;
			}
		}
		return $cys;
	}

	private function _getCostCY($executive_ids) {
		$cys = array();
		$results = $this->db
				->get_results(
						'SELECT a.*,b.supplier_name,c.media_short,d.category_name,e.industry_name
FROM
(
SELECT executive_id,pid,ym,cost_amount,is_support,support_dep,supplier_id,supplier_short_id,category_id,industry_id from executive_cy WHERE executive_id IN ('
								. implode(',', $executive_ids)
								. ') 
) a
LEFT JOIN new_supplier b
ON a.supplier_id=b.id
LEFT JOIN finance_supplier_short c
ON a.supplier_short_id=c.id
LEFT JOIN new_supplier_category d
ON a.category_id=d.id
LEFT JOIN new_supplier_industry e
ON a.industry_id=e.id');

		if ($results !== NULL) {
			foreach ($results as $result) {
				$cys['cy'][$result->ym][] = array(
						'id' => $result->executive_id, 'pid' => $result->pid,
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
						'industry_name' => $result->industry_name);
				$cys['cy_sum'][$result->ym][$result->executive_id] += $result
						->cost_amount;
			}
		}
		return $cys;
	}

	public function get_finance_report() {
		if ($this->has_finance_report_permission) {
			$pids = $this->_getReportPids();
			if (!empty($pids['pids'])) {
				//开始生成excel
				$objPHPExcel = new PHPExcel();
				PHPExcel_Settings::setCacheStorageMethod(
						PHPExcel_CachedObjectStorageFactory::cache_in_memory_serialized);

				//成本拆月
				$cys = $this->_getCostCY(array_keys($pids['pids']));
				$cys_n = $cys['cy'];

				//金额拆月
				$amount_cys = $this->_getAmountCY(array_keys($pids['pids']));

				if (!empty($cys_n)) {
					$months = array_keys($cys_n);
					sort($months);

					//返点比例
					$rebates = Setting_Rebate::getRebateInstance();

					//部门信息
					$dep = Dep::getInstance();
					$team = Team::getInstance();

					foreach ($months as $key => $month) {
						if ($key === 0) {
							$objPHPExcel->setActiveSheetIndex($key);
							$objPHPExcel->getActiveSheet()->setTitle($month);
							$objPHPExcel->getActiveSheet()
									->setCellValueByColumnAndRow(0, 1,
											'媒体投放量汇总');

						} else {
							$objPHPExcel
									->addSheet(
											new PHPExcel_Worksheet(
													$objPHPExcel, $month));
						}

						$objPHPExcel->setActiveSheetIndex($key);
						//标题
						foreach (self::$title_fixed as $k => $v) {
							$objPHPExcel->getActiveSheet()
									->setCellValueByColumnAndRow($k,
											self::TITLE_START_ROW, $v);
						}

						//内容
						$contents = $cys_n[$month];
						$now_pid = '';
						foreach ($contents as $num => $content) {
							$now_line = $num + self::TITLE_START_ROW + 1;
							//序号
							$objPHPExcel->getActiveSheet()
									->setCellValueByColumnAndRow(0, $now_line,
											$month);

							//签约日期
							$objPHPExcel->getActiveSheet()
									->setCellValueByColumnAndRow(1, $now_line,
											date('Y-m-d H:i:s',
													$pids['pids'][$content['id']]['time']));

							//执行单号
							$objPHPExcel->getActiveSheet()
									->setCellValueByColumnAndRow(2, $now_line,
											$content['pid']);

							//客户
							$objPHPExcel->getActiveSheet()
									->setCellValueByColumnAndRow(3, $now_line,
											$pids['cids'][$pids['pids'][$content['id']]['cid']]['cusname']);

							//所属公司
							$objPHPExcel->getActiveSheet()
									->setCellValueByColumnAndRow(4, $now_line,
											self::_getCompany(
													$pids['pids'][$content['id']]['company']));

							//所属发票
							$objPHPExcel->getActiveSheet()
									->setCellValueByColumnAndRow(5, $now_line,
											self::_getBilltype(
													$pids['cids'][$pids['pids'][$content['id']]['cid']]['billtype']));

							//直客
							$objPHPExcel->getActiveSheet()
									->setCellValueByColumnAndRow(6, $now_line,
											self::_getCidCustomType(
													$pids['cids'][$pids['pids'][$content['id']]['cid']['type1']]));

							//4A（留空）
							$objPHPExcel->getActiveSheet()
									->setCellValueByColumnAndRow(7, $now_line,
											'');

							//campaign
							$objPHPExcel->getActiveSheet()
									->setCellValueByColumnAndRow(8, $now_line,
											$pids['pids'][$content['id']]['name']);

							//项目管理-客户
							$objPHPExcel->getActiveSheet()
									->setCellValueByColumnAndRow(9, $now_line,
											self::_getDepInfo($dep, $team,
													$pids['pids'][$content['id']]['dep'],
													$pids['pids'][$content['id']]['team']));

							//项目管理-媒体（留空）
							$objPHPExcel->getActiveSheet()
									->setCellValueByColumnAndRow(10, $now_line,
											'');

							//执行单类型
							$objPHPExcel->getActiveSheet()
									->setCellValueByColumnAndRow(11, $now_line,
											self::_getPidType(
													$pids['pids'][$content['id']]['type']));

							//投放时间
							$objPHPExcel->getActiveSheet()
									->setCellValueByColumnAndRow(12, $now_line,
											self::_getTimeRange(
													$pids['pids'][$content['id']]['starttime'],
													$pids['pids'][$content['id']]['endtime']));

							//应收时间
							$objPHPExcel->getActiveSheet()
									->setCellValueByColumnAndRow(13, $now_line,
											implode(',',
													$pids['paytimes'][$content['id']]));

							if ($now_pid !== $content['pid']) {
								//同一执行单只显示第一行

								//执行总金额
								$objPHPExcel->getActiveSheet()
										->setCellValueByColumnAndRow(14,
												$now_line,
												$pids['pids'][$content['id']]['amount']);
								$objPHPExcel->getActiveSheet()
										->getStyleByColumnAndRow(14, $now_line)
										->getNumberFormat()
										->setFormatCode(
												PHPExcel_Style_NumberFormat::FORMAT_NUMBER_COMMA_SEPARATED3);

								//当月执行金额
								$objPHPExcel->getActiveSheet()
										->setCellValueByColumnAndRow(15,
												$now_line,
												$amount_cys[$month][$content['id']]);
								$objPHPExcel->getActiveSheet()
										->getStyleByColumnAndRow(15, $now_line)
										->getNumberFormat()
										->setFormatCode(
												PHPExcel_Style_NumberFormat::FORMAT_NUMBER_COMMA_SEPARATED3);

								//执行总成本
								$objPHPExcel->getActiveSheet()
										->setCellValueByColumnAndRow(16,
												$now_line,
												$pids['pids'][$content['id']]['allcost']);
								$objPHPExcel->getActiveSheet()
										->getStyleByColumnAndRow(16, $now_line)
										->getNumberFormat()
										->setFormatCode(
												PHPExcel_Style_NumberFormat::FORMAT_NUMBER_COMMA_SEPARATED3);

								//当月执行成本
								$objPHPExcel->getActiveSheet()
										->setCellValueByColumnAndRow(17,
												$now_line,
												$cys['cy_sum'][$month][$content['id']]);
								$objPHPExcel->getActiveSheet()
										->getStyleByColumnAndRow(17, $now_line)
										->getNumberFormat()
										->setFormatCode(
												PHPExcel_Style_NumberFormat::FORMAT_NUMBER_COMMA_SEPARATED3);

								$now_pid = $content['pid'];
							}

							//其他费用（留空）
							$objPHPExcel->getActiveSheet()
									->setCellValueByColumnAndRow(18, $now_line,
											'');

							//营业税及附加
							$objPHPExcel->getActiveSheet()
									->setCellValue('T' . $now_line,
											'=IF(F' . $now_line . '="广告发票",(P'
													. $now_line . '-X'
													. $now_line . '+U'
													. $now_line
													. ')*0.0996,IF(F'
													. $now_line . '="服务发票",(P'
													. $now_line . '-X'
													. $now_line . '+U'
													. $now_line . ')*0.0678))');
							$objPHPExcel->getActiveSheet()
									->getStyle('T' . $now_line)
									->getNumberFormat()
									->setFormatCode(
											PHPExcel_Style_NumberFormat::FORMAT_NUMBER_COMMA_SEPARATED3);

							//应得返点
							$objPHPExcel->getActiveSheet()
									->setCellValue('U' . $now_line,
											'=X' . $now_line . '*Y' . $now_line);
							$objPHPExcel->getActiveSheet()
									->getStyle('U' . $now_line)
									->getNumberFormat()
									->setFormatCode(
											PHPExcel_Style_NumberFormat::FORMAT_NUMBER_COMMA_SEPARATED3);

							//利润小计
							$objPHPExcel->getActiveSheet()
									->setCellValue('V' . $now_line,
											'=P' . $now_line . '-X' . $now_line
													. '-T' . $now_line . '-S'
													. $now_line . '+U'
													. $now_line);
							$objPHPExcel->getActiveSheet()
									->getStyle('V' . $now_line)
									->getNumberFormat()
									->setFormatCode(
											PHPExcel_Style_NumberFormat::FORMAT_NUMBER_COMMA_SEPARATED3);

							//备注（留空）
							$objPHPExcel->getActiveSheet()
									->setCellValueByColumnAndRow(22, $now_line,
											'');

							//当月执行成本明细
							$objPHPExcel->getActiveSheet()
									->setCellValueByColumnAndRow(23, $now_line,
											$content['cost_amount']);
							$objPHPExcel->getActiveSheet()
									->getStyleByColumnAndRow(23, $now_line)
									->getNumberFormat()
									->setFormatCode(
											PHPExcel_Style_NumberFormat::FORMAT_NUMBER_COMMA_SEPARATED3);

							//返点比例
							$rebid = $content['supplier_id'] . '|'
									. $content['supplier_short_id'] . '|'
									. $content['category_id'] . '|'
									. $content['industry_id'];

							$objPHPExcel->getActiveSheet()
									->setCellValueByColumnAndRow(24, $now_line,
											!empty($rebates[$rebid]) ? ($rebates[$rebid]
															/ 100) : 0);
							$objPHPExcel->getActiveSheet()
									->getStyleByColumnAndRow(24, $now_line)
									->getNumberFormat()
									->setFormatCode(
											PHPExcel_Style_NumberFormat::FORMAT_PERCENTAGE_00);

							//媒体
							$objPHPExcel->getActiveSheet()
									->setCellValueByColumnAndRow(25, $now_line,
											$content['supplier_name']);

							//媒体简称
							$objPHPExcel->getActiveSheet()
									->setCellValueByColumnAndRow(26, $now_line,
											$content['media_short']);

							//投放产品分类
							$objPHPExcel->getActiveSheet()
									->setCellValueByColumnAndRow(27, $now_line,
											$content['category_name']);

							//客户行业分类
							$objPHPExcel->getActiveSheet()
									->setCellValueByColumnAndRow(28, $now_line,
											$content['industry_name']);
						}
					}
				}

				//生成
				$objPHPExcel->setActiveSheetIndex(0);
				header('Content-Type: application/vnd.ms-excel');
				header(
						'Content-Disposition: attachment;filename="'
								. urlencode(
										$this->starttime . '~' . $this->endtime
												. '_财务大表') . '.xls"');
				header('Cache-Control: max-age=0');

				$objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel,
						'Excel5');
				$objWriter->save('php://output');
				$objPHPExcel->disconnectWorksheets();
				unset($objPHPExcel);
			} else {
				return User::no_object('没有符合要求的执行单');
			}
		} else {
			return User::no_permission();
		}
	}
}
