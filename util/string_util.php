<?php
class String_Util {
	//不使用o/O/0，以免混淆
	private static $tchars = 'ABCDEFGHIJKLMNPQRSTUVWXYZ123456789abcdefghijklmnpqrstuvwxyz123456789';
	/**
	 * 多次MD5编码字符串
	 * @param string $source 原字符串
	 * @param int $md5_times 编码次数
	 * @return 如果参数不合法返回FALSE，否则返回多次编码后的字符串
	 */
	public static function my_md5($source, $md5_times = 5) {
		if (! is_int ( $md5_times ) || $md5_times < 0 || ! is_string ( $source )) {
			return FALSE;
		}
		if ($md5_times > 0) {
			$source = md5 ( $source );
			$md5_times --;
			return self::my_md5 ( $source, $md5_times );
		}
		return $source;
	}
	
	/**
	 * 随机生成一定长度的字符串
	 * @param int $length 字符串长度
	 * @return 如果参数不合法返回FALSE，否则返回$length长度的随机字符串
	 */
	public static function get_random_str($length = 4) {
		if (! is_int ( $length ) || $length <= 0) {
			return FALSE;
		}
		$random_str = '';
		
		$tmax = strlen ( self::$tchars ) - 1;
		for($i = 0; $i < $length; $i ++) {
			$random_str .= self::$tchars [mt_rand ( 0, $tmax )];
		}
		return $random_str;
	}
	
	/**
	 * 判断字符串$str是否由$search作为开头
	 * @param string $str 原字符串
	 * @param string $search 待搜索的字符串 
	 * @return TRUE or FALSE
	 */
	public static function start_with($str, $search) {
		if (empty ( $str ) || empty ( $search ) || ! is_string ( $str ) || ! is_string ( $search )) {
			return FALSE;
		}
		return substr ( $str, 0, strlen ( $search ) ) == $search;
	}
	
	/**
	 * 判断字符串$str是否由$search作为结尾
	 * @param string $str 原字符串
	 * @param string $search 待搜索的字符串 
	 * @return TRUE or FALSE
	 */
	public static function end_with($str, $search) {
		if (empty ( $str ) || empty ( $search ) || ! is_string ( $str ) || ! is_string ( $search )) {
			return FALSE;
		}
		return substr ( $str, 0 - strlen ( $search ) ) == $search;
	}
	
	/**
	 * 截取UTF-8编码字符串
	 * @param string $str 原字符串
	 * @param int $from 截取的开始位置
	 * @param int $len 截取的长度
	 * @return 截取后的字符串
	 */
	public static function utf8_substr($str, $from, $len) {
		return preg_replace ( '#^(?:[\x00-\x7F]|[\xC0-\xFF][\x80-\xBF]+){0,' . $from . '}' . '((?:[\x00-\x7F]|[\xC0-\xFF][\x80-\xBF]+){0,' . $len . '}).*#s', '$1', $str );
	}
	
	/**
	 * 截取GB2312编码字符串
	 * @param string $str 原字符串
	 * @param int $start 截取的开始位置
	 * @param int $len 截取的长度
	 * @return 截取后的字符串
	 */
	public static function gb2312_substr($str, $start, $len) {
		$tmpstr = "";
		$strlen = $start + $len;
		for($i = 0; $i < $strlen; $i ++) {
			if (ord ( substr ( $str, $i, 1 ) ) > 0xa0) {
				$tmpstr .= substr ( $str, $i, 2 );
				$i ++;
			} else {
				$tmpstr .= substr ( $str, $i, 1 );
			}
		}
		return $tmpstr;
	}
	
