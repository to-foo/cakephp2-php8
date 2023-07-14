<?php
App::uses('AppModel', 'Model');
/**
 * HiddenField Model
 *
 * @property Reportnumber $Reportnumber
 * @property User $User
 */
class HiddenField extends AppModel {


	//The Associations below have been created with all possible keys, those that are not needed can be removed

/**
 * belongsTo associations
 *
 * @var array
 */
	public $belongsTo = array(
		'Reportnumber' => array(
			'className' => 'Reportnumber',
			'foreignKey' => 'reportnumber_id',
			'conditions' => '',
			'fields' => '',
			'order' => ''
		),
		'User' => array(
			'className' => 'User',
			'foreignKey' => 'user_id',
			'conditions' => '',
			'fields' => '',
			'order' => ''
		)
	);
}
