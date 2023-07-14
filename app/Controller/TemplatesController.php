<?php
App::uses('AppController', 'Controller');
/**
 * Weldingmethods Controller
 *
 * @property Weldingmethod $Weldingmethod
 */
class TemplatesController extends AppController
{
    public $components = array('Auth','Acl','Autorisierung','Cookie','Navigation','Lang','Sicherheit','Xml','Data','Templates','Drops','Image');
    protected $writeprotection = false;

    public $helpers = array('Lang','Navigation','JqueryScripte');
    public $layout = 'ajax';

    public function beforeFilter()
    {

        App::import('Vendor', 'Authorize');
        App::uses('Folder', 'Utility');
        App::uses('File', 'Utility');
        $this->Navigation->ReportVars();
        $this->loadModel('User');
        $this->loadModel('Testingmethod');
        $this->loadModel('Reportnumber');
        $this->loadModel('TemplatesEvaluation');

        $this->Autorisierung->Protect();

        $noAjaxIs = 0;
    		$noAjax = array('json','quicksearch');

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

        $lang = $this->Lang->Discription();
    		$this->request->lang = $lang;

        $this->Lang->Choice();
        $this->Lang->Change();
        $this->set('lang', $this->Lang->Choice());
        $this->set('selected', $this->Lang->Selected());
        $this->set('menues', $this->Navigation->Menue());
        //		$this->set('breadcrumbs', $this->Navigation->Breadcrumb());
        $this->set('login_info', $this->Navigation->loggedUser());
        $this->set('lang_choise', $this->Lang->Choice());
        $this->set('previous_url', $this->base.'/'.$this->Session->read('lastURL'));
        $this->loadModel('User');
        $this->loadModel('Testingcomp');
        $testingcomps = $this->Testingcomp->find('list');
        $users = $this->User->find('list');

        $this->set(compact('users'));
        $this->set(compact('testingcomps'));

        if (isset($this->Auth)) {
            $this->set('authUser', $this->Auth);
        }
    }

    public function afterFilter()
    {
        $this->Navigation->lastURL();
    }

    public function quicksearch() {

  		App::uses('Sanitize', 'Utility');

   		$this->layout = 'json';

  		if(isset($this->request->data['term'])) $term = Sanitize::stripAll($this->request->data['term']);

  		if(!isset($term)){
  			$this->set('test',json_encode(array()));
  			return;
  		}

  		$term = Sanitize::escape($term);

      $this->Template->recursive = -1;

      $Template = $this->Template->find('all',array(
        'fields' => array('id','name','testingmethod_id'),
        'conditions' => array(
            'Template.name LIKE' => '%' . $term . '%'
          )
        )
      );

      $response = array();

      foreach ($Template as $key => $value) {
        $Template[$key] = $this->Templates->CollectTestingmethods($Template[$key]);
        $Template[$key] = $this->Templates->CollectTestingcoms($Template[$key]);
      }

      $Template = $this->Templates->CheckTestingcoms($Template);

  		foreach($Template as $key => $value){
  			array_push($response, array('key' => $value['Template']['id'] . '_1','value' => $value['Template']['name'] . ' ' . $value['Testingmethod']['verfahren'] . ' (' . __('General Template') . ')'));
  		}

      $Template = $this->TemplatesEvaluation->find('all',array(
        'fields' => array('id','name'),
        'conditions' => array(
				  	'TemplatesEvaluation.testingcomp_id' => $this->Auth->user('testingcomp_id'),
            'TemplatesEvaluation.name LIKE' => '%' . $term . '%'
          )
        )
      );


      foreach($Template as $key => $value){
  			array_push($response, array('key' => $key . '_2','value' => $value . ' (' . __('Evaluation Template') . ')'));
  		}

  		$this->set('response',json_encode($response));
  	}

    /**
     * index method
     *
     * @return void
     */
    public function index($id = 0)
    {
      $this->layout = 'blank';

      if(isset($this->request->data['Quicksearch']['this_id'])){

        $this_id = explode('_',$this->request->data['Quicksearch']['this_id']);
        $id = intval($this_id[1]);

      }

      $this->_index_template($id);
      $this->_index_evaluation_template($id);

    }

