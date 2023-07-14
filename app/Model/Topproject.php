<?php
App::uses('AppModel', 'Model');
/**
 * Topproject Model
 *
 * @property Testingcomp $Testingcomp
 * @property Report $Report
 */
class Topproject extends AppModel {

	// The Associations below have been created with all possible keys, those that are not needed can be removed

/**
 * hasAndBelongsToMany associations
 *
 * @var array
 */

	function beforeValidate($options = array()) {
		foreach($this->hasAndBelongsToMany as $_key => $_data) {
			if(isset($this->data[$_key][$_key])) $this->data[$this->alias][$_key] = $this->data[$_key][$_key];
		}
	}

	public $validate = array(
		'projektname' => array(
			'notBlank' => array(
				'rule' => array('notBlank'),
			),
		),
		'identification' => array(
			'notBlank' => array(
				'rule' => array('notBlank'),
			),
		),
		'Report' => array(
			'multiple' => array(
			'rule' => array('multiple', array('min' => 1)),
			'message' => 'You need to select at least one tag',
			'required' => true,
			)
		),
		'Testingcomp' => array(
			'multiple' => array(
			'rule' => array('multiple', array('min' => 1)),
			'message' => 'You need to select at least one tag',
			'required' => true,
			)
		)
	);
		 
	public $hasAndBelongsToMany = array(
		'Testingcomp' => array(
			'className' => 'Testingcomp',
			'joinTable' => 'testingcomps_topprojects',
			'foreignKey' => 'topproject_id',
			'associationForeignKey' => 'testingcomp_id',
			'unique' => 'keepExisting',
			'conditions' => '',
			'fields' => '',
			'order' => '',
			'limit' => '',
			'offset' => '',
			'finderQuery' => '',
		),
		'Report' => array(
			'className' => 'Report',
			'joinTable' => 'reports_topprojects',
			'foreignKey' => 'topproject_id',
			'associationForeignKey' => 'report_id',
			'unique' => 'keepExisting',
			'conditions' => '',
			'fields' => '',
			'order' => '',
			'limit' => '',
			'offset' => '',
			'finderQuery' => '',
		),
	);
}
