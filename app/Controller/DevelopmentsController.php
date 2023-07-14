<?php
App::uses('AppController', 'Controller');
/**
 * Orders Controller
 *
 * @property Order $Order
 */
class DevelopmentsController extends AppController
{
    public $components = array('Auth','Acl','Autorisierung','Cookie','Navigation','Lang','Sicherheit','Xml','Pdf','RequestHandler','Search','Data');
    public $helpers = array('Lang','Navigation','JqueryScripte','ViewData');
    public $layout = 'ajax';
    protected $writeprotection = false;

    public function beforeFilter()
    {
        App::import('Vendor', 'Authorize');
        // Es wird das in der Auswahl gewählte Project in einer Session gespeichert
        $this->Session->write('orderURL', null);

        $this->Navigation->ReportVars();

        //		$this->Autorisierung->ConditionsTopprojectsTest($this->request->projectID);

        $this->loadModel('User');
        $this->loadModel('Topproject');
        $this->Autorisierung->Protect();
        $this->Navigation->ajaxURL();
        $this->Lang->Choice();
        $this->Lang->Change();

        $this->Development->Order->Reportnumber->Report->Reportlock->recursive = -1;
        $this->writeprotection = 0 < $this->Development->Order->Reportnumber->Report->Reportlock->find('count', array('conditions'=>array('Reportlock.topproject_id'=>$this->request->projectID, 'Reportlock.report_id'=>$this->request->reportID)));
        $this->set('writeprotection', $this->writeprotection);

        $this->set('lang', $this->Lang->Choice());
        $this->set('selected', $this->Lang->Selected());
        $this->set('login_info', $this->Navigation->loggedUser());
        $this->set('lang_choise', $this->Lang->Choice());
        $this->set('previous_url', $this->base.'/'.$this->Session->read('lastURL'));
        $this->set('locale', $this->Lang->Discription());
        $this->set('SettingsArray', null);

        if (isset($this->Auth)) {
            $this->set('authUser', $this->Auth);
        }
    }

    public function afterFilter()
    {
        $this->Navigation->lastURL();
    }

    public function index()
    {
        $this->layout = 'modal';
        $SettingsArray['addlink'] = array('discription' => __('Add progress', true), 'controller' => 'developments','action' => 'add', 'terms' => array());
        $this->set('SettingsArray', $SettingsArray);
    }

    public function overview()
    {
        $this->layout = 'modal';

        $options = array();
        //		$options['Testingcomp.id'] = $this->Session->read('conditionsTestingcomps');

        $this->paginate = array(
            'conditions' => array($options),
            'order' => array('id' => 'desc'),
//			'order' => array('projektname' => 'asc'),
            'limit' => 25
        );

        $developments = $this->paginate('Development');

        $SettingsArray['addlink'] = array('discription' => __('Add progress', true), 'controller' => 'developments','action' => 'add', 'terms' => array());

        $this->set('SettingsArray', $SettingsArray);
        $this->set('developments', $developments);
    }

    public function progress()
    {
        $this->include_progress();
    }

    public function include_progress()
    {
        $this->layout = 'modal';

        $projectID = $this->request->projectvars['VarsArray'][0];
        $cascadeID = $this->request->projectvars['VarsArray'][1];
        $orderID = $this->request->projectvars['VarsArray'][2];
        $developmentID = $this->request->projectvars['VarsArray'][3];
        $ThisStatus = $this->request->projectvars['VarsArray'][4];

        $statusDiscription = array('3' => 'all', '0' => 'open', '1' => 'repairs', '2' => 'closet');
        //		$status = array('number' => 0, 'value' => __('open', true));
        $OrdersStatus = array('all' => 0,'open' => 0,'repairs' => 0,'closet' => 0);

        $optionsDevelopmentData['conditions']['DevelopmentData.topproject_id'] =  $projectID;
        $optionsDevelopmentData['conditions']['DevelopmentData.order_id'] =  $orderID;
        $optionsDevelopmentData['conditions']['DevelopmentData.development_id'] =  $developmentID;

        $optionsDevelopmentData['conditions']['DevelopmentData.result'] =  array(0,1,2);
        $OrdersStatus['all'] = $this->Development->DevelopmentData->find('count', $optionsDevelopmentData);

        $optionsDevelopmentData['conditions']['DevelopmentData.result'] =  array(0);
        $OrdersStatus['open'] = $this->Development->DevelopmentData->find('count', $optionsDevelopmentData);

        $optionsDevelopmentData['conditions']['DevelopmentData.result'] =  array(1);
        $OrdersStatus['repairs'] = $this->Development->DevelopmentData->find('count', $optionsDevelopmentData);

        $optionsDevelopmentData['conditions']['DevelopmentData.result'] =  array(2);
        $OrdersStatus['closet'] = $this->Development->DevelopmentData->find('count', $optionsDevelopmentData);

        if ($this->request->params['action'] == 'progress') {
            $this->Session->write($this->request->params['controller'] . '.' . $this->request->params['action'] . '.' . $developmentID . '.' .'params.', $this->request->params);
        } elseif ($this->request->params['action'] != 'progress') {
            $named = $this->Session->read('developments.progress.' . $developmentID . '.params');
            foreach ($named as $_named) {
                $this->request->params['named'] = $_named['named'];
                break;
            }
            $this->request->params['action'] = 'progress';
        }


        //		$status['number'] = array(0,1,2);
        if (isset($this->request->params['pass'][5])) {
            $this->request->data['DevelopmentData']['status'] = $ThisStatus;
            $status['number'] = array($ThisStatus);

            if ($ThisStatus == 3) {
                $status['number'] = array(0,1,2);
            }
        } elseif (isset($this->request->data['DevelopmentData']['status'])) {
            $status['value'] = $statusDiscription[$this->request->data['DevelopmentData']['status']];
            $this->request->params['pass'][5] = $this->request->data['DevelopmentData']['status'];

            if ($this->request->data['DevelopmentData']['status'] == 3) {
                $status['number'] = array(0,1,2);
            } else {
                $status['number'] = $this->Sicherheit->Numeric($this->request->data['DevelopmentData']['status']);
            }
        }

        $this->Session->write('developments.'.$developmentID.'.progress.'.$orderID.'.named', $this->request->params['named']);

        $options['DevelopmentData.topproject_id'] =  $projectID;
        $options['DevelopmentData.order_id'] =  $orderID;
        $options['DevelopmentData.development_id'] = $developmentID;
        $options['DevelopmentData.result'] = $status['number'];

        $this->paginate = array(
            'conditions' => array($options),
            'order' => array('id' => 'desc'),
//			'order' => array('projektname' => 'asc'),
            'limit' => 10
        );


        $developments = $this->paginate('DevelopmentData');

        $this->loadModel('Topproject');
        $this->Topproject->recursive = 2;
        $optionsReports = array('conditions' => array('Topproject.'.$this->Topproject->primaryKey => $projectID));
        $topproject = $this->Topproject->find('first', $optionsReports);

        $alltestingmethods = array();
        $alltestingmethodsModel = array();
        $topproject = $this->Data->BelongsToManySelected($topproject, 'Topproject', 'Report', array('ReportsTopprojects','report_id','topproject_id'));

        $topproject['Report'] = $this->Topproject->Report->find('all', array('conditions' => array('Report.id' => $topproject['Report']['selected'])));

        foreach ($topproject['Report'] as $_report) {
            $TestingmethodIds = $this->Data->BelongsToManySelected($_report, 'Report', 'Testingmethod', array('TestingmethodsReports','testingmethod_id','report_id'));
            $Testingmethods = $this->Topproject->Report->Testingmethod->find('all', array('conditions' => array('Testingmethod.id' => $TestingmethodIds['Testingmethod']['selected'])));

            foreach ($Testingmethods as $__report) {
                $alltestingmethods[$__report['Testingmethod']['id']] = $__report['Testingmethod']['verfahren'];
                $alltestingmethodsModel[$__report['Testingmethod']['id']] = $__report['Testingmethod']['value'];
            }
        }

        $this->loadModel('Reportnumber');
        $this->Reportnumber->recursive = -1;

        foreach ($developments as $_key => $_developments) {
            $developments[$_key]['DevelopmentData']['testing_method'] = $alltestingmethods[$developments[$_key]['DevelopmentData']['testing_method']];

            if ($_developments['DevelopmentData']['evaluation_id'] > 0) {
                $ReportEvaluation = 'Report' . ucfirst($alltestingmethodsModel[$_developments['DevelopmentData']['testing_method']]) . 'Evaluation';

                if (!isset($this->$ReportEvaluation)) {
                    $this->loadModel($ReportEvaluation);
                }

                $optionsReports = array('conditions' => array($this->$ReportEvaluation->primaryKey => $_developments['DevelopmentData']['evaluation_id']));
                $evaluation = $this->$ReportEvaluation->find('first', $optionsReports);

                $reportnumber = $this->Reportnumber->find('first', array('conditions' => array('Reportnumber.id' => $evaluation[$ReportEvaluation]['reportnumber_id'])));

                $developments[$_key]['DevelopmentData']['description'] = $evaluation[$ReportEvaluation]['description'];
                $developments[$_key]['Reportnumber']= $reportnumber['Reportnumber'];
            } elseif ($_developments['DevelopmentData']['evaluation_id'] == 0 && $_developments['DevelopmentData']['result'] > 0) {
                $developments[$_key]['DevelopmentData']['description'] = __('this measuring point is not associated with a evaluation area', true);
            } else {
                $developments[$_key]['DevelopmentData']['description'] = '-';
            }
        }

        $SettingsArray = array();

        $SettingsArray['addlink'] = array('discription' => __('Add', true), 'controller' => 'developments','action' => 'progressadd', 'terms' => array_merge_recursive($this->request->projectvars['VarsArray'], array(1), array($ThisStatus)));

        $status['number'] = '-';

        $this->set('SettingsArray', $SettingsArray);
        $this->set('alltestingmethods', $alltestingmethods);
        $this->set('status', $status);
        $this->set('OrdersStatus', $OrdersStatus);
        $this->set('developments', $developments);
        $this->render('progress', 'modal');
    }

