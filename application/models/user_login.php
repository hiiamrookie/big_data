<?php
class User_Login extends Dao_Impl {
	private $result = array();

	/**
	 * @return the $result
	 */
	public function getResult() {
		return $this->result;
	}

	public function __construct($username, $password, $ismobile = FALSE) {
		if (empty($username) || empty($password)) {
			$this->result = array('status' => 'error',
					'message' => '用户名或密码不能为空');
		} else {
			parent::__construct();
			$username = is_string($username) ? strtolower($username) : '';
			$password = String_Util::my_md5(
					is_string($password) ? $password : '', 1);

			$user = $this->db
					->get_row(
							'SELECT uid,username FROM users WHERE username="'
									. $username . '" AND password="'
									. $password . '" AND islive=1');
			if ($user === NULL) {
				//没有该用户或非正常用户
				$this->result = array('status' => 'error',
						'message' => '用户名或密码错误');
			} else {
				if ($ismobile) {
					$time = time();
					$token = String_Util::my_md5(
							uniqid(
									User::SALT_VALUE . '|' . $username . '|'
											. $password . '|' . $time));

					$row = $this->db
							->get_row(
									'SELECT uid,time FROM mobile_login WHERE username="'
											. $username . '"');
					if ($row === NULL) {
						$insert_result = $this->db
								->query(
										'INSERT INTO mobile_login(uid,username,token,time) VALUES('
												. $user->uid . ',"' . $username
												. '","' . $token . '",' . $time
												. ')');
					} else {
						$insert_result = $this->db
								->query(
										'UPDATE mobile_login SET token="'
												. $token . '",time=' . $time
												. ' WHERE username="'
												. $username . '"');
					}
					if ($insert_result === FALSE) {
						$this->result = array('status' => 'error',
								'message' => '获取token出错');
					} else {
						$this->result = array('status' => 'success',
								'message' => '登录成功', 'token' => $token,'userid'=>$user->uid);
					}
				} else {
					$insert_result = $this->db
							->query(
									'INSERT INTO login(username,ip,time) VALUES("'
											. $user->username . '","'
											. Server_Util::get_ip() . '",'
											. time() . ')');

					if ($insert_result === FALSE) {
						//添加登录LOG出错
						$this->result = array('status' => 'error',
								'message' => '记录用户登录信息出错');
					} else {
						//用户信息加入session
						Session_Util::my_session_set('user',
								json_encode(
										array('uid' => $user->uid,
												'username' => $user->username)));

						$this->result = array('status' => 'success',
								'message' => '登录成功');
					}
				}
			}
		}
	}
}
