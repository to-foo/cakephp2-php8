<?php
App::uses('AppModel', 'Model');
/**
 * Topproject Model
 *
 *
 */
class Task extends AppModel {

	// The Associations below have been created with all possible keys, those that are not needed can be removed

/**
 * hasAndBelongsToMany associations
 *
 *
 */
	public $belongsTo = array(
		'Cascade' => array(
			'className' => 'Cascade',
			'foreignKey' => 'cascade_id',
			'dependent' => true,
	
		));

}
?>
