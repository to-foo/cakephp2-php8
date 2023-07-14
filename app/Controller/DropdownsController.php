<?php
App::uses('AppController', 'Controller');
App::uses('Sanitize', 'Utility');

/**
 * Dropdowns Controller
 *
 * @property Dropdown $Dropdown
 */
class DropdownsController extends AppController
{

    public $components = array('Auth', 'Acl', 'Autorisierung', 'Cookie', 'Navigation', 'Lang', 'Sicherheit', 'Xml', 'Data', 'RequestHandler', 'Drops');
    public $helpers = array('Lang', 'Navigation', 'JqueryScripte', 'ViewData');
    public $layout = 'modal';
    protected $_writeprotection = false;

    public function beforeFilter()
    {
        App::import('Vendor', 'Authorize');
        // Es wird das in der Auswahl gewählte Project in einer Session gespeichert

        $this->Navigation->ReportVars();

        $this->loadModel('User');
        $this->loadModel('Topproject');
        $this->loadModel('Testingmethod');

        $lastmodalURL = explode('/', $this->Session->read('lastmodalURL'));

        if ($this->request->reportID > 0) {
            $this->Autorisierung->ConditionsTopprojectsTest($this->request->params['pass'][0]);
            $this->Dropdown->Report->Reportlock->recursive = -1;
            $this->writeprotection                         = 0 < $this->Dropdown->Report->Reportlock->find('count', array('conditions' => array('Reportlock.topproject_id' => $this->request->projectID, 'Reportlock.report_id' => $this->request->reportID)));
        }

        $this->set('writeprotection', $this->writeprotection);

        $noAjaxIs = 0;
        $noAjax   = array('quicksearch');

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

        $this->set('roll', $this->Auth->user('roll_id'));
        $this->set('lang', $this->Lang->Choice());
        $this->set('select_lang', Configure::read('Config.language'));
        $this->set('selected', $this->Lang->Selected());
        $this->set('login_info', $this->Navigation->loggedUser());
        $this->set('lang_choise', $this->Lang->Choice());
        $this->set('previous_url', $this->base . '/' . $this->Session->read('lastURL'));

        if (isset($this->Auth)) {
            $this->set('authUser', $this->Auth);
        }
    }

    public function afterRender()
    {
        $last = $this->Session->read();
        // Bei anderen Actions im Dropdown controller nicht mitschreiben
        if ($last['lastmodalArrayURL']['controller'] != $this->request->controller) {
            $this->Navigation->lastURL();
        }

    }

    public function get()
    {

        $this->__getjson();
        $this->__getajax();

    }

    protected function __getjson()
    {

        $this->layout = 'blank';

        $data = $this->Drops->GetReportDropdownsMastersJson();

        $this->set('response', json_encode($data));

    }

    protected function __getajax()
    {

    }

    public function quicksearch()
    {

        App::uses('Sanitize', 'Utility');

        $this->layout = 'json';

        $term = Sanitize::stripAll($this->request->data['term']);

        $UserId        = $this->Auth->user('id');
        $TestingcompId = $this->Auth->user('testingcomp_id');

        $this->loadModel('DropdownsMaster');
        $this->loadModel('DropdownsMastersTestingcomp');

        $Check = $this->DropdownsMastersTestingcomp->find('all', array('conditions' => array('DropdownsMastersTestingcomp.testingcomp_id' => $TestingcompId)));
        $DropdownMastersId = Hash::extract($Check, '{n}.DropdownsMastersTestingcomp.dropdowns_masters_id');

        $options = array(
            'limit'      => 10,
            'order'      => array('name'),
            'conditions' => array(
                'DropdownsMaster.name LIKE' => '%' . $term . '%',
                'DropdownsMaster.id'        => $DropdownMastersId,
                'DropdownsMaster.testingcomp_id' => $this->Auth->user('testingcomp_id'),
                'DropdownsMaster.deleted'   => 0,
            ),
        );

        $this->DropdownsMaster->recursive = -1;
        $DropdownsMaster = $this->DropdownsMaster->find('all', $options);

        $response = array();

        foreach ($DropdownsMaster as $key => $value) {

            array_push($response, array('key' => $value['DropdownsMaster']['id'], 'value' => $value['DropdownsMaster']['name'] . ' (' . $value['DropdownsMaster']['description'] . ')'));

        }

        $this->set('response', json_encode($response));
    }

    public function masterdelete()
    {
        $this->layout = "modal";
        $this->loadModel('Testingcomp');
        $this->loadModel('DropdownsMaster');
        $this->DropdownsMaster->recursive = -1;
        $this->Testingcomp->recursive     = -1;
        $SettingsArray                    = array();
        $notAllowed                       = 0;
        $FormName                         = array();
        $FormName['controller']           = 'dropdowns';
        $FormName['action']               = 'master';
        $FormName['terms']                = '';

        $id = $this->request->projectvars['VarsArray'][15];

        $dropdownMaster      = $this->DropdownsMaster->find('first', array('conditions' => array('DropdownsMaster.id' => $id)));
        $testing_comp        = $this->Testingcomp->find('first', array('conditions' => array('Testingcomp.id' => $dropdownMaster['DropdownsMaster']['testingcomp_id'])));
        $this->request->data = $dropdownMaster;

        $this->set('SettingsArray', $SettingsArray);

        if (isset($dropdownMaster['DropdownsMaster']['testingcomp_id']) && $dropdownMaster['DropdownsMaster']['testingcomp_id'] > 0) {
            if ($dropdownMaster['DropdownsMaster']['testingcomp_id'] != $this->Auth->user('testingcomp_id')) {
                $this->Flash->error(__('Only users of company %s allowed to delete this dropdown field!', $testing_comp['Testingcomp']['firmenname']), array('key' => 'error'));
                $notAllowed = 1;

                $this->set('FormName', $FormName);
                $this->set('notAllowed', $notAllowed);
                return;
            }
        }

        if ($this->request->is('put') && !empty($this->request->data['DropdownsMaster']['id'])) {
            $dropdownMaster['DropdownsMaster']['deleted'] = 1;
            if ($this->DropdownsMaster->save($dropdownMaster['DropdownsMaster'])) {
                $this->set('FormName', $FormName);
                $this->Flash->success(__('Value was deleted'), array('key' => 'success'));

            } else {
                $this->Flash->error(__('The value could not be saved. Please try again!'), array('key' => 'error'));
            }

        } else {
            $this->Flash->warning(__('Will you delete this value?'), array('key' => 'warning'));
        }
        //var_dump($dropdownMaster);
        $this->set('notAllowed', $notAllowed);
    }

    /**
     * index method
     *
     * @return void
     */

    public function master()
    {

        $this->loadModel('DropdownsMaster');
        $this->loadModel('DropdownsMastersTestingcomp');
        $this->loadModel('Testingmethods');
        $this->loadModel('Testingcomp');
        $this->loadModel('Report');
        $this->loadModel('Topproject');

        $UserId        = $this->Auth->user('id');
        $TestingcompId = $this->Auth->user('testingcomp_id');

        $this->request->data['modul'] = array('generally_area' => __('Generally', true), 'specific_area' => __('Specific', true), 'evaluation_area' => __('Evaluation', true), 'device' => __('Devices', true), 'examiner' => __('Examiner'), 'document' => __('Document'));

        if (isset($this->request->data['landig_page']) && $this->request->data['landig_page'] == 1) {
            $landig_page = 1;
        }

        if (isset($this->request->data['landig_page_large']) && $this->request->data['landig_page_large'] == 1) {
            $landig_page_large = 1;
        }

        $testingmethods = $this->Testingmethods->find('list', array('fields' => array('id', 'verfahren'), 'order' => array('verfahren')));
        $testingcomps   = $this->Testingcomp->find('list', array('fields' => array('id', 'name'), 'order' => array('name')));
        $reports        = $this->Report->find('list', array('fields' => array('id', 'name'), 'order' => array('name')));
        $topprojects    = $this->Topproject->find('list', array('fields' => array('id', 'projektname'), 'order' => array('projektname')));

        $Check             = $this->DropdownsMastersTestingcomp->find('all', array('conditions' => array('DropdownsMastersTestingcomp.testingcomp_id' => $TestingcompId)));
        $DropdownMastersId = Hash::extract($Check, '{n}.DropdownsMastersTestingcomp.dropdowns_masters_id');

        $this->DropdownsMaster->recursive = -1;

        $Dropdowns = array();

        foreach ($this->request->data['modul'] as $key => $value) {
            // Zugriff muss noch geregelt werden
            $conditions = array(
                'conditions' => array(
                    'DropdownsMaster.id'      => $DropdownMastersId,
                    'DropdownsMaster.modul'   => $key,
                    'DropdownsMaster.testingcomp_id' => $this->Auth->user('testingcomp_id'),
                    'DropdownsMaster.deleted' => 0,
                ),
                'order'      => array('name asc'),
            );

            $DropdownsMaster = $this->DropdownsMaster->find('all', $conditions);

            if (count($DropdownsMaster) > 0) {
                $Dropdowns[$key] = array('desc' => $value, 'data' => $DropdownsMaster);
            }
        }

        $Dropdowns = $this->Drops->CollectTestingmethods($Dropdowns);
        $Dropdowns = $this->Drops->AddMasterDropdownTestingcomp($Dropdowns);

        $breads = $this->Navigation->Breads(null);

        $breads[] = array(
            'discription' => __('Dropdown manager', true),
            'controller'  => 'dropdowns',
            'action'      => 'master',
            'pass'        => null,
        );

        $this->set('DropdownsMaster', $Dropdowns);
        $this->set('breads', $breads);
        $this->set('SettingsArray', array());

        if (isset($landig_page_large) && $landig_page_large == 1) {

            $this->render('landingpage_large', 'blank');
            return;

        }
    }

    public function masteradd()
    {

        $this->layout  = 'modal';
        $SettingsArray = array();

        $this->loadModel('DropdownsMaster');
        $this->loadModel('Testingmethods');
        $this->loadModel('Testingcomp');
        $this->loadModel('Report');
        $this->loadModel('Topproject');

        $Check = $this->Drops->CheckMasterAddFields();

        if (isset($this->request->data['DropdownsMaster']) && count($Check) == 0) {

            $this->request->data['DropdownsMaster']['user_id']        = $this->Auth->user('id');
            $this->request->data['DropdownsMaster']['testingcomp_id'] = $this->Auth->user('testingcomp_id');

            $this->DropdownsMaster->create();

            if ($this->DropdownsMaster->save($this->request->data)) {

                $id = $this->DropdownsMaster->getInsertID();

                //                $authorisation = $this->DropdownsMaster->UpdateDropdownsMasterAuthorization($this->request->data['DropdownsMaster'],$id);
//                $this->Drops->SaveAuthorization($authorisation,$id);

                $this->request->data['DropdownsMaster']['id'] = $id;

                $this->request->projectvars['VarsArray'][15] = $id;

                $this->Autorisierung->Logger($this->request->data['DropdownsMaster']['id'], $this->request->data);

                $FormName               = array();
                $FormName['controller'] = 'dropdowns';
                $FormName['action']     = 'masteredit';
                $FormName['terms']      = implode('/', $this->request->projectvars['VarsArray']);

                $this->set('FormName', $FormName);

                $this->Flash->success(__('The entry has been saved'), array('key' => 'success'));

            } else {
                $this->Flash->error(__('The entry could not be saved. Please, try again.'), array('key' => 'error'));
            }
        }

        $testingmethods = $this->Testingmethods->find('list', array('fields' => array('id', 'verfahren'), 'order' => array('verfahren')));
        $testingcomps   = $this->Testingcomp->find('list', array('fields' => array('id', 'name'), 'order' => array('name')));
        $reports        = $this->Report->find('list', array('fields' => array('id', 'name'), 'order' => array('name')));
        $topprojects    = $this->Topproject->find('list', array('fields' => array('id', 'projektname'), 'order' => array('projektname')));

        $this->request->data['Dropdowns']['status']       = 0;
        $this->request->data['Testingcomp']['selected']   = array($this->Auth->user('testingcomp_id') => $this->Auth->user('testingcomp_id'));
        $this->request->data['Topproject']['selected']    = array();
        $this->request->data['Testingmethod']['selected'] = array();
        $this->request->data['Report']['selected']        = array();

        $this->request->data['modul'] = array('generally_area' => __('Generally', true), 'specific_area' => __('Specific', true), 'evaluation_area' => __('Evaluation', true), 'device' => __('Devices', true), 'examiner' => __('Examiner'), 'document' => __('Document'));

        $this->set(compact('testingcomps', 'reports', 'topprojects', 'testingmethods'));
        $this->set('SettingsArray', $SettingsArray);

    }

