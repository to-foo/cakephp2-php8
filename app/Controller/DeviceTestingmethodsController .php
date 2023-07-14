<?php
App::uses('AppController', 'Controller');
App::uses('ConnectionManager', 'Model');

class DeviceTestingmethodsController extends AppController {
        
        public $components = array('Data','Auth','Acl','Autorisierung','Cookie','Navigation','Lang','Sicherheit','SelectValue', 'Paginator','Xml');
	protected $writeprotection = false;

	public $helpers = array('Lang','Navigation','JqueryScripte','Pdf','Quality');
	public $layout = 'ajax';  
        public $uses = array('DeviceTestingmethod');
    
        function beforeFilter() {
            
            if($this->request->params['action'] == 'testgraph') return;  
            App::import('Vendor', 'Authorize');
            $this->loadModel('User');
            $this->Autorisierung->Protect();
            
            $noAjaxIs = 0;
            if($noAjaxIs == 0){
			$this->Navigation->ajaxURL();
		}
            
            
            $this->Lang->Choice();
	    $this->Lang->Change();
            $this->Navigation->ReportVars();

            $this->set('writeprotection', $this->writeprotection);

            $this->set('lang', $this->Lang->Choice());
            $this->set('selected', $this->Lang->Selected());
            $this->set('menues', $this->Navigation->Menue());
            $this->set('login_info', $this->Navigation->loggedUser());
            $this->set('lang_choise', $this->Lang->Choice());
            $this->set('lang_discription', $this->Lang->Discription());
            $this->set('previous_url', $this->base.'/'.$this->Session->read('lastURL'));
            $this->set('locale', $this->Lang->Discription());     
                
            if(isset($this->Auth)) {
                $this->set('authUser', $this->Auth);
            }   
        }
        
        function afterFilter() {
		$this->Navigation->lastURL();
	}
        
	/*public function quicksearch() {
		
		App::uses('Sanitize', 'Utility');

		$this->__quicksearch();
	}

	protected function __quicksearch() {

 		$this->layout = 'json';

		$model = Sanitize::stripAll($this->request->data['model']);
		$field = Sanitize::stripAll($this->request->data['field']);
		$term = Sanitize::escape($this->request->data['term']);
		
		$conditions =  array(
//								'Examiner.testingcomp_id' => $this->Autorisierung->ConditionsTopprojects(),
								$model.'.'.$field.' LIKE' => '%'.$term.'%',
								$model.'.deleted' => 0
							);

		if(isset($this->request->data['Conditions'])){
			foreach($this->request->data['Conditions'] as $_key => $_conditions){
				$conditions[$model.'.'.Sanitize::escape($_key)] = Sanitize::escape($_conditions);
			}
		}

		if (!is_object($model)){
			$this->loadModel($model);	
		}

		$options = array(
						'fields' => array('id',$field),
						'limit' => 5,
						'conditions' => $conditions
						);

		$this->$model->recursive = -1;
		$data = $this->$model->find('all', $options);

		$response = array();

		foreach($data as $_key => $_data){
			array_push($response, array('key' => $_data[$model]['id'],'value' => $_data[$model][$field]));
		}

		$this->set('response',json_encode($response));
	}*/        
        
        public function index () {
            $this->include_index();            
        }
        
