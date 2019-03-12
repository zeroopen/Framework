<?php

class File {
	
	private static $directory='';
	
	public static function directory($directory) {
		if (is_dir($directory)) {
			self::$directory = rtrim($directory, '/') . '/';
			return true;
		}
		return false;
	}
	
	public static function exists($file) {
		return file_exists(self::$directory . $file);
	}
	
	public static function isReadable($file) {
		return is_readable(self::$directory . $file);
	}
	
	public static function isWritable($file) {
		$file = self::$directory . $file;
		if (DS == '/' and @ini_get("safe_mode") == false) {
			return is_writable($file);
		}
		if (is_dir($file)) {
			$file = rtrim($file, '/').'/'.md5(mt_rand(1,100).mt_rand(1,100));
			if (($fp = @fopen($file, 'ab')) === false) {
				return false;
			}
			fclose($fp);
			@chmod($file, 0755);
			@unlink($file);
			return true;
		} else if (!is_file($file) or ($fp = @fopen($file, 'ab')) === false) {
			return false;
		}
		fclose($fp);
		return true;
	}
	
	public static function create($file) {
		$file = self::$directory . $file;
		if (file_exists($file)) {
			return false;
		}
		return file_put_contents($file, '') === false ? false : true;
	}
	
	public static function copy($file, $target_file, $over_write=false) {
		$file = self::$directory . $file;
		if (!file_exists($file)) {
			return false;
		}
		if ($over_write && file_exists($target_file)) {
			return false;
		}
		return copy($file, $target_file);
	}
	
	public static function delete($file) {
		return unlink(self::$directory . $file);
	}
	
	public static function move($file, $target_file, $over_write=false) {
		$file = self::$directory . $file;
		if (!file_exists($file)) {
			return false;
		}
		if ($over_write && file_exists($target_file)) {
			return false;
		}
		return rename($file, $target_file);
	}
	
	public static function get($file) {
		$file = self::$directory . $file;
		if (!file_exists($file)) {
			return false;
		}
		return file_get_contents($file);
	}
	
	public static function edit($file, $text) {
		return file_put_contents(self::$directory . $file, $text) === false ? false : true;
	}
	
	public static function append($file, $text) {
		return file_put_contents(self::$directory . $file, $text, FILE_APPEND|LOCK_EX) === false ? false : true;
	}
	
	public static function sizeConvert($bytes, $precision=2, $unit='') {
		$bytes = intval($bytes);
		$precision = intval($precision);
		if ($bytes < 0 || $precision < 0) {
			return false;
		}
		!empty($unit) && $unit = strtoupper($unit);
		$array = array('B', 'KB', 'MB', 'GB', 'TB');
		if (!empty($unit)) {
			if (!in_array($unit, $array)) {
				return false;
			}
			$key = array_search($unit, $array);
			if ($key === false) {
				return false;
			}
		} else {
			$key = 0;
			for ($num=count($array); $key < $num; $key++) {
				if ($bytes <= pow(1024, $key+1)) {
					break;
				}
			}
		}
		$result = round($bytes/pow(1024, $key), $precision);
		return $result . ' ' . $array[$key];
	}
	
	public static function getSize($file) {
		return filesize(self::$directory . $file);
	}
	
	public static function getModified($file) {
		return filemtime(self::$directory . $file);
	}
	
}