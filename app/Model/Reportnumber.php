<?php
App::uses('AppModel', 'Model');
/**
 * Reportnumber Model
 *
 * @property ExaminerTime $ExaminerTime
 * @property FilmUsage $FilmUsage
 * @property Topproject $Topproject
 * @property Order $Order
 * @property Report $Report
 * @property Testingmethod $Testingmethod
 * @property Testingcomp $Testingcomp
 * @property User $User
 * @property EquipmentType $EquipmentType
 * @property Equipment $Equipment
 * @property HiddenField $HiddenField
 * @property Reportfile $Reportfile
 * @property Reportimage $Reportimage
 */
class Reportnumber extends AppModel {

/**
 * Validation rules
 *
 * @var array
 */
	public $validate = array(
		'number' => array(
			'numeric' => array(
				'rule' => array('numeric'),
				//'message' => 'Your custom message here',
				//'allowEmpty' => false,
				//'required' => false,
				//'last' => false, // Stop validation after this rule
				//'on' => 'create', // Limit validation to 'create' or 'update' operations
			),
		),
		'year' => array(
			'numeric' => array(
				'rule' => array('numeric'),
				//'message' => 'Your custom message here',
				//'allowEmpty' => false,
				//'required' => false,
				//'last' => false, // Stop validation after this rule
				//'on' => 'create', // Limit validation to 'create' or 'update' operations
			),
		),
		'topproject_id' => array(
			'numeric' => array(
				'rule' => array('numeric'),
				//'message' => 'Your custom message here',
				//'allowEmpty' => false,
				//'required' => false,
				//'last' => false, // Stop validation after this rule
				//'on' => 'create', // Limit validation to 'create' or 'update' operations
			),
		),
		'order_id' => array(
			'numeric' => array(
				'rule' => array('numeric'),
				//'message' => 'Your custom message here',
				//'allowEmpty' => false,
				//'required' => false,
				//'last' => false, // Stop validation after this rule
				//'on' => 'create', // Limit validation to 'create' or 'update' operations
			),
		),
		'report_id' => array(
			'numeric' => array(
				'rule' => array('numeric'),
				//'message' => 'Your custom message here',
				//'allowEmpty' => false,
				//'required' => false,
				//'last' => false, // Stop validation after this rule
				//'on' => 'create', // Limit validation to 'create' or 'update' operations
			),
		),
		'testingmethod_id' => array(
			'numeric' => array(
				'rule' => array('numeric'),
				//'message' => 'Your custom message here',
				//'allowEmpty' => false,
				//'required' => false,
				//'last' => false, // Stop validation after this rule
				//'on' => 'create', // Limit validation to 'create' or 'update' operations
			),
		),
		'testingcomp_id' => array(
			'numeric' => array(
				'rule' => array('numeric'),
				//'message' => 'Your custom message here',
				//'allowEmpty' => false,
				//'required' => false,
				//'last' => false, // Stop validation after this rule
				//'on' => 'create', // Limit validation to 'create' or 'update' operations
			),
		),
		'user_id' => array(
			'numeric' => array(
				'rule' => array('numeric'),
				//'message' => 'Your custom message here',
				//'allowEmpty' => false,
				//'required' => false,
				//'last' => false, // Stop validation after this rule
				//'on' => 'create', // Limit validation to 'create' or 'update' operations
			),
		),
		'modified_user_id' => array(
			'numeric' => array(
				'rule' => array('numeric'),
				//'message' => 'Your custom message here',
				//'allowEmpty' => false,
				//'required' => false,
				//'last' => false, // Stop validation after this rule
				//'on' => 'create', // Limit validation to 'create' or 'update' operations
			),
		),
		'equipment_type_id' => array(
			'numeric' => array(
				'rule' => array('numeric'),
				//'message' => 'Your custom message here',
				//'allowEmpty' => false,
				//'required' => false,
				//'last' => false, // Stop validation after this rule
				//'on' => 'create', // Limit validation to 'create' or 'update' operations
			),
		),
		'equipment_id' => array(
			'numeric' => array(
				'rule' => array('numeric'),
				//'message' => 'Your custom message here',
				//'allowEmpty' => false,
				//'required' => false,
				//'last' => false, // Stop validation after this rule
				//'on' => 'create', // Limit validation to 'create' or 'update' operations
			),
		),
		'parent_id' => array(
			'numeric' => array(
				'rule' => array('numeric'),
				//'message' => 'Your custom message here',
				//'allowEmpty' => false,
				//'required' => false,
				//'last' => false, // Stop validation after this rule
				//'on' => 'create', // Limit validation to 'create' or 'update' operations
			),
		),
		'repair_for' => array(
			'numeric' => array(
				'rule' => array('numeric'),
				//'message' => 'Your custom message here',
				//'allowEmpty' => false,
				//'required' => false,
				//'last' => false, // Stop validation after this rule
				//'on' => 'create', // Limit validation to 'create' or 'update' operations
			),
		),
	);