    protected function _index_template($id){

      if(empty($id)) return;
      if($id != 1) return;

      $this->layout = 'ajax';

      $locale = $this->Lang->Discription();
      $Templates = $this->Templates->FindTemplates();

      foreach ($Templates as $key => $value) {
        $Templates[$key] = $this->Templates->CollectTestingmethods($Templates[$key]);
        $Templates[$key] = $this->Templates->CollectTestingcoms($Templates[$key]);
      }

      $Templates = $this->Templates->CheckTestingcoms($Templates);

      $this->request->data = $Templates;

      $breads = $this->Navigation->Breads(null);

      $breads[] = array(
              'discription' => __('Template manager', true),
              'controller' => 'templates',
              'action' => 'index',
              'pass' => 1
      );

      $this->set('breads', $breads);
      $this->set('SettingsArray', array());
      $this->set('locale', $locale);

      $this->render('index_template');

    }

    protected function _index_evaluation_template($id){

      if(empty($id)) return;
      if($id != 2) return;

      $this->layout = 'ajax';

//      $this->Templates->FindTemplateEvaluationTestingcomp();

      $locale = $this->Lang->Discription();
      $Templates = $this->Templates->FindEvaluationTemplates();
      $this->request->data = $Templates;

      $breads = $this->Navigation->Breads(null);

      $breads[] = array(
              'discription' => __('Template manager', true),
              'controller' => 'templates',
              'action' => 'index',
              'pass' => 2
      );

      $this->set('breads', $breads);
      $this->set('SettingsArray', array());
      $this->set('locale', $locale);

      $this->render('index_evaluation_template');

    }

    /**
     * view method
     *
     * @throws NotFoundException
     * @param string $id
     * @return void
     */

    /**
     * add method
     *
     * @return void
     */
    public function add()
    {

      $this->layout = 'modal';
      $locale = $this->Lang->Discription();

      $projectID = $this->request->projectvars['VarsArray'][0];
  	 	$cascadeID = $this->request->projectvars['VarsArray'][1];
  	 	$orderID = $this->request->projectvars['VarsArray'][2];
  	 	$reportID = $this->request->projectvars['VarsArray'][3];
  	 	$id = $this->request->projectvars['VarsArray'][4];

      if (!$this->Reportnumber->exists($id)) {
  	 		throw new NotFoundException(__('Invalid reportnumber'));
  	 	}

  	 	$options = array('conditions' => array('Reportnumber.' . $this->Reportnumber->primaryKey => $id));
  	 	$this->Reportnumber->recursive = 0;
  	 	$reportnumber = $this->Reportnumber->find('first', $options);

      $verfahren = $reportnumber['Testingmethod']['value'];
      $Verfahren = ucfirst($reportnumber['Testingmethod']['value']);
      $reportnumber['TemplateModels'] = array('Report' . $Verfahren . 'Generally','Report' . $Verfahren . 'Specific');

      $xml = $this->Xml->DatafromXml($verfahren,'file',$Verfahren);

  	 	$reportnumber = $this->Data->GetReportData($reportnumber,array('Generally','Specific','Evaluation'));
      $reportnumber = $this->Templates->RemoveEvaluationResults($reportnumber);

//      $errors = $this->Reportnumber->getValidationErrors($xml, $reportnumber, $verfahren);
      $errors = array();

      $this->set('SettingsArray', array());

      if(isset($this->request->data['Template'])){

        if($this->Templates->SaveTemplate($reportnumber) === true){

          $this->render('add_success');
          return;

        } else {

          $SubmitDescription = __('Submit',true);

        }

      } else {

        if(count($errors) == 0) $this->Flash->hint(__('Should this report be saved as a template?') . ' ' . __('The saved template can be edited later.'), array('key' => 'hint'));
        elseif(count($errors) > 0) $this->Flash->error(__('The test report was not filled out completely. Only fully completed reports can be used for a template.'), array('key' => 'error'));

        $SubmitDescription = __('Submit',true);

      }

      $parts_set = array('Generally','Specific');

  	 	foreach($xml['settings'] as $_key => $_settings){
  	 		$part = explode($Verfahren,$_key);
  	 		if(isset($part[1])) $parts_is[] = $part[1];
  	 	}

  	 	$parts = array_intersect($parts_set, $parts_is);


      $this->request->data = $reportnumber;

      $this->set('parts', $parts);
      $this->set('SubmitDescription', $SubmitDescription );
      $this->set('errors', $errors);
      $this->set('reportnumber', $reportnumber );

    }

    public function show($id = null)
    {
      $this->_showmodal($id);
      $this->_attentionevaluation($id);
    }

