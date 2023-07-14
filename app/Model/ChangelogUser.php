<?php
App::uses('AppModel', 'Model');

class ChangelogUser extends AppModel {

  public $useTable = 'changelogs_users';
	//public $actsAs = array('Containable');

  public $hasAndBelongsToMany = array(
    'Changelog' => array(
      'className' => 'Changelog',
      'joinTable' => 'changelogs_users',
      'foreignKey' => 'user_id',
      'associationForeignKey' => 'changelog_id',
      'unique' => 'keepExisting',
      'conditions' => '',
      'fields' => '',
      'order' => '',
      'limit' => '',
      'offset' => '',
      'finderQuery' => '',
      'deleteQuery' => '',
      'insertQuery' => ''
    )
  );
}
