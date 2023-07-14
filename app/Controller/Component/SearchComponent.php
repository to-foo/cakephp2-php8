<?php
class SearchComponent extends Component {

	//
	//

	protected $_controller = null;

	public function initialize(Controller $controller) {
		$this->_controller = $controller;
	}

	public function ShowSearchResult($response) {

		if(!isset($this->_controller->request->data['show_search_result'])) return ($response);

		$AutoFastSearchResponse = $this->_controller->Session->read('AutoFastSearchResponse');

		if(empty($AutoFastSearchResponse)) return $response;
		if(!is_array($AutoFastSearchResponse)) return $response;
		if(count($AutoFastSearchResponse) == 0) return $response;

		$options['Reportnumber.id'] = $AutoFastSearchResponse['reports'];
		$options['Reportnumber.delete'] = 0;
//		$options['Reportnumber.deactive'] = 0;
		$options['Reportnumber.testingmethod_id !='] = 0;
		$options['Reportnumber.moved_id'] = 0;

		if(Configure::read('ShowChildrenReports') == false) $options['Reportnumber.parent_id'] = 0;


		$this->_controller->paginate = array(
			'conditions' => array($options),
			'order' => array('id' => 'desc'),
			'limit' => 25
		);

		$this->_controller->Reportnumber->recursive = 0;
		$response = $this->_controller->paginate('Reportnumber');

		return ($response);

	}

	public function UpdateSearchResult($response) {

		if(!isset($this->_controller->request->data['update_search_result'])) return ($response);
		if(!isset($this->_controller->request->data['Searching'])) return ($response);
		if(count($this->_controller->request->data['Searching']) == 0) return ($response);

		$ConditionsTopprojects = $this->_controller->Autorisierung->ConditionsTopprojects();
		$reportnumber_ids = array();
		$Reportnumbers = array();
		$SearchingIds = array();

		$options = array();

		$options['conditions']['topproject_id'] = $ConditionsTopprojects;

		$this->_controller->Searching->recursive = -1;

		foreach ($this->_controller->request->data['Searching'] as $key => $value) {

			if(!is_array($value)) continue;
			if(count($value) == 0) continue;

			foreach ($value as $_key => $_value) {

				if($_value == 0) continue;

				$Searching = $this->_controller->Searching->find('first',array('conditions' => array('Searching.id' => $_value)));

				if(count($Searching) == 0) continue;

				$options = array(
					'conditions' => array(
						'Searching.model' => $key,
						'Searching.field' => $_key,
						'AND' => array(
							'Searching.value' => $Searching['Searching']['value'],
						)
					)
				);

				$this->_controller->Searching->recursive = 1;
				$Searchings = $this->_controller->Searching->find('all',$options);

				if(count($Searchings) == 0) continue;

				$Reportnumbers[] = Hash::extract($Searchings, '{n}.SearchingValue.{n}.reportnumber_id');

			}
		}

		if(count($Reportnumbers) == 0) {

			$response['count'] = 0;
			$response['reports'] = array();

			return ($response);

		}

		if(count($Reportnumbers) == 1) $FoundetIDs = $Reportnumbers[0];
		else $FoundetIDs = call_user_func_array('array_intersect', $Reportnumbers);

		$Test = $this->_controller->Reportnumber->find('list',array('conditions' => array('Reportnumber.id' => $FoundetIDs,'Reportnumber.delete' => 1)));

		if(count($Test) > 0){
			foreach ($Test as $key => $value) {

				$_key = array_search($value, $FoundetIDs);
				if($_key !== false) unset($FoundetIDs[$_key]);

			}
		}

		if(count($FoundetIDs) == 0) {

			$response['count'] = 0;
			$response['reports'] = array();

			$this->_controller->Session->write('AutoFastSearchResponse',$response);

			return ($response);

		}

		$response['count'] = count($FoundetIDs);
		$response['reports'] = $FoundetIDs;

		$this->_controller->Session->write('AutoFastSearchResponse',$response);

		return ($response);

	}

	public function AutoFastStandard($response) {

//		if(!isset($this->_controller->request->data['landig_page_large'])) return ($response);

		$data = $this->_controller->request->data;

		if(!isset($data['Reportnumber'])) return $response;

		if(isset($data['Reportnumber']['testingmethod_id'])) $response = $this->__AutoFastStandardTestingmethod($response);

		return ($response);

	}

	protected function __AutoFastStandardTestingmethod($response){

		$data = $this->_controller->request->data;

		if(!isset($data['Reportnumber']['testingmethod_id'])) return $response;

		return $response;

	}

	public function AutoFastAdditional($response) {

		if(!isset($this->_controller->request->data['landig_page_large'])) return ($response);

		unset($this->_controller->request->data['ajax_true']);
		unset($this->_controller->request->data['landig_page_large']);

		if(count($this->_controller->request->data) == 0) return ($response);

		$Model = Sanitize::clean(key($this->_controller->request->data));

		if($Model != 'Generally' && $Model != 'Specific' && $Model != 'Evaluation') return ($response);

		$field = Sanitize::clean(key($this->_controller->request->data[$Model]));

		$Field = Inflector::camelize($field);

		if(!isset($this->_controller->request->data[$Model][$field])) return ($response);

		$Value = Sanitize::clean($this->_controller->request->data[$Model][$field]);

		$ConditionsTopprojects = $this->_controller->Autorisierung->ConditionsTopprojects();

		$options = array(
			'limit' => 10,
			'order' => array('value'),
			'group' => array('value'),
			'fields' => array('id','value'),
			'conditions' => array(
				'Searching.topproject_id' => $ConditionsTopprojects,
				'Searching.model' => $Model,
				'Searching.field' => $field,
				'Searching.value LIKE' => '%' . html_entity_decode($Value) . '%',
			)
		);

		$this->_controller->Searching->recursive = -1;
		$this->_controller->Topproject->recursive = -1;

		$Searchings = $this->_controller->Searching->find('all',$options);

		if(count($Searchings) == 0) return ($response);

		foreach ($Searchings as $key => $value) {

			array_push($response, array(
				'key' => $value['Searching']['id'],
				'value' => $value['Searching']['value'],
				'label' => $value['Searching']['value'],
				'field' => 'Searching' .$Model . $Field
				)
			);

		}

		return ($response);
	}

	// Die Kaskadensuchfunktionen existieren auch in der Advancekomponente
	// und sollen später ersetzt werden
	protected function __CascadeGetTreeList($Output) {

		$Cascade = $this->_controller->Cascade->find('list',array('conditions'=>array('Cascade.parent' => $Output)));

		if(count($Cascade) == 0) return $Output;

		$Output = array_merge($Output,$Cascade);
		$Output = array_unique($Output);

		foreach ($Output as $key => $value) {
			$Out = $this->__CascadeGetTreeListRecursive($value);
			$Output = array_merge($Output,$Out);
			$Output = array_unique($Output);
		}

		sort($Output);
		$Output = array_unique($Output);

		return $Output;
	}

	protected function __CascadeGetTreeListRecursive($Input) {

		$Cascade = $this->_controller->Cascade->find('list',array('conditions'=>array('Cascade.parent' => $Input)));

		if(count($Cascade) == 0) return $Cascade;

		foreach ($Cascade as $key => $value) {
			$Out = $this->__CascadeGetTreeListRecursive($value);
			$Cascade = array_merge($Cascade,$Out);
			$Cascade = array_unique($Cascade);
		}

		sort($Cascade);
		$Cascade = array_unique($Cascade);

		return $Cascade;
	}

	protected function __MinMaxDateSearings($Data,$Model,$Field){

		$projectID = $this->_controller->request->projectvars['VarsArray'][0];

		$conditions = array(
			'fields' => array('value'),
			'order' => array('value DESC'),
			'conditions' => array(
				'Searching.topproject_id' => $projectID,
				'Searching.model' => $Model,
				'Searching.field' => $Field,
			)
		);

		$End = $this->_controller->Searching->find('first',$conditions);
		$End = $this->__ConfigDateFormate($End['Searching']['value'],'date');

		$Data['end'] = $End;

		$conditions['order'] = array('value ASC');

		$Start = $this->_controller->Searching->find('first',$conditions);
		$Start = $this->__ConfigDateFormate($Start['Searching']['value'],'date');

		$Data['start'] = $Start;

		return $Data;

	}

	protected function __ConfigDateFormate($Date,$Format){

		if($Format == 'timestamp'){
			$DateArray = new DateTime($Date);
			$Date = $DateArray->getTimestamp();
			return $Date;
		}

		if(Configure::check('Dateformat') === false){

			$Dateformat = 'Y-m-d';
			$DateArray = new DateTime($Date);
			$Lang = 'eng';
			$NewDate = $DateArray->format($Dateformat);

			return $NewDate;
		}

		$Dateformat = Configure::read('Dateformat');

		$Lang = $this->_controller->request->lang;
		if(!is_array($Dateformat)) return $Date;
		if(count($Dateformat) == 0) return $Date;
		if(!isset($Dateformat[$Lang])) return $Date;
		if(!isset($Dateformat[$Lang][$Format])) return $Date;

		$DateArray = new DateTime($Date);
		$NewDate = $DateArray->format($Dateformat[$Lang][$Format]);

		return $NewDate;

	}

	protected function __ReConfigDateFormate($Date,$Format){

		if($Format == 'timestamp'){
			$DateArray = new DateTime($Date);
			$Date = $DateArray->getTimestamp();
			return $Date;
		}

		if(Configure::check('Dateformat') === false) return $Date;

		if($Format == 'isotime'){
			$DateArray = new DateTime($Date);
			$Date = $DateArray->format('Y-m-d');
			return $Date;
		}

		return $Date;

	}

	public function CompanyNeWeldFilter($ReportIDs,$Roll,$SearchFormData){

		if($this->_controller->Auth->user('roll_id') <= $Roll) return array('ReportIDs' => $ReportIDs,'SearchFormData' => $SearchFormData);;

		$TestOptions['conditions']['Reportnumber.id'] = $ReportIDs;
		if(AuthComponent::user('Testingcomp.extern') <> 1)	$TestOptions['conditions']['Reportnumber.testingcomp_id'] = $this->_controller->Auth->user('testingcomp_id');

		$TestX = $this->_controller->Reportnumber->find('list',$TestOptions);

		unset($TestOptions['conditions']['Reportnumber.testingcomp_id']);

		$TestOptions['conditions']['Reportnumber.result'] = 2;

		$TestY = $this->_controller->Reportnumber->find('list',$TestOptions);

		$TestZ = array_merge($TestY,$TestX);

		if(count($TestZ) > 0) $ReportIDs = $TestZ;
		else $ReportIDs = 0;

		$SearchFormData['Result']['Reports'] = $TestZ;

		return array('ReportIDs' => $ReportIDs,'SearchFormData' => $SearchFormData);
	}

	public function StandardSearchOptions($SearchFormData,$StandardModels){

		$projectID = $this->_controller->request->projectvars['VarsArray'][0];
		$cascadeID = $this->_controller->request->projectvars['VarsArray'][1];
		$orderID = $this->_controller->request->projectvars['VarsArray'][2];
		$reportID = $this->_controller->request->projectvars['VarsArray'][3];

		$StandardSearchOptions = array('conditions' => array('Reportnumber.topproject_id' => $projectID));

		foreach($StandardModels as $_key => $_value){
			if(!isset($SearchFormData[$_key])) continue;
			switch($_key){
				case 'Report':
				if(isset($SearchFormData[$_key]['id']) && $SearchFormData[$_key]['id'] > 0) $StandardSearchOptions['conditions']['Reportnumber.report_id'] = $SearchFormData[$_key]['id'];
				break;

				case 'Testingmethod':
				if(isset($SearchFormData[$_key]['id']) && $SearchFormData[$_key]['id'] > 0) $StandardSearchOptions['conditions']['Reportnumber.testingmethod_id'] = $SearchFormData[$_key]['id'];
				if(isset($SearchFormData[$_key]['verfahren']) && is_array($SearchFormData[$_key]['verfahren']) && count($SearchFormData[$_key]['verfahren']) > 0) $StandardSearchOptions['conditions']['Reportnumber.testingmethod_id'] = $SearchFormData[$_key]['verfahren'];
				break;

				case 'Testingcomp':
				if(isset($SearchFormData[$_key]['id']) && $SearchFormData[$_key]['id'] > 0) $StandardSearchOptions['conditions']['Reportnumber.testingcomp_id'] = $SearchFormData[$_key]['id'];
				break;

				case 'Reportnumber':

				if(isset($SearchFormData['Reportnumber'])){
					foreach($SearchFormData['Reportnumber'] as $__key => $__value){
						if($__key == 'date_of_test'){

							if(isset($__value['start']) && !empty($__value['start'])){
								$start = $this->__ReConfigDateFormate($__value['start'],'isotime');
								$StandardSearchOptions['conditions']['Reportnumber.date_of_test >'] = $start . ' 00:00:01';
							}
							if(isset($__value['end']) && !empty($__value['end'])){
								$end = $this->__ReConfigDateFormate($__value['end'],'isotime');
								$StandardSearchOptions['conditions']['Reportnumber.date_of_test <'] = $end . ' 23:59:59';
							}
						}
						if($__key == 'year'){
							if($__value > 0) $StandardSearchOptions['conditions']['Reportnumber.year'] = intval($__value);
						}
						if($__key == 'result'){
							if($__value > 0) $StandardSearchOptions['conditions']['Reportnumber.result'] = intval($__value);
						}
						if($__key == 'repair_for'){
							if($__value > 0) $StandardSearchOptions['conditions']['Reportnumber.repair_for <>'] = '0';
						}
					}
				}
				break;
				case 'Cascade':
				if(isset($SearchFormData['Cascade'])){
					foreach($SearchFormData['Cascade'] as $__key => $__value){
						if($__key == 'id'){

							$AllCasadeIDs = $this->__CascadeGetTreeList(array($SearchFormData['Cascade']['id']));

							if(is_array($AllCasadeIDs) && count($AllCasadeIDs) > 0){
								$StandardSearchOptions['conditions']['Reportnumber.cascade_id'] = $AllCasadeIDs;
							} else {
								$StandardSearchOptions['conditions']['Reportnumber.cascade_id'] = intval($__value);
							}
						}
					}
				}
				break;
/*
				case 'Order':
				if(isset($SearchFormData['Order'])){
					foreach($SearchFormData['Order'] as $__key => $__value){
						if($__key != 'id') break;
						if($__value > 0) $StandardSearchOptions['conditions']['Reportnumber.order_id'] = intval($__value);
					}
				}
				break;
*/
			}
		}

		$StandardSearchOptions['fields'] = array('Reportnumber.id');
		$StandardSearchOptions['conditions']['Reportnumber.delete'] = 0;

		return $StandardSearchOptions;

	}

	public function PutOrderSearch($StandardSearchOptions,$StandardFieldsOptions,$FoundetIDs){
		if($StandardFieldsOptions['Count']['Order'] == 0) return $StandardSearchOptions;
		$Option = array();
		$Option['fields'] = array('id');
		$Option['conditions'] = array();

		if(isset($FoundetIDs['Order']['result']) && count($FoundetIDs['Order']['result']) > 0){
			$StandardSearchOptions['conditions']['Reportnumber.order_id'] = $FoundetIDs['Order']['result'];

			return $StandardSearchOptions;
		}

		foreach($StandardFieldsOptions['Order'] as $_key => $_data){

			if(!isset($_data['value'])) continue;
			unset($_data['value'][0]);

				foreach($_data['value'] as $__key => $__data){
					$Option['conditions']['Order.' . Inflector::underscore($_key)][] = $__data[key($__data)];
				}
		}

		$OrderIDs = $this->_controller->Order->find('list',$Option);

		if(count($OrderIDs) > 0){
			$StandardSearchOptions['conditions']['Reportnumber.order_id'] = $OrderIDs;

		}

		return $StandardSearchOptions;
	}

	public function StandardAutocompleteOptions($AutocompleteOptions){

		$output = array();

		$projectID = $this->_controller->request->projectvars['VarsArray'][0];
		$cascadeID = $this->_controller->request->projectvars['VarsArray'][1];
		$orderID = $this->_controller->request->projectvars['VarsArray'][2];
		$reportID = $this->_controller->request->projectvars['VarsArray'][3];

		$this->_controller->Autorisierung->ConditionsTopprojectsTest($projectID);

		$StandardModels = array('Testingmethod' => true,'Topproject' => true,'Reportnumber' => true,'Cascade' => true,'Order' => true);
//		$StandardSearchOptions = array('conditions' => array('Reportnumber.topproject_id' => $projectID));

		if(!isset($StandardModels[$AutocompleteOptions['Model']])) return $output;

		switch($AutocompleteOptions['Model']){
			case 'Testingmethod':
			break;
			case 'Reportnumber':
			break;
			case 'Cascade':
			break;
			case 'Order':
			$this->_controller->Order->recursive = -1;
			$SearchOption = array(
							'Order.topproject_id' => $projectID,
							'AND' => array('Order.auftrags_nr LIKE' => $AutocompleteOptions['Value'] . '%'),
							);
			$Searching = $this->_controller->Order->find('list',array('limit' => 15,'fields' => array('id','auftrags_nr'),'conditions' => $SearchOption));
			foreach($Searching as $_key => $_Searching){
				$response[] = array('key' => $_key,'value' => $_Searching);
			}

			if(isset($response)) return $response;
			break;
		}

		return $output;

	}

	public function GetSearchingValueIDs($SearchFormData,$StandardModels,$SearchFieldsAdditional) {

		$projectID = $this->_controller->request->projectvars['VarsArray'][0];
		$cascadeID = $this->_controller->request->projectvars['VarsArray'][1];
		$orderID = $this->_controller->request->projectvars['VarsArray'][2];
		$reportID = $this->_controller->request->projectvars['VarsArray'][3];

		$AllSearchingValuesIDs = array();
		$output = array();

		$this->_controller->Searching->recursive = -1;
		$this->_controller->Searching->SearchingValue->recursive = -1;

		foreach($SearchFormData as $_key => $_SearchFormData){

			if($_key == 'Result') continue;
			// Die Standardwerte werden in der Reportnumbertabelle gesucht
			if(isset($StandardModels[$_key])) continue;

			foreach($_SearchFormData as $__key => $__SearchFormData){
				// Wenn bei dem Multiselects leere Daten im Array ankommen
				if(is_array($__SearchFormData) && empty($__SearchFormData[0])){
//					var_dump($__SearchFormData[0]);
//					continue;
				}

				if($__SearchFormData == 0) continue;

				if(isset($SearchingValuesIDs)) $LastSearchingValuesIDs = $SearchingValuesIDs;

				if(is_array($__SearchFormData)) $SearchIDS = $__SearchFormData;
				else $SearchIDS = intval($__SearchFormData);

				$fieldtype = trim($SearchFieldsAdditional->fields->$__key->fieldtype);

				if($fieldtype == 'multiselect') {
					$SearchIDSArray = explode(',',$SearchIDS[0]);
					$options = array(
						'fields' => array('id'),
						'conditions' => array(
							'Searching.topproject_id' => $projectID,
							'Searching.model' => $_key,
							'Searching.field' => $__key,
							'Searching.value' => $SearchIDSArray,
						)
					);

					$SearchIDS = $this->_controller->Searching->find('list',$options);
				}

				$SearchingValuesIDs = $this->_controller->Searching->SearchingValue->find('list',array('fields' => array('reportnumber_id'),'conditions' => array('SearchingValue.searching_id' => $SearchIDS)));
				sort($SearchingValuesIDs);
				if(count($SearchingValuesIDs) > 0) $AllSearchingValuesIDs[] = $SearchingValuesIDs;

			}
		}

		return $AllSearchingValuesIDs;

	}