	/**
	 * UTF-8、GB2312都支持的汉字截取函数
	 * @param string $string 原字符串
	 * @param int $sublen 截取后长度
	 * @param int $start 截取的开始位置
	 * @param string $code 字符串编码方式
	 */
	public static function cut_str($string, $sublen, $start = 0, $code = 'UTF-8', $ellipsis = '') {
		if ($code == 'UTF-8') {
			$pa = "/[\x01-\x7f]|[\xc2-\xdf][\x80-\xbf]|\xe0[\xa0-\xbf][\x80-\xbf]|[\xe1-\xef][\x80-\xbf][\x80-\xbf]|\xf0[\x90-\xbf][\x80-\xbf][\x80-\xbf]|[\xf1-\xf7][\x80-\xbf][\x80-\xbf][\x80-\xbf]/";
			preg_match_all ( $pa, $string, $t_string );
			if (count ( $t_string [0] ) - $start > $sublen) {
				return join ( '', array_slice ( $t_string [0], $start, $sublen ) ) . $ellipsis;
			}
			return join ( '', array_slice ( $t_string [0], $start, $sublen ) );
		} else {
			$start = $start * 2;
			$sublen = $sublen * 2;
			$strlen = strlen ( $string );
			$tmpstr = '';
			for($i = 0; $i < $strlen; $i ++) {
				if ($i >= $start && $i < ($start + $sublen)) {
					if (ord ( substr ( $string, $i, 1 ) ) > 129) {
						$tmpstr .= substr ( $string, $i, 2 );
					} else {
						$tmpstr .= substr ( $string, $i, 1 );
					}
				}
				if (ord ( substr ( $string, $i, 1 ) ) > 129) {
					$i ++;
				}
			}
			if (strlen ( $tmpstr ) < $strlen) {
				$tmpstr .= $ellipsis;
			}
			return $tmpstr;
		}
	}
	
	/**
	 * 获取UTF-8编码字符串的长度
	 * @param string $str 待检测的字符串
	 * @return 返回字符串的长度
	 */
	public static function strlen_utf8($str) {
		$i = 0;
		$count = 0;
		$len = strlen ( $str );
		while ( $i < $len ) {
			$chr = ord ( $str [$i] );
			$count ++;
			$i ++;
			if ($i >= $len) {
				break;
			}
			if ($chr & 0x80) {
				$chr <<= 1;
				while ( $chr & 0x80 ) {
					$i ++;
					$chr <<= 1;
				}
			}
		}
		return $count;
	}
	
	/**
	 * 判断是否是UTF-8字符串
	 * @param string $word 待检测字符串
	 * @return TRUE or FALSE
	 */
	public static function is_utf8($word) {
		if (preg_match ( "/^([" . chr ( 228 ) . "-" . chr ( 233 ) . "]{1}[" . chr ( 128 ) . "-" . chr ( 191 ) . "]{1}[" . chr ( 128 ) . "-" . chr ( 191 ) . "]{1}){1}/", $word ) == true || preg_match ( "/([" . chr ( 228 ) . "-" . chr ( 233 ) . "]{1}[" . chr ( 128 ) . "-" . chr ( 191 ) . "]{1}[" . chr ( 128 ) . "-" . chr ( 191 ) . "]{1}){1}$/", $word ) == true || preg_match ( "/([" . chr ( 228 ) . "-" . chr ( 233 ) . "]{1}[" . chr ( 128 ) . "-" . chr ( 191 ) . "]{1}[" . chr ( 128 ) . "-" . chr ( 191 ) . "]{1}){2,}/", $word ) == true) {
			return TRUE;
		} else {
			return FALSE;
		}
	}
	
	/**
	 * 判断一个字符串$str是否包含$search
	 * @param string $str 待检测字符串
	 * @param string $search 包含的字符串
	 * @param boolean $case_sensitive 大小写是否敏感
	 * @return TRUE or FALSE
	 */
	public static function my_is_str_include($str, $search, $case_sensitive = TRUE) {
		if ($case_sensitive) {
			return FALSE !== strpos ( $str, $search );
		} else {
			return FALSE !== stripos ( $str, $search );
		}
	}
	
