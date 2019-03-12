<?php

class Url {
	
	public static function parse() {
		$result = array();
		$array = explode('/', self::getPath(REQUEST_URI));
		$e = 1;
		if (Framework::moduleState()) {
			$result['module'] = !empty($array[$e]) ? ucfirst(strtolower($array[$e])) : '';
			$e++;
		}
		$result['action'] = !empty($array[$e]) ? ucfirst(strtolower($array[$e])) : '';
		return $result;
	}
	
	public static function get($module=null, $action=null, $do=null) {
		$url = URL_REWRITE ? '/' : '/index.php/';
		$url .= empty($module) ? 'index/' : $module.'/';
		$url .= empty($action) ? 'index/' : $action.'/';
		if (!empty($do)) {
			if (is_string($do)) {
				$array[Controller::DO_KEY] = $do;
			} else if (is_array($do)) {
				$array = $do;
			}
			if (!empty($array)) {
				$url .= '?'.http_build_query($array);
			}
		}
		return $url;
	}
	
	public static function jump($module, $action=null) {
		header('location: ' . self::get($module, $action), true, 301);
		exit;
	}
	
	protected static function getPath($url) {
		$array = parse_url($url);
		return $array['path'];
	}
	
}