<?php
class Media extends User {
	private $media_id;

	private $name;
	private $cname;
	private $ename;
	private $url;
	private $bankinfo;
	private $person;
	private $contact;
	private $dailiinfo;
	private $zcinfo;
	private $payinfo;
	private $sendinfo;
	private $cidinfo;
	private $discount;
	private $other;

	private $errors;
	private $has_media_permission = FALSE;

	/**
	 * @return the $media_id
	 */
	public function getMedia_id() {
		return $this->media_id;
	}

	/**
	 * @return the $name
	 */
	public function getName() {
		return $this->name;
	}

	/**
	 * @return the $cname
	 */
	public function getCname() {
		return $this->cname;
	}

	/**
	 * @return the $ename
	 */
	public function getEname() {
		return $this->ename;
	}

	/**
	 * @return the $url
	 */
	public function getUrl() {
		return $this->url;
	}

	/**
	 * @return the $bankinfo
	 */
	public function getBankinfo() {
		return $this->bankinfo;
	}

	/**
	 * @return the $person
	 */
	public function getPerson() {
		return $this->person;
	}

	/**
	 * @return the $contact
	 */
	public function getContact() {
		return $this->contact;
	}

	/**
	 * @return the $dailiinfo
	 */
	public function getDailiinfo() {
		return $this->dailiinfo;
	}

	/**
	 * @return the $zcinfo
	 */
	public function getZcinfo() {
		return $this->zcinfo;
	}

	/**
	 * @return the $payinfo
	 */
	public function getPayinfo() {
		return $this->payinfo;
	}

	/**
	 * @return the $sendinfo
	 */
	public function getSendinfo() {
		return $this->sendinfo;
	}

	/**
	 * @return the $cidinfo
	 */
	public function getCidinfo() {
		return $this->cidinfo;
	}

	/**
	 * @return the $discount
	 */
	public function getDiscount() {
		return $this->discount;
	}

	/**
	 * @return the $other
	 */
	public function getOther() {
		return $this->other;
	}

	/**
	 * @return the $has_media_permission
	 */
	public function getHas_media_permission() {
		return $this->has_media_permission;
	}

	public function __construct($media_id = NULL, $fields = array()) {
		parent::__construct();
		if (in_array($this->getUsername(),
				$GLOBALS['manager_media_data_permission'], TRUE)
				|| intval($this->getBelong_dep()) === 4) {
			$this->has_media_permission = TRUE;
		}
		if ($this->has_media_permission) {
			if ($media_id !== NULL) {
				$row = $this->db
						->get_row(
								'SELECT * FROM media_library WHERE id='
										. intval($media_id));
				if ($row !== NULL) {
					$this->media_id = intval($media_id);
					$this->name = $row->name;
					$this->cname = $row->cname;
					$this->ename = $row->ename;
					$this->url = $row->url;
					$this->bankinfo = $row->bankinfo;
					$this->person = $row->person;
					$this->contact = $row->contact;
					$this->dailiinfo = $row->dailiinfo;
					$this->zcinfo = $row->zcinfo;
					$this->payinfo = $row->payinfo;
					$this->sendinfo = $row->sendinfo;
					$this->cidinfo = $row->cidinfo;
					$this->discount = $row->discount;
					$this->other = $row->other;
				}
			} else if (!empty($fields)) {
				foreach ($this as $key => $value) {
					if ($fields[$key] !== NULL
							&& !in_array($key, array('has_media_permission'),
									TRUE)) {
						$this->$key = $fields[$key];
					}
				}
			}
		}
	}