    public function masteredit()
    {

        $locale        = $this->Lang->Discription();
        $id            = $this->request->projectvars['VarsArray'][15];
        $SettingsArray = array();

        $this->loadModel('DropdownsMaster');
        $this->loadModel('DropdownsMastersDependenciesField');
        $this->loadModel('Testingmethods');
        $this->loadModel('Testingcomp');
        $this->loadModel('Report');
        $this->loadModel('Topproject');

        if (isset($this->request->data)) {

            $this->request->data['DropdownsMaster']['user_id'] = $this->Auth->user('id');

            if ($this->DropdownsMaster->save($this->request->data)) {

                $this->Drops->RegistryProjekt();

                $this->request->data['DropdownsMaster']['id'] = $id;

                $this->request->projectvars['VarsArray'][15] = $id;

                $this->Autorisierung->Logger($this->request->data['DropdownsMaster']['id'], $this->request->data);

//                $this->Flash->success(__('The entry has been saved'), array('key' => 'success'));
            } else {
                $this->Flash->error(__('The entry could not be saved. Please, try again.'), array('key' => 'error'));
            }
        }

        if (isset($this->request->data['DropdownsMastersDependenciesField'])) {

            if (
                isset($this->request->data['DropdownsMastersDependenciesField']['Dependency']) &&
                is_array($this->request->data['DropdownsMastersDependenciesField']['Dependency'])
            ) {

                $this->DropdownsMastersDependenciesField->deleteAll(array('DropdownsMastersDependenciesField.dropdowns_masters_id' => $id), false);
                $DependencyFields = $this->request->data['DropdownsMastersDependenciesField']['Dependency'];

                $Insert['DropdownsMastersDependenciesField']['dropdowns_masters_id'] = $id;
                $Insert['DropdownsMastersDependenciesField']['testingcomp_id']       = $this->Auth->user('testingcomp_id');
                $Insert['DropdownsMastersDependenciesField']['user_id']              = $this->Auth->user('id');

                $Option['conditions']['dropdowns_masters_id'] = $id;

                foreach ($DependencyFields as $key => $value) {

                    if (empty($value)) {
                        continue;
                    }

                    $Insert['DropdownsMastersDependenciesField']['field'] = $value;

                    $this->DropdownsMastersDependenciesField->create();

                    if ($this->DropdownsMastersDependenciesField->save($Insert)) {
                    } else {
                        $this->Flash->error('The entry could not be saved. Please, try again.', array('key' => 'error'));
                        break;
                    }
                    $this->Autorisierung->Logger($id, $Insert);
                }

//                $this->Flash->success(__('The entry has been saved'), array('key' => 'success'));

            }
        }

        $options =
            array(
                'conditions' => array(
                    'DropdownsMaster.' . $this->DropdownsMaster->primaryKey => $id,
                    'DropdownsMaster.testingcomp_id' => $this->Auth->user('testingcomp_id'),
                ),
            );

        $DropdownsMaster = $this->DropdownsMaster->find('first', $options);
        $DropdownsMaster = $this->Data->BelongsToManySelected($DropdownsMaster, 'DropdownsMaster', 'Testingcomp', array('DropdownsMastersTestingcomp', 'testingcomp_id', 'dropdowns_masters_id'));
        $DropdownsMaster = $this->Data->BelongsToManySelected($DropdownsMaster, 'DropdownsMaster', 'Report', array('DropdownsMastersReport', 'report_id', 'dropdowns_masters_id'));
        $DropdownsMaster = $this->Data->BelongsToManySelected($DropdownsMaster, 'DropdownsMaster', 'Topproject', array('DropdownsMastersTopproject', 'topproject_id', 'dropdowns_masters_id'));
        $DropdownsMaster = $this->Data->BelongsToManySelected($DropdownsMaster, 'DropdownsMaster', 'Testingmethod', array('DropdownsMastersTestingmethod', 'testingmethod_id', 'dropdowns_masters_id'));
        $DropdownsMaster = $this->Drops->CollectFieldsForMaster($DropdownsMaster);
        $DropdownsMaster = $this->Drops->SortNatDropdowns($DropdownsMaster);

        $testingmethods = $this->Testingmethods->find('list', array('fields' => array('id', 'verfahren'), 'order' => array('verfahren')));
        $testingcomps   = $this->Testingcomp->find('list', array('fields' => array('id', 'name'), 'order' => array('name')));
        $reports        = $this->Report->find('list', array('fields' => array('id', 'name'), 'order' => array('name')));
        $topprojects    = $this->Topproject->find('list', array('fields' => array('id', 'projektname'), 'order' => array('projektname')));

        if (isset($this->request->data['data_area']) && !empty($this->request->data['data_area'])) {
            $DropdownsMaster['data_area'] = $this->request->data['data_area'];
        }

        $DropdownsMaster['DependencyFields'] = $DropdownsMaster['DropdownsFields'];
        unset($DropdownsMaster['DependencyFields'][$DropdownsMaster['DropdownsMaster']['field']]);
        $dependencies    = $DropdownsMaster['DependencyFields'];
        $DropdownsMaster = $this->Drops->CollectDependenciesField($DropdownsMaster);
        $this->request->data                              = $DropdownsMaster;
        $this->request->data['Testingcomp']['selected']   = array($this->Auth->user('testingcomp_id') => $this->Auth->user('testingcomp_id'));
        $this->request->data['Topproject']['selected']    = array();
        $this->request->data['Testingmethod']['selected'] = array();
        $this->request->data['Report']['selected']        = array();

        $breads = $this->Navigation->Breads(null);

        $breads[] = array(
            'discription' => __('Dropdown manager', true),
            'controller'  => 'dropdowns',
            'action'      => 'master',
            'pass'        => null,
        );

        $breads[] = array(
            'discription' => $DropdownsMaster['DropdownsMaster']['name'],
            'controller'  => 'dropdowns',
            'action'      => 'masteredit',
            'pass'        => $this->request->projectvars['VarsArray'],
        );

        $this->set('breads', $breads);
        $this->set('SettingsArray', array());
        $this->set(compact('testingcomps', 'reports', 'topprojects', 'testingmethods', 'dependencies'));
        $this->set('DropdownsMaster', $DropdownsMaster);

        if(count($DropdownsMaster['Testingmethod']['selected']) == 0){

            $this->Flash->warning(__('No testingmethod selected. Please select one.'), array('key' => 'warning'));
            $this->render('master_edit_add_testingmethod');
        }

    }

    public function editdependency()
    {

        $this->layout = 'json';

        $StopRequets = false;

        if (!isset($this->request->data['json_value_old'])) {
            $StopRequets = true;
        }

        if (!isset($this->request->data['json_value'])) {
            $StopRequets = true;
        }

        if (empty($this->request->data['json_value_old'])) {
            $StopRequets = true;
        }

        if (empty($this->request->data['json_value'])) {
            $StopRequets = true;
        }

        if ($StopRequets == true) {
            $this->set('request', json_encode(array()));
            return;
        }

        $DropdownsMaster = $this->Drops->CollectDropdownMaster('json');
        $DropdownsMaster = $this->Drops->FindCurrentDependencyValue($DropdownsMaster);
        $DropdownsMaster = $this->Drops->EditCurrentDependencyValue($DropdownsMaster);

        $this->set('request', json_encode($DropdownsMaster));
    }

    public function deletedependency()
    {
        $this->layout    = 'json';
        $DropdownsMaster = $this->Drops->CollectDropdownMaster('json');
        $DropdownsMaster = $this->Drops->DeleteCurrentDependencyValue($DropdownsMaster);
        $this->set('request', json_encode($DropdownsMaster));
    }

    public function masterdependency()
    {

        if (isset($this->request->data['json_true']) && $this->request->data['json_true'] == 1) {
            $this->layout = 'json';
            $this->_masterdependency_json();
            $this->render('masterdependency_json');
            return;
        } else {
            $this->layout = 'modal';
            $this->_masterdependency();
        }
    }

    protected function _masterdependency_json()
    {

        if (!isset($this->request->data['DependencyFields']) && empty($this->request->data['DependencyFields'])) {
            return;
        }

        $DependencyFields = $this->request->data['DependencyFields'];

        $DropdownsMaster = $this->Drops->CollectDropdownMaster('json');
        $DropdownsMaster = $this->Drops->SeparateThisDependenciesField($DropdownsMaster, $DependencyFields);

        $DropdownsMaster['StatusText'] =
            __('Add dependency data for master field') . ' "' .
            $DropdownsMaster['DropdownsMaster']['field_name'] . '" ' .
            __('with value', true) . ' "' .
            $DropdownsMaster['DropdownsMastersData']['value'] . '" > ' .
            $DropdownsMaster['DependencyFields'][$DependencyFields]
        ;

        $this->set('request', json_encode($DropdownsMaster));

    }

    protected function _masterdependency()
    {

        $SettingsArray = array();

        $DropdownsMaster = $this->Drops->CollectDropdownMaster('html');

        if (isset($this->request->data['DropdownsMastersDependency'])) {

            $this->request->data['DropdownsMastersDependency']['dropdowns_masters_id']      = $DropdownsMaster['DropdownsMaster']['id'];
            $this->request->data['DropdownsMastersDependency']['dropdowns_masters_data_id'] = $DropdownsMaster['DropdownsMastersData']['id'];
            $this->request->data['DropdownsMastersDependency']['testingcomp_id']            = $this->Auth->user('testingcomp_id');
            $this->request->data['DropdownsMastersDependency']['global']                    = 0;

            //******************************
            //
            // Unbedingt noch auf doppelte Einträge testen
            //
            //******************************
            $this->DropdownsMastersDependency->create();

            if ($this->DropdownsMastersDependency->save($this->request->data)) {
                $this->Session->setFlash(__('The entry has been saved'));
                $this->set('DependencyFieldAfterSaving', $this->request->data['DropdownsMastersDependency']['field']);
            } else {
                $this->Session->setFlash(__('The entry could not be saved. Please, try again.'));
            }
        }

        $DropdownsMaster = $this->Drops->CollectDropdownMaster('html');
        if (isset($this->request->data['data_area']) && !empty($this->request->data['data_area'])) {
            $DropdownsMaster['data_area'] = $this->request->data['data_area'];
        }

        $this->request->data                              = $DropdownsMaster;
        $this->request->data['Testingcomp']['selected']   = array($this->Auth->user('testingcomp_id') => $this->Auth->user('testingcomp_id'));
        $this->request->data['Topproject']['selected']    = array();
        $this->request->data['Testingmethod']['selected'] = array();
        $this->request->data['Report']['selected']        = array();

        $SettingsArray['backlink'] = array('discription' => __('Back', true), 'controller' => 'dropdowns', 'action' => 'mastereditdata', 'terms' => $this->request->projectvars['VarsArray']);

        $this->set('SettingsArray', $SettingsArray);
        $this->set('DropdownsMaster', $DropdownsMaster);
    }

    public function masteradddata()
    {

        $this->layout  = 'modal';
        $id            = $this->request->projectvars['VarsArray'][15];
        $SettingsArray = array();

        $this->loadModel('DropdownsMaster');

        $options =
            array(
                'conditions' => array(
                    'DropdownsMaster.' . $this->DropdownsMaster->primaryKey => $id,
                ),
            );

        $DropdownsMaster = $this->DropdownsMaster->find('first', $options);

        if (isset($this->request->data['DropdownsMastersData']) || $this->request->is('put')) {

            $this->request->data['DropdownsMastersData']['dropdowns_masters_id'] = $DropdownsMaster['DropdownsMaster']['id'];

            $this->DropdownsMaster->DropdownsMastersData->create();
            $this->DropdownsMaster->DropdownsMastersData->save($this->request->data);

            $DropdownsMaster['DropdownsMastersData']['action_after_saving'] = $this->request->data['DropdownsMastersData']['action_after_saving'];

            $this->Flash->success(__('Value was saved'), array('key' => 'success'));

            $FormName               = array();
            $FormName['controller'] = 'dropdowns';
            $FormName['action']     = 'masteredit';
            $FormName['terms']      = implode('/', $this->request->projectvars['VarsArray']);
            $this->set('FormName', $FormName);

            $DropdownsMaster['data_area'] = 'data_area';

            if ($this->request->data['DropdownsMastersData']['action_after_saving'] == 0) {

                $FormName2               = array();
                $FormName2['controller'] = 'dropdowns';
                $FormName2['action']     = 'masteradddata';
                $FormName2['terms']      = implode('/', $this->request->projectvars['VarsArray']);

                $this->set('FormName2', $FormName2);
            }

        } else {

            $DropdownsMaster['DropdownsMastersData']['status']              = 0;
            $DropdownsMaster['DropdownsMastersData']['action_after_saving'] = 0;

        }

        $this->request->data = $DropdownsMaster;

        $this->set('SettingsArray', $SettingsArray);
    }

    public function mastereditdata()
    {

        $this->layout  = 'modal';
        $id            = $this->request->projectvars['VarsArray'][15];
        $id_data       = $this->request->projectvars['VarsArray'][16];
        $SettingsArray = array();

        $this->loadModel('DropdownsMaster');

        $options =
            array(
                'conditions' => array(
                    'DropdownsMaster.id' => $id,
                ),
            );

        $this->DropdownsMaster->recursive = -1;
        $DropdownsMaster                  = $this->DropdownsMaster->find('first', $options);

        $options =
            array(
                'conditions' => array(
                    'DropdownsMastersData.id' => $id_data,
                ),
            );

        $DropdownsMasterData = $this->DropdownsMaster->DropdownsMastersData->find('first', $options);
        $DropdownsMaster     = array_merge($DropdownsMaster, $DropdownsMasterData);

        if (isset($this->request->data['DropdownsMastersData']) || $this->request->is('put')) {

            $this->DropdownsMaster->DropdownsMastersData->save($this->request->data);

            $DropdownsMaster['DropdownsMastersData']['action_after_saving'] = $this->request->data['DropdownsMastersData']['action_after_saving'];

            $this->request->data              = $DropdownsMaster;
            $this->request->data['data_area'] = 'data_area';

            $this->Flash->success(__('Value was saved'), array('key' => 'success'));

            $FormName               = array();
            $FormName['controller'] = 'dropdowns';
            $FormName['action']     = 'masteredit';
            $FormName['terms']      = implode('/', $this->request->projectvars['VarsArray']);
            $this->set('FormName', $FormName);

            if ($this->request->data['DropdownsMastersData']['action_after_saving'] == 0) {

                $FormName2               = array();
                $FormName2['controller'] = 'dropdowns';
                $FormName2['action']     = 'masteradddata';
                $FormName2['terms']      = implode('/', $this->request->projectvars['VarsArray']);

                $this->set('FormName2', $FormName2);
            }

        } else {

            $DropdownsMaster['DropdownsMastersData']['action_after_saving'] = 1;
            $this->request->data                                            = $DropdownsMaster;

        }

        $SettingsArray['dellink'] = array('discription' => __('Delete this Entry', true), 'controller' => 'dropdowns', 'action' => 'masterdeletedata', 'terms' => $this->request->projectvars['VarsArray']);

        $SettingsArray = $this->Autorisierung->AclCheckLinks($SettingsArray);

        $this->set('SettingsArray', $SettingsArray);
    }

