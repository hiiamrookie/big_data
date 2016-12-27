<?php
class Sql_Util {
	private static $QUERY_SQL_BASE = 'SELECT %s FROM `%s` WHERE %s';
	
	private static $INSERT_SQL_BASE = 'INSERT INTO `%s` %s VALUES %s';
	
	private static $UPDATE_SQL_BASE = 'UPDATE %s SET %s WHERE %s';
	
	private static $where_fields_between = array ('AND', 'OR' );
	
	private static $query_type = array ('object', 'count' );
	
	private static $where_field_value_symbol = array ('=', '<', '>', '<>', '!=' );
	
	private static $debug = FALSE;
	
	private static $error_message_param = '生成SQL语句的参数错误';
	
	private static function add_field_flag($field) {
		return '`' . $field . '`';
	}
	
	private static function add_value_flag($value) {
		return '\'' . $value . '\'';
	}
	
	private static function get_query_sql($table_name, $field, $where_array, $where_between) {
		if (! is_string ( $table_name ) || ! is_array ( $where_array ) || ! in_array ( $where_between, self::$where_fields_between )) {
			throw new Exception ( self::$error_message_param );
		}
		$sql_template = self::$QUERY_SQL_BASE;
		$where_sql = array ();
		if (! empty ( $where_array )) {
			foreach ( $where_array as $key => $value ) {
				if (! in_array ( $value [0], self::$where_field_value_symbol )) {
					throw new Exception ( self::$error_message_param );
				}
				$where_sql [] = '`' . $key . '`' . $value [0] . '"' . $value [1] . '"';
			}
			$where_sql = implode ( ' ' . $where_between . ' ', $where_sql );
		
		//			$where_sql = str_replace ( '\'', '\\\'', $where_sql );
		}
		if (is_array ( $field )) {
			$field = array_map ( array (__CLASS__, 'add_field_flag' ), $field );
			$field = implode ( ',', $field );
		} else {
			if (FALSE === strpos ( $field, '*' )) {
				$field = '`' . $field . '`';
			}
		}
		$tmp = 'return sprintf ( \'' . $sql_template . '\', \'' . $field . '\' , \'' . $table_name . '\', \'' . $where_sql . '\' );';

		if (self::$debug) {
			var_dump ( $tmp );
		}
		return eval ( $tmp );
	}
	
	public static function get_query($table_name, $field, $where_array, $where_between) {
		try {
			$sql = self::get_query_sql ( $table_name, $field, $where_array, $where_between );
			return array ('status' => 'success', 'sql' => $sql );
		} catch ( Exception $e ) {
			return array ('status' => 'error', 'message' => $e->getMessage () );
		}
	}
	
	private static function get_insert_sql($table_name, $field_value_array) {
		if (! is_string ( $table_name ) || ! is_array ( $field_value_array )) {
			throw new Exception ( self::$error_message_param );
		}
		$sql_template = self::$INSERT_SQL_BASE;
		$field_array = array_keys ( $field_value_array );
		$value_array = array_values ( $field_value_array );
		$field_array = array_map ( array (__CLASS__, 'add_field_flag' ), $field_array );
		$field_array = '(' . implode ( ',', $field_array ) . ')';
		if (count ( $value_array ) === count ( $value_array, 1 )) {
			//一维数组
			$value_array = array_map ( array (__CLASS__, 'add_value_flag' ), $value_array );
			$all_value = '(' . implode ( ',', $value_array ) . ')';
		} else {
			$all_value = array ();
			foreach ( $value_array as $va ) {
				$va = array_map ( array (__CLASS__, 'add_value_flag' ), $va );
				$va = '(' . implode ( ',', $va ) . ')';
				$all_value [] = $va;
			}
			
			$all_value = implode ( ',', $all_value );
		}
		$field_array = str_replace ( '\'', '\\\'', $field_array );
		$all_value = str_replace ( '\'', '\\\'', $all_value );
		$tmp = 'return sprintf ( \'' . $sql_template . '\', \'' . $table_name . '\' , \'' . $field_array . '\', \'' . $all_value . '\' );';
		if (self::$debug) {
			var_dump ( $tmp );
		}
		return eval ( $tmp );
	}
	
	public static function get_insert($table_name, $field_value_array) {
		try {
			$sql = self::get_insert_sql ( $table_name, $field_value_array );
			return array ('status' => 'success', 'sql' => $sql );
		} catch ( Exception $e ) {
			return array ('status' => 'error', 'message' => $e->getMessage () );
		}
	}
	
	private static function get_update_sql($table_name, $field_value_array, $where_array, $where_between) {
		if (! is_string ( $table_name ) || ! is_array ( $field_value_array ) || ! is_array ( $where_array ) || ! in_array ( $where_between, self::$where_fields_between )) {
			throw new Exception ( self::$error_message_param );
		}
		$sql_template = self::$UPDATE_SQL_BASE;
		$where_sql = array ();
		if(!empty($where_array)){
			foreach ($where_array as $key=>$value){
				if (! in_array ( $value [0], self::$where_field_value_symbol )) {
					throw new Exception ( self::$error_message_param );
				}
				$where_sql[] = '`' . $key . '`' . $value [0] . '"' . $value [1] . '"';
			}
			$where_sql = implode ( ' ' . $where_between . ' ', $where_sql );
		}
		
		$field_sql = array();
		if(empty($field_value_array)){
			throw new Exception ( self::$error_message_param );
		}else{
			foreach ($field_value_array as $key=>$value){
				$field_sql[] = '`' . $key . '`="' . $value . '"';
			}
			$field_sql = implode(',', $field_sql);
		}
		$tmp = 'return sprintf ( \'' . $sql_template . '\', \'' . $table_name . '\' , \'' . $field_sql . '\', \'' . $where_sql . '\' );';
		if (self::$debug) {
			var_dump ( $tmp );
		}
		return eval ( $tmp );
	}
	
	public static function get_update($table_name, $field_value_array, $where_array, $where_between) {
		try {
			$sql = self::get_update_sql ( $table_name, $field_value_array, $where_array, $where_between );
			return array ('status' => 'success', 'sql' => $sql );
		} catch ( Exception $e ) {
			return array ('status' => 'error', 'message' => $e->getMessage () );
		}
	}
}