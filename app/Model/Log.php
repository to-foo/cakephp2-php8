<?php
App::uses('AppModel', 'Model');
/**
 * Log Model

 */
class Log extends AppModel {
	public $belongsTo = array(
		'User'=>array(
			'className' => 'User',
			'foreignKey' => 'user_id',
			'dependent' => false
		)
	);
}
