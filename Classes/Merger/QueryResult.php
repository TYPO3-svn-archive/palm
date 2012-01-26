<?php
/***************************************************************
*  Copyright notice
*
*  (c) 2010 Bastian Waidelich <bastian@typo3.org>
*  All rights reserved
*
*  This class is a backport of the corresponding class of FLOW3.
*  All credits go to the v5 team.
*
*  This script is part of the TYPO3 project. The TYPO3 project is
*  free software; you can redistribute it and/or modify
*  it under the terms of the GNU General Public License as published by
*  the Free Software Foundation; either version 2 of the License, or
*  (at your option) any later version.
*
*  The GNU General Public License can be found at
*  http://www.gnu.org/copyleft/gpl.html.
*
*  This script is distributed in the hope that it will be useful,
*  but WITHOUT ANY WARRANTY; without even the implied warranty of
*  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
*  GNU General Public License for more details.
*
*  This copyright notice MUST APPEAR in all copies of the script!
***************************************************************/

/**
 * A lazy result list that is returned by Query::execute()
 *
 * @package Extbase
 * @subpackage Persistence
 * @scope prototype
 * @api
 */
class Tx_Palm_Merger_QueryResult implements Tx_Extbase_Persistence_QueryResultInterface {

	/**
	 * @var Tx_Palm_Merger_Query
	 */
	protected $query;

	/**
	 * @var DOMNodeList
	 */
	protected $queryResults;

	/**
	 * @var array
	 * @transient
	 */
	protected $records = Array();

	/**
	 * @var Tx_Extbase_Object_ObjectManager
	 */
	protected $objectManager;

	/**
	 * @var Tx_Palm_Xml_Serializer
	 */
	protected $xmlSerializer;

	/**
	 * @var Tx_Palm_DOM_Document
	 */
	protected $dom;

	/**
	 * @var int
	 */
	protected $domNodeCount = 0;

	/**
	 * @var array
	 */
	protected $domNodeList = Array();

	/**
	 * @var string
	 */
	protected $domNodeListStatement;

	/**
	 * Constructor
	 *
	 * @param Tx_Extbase_Persistence_QueryInterface $query
	 */
	public function __construct(Tx_Palm_Merger_Query $query) {
		$this->query = $query;
	}

	/**
	 * Injector method for a Tx_Extbase_Object_ObjectManager
	 *
	 * @param Tx_Extbase_Object_ObjectManager
	 */
	public function injectObjectManager(Tx_Extbase_Object_ObjectManager $objectManager) {
		$this->objectManager = $objectManager;
	}

	/**
	 * Injector method for a xml serializer
	 *
	 * @param Tx_Palm_Xml_Serializer $xmlSerializer
	 */
	public function injectXmlSerializer(Tx_Palm_Xml_Serializer $xmlSerializer) {
		$this->xmlSerializer = $xmlSerializer;
	}

	/**
	 * @return void
	 */
	protected function initializeDOM() {
		if ($this->dom === null) {
			/** @var $dom Tx_Palm_DOM_Document */
			if ($this->query->getSource() instanceof Tx_Palm_Merger_FileLocation) {
				$dom = $this->objectManager->create('Tx_Palm_DOM_Document');
				$dom->load($this->query->getSource()->getAbsoluteLocation());
				$this->dom = $dom;
				$this->domNodeList = Array();
				$this->domNodeListStatement = null;
				$this->records = Array();
			}
		}
	}


	/**
	 * @return void
	 */
	protected function initializeDOMNodeList() {
		$statementObject = $this->query->getStatement();
		$statement = ($statementObject instanceof Tx_Extbase_Persistence_QOM_Statement ) ? $statementObject->getStatement() . '###' . $this->query->getOffset() . '###' . $this->query->getLimit() : '';
		if (
			$this->dom !== null
			&& $statement !== $this->domNodeListStatement
		) {
			/** @var $domXPath DOMXPath */
			$domXPath = $this->objectManager->create('DOMXPath', $this->dom);
			$this->domNodeList = Array();
			$this->records = Array();
			$this->domNodeListStatement = $statement;
			$domNodeList = Array();
			foreach ($domXPath->query($statementObject->getStatement()) as $node) {
				/** @var $node DOMNode */
				$domNodeList[$node->getNodePath()] = $node;
			}
			$this->domNodeCount = count($domNodeList);
			$offset = ($this->query->getOffset() > 0) ? $this->query->getOffset() : 0;
			$limit = ($this->query->getLimit() > 0) ? $this->query->getLimit() : $this->domNodeCount;
			var_dump($offset, $limit);
			$this->domNodeList = array_slice($domNodeList, $offset, $limit);
			var_dump($this->domNodeList);
		}
	}

