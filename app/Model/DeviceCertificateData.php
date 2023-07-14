<?php

App::uses('AppModel', 'Model');


class DeviceCertificateData extends AppModel {
 public $useTable = 'device_certificate_datas';
	public $belongsTo = array(
		'Device' => array(
			'className' => 'Device',
			'foreignKey' => 'device_id',
			'conditions' => '',
			'fields' => '',
			'order' => ''
		),
		'DeviceCertificate' => array(
			'className' => 'DeviceCertificate',
			'foreignKey' => 'device_certificate_id',
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
