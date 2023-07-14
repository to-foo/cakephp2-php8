<?php
App::uses('AppModel', 'Model');
/**
 * Language Model
 *
 */
class WelderEyecheck extends AppModel {
	public $hasMany = array(
		'WelderEyecheckData' => array(
			'className' => 'WelderEyecheckData',
			'foreignKey' => 'certificate_id',
			'dependent' => false,
		)
	);

}
