<?php
App::uses('AppController', 'Controller');
/**
 * Reportnumbers Controller
 *
 * @property Reportnumber $Reportnumber
 */
class SearchingsController extends AppController {

	public $components = array('Auth','Acl','Csv','Autorisierung','Cookie','Navigation','Lang','Sicherheit','Xml','Data','RequestHandler','Image','Pdf','Search','Statistic','Repairtracking');
	public $helpers = array('Js','Lang','Navigation','JqueryScripte','ViewData','Html');
	public $layout = 'ajax';
	protected $writeprotection = false;

	// Das ist ein Testkommentar für GIT

	function beforeFilter() {

		if($this->RequestHandler->isAjax()) {
			if (!$this->Auth->login()) {
				header('Requires-Auth: 1');
			}
		}

		// muss man noch ne Funkton draus machen
		$Auth = $this->Session->read('Auth');

		App::import('Vendor', 'Authorize');
		App::uses('Folder', 'Utility');
		App::uses('File', 'Utility');
		App::uses('CakeTime', 'Utility');

		$this->loadModel('User');
		$this->loadModel('Topproject');
		$this->loadModel('Order');
		$this->loadModel('Testingmethod');
		$this->loadModel('Topproject');
		$this->loadModel('Report');
		$this->loadModel('Cascade');
		$this->loadModel('Testingcomp');
		$this->loadModel('Reportnumber');
		$this->loadModel('Searching');
		$this->loadModel('SearchingValue');

		$this->Autorisierung->Protect();

		$this->Lang->Choice();
		$this->Lang->Change();
		$this->Navigation->ReportVars();
		$this->request->lang = $this->Lang->Discription();

		$noAjaxIs = 0;

		$noAjaxIs = 0;
		$noAjax = array('auto','update','insertdata','pdf','csv');

		// Test ob die aktuelle Funktion per Ajax oder direkt aufgerufen werden soll
		foreach($noAjax as $_noAjax){
			if($_noAjax == $this->request->params['action']){
				$noAjaxIs++;
				break;
			}
		}

		if($noAjaxIs == 0){
			$this->Navigation->ajaxURL();
		}

		$this->request->local =	$this->Lang->Discription();
		$this->set('lang', $this->Lang->Choice());
		$this->set('selected', $this->Lang->Selected());
		$this->set('login_info', $this->Navigation->loggedUser());
		$this->set('lang_choise', $this->Lang->Choice());
		$this->set('previous_url', $this->base.'/'.$this->Session->read('lastURL'));
		$this->set('locale', $this->Lang->Discription());

		if(isset($this->Auth)) {
			$this->set('authUser', $this->Auth);
		}
	}

	function afterFilter() {
		$this->Navigation->lastURL();
	}

	function autofast() {

//		$this->autoRender = false;
		$this->layout = 'json';

		$request = array();

		$request = $this->Search->AutoFastStandard($request);
		$request = $this->Search->AutoFastAdditional($request);
		$request = $this->Search->UpdateSearchResult($request);
		$request = $this->Search->ShowSearchResult($request);

		if(isset($this->request->data['show_search_result'])){

			$this->set('reportnumbers',$request);
			$this->render('searchresult_landingpage','blank');

		} else {
			$this->set('response',json_encode($request));
		}
	}

	function auto() {

		$projectID = $this->request->projectvars['VarsArray'][0];
		$cascadeID = $this->request->projectvars['VarsArray'][1];
		$orderID = $this->request->projectvars['VarsArray'][2];
		$reportID = $this->request->projectvars['VarsArray'][3];

		App::uses('Sanitize', 'Utility');
//		$this->autoRender = false;
		$StandardAutoSearch = false;

 		$this->layout = 'json';

		$Model = null;
		$Field = null;
		$Value = null;

		$Model = Sanitize::clean(key($this->request->data));
		if(isset($this->request->data[$Model])) $Field = Sanitize::clean(key($this->request->data[$Model]));
		if(isset($this->request->data[$Model][$Field])) $Value = Sanitize::clean($this->request->data[$Model][$Field]);


		if($Model == null) $Model = Sanitize::clean(key($this->request->query['data']));
		if($Field == null) $Field = Sanitize::clean(key($this->request->query['data'][$Model]));
		if($Value == null) $Value = Sanitize::clean($this->request->query['data'][$Model][$Field]);

		$AutocompleteOptions['Model'] = $Model;
		$AutocompleteOptions['Field'] = $Field;
		$AutocompleteOptions['Value'] = $Value;

		$StandardModels = array('Testingmethod' => true,'Topproject' => true,'Reportnumber' => true,'Cascade' => true,'Order' => true,'Report' => true);
		$SearchFormData = $this->Session->read('SearchFormData.Current');
		$AllSearchingValuesIDs = array();
		$SearchingReportnumberIDs = array();

		$this->loadModel('Reportnumber');
		$this->Reportnumber->recursive = -1;

		$this->Searching->recursive = -1;
		$this->Searching->SearchingValue->recursive = -1;

		foreach($StandardModels as $_key => $_StandardModels){
			if(isset($this->request->data[$_key])){
				$StandardAutoSearch = true;
				break;
			}
		}

		if($SearchFormData != NULL && is_array($SearchFormData) && $StandardAutoSearch === false){
			foreach($SearchFormData as $_key => $_SearchFormData){
				foreach($_SearchFormData as $__key => $__SearchFormData){

					if(isset($SearchingValuesIDs)) $LastSearchingValuesIDs = $SearchingValuesIDs;

					$SearchingValuesIDs = $this->Searching->SearchingValue->find('list',array('fields' => array('reportnumber_id'),'conditions' => array('SearchingValue.searching_id' => intval($__SearchFormData))));

					if(isset($LastSearchingValuesIDs)){
						$AllSearchingValuesIDs = array(array_intersect(($SearchingValuesIDs),($LastSearchingValuesIDs)));
					} else {
						$AllSearchingValuesIDs[] = $SearchingValuesIDs;
					}
				}
			}
		}

		$StandardSearchOptions = $this->Search->StandardSearchOptions($SearchFormData,$StandardModels);
		$StandardSearchResult = $this->Reportnumber->find('list',$StandardSearchOptions);

		if(count($AllSearchingValuesIDs) > 0) $AllSearchingValuesIDs[] = $StandardSearchResult;
		elseif(count($AllSearchingValuesIDs) == 0) $AllSearchingValuesIDs[0] = $StandardSearchResult;

		if(count($AllSearchingValuesIDs) > 1){
			$AllSearchingValuesIDs = call_user_func_array('array_intersect', $AllSearchingValuesIDs);
			$AllSearchingValuesIDs = $AllSearchingValuesIDs;
		} elseif(count($AllSearchingValuesIDs) == 1){
			$AllSearchingValuesIDs = $AllSearchingValuesIDs[0];
		} else {
			$AllSearchingValuesIDs = array();
		}

		sort($AllSearchingValuesIDs);

		if(count($AllSearchingValuesIDs) > 0) $SearchingReportnumberIDs = $this->Searching->SearchingValue->find('list',array('fields' => array('searching_id'),'group' => 'searching_id', 'conditions' => array('SearchingValue.reportnumber_id' => $AllSearchingValuesIDs)));

		$SessionValueArray = NULL;

		if(count($SearchingReportnumberIDs) > 0) $SessionValueArray = array('AND' => array('Searching.id' => $SearchingReportnumberIDs));

		$response = array();

		if($StandardAutoSearch == false){

			$SearchOption = array(
							'Searching.topproject_id' => $projectID,
							'Searching.model' => $Model,
							'Searching.field' => $Field,
							'AND' => array('Searching.value LIKE' => '%' . html_entity_decode($Value) . '%'),
//							$SessionValueArray
							);

			$Searching = $this->Searching->find('list',array('limit' => 15,'fields' => array('id','value'),'conditions' => $SearchOption));
			foreach($Searching as $_key => $_Searching) array_push($response, array('key' => $_key,'value' => $_Searching));
			$this->set('response',json_encode($response));

		} else {
			$response = $this->Search->StandardAutocompleteOptions($AutocompleteOptions);
			$this->set('response',json_encode($response));
		}
	}