	public function DateSearchFormating($DropdownValues,$SearchFieldsAdditional) {

		$this->_controller->Searching->recursive = -1;
		$this->_controller->Searching->SearchingValue->recursive = -1;

		foreach($DropdownValues as $_key => $_data){
			foreach($_data as $__key => $__data){

				$key = Inflector::underscore($__key);

				if(empty($SearchFieldsAdditional->fields->$key)) continue;
				if(empty($SearchFieldsAdditional->fields->$key->key)) continue;
				if(empty($SearchFieldsAdditional->fields->$key->model)) continue;

				$Field = trim($SearchFieldsAdditional->fields->$key->key);
				$Model = trim($SearchFieldsAdditional->fields->$key->model);
				$Fieldtype = trim($SearchFieldsAdditional->fields->$key->fieldtype);

				switch($Fieldtype){
					case 'date':

					if(!isset($DropdownValues[$_key][$__key]['start'])) break;
					if(!isset($DropdownValues[$_key][$__key]['end'])) break;

					$Start = $this->__ReConfigDateFormate($DropdownValues[$_key][$__key]['start'],'isotime');
					$End = $this->__ReConfigDateFormate($DropdownValues[$_key][$__key]['end'],'isotime');


					$SearchingOptions = array(
										'conditions' => array(
											'Searching.model' => $_key,
											'Searching.field' => $__key,
											'Searching.value >=' => $Start,
											'Searching.value <=' => $End
											)
										);

					$SearchingIDs = $this->_controller->Searching->find('list',$SearchingOptions);

					unset($DropdownValues[$_key][$__key]);
					$DropdownValues[$_key][$__key] = $SearchingIDs;

					break;
				}
			}
		}

		return $DropdownValues;
	}

	public function DropdownFormating($DropdownValues,$SearchFieldsAdditional) {

		foreach($DropdownValues as $_key => $_data){
			foreach($_data as $__key => $__data){

				$key = Inflector::underscore($__key);

				if(empty($SearchFieldsAdditional->fields->$key)) continue;
				if(empty($SearchFieldsAdditional->fields->$key->key)) continue;
				if(empty($SearchFieldsAdditional->fields->$key->model)) continue;

				$Field = trim($SearchFieldsAdditional->fields->$key->key);
				$Model = trim($SearchFieldsAdditional->fields->$key->model);
				$Fieldtype = trim($SearchFieldsAdditional->fields->$key->fieldtype);

				switch($Fieldtype){
					case 'date':
					unset($__data['value'][0]);

					$Lang = $this->_controller->request->lang;
					$Dates = Hash::extract($__data, 'value.{n}.{n}');
					$Dateformat = Configure::read('Dateformat');

					if(count($Dates) == 0){

//						$DropdownValues[$_key][$__key] = $this->__MinMaxDateSearings($DropdownValues[$_key][$__key],$Model,$Field);
						$DropdownValues[$_key][$__key]['start'] = '';
						$DropdownValues[$_key][$__key]['end'] = '';
						$DropdownValues[$_key][$__key]['fieldtype'] = 'date';

						break;
					}

					$MinDate = $Dates[0];
					$MaxDate = $Dates[count($Dates) - 1];

					$DropdownValues[$_key][$__key]['start'] = $MinDate;
					$DropdownValues[$_key][$__key]['end'] = $MaxDate;
					$DropdownValues[$_key][$__key]['fieldtype'] = 'date';

					break;
				}
			}
		}

		return $DropdownValues;
	}

	public function DateAddMinMax($AllDropdownValues,$SearchFieldsAdditional,$NoAdditionalPostData) {

		if($NoAdditionalPostData === false) return $AllDropdownValues;

		// Wenn in den zusätzhlichen Feldern ein Datumsfeld vorhanden ist
		// und noch keine Werte vom Formular gekommen SearchInsertData
		// Müssen Max- und Minwerte hinzugefügt werden
		$WithList = array('Generally');

		foreach($SearchFieldsAdditional->fields->children() as $key => $value){

			if(!in_array(trim($value->model), $WithList)) continue;
			if(trim($value->fieldtype) != 'date') continue;

			$Model = trim($value->model);
			$Key = Inflector::camelize(trim($value->key));
			$DataArray = $AllDropdownValues[$Model][$Key]['value'];

			unset($DataArray[0]);

			$SearchIDs = array();

			foreach ($DataArray as $_key => $_value) {
				$SearchIDs[] = key($_value);
			}

			$SearchingValues = $this->_controller->Searching->SearchingValue->find('list',array('fields' => array('reportnumber_id'),'conditions' => array('SearchingValue.searching_id' => $SearchIDs)));
			$Reportnumber = $this->_controller->Reportnumber->find('count',array('conditions' => array('Reportnumber.id' => $SearchingValues,'Reportnumber.delete' => 0)));
			break;
		}

		return $AllDropdownValues;
	}

	public function SearchModulFields($fields,$SearchFormData,$IDs,$modus) {

		$Values = array();

		if(!is_object($fields)) return $Values;

		foreach($fields->fields->children() as $_key => $_fields){

		//	if(trim($_fields->fieldtype) == 'autocomplete') continue;

			$model = trim($_fields->model);
			$Model = ucfirst($model);
			$field_camelize = Inflector::camelize(trim($_fields->key));
			$field = trim($_fields->key);

			$Option['conditions'][$Model . '.testingcomp_id'] = AuthComponent::user('testingcomp_id');
			$Option['conditions'][$Model . '.deleted'] = 0;
			$Option['conditions'][$Model . '.active'] = 1;
			$Option['fields'] = array($field);

			$Option['group'] = array($field);
			$Option['order'] = array($field);

			if(is_array($IDs) && count($IDs) > 0){
				$Option['conditions'][$Model . '.id'] = $IDs;
				$Option['conditions'][$Model . '.' . $field . ' !='] = '';

			}

			switch (trim($_fields->fieldtype)) {
				case 'date':
					$Searching = $this->_controller->$Model->find('list',$Option);
					$Field = $field_camelize;
					if(isset($Searching) && count($Searching) > 0){
						$Limitation = Hash::extract($Searching, '{n}.'.$Model.'.' . trim($_fields->option));
						if(count($Limitation) > 0){
							$Limitation = array_unique($Limitation);
							$Option['conditions'][trim($_fields->option)] = $Limitation;
						}
					}

					$Option['order'] = array($Model.'.'.trim($_fields->output).' DESC');
					$ResultLast = $this->_controller->$Model->find('first',$Option);
					$Option['order'] = array($Model.'.'.trim($_fields->output).' ASC');
					$ResultFirst = $this->_controller->$Model->find('first',$Option);

					if(count($ResultFirst)){
						$Values[$Model][$field_camelize]['start_timestamp'] = $this->__ConfigDateFormate($ResultFirst[$Model][trim($_fields->output)],'timestamp');
						$Values[$Model][$field_camelize]['start'] = $this->__ConfigDateFormate($ResultFirst[$Model][trim($_fields->output)],'date');
					}
					if(count($ResultLast)){
						$Values[$Model][$field_camelize]['end_timestamp'] = $this->__ConfigDateFormate($ResultLast[$Model][trim($_fields->output)],'timestamp');
						$Values[$Model][$field_camelize]['end'] = $this->__ConfigDateFormate($ResultLast[$Model][trim($_fields->output)],'date');
					}


					if($modus == 'update')foreach($Searching as $_key => $_Result) $FinalForJson[] = array($_key => $_Result);
					else $FinalForJson = $Searching;


					$Values[$model][$field_camelize]['value'] = $FinalForJson;
					$Values[$model][$field_camelize]['selected'] = 0;
					$Values[$model][$field_camelize]['description'] = $_fields->description;
					$Values[$model][$field_camelize]['key'] = $field;
					$Values[$Model][$field_camelize]['fieldtype'] = 'date';
				break;
				case 'autocomplete':
					$Searching = $this->_controller->$Model->find('list',$Option);

					$Searching[0] = '';
					asort($Searching);

					if($modus == 'update')foreach($Searching as $_key => $_Result) $FinalForJson[] = array($_key => $_Result);
					else $FinalForJson = $Searching;



					$Values[$model][$field_camelize]['value'] = $FinalForJson;
					$Values[$model][$field_camelize]['selected'] = 0;
					$Values[$model][$field_camelize]['fieldtype'] = 'autocomplete';
					$Values[$model][$field_camelize]['description'] = $_fields->description;
					$Values[$model][$field_camelize]['key'] = $field;
				break;
				case 'dropdown':
					$Searching = $this->_controller->$Model->find('list',$Option);

					if(empty($_fields->radiooption)){

						$Searching[0] = '';
						asort($Searching);

					} elseif(!empty($_fields->radiooption) && isset($_fields->radiooption->value)){

						$Searching[0] = '';

						foreach ($Searching as $key => $value) {

							if(!empty($_fields->radiooption->value->$value)){
								$Searching[$key] = trim($_fields->radiooption->value->$value);
							}
						}

						asort($Searching);

					}

					if($modus == 'update')foreach($Searching as $_key => $_Result) $FinalForJson[] = array($_key => $_Result);
					else $FinalForJson = $Searching;

					$Values[$model][$field_camelize]['value'] = $FinalForJson;
					$Values[$model][$field_camelize]['selected'] = 0;
					$Values[$model][$field_camelize]['fieldtype'] = 'dropdown';
					$Values[$model][$field_camelize]['description'] = $_fields->description;
				break;

				case 'multiple':
					$Searching = $this->_controller->$Model->find('list',$Option);

					$Searching[0] = '';
					asort($Searching);

					if($modus == 'update')foreach($Searching as $_key => $_Result) $FinalForJson[] = array($_key => $_Result);
					else $FinalForJson = $Searching;

					$Values[$model][$field_camelize]['value'] = $FinalForJson;
					$Values[$model][$field_camelize]['selected'] = 0;
					$Values[$model][$field_camelize]['fieldtype'] = 'dropdown';
					$Values[$model][$field_camelize]['description'] = $_fields->description;
				break;

			}

			if(isset($this->_controller->request->data[$Model][$field])) $Values[$model][$field_camelize]['selected'] = $this->_controller->request->data[$Model][$field];

			unset($FinalForJson);

		}

		return $Values;
	}

	public function SearchModulResults($FieldValues,$Model) {

		if(!is_array($FieldValues)) return array();
		if(!isset($this->_controller->request->data[$Model])) return $FieldValues;

		$RequestData = $this->_controller->request->data[$Model];

		if(count($RequestData) == 0) return $FieldValues;

		$this->_controller->$Model->recursive = -1;
		$conditions = array();
		foreach($RequestData as $_key => $_data){

			foreach ($FieldValues[$Model] as $fvkey => $fvalue) {

				if($fvalue['fieldtype'] == 'autocomplete' && !empty($fvalue['key']) && $fvalue ['key'] == $_key && !empty($_data)){
					$dataid = $this->_controller->$Model->find('first',
						array(
							'conditions' =>array($Model .'.'.$fvalue['key']  => $_data ),
							'fields' => array('id'),
						)
					);
					$_data = $dataid [$Model] ['id'];

				}

				if($fvalue['fieldtype'] == 'date' && !empty($fvalue['key']) && $fvalue ['key'] == $_key && !empty($_data)){
					if(!empty($_data['start']) && !empty($_data['end'])){
						$dataid = $this->_controller->$Model->find('list',
										array(
											'conditions' =>array('date('.$Model.'.'.$_key.') BETWEEN ? AND ?' => array($_data['start'], $_data['end'])),
											'fields' => array('id'),
										)
									);

						$_data = $dataid;
					}
				}
			}
			$Field = Inflector::camelize(trim($_key));

			// Da nur die Id übermittelt werden,
			// werden die Suchbegriffe ermittelt
			$parms = $this->_controller->$Model->find('list',
				array(
					'conditions' =>array($Model . '.id' => $_data) ,
					'fields' => array($_key)
				)
			);

			if(count($parms) == 0) continue;

			$FieldValues[$Model][$Field]['selected'] = array_keys($parms);
			foreach ($parms as $pkey => $pvalue) {
				if($this->_controller->Session->check('SearchHistoryString') == true) $HistoryString = $this->_controller->Session->delete('SearchHistoryString.'.$_key);
				$this->_controller->Session->write('SearchHistoryString.'.$_key,$pvalue);
			}

			$Results = $this->_controller->$Model->find('list',
				array(
					'conditions' => array($Model . '.' . $_key => $parms),
					'fields' => array($_key)
				)
			);
			$FieldValues[$Model][$Field]['ids'] = array_keys($Results);
		}

		return $FieldValues;
	}

	public function SearchFields($fields,$SearchFormData,$modus) {

		$projectID = $this->_controller->request->projectvars['VarsArray'][0];
		$cascadeID = $this->_controller->request->projectvars['VarsArray'][1];
		$orderID = $this->_controller->request->projectvars['VarsArray'][2];
		$reportID = $this->_controller->request->projectvars['VarsArray'][3];

		$Values = array();

		if(!is_object($fields)) return $Values;

		foreach($fields->fields->children() as $_key => $_fields){

			if(trim($_fields->fieldtype) == 'autocomplete') continue;

			switch (trim($_fields->fieldtype)) {
				case 'multiselect':
				$model = trim($_fields->model);
				$field_camelize = Inflector::camelize(trim($_fields->key));
				$field = trim($_fields->key);

				$SearchingFormOptions = array(
								'fields' => array('Searching.id','Searching.value'),
								'order' => array('Searching.value'),
								'conditions' => array(
									'Searching.topproject_id' => $projectID ,
									'Searching.value !=' => '',
									'Searching.model' => $model,
									'Searching.field' => $field
								)
							);

				$Searching = $this->_controller->Searching->find('list',$SearchingFormOptions);

				$Searching[0] = '';
				asort($Searching);

				if($modus == 'update') foreach($Searching as $_key => $_Result) $FinalForJson[] = array($_Result => $_Result);
				else foreach($Searching as $_key => $_Result) $FinalForJson[$_Result] = $_Result;

				$Values[$model][$field_camelize]['value'] = $FinalForJson;
				$Values[$model][$field_camelize]['selected'] = 0;
				$Values[$model][$field_camelize]['fieldtype'] = 'multiselect';

				if(isset($SearchFormData[$model][$field][0]) && !empty($SearchFormData[$model][$field][0])){
					$Selected = explode(',',$SearchFormData[$model][$field][0]);
					$Values[$model][$field_camelize]['selected'] = $Selected;
				}

				$SearchingFormOptions = array();
				$Searching = array();
				$FinalForJson = array();

				break;

				case 'dropdown':
					$model = trim($_fields->model);
					$field_camelize = Inflector::camelize(trim($_fields->key));
					$field = trim($_fields->key);

					$SearchingFormOptions = array(
									'fields' => array('Searching.id','Searching.value'),
									'order' => array('Searching.value'),
									'conditions' => array(
										'Searching.topproject_id' => $projectID ,
										'Searching.value !=' => '',
										'Searching.model' => $model,
										'Searching.field' => $field
									)
								);

					$Searching = $this->_controller->Searching->find('list',$SearchingFormOptions);

					$Searching[0] = '';
					asort($Searching);

					if($modus == 'update')foreach($Searching as $_key => $_Result) $FinalForJson[] = array($_key => $_Result);
					else $FinalForJson = $Searching;

					$Values[$model][$field_camelize]['value'] = $FinalForJson;
					$Values[$model][$field_camelize]['selected'] = 0;
					$Values[$model][$field_camelize]['fieldtype'] = 'dropdown';

					$SearchingFormOptions = array();
					$Searching = array();
					$FinalForJson = array();

				break;

				case 'date':

					$model = trim($_fields->model);
					$field_camelize = Inflector::camelize(trim($_fields->key));
					$field = trim($_fields->key);

					if($field == 'date_of_test'){

						if(isset($Option)) unset($Option['conditions'][$model.'.'.$field]);

						if(isset($SearchFormData[$model][$field]['start']) && !empty($SearchFormData[$model][$field]['start'])){

							$Start = $this->__ReConfigDateFormate($SearchFormData[$model][$field]['start'],'isotime');
							$Start = $Start . ' 00:00:01';

							$Option['conditions'][$model.'.'.$field.' >'] = $Start;

						}
						if(isset($SearchFormData[$model][$field]['end']) && !empty($SearchFormData[$model][$field]['end'])){

							$End = $this->__ReConfigDateFormate($SearchFormData[$model][$field]['end'],'isotime');
							$End = $End . ' 23:59:59';

							$Option['conditions'][$model.'.'.$field.' <'] = $End;
						}
					}


					$SearchingFormOptions = array(
									'fields' => array('Searching.id','Searching.value'),
									'order' => 'Searching.field DESC',
									'conditions' => array(
										'topproject_id' => $projectID,
										'model' => $model,
										'field' => $field
									)
								);

					$Searching = $this->_controller->Searching->find('list',$SearchingFormOptions);
					sort($Searching);

					$Values[$model][$field_camelize]['description'] = $_fields->description;
					$Values[$model][$field_camelize]['start'][0] = min($Searching);
					$Values[$model][$field_camelize]['start'][1] = '00:00:00';
					$Values[$model][$field_camelize]['end'][0] = max($Searching);
					$Values[$model][$field_camelize]['end'][1] = '23:59:59';
					$Values[$model][$field_camelize]['fieldtype'] = 'date';

					$SearchingFormOptions = array();
					$Searching = array();

				break;

			}
		}

		return $Values;
	}

