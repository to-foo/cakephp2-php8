<?php
App::uses('AppController', 'Controller');
/**
 * Reports Controller
 *
 * @property Report $Report
 */
class TestinginstructionsController extends AppController
{
    public $components = array('Auth','Acl','Autorisierung','Cookie','Navigation','Lang','Sicherheit','Testinstruction','Data','Xml','SelectValue');

    public $helpers = array('Lang','Navigation','JqueryScripte');
    public $layout = 'ajax';

    public function beforeFilter()
    {
        App::import('Vendor', 'Authorize');
        $this->loadModel('User');
        $this->Autorisierung->Protect();
        $this->Navigation->ReportVars();

        $noAjaxIs = 0;
    		$noAjax = array('reason','pdf');

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

//        $this->Navigation->ajaxURL();
        $this->Lang->Choice();
        $this->Lang->Change();

        $this->set('lang', $this->Lang->Choice());
        $this->set('selected', $this->Lang->Selected());
        $this->set('menues', $this->Navigation->Menue());
        //		$this->set('breadcrumbs', $this->Navigation->Breadcrumb());
        $this->set('login_info', $this->Navigation->loggedUser());
        $this->set('lang_choise', $this->Lang->Choice());
        $this->set('previous_url', $this->base.'/'.$this->Session->read('lastURL'));
        $this->set('SettingsArray', array());

        if (isset($this->Auth)) {
            $this->set('authUser', $this->Auth);
        }
    }

    public function afterFilter()
    {
        $this->Navigation->lastURL();
    }

    public function beforeRender()
    {
        $model = Inflector::singularize($this->name);
    }

    public function index() {

      if(isset($this->request->data['landig_page']) && $this->request->data['landig_page'] == 1) $landig_page = 1;
  		if(isset($this->request->data['landig_page_large']) && $this->request->data['landig_page_large'] == 1) $landig_page_large = 1;

      $Authorization = $this->Testinstruction->Authorization($topproject_id = array(), $report_id = array(), $testingmethod_id = array(), $testingcomp_id = array(AuthComponent::user('testingcomp_id')));

      $Conditions['Testinginstruction.id'] = $Authorization['TestingstructionIDs'];
      $Conditions['Testinginstruction.deleted'] = 0;
      $this->paginate = array(
          'conditions' => array($Conditions),
          'order' => array('Testinginstruction.name' => 'desc'),
          'limit' => 25
      );

      $Testinginstructions = $this->paginate('Testinginstruction');

      $this->loadModel('Topproject');
      $this->loadModel('Report');
      $this->loadModel('Testingmethod');
      $this->loadModel('Testingcomp');

      $this->Topproject->recursive = -1;
      $this->Report->recursive = -1;
      $this->Testingmethod->recursive = -1;
      $this->Testingcomp->recursive = -1;
      foreach ($Testinginstructions as $_key => $_data) {
          $Result = Hash::extract($Authorization, 'Result.{n}.TestinginstructionsAuthorization[testingstruction_id= ' . $_data['Testinginstruction']['id'] . ']');

          if (count($Result) == 0) {
              continue;
          }

          foreach ($Result as $__key => $__data) {
              $Linked = array();
              $Linked = array_merge($Linked, $this->Topproject->find('first', array('conditions' => array('Topproject.id' => $__data['topproject_id']))));
              $Linked = array_merge($Linked, $this->Report->find('first', array('conditions' => array('Report.id' => $__data['report_id']))));
              $Linked = array_merge($Linked, $this->Testingmethod->find('first', array('conditions' => array('Testingmethod.id' => $__data['testingmethod_id']))));
              $Linked = array_merge($Linked, $this->Testingcomp->find('first', array('conditions' => array('Testingcomp.id' => $__data['testingcomp_id']))));
              $Testinginstructions[$_key]['Linked'][] = $Linked;
          }
      }

      $breads = $this->Navigation->Breads(null);

      $breads[] = array(
              'discription' => __('Testing instructions', true),
              'controller' => 'testinginstructions',
              'action' => 'index',
              'pass' => null
      );

      $this->set('breads', $breads);
      $this->set('Testinginstructions', $Testinginstructions);

      if(isset($landig_page_large) && $landig_page_large == 1){

  			$this->render('landingpage_large','blank');
  			return;

  		}		      
    }

