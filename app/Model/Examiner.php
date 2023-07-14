<?php
App::uses('AppModel', 'Model');
/**
 * Examiner Model
 *
 * @property Testingcomp $Testingcomp
 * @property ExaminerTime $ExaminerTime
 */
class Examiner extends AppModel {

	public $useTable = 'examiners';
	public $actsAs = array('Containable');

/**
 * Display field
 *
 * @var string
 */
	public $displayField = 'name';

/**
 * Validation rules
 *
 * @var array
 */
	public $validate = array(
		'first_name' => array(
			'notBlank' => array(
				'rule' => array('notBlank'),
			),
		),
		'name' => array(
			'notBlank' => array(
				'rule' => array('notBlank'),
			),
		),
		'da_no' => array(
			'notBlank' => array(
				'rule' => array('notBlank'),
			),
		),
		'date_of_birth' => array(
			'notBlank' => array(
				'rule' => array('notBlank'),
			),
		),
		'plz_place_of_birth' => array(
			'notBlank' => array(
				'rule' => array('notBlank'),
			),
		),
		'place_of_birth' => array(
			'notBlank' => array(
				'rule' => array('notBlank'),
			),
		),
		'plz' => array(
			'notBlank' => array(
				'rule' => array('notBlank'),
			),
		),
		'place' => array(
			'notBlank' => array(
				'rule' => array('notBlank'),
			),
		),
		'street' => array(
			'notBlank' => array(
				'rule' => array('notBlank'),
			),
		),
		'working_place' => array(
			'notBlank' => array(
				'rule' => array('notBlank'),
			),
		),
		'contact_person' => array(
			'notBlank' => array(
				'rule' => array('notBlank'),
			),
		),
		'job' => array(
			'notBlank' => array(
				'rule' => array('notBlank'),
			),
		),
	);

	public $hasOne = array(
		'ExaminersStamp' => array(
				'className' => 'ExaminersStamp',
				'foreignKey' => 'examiner_id',
				'dependent' => false
		)
);

	//The Associations below have been created with all possible keys, those that are not needed can be removed

/**
 * belongsTo associations
 *
 * @var array
 */
	public $belongsTo = array(
		'Testingcomp' => array(
			'className' => 'Testingcomp',
			'foreignKey' => 'testingcomp_id',
			'conditions' => '',
			'fields' => '',
			'order' => ''
		),
		'User' => array(
	    'className'  => 'User',
	    'foreignKey'  => 'user_id',
    )
	);

/**
 * hasMany associations
 *
 * @var array
 */
	public $hasMany = array(
		'Qualification' => array(
			'className' => 'Qualification',
			'foreignKey' => 'examiner_id',
			'dependent' => true,
		),
		'Certificate' => array(
			'className' => 'Certificate',
			'foreignKey' => 'examiner_id',
			'dependent' => true,
		),
		'Eyecheck' => array(
			'className' => 'Eyecheck',
			'foreignKey' => 'examiner_id',
			'dependent' => true,
		),
		'ExaminerTime' => array(
			'className' => 'ExaminerTime',
			'foreignKey' => 'examiner_id',
			'dependent' => true,
		),
		'Device' => array(
			'className' => 'Device',
			'foreignKey' => 'examiner_id',
			'dependent' => true,
		),
		'ExaminerMonitoring' => array(
			'className' => 'ExaminerMonitoring',
			'foreignKey' => 'examiner_id',
			'dependent' => true,
		),
		'ExaminerMonitoringData' => array(
			'className' => 'ExaminerMonitoringData',
			'foreignKey' => 'examiner_id',
			'dependent' => true,
		),
		'User' => array(
			'className' => 'User',
			'foreignKey' => 'examiner_id',
			'dependent' => false,
		)
	);

		public $hasAndBelongsToMany = array(
			'ExaminersTestingcomp' => array(
				'className' => 'ExaminersTestingcomp',
				'joinTable' => 'examiner_testingcomps',
				'foreignKey' => 'examiner_id',
				'associationForeignKey' => 'testingcomp_id',
				'unique' => 'keepExisting',
				'conditions' => '',
				'fields' => '',
				'order' => '',
				'limit' => '',
				'offset' => '',
				'finderQuery' => '',
				'deleteQuery' => '',
				'insertQuery' => ''
			)
		);




	public function beforeSave($options=array()) {
		if(!parent::beforeSave($options)) return false;

		$this->data = $this->removeDisplayName($this->data, array('display', 'workload_count'));

		return true;
	}

	public function afterFind($results=array(), $primary = false) {
		$results = parent::afterFind($results, $primary);

		foreach($results as $k=>$v) {

			if(is_array($v)) {
				if(!isset($v['name'])) $results[$k] = $this->afterFind($v, $primary);
				else
				{
					if(isset($v['f'])) {
						$results[$k]['display'] = str_replace(' ()', '', $v['name'].' ('.$v['f'].')');
					}

					if(!isset($results[$k]['workload']))
					{
						$rec = $this->ExaminerTime->recursive;
						$this->ExaminerTime->recursive = -1;
						$workload = Set::combine($this->ExaminerTime->find('all', array(
							'fields' => array(
								'COUNT(ExaminerTime.id) as count',
								'DATE(ExaminerTime.testing_time_start) as date'
							),
							'conditions'=>array(
								'ExaminerTime.examiner_id'=> @$v[$this->primaryKey]
							),
							'group'=>'DATE(ExaminerTime.testing_time_start)'
						)), '{n}.0.date', '{n}.0.count');
						$this->ExaminerTime->recursive = $rec;
						$workload = array_filter($workload);
						//$workload['total'] = array_reduce($workload, function($prev, $curr){return $prev+$curr;});
						//$results[$k]['workload'] = $workload;
						$results[$k]['workload'] = array_reduce($workload, function($prev, $curr){return $prev+$curr;});
					}
				}
			}
		}

		return $results;
	}

	protected function removeDisplayName($results, $remove=array('display')) {
		foreach($results as $k=>$v) {
			if(is_array($v)) {
				$results[$k] = $this->removeDisplayName($v);
			} else {
				if(array_search($k, $remove) !== false) unset($results[$k]);
			}
		}

		return $results;
	}
}