	public function ShowAdditionalFieldResults($fields,$SearchFormData,$Values = array()) {

		if(!is_object($fields)) return $Values;
		if($this->_controller->request->projectvars['VarsArray'][0] == 0) return $Values;

		$projectID = $this->_controller->request->projectvars['VarsArray'][0];
		$cascadeID = $this->_controller->request->projectvars['VarsArray'][1];
		$orderID = $this->_controller->request->projectvars['VarsArray'][2];
		$reportID = $this->_controller->request->projectvars['VarsArray'][3];

		$local = $this->_controller->request->local;

		$this->_controller->Searching->recursive = -1;

		foreach($fields->fields->children() as $_key => $_fields){

			$Model = trim($_fields->model);
			$field_camelize = Inflector::camelize(trim($_fields->key));
			$field = trim($_fields->option);
			$fieldtype = trim($_fields->fieldtype);

			$name = trim($_fields->output);
			$Field = ucfirst($field);
			$Limitation = array();

			if(!isset($SearchFormData['Current'][$Model])) continue;
			if(!isset($SearchFormData['Current'][$Model][$field])) continue;
			if($SearchFormData['Current'][$Model][$field] == 0) continue;

			$Result = $this->_controller->Searching->find('first',array('fields' => array('value'), 'conditions' => array('Searching.id' => intval($SearchFormData['Current'][$Model][$field]))));

			if(count($Result) == 1) $Values[$Model][$field] = trim($_fields->description->$local) . ': ' . $Result['Searching']['value'];

			if($fieldtype == 'multiselect'){
				$Values[$Model][$field] = trim($_fields->description->$local) . ': ' . implode(',',$SearchFormData['Current'][$Model][$field]);
			}

			if(trim($_fields->fieldtype) == 'date' && isset($SearchFormData['Current'][$Model][$field]['start']) && isset($SearchFormData['Current'][$Model][$field]['end'])){
				$Values[$Model][$field] = trim($_fields->description->$local) . ': ' . $SearchFormData['Current'][$Model][$field]['start'] . ' - ' . $SearchFormData['Current'][$Model][$field]['end'];
			}

		}
		return $Values;
	}

	public function ShowStandardFieldResults($fields,$SearchFormData,$Values = array()) {

		if(!is_object($fields)) return $Values;
		if($this->_controller->request->projectvars['VarsArray'][0] == 0) return $Values;

		$projectID = $this->_controller->request->projectvars['VarsArray'][0];
		$cascadeID = $this->_controller->request->projectvars['VarsArray'][1];
		$orderID = $this->_controller->request->projectvars['VarsArray'][2];
		$reportID = $this->_controller->request->projectvars['VarsArray'][3];

		$local = $this->_controller->request->local;

		$this->_controller->Order->recursive = -1;

		$this->_controller->Testingmethod->recursive = -1;

		$this->_controller->Report->recursive = -1;

		$this->_controller->Topproject->recursive = 0;
		$Topproject = $this->_controller->Topproject->find('first',array('conditions' => array('Topproject.id' => $projectID)));

		if(isset($SearchFormData['Cascade']['id']))$ChildrenList = $this->__CascadeGetTreeList(array($SearchFormData['Cascade']['id']));
		else $ChildrenList = $this->__CascadeGetTreeList(array($cascadeID));

		foreach($fields->fields->children() as $_key => $_fields){

			if(empty($_fields->fieldtype)) continue;

			$Model = trim($_fields->model);
			$field_camelize = Inflector::camelize(trim($_fields->key));
			$field = trim($_fields->option);
			$name = trim($_fields->output);
			$Field = ucfirst($field);
			$Limitation = array();

			switch ($Model) {

				case 'Topproject':

				$ConditionsTopprojects = $this->_controller->Autorisierung->ConditionsTopprojects();

				$Fields = array('projektname');

				$ConditionsTopprojects = array(
											'fields' => $Fields,
											'conditions'=>array(
												'Topproject.id' => $projectID
//												'Topproject.id' => $ConditionsTopprojects
												)
											);

				$Result = $this->_controller->Topproject->find('first',$ConditionsTopprojects);

				$Values[$Model]['projektname'] = $Result[$Model]['projektname'];

				break;

				case 'Cascade':

				if(!isset($SearchFormData['Current'][$Model])) break;
				if(!isset($SearchFormData['Current'][$Model][$field])) break;
				if($SearchFormData['Current'][$Model][$field] == '0') break;

				$this->_controller->Cascade->recursive = -1;

				$Option = array();
				$Option['conditions']['topproject_id'] = $projectID;
				$Option['conditions']['id'] = $SearchFormData['Current']['Cascade']['id'];


				$Option['fields'] = array('discription');

				$Result = $this->_controller->Cascade->find('first',$Option);

				if(isset($Result[$Model][$field])) $Values[$Model][$field] = $Result[$Model][$field];

				break;

				case 'Order':

				if(!isset($SearchFormData['Current'][$Model])) break;
				if(!isset($SearchFormData['Current'][$Model][$field])) break;
				if($SearchFormData['Current'][$Model][$field] == '0') break;

				$Option = array();
				$Option['conditions']['topproject_id'] = $projectID;
				$Option['conditions']['deleted'] = 0;
				$Option['conditions']['id'] = $SearchFormData['Current'][$Model][$field];
				$Result = $this->_controller->Order->find('first',$Option);

				if(isset($Result[$Model][$field])) $Values[$Model][$field] = $Result[$Model][$field];

				break;

				case 'Testingmethod':

				if(!isset($SearchFormData['Current'][$Model])) break;
				if(!isset($SearchFormData['Current'][$Model][$field])) break;

				if($SearchFormData['Current'][$Model][$field] != '0') {

					$Option = array();
					$Option['conditions']['id'] = $SearchFormData['Current'][$Model][$field];
					$Option['fields'] = array('verfahren');
					$Result = $this->_controller->Testingmethod->find('first',$Option);

					if(count($Result) > 0) $Values[$Model][$field] = $Result[$Model]['verfahren'];
					elseif(count($Result) == 0) $Values[$Model][$field] = __('all testingmethods',true);

				} else {
					$Values[$Model][$field] = __('all testingmethods',true);
				}

				break;

				case 'Report':

				if(!isset($SearchFormData['Current'][$Model])) break;
				if(!isset($SearchFormData['Current'][$Model][$field])) break;

				if($SearchFormData['Current'][$Model][$field] != '0') {
					$Option = array();
					$Option['fields'] = array('name');
					$Option['conditions'][$Model.'.id'] = $SearchFormData['Current'][$Model][$field];
					$Result = $this->_controller->Report->find('first',$Option);
					$Values[$Model][$field] = $Result[$Model]['name'];
				}

				break;

				case 'Reportnumber':

				if(!isset($SearchFormData['Current'][$Model])) break;

//				$Result['Reportnumber'] = array();

				if($field == 'date_of_test'){

					$Result['Reportnumber']['date_of_test'] = array();
					if(isset($SearchFormData['Current'][$Model][$field]['start']) && !empty($SearchFormData['Current'][$Model][$field]['start'])){
						$Result['Reportnumber']['date_of_test'] = __('from',true) . ' ' . $SearchFormData['Current'][$Model][$field]['start'] . ' ';
					}
					if(isset($SearchFormData['Current'][$Model][$field]['end']) && !empty($SearchFormData['Current'][$Model][$field]['end'])){
						$Result['Reportnumber']['date_of_test'] .= __('to',true) . ' ' . $SearchFormData['Current'][$Model][$field]['end'];
					}

					$Values[$Model][$field] = $Result[$Model][$field];
				}

				if($field == 'year'){
					if(!isset($SearchFormData['Current'][$Model][$field])) break;
					if($SearchFormData['Current'][$Model][$field] == '0') break;
					$Values[$Model][$field] = __('Testing year') . ' ' . $SearchFormData['Current'][$Model][$field];
				}

				break;
			}
		}

		if(isset($this->_controller->request->data['Reportnumber']['testingmethod']) && $this->_controller->request->data['Reportnumber']['testingmethod'] != 'all_testingmethods') {

			$Option = array();
			$Option['conditions']['value'] = $this->_controller->request->data['Reportnumber']['testingmethod'];
			$Option['fields'] = array('verfahren');

			$Result = $this->_controller->Testingmethod->find('first',$Option);
			$Values['Testingmethod']['id'] = $Result['Testingmethod']['verfahren'];
		}

		return $Values;
	}

	public function DeleteEmptyMultipleArrayAdditional($SearchFields,$SearchFormData,$Type){

		foreach($SearchFields->fields->children() as $key => $value){

			$Model = trim($value->model);
			$Key = trim($value->key);
			$Fieldtype = trim($value->fieldtype);

			if(!isset($SearchFormData[$Model][$Key])) continue;
			if($Fieldtype != 'multiselect') continue;

			if(is_array($SearchFormData[$Model][$Key])){
				if(count($SearchFormData[$Model][$Key]) == 1 && empty($SearchFormData[$Model][$Key][0])){
						unset($SearchFormData[$Model][$Key]);
				}
			}
		}

		return $SearchFormData;
	}

	public function CreateMultipleArray($SearchFieldsStandard,$SearchFormData,$Type){

		foreach($SearchFieldsStandard->fields->children() as $key => $value){

			$Model = trim($value->model);
			$Key = trim($value->key);
			$Fieldtype = trim($value->fieldtype);

			if(!isset($SearchFormData[$Model][$Key])) continue;

			if($Fieldtype != 'multiselect') continue;

			$output = array();

			if(is_array($SearchFormData[$Model][$Key])){

				if(count($SearchFormData[$Model][$Key]) == 0) continue;

				foreach ($SearchFormData[$Model][$Key] as $_key => $_value) {

					if(empty($_value)) {
						$SearchFormData[$Model][$Key] = 0;
						continue;
					}

					$output = explode(',',$_value);
					$SearchFormData[$Model][$Key] = $output;
				}
			}
		}

		return $SearchFormData;

	}

	public function ColletAdditionalsFromSearchTable($AllSearchingValuesIDs,$DropdownValues,$SearchFieldsAdditional) {

		$SearchingReportnumberIDs = $this->_controller->Searching->SearchingValue->find('list',array('fields' => array('searching_id'),'group' => 'searching_id', 'conditions' => array('SearchingValue.reportnumber_id' => $AllSearchingValuesIDs)));

		$SearchOptions = array(
							'order' => array('Searching.value'),
							'fields' => array('Searching.id','Searching.value','Searching.field','Searching.model'),
							'conditions' => array('Searching.id' => $SearchingReportnumberIDs)
						);

		$Searching = $this->_controller->Searching->find('all',$SearchOptions);

		foreach($Searching as $_Searching){

			$Model = $_Searching['Searching']['model'];
			$field = $_Searching['Searching']['field'];
			$Field = Inflector::camelize($field);
			$fieldtype = trim($SearchFieldsAdditional->fields->{$field}->fieldtype);

			$DropdownValues[$Model][$Field]['value'][0] = array(0 => '');

			if($fieldtype == 'multiselect') $DropdownValues[$Model][$Field]['value'][] = array($_Searching['Searching']['value'] => $_Searching['Searching']['value']);
			if($fieldtype == 'dropdown') $DropdownValues[$Model][$Field]['value'][] = array($_Searching['Searching']['id'] => $_Searching['Searching']['value']);
			if($fieldtype == 'date'){
				$_Searching['Searching']['value'] = $this->__ConfigDateFormate($_Searching['Searching']['value'] ,'date');
				$DropdownValues[$Model][$Field]['value'][] = array($_Searching['Searching']['id'] => $_Searching['Searching']['value']);
			}

			if(isset($SearchFormData[$Model][$_Searching['Searching']['field']]) && $SearchFormData[$Model][$field] > 0){
				$DropdownValues[$Model][$Field]['selected'] = $SearchFormData[$Model][$field];
			}

		}

		return $DropdownValues;
	}

