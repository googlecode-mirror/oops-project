<?php

class Oops_Sql_Pager {
	public $limit;
	public $start;
	public $sort;
	public $dir = Oops_Sql_Selector::ORDER_ASC;

	public function __construct(array $params) {
		$this->limit = intval($params['limit']);
		$this->start = intval($params['start']);
		
		if(isset($params['sort'])) $this->sort = $params['sort'];
		if(isset($params['dir']) && strtolower($params['dir']) == strtolower(Oops_Sql_Selector::ORDER_DESC)) $this->dir = Oops_Sql_Selector::ORDER_DESC;
	}
}