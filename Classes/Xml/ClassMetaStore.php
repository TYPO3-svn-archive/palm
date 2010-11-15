<?php
//namespace Lexa\XmlSerialization;

class Tx_Palm_Xml_ClassMetaStore {
	private static $instance;

	/** @return Tx_Palm_Xml_ClassMetaStore */
	static function get() {
		if(!self::$instance)
			self::$instance = new Tx_Palm_Xml_ClassMetaStore();
		return self::$instance;
	}

	static function set(Tx_Palm_Xml_ClassMetaStore $store) {
		self::$instance = $store;
	}

	/** @return Tx_Palm_Xml_ClassMeta */
	static function getMeta($class) {
		if(is_object($class))
			$class = get_class($class);
		$store = self::get();
		$meta = $store->getMetaCore($class);
		if(!$meta) {
			$meta = new Tx_Palm_Xml_ClassMeta($class);
			$store->registerMeta($meta);
		}
		return $meta;
	}

	protected $data = array();

	protected function getMetaCore($className) {
		$key = $this->getKey($className);
		if(array_key_exists($key, $this->data))
			return $this->data[$key];
		return null;
	}

	protected function registerMeta(Tx_Palm_Xml_ClassMeta $meta) {
		$key = $this->getKey($meta->getClassName());
		$this->data[$key] = $meta;
	}

	protected function getKey($className) {
		return ltrim(strtolower($className), "\\");
	}

}
?>