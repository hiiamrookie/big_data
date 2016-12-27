<?php
class Class_Util {
	public static function call_method_for_args($object, $args, $name = 'construct') {
		$method = $name . '_' . implode('_', Validate_Util::my_typelist($args));
		if(!is_callable(array($object,$method))){
			throw new Exception('method isn\'t callabled');
		}
		call_user_func_array(array($object,$method), $args);
	}
}