    public function change()
    {
        $this->layout = 'modal';

        $projectID = $this->request->projectvars['VarsArray'][0];
        $cascadeID = $this->request->projectvars['VarsArray'][1];
        $orderID = $this->request->projectvars['VarsArray'][2];
        $reportID = $this->request->projectvars['VarsArray'][3];
        $reportnumberID = $this->request->projectvars['VarsArray'][4];
        $evaluationID = $this->request->projectvars['VarsArray'][5];

        $this->loadModel('Reportnumber');
        $this->loadModel('Testingmethod');

        $this->Reportnumber->recursive = -1;
        $optionsReports = array('conditions' => array('Reportnumber.'.$this->Reportnumber->primaryKey => $reportnumberID));
        $reportnumber = $this->Reportnumber->find('first', $optionsReports);

        $this->Testingmethod->recursive = -1;
        $testingmethod = $this->Testingmethod->find('first', array('conditions' => array('id' => $reportnumber['Reportnumber']['testingmethod_id'])));

        $ReportEvaluation = 'Report' . ucfirst($testingmethod['Testingmethod']['value']) . 'Evaluation';

        $this->loadModel($ReportEvaluation);
        $evalution = $this->$ReportEvaluation->find(
            'first',
            array('fields' => array('description'),
                                                        'conditions' => array(
                                                            'id' => $evaluationID
                                                            )
                                                        )
        );

        $evalutions = $this->$ReportEvaluation->find(
            'list',
            array(
                                                        'conditions' => array(
                                                            'reportnumber_id' => $reportnumber['Reportnumber']['id'],
                                                            'description' => $evalution[$ReportEvaluation]['description'],
                                                            'deleted' => 0
                                                            )
                                                        )
        );

        $this->loadModel('Order');
        $this->Order->recursive = 1;
        $optionsReports = array('conditions' => array('Order.'.$this->Order->primaryKey => $orderID));
        $order = $this->Order->find('first', $optionsReports);

        $this->loadModel('OrdersDevelopments');
        $OrdersDevelopments = $this->OrdersDevelopments->find('first', array('conditions' => array('OrdersDevelopments.order_id' => $orderID)));

        $developmentID = $OrdersDevelopments['OrdersDevelopments']['development_id'];

        // Test ob von der aufgerufenen Naht schon ein Eintrag vorliegt
        if ($this->Development->DevelopmentData->find(
            'count',
            array(
                                                        'conditions' => array(
                                                            'DevelopmentData.evaluation_id' => $evalutions
                                                            )
                                                            )
        ) == 1) {
            $optionsDevelopmentData['conditions']['DevelopmentData.evaluation_id'] =  $evalutions;
            $options['DevelopmentData.evaluation_id'] = $evalutions;
        }

        $statusDiscription = array('-' => 'all', '0' => 'open', '1' => 'repairs', '2' => 'closet');
        $status = array('number' => 0, 'value' => __('open', true));
        $OrdersStatus = array('all' => 0,'open' => 0,'repairs' => 0,'closet' => 0);

        $optionsDevelopmentData['conditions']['DevelopmentData.topproject_id'] =  $projectID;
        $optionsDevelopmentData['conditions']['DevelopmentData.development_id'] =  $developmentID;
        $optionsDevelopmentData['conditions']['DevelopmentData.order_id'] =  $orderID;
        $optionsDevelopmentData['conditions']['DevelopmentData.testing_method'] =  $reportnumber['Reportnumber']['testingmethod_id'];
        $optionsDevelopmentData['conditions']['DevelopmentData.result'] =  array(0,1,2);

        $OrdersStatus['all'] = $this->Development->DevelopmentData->find('count', $optionsDevelopmentData);

        $optionsDevelopmentData['conditions']['DevelopmentData.result'] =  array(0);
        $OrdersStatus['open'] = $this->Development->DevelopmentData->find('count', $optionsDevelopmentData);

        $optionsDevelopmentData['conditions']['DevelopmentData.result'] =  array(1);
        $OrdersStatus['repairs'] = $this->Development->DevelopmentData->find('count', $optionsDevelopmentData);

        $optionsDevelopmentData['conditions']['DevelopmentData.result'] =  array(2);
        $OrdersStatus['closet'] = $this->Development->DevelopmentData->find('count', $optionsDevelopmentData);

        $status['number'] = array(0,1,2);

        if (isset($this->request->data['DevelopmentData']['status'])) {
            $status['value'] = $statusDiscription[$this->request->data['DevelopmentData']['status']];

            if ($this->request->data['DevelopmentData']['status'] == '-') {
                $status['number'] = array(0,1,2);
            } else {
                $status['number'] = $this->Sicherheit->Numeric($this->request->data['DevelopmentData']['status']);
            }
        }


        $options['DevelopmentData.topproject_id'] =  $projectID;
        $options['DevelopmentData.order_id'] =  $orderID;
        $options['DevelopmentData.development_id'] = $developmentID;
        $options['DevelopmentData.testing_method'] = $reportnumber['Reportnumber']['testingmethod_id'];
        //		$options['DevelopmentData.result'] = array(0);

        if (isset($options['DevelopmentData.evaluation_id'])) {
            $options['DevelopmentData.result'] = array(0,1,2);
        } else {
            $options['DevelopmentData.result'] = array(0);
        }

        $this->loadModel('Topproject');
        $this->Topproject->recursive = 2;
        $optionsReports = array('conditions' => array('Topproject.'.$this->Topproject->primaryKey => $projectID));
        $topproject = $this->Topproject->find('first', $optionsReports);

        $topproject = $this->Data->BelongsToManySelected($topproject, 'Topproject', 'Report', array('ReportsTopprojects','report_id','topproject_id'));
        $topproject = $this->Data->BelongsToManySelected($topproject, 'Topproject', 'Testingcomp', array('TestingcompsTopprojects','testingcomp_id','topproject_id'));
        $topproject['Report'] = $this->Topproject->Report->find('all', array('conditions' => array('Report.id' => $topproject['Report']['selected'])));

        $alltestingmethods = array();

        foreach ($topproject['Report'] as $_report) {
            $_report = $this->Data->BelongsToManySelected($_report, 'Report', 'Testingmethod', array('TestingmethodsReports','testingmethod_id','report_id'));
            $_report['Testingmethod'] = $this->Topproject->Report->Testingmethod->find('all', array('conditions' => array('Testingmethod.id' => $_report['Testingmethod']['selected'])));

            foreach ($_report['Testingmethod'] as $__report) {
                $alltestingmethods[$__report['Testingmethod']['id']] = $__report['Testingmethod']['verfahren'];

                if ($__report['Testingmethod']['id'] == $reportnumber['Reportnumber']['testingmethod_id']) {
                    $thisTestingmethod = $__report['Testingmethod'];
                }
            }
        }

        $this->paginate = array(
            'conditions' => array($options),
            'order' => array('id' => 'desc'),
//			'order' => array('projektname' => 'asc'),
            'limit' => 25
        );

        $developments = $this->paginate('DevelopmentData');

        foreach ($developments as $_key => $_developments) {
            if ($_developments['DevelopmentData']['evaluation_id'] > 0) {
                $ThisEvalution = $this->$ReportEvaluation->find(
                    'first',
                    array(
//												'fields' => array('description'),
                                                'conditions' => array(
                                                    'id' => $_developments['DevelopmentData']['evaluation_id']
                                                    )
                                                )
                );

                $developments[$_key]['Evaluation'] = $ThisEvalution[$ReportEvaluation];

                //				pr($ThisEvalution[$ReportEvaluation]['description']);
                if ($ThisEvalution[$ReportEvaluation]['reportnumber_id'] > 0) {
                    $this->Reportnumber->recursive = 0;
                    $ThisReportnumber = $this->Reportnumber->find(
                        'first',
                        array(
                                                            'conditions' => array(
                                                                'Reportnumber.id' => $ThisEvalution[$ReportEvaluation]['reportnumber_id']
                                                            )
                                                        )
                    );

                    if (in_array($ThisReportnumber['Reportnumber']['topproject_id'], $this->Session->read('conditionsTopprojects'))) {
                        $link = Router::url(array(
                                                'controller'=>'reportnumbers',
                                                'action'=>'edit',
                                                $ThisReportnumber['Reportnumber']['topproject_id'],
                                                $ThisReportnumber['Reportnumber']['cascade_id'],
                                                $ThisReportnumber['Reportnumber']['order_id'],
                                                $ThisReportnumber['Reportnumber']['report_id'],
                                                $ThisReportnumber['Reportnumber']['id']
                                                ), false);

                        $ThisReportnumber['Reportnumber']['link_to'] = $link;
                        $developments[$_key]['Reportnumber'] = $ThisReportnumber['Reportnumber'];
                    } else {
                        $developments[$_key]['Reportnumber']['Reportnumber']['link_to_false'] = __('Sie haben keinen Zugriff auf diesen Prüfbericht', true);
                    }
                }
            }
        }

        $this->Reportnumber->recursive = -1;

        $ReportEvaluation = 'Report' . ucfirst($thisTestingmethod['value']) . 'Evaluation';

        $this->loadModel($ReportEvaluation);

        $optionsReports = array(
                            'conditions' => array(
                                                $this->$ReportEvaluation->primaryKey => $evaluationID
                                            )
                                        );


        $evaluation = $this->$ReportEvaluation->find('first', $optionsReports);

        $AllEvaluationIDs = $this->$ReportEvaluation->find('list', array(
                                                                        'fields' => array('id'),
                                                                        'conditions' =>
                                                                            array(
                                                                                $ReportEvaluation.'.deleted' => 0,
                                                                                $ReportEvaluation.'.reportnumber_id' => $evaluation[$ReportEvaluation]['reportnumber_id'],
                                                                                $ReportEvaluation.'.description' => $evaluation[$ReportEvaluation]['description']
                                                                            )));


        $SettingsArray = array();

        $status['number'] = '-';

        $this->set('SettingsArray', $SettingsArray);
        $this->set('thisTestingmethod', $thisTestingmethod);
        $this->set('evaluation', $evaluation[$ReportEvaluation]);
        $this->set('alltestingmethods', $alltestingmethods);
        $this->set('status', $status);
        $this->set('OrdersStatus', $OrdersStatus);
        $this->set('developments', $developments);
    }

