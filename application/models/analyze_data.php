<?php
class Analyze_Data extends User {
	private $search;
	private $is_check = FALSE;
	private $is_manager = FALSE;
	private $limit_arr = array();
	private $starttime;
	private $endtime;
	private $range;


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
		//return "15GZ100037-001";
		return "15SH010001-085";
	}

	//获取执行单
	public function get_executive(){
		$pid = $this->getSearch();// 10SHZ08150-002
		$sql = "SELECT * from (SELECT * FROM executive WHERE pid='".$pid."'  ORDER BY isalter DESC LIMIT 1 )a WHERE a.isok=1";
		$results = $this->db->get_row ($sql);
		return $results;
	}

	//应得返点
	
/*	public function deserve_rebate(){
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

			$deserve_rebate_data_json = json_encode($deserve_rebate_data,JSON_UNESCAPED_UNICODE);
			$key_json = json_encode(array_unique($key_arr),JSON_UNESCAPED_UNICODE);
			$data_arr = array('deserve_rebate_data_json'=>$deserve_rebate_data_json,'key_json'=>$key_json,'limit_value'=>$limit_value);
		}else{
			$data_arr = array('deserve_rebate_data_json'=>"''",'key_json'=>"''");
		}
		return $data_arr;
	}*/


/*项目盈利分析*/

	//利润
	public function get_profit_gain_data(){
		$executive = $this->get_executive();
		$profit_gain_data = array();

		$executive_id = $executive->id;
		$cid = $executive->cid;
		$pid = $this->getSearch();
		//获取当月执行金额
		$month_execute_amount_sql = "SELECT quote_amount,ym FROM executive_amount_cy  WHERE pid = '".$pid."' and executive_id='".$executive_id."'";
		$month_execute_amount_result = $this->db->get_results($month_execute_amount_sql);
		$month_execute_amount_arr = array();
		foreach ($month_execute_amount_result as $key => $value) {
			$month_execute_amount_arr[$value->ym] = $value->quote_amount; 	
		}
		//获取发票所属
		$invoice_own_sql= "SELECT billtype from contract_cus WHERE cid = '".$cid."'";
		$invoice_own_result = $this->db->get_row ($invoice_own_sql);
		$invoice_own = $invoice_own_result->billtype;

		$profit_gain_sql = "SELECT a.payname,a.deliverytype,a.cost_amount,a.ym,b.media_short,c.rebate,(a.cost_amount*0.01*c.rebate) as deserve_rebate 
							from executive_cy as a LEFT JOIN finance_supplier_short as b on a.supplier_short_id=b.id LEFT JOIN finance_rebate_rate as 
							c on a.supplier_id=c.supplier_id and a.supplier_short_id = c.supplier_short_id and a.category_id = c.category_id  and 
							a.industry_id=c.industry_id WHERE a.pid = '".$pid."' and a.executive_id='".$executive_id."'";
		$profit_gain_results_1 = $this->db->get_results($profit_gain_sql);
		$profit_gain_results_2 =array();
		foreach ($profit_gain_results_1 as $key => $value) {
			$profit_gain_results_2[$key] = array(
			   'payname'	 	=> $value->payname,
			   'deliverytype'	=> $value->deliverytype,
			   'cost_amount' 	=> $value->cost_amount,
			   'ym'			 	=> $value->ym,
			   'deserve_rebate'	=> $value->deserve_rebate,
			   'media_short' 	=> $value->media_short,
			   'rebate'		 	=> $value->rebate,
			   'quote_amount'	=> 0
			);
		}
/*		var_dump($month_execute_amount_arr);
		die;*/
		foreach ($profit_gain_results_2 as &$value) {
			if(array_key_exists($value['ym'],$month_execute_amount_arr)){
				$value['quote_amount'] = $month_execute_amount_arr[$value['ym']];
				unset($month_execute_amount_arr[$value['ym']]); 
			}
		}

		$profit_gain_key_arr = array();
		$profit_gain_amount_arr = array();
		$profit_gain_results_3 = array();
		foreach ($profit_gain_results_2 as $key => $value) {
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
			$profit_gain_results_3[$value['ym']][$str] = $profit;
			$profit_gain_amount_arr[] = $profit;
			$profit_gain_key_arr[] = $str;
		}

		$profit_gain_results = array();
		foreach ($profit_gain_results_3 as $key=>$value) {
		    $value['year'] = $key;
		    $profit_gain_results[] = $value;
		}
/*
		echo "<pre>";
		print_r($profit_gain_results);
		die;*/
		//利润的极值
/*		var_dump($profit_gain_amount_arr);
		die;*/
		$this->limit_arr = $profit_gain_amount_arr;
		$profit_gain_limit_value = $this->limit_value();

		$profit_gain_json = json_encode($profit_gain_results,JSON_UNESCAPED_UNICODE);
		$profit_gain_key_json = json_encode(array_unique($profit_gain_key_arr),JSON_UNESCAPED_UNICODE);
		$profit_gain_data = array('profit_gain_json'=>$profit_gain_json,'profit_gain_key_json'=>$profit_gain_key_json,'profit_gain_limit_value'=>$profit_gain_limit_value);

		return $profit_gain_data;
	}


    //当月执行成本明细
	public function get_profit_cost_data(){
		$executive = $this->get_executive();
		$profit_cost_data = array();
		$executive_id = $executive->id;
		$pid = $this->getSearch();

		$profit_cost_sql = "SELECT a.*,b.media_short as media_short from executive_cy as a LEFT JOIN finance_supplier_short as b 
							on a.supplier_short_id=b.id WHERE a.pid = '".$pid."' and a.executive_id='".$executive_id."'";
		$profit_cost_results_1 = $this->db->get_results($profit_cost_sql);
		$profit_cost_key_arr = array();
		$profit_cost_amount_arr = array();
		$profit_cost_results_2 = array();
		foreach ($profit_cost_results_1 as $value) {
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
			$profit_cost_results_2[$value->ym][$str] = $cost_amount_value;
			$profit_cost_amount_arr[] = (float)$cost_amount_value;
			$profit_cost_key_arr[] = $str;
		}

		$profit_cost_results = array();
		foreach ($profit_cost_results_2 as $key=>$value) {
		    $value['year'] = $key;
		    $profit_cost_results[] = $value;
		}

		//获取执行金额的极值
		$this->limit_arr = $profit_cost_amount_arr;
		$profit_cost_limit_value = $this->limit_value();

		$profit_cost_json = json_encode($profit_cost_results,JSON_UNESCAPED_UNICODE);
		$profit_cost_key_json = json_encode(array_unique($profit_cost_key_arr),JSON_UNESCAPED_UNICODE);
		$profit_cost_data = array('profit_cost_json'=>$profit_cost_json,'profit_cost_key_json'=>$profit_cost_key_json,'profit_cost_limit_value'=>$profit_cost_limit_value);

		return $profit_cost_data;
   }

   public function get_profit_html() {
   	$profit_gain_data = $this->get_profit_gain_data();
   	$profit_cost_data = $this->get_profit_cost_data();
   	$data =  array_merge($profit_gain_data,$profit_cost_data);
   	foreach ($data as $key => &$value) {
   		if(substr($key,-4,4) == 'json' && empty($value)){
   			$value = "''";
   		}
   	}
/*   	var_dump($data);
   	die;*/
   	$buf = file_get_contents(
   			TEMPLATE_PATH . 'analyze/profit_data.tpl');
   	return str_replace(
   			array(
   				'[LEFT]',
   				'[TOP]', 
   				'[VCODE]',
   				'[SEARCH]',
   				'[BASE_URL]',
   				'[profit_gain_json]',
   				'[profit_gain_key_json]',
   				'[profit_gain_max]',
   				'[profit_gain_min]',
   				'[profit_cost_json]',
   				'[profit_cost_key_json]',
   				'[profit_cost_max]',
   				'[profit_cost_min]'
   			),
   			array(
   				$this->get_left_html(),
   				$this->get_top_html(),
   				$this->get_vcode(),
   				$this->search,
   				BASE_URL,
   				$data['profit_gain_json'],
   				$data['profit_gain_key_json'],
   				$data['profit_gain_limit_value']['max'],
   				$data['profit_gain_limit_value']['min'],
   				$data['profit_cost_json'],
   				$data['profit_cost_key_json'],
   				$data['profit_cost_limit_value']['max'],
   				$data['profit_cost_limit_value']['min']
   			), 
   			$buf);
   }

