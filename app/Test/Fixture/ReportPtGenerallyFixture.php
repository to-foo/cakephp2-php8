<?php
/**
 * ReportPtGenerallyFixture
 *
 */
class ReportPtGenerallyFixture extends CakeTestFixture {

/**
 * Table name
 *
 * @var string
 */
	public $table = 'report_pt_generally';

/**
 * Fields
 *
 * @var array
 */
	public $fields = array(
		'id' => array('type' => 'integer', 'null' => false, 'default' => null, 'key' => 'primary'),
		'reportnumber_id' => array('type' => 'integer', 'null' => false, 'default' => null),
		'factory_no' => array('type' => 'string', 'null' => false, 'default' => null, 'length' => 250, 'collate' => 'latin1_swedish_ci', 'charset' => 'latin1'),
		'order' => array('type' => 'string', 'null' => false, 'default' => null, 'length' => 250, 'collate' => 'latin1_swedish_ci', 'charset' => 'latin1'),
		'drawing_no' => array('type' => 'string', 'null' => false, 'default' => null, 'length' => 250, 'collate' => 'latin1_swedish_ci', 'charset' => 'latin1'),
		'test_piece' => array('type' => 'string', 'null' => false, 'default' => null, 'length' => 250, 'collate' => 'latin1_swedish_ci', 'charset' => 'latin1'),
		'test_location' => array('type' => 'string', 'null' => false, 'default' => null, 'length' => 250, 'collate' => 'latin1_swedish_ci', 'charset' => 'latin1'),
		'manufacturer' => array('type' => 'string', 'null' => false, 'default' => null, 'length' => 250, 'collate' => 'latin1_swedish_ci', 'charset' => 'latin1'),
		'date_of_test' => array('type' => 'date', 'null' => false, 'default' => null),
		'material' => array('type' => 'string', 'null' => false, 'default' => null, 'length' => 250, 'collate' => 'latin1_swedish_ci', 'charset' => 'latin1'),
		'examiner' => array('type' => 'string', 'null' => false, 'default' => null, 'length' => 250, 'collate' => 'latin1_swedish_ci', 'charset' => 'latin1'),
		'specification' => array('type' => 'string', 'null' => false, 'default' => null, 'length' => 250, 'collate' => 'latin1_swedish_ci', 'charset' => 'latin1'),
		'scope_of_testing' => array('type' => 'string', 'null' => false, 'default' => null, 'length' => 250, 'collate' => 'latin1_swedish_ci', 'charset' => 'latin1'),
		'time_of_testing' => array('type' => 'string', 'null' => false, 'default' => null, 'length' => 250, 'collate' => 'latin1_swedish_ci', 'charset' => 'latin1'),
		'assessment' => array('type' => 'string', 'null' => false, 'default' => null, 'length' => 250, 'collate' => 'latin1_swedish_ci', 'charset' => 'latin1'),
		'created' => array('type' => 'datetime', 'null' => false, 'default' => null),
		'modified' => array('type' => 'datetime', 'null' => false, 'default' => null),
		'indexes' => array(
			'PRIMARY' => array('column' => 'id', 'unique' => 1)
		),
		'tableParameters' => array('charset' => 'latin1', 'collate' => 'latin1_swedish_ci', 'engine' => 'InnoDB')
	);

/**
 * Records
 *
 * @var array
 */
	public $records = array(
		array(
			'id' => 1,
			'reportnumber_id' => 1,
			'factory_no' => 'Lorem ipsum dolor sit amet',
			'order' => 'Lorem ipsum dolor sit amet',
			'drawing_no' => 'Lorem ipsum dolor sit amet',
			'test_piece' => 'Lorem ipsum dolor sit amet',
			'test_location' => 'Lorem ipsum dolor sit amet',
			'manufacturer' => 'Lorem ipsum dolor sit amet',
			'date_of_test' => '2013-11-22',
			'material' => 'Lorem ipsum dolor sit amet',
			'examiner' => 'Lorem ipsum dolor sit amet',
			'specification' => 'Lorem ipsum dolor sit amet',
			'scope_of_testing' => 'Lorem ipsum dolor sit amet',
			'time_of_testing' => 'Lorem ipsum dolor sit amet',
			'assessment' => 'Lorem ipsum dolor sit amet',
			'created' => '2013-11-22 13:04:16',
			'modified' => '2013-11-22 13:04:16'
		),
	);

}
