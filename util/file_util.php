<?php
class File_Util {
	/**
	 * 生成文件上传后的文件名
	 * @param string $file_type 文件后缀类型
	 */
	public static function generate_upload_file_name($file_type) {
		date_default_timezone_set ( 'PRC' );
		return date ( 'YmdHis', time () ) . mt_rand ( 0, 100000000 ) . '.' . $file_type;
	}
	
	/**
	 * 追加内容到文件中
	 * @param string $filename 文件名（含路径）
	 * @param string $content 写入的内容
	 */
	public static function my_append_in_file($filename, $content) {
		$handle = fopen ( $filename, 'a' );
		$old_content = file_get_contents ( $filename );
		flock ( $handle, LOCK_EX );
		fwrite ( $handle, $old_content . $content . "\n" );
		flock ( $handle, LOCK_UN );
		fclose ( $handle );
	}
	
	/**
	 * 写内容到文件中
	 * @param string $filename 文件名（含路径）
	 * @param string $content 写入的内容
	 */
	public static function my_write_in_file($filename, $content) {
		$handle = fopen ( $filename, 'w' );
		flock ( $handle, LOCK_EX );
		fwrite ( $handle, $content . "\n" );
		flock ( $handle, LOCK_UN );
		fclose ( $handle );
	}
	
	/**
	 * 生成略缩图
	 * @param string $src_img 原文件名
	 * @param string $des_img 略缩图名
	 * @param int $dst_h 略缩图长
	 * @param int $dst_w 略缩图宽
	 */
	public static function resize_image($src_img, $des_img, $dst_h, $dst_w) {
		// 获取原图尺寸
		list ( $src_w, $src_h ) = getimagesize ( $src_img );
		
		//目标图像长宽比
		$dst_scale = $dst_h / $dst_w;
		//原图长宽比
		$src_scale = $src_h / $src_w;
		
		if ($src_scale >= $dst_scale) {
			//过高
			$w = intval ( $src_w );
			$h = intval ( $dst_scale * $w );
			
			$x = 0;
			$y = ($src_h - $h) / 3;
		
		} else {
			// 过宽
			$h = intval ( $src_h );
			$w = intval ( $h / $dst_scale );
			
			$x = ($src_w - $w) / 2;
			$y = 0;
		}
		
		// 剪裁
		$ext = explode ( '.', $src_img );
		$ext = $ext [count ( $ext ) - 1];
		if ($ext == 'jpg' || $ext == 'jpeg')
			$source = imagecreatefromjpeg ( $src_img );
		elseif ($ext == 'png')
			$source = imagecreatefrompng ( $src_img );
		elseif ($ext == 'gif')
			$source = imagecreatefromgif ( $src_img );
		
		$croped = imagecreatetruecolor ( $w, $h );
		imagecopy ( $croped, $source, 0, 0, $x, $y, $src_w, $src_h );
		
		// 缩放
		$scale = $dst_w / $w;
		$target = imagecreatetruecolor ( $dst_w, $dst_h );
		$final_w = intval ( $w * $scale );
		$final_h = intval ( $h * $scale );
		imagecopyresampled ( $target, $croped, 0, 0, 0, 0, $final_w, $final_h, $w, $h );
		
		// 保存
		$timestamp = time ();
		if ($ext == 'jpg' || $ext == 'jpeg')
			imagejpeg ( $target, $des_img );
		elseif ($ext == 'png')
			imagepng ( $target, $des_img );
		elseif ($ext == 'gif')
			imagegif ( $target, $des_img );
		
		imagedestroy ( $target );
	}
}