<?php

class Oops_Cache_Mapstore_Mongodb extends Oops_Cache_Mapstore_Abstract {
	
	/**
	 * @var MongoCollection
	 */
	private $_collection;
	
	/**
	 * 
	 * @param Oops_Config|MongoCollection $config
	 */
	public function __construct($config = null) {
		if($config instanceof MongoCollection) $this->_collection = $config;
		else {
			$db = null;
			$coll = null;
			$client = null;
			if($config instanceof Oops_Config) $config = $config->__toArray();
			if(is_array($config)) {
				if(isset($config['db'])) $db = $config['db'];
				if(isset($config['collection'])) $coll = $config['collection'];
				
				if(isset($config['server'])) {
					if(isset($config['options'])) $client = new MongoClient($config['server'], $config['options']);
					else $client = new MongoClient($config['server']);
				}
			}
			
			if(!isset($client)) $client = new MongoClient;
			if(!isset($db)) $db = 'cascache';
			if(!isset($coll)) $coll = 'mapstore';
			
			$this->_collection = $client->$db->$coll;
			$this->_collection->ensureIndex('source');
		}
	}
	
	public function store($target, $sources) {
		$this->_collection->save(array('_id' => $target, 'source' => $sources));
	}
	
	public function find($source) {
		$targets = $this->_collection->find(array('source' => $source), array('source' => false));
		$ret = array();
		foreach($targets as $t) $ret[] = (string) $t['_id'];
		return $ret; 
	}
	
	public function drop($target) {
		$this->_collection->remove(array('_id' => $target));
	}
	
}