/*获取极值*/
   private function limit_value(){
   		$limit_arr  = $this->limit_arr;
   		$limit_value = array();
   		$limit_arr =  array_unique($limit_arr);
   		if(!empty($limit_arr)){
   			if(count($limit_arr)==1){
   				if($limit_arr[0] == 0){
   					$limit_value['max'] = $limit_arr[0];
   					$limit_value['min']	= -10;
   				}elseif ($limit_arr[0] > 0) {
   					$limit_value['max'] = $limit_arr[0];
   					$limit_value['min']	= 0;
   				}else{
   					$limit_value['max'] = 0;
   					$limit_value['min']	= $limit_arr[0];
   				}
   			}else{
   				@sort($limit_arr);
   				$limit_value['min'] = $limit_arr[0];
   				$limit_value['max']	= $limit_arr[count($limit_arr)-1];
   			}
   		}else{
   			$limit_value['min'] = 0;
   			$limit_value['max']	= 10;
   		}

   		return $limit_value;
    }

    //投放效果分析
   	public function get_effect_data(){
   		$starttime = $this->starttime;
   		$endtime   = $this->endtime;
   		$pid = $this->getSearch();
   		$format_time = $this->format_time();

   		//预算
   		$budget_data = array();

   		//订单数
   		$order_cnt_data	= array();

   		//注册量 
   		$reg_cnt_data = array();

   		//订单金额
   		$order_amount_data = array();

   		//展现次数
   		$dsp_impressions_data = array();

   		//点击次数
   		$dsp_click_data = array();

   		//cpc
   		$dsp_cpc_data = array();

   		//cpm
   		$dsp_cpm_data = array();

   		//ctr
   		$dsp_ctr_data = array();

		/*print_r($format_time);*/
   		if($format_time['type'] == 'hour'){

   			$this->endtime = $this->starttime;

			$media_sql 	 =	"SELECT
							SUM(budget_0) as time_0,
							SUM(budget_1) as time_1,
							SUM(budget_2) as time_2,
							SUM(budget_3) as time_3,
							SUM(budget_4) as time_4,
							SUM(budget_5) as time_5,
							SUM(budget_6) as time_6,
							SUM(budget_7) as time_7,
							SUM(budget_8) as time_8,
							SUM(budget_9) as time_9,
							SUM(budget_10) as time_10,
							SUM(budget_11) as time_11,
							SUM(budget_12) as time_12,
							SUM(budget_13) as time_13,
							SUM(budget_14) as time_14,
							SUM(budget_15) as time_15,
							SUM(budget_16) as time_16,
							SUM(budget_17) as time_17,
							SUM(budget_18) as time_18,
							SUM(budget_19) as time_19,
							SUM(budget_20) as time_20,
							SUM(budget_21) as time_21,
							SUM(budget_22) as time_22,
							SUM(budget_23) as time_23
						FROM
							oa_test.executive_media_schedule_content
					    WHERE
							schedule_date = '".$format_time['starttime']."' AND pid='".$pid."'";
			$dsp_sql   	 = "SELECT a.times,a.dsp_cost,a.dsp_impressions,a.dsp_cpm,a.dsp_click,a.dsp_ctr,a.dsp_cpc FROM executive_dsp_data AS a LEFT JOIN executive_media_schedule_content AS b ON a.md5str=b.md5str WHERE a.schedule_date='".$format_time['starttime']."' AND b.pid='".$pid."'";
			$offline_sql = "SELECT SUM(a.reg_cnt) AS reg_cnt,SUM(a.order_cnt) AS order_cnt,SUM(a.order_amount) AS order_amount,b.schedule_date FROM executive_offline_data as a LEFT JOIN executive_media_schedule_content as b on a.md5str=b.md5str WHERE b.schedule_date = '".$format_time['starttime']."' AND b.pid='".$pid."' GROUP BY b.schedule_date";
			$media_result 	= $this->db->get_row ($media_sql);
			$dsp_result 	= $this->db->get_results ($dsp_sql);
			$offline_result = $this->db->get_row ($offline_sql);
			/*echo $dsp_sql;*/
			$dsp_result_1 = array();
			foreach ($dsp_result as $key => $value) {
				if(isset($value->times)){
					$keys = 'time_'.$value->times;
					$dsp_result_1[$keys]['dsp_cost'] 		= $value->dsp_cost;
					$dsp_result_1[$keys]['dsp_impressions'] = $value->dsp_impressions;
					$dsp_result_1[$keys]['dsp_click'] 		= $value->dsp_click;
					$dsp_result_1[$keys]['dsp_cpc'] 		= $value->dsp_cpc;
					$dsp_result_1[$keys]['dsp_cpm']			= $value->dsp_cpm;
					$dsp_result_1[$keys]['dsp_ctr'] 		= $value->dsp_ctr;
				}
			}

			for ($i=0; $i <24 ; $i++) { 
				$key = 'time_'.$i;
				$budget_data[$i] = array('year'=>$i,'budget'=>$media_result->$key);
				if(empty($dsp_result_1[$key])){
					$dsp_impressions_data[$i] = array('year'=>$i,'dsp_impressions'=>0);
					$dsp_click_data[$i] 	  = array('year'=>$i,'dsp_click'=>0);
					$dsp_cpc_data[$i] 		  = array('year'=>$i,'dsp_cpc'=>0);
					$dsp_cpm_data[$i]		  = array('year'=>$i,'dsp_cpm'=>0);
					$dsp_ctr_data[$i] 		  = array('year'=>$i,'dsp_ctr'=>0);
				}else{
					$dsp_impressions_data[$i] = array('year'=>$i,'dsp_impressions'=>$dsp_result_1[$key]['dsp_impressions']);
					$dsp_click_data[$i] 	  = array('year'=>$i,'dsp_click'=>$dsp_result_1[$key]['dsp_click']);
					$dsp_cpc_data[$i] 		  = array('year'=>$i,'dsp_cpc'=>$dsp_result_1[$key]['dsp_cpc']);
					$dsp_cpm_data[$i]		  = array('year'=>$i,'dsp_cpm'=>$dsp_result_1[$key]['dsp_cpm']);
					$dsp_ctr_data[$i] 		  = array('year'=>$i,'dsp_ctr'=>$dsp_result_1[$key]['dsp_ctr']*100);
				}

			}

			$order_cnt_data[]	= array('year'=>$offline_result->schedule_date,'order_cnt'=>$offline_result->order_cnt); 
			$reg_cnt_data[] = array('year'=>$offline_result->schedule_date,'reg_cnt'=>$offline_result->reg_cnt);
			$order_amount_data[] = array('year'=>$offline_result->schedule_date,'order_amount'=>$offline_result->order_amount);

   		}elseif($format_time['type'] == 'day'){
   			//echo 22222222222;

   			$media_sql = "SELECT
						   	(SUM(budget_sum)) AS total,
						   	schedule_date
						   FROM
						   	oa_test.executive_media_schedule_content
						   WHERE
						   	date_format(schedule_date, '%Y-%m-%d') >= '".date('Y-m-d',strtotime($format_time['starttime']))."' AND date_format(schedule_date, '%Y-%m-%d') <= '".date('Y-m-d',strtotime($format_time['endtime']))."' AND pid = '".$pid."' 
						   	GROUP BY
						   	schedule_date";

		    $dsp_sql    =  "SELECT
								b.schedule_date,
								SUM(a.dsp_cost) AS dsp_cost,
								SUM(a.dsp_impressions) AS dsp_impressions,
								SUM(a.dsp_click) AS dsp_click,
								(
									SUM(a.dsp_cost) / SUM(a.dsp_click)
								) AS dsp_cpc,
								(
									SUM(a.dsp_cost) / SUM(a.dsp_impressions) * 1000
								) AS dsp_cpm,
								(
									SUM(a.dsp_click) / SUM(a.dsp_impressions)
								) AS dsp_ctr
							FROM
								executive_dsp_data AS a
							LEFT JOIN executive_media_schedule_content AS b ON a.md5str = b.md5str
							WHERE
								date_format(a.schedule_date, '%Y-%m-%d') >= '".date('Y-m-d',strtotime($format_time['starttime']))."' AND  date_format(a.schedule_date, '%Y-%m-%d') <= '".date('Y-m-d',strtotime($format_time['endtime']))."' AND b.pid = '".$pid."' 
							GROUP BY
								a.schedule_date";
			$offline_sql = "SELECT SUM(a.reg_cnt) AS reg_cnt,SUM(a.order_cnt) AS order_cnt,SUM(a.order_amount) AS order_amount,b.schedule_date FROM executive_offline_data as a LEFT JOIN executive_media_schedule_content as b on a.md5str=b.md5str WHERE date_format(schedule_date, '%Y-%m-%d') >= '".date('Y-m-d',strtotime($format_time['starttime']))."' AND  date_format(schedule_date, '%Y-%m-%d') <= '".date('Y-m-d',strtotime($format_time['endtime']))."' AND b.pid = '".$pid."' GROUP BY b.schedule_date";
			$media_result 	= $this->db->get_results ($media_sql);
			$dsp_result 	= $this->db->get_results ($dsp_sql);
			$offline_result = $this->db->get_results ($offline_sql);
			
			foreach ($media_result as $key => $value) {
				$budget_data[$key] = array('year'=>$value->schedule_date,'budget'=>$value->total);
			}

			foreach ($dsp_result as $key => $value) {
				$dsp_impressions_data[$key] = array('year'=>$value->schedule_date,'dsp_impressions'=>$value->dsp_impressions);
				$dsp_click_data[$key] 	  	= array('year'=>$value->schedule_date,'dsp_click'=>$value->dsp_click);
				$dsp_cpc_data[$key] 		= array('year'=>$value->schedule_date,'dsp_cpc'=>$value->dsp_cpc);
				$dsp_cpm_data[$key]		  	= array('year'=>$value->schedule_date,'dsp_cpm'=>$value->dsp_cpm);
				$dsp_ctr_data[$key] 		= array('year'=>$value->schedule_date,'dsp_ctr'=>$value->dsp_ctr*100);
			}

			foreach ($offline_result as $key => $value) {
				$order_cnt_data[$key]	 = array('year'=>$value->schedule_date,'order_cnt'=>$value->order_cnt); 
				$reg_cnt_data[$key] 	 = array('year'=>$value->schedule_date,'reg_cnt'=>$value->reg_cnt);
				$order_amount_data[$key] = array('year'=>$value->schedule_date,'order_amount'=>$value->order_amount);
			}

   		}elseif ($format_time['type'] == 'month') {
			$media_sql = 	"SELECT
								(SUM(budget_sum)) AS total,
								date_format(schedule_date, '%Y-%m') AS months
							FROM
								oa_test.executive_media_schedule_content
							WHERE
								date_format(schedule_date, '%Y-%m') >= '".date('Y-m',strtotime($format_time['starttime']))."' AND date_format(schedule_date, '%Y-%m') <= '".date('Y-m',strtotime($format_time['endtime']))."' AND pid = '".$pid."' 
							GROUP BY 
								months";

	   	    $dsp_sql    =  "SELECT
						   	    date_format(b.schedule_date, '%Y-%m') AS months,
						   	    SUM(a.dsp_cost) AS dsp_cost,
						   	    SUM(a.dsp_impressions) AS dsp_impressions,
						   	    SUM(a.dsp_click) AS dsp_click,
						   	    (
						   	    	SUM(a.dsp_cost) / SUM(a.dsp_click)
						   	    ) AS dsp_cpc,
						   	    (
						   	    	SUM(a.dsp_cost) / SUM(a.dsp_impressions) * 1000
						   	    ) AS dsp_cpm,
						   	    (
						   	    	SUM(a.dsp_click) / SUM(a.dsp_impressions)
						   	    ) AS dsp_ctr
	   						FROM
	   							executive_dsp_data AS a
	   						LEFT JOIN executive_media_schedule_content AS b ON a.md5str = b.md5str
	   						WHERE
	   							date_format(a.schedule_date, '%Y-%m') >= '".date('Y-m',strtotime($format_time['starttime']))."' AND date_format(a.schedule_date, '%Y-%m') <= '".date('Y-m',strtotime($format_time['endtime']))."' AND b.pid = '".$pid."' 
	   						GROUP BY
	   							months";
	   		$offline_sql = "SELECT date_format(b.schedule_date, '%Y-%m') AS months,SUM(a.reg_cnt) AS reg_cnt,SUM(a.order_cnt) AS 
	   		order_cnt,SUM(a.order_amount) AS order_amount,b.schedule_date FROM executive_offline_data as a LEFT JOIN executive_media_schedule_content as b on a.md5str=b.md5str WHERE date_format(schedule_date, '%Y-%m') >= '".date('Y-m',strtotime($format_time['starttime']))."' AND  date_format(schedule_date, '%Y-%m') <= '".date('Y-m',strtotime($format_time['endtime']))."' AND b.pid = '".$pid."' GROUP BY months";
	   		
	   		$media_result 	= $this->db->get_results ($media_sql);
	   		$dsp_result 	= $this->db->get_results ($dsp_sql);
	   		$offline_result = $this->db->get_results ($offline_sql);
	   		/*echo $media_sql;*/
	   		foreach ($media_result as $key => $value) {
	   			$budget_data[$key] = array('year'=>$value->months,'budget'=>$value->total);
	   		}

	   		foreach ($dsp_result as $key => $value) {
	   			$dsp_impressions_data[$key] = array('year'=>$value->months,'dsp_impressions'=>$value->dsp_impressions);
	   			$dsp_click_data[$key] 	  	= array('year'=>$value->months,'dsp_click'=>$value->dsp_click);
	   			$dsp_cpc_data[$key] 		= array('year'=>$value->months,'dsp_cpc'=>$value->dsp_cpc);
	   			$dsp_cpm_data[$key]		  	= array('year'=>$value->months,'dsp_cpm'=>$value->dsp_cpm);
	   			$dsp_ctr_data[$key] 		= array('year'=>$value->months,'dsp_ctr'=>$value->dsp_ctr*100);
	   		}

	   		foreach ($offline_result as $key => $value) {
	   			$order_cnt_data[$key]	 = array('year'=>$value->months,'order_cnt'=>$value->order_cnt); 
	   			$reg_cnt_data[$key] 	 = array('year'=>$value->months,'reg_cnt'=>$value->reg_cnt);
	   			$order_amount_data[$key] = array('year'=>$value->months,'order_amount'=>$value->order_amount);
	   		}

   		}else{

			$media_sql =  "SELECT
								(SUM(budget_sum)) AS total,
								YEAR (schedule_date) AS years
							FROM
								oa_test.executive_media_schedule_content
							WHERE
								YEAR (schedule_date) >= ".date('Y',strtotime($format_time['starttime']))."
								AND YEAR (schedule_date) <= ".date('Y',strtotime($format_time['endtime']))."
							GROUP BY
								years";

			$dsp_sql =  "SELECT
							YEAR (b.schedule_date) AS years,
							SUM(a.dsp_cost) AS dsp_cost,
							SUM(a.dsp_impressions) AS dsp_impressions,
							SUM(a.dsp_click) AS dsp_click,
							(
								SUM(a.dsp_cost) / SUM(a.dsp_click)
							) AS dsp_cpc,
							(
								SUM(a.dsp_cost) / SUM(a.dsp_impressions) * 1000
							) AS dsp_cpm,
							(
								SUM(a.dsp_click) / SUM(a.dsp_impressions)
							) AS dsp_ctr
						FROM
							executive_dsp_data AS a
						LEFT JOIN executive_media_schedule_content AS b ON a.md5str = b.md5str
						WHERE
							YEAR (b.schedule_date) >= ".date('Y',strtotime($format_time['starttime']))."
							AND YEAR (b.schedule_date) <= ".date('Y',strtotime($format_time['endtime']))."
						GROUP BY
							years";
			$offline_sql = "SELECT YEAR (b.schedule_date) AS years,SUM(a.reg_cnt) AS reg_cnt,SUM(a.order_cnt) AS order_cnt,SUM(a.order_amount) AS order_amount,b.schedule_date FROM executive_offline_data as a LEFT JOIN executive_media_schedule_content as b on a.md5str=b.md5str WHERE 	YEAR (b.schedule_date) >= ".date('Y',strtotime($format_time['starttime']))." AND YEAR (b.schedule_date) <= ".date('Y',strtotime($format_time['endtime']))." GROUP BY years";
	   		$media_result 	= $this->db->get_results ($media_sql);
	   		$dsp_result 	= $this->db->get_results ($dsp_sql);
	   		$offline_result = $this->db->get_results ($offline_sql);

	   		foreach ($media_result as $key => $value) {
	   			$budget_data[$key] = array('year'=>$value->years,'budget'=>$value->total);
	   		}

	   		foreach ($dsp_result as $key => $value) {
	   			$dsp_impressions_data[$key] = array('year'=>$value->years,'dsp_impressions'=>$value->dsp_impressions);
	   			$dsp_click_data[$key] 	  	= array('year'=>$value->years,'dsp_click'=>$value->dsp_click);
	   			$dsp_cpc_data[$key] 		= array('year'=>$value->years,'dsp_cpc'=>$value->dsp_cpc);
	   			$dsp_cpm_data[$key]		  	= array('year'=>$value->years,'dsp_cpm'=>$value->dsp_cpm);
	   			$dsp_ctr_data[$key] 		= array('year'=>$value->years,'dsp_ctr'=>$value->dsp_ctr*100);
	   		}

	   		foreach ($offline_result as $key => $value) {
	   			$order_cnt_data[$key]	 = array('year'=>$value->years,'order_cnt'=>$value->order_cnt); 
	   			$reg_cnt_data[$key] 	 = array('year'=>$value->years,'reg_cnt'=>$value->reg_cnt);
	   			$order_amount_data[$key] = array('year'=>$value->years,'order_amount'=>$value->order_amount);
	   		}
   		}

/*		print_r($order_cnt_data);
		print_r($reg_cnt_data);
		print_r($order_amount_data);
		print_r($budget_data);

		print_r($dsp_impressions_data);
		print_r($dsp_cpc_data);
		print_r($dsp_cpm_data);
		print_r($dsp_ctr_data);*/
		$get_effect_data = array(
			'order_cnt_data_json'	   =>json_encode($order_cnt_data),
			'reg_cnt_data_json'	 	   =>json_encode($reg_cnt_data),
			'order_amount_data_json'   =>json_encode($order_amount_data),
			'budget_data_json'		   =>json_encode($budget_data),
			'dsp_impressions_data_json'=>json_encode($dsp_impressions_data),
			'dsp_cpc_data_json'		   =>json_encode($dsp_cpc_data),
			'dsp_cpm_data_json'		   =>json_encode($dsp_cpm_data),
			'dsp_ctr_data_json'		   =>json_encode($dsp_ctr_data)
		);
		return $get_effect_data;
    }

      public function get_effect_html(){

      	$effect_data = $this->get_effect_data();
/*      	echo "<pre>";
      	var_dump($this->range);
      	die;
*/
      	$buf = file_get_contents(
      			TEMPLATE_PATH . 'analyze/effect_data.tpl');
      	return str_replace(
      			array(
      				'[LEFT]',
      				'[TOP]', 
      				'[VCODE]',
      				'[SEARCH]',
      				'[RANGE]',
      				'[BASE_URL]',
      				'[STARTTIME]',
      				'[ENDTIME]',
      				'[order_cnt_data_json]',
      				'[reg_cnt_data_json]',
      				'[order_amount_data_json]',
      				'[budget_data_json]',
      				'[dsp_impressions_data_json]',
      				'[dsp_cpc_data_json]',
      				'[dsp_cpm_data_json]',
      				'[dsp_ctr_data_json]'
      			),
      			array(
      				$this->get_left_html(),
      				$this->get_top_html(),
      				$this->get_vcode(),
      				$this->search,
      				$this->range,
      				BASE_URL,
      				$this->starttime,
      				$this->endtime,
      				$effect_data['order_cnt_data_json'],
      				$effect_data['reg_cnt_data_json'],
      				$effect_data['order_amount_data_json'],
      				$effect_data['budget_data_json'],
      				$effect_data['dsp_impressions_data_json'],
      				$effect_data['dsp_cpc_data_json'],
      				$effect_data['dsp_cpm_data_json'],
      				$effect_data['dsp_ctr_data_json']
      			), 
      			$buf);
      }

    public function format_time(){
     	$starttime = $this->starttime;
     	$endtime = $this->endtime;
     	$data = array();
     	$range = $this->range;
     	if(empty($range)){
	    	if (empty($starttime) || empty ($endtime)) {

	    		if(!empty($starttime) && empty ($endtime)){
	    			$data['starttime'] = $starttime;
	    			$data['endtime'] = $starttime;
	    		}elseif(empty($starttime) && !empty($endtime)){
	    			$data['starttime'] = $endtime;
	    			$data['endtime'] = $endtime;
	    		}else{
	    			$data['starttime'] = date('Y-m-d');
	    			$data['endtime'] = date('Y-m-d');
	    		}
	    		$data['x_time'] = range(0,23);
	    		$data['type'] = 'hour';
	    	}else{
	    		/*echo 111111;*/
	    		if(strtotime($starttime)>strtotime($endtime)){
	    			$time = $endtime;
	    			$endtime = $starttime;
	    			$starttime = $time;
	    		}
	    		$data['starttime'] = $starttime;
	    		$data['endtime'] = $endtime;
	    		if($starttime == $endtime){
	    			$data['x_time'] = range(0,23);
	    			$data['type'] = 'hour';
	    		}else{
	    			 if(date('Y-m',strtotime($starttime)) == date('Y-m',strtotime($endtime))){
	    			 	$data['x_time'] = range(1,31);
	    				$data['type'] = 'day';
	    			 }else{
	    			 	if(date('Y',strtotime($starttime)) == date('Y',strtotime($endtime))){
				 		 	$data['x_time'] = range(1,12);
				 			$data['type'] = 'month';
	    			 	}else{
	    			 		for ($i= date('Y',strtotime($starttime)); $i <= date('Y',strtotime($endtime)); $i++) { 
	    			 			$data['x_time'][] = $i;
	    			 		}
	    			 		$data['type'] = 'year';
	    			 	}
	    			 }
	    		}
	    	}
     	}else{
     	    $data['type'] = $range;
	    	if (empty($starttime) || empty ($endtime)) {

	    		if(!empty($starttime) && empty ($endtime)){
	    			$data['starttime'] = $starttime;
	    			$data['endtime'] = $starttime;
	    		}elseif(empty($starttime) && !empty($endtime)){
	    			$data['starttime'] = $endtime;
	    			$data['endtime'] = $endtime;
	    		}else{
	    			$data['starttime'] = date('Y-m-d');
	    			$data['endtime'] = date('Y-m-d');
	    		}
	    		$data['x_time'] = range(0,23);
	    	}else{
	    		/*echo 111111;*/
	    		if(strtotime($starttime)>strtotime($endtime)){
	    			$time = $endtime;
	    			$endtime = $starttime;
	    			$starttime = $time;
	    		}
	    		$data['starttime'] = $starttime;
	    		$data['endtime'] = $endtime;
	    		if($starttime == $endtime){
	    			$data['x_time'] = range(0,23);

	    		}else{
	    			 if(date('Y-m',strtotime($starttime)) == date('Y-m',strtotime($endtime))){
	    			 	$data['x_time'] = range(1,31);
	    			 }else{
	    			 	if(date('Y',strtotime($starttime)) == date('Y',strtotime($endtime))){
				 		 	$data['x_time'] = range(1,12);
	    			 	}else{
	    			 		for ($i= date('Y',strtotime($starttime)); $i <= date('Y',strtotime($endtime)); $i++) { 
	    			 			$data['x_time'][] = $i;
	    			 		}
	    			 	}
	    			 }
	    		}
	    	}
     	}

     	return $data;
    }

}	

