<?php
App::uses('AppModel', 'Model');
/**
 * Testingcomp Model
 *
 * @property Roll $Roll
 * @property Qualification $Qualification
 * @property User $User
 * @property Topproject $Topproject
 */
class Testingcompcat extends AppModel {

/**
 * validation rules
 * 
 * @var array
 */
 

	//The Associations below have been created with all possible keys, those that are not needed can be removed

/**
 * belongsTo associations
 *
 * @var array
 */

/**
 * hasMany associations
 *
 * @var array
 */



/**
 * hasAndBelongsToMany associations
 *
 * @var array
 */
	public $hasAndBelongsToMany = array(
			
            'Testingcomp' => array(
			'className' => 'Testingcomp',
			'joinTable' => 'testingcompcategories_testingcomps',
			'foreignKey' => 'testingcomp_id',
			'associationForeignKey' => 'testingcomp_category_id',
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
