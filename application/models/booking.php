<?php
class Booking extends User {
	private $id;
	private $type;
	private $st;
	private $en;
	private $title;
	private $telmeeting;
	private $telmeeting_type;

	private $arr = array();

	private $errors = array();

	private static $type_arr = array('9A', '9B', '8A', '8B', '8C');

	public function __construct($fields = array()) {
		if (date_default_timezone_get() !== 'UTC') {
			date_default_timezone_set('UTC');
		}
		parent::__construct();
		if (!empty($fields)) {
			foreach ($this as $key => $value) {
				if ($fields[$key] !== NULL) {
					$this->$key = $fields[$key];
				}
			}
		}

		if ($this->type !== NULL && $this->st !== NULL && $this->en !== NULL) {
			$sql = 'SELECT * FROM booking_meetingroom WHERE type="'
					. $this->type . '" AND isok=1 AND start>=' . $this->st;
			if ($this->en > 0) {
				$sql .= ' AND end<' . $this->en;
			}
			$results = $this->db->get_results($sql);
			$arr = array();
			if ($results !== NULL) {
				foreach ($results as $result) {
					$arr[] = array('id' => $result->id,
							'start' => date('c', $result->start),
							'end' => date('c', $result->end),
							'title' => (empty($result->title) ? $this
											->_get_user_info($result->uid)
											. ' 预订 <br> '
											. $this
													->_get_telmeeting(
															intval(
																	$result
																			->telmeeting),
															intval(
																	$result
																			->telmeeting_type))
									: $this->_get_user_info($result->uid)
											. ' 预订 <br> 《' . $result->title
											. '》'
											. $this
													->_get_telmeeting(
															intval(
																	$result
																			->telmeeting),
															intval(
																	$result
																			->telmeeting_type))),
							'readOnly' => (intval($this->getUid())
									=== intval($result->uid)
									&& $result->end > time() ? FALSE : TRUE),
							'telmeeting' => (intval($result->telmeeting) === 1 ? TRUE
									: FALSE),
							'telmeeting_type' => intval(
									$result->telmeeting_type));
				}
			}
			$this->arr = $arr;
		}
	}

	/**
	 * @return the $type
	 */
	private function _get_type() {
		if (!in_array($this->type, self::$type_arr, TRUE)) {
			$this->type = '9A';
		}
		return $this->type;
	}

	private function _get_telmeeting($telmeeting, $telmeeting_type) {
		$s = '';
		if ($telmeeting !== 0 && $telmeeting_type !== 0) {
			if ($telmeeting === 1) {
				$s .= ($telmeeting_type === 1 ? '两方' : '多方') . '电话会议';
			}
		}
		return $s;
	}

	private function _get_user_info($uid) {
		$row = $this->db
				->get_row(
						'SELECT a.realname,b.depname,c.companyname FROM users a LEFT JOIN hr_department b ON a.dep = b.id LEFT JOIN hr_company c ON a.city = c.id WHERE a.uid='
								. intval($uid));
		if ($row !== NULL) {
			return $row->companyname . $row->depname . ' ' . $row->realname;
		}
		return '';
	}

	public function get_json() {
		return json_encode($this->arr);
	}

	private function _get_class_type() {
		$s = '<li [CLASS_9A]><a href="[BASE_URL]booking/?type=9A"><b>9F / A </b></a></li>
              <li [CLASS_9B]><a href="[BASE_URL]booking/?type=9B"><b>9F / B </b></a></li>
			  <li [CLASS_8A]><a href="[BASE_URL]booking/?type=8A"><b>8F / A </b></a></li>
              <li [CLASS_8B]><a href="[BASE_URL]booking/?type=8B"><b>8F / B </b></a></li>
              <li [CLASS_8C]><a href="[BASE_URL]booking/?type=8C"><b>8F / C </b></a></li>';
		$types = self::$type_arr;
		$search = array();
		$replace = array();
		foreach ($types as $type) {
			$search[] = '[CLASS_' . $type . ']';
			if ($type === $this->type) {
				$replace[] = 'class="on"';
			} else {
				$replace[] = '';
			}
		}
		return str_replace($search, $replace, $s);
	}

	private function validate_form_value($action) {
		$errors = array();
		if (in_array($action, array('add', 'del'), TRUE)) {
			if ($action === 'del') {
				$id = intval($this->id);
				if (!self::validate_id($id)) {
					$errors[] = '预定记录选择有误';
				}
			} else {
				if (strtotime($this->st) === FALSE) {
					$errors[] = '开始时间不是一个有效的时间值';
				} else if (strtotime($this->st) < $_SERVER['REQUEST_TIME']) {
					$errors[] = '开始时间必须等于或晚于现在的时间';
				}

				if (strtotime($this->en) === FALSE) {
					$errors[] = '结束时间不是一个有效的时间值';
				} else if (strtotime($this->en) < $_SERVER['REQUEST_TIME']) {
					$errors[] = '结束时间必须等于或晚于现在的时间';
				} else if (strtotime($this->st) - strtotime($this->en) >= 0) {
					$errors[] = '开始时间必须早于结束时间';
				}

				if (self::validate_field_not_empty($this->title)
						&& !self::validate_field_max_length($this->title, 500)) {
					$errors[] = '会议内容最多500个字符';
				}

				if (!in_array($this->type, self::$type_arr, TRUE)) {
					$errors[] = '会议室选择有误';
				}

				if (intval($this->telmeeting) === 1) {
					if (!in_array(intval($this->telmeeting_type), array(1, 2),
							TRUE)) {
						$errors[] = '电话会议类型选择有误';
					}
				}
			}
		} else {
			$errors[] = '无权限操作';
		}

		if (empty($errors)) {
			return TRUE;
		}
		$this->errors = $errors;
		unset($errors);
		return FALSE;
	}