	public function SearchStandardFieldsbyOrder($fields,$SearchFormData,$modus) {

		$Values = array();

		if(!is_object($fields)) return $Values;
		if($this->_controller->request->projectvars['VarsArray'][0] == 0) return $Values;

		$projectID = $this->_controller->request->projectvars['VarsArray'][0];
		$cascadeID = $this->_controller->request->projectvars['VarsArray'][1];
		$orderID = $this->_controller->request->projectvars['VarsArray'][2];
		$reportID = $this->_controller->request->projectvars['VarsArray'][3];

		$local = $this->_controller->request->local;

		$FoundetIDs = array();

		$this->_controller->Order->recursive = -1;
		$this->_controller->Report->recursive = -1;

		$this->_controller->Testingcomp->recursive = -1;

		$this->_controller->Topproject->recursive = 1;
		$Topproject = $this->_controller->Topproject->find('first',array('conditions' => array('Topproject.id' => $projectID)));

		if(isset($SearchFormData['Cascade']['id'])) $ChildrenList = $this->__CascadeGetTreeList(array($SearchFormData['Cascade']['id']));
		else $ChildrenList = $this->__CascadeGetTreeList(array($cascadeID));

		// Alle Ids zu den Suchanfragen holen
		foreach($fields->fields->children() as $_key => $_fields){

			if(empty($_fields->fieldtype)) continue;

			$Model = trim($_fields->model);
			$field_camelize = Inflector::camelize(trim($_fields->key));
			$field = trim($_fields->option);
			$name = trim($_fields->output);
			$Field = ucfirst($field);
			$Limitation = array();

			switch ($Model) {

				case 'Topproject':

				$ConditionsTopprojects = $this->_controller->Autorisierung->ConditionsTopprojects();

				$Fields = array('id','id');
				if($modus == 'result') $Fields = array('id');

				$ConditionsTopprojects = array(
											'fields' => $Fields,
											'conditions'=>array(
												'Topproject.id' => $projectID
												)
											);

				$Topprojects = $this->_controller->Topproject->find('list',$ConditionsTopprojects);

				$FoundetIDs[$Model]['result'][] = $Topprojects;

				break;

				case 'Cascade':

				$Option = array();
				$this->_controller->Cascade->recursive = -1;
				$Option['conditions']['topproject_id'] = $projectID;
				$Option['conditions']['level >'] = 0;

				$Option['fields'] = array(trim($_fields->output));

				$Option['fields'] = array('id','id');
				if($modus == 'result') $Option['fields'] = array('id');

				$Option['group'] = array(trim($_fields->output));

				$Option['conditions']['id'] = $ChildrenList;

				$Result = $this->_controller->Cascade->find('list',$Option);
				$FoundetIDs[$Model]['result'][] = $Result;

				break;

				case 'Order':

				if(!isset($SearchFormData[$Model])) break;
				if(!isset($SearchFormData[$Model][$field])) break;
				if($SearchFormData[$Model][$field] == '0') break;

				$Option = array();

				$Option['conditions']['topproject_id'] = $projectID;
				$Option['conditions']['deleted'] = 0;

				if(isset($SearchFormData['Cascade']['id']) && $SearchFormData['Cascade']['id'] != 0 && $SearchFormData['Cascade']['id'] != ''){
					$ChildrenList = $this->__CascadeGetTreeList(array($SearchFormData['Cascade']['id']));
					$Option['conditions']['cascade_id'] = $ChildrenList;
				}
				if(isset($SearchFormData[$Model])){
					if(is_array($SearchFormData[$Model][$field])){
						if(count($SearchFormData[$Model][$field]) > 0) {
							$Option['conditions'][$field] = $SearchFormData[$Model][$field];
						}
					} else {
						if(isset($SearchFormData[$Model][$field]) && $SearchFormData[$Model][$field] != '0'){
							$Option['conditions'][$field] = $SearchFormData[$Model][$field];
						}
					}
				}

				$Result = $this->_controller->Order->find('list',$Option);

				$FoundetIDs[$Model]['result'][] = $Result;

				break;

				case 'Testingmethod':

				$Topproject = $this->_controller->Data->BelongsToManySelected($Topproject,'Topproject','Report',array('ReportsTopprojects','report_id','topproject_id'));
				$Testingmethods = array();

				foreach($Topproject['Report']['selected'] as $_key => $_value){
					$HelpArray['Report']['id'] = $_value;
					$HelpArray = $this->_controller->Data->BelongsToManySelected($HelpArray,'Report','Testingmethod',array('TestingmethodsReports','testingmethod_id','report_id'));
					if(count($HelpArray['Testingmethod']['selected']) > 0){
						$Testingmethods = array_merge($Testingmethods, $HelpArray['Testingmethod']['selected']);
					}
				}

				$Testingmethods = array_unique($Testingmethods);
				if(!isset($SearchFormData[$Model])) break;
				if(!isset($SearchFormData[$Model][$field])) break;
				if(count($Testingmethods) == 0) break;
				if(!is_array($SearchFormData[$Model][$field]) && isset($SearchFormData[$Model][$field]) && $SearchFormData[$Model][$field] != '0') $Testingmethod = array_intersect($Testingmethods,array($SearchFormData[$Model][$field]));
				elseif(is_array($SearchFormData[$Model][$field]) && count($SearchFormData[$Model][$field]) > 0) $Testingmethod = array_intersect($Testingmethods,$SearchFormData[$Model][$field]);
				else $Testingmethod = $Testingmethods;

				if(count($Testingmethod) == 0) break;

				$Option = array();

				$Option['conditions']['id'] = $Testingmethod;
				$Option['order'] = array('verfahren');
				$Option['fields'] = array('id','id');

				$Result = $this->_controller->Testingmethod->find('list',$Option);

				$FoundetIDs[$Model]['result'][] = $Result;


				break;

				case 'Testingcomp':

				$Testingcomps = $this->_controller->Reportnumber->find('all',array('fields' => array('DISTINCT testingcomp_id'),'conditions' => array('Reportnumber.topproject_id' => $projectID)));
				$Limitation = Hash::extract($Testingcomps, '{n}.Reportnumber.testingcomp_id');
				$Testingcomps = $this->_controller->Testingcomp->find('list',array('fields' => array('id'),'order' => array('name'),'conditions' => array('Testingcomp.id' => $Limitation)));

				if(isset($SearchFormData[$Model][$field]) && $SearchFormData[$Model][$field] != '0') $Testingcomps = array_intersect($Testingcomps, array($SearchFormData[$Model][$field]));

				$FoundetIDs[$Model]['result'][] = $Testingcomps;

				break;

				case 'Report':

				$ReportsTopproject = $this->_controller->Data->BelongsToManySelected($Topproject,'Topproject','Report',array('ReportsTopprojects','report_id','topproject_id'));

				$Option = array();
				$Option['fields'] = array('id','id');
				$Option['conditions'][$Model.'.id'] = $ReportsTopproject['Report']['selected'];

				if(isset($SearchFormData[$Model][$field]) && $SearchFormData[$Model][$field] != '0') $Option['conditions'][$Model.'.'.$field] = $SearchFormData[$Model][$field];

				$Result = $this->_controller->Report->find('list',$Option);
				$FoundetIDs[$Model]['result'][] = $Result;

				break;

				case 'Reportnumber':

				$Option = array();
				$Option['fields'] = array('id','id');
				$Option['conditions'][$Model.'.delete'] = 0;
				$Option['conditions'][$Model.'.topproject_id'] = $projectID;
				if(count($ChildrenList) > 0)$Option['conditions'][$Model.'.cascade_id'] = $ChildrenList;

				if(isset($SearchFormData[$Model][$field]) && $SearchFormData[$Model][$field] != '0') $Option['conditions'][$Model.'.'.$field] = $SearchFormData[$Model][$field];
	
				if($field == 'date_of_test'){

					unset($Option['conditions'][$Model.'.'.$field]);

					if(isset($SearchFormData[$Model][$field]['start']) && !empty($SearchFormData[$Model][$field]['start'])){

						$Start = $this->__ReConfigDateFormate($SearchFormData[$Model][$field]['start'],'isotime');

						$Start = $Start . ' 00:00:01';

						$Option['conditions'][$Model.'.'.$field.' >'] = $Start;

					}
					if(isset($SearchFormData[$Model][$field]['end']) && !empty($SearchFormData[$Model][$field]['end'])){

						$End = $this->__ReConfigDateFormate($SearchFormData[$Model][$field]['end'],'isotime');

						$End = $End  . ' 23:59:59';
						$Option['conditions'][$Model.'.'.$field.' <'] = $End;
					}



				}

				$Result = $this->_controller->Reportnumber->find('list',$Option);

				$FoundetIDs[$Model]['result'][] = $Result;

				break;
			}
		}

		// das Array wird bereinigt
		// bei Arrays mit mehreren Elementen
		// wird die Schnittmenge gebildet
		foreach($FoundetIDs as $_key => $_data){
			if(count($_data['result']) == 0) unset($FoundetIDs[$_key]);
			if(count($_data['result']) == 1){
				unset($FoundetIDs[$_key]['result'][0]);
				$FoundetIDs[$_key]['result'] = $_data['result'][0];
			}
			if(count($_data['result']) > 1){
				unset($FoundetIDs[$_key]['result']);
				$FoundetIDs[$_key]['result'] = call_user_func_array('array_intersect', $_data['result']);

			}
		}

		// Wenn keine Prüfmethoden gesucht werden
		// werden alle Prüfmethoden der Kaskade geholt

		if(isset($FoundetIDs['Testingmethod'])){
			$Option = array();
			$Option['fields'] = array('id','order_id');
			$Option['group'] = array('order_id');
			$Option['conditions']['Reportnumber.delete'] = 0;
			$Option['conditions']['Reportnumber.topproject_id'] = $projectID;
			$Option['conditions']['Reportnumber.cascade_id'] = $ChildrenList;
			$Option['conditions']['Reportnumber.testingmethod_id'] = $FoundetIDs['Testingmethod']['result'];
			$Result = $this->_controller->Reportnumber->find('list',$Option);
			if(count($Result) > 0){
				if(isset($FoundetIDs['Order']['result'])){
					$FoundetIDs['Order']['result'] = array_intersect($FoundetIDs['Order']['result'], $Result);
				}
				if(isset($FoundetIDs['Reportnumber']['result'])){
					$Option['fields'] = array('id','id');
					$Option['group'] = array('id');
					$Result = $this->_controller->Reportnumber->find('list',$Option);
					$FoundetIDs['Reportnumber']['result'] = array_intersect($FoundetIDs['Reportnumber']['result'], $Result);
				}
			}
		}

		// Wenn mit den Suchoptionen Prüfberichte gefunden wurden
		// werden anhand derer, die OrderIds geholt
		if(isset($FoundetIDs['Reportnumber'])){
			$Option = array();
			$Option['fields'] = array('id','order_id');
			$Option['group'] = array('order_id');
			$Option['conditions']['Reportnumber.delete'] = 0;
			$Option['conditions']['Reportnumber.topproject_id'] = $projectID;
			$Option['conditions']['Reportnumber.cascade_id'] = $ChildrenList;
			$Option['conditions']['Reportnumber.id'] = $FoundetIDs['Reportnumber']['result'];
			$Result = $this->_controller->Reportnumber->find('list',$Option);

			if(count($Result) > 0 && isset($FoundetIDs['Order'])){

				// Wenn Orderids gefunden wurden, muss überprüft werden ob unterhalb Berichte erstellt wurden
				// wenn nicht wird der Reportcount auf null gesetzt
				if(isset($FoundetIDs['Order']) && isset($FoundetIDs['Order']['result']) && count($FoundetIDs['Order']['result']) > 0){
					$Option = array();
					$Option['conditions']['Reportnumber.delete'] = 0;
					$Option['conditions']['Reportnumber.topproject_id'] = $projectID;
					$Option['conditions']['Reportnumber.cascade_id'] = $ChildrenList;
					$Option['conditions']['Reportnumber.order_id'] = $FoundetIDs['Order']['result'];
					$ResultTest = $this->_controller->Reportnumber->find('list',$Option);

					if(count($ResultTest) == 0){
						$HasReports = false;
						$Result = array();
						$FoundetIDs['Reportnumber']['result'] = array();
					} else {
						$HasReports = true;
					}
				}

				if(isset($HasReports) && $HasReports === true){
//					$FoundetIDs['Order']['result'] = array_intersect($FoundetIDs['Order']['result'], $Result);
				} else {
					$FoundetIDs['Reportnumber']['result'] = array();
				}
			}
		}

		// Das Array für die Dropdownfelder befüllen
		foreach($fields->fields->children() as $_key => $_fields){
			if(empty($_fields->fieldtype)) continue;

			$Model = trim($_fields->model);
			$field_camelize = Inflector::camelize(trim($_fields->key));
			$field = trim($_fields->option);
			$Field = Inflector::camelize($field);
			$Limitation = array();
			$Option = array();

			if(!isset($FoundetIDs[$Model]['result'])) continue;
			if(count($FoundetIDs[$Model]['result']) == 0) continue;

			switch ($Model) {
				case 'Topproject':

				if(trim($_fields->fieldtype) != 'dropdown') break;

				$Fields = array(trim($_fields->option),trim($_fields->output));
				if($modus == 'result') $Fields = array(trim($_fields->option),trim($_fields->option));
				$Option = array(
											'fields' => $Fields,
											'conditions'=>array(
												'Topproject.id' => $FoundetIDs[$Model]['result']
												)
											);


				$Topprojects = $this->_controller->Topproject->find('list',$Option);
				if(!empty($_fields->description->$local)) $Values[$Model][$Field]['description'] = trim($_fields->description->$local);
				else $Values[$Model][$Field]['description'] = __('no value',true);

				if($modus == 'update') foreach($Topprojects as $_key => $_Result) $FinalForJson[] = array($_key => $_Result);
				else $FinalForJson = $Topprojects;

				$Values[$Model][$Field]['value'] = $FinalForJson;
				$Values[$Model][$Field]['selected'] = $projectID;

				$Values[$Model][$Field]['fieldtype'] = 'dropdown';

				$FinalForJson = array();
				$Option = array();

				break;

				case 'Cascade':

				if(trim($_fields->fieldtype) != 'dropdown'  && trim($_fields->fieldtype) != 'multiselect') break;

				$Option['fields'] = array(trim($_fields->option),trim($_fields->output));
				if($modus == 'result') $Option['fields'] = array(trim($_fields->option),trim($_fields->option));

				$Option['group'] = array(trim($_fields->output));
				$Option['conditions']['id'] = $FoundetIDs[$Model]['result'];

				$Result = $this->_controller->Cascade->find('list',$Option);
				$CascadeTree = $this->_controller->Cascade->find('all',array('order' => array('level ASC','discription'), 'conditions' => array('Cascade.topproject_id' => $projectID)));
				$FirstCascadeId = $CascadeTree[0]['Cascade']['id'];
				$FirstCascadeDiscription = $CascadeTree[0]['Cascade']['discription'];
				$CascadeTree = Hash::combine($CascadeTree, '{n}.Cascade.id', '{n}.Cascade');

				$CascadesDropdownList = $this->GetCascadesDropdownList($CascadeTree,$FirstCascadeId,array($FirstCascadeId => $FirstCascadeDiscription));

				$CascadesParent = array();

				foreach($Result as $_key => $_Result){
					$CascadesParentList = $this->GetCascadesParentList($CascadeTree,$_key);
					foreach($CascadesParentList as $_key => $_CascadesParentList){
						$CascadesParent[$_key] = $_CascadesParentList;
					}
				}

				$CascadesParentFinal = array_intersect($CascadesDropdownList, $CascadesParent);

				// Der Schritt ist notwendig da das Json sonst vom Browser nach der ID sortiert wird, so ein scheiß
				$FinalForJson = array();

				if($modus == 'update') foreach($CascadesParentFinal as $_key => $_Result) $FinalForJson[] = array($_key => $_Result);
				else $FinalForJson = $CascadesParentFinal;

				if($modus == 'result'){
					$FinalForJson = array();
					foreach($CascadesParentFinal as $_key => $_Result){
						 $FinalForJson[$_key] = $_key;
					}
				}

				if(isset($SearchFormData['Cascade'])){
					if(isset($SearchFormData['Cascade']['id'])){
						$Values[$Model][$Field]['selected'] = $SearchFormData['Cascade']['id'];
					} else {
						$Values[$Model][$Field]['selected'] = 0;
					}
				} else {
					$Values[$Model][$Field]['selected'] = 0;
				}

				$Values[$Model][$Field]['value'] = $FinalForJson;

				if(trim($_fields->fieldtype) == 'dropdown') $Values[$Model][$Field]['fieldtype'] = 'dropdown';
				if(trim($_fields->fieldtype) == 'multiselect') $Values[$Model][$Field]['fieldtype'] = 'multiselect';

				$FinalForJson = array();
				$Option = array();

				break;

				case 'Testingcomp':

				if(trim($_fields->fieldtype) != 'dropdown'  && trim($_fields->fieldtype) != 'multiselect') break;

				if(!empty($_fields->description->$local)) $Values[$Model][$field_camelize]['description'] = trim($_fields->description->$local);
				else $Values[$Model][$field_camelize]['description'] = __('no value',true);

				$Option['order'] = array('Testingcomp.' . trim($_fields->output));
				$Option['conditions'] = array('Testingcomp.id' => $FoundetIDs[$Model]['result']);
				$Result = $this->_controller->Testingcomp->find('list',$Option);

				$FinalForJson[0] = array(0 => '');

				if($modus == 'update') foreach($Result as $_key => $_Result) $FinalForJson[] = array($_key => $_Result);
				else $FinalForJson = $Result;

				$Values[$Model][$field_camelize]['value'] = $FinalForJson;

				if(trim($_fields->fieldtype) == 'dropdown') $Values[$Model][$field_camelize]['fieldtype'] = 'dropdown';
				if(trim($_fields->fieldtype) == 'multiselect') $Values[$Model][$field_camelize]['fieldtype'] = 'multiselect';

				$Values[$Model][$field_camelize]['selected'] = 0;

				if(isset($SearchFormData[$Model][$field]) && $SearchFormData[$Model][$field] != '0') $Values[$Model][$field_camelize]['selected'] =$SearchFormData[$Model][$field];

				$FinalForJson = array();
				$Option = array();

				break;

				case 'Report':

				if(trim($_fields->fieldtype) != 'dropdown'  && trim($_fields->fieldtype) != 'multiselect') break;

				if(!empty($_fields->description->$local)) $Values[$Model][$field_camelize]['description'] = trim($_fields->description->$local);
				else $Values[$Model][$field_camelize]['description'] = __('no value',true);

				$Option['fields'] = array(trim($_fields->option),trim($_fields->output));
				$Option['conditions'] = array('Report.id' => $FoundetIDs[$Model]['result']);

				$Result = $this->_controller->Report->find('list',$Option);

//				$FoundetOrderIDs[$Model] = $Result;

				$FinalForJson[0] = array(0 => '');

				if($modus == 'update') foreach($Result as $_key => $_Result) $FinalForJson[] = array($_key => $_Result);
				else $FinalForJson = $Result;
				$Values[$Model][$field_camelize]['value'] = $FinalForJson;

				if(trim($_fields->fieldtype) == 'dropdown') $Values[$Model][$field_camelize]['fieldtype'] = 'dropdown';
				if(trim($_fields->fieldtype) == 'multiselect') $Values[$Model][$field_camelize]['fieldtype'] = 'multiselect';

				$Values[$Model][$field_camelize]['selected'] = 0;

				if(isset($SearchFormData[$Model][$field]) && $SearchFormData[$Model][$field] != '0') $Values[$Model][$field_camelize]['selected'] =$SearchFormData[$Model][$field];

				$FinalForJson = array();
				$Option = array();

				break;

				case 'Testingmethod':

				if(trim($_fields->fieldtype) != 'dropdown'  && trim($_fields->fieldtype) != 'multiselect') break;

				if(!empty($_fields->description->$local)) $Values[$Model][$field_camelize]['description'] = trim($_fields->description->$local);
				else $Values[$Model][$field_camelize]['description'] = __('no value',true);

				$Option['fields'] = array(trim($_fields->option),trim($_fields->output));
				if($modus == 'result') $Option['fields'] = array(trim($_fields->option),trim($_fields->option));

				$Option['group'] = array(trim($_fields->output));

				$Option['conditions'] = array('Testingmethod.id' => $FoundetIDs[$Model]['result']);
				$Result = $this->_controller->Testingmethod->find('list',$Option);

//				$FoundetOrderIDs[$Model] = $Result;

				$FinalForJson[0] = array(0 => '');

				if($modus == 'update') foreach($Result as $_key => $_Result) $FinalForJson[] = array($_key => $_Result);
				else $FinalForJson = $Result;

				$Values[$Model][$field_camelize]['value'] = $FinalForJson;

				if(trim($_fields->fieldtype) == 'dropdown') $Values[$Model][$field_camelize]['fieldtype'] = 'dropdown';
				if(trim($_fields->fieldtype) == 'multiselect') $Values[$Model][$field_camelize]['fieldtype'] = 'multiselect';

				$Values[$Model][$field_camelize]['selected'] = 0;

				if(isset($SearchFormData[$Model][$field]) && $SearchFormData[$Model][$field] != '0') $Values[$Model][$field_camelize]['selected'] =$SearchFormData[$Model][$field];

				$FinalForJson = array();
				$Option = array();

				break;

				case 'Reportnumber':

				if(!empty($_fields->description->$local)) $Values[$Model][$field_camelize]['description'] = trim($_fields->description->$local);
				else $Values[$Model][$field_camelize]['description'] = __('no value',true);

				if(trim($_fields->fieldtype) == 'date'){
					if($field == 'date_of_test'){

						$Option['fields'] = array(trim($_fields->output));
						$Option['group'] = array(trim($_fields->output));
						$Option['conditions'] = array(
							'Reportnumber.id' => $FoundetIDs[$Model]['result'],
							'Reportnumber.' . trim($_fields->output) . ' !=' => '',
							'Reportnumber.date_of_test !=' => '1970-01-01 00:00:00',
						);

						$Option['order'] = array('date_of_test ASC');
						$Option['limit'] = 1;
						
						$Result = $this->_controller->Reportnumber->find('list',$Option);

						$Values[$Model][$Field]['start_timestamp'] = $this->__ConfigDateFormate($Result[key($Result)],'timestamp');
						$Values[$Model][$Field]['start'] = $this->__ConfigDateFormate($Result[key($Result)],'date');

						$Option['order'] = array('date_of_test DESC');
						$Option['limit'] = 1;

						$Result = $this->_controller->Reportnumber->find('list',$Option);

						$Values[$Model][$Field]['end_timestamp'] = $this->__ConfigDateFormate($Result[key($Result)],'timestamp');
						$Values[$Model][$Field]['end'] = $this->__ConfigDateFormate($Result[key($Result)],'date');

						$Values[$Model][$Field]['fieldtype'] = 'date';

						if(isset($SearchFormData[$Model][$field]['start'])){
							if($SearchFormData[$Model][$field]['start'] != 0 && !empty($SearchFormData[$Model][$field]['start'])) $Values[$Model][$Field]['start'] = $SearchFormData[$Model][$field]['start'];
						}
						if(isset($SearchFormData[$Model][$field]['end'])){
							if($SearchFormData[$Model][$field]['end'] != 0 && !empty($SearchFormData[$Model][$field]['end'])) $Values[$Model][$Field]['end'] = $SearchFormData[$Model][$field]['end'];
						}
					}
				}
				if(trim($_fields->fieldtype) == 'dropdown'){

					$Option['fields'] = array(trim($_fields->option),trim($_fields->output));
					if($modus == 'result') $Option['fields'] = array(trim($_fields->option),trim($_fields->option));

					$Option['group'] = array(trim($_fields->output));

					$Option['conditions'] = array('Reportnumber.id' => $FoundetIDs[$Model]['result']);

					$Result = $this->_controller->Reportnumber->find('list',$Option);

					if($field_camelize == 'Result'){
						unset($Result);
						$Result[2] = __('Error testingreports',true);
					}
					if($field_camelize == 'RepairFor'){
						unset($Result);
						$Result[2] = __('Repair testingreports',true);
					}

//					$FoundetOrderIDs[$Model] = $Result;

					$FinalForJson[0] = array(0 => '');

					if($modus == 'update') foreach($Result as $_key => $_Result) $FinalForJson[] = array($_key => $_Result);
					else $FinalForJson = $Result;

					$Values[$Model][$field_camelize]['value'] = $FinalForJson;
					$Values[$Model][$field_camelize]['fieldtype'] = 'dropdown';
					$Values[$Model][$field_camelize]['selected'] = 0;

					if(isset($SearchFormData[$Model][$field]) && $SearchFormData[$Model][$field] != '0') $Values[$Model][$field_camelize]['selected'] =$SearchFormData[$Model][$field];
				}

				$FinalForJson = array();
				$Option = array();

				break;
			}
		}

		$CountOrders = 0;

		$this->_controller->Session->write('SearchFormData.Result.Orders',0);

		$Values['Count']['Order'] = 0;

		$Output['Values'] = $Values;
		$Output['FoundetIDs'] = $FoundetIDs['Reportnumber']['result'];

		return $Output;

	}

	public function SearchAdditionalFieldsbyOrder($fields,$SearchFormData,$modus) {

		$Values = false;
		$AllSearchingReportnumbers = array();

		if(!is_object($fields)) return $Values;
		if($this->_controller->request->projectvars['VarsArray'][0] == 0) return $Values;

		$projectID = $this->_controller->request->projectvars['VarsArray'][0];
		$cascadeID = $this->_controller->request->projectvars['VarsArray'][1];
		$orderID = $this->_controller->request->projectvars['VarsArray'][2];
		$reportID = $this->_controller->request->projectvars['VarsArray'][3];

		foreach($fields->fields->children() as $_key => $_fields){

			$Model = trim($_fields->model);
			$field_camelize = Inflector::camelize(trim($_fields->key));
			$field = trim($_fields->option);
			$Field = ucfirst($field);
			$Limitation = array();

			$Option = array();

			if(!isset($SearchFormData[$Model])) continue;
			if(!isset($SearchFormData[$Model][$field])) continue;
			if($SearchFormData[$Model][$field] == '0') continue;

			$Option['fields'] = array('id');
			$Option['conditions']['topproject_id'] = $projectID;
			$Option['conditions']['model'] = $Model;
			$Option['conditions']['field'] = $field;
			$Option['conditions']['id'] = intval($SearchFormData[$Model][$field]);
			$Searching = $this->_controller->Searching->find('first',$Option);

			if(count($Searching) != 1) continue;

			$Option = array();
			$Option['fields'] = array('reportnumber_id');
			$Option['conditions']['searching_id'] = intval($SearchFormData[$Model][$field]);

			$SearchingValue = $this->_controller->Searching->SearchingValue->find('list',$Option);

			$AllSearchingReportnumbers[] = 	$SearchingValue;

			switch ($Model) {
				case 'Generally':
				break;

				case 'Specific':
				break;

				case 'Evaluation':
				break;

			}
		}

		if(count($AllSearchingReportnumbers) == 0) return $Values;
 		if(count($AllSearchingReportnumbers) == 1) $AllSearchingValuesIDs = $AllSearchingReportnumbers[0];
 		if(count($AllSearchingReportnumbers) > 1) $AllSearchingValuesIDs = call_user_func_array('array_intersect', $AllSearchingReportnumbers);

		$Values = $this->_controller->Reportnumber->find('count',array('group' => array('order_id'),'conditions' => array('Reportnumber.id' => $AllSearchingValuesIDs)));

//		return $Values;
		return false;

	}

