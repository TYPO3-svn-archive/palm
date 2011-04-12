<?php
/***************************************************************
 *  Copyright notice
 *
 *  (c) 2011 Thomas Maroschik <tmaroschik@dfau.de>
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
 * Enter descriptions here
 *
 * @package $PACKAGE$
 * @subpackage $SUBPACKAGE$
 * @scope prototype
 * @entity
 * @api
 */
class Tx_Palm_Configuration_FrontendConfigurationManager extends Tx_Palm_Configuration_AbstractConfigurationManager {

	/**
	 * Returns TypoScript Setup array from current Environment.
	 *
	 * @return array the raw TypoScript setup
	 */
	public function getTypoScriptSetup() {
		return $GLOBALS['TSFE']->tmpl->setup;
	}

	/**
	 * Returns the TypoScript configuration found in plugin.tx_yourextension_yourplugin
	 * merged with the global configuration of your extension from plugin.tx_yourextension
	 *
	 * @param string $extensionName
	 * @param string $pluginName
	 * @return array
	 */
	protected function getPluginConfiguration($extensionName, $pluginName) {
		$setup = $this->getTypoScriptSetup();
		$pluginConfiguration = array();
		if (is_array($setup['plugin.']['tx_' . strtolower($extensionName) . '.'])) {
			$pluginConfiguration = Tx_Extbase_Utility_TypoScript::convertTypoScriptArrayToPlainArray($setup['plugin.']['tx_' . strtolower($extensionName) . '.']);
		}
		$pluginSignature = strtolower($extensionName . '_' . $pluginName);
		if (is_array($setup['plugin.']['tx_' . $pluginSignature . '.'])) {
			$pluginConfiguration = t3lib_div::array_merge_recursive_overrule($pluginConfiguration, Tx_Extbase_Utility_TypoScript::convertTypoScriptArrayToPlainArray($setup['plugin.']['tx_' . $pluginSignature . '.']));
		}
		return $pluginConfiguration;
	}

	/**
	 * Get context specific framework configuration.
	 * - Overrides storage PID with setting "Startingpoint"
	 * - merge flexform configuration, if needed
	 *
	 * @param array $frameworkConfiguration The framework configuration to modify
	 * @return array the modified framework configuration
	 */
	protected function getContextSpecificFrameworkConfiguration(array $frameworkConfiguration) {
		$frameworkConfiguration = $this->overrideConfigurationFromPlugin($frameworkConfiguration);
		$frameworkConfiguration = $this->overrideConfigurationFromFlexform($frameworkConfiguration);

		return $frameworkConfiguration;
	}

	/**
	 * Overrides configuration settings from the plugin typoscript (plugin.tx_myext_pi1.)
	 *
	 * @param array the framework configuration
	 * @return array the framework configuration with overridden data from typoscript
	 */
	protected function overrideConfigurationFromPlugin(array $frameworkConfiguration) {
		$setup = $this->getTypoScriptSetup();
		$pluginSignature = strtolower($frameworkConfiguration['extensionName'] . '_' . $frameworkConfiguration['pluginName']);
		$pluginConfiguration = $setup['plugin.']['tx_' . $pluginSignature . '.'];
		if (is_array($pluginConfiguration)) {
			$pluginConfiguration = Tx_Extbase_Utility_TypoScript::convertTypoScriptArrayToPlainArray($pluginConfiguration);
			$frameworkConfiguration = $this->mergeConfigurationIntoFrameworkConfiguration($frameworkConfiguration, $pluginConfiguration, 'mapping');
		}
		return $frameworkConfiguration;
	}

	/**
	 * Overrides configuration settings from flexforms.
	 * This merges the whole flexform data.
	 *
	 * @param array the framework configuration
	 * @return array the framework configuration with overridden data from flexform
	 */
	protected function overrideConfigurationFromFlexform(array $frameworkConfiguration) {
		if (strlen($this->contentObject->data['pi_flexform']) > 0) {
			$flexformConfiguration = $this->convertFlexformContentToArray($this->contentObject->data['pi_flexform']);
			$frameworkConfiguration = $this->mergeConfigurationIntoFrameworkConfiguration($frameworkConfiguration, $flexformConfiguration, 'mapping');
		}
		return $frameworkConfiguration;
	}

