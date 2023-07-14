<?php
App::uses('AppModel', 'Model');
/**
 * User Model
 *
 * @property Roll $Roll
 * @property Testingcomp $Testingcomp
 * @property Qualification $Qualification
 */
class UserData extends AppModel {

	public $useTable = 'user_datas';

/**
 * Validation rules
 *
 * @var array
 */

/**
 * belongsTo associations
 *
 * @var array
 */
    public $belongsTo = array(
        'User' => array(
            'className' => 'User',
            'foreignKey' => 'id'
        )
    );	
}
