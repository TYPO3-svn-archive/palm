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
 * A view helper for creating URIs to extbase actions.
 *
 * = Examples =
 *
 * <code title="URI to the show-action of the current controller">
 * <f:uri.action action="show" />
 * </code>
 * <output>
 * index.php?id=123&tx_myextension_plugin[action]=show&tx_myextension_plugin[controller]=Standard&cHash=xyz
 * (depending on the current page and your TS configuration)
 * </output>
 *
 * @license http://www.gnu.org/licenses/lgpl.html GNU Lesser General Public License, version 3 or later
 *
 */
class Tx_Palm_ViewHelpers_Uri_ActionViewHelper extends Tx_Fluid_ViewHelpers_Uri_ActionViewHelper {

	/**
	 * @param Tx_Palm_Merger_AbstractRule $rule
	 * @param string $action Target action
	 * @param array $arguments Arguments
	 * @param string $controller Target controller. If NULL current controllerName is used
	 * @param string $extensionName Target Extension Name (without "tx_" prefix and no underscores). If NULL the current extension name is used
	 * @param string $pluginName Target plugin. If empty, the current plugin name is used
	 * @param integer $pageUid target page. See TypoLink destination
	 * @param integer $pageType type of the target page. See typolink.parameter
	 * @param boolean $noCache set this to disable caching for the target page. You should not need this.
	 * @param boolean $noCacheHash set this to supress the cHash query parameter created by TypoLink. You should not need this.
	 * @param string $section the anchor to be added to the URI
	 * @param string $format The requested format, e.g. ".html"
	 * @param boolean $linkAccessRestrictedPages If set, links pointing to access restricted pages will still link to the page even though the page cannot be accessed.
	 * @param array $additionalParams additional query parameters that won't be prefixed like $arguments (overrule $arguments)
	 * @param boolean $absolute If set, an absolute URI is rendered
	 * @param boolean $addQueryString If set, the current query parameters will be kept in the URI
	 * @param array $argumentsToBeExcludedFromQueryString arguments to be removed from the URI. Only active if $addQueryString = TRUE
	 * @return string Rendered link
	 * @author Sebastian Kurfürst <sebastian@typo3.org>
	 * @author Bastian Waidelich <bastian@typo3.org>
	 */
	public function render(Tx_Palm_Merger_AbstractRule $rule, $action = NULL, array $arguments = array(), $controller = NULL, $extensionName = NULL, $pluginName = NULL, $pageUid = NULL, $pageType = 0, $noCache = FALSE, $noCacheHash = FALSE, $section = '', $format = '', $linkAccessRestrictedPages = FALSE, array $additionalParams = array(), $absolute = FALSE, $addQueryString = FALSE, array $argumentsToBeExcludedFromQueryString = array()) {
		$entityType = $rule->getEntityName();
		if (!empty($arguments)) {
			foreach ($arguments as $argumentName => $argumentValue) {
				if ($argumentValue instanceof $entityType) {
					$arguments[$argumentName] = Tx_Extbase_Reflection_ObjectAccess::getPropertyPath($argumentValue, $rule->getMatchOn());
				}
			}
		}
		return parent::render(
			$action,
			$arguments,
			$controller,
			$extensionName,
			$pluginName,
			$pageUid,
			$pageType,
			$noCache,
			$noCacheHash,
			$section,
			$format,
			$linkAccessRestrictedPages,
			$additionalParams,
			$absolute,
			$addQueryString,
			$argumentsToBeExcludedFromQueryString
		);
	}
}
?>