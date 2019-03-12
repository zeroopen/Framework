<?php

class ArticleModel extends Model {
	
	const TABLE = 'article';
	
	public function getList($p) {
		return $this->db->table(self::TABLE)->order('id DESC')->page($p, PAGESIZE)->select();
	}
	
	public function getInfo($id) {
		
	}
	
}