    public function masterdeletedata()
    {

        $this->layout  = 'modal';
        $id            = $this->request->projectvars['VarsArray'][15];
        $id_data       = $this->request->projectvars['VarsArray'][16];
        $SettingsArray = array();

        $this->loadModel('DropdownsMaster');
        $this->loadModel('DropdownsMastersDependency');

        $options =
            array(
                'conditions' => array(
                    'DropdownsMaster.id' => $id,
                ),
            );

        $this->DropdownsMaster->recursive = -1;
        $DropdownsMaster                  = $this->DropdownsMaster->find('first', $options);

        $options =
            array(
                'conditions' => array(
                    'DropdownsMastersData.id' => $id_data,
                ),
            );

        $DropdownsMasterData = $this->DropdownsMaster->DropdownsMastersData->find('first', $options);
        $DropdownsMaster     = array_merge($DropdownsMaster, $DropdownsMasterData);

        if (isset($this->request->data['DropdownsMastersData']) || $this->request->is('put')) {

            if ($this->DropdownsMaster->DropdownsMastersData->delete($this->request->data('DropdownsMastersData.id'))) {

                $this->DropdownsMastersDependency->deleteAll(array('DropdownsMastersDependency.dropdowns_masters_data_id' => $this->request->data('DropdownsMastersData.id')), false);

                $this->Autorisierung->Logger($this->request->data['DropdownsMastersData']['id'], $this->request->data);

                $FormName               = array();
                $FormName['controller'] = 'dropdowns';
                $FormName['action']     = 'masteredit';
                $FormName['terms']      = implode('/', $this->request->projectvars['VarsArray']);

                $this->set('FormName', $FormName);

                $this->Flash->success(__('The entry has been deleted'), array('key' => 'success'));

            } else {

                $this->Flash->error(__('The entry could not be deleted'), array('key' => 'error'));

            }

        } else {

            $this->Flash->warning(__('Should this value be deleted'), array('key' => 'warning'));
            $this->request->data = $DropdownsMaster;

        }

        $SettingsArray['backlink'] = array('discription' => __('Back', true), 'controller' => 'dropdowns', 'action' => 'mastereditdata', 'terms' => $this->request->projectvars['VarsArray']);

        $SettingsArray = $this->Autorisierung->AclCheckLinks($SettingsArray);

        $this->set('SettingsArray', $SettingsArray);

    }

    public function changemodul()
    {

        $this->layout  = 'modal';
        $SettingsArray = array();
        $id            = $this->request->projectvars['VarsArray'][15];

        $this->loadModel('DropdownsMaster');
        $this->loadModel('DropdownsMastersDependenciesField');
        $this->loadModel('Testingmethods');
        $this->loadModel('Testingcomp');
        $this->loadModel('Report');
        $this->loadModel('Topproject');

        if (isset($this->request->data['DropdownsMaster']) || $this->request->is('put')) {

        }

        $testingmethods = $this->Testingmethods->find('list', array('fields' => array('id', 'verfahren'), 'order' => array('verfahren')));
        $testingcomps   = $this->Testingcomp->find('list', array('fields' => array('id', 'name'), 'order' => array('name')));
        $reports        = $this->Report->find('list', array('fields' => array('id', 'name'), 'order' => array('name')));
        $topprojects    = $this->Topproject->find('list', array('fields' => array('id', 'projektname'), 'order' => array('projektname')));

        $options =
            array(
                'conditions' => array(
                    'DropdownsMaster.' . $this->DropdownsMaster->primaryKey => $id,
                ),
            );

        $DropdownsMaster = $this->DropdownsMaster->find('first', $options);

        $DropdownsMaster = $this->Data->BelongsToManySelected($DropdownsMaster, 'DropdownsMaster', 'Testingcomp', array('DropdownsMastersTestingcomp', 'testingcomp_id', 'dropdowns_masters_id'));
        $DropdownsMaster = $this->Data->BelongsToManySelected($DropdownsMaster, 'DropdownsMaster', 'Report', array('DropdownsMastersReport', 'report_id', 'dropdowns_masters_id'));
        $DropdownsMaster = $this->Data->BelongsToManySelected($DropdownsMaster, 'DropdownsMaster', 'Topproject', array('DropdownsMastersTopproject', 'topproject_id', 'dropdowns_masters_id'));
        $DropdownsMaster = $this->Data->BelongsToManySelected($DropdownsMaster, 'DropdownsMaster', 'Testingmethod', array('DropdownsMastersTestingmethod', 'testingmethod_id', 'dropdowns_masters_id'));
        $DropdownsMaster = $this->Drops->CollectFieldsForMaster($DropdownsMaster);

        $testingmethods = $this->Testingmethods->find('list', array('fields' => array('id', 'verfahren'), 'order' => array('verfahren')));
        $testingcomps   = $this->Testingcomp->find('list', array('fields' => array('id', 'name'), 'order' => array('name')));
        $reports        = $this->Report->find('list', array('fields' => array('id', 'name'), 'order' => array('name')));
        $topprojects    = $this->Topproject->find('list', array('fields' => array('id', 'projektname'), 'order' => array('projektname')));

        if (isset($this->request->data['data_area']) && !empty($this->request->data['data_area'])) {
            $DropdownsMaster['data_area'] = $this->request->data['data_area'];
        }

        $DropdownsMaster['DependencyFields'] = $DropdownsMaster['DropdownsFields'];
        unset($DropdownsMaster['DependencyFields'][$DropdownsMaster['DropdownsMaster']['field']]);
        $dependencies    = $DropdownsMaster['DependencyFields'];
        $DropdownsMaster = $this->Drops->CollectDependenciesField($DropdownsMaster);

        $this->request->data = $DropdownsMaster;

        $this->request->data['modul'] = array('generally_area' => __('Generally', true), 'specific_area' => __('Specific', true), 'evaluation_area' => __('Evaluation', true), 'device' => __('Devices', true), 'examiner' => __('Examiner'), 'document' => __('Document'));

        $this->set(compact('testingcomps', 'reports', 'topprojects', 'testingmethods'));
        $this->set('SettingsArray', $SettingsArray);

    }


    //**********************************************************************************
/*

Alle Funktionen ab hier gehören zur alten Dropdownlösung
diese Funktionen werden nicht mit in die neue Cake-Version übernommen

*/
    //********************************************************************************

    public function index()
    {
        $this->layout = 'modal';

        $projectID = $this->request->projectID;

        $this->loadModel('Reportnumber');
        $this->Reportnumber->recursive = 0;
        $reportnumber                  = $this->Reportnumber->find('first', array('conditions' => array('Reportnumber.id' => $this->request->reportnumberID)));

        $this->paginate = array(
            'joins'      => array(
                    /*    array(
                    'alias' => 'DropdownsValue',
                    'table' => 'dropdowns_values',
                    'conditions' => array('Dropdown.id = DropdownsValue.dropdown_id')
                    ),
                    */        array(
                    'alias'      => 'Testingcomp',
                    'table'      => 'testingcomps',
                    'conditions' => array('Dropdown.testingcomp_id = Testingcomp.id'),
                ),
            ),
            'conditions' => array(
                'Testingcomp.id' => $this->Session->read('conditionsTestingcomps'),
                //            'Dropdown.testingmethod_id'=>$reportnumber['Testingmethod']['id']
                //            'report_id' => $reportID,
            ),
            'fields'     => array('Dropdown.id', 'Dropdown.' . Configure::read('Config.language'), 'Dropdown.testingmethod_id', 'Testingcomp.name' /*'COUNT(DropdownsValue.id)'*/),
            'limit'      => 20,
            'order'      => array('Dropdown.' . Configure::read('Config.language') => 'asc', 'Testingcomp.name', 'Dropdown.id' => 'desc'),
            'group'      => array('Dropdown.id'),
        );

        $this->Dropdown->recursive = -1;
        $dropdown                  = $this->paginate('Dropdown');
        $dropdownModels            = array('DropdownsValue');
        $this->loadModel('Report');
        $this->loadModel('DropdownsValue');
        $this->DropdownsValue->recursive = -1;

        foreach ($dropdown as $key => $val) {
            $dropdown[$key]['Values'] = $this->DropdownsValue->find('count', array('conditions' => array('dropdown_id' => $val['Dropdown']['id'])));
        }

        $this->Report->recursive = -1;

        $SettingsArray             = array();
        $SettingsArray['viewlink'] = array('discription' => __('Toggle dropdowns for other testingmethods', true), 'controller' => 'reportnumbers', 'action' => 'edit', 'terms' => $this->request->projectvars['VarsArray']);
        $SettingsArray['backlink'] = array('discription' => __('Back', true), 'controller' => 'reportnumbers', 'action' => 'settings', 'terms' => $this->request->projectvars['VarsArray']);
        //        $SettingsArray['addlink'] = array('discription' => __('Add',true), 'controller' => 'users','action' => 'add', 'terms' => null);
//        $SettingsArray['addsearching'] = array('discription' => __('Searching',true), 'controller' => 'reportnumbers','action' => 'search', 'terms' => null,);
//        $SettingsArray['settingslink'] = array('discription' => __('Settings',true), 'controller' => 'topprojects','action' => 'settings', 'terms' => null,);

        $this->set('SettingsArray', $SettingsArray);

        $this->set('projectID', $projectID);
        $this->set('dropdowns', $dropdown);
        $this->set('reportnumber', $reportnumber);
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

        $id = $this->request->dropdown;

        if (!$this->Dropdown->exists($id)) {
            throw new NotFoundException(__('Invalid dropdown'));
        }

        $options   = array(
            'conditions' => array(
                'Dropdown.' . $this->Dropdown->primaryKey => $id,
                'Dropdown.testingcomp_id'                 => $this->Session->read('conditionsTestingcomps'),
            ),
        );
        $dropdowns = $this->Dropdown->find('first', $options);

        if (isset($this->request->data['Dropdown']) || $this->request->is('put')) {
            $data                          = $this->request->data;
            $data['Dropdown']['report_id'] = $dropdowns['Dropdown']['report_id'];
            if ($this->Dropdown->save($data)) {
                $this->Autorisierung->Logger($data['Dropdown']['id'], $data);
                $this->Session->setFlash(__('The dropdown has been saved'));
                $this->Session->setFlash(__('The value has been be saved.'));
                //                $this->set('afterEDIT', $this->Navigation->afterEDIT('dropdowns/index/'.$dropdowns['Dropdown']['report_id'],3000));
            } else {
                $this->Session->setFlash(__('The dropdown could not be saved. Please, try again.'));
            }
        } else {
            $this->request->data = $dropdowns;
        }

        $this->loadModel('Report');
        $this->Report->recursive = -1;
        $optionsReport           = array('conditions' => array('Report.id' => $dropdowns['Dropdown']['report_id']));
        $reportArray             = $this->Report->find('first', $optionsReport);
        $reportArray['Dropdown'] = $dropdowns['Dropdown'];

        $testingcomps = $this->Dropdown->Testingcomp->find('list');
        $braed        = $this->Navigation->Breads($reportArray);
        $menue        = $this->Navigation->NaviMenue($reportArray);

        $SettingsArray             = array();
        $SettingsArray['backlink'] = array('discription' => __('Back', true), 'controller' => 'dropdowns', 'action' => 'index', 'terms' => $this->request->projectvars['VarsArray']);
        //            $SettingsArray['addlink'] = array('discription' => __('Add',true), 'controller' => 'dropdowns','action' => 'add', 'terms' => null);
//            $SettingsArray['addsearching'] = array('discription' => __('Searching',true), 'controller' => 'reportnumbers','action' => 'search', 'terms' => null,);
//            $SettingsArray['settingslink'] = array('discription' => __('Settings',true), 'controller' => 'topprojects','action' => 'settings', 'terms' => null,);

        $this->set('SettingsArray', $SettingsArray);
        $this->set(compact('testingcomps'));
    }

    public function dropdownindex($id = null)
    {
        if ($this->request->reportnumberID > 0) {
            $this->include_dropdownindex($id);
        } else {
            $this->include_modaldropdownindex($id);
        }
    }

