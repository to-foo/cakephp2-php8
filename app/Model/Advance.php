<?php
App::uses('AppModel', 'Model');

class Advance extends AppModel {

	public $useTable = 'advances';

	function beforeValidate($options = array()) {

	  if (!isset($this->data['Testingcomp']['Testingcomp']) || empty($this->data['Testingcomp']['Testingcomp'])) {
	    $this->invalidate('non_existent_field'); // fake validation error on Project
	    $this->Testingcomp->invalidate('Testingcomp', __('Please select at least one company',true));
	  }

		if (!isset($this->data['Topproject']['Topproject']) || empty($this->data['Topproject']['Topproject'])) {
	    $this->invalidate('non_existent_field'); // fake validation error on Project
	    $this->Topproject->invalidate('Topproject', __('Please select at least one project',true));
	  }


	  return true;

	}

	public $hasAndBelongsToMany = array(

		'Topproject' => array(
			'className' => 'Topproject',
			'joinTable' => 'advances_topproject',
			'foreignKey' => 'advance_id',
			'associationForeignKey' => 'topproject_id',
			'unique' => 'keepExisting',
			'conditions' => '',
			'fields' => '',
			'order' => '',
			'limit' => '',
			'offset' => '',
			'finderQuery' => '',
			'deleteQuery' => '',
			'insertQuery' => ''
		),
		'Testingcomp' => array(
			'className' => 'Testingcomp',
			'joinTable' => 'advances_testingcomp',
			'foreignKey' => 'advance_id',
			'associationForeignKey' => 'testingcomp_id',
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

	public $hasOne = 'AdvancesSetting';

	public $validate = array(
		'name' => array(
			'notBlank' => array(
				'rule' => array('notBlank'),
				'message' => 'Your custom message here',
			),
		),
	);
}
