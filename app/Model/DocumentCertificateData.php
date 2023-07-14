<?php

App::uses('AppModel', 'Model');


class DocumentCertificateData extends AppModel {
        public $useTable = 'document_certificate_datas';
	public $belongsTo = array(
		'Document' => array(
			'className' => 'Document',
			'foreignKey' => 'document_id',
			'conditions' => '',
			'fields' => '',
			'order' => ''
		),
		'DocumentCertificate' => array(
			'className' => 'DocumentCertificate',
			'foreignKey' => 'document_certificate_id',
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
		'recertification_in_year' => array(
			'notBlank' => array(
				'rule' => array('notBlank'),
				'message' => 'Your custom message here',
			),
		),
		'horizon' => array(
			'notBlank' => array(
				'rule' => array('comparison', '>=', 1),
				'message' => 'Pleace select a level',
			),
		),
	);		
}

