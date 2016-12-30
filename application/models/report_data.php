<?php
class Report_Data extends User {
	private $page;
	private $starttime;
	private $endtime;
	private $search;

	private $city;
	private $dep;
	private $team;

	private $gd;

	private $is_check = FALSE;
	private $is_manager = FALSE;
	private $all_count;
	private $page_count;
	private $executives = array();
	private $process_array = array();

	public function __construct($fields, $is_check = FALSE, $is_manager = FALSE) {
		parent::__construct();
		if (!empty($fields)) {
			foreach ($this as $key => $value) {
				if ($fields[$key] !== NULL
						&& !in_array($key, array('is_check', 'is_manager'),
								TRUE)) {
					$this->$key = $fields[$key];
				}
			}
		}
	}

	public function getSearch() {
		return $this->search;
/*		return "15GZ100037-001";*/
	}

	//获取执行单
	public function get_executive(){
		$pid = $this->getSearch();// 10SHZ08150-002
		$sql = "SELECT * from (SELECT * FROM executive WHERE pid='".$pid."'  ORDER BY isalter DESC LIMIT 1 )a WHERE a.isok=1";
		$results = $this->db->get_row ($sql);
		return $results;
	}

	//当月执行成本明细
	public function month_execute_cost_details(){
		$executive = $this->get_executive();
		$data_arr = array();
		if($executive){
			$executive_id = $executive->id;
			$pid = $this->getSearch();
			$sql = "SELECT a.*,b.media_short as media_short from executive_cy as a LEFT JOIN finance_supplier_short as b on a.supplier_short_id=b.id WHERE a.pid = '".$pid."' and a.executive_id='".$executive_id."'";
/*			echo $sql;
			die;*/
			$results = $this->db->get_results($sql);
			$key_arr = array();
			$amount_arr = array();
			$month_execute_amount = array();
			foreach ($results as $value) {
				$str = $value->payname;
				if($value->deliverytype){
				    $str .= "->".$value->deliverytype;
				}
				if($value->media_short){
				     $str .= "->".$value->media_short;
				}
				$cost_amount_value = $value->cost_amount;
				if(empty($cost_amount_value)){
					$cost_amount_value = 0 ;
				}
				$month_execute_amount[$value->ym][$str] = $cost_amount_value;
				$amount_arr[] = (float)$cost_amount_value;
				$key_arr[] = $str;
			}
			$month_execute_amount_data = array();
			foreach ($month_execute_amount as $key=>$value) {
			    $value['year'] = $key;
			    $month_execute_amount_data[] = $value;
			}

			//获取执行金额的极值
			$limit_value = array();
			$amount_arr =  array_unique($amount_arr);
			if(count($amount_arr)==1){
				if($amount_arr[0] == 0){
					$limit_value['max'] = $amount_arr[0];
					$limit_value['min']	= -10;
				}elseif ($amount_arr[0] > 0) {
					$limit_value['max'] = $amount_arr[0];
					$limit_value['min']	= 0;
				}else{
					$limit_value['max'] = 0;
					$limit_value['min']	= $amount_arr[0];
				}
			}else{
				@sort($amount_arr);
				$limit_value['min'] = $amount_arr[0];
				$limit_value['max']	= $amount_arr[count($amount_arr)-1];
			}
/*			var_dump($limit_value);
			die;*/
			$month_execute_amount_data_json = json_encode($month_execute_amount_data,JSON_UNESCAPED_UNICODE);
			$key_json = json_encode(array_unique($key_arr),JSON_UNESCAPED_UNICODE);
			$data_arr = array('month_execute_amount_data_json'=>$month_execute_amount_data_json,'key_json'=>$key_json,'limit_value'=>$limit_value);
		}else{
			$data_arr = array('month_execute_amount_data_json'=>"''",'key_json'=>"''");
		}
		return $data_arr;

	}
	public function get_report_data_html() {
		$data_arr = $this->month_execute_cost_details();
		$json = $data_arr['month_execute_amount_data_json'];
		$key_json = $data_arr['key_json'];
		$max  = $data_arr['limit_value']['max'];
		$min  = $data_arr['limit_value']['min'];
		if(empty($max)){
			$max = 10;
		}
		if(empty($min)){
			$min = 0;
		}
		if(empty($json)){
			$json = "''";
		}
		if (empty($key_json)) {
			$key_json = "''";
		}
		$buf = file_get_contents(
				TEMPLATE_PATH . 'report/report_data.tpl');
		return str_replace(
				array('[LEFT]', '[TOP]', '[VCODE]',
					'[SEARCH]', '[BASE_URL]','[month_execute_amount_data_json]','[key_json]','[min]','[max]'),
				array($this->get_left_html(), $this->get_top_html(),
						$this->get_vcode(),
						 $this->search, BASE_URL,$json,$key_json,$min,$max), 
				$buf);
	}

	//应得返点
	
