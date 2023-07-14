<?php
App::uses('AppModel', 'Model');
/**
 * Roll Model
 *
 * @property Testingcomp $Testingcomp
 * @property User $User
 */
class Specialcharacter extends AppModel {

/**
 * Validation rules
 *
 * @var array
 */

  public $validate = array(
	    'value' => array(
	        'notBlank' => array(
	            'rule' => array('notBlank'),
	            'required' => true,
	        ),
	    ),
	);
}