	/**
	 * @param DOMNode $node
	 * @return mixed
	 */
	protected function getMappedRecordForNode(DOMNode $node) {
		/** @var $nodePath string */
		$nodePath = $node->getNodePath();
		if (!isset($this->records[$nodePath])) {
			/** @var Tx_Palm_DOM_Document $doc */
			$doc = $this->objectManager->create('Tx_Palm_DOM_Document');
			$doc->appendChild($doc->importNode($node, true));
			$this->records[$nodePath] = $this->xmlSerializer->unserialize($doc, $this->query->getType());
		}
		return isset($this->records[$nodePath]) ? $this->records[$nodePath] : NULL;
	}

	/**
	 * Returns a clone of the query object
	 *
	 * @return Tx_Palm_Persistence_Query
	 * @api
	 */
	public function getQuery() {
		return $this->query;
	}

	/**
	 * Returns the first object in the result set
	 *
	 * @return object
	 * @api
	 */
	public function getFirst() {
		$this->initializeDOM();
		$this->initializeDOMNodeList();
		reset($this->domNodeList);
		$first = current($this->domNodeList);
		if ($first) {
			return $this->getMappedRecordForNode($first);
		}
		return NULL;
	}

	/**
	 * Returns the number of objects in the result
	 *
	 * @return integer The number of matching objects
	 * @api
	 */
	public function count() {
		$this->initializeDOM();
		$this->initializeDOMNodeList();
		return $this->domNodeCount;
	}

	/**
	 * Returns an array with the objects in the result set
	 *
	 * @return array
	 * @api
	 */
	public function toArray() {
		$this->initializeDOM();
		$this->initializeDOMNodeList();
		$result = Array();
		foreach ($this->domNodeList as $node) {
			$result[] = $this->getMappedRecordForNode($node);
		}
		return $result;
	}

	/**
	 * This method is needed to implement the ArrayAccess interface,
	 * but it isn't very useful as the offset has to be an integer
	 *
	 * @param mixed $offset
	 * @return boolean
	 * @see ArrayAccess::offsetExists()
	 */
	public function offsetExists($offset) {
		$this->initializeDOM();
		$this->initializeDOMNodeList();
		return isset($this->domNodeList[$offset]);
	}

	/**
	 * @param mixed $offset
	 * @return mixed
	 * @see ArrayAccess::offsetGet()
	 */
	public function offsetGet($offset) {
		$this->initializeDOM();
		$this->initializeDOMNodeList();
		return isset($this->domNodeList[$offset]) ? $this->getMappedRecordForNode($this->domNodeList[$offset]) : NULL;
	}

	/**
	 * This method has no effect on the persisted objects but only on the result set
	 *
	 * @param mixed $offset
	 * @param mixed $value
	 * @return void
	 * @see ArrayAccess::offsetSet()
	 */
	public function offsetSet($offset, $value) {
		// TODO: elaborate on a read and write query result
		// $this->records[$offset] = $value;
	}

	/**
	 * This method has no effect on the persisted objects but only on the result set
	 *
	 * @param mixed $offset
	 * @return void
	 * @see ArrayAccess::offsetUnset()
	 */
	public function offsetUnset($offset) {
		// TODO: elaborate on a read and write query result
		// unset($this->records[$offset]);
	}

	/**
	 * @return mixed
	 * @see Iterator::current()
	 */
	public function current() {
		$this->initializeDOM();
		$this->initializeDOMNodeList();
		return $this->getMappedRecordForNode(current($this->domNodeList));
	}

	/**
	 * @return mixed
	 * @see Iterator::key()
	 */
	public function key() {
		$this->initializeDOM();
		$this->initializeDOMNodeList();
		return key($this->domNodeList);
	}

	/**
	 * @return void
	 * @see Iterator::next()
	 */
	public function next() {
		$this->initializeDOM();
		$this->initializeDOMNodeList();
		next($this->domNodeList);
	}

	/**
	 * @return void
	 * @see Iterator::rewind()
	 */
	public function rewind() {
		$this->initializeDOM();
		$this->initializeDOMNodeList();
		reset($this->domNodeList);
	}

	/**
	 * @return bool
	 * @see Iterator::valid()
	 */
	public function valid() {
		$this->initializeDOM();
		$this->initializeDOMNodeList();
		return current($this->domNodeList) !== FALSE;
	}

}
?>