<?php
App::uses('AppModel', 'Model');
/**
 * Weldingprocess Model
 *
 */
class Imageconfiguration extends AppModel {

/**
 * Display field
 *
 * @var string
 */

public $belongsTo = array(
		'User' => array(
			'className' => 'User',
			'foreignKey' => 'user_id',
			'conditions' => '',
			'fields' => '',
			'order' => ''
		),
		'Testingcomp' => array(
			'className' => 'Testingcomp',
			'foreignKey' => 'testingcomp_id',
			'conditions' => '',
			'fields' => '',
			'order' => ''
		),
		'Report' => array(
			'className' => 'Report',
			'foreignKey' => 'report_id',
			'conditions' => '',
			'fields' => '',
			'order' => ''
		),
		'Dropdown' => array(
			'className' => 'Dropdown',
			'foreignKey' => 'dropdown_id',
			'conditions' => '',
			'fields' => '',
			'order' => ''
		)										
	);
}