    public function add() {

      $this->layout = 'modal';
      $arrayData = $this->Xml->DatafromXml('Testinginstruction', 'file', null);
      $arrayDataDetail = $this->Xml->DatafromXml('TestinginstructionsData', 'file', null);

      $locale = $this->Lang->Discription();
      $id = $this->request->projectvars['VarsArray'][15];

      $user = $this->Auth->user('id');

      if (isset($this->request->data['Testinginstruction']) || $this->request->is('put')) {

        $this->request->data['Testinginstruction']['user_id'] = $user;

        $this->loadModel('TestinginstructionsAuthorization');

        $this->Testinginstruction->create();

        if ($this->Testinginstruction->save($this->request->data)) {

          $id = $this->Testinginstruction->getInsertID();

          $authorisation = $this->Testinginstruction->UpdateTestinginstructionAuthorization($this->request->data['Testinginstruction'],$id);
          $this->Testinstruction->SaveAuthorization($authorisation,$id);

          $this->request->data['Testinginstruction']['testingstruction_id'] = $id;

          $this->request->projectvars['VarsArray'][15] = $id;

          $this->Autorisierung->Logger($this->request->data['Testinginstruction']['testingstruction_id'], $this->request->data);

          $FormName = array();
          $FormName['controller'] = 'testinginstructions';
          $FormName['action'] = 'edit';
          $FormName['terms'] = implode('/',$this->request->projectvars['VarsArray']);

          $this->set('FormName', $FormName);

          $this->Session->setFlash(__('The testinginstruction has been saved'));
        } else {
          $this->Session->setFlash(__('The testinginstruction could not be saved. Please, try again.'));
        }
      }

      // Das Array für das Settingsmenü
      $SettingsArray = array();

      $this->loadModel('Testingmethods');
      $this->loadModel('Testingcomp');
      $this->loadModel('Report');
      $this->loadModel('Topproject');

      $testingmethods = $this->Testingmethods->find('list',array('fields' => array('id','verfahren'),'order' => array('verfahren')));
      $testingcomps = $this->Testingcomp->find('list',array('fields' => array('id','name'),'order' => array('name')));
      $reports = $this->Report->find('list',array('fields' => array('id','name'),'order' => array('name')));
      $topprojects = $this->Topproject->find('list',array('fields' => array('id','projektname'),'order' => array('projektname')));

      $this->request->data['Testinginstruction']['status'] = 0;
      $this->request->data['Testingcomp']['selected'] = array($this->Auth->user('testingcomp_id') => $this->Auth->user('testingcomp_id'));
//      $this->request->data['Topproject']['selected'] = array(8 => 8);
      $this->request->data['Testingmethod']['selected'] = array();
      $this->request->data['Report']['selected'] = array();

      $this->set(compact('testingcomps', 'reports','topprojects','testingmethods'));
      $this->set('SettingsArray', $SettingsArray);
      $this->set('settings', $arrayData['headoutput']);
      $this->set('locale', $locale);
    }

