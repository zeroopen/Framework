<?php

class ListController extends Controller {
	
	const MODEL = 'ArticleModel';
	
	public function index() {
		$list = $this->model->getList(1);
		var_export($list);
	}
	
}