	/**
	 * Parses the FlexForm content and converts it to an array
	 * The resulting array will be multi-dimensional, as a value "bla.blubb"
	 * results in two levels, and a value "bla.blubb.bla" results in three levels.
	 *
	 * Note: multi-language FlexForms are not supported yet
	 *
	 * @param string $flexFormContent FlexForm xml string
	 * @return array the processed array
	 */
	protected function convertFlexformContentToArray($flexFormContent) {
		$settings = array();
		$languagePointer = 'lDEF';
		$valuePointer = 'vDEF';

		$flexFormArray = t3lib_div::xml2array($flexFormContent);
		$flexFormArray = isset($flexFormArray['data']) ? $flexFormArray['data'] : array();
		foreach(array_values($flexFormArray) as $languages) {
			if (!is_array($languages[$languagePointer])) {
				continue;
			}

			foreach($languages[$languagePointer] as $valueKey => $valueDefinition) {
				if (strpos($valueKey, '.') === false) {
					$settings[$valueKey] = $this->walkFlexformNode($valueDefinition, $valuePointer);
				} else {
					$valueKeyParts = explode('.', $valueKey);
					$currentNode =& $settings;
					foreach ($valueKeyParts as $valueKeyPart) {
						$currentNode =& $currentNode[$valueKeyPart];
					}
					if (is_array($valueDefinition)) {
						if (array_key_exists($valuePointer, $valueDefinition)) {
							$currentNode = $valueDefinition[$valuePointer];
						} else {
							$currentNode = $this->walkFlexformNode($valueDefinition, $valuePointer);
						}
					} else {
						$currentNode = $valueDefinition;
					}
				}
			}
		}
		return $settings;
	}

	/**
	 * Parses a flexform node recursively and takes care of sections etc
	 * @param array $nodeArray The flexform node to parse
	 * @param string $valuePointer The valuePointer to use for value retrieval
	 */
	protected function walkFlexformNode($nodeArray, $valuePointer = 'vDEF') {
		if (is_array($nodeArray)) {
			$return = array();

			foreach ($nodeArray as $nodeKey => $nodeValue) {
				if ($nodeKey === $valuePointer) {
					return $nodeValue;
				}

				if (in_array($nodeKey, array('el', '_arrayContainer'))) {
					return $this->walkFlexformNode($nodeValue, $valuePointer);
				}

				if (substr($nodeKey, 0, 1) === '_') {
					continue;
				}

				if (strpos($nodeKey, '.')) {
					$nodeKeyParts = explode('.', $nodeKey);
					$currentNode =& $return;
					for ($i = 0; $i < count($nodeKeyParts) - 1; $i++) {
						$currentNode =& $currentNode[$nodeKeyParts[$i]];
					}
					$newNode = array(next($nodeKeyParts) => $nodeValue);
					$currentNode = $this->walkFlexformNode($newNode, $valuePointer);
				} else if (is_array($nodeValue)) {
					if (array_key_exists($valuePointer, $nodeValue)) {
						$return[$nodeKey] = $nodeValue[$valuePointer];
					} else {
						$return[$nodeKey] = $this->walkFlexformNode($nodeValue, $valuePointer);
					}
				} else {
					$return[$nodeKey] = $nodeValue;
				}
			}
			return $return;
		}

		return $nodeArray;
	}

	/**
	 * Merge a configuration into the framework configuration.
	 *
	 * @param array $frameworkConfiguration the framework configuration to merge the data on
	 * @param array $configuration The configuration
	 * @param string $configurationPartName The name of the configuration part which should be merged.
	 * @return array the processed framework configuration
	 */
	protected function mergeConfigurationIntoFrameworkConfiguration(array $frameworkConfiguration, array $configuration, $configurationPartName) {
		if (is_array($frameworkConfiguration[$configurationPartName]) && is_array($configuration[$configurationPartName])) {
			$frameworkConfiguration[$configurationPartName] = t3lib_div::array_merge_recursive_overrule($frameworkConfiguration[$configurationPartName], $configuration[$configurationPartName]);
		}
		return $frameworkConfiguration;
	}

}
