<?php
class Executive_Media_Schedule extends User {
	private $page;
	private $starttime;
	private $endtime;
	private $search;
	private $all_count;
	private $page_count;
	private $pid;
	private $executives = array ();
	const LIMIT = 50;
	public function __construct($fields = array()) {
		parent::__construct ();
		if (! empty ( $fields )) {
			foreach ( $this as $key => $value ) {
				if ($fields [$key] !== NULL) {
					$this->$key = $fields [$key];
				}
			}
		}
	}
	private function _get_myexecutive_list_datas() {
		$where_sql = array ();
		
		if (strtotime ( $this->starttime ) !== FALSE) {
			$where_sql [] = ' m.time>=' . strtotime ( $this->starttime ) . ' ';
		}
		
		if (strtotime ( $this->endtime ) !== FALSE) {
			$where_sql [] = ' m.time<' . (strtotime ( $this->endtime ) + 86400) . ' ';
		}
		
		if (self::validate_field_not_null ( $this->search ) && self::validate_field_not_empty ( $this->search )) {
			$where_sql [] = ' (m.pid LIKE "%' . $this->search . '%" OR m.name LIKE "%' . $this->search . '%" OR n.cusname LIKE "%' . $this->search . '%") ';
		}
		
		$sql = 'SELECT m.*,n.cusname,o.pid_schedule
				FROM
				(
					SELECT a.id,a.pid,a.cid,a.name,a.amount,a.allcost,a.isok,a.isalter,a.time,a.isyg FROM executive a,
						(
							SELECT MAX(isalter) AS isalter,pid FROM executive WHERE user=' . $this->getUid () . ' AND isok<>-1 GROUP BY pid
						)
						b WHERE a.isok=1 AND a.pid=b.pid AND a.isalter=b.isalter
				)
				m
				LEFT JOIN contract_cus n
				ON m.cid=n.cid
				LEFT JOIN (SELECT DISTINCT(pid) AS pid_schedule FROM executive_media_schedule_content) o
				ON m.pid=o.pid_schedule';
		if (! empty ( $where_sql )) {
			$sql .= ' WHERE ' . implode ( 'AND ', $where_sql );
		}
		
		$this->all_count = intval ( $this->db->get_var ( 'SELECT COUNT(*) FROM (' . $sql . ') x' ) );
		$this->page_count = ceil ( $this->all_count / self::LIMIT );
		$start = self::LIMIT * intval ( $this->page ) - self::LIMIT;
		if ($start < 0) {
			$start = 0;
		}
		
		$results = $this->db->get_results ( $sql . ' ORDER BY m.time DESC LIMIT ' . $start . ',' . self::LIMIT );
		$datas = array ();
		if ($results !== NULL) {
			foreach ( $results as $result ) {
				$datas [] = array (
						'id' => $result->id,
						'pid' => $result->pid,
						'cid' => $result->cid,
						'name' => $result->name,
						'amount' => $result->amount,
						'allcost' => $result->allcost,
						'isok' => $result->isok,
						'isalter' => $result->isalter,
						'isyg' => $result->isyg,
						'cusname' => $result->cusname,
						'pid_schedule' => $result->pid_schedule 
				);
			}
		}
		$this->executives = $datas;
		unset ( $datas );
	}
	private function _get_executive_list_html() {
		$result = '';
		if (! empty ( $this->executives )) {
			foreach ( $this->executives as $key => $value ) {
				$result .= '<tr><td>' . Executive_List::get_executive_type ( intval ( $value ['isalter'] ) ) . '</td><td>' . $value ['pid'] . '</td><td>' . $value ['cusname'] . '</td><td>' . Executive_List::get_executive_name_link ( $value ['id'], $value ['pid'], $value ['name'] ) . '</td><td>' . Executive_List::get_executive_amount ( $value ['amount'] ) . '</td><td>' . Executive_List::get_executive_cost ( $value ['allcost'], intval ( $value ['isyg'] ) ) . '</td><td>' . self::_has_media_schedule ( $value ['pid_schedule'] ) . '</td><td>' . self::_get_action ( $value ['pid'], $value ['pid_schedule'] ) . '</td></tr>';
			}
		}
		return $result;
	}
	private static function _has_media_schedule($pid_schedule) {
		return empty ( $pid_schedule ) ? '<font color="#ff6600"><b>未上传</b></font>' : '<font color="#66cc00"><b>已上传</b></font>';
	}
	private static function _get_action($pid, $pid_schedule) {
		return empty ( $pid_schedule ) ? '<a href="' . BASE_URL . 'executive/?o=upload_media_schedule&pid=' . $pid . '">上传</a>' : '查看';
	}
	private function _get_executive_counts() {
		return $this->page . '	/' . $this->page_count . ' 页 &nbsp;&nbsp;';
	}
	private function _get_pagination($is_prev) {
		$param = '&starttime=' . $this->starttime . '&endtime=' . $this->endtime . '&page=' . ($is_prev ? intval ( $this->page ) - 1 : intval ( $this->page ) + 1) . '&search=' . $this->search;
		return '<a href="' . BASE_URL . 'executive/?o=media_schedule' . $param . '">' . ($is_prev ? '上一页' : '下一页') . '</a>';
	}
	private function _get_next() {
		if (intval ( $this->page ) >= intval ( $this->page_count )) {
			return '';
		} else {
			return $this->_get_pagination ( FALSE );
		}
	}
	private function _get_prev() {
		if (intval ( $this->page ) === 1) {
			return '';
		} else {
			return $this->_get_pagination ( TRUE );
		}
	}
	public function get_executive_mylist_media_schedule_html() {
		$this->_get_myexecutive_list_datas ();
		
		$buf = file_get_contents ( TEMPLATE_PATH . 'executive/executive_mylist_media_schedule.tpl' );
		return str_replace ( array (
				'[LEFT]',
				'[TOP]',
				'[VCODE]',
				'[EXECUTIVELIST]',
				'[ALLCOUNTS]',
				'[COUNTS]',
				'[NEXT]',
				'[PREV]',
				'[STARTTIME]',
				'[ENDTIME]',
				'[SEARCH]',
				'[BASE_URL]' 
		), array (
				$this->get_left_html (),
				$this->get_top_html (),
				$this->get_vcode (),
				$this->_get_executive_list_html (),
				$this->all_count,
				$this->_get_executive_counts (),
				$this->_get_next (),
				$this->_get_prev (),
				$this->starttime,
				$this->endtime,
				$this->search,
				BASE_URL 
		), $buf );
	}
	public function get_upload_media_schedule_html() {
		$buf = file_get_contents ( TEMPLATE_PATH . 'executive/media_schedule_import.tpl' );
		return str_replace ( array (
				'[LEFT]',
				'[TOP]',
				'[VCODE]',
				'[VALIDATEFILE]',
				'[MAXFILESIZE]',
				'[PID]',
				'[BASE_URL]' 
		), array (
				$this->get_left_html (),
				$this->get_top_html (),
				$this->get_vcode (),
				implode ( ',', $GLOBALS ['defined_upload_execel_validate_type'] ),
				UPLOAD_FILE_MAX_SIZE / (1024 * 1024),
				$this->pid,
				BASE_URL 
		), $buf );
	}
	private function _check_data_format($line, $infos, &$errors) {
		$isok = TRUE;
		// 第一列 dsp平台
		if (! in_array ( $infos [0], $GLOBALS ['defined_dsp_platform'], TRUE )) {
			$errors [] = '第' . $line . '行，第1列【dsp平台】输入有误';
			$isok = $isok ? FALSE : $isok;
		}
		
		// 第二列 执行单号
		if (empty ( $infos [1] )) {
			$errors [] = '第' . $line . '行，第2列【执行单号】不能为空';
			$isok = $isok ? FALSE : $isok;
		} else if (! self::validate_field_max_length ( $infos [1], 100 )) {
			$errors [] = '第' . $line . '行，第2列【执行单号】长度最多100个字符';
			$isok = $isok ? FALSE : $isok;
		}
		
		// 第三列 订单
		if (empty ( $infos [2] )) {
			$errors [] = '第' . $line . '行，第3列【订单】不能为空';
			$isok = $isok ? FALSE : $isok;
		} else if (! self::validate_field_max_length ( $infos [2], 100 )) {
			$errors [] = '第' . $line . '行，第3列【订单】长度最多100个字符';
			$isok = $isok ? FALSE : $isok;
		}
		
		// 第四列 广告
		if (empty ( $infos [3] )) {
			$errors [] = '第' . $line . '行，第4列【广告】不能为空';
			$isok = $isok ? FALSE : $isok;
		} else if (! self::validate_field_max_length ( $infos [3], 100 )) {
			$errors [] = '第' . $line . '行，第4列【广告】长度最多100个字符';
			$isok = $isok ? FALSE : $isok;
		}
		
		// 第五列 创意
		if (empty ( $infos [4] )) {
			$errors [] = '第' . $line . '行，第5列【创意】不能为空';
			$isok = $isok ? FALSE : $isok;
		} else if (! self::validate_field_max_length ( $infos [4], 100 )) {
			$errors [] = '第' . $line . '行，第5列【创意】长度最多100个字符';
			$isok = $isok ? FALSE : $isok;
		}
		
		// 第六列 网站
		if (empty ( $infos [5] )) {
			$errors [] = '第' . $line . '行，第6列【网站】不能为空';
			$isok = $isok ? FALSE : $isok;
		} else if (! self::validate_field_max_length ( $infos [5], 100 )) {
			$errors [] = '第' . $line . '行，第6列【创意】长度最多100个字符';
			$isok = $isok ? FALSE : $isok;
		}
		
		// 第七列 一级行业
		if (empty ( $infos [6] )) {
			$errors [] = '第' . $line . '行，第7列【一级行业】不能为空';
			$isok = $isok ? FALSE : $isok;
		} else if (! self::validate_field_max_length ( $infos [6], 100 )) {
			$errors [] = '第' . $line . '行，第7列【一级行业】长度最多100个字符';
			$isok = $isok ? FALSE : $isok;
		}
		
		// 第八列 二级行业
		if (empty ( $infos [7] )) {
			$errors [] = '第' . $line . '行，第8列【二级行业】不能为空';
			$isok = $isok ? FALSE : $isok;
		} else if (! self::validate_field_max_length ( $infos [7], 100 )) {
			$errors [] = '第' . $line . '行，第8列【二级行业】长度最多100个字符';
			$isok = $isok ? FALSE : $isok;
		}
		
		// 第九列 年月日
		if (! self::validate_field_not_null ( $infos [8] )) {
			$errors [] = '第' . $line . '行，第9列【年月日】不能为空';
			$isok = $isok ? FALSE : $isok;
		} else if (! self::validate_date_int ( PHPExcel_Shared_Date::ExcelToPHP ( $infos [8] ) )) {
			$errors [] = '第' . $line . '行，第9列【年月日】不是有效的时间值';
			$isok = $isok ? FALSE : $isok;
		}
		
		// 第十列~第三十三列 0点~24点的预算
		for($m = 9; $m < 33; $m ++) {
			if (! empty ( $infos [$m] ) && ! self::validate_money ( $infos [$m] )) {
				$errors [] = '第' . $line . '行，第' . ($m + 1) . '列【 ' . ($m - 9) . '】不是有效的金额值';
				$isok = $isok ? FALSE : $isok;
			}
		}
		
		return $isok;
	}
	public function import_media_schedule($pid, $filename) {
		$file = UPLOAD_FILE_PATH . $filename;
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
			// 应该是33列，行数>1
			if ($sum_cols_count !== 33) {
				$errors [] = '上传文件的列数非有效';
			} else if ($sum_rows_count <= 1) {
				$errors [] = '上传文件的行数非有效';
			} else {
				
				$budget = array ();
				for($x = 0; $x < 24; $x ++) {
					$budget [] = 'budget_' . $x;
				}
				for($i = 2; $i <= $sum_rows_count; $i ++) {
					for($j = 0; $j < $sum_cols_count; $j ++) {
						$infos [$i] [$j] = $sheet->getCellByColumnAndRow ( $j, $i )->getCalculatedValue ();
						$infos [$i] [$j] = $infos [$i] [$j] === NULL ? NULL : trim ( $infos [$i] [$j] );
					}
					$this->db->query ( 'BEGIN' );
					$isok = $this->_check_data_format ( $i, $infos [$i], $errors );
					if ($isok) {
						$db_ok = TRUE;
						
						$url = str_replace ( array (
								'http://',
								'https://' 
						), array (
								'' 
						), strtolower ( $infos [$i] [5] ) );
						
						$budget_sum = 0;
						$budget_str = array ();
						for($y = 9; $y < 33; $y ++) {
							$budget_str [] = empty ( $infos [$i] [$y] ) ? 0 : $infos [$i] [$y];
							$budget_sum += $infos [$i] [$y];
						}
						$result = $this->db->query ( 'INSERT INTO executive_media_schedule_content(dsp_platform,pid,dsp_order,dsp_adv,dsp_creative,dsp_website,dsp_industry_1,dsp_industry_2,schedule_date,' . implode ( ',', $budget ) . ',budget_sum,addtime,adduser) 
								VALUE("' . $infos [$i] [0] . '","' . $pid . '","' . $infos [$i] [2] . '","' . $infos [$i] [3] . '","' . $infos [$i] [4] . '","' . $url . '","' . $infos [$i] [6] . '","' . $infos [$i] [7] . '","' . date ( 'Y-m-d', PHPExcel_Shared_Date::ExcelToPHP ( $infos [$i] [8] ) ) . '",' . implode ( ',', $budget_str ) . ',' . $budget_sum . ',"' . date ( 'Y-m-d H:i:s', time () ) . '",' . $this->getUid () . ')' );
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
}