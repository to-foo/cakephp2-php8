<?php
App::uses('AppModel', 'Model');
App::uses('CakeTime', 'Utility');
/**
 * Qualification Model
 *
 * @property Testingmethod $Testingmethod
 * @property User $User
 * @property Testingcomp $Testingcomp
 */
class Order extends AppModel {

	public $actsAs = array('Containable');
	public $name = 'Order';

	public $hasAndBelongsToMany = array(

		'Development' => array(
			'className' => 'Development',
			'joinTable' => 'orders_developments',
			'foreignKey' => 'order_id',
			'associationForeignKey' => 'development_id',
			'unique' => 'keepExisting',
			'conditions' => '',
			'fields' => '',
			'order' => '',
			'limit' => '',
			'offset' => '',
			'finderQuery' => '',
			'deleteQuery' => '',
			'insertQuery' => ''
		),
		'Testingcomp' => array(
			'className' => 'Testingcomp',
			'joinTable' => 'orders_testingcomps',
			'foreignKey' => 'order_id',
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
		),
		'Cascade' => array(
			'className' => 'Cascade',
			'joinTable' => 'cascades_orders',
			'foreignKey' => 'order_id',
			'associationForeignKey' => 'cascade_id',
			'unique' => 'keepExisting',
			'conditions' => '',
			'fields' => '',
			'order' => '',
			'limit' => '',
			'offset' => '',
			'finderQuery' => '',
			'deleteQuery' => '',
			'insertQuery' => ''
		),
		'Emailaddress' => array(
			'className' => 'Emailaddress',
			'joinTable' => 'orders_emailadresses',
			'foreignKey' => 'order_id',
			'associationForeignKey' => 'emailadress_id',
			'unique' => 'keepExisting',
//			'unique' => false,
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

	public $belongsTo = array(
		'Topproject' => array(
			'className' => 'Topproject',
			'foreignKey' => 'topproject_id',
			'conditions' => '',
			'fields' => '',
			'order' => ''
		)
	);

	public $hasOne = 'Deliverynumber';

	public $hasMany = array(
		'Reportnumber' => array(
			'className' => 'Reportnumber',
			'foreignKey' => 'order_id',
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
		'Deliverynumber' => array(
			'className' => 'Deliverynumber',
			'foreignKey' => 'order_id',
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
			'foreignKey' => 'order_id',
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

	public $validate = array(
		'auftrags_nr' => array(
			'notBlank' => array(
				'rule' => array('notBlank'),
				//'message' => 'Your custom message here',
				//'allowEmpty' => false,
				//'required' => false,
				//'last' => false, // Stop validation after this rule
				//'on' => 'create', // Limit validation to 'create' or 'update' operations
			),
		),
	);

	public function beforeSave($options = array()) {

//					$options = $this->_DateFormateSave($options);

			    return $options;
	}


	public function afterFind($results, $primary = false) {

//			$results = $this->_DateFormateFind($results);

	    return $results;
	}
}
