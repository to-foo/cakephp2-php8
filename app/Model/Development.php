<?php
App::uses('AppModel', 'Model');

class Development extends AppModel {

	public $hasAndBelongsToMany = array(
		'Testingcomp' => array(
			'className' => 'Testingcomp',
			'joinTable' => 'testingcomps_developments',
			'foreignKey' => 'development_id',
			'associationForeignKey' => 'testingcomp_id',
			'unique' => 'keepExisting',
			'conditions' => '',
			'fields' => '',
			'order' => '',
			'limit' => '',
			'offset' => '',
			'finderQuery' => '',
			'deleteQuery' => '',
			'insertQuery' => ''
		),
		'Order' => array(
			'className' => 'Order',
			'joinTable' => 'orders_developments',
			'foreignKey' => 'development_id',
			'associationForeignKey' => 'order_id',
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
/*		
		'DevelopmentData' => array(
			'className' => 'DevelopmentData',
			'joinTable' => 'developments_development_datas',
			'foreignKey' => 'development_id',
			'associationForeignKey' => 'development_data_id',
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
*/		
	);

	public $hasMany = array(
		'Order' => array(
			'className' => 'Order',
			'foreignKey' => 'development_id',
			'dependent' => false,
			'conditions' => '',
			'fields' => '',
			'order' => '',
			'limit' => '',
			'offset' => '',
			'exclusive' => '',
			'finderQuery' => '',
			'counterQuery' => ''
		),
		'DevelopmentData' => array(
			'className' => 'DevelopmentData',
			'foreignKey' => 'development_id',
			'dependent' => false,
			'conditions' => '',
			'fields' => '',
			'order' => '',
			'limit' => '',
			'offset' => '',
			'exclusive' => '',
			'finderQuery' => '',
			'counterQuery' => ''
		)
	);

}