	public function PutEmptyFields($DropdownValues,$SearchFieldsAdditional) {

		foreach($SearchFieldsAdditional->fields->children() as $_key => $_data){
			$Model = trim($_data->model);
			$Key = Inflector::camelize(trim($_data->key));
			if(!isset($DropdownValues[$Model][$Key])){
				$DropdownValues[$Model][$Key]['value'][0][0] = '';
			}
		}

		return $DropdownValues;
	}

	public function ShowStandardOptionValue($fields,$data) {

		$projectID = $this->_controller->request->projectvars['VarsArray'][0];
		$cascadeID = $this->_controller->request->projectvars['VarsArray'][1];
		$orderID = $this->_controller->request->projectvars['VarsArray'][2];
		$reportID = $this->_controller->request->projectvars['VarsArray'][3];
		$local = $this->_controller->request->local;

		$SearchHistoryOptions = array();

		foreach($data['History'] as $_key => $_data){
			foreach($fields->fields->children() as $__key => $__fields){

				if(empty($__fields->fieldtype)) continue;

				$Model = trim($__fields->model);
				$field_camelize = Inflector::camelize(trim($__fields->key));
				$field = trim($__fields->option);
				$Field = Inflector::camelize($__fields);

				if(isset($_data['Current'][$Model][$field]) && $data['Current'][$Model][$field] != '0'){
					if(is_array($data['Current'][$Model][$field])){
					} else {
					}
					$SearchHistoryOptions[$_key] = date("H:i:s d.m.Y",$_key) . ' ' . trim($__fields->description->$local);
				}
			}
		}
	}

	public function SearchStandardFields($fields,$Options = array(),$modus) {

		$SearchFormData = $this->_controller->Session->read('SearchFormData.Current');
		$Values = array();

		if(!is_object($fields)) return $Values;
		if($this->_controller->request->projectvars['VarsArray'][0] == 0) return $Values;

		$this->_controller->Reportnumber->recursive = -1;
		$Deleted = 0;
//		$DeletedReportsOption = NULL;

		$Option['conditions']['Reportnumber.delete'] = $Deleted;
		$Option['conditions']['Reportnumber.id'] = $Options;

		if(count($Options) > 0){
			$Reportnumbers = $this->_controller->Reportnumber->find('all',$Option);
		}

		unset($Options);

		$projectID = $this->_controller->request->projectvars['VarsArray'][0];
		$cascadeID = $this->_controller->request->projectvars['VarsArray'][1];
		$orderID = $this->_controller->request->projectvars['VarsArray'][2];
		$reportID = $this->_controller->request->projectvars['VarsArray'][3];

		$local = $this->_controller->request->local;

		$this->_controller->Testingcomp->recursive = -1;

		$this->_controller->Topproject->recursive = 1;
		$Topproject = $this->_controller->Topproject->find('first',array('conditions' => array('Topproject.id' => $projectID)));
		$Topproject = $this->_controller->Data->BelongsToManySelected($Topproject,'Topproject','Report',array('ReportsTopprojects','report_id','topproject_id'));

		foreach($fields->fields->children() as $_key => $_fields){

			if(empty($_fields->fieldtype)) continue;

			$Model = trim($_fields->model);
			$field_camelize = Inflector::camelize(trim($_fields->key));
			$field = trim($_fields->option);
//			$Field = ucfirst($field);
			$Field = Inflector::camelize($field);

			$ConditionsTopprojects = $this->_controller->Autorisierung->ConditionsTopprojects();

			$Limitation = array();

			switch ($Model) {
				case 'Topproject':

				$ConditionsTopprojects = array(
											'fields' => array('id','projektname'),
											'conditions'=>array(
												'Topproject.id' => $projectID
//												'Topproject.id' => $ConditionsTopprojects
												),
											'contain' => array('Report','Testingcomp')
											);

				$Topprojects = $this->_controller->Topproject->find('list',$ConditionsTopprojects);

				if(!empty($_fields->description->$local)) $Values[$Model][$Field]['description'] = trim($_fields->description->$local);
				else $Values[$Model][$Field]['description'] = __('no value',true);

				if($modus == 'update') foreach($Topprojects as $_key => $_Result) $FinalForJson[] = array($_key => $_Result);
				else $FinalForJson = $Topprojects;
				$Values[$Model][$Field]['value'] = $FinalForJson;
				$Values[$Model][$Field]['selected'] = $projectID;
				$Values[$Model][$Field]['fieldtype'] = 'dropdown';

				$FinalForJson = array();

				break;

				case 'Report':

				if(!isset($Topproject['Report'])) return $Values;

				$ReportList = $this->_controller->Topproject->Report->find('list',array('fields' => array('id','name'), 'conditions' => array('Report.id' => $Topproject['Report']['selected'])));
				$ReportList[0] = '';
				ksort($ReportList);

				if($modus == 'update') foreach($ReportList as $_key => $_Result) $FinalForJson[] = array($_key => $_Result);
				else $FinalForJson = $ReportList;

				if(!empty($_fields->description->$local)) $Values[$Model][$Field]['description'] = trim($_fields->description->$local);
				else $Values[$Model][$Field]['description'] = __('no value',true);

				$Values[$Model][$Field]['value'] = $FinalForJson;
				$Values[$Model][$Field]['selected'] = 0;
				$Values[$Model][$Field]['fieldtype'] = 'dropdown';

				if(count($Topproject['Report']) == 0) $Values[$Model][$Field]['value'] = array();

				$FinalForJson = array();

				break;

				case 'Testingmethod':

				$Topproject = $this->_controller->Data->BelongsToManySelected($Topproject,'Topproject','Report',array('ReportsTopprojects','report_id','topproject_id'));

				if(count($Topproject['Report']['selected']) == 0) return;

				$Testingmethods = array();

				foreach($Topproject['Report']['selected'] as $_key => $_value){
					$HelpArray['Report']['id'] = $_value;
					$HelpArray = $this->_controller->Data->BelongsToManySelected($HelpArray,'Report','Testingmethod',array('TestingmethodsReports','testingmethod_id','report_id'));
					if(count($HelpArray['Testingmethod']['selected']) > 0){
						$Testingmethods = array_merge($Testingmethods, $HelpArray['Testingmethod']['selected']);
					}
				}

				$Testingmethods = array_unique($Testingmethods);

				if(count($Testingmethods) == 0) return $Values;

				$TestingmethodList = array();

				$TestingmethodList = $this->_controller->Testingmethod->find('list',array('order' => array('verfahren'),'fields' => array('id','verfahren'),'conditions' => array('Testingmethod.id' => $Testingmethods)));

				if(!empty($_fields->description->$local)) $Values[$Model][$Field]['description'] = trim($_fields->description->$local);
				else $Values[$Model][$Field]['description'] = __('no value',true);

				if(count($TestingmethodList) > 1){
					$TestingmethodList[0] = '';
					asort($TestingmethodList);
				}

				if($modus == 'update') foreach($TestingmethodList as $_key => $_Result) $FinalForJson[] = array($_key => $_Result);
				else $FinalForJson = $TestingmethodList;

				$Values[$Model][$Field]['value'] = $FinalForJson;

				$Values[$Model][$Field]['selected'] = 0;
				if(isset($this->_controller->request->data[$Model][$field]) && $this->_controller->request->data[$Model][$field] != '0') {
					$Values[$Model][$Field]['selected'] = $this->_controller->request->data[$Model][$field];
				}

				$Values[$Model][$Field]['fieldtype'] = 'dropdown';

				$FinalForJson = array();

				break;

				case 'Testingcomp':

				$Testingcomps = $this->_controller->Reportnumber->find('all',array('fields' => array('DISTINCT testingcomp_id'),'conditions' => array('Reportnumber.topproject_id' => $projectID)));
				$Limitation = Hash::extract($Testingcomps, '{n}.Reportnumber.testingcomp_id');
				$Testingcomps = $this->_controller->Testingcomp->find('list',array('fields' => array('id','name'),'order' => array('name'),'conditions' => array('Testingcomp.id' => $Limitation)));

				if(count($Testingcomps) > 1){
					$Testingcomps[0] = '';
					asort($Testingcomps);
				}

				if($modus == 'update') foreach($Testingcomps as $_key => $_Result) $FinalForJson[] = array($_key => $_Result);
				else $FinalForJson = $Testingcomps;

				if(!empty($_fields->description->$local)) $Values[$Model][$Field]['description'] = trim($_fields->description->$local);
				else $Values[$Model][$Field]['description'] = __('no value',true);

				$Values[$Model][$Field]['value'] = $FinalForJson;

				$Values[$Model][$Field]['selected'] = 0;
				if(isset($this->_controller->request->data[$Model][$field]) && $this->_controller->request->data[$Model][$field] != '0') {
					$Values[$Model][$Field]['selected'] = $this->_controller->request->data[$Model][$field];
				}

				$Values[$Model][$Field]['fieldtype'] = 'dropdown';

				$FinalForJson = array();

				break;

				case 'Reportnumber':

				$Option = array();
				$Option['conditions']['topproject_id'] = $projectID;
				$Option['conditions']['delete'] = $Deleted;
				$Option['conditions']['not'] = array('Reportnumber.' . $field => null);
				$Option['conditions']['not'] = array('Reportnumber.' . $field => '1970-01-01 01:00:00');
				$Option['fields'] = array(trim($_fields->output));

				if(!empty($_fields->description->$local)) $Values[$Model][$Field]['description'] = trim($_fields->description->$local);
				else $Values[$Model][$Field]['description'] = __('no value',true);

				if(trim($_fields->fieldtype) == 'date'){

					if(isset($Reportnumbers) && count($Reportnumbers) > 0){
						$Limitation = Hash::extract($Reportnumbers, '{n}.Reportnumber.' . trim($_fields->option));
						if(count($Limitation) > 0){
							$Limitation = array_unique($Limitation);
							$Option['conditions'][trim($_fields->option)] = $Limitation;
						}
					}

					$Option['order'] = array('Reportnumber.'.trim($_fields->output).' DESC');
					$ResultLast = $this->_controller->Reportnumber->find('first',$Option);
					$Option['order'] = array('Reportnumber.'.trim($_fields->output).' ASC');
					$ResultFirst = $this->_controller->Reportnumber->find('first',$Option);

					if(count($ResultFirst)){
						$Values[$Model][$Field]['start_timestamp'] = $this->__ConfigDateFormate($ResultFirst['Reportnumber'][trim($_fields->output)],'timestamp');
						$Values[$Model][$Field]['start'] = $this->__ConfigDateFormate($ResultFirst['Reportnumber'][trim($_fields->output)],'date');
					}
					if(count($ResultLast)){
						$Values[$Model][$Field]['end_timestamp'] = $this->__ConfigDateFormate($ResultLast['Reportnumber'][trim($_fields->output)],'timestamp');
						$Values[$Model][$Field]['end'] = $this->__ConfigDateFormate($ResultLast['Reportnumber'][trim($_fields->output)],'date');
					}

					$Values[$Model][$Field]['fieldtype'] = 'date';

				}
				if(trim($_fields->fieldtype) == 'dropdown'){

					$Option['fields'] = array(trim($_fields->option),trim($_fields->output));
					$Option['group'] = array(trim($_fields->output));
					$Option['order'] = array(trim($_fields->output) . ' DESC');

					if(isset($Reportnumbers) && count($Reportnumbers) > 0){
						$Limitation = Hash::extract($Reportnumbers, '{n}.Reportnumber.' . trim($_fields->option));
						if(count($Limitation) > 0){
							$Limitation = array_unique($Limitation);
							$Option['conditions'][trim($_fields->option)] = $Limitation;
						}
					}

					$Result = $this->_controller->Reportnumber->find('list',$Option);

					if($field == 'result'){
						unset($Result);
//						$Result[1] = __('Success testingreports',true);
						$Result[2] = __('Error testingreports',true);
					}

					if($field_camelize == 'RepairFor'){
						unset($Result);
						$Result[2] = __('Repair testingreports',true);
					}
/*
					if($field == 'status'){
						unset($Result);
						$Result[1] = __('Error testingreports',true);
						$Result[2] = __('Error testingreports',true);
						$Result[3] = __('Error testingreports',true);
					}
*/
					$FinalForJson[0] = array(0 => '');
					$FinalFor[0] = '';

					if($modus == 'update') foreach($Result as $_key => $_Result) $FinalForJson[] = array($_key => $_Result);
					else $FinalForJson = $FinalFor + $Result;

					$Values[$Model][$Field]['value'] = $FinalForJson;

					$Values[$Model][$Field]['selected'] = 0;
					if(isset($this->_controller->request->data[$Model][$field]) && $this->_controller->request->data[$Model][$field] != '0'){
						$Values[$Model][$Field]['selected'] = $this->_controller->request->data[$Model][$field];
					}


					$Values[$Model][$Field]['fieldtype'] = 'dropdown';
				}

				$FinalForJson = array();

				break;

				case 'Cascade':

				$Option = array();
				$this->_controller->Cascade->recursive = -1;
				$Option['conditions']['topproject_id'] = $projectID;
				$Option['conditions']['level >'] = 0;

				if(isset($Reportnumbers) && count($Reportnumbers) > 0){
					$Limitation = Hash::extract($Reportnumbers, '{n}.Reportnumber.cascade_id');
					if(count($Limitation) > 0){
						$Limitation = array_unique($Limitation);
						$Option['conditions']['id'] = $Limitation;
					}
				}

				$Option['fields'] = array(trim($_fields->output));

				if(!empty($_fields->description->$local)) $Values[$Model][$Field]['description'] = trim($_fields->description->$local);
				else $Values[$Model][$Field]['description'] = __('no value',true);

				if(trim($_fields->fieldtype) == 'dropdown'){

					$Option['fields'] = array(trim($_fields->option),trim($_fields->output));
					$Option['group'] = array(trim($_fields->output));
					$Result = $this->_controller->Cascade->find('list',$Option);
					$CascadeTree = $this->_controller->Cascade->find('all',array('order' => array('level ASC','discription'), 'conditions' => array('Cascade.topproject_id' => $projectID)));
					$FirstCascadeId = $CascadeTree[0]['Cascade']['id'];
					$FirstCascadeDiscription = $CascadeTree[0]['Cascade']['discription'];
					$CascadeTree = Hash::combine($CascadeTree, '{n}.Cascade.id', '{n}.Cascade');

					$CascadesDropdownList = $this->GetCascadesDropdownList($CascadeTree,$FirstCascadeId,array($FirstCascadeId => $FirstCascadeDiscription));

					$CascadesParent = array();

					foreach($Result as $_key => $_Result){
						$CascadesParentList = $this->GetCascadesParentList($CascadeTree,$_key);
						foreach($CascadesParentList as $_key => $_CascadesParentList){
							$CascadesParent[$_key] = $_CascadesParentList;
						}
					}

					$CascadesParentFinal = array_intersect($CascadesDropdownList, $CascadesParent);

					// Der Schritt ist notwendig da das Json sonst vom Browser nach der ID sortiert wird, so ein scheiß
					$FinalForJson = array();

					if($modus == 'update') foreach($CascadesParentFinal as $_key => $_Result) $FinalForJson[] = array($_key => $_Result);
					else $FinalForJson = $CascadesParentFinal;

					$Values[$Model][$Field]['value'] = $FinalForJson;

					$Values[$Model][$Field]['selected'] = 0;
					if(isset($this->_controller->request->data[$Model][$field]) && $this->_controller->request->data[$Model][$field] != '0'){
						$Values[$Model][$Field]['selected'] = $this->_controller->request->data[$Model][$field];
					}

					$Values[$Model][$Field]['fieldtype'] = 'dropdown';

				}

				$FinalForJson = array();

				break;
				case 'Order':

				$Option = array();
				$this->_controller->Order->recursive = -1;

				$OptionTestingcomp['conditions']['testingcomp_id'] = AuthComponent::user('testingcomp_id');
				$OptionTestingcomp['group'] = array('order_id');
				$OptionTestingcomp['fields'] = array('order_id','order_id');
				$OrdersTestingcomps = $this->_controller->Order->OrdersTestingcomp->find('list',$OptionTestingcomp);

				$Option['conditions']['topproject_id'] = $projectID;
				$Option['conditions'][trim($_fields->output) . ' !='] = '';
				$Option['conditions']['id'] = $OrdersTestingcomps;

				if(isset($Reportnumbers) && count($Reportnumbers) > 0){
					$OrdersExist = Hash::extract($Reportnumbers, '{n}.Reportnumber[order_id>0].order_id');
					$Limitation = Hash::extract($Reportnumbers, '{n}.Reportnumber.order_id');
					if(count($Limitation) > 0){
						$Limitation = array_unique($Limitation);
						$Option['conditions']['id'] = $Limitation;
					}
				}

				$Option['fields'] = array(trim($_fields->output));

				if(!empty($_fields->description->$local)) $Values[$Model][$Field]['description'] = trim($_fields->description->$local);
				else $Values[$Model][$Field]['description'] = __('no value',true);

				if(trim($_fields->fieldtype) == 'dropdown' || trim($_fields->fieldtype) == 'multiselect'){
					$Option['fields'] = array(trim($_fields->option),trim($_fields->output));
					$Option['group'] = array(trim($_fields->output));
					$Result = $this->_controller->Order->find('list',$Option);

					$FinalForJson[0] = array(0 => '');

					if($modus == 'update') {
						foreach($Result as $_key => $_Result) $FinalForJson[] = array($_key => $_Result);
					} else {
						$ResultEmpty[0] = array('');
						$FinalForJson = array_merge($ResultEmpty[0],$Result);
					}

					$Values[$Model][$Field]['value'] = $FinalForJson;
					$Values[$Model][$Field]['selected'] = 0;

					if(isset($this->_controller->request->data[$Model][$field]) && $this->_controller->request->data[$Model][$field] != '0'){
						$Values[$Model][$Field]['selected'] = $this->_controller->request->data[$Model][$field];
					}

					$Values[$Model][$Field]['fieldtype'] = 'dropdown';

					if(isset($OrdersExist) && count($OrdersExist) == 0) $Values[$Model][$Field]['disabled'] = 'disabled';;
				}

				$FinalForJson = array();

				break;
			}

		}

		return $Values;
	}