    protected function include_modaldropdownindex($id = null)
    {
        $this->layout = 'modal';
        //         $this->Autorisierung->Protect(); // request daten bzw prüfverfahren suchen
        //     pr($this->request);
        $projectID      = $this->request->projectvars['VarsArray'][0];
        $cascadeID      = $this->request->projectvars['VarsArray'][1];
        $orderID        = $this->request->projectvars['VarsArray'][2];
        $reportID       = $this->request->projectvars['VarsArray'][3];
        $reportnumberID = $this->request->projectvars['VarsArray'][4];
        $evalutionID    = $this->request->projectvars['VarsArray'][5];
        $id             = $this->request->projectvars['VarsArray'][6];
        $count          = $this->request->projectvars['VarsArray'][7];

        $dropdownid  = isset($this->request->data['dropdownid']) ? $this->request->data['dropdownid'] : '';
        $_dropdownid = array();
        $ModelArray  = explode('0', $dropdownid, 2);

        $options = array(
            'conditions' => array(
                'Dropdown.testingcomp_id' => $this->Autorisierung->ConditionsTestinccomps(),
                'ODropdown.id'            => $id,
            ),
            'fields'     => array('Dropdown.id', 'Dropdown.linking', 'Dropdown.model', 'Dropdown.field'),
            'joins'      => array(
                array(
                    'table'      => 'dropdowns',
                    'alias'      => 'ODropdown',
                    'conditions' => array(
                        'Dropdown.model = ODropdown.model',
                        'Dropdown.field = ODropdown.field',
                    ),
                ),
            ),
        );

        $dropdownsCount = count($dropdowns = $this->Dropdown->find('all', $options));

        if ($dropdownsCount > 0) {
            $ModelArray = array($dropdowns[0]['Dropdown']['model'], $dropdowns[0]['Dropdown']['field']);
        } else {

        }

        $model = strtolower($ModelArray[0]);
        // Die XML-Datei des Verfahrens einlesen
        //$arrayData = $this->Xml->DatafromXml('Examiner','file','Examiner');

        $arrayData = $this->Xml->DatafromXml($ModelArray[0], 'file', null);
        // Wenn zu diesem Eintrag nix existiert

        if ($dropdownsCount == 0) {

            $Model = $ModelArray[0];

            if (is_object($arrayData['settings']->$Model) || is_array($arrayData['settings']->$Model)) {

                foreach ($arrayData['settings']->$Model->children() as $_key => $_arrayData) {

                    if (preg_replace('/\_(\d+)/', '$1', trim($_arrayData->key)) != preg_replace('/\_(\d+)/', '$1', Inflector::underscore($ModelArray[1]))) {
                        continue;
                    }

                    if (trim($_arrayData->output->screen) != '') {

                        // Test ob dieser Eintrag schon vorhanden ist
                        $thisDropdown = $this->Dropdown->find('first', array('conditions' => array('dropdownid' => $dropdownid, 'testingcomp_id' => $this->Autorisierung->ConditionsTestinccomps())));

                        if (count($thisDropdown) == 0) {
                            $insert = array(
                                'dropdownid'       => $dropdownid,
                                'model'            => trim($_arrayData->model),
                                'field'            => trim($_arrayData->key),
                                'eng'              => trim($_arrayData->discription->eng),
                                'deu'              => trim($_arrayData->discription->deu),
                                'testingcomp_id'   => $this->Auth->user('testingcomp_id'),
                                'report_id'        => 0,
                                'testingmethod_id' => 0,
                            );

                            $this->Dropdown->create();
                            $this->Dropdown->save($insert);
                            $this->request->projectvars['VarsArray'][6] = $id = $this->Dropdown->getInsertID();
                        } else {
                            $this->request->projectvars['VarsArray'][6] = $id = $thisDropdown['Dropdown']['id'];
                        }
                        break;
                    }
                }
            }
        }

        $options = array(
            'conditions' => array(
                'Dropdown.testingcomp_id' => $this->Autorisierung->ConditionsTestinccomps(),
                'ODropdown.id'            => $id,
            ),
            //            'fields'=>array('Dropdown.id', 'Dropdown.linking'),
            'joins'      => array(
                array(
                    'table'      => 'dropdowns',
                    'alias'      => 'ODropdown',
                    'conditions' => array(
                        'Dropdown.model = ODropdown.model',
                        'Dropdown.field = ODropdown.field',
                    ),
                ),
            ),
        );

        $dropdowns = $this->Dropdown->find('all', $options);

        if (count($dropdowns) > 0) {

            $this->set('has_dependencies', false);
            $DropdownModel = $dropdowns[0]['Dropdown']['model'];
            $DropdownField = $dropdowns[0]['Dropdown']['field'];

            if (!empty($arrayData['settings']->$DropdownModel->$DropdownField->dependencies)) {
                $this->set('has_dependencies', true);
            }

            $dropdown_id = array_map(function ($_dropdown) {
                return $_dropdown['Dropdown']['id']; }, $dropdowns); //['Dropdown']['id'];
        } else {
            $dropdown_id = 0;
        }

        $Model = 'DropdownsValue';
        $this->loadModel($Model);
        $projectID = 0;

        $this->$Model->recursive = 0;

        $modelDataOptions = array(
            'conditions' => array(
                $Model . '.report_id' => 0,
                'OR'                  => array(
                    $Model . '.testingcomp_id' => $this->Autorisierung->ConditionsTestinccomps(),
                    $Model . '.global' => 1,
                ),
            ),
            'order'      => array($Model . '.discription' => 'desc'),
        );

        $linking = array();
        //        $linking = array_filter(array_map(function($_dropdown) { return $_dropdown['Dropdown']['linking']; }, $dropdowns));

        if (count($linking) == 0) {
            $modelDataOptions['conditions'][$Model . '.dropdown_id'] = $dropdown_id;
            $this->paginate                                          = $modelDataOptions;
            $modelData                                               = $this->paginate($Model);
        } else {

            $modelData = null;

            $Model = 'DropdownsValue';
            $this->loadModel($Model);
            $projectID = 0;

            $this->$Model->recursive = 0;

            $modelDataOptions = array(
                'conditions' => array(
                    'OR'                    => array(
                        $Model . '.testingcomp_id' => $this->Autorisierung->ConditionsTestinccomps(),
                        $Model . '.global' => 1,
                    ),
                    $Model . '.dropdown_id' => $linking,
                    //$dropdowns['Dropdown']['linking']
                ),
                'order'      => array($Model . '.id' => 'desc'),
            );

            $modelData = $this->$Model->find('all', $modelDataOptions);

            // Der Verknüpfunslink kommt nur, wenn noch keine Einträge vorhandne sind

            //            $this->request->projectvars['VarsArray'][7] = $id;
//            $this->request->projectvars['VarsArray'][8] = $count;

            $SettingsArray = array();

            // Der Verknüpfunslink kommt nur, wenn noch keine Einträge vorhandne sind
//            $SettingsArray['linklink'] = array('discription' => __('Linking',true), 'controller' => 'dropdowns','action' => 'linking', 'terms' => array($id,$count,$reportnumberID,$evalutionID));
//            $SettingsArray['copylink'] = array('discription' => __('Copy other dropdown values for this dropdown',true), 'controller' => 'dropdowns','action' => 'linking', 'terms' => $this->request->projectvars['VarsArray']);

            $this->set('SettingsArray', $SettingsArray);
            $this->set('Model', $Model);
            $this->set('modelDatas', $modelData);

            if (isset($dropdowns['Dropdown'])) {
                $this->set('title', $dropdowns['Dropdown'][Configure::read('Config.language')]);
                $this->set('linkingID', $dropdowns['Dropdown']['linking']);
            }
            $this->set('id', $id);
            $this->set('count', $count);
            $this->set('reportnumberID', 0);
            $this->set('evalutionID', 0);
            $this->render('islinking');

            return;
        }

        $testingmethods    = array();
        $testingmethods[0] = __($ModelArray[0], true);

        //        $this->request->projectvars['VarsArray'][8] = $id;
//        $this->request->projectvars['VarsArray'][9] = $count;

        $_dropdown = reset($dropdowns);

        $path      = $_dropdown['Dropdown']['model'] . '/' . $_dropdown['Dropdown']['field'] . '[output/screen!=\'\']/select/protected';
        $protected = false;
        if (is_countable($arrayData['settings']->xpath($path)) && count($path = $arrayData['settings']->xpath($path)) != 0) {
            $protected = (array_search(strtolower(reset($path)), array('1', 'yes')) !== false);
        }

        if (!$protected) {
            $SettingsArray['dellink']  = array('discription' => __('Delete all dropdown values', true), 'controller' => 'dropdowns', 'action' => 'dropdowndelete', 'terms' => $this->request->projectvars['VarsArray']);
            $SettingsArray['copylink'] = array('discription' => __('Copy other dropdown values for this dropdown', true), 'controller' => 'dropdowns', 'action' => 'linking', 'terms' => $this->request->projectvars['VarsArray']);

            if (count($linking) == 0) {
                $SettingsArray['addlink'] = array('discription' => __('Add', true), 'controller' => 'dropdowns', 'action' => 'dropdownadd', 'terms' => $this->request->projectvars['VarsArray']);
            }
        }

        $lastUrl = $this->Session->read('lastmodalArrayURL');

        $SettingsArray['backlink'] = array('discription' => __('back to list', true), 'controller' => $lastUrl['controller'], 'action' => $lastUrl['action'], 'terms' => $lastUrl['pass']);

        $this->set('protected', $protected);
        $this->set('SettingsArray', $SettingsArray);
        $this->set('Model', $Model);
        $this->set('modelDatas', $modelData);
        if (isset($dropdowns[0]['Dropdown'])) {
            $this->set('title', $dropdowns[0]['Dropdown'][Configure::read('Config.language')]);
            $this->set('linkingID', $dropdowns[0]['Dropdown']['linking']);
        }
        $this->set('id', $id);
        $this->set('count', $count);
        $this->set('reportnumberID', 0);
        $this->set('evalutionID', 0);

        $this->render('modaldropdownindex');
    }

