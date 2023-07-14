<?php
App::uses('AppModel', 'Model');

class Changelog extends AppModel {

  public $useTable = 'changelogs';
	//public $actsAs = array('Containable');

	public $hasMany = array(
		'ChangelogData' => array(
			'className' => 'ChangelogData',
			'foreignKey' => 'changelog_id',
			'dependent' => true,
      'conditions' => array('category <>' => 'Testeintrag' ),
		),
    'Changelogfile' => array(
      'className' => 'Changelogfile',
      'foreignKey' => 'changelog_id',
      'dependent' => true,
    )
	);

  public $hasAndBelongsToMany = array(
    'ChangelogUser' => array(
      'className' => 'ChangelogUser',
      'joinTable' => 'changelogs_users',
      'foreignKey' => 'changelog_id',
      'associationForeignKey' => 'user_id',
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
