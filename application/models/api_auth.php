<?php
class Api_Auth extends Dao_Impl {
	private $auth_name;
	private $auth_code;
	private $auth_pass = FALSE;
	private $errors = array ();
	public function __construct($auth_name, $auth_code, $is_dsp = TRUE) {
		parent::__construct ();
		$this->auth_name = $auth_name;
		$this->auth_code = $auth_code;
		$this->_auth ( $is_dsp );
	}
	public function isAuthPass() {
		return $this->auth_pass;
	}
	public function getAuthErrors() {
		return $this->errors;
	}
	public function getAuthName() {
		return $this->auth_name;
	}
	private function _auth($is_dsp) {
		$row = $this->db->get_row ( 'SELECT auth_type,is_live FROM api_auth WHERE auth_name="' . $this->auth_name . '" AND auth_code="' . $this->auth_code . '"' );
		if ($row === NULL) {
			$this->errors [] = 'API验证信息不成功，验证用户名或验证代码错误';
		} else {
			$auth_type = intval ( $row->auth_type );
			if ($auth_type !== 0 && $is_dsp || $auth_type === 0 && ! $is_dsp) {
				$this->errors [] = 'API验证信息不成功，非' . ($is_dsp ? 'dsp' : '用户') . '验证信息';
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
}