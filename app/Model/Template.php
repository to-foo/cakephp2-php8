<?php
App::uses('AppModel', 'Model');
/**
 * Testingmethod Model
 *
 */
class Template extends AppModel {

  public $validate = array(
 		'name'=>array(
			'notBlank' => array(
				'rule' => 'notBlank',
				'message' => 'This field may not be empty'
			)
		),
		'description' => array(
			'notBlank' => array(
				'rule' => 'notBlank',
				'message' => 'This field may not be empty'
			)
		),
  );

  public $hasAndBelongsToMany = array(
		'Testingcomp' => array(
			'className' => 'Testingcomp',
			'joinTable' => 'templates_testingcomps',
			'foreignKey' => 'templates_id',
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
		)
	);
}
