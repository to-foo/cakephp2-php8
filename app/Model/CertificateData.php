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

class CertificateData extends AppModel {
public $useTable = 'certificate_datas';
	public $belongsTo = array(
		'Certificate' => array(
			'className' => 'Certificate',
			'foreignKey' => 'certificate_id',
			'conditions' => '',
			'fields' => '',
			'order' => ''
		),
		'Examiner' => array(
			'className' => 'Examiner',
			'foreignKey' => 'examiner_id',
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
		'expiration_date' => array(
			'notBlank' => array(
				'rule' => array('notBlank'),
				'message' => 'Your custom message here',
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
