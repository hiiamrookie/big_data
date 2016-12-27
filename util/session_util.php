<?php
class Session_Util {
	/**
	 * 获得SESSION值
	 * @param string $key SESSION值的key
	 */
	public static function my_session_get($key) {
		if (isset ( $_SESSION ["$key"] )) {
			return $_SESSION ["$key"];
		}
		return NULL;
	}
	
	/**
	 * 设置SESSION值
	 * @param string $key SESSION值的key
	 * @param unknown_type $value SESSION值
	 */
	public static function my_session_set($key, $value) {
		$_SESSION ["$key"] = $value;
		return TRUE;
	}
	
	/**
	 * SESSION变量注销，PHP5.3中代替取消的session_unregister
	 * @param unknown_type $key
	 */
	public static function my_session_unregister($key) {
		unset ( $_SESSION ["$key"] );
	}
}