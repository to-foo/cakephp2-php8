<?php

App::uses('AppModel', 'Model');


class DocumentCertificate extends AppModel {

	public $hasMany = array(
		'DocumentCertificateData' => array(
			'className' => 'DocumentCertificateData',
			'foreignKey' => 'document_certificate_id',
			'dependent' => false,
		)
	);

	public $belongsTo = array(
		'Document' => array(
			'className' => 'Document',
			'foreignKey' => 'document_id',
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
		'recertification_in_year' => array(
			'notBlank' => array(
				'rule' => array('comparison', '>=', 1),
				'message' => 'Pleace select a level',
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