	function update() {

 		$this->layout = 'json';
		//$this->autoRender = false;

		App::uses('Sanitize', 'Utility');

		$projectID = $this->request->projectvars['VarsArray'][0];
		$cascadeID = $this->request->projectvars['VarsArray'][1];
		$orderID = $this->request->projectvars['VarsArray'][2];
		$reportID = $this->request->projectvars['VarsArray'][3];

		$SearchFieldsStandard = $this->Xml->XmltoArray('search_standard', 'file', null);
		$SearchFieldsAdditional = $this->Xml->XmltoArray('search_additional', 'file', null);

		$this->Session->delete('SearchFormData.Result.Reports');

		if(count($this->request->data) == 0){
			$this->Session->delete('SearchFormData.Current');
			$SearchFields = $SearchFieldsAdditional;
			$DropdownValues = $this->Search->SearchFields($SearchFields,array(),'update');
			$this->set('response',json_encode($DropdownValues));
			return;
		}

		$Models = array();
		$StandardModels = array('Testingcomp' => true,'Testingmethod' => true,'Topproject' => true,'Reportnumber' => true,'Cascade' => true,'Report' => true);
		$DropdownValues = array();
		$SearchFormData = $this->Session->read('SearchFormData.Current');

		$this->loadModel('Reportnumber');
		$this->Reportnumber->recursive = -1;

		if(isset($this->request->data['history'])) $SearchFormData = $this->Search->HistorySearchOverload();

		if($SearchFormData == NULL){
			$SearchFormData = $this->request->data;
			$this->Session->write('SearchFormData.Current',$SearchFormData);
		}

		elseif(is_array($SearchFormData) && !isset($this->_controller->request->data['history'])){
			// Vorhanden Werte aus der Session
			// werdenn mit den Werten aus dem Request aktualisiert
			foreach($SearchFormData as $_key => $_SearchFormData){
				foreach($_SearchFormData as $__key => $__SearchFormData){
					if(isset($this->request->data[$_key][$__key])){
						if(!is_array($this->request->data[$_key][$__key])) $SearchFormData[$_key][$__key] = $this->request->data[$_key][$__key];
						if(is_array($this->request->data[$_key][$__key])) $SearchFormData[$_key][$__key] = $this->request->data[$_key][$__key];
					}
				}
			}
			// Neue Werte werden hinzugefügt
			// Elemente mit dem Wert 0 werden gelöscht
			foreach($this->request->data as $_key => $_request){

				if(!is_array($_request)) continue;

				foreach($_request as $__key => $__request){
					if(!isset($SearchFormData[$_key][$__key])){
						$SearchFormData[$_key][$__key] = ($__request);
						if(!is_array($__request)) $SearchFormData[$_key][$__key] = $__request;
						if(is_array($__request)) $SearchFormData[$_key][$__key] = $__request;
					}
					if($SearchFormData[$_key][$__key] == 0 && !is_array($SearchFormData[$_key][$__key])){
						//unset($SearchFormData[$_key][$__key]);
					}
				}
			}

			$this->Session->write('SearchFormData.Current',$SearchFormData);

		}

		// sollte später schon richtig formatiert per Post geliefert werden
		$SearchFormData = $this->Search->CreateMultipleArray($SearchFieldsStandard,$SearchFormData,'update');
		$SearchFormData = $this->Search->DeleteEmptyMultipleArrayAdditional($SearchFieldsAdditional,$SearchFormData,'update');
		// Die Options für die Dropdownfelder holen
		$Output = $this->Search->SearchStandardFieldsbyOrder($SearchFieldsStandard,$SearchFormData,'update');

		$StandardFieldsOptions = $Output['Values'];
		$FoundetIDs = $Output['FoundetIDs'];

		unset($Output);

		$CountSearchingOrdersIDs = $StandardFieldsOptions['Count']['Order'];
		$SearchingValuesIDs = array();

		// Alle ReportnumberIDs aus der SearchingValueTabelle holen
		// die mit den Suchparametern übereinstimmen
		// Standardmodelle werden als Blacklist übergeben
		$SearchFormDataUnformated = $SearchFormData;
		$NoAdditionalPostData = false;

		$SearchFormData = $this->Search->DateSearchFormating($SearchFormData,$SearchFieldsAdditional);
		$AllSearchingValuesIDs = $this->Search->GetSearchingValueIDs($SearchFormData,$StandardModels,$SearchFieldsAdditional);

		if(count($AllSearchingValuesIDs) == 0) $NoAdditionalPostData = true;
		$SearchFormData = $SearchFormDataUnformated;

		// Conditions für die ReportnumberTabelle anhand der Models
		// Testingmethod, Topproject, Reportnumber, Cascade, Order, Report zusammenstellen
		// außer bei Reportnumber werden nur die id-Felder berücksichtigt

		$StandardSearchOptions = $this->Search->StandardSearchOptions($SearchFormData,$StandardModels);

		// Wenn es bei den Aufträgen Ergebnisse gab
		// Müssen diese in die Prüfberichtssuche einfließen

		if(Configure::check('ShowNeGlobally') && Configure::read('ShowNeGlobally') == false) {
			$StandardSearchOptions['conditions']['Reportnumber.testingcomp_id'] = AuthComponent::user('testingcomp_id');
		}

		// ReportnumberIds mit den Standardconditions suchen
		$StandardSearchResult = $this->Reportnumber->find('list',$StandardSearchOptions);

		if(count($StandardSearchResult) > 0) $AllSearchingValuesIDs[] = $StandardSearchResult;

		$StandardSearchResult = array();

		if(count($AllSearchingValuesIDs) > 1){
			$AllSearchingValuesIDs = call_user_func_array('array_intersect', $AllSearchingValuesIDs);
			$AllSearchingValuesIDs = $AllSearchingValuesIDs;
		} elseif(count($AllSearchingValuesIDs) == 1){
			$AllSearchingValuesIDs = $AllSearchingValuesIDs[0];
		} else {
			$AllSearchingValuesIDs = array();
		}

		$CountSearchingReportnumberIDs = count($AllSearchingValuesIDs);

		$DropdownValues = $this->Search->ColletAdditionalsFromSearchTable($AllSearchingValuesIDs,$DropdownValues,$SearchFieldsAdditional);

		if(count($DropdownValues) == 0){
			$SearchFieldsStandard = $this->Xml->XmltoArray('search_standard', 'file', null);
			$SearchFields = $SearchFieldsAdditional;
			$DropdownValues = $this->Search->SearchFields($SearchFields,$SearchFormData,'update');
		}

		// Bei zusätzlichen Felder ohne Daten, werden leere Knoten eingefügt
		// um das Suchformular zu aktualisieren
		$DropdownValues = $this->Search->PutEmptyFields($DropdownValues,$SearchFieldsAdditional);
		$DropdownValues = $this->Search->DropdownFormating($DropdownValues,$SearchFieldsAdditional);

		$AllDropdownValues = array_merge($StandardFieldsOptions,$DropdownValues);
		$AllDropdownValues['CountOfSearch'] = $CountSearchingReportnumberIDs;
		$AllDropdownValues['CountOfOrders'] = 0;
		$this->Session->write('SearchFormData.Result.Reports',$AllSearchingValuesIDs);
		$AllDropdownValues = $this->Search->AddSelectedEntrys($AllDropdownValues,$SearchFormData);
		
		$this->set('response',json_encode($AllDropdownValues));
	}

	public function statistic() {
		$this->Session->delete('SearchFormData.PdfExport');
		$this->__searchform('statistic');
	}

