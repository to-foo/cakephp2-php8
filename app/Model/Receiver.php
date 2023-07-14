<?php
App::uses('AppModel', 'Model');
/**
 * Testingmethod Model
 *
 * @property Qualification $Qualification
 * @property Topproject $Topproject
 */
class Receiver extends AppModel {

/**
 * Validation rules
 *
 * @var array
 */
	public $actsAs = array('Containable');

	public $validate = array(
		'name' => array(
			'notBlank' => array(
				'rule' => array('notBlank'),
				//'message' => 'Your custom message here',
				//'allowEmpty' => false,
				//'required' => false,
				//'last' => false, // Stop validation after this rule
				//'on' => 'create', // Limit validation to 'create' or 'update' operations
			),
		),
		'email' => array(
			'notBlank' => array(
				'rule' => array('notBlank'),
				//'message' => 'Your custom message here',
				//'allowEmpty' => false,
				//'required' => false,
				//'last' => false, // Stop validation after this rule
				//'on' => 'create', // Limit validation to 'create' or 'update' operations
			),
		),
	);

	//The Associations below have been created with all possible keys, those that are not needed can be removed

/**
 * hasMany associations
 *
 * @var array
 */
	


/**
 * hasAndBelongsToMany associations
 *
 * @var array
 */
	public $hasAndBelongsToMany = array(
		'Report' => array(
			'className' => 'Report',
			'joinTable' => 'testingmethods_reports',
			'foreignKey' => 'testingmethod_id',
			'associationForeignKey' => 'report_id',
			'unique' => 'keepExisting',
			'conditions' => '',
			'fields' => '',
			'order' => '',
			'limit' => '',
			'offset' => '',
			'finderQuery' => '',
			'deleteQuery' => '',
			'insertQuery' => ''
		));
		
	public function beforeDelete($cascade = true) {
			die('tot');
	}
}
