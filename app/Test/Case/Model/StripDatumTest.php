<?php
App::uses('StripDatum', 'Model');

/**
 * StripDatum Test Case
 *
 */
class StripDatumTest extends CakeTestCase {

/**
 * Fixtures
 *
 * @var array
 */
	public $fixtures = array(
		'app.strip_datum',
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
		'app.reportlock',
		'app.reportnumber',
		'app.reportimage',
		'app.examiner_time',
		'app.examiner',
		'app.certificate',
		'app.certificate_data',
		'app.eyecheck',
		'app.eyecheck_data',
		'app.reports_topproject',
		'app.testingmethods_report',
		'app.device',
		'app.device_certificate_data',
		'app.device_certificate',
		'app.testingmethods_device',
		'app.testingcomps_topproject',
		'app.dropdown',
		'app.testingcomps_dropdown',
		'app.development',
		'app.orders_development',
		'app.development_data',
		'app.testingcomps_development',
		'app.strip_evaluation'
	);

/**
 * setUp method
 *
 * @return void
 */
	public function setUp() {
		parent::setUp();
		$this->StripDatum = ClassRegistry::init('StripDatum');
	}

/**
 * tearDown method
 *
 * @return void
 */
	public function tearDown() {
		unset($this->StripDatum);

		parent::tearDown();
	}

}