        public function include_index(){
                $options = array();
                $this->layout = 'modal';
		$this->Navigation->GetSessionForPaging();
		$this->DeviceTestingmethod->recursive = 0;            
                $options['DeviceTestingmethod.deleted'] = 0 ;
                
                 
                if(isset($this->request->data['devicetestingmethod_id']) && $this->request->data['devicetestingmethod_id'] > 0){
	 		$devicetestingmethod_id = $this->Sicherheit->Numeric($this->request->data['devicetestingmethod_id']);			                       
                        $options['DeviceTestingmethod.id'] = $devicetestingmethod_id;                                                
		}   
                $SettingsArray = array();
                
                $this->paginate = array(
                        'conditions' => array($options),
			'order' => array('DeviceTestingmethod.verfahren' => 'asc'),
			'limit' => 10
		);
                
                $devicetestingmethod = $this->paginate('DeviceTestingmethod');
		foreach($devicetestingmethod as $_key => $_devicetestingmethod){

			$devicetestingmethod_link = $this->request->projectvars['VarsArray'];

			$devicetestingmethod_link[13] = 0;
			$devicetestingmethod_link[14] = 0;
			$devicetestingmethod_link[15] = $_devicetestingmethod['DeviceTestingmethod']['id'];
			unset($devicetestingmethod_link[16]);
			unset($devicetestingmethod_link[17]);	
                        
			$devicetestingmethod[$_key]['DeviceTestingmethod']['_devicetestingmethod_link'] = $devicetestingmethod_link;
			
		}  
                
                $breads = $this->Navigation->Breads(null);

		$breads[1]['discription'] = __('DeviceTestingmethods',true);
		$breads[1]['controller'] = 'devicetestingmethods';
		$breads[1]['action'] = 'index';
		$breads[1]['pass'] = null;	
                
                $SettingsArray['addlink'] = array('discription' => __('Add devicetestingmethod',true), 'controller' => 'devicetestingmethods','action' => 'add', 'terms' => null,);
 				$SettingsArray['backlink'] = array('discription' => __('Back',true), 'controller' => 'devices','action' => 'add', 'terms' => null,);
               $this->set('breads', $breads);
                $this->set('SettingsArray', $SettingsArray);
                $this->set('devicetestingmethods', $devicetestingmethod);		  
                $this->render('/DeviceTestingmethods/index');
                
                $this->Navigation->SetSessionForPaging();
                //pr($this->request);       
        }        
/**
 * add method
 *
 * @return void
 */        
        public function add(){
                $this->layout = 'modal';
                $SettingsArray = array();
		
		
		$afterEdit = null;
		$arrayData = $this->Xml->DatafromXml('DeviceTestingmethod','file',null);

		if (isset($this->request->data['DeviceTestingmethod']) && ($this->request->is('post') || $this->request->is('put'))) {

			$this->DeviceTestingmethod->create();
			
			// muss noch in der xml angepasst werden
			//$this->request->data['Examiner']['testingcomp_id'] = 1;

			
			if ($this->DeviceTestingmethod->save($this->request->data)) {
				$this->Session->setFlash(__('The DeviceTestingmethod has been saved'));
				$this->Autorisierung->Logger($this->DeviceTestingmethod->getLastInsertID(), $this->request->data['DeviceTestingmethod']);

				$this->request->projectvars['VarsArray'][15] = $this->DeviceTestingmethod->getLastInsertID();
				
				$FormName['controller'] = 'devicetestingmethods';
				$FormName['action'] = '';
				$FormName['terms'] = implode('/',$this->request->projectvars['VarsArray']);
					
				$this->set('FormName',$FormName);
                                $this->include_index();
			} else {
				$this->Session->setFlash(__('The DeviceTestingmethod could not be saved. Please, try again.'));
			}
		}
                
		$SettingsArray['backlink'] = array('discription' => __('Back',true), 'controller' => 'devicetestingmethods','action' => 'index', 'terms' => null,);

		$this->request->data['DeviceTestingmethod']['active'] = 1;
		
		$this->set('SettingsArray', $SettingsArray);
		$this->set('arrayData', $arrayData);
		$this->set('settings', $arrayData['headoutput']); 

            
        }
        

        
        
        public function view(){
            
            
        }
        
