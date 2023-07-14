<?php
App::uses('AppController', 'Controller');
/**
 * EquipmentTypes Controller
 *
 * @property EquipmentType $EquipmentType
 */
class CascadesController extends AppController
{
    public $components = array('Auth','Acl','Autorisierung','Cookie','Navigation','Lang','Sicherheit','Xml','Pdf','RequestHandler','Search','Data','Cascades','MonitoringTool','Paginator');
    public $helpers = array('Lang','Navigation','JqueryScripte','ViewData');
    public $layout = 'ajax';

    public function beforeFilter()
    {

      // Testkommentar für Git
        App::import('Vendor', 'Authorize');
        // Es wird das in der Auswahl gewählte Project in einer Session gespeichert


        $this->Navigation->ReportVars();

        $this->Autorisierung->ConditionsTopprojectsTest($this->request->projectID);

        $this->loadModel('User');
        $this->loadModel('Topproject');

        $this->Autorisierung->Protect();

        $noAjaxIs = 0;
        $noAjax = array('quicksearch','quicksearchtype','autocomplete','update');

        // Test ob die aktuelle Funktion per Ajax oder direkt aufgerufen werden soll
        foreach ($noAjax as $_noAjax) {
            if ($_noAjax == $this->request->params['action']) {
                $noAjaxIs++;
                break;
            }
        }

        if ($noAjaxIs == 0) {
            $this->Navigation->ajaxURL();
        }

        $this->Lang->Choice();
        $this->Lang->Change();

        $lang = $this->Lang->Discription();
    		$this->request->lang = $lang;

        $this->loadModel('Order');
        $this->loadModel('OrdersTestingcomp');
        $this->loadModel('CascadesOrder');

        $this->set('lang', $this->Lang->Choice());
        $this->set('selected', $this->Lang->Selected());
        $this->set('login_info', $this->Navigation->loggedUser());
        $this->set('lang_choise', $this->Lang->Choice());
        $this->set('previous_url', $this->base.'/'.$this->Session->read('lastURL'));
        $this->set('SettingsArray', array());

        $this->Session->delete('search.params');
        $this->Session->delete('search.results');

        if (isset($this->Auth)) {
            $this->set('authUser', $this->Auth);
        }
    }

    public function afterFilter()
    {
        $this->Navigation->lastURL();
    }

    /**
     * index method
     *
     * @return void
     */

