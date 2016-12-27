<?php
class Validate_Util {
	/**
	 * 判断是否是整数
	 * @param int $value 待检验的值
	 * @return TRUE or FALSE
	 */
	public static function my_is_int($int) {
		if (FALSE === filter_var ( $int, FILTER_VALIDATE_INT )) {
			return FALSE;
		} else {
			return TRUE;
		}
	}
	
	/**
	 * 判断是否是浮点数
	 * @param float $value 待检验的值
	 * @return TRUE or FALSE
	 */
	public static function my_is_float($float) {
		if (FALSE === filter_var ( $float, FILTER_VALIDATE_FLOAT )) {
			return FALSE;
		} else {
			return TRUE;
		}
	}
	
	/**
	 * 判断是否是合法URL
	 * @param string $url 待检验的值
	 * @param int $flag 检验标志
	 * 可选值：
	 * FILTER_FLAG_SCHEME_REQUIRED 要求 URL 是 RFC 兼容 URL
	 * FILTER_FLAG_HOST_REQUIRED 要求 URL 包含主机名
	 * FILTER_FLAG_PATH_REQUIRED 要求 URL 在主机名后存在路径
	 * FILTER_FLAG_QUERY_REQUIRED 要求 URL 存在查询字符串
	 * @return TRUE or FALSE
	 */
	public static function my_is_url($url, $flag = NULL) {
		if (! empty ( $flag ) && ! in_array ( $flag, array (FILTER_FLAG_SCHEME_REQUIRED, FILTER_FLAG_HOST_REQUIRED, FILTER_FLAG_PATH_REQUIRED, FILTER_FLAG_QUERY_REQUIRED ) )) {
			return FALSE;
		}
		if (! filter_var ( $url, FILTER_VALIDATE_URL, $flag )) {
			return FALSE;
		} else {
			return TRUE;
		}
	}
	
	/**
	 * 判断是否是合法EMAIL
	 * @param string $email 待检验的值
	 * @param boolean $check_dns 是否检查域名的DNS记录
	 * @return TRUE or FALSE
	 */
	public static function my_is_email($email, $check_dns) {
		//var_dump(filter_var ( $email, FILTER_VALIDATE_EMAIL ));
		if (! filter_var ( $email, FILTER_VALIDATE_EMAIL )) {
			return FALSE;
		} else {
			if (function_exists ( 'checkdnsrr' ) && $check_dns && ($domain = end ( explode ( '@', $email, 2 ) ))) {
				return checkdnsrr ( $domain . '.', 'MX' );
			}
			return TRUE;
		}
	}
	
	/**
	 * 判断是否是合法IP地址
	 * @param string $ip 待检验的IP地址
	 * @param int $flag 检验标志
	 * 可选值：
	 * FILTER_FLAG_IPV4 要求值是合法的 IPv4 IP
	 * FILTER_FLAG_IPV6 要求值是合法的 IPv6 IP
	 * FILTER_FLAG_NO_PRIV_RANGE 要求值是 RFC 指定的私域 IP
	 * FILTER_FLAG_NO_RES_RANGE 要求值不在保留的 IP 范围内。该标志接受 IPV4 和 IPV6 值。
	 * @return TRUE or FALSE
	 */
	public static function my_is_ip($ip, $flag = NULL) {
		if (! empty ( $flag ) && ! in_array ( $flag, array (FILTER_FLAG_IPV4, FILTER_FLAG_IPV6, FILTER_FLAG_NO_PRIV_RANGE, FILTER_FLAG_NO_RES_RANGE ) )) {
			return FALSE;
		}
		if (! filter_var ( $ip, FILTER_VALIDATE_IP, $flag )) {
			return FALSE;
		} else {
			return TRUE;
		}
	}
	
	/**
	 * 检查用户名是否合法
	 * @param string $username 用户名
	 * @param int $minlength 最小长度
	 * @param int $maxlength 最大长度
	 * @return TRUE or FALSE
	 */
	public static function my_check_username($username, $minlength, $maxlength) {
		if (! String_Util::my_check_strlength ( $username, $minlength, $maxlength )) {
			return FALSE;
		}
		if (preg_match ( "/^[a-zA-Z]{1}[_a-zA-Z0-9]*$/", $username ) === 0) {
			return FALSE;
		}
		return TRUE;
	}
	
	/**
	 * 检查页面来源
	 */
	public static function my_check_referer() {
		if (! isset ( $_SERVER ['HTTP_REFERER'] )) {
			return FALSE;
		}
		$urlar = parse_url ( $_SERVER ['HTTP_REFERER'] );
		
		if ($_SERVER ['HTTP_HOST'] != $urlar ['host']) {
			return FALSE;
		}
		return TRUE;
	}
	
	/**
	 * 检测是否是数值
	 * @param unknown_type $value 待检测值
	 */
	public static function my_is_numeric($value) {
		//if (! ereg ( "^[0-9]+$", $value )) {
		if (preg_match ( "/^[0-9]+$/", $value ) === 0) {
			return FALSE;
		}
		return TRUE;
	}
	
	public static function my_typeof($var){
		if(is_object($var)){
			return get_class($var);
		}else if(is_array($var)){
			return 'array';
		}else if(is_string($var)){
			return 'string';
		}else if(is_numeric($var)){
			return 'numeric';
		}else{
			return '';
		}
	}
	
	public static function my_typelist($args){
		return array_map(array('self','my_typeof'), $args);
	}
}