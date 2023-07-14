<?php
App::uses('AppModel', 'Model');
/**
 * Language Model
 *
 */
class DropdownsMastersData extends AppModel {

  public $validate = array(
		'value' => array(
			'notBlank' => array(
				'rule' => array('notBlank'),
				'message' => 'Your custom message here',
			),
		),
	);
}