    public function index()
    {

        $this->Navigation->ResetAllSessionForPaging();
        $projectID = $this->request->projectID;
        $cascadeID = $this->request->cascadeID;
        $orderID = $this->request->orderID;

        $ConditionsTopprojects = $this->Autorisierung->ConditionsTopprojects();

        $test = array_search($projectID, $ConditionsTopprojects);
        if ($test === false) {
            die('error');
        }

        $this->loadModel('Order');

        $SettingsArray = array();
        //		$SettingsArray['movelink'] = array('discription' => __('move order',true), 'controller' => 'orders','action' => 'move', 'terms' => $this->request->projectvars['VarsArray']);
        $SettingsArray['editlink'] = array('discription' => __('Edit current cascade', true), 'controller' => 'cascades','action' => 'edit', 'terms' => $this->request->projectvars['VarsArray'],);
        $SettingsArray['addsearching'] = array('discription' => __('Searching', true), 'controller' => 'searchings','action' => 'search', 'terms' => $this->request->projectvars['VarsArray'],);
        $SettingsArray['last_ten'] = array('discription' => __('Show my last reports of each testing method', true), 'controller' => 'reportnumbers','action' => 'last_ten', 'terms' => null,);
        $SettingsArray['progresstool'] = array('discription' => __('Show progress', true), 'controller' => 'developments','action' => 'overview', 'terms' => null,);
        if (Configure::check('ExpeditingManager') && Configure::read('ExpeditingManager') == true) {
            $SettingsArray['addexpediting'] = array('discription' => __('Expediting', true), 'controller' => 'suppliers','action' => 'index', 'terms' => $this->request->projectvars['VarsArray']);
        }
//        $SettingsArray['statistik'] = array('discription' => __('Statistics', true), 'controller'=>'searchings', 'action'=>'statistic', 'terms'=>$this->request->projectvars['VarsArray']);

        $SettingsArray = $this->Autorisierung->AclCheckLinks($SettingsArray);

        $CascadeCurrent = $this->Cascade->find('first', array(
            'conditions' => array(
                'Cascade.id' => $cascadeID
                )
            )
        );

        $this->Cascade->recursive = -1;

        $CascadeChild = $this->Cascade->find('first', array(
            'fields' => array('id'),
            'conditions' => array(
                'Cascade.parent' => $CascadeCurrent['Cascade']['id']
                )
            )
        );

        $CascadeParent = $this->Cascade->find('first', array(
            'conditions' => array(
                'Cascade.id' => $CascadeCurrent['Cascade']['parent']
                )
            )
        );

        $CascadeChildrenList = $this->Navigation->CascadeGetChildrenList($CascadeCurrent['Cascade']['id']);

        $SettingsArray['addsearching'] = array('discription' => __('Searching', true), 'controller' => 'searchings','action' => 'search', 'terms' => $this->request->projectvars['VarsArray'],);

        $OrdersTestingComps = $this->Order->OrdersTestingcomp->find('list', array(
            'fields' => array('order_id'),
            'conditions' => array(
                'OrdersTestingcomp.testingcomp_id' => $this->Auth->user('testingcomp_id')
                )
            )
        );

        $CascadesOrder = $this->Order->CascadesOrder->find('all', array(
            'conditions' => array(
                'CascadesOrder.order_id' => $OrdersTestingComps,
                'CascadesOrder.cascade_id' => $CascadeChildrenList
                )
            )
        );

        $CascadeChildrenCascadeIdList = array_unique(Hash::extract($CascadesOrder, '{n}.CascadesOrder.cascade_id'));
        $CascadeChildrenOrderIdList = array_unique(Hash::extract($CascadesOrder, '{n}.CascadesOrder.order_id'));

        $Cascade = array(
            'current' => $CascadeCurrent,
            'child' => $CascadeChild,
            'parent' => $CascadeParent
        );

        $breads = $this->Navigation->Breads(null);

        $CascadeForBread[] = $Cascade['current'];
        $CascadeForBread = $this->Navigation->CascadeGetBreads($CascadeForBread);

        $breads = $this->Navigation->CascadeCreateBreadcrumblist($breads, $CascadeForBread);

        if($CascadeCurrent['Cascade']['orders'] == 0) {
          $SettingsArray['addlink'] = array('discription' => __('Add element', true), 'controller' => 'cascades','action' => 'add', 'terms' => $this->request->projectvars['VarsArray'],);
        }

        $this->set('SettingsArray', $SettingsArray);

        if ($CascadeCurrent['Cascade']['orders'] > 0 && count($CascadeChild) == 0 && count($CascadesOrder) > 0) {
            $this->loadModel('Topproject');
            $this->Topproject->recursive = 1;
            $Topproject = $this->Topproject->find('first', array('conditions' => array('Topproject.id' => $projectID)));

            $this->Navigation->ResetSessionForPaging();

            if (!isset($this->request['data']['Reportnumber']['searching'])) {
                $this->Autorisierung->ConditionsTopprojectsTest($projectID);
            }

            //			Wenn über Suche muss noch rein
            //			elseif(isset($this->request['data']['Reportnumber']['searching']) && $this->request['data']['Reportnumber']['hidden'] == 2){
            //			}

            if ($CascadeCurrent['Cascade']['orders'] > 0) {
                // Aufträge nur anzeigen, wenn dies das letzte Element in der Reihe ist
                // kann man später vielleicht noch ändern
                if ($CascadeCurrent['Cascade']['child'] == 0) {

                  $deleted = 0;
                    $status = array('number' => 0, 'value' => __('open', true));
                    $OrdersStatus = array('all' => 0,'open' => 0,'closed' => 0);

                    if (isset($this->request->data['Equipment']['gender']) && $this->request->data['Equipment']['gender'] < 3) {
                        if ($this->request->data['Equipment']['gender'] == 0) {
                            $status = array('number' => 0, 'value' => __('open', true));
                        }
                        if ($this->request->data['Equipment']['gender'] == 1) {
                            $status = array('number' => 1, 'value' => __('close', true));
                        }
                        if ($this->request->data['Equipment']['gender'] == 2) {
                            $status = array('number' => 2, 'value' => __('all', true));
                        }
                    }

                    if (isset($this->request->data['Quicksearch']['this_id']) && $this->request->data['Quicksearch']['this_id'] > 0) {
                        $this_id = $this->Sicherheit->Numeric($this->request->data['Quicksearch']['this_id']);
                        $optionsDeliverynumber['Deliverynumber.order_id'] = $this_id;
                    }

                    $order_option = array();

                    $order_option = array();
                    $this->loadModel('OrdersTestingcomps');
                    $OrdersTestingcomps = $this->OrdersTestingcomps->find('all', array('fields' => array('order_id'), 'conditions' => array('testingcomp_id' => $this->Auth->user('Testingcomp.id'))));

                    $OrdersTestingcompsOption = Hash::extract($OrdersTestingcomps, '{n}.OrdersTestingcomps.order_id');
                    $order_option['Order.id'] = $OrdersTestingcompsOption;
                    $order_option['Order.deleted'] = $deleted;
                    $order_option['Order.topproject_id'] = $projectID;
                    $order_option['Order.cascade_id'] = $cascadeID;
                    $OrdersStatus['all'] = $this->Order->find('count', array('conditions' => $order_option));
                    $order_option['Order.status'] = 0;
                    $OrdersStatus['open'] = $this->Order->find('count', array('conditions' => $order_option));
                    $order_option['Order.status'] = 1;
                    $OrdersStatus['closed'] = $this->Order->find('count', array('conditions' => $order_option));
                    $order_option['Order.status'] = $status['number'];
                    $order_option['Order.id'] = $CascadeChildrenOrderIdList;

                    if ($status['number'] == 2) {
                        unset($order_option['Order.status']);
                    }

                    $this->paginate = array(
                        'conditions' => array($order_option),
                        'order' => array('Order.id' => 'desc'),
                        'limit' => 25
                    );

                    $Orders = $this->paginate('Order');

                    if (Configure::read('DevelopmentsEnabled') == true) {
                        $Orders = $this->Data->AddDevelopmentData($Orders);
                    }

                    unset($SettingsArray['addlink']);

                    $this->set('status', $status);
                    $this->set('OrdersStatus', $OrdersStatus);
                    $this->set('Orders', $Orders);
                    $this->set('breads', $breads);

                    if(count($Orders)== 1 && Configure::check('CascadeLoadModules') && Configure::read('CascadeLoadModules') == true){
                      $orderID = $Orders[0]['Order']['id'];
                      $this->redirect(array('controller' => 'reportnumbers', 'action' => 'index',$projectID,$cascadeID,$orderID));
                      return;
                    }
                    $this->render('orders');
                    return;
                }
                // Wenn Aufträge aktiviert sind
            } else {
                $this->loadModel('Reportnumber');

                $this->Reportnumber->recursive = -1;

                $ReportnumberCountOption['conditions']['Reportnumber.topproject_id'] = $projectID;
                $ReportnumberCountOption['conditions']['Reportnumber.cascade_id'] = $cascadeID;

                if ($this->Auth->user('Roll.id') > 4) {
                    $ReportnumberCountOption['conditions']['Reportnumber.testingcomp_id'] = $this->Auth->user('Testingcomp.id');
                }

                if (AuthComponent::user('Testingcomp.extern') == 1) {
                    unset($ReportnumberCountOption['conditions']['Reportnumber.testingcomp_id']);
                }

                $ReportnumberCount = array();
                $ReportnumberCount = count($this->Reportnumber->find('first', $ReportnumberCountOption));

                if ($ReportnumberCount == 1) {
                    $ReportnumberLink = array('description' => __('Show', true),'controller' => 'reportnumbers','action' => 'show');
                    unset($SettingsArray['addlink']);
                } else {
                    $ReportnumberLink = array('description' => __('Create', true),'controller' => 'testingmethods','action' => 'listing');
                }

                $reportsArrayContainer = $this->Navigation->SubMenue(
                    'Report',
                    'Topproject',
                    $ReportnumberLink['description'],
                    $ReportnumberLink['action'],
                    $ReportnumberLink['controller'],
                    $this->request->projectvars['VarsArray']
                );

                $this->set('reportsContainer', $reportsArrayContainer);
                $this->set('SettingsArray', $SettingsArray);
                $this->set('Cascade', $Cascade);
                $this->set('breads', $breads);
                $this->render('reports');
            }
        }

        if ($CascadeCurrent['Cascade']['orders'] == 1 && count($CascadeChild) == 0 && count($CascadesOrder) == 0) {
            $status = array('number' => 0, 'value' => __('open', true));
            $OrdersStatus = array('all' => 0,'open' => 0,'closed' => 0);

            unset($SettingsArray['addlink']);

            $this->set('SettingsArray', $SettingsArray);
            $this->set('status', $status);
            $this->set('OrdersStatus', $OrdersStatus);
            $this->set('Orders', array());
            $this->set('breads', $breads);
            $this->render('orders');
            return;
        }

        if (count($CascadeChild) == 1 && count($CascadeChildrenCascadeIdList) > 0) {

            $options = array();
            $options['Cascade.topproject_id'] = $projectID;
            $options['Cascade.parent'] = $CascadeCurrent['Cascade']['id'];
            $options['Cascade.id'] = $CascadeChildrenCascadeIdList;


            $this->paginate = array(
                'conditions' => $options,
                'order' => array('Cascade.discription' => 'asc'),
                'limit' => 25
            );

            $CascadeChild = $this->paginate('Cascade');
            $this->set('Cascade', $CascadeChild);

            $Cascade = array('current' => $CascadeCurrent,'child' => $CascadeChild,'parent' => $CascadeParent);

            $projects = array_values($this->Autorisierung->ConditionsTopprojects());

            $_projectID = array_search($projectID, $projects);
            $optionsNext = isset($projects[$_projectID+1]) ? $projects[$_projectID+1] : 0;
            $optionsBefore = isset($projects[$_projectID-1]) ? $projects[$_projectID-1] : 0;

            $optionsNext = array(
                    'fields' => array('id','projektname', 'subdivision'),
                    'conditions' => array('Topproject.id' => $optionsNext),
                    'limit' => 1
            );

            $optionsBefore = array(
                    'fields' => array('id','projektname','subdivision'),
                    'conditions' => array('Topproject.id' => $optionsBefore),
                    'limit' => 1
            );

            $this->set('Cascade', $Cascade);
            $this->set('projectID', $projectID);
            $this->set('breads', $breads);

            if ($CascadeCurrent['Cascade']['orders'] == 1) {
                if ($CascadeCurrent['Cascade']['child'] == 0) {
                    unset($SettingsArray['addlink']);
                    $this->set('SettingsArray', $SettingsArray);
                    $this->render('orders');
                } else {
                    $this->set('SettingsArray', $SettingsArray);
                    $this->render('index_orders');
                }
            }
        }

        if (count($CascadeChild) == 1 && count($CascadeChildrenCascadeIdList) == 0) {

            $options = array();
            $options['Cascade.topproject_id'] = $projectID;
            $options['Cascade.parent'] = $CascadeCurrent['Cascade']['id'];

            $this->paginate = array(
                'conditions' => $options,
                'order' => array('Cascade.discription' => 'asc'),
                'limit' => 25
            );

            $Cascade['child'] = $this->paginate('Cascade');

            $this->set('Cascade', $Cascade);
            $this->set('breads', $breads);

            $this->render('index');
        }

        // für den Fall, dass Cascaden aber noch keine Aufträge vorhanden sind
        $CascadeChilds = $this->Cascade->find('all', array('fields' => array('id'), 'conditions' => array('Cascade.parent' => $CascadeCurrent['Cascade']['id'])));

        if (count($CascadeChilds) == 0) {
            $this->loadModel('Reportnumber');

            $this->Reportnumber->recursive = -1;

            $ReportnumberCountOption['conditions']['Reportnumber.topproject_id'] = $projectID;
            $ReportnumberCountOption['conditions']['Reportnumber.cascade_id'] = $cascadeID;

            if ($this->Auth->user('Roll.id') > 4) {
                $ReportnumberCountOption['conditions']['Reportnumber.testingcomp_id'] = $this->Auth->user('Testingcomp.id');
            }

            if (AuthComponent::user('Testingcomp.extern') == 1) {
                unset($ReportnumberCountOption['conditions']['Reportnumber.testingcomp_id']);
            }

            $ReportnumberCount = array();
            $ReportnumberCount = count($this->Reportnumber->find('first', $ReportnumberCountOption));

            if ($ReportnumberCount == 1) {
                $ReportnumberLink = array('description' => __('Show', true),'controller' => 'reportnumbers','action' => 'show');
                unset($SettingsArray['addlink']);
            } else {
                $ReportnumberLink = array('description' => __('Create', true),'controller' => 'testingmethods','action' => 'listing');
            }

            $reportsArrayContainer = $this->Navigation->SubMenue(
                'Report',
                'Topproject',
                $ReportnumberLink['description'],
                $ReportnumberLink['action'],
                $ReportnumberLink['controller'],
                $this->request->projectvars['VarsArray']
            );

            $this->set('reportsContainer', $reportsArrayContainer);
            $this->set('SettingsArray', $SettingsArray);
            $this->set('Cascade', $Cascade);
            $this->set('breads', $breads);
            $this->render('reports');


            //			$this->render('reports');
        }

        foreach ($CascadeChilds as $_key => $_data) {
            $CascadeChilds[$_key] = $this->Data->BelongsToManySelected($_data, 'Cascade', 'Testingcomp', array('TestingcompsCascades','testingcomp_id','cascade_id'));

             if (count($CascadeChilds[$_key]['Testingcomp']['selected']) == 0) {
                unset($CascadeChilds[$_key]);
            }else{
              foreach ($CascadeChilds[$_key]['Testingcomp']['selected'] as $tskey => $tsvalue) {
                if($this->Auth->user('testingcomp_id') <> $tsvalue){
                  $no=true;
                }else{
                  $no = false;
                  break;
                }
              }
              if($no == true){
                unset($CascadeChilds[$_key]);
              }
            }
        }

        $CascadeChildrenCascadeIdList = array_unique(Hash::extract($CascadeChilds, '{n}.Cascade.id'));

        $option = array();
        $options['Cascade.topproject_id'] = $projectID;
        $options['Cascade.parent'] = $CascadeCurrent['Cascade']['id'];
        $options['Cascade.id'] = $CascadeChildrenCascadeIdList;

        $this->paginate = array(
            'conditions' => $options,
            'order' => array('Cascade.discription' => 'asc'),
            'limit' => 25
        );

        $Cascade['child'] = $this->paginate('Cascade');


        if (count($Cascade['child']) > 0) {

        //  $Cascade = $this->Cascades->SepardeCascadeCats($Cascade);
          //$Cascade = $this->Cascades->SelectCascadeCat($Cascade);
           $this->set('Cascade', $Cascade);
          $this->set('breads', $breads);

          if (isset($Cascade['current']['Cascade']['monitoring_summary'])&& $Cascade['current']['Cascade']['monitoring_summary'] > 0) {
          $this->loadModel('Monitoring');

          $SettingsArray = array();
		      $Task = $this->MonitoringTool->getTasks();

		      $this->set('tasks', $Task);

          $this->request->data = $this->MonitoringTool->GetData();

          $this->set('monitoring', $data);
          $this->render('index_monitoring_all');
          }
          if(isset($Cascade['Cascadegroups'])) $this->render('index_categorie');
          else $this->render('index');




        } else {

        }
    }