    public function progressadd()
    {
        $this->layout = 'modal';

        $SettingsArray = array();

        $projectID = $this->request->projectvars['VarsArray'][0];
        $cascadeID = $this->request->projectvars['VarsArray'][1];
        $orderID = $this->request->projectvars['VarsArray'][2];
        $developmentID = $this->request->projectvars['VarsArray'][3];
        $ThisStatus = $this->request->projectvars['VarsArray'][4];
        $developmentdataID = $this->request->projectvars['VarsArray'][5];

        $alltestingmethods = array();
        $this->loadModel('Topproject');
        $this->Topproject->recursive = 2;
        $optionsReports = array('conditions' => array('Topproject.'.$this->Topproject->primaryKey => $projectID));
        $topproject = $this->Topproject->find('first', $optionsReports);

        $topproject = $this->Topproject->find('first', $optionsReports);
        $topproject = $this->Data->BelongsToManySelected($topproject, 'Topproject', 'Report', array('ReportsTopprojects','report_id','topproject_id'));
        $topproject['Report'] = $this->Topproject->Report->find('all', array('conditions' => array('Report.id' => $topproject['Report']['selected'])));

        $alltestingmethods = array();

        foreach ($topproject['Report'] as $_report) {
            $TestingmethodIds = $this->Data->BelongsToManySelected($_report, 'Report', 'Testingmethod', array('TestingmethodsReports','testingmethod_id','report_id'));
            $Testingmethods = $this->Topproject->Report->Testingmethod->find('all', array('conditions' => array('Testingmethod.id' => $TestingmethodIds['Testingmethod']['selected'])));

            foreach ($Testingmethods as $__report) {
                $alltestingmethods[$__report['Testingmethod']['id']] = $__report['Testingmethod']['verfahren'];
            }
        }

        $status = array_pop($this->request->params['pass']);
        $forwarding = array_pop($this->request->params['pass']);

        if ($forwarding == 1) {
            $SettingsArray['backlink'] = array('discription' => __('Back', true), 'controller' => 'developments','action' => 'progress', 'terms' => $this->request->projectvars['VarsArray']);
        }
        if ($forwarding == 2) {
            $SettingsArray['backlink'] = array('discription' => __('Back', true), 'controller' => 'developments','action' => 'orderdetails', 'terms' => array($developmentID,$orderID));
        }

/*
        if (!empty($this->request->projectvars['evalId'])) {

            $evalid = $this->request->projectvars['evalId'];
            $evalid = $this->request->projectvars['evalId'];
            $this->loadModel('Order');
            $this->Order->recursive = 1;
            $optionsReports = array('conditions' => array('Order.'.$this->Order->primaryKey => $orderID));
            $order = $this->Order->find('first', $optionsReports);

            $developmentID = $order['Development'][0]['id'];
            $newprog = $this->Development->DevelopmentData->find('first', array('conditions' =>
							array(
								'DevelopmentData.topproject_id' =>$projectID,
								'DevelopmentData.equipment_type_id' =>0,
								'DevelopmentData.equipment_id' =>0,
								'DevelopmentData.order_id' =>$orderID,
								'DevelopmentData.development_id' =>$developmentID,
								'DevelopmentData.evaluation_id' =>$evalid
								)));

            $newprog['DevelopmentData'] ['result'] = 0;
            $newprog['DevelopmentData'] ['id'] = 0;

            $newprog['DevelopmentData'] ['evaluation_id'] = '';
            $newprog['DevelopmentData'] ['measuring_point'] = 1;

            $newprog['sendform'] = '1';

            $FormName = array();
            $FormName['controller'] = 'reportnumbers';
            $FormName['action'] = 'edit';
            $FormName['terms'] = implode('/', $this->request->projectvars['VarsArray']);

            $FormNameContainerTerm = $this->request->projectvars['VarsArray'];
            $FormNameContainerTerm[3] = 0;
            $FormNameContainerTerm[4] = 0;
            $FormNameContainer = array();
            $FormNameContainer['controller'] = 'reportnumbers';
            $FormNameContainer['action'] = 'index';
            $FormNameContainer['terms'] = implode('/', $FormNameContainerTerm);

            $this->set('FormNameContainer', $FormNameContainer);
            $this->set('FormName', $FormName);
            $this->set('newprog', $newprog);
            $this->request->data = $newprog;
            //pr($newprog);
        }
*/
        if (isset($this->request->data['DevelopmentData']) || $this->request->is('put')) {
            if (isset($this->request->data['DevelopmentData']['measuring_point']) && $this->request->data['DevelopmentData']['measuring_point'] > 0) {
                $this->request->data['DevelopmentData']['topproject_id'] = $projectID;
                $this->request->data['DevelopmentData']['order_id'] = $orderID;
                $this->request->data['DevelopmentData']['development_id'] = $developmentID;

                for ($x = 0; $x < $this->request->data['DevelopmentData']['measuring_point']; $x++) {
                    $this->Development->DevelopmentData->create();
                    $this->Development->DevelopmentData->save($this->request->data['DevelopmentData']);
                }
            }

            if ($forwarding == 1) {
							$FormName = array();
							$FormName['controller'] = 'developments';
							$FormName['action'] = 'progress';
							$FormName['terms'] = implode('/', $this->request->projectvars['VarsArray']);

              $FormNameContainerTerm = $this->request->projectvars['VarsArray'];
              $FormNameContainerTerm[3] = 0;
              $FormNameContainerTerm[4] = 0;
              $FormNameContainer = array();
              $FormNameContainer['controller'] = 'reportnumbers';
              $FormNameContainer['action'] = 'index';
              $FormNameContainer['terms'] = implode('/', $FormNameContainerTerm);

              $this->set('FormNameContainer', $FormNameContainer);
							$this->set('FormName', $FormName);

            }
            if ($forwarding == 2) {
                $this->request->projectvars['VarsArray'][0] = $developmentID;
                $this->request->projectvars['VarsArray'][1] = $orderID;
                $this->request->projectvars['VarsArray'][2] = $status;
                $this->request->projectvars['VarsArray'][3] = 0;
                $this->request->projectvars['VarsArray'][4] = 0;
                $this->request->projectvars['VarsArray'][5] = 0;
                $this->request->projectvars['VarsArray'][6] = 0;

                $this->request->params['pass'][0] = $developmentID;
                $this->request->params['pass'][1] = $orderID;
                $this->request->params['pass'][2] = 3;
                $this->request->params['pass'][3] = 0;
                $this->request->params['pass'][4] = 0;
                $this->request->params['pass'][5] = 0;
                $this->request->params['pass'][6] = 0;
                $this->request->params['action'] = 'orderdetails';

                $this->request->params['named'] = $this->Session->read('developments.'.$developmentID.'.orderdetails.'.$orderID.'.named');
                $FormName = array();
                $FormName['controller'] = 'developments';
                $FormName['action'] = 'orders';
                $FormName['terms'] = $developmentID.'/';

                $this->set('FormName', $FormName);
                $this->set('saveOK', 1);
                $this->include_orderdetails();
            }

            return;
        }

				$this->request->data['DevelopmentData']['measuring_point'] = 1;

        $this->set('alltestingmethods', $alltestingmethods);
        $this->set('SettingsArray', $SettingsArray);
    }