	public function search() {

		if(isset($this->request->data['landig_page_large']) && $this->request->data['landig_page_large'] == 1) $this->_searchform_landingpage('search');

		if(isset($this->request->data['search_type'])){

			switch ($this->request->data['search_type']) {
				case 'testingreports':
					$this->__searchform_reports();
					break;

				default:
					// code...
					break;
			}

		}

		else $this->__searchform('search');

	}

	protected function _searchform_landingpage($modus) {

		$SearchFieldsStandard = $this->Xml->XmltoArray('search_landingpage', 'file', null);
		$Request = array();

		$Request = $this->_searchform_landingpage_dropdown($SearchFieldsStandard,$Request);

		$this->request->data = $Request;

		$this->set('SearchFieldsStandard',$SearchFieldsStandard);

		$this->render('search_landingpage','blank');

	}

	protected function _searchform_landingpage_dropdown($xml,$data) {
		
		if(!is_object($xml)) return;

		$lang = $this->Lang->Discription();

		foreach($xml->fields->children() as $key => $value){

			$Fieldtype = trim($value->fieldtype);

			if($Fieldtype != 'dropdown') continue;

			$Model = trim($value->model);
			$field = trim($value->key);
			$Description = trim($value->description->{$lang});

			$this->Searching->recursive = -1;
	
			$Searching = $this->Searching->find('list',array(
				'conditions' => array(
					'Searching.model' => $Model,
					'Searching.field' => $field
					)
				)
			);

			if(count($Searching) == 0) continue;

			$SearchingValue = $this->SearchingValue->find('first',array('conditions' => array('SearchingValue.searching_id' => $Searching)));

			if(count($SearchingValue) == 0) continue;

			$Searching = $this->Searching->find('list',array(
								'fields' => array('id','value'),
								'conditions' => array(
									'Searching.model' => $Model,
									'Searching.field' => $field
									)
								)
							);

			$Searching = array_unique($Searching);				
				
			$Field = Inflector::camelize($field);

			$Options = array(0 => '');

			foreach ($Searching as $_key => $_value){
				$Options[$_key] = $_value;
			}

			$data[$Model][$Field]['value'] = $Options;
			$data[$Model][$Field]['description'] = $Description;

		}
		
		return $data;

	}

	protected function __searchform_reports() {

		$projectID = $this->request->projectvars['VarsArray'][0];
		$cascadeID = $this->request->projectvars['VarsArray'][1];
		$orderID = $this->request->projectvars['VarsArray'][2];
		$reportID = $this->request->projectvars['VarsArray'][3];

		$breads = $this->Navigation->Breads(null);
		$CascadeForBread = $this->Navigation->CascadeGetBreads(intval($cascadeID));
		$breads = $this->Navigation->CascadeCreateBreadcrumblist($breads,$CascadeForBread);

		$SearchFieldsStandard = $this->Xml->XmltoArray('search_report_standard', 'file', null);
		$StandardFieldValues = $this->Search->SearchStandardFields($SearchFieldsStandard,array(),'initial');

		$StandardFieldValues['Cascade']['Id']['selected'] = $cascadeID;

		$this->request->data = $StandardFieldValues;

		$this->set('breads',$breads);
		$this->set('SettingsArray', array());
		$this->set('SearchFieldsStandard',$SearchFieldsStandard);

		$this->render('search_reports','ajax');

	}

	protected function __searchform($modus) {

		$this->Session->delete('SearchInsertData');
		$this->Session->delete('SearchFormData.SearchTyp');
		$this->Session->delete('SearchFormData.Current');
		$this->Session->delete('SearchFormData.Result');
		$SearchFormData = $this->Session->read('SearchFormData');

		$projectID = $this->request->projectvars['VarsArray'][0];
		$cascadeID = $this->request->projectvars['VarsArray'][1];
		$orderID = $this->request->projectvars['VarsArray'][2];
		$reportID = $this->request->projectvars['VarsArray'][3];

		$SearchFieldsStandard = $this->Xml->XmltoArray('search_standard', 'file', null);
		$SearchFieldsAdditional = $this->Xml->XmltoArray('search_additional', 'file', null);
		$StandardFieldValues = $this->Search->SearchStandardFields($SearchFieldsStandard,array(),'initial');

		$FieldValues = $this->Search->SearchFields($SearchFieldsAdditional,array(),'initial');

		$StandardFieldValues['Cascade']['Id']['selected'] = $cascadeID;

		$this->request->data = $StandardFieldValues;

		$this->request->data = array_merge($StandardFieldValues,$FieldValues);
		$breads = $this->Navigation->Breads(null);
		$CascadeForBread = $this->Navigation->CascadeGetBreads(intval($cascadeID));
		$breads = $this->Navigation->CascadeCreateBreadcrumblist($breads,$CascadeForBread);

		$SearchDataForDropdown = $this->Search->GetHistorySearchDataForDropdown($SearchFormData,$xml = array('Standard' => $SearchFieldsStandard,'Additional' =>$SearchFieldsAdditional));

		$SettingsArray = array();

		switch($modus){

			case 'search':

			$h2 = __('Searching',true);
			$breads[] = array('discription'=>__('Search',true),'controller'=>'searchings','action'=>'search','pass'=>$this->request->projectvars['VarsArray']);

			break;

			case 'statistic':

			$h2 = __('Statistic',true);
			$breads[] = array('discription'=>__('Statistic',true),'controller'=>'searchings','action'=>'search','pass'=>$this->request->projectvars['VarsArray']);

			break;
		}

		$HistorySearch = $this->Search->MatchHistoryForForm();

		$this->set('SettingsArray', $SettingsArray);
		$this->set('breads',$breads);
		$this->set('SearchFieldsAdditional',$SearchFieldsAdditional);
		$this->set('SearchFieldsStandard',$SearchFieldsStandard);
		$this->set('FieldValues',$FieldValues);
		$this->set('StandardFieldValues',$StandardFieldValues);
		$this->set('SearchFormData',$SearchFormData);
		$this->set('SearchDataForDropdown',$SearchDataForDropdown);
		$this->set('HistorySearch',$HistorySearch);
		$this->set('h2',$h2);
	}

	public function results() {

		$projectID = $this->request->projectvars['VarsArray'][0];
		$cascadeID = $this->request->projectvars['VarsArray'][1];
		$orderID = $this->request->projectvars['VarsArray'][2];
		$reportID = $this->request->projectvars['VarsArray'][3];
		$SearchTyp = $this->request->data['search_typ'];

		if(!isset($this->request->data['history']) && $this->Session->check('SearchFormData.Current') == false){
			$breads = $this->Navigation->Breads(null);
			$CascadeForBread = $this->Navigation->CascadeGetBreads(intval($cascadeID));
			$breads = $this->Navigation->CascadeCreateBreadcrumblist($breads,$CascadeForBread);
			$breads[] = array('discription'=>__('Search',true),'controller'=>'searchings','action'=>'search','pass'=>$this->request->projectvars['VarsArray']);
//			$breads[] = array('discription'=>__('Search result',true),'controller'=>'searchings','action'=>'results','pass'=>$this->request->projectvars['VarsArray']);

			$this->set('SettingsArray', array());
			$this->set('breads',$breads);
			$this->render('results');
		}

		if(isset($this->request->data['search_typ'])) {
			$this->Session->write('SearchFormData.SearchTyp',$this->request->data['search_typ']);
		}
		if(isset($this->request->data['statistic_typ'])) {
			$this->Session->write('SearchFormData.StatisticTyp',$this->request->data['statistic_typ']);
		}

		$SearchFormData = $this->Session->read('SearchFormData');

		if(isset($this->request->data['history']) && $this->request->data['search_typ'] != 4) $SearchFormData = $this->Search->HistorySearchResultOverload($SearchFormData);

		$months = array(1 => __('January', true),2 =>  __('February', true),3 =>  __('March', true),4 =>  __('April', true),5 =>  __('May', true),6 =>  __('June', true),7 =>  __('July', true),8 =>  __('August', true),9 =>  __('September', true),10 =>  __('October', true),11 =>  __('November', true),12 =>  __('December', true));
		$this->set('Months',$months);

		if(isset($this->request->data['search_typ'])){
			switch($this->request->data['search_typ']){
				case 1:
					$this->__ReportResults($SearchFormData);
				break;
				case 2:
					$this->__OrderResults($SearchFormData);
				break;
				case 3:
					$this->__StatisticResults($SearchFormData);
				break;
				case 4:
					if(isset($SearchFormData['StatisticTyp'])) {
						$this->__StatisticSubresults($SearchFormData);
					}
				break;
			}
		}
	}