	public function deserve_rebate(){
		$executive = $this->get_executive();
		$data_arr = array();
		if($executive){
			$executive_id = $executive->id;
			$pid = $this->getSearch();
			$sql = "SELECT a.payname,a.deliverytype,a.cost_amount,a.ym,b.media_short,c.rebate,(a.cost_amount*0.01*c.rebate) as deserve_rebate from 
			executive_cy as a LEFT JOIN finance_supplier_short as b on a.supplier_short_id=b.id LEFT JOIN 
			finance_rebate_rate as c on a.supplier_id=c.supplier_id and a.supplier_short_id = c.supplier_short_id and a.category_id = c.category_id and a.industry_id=c.industry_id WHERE a.pid = '".$pid."' 
			and a.executive_id='".$executive_id."'";
			$results = $this->db->get_results($sql);
/*			var_dump($results);
			die;*/
			$key_arr = array();
			$amount_arr = array();
			$deserve_rebate = array();
			foreach ($results as $value) {
				$str = $value->payname;
				if($value->deliverytype){
				    $str .= "->".$value->deliverytype;
				}
				if($value->media_short){
				     $str .= "->".$value->media_short;
				}
				$deserve_rebate_value = $value->deserve_rebate;
				if(empty($deserve_rebate_value)){
					$deserve_rebate_value = 0 ;
				}
				$deserve_rebate[$value->ym][$str] = $deserve_rebate_value;
				$amount_arr[] = (float)$deserve_rebate_value;
				$key_arr[] = $str;
			}
			$deserve_rebate_data = array();
			foreach ($deserve_rebate as $key=>$value) {
			    $value['year'] = $key;
			    $deserve_rebate_data[] = $value;
			}
/*			echo "<pre>";
			print_r($amount_arr);
			print_r($deserve_rebate_data);
			die;*/
			//获取执行金额的极值
			$limit_value = array();
			$amount_arr =  array_unique($amount_arr);
			if(count($amount_arr)==1){
				if($amount_arr[0] == 0){
					$limit_value['max'] = $amount_arr[0];
					$limit_value['min']	= -10;
				}elseif ($amount_arr[0] > 0) {
					$limit_value['max'] = $amount_arr[0];
					$limit_value['min']	= 0;
				}else{
					$limit_value['max'] = 0;
					$limit_value['min']	= $amount_arr[0];
				}
			}else{
				@sort($amount_arr);
				$limit_value['min'] = $amount_arr[0];
				$limit_value['max']	= $amount_arr[count($amount_arr)-1];
			}
/*			print_r($limit_value);
			die;*/
			$deserve_rebate_data_json = json_encode($deserve_rebate_data,JSON_UNESCAPED_UNICODE);
			$key_json = json_encode(array_unique($key_arr),JSON_UNESCAPED_UNICODE);
			$data_arr = array('deserve_rebate_data_json'=>$deserve_rebate_data_json,'key_json'=>$key_json,'limit_value'=>$limit_value);
		}else{
			$data_arr = array('deserve_rebate_data_json'=>"''",'key_json'=>"''");
		}
		return $data_arr;
	}

	public function get_deserve_rebate_html() {
		$data_arr = $this->deserve_rebate();
		$json = $data_arr['deserve_rebate_data_json'];
		$key_json = $data_arr['key_json'];
		$max  = $data_arr['limit_value']['max'];
		$min  = $data_arr['limit_value']['min'];
		if(empty($max)){
			$max = 10;
		}
		if(empty($min)){
			$min = 0;
		}
		if(empty($json)){
			$json = "''";
		}
		if (empty($key_json)) {
			$key_json = "''";
		}
		$buf = file_get_contents(
				TEMPLATE_PATH . 'report/deserve_rebate.tpl');
		return str_replace(
				array('[LEFT]', '[TOP]', '[VCODE]',
					'[SEARCH]', '[BASE_URL]','[deserve_rebate_data_json]','[key_json]','[min]','[max]'),
				array($this->get_left_html(), $this->get_top_html(),
						$this->get_vcode(),
						 $this->search, BASE_URL,$json,$key_json,$min,$max), 
				$buf);
	}


