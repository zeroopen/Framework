<?php

class Encrypt {
	
	protected static $string = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789';
	
	protected static $length = array(
		'md5' => 32,
		'sha1' => 40,
		'sha256' => 64,
		'sha512' => 128,
	);
	
	/**
	 * 生成密码哈希值
	 * @param string $password 密码
	 * @param int $time 时间
	 * @param string $hash 散列算法（默认：sha256）
	 * @return string
	 */
	public static function password($password, $time, $hash='sha256') {
		if (!is_numeric($time) || !isset(self::$length[$hash])) {
			return false;
		}
		$num = substr($time, -3)%64;
		$before = hash($hash, md5($time));
		$salt_length = self::$length[$hash];
		$salt = substr($before, floor($num/2), $salt_length);
		if (strlen($salt) < $salt_length) {
			$salt .= substr($before, 0, $salt_length-strlen($salt));
		}
		$password = hash($hash, $salt.$password.$time);
		return str_insert($password, $num, $salt);
	}
	
	/**
	 * 生成密钥
	 * @param mixed $args 参数
	 * @return string
	 */
	public static function key() {
		$args = func_get_args();
		$string = '';
		foreach ($args as $arg) {
			$string .= md5($arg);
		}
		return hash('sha256', $string);
	}
	
}