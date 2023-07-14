<?php
App::uses('AppModel', 'Model');
/**
 * Equipment Model
 *
 * @property EquipmentType $EquipmentType
 */
class Cascade extends AppModel {

/**
 * Use table
 *
 * @var mixed False or table name
 */
	public $useTable = 'cascades';

	function beforeValidate($options = array()) {
/*
	  if (!isset($this->data['Testingcomp']['Testingcomp']) || empty($this->data['Testingcomp']['Testingcomp'])) {
	    $this->invalidate('non_existent_field'); // fake validation error on Project
	    $this->Testingcomp->invalidate('Testingcomp', __('Please select at least one company',true));
	  }
*/
	  return true;

	}

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
		)
	);

	public $hasMany = array(
		'Task' => array(
			'className' => 'Task',
			'foreignKey' => 'cascade_id',
			'dependent' => true,
			'fields' => '',
			'order' => ''
		)
	);
	public $hasAndBelongsToMany = array(
    'Testingcomp' => array(
			'className' => 'Testingcomp',
			'joinTable' => 'testingcomps_cascades',
			'foreignKey' => 'cascade_id',
			'associationForeignKey' => 'testingcomp_id',
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
		'CascadeGroup' => array(
	'className' => 'CascadeGroup',
	'joinTable' => 'cascadegroups_cascades',
	'foreignKey' => 'cascade_id',
	'associationForeignKey' => 'cascade_group_id',
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
}
