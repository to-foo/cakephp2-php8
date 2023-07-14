<?php
App::uses('StripEvaluation', 'Model');

/**
 * StripEvaluation Test Case
 *
 */
class StripEvaluationTest extends CakeTestCase {

/**
 * Fixtures
 *
 * @var array
 */
	public $fixtures = array(
		'app.strip_evaluation',
		'app.strip_data'
	);

/**
 * setUp method
 *
 * @return void
 */
	public function setUp() {
		parent::setUp();
		$this->StripEvaluation = ClassRegistry::init('StripEvaluation');
	}

/**
 * tearDown method
 *
 * @return void
 */
	public function tearDown() {
		unset($this->StripEvaluation);

		parent::tearDown();
	}

}
