<?php
class Security_Util {
	/**
	 * 清理外来输入
	 * @param string $input 外来输入内容
	 */
	public static function clean_up_input($input, $editor = FALSE) {
		if ($input !== NULL) {
			$input = trim ( $input );
			if (! $editor) {
				$input = strip_tags ( $input );
			} else {
				require (dirname ( dirname ( __FILE__ ) ) . '/lib/htmlpurifier-4.4.0/library/HTMLPurifier.auto.php');
				$config = HTMLPurifier_Config::createDefault ();
				$config->set ( 'HTML.SafeEmbed', true );
				$def = $config->getHTMLDefinition ( true );
				$def->addAttribute ( 'a', 'target', 'Enum#_blank,_self' );
				$purifier = new HTMLPurifier ( $config );
				$input = $purifier->purify ( $input );
			}
			
			$input = htmlspecialchars ( $input );
			if (! get_magic_quotes_gpc ()) {
				$input = addslashes ( $input );
			}
		}
		return $input;
	}
	
	/**
	 * 获得POST数据
	 * @param string $name POST数据名
	 * @param boolean $editor 是否是富文本编辑器传来的数据
	 */
	public static function my_post($name, $editor = FALSE) {
		$value = '';
		eval ( '$value = $_POST["' . $name . '"];' );
		$value = self::clean_up_input ( $value, $editor );
		return $value;
	}
	
	/**
	 * 获得checkbox的POST数据
	 * @param string $name checkbox名
	 */
	public static function my_checkbox_post($name) {
		$value = '';
		eval ( '$value = $_POST["' . $name . '"];' );
		$result = array ();
		foreach ( $value as $v ) {
			$v = self::clean_up_input ( $v );
			if (! empty ( $v )) {
				$result [] = $v;
			}
		}
		return $result;
	}
	
	/**
	 * 获得GET数据
	 * @param string $name GET数据名
	 */
	public static function my_get($name, $editor = FALSE) {
		$value = '';
		eval ( '$value = $_GET["' . $name . '"];' );
		$value = self::clean_up_input ( $value, $editor );
		return $value;
	}
	
	public static function my_request($name){
		$value = '';
		eval ( '$value = $_REQUEST["' . $name . '"];' );
		$value = self::clean_up_input ( $value );
		return $value;
	}
	
	/**
	 * 防止EMAIL注入
	 * @param string $email 待检测EMAIL地址
	 * @return 如果过滤后的EMAIL地址不合法返回FALSE，否则返回过滤后的EMAIL地址
	 */
	public static function anti_email_inject($email) {
		$email = filter_var ( $email, FILTER_SANITIZE_EMAIL );
		if (! Validate_Util::my_is_email ( $email, TRUE )) {
			return FALSE;
		}
		return $email;
	}
	
