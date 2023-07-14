<?php

App::uses('AppModel', 'Model');

/**

 * DropdownsValue Model

 *

 * @property Testingcomp $Testingcomp

 * @property User $User

 * @property Report $Report

 * @property Dropdown $Dropdown

 * @property Dependency $Dependency

 */

class DropdownsValue extends AppModel {



/**

 * Display field

 *

 * @var string

 */

	public $displayField = 'field';





	//The Associations below have been created with all possible keys, those that are not needed can be removed



/**

 * belongsTo associations

 *

 * @var array

 */

	public $belongsTo = array(

		'Testingcomp' => array(

			'className' => 'Testingcomp',

			'foreignKey' => 'testingcomp_id',

			'conditions' => '',

			'fields' => '',

			'order' => ''

		),

		'User' => array(

			'className' => 'User',

			'foreignKey' => 'user_id',

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



/**

 * hasMany associations

 *

 * @var array

 */

	public $hasMany = array(

		'Dependency' => array(

			'className' => 'Dependency',

			'foreignKey' => 'dropdowns_value_id',

			'dependent' => true,

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