 /**
 * view method
 *
 * @throws NotFoundException
 * @param string $id
 * @return void
 */
        public function overview(){
                $this->layout = 'modal';
		// Wenn die Testmethode über die Schnellsuche aufgerufen wird
		if($this->request->projectvars['VarsArray'][15] == 0 && isset($this->request->data['devicetestingmethod_id']) && $this->request->data['devicetestingmethod_id'] > 0){
			$this->request->projectvars['VarsArray'][15] = $this->Sicherheit->Numeric($this->request->data['devicetestingmethod_id']);
		}

		$id = $this->request->projectvars['VarsArray'][15];
//		$this->request->projectvars['VarsArray'][15] = $id;


		if (!$this->DeviceTestingmethod->exists($id)) {
			throw new NotFoundException(__('Invalid devicetestingmethod'));
		}

		$arrayData = $this->Xml->DatafromXml('DeviceTestingmethod','file',null);
		$devicetestingmethod = $this->DeviceTestingmethod->find('first',array('conditions' => array('DeviceTestingmethod.id' => $id)));

		$this->request->data = $devicetestingmethod;
		//$this->request->data = $this->Data->GeneralDropdownData(array('DeviceTestingmethod'), $arrayData, $this->request->data);
		
		$SettingsArray = array();
                
                $SettingsArray['backlink'] = array('discription' => __('Back',true), 'controller' => 'devicetestingmethods','action' => 'index', 'terms' => null,);
                $SettingsArray['editlink'] = array('discription' => __('edit devicetestingmethod',true), 'controller' => 'devicetestingmethods','action' => 'edit', 'terms' => $this->request->params['pass'],);

		$breads = $this->Navigation->Breads(null);

		unset($this->request->projectvars['VarsArray'][16]);
		unset($this->request->projectvars['VarsArray'][17]);
		
		$breads[1]['discription'] = __('DeviceTestingmethods',true);
		$breads[1]['controller'] = 'devicetestingmethods';
		$breads[1]['action'] = 'index';
		$breads[1]['pass'] = null;		

		$breads[2]['discription'] = $devicetestingmethod['DeviceTestingmethod']['verfahren'];
		$breads[2]['controller'] = 'devicetestingmethods';
		$breads[2]['action'] = 'overview';
		$breads[2]['pass'] = $this->request->projectvars['VarsArray'];		

		$this->set('breads', $breads);
		$this->set('arrayData', $arrayData);
		$this->set('devicetestingmethod', $devicetestingmethod);
		$this->set('SettingsArray', $SettingsArray);
                
	            
            
        }
        
        
    /**
 * edit method
 *
 * @throws NotFoundException
 * @param string $id
 * @return void
 */
	public function edit() {
                $SettingsArray = array();
		$this->layout = 'modal';
		$id = $this->request->projectvars['VarsArray'][15];
		$afterEdit = null;

		if (!$this->DeviceTestingmethod->exists($id)) {
			throw new NotFoundException(__('Invalid devicetestingmethod'));
		}

		$arrayData = $this->Xml->DatafromXml('DeviceTestingmethod','file',null);

		if (isset($this->request->data['DeviceTestingmethod']) && ($this->request->is('post') || $this->request->is('put'))) {
			$this->request->data['id'] = $id;
			if ($this->DeviceTestingmethod->save($this->request->data)) {
				$this->Session->setFlash(__('The devicetestingmethod has been saved'));
				$this->Autorisierung->Logger($id, $this->request->data['DeviceTestingmethod']);

				$FormName['controller'] = 'devicetestingmethods';
				$FormName['action'] = '';
                                $this->include_index();

				$FormName['terms'] = implode('/',$this->request->projectvars['VarsArray']);
				
				$this->set('FormName',$FormName);
				$this->set('saveOK',1);
                             //   $this->include_index();
                                
			} else {
				$this->Session->setFlash(__('The devicetestingmethod could not be saved. Please, try again.'));
			}
		} else {
			$options = array('conditions' => array('DeviceTestingmethod.' . $this->DeviceTestingmethod->primaryKey => $id));
			$this->request->data = $this->DeviceTestingmethod->find('first', $options);
		}
                 $SettingsArray['backlink'] = array('discription' => __('Back',true), 'controller' => 'devicetestingmethods','action' => 'index', 'terms' => null,);

		
		$SettingsArray['dellink'] = array('discription' => __('delete devicetestingmethod',true), 'controller' => 'devicetestingmethods','action' => 'delete', 'terms' => $this->request->params['pass']);
		$this->set('SettingsArray', $SettingsArray);
		$this->set('arrayData', $arrayData);
		$this->set('settings', $arrayData['headoutput']);
               // $this->render('/DeviceTestingmethods/index');

		
	}
    
    
/**
 * delete method
 *
 * @throws NotFoundException
 * @param string $id
 * @return void
 */
	public function delete() {

		$this->layout = 'modal';

		$id = $this->request->projectvars['VarsArray'][15];

		$this->DeviceTestingmethod->id = $id;

		if (!$this->DeviceTestingmethod->exists()) {
			throw new NotFoundException(__('Invalid devicetestingmethod'));
		}

		if (isset($this->request->data['DeviceTestingmethod']) && ($this->request->is('post') || $this->request->is('put'))) {
			$this->request->data['DeviceTestingmethod']['deleted'] = 1;
			$this->request->data['DeviceTestingmethod']['active'] = 0;
			if ($this->DeviceTestingmethod->save($this->request->data)) {
				$this->Session->setFlash(__('DeviceTestingmethod deleted'));
				$this->Autorisierung->Logger($id, $this->request->data['DeviceTestingmethod']);
				$this->include_index();
			} 
                        else {
				$this->Session->setFlash(__('DeviceTestingmethod was not deleted. Please, try again.'));
			}
		}
		
		$options = array('conditions' => array('DeviceTestingmethod.' . $this->DeviceTestingmethod->primaryKey => $id));
		$this->request->data = $this->DeviceTestingmethod->find('first', $options);
		
		$SettingsArray = array();
		$SettingsArray['backlink'] = array('discription' => __('Back',true), 'controller' => 'devicetestingmethods','action' => 'edit', 'terms' =>  $this->request->params['pass']);

		$this->set('SettingsArray', $SettingsArray);
//		$this->set('afterEDIT', $afterEdit);
	}           
    
}