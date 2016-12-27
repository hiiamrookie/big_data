<?php
class Tec_Project extends User {
	private $has_tec_project_permission = FALSE;
	private $errors = array();

	private $id;
	private $pid;
	private $project_name;
	private $project_type;
	private $cycle;
	private $traffic;
	private $dids;
	private $project_background;
	private $prequirement_array = array();

	private $project_id;
	private $requirementids;
	private $tpid;

	private $tec_records = array();

	public function __construct($fields = array()) {
		parent::__construct();
		if (intval($this->getBelong_dep()) === 6) {
			//技术部
			$this->has_tec_project_permission = TRUE;
		}

		if ($fields['id'] !== NULL && $fields['tpid'] === NULL) {
			$row = $this->db
					->get_row(
							'SELECT project_id,pid,project_name,project_type,cycle,traffic,attachment,project_background,requirementids FROM tec_project WHERE id='
									. intval($fields['id']));
			if ($row !== NULL) {
				$this->project_id = $row->project_id;
				$this->pid = !empty($row->pid) ? $row->pid . '~'
								. $row->project_name : '';
				$this->project_name = $row->project_name;
				$this->project_type = $row->project_type;
				$this->cycle = $row->cycle;
				$this->traffic = $row->traffic;
				$this->dids = $row->attachment;
				$this->project_background = $row->project_background;
				$this->id = intval($fields['id']);
				$this->requirementids = $row->requirementids;
			} else {
				$this->id == NULL;
			}
		} else if (!empty($fields)) {
			foreach ($this as $key => $value) {
				if ($fields[$key] !== NULL
						&& !in_array($key, array('has_tec_project_permission'),
								TRUE)) {
					$this->$key = $fields[$key];
				}
			}
		}
		if ($fields['id'] !== NULL && $fields['tpid'] !== NULL) {
			$this->_get_tec_project_records();
		}
	}

	public function get_tec_project_add_html() {
		if ($this->has_tec_project_permission) {
			$buf = file_get_contents(TEMPLATE_PATH . 'tec/tec_project_add.tpl');
			return str_replace(
					array('[LEFT]', '[TOP]', '[VCODE]', '[VALIDATE_TYPE]',
							'[VALIDATE_SIZE]', '[BASE_URL]'),
					array($this->get_left_html(), $this->get_top_html(),
							$this->get_vcode(),
							implode(',',
									$GLOBALS['defined_upload_validate_type']),
							UPLOAD_FILE_MAX_SIZE / (1024 * 1024), BASE_URL),
					$buf);
		}
		return User::no_permission();
	}