    public function _attentionevaluation($id)
    {

      if(!isset($this->request->data['find_attention'])) return;

      $this->layout = 'blank';

      $reportnumber_id = $this->request->data['reportnumber_id'];
      $data = array();

      $this->loadModel('Reportnumber');
      $this->loadModel('TemplatesEvaluationReportnumber');

      $this->Reportnumber->recursive = -1;
      $this->Testingmethod->recursive = -1;

      $Reportnumber = $this->Reportnumber->find('first',array('conditions' => array('Reportnumber.id' => $reportnumber_id)));
      $Testingmethod = $this->Testingmethod->find('first',array('conditions' => array('Testingmethod.id' => $Reportnumber['Reportnumber']['testingmethod_id'])));

      $verfahren = $Testingmethod['Testingmethod']['value'];
  		$Verfahren = ucfirst($Testingmethod['Testingmethod']['value']);
      $Model = 'Report' . $Verfahren . 'Evaluation';

      $this->loadModel($Model);

      if($id == 0) $Evaluation = $this->{$Model}->find('list',array('conditions' => array($Model . '.reportnumber_id' => $reportnumber_id)));
      elseif($id > 0) $Evaluation = $id;

      $TemplatesEvaluation = $this->TemplatesEvaluationReportnumber->find('all',array('conditions' => array('TemplatesEvaluationReportnumber.evaluation_id' => $Evaluation)));

      $EvaluationIds = array_flip(Hash::extract($TemplatesEvaluation, '{n}.TemplatesEvaluationReportnumber.evaluation_id'));
      $data = array();

      foreach ($TemplatesEvaluation as $key => $value) {

        $Attention = $this->Templates->FindEvaluationTemplateForReport($value,$EvaluationIds);
        
        if(count($Attention) == 0){

          $data['overview'][$value['TemplatesEvaluationReportnumber']['evaluation_id']]['Attention'] = null;

          continue;

        }

        if($id == 0) $data['overview'][$value['TemplatesEvaluationReportnumber']['evaluation_id']]['Attention'] = $Attention[$Model];
        elseif($id > 0) $data['edit'][$value['TemplatesEvaluationReportnumber']['evaluation_id']]['Attention'] = $Attention[$Model];
      }

      $this->set('Response', json_encode($data));
      $this->render('json');

    }

    public function _showmodal($id)
    {


      if(isset($this->request->data['find_attention'])) return;

      $this->layout = 'modal';

      $projectID = $this->request->projectvars['VarsArray'][0];
      $cascadeID = $this->request->projectvars['VarsArray'][1];
      $orderID = $this->request->projectvars['VarsArray'][2];
      $reportID = $this->request->projectvars['VarsArray'][3];  
      $ID = $this->request->projectvars['VarsArray'][4];  

      $SettingsArray = array();
  		$SettingsArray['backlink'] = array('discription' => __('Back',true), 'controller' => 'templates','action' => 'get', 'terms' => $this->request->projectvars['VarsArray']);

      $this->set('SettingsArray', $SettingsArray);

      $Test = $this->Templates->ApplyEvaluationTemplate();

      if($Test === true){

  			$FormName['controller'] = 'reportnumbers';
  			$FormName['action'] = 'edit';
  			$FormName['terms'] = implode('/',$this->request->projectvars['VarsArray']);

  			$this->set('FormName',$FormName);

        return;
      }

      $locale = $this->Lang->Discription();

      $id = $this->request->projectvars['VarsArray'][5];
      $ide = $this->request->projectvars['VarsArray'][6];

      $this->request->projectvars['VarsArray'][0] = $id;
      $this->request->projectvars['VarsArray'][1] = $ide;
      $this->request->projectvars['VarsArray'][2] = 0;
      $this->request->projectvars['VarsArray'][4] = 0;
      $this->request->projectvars['VarsArray'][3] = 0;
      $this->request->projectvars['VarsArray'][5] = 0;
      $this->request->projectvars['VarsArray'][6] = 0;
      
      $Template = $this->Templates->CollectEvaluationTemplate(array());
      $Template = $this->Templates->ApplyTemplate($Template);

      $this->loadModel('Reportnumber');
      $this->Reportnumber->recursive = 0;
      $Reportnumber = $this->Reportnumber->find('first',array('conditions' => array('Reportnumber.id' => $ID)));
      $Template['Xml'] = $this->Xml->DatafromXmlForReport($Reportnumber['Testingmethod']['value'],$Reportnumber);

      $locale = $this->Lang->Discription();

      $this->set('locale',$locale);

      $this->request->reportID = 0;
      $this->request->data = $Template;

    }

