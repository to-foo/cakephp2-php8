<?php
App::uses('AppModel', 'Model');
/**
 * Equipment Model
 *
 * @property EquipmentType $EquipmentType
 */
class Expediting extends AppModel {

/**
 * Use table
 *
 * @var mixed False or table name
 */
	public $useTable = 'expeditings';
//	public $hasAndBelongsToMany = array('Roll');
	public $displayFild = 'roll';

	public $hasAndBelongsToMany = array(
		'Roll' => array(
			'className' => 'Roll',
			'joinTable' => 'expeditings_rolls',
			'foreignKey' => 'expediting_id',
			'associationForeignKey' => 'roll_id',
			'unique' => 'keepExisting',
//			'unique' => false,
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

	public $validate = array(
		'date_soll' => array(
			'notBlank' => array(
				'rule' => array('notBlank'),
			),
		),
	);

	//The Associations below have been created with all possible keys, those that are not needed can be removed

/**
 * belongsTo associations
 *
 * @var array
 */
}
