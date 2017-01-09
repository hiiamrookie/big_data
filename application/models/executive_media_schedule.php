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
		
		$sql = 'SELECT m.*,n.cusname,o.media_schedule_filename
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
				LEFT JOIN executive_media_schedule o
				ON m.pid=o.pid';
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
						'media_schedule_filename' => $result->media_schedule_filename 
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
				$result .= '<tr><td>' . Executive_List::get_executive_type ( intval ( $value ['isalter'] ) ) . '</td><td>' . $value ['pid'] . '</td><td>' . $value ['cusname'] . '</td><td>' . Executive_List::get_executive_name_link ( $value ['id'], $value ['pid'], $value ['name'] ) . '</td><td>' . Executive_List::get_executive_amount ( $value ['amount'] ) . '</td><td>' . Executive_List::get_executive_cost ( $value ['allcost'], intval ( $value ['isyg'] ) ) . '</td><td>' . self::_has_media_schedule ( $value ['media_schedule_filename'] ) . '</td><td>' . self::_get_action ( $value ['pid'], $value ['media_schedule_filename'] ) . '</td></tr>';
			}
		}
		return $result;
	}
	private static function _has_media_schedule($media_schedule_filename) {
		return empty ( $media_schedule_filename ) ? '<font color="#ff6600"><b>未上传</b></font>' : '<font color="#66cc00"><b>已上传</b></font>';
	}
	private static function _get_action($pid, $media_schedule_filename) {
		return empty ( $media_schedule_filename ) ? '<a href="' . BASE_URL . 'executive/?o=upload_media_schedule&pid=' . $pid . '">上传</a>' : '查看';
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
}