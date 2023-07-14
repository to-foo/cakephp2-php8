<?php
/**
 * StripDatumFixture
 *
 */
class StripDatumFixture extends CakeTestFixture {

/**
 * Fields
 *
 * @var array
 */
	public $fields = array(
		'id' => array('type' => 'integer', 'null' => false, 'default' => null, 'key' => 'primary'),
		'topproject_id' => array('type' => 'integer', 'null' => false, 'default' => null),
		'equipment_type_id' => array('type' => 'integer', 'null' => false, 'default' => null),
		'equipment_id' => array('type' => 'integer', 'null' => false, 'default' => null),
		'order_id' => array('type' => 'integer', 'null' => false, 'default' => null),
		'report_id' => array('type' => 'integer', 'null' => false, 'default' => null),
		'description' => array('type' => 'string', 'null' => false, 'default' => null, 'collate' => 'utf8_general_ci', 'charset' => 'utf8'),
		'batch_no' => array('type' => 'string', 'null' => false, 'default' => null, 'collate' => 'utf8_general_ci', 'charset' => 'utf8'),
		'certificate' => array('type' => 'string', 'null' => false, 'default' => null, 'collate' => 'utf8_general_ci', 'charset' => 'utf8'),
		'Sr' => array('type' => 'string', 'null' => false, 'default' => null, 'length' => 10, 'collate' => 'utf8_general_ci', 'charset' => 'utf8'),
		'Cr' => array('type' => 'string', 'null' => false, 'default' => null, 'length' => 10, 'collate' => 'utf8_general_ci', 'charset' => 'utf8'),
		'processor_type' => array('type' => 'string', 'null' => false, 'default' => null, 'collate' => 'utf8_general_ci', 'charset' => 'utf8'),
		'processor_f_no' => array('type' => 'string', 'null' => false, 'default' => null, 'collate' => 'utf8_general_ci', 'charset' => 'utf8'),
		'developer_type' => array('type' => 'string', 'null' => false, 'default' => null, 'collate' => 'utf8_general_ci', 'charset' => 'utf8'),
		'developer_temp' => array('type' => 'string', 'null' => false, 'default' => null, 'collate' => 'utf8_general_ci', 'charset' => 'utf8'),
		'developer_replenishment' => array('type' => 'string', 'null' => false, 'default' => null, 'collate' => 'utf8_general_ci', 'charset' => 'utf8'),
		'fixer_type' => array('type' => 'string', 'null' => false, 'default' => null, 'collate' => 'utf8_general_ci', 'charset' => 'utf8'),
		'fixer_temp' => array('type' => 'string', 'null' => false, 'default' => null, 'collate' => 'utf8_general_ci', 'charset' => 'utf8'),
		'fixer_replenishment' => array('type' => 'string', 'null' => false, 'default' => null, 'collate' => 'utf8_general_ci', 'charset' => 'utf8'),
		'examination_object' => array('type' => 'string', 'null' => false, 'default' => null, 'collate' => 'utf8_general_ci', 'charset' => 'utf8'),
		'year' => array('type' => 'string', 'null' => false, 'default' => null, 'length' => 4, 'collate' => 'utf8_general_ci', 'charset' => 'utf8'),
		'development' => array('type' => 'string', 'null' => false, 'default' => null, 'collate' => 'utf8_general_ci', 'charset' => 'utf8'),
		'created' => array('type' => 'string', 'null' => false, 'default' => null, 'collate' => 'utf8_general_ci', 'charset' => 'utf8'),
		'modified' => array('type' => 'string', 'null' => false, 'default' => null, 'collate' => 'utf8_general_ci', 'charset' => 'utf8'),
		'user_id' => array('type' => 'integer', 'null' => false, 'default' => null),
		'testingcomp_id' => array('type' => 'integer', 'null' => false, 'default' => null),
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
			'topproject_id' => 1,
			'equipment_type_id' => 1,
			'equipment_id' => 1,
			'order_id' => 1,
			'report_id' => 1,
			'description' => 'Lorem ipsum dolor sit amet',
			'batch_no' => 'Lorem ipsum dolor sit amet',
			'certificate' => 'Lorem ipsum dolor sit amet',
			'Sr' => 'Lorem ip',
			'Cr' => 'Lorem ip',
			'processor_type' => 'Lorem ipsum dolor sit amet',
			'processor_f_no' => 'Lorem ipsum dolor sit amet',
			'developer_type' => 'Lorem ipsum dolor sit amet',
			'developer_temp' => 'Lorem ipsum dolor sit amet',
			'developer_replenishment' => 'Lorem ipsum dolor sit amet',
			'fixer_type' => 'Lorem ipsum dolor sit amet',
			'fixer_temp' => 'Lorem ipsum dolor sit amet',
			'fixer_replenishment' => 'Lorem ipsum dolor sit amet',
			'examination_object' => 'Lorem ipsum dolor sit amet',
			'year' => 'Lo',
			'development' => 'Lorem ipsum dolor sit amet',
			'created' => 'Lorem ipsum dolor sit amet',
			'modified' => 'Lorem ipsum dolor sit amet',
			'user_id' => 1,
			'testingcomp_id' => 1,
			'deleted' => 1
		),
	);

}
