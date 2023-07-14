<?php
App::uses('AppModel', 'Model');

class Landingpage extends AppModel {

/**
 * Use table
 *
 * @var mixed False or table name
 */

	//The Associations below have been created with all possible keys, those that are not needed can be removed

	public $hasMany = array(
		'LandingpagesData' => array(
			'className' => 'LandingpagesData',
			'foreignKey' => 'landingpages_id',
			'dependent' => true,
			'fields' => '',
			'order' => ''
		)
	);
}