    public function edit($id = null) {

        $arrayData = $this->Xml->DatafromXml('Testinginstruction', 'file', null);
        $arrayDataDetail = $this->Xml->DatafromXml('TestinginstructionsData', 'file', null);
        $locale = $this->Lang->Discription();
        $id = $this->request->projectvars['VarsArray'][15];

        $this->loadModel('Testingmethods');
        $this->loadModel('Testingcomp');
        $this->loadModel('Report');
        $this->loadModel('Topproject');
        $this->loadModel('TestinginstructionsAuthorization');

        $testingmethods = $this->Testingmethods->find('list',array('fields' => array('id','verfahren'),'order' => array('verfahren')));
        $testingcomps = $this->Testingcomp->find('list',array('fields' => array('id','name'),'order' => array('name')));
        $reports = $this->Report->find('list',array('fields' => array('id','name'),'order' => array('name')));
        $topprojects = $this->Topproject->find('list',array('fields' => array('id','projektname'),'order' => array('projektname')));

        if (!$this->Testinginstruction->exists($id)) {
            throw new NotFoundException(__('Invalid testing instruction'));
        }

        if (isset($this->request->data['Testinginstruction']) || $this->request->is('put')) {
            //			$this->Autorisierung->ConditionsTest('ReportsTopprojects','report_id',$this->request->data['Report']['id']);

            if ($this->Testinginstruction->save($this->request->data)) {
                $this->Autorisierung->Logger($this->request->data['Testinginstruction']['id'], $this->request->data);

                $user = $this->Auth->user('id');
                $id = $this->request->data['Testinginstruction']['id'];

                $authorisation = $this->Testinginstruction->UpdateTestinginstructionAuthorization($this->request->data['Testinginstruction'],$id);
                $this->Testinstruction->SaveAuthorization($authorisation,$id);

                $this->Session->setFlash(__('The testinginstruction has been saved'));

            } else {
                $this->Session->setFlash(__('The testinginstruction could not be saved. Please, try again.'));
            }
        }

        $options =
            array(
                'conditions' => array(
                    'Testinginstruction.'.$this->Testinginstruction->primaryKey => $id
                )
            );

        $Testinginstruction = $this->Testinginstruction->find('first', $options);

        $Testinginstruction = $this->Data->BelongsToManySelected($Testinginstruction, 'Testinginstruction', 'Testingcomp', array('TestinginstructionsAuthorizations','testingcomp_id','testingstruction_id'));
        $Testinginstruction = $this->Data->BelongsToManySelected($Testinginstruction, 'Testinginstruction', 'Report', array('TestinginstructionsAuthorizations','report_id','testingstruction_id'));
        $Testinginstruction = $this->Data->BelongsToManySelected($Testinginstruction, 'Testinginstruction', 'Topproject', array('TestinginstructionsAuthorizations','topproject_id','testingstruction_id'));
        $Testinginstruction = $this->Data->BelongsToManySelected($Testinginstruction, 'Testinginstruction', 'Testingmethod', array('TestinginstructionsAuthorizations','testingmethod_id','testingstruction_id'));

        $Testingmethod = $this->Testingmethods->find('first',array('conditions' => array('Testingmethods.id' => $Testinginstruction['Testinginstruction']['testingmethod_id'])));

        $verfahren = $Testingmethod['Testingmethods']['value'];
        $Verfahren = Inflector::camelize($verfahren);
        $testingmethod_xml = $this->Xml->DatafromXml($verfahren,'file',$Verfahren);

        $Testinginstruction = $this->Testinstruction->MergeDescriptions($Testinginstruction,$testingmethod_xml,$Testingmethod,$locale);
        $Testinginstruction = $this->Testinstruction->AreaDivision($Testinginstruction,$Testingmethod);

        if(isset($this->request->data['data_area']) && !empty($this->request->data['data_area'])) $Testinginstruction['data_area'] = $this->request->data['data_area'];

        $this->request->data = $Testinginstruction;

        // Das Array für das Settingsmenü
        $SettingsArray = array();

        $breads = $this->Navigation->Breads(null);

        $breads[] = array(
                'discription' => __('Testing instructions', true),
                'controller' => 'testinginstructions',
                'action' => 'index',
                'pass' => null
        );

        $breads[] = array(
                'discription' => $Testinginstruction['Testinginstruction']['name'],
                'controller' => 'testinginstructions',
                'action' => 'edit',
                'pass' => $this->request->projectvars['VarsArray']
        );

        $this->set(compact('testingcomps', 'reports','topprojects','testingmethods'));
        $this->set('breads', $breads);
        $this->set('SettingsArray', $SettingsArray);
        $this->set('settings', $arrayData['headoutput']);
        $this->set('locale', $locale) ;
    }