	public function GetCascadesParentList($CascadeTree,$key) {

		$output = array();

		$data = Hash::extract($CascadeTree, '{n}[id='.$key.']');

		if(count($data) > 0){
			foreach($data as $_key => $_data){

				$level = $_data['level'] * 2;
				$distance = '';
				$distance = str_pad($distance, $level, '-', STR_PAD_LEFT );

				$output[$_data['id']] = $distance . $_data['discription'];
//				$output[$_data['id']] = '<span>' . $distance . '</span>' . $_data['discription'];

				$output_2 = $this->GetCascadesParentList($CascadeTree,$_data['parent']);
				if(count($output_2) > 0){
					foreach($output_2 as $__key => $__output_2){
						$output[$__key] = $__output_2;
					}
				}
			}
		}

		return $output;
	}

	public function GetCascadesDropdownList($CascadeTree,$key,$output) {

		if(count($output) == 0) $output = array();

		$data = Hash::extract($CascadeTree, '{n}[parent='.$key.']');

		$FromTestingcomp = $this->_controller->Navigation->CascadeGetFromTestingcomp();

		if(count($FromTestingcomp) == 0) $FromTestingcomp = $this->_controller->Navigation->CascadeGetFrom($CascadeTree);

		foreach($data as $_key => $_data){

			if(!isset($FromTestingcomp[$_data['id']])) continue;

			$level = $_data['level'] * 2;
			$distance = '';
			$distance = str_pad($distance, $level, '-', STR_PAD_LEFT );

			$output[$_data['id']] = $distance . $_data['discription'];
//			$output[$_data['id']] = '<span>' . $distance . '<span>' . $_data['discription'];

			$output_2 = $this->GetCascadesDropdownList($CascadeTree,$_data['id'],array());

			if(count($output_2) > 0){
				foreach($output_2 as $__key => $__output_2){
					$output[$__key] = $__output_2;
				}
			}
		}

		return $output;
	}

	public function SelectArrays() {
		// Suchfelder definieren
		$toSearchFields = array('order_number','bestellung','block','auftrags_nr','bauteil','postition','kks','werkstoff','abmessungen','ausfuehrende_firma','hoehenangabe','kesselseite','termin');
		$searchFields = array();
		$model = $this->_controller->modelClass;

		$this->_controller->$model->recursive = -1;

		foreach($toSearchFields as $_toSearchFields) {
			$searchFields[$_toSearchFields][0] = 'Wert wählen';
			$search = ($this->_controller->$model->find('all', array('fields' => array('DISTINCT '.$model.'.'.$_toSearchFields))));
			if(count($search > 0)) {
				foreach($search as $_search){
					foreach($_search as $__search){
						$searchFields[key($__search)][] = $__search[key($__search)];
					}
				}
			}
		}

		$this->_controller->Session->write('searchFields', $searchFields);
	return $searchFields;
	}

	public function LineForInvoice($thisOrder) {

				$line[0] = array(
						'0' => array(
							'width' => 50,
							'height' => 7,
							'border' => array('LTRB' => array('width' => 0.25, 'color' => array(0, 0, 0))),
							'text' => "Auftraggeber",
							'fill' => true,
							'textcolor' => array(255, 255, 255)
							),
						'1' => array(
							'width' => 50,
							'height' => 7,
							'border' => array('LTRB' => array('width' => 0.25, 'color' => array(0, 0, 0))),
							'text' => "Auftragnehmer / Firma",
							'fill' => true,
							'textcolor' => array(255, 255, 255)
							),
						'2' => array(
							'width' => 100,
							'height' => 7,
							'border' => array('LTRB' => array('width' => 0.25, 'color' => array(0, 0, 0))),
							'text' => "Verantwortlicher des Arbeitnehmers",
							'fill' => true,
							'textcolor' => array(255, 255, 255)
							),
						'3' => array(
							'width' => 60,
							'height' => 7,
							'border' => array('LTRB' => array('width' => 0.25, 'color' => array(0, 0, 0))),
							'text' => "Bestell Nr.",
							'fill' => true,
							'textcolor' => array(255, 255, 255)
							),
						'4' => array(
							'width' => 15,
							'height' => 7,
							'border' => array('LTRB' => array('width' => 0.25, 'color' => array(0, 0, 0))),
							'text' => "Blatt",
							'fill' => true,
							'textcolor' => array(255, 255, 255)
							),
						);
				$line[1] = array(
						'0' => array(
							'width' => 50,
							'height' => 7,
							'border' => array('LTRB' => array('width' => 0.25, 'color' => array(0, 0, 0))),
							'text' => "Betroffene Anlage",
							'fill' => false,
							'textcolor' => array(0, 0, 0)
							),
						'1' => array(
							'width' => 50,
							'height' => 7,
							'border' => array('LTRB' => array('width' => 0.25, 'color' => array(0, 0, 0))),
							'text' => "Auftragnehmer",
							'fill' => false,
							'textcolor' => array(0, 0, 0)
							),
						'2' => array(
							'width' => 100,
							'height' => 7,
							'border' => array('LTRB' => array('width' => 0.25, 'color' => array(0, 0, 0))),
							'text' => "",
							'fill' => false,
							'textcolor' => array(0, 0, 0)
							),
						'3' => array(
							'width' => 60,
							'height' => 7,
							'border' => array('LTRB' => array('width' => 0.25, 'color' => array(0, 0, 0))),
							'text' => $thisOrder['Order']['bestellung'],
							'fill' => false,
							'textcolor' => array(0, 0, 0)
							),
						'4' => array(
							'width' => 15,
							'height' => 7,
							'border' => array('LTRB' => array('width' => 0.25, 'color' => array(0, 0, 0))),
							'text' => "Blatt",
							'fill' => false,
							'textcolor' => array(0, 0, 0)
							),
						);
				$line[3] = array(
						'0' => array(
							'width' => 50,
							'height' => 7,
							'border' => array('LTRB' => array('width' => 0.25, 'color' => array(0, 0, 0))),
							'text' => "Gewerk",
							'fill' => true,
							'textcolor' => array(255, 255, 255)
							),
						'1' => array(
							'width' => 50,
							'height' => 7,
							'border' => array('LTRB' => array('width' => 0.25, 'color' => array(0, 0, 0))),
							'text' => "zustd. Abteilung",
							'fill' => true,
							'textcolor' => array(255, 255, 255)
							),
						'2' => array(
							'width' => 100,
							'height' => 7,
							'border' => array('LTRB' => array('width' => 0.25, 'color' => array(0, 0, 0))),
							'text' => "Ansprechpartner Tel./Name",
							'fill' => true,
							'textcolor' => array(255, 255, 255)
							),
						'3' => array(
							'width' => 20,
							'height' => 7,
							'border' => array('LTRB' => array('width' => 0.25, 'color' => array(0, 0, 0))),
							'text' => "Lerf Nr.",
							'fill' => true,
							'textcolor' => array(255, 255, 255)
							),
						'4' => array(
							'width' => 40,
							'height' => 7,
							'border' => array('LTRB' => array('width' => 0.25, 'color' => array(0, 0, 0))),
							'text' => "Kostenstelle intern",
							'fill' => true,
							'textcolor' => array(255, 255, 255)
							),
						'5' => array(
							'width' => 15,
							'height' => 7,
							'border' => array('LTRB' => array('width' => 0.25, 'color' => array(0, 0, 0))),
							'text' => "von",
							'fill' => true,
							'textcolor' => array(255, 255, 255)
							),
						);
				$line[4] = array(
						'0' => array(
							'width' => 50,
							'height' => 7,
							'border' => array('LTRB' => array('width' => 0.25, 'color' => array(0, 0, 0))),
							'text' => "ZfP-Dienstleistung",
							'fill' => false,
							'textcolor' => array(0, 0, 0)
							),
						'1' => array(
							'width' => 50,
							'height' => 7,
							'border' => array('LTRB' => array('width' => 0.25, 'color' => array(0, 0, 0))),
							'text' => $thisOrder['Order']['anfordernde_abteilung'],
							'fill' => false,
							'textcolor' => array(0, 0, 0)
							),
						'2' => array(
							'width' => 100,
							'height' => 7,
							'border' => array('LTRB' => array('width' => 0.25, 'color' => array(0, 0, 0))),
							'text' => $thisOrder['Order']['name'].' '.$thisOrder['Order']['telefon_2'],
							'fill' => false,
							'textcolor' => array(0, 0, 0)
							),
						'3' => array(
							'width' => 20,
							'height' => 7,
							'border' => array('LTRB' => array('width' => 0.25, 'color' => array(0, 0, 0))),
							'text' => "",
							'fill' => false,
							'textcolor' => array(0, 0, 0)
							),
						'4' => array(
							'width' => 40,
							'height' => 7,
							'border' => array('LTRB' => array('width' => 0.25, 'color' => array(0, 0, 0))),
							'text' => "",
							'fill' => false,
							'textcolor' => array(0, 0, 0)
							),
						'5' => array(
							'width' => 15,
							'height' => 7,
							'border' => array('LTRB' => array('width' => 0.25, 'color' => array(0, 0, 0))),
							'text' => "",
							'fill' => false,
							'textcolor' => array(0, 0, 0)
							),
						);
				$line[5] = array(
						'0' => array(
							'width' => 50,
							'height' => 7,
							'border' => array('LTRB' => array('width' => 0.25, 'color' => array(0, 0, 0))),
							'text' => "Ausführungszeitraum",
							'fill' => true,
							'textcolor' => array(255, 255, 255)
							),
						'1' => array(
							'width' => 50,
							'height' => 7,
							'border' => array('LTRB' => array('width' => 0.25, 'color' => array(0, 0, 0))),
							'text' => "KKS, Ort",
							'fill' => true,
							'textcolor' => array(255, 255, 255)
							),
						'2' => array(
							'width' => 100,
							'height' => 7,
							'border' => array('LTRB' => array('width' => 0.25, 'color' => array(0, 0, 0))),
							'text' => "Meldungs Nr. / Positions Nr.",
							'fill' => true,
							'textcolor' => array(255, 255, 255)
							),
						'3' => array(
							'width' => 75,
							'height' => 7,
							'border' => array('LTRB' => array('width' => 0.25, 'color' => array(0, 0, 0))),
							'text' => "Auftrags-Nr.",
							'fill' => true,
							'textcolor' => array(255, 255, 255)
							),
						);
				$line[6] = array(
						'0' => array(
							'width' => 50,
							'height' => 7,
							'border' => array('LTRB' => array('width' => 0.25, 'color' => array(0, 0, 0))),
							'text' => "",
							'fill' => false,
							'textcolor' => array(0, 0, 0)
							),
						'1' => array(
							'width' => 50,
							'height' => 7,
							'border' => array('LTRB' => array('width' => 0.25, 'color' => array(0, 0, 0))),
							'text' => $thisOrder['Order']['kks'],
							'fill' => false,
							'textcolor' => array(0, 0, 0)
							),
						'2' => array(
							'width' => 100,
							'height' => 7,
							'border' => array('LTRB' => array('width' => 0.25, 'color' => array(0, 0, 0))),
							'text' => "",
							'fill' => false,
							'textcolor' => array(0, 0, 0)
							),
						'3' => array(
							'width' => 75,
							'height' => 7,
							'border' => array('LTR' => array('width' => 0.25, 'color' => array(0, 0, 0))),
							'text' => "",
							'fill' => false,

							'textcolor' => array(0, 0, 0)
							),
						);
				$line[7] = array(
						'0' => array(
							'width' => 25,
							'height' => 7,
							'border' => array('LTRB' => array('width' => 0.25, 'color' => array(0, 0, 0))),
							'text' => "Revision",
							'fill' => true,
							'textcolor' => array(255, 255, 255)
							),
						'1' => array(
							'width' => 25,
							'height' => 7,
							'border' => array('LTRB' => array('width' => 0.25, 'color' => array(0, 0, 0))),
							'text' => "lfd. Betrieb",
							'fill' => true,
							'textcolor' => array(255, 255, 255)
							),
						'2' => array(
							'width' => 150,
							'height' => 7,
							'border' => array('LTRB' => array('width' => 0.25, 'color' => array(0, 0, 0))),
							'text' => "Beschreibung der Leistungen",
							'fill' => true,
							'textcolor' => array(255, 255, 255)
							),
						'3' => array(
							'width' => 75,
							'height' => 7,
							'border' => array('LR' => array('width' => 0.25, 'color' => array(0, 0, 0))),
							'text' => $thisOrder['Order']['auftrags_nr'],
							'fill' => false,
							'textcolor' => array(0, 0, 0)
							),
						);
				$line[8] = array(
						'0' => array(
							'width' => 25,
							'height' => 7,
							'border' => array('LTRB' => array('width' => 0.25, 'color' => array(0, 0, 0))),
							'text' => $revision,
							'fill' => false,
							'textcolor' => array(0, 0, 0)
							),
						'1' => array(
							'width' => 25,
							'height' => 7,
							'border' => array('LTRB' => array('width' => 0.25, 'color' => array(0, 0, 0))),
							'text' => $laufender_betrieb,
							'fill' => false,
							'textcolor' => array(0, 0, 0)
							),
						'2' => array(
							'width' => 150,
							'height' => 7,
							'border' => array('LTRB' => array('width' => 0.25, 'color' => array(0, 0, 0))),
							'text' => "",
							'fill' => true,
							'textcolor' => array(255, 255, 255)
							),
						'3' => array(
							'width' => 75,
							'height' => 7,
							'border' => array('LRB' => array('width' => 0.25, 'color' => array(0, 0, 0))),
							'text' => "",
							'fill' => false,
							'textcolor' => array(0, 0, 0)
							),
						);
				$line[9] = array(
						'0' => array(
							'width' => 25,
							'height' => 10,
							'border' => array('LTRB' => array('width' => 0.25, 'color' => array(0, 0, 0))),
							'text' => 'LV-Pos',
							'fill' => false,
							'textcolor' => array(0, 0, 0)
							),
						'1' => array(
							'width' => 25,
							'height' => 10,
							'border' => array('LTRB' => array('width' => 0.25, 'color' => array(0, 0, 0))),
							'text' => 'Leistungs Nr.',
							'fill' => false,
							'textcolor' => array(0, 0, 0)
							),
						'2' => array(
							'width' => 150,
							'height' => 10,
							'border' => array('LTRB' => array('width' => 0.25, 'color' => array(0, 0, 0))),
							'text' => $thisOrder['Order']['bauteil'],
							'fill' => false,
							'textcolor' => array(0, 0, 0)
							),
						'3' => array(
							'width' => 16,
							'height' => 10,
							'border' => array('LTRB' => array('width' => 0.25, 'color' => array(0, 0, 0))),
							'text' => "Menge",
							'fill' => false,
							'textcolor' => array(0, 0, 0)
							),
						'4' => array(
							'width' => 16,
							'height' => 10,
							'border' => array('LTRB' => array('width' => 0.25, 'color' => array(0, 0, 0))),
							'text' => "Mengen-\neinheit",
							'fill' => false,
							'textcolor' => array(0, 0, 0)
							),
						'5' => array(
							'width' => 16,
							'height' => 10,
							'border' => array('LTRB' => array('width' => 0.25, 'color' => array(0, 0, 0))),
							'text' => "Einzel-\npreis",
							'fill' => false,
							'textcolor' => array(0, 0, 0)
							),
						'6' => array(
							'width' => 16,
							'height' => 10,
							'border' => array('LTRB' => array('width' => 0.25, 'color' => array(0, 0, 0))),
							'text' => "Summe",
							'fill' => false,
							'textcolor' => array(0, 0, 0)
							),
						'7' => array(
							'width' => 11,
							'height' => 10,
							'border' => array('LTRB' => array('width' => 0.25, 'color' => array(0, 0, 0))),
							'text' => "ge-\nprüft",
							'fill' => false,
							'textcolor' => array(0, 0, 0)
							),
						);
		return $line;
	}

	public function SucheTestdaten($data) {

		$model = $data['Model'];
		$field = $data['Field'];

		$model_array =  explode('_',Inflector::underscore($model));

		$projectID = $this->_controller->request->projectvars['VarsArray'][0];
		$cascadeID = $this->_controller->request->projectvars['VarsArray'][1];
		$orderID = $this->_controller->request->projectvars['VarsArray'][2];
		$reportID = $this->_controller->request->projectvars['VarsArray'][3];

		$this->_controller->Reportnumber->recursive = -1;
		$reportnumber = $this->_controller->Reportnumber->find('list',array('conditions'=>array('Reportnumber.topproject_id' => $projectID)));

		$this->_controller->loadModel($model);

		$model_data = $this->_controller->$model->find('all',array( 'conditions' => array($model.'.reportnumber_id' => $reportnumber), 'fields' => array('reportnumber_id',$field)));
//		$model_data = $this->_controller->$model->find('all',array( 'conditions' => array($model.'.reportnumber_id' => $reportnumber),array('not' => array($model.'.'.$field => '',$model.'.'.$field => NULL)), 'fields' => array('reportnumber_id',$field)));

		unset($reportnumber);

		$output = array();
		$input_search = array();
		$input_search_data = array();
		$numbers = array();

		foreach($model_data as $_key => $_model_data){
			if($_model_data[$model][$field] != ''){
				$output[($_model_data[$model][$field])] = $_model_data[$model]['reportnumber_id'];
				$numbers[strtolower($_model_data[$model][$field])][$_model_data[$model]['reportnumber_id']] = $_model_data[$model]['reportnumber_id'];
			}
		}

		$AllInsertIds = array();
		$AllUpdateIds = array();

		foreach($output as $_key => $_output){
			$reportnumber = $this->_controller->Reportnumber->find('first',array('conditions'=>array('Reportnumber.id' => $_output, 'Reportnumber.topproject_id' => $projectID)));

			if(count($reportnumber) == 0) continue;

			$SearchingData = array(
							'model' => ucfirst($model_array[2]),
							'field' => $field,
							'value' => $_key,
							'topproject_id' => $reportnumber['Reportnumber']['topproject_id']
						);

			$Searching = $this->_controller->Searching->find('first',array(
															'conditions'=>array(
																'Searching.model' => ucfirst($model_array[2]),
																'Searching.field' => $field,
																'Searching.value' => $_key,
																'Searching.topproject_id' => $projectID,
																)
															)
														);



			if(count($Searching) == 0) {
				$this->_controller->Searching->create();
				$this->_controller->Searching->save($SearchingData);
				$AllInsertIds[] = $this->_controller->Searching->getLastInsertID();
			} elseif(count($Searching) > 0){
				$AllUpdateIds[] = $Searching['Searching']['id'];
			}
		}

		$AllIds = array_merge($AllUpdateIds,$AllInsertIds);
		$Searching = $this->_controller->Searching->find('all',array('conditions' => array('Searching.id' => $AllIds)));

		foreach($Searching as $_key => $_Searching){

			if(!isset($numbers[strtolower($_Searching['Searching']['value'])])) continue;

			foreach($numbers[strtolower($_Searching['Searching']['value'])] as $__key => $__numbers){

				$reportnumber = $this->_controller->Reportnumber->find('first',array('conditions'=>array('Reportnumber.id'=>$__numbers,'Reportnumber.topproject_id' => $projectID)));

				if(count($reportnumber) == 0) continue;

				$option_search = array(
							'SearchingValue.searching_id' => $_Searching['Searching']['id'],
							'SearchingValue.reportnumber_id' => $reportnumber['Reportnumber']['id'],
						);
				$SearchingValue = $this->_controller->Searching->SearchingValue->find('first',array('conditions' => $option_search));

				if(count($SearchingValue) == 0){

					$input_search = array(
							'searching_id' => $_Searching['Searching']['id'],
							'reportnumber_id' => $reportnumber['Reportnumber']['id'],
							'topproject_id' => $reportnumber['Reportnumber']['topproject_id'],
							'cascade_id' => $reportnumber['Reportnumber']['cascade_id'],
							'order_id' => $reportnumber['Reportnumber']['order_id'],
							'report_id' => $reportnumber['Reportnumber']['report_id'],
							'testingmethod_id' => $reportnumber['Reportnumber']['testingmethod_id'],
							'testingcomp_id' => $reportnumber['Reportnumber']['testingcomp_id'],
						);
					$this->_controller->Searching->SearchingValue->create();
					$this->_controller->Searching->SearchingValue->save($input_search);
				}
			}
		}
	}

