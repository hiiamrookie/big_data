<?php
class Server_Util {
	/**
	 * 获得客户端IP地址
	 */
	public static function get_ip() {
		if (getenv ( "HTTP_CLIENT_IP" ) && strcasecmp ( getenv ( "HTTP_CLIENT_IP" ), "0.0.0.0" ))
			$ip = getenv ( "HTTP_CLIENT_IP" );
		elseif (getenv ( "HTTP_X_FORWARDED_FOR" ) && strcasecmp ( getenv ( "HTTP_X_FORWARDED_FOR" ), "0.0.0.0" ))
			$ip = getenv ( "HTTP_X_FORWARDED_FOR" );
		elseif (getenv ( "REMOTE_ADDR" ) && strcasecmp ( getenv ( "REMOTE_ADDR" ), "0.0.0.0" ))
			$ip = getenv ( "REMOTE_ADDR" );
		elseif (isset ( $_SERVER ['REMOTE_ADDR'] ) && $_SERVER ['REMOTE_ADDR'] && strcasecmp ( $_SERVER ['REMOTE_ADDR'], "0.0.0.0" ))
			$ip = $_SERVER ['REMOTE_ADDR'];
		else
			$ip = "0.0.0.0";
		return $ip;
	}
	
	/**
	 * 如无http，则自动加上http
	 * @param string $url URL
	 */
	public static function my_auto_add_http($url) {
		if (! preg_match ( "/^(http|ftp):/", $url )) {
			$url = 'http://' . $url;
		}
		return $url;
	}
	
	/**
	 * 页面跳转
	 * @param string $url 跳转后的页面URL
	 */
	public static function my_server_redirect($url) {
		header ( 'Location:' . $url );
		exit ();
	}
	
	/**
	 * 获得当前页面完整URL
	 */
	public static function my_current_page_url() {
		$pageURL = 'http';
		
		if ($_SERVER ["HTTPS"] == "on") {
			$pageURL .= "s";
		}
		$pageURL .= "://";
		
		if ($_SERVER ["SERVER_PORT"] != "80") {
			$pageURL .= $_SERVER ["SERVER_NAME"] . ":" . $_SERVER ["SERVER_PORT"] . $_SERVER ["REQUEST_URI"];
		} else {
			$pageURL .= $_SERVER ["SERVER_NAME"] . $_SERVER ["REQUEST_URI"];
		}
		return $pageURL;
	}
	
	/**
	 * 获得浏览器类型
	 */
	public static function get_http_user_agent() {
		return $_SERVER ["HTTP_USER_AGENT"];
	}
	
	/**
	 * 获得浏览器语言
	 */
	public static function get_http_accept_language() {
		return $_SERVER ["HTTP_ACCEPT_LANGUAGE"];
	}
	
	/**
	 * 跳转到错误页
	 * @param string $code 错误代码
	 */
	public static function to_error_page($code) {
		self::my_server_redirect ( BASE_URL . $code . '.html' );
	}
}