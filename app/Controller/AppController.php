<?php
/**
 * Application level Controller
 *
 * This file is application-wide controller file. You can put all
 * application-wide controller-related methods here.
 *
 * PHP 5
 *
 * CakePHP(tm) : Rapid Development Framework (http://cakephp.org)
 * Copyright (c) Cake Software Foundation, Inc. (http://cakefoundation.org)
 *
 * Licensed under The MIT License
 * For full copyright and license information, please see the LICENSE.txt
 * Redistributions of files must retain the above copyright notice.
 *
 * @copyright     Copyright (c) Cake Software Foundation, Inc. (http://cakefoundation.org)
 * @link          http://cakephp.org CakePHP(tm) Project
 * @package       app.Controller
 * @since         CakePHP(tm) v 0.2.9
 * @license       http://www.opensource.org/licenses/mit-license.php MIT License
 */
App::uses('Controller', 'Controller');

/**
 * Application Controller
 *
 * Add your application-wide methods in the class below, your controllers
 * will inherit them.
 *
 * @package		app.Controller
 * @link		http://book.cakephp.org/2.0/en/controllers.html#the-app-controller
 */
class AppController extends Controller {
//	public $components = array('Navigation', 'Session');
//	public $components = array('DebugKit.Toolbar','Navigation', 'Session');

	public $components = array(
			'Navigation',
			'Session',
			'Auth' => array(
				'authenticate' => array(
					'JwtAuth.JwtToken' => array(
						'fields' => array(
							'username' => 'username',
							'password' => 'password',
							'token' => 'public_key',
						),
					'parameter' => '_token',
					'userModel' => 'User',
					'scope' => array(
						'User.active' => 1
						),
					'pepper' => 'sneezing',
					),
				),
			),
			'Session','RequestHandler','Email','Flash'
	);

	// getter ist nötig, da für globale Variablendeklarationen keine Funktionen wie __() ausgeführt werden dürfen
	public function __get($name) {
		switch($name) {
			case 'radiodefault':
				return array(__('yes', true), __('no', true));

			default:
				return parent::__get($name);
		}
	}

	public function beforeRender() {

		$this->set('paginateOptions', array());

		if($this->layout == 'modal') {
			$this->set('paginateOptions', array('class'=>'mymodal'));
		} else {
		//	$this->__handleHistory();
		}

	}

	public function afterRender() {
		$this->Navigation->lastURL();
	}

	public function modelExists($modelName){
      $models = App::objects('model');
      return in_array($modelName,$models);
   }

	/*
	 Generiert aus einem Array eine Insert Query
	*/
	protected function generateSQLInsertQuery($obj, $is_save_many, $can_extend_update_file = false,$model = null){
			 if (!function_exists('add_quotes')){
				 function add_quotes($str) {
					 return sprintf("`%s`", $str);
				 }
			 }

			 $spacer = ' ';
			 $quote = '`';
		 	 $reg = '/\d+/';

			 if(isset($obj)){
				 foreach($obj as $key=>$value) {
					 $current_model_name = $model;
					 $this->loadModel($current_model_name);
					 $current_table_name = $this->{$current_model_name}->useTable;

					 $has_field_created = !empty($this->{$current_model_name}->schema('created'));
					 $has_field_modified = !empty($this->{$current_model_name}->schema('modified'));

					 $fields = $value;
					 $sql_fields = implode(',', array_map('add_quotes', array_keys($fields)));

					 if($has_field_created) {
						 if(!empty($sql_fields)){
							 $sql_fields .= ',' . $quote. 'created' .$quote;
						 } else {
							 $sql_fields .= $quote. 'created' .$quote;
						 }
					 }

					 if($has_field_modified) {
						 if(!empty($sql_fields)){
							 $sql_fields .= ',' . $quote. 'modified' .$quote;
						 } else {
							 $sql_fields .= $quote. 'modified' .$quote;
						 }
					 }

					 $sql_insert_into = 'insert into';
					 $sql_insert_into .= $spacer . $quote . $current_table_name . $quote;
					 $sql_insert_into .= '(' . $sql_fields . ')';

					 $sql_insert_into .= $spacer . 'values(';

					 $fieldCount = 0;
					 foreach($fields as $field_key=>$field_value){
						 $current_field_key = $field_key;
						 $current_field_value = $field_value;
						 if(!$fieldCount > 0){
							 $sql_insert_into .=  '\'' . $field_value . '\'';
						 } else {
							 $sql_insert_into .=  ',\'' . $field_value . '\'';
						 }
						 $fieldCount++;
					 }

					 $insert_date = new DateTime();

					 if($has_field_created) {
						 $sql_insert_into .=  ',' . '\'' . $insert_date->format('Y-m-d\TH:i:s') . '\'';
					 }

					 if($has_field_modified) {
							 $sql_insert_into .=  ',' . '\'' . $insert_date->format('Y-m-d\TH:i:s') . '\'';
					 }

					 $sql_insert_into .= ');';

					 if($can_extend_update_file){
						 $update_folder = APP . 'update' . DS . 'sql' . DS;
						 $update_file = $update_folder . $current_table_name . '.sql';
						 file_put_contents($update_file, $sql_insert_into . "\n", FILE_APPEND);
					 }
				}
				return $sql_insert_into;
			}
	}

	private function __handleHistory() {
		if(isset($this->request->data['this_id'])) return 0;
		if(isset($this->request->data['ReportRtEvaluation']))  return 0;
		if(isset($this->request->data['ajax_true']))  return 0;
		if(isset($this->request->data['json_true'])) {
			if($this->request->data['json_true'] == 1) return 0;
		}

		if($this->request->params['action'] != 'pdf') {
				if(isset($_SERVER['HTTP_REFERER'])) {
					$referer = $_SERVER['HTTP_REFERER'];
				} else {
					$referer = '';
				}

				if(isset($_SERVER['REQUEST_URI'])) {
					$request_uri = $_SERVER['REQUEST_URI'];
				} else {
					$request_uri = $_SERVER['REQUEST_URI'];
				}
				if(!empty($referer)){
					echo '<script>window.history.pushState( {}, "' . $referer . '", "");</script>';
				}
		}
	}
}