	protected function __StatisticResults($SearchFormData) {

		App::uses('Sanitize', 'Utility');
//		$this->autoRender = false;
		$lang = $this->Lang->Discription();

		$projectID = $this->request->projectvars['VarsArray'][0];
		$cascadeID = $this->request->projectvars['VarsArray'][1];
		$orderID = $this->request->projectvars['VarsArray'][2];
		$reportID = $this->request->projectvars['VarsArray'][3];

		$this->Autorisierung->ConditionsTopprojectsTest($projectID);
		$user = AuthComponent::user();

		$StandardModels = array('Testingmethod' => true,'Topproject' => true,'Reportnumber' => true,'Cascade' => true,'Order' => true,'Report' => true);

		unset($this->request->data['ajax_true']);

//		if(count($this->request->data) == 0) $SearchFormData = $this->Session->read('SearchFormData.current');
//		else $SearchFormData = $this->request->data;

		if($SearchFormData == NULL) return;
		if(is_array($SearchFormData['Result']['Reports'])) $ReportIDs = $SearchFormData['Result']['Reports'];

		$SearchFields = $this->Xml->XmltoArray('search_additional', 'file', null);
		$SearchFieldsStandard = $this->Xml->XmltoArray('search_standard', 'file', null);

		if(!isset($this->request->data['Generally']['history'])) $SearchFormData = $this->Search->SearchHistoryUpdate($SearchFormData,$SearchFieldsStandard,$SearchFields);

		$ConditionsTopprojects = $this->Autorisierung->ConditionsTopprojects();

		$breads = $this->Navigation->Breads(null);
		$CascadeForBread = $this->Navigation->CascadeGetBreads(intval($cascadeID));
		$breads = $this->Navigation->CascadeCreateBreadcrumblist($breads,$CascadeForBread);

		$breads[] = array('discription'=>__('Searching',true),'controller'=>'searchings','action'=>'search','pass'=>$this->request->projectvars['VarsArray']);
//		$breads[] = array('discription'=>__('Statistic result',true),'controller'=>'searchings','action'=>'results','pass'=>$this->request->projectvars['VarsArray']);
//		$SettingsArray['addsearching'] = array('discription' => __('Searching',true), 'controller' => 'searchings','action' => 'search', 'terms' => $this->request->projectvars['VarsArray'],);
		$SettingsArray = array();

		$this->set('SettingsArray', $SettingsArray);
		$this->set('breads',$breads);
		$this->set('ReportIDs',$ReportIDs);
		$this->set('SearchFormData',$SearchFormData);
		$this->render('statistic_results');
	}

	protected function __StatisticSubresults($SearchFormData) {

//		$this->Session->delete('SearchFormData.PdfExport');

		$SearchFieldsStandard = $this->Xml->XmltoArray('search_standard', 'file', null);
		$SearchFieldsAdditional = $this->Xml->XmltoArray('search_additional', 'file', null);

		$SearchingDescription = $this->Search->ShowStandardFieldResults($SearchFieldsStandard,$SearchFormData,$Values = array());
		$SearchingDescription = $this->Search->ShowAdditionalFieldResults($SearchFieldsAdditional,$SearchFormData,$SearchingDescription);
		$SearchFormData = $this->Statistic->ReformateDateFormating($SearchFormData,$SearchFieldsStandard,$SearchFieldsAdditional);

		if(isset($SearchingDescription['Reportnumber']['date_of_test'])) unset($SearchingDescription['Reportnumber']['date_of_test']);

		$this->set('SearchingDescription',$SearchingDescription);
		switch($SearchFormData['StatisticTyp']){
			case 1 :

			$output = $this->Statistic->StatisticTable($SearchFormData);

			$this->request->data = $this->Session->read('SearchFormData.Current');

			if(!isset($this->request->data['Reportnumber']['testingmethod'])) $this->request->data['Reportnumber']['testingmethod'] = 'all_testingmethods';

			if(isset($this->request->data['Generally']['welding_company']) && $this->request->data['Generally']['welding_company'] > 0){
				$this->Session->write('SearchFormData.PdfExport.welding_company',$this->request->data['Generally']['welding_company']);
			}

			if(isset($output['Day'])){
				$TableData = $output['Day'][0];
				$this->set('List',array('Day' => $TableData));
			}
			if(isset($output['Month'])){
				$TableData = $output['Month'][0];
				$this->set('List',array('Mouth' => $TableData));
			}
			if(isset($output['Year'])){
				$TableData = $output['Year'][0];
				$this->set('List',array('Year' => $TableData));
			}

			if($output == NULL) $this->render('results','blank');
			else $this->render('statistic_results_01','blank');

			break;

			case 2 :

//			$this->Session->delete('SearchFormData.PdfExport.years');
//			$this->Session->delete('SearchFormData.PdfExport.months');
//			$this->Session->delete('SearchFormData.PdfExport.verfahren');

			$output = $this->Statistic->StatisticTable($SearchFormData);

			if(isset($output['Day'])){
				$Data = $output['Day'][1];
			}
			if(isset($output['Month'])){
				$Data = $output['Month'][1];
			}
			if(isset($output['Year'])){
				$Data = $output['Year'][1];
			}

			$Data = $this->Statistic->ReduceToTimerange($Data,$SearchFormData,$SearchFieldsAdditional);

			if(isset($this->request->data['image_width'])) $ImageWidth = $this->request->data['image_width'];
			else $ImageWidth = 2000;
			if(isset($this->request->data['image_height'])) $ImageHeight = $this->request->data['image_height'];
			else $ImageHeight = 800;

			$Verfahren = 'all_testingmethods';

			if(isset($this->request->data['Reportnumber']['testingmethod'])){
				$Verfahren = $this->request->data['Reportnumber']['testingmethod'];
				$this->Session->write('SearchFormData.PdfExport.verfahren',$Verfahren);
			}

			if(isset($this->request->data['Reportnumber']['years'])){
				$this->Session->write('SearchFormData.PdfExport.years',$this->request->data['Reportnumber']['years']);
			} else {
				$this->Session->delete('SearchFormData.PdfExport.years');
			}

			if(isset($this->request->data['Reportnumber']['months'])){
				$this->Session->write('SearchFormData.PdfExport.months',$this->request->data['Reportnumber']['months']);
			} else {
				$this->Session->delete('SearchFormData.PdfExport.months');
			}

			$this->set('Verfahren',$Verfahren);
			$this->set('return',true);
			$this->set('ImageWidth',$ImageWidth);
			$this->set('ImageHeight',$ImageHeight);
			$this->autoRender = false;

			if(is_array($output)){

				switch($output){

					case (isset($output['Year'])):
					$this->set('List',$Data);
					$this->render('statistic/statistic_results_years','jpgraph');
					break;

					case (isset($output['Month'])):
					$this->set('List',$Data);
					$this->render('statistic/statistic_results_months','jpgraph');
					break;

					case (isset($output['Day'])):
					$this->set('List',$Data);
					$this->render('statistic/statistic_results_days','jpgraph');
					break;

				}

			} else {
				$this->render('results','blank');
			}

			break;

			case 3 :

			$output = $this->Statistic->StatisticTable($SearchFormData);

			if(isset($output['Day'])) $Data['Day'] = $output['Day'][0];
			if(isset($output['Month'])) $Data['Month'] = $output['Month'][0];
			if(isset($output['Year'])) $Data['Year'] = $output['Year'][0];

			$WelderMistakes = $this->Statistic->SearchWelderMistake($Data);

			$Data = $WelderMistakes[1];
			$Mistakes = $WelderMistakes[0];

			if(isset($this->request->data['image_width'])) $ImageWidth = $this->request->data['image_width'];
			else $ImageWidth = 2000;
			if(isset($this->request->data['image_height'])) $ImageHeight = $this->request->data['image_height'];
			else $ImageHeight = 800;

			$this->autoRender = false;

			$this->set('SearchingDescription',$SearchingDescription);
			$this->set('return',true);
			$this->set('ImageWidth',$ImageWidth);
			$this->set('ImageHeight',$ImageHeight);
			$this->set('Data',$Data);
			$this->set('Mistakes',$Mistakes);

			if(count($Mistakes[key($Mistakes)]['Mistakes']['errors']) > 0) $this->render('statistic/statistic_results_welder_mistake','jpgraph');
			else $this->render('results','blank');
			break;

			case 4 :

			if(isset($this->request->data['history']) && $this->request->data['history'] > 0) $history = $this->request->data['history'];

			if(isset($this->request->data['image_width'])) $ImageWidth = $this->request->data['image_width'];
			else $ImageWidth = 2000;
			if(isset($this->request->data['image_height'])) $ImageHeight = $this->request->data['image_height'];
			else $ImageHeight = 800;

			$output = $this->Statistic->StatisticTable($SearchFormData);

			if(isset($output['Day'])){
				$Data['Day'] = $output['Day'][0];
			}
			if(isset($output['Month'])){
				$Data['Month'] = $output['Month'][0];
			}
			if(isset($output['Year'])){
				$Data['Year'] = $output['Year'][0];
			}

			$WelderOverview = $this->Statistic->WelderOverview($Data,$SearchFormData);

			$this->set('WelderOverview',$WelderOverview);
			$this->set('return',true);
			$this->set('ImageWidth',$ImageWidth);
			$this->set('ImageHeight',$ImageHeight);

			if(count($WelderOverview) == 0) $this->render('results','blank');
			if(count($WelderOverview) == 1) $this->render('statistic/statistic_results_welder_single_overview','jpgraph');
			if(count($WelderOverview) > 1) $this->render('statistic/statistic_results_welder_overview','jpgraph');

			break;
		}
	}

