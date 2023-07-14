<?php
App::uses('AppController', 'Controller');
/**
 * Dropdowns Controller
 *
 * @property Dropdown $Dropdown
 */
class SpecialcharactersController extends AppController
{

    public $components = array('Auth', 'Acl', 'Autorisierung', 'Cookie', 'Navigation', 'Lang', 'Sicherheit', 'Xml', 'Data', 'RequestHandler');
    public $helpers = array('Lang', 'Navigation', 'JqueryScripte', 'ViewData');
    public $layout = 'modal';

    public function beforeFilter()
    {

        App::import('Vendor', 'Authorize');
        // Es wird das in der Auswahl gewählte Project in einer Session gespeichert

        $this->Navigation->ReportVars();

        if ($this->request->params['action'] == 'index' && $this->request->params['controller'] == 'orders') {
            $this->Session->write('projectURL', $this->request->projectID);
        }

        $this->Autorisierung->ConditionsTopprojectsTest($this->request->projectID);

        $this->loadModel('User');
        $this->loadModel('Topproject');
        $this->loadModel('Testingmethod');
        $this->Autorisierung->Protect();

        $this->Navigation->ajaxURL();
        $this->Lang->Choice();
        $this->Lang->Change();
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

    public function afterFilter()
    {
        $this->Navigation->lastURL();
    }

    public function index()
    {
        $this->layout = 'modal';

        if (isset($this->request->data['thisID'])) {
            $thisID = $this->request->data['thisID'];
        }

        if (isset($this->request->data['type'])) {
            $thisID = $this->request->data['type'];
        }

        $field = '';

        $Specialcharacter = $this->Specialcharacter->find('all', array(
            'conditions' => array(
                'testingcomp_id' => $this->Autorisierung->Conditions('TestingcompsTopprojects', 'testingcomp_id'),
            ),
        )
        );

        $SettingsArray['addlink'] = array('discription' => __('Add', true), 'controller' => 'specialcharacters', 'action' => 'add', 'terms' => $this->request->projectvars['VarsArray']);
        $SettingsArray['editlink'] = array('discription' => __('Edit', true), 'controller' => 'specialcharacters', 'action' => 'listing', 'terms' => $this->request->projectvars['VarsArray']);

        $this->set('SettingsArray', $SettingsArray);
        $this->set('Specialcharacter', $Specialcharacter);
        $this->set('field', $field);
    }

    public function listing()
    {

        $this->layout = 'modal';

        $Specialcharacter = $this->Specialcharacter->find('all', array('conditions' => array('testingcomp_id' => $this->Autorisierung->Conditions('TestingcompsTopprojects', 'testingcomp_id'))));

        $SettingsArray['addlink'] = array('discription' => __('Add', true), 'controller' => 'specialcharacters', 'action' => 'add', 'terms' => $this->request->projectvars['VarsArray']);

        $SettingsArray['backlink'] = array('discription' => __('Back', true), 'controller' => 'specialcharacters', 'action' => 'index', 'terms' => $this->request->projectvars['VarsArray']);

        $this->set('Model', 'Specialcharacter');
        $this->set('SettingsArray', $SettingsArray);
        $this->set('Specialcharacter', $Specialcharacter);
    }

    public function edit()
    {

        $this->layout = 'modal';

        $id = $this->request->params['pass'][15];

        if (isset($this->request->data['Specialcharacter']) && $this->request->is('put')) {

            $this->request->data['Specialcharacter']['testingcomp_id'] = $this->Auth->user('testingcomp_id');

            if ($this->Specialcharacter->save($this->request->data)) {

                $this->Autorisierung->Logger($id, $this->request->data);

                $this->Flash->success(__('The Specialcharacter has been saved', true), array('key' => 'success'));

            } else {
                $this->Flash->error(__('The Specialcharacter could not be saved. Please, try again.', true), array('key' => 'error'));
            }
        }

        $options = array('conditions' => array(
            'Specialcharacter.' . $this->Specialcharacter->primaryKey => $id,
            'Specialcharacter.testingcomp_id' => $this->Autorisierung->Conditions('TestingcompsTopprojects', 'testingcomp_id'),
        ),
        );

        $this->request->data = $this->Specialcharacter->find('first', $options);

        $SettingsArray['backlink'] = array('discription' => __('Back', true), 'controller' => 'specialcharacters', 'action' => 'listing', 'terms' => $this->request->projectvars['VarsArray']);
        $SettingsArray['addlink'] = array('discription' => __('Add', true), 'controller' => 'specialcharacters', 'action' => 'add', 'terms' => $this->request->projectvars['VarsArray']);

        $this->set('SettingsArray', $SettingsArray);

    }

    public function add()
    {

        $this->layout = 'modal';

        if (isset($this->request->data['Specialcharacter'])) {
//            $this->Autorisierung->ConditionsTest('TestingcompsTopprojects','testingcomp_id',$this->request->data['Specialcharacter']['testingcomp_id']);
            $this->Specialcharacter->create();

            $this->request->data['Specialcharacter']['testingcomp_id'] = $this->Auth->user('testingcomp_id');

            if ($this->Specialcharacter->save($this->request->data)) {

                $id = $this->Specialcharacter->getInsertID();
                $this->Autorisierung->Logger($id, $this->request->data);

                $options = array('conditions' => array(
                    'Specialcharacter.' . $this->Specialcharacter->primaryKey => $id,
                    'Specialcharacter.testingcomp_id' => $this->Autorisierung->Conditions('TestingcompsTopprojects', 'testingcomp_id'),
                ),
                );

                $this->request->data = $this->Specialcharacter->find('first', $options);

                $this->Flash->success(__('The Specialcharacter has been saved', true), array('key' => 'success'));
            } else {
                $this->Flash->error(__('The Specialcharacter could not be saved. Please, try again.', true), array('key' => 'error'));
            }
        }

        $SettingsArray['backlink'] = array('discription' => __('Back', true), 'controller' => 'specialcharacters', 'action' => 'index', 'terms' => $this->request->projectvars['VarsArray']);
        $SettingsArray['editlink'] = array('discription' => __('Edit', true), 'controller' => 'specialcharacters', 'action' => 'listing', 'terms' => $this->request->projectvars['VarsArray']);
        $SettingsArray['addlink'] = array('discription' => __('Add', true), 'controller' => 'specialcharacters', 'action' => 'add', 'terms' => $this->request->projectvars['VarsArray']);

        $this->set('SettingsArray', $SettingsArray);

    }

    public function delete()
    {

        $this->layout = 'modal';

        $id = $this->request->params['pass'][15];

        $this->Specialcharacter->id = $id;

        if (!$this->Specialcharacter->exists()) {
            throw new NotFoundException(__('Invalid data'));
        }

        $options = array('conditions' => array(
            'Specialcharacter.' . $this->Specialcharacter->primaryKey => $id,
            'Specialcharacter.testingcomp_id' => $this->Autorisierung->Conditions('TestingcompsTopprojects', 'testingcomp_id'),
        ),
        );

        $Specialcharacter = $this->Specialcharacter->find('first', $options);
        $this->request->data = $Specialcharacter;

        if (isset($this->request->data['Specialcharacter']) && $this->request->is('put')) {

            if ($this->Specialcharacter->delete()) {

                $this->Autorisierung->Logger($id, $Specialcharacter);

                $this->Flash->success(__('Value was deleted', true), array('key' => 'success'));

            } else {

                $this->Flash->error(__('Evalution was not deleted', true), array('key' => 'error'));

            }

        } else {

            $this->Flash->warning(__('Delete Specialcharacter', true) . ' ' . $Specialcharacter['Specialcharacter']['discription'] . ' (' . $Specialcharacter['Specialcharacter']['value'] . ')', array('key' => 'warning'));

        }

        $SettingsArray = array();
        $SettingsArray['backlink'] = array('discription' => __('Back', true), 'controller' => 'specialcharacters', 'action' => 'listing', 'terms' => $this->request->projectvars['VarsArray']);
        $SettingsArray['addlink'] = array('discription' => __('Add', true), 'controller' => 'specialcharacters', 'action' => 'add', 'terms' => $this->request->projectvars['VarsArray']);

        $this->set('SettingsArray', $SettingsArray);
    }
}