    public function progressedit()
    {
        $this->layout = 'modal';

        $projectID = $this->request->projectvars['VarsArray'][0];
        $cascadeID = $this->request->projectvars['VarsArray'][1];
        $orderID = $this->request->projectvars['VarsArray'][2];
        $developmentID = $this->request->projectvars['VarsArray'][3];
        $ThisStatus = $this->request->projectvars['VarsArray'][4];
        $developmentdataID = $this->request->projectvars['VarsArray'][5];


        if (!$this->Development->DevelopmentData->exists($developmentdataID)) {
            throw new NotFoundException(__('Invalid evalution'));
        }

        $developmentdata = $this->Development->DevelopmentData->find('first', array('conditions' => array('DevelopmentData.id' =>$developmentdataID)));

        $this->loadModel('Topproject');
        $this->Topproject->recursive = 2;
        $optionsReports = array('conditions' => array('Topproject.'.$this->Topproject->primaryKey => $projectID));
        $topproject = $this->Topproject->find('first', $optionsReports);
        $topproject = $this->Data->BelongsToManySelected($topproject, 'Topproject', 'Report', array('ReportsTopprojects','report_id','topproject_id'));
        $topproject['Report'] = $this->Topproject->Report->find('all', array('conditions' => array('Report.id' => $topproject['Report']['selected'])));

        $alltestingmethods = array();

        foreach ($topproject['Report'] as $_report) {
            $TestingmethodIds = $this->Data->BelongsToManySelected($_report, 'Report', 'Testingmethod', array('TestingmethodsReports','testingmethod_id','report_id'));
            $Testingmethods = $this->Topproject->Report->Testingmethod->find('all', array('conditions' => array('Testingmethod.id' => $TestingmethodIds['Testingmethod']['selected'])));

            foreach ($Testingmethods as $__report) {
                $alltestingmethods[$__report['Testingmethod']['id']] = $__report['Testingmethod']['verfahren'];
            }
        }

        if ($this->request->projectvars['VarsArray'][6] == 1) {
            $Terms = $this->request->projectvars['VarsArray'];
            $Terms[5] = 0;
            $Terms[6] = 0;

            $SettingsArray['backlink'] = array('discription' => __('Back', true), 'controller' => 'developments','action' => 'progress', 'terms' => $Terms);
        }
        if ($this->request->projectvars['VarsArray'][6] == 2) {
            $SettingsArray['backlink'] = array('discription' => __('Back', true), 'controller' => 'developments','action' => 'orderdetails', 'terms' => array($developmentID,$orderID,$this->request->params['pass'][7]));
        }

        if (isset($this->request->data['DevelopmentData']) || $this->request->is('put')) {
            $this->request->data['DevelopmentData']['topproject_id'] = $projectID;
            $this->request->data['DevelopmentData']['cascade_id'] = $cascadeID;
            $this->request->data['DevelopmentData']['order_id'] = $orderID;
            $this->request->data['DevelopmentData']['development_id'] = $developmentID;

            if ($this->Development->DevelopmentData->save($this->request->data['DevelopmentData'])) {
                if ($this->request->projectvars['VarsArray'][6] == 1) {
                    $FormName = array();
                    $FormName['controller'] = 'developments';
                    $FormName['action'] = 'progress';
                    $FormName['terms'] = implode('/', $this->request->projectvars['VarsArray']);

                    $FormNameContainerTerm = $this->request->projectvars['VarsArray'];
                    $FormNameContainerTerm[3] = 0;
                    $FormNameContainerTerm[4] = 0;
                    $FormNameContainer = array();
                    $FormNameContainer['controller'] = 'reportnumbers';
                    $FormNameContainer['action'] = 'index';
                    $FormNameContainer['terms'] = implode('/', $FormNameContainerTerm);

                    $this->set('FormNameContainer', $FormNameContainer);
                    $this->set('FormName', $FormName);
                }
                if ($this->request->projectvars['VarsArray'][6] == 2) {
                    $this->request->projectvars['VarsArray'][0] = $developmentID;
                    $this->request->projectvars['VarsArray'][1] = $orderID;
                    $this->request->projectvars['VarsArray'][2] = $this->request->projectvars['VarsArray'][7];
                    $this->request->projectvars['VarsArray'][3] = 0;
                    $this->request->projectvars['VarsArray'][4] = 0;
                    $this->request->projectvars['VarsArray'][5] = 0;
                    $this->request->projectvars['VarsArray'][6] = 0;

                    $this->request->params['pass'][0] = $developmentID;
                    $this->request->params['pass'][1] = $orderID;
                    $this->request->params['pass'][2] = $this->request->projectvars['VarsArray'][7];
                    $this->request->params['pass'][3] = 0;
                    $this->request->params['pass'][4] = 0;
                    $this->request->params['pass'][5] = 0;
                    $this->request->params['pass'][6] = 0;
                    $this->request->params['action'] = 'orderdetails';

                    $this->request->params['named'] = $this->Session->read('developments.'.$developmentID.'.orderdetails.'.$orderID.'.named');
                    $FormName = array();
                    $FormName['controller'] = 'developments';
                    $FormName['action'] = 'orders';
                    $FormName['terms'] = $developmentID.'/';

                    $this->set('FormName', $FormName);
                    $this->set('saveOK', 1);
                    $this->include_orderdetails();
                }
                return;
            }
        }

        $this->request->data = $developmentdata;

        $this->set('alltestingmethods', $alltestingmethods);
        $this->set('SettingsArray', $SettingsArray);
    }