	protected function __OrderResults($SearchFormData) {

		App::uses('Sanitize', 'Utility');
//		$this->autoRender = false;

		$this->loadModel('Order');
		$this->loadModel('Reportnumber');

		$projectID = $this->request->projectvars['VarsArray'][0];
		$cascadeID = $this->request->projectvars['VarsArray'][1];
		$orderID = $this->request->projectvars['VarsArray'][2];
		$reportID = $this->request->projectvars['VarsArray'][3];
		$user = AuthComponent::user();
		$lang = $this->Lang->Discription();

		$this->Autorisierung->ConditionsTopprojectsTest($projectID);

		$OderIDs = array();

		if(is_array($SearchFormData['Result']['Orders'])) $OderIDs = $SearchFormData['Result']['Orders'];

		$OderIDs = $this->Autorisierung->CheckOrdersforTestingcomp($OderIDs);

		$SearchFieldsStandard = $this->Xml->XmltoArray('search_standard', 'file', null);
		$SearchFieldsAdditional = $this->Xml->XmltoArray('search_additional', 'file', null);

		$SearchFormData = $this->Search->SearchHistoryUpdate($SearchFormData,$SearchFieldsStandard,$SearchFieldsAdditional);
		$Options = array('Order.deleted' => 0);
		$Options = array('Order.id' => $OderIDs);
		$this->paginate = array(
    		'conditions' => array($Options),
			'order' => array('id DESC'),
			'limit' => 20000
		);

		$Orders = $this->paginate('Order');

		// Wenn Expediting vorhanden ist
		$Orders = $this->Search->getExpeditingsOfOrder($Orders);

		$items = array();

		foreach($SearchFieldsStandard->fields->children() as $_SearchFields){

			if(trim($_SearchFields->model) == 'Reportnumber') continue;

			if(trim($_SearchFields->showresult) != 1) continue;

			$description = trim($_SearchFields->description->$lang);

			unset($_SearchFields->description);
			unset($_SearchFields->fieldtype);
			unset($_SearchFields->pdf);
			unset($_SearchFields->area);
			unset($_SearchFields->break);
			unset($_SearchFields->option);
			unset($_SearchFields->value);
			unset($_SearchFields->output);

			$_SearchFields->description = $description;

			$items[] = $_SearchFields;
		}

		$breads = $this->Navigation->Breads(null);
		$CascadeForBread = $this->Navigation->CascadeGetBreads(intval($cascadeID));
		$breads = $this->Navigation->CascadeCreateBreadcrumblist($breads,$CascadeForBread);
		$breads[] = array('discription'=>__('Search',true),'controller'=>'searchings','action'=>'search','pass'=>$this->request->projectvars['VarsArray']);
//		$breads[] = array('discription'=>__('Search result',true),'controller'=>'searchings','action'=>'results','pass'=>$this->request->projectvars['VarsArray']);

		$this->set('SettingsArray', array());
		$this->set('breads',$breads);
		$this->set('items',$items);
		$this->set('Data',$Orders);
		$this->set('SearchFormData',$SearchFormData);
		$this->render('order_results');
	}

