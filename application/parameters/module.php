<?php
class Module {
	private static $instance = NULL;
	public static function getInstance($force_flush = FALSE) {
		if (self::$instance === NULL || $force_flush) {
			$module_cache_filename = md5('module_cache_filename');
			$module_cache = new FileCache(CACHE_TIME, CACHE_PATH);
			$module_cache_file = $module_cache->get($module_cache_filename);

			if ($module_cache_file === FALSE || $force_flush) {
				//读取数据库
				$dao = new Dao_Impl();
				$modules = $dao->db
						->get_results(
								'SELECT id,modulename FROM sys_module WHERE islive=1 ORDER BY id');
				if ($modules !== NULL) {
					$datas = array();
					foreach ($modules as $module) {
						$datas[$module->id] = array(
								'modulename' => $module->modulename);
					}
					$module_cache->set($module_cache_filename, $datas);
				}
			}
			self::$instance = $module_cache->get($module_cache_filename);
		}
		return self::$instance;
	}

	public static function get_module_html($force_flush = FALSE,
			$module_id = NULL) {
		self::getInstance($force_flush);
		$instance = self::$instance;
		$result = '<option value="">请选择</option>';

		if ($instance !== NULL) {
			if (!is_int($module_id)) {
				$module_id = intval($module_id);
			}
			foreach ($instance as $moduleid => $module) {
				$result .= '<option value="' . $moduleid . '" '
						. (intval($moduleid) === $module_id ? 'selected="selected"'
								: '') . '>' . $module['modulename']
						. '</option>';
			}
		}
		return $result;
	}
}