    public function progressdel()
    {
        $this->layout = 'modal';

				$projectID = $this->request->projectvars['VarsArray'][0];
        $cascadeID = $this->request->projectvars['VarsArray'][1];
        $orderID = $this->request->projectvars['VarsArray'][2];
        $developmentID = $this->request->projectvars['VarsArray'][3];
        $ThisStatus = $this->request->projectvars['VarsArray'][4];
        $developmentdataID = $this->request->projectvars['VarsArray'][5];

        if (!$this->Development->DevelopmentData->exists($developmentdataID)) {
            throw new NotFoundException(__('Invalid evalution'));
        }

        $developmentdata = $this->Development->DevelopmentData->find('first', array('conditions' => array('DevelopmentData.id' =>$developmentdataID)));

        if ($this->request->projectvars['VarsArray'][6] == 1) {
            $Terms = $this->request->projectvars['VarsArray'];
            $Terms[5] = 0;
            $Terms[6] = 0;

            $SettingsArray['backlink'] = array('discription' => __('Back', true), 'controller' => 'developments','action' => 'progress', 'terms' => $Terms);
        }
        if ($this->request->projectvars['VarsArray'][6] == 2) {
            $SettingsArray['backlink'] = array('discription' => __('Back', true), 'controller' => 'developments','action' => 'orderdetails', 'terms' => array($developmentID,$orderID,$this->request->params['pass'][7]));
        }

        if (isset($this->request->data['DevelopmentData']) || $this->request->is('put')) {
          $this->Development->DevelopmentData->id = $developmentdataID;

          if ($this->Development->DevelopmentData->delete()) {
            if ($this->request->projectvars['VarsArray'][6] == 1) {
              $FormName = array();
              $FormName['controller'] = 'developments';
              $FormName['action'] = 'progress';
              $FormName['terms'] = implode('/', $this->request->projectvars['VarsArray']);

              $FormNameContainerTerm = $this->request->projectvars['VarsArray'];
              $FormNameContainerTerm[3] = 0;
              $FormNameContainerTerm[4] = 0;
              $FormNameContainer = array();
              $FormNameContainer['controller'] = 'reportnumbers';
              $FormNameContainer['action'] = 'index';
              $FormNameContainer['terms'] = implode('/', $FormNameContainerTerm);

              $this->set('FormNameContainer', $FormNameContainer);
              $this->set('FormName', $FormName);
            }
            if ($this->request->projectvars['VarsArray'][6] == 2) {
                $this->request->projectvars['VarsArray'][0] = $developmentID;
                $this->request->projectvars['VarsArray'][1] = $orderID;
                $this->request->projectvars['VarsArray'][2] = $this->request->projectvars['VarsArray'][7];
                $this->request->projectvars['VarsArray'][3] = 0;
                $this->request->projectvars['VarsArray'][4] = 0;
                $this->request->projectvars['VarsArray'][5] = 0;
                $this->request->projectvars['VarsArray'][6] = 0;

                $this->request->params['pass'][0] = $developmentID;
                $this->request->params['pass'][1] = $orderID;
                $this->request->params['pass'][2] = $this->request->projectvars['VarsArray'][7];
                $this->request->params['pass'][3] = 0;
                $this->request->params['pass'][4] = 0;
                $this->request->params['pass'][5] = 0;
                $this->request->params['pass'][6] = 0;
                $this->request->params['action'] = 'orderdetails';

                //					$this->request->params['named'] = $this->Session->read('developments.'.$developmentID.'.orderdetails.'.$orderID.'.named');
                $FormName = array();
                $FormName['controller'] = 'developments';
                $FormName['action'] = 'orders';
                $FormName['terms'] = $developmentID.'/';

                $this->set('FormName', $FormName);
                $this->set('saveOK', 1);
                $this->include_orderdetails();
            }
            return;
          }
        }

        $this->request->data = $developmentdata;

        $this->set('SettingsArray', $SettingsArray);

    }

    public function result()
    {
        $this->layout = 'csv';

        $result = $this->Sicherheit->Numeric($this->request->data['DevelopmentData']['result']);
        $id = $this->Sicherheit->Numeric($this->request->data['DevelopmentData']['id']);

        $projectID = $this->request->projectvars['VarsArray'][0];
        $cascadeID = $this->request->projectvars['VarsArray'][1];
        $orderID = $this->request->projectvars['VarsArray'][2];
        $reportID = $this->request->projectvars['VarsArray'][3];
        $reportnumberID = $this->request->projectvars['VarsArray'][4];
        $evaluationID = $this->request->projectvars['VarsArray'][5];

        $evalutionIN = 0;

        $this->loadModel('Reportnumber');
        $this->Reportnumber->recursive = -1;
        $optionsReports = array('conditions' => array('Reportnumber.'.$this->Reportnumber->primaryKey => $reportnumberID));
        $reportnumber = $this->Reportnumber->find('first', $optionsReports);

        $this->loadModel('Order');
        $this->Order->recursive = -1;
        $optionsReports = array('conditions' => array('Order.'.$this->Order->primaryKey => $orderID));
        $order = $this->Order->find('first', $optionsReports);
        $developmentID = $order['Order']['development_id'];

        if ($this->request->data['DevelopmentData']['result'] > 0) {
            $this->request->data['DevelopmentData']['reportnumber_id'] = $reportnumberID;
            $this->request->data['DevelopmentData']['evaluation_id'] = $evaluationID;

            if ($this->request->data['DevelopmentData']['result'] == 1) {
                $this->loadModel('Topproject');
                $this->Topproject->recursive = 2;
                $optionsReports = array('conditions' => array('Topproject.'.$this->Topproject->primaryKey => $projectID));
                $topproject = $this->Topproject->find('first', $optionsReports);

                $alltestingmethods = array();

                foreach ($topproject['Report'] as $_report) {
                    foreach ($_report['Testingmethod'] as $__report) {
                        $alltestingmethods[$__report['id']] = $__report['verfahren'];
                    }
                }

                $this->set('alltestingmethods', $alltestingmethods);
            }
        }
        if ($this->request->data['DevelopmentData']['result'] == 0) {
            $this->request->data['DevelopmentData']['reportnumber_id'] = 0;
            $this->request->data['DevelopmentData']['evaluation_id'] = 0;
        }

        if ($this->Development->DevelopmentData->save($this->request->data)) {
            $this->request->data['DevelopmentData']['reportnumber_id'] = $reportnumberID;
            $this->request->data['DevelopmentData']['evaluation_id'] = $evaluationID;

            $this->Autorisierung->Logger($id, $this->request->data);

            $FormName = array();

            $FormName['controller'] = 'reportnumbers';

            if ($evalutionIN != 0) {
                $FormName['action'] = 'editevalution';
                $Terms = implode('/', $this->request->projectvars['VarsArray']);
                $FormName['terms'] = $Terms;
            } else {
                $FormName['action'] = 'edit';
                $Terms = implode('/', $this->request->projectvars['VarsArray']);
                $FormName['terms'] = $Terms;
            }

            $this->set('FormName', $FormName);

            $this->set('success', true);
            $this->set('message', __('Progress was saved'));
            $this->set('saveOK', 1);
        } else {
            $this->set('success', false);
            $this->set('message', __('Progress could not be saved. Please, try again.'));
        }

        $optionsDevelopmentData['conditions']['DevelopmentData.topproject_id'] =  $projectID;
        $optionsDevelopmentData['conditions']['DevelopmentData.development_id'] =  $developmentID;
        $optionsDevelopmentData['conditions']['DevelopmentData.testing_method'] =  $reportnumber['Reportnumber']['testingmethod_id'];

        $optionsDevelopmentData['conditions']['DevelopmentData.result'] =  array(0,1,2);

        $OrdersStatus['all'] = $this->Development->DevelopmentData->find('count', $optionsDevelopmentData);

        $optionsDevelopmentData['conditions']['DevelopmentData.result'] =  array(0);
        $OrdersStatus['open'] = $this->Development->DevelopmentData->find('count', $optionsDevelopmentData);

        $optionsDevelopmentData['conditions']['DevelopmentData.result'] =  array(1);
        $OrdersStatus['repairs'] = $this->Development->DevelopmentData->find('count', $optionsDevelopmentData);

        $optionsDevelopmentData['conditions']['DevelopmentData.result'] =  array(2);
        $OrdersStatus['closet'] = $this->Development->DevelopmentData->find('count', $optionsDevelopmentData);

        $this->set('OrdersStatus', $OrdersStatus);
    }

