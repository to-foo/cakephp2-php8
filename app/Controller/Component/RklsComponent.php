<?php
class RklsComponent extends Component {

	//
	//

	protected $_controller = null;

	public function initialize(Controller $controller) {
		$this->_controller = $controller;
	}

	public function AutoFastRklc($response) {

		if(!isset($this->_controller->request->data['landig_page_large'])) return ($response);

		unset($this->_controller->request->data['ajax_true']);
		unset($this->_controller->request->data['landig_page_large']);

		if(count($this->_controller->request->data) == 0) return ($response);

		$Model = Sanitize::clean(key($this->_controller->request->data));

		$field = Sanitize::clean(key($this->_controller->request->data[$Model]));

		$Field = Inflector::camelize($field);

		if(!isset($this->_controller->request->data[$Model][$field])) return ($response);

		$Value = Sanitize::clean($this->_controller->request->data[$Model][$field]);

		$options = array(
			'limit' => 10,
			'order' => array($field),
			'group' => array($field),
			'fields' => array('id',$field),
			'conditions' => array(
			$Model . '.' . $field . ' LIKE' => '%' . html_entity_decode($Value) . '%',
			)
		);

		$Searchings = $this->_controller->{$Model}->find('all',$options);

		if(count($Searchings) == 0) return ($response);

		foreach ($Searchings as $key => $value) {

			array_push($response, array(
				'key' => $value[$Model]['id'],
				'value' => $value[$Model][$field],
				'label' => $value[$Model][$field],
				'field' => 'Searching' .$Model . $Field
				)
			);

		}

		return ($response);

	}

	public function AutoFastAdditional($response) {

		if(!isset($this->_controller->request->data['landig_page_large'])) return ($response);

		unset($this->_controller->request->data['ajax_true']);
		unset($this->_controller->request->data['landig_page_large']);

		if(count($this->_controller->request->data) == 0) return ($response);

		$Model = Sanitize::clean(key($this->_controller->request->data));

		$field = Sanitize::clean(key($this->_controller->request->data[$Model]));

		$Field = Inflector::camelize($field);

		if(!isset($this->_controller->request->data[$Model][$field])) return ($response);

		$Value = Sanitize::clean($this->_controller->request->data[$Model][$field]);

		$options = array(
			'limit' => 10,
			'order' => array($field),
			'group' => array($field),
			'fields' => array('id',$field),
			'conditions' => array(
			$Model . '.' . $field . ' LIKE' => '%' . html_entity_decode($Value) . '%',
			)
		);

		$this->_controller->{$Model}->recursive = -1;

		$Searchings = $this->_controller->{$Model}->find('all',$options);

		if(count($Searchings) == 0) return ($response);

		foreach ($Searchings as $key => $value) {

			array_push($response, array(
				'key' => $value[$Model]['id'],
				'value' => $value[$Model][$field],
				'label' => $value[$Model][$field],
				'field' => 'Searching' .$Model . $Field
				)
			);

		}

		return ($response);

	}

	public function UpdateSearchResultRkls($response) {

		if(!isset($this->_controller->request->data['update_search_result'])) return ($response);

		if(!isset($this->_controller->request->data['Searching'])) return ($response);
		if(count($this->_controller->request->data['Searching']) == 0) return ($response);

		$this->_controller->RklNumber->recursive = -1;

		foreach ($this->_controller->request->data['Searching'] as $key => $value) {

			if(!is_array($value)) continue;
			if(count($value) == 0) continue;

			foreach ($value as $_key => $_value) {

				if($_value == 0) continue;

				$Searching = $this->_controller->RklNumber->find('first',array('conditions' => array('RklNumber.id' => $_value)));

				if(count($Searching) == 0){

					$response['count'] = 0;
					$response['reports'] = array();

				} else {

					$response['count'] = 1;
					$response['reports'] = $Searching;

				}
			}
		}

		$this->_controller->Session->write('AutoFastSearchRkls',$response);

		return ($response);

	}

