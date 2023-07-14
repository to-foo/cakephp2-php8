<?php
App::uses('AppModel', 'Model');
/**
 * User Model
 *
 * @property Roll $Roll
 * @property Testingcomp $Testingcomp
 * @property Qualification $Qualification
 */
class ExternData extends AppModel {

	public $useTable = 'extern_datas';
	

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
        'Extern' => array(
            'className' => 'Extern',
            'foreignKey' => 'id'
        )
    );	
}
