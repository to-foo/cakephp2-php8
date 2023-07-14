<?php
App::uses('AppModel', 'Model');
/**
 * Testingmethod Model
 *
 * @property Qualification $Qualification
 * @property Topproject $Topproject
 */
class Testingmethod extends AppModel {

/**
 * Validation rules
 *
 * @var array
 */
	public $actsAs = array('Containable');

	public $validate = array(
		'value' => array(
			'notBlank' => array(
				'rule' => array('notBlank'),
				//'message' => 'Your custom message here',
				//'allowEmpty' => false,
				//'required' => false,
				//'last' => false, // Stop validation after this rule
				//'on' => 'create', // Limit validation to 'create' or 'update' operations
			),
		),
		'verfahren' => array(
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
	public $hasMany = array(
		'Qualification' => array(
			'className' => 'Qualification',
			'foreignKey' => 'testingmethod_id',
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
		),
		'Device' => array(
			'className' => 'Device',
			'joinTable' => 'testingmethods_devices',
			'foreignKey' => 'testingmethod_id',
			'associationForeignKey' => 'device_id',
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
        'Certificate' => array(
			'className' => 'Certificate',
			'joinTable' => 'testingmethods_certificates',
			'foreignKey' => 'testingmethod_id',
			'associationForeignKey' => 'certificate_id',
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

	public function beforeDelete($cascade = true) {
			die('tot');
	}
}
