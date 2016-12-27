<?php
class Model extends Dao_Impl {
	protected $addtime;
	
	public function getAddtime() {
		return $this->addtime;
	}
	
	public function __construct($timezone = 'PRC') {
		parent::__construct ();
		$now_timezone = date_default_timezone_get ();

		if ($timezone !== $now_timezone) {
			date_default_timezone_set ( $timezone );
		}
		$this->addtime = date ( 'Y-m-d H:i:s', time () );
	}
}