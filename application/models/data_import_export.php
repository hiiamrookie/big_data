<?php
class Data_Import_Export extends User {
	public function __construct() {
		parent::__construct ( FALSE, NULL );
	}
	public function get_own_data_import_html() {
		$buf = file_get_contents ( TEMPLATE_PATH . 'report/own_data_import.tpl' );
		return str_replace ( array (
				'[LEFT]',
				'[TOP]',
				'[VCODE]',
				'[VALIDATEFILE]',
				'[MAXFILESIZE]',
				'[BASE_URL]' 
		), array (
				$this->get_left_html (),
				$this->get_top_html (),
				$this->get_vcode (),
				implode ( ',', $GLOBALS ['defined_upload_execel_validate_type'] ),
				UPLOAD_FILE_MAX_SIZE / (1024 * 1024),
				BASE_URL 
		), $buf );
	}
	public function get_third_data_import_html() {
		$buf = file_get_contents ( TEMPLATE_PATH . 'report/third_data_import.tpl' );
		return str_replace ( array (
				'[LEFT]',
				'[TOP]',
				'[VCODE]',
				'[VALIDATEFILE]',
				'[MAXFILESIZE]',
				
				'[BASE_URL]' 
		), array (
				$this->get_left_html (),
				$this->get_top_html (),
				$this->get_vcode (),
				implode ( ',', $GLOBALS ['defined_upload_execel_validate_type'] ),
				UPLOAD_FILE_MAX_SIZE / (1024 * 1024),
				
				BASE_URL 
		), $buf );
	}
	public function get_data_export_html() {
		$tables = '';
		$websites = $this->db->get_results ( 'SELECT DISTINCT(website) AS website FROM data_import_third' );
		if ($websites !== NULL) {
			$tables .= '<tr><td style="font-weight:bold;width:150px;">Website</td><td><select id="website" name="website" class="validate[required] select">';
			foreach ( $websites as $website ) {
				$tables .= '<option value="' . $website->website . '">' . $website->website . '</option>';
			}
			$tables .= '</select></td></tr>';
		}
		$buf = file_get_contents ( TEMPLATE_PATH . 'report/data_export.tpl' );
		return str_replace ( array (
				'[LEFT]',
				'[TOP]',
				'[VCODE]',
				'[TABLE]',
				'[BASE_URL]' 
		), array (
				$this->get_left_html (),
				$this->get_top_html (),
				$this->get_vcode (),
				$tables,
				BASE_URL 
		), $buf );
	}
	private function _check_own_format($line, $infos, &$errors) {
		$isok = TRUE;
		// 第一列 website
		if (empty ( $infos [0] )) {
			$errors [] = '第' . $line . '行，第1列【website】不能为空';
			$isok = $isok ? FALSE : $isok;
		}
		
		// 第二列 姓名
		if (! empty ( $infos [1] ) && ! self::validate_field_max_length ( $infos [1], 100 )) {
			$errors [] = '第' . $line . '行，第2列【姓名】长度最多100个字符';
			$isok = $isok ? FALSE : $isok;
		}
		
		// 第三列 手机号
		if (! empty ( $infos [2] ) && ! preg_match ( '/^1[34578]\d{9}$/', $infos [2] )) {
			$errors [] = '第' . $line . '行，第3列【手机号】有误';
			$isok = $isok ? FALSE : $isok;
		}
		
		// 第四列 性别
		if (! empty ( $infos [3] ) && ! in_array ( $infos [3], array (
				'男',
				'女' 
		), TRUE )) {
			$errors [] = '第' . $line . '行，第4列【性别】有误';
			$isok = $isok ? FALSE : $isok;
		} else if (empty ( $infos [1] ) && empty ( $infos [2] ) && ! empty ( $infos [3] )) {
			$errors [] = '第' . $line . '行，数据缺失';
			$isok = $isok ? FALSE : $isok;
		}
		
		if (empty ( $infos [1] ) && empty ( $infos [2] ) && empty ( $infos [3] )) {
			$errors [] = '第' . $line . '行，数据缺失';
			$isok = $isok ? FALSE : $isok;
		}
		return $isok;
	}
	public function own_data_import($file) {
		if (file_exists ( $file )) {
			$PHPExcel = new PHPExcel ();
			if (strtolower ( pathinfo ( $file, PATHINFO_EXTENSION ) ) === 'xls') {
				$PHPReader = new PHPExcel_Reader_Excel5 ();
			} else if (strtolower ( pathinfo ( $file, PATHINFO_EXTENSION ) ) === 'xlsx') {
				$PHPReader = new PHPExcel_Reader_Excel2007 ();
			}
			
			$PHPExcel = $PHPReader->load ( $file );
			$PHPExcel->setActiveSheetIndex ( 0 );
			$sheet = $PHPExcel->getActiveSheet ();
			$sum_cols_count = PHPExcel_Cell::columnIndexFromString ( $sheet->getHighestColumn () );
			$sum_rows_count = $sheet->getHighestRow ();
			
			$errors = array ();
			// 应该是4列，行数>1
			if ($sum_cols_count !== 4) {
				$errors [] = '上传文件的列数非有效';
			} else if ($sum_rows_count <= 1) {
				$errors [] = '上传文件的行数非有效';
			} else {
				
				for($i = 2; $i <= $sum_rows_count; $i ++) {
					for($j = 0; $j < $sum_cols_count; $j ++) {
						$infos [$i] [$j] = $sheet->getCellByColumnAndRow ( $j, $i )->getCalculatedValue ();
						$infos [$i] [$j] = $infos [$i] [$j] === NULL ? NULL : trim ( $infos [$i] [$j] );
					}
					$this->db->query ( 'BEGIN' );
					$isok = $this->_check_own_format ( $i, $infos [$i], $errors );
					if ($isok) {
						$db_ok = TRUE;
						
						$url = str_replace ( array (
								'http://',
								'https://' 
						), array (
								'' 
						), strtolower ( $infos [$i] [0] ) );
						$sex = $infos [$i] [3] === '男' ? 1 : 0;
						$result = $this->db->query ( 'INSERT INTO data_import_own(website,name,mobile,sex) VALUE("' . $url . '","' . $infos [$i] [1] . '","' . $infos [$i] [2] . '",' . $sex . ')' );
						if ($result === FALSE) {
							$db_ok = FALSE;
						}
						
						if ($db_ok) {
							$this->db->query ( 'COMMIT' );
						} else {
							$errors [] = '第' . $i . '行记录导入失败';
							$this->db->query ( 'ROLLBACK' );
						}
					} else {
						$this->db->query ( 'COMMIT' );
					}
				}
			}
			return array (
					'status' => 'success',
					'message' => empty ( $errors ) ? '导入成功' : $errors 
			);
		} else {
			return array (
					'status' => 'error',
					'message' => '上传的文件不存在' 
			);
		}
	}
	private function _check_third_format($line, $infos, &$errors) {
		$isok = TRUE;
		// 第一列 website
		if (empty ( $infos [0] )) {
			$errors [] = '第' . $line . '行，第1列【website】不能为空';
			$isok = $isok ? FALSE : $isok;
		}
		
		// 第二列 pv
		if (! empty ( $infos [1] ) && ! Validate_Util::my_is_int ( $infos [1] )) {
			$errors [] = '第' . $line . '行，第2列【pv】必须是整数';
			$isok = $isok ? FALSE : $isok;
		}
		
		// 第三列 uv
		if (! empty ( $infos [2] ) && ! Validate_Util::my_is_int ( $infos [2] )) {
			$errors [] = '第' . $line . '行，第3列【uv】必须是整数';
			$isok = $isok ? FALSE : $isok;
		}
		
		if ($infos [2] > $infos [1]) {
			$errors [] = '第' . $line . '行，uv数不可大于pv数';
			$isok = $isok ? FALSE : $isok;
		}
		
		// 第四列 impressions
		if (! empty ( $infos [3] ) && ! Validate_Util::my_is_int ( $infos [3] )) {
			$errors [] = '第' . $line . '行，第4列【impressions】必须是整数';
			$isok = $isok ? FALSE : $isok;
		}
		
		// 第五列 click
		if (! empty ( $infos [4] ) && ! Validate_Util::my_is_int ( $infos [4] )) {
			$errors [] = '第' . $line . '行，第5列【click】必须是整数';
			$isok = $isok ? FALSE : $isok;
		}
		
		if ($infos [4] > $infos [3]) {
			$errors [] = '第' . $line . '行，click数不可大于impressions数';
			$isok = $isok ? FALSE : $isok;
		}
		
		// 第六列 日期
		if (! self::validate_field_not_null ( $infos [5] )) {
			$errors [] = '第' . $line . '行，第6列【date】不能为空';
			$isok = $isok ? FALSE : $isok;
		} else if (! self::validate_date_int ( PHPExcel_Shared_Date::ExcelToPHP ( $infos [5] ) )) {
			$errors [] = '第' . $line . '行，第6列【date】不是有效的时间值';
			$isok = $isok ? FALSE : $isok;
		}
		
		return $isok;
	}
	public function third_data_import($file) {
		if (file_exists ( $file )) {
			$PHPExcel = new PHPExcel ();
			if (strtolower ( pathinfo ( $file, PATHINFO_EXTENSION ) ) === 'xls') {
				$PHPReader = new PHPExcel_Reader_Excel5 ();
			} else if (strtolower ( pathinfo ( $file, PATHINFO_EXTENSION ) ) === 'xlsx') {
				$PHPReader = new PHPExcel_Reader_Excel2007 ();
			}
			
			$PHPExcel = $PHPReader->load ( $file );
			$PHPExcel->setActiveSheetIndex ( 0 );
			$sheet = $PHPExcel->getActiveSheet ();
			$sum_cols_count = PHPExcel_Cell::columnIndexFromString ( $sheet->getHighestColumn () );
			$sum_rows_count = $sheet->getHighestRow ();
			
			$errors = array ();
			// 应该是6列，行数>1
			if ($sum_cols_count !== 6) {
				$errors [] = '上传文件的列数非有效';
			} else if ($sum_rows_count <= 1) {
				$errors [] = '上传文件的行数非有效';
			} else {
				
				for($i = 2; $i <= $sum_rows_count; $i ++) {
					for($j = 0; $j < $sum_cols_count; $j ++) {
						$infos [$i] [$j] = $sheet->getCellByColumnAndRow ( $j, $i )->getCalculatedValue ();
						$infos [$i] [$j] = $infos [$i] [$j] === NULL ? NULL : trim ( $infos [$i] [$j] );
					}
					$this->db->query ( 'BEGIN' );
					$isok = $this->_check_third_format ( $i, $infos [$i], $errors );
					if ($isok) {
						$db_ok = TRUE;
						
						$url = str_replace ( array (
								'http://',
								'https://' 
						), array (
								'' 
						), strtolower ( $infos [$i] [0] ) );
						
						$result = $this->db->query ( 'INSERT INTO data_import_third(website,pv,uv,impressions,click,date,addtime) VALUE("' . $url . '",' . $infos [$i] [1] . ',' . $infos [$i] [2] . ',' . $infos [$i] [3] . ',' . $infos [$i] [4] . ',"' . date ( 'Y-m-d', PHPExcel_Shared_Date::ExcelToPHP ( $infos [$i] [5] ) ) . '","' . date ( 'Y-m-d H:i:s', time () ) . '")' );
						if ($result === FALSE) {
							$db_ok = FALSE;
						}
						
						if ($db_ok) {
							$this->db->query ( 'COMMIT' );
						} else {
							$errors [] = '第' . $i . '行记录导入失败';
							$this->db->query ( 'ROLLBACK' );
						}
					} else {
						$this->db->query ( 'COMMIT' );
					}
				}
			}
			return array (
					'status' => 'success',
					'message' => empty ( $errors ) ? '导入成功' : $errors 
			);
		} else {
			return array (
					'status' => 'error',
					'message' => '上传的文件不存在' 
			);
		}
	}
	public function data_export($website, $items) {
		$errors = array ();
		if (empty ( $website )) {
			$errors [] = 'Website必须选择';
		}
		
		if (empty ( $items )) {
			$errors [] = '请至少选择一个数据项';
		}
		
		if (! empty ( $errors )) {
			return array (
					'status' => 'error',
					'message' => $errors 
			);
		}
		
		$fields = array ();
		foreach ( $items as $item ) {
			switch ($item) {
				case 'pv' :
					$fields [] = 'SUM(pv) AS pv';
					break;
				case 'uv' :
					$fields [] = 'SUM(uv) AS uv';
					break;
				case 'impressions' :
					$fields [] = 'SUM(impressions) AS impressions';
					break;
				case 'click' :
					$fields [] = 'SUM(click) AS click';
					break;
				case 'ctr' :
					if (! in_array ( 'SUM(click) AS click', $fields, TRUE )) {
						$fields [] = 'SUM(click) AS click';
					}
					
					if (! in_array ( 'SUM(impressions) AS impressions', $fields, TRUE )) {
						$fields [] = 'SUM(impressions) AS impressions';
					}
					
					break;
			}
		}
		$fields = implode ( ',', $fields );
		$sql = 'SELECT ' . $fields . ' FROM data_import_third WHERE website="' . $website . '"';
		
		$result = $this->db->get_row ( $sql );
		if ($result === NULL) {
			return array (
					'status' => 'error',
					'message' => '没有记录' 
			);
		}
		
		// 开始生成excel
		$objPHPExcel = new PHPExcel ();
		PHPExcel_Settings::setCacheStorageMethod ( PHPExcel_CachedObjectStorageFactory::cache_in_memory_serialized );
		$objPHPExcel->setActiveSheetIndex ( 0 );
		$objPHPExcel->getActiveSheet ()->setTitle ( '数据导出' );
		
		$objPHPExcel->getActiveSheet ()->setCellValueByColumnAndRow ( 0, 1, 'Website' );
		foreach ( $items as $key => $item ) {
			$objPHPExcel->getActiveSheet ()->setCellValueByColumnAndRow ( $key + 1, 1, $item );
		}
		
		$objPHPExcel->getActiveSheet ()->setCellValueByColumnAndRow ( 0, 2, $website );
		foreach ( $items as $key => $item ) {
			if($item !== 'ctr'){
				$objPHPExcel->getActiveSheet ()->setCellValueByColumnAndRow ( $key + 1, 2, $result->$item );
			}else{
				$objPHPExcel->getActiveSheet ()->setCellValueByColumnAndRow ( $key + 1, 2, round(($result->click / $result->impressions) * 100,2) . '%' );
			}
		}
		
		$ua = $_SERVER ['HTTP_USER_AGENT'];
		
		if (preg_match ( '/MSIE/', $ua ) || preg_match ( '/Trident/', $ua )) {
			$filename = urlencode ( '数据导出' ) . time () . '.xls';
		} else {
			$filename = '数据导出' . time () . '.xls';
		}
		
		// 生成
		$objPHPExcel->setActiveSheetIndex ( 0 );
		header ( 'Content-Type: application/vnd.ms-excel' );
		header ( 'Content-Disposition: attachment;filename="' . $filename . '"' );
		header ( 'Cache-Control: max-age=0' );
		
		$objWriter = PHPExcel_IOFactory::createWriter ( $objPHPExcel, 'Excel5' );
		$objWriter->save ( 'php://output' );
		$objPHPExcel->disconnectWorksheets ();
		unset ( $objPHPExcel );
	}
}