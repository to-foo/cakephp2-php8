<?php

App::uses('AppModel', 'Model');


class WelderMonitoringData extends AppModel {
 public $useTable = 'welder_monitoring_datas';
	public $belongsTo = array(
		'Welder' => array(
			'className' => 'Welder',
			'foreignKey' => 'welder_id',
			'conditions' => '',
			'fields' => '',
			'order' => ''
		),
		'WelderMonitoring' => array(
			'className' => 'WelderMonitoring',
			'foreignKey' => 'welder_monitoring_id',
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
                 var $virtualFields = array(
            'certificat' => "CONCAT(WelderMonitoringData.norm, '-', WelderMonitoringData.welding_method,'-',WelderMonitoringData.material_form,'-',WelderMonitoringData.weld_category,'-',WelderMonitoringData.welding_additional_group,'-',WelderMonitoringData.welding_additional,'-',WelderMonitoringData.dimension,'-',WelderMonitoringData.welding_position,'-',WelderMonitoringData.weld_details)"
        );
}

