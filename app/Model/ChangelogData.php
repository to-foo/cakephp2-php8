<?php
App::uses('AppModel', 'Model');

class ChangelogData extends AppModel {

  public $useTable = 'changelog_datas';
	//public $actsAs = array('Containable');
/*
	public $hasOne = array(
		'Changelogfile' => array(
      'className' => 'Changelogfile',
			'foreignKey' => 'changelog_data_id ',
			'dependent' => true,
		)
	 );
*/
  public $belongsTo = array(
    'Changelog' => array(
      'className' => 'Changelog',
      'foreignKey' => 'changelog_id',
      'conditions' => '',
      'fields' => '',
      'order' => ''
    )
  );
}
