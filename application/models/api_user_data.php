<?php
class Api_User_Data extends Api_Auth {
	public function __construct($auth_name, $auth_code) {
		parent::__construct ( $auth_name, $auth_code, FALSE );
	}
	public function getPostOfflineDataResult($user_data) {
		if ($this->isAuthPass ()) {
			if (! empty ( $user_data )) {
				$user_data = json_decode ( $user_data );
				if ($user_data !== NULL && $user_data !== FALSE) {
					
					$value_array = array ();
					foreach ( $user_data as $ud ) {
						$md5str = $ud->md5str;
						$data_date = $ud->data_date;
						$reg_cnt = $ud->reg_cnt;
						$order_cnt = $ud->order_cnt;
						$order_amount = $ud->order_amount;
						
						$value_array [] = '("' . $md5str . '","' . $data_date . '",' . $reg_cnt . ',' . $order_cnt . ',' . $order_amount . ',now(),"' . $this->getAuthName () . '")';
					}
					
					if (! empty ( $value_array )) {
						$result = $this->db->query ( 'INSERT INTO executive_offline_data(md5str,data_date,reg_cnt,order_cnt,order_amount,addtime,auth_name) VALUES' . implode ( ',', $value_array ) );
						return json_encode ( array (
								'status' => $result === FALSE ? 'error' : 'success',
								'message' => $result === FALSE ? '传输数据错误' : '传输数据成功' 
						), JSON_UNESCAPED_UNICODE );
					}
					return json_encode ( array (
							'status' => 'error',
							'message' => '无数据' 
					), JSON_UNESCAPED_UNICODE );
				}
				return json_encode ( array (
						'status' => 'error',
						'message' => '数据解析有误' 
				), JSON_UNESCAPED_UNICODE );
			}
			return json_encode ( array (
					'status' => 'error',
					'message' => '数据不能为空' 
			), JSON_UNESCAPED_UNICODE );
		}
		return json_encode ( array (
				'status' => 'error',
				'message' => implode ( '，', $this->getAuthErrors () ) 
		), JSON_UNESCAPED_UNICODE );
	}
	public function getPostMediaScheduleResult($user_data) {
		if ($this->isAuthPass ()) {
			if (! empty ( $user_data )) {
				$user_data = json_decode ( $user_data );
				if ($user_data !== NULL && $user_data !== FALSE) {
					
					$value_array = array ();
					$budget_str = array ();
					for($x = 0; $x < 24; $x ++) {
						$budget_str [] = 'budget_' . $x;
					}
					
					foreach ( $user_data as $ud ) {
						$dsp_platform = $ud->dsp_platform;
						$pid = $ud->pid;
						$dsp_order = $ud->dsp_order;
						$dsp_adv = $ud->dsp_adv;
						$dsp_creative = $ud->dsp_creative;
						$dsp_website = $ud->dsp_website;
						$dsp_industry_1 = $ud->dsp_industry_1;
						$dsp_industry_2 = $ud->dsp_industry_2;
						$schedule_date = $ud->schedule_date;
						$budgets = ( array ) $ud->budget;
						
						$budget_values = array(0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0);
						$budget_sum = 0;
						foreach ( $budgets as $times => $budget ) {
							$budget_values [$times] = $budget;
							$budget_sum += $budget;
						}
						
						$url = str_replace ( array (
								'http://',
								'https://' 
						), array (
								'' 
						), strtolower ( $dsp_website ) );
						
						$md5str = md5 ( $dsp_platform . '|' . $dsp_order . '|' . $dsp_adv . '|' . $dsp_creative . '|' . $url . '|' . $dsp_industry_1 . '|' . $dsp_industry_2 . '|' . $schedule_date );
						$value_array [] = '("' . $dsp_platform . '","' . $pid . '","' . $dsp_order . '","' . $dsp_adv . '","' . $dsp_creative . '","' . $url . '","' . $dsp_industry_1 . '","' . $dsp_industry_2 . '","' . $schedule_date . '",' . implode ( ',', $budget_values ) . ',' . $budget_sum . ',now(),0,"' . $md5str . '")';
					}
					
					if (! empty ( $value_array )) {
						$result = $this->db->query ( 'INSERT INTO executive_media_schedule_content(dsp_platform,pid,dsp_order,dsp_adv,dsp_creative,dsp_website,dsp_industry_1,dsp_industry_2,schedule_date,' . implode ( ',', $budget_str ) . ',budget_sum,addtime,adduser,md5str) VALUES' . implode ( ',', $value_array ) );
						return json_encode ( array (
								'status' => $result === FALSE ? 'error' : 'success',
								'message' => $result === FALSE ? $this->db->last_error : '传输数据成功' 
						), JSON_UNESCAPED_UNICODE );
					}
					return json_encode ( array (
							'status' => 'error',
							'message' => '无数据' 
					), JSON_UNESCAPED_UNICODE );
				}
				return json_encode ( array (
						'status' => 'error',
						'message' => '数据解析有误' 
				), JSON_UNESCAPED_UNICODE );
			}
			return json_encode ( array (
					'status' => 'error',
					'message' => '数据不能为空' 
			), JSON_UNESCAPED_UNICODE );
		}
		return json_encode ( array (
				'status' => 'error',
				'message' => implode ( '，', $this->getAuthErrors () ) 
		), JSON_UNESCAPED_UNICODE );
	}
}