    public function add()
    {
        $this->layout = 'modal';

        if (isset($this->request->data['Development'])) {

//			$this->Autorisierung->ConditionsTesting($this->request->data['Topproject']['id'],'conditionsTopprojects');
            $this->Development->create();
            if ($this->Development->save($this->request->data)) {
                $id = $this->Development->getInsertID();
                $this->Autorisierung->Logger($id, $this->request->data);

                $this->Session->setFlash(__('Progress was saved'));
            } else {
                $this->Session->setFlash(__('Progress could not be saved. Please, try again.'));
            }
        }

        $options = array(
            'conditions' => array(
                'Testingcomp.id' => $this->Session->read('conditionsTestingcomps')
                ),
            'fields' => array('Testingcomp.firmenname')
            );

        $this->Development->Testingcomp->recursive = -1;
        $testingcomps = $this->Development->Testingcomp->find('list', $options);

        $SettingsArray['backlink'] = array('discription' => __('Back', true), 'controller' => 'developments','action' => 'overview', 'terms' => $this->request->projectvars['VarsArray']);

        $this->set('SettingsArray', $SettingsArray);
        $this->set('testingcomps', $testingcomps);
    }

    public function edit($id = null)
    {
        $this->layout = 'modal';

        if (isset($this->request->data['Development'])) {

//			$this->Autorisierung->ConditionsTesting($this->request->data['Topproject']['id'],'conditionsTopprojects');
            if ($this->Development->save($this->request->data)) {
                $this->Autorisierung->Logger($id, $this->request->data);

                $this->Session->setFlash(__('Progress was saved'));
            } else {
                $this->Session->setFlash(__('Progress could not be saved. Please, try again.'));
            }
        }

        $options =
            array(
                'conditions' => array(
                    'Development.'.$this->Development->primaryKey => $id
                )
            );

        $this->request->data = $this->Development->find('first', $options);
        $development = $this->Development->find('first', $options);

        $orders = array();

        foreach ($development['Order'] as $_orders) {
            $orders[$_orders['id']] = $_orders['auftrags_nr'];
        }

        $options = array(
            'conditions' => array(
                'Testingcomp.id' => $this->Session->read('conditionsTestingcomps')
                ),
            'fields' => array('Testingcomp.firmenname')
            );

        $this->Development->Testingcomp->recursive = -1;
        $testingcomps = $this->Development->Testingcomp->find('list', $options);

        $SettingsArray['backlink'] = array('discription' => __('Back', true), 'controller' => 'developments','action' => 'overview', 'terms' => $this->request->projectvars['VarsArray']);


        $this->set(compact('orders'));
        $this->set('SettingsArray', $SettingsArray);
        $this->set('testingcomps', $testingcomps);
    }

    public function create()
    {
        $this->layout = 'modal';

        $projectID = 0;
        $orderKat = 0;
    }

    public function show($id = null)
    {
        $options =
            array(
                'conditions' => array(
                    'Development.'.$this->Development->primaryKey => $id
                )
            );

        $development = $this->Development->find('first', $options);

        $this->loadModel('Topproject');
        $this->loadModel('Order');

        $this->Order->recursive = -1;
        $this->Topproject->recursive = -1;


        $breads = $this->Navigation->Breads(null);

        $breads[1]['discription'] = $development['Development']['name'];
        $breads[1]['controller'] = 'developments';
        $breads[1]['action'] = 'show';

        $SettingsArray = array();
        $SettingsArray['progresstool'] = array('discription' => __('Show progress', true), 'controller' => 'developments','action' => 'index', 'terms' => null);

        $this->set('breads', $breads);
        $this->set('SettingsArray', $SettingsArray);
        $this->set('progressmenue', $menue);
        $this->set('development', $development);
    }

