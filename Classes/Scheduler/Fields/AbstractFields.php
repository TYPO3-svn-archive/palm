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
class tx_Palm_Scheduler_Fields_AbstractFields implements tx_scheduler_AdditionalFieldProvider {


	/**
	 * Additional fields
	 *
	 * @var	array
	 */
	protected $fields = array(
		'pid'
	);

	/**
	 * Field prefix.
	 *
	 * @var	string
	 */
	protected $fieldPrefix = 'PalmSchedulerField';

	/**
	 * Gets additional fields to render in the form to add/edit a task
	 *
	 * @param	array	$taskInfo Values of the fields from the add/edit task form
	 * @param	tx_scheduler_Task	$task The task object being eddited. Null when adding a task!
	 * @param	tx_scheduler_Module	$schedulerModule Reference to the scheduler backend module
	 * @return	array	A two dimensional array, array('Identifier' => array('fieldId' => array('code' => '', 'label' => '', 'cshKey' => '', 'cshLabel' => ''))
	 */
	public function getAdditionalFields(array &$taskInfo, $task, tx_scheduler_Module $schedulerModule) {

		if ($schedulerModule->CMD == 'edit') {
			foreach($this->fields as $field) {
				$taskInfo[$this->getFullFieldName($field)] = call_user_func(array($task, 'get' . $field));
			}
		}

		$additionalFields = array();
		foreach($this->fields as $field) {
			$fieldName = $this->getFullFieldName($field);
			$fieldId   = 'task_' . $fieldName;
			$fieldHtml = '<input type="text" '
				. 'name="tx_scheduler[' . $fieldName . ']" '
				. 'id="' . $fieldId . '" '
				. 'value="' . htmlspecialchars($taskInfo[$fieldName]) . '" />';

			$additionalFields[$fieldId] = array(
				'code'		=> $fieldHtml,
				'label'		=> 'LLL:EXT:palm/Resources/Private/Language/locallang_mod.xml:scheduler_taskField_' . $field,
				'cshKey'	=> '',
				'cshLabel'	=> $fieldId
			);
		}

		return $additionalFields;
	}

	/**
	 * Validates the additional fields' values
	 *
	 * @param	array	$submittedData An array containing the data submitted by the add/edit task form
	 * @param	tx_scheduler_Module	$schedulerModule Reference to the scheduler backend module
	 * @return	boolean	True if validation was ok (or selected class is not relevant), false otherwise
	 */
	public function validateAdditionalFields(array &$submittedData, tx_scheduler_Module $schedulerModule) {
		$validInput = TRUE;
		return $validInput;
	}

	/**
	 * Takes care of saving the additional fields' values in the task's object
	 *
	 * @param	array	$submittedData An array containing the data submitted by the add/edit task form
	 * @param	tx_scheduler_Task	$task Reference to the scheduler backend module
	 * @return	void
	 */
	public function saveAdditionalFields(array $submittedData, tx_scheduler_Task $task) {

		if (!($task instanceof tx_Palm_Scheduler_AbstractTask)) {
			throw new InvalidArgumentException(
				'Expected a task of type tx_Palm_Scheduler_AbstractTask, but got ' . get_class($task),
				1322492175
			);
		}

		foreach ($this->fields as $field) {
			call_user_func(array($task, 'set' . $field), $submittedData[$this->getFullFieldName($field)]);
		}
	}

	/**
	 * Constructs the full field name which can be used in HTML markup.
	 *
	 * @param	string	$fieldName A raw field name
	 * @return	string Field name ready to use in HTML markup
	 */
	protected function getFullFieldName($fieldName) {
		return $this->fieldPrefix . ucfirst($fieldName);
	}

}