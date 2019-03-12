<?php

class Config {
	
	private static $config = array();
	
	public static function __callstatic($name, $arguments) {
		if (isset(self::$config[$name])) {
			return self::getValue($name, $arguments);
		}
	}
	
	public static function load($name, $mode=false) {
		if (file_exists(self::getFilePath($name))) {
			$config = (require self::getFilePath($name));
			if ($mode) {
				return $config;
			} else {
				self::$config[$name] = $config;
			}
			return true;
		} else {
			return false;
		}
	}
	
	public static function remove($name) {
		if (isset(self::$config[$name])) {
			unset(self::$config[$name]);
			return true;
		}
		return false;
	}
	
	public static function update($name, $config, $mode=false) {
		if (isset(self::$config[$name])) {
			self::$config[$name] = array_merge(self::$config[$name], $config);
			if ($mode) {
				$conf = "<?php\n\nreturn " . var_export(self::$config[$name], true) . ";";
				$conf = preg_replace("/=> \s{0,}array/", "=> array", $conf);
				$conf = str_replace('array (', 'array(', $conf);
				$conf = str_replace('  ', "\t", $conf);
				file_put_contents(self::getFilePath($name), $conf);
			}
		}
	}
	
	private static function getFilePath($name) {
		return CONF_PATH . $name . '.config.php';
	}
	
	private static function getValue($name, $keys=array()) {
		$array = self::$config[$name];
		if (!empty($keys)) {
			foreach ($keys as $v) {
				if (!isset($array[$v])) return null;
				$array = $array[$v];
			}
		}
		return $array;
	}
	
}
