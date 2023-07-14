<?php
App::uses('Qualification', 'Model');

/**
 * Qualification Test Case
 *
 */
class QualificationTest extends CakeTestCase {

/**
 * Fixtures
 *
 * @var array
 */
	public $fixtures = array(
		'app.qualification',
		'app.testingmethod',
		'app.user',
		'app.roll',
		'app.testingcomp',
		'app.topproject',
		'app.testingcomps_topproject',
		'app.testingmethods_topproject'
	);

/**
 * setUp method
 *
 * @return void
 */
	public function setUp() {
		parent::setUp();
		$this->Qualification = ClassRegistry::init('Qualification');
	}

/**
 * tearDown method
 *
 * @return void
 */
	public function tearDown() {
		unset($this->Qualification);

		parent::tearDown();
	}

}
