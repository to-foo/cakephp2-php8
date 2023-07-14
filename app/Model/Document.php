<?php
App::uses('AppModel', 'Model');

class Document extends AppModel {

	public $actsAs = array('Containable');

	public $hasMany = array(
		'DocumentCertificateData' => array(
			'className' => 'DocumentCertificateData',
			'foreignKey' => 'document_id',
			'dependent' => false,
		),
		'DocumentCertificate' => array(
			'className' => 'DocumentCertificate',
			'foreignKey' => 'document_id',
			'dependent' => false,
		),
/*
		'Examiner' => array(
			'className' => 'Examiner',
			'foreignKey' => 'document_id',
			'dependent' => false,
		)
*/
	);

	public $hasAndBelongsToMany = array(
		'DocumentTestingmethod' => array(
			'className' => 'DocumentTestingmethod',
			'joinTable' => 'documents_testingmethods_documents',
			'foreignKey' => 'document_id',
			'associationForeignKey' => 'testingmethod_id',
			'unique' => 'keepExisting',
			'conditions' => '',
			'fields' => '',
			'order' => '',
			'limit' => '',
			'offset' => '',
			'finderQuery' => '',
			'deleteQuery' => '',
			'insertQuery' => ''
		)

	);

	public $validate = array(

		'DocumentTestingmethod' => array(
			'notBlank' => array(
				'rule' => array('notBlank'),
				'message' => 'Your custom message here',
			),
		),
		'document_type' => array(
			'notBlank' => array(
				'rule' => array('notBlank'),
				'message' => 'Your custom message here',
			),
		),
		'name' => array(
			'notBlank' => array(
				'rule' => array('notBlank'),
				'message' => 'Your custom message here',
			),
		),

		'webplan' => array(
			'notBlank' => array(
				'rule' => array('notBlank'),
				'message' => 'Your custom message here',
			),
		),
		'working_place' => array(
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

	function beforeValidate($options = array()) {
	//	if(!isset($this->data['DocumentTestingmethod']['DocumentTestingmethod']) || empty($this->data['DocumentTestingmethod']['DocumentTestingmethod'])) {
//			$this->invalidate('non_existent_field');
		//	$this->invalidate('DocumentTestingmethod', 'Your custom message here');
	//	}
	//	return true;
	}
}