    protected function include_dropdownindex($id = null)
    {

        $this->layout   = 'modal';
        $reportnumberID = 0;
        $evalutionID    = 0;
        $id             = 0;

        $projectID      = $this->request->projectvars['VarsArray'][0];
        $cascadeID      = $this->request->projectvars['VarsArray'][1];
        $orderID        = $this->request->projectvars['VarsArray'][2];
        $reportID       = $this->request->projectvars['VarsArray'][3];
        $reportnumberID = $this->request->projectvars['VarsArray'][4];
        $evalutionID    = $this->request->projectvars['VarsArray'][5];
        $id             = $this->request->projectvars['VarsArray'][6];
        $count          = $this->request->projectvars['VarsArray'][7];

        $this->request->projectvars['VarsArray'][9] = 0;

        // Die Daten des betreffenden Prüfberichtes holen
        $this->loadModel('Reportnumber');
        $this->Reportnumber->recursive = 0;

        $options = array(
            'conditions' => array(
                'Reportnumber.id' => $reportnumberID,
            ),
        );

        $reportnumber = $this->Reportnumber->find('first', $options);
        // Die XML-Datei des Verfahrens einlesen
        $arrayData = $this->Xml->DatafromXml($reportnumber['Testingmethod']['value'], 'file', ucfirst($reportnumber['Testingmethod']['value']));

        $id = $this->Drops->CreateNewDropdownEntry($arrayData, $reportnumber);

        $this->loadModel('DropdownsValue');

        if ($id != 0) {

            $this->DropdownsValue->recursive = 0;
            $countvaluesdd                   = $this->DropdownsValue->find('all', array('conditions' => array('dropdown_id' => $id)));

            $countvaluesdd = count($countvaluesdd);
            $pagebefore    = $this->Session->read('paging.' . $this->request->params['controller'] . '.' . $this->request->params['action'] . '.page');

            $divval = $countvaluesdd / 25;

            if (ceil($divval) < $pagebefore) {
                $this->Navigation->GetSessionForPaging($pagebefore - 1);
            } else {
                $this->Navigation->GetSessionForPaging();
            }
        }

        $globalDiscription = array(0 => __('not global', true), 1 => __('global', true));

        $this->set('globalDiscription', $globalDiscription);

        $reportArray                     = array(1, 2, 3, 4, 6, 7, 8, 9, 10, 11, 12);
        $this->DropdownsValue->recursive = 0;
        $this->Dropdown->recursive       = -1;

        $DropdownTestingcompOption = array('Dropdown.testingcomp_id' => AuthComponent::user('Testingcomp.id'));

        $DropdownModel = null;
        $DropdownField = null;

        if (isset($this->request->data['dropdownid'])) {
            $dropdownid = explode('0', $this->request->data['dropdownid']);

            if (count($dropdownid) == 2) {
                $DropdownModel = Sanitize::clean($dropdownid[0]);
                $DropdownField = Sanitize::clean($dropdownid[1]);
            }
            if (count($dropdownid) == 3) {
                $DropdownModel = Sanitize::clean($dropdownid[0]);
                $DropdownField = Sanitize::clean($dropdownid[1]) . '0' . Sanitize::clean($dropdownid[2]);
            }
        }

        if (AuthComponent::user('Roll.id') == 1) {

            if ($DropdownModel == null && $DropdownField == null && $id > 0) {
                $Dropdown      = $this->Dropdown->find('first', array('conditions' => array('Dropdown.id' => $id)));
                $DropdownModel = $Dropdown['Dropdown']['model'];
                $DropdownField = $Dropdown['Dropdown']['field'];
                unset($Dropdown);
            }

            $ProjectMembers = $this->Autorisierung->ProjectMember($projectID);

            $DropdownTestingcompOption = array('Dropdown.testingcomp_id !=' => 0);
            $options                   = array(
                'conditions' => array(
                    'Dropdown.model'          => $DropdownModel,
                    'Dropdown.field'          => $DropdownField,
                    'Dropdown.testingcomp_id' => AuthComponent::user('Testingcomp.id'),
                    'Dropdown.report_id'      => $reportID,
                ),
                'fields'     => array('Dropdown.id', 'Dropdown.linking'),
            );
        } else {
            $options = array(
                'conditions' => array(
                    'Dropdown.testingcomp_id' => AuthComponent::user('Testingcomp.id'),
                    'Dropdown.id'             => $id,
                ),
                'fields'     => array('Dropdown.id', 'Dropdown.linking'),
            );
        }

        $dropdownsCount = count($dropdowns = $this->Dropdown->find('all', $options));

        // Wenn zu diesem Eintrag nix existiert
        if ($dropdownsCount == 0 && isset($this->request->data['dropdownid'])) {
            // Test von wo der Aufruf kommt
            // zwecks späterer Aktualisierunt der Ursprungsseite

            //            $this->Autorisierung->IsThisMyReport($reportID);

            // Den Modelnamen aus den POST-Daten holen
            $ModelArray = explode('0', ($this->request->data['dropdownid']), 2);

            $Model0 = $ModelArray[0];
            $Model1 = $ModelArray[1];

            if (is_object($arrayData['settings']->$Model0) || is_array($arrayData['settings']->$Model0)) {
                $i = 0;
                foreach ($arrayData['settings']->$Model0->children() as $_key => $_arrayData) {
                    if (preg_replace('/\_(\d+)/', '$1', trim($_arrayData->key)) != preg_replace('/\_(\d+)/', '$1', Inflector::underscore($Model1))) {
                        continue;
                    }

                    if (trim($_arrayData->output->screen) != '') {

                        // Test ob dieser Eintrag schon vorhanden ist
                        $thisDropdown = $this->Dropdown->find('first', array('conditions' => array('dropdownid' => $dropdownid, 'report_id' => $this->request->reportID, 'testingcomp_id' => AuthComponent::user('Testingcomp.id'))));

                        if (count($thisDropdown) == 0) {
                            $insert = array(
                                'dropdownid'       => $dropdownid,
                                'model'            => trim($_arrayData->model),
                                'field'            => trim($_arrayData->key),
                                'eng'              => trim($_arrayData->discription->eng),
                                'deu'              => trim($_arrayData->discription->deu),
                                'testingcomp_id'   => AuthComponent::user('Testingcomp.id'),
                                'report_id'        => $reportnumber['Report']['id'],
                                'testingmethod_id' => $reportnumber['Testingmethod']['id'],
                            );

                            $FormName['controller'] = 'reportnumbers';

                            if ($evalutionID != 0) {
                                $FormName['action'] = 'editevalution';
                                $Terms              = implode('/', $this->request->projectvars['VarsArray']);
                                $FormName['terms']  = $Terms;
                            } else {
                                $FormName['action'] = 'edit';
                                $Terms              = implode('/', $this->request->projectvars['VarsArray']);
                                $FormName['terms']  = $Terms;
                            }

                            $this->Dropdown->create();

                            if ($this->Dropdown->save($insert)) {
                                $this->set('FormName', $FormName);
                            }

                            $this->request->projectvars['VarsArray'][6] = $id = $this->Dropdown->getInsertID();

                            // Wenn von einem Hauptadmin ein Dropdown angelegt wird,
                            // werden die Dropdopwns von den weitern Firmen gleich mit angelegt
                            if (AuthComponent::user('Roll.id') == 1) {

                                $this->Dropdown->recursive = -1;

                                if (isset($ProjectMembers) && count($ProjectMembers) > 0) {
                                    foreach ($ProjectMembers as $_ProjectMembers) {

                                        if ($_ProjectMembers == $this->request->projectvars['VarsArray'][1]) {
                                            continue;
                                        }

                                        $optionProjectMemeber                   = $insert;
                                        $optionProjectMemeber['testingcomp_id'] = $_ProjectMembers;
                                        $ProjectMemeberCompanies                = $this->Dropdown->find('first', array('conditions' => $optionProjectMemeber));
                                        if (count($ProjectMemeberCompanies) == 0) {
                                            $this->Dropdown->create();
                                            $this->Dropdown->save($optionProjectMemeber);
                                        }
                                    }
                                }
                            }
                        } else {
                            $this->request->projectvars['VarsArray'][6] = $id = $thisDropdown['Dropdown']['id'];
                        }
                        break;
                    }
                }
            }
        }

        // Wenn die Dropdownbearbeitung direkt aufgerufen wird, wird die ID vom sendenten Selectfeld als Session gespeichert
        // um eventuell angefügte Werte per Javascript dem UrsprungsSelect anzufügen
        if (isset($this->request['data']['dropdownid'])) {
            $this->Session->write('dropdownid', $this->request['data']['dropdownid']);
        }

        if (AuthComponent::user('Roll.id') == 1) {

            $options = array(
                'conditions' => array(
                    'Dropdown.model'     => $DropdownModel,
                    'Dropdown.field'     => $DropdownField,
                    'Dropdown.report_id' => $reportID,
                ),
            );
        } else {
            $options = array(
                'conditions' => array(
                    'Dropdown.testingcomp_id' => AuthComponent::user('Testingcomp.id'),
                    'Dropdown.id'             => $id,
                ),
            );
        }

        $dropdowns = $this->Dropdown->find('all', $options);

        if (count($dropdowns) > 0) {
            $model = $dropdowns[0]['Dropdown']['model'];
            $field = $dropdowns[0]['Dropdown']['field'];
            $this->set('has_dependencies', false);

            if (!empty($arrayData['settings']->$model->$field->dependencies)) {
                $this->set('has_dependencies', true);
            }

            $dropdown_id = array_map(function ($_dropdown) {
                return $_dropdown['Dropdown']['id']; }, $dropdowns); //['Dropdown']['id'];
        } else {
            $dropdown_id = 0;
        }

        $modelData = null;

        $Model = 'DropdownsValue';
        $this->loadModel($Model);
        $projectID = $this->request->projectID;

        $this->$Model->recursive = 0;

        $TestingcompOption                          = null;
        $TestingcompOption[$Model . '.report_id']   = $this->request->reportID;
        $TestingcompOption[$Model . '.dropdown_id'] = $dropdown_id;

        if (AuthComponent::user('Roll.id') == 1) {

        } else {
            $TestingcompOption[$Model . '.testingcomp_id'] = AuthComponent::user('Testingcomp.id');
        }

        $modelDataOptions = array(
            'conditions' => $TestingcompOption,
            'order'      => array($Model . '.discription' => 'desc'),
        );

        $modelData = $this->$Model->find('all', $modelDataOptions);

        // Wenn es Einträge in der Dropdowntabelle aber noch kein zugehörigen Werte gibt
        if (count($dropdowns) > 0 && $id == 0) {
            if (Set::matches('/Dropdown[testingcomp_id= ' . AuthComponent::user('Testingcomp.id') . ' ]', $dropdowns) == true) {
                foreach ($dropdowns as $_dropdowns) {
                    if ($_dropdowns['Dropdown']['testingcomp_id'] == AuthComponent::user('Testingcomp.id')) {
                        $id = $_dropdowns['Dropdown']['id'];
                        break;
                    }
                }
            }
        }

        //        $linking = array_filter(array_map(function($_dropdown) { return $_dropdown['Dropdown']['linking']; }, $dropdowns));

        unset($modelDataOptions);

        $modelDataOptions[$Model . '.report_id']      = $this->request->reportID;
        $modelDataOptions[$Model . '.testingcomp_id'] = AuthComponent::user('Testingcomp.id');
        $modelDataOptions[$Model . '.dropdown_id']    = $dropdown_id;

        if (AuthComponent::user('Roll.id') == 1) {
            unset($modelDataOptions[$Model . '.testingcomp_id']);
        }

        $this->paginate = array(
            'conditions' => $modelDataOptions,
            'limit'      => 25,
            'order'      => array('discription' => 'asc'),
        );

        $modelData = $this->paginate($Model);

        $this->Testingmethod->recursive = -1;
        $testingmethod                  = $this->Testingmethod->find('all');
        $testingmethods                 = array();
        $testingmethods[0]              = __('For all testingmethods', true);

        foreach ($testingmethod as $_testingmethod) {
            $testingmethods[$_testingmethod['Testingmethod']['value']] = $_testingmethod['Testingmethod']['verfahren'];
        }

        $this->request->projectvars['VarsArray'][6] = $id;
        $this->request->projectvars['VarsArray'][7] = $count;

        $SettingsArray = array();
        if (isset($this->request->data['backlink'])) {
            $SettingsArray['backlink'] = array(
                'discription' => __('Back', true),
                'controller'  => 'dropdowns',
                'action'      => 'index',
                'terms'       => $this->request->projectvars['VarsArray'],
            );
        }

        //        pr($arrayData['settings']);
        $_dropdown = reset($dropdowns);

        $path      = $_dropdown['Dropdown']['model'] . '/' . $_dropdown['Dropdown']['field'] . '[output/screen!=\'\']/select/protected';
        $protected = false;
        /*
        if(count($_path = $arrayData['settings']->xpath($path)) != 0) {
        $protected = (array_search(strtolower(reset($_path)), array('1','yes')) !== false);
        }
        */
        if (!$protected) {
            $SettingsArray['dellink']  = array('discription' => __('Delete all dropdown values', true), 'controller' => 'dropdowns', 'action' => 'dropdowndelete', 'terms' => $this->request->projectvars['VarsArray']);
            $SettingsArray['copylink'] = array('discription' => __('Copy other dropdown values for this dropdown', true), 'controller' => 'dropdowns', 'action' => 'linking', 'terms' => $this->request->projectvars['VarsArray']);
            $SettingsArray['addlink']  = array('discription' => __('Add', true), 'controller' => 'dropdowns', 'action' => 'dropdownadd', 'terms' => $this->request->projectvars['VarsArray']);
        }

        $modelData = $this->Data->SortthisArray($modelData, 'DropdownsValue', 'discription', 'asc', 'natural');

        $this->Navigation->SetSessionForPaging();

        $this->set('protected', $protected);
        $this->set('SettingsArray', $SettingsArray);
        $this->set('testingmethods', $testingmethods);
        $this->set('Model', $Model);
        $this->set('count', $count);
        $this->set('dropdowns', $dropdowns);
        $this->set('modelDatas', $modelData);
        $this->set('reportnumberID', $reportnumberID);
        $this->set('evalutionID', $evalutionID);

        $this->render('dropdownindex', 'modal');
    }

