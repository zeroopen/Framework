<?php

class Log {
	
	const DEBUG = 10;
	const INFO = 9;
	const NOTICE = 7;
	const WARNING = 6;
	const EXCEPTION = 5;
	const ILLEGAL = 4;
	const ERROR = 3;
	const CRITICAL = 2;
	const FATAL = 1;
	
	private static function logFile() {
		return DATA_PATH . 'log/log_' . date('Ym').'.log';
	}
	
	private static function levelToString($level) {
		switch ($level) {
			case self::DEBUG:
				return 'Debug';
			case self::INFO:
				return 'Info';
			case self::NOTICE:
				return 'Notice';
			case self::WARNING:
				return 'Warning';
			case self::EXCEPTION:
				return 'Exception';
			case self::ILLEGAL:
				return 'Illegal';
			case self::ERROR:
				return 'Error';
			case self::CRITICAL:
				return 'Critical';
			case self::FATAL:
				return 'Fatal';
			default:
				return $level;
		}
	}
	
	public static function record($level, $message) {
		if (!LOG || ($level > config::system('log', 'level'))) {
			return;
		}
		$log_file = self::logFile();
		if (is_array($message)) {
			$message = var_export($message, true);
		}
		$log = date('m/d H:i') . '  ' . self::levelToString($level) . " :{$message}\r\n";
		File::append($log_file, $log);
	}
	
	public static function error($level, $message, $file, $line) {
		$log = "{$message} in {$file} on line {$line}";
		self::record(self::ERROR, $log);
		$level != 2 && exit;
	}
	
	public static function lastError() {
		$error = error_get_last();
		if (!empty($error['message'])) {
			self::error($error['type'], $error['message'], $error['file'], $error['line']);
		}
	}
	
	public static function exception($exception) {
		self::record(self::EXCEPTION, $exception);
	}
	
}