<?php
App::uses('AppModel', 'Model');
/**
 * ExaminerTime Model
 *
 * @property Reportnumber $Reportnumber
 * @property Examiner $Examiner
 */
class ExaminerTime extends AppModel {
/**
 * Validation rules
 *
 * @var array
 */
	public $validate = array(
		'reportnumber_id' => array(
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
		'testing_time_start' => array(
//			'notBlank' => array(
//				'rule' => array('notBlank'),
				//'message' => 'Your custom message here',
				//'allowEmpty' => false,
				//'required' => false,
				//'last' => false, // Stop validation after this rule
				//'on' => 'create', // Limit validation to 'create' or 'update' operations
//			),
		),
		'testing_time_end' => array(
//			'notBlank' => array(
//				'rule' => array('notBlank'),
				//'message' => 'Your custom message here',
				//'allowEmpty' => false,
				//'required' => false,
				//'last' => false, // Stop validation after this rule
				//'on' => 'create', // Limit validation to 'create' or 'update' operations
//			),
		),
	);

	//The Associations below have been created with all possible keys, those that are not needed can be removed

/**
 * belongsTo associations
 *
 * @var array
 */
	public $belongsTo = array(
		'Reportnumber' => array(
			'className' => 'Reportnumber',
			'foreignKey' => 'reportnumber_id',
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
		'Examiner' => array(
			'className' => 'Examiner',
			'foreignKey' => 'examiner_id',
			'conditions' => '',
			'fields' => '',
			'order' => ''
		)
	);

	public function beforeFind($queryData) {
		$_queryData = parent::beforeFind($queryData);
		if(is_array($_queryData)) $queryData = $_queryData;
		if(!isset($queryData['order']) || empty($queryData['order']) || (count($queryData['order']) == 1 && reset($queryData['order']) == null))
		{
			$queryData['order'] = array(
				'date('.$this->alias.'.testing_time_start) asc',
				'time('.$this->alias.'.testing_time_start) asc'
			);
		}
		return $queryData;
	}

	public function beforeSave($options = array()) {
		$save = parent::beforeSave($options);
		$this->recursive = 0;

		$force_save = false;
		if(isset($this->data[$this->alias]['force_save'])) {
			$force_save = $this->data[$this->alias]['force_save'];
			unset($this->data[$this->alias]['force_save']);
		}

		$errors = array();

		$this->recursive = 0;

		$options = array();
		if(isset($this->data[$this->alias][$this->primaryKey])) {
			$options = array($this->alias.'.'.$this->primaryKey.' !='=>$this->data[$this->alias][$this->primaryKey]);
		}
		$options['Reportnumber.delete']=0;

		// Test auf Kollisionen in der Wartezeit
		$options_waiting = array_merge($options, array(
			$this->alias.'.examiner_id' => $this->data[$this->alias]['examiner_id'],
			array(
				$this->alias.'.waiting_time_end >' => $this->data[$this->alias]['waiting_time_start'],
				$this->alias.'.waiting_time_start <' => $this->data[$this->alias]['waiting_time_end']
			)
		));
		$times = $this->find('all', array('conditions'=>$options_waiting));
		if(count($times) != 0) { $errors['waiting_time'] = $times; }

		// Test auf Kollisionen in der Prüfzeit
		$options_testing = array_merge($options, array(
			$this->alias.'.examiner_id' => $this->data[$this->alias]['examiner_id'],
			array(
				$this->alias.'.testing_time_end >' => $this->data[$this->alias]['testing_time_start'],
				$this->alias.'.testing_time_start <' => $this->data[$this->alias]['testing_time_end']
			)
		));

		$times = $this->find('all', array('conditions'=>$options_testing));
		if(count($times) != 0) { $errors['testing_time'] = $times; }

		$id = isset($this->data[$this->alias][$this->primaryKey]) ? $this->data[$this->alias][$this->primaryKey] : null;

		// Fehlende Start- oder Endzeit, falls eine Wartezeit angegeben ist
		if(empty($this->data[$this->alias]['waiting_time_start']) xor empty($this->data[$this->alias]['waiting_time_end'])) {
			$errors['waiting_time_missing'] = $id;
		}

		// Verkehrte Reihenfolge der Zeitangaben in Wartezeit
		if(strtotime($this->data[$this->alias]['waiting_time_end']) < strtotime($this->data[$this->alias]['waiting_time_start'])) {
			$errors['order_waiting_time'] = $id;
		}

		// Verkehrte Reihenfolge der Zeitangaben in Prüfzeit
		if(strtotime($this->data[$this->alias]['testing_time_end']) < strtotime($this->data[$this->alias]['testing_time_start'])) {
			$errors['order_testing_time'] = $id;
		}

		// Prüfzeit überschneidet Wartezeit oder liegt ganz davor
		if(!empty($this->data[$this->alias]['testing_time_start']) && strtotime($this->data[$this->alias]['testing_time_start']) < strtotime($this->data[$this->alias]['waiting_time_end'])) {
			$errors['order_waiting_time_testing_time'] = $id;
		}

		// registrierte Fehler zu den Daten schreiben
		$this->data[$this->alias]['collision'] = empty($errors) ? null : $errors;

		// Abbrechen, falls nicht nur eine Wartezeitkollision vorliegt
		if(!$force_save && count($errors)>0 && !(count($errors) == 1 && isset($errors['waiting_time']))) {
			return false;
		} else {
			if(!empty($errors))
			{
				if(isset($this->data[$this->alias]['collision'])) {
					if(isset($this->data[$this->alias]['collision']['waiting_time']) && is_array($this->data[$this->alias]['collision']['waiting_time'])) {
						$this->data[$this->alias]['collision']['waiting_time'] = array_map(function($elem) use($id) {
							return array(
								'id'=>$id,
								'target'=>$elem['Reportnumber']['id'],
								'link'=>join('/', array(
									'controller'=>'reportnumbers',
									'action'=>'view',
									$elem['Reportnumber']['topproject_id'],
									$elem['Reportnumber']['equipment_type_id'],
									$elem['Reportnumber']['equipment_id'],
									$elem['Reportnumber']['order_id'],
									$elem['Reportnumber']['report_id'],
									$elem['Reportnumber']['id']
								)),
								'report'=>$elem['Reportnumber']['number'],
								'workload'=>$elem['ExaminerTime']['id'],
								'start'=>$elem['ExaminerTime']['waiting_time_start'],
								'end'=>$elem['ExaminerTime']['waiting_time_end']
							);
						}, $this->data[$this->alias]['collision']['waiting_time']);
					}
					if(isset($this->data[$this->alias]['collision']['testing_time']) && is_array($this->data[$this->alias]['collision']['testing_time'])) {
						$this->data[$this->alias]['collision']['testing_time'] = array_map(function($elem) use($id) {
							return array(
								'id'=>$id,
								'target'=>$elem['Reportnumber']['id'],
								'link'=>join('/', array(
									'controller'=>'reportnumbers',
									'action'=>'view',
									$elem['Reportnumber']['topproject_id'],
									$elem['Reportnumber']['equipment_type_id'],
									$elem['Reportnumber']['equipment_id'],
									$elem['Reportnumber']['order_id'],
									$elem['Reportnumber']['report_id'],
									$elem['Reportnumber']['id']
								)),
								'report'=>$elem['Reportnumber']['number'],
								'workload'=>$elem['ExaminerTime']['id'],
								'start'=>$elem['ExaminerTime']['testing_time_start'],
								'end'=>$elem['ExaminerTime']['testing_time_end']
							);
						}, $this->data[$this->alias]['collision']['testing_time']);
					}
					$this->data[$this->alias]['collision'] = json_encode($this->data[$this->alias]['collision']);
				}
			}
			return $save;
		}
	}
}