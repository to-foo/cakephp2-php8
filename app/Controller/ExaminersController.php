<?php
App::uses('AppController', 'Controller');
App::uses('ConnectionManager', 'Model');
/**
 * Examiners Controller
 *
 * @property Examiner $Examiner
 * @property AutorisierungComponent $Autorisierung
 * @property LangComponent $Lang
 * @property NavigationComponent $Navigation
 * @property SicherheitComponent $Sicherheit
 */
class ExaminersController extends AppController
{
    public $components = array('Qualification', 'Data', 'Session', 'Auth', 'Acl', 'Autorisierung', 'Image', 'Cookie', 'Navigation', 'Lang', 'Sicherheit', 'SelectValue', 'Paginator', 'Xml', 'Drops');
    protected $_writeprotection = false;

    public $helpers = array('Lang', 'Navigation', 'JqueryScripte', 'Pdf', 'Quality');
    public $layout = 'ajax';
    public $uses = array('Examiner', 'ExaminerTime');

    public function validateDate($date)
    {
        if (!is_string($date)) {
            return false;
        }
        $d = DateTime::createFromFormat('Y-m-d', $date);
        return $d && $d->format('Y-m-d') == $date;
    }

    public function beforeFilter()
    {
        $abort = true;
        $abort &= !Configure::read('CertifcateManager');
        $abort &= !Configure::read('WorkloadManager');

        if ($abort) {
            die();
        }

        if ($this->request->params['action'] == 'testgraph') {
            return;
        }
        App::import('Vendor', 'Authorize');
        $this->loadModel('User');
        $this->Autorisierung->Protect();

        $noAjaxIs = 0;
        $noAjax = array('printworkload_xxx', 'certificatesfiles', 'certificatefile', 'getfiles', 'getcertificatefiles', 'getmonitoringfile', 'monitoringfile', 'getcertificatefile', 'eyecheckfile', 'eyecheckfiles', 'geteyecheckfile', 'geteyecheckfiles', 'pdf', 'pdfinv', 'files', 'quicksearch', 'email', 'autocomplete');

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

        App::uses('Folder', 'Utility');
        App::uses('File', 'Utility');

        $this->Lang->Choice();
        $this->Lang->Change();
        $this->Navigation->ReportVars();

        $this->Examiner->Testingcomp->Topproject->Report->Reportlock->recursive = -1;
        $this->writeprotection = 0 < $this->Examiner->Testingcomp->Topproject->Report->Reportlock->find('count', array('conditions' => array('Reportlock.topproject_id' => $this->request->projectID, 'Reportlock.report_id' => $this->request->reportID)));
        $this->set('writeprotection', $this->writeprotection);

        $this->set('lang', $this->Lang->Choice());
        $this->set('selected', $this->Lang->Selected());
        $this->set('menues', $this->Navigation->Menue());
        //        $this->set('breadcrumbs', $this->Navigation->Breadcrumb());
        $this->set('login_info', $this->Navigation->loggedUser());
        $this->set('lang_choise', $this->Lang->Choice());
        $this->set('lang_discription', $this->Lang->Discription());
        $this->set('previous_url', $this->base . '/' . $this->Session->read('lastURL'));
        $this->set('locale', $this->Lang->Discription());

        // Werte für die Schnellsuche
        $ControllerQuickSearch =
        array(
            'Model' => 'Examiner',
            'Field' => 'name',
            'description' => __('Examiner name', true),
            'minLength' => 2,
            'targetcontroller' => 'examiners',
            'target_id' => 'examiner_id',
            'targetation' => 'overview',
            'Quicksearch' => 'QuickExaminersearch',
            'QuicksearchForm' => 'QuickExaminersearchForm',
            'QuicksearchSearchingAutocomplet' => 'QuickExaminersearchSearchingAutocomplet',
        );

        $this->set('ControllerQuickSearch', $ControllerQuickSearch);

        $ExaminerManagerAutocomplete = null;

        if (Configure::check('ExamineManagerSearchAutocomplete') && Configure::read('ExamineManagerSearchAutocomplete') == true) {
            $ExaminerManagerAutocomplete =
            array(
                'Model' => 'Examiner',
            );
        }

        $this->request->data['ExaminerManagerAutocomplete'] = $ExaminerManagerAutocomplete;

        if (isset($this->Auth)) {
            $this->set('authUser', $this->Auth);
        }

        $this->set('SettingsArray', array());
    }

    public function afterFilter()
    {
        $this->Navigation->lastURL();
    }

    public function quicksearch()
    {
        App::uses('Sanitize', 'Utility');

        $this->_quicksearch();
    }

    protected function _quicksearch()
    {

//        $this->autoRender = false;
        $this->layout = 'json';

        $term = Sanitize::escape($this->request->data['term']);
        $model = 'Examiner';
        $field = 'name';

        $current_roll_id = $this->Auth->user('roll_id');

        if ($current_roll_id < 2) {
            $conditions = array(
                $model . '.' . $field . ' LIKE' => '%' . $term . '%',
                $model . '.deleted' => 0,
            );
        } else {
            $this->loadModel('ExaminerTestingcomp');
            $testingcomps = $this->ExaminerTestingcomp->find('list', array('conditions' => array(
                'ExaminerTestingcomp.testingcomp_id' => $this->Auth->user('testingcomp_id'),
            ), 'fields' => array('id', 'examiner_id')));
            $options['Examiner.id'] = $testingcomps;

            $conditions = array(
                $model . '.' . $field . ' LIKE' => '%' . $term . '%',
                $model . '.deleted' => 0,
                $model . '.id' => $testingcomps,
            );
        }

/*
$conditions = array(
$model.'.deleted' => 0,
'OR' => array(
array(
$model.'.name LIKE' => '%'.$term.'%',
$model.'.first_name LIKE' => '%'.$term.'%',
),
)
);
 */
        if (isset($this->request->data['Conditions'])) {
            foreach ($this->request->data['Conditions'] as $_key => $_conditions) {
                $conditions[$model . '.' . Sanitize::escape($_key)] = Sanitize::escape($_conditions);
            }
        }

        if (!is_object($model)) {
            $this->loadModel($model);
        }

        $options = array(
            'fields' => array('id', 'name', 'first_name'),
            'limit' => 5,
            'conditions' => $conditions,
        );

        $this->$model->recursive = -1;
        $data = $this->$model->find('all', $options);

        $response = array();

        foreach ($data as $_key => $_data) {
            array_push($response, array('key' => $_data[$model]['id'], 'value' => $_data[$model]['name'] . ' ' . $_data[$model]['first_name']));
        }

        $this->set('response', json_encode($response));
    }

    public function save($id = null)
    {
        // Die IDs des Projektes und des Auftrages werden getestet

        $examiner_id = $this->Sicherheit->Numeric($this->request->params['pass']['15']);

        $this->layout = 'blank';

        if (key($this->request->data['orginal_data']) == 'Examiner') {
            $examiner_id = $this->Sicherheit->Numeric($this->request->params['pass']['15']);
            $id = $this->Sicherheit->Numeric($this->request->params['pass']['15']);

            if (!$this->Examiner->exists($examiner_id)) {
                throw new NotFoundException(__('Invalid examiner'));
            }

            $Model = key($this->request->data['orginal_data']);
        }

        if (key($this->request->data['orginal_data']) == 'Certificate') {
            $certificate_id = $this->Sicherheit->Numeric($this->request->params['pass']['16']);
            $id = $this->Sicherheit->Numeric($this->request->params['pass']['16']);

            if (!$this->Examiner->Certificate->exists($certificate_id)) {
                throw new NotFoundException(__('Invalid certificate'));
            }

            $Model = key($this->request->data['orginal_data']);
            $this->loadModel($Model);
        }

        if (key($this->request->data['orginal_data']) == 'Eyecheck') {
            $certificate_id = $this->Sicherheit->Numeric($this->request->params['pass']['16']);
            $id = $this->Sicherheit->Numeric($this->request->params['pass']['16']);

            if (!$this->Examiner->Eyecheck->exists($certificate_id)) {
                throw new NotFoundException(__('Invalid vision test'));
            }

            $Model = key($this->request->data['orginal_data']);
            $this->loadModel($Model);
        }

        $this_id = $this->request->data['this_id'];
        $orginal_data = $this->request->data['orginal_data'];
        unset($this->request->data['orginal_data']);

        foreach ($this->request->data as $_key => $_data) {
            if (!is_array($_data)) {
                unset($this->request->data[$_key]);
            }
        }

        $orginalData = $this->$Model->find('first', array('conditions' => array($Model . '.id' => $id)));

        $field_key = key($this->request->data[$Model]);

        if ($field_key == '0') {
            $field_key = key($this->request->data[$Model][0]);
            $this_value = $this->request->data[$Model][0][$field_key];
        } else {
            $this_value = $this->request->data[$Model][$field_key];
        }

        $last_value = $orginalData[$Model][$field_key];
        $this_field = key($this->request->data[$Model]);
        $last_id_for_radio = $Model . Inflector::camelize($field_key);

        $this->set('this_id', $this_id);
        $this->set('last_value', $last_value);
        $this->set('this_value', $this_value);
        $this->set('last_id_for_radio', $last_id_for_radio);

        if ($orginalData[$Model]['active'] == 0 && $this_field != 'active') {
            $this->set('closed', __('The value could not be saved. The record is deactive!'));
            $this->render();
            return;
        }

        if ($orginalData[$Model]['deleted'] == 1) {
            $this->set('closed', __('The value could not be saved. The record is deleted!'));
            $this->render();
            return;
        }

        $option = array(
            'id' => $id,
            $field_key => $this_value,
        );

        if ($Model == 'Certificate') {

            // Qulifikationen mit diesen Werten dürfen nicht doppelt vorhanden sein
            if (isset($option['level']) || isset($option['testingmethod']) || isset($option['sector'])) {
                $this->$Model->recursive = -1;
                $orginal_data = $this->$Model->find(
                    'first',
                    array(
                        'fields' => array('id', 'sector', 'level', 'testingmethod', 'examiner_id'),
                        'conditions' => array(
                            $Model . '.id' => $id,
                        ),
                    )
                );

                $orginal_data_test_option = array_merge($orginal_data['Certificate'], $option);
                $orginal_data_test_option['deleted'] = 0;
                unset($orginal_data_test_option['id']);
                $orginal_data_test = $this->$Model->find('first', array('conditions' => $orginal_data_test_option));

                if (count($orginal_data_test) > 0) {
                    $this->set('closed', __('The value could not be saved. There is a qualification with the following values: ' . $orginal_data_test['Certificate']['sector'] . '/' . $orginal_data_test['Certificate']['testingmethod'] . '/' . $orginal_data_test['Certificate']['level']));
                    $this->render();
                    return;
                }
            }
        }

        if ($this->$Model->save($option)) {

            // Falls eine Qualifikation mit geringerem Level vorhanden ist,
            // wird diese deaktiviert
            if ($Model == 'Certificate' && isset($option['level'])) {
                unset($orginal_data_test_option['level']);
                $orginal_data_test_option['id !='] = $id;
                $orginal_data_test_option['level <'] = $option['level'];
                $orginal_data_test = $this->$Model->find(
                    'all',
                    array(
                        'fields' => array('id', 'sector', 'testingmethod', 'level'),
                        'conditions' => $orginal_data_test_option,
                    )
                );

                $hint_quilification = null;
                if (count($orginal_data_test) > 0) {
                    foreach ($orginal_data_test as $_orginal_data_test) {
                        $_orginal_data_test['Certificate']['active'] = 0;
                        $this->Certificate->CertificateData->updateAll(array('active' => 0), array('certificate_id' => $_orginal_data_test['Certificate']['id']));
                        $hint_quilification .= $_orginal_data_test['Certificate']['sector'] . '/' . $_orginal_data_test['Certificate']['testingmethod'] . '/' . $_orginal_data_test['Certificate']['level'];
                    }

                    $this->set('hint', __('The Qualification with lower level has been disabled' . ': ' . $hint_quilification));
                }
            }

            $this->Autorisierung->Logger($id, $this->request->data[$Model]);
            $this->set('success', __('The value has been saved.'));
        } else {
            $this->set('error', __('The value could not be saved. Please try again!'));
        }
    }

    public function testgraph()
    {
        $this->layout = 'jpgraph';

        $this->loadModel('ExaminerTime');
        $this->ExaminerTime->recursive = -1;
        $this->set('examinerTimes', $this->ExaminerTime->find('all', array('conditions' => array('ExaminerTime.examiner_id' => 14))));
        $this->render('list_examiner_workload_month_gantt');
    }
    /**
     * index method
     *
     * @return void
     */
    public function index()
    {
        $this->_include_index();
    }

    private function _getTestingcompsDropdown()
    {
        $current_roll_id = $this->Auth->user('roll_id');

        if ($current_roll_id < 2) {
            $testingcomps = $this->Examiner->Testingcomp->find('list', array('conditions' => array('Testingcomp.id' => $this->Autorisierung->ConditionsTestinccomps())));
        } else {
            $testingcomps = $this->Examiner->Testingcomp->find('list', array('conditions' => array(
                'Testingcomp.id' => $this->Auth->user('testingcomp_id'),
            ), 'fields' => array('id', 'name')));
        }
        return $testingcomps;
    }

    protected function _include_index()
    {
        if (isset($this->request->params['named']['origin'])) {
            if ($this->request->params['named']['origin'] == 'topprojects') {
                $this->Navigation->RestoreAllExaminerSessions();
            }
        }

 //       $this->Session->delete('ConditionsExaminer');
        $this->Examiner->recursive = 1;
        $this->Session->delete('searchexaminer');

        if (isset($this->request->data['Examiner']['ExaminerTestingcomp']) && !empty($this->request->data['Examiner']['ExaminerTestingcomp'])) {
            $sort_examiner_table_backup = array();
            if ($this->Session->check('SortExaminerTable')) {
                if (!empty($this->Session->read('SortExaminerTable'))) {
                    $sort_examiner_table_backup = $this->Session->read('SortExaminerTable');
                }
            }
            $this->Session->write('SortExaminerTable', $sort_examiner_table_backup);
            $this->Session->write('Examiner.testingcomp', $this->request->data['Examiner']['ExaminerTestingcomp']);
            $this->Navigation->ResetSessionForPaging();
            $new_testingcomp_selected = true;
        } else {
            if (isset($this->request->data['Examiner']['ExaminerTestingcomp']) && empty($this->request->data['Examiner']['ExaminerTestingcomp'])) {
                $this->Session->delete('Examiner.testingcomp');
                $this->Navigation->ResetSessionForPaging();
            } else {
                $new_page_selected = true;
                $this->Navigation->GetSessionForPaging();
            }
        }

        if (isset($this->request->data['landig_page']) && $this->request->data['landig_page'] == 1) {
            $landig_page = 1;
        }

        if (isset($this->request->data['landig_page_large']) && $this->request->data['landig_page_large'] == 1) {
            $landig_page_large = 1;
        }

        $xml = $this->Xml->DatafromXml('display_examiner', 'file', null);
        if (!is_object($xml['settings'])) {
            pr('Einstellungen konnten nicht geladen werden.');
            return;
        } else {
            $this->set('xml', $xml['settings']);
        }

        $SettingsArray = array();
        $summary = array();

        if (isset($new_testingcomp_selected) && !$new_testingcomp_selected) {
            $order = $this->Data->SortExaminerTable();
        } else {
            if (isset($new_page_selected) && !$new_page_selected) {
                $order = $this->Session->read('SortExaminerTable');
            } else {
                $order = $this->Data->SortExaminerTable();
                if (isset($new_testingcomp_selected) && $new_testingcomp_selected) {
                    if ($this->Session->check('SortExaminerTable')) {
                        $order = $this->Session->read('SortExaminerTable');
                    }
                }
            }
        }

        $options = $this->Data->ConditionsExaminer();

        if (isset($this->request->data['landig_page_large']) && $this->request->data['landig_page_large'] == 1) {
            $limit = 1000000000;
        } else {
            $limit = 10;
        }

        $this->paginate = array(
            'conditions' => array($options),
            'order' => $order,
            'limit' => $limit,
        );

        if (!isset($this->_controller->request->query['is_infinite'])) {
            $this->Session->write('SortExaminerTable', $order);
        }

        $examiner = $this->paginate('Examiner');

        foreach ($examiner as $_key => $_examiner) {
            $examiner_link = $this->request->projectvars['VarsArray'];
            $summary[$_examiner['Examiner']['id']]['summary'] = $this->Qualification->CertificateSummary($this->Qualification->CertificatesSectors($_examiner), array('main' => 'Certificate', 'sub' => 'CertificateData'));
            $summary[$_examiner['Examiner']['id']]['eyecheck'] = $this->Qualification->EyecheckSummary($this->Qualification->Eyechecks($_examiner), array('main' => 'Eyecheck', 'sub' => 'EyecheckData'));
            $examiner_link[13] = 0;
            $examiner_link[14] = 0;
            $examiner_link[15] = $_examiner['Examiner']['id'];

            unset($examiner_link[16]);
            unset($examiner_link[17]);

            foreach ($summary[$_examiner['Examiner']['id']]['summary']['qualifications'] as $__key => $__qualifications) {
                $examiner[$_key]['Examiner']['_examiner_qualification'][] = array(
                    'certificate' => $__qualifications['certificat'],
                    'qualification' => $__qualifications,
                    'term' => $__qualifications['term'],
                    'termlink' => 'examiner/certificate/' . implode('/', $__qualifications['term']),
                    'certificate_id' => $__qualifications['certificate_id'],
                    'class' => $__qualifications['class'],
                );
            }

            $examiner[$_key]['Examiner']['_examiner_link'] = $examiner_link;

            // alle Zertifikatsnummer sammeln
            // Foth
            $all_certificats_no = array();
            if (isset($examiner[$_key]['Examiner']['_examiner_qualification'])) {
                foreach ($examiner[$_key]['Examiner']['_examiner_qualification'] as $__key => $_examiner_qualification) {
                    if ($_examiner_qualification['certificate'] == '-') {
                        continue;
                    } else {
                        $all_certificats_no[] = $_examiner_qualification['certificate'];
                    }
                }

                $all_certificats_no = array_unique($all_certificats_no);
            }

            if (count($all_certificats_no) > 0) {
                $examiner[$_key]['Certificate']['certificat'] = implode('/', $all_certificats_no);
            } else {
                $examiner[$_key]['Certificate']['certificat'] = '-';
            }
        }

        $testingcomps_dropdown = $this->_getTestingCompsDropdown();

        $breads = $this->Navigation->Breads(null);

        $breads[1]['discription'] = __('Examiners', true);
        $breads[1]['controller'] = 'examiners';
        $breads[1]['action'] = 'index';
        $breads[1]['pass'] = null;

        $monitorings = array();
        if (Configure::read('DeviceManager') == true) {
            $SettingsArray['devicelink'] = array('discription' => __('Devices administration', true), 'controller' => 'devices', 'action' => 'index', 'terms' => null);
        }

        if (Configure::read('DocumentManager') == true) {
            $SettingsArray['documentlink'] = array('discription' => __('Document administration', true), 'controller' => 'documents', 'action' => 'index', 'terms' => null);
        }

        if (Configure::read('search.examiner') == true) {
            //            $SettingsArray['addsearching'] = array('discription' => __('Search examiner', true), 'controller' => 'examiners', 'action'=>'search', 'terms'=>null, 'rel'=>'examiners');
        }
        foreach ($examiner as $_key => $_examiners) {
            $monitorings[$_examiners['Examiner']['id']] = $this->Qualification->ExaminerMonitoringSummary($_examiners, array('top' => 'Examiner', 'main' => 'ExaminerMonitoring', 'sub' => 'ExaminerMonitoringData'));
        }

        if (isset($monitorings)) {
            $this->set('monitorings', $monitorings);
        }

        $this->set('TestingcompsDropdown', $testingcomps_dropdown);
        $this->set('SettingsArray', $SettingsArray);
        $this->set('breads', $breads);
        $this->set('examiners', $examiner);
        $this->set('summary', $summary);
        if (!empty($this->Session->read('Examiner.testingcomp'))) {
            $this->set('LastSelectedExaminerTestingcomp', $this->Session->read('Examiner.testingcomp'));
        }

        if (isset($landig_page) && $landig_page == 1) {

            $this->render('landingpage', 'blank');
            return;

        }

        if (isset($landig_page_large) && $landig_page_large == 1) {

            $this->render('landingpage_large', 'blank');
            return;

        }

        $this->Navigation->SetTableAnchor();
        $this->Navigation->SetSessionForPaging();
        $this->Navigation->InfiniteScrollPaging('index_infinite');
    }

    public function email_eyecheck()
    {
        $this->layout = 'modal';

        $message = null;
        $message .= __('A summary of eye check information will be sent to the current email address.', true);
        $message .= '<br>';
        $message .= __('Deactivate checkbox of a messages to remove.', true);

        $this->set('message', $message);

        $_ids = array();

        if (isset($this->request->data['Examiner']) && ($this->request->is('post') || $this->request->is('put'))) {
            $emailto = $this->request->data['Examiner']['email'];
            $isValid = Validation::email($emailto);

            if ($isValid === true) {
                $_id = array();
                foreach ($this->request->data['Examiner'] as $_key => $_data) {
                    if (is_numeric($_key)) {
                        if ($_data == 1) {
                            $_id[] = $_key;
                        }
                    }
                }

                $_ids = array('EyecheckData.id' => $_id);
            }
        }

        $certificates = array();

        $eyechecks_options = array(
            'order' => array('EyecheckData.id DESC'),
            'conditions' => array(
                'Examiner.testingcomp_id' => $this->Autorisierung->ConditionsTestinccomps(),
                'Examiner.deleted' => 0,
                $_ids,
            ),
        );

        $eyechecks = $this->Examiner->Eyecheck->EyecheckData->find('all', $eyechecks_options);

        $summary = array(
            'errors' => array(),
            'warnings' => array(),
            'futurenow' => array(),
            'future' => array(),
            'hints' => array(),
            'deactive' => array(),
        );

        $summary_desc = array(
            'futurenow' => array(
                0 => __('First-time eyecheck reached', true),
                1 => __('First-time eyecheck reached', true),
            ),

            'future' => array(
                0 => __('First-time eyecheck', true),
                1 => __('First-time eyecheck', true),
            ),
            'errors' => array(
                0 => __('Irregularity', true),
                1 => __('Irregularities', true),
            ),
            'warnings' => array(
                0 => __('Warning', true),
                1 => __('Warnings', true),
            ),
            'hints' => array(
                0 => __('Hint', true),
                1 => __('Hints', true),
            ),
            'deactive' => array(
                0 => __('Deactive', true),
                1 => __('Deactive', true),
            ),
        );

        foreach ($eyechecks as $_key => $_certificates) {
            $__certificates = $this->Qualification->Eyechecks($_certificates);
            $___certificates = $this->Qualification->EyecheckSummary($__certificates, array('main' => 'Eyecheck', 'sub' => 'EyecheckData'));

            $certificates[$_certificates['Examiner']['id']]['summary'] = $___certificates;

            // Nach Fehler/Hinweisarten sortieren
            foreach ($summary as $__key => $_summary) {
                if (isset($___certificates['summary'][$__key]) && count($___certificates['summary'][$__key]) > 0) {
                    $summaryeyecheck[$__key][$_certificates['Examiner']['id']] = $___certificates['summary'][$__key];
                }
            }
        }

        $this->_email($summaryeyecheck, $summary_desc, 'summary_eyecheck', 'Sehtestinfos');
    }

    public function email_certificate()
    {
        $this->layout = 'modal';

        $message = null;
        $message .= __('A summary of certificat information will be sent to the current email address.', true);
        $message .= '<br>';
        $message .= __('Deactivate checkbox of a messages to remove.', true);

        $this->set('message', $message);

        $_ids = array();

        if (isset($this->request->data['Examiner']) && ($this->request->is('post') || $this->request->is('put'))) {
            $emailto = $this->request->data['Examiner']['email'];
            $isValid = Validation::email($emailto);

            if ($isValid === true) {
                $_id = array();
                foreach ($this->request->data['Examiner'] as $_key => $_data) {
                    if (is_numeric($_key)) {
                        if ($_data == 1) {
                            $_id[] = $_key;
                        }
                    }
                }

                $_ids = array('CertificateData.id' => $_id);
            }
        }

        $certificates_options = array(
            'order' => array('CertificateData.id DESC'),
            'conditions' => array(
                'Examiner.testingcomp_id' => $this->Autorisierung->ConditionsTestinccomps(),
                'Examiner.deleted' => 0,
                'CertificateData.active' => 1,
                'CertificateData.deleted' => 0,
                $_ids,
            ),
        );

        $certificate = $this->Examiner->Certificate->CertificateData->find('all', $certificates_options);

        $certficates = array();

        $summary = array(
            'errors' => array(),
            'warnings' => array(),
            'futurenow' => array(),
            'future' => array(),
            'hints' => array(),
            'deactive' => array(),
        );

        $summary_desc = array(
            'futurenow' => array(
                0 => __('First-time certification reached', true),
                1 => __('First-time certifications reached', true),
            ),

            'future' => array(
                0 => __('First-time certification', true),
                1 => __('First-time certifications', true),
            ),
            'errors' => array(
                0 => __('Irregularity', true),
                1 => __('Irregularities', true),
            ),
            'warnings' => array(
                0 => __('Warning', true),
                1 => __('Warnings', true),
            ),
            'hints' => array(
                0 => __('Hint', true),
                1 => __('Hints', true),
            ),
            'deactive' => array(
                0 => __('Deactive', true),
                1 => __('Deactive', true),
            ),
        );

        foreach ($certificate as $_key => $_certificates) {
            $__certificates = $this->Qualification->CertificatesSectors($_certificates);
            $___certificates = $this->Qualification->CertificateSummary($__certificates, array('main' => 'Certificate', 'sub' => 'CertificateData'));

            $certificates[$_certificates['Examiner']['id']]['summary'] = $___certificates;

            // Nach Fehler/Hinweisarten sortieren
            foreach ($summary as $__key => $_summary) {
                if (isset($___certificates['summary'][$__key]) && count($___certificates['summary'][$__key]) > 0) {
                    $summary[$__key][$_certificates['Examiner']['id']] = $___certificates['summary'][$__key];
                }
            }
        }
        $this->_email($summary, $summary_desc, 'summary_certificat', 'Zertifizierungsinfos');
    }

    protected function _email($summary, $summary_desc, $mailview, $subject)
    {
        $lastModalURLArray['controller'] = 'examiners';
        $lastModalURLArray['action'] = 'index';
        $lastModalURLArray['pass'] = array();
        $lastModal = $this->Session->read('lastModalURLArray');

        if (is_array($lastModal) && count($lastModal) == 3) {
            $lastModalURLArray['controller'] = $this->Session->read('lastModalURLArray.controller');
            $lastModalURLArray['action'] = $this->Session->read('lastModalURLArray.action');
            $lastModalURLArray['pass'] = $this->Session->read('lastModalURLArray.pass');
        }

        $SettingsArray = array();

        $this->set('lastModalURLArray', $lastModalURLArray);
        $this->set('summary_desc', $summary_desc);
        $this->set('summary', $summary);
        $this->set('SettingsArray', $SettingsArray);

        if (isset($this->request->data['Examiner']) && ($this->request->is('post') || $this->request->is('put'))) {
            $emailto = $this->request->data['Examiner']['email'];
            $isValid = Validation::email($emailto);
            $url['controller'] = $this->request->data['url']['controller'];
            $url['action'] = $this->request->data['url']['action'];
            $url['pass'] = $this->request->data['url']['pass'];

            if (!isset($this->request->data['url']['pass'])) {
                $url['pass'] = array();
            }

            if ($isValid === true) {
                App::uses('CakeEmail', 'Network/Email');

                $commentar = null;

                if ($this->request->data['Examiner']['comment'] != '') {
                    App::uses('Sanitize', 'Utility');
                    $commentar = Sanitize::stripScripts($this->request->data['Examiner']['comment']);
                    $this->set('commentar', $commentar);
                }

                $email = new CakeEmail('default');
                $email->domain(FULL_BASE_URL);
                $email->from(array('reminder@qm-systems.info' => 'MPS Prüfberichtsdatenbank'));
                $email->sender(array('reminder@qm-systems.info' => 'MPS Prüfberichtsdatenbank'));
                $email->to($emailto);
                $email->subject($subject);
                $email->template($mailview, 'summary');
                $email->emailFormat('both');
                $email->viewVars(
                    array(
                        'summary_desc' => $summary_desc,
                        'summary' => $summary,
                        'commentar' => $commentar,
                    )
                );
                $email->send();

                $this->set('lastModalURLArray', $url);
                $this->set('message', __('E-Mail versendet an ' . $emailto, true));
                $this->render('email_successful_send', 'modal');
            } elseif ($isValid === false) {
                $this->set('message', __('E-Mail konte nicht versendet werden. Sie haben keine gültige E-Mail-Adresse angegeben.', true));
                $this->set('lastModalURLArray', $url);
            }
        }
    }

    public function single_eyecheck_summary($id)
    {
        $examiner_id = $id;

        $this->_singlesummary($examiner_id);
    }

    public function singlesummary($examiner_id)
    {
        $this->layout = 'modalsummary';

        if (!$this->Examiner->exists($examiner_id)) {
            throw new NotFoundException(__('Invalid examiner'));
        }

        $arrayData = $this->Xml->DatafromXml('Examiner', 'file', null);
        $examiner = $this->Examiner->find('first', array('conditions' => array('Examiner.id' => $examiner_id)));

        $certificates = $this->Qualification->CertificatesSectors($examiner);
        $summary = $this->Qualification->CertificateSummary($certificates, array('main' => 'Certificate', 'sub' => 'CertificateData'));

        $eyechecks = $this->Qualification->Eyechecks($examiner);
        $eyechecksummary = $this->Qualification->EyecheckSummary($eyechecks, array('main' => 'Eyecheck', 'sub' => 'EyecheckData'));

        $this->set('examiner', $examiner);
        $this->set('eyechecks', $eyechecks);
        $this->set('eyechecksummary', $eyechecksummary);
        $this->set('certificates', $certificates);
        $this->set('summary', $summary);

        $this->render('singlesummary');
    }

