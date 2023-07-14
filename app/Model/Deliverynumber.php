<?php
App::uses('AppModel', 'Model');
/**
 * Deliverynumber Model
 *
 * @property Topproject $Topproject
 * @property Equipment $Equipment
 * @property EquipmentType $EquipmentType
 * @property Order $Order
 */
class Deliverynumber extends AppModel {


	//The Associations below have been created with all possible keys, those that are not needed can be removed

/**
 * belongsTo associations
 *
 * @var array
 */
	public $belongsTo = array(
		'Topproject' => array(
			'className' => 'Topproject',
			'foreignKey' => 'topproject_id',
			'conditions' => '',
			'fields' => '',
			'order' => ''
		),
		'Equipment' => array(
			'className' => 'Equipment',
			'foreignKey' => 'equipment_id',
			'conditions' => '',
			'fields' => '',
			'order' => ''
		),
		'EquipmentType' => array(
			'className' => 'EquipmentType',
			'foreignKey' => 'equipment_type_id',
			'conditions' => '',
			'fields' => '',
			'order' => ''
		),
		'Order' => array(
			'className' => 'Order',
			'foreignKey' => 'order_id',
			'conditions' => '',
			'fields' => '',
			'order' => ''
		),
		'Testingcomp' => array(
			'className' => 'Testingcomp',
			'foreignKey' => 'testingcomp_id',
			'conditions' => '',
			'fields' => '',
			'order' => ''
		)
	);
}
