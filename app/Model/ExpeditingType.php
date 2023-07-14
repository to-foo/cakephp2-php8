<?php
App::uses('AppModel', 'Model');
/**
 * User Model
 *
 * @property Roll $Roll
 * @property Testingcomp $Testingcomp
 * @property Qualification $Qualification
 */
class ExpeditingType extends AppModel {

  	public $validate = array(
		'description' => array(
			'notBlank' => array(
				'rule' => array('notBlank'),
			)
		)
	);
}
