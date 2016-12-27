<?php
class Tec_Project_Response extends User {
	private $has_tec_project_permission = FALSE;
	private $requirementids_array = array();
	private $errors = array();
	private $id;

	public function __construct($fields) {
		parent::__construct();
		if (intval($this->getBelong_dep()) === 6) {
			//技术部
			$this->has_tec_project_permission = TRUE;
		}
		if (!empty($fields)) {
			foreach ($this as $key => $value) {
				if ($fields[$key] !== NULL
						&& !in_array($key, array('has_tec_project_permission'),
								TRUE)) {
					$this->$key = $fields[$key];
				}
			}
		}
	}

	private function validate_form_value($action) {
		$errors = array();
		if ($this->has_tec_project_permission
				&& in_array($action, array('response'), TRUE)) {
			//project id
			if(!self::validate_id(intval($this->id))){
				$errors[] = '项目选择有误';
			}
					
			//response
			$requirementids_array = $this->requirementids_array;
			if(empty($requirementids_array)){
				$errors[] = '项目响应不能为空';
			}else{
				$count = 1;
				foreach ($requirementids_array as $key => $value) {
					if(!self::validate_id($key)){
						$errors[] = '第' . $count . '条需求选择有误';
					}
					
					$response = intval($value['response']);
					if (!in_array($response, array(1, 2), TRUE)) {
						$errors[] = '第' . $count . '条响应选择有误';
					} else {
						if ($response === 2
								&& !self::validate_field_not_empty($value['remark'])) {
							$errors[] = '第' . $count . '条不通过时响应内容不能为空';
						} else if (self::validate_field_not_empty($value['remark'])
								&& !self::validate_field_max_length(
										$value['remark'], 1000)) {
							$errors[] = '第' . $count . '条响应备注最多1000个字符 ';
						}
					}
					$count++;
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

	public function response_tec_project() {
		if ($this->validate_form_value('response')) {
			$success = TRUE;
			$error = '';
			$this->db->query('BEGIN');
			
			$sql = array();
			$requirementids_array = $this->requirementids_array;
			foreach ($requirementids_array as $key => $value) {
				$sql[] = '(' . $key . ',' . intval($value['response']) . ',"' . $value['remark']  . '")';
			}
			
			if(!empty($sql)){
				$insert_result = $this->db->query('INSERT INTO tec_project_response(tpr_id,response,remark) VALUES' . implode(',', $sql));
				if($insert_result === FALSE){
					$success = FALSE;
					$error = '新增项目响应失败';
				}else{
					$update_result = $this->db->query('UPDATE tec_project SET isresponse=1,response_time=now(),response_userid=' . $this->getUid() . ' WHERE id=' . intval($this->id));
					if($update_result === FALSE){
						$success = FALSE;
						$error = '更新项目状态失败';
					}
				}
			}else{
				$success = FALSE;
				$error = '项目响应不能为空';
			}

			if ($success) {
				$this->db->query('COMMIT');
			} else {
				$this->db->query('ROLLBACK');
			}
			return array('status' => $success ? 'success' : 'error',
					'message' => $success ? '响应项目需求成功' : $error);
		}

		return array('status' => 'error', 'message' => $this->errors);
	}
}
