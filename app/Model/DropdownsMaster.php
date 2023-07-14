<?php
App::uses('AppModel', 'Model');

class DropdownsMaster extends AppModel
{

    public $hasAndBelongsToMany = array(

        'Topproject' => array(
            'className' => 'Topproject',
            'joinTable' => 'dropdowns_masters_topprojects',
            'foreignKey' => 'dropdowns_masters_id',
            'associationForeignKey' => 'topproject_id',
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
        'Report' => array(
            'className' => 'Report',
            'joinTable' => 'dropdowns_masters_reports',
            'foreignKey' => 'dropdowns_masters_id',
            'associationForeignKey' => 'report_id',
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
        'Testingmethod' => array(
            'className' => 'Testingmethod',
            'joinTable' => 'dropdowns_masters_testingmethods',
            'foreignKey' => 'dropdowns_masters_id',
            'associationForeignKey' => 'testingmethod_id',
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
        'Testingcomp' => array(
            'className' => 'Testingcomp',
            'joinTable' => 'dropdowns_masters_testingcomps',
            'foreignKey' => 'dropdowns_masters_id',
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

    public $hasMany = array(
        'DropdownsMastersData' => array(
            'className' => 'DropdownsMastersData',
            'foreignKey' => 'dropdowns_masters_id',
            'order' => array(
                'DropdownsMastersData.value ASC',
            ),
            'dependent' => true,
        ),
    );

    public $validate = array(
        'name' => array(
            'notBlank' => array(
                'rule' => array('notBlank'),
                'message' => 'Your custom message here',
            ),
        ),
        'modul' => array(
            'notBlank' => array(
                'rule' => array('notBlank'),
                'message' => 'Your custom message here',
            ),
        ),
        'field' => array(
            'notBlank' => array(
                'rule' => array('notBlank'),
                'message' => 'Your custom message here',
            ),
        ),
    );
}
