<?php

App::uses('AppModel', 'Model');


class DeviceCertificate extends AppModel {

	public $hasMany = array(
		'DeviceCertificateData' => array(
			'className' => 'DeviceCertificateData',
			'foreignKey' => 'device_certificate_id',
			'dependent' => false,
		)
	);

	public $belongsTo = array(
		'Device' => array(
			'className' => 'Device',
			'foreignKey' => 'device_id',
			'conditions' => '',
			'fields' => '',
			'order' => ''
		),
/*
		'Testingcomp' => array(
			'className' => 'Testingcomp',
			'foreignKey' => 'testingcomp_id',
			'conditions' => '',
			'fields' => '',
			'order' => ''
		)
*/
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

	);
}
