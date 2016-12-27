<?php
class Media_List extends User {
	private $page;
	private $all_count;
	private $page_count;
	const LIMIT = 50;

	private $medias = array();
	private $has_media_permission = FALSE;

	/**
	 * @return the $all_count
	 */
	public function getAll_count() {
		return $this->all_count;
	}

	/**
	 * @return the $has_media_permission
	 */
	public function getHas_media_permission() {
		return $this->has_media_permission;
	}

	public function __construct($fields) {
		parent::__construct();
		if (in_array($this->getUsername(),
				$GLOBALS['manager_media_data_permission'], TRUE)
				|| intval($this->getBelong_dep()) === 4) {
			$this->has_media_permission = TRUE;
		}
		if ($this->has_media_permission) {
			foreach ($this as $key => $value) {
				if ($fields[$key] !== NULL
						&& !in_array($key, array('has_media_permission'), TRUE)) {
					$this->$key = $fields[$key];
				}
			}
			$this->_get_media_list_datas();
		}
	}

	private function _get_media_list_datas() {
		$this->all_count = intval(
				$this->db->get_var('SELECT COUNT(*) FROM media_library'));
		$this->page_count = ceil($this->all_count / self::LIMIT);
		$start = self::LIMIT * intval($this->page) - self::LIMIT;
		if ($start < 0) {
			$start = 0;
		}

		$results = array();
		$medias = $this->db
				->get_results(
						'SELECT * FROM media_library ORDER BY time DESC LIMIT '
								. $start . ',' . self::LIMIT);
		if ($medias !== NULL) {
			foreach ($medias as $media) {
				$results[] = array('id' => $media->id, 'name' => $media->name,
						'cname' => $media->cname, 'ename' => $media->ename,
						'url' => $media->url, 'person' => $media->person,
						'contact' => $media->contact,
						'daili' => $media->dailiinfo);
			}
		}
		$this->medias = $results;
	}

	private function _get_list_html() {
		$s = '';
		if (!empty($this->medias)) {
			$medias = $this->medias;
			foreach ($medias as $key => $media) {
				$s .= '<tr><td>'
						. ((intval($this->page) - 1) * self::LIMIT + $key + 1)
						. '</td><td>' . $media['name'] . '</td><td>'
						. $media['cname'] . '</td><td>' . $media['ename']
						. '</td><td>' . $media['url'] . '</td><td>'
						. $media['person'] . '</td><td>' . $media['contact']
						. '</td><td>' . $media['daili'] . '</td><td>'
						. self::_get_media_action(intval($media['id']))
						. '</td></tr>';
			}
			unset($medias);
		}
		return $s;
	}

	private static function _get_media_action($id) {
		return '<a href="' . BASE_URL . 'media/medialibrary/?o=mtedit&id='
				. $id . '">修改</a> | <a href="' . BASE_URL
				. 'media/medialibrary/?o=mtinfo&id=' . $id . '">查看<a>';
	}

	private function _get_pagination($is_prev) {
		return '<a href="' . BASE_URL . 'media/medialibrary/?o=mtlist&page='
				. ($is_prev ? intval($this->page) - 1 : intval($this->page) + 1)
				. '">' . ($is_prev ? '上一页' : '下一页') . '</a>';
	}

	public function get_media_counts() {
		return $this->page . '	/' . $this->page_count . ' 页 &nbsp;&nbsp;';
	}

	public function getPrev() {
		if (intval($this->page) === 1) {
			return '';
		} else {
			return $this->_get_pagination(TRUE);
		}
	}

	public function getNext() {
		if (intval($this->page) >= intval($this->page_count)) {
			return '';
		} else {
			return $this->_get_pagination(FALSE);
		}
	}

	public function get_media_list_html() {
		if ($this->getHas_media_permission()) {
			$buf = file_get_contents(
					TEMPLATE_PATH . 'media/medialibrary/media_list.tpl');
			return str_replace(
					array('[LEFT]', '[TOP]', '[MEDIALIST]', '[ALLCOUNTS]',
							'[COUNTS]', '[NEXT]', '[PREV]', '[BASE_URL]'),
					array($this->get_left_html(), $this->get_top_html(),
							$this->_get_list_html(), $this->all_count,
							$this->get_media_counts(), $this->getNext(),
							$this->getPrev(), BASE_URL), $buf);
		} else {
			return User::no_permission();
		}
	}
}