	//The Associations below have been created with all possible keys, those that are not needed can be removed

/**
 * belongsTo associations
 *
 * @var array
 */
	public $belongsTo = array(
		'Topproject' => array(
			'className' => 'Topproject',
			'foreignKey' => 'topproject_id',
			'conditions' => '',
			'fields' => '',
			'order' => ''
		),
		'Order' => array(
			'className' => 'Order',
			'foreignKey' => 'order_id',
			'conditions' => '',
			'fields' => '',
			'order' => ''
		),
		'Report' => array(
			'className' => 'Report',
			'foreignKey' => 'report_id',
			'conditions' => '',
			'fields' => '',
			'order' => ''
		),
		'Testingmethod' => array(
			'className' => 'Testingmethod',
			'foreignKey' => 'testingmethod_id',
			'conditions' => '',
			'fields' => '',
			'order' => ''
		),
		'Testingcomp' => array(
			'className' => 'Testingcomp',
			'foreignKey' => 'testingcomp_id',
			'conditions' => '',
			'fields' => '',
			'order' => ''
		),
		'User' => array(
			'className' => 'User',
			'foreignKey' => 'user_id',
			'conditions' => '',
			'fields' => '',
			'order' => ''
		),
		'EquipmentType' => array(
			'className' => 'EquipmentType',
			'foreignKey' => 'equipment_type_id',
			'conditions' => '',
			'fields' => '',
			'order' => ''
		),
		'Equipment' => array(
			'className' => 'Equipment',
			'foreignKey' => 'equipment_id',
			'conditions' => '',
			'fields' => '',
			'order' => ''
		)
	);

/**
 * hasMany associations
 *
 * @var array
 */
	public $hasMany = array(
		'HiddenField' => array(
			'className' => 'HiddenField',
			'foreignKey' => 'reportnumber_id',
			'dependent' => false,
			'conditions' => '',
			'fields' => '',
			'order' => '',
			'limit' => '',
			'offset' => '',
			'exclusive' => '',
			'finderQuery' => '',
			'counterQuery' => ''
		),
		'Reportfile' => array(
			'className' => 'Reportfile',
			'foreignKey' => 'reportnumber_id',
			'dependent' => false,
			'conditions' => '',
			'fields' => '',
			'order' => '',
			'limit' => '',
			'offset' => '',
			'exclusive' => '',
			'finderQuery' => '',
			'counterQuery' => ''
		),
		'Reportimage' => array(
			'className' => 'Reportimage',
			'foreignKey' => 'reportnumber_id',
			'dependent' => false,
			'conditions' => '',
			'fields' => '',
			'order' => '',
			'limit' => '',
			'offset' => '',
			'exclusive' => '',
			'finderQuery' => '',
			'counterQuery' => ''
		),
		'ExaminerTime' => array(
			'className' => 'ExaminerTime',
			'foreignKey' => 'reportnumber_id',
			'conditions' => '',
			'fields' => '',
			'order' => ''
		),
		'FilmUsage' => array(
			'className' => 'FilmUsage',
			'foreignKey' => 'reportnumber_id',
			'conditions' => '',
			'fields' => '',
			'order' => ''
		),
		'Revision' => array(
			'className' => 'Revision',
			'foreignKey' => 'reportnumber_id',
			'conditions' => '',
			'fields' => '',
			'order' => ''
		)
	);

