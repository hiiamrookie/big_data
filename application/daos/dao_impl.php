<?php
class Dao_Impl {
	var $db;

	public function __construct() {
		$this->db = new ezSQL_mysql(DB_USER, DB_PASSWORD, DB_NAME, DB_HOST);
		$this->db->query('set names utf8');
	}

	public static function validate_id($id) {
		if (Validate_Util::my_is_int($id) && $id > 0) {
			return TRUE;
		}
		return FALSE;
	}

	public static function validate_field_not_empty($field) {
		return empty($field) ? FALSE : TRUE;
	}

	public static function validate_field_not_null($field) {
		return $field !== NULL;
	}

	public static function validate_field_max_length($field, $length) {
		return String_Util::strlen_utf8($field) > $length ? FALSE : TRUE;
	}

	public static function validate_field_min_length($field, $length) {
		return String_Util::strlen_utf8($field) < $length ? FALSE : TRUE;
	}

	public static function validate_date($date) {
		if (!self::validate_field_not_empty($date)
				|| !self::validate_field_not_null($date)) {
			return FALSE;
		}
		return strtotime($date) !== FALSE;
	}

	public static function validate_date_int($date) {
		if (!self::validate_field_not_empty($date)
				|| !self::validate_field_not_null($date)) {
			return FALSE;
		}
		return date('Y-m-d', $date) !== FALSE;
	}

	public static function validate_month_int($date) {
		if (!self::validate_field_not_empty($date)
				|| !self::validate_field_not_null($date)) {
			return FALSE;
		}
		return date('Y-m', $date) !== FALSE;
	}

	public static function validate_money($money ,$equalsZero = TRUE) {
		if (Validate_Util::my_is_float($money) && ($equalsZero && $money >= 0 || !$equalsZero && $money>0) ) {
			if (strpos($money, '.') !== FALSE) {
				$money = explode('.', $money);
				$money = end($money);
				if (strlen($money) > 2) {
					return FALSE;
				}
			}
			return TRUE;
		}
		return FALSE;
	}

	public static function validate_invoice_money($money) {
		if (Validate_Util::my_is_float($money)) {
			if (strpos($money, '.') !== FALSE) {
				$money = explode('.', $money);
				$money = end($money);
				if (strlen($money) > 2) {
					return FALSE;
				}
			}
			return TRUE;
		}
		return FALSE;
	}

	public static function validate_utf8_chinese($field) {
		$p = '/^[\x{4e00}-\x{9fa5}]+$/u';
		return preg_match($p, $field)>0;
	}
	
	public static function validate_utf8_chinese_and_other($field){
		$p = '/^[\x{4e00}-\x{9fa5}A-Za-z0-9_]+$/u';
		return preg_match($p, $field)>0;
	}
}
