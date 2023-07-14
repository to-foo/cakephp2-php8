<?php
/**
 * QualificationFixture
 *
 */
class QualificationFixture extends CakeTestFixture {

/**
 * Fields
 *
 * @var array
 */
	public $fields = array(
		'id' => array('type' => 'integer', 'null' => false, 'default' => null, 'key' => 'primary'),
		'examiner-id' => array('type' => 'integer', 'null' => false, 'default' => null),
		'certification-number' => array('type' => 'string', 'null' => false, 'default' => null, 'length' => 200, 'collate' => 'latin1_swedish_ci', 'charset' => 'latin1'),
		'testingmethod_id' => array('type' => 'integer', 'null' => false, 'default' => null),
		'level' => array('type' => 'integer', 'null' => false, 'default' => null, 'length' => 20),
		'timeperiod' => array('type' => 'date', 'null' => false, 'default' => null),
		'created' => array('type' => 'datetime', 'null' => false, 'default' => null),
		'modified' => array('type' => 'datetime', 'null' => true, 'default' => null),
		'user_id' => array('type' => 'integer', 'null' => true, 'default' => null),
		'testingcomp_id' => array('type' => 'integer', 'null' => false, 'default' => null),
		'supervisors' => array('type' => 'boolean', 'null' => false, 'default' => null),
		'indexes' => array(
			'PRIMARY' => array('column' => 'id', 'unique' => 1)
		),
		'tableParameters' => array('charset' => 'latin1', 'collate' => 'latin1_swedish_ci', 'engine' => 'MyISAM')
	);

/**
 * Records
 *
 * @var array
 */
	public $records = array(
		array(
			'id' => 1,
			'examiner-id' => 1,
			'certification-number' => 'Lorem ipsum dolor sit amet',
			'testingmethod_id' => 1,
			'level' => 1,
			'timeperiod' => '2013-10-23',
			'created' => '2013-10-23 16:27:54',
			'modified' => '2013-10-23 16:27:54',
			'user_id' => 1,
			'testingcomp_id' => 1,
			'supervisors' => 1
		),
	);

}