	/**
	 * 匹配出$str中的UTF-8编码的中文字
	 * @param string $str 待检测字符串
	 * @return 匹配出的中文字数组
	 */
	public static function my_match_utf8_chinese($str) {
		preg_match_all ( '~[\x{4e00}-\x{9fa5}]+~u', $str, $tmp );
		return $tmp;
	}
	
	/**
	 * 检查字符串长度是否在规定长度内
	 * @param string $str 待检测字符串
	 * @param int $minlength 最小长度
	 * @param int $maxlength 最大长度
	 * @return TRUE or FALSE
	 */
	public static function my_check_strlength($str, $minlength, $maxlength) {
		if (! is_string ( $str ) || ! is_int ( $minlength ) || ! is_int ( $maxlength ) || $minlength > $maxlength) {
			return FALSE;
		}
		$str = trim ( $str );
		if (self::strlen_utf8 ( $str ) < $minlength || self::strlen_utf8 ( $str ) > $maxlength) {
			return FALSE;
		}
		return TRUE;
	}
	
	/** 
	 * js escape php 实现 
	 * @param $string the sting want to be escaped 
	 * @param $in_encoding       
	 * @param $out_encoding      
	 */
	public static function escape($string, $in_encoding = 'UTF-8', $out_encoding = 'UCS-2') {
		$return = '';
		if (function_exists ( 'mb_get_info' )) {
			for($x = 0; $x < mb_strlen ( $string, $in_encoding ); $x ++) {
				$str = mb_substr ( $string, $x, 1, $in_encoding );
				if (strlen ( $str ) > 1) { // 多字节字符 
					$return .= '%u' . strtoupper ( bin2hex ( mb_convert_encoding ( $str, $out_encoding, $in_encoding ) ) );
				} else {
					$return .= '%' . strtoupper ( bin2hex ( $str ) );
				}
			}
		}
		return $return;
	}
	
	/** 
	 * js unescape php 实现 
	 * @param $string           the sting want to be unescaped     
	 */
	public static function unescape($str) {
		$ret = '';
		$len = strlen ( $str );
		for($i = 0; $i < $len; $i ++) {
			if ($str [$i] == '%' && $str [$i + 1] == 'u') {
				$val = hexdec ( substr ( $str, $i + 2, 4 ) );
				if ($val < 0x7f) {
					$ret .= chr ( $val );
				} else if ($val < 0x800) {
					$ret .= chr ( 0xc0 | ($val >> 6) ) . chr ( 0x80 | ($val & 0x3f) );
				} else {
					$ret .= chr ( 0xe0 | ($val >> 12) ) . chr ( 0x80 | (($val >> 6) & 0x3f) ) . chr ( 0x80 | ($val & 0x3f) );
				}
				$i += 5;
			} else if ($str [$i] == '%') {
				$ret .= urldecode ( substr ( $str, $i, 3 ) );
				$i += 2;
			} else {
				$ret .= $str [$i];
			}
		}
		return $ret;
	}
	
	/**
	 * 生成可读性较强的随机字符串
	 * @param int $length 字符串长度
	 */
	public static function readable_random_string($length = 6) {
		$conso = array ("b", "c", "d", "f", "g", "h", "j", "k", "l", "m", "n", "p", "r", "s", "t", "v", "w", "x", "y", "z" );
		$vocal = array ("a", "e", "i", "o", "u" );
		$password = "";
		srand ( ( double ) microtime () * 1000000 );
		$max = $length / 2;
		for($i = 1; $i <= $max; $i ++) {
			$password .= $conso [rand ( 0, 19 )];
			$password .= $vocal [rand ( 0, 4 )];
		}
		return $password;
	}
	
	/**
	 * 生成唯一码
	 */
	public static function generate_unique_code() {
		return self::my_md5 ( uniqid ( TRUE ) );
	}
	
	public static function my_trim($val){
		return $val === NULL ? NULL : trim($val);
	}
}