    public function add()
    {
      if(!isset($this->request->data['save_result'])) $this->__addajax();
      elseif(isset($this->request->data['save_result'])) $this->__addjson();
    }

    protected function __addjson()
    {

      $this->layout = 'blank';

      $projectID = $this->request->projectID;
      $cascadeID = $this->request->cascadeID;
      $orderID = $this->request->orderID;

      $ConditionsTopprojects = $this->Autorisierung->ConditionsTopprojects();

      $test = array_search($projectID, $ConditionsTopprojects);
      if ($test === false) {
          die('error');
      }

      $Data = array();

      $this->Cascade->recursive = -1;

      $Data['CascadeCurrent'] = $this->Cascade->find('first', array('conditions' => array('Cascade.id' => $cascadeID)));
      $Data['CascadeChild'] = $this->Cascade->find('all', array('fields' => array('id'), 'conditions' => array('Cascade.parent' => $Data['CascadeCurrent']['Cascade']['id'])));
      $Data['CascadeParent'] = $this->Cascade->find('first', array('conditions' => array('Cascade.id' => $Data['CascadeCurrent']['Cascade']['parent'])));
      $Data['CascadeCurrent'] = $this->Data->BelongsToManySelected($Data['CascadeCurrent'], 'Cascade', 'Testingcomp', array('TestingcompsCascades','testingcomp_id','cascade_id'));
      $Data['Testingcomps'] = $this->Cascade->Testingcomp->find('list', array('fields' => array('id','firmenname')));
      $Data['CascadeCurrent'] = $this->Data->BelongsToManySelected($Data['CascadeCurrent'], 'Cascade', 'CascadeGroup', array('CascadegroupsCascades','cascade_group_id','cascade_id'));
      $Data['CascadeGroups'] = $this->Cascade->CascadeGroup->find('list', array('fields' => array('id','deu')));

      if (isset($this->request->data['Cascade'])) {

          $this->request->data['Cascade']['topproject_id'] = $projectID;
          $this->request->data['Cascade']['status'] = 1;
          $this->request->data['Cascade']['level'] = $Data['CascadeCurrent']['Cascade']['level'] + 1;
          $this->request->data['Cascade']['parent'] = $Data['CascadeCurrent']['Cascade']['id'];
          $this->request->data['Cascade']['child'] = 0;

          $Test = $this->Cascades->CheckInputForAdd($this->request->data,$Data);

          if($Test !== false){

            $this->set('response',json_encode($Test));
            $this->render('json');
            return false;

          }

          $Data = $this->Cascades->SaveNewCascade($Data);

          if(isset($Data['Message'])){

            $this->set('response',json_encode($Data['Message']));
            $this->render('json');
            return;

          } else {

            $Message['success'][] = array(
      				'message' => __('New cascade has been saved.',true),
      				'id' => ''
      			);

            $Message['url'] = $Data['Url'];

            $this->set('response',json_encode($Message));
            $this->render('json');
            return;

          }

/*
          $this->Cascade->create();

          if ($this->Cascade->save($this->request->data)) {
              $this->Session->setFlash(__('Value was saved'));

              $this->Autorisierung->Logger($this->Cascade->getLastInsertID(), $this->request->data['Cascade']);

              $CascadeCurrentData['child'] = $this->Cascade->getLastInsertID();

              $this->Cascade->save($CascadeCurrentData);

              $this->request->projectvars['VarsArray'][1] = $this->Cascade->getLastInsertID();


              if (Configure::check('CascadeLoadModules') && Configure::read('CascadeLoadModules') == true) {
                  $FormName2['controller'] = 'cascades';
                  $FormName2['action'] = 'edit';
                  $FormName2['terms'] = implode('/', $this->request->projectvars['VarsArray']);
                  $this->set('FormName2', $FormName2);
              }
                  $FormName['controller'] = 'cascades';
                  $FormName['action'] = 'index';
                  $FormName['terms'] = implode('/', $this->request->projectvars['VarsArray']);
                  $this->set('FormName', $FormName);
          } else {
              $this->Session->setFlash(__('Value could not be saved. Please, try again.'));
          }
*/
      }

      $this->set('response',json_encode(array()));
      $this->render('json');

    }

