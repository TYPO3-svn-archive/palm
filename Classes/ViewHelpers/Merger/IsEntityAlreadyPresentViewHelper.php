<?php

/*                                                                        *
 * This script is part of the TYPO3 project - inspiring people to share!  *
 *                                                                        *
 * TYPO3 is free software; you can redistribute it and/or modify it under *
 * the terms of the GNU General Public License version 2 as published by  *
 * the Free Software Foundation.                                          *
 *                                                                        *
 * This script is distributed in the hope that it will be useful, but     *
 * WITHOUT ANY WARRANTY; without even the implied warranty of MERCHAN-    *
 * TABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the GNU General      *
 * Public License for more details.                                       *
 *                                                                        */

/**
 *
 */
class Tx_Palm_ViewHelpers_Merger_IsEntityAlreadyPresentViewHelper extends Tx_Fluid_Core_ViewHelper_AbstractViewHelper {


	/**
	 * @var Tx_Palm_Merger_Service
	 */
	protected $mergerService;

	/**
	 * Injector method for a merger service
	 *
	 * @param Tx_Palm_Merger_Service $mergerService
	 */
	public function injectMergerService(Tx_Palm_Merger_Service $mergerService) {
		$this->mergerService = $mergerService;
	}


	/**
	 * The render method
	 *
	 * @param Tx_Palm_Merger_Rule $rule
	 * @param Tx_Extbase_DomainObject_AbstractDomainObject $item
	 * @return boolean
	 */
	public function render(Tx_Palm_Merger_RootRule $rule, Tx_Extbase_DomainObject_AbstractDomainObject $item) {
		return (bool) $this->mergerService->isEntityAlreadyPresent($rule, $item);
	}
}


?>