    public function view($id = null)
    {

      $this->_viewdropdown($id);
      $this->_viewattention($id);
      $this->_viewattentionevaluation($id);

    }

    public function _viewattentionevaluation($id)
    {
      if($id != 0) return;

      $this->layout = 'blank';

      if(!isset($this->request->data['evaluation_id'])){
        return;
      }

      if($this->request->data['evaluation_id'] == 0){
        $this->set('response',json_encode(array()));
        return;
      }

      $id = $this->request->data['evaluation_id'];
      $data = array();

      $this->loadModel('TemplatesEvaluationReportnumber');
      $TemplatesEvaluationReportnumber = $this->TemplatesEvaluationReportnumber->find('first',array('conditions' => array('TemplatesEvaluationReportnumber.evaluation_id' => $id)));

      if(count($TemplatesEvaluationReportnumber) == 0){

        $data = json_encode($data);
        $this->set('response',$data);
        return;

      }

      $template_id = $TemplatesEvaluationReportnumber['TemplatesEvaluationReportnumber']['template_id'];

      $Template = $this->TemplatesEvaluation->find('first',array('conditions' => array('TemplatesEvaluation.id' => $template_id)));
      $Template = $this->Templates->FormatDataEvaluation($Template,'decode');
      $Template = $this->Templates->CheckForAttentionEvaluation($Template);

      $Template = json_encode($Template);
      $this->set('response',$Template);

    }

    public function _viewattention($id)
    {
      if($id != 0) return;

      $this->layout = 'blank';

      if(!isset($this->request->data['reportnumber_id'])){
        $this->set('response',json_encode(array()));
        return;
      }

      if($this->request->data['reportnumber_id'] == 0){
        $this->set('response',json_encode(array()));
        return;
      }

      $id = $this->request->data['reportnumber_id'];
      $data = array();

      $this->loadModel('TemplatesReportnumber');
      $TemplatesReportnumber = $this->TemplatesReportnumber->find('first',array('conditions' => array('TemplatesReportnumber.reportnumber_id' => $id)));

      if(empty($TemplatesReportnumber)){

        $data = json_encode($data);
        $this->set('response',$data);
        return;

      }

      $template_id = $TemplatesReportnumber['TemplatesReportnumber']['template_id'];

      $Template = $this->Template->find('first',array('conditions' => array('Template.id' => $template_id)));
      $Template = $this->Templates->FormatData($Template,'decode');
      $Template = $this->Templates->CheckForAttention($Template);

      $Template = json_encode($Template);
      $this->set('response',$Template);

    }

    public function _viewdropdown($id)
    {

      if($id == 0) return;

      $this->layout = 'blank';

      if(!isset($this->request->data['reportnumber_id'])){
        $this->set('response',json_encode(array()));
        return;
      }

      if($this->request->data['reportnumber_id'] == 0){
        $this->set('response',json_encode(array()));
        return;
      }

      $locale = $this->Lang->Discription();
  		$id = $this->request->projectvars['VarsArray'][0];

      $Template = $this->Template->find('first',array('conditions' => array('Template.id' => $id)));
      $Template = $this->Templates->FormatData($Template,'encode');

      if(count($Template) == 0){
        $this->set('response',array());
        return;
      }

//      $this->loadModel('Reportnumber');
      $this->loadModel('TemplatesReportnumber');

      $reportnumber_id = intval($this->request->data['reportnumber_id']);
      $this->TemplatesReportnumber->deleteAll(array('TemplatesReportnumber.reportnumber_id' => $reportnumber_id), false);

      $Update = array(
        'reportnumber_id' => $reportnumber_id,
        'template_id' => $Template['Template']['id'],
      );

      $this->TemplatesReportnumber->create();
      $this->TemplatesReportnumber->save($Update);

      $Data = json_decode($Template['Template']['data'],true);
      $data['data'] = $this->Templates->SaveReport($Data);

      if(isset($Template['Attention'])) $data['attention'] = $Template['Attention'];

      $data = json_encode($data);

      $this->set('response',$data);

    }

    /**
     * edit method
     *
     * @throws NotFoundException
     * @param string $id
     * @return void
     */

    public function json($temptype = null)
    {
      if($temptype == 1){
        $this->json_template();
        $this->json_attention();
      }

      if($temptype == 2){
        $this->json_evaluation_template();
        $this->json_attention();
      }
    }

