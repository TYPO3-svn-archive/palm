<?php

/***************************************************************
*  Copyright notice
*
*  (c) 2010 Thomas Maroschik <tmaroschik@dfau.de>
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
 * The Backend Controller
 * @version $Id:$
 * @license http://opensource.org/licenses/gpl-license.php GNU Public License, version 2
 */
class Tx_Palm_Controller_PullDataController extends Tx_Extbase_MVC_Controller_ActionController {

	/**
	 * @var Tx_Palm_Merger_ServiceInterface
	 */
	protected $mergerService;

	/**
	 * Injector method for a merger service
	 *
	 * @param Tx_Palm_Merger_ServiceInterface $mergerService
	 */
	public function injectMergerService(Tx_Palm_Merger_ServiceInterface $mergerService) {
		$this->mergerService = $mergerService;
	}

	public function indexAction() {
		$pullableEntities = $this->mergerService->getPullableEntities();
		$entityTable = array();
		foreach($pullableEntities as $entityName=>$directiveCount) {
			$entityTable[] = array($entityName, $directiveCount);
		}
		$this->view->assign('pid', $this->getCurrentPid());
		$this->view->assign('entities', $entityTable);
	}

	/**
	 * The list action
	 *
	 * @param string $entityName
	 */
	public function listAction($entityName) {
		$rules = $this->mergerService->getPullRulesByEntityName($entityName);
		$rulesTable = array();
		foreach ($rules as $fileLocation=>$rule) {
			$rulesTable[] = array($fileLocation, $rule->getSinglePathInCollection());
		}
		$this->view->assign('pid', $this->getCurrentPid());
		$this->view->assign('entityName', $entityName);
		$this->view->assign('rules', $rulesTable);
	}

	/**
	 * The show action
	 *
	 * @param string $fileLocation
	 * @param integer $currentPage
	 */
	public function selectRecordAction($fileLocation, $currentPage = 1) {
		$rule = $this->mergerService->getPullRuleByFileLocation($fileLocation);
		$repository = $this->getRepositoryFromRule($rule);
		$repository->setDefaultOrderings(Array(
			'uid' => Tx_Extbase_Persistence_QueryInterface::ORDER_ASCENDING,
		));
		
		$this->view->assign('entityName', $rule->getEntityName());
		$this->view->assign('rule', $rule);
		$this->view->assign('propertyPath', $this->mergerService->getPropertyPathFromRule($rule));
		$this->view->assign('items', $repository->findAll());
	}


	/**
	 * Enter description here ...
	 *
	 * @param string $fileLocation
	 * @param int $record
	 */
	public function mergeRecordAction($fileLocation, $record) {
		$container = t3lib_div::makeInstance('Tx_Extbase_Object_Container_Container');
		$container->registerImplementation('Tx_Extbase_Persistence_Typo3QuerySettings', 'Tx_Palm_Persistence_MergerQuerySettings');
		$this->objectManager->get('Tx_Palm_Persistence_Mapper_DataMapper')->setEnableLazyLoading(false);
		$rule = $this->mergerService->getPullRuleByFileLocation($fileLocation);
		$repository = $this->getRepositoryFromRule($rule);

		$entity = $repository->findByUid($record);
		$this->mergerService->mergeByRule($entity, $rule);
		$repository->update($entity);
		
		$this->objectManager->get('Tx_Extbase_Persistence_Manager')->persistAll();

		$this->flashMessageContainer->add('The record with the uid ' . $record . ' has been successfully merged!', t3lib_FlashMessage::OK);

		$this->redirectToURI(t3lib_div::sanitizeLocalUrl(t3lib_div::getIndpEnv('HTTP_REFERER')));
	}


	/**
	 * Initialize method for test record action
	 */
	public function initializeTestRecordAction() {
		$edit = t3lib_div::_GPmerged('edit');
		if(count($edit) == 1) {
			$tableName = key($edit);
			$recordIdentifier = key(current($edit));
			$configuration = $this->configurationManager->getConfiguration(Tx_Extbase_Configuration_ConfigurationManagerInterface::CONFIGURATION_TYPE_FRAMEWORK);
			$classMapping = $configuration['persistence']['classes'];
			if ($classMapping && !empty($classMapping)) {
				$potentialEntities = array();
				foreach ($classMapping as $entityName=>$classMap) {
					if(Tx_Extbase_Reflection_ObjectAccess::getPropertyPath($classMap, 'mapping.tableName') == $tableName) {
						$potentialEntities[$entityName] = $classMap['subclasses'];
					}
				}
			}
			if(isset($potentialEntities) && !empty($potentialEntities)) {
				foreach($potentialEntities as $entityName => $potentialEntity) {
					if(is_array($potentialEntity)) {
						foreach($potentialEntity as $subClass) {
							$potentialEntities[$subClass] = null;
						}
					}
				}
			}
			if(!isset($entity)) {
				$entity = str_replace(' ', '_', ucwords(str_replace('_', ' ', $tableName)));
			}
			if($entity && $recordIdentifier) {
				$this->request->setArgument('entityNames', array_keys($potentialEntities));
				$this->request->setArgument('recordIdentifier', $recordIdentifier);
			}
		}
	}


	/**
	 * Enter description here ...
	 *
	 * @param array $entityNames
	 * @param int $recordIdentifier
	 * @return void|string
	 */
	public function testRecordAction($entityNames = array(), $recordIdentifier = null) {
		if(empty($entityNames) || !$recordIdentifier) return '';
		foreach ($entityNames as $entityName) {
			$rules = $this->mergerService->getPullRulesByEntityName($entityName);
			$this->uriBuilder->setArguments(array('M' => 'web_PalmTxPalmM1'));
			foreach($rules as $fileLocation => $rule) {
				$repository = $this->getRepositoryFromRule($rule);
				$entity = $repository->findByUid($recordIdentifier);
				if($entity !== null && $this->mergerService->isRuleApplicableOnEntity($rule, $entity)) {
					$this->flashMessageContainer->add(
						'The import rule for "' . $fileLocation . '" is applicable to this record. ' .
						'Click <a href="' .$this->uriBuilder->uriFor('mergeRecord', array('fileLocation'=>$fileLocation, 'record'=>$recordIdentifier)) . '">here</a> to merge this record with the current.',
						'',
						t3lib_FlashMessage::INFO
					);
				}
			}
		}
		return '';
	}


	protected function getRepositoryFromRule(Tx_Palm_Merger_AbstractRule $rule) {
		if (!class_exists($rule->getRepositoryName())) {
			die('PullDataController: This should not happen. The check occurs alreade in rule builder');
		}
		return $this->objectManager->get($rule->getRepositoryName());
	}

	protected function getCurrentPid() {
		$pageId = (integer)t3lib_div::_GP('id');
		if ($pageId > 0) {
			return $pageId;
		} else {
//			$message = $this->objectManager->create('t3lib_FlashMessage', 'Please choose a page from the pagetree', '', t3lib_FlashMessage::WARNING);
			$this->flashMessageContainer->add('Please choose a page from the pagetree', '', t3lib_FlashMessage::WARNING);
		}
	}


}
?>