    public function adddata() {

      $arrayData = $this->Xml->DatafromXml('TestinginstructionsData', 'file', null);
      $locale = $this->Lang->Discription();
      $testinginstruction_id = $this->request->projectvars['VarsArray'][15];
      $id = $this->request->projectvars['VarsArray'][16];
      $this->layout = 'modal';

      if (isset($this->request->data['TestinginstructionsData']) || $this->request->is('put')) {

          $this->request->data['TestinginstructionsData']['testinginstruction_id'] = $testinginstruction_id;

          if ($this->Testinginstruction->TestinginstructionsData->save($this->request->data)) {

            $id = $this->Testinginstruction->TestinginstructionsData->getLastInsertId();

            $options =
                array(
                    'conditions' => array(
                        'TestinginstructionsData.'.$this->Testinginstruction->TestinginstructionsData->primaryKey => $id
                    )
                );

            $TestinginstructionsData = $this->Testinginstruction->TestinginstructionsData->find('first', $options);

            $this->Autorisierung->Logger($TestinginstructionsData['TestinginstructionsData']['id'], $this->request->data);

            $FormName = array();
            $FormName['controller'] = 'testinginstructions';
            $FormName['action'] = 'edit';
            $FormName['terms'] = implode('/',$this->request->projectvars['VarsArray']);

            if(isset($this->request->data['TestinginstructionsData']['data_area']) && !empty($this->request->data['TestinginstructionsData']['data_area'])){
              $this->request->data['data_area'] = $this->request->data['TestinginstructionsData']['data_area'];
            }

            $this->set('FormName', $FormName);

            $this->Session->setFlash(__('The testinginstruction has been saved'));
          } else {
              $this->Session->setFlash(__('The testinginstruction could not be saved. Please, try again.'));
          }
      }

      $options =
          array(
              'conditions' => array(
                  'Testinginstruction.'.$this->Testinginstruction->primaryKey => $testinginstruction_id
              )

          );

      $Testinginstruction = $this->Testinginstruction->find('first', $options);

      $UsedFields = Hash::extract($Testinginstruction['TestinginstructionsData'], '{n}.field');

      $this->loadModel('Testingmethods');
      $Testingmethod = $this->Testingmethods->find('first',array('conditions' => array('Testingmethods.id' => $Testinginstruction['Testinginstruction']['testingmethod_id'])));
      $verfahren = $Testingmethod['Testingmethods']['value'];
      $Verfahren = Inflector::camelize($verfahren);
      $testingmethod_xml = $this->Xml->DatafromXml($verfahren,'file',$Verfahren);

      $Models = $this->Testinstruction->CreateModels($Verfahren);


      $ModelOption = array();

      foreach ($Models as $key => $value) {
        $ModelOption[$value['model']] = $value['description'];
      }

      $FieldOptions = $this->Testinstruction->CreateFieldOptions($testingmethod_xml,$ModelOption,array(),$UsedFields,$locale);

      $TypeOptions[0] = 'Erläuterung';
      $TypeOptions[1] = 'Hinweis';
      $TypeOptions[2] = 'Pflichtfeld';

      $this->request->data['TestinginstructionsData']['testinginstruction_id'] = $this->request->projectvars['VarsArray'][15];

//      $this->request->data = $TestinginstructionsData;

      // Das Array für das Settingsmenü
      $SettingsArray = array();
      $SettingsArray['dellink'] = array('discription' => __('Delete', true), 'controller' => 'testinginstructions','action' => 'deletedata', 'terms' => $this->request->projectvars['VarsArray']);

      $this->set('TypeOptions', $TypeOptions);
      $this->set('FieldOptions', $FieldOptions);
      $this->set('ModelOption', $ModelOption);
      $this->set('SettingsArray', $SettingsArray);
//      $this->set('settings', $arrayData['headoutput']);
      $this->set('locale', $locale) ;
    }