    protected function json_evaluation_template()
    {

      if(isset($this->request->data['attention'])) return;

      $this->layout = 'blank';

      if(empty($this->request->data['id'])){
        $this->set('Response',$this->request->data['value']);
        return;
      }

  		$id = $this->request->projectvars['VarsArray'][1];
      $locale = $this->Lang->Discription();
  		$SettingsArray = array();

      $Template = $this->TemplatesEvaluation->find('first',array('conditions' => array('TemplatesEvaluation.id' => $id)));
      $Template['TemplatesEvaluation']['data'] = json_decode($Template['TemplatesEvaluation']['data'],true);

      $Template = $this->Templates->CreateFormData($Template);
      $Template = $this->Templates->FindUpdateMod($Template);
      $Template = $this->Templates->DupliEvaluationData($Template);
      $Template = $this->Templates->FindUpdateMod($Template);
      $Template = $this->Templates->UpdateEvaluationTemplateDesctiption($Template);
      $Template = $this->Templates->FindUpdateMod($Template);
      $Template = $this->Templates->CreateFormData($Template);

      if(isset($this->request->data['value'])) $Template['Value'] = $this->request->data['value'];
      if(isset($this->request->data['data_field_id'])) $Template['DataFieldId'] = $this->request->data['data_field_id'];
      if(isset($this->request->data['data_field'])) $Template['DataField'] = $this->request->data['data_field'];
      if(isset($this->request->data['data_field'])) $Template['DataWeld'] = $this->request->data['data_weld'];

      $Template = $this->Templates->DeleteEvaluationTemplateDesctiption($Template);

      if(is_string($Template)){

        $this->set('Response',$Template);
        return;

      }

      if(isset($Template['DupliPos'])){

        $this->request->data = $Template;
        $this->set('locale', $locale);
        $this->render('blank');
        return;

      }

      $this->set('Response',$Template['Value']);

    }

    protected function json_attention() {

      if($this->request->projectvars['VarsArray'][0] == 1) $this->_json_attention_1();
      if($this->request->projectvars['VarsArray'][0] == 2) $this->_json_attention_2();

    }

    protected function _json_attention_1() {

      if(!isset($this->request->data['attention'])) return;

      $this->layout = 'blank';

      $id = $this->request->projectvars['VarsArray'][1];
      $locale = $this->Lang->Discription();
      $class = '';
      $output = array();

      $Model = $this->request->data['model'];
      $Field = $this->request->data['field'];

      $Template = $this->Template->find('first',array('conditions' => array('Template.id' => $id)));
      $Template['Template']['data'] = json_decode($Template['Template']['data'],true);

      if(!isset($Template['Template']['data']['Attention'])) $Template['Template']['data']['Attention'] = array();
      if(!isset($Template['Template']['data']['Attention'][$Model])) $Template['Template']['data']['Attention'][$Model] = array();

      if(!isset($Template['Template']['data']['Attention'][$Model][$Field])){
        $Template['Template']['data']['Attention'][$Model][$Field] = 1;
        $class = 'attention_field';
      }
      elseif(isset($Template['Template']['data']['Attention'][$Model][$Field])){
        unset($Template['Template']['data']['Attention'][$Model][$Field]);
      }

      $Template['Template']['data'] = json_encode($Template['Template']['data']);

      $this->Template->save($Template['Template']);

      $Id =   '#' . Inflector::camelize($Model) . Inflector::camelize($Field);
      $Name = 'data[' . $Model . '][' . $Field . ']';

      $output['data'] = array('Model' => $Model,'Field' => $Field,'Name' => $Name,'Id' => $Id,'Class' => $class);
      $output['type'] = 1;

      $this->set('Response',json_encode($output));

      $this->render('json');
      return;

    }

