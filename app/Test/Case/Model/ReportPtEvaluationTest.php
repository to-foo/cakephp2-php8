<?php
App::uses('ReportPtEvaluation', 'Model');

/**
 * ReportPtEvaluation Test Case
 *
 */
class ReportPtEvaluationTest extends CakeTestCase {

/**
 * Fixtures
 *
 * @var array
 */
	public $fixtures = array(
		'app.report_pt_evaluation'
	);

/**
 * setUp method
 *
 * @return void
 */
	public function setUp() {
		parent::setUp();
		$this->ReportPtEvaluation = ClassRegistry::init('ReportPtEvaluation');
	}

/**
 * tearDown method
 *
 * @return void
 */
	public function tearDown() {
		unset($this->ReportPtEvaluation);

		parent::tearDown();
	}

}