    public function linking($id = null)
    {
        $this->layout = 'modal';

        $reportnumberID = 0;
        $evalutionID    = 0;

        $projectID       = $this->request->projectvars['VarsArray'][0];
        $cascadeID       = $this->request->projectvars['VarsArray'][1];
        $orderID         = $this->request->projectvars['VarsArray'][2];
        $reportID        = $this->request->projectvars['VarsArray'][3];
        $reportnumberID  = $this->request->projectvars['VarsArray'][4];
        $evalutionID     = $this->request->projectvars['VarsArray'][5];
        $dropdown        = $this->request->projectvars['VarsArray'][6];
        $count           = $this->request->projectvars['VarsArray'][7];
        $testingmethodID = $this->request->projectvars['VarsArray'][8];
        $model_name      = $this->request->projectvars['VarsArray'][9];
        $linkinID        = $this->request->projectvars['VarsArray'][10];
        $linkinID_okay   = $this->request->projectvars['VarsArray'][11];

        $Model = 'DropdownsValue';
        $this->loadModel($Model);
        if ($linkinID != 0 && $linkinID_okay == 0) { // war 5

            $projectID = $this->request->projectID;

            if (AuthComponent::user('Roll.id') == 1) {
                $TestingcompCheck = null;
            } else {
                $TestingcompCheck['Dropdown.testingcomp_id'] = AuthComponent::user('Testingcomp.id');
            }

            $optionsDropdownOld = array(
                'conditions' => array(
                    $TestingcompCheck,
                    'Dropdown.id' => $linkinID,
                ),
            );

            $linkingDropdownOld = $this->Dropdown->find('first', $optionsDropdownOld);

            $verfahren_array = (explode('_', Inflector::underscore($linkingDropdownOld['Dropdown']['model'])));
            $verfahren_old   = $verfahren_array[1];

            $optionsDropdownnew = array(
                'conditions' => array(
                    'Dropdown.testingcomp_id' => AuthComponent::user('Testingcomp.id'),
                    'Dropdown.id'             => $dropdown,
                ),
            );

            $linkingDropdownNew = $this->Dropdown->find('first', $optionsDropdownnew);

            $verfahren_array = (explode('_', Inflector::underscore($linkingDropdownNew['Dropdown']['model'])));
            $verfahren_new   = $verfahren_array[1];

            $this->Testingmethod->recursive = -1;
            $testingmethod_old              = $this->Testingmethod->find('first', array('fields' => array('verfahren'), 'conditions' => array('Testingmethod.value' => $verfahren_old)));
            $testingmethod_new              = $this->Testingmethod->find('first', array('fields' => array('verfahren'), 'conditions' => array('Testingmethod.value' => $verfahren_new)));

            $xml_old       = $this->Xml->XmlToArray($verfahren_old, 'file', ucfirst($verfahren_old));
            $xml_new       = $this->Xml->XmlToArray($verfahren_new, 'file', ucfirst($verfahren_new));
            $xml_old_count = 0;
            $xml_new_count = 0;
            $lang          = Configure::read('Config.language');
            $hint_array    = array();

            $DropdownOldModel = $linkingDropdownOld['Dropdown']['model'];
            $DropdownOldField = $linkingDropdownOld['Dropdown']['field'];
            $DropdownNewModel = $linkingDropdownNew['Dropdown']['model'];
            $DropdownNewField = $linkingDropdownNew['Dropdown']['field'];

            $hint_array['old']['verfahren']   = $testingmethod_old['Testingmethod']['verfahren'];
            $hint_array['old']['model']       = $DropdownOldModel;
            $hint_array['old']['field']       = $linkingDropdownOld['Dropdown']['field'];
            $hint_array['old']['description'] = trim($xml_old->$DropdownOldModel->$DropdownOldField->discription->$lang);
            $hint_array['new']['verfahren']   = $testingmethod_new['Testingmethod']['verfahren'];
            $hint_array['new']['model']       = $linkingDropdownNew['Dropdown']['model'];
            $hint_array['new']['field']       = $linkingDropdownNew['Dropdown']['field'];
            $hint_array['new']['description'] = trim($xml_new->$DropdownNewModel->$DropdownNewField->discription->$lang);

            if (isset($xml_old->$DropdownOldModel->$DropdownOldField->dependencies)) {
                $xml_old_count = count($xml_old->$DropdownOldModel->$DropdownOldField->dependencies->children());
            }
            if (isset($xml_new->$DropdownNewModel->$DropdownNewField->dependencies)) {
                $xml_new_count = count($xml_new->$DropdownNewModel->$DropdownNewField->dependencies->children());
            }

            if ($xml_new_count > 0 && $xml_old_count == $xml_new_count) {
                foreach ($xml_old->$DropdownOldModel->$DropdownOldField->dependencies->children() as $_dependencies) {
                    $__dependencies                      = trim($_dependencies);
                    $hint_array['old']['dependencies'][] = trim($xml_old->$DropdownOldModel->$__dependencies->discription->$lang);

                }

                foreach ($xml_new->$DropdownNewModel->$DropdownNewField->dependencies->children() as $_dependencies) {
                    $__dependencies                      = trim($_dependencies);
                    $hint_array['new']['dependencies'][] = trim($xml_new->$DropdownNewModel->$__dependencies->discription->$lang);
                }
            }

            $this->set('hint_array', $hint_array);

            $this->$Model->recursive = 0;

            $modelDataOptionsNew = array(
                'conditions' => array(
                    $Model . '.dropdown_id' => $this->Sicherheit->Numeric($dropdown),
                ),
                'order'      => array($Model . '.id' => 'desc'),
            );
            $modelDataNew        = $this->$Model->find('all', $modelDataOptionsNew);

            $modelDataOptions = array(
                'conditions' => array(
                    $Model . '.dropdown_id' => $this->Sicherheit->Numeric($linkinID),
                ),
                'order'      => array($Model . '.id' => 'desc'),
            );

            $modelData = $this->$Model->find('all', $modelDataOptions);

            // Werte die im Zielfeld schon vorhanden sind werden gelöscht
            foreach ($modelData as $_key => $_modelData) {
                foreach ($modelDataNew as $__key => $__modelDataNew) {
                    if (trim($__modelDataNew[$Model]['discription']) == trim($_modelData[$Model]['discription'])) {
                        unset($modelData[$_key]);
                    }
                }
            }

            $SettingsArray = array();

            $varsArray     = $this->request->projectvars['VarsArray'];
            $varsArray[10] = 0;

            $SettingsArray['backlink'] = array('discription' => __('Back', true), 'controller' => 'dropdowns', 'action' => 'linking', 'terms' => $varsArray);

            $this->set('id', $id);
            $this->set('count', $count);
            $this->set('reportnumberID', $reportnumberID);
            $this->set('evalutionID', $evalutionID);
            $this->set('linkinID', $linkinID);
            $this->set('SettingsArray', $SettingsArray);
            $this->set('Model', $Model);
            $this->set('modelDatas', $modelData);
            $this->render('linkingadd');
        }

        if ($linkinID != 0 && $linkinID_okay == 1) { // war 5

            if (!isset($this->request->data['dependencies'])) {
                $this->request->data['dependencies'] = 0;
            }

            $optionsDropdownTest = array(
                'conditions' => array(
                    'Dropdown.testingcomp_id' => $this->Session->read('conditionsTestingcomps'),
                    'Dropdown.id'             => $linkinID,
                ),
            );

            $linkingDropdownTest = $this->Dropdown->find('first', $optionsDropdownTest);
            if (count($linkingDropdownTest) > 0) {

                $verfahren_array = (explode('_', Inflector::underscore($linkingDropdownTest['Dropdown']['model'])));
                $verfahren_old   = $verfahren_array[1];

                //                $linkingDropdownTest = reset($linkingDropdownTest);
                $optionsDropdown = array(
                    'conditions' => array(
                        'Dropdown.testingcomp_id' => $this->Session->read('conditionsTestingcomps'),
                        'Dropdown.id'             => $dropdown,
                    ),
                );

                $linkingDropdown = $this->Dropdown->find('first', $optionsDropdown);
                $verfahren_array = (explode('_', Inflector::underscore($linkingDropdown['Dropdown']['model'])));
                $verfahren_new   = $verfahren_array[1];

                //if($this->Dropdown->save($linkingDropdownUpdate)) {

                $this->loadModel('DropdownsValue');
                $this->DropdownsValue->recursive = -1;
                $values                          = $this->DropdownsValue->find('all', array('conditions' => array('dropdown_id' => $linkinID)));
                $values_old                      = $values;

                foreach ($values as $_id => $_value) {
                    $_value = array_merge($_value['DropdownsValue'], array(
                        'model'          => $linkingDropdown['Dropdown']['model'],
                        'field'          => $linkingDropdown['Dropdown']['field'],
                        'testingcomp_id' => $this->Auth->user('testingcomp_id'),
                        'user_id'        => $this->Auth->user('id'),
                        'report_id'      => $reportID,
                        'dropdown_id'    => $linkingDropdown['Dropdown']['id'],
                    ));

                    foreach (array('id', 'created', 'modified') as $field) {
                        if (isset($_value[$field])) {
                            unset($_value[$field]);
                        }

                    }

                    $values[$_id]['DropdownsValue'] = $_value;
                }

                // doppelte Werte entfernen
                foreach ($values as $_key => $_values) {
                    $conditions = array(
                        'conditions' => array(
                            'DropdownsValue.model'       => $_values['DropdownsValue']['model'],
                            'DropdownsValue.field'       => $_values['DropdownsValue']['field'],
                            'DropdownsValue.discription' => $_values['DropdownsValue']['discription'],
                            'DropdownsValue.report_id'   => $_values['DropdownsValue']['report_id'],
                        ),
                    );
                    if ($this->DropdownsValue->find('count', $conditions) > 0) {
                        unset($values[$_key]);
                        unset($values_old[$_key]);
                    }
                }

                if ($this->request->data['dependencies'] == 1) {

                    $this->loadModel('Dependencie');
                    foreach ($values_old as $_key => $_values_old) {
                        $values_old[$_key]['Dependencie'] = $this->Dependencie->find('all', array('conditions' => array('dropdowns_value_id' => $_values_old['DropdownsValue']['id'])));

                    }
                }

                if ($values) {

                    if ($this->request->data['dependencies'] == 1) {
                        $dependencie_new = array();

                        $this->loadModel('Dependencie');
                        $dependencie_test = $this->Dependencie->find('first', array('conditions' => array('dropdown_id' => $linkinID)));

                        if (count($dependencie_test) == 1) {
                            $values_new = $this->DropdownsValue->find(
                                'all',
                                array(
                                    'conditions' => array(
                                        'dropdown_id' => $dropdown,
                                    ),
                                )
                            );

                            $xml_old = $this->Xml->XmlToArray($verfahren_old, 'file', ucfirst($verfahren_old));
                            $xml_new = ($verfahren_old == $verfahren_new) ? $xml_old : $this->Xml->XmlToArray($verfahren_new, 'file', ucfirst($verfahren_new));

                            $DropdownTestField    = $linkingDropdownTest['Dropdown']['field'];
                            $DropdownTestModel    = $linkingDropdownTest['Dropdown']['model'];
                            $DropdownLinkingModel = $linkingDropdown['Dropdown']['model'];
                            $DropdownLinkingField = $linkingDropdown['Dropdown']['field'];

                            $xml_old_count = count($xml_old->$DropdownTestModel->$DropdownTestField->dependencies->children());
                            $xml_new_count = count($xml_new->$DropdownLinkingModel->$DropdownLinkingField->dependencies->children());

                            foreach ($xml_old->$DropdownTestModel->$DropdownTestField->dependencies->children() as $_dependencies_old) {
                                $xml_old_dependencies[] = trim($_dependencies_old);
                            }
                            foreach ($xml_new->$DropdownLinkingModel->$DropdownLinkingField->dependencies->children() as $_dependencies_new) {
                                $xml_new_dependencies[] = trim($_dependencies_new);
                            }

                            if ($xml_old_count == $xml_new_count) {
                                foreach ($values_old as $_key => $_values_old) {

                                    // Dropdown Values anlegen, welche abhängige Werte haben und diese aus der Liste entfernen
                                    if (isset($_values_old['Dependencie']) && count($_values_old['Dependencie']) > 0) {
                                        $this->DropdownsValue->create();
                                        $this->DropdownsValue->save($values[$_key]);

                                        unset($values[$_key]);

                                        $inser_id = $this->DropdownsValue->getInsertID();

                                        foreach ($_values_old['Dependencie'] as $__key => $__values_old) {
                                            unset($__values_old['Dependencie']['id']);
                                            $__values_old['Dependencie']['dropdown_id']        = $linkingDropdown['Dropdown']['id'];
                                            $__values_old['Dependencie']['dropdowns_value_id'] = $inser_id;

                                            foreach ($xml_old_dependencies as $___key => $___xml_old_dependencies) {
                                                if ($__values_old['Dependencie']['field'] == $___xml_old_dependencies) {
                                                    $__values_old['Dependencie']['field'] = $xml_new_dependencies[$___key];
                                                }
                                            }
                                            $dependencie_new[] = $__values_old['Dependencie'];
                                        }
                                    }
                                }
                            }
                        }

                        $this->Dependencie->saveMany($dependencie_new);
                    }

                    // Dropdown Values ohne abhängige Werte noch abspeichern
                    $this->DropdownsValue->saveMany($values);

                    $FormName = array();

                    $FormName['controller'] = 'reportnumbers';

                    if ($evalutionID != 0) {
                        $FormName['action'] = 'editevalution';
                        $Terms              = implode('/', $this->request->projectvars['VarsArray']);
                        $FormName['terms']  = $Terms;
                    } else {
                        $FormName['action'] = 'edit';
                        $Terms              = implode('/', $this->request->projectvars['VarsArray']);
                        $FormName['terms']  = $Terms;
                    }

                    $this->set('reportnumberID', $reportnumberID);
                    $this->set('evalutionID', 0);
                    $this->set('FormName', $FormName);
                    $this->render('dellinking');
                }
            }
        }

        $this->loadModel('DropdownsValue');
        $testingmethods = array();
        $_models        = $this->DropdownsValue->find('list', array('fields' => array('model'), 'group' => array('model')));
        $parts          = array();

        foreach ($_models as $key => $value) {
            $_replace = null;
            if (preg_match('/^Report([a-zA-Z]+)(Generally|Specific|Evaluation)$/', $value, $_replace)) {
                $parts[strtolower($_replace[1])][] = $_replace[2];
                $parts[strtolower($_replace[1])]   = array_intersect(array(1 => 'Generally', 2 => 'Specific', 3 => 'Evaluation'), $parts[strtolower($_replace[1])]);

                $testingmethods[strtolower($_replace[1])] = strtolower($_replace[1]);
            }
        }

        $testingmethods = $this->Testingmethod->find('all', array(
            'conditions' => array(
                'Testingmethod.value'             => array_values($testingmethods),
                'ReportsTopproject.topproject_id' => $this->Autorisierung->ConditionsTopprojects(),
            ),
            'joins'      => array(
                array('table' => 'testingmethods_reports', 'alias' => 'TestingmethodsReport', 'conditions' => array('TestingmethodsReport.testingmethod_id = Testingmethod.id')),
                array('table' => 'reports_topprojects', 'alias' => 'ReportsTopproject', 'conditions' => array('ReportsTopproject.report_id = TestingmethodsReport.report_id')),
            ),
        ));

        $all_value_testingmethods = Hash::combine($testingmethods, '{n}.Testingmethod.id', '{n}.Testingmethod.value');
        $all_id_testingmethods    = Hash::combine($testingmethods, '{n}.Testingmethod.id', '{n}.Testingmethod.verfahren');

        $all_order_testingmethods = array();
        $methods_tmp              = array_flip($all_value_testingmethods);
        foreach ($parts as $key => $value) {
            $all_order_testingmethods[$methods_tmp[$key]] = $value;
        }

        if ($testingmethodID == 0 && $model_name == 0) {
            $SettingsArray['backlink'] = array('discription' => __('Back', true), 'controller' => 'dropdowns', 'action' => 'dropdownindex', 'terms' => $this->request->projectvars['VarsArray']);
            $this->set('SettingsArray', $SettingsArray);
            $this->set('all_id_testingmethods', $all_id_testingmethods);
            $this->set('all_order_testingmethods', $all_order_testingmethods);
            $this->render('linkingsort');
            return;
        }

        $Suchmuster  = array('Generally', 'Specific', 'Evaluation');
        $ReportModel = 'Report' . ucfirst($all_value_testingmethods[$testingmethodID]) . $Suchmuster[$model_name - 1];

        $option    = array(
            'conditions' => array(
                'Dropdown.id' => $id,
            ),
        );
        $dropdowns = $this->Dropdown->find('first', $option);

        $this->Dropdown->recursive = 0;
        $this->paginate            = array(
            'conditions' => array(
                'Dropdown.testingcomp_id' => $this->Session->read('conditionsTestingcomps'),
                //                'Dropdown.testingmethod_id' => $testingmethodID,
                'Dropdown.model'          => $ReportModel,
                'Dropdown.linking'        => 0,
                'Dropdown.id !='          => $dropdown,
            ),
            'limit'      => 5000,
            'order'      => array('Dropdown.' . Configure::read('Config.language') => 'ASC', 'Dropdown.model' => 'ASC', 'Report.Name' => 'ASC'),
        );

        $modelData = $this->paginate('Dropdown');

        $SettingsArray = array();

        $VarsArraySettings    = $this->request->projectvars['VarsArray'];
        $VarsArraySettings[8] = 0;
        $VarsArraySettings[9] = 0;

        $SettingsArray['backlink'] = array('discription' => __('Back', true), 'controller' => 'dropdowns', 'action' => 'linking', 'terms' => $VarsArraySettings);

        foreach ($modelData as $_key => $_modelData) {
            //            pr($_key);
            $availableModels = array();
            $ModelNames      = explode(' ', $_modelData['Dropdown']['model']);
            $FieldNames      = explode(' ', $_modelData['Dropdown']['field']);
            $x               = 0;
            foreach ($ModelNames as $__key => $_ModelNames) {
                $__ModelNames = (preg_replace('/Report/', '', $_ModelNames));
                foreach ($Suchmuster as $_Suchmuster) {
                    if (count(explode($_Suchmuster, $__ModelNames)) == 2) {
                        $thisModel                        = (explode($_Suchmuster, $__ModelNames));
                        $availableModels[$x]['model']     = $_Suchmuster;
                        $availableModels[$x]['verfahren'] = $thisModel[0];
                        $availableModels[$x]['feld']      = $FieldNames[$__key];
                    }
                }
                $x++;
            }
            $modelData[$_key]['Dropdown']['available'] = $availableModels;
            $valueCount                                = $this->DropdownsValue->find('count', array('conditions' => array('dropdown_id' => $_modelData['Dropdown']['id'])));
            if (($valueCount) > 0) {
                $modelData[$_key]['valueCount'] = $valueCount;
            } else {
                unset($modelData[$_key]);
            }
        }

        //        pr($modelData);

        $this->set('id', $id);
        $this->set('reportnumberID', $reportnumberID);
        $this->set('evalutionID', $evalutionID);
        $this->set('id', $id);
        $this->set('count', $count);
        $this->set('SettingsArray', $SettingsArray);
        $this->set('dropdown', $dropdowns);
        $this->set('modelDatas', $modelData);
        $this->set('all_id_testingmethods', $all_id_testingmethods);
        $this->set('all_order_testingmethods', $all_order_testingmethods);
    }

