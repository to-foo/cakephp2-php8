<?php
App::uses('Report', 'Model');

/**
 * Report Test Case
 *
 */
class ReportTest extends CakeTestCase {

/**
 * Fixtures
 *
 * @var array
 */
	public $fixtures = array(
		'app.report',
		'app.reportlock',
		'app.topproject',
		'app.order',
		'app.deliverynumber',
		'app.equipment',
		'app.equipment_type',
		'app.testingcomp',
		'app.roll',
		'app.user',
		'app.qualification',
		'app.testingmethod',
		'app.testingmethods_report',
		'app.examiner',
		'app.examiner_time',
		'app.reportnumber',
		'app.reportimage',
		'app.testingcomps_topproject',
		'app.dropdown',
		'app.testingcomps_dropdown',
		'app.development',
		'app.orders_development',
		'app.development_data',
		'app.testingcomps_development',
		'app.reports_topproject'
	);

/**
 * setUp method
 *
 * @return void
 */
	public function setUp() {
		parent::setUp();
		$this->Report = ClassRegistry::init('Report');
	}

/**
 * tearDown method
 *
 * @return void
 */
	public function tearDown() {
		unset($this->Report);

		parent::tearDown();
	}

}
