<?php
App::uses('AppModel', 'Model');
/**
 * Topproject Model
 *
 *
 */
class Ticket extends AppModel {

	// The Associations below have been created with all possible keys, those that are not needed can be removed

/**
 * hasAndBelongsToMany associations
 *
 *
 */
	public $hasMany = array(
		'TicketData' => array(
			'className' => 'TicketData',
			'foreignKey' => 'ticket_id',
			'dependent' => true,
		),

	);
}
?>
