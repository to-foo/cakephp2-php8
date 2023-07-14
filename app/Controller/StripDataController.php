<?php
App::uses('AppController', 'Controller');
App::import('Vendor', 'jpgraph/jpgraph');
App::import('Vendor', 'jpgraph/jpgraph_line');
App::import('Vendor', 'jpgraph/jpgraph_bar');

/**
 * StripData Controller
 *
 * @property StripDatum $StripDatum
 */
class StripDataController extends AppController {
	public $components = array('Autorisierung', 'Lang', 'Session', 'Navigation', 'RequestHandler', 'Auth', 'Acl', 'Cookie', 'Sicherheit', 'Xml');
	
	function beforeFilter() {
	
		if($this->RequestHandler->isAjax()) {
			if (!$this->Auth->login()) {
				header('Requires-Auth: 1');
			}
		}
	
		if (!$this->Auth->login()) {
			//			$this->redirect(array('controller' => 'users', 'action' => 'logout'));
		}
	
		// muss man noch ne Funkton draus machen
		$Auth = $this->Session->read('Auth');
		
		$this->Autorisierung->Protect();

		$this->Lang->Choice();
		$this->Lang->Change();
		$this->Navigation->ReportVars();
		
		$noAjaxIs = 0;
		$noAjax = array('pdf');
		
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
		
		$this->loadModel('Reportlock');
		$this->Reportlock->recursive = -1;
		$this->writeprotection = 0 < $this->Reportlock->find('count', array('conditions'=>array('Reportlock.topproject_id'=>$this->request->projectID, 'Reportlock.report_id'=>$this->request->reportID)));
		$this->set('writeprotection', $this->writeprotection);
		
		
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
	
/**
 * index method
 *
 * @return void
 */
	public function index() {
		$this->layout = 'modal';
		
		$this->StripDatum->recursive = 0;
		$this->paginate = array('sort'=>'description', 'conditions'=>array('StripDatum.deleted'=>0));
		$this->set('stripData', $this->paginate());
		
		
		$SettingsArray = array();
		$SettingsArray['addlink'] = array('discription' => __('Add',true), 'controller' => $this->request->controller,'action' => 'add', 'terms' => $this->request->projectvars['VarsArray']);
		
		$this->set('SettingsArray', $SettingsArray);
	}

/**
 * view method
 *
 * @throws NotFoundException
 * @param string $id
 * @return void
 */
	public function view() {
		$this->layout = 'modal';
		$id = $this->request->data['id'];
		
		if (!$this->StripDatum->exists($id)) {
			throw new NotFoundException(__('Invalid strip datum'));
		}
		
		$SettingsArray = array();
		$SettingsArray['addlink'] = array('discription' => __('Add evaluation',true), 'controller' => $this->request->controller,'action' => 'addevaluation', 'terms' => $this->request->projectvars['VarsArray']);
		$SettingsArray['backlink'] = array('discription' => __('Back',true), 'controller' => $this->request->controller,'action' => 'index', 'terms' => $this->request->projectvars['VarsArray']);
		
		$VarsArray = $this->request->projectvars['VarsArray'];
		$VarsArray[5] = $id;
		$SettingsArray['printlink'] = array('discription' => __('Print',true), 'controller' => $this->request->controller,'action' => 'pdf', 'terms' => $VarsArray, 'target'=>'_blank');
		
		$this->set('SettingsArray', $SettingsArray);
		
		$options = array('conditions' => array('StripDatum.' . $this->StripDatum->primaryKey => $id));
		$this->StripDatum->recursive = -1;
		
		$stripDatum = $this->StripDatum->find('first', $options);
		$this->StripDatum->StripEvaluation->recursive = -1;
		$eval = $this->StripDatum->StripEvaluation->find('all', array('conditions'=>array('StripEvaluation.strip_datum_id'=>$id, 'StripEvaluation.deleted'=>0)));
		
		$stripDatum['stripEvaluation'] = array_map(function($elem) { return $elem['StripEvaluation']; }, $eval);
		
		$this->set('stripDatum', $stripDatum);
		$this->set('strip_id', $id);
	}

/**
 * add method
 *
 * @return void
 */
	public function add() {
		$this->layout = 'modal';
		
		if ($this->request->is('post')) {
			$last = null;
			$this->StripDatum->create();
			if ($this->StripDatum->save(array(
				'user_id'=>$this->Session->read('Auth.User.id'),
				'testingcomp_id' => $this->Session->read('Auth.User.testingcomp_id'),
				'topproject_id' => $this->request->projectID,
				'equipment_type_id' => $this->request->equipmentType,
				'equipment_id' => $this->request->equipment,
				'order_id' => $this->request->orderID,
				'report_id' => $this->request->reportID
			))) {
				$this->Session->setFlash(__('The strip has been created.'));

				$afterEdit = '<script type="text/javascript">
				$(document).ready(function(){
						setTimeout( function(){
							var data = $(this).serializeArray();
							data.push({name: "ajax_true", value: 1});
							data.push({name: "id", value: '.$this->StripDatum->getLastInsertId().'});
									
							$.ajax({
								type	: "POST",
								cache	: true,
								url		: "'.Router::url(array_merge(array('action'=>'edit'), $this->request->projectvars['VarsArray'])).'",
								data	: data,
								success: function(data) {
		    						$("#dialog").html(data);
		    						$("#dialog").show();
								}
							});
							return false;
						}, 500);
					});
				</script>';
			} else {
				$this->Session->setFlash(__('The strip could not be saved. Please, try again.'));
				$afterEdit = $this->Navigation->afterEditModal(Router::url(array_merge(array('action'=>'index'), $this->request->projectvars['VarsArray'])), 500);
			}
		}

		$this->set('afterEdit', $afterEdit);
	}

/**
 * edit method
 *
 * @throws NotFoundException
 * @param string $id
 * @return void
 */
	public function edit() {
		$this->layout = 'modal';
		$id = isset($this->request->data['Strip']['id']) ? $this->request->data['Strip']['id'] : $this->request->data['id'];
		
		if (!$this->StripDatum->exists($id) || $this->StripDatum->find('count',array('conditions'=>array('StripDatum.id'=>$id, 'StripDatum.deleted'=>1), 'limit'=>1))) {
			$this->Session->setFlash(__('The strip was not found.'));
			$this->set('afterEdit', $this->Navigation->afterEditModal(Router::url(array_merge(array('action'=>'index'), $this->request->projectvars['VarsArray'])), 500));
		} elseif (isset($this->request->data['Strip'])) {
			$this->request->data['Strip'] = array_merge($this->request->data['Strip'], array(
				'topproject_id' => $this->request->projectID,
				'equipment_type_id' => $this->request->equipmentType,
				'equipment_id' => $this->request->equipment,
				'order_id' => $this->request->orderID,
				'report_id' => $this->request->reportID,
			));
			if ($this->StripDatum->save($this->request->data['Strip'])) {
				$this->Session->setFlash(__('The strip datum has been saved'));
				$this->set('afterEdit', $this->Navigation->afterEditModal(Router::url(array_merge(array('action'=>'index'), $this->request->projectvars['VarsArray'])), 500));
			} else {
				$this->Session->setFlash(__('The strip datum could not be saved. Please, try again.'));
			}
		} else {
			$options = array('conditions' => array('StripDatum.'.$this->StripDatum->primaryKey => $id, 'StripDatum.deleted'=>0));
			$data = $this->StripDatum->find('first', $options);
			$this->request->data['Strip'] = reset($data);
		}
		
		$SettingsArray = array();
		$SettingsArray['backlink'] = array('discription' => __('Back',true), 'controller' => $this->request->controller,'action' => 'index', 'terms' => $this->request->projectvars['VarsArray']);
		
		$this->set('SettingsArray', $SettingsArray);
		$this->set('strip_id', $id);
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
		$id = $this->request->data['id'];
		if (!$this->StripDatum->exists($id)) {
			throw new NotFoundException(__('Invalid strip'));
		}

		$this->request->onlyAllow('post', 'delete');
		if ($this->StripDatum->save(array('id'=>$id, 'deleted'=>1), array('callbacks'=>false))) {
			$this->Session->setFlash(__('The strip was deleted.'));
		} else {
			$this->Session->setFlash(__('The strip was not deleted.'));
		}
		
		$this->set('afterEdit', $this->Navigation->afterEditModal(Router::url(array_merge(array('action'=>'index'), $this->request->projectvars['VarsArray'])), 500));
	}
	
	public function addevaluation() {
		$this->layout = 'modal';

		$this->loadModel('StripEvaluation');
		$this->StripEvaluation->recursive = -1;
		
		$this->StripEvaluation->create();
		if ($this->StripEvaluation->save(array(
			'strip_data_id'=>$this->request->data['id']
		))) {
			$this->Session->setFlash(__('The strip evaluation has been created.'));
		
			$afterEdit = '<script type="text/javascript">
				$(document).ready(function(){
						setTimeout( function(){
							var data = $(this).serializeArray();
							data.push({name: "ajax_true", value: 1});
							data.push({name: "data[StripEvaluation][id]", value: '.$this->StripEvaluation->getLastInsertId().'});
					
							$.ajax({
								type	: "POST",
								cache	: true,
								url		: "'.Router::url(array_merge(array('action'=>'editevaluation'), $this->request->projectvars['VarsArray'])).'",
								data	: data,
								success: function(data) {
		    						$("#dialog").html(data);
		    						$("#dialog").show();
								}
							});
							return false;
						}, 500);
					});
				</script>';
		} else {
			$this->Session->setFlash(__('The strip evaluation could not be saved. Please, try again.'));
			$afterEdit = '<script type="text/javascript">
				$(document).ready(function(){
						setTimeout( function(){
							var data = $(this).serializeArray();
							data.push({name: "ajax_true", value: 1});
							data.push({name: "id", value: '.$this->request->data['id'].'});
					
							$.ajax({
								type	: "POST",
								cache	: true,
								url		: "'.Router::url(array_merge(array('action'=>'view'), $this->request->projectvars['VarsArray'])).'",
								data	: data,
								success: function(data) {
		    						$("#dialog").html(data);
		    						$("#dialog").show();
								}
							});
							return false;
						}, 500);
					});
				</script>';
		}
		
		$this->set('afterEdit', $afterEdit);
		$this->render('add');
	}

	public function editevaluation() {
		$this->layout = 'modal';
		$this->StripDatum->StripEvaluation->recursive = -1;
		
		if(isset($this->request->data['StripEvaluation'])) {
			if($this->StripDatum->StripEvaluation->save($this->request->data)) {
				$this->Session->setFlash(__('Evaluation was saved.'));
			} else {
				$this->Session->setFlash(__('Evaluation could not be deleted. Please try again later.'));
			}
			
			$this->request->data = $this->StripDatum->StripEvaluation->find('first', array('conditions'=>array('id'=>$this->request->data['StripEvaluation']['id'])));
		} else {
			$this->request->data = $this->StripDatum->StripEvaluation->find('first', array('conditions'=>array('id'=>$this->request->data['id'])));
		}
		
		$this->set('strip_id', $this->request->data['StripEvaluation']['strip_datum_id']);
		$SettingsArray = array();
		$SettingsArray['backlink'] = array('discription' => __('Back',true), 'controller' => $this->request->controller,'action' => 'view', 'terms' => $this->request->projectvars['VarsArray']);
		
		$this->set('SettingsArray', $SettingsArray);
	}

	public function deleteevaluation() {
		$this->layout = 'modal';
		
		$this->StripDatum->StripEvaluation->recursive = -1;
		$id = $this->StripDatum->StripEvaluation->find('list', array('fields'=>array('id', 'strip_datum_id'), 'conditions'=>array('id'=>$this->request->data['id']), 'limit'=>1));
		
		if(count($id) > 0) {
			if($this->StripDatum->StripEvaluation->save(array('id'=>key($id), 'deleted'=>1), array('callbacks'=>false))) {
				$this->Session->setFlash(__('Evaluation was deleted.'));
			} else {
				$this->Session->setFlash(__('Evaluation could not be deleted. Please try again later.'));
			}
		}
		
		$afterEdit = '<script type="text/javascript">
			$(document).ready(function(){
					setTimeout( function(){
						var data = $(this).serializeArray();
						data.push({name: "ajax_true", value: 1});
						data.push({name: "id", value: '.reset($id).'});
		
						$.ajax({
							type	: "POST",
							cache	: true,
							url		: "'.Router::url(array_merge(array('action'=>'view'), $this->request->projectvars['VarsArray'])).'",
							data	: data,
							success: function(data) {
	    						$("#dialog").html(data);
	    						$("#dialog").show();
							}
						});
						return false;
					}, 500);
				});
			</script>';
		$this->set('afterEdit',$afterEdit);
		$this->render('delete');
	}
	
	public function pdf() {
		$this->layout = 'pdf';
		
		$xml = $this->Xml->XmltoArray('strips', 'file');
		$data = $this->StripDatum->find('first', array('conditions'=>array('StripDatum.id'=>$this->request->reportnumberID)));
		$this->StripDatum->StripEvaluation->recursive = -1;
		
		$eval = $this->StripDatum->StripEvaluation->find('all', array('conditions'=>array('StripEvaluation.strip_datum_id'=>$this->request->reportnumberID, 'StripEvaluation.deleted'=>0)));
		$data['StripEvaluation'] = array_map(function($elem) {return reset($elem);}, $eval);
		
		$graph = new Graph(800,300);
		$graph->SetScale("intlin");
		$graph->img->SetMargin(30, 5, 10, 0);
		$graph->xaxis->SetFont(FF_FONT1, FS_BOLD);
		$graph->xaxis->SetLabelAngle(90);
		$graph->xaxis->SetLabelSide(SIDE_UP);
		$graph->xaxis->SetLabelMargin(250);
		$graph->xaxis->HideZeroLabel();
		
		//$graph->yaxis->Set
		
		$line = new LinePlot(array(0.14,0.14,0.14,0.14,0.14,0.14,0.14,0.14,0.14,0.14,0.14,0.14));
		$line->setWeight(5);
		
		$graph->Add($line);
		
		$handle = $graph->Stroke( _IMG_HANDLER );
		ob_start();
		imagepng($handle);
		$img = ob_get_contents();
		ob_end_clean();
		
		$this->set('graph', $img);
		$this->set('data', $data);
		$this->set('settings', $xml);
		$this->set('user', $this->Auth->user());
	}
}
