<?php
/***************************************************************
*  Copyright notice
*
*  (c) 2011 Thomas Maroschik <tmaroschik@dfau.de>
*  All rights reserved
*
*  This class is a mixup of the initial extbase class and the lexa-xml-serialization class meta
*  All credits go to the v5 team and http://code.google.com/p/lexa-xml-serialization/.
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
 * A schema generator
 *
 * @version $Id$
 * @license http://www.gnu.org/licenses/lgpl.html GNU Lesser General Public License, version 3 or later
 */
interface Tx_Palm_Configuration_ConfigurationManagerInterface {

	const CONFIGURATION_TYPE_FRAMEWORK = 'Framework';

	/**
	 * Returns the specified configuration.
	 * The actual configuration will be merged from different sources in a defined order.
	 *
	 * Note that this is a low level method and only makes sense to be used by Extbase internally.
	 *
	 * @param string $configurationType The kind of configuration to fetch - must be one of the CONFIGURATION_TYPE_* constants
	 * @return array The configuration
	 */
	public function getConfiguration($configurationType);

	/**
	 * Sets the specified raw configuration coming from the outside.
	 * Note that this is a low level method and only makes sense to be used by Extbase internally.
	 *
	 * @param array $configuration The new configuration
	 * @return void
	 */
	public function setConfiguration(array $configuration = array());

}
