<?php
App::uses('DropdownsValue', 'Model');

/**
 * DropdownsValue Test Case
 *
 */
class DropdownsValueTest extends CakeTestCase {

/**
 * Fixtures
 *
 * @var array
 */
	public $fixtures = array(
		'app.dropdowns_value',
		'app.testingcomp',
		'app.roll',
		'app.user',
		'app.qualification',
		'app.testingmethod',
		'app.report',
		'app.reportlock',
		'app.topproject',
		'app.order',
		'app.deliverynumber',
		'app.equipment',
		'app.equipment_type',
		'app.reportnumber',
		'app.reportimage',
		'app.examiner_time',
		'app.examiner',
		'app.development',
		'app.orders_development',
		'app.development_data',
		'app.testingcomps_development',
		'app.testingcomps_topproject',
		'app.reports_topproject',
		'app.testingmethods_report',
		'app.dropdown',
		'app.testingcomps_dropdown',
		'app.dependency'
	);

/**
 * setUp method
 *
 * @return void
 */
	public function setUp() {
		parent::setUp();
		$this->DropdownsValue = ClassRegistry::init('DropdownsValue');
	}

/**
 * tearDown method
 *
 * @return void
 */
	public function tearDown() {
		unset($this->DropdownsValue);

		parent::tearDown();
	}

}