	public function FlattenRequestData($response,$xml){

		$output = array();
		foreach($response as $key => $value){

			if(empty($xml['settings']->$key)){
				unset($response[$key]);
				continue;
			}

//			pr($value);

			if(count($value) == 0) continue;

			foreach($xml['settings']->$key->fields->children() as $_key => $_value){

				if(empty($_value->editform)) continue;

				$Model = trim($_value->model);
				$Key = trim($_value->key);
				$Option = trim($_value->option);

				if(empty($_value->editform)) unset($response[$Model]);

				foreach ($value as $__key => $__value) {
					$output[$key][$Option][$__value[$Key]] = $__value[$Option];
				}

			}
		}

		$output = $this->UniqidRequestData($output,$xml);

		$output['RklNumber'] = $response['RklNumber'];

		return $output;

	}

	public function UniqidRequestData($response,$xml){

		foreach($response as $key => $value){
			foreach ($value as $_key => $_value) {
				$response[$key][$_key] = array_unique($response[$key][$_key]);
				natsort($response[$key][$_key]);
			}
		}

		return $response;

	}

	public function ShowSearchResultRkls($response) {

		if(!isset($this->_controller->request->data['show_search_result'])) return ($response);
		$AutoFastSearchResponse = $this->_controller->Session->read('AutoFastSearchRkls');

		if(empty($AutoFastSearchResponse)) return $response;
		if(!is_array($AutoFastSearchResponse)) return $response;
		if(count($AutoFastSearchResponse) == 0) return $response;

		$response = $this->_controller->RklNumber->find('first',array('conditions' => array('RklNumber.id' => $AutoFastSearchResponse['reports']['RklNumber']['id'])));

		return $response;

	}

	public function ShowSearchResultRklsDupli($response) {

		if(!isset($this->_controller->request->data['show_search_result_dupli'])) return ($response);

		$response = $this->_controller->RklNumber->find('first',array('conditions' => array('RklNumber.description' => $this->_controller->request->data['Rkl']['class'])));

		return $response;

	}

	public function UpdateSearchResult($response) {

		if(!isset($this->_controller->request->data['update_search_result'])) return ($response);

		if(!isset($this->_controller->request->data['Searching'])) return ($response);
		if(count($this->_controller->request->data['Searching']) == 0) return ($response);

		foreach ($this->_controller->request->data['Searching'] as $key => $value) {

			if(!is_array($value)) continue;
			if(count($value) == 0) continue;

			foreach ($value as $_key => $_value) {

				if($_value == 0) continue;

				$Searching = $this->_controller->Rkl->find('first',array('conditions' => array('Rkl.id' => $_value)));

				if(count($Searching) == 0){

					$response['count'] = 0;
					$response['reports'] = array();

				} else {

					$response['count'] = 1;
					$response['reports'] = $Searching;

				}
			}
		}

		$this->_controller->Session->write('AutoFastSearchRkls',$response);

		return ($response);

	}

	public function ShowSearchResult($response) {

		if(!isset($this->_controller->request->data['show_search_result'])) return ($response);

		$AutoFastSearchResponse = $this->_controller->Session->read('AutoFastSearchRkls');

		if(empty($AutoFastSearchResponse)) return $response;
		if(!is_array($AutoFastSearchResponse)) return $response;
		if(count($AutoFastSearchResponse) == 0) return $response;

		$response = $this->_controller->Rkl->find('first',array('conditions' => array('Rkl.id' => $AutoFastSearchResponse['reports']['Rkl']['id'])));

		return $response;

	}

	public function SaveSearchResult($response) {

		if(!isset($this->_controller->request->data['save_result'])) return ($response);
		if(!isset($this->_controller->request->data['Rkl'])) return ($response);

		unset($this->_controller->request->data['ajax_true']);
		unset($this->_controller->request->data['save_result']);

		if($this->_controller->Rkl->save($this->_controller->request->data['Rkl'])){
			$response['message'] = "Success";
		} else {
			$response['message'] = "Error";
		}

		return $response;
	}

}
