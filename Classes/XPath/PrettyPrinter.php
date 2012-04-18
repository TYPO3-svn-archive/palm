<?php
/***************************************************************
 *  Copyright notice
 *
 *  (c) 2012 Thomas Maroschik <tmaroschik@dfau.de>
 *  All rights reserved
 *
 *  This script is part of the TYPO3 project. The TYPO3 project is
 *  free software; you can redistribute it and/or modify
 *  it under the terms of the GNU General Public License as published by
 *  the Free Software Foundation; either version 2 of the License, or
 *  (at your option) any later version.
 *
 *  The GNU General Public License can be found at
 *  http://www.gnu.org/copyleft/gpl.html.
 *  A copy is found in the textfile GPL.txt and important notices to the license
 *  from the author is found in LICENSE.txt distributed with these scripts.
 *
 *
 *  This script is distributed in the hope that it will be useful,
 *  but WITHOUT ANY WARRANTY; without even the implied warranty of
 *  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 *  GNU General Public License for more details.
 *
 *  This copyright notice MUST APPEAR in all copies of the script!
 ***************************************************************/

/**
 * Enter descriptions here
 *
 * @package $PACKAGE$
 * @subpackage $SUBPACKAGE$
 * @scope prototype
 * @entity
 * @api
 */
class Tx_Palm_XPath_PrettyPrinter {

	protected $output;

	public function prettyPrint(array $xPathArray) {
		$this->output = '';
		$this->printPath($xPathArray);
		return $this->output;
	}

	protected function printPath(array $xPathArray) {
		foreach ($xPathArray as $key => $value) {
			switch ($key) {
				case 'location':
					$this->printLocation($value, true);
					break;
				case 'operator':
					$this->printOperator($value);
					break;
				case 'literal':
					$this->printLiteral($value);
					break;
				case 'logical':
					$this->printLogical($value);
					break;
			}
		}
	}

	protected function printSubPath(array $xPathArray) {
		foreach ($xPathArray as $key => $value) {
			switch ($key) {
				case 'location':
					$this->printLocation($value);
					break;
				case 'operator':
					$this->printOperator($value);
					break;
				case 'literal':
					$this->printLiteral($value);
					break;
				case 'logical':
					$this->printLogical($value);
					break;
			}
		}
	}

	protected function printLocation(array $xPathArray, $prependSlash = false) {
		foreach ($xPathArray as $key => $value) {
			if (isset($value['axis'])) {
				if ($prependSlash) {
					$this->output .= '/';
				}
				$this->printPathPart($value);
			}
		}
	}

	protected function printPathPart(array $xPathArray) {
		switch ($xPathArray['axis']) {
			case 'descendant-or-self':
				$this->output .= '/';
				break;
			case 'child':
				$this->output .= '';
				break;
			case 'attribute':
				$this->output .= '@';
				break;
			case 'self':
				$this->output .= 'self::';
				break;
			case 'parent':
				$this->output .= 'parent::';
				break;
		}
		if (isset($xPathArray['localName'])) {
			$this->output .= $xPathArray['localName'];
		} else {
			$this->output .= '*';
		}
		if (isset($xPathArray['condition'])) {
			$this->printCondition($xPathArray['condition']);
		}
	}

	protected function printCondition(array $xPathArray) {
		if (!empty($xPathArray)) $this->output .= '[';
		foreach ($xPathArray as $value) {
			$this->printSubPath($value);
		}
		if (!empty($xPathArray)) $this->output .= ']';
	}

	protected function printOperator($operator) {
		$this->output .= $operator;
	}

	protected function printLiteral($literal) {
		$this->output .= "'" . $literal . "'";
	}

	protected function printLogical($logical) {
		$this->output .= ' ' . $logical . ' ';
	}
}
