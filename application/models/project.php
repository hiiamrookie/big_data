<?php
class Project extends User {
	private $projectname;
	private $remark;
	private $errors = array ();
	private $id;
	private $audit_pass;
	private $reason;
	public function __construct($fields = array()) {
		parent::__construct ();
		if (! empty ( $fields )) {
			foreach ( $this as $key => $value ) {
				if ($fields [$key] !== NULL && ! in_array ( $key, array (
						'errors' 
				), TRUE )) {
					$this->$key = $fields [$key];
				}
			}
		}
	}
	public function getIndexHtml() {
		return str_replace ( array (
				'[LEFT]',
				'[TOP]',
				'[VCODE]',
				'[BASE_URL]' 
		), array (
				$this->get_left_html (),
				$this->get_top_html (),
				$this->get_vcode (),
				BASE_URL 
		), file_get_contents ( TEMPLATE_PATH . 'project/project_add.tpl' ) );
	}
	private function validate_form_value($action) {
		$errors = array ();
		if (in_array ( $action, array (
				'add',
				'audit',
				'update',
				'cancel' 
		), TRUE )) {
			if ($action === 'add' || $action === 'update') {
				if ($action === 'update') {
					if (! self::validate_id ( $this->id )) {
						$errors [] = '立项记录选择有误';
					}
				}
				
				if (! self::validate_field_not_empty ( $this->projectname ) || ! self::validate_field_not_null ( $this->projectname )) {
					$errors [] = '立项名称不能为空';
				} else if (! self::validate_field_max_length ( $this->projectname, 200 )) {
					$errors [] = '执行单名称长度最多200个字符';
				}
				
				if (self::validate_field_not_empty ( $this->remark ) && ! self::validate_field_max_length ( $this->remark, 1000 )) {
					$errors [] = '备注长度最多1000个字符';
				}
			} else if ($action === 'audit' || $action === 'cancel') {
				if (! self::validate_id ( $this->id )) {
					$errors [] = '立项记录选择有误';
				}
				
				if ($action === 'audit') {
					if (! in_array ( intval ( $this->audit_pass ), array (
							0,
							1 
					), TRUE )) {
						$errors [] = '审核结果选择有误';
					}
					
					if (intval ( $this->audit_pass ) === 0 && empty ( $this->reason )) {
						$errors [] = '审核驳回的话审核意见不能为空';
					}
					
					if (self::validate_field_not_empty ( $this->reason ) && ! self::validate_field_max_length ( $this->reason, 1000 )) {
						$errors [] = '审核意见长度最多1000个字符';
					}
				}
			}
		} else {
			$errors [] = '无权限操作';
		}
		
		if (empty ( $errors )) {
			return TRUE;
		}
		$this->errors = $errors;
		unset ( $errors );
		return FALSE;
	}
	public function getAddProjectResult() {
		if ($this->validate_form_value ( 'add' )) {
			$success = TRUE;
			$error = '';
			$this->db->query ( 'BEGIN' );
			
			$row = $this->db->get_row ( 'SELECT id FROM bd_project WHERE project_name="' . $this->projectname . '" FOR UPDATE' );
			if ($row !== NULL) {
				$success = FALSE;
				$error = '立项名称【' . $this->projectname . '】已存在';
			} else {
				$insert_result = $this->db->query ( 'INSERT INTO bd_project(project_name,remark,userid,addtime) VALUES("' . $this->projectname . '","' . $this->remark . '",' . $this->getUid () . ',now())' );
				if ($insert_result === FALSE) {
					$success = FALSE;
					$error = '新建立项失败';
				}
			}
			
			if ($success) {
				$this->db->query ( 'COMMIT' );
			} else {
				$this->db->query ( 'ROLLBACK' );
			}
			return array (
					'status' => $success ? 'success' : 'error',
					'message' => $success ? '立项成功' : $error 
			);
		}
		return array (
				'status' => 'error',
				'message' => $this->errors 
		);
	}
	public function getUpdateProjectResult() {
		$row = $this->db->get_row ( 'SELECT * FROM bd_project WHERE id=' . intval ( $this->id ) . ' AND userid=' . $this->getUid () );
		if ($row !== NULL) {
			if (intval ( $row->status ) === - 1 || intval ( $row->status ) === 1) {
				if ($this->validate_form_value ( 'update' )) {
					$success = TRUE;
					$error = '';
					$this->db->query ( 'BEGIN' );
					
					// 是否已有
					$obj = $this->db->get_row ( 'SELECT id FROM bd_project WHERE project_name="' . $this->projectname . '" AND id<>' . intval ( $this->id ) );
					if ($obj === NULL) {
						$result = $this->db->query ( 'UPDATE bd_project SET project_name="' . $this->projectname . '",remark="' . $this->remark . '",status=0,audit_reason="" WHERE id=' . intval ( $this->id ) );
						if ($result === FALSE) {
							$success = FALSE;
							$error = '修改立项失败';
						}
					} else {
						$success = FALSE;
						$error = '已有同名的立项申请';
					}
					
					if ($success) {
						$this->db->query ( 'COMMIT' );
					} else {
						$this->db->query ( 'ROLLBACK' );
					}
					return array (
							'status' => $success ? 'success' : 'error',
							'message' => $success ? '修改立项成功' : $error 
					);
				}
				return array (
						'status' => 'error',
						'message' => $this->errors 
				);
			}
			return array (
					'status' => 'error',
					'message' => '该立项申请非可修改状态' 
			);
		}
		return array (
				'status' => 'error',
				'message' => '没有该立项申请或非本人创建的立项申请' 
		);
	}
	public function getAuditProjectResult() {
		if ($this->getHas_project_tab () && count ( $this->getProjects () ) > 0) {
			if ($this->validate_form_value ( 'audit' )) {
				$success = TRUE;
				$error = '';
				$this->db->query ( 'BEGIN' );
				
				$row = $this->db->get_row ( 'SELECT id FROM bd_project WHERE id=' . intval ( $this->id ) . ' AND status=0 FOR UPDATE' );
				if ($row !== NULL) {
					$sql = 'UPDATE bd_project SET status=' . (intval ( $this->audit_pass ) === 0 ? - 1 : 1);
					if (! empty ( $this->reason )) {
						$sql .= ',audit_reason="' . $this->reason . '"';
					}
					$sql .= ' WHERE id=' . intval ( $this->id );
					
					$result = $this->db->query ( $sql );
					if ($result === FALSE) {
						$success = FALSE;
						$error = '审核立项失败';
					}
				} else {
					$success = FALSE;
					$error = '没有该立项申请或者该立项申请的状态非可审核';
				}
				
				if ($success) {
					$this->db->query ( 'COMMIT' );
				} else {
					$this->db->query ( 'ROLLBACK' );
				}
				return array (
						'status' => $success ? 'success' : 'error',
						'message' => $success ? '审核立项成功' : $error 
				);
			}
			return array (
					'status' => 'error',
					'message' => $this->errors 
			);
		}
		return array (
				'status' => 'error',
				'message' => NO_RIGHT_TO_DO_THIS 
		);
	}
	public function getProjectEditHtml($id) {
		$row = $this->db->get_row ( 'SELECT * FROM bd_project WHERE id=' . intval ( $id ) . ' AND userid=' . $this->getUid () );
		if ($row !== NULL) {
			if (intval ( $row->status ) === - 1 || intval ( $row->status ) === 1) {
				return str_replace ( array (
						'[LEFT]',
						'[TOP]',
						'[VCODE]',
						'[PROJECTNAME]',
						'[REMARK]',
						'[ID]',
						'[BASE_URL]' 
				), array (
						$this->get_left_html (),
						$this->get_top_html (),
						$this->get_vcode (),
						$row->project_name,
						$row->remark,
						$row->id,
						BASE_URL 
				), file_get_contents ( TEMPLATE_PATH . 'project/project_edit.tpl' ) );
			}
			User::no_object ( '该立项申请非可修改状态' );
		}
		User::no_permission ( '没有该立项申请或非本人创建的立项申请' );
	}
	public function getProjectAuditHtml($id) {
		if ($this->getHas_project_tab () && count ( $this->getProjects () ) > 0) {
			$row = $this->db->get_row ( 'SELECT * FROM bd_project WHERE id=' . intval ( $id ) );
			if ($row !== NULL) {
				return str_replace ( array (
						'[LEFT]',
						'[TOP]',
						'[VCODE]',
						'[PROJECTNAME]',
						'[REMARK]',
						'[ID]',
						'[BASE_URL]' 
				), array (
						$this->get_left_html (),
						$this->get_top_html (),
						$this->get_vcode (),
						$row->project_name,
						$row->remark,
						$row->id,
						BASE_URL 
				), file_get_contents ( TEMPLATE_PATH . 'project/project_audit.tpl' ) );
			}
			User::no_object ( '没有该立项申请' );
		}
		User::no_permission ( NO_RIGHT_TO_DO_THIS );
	}
	public function getProjectSelect($project_id = NULL, $isSelect = TRUE) {
		if ($isSelect) {
			$s = '<option value="">请选择</option>';
			$results = $this->db->get_results ( 'SELECT id,project_name FROM bd_project WHERE status=1' );
			if ($results !== NULL) {
				foreach ( $results as $result ) {
					$s .= '<option value="' . $result->id . '" ' . (intval ( $project_id ) === intval ( $result->id ) ? 'selected="selected"' : '') . '>' . $result->project_name . '</option>';
				}
			}
		} else {
			$s = $this->db->get_var ( 'SELECT project_name FROM bd_project WHERE id=' . intval ( $project_id ) );
		}
		return $s;
	}
	public function getCancelProjectResult() {
		$row = $this->db->get_row ( 'SELECT * FROM bd_project WHERE id=' . intval ( $this->id ) . ' AND userid=' . $this->getUid () );
		if ($row !== NULL) {
			
			if ($this->validate_form_value ( 'cancel' )) {
				$success = TRUE;
				$error = '';
				$this->db->query ( 'BEGIN' );
				
				// 是否已关联
				$obj = $this->db->get_row ( 'SELECT id FROM bd_project_contract WHERE project_id=' . intval ( $this->id ) );
				if ($obj === NULL) {
					
					$result = $this->db->query ( 'UPDATE bd_project SET status=-2 WHERE id=' . intval ( $this->id ) );
					if ($result === FALSE) {
						$success = FALSE;
						$error = '取消立项失败';
					}
				} else {
					$success = FALSE;
					$error = ' 该立项申请已关联合同，无法取消';
				}
				
				if ($success) {
					$this->db->query ( 'COMMIT' );
				} else {
					$this->db->query ( 'ROLLBACK' );
				}
				return array (
						'status' => $success ? 'success' : 'error',
						'message' => $success ? '取消立项成功' : $error 
				);
			}
			return array (
					'status' => 'error',
					'message' => $this->errors 
			);
		}
		return array (
				'status' => 'error',
				'message' => '没有该立项申请或非本人创建的立项申请' 
		);
	}
}