<?php
class Upload_Util {
	/**
	 * 上传文件
	 * @param string $upfield 上传文件框的名字
	 * @param numic $validate_size 有效的文件容量大小
	 * @param string $final_file_path 最终文件存放的目录地址
	 * @param boolean $must_upload 是否该文件必须上传
	 * @param array $validate_type 允许上传的文件类型
	 * @param array $validate_mime 允许上传的文件mime类型
	 */
	public static function upload($upfield, $validate_size, $final_file_path,
			$must_upload, $validate_type, $validate_mime) {
		//获取上传文件数组
		$upload_file = eval('return $_FILES [\'' . $upfield . '\'];');
		if (!empty($upload_file['name'])) {
			//获取文件扩展名
			$name = explode('.', $upload_file['name']);
			$upload_file_xp = strtolower(
					end($name));

			if (!in_array($upload_file_xp, $validate_type)) {
				//非有效的文件类型
				return json_encode(
						array('status' => 'error', 'message' => '非有效文件类型'));
			}

			$upload_file_type = $upload_file['type'];
			if (!in_array($upload_file_type, $validate_mime)) {
				//非有效的文件类型
				return json_encode(
						array('status' => 'error', 'message' => '非有效文件类型'));
			} elseif ($upload_file['size'] > $validate_size) {
				//非有效的文件大小
				return json_encode(
						array('status' => 'error', 'message' => '非有效文件容量'));
			} else {
				//生成新的文件名
				$new_upload_file_name = File_Util::generate_upload_file_name(
						$upload_file_xp);
				$new_final_file_path = $final_file_path . $new_upload_file_name;

				if (!move_uploaded_file($upload_file['tmp_name'],
						$new_final_file_path)) {
					//上传文件失败
					return json_encode(
							array('status' => 'error', 'message' => '上传失败'));
				} else {
					//校验文件真实类型
					$file_ok = FALSE;
					foreach ($validate_type as $vtype) {
						if (FileTypeValidation::validation(
								$new_final_file_path, $vtype)) {
							$file_ok = TRUE;
							break;
						}
					}

					if (!$file_ok) {
						//非有效文件类型，删除文件
						unlink($new_final_file_path);
						return json_encode(
								array('status' => 'error',
										'message' => '非有效文件类型'));
					} else {
						//修改文件权限
						chmod($new_final_file_path, 0644);
						return json_encode(
								array('status' => 'success',
										'message' => array(
												'file_name' => str_replace(
														UPLOAD_FILE_PATH, '',
														$final_file_path)
														. $new_upload_file_name,
												'file_size' => $upload_file['size'],
												'file_realname' => $upload_file['name'])));
					}
				}
			}
		} else if ($must_upload && empty($upload_file['name'])) {
			//如果上传文件框为空且定义必须上传，返回出错信息
			return json_encode(
					array('status' => 'error', 'message' => '文件不能为空'));
		} else {
			return NULL;
		}
	}
}