    public function dellinking($id = null)
    {

        $id           = null;
        $this->layout = 'modal';

        $reportnumberID = $this->request->projectvars['VarsArray'][5];
        $evalutionID    = $this->request->projectvars['VarsArray'][6];
        $id             = $this->request->projectvars['VarsArray'][8];
        $count          = $this->request->projectvars['VarsArray'][9];

        $linkingDropdownUpdate = array('id' => $id, 'linking' => 0);

        if ($this->Dropdown->save($linkingDropdownUpdate)) {

            $FormName = array();

            $FormName['controller'] = 'reportnumbers';

            if ($evalutionID != 0) {
                $FormName['action'] = 'editevalution';
                $Terms              =
                    $this->request->projectvars['VarsArray'][0] . '/' .
                    $this->request->projectvars['VarsArray'][1] . '/' .
                    $this->request->projectvars['VarsArray'][2] . '/' .
                    $this->request->projectvars['VarsArray'][3] . '/' .
                    $this->request->projectvars['VarsArray'][4] . '/' .
                    $this->request->projectvars['VarsArray'][5] . '/' .
                    $this->request->projectvars['VarsArray'][6];
                $FormName['terms']  = $Terms;
            } else {
                $FormName['action'] = 'edit';
                $Terms              =
                    $this->request->projectvars['VarsArray'][0] . '/' .
                    $this->request->projectvars['VarsArray'][1] . '/' .
                    $this->request->projectvars['VarsArray'][2] . '/' .
                    $this->request->projectvars['VarsArray'][3] . '/' .
                    $this->request->projectvars['VarsArray'][4] . '/' .
                    $this->request->projectvars['VarsArray'][5];
                $FormName['terms']  = $Terms;
            }

            $this->set('FormName', $FormName);
            $this->set('reportnumberID', $reportnumberID);
            $this->set('evalutionID', $evalutionID);
        } else {
            $this->Session->setFlash(__('Error, unlinking fail'));
        }
    }

    public function dropdownedit($id = null)
    {

        $this->layout = 'modal';

        $projectID      = $this->request->projectvars['VarsArray'][0];
        $cascadeID      = $this->request->projectvars['VarsArray'][1];
        $orderID        = $this->request->projectvars['VarsArray'][2];
        $reportID       = $this->request->projectvars['VarsArray'][3];
        $reportnumberID = $this->request->projectvars['VarsArray'][4];
        $evalutionID    = $this->request->projectvars['VarsArray'][5];
        $dropdown       = $this->request->projectvars['VarsArray'][6];
        $count          = $this->request->projectvars['VarsArray'][7];
        $dropdownID     = $this->request->projectvars['VarsArray'][9];

        $FormName = array();

        $settings = null;
        $field    = null;
        $model    = null;

        // pr($this->request);
        if ($reportnumberID > 0) {
            $this->loadModel('Reportnumber');
            $report       = $this->Reportnumber->find('first', array('conditions' => array('Reportnumber.id' => $reportnumberID)));
            $dd           = $this->Dropdown->find('first', array('fields' => array('field', 'model'), 'conditions' => array('Dropdown.id' => $dropdown)));
            $field        = $dd['Dropdown']['field'];
            $model        = $dd['Dropdown']['model'];
            $Verfahren    = ucfirst($report['Testingmethod']['value']);
            $verfahren    = $report['Testingmethod']['value'];
            $settingsData = $this->Xml->DatafromXml($verfahren, 'file', $Verfahren);
            $settings     = $settingsData['settings'];
        }

        //     $this->Autorisierung->Protect($settings,$field,$model);
        //  pr($report);

        /*
        if (!$this->Dropdown->exists($dropdown)) {
        throw new NotFoundException(__('Invalid evalution'));
        }
        */
        $options   = array(
            'conditions' => array(
                'Dropdown.' . $this->Dropdown->primaryKey => $dropdown,
                'Dropdown.testingcomp_id'                 => $this->Session->read('conditionsTestingcomps'),
            ),
        );
        $dropdowns = $this->Dropdown->find('first', $options);

        $Model = 'DropdownsValue';
        $this->loadModel($Model);
        $options = array('conditions' => array($Model . '.id' => $dropdownID));

        $this->$Model->recursive = -1;
        $dropdownData            = ($this->$Model->find('first', $options));

        if (isset($this->request->data[$Model]) || $this->request->is('put')) {

            // Werte ergänzen
            $Auth = $this->Session->read('Auth');
            $data = $this->request->data;
            //            $data[$Model]['testingcomp_id'] = $Auth['User']['testingcomp_id'];
//            $data[$Model]['user_id'] = $Auth['User']['id'];
            $data[$Model]['report_id']   = $reportID;
            $data[$Model]['dropdown_id'] = $dropdownID;

            if ($this->$Model->save($data)) {
                $this->Autorisierung->Logger($data[$Model]['dropdown_id'], $data);
                $this->Session->setFlash(__('The value has been be saved.'));

                $lastmodalArrayURL = $this->Session->read('lastmodalArrayURL');

                if (
                    isset($lastmodalArrayURL) && is_array($lastmodalArrayURL) && count($lastmodalArrayURL) == 4 &&
                    ($lastmodalArrayURL['controller'] != 'reportnumbers' ||
                        ($lastmodalArrayURL['controller'] != 'orders' ||
                            $lastmodalArrayURL['controller'] != 'examiners'))
                ) {

                    $FormName['controller'] = $lastmodalArrayURL['controller'];
                    $FormName['action']     = $lastmodalArrayURL['action'];
                    $FormName['terms']      = implode('/', $lastmodalArrayURL['pass']);

                    $this->set('saveOK', 4);
                    $this->set('FormName', $FormName);

                    return false;
                }

                $this->set('reportnumberID', $reportnumberID);
                $this->set('evalutionID', $evalutionID);

                $FormName['controller'] = 'reportnumbers';

                if ($evalutionID != 0) {
                    $FormName['action'] = 'editevalution';
                    $Terms              = implode('/', $this->request->projectvars['VarsArray']);
                    $FormName['terms']  = $Terms;
                } else {
                    $FormName['action'] = 'edit';
                    $Terms              = implode('/', $this->request->projectvars['VarsArray']);
                    $FormName['terms']  = $Terms;
                }

                if ($this->request->data[$Model]['after_saving'] == 2) {
                    $FormName2['controller'] = 'dropdowns';
                    $FormName2['action']     = 'dropdownindex';
                    $FormName2['terms']      = $Terms;
                    $this->set('FormName2', $FormName2);
                }

                if ($this->request->data[$Model]['after_saving'] == 3) {
                    $FormName2['controller'] = 'dropdowns';
                    $FormName2['action']     = 'dropdownadd';
                    $FormName2['terms']      = $Terms;
                    $this->set('FormName2', $FormName2);
                }

                $this->set('FormName', $FormName);
                $this->set('saveOK', $this->request->data[$Model]['after_saving']);

            } else {
                $this->Session->setFlash(__('The evalution could not be saved. Please, try again.'));
            }
        } else {
            $this->request->data = $dropdownData;
        }

        $this->Testingmethod->recursive = -1;
        $testingmethod                  = $this->Testingmethod->find('all');
        $testingmethods                 = array();
        $testingmethods[0]              = __('For all testingmethods', true);
        foreach ($testingmethod as $_testingmethod) {
            $testingmethods[$_testingmethod['Testingmethod']['value']] = $_testingmethod['Testingmethod']['verfahren'];
        }

        // nur ein Notbehelf, wird mit der Zusammenfassung der Dropdowns neu programmiert
        if ($Model == 'Examinierer') {
            foreach ($dropdownData as $_key => $_dropdownData) {
                if ($_key == 'Qualification') {

                    // Prüfverfahren hinzufügen
                    foreach ($_dropdownData as $__key => $__dropdownData) {
                        foreach ($testingmethod as $_testingmethod) {
                            if ($_testingmethod['Testingmethod']['id'] == $__dropdownData['testingmethod_id']) {
                                $_dropdownData[$__key]['testingmethod'] = $_testingmethod['Testingmethod']['verfahren'];
                                break;
                            }
                        }
                    }

                    $belonging[$_key] = $_dropdownData;
                    $this->set('belonging', $belonging);
                    break;
                }
            }
        }

        $SettingsArray                              = array();
        $this->request->projectvars['VarsArray'][9] = 0;

        $SettingsArray['addlink']  = array('discription' => __('Add', true), 'controller' => 'dropdowns', 'action' => 'dropdownadd', 'terms' => $this->request->projectvars['VarsArray']);
        $SettingsArray['backlink'] = array('discription' => __('Back', true), 'controller' => 'dropdowns', 'action' => 'dropdownindex', 'terms' => $this->request->projectvars['VarsArray']);

        $this->set('FormName', $FormName);
        $this->set('SettingsArray', $SettingsArray);
        $this->set('id', $id);
        $this->set('testingmethods', $testingmethods);
        $this->set('Model', $Model);
        $this->set('dropdowns', $dropdowns);
        $this->set('dropdownData', $dropdownData);
        //$this->layout = 'mdump';
    }

    public function dropdownadd($id = null)
    {
        $this->layout = 'modal';

        $reportnumberID = 0;
        $evalutionID    = 0;

        $projectID      = $this->request->projectvars['VarsArray'][0];
        $cascadeID      = $this->request->projectvars['VarsArray'][1];
        $orderID        = $this->request->projectvars['VarsArray'][2];
        $reportID       = $this->request->projectvars['VarsArray'][3];
        $reportnumberID = $this->request->projectvars['VarsArray'][4];
        $evalutionID    = $this->request->projectvars['VarsArray'][5];
        $dropdown       = $this->request->projectvars['VarsArray'][6];
        $count          = $this->request->projectvars['VarsArray'][7];
        $dropdownID     = $this->request->projectvars['VarsArray'][9];

        $settings = null;
        $field    = null;
        $model    = null;

        // pr($this->request);
        if ($reportnumberID > 0) {
            $this->loadModel('Reportnumber');
            $report       = $this->Reportnumber->find('first', array('conditions' => array('Reportnumber.id' => $reportnumberID)));
            $dd           = $this->Dropdown->find('first', array('fields' => array('field', 'model'), 'conditions' => array('Dropdown.id' => $dropdown)));
            $field        = $dd['Dropdown']['field'];
            $model        = $dd['Dropdown']['model'];
            $Verfahren    = ucfirst($report['Testingmethod']['value']);
            $verfahren    = $report['Testingmethod']['value'];
            $settingsData = $this->Xml->DatafromXml($verfahren, 'file', $Verfahren);
            $settings     = $settingsData['settings'];
        }

        //    $this->Autorisierung->Protect($settings,$field,$model);
        if ($dropdown == 0) {
            $dropdown = $this->request->projectvars['VarsArray'][8];
        }

        if ($count == 0) {
            $count = $this->request->projectvars['VarsArray'][9];
        }

        if (!$this->Dropdown->exists($dropdown)) {
            return;
        }

        $options = array(
            'conditions' => array(
                'Dropdown.id'             => $dropdown,
                'Dropdown.testingcomp_id' => $this->Session->read('conditionsTestingcomps'),
            ),
        );

        $dropdowns = $this->Dropdown->find('first', $options);

        $options = array(
            'conditions' => array(
                'Reportnumber.id' => $reportnumberID,
            ),
        );

        if ($reportnumberID > 0) {

            $reportnumber = $this->Reportnumber->find('first', $options);

            if (count($dropdowns) == 0 && $reportnumberID > 0) {

                $this->request->projectvars['VarsArray'][6] = 0;
                $dropdown                                   = $this->Drops->CreateNewDropdownEntry($settingsData, $reportnumber);
                $this->request->projectvars['VarsArray'][6] = $dropdown;

                $options = array(
                    'conditions' => array(
                        'Dropdown.' . $this->Dropdown->primaryKey => $dropdown,
                        'Dropdown.testingcomp_id'                 => $this->Session->read('conditionsTestingcomps'),
                    ),
                );

                $dropdowns = $this->Dropdown->find('first', $options);

            }
        }
        unset($dropdowns['Testingcomp']);
        $Model = 'DropdownsValue';
        $this->loadModel($Model);
        $this->loadModel('Report');
        // Info, welche Spalten gibt es im geladenen Model
        $this->$Model->recursive = -1;
        $modelTest               = ($this->$Model->find('first'));

        $this->Report->recursive = -1;
        $optionsReports          = array('conditions' => array('Report.id' => $dropdowns['Dropdown']['report_id']));
        $report                  = ($this->Report->find('first', $optionsReports));
        $dropdownData            = array_merge($dropdowns, $report);

        if (isset($this->request->data[$Model]) && trim($this->request->data[$Model]['discription']) != '') {

            $this->$Model->create();
            // Werte ergänzen
            $Auth = $this->Session->read('Auth');
            $data = $this->request->data;

            $data[$Model]['testingcomp_id'] = $Auth['User']['testingcomp_id'];
            $data[$Model]['user_id']        = $Auth['User']['id'];
            $data[$Model]['report_id']      = $reportID; //$dropdownData['Report']['id'];
            $data[$Model]['dropdown_id']    = $dropdownData['Dropdown']['id'];
            $data[$Model]['model']          = $dropdowns['Dropdown']['model'];
            $data[$Model]['field']          = $dropdowns['Dropdown']['field'];

            if ($this->$Model->save($data)) {

                $this->Autorisierung->Logger($data[$Model]['dropdown_id'], $data);
                $this->Session->setFlash(__('The data has been saved'));

                $lastmodalArrayURL = $this->Session->read('lastmodalArrayURL');

                if (
                    isset($lastmodalArrayURL) && is_array($lastmodalArrayURL) && count($lastmodalArrayURL) == 4 &&
                    ($lastmodalArrayURL['controller'] == 'examiners' ||
                        $lastmodalArrayURL['controller'] == 'devices' ||
                        $lastmodalArrayURL['controller'] == 'orders' ||
                        $lastmodalArrayURL['controller'] == 'documents' ||
                        $lastmodalArrayURL['controller'] == 'welders')
                ) {

                    $FormName['controller'] = $lastmodalArrayURL['controller'];
                    $FormName['action']     = $lastmodalArrayURL['action'];
                    $FormName['terms']      = implode('/', $lastmodalArrayURL['pass']);

                    $this->set('saveOK', 4);
                    $this->set('FormName', $FormName);

                    return false;
                }

                if (!empty($reportnumberID)) {
                    $this->set('reportnumberID', $reportnumberID);
                    $this->set('evalutionID', $evalutionID);

                    $FormName['controller'] = 'reportnumbers';

                    if ($evalutionID != 0) {
                        $FormName['action'] = 'editevalution';
                        $Terms              = implode('/', $this->request->projectvars['VarsArray']);
                        $FormName['terms']  = $Terms;
                    } else {
                        $FormName['action'] = 'edit';
                        $Terms              = implode('/', $this->request->projectvars['VarsArray']);
                        $FormName['terms']  = $Terms;
                    }

                    if ($this->request->data[$Model]['after_saving'] == 2) {
                        $FormName2['controller'] = 'dropdowns';
                        $FormName2['action']     = 'dropdownindex';
                        $FormName2['terms']      = $Terms;
                        $this->set('FormName2', $FormName2);
                    }
                    if ($this->request->data[$Model]['after_saving'] == 3) {
                        $FormName2['controller'] = 'dropdowns';
                        $FormName2['action']     = 'dropdownadd';
                        $FormName2['terms']      = $Terms;
                        $this->set('FormName2', $FormName2);
                    }
                } else {
                    if ($this->request->examinerID > 0) {
                        $FormName['controller'] = 'examiners';
                        $FormName['action']     = 'overview';
                    }
                    if ($this->request->deviceID > 0) {
                        $FormName['controller'] = 'devices';
                        $FormName['action']     = 'view';
                    }

                    $Terms             = implode('/', $this->request->projectvars['VarsArray']);
                    $FormName['terms'] = $Terms;

                    if ($this->request->data[$Model]['after_saving'] == 2) {
                        $FormName2['controller'] = 'dropdowns';
                        $FormName2['action']     = 'dropdownindex';
                        $FormName2['terms']      = $Terms;
                        $this->set('FormName2', $FormName2);
                    }
                    if ($this->request->data[$Model]['after_saving'] == 3) {
                        $FormName2['controller'] = 'dropdowns';
                        $FormName2['action']     = 'dropdownadd';
                        $FormName2['terms']      = $Terms;
                        $this->set('FormName2', $FormName2);
                    }
                }

                $this->set('saveOK', $this->request->data[$Model]['after_saving']);
                $this->set('FormName', $FormName);
                /*
                if($this->request->data[$Model]['after_saving'] == 1){
                }
                if($this->request->data[$Model]['after_saving'] == 2){
                $url = explode('/',$this->request->url);
                $here = explode('/',$this->request->here);
                $this->request->params['action'] = 'dropdownindex';
                $url[1] = 'dropdownindex';
                $here[3] = 'dropdownindex';
                $this->request->url = implode('/',$url);
                $this->request->here = implode('/',$here);
                if($this->request->reportnumberID > 0) {
                $this->include_dropdownindex($id = null);
                } else {
                $this->include_modaldropdownindex($id = null);
                }
                return;
                }
                if($this->request->data[$Model]['after_saving'] == 3){
                unset($this->request->data);
                $this->request->params['pass'][9] = 0;
                $this->request->projectvars['VarsArray'][9] = 0;
                $this->request->projectvars['dropdownID'] = 0;
                $this->dropdownadd($id = null);
                return;
                }
                */

            } else {
                $this->Session->setFlash(__('The data could not be saved. Please, try again.'));
            }
        }

        $this->Testingmethod->recursive = -1;
        $testingmethod                  = $this->Testingmethod->find('all');
        $testingmethods                 = array();
        $testingmethods[0]              = __('For all testingmethods', true);
        foreach ($testingmethod as $_testingmethod) {
            $testingmethods[$_testingmethod['Testingmethod']['value']] = $_testingmethod['Testingmethod']['verfahren'];
        }

        $SettingsArray                              = array();
        $this->request->projectvars['VarsArray'][9] = 0;

        $SettingsArray['backlink'] = array('discription' => __('Back', true), 'controller' => 'dropdowns', 'action' => 'dropdownindex', 'terms' => $this->request->projectvars['VarsArray']);

        $this->set('SettingsArray', $SettingsArray);
        $this->set('testingmethods', $testingmethods);
        $this->set('modelTest', $modelTest);
        $this->set('Model', $Model);
        $this->set('dropdowns', $dropdowns);
        $this->set('dropdownData', $dropdownData);
        $this->render('dropdownadd');
        return;
    }

