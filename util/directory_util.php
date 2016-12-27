<?php
class Directory_Util {
	/**
	 * 删除非空目录
	 * @param string $dir 目录
	 */
	public static function remove_directory($dir) {
		if (FALSE !== ($handle = opendir ( "$dir" ))) {
			while ( FALSE !== ($item = readdir ( $handle )) ) {
				if ($item != "." && $item != "..") {
					if (is_dir ( "$dir/$item" )) {
						self::remove_directory ( "$dir/$item" );
					} else {
						unlink ( "$dir/$item" );
					}
				}
			}
			closedir ( $handle );
			rmdir ( $dir );
		}
	}
	
	/**
	 * 递归创建目录
	 * @param string $path 目录树
	 * @param unknown_type $mode 目录模式
	 */
	public static function my_mkdirs($path, $mode = 0777) {
		$dirs = explode ( '/', $path );
		$pos = strrpos ( $path, '.' );
		if (FALSE === $pos) {
			$subamount = 0;
		} else {
			$subamount = 1;
		}
		for($c = 0; $c < count ( $dirs ) - $subamount; $c ++) {
			$thispath = '';
			for($cc = 0; $cc <= $c; $cc ++) {
				$thispath .= $dirs [$cc] . '/';
			}
			if (! is_dir ( $thispath )) {
				self::my_mkdirs ( $thispath, $mode );
			}
		}
	}
}