<?php
App::uses('ReportPtArchiv', 'Model');

/**
 * ReportPtArchiv Test Case
 *
 */
class ReportPtArchivTest extends CakeTestCase {

/**
 * Fixtures
 *
 * @var array
 */
	public $fixtures = array(
		'app.report_pt_archiv'
	);

/**
 * setUp method
 *
 * @return void
 */
	public function setUp() {
		parent::setUp();
		$this->ReportPtArchiv = ClassRegistry::init('ReportPtArchiv');
	}

/**
 * tearDown method
 *
 * @return void
 */
	public function tearDown() {
		unset($this->ReportPtArchiv);

		parent::tearDown();
	}

}