    protected function _json_attention_2() {

      if(!isset($this->request->data['attention'])) return;

      $this->layout = 'blank';

      $id = $this->request->projectvars['VarsArray'][1];
      $locale = $this->Lang->Discription();
      $class = '';
      $output = array();

      $Model = $this->request->data['model'];
      $Field = $this->request->data['field'];

      $Template = $this->TemplatesEvaluation->find('first',array('conditions' => array('TemplatesEvaluation.id' => $id)));
      $Template['TemplatesEvaluation']['data'] = json_decode($Template['TemplatesEvaluation']['data'],true);

      if(!isset($Template['TemplatesEvaluation']['data']['Attention'])) $Template['TemplatesEvaluation']['data']['Attention'] = array();
      if(!isset($Template['TemplatesEvaluation']['data']['Attention'][$Model])) $Template['TemplatesEvaluation']['data']['Attention'][$Model] = array();

      if(!isset($Template['TemplatesEvaluation']['data']['Attention'][$Model][$Field])){
        $Template['TemplatesEvaluation']['data']['Attention'][$Model][$Field] = 1;
        $class = 'attention_field';
      }
      elseif(isset($Template['TemplatesEvaluation']['data']['Attention'][$Model][$Field])){
        unset($Template['TemplatesEvaluation']['data']['Attention'][$Model][$Field]);
      }

      $Template['TemplatesEvaluation']['data'] = json_encode($Template['TemplatesEvaluation']['data']);

      $this->TemplatesEvaluation->save($Template['TemplatesEvaluation']);

      $Id =   '#' . Inflector::camelize($Model) . Inflector::camelize($Field);
      $Name = 'data[TemplateEvaluation][data][' . $Field . ']';
// data[TemplateEvaluation][data][description]
      $output['data'] = array('Name' => $Name,'Id' => $Id,'Class' => $class);
      $output['type'] = 2;

      $this->set('Response',json_encode($output));

      $this->render('json');
      return;

    }

    protected function json_template()
    {

      if(isset($this->request->data['attention'])) return;

      $this->layout = 'blank';

      if(empty($this->request->data['id'])){
        $this->set('Response',$this->request->data['value']);
        return;
      }

  		$id = $this->request->projectvars['VarsArray'][1];
      $locale = $this->Lang->Discription();
  		$SettingsArray = array();

      $Template = $this->Template->find('first',array('conditions' => array('Template.id' => $id)));
      $Template['Template']['data'] = json_decode($Template['Template']['data'],true);

      $Template = $this->Templates->CreateFormData($Template);
      $Template = $this->Templates->FindUpdateMod($Template);
      $Template = $this->Templates->UpdateEvaluationData($Template);
      $Template = $this->Templates->FindUpdateMod($Template);
      $Template = $this->Templates->DupliEvaluationData($Template);
      $Template = $this->Templates->FindUpdateMod($Template);
      $Template = $this->Templates->DeleteEvaluationData($Template);
      $Template = $this->Templates->UpdateEvaluationTemplateDesctiption($Template);

      $Template = $this->Data->BelongsToManySelected($Template, 'Template', 'Testingcomp', array('TemplatesTestingcomp','testingcomp_id','templates_id'));
      $Template = $this->Templates->FindUpdateMod($Template);
      $Template = $this->Templates->CreateFormData($Template);

      if(isset($this->request->data['value'])) $Template['Value'] = $this->request->data['value'];
      if(isset($this->request->data['data_field_id'])) $Template['DataFieldId'] = $this->request->data['data_field_id'];
      if(isset($this->request->data['data_field'])) $Template['DataField'] = $this->request->data['data_field'];
      if(isset($this->request->data['data_field'])) $Template['DataWeld'] = $this->request->data['data_weld'];

      $Template = $this->Templates->DeleteEvaluationTemplateDesctiption($Template);

      if(is_string($Template)){

        $this->set('Response',$Template);
        return;

      }

      if(isset($Template['DupliPos'])){

        $this->request->data = $Template;
        $this->set('locale', $locale);
        $this->render('blank');
        return;

      }

      $this->set('Response',$Template['Value']);

    }

