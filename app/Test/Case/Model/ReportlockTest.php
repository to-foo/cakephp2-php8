<?php
App::uses('Reportlock', 'Model');

/**
 * Reportlock Test Case
 *
 */
class ReportlockTest extends CakeTestCase {

/**
 * Fixtures
 *
 * @var array
 */
	public $fixtures = array(
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
		'app.report',
		'app.reportnumber',
		'app.reportimage',
		'app.examiner_time',
		'app.examiner',
		'app.reports_topproject',
		'app.testingmethods_report',
		'app.testingcomps_topproject',
		'app.dropdown',
		'app.testingcomps_dropdown',
		'app.development',
		'app.orders_development',
		'app.development_data',
		'app.testingcomps_development'
	);

/**
 * setUp method
 *
 * @return void
 */
	public function setUp() {
		parent::setUp();
		$this->Reportlock = ClassRegistry::init('Reportlock');
	}

/**
 * tearDown method
 *
 * @return void
 */
	public function tearDown() {
		unset($this->Reportlock);

		parent::tearDown();
	}

}