    public function summary()
    {
        $this->layout = 'modal';
        $certificates = array();

        $certificates_options = array(
            'order' => array('CertificateData.id DESC'),
            'conditions' => array(
                'Examiner.testingcomp_id' => $this->Autorisierung->ConditionsTestinccomps(),
                'Examiner.deleted' => 0,
                'CertificateData.active' => 1,
                'CertificateData.deleted' => 0,
            ),
        );

        $certificate = $this->Examiner->Certificate->CertificateData->find('all', $certificates_options);

        $certficates = array();
        $summary = array(
            'futurenow' => array(),
            'future' => array(),
            'errors' => array(),
            'warnings' => array(),
            'hints' => array(),
            'deactive' => array(),
        );

        $summaryeyecheck = array(
            'futurenow' => array(),
            'future' => array(),
            'errors' => array(),
            'warnings' => array(),
            'hints' => array(),
            'deactive' => array(),
        );

        foreach ($certificate as $_key => $_certificates) {
            $__certificates = $this->Qualification->CertificatesSectors($_certificates);
            $___certificates = $this->Qualification->CertificateSummary($__certificates, array('main' => 'Certificate', 'sub' => 'CertificateData'));

            $certificates[$_certificates['Examiner']['id']]['summary'] = $___certificates;

            // Nach Fehler/Hinweisarten sortieren
            foreach ($summary as $__key => $_summary) {
                if (isset($___certificates['summary'][$__key]) && count($___certificates['summary'][$__key]) > 0) {
                    $summary[$__key][$_certificates['Examiner']['id']] = $___certificates['summary'][$__key];
                }
            }
        }

        $this->set('certificates', $certificates);

        $certificates = array();

        $eyechecks_options = array(
            'order' => array('EyecheckData.id DESC'),
            'conditions' => array(
                'Examiner.testingcomp_id' => $this->Autorisierung->ConditionsTestinccomps(),
                'Examiner.deleted' => 0,
            ),
        );

        $eyechecks = $this->Examiner->Eyecheck->EyecheckData->find('all', $eyechecks_options);
        foreach ($eyechecks as $_key => $_certificates) {
            $__certificates = $this->Qualification->Eyechecks($_certificates);
            $___certificates = $this->Qualification->EyecheckSummary($__certificates, array('main' => 'Eyecheck', 'sub' => 'EyecheckData'));

            $certificates[$_certificates['Examiner']['id']]['summary'] = $___certificates;

            // Nach Fehler/Hinweisarten sortieren
            foreach ($summary as $__key => $_summary) {
                if (isset($___certificates['summary'][$__key]) && count($___certificates['summary'][$__key]) > 0) {
                    $summaryeyecheck[$__key][$_certificates['Examiner']['id']] = $___certificates['summary'][$__key];
                }
            }
        }

        $summary_desc = array(
            'futurenow' => array(
                0 => __('First-time certification reached', true),
                1 => __('First-time certifications reached', true),
            ),

            'future' => array(
                0 => __('First-time certification', true),
                1 => __('First-time certifications', true),
            ),
            'errors' => array(
                0 => __('Irregularity', true),
                1 => __('Irregularities', true),
            ),
            'warnings' => array(
                0 => __('Warning', true),
                1 => __('Warnings', true),
            ),
            'hints' => array(
                0 => __('Hint', true),
                1 => __('Hints', true),
            ),
            'deactive' => array(
                0 => __('Deactive', true),
                1 => __('Deactive', true),
            ),
        );

        $SettingsArray = array();

        $this->set('summary_desc', $summary_desc);
        $this->set('SettingsArray', $SettingsArray);
        $this->set('summary', $summary);
        $this->set('summaryeyecheck', $summaryeyecheck);
    }
    /**
     * view method
     *
     * @throws NotFoundException
     * @param string $id
     * @return void
     */
    public function overview()
    {
        $this->Session->delete('Examiner.form');

        if (isset($this->request->data['named']) && $this->request->data['named'] > 0) {
            $named = $this->request->data['named'];
        }

        // Wenn der Prüfer über die Schnellsuche aufgerufen wird
        if ($this->request->projectvars['VarsArray'][15] == 0 && isset($this->request->data['examiner_id']) && $this->request->data['examiner_id'] > 0) {
            $this->request->projectvars['VarsArray'][15] = $this->Sicherheit->Numeric($this->request->data['examiner_id']);
        }

        $id = $this->request->projectvars['VarsArray'][15];
        //        $this->request->projectvars['VarsArray'][15] = $id;

        if ($this->Session->check('paging') === true) {

            $Paging = $this->Session->read('paging');

            if (isset($Paging['examiners']['index'])) {

                $Paging['examiners']['index']['anchor'] = $id;

                $this->Session->write('paging', $Paging);

            }
        }

        if (!$this->Examiner->exists($id)) {
            throw new NotFoundException(__('Invalid examiner'));
        }

        $this->Autorisierung->CheckExaminerLink($id);

        $arrayData = $this->Xml->DatafromXml('Examiner', 'file', null);

        $examiner = $this->Examiner->find('first', array('conditions' => array('Examiner.id' => $id)));

        $this->request->data = $examiner;

        $options = array(
            'conditions' => array(
                'ExaminerMonitoring.deleted' => 0,
                'ExaminerMonitoring.examiner_id' => $id,
                'ExaminerMonitoring.testingcomp_id' => $this->Autorisierung->ConditionsTestinccomps(),
            ),
        );

        $this->Examiner->ExaminerMonitoring->recursive = -1;
        $ExaminerMonitoring = $this->Examiner->ExaminerMonitoring->find('first', $options);

        if (count($ExaminerMonitoring) == 1) {
            $examiner['ExaminerMonitoring'] =
                $ExaminerMonitoring['ExaminerMonitoring'];
        }

        $this->request->data = $this->Data->GeneralDropdownData(array('Examiner'), $arrayData, $this->request->data);

        $certificates = $this->Qualification->CertificatesSectors($examiner);
        $summary = $this->Qualification->CertificateSummary($certificates, array('main' => 'Certificate', 'sub' => 'CertificateData'));
        $this->set('summary', $summary['summary']);

        $eyechecks = $this->Qualification->Eyechecks($examiner);
        $eyechecksummary = $this->Qualification->EyecheckSummary($eyechecks, array('main' => 'Eyecheck', 'sub' => 'EyecheckData'));
        $this->set('eyechecksummary', $eyechecksummary['summary']);

        $SettingsArray = array();

        if (Configure::read('DeviceManager') == true) {
            $SettingsArray['devicelink'] = array('discription' => __('Devices administration', true), 'controller' => 'devices', 'action' => 'index', 'terms' => null);
        }

        if (Configure::read('DocumentManager') == true) {
            $SettingsArray['documentlink'] = array('discription' => __('Document administration', true), 'controller' => 'documents', 'action' => 'index', 'terms' => null);
        }

        $breads = $this->Navigation->Breads(null);

        unset($this->request->projectvars['VarsArray'][16]);
        unset($this->request->projectvars['VarsArray'][17]);

        if (isset($named)) {
            $this->request->projectvars['VarsArray'][16] = 'page:' . $named;
        }

        $breads[1]['discription'] = __('Examiners', true);
        $breads[1]['controller'] = 'examiners';
        $breads[1]['action'] = 'index';
        $breads[1]['pass'] = $this->request->projectvars['VarsArray'];

        $breads[2]['discription'] = $examiner['Examiner']['first_name'] . ' ' . $examiner['Examiner']['name'];
        $breads[2]['controller'] = 'examiners';
        $breads[2]['action'] = 'overview';
        $breads[2]['pass'] = $this->request->projectvars['VarsArray'];

        $this->set('breads', $breads);
        $this->set('eyechecks', $eyechecks);
        $this->set('arrayData', $arrayData);
        $this->set('examiner', $examiner);
        $this->set('SettingsArray', $SettingsArray);
    }

    public function eyechecks()
    {
        if (isset($this->request->params['pass']['15'])) {
            $examiner_id = $this->Sicherheit->Numeric($this->request->params['pass']['15']);
        }

        if (!$this->Examiner->exists($examiner_id)) {
            throw new NotFoundException(__('Invalid examiner'));
        }

        $arrayData = $this->Xml->DatafromXml('Examiner', 'file', null);

        $this->Examiner->recursive = -1;
        $examiner = $this->Examiner->find('first', array('conditions' => array('Examiner.id' => $examiner_id)));

        $this->Examiner->Eyecheck->recursive = -1;
        $eyecheck_options = array(
            'order' => array('EyecheckData.id DESC'),
            'conditions' => array(
                'EyecheckData.examiner_id' => $examiner_id,
            ),
        );

        $eyechecks = $this->Examiner->Eyecheck->EyecheckData->find('first', $eyecheck_options);
        if (count($eyechecks) > 0) {
            $this->request->data = $eyechecks;
            $this->request->data = $this->Data->GeneralDropdownData(array('Examiner'), $arrayData, $this->request->data);

            $eyechecks['EyecheckData']['period'] = $eyechecks['EyecheckData']['recertification_in_year'];
            $eyechecks = $this->Qualification->TimeHorizons($eyechecks, array('main' => 'Eyecheck', 'sub' => 'EyecheckData'), $eyechecks['EyecheckData']['period']);

            $eyechecks = $this->Qualification->Eyechecks($examiner);
            $eyechecksummary = $this->Qualification->EyecheckSummary($eyechecks, array('main' => 'Eyecheck', 'sub' => 'EyecheckData'));

            $this->set('eyechecksummary', $eyechecksummary['summary']);
        } else {
            $this->request->data = $examiner;
        }

        $this->request->projectvars['VarsArray'][15] = $examiner_id;
        $SettingsArray = array();

        if (Configure::read('DeviceManager') == true) {
            $SettingsArray['devicelink'] = array('discription' => __('Devices administration', true), 'controller' => 'devices', 'action' => 'index', 'terms' => null);
        }

        if (Configure::read('DocumentManager') == true) {
            $SettingsArray['documentlink'] = array('discription' => __('Document administration', true), 'controller' => 'documents', 'action' => 'index', 'terms' => null);
        }

        unset($this->request->projectvars['VarsArray'][16]);
        unset($this->request->projectvars['VarsArray'][17]);

        $breads = $this->Navigation->Breads(null);

        $breads[1]['discription'] = __('Examiners', true);
        $breads[1]['controller'] = 'examiners';
        $breads[1]['action'] = 'index';
        $breads[1]['pass'] = null;

        $breads[2]['discription'] = $examiner['Examiner']['first_name'] . ' ' . $examiner['Examiner']['name'];
        $breads[2]['controller'] = 'examiners';
        $breads[2]['action'] = 'overview';
        $breads[2]['pass'] = $this->request->projectvars['VarsArray'];

        $breads[3]['discription'] = __('eye checks');
        $breads[3]['controller'] = 'examiners';
        $breads[3]['action'] = 'eyechecks';
        $breads[3]['pass'] = $this->request->projectvars['VarsArray'];

        $this->request->projectvars['VarsArray'][16] = isset($eyechecks['Eyecheck']['id']) ? $eyechecks['Eyecheck']['id'] : '';
        $this->request->projectvars['VarsArray'][17] = isset($eyechecks['EyecheckData']['id']) ? $eyechecks['EyecheckData']['id'] : '';

        $this->set('breads', $breads);
        $this->set('arrayData', $arrayData);
        $this->set('examiner', $examiner);
        $this->set('eyechecks', $eyechecks);
        $this->set('SettingsArray', $SettingsArray);
    }

    public function eyecheck()
    {
        $this->layout = 'modal';

        $examiner_id = $this->Sicherheit->Numeric($this->request->params['pass']['15']);
        $certificate_id = $this->Sicherheit->Numeric($this->request->params['pass']['16']);

        $modus = $this->Sicherheit->Numeric($this->request->data['modus']);
        $this->set('modus', $modus);

        if (!$this->Examiner->exists($examiner_id)) {
            throw new NotFoundException(__('Invalid examiner'));
        }

        if (!$this->Examiner->Eyecheck->exists($certificate_id)) {
            throw new NotFoundException(__('Invalid eyecheck'));
        }

        if (isset($this->request->data['Eyecheck']) && ($this->request->is('post') || $this->request->is('put'))) {
            unset($this->request->data['Order']);

            if ($this->Examiner->Eyecheck->save($this->request->data)) {
                $this->Flash->success(__('The eye check has been saved'), array('key' => 'success'));
                $this->Autorisierung->Logger($certificate_id, $this->request->data['Eyecheck']);
            } else {
                $this->Flash->error(__('The eye check could not be saved. Please, try again.'), array('key' => 'error'));
            }
        }

        $eyecheck_data_option = array(
            'order' => array('certified_date DESC'),
            'conditions' => array(
                'EyecheckData.certificate_id' => $certificate_id,
                'EyecheckData.deleted' => 0,
                'EyecheckData.active' => 1,
            ),
        );

        $eyecheck_data = $this->Examiner->Eyecheck->EyecheckData->find('first', $eyecheck_data_option);

        if (empty($eyecheck_data)) {
            $eyecheck_data_option = array(
                'order' => array('certified_date DESC'),
                'conditions' => array(
                    'EyecheckData.certificate_id' => $certificate_id,
                    'EyecheckData.deleted' => 0,
                    'EyecheckData.active' => 0,
                ),
            );
            $eyecheck_data = $this->Examiner->Eyecheck->EyecheckData->find('first', $eyecheck_data_option);
        }

        $arrayData = $this->Xml->DatafromXml('Eyecheck', 'file', null);

        $eyecheck_data['EyecheckData']['period'] = $eyecheck_data['Eyecheck']['recertification_in_year'];

        $eyecheck_data = $this->Qualification->TimeHorizons($eyecheck_data, array('main' => 'Eyecheck', 'sub' => 'EyecheckData'), $eyecheck_data['EyecheckData']['period']);

        $this->request->data = $eyecheck_data;

        $SettingsArray = array();
        $this->request->projectvars['VarsArray'][15] = $examiner_id;
        $this->request->projectvars['VarsArray'][16] = $certificate_id;
        $FileUploadErrors = $this->Session->read('FileUploadErrors');

        if (isset($FileUploadErrors)) {
            $this->set('FileUploadErrors', $FileUploadErrors);
            $this->set('message', Configure::read('FileUploadErrors.' . $FileUploadErrors));
        }

        $this->set('SettingsArray', $SettingsArray);
        $this->set('eyecheck_data', $eyecheck_data);
        $this->set('settings', $arrayData['headoutput']);
    }

    public function neweyecheck()
    {
        $this->layout = 'modal';

        $examiner_id = $this->Sicherheit->Numeric($this->request->params['pass']['15']);

        if (isset($this->request->data['Eyecheck']) && ($this->request->is('post') || $this->request->is('put'))) {

            $message = null;

            $certificate_data_option = array(
                'conditions' => array(
                    'Examiner.id' => $examiner_id,
                ),
            );
    
            $certificate_data = $this->Examiner->find('first', $certificate_data_option);    

            // muss noch im Helper korrigiert werden
            foreach ($this->request->data['Eyecheck'] as $_key => $_certificate) {
                if ($_key == '0') {
                    foreach ($this->request->data['Eyecheck'][0] as $__key => $__certificate) {
                        $this->request->data['Eyecheck'][$__key] = $__certificate;
                    }
                }
            }

            $Insert = $this->request->data;
            
            $Insert['Eyecheck']['renewal_in_year'] = 0;
            $Insert['Eyecheck']['recertification_in_year'] = 0;
            $Insert['Eyecheck']['examiner_id'] = $certificate_data['Examiner']['id'];
            $Insert['Eyecheck']['user_id'] = $this->Auth->user('id');

            unset($Insert['Eyecheck']['id']);

            if ($this->Examiner->Eyecheck->save($Insert)) {
                $message .= __('The vision test has been saved');

                $this->Flash->success($message, array('key' => 'success'));

                $certificate_id = $this->Examiner->Eyecheck->getLastInsertID();
   
                $this->Autorisierung->Logger($certificate_id, $Insert);

                $certificate_data = $this->Examiner->Eyecheck->find('first', array('conditions' => array('Eyecheck.id' => $certificate_id)));

                $certificate_data_data = array(
                    'certificate_id' => $certificate_id,
                    'examiner_id' => $certificate_data['Eyecheck']['examiner_id'],
                    'certified' => 1,
                    'first_registration' => $certificate_data['Eyecheck']['first_registration'],
                    'first_certification' => 0,
                    'certified_date' => $certificate_data['Eyecheck']['first_registration'],
                    'horizon' => $certificate_data['Eyecheck']['horizon'],
                    'recertification_in_year' => $certificate_data['Eyecheck']['recertification_in_year'],
                 //   'expiration_date' => $Insert['EyecheckData']['expiration_date'],
                    'active' => 1,
                    'deleted' => 0,
                    'user_id' => $this->Auth->user('id'),
                );

                $this->Examiner->Eyecheck->EyecheckData->save($certificate_data_data);
                $certificate_data_id = $this->Examiner->Eyecheck->EyecheckData->getLastInsertId();

                $certificate_data_option = array(
                    'conditions' => array(
                        'EyecheckData.id' => $certificate_data_id,
                    ),
                );

                $certificate_data = $this->Examiner->Eyecheck->EyecheckData->find('first', $certificate_data_option);
    
                $VarsArray = $this->request->projectvars['VarsArray'];

                $VarsArray[15] = $certificate_data['Examiner']['id'];
                $VarsArray[16] = $certificate_data['Eyecheck']['id'];
                $VarsArray[17] = $certificate_data['EyecheckData']['id'];
       
                $FormName['controller'] = 'examiners';
                $FormName['action'] = 'eyechecks';
                $FormName['terms'] = implode('/', $VarsArray);
    
                $this->set('FormName', $FormName);
                $this->set('certificate_data', $certificate_data);

            } else {
                $this->Flash->error(__('The vision test could not be saved. Please, try again.'), array('key' => 'error'));
            }
        }

        $arrayData['settings'] = $this->Xml->CollectXMLFromFile('Eyecheck');

        $certificate_data_option = array(
            'conditions' => array(
                'Examiner.id' => $examiner_id,
            ),
        );

        $certificate_data = $this->Examiner->find('first', $certificate_data_option);

        $certificate_data['Eyecheck']['active'] = 1;

        $this->request->data = $certificate_data;
        $this->request->data = $this->Data->GeneralDropdownData(array('Certificate'), $arrayData, $this->request->data);

        $SettingsArray = array();
        $this->request->projectvars['VarsArray'][15] = $examiner_id;

        $this->set('certificate_data', $certificate_data);
        $this->set('SettingsArray', $SettingsArray);
        $this->set('settings', $arrayData['settings']);
    }

    public function editeyecheck()
    {
        $this->layout = 'modal';

        $examiner_id = $this->Sicherheit->Numeric($this->request->params['pass']['15']);
        $certificate_id = $this->Sicherheit->Numeric($this->request->params['pass']['16']);
        $certificate_data_id = $this->Sicherheit->Numeric($this->request->params['pass']['17']);

        if (!$this->Examiner->exists($examiner_id)) {
            throw new NotFoundException(__('Invalid examiner'));
        }

        if (!$this->Examiner->Eyecheck->exists($certificate_id)) {
            throw new NotFoundException(__('Invalid certificate'));
        }

        if (!$this->Examiner->Eyecheck->EyecheckData->exists($certificate_data_id)) {
            throw new NotFoundException(__('Invalid certificate info'));
        }

        if (isset($this->request->data['EyecheckData']) && ($this->request->is('post') || $this->request->is('put'))) {

            unset($this->request->data['Order']);

            if ($this->Examiner->Eyecheck->EyecheckData->save($this->request->data)) {
                $this->Flash->success(__('The eye check infos has been saved'), array('key' => 'success'));
                $this->Autorisierung->Logger($certificate_data_id, $this->request->data['EyecheckData']);
            } else {
                $this->Flash->error(__('The eye check infos could not be saved. Please, try again.'), array('key' => 'error'));
            }

            $FormName['controller'] = 'examiners';
            $FormName['action'] = 'eyechecks';
            $FormName['terms'] = implode('/', $this->request->projectvars['VarsArray']);

            $this->set('FormName', $FormName);
        }

        $eyecheck_data_option = array(
            'order' => array('certified_date DESC'),
            'conditions' => array(
                'EyecheckData.id' => $certificate_data_id,
                'EyecheckData.deleted' => 0,
                'EyecheckData.active' => 1,
            ),
        );

        $eyecheck_data = $this->Examiner->Eyecheck->EyecheckData->find('first', $eyecheck_data_option);

        if (empty($eyecheck_data)) {
            $eyecheck_data_option = array(
                'order' => array('certified_date DESC'),
                'conditions' => array(
                    'EyecheckData.certificate_id' => $certificate_id,
                    'EyecheckData.deleted' => 0,
                    'EyecheckData.active' => 0,
                ),
            );

            $eyecheck_data = $this->Examiner->Eyecheck->EyecheckData->find('first', $eyecheck_data_option);
        }

        if (isset($this->request->data['back']) && $this->request->data['back'] == 1) {
            $certificateinfo = 1;
        }

        $this->request->data = $eyecheck_data;

        $arrayData['settings'] = $this->Xml->CollectXMLFromFile('EyecheckData');

        $this->request->projectvars['VarsArray'][17] = $certificate_data_id;

        $SettingsArray = array();

        $this->set('SettingsArray', $SettingsArray);
        $this->set('eyecheck_data', $eyecheck_data);
        $this->set('settings', $arrayData['settings']);

    }

    public function qualifications()
    {
        $id = $this->Sicherheit->Numeric($this->request->params['pass']['15']);

        if (!$this->Examiner->exists($id)) {
            throw new NotFoundException(__('Invalid examiner'));
        }

        // Wenn qualifications mit einer Variablen für eine spezielle Qualifikation aufgerufen wird
        // wird dies mit der Variable QualiMarking übergeben, um diese Qualifikation im View zu markieren
        // Foth, 14.08.2017
        if (isset($this->request->projectvars['VarsArray'][16]) && $this->request->projectvars['VarsArray'][16] > 0) {
            $this->set('QualiMarking', $this->request->projectvars['VarsArray'][16]);
        } else {
            $this->set('QualiMarking', 0);
        }

        $arrayData = $this->Xml->DatafromXml('Examiner', 'file', null);

        $this->Examiner->recursive = -1;
        $examiner = $this->Examiner->find('first', array('conditions' => array('Examiner.id' => $id)));
        $certificates = $this->Qualification->CertificatesSectors($examiner);

        $this->request->data = $examiner;
        $this->request->data = $this->Data->GeneralDropdownData(array('Examiner'), $arrayData, $this->request->data);

        $summary = $this->Qualification->CertificateSummary($certificates, array('main' => 'Certificate', 'sub' => 'CertificateData'));

        $this->request->projectvars['VarsArray'][15] = $id;
        $SettingsArray = array();

        if (Configure::read('DeviceManager') == true) {
            $SettingsArray['devicelink'] = array('discription' => __('Devices administration', true), 'controller' => 'devices', 'action' => 'index', 'terms' => null);
        }

        if (Configure::read('DocumentManager') == true) {
            $SettingsArray['documentlink'] = array('discription' => __('Document administration', true), 'controller' => 'documents', 'action' => 'index', 'terms' => null);
        }

        unset($this->request->projectvars['VarsArray'][16]);
        unset($this->request->projectvars['VarsArray'][17]);

        $breads = $this->Navigation->Breads(null);

        $breads[1]['discription'] = __('Examiners', true);
        $breads[1]['controller'] = 'examiners';
        $breads[1]['action'] = 'index';
        $breads[1]['pass'] = null;

        $breads[2]['discription'] = $examiner['Examiner']['first_name'] . ' ' . $examiner['Examiner']['name'];
        $breads[2]['controller'] = 'examiners';
        $breads[2]['action'] = 'overview';
        $breads[2]['pass'] = $this->request->projectvars['VarsArray'];

        $breads[3]['discription'] = __('qualifications');
        $breads[3]['controller'] = 'examiners';
        $breads[3]['action'] = 'qualifications';
        $breads[3]['pass'] = $this->request->projectvars['VarsArray'];

        $this->set('breads', $breads);

        $this->set('arrayData', $arrayData);
        $this->set('summary', $summary['summary']);
        $this->set('examiner', $examiner);
        $this->set('certificates', $certificates);
        $this->set('SettingsArray', $SettingsArray);
    }

    // ----------------------------------------------------------------
    // nur zum transferieren der Prüfverfahren in die Verknüpfungstabelle
    // ----------------------------------------------------------------
    protected function _MoveTestingMethods(){

        $this->loadModel('Testingmethod');
        $this->loadModel('CertificatesTestingmethode');

        $this->Examiner->Certificate->recursive = -1;
        $this->Testingmethod->recursive = -1;

        $Certificates = $this->Examiner->Certificate->find('all');

        foreach($Certificates as $key => $value){
            
            $testingmethod = strtolower($value['Certificate']['testingmethod']);
            $testingmethods = $this->Testingmethod->find('list', array('conditions' => array('Testingmethod.invoice' => $testingmethod)));

            pr($value['Certificate']['id']);
            $testingmethods = array_keys($testingmethods);

            if(count($testingmethods) == 0) continue;

            foreach($testingmethods as $_key => $_value){

                $Insert = array('certificate_id' => $value['Certificate']['id'],'testingmethod_id' => $_value);

                pr($Insert);

                $this->CertificatesTestingmethode->save($Insert);

            }

        }

        die();

    }

    public function qualification()
    {
        // nur zum einfügen der Prüfverfahrne in die Verknüpfungstabelle
        //        $this->_MoveTestingMethods();

        $this->layout = 'modal';
        $this->loadModel('Testingmethod');

        $examiner_id = $this->Sicherheit->Numeric($this->request->params['pass'][15]);
        $certificate_id = $this->Sicherheit->Numeric($this->request->params['pass'][16]);

        $modus = 0;
        if (isset($this->request->data['type'])) {
            $modus = $this->Sicherheit->Numeric($this->request->data['type']);
        }

        $this->set('modus', $modus);

        $this->Testingmethod->recursive = -1;

        $testingmethods = $this->Testingmethod->find('all', array(
            'conditions' => array(
                'Testingmethod.enabled' => 1
                ), 
            'order' => array(
                'Testingmethod.verfahren ASC'
                )
            )
        );

        $testingmethods = Hash::combine($testingmethods, '{n}.Testingmethod.id', '{n}.Testingmethod.verfahren');
        $testingmethods = array_unique($testingmethods);

        $Data = $this->Qualification->SaveQulification($testingmethods);

        if(isset($Data['FormName'])){

            $this->set('SettingsArray', array());
            $this->set('NextModal', 1);
            $this->set('certificate_data', $Data);
            $this->set('FormName', $Data['FormName']);

            return;

        }

        $certificate_data_option = array(
            'conditions' => array(
                'Certificate.id' => $certificate_id,
                'Certificate.deleted' => 0,
            ),
        );

        $this->Examiner->Certificate->recursive = 1;
        $this->Examiner->Certificate->CertificateData->recursive = -1;

        $certificate_data = $this->Examiner->Certificate->find('first', $certificate_data_option);

        $certificate_data_option = array(
            'order' => array('id DESC'),
            'conditions' => array(
                'CertificateData.certificate_id' => $certificate_id,
                'CertificateData.deleted' => 0,
            ),
        );

        $certificate_data_data = $this->Examiner->Certificate->CertificateData->find('first', $certificate_data_option);

        if(count($certificate_data_data) > 0) $certificate_data['CertificateData'] = $certificate_data_data['CertificateData'];

        $certificate_data['CertificatesTestingmethodes'] = Hash::extract($certificate_data['CertificatesTestingmethodes'], '{n}.testingmethod_id');

        //        $arrayData = $this->Xml->DatafromXml('Certificate', 'file', null);
        $arrayData['settings'] = $this->Xml->CollectXMLFromFile('Certificate');

        // Wenn Zertifizierung aktiviert oder deaktiviert wird
        if (isset($this->request->data['certificate_data_active'])) {
            if ($this->request->data['certificate_data_active'] == 0) {
                if (empty($certificate_data['Certificate']['first_registration'])) {
                }
                if (!empty($certificate_data['Certificate']['first_registration'])) {
                    $certificate_data['Certificate']['first_registration'] = null;
                    $certificate_data['Certificate']['certificate_data_active'] = 0;
                    $certificate_data['Certificate']['certificat'] = '-';
                    $certificate_data['Certificate']['first_certification'] = 0;
                    $certificate_data['Certificate']['renewal_in_year'] = 0;
                    $certificate_data['Certificate']['recertification_in_year'] = 0;
                    $certificate_data['Certificate']['horizon'] = 0;
                }
            }
            if ($this->request->data['certificate_data_active'] == 1) {
                // Wenn Zertifizierung auf aktiv steht wird first_registration zum Pflichfeld
                $this->Examiner->Certificate->validate['first_registration'] = array(
                    'notempty' => array(
                        'rule' => array('notempty'),
                        'message' => 'Your custom message here',
                    ),
                );
                $certificate_data['Certificate']['certificate_data_active'] = 1;
                $certificate_data['Certificate']['certificat'] = '';
                $certificate_data['Certificate']['first_certification'] = '';
                $certificate_data['Certificate']['renewal_in_year'] = '';
                $certificate_data['Certificate']['recertification_in_year'] = '';
                $certificate_data['Certificate']['horizon'] = '';
                $certificate_data['Certificate']['certificate_data_active'] = 1;

                $certificate_data['CertificateData']['active'] = 1;

            }
        }

        if(count($certificate_data['CertificateData']) == 0 && $certificate_data['Certificate']['certificate_data_active'] == 0){

            $certificate_data['Certificate']['horizon'] = 0;
            $certificate_data['Certificate']['first_registration'] = null;
            $certificate_data['CertificateData']['first_certification'] = 0;

        }

        $testingmethod_id_key = $this->Testingmethod->find('first', array(
            'conditions' => array(
                'Testingmethod.name' => $certificate_data['Certificate']['testingmethod']
            ),
            'fields' => array('id')
            )
        );

        if(count($certificate_data['CertificatesTestingmethodes']) == 0){
            $certificate_data['CertificatesTestingmethodes'][] = $testingmethod_id_key['Testingmethod']['id'];
        }

        $this->set('testingmethods', $testingmethods);
        $this->set('testingmethod_id', $testingmethod_id_key['Testingmethod']['id']);

        $this->request->data = $certificate_data;
        $this->request->data = $this->Data->GeneralDropdownData(array('Certificate'), $arrayData, $this->request->data);

        if($this->request->data['Certificate']['certificate_data_active'] == 0){
            $this->request->data['CertificateData']['active'] = 0;
        }

        $this->Data->SessionFormload('Certificate');

        $SettingsArray = array();
        $this->request->projectvars['VarsArray'][15] = $examiner_id;
        $this->request->projectvars['VarsArray'][16] = $certificate_id;

        $this->set('SettingsArray', $SettingsArray);
        $this->set('certificate_data', $certificate_data);
        $this->set('settings', $arrayData['settings']);
    }

    public function qualificationdel()
    {
        $this->layout = 'modal';

        $examiner_id = $this->Sicherheit->Numeric($this->request->params['pass']['15']);
        $certificate_id = $this->Sicherheit->Numeric($this->request->params['pass']['16']);

        $modus = 0;
        if (isset($this->request->data['type'])) {
            $modus = $this->Sicherheit->Numeric($this->request->data['type']);
        }

        $this->set('modus', $modus);

        if (!$this->Examiner->exists($examiner_id)) {
            throw new NotFoundException(__('Invalid examiner'));
        }

        if (!$this->Examiner->Certificate->exists($certificate_id)) {
            throw new NotFoundException(__('Invalid qualification'));
        }

        if (isset($this->request->data['Certificate']) && ($this->request->is('post') || $this->request->is('put'))) {
            $message = null;

            //            $this->Examiner->Certificate->recursive = -1;
            $certificate_data = $this->Examiner->Certificate->find('first', array('conditions' => array('Certificate.id' => $certificate_id)));

            $this->request->data['Certificate']['status'] = 0;
            $this->request->data['Certificate']['deleted'] = 1;
            $this->request->data['Certificate']['user_id'] = $this->Auth->user('id');

            $arrayData = $this->Xml->DatafromXml('Certificate', 'file', null);

            $certificate_data['Certificate']['deleted'] = 1;
            $certificate_data['Certificate']['active'] = 0;

            if(empty($certificate_data['Certificate']['certificat'])) $certificate_data['Certificate']['certificat'] = '-';

            if (isset($certificate_data['Certificate']['first_registration'])) {
                if ($certificate_data['Certificate']['deleted'] == '') {
                    unset($certificate_data['Certificate']['first_registration']);
                }
            }

            $save = $certificate_data['Certificate'];

            $this->Examiner->Certificate->save($save);

            $errors = $this->Examiner->Certificate->validationErrors;

            if ($this->Examiner->Certificate->save($save)) {

                $message .= __('The qualification has been deleted');
                $this->Flash->success($message, array('key' => 'success'));

                $this->Autorisierung->Logger($certificate_id, $this->request->data['Certificate']);

                $certificate_data = $this->Examiner->Certificate->find('first', array('conditions' => array('Certificate.id' => $certificate_id)));

                if ($certificate_data['Certificate']['certificate_data_active'] == 0) {

                    $this->request->projectvars['VarsArray'][15] = $examiner_id;
                    $this->request->projectvars['VarsArray'][16] = 0;
                    $this->request->projectvars['VarsArray'][17] = 0;

                    $FormName['controller'] = 'examiners';
                    $FormName['action'] = 'qualifications';
                    $FormName['terms'] = implode('/', $this->request->projectvars['VarsArray']);

                    $this->set('CloseModal', 1);

                    $this->set('SettingsArray', array());
                    $this->set('certificate_data', $certificate_data);
                    $this->set('FormName', $FormName);

                    $this->Flash->success($message, array('key' => 'success'));

                    return false;
                }

                if ($certificate_data['Certificate']['certificate_data_active'] == 1) {
                    $certificat_data_current = array(
                        'CertificateData.deleted' => 1,
                        'CertificateData.user_id' => $this->Auth->user('id'),
                    );

                    if ($this->Examiner->Certificate->CertificateData->updateAll(
                        $certificat_data_current,
                        array('CertificateData.certificate_id' => $certificate_id)
                    )) {
                        $this->request->projectvars['VarsArray'][15] = $examiner_id;
                        $this->request->projectvars['VarsArray'][16] = 0;
                        $this->request->projectvars['VarsArray'][17] = 0;

                        $FormName['controller'] = 'examiners';
                        $FormName['action'] = 'qualifications';
                        $FormName['terms'] = implode('/', $this->request->projectvars['VarsArray']);

                        $this->set('CloseModal', 1);

                        $this->set('SettingsArray', array());
                        $this->set('certificate_data', $certificate_data);
                        $this->set('FormName', $FormName);
                        $this->Flash->success(__('The value has been saved.'), array('key' => 'success'));
                        return false;
                    } else {
                        $this->Flash->error(__('The qualification could not be deleted. Please, try again.'), array('key' => 'error'));
                    }
                }
            } else {
                $this->Flash->error(__('The qualification could not be deleted. Please, try again.'), array('key' => 'error'));

            }
        }

        $certificate_data_option = array(
            'conditions' => array(
                'Certificate.id' => $certificate_id,
                'Certificate.deleted' => 0,
            ),
        );

        $certificate_data = $this->Examiner->Certificate->find('first', $certificate_data_option);

        $arrayData = $this->Xml->DatafromXml('Certificate', 'file', null);

        // Wenn Zertifizierung aktiviert oder deaktiviert wird
        if (isset($this->request->data['certificate_data_active'])) {
            if ($this->request->data['certificate_data_active'] == 0) {
                if (empty($certificate_data['Certificate']['first_registration'])) {
                    pr('leer');
                }
                if (!empty($certificate_data['Certificate']['first_registration'])) {
                    pr('voll');
                    $certificate_data['Certificate']['first_registration'] = null;
                    $certificate_data['Certificate']['certificate_data_active'] = 0;
                    $certificate_data['Certificate']['certificat'] = '-';
                    $certificate_data['Certificate']['first_certification'] = 0;
                    $certificate_data['Certificate']['renewal_in_year'] = 0;
                    $certificate_data['Certificate']['recertification_in_year'] = 0;
                    $certificate_data['Certificate']['horizon'] = 0;
                }
            }
            if ($this->request->data['certificate_data_active'] == 1) {
                // Wenn Zertifizierung auf aktiv steht wird first_registration zum Pflichfeld
                $this->Examiner->Certificate->validate['first_registration'] = array(
                    'notempty' => array(
                        'rule' => array('notempty'),
                        'message' => 'Your custom message here',
                    ),
                );
                $certificate_data['Certificate']['certificate_data_active'] = 1;
                $certificate_data['Certificate']['certificat'] = '';
                $certificate_data['Certificate']['first_certification'] = '';
                $certificate_data['Certificate']['renewal_in_year'] = '';
                $certificate_data['Certificate']['recertification_in_year'] = '';
                $certificate_data['Certificate']['horizon'] = '';
            }
        }

        $this->request->data = $certificate_data;
        $this->request->data = $this->Data->GeneralDropdownData(array('Certificate'), $arrayData, $this->request->data);
        $this->request->data['Certificate']['status'] = 0;

        $SettingsArray = array();
        $this->request->projectvars['VarsArray'][15] = $examiner_id;
        $this->request->projectvars['VarsArray'][16] = $certificate_id;

        $message = __('Do you really want to delete this qualification?');
        $message .= ' ';
        $message .= $this->request->data['Certificate']['third_part'] . ' ' . $this->request->data['Certificate']['testingmethod'] . ' ' . $this->request->data['Certificate']['level'] . ' ' . $this->request->data['Certificate']['certificat'] . ' (' . $this->request->data['Certificate']['sector'] . ')';
        $this->Flash->warning($message, array('key' => 'warning'));

        $this->set('SettingsArray', $SettingsArray);
        $this->set('certificate_data', $certificate_data);
        $this->set('settings', $arrayData['headoutput']);
    }

