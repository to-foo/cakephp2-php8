<?php
App::uses('AppModel', 'Model');

class DocumentTestingmethod extends AppModel {
	 public $useTable = 'document_testingmethods';
	public $hasAndBelongsToMany = array(
		'Document' => array(
			'className' => 'Document',
			'joinTable' => 'documents_testingmethods_documents',
			'foreignKey' => 'testingmethod_id',
			'associationForeignKey' => 'document_id',
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
}
