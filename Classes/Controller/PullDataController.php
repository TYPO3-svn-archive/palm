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
	 * Contains hookObjectsArr
	 *
	 * @var array
	 */
	protected $hookObjectsArr = array();

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
	 * @return void
	 */
	public function initializeAction() {
		global $TYPO3_CONF_VARS;
		if (is_array($TYPO3_CONF_VARS['SC_OPTIONS']['t3lib/class.t3lib_tcemain.php']['processDatamapClass'])) {
			foreach ($TYPO3_CONF_VARS['SC_OPTIONS']['t3lib/class.t3lib_tcemain.php']['processDatamapClass'] as $classRef) {
				$this->hookObjectsArr[] = t3lib_div::getUserObj($classRef);
			}
		}
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
		$repository = $this->mergerService->getRepositoryByRule($rule);
		$repository->setDefaultOrderings(Array(
			'uid' => Tx_Extbase_Persistence_QueryInterface::ORDER_ASCENDING,
		));
		$this->view->assign('entityName', $rule->getEntityName());
		$this->view->assign('rule', $rule);
		$this->view->assign('propertyPath', $this->mergerService->getPropertyPathFromRule($rule));
		$this->view->assign('items', $repository->findAll());
	}

	/**
	 * The show action
	 *
	 * @param string $fileLocation
	 * @param integer $currentPage
	 */
	public function selectImportRecordAction($fileLocation, $currentPage = 1) {
		$rule = $this->mergerService->getPullRuleByFileLocation($fileLocation);
		$repository = $this->mergerService->getXmlRepositoryByRule($rule);
		$this->view->assign('entityName', $rule->getEntityName());
		$this->view->assign('rule', $rule);
		$this->view->assign('propertyPath', $this->mergerService->getPropertyPathFromRule($rule));
		$this->view->assign('items', $repository->findAll());
	}

	/**
	 * Enter description here ...
	 *
	 * @param string $fileLocation
	 * @param string $record
	 */
	public function importRecordAction($fileLocation, $record) {
		$rule = $this->mergerService->getPullRuleByFileLocation($fileLocation);
		$resolvedPath = str_replace(
			'{' . $this->mergerService->getPropertyPathFromRule($rule) . '}',
			$record,
			$rule->getSinglePathInCollection()
		);
		$repository = $this->mergerService->getRepositoryByRule($rule);
		$externalEntity = $this->mergerService->getExternalEntityByExternalPath(
			$this->mergerService->getDOMByRule($rule),
			$resolvedPath,
			$rule->getEntityName()
		);
		$repository->add($externalEntity);
		$this->objectManager->get('Tx_Extbase_Persistence_Manager')->persistAll();
		$parent = t3lib_div::makeInstance('t3lib_TCEmain');
		foreach ($this->hookObjectsArr as $hookObj) {
			if (method_exists($hookObj, 'processDatamap_afterDatabaseOperations')) {
				/** @var Tx_Extbase_Persistence_Mapper_DataMap $dataMap */
				$dataMap = $this->objectManager->get('Tx_Palm_Persistence_Mapper_DataMapper')->getDataMap(get_class($externalEntity));
				$parent->substNEWwithIDs = Array(
					$externalEntity->getUid() => $externalEntity->getUid()
				);
				$fieldArray = Array(
					'uid'	=> $externalEntity->getUid(),
					'pid'	=> $externalEntity->getPid()
				);
				$hookObj->processDatamap_afterDatabaseOperations('new', $dataMap->getTableName(), $externalEntity->getUid(), $fieldArray, $parent);
			}
		}
		$this->flashMessageContainer->add('All records have been successfully merged!', t3lib_FlashMessage::OK);
		$this->redirectToURI(t3lib_div::sanitizeLocalUrl(t3lib_div::getIndpEnv('HTTP_REFERER')));
	}

	/**
	 * Enter description here ...
	 *
	 * @param string $fileLocation
	 */
	public function importAllRecordsAction($fileLocation) {
		$container = t3lib_div::makeInstance('Tx_Extbase_Object_Container_Container');
		$container->registerImplementation('Tx_Extbase_Persistence_Typo3QuerySettings', 'Tx_Palm_Persistence_MergerQuerySettings');
		$this->objectManager->get('Tx_Palm_Persistence_Mapper_DataMapper')->setEnableLazyLoading(false);
		$rule = $this->mergerService->getPullRuleByFileLocation($fileLocation);
		$repository = $this->mergerService->getRepositoryByRule($rule);
		$xmlRepository = $this->mergerService->getXmlRepositoryByRule($rule);
		$added = Array();
		foreach($xmlRepository->findAll() as $entity) {
			if (!$this->mergerService->isEntityAlreadyPresent($rule, $entity)) {
				$repository->add($entity);
				$added[] = $entity;
				if (count($added) % 20 == 0) {
					$this->objectManager->get('Tx_Extbase_Persistence_Manager')->persistAll();
				}
			}
		}
		$this->objectManager->get('Tx_Extbase_Persistence_Manager')->persistAll();
		$parent = t3lib_div::makeInstance('t3lib_TCEmain');
		foreach ($this->hookObjectsArr as $hookObj) {
			if (method_exists($hookObj, 'processDatamap_afterDatabaseOperations')) {
				foreach ($added as $externalEntity) {
					/** @var Tx_Extbase_Persistence_Mapper_DataMap $dataMap */
					$dataMap = $this->objectManager->get('Tx_Palm_Persistence_Mapper_DataMapper')->getDataMap(get_class($externalEntity));
					$parent->substNEWwithIDs = Array(
						$externalEntity->getUid()=> $externalEntity->getUid()
					);
					$fieldArray = Array(
						'uid'	=> $externalEntity->getUid(),
						'pid'	=> $externalEntity->getPid()
					);
					$hookObj->processDatamap_afterDatabaseOperations('new', $dataMap->getTableName(), $externalEntity->getUid(), $fieldArray, $parent);
				}
			}
		}
		$this->flashMessageContainer->add('All records have been successfully merged!', t3lib_FlashMessage::OK);
		$this->redirectToURI(t3lib_div::sanitizeLocalUrl(t3lib_div::getIndpEnv('HTTP_REFERER')));
	}

	/**
	 * Enter description here ...
	 *
	 * @param string $fileLocation
	 * @param int $record The record uid
	 */
	public function mergeRecordAction($fileLocation, $record) {
		$container = t3lib_div::makeInstance('Tx_Extbase_Object_Container_Container');
		$container->registerImplementation('Tx_Extbase_Persistence_Typo3QuerySettings', 'Tx_Palm_Persistence_MergerQuerySettings');
		$this->objectManager->get('Tx_Palm_Persistence_Mapper_DataMapper')->setEnableLazyLoading(false);
		$rule = $this->mergerService->getPullRuleByFileLocation($fileLocation);
		$repository = $this->mergerService->getRepositoryByRule($rule);
		$entity = $repository->findByUid($record);
		$this->mergerService->mergeByRule($entity, $rule);
		$repository->update($entity);
		$this->objectManager->get('Tx_Extbase_Persistence_Manager')->persistAll();
		$parent = t3lib_div::makeInstance('t3lib_TCEmain');
		foreach ($this->hookObjectsArr as $hookObj) {
			if (method_exists($hookObj, 'processDatamap_afterDatabaseOperations')) {
				/** @var Tx_Extbase_Persistence_Mapper_DataMap $dataMap */
				$dataMap = $this->objectManager->get('Tx_Palm_Persistence_Mapper_DataMapper')->getDataMap(get_class($entity));
				$fieldArray = Array(
					'uid'	=> $entity->getUid(),
					'pid'	=> $entity->getPid()
				);
				$hookObj->processDatamap_afterDatabaseOperations('update', $dataMap->getTableName(), $entity->getUid(), $fieldArray, $parent);
			}
		}
		$this->flashMessageContainer->add('The record with the uid ' . $record . ' has been successfully merged!', t3lib_FlashMessage::OK);
		$this->redirectToURI(t3lib_div::sanitizeLocalUrl(t3lib_div::getIndpEnv('HTTP_REFERER')));
	}

	/**
	 * Enter description here ...
	 *
	 * @param string $fileLocation
	 */
	public function mergeAllRecordsAction($fileLocation) {
		$container = t3lib_div::makeInstance('Tx_Extbase_Object_Container_Container');
		$container->registerImplementation('Tx_Extbase_Persistence_Typo3QuerySettings', 'Tx_Palm_Persistence_MergerQuerySettings');
		$this->objectManager->get('Tx_Palm_Persistence_Mapper_DataMapper')->setEnableLazyLoading(false);
		$rule = $this->mergerService->getPullRuleByFileLocation($fileLocation);
		$repository = $this->mergerService->getRepositoryByRule($rule);
		$updated = Array();
		foreach($repository->findAll() as $entity) {
			if ($this->mergerService->isRuleApplicableOnEntity($rule, $entity)) {
				set_time_limit(90);
				$this->mergerService->mergeByRule($entity, $rule);
				$repository->update($entity);
				$updated[] = $entity;
				if (count($updated) % 20 == 0) {
					$this->objectManager->get('Tx_Extbase_Persistence_Manager')->persistAll();
				}
			}
		}
		$this->objectManager->get('Tx_Extbase_Persistence_Manager')->persistAll();
		$parent = t3lib_div::makeInstance('t3lib_TCEmain');
		foreach ($this->hookObjectsArr as $hookObj) {
			if (method_exists($hookObj, 'processDatamap_afterDatabaseOperations')) {
				foreach ($updated as $externalEntity) {
					/** @var Tx_Extbase_Persistence_Mapper_DataMap $dataMap */
					$dataMap = $this->objectManager->get('Tx_Palm_Persistence_Mapper_DataMapper')->getDataMap(get_class($externalEntity));
					$fieldArray = Array(
						'uid'	=> $externalEntity->getUid(),
						'pid'	=> $externalEntity->getPid()
					);
					$hookObj->processDatamap_afterDatabaseOperations('update', $dataMap->getTableName(), $externalEntity->getUid(), $fieldArray, $parent);
				}
			}
		}
		$this->flashMessageContainer->add('All records have been successfully merged!', t3lib_FlashMessage::OK);
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
			$record = t3lib_BEfunc::getRecord($tableName, $recordIdentifier, 'pid');
			$pid = $record['pid'];
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
				$this->request->setArgument('pid', (int) $pid);
				$this->request->setArgument('entityNames', array_keys($potentialEntities));
				$this->request->setArgument('recordIdentifier', $recordIdentifier);
			}
		}
	}

	/**
	 * Enter description here ...
	 *
	 * @param int $pid
	 * @param array $entityNames
	 * @param int $recordIdentifier
	 * @return void|string
	 */
	public function testRecordAction($pid, $entityNames = array(), $recordIdentifier = null) {
		if(empty($entityNames) || !$recordIdentifier) return '';
		foreach ($entityNames as $entityName) {
			$rules = $this->mergerService->getPullRulesByEntityName($entityName);
			$this->uriBuilder->setArguments(array('M' => 'web_PalmTxPalmM1', 'id' => $pid));
			foreach($rules as $fileLocation => $rule) {
				$repository = $this->mergerService->getRepositoryByRule($rule);
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

	/**
	 * @return int
	 */
	protected function getCurrentPid() {
		$pageId = (integer)t3lib_div::_GP('id');
		if ($pageId > 0) {
			return $pageId;
		} else {
			$this->flashMessageContainer->add('Please choose a page from the pagetree', '', t3lib_FlashMessage::WARNING);
		}
	}

}
?>