    public function qualificationnew()
    {

        $this->layout = 'modal';
        $this->loadModel('Testingmethod');

        $examiner_id = $this->Sicherheit->Numeric($this->request->params['pass']['15']);

        $certificate_data_option = array(
            'conditions' => array(
                'Examiner.id' => $examiner_id,
            ),
        );

        $certificate_data = $this->Examiner->find('first', $certificate_data_option);
        $this->set('certificate_data', $certificate_data);

        $testingmethods = $this->Testingmethod->find('all', array(
            'conditions' => array(
                'Testingmethod.enabled' => 1
                ),
            'order' => array('Testingmethod.verfahren ASC')
            )
        );

        $testingmethods = Hash::combine($testingmethods, '{n}.Testingmethod.id', '{n}.Testingmethod.verfahren');
        $testingmethods = array_unique($testingmethods, SORT_REGULAR);

        $this->set('testingmethods', $testingmethods);

        $Data = $this->Qualification->SaveQulification($testingmethods);

        if(isset($Data['FormName'])){

            $this->set('SettingsArray', array());
            $this->set('NextModal', 1);
            $this->set('certificate_data', $Data);
            $this->set('FormName', $Data['FormName']);

            return;

        }

        $arrayData['settings'] = $this->Xml->CollectXMLFromFile('Certificate');

        $certificate_data_option = array('conditions' => array('Examiner.id' => $examiner_id));
        $certificate_data = $this->Examiner->Certificate->CertificateData->find('first', $certificate_data_option);

        if (!isset($this->request->data['Certificate'])) {
            $this->request->data = $certificate_data;

            unset($this->request->data['Certificate']);
            unset($this->request->data['CertificateData']);

            $this->request->data['Certificate']['certificat'] = '-';
            $this->request->data['Certificate']['certificate_data_active'] = 0;
            $this->request->data['Certificate']['level'] = 0;
            $this->request->data['Certificate']['supervisor'] = 0;
            $this->request->data['Certificate']['first_certification'] = 0;
            $this->request->data['Certificate']['renewal_in_year'] = 0;
            $this->request->data['Certificate']['recertification_in_year'] = 0;
            $this->request->data['Certificate']['horizon'] = 0;
        }

        $this->Data->SessionFormload('Certificate');
        $this->request->data = $this->Data->GeneralDropdownData(array('Certificate'), $arrayData, $this->request->data);

        $this->request->data['Certificate']['status'] = 0;
        $this->request->data['Certificate']['active'] = 1;
        $this->request->data['CertificateData']['active'] = 0;

        $SettingsArray = array();
        $this->request->projectvars['VarsArray'][15] = $examiner_id;

        $this->set('SettingsArray', $SettingsArray);
        $this->set('settings', $arrayData['settings']);
    }

    public function certificates()
    {
        $id = $this->Sicherheit->Numeric($this->request->params['pass']['15']);

        if (!$this->Examiner->exists($id)) {
            throw new NotFoundException(__('Invalid examiner'));
        }

        $arrayData = $this->Xml->DatafromXml('Examiner', 'file', null);

        // Wenn certificates mit einer Variablen für ein spezielles Zertifikat aufgerufen wird
        // wird dies mit der Variable QualiMarking übergeben, um dieses Zertifikat im View zu markieren
        // Foth, 14.08.2017
        if (isset($this->request->projectvars['VarsArray'][16]) && $this->request->projectvars['VarsArray'][16] > 0) {
            $this->set('QualiMarking', $this->request->projectvars['VarsArray'][16]);
        } else {
            $this->set('QualiMarking', 0);
        }

        $this->Examiner->recursive = -1;
        $examiner = $this->Examiner->find('first', array('conditions' => array('Examiner.id' => $id)));
        $certificates = $this->Qualification->CertificatesSectors($examiner);

        $this->request->data = $examiner;
        $this->request->data = $this->Data->GeneralDropdownData(array('Examiner'), $arrayData, $this->request->data);

        $summary = $this->Qualification->CertificateSummary($certificates, array('main' => 'Certificate', 'sub' => 'CertificateData'));

        $this->request->projectvars['VarsArray'][15] = $id;
        $SettingsArray = array();

        if (Configure::read('DeviceManager') == true) {
            $SettingsArray['devicelink'] = array('discription' => __('Devices administration', true), 'controller' => 'devices', 'action' => 'index', 'terms' => null);
        }

        if (Configure::read('DocumentManager') == true) {
            $SettingsArray['documentlink'] = array('discription' => __('Document administration', true), 'controller' => 'documents', 'action' => 'index', 'terms' => null);
        }

        unset($this->request->projectvars['VarsArray'][16]);
        unset($this->request->projectvars['VarsArray'][17]);

        $breads = $this->Navigation->Breads(null);

        $breads[1]['discription'] = __('Examiners', true);
        $breads[1]['controller'] = 'examiners';
        $breads[1]['action'] = 'index';
        $breads[1]['pass'] = null;

        $breads[2]['discription'] = $examiner['Examiner']['first_name'] . ' ' . $examiner['Examiner']['name'];
        $breads[2]['controller'] = 'examiners';
        $breads[2]['action'] = 'overview';
        $breads[2]['pass'] = $this->request->projectvars['VarsArray'];

        $breads[3]['discription'] = __('Qualifications');
        $breads[3]['controller'] = 'examiners';
        $breads[3]['action'] = 'qualifications';
        $breads[3]['pass'] = $this->request->projectvars['VarsArray'];

        $breads[4]['discription'] = __('Certificates');
        $breads[4]['controller'] = 'examiners';
        $breads[4]['action'] = 'certificates';
        $breads[4]['pass'] = $this->request->projectvars['VarsArray'];

        $this->set('breads', $breads);

        $this->set('arrayData', $arrayData);
        $this->set('summary', $summary['summary']);
        $this->set('examiner', $examiner);
        $this->set('certificates', $certificates);
        $this->set('SettingsArray', $SettingsArray);
    }

    public function certificate()
    {
        $this->layout = 'modal';

        $examiner_id = $this->Sicherheit->Numeric($this->request->params['pass']['15']);
        $certificate_id = $this->Sicherheit->Numeric($this->request->params['pass']['16']);

        $modus = 0;
        if (isset($this->request->data['type'])) {
            $modus = $this->Sicherheit->Numeric($this->request->data['type']);
        }

        $this->set('modus', $modus);

        if (!$this->Examiner->exists($examiner_id)) {
            throw new NotFoundException(__('Invalid examiner'));
        }

        if (!$this->Examiner->Certificate->exists($certificate_id)) {
            throw new NotFoundException(__('Invalid certificate'));
        }

        if (isset($this->request->data['Certificate']) && ($this->request->is('post') || $this->request->is('put'))) {
            unset($this->request->data['Order']);

            $this->Examiner->Certificate->set($this->request->data);
            $errors = array();

            if ($this->Examiner->Certificate->validates()) {
            } else {
                $errors = $this->Examiner->Certificate->validationErrors;
            }
        }

        if (isset($errors) && count($errors) == 0 && isset($this->request->data['Certificate']) && ($this->request->is('post') || $this->request->is('put'))) {

            // Test, ob für die Qualifizierung eine Zertifizierung vorliegt
            $examiner_certificate_data_option = array(
                'order' => array('CertificateData.certified_date DESC'),
                'contain' => array(
                    'CertificateData' => array(
                        'conditions' => array(
                            'CertificateData.certificate_id' => $certificate_id,
                            'CertificateData.deleted' => 0,
                        ),
                    ),
                    'Examiner' => array(
                        'conditions' => array(
                            'Examiner.id' => $examiner_id,
                            'Examiner.deleted' => 0,
                        ),
                    ),
                ),
            );

            $examiner_certificate_data = $this->Examiner->Certificate->CertificateData->find('first', $examiner_certificate_data_option);

            $message = null;

            // das gleiche Prüfverfahren, das gleiche level
            $certificate_exist_options = array(
                'conditions' => array(
                    'Certificate.id !=' => $certificate_id,
                    'Certificate.examiner_id' => $examiner_id,
                    'Certificate.level' => $this->request->data['Certificate']['level'],
                    'Certificate.testingmethod' => $this->request->data['Certificate']['testingmethod'],
                    'Certificate.sector' => $this->request->data['Certificate']['sector'],
                    'Certificate.deleted' => 0,
                ),
            );
            $certificate_exist = $this->Examiner->Certificate->find('first', $certificate_exist_options);

            if (count($certificate_exist) > 0) {
                $message = __('Information could not be saved, there is already a certificate', true) . ' ' . $certificate_exist['Certificate']['sector'] . '/' . $certificate_exist['Certificate']['certificat'] . '/' . $certificate_exist['Certificate']['testingmethod'] . '-' . $certificate_exist['Certificate']['level'];
                $this->Flash->error($message, array('key' => 'error'));
                $this->render('error_modal', 'modal');
                return false;
            }

            // Wenn ein Zertifikat mit gleichem Sector/Prüfverfahren/Zertifikatsnummer existiert
            // wird dies deaktiviert wenn dessen Level kleiner ist als das neu angelegte
            $certificate_exist_options = array(
                'fields' => array('id', 'level'),
                'order' => array('Certificate.id DESC'),
                'conditions' => array(
                    'Certificate.id !=' => $certificate_id,
                    'Certificate.examiner_id' => $examiner_id,
//                                        'Certificate.certificat' => $this->request->data['Certificate']['certificat'],
                    'Certificate.testingmethod' => $this->request->data['Certificate']['testingmethod'],
                    'Certificate.sector' => $this->request->data['Certificate']['sector'],
                    'Certificate.deleted' => 0,
                ),
            );

            $certificate_exist = $this->Examiner->Certificate->find('list', $certificate_exist_options);

            if (count($certificate_exist) > 0) {
                if ($this->request->data['Certificate']['level'] <= max($certificate_exist)) {
                    $message = __('Information could not be saved, there is already a certificate with a higher level for this testingmethod.');
                    $this->Flash->error($message, array('key' => 'error'));
                    $this->render('error_modal', 'modal');
                    return false;
                }
                if ($this->request->data['Certificate']['level'] > max($certificate_exist)) {
                    $this->Examiner->Certificate->CertificateData->updateAll(
                        array(
                            'CertificateData.active' => 0,
                        ),
                        array(
                            'CertificateData.certificate_id' => array_flip($certificate_exist),
                        )
                    );
                }
            }

            $this->request->data['Certificate']['status'] = 0;
            $this->request->data['Certificate']['user_id'] = $this->Auth->user('id');

            $arrayData = $this->Xml->DatafromXml('Certificate', 'file', null);
            $this->request->data = $this->Data->ChangeDropdownData($this->request->data, $arrayData, array('Certificate'));

            // Wenn eine Zertifizierungsfreigabe zurück genomen wird,
            // Werden die entsprechenden Werte in der Qualifikation auf null gesetzt

            if ($this->request->data['Certificate']['certificate_data_active'] == 0 && count($examiner_certificate_data > 0) && isset($examiner_certificate_data['CertificateData']['id'])) {
                $this->request->data['Certificate']['certificat'] = '-';
                $this->request->data['Certificate']['first_certification'] = 0;
                $this->request->data['Certificate']['renewal_in_year'] = 0;
                $this->request->data['Certificate']['recertification_in_year'] = 0;
                $this->request->data['Certificate']['horizon'] = 0;
            }

            if ($this->Examiner->Certificate->save($this->request->data)) {
                $message .= __('The qualification has been saved');
                $this->Flash->success($message, array('key' => 'success'));

                $this->Autorisierung->Logger($certificate_id, $this->request->data['Certificate']);

                $certificate_data = $this->Examiner->Certificate->find('first', array('conditions' => array('Certificate.id' => $certificate_id)));

                if ($this->request->data['Certificate']['certificate_data_active'] == 0) {

                    // Wenn eine Zertifizierungsfreigabe zurück genommen wird
                    // Werden alle Zertifizierungsdaten auf gelöscht gestellt
                    if (count($examiner_certificate_data > 0) && isset($examiner_certificate_data['CertificateData']['id'])) {
                        $this->Examiner->Certificate->CertificateData->updateAll(
                            array(
                                'CertificateData.deleted' => 1,
                                'CertificateData.active' => 0,
                                'CertificateData.user_id' => $this->Auth->user('id'),
                            ),
                            array('CertificateData.certificate_id' => $certificate_id)
                        );
                    }

                    $certificate_data = $this->Examiner->find('first', array('conditions' => array('Examiner.id' => $examiner_id)));

                    $FormName['controller'] = 'examiners';
                    $FormName['action'] = 'certificates';
                    $FormName['terms'] = implode('/', $this->request->projectvars['VarsArray']);

                    $this->set('SettingsArray', array());
                    $this->set('CloseModal', 1);
                    $this->set('certificate_data', $certificate_data);
                    $this->set('FormName', $FormName);
                    return false;
                }

                if ($this->request->data['Certificate']['certificate_data_active'] == 1) {
                    if (count($examiner_certificate_data) > 0) {

                        // Wenn eine vorher zurück gezogene Zertifizierungsfreigabe wieder erteilt wird
                        // muss diese mit den neuen Daten aktualisiert werden
                        $certificat_data_current = array(
                            'CertificateData.first_certification' => $this->request->data['Certificate']['first_certification'],
                            'CertificateData.renewal_in_year' => $this->request->data['Certificate']['renewal_in_year'],
                            'CertificateData.recertification_in_year' => $this->request->data['Certificate']['recertification_in_year'],
                            'CertificateData.horizon' => $this->request->data['Certificate']['horizon'],
                            'CertificateData.deleted' => 0,
                            'CertificateData.active' => 1,
                            'CertificateData.user_id' => $this->Auth->user('id'),
                        );

                        $this->Examiner->Certificate->CertificateData->updateAll(
                            $certificat_data_current,
                            array('CertificateData.id' => $examiner_certificate_data['CertificateData']['id'])
                        );

                        $this->request->projectvars['VarsArray'][15] = $examiner_certificate_data['Examiner']['id'];
                        $this->request->projectvars['VarsArray'][16] = $examiner_certificate_data['Certificate']['id'];
                        $this->request->projectvars['VarsArray'][17] = $examiner_certificate_data['CertificateData']['id'];

                        $FormName['controller'] = 'examiners';
                        $FormName['action'] = 'certificates';
                        $FormName['terms'] = implode('/', $this->request->projectvars['VarsArray']);

                        $this->set('NextModal', 1);

                        $this->set('SettingsArray', array());
                        $this->set('certificate_data', $certificate_data);
                        $this->set('FormName', $FormName);
                        return false;
                    }

                    $certificate_data_data['CertificateData'] = array(
                        'certificate_id' => $certificate_id,
                        'examiner_id' => $certificate_data['Certificate']['examiner_id'],
                        'certified' => 0,
                        'testingmethod' => $certificate_data['Certificate']['testingmethod'],
                        'first_registration' => $certificate_data['Certificate']['first_registration'],
                        'recertification_in_year' => $certificate_data['Certificate']['recertification_in_year'],
                        'renewal_in_year' => $certificate_data['Certificate']['renewal_in_year'],
                        'horizon' => $certificate_data['Certificate']['horizon'],
                        'first_certification' => $certificate_data['Certificate']['first_certification'],
                        'active' => 1,
                        'deleted' => 0,
                        'user_id' => $this->Auth->user('id'),
                    );

                    if (!$this->Examiner->Certificate->CertificateData->save($certificate_data_data)) {
                        $message = __('Information could not be saved.');
                        $this->Flash->error($message, array('key' => 'error'));
                        $this->render('error_modal', 'modal');
                        return false;
                    }

                    $certificate_data_id = $this->Examiner->Certificate->CertificateData->getLastInsertId();
                }

                $certificate_data_option = array(
                    'conditions' => array(
                        'CertificateData.id' => $certificate_data_id,
                    ),
                );

                $certificate_data = $this->Examiner->Certificate->CertificateData->find('first', $certificate_data_option);

                $this->request->projectvars['VarsArray'][15] = $certificate_data['Examiner']['id'];
                $this->request->projectvars['VarsArray'][16] = $certificate_data['Certificate']['id'];
                $this->request->projectvars['VarsArray'][17] = $certificate_data['CertificateData']['id'];

                $FormName['controller'] = 'examiners';
                $FormName['action'] = 'certificates';
                $FormName['terms'] = implode('/', $this->request->projectvars['VarsArray']);

                $this->set('FormName', $FormName);
                $this->set('certificate_data', $certificate_data);
                $this->set('NextModal', 1);
                return false;
            } else {
                $this->Flash->error(__('The qualification could not be saved. Please, try again.'), array('key' => 'error'));
            }
        }

        $certificate_data_option = array(
            'conditions' => array(
                'Certificate.id' => $certificate_id,
                'Certificate.deleted' => 0,
            ),
        );

        $certificate_data = $this->Examiner->Certificate->find('first', $certificate_data_option);

        $arrayData = $this->Xml->DatafromXml('Certificate', 'file', null);

        $this->request->data = $certificate_data;
        $this->request->data = $this->Data->GeneralDropdownData(array('Certificate'), $arrayData, $this->request->data);
        $this->request->data['Certificate']['status'] = 0;

        $SettingsArray = array();
        $this->request->projectvars['VarsArray'][15] = $examiner_id;
        $this->request->projectvars['VarsArray'][16] = $certificate_id;

        $this->set('SettingsArray', $SettingsArray);
        $this->set('certificate_data', $certificate_data);
        $this->set('settings', $arrayData['headoutput']);
    }

    protected function _files($path, $examiner_id, $certificate_id, $certificatefiles_id, $models)
    {
        $outputarray = $this->Qualification->DokuFiles($path, $examiner_id, $certificate_id, $certificatefiles_id, $models);

        $this->set('_data', $outputarray['_data']);
        $this->set('_files', $outputarray['_files']);
        $this->set('models', $outputarray['models']);
    }

    public function certificatesfiles()
    {
        $this->layout = 'modal';

        $models['top'] = 'Examiner';
        $models['main'] = 'Certificate';
        $models['sub'] = 'CertificateData';
        $models['file'] = 'Certificatefile';

        $certificatefile_id = 0;
        $this->loadModel($models['file']);

        if (isset($this->request->data['QuickCertificatefilesearch']) && ($this->request->is('post') || $this->request->is('put'))) {
            if (isset($this->request->data['id'])) {
                $certificatefile_id = $this->Sicherheit->Numeric($this->request->data['id']);

                if (!$this->{$models['file']}->exists($certificatefile_id)) {
                    throw new NotFoundException(__('Invalid value'));
                }

                $certificate_files = $this->{$models['file']}->find(
                    'first',
                    array(
                        'conditions' => array(
                            $models['file'] . '.deleted' => 0,
                            $models['file'] . '.id' => $certificatefile_id,
                            $models['file'] . '.testingcomp_id' => $this->Autorisierung->ConditionsTestinccomps(),
                        ),
                    )
                );

                $certificatefile_id = $certificate_files[$models['file']]['id'];
                $examiner_id = $certificate_files[$models['file']]['examiner_id'];
                $certificate_id = $certificate_files[$models['file']]['certificate_id'];
            }
        } else {
            $examiner_id = $this->Sicherheit->Numeric($this->request->params['pass']['15']);
            $certificate_id = $this->Sicherheit->Numeric($this->request->params['pass']['16']);

            if (!$this->Examiner->exists($examiner_id)) {
                throw new NotFoundException(__('Invalid examiner'));
            }

            if (!$this->Examiner->Certificate->exists($certificate_id)) {
                throw new NotFoundException(__('Invalid certificate'));
            }
        }

        $path = Configure::read('certificate_folder') . $examiner_id . DS . $certificate_id . DS . 'documents' . DS;

        $this->_files($path, $examiner_id, $certificate_id, $certificatefile_id, $models);

        // Werte für die Schnellsuche
        $ControllerQuickSearch =
        array(
            'Model' => 'Certificatefile',
            'Field' => 'description',
            'Conditions' => array(
                'parent_id' => $certificate_id,
            ),
            'description' => __('Dokument description', true),
            'minLength' => 2,
            'targetcontroller' => 'examiners',
            'target_id' => 'id',
            'targetation' => 'certificatesfiles',
            'Quicksearch' => 'QuickCertificatefilesearch',
            'QuicksearchForm' => 'QuickCertificatefilesearchForm',
            'QuicksearchSearchingAutocomplet' => 'QuickCertificatefilesearchSearchingAutocomplet',
        );

        $SettingsArray = array();

        $this->set('SettingsArray', $SettingsArray);
        $this->set('ControllerQuickSearch', $ControllerQuickSearch);
    }

    public function eyecheckfiles()
    {
        $this->layout = 'modal';

        $models['top'] = 'Examiner';
        $models['main'] = 'Eyecheck';
        $models['sub'] = 'EyecheckData';
        $models['file'] = 'Eyecheckfile';
        $certificatefile_id = 0;
        $this->loadModel($models['file']);

        if (isset($this->request->data['QuickEyecheckfilesearch']) && ($this->request->is('post') || $this->request->is('put'))) {
            if (isset($this->request->data['id'])) {
                $certificatefile_id = $this->Sicherheit->Numeric($this->request->data['id']);

                if (!$this->{$models['file']}->exists($certificatefile_id)) {
                    throw new NotFoundException(__('Invalid value'));
                }

                $certificate_files = $this->{$models['file']}->find(
                    'first',
                    array(
                        'conditions' => array(
                            $models['file'] . '.deleted' => 0,
                            $models['file'] . '.id' => $certificatefile_id,
                            $models['file'] . '.testingcomp_id' => $this->Autorisierung->ConditionsTestinccomps(),
                        ),
                    )
                );

                $certificatefile_id = $certificate_files[$models['file']]['id'];
                $examiner_id = $certificate_files[$models['file']]['examiner_id'];
                $certificate_id = $certificate_files[$models['file']]['parent_id'];
            }
        } else {
            $examiner_id = $this->Sicherheit->Numeric($this->request->params['pass'][15]);
            $certificate_id = $this->Sicherheit->Numeric($this->request->params['pass'][16]);
            $certificate_data_id = $this->Sicherheit->Numeric($this->request->params['pass'][17]);

            if (!$this->Examiner->exists($examiner_id)) {
                throw new NotFoundException(__('Invalid examiner'));
            }

            if (!$this->Examiner->Eyecheck->exists($certificate_id)) {
                throw new NotFoundException(__('Invalid certificate'));
            }
        }

        $path = Configure::read('eyecheck_folder') . $examiner_id . DS . $certificate_id . DS . 'documents' . DS;

        $this->_files($path, $examiner_id, $certificate_id, $certificatefile_id, $models);

        $lastModal = array(
            'controller' => 'examiners',
            'action' => 'eyechecks',
            'terms' => $this->request->projectvars['VarsArray'],
        );

        // Werte für die Schnellsuche
        $ControllerQuickSearch =
        array(
            'Model' => 'Eyecheckfile',
            'Field' => 'description',
            'Conditions' => array(
                'parent_id' => $certificate_id,
            ),
            'description' => __('Dokument description', true),
            'minLength' => 2,
            'targetcontroller' => 'examiners',
            'target_id' => 'id',
            'targetation' => 'eyecheckfiles',
            'Quicksearch' => 'QuickEyecheckfilesearch',
            'QuicksearchForm' => 'QuickEyecheckfilesearchForm',
            'QuicksearchSearchingAutocomplet' => 'QuickEyecheckfilesearchSearchingAutocomplet',
        );

        $SettingsArray = array();
        //        $SettingsArray['examinerlink'] = array('discription' => __('Back to overview',true), 'controller' => 'examiners','action' => 'index', 'terms' => null);
        //        $SettingsArray['backlink'] = array('discription' => __('Back',true), 'controller' => $lastModal['controller'],'action' => $lastModal['action'], 'terms' => $lastModal['terms']);
        //        $SettingsArray['eyechecklink'] = array('discription' => __('Examiner eyechecks',true), 'controller' => 'examiners','action' => 'eyechecks', 'terms' => $this->request->projectvars['VarsArray']);

        $this->set('SettingsArray', $SettingsArray);
        $this->set('ControllerQuickSearch', $ControllerQuickSearch);
    }

    protected function _filesdescription($models, $successtarget)
    {
        $examiner_id = $this->Sicherheit->Numeric($this->request->params['pass']['15']);
        $certificate_id = $this->Sicherheit->Numeric($this->request->params['pass']['16']);
        $file_id = 0;

        if (isset($this->request->params['pass']['17']) && $this->Sicherheit->Numeric($this->request->params['pass']['17']) > 0) {
            $file_id = $this->Sicherheit->Numeric($this->request->params['pass']['17']);
        }

        if (!$this->{$models['top']}->exists($examiner_id)) {
            throw new NotFoundException(__('Invalid value'));
        }

        if (!$this->{$models['top']}->{$models['main']}->exists($certificate_id)) {
            throw new NotFoundException(__('Invalid value'));
        }

        $this->loadModel($models['file']);

        $certificate_option = array(
            'conditions' => array(
                $models['main'] . '.id' => $certificate_id,
                $models['main'] . '.deleted' => 0,
            ),
        );

        $certificate_data = $this->{$models['top']}->{$models['main']}->find('first', $certificate_option);

        if (isset($this->request->data[$models['file']]) && ($this->request->is('post') || $this->request->is('put'))) {
            if ($this->request->data[$models['file']]['description'] == '') {
                $file_id = $this->Sicherheit->Numeric($this->request->data[$models['file']]['id']);

                $certificate_data_option = array(
                    'conditions' => array(
                        $models['file'] . '.id' => $file_id,
                        $models['file'] . '.testingcomp_id' => $this->Autorisierung->ConditionsTestinccomps(),
                    ),
                );

                $certificate_files = $this->{$models['file']}->find('first', $certificate_data_option);

                if ($certificate_files[$models['file']]['description'] == '') {
                    $this->request->data = $certificate_files;
                    $this->set('certificate_files', $certificate_files);
                    $this->set('certificate_data', $certificate_data);
                    $this->set('models', $models);
                    return;
                }
            }

            if ($this->{$models['file']}->save($this->request->data)) {

                $this->Flash->success(__('The value has been saved'), array('key' => 'success'));

                $this->Autorisierung->Logger($this->request->data[$models['file']]['id'], $this->request->data[$models['file']]);

                if ($models['file'] == 'Certificatefile') {
                    $_here = explode('/', $this->request->here);
                    $_here[3] = 'certificatesfiles';
                    $this->request->here = implode('/', $_here);
                    $this->certificatesfiles();
                }
                if ($models['file'] == 'Eyecheckfile') {
                    $_here = explode('/', $this->request->here);
                    $_here[3] = 'eyecheckfiles';
                    $this->request->here = implode('/', $_here);
                    $this->eyecheckfiles();
                }

                $this->render($successtarget);
            } else {
                $this->Flash->error(__('The value could not be saved. Please, try again.'), array('key' => 'error'));
            }
        }

        if ($file_id == 0) {
            $certificate_data_option = array(
                'order' => array($models['file'] . '.id DESC'),
                'conditions' => array(
                    $models['file'] . '.description' => '',
                    $models['file'] . '.deleted' => 0,
                    $models['file'] . '.examiner_id' => $examiner_id,
                    $models['file'] . '.parent_id' => $certificate_id,
                    $models['file'] . '.user_id' => $this->Auth->user('id'),
                    $models['file'] . '.testingcomp_id' => $this->Autorisierung->ConditionsTestinccomps(),
                ),
            );
        } elseif ($file_id > 0) {
            $certificate_data_option = array(
                'conditions' => array(
                    $models['file'] . '.id' => $file_id,
                    $models['file'] . '.testingcomp_id' => $this->Autorisierung->ConditionsTestinccomps(),
                ),
            );
        }

        $certificate_files = $this->{$models['file']}->find('first', $certificate_data_option);

        $this->request->data = $certificate_files;

        $FileUploadErrors = $this->Session->read('FileUploadErrors');
        $this->Session->delete('FileUploadErrors');

        $this->set('certificate_files', $certificate_files);
        $this->set('certificate_data', $certificate_data);
        $this->set('models', $models);
        $this->set('message', Configure::read('FileUploadErrors.' . $FileUploadErrors));
        $this->set('uploaderror', $FileUploadErrors);
    }

    public function eyecheckfilesdescription()
    {
        $this->layout = 'modal';

        $models['top'] = 'Examiner';
        $models['main'] = 'Eyecheck';
        $models['sub'] = 'EyecheckData';
        $models['file'] = 'Eyecheckfile';

        $this->_filesdescription($models, 'eyecheckfiles');

        $SettingsArray = array();
        //        $SettingsArray['examinerlink'] = array('discription' => __('Back to overview',true), 'controller' => 'examiners','action' => 'index', 'terms' => null);
        $SettingsArray['backlink'] = array('discription' => __('Back', true), 'controller' => 'examiners', 'action' => 'eyecheckfiles', 'terms' => $this->request->projectvars['VarsArray']);

        $this->set('SettingsArray', $SettingsArray);
    }

    public function certificatefilesdescription()
    {
        $this->layout = 'modal';

        $models['top'] = 'Examiner';
        $models['main'] = 'Certificate';
        $models['sub'] = 'CertificateData';
        $models['file'] = 'Certificatefile';

        $this->_filesdescription($models, 'certificatesfiles');

        $SettingsArray = array();

        $SettingsArray['backlink'] = array('discription' => __('Back', true), 'controller' => 'examiners', 'action' => 'certificatesfiles', 'terms' => $this->request->projectvars['VarsArray']);

        $this->set('SettingsArray', $SettingsArray);
    }