    protected function __addajax()
    {

      $this->layout = 'modal';

      $projectID = $this->request->projectID;
      $cascadeID = $this->request->cascadeID;
      $orderID = $this->request->orderID;

      $ConditionsTopprojects = $this->Autorisierung->ConditionsTopprojects();

      $test = array_search($projectID, $ConditionsTopprojects);
      if ($test === false) {
          die('error');
      }

      $CascadeCurrent = $this->Cascade->find('first', array('conditions' => array('Cascade.id' => $cascadeID)));

      $this->Cascade->recursive = -1;
      $CascadeChild = $this->Cascade->find('all', array('fields' => array('id'), 'conditions' => array('Cascade.parent' => $CascadeCurrent['Cascade']['id'])));
      $CascadeParent = $this->Cascade->find('first', array('conditions' => array('Cascade.id' => $CascadeCurrent['Cascade']['parent'])));

      $CascadeCurrent = $this->Data->BelongsToManySelected($CascadeCurrent, 'Cascade', 'Testingcomp', array('TestingcompsCascades','testingcomp_id','cascade_id'));
      $Testingcomps = $this->Cascade->Testingcomp->find('list', array('fields' => array('id','firmenname')));
      $CascadeCurrent = $this->Data->BelongsToManySelected($CascadeCurrent, 'Cascade', 'CascadeGroup', array('CascadegroupsCascades','cascade_group_id','cascade_id'));
      $CascadeGroups = $this->Cascade->CascadeGroup->find('list', array('fields' => array('id','deu')));

      $Cascade = array('current' => $CascadeCurrent,'child' => $CascadeChild,'parent' => $CascadeParent);

      $CascadeTree[] = $Cascade['current'];
      $CascadeTree = $this->Navigation->CascadeGetBreads($CascadeTree);


      $this->loadModel('Reportnumber');
      $this->Reportnumber->recursive = -1;

      $ReportnumberCountOption['conditions']['Reportnumber.topproject_id'] = $projectID;
      $ReportnumberCountOption['conditions']['Reportnumber.cascade_id'] = $cascadeID;

      if ($this->Auth->user('Roll.id') > 4) {
          $ReportnumberCountOption['conditions']['Reportnumber.testingcomp_id'] = $this->Auth->user('Testingcomp.id');
      }

      if (AuthComponent::user('Testingcomp.extern') == 1) {
          unset($ReportnumberCountOption['conditions']['Reportnumber.testingcomp_id']);
      }

      $ReportnumberCount = array();
      $ReportnumberCount = $this->Reportnumber->find('first', $ReportnumberCountOption);

      if (count($ReportnumberCount) > 0) {
//          $this->Session->setFlash(__('Value could not be saved. Please, try again.'));
          $this->set('StopAdd', true);
      }

      $this->request->data['Cascade']['orders'] = 0;
      $this->request->data['Testingcomp']['Testingcomp'] = $Testingcomps;
      $this->request->data['Expediting']['Expediting'] = 0;
      $this->request->data['Advance']['Advance'] = 0;

//      $this->Flash->success(__('Value could not be saved.',true),array('key' => 'success'));

      $this->set('locale', $this->Lang->Discription());
      $this->set('Cascade', $Cascade);
      $this->set('CascadeTree', $CascadeTree);
      $this->set(compact('Testingcomps'));
      $this->set(compact('CascadeGroups'));

    }

