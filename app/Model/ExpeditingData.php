<?php
App::uses('AppModel', 'Model');
/**
 * User Model
 *
 * @property Roll $Roll
 * @property Testingcomp $Testingcomp
 * @property Qualification $Qualification
 */
class ExpeditingData extends AppModel {

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
        'Expediting' => array(
            'className' => 'Expediting',
            'foreignKey' => 'id'
        )
    );	
}
