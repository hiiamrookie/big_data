<?php
class Executive_log extends Dao_Impl {
	private $log_id;
	private $auditname;
	private $relaname;
	private $content;

	/**
	 * @return the $message
	 */
	public function getMessage() {
		$s = '';
		if ($this->log_id !== NULL) {
			$s = sprintf('%s %s 驳回 : %s', $this->auditname, $this->relaname,
					$this->content);
		}
		return $s;
	}

	public function __construct($logid = NULL) {
		parent::__construct();
		if ($logid !== NULL) {
			if (self::validate_id(intval($logid))) {
				$row = $this->db
						->get_row(
								'SELECT a.auditname,a.uid,a.content,b.realname FROM executive_log a LEFT JOIN users b WHERE id='
										. intval($logid) . ' AND a.uid=b.uid');
				if ($row !== NULL) {
					$this->log_id = intval($logid);
					$this->auditname = $row->auditname;
					$this->content = $row->content;
					$this->relaname = $row->realname;
				}
			}
		}
	}
}
