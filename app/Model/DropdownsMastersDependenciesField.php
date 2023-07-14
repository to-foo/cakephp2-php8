<?php
App::uses('AppModel', 'Model');
/**
 * Language Model
 *
 */
class DropdownsMastersDependenciesField extends AppModel {

  public $validate = array(
		'field' => array(
			'notBlank' => array(
				'rule' => array('notBlank'),
				'message' => 'Your custom message here',
			),
		),
	);
}