    protected function _delfiles($models, $successtarget)
    {
        $examiner_id = $this->Sicherheit->Numeric($this->request->params['pass']['15']);
        $certificate_id = $this->Sicherheit->Numeric($this->request->params['pass']['16']);
        $file_id = $this->Sicherheit->Numeric($this->request->params['pass']['17']);

        if (!$this->{$models['top']}->exists($examiner_id)) {
            throw new NotFoundException(__('Invalid value'));
        }
        if (!empty($models['main'])) {
            if (!$this->{$models['top']}->{$models['main']}->exists($certificate_id)) {
                throw new NotFoundException(__('Invalid value'));
            }
        }
        $this->loadModel($models['file']);

        if (!$this->{$models['file']}->exists($file_id)) {
            throw new NotFoundException(__('Invalid file'));
        }

        if (isset($this->request->data[$models['file']]) && ($this->request->is('post') || $this->request->is('put'))) {
            $this->request->data[$models['file']]['deleted'] = 1;

            if ($this->{$models['file']}->save($this->request->data)) {

                $this->Flash->success(__('The file has been deleted'), array('key' => 'success'));

                $this->Autorisierung->Logger($this->request->data[$models['file']]['id'], $this->request->data[$models['file']]);

                $_here = explode('/', $this->request->here);
                $_here[3] = 'certificatesfiles';
                $this->request->here = implode('/', $_here);

                if ($models['file'] == 'Certificatefile') {
                    $_here = explode('/', $this->request->here);
                    $_here[3] = 'certificatesfiles';
                    $this->request->here = implode('/', $_here);
                    $this->request->params['action'] = 'certificatesfiles';
                    $FormName['controller'] = 'examiners';
                    $FormName['action'] = 'qualifications';
                    $FormName['terms'] = implode('/', $this->request->projectvars['VarsArray']);
                    $this->set('FormName', $FormName);
                    $this->certificatesfiles();
                }
                if ($models['file'] == 'Eyecheckfile') {
                    $_here = explode('/', $this->request->here);
                    $_here[3] = 'eyecheckfiles';
                    $this->request->here = implode('/', $_here);
                    $this->eyecheckfiles();
                }
                if ($models['file'] == 'Examinerfile') {
                    $FormName['controller'] = 'examiners';
                    $FormName['action'] = 'files';
                    $FormName['terms'] = implode('/', $this->request->projectvars['VarsArray']);
                    $this->set('FormName', $FormName);
                    $this->render();
                    return;
                }

                $this->render($successtarget);
            } else {
                $this->Flash->error(__('The file could not be deleted. Please, try again.'), array('key' => 'error'));
            }
        }

        if (!empty($models['sub'])) {
            $certificate_data_option = array(
                'order' => array($models['sub'] . '.id DESC'),
                'conditions' => array(
                    $models['sub'] . '.certificate_id' => $certificate_id,
                    $models['sub'] . '.deleted' => 0,
                ),
            );

            $certificate_data = $this->{$models['top']}->{$models['main']}->{$models['sub']}->find('first', $certificate_data_option);
        } else {
            $certificate_data_option = array(
                'conditions' => array(
                    $models['file'] . '.id' => $file_id,
                    $models['file'] . '.testingcomp_id' => $this->Autorisierung->ConditionsTestinccomps(),
                ),
            );
        }

        $certificate_data_option = array(
            'conditions' => array(
                $models['file'] . '.id' => $file_id,
                $models['file'] . '.testingcomp_id' => $this->Autorisierung->ConditionsTestinccomps(),
            ),
        );

        $certificate_files = $this->{$models['file']}->find('first', $certificate_data_option);
        $this->request->data = $certificate_files;
        $this->set('certificate_files', $certificate_files);
        isset($certificate_data) ? $this->set('certificate_data', $certificate_data) : '';
        $this->set('models', $models);
    }

    public function delcertificatefiles()
    {
        $this->layout = 'modal';

        $models['top'] = 'Examiner';
        $models['main'] = 'Certificate';
        $models['sub'] = 'CertificateData';
        $models['file'] = 'Certificatefile';

        if (isset($this->request->data[$models['file']]) && ($this->request->is('post') || $this->request->is('put'))) {
            $FormName['controller'] = 'examiners';
            $FormName['action'] = 'certificates';
            $FormName['terms'] = implode('/', $this->request->projectvars['VarsArray']);

            $this->set('FormName', $FormName);
        }

        $this->_delfiles($models, 'certificatesfiles');

        $lastModal = array(
            'controller' => 'examiners',
            'action' => 'certificatesfiles',
            'terms' => $this->request->projectvars['VarsArray'],
        );

        if ($this->Session->check('lastModalURLArray')) {
            $lastModal = array(
                'controller' => $this->Session->read('lastModalURLArray.controller'),
                'action' => $this->Session->read('lastModalURLArray.action'),
                'terms' => $this->Session->read('lastModalURLArray.pass'),
            );
        }

        $SettingsArray = array();
        $SettingsArray['backlink'] = array('discription' => __('Back', true), 'controller' => $lastModal['controller'], 'action' => $lastModal['action'], 'terms' => $lastModal['terms']);

        $this->set('SettingsArray', $SettingsArray);
    }

    public function deleyecheckfiles()
    {
        $this->layout = 'modal';

        $models['top'] = 'Examiner';
        $models['main'] = 'Eyecheck';
        $models['sub'] = 'EyecheckData';
        $models['file'] = 'Eyecheckfile';

        if (isset($this->request->data[$models['file']]) && ($this->request->is('post') || $this->request->is('put'))) {
            $FormName['controller'] = 'examiners';
            $FormName['action'] = 'eyechecks';
            $FormName['terms'] = implode('/', $this->request->projectvars['VarsArray']);

            $this->set('FormName', $FormName);
        }

        $this->_delfiles($models, 'eyecheckfiles');

        $lastModal = array(
            'controller' => 'examiners',
            'action' => 'eyecheckfiles',
            'terms' => $this->request->projectvars['VarsArray'],
        );

        if ($this->Session->check('lastModalURLArray')) {
            $lastModal = array(
                'controller' => $this->Session->read('lastModalURLArray.controller'),
                'action' => $this->Session->read('lastModalURLArray.action'),
                'terms' => $this->Session->read('lastModalURLArray.pass'),
            );
        }

        $SettingsArray = array();
        $SettingsArray['backlink'] = array('discription' => __('Back', true), 'controller' => $lastModal['controller'], 'action' => $lastModal['action'], 'terms' => $lastModal['terms']);

        $this->set('SettingsArray', $SettingsArray);
    }

    public function getcertificatefiles()
    {
        $examiner_id = $this->Sicherheit->Numeric($this->request->params['pass']['15']);
        $certificate_id = $this->Sicherheit->Numeric($this->request->params['pass']['16']);
        $file_id = $this->Sicherheit->Numeric($this->request->params['pass']['17']);

        $models = array('main' => 'Certificatefile', 'sub' => null);
        $path = Configure::read('certificate_folder') . $examiner_id . DS . $certificate_id . DS . 'documents' . DS;

        $this->loadModel($models['main']);

        $this->_getfiles($path, $models);
    }

    public function geteyecheckfiles()
    {
        $examiner_id = $this->Sicherheit->Numeric($this->request->params['pass']['15']);
        $certificate_id = $this->Sicherheit->Numeric($this->request->params['pass']['16']);
        $file_id = $this->Sicherheit->Numeric($this->request->params['pass']['17']);

        $models = array('main' => 'Eyecheckfile', 'sub' => null);
        $path = Configure::read('eyecheck_folder') . $examiner_id . DS . $certificate_id . DS . 'documents' . DS;

        $this->loadModel($models['main']);

        $this->_getfiles($path, $models);
    }

    protected function _getfiles($path, $models)
    {
        $this->layout = 'blank';
        $examiner_id = $this->Sicherheit->Numeric($this->request->params['pass']['15']);
        $certificate_id = $this->Sicherheit->Numeric($this->request->params['pass']['16']);
        $file_id = $this->Sicherheit->Numeric($this->request->params['pass']['17']);
        $main = $models['main'];
        $certificate_data_option = array(
            'conditions' => array(
                $models['main'] . '.testingcomp_id' => $this->Autorisierung->ConditionsTestinccomps(),
                $models['main'] . '.id' => $file_id,
            ),
        );

        $certificate_data = $this->$main->find('first', $certificate_data_option);

        if (count($certificate_data) == 0) {
            $this->redirect(array_merge(array('controller' => $this->Session->read('lastURLArray.controller'), 'action' => $this->Session->read('lastURLArray.action')), $this->request->projectvars['VarsArray']));
        } elseif (count($certificate_data) == 1) {
            if (file_exists($path . $certificate_data[$models['main']]['name'])) {
                $this->set('path', $path . $certificate_data[$models['main']]['name']);
            } else {
                $this->redirect(array_merge(array('controller' => $this->Session->read('lastURLArray.controller'), 'action' => $this->Session->read('lastURLArray.action')), $this->request->projectvars['VarsArray']));
            }
        }
    }

    public function removeeyecheck()
    {
        $this->layout = 'modal';

        $examiner_id = $this->request->projectvars['examinerID'];
        $certificate_id = $this->request->projectvars['deviceID'];

        if (!$this->Examiner->exists($examiner_id)) {
            throw new NotFoundException(__('Invalid examiner'));
        }

        if (!$this->Examiner->Eyecheck->exists($certificate_id)) {
            throw new NotFoundException(__('Invalid certificate'));
        }

        $this->set('hint', __('Will you delete this eye check completely?', true));
        $this->set('del_button', __('Delete', true));

        if (isset($this->request->data['Eyecheck']) && ($this->request->is('post') || $this->request->is('put'))) {
            $this->request->data['Eyecheck']['deleted'] = 1;
            $this->request->data['Eyecheck']['active'] = 0;

            if ($this->Examiner->Eyecheck->save($this->request->data)) {
                $this->Examiner->Eyecheck->EyecheckData->updateAll(
                    array(
                        'EyecheckData.deleted' => 1,
                        'EyecheckData.active' => 0,
                        'EyecheckData.user_id' => $this->Auth->user('id'),
                    ),
                    array(
                        'EyecheckData.certificate_id' => $certificate_id,
                    )
                );

                $path = Configure::read('eyecheck_folder') . $examiner_id . DS . $certificate_id . DS;
                $folder = new Folder($path);
                $folder->delete();

                $this->request->projectvars['VarsArray'][15] = $examiner_id;
                $this->request->projectvars['VarsArray'][16] = 0;

                $FormName['controller'] = 'examiners';
                $FormName['action'] = 'certificates';
                $FormName['terms'] = implode('/', $this->request->projectvars['VarsArray']);

                $this->set('FormName', $FormName);

                $this->Flash->success(__('The eyecheck has been deleted'), array('key' => 'success'));
                $this->Autorisierung->Logger($certificate_id, $this->request->data['Eyecheck']);
            } else {
                $this->Flash->error(__('The eyecheck could not be deleted. Please, try again.'), array('key' => 'error'));
            }
        } else {
            $certificate_data_option = array(
                'conditions' => array(
                    'Eyecheck.id' => $certificate_id,
                    'Eyecheck.deleted' => 0,
//                                        'CertificateData.active' => 1
                ),
            );

            $certificate_data = $this->Examiner->Eyecheck->find('first', $certificate_data_option);

            $this->Examiner->recursive = -1;
            $Examiner = $this->Examiner->find('first', array('conditions' => array('Examiner.id' => $examiner_id)));
            $certificate_data['Examiner'] = $Examiner['Examiner'];

            $arrayData = $this->Xml->DatafromXml('EyecheckData', 'file', null);
            $this->request->data = $certificate_data;

            $SettingsArray = array();
            $this->request->projectvars['VarsArray'][15] = $examiner_id;
            $this->request->projectvars['VarsArray'][16] = $certificate_id;

            $this->Flash->warning(__('Should the eye test be deleted?'), array('key' => 'warning'));

            $this->set('SettingsArray', $SettingsArray);
            $this->set('certificate_data', $certificate_data);
            $this->set('settings', $arrayData);
        }
    }

    public function removecertificate()
    {
        $this->layout = 'modal';

        $examiner_id = $this->Sicherheit->Numeric($this->request->params['pass']['15']);
        $certificate_id = $this->Sicherheit->Numeric($this->request->params['pass']['16']);

        if (!$this->Examiner->exists($examiner_id)) {
            throw new NotFoundException(__('Invalid examiner'));
        }

        if (!$this->Examiner->Certificate->exists($certificate_id)) {
            throw new NotFoundException(__('Invalid certificate'));
        }

        $this->set('del_button', __('Delete', true));

        if (isset($this->request->data['Certificate']) && ($this->request->is('post') || $this->request->is('put'))) {
            unset($this->request->data['Order']);

            $this->request->data['Certificate']['deleted'] = 1;
            $this->request->data['Certificate']['active'] = 0;

            if ($this->Examiner->Certificate->save($this->request->data)) {
                $this->Examiner->Certificate->CertificateData->updateAll(
                    array(
                        'CertificateData.deleted' => 1,
                        'CertificateData.active' => 0,
                        'CertificateData.user_id' => $this->Auth->user('id'),
                    ),
                    array(
                        'CertificateData.certificate_id' => $certificate_id,
                    )
                );

                $path = Configure::read('certificate_folder') . $examiner_id . DS . $certificate_id . DS;
                $folder = new Folder($path);
                $folder->delete();

                $this->request->projectvars['VarsArray'][15] = $examiner_id;
                $this->request->projectvars['VarsArray'][16] = 0;

                $certificate_data_option = array(
                    'order' => array('certified_date DESC'),
                    'conditions' => array(
                        'CertificateData.certificate_id' => $certificate_id,
                    ),

                );

                $certificate_data = $this->Examiner->Certificate->CertificateData->find('first', $certificate_data_option);
                $arrayData = $this->Xml->DatafromXml('Certificate', 'file', null);
                $this->request->data = $certificate_data;

                $SettingsArray = array();
                $this->request->projectvars['VarsArray'][15] = $examiner_id;
                //                $SettingsArray['backlink'] = array('discription' => __('Back',true), 'controller' => 'examiners','action' => 'certificate', 'terms' => $this->request->projectvars['VarsArray']);

                $FormName['controller'] = 'examiners';
                $FormName['action'] = 'certificates';
                $FormName['terms'] = implode('/', $this->request->projectvars['VarsArray']);

                $this->set('FormName', $FormName);

                $this->set('SettingsArray', $SettingsArray);
                $this->set('certificate_data', $certificate_data);
                $this->set('settings', $arrayData['headoutput']);

                $this->Flash->success(__('The certificate has been deleted'), array('key' => 'success'));
                $this->Autorisierung->Logger($certificate_id, $this->request->data['Certificate']);
            } else {
                $this->Flash->error(__('The certificate could not be deleted. Please, try again.'), array('key' => 'error'));
            }
        } else {
            $certificate_data_option = array(
                'order' => array('certified_date DESC'),
                'conditions' => array(
                    'CertificateData.certificate_id' => $certificate_id,
                    'CertificateData.deleted' => 0,
//                                        'CertificateData.active' => 1
                ),
            );

            $certificate_data = $this->Examiner->Certificate->CertificateData->find('first', $certificate_data_option);
            $arrayData = $this->Xml->DatafromXml('Certificate', 'file', null);
            $this->request->data = $certificate_data;

            $SettingsArray = array();
            $this->request->projectvars['VarsArray'][15] = $examiner_id;
            $this->request->projectvars['VarsArray'][16] = $certificate_id;
            //            $SettingsArray['backlink'] = array('discription' => __('Back',true), 'controller' => 'examiners','action' => 'certificate', 'terms' => $this->request->projectvars['VarsArray']);

            $this->Flash->warning(__('Will you delete this certificate?', true), array('key' => 'warning'));

            $this->set('SettingsArray', $SettingsArray);
            $this->set('certificate_data', $certificate_data);
            $this->set('settings', $arrayData['headoutput']);
        }
    }

    public function editcertificate()
    {
        $this->layout = 'modal';

        $examiner_id = $this->Sicherheit->Numeric($this->request->params['pass']['15']);
        $certificate_id = $this->Sicherheit->Numeric($this->request->params['pass']['16']);
        $certificate_data_id = $this->Sicherheit->Numeric($this->request->params['pass']['17']);

        if (!$this->Examiner->exists($examiner_id)) {
            throw new NotFoundException(__('Invalid examiner'));
        }

        if (!$this->Examiner->Certificate->CertificateData->exists($certificate_data_id)) {
            throw new NotFoundException(__('Invalid certificate info'));
        }

        if (isset($this->request->data['CertificateData']) && ($this->request->is('post') || $this->request->is('put'))) {

            $certificate_data_option = array(
                'order' => array('certified_date DESC'),
                'conditions' => array(
                    'CertificateData.id' => $certificate_data_id,
                    'CertificateData.deleted' => 0,
                ),
            );

            $certificate_data = $this->Examiner->Certificate->CertificateData->find('first', $certificate_data_option);

            // Test ob es diese Qualifikation mit höherem Level schon gibt
            $qualification_test = $this->Examiner->Certificate->find(
                'first',
                array('conditions' => array(
                    'Certificate.examiner_id' => $certificate_data['Certificate']['examiner_id'],
                    'Certificate.sector' => $certificate_data['Certificate']['sector'],
                    'Certificate.testingmethod' => $certificate_data['Certificate']['testingmethod'],
                    'Certificate.level >=' => $certificate_data['Certificate']['level'],
                    'Certificate.id !=' => $certificate_data['Certificate']['id'],
                    'Certificate.active' => 1,
                ),
                )
            );
            if (count($qualification_test) > 0) {
                $qualification_data_test = $this->Examiner->Certificate->CertificateData->find(
                    'first',
                    array(
                        'order' => array('CertificateData.certified_date DESC'),
                        'conditions' => array(
                            'CertificateData.certificate_id' => $qualification_test['Certificate']['id'],
                        ),
                    )
                );
            } else {
                $qualification_data_test = array();
            }

            if (count($qualification_test) > 0 && $this->request->data['CertificateData']['active'] > 0 && count($qualification_data_test) > 0 && $qualification_data_test['CertificateData']['active'] > 0) {
                $this->Flash->error(__('The certificate infos could not be saved.') . ' ' . __('There is a qualification with the same testingmethod and a higher level.'), array('key' => 'error'));
            } else {
                unset($this->request->data['Order']);

                $this->request->data['CertificateData']['user_id'] = $this->Auth->user('id');
                $this->request->data['Certificate']['id'] = $certificate_id;

                $CertificateSave = $this->Examiner->Certificate->save($this->request->data);
                $CertificateDataSave = $this->Examiner->Certificate->CertificateData->save($this->request->data);

                if ($CertificateSave != false && $CertificateDataSave != false) {
                    $message = __('The certificate infos has been saved');

                    // Test ob es diese Qualifikation mit niedrigerem Level gibt
                    // diese wird deaktiviert wenn die Aktuelle aktiv ist
                    $qualification_test = $this->Examiner->Certificate->find(
                        'first',
                        array('conditions' => array(
                            'Certificate.examiner_id' => $certificate_data['Certificate']['examiner_id'],
                            'Certificate.sector' => $certificate_data['Certificate']['sector'],
                            'Certificate.testingmethod' => $certificate_data['Certificate']['testingmethod'],
                            'Certificate.level <' => $certificate_data['Certificate']['level'],
                            'Certificate.id !=' => $certificate_data['Certificate']['id'],
                            'Certificate.active' => 1,
                        ),
                        )
                    );
                    if (count($qualification_test) > 0) {
                        $this->Examiner->Certificate->CertificateData->recursive = -1;

                        $qualification_data_test = $this->Examiner->Certificate->CertificateData->find(
                            'first',
                            array(
                                'order' => array('CertificateData.certified_date DESC'),
                                'conditions' => array(
                                    'CertificateData.certificate_id' => $qualification_test['Certificate']['id'],
                                ),
                            )
                        );
                        if ($this->request->data['CertificateData']['active'] > 0 && $qualification_data_test['CertificateData']['active'] > 0) {
                            $qualification_data_test['CertificateData']['active'] = 0;
                            if ($this->Examiner->Certificate->CertificateData->save($qualification_data_test)) {
                                $message .= ' ' . __('A qualification with the same testingmethod and a lower level was deactivated.');
                            } else {
                                $message .= ' ' . __('A qualification with the same testingmethod and a lower level could not be disabled.');
                            }
                        }

                        $this->Examiner->Certificate->CertificateData->recursive = 1;
                    }

                    $this->Flash->success($message, array('key' => 'success'));
                    $this->Autorisierung->Logger($certificate_data_id, $this->request->data['CertificateData']);

                    $FormName['controller'] = 'examiners';
                    $FormName['action'] = 'certificates';
                    $FormName['terms'] = implode('/', $this->request->projectvars['VarsArray']);

                    $this->set('FormName', $FormName);
                } else {
                    $this->Flash->success(__('The certificate infos could not be saved. Please, try again.'), array('key' => 'success'));
                }
            }
        }

        $certificate_data_option = array(
            'order' => array('certified_date DESC'),
            'conditions' => array(
                'CertificateData.id' => $certificate_data_id,
                'CertificateData.deleted' => 0,
//                                        'CertificateData.active' => 1
            ),
        );

        $certificate_data = $this->Examiner->Certificate->CertificateData->find('first', $certificate_data_option);

        $this->Qualification->SingleCertificateSummary($certificate_data, array('main' => 'Certificate', 'sub' => 'CertificateData'));

        if (isset($this->request->data['back']) && $this->request->data['back'] == 1) {
            $certificateinfo = 1;
        }

        if (!isset($this->request->data['CertificateData'])) {

            if($certificate_data['CertificateData']['certified'] == 0 && empty($certificate_data['CertificateData']['certified_date'])){

                $certificate_data['CertificateData']['certified_date'] = $certificate_data['CertificateData']['first_registration'];

            }

            $this->request->data = $certificate_data;
        }

        $arrayData['settings'] = $this->Xml->CollectXMLFromFile('CertificateData');
//        $arrayData = $this->Xml->DatafromXml('CertificateData', 'file', null);

        $certificate_data = $this->Qualification->IsRenewal($certificate_data);

        if (isset($certificate_data['CertificateData']['period'])) {

            $certificate_data = $this->Qualification->TimeHorizons($certificate_data, array('main' => 'Certificate', 'sub' => 'CertificateData'), $certificate_data['CertificateData']['period']);
        }

        $SettingsArray = array();

        $this->set('SettingsArray', $SettingsArray);
        $this->set('certificate_data', $certificate_data);
        $this->set('settings', $arrayData['settings']);
    }
/*
    public function linking()
    {
        $this->layout = 'modal';

        $examiner_id = $this->Sicherheit->Numeric($this->request->params['pass']['15']);
        $certificate_id = $this->Sicherheit->Numeric($this->request->params['pass']['16']);
        $certificate_data_id = $this->Sicherheit->Numeric($this->request->params['pass']['17']);

        if (!$this->Examiner->exists($examiner_id)) {
            throw new NotFoundException(__('Invalid examiner'));
        }

        if (!$this->Examiner->Certificate->exists($certificate_id)) {
            throw new NotFoundException(__('Invalid certificate'));
        }

        if (!$this->Examiner->Certificate->CertificateData->exists($certificate_data_id)) {
            throw new NotFoundException(__('Invalid certificate info'));
        }

        if (isset($this->request->data['CertificateData']) && ($this->request->is('post') || $this->request->is('put'))) {
            $certificate_data_option = array(
                'order' => array('certified_date DESC'),
                'conditions' => array(
                    'CertificateData.id' => $certificate_data_id,
                    'CertificateData.deleted' => 0,
//                                        'CertificateData.active' => 1
                ),
            );

            $certificate_data = $this->Examiner->Certificate->CertificateData->find('first', $certificate_data_option);

            // Test ob es diese Qualifikation mit höherem Level schon gibt
            $qualification_test = $this->Examiner->Certificate->find(
                'first',
                array('conditions' => array(
                    'Certificate.examiner_id' => $certificate_data['Certificate']['examiner_id'],
                    'Certificate.sector' => $certificate_data['Certificate']['sector'],
                    'Certificate.testingmethod' => $certificate_data['Certificate']['testingmethod'],
                    'Certificate.level >=' => $certificate_data['Certificate']['level'],
                    'Certificate.id !=' => $certificate_data['Certificate']['id'],
                    'Certificate.active' => 1,
                ),
                )
            );
            if (count($qualification_test) > 0) {
                $qualification_data_test = $this->Examiner->Certificate->CertificateData->find(
                    'first',
                    array(
                        'order' => array('CertificateData.certified_date DESC'),
                        'conditions' => array(
                            'CertificateData.certificate_id' => $qualification_test['Certificate']['id'],
                        ),
                    )
                );
            } else {
                $qualification_data_test = array();
            }

            if (count($qualification_test) > 0 && $this->request->data['CertificateData']['active'] > 0 && count($qualification_data_test) > 0 && $qualification_data_test['CertificateData']['active'] > 0) {
                $this->Flash->error(__('The certificate infos could not be saved.') . ' ' . __('There is a qualification with the same testingmethod and a higher level.'), array('key' => 'error'));
            } else {
                unset($this->request->data['Order']);

                $this->request->data['CertificateData']['user_id'] = $this->Auth->user('id');

                if ($this->Examiner->Certificate->CertificateData->save($this->request->data)) {
                    $message = __('The certificate infos has been saved');

                    // Test ob es diese Qualifikation mit niedrigerem Level gibt
                    // diese wird deaktiviert wenn die Aktuelle aktiv ist
                    $qualification_test = $this->Examiner->Certificate->find(
                        'first',
                        array('conditions' => array(
                            'Certificate.examiner_id' => $certificate_data['Certificate']['examiner_id'],
                            'Certificate.sector' => $certificate_data['Certificate']['sector'],
                            'Certificate.testingmethod' => $certificate_data['Certificate']['testingmethod'],
                            'Certificate.level <' => $certificate_data['Certificate']['level'],
                            'Certificate.id !=' => $certificate_data['Certificate']['id'],
                            'Certificate.active' => 1,
                        ),
                        )
                    );
                    if (count($qualification_test) > 0) {
                        $this->Examiner->Certificate->CertificateData->recursive = -1;

                        $qualification_data_test = $this->Examiner->Certificate->CertificateData->find(
                            'first',
                            array(
                                'order' => array('CertificateData.certified_date DESC'),
                                'conditions' => array(
                                    'CertificateData.certificate_id' => $qualification_test['Certificate']['id'],
                                ),
                            )
                        );
                        if ($this->request->data['CertificateData']['active'] > 0 && $qualification_data_test['CertificateData']['active'] > 0) {
                            $qualification_data_test['CertificateData']['active'] = 0;
                            if ($this->Examiner->Certificate->CertificateData->save($qualification_data_test)) {
                                $message .= ' ' . __('A qualification with the same testingmethod and a lower level was deactivated.');
                            } else {
                                $message .= ' ' . __('A qualification with the same testingmethod and a lower level could not be disabled.');
                            }
                        }

                        $this->Examiner->Certificate->CertificateData->recursive = 1;
                    }

                    $this->Flash->success($message, array('key' => 'success'));
                    $this->Autorisierung->Logger($certificate_data_id, $this->request->data['CertificateData']);

                    $FormName['controller'] = 'examiners';
                    $FormName['action'] = 'certificates';
                    $FormName['terms'] = implode('/', $this->request->projectvars['VarsArray']);

                    $this->set('FormName', $FormName);
                } else {
                    $this->Flash->error(__('The certificate infos could not be saved. Please, try again.'), array('key' => 'error'));
                }
            }
        }

        $certificate_data_option = array(
            'order' => array('certified_date DESC'),
            'conditions' => array(
                'CertificateData.id' => $certificate_data_id,
                'CertificateData.deleted' => 0,
//                                        'CertificateData.active' => 1
            ),
        );

        $certificate_data = $this->Examiner->Certificate->CertificateData->find('first', $certificate_data_option);

        $this->Qualification->SingleCertificateSummary($certificate_data, array('main' => 'Certificate', 'sub' => 'CertificateData'));

        if (isset($this->request->data['back']) && $this->request->data['back'] == 1) {
            $certificateinfo = 1;
        }

        $arrayData = $this->Xml->DatafromXml('CertificateData', 'file', null);

        $certificate_data = $this->Qualification->IsRenewal($certificate_data);
        $certificate_data = $this->Qualification->TimeHorizons($certificate_data, array('main' => 'Certificate', 'sub' => 'CertificateData'), $certificate_data['CertificateData']['period']);
        $certificate_data = $this->Data->BelongsToManySelected($certificate_data, 'Certificate', 'Testingmethod', array('TestingmethodsCertificates', 'certificate_id', 'testingmethod_id'));

        $this->loadModel('Topproject');
        $this->Topproject->recursive = -1;

        $Topprojects = $this->Topproject->find('all', array('conditions' => array('Topproject.id' => $this->Autorisierung->ConditionsTopprojects())));

        $testingmethods = array();
        $testingmethod_id = array();

        foreach ($Topprojects as $_key => $_topproject) {
            $ReportIDs = $this->Data->BelongsToManySelected($_topproject, 'Topproject', 'Report', array('ReportsTopprojects', 'report_id', 'topproject_id'));
            $reports = $this->Topproject->Report->find('all', array('conditions' => array('Report.id' => $ReportIDs['Report']['selected'])));
            $Topprojects[$_key]['Report'] = $reports;
            if (count($Topprojects[$_key]['Report']) > 0) {
                foreach ($Topprojects[$_key]['Report'] as $__key => $__report) {
                    $TestingmethodIDs = $this->Data->BelongsToManySelected($__report, 'Report', 'Testingmethod', array('TestingmethodsReports', 'testingmethod_id', 'report_id'));
                    if (count($TestingmethodIDs['Testingmethod']['selected']) > 0) {
                        $testingmethods = $this->Topproject->Report->Testingmethod->find('all', array('conditions' => array('Testingmethod.id' => $TestingmethodIDs['Testingmethod']['selected'])));
                        $TestingmethodId = Hash::extract($testingmethods, '{n}.Testingmethod.id');
                        $TestingmethodValue = Hash::combine($testingmethods, '{n}.Testingmethod.id', '{n}.Testingmethod.value');
                        $TestingmethodNames = Hash::combine($testingmethods, '{n}.Testingmethod.id', '{n}.Testingmethod.verfahren');
                        $testingmethod_id = array_merge($testingmethod_id, $TestingmethodId);
                    }
                }
            }
        }

        $testingmethod_id = array_unique($testingmethod_id);

        $Testingmethods = $this->Examiner->Certificate->Testingmethod->find('list', array('fields' => array('verfahren', 'id'), 'conditions' => array('Testingmethod.id' => $testingmethod_id)));

        if (!isset($this->request->data['CertificateData'])) {
            $this->request->data = $certificate_data;
        }

        $SettingsArray = array();

        $this->set('SettingsArray', $SettingsArray);
        $this->set('certificate_data', $certificate_data);
        $this->set('settings', $arrayData['orderoutput']);
        $this->set('Testingmethods', $Testingmethods);
    }
*/
    public function askcertification()
    {
        $this->layout = 'modal';

        $examiner_id = $this->Sicherheit->Numeric($this->request->params['pass']['15']);
        $certificate_id = $this->Sicherheit->Numeric($this->request->params['pass']['16']);
        $certificate_data_id = $this->Sicherheit->Numeric($this->request->params['pass']['17']);

        if (!$this->Examiner->exists($examiner_id)) {
            throw new NotFoundException(__('Invalid examiner'));
        }

        if (!$this->Examiner->Certificate->exists($certificate_id)) {
            throw new NotFoundException(__('Invalid certificate'));
        }

        if (!$this->Examiner->Certificate->CertificateData->exists($certificate_data_id)) {
            throw new NotFoundException(__('Invalid certificate info'));
        }

        if (isset($this->request->data['CertificateData']) && ($this->request->is('post') || $this->request->is('put'))) {
            unset($this->request->data['Order']);

            $this->request->data['CertificateData']['user_id'] = $this->Auth->user('id');

            if ($this->Examiner->Certificate->CertificateData->save($this->request->data)) {
                $this->Flash->success(__('The certificate infos has been saved'), array('key' => 'succsess'));
                $this->Autorisierung->Logger($certificate_data_id, $this->request->data['CertificateData']);
            } else {
                $this->Flash->error(__('The certificate infos could not be saved. Please, try again.'), array('key' => 'error'));
            }

            $FormName['controller'] = 'examiners';
            $FormName['action'] = 'certificates';
            $FormName['terms'] = implode('/', $this->request->projectvars['VarsArray']);

            $this->set('FormName', $FormName);
        }

        $certificate_data_option = array(
            'order' => array('certified_date DESC'),
            'conditions' => array(
                'CertificateData.id' => $certificate_data_id,
                'CertificateData.deleted' => 0,
//                                        'CertificateData.active' => 1
            ),
        );

        $certificate_data = $this->Examiner->Certificate->CertificateData->find('first', $certificate_data_option);

        $this->Qualification->SingleCertificateSummary($certificate_data, array('main' => 'Certificate', 'sub' => 'CertificateData'));

        if (isset($this->request->data['back']) && $this->request->data['back'] == 1) {
            $certificateinfo = 1;
        }

        $this->request->data = $certificate_data;

//        $arrayData = $this->Xml->DatafromXml('CertificateDataAsk', 'file', null);
        $arrayData['settings'] = $this->Xml->CollectXMLFromFile('CertificateDataAsk');

        $certificate_data = $this->Qualification->IsRenewal($certificate_data);

        $certificate_data = $this->Qualification->TimeHorizons($certificate_data, array('main' => 'Certificate', 'sub' => 'CertificateData'), $certificate_data['CertificateData']['period'] ?? $certificate_data['CertificateData']['expiration_date']);

        $SettingsArray['backlink'] = array('discription' => __('Back', true), 'controller' => 'examiners', 'action' => 'editcertificate', 'terms' => $this->request->projectvars['VarsArray']);

        $this->set('certificate_data', $certificate_data);
        $this->set('settings', $arrayData['settings']);
        $this->set('SettingsArray', $SettingsArray);

        if (isset($certificateinfo) && $certificateinfo == 1) {
            $this->set('certificate_data', $certificate_data);
            $this->render('certificateinfo', 'blank');
        }
    }

