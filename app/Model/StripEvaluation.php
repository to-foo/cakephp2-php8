<?php
App::uses('AppModel', 'Model');
/**
 * StripEvaluation Model
 *
 * @property StripDatum $StripDatum
 */
class StripEvaluation extends AppModel {

/**
 * Use table
 *
 * @var mixed False or table name
 */
	public $useTable = 'strip_evaluation';


	//The Associations below have been created with all possible keys, those that are not needed can be removed

/**
 * belongsTo associations
 *
 * @var array
 */
	public $belongsTo = array(
		'StripDatum' => array(
			'className' => 'StripDatum',
			'foreignKey' => 'strip_datum_id',
			'conditions' => '',
			'fields' => '',
			'order' => ''
		)
	);
}
