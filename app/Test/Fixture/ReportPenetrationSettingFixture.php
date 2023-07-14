<?php
/**
 * ReportPenetrationSettingFixture
 *
 */
class ReportPenetrationSettingFixture extends CakeTestFixture {

/**
 * Fields
 *
 * @var array
 */
	public $fields = array(
		'id' => array('type' => 'integer', 'null' => false, 'default' => null, 'key' => 'primary'),
		'topproject_id' => array('type' => 'integer', 'null' => false, 'default' => null),
		'report_id' => array('type' => 'integer', 'null' => false, 'default' => null),
		'testingmethod_id' => array('type' => 'integer', 'null' => false, 'default' => null),
		'testingcomp_id' => array('type' => 'integer', 'null' => false, 'default' => null),
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
			'topproject_id' => 1,
			'report_id' => 1,
			'testingmethod_id' => 1,
			'testingcomp_id' => 1
		),
	);

}
