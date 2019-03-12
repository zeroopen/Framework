<?php

class Cache {
	
	const MINUTE = 1;
	const HOUR = 60;
	const DAY = 1440;
	const MONTH = 43200;
	const YEAR = 525600;
	
	private static $cache_dir = '';
	private static $cache_info = 'cache_info.json';
	
	public static function directory($directory=null) {
		if (is_string($directory)) {
			if (File::exists($directory)) {
				self::$cache_dir = rtrim($directory, '/') . '/';
				return true;
			}
			return false;
		} else if (is_null($directory)) {
			if (empty(self::$cache_dir)) {
				return DATA_PATH . 'cache/';
			}
			return self::$cache_dir;
		}
	}
	
	private static function addInfo($name, $data_type, $expired_time) {
		$info = self::getInfo() ? : array();
		if (!empty($info[$name])) {
			File::delete(self::directory() . $info[$name]['key']);
		}
		$key = md5(__TIME__ . Random::string(18));
		$expired_time = empty($config['exp_time']) ? config::system('cache', 'time') : $expired_time;
		$info[$name] = array(
			'key' => $key,
			'data_type' => $data_type,
			'exp_time' => __TIME__ + 60*$expired_time
		);
		$cache_info = self::directory() . self::$cache_info;
		if (File::edit($cache_info, json_encode($info, true))) {
			return $key;
		}
		return false;
	}
	
	private static function getInfo($name=null) {
		$cache_info = self::directory() . self::$cache_info;
		if (!File::exists($cache_info)) {
			File::create($cache_info);
			return false;
		}
		$info = File::get($cache_info);
		if (!empty($info)) {
			$info = json_decode($info, true);
			if (empty($name)) {
				return $info;
			}
			if (!empty($info[$name])) {
				return $info[$name];
			}
		}
		return false;
	}
	
	public static function exists($name) {
		if (empty($name)) {
			return false;
		}
		$info = self::getInfo($name);
		if (empty($info)) {
			return false;
		}
		if (!File::exists(self::directory() . $info['key']) || $info['exp_time'] < __TIME__) {
			self::remove($name);
			return false;
		}
		return true;
	}
	
	public static function get($name) {
		if (self::exists($name)) {
			$info = self::getInfo($name);
			$data = File::get(self::directory() . $info['key']);
			switch ($info['data_type']) {
				case 'array':
					return json_decode($data, true);
				case 'object':
					return unserialize($data);
				case 'boolean':
					return (boolean) $data;
				case 'integer':
					return (int) $data;
				case 'double':
					return (float) $data;
				case 'string':
					return (string) $data;
				case 'NULL':
					return (unset) $data;
			}
		}
		return null;
	}
	
	public static function set($name, $data, $expired_time=null) {
		$data_type = gettype($data);
		if ($key = self::addInfo($name, $data_type, $expired_time)) {
			$file = self::directory() . $key;
			$content = '';
			switch ($data_type) {
				case 'array':
					$content = json_encode($data, true);
					break;
				case 'object':
					$content = serialize($data);
					break;
				case 'boolean':
				case 'integer':
				case 'double':
				case 'string':
					$content = (string) $data;
					break;
			}
			return File::edit($file, $content);
		}
		return false;
	}
	
	public static function remove($name) {
		$info = self::getInfo();
		if (!empty($info[$name])) {
			$cache_file = self::directory() . $info[$name]['key'];
			if (File::exists($cache_file) && !File::delete($cache_file)) {
				return false;
			}
			unset($info[$name]);
			$cache_info = self::directory() . self::$cache_info;
			return File::edit($cache_info, json_encode($info, true));
		}
		return false;
	}
	
	public static function clear($mode=false) {
		$dir = self::directory();
		if (is_dir($dir) && File::exists($dir.self::$cache_info)) {
			if ($dh = opendir($dir)) {
				if ($mode) {
					$array = array();
				} else {
					$array = array_column(self::getInfo(), 'key');
					array_unshift($array, self::$cache_info);
				}
				while (($file = readdir($dh)) !== false) {
					if (is_file($dir . $file)) {
						if (!in_array($file, $array)) {
							File::delete($dir . $file);
						}
					}
				}
				closedir($dh);
				return true;
			}
		}
		return false;
	}
	
}