<?php
class Format_Util {
	public static function my_money_format($format, $number) {
		if(empty($number)){
			$number = 0;
		}
		if (function_exists ( 'money_format' )) {
			return money_format ( $format, $number );
		}
		//TODO
		return $number;
	}
	
	public static function get_file_real_size($size) {
		$kb = 1024; // Kilobyte
		$mb = 1024 * $kb; // Megabyte
		$gb = 1024 * $mb; // Gigabyte
		$tb = 1024 * $gb; // Terabyte
		

		if ($size < $kb) {
			return $size . " B";
		} else if ($size < $mb) {
			return round ( $size / $kb, 2 ) . " KB";
		} else if ($size < $gb) {
			return round ( $size / $mb, 2 ) . " MB";
		} else if ($size < $tb) {
			return round ( $size / $gb, 2 ) . " GB";
		} else {
			return round ( $size / $tb, 2 ) . " TB";
		}
	}
	
	public static function format_html($content){
		return str_replace(array("\t","\r\n","\r","\n",' '), array('&nbsp;&nbsp;','<br>','','<br>','&nbsp;'), $content);
	}
}