    public function replacecertificate()
    {
        $model = array('main' => 'Certificate', 'sub' => 'CertificateData');

        if (isset($this->request->data[$model['sub']]) && ($this->request->is('post') || $this->request->is('put'))) {
            $FormName['controller'] = 'examiners';
            $FormName['action'] = 'certificates';
            $FormName['terms'] = implode('/', $this->request->projectvars['VarsArray']);

            $this->set('FormName', $FormName);
        }

        $this->_replacecertificate($model, 'certificate_folder', 'certificateinfo', 'certificate_data');
    }

    public function replaceeyecheck()
    {

        $this->layout = 'modal';

        $examiner_id = $this->Sicherheit->Numeric($this->request->params['pass']['15']);
        $certificate_id = $this->Sicherheit->Numeric($this->request->params['pass']['16']);
        $certificate_data_id = $this->Sicherheit->Numeric($this->request->params['pass']['17']);

        if (!$this->Examiner->exists($examiner_id)) {
            throw new NotFoundException(__('Invalid examiner'));
        }

        if (!$this->Examiner->Eyecheck->exists($certificate_id)) {
            throw new NotFoundException(__('Invalid certificate'));
        }

        if (!$this->Examiner->Eyecheck->EyecheckData->exists($certificate_data_id)) {
            throw new NotFoundException(__('Invalid certificate info'));
        }
 
        if (isset($this->request->data['EyecheckData']) && ($this->request->is('post') || $this->request->is('put'))) {

            $message = null;

            $certificate_data_option = array(
                'conditions' => array(
                    'Eyecheck.examiner_id' => $examiner_id,
                ),
            );
    
            $certificate_data = $this->Examiner->Eyecheck->find('first', $certificate_data_option);    

            $Insert = $this->request->data;

            unset($Insert['EyecheckData']['id']);
            unset($Insert['EyecheckData'][0]);

            $Insert['EyecheckData']['user_id'] = $this->Auth->user('id');
            $Insert['EyecheckData']['examiner_id'] = $certificate_data['Eyecheck']['examiner_id'];
            $Insert['EyecheckData']['first_registration'] = $certificate_data['Eyecheck']['first_registration'];
            $Insert['EyecheckData']['first_certification'] = 0;
            $Insert['EyecheckData']['user_id'] = $this->Auth->user('id');            

            if ($this->Examiner->Eyecheck->EyecheckData->save($Insert)) {

                $message .= __('The vision test has been saved');

                $this->Flash->success($message, array('key' => 'success'));

                $id = $this->Examiner->Eyecheck->EyecheckData->getLastInsertId();

                $this->Autorisierung->Logger($id, $Insert['EyecheckData']);

                $certificate_data_data = array(
                    'id' => $certificate_data_id,
                    'active' => 0,
                    'user_id' => $this->Auth->user('id'),
                );

                $this->Examiner->Eyecheck->EyecheckData->save($certificate_data_data);

                $certificate_data = $this->Examiner->Eyecheck->EyecheckData->find('first',array(
                    'conditions' => array(
                        'EyecheckData.id' => $id
                        )
                    )
                );
    
                $VarsArray = $this->request->projectvars['VarsArray'];

                $VarsArray[15] = $certificate_data['Examiner']['id'];
                $VarsArray[16] = $certificate_data['Eyecheck']['id'];
                $VarsArray[17] = $certificate_data['EyecheckData']['id'];

                $FormName['controller'] = 'examiners';
                $FormName['action'] = 'eyechecks';
                $FormName['terms'] = implode('/', $VarsArray);
    
                $this->set('FormName', $FormName);
                $this->set('certificate_data', $certificate_data);
                return;

            } else {
                $this->Flash->error(__('The vision test could not be saved. Please, try again.'), array('key' => 'error'));
            }
        }

/*
        if (isset($this->request->data[$model['sub']]) && ($this->request->is('post') || $this->request->is('put'))) {
            $FormName['controller'] = 'examiners';
            $FormName['action'] = 'eyechecks';
            $FormName['terms'] = implode('/', $this->request->projectvars['VarsArray']);

            $this->set('FormName', $FormName);
        }
 */

        $eyecheck_data_option = array(
            'order' => array('certified_date DESC'),
            'conditions' => array(
                'EyecheckData.id' => $certificate_data_id,
                'EyecheckData.deleted' => 0,
                'EyecheckData.active' => 1,
            ),
        );

        $eyecheck_data = $this->Examiner->Eyecheck->EyecheckData->find('first', $eyecheck_data_option);

        $eyecheck_data['EyecheckData']['certified_date'] = $eyecheck_data['EyecheckData']['expiration_date'];

        $eyecheck_data['EyecheckData']['expiration_date'] = '';

        $this->request->data = $eyecheck_data;

        $arrayData['settings'] = $this->Xml->CollectXMLFromFile('EyecheckData');

        $SettingsArray = array();

        $this->Flash->warning(__('Replace vision test'), array('key' => 'warning'));

        $this->set('SettingsArray', $SettingsArray);
        $this->set('settings', $arrayData['settings']);
        
    }

    protected function _replacecertificate($model, $folder, $target, $varname)
    {
        $this->layout = 'modal';
        $main = $model['main'];
        $sub = $model['sub'];
        $examiner_id = $this->Sicherheit->Numeric($this->request->params['pass']['15']);
        $certificate_id = $this->Sicherheit->Numeric($this->request->params['pass']['16']);
        $certificate_data_id = $this->Sicherheit->Numeric($this->request->params['pass']['17']);

        if (!$this->Examiner->exists($examiner_id)) {
            throw new NotFoundException(__('Invalid examiner'));
        }

        if (!$this->Examiner->$main->exists($certificate_id)) {
            throw new NotFoundException(__('Invalid certificate'));
        }

        if (isset($this->request->data[$sub]) && ($this->request->is('post') || $this->request->is('put'))) {
            $certificat_test = $this->Examiner->$main->$sub->find('first', array('conditions' => array($sub . '.id' => $certificate_data_id)));

            if ($this->request->data[$sub]['certified_date'] < $certificat_test[$sub]['certified_date']) {
                $message = __('Information could not be saved.');
                $message .= ' ' . __('The date is too far in the past.');
                $this->Flash->error($message, array('key' => 'error'));
                $this->set('certificate_data', $certificat_test);
                $this->render('error_modal', 'modal');
                return false;
            }

            $this->Examiner->$main->$sub->updateAll(
                array(
                    $model['sub'] . '.active' => 0,
                ),
                array(
                    $model['sub'] . '.certificate_id' => $certificate_id,
                )
            );

            unset($this->request->data['Order']);

            $certificat = $this->Examiner->$main->find('first', array('conditions' => array($model['main'] . '.id' => $certificate_id)));

            if (isset($certificat[$model['main']]['testingmethod'])) {
                $this->request->data[$model['sub']]['testingmethod'] = $certificat[$model['main']]['testingmethod'];
            }

            $this->request->data[$model['sub']]['first_certification'] = $certificat[$model['main']]['first_certification'];
            $this->request->data[$model['sub']]['first_registration'] = $certificat[$model['main']]['first_registration'];
            $this->request->data[$model['sub']]['renewal_in_year'] = $certificat[$model['main']]['renewal_in_year'];
            $this->request->data[$model['sub']]['recertification_in_year'] = $certificat[$model['main']]['recertification_in_year'];
            $this->request->data[$model['sub']]['user_id'] = $this->Auth->user('id');

            unset($this->request->data[$model['sub']]['id']);

            if ($this->Examiner->$main->$sub->save($this->request->data)) {

                $certificate_data_id = $this->Examiner->$main->$sub->getLastInsertId();
                $this->Flash->success(__('The infos has been saved'), array('key' => 'success'));
                $this->Autorisierung->Logger($certificate_data_id, $this->request->data[$model['sub']]);

            } else {
                $this->Flash->error(__('The infos could not be saved. Please, try again.'), array('key' => 'error'));
            }
        }

        $certificate_data_option = array(
            'order' => array('certified_date DESC'),
            'conditions' => array(
                $model['sub'] . '.id' => $certificate_data_id,
                $model['sub'] . '.deleted' => 0,
                $model['sub'] . '.active' => 1,
            ),
        );

        $certificate_old_data_option = array(
            'order' => array('certified_date DESC'),
            'conditions' => array(
                $model['sub'] . '.certificate_id' => $certificate_id,
                $model['sub'] . '.deleted' => 0,
                $model['sub'] . '.active' => 1,
            ),
        );

        $certificate_data = $this->Examiner->$main->$sub->find('first', $certificate_data_option);
        $certificate_data_old = $this->Examiner->$main->$sub->find('first', $certificate_old_data_option);

        if (count($certificate_data) == 0) {
            $this->request->data[$model['sub']]['examiner_id'] = $examiner_id;
            $this->request->data[$model['sub']]['certificate_id'] = $certificate_id;
            $this->request->data[$model['sub']]['recertification_in_year'] = $certificate_data_old[$model['main']]['recertification_in_year'];
            $this->request->data[$model['sub']]['renewal_in_year'] = $certificate_data_old[$model['main']]['renewal_in_year'];
            $this->request->data[$model['sub']]['horizon'] = $certificate_data_old[$model['main']]['horizon'];
            $this->request->data[$model['sub']]['apply_for_recertification'] = 0;
            $this->request->data[$model['sub']]['active'] = 1;
            $this->request->data[$model['sub']]['certified'] = 1;

            if ($this->request->data[$model['sub']]['recertification_in_year'] == 0) {
                $this->request->data[$model['sub']]['recertification_in_year'] = $certificate_data_old[$model['main']]['recertification_in_year'];
            }
        } else {
            $this->request->data = $certificate_data;
        }

        if (isset($this->request->data['CertificateData']['certified_date'])) {
            unset($this->request->data['CertificateData']['certified_date']);
        }

        $this->request->data['EyecheckData']['vt'] = 0;
        $this->request->data['EyecheckData']['pt'] = 0;
        $this->request->data['EyecheckData']['mt'] = 0;
        $this->request->data['EyecheckData']['ut'] = 0;
        $this->request->data['EyecheckData']['sv'] = 0;
        $this->request->data['EyecheckData']['near_vision'] = 0;
        $this->request->data['EyecheckData']['color_contrast'] = 0;
        $this->request->data['EyecheckData']['anomalies'] = 0;
        $this->request->data['EyecheckData']['face'] = 0;
        $this->request->data['EyecheckData']['distance_vision'] = 0;
        $this->request->data['EyecheckData']['landolt'] = 0;
        $this->request->data['EyecheckData']['one_eye'] = 0;
        $this->request->data['EyecheckData']['different_way'] = 0;

        $this->request->data['EyecheckData']['glasses'] = 0;

        $arrayData['settings'] = $this->Xml->CollectXMLFromFile($model['sub']);

        $this->Qualification->SingleCertificateSummary($certificate_data_old, $model);
        $this->set('certificate_data', $certificate_data);
        $this->set('certificate_data_old', $certificate_data_old);
        $this->set('settings', $arrayData['settings']);
    }

    public function delcertificate()
    {
        $this->layout = 'blank';

        $examiner_id = $this->Sicherheit->Numeric($this->request->params['pass']['15']);
        $certificate_id = $this->Sicherheit->Numeric($this->request->params['pass']['16']);
        $certificate_data_id = $this->Sicherheit->Numeric($this->request->params['pass']['17']);

        if (!$this->Examiner->exists($examiner_id)) {
            throw new NotFoundException(__('Invalid examiner'));
        }

        if (!$this->Examiner->Certificate->exists($certificate_id)) {
            throw new NotFoundException(__('Invalid certificate'));
        }

        if (!$this->Examiner->Certificate->CertificateData->exists($certificate_data_id)) {
            throw new NotFoundException(__('Invalid certificate info'));
        }

        $this->set('hint', __('Will you delete the certificate info from this certifikate? The certificate will then be invalid.', true));
        $this->set('del_button', __('Delete', true));

        $certificate_data_option = array(
            'order' => array('certified_date DESC'),
            'conditions' => array(
                'CertificateData.id' => $certificate_data_id,
                'CertificateData.deleted' => 0,
//                                        'CertificateData.active' => 1
            ),
        );

        if (isset($this->request->data['CertificateData']) && ($this->request->is('post') || $this->request->is('put'))) {
            unset($this->request->data['Order']);

            $this->request->data['CertificateData']['deleted'] = 1;
            $this->request->data['CertificateData']['active'] = 0;
            $this->request->data['CertificateData']['certified'] = 0;
            $this->request->data['CertificateData']['certified_file'] = '';
            $this->request->data['CertificateData']['user_id'] = $this->Auth->user('id');

            if ($this->Examiner->Certificate->CertificateData->save($this->request->data)) {

                $path = Configure::read('certificate_folder') . $examiner_id . DS . $certificate_id . DS . $certificate_data_id . DS;
                $folder = new Folder($path);
                $folder->delete();

                $certificate_data_option = array(
                    'order' => array('certified_date DESC'),
                    'conditions' => array(
                        'CertificateData.certificate_id' => $certificate_id,
                        'CertificateData.deleted' => 0,
//                                        'CertificateData.active' => 1
                    ),
                );

                $this->Autorisierung->Logger($certificate_data_id, $this->request->data['CertificateData']);

                $this->request->projectvars['VarsArray'][15] = $examiner_id;
                $this->request->projectvars['VarsArray'][16] = $certificate_id;
                $this->request->projectvars['VarsArray'][17] = 0;
                unset($this->request->data['back']);
                unset($this->request->data['CertificateData']);

                $this->_include_replacecertificate();
                $this->render('replacecertificate');
                return;
            } else {
                $this->Flash->error(__('The certificate infos could not be delete. Please, try again.'), array('key' => 'error'));
            }
        }

        $certificate_data = $this->Examiner->Certificate->CertificateData->find('first', $certificate_data_option);

        if (isset($this->request->data['back']) && $this->request->data['back'] == 1) {
            $certificateinfo = 1;
        }

        $this->request->data = $certificate_data;

        $arrayData = $this->Xml->DatafromXml('CertificateData', 'file', null);

        $this->set('certificate_data', $certificate_data);
        $this->set('settings', $arrayData['orderoutput']);

        if (isset($certificateinfo) && $certificateinfo == 1) {
            $certificate_data = $this->Qualification->IsRenewal($certificate_data);

            $certificate_data = $this->Qualification->TimeHorizons($certificate_data, array('main' => 'Certificate', 'sub' => 'CertificateData'), $certificate_data['CertificateData']['period']);

            $this->set('certificate_data', $certificate_data);
            $this->render('certificateinfo', 'blank');
        }
    }

    public function eyecheckfile()
    {
        $this->layout = 'modal';

        $examiner_id = $this->Sicherheit->Numeric($this->request->params['pass']['15']);
        $certificate_id = $this->Sicherheit->Numeric($this->request->params['pass']['16']);
        $certificate_data_id = $this->Sicherheit->Numeric($this->request->params['pass']['17']);

        $this->request->projectvars['VarsArray'][17] = $certificate_data_id;

        $SettingsArray = array();
        $SettingsArray['backlink'] = array('discription' => __('Back', true), 'controller' => 'examiners', 'action' => 'editeyecheck', 'terms' => $this->request->projectvars['VarsArray']);

        $this->set('SettingsArray', $SettingsArray);

        $hint = __('Eine vorhandene Datei wird mit dem Upload überschrieben.', true);
        $this->Flash->hint($hint, array('key' => 'hint'));

        $models = array('main' => 'Eyecheck', 'sub' => 'EyecheckData', 'file' => 'Eyecheckfile');
        $path = Configure::read('eyecheck_folder') . $examiner_id . DS . $certificate_id . DS . $certificate_data_id . DS;

        $main = $models['main'];
        $file = $models['file'];
        $sub = $models['sub'];

        $_data_option = array(
            'conditions' => array(
                $models['sub'] . '.id' => $certificate_data_id,
                $models['sub'] . '.certificate_id' => $certificate_id,
                $models['sub'] . '.deleted' => 0,
//                        $models['sub'] . '.active' => 1
            ),
        );

        $_data = $this->Examiner->$main->$sub->find('first', $_data_option);

        $this->set('data', $_data);
        $this->set('models', $models);

        if (isset($_FILES) && count($_FILES) > 0) {

            $this->_fileupload($path, $models);

            unset($this->request->projectvars['VarsArray'][16]);
            unset($this->request->projectvars['VarsArray'][17]);

            $FormName['controller'] = 'examiners';
            $FormName['action'] = 'eyechecks';
            $FormName['terms'] = implode('/', $this->request->projectvars['VarsArray']);

            $hint = __('Die Datei wurde gespeichert.', true);

            $this->Flash->success($hint, array('key' => 'success'));

            $this->set('FormName', $FormName);
        }

        if (isset($this->request->data['close_reload']) && $this->request->data['close_reload'] == 1) {

            unset($this->request->projectvars['VarsArray'][16]);
            unset($this->request->projectvars['VarsArray'][17]);

            $FormName['controller'] = 'examiners';
            $FormName['action'] = 'eyechecks';
            $FormName['terms'] = implode('/', $this->request->projectvars['VarsArray']);

            $hint = __('Die Datei wurde gespeichert.', true);
            $this->Flash->success($hint, array('key' => 'success'));

            $this->set('FormName', $FormName);

        }

    }

    public function certificatefile()
    {
        $this->layout = 'modal';

        $examiner_id = $this->Sicherheit->Numeric($this->request->params['pass']['15']);
        $certificate_id = $this->Sicherheit->Numeric($this->request->params['pass']['16']);
        $certificate_data_id = $this->Sicherheit->Numeric($this->request->params['pass']['17']);

        $SettingsArray = array();

        $this->set('SettingsArray', $SettingsArray);

        $hint = __('Eine vorhandene Datei wird mit dem Upload überschrieben.', true);
        $this->set('hint', $hint);
        $this->set('message_class', 'hint');

        if (isset($this->request['data']['Examiner']['file'])) {
            $FormName['controller'] = 'examiners';
            $FormName['action'] = 'certificates';
            $FormName['terms'] = implode('/', $this->request->projectvars['VarsArray']);

            $this->set('FormName', $FormName);
        }

        $models = array('main' => 'Certificate', 'sub' => 'CertificateData', 'file' => 'Certifcatefile');

        $path = Configure::read('certificate_folder') . $examiner_id . DS . $certificate_id . DS . $certificate_data_id . DS;
        $this->_fileupload($path, $models);

        if (isset($this->request->data['close_reload']) && $this->request->data['close_reload'] == 1) {

            unset($this->request->projectvars['VarsArray'][16]);
            unset($this->request->projectvars['VarsArray'][17]);

            $FormName['controller'] = 'examiners';
            $FormName['action'] = 'certificates';
            $FormName['terms'] = implode('/', $this->request->projectvars['VarsArray']);

            $hint = __('Die Datei wurde gespeichert.', true);
            $this->set('hint', $hint);
            $this->set('message_class', 'success');

            $this->set('FormName', $FormName);

        }

    }

    protected function _fileupload($path, $models)
    {

        $examiner_id = $this->Sicherheit->Numeric($this->request->params['pass']['15']);
        $certificate_id = $this->Sicherheit->Numeric($this->request->params['pass']['16']);
        $certificate_data_id = $this->Sicherheit->Numeric($this->request->params['pass']['17']);

        $main = $models['main'];
        $file = $models['file'];
        $sub = $models['sub'];

        $_data_option = array(
            'conditions' => array(
                $models['sub'] . '.id' => $certificate_data_id,
                $models['sub'] . '.certificate_id' => $certificate_id,
                $models['sub'] . '.deleted' => 0,
//                        $models['sub'] . '.active' => 1
            ),
        );

        $_data = $this->Examiner->$main->$sub->find('first', $_data_option);

        if ($_data[$models['sub']]['certified_file'] != '' && file_exists($path . $_data[$models['sub']]['certified_file'])) {
            $this->set('hint', __('Do you want to overwrite the existing file?', true));
        }

        $this->set('data', $_data);
        $this->set('models', $models);

        $Extension = 'pdf';

        if (isset($_FILES) && count($_FILES) > 0) {

            // Infos zur Datei
            $fileinfo = pathinfo($_FILES['file']['name']);
            $filename_new = $examiner_id . '_' . $certificate_id . '_' . $certificate_data_id . '_' . time() . '.' . $fileinfo['extension'];

            if ($Extension == strtolower($fileinfo['extension'])) {
                if (isset($_FILES['error']['file'])) {
                    $this->Session->write('FileUploadErrors', $_FILES['error']['file']);
                }

                // Vorhandene Dateien werden gelöscht
                if ($_FILES['file']['error'] == 0) {

                    $folder = new Folder($path);
                    $folder->delete();

                    if (!file_exists($path)) {
                        $dir = new Folder($path, true, 0755);
                    }

                }

                $this->Session->write('FileUploadErrors', $_FILES['file']['error']);

                move_uploaded_file($_FILES['file']["tmp_name"], $path . DS . $filename_new);

                if ($_FILES['file']['error'] == 0) {
                    $dataFiles = array(
                        'id' => $certificate_data_id,
                        'certified_file' => $filename_new,
                        'user_id' => $this->Auth->user('id'),
                    );

                    $this->Examiner->$main->$sub->save($dataFiles);
                }
            }
        }
    }

    public function getcertificatefile()
    {
        $examiner_id = $this->Sicherheit->Numeric($this->request->params['pass']['15']);
        $certificate_id = $this->Sicherheit->Numeric($this->request->params['pass']['16']);
        $certificate_data_id = $this->Sicherheit->Numeric($this->request->params['pass']['17']);
        $models = array('main' => 'Certificate', 'sub' => 'CertificateData');
        $path = Configure::read('certificate_folder') . $examiner_id . DS . $certificate_id . DS . $certificate_data_id . DS;
        $this->_getfile($path, $models);
    }

    public function geteyecheckfile()
    {
        $examiner_id = $this->Sicherheit->Numeric($this->request->params['pass']['15']);
        $certificate_id = $this->Sicherheit->Numeric($this->request->params['pass']['16']);
        $certificate_data_id = $this->Sicherheit->Numeric($this->request->params['pass']['17']);
        $models = array('main' => 'Eyecheck', 'sub' => 'EyecheckData');
        $path = Configure::read('eyecheck_folder') . $examiner_id . DS . $certificate_id . DS . $certificate_data_id . DS;
        $this->_getfile($path, $models);
    }

    protected function _getfile($path, $models)
    {
        $this->layout = 'blank';
        $main = $models['main'];
        $sub = $models['sub'];
        $examiner_id = $this->Sicherheit->Numeric($this->request->params['pass']['15']);
        $certificate_id = $this->Sicherheit->Numeric($this->request->params['pass']['16']);
        $certificate_data_id = $this->Sicherheit->Numeric($this->request->params['pass']['17']);
        $certificate_data_option = array(
            'order' => array('certified_date DESC'),
            'conditions' => array(
                $models['sub'] . '.id' => $certificate_data_id,
            ),
        );

        $certificate_data = $this->Examiner->$main->$sub->find('first', $certificate_data_option);

        $this->set('path', $path . $certificate_data[$models['sub']]['certified_file']);
    }

    public function delcertificatefile()
    {
        $model = array('main' => 'Certificate', 'sub' => 'CertificateData');

        if (isset($this->request->data[$model['sub']]) && ($this->request->is('post') || $this->request->is('put'))) {
            $FormName['controller'] = 'examiners';
            $FormName['action'] = 'certificates';
            $FormName['terms'] = implode('/', $this->request->params['pass']);

            $this->set('FormName', $FormName);
        }

        $SettingsArray = array();
        $SettingsArray['backlink'] = array('discription' => __('Back', true), 'controller' => 'examiners', 'action' => 'editcertificate', 'terms' => $this->request->params['pass']);

        $this->set('SettingsArray', $SettingsArray);
        $this->_delfile($model, 'certificate_folder', 'certificateinfo');
    }

    public function deleyecheckfile()
    {
        $model = array('main' => 'Eyecheck', 'sub' => 'EyecheckData');

        if (isset($this->request->data[$model['sub']]) && ($this->request->is('post') || $this->request->is('put'))) {
            $FormName['controller'] = 'examiners';
            $FormName['action'] = 'eyechecks';
            $FormName['terms'] = implode('/', $this->request->projectvars['VarsArray']);

            $this->set('FormName', $FormName);
        }

        $SettingsArray = array();
        $SettingsArray['backlink'] = array('discription' => __('Back', true), 'controller' => 'examiners', 'action' => 'eyecheck', 'terms' => $this->request->projectvars['VarsArray']);

        $this->set('SettingsArray', $SettingsArray);

        $this->_delfile($model, 'eyecheck_folder', 'eyecheckinfo');
    }

    protected function _delfile($model, $folder, $target)
    {
        $this->layout = 'modal';

        $examiner_id = $this->Sicherheit->Numeric($this->request->params['pass']['15']);
        $certificate_id = $this->Sicherheit->Numeric($this->request->params['pass']['16']);
        $certificate_data_id = $this->Sicherheit->Numeric($this->request->params['pass']['17']);

        if (!$this->Examiner->exists($examiner_id)) {
            throw new NotFoundException(__('Invalid examiner'));
        }

        if (!$this->Examiner->{$model['main']}->exists($certificate_id)) {
            throw new NotFoundException(__('Invalid certificate'));
        }

        if (!$this->Examiner->{$model['main']}->{$model['sub']}->exists($certificate_data_id)) {
            throw new NotFoundException(__('Invalid certificate info'));
        }

        $this->set('hint', __('Will you delete this file?', true));
        $this->set('del_button', __('Delete', true));

        if (isset($this->request->data[$model['sub']]) && ($this->request->is('post') || $this->request->is('put'))) {
            unset($this->request->data['Order']);

            $this->request->data[$model['sub']]['user_id'] = $this->Auth->user('id');

            if ($this->Examiner->{$model['main']}->{$model['sub']}->save($this->request->data)) {
                $path = Configure::read($folder) . $examiner_id . DS . $certificate_id . DS . $certificate_data_id . DS;
                $folder = new Folder($path);
                $folder->delete();

                $this->set('hint', __('The file has been delete', true));
                $this->Autorisierung->Logger($certificate_data_id, $this->request->data[$model['sub']]);
                $this->set('del_button', null);
            } else {
                $this->set('hint', __('The file could not be delete. Please, try again.', true));
            }
            /*
        $FormName['controller'] = 'examiners';
        $FormName['action'] = 'certificates';
        $FormName['terms'] = implode('/',$this->request->projectvars['VarsArray']);

        $this->set('FormName',$FormName);
         */
        }

        $certificate_data_option = array(
            'order' => array('certified_date DESC'),
            'conditions' => array(
                $model['sub'] . '.id' => $certificate_data_id,
            ),
        );

        $certificate_data = $this->Examiner->{$model['main']}->{$model['sub']}->find('first', $certificate_data_option);

        if (isset($this->request->data['back']) && $this->request->data['back'] == 1) {
            $certificateinfo = 1;
        }

        $this->request->data = $certificate_data;

        $arrayData = $this->Xml->DatafromXml($model['sub'], 'file', null);

        $this->set('certificate_data', $certificate_data);
        $this->set('settings', $arrayData['orderoutput']);

        if (isset($certificateinfo) && $certificateinfo == 1) {
            $certificate_data = $this->Qualification->IsRenewal($certificate_data);

            $certificate_data = $this->Qualification->TimeHorizons($certificate_data, $model, $certificate_data[$model['sub']]['period']);

            $this->set('certificate_data', $certificate_data);
            $this->render($target, 'blank');
        }
    }

    public function historyeyecheck()
    {
        $this->layout = 'modal';

        $examiner_id = $this->Sicherheit->Numeric($this->request->params['pass']['15']);
        $certificate_id = $this->Sicherheit->Numeric($this->request->params['pass']['16']);
        $path = Configure::read('eyecheck_folder');

        if (!$this->Examiner->exists($examiner_id)) {
            return;
        }

        if (!$this->Examiner->Eyecheck->exists($certificate_id)) {
            return;
        }

        $certificate_data_option = array(
            'order' => array('EyecheckData.id ASC'),
            'conditions' => array(
                'EyecheckData.certificate_id' => $certificate_id,
            ),
        );

        $certificate_datas = $this->Examiner->Eyecheck->EyecheckData->find('all', $certificate_data_option);

        if (count($certificate_datas) > 0) {
            $certificate_data = $certificate_datas[0];

            foreach ($certificate_datas as $_key => $_certificate_datas) {
                if ($_certificate_datas['EyecheckData']['certified_file'] != '') {
                    $filepath = $path . $_certificate_datas['Examiner']['id'] . DS . $_certificate_datas['Eyecheck']['id'] . DS . $_certificate_datas['EyecheckData']['id'] . DS . $_certificate_datas['EyecheckData']['certified_file'];
                    if (file_exists($filepath) == true) {
                        $certificate_datas[$_key]['EyecheckData']['file'] = true;
                    }
                }
            }
        }

        $SettingsArray = array();
        //        $SettingsArray['examinerlink'] = array('discription' => __('Back to list',true), 'controller' => 'examiners','action' => 'index', 'terms' => null);
        //        $SettingsArray['certificatelink'] = array('discription' => __('Examiner qualifications',true), 'controller' => 'examiners','action' => 'certificates', 'terms' => $this->request->projectvars['VarsArray']);

        $this->set('SettingsArray', $SettingsArray);
        $this->set('certificate_data', $certificate_data);
        $this->set('certificate_datas', $certificate_datas);
        $this->set('path', $path);
    }

    public function history()
    {
        $this->layout = 'modal';

        $examiner_id = $this->Sicherheit->Numeric($this->request->params['pass']['15']);
        $certificate_id = $this->Sicherheit->Numeric($this->request->params['pass']['16']);
        $path = Configure::read('certificate_folder');

        if (!$this->Examiner->exists($examiner_id)) {
            throw new NotFoundException(__('Invalid examiner'));
        }

        if (!$this->Examiner->Certificate->exists($certificate_id)) {
            throw new NotFoundException(__('Invalid certificate'));
        }

        $certificate_data_option = array(
            'order' => array('CertificateData.id ASC'),
            'conditions' => array(
                'CertificateData.certificate_id' => $certificate_id,
            ),
        );

        $certificate_datas = $this->Examiner->Certificate->CertificateData->find('all', $certificate_data_option);
        $certificate_datas_sectors = $certificate_datas;

        if (count($certificate_datas) > 0) {
            $certificate_data = $certificate_datas[0];
        }

        foreach ($certificate_datas_sectors as $_key => $_certificate_datas_sectors) {
            $certificate_datas_sectors[$_key] = $this->Qualification->CertificatesSectors($_certificate_datas_sectors);

            if ($_certificate_datas_sectors['CertificateData']['certified_file'] != '') {
                $filepath = $path . $_certificate_datas_sectors['Examiner']['id'] . DS . $_certificate_datas_sectors['Certificate']['id'] . DS . $_certificate_datas_sectors['CertificateData']['id'] . DS . $_certificate_datas_sectors['CertificateData']['certified_file'];
                if (file_exists($filepath) == true) {
                    $certificate_datas[$_key]['CertificateData']['file'] = true;
                }
            }
        }

        $SettingsArray = array();
        //        $SettingsArray['examinerlink'] = array('discription' => __('Back to list',true), 'controller' => 'examiners','action' => 'index', 'terms' => null);
        //        $SettingsArray['certificatelink'] = array('discription' => __('Examiner qualifications',true), 'controller' => 'examiners','action' => 'certificates', 'terms' => $this->request->projectvars['VarsArray']);

        $this->set('SettingsArray', $SettingsArray);
        $this->set('certificate_data', $certificate_data);
        $this->set('certificate_datas', $certificate_datas);
        $this->set('certificate_datas_sectors', $certificate_datas_sectors);
        $this->set('path', $path);
    }

    // kann wohl gelöscht werden
    public function visiontests()
    {
        $id = $this->request->projectvars['VarsArray'][13];

        if (!$this->Examiner->exists($id)) {
            throw new NotFoundException(__('Invalid examiner'));
        }

        $examiner = $this->Examiner->find('first', array('conditions' => array('Examiner.id' => $id)));

        $SettingsArray = array();
        $SettingsArray['backlink'] = array('discription' => __('Back', true), 'controller' => 'examiners', 'action' => 'overview', 'terms' => $this->request->projectvars['VarsArray']);
        $this->set('examiner', $examiner);
        $this->set('SettingsArray', $SettingsArray);
    }

