<?php

App::uses('AppModel', 'Model');


class WelderMonitoring extends AppModel {

	public $hasMany = array(
		'WelderMonitoringData' => array(
			'className' => 'WelderMonitoringData',
			'foreignKey' => 'welder_monitoring_id',
			'dependent' => false,
		)
	);

	public $belongsTo = array(
		'Welder' => array(
			'className' => 'Welder',
			'foreignKey' => 'welder_id',
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
        
                var $virtualFields = array(
            'certificat' => "CONCAT(WelderMonitoring.norm, '-', WelderMonitoring.welding_method,'-',WelderMonitoring.material_form,'-',WelderMonitoring.weld_category,'-',WelderMonitoring.welding_additional_group,'-',WelderMonitoring.welding_additional,'-',WelderMonitoring.dimension,'-',WelderMonitoring.welding_position,'-',WelderMonitoring.weld_details)"
        );
}

