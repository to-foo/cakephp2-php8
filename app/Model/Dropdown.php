<?php
App::uses('AppModel', 'Model');
/**
 * Dropdown Model
 *
 */
class Dropdown extends AppModel
{

    public $hasAndBelongsToMany = array(
        'Testingcomp' => array(
            'className' => 'Testingcomp',
            'joinTable' => 'testingcomps_dropdowns',
            'foreignKey' => 'dropdown_id',
            'associationForeignKey' => 'testingcomp_id',
            'unique' => 'keepExisting',
            'conditions' => '',
            'fields' => '',
            'order' => '',
            'limit' => '',
            'offset' => '',
            'finderQuery' => '',
            'deleteQuery' => '',
            'insertQuery' => '',
        ),
    );

    public $belongsTo = array(
        'Testingcomp' => array(
            'className' => 'Testingcomp',
            'foreignKey' => 'testingcomp_id',
            'conditions' => '',
            'fields' => '',
            'order' => '',
        ),
        'Report' => array(
            'className' => 'Report',
            'foreignKey' => 'report_id',
        ),
    );
}
