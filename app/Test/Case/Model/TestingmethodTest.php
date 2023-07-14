<?php
App::uses('Testingmethod', 'Model');

/**
 * Testingmethod Test Case
 *
 */
class TestingmethodTest extends CakeTestCase {

/**
 * Fixtures
 *
 * @var array
 */
	public $fixtures = array(
		'app.testingmethod',
		'app.qualification',
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
		$this->Testingmethod = ClassRegistry::init('Testingmethod');
	}

/**
 * tearDown method
 *
 * @return void
 */
	public function tearDown() {
		unset($this->Testingmethod);

		parent::tearDown();
	}

}
