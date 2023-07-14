<?php
App::uses('AppModel', 'Model');

/**
 * StripDatum Model
 *
 * @property Topproject $Topproject
 * @property EquipmentType $EquipmentType
 * @property Equipment $Equipment
 * @property Order $Order
 * @property Report $Report
 * @property User $User
 * @property Testingcomp $Testingcomp
 * @property StripEvaluation $StripEvaluation
 */
class StripDatum extends AppModel {

	public function exists($id=null) {
		if($id == null) $id = $this->{$this->primaryKey};
		
		$cond = array($this->alias.'.'.$this->primaryKey=>$id);
		if(isset(Router::getRequest()->projectvars)) {
			$cond[$this->alias.'.topproject_id'] = Router::getRequest()->projectID;
			$cond[$this->alias.'.equipment_type_id'] = Router::getRequest()->equipmentType;
			$cond[$this->alias.'.equipment_id'] = Router::getRequest()->equipment;
			$cond[$this->alias.'.order_id'] = Router::getRequest()->orderID;
			$cond[$this->alias.'.report_id'] = Router::getRequest()->reportID;
		}
		
		return 0 < $this->find('count', array('conditions'=>$cond, 'limit'=>1, 'fields'=>array($this->alias.'.'.$this->primaryKey, $this->alias.'.'.$this->primaryKey)));
	}

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
		'User' => array(
			'className' => 'User',
			'foreignKey' => 'user_id',
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
		)
	);

/**
 * hasMany associations
 *
 * @var array
 */
	public $hasMany = array(
		'StripEvaluation' => array(
			'className' => 'StripEvaluation',
			'foreignKey' => 'strip_datum_id',
			'dependent' => false,
			'conditions' => '',
			'fields' => '',
			'order' => '',
			'limit' => '',
			'offset' => '',
			'exclusive' => '',
			'finderQuery' => '',
			'counterQuery' => ''
		)
	);

}
