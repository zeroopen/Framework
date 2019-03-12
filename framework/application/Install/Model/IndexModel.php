<?php

class IndexModel {
	
	public function checkPermission($path, &$is) {
		$dirs = array();
		foreach($path as $k => $v) {
			$is_read = File::isReadable($v);
			$is_write = File::isWritable($v);
			if (!$is_read || !$is_write) {
				$is = 0;
			}
			$dirs[$k] = array(
				'is_read' => $is_read,
				'is_write' => $is_write
			);
		}
		return $dirs;
	}
	
}