	//利润
	public function profit_append(){
		$executive = $this->get_executive();
		$data_arr = array();
		if($executive){
			$executive_id = $executive->id;
			$cid = $executive->cid;
			$pid = $this->getSearch();
			//获取当月执行金额
			$sql_amount= "SELECT quote_amount,ym FROM executive_amount_cy  WHERE pid = '".$pid."' and executive_id='".$executive_id."'";
			$quote_amount_result = $this->db->get_results($sql_amount);
			$quote_amount_arr = array();
			foreach ($quote_amount_result as $key => $value) {
				$quote_amount_arr[$value->ym] = $value->quote_amount; 	
			}
			//获取发票所属
			$sql_invoice_own= "SELECT billtype from contract_cus WHERE cid = '".$cid."'";
			$invoice_own_result = $this->db->get_row ($sql_invoice_own);
			$invoice_own = $invoice_own_result->billtype;

			$sql = "SELECT a.payname,a.deliverytype,a.cost_amount,a.ym,b.media_short,c.rebate,(a.cost_amount*0.01*c.rebate) as deserve_rebate from 
					executive_cy as a LEFT JOIN finance_supplier_short as b on a.supplier_short_id=b.id LEFT JOIN 
					finance_rebate_rate as c on a.supplier_id=c.supplier_id and a.supplier_short_id = c.supplier_short_id and a.category_id = c.category_id  and a.industry_id=c.industry_id
					WHERE a.pid = '".$pid."' and a.executive_id='".$executive_id."'";
			$results = $this->db->get_results($sql);

			foreach ($results as $key => $value) {
				$results_arr[$key] = array('payname'	 	=> $value->payname,
										   'deliverytype'	=> $value->deliverytype,
										   'cost_amount' 	=> $value->cost_amount,
										   'ym'			 	=> $value->ym,
										   'deserve_rebate'	=> $value->deserve_rebate,
										   'media_short' 	=> $value->media_short,
										   'rebate'		 	=> $value->rebate,
										   'quote_amount'	=> 0
									 );
			}
			foreach ($results_arr as &$value) {
				if(array_key_exists($value['ym'],$quote_amount_arr)){
					$value['quote_amount'] = $quote_amount_arr[$value['ym']];
					unset($quote_amount_arr[$value['ym']]); 
				}
			}

			$key_arr = array();
			$amount_arr = array();
			$profit_append = array();
			foreach ($results_arr as $key => $value) {
				$str = $value['payname'];
				if($value['deliverytype']){
				    $str .= "->".$value['deliverytype'];
				}
				if($value['media_short']){
				     $str .= "->".$value['media_short'];
				}

				//营业税及附加
				$business_tax = 0;
				if($invoice_own == 2){
					$business_tax = round(($value['quote_amount'] - $value['cost_amount'] + $value['deserve_rebate'])*0.0678,2);
				}else{
					$business_tax = round(($value['quote_amount'] - $value['cost_amount'] + $value['deserve_rebate'])*0.0996,2);
				}

				//利润
				$profit = $value['quote_amount'] - $value['cost_amount'] - $business_tax + $value['deserve_rebate'];
				$profit_append[$value['ym']][$str] = $profit;
				$amount_arr[] = $profit;
				$key_arr[] = $str;
			}
/*			echo "<pre>";
			print_r($results_arr);
			die;*/
			$profit_append_data = array();
			foreach ($profit_append as $key=>$value) {
			    $value['year'] = $key;
			    $profit_append_data[] = $value;
			}

			/*print_r($amount_arr);*/
			//利润的极值
			$limit_value = array();
			$amount_arr =  array_unique($amount_arr);
			if(count($amount_arr)==1){
				if($amount_arr[0] == 0){
					$limit_value['max'] = $amount_arr[0];
					$limit_value['min']	= -10;
				}elseif ($amount_arr[0] > 0) {
					$limit_value['max'] = $amount_arr[0];
					$limit_value['min']	= 0;
				}else{
					$limit_value['max'] = 0;
					$limit_value['min']	= $amount_arr[0];
				}
			}else{
				@sort($amount_arr);
				$limit_value['min'] = $amount_arr[0];
				$limit_value['max']	= $amount_arr[count($amount_arr)-1];
			}
/*			print_r($limit_value);
			die;*/
			$profit_append_data_json = json_encode($profit_append_data,JSON_UNESCAPED_UNICODE);
			$key_json = json_encode(array_unique($key_arr),JSON_UNESCAPED_UNICODE);
			$data_arr = array('profit_append_data_json'=>$profit_append_data_json,'key_json'=>$key_json,'limit_value'=>$limit_value);
		}else{
			$data_arr = array('profit_append_data_json'=>"''",'key_json'=>"''");
		}
		return $data_arr;
	}

	public function get_profit_append_html() {
		$data_arr = $this->profit_append();
		$json = $data_arr['profit_append_data_json'];
		$key_json = $data_arr['key_json'];
		$max  = $data_arr['limit_value']['max'];
		$min  = $data_arr['limit_value']['min'];
		if(empty($max)){
			$max = 10;
		}
		if(empty($min)){
			$min = 0;
		}
		if(empty($json)){
			$json = "''";
		}
		if (empty($key_json)) {
			$key_json = "''";
		}
		$buf = file_get_contents(
				TEMPLATE_PATH . 'report/profit_append.tpl');
		return str_replace(
				array('[LEFT]', '[TOP]', '[VCODE]',
					'[SEARCH]', '[BASE_URL]','[profit_append_data_json]','[key_json]','[min]','[max]'),
				array($this->get_left_html(), $this->get_top_html(),
						$this->get_vcode(),
						 $this->search, BASE_URL,$json,$key_json,$min,$max), 
				$buf);
	}

}	

