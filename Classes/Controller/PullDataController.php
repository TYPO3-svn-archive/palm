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
	 * @var integer
	 */
	protected $pid;

	/**
	 * Contains hookObjectsArr
	 *
	 * @var array
	 */
	protected $hookObjectsArr = array();

	/**
	 * Contains objectManager
	 *
	 * @var Tx_Extbase_Object_ObjectManager
	 */
	protected $objectManager;

	/**
	 * @var Tx_Palm_Merger_Service
	 */
	protected $mergerService;

	/**
	 * Contains tceMain
	 *
	 * @var t3lib_TCEmain
	 */
	protected $tceMain;


	/**
	 * Contains queue
	 *
	 * @var tx_Palm_Scheduler_WorkerQueue
	 */
	protected $wokerQueue;

	/**
	 * Injector method for a merger service
	 *
	 * @param Tx_Palm_Merger_Service $mergerService
	 */
	public function injectMergerService(Tx_Palm_Merger_Service $mergerService) {
		$this->mergerService = $mergerService;
	}

	/**
	 * Constructor method for a pull data controller
	 *
	 * @param int $pid
	 */
	public function __construct($pid = NULL) {
		$this->pid = $pid;
		$this->wokerQueue = t3lib_div::makeInstance('tx_Palm_Scheduler_WorkerQueue');
		if (!isset($GLOBALS['TT'])) {
			require_once(PATH_t3lib.'class.t3lib_timetracknull.php');
			$GLOBALS['TT'] = new t3lib_timeTrackNull;
		}
	}

	/**
	 * @return void
	 */
	public function initializeAction() {
		if ($this->actionMethodName !== 'testRecordAction') {
			$this->pid = ($this->pid === NULL) ? $this->getCurrentPid() : $this->pid;
		}
		global $TYPO3_CONF_VARS;
		if (is_array($TYPO3_CONF_VARS['SC_OPTIONS']['t3lib/class.t3lib_tcemain.php']['processDatamapClass'])) {
			$this->tceMain = t3lib_div::makeInstance('t3lib_TCEmain');
			foreach ($TYPO3_CONF_VARS['SC_OPTIONS']['t3lib/class.t3lib_tcemain.php']['processDatamapClass'] as $classRef) {
				$this->hookObjectsArr[] = t3lib_div::getUserObj($classRef);
			}
		}
	}

	/**
	 * @return void
	 */
	public function indexAction() {
		$pullableEntities = $this->mergerService->getPullableEntities();
		$entityTable = array();
		foreach($pullableEntities as $entityName=>$directiveCount) {
			$entityTable[] = array($entityName, $directiveCount);
		}
		$this->view->assign('pid', $this->pid);
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
		/** @var $rule Tx_Palm_Merger_RootRule */
		foreach ($rules as $fileLocation=>$rule) {
			$rulesTable[] = array($fileLocation, $rule->getSinglePathInCollection());
		}
		$this->view->assign('pid', $this->pid);
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
		/** @var Tx_Extbase_Persistence_Typo3QuerySettings $querySettings */
		$querySettings = $this->objectManager->create('Tx_Extbase_Persistence_Typo3QuerySettings');
		$querySettings->setStoragePageIds(array($this->pid));
		$repository = $this->mergerService->getRepositoryByRule($rule);
		$repository->setDefaultQuerySettings($querySettings);
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
		/** @var Tx_Extbase_Persistence_Typo3QuerySettings $querySettings */
		$querySettings = $this->objectManager->create('Tx_Extbase_Persistence_Typo3QuerySettings');
		$querySettings->setStoragePageIds(array($this->pid));
		$repository = $this->mergerService->getRepositoryByRule($rule);
		$repository->setDefaultQuerySettings($querySettings);
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
		/** @var Tx_Extbase_Persistence_Typo3QuerySettings $querySettings */
		$querySettings = $this->objectManager->create('Tx_Extbase_Persistence_Typo3QuerySettings');
		$querySettings->setStoragePageIds(array($this->pid));
		$repository = $this->mergerService->getRepositoryByRule($rule);
		$repository->setDefaultQuerySettings($querySettings);
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
	 * @param bool $isQueued If the record is a queued one
	 */
	public function mergeRecordAction($fileLocation, $record, $isQueued = false) {
		$rule = $this->getRuleByFileLocation($fileLocation);
		$repository = $this->getRepositoryByRule($rule);
		$this->objectManager->get('Tx_Palm_Persistence_Mapper_DataMapper')->setEnableLazyLoading(false);
		/** @var $entity Tx_Extbase_DomainObject_AbstractEntity */
		$entity = $repository->findByUid($record);
		$this->mergerService->mergeByRule($entity, $rule);
		$repository->update($entity);
		$this->objectManager->get('Tx_Extbase_Persistence_Manager')->persistAll();
		$this->callProcessDatamapAfterDatabaseOperationsHook(get_class($entity), $entity->getUid(), $entity->getPid());
		if ($isQueued) {
			$this->wokerQueue->unregisterRecord($fileLocation, $record);
		}
		$this->flashMessageContainer->add('The record with the uid ' . $record . ' has been successfully merged!', t3lib_FlashMessage::OK);
		$this->redirectToURI(t3lib_div::sanitizeLocalUrl(t3lib_div::getIndpEnv('HTTP_REFERER')));
	}

	/**
	 * Enter description here ...
	 *
	 * @param string $fileLocation
	 * @param string $queue
	 */
	public function mergeAllRecordsAction($fileLocation, $queue = NULL) {
		$rule = $this->getRuleByFileLocation($fileLocation);
		$repository = $this->getRepositoryByRule($rule);
		/** @var $identityMap Tx_Extbase_Persistence_IdentityMap */
		$identityMap = $this->objectManager->get('Tx_Extbase_Persistence_IdentityMap');
		/** @var $persistenceSession Tx_Extbase_Persistence_Session */
		$persistenceSession = $this->objectManager->get('Tx_Extbase_Persistence_Session');
		/** @var $allEntitiesQueryResult Tx_Extbase_Persistence_QueryResult */
		$allEntitiesQueryResult = $repository->findAll();
		$query = $allEntitiesQueryResult->getQuery();
		$offset = 0;
		$maxOffset = $query->count();
		$query->setLimit(1);
		if (!$queue) {
			$this->objectManager->get('Tx_Palm_Persistence_Mapper_DataMapper')->setEnableLazyLoading(false);
		}
		$result = $query->execute();
		while($entity = $result->getFirst()) {
			if ($this->mergerService->isRuleApplicableOnEntity($rule, $entity)) {
				if ($queue) {
					$this->wokerQueue->registerActionForRecord(tx_Palm_Scheduler_WorkerQueue::ACTION_MERGE, $fileLocation, $entity->getUid());
				} else {
					set_time_limit(240);
					$this->mergerService->mergeByRule($entity, $rule);
					$repository->update($entity);
					$this->objectManager->get('Tx_Extbase_Persistence_Manager')->persistAll();
					$this->callProcessDatamapAfterDatabaseOperationsHook(get_class($entity), $entity->getUid(), $entity->getPid());
				}
			}
			foreach ($persistenceSession->getReconstitutedObjects() as $reconstitutedObject) {
				$persistenceSession->unregisterReconstitutedObject($reconstitutedObject);
				if ($identityMap->hasObject($reconstitutedObject)) {
					$identityMap->unregisterObject($reconstitutedObject);
				}
			}
			$offset++;
			if ($offset < $maxOffset) {
				$query->setOffset($offset);
				$result = $query->execute();
			} else {
				break;
			}
		}
		$this->flashMessageContainer->add('All records have been successfully merged!', t3lib_FlashMessage::OK);
		$this->redirectToURI(t3lib_div::sanitizeLocalUrl(t3lib_div::getIndpEnv('HTTP_REFERER')));
	}


	/**
	 * @param string $fileLocation
	 * @return Tx_Extbase_Persistence_Repository
	 */
	protected function getRuleByFileLocation($fileLocation) {
		/** @var $container Tx_Extbase_Object_Container_Container */
		$container = t3lib_div::makeInstance('Tx_Extbase_Object_Container_Container');
		$container->registerImplementation('Tx_Extbase_Persistence_Typo3QuerySettings', 'Tx_Palm_Persistence_MergerQuerySettings');
		$rule = $this->mergerService->getPullRuleByFileLocation($fileLocation);
		return $rule;
	}

	/**
	 * @param Tx_Palm_Merger_RootRule $rule
	 * @return Tx_Extbase_Persistence_Repository
	 */
	protected function getRepositoryByRule(Tx_Palm_Merger_RootRule $rule) {
		/** @var Tx_Extbase_Persistence_Typo3QuerySettings $querySettings */
		$querySettings = $this->objectManager->create('Tx_Extbase_Persistence_Typo3QuerySettings');
		$querySettings->setStoragePageIds(array($this->pid));
		$repository = $this->mergerService->getRepositoryByRule($rule);
		$repository->setDefaultQuerySettings($querySettings);
		return $repository;
	}


	/**
	 * @param string $className
	 * @param int $uid
	 * @param int $pid
	 */
	protected function callProcessDatamapAfterDatabaseOperationsHook($className, $uid, $pid) {
		foreach ($this->hookObjectsArr as $hookObj) {
			if (method_exists($hookObj, 'processDatamap_afterDatabaseOperations')) {
				/** @var Tx_Extbase_Persistence_Mapper_DataMap $dataMap */
				$dataMap = $this->objectManager->get('Tx_Palm_Persistence_Mapper_DataMapper')->getDataMap($className);
				$fieldArray = Array(
					'uid'	=> $uid,
					'pid'	=> $pid
				);
				$hookObj->processDatamap_afterDatabaseOperations('update', $dataMap->getTableName(), $uid, $fieldArray, $this->tceMain);
			}
		}
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
