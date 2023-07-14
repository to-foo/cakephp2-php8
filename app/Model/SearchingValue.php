<?php
App::uses('AppModel', 'Model');

class SearchingValue extends AppModel {
    public $belongsTo = array(
        'Searching' => array(
            'className' => 'Searching',
            'foreignKey' => 'id'
        )
    );	

}