    public function editdata($id = null)
    {
        $arrayData = $this->Xml->DatafromXml('TestinginstructionsData', 'file', null);
        $locale = $this->Lang->Discription();
        $testinginstruction_id = $this->request->projectvars['VarsArray'][15];
        $id = $this->request->projectvars['VarsArray'][16];
        $this->layout = 'modal';

        //$contitions = array('conditions' => array('id' => $this->Autorisierung->ConditionsTopprojects()));

        if (!$this->Testinginstruction->TestinginstructionsData->exists($id)) {
            throw new NotFoundException(__('Invalid testingstruction'));
        }

        if(isset($this->request->data['TestinginstructionsData']) || $this->request->is('put')) {
            //			$this->Autorisierung->ConditionsTest('ReportsTopprojects','report_id',$this->request->data['Report']['id']);
            if ($this->Testinginstruction->TestinginstructionsData->save($this->request->data)) {
                $this->Autorisierung->Logger($this->request->data['TestinginstructionsData']['id'], $this->request->data);

                $user = $this->Auth->user('id');
                $id = $this->request->data['TestinginstructionsData']['id'];

                if(isset($this->request->data['TestinginstructionsData']['data_area']) && !empty($this->request->data['TestinginstructionsData']['data_area'])){
                  $this->request->data['data_area'] = $this->request->data['TestinginstructionsData']['data_area'];
                }

                $FormName = array();
    	 					$FormName['controller'] = 'testinginstructions';
    	 					$FormName['action'] = 'edit';
    	 					$FormName['terms'] = implode('/',$this->request->projectvars['VarsArray']);

    	 					$this->set('FormName', $FormName);

                $this->Session->setFlash(__('The testinginstruction has been saved'));
            } else {
                $this->Session->setFlash(__('The testinginstruction could not be saved. Please, try again.'));
            }
        }

        $options =
            array(
                'conditions' => array(
                    'Testinginstruction.'.$this->Testinginstruction->primaryKey => $testinginstruction_id
                )

            );

        $Testinginstruction = $this->Testinginstruction->find('first', $options);

        $UsedFields = Hash::extract($Testinginstruction['TestinginstructionsData'], '{n}.field');

        $options =
            array(
                'conditions' => array(
                    'TestinginstructionsData.'.$this->Testinginstruction->TestinginstructionsData->primaryKey => $id
                )

            );

        $TestinginstructionsData = $this->Testinginstruction->TestinginstructionsData->find('first', $options);

        $this->loadModel('Testingmethods');
        $Testingmethod = $this->Testingmethods->find('first',array('conditions' => array('Testingmethods.id' => $Testinginstruction['Testinginstruction']['testingmethod_id'])));
        $verfahren = $Testingmethod['Testingmethods']['value'];
        $Verfahren = Inflector::camelize($verfahren);
        $testingmethod_xml = $this->Xml->DatafromXml($verfahren,'file',$Verfahren);

        $Models = $this->Testinstruction->CreateModels($Verfahren);

        $ModelOption = array();

        foreach ($Models as $key => $value) {
  //        if($value['model'] != $TestinginstructionsData['TestinginstructionsData']['model']) continue;
          $ModelOption[$value['model']] = $value['description'];
        }

        $FieldOptions = $this->Testinstruction->CreateFieldOptions($testingmethod_xml,$ModelOption,$TestinginstructionsData,$UsedFields,$locale);

        $TypeOptions[0] = 'Erläuterung';
        $TypeOptions[1] = 'Hinweis';
        $TypeOptions[2] = 'Pflichtfeld';

        if(isset($this->request->data['data_area']) && !empty($this->request->data['data_area'])) $TestinginstructionsData['data_area'] = $this->request->data['data_area'];

        $this->request->data = $TestinginstructionsData;
        // Das Array für das Settingsmenü
        $SettingsArray = array();
        $SettingsArray['dellink'] = array('discription' => __('Delete', true), 'controller' => 'testinginstructions','action' => 'deletedata', 'terms' => $this->request->projectvars['VarsArray']);

        $this->set('TypeOptions', $TypeOptions);
        $this->set('FieldOptions', $FieldOptions);
        $this->set('ModelOption', $ModelOption);
        $this->set('SettingsArray', $SettingsArray);
//        $this->set('settings', $arrayData['headoutput']);
        $this->set('locale', $locale) ;
    }

    public function reason() {

      if(isset($this->request->data['delete']) && $this->request->data['delete'] = 1){
        $this->__reasondelete();
      } else {
        $this->__reasonupdate();
      }

    }

