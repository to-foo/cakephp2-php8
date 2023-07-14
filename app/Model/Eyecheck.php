<?php
App::uses('AppModel', 'Model');
/**
 * Language Model
 *
 */
class Eyecheck extends AppModel {

    public $useTable = 'eyechecks';

	public $hasMany = array(
		'EyecheckData' => array(
			'className' => 'EyecheckData',
			'foreignKey' => 'certificate_id',
			'dependent' => false,
		)
	);

	public $validate = array(

		'certificat' => array(
			'notBlank' => array(
				'rule' => array('notBlank'),
				'message' => 'Your custom message here',
			),
		),
		'first_registration' => array(
			'notBlank' => array(
				'rule' => array('notBlank'),
				'message' => 'Your custom message here',
			),
		),
		'horizon' => array(
			'notBlank' => array(
				'rule' => array('notBlank'),
				'message' => 'Your custom message here',
			),
		),
	);
}