    public function view()
    {
        $this->loadModel('Reportnumber');

        $order = $this->ExaminerTime->Order->find('first', array('conditions' => array('Order.id' => $this->request->projectvars['orderID'])));
        $this->set('order', $order);

        $this->set('examiner', array());

        if (isset($this->request->params['pass'][15]) && $this->request->params['pass'][15] > 0) {
            $id = $this->Sicherheit->Numeric($this->request->params['pass'][15]);
            $this->request->projectvars['VarsArray'][15] = $id;

            if (!$this->Examiner->exists($id)) {
                throw new NotFoundException(__('Invalid examiner'));
            }

            $options = array('conditions' => array(
                'Examiner.' . $this->Examiner->primaryKey => $id,
            ),
            );
            $this->ExaminerTime->recursive = 0;

            $examiner = $this->Examiner->find('first', $options);

            $this->set('examiner', $examiner);

            $id_condition = array('ExaminerTime.examiner_id' => $id);
        }

        if ($this->request->projectvars['reportID'] > 0) {
            $reportnumberID_condition = array('ExaminerTime.reportnumber_id' => $this->request->projectvars['reportnumberID']);
        }
        if ($this->request->projectvars['orderID'] > 0) {
            $orderID_condition = array('ExaminerTime.order_id' => $this->request->projectvars['orderID']);
        }

        if (isset($id_condition)) {
            $options['conditions'][] = $id_condition;
        }
        if (isset($reportnumberID_condition)) {
            $options['conditions'][] = $reportnumberID_condition;
        }
        if (isset($orderID_condition)) {
            $options['conditions'][] = $orderID_condition;
        }

        $examinertime = $this->ExaminerTime->find('all', array_merge($options, array('order' => array('date(ExaminerTime.testing_time_start) ASC', 'time(ExaminerTime.testing_time_start) ASC'))));

        $times = array();

        foreach ($examinertime as $_key => $_examinertime) {
            if (isset($this->request->projectvars['VarsArray'][15]) && $this->request->projectvars['VarsArray'][15] != 0) {
                $_examiner_id = $_examinertime['ExaminerTime']['examiner_id'];
            } else {
                $_examiner_id = 'all';
            }

            $timestamp_waitingtime = strtotime($_examinertime['ExaminerTime']['waiting_time_start']);
            $timestamp_testingtime = strtotime($_examinertime['ExaminerTime']['testing_time_start']);

            if (date('Y', $timestamp_testingtime) != '1970') {
                $times[$_examiner_id]['testing'][date('Y', $timestamp_testingtime)][date('m', $timestamp_testingtime)][date('d', $timestamp_testingtime)]['id'] = $_examinertime['ExaminerTime']['id'];
                if ($_examinertime['ExaminerTime']['collision'] != null) {
                    $times[$_examiner_id]['testing'][date('Y', $timestamp_testingtime)][date('m', $timestamp_testingtime)][date('d', $timestamp_testingtime)]['collision'] = 1;
                }
                if ($_examinertime['ExaminerTime']['collision'] == null) {
                    $times[$_examiner_id]['testing'][date('Y', $timestamp_testingtime)][date('m', $timestamp_testingtime)][date('d', $timestamp_testingtime)]['collision'] = 0;
                }
            }
            if (date('Y', $timestamp_waitingtime) != '1970') {
                $times[$_examiner_id]['waiting'][date('Y', $timestamp_waitingtime)][date('m', $timestamp_waitingtime)][date('d', $timestamp_waitingtime)]['id'] = $_examinertime['ExaminerTime']['id'];
            }
        }

        $this->set('times', $times);
        $this->set('examinerTime', $examinertime);

        $SettingsArray = array();
        $SettingsArray['backlink'] = array('discription' => __('Back', true), 'controller' => 'examiners', 'action' => 'overview', 'terms' => $this->request->projectvars['VarsArray']);
        $SettingsArray['addlink'] = array('discription' => __('Add', true), 'controller' => 'examiners', 'action' => 'add', 'terms' => null);
        //        $SettingsArray['printlink'] = array('discription' => __('Print workload'), 'controller'=>'examiners', 'action'=>'printworkload', 'terms' => array_merge($this->request->projectvars['VarsArray'], array($id)), 'class'=>'printlink');

        $this->set('SettingsArray', $SettingsArray);
    }

    private function _addUserInDropdownData($data)
    {
        $users = $this->Examiner->User->find('all', array('fields' => array('User.id', 'User.name', 'User.username')));
        $data['Users'] = Hash::combine($users, '{n}.User.id', array('%s', '{n}.User.name'));
        return $data;
    }

    private function _addTestingcompsInSelection()
    {
        $testingcomps = $this->Examiner->Testingcomp->find('list', array('conditions' => array('Testingcomp.id' => $this->Autorisierung->ConditionsTestinccomps())));
        return $testingcomps;
    }

    private function _getAssignedTestingcomps($examiner_id)
    {
        $examiner_testingcomps = $this->Examiner->ExaminerTestingcomp->find('all', array('conditions' => array('ExaminerTestingcomp.examiner_id' => $examiner_id)));

        $testingcomps = array();
        foreach ($examiner_testingcomps as $examiner_testingcomp) {
            $current_examiner_id = $examiner_testingcomp['ExaminerTestingcomp']['testingcomp_id'];
            $current_testingcomp = $this->Examiner->Testingcomp->find('first', array('conditions' => array('Testingcomp.id' => $current_examiner_id)));
            array_push($testingcomps, $current_testingcomp);
        }
        $testingcomps = Hash::combine($testingcomps, '{n}.Testingcomp.id', array('%s', '{n}.Testingcomp.id'));
        return $testingcomps;
    }

    //Testincomps für Examiner speichern und weggefallene entfernen
    private function _saveExaminerTestingcomps($examiner_id, $testingcomps)
    {
        $testingcomps = is_array($testingcomps) ? $testingcomps : array($testingcomps);
        foreach ($testingcomps as $testingcomp_id) {
            $checkTestingcomp = $this->Examiner->ExaminerTestingcomp->find('list', array('conditions' => array('ExaminerTestingcomp.testingcomp_id' => $testingcomp_id,
                'ExaminerTestingcomp.examiner_id' => $examiner_id)));
            if (is_countable($checkTestingcomp) && count($checkTestingcomp) < 1) {
                $data = array('id' => null, 'examiner_id' => $examiner_id, 'testingcomp_id' => $testingcomp_id);
                $saveres = $this->Examiner->ExaminerTestingcomps->save($data);
            }
        }

        //Prüfen ob Werte weggefallen sind
        $allTestingcomps = $this->Examiner->ExaminerTestingcomp->find('list', array('fields' => array('ExaminerTestingcomp.testingcomp_id'),
            'conditions' => array('ExaminerTestingcomp.examiner_id' => $examiner_id)));
        $notFound = array();

        foreach ($allTestingcomps as $x => $value) {
            $bFound = false;
            if (is_countable($testingcomps)) {
                for ($y = 0; $y < count($testingcomps); $y++) {
                    if (isset($testingcomps[$y]) && $value == $testingcomps[$y]) {
                        $bFound = true;
                    }
                }
            }
            if (!$bFound) {
                array_push($notFound, $value);
            }
        }
        if (is_countable($notFound) && count($notFound) > 0) {
            foreach ($notFound as $deleteable_id => $value) {
                $this->Examiner->ExaminerTestingcomp->deleteAll([
                    'ExaminerTestingcomp.examiner_id' => $examiner_id,
                    'ExaminerTestingcomp.testingcomp_id' => $value,
                ], false);
            }
        }
    }

    /**
     * add method
     *
     * @return void
     */
    public function add()
    {
        $this->layout = 'modal';

        $afterEdit = null;
        $arrayData['settings'] = $this->Xml->CollectXMLFromFile('Examiner');

        if (isset($this->request->data['Examiner']) && ($this->request->is('post') || $this->request->is('put'))) {
            $this->request->data = $this->Data->ChangeDropdownData($this->request->data, $arrayData, array('Examiner'));

            $this->Examiner->create();

            // muss noch in der xml angepasst werden

            // testingcomp_id durch mehrfachauswahl in examiners_testingcomps eintragen
            $this->request->data['Examiner']['testingcomp_id'] = $this->Auth->user('testingcomp_id');

            if ($this->Examiner->save($this->request->data)) {
                $new_examiner_id = $this->Examiner->getLastInsertId();

                if (isset($this->request->data['ExaminerTestingcomps']['widget'])) {
                    $this->_saveExaminerTestingcomps($this->Examiner->getLastInsertID(), $this->request->data['ExaminerTestingcomps']['widget']);
                }

                $this->Flash->success(__('The examiner has been saved'), array('key' => 'success'));
                $this->Session->delete('Examiner.form');

                $this->Autorisierung->Logger($this->Examiner->getLastInsertID(), $this->request->data['Examiner']);

                $this->request->projectvars['VarsArray'][15] = $this->Examiner->getLastInsertID();

                $FormName['controller'] = 'examiners';
                $FormName['action'] = 'overview';
                $FormName['terms'] = implode('/', $this->request->projectvars['VarsArray']);
                $this->set('examiner_id', $new_examiner_id);
                $this->set('FormName', $FormName);
            } else {
                $this->Flash->error(__('The examiner could not be saved. Please, try again.'), array('key' => 'error'));
            }
        }

        $this->set(compact('testingcomps'));

        //        $this->request->data = array();
        $this->request->data = $this->Data->GeneralDropdownData(array('Examiner'), $arrayData, $this->request->data);
        $testingcomps = $this->Examiner->Testingcomp->find('list', array('conditions' => array('Testingcomp.id' => $this->Autorisierung->ConditionsTestinccomps())));

        $this->request->data = $this->_addUserInDropdownData($this->request->data);

        $testingcompSelection = $this->_addTestingcompsInSelection();

        if (!empty($id)) {
            $testingcompAssigned = $this->_getAssignedTestingcomps($id);
        } else {
            if (!isset($testingcompAssigned)) {
                $testingcompAssigned = array();
                $testingcompAssigned[$this->Auth->user('testingcomp_id')] = $this->Auth->user('testingcomp_id');
            }
        }

        $this->set('testingcompSelection', $testingcompSelection);
        $this->set('testingcompAssigned', $testingcompAssigned);

        $this->request->data['Examiner']['active'] = 1;
        $this->request->data['Examiner']['send_infos'] = 0;
        $this->Data->SessionFormload(); //wenn Sessiondatenvorhanden diese setzen P.Schmidt 4.9.2017
        $SettingsArray = array();

        $this->set('SettingsArray', $SettingsArray);
        $this->set('arrayData', $arrayData);
        $this->set('settings', $arrayData['settings']);
    }

    public function uploadstamp()
    {
        $this->loadModel('ExaminersStamp');
        $this->Sicherheit->ClamavScan();
        $ApplicationType = array(
            'image/jpeg',
            'image/png',
            'image/svg+xml',
        );

        if (isset($_FILES['file']) && count($_FILES['file']) > 0) {
            if (isset($_FILES['file']['filename'])) {
                $filename = $_FILES['file']['filename'];
            }

            if (isset($_FILES['file']['tmp_name'])) {
                $filedata = $_FILES['file']['tmp_name'];
            }

            if (isset($_FILES['file']['size'])) {
                $filesize = $_FILES['file']['size'];
            }

            if (isset($_FILES['file']['type'])) {
                $fileTypeArray = explode('/', $_FILES['file']['type']);
            }
            $fileType = $fileTypeArray[1];

            $ExtensionGrant = false;
            foreach ($ApplicationType as $key => $value) {
                if ($value == $_FILES['file']['type']) {
                    $ExtensionGrant = true;
                    break;
                }
            }

            if ($ExtensionGrant === false) {
                die();
            }

            if (isset($this->request->data['examiner_id'])) {
                $fileType = str_replace("svg+xml", "svg", $fileType);
                $fileNameNew = '';
                $fileNameNew .= uniqid(rand(), true) . '.' . $fileType;

                $filesave = array(
                    'examiner_id' => intval($this->request->data['examiner_id']),
                    'file_name' => $fileNameNew,
                );

                $stamp = $this->ExaminersStamp->find('first', array('conditions' => array('examiner_id' => $this->request->data['examiner_id'])));

                if (is_countable($stamp) && count($stamp) > 0) {
                    $this->ExaminersStamp->deleteAll(array('examiner_id' => $this->request->data['examiner_id']));
                }

                if ($this->ExaminersStamp->save($filesave)) {
                    $savePath = Configure::read('examiner_folder') . 'stamps' . DS . $this->request->data['examiner_id'] . DS;
                    if (!file_exists($savePath)) {
                        $folderpath = new Folder($savePath, true, 0755);
                    }
                    $info = getimagesize($filedata);

                    $absolute_path = $savePath . DS . $fileNameNew;

                    if (!file_exists($absolute_path)) {
                        foreach (new DirectoryIterator($savePath) as $fileInfo) {
                            if (!$fileInfo->isDot()) {
                                unlink($fileInfo->getPathname());
                            }
                        }
                        move_uploaded_file($filedata, $absolute_path);
                    }
                };

            }

        }

        /*  if(isset($this->request->data['save_examiner_file']) && $this->request->data['save_examiner_file'] == 1) {

    }*/
    }

    /**
     * edit method
     *
     * @throws NotFoundException
     * @param string $id
     * @return void
     */

    public function delete_stamp()
    {
        $this->loadModel('ExaminersStamp');

        if (isset($this->request->data['delete_stamp'])) {
            $this->ExaminersStamp->deleteAll(array('examiner_id' => $this->request->data['examiner_id']));
            $savePath = Configure::read('examiner_folder') . 'stamps' . DS . $this->request->data['examiner_id'] . DS;
            foreach (new DirectoryIterator($savePath) as $fileInfo) {
                if (!$fileInfo->isDot()) {
                    unlink($fileInfo->getPathname());
                }
            }
        }
    }

    public function edit()
    {
        $this->layout = 'modal';
        $id = $this->request->projectvars['VarsArray'][15];
        $afterEdit = null;

        if (!$this->Examiner->exists($id)) {
            throw new NotFoundException(__('Invalid examiner'));
        }

        $this->Autorisierung->CheckExaminerLink($id);

//        $arrayData = $this->Xml->DatafromXml('Examiner', 'file', null);
        $arrayData['settings'] = $this->Xml->CollectXMLFromFile('Examiner');

        $this->request->data = $this->Data->ChangeDropdownData($this->request->data, $arrayData, array('Examiner'));

        if (isset($this->request->data['Examiner']) && ($this->request->is('post') || $this->request->is('put'))) {

            $this->request->data['id'] = $id;
            if ($this->Examiner->save($this->request->data)) {

                if (isset($this->request->data['ExaminerTestingcomps']['widget'])) {
                    $this->_saveExaminerTestingcomps($id, $this->request->data['ExaminerTestingcomps']['widget']);
                }

                $this->Flash->success(__('The examiner has been saved'), array('key' => 'success'));

                $this->Autorisierung->Logger($id, $this->request->data['Examiner']);
                $this->Session->delete('Examiner.form');
                $FormName['controller'] = 'examiners';
                $FormName['action'] = 'overview';
                $FormName['terms'] = implode('/', $this->request->projectvars['VarsArray']);

                $this->set('FormName', $FormName);
                $this->set('saveOK', 1);
            } else {
                $this->Flash->error(__('The examiner could not be saved. Please, try again.'), array('key' => 'error'));
            }
        } else {
            $options = array('conditions' => array('Examiner.' . $this->Examiner->primaryKey => $id));
            $this->request->data = $this->Examiner->find('first', $options);
        }

        $testingcomps = $this->Examiner->Testingcomp->find('list', array('conditions' => array('Testingcomp.id' => $this->Autorisierung->ConditionsTestinccomps())));
        $this->set(compact('testingcomps'));

        $this->request->data = $this->Data->GeneralDropdownData(array('Examiner'), $arrayData, $this->request->data);
        $this->request->data = $this->_addUserInDropdownData($this->request->data);

        $testingcompSelection = $this->_addTestingcompsInSelection();

        if (!empty($id)) {
            $testingcompAssigned = $this->_getAssignedTestingcomps($id);
        }

        $this->set('testingcompSelection', $testingcompSelection);
        $this->set('testingcompAssigned', $testingcompAssigned);

        $SettingsArray = array();

        $SettingsArray['dellink'] = array('discription' => __('Delete examiner', true), 'controller' => 'examiners', 'action' => 'delete', 'terms' => $this->request->params['pass']);

        $this->request->data['Examiner']['status'] = 0;
        $this->Data->SessionFormload(); //wenn Sessiondatenvorhanden diese setzen P.Schmidt 4.9.2017
        $this->set('SettingsArray', $SettingsArray);
        $this->set('arrayData', $arrayData);
        $this->set('settings', $arrayData['settings']);
        $this->set('examiner_id', $id);

        if ($this->Session->read('lastModalURLArray.action') == 'certificate') {
            $this->layout = 'blank';
            $this->set('certificate_id', $this->request->params['pass'][16]);
            $this->render('edit_fast');
        }
    }

    /**
     * delete method
     *
     * @throws NotFoundException
     * @param string $id
     * @return void
     */
    public function delete()
    {
        $this->layout = 'modal';

        $id = $this->request->projectvars['VarsArray'][15];

        $this->Examiner->id = $id;

        if (!$this->Examiner->exists()) {
            throw new NotFoundException(__('Invalid examiner'));
        }

        $this->Autorisierung->CheckExaminerLink($id);

        if (isset($this->request->data['Examiner']) && ($this->request->is('post') || $this->request->is('put'))) {
            $this->request->data['Examiner']['deleted'] = 1;
            $this->request->data['Examiner']['active'] = 0;
            if ($this->Examiner->save($this->request->data)) {
                $this->Flash->success('Examiner deleted', array('key' => 'success'));
                $this->Autorisierung->Logger($id, $this->request->data['Examiner']);
                //$this->Autorisierung->Logger($this->Device->getLastInsertID(), $device['Device']);

                $FormName['controller'] = 'examiners';
                $FormName['action'] = 'index';
                $FormName['terms'] = implode('/', $this->request->projectvars['VarsArray']);

                $this->set('FormName', $FormName);
                $this->set('saveOK', 1);
                //$this->index();
                //$this->render('index', 'ajax');
            } else {
                $this->Flash->error('Examiner was not deleted. Please, try again.', array('key' => 'error'));
            }
        }

        $options = array('conditions' => array('Examiner.' . $this->Examiner->primaryKey => $id));
        $this->request->data = $this->Examiner->find('first', $options);

        $SettingsArray = array();
        $SettingsArray['backlink'] = array('discription' => __('Back', true), 'controller' => 'examiners', 'action' => 'edit', 'terms' => $this->request->params['pass']);

        $this->set('SettingsArray', $SettingsArray);

        //        $this->set('afterEDIT', $afterEdit);
    }

    public function duplicate_workload()
    {
        $id = $this->request->params['pass'][15];

        if (!$this->ExaminerTime->exists($id)) {
            throw new NotFoundException(__('Invalid workload id'));
        }

        $this->loadModel('Reportnumber');
        if (!$this->Reportnumber->exists($this->request->projectvars['reportnumberID'])) {
            throw new NotFoundException(__('Invalid reportnumber'));
        }

        $this->Autorisierung->IsThisMyReport($this->request->projectvars['reportnumberID']);

        $this->request->onlyAllow('post', 'duplicate_workload');

        $this->ExaminerTime->recursive = -1;
        $workload = $this->ExaminerTime->find('first', array('conditions' => array('ExaminerTime.' . $this->ExaminerTime->primaryKey => $id)));
        unset($workload['ExaminerTime'][$this->ExaminerTime->primaryKey]);
        $workload['ExaminerTime']['force_save'] = 1;

        $this->ExaminerTime->create();
        if ($this->ExaminerTime->save($workload)) {
            $this->Flash->success('Examiner workload duplicated', array('key' => 'success'));
            $this->ExaminerTime->recursive = -1;
            $this->request->data = $this->ExaminerTime->find('first', array('conditions' => array('ExaminerTime.' . $this->ExaminerTime->primaryKey => $this->ExaminerTime->getLastInsertID())));

            if (isset($this->request->data['ExaminerTime']['collision'])) {
                $this->request->data['ExaminerTime']['collision'] = str_replace('"id":null', '"id":' . $this->request->data['ExaminerTime']['id'], $this->request->data['ExaminerTime']['collision']);
                $this->request->data['ExaminerTime']['force_save'] = 1;
                $this->ExaminerTime->save($this->request->data);
            }
            $afterEdit = $this->Navigation->afterEditModal(Router::url(array_merge(array('action' => 'edit_workload'), $this->request->projectvars['VarsArray'], array($this->ExaminerTime->getLastInsertID()))), 0);
            //            $this->Autorisierung->Logger($this->ExaminerTime->getLastInsertID(), $this->request->data['ExaminerTime']);

            $logdata = $this->request->data['ExaminerTime'];
            $exam = $this->Examiner->find('first', array('conditions' => array('Examiner.id' => $this->request->data['ExaminerTime']['examiner_id'])));
            $logdata['examiner'] = $exam['Examiner']['display'];
            $this->Autorisierung->Logger($this->ExaminerTime->getLastInsertID(), $logdata);
        } else {
            $this->Flash->error('Examiner workload was not duplicated', array('key' => 'error'));
            $afterEdit = $this->Navigation->afterEditModal(Router::url(array_merge(array('action' => 'list_workload'), $this->request->projectvars['VarsArray'])), 0);
        }

        $SettingsArray = array();
        $SettingsArray['backlink'] = array('discription' => __('Back', true), 'controller' => 'examiners', 'action' => 'list_workload', 'terms' => $this->request->projectvars['VarsArray']);

        $this->set('SettingsArray', $SettingsArray);
        $this->set('afterEDIT', $afterEdit);
    }

    public function list_workload()
    {
        if (Configure::read('WorkloadManager') == false) {
            return;
        }

        $this->loadModel('Reportnumber');

        if ($this->request->projectvars['VarsArray'][5] == 0) {
            $optionArray = array();

            $optionArray['Reportnumber.testingcomp_id'] = $this->Autorisierung->ConditionsTestinccomps();
            $optionArray['Reportnumber.delete'] = 0;
            //            $optionArray['Reportnumber.status >'] = 0;

            if ($this->request->projectvars['VarsArray'][0] > 0) {
                $optionArray['Reportnumber.topproject_id'] = $this->request->projectvars['VarsArray'][0];
            }
            if ($this->request->projectvars['VarsArray'][1] > 0) {
                $optionArray['Reportnumber.equipment_type_id'] = $this->request->projectvars['VarsArray'][1];
            }
            if ($this->request->projectvars['VarsArray'][2] > 0) {
                $optionArray['Reportnumber.equipment_id'] = $this->request->projectvars['VarsArray'][2];
            }
            if ($this->request->projectvars['VarsArray'][3] > 0) {
                $optionArray['Reportnumber.order_id'] = $this->request->projectvars['VarsArray'][3];
            }
            if ($this->request->projectvars['VarsArray'][4] > 0) {
                $optionArray['Reportnumber.report_id'] = $this->request->projectvars['VarsArray'][4];
            }

            $options = array(
                'fields' => array('id'),
                'conditions' => $optionArray,
            );

            //            $options['conditions'] = array_filter($options['conditions']);

            $reportnumbers = $this->Reportnumber->find('list', $options);
        } else {
            if (!$this->Reportnumber->exists($this->request->projectvars['reportnumberID'])) {
                throw new NotFoundException(__('Invalid reportnumber'));
            }
            $this->Autorisierung->IsThisMyReport($this->request->projectvars['reportnumberID']);

            $reportnumbers = array($this->request->projectvars['reportnumberID']);
        }

        $options = array('fields' => array('Testingcomp_id'), 'conditions' => array('Reportnumber.' . $this->Reportnumber->primaryKey => $reportnumbers));
        $this->Reportnumber->recursive = 0;
        $testingcomps = array_unique($this->Reportnumber->find('list', $options));

        $SettingsArray = array();

        if (!$this->writeprotection && $this->request->projectvars['VarsArray'][5] != 0) {
            $SettingsArray['addlink'] = array('discription' => __('Add', true), 'controller' => 'examiners', 'action' => 'add_workload', 'terms' => $this->request->projectvars['VarsArray']);
        }

        $SettingsArray['backlink'] = array('discription' => __('Back', true), 'controller' => 'examiners', 'action' => 'overview', 'terms' => $this->request->projectvars['VarsArray']);

        $this->set('SettingsArray', $SettingsArray);

        if (count($this->ExaminerTime->find('first', array('conditions' => $options['conditions']))) == 0) {
            $this->render('no_data', 'modal');
            return;
        }

        $this->paginate = array_merge($this->paginate, array(
            'order' => array(
                'ExaminerTime.testing_time_start' => 'asc',
                'ExaminerTime.waiting_time_start' => 'asc',
            ),
        ));

        $this->ExaminerTime->recursive = 2;
        $options = array('Examiner.testingcomp_id' => $testingcomps, 'ExaminerTime.reportnumber_id' => $reportnumbers);
        $ExaminerTimes = $this->paginate('ExaminerTime', $options);

        $this->set('examiners', $ExaminerTimes);
    }

    public function list_examiner_workload()
    {
        if (isset($this->request->data['year']) && isset($this->request->data['month'])) {
            if ($this->request->data['month'] > 0) {
                $this->_list_examiner_workload_complett_month();
            }
            if ($this->request->data['month'] == 0) {
                $this->_list_examiner_workload_complett_year();
            }
            return;
        }

        if (!is_array($this->request->data['day'])) {
            $this->request->data['day'] = array($this->request->data['day']);
        }
        sort($this->request->data['day']);
        $that = $this;
        if (!array_reduce($this->request->data['day'], function ($prev, $curr) use ($that) {
            return $prev && $that->_validateDate($curr);
        }, true)) {
            throw new BadRequestException(__('Invalid Date'));
        }

        if (count($this->request->data['day']) == 1) {
            $this->_list_examiner_workload_by_day();
        } else {
            $this->_list_examiner_workload_by_month();
        }
    }

    protected function _list_examiner_workload_complett_year()
    {
        $this->loadModel('Reportnumber');

        $examinerID_condition = array();

        if ($this->request->projectvars['reportnumberID'] == 0) {
            $options = array('fields' => array('id'), 'conditions' => array(
                'Reportnumber.topproject_id' => $this->request->projectvars['projectID'],
                'Reportnumber.order_id' => $this->request->projectvars['orderID'],
                'Reportnumber.report_id' => $this->request->projectvars['reportID'],
                'Reportnumber.testingcomp_id' => $this->Autorisierung->ConditionsTestinccomps(),
                'Reportnumber.delete' => 0,
            ));
            $options['conditions'] = array_filter($options['conditions']);
            $reportnumbers = $this->Reportnumber->find('list', $options);
        } else {
            if (!$this->Reportnumber->exists($this->request->projectvars['reportnumberID'])) {
                throw new NotFoundException(__('Invalid reportnumber'));
            }
            $this->Autorisierung->IsThisMyReport($this->request->projectvars['reportnumberID']);

            $reportnumbers = array($this->request->projectvars['reportnumberID']);
        }

        $options = array('fields' => array('Testingcomp_id'), 'conditions' => array('Reportnumber.' . $this->Reportnumber->primaryKey => $reportnumbers));
        $this->Reportnumber->recursive = 0;
        $testingcomps = array_unique($this->Reportnumber->find('list', $options));

        if (isset($this->request->projectvars['examiniererID']) && $this->request->projectvars['examiniererID'] > 0) {
            $examinerid = $this->Examiner->find('first', array('fields' => array('name'),
                'conditions' => array(
                    'Examiner.testingcomp_id' => $testingcomps,
                    'Examiner.id' => $this->request->projectvars['examiniererID'],
                )));

            $examinerID_condition = array('ExaminerTime.examiner_id' => $this->request->projectvars['examiniererID']);

            $this->set('examiner', $examinerid);
        }

        $this->ExaminerTime->recursive = -1;

        $order = array('order' => array(
            'YEAR(ExaminerTime.testing_time_start)',
        ),
        );

        $reportnumberID_condition = array();

        if ($this->request->projectvars['reportID'] > 0) {
            $reportnumberID_condition = array('ExaminerTime.reportnumber_id' => $reportnumbers);
        }

        $options = array(
            'conditions' => array(
                $examinerID_condition,
                $reportnumberID_condition,
                'YEAR(ExaminerTime.testing_time_start)' => $this->request->data['year'],
                'ExaminerTime.waiting_time_start' => null,
            ),
            $order,
        );

        $examinertime1 = $this->ExaminerTime->find('all', $options);

        $options = array('conditions' => array(
            $examinerID_condition,
            $reportnumberID_condition,
            'YEAR(ExaminerTime.waiting_time_start)' => $this->request->data['year'],
            'ExaminerTime.testing_time_start' => null,
        ),
            $order,
        );
        $examinertime2 = $this->ExaminerTime->find('all', $options);

        $options = array('conditions' => array(
            $examinerID_condition,
            $reportnumberID_condition,
            'YEAR(ExaminerTime.waiting_time_start)' => $this->request->data['year'],
            'YEAR(ExaminerTime.waiting_time_start)' => $this->request->data['year'],
        ),
            $order,
        );
        $examinertime3 = $this->ExaminerTime->find('all', $options);

        $examinertime = array_merge($examinertime1, $examinertime2, $examinertime3);

        $SettingsArray = array();
        $this->request->projectvars['VarsArray']['15'] = $this->request->projectvars['examiniererID'];
        $SettingsArray['backlink'] = array('discription' => __('Back', true), 'controller' => 'examiners', 'action' => 'view', 'terms' => $this->request->projectvars['VarsArray']);
        if ($this->request->projectvars['reportnumberID'] != 0) {
            $SettingsArray['addlink'] = array('discription' => __('Add', true), 'controller' => 'examiners', 'action' => 'add_workload', 'terms' => $this->request->projectvars['VarsArray']);
        }

        $this->set('SettingsArray', $SettingsArray);

        if (count($examinertime) == 0) {
            $this->render('no_data', 'modal');
            return;
        }

        $order = ($this->ExaminerTime->Order->find('first', array('conditions' => array('Order.id' => $examinertime[0]['ExaminerTime']['order_id']))));

        $this->set('examinerTimes', $examinertime);
        $this->set('order', $order);
        $this->set('width', $this->request->data['width']);
        $this->set('limits', count($examinertime));

        $this->render('list_examiner_workload_complett_year_gantt', 'modal');
    }