	protected function __ReportResults($SearchFormData) {

		App::uses('Sanitize', 'Utility');
//		$this->autoRender = false;
		$lang = $this->Lang->Discription();

		$projectID = $this->request->projectvars['VarsArray'][0];
		$cascadeID = $this->request->projectvars['VarsArray'][1];
		$orderID = $this->request->projectvars['VarsArray'][2];
		$reportID = $this->request->projectvars['VarsArray'][3];

		$this->Autorisierung->ConditionsTopprojectsTest($projectID);
		$user = AuthComponent::user();

		$StandardModels = array('Testingmethod' => true,'Topproject' => true,'Reportnumber' => true,'Cascade' => true,'Order' => true,'Report' => true);

		unset($this->request->data['ajax_true']);
		if($SearchFormData == NULL) return;

		if(is_array($SearchFormData['Result']['Reports'])) $ReportIDs = $SearchFormData['Result']['Reports'];

		$SearchFields = $this->Xml->XmltoArray('search_additional', 'file', null);
		$SearchFieldsStandard = $this->Xml->XmltoArray('search_standard', 'file', null);

		if(!isset($this->request->data['history'])) $SearchFormData = $this->Search->SearchHistoryUpdate($SearchFormData,$SearchFieldsStandard,$SearchFields);

		$Models = array();
		$DropdownValues = array();

		$ConditionsTopprojects = $this->Autorisierung->ConditionsTopprojects();
		$SearchingIds = array();
		$SearchingValueIds = array();

		$this->loadModel('Reportnumber');
		$this->Reportnumber->recursive = -1;

//		if($user['roll_id'] > 4) $ReportnumbersOptions['Reportnumber.testingcomp_id'] = $user['testingcomp_id'];

		$this->loadModel('Reportnumber');
		$this->loadModel('Testingmethod');

		$this->Reportnumber->recursive = -1;
//pr($SearchFormData);
		$Output = $this->Search->CompanyNeWeldFilter($ReportIDs,4,$SearchFormData);

		$ReportIDs = $Output['ReportIDs'];
		$SearchFormData = $Output['SearchFormData'];

		$ReportnumbersOptions['Reportnumber.id'] = $ReportIDs;
		$ReportnumbersOptions['Reportnumber.topproject_id'] = $ConditionsTopprojects;

		$this->paginate = array(
    		'conditions' => array($ReportnumbersOptions),
			'order' => array('Reportnumber.id' => 'DESC'),
			'limit' => 20
		);

		$Reportnumbers = $this->paginate('Reportnumber');

		foreach($Reportnumbers as $_key => $_Reportnumber){

			if($_Reportnumber['Reportnumber']['testingmethod_id'] == 0) continue;

			$Testingmethod = $this->Testingmethod->find('first',array('conditions' => array('Testingmethod.id' => $_Reportnumber['Reportnumber']['testingmethod_id'])));

			$Reportnumbers[$_key]['Testingmethod'] = $Testingmethod['Testingmethod'];

			$Verfahren = ucfirst($Testingmethod['Testingmethod']['value']);

			$ReportGenerally  = 'Report' . $Verfahren . 'Generally';

			if(ClassRegistry::isKeySet($ReportGenerally) == false) $this->loadModel($ReportGenerally);

			$Generally = $this->$ReportGenerally->find('first',array('conditions' => array($ReportGenerally . '.reportnumber_id' => $_Reportnumber['Reportnumber']['id'])));

			if(count($Generally) == 0) continue;

			$Reportnumbers[$_key] = array_merge($Reportnumbers[$_key],$Testingmethod,array('Generally' => $Generally[$ReportGenerally]));

			$xml[$Reportnumbers[$_key]['Testingmethod']['value']] = $this->Xml->DatafromXml($Reportnumbers[$_key]['Testingmethod']['value'], 'file', ucfirst($Reportnumbers[$_key]['Testingmethod']['value']));

			if(Configure::read('WeldManager.show') && !isset($xml[$Reportnumbers[$_key]['Testingmethod']['value']])){
				$Reportnumbers[$_key] = $this->Data->WeldTypes($Reportnumbers[$_key],$xml);
			}


			if(Configure::check('RepairManager') && Configure::read('RepairManager') == true){
				$Reportnumbers[$_key] = $this->Repairtracking->QuickCheckRepairStatus($Reportnumbers[$_key]);
				$Reportnumbers[$_key] = $this->Repairtracking->CheckIsRepairReport($Reportnumbers[$_key]);
	 		}

			$Reportnumbers[$_key] = $this->Data->WeldTypes($Reportnumbers[$_key],$xml);

		}

		$breads = $this->Navigation->Breads(null);
		$CascadeForBread = $this->Navigation->CascadeGetBreads(intval($cascadeID));
		$breads = $this->Navigation->CascadeCreateBreadcrumblist($breads,$CascadeForBread);

		$breads[] = array('discription'=>__('Search',true),'controller'=>'searchings','action'=>'search','pass'=>$this->request->projectvars['VarsArray']);
//		$breads[] = array('discription'=>__('Result',true),'controller'=>'searchings','action'=>'results','pass'=>$this->request->projectvars['VarsArray']);

		$items = array();

		foreach($SearchFields->fields->children() as $_SearchFields){

			if(trim($_SearchFields->showresult) != 1) continue;

			$description = trim($_SearchFields->description->$lang);

			unset($_SearchFields->description);
			unset($_SearchFields->fieldtype);
			unset($_SearchFields->pdf);
			unset($_SearchFields->area);
			unset($_SearchFields->break);
			unset($_SearchFields->option);
			unset($_SearchFields->value);
			unset($_SearchFields->output);

			$_SearchFields->description = $description;

			$items[] = $_SearchFields;
		}

		unset($SearchFields);

		foreach($SearchFieldsStandard->fields->children() as $_SearchFields){

			if(trim($_SearchFields->showresult) != 1) continue;

			$description = trim($_SearchFields->description->$lang);

			unset($_SearchFields->description);
			unset($_SearchFields->fieldtype);
			unset($_SearchFields->pdf);
			unset($_SearchFields->area);
			unset($_SearchFields->break);
			unset($_SearchFields->option);
			unset($_SearchFields->value);
			unset($_SearchFields->output);

			$_SearchFields->description = $description;

			$items[] = $_SearchFields;
		}

		$SettingsArray = array();

//		$SettingsArray['statistik'] = array('discription' => __('Statistics', true), 'controller'=>'searchings', 'action'=>'statistic', 'terms'=>$this->request->projectvars['VarsArray']);
		$this->set('SettingsArray', array());
		$this->set('xml',$xml);
		$this->set('breads',$breads);
		$this->set('items',$items);
		$this->set('reportnumbers',$Reportnumbers);
		$this->set('SearchFormData',$SearchFormData);
		$this->set('SettingsArray',$SettingsArray);
		$this->render('report_results');
	}