	/**
	 * XSS过滤函数
	 * @param unknown_type $val 待过滤的值
	 */
	public static function remove_xss($val) {
		// remove all non-printable characters. CR(0a) and LF(0b) and TAB(9) are allowed
		// this prevents some character re-spacing such as <java\0script>
		// note that you have to handle splits with \n, \r, and \t later since they *are* allowed in some inputs
		$val = preg_replace ( '/([\x00-\x08,\x0b-\x0c,\x0e-\x19])/', '', $val );
		
		// straight replacements, the user should never need these since they're normal characters
		// this prevents like <IMG SRC=@avascript:alert('XSS')>
		$search = 'abcdefghijklmnopqrstuvwxyz';
		$search .= 'ABCDEFGHIJKLMNOPQRSTUVWXYZ';
		$search .= '1234567890!@#$%^&*()';
		$search .= '~`";:?+/={}[]-_|\'\\';
		for($i = 0; $i < strlen ( $search ); $i ++) {
			// ;? matches the ;, which is optional
			// 0{0,7} matches any padded zeros, which are optional and go up to 8 chars 
			

			// @ @ search for the hex values
			$val = preg_replace ( '/(&#[xX]0{0,8}' . dechex ( ord ( $search [$i] ) ) . ';?)/i', $search [$i], $val ); // with a ;
			// @ @ 0{0,7} matches '0' zero to seven times
			$val = preg_replace ( '/(&#0{0,8}' . ord ( $search [$i] ) . ';?)/', $search [$i], $val ); // with a ;
		}
		
		// now the only remaining whitespace attacks are \t, \n, and \r
		$ra1 = Array ('javascript', 'vbscript', 'expression', 'applet', 'meta', 'xml', 'blink', 'link', 'style', 'script', 'embed', 'object', 'iframe', 'frame', 'frameset', 'ilayer', 'layer', 'bgsound', 'title', 'base' );
		$ra2 = Array ('onabort', 'onactivate', 'onafterprint', 'onafterupdate', 'onbeforeactivate', 'onbeforecopy', 'onbeforecut', 'onbeforedeactivate', 'onbeforeeditfocus', 'onbeforepaste', 'onbeforeprint', 'onbeforeunload', 'onbeforeupdate', 'onblur', 'onbounce', 'oncellchange', 'onchange', 'onclick', 'oncontextmenu', 'oncontrolselect', 'oncopy', 'oncut', 'ondataavailable', 'ondatasetchanged', 'ondatasetcomplete', 'ondblclick', 'ondeactivate', 'ondrag', 'ondragend', 'ondragenter', 'ondragleave', 'ondragover', 'ondragstart', 'ondrop', 'onerror', 'onerrorupdate', 'onfilterchange', 'onfinish', 'onfocus', 'onfocusin', 'onfocusout', 'onhelp', 'onkeydown', 'onkeypress', 'onkeyup', 'onlayoutcomplete', 'onload', 'onlosecapture', 'onmousedown', 'onmouseenter', 'onmouseleave', 'onmousemove', 'onmouseout', 'onmouseover', 'onmouseup', 'onmousewheel', 'onmove', 'onmoveend', 'onmovestart', 'onpaste', 'onpropertychange', 'onreadystatechange', 'onreset', 'onresize', 'onresizeend', 'onresizestart', 'onrowenter', 'onrowexit', 'onrowsdelete', 'onrowsinserted', 'onscroll', 'onselect', 'onselectionchange', 'onselectstart', 'onstart', 'onstop', 'onsubmit', 'onunload' );
		$ra = array_merge ( $ra1, $ra2 );
		
		$found = true; // keep replacing as long as the previous round replaced something
		while ( $found == true ) {
			$val_before = $val;
			for($i = 0; $i < sizeof ( $ra ); $i ++) {
				$pattern = '/';
				for($j = 0; $j < strlen ( $ra [$i] ); $j ++) {
					if ($j > 0) {
						$pattern .= '(';
						$pattern .= '(&#[xX]0{0,8}([9ab]);)';
						$pattern .= '|';
						$pattern .= '|(&#0{0,8}([9|10|13]);)';
						$pattern .= ')*';
					}
					$pattern .= $ra [$i] [$j];
				}
				$pattern .= '/i';
				$replacement = substr ( $ra [$i], 0, 2 ) . '<x>' . substr ( $ra [$i], 2 ); // add in <> to nerf the tag
				$val = preg_replace ( $pattern, $replacement, $val ); // filter out the hex tags
				if ($val_before == $val) {
					// no replacements were made, so exit the loop
					$found = false;
				}
			}
		}
		
		return $val;
	}
	
	/**
	 * 检测页面是否合法连接过来
	 * @param string $err_redirect_url 如果检测是非法链接，跳转到的URL
	 */
	public static function check_url($err_redirect_url) {
		//如果直接从浏览器连接到页面，就跳转到$err_redirect_url
		if (! isset ( $_SERVER ['HTTP_REFERER'] )) {
			Server_Util::my_server_redirect ( $err_redirect_url );
			exit ();
		}
		$urlar = parse_url ( $_SERVER ['HTTP_REFERER'] );
		
		//如果页面的域名不是服务器域名,就跳转到$err_redirect_url
		if ($_SERVER ['HTTP_HOST'] != $urlar ['host']) {
			Server_Util::my_server_redirect ( $err_redirect_url );
			exit ();
		}
	}
	
	public static function encrypt($key, $plain_text) {
		$plain_text = trim ( $plain_text );
		$iv = substr ( md5 ( $key ), 0, mcrypt_get_iv_size ( MCRYPT_CAST_256, MCRYPT_MODE_CFB ) );
		$c_t = mcrypt_cfb ( MCRYPT_CAST_256, $key, $plain_text, MCRYPT_ENCRYPT, $iv );
		return trim ( chop ( base64_encode ( $c_t ) ) );
	}
	
	public static function decrypt($key, $c_t) {
		$c_t = trim ( chop ( base64_decode ( $c_t ) ) );
		$iv = substr ( md5 ( $key ), 0, mcrypt_get_iv_size ( MCRYPT_CAST_256, MCRYPT_MODE_CFB ) );
		$p_t = mcrypt_cfb ( MCRYPT_CAST_256, $key, $c_t, MCRYPT_DECRYPT, $iv );
		return trim ( chop ( $p_t ) );
	}
}