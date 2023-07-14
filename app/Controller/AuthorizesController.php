<?php
App::uses('AppController', 'Controller');
/**
 * Helps Controller
 *
 */
class AuthorizesController extends AppController {

	var $uses = false;
	
	public function index() {
		$this->layout = 'blank';
	}
	
	public function notauthorize() {
		$this->layout = 'modal';
	}
}