    public function orders($id = null)
    {
        $status = array('number' => 0, 'value' => __('open', true));
        $OrdersStatus = array('all' => 0,'open' => 0,'closet' => 0);
        $OrdersIDs = array();
        $this->loadModel('Order');


        $options =
            array(
                'conditions' => array(
                    'Development.'.$this->Development->primaryKey => $id
                )
            );

        $this->Development->recursive = 2;
        $development = $this->Development->find('first', $options);

        foreach ($development['Order'] as $_orders) {
            if ($_orders['Topproject']['status'] == 0) {
                $OrdersStatus['all']++;
                $OrdersIDs[] = $_orders['id'];

                if ($_orders['status'] == 0) {
                    $OrdersStatus['open']++;
                }
                if ($_orders['status'] == 1) {
                    $OrdersStatus['closet']++;
                }
            }
        }

        if (isset($this->request->data['Orders']['status'])) {
            $this->Session->write($this->request->params['controller'] . '.' . $this->request->params['action'] . '.' . $id . '.' .'params.', $this->request->params);

            $ThisStatus = $this->Sicherheit->Numeric($this->request->data['Orders']['status']);

            $this->request->url = $this->request->url . '/' . $ThisStatus;
            $this->request->here = $this->request->here . '/' . $ThisStatus;
            $this->request->params['pass'][1] = $ThisStatus;
            $this->request->projectvars['VarsArray'][1] = $ThisStatus;

            $this->Session->write($this->request->params['controller'] . '.' . $this->request->params['action'] . '.' . $id . '.' .'status.', $ThisStatus);

            if ($this->request->params['pass'][1] == 2) {
                $status['number'] = array(0,1);
            } else {
                $status['number'] = $this->Sicherheit->Numeric($this->request->params['pass'][1]);
            }
        }
        if (isset($this->request->params['pass'][1])) {
            $this->Session->write($this->request->params['controller'] . '.' . $this->request->params['action'] . '.' . $id . '.' .'params.', $this->request->params);

            if ($this->Sicherheit->Numeric($this->request->params['pass'][1]) == 2) {
                $status['number'] = array(0,1);
            }
            if ($this->Sicherheit->Numeric($this->request->params['pass'][1]) == 1) {
                $status['number'] = array(1);
            }
            $this->request->data['Orders']['status'] = $this->request->params['pass'][1];
        }
        if (
            $this->Session->read($this->request->params['controller'] . '.' . $this->request->params['action'] . '.' . $id . '.' .'status.') != '' &&
            !isset($this->request->data['Orders']['status']) &&
            !isset($this->request->params['pass'][1])
            ) {
            $named = ($this->Session->read($this->request->params['controller'] . '.' . $this->request->params['action'] . '.' . $id . '.' .'params.'));

            $this->request->params['named'] = $named['named'];

            $ThisStatus = $this->Session->read($this->request->params['controller'] . '.' . $this->request->params['action'] . '.' . $id . '.' .'status.');
            $this->request->url = $this->request->url . '/' . $ThisStatus;
            $this->request->here = $this->request->here . '/' . $ThisStatus;
            $this->request->params['pass'][1] = $ThisStatus;
            $this->request->projectvars['VarsArray'][1] = $ThisStatus;
            $this->request->data['Orders']['status'] = $ThisStatus;


            if ($ThisStatus == 0) {
                $status['number'] = array(0);
            }
            if ($ThisStatus == 1) {
                $status['number'] = array(1);
            }
            if ($ThisStatus == 2) {
                $status['number'] = array(0,1);
            }
        }

        $options = array();
        $options['Order.id'] = $OrdersIDs;
        $options['Order.status'] = $status['number'];
        $options['Topproject.status'] = 0;

        if (count($this->request->params['named']) == 0 && isset($named[$this->request->params['controller']])) {
        }

        $this->paginate = array(
            'conditions' => array($options),
            'order' => array('id' => 'desc'),
            'limit' => 20
        );

        $deliverynumber = $this->paginate('Order');
        $progress = array('all' => 0, 'ok' => 0, 'rep' => 0, 'open' => 0, 'prozent_or' => 0, 'prozent_mr' => 0);
        $progress_all = array('all' => 0, 'ok' => 0, 'rep' => 0, 'open' => 0, 'prozent_or' => 0, 'prozent_mr' => 0);

        foreach ($deliverynumber as $_key => $_deliverynumber) {
            $DevelopmentData = $this->Development->DevelopmentData->find(
                'all',
                array(
                        'conditions' => array(
                                    'Development.id' => $id,
                                    'DevelopmentData.order_id' => $_deliverynumber['Order']['id']
                                    ),
                        'fields' => array('DevelopmentData.result')
                                )
            );

            foreach ($DevelopmentData as $_DevelopmentData) {
                $progress['all']++;
                if ($_DevelopmentData['DevelopmentData']['result'] == 0) {
                    $progress['open']++;
                }
                if ($_DevelopmentData['DevelopmentData']['result'] == 1) {
                    $progress['rep']++;
                }
                if ($_DevelopmentData['DevelopmentData']['result'] == 2) {
                    $progress['ok']++;
                }
            }


            $progress['prozent_or'] = @round(100 * $progress['ok'] / $progress['all'], 2);
            $progress['prozent_mr'] = @round(100 * ($progress['ok'] + $progress['rep']) / $progress['all'], 2);

            $deliverynumber[$_key]['progress'] = $progress;

            $progress_all['all'] = $progress_all['all'] + $deliverynumber[$_key]['progress']['all'];
            $progress_all['ok'] = $progress_all['ok'] + $deliverynumber[$_key]['progress']['ok'];
            $progress_all['rep'] = $progress_all['rep'] + $deliverynumber[$_key]['progress']['rep'];
            $progress_all['open'] = $progress_all['open'] + $deliverynumber[$_key]['progress']['open'];

            $progress = array('all' => 0, 'ok' => 0, 'rep' => 0, 'open' => 0, 'prozent_or' => 0, 'prozent_mr' => 0);
        }

        $progress_all['prozent_or'] = @round(100 * $progress_all['ok'] / $progress_all['all'], 2);
        $progress_all['prozent_mr'] = @round(100 * ($progress_all['ok'] + $progress_all['rep']) / $progress_all['all'], 2);

        $breads = $this->Navigation->Breads(null);

        $breads[1]['discription'] = $development['Development']['name'];
        $breads[1]['controller'] = 'developments';
        $breads[1]['action'] = 'orders';
        $breads[1]['pass'] = $this->request->projectvars['VarsArray'][0]. '/' . $this->request->params['pass'][1];

        $SettingsArray = array();
        $SettingsArray['progresstool'] = array('discription' => __('Show progress', true), 'controller' => 'developments','action' => 'index', 'terms' => null);

        $this->set('development', $development);
        $this->set('progress_all', $progress_all);
        $this->set('deliverynumber', $deliverynumber);
        $this->set('status', $status);
        $this->set('OrdersStatus', $OrdersStatus);
        $this->set('breads', $breads);
        $this->set('SettingsArray', $SettingsArray);
    }

    public function orderdetails()
    {
        $this->include_orderdetails();
    }

    public function include_orderdetails()
    {
        $this->layout = 'modal';
        $this->loadModel('Order');

        $id = $this->request->projectvars['VarsArray'][1];

        $deliverynumber = $this->Order->find('first', array('conditions' => array('Order.id' => $id)));

        $developmentID = $this->request->projectvars['VarsArray'][0];
        $this->request->projectvars['VarsArray'][0] = $deliverynumber['Order']['topproject_id'];
        $this->request->projectvars['VarsArray'][1] = $deliverynumber['Order']['cascade_id'];
        $this->request->projectvars['VarsArray'][2] = $deliverynumber['Order']['id'];
        $this->request->projectvars['VarsArray'][3] = $developmentID;

        $projectID = $this->request->projectvars['VarsArray'][0];
        $cascadeID = $this->request->projectvars['VarsArray'][1];
        $orderID = $this->request->projectvars['VarsArray'][2];
        $developmentID = $this->request->projectvars['VarsArray'][3];

        $statusDiscription = array('3' => 'all', '0' => 'open', '1' => 'repairs', '2' => 'closet');
        //		$status = array('number' => 0, 'value' => __('open', true));
        $OrdersStatus = array('all' => 0,'open' => 0,'repairs' => 0,'closet' => 0);

        $optionsDevelopmentData['conditions']['DevelopmentData.topproject_id'] =  $projectID;
        $optionsDevelopmentData['conditions']['DevelopmentData.order_id'] =  $orderID;
        $optionsDevelopmentData['conditions']['DevelopmentData.development_id'] =  $developmentID;
        $optionsDevelopmentData['conditions']['DevelopmentData.result'] =  array(0,1,2);
        $OrdersStatus['all'] = $this->Development->DevelopmentData->find('count', $optionsDevelopmentData);

        $optionsDevelopmentData['conditions']['DevelopmentData.result'] =  array(0);
        $OrdersStatus['open'] = $this->Development->DevelopmentData->find('count', $optionsDevelopmentData);

        $optionsDevelopmentData['conditions']['DevelopmentData.result'] =  array(1);
        $OrdersStatus['repairs'] = $this->Development->DevelopmentData->find('count', $optionsDevelopmentData);

        $optionsDevelopmentData['conditions']['DevelopmentData.result'] =  array(2);
        $OrdersStatus['closet'] = $this->Development->DevelopmentData->find('count', $optionsDevelopmentData);

        if (isset($this->request->params['pass'][2])) {
            $status['number'] = $this->Sicherheit->Numeric($this->request->params['pass'][2]);

            if ($status['number'] == 3) {
                $status['number'] = array(0,1,2);
            }

            $this->request->data['DevelopmentData']['status'] = $this->Sicherheit->Numeric($this->request->params['pass'][2]);
        } elseif (isset($this->request->data['DevelopmentData']['status'])) {
            $status['value'] = $statusDiscription[$this->request->data['DevelopmentData']['status']];
            $this->request->params['pass'][2] = $this->request->data['DevelopmentData']['status'];

            if ($this->request->data['DevelopmentData']['status'] == 3) {
                $status['number'] = array(0,1,2);
            } else {
                $status['number'] = $this->Sicherheit->Numeric($this->request->data['DevelopmentData']['status']);
            }
        }

        $this->Session->write('developments.'.$developmentID.'.orderdetails.'.$orderID.'.named', $this->request->params['named']);

        $options['DevelopmentData.topproject_id'] =  $projectID;
        $options['DevelopmentData.order_id'] =  $orderID;
        $options['DevelopmentData.development_id'] = $developmentID;
        $options['DevelopmentData.result'] = $status['number'];

        $this->paginate = array(
            'conditions' => array($options),
            'order' => array('id' => 'desc'),
//			'order' => array('projektname' => 'asc'),
            'limit' => 10
        );

        $developments = $this->paginate('DevelopmentData');

        $this->loadModel('Topproject');
        $this->Topproject->recursive = 2;
        $optionsReports = array('conditions' => array('Topproject.'.$this->Topproject->primaryKey => $projectID));
        $topproject = $this->Topproject->find('first', $optionsReports);

        $alltestingmethods = array();
        $alltestingmethodsModel = array();

        $topproject = $this->Data->BelongsToManySelected($topproject, 'Topproject', 'Report', array('ReportsTopprojects','report_id','topproject_id'));
        $topproject = $this->Data->BelongsToManySelected($topproject, 'Topproject', 'Testingcomp', array('TestingcompsTopprojects','testingcomp_id','topproject_id'));
        $topproject['Report'] = $this->Topproject->Report->find('all', array('conditions' => array('Report.id' => $topproject['Report']['selected'])));

        $alltestingmethods = array();

        foreach ($topproject['Report'] as $_report) {
            $_report = $this->Data->BelongsToManySelected($_report, 'Report', 'Testingmethod', array('TestingmethodsReports','testingmethod_id','report_id'));
            $_report['Testingmethod'] = $this->Topproject->Report->Testingmethod->find('all', array('conditions' => array('Testingmethod.id' => $_report['Testingmethod']['selected'])));

            foreach ($_report['Testingmethod'] as $__report) {
                $alltestingmethods[$__report['Testingmethod']['id']] = $__report['Testingmethod']['verfahren'];
                $alltestingmethodsModel[$__report['Testingmethod']['id']] = $__report['Testingmethod']['value'];
            }
        }

        $this->loadModel('Reportnumber');
        $this->Reportnumber->recursive = -1;

        foreach ($developments as $_key => $_developments) {
            if ($_developments['DevelopmentData']['evaluation_id'] > 0) {
                $ReportEvaluation = 'Report' . ucfirst($alltestingmethodsModel[$_developments['DevelopmentData']['testing_method']]) . 'Evaluation';
                $this->loadModel($ReportEvaluation);

                $optionsReports = array('conditions' => array($this->$ReportEvaluation->primaryKey => $_developments['DevelopmentData']['evaluation_id']));
                $evaluation = $this->$ReportEvaluation->find('first', $optionsReports);

                $reportnumber = $this->Reportnumber->find('first', array('conditions' => array('Reportnumber.id' => $evaluation[$ReportEvaluation]['reportnumber_id'])));

                $developments[$_key]['DevelopmentData']['description'] = $evaluation[$ReportEvaluation]['description'];
                $developments[$_key]['Reportnumber']= $reportnumber['Reportnumber'];
            } elseif ($_developments['DevelopmentData']['evaluation_id'] == 0 && $_developments['DevelopmentData']['result'] > 0) {
                $developments[$_key]['DevelopmentData']['description'] = __('this measuring point is not associated with a evaluation area', true);
            } else {
                $developments[$_key]['DevelopmentData']['description'] = '-';
            }
        }

        $SettingsArray = array();
        $SettingsArray['addlink'] = array('discription' => __('Add', true), 'controller' => 'developments','action' => 'progressadd', 'terms' => array_merge_recursive($this->request->projectvars['VarsArray'], array(2), array($this->request->params['pass'][2])));

        $this->set('deliverynumber', $deliverynumber);
        $this->set('SettingsArray', $SettingsArray);
        $this->set('alltestingmethods', $alltestingmethods);
        $this->set('status', $status);
        $this->set('OrdersStatus', $OrdersStatus);
        $this->set('developments', $developments);
        $this->render('orderdetails', 'modal');
    }

