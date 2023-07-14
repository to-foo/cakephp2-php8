<?php
App::uses('AppModel', 'Model');
/**
 * User Model
 *
 * @property Roll $Roll
 * @property Testingcomp $Testingcomp
 * @property Qualification $Qualification
 */
class ExpeditingSetsExpeditingType extends AppModel {

  	public $validate = array(
		/*
		'period_datum' => array(
			'notBlank' => array(
				'rule' => array('notBlank'),
			)
		),
		'period_sign' => array(
			'notBlank' => array(
				'rule' => array('notBlank'),
			)
		),
		'period_time' => array(
			'notBlank' => array(
				'rule' => array('notBlank'),
			)
		),
		'period_measure' => array(
			'notBlank' => array(
				'rule' => array('notBlank'),
			)
		),
		*/
	);
}
