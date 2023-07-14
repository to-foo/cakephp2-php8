<?php
App::uses('AppModel', 'Model');
/**
 * Model
 *
 */
class ExpeditingEvent extends AppModel {

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