    public function edit($temptype = null)
    {

      if($temptype != 1) return;

      $locale = $this->Lang->Discription();
  		$id = $this->request->projectvars['VarsArray'][1];
  		$SettingsArray = array();

      $Template = $this->Template->find('first',array('conditions' => array('Template.id' => $id)));
      $Template = $this->Templates->EditTemplateDescription($Template);

      $breads = $this->Navigation->Breads(null);

  		$breads[] = array(
  						'discription' => __('Template manager', true),
  						'controller' => 'templates',
  						'action' => 'index',
  						'pass' => $temptype
  		);

  		$breads[] = array(
  						'discription' => $Template['Template']['name'],
  						'controller' => 'templates',
  						'action' => 'edit',
  						'pass' => $this->request->projectvars['VarsArray']
  		);

  		$this->set('breads', $breads);
  		$this->set('SettingsArray', $SettingsArray);
      $this->set('locale', $locale);

      $Template = $this->Data->BelongsToManySelected($Template, 'Template', 'Testingcomp', array('TemplatesTestingcomp','testingcomp_id','templates_id'));
      $Template['Template']['data'] = json_decode($Template['Template']['data'],true);
      $Template = $this->Templates->CreateFormData($Template);
      $Template = $this->Templates->EditTemplate($Template);
      $Template = $this->Templates->SaveDropdownValues($Template);


      $this->loadModel('Report');
      $Reports = $this->Report->find('list',array('fields' => array('id','id')));
      $this->request->reportID = $Reports;
      $Template['Template']['data'] = $this->Drops->DropdownData($Template['Template']['models'],$Template['Template']['xml'],$Template['Template']['data']);
      $Template['Template']['data'] = $this->Data->MultiselectData($Template['Template']['models'],$Template['Template']['xml'],$Template['Template']['data']);
      $Template['Template']['data'] = $this->Data->ChangeDropdownData($Template['Template']['data'],$Template['Template']['xml'],$Template['Template']['models']);
  		$Template['Template']['data'] = $this->Drops->CollectMasterDropdown($Template['Template']['data']);
  		$Template['Template']['data'] = $this->Drops->CollectMasterDependency($Template['Template']['data']);
  		$Template['Template']['data'] = $this->Drops->CollectModulDropdown($Template['Template']['data']);
  		$Template['Template']['data'] = $this->Data->SortAllDropdownArraysNatural($Template['Template']['data']);
      $Template['Template']['data'] = $this->Templates->ChangeDropdownValues($Template['Template']['data']);

      unset($Template['Template']['data']['DropdownInfo']);
      unset($Template['Template']['data']['xml']);

      $this->request->reportID = 0;

      $this->request->data = $Template;

    }

    public function fastview($id = null)
    {

      $this->layout = 'modal';
      $SettingsArray = array();

      $locale = $this->Lang->Discription();
  		$id = $this->request->projectvars['VarsArray'][0];
  		$SettingsArray = array();

      $Template = $this->Template->find('first',array('conditions' => array('Template.id' => $id)));

  		$this->set('SettingsArray', $SettingsArray);
      $this->set('locale', $locale);

      $Template = $this->Data->BelongsToManySelected($Template, 'Template', 'Testingcomp', array('TemplatesTestingcomp','testingcomp_id','templates_id'));
      $Template['Template']['data'] = json_decode($Template['Template']['data'],true);
//      $Template = $this->Templates->CollectEvaluationTemplates($Template);
      $Template = $this->Templates->FindDataKeysForFastview($Template);

      $this->request->reportID = 0;

      $this->request->data = $Template;

    }

    public function addevaluation(){

      $this->layout = 'modal';

      $SettingsArray = array();
      $locale = $this->Lang->Discription();

      $Check = $this->Templates->CreateEvaluations();

      if($Check === true) return;

      $this->Testingmethod->recursive = -1;
  		$Testingmethod = $this->Testingmethod->find('list',array(
        'fields' => array('id','verfahren'),
        'conditions' => array(
          'Testingmethod.enabled' => 1
          )
        )
      );

      $this->Flash->hint(__('Please select a testing method'), array('key' => 'hint'));

      $this->set('Testingmethod', $Testingmethod);
  		$this->set('SettingsArray', $SettingsArray);
      $this->set('locale', $locale);

    }

    public function evaluation($temptype = 0)
    {

//      $this->layout = 'modal';
      $SettingsArray = array();

      $locale = $this->Lang->Discription();
  		$id = $this->request->projectvars['VarsArray'][1];
  		$SettingsArray = array();
      $Template = $this->TemplatesEvaluation->find('first',array('conditions' => array('TemplatesEvaluation.id' => $id)));
      $Template = $this->Templates->ColledtEvaluationTemplate($Template);

      $breads = $this->Navigation->Breads(null);

  		$breads[] = array(
  						'discription' => __('Template manager', true),
  						'controller' => 'templates',
  						'action' => 'index',
  						'pass' => $temptype
  		);

  		$breads[] = array(
  						'discription' => $Template['TemplatesEvaluation']['name'],
  						'controller' => 'templates',
  						'action' => 'evaluation',
  						'pass' => $this->request->projectvars['VarsArray']
  		);

  		$this->set('breads', $breads);
  		$this->set('SettingsArray', $SettingsArray);
      $this->set('locale', $locale);

//      $Template = $this->Templates->CreateFormData($Template);
//      $Template = $this->Templates->SaveEvaluationTemplate($Template);
      $Template = $this->Templates->UpdateEvaluationTemplate($Template);
      $Template = $this->Templates->CollectEvaluationTemplate($Template);
      $Template = $this->Templates->SaveEvaluationTemplateDescription($Template);
      $Template = $this->Templates->CreateEvaluationData($Template);
      $Template = $this->Templates->AddEvaluationsTemplate($Template);
      $Template = $this->Templates->AddEvaluationDescriptionTemplate($Template);
//pr($Template['Template']['evaluation_temp_description']);
//      unset($Template['Template']['data']['DropdownInfo']);
//      unset($Template['Template']['data']['xml']);


      if(isset($this->request->data['show_blank'])){

        unset($Template['Template']);
        unset($Template['Evaluationmodel']);
        unset($Template['TemplatesEvaluation']);

        $this->layout = 'blank';
        $this->set('Response',json_encode($Template));
        $this->render('json');
        return;

      }

      $this->request->reportID = 0;

      $this->request->data = $Template;

      $this->set('SettingsArray',$SettingsArray);

      if($this->request->projectvars['VarsArray'][1] > 0){

        $this->render('evaluation_data_template');
        return;

      }
/*
      if(isset($Template['TemplatesEvaluation'])){

        $this->render('evaluation_template');
        return;

      }
*/
    }

