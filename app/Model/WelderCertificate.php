<?php
App::uses('AppModel', 'Model');
/**
 * Language Model
 *
 */
class WelderCertificate extends AppModel {

 public $belongsTo = array(
        'Welder' => array(
            'className' => 'Welder',
            'foreignKey' => 'id'
        )
    );
	
	public $hasMany = array(
		'WelderCertificateData' => array(
			'className' => 'WelderCertificateData',
			'foreignKey' => 'certificate_id',
			'dependent' => false,
		)
	);

	public $validate = array(

    	'third_part' => array(
        	'notBlank' => array(
            	'rule' => array('notBlank'),
				'message' => 'Your custom message here',
        	),
		),		
		'sector' => array(
			'notBlank' => array(
				'rule' => array('notBlank'),
				'message' => 'Your custom message here',
			),
		),
	
		'certificat' => array(
			'notBlank' => array(
				'rule' => array('notBlank'),
				'message' => 'Your custom message here',
			),
		),
		'exam_date' => array(
			'notBlank' => array(
				'rule' => array('notBlank'),
				'message' => 'Your custom message here',
			),
		),
		'first_certification' => array(
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
	);
}