    protected function __reasondelete() {

      $this->layout = 'blank';

      $projectID = $this->request->projectvars['VarsArray'][0];
  		$cascadeID = $this->request->projectvars['VarsArray'][1];
  		$orderID = $this->request->projectvars['VarsArray'][2];
  		$reportID = $this->request->projectvars['VarsArray'][3];
  	 	$reportnumberID = $this->request->projectvars['VarsArray'][4];

      $Response = array();

      $dataId = $this->request->data['data_id'];

      $this->loadModel('TestinginstructionsIrregularitie');

//  	 	$this->Autorisierung->IsThisMyReport($id);

      $options = array(
        'conditions' => array(
          'TestinginstructionsData.'.$this->Testinginstruction->TestinginstructionsData->primaryKey => $this->request->data['data_id']
        )
      );

      $TestinginstructionsData = $this->Testinginstruction->TestinginstructionsData->find('first', $options);

      if(count($TestinginstructionsData) == 0){
        $this->set('request',json_encode($Response));
        $this->render();
        return;
      }

      $Response = array_merge($Response, $TestinginstructionsData);


      $options = array(
        'conditions' => array(
          'TestinginstructionsIrregularitie.reportnumber_id' => $reportnumberID,
          'TestinginstructionsIrregularitie.testinginstruction_data_id' => $dataId,
        )
      );

      $TestingstructionIrregularitie = $this->TestinginstructionsIrregularitie->find('first', $options);

      if(count($TestingstructionIrregularitie) == 0){
        $this->set('request',json_encode($Response));
        $this->render();
        return;
      }

      $Response = array_merge($Response, $TestingstructionIrregularitie);

      $this->TestinginstructionsIrregularitie->delete($TestingstructionIrregularitie['TestinginstructionsIrregularitie']['id']);

      $this->set('request',json_encode($Response));

    }

    protected function __reasonupdate() {

      $this->layout = 'blank';

      $projectID = $this->request->projectvars['VarsArray'][0];
  		$cascadeID = $this->request->projectvars['VarsArray'][1];
  		$orderID = $this->request->projectvars['VarsArray'][2];
  		$reportID = $this->request->projectvars['VarsArray'][3];
  	 	$reportnumberID = $this->request->projectvars['VarsArray'][4];

      $this->loadModel('TestinginstructionsIrregularitie');

//  	 	$this->Autorisierung->IsThisMyReport($id);

      $options = array(
              'conditions' => array(
                  'TestinginstructionsData.'.$this->Testinginstruction->TestinginstructionsData->primaryKey => $this->request->data['data_id']
              )

          );

      $TestinginstructionsData = $this->Testinginstruction->TestinginstructionsData->find('first', $options);

      $Insert = array(
        'testinginstruction_id' => $TestinginstructionsData['TestinginstructionsData']['testinginstruction_id'],
        'testinginstruction_data_id' => $TestinginstructionsData['TestinginstructionsData']['id'],
        'reportnumber_id' => $reportnumberID,
        'reason' => $this->request->data['value'],
      );

      $options = array(
              'conditions' => array(
                'TestinginstructionsIrregularitie.reportnumber_id' => $reportnumberID,
                'TestinginstructionsIrregularitie.testinginstruction_data_id' => $TestinginstructionsData['TestinginstructionsData']['id'],
              )

          );

      $TestingstructionIrregularitie = $this->TestinginstructionsIrregularitie->find('first', $options);

      if(count($TestingstructionIrregularitie) > 0){
        $Insert['id'] = $TestingstructionIrregularitie['TestinginstructionsIrregularitie']['id'];
      } else {
        $this->TestinginstructionsIrregularitie->create();
      }

      $this->TestinginstructionsIrregularitie->save($Insert);

      $this->set('request',$this->request->data['value']);

      $this->render();

    }

    public function delete()
    {
    }

    public function deletedata()
    {
    }


