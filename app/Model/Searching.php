<?php
App::uses('AppModel', 'Model');

class Searching extends AppModel {
	public $hasMany = array(
		'SearchingValue' => array(
			'className' => 'SearchingValue',
			'foreignKey' => 'searching_id',
		),
	);

}