	public function CreateAllEntries($Reportnumber,$Model,$Field,$Value) {

		$ModelArray = explode('_',Inflector::underscore($Model));
		$ModelKind = ucfirst($ModelArray[2]);

		$InputSearching = array(
			'model' => $ModelKind,
			'field' => $Field,
			'value' => $Value,
			'topproject_id' => $Reportnumber['Reportnumber']['topproject_id'],
			'cascade_id' => $Reportnumber['Reportnumber']['cascade_id'],
			'order_id' => $Reportnumber['Reportnumber']['order_id'],
			'report_id' => $Reportnumber['Reportnumber']['report_id'],
			'testingmethod_id' => $Reportnumber['Reportnumber']['testingmethod_id'],
			'testingcomp_id' => $Reportnumber['Reportnumber']['testingcomp_id'],
		);

		$InputSearchingValue = array(
			'reportnumber_id' => $Reportnumber['Reportnumber']['id'],
			'topproject_id' => $Reportnumber['Reportnumber']['topproject_id'],
			'cascade_id' => $Reportnumber['Reportnumber']['cascade_id'],
			'order_id' => $Reportnumber['Reportnumber']['order_id'],
			'report_id' => $Reportnumber['Reportnumber']['report_id'],
			'testingmethod_id' => $Reportnumber['Reportnumber']['testingmethod_id'],
			'testingcomp_id' => $Reportnumber['Reportnumber']['testingcomp_id'],
		);

		// beide Tabellen befüllen
		$this->_controller->Searching->create();

		if($this->_controller->Searching->save($InputSearching)){

			$LastId = $this->_controller->Searching->getLastInsertID();
			$InputSearchingValue['searching_id'] = $LastId;
			$this->_controller->Searching->SearchingValue->create();

			if($this->_controller->Searching->SearchingValue->save($InputSearchingValue)){
				return true;
			} else {
				return false;
			}
		} else {
			return false;
		}
	}

	public function DeleteLinkingEvaluationEntries($Reportnumber,$Searching) {

		$this->_controller->loadModel('Searching');
		$this->_controller->Searching->recursive = -1;
		$this->_controller->Searching->SearchingValue->recursive = -1;

		$SearchingOptions['conditions']['SearchingValue.reportnumber_id'] = $Reportnumber['Reportnumber']['id'];
		$SearchingOptions['fields'] = array('searching_id','searching_id');

		$SearchingValue = $this->_controller->Searching->SearchingValue->find('list',$SearchingOptions);

		unset($SearchingOptions);
		$SearchingOptions['conditions']['Searching.id'] = $SearchingValue;
		$SearchingOptions['conditions']['Searching.model'] = 'Evaluation';

		$Searching = $this->_controller->Searching->find('all',$SearchingOptions);

		unset($SearchingOptions);

		foreach($Searching as $_key => $_data){

			$DeleteOption['SearchingValue.reportnumber_id'] = $Reportnumber['Reportnumber']['id'];
			$DeleteOption['SearchingValue.searching_id'] = $_data['Searching']['id'];

			$this->_controller->Searching->SearchingValue->deleteAll($DeleteOption , false);

			unset($DeleteOption['SearchingValue.reportnumber_id']);

			$DeleteData = $this->_controller->Searching->SearchingValue->find('all',array('conditions' => $DeleteOption));

			if($DeleteData == 0){
				$this->_controller->Searching->delete($_data['Searching']['id']);
			}
		}
	}

	public function DeleteLinkingEntries($Reportnumber,$Searching) {

		if(!isset($Reportnumber['LastRecord'])) return false;

		$SearchingTest = $this->_controller->Searching->find('first',array(
							'conditions' => array(
								'Searching.value' => $Reportnumber['LastRecord']['last_value'],
								'Searching.model' => $Searching['Searching']['model'],
								'Searching.field' => $Searching['Searching']['field'],
								'Searching.topproject_id' => $Searching['Searching']['topproject_id'],
								)
							)
						);

		if(count($SearchingTest) > 0){

			$SearchingValueTest = $this->_controller->Searching->SearchingValue->find('all',array(
							'conditions' => array(
								'SearchingValue.reportnumber_id' => $Reportnumber['Reportnumber']['id'],
								'SearchingValue.searching_id' => $SearchingTest['Searching']['id'],
								)
							)
						);

			if(count($SearchingValueTest) > 0){

				foreach ($SearchingValueTest as $key => $value) {

					if($this->_controller->Searching->SearchingValue->delete($value['SearchingValue']['id'])){

					} else {

					}
				}
			}

			return;

		}
	}

	public function CreateLinkingEntries($Reportnumber,$Searching) {

		$this->DeleteLinkingEntries($Reportnumber,$Searching);

		$InputSearchingValue = array(
			'searching_id' => $Searching['Searching']['id'],
			'reportnumber_id' => $Reportnumber['Reportnumber']['id'],
			'topproject_id' => $Reportnumber['Reportnumber']['topproject_id'],
			'cascade_id' => $Reportnumber['Reportnumber']['cascade_id'],
			'order_id' => $Reportnumber['Reportnumber']['order_id'],
			'report_id' => $Reportnumber['Reportnumber']['report_id'],
			'testingmethod_id' => $Reportnumber['Reportnumber']['testingmethod_id'],
			'testingcomp_id' => $Reportnumber['Reportnumber']['testingcomp_id'],
		);

		$this->_controller->Searching->SearchingValue->create();

		if($this->_controller->Searching->SearchingValue->save($InputSearchingValue)){
			return true;
		} else {
			return false;
		}
	}

	public function SearchHistoryUpdate($SearchFormData,$SearchFieldsStandard,$SearchFieldsAdditional) {

		$SearchOption = array();
		$projectID = $this->_controller->request->projectvars['VarsArray'][0];

		$Lang = $this->_controller->Lang->Discription();
		$this->_controller->Testingmethod->recursive = -1;

		// die Suchbegriffe in die Suche einfügen
		if($SearchFieldsStandard != NULL){
			foreach($SearchFieldsStandard->fields->children() as $_key => $_data){

				$Model = trim($_data->model);
				$key = trim($_data->key);

				if(isset($SearchFormData['Current'][$Model][$key])){

					$Value = $SearchFormData['Current'][$Model][$key];

					if($SearchFormData['Current'][$Model][$key] == '0') continue;

					if($Model == 'Testingmethod'){
						$Testingmethod = $this->_controller->Testingmethod->find('first',array('fields' => array('verfahren'),'conditions' => array('Testingmethod.id' => $Value)));
						if(count($Testingmethod) == 1) $Value = $Testingmethod['Testingmethod']['verfahren'];
					}

					$SearchOption[$Model][$key]['description'] = trim($_data->description->$Lang);
					if(is_array($SearchFormData['Current'][$Model][$key])) $SearchOption[$Model][$key]['value'] = $Value;
					else $SearchOption[$Model][$key]['value'] = array($Value);
				}
			}
		}
		foreach($SearchFieldsAdditional->fields->children() as $_key => $_data){
			$Model = trim($_data->model);
			$key = trim($_data->key);
			if(isset($SearchFormData['Current'][$Model][$key])){

				$fieldtype = trim($_data->fieldtype);

				switch($fieldtype) {

				case 'dropdown';

				$Value = $SearchFormData['Current'][$Model][$key];

				if($SearchFormData['Current'][$Model][$key] == '0') break;

				$Searching = $this->_controller->Searching->find('first',array('fields' => array('value'),'conditions' => array('Searching.id' => $Value)));
				$Value = $Searching['Searching']['value'];

				$SearchOption[$Model][$key]['description'] = trim($_data->description->$Lang);
				if(is_array($SearchFormData['Current'][$Model][$key])) $SearchOption[$Model][$key]['value'] = $Value;
				else $SearchOption[$Model][$key]['value'] = array($Value);

				break;

				case 'autocomplete';

				$Value = $SearchFormData['Current'][$Model][$key];

				if($SearchFormData['Current'][$Model][$key] == '0') break;

				$Searching = $this->_controller->Searching->find('first',array('fields' => array('value'),'conditions' => array('Searching.id' => $Value)));
				$Value = $Searching['Searching']['value'];

				$SearchOption[$Model][$key]['description'] = trim($_data->description->$Lang);
				if(is_array($SearchFormData['Current'][$Model][$key])) $SearchOption[$Model][$key]['value'] = $Value;
				else $SearchOption[$Model][$key]['value'] = array($Value);

				break;

				case 'date';

				if(!is_array($SearchFormData['Current'][$Model][$key])) break;
				if(!isset($SearchFormData['Current'][$Model][$key]['start'])) break;
				if(!isset($SearchFormData['Current'][$Model][$key]['end'])) break;
				if(empty($SearchFormData['Current'][$Model][$key]['start'])) break;
				if(empty($SearchFormData['Current'][$Model][$key]['end'])) break;

				$SearchOption[$Model][$key]['description'] = trim($_data->description->$Lang);
				$SearchOption[$Model][$key]['value']['start'] = $SearchFormData['Current'][$Model][$key]['start'];
				$SearchOption[$Model][$key]['value']['end'] = $SearchFormData['Current'][$Model][$key]['end'];
				break;
				}
			}
		}

		$SearchFormData['CurrentOption'] = $SearchOption;

		$SearchComparison = array();

		// Suchhistory auffüllen
//$projectID
		// die History, die mit der Aktuellen gleich ist, wird gelöscht
		if(isset($SearchFormData['History'])){
			foreach($SearchFormData['History'] as $_key => $_data){
				if(!isset($_data['Current'])) continue;
				if(Hash::contains($_data['Current'], $SearchFormData['Current']) === true){
					unset($SearchFormData['History'][$_key]);
				}
			}
		}

		// Wenn die Höchstanzahl erreicht ist wird die Älteste gelöscht
		if(isset($SearchFormData['History']) && count($SearchFormData['History']) == 5){
			unset($SearchFormData['History'][key($SearchFormData['History'])]);
		}

		if(isset($SearchFormData['History']) && count($SearchFormData['History']) > 0 && count($SearchFormData['History']) < 5){
			$SearchFormDataForHistory = $SearchFormData;
			$SearchFormDataForHistory['topproject_id'] = $projectID;
			unset($SearchFormDataForHistory['History']);

			$SearchFormData['History'][time()] = $SearchFormDataForHistory;
			$this->_controller->Session->delete('SearchFormData.History');
			$this->_controller->Session->write('SearchFormData.History',$SearchFormData['History']);
		}
		if(!isset($SearchFormData['History'])){
			$SearchFormDataForHistory = $SearchFormData;
			$SearchFormDataForHistory['topproject_id'] = $projectID;
			unset($SearchFormDataForHistory['History']);
			$SearchFormData['History'][time()] = $SearchFormDataForHistory;
			$this->_controller->Session->delete('SearchFormData.History');
			$this->_controller->Session->write('SearchFormData.History',$SearchFormData['History']);
		}

		$SearchFormData = $this->_controller->Session->read('SearchFormData');


		return $SearchFormData;
	}

	public function MatchHistoryForForm() {

		$Output = array();
		$projectID = $this->_controller->request->projectvars['VarsArray'][0];
		$History = $this->_controller->Session->read('SearchFormData');

		if($History == null) return $Output;
		if(count($History) == 0) return $Output;

		$Autorisierung = $this->_controller->Autorisierung->ConditionsTopprojects();
		$Autorisierung = array_flip($Autorisierung);

		$Output['orders'] = array();
		$Output['reports'] = array();

		foreach($History['History'] as $_key => $_data){

			$Output['orders'][$_key] = 0;
			$Output['reports'][$_key] = count($_data['Result']['Reports']);

			if(!isset($_data['topproject_id'])) continue;
			if(!isset($_data['Result'])) continue;
			if(count($_data['Result']['Reports']) == 0 && count($_data['Result']['Orders']) == 0) continue;
			if(!isset($Autorisierung[$_data['topproject_id']])) continue;
			if($projectID != $_data['topproject_id']) continue;

			$array_key = strftime('%d.%m.%Y %H:%M:%S', $_key);
			$Output['link'][$_key] = $array_key . ' - ';

			foreach($_data['CurrentOption'] as $__key => $__data){
				foreach($__data as $___key => $___data){
					$Output['link'][$_key] .= $___data['description'] . ': ';
					if(is_array($___data['value'])) $Output['link'][$_key] .= implode(' - ',$___data['value']) . '; ';
				}
			}

			if(count($_data['Result']['Reports']) > 0) $Output['link'][$_key] .= '(' . __('found reports',true) . ': ' . count($_data['Result']['Reports']) . ')';
//			if(count($_data['Result']['Orders']) > 0) $Output['link'][$_key] .= '(' . __('found equipments',true) . ': ' . count($_data['Result']['Orders']) . ')';
		}

		if(isset($Output['link'])) krsort($Output['link']);

		return $Output;
	}


	public function MatchHistoryForFormDevice() {

		$Output = array();
	//	$projectID = $this->_controller->request->projectvars['VarsArray'][0];
		$History = $this->_controller->Session->read('SearchFormDataDevice');

		if($History == null) return $Output;
		if(count($History) == 0) return $Output;

		//$Autorisierung = $this->_controller->Autorisierung->ConditionsTopprojects();
		//$Autorisierung = array_flip($Autorisierung);

		//$Output['orders'] = array();
		//$Output['reports'] = array();

		foreach($History['History'] as $_key => $_data){

			//$Output['orders'][$_key] = 0;
			$Output['devices'][$_key] = $_data['devices'];

		//	if(!isset($_data['topproject_id'])) continue;
		//	if(!isset($_data['Result'])) continue;
			if(count($_data['devices']) == 0 && count($_data['devices']) == 0) continue;
			//if(!isset($Autorisierung[$_data['topproject_id']])) continue;
		//	if($projectID != $_data['topproject_id']) continue;

			$array_key = strftime('%d.%m.%Y %H:%M:%S', $_key);
			$Output['link'][$_key] = $array_key . ' - ';

			$currentsearch = implode($_data['CurrentSearch'],'; ');

			if(count($_data['devices']) > 0) $Output['link'][$_key] .= $currentsearch. ' (' . __('found devices',true) . ': ' . count($_data['devices']) . ')';
//			if(count($_data['Result']['Orders']) > 0) $Output['link'][$_key] .= '(' . __('found equipments',true) . ': ' . count($_data['Result']['Orders']) . ')';
		}

		krsort($Output['link']);

		return $Output;
	}

	public function HistorySearchOverload() {

		$SearchForm = $this->_controller->Session->read('SearchFormData');

		if(!isset($this->_controller->request->data['history'])) return NULL;
		if(empty($this->_controller->request->data['history'])) return NULL;

		$history = intval($this->_controller->request->data['history']);

		if(strlen($history) != 10) return NULL;


		$SearchFormData = $SearchForm['History'][$history]['Current'];

		unset($SearchForm['History'][$history]);

		$this->_controller->Session->write('SearchFormData.Current',$SearchFormData);

		return $SearchFormData;
	}

	public function HistorySearchResultOverload($SearchFormData) {

		if(!isset($this->_controller->request->data['history'])) return $SearchFormData;
		if(empty($this->_controller->request->data['history'])) return $SearchFormData;

		$history = intval($this->_controller->request->data['history']);
		if(empty($history)) return $SearchFormData;
		if(strlen($history) != 10) return $SearchFormData;

		if(!isset($SearchFormData['History'][$history])) return $SearchFormData;
		if(!isset($SearchFormData['History'][$history]['Current'])) return $SearchFormData;
		if(!isset($SearchFormData['History'][$history]['Result'])) return $SearchFormData;
		if(!isset($SearchFormData['History'][$history]['SearchTyp'])) return $SearchFormData;

		unset($SearchFormData['Current']);
		unset($SearchFormData['Result']);
		unset($SearchFormData['SearchTyp']);

		$SearchFormData['Current'] = $SearchFormData['History'][$history]['Current'];
		$SearchFormData['Result'] = $SearchFormData['History'][$history]['Result'];
		$SearchFormData['SearchTyp'] = $SearchFormData['History'][$history]['SearchTyp'];

		$this->_controller->Session->delete('SearchFormData');
		$this->_controller->Session->write('SearchFormData',$SearchFormData);

		return $SearchFormData;
	}

	public function MatchReportOrderIds($AllDropdownValues) {

		$ReportIds = $this->_controller->Session->read('SearchFormData.Result.Reports');
		$OrderIds = $this->_controller->Session->read('SearchFormData.Result.Orders');
		$Reportnumbers = $this->_controller->Reportnumber->find('list',array('group' => array('order_id'),'fields' => array('order_id'),'conditions' => array('Reportnumber.id' => $ReportIds)));

		if(count($Reportnumbers) > 0){

			$NewOrderIDs = array_intersect($OrderIds, $Reportnumbers);
//			$AllDropdownValues['Count']['Order'] = count($NewOrderIDs);
//			$AllDropdownValues['CountOfOrders'] = count($NewOrderIDs);
//			$this->_controller->Session->write('SearchFormData.Result.Orders',$NewOrderIDs);
		}

		return $AllDropdownValues;
	}