    public function dropdowndelete($id = null)
    {

        $this->layout = 'modal';

        $projectID      = $this->request->projectvars['VarsArray'][0];
        $cascadeID      = $this->request->projectvars['VarsArray'][1];
        $orderID        = $this->request->projectvars['VarsArray'][2];
        $reportID       = $this->request->projectvars['VarsArray'][3];
        $reportnumberID = $this->request->projectvars['VarsArray'][4];
        $evalutionID    = $this->request->projectvars['VarsArray'][5];
        $dropdown       = $this->request->projectvars['VarsArray'][6];
        $count          = $this->request->projectvars['VarsArray'][7];
        $dropdownID     = $this->request->projectvars['VarsArray'][9];

        $settings = null;
        $field    = null;
        $model    = null;
        // pr($this->request);
        if ($reportnumberID > 0) {
            $this->loadModel('Reportnumber');
            $report       = $this->Reportnumber->find('first', array('conditions' => array('Reportnumber.id' => $reportnumberID)));
            $dd           = $this->Dropdown->find('first', array('fields' => array('field', 'model'), 'conditions' => array('Dropdown.id' => $dropdown)));
            $field        = $dd['Dropdown']['field'];
            $model        = $dd['Dropdown']['model'];
            $Verfahren    = ucfirst($report['Testingmethod']['value']);
            $verfahren    = $report['Testingmethod']['value'];
            $settingsData = $this->Xml->DatafromXml($verfahren, 'file', $Verfahren);
            $settings     = $settingsData['settings'];

        }
        $this->Autorisierung->Protect($settings, $field, $model);

        /*    if (!$this->Dropdown->exists($dropdown)) {
        throw new NotFoundException(__('Invalid evalution'));
        }*/

        $options   = array(
            'conditions' => array(
                'Dropdown.' . $this->Dropdown->primaryKey => $dropdown,
                'Dropdown.testingcomp_id'                 => $this->Session->read('conditionsTestingcomps'),
            ),
        );
        $dropdowns = $this->Dropdown->find('first', $options);

        $Model = 'DropdownsValue';
        $this->loadModel($Model);
        $this->$Model->recursive = -1;

        $options                 = array('conditions' => array($Model . '.id' => $dropdownID));
        $this->$Model->recursive = 0;
        $dropdownData            = ($this->$Model->find('first', $options));

        if ($dropdownID > 0) {
            $this->$Model->id = $dropdownID;
            if (!$this->$Model->exists()) {
                throw new NotFoundException(__('Invalid data'));
            }
        }

        $this->DropdownsValue->Dependency->recursive = -1;

        if (!isset($this->request->data['Dropdown']['_delete'])) {

            $lang = Configure::read('Config.language');

            if (!isset($dropdownData['DropdownsValue'])) {
                $message = __('Will you delete all values from the dropdown field', true) . ': ' . $dropdowns['Dropdown'][$lang];
            } else {
                $message = __('Will you delete this value?');
            }

            $this->request->projectvars['VarsArray'][9] = 0;
            $SettingsArray['backlink']                  = array('discription' => __('Back', true), 'controller' => 'dropdowns', 'action' => 'dropdownindex', 'terms' => $this->request->projectvars['VarsArray']);
            $this->set('message', $message);
            $this->set('SettingsArray', $SettingsArray);
            $this->set('lang', $lang);
            $this->set('dropdowns', $dropdowns);
            $this->set('dropdownData', $dropdownData);
            $this->render('confirm');
            return;
        }

        if (isset($this->request->data['Dropdown']['_delete']) && $this->request->data['Dropdown']['_delete'] == 1) {

            if ($dropdownID > 0) {
                if (!$this->$Model->delete()) {
                    $this->Session->setFlash(__('Evalution was not deleted'));
                    $this->request->projectvars['VarsArray'][10] = 0;
                    $SettingsArray['backlink']                   = array('discription' => __('Back', true), 'controller' => 'dropdowns', 'action' => 'dropdownindex', 'terms' => $this->request->projectvars['VarsArray']);
                    $this->set('SettingsArray', $SettingsArray);
                    $this->render('confirm');
                    return;
                }
                $DependencyOption = array('Dependency.dropdowns_value_id' => $dropdownID);
            }
            if ($dropdownID == 0) {
                $DropdownValueOption = array($Model . '.dropdown_id' => $dropdown);
                $DependencyOption    = array('Dependency.dropdown_id' => $dropdown);
                $this->$Model->deleteAll($DropdownValueOption, false, false);
            }

            $this->DropdownsValue->Dependency->deleteAll($DependencyOption, false, false);
            isset($dropdownData[$Model]['dropdown_id']) ? $this->Autorisierung->Logger($dropdownData[$Model]['dropdown_id'], $dropdownData) : 0;

            $lastmodalArrayURL = $this->Session->read('lastmodalArrayURL');

            if (isset($lastmodalArrayURL) && is_array($lastmodalArrayURL) && count($lastmodalArrayURL) == 4 && $lastmodalArrayURL['controller'] == 'examiners') {

                $FormName['controller'] = $lastmodalArrayURL['controller'];
                $FormName['action']     = $lastmodalArrayURL['action'];
                $FormName['terms']      = implode('/', $lastmodalArrayURL['pass']);

                $this->set('saveOK', 4);
                $this->set('FormName', $FormName);

                return false;
            }

            $this->set('reportnumberID', $reportnumberID);
            $this->set('evalutionID', $evalutionID);

            $this->Session->setFlash(__('Value was deleted'));

            $FormName['controller'] = 'reportnumbers';

            if ($evalutionID != 0) {
                $FormName['action'] = 'editevalution';
                $Terms              = implode('/', $this->request->projectvars['VarsArray']);
                $FormName['terms']  = $Terms;
            } else {
                $FormName['action'] = 'edit';
                $Terms              = implode('/', $this->request->projectvars['VarsArray']);
                $FormName['terms']  = $Terms;
            }

            $this->set('FormName', $FormName);

            if ($this->request->data['Dropdown']['after_saving'] == 1) {
            }

            if ($this->request->data['Dropdown']['after_saving'] == 2) {

                $url                             = explode('/', $this->request->url);
                $here                            = explode('/', $this->request->here);
                $this->request->params['action'] = 'dropdownindex';
                $url[1]                          = 'dropdownindex';
                $here[3]                         = 'dropdownindex';
                $this->request->url              = implode('/', $url);
                $this->request->here             = implode('/', $here);

                if ($this->request->reportnumberID > 0) {
                    $this->include_dropdownindex($id = null);
                } else {
                    $this->include_modaldropdownindex($id = null);
                }
                return;
            }

            if ($this->request->data['Dropdown']['after_saving'] == 3) {

                unset($this->request->data);
                $this->request->params['pass'][9]           = 0;
                $this->request->projectvars['VarsArray'][9] = 0;
                $this->request->projectvars['dropdownID']   = 0;
                $this->dropdownadd($id = null);
                return;
            }

            $this->set('saveOK', $this->request->data['Dropdown']['after_saving']);
        }

        $SettingsArray                              = array();
        $this->request->projectvars['VarsArray'][9] = 0;
        $SettingsArray['backlink']                  = array('discription' => __('Back', true), 'controller' => 'dropdowns', 'action' => 'dropdownindex', 'terms' => $this->request->projectvars['VarsArray']);

        $this->set('FormName', $FormName);
        $this->set('SettingsArray', $SettingsArray);
    }

    public function modaladddata()
    {
        $this->layout  = 'modal';
        $id            = $this->request->projectvars['VarsArray'][15];
        $SettingsArray = array();

        $this->loadModel('DropdownsMaster');

        $options =
            array(
                'conditions' => array(
                    'DropdownsMaster.' . $this->DropdownsMaster->primaryKey => $id,
                ),
            );

        $DropdownsMaster = $this->DropdownsMaster->find('first', $options);

        if (isset($this->request->data['DropdownsMastersData']) || $this->request->is('put')) {

            $this->request->data['DropdownsMastersData']['dropdowns_masters_id'] = $DropdownsMaster['DropdownsMaster']['id'];

            $this->DropdownsMaster->DropdownsMastersData->create();
            $this->DropdownsMaster->DropdownsMastersData->save($this->request->data);

            $this->Flash->success(__('Value was saved'), array('key' => 'success'));

            $FormName               = array();
            $FormName['controller'] = $this->Session->read('lastArrayURL.controller');
            $FormName['action']     = $this->Session->read('lastArrayURL.action');
            $FormName['terms']      = implode('/', $this->request->projectvars['VarsArray']);

            $this->set('FormName', $FormName);

            $DropdownsMaster['data_area'] = 'data_area';

        } else {

            $DropdownsMaster['DropdownsMastersData']['status']              = 0;
            $DropdownsMaster['DropdownsMastersData']['action_after_saving'] = 0;

        }

        $parms = array(0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,$id,0,0);

		$EditLinkContainer[] = array(
			'class' => '',
            'link_id' => 'edit_master_dropdown',
			'link_class' => 'round edit_master_dropdown',
			'parms' => $parms,
			'controller' => 'dropdowns',
			'action' => 'masteredit',
			'discription' => __('Edit Dropdownfield',true) . ' ' . $DropdownsMaster['DropdownsMaster']['name']
		);

		$EditLinkContainer = $this->Autorisierung->AclCheckLinks($EditLinkContainer);

        $this->request->data = $DropdownsMaster;

        $this->set('EditLinkContainer', $EditLinkContainer);
        $this->set('SettingsArray', $SettingsArray);
    }
}