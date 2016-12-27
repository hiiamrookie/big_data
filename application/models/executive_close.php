<?php
class Executive_Close extends User {
	private $id;
	private $permission_close_executive = FALSE;

	public function __construct($id) {
		parent::__construct();
		$this->id = $id;
		if (in_array($this->getUsername(),
				$GLOBALS['manager_finance_permission'], TRUE)
				|| (int) ($this->getBelong_dep()) === 2) {
			$this->permission_close_executive = TRUE;
		}
	}

	public function closeExecutive($isclose) {
		if ($this->permission_close_executive) {
			$result = $this->db
					->query(
							'UPDATE executive SET is_closed='
									. ($isclose ? 1 : 0) . ' WHERE id='
									. (int) ($this->id));
			return array('status' => $result !== FALSE ? 'success' : 'error',
					'message' => ($isclose ? '关闭' : '打开')
							. ($result !== FALSE ? '执行单成功' : '执行单失败'));
		}
		return array('status' => 'error', 'message' => NO_RIGHT_TO_DO_THIS);
	}
}