	public function getExpeditingsOfOrder($Orders) {

		if(!Configure::check('ExpeditingManager')) return $Orders;
		if(Configure::read('ExpeditingManager') != true) return $Orders;
		if(Configure::read('ExpeditingManager') == false) return $Orders;
		if(count($Orders) == 0) return $Orders;

		App::uses('CakeTime', 'Utility');

		$this->_controller->loadModel('Supplier');
		$this->_controller->loadModel('Expediting');

		foreach($Orders as $_key => $_data){

			$Suppliere = $this->_controller->Supplier->find('first',array('conditions' => array('Supplier.order_id' => $_data['Order']['id'])));
			if(count($Suppliere) == 0) continue;

			$Expeditings = $this->_controller->Expediting->find('all',array('order' => array('Expediting.sequence ASC'), 'conditions' => array('Expediting.supplier_id' => $Suppliere['Supplier']['id'])));

			if(count($Expeditings) == 0) continue;


			$Priority = 5;

			foreach($Expeditings as $__key => $__Expeditings){
				$Expeditings[$__key] = $this->_controller->ExpeditingTool->ExpeditingTimePeriode($Expeditings,$__Expeditings,$__key);
				if($Expeditings[$__key]['Expediting']['priority'] < $Priority) $Priority = $Expeditings[$__key]['Expediting']['priority'];
			}

			$Suppliere['Expediting'] = $Expeditings;
			$Suppliere['Supplier']['priority'] = $Priority;
			$Orders[$_key] = array_merge($Orders[$_key],$Suppliere);
		}

		$Priority = array(1 => 'icon_critical',2 => 'icon_delayed',3 => 'icon_plan',4 => 'icon_future',5 => 'icon_finished');

		$this->_controller->set('Priority',$Priority);

		return $Orders;

	}

	public function GetHistorySearchDataForDropdown($History,$xml){

		$output = array();

		if(!isset($History['History'])) return false;
		if(count($History['History']) == 0) return false;

		$StandardModels = array('Testingmethod' => true,'Topproject' => true,'Reportnumber' => true,'Cascade' => true,'Order' => true,'Report' => true);
		foreach($History['History'] as $_key => $_data){

			$Description = NULL;

			foreach($_data['CurrentOption'] as $__key => $__data){
				foreach($__data as $___key => $___data){
//					$Description .= $___data['description'] . ' > ' . implode(' ',$___data['value']) . ' | ';
					$Description .= $___data['description'];
				}
			}

			$output[$_key] = strftime('%H:%M:%S',$_key) . ' | ' . $Description;
		}
		return $output;
	}

	public function DeleteSearchTableForDuplication($id) {

		$this->_controller->loadModel('Searching');
		$this->_controller->Searching->SearchingValue->recursive = -1;
		$this->_controller->Searching->recursive = -1;

		$Option['conditions'] = array('SearchingValue.reportnumber_id' => $id);
		$Option['fields'] = array('searching_id');

		$SearchingValues = $this->_controller->Searching->SearchingValue->find('list',$Option);

		if(count($SearchingValues) == 0) return;

		$this->_controller->Searching->SearchingValue->deleteAll(array('SearchingValue.reportnumber_id' => $id), false);

		$Option = array();
		$Option['conditions'] = array('Searching.id' => $SearchingValues);

		$Searching = $this->_controller->Searching->find('list',$Option);

		if(count($Searching) == 0) return;

		$Option = array();

		foreach ($Searching as $key => $value) {

			$Option['conditions'] = array('SearchingValue.searching_id 	' => $value);
			$SearchingValue = $this->_controller->Searching->SearchingValue->find('count',$Option);

			if($SearchingValue == 0) $this->_controller->Searching->delete($value);

		}
	}

	public function CheckSearchingValueForReport($id,$Verfahren,$data) {

		$this->_controller->loadModel('Searching');
		$this->_controller->Searching->SearchingValue->recursive = -1;
		$this->_controller->Searching->recursive = -1;

		$Option['conditions'] = array('SearchingValue.reportnumber_id' => $id);
		$Option['fields'] = array('searching_id');

		$SearchingValues = $this->_controller->Searching->SearchingValue->find('list',$Option);

		if(count($SearchingValues) == 0) $this->UpdateSearchTableForDuplication($id,$Verfahren,$data);

	}

	public function UpdateSearchTableForDuplication($id,$Verfahren,$data) {

		$projectID = $this->_controller->request->projectvars['VarsArray'][0];

		$ReportnumberId = $id;

		$ReportGenerally = 'Report' . $Verfahren . 'Generally';
		$ReportSpecific = 'Report' . $Verfahren . 'Specific';
		$ReportEvaluation = 'Report' . $Verfahren . 'Evaluation';


		$SearchFieldsAdditional = $this->_controller->Xml->XmltoArray('search_additional', 'file', null);

		$this->__UpdateSearchTableForDuplication($ReportnumberId,$data,$ReportGenerally,$SearchFieldsAdditional);
		$this->__UpdateSearchTableForDuplication($ReportnumberId,$data,$ReportSpecific,$SearchFieldsAdditional);

	}

	protected function __UpdateSearchTableForDuplication($id,$data,$model,$xml) {

		$projectID = $this->_controller->request->projectvars['VarsArray'][0];

		if(!isset($data[$model])) return;

		foreach ($data[$model] as $key => $value) {

			if(empty($value)) continue;
			if(empty($xml->fields->{$key})) continue;
			if(trim($xml->fields->{$key}->model) != 'Generally') continue;

			$Model = trim($xml->fields->{$key}->model);
			$Field = trim($xml->fields->{$key}->key);

			$Option = array();
			$Option['conditions']['Searching.topproject_id'] = $projectID;
			$Option['conditions']['Searching.model'] = $Model;
			$Option['conditions']['Searching.field'] = $Field;
			$Option['conditions']['Searching.value'] = $value;

			$Searching = $this->_controller->Searching->find('first',$Option);

			$Insert = array();
			$Insert['topproject_id'] = $projectID;
			$Insert['model'] = $Model;
			$Insert['field'] = $Field;
			$Insert['value'] = $value;

			if(count($Searching) == 0){

				$this->_controller->Searching->create();
				$this->_controller->Searching->save($Insert);

			} else {

				$Option = array();
				$Option['conditions']['SearchingValue.reportnumber_id'] = $id;
				$Option['conditions']['SearchingValue.searching_id'] = $Searching['Searching']['id'];
				$SearchingTest = $this->_controller->Searching->SearchingValue->find('list',$Option);

				if(count($SearchingTest) == 0){

					$Insert = array();
					$Insert['reportnumber_id'] = $id;
					$Insert['searching_id'] = $Searching['Searching']['id'];

					$this->_controller->Searching->SearchingValue->create();
					$this->_controller->Searching->SearchingValue->save($Insert);

				}
			}
		}
	}

	public function UpdateTableForDuplication($IdNew,$IdOld) {

		$this->_controller->loadModel('Searching');
		$this->_controller->Searching->SearchingValue->recursive = -1;

		$SearchingTestOption['conditions'] = array('SearchingValue.reportnumber_id' => $IdOld);
		$SearchingTestOption['fields'] = array('searching_id');
		$SearchingTest = $this->_controller->Searching->SearchingValue->find('list',$SearchingTestOption);

		$SearchingValueData = array();

		foreach($SearchingTest as $_key => $_data){
			$SearchingValueData[] = array('searching_id' => $_data,'reportnumber_id' => $IdNew);
		}

		if($this->_controller->Searching->SearchingValue->saveAll($SearchingValueData)) return true;
		else return false;
	}

	public function UpdateTable($Reportnumber,$Model,$Field,$Value) {

		// Die IDs des Projektes und des Auftrages werden getestet
		$projectID = $this->_controller->request->projectvars['VarsArray'][0];
		$cascadeID = $this->_controller->request->projectvars['VarsArray'][1];
		$orderID = $this->_controller->request->projectvars['VarsArray'][2];
		$reportID = $this->_controller->request->projectvars['VarsArray'][3];
	 	$id = $this->_controller->request->projectvars['VarsArray'][4];
	 	$evalId = $this->_controller->request->projectvars['VarsArray'][5];
	 	$weldedit = $this->_controller->request->projectvars['VarsArray'][6];

		$Value = trim($Value);

		$ModelArray = explode('_',Inflector::underscore($Model));
		$ModelKind = ucfirst($ModelArray[2]);

		$this->_controller->loadModel('Searching');
		$this->_controller->Searching->recursive = -1;

		$SearchingTestOption = array('conditions' => array('Searching.topproject_id' => $projectID,'Searching.model' => $ModelKind,'Searching.field' => $Field));
		$SearchingTest = $this->_controller->Searching->find('first',$SearchingTestOption);

		// Schritt 1
		// Wenn keine Infos zum Model und Feld in der Tabelle vorhanden sind
		// ist davon auszugehen, dass es sich um kein Suchfeld handelt
		// hier sollte eine neue Funktion gebaut werden die automatisch ein neues Feld angelegt
		if(count($SearchingTest) == 0) return 'kein Suchfeld (1)';

		// Schritt 2
		// Die übergebene ReportnummerID wird in der verknüpften Suchtabelle gesucht
		$this->_controller->Searching->SearchingValue->recursive = -1;
		$SearchingValueTestOption = array('fields' => array('searching_id'),'conditions' => array('SearchingValue.reportnumber_id' => $Reportnumber['Reportnumber']['id']));
		$SearchingValueTest = $this->_controller->Searching->SearchingValue->find('list',$SearchingValueTestOption);

		// Schritt 3
		// Wenn die ReportnumberID noch nicht in der verknüpften Tabelle vorhanden ist
		// exisitiert auch kein Wert in der Suchtabelle
		// Es wird der komplette Datensatz angelegt
		if(count($SearchingValueTest) == 0 && !empty($Value)){

			$SearchingOption = array('conditions' => array('Searching.topproject_id' => $projectID,'Searching.model' => $ModelKind,'Searching.field' => $Field,'Searching.value' => $Value));
			$Searching = $this->_controller->Searching->find('first',$SearchingOption);

			// Falls der gleiche Wert schon in der Searchingtabelle existiert
			// nur ohne Verknüpfung
			if(count($Searching) == 0){
				// Alles anlegen
				$Create = $this->CreateAllEntries($Reportnumber,$Model,$Field,$Value);
				if($Create == true) return 'Es war nix vorhanden, alles angelegt (3)';
				if($Create == false) return 'Es war nix vorhanden, angelegen gescheitert (3)';
			}
			if(count($Searching) == 1){
				// nur den Wert in SearchingValue Tabelle anlegen
				$Insert['reportnumber_id'] = $Reportnumber['Reportnumber']['id'];
				$Insert['searching_id'] = $Searching['Searching']['id'];
				$this->_controller->Searching->SearchingValue->create();
				$this->_controller->Searching->SearchingValue->save($Insert);
			}
		}

		// Schritt 4
		// Wenn die ReportnumberID gefunden wurde,
		// werden in der Suchtabelle, die passenden Einträge gesucht
		if(count($SearchingValueTest) > 0 && !empty($Value)){

			// Es wird geprüft ob der aktuelle Wert (Suchbegriff), verknüpft mit der aktuelle
			// SearchingIDs schon vorhanden sind
			$SearchingOption = array('conditions' =>
				array(
					'Searching.topproject_id' => $projectID,
					'Searching.model' => $ModelKind,
					'Searching.field' => $Field,
					'Searching.value' => $Value,
					'Searching.id' => $SearchingValueTest
				)
			);

			$Searching = $this->_controller->Searching->find('first',$SearchingOption);

			// Schritt 5
			// Wenn der Wert (Suchbegriff) vorhanden sind, sind keine Aktionen mehr erforderlich
			if(count($Searching) > 0) return 'Alles vorhanden nix angelegt (5)';

			// Test ob der Wert ersetzt werden muss
			$SearchingOption = array('conditions' =>
				array(
					'Searching.topproject_id' => $projectID,
					'Searching.model' => $ModelKind,
					'Searching.field' => $Field,
					'Searching.id' => $SearchingValueTest
				)
			);

			$Searching = $this->_controller->Searching->find('first',$SearchingOption);

			if(count($Searching) == 1){

				$UpdateSearchValue = $Searching['Searching']['id'];

				$SearchingOption = array('conditions' =>
					array(
						'Searching.topproject_id' => $projectID,
						'Searching.model' => $ModelKind,
						'Searching.field' => $Field,
						'Searching.value' => $Value,
					)
				);

				$Searching = $this->_controller->Searching->find('first',$SearchingOption);

				if(count($Searching) == 1){

					$Insert['reportnumber_id'] = $Reportnumber['Reportnumber']['id'];
					$Insert['searching_id'] = $Searching['Searching']['id'];

					$this->_controller->Searching->SearchingValue->updateAll(
						array(
							'SearchingValue.searching_id' => $Searching['Searching']['id']),
    				array(
							'SearchingValue.reportnumber_id' => $Reportnumber['Reportnumber']['id'],
							'SearchingValue.searching_id' => $UpdateSearchValue
						)
					);
/*
					$this->_controller->Searching->SearchingValue->delete($SearchingDelete);
					$this->_controller->Searching->SearchingValue->create();
					$this->_controller->Searching->SearchingValue->save($Insert);
*/
					return;

				}
			}

			// Schritt 6
			// Wenn kein Ergebnis gefunden wurde, wird die Suche nochmals ohne
			// die Liste der SearchingIDs durchgeführt,
			$SearchingOption = array('conditions' =>
				array(
					'Searching.topproject_id' => $projectID,
					'Searching.model' => $ModelKind,
					'Searching.field' => $Field,
					'Searching.value' => $Value,
				)
			);

			$Searching = $this->_controller->Searching->find('all',$SearchingOption);

			// Schritt 7
			// Der Suchbegriff ist in der Searchingtabelle vorhanden
			// aber es existiert noch keine Verknüpfung mit der aktuellen ReportnumberID
			if(count($Searching) > 0){

				$SearchingTest = $this->_controller->Searching->find('first',$SearchingOption);

				unset($SearchingOption['conditions']['Searching.id']);

				$SearchingOption['conditions']['Searching.value'] = $Value;
				$Searching = $this->_controller->Searching->find('first',$SearchingOption);

				// Schritt 8
				// Verknüpfung anlegen
				if(count($Searching) > 0){
					$Create = $this->CreateLinkingEntries($Reportnumber,$Searching);
					if($Create == true) return 'Verknüpfung nicht vorhanden, angelegt (8)';
					if($Create == false) return 'Verknüpfung anlegen gescheitert (8)';
				}

				// Schritt 9
				if(count($Searching) == 0){

					$DeleteSearchingValueOption['conditions']['reportnumber_id'] = $Reportnumber['Reportnumber']['id'];
					$DeleteSearchingValueOption['conditions']['searching_id'] = $SearchingTest['Searching']['id'];
					$SearchingValueDeleteTest = $this->_controller->Searching->SearchingValue->find('all',$DeleteSearchingValueOption);

					// Wenn sich der Wert eines vorhanden Feldes ändert
					// muss zuerst die ID aus der Valuetabelle gelöscht werden
					if(count($SearchingValueDeleteTest) > 0){

						foreach ($SearchingValueDeleteTest as $key => $value) {
							$this->_controller->Searching->SearchingValue->delete($value['SearchingValue']['id']);
						}
					}

					// Alles anlegen
					$Create = $this->CreateAllEntries($Reportnumber,$Model,$Field,$Value);

					if($Create == true) return 'Wert nicht vorhanden, alles angelegt (9)';
					if($Create == false) return 'Wert anlegen gescheitert (9)';
				}
			}
		}


		// Wenn ein Wert leer kommt, wird der Eintrag in der Value-Tabelle gelöscht
		if(count($SearchingValueTest) > 0 && empty($Value)){


			$SearchingOption = array('fields' => array('id'), 'conditions' => array('Searching.topproject_id' => $projectID,'Searching.model' => $ModelKind,'Searching.field' => $Field,'Searching.id' => $SearchingValueTest));
			$Searching = $this->_controller->Searching->find('first',$SearchingOption);

			if(count($Searching) == 1){

				$DeleteOption = array(
									'SearchingValue.searching_id' => $Searching['Searching']['id'],
									'SearchingValue.reportnumber_id' => $Reportnumber['Reportnumber']['id']
									);

				$this->_controller->Searching->SearchingValue->deleteAll($DeleteOption, false);

				// Test,sind zum Haupteintrag noch Valueeinträge vorhanden
				// Wenn nicht Hauptwert löschen
				unset($DeleteOption['SearchingValue.reportnumber_id']);

				if($this->_controller->Searching->SearchingValue->find('count',array('conditions' => $DeleteOption)) == 0){
					$this->_controller->Searching->delete($Searching['Searching']['id']);
				}

				return 'Value gelöscht';
			}
		}

		if(count($SearchingValueTest) && !empty($Value)){
			// Schritt 10
			// Es wird geprüft ob der aktuelle Wert (Suchbegriff), verknüpft mit der aktuelle
			// SearchingIDs schon vorhanden sind
			$SearchingOption = array('conditions' => array('Searching.topproject_id' => $projectID,'Searching.model' => $ModelKind,'Searching.field' => $Field,'Searching.value' => $Value));
			$Searching = $this->_controller->Searching->find('first',$SearchingOption);

			// Schritt 11
			if(count($Searching) > 0){
				// Eintrag der SearchID in die verknüpfte Tabelle
				$Create = $this->CreateLinkingEntries($Reportnumber,$Searching);
				if($Create == true) return 'Verknüpfung nicht vorhanden, angelegt (11)';
				if($Create == false) return 'Verknüpfung anlegen gescheitert (11)';
				return 'Wert vorhanden, Verknüpfung angelegt (11)';
			}
			// Schritt 12
			if(count($Searching) == 0){
				$Create = $this->CreateAllEntries($Reportnumber,$Model,$Field,$Value);
				if($Create == true) return 'Wert nicht vorhanden, alles angelegt (12)';
				if($Create == false) return 'Wert anlegen gescheitert (12)';
			}
		}

		return false;
	}

	public function SearchValueCorrection(){

		$Option['conditions']['Searching.topproject_id'] = 14;
		$Option['conditions']['Searching.model'] = 'Evaluation';
		$Option['conditions']['Searching.field'] = 'welder';

		$SearchingIDs = $this->_controller->Searching->find('list',$Option);

		unset($Option);

		$Option['conditions']['SearchingValue.searching_id'] = $SearchingIDs;

		$this->_controller->Searching->SearchingValue->recursive = -1;

		$SearchingValues = $this->_controller->Searching->SearchingValue->find('all',$Option);

		$TestArray = array();

		foreach($SearchingValues as $_key => $_data){
			$TestArray[$_data['SearchingValue']['reportnumber_id']][$_data['SearchingValue']['searching_id']] = $_data['SearchingValue']['id'];
		}

		foreach($TestArray as $_key => $_data){
			if(count($TestArray[$_key]) > 1){

			}
		}

		die();
	}


	public function AddSelectedEntrys($AllDropdownValues,$SearchFormData){

		foreach ($SearchFormData as $key => $value) {

			foreach ($value as $_key => $_value) {

				if(is_array($_value)) continue;
				if(is_int($_value) && $_value == 0) continue;
				if($_value == '0') continue;

				$Field = Inflector::camelize($_key);

				$AllDropdownValues[$key][$Field]['selected'] = $_value;
			}
		}

		return $AllDropdownValues;

	}
}
