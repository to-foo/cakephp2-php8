<?php
App::uses('AppModel', 'Model');
/**
 * Equipment Model
 *
 * @property EquipmentType $EquipmentType
 */
class Equipment extends AppModel {

/**
 * Use table
 *
 * @var mixed False or table name
 */
	public $useTable = 'equipments';


	//The Associations below have been created with all possible keys, those that are not needed can be removed

/**
 * belongsTo associations
 *
 * @var array
 */
	public $belongsTo = array(
		'EquipmentType' => array(
			'className' => 'EquipmentType',
			'foreignKey' => 'equipment_type_id',
			'conditions' => '',
			'fields' => '',
			'order' => ''
		)
	);
}
