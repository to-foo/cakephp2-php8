<?php

App::uses('AppModel', 'Model');

/**

 * DropdownsValue Model

 *

 * @property Testingcomp $Testingcomp

 * @property User $User

 * @property Report $Report

 * @property Dropdown $Dropdown

 * @property Dependency $Dependency

 */

class EyecheckData extends AppModel {

public $useTable = 'eyecheck_datas';
	public $belongsTo = array(
		'Eyecheck' => array(
			'className' => 'Eyecheck',
			'foreignKey' => 'certificate_id',
			'conditions' => '',
			'fields' => '',
			'order' => ''
		),
		'Examiner' => array(
			'className' => 'Examiner',
			'foreignKey' => 'examiner_id',
			'conditions' => '',
			'fields' => '',
			'order' => ''
		)
	);

	public $validate = array(

		'certified_date' => array(
			'notBlank' => array(
				'rule' => array('notBlank'),
				'message' => 'Your custom message here',
			),
		),

		'expiration_date' => array(
			'notBlank' => array(
				'rule' => array('notBlank'),
				'message' => 'Your custom message here',
			),
		),
	);

}
