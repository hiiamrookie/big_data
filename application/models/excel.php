<?php
class Excel extends User {
	private $apply_id;
	private $type;
	private $has_finance_excel_permission = FALSE;

	public function __construct($fields = array()) {
		parent::__construct();
		if (in_array($this->getUsername(),
				$GLOBALS['manager_finance_permission'], TRUE)
				|| intval($this->getBelong_dep()) === 2) {
			$this->has_finance_excel_permission = TRUE;
		}
		if (!empty($fields)) {
			foreach ($this as $key => $value) {
				if ($fields[$key] !== NULL
						&& !in_array($key,
								array('has_finance_excel_permission '), TRUE)) {
					$this->$key = $fields[$key];
				}
			}
		}
	}

	public function get_datas($action) {
		switch ($action) {
		case 'payment':
			return $this->_getPaymentApplyExcel();
		default:
			return NULL;
		}
	}

	/**
	 * 
	 * 付款申请单
	 */
	private function _getPaymentApplyExcel() {
		$datas = array();
		$row = NULL;
		$logs = array();
		if ($this->type === 'pc') {
			//个人申请合同款
			$row = $this->db
					->get_row(
							'SELECT a.id,a.payment_date,b.media_name,b.account_bank,b.account ,c.realname,d.companyname,e.depname,f.teamname
FROM finance_payment_person_apply a
LEFT JOIN finance_payment_media_info b
ON a.media_info_id=b.id
LEFT JOIN users c
ON a.user=c.uid
LEFT JOIN hr_company d
ON c.city=d.id
LEFT JOIN hr_department e
ON c.dep=e.id
LEFT JOIN hr_team f
ON c.team=f.id
WHERE a.id=' . intval($this->apply_id) . ' AND a.isok=1');

		} else if ($this->type === 'pd') {
			//个人申请保证金
			$row = $this->db
					->get_row(
							'SELECT a.id,a.payment_date,b.media_name,b.account_bank,b.account ,c.realname,d.companyname,e.depname,f.teamname
FROM finance_payment_person_deposit_apply a
LEFT JOIN finance_payment_media_info b
ON a.media_info_id=b.id
LEFT JOIN users c
ON a.user=c.uid
LEFT JOIN hr_company d
ON c.city=d.id
LEFT JOIN hr_department e
ON c.dep=e.id
LEFT JOIN hr_team f
ON c.team=f.id
WHERE a.id=' . intval($this->apply_id) . ' AND a.isok=1');
		}

		if ($row !== NULL) {
			//获取日志
			if($this->type === 'pc'){
				//logs
			$logs = $this->db->get_results('SELECT * FROM
(
SELECT FROM_UNIXTIME(a.time) AS time,a.type,a.id ,b.realname
FROM finance_payment_person_apply_log a
LEFT JOIN users b
ON a.uid=b.uid
WHERE a.payment_id=(SELECT payment_id FROM finance_payment_person_apply WHERE id=' . intval($this->apply_id) . ') ORDER BY a.time DESC
) m GROUP BY m.type ORDER BY m.time DESC');
			} else if ($this->type === 'pd') {
				$logs = $this->db->get_results('SELECT * FROM
(
SELECT FROM_UNIXTIME(a.time) AS time,a.type,a.id ,b.realname
FROM finance_payment_person_deposit_apply_log a
LEFT JOIN users b
ON a.uid=b.uid
WHERE a.payment_id=(SELECT payment_id FROM finance_payment_person_deposit_apply WHERE id=' . intval($this->apply_id) . ') ORDER BY a.time DESC
) m GROUP BY m.type ORDER BY m.time DESC');
			}
			
			if(!empty($logs)){
				foreach ($logs as $log){
					
					if(strpos($log->type, '新建个人付款申请') !== FALSE && $this->type === 'pc'
					|| strpos($log->type, '新建个人保证金付款申请') !== FALSE && $this->type === 'pd'){
						//A30申请人
						$datas['A30'] = array('v' => $log->realname,'t' => PHPExcel_Cell_DataType::TYPE_STRING);
						$datas['A31'] = array('v' => '日期：' .  $log->time,'t' => PHPExcel_Cell_DataType::TYPE_STRING);
					}else if(strpos($log->type, '部门leader审核 确认') !== FALSE){
						//B30部门经理
						$datas['B30'] = array('v' => $log->realname,'t' => PHPExcel_Cell_DataType::TYPE_STRING);
						$datas['B31'] = array('v' => '日期：' .  $log->time,'t' => PHPExcel_Cell_DataType::TYPE_STRING);
					}else if(strpos($log->type, '财务部审核 确认') !== FALSE){
						//C30财务部
						$datas['C30'] = array('v' => $log->realname,'t' => PHPExcel_Cell_DataType::TYPE_STRING);
						$datas['C31'] = array('v' => '日期：' .  $log->time,'t' => PHPExcel_Cell_DataType::TYPE_STRING);
					}
				}
			}
			
			//部门
			$datas['B3'] = array(
					'v' => $row->companyname . ' ' . $row->depname . ' '
							. $row->teamnane,
					't' => PHPExcel_Cell_DataType::TYPE_STRING);
			//日期
			$datas['F3'] = array('v' => $row->payment_date,
					't' => PHPExcel_Cell_DataType::TYPE_STRING);
			//申请人姓名
			$datas['B5'] = array('v' => $row->realname,
					't' => PHPExcel_Cell_DataType::TYPE_STRING);
			//对方公司
			$datas['E5'] = array('v' => $row->media_name,
					't' => PHPExcel_Cell_DataType::TYPE_STRING);
			//对方开户行
			$datas['B6'] = array('v' => $row->account_bank,
					't' => PHPExcel_Cell_DataType::TYPE_STRING);
			//对方账号
			$datas['E6'] = array('v' => $row->account,
					't' => PHPExcel_Cell_DataType::TYPE_STRING);

			if ($this->type === 'pc') {
				//执行单条目
				$results = $this->db
						->get_results(
								'SELECT a.id,a.pid AS pcid,a.paycostid,a.payment_amount,a.rebate_deduction_amount,person_loan_amount,is_nim_pay_first,nim_pay_first_amount,b.starttime,b.endtime,b.name
FROM finance_payment_person_apply_list a
LEFT JOIN v_last_executive b
ON a.pid=b.pid
WHERE a.apply_id=' . intval($this->apply_id) . ' AND a.isok=1');
			} else if ($this->type === 'pd') {
				//合同条目
				$results = $this->db
						->get_results(
								'SELECT a.id,a.cid AS pcid,a.media_name,a.media_category,a.payment_amount,a.rebate_deduction_amount,a.person_loan_amount,a.is_nim_pay_first,a.nim_pay_first_amount,b.starttime,b.endtime,b.contractname AS name
FROM finance_payment_person_deposit_apply_list a
LEFT JOIN contract_cus b
ON a.cid=b.cid
WHERE a.apply_id=' . intval($this->apply_id) . ' AND a.isok=1');
			}

			if ($results !== NULL) {
				foreach ($results as $key => $result) {
					//从第12行开始

					//投放日期/发生日期
					$datas['A' . (12 + $key)] = array(
							'v' => $result->starttime . '至' . $result->endtime,
							't' => PHPExcel_Cell_DataType::TYPE_STRING);
					//项目名称/用途/附注
					$datas['B' . (12 + $key)] = array('v' => $result->name,
							't' => PHPExcel_Cell_DataType::TYPE_STRING);
					//合同号/执行单号
					$datas['D' . (12 + $key)] = array('v' => $result->pcid,
							't' => PHPExcel_Cell_DataType::TYPE_STRING);
					//发票
					$datas['E' . (12 + $key)] = array('v' => '',
							't' => PHPExcel_Cell_DataType::TYPE_STRING);
					//实付金额
					$datas['F' . (12 + $key)] = array(
							'v' => $result->payment_amount
									- $result->rebate_deduction_amount
									- $result->person_loan_amount,
							't' => PHPExcel_Cell_DataType::TYPE_NUMERIC);
					//其中垫付金额
					$datas['G' . (12 + $key)] = array(
							'v' => (intval($result->is_nim_pay_first) === 1 ? $result
											->nim_pay_first_amount : 0),
							't' => PHPExcel_Cell_DataType::TYPE_NUMERIC);
				}
			}
		}
		return $datas;
	}
}
