<?php
App::uses('ReportPtSpecific', 'Model');

/**
 * ReportPtSpecific Test Case
 *
 */
class ReportPtSpecificTest extends CakeTestCase {

/**
 * Fixtures
 *
 * @var array
 */
	public $fixtures = array(
		'app.report_pt_specific'
	);

/**
 * setUp method
 *
 * @return void
 */
	public function setUp() {
		parent::setUp();
		$this->ReportPtSpecific = ClassRegistry::init('ReportPtSpecific');
	}

/**
 * tearDown method
 *
 * @return void
 */
	public function tearDown() {
		unset($this->ReportPtSpecific);

		parent::tearDown();
	}

}
