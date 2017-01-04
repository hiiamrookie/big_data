<?php
class Data_Import_Export extends User {
	public function __construct() {
		parent::__construct ( FALSE, NULL );
	}
	public function get_data_import_html() {
		$buf = file_get_contents ( TEMPLATE_PATH . 'report/data_import.tpl' );
		return str_replace ( array (
				'[LEFT]',
				'[TOP]',
				'[VCODE]',
				'[VALIDATEFILE]',
				'[MAXFILESIZE]',
				'[BASE_URL]' 
		), array (
				$this->get_left_html (),
				$this->get_top_html (),
				$this->get_vcode (),
				implode ( ',', $GLOBALS ['defined_upload_execel_validate_type'] ),
				UPLOAD_FILE_MAX_SIZE / (1024 * 1024),
				BASE_URL 
		), $buf );
	}
	public function get_data_export_html() {
		$buf = file_get_contents ( TEMPLATE_PATH . 'report/data_export.tpl' );
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
	}
	public function import($filename){
		//var_dump($filename);
	}
	public function export(){
		
	}
}