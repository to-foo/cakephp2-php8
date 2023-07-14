<?php
App::uses('Topproject', 'Model');

/**
 * Topproject Test Case
 *
 */
class TopprojectTest extends CakeTestCase {

/**
 * Fixtures
 *
 * @var array
 */
	public $fixtures = array(
		'app.topproject',
		'app.testingcomp',
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
		$this->Topproject = ClassRegistry::init('Topproject');
	}

/**
 * tearDown method
 *
 * @return void
 */
	public function tearDown() {
		unset($this->Topproject);

		parent::tearDown();
	}

}
