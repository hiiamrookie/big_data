<?php
if(!defined('DIRECTORY_SEPARATOR'))
	define ( 'DIRECTORY_SEPARATOR', '/' );
if(!defined('FOPEN_WRITE_CREATE_DESTRUCTIVE'))
	define ( 'FOPEN_WRITE_CREATE_DESTRUCTIVE', 'wb' );
if(!defined('FOPEN_WRITE_CREATE'))
	define ( 'FOPEN_WRITE_CREATE', 'ab' );
if(!defined('DIR_WRITE_MODE'))
	define ( 'DIR_WRITE_MODE', 0777 );
class FileCache {
	/**
	 * 缓存路径
	 *
	 * @access private
	 * @var string
	 */
	private $_cache_path;
	/**
	 * 缓存过期时间，单位是秒second
	 *
	 * @access private
	 * @var int
	 */
	private $_cache_expire;
	/**
	 * 解析函数，设置缓存过期实践和存储路径
	 *
	 * @access public
	 * @return void
	 */
	public function __construct($expire, $cache_path) {
		$this->_cache_expire = $expire;
		$this->_cache_path = $cache_path;
	}
	/**
	 * 缓存文件名
	 *
	 * @access public
	 * @param  string $key
	 * @return void
	 */
	private function _file($key) {
		return $this->_cache_path . md5 ( $key );
	}
	/**
	 * 设置缓存
	 *
	 * @access public
	 * @param  string $key 缓存的唯一键
	 * @param  string $data 缓存的内容
	 * @return bool
	 */
	public function set($key, $data) {
		$value = serialize ( $data );
		$file = $this->_file ( $key );
		return $this->write_file ( $file, $value );
	}
	/**
	 * 获取缓存
	 *
	 * @access public
	 * @param  string $key 缓存的唯一键
	 * @return mixed
	 */
	public function get($key) {
		$file = $this->_file ( $key );
		/** 文件不存在或目录不可写 */
		if (! file_exists ( $file ) || ! $this->is_really_writable ( $file )) {
			return false;
		}
		/** 缓存没有过期，仍然可用 */
		if (time () < (filemtime ( $file ) + $this->_cache_expire)) {
			$data = $this->read_file ( $file );
			if (FALSE !== $data) {
				return unserialize ( $data );
			}
			return FALSE;
		}
		/** 缓存过期，删除之 */
		@unlink ( $file );
		return FALSE;
	}
	function read_file($file) {
		if (! file_exists ( $file )) {
			return FALSE;
		}
		if (function_exists ( 'file_get_contents' )) {
			return file_get_contents ( $file );
		}
		if (! $fp = @fopen ( $file, FOPEN_READ )) {
			return FALSE;
		}
		flock ( $fp, LOCK_SH ); //读取之前加上共享锁
		$data = '';
		if (filesize ( $file ) > 0) {
			$data = & fread ( $fp, filesize ( $file ) );
		}
		flock ( $fp, LOCK_UN ); //释放锁
		fclose ( $fp );
		return $data;
	}
	function write_file($path, $data, $mode = FOPEN_WRITE_CREATE_DESTRUCTIVE) {
		if (! $fp = @fopen ( $path, $mode )) {
			return FALSE;
		}
		flock ( $fp, LOCK_EX );
		fwrite ( $fp, $data );
		flock ( $fp, LOCK_UN );
		fclose ( $fp );
		return TRUE;
	}
	function is_really_writable($file) //兼容各平台判断文件是否有写入权限
{
		// If we're on a Unix server with safe_mode off we call is_writable
		if (DIRECTORY_SEPARATOR == '/' and @ini_get ( "safe_mode" ) == FALSE) {
			return is_writable ( $file );
		}
		// For windows servers and safe_mode "on" installations we'll actually
		// write a file then read it.  Bah...
		if (is_dir ( $file )) {
			$file = rtrim ( $file, '/' ) . '/' . md5 ( rand ( 1, 100 ) );
			if (($fp = @fopen ( $file, FOPEN_WRITE_CREATE )) === FALSE) {
				return FALSE;
			}
			fclose ( $fp );
			@chmod ( $file, DIR_WRITE_MODE );
			@unlink ( $file );
			return TRUE;
		} elseif (($fp = @fopen ( $file, FOPEN_WRITE_CREATE )) === FALSE) {
			return FALSE;
		}
		fclose ( $fp );
		return TRUE;
	}
}

/* End of file FlieCache.php */