	public function booking_add() {
		if ($this->validate_form_value('add')) {
			$success = TRUE;
			$error = '';
			$this->db->query('BEGIN');

			$starttime = strtotime($this->st . ' + 8 hours');
			$endtime = strtotime($this->en . ' + 8 hours');

			if ($this->type === '9B'
					&& $starttime >= strtotime('2013-10-08 00:00:00')
					&& $endtime <= strtotime('2013-10-15 23:59:59')) {
				$success = FALSE;
				$error = '9B会议室2013年10月8日～2013年10月15日暂不对外开放';
			} else {
				$today = sprintf('%s 00:00:00', date('Y-m-d', $starttime));
				$todaystart = strtotime($today);
				$todayend = $todaystart + 86400;

				$id = $this->db
						->get_var(
								'SELECT id FROM booking_meetingroom WHERE type="'
										. $this->type
										. '" AND isok=1 and start>='
										. $todaystart . ' AND end<' . $todayend
										. ' AND ( (start<' . $endtime
										. ' AND end >=' . $endtime
										. ') OR (end>' . $starttime
										. ' AND start<=' . $starttime
										. ') OR (start>=' . $starttime
										. ' AND end<=' . $endtime
										. ') OR (start<=' . $starttime
										. ' AND end>=' . $endtime
										. ')) FOR UPDATE');

				if ($id > 0) {
					$success = FALSE;
					$error = '您选择的时间段已被预订或有冲突，请重新选择';
				} else if (intval($this->telmeeting) === 1
						&& intval($this->telmeeting_type) === 1) { //同一时段只可以有3部两方电话会议系统使用
					$rows = $this->db
							->get_results(
									'SELECT id FROM booking_meetingroom WHERE telmeeting=1 AND telmeeting_type=1 AND isok=1 and start>='
											. $todaystart . ' AND end<'
											. $todayend . ' AND ( (start<'
											. $endtime . ' AND end >='
											. $endtime . ') OR (end>'
											. $starttime . ' AND start<='
											. $starttime . ') OR (start>='
											. $starttime . ' AND end<='
											. $endtime . ') OR (start<='
											. $starttime . ' AND end>='
											. $endtime . ')) FOR UPDATE');
					if (count($rows) >= 3) {
						$success = FALSE;
						$error = '该时段两方电话会议系统已被使用，请重新选择';
					}
				} else if (intval($this->telmeeting) === 1
						&& intval($this->telmeeting_type) === 2) { //同一时段只可以有1部多方电话会议系统使用
					$id = $this->db
							->get_var(
									'SELECT id FROM booking_meetingroom WHERE telmeeting=1 AND telmeeting_type=2 AND isok=1 and start>='
											. $todaystart . ' AND end<'
											. $todayend . ' AND ( (start<'
											. $endtime . ' AND end >='
											. $endtime . ') OR (end>'
											. $starttime . ' AND start<='
											. $starttime . ') OR (start>='
											. $starttime . ' AND end<='
											. $endtime . ') OR (start<='
											. $starttime . ' AND end>='
											. $endtime . ')) FOR UPDATE');
					if ($id > 0) {
						$success = FALSE;
						$error = '该时段多方电话会议系统已被使用，请重新选择';
					}
				}
			}

			if ($success) {
				$insert = Sql_Util::get_insert('booking_meetingroom',
						array('start' => $starttime, 'end' => $endtime,
								'title' => $this->title,
								'uid' => $this->getUid(), 'isok' => 1,
								'type' => $this->type,
								'telmeeting' => intval($this->telmeeting),
								'telmeeting_type' => intval(
										$this->telmeeting_type)));
				if ($insert['status'] === 'success') {
					$insert_result = $this->db->query($insert['sql']);
					if ($insert_result === FALSE) {
						$success = FALSE;
						$error = '会议室预定失败，请联系系统管理员';
					}
				} else {
					$success = FALSE;
					$error = '系统内部错误，请联系系统管理员';
				}
			}

			if ($success) {
				$this->db->query('COMMIT');
			} else {
				$this->db->query('ROLLBACK');
			}
			return $success ? '会议室预订成功' : $error;
		}
		$errors = $this->errors;
		return implode("\n", $errors);
	}

	public function booking_del() {
		if ($this->validate_form_value('del')) {
			$update_result = $this->db
					->query(
							'UPDATE booking_meetingroom SET isok=-1 WHERE id='
									. intval($this->id));
			if ($update_result === FALSE) {
				return '会议室预定取消失败，请联系系统管理员';
			} else {
				return '会议室预定取消成功';
			}
		}
		$errors = $this->errors;
		return implode("\n", $errors);
	}

	public function get_booking_index_html() {
		$buf = file_get_contents(TEMPLATE_PATH . 'booking/booking_index.tpl');
		return str_replace(
				array('[LEFT]', '[TOP]', '[TYPE]', '[CLASS_TYPE]', '[VCODE]',
						'[BASE_URL]'),
				array($this->get_left_html(), $this->get_top_html(),
						$this->_get_type(), $this->_get_class_type(),
						$this->get_vcode(), BASE_URL), $buf);
	}
}
