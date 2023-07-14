<?php
App::uses('ReportPtGenerally', 'Model');

/**
 * ReportPtGenerally Test Case
 *
 */
class ReportPtGenerallyTest extends CakeTestCase {

/**
 * Fixtures
 *
 * @var array
 */
	public $fixtures = array(
		'app.report_pt_generally'
	);

/**
 * setUp method
 *
 * @return void
 */
	public function setUp() {
		parent::setUp();
		$this->ReportPtGenerally = ClassRegistry::init('ReportPtGenerally');
	}

/**
 * tearDown method
 *
 * @return void
 */
	public function tearDown() {
		unset($this->ReportPtGenerally);

		parent::tearDown();
	}

}
