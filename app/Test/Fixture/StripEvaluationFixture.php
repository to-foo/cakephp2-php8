<?php
/**
 * StripEvaluationFixture
 *
 */
class StripEvaluationFixture extends CakeTestFixture {

/**
 * Table name
 *
 * @var string
 */
	public $table = 'strip_evaluation';

/**
 * Fields
 *
 * @var array
 */
	public $fields = array(
		'id' => array('type' => 'integer', 'null' => false, 'default' => null, 'key' => 'primary'),
		'strip_datum_id' => array('type' => 'integer', 'null' => false, 'default' => null),
		'date' => array('type' => 'string', 'null' => false, 'default' => null, 'collate' => 'utf8_general_ci', 'charset' => 'utf8'),
		'D0' => array('type' => 'integer', 'null' => false, 'default' => null),
		'Dx' => array('type' => 'integer', 'null' => false, 'default' => null),
		'Dx4' => array('type' => 'integer', 'null' => false, 'default' => null),
		'developer_fresh' => array('type' => 'boolean', 'null' => false, 'default' => null),
		'fixer_fresh' => array('type' => 'boolean', 'null' => false, 'default' => null),
		'created' => array('type' => 'string', 'null' => false, 'default' => null, 'collate' => 'utf8_general_ci', 'charset' => 'utf8'),
		'modified' => array('type' => 'integer', 'null' => false, 'default' => null, 'length' => 255),
		'deleted' => array('type' => 'boolean', 'null' => false, 'default' => '0'),
		'indexes' => array(
			'PRIMARY' => array('column' => 'id', 'unique' => 1)
		),
		'tableParameters' => array('charset' => 'utf8', 'collate' => 'utf8_general_ci', 'engine' => 'InnoDB')
	);

/**
 * Records
 *
 * @var array
 */
	public $records = array(
		array(
			'id' => 1,
			'strip_datum_id' => 1,
			'date' => 'Lorem ipsum dolor sit amet',
			'D0' => 1,
			'Dx' => 1,
			'Dx4' => 1,
			'developer_fresh' => 1,
			'fixer_fresh' => 1,
			'created' => 'Lorem ipsum dolor sit amet',
			'modified' => 1,
			'deleted' => 1
		),
	);

}