	private function validate_form_value($action) {
		$errors = array();
		if ($this->has_tec_project_permission
				&& in_array($action, array('add', 'update'), TRUE)) {

			//关联执行单
			if (self::validate_field_not_empty($this->pid)) {
				$pid = explode('~', $this->pid);
				$id = $this->db
						->get_row(
								'SELECT id FROM executive where pid="'
										. $pid[0] . '" AND isok<>-1');
				if ($id <= 0) {
					$errors[] = '关联的执行单不存在或非正常状态';
				} else {
					$this->pid = $pid[0];
				}
			}

			//项目名称
			if (!self::validate_field_not_empty($this->project_name)
					|| !self::validate_field_not_null($this->project_name)) {
				$errors[] = '项目名称不能为空';
			} else if (!self::validate_field_max_length($this->project_name,
					200)) {
				$errors[] = '项目名称长度最多200个字符';
			}

			//项目分类
			if (!in_array(intval($this->project_type), array(1, 2), TRUE)) {
				$errors[] = '项目分类选择有误';
			}

			//项目开发周期
			if (!self::validate_field_not_empty($this->cycle)
					|| !self::validate_field_not_null($this->cycle)) {
				$errors[] = '项目开发周期不能为空';
			} else if (!self::validate_field_max_length($this->cycle, 1000)) {
				$errors[] = '项目开发周期最多1000个字符';
			}

			//流量预估
			if (self::validate_field_not_null($this->traffic)
					&& !self::validate_field_max_length($this->traffic, 1000)) {
				$errors[] = '网站流量预估最多1000个字符';
			}

			//附件
			if (!String_Util::start_with($this->dids, '^')
					|| !String_Util::end_with($this->dids, '^')) {
				$errors[] = '项目附件有误';
			} else if (!self::validate_field_max_length($this->dids, 1000)) {
				$errors[] = '项目附件选择过多';
			} else {
				$dids = $this->dids;
				$this->dids = substr($dids, 1, strlen($dids) - 2);
			}

			//项目背景
			if (!self::validate_field_not_empty($this->project_background)
					|| !self::validate_field_not_null($this->project_background)) {
				$errors[] = '项目背景不能为空';
			} else if (!self::validate_field_max_length(
					$this->project_background, 1000)) {
				$errors[] = '项目背景最多1000个字符';
			}

			//项目需求
			$prequirement_array = $this->prequirement_array;
			if (empty($prequirement_array)) {
				$errors[] = '至少需要填写1个需求';
			} else {
				foreach ($prequirement_array as $key => $prequirement) {
					if (!self::validate_field_not_empty(
							$prequirement['requirement'])
							|| !self::validate_field_not_null(
									$prequirement['requirement'])) {
						$errors[] = '第' . ($key + 1) . '条【需求】不能为空 ';
					} else if (!self::validate_field_max_length(
							$prequirement['requirement'], 1000)) {
						$errors[] = '第' . ($key + 1) . '条【需求】最多1000个字符 ';
					}
				}
			}

			if ($action === 'update') {
				$row = $this->db
						->get_row(
								'SELECT id FROM tec_project WHERE id='
										. intval($this->tpid)
										. ' AND project_id="'
										. $this->project_id . '"');
				if ($row === NULL) {
					$errors[] = '项目选择有误';
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

	public function add_tec_project() {
		if ($this->validate_form_value('add')) {
			$success = TRUE;
			$error = '';
			$this->db->query('BEGIN');

			//获得sequence
			$project_id = $this
					->getSequence(
							date('y', time()) . $this->getCity_show()
									. date('m', time()) . 'TEC');

			if ($project_id === FALSE) {
				$success = FALSE;
				$error = '生成单号出错';
			} else {
				$insert_result = $this->db
						->query(
								'INSERT INTO tec_project(project_id,pid,project_name,project_type,cycle,traffic,attachment,project_background,addtime,userid,isalter,isok,isresponse) VALUE("'
										. $project_id . '","' . $this->pid
										. '","' . $this->project_name . '",'
										. intval($this->project_type) . ',"'
										. $this->cycle . '","' . $this->traffic
										. '","' . $this->dids . '","'
										. $this->project_background
										. '",now(),' . $this->getUid()
										. ',0,1,0)');
				if ($insert_result === FALSE) {
					$success = FALSE;
					$error = '插入项目记录出错';
				} else {
					$tp_id = $this->db->insert_id;
					$prequirement_array = $this->prequirement_array;

					$ids = array();
					foreach ($prequirement_array as $key => $value) {
						$insert_result = $this->db
								->query(
										'INSERT INTO tec_project_requirement(tp_id,project_id,requirement) VALUE ('
												. $tp_id . ',"' . $project_id
												. '","' . $value['requirement']
												. '")');
						if ($insert_result === FALSE) {
							$success = FALSE;
							$error = '插入项目需求出错';
							break;
						} else {
							$ids[] = $this->db->insert_id;
						}
					}

					if (!empty($ids)) {
						//更新requirementids
						$update_result = $this->db
								->query(
										'UPDATE tec_project SET requirementids="'
												. implode('^', $ids)
												. '" WHERE id=' . $tp_id);
						if ($update_result === FALSE) {
							$success = FALSE;
							$error = '关联项目需求出错';
						}
					}
				}
			}

			if ($success) {
				$this->db->query('COMMIT');

				//发邮件给项目负责人
				$subject = '新增项目需求提醒邮件';
				$body = '项目需求已新增，详情请访问：' . BASE_URL
						. 'tec/?o=projectresponse&id=' . $tp_id
						. '&project_id=' . $project_id;

				$mail = new PHPMailer();
				$body = eregi_replace('[\]', '', $body); //对邮件内容进行必要的过滤
				$mail->CharSet = 'UTF-8'; //设定邮件编码，默认ISO-8859-1，如果发中文此项必须设置，否则乱码
				$mail->IsSMTP(); // 设定使用SMTP服务
				$mail->SMTPDebug = 1; // 启用SMTP调试功能
				// 1 = errors and messages
				// 2 = messages only
				$mail->SMTPAuth = true; // 启用 SMTP 验证功能
				//$mail->SMTPSecure = "ssl";          // 安全协议
				$mail->Host = 'smtp.nimdigital.com'; // SMTP 服务器
				$mail->Port = 25; // SMTP服务器的端口号
				$mail->Username = 'info@nimdigital.com'; // SMTP服务器用户名
				$mail->Password = '@@nim.com1'; // SMTP服务器密码
				$mail->SetFrom('info@nimdigital.com', '网迈 OA');
				$mail->AddReplyTo('info@nimdigital.com', '网迈 OA');
				$mail->Subject = $subject;
				$mail->MsgHTML($body);
				$mail->AddAddress('jesse.shen@nimdigital.com', '沈蔚乐');
				$mail->AddCC('skystar.qi@nimdigital.com', '祁洋阳');

				//if ($mail->Send()) {
					return array('status' => 'success', 'message' => '新建项目需求成功');
				//} else {
				//	return array('status' => 'success',
				//			'message' => '新建项目需求成功，但给项目负责人发送邮件失败，请联系系统管理员');
				//}
			} else {
				$this->db->query('ROLLBACK');
				return array('status' => 'error', 'message' => $error);
			}
			//return array('status' => $success ? 'success' : 'error',
			//		'message' => $success ? '新建项目需求成功' : $error);
		}
		return array('status' => 'error', 'message' => $this->errors);
	}

	private function _get_project_type_js() {
		return '$("#project_type").val("' . $this->project_type . '");';
	}

	private function _get_dids_value() {
		$did_value = $this->dids;
		if (!empty($did_value)) {
			$did_value = '^' . $did_value . '^';
		} else {
			$did_value = '^';
		}
		return $did_value;
	}

	private function _get_project_requirement() {
		$ids = explode('^', $this->requirementids);
		$s = '<input type="hidden" name="prequirement" id="prequirement" value="[PREQUIREMENTCOUNT]"/><br/><div id="prequirementlist">';

		$count = 1;
		$count_array = array();
		foreach ($ids as $id) {
			if (intval($id) > 0) {
				$row = $this->db
						->get_row(
								'SELECT requirement FROM tec_project_requirement WHERE id='
										. intval($id));
				if ($row !== NULL) {
					$s .= '<div>';
					$s .= '<input type="text" style="width:500px;" class="validate[required,maxSize[1000]] text" name="prequirement_'
							. $count . '" id="prequirement_' . $count
							. '" value="' . $row->requirement
							. '">&nbsp;&nbsp;';
					$s .= '<img src="' . BASE_URL
							. 'images/close.png" onclick="delprequirement(this,'
							. $count
							. ')" width="17" height="17" /><br /></div>';
					$count_array[] = $count;
					$count++;
				}
			}
		}
		$s .= '</div><script>var prequirement_count = ' . $count . ';</script>';
		return str_replace(array('[PREQUIREMENTCOUNT]'),
				array(
						empty($count_array) ? ','
								: ',' . implode(',', $count_array) . ','), $s);
	}

	public function get_tec_project_edit_html() {
		if ($this->has_tec_project_permission) {
			if ($this->id !== NULL) {
				$buf = file_get_contents(
						TEMPLATE_PATH . 'tec/tec_project_edit.tpl');
				return str_replace(
						array('[LEFT]', '[TOP]', '[VCODE]', '[PID]',
								'[PROJECTNAME]', '[PROJECTTYPEJS]', '[CYCLE]',
								'[TRAFFIC]', '[DIDS]', '[DIDSVALUE]',
								'[PROJECTBACKGROUND]', '[PROJECTREQUIREMENT]',
								'[PROJECTID]', '[TPID]', '[VALIDATE_TYPE]',
								'[VALIDATE_SIZE]', '[BASE_URL]'),
						array($this->get_left_html(), $this->get_top_html(),
								$this->get_vcode(), $this->pid,
								$this->project_name,
								$this->_get_project_type_js(), $this->cycle,
								$this->traffic,
								$this->get_upload_files($this->dids, TRUE),
								$this->_get_dids_value(),
								$this->project_background,
								$this->_get_project_requirement(),
								$this->project_id, $this->id,
								implode(',',
										$GLOBALS['defined_upload_validate_type']),
								UPLOAD_FILE_MAX_SIZE / (1024 * 1024), BASE_URL),
						$buf);
			}
			return User::no_object('没有该项目');
		}
		return User::no_permission();
	}

	public function update_tec_project() {
		if ($this->validate_form_value('update')) {
			$success = TRUE;
			$error = '';
			$this->db->query('BEGIN');

			$isalter = $this->db
					->get_var(
							'SELECT MAX(isalter) FROM tec_project WHERE project_id="'
									. $this->project_id
									. '" AND isok=1 FOR UPDATE');
			if ($isalter !== NULL) {
				$insert_result = $this->db
						->query(
								'INSERT INTO tec_project(project_id,pid,project_name,project_type,cycle,traffic,attachment,project_background,addtime,userid,isalter,isok,isresponse) VALUE("'
										. $this->project_id . '","'
										. $this->pid . '","'
										. $this->project_name . '",'
										. intval($this->project_type) . ',"'
										. $this->cycle . '","' . $this->traffic
										. '","' . $this->dids . '","'
										. $this->project_background
										. '",now(),' . $this->getUid() . ','
										. (intval($isalter) + 1) . ',1,0)');
				if ($insert_result === FALSE) {
					$success = FALSE;
					$error = '插入项目记录出错';
				} else {
					$tp_id = $this->db->insert_id;
					$prequirement_array = $this->prequirement_array;

					$ids = array();
					foreach ($prequirement_array as $key => $value) {
						$insert_result = $this->db
								->query(
										'INSERT INTO tec_project_requirement(tp_id,project_id,requirement) VALUE ('
												. $tp_id . ',"'
												. $this->project_id . '","'
												. $value['requirement'] . '")');
						if ($insert_result === FALSE) {
							$success = FALSE;
							$error = '插入项目需求出错';
							break;
						} else {
							$ids[] = $this->db->insert_id;
						}
					}

					if (!empty($ids)) {
						//更新requirementids
						$update_result = $this->db
								->query(
										'UPDATE tec_project SET requirementids="'
												. implode('^', $ids)
												. '" WHERE id=' . $tp_id);
						if ($update_result === FALSE) {
							$success = FALSE;
							$error = '关联项目需求出错';
						}
					}
				}
			} else {
				$success = FALSE;
				$error = '没有该项目';
			}

			if ($success) {
				$this->db->query('COMMIT');
			} else {
				$this->db->query('ROLLBACK');
			}
			return array('status' => $success ? 'success' : 'error',
					'message' => $success ? '修改项目需求成功' : $error);
		}
		return array('status' => 'error', 'message' => $this->errors);
	}

	private function _get_tec_project_requirement($can_response = TRUE) {
		$s = '';
		$tecinfo = $this->_get_tec_project_html();
		$buf = file_get_contents(
				TEMPLATE_PATH . 'tec/requirement_response'
						. ($can_response ? '' : '_info') . '.tpl');
		$datas = $tecinfo['requirement_datas_new'];
		$datas_old = $tecinfo['requirement_datas_old'];
		$count = 1;

		$s .= '<tr><td style="font-weight:bold" width="10%"></td><td '
				. (!empty($datas_old) ? 'width="45%"' : '') . '>';
		foreach ($datas as $id => $requirement) {
			$s .= '<div>' . $count . '.' . $requirement['requirement']
					. '&nbsp;'
					. self::_get_response(intval($requirement['response']))
					. '&nbsp;' . $requirement['remark'] . '</div>';
			$count++;
		}
		$s .= '</td>';
		if (!empty($datas_old)) {
			$s .= '<td>';
			foreach ($datas_old as $id => $requirement) {
				$s .= '<div>' . $count . '.' . $requirement['requirement']
						. '&nbsp;'
						. self::_get_response(intval($requirement['response']))
						. '&nbsp;' . $requirement['remark'] . '</div>';
				$count++;
			}
			$s .= '</td>';
		}
		$s .= '</tr>';

		$s = $tecinfo['s'] . '</div>'
				. str_replace(array('[REQUIREMENTS]'), array($s), $buf);

		if ($can_response) {
			$xs = '';
			$ids = array();
			$count = 1;
			foreach ($datas as $id => $requirement) {
				$ids[] = $id;
				$xs .= '<tr><td width="10%">' . $count . '</td><td>'
						. $requirement['requirement']
						. '</td><td width="120px;"><input type="radio" name="response_'
						. $id
						. '" value="1" checked>&nbsp;通过&nbsp;&nbsp;<input type="radio" name="response_'
						. $id
						. '" value="2" >&nbsp;不通过</td><td><input type="text" name="remark_'
						. $id . '" id="remark_' . $id
						. '" class="validate[optional,maxSize[1000]] text" style="height:20px;width:400px;"></td></tr>';
				$count++;
			}
			$s = str_replace(array('[REQUIREMENTLISTS]', '[REQUIREMENTIDS]'),
					array($xs, ',' . implode(',', $ids) . ','), $s);

		}
		return $s;

	}

	private static function _get_response($response) {
		if ($response === 1) {
			return '<font color="#66cc00">通过</font>';
		} else if ($response === 2) {
			return '<font color="#ff6600">不通过</font>';
		}
		return '';
	}

	private function _get_tec_project_records() {
		$results = array();
		if ($this->tpid !== NULL) {
			$tec_records = $this->db
					->get_results(
							'SELECT a.*,b.realname FROM tec_project a LEFT JOIN users b ON a.response_userid=b.uid WHERE a.project_id="'
									. $this->tpid
									. '" AND a.isok=1 ORDER BY a.isalter');
			if ($tec_records !== NULL) {
				if (count($tec_records) === 1) {
					$results['old'] = $tec_records[0];
					$results['new'] = $tec_records[0];
				} else {
					for ($i = count($tec_records) - 1; $i >= 0; $i--) {
						if (intval($tec_records[$i]->id) === intval($this->id)) {
							$results['old'] = $tec_records[($i - 1)];
							$results['new'] = $tec_records[$i];
							break;
						}
					}
				}
				if (count($results) !== 2) {
					return User::no_object('获取项目信息失败',
							(BASE_URL . 'tec/?o=projectlist'));
				}
			} else {
				return User::no_object('获取项目信息失败',
						(BASE_URL . 'tec/?o=projectlist'));
			}
		}
		$this->tec_records = $results;
	}

	private static function _generate_search_field($search) {
		return '[' . $search . ']';
	}

	private function _get_requirement($requirementids) {
		$s = '';
		$data = array();
		//$results = $this->db->get_results('SELECT id,requirement FROM tec_project_requirement WHERE id IN ('. str_replace('^', ',', $requirementids) . ')');
		$results = $this->db
				->get_results(
						'SELECT a.id,a.requirement,b.response,b.remark FROM tec_project_requirement a LEFT JOIN tec_project_response b ON a.id=b.tpr_id WHERE a.id IN ('
								. str_replace('^', ',', $requirementids) . ')');
		if ($results !== NULL) {
			foreach ($results as $key => $result) {
				$data[$result->id] = array(
						'requirement' => $result->requirement,
						'response' => $result->response,
						'remark' => $result->remark);
				$s .= '<div>' . ($key + 1) . '.&nbsp;' . $result->requirement
						. '</div>';
			}
		}
		return array('s' => $s, 'data' => $data);
	}

	private function _get_tec_project_html() {

		$tecs = $this->tec_records;
		$bj = FALSE;
		if ($tecs['old']->id !== $tecs['new']->id && $tecs['old']->id !== NULL) {
			$bj = TRUE;
		}

		$tecinfo = file_get_contents(
				TEMPLATE_PATH . 'tec/projectinfo' . ($bj ? '_double' : '')
						. '.tpl');
		$tmp_searchs = array('ADDTIME', 'PID', 'PROJECTNAME', 'PROJECTTYPE',
				'CYCLE', 'TRAFFIC', 'DIDS', 'PROJECTBACKGROUND',
				'PROJECTREQUIREMENT', 'PROJECTISALTER', 'RESPONSESTATUS',
				'RESPONSEUSER', 'RESPONSETIME');
		$search = array();
		foreach ($tmp_searchs as $tmp_search) {
			$search[] = $tmp_search;
			if ($bj) {
				$search[] = $tmp_search . '1';
			}
		}

		$replace = array();
		$requirement_datas = array();
		foreach ($search as $s) {
			switch ($s) {
			case 'ADDTIME':
				$replace[] = $tecs['new']->addtime;
				break;
			case 'ADDTIME1':
				$replace[] = $tecs['old']->addtime;
				break;
			case 'PID':
				$replace[] = $tecs['new']->pid;
				break;
			case 'PID1':
				$replace[] = $tecs['old']->pid;
				break;
			case 'PROJECTNAME':
				$replace[] = $tecs['new']->project_name;
				break;
			case 'PROJECTNAME1':
				$replace[] = $tecs['old']->project_name;
				break;
			case 'PROJECTTYPE':
				$replace[] = Tec_Project_List::get_tec_project_type(
						intval($tecs['new']->project_type));
				break;
			case 'PROJECTTYPE1':
				$replace[] = Tec_Project_List::get_tec_project_type(
						intval($tecs['old']->project_type));
				break;
			case 'CYCLE':
				$replace[] = Format_Util::format_html($tecs['new']->cycle);
				break;
			case 'CYCLE1':
				$replace[] = Format_Util::format_html($tecs['old']->cycle);
				break;
			case 'TRAFFIC':
				$replace[] = Format_Util::format_html($tecs['new']->traffic);
				break;
			case 'TRAFFIC1':
				$replace[] = Format_Util::format_html($tecs['old']->traffic);
				break;
			case 'DIDS':
				$replace[] = $this->get_upload_files($tecs['new']->attachment);
				break;
			case 'DIDS1':
				$replace[] = $this->get_upload_files($tecs['old']->attachment);
				break;
			case 'PROJECTBACKGROUND':
				$replace[] = Format_Util::format_html(
						$tecs['new']->project_background);
				break;
			case 'PROJECTBACKGROUND1':
				$replace[] = Format_Util::format_html(
						$tecs['old']->project_background);
				break;
			case 'PROJECTREQUIREMENT':
				$requirements = $this
						->_get_requirement($tecs['new']->requirementids);
				$replace[] = $requirements['s'];
				$requirement_datas_new = $requirements['data'];
				break;
			case 'PROJECTREQUIREMENT1':
				$requirements = $this
						->_get_requirement($tecs['old']->requirementids);
				$replace[] = $requirements['s'];
				$requirement_datas_old = $requirements['data'];
				break;
			case 'PROJECTISALTER':
				$replace[] = Tec_Project_List::get_tec_project_version(
						intval($tecs['new']->isalter));
				break;
			case 'PROJECTISALTER1':
				$replace[] = Tec_Project_List::get_tec_project_version(
						intval($tecs['old']->isalter));
				break;
			case 'RESPONSESTATUS':
				$replace[] = Tec_Project_List::get_tec_project_response(
						intval($tecs['new']->isresponse));
				break;
			case 'RESPONSESTATUS1':
				$replace[] = Tec_Project_List::get_tec_project_response(
						intval($tecs['old']->isresponse));
				break;
			case 'RESPONSEUSER':
				$replace[] = $tecs['new']->realname;
				break;
			case 'RESPONSEUSER1':
				$replace[] = $tecs['old']->realname;
				break;
			case 'RESPONSETIME':
				$replace[] = $tecs['new']->response_time;
				break;
			case 'RESPONSETIME1':
				$replace[] = $tecs['old']->response_time;
				break;
			}
		}

		$search = array_map(array(__CLASS__, '_generate_search_field'), $search);
		return array('s' => str_replace($search, $replace, $tecinfo),
				'requirement_datas_new' => $requirement_datas_new,
				'requirement_datas_old' => $requirement_datas_old);
	}

	public function get_tec_project_info_html() {
		if ($this->has_tec_project_permission) {
			if ($this->id !== NULL) {

				$buf = file_get_contents(
						TEMPLATE_PATH . 'tec/tec_project_info.tpl');
				return str_replace(
						array('[LEFT]', '[TOP]', '[PROJECTINFO]', '[BASE_URL]'),
						array($this->get_left_html(), $this->get_top_html(),
								$this->_get_tec_project_requirement(FALSE),
								BASE_URL), $buf);
			}
			return User::no_object('没有该项目');
		}
		return User::no_permission();
	}

	public function get_tec_project_response_html() {
		if ($this->has_tec_project_permission) {
			if ($this->id !== NULL) {

				$buf = file_get_contents(
						TEMPLATE_PATH . 'tec/tec_project_response.tpl');
				return str_replace(
						array('[LEFT]', '[TOP]', '[PROJECTINFO]', '[VCODE]',
								'[ID]', '[BASE_URL]'),
						array($this->get_left_html(), $this->get_top_html(),
								$this->_get_tec_project_requirement(),
								$this->get_vcode(), $this->id, BASE_URL), $buf);
			}
			return User::no_object('没有该项目');
		}
		return User::no_permission();
	}
}
