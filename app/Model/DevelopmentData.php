<?php
App::uses('AppModel', 'Model');
/**
 * Weldingprocess Model
 *
 */
class DevelopmentData extends AppModel {
public $useTable = 'development_datas';
/**
 * Display field
 *
 * @var string
 */

public $belongsTo = array(
		'Development' => array(
			'className' => 'Development',
			'foreignKey' => 'development_id',
			'conditions' => '',
			'fields' => '',
			'order' => ''
		)						
	);

/*	
	public $hasAndBelongsToMany = array(
		'Development' => array(
			'className' => 'Development',
			'joinTable' => 'developments_development_datas',
			'foreignKey' => 'development_data_id',
			'associationForeignKey' => 'development_id',
			'unique' => 'keepExisting',
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
*/		
}
