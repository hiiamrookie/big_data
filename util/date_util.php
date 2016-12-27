<?php
class Date_Util{
/**
	 * 时间差计算
	 * @param string $part 计算标志
	 * @param string $begin 开始时间
	 * @param string $end 结束时间
	 * @return 返回时间差
	 */
	public static function my_date_diff($part, $begin, $end) {
		$diff = strtotime ( $end ) - strtotime ( $begin );
		switch (strval($part)) {
			case 'y' :
				$retval = $diff / (60 * 60 * 24 * 365);
				break;
			case 'm' :
				$retval = $diff / (60 * 60 * 24 * 30);
				break;
			case 'w' :
				$retval = $diff / (60 * 60 * 24 * 7);
				break;
			case 'd' :
				$retval = $diff / (60 * 60 * 24);
				break;
			case 'h' :
				$retval = $diff / (60 * 60);
				break;
			case 'n' :
				$retval = $diff / 60;
				break;
			case 's' :
				$retval = $diff;
				break;
		}
		return $retval;
	}
}