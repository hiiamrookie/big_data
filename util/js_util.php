<?php
class Js_Util {
	private static $js_start = '<script type="text/javascript">';
	private static $js_end = '</script>';
	/**
	 * 生成action以后的JS
	 * @param string $msg alert的信息
	 * @param string $url 跳转到的页面
	 */
	public static function my_action_js($msg, $url) {
		$js = self::$js_start;
		if (! empty ( $msg )) {
			$js .= 'alert("' . $msg . '");';
		}
		if (! empty ( $url )) {
			$js .= 'location.href="' . $url . '"';
		}
		$js .= self::$js_end;
		echo $js;
		exit ();
	}
	
	/**
	 * 用JS形式显示成功信息
	 * @param string $message 成功信息
	 */
	public static function my_show_success_message($message = '新增成功',$url=NULL) {
		$all_message = '';
		if (is_string ( $message )) {
			$all_message = $message;
		} elseif (is_array ( $message )) {
			foreach ( $message as $msg ) {
				$all_message .= $msg . '\n';
			}
		}
		$js = self::$js_start;
		$js .= 'if(window.parent){';
		$js .= 'parent.show_message("' . $all_message . '");';
		$js .= 'parent.location.href="' . ($url === NULL ? $_SERVER ['HTTP_REFERER'] : $url) . '";';
		$js .= '}else{';
		$js .= 'show_message("' . $all_message . '");';
		$js .= 'location.href="' . ($url === NULL ? $_SERVER ['HTTP_REFERER'] : $url) . '";';
		$js .= '}';
		$js .= self::$js_end;
		echo $js;
	}
	
	/**
	 * 用JS形式显示错误信息
	 * @param string or array $message 错误信息
	 */
	public static function my_show_error_message($message = '非正常访问') {
		$all_message = '';
		if (is_string ( $message )) {
			$all_message = $message;
		} elseif (is_array ( $message )) {
			foreach ( $message as $msg ) {
				$all_message .= $msg . '\n';
			}
		}
		$js = self::$js_start;
		$js .= 'if(window.parent){';
		$js .= 'parent.show_message("' . $all_message . '");';
		$js .= '}else{';
		$js .= 'show_message("' . $all_message . '");';
		$js .= '}';
		$js .= self::$js_end;
		echo $js;
	}
	
	/**
	 * 生成JS弹出alert框后跳转URL
	 * @param string $msg alert框中文字内容
	 * @param string $direct 跳转的URL
	 */
	public static function my_js_alert($msg, $direct = '') {
		$js = self::$js_start;
		$js .= 'alert("' . $msg . '");';
		$js .= self::$js_end;
		echo $js;
		if ('' !== $direct) {
			self::my_js_redirect ( $direct );
		}
	}
	
	/**
	 * 页面跳转
	 * @param string $url 跳转后的页面URL
	 */
	public static function my_js_redirect($url) {
		$js = self::$js_start;
		$js .= 'if(window.top){';
		$js .= 'window.top.location.href="' . $url . '";';
		$js .= '}else{';
		$js .= 'window.location.href="' . $url . '";';
		$js .= '}';
		$js .= self::$js_end;
		$js .= '<noscript>';
		$js .= '<meta http-equiv="refresh" content="0;url=' . $url . '" />';
		$js .= '</noscript>';
		echo $js;
		exit ();
	}
}