    protected function _list_examiner_workload_complett_month()
    {
        $this->loadModel('Reportnumber');

        $examinerID_condition = array();

        if ($this->request->projectvars['reportnumberID'] == 0) {
            $options = array('fields' => array('id'), 'conditions' => array(
                'Reportnumber.topproject_id' => $this->request->projectvars['projectID'],
                'Reportnumber.order_id' => $this->request->projectvars['orderID'],
                'Reportnumber.report_id' => $this->request->projectvars['reportID'],
                'Reportnumber.testingcomp_id' => $this->Autorisierung->ConditionsTestinccomps(),
                'Reportnumber.delete' => 0,
            ));
            $options['conditions'] = array_filter($options['conditions']);
            $reportnumbers = $this->Reportnumber->find('list', $options);
        } else {
            if (!$this->Reportnumber->exists($this->request->projectvars['reportnumberID'])) {
                throw new NotFoundException(__('Invalid reportnumber'));
            }
            $this->Autorisierung->IsThisMyReport($this->request->projectvars['reportnumberID']);

            $reportnumbers = array($this->request->projectvars['reportnumberID']);
        }

        $options = array('fields' => array('Testingcomp_id'), 'conditions' => array('Reportnumber.' . $this->Reportnumber->primaryKey => $reportnumbers));
        $this->Reportnumber->recursive = 0;
        $testingcomps = array_unique($this->Reportnumber->find('list', $options));

        if (isset($this->request->projectvars['examiniererID']) && $this->request->projectvars['examiniererID'] > 0) {
            $examinerid = $this->Examiner->find('first', array('fields' => array('name'),
                'conditions' => array(
                    'Examiner.testingcomp_id' => $testingcomps,
                    'Examiner.id' => $this->request->projectvars['examiniererID'],
                )));

            $examinerID_condition = array('ExaminerTime.examiner_id' => $this->request->projectvars['examiniererID']);

            $this->set('examiner', $examinerid);
        }

        $this->ExaminerTime->recursive = -1;

        $order = array('order' => array(
            'YEAR(ExaminerTime.testing_time_start)',
            'MONTH(ExaminerTime.testing_time_start)',
        ),
        );

        $reportnumberID_condition = array();

        if ($this->request->projectvars['reportID'] > 0) {
            $reportnumberID_condition = array('ExaminerTime.reportnumber_id' => $reportnumbers);
        }

        $options = array(
            'conditions' => array(
                $examinerID_condition,
                $reportnumberID_condition,
                'YEAR(ExaminerTime.testing_time_start)' => $this->request->data['year'],
                'MONTH(ExaminerTime.testing_time_start)' => $this->request->data['month'],
                'ExaminerTime.waiting_time_start' => null,
            ),
            $order,
        );

        $examinertime1 = $this->ExaminerTime->find('all', $options);

        $options = array('conditions' => array(
            $examinerID_condition,
            $reportnumberID_condition,
            'YEAR(ExaminerTime.waiting_time_start)' => $this->request->data['year'],
            'MONTH(ExaminerTime.waiting_time_start)' => $this->request->data['month'],
            'ExaminerTime.testing_time_start' => null,
        ),
            $order,
        );
        $examinertime2 = $this->ExaminerTime->find('all', $options);

        $options = array('conditions' => array(
            $examinerID_condition,
            $reportnumberID_condition,
            'YEAR(ExaminerTime.waiting_time_start)' => $this->request->data['year'],
            'MONTH(ExaminerTime.waiting_time_start)' => $this->request->data['month'],
            'YEAR(ExaminerTime.waiting_time_start)' => $this->request->data['year'],
            'MONTH(ExaminerTime.waiting_time_start)' => $this->request->data['month'],
        ),
            $order,
        );
        $examinertime3 = $this->ExaminerTime->find('all', $options);

        $examinertime = array_merge($examinertime1, $examinertime2, $examinertime3);

        $SettingsArray = array();
        $this->request->projectvars['VarsArray']['15'] = $this->request->projectvars['examiniererID'];
        $SettingsArray['backlink'] = array('discription' => __('Back', true), 'controller' => 'examiners', 'action' => 'view', 'terms' => $this->request->projectvars['VarsArray']);
        if ($this->request->projectvars['reportnumberID'] != 0) {
            $SettingsArray['addlink'] = array('discription' => __('Add', true), 'controller' => 'examiners', 'action' => 'add_workload', 'terms' => $this->request->projectvars['VarsArray']);
        }

        $this->set('SettingsArray', $SettingsArray);

        if (count($examinertime) == 0) {
            $this->render('no_data', 'modal');
            return;
        }

        $order = ($this->ExaminerTime->Order->find('first', array('conditions' => array('Order.id' => $examinertime[0]['ExaminerTime']['order_id']))));

        $this->set('examinerTimes', $examinertime);
        $this->set('order', $order);
        $this->set('width', $this->request->data['width']);
        $this->set('limits', count($examinertime));

        $this->render('list_examiner_workload_complett_month_gantt', 'modal');
    }

    protected function _list_examiner_workload_by_month()
    {
        $this->loadModel('Reportnumber');
        if ($this->request->projectvars['reportnumberID'] == 0) {
            $options = array('fields' => array('id'), 'conditions' => array(
                'Reportnumber.topproject_id' => $this->request->projectvars['projectID'],
                'Reportnumber.order_id' => $this->request->projectvars['orderID'],
                'Reportnumber.report_id' => $this->request->projectvars['reportID'],
                'Reportnumber.testingcomp_id' => $this->Autorisierung->ConditionsTestinccomps(),
                'Reportnumber.delete' => 0,
            ));
            $options['conditions'] = array_filter($options['conditions']);
            $reportnumbers = $this->Reportnumber->find('list', $options);
        } else {
            if (!$this->Reportnumber->exists($this->request->projectvars['reportnumberID'])) {
                throw new NotFoundException(__('Invalid reportnumber'));
            }
            $this->Autorisierung->IsThisMyReport($this->request->projectvars['reportnumberID']);

            $reportnumbers = array($this->request->projectvars['reportnumberID']);
        }

        $options = array('fields' => array('Testingcomp_id'), 'conditions' => array('Reportnumber.' . $this->Reportnumber->primaryKey => $reportnumbers));
        $this->Reportnumber->recursive = 0;
        $testingcomps = array_unique($this->Reportnumber->find('list', $options));

        $examinerid = $this->Examiner->find('list', array('fields' => array('id'), 'conditions' => array(
            'Examiner.id' => $this->request->data['examiner'],
            'Examiner.testingcomp_id' => $testingcomps,
        )));

        $options = array('conditions' => array(
            'ExaminerTime.examiner_id' => $examinerid,
            'ExaminerTime.reportnumber_id' => $reportnumbers,
            'date(ExaminerTime.testing_time_start) >=' => reset($this->request->data['day']),
            'date(ExaminerTime.testing_time_end) <=' => end($this->request->data['day']),
        ));

        $this->ExaminerTime->recursive = -1;
        $this->set('limits', $this->request->data['day']);
        //$this->set('examinerTimes', $this->ExaminerTime->find('all', $options));
        $this->paginate = array(
            'limit' => 25,
        );

        $this->set('examinerTimes', $this->paginate('ExaminerTime', $options['conditions']));
        $this->set('width', $this->request->data['width']);

        $SettingsArray = array();

        if ($this->request->projectvars['reportnumberID'] != 0) {
            $SettingsArray['addlink'] = array('discription' => __('Add', true), 'controller' => 'examiners', 'action' => 'add_workload', 'terms' => $this->request->projectvars['VarsArray']);
        }

        $this->set('SettingsArray', $SettingsArray);

        $diff = array_diff(explode('-', $this->request->data['day'][0]), explode('-', $this->request->data['day'][1]));
        if ($this->ExaminerTime->find('count', $options) == 0) {
            echo '<p>' . __('No data to display') . '</p>';
            $this->render(null, 'blank');
        } elseif (isset($diff[1])) { // Monat unterschiedlich - Jahresübersicht
            $this->render('list_examiner_workload_year_gantt', 'csv');
        } else {
            $this->render('list_examiner_workload_month_gantt', 'csv');
        }
    }

    protected function _list_examiner_workload_by_day()
    {
        $this->loadModel('Reportnumber');
        if ($this->request->projectvars['reportnumberID'] == 0) {
            $options = array(
                'fields' => array('id'),
                'conditions' => array(
                    'Reportnumber.topproject_id' => $this->request->projectvars['projectID'],
                    'Reportnumber.order_id' => $this->request->projectvars['orderID'],
                    'Reportnumber.report_id' => $this->request->projectvars['reportID'],
                    'Reportnumber.testingcomp_id' => $this->Autorisierung->ConditionsTestinccomps(),
                    'Reportnumber.delete' => 0,
                ),
            );
            $options['conditions'] = array_filter($options['conditions']);
            $reportnumbers = $this->Reportnumber->find('list', $options);
        } else {
            if (!$this->Reportnumber->exists($this->request->projectvars['reportnumberID'])) {
                throw new NotFoundException(__('Invalid reportnumber'));
            }
            $this->Autorisierung->IsThisMyReport($this->request->projectvars['reportnumberID']);

            $reportnumbers = array($this->request->projectvars['reportnumberID']);
        }

        $options = array('fields' => array('Testingcomp_id'), 'conditions' => array('Reportnumber.' . $this->Reportnumber->primaryKey => $reportnumbers));
        $this->Reportnumber->recursive = 0;
        $testingcomps = array_unique($this->Reportnumber->find('list', $options));

        $this->ExaminerTime->recursive = 2;
        $options = array(
            'Examiner.id' => $this->request->data['examiner'],
            'Examiner.testingcomp_id' => $testingcomps,
            'ExaminerTime.reportnumber_id' => $reportnumbers,
            'date(ExaminerTime.testing_time_start) >=' => reset($this->request->data['day']),
            'date(ExaminerTime.testing_time_end) <=' => end($this->request->data['day']),
        );

        $this->set('examiners', $this->paginate('ExaminerTime', $options));

        $SettingsArray = array();
        if ($this->request->projectvars['reportnumberID'] != 0) {
            $SettingsArray['addlink'] = array('discription' => __('Add', true), 'controller' => 'examiners', 'action' => 'add_workload', 'terms' => $this->request->projectvars['VarsArray']);
        }

        $this->set('SettingsArray', $SettingsArray);

        if ($this->ExaminerTime->find('count', array('conditions' => $options)) == 0) {
            $this->render(null, 'blank');
        }

        $this->render('list_examiner_workload', 'csv');
    }

    public function add_workload()
    {
        $afterEdit = null;
        $this->loadModel('Reportnumber');
        if (!$this->Reportnumber->exists($this->request->projectvars['reportnumberID'])) {
            throw new NotFoundException(__('Invalid reportnumber'));
        }

        $this->Autorisierung->IsThisMyReport($this->request->projectvars['reportnumberID']);

        $options = array('conditions' => array('Reportnumber.' . $this->Reportnumber->primaryKey => $this->request->projectvars['reportnumberID']));
        $this->Reportnumber->recursive = 0;
        $reportnumbers = $this->Reportnumber->find('first', $options);

        $this->set('collision', array());

        $examiners = $this->Examiner->find('all', array('order' => array('Examiner.name ASC'), 'conditions' => array('Examiner.testingcomp_id' => $reportnumbers['Testingcomp']['id'])));
        $exam = array();

        foreach ($examiners as $examiner) {
            $exam[$examiner['Examiner']['id']] = $examiner['Examiner']['name'];
        }

        if (isset($this->request->data['ExaminerTime']) && ($this->request->is('post') || $this->request->is('put'))) {
            if (isset($this->request->data['caption'])) {
                unset($this->request->data['caption']);
            }
            $this->request->data['ExaminerTime']['reportnumber_id'] = $reportnumbers['Reportnumber']['id'];
            $this->request->data['ExaminerTime']['order_id'] = $reportnumbers['Reportnumber']['order_id'];
            if (isset($this->request->data['force_save'])) {
                $this->request->data['ExaminerTime']['force_save'] = $this->request->data['force_save'];
            }
            $this->ExaminerTime->create();
            if ($this->ExaminerTime->save($this->request->data)) {
                // Erneut abspeichern, um die Fehler-IDs richtig einzufügen
                $this->ExaminerTime->recursive = -1;
                $this->request->data = $this->ExaminerTime->find('first', array('conditions' => array('ExaminerTime.' . $this->ExaminerTime->primaryKey => $this->ExaminerTime->getLastInsertID())));

                if (isset($this->request->data['ExaminerTime']['collision'])) {
                    $this->request->data['ExaminerTime']['collision'] = str_replace('"id":null', '"id":' . $this->request->data['ExaminerTime']['id'], $this->request->data['ExaminerTime']['collision']);
                    $this->request->data['ExaminerTime']['force_save'] = 1;
                    $this->ExaminerTime->save($this->request->data);
                }

                $this->Flash->success('Examiner workload saved', array('key' => 'success'));
                $afterEdit = $this->Navigation->afterEditModal(Router::url(array_merge(array('action' => 'list_workload'), $this->request->projectvars['VarsArray'])), 0);

                $logdata = $this->request->data['ExaminerTime'];
                $logdata['examiner'] = $exam[$logdata['examiner_id']];
                $this->Autorisierung->Logger($this->ExaminerTime->getLastInsertID(), $logdata);
            } else {
                $this->set('collision', $this->ExaminerTime->data['ExaminerTime']['collision']);
                if (isset($this->ExaminerTime->data['ExaminerTime']['collision']['waiting_time'])) {
                    $this->ExaminerTime->data['ExaminerTime']['collision']['waiting_time'] = array_map(function ($elem) {
                        return $elem['Reportnumber']['number'] . '-' . $elem['ExaminerTime']['id'];
                    }, $this->ExaminerTime->data['ExaminerTime']['collision']['waiting_time']);
                }
                if (isset($this->ExaminerTime->data['ExaminerTime']['collision']['testing_time'])) {
                    $this->ExaminerTime->data['ExaminerTime']['collision']['testing_time'] = array_map(function ($elem) {
                        return $elem['Reportnumber']['number'] . '-' . $elem['ExaminerTime']['id'];
                    }, $this->ExaminerTime->data['ExaminerTime']['collision']['testing_time']);
                }
                $this->request->data = $this->ExaminerTime->data;

                $this->Flash->error('Examiner workload was not saved', array('key' => 'error'));
                $this->set('ask_force', 1);
            }
        }

        $this->set('examiners', $exam);
        $this->set('reportnumber', $reportnumbers);
        $this->set('afterEDIT', $afterEdit);

        $SettingsArray = array();
        $SettingsArray['backlink'] = array('discription' => __('Add', true), 'controller' => 'examiners', 'action' => 'list_workload', 'terms' => $this->request->projectvars['VarsArray']);

        $this->set('SettingsArray', $SettingsArray);
    }

    public function edit_workload()
    {
        $afterEdit = null;
        $id = $this->request->params['pass'][15];

        if (!$this->ExaminerTime->exists($id)) {
            throw new NotFoundException(__('Invalid workload id'));
        }

        $this->loadModel('Reportnumber');
        if (!$this->Reportnumber->exists($this->request->projectvars['reportnumberID'])) {
            //            throw new NotFoundException(__('Invalid reportnumber'));
        }

        $this->Autorisierung->IsThisMyReport($this->request->projectvars['reportnumberID']);

        $options = array('conditions' => array('Reportnumber.' . $this->Reportnumber->primaryKey => $this->request->projectvars['reportnumberID']));
        $this->Reportnumber->recursive = 0;
        $reportnumbers = $this->Reportnumber->find('first', $options);

        $this->set('collision', array());

        if (isset($this->request->data['ExaminerTime']) && ($this->request->is('post') || $this->request->is('put'))) {
            if (isset($this->request->data['caption'])) {
                unset($this->request->data['caption']);
            }
            $this->request->data['ExaminerTime'][$this->ExaminerTime->primaryKey] = $id;
            $this->request->data['ExaminerTime']['reportnumber_id'] = $reportnumbers['Reportnumber']['id'];
            $this->request->data['ExaminerTime']['order_id'] = $reportnumbers['Reportnumber']['order_id'];
            if (isset($this->request->data['force_save'])) {
                $this->request->data['ExaminerTime']['force_save'] = $this->request->data['force_save'];
            }
            if ($this->ExaminerTime->save($this->request->data)) {

                $this->Flash->success('Examiner workload saved', array('key' => 'success'));
                $afterEdit = $this->Navigation->afterEditModal(Router::url(array_merge(array('action' => 'list_workload'), $this->request->projectvars['VarsArray'])), 0);

                // neue Werte aus der Datenbank holen, mum automatisch ergänzte Felder zu bekommen
                $logData = $this->request->data['ExaminerTime'];
                if (isset($logData['collision'])) {
                    unset($logData['collision']);
                }

                $examiner = $this->ExaminerTime->Examiner->find('first', array('conditions' => array('Examiner.' . $this->ExaminerTime->Examiner->primaryKey => $logData['examiner_id'])));
                $logData['examiner'] = $examiner['Examiner']['display'];
                $this->Autorisierung->Logger($id, $logData);
            } else {
                $this->set('collision', $this->ExaminerTime->data['ExaminerTime']['collision']);
                if (isset($this->ExaminerTime->data['ExaminerTime']['collision']['waiting_time'])) {
                    $this->ExaminerTime->data['ExaminerTime']['collision']['waiting_time'] = array_map(function ($elem) {
                        return $elem['Reportnumber']['number'] . '-' . $elem['ExaminerTime']['id'];
                    }, $this->ExaminerTime->data['ExaminerTime']['collision']['waiting_time']);
                }
                if (isset($this->ExaminerTime->data['ExaminerTime']['collision']['testing_time'])) {
                    $this->ExaminerTime->data['ExaminerTime']['collision']['testing_time'] = array_map(function ($elem) {
                        return $elem['Reportnumber']['number'] . '-' . $elem['ExaminerTime']['id'];
                    }, $this->ExaminerTime->data['ExaminerTime']['collision']['testing_time']);
                }
                $this->request->data = $this->ExaminerTime->data;

                $this->Flash->error('Examiner workload was not saved', array('key' => 'error'));
                $this->set('ask_force', 1);
            }
        } else {
            $this->request->data = $this->ExaminerTime->find('first', array('conditions' => array('ExaminerTime.' . $this->ExaminerTime->primaryKey => $id)));
        }

        $examiners = $this->Examiner->find('all', array('order' => array('Examiner.name ASC'), 'conditions' => array('Examiner.testingcomp_id' => $reportnumbers['Testingcomp']['id'])));
        $exam = array();
        foreach ($examiners as $examiner) {
            $exam[$examiner['Examiner']['id']] = $examiner['Examiner']['name'];
        }
        $this->set('examiners', $exam);
        $this->Reportnumber->recursive = 0;
        $this->set('reportnumber', $this->Reportnumber->find('first', array('conditions' => array('Reportnumber.' . $this->Reportnumber->primaryKey => $reportnumbers['Reportnumber']['id']))));
        $this->set('afterEDIT', $afterEdit);

        $SettingsArray = array();
        $SettingsArray['backlink'] = array('discription' => __('Add', true), 'controller' => 'examiners', 'action' => 'list_workload', 'terms' => $this->request->projectvars['VarsArray']);

        $this->set('SettingsArray', $SettingsArray);
    }

    public function delete_workload()
    {
        $id = $this->request->params['pass'][15];

        if (!$this->ExaminerTime->exists($id)) {
            throw new NotFoundException(__('Invalid workload id'));
        }

        $this->loadModel('Reportnumber');
        if (!$this->Reportnumber->exists($this->request->projectvars['reportnumberID'])) {
            throw new NotFoundException(__('Invalid reportnumber'));
        }

        $this->Autorisierung->IsThisMyReport($this->request->projectvars['reportnumberID']);

        $this->request->onlyAllow('post', 'delete_workload');

        $afterEdit = $this->Navigation->afterEditModal(Router::url(array_merge(array('action' => 'list_workload'), $this->request->projectvars['VarsArray'])), 0);
        $this->ExaminerTime->recursive = -1;
        $logData = $this->ExaminerTime->find('first', array('conditions' => array('ExaminerTime.' . $this->ExaminerTime->primaryKey => $id)));

        if ($this->ExaminerTime->delete($id)) {
            $this->ExaminerTime->recursive = -1;
            $workloads = $this->ExaminerTime->find('all', array('conditions' => array('collision REGEXP' => '"workload":' . $id)));
            $workloads = array_map(function ($elem) {
                unset($elem['ExaminerTime']['collision']);
                $elem['ExaminerTime']['force_save'] = 1;
                return $elem;
            }, $workloads);

            $this->ExaminerTime->saveMany($workloads);

            $this->Flash->success(__('Examiner workload deleted'), array('key' => 'success'));
            $this->Autorisierung->Logger($id, $logData['ExaminerTime']);
        } else {
            $this->Flash->error(__('Examiner workload was not deleted'), array('key' => 'error'));
        }

        $SettingsArray = array();
        $SettingsArray['backlink'] = array('discription' => __('Back', true), 'controller' => 'examiners', 'action' => 'index', 'terms' => null);

        $this->set('SettingsArray', $SettingsArray);
        $this->set('afterEDIT', $afterEdit);

        $SettingsArray = array();
        $SettingsArray['backlink'] = array('discription' => __('Add', true), 'controller' => 'examiners', 'action' => 'list_workload', 'terms' => $this->request->projectvars['VarsArray']);

        $this->set('SettingsArray', $SettingsArray);
    }

    public function printworkload()
    {
    }

    //14.08.2017 Schmidt
    public function pdf()
    {
        $this->layout = 'pdf';

        if ($this->request->projectvars['VarsArray'][15] == 0 && isset($this->request->data['examiner_id']) && $this->request->data['examiner_id'] > 0) {
            $this->request->projectvars['VarsArray'][15] = $this->Sicherheit->Numeric($this->request->data['examiner_id']);
        }

        if ($this->request->projectvars['VarsArray'][15] > 0) {
            $examiner_id = $this->request->projectvars['VarsArray'][15];
        } else {
            $examiner_id = 0;
        }

        if (isset($this->request->data['showpdf']) && $this->request->data['showpdf'] == 1) {
            $ShowPdf = 1;
        }

        $arrayData = $this->Xml->DatafromXml('Examiner', 'file', null);

        if (!is_array($arrayData)) {
            return;
        }

        $this->set('xml_examiner', $arrayData['settings']);

        $options = array(
            'conditions' => array(
                'Examiner.deleted' => 0,
                'Examiner.id' => $examiner_id,
            ),
        );

        $this->Examiner->recursive = 1;
        $Examiner = $this->Examiner->find('first', $options);

        if ($Examiner['Examiner']['id'] == 0) {
            unset($Examiner['Examiner']);
        }

        if (count($Examiner) == 0) {
            return;
        }

        $this->loadModel('Testingcomp');

        $options = array(
            'conditions' => array(
                'Testingcomp.id' => $Examiner['Examiner']['testingcomp_id'],
            ),
        );
        $this->Testingcomp->recursive = -1;
        $Testingcomp = $this->Testingcomp->find('first', $options);
        $Examiner = array_merge($Examiner, $Testingcomp);

// Anfang

        $adress = null;
        $testingcomp = null;
        $contact = null;

        $output = $this->Qualification->ExaminerDataForPDF($Examiner);

        if (isset($output['adress'])) {
            $adress = $output['adress'];
        }

        if (isset($output['testingcomp'])) {
            $testingcomp = $output['testingcomp'];
        }

        if (isset($output['contact'])) {
            $testingcomp = $output['contact'];
        }

        if (file_exists(Configure::read('company_logo_folder') . $this->Auth->user('Testingcomp.id') . DS . 'logo.png')) {
            $this->request->data['examinerinfo']['logo'] = Configure::read('company_logo_folder') . $this->Auth->user('Testingcomp.id') . DS . 'logo.png';
        }

        //Sehtests
        $eyecheck_data_option = array(
            'order' => array('certified_date DESC'),
            'conditions' => array(
                'EyecheckData.examiner_id' => $examiner_id,
                'EyecheckData.deleted' => 0,
                'EyecheckData.active' => 1,
            ),
        );

        $arrayData = array();
        $arrayData = $this->Xml->DatafromXml('EyecheckData', 'file', null);
        if (!is_array($arrayData)) {
            return;
        }

        $this->set('xml_eyecheckdata', $arrayData['settings']);
        $this->Examiner->Eyecheck->EyecheckData->recursive = 0;
        $eyecheck_data = $this->Examiner->Eyecheck->EyecheckData->find('first', $eyecheck_data_option);

        $arrayData = array();
        $arrayData = $this->Xml->DatafromXml('Eyecheck', 'file', null);
        if (!is_array($arrayData)) {
            return;
        }
        $this->set('xml_eyecheck', $arrayData['settings']);

        $certificates = $this->Qualification->CertificatesSectors($Examiner);
        if (isset($certificates) && count($certificates) > 0) {
            $this->request->data['examinerinfo']['Certificate'] = $certificates;
        }

        //$summary = $this->Qualification->CertificateSummary($certificates,array('main'=>'Certificate','sub'=>'CertificateData'));
        $arrayData = $this->Xml->DatafromXml('Certificate', 'file', null);
        if (!is_array($arrayData)) {
            return;
        }
        $this->set('xml_certificate', $arrayData['settings']);

        $arrayData = $this->Xml->DatafromXml('CertificateData', 'file', null);
        if (!is_array($arrayData)) {
            return;
        }
        $this->set('xml_certificatedata', $arrayData['settings']);

        $SettingsArray = array();
        $this->request->projectvars['VarsArray'][15] = $examiner_id;

        $this->request->data['examinerinfo']['info'] = $Examiner;
        $this->request->data['examinerinfo']['contact'] = $contact;
        $this->request->data['examinerinfo']['adress'] = $adress;
        $this->request->data['examinerinfo']['Eyecheck'] = $eyecheck_data;

        if (isset($ShowPdf) && $ShowPdf == 1) {

            $this->layout = 'json';

            $this->autoRender = false;

            $PDFView = new View($this);
            $PDFView->autoRender = false;
            $PDFView->layout = null;
            $PDFView->set('return_show', true);

            $Data = $PDFView->render('pdf');

            $this->set('request', json_encode(array('string' => $Data)));
            $this->render('pdf_show');

            return;

        }

    } //14.08.2017 Schmidt

    public function sessionstorage()
    {
        if ($this->request->data['req_controller'] == 'examiners') {
            $act = $this->request->data['req_action'];
            switch ($act) {
                case 'qualification':
                    $this->Data->Sessionstoraging('Certificate');
                    break;
                case 'qualificationnew':
                    $this->Data->Sessionstoraging('Certificate');
                    break;
                default:
                    $this->Data->Sessionstoraging();
            }
            // $this->Data->Sessionstoraging();
        }
    }

    public function search()
    {
    }

    public function autocomplete()
    {
        App::uses('Sanitize', 'Utility');

        $this->layout = 'json';

        $fields = explode('_', Inflector::underscore($this->request->data['field']));

        if (count($fields) < 2) {
            $this->set('test', json_encode(array()));
            return;
        }

        unset($fields[0]);

        $field = Sanitize::clean(implode('_', $fields));

        $data = Sanitize::escape($this->request->data['term']);

        $Source = $this->Examiner->getDataSource('columns');
        $table = $Source->describe('examiners');

        if (!isset($table[$field])) {
            $this->set('test', json_encode(array()));
            return;
        }

        $options = array(
            'limit' => 20,
            'fields' => array($field),
            'group' => array($field),
            'conditions' => array(
                'Examiner.deleted' => 0,
                'Examiner.active' => 1,
                'Examiner.' . $field . ' LIKE' => '%' . $data . '%',
            ),
        );

        $result = $this->Examiner->find('list', $options);

        //$this->Autorisierung->WriteToLog($result);

        $this->set('test', json_encode($result));
    }

    public function addmonitoring()
    {
        $this->layout = 'modal';

        if ($this->request->projectvars['VarsArray'][15] > 0) {
            $examiner_id = $this->request->projectvars['VarsArray'][15];
        } else {
            throw new NotFoundException(__('Invalid value 2'));
        }

        $options = array(
            'conditions' => array(
                'Examiner.deleted' => 0,
                'Examiner.id' => $examiner_id,
                'Examiner.testingcomp_id' => $this->Autorisierung->ConditionsTestinccomps(),
            ),
        );

//        $arrayData = $this->Xml->DatafromXml('ExaminerMonitoring', 'file', null);
        $arrayData['settings'] = $this->Xml->CollectXMLFromFile('ExaminerMonitoring');

        if (isset($this->request->data['ExaminerMonitoring']) && ($this->request->is('post') || $this->request->is('put'))) {
            $this->request->data['ExaminerMonitoring']['examiner_id'] = $examiner_id;
            $this->request->data['ExaminerMonitoring']['testingcomp_id'] = $this->Auth->user('testingcomp_id');
            $this->request->data['ExaminerMonitoring']['user_id'] = $this->Auth->user('id');

            // muss noch im Helper gefixt werden
            //            $this->request->data['DeviceCertificate']['certificat'] = $this->request->data['DeviceCertificate'][0]['certificat'];
            //            unset($this->request->data['DeviceCertificate'][0]);

            //    $this->request->data = $this->Data->GeneralDropdownData(array('ExaminerMonitoring'), $arrayData, $this->request->data);
            $this->request->data = $this->Data->ChangeDropdownData($this->request->data, $arrayData, array('ExaminerMonitoring'));

            // Filter einfügen, keine doppelte Überwachungen zulassen
            // z.B. E-Checks für ein Gerät
            $ExaminerMonitoringExist = $this->Examiner->ExaminerMonitoring->find(
                'first',
                array(
                    'conditions' => array(
                        'ExaminerMonitoring.deleted' => 0,
                        'ExaminerMonitoring.examiner_id' => $examiner_id,
                        'ExaminerMonitoring.certificat' => $this->request->data['ExaminerMonitoring']['certificat'],

                    ),
                )
            );
            if (count($ExaminerMonitoringExist) > 0) {

                $this->Flash->error(__('The monitoring infos could not be saved. The following monitoring exists for this device') . ': ' . $this->request->data['ExaminerMonitoring']['certificat'], array('key' => 'error'));

            }

            if (count($ExaminerMonitoringExist) == 0) {
                $this->Examiner->ExaminerMonitoring->create();

                if ($this->Examiner->ExaminerMonitoring->save($this->request->data['ExaminerMonitoring'])) {

                    foreach ($this->request->data['ExaminerMonitoring'] as $_key => $_ExaminerMonitoring) {
                        $this->request->data['ExaminerMonitoringData'][$_key] = $_ExaminerMonitoring;
                    }

                    $this->request->data['ExaminerMonitoringData']['examiner_monitoring_id'] = $this->Examiner->ExaminerMonitoring->getLastInsertId();
                    $this->request->data['ExaminerMonitoringData']['testingcomp_id'] = $this->Auth->user('testingcomp_id');
                    $this->request->data['ExaminerMonitoringData']['user_id'] = $this->Auth->user('id');
                    $this->request->data['ExaminerMonitoringData']['examiner_id'] = $examiner_id;
                    $this->request->data['ExaminerMonitoringData']['first_certification'] = 0;
                    $this->request->data['ExaminerMonitoringData']['certified'] = 1;
                    $this->request->data['ExaminerMonitoringData']['certified_date'] = $this->request->data['ExaminerMonitoring']['first_registration'];
                    $this->request->data['ExaminerMonitoringData']['renewal_in_year'] = 0;
                    $this->request->data['ExaminerMonitoringData']['apply_for_recertification'] = 0;
                    $this->request->data['ExaminerMonitoringData']['deleted'] = 0;
                    $this->request->data['ExaminerMonitoringData']['active'] = 1;

                    $this->Examiner->ExaminerMonitoring->ExaminerMonitoringData->create();

                    if ($this->Examiner->ExaminerMonitoring->ExaminerMonitoringData->save($this->request->data['ExaminerMonitoringData'])) {

                        $this->Flash->success(__('The monitoring infos has been saved.'), array('key' => 'success'));

                        $this->Session->delete('ExaminerMonitoring.form');
                        $this->request->params['pass']['17'] = $this->Examiner->ExaminerMonitoring->getLastInsertId();
                        $this->request->params['pass']['18'] = $this->Examiner->ExaminerMonitoringData->getLastInsertId();

                        $this->request->params['action'] = 'monitoring';
                        $request_url = explode('/', $this->request->url);
                        $request_here = explode('/', $this->request->here);
                        $request_url[1] = 'monitoring';
                        $request_here[3] = 'monitoring';
                        $this->request->url = implode('/', $request_url);
                        $this->request->here = implode('/', $request_here);

                        $FormName['controller'] = 'examiners';
                        $FormName['action'] = 'monitorings';
                        $FormName['terms'] = implode('/', $this->request->projectvars['VarsArray']);

                        $this->set('saveOK', 1);
                        $this->set('FormName', $FormName);
                    } else {
                        $this->Examiner->ExaminerMonitoring->delete($this->Examiner->ExaminerMonitoring->getLastInsertId());
                        $this->Flash->error(__('The monitoring infos could not be saved. Please, try again.'), array('key' => 'error'));
                    }
                } else {
                    $this->Flash->error(__('The monitoring infos could not be saved. Please, try again.'), array('key' => 'error'));
                }
            }
        }

        $examiner = $this->Examiner->find('first', $options);

        $SettingsArray = array();

        if (!empty($arrayData['settings']->Examiner)) {
            foreach ($arrayData['settings']->Examiner->children() as $_key => $_settings) {
                $this->request->data[trim($_settings->model)][trim($_settings->key)] = null;
            }
        }

        $this->Data->SessionFormload('ExaminerMonitoring');
        $this->request->data = $this->Data->GeneralDropdownData(array('ExaminerMonitoring'), $arrayData, $this->request->data);

        $this->request->data['ExaminerMonitoring']['file'] = 1;
        $this->request->data['ExaminerMonitoring']['extern_intern'] = 0;
        $this->request->data['ExaminerMonitoring']['active'] = 1;
        $this->request->data['ExaminerMonitoring']['status'] = 0;

        $this->set('arrayData', $arrayData);
        $this->set('examiner', $examiner);
        $this->set('SettingsArray', $SettingsArray);
        $this->set('settings', $arrayData['settings']);
    }