	public function showReportVerificationByTime() {

	}

	public function getMailAdresses($xmlData, $reportnumber) {

		$Output = array();

		$verfahren = $reportnumber['Testingmethod']['value'];

		$Verfahren = ucfirst($verfahren);
		$WithList = array('Generally','Specific','Evaluation');

		$settings = $xmlData['settings'];

		foreach($settings->children() as $key => $setting) {

			$Continue = 0;

			foreach ($WithList as $_key => $_value) {

				$KeyTest = strstr($key, $_value, true);

				if($KeyTest !== false){
					$Continue = 1;
					break;
				}
			}

			if($Continue == 0) continue;
			foreach ($setting as $_key => $_value) {

				if(empty($_value->validate)) continue;
				if(empty($_value->validate->email)) continue;

				if(!isset($reportnumber[trim($key)][trim($_key)])) continue;
				if(empty($reportnumber[trim($key)][trim($_key)])) continue;

				$EmailAdresses = preg_split('/\n|\r\n?/', $reportnumber[trim($key)][trim($_key)]);

				if(count($EmailAdresses) == 0) continue;

				foreach ($EmailAdresses as $__key => $__value) {

					$EmailValidation = Validation::email(trim($__value));

					if($EmailValidation === true){
						$Output[] = trim($__value);
					}
				}
			}
		}

			if(isset($reportnumber['Testingcomp']['report_email']) && !empty($reportnumber['Testingcomp']['report_email'])) {
			$Output[] = $reportnumber['Testingcomp']['report_email'];
		}
		if(isset($reportnumber['Topproject']['email']) && !empty($reportnumber['Topproject']['email'])) {
			$Output[] = $reportnumber['Topproject']['email'];
		}
		$Output = array_unique($Output);

		return $Output;
	}

