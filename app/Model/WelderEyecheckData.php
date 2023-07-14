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

class WelderEyecheckData extends AppModel {
 public $useTable = 'welder_eyecheck_datas';
	public $belongsTo = array(
		'WelderEyecheck' => array(
			'className' => 'WelderEyecheck',
			'foreignKey' => 'certificate_id',
			'conditions' => '',
			'fields' => '',
			'order' => ''
		),
		'Welder' => array(
			'className' => 'Welder',
			'foreignKey' => 'welder_id',
			'conditions' => '',
			'fields' => '',
			'order' => ''
		)
	);
	
}

