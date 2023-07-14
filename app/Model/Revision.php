<?php
App::uses('AppModel', 'Model');
/**
 * Roll Model
 *
 * @property Testingcomp $Testingcomp
 * @property User $User
 */
class Revision extends AppModel {

	public $belongsTo = array(
		'Reportnumber' => array(
			'className' => 'Reportnumber',
			'foreignKey' => 'reportnumber_id',
			'conditions' => '',
			'fields' => '',
			'order' => ''
		),
	);	
}
