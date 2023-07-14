<?php
App::uses('AppModel', 'Model');

class Testinginstruction extends AppModel {

	function UpdateTestinginstructionAuthorization($data,$testinstruction_id){

		$models = array('Topproject' => array(),'Testingcomp' => array(),'Report' => array(),'Testingstruction' => array());
		$fields = array('Topproject' => 'topproject_id','Testingcomp' => 'testingcomp_id','Report' => 'report_id','Testingstruction' => 'testingstruction_id');
		$output = array();

		if(!isset($data['testingmethod_id'])) return $output;

		$Testingmethod = $data['testingmethod_id'];

		foreach ($models as $key => $value) {

			if(!isset($data[$key])) continue;
			if(count($data[$key]) == 0) continue;

			foreach ($data[$key] as $_key => $_value) {

				foreach ($data as $__key => $__value) {

					if(!is_array($__value)) continue;
					if(count($__value) == 0) continue;

					$output[$key][$_value][$fields[$key]][$_value] = $_value;
					$output[$key][$_value]['testingmethod_id'][$_value] = $Testingmethod;
					$output[$key][$_value]['testingstruction_id'][$_value] = $testinstruction_id;

					foreach ($__value as $___key => $___value) {

						$output[$key][$_value][$fields[$__key]][$___value] = $___value;

						if(count($output[$key][$_value][$fields[$__key]]) > 1) unset($output[$key][$_value][$fields[$__key]][$___value]);
					}
				}

				ksort($output[$key][$_value]);

			}
		}

		return $output;

	}

	public $hasMany = array(
		'TestinginstructionsData' => array(
			'className' => 'TestinginstructionsData',
			'foreignKey' => 'testinginstruction_id',
			'dependent' => true,
		),
	);

	public $validate = array(
/*
		'DeviceTestingmethod' => array(
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
*/
	);

/*
 public $hasAndBelongsToMany = array(
		'Topproject' => array(
			'className' => 'Topproject',
			'joinTable' => 'testingstructions_authorizations',
			'foreignKey' => 'testingstruction_id',
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
		'Testingmethod' => array(
			'className' => 'Testingmethod',
			'joinTable' => 'testingstructions_authorizations',
			'foreignKey' => 'testingstruction_id',
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
*/
}
