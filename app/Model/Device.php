<?php
App::uses('AppModel', 'Model');

class Device extends AppModel {

  public $useTable = 'devices';
	public $actsAs = array('Containable');

	public $belongsTo = array(
		'Examiner' => array(
			'className' => 'Examiner',
			'foreignKey' => 'examiner_id',
			'conditions' => array('Examiner.id !=' => 0),
			'fields' => '',
			'order' => ''
		),
	);


	public $hasMany = array(
		'DeviceCertificate' => array(
			'className' => 'DeviceCertificate',
			'foreignKey' => 'device_id',
			'dependent' => true,
		),
		'DeviceCertificateData' => array(
			'className' => 'DeviceCertificateData',
			'foreignKey' => 'device_id',
			'dependent' => true,
		),
		'Examiner' => array(
			'className' => 'Examiner',
			'foreignKey' => 'device_id',
			'dependent' => false,
		)
	);


  public $hasAndBelongsToMany = array(

    'DeviceTestingmethod' => array(
      'className' => 'DeviceTestingmethod',
      'joinTable' => 'devices_testingmethods_devices',
      'foreignKey' => 'device_id',
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
    ),

    'Dropdown' => array(
      'className' => 'Dropdown',
      'joinTable' => 'device_dropdowns',
      'foreignKey' => 'device_id',
      'associationForeignKey' => 'dropdown_id',
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

		'DeviceTestingmethod' => array(
			'notBlank' => array(
				'rule' => array('notBlank'),
				'message' => 'Your custom message here',
			),
		),
    'intern_no' => array(
			'notBlank' => array(
				'rule' => array('notBlank'),
				'message' => 'Your custom message here',
			),
		),
		'producer' => array(
			'notBlank' => array(
				'rule' => array('notBlank'),
				'message' => 'Your custom message here',
			),
		),
		'device_type' => array(
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
		'registration_no' => array(
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
		if(!isset($this->data['DeviceTestingmethod']['DeviceTestingmethod']) || empty($this->data['DeviceTestingmethod']['DeviceTestingmethod'])) {
//			$this->invalidate('non_existent_field');
			$this->invalidate('DeviceTestingmethod', 'Your custom message here');
		}
		return true;
	}
}
