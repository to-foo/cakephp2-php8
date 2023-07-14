<?php
class SelectValueComponent extends Component {

	protected $_controller = null;

	public function initialize(Controller $controller) {
		$this->_controller = $controller;
	}

	public function SelectData($value,$name,$conditions) {
		
		if(!is_array($conditions)){
		if($conditions == 'conditionsTestingcomps' || $conditions == 'conditionsTopprojects') {
			$conditions = array(
				'order' => array($name),
				'conditions' => array(
					$value.'.id' => $this->_controller->Session->read($conditions)
					)
				);
		}

		if($conditions == 'Topproject') {
			$_conditions = array();
			$hasAndBelongsToManyTable = $value.'s'.$conditions.'s';
			$this->_controller->loadModel($value.'s'.$conditions.'s');

			foreach($this->_controller->$hasAndBelongsToManyTable->find('all') as $_Value) {
				foreach($this->_controller->Session->read('conditions'.$conditions.'s') as $_valueConditions) {
					if($_Value[$value.'s'.$conditions.'s'][strtolower($conditions).'_id'] == $_valueConditions) {
						$_conditions[] = $_Value[$value.'s'.$conditions.'s'][strtolower($value).'_id'];
					}
				}
			}

			$conditions = array(
				'order' => array($name),
				'conditions' => array(
					'id' => array_unique($_conditions)
					)
				);
		}
		}
		$_select = array();
		$this->_controller->loadModel($value);
		$_selectValue = $this->_controller->$value->find('all', $conditions);

		foreach($_selectValue as $_Value) {
			$_select[$_Value[$value]['id']]	= $_Value[$value][$name];	
		}

		return $_select;
	}
}