<?php
class Api_Dsp_Data extends Dao_Impl {
	private $auth_name;
	private $auth_code;
	private $auth_pass = FALSE;
	private $errors = array ();
	public function __construct($auth_name, $auth_code) {
		parent::__construct ();
		$this->auth_name = $auth_name;
		$this->auth_code = $auth_code;
		$this->_auth ();
	}
	private function _auth() {
		$row = $this->db->get_row ( 'SELECT auth_type,is_live FROM api_auth WHERE auth_name="' . $this->auth_name . '" AND auth_code="' . $this->auth_code . '"' );
		if ($row === NULL) {
			$this->errors [] = 'API验证信息不成功，验证用户名或验证代码错误';
		} else {
			$auth_type = intval ( $row->auth_type );
			if ($auth_type !== 0) {
				$this->errors [] = 'API验证信息不成功，非dsp验证信息';
			} else {
				$is_live = intval ( $row->is_live );
				if ($is_live === - 1) {
					$this->errors [] = 'API验证信息不成功，该验证信息已不可使用';
				} else {
					$this->auth_pass = TRUE;
				}
			}
		}
	}
	public function getPostDspDataResult($dsp_data) {
		if ($this->auth_pass) {
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
						
						$md5str = md5 ( $dsp_platform . '|' . $dsp_order . '|' . $dsp_adv . '|' . $dsp_creative . '|' . $dsp_website . '|' . $dsp_industry_1 . '|' . $dsp_industry_2 . '|' . $schedule_date );
						
						$times_datas = ( array ) $data->times_data;
						foreach ( $times_datas as $time => $times_data ) {
							$dsp_cost = $times_data->dsp_cost;
							$dsp_impressions = $times_data->dsp_impressions;
							$dsp_click = $times_data->dsp_click;
							$value_array [] = '("' . $dsp_platform . '","' . $dsp_order . '","' . $dsp_adv . '","' . $dsp_creative . '","' . $dsp_website . '","' . $dsp_industry_1 . '","' . $dsp_industry_2 . '","' . $schedule_date . '","' . $md5str . '",' . $time . ',' . $dsp_cost . ',' . $dsp_impressions . ',' . self::_cpm ( $dsp_cost, $dsp_impressions ) . ',' . $dsp_click . ',' . self::_ctr ( $dsp_click, $dsp_impressions ) . ',' . self::_cpc ( $dsp_click, $dsp_cost ) . ',now())';
						}
					}
					if (! empty ( $value_array )) {
						$result = $this->db->query ( 'INSERT INTO executive_dsp_data(dsp_platform,dsp_order,dsp_adv,dsp_creative,dsp_website,dsp_industry_1,dsp_industry_2,schedule_date,md5str,times,dsp_cost,dsp_impressions,dsp_cpm,dsp_click,dsp_ctr,dsp_cpc,addtime) VALUES' . implode ( ',', $value_array ) );
						return json_encode ( array (
								'status' => $result === FALSE ? 'error' : 'success',
								'message' => $result === FALSE ? '传输数据错误' : '传输数据成功' 
						), JSON_UNESCAPED_UNICODE );
					}
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
				'message' => implode ( '，', $this->errors ) 
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