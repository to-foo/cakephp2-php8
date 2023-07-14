<?php
App::uses('ReportPtSetting', 'Model');

/**
 * ReportPtSetting Test Case
 *
 */
class ReportPtSettingTest extends CakeTestCase {

/**
 * Fixtures
 *
 * @var array
 */
	public $fixtures = array(
		'app.report_pt_setting'
	);

/**
 * setUp method
 *
 * @return void
 */
	public function setUp() {
		parent::setUp();
		$this->ReportPtSetting = ClassRegistry::init('ReportPtSetting');
	}

/**
 * tearDown method
 *
 * @return void
 */
	public function tearDown() {
		unset($this->ReportPtSetting);

		parent::tearDown();
	}

}