    public function monitorings()
    {


        if ($this->request->projectvars['VarsArray'][15] > 0) {
            $examiner_id = $this->request->projectvars['VarsArray'][15];
        } else {
            throw new NotFoundException(__('Invalid value 2'));
        }

        $arrayData = $this->Xml->DatafromXml('Examiner', 'file', null);
        $xmlcert = $this->Xml->DatafromXml('ExaminerMonitoring', 'file', null);

        $options = array(
            'conditions' => array(
                'Examiner.id' => $examiner_id,
                'Examiner.testingcomp_id' => $this->Autorisierung->ConditionsTestinccomps(),
                'Examiner.deleted' => 0,
            ),
        );

        $model = array('top' => 'Examiner', 'main' => 'ExaminerMonitoring', 'sub' => 'ExaminerMonitoringData');
        $examiner = $this->Examiner->find('first', $options);
        $this->request->data = $examiner;

        $this->request->data = $this->Data->GeneralDropdownData(array('Examiner'), $arrayData, $this->request->data);

        //$testingmethodes = $device['DeviceTestingmethod'];

        $options = array(
            'order' => array('ExaminerMonitoring.certificat ASC'),
            'conditions' => array(
                'ExaminerMonitoring.examiner_id' => $examiner_id,
                'ExaminerMonitoring.deleted' => 0,
            ),
        );

        $examiner = $this->Examiner->ExaminerMonitoring->find('all', $options);
        //$this->Device->DeviceTestingmethod->recursive = -1;
        $this->Examiner->ExaminerMonitoring->ExaminerMonitoringData->recursive = -1;
        //$testingmethod = $this->Device->DeviceTestingmethod->find('first',array('conditions' => array('DeviceTestingmethod.id' => $testingmethod_id)));

        $options = array(
            'order' => array('ExaminerMonitoringData.examiner_id ASC'),
            'conditions' => array(
                'ExaminerMonitoringData.examiner_id' => $examiner_id,
                'ExaminerMonitoringData.deleted' => 0,
            ),
        );
        $option['order'] = 'ExaminerMonitoringData.id ASC';

        //$testingmethods = $this->Device->find('first',array('conditions'=>array('Device.id' => $device_id,'Device.testingcomp_id' => $this->Autorisierung->ConditionsTestinccomps())));

        if (count($examiner) > 0) {
            foreach ($examiner as $_key => $_examiner) {
                $option['conditions']['ExaminerMonitoringData.examiner_monitoring_id'] = $_examiner['ExaminerMonitoring']['id'];
                $option['conditions']['ExaminerMonitoringData.deleted'] = 0;
                $option['order'] = array('ExaminerMonitoringData.id DESC');
                $ExaminerMonitoringData = $this->Examiner->ExaminerMonitoring->ExaminerMonitoringData->find('first', $option);

                $examiner[$_key]['ExaminerMonitoringData'] = array_merge($examiner[$_key]['ExaminerMonitoringData'], $ExaminerMonitoringData['ExaminerMonitoringData']);

                $examiner[$_key] = $this->Qualification->DeviceCertification($examiner[$_key], $model); //->ExaminerMonitoring wird die Funktion heissen.!
            }
        }

        $SettingsArray = array();
        if (Configure::read('DeviceManager') == true) {
            $SettingsArray['devicelink'] = array('discription' => __('Devices administration', true), 'controller' => 'devices', 'action' => 'index', 'terms' => null);
        }

        if (Configure::read('DocumentManager') == true) {
            $SettingsArray['documentlink'] = array('discription' => __('Document administration', true), 'controller' => 'documents', 'action' => 'index', 'terms' => null);
        }

        //        $SettingsArray['addsearching'] = array('discription' => __('Search examiner', true), 'controller' => 'examiners', 'action'=>'search', 'terms'=>null, 'rel'=>'examiners');

        $breads = $this->Navigation->Breads(null);

        $breads[1]['discription'] = __('Examiner', true);
        $breads[1]['controller'] = 'examiners';
        $breads[1]['action'] = 'index';
        $breads[1]['pass'] = null;

        //$breads[2]['discription'] = $testingmethod['DeviceTestingmethod']['verfahren'];
        //$breads[2]['controller'] = 'examiners';
        //$breads[2]['action'] = 'view';
        //$breads[2]['pass'] = $this->request->projectvars['VarsArray'];

        $breads[2]['discription'] = $examiner[0]['Examiner']['first_name'] . ' ' . $examiner[0]['Examiner']['name'];
        $breads[2]['controller'] = 'examiners';
        $breads[2]['action'] = 'overview';
        $breads[2]['pass'] = $this->request->projectvars['VarsArray'];

        $breads[3]['discription'] = __('Monitorings', true);
        $breads[3]['controller'] = 'examiners';
        $breads[3]['action'] = 'monitorings';
        $breads[3]['pass'] = $this->request->projectvars['VarsArray'];

        $this->set('breads', $breads);

        $this->set('arrayData', $arrayData);
        $this->set('xmlcert', $xmlcert['settings']);
        $this->set('SettingsArray', $SettingsArray);
        $this->set('examiner', $examiner);
        //$this->set('testingmethod', $testingmethod);
    }

    public function replacemonitoring()
    {
        $this->layout = 'modal';

        //$testingmethod_id = $this->Sicherheit->Numeric($this->request->params['pass'][15]);
        $examiner_id = $this->Sicherheit->Numeric($this->request->params['pass'][15]);
        $examiner_certifificate_id = $this->Sicherheit->Numeric($this->request->params['pass'][16]);
        $examiner_monitoring_data_id = $this->Sicherheit->Numeric($this->request->params['pass'][17]);

        //        $this->request->projectvars['VarsArray'][17] = $examiner_certifificate_id;
        //        $this->request->projectvars['VarsArray'][18] = $examiner_monitoring_data_id;

        if (!$this->Examiner->exists($examiner_id)) {
            throw new NotFoundException(__('Invalid id 1'));
        }

        if (!$this->Examiner->ExaminerMonitoring->exists($examiner_certifificate_id)) {
            throw new NotFoundException(__('Invalid id 2'));
        }

        if (!$this->Examiner->ExaminerMonitoring->ExaminerMonitoringData->exists($examiner_monitoring_data_id)) {
            throw new NotFoundException(__('Invalid id 3'));
        }

        $arrayData = $this->Xml->DatafromXml('ExaminerMonitoringRefresh', 'file', null);

        $certificate_data_option = array(
            'order' => array('ExaminerMonitoringData.id DESC'),
            'conditions' => array(
                'ExaminerMonitoringData.id' => $examiner_monitoring_data_id,
                'ExaminerMonitoringData.deleted' => 0,
            ),
        );

        //        $this->Examiner->ExaminerMonitoring->ExaminerMonitoringData->recursive = -1;
        $certificate_data = $this->Examiner->ExaminerMonitoring->ExaminerMonitoringData->find('first', $certificate_data_option);

        if (isset($this->request->data['ExaminerMonitoringData']) && ($this->request->is('post') || $this->request->is('put'))) {

            // Die Daten aus dem Formular kommen in unterschiedlichen Arrays und werden vereiningt
            $replace_data = $this->request->data['ExaminerMonitoringData'];

            // Der alte Datensatz aus der Datenbank wird mit den neuen Werten aus dem Formular aktualisierst
            foreach ($certificate_data['ExaminerMonitoringData'] as $_key => $_certificate_data) {
                if (isset($replace_data[$_key])) {
                    $certificate_data['ExaminerMonitoringData'][$_key] = $replace_data[$_key];
                }
            }

            // Die ID wird entfernt, und noch ein paar Werte aktualisiert
            unset($certificate_data['ExaminerMonitoringData']['id']);
            $certificate_data['ExaminerMonitoringData']['testingcomp_id'] = $this->Auth->user('testingcomp_id');
            $certificate_data['ExaminerMonitoringData']['user_id'] = $this->Auth->user('id');

            // Die veralteten Datensätze werden auf inaktiv gestellt
            $this->Examiner->ExaminerMonitoring->ExaminerMonitoringData->updateAll(
                array(
                    'ExaminerMonitoringData.active' => 0,
                ),
                array(
                    'ExaminerMonitoringData.examiner_monitoring_id' => $certificate_data['ExaminerMonitoring']['id'],
                    'ExaminerMonitoringData.deleted' => 0,
                )
            );

            // neuer Datensatz und speichern
            $this->Examiner->ExaminerMonitoring->ExaminerMonitoringData->create();

            if ($this->Examiner->ExaminerMonitoring->ExaminerMonitoringData->save($certificate_data)) {
                $certificate_data_option = array(
                    'order' => array('ExaminerMonitoringData.id DESC'),
                    'conditions' => array(
                        'ExaminerMonitoringData.id' => $this->Examiner->ExaminerMonitoring->ExaminerMonitoringData->getLastInsertId(),
                        'ExaminerMonitoringData.deleted' => 0,
                    ),
                );

                $certificate_data = $this->Examiner->ExaminerMonitoring->ExaminerMonitoringData->find('first', $certificate_data_option);

                $this->Flash->success(__('The monitoring infos has been saved.'), array('key' => 'success'));

                $this->request->params['pass']['18'] = $this->Examiner->ExaminerMonitoring->ExaminerMonitoringData->getLastInsertId();

                $this->request->params['controller'] = 'monitoring';

                $FormName['controller'] = 'examiners';
                $FormName['action'] = 'monitorings';
                $FormName['terms'] = implode('/', $this->request->projectvars['VarsArray']);

                $this->set('settings', array());
                $this->set('certificate_data_old_infos', $certificate_data);
                $this->set('FormName', $FormName);

                return;
            } else {
                $this->Flash->error(__('The monitoring infos could not be saved. Please, try again.'), array('key' => 'error'));
            }
        }

        $this->request->data = $certificate_data;

        $this->request->data['ExaminerMonitoringData']['certified_date'] = null;
        $this->request->data['ExaminerMonitoringData']['status'] = 0;

        $model = array('top' => 'Examiner', 'main' => 'ExaminerMonitoring', 'sub' => 'ExaminerMonitoringData');
        $this->Qualification->SingleCertificateSummary($certificate_data, $model);

        $SettingsArray = array();

        $this->set('SettingsArray', $SettingsArray);
        $this->set('settings', $arrayData['orderoutput']);
    }

    public function editmonitoring()
    {
        $this->layout = 'modal';

        $examiner_id = $this->Sicherheit->Numeric($this->request->params['pass']['15']);
        $examiner_monitoring_id = $this->Sicherheit->Numeric($this->request->params['pass']['16']);
        $examiner_monitoring_data_id = $this->Sicherheit->Numeric($this->request->params['pass']['17']);

        if (!$this->Examiner->exists($examiner_id)) {
            throw new NotFoundException(__('Invalid id'));
        }

        if (!$this->Examiner->ExaminerMonitoring->ExaminerMonitoringData->exists($examiner_monitoring_data_id)) {
            throw new NotFoundException(__('Invalid id'));
        }

        if (isset($this->request->data['ExaminerMonitoringData']) && ($this->request->is('post') || $this->request->is('put'))) {
            $this->request->data['ExaminerMonitoring']['id'] = $examiner_monitoring_id;
            $this->request->data['ExaminerMonitoring']['user_id'] = $this->Auth->user('id');

            $this->request->data['ExaminerMonitoringData']['id'] = $examiner_monitoring_data_id;
            $this->request->data['ExaminerMonitoringData']['user_id'] = $this->Auth->user('id');

            unset($this->request->data['Order']);
            //unset($this->request->data['ExaminerMonitoring']);

            if ($this->Examiner->ExaminerMonitoring->ExaminerMonitoringData->save($this->request->data['ExaminerMonitoringData'])) {
                //                $this->Examiner->ExaminerMonitoring->save($this->request->data['ExaminerMonitoring']);
                $this->Flash->success(__('The monitoring infos has been saved'), array('key' => 'success'));

                $this->Autorisierung->Logger($examiner_monitoring_data_id, $this->request->data['ExaminerMonitoringData']);
            } else {
                $this->Flash->error(__('The monitoring infos could not be saved. Please, try again.'), array('key' => 'error'));
            }

            $FormName['controller'] = 'examiners';
            $FormName['action'] = 'monitorings';
            $FormName['terms'] = implode('/', $this->request->projectvars['VarsArray']);

            $this->set('FormName', $FormName);
        }

        $certificate_data_option = array(
            'order' => array('ExaminerMonitoringData.id DESC'),
            'conditions' => array(
                'ExaminerMonitoringData.id' => $examiner_monitoring_data_id,
                'ExaminerMonitoringData.deleted' => 0,
            ),
        );

        $certificate_data = $this->Examiner->ExaminerMonitoring->ExaminerMonitoringData->find('first', $certificate_data_option);

        if (isset($this->request->data['back']) && $this->request->data['back'] == 1) {
            $certificateinfo = 1;
        }

        $this->request->data = $certificate_data;
//        $arrayData = $this->Xml->DatafromXml('ExaminerMonitoring', 'file', null);
        $arrayData['settings'] = $this->Xml->CollectXMLFromFile('ExaminerMonitoringEdit');

        $model = array('top' => 'Examiner', 'main' => 'ExaminerMonitoring', 'sub' => 'ExaminerMonitoringData');
        $this->Qualification->SingleCertificateSummary($certificate_data, $model);

        $SettingsArray = array();

        $this->request->data = $this->Data->GeneralDropdownData(array('ExaminerMonitoring'), $arrayData, $this->request->data);
        $this->set('SettingsArray', $SettingsArray);
        $this->set('certificate_data', $certificate_data);
        $this->set('settings', $arrayData['settings']);

    }

    public function delmonitoring()
    {
        $this->layout = 'modal';

        $examiner_id = $this->Sicherheit->Numeric($this->request->params['pass']['15']);
        $examiner_monitoring_id = $this->Sicherheit->Numeric($this->request->params['pass']['16']);
        $examiner_monitoring_data_id = $this->Sicherheit->Numeric($this->request->params['pass']['17']);

        if (isset($this->request->data['ExaminerManagerAutocomplete'])) {
            unset($this->request->data['ExaminerManagerAutocomplete']);
        }

        if (!$this->Examiner->exists($examiner_id)) {
            throw new NotFoundException(__('Invalid id'));
        }

        if (!$this->Examiner->ExaminerMonitoring->ExaminerMonitoringData->exists($examiner_monitoring_data_id)) {
            throw new NotFoundException(__('Invalid id'));
        }

        $certificate_data_option = array(
            'order' => array('ExaminerMonitoringData.id DESC'),
            'conditions' => array(
                'ExaminerMonitoringData.id' => $examiner_monitoring_data_id,
                'ExaminerMonitoringData.deleted' => 0,
            ),
        );

        $certificate_data = $this->Examiner->ExaminerMonitoring->ExaminerMonitoringData->find('first', $certificate_data_option);

        if (isset($this->request->data['ExaminerMonitoring']) && ($this->request->is('post') || $this->request->is('put'))) {
            if ($examiner_monitoring_id != $this->request->data['ExaminerMonitoring']['id']) {
                $this->Flash->error(__('The monitoring infos could not be deleted. Please, try again.'), array('key' => 'error'));
                return;
            }

            unset($this->request->data['Order']);
            $this->request->data['ExaminerMonitoring']['id'] = $examiner_monitoring_id;
            $this->request->data['ExaminerMonitoring']['user_id'] = $this->Auth->user('id');
            $this->request->data['ExaminerMonitoring']['testingcomp_id'] = $this->Auth->user('testingcomp_id');
            $this->request->data['ExaminerMonitoring']['recertification_in_year'] = $certificate_data['ExaminerMonitoring']['recertification_in_year'];
            $this->request->data['ExaminerMonitoring']['horizon'] = $certificate_data['ExaminerMonitoring']['horizon'];
            $this->request->data['ExaminerMonitoring']['deleted'] = 1;

            if ($this->Examiner->ExaminerMonitoring->save($this->request->data)) {
                $this->Examiner->ExaminerMonitoring->ExaminerMonitoringData->updateAll(
                    array(
                        'ExaminerMonitoringData.deleted' => 1,
                    ),
                    array(
                        'ExaminerMonitoringData.Examiner_monitoring_id' => $examiner_monitoring_id,
                    )
                );

                $this->Flash->success(__('The monitoring has been deleted'), array('key' => 'success'));
                $this->Autorisierung->Logger($examiner_monitoring_data_id, $this->request->data['ExaminerMonitoring']);

                unset($this->request->params['pass']['17']);
                unset($this->request->params['pass']['18']);

                unset($this->request->projectvars['VarsArray']['17']);
                unset($this->request->projectvars['VarsArray']['18']);

                $FormName['controller'] = 'examiners';
                $FormName['action'] = 'monitorings';
                $FormName['terms'] = implode('/', $this->request->projectvars['VarsArray']);

                $this->set('saveOK', 1);
                $this->set('FormName', $FormName);
                return;
            } else {
                $this->Flash->error(__('The monitoring infos could not be deleted. Please, try again.'), array('key' => 'error'));
            }
        } else {
            $this->Flash->error(__('Should this monitoring be deleted?'), array('key' => 'error'));
        }

        $certificate_data_option = array('order' => array('ExaminerMonitoringData.id DESC'),
            'conditions' => array(
                'ExaminerMonitoringData.id' => $examiner_monitoring_data_id,
                'ExaminerMonitoringData.deleted' => 0,
            ),
        );

        $certificate_data = $this->Examiner->ExaminerMonitoring->ExaminerMonitoringData->find('first', $certificate_data_option);

        $this->request->data = $certificate_data;

        $arrayData = $this->Xml->DatafromXml('ExaminerMonitoringEdit', 'file', null);

        $model = array('top' => 'Examiner', 'main' => 'ExaminerMonitoring', 'sub' => 'ExaminerMonitoringData');
        $this->Qualification->SingleCertificateSummary($certificate_data, $model);

        $this->set('certificate_data', $certificate_data);
        $this->set('settings', $arrayData['settings']);

    }

    public function printoverview()
    {

        $this->layout = 'modal';
        $SettingsArray = array();

        if (isset($this->request->data['json_true']) && $this->request->data['json_true'] == 1) {
            $this->layout = 'json';
            $this->Session->write('PrintOverview', $this->request->data);
            $url = Router::url(['controller' => 'examiners', 'action' => 'pdfinv']);
//        $url = str_replace("%2C", "/", $url);

            $this->set('data', json_encode(array('url' => $url)));
            $this->render('json');
            return;
        }

        if (isset($this->request->data['Examiner'])) {
            $this->set('Output', false);
            $Examinerdata = $this->_pdfinv();
        }

        $Examinerdata = $this->_pdfinv();
        $WorkingPlaces['Examiner']['working_place'] = Hash::extract($Examinerdata, '{n}.working_place');
        $WorkingPlaces['Examiner']['working_place'] = array_unique($WorkingPlaces['Examiner']['working_place']);
        foreach ($WorkingPlaces['Examiner']['working_place'] as $key => $value) {
            $WorkingPlace['Examiner']['working_place'][$value] = $value;
        }
        //    sort($WorkingPlace['Examiner']['working_place']);
        natsort($WorkingPlace['Examiner']['working_place']);

        $WorkingPlace['Examiner']['page_break_options'] = array(0 => __('Einzelseiten', true), 1 => __('Gesamtseiten', true));

        $this->request->data = $WorkingPlace;

        $this->set('SettingsArray', $SettingsArray);
    }

    public function sendoverview()
    {

        App::uses('CakeEmail', 'Network/Email');
        $this->layout = 'modal';
        $SettingsArray = array();

        if (isset($this->request->data['Examiner'])) {

            $this->set('Output', false);
            $Examinerdata = $this->_pdfinv();

            $Examinerdata = $this->Qualification->FilterExaminerDataForPDF($Examinerdata);

            $Microtime = microtime();
            $Microtime = str_replace(array('.', ' '), '_', $Microtime);
            $Filename = $Microtime . '_' . uniqid();

            $EmailView = new View($this);
            $EmailView->autoRender = false;
            $EmailView->layout = null;
            $EmailView->set('Filename', $Filename);
            $EmailView->set('Examiners', $Examinerdata);
            $EmailView->set('Output', false);

            if (count($Examinerdata) > 0) {
                $temp = sys_get_temp_dir() . DS;
                $SendData = $EmailView->render('pdfinv');
                file_put_contents($temp . $Filename . '.pdf', $SendData);
            }

            $emailto = $this->request->data['Examiner']['email'];
            $subject = 'Test of Übersicht';

            $email = new CakeEmail('default');
            $email->domain(FULL_BASE_URL);
            $email->from(array('reminder@docu-dynamics.cloud' => 'Docu-Dynamics'));
            $email->sender(array('reminder@docu-dynamics.cloud' => 'Docu-Dynamics'));
            $email->to($emailto);
            $email->subject($subject);
            //      $email->template($mailview, 'summary');
            $email->emailFormat('both');
            $email->attachments(array(
                'uebersicht.pdf' => array(
                    'file' => $temp . $Filename . '.pdf',
                    'mimetype' => 'application/pdf',
                    'contentId' => $Filename,
                ),
            ));

            if ($email->send()) {
                $this->Flash->success(__('Email gesendet'), array('key' => 'success'));
            } else {
                $this->Flash->error(__('Fehler beim senden, bitte versuchen Sie es erneut'), array('key' => 'error'));
            }
        }

        $Examinerdata = $this->_pdfinv();
        $WorkingPlace['Examiner']['working_place'] = Hash::extract($Examinerdata, '{n}.working_place');
        $WorkingPlace['Examiner']['working_place'] = array_unique($WorkingPlace['Examiner']['working_place']);
        sort($WorkingPlace['Examiner']['working_place']);
        natsort($WorkingPlace['Examiner']['working_place']);

        $WorkingPlace['Examiner']['page_break_options'] = array(0 => __('Einzelseiten', true), 1 => __('Gesamtseiten', true));

        $this->request->data = $WorkingPlace;

        $this->set('SettingsArray', $SettingsArray);

    }

    public function pdfinv()
    {

        $this->layout = 'pdf';

        $Examinerdata = $this->_pdfinv();

        $Examinerdata = $this->Qualification->FilterExaminerDataForPDF($Examinerdata);
        $this->set('Output', true);
        $this->set('Examiners', $Examinerdata);
    }

    protected function _pdfinv()
    {

        if ($this->Session->check('searchexaminer.examiners')) {
            $sessionexaminer = $this->Session->read('searchexaminer.examiners');
        }

        if (isset($sessionexaminer) && !empty($sessionexaminer)) {
            $examiners = $this->Session->read('searchexaminer.examiners');
        } else {

            $order = array('Examiner.name' => 'asc', 'Examiner.first_name' => 'asc');
            $order = $this->Data->SortExaminerTable($order);
            $options = $this->Data->ConditionsExaminer();

            $this->Examiner->recursive = 1;
            $examiners = $this->Examiner->find('all', array(
                'order' => $order,
                'conditions' => $options,

            ));
        }

        $arrayData = null;
        $arrayData = $this->Xml->DatafromXml('Examiner_Inv', 'file', null);

        if (!is_array($arrayData)) {
            return;
        }

        $this->set('xml_examinerinv', $arrayData["settings"]);

        if (file_exists(Configure::read('company_logo_folder') . $this->Auth->user('Testingcomp.id') . DS . 'logo.svg')) {
            $this->request->data['Testingcomp'] = $this->Auth->user('Testingcomp');
            $this->request->data['Testingcomp']['logo_suffix'] = 'svg';
            $this->request->data['Testingcomp']['logo'] = Configure::read('company_logo_folder') . $this->Auth->user('Testingcomp.id') . DS . 'logo.svg';
        } elseif (file_exists(Configure::read('company_logo_folder') . $this->Auth->user('Testingcomp.id') . DS . 'logo.png')) {
            $this->request->data['Testingcomp'] = $this->Auth->user('Testingcomp');
            $this->request->data['Testingcomp']['logo_suffix'] = 'png';
            $this->request->data['Testingcomp']['logo'] = Configure::read('company_logo_folder') . $this->Auth->user('Testingcomp.id') . DS . 'logo.png';
        }

        $model = array('top' => 'Examiner', 'main' => 'ExaminerMonitoring', 'sub' => 'ExaminerMonitoringData');

        foreach ($examiners as $key => $value) {
            $Examinerdata[$key] = $this->Qualification->CollectCertificateInfos($value);
        }

        return $Examinerdata;

    }

    public function files()
    {
        $this->layout = 'modal';

        $models['top'] = 'Examiner';
        $models['main'] = null;
        $models['sub'] = null;
        $models['file'] = 'Examinerfile';
        $certificatefile_id = 0;
        $this->loadModel($models['file']);

        $examiner_id = $this->Sicherheit->Numeric($this->request->params['pass']['15']);

        if (!$this->Examiner->exists($examiner_id)) {
            throw new NotFoundException(__('Invalid examiner'));
        }

        $path = Configure::read('examiner_folder') . $examiner_id . DS . 'documents' . DS;

        $this->_files($path, 0, $examiner_id, $certificatefile_id, $models);

        $SettingsArray = array();
        $this->set('SettingsArray', $SettingsArray);
    }

    public function stamps()
    {
        $this->layout = 'modal';
        $this->loadModel('ExaminersStamp');

        $examiner_id = $this->Sicherheit->Numeric($this->request->params['pass']['15']);

        if (!$this->Examiner->exists($examiner_id)) {
            throw new NotFoundException(__('Invalid examiner'));
        }

        if (!empty($examiner_id)) {
            $savePath = Configure::read('examiner_folder') . 'stamps' . DS . $examiner_id . DS;

            $stamp = $this->Examiner->ExaminersStamp->find('first', array('conditions' => array('examiner_id' => $examiner_id)));
            $examiner = $this->Examiner->find('first', array('conditions' => array('Examiner.id' => $examiner_id), 'fields' => array('name')));

            if (isset($stamp['ExaminersStamp']) && file_exists($savePath . DS . $stamp['ExaminersStamp']['file_name']) !== false) {
                if (is_countable($stamp) && count($stamp) > 0) {

                    $encodedSVG = \rawurlencode(\str_replace(["\r", "\n"], ' ', \file_get_contents($savePath . DS . $stamp['ExaminersStamp']['file_name'])));
                    $this->set('ImageData', $encodedSVG);
                    $this->set('HasFile', true);
                }
            } else {
                $this->set('HasFile', false);
            }
            if (isset($examiner['Examiner']['name'])) {
                $this->set('examiner_name', $examiner['Examiner']['name']);
            }
        }
        //$this->_files($path, 0, $examiner_id, $certificatefile_id, $models);
        $this->set('examiner_id', $examiner_id);
        $SettingsArray = array();
        $this->set('SettingsArray', $SettingsArray);
    }

    public function getfiles()
    {
        //$testingmethod_id = $this->Sicherheit->Numeric($this->request->params['pass']['15']);
        $examiner_id = $this->Sicherheit->Numeric($this->request->params['pass']['15']);
        $file_id = $this->Sicherheit->Numeric($this->request->params['pass']['16']);

        $models = array('main' => 'Examinerfile', 'sub' => null);
        $path = Configure::read('examiner_folder') . $examiner_id . DS . 'documents' . DS;

        $this->loadModel($models['main']);
        $this->_getfiles($path, $models);
    }

    public function monitoringfile()
    {
        $this->layout = 'modal';

        $examiner_id = $this->Sicherheit->Numeric($this->request->params['pass']['15']);
        $certificate_id = $this->Sicherheit->Numeric($this->request->params['pass']['16']);
        $certificate_data_id = $this->Sicherheit->Numeric($this->request->params['pass']['17']);

        $SettingsArray = array();
        $SettingsArray['backlink'] = array('discription' => __('Back', true), 'controller' => 'examiners', 'action' => 'editmonitoring', 'terms' => $this->request->params['pass']);

        $this->set('SettingsArray', $SettingsArray);

        if (isset($this->request->data['close_reload']) && $this->request->data['close_reload'] == 1) {
            $FormName['controller'] = 'examiners';
            $FormName['action'] = 'monitorings';
            $FormName['terms'] = implode('/', $this->request->projectvars['VarsArray']);
            $this->set('FormName', $FormName);
        }

        $models = array('top' => 'Examiner', 'main' => 'ExaminerMonitoring', 'sub' => 'ExaminerMonitoringData');
        $path = Configure::read('monitoring_folder') . $examiner_id . DS . $certificate_id . DS . $certificate_data_id . DS;
        $this->_monitoringfileupload($path, $models);
    }

    protected function _monitoringfileupload($path, $models)
    {
        $examiner_id = $this->Sicherheit->Numeric($this->request->params['pass']['15']);
        $certificate_id = $this->Sicherheit->Numeric($this->request->params['pass']['16']);
        $certificate_data_id = $this->Sicherheit->Numeric($this->request->params['pass']['17']);
        $sub = $models['sub'];

        $main = $models['main'];
        $_data_option = array(
            'conditions' => array(
                $models['sub'] . '.id' => $certificate_data_id,
                $models['sub'] . '.examiner_monitoring_id' => $certificate_id,
                $models['sub'] . '.deleted' => 0,
//                        $models['sub'] . '.active' => 1
            ),
        );

        $_data = $this->Examiner->$main->$sub->find('first', $_data_option);

        if ($_data[$models['sub']]['certified_file'] != '' && file_exists($path . $_data[$models['sub']]['certified_file'])) {
            $this->set('hint', __('Do you want to overwrite the existing file?', true));
        }

        $this->set('data', $_data);
        $this->set('models', $models);

        $Extension = 'pdf';

        if (isset($_FILES) && count($_FILES) > 0) {

            // Infos zur Datei
            $fileinfo = pathinfo($_FILES['file']['name']);

            $Microtime = microtime();
            $Microtime = str_replace(array('.', ' '), '_', $Microtime);
            $Filename = $Microtime . '_' . uniqid();

            $filename_new = $examiner_id . '_' . $certificate_id . '_' . $certificate_data_id . '_' . $Filename . '.' . $fileinfo['extension'];

            if ($Extension == strtolower($fileinfo['extension'])) {
                if (isset($_FILES['data']['error'][$models['top']]['file'])) {
                    $this->Session->write('FileUploadErrors', $_FILES['data']['error'][$models['top']]['file']);
                }

                // Vorhandene Dateien werden gelöscht
                if ($_FILES['file']['error'] == 0) {
                    $folder = new Folder($path);
                    $folder->delete();

                    if (!file_exists($path)) {
                        $dir = new Folder($path, true, 0755);
                    }
                }

                $this->Session->write('FileUploadErrors', $_FILES['file']['error']);
                move_uploaded_file($_FILES['file']["tmp_name"], $path . DS . $filename_new);

                if ($_FILES['file']['error'] == 0) {
                    $dataFiles = array(
                        'id' => $certificate_data_id,
                        'certified_file' => $filename_new,
                        'user_id' => $this->Auth->user('id'),
                    );

                    $this->Examiner->$main->$sub->save($dataFiles);
                }
            }
        }
    }

    public function getmonitoringfile()
    {

        $examiner_id = $this->Sicherheit->Numeric($this->request->params['pass']['15']);
        $certificate_id = $this->Sicherheit->Numeric($this->request->params['pass']['16']);
        $certificate_data_id = $this->Sicherheit->Numeric($this->request->params['pass']['17']);
        $models = array('main' => 'ExaminerMonitoring', 'sub' => 'ExaminerMonitoringData');
        $path = Configure::read('monitoring_folder') . $examiner_id . DS . $certificate_id . DS . $certificate_data_id . DS;
        $this->_getfile($path, $models);
    }

    public function examinerfilesdescription()
    {

        $this->layout = 'modal';

        $examiner_id = $this->Sicherheit->Numeric($this->request->params['pass']['15']);
        $certificate_id = $this->Sicherheit->Numeric($this->request->params['pass']['16']);
        $file_id = $this->Sicherheit->Numeric($this->request->params['pass']['17']);

        $this->loadModel('Examinerfile');

        $file = $this->Examinerfile->find('first', array('conditions' => array('Examinerfile.id' => $file_id)));

        if (isset($this->request->data['Examinerfile']) && ($this->request->is('post') || $this->request->is('put'))) {

            if ($this->Examinerfile->save($this->request->data['Examinerfile'])) {

                $FormName['controller'] = 'examiners';
                $FormName['action'] = 'files';
                $FormName['terms'] = implode('/', $this->request->projectvars['VarsArray']);

                $this->set('FormName', $FormName);

                $this->Flash->success(__('Value was saved.'), array('key' => 'success'));

            } else {

                $this->Flash->error(__('Value could not be saved. Please, try again.'), array('key' => 'error'));

            }
        }

        $this->request->data = $file;

        $SettingsArray = array();
        $SettingsArray['backlink'] = array('discription' => __('Back', true), 'controller' => 'examiners', 'action' => 'files', 'terms' => $this->request->projectvars['VarsArray']);

        $this->set('SettingsArray', $SettingsArray);

    }

    public function delexaminerfiles()
    {
        $this->layout = 'modal';

        $models['top'] = 'Examiner';
        $models['main'] = null;
        $models['sub'] = null;
        $models['file'] = 'Examinerfile';

        if (isset($this->request->data['ExaminerManagerAutocomplete'])) {
            unset($this->request->data['ExaminerManagerAutocomplete']);
        }

        $this->_delfiles($models, 'files');

        $this->Flash->error(__('Will you delete this file?', true), array('key' => 'error'));

        $lastModal = array(
            'controller' => 'examiner',
            'action' => 'overview',
            'terms' => $this->request->projectvars['VarsArray'],
        );

        $SettingsArray = array();
        $SettingsArray['backlink'] = array('discription' => __('Back', true), 'controller' => 'examiners', 'action' => 'files', 'terms' => $lastModal['terms']);

        $this->set('SettingsArray', $SettingsArray);
    }

    public function infos()
    {
        $this->layout = 'modal';
    }
}