    public function diagramm($id = null)
    {
        $this->layout = 'jpgraph';

        $options =
            array(
                'conditions' => array(
                    'Development.'.$this->Development->primaryKey => $id
                )
            );

        $development = $this->Development->find('first', $options);
        $headline = $development['Development']['name'];
        $subheadline = null;

        $this->loadModel('Topproject');
        $this->loadModel('EquipmentType');
        $this->loadModel('Equipment');
        $this->loadModel('Order');

        $optionsDevelopmentData['conditions']['DevelopmentData.development_id'] =  $id;

        if ($this->request->projectvars['VarsArray'][1] > 0) {
            $projectID = $this->request->projectvars['VarsArray'][1];
            $optionsDevelopmentData['conditions']['DevelopmentData.topproject_id'] = $projectID;

            if ($this->request->projectvars['VarsArray'][2] > 0) {
                $equipmenttypeID = $this->request->projectvars['VarsArray'][2];
                $optionsDevelopmentData['conditions']['DevelopmentData.equipment_type_id'] = $equipmenttypeID;

                if ($this->request->projectvars['VarsArray'][3] > 0) {
                    $equipmentID = $this->request->projectvars['VarsArray'][3];
                    $optionsDevelopmentData['conditions']['DevelopmentData.equipment_id'] = $equipmentID;
                }
            }
        }

        $optionsDevelopmentData['group'] = array('topproject_id');
        $developmentdatas['projects'] = $this->Development->DevelopmentData->find('all', $optionsDevelopmentData);
        $optionsDevelopmentData['group'] = array();

        $this->Development->DevelopmentData->recursive = -1;
        $this->Topproject->recursive = -1;

        foreach ($developmentdatas['projects'] as $_projects) {
            $optionsDevelopmentData['conditions']['DevelopmentData.topproject_id'] = $_projects['DevelopmentData']['topproject_id'];
            $optionsDevelopmentData['conditions']['DevelopmentData.result'] = array(0,1,2);
            $developmentdatas['result'][$_projects['DevelopmentData']['topproject_id']]['all'] = $this->Development->DevelopmentData->find('count', $optionsDevelopmentData);

            $optionsDevelopmentData['conditions']['DevelopmentData.result'] = array(0);
            $developmentdatas['result'][$_projects['DevelopmentData']['topproject_id']]['open'] = $this->Development->DevelopmentData->find('count', $optionsDevelopmentData);

            $optionsDevelopmentData['conditions']['DevelopmentData.result'] = array(1);
            $developmentdatas['result'][$_projects['DevelopmentData']['topproject_id']]['repair'] = $this->Development->DevelopmentData->find('count', $optionsDevelopmentData);

            $optionsDevelopmentData['conditions']['DevelopmentData.result'] = array(2);
            $developmentdatas['result'][$_projects['DevelopmentData']['topproject_id']]['close'] = $this->Development->DevelopmentData->find('count', $optionsDevelopmentData);

            $developmentdatas['result'][$_projects['DevelopmentData']['topproject_id']]['dezimal'] = $developmentdatas['result'][$_projects['DevelopmentData']['topproject_id']]['close'] / $developmentdatas['result'][$_projects['DevelopmentData']['topproject_id']]['all'];

            $developmentdatas['result'][$_projects['DevelopmentData']['topproject_id']]['prozent'] = round($developmentdatas['result'][$_projects['DevelopmentData']['topproject_id']]['dezimal'] * 100, 2);

            $topproject = ($this->Topproject->find('first', array('conditions' => array('Topproject.id' => $_projects['DevelopmentData']['topproject_id']))));
            $developmentdatas['result'][$_projects['DevelopmentData']['topproject_id']]['name'] = $topproject['Topproject']['projektname'];

            $subheadline .= $topproject['Topproject']['projektname'] . " ";

            if ($this->request->projectvars['VarsArray'][2] > 0) {
                $equipmenttype = ($this->EquipmentType->find('first', array('conditions' => array('EquipmentType.id' => $this->request->projectvars['VarsArray'][2]))));
                $subheadline .= $equipmenttype['EquipmentType']['discription'] . " ";
            }
            if ($this->request->projectvars['VarsArray'][3] > 0) {
                $equipment = ($this->Equipment->find('first', array('conditions' => array('Equipment.id' => $this->request->projectvars['VarsArray'][3]))));
                $subheadline .= $equipment['Equipment']['discription'] . " ";
                $developmentdatas['result'][$_projects['DevelopmentData']['topproject_id']]['name'] = $equipment['Equipment']['discription'];
            }
        }

        $this->set('headline', $headline);
        $this->set('subheadline', $subheadline);
        $this->set('developmentdatas', $developmentdatas);
    }
}
