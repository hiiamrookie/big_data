<?php
class Array_Util {
	/**
	 * 去除$source数组中属于$other数组中的值
	 * @param array $source 需要处理的数组
	 * @param array $other 需要去除的值的数组
	 */
	public static function my_remove_array_other_value($source, $other) {
		return array_diff ( $source, $other );
	}
	
	/**
	 * 去除数组中的空值
	 * @param array $source 需要处理的数组
	 */
	public static function my_remove_array_null_value($source) {
		return self::my_remove_array_other_value ( $source, array (NULL ) );
	}
}