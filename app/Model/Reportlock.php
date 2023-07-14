<?php

App::uses('AppModel', 'Model');

/**

 * Reportlock Model

 *

 * @property Topproject $Topproject

 * @property Report $Report

 * @property User $User

 */

class Reportlock extends AppModel {





	//The Associations below have been created with all possible keys, those that are not needed can be removed



/**

 * belongsTo associations

 *

 * @var array

 */

	public $belongsTo = array(

		'Topproject' => array(

			'className' => 'Topproject',

			'foreignKey' => 'topproject_id',

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

		'User' => array(

			'className' => 'User',

			'foreignKey' => 'user_id',

			'conditions' => '',

			'fields' => '',

			'order' => ''

		)

	);

}

