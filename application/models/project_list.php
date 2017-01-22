<?php
class Project_List extends User {
	private $page;
	private $all_count;
	private $page_count;
	const LIMIT = 50;
	public function __construct($fields = array()) {
		parent::__construct ();
		if (! empty ( $fields )) {
			foreach ( $this as $key => $value ) {
				if ($fields [$key] !== NULL && ! in_array ( $key, array (
						'all_count',
						'page_count' 
				), TRUE )) {
					$this->$key = $fields [$key];
				}
			}
		}
	}
	private static function _getStatus($status) {
		switch ($status) {
			case 0 :
				return '等待审核';
			case 1 :
				return '审核通过';
			case - 1 :
				return '审核驳回';
			case - 2 :
				return '已取消';
			default :
				return '';
		}
	}
	private static function _getAction($status, $id) {
		$c = $status === -2 ? '' : '<a href="javascript:cancel(\'' . $id . '\');">取消</a>';
		if ($status === 1 || $status === - 1) {
			$c = '<a href="' . BASE_URL . 'project/?o=edit&id=' . $id . '">修改</a>&nbsp;|&nbsp;' . $c;
		}
		return $c;
	}
	private function _get_pagination($is_prev) {
		return '<a href="' . BASE_URL . 'project/?o=mylist&page=' . ($is_prev ? intval ( $this->page ) - 1 : intval ( $this->page ) + 1) . '">' . ($is_prev ? '上一页' : '下一页') . '</a>';
	}
	private function _getPrev() {
		if (intval ( $this->page ) === 1) {
			return '';
		} else {
			return $this->_get_pagination ( TRUE );
		}
	}
	private function _getNext() {
		if (intval ( $this->page ) >= intval ( $this->page_count )) {
			return '';
		} else {
			return $this->_get_pagination ( FALSE );
		}
	}
	public function getMyProjectListHtml() {
		$this->all_count = intval ( $this->db->get_var ( 'SELECT COUNT(*) FROM bd_project WHERE userid=' . $this->getUid () ) );
		$this->page_count = ceil ( $this->all_count / self::LIMIT );
		$start = self::LIMIT * intval ( $this->page ) - self::LIMIT;
		if ($start < 0) {
			$start = 0;
		}
		
		$results = $this->db->get_results ( 'SELECT id,project_name,remark,addtime,status FROM bd_project WHERE userid=' . $this->getUid () . ' ORDER BY addtime DESC LIMIT ' . $start . ',' . self::LIMIT );
		$s = '';
		if ($results !== NULL) {
			foreach ( $results as $key => $result ) {
				$s .= '<tr><td>' . (($this->page - 1) * self::LIMIT + $key + 1) . '</td>
	                    <td>' . $result->project_name . '</td>
	                    <td>' . $result->remark . '</td>
	                    <td>' . $result->addtime . '</td>
	                    <td>' . self::_getStatus ( intval ( $result->status ) ) . '</td>
	                    <td>' . self::_getAction ( intval ( $result->status ), intval ( $result->id ) ) . '</td></tr>';
			}
		}
		return str_replace ( array (
				'[LEFT]',
				'[TOP]',
				'[VCODE]',
				'[PROJECTLIST]',
				'[ALLCOUNTS]',
				'[COUNTS]',
				'[PREV]',
				'[NEXT]',
				'[BASE_URL]' 
		), array (
				$this->get_left_html (),
				$this->get_top_html (),
				$this->get_vcode (),
				$s,
				$this->all_count,
				$this->page . '	/' . $this->page_count . ' 页 &nbsp;&nbsp;',
				$this->_getPrev (),
				$this->_getNext (),
				BASE_URL 
		), file_get_contents ( TEMPLATE_PATH . 'project/project_mylist.tpl' ) );
	}
}