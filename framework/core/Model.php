<?php

class Model {
	
	const TABLE = '';
	
	protected $db;
	
	public function __construct() {
		$this->db = db::init();
		$this->_initialize();
		if (static::TABLE != '') {
			$this->db->table(static::TABLE);
		}
	}
	
	protected function _initialize() {}
	
}
