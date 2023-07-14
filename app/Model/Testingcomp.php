<?php
App::uses('AppModel', 'Model');
/**
 * Testingcomp Model
 *
 * @property Roll $Roll
 * @property Qualification $Qualification
 * @property User $User
 * @property Topproject $Topproject
 */
class Testingcomp extends AppModel {
public $actsAs = array('Containable');
/**
 * validation rules
 * 
 * @var array
 */
 	public $validate = array(
 		'name'=>array(
			'notBlank' => array(
				'rule' => 'notBlank',
				'message' => 'This field may not be empty'
			)
		),
		'firmenname' => array(
			'notBlank' => array(
				'rule' => 'notBlank',
				'message' => 'This field may not be empty'
			)
		),
		'strasse' => array(
			'notBlank' => array(
				'rule' => 'notBlank',
				'message' => 'This field may not be empty'
			)
		),
		'plz' => array(
			'notBlank' => array(
				'rule' => 'notBlank',
				'message' => 'This field may not be empty'
			)
		),
		'ort' => array(
			'notBlank' => array(
				'rule' => 'notBlank',
				'message' => 'This field may not be empty'
			)
		),
		'email' => array(
			'email' => array(
				'rule'=>'email',
				'message' => 'This field must contain a valid email',
				'allowEmpty' => true
			)
		)
	);

	//The Associations below have been created with all possible keys, those that are not needed can be removed

/**
 * belongsTo associations
 *
 * @var array
 */
	public $belongsTo = array(
		'Roll' => array(
			'className' => 'Roll',
			'foreignKey' => 'roll_id',
			'conditions' => '',
			'fields' => '',
			'order' => ''
		)
	);

/**
 * hasMany associations
 *
 * @var array
 */
	public $hasMany = array(
		'Qualification' => array(
			'className' => 'Qualification',
			'foreignKey' => 'testingcomp_id',
			'dependent' => false,
			'conditions' => '',
			'fields' => '',
			'order' => '',
			'limit' => '',
			'offset' => '',
			'exclusive' => '',
			'finderQuery' => '',
			'counterQuery' => ''
		),
		'User' => array(
			'className' => 'User',
			'foreignKey' => 'testingcomp_id',
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
		'Topproject' => array(
			'className' => 'Topproject',
			'joinTable' => 'testingcomps_topprojects',
			'foreignKey' => 'testingcomp_id',
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
		'Dropdown' => array(
			'className' => 'Dropdown',
			'joinTable' => 'testingcomps_dropdowns',
			'foreignKey' => 'testingcomp_id',
			'associationForeignKey' => 'dropdown_id',
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
		'Development' => array(
			'className' => 'Development',
			'joinTable' => 'testingcomps_developments',
			'foreignKey' => 'testingcomp_id',
			'associationForeignKey' => 'development_id',
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
        'Testingcompcat' => array(
			'className' => 'Testingcompcat',
			'joinTable' => 'testingcompcategories_testingcomps',
			'foreignKey' => 'testingcomp_id',
			'associationForeignKey' => 'testingcomp_category_id',
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
        'Cascade' => array(
			'className' => 'Cascade',
			'joinTable' => 'testingcomps_cascades',
			'foreignKey' => 'testingcomp_id',
			'associationForeignKey' => 'cascade_id',
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
