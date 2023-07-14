<?php
App::uses('AppModel', 'Model');
/**
 * Testingmethod Model
 *
 * @property Qualification $Qualification
 * @property Topproject $Topproject
 */
class Weldingmethod extends AppModel {

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
	


/**
 * hasAndBelongsToMany associations
 *
 * @var array
 */
public $hasAndBelongsToMany = array(
		
            'Testingmethod' => array(
			'className' => 'Testingmethod',
			'joinTable' => 'weldingmethods_testingmethods',
			'foreignKey' => 'weldingmethod_id',
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

	

	public function beforeDelete($cascade = true) {
			die('tot');
	}
}
