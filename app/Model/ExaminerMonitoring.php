<?php

App::uses('AppModel', 'Model');


class ExaminerMonitoring extends AppModel {

	public $hasMany = array(
		'ExaminerMonitoringData' => array(
			'className' => 'ExaminerMonitoringData',
			'foreignKey' => 'examiner_monitoring_id',
			'dependent' => false,
		)
	);

	public $belongsTo = array(
		'Examiner' => array(
			'className' => 'Examiner',
			'foreignKey' => 'examiner_id',
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
		'recertification_in_year' => array(
			'notBlank' => array(
//				'rule' => array('comparison', '>=', 1),
				'rule' => array('notBlank'),
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