	// Funktion zur Prüfung von Validationregeln aus der XML
	public function getValidationErrors($xmlData, $reportnumber ,$verfahren=null) {
		if($verfahren == null) $verfahren = $reportnumber['Testingmethod']['value'];

		$Verfahren = ucfirst($verfahren);
		$ReportEvaluation = 'Report' . $Verfahren . 'Evaluation';

		// Settings filtern, welche einen validate-Kindknoten haben
		$settings = $xmlData['settings']->xpath('/settings/*/*[validate]');

		$evaluation_fields = array();
		$errors = array();
		$errors_alternative = array();
		$ModelArray = array();

		foreach($settings as $setting) {

			/* mögliche Regeln durchgehen und Feldnamen sowie Verstöße merken
			 *
			 * Beispiel:
			 * 	$errors[ReportRtGenerally0auftraggeber][0] => 'notBlank'
			 * 	$errors[ReportRtGenerally0auftraggeber][1] => 'numeric'
			 *
			 */

			if(!isset($setting->output->screen) || trim($setting->output->screen) == '') continue;

			foreach($setting->validate->children() as $key => $rule) {

				$key = strtolower($key);

				$ModelArray = explode('_',Inflector::underscore(trim($setting->model)));
				if(count($ModelArray) != 3) continue;
				$Model = ucfirst($ModelArray[2]);
				$field = trim($setting->key);

				if($field == 'fake') continue;

				switch($key) {

					case 'notempty':

					if(
						isset($reportnumber[trim($setting->model)][trim($setting->key)]) &&
						(trim($reportnumber[trim($setting->model)][trim($setting->key)]) === '' || $reportnumber[trim($setting->model)][trim($setting->key)] === null)) {

							$errors_alternative[$Model][$field][] = array(
								'reportnumber'=> array($reportnumber['Reportnumber']['topproject_id'],$reportnumber['Reportnumber']['cascade_id'],$reportnumber['Reportnumber']['order_id'],$reportnumber['Reportnumber']['report_id'],$reportnumber['Reportnumber']['id']),
								'model'=>$Model,
								'field'=>$field,
								'message'=> __('This field must not be empty'),
								'description' => $setting->discription
							);
							$errors[trim($setting->model).'0'.trim($setting->key)][] = array('field'=>Inflector::camelize(trim($setting->model).'0'.ucfirst(trim($setting->key))), 'message'=>$key);
						}
						if(
						!isset($reportnumber[trim($setting->model)][trim($setting->key)]) && strpos(trim($setting->model),'Evaluation') == false)
							{
								$errors_alternative[$Model][$field][] = array(
									'reportnumber'=> array($reportnumber['Reportnumber']['topproject_id'],$reportnumber['Reportnumber']['cascade_id'],$reportnumber['Reportnumber']['order_id'],$reportnumber['Reportnumber']['report_id'],$reportnumber['Reportnumber']['id']),
									'model'=>$Model,
									'field'=>$field,
									'message'=> __('This field must not be empty'),
									'description' => $setting->discription
								);
								$errors[trim($setting->model).'0'.trim($setting->key)][] = array('field'=>Inflector::camelize(trim($setting->model).'0'.ucfirst(trim($setting->key))), 'message'=>$key);

							}

						if(
							isset($reportnumber[trim($setting->model)]) &&
							strpos(trim($setting->model),'Evaluation') !== false &&
							key($reportnumber[trim($setting->model)]) == 0
						){
							$evaluation_fields[trim($setting->key)] = $key;
						}
					break;

					case 'c39':
					if(isset($reportnumber[trim($setting->model)][trim($setting->key)])) {
					//	var_dump($reportnumber[trim($setting->model)][trim($setting->key)]);

						if (!preg_match("#^[a-zA-Z0-9 \-%&./+]+$#", $reportnumber[trim($setting->model)][trim($setting->key)])) {
							$errors_alternative[$Model][$field][] = array(
								'reportnumber'=> array($reportnumber['Reportnumber']['topproject_id'],$reportnumber['Reportnumber']['cascade_id'],$reportnumber['Reportnumber']['order_id'],$reportnumber['Reportnumber']['report_id'],$reportnumber['Reportnumber']['id']),
								'model'=>$Model,
								'field'=>$field,
								'message'=>__('Gemäß des Code 39 Formates sind nur folgende Zeichen erlaubt: a-z, A-Z, 0-9 sowie die Sonderzeichen - $ % & / +'),
								'description' => $setting->discription
							);
						}

					}
					break;
					case 'email':
						if(isset($reportnumber[trim($setting->model)][trim($setting->key)])  && !empty($reportnumber[trim($setting->model)][trim($setting->key)])){

							// NOTLÖSUNG der Strich muss entfernt werden
							// bei der Eingab der abhängigen Felder muss auf gültige Email-Adresse geprüft werden
						//	$reportnumber[trim($setting->model)][trim($setting->key)] = str_replace('-','',$reportnumber[trim($setting->model)][trim($setting->key)]);

								$expemail = preg_split('/\n|\r\n?/', $reportnumber[trim($setting->model)][trim($setting->key)]);
//	          	$expemail = explode(PHP_EOL, $reportnumber[trim($setting->model)][trim($setting->key)]);

							foreach ($expemail as $expkey => $expval) {

								if($expval == '-') continue;
								$expvalarray = explode(' ',trim($expval));

								if(count($expvalarray) == 0) continue;

								$email_error_test = 0;

								foreach ($expvalarray as $_key => $_value) {

									$EmailValidation = Validation::email($_value);

									if($EmailValidation != 1) continue;
									if($EmailValidation == 1) $email_error_test++;

									unset($EmailValidation);

								}

								if ($email_error_test == 0)  {

									$errors_alternative[$Model][$field][] = array(
										'reportnumber'=> array($reportnumber['Reportnumber']['topproject_id'],$reportnumber['Reportnumber']['cascade_id'],$reportnumber['Reportnumber']['order_id'],$reportnumber['Reportnumber']['report_id'],$reportnumber['Reportnumber']['id']),
										'model'=>$Model,
										'field'=>$field,
										'message'=>__('This field must contain a valid email').': '.$expval,
										'description' => $setting->discription
									);

									$errors[trim($setting->model).'0'.trim($setting->key)][] = array('field'=>Inflector::camelize(trim($setting->model).'0'.ucfirst(trim($setting->key))), 'message'=>$key);
								}
							}
						}
					}
				}
			}

		$ModelArray = array();
		$ModelArray = explode('_',Inflector::underscore(trim($ReportEvaluation)));
		$Model = ucfirst($ModelArray[2]);

		if(isset($reportnumber[$ReportEvaluation]) && is_array($reportnumber[$ReportEvaluation]) && count($reportnumber[$ReportEvaluation]) > 0){

			foreach($reportnumber[$ReportEvaluation] as $_key => $_evaluation){
				if(count($ModelArray) != 3) continue;

				foreach($evaluation_fields as $__fields => $__key){

					$field = $__fields;
					switch($__key) {
						case 'notempty':
							if($_evaluation[$ReportEvaluation][$__fields] === '' || $_evaluation[$ReportEvaluation][$__fields] === null){
								$description = $_evaluation[$ReportEvaluation]['description'];

								if(isset($_evaluation[$ReportEvaluation]['position'])) $description .= '/'.$_evaluation[$ReportEvaluation]['position'];
								$errors_alternative[$Model][$field][] = array(
													'reportnumber'=> array($reportnumber['Reportnumber']['topproject_id'],$reportnumber['Reportnumber']['cascade_id'],$reportnumber['Reportnumber']['order_id'],$reportnumber['Reportnumber']['report_id'],$reportnumber['Reportnumber']['id'],$_evaluation[$ReportEvaluation]['id']),
													'model'=> $ReportEvaluation,
													'field'=> $__fields,
													'description' => $xmlData['settings']->$ReportEvaluation->$field->discription,
													'position' =>$description,
													'message'=> __('This field must not be empty'),
													);
								$errors[$ReportEvaluation.'0'.$__fields][] = array('id'=>$_evaluation[$ReportEvaluation]['id'], 'field'=>Inflector::camelize($ReportEvaluation.'0'.ucfirst($__fields)), 'message'=>$__key, 'description' =>$description);
							}
							break;
					}
				}
			}
		}

		$errors = $errors_alternative;
		return $errors;
	}

public function autoClose() {

	/*	if(Configure::read('CloseMethode') != 'showReportVerificationByTime') return;

		$distance = Configure::read('CloseMethodeTime');
		if($distance === null) return;

		$this->recursive = -1;
		$this->updateAll(
				array($this->alias.'.status'=>2),
				array(
						$this->alias.'.delete'=>0,
						$this->alias.'.print >'=>0,
						$this->alias.'.print <='=>time()-intval($distance)
				)
			);*/
	}

	public function setClosureTime($id=0) {
		if(!$this->exists($id)) return;
		$this->recursive = -1;
		$this->updateAll(
				array($this->alias.'.'.'print'=>time()),
				array(
						$this->alias.'.'.$this->primaryKey=>$id,
						$this->alias.'.print'=>array(0,null)
				)
				);

	}
}
