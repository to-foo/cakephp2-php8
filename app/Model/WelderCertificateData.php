<?php

App::uses('AppModel', 'Model');

/**

 * DropdownsValue Model

 *

 * @property Testingcomp $Testingcomp

 * @property User $User

 * @property Report $Report

 * @property Dropdown $Dropdown

 * @property Dependency $Dependency

 */

class WelderCertificateData extends AppModel {

	public $belongsTo = array(
		'WelderCertificate' => array(
			'className' => 'WelderCertificate',
			'foreignKey' => 'certificate_id',
			'conditions' => '',
			'fields' => '',
			'order' => ''
		),
		'Welder' => array(
			'className' => 'Welder',
			'foreignKey' => 'welder_id',
			'conditions' => '',
			'fields' => '',
			'order' => ''
		)
		
	);

	public $validate = array(

    	'first_certification' => array(
        	'notBlank' => array(
            	'rule' => array('notBlank'),
				'message' => 'Your custom message here',
        	),
		),		
		'certified_date' => array(
			'notBlank' => array(
				'rule' => array('notBlank'),
				'message' => 'Your custom message here',
			),
		),
		'renewal_in_year' => array(
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
		'certified' => array(
			'notBlank' => array(
//				'rule' => array('notBlank'),
				'rule' => array('comparison', '>=', 0),
				'message' => 'Your custom message here',
			),
		),
	);	
}