    /**
     * delete method
     *
     * @throws NotFoundException
     * @param string $id
     * @return void
     */
    public function delete($id = null)
    {

      $this->_delete();
      $this->_deleteevaluation();

    }

    protected function _delete($id = null)
    {

      if($this->request->projectvars['VarsArray'][0] != 1) return;

      $this->layout = 'modal';

      $locale = $this->Lang->Discription();
  		$id = $this->request->projectvars['VarsArray'][1];
  		$SettingsArray = array();
      $Template = $this->Template->find('first',array('conditions' => array('Template.id' => $id)));


      $this->set('locale', $locale);
      $this->set('SettingsArray', array());

      $Check = $this->Templates->DeleteTemplate($Template);

      if($Check === true){

        $FormName['controller'] = 'templates';
        $FormName['action'] = 'index';
        $FormName['terms'] = 1;

        $this->set('FormName',$FormName);
        $this->Flash->success(__('The template has been deleted.'), array('key' => 'success'));

        return;

      } elseif($Check === false) {

        $this->Flash->error(__('An error has occurred.'), array('key' => 'error'));
        $this->request->data = $Template;

        return;

      }

      $this->request->data = $Template;

      $this->Flash->error(__('Should this template be deleted?'), array('key' => 'error'));

      $this->render('delete');

    }

    protected function _deleteevaluation($id = null)
    {

      if($this->request->projectvars['VarsArray'][0] != 2) return;

      $this->layout = 'modal';

      $locale = $this->Lang->Discription();
  		$id = $this->request->projectvars['VarsArray'][1];
  		$SettingsArray = array();
      $Template = $this->TemplatesEvaluation->find('first',array('conditions' => array('TemplatesEvaluation.id' => $id)));

      $this->set('locale', $locale);
      $this->set('SettingsArray', array());

      $Template = $this->Templates->ColledtEvaluationTemplate($Template);
      $Template = $this->Templates->CollectEvaluationTemplate($Template);
      $Template = $this->Templates->CreateEvaluationData($Template);
      $Template = $this->Templates->DeleteEvaluationTemplate($Template);

      $this->request->reportID = 0;
      $this->request->data = $Template;

      if(isset($Template['delete_evaluation_template_okay'])){

        $FormName['controller'] = 'templates';
  			$FormName['action'] = 'index';
  			$FormName['terms'] = 2;

  			$this->set('FormName',$FormName);

        return;
      }

      $this->Flash->error(__('Should this template be deleted?'), array('key' => 'error'));

      $this->render('deleteevaluation');

    }

    public function get($id = null){

      $this->layout = 'modal';

      $projectID = $this->request->projectvars['VarsArray'][0];
  		$cascadeID = $this->request->projectvars['VarsArray'][1];
  		$orderID = $this->request->projectvars['VarsArray'][2];
  		$reportID = $this->request->projectvars['VarsArray'][3];
  	 	$id = $this->request->projectvars['VarsArray'][4];

      $this->loadModel('Reportnumber');
      $this->Reportnumber->recursive = -1;
      $Reportnumber = $this->Reportnumber->find('first',array('conditions' => array('Reportnumber.id' => $id)));

      if(isset($this->request->data['Template'])){

        $this->Session->write('ApplyTemplate',$this->request->data['Template']);

      }

      $Template = $this->Templates->CollectEvaluationTemplates($Reportnumber);

      $this->request->data = $Template;

      $this->set('SettingsArray', array());

    }
}
