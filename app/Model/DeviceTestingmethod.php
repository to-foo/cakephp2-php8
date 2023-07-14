<?php
App::uses('AppModel', 'Model');

class DeviceTestingmethod extends AppModel {

    public $useTable = 'device_testingmethods';

		public $validate = array(
		'verfahren' => array(
			'notBlank' => array(
				'rule' => array('notBlank'),

			)
		));

	 public $hasAndBelongsToMany = array(
		'Device' => array(
			'className' => 'Device',
			'joinTable' => 'devices_testingmethods_devices',
			'foreignKey' => 'testingmethod_id',
			'associationForeignKey' => 'device_id',
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
