<?php

App::uses('AppModel', 'Model');

/**

 * Report Model

 *

 * @property Reportlock $Reportlock

 * @property Reportnumber $Reportnumber

 * @property Topproject $Topproject

 * @property Testingmethod $Testingmethod

 */

class Report extends AppModel {

	function beforeValidate($options = array()) {
		foreach($this->hasAndBelongsToMany as $_key => $_data) {
			if(isset($this->data[$_key][$_key])) $this->data[$this->alias][$_key] = $this->data[$_key][$_key];
		}
	}

	public $actsAs = array('Containable');

	public $displayField = 'name';

	public $validate = array(
		'name' => array(
			'notBlank' => array(
				'rule' => array('notBlank'),
			),
		),
		'identification' => array(
			'notBlank' => array(
				'rule' => array('notBlank'),
			),
		),
		'projektbeschreibung' => array(
			'notBlank' => array(
				'rule' => array('notBlank'),
			),
		),
		'Testingmethod' => array(
			'multiple' => array(
			'rule' => array('multiple', array('min' => 1)),
			'message' => 'You need to select at least one tag',
			'required' => true,
			)
		),
	);

	public $hasMany = array(
		'Reportlock' => array(
			'className' => 'Reportlock',
			'foreignKey' => 'report_id',
			'dependent' => true,
			'type' => 'left',
			'conditions' => '',
			'fields' => '',
			'order' => '',
			'limit' => '',
			'offset' => '',
			'exclusive' => '',
			'finderQuery' => '',
			'counterQuery' => ''
		),
		'Reportnumber' => array(
			'className' => 'Reportnumber',
			'foreignKey' => 'report_id',
			'dependent' => false,
			'conditions' => '',
			'fields' => '',
			'order' => '',
			'limit' => '',
			'offset' => '',
			'exclusive' => '',
			'finderQuery' => '',
			'counterQuery' => ''
		)
	);

	public $hasAndBelongsToMany = array(
		'Topproject' => array(
			'className' => 'Topproject',
			'joinTable' => 'reports_topprojects',
			'foreignKey' => 'report_id',
			'associationForeignKey' => 'topproject_id',
			'unique' => 'keepExisting',
			'conditions' => '',
			'fields' => '',
			'order' => '',
			'limit' => '',
			'offset' => '',
			'finderQuery' => '',
			'deleteQuery' => '',
			'insertQuery' => ''
		),
		'Testingmethod' => array(
			'className' => 'Testingmethod',
			'joinTable' => 'testingmethods_reports',
			'foreignKey' => 'report_id',
			'associationForeignKey' => 'testingmethod_id',
			'unique' => 'keepExisting',
			'conditions' => '',
			'fields' => '',
			'order' => '',
			'limit' => '',
			'offset' => '',
			'finderQuery' => '',
			'deleteQuery' => '',
			'insertQuery' => ''
		)
	);
}

