<?php
class Api_Dsp_Data extends Api_Auth {
	public function __construct($auth_name, $auth_code) {
		parent::__construct ( $auth_name, $auth_code );
	}
	public function getPostDspDataResult($dsp_data) {
		if ($this->isAuthPass ()) {
			if (! empty ( $dsp_data )) {
				$dsp_data = json_decode ( $dsp_data );
				if ($dsp_data !== NULL && $dsp_data !== FALSE) {
					$dsp_platform = $dsp_data->dsp_platform;
					$datas = $dsp_data->data; // array
					
					$value_array = array ();
					foreach ( $datas as $data ) {
						$dsp_order = $data->dsp_order;
						$dsp_adv = $data->dsp_adv;
						$dsp_creative = $data->dsp_creative;
						$dsp_website = $data->dsp_website;
						$dsp_industry_1 = $data->dsp_industry_1;
						$dsp_industry_2 = $data->dsp_industry_2;
						$schedule_date = $data->schedule_date;
						$landing_page = str_replace ( array (
								'http://',
								'https://' 
						), array (
								'',
								'' 
						), strtolower ( $data->landing_page ) );
						
						$md5str = md5 ( $dsp_platform . '|' . $dsp_order . '|' . $dsp_adv . '|' . $dsp_creative . '|' . $dsp_website . '|' . $dsp_industry_1 . '|' . $dsp_industry_2 . '|' . $schedule_date );
						
						$times_datas = ( array ) $data->times_data;
						foreach ( $times_datas as $time => $times_data ) {
							$dsp_cost = $times_data->dsp_cost;
							$dsp_impressions = $times_data->dsp_impressions;
							$dsp_click = $times_data->dsp_click;
							$value_array [] = '("' . $dsp_platform . '","' . $dsp_order . '","' . $dsp_adv . '","' . $dsp_creative . '","' . $dsp_website . '","' . $dsp_industry_1 . '","' . $dsp_industry_2 . '","' . $schedule_date . '","' . $md5str . '",' . $time . ',' . $dsp_cost . ',' . $dsp_impressions . ',' . self::_cpm ( $dsp_cost, $dsp_impressions ) . ',' . $dsp_click . ',' . self::_ctr ( $dsp_click, $dsp_impressions ) . ',' . self::_cpc ( $dsp_click, $dsp_cost ) . ',now(),"' . $landing_page . '")';
						}
					}
					if (! empty ( $value_array )) {
						$result = $this->db->query ( 'INSERT INTO executive_dsp_data(dsp_platform,dsp_order,dsp_adv,dsp_creative,dsp_website,dsp_industry_1,dsp_industry_2,schedule_date,md5str,times,dsp_cost,dsp_impressions,dsp_cpm,dsp_click,dsp_ctr,dsp_cpc,addtime,landing_page) VALUES' . implode ( ',', $value_array ) );
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
	private static function _cpm($cost, $impressions) {
		return $impressions == 0 ? 0 : ($cost / $impressions) * 1000;
	}
	private static function _ctr($click, $impressions) {
		return $impressions == 0 ? 0 : $click / $impressions;
	}
	private static function _cpc($click, $cost) {
		return $click == 0 ? 0 : $cost / $click;
	}
}