<?php
App::uses('AppModel', 'Model');

class Monitoring extends AppModel {

	public $useTable = 'monitorings';

	function beforeValidate($options = array()) {

	  return true;

	}
}
