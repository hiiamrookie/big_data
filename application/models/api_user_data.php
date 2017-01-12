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
}