<?php
App::uses('AppController', 'Controller');
/**
 * Weldingmethods Controller
 *
 * @property Weldingmethod $Weldingmethod
 */
class WeldingmethodsController extends AppController
{
    public $components = array('Auth','Acl','Autorisierung','Cookie','Navigation','Lang','Sicherheit','SelectValue','Xml');
    protected $writeprotection = false;

    public $helpers = array('Lang','Navigation','JqueryScripte');
    public $layout = 'modal';

    public function beforeFilter()
    {
        App::import('Vendor', 'Authorize');
        App::uses('Folder', 'Utility');
        App::uses('File', 'Utility');
        $this->Navigation->ReportVars();
        $this->loadModel('User');
        $this->Autorisierung->Protect();
        $this->Navigation->ajaxURL();
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

    /**
     * index method
     *
     * @return void
     */
    public function index()
    {
        $this->layout = 'modal';
        $this->Weldingmethod->recursive = 0;

        $SettingsArray = array();
        $SettingsArray['backlink'] = array('discription' => __('Back', true), 'controller' => 'topprojects','action' => 'settings', 'terms' => null);
        $SettingsArray['addlink'] = array('discription' => __('Add', true), 'controller' => 'weldingmethods','action' => 'add', 'terms' => null);
        //			$SettingsArray['addsearching'] = array('discription' => __('Searching',true), 'controller' => 'reportnumbers','action' => 'search', 'terms' => null,);
        //			$SettingsArray['settingslink'] = array('discription' => __('Settings',true), 'controller' => 'topprojects','action' => 'settings', 'terms' => null,);

        $this->set('SettingsArray', $SettingsArray);


        $this->set('weldingmethods', $this->paginate());
    }

    public function listing()
    {
        if ($this->writeprotection) {
            $this->set('error', __('Report is writeprotected - changes will not be saved.'));
            $this->render('/closemodal', 'modal');
            return;
        }

        // Die IDs des Projektes und des Auftrages werden getestet
        $projectID = $this->request->projectvars['VarsArray'][0];
        $cascadeID = $this->request->projectvars['VarsArray'][1];
        $orderID = $this->request->projectvars['VarsArray'][2];
        $reportID = $this->request->projectvars['VarsArray'][3];
        if (isset($this->request->projectvars['VarsArray'][4]) && $this->request->projectvars['VarsArray'][4] > 0) {
            $reportnumberID = $this->request->projectvars['VarsArray'][4];
        }
        $modaloutput = false;

        $this->layout = 'modal';

        if (isset($this->request->params['pass'][3]) && $this->request->params['pass'][3] == 1) {
            $modaloutput = true;
        }


        //		$orderKat = $this->Session->read('orderKat');

        $reportsArray = $this->Navigation->SubMenue('Report', 'Topproject', __('Create', true), 'listing', 'weldingmethods', $this->Session->read('projectURL'));

        $optionReport = array(
                        'conditions' => array(
                                'Report.id' => $reportID
                                )
                            );

        $this->Weldingmethod->Report->recursive = -1;
        $report = $this->Weldingmethod->Report->find('first', $optionReport);

        $this->Session->write('reportURL', $report['Report']['id']);
        $this->loadModel('Topproject');
        $this->loadModel('Order');
        $this->Topproject->recursive = 0;
        $this->Order->recursive = -1;
        $optionTopproject = array('conditions' => array('Topproject.id' => $projectID));
        $topproject = $this->Topproject->find('first', $optionTopproject);
        $optionOrder = array('conditions' => array('Order.id' => $orderID));
        $order = $this->Order->find('first', $optionOrder);

        $menueArray = array(
            'general' => array(
                'haedline' => __('Actions', true),
                'controller' => 'reportnumbers',
                'discription' => array('Search Report','List Report'),
                'actions' => array('index','index/'.$projectID.'/'.$orderID),
                'params' => array(null,null),
            ));

        $options =
            array(
                'order' => array('Weldingmethod.verfahren ASC'),
                'conditions' => array(
                    'Weldingmethod.id' => $this->Autorisierung->ConditionsVariabel('Weldingmethod', 'Report', $reportID),
                    'Weldingmethod.enabled' => 1
                )
            );

        // TODO: noch die Unterscheidung nach Unterordnungen und �berordnungen
        if ($this->request->reportnumberID > 0 && isset($this->request->data['linked']) && $this->request->data['linked'] == 1) {
            $this->Autorisierung->IsThisMyReport($reportnumberID);
            $this->loadModel('Reportnumber');
            $this->Reportnumber->recursive = 0;

            $reportnumber = $this->Reportnumber->find('first', array('conditions'=>array('Reportnumber.id'=>$reportnumberID)));
            $options['conditions']['Weldingmethod.allow_children'] = !intval($reportnumber['Weldingmethod']['allow_children']);
            $this->set('allow_children', !intval($reportnumber['Weldingmethod']['allow_children']));
        }

        // Falls es keine Pr�fverfahren zum anzeigen gibt, gehts zur�ck
        if (count($this->Weldingmethod->find('all', $options)) == 0) {
            $this->Session->setFlash($report['Report']['name'].__(' has no weldingmethod', true));
            $this->set('afterEDIT', $this->Navigation->afterEDIT($this->Session->read('lastURL'), 2000));
        };

        $this->set('projectID', $projectID);
        $this->set('orderID', $orderID);
        $this->set('reportID', $reportID);
        $this->set('SettingsArray', null);

        $this->set('reportName', $report['Report']['name']);
        $this->set('reportid', $report['Report']['id']);
        $this->set('menues', $menueArray);
        $this->set('reports', $reportsArray);

        $this->Weldingmethod->recursive = 0;
        $weldingmethods = $this->Weldingmethod->find('all', $options);
        $weldingmethodIncIDs = array();
        $weldingmethod = array();

        // Die benötigten Variablen f�r den Link werden hinzugef�gt
        foreach ($weldingmethods as $_key =>$_weldingmethods) {
            if ($this->Xml->TestXmlExists($_weldingmethods['Weldingmethod']['value']) == false) {
                unset($weldingmethods[$_key]);
                continue;
            }

            $_weldingmethods['Weldingmethod']['projectID'] = $projectID;
            $_weldingmethods['Weldingmethod']['orderID'] = $orderID;
            $_weldingmethods['Weldingmethod']['reportID'] = $reportID;
            $weldingmethodIncIDs[] = $_weldingmethods;
        }

        // Es werden die Weldingmethods herausgefilter
        // f�r welche es keine XML-Vorlage gibt
        $dir = new Folder(Configure::read('xml_folder') . 'weldingmethod');
        foreach ($weldingmethodIncIDs as $_weldingmethods) {
            $files = $dir->findRecursive($_weldingmethods['Weldingmethod']['value'].'.xml');
            if (count($files) > 0) {
                $weldingmethod[] = $_weldingmethods;
            }
        }

        if ($order['Order']['status'] == 1) {
            $weldingmethod = array();
        }

        $this->set('weldingmethods', $weldingmethod);

        if ($modaloutput == true) {
            $this->render('/Weldingmethods/listingmodal');
        }
    }

    /**
     * view method
     *
     * @throws NotFoundException
     * @param string $id
     * @return void
     */
    public function view($id = null)
    {
        if (!$this->Weldingmethod->exists($id)) {
            throw new NotFoundException(__('Invalid weldingmethod'));
        }
        $this->Autorisierung->ConditionsTest('WeldingmethodsTopprojects', 'weldingmethod_id', $id);

        $options =
            array(
                'conditions' => array(
                    'Weldingmethod.'.$this->Weldingmethod->primaryKey => $id
                ),
                'contain' => array(
                    'Qualification' => array(
                        'conditions' => array(
                            'Qualification.testingcomp_id' => $this->Autorisierung->ConditionsTestinccomps()
                        )
                    ),
                    'Topproject' => array(
                        'conditions' => array(
                            'Topproject.id' => $this->Autorisierung->ConditionsTopprojects()
                        )
                    )
                )
            );

        $result = $this->Weldingmethod->find('first', $options);

        $this->set('breads', $this->Navigation->Breads(null));
        $this->set('weldingmethod', $this->Weldingmethod->find('first', $options));
    }

    /**
     * add method
     *
     * @return void
     */
    public function add()
    {
        $this->layout = 'modal';
        if (isset($this->request->data['Weldingmethod'])) {
            $this->Weldingmethod->create();
            if ($this->Weldingmethod->save($this->request->data)) {
                $this->Session->setFlash(__('Weldingmethod was saved'));

                $this->index();
                $this->render('index');
            } else {
                $this->Session->setFlash(__('Weldingmethod could not be saved. Please, try again.'));
            }
        }
        $testingmethods = $this->Weldingmethod->Testingmethod->find('list', array('fields' => array('id','name')));
        $this->set('testingmethods', $testingmethods);
        $SettingsArray['backlink'] = array('discription' => __('Back', true), 'controller' => 'weldingmethods','action' => 'index', 'terms' => null);
        $this->set('SettingsArray', $SettingsArray);

        $this->set('breads', $this->Navigation->Breads(null));
    }

    /**
     * edit method
     *
     * @throws NotFoundException
     * @param string $id
     * @return void
     */
    public function edit($id = null)
    {
        $id= $this->request->projectvars['VarsArray'] [0];
        if (!$this->Weldingmethod->exists($id)) {
            throw new NotFoundException(__('Invalid weldingmethod'));
        }
        if (isset($this->request->data['Weldingmethod']) || $this->request->is('put')) {
            $this->Autorisierung->ConditionsTesting($this->request->data['Topproject']['Topproject'], 'conditionsTopprojects');
            if ($this->Weldingmethod->save($this->request->data)) {
                $this->Session->setFlash(__('Weldingmethod was saved'));


                $FormName['controller'] = 'weldingmethods';
                $FormName['action'] = 'index';
                $FormName['terms'] = implode('/', $this->request->projectvars['VarsArray']);

                $this->set('FormName', $FormName);

                $this->set('NextModal', 1);
            } else {
                $this->Session->setFlash(__('Weldingmethod could not be saved. Please, try again.'));
            }
        } else {
            $OptionsWeldingmethod =
            array(
                'conditions' => array(
                    'Weldingmethod.id' => $id
                ),

            );

            $this->request->data = $this->Weldingmethod->find('first', $OptionsWeldingmethod);
        }

        $testingmethods = $this->Weldingmethod->Testingmethod->find('list', array('fields' => array('id','name')));
        $this->set('testingmethods', $testingmethods);
        $SettingsArray = array();
        $SettingsArray['backlink'] = array('discription' => __('Back', true), 'controller' => 'weldingmethods','action' => 'index', 'terms' => null);
        $SettingsArray['dellink'] = array('discription' => __('Delete', true), 'controller' => 'weldingmethods','action' => 'delete', 'terms' => $this->request->projectvars['VarsArray'] );
        $this->set('SettingsArray', $SettingsArray);

        $this->set('breads', $this->Navigation->Breads(null));
        $this->set(compact('topprojects'));
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
        $id= $this->request->projectvars['VarsArray'] [0];

        $options =
            array(
                'conditions' => array(
                    'Weldingmethod.id' => $id
                ),

            );

        $_weldingmethod = $this->Weldingmethod->find('first', $options);
        !empty($_weldingmethod) ? $this->request->data['Weldingmethod'] = $_weldingmethod:'';



        if (!$this->Weldingmethod->exists($id)) {
            throw new NotFoundException(__('Invalid weldingmethod'));
        }
        if (isset($this->request->data['Weldingmethod']) && ($this->request->is('post') || $this->request->is('put'))) {
            if ($this->Weldingmethod->deleteall(array('Weldingmethod.id' => $id), false)) {
                $this->Session->setFlash(__('Weldingmethod was deleted'));
                $this->index();
                $this->render('index');
            } else {
                $this->Session->setFlash(__('Weldingmethod was not deleted'));
            }
        }
    }
}
