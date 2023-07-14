<?php
App::uses('Testingcomp', 'Model');

/**
 * Testingcomp Test Case
 *
 */
class TestingcompTest extends CakeTestCase {

/**
 * Fixtures
 *
 * @var array
 */
	public $fixtures = array(
		'app.testingcomp',
		'app.roll',
		'app.qualification',
		'app.user',
		'app.topproject',
		'app.testingcomps_topproject',
		'app.testingmethod',
		'app.testingmethods_topproject'
	);

/**
 * setUp method
 *
 * @return void
 */
	public function setUp() {
		parent::setUp();
		$this->Testingcomp = ClassRegistry::init('Testingcomp');
	}

/**
 * tearDown method
 *
 * @return void
 */
	public function tearDown() {
		unset($this->Testingcomp);

		parent::tearDown();
	}

}
