<?php
App::uses('AppModel', 'Model');
/**
 * Language Model
 *
 */
class Certificate extends AppModel {

	public $virtualFields = array(
        'upper_testingmethod' => "UPPER(Certificate.testingmethod)"
    );

	public $belongsTo = array(
		'Examiner' => array(
			'className' => 'Examiner',
			'foreignKey' => 'examiner_id',
			'conditions' => '',
			'fields' => '',
			'order' => ''
		),
	);

	public $hasMany = array(
		'CertificateData' => array(
			'className' => 'CertificateData',
			'foreignKey' => 'certificate_id',
			'dependent' => false,
		),
		'CertificatesTestingmethodes' => array(
			'className' => 'CertificatesTestingmethodes',
			'foreignKey' => 'certificate_id',
			'dependent' => false,
		),
	);

	public $hasAndBelongsToMany = array(
		'Testingmethod' => array(
			'className' => 'Testingmethod',
			'joinTable' => 'testingmethods_certificates',
			'foreignKey' => 'certificate_id',
			'associationForeignKey' => 'testingmethod_id',
			'unique' => 'keepExisting',
//			'unique' => false,
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

		'level' => array(
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

    	'third_part' => array(
        	'notBlank' => array(
            	'rule' => array('notBlank'),
				'message' => 'Your custom message here',
        	),
		),

		'testingmethod' => array(
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
		'horizon' => array(
			'notBlank' => array(
				'rule' => array('notBlank'),
				'message' => 'Your custom message here',
			),
		),
	);
}