    public function edit()
    {
        $this->layout = 'modal';

        $projectID = $this->request->projectID;
        $cascadeID = $this->request->cascadeID;
        $orderID = $this->request->orderID;

        $ConditionsTopprojects = $this->Autorisierung->ConditionsTopprojects();

        $test = array_search($projectID, $ConditionsTopprojects);

        if ($test === false) die('error');

        $Cascade = $this->Cascades->GetCompleteCascadeObject();

        $Testingcomps = $this->Cascade->Testingcomp->find('list', array('fields' => array('id','firmenname')));



/*
        $this->Cascade->recursive = 1;
        $CascadeCurrent = $this->Cascade->find('first', array('conditions' => array('Cascade.id' => $cascadeID)));

        $CascadeChild = $this->Cascade->find('all', array('fields' => array('id'), 'conditions' => array('Cascade.parent' => $CascadeCurrent['Cascade']['id'])));
        $CascadeParent = $this->Cascade->find('first', array('conditions' => array('Cascade.id' => $CascadeCurrent['Cascade']['parent'])));

        $CascadeCurrent = $this->Data->BelongsToManySelected($CascadeCurrent, 'Cascade', 'Testingcomp', array('TestingcompsCascades','testingcomp_id','cascade_id'));
        $Testingcomps = $this->Cascade->Testingcomp->find('list', array('fields' => array('id','firmenname'),'conditions' => array('Testingcomp.id' => $CascadeCurrent['Testingcomp']['selected'])));

        //Kategorien auflisten und ermittelt
        if (Configure::check('CascadeLoadModules') && Configure::read('CascadeLoadModules') == true) {
            $CascadeCurrent = $this->Data->BelongsToManySelected($CascadeCurrent, 'Cascade', 'CascadeGroup', array('CascadegroupsCascades','cascade_group_id','cascade_id'));
            $CascadeGroup = $this->Cascade->CascadeGroup->find('all','conditions' => array('CascadeGroup.id' => $CascadeCurrent['CascadeGroup']['selected'])));
            $CascadeGroups = $this->Cascade->CascadeGroup->find('list', array('fields' => array('id','deu'),'conditions' => array('CascadeGroup.id' => $CascadeCurrent['CascadeGroup']['selected'])));

            if (empty($CascadeGroups)) {
                $CascadeGroups = $this->Cascade->CascadeGroup->find('list', array('fields' => array('id','deu')));
            }
######################
            if (!empty($CascadeGroups)) {
                $xmlname = $this->Cascade->CascadeGroup->find('first', array('fields' => array('model','xml_name'),'conditions' => array('CascadeGroup.id' => $CascadeCurrent['CascadeGroup']['selected'])));
                $xml = $xmlname['CascadeGroup']['xml_name']; // Name der zu ladenden xml Datei

                $cascadegroupmodel = $xmlname['CascadeGroup']['model']; // Model wird benötigt um die Datenbanktabelle anzusprechen


                if (!empty($cascadegroupmodel)) {
                    $this->loadModel($cascadegroupmodel);
                    $groupdata = $this->$cascadegroupmodel->find('first', array('conditions'=>array('cascade_id'=>$cascadeID)));

                    if(count($groupdata)) $groupid = $groupdata[$cascadegroupmodel]['id'];

                }
                if (!empty($cascadegroupmodel)) {
                  pr($cascadegroupmodel);
  //                \techicalplaces
                    $arrayData = $this->Xml->LoadXmlFile('techicalplaces' . DS . '');
//                    $arrayData = $this->Xml->DatafromXml($xml, 'file', 'CascadeGroup', null, $cascadegroupmodel);
                }

                $this->set('settings', $arrayData['headoutput']);
            }

            $this->set(compact('CascadeGroups'));

        }

        $Cascade = array('current' => $CascadeCurrent,'child' => $CascadeChild,'parent' => $CascadeParent);
        $CascadeTree[] = $Cascade['current'];
        $CascadeTree = $this->Navigation->CascadeGetBreads($CascadeTree);

*/

        if (isset($this->request->data['Cascade'])) {

            $this->request->data['Cascade']['id'] = $Cascade['CascadeCurrent']['Cascade']['id'];

            if ($this->Cascade->save($this->request->data)) {
                if(!empty($this->request->data['Task']['date_to'])) {
                  $this->request->data['Task']['cascade_id'] =  $cascadeID;
                  $this->request->data['Task']['topproject_id'] =$projectID;
                  $this->Cascade->Task->save($this->request->data['Task']);
                }
                $this->Flash->success(__('Value was saved'), array('key' => 'success'));

                if (!empty($cascadegroupmodel) && isset($this->request->data[$cascadegroupmodel])) {
                    $this->loadModel($cascadegroupmodel);
                    $this->request->data[$cascadegroupmodel]['id'] =$groupid;
                    $this->request->data[$cascadegroupmodel]['cascade_id'] = $cascadeID;
                    $this->$cascadegroupmodel->save($this->request->data[$cascadegroupmodel]);
                }
                $this->Autorisierung->Logger($Cascade['CascadeCurrent']['Cascade']['id'], $this->request->data['Cascade']);

                $this->request->projectvars['VarsArray'][1] = $Cascade['CascadeCurrent']['Cascade']['id'];

                $FormName['controller'] = 'cascades';
                $FormName['action'] = 'index';
                $FormName['terms'] = implode('/', $this->request->projectvars['VarsArray']);

                $this->set('FormName', $FormName);
            } else {
                if(isset($this->Testingcomp->validationErrors)){
                  $this->set('validationErrors',$this->Testingcomp->validationErrors);
                }
                  $this->Flash->error(__('Value could not be saved. Please, try again.'), array('key' => 'error'));
            }
        }
//pr($Cascade);
        $this->request->data = $Cascade['CascadeCurrent'];
/*
        if(!empty($cascadegroupmodel)){
            $this->loadModel($cascadegroupmodel);
            $groupdata = $this->$cascadegroupmodel->find('first', array('conditions'=>array('cascade_id'=>$cascadeID)));

            if(count($groupdata) > 0) $this->request->data[$cascadegroupmodel] = $groupdata[$cascadegroupmodel];

        }
*/
        $this->loadModel('Reportnumber');
        $this->Reportnumber->recursive = -1;

        $ReportnumberCountOption['conditions']['Reportnumber.topproject_id'] = $projectID;
        $ReportnumberCountOption['conditions']['Reportnumber.cascade_id'] = $cascadeID;

        if ($this->Auth->user('Roll.id') > 4) {
            $ReportnumberCountOption['conditions']['Reportnumber.testingcomp_id'] = $this->Auth->user('Testingcomp.id');
        }

        if (AuthComponent::user('Testingcomp.extern') == 1) {
            unset($ReportnumberCountOption['conditions']['Reportnumber.testingcomp_id']);
        }

        $ReportnumberCount = array();
        $ReportnumberCount = $this->Reportnumber->find('first', $ReportnumberCountOption);

        if (count($ReportnumberCount) > 0) {
            $this->set('AddRemoveOrders', false);
        } elseif (count($ReportnumberCount) == 0) {
            $this->set('AddRemoveOrders', true);
        }

        $SettingsArray['dellink'] = array('discription' => __('Delete cascade', true),'controller' => 'cascades','action' => 'delete', 'terms' => $this->request->params['pass']);
        if($Cascade['CascadeCurrent']['Cascade']['level'] == 0) unset($SettingsArray['dellink']);
        if(count($Cascade['CascadeChild']) > 0 && isset($SettingsArray['dellink'])) unset($SettingsArray['dellink']);


        $SettingsArray = $this->Autorisierung->AclCheckLinks($SettingsArray);

//        $this->set('locale', $this->Lang->Discription());
        $this->set('SettingsArray', $SettingsArray);
        $this->set('Cascade', $Cascade);
//        $this->set('CascadeTree', $CascadeTree);
        $this->set(compact('Testingcomps'));
    }

