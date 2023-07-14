<?php
App::uses('AppModel', 'Model');
/**
 * Welder Model
 *
 * @property Testingcomp $Testingcomp
 * @property WelderTime $WelderTime
 */
class Welder extends AppModel {
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
            'language' => array(
			'notBlank' => array(
				'rule' => array('notBlank'),
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
		'Testingcomp' => array(
			'className' => 'Testingcomp',
			'foreignKey' => 'testingcomp_id',
			'conditions' => '',
			'fields' => '',
			'order' => ''
		),
	);
	public $hasOne = array(
		'WelderActive' => array(
			'className' => 'WelderActive',

			'foreignKey' => 'welder_id',
			'conditions' => '',
			'fields' => '',
			'order' => ''
		),
	);
/**
 * hasMany associations
 *
 * @var array
 */
	public $hasMany = array(

		'WelderCertificate' => array(
			'className' => 'WelderCertificate',
			'foreignKey' => 'welder_id',

		),
		'WelderEyecheck' => array(
			'className' => 'WelderEyecheck',
			'foreignKey' => 'welder_id',
			'dependent' => true,
		),
		'WelderTime' => array(
			'className' => 'WelderTime',
			'foreignKey' => 'welder_id',
			'dependent' => true,
		),

		'WelderMonitoring' => array(
			'className' => 'WelderMonitoring',
			'foreignKey' => 'welder_id',
			'dependent' => true,
		),
		'WelderMonitoringData' => array(
			'className' => 'WelderMonitoringData',
			'foreignKey' => 'welder_id',
			'dependent' => true,
		),
	);

        var $virtualFields = array(
            'fullname' => "CONCAT(Welder.first_name, ' ', Welder.name)"
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
						$rec = $this->WelderTime->recursive;
						$this->WelderTime->recursive = -1;
						$workload = Set::combine($this->WelderTime->find('all', array(
							'fields' => array(
								'COUNT(WelderTime.id) as count',
								'DATE(WelderTime.testing_time_start) as date'
							),
							'conditions'=>array(
								'WelderTime.welder_id'=> @$v[$this->primaryKey]
							),
							'group'=>'DATE(WelderTime.testing_time_start)'
						)), '{n}.0.date', '{n}.0.count');
						$this->WelderTime->recursive = $rec;
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
