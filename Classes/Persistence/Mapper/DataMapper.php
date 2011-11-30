<?php
/***************************************************************
 *  Copyright notice
 *
 *  (c) 2009 Jochen Rau <jochen.rau@typoplanet.de>
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
 *
 *  This script is distributed in the hope that it will be useful,
 *  but WITHOUT ANY WARRANTY; without even the implied warranty of
 *  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 *  GNU General Public License for more details.
 *
 *  This copyright notice MUST APPEAR in all copies of the script!
 ***************************************************************/

/**
 * A mapper to map database tables configured in $TCA on domain objects.
 *
 * @package Extbase
 * @subpackage Persistence\Mapper
 * @version $ID:$
 */
class Tx_Palm_Persistence_Mapper_DataMapper extends Tx_Extbase_Persistence_Mapper_DataMapper {

	/**
	 *
	 * @var bool
	 */
	protected $enableLazyLoading = TRUE;

	/**
	 * Fetches a collection of objects related to a property of a parent object
	 *
	 * @param Tx_Extbase_DomainObject_DomainObjectInterface $parentObject The object instance this proxy is part of
	 * @param string $propertyName The name of the proxied property in it's parent
	 * @param mixed $fieldValue The raw field value.
	 * @param bool $enableLazyLoading A flag indication if the related objects should be lazy loaded
	 * @param bool $performLanguageOverlay A flag indication if the related objects should be localized
	 * @return Tx_Extbase_Persistence_LazyObjectStorage|Tx_Extbase_Persistence_QueryResultInterface The result
	 */
	public function fetchRelated(Tx_Extbase_DomainObject_DomainObjectInterface $parentObject, $propertyName, $fieldValue = '', $enableLazyLoading = null) {
		if($enableLazyLoading === null) {
			$enableLazyLoading = $this->enableLazyLoading;
		}
		return parent::fetchRelated($parentObject, $propertyName, $fieldValue, $enableLazyLoading);
	}

	/**
	 * Sets enableLazyLoading
	 *
	 * @param bool $enableLazyLoading
	 * @return void
	 */
	public function setEnableLazyLoading($enableLazyLoading) {
		$this->enableLazyLoading = (bool) $enableLazyLoading;
	}

	/**
	 * Returns enableLazyLoading
	 *
	 * @return bool
	 */
	public function getEnableLazyLoading() {
		return $this->enableLazyLoading;
	}


}