    public function delete()
    {
        $this->layout = 'modal';

        $projectID = $this->request->projectID;
        $cascadeID = $this->request->cascadeID;
        $orderID = $this->request->orderID;

        $ConditionsTopprojects = $this->Autorisierung->ConditionsTopprojects();

        $test = array_search($projectID, $ConditionsTopprojects);

        if ($test === false) die('error');

        $this->Cascade->recursive = -1;
        $CascadeCurrent = $this->Cascade->find('first', array('conditions' => array('Cascade.id' => $cascadeID)));

        $CascadeChild = $this->Cascade->find('all', array('fields' => array('id'), 'conditions' => array('Cascade.parent' => $CascadeCurrent['Cascade']['id'])));
        $CascadeParent = $this->Cascade->find('first', array('conditions' => array('Cascade.id' => $CascadeCurrent['Cascade']['parent'])));

        $CascadeCurrent = $this->Data->BelongsToManySelected($CascadeCurrent, 'Cascade', 'Testingcomp', array('TestingcompsCascades','testingcomp_id','cascade_id'));
        $Testingcomps = $this->Cascade->Testingcomp->find('list', array('fields' => array('id','firmenname'),'conditions' => array('Testingcomp.id' => $CascadeCurrent['Testingcomp']['selected'])));

        $Cascade = array('current' => $CascadeCurrent,'child' => $CascadeChild,'parent' => $CascadeParent);
        $CascadeTree[] = $Cascade['current'];
        $CascadeTree = $this->Navigation->CascadeGetBreads($CascadeTree);

        $SettingsArray['backlink'] = array('discription' => __('Back', true),'controller' => 'cascades','action' => 'edit', 'terms' => $this->request->params['pass']);
        $SettingsArray = $this->Autorisierung->AclCheckLinks($SettingsArray);
        $this->set('SettingsArray', $SettingsArray);

        if($CascadeCurrent['Cascade']['level'] == 0 || count($CascadeChild) > 0) {

          $this->request->projectvars['VarsArray'][1] = 0;
          $FormName['controller'] = 'topprojects';
          $FormName['action'] = 'index';
          $FormName['terms'] = implode('/', $this->request->projectvars['VarsArray']);
          $this->set('FormName', $FormName);

        };

         if (isset($this->request->data['Cascade'])) {

          $Delete = $this->Cascades->DeleteCascadeIncElements();

          if($Delete == true){

            array_shift($CascadeTree);
            $ParentCascade = array_shift($CascadeTree);
            $this->Session->setFlash(__('Cascade was deleted'));

            $this->request->projectvars['VarsArray'][1] = $ParentCascade['Cascade']['id'];

            $FormName['controller'] = 'cascades';
            $FormName['action'] = 'index';
            $FormName['terms'] = implode('/', $this->request->projectvars['VarsArray']);

            $this->set('FormName', $FormName);

          } else {
            $this->Session->setFlash(__('Cascade could not be deleted. Please, try again.'));
          }
        }

        $this->request->data = $CascadeCurrent;

    }


