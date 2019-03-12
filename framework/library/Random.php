<?php

require LIB_PATH . 'Random/random_compat.phar';

class Random {
	
	const STRING = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789';
	const NUMBER = '0123456789';
	
	public static function string($length, $chars=null) {
		if (is_null($chars)) {
			$chars = self::STRING;
		}
		$chars = str_shuffle($chars);
		$string = '';
		$max = strlen($chars) - 1;
		for ($i = 0; $i < $length; $i++){
			$string .= $chars[random_int(0, $max)];
		}
		return $string;
	}
	
	public static function number($length) {
		return self::string($length, self::NUMBER);
	}
	
	public static function int($min, $max) {
		return random_int($min, $max);
	}
	
	public static function boolean() {
		return random_int(0, 1) ? true : false;
	}
	
}