	public function pdf() {

		$this->autoRender = false;

		$DiagrammImages = array();

		$projectID = $this->request->projectvars['VarsArray'][0];
		$cascadeID = $this->request->projectvars['VarsArray'][1];
		$orderID = $this->request->projectvars['VarsArray'][2];
		$reportID = $this->request->projectvars['VarsArray'][3];

		$PdfExport = array();
		$ReportPdf = 'searchPdf';
		$ImageHeightPx = 1250;
		$ImageWidhtPx = 2000;
		$DPI = 300;
		$Inch = 25.4;

		$SearchFieldsStandard = $this->Xml->XmltoArray('search_standard', 'file', null);
		$SearchFieldsAdditional = $this->Xml->XmltoArray('search_additional', 'file', null);

		$SearchFormData = $this->Session->read('SearchFormData');

		if(isset($SearchFormData['PdfExport'])) $PdfExport = $SearchFormData['PdfExport'];

		$months = array(1 => __('January', true),2 =>  __('February', true),3 =>  __('March', true),4 =>  __('April', true),5 =>  __('May', true),6 =>  __('June', true),7 =>  __('July', true),8 =>  __('August', true),9 =>  __('September', true),10 =>  __('October', true),11 =>  __('November', true),12 =>  __('December', true));
		$this->set('Months',$months);

		$SearchingDescription = $this->Search->ShowStandardFieldResults($SearchFieldsStandard,$SearchFormData,$Values = array());
		$SearchingDescription = $this->Search->ShowAdditionalFieldResults($SearchFieldsAdditional,$SearchFormData,$SearchingDescription);

		if(isset($SearchingDescription['Reportnumber']['date_of_test'])) unset($SearchingDescription['Reportnumber']['date_of_test']);

		$this->set('SearchingDescription',$SearchingDescription);

		if(isset($PdfExport['years']) && !empty($PdfExport['years'])) $this->request->data['Reportnumber']['years'] = $PdfExport['years'];
		if(isset($PdfExport['months']) && !empty($PdfExport['months'])) $this->request->data['Reportnumber']['months'] = $PdfExport['months'];

		$Verfahren = 'all_testingmethods';

		if(isset($this->request->data['Reportnumber']['testingmethod'])) $Verfahren = $this->request->data['Reportnumber']['testingmethod'];


		$OverviewView = new View($this);
		$OverviewView->autoRender = false;
		$OverviewView->layout = null;

		$OverviewView->set('SearchingDescription',$SearchingDescription);
		$OverviewView->set('Verfahren',$Verfahren);
		$OverviewView->set('ImageWidth',$ImageWidhtPx);
		$OverviewView->set('ImageHeight',$ImageHeightPx);
		$OverviewView->set('return', true);

		$output = $this->Statistic->StatisticTable($SearchFormData);

		if(isset($output['Day'])) {
			$Data1 = $output['Day'][1];
			$Data2['Day'] = $output['Day'][0];
			$OverviewView->set('List',$Data1);
			$DiagrammImages['overviewData']['image'] = $OverviewView->render('statistic/statistic_results_days');
			$DiagrammImages['overviewData']['headline'] = __('General overview',true);
		}
		if(isset($output['Month'])){
			$Data1 = $output['Month'][1];
			$Data2['Month'] = $output['Month'][0];
			$OverviewView->set('List',$Data1);
			$DiagrammImages['overviewData']['image'] = $OverviewView->render('statistic/statistic_results_months');
			$DiagrammImages['overviewData']['headline'] = __('General overview',true);
		}
		if(isset($output['Year'])){
			$Data1 = $output['Year'][1];
			$Data2['Year'] = $output['Year'][0];
			$OverviewView->set('List',$Data1);
			$DiagrammImages['overviewData']['image'] = $OverviewView->render('statistic/statistic_results_years');
			$DiagrammImages['overviewData']['headline'] = __('General overview',true);
		}

		if(isset($PdfExport['welding_company']) && !empty($PdfExport['welding_company'])){
			$WelderOverview = $this->Statistic->WelderOverview($Data2,$SearchFormData);
			$WelderView = new View($this);
			$WelderView->autoRender = false;
			$WelderView->layout=null;
			$WelderView->set('WelderOverview',$WelderOverview);
			$WelderView->set('return',true);
			$WelderView->set('ImageWidth',$ImageWidhtPx);
			$WelderView->set('ImageHeight',$ImageHeightPx);
			if(count($WelderOverview) == 1){
				$DiagrammImages['weldingData']['image'] = $WelderView->render('statistic/statistic_results_welder_single_overview');
				$DiagrammImages['weldingData']['headline'] = __('Overview welding company',true);
			}
			if(count($WelderOverview) > 1){
				$DiagrammImages['weldingData']['image'] = $WelderView->render('statistic/statistic_results_welder_overview');
				$DiagrammImages['weldingData']['headline'] = __('Overview welding company',true);
			}
		}

		$WelderMistakes = $this->Statistic->SearchWelderMistake($Data2);

		$Data = $WelderMistakes[1];
		$Mistakes = $WelderMistakes[0];

		$MistakesView = new View($this);
		$MistakesView->autoRender = false;
		$MistakesView->layout=null;
		$MistakesView->set('Data',$Data);
		$MistakesView->set('Mistakes',$Mistakes);
		$MistakesView->set('return',true);
		$MistakesView->set('ImageWidth',1000);
		$MistakesView->set('ImageHeight',2000);
		$MistakesView->set('SearchingDescription',$SearchingDescription);
		$MistakesView->set('return',true);

		if(count($Mistakes[key($Mistakes)]['Mistakes']['errors']) > 0){
			$DiagrammImages['mistakeData']['image'] = $MistakesView->render('statistic/statistic_results_welder_mistake');
			$DiagrammImages['mistakeData']['headline'] = __('Welding mistakes',true);
		}

		$ImagePdfWidht = trim($SearchFieldsStandard->$ReportPdf->settings->QM_CELL_LAYOUT_CLEAR) - trim($SearchFieldsStandard->$ReportPdf->settings->PDF_MARGIN_LEFT) - trim($SearchFieldsStandard->$ReportPdf->settings->QM_CELL_PADDING_R) - trim($SearchFieldsStandard->$ReportPdf->settings->QM_CELL_PADDING_L) - 1;

		foreach($DiagrammImages as $_key => $_data){
			if($_data['image'] == 'no data to display'){
				unset($DiagrammImages[$_key]);
				continue;
			}

			if(isset($_data['image']) && !empty($_data['image'])){
				$size = getimagesizefromstring($_data['image']);
				$DiagrammImages[$_key]['info'] = $size;
				unset($size);

				$ThisWidthPx = $DiagrammImages[$_key]['info'][0];
				$ThisHeightPx = $DiagrammImages[$_key]['info'][1];

				$NewWidthPx = ceil($ImagePdfWidht * $DPI / $Inch);
				$NewHeightPx = ceil($NewWidthPx * $ThisHeightPx / $ThisWidthPx);
				$NewHeightMM = round($NewHeightPx  * $Inch / $DPI,1);
				$NewWidthMM = round($ImagePdfWidht,1);

				$DiagrammImages[$_key]['info']['height'] = $NewHeightMM;
				$DiagrammImages[$_key]['info']['width'] = $NewWidthMM;

			} else {
				unset($DiagrammImages[$_key]);
			}
		}

		$this->set('SearchingDescription',$SearchingDescription);
//		$this->set('TableData', $TableData);
		$this->set('settings', $SearchFieldsStandard);
		$this->set('SearchFieldsAdditional', $SearchFieldsAdditional);

		if(isset($DiagrammImages)) $this->set('DiagrammImages', $DiagrammImages);

		$this->render('statistic/statistic_export_pdf','pdf');
	}