    public function pdf() {

      if(isset($this->request->data['showpdf']) && $this->request->data['showpdf'] == 1) $ShowPdf = 1;

      $this->layout = 'pdf';
      $locale = $this->Lang->Discription();
      $arrayData = $this->Xml->DatafromXml('Testinginstruction', 'file', null);
      $arrayDataDetail = $this->Xml->DatafromXml('TestinginstructionsData', 'file', null);
      $locale = $this->Lang->Discription();
      $id = $this->request->projectvars['VarsArray'][15];
      if (!$this->Testinginstruction->exists($id)) {
        throw new NotFoundException(__('Invalid testing instruction'));
      }

      $options =
          array(
              'conditions' => array(
                  'Testinginstruction.'.$this->Testinginstruction->primaryKey => $id
              )
          );
      $tinstruction = $this->Testinginstruction->find('first',$options);
      if (!empty($tinstruction)&& !empty($tinstruction['TestinginstructionsData'])) {
        $this->loadModel('Testingmethod');
        $testingmethod = $this->Testingmethod->find('first',array('conditions'=>array('Testingmethod.id'=> $tinstruction['Testinginstruction']['testingmethod_id'] )));

        if(!empty($testingmethod)) {$testingmethod = ucfirst ($testingmethod['Testingmethod']['value']);}
        $pdfdata = array();
        $pdfdata ['name'] = $tinstruction ['Testinginstruction'] ['name'];
        $pdfdata ['description'] = $tinstruction ['Testinginstruction'] ['description'];
        $models = array('Generally'=>'Report'.$testingmethod.'Generally', 'Specific' => 'Report'.$testingmethod.'Specific','Evaluation'=>'Report'.$testingmethod.'Evaluation');
        $arrayDatatestingmethod = array();
        $arrayDatatestingmethod = $this->Xml->DatafromXml(strtolower($testingmethod),'file',$testingmethod);

        $arrayDatatestingmethod = $arrayDatatestingmethod ['settings'];
        foreach ($models as $key => $value) {
          $Modeldata =   Hash::extract($tinstruction ['TestinginstructionsData'], '{n}[model='.$value.']');

        foreach ($Modeldata as $mdkey => $mdvalue) {
          $modeltestingmethod = $mdvalue['model'];
          $fieldtestingmethod = $mdvalue ['field'];
          if(isset($arrayDatatestingmethod->$modeltestingmethod->$fieldtestingmethod)){

            $description = $arrayDatatestingmethod->$modeltestingmethod->$fieldtestingmethod->discription->$locale;

            $Modeldata[$mdkey]['field'] = trim($description);
          }

        }

        if(!empty($Modeldata)){ $pdfdata ['data'] [__($key)] = $Modeldata;}
        }
        $this->loadModel('Testingcomp');
		     $options = array(
						'conditions' => array(
											'Testingcomp.id' =>  $this->Auth->user('Testingcomp.id'),
										)
						);
		     $this->Testingcomp->recursive = -1;
		     $Testingcomp = $this->Testingcomp->find('first',$options);

         if(Configure::read('company_logo_folder') . $this->Auth->user('Testingcomp.id') . DS . 'logo.svg'){
           $pdfdata['Testingcomp'] = $this->Auth->user('Testingcomp');
			     $pdfdata['Testingcomp']['logo'] = Configure::read('company_logo_folder') . $this->Auth->user('Testingcomp.id') . DS . 'logo.svg';
		      }



      }

      if(isset($ShowPdf) && $ShowPdf == 1){

  			$this->layout = 'json';

  			$this->autoRender = false;

  			$PDFView = new View($this);
  			$PDFView->autoRender = false;
  			$PDFView->layout = null;

        $PDFView->set('return_show',true);
        $PDFView->set('pdfdata',$pdfdata);
        $PDFView->set('arrayDataDetail', $arrayDataDetail['settings']);

  			$Data = $PDFView->render('pdf');

  			$this->set('request',json_encode(array('string' => $Data)));
  			$this->render('pdf_show');

  			return;

  		}

      $this->set('pdfdata',$pdfdata);
      $this->set('arrayDataDetail', $arrayDataDetail['settings']);

    }

}
