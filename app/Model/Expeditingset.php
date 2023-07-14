<?php
App::uses('AppModel', 'Model');
/**
 * User Model
 *
 * @property Roll $Roll
 * @property Testingcomp $Testingcomp
 * @property Qualification $Qualification
 */
class Expeditingset extends AppModel {

  	public $validate = array(
		'name' => array(
			'notBlank' => array(
				'rule' => array('notBlank'),
			)
		)
	);

	public $hasMany = array(
        'ExpeditingSetsExpeditingType' => array(
            'className' => 'ExpeditingSetsExpeditingType',
            'foreignKey' => 'expediting_set_id',
			'order' => 'ExpeditingSetsExpeditingType.sorting ASC',
		),
		'ExpeditingSetsExpeditingRoll' => array(
            'className' => 'ExpeditingSetsExpeditingRoll',
            'foreignKey' => 'expediting_set_id',
		),
    );
}