	public function csv() {

		$this->autoRender = false;

		$DiagrammImages = array();

		$projectID = $this->request->projectvars['VarsArray'][0];
		$cascadeID = $this->request->projectvars['VarsArray'][1];
		$orderID = $this->request->projectvars['VarsArray'][2];
		$reportID = $this->request->projectvars['VarsArray'][3];

		$SearchFieldsStandard = $this->Xml->XmltoArray('search_standard', 'file', null);
		$SearchFieldsAdditional = $this->Xml->XmltoArray('search_additional', 'file', null);

		$SearchFormData = $this->Session->read('SearchFormData');

		if(isset($SearchFormData['PdfExport'])) $PdfExport = $SearchFormData['PdfExport'];

		$months = array(1 => __('January', true),2 =>  __('February', true),3 =>  __('March', true),4 =>  __('April', true),5 =>  __('May', true),6 =>  __('June', true),7 =>  __('July', true),8 =>  __('August', true),9 =>  __('September', true),10 =>  __('October', true),11 =>  __('November', true),12 =>  __('December', true));
		$this->set('Months',$months);

		$SearchingDescription = $this->Search->ShowStandardFieldResults($SearchFieldsStandard,$SearchFormData,$Values = array());
		$SearchingDescription = $this->Search->ShowAdditionalFieldResults($SearchFieldsAdditional,$SearchFormData,$SearchingDescription);

		if(isset($SearchingDescription['Reportnumber']['date_of_test'])) unset($SearchingDescription['Reportnumber']['date_of_test']);

		$this->set('SearchingDescription',$SearchingDescription);

		if(isset($PdfExport['years']) && !empty($PdfExport['years'])) $this->request->data['Reportnumber']['years'] = $PdfExport['years'];
		if(isset($PdfExport['months']) && !empty($PdfExport['months'])) $this->request->data['Reportnumber']['months'] = $PdfExport['months'];

		$output = $this->Statistic->StatisticTable($SearchFormData);

			if(isset($output['Day'])){
				$Data['Day'] = $output['Day'][0];
				$TableData = $output['Day'][1];
			}
			if(isset($output['Month'])){
				$Data['Month'] = $output['Month'][0];
				$TableData = $output['Month'][1];
			}
			if(isset($output['Year'])){
				$Data['Year'] = $output['Year'][0];
				$TableData = $output['Year'][1];
			}

		$Verfahren = 'all_testingmethods';
		if(isset($this->request->data['Reportnumber']['testingmethod'])) $Verfahren = $this->request->data['Reportnumber']['testingmethod'];

		$WelderOverview = $this->Statistic->WelderOverview($Data,$SearchFormData);
		$WelderMistakes = $this->Statistic->SearchWelderMistake($Data);

		$data = array();
		$SearchingDescriptionString = array();

		foreach($SearchingDescription as $_key => $_data){
			if(isset($_data[key($_data)])) $data[] = array($_data[key($_data)]);

		}

		$data[] = array();
		$data[] = array(__('General overview',true));

		if(isset($PdfExport['years']) && !empty($PdfExport['years']) && isset($PdfExport['months']) && !empty($PdfExport['months'])){

			$data[] = array(__('all testingmethods',true));
			$data[] = array(__('Start',true),__('End',true),__('All welds',true),__('E-welds',true),__('NE-welds',true),__('not evaluated',true));

			foreach($TableData[key($TableData)] as $_key => $_data){

				if(!isset($_data['result']['all_testingmethods'])) continue;

				$input[] = $_data['Start'];
				$input[] = $_data['End'];
				$input[] = $_data['result']['all_testingmethods']['all_count'];
				$input[] = $_data['result']['all_testingmethods']['e_count'];
				$input[] = $_data['result']['all_testingmethods']['ne_count'];
				$input[] = $_data['result']['all_testingmethods']['non_count'];
				$data[] = $input;
				unset($input);
			}
		} else {

			$data[] = array(__('all testingmethods',true));
			$data[] = array(__('Date',true),__('All welds',true),__('E-welds',true),__('NE-welds',true),__('not evaluated',true));

			foreach($TableData as $_key => $_data){
				$input[] = $_key;
				$input[] = $_data['all_count'];
				$input[] = $_data['e_count'];
				$input[] = $_data['ne_count'];
				$input[] = $_data['non_count'];
				$data[] = $input;
				unset($input);
			}
		}

		$data[] = array();
		$data[] = array(__('Overview welders of welding company',true));
		$data[] = array(__('Welder',true),__('All welds',true),__('E-welds',true),__('NE-welds',true),__('not evaluated',true),__('Percent',true));

		$BadCharts = array(",",";","\n","\r");

		if($WelderOverview != false && count($WelderOverview) > 0){
			foreach($WelderOverview as $_key => $_data){
				$input[] =  str_replace($BadCharts, ' ', $_key);
				$input[] = $_data['e'] + $_data['ne'];
				$input[] = $_data['e'];
				$input[] = $_data['ne'];
				$input[] = $_data['non'];
				$input[] =  number_format(round(100 * $_data['ne'] / ($_data['e'] + $_data['ne']),2), 2, '.', '');
				$data[] = $input;
				unset($input);
			}
		}

		$data[] = array();
		$data[] = array(__('Overview welding mistakes',true));
		$data[] = array(__('Description',true),__('Count',true),__('Percentage',true));

		if(count($WelderMistakes[0][key($WelderMistakes[0])]['Mistakes']['errors']) > 0){
			if(isset($WelderMistakes[0][key($WelderMistakes[0])]['Mistakes'])){
				foreach($WelderMistakes[0][key($WelderMistakes[0])]['Mistakes']['description'] as $_key => $_data){
					$HelpArray[$_key] = true;
				}
				foreach($HelpArray as $_key => $_data){
					$input[] = $WelderMistakes[0][key($WelderMistakes[0])]['Mistakes']['description'][$_key];
					$input[] = $WelderMistakes[0][key($WelderMistakes[0])]['Mistakes']['errors'][$_key];
					$input[] = $WelderMistakes[0][key($WelderMistakes[0])]['Mistakes']['label'][$_key];
					$data[] = $input;
					unset($input);
				}
			}
		} else {
			$data[] = array(__('No results available.',true));
		}

//		$data = array_map_recursive('utf8_decode', $data);
		$this->Csv->exportCsv($data,'statistics.csv');

	}

	function insertdata() {
//		$this->autoRender = false;
// 		$this->layout = 'blank';
set_time_limit(0);
/*
if('DREWAG Netz GmbH' == 'DREWAG NETZ GmbH') pr('geht doch');
else pr('nö');
*/
		$projectID = $this->request->projectvars['VarsArray'][0];
		$cascadeID = $this->request->projectvars['VarsArray'][1];
		$orderID = $this->request->projectvars['VarsArray'][2];
		$reportID = $this->request->projectvars['VarsArray'][3];

		if($this->Session->check('SearchInsertData') == false){

			$SearchFieldsAdditional = $this->Xml->XmltoArray('search_additional', 'file', null);

			$this->loadModel('ReportsTopprojects');
			$this->loadModel('TestingmethodsReports');
			$this->loadModel('Testingmethod');

			$ReportsTopprojects = $this->ReportsTopprojects->find('list',array('fields' => array('report_id','report_id'), 'conditions' => array('ReportsTopprojects.topproject_id' => $projectID)));
			$TestingmethodsReports = $this->TestingmethodsReports->find('list',array('fields' => array('testingmethod_id','testingmethod_id'), 'conditions' => array('TestingmethodsReports.report_id' => $ReportsTopprojects)));
			$Testingmethod = $this->Testingmethod->find('all',array('conditions' => array('Testingmethod.id' => $TestingmethodsReports)));
//			$Testingmethod = $this->Testingmethod->find('all',array('conditions' => array('Testingmethod.id' => 20)));

			$Testingmethods = array();
			$InsertSchema = array();

			foreach($Testingmethod as $_reports){
				$Testingmethods[$_reports['Testingmethod']['value']] = true;

			}

			foreach($SearchFieldsAdditional->fields->children() as $_key => $_children){
				$field = trim($_children->key);
				foreach($Testingmethods as $__key => $__testingmethods){
					$Model = 'Report' . ucfirst($__key) . trim($_children->model);
					$this->loadModel($Model);
					$Schema = $this->$Model->schema();
					if(isset($Schema[$field])){
						array_push($InsertSchema,array('Model' => $Model, 'Field' => $field));
					}
				}
			}

			$this->Session->write('SearchInsertData',$InsertSchema);

			$breads = $this->Navigation->Breads(null);
			$CascadeForBread = $this->Navigation->CascadeGetBreads(intval($cascadeID));
			$breads = $this->Navigation->CascadeCreateBreadcrumblist($breads,$CascadeForBread);

			$breads[] = array('discription'=>__('Search',true),'controller'=>'searchings','action'=>'search','pass'=>$this->request->projectvars['VarsArray']);
			$breads[] = array('discription'=>__('Insert data',true),'controller'=>'searchings','action'=>'insertdata','pass'=>$this->request->projectvars['VarsArray']);

			$this->set('CountSearchInsertData',count($InsertSchema));
			$this->set('SearchInsertData',$InsertSchema);
			$this->set('breads',$breads);

		} elseif($this->Session->check('SearchInsertData') == true) {

 			$this->layout = 'blank';

			$SearchInsertData = $this->Session->read('SearchInsertData');

			if(count($SearchInsertData) > 0){

				$CurrentElement = array_pop($SearchInsertData);

//				$CurrentElement = array('Model' => 'ReportRtEvaluation','Field' => 'welder');

				$this->Search->SucheTestdaten($CurrentElement);

				$this->Session->write('SearchInsertData',$SearchInsertData);
				$this->set('CountSearchInsertData',count($SearchInsertData));
				$this->set('SearchInsertData',$CurrentElement);
				$this->render('inserdatablank');
			} else {

 				$this->layout = 'blank';

				$this->Session->delete('SearchInsertData');
				$this->set('stop',true);
				$this->set('CountSearchInsertData',0);
				$this->set('SearchInsertData',array('Ende'));
				$this->render('inserdatablank');

			}
		}
	}

	protected function __UpdateSearchTable(){

		$projectID = $this->request->projectvars['VarsArray'][0];
		$cascadeID = $this->request->projectvars['VarsArray'][1];
		$orderID = $this->request->projectvars['VarsArray'][2];
		$reportID = $this->request->projectvars['VarsArray'][3];

		$SearchFieldsAdditional = $this->Xml->XmltoArray('search_additional', 'file', null);
		$FieldValues = $this->Search->SearchFields($SearchFieldsAdditional,array(),'initial');

		$this->loadModel('Topproject');
		$ReportsTopprojects = $this->Topproject->Report->find('all',array('conditions' => array('Topproject.id' => $projectID)));

		die('tot');
	}
}
