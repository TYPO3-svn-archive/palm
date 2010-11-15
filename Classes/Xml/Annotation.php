<?php
//	namespace Lexa\XmlSerialization;

class Tx_Palm_Xml_Annotation {

	/** @return array */
	static function parse($docComment) {
		preg_match_all("/\@xml(element|attribute|root|value)\s*(?:\((.*?)\))?/is", $docComment, $matches, PREG_SET_ORDER);
		$result = array(); $description = '';
		$lines = explode(chr(10), $docComment);
		foreach ($lines as $line) {
			if (strlen($line) > 0 && strpos($line, '@') === FALSE) {
				$description .= preg_replace('/\s*\\/?[\\\*\/]*(.*)$/', '$1', $line) . chr(10);
			}
		}
		$description = trim($description);
		foreach($matches as $match) {
			$annotation = new Tx_Palm_Xml_Annotation;
			$annotation->description = $description;
			$annotation->name = strtolower($match[1]);
			if(count($match) > 2)
				$annotation->params = preg_split("/\s*\,\s*/", trim($match[2]), -1, PREG_SPLIT_NO_EMPTY);
			$result[] = $annotation;
		}
		return $result;
	}

	private $name;
	private $description;
	private $params;

	protected function __construct() {
	}

	function getName() {
		return $this->name;
	}

	function getDescription() {
		return $this->description;
	}

	function getParamCount() {
		return count($this->params);
	}

	function getParam($index) {
		return $this->params[$index];
	}

}

?>