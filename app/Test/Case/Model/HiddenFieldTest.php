<?php
App::uses('HiddenField', 'Model');

/**
 * HiddenField Test Case
 *
 */
class HiddenFieldTest extends CakeTestCase {

/**
 * Fixtures
 *
 * @var array
 */
	public $fixtures = array(
		'app.hidden_field',
		'app.reportnumber',
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
		'app.reports_topproject',
		'app.testingmethods_report',
		'app.device',
		'app.device_certificate_data',
		'app.device_certificate',
		'app.testingmethods_device',
		'app.examiner',
		'app.certificate',
		'app.certificate_data',
		'app.eyecheck',
		'app.eyecheck_data',
		'app.examiner_time',
		'app.testingcomps_topproject',
		'app.dropdown',
		'app.testingcomps_dropdown',
		'app.development',
		'app.orders_development',
		'app.development_data',
		'app.testingcomps_development',
		'app.reportimage'
	);

/**
 * setUp method
 *
 * @return void
 */
	public function setUp() {
		parent::setUp();
		$this->HiddenField = ClassRegistry::init('HiddenField');
	}

/**
 * tearDown method
 *
 * @return void
 */
	public function tearDown() {
		unset($this->HiddenField);

		parent::tearDown();
	}

}
