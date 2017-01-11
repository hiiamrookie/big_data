<?php
class API extends User {
	private static $instance = NULL;
	private $has_setup_permission = FALSE;
	private $auth_name;
	private $auth_type;
	private $id;
	private $errors = array ();
	public function __construct($fields = array()) {
		parent::__construct ();
		if (in_array ( $this->getUsername (), $GLOBALS ['manager_setup_permission'], TRUE )) {
			$this->has_setup_permission = TRUE;
		}
		
		if ($this->has_setup_permission) {
			if (! empty ( $fields )) {
				foreach ( $this as $key => $value ) {
					if ($fields [$key] !== NULL && ! in_array ( $key, array (
							'has_setup_permission' 
					), TRUE )) {
						$this->$key = $fields [$key];
					}
				}
			}
		}
	}
	public static function getInstance($force_flush = FALSE) {
		if (self::$instance === NULL || $force_flush) {
			$api_cache_filename = md5 ( 'api_cache_filename' );
			$api_cache = new FileCache ( CACHE_TIME, CACHE_PATH );
			$api_cache_file = $api_cache->get ( $api_cache_filename );
			
			if ($api_cache_file === FALSE || $force_flush) {
				// 读取数据库
				$dao = new Dao_Impl ();
				$apis = $dao->db->get_results ( 'SELECT * FROM api_auth WHERE is_live=1' );
				if ($apis !== NULL) {
					$datas = array ();
					foreach ( $apis as $api ) {
						$datas [] = array (
								'id' => $api->id,
								'auth_name' => $api->auth_name,
								'auth_code' => $api->auth_code,
								'auth_type' => $api->auth_type 
						);
					}
					$api_cache->set ( $api_cache_filename, $datas );
				}
			}
			self::$instance = $api_cache->get ( $api_cache_filename );
		}
		return self::$instance;
	}
	private static function _get_auth_type($type) {
		$s = '';
		switch (intval ( $type )) {
			case 0 :
				$s = 'DSP平台';
				break;
			case 1 :
				$s = '用户';
				break;
		}
		
		return $s;
	}
	private static function _get_action($id, $is_live) {
		return intval ( $is_live ) === 1 ? '<a href="javascript:del(\'' . $id . '\');">删除</a>' : '';
	}
	private static function _get_status($is_live) {
		switch (intval ( $is_live )) {
			case - 1 :
				return '<font color="red"><b>已删除</b></font>';
			case 1 :
				return '<font color="green"><b>正常</b></font>';
			default :
				return '<font color="red"><b>状体异常</b></font>';
		}
	}
	public function get_auth_list_html() {
		if ($this->has_setup_permission) {
			$results = $this->db->get_results ( 'SELECT * FROM api_auth ORDER BY is_live DESC ,id DESC' );
			$apis = '';
			if ($results !== NULL) {
				$count = 0;
				foreach ( $results as $result ) {
					$apis .= '<tr><td>' . ($count + 1) . '</td><td>' . $result->auth_name . '</td><td>' . $result->auth_code . '</td><td>' . self::_get_auth_type ( $result->auth_type ) . '</td><td>' . self::_get_status ( $result->is_live ) . '</td><td>' . self::_get_action ( $result->id, $result->is_live ) . '</td></tr>';
					$count ++;
				}
			} else {
				$apis = '<tr><td colspan="6"><font color="red">没有API验证数据</font></td></tr>';
			}
			$buf = file_get_contents ( TEMPLATE_PATH . 'system/api_auth_list.tpl' );
			return str_replace ( array (
					'[LEFT]',
					'[TOP]',
					'[VCODE]',
					'[API_AUTH_LIST]',
					'[BASE_URL]' 
			), array (
					$this->get_left_html (),
					$this->get_top_html (),
					$this->get_vcode (),
					$apis,
					BASE_URL 
			), $buf );
		} else {
			return User::no_permission ();
		}
	}
	public function get_auth_add_html() {
		if ($this->has_setup_permission) {
			
			$buf = file_get_contents ( TEMPLATE_PATH . 'system/api_auth_add.tpl' );
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
			), $buf );
		} else {
			return User::no_permission ();
		}
	}
	private function validate_form_value($action) {
		$errors = array ();
		if ($action === 'add') {
			if (! self::validate_field_not_empty ( $this->auth_name ) || ! self::validate_field_not_null ( $this->auth_name )) {
				$errors [] = 'API验证名称不能为空';
			} else if (! self::validate_field_max_length ( $this->auth_name, 100 )) {
				$errors [] = 'API验证名称长度最多100个字符';
			}
			
			if (! in_array ( $this->auth_type, array (
					'0',
					'1' 
			), TRUE )) {
				$errors [] = 'API验证类型选择有误';
			}
		} else if ($action === 'del') {
			if (! self::validate_id ( $this->id )) {
				$errors [] = '记录选择有误';
			}
		} else {
			$errors [] = NO_RIGHT_TO_DO_THIS;
		}
		
		if (empty ( $errors )) {
			return TRUE;
		}
		$this->errors = $errors;
		unset ( $errors );
		return FALSE;
	}
	public function add_api_auth() {
		if ($this->has_setup_permission) {
			if ($this->validate_form_value ( 'add' )) {
				$error = '';
				$success = TRUE;
				
				$this->db->query ( 'BEGIN' );
				
				$id = $this->db->get_var ( 'SELECT id FROM api_auth WHERE auth_name="' . $this->auth_name . '"' );
				if ($id > 0) {
					$error = 'API验证名称已存在';
					$success = FALSE;
				} else {
					$result = $this->db->query ( 'INSERT INTO api_auth(auth_name,auth_code,auth_type,is_live) VALUES("' . $this->auth_name . '","' . String_Util::my_md5 ( $this->auth_name . parent::SALT_VALUE ) . '",' . $this->auth_type . ',1)' );
					if ($result === FALSE) {
						$error = '新增API验证信息失败';
						$success = FALSE;
					}
				}
				
				if ($success) {
					$this->db->query ( 'COMMIT' );
					self::getInstance ( TRUE );
				} else {
					$this->db->query ( 'ROLLBACK' );
				}
				return array (
						'status' => $success ? 'success' : 'error',
						'message' => $success ? '新增API验证信息成功' : $error 
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
	public function del_api_auth() {
		if ($this->has_setup_permission) {
			if ($this->validate_form_value ( 'del' )) {
				$error = '';
				$success = TRUE;
				
				$this->db->query ( 'BEGIN' );
				
				$id = $this->db->get_var ( 'SELECT id FROM api_auth WHERE id=' . $this->id . ' AND is_live=1' );
				if (! $id) {
					$error = 'API验证名称不存在或状态已删除';
					$success = FALSE;
				} else {
					$result = $this->db->query ( 'UPDATE api_auth SET is_live=-1 WHERE id=' . $this->id );
					if ($result === FALSE) {
						$error = '删除API验证信息失败';
						$success = FALSE;
					}
				}
				
				if ($success) {
					$this->db->query ( 'COMMIT' );
					self::getInstance ( TRUE );
				} else {
					$this->db->query ( 'ROLLBACK' );
				}
				return array (
						'status' => $success ? 'success' : 'error',
						'message' => $success ? '删除API验证信息成功' : $error 
				);
			}
			return array (
					'status' => 'error',
					'message' => implode ( '，', $this->errors ) 
			);
		}
		return array (
				'status' => 'error',
				'message' => NO_RIGHT_TO_DO_THIS 
		);
	}
}