	private function validate_form_value($action) {
		$errors = array();
		if (in_array($action, array('add', 'update'), TRUE)) {
			if ($this->has_media_permission) {
				if (!self::validate_field_not_empty($this->name)
						|| !self::validate_field_not_null($this->name)) {
					$errors[] = '媒体全称不能为空';
				} else if (!self::validate_field_max_length($this->name, 200)) {
					$errors[] = '媒体全称长度最多200个字符';
				}

				if (!self::validate_field_not_empty($this->url)
						|| !self::validate_field_not_null($this->url)) {
					$errors[] = 'URL不能为空';
				} else if (!Validate_Util::my_is_url($this->url)) {
					$errors[] = 'URL输入有误';
				} else if (!self::validate_field_max_length($this->url, 500)) {
					$errors[] = 'URL长度最多500个字符';
				}

				if (self::validate_field_not_empty($this->cname)
						&& !self::validate_field_max_length($this->cname, 200)) {
					$errors[] = '媒体中文简称长度最多200个字符';
				}

				if (self::validate_field_not_empty($this->ename)
						&& !self::validate_field_max_length($this->ename, 200)) {
					$errors[] = '媒体英文简称长度最多200个字符';
				}

				if (self::validate_field_not_empty($this->bankinfo)
						&& !self::validate_field_max_length($this->bankinfo,
								500)) {
					$errors[] = '银行账号信息长度最多500个字符';
				}

				if (self::validate_field_not_empty($this->person)
						&& !self::validate_field_max_length($this->person, 500)) {
					$errors[] = '媒体联络人长度最多500个字符';
				}

				if (self::validate_field_not_empty($this->contact)
						&& !self::validate_field_max_length($this->contact, 500)) {
					$errors[] = '联系方式长度最多500个字符';
				}

				if (self::validate_field_not_empty($this->dailiinfo)
						&& !self::validate_field_max_length($this->dailiinfo,
								500)) {
					$errors[] = '代理商资质信息长度最多500个字符';
				}

				if (self::validate_field_not_empty($this->zcinfo)
						&& !self::validate_field_max_length($this->zcinfo, 500)) {
					$errors[] = '政策信息长度最多500个字符';
				}

				if (self::validate_field_not_empty($this->payinfo)
						&& !self::validate_field_max_length($this->payinfo, 500)) {
					$errors[] = '账期/付款规则长度最多500个字符';
				}

				if (self::validate_field_not_empty($this->sendinfo)
						&& !self::validate_field_max_length($this->sendinfo,
								500)) {
					$errors[] = '配送信息长度最多500个字符';
				}

				if (self::validate_field_not_empty($this->cidinfo)
						&& !self::validate_field_max_length($this->cidinfo, 500)) {
					$errors[] = '框架合同长度最多500个字符';
				}

				if (self::validate_field_not_empty($this->discount)
						&& !self::validate_field_max_length($this->discount,
								500)) {
					$errors[] = '折扣长度最多500个字符';
				}

				if (self::validate_field_not_empty($this->other)
						&& !self::validate_field_max_length($this->other, 500)) {
					$errors[] = '其他信息长度最多500个字符';
				}

				if ($action === 'update') {
					if (!self::validate_id(intval($this->media_id))) {
						$errors[] = '媒体选择有误';
					}
				}
			} else {
				$errors[] = '无权限操作';
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

	public function add_media() {
		if ($this->validate_form_value('add')) {
			$field_value_array = array('name' => $this->name,
					'url' => $this->url, 'time' => $_SERVER['REQUEST_TIME'],
					'cname' => $this->cname, 'ename' => $this->ename,
					'bankinfo' => $this->bankinfo, 'person' => $this->person,
					'contact' => $this->contact,
					'dailiinfo' => $this->dailiinfo, 'zcinfo' => $this->zcinfo,
					'payinfo' => $this->payinfo, 'sendinfo' => $this->sendinfo,
					'cidinfo' => $this->cidinfo, 'discount' => $this->discount,
					'other' => $this->other);
			$query = Sql_Util::get_insert('media_library', $field_value_array);
			if ($query['status'] === 'success') {
				$insert_result = $this->db->query($query['sql']);
				if ($insert_result === FALSE) {
					return array('status' => 'error',
							'message' => '新建媒体信息失败，请联系管理员');
				}
			} else {
				return array('status' => 'error',
						'message' => '新建媒体信息失败，内部错误1，请联系管理员');
			}
			return array('status' => 'success', 'message' => '新建媒体信息成功');
		}
		return array('status' => 'error', 'message' => $this->errors);
	}

	public function update_media() {
		if ($this->validate_form_value('update')) {
			$query = Sql_Util::get_update('media_library',
					array('name' => $this->name, 'url' => $this->url,
							'cname' => $this->cname, 'ename' => $this->ename,
							'bankinfo' => $this->bankinfo,
							'person' => $this->person,
							'contact' => $this->contact,
							'dailiinfo' => $this->dailiinfo,
							'zcinfo' => $this->zcinfo,
							'payinfo' => $this->payinfo,
							'sendinfo' => $this->sendinfo,
							'cidinfo' => $this->cidinfo,
							'discount' => $this->discount,
							'other' => $this->other),
					array('id' => array('=', intval($this->media_id))), 'AND');
			if ($query['status'] === 'success') {
				$update_result = $this->db->query($query['sql']);
				if ($update_result === FALSE) {
					return array('status' => 'error',
							'message' => '更新媒体信息失败，请联系管理员');
				}
			} else {
				return array('status' => 'error',
						'message' => '更新媒体信息失败，内部错误1，请联系管理员');
			}
			return array('status' => 'success', 'message' => '更新媒体信息成功');
		}
		return array('status' => 'error', 'message' => $this->errors);
	}

	public function get_media_add_html() {
		if ($this->getHas_media_permission()) {
			$buf = file_get_contents(
					TEMPLATE_PATH . 'media/medialibrary/media_add.tpl');
			return str_replace(
					array('[LEFT]', '[TOP]', '[VCODE]', '[BASE_URL]'),
					array($this->get_left_html(), $this->get_top_html(),
							$this->get_vcode(), BASE_URL), $buf);
		} else {
			return User::no_permission();
		}
	}

	public function get_media_edit_html() {
		if ($this->getHas_media_permission()) {
			$buf = file_get_contents(
					TEMPLATE_PATH . 'media/medialibrary/media_edit.tpl');
			return str_replace(
					array('[LEFT]', '[TOP]', '[VCODE]', '[NAME]', '[CNAME]',
							'[ENAME]', '[URL]', '[BANKINFO]', '[PERSON]',
							'[CONTACT]', '[DAILIINFO]', '[ZCINFO]',
							'[PAYINFO]', '[CIDINFO]', '[SENDINFO]',
							'[DISCOUNT]', '[OTHER]', '[MEDIAID]', '[BASE_URL]'),
					array($this->get_left_html(), $this->get_top_html(),
							$this->get_vcode(), $this->name, $this->cname,
							$this->ename, $this->url, $this->bankinfo,
							$this->person, $this->contact, $this->dailiinfo,
							$this->zcinfo, $this->payinfo, $this->cidinfo,
							$this->sendinfo, $this->discount, $this->other,
							$this->media_id, BASE_URL), $buf);
		} else {
			return User::no_permission();
		}
	}

	public function get_media_info_html() {
		if ($this->getHas_media_permission()) {
			$buf = file_get_contents(
					TEMPLATE_PATH . 'media/medialibrary/media_info.tpl');
			return str_replace(
					array('[LEFT]', '[TOP]', '[NAME]', '[CNAME]', '[ENAME]',
							'[URL]', '[BANKINFO]', '[PERSON]', '[CONTACT]',
							'[DAILIINFO]', '[ZCINFO]', '[PAYINFO]',
							'[CIDINFO]', '[SENDINFO]', '[DISCOUNT]', '[OTHER]',
							'[BASE_URL]'),
					array($this->get_left_html(), $this->get_top_html(),
							$this->name, $this->cname, $this->ename,
							$this->url, $this->bankinfo, $this->person,
							$this->contact, $this->dailiinfo, $this->zcinfo,
							$this->payinfo, $this->cidinfo, $this->sendinfo,
							$this->discount, $this->other, BASE_URL), $buf);
		} else {
			return User::no_permission();
		}
	}
}