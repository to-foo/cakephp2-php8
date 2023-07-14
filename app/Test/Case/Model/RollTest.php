<?php
App::uses('Roll', 'Model');

/**
 * Roll Test Case
 *
 */
class RollTest extends CakeTestCase {

/**
 * Fixtures
 *
 * @var array
 */
	public $fixtures = array(
		'app.roll',
		'app.testingcomp',
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
		$this->Roll = ClassRegistry::init('Roll');
	}

/**
 * tearDown method
 *
 * @return void
 */
	public function tearDown() {
		unset($this->Roll);

		parent::tearDown();
	}

}
