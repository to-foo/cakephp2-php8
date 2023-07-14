<?php
App::uses('Reportnumber', 'Model');

/**
 * Reportnumber Test Case
 *
 */
class ReportnumberTest extends CakeTestCase {

/**
 * Fixtures
 *
 * @var array
 */
	public $fixtures = array(
		'app.reportnumber',
		'app.topproject',
		'app.testingcomp',
		'app.roll',
		'app.user',
		'app.qualification',
		'app.testingmethod',
		'app.report',
		'app.reports_topproject',
		'app.reports_testingmethod',
		'app.testingcomps_topproject',
		'app.report_pt_archiv',
		'app.report_pt_evaluation',
		'app.report_pt_generally',
		'app.report_pt_specific'
	);

/**
 * setUp method
 *
 * @return void
 */
	public function setUp() {
		parent::setUp();
		$this->Reportnumber = ClassRegistry::init('Reportnumber');
	}

/**
 * tearDown method
 *
 * @return void
 */
	public function tearDown() {
		unset($this->Reportnumber);

		parent::tearDown();
	}

}