    public function autocomplete()
    {

//var_dump($this->request->data['field']); die();
        App::uses('Sanitize', 'Utility');

        $this->layout = 'json';
        $this->request->data['field'] = 'crb';
        $this->request->data['model'] = 'Rkl';
        //$fields = explode('_',Inflector::underscore($this->request->data['field']));
        $model = $this->request->data['model'];



  //      unset($fields[0]);


        $data = Sanitize::escape($this->request->data['term']);

        $field = $this->request->data['field'] = 'crb';

        $options = array(
                   'limit' => 20,
                            'fields' => array($field),
                            'group' => array($field),
                            'conditions' => array(
                                $model .'.'. $field . ' LIKE' => '%' . $data . '%'
                                )
                            );
        $this->loadModel($model);
        $result = $this->$model->find('list', $options);

        //$this->Autorisierung->WriteToLog($result);
        //var_dump($result); die();
        $this->set('test', json_encode($result));
    }
    public function update()
    {
        App::uses('Sanitize', 'Utility');

        $projectID = $this->request->projectID;
        $cascadeID = $this->request->cascadeID;
        $orderID = $this->request->orderID;

        $ConditionsTopprojects = $this->Autorisierung->ConditionsTopprojects();

        $test = array_search($projectID, $ConditionsTopprojects);
        if ($test === false) {
            die('error');
        }

        $this->Cascade->recursive = -1;
        $CascadeCurrent = $this->Cascade->find('first', array('conditions' => array('Cascade.id' => $cascadeID)));

        $CascadeChild = $this->Cascade->find('all', array('fields' => array('id'), 'conditions' => array('Cascade.parent' => $CascadeCurrent['Cascade']['id'])));
        $CascadeParent = $this->Cascade->find('first', array('conditions' => array('Cascade.id' => $CascadeCurrent['Cascade']['parent'])));

        $CascadeCurrent = $this->Data->BelongsToManySelected($CascadeCurrent, 'Cascade', 'Testingcomp', array('TestingcompsCascades','testingcomp_id','cascade_id'));
        $Testingcomps = $this->Cascade->Testingcomp->find('list', array('fields' => array('id','firmenname'),'conditions' => array('Testingcomp.id' => $CascadeCurrent['Testingcomp']['selected'])));

        if (Configure::check('CascadeLoadModules') && Configure::read('CascadeLoadModules') == true) {
            $CascadeCurrent = $this->Data->BelongsToManySelected($CascadeCurrent, 'Cascade', 'CascadeGroup', array('CascadegroupsCascades','cascade_group_id','cascade_id'));
            $CascadeGroups = $this->Cascade->CascadeGroup->find('list', array('fields' => array('id','deu'),'conditions' => array('CascadeGroup.id' => $CascadeCurrent['CascadeGroup']['selected'])));


            if (empty($CascadeGroups)) {
                $CascadeGroups = $this->Cascade->CascadeGroup->find('list', array('fields' => array('id','deu')));
            }
            if (!empty($CascadeGroups)) {
                $xmlname = $this->Cascade->CascadeGroup->find('first', array('fields' => array('model','xml_name'),'conditions' => array('CascadeGroup.id' => $CascadeCurrent['CascadeGroup']['selected'])));
                $xml = $xmlname['CascadeGroup']['xml_name']; // Name der zu ladenden xml Datei

                $cascadegroupmodel = $xmlname['CascadeGroup']['model']; // Model wird benötigt um die Datenbanktabelle anzusprechen

                if (!empty($cascadegroupmodel)) {
                    $this->loadModel($cascadegroupmodel);
                    $groupdata = $this->$cascadegroupmodel->find('first', array('conditions'=>array('cascade_id'=>$cascadeID)));

                    if(count($groupdata) > 0) $groupid = $groupdata[$cascadegroupmodel]['id'];

                }

                $arrayData = $this->Xml->DatafromXml($xml, 'file', 'CascadeGroup', null, $cascadegroupmodel);
                $arrayData = $arrayData['settings']->$cascadegroupmodel;

                foreach ($arrayData->children() as $key => $value) {
                    $xmlfield = Inflector::camelize(trim($value->key));
                    $xmlmodel= trim($value->model);
                    $fieldids[$xmlmodel.$xmlfield] = trim($value->key);
                    // code...
                }
            }
        }

        $this->layout = 'json';
        $this->request->data['field'] = 'crb';
        $this->request->data['model'] = 'Rkl';
        $model = $this->request->data['model'];
        $field = $this->request->data['field'] ;

        $value = $this->request->data['Cascade']['searching_autocomplet'];

        $options = array(

              'conditions' => array(
                $model .'.'. $field  => $value
                )
              );
        $this->loadModel($model);
        $result = $this->$model->find('all', $options);
        $result['result'] = $result[0]['Rkl'];
        $result['fieldids'] = $fieldids;

        $this->set('response', json_encode($result));
    }

	public function json_scheme() {

		$this->layout = 'blank';
    $this->loadModel('Monitoring');
		$Response = $this->MonitoringTool->GetNewData();
		sort($Response);

		$this->set('Response',json_encode($Response));

	}
}
