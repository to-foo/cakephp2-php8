<?php

App::uses('AppModel', 'Model');


class ExaminerMonitoringData extends AppModel {
 public $useTable = 'examiner_monitoring_datas';
	public $belongsTo = array(
		'Examiner' => array(
			'className' => 'Examiner',
			'foreignKey' => 'examiner_id',
			'conditions' => '',
			'fields' => '',
			'order' => ''
		),
		'ExaminerMonitoring' => array(
			'className' => 'ExaminerMonitoring',
			'foreignKey' => 'examiner_monitoring_id',
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
