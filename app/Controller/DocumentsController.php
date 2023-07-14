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
class DocumentsController extends AppController
{
    public $components = array('Qualification','Data','Auth','Acl','Autorisierung','Cookie','Navigation','Lang','Sicherheit','SelectValue', 'Paginator','Xml');
    protected $writeprotection = false;

    public $helpers = array('Lang','Navigation','JqueryScripte','Pdf','Quality');
    public $layout = 'ajax';

    public function _validateDate($date)
    {
        if (!is_string($date)) {
            return false;
        }
        $d = DateTime::createFromFormat('Y-m-d', $date);
        return $d && $d->format('Y-m-d') == $date;
    }

    public function beforeFilter()
    {
        if (Configure::read('DocumentManager') == false) {
            die();
        }

        App::import('Vendor', 'Authorize');
        $this->loadModel('User');
        //	$this->Autorisierung->Protect();

        $noAjaxIs = 0;
        $noAjax = array('email','quicksearch','files','getfiles','certificatefile','getcertificatefile','autocomplete');

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

        $this->set('lang', $this->Lang->Choice());
        $this->set('selected', $this->Lang->Selected());
        $this->set('menues', $this->Navigation->Menue());
        //		$this->set('breadcrumbs', $this->Navigation->Breadcrumb());
        $this->set('login_info', $this->Navigation->loggedUser());
        $this->set('lang_choise', $this->Lang->Choice());
        $this->set('lang_discription', $this->Lang->Discription());
        $this->set('previous_url', $this->base.'/'.$this->Session->read('lastURL'));
        $this->set('locale', $this->Lang->Discription());

        if (Configure::check('DocumentManagerSearchField')) {
            $quicksearchfield = Configure::read('DocumentManagerSearchField');
        }
        $arrayData = $this->Xml->XmltoArray('Document', 'file', null);
        $locale = $this->Lang->Discription();
        foreach ($arrayData->Document->children() as $_settings => $value) {
            if (trim($value->key) == $quicksearchfield) {
                $searchdescription =  trim($value->discription->$locale);
            }
        }
        // Werte für die Schnellsuche
        $ControllerQuickSearch =
            array(
                'Model' => 'Document',
                'Field' => $quicksearchfield,
                'description' => $searchdescription,
                'minLength' => 2,
                'targetcontroller' => 'documents',
                'target_id' => 'document_id',
                'targetation' => 'view',
                'Quicksearch' => 'QuickDocumentsearch',
                'QuicksearchForm' => 'QuickDocumentsearchForm',
                'QuicksearchSearchingAutocomplet' => 'QuickDocumentsearchSearchingAutocomplet',
        );

        $this->set('ControllerQuickSearch', $ControllerQuickSearch);




        $DocumentManagerAutocomplete = null;

        if (Configure::check('DocumentManagerSearchAutocomplete') && Configure::read('DocumentManagerSearchAutocomplete') == true) {
            $DocumentManagerAutocomplete =
                array(
                    'Model' => 'Document',
                );
        }

        $this->request->data['DocumentManagerSearchAutocomplete'] = $DocumentManagerAutocomplete;

        if (isset($this->Auth)) {
            $this->set('authUser', $this->Auth);
        }
    }

    public function afterFilter()
    {
        $this->Navigation->lastURL();
    }

    public function quicksearch()
    {
        App::uses('Sanitize', 'Utility');

        if (Configure::check('DocumentManagerSearchField')) {
            $quicksearchfield = Configure::read('DocumentManagerSearchField');
        }


        $this->layout = 'json';

        $term = Sanitize::stripAll($this->request->data['term']);
        $term = Sanitize::paranoid($term);

        $options = array(

                        'limit' => 10,
                        'conditions' => array(
//								'Document.testingcomp_id' => $this->Autorisierung->ConditionsTopprojects(),
                                'Document.'.$quicksearchfield.' LIKE' => '%'.$term.'%',
                                'Document.deleted' => 0
                            )
                        );

        $this->Document->recursive = -1;
        $document = $this->Document->find('all', $options);

        $response = array();

        if (Configure::check('DocumentManagerDisplayFields')) {
            $displaysearchfields = Configure::read('DocumentManagerDisplayFields');
            $displayfields = explode(".", $displaysearchfields);
            //pr($displayfields);
            // pr($displayfields);
        }
        //  pr($document);
        foreach ($document as $_key => $_document) {
            $responsevalue = '';
            foreach ($displayfields as $dkey => $dvalue) {
                isset($_document['Document'][$dvalue]) ? $responsevalue.= $_document['Document'][$dvalue].' ' : '';
            }
            array_push($response, array('key' => $_document['Document']['id'],'value' => $responsevalue));
        }

        $this->set('response', json_encode($response));
    }

    public function barcode()
    {
        if (Configure::read('BarcodeDocumentScanner') === false) {
            die(__('Deactiv', true));
        }

        App::uses('Sanitize', 'Utility');

        $this->layout = 'json';
        $barcode = Sanitize::stripAll($this->request->data['barcode']);
        $barcode = Sanitize::paranoid($barcode);

        $options = array(
                        'conditions' => array(
                                'Document.barcode' => $barcode,
                                'Document.deleted' => 0
                            )
                        );

        $this->Document->recursive = 1;
        $document = $this->Document->find('all', $options);

        if (!empty($document)) {
            if (count($document) == 1) {
                $message['text'] = __('1 Document found', true);
                $message['status'] = 'one';
                $message['class'] = 'hint';
                $this->request->projectvars['VarsArray'][15] = $document[0]['DocumentTestingmethod'][0]['id'];
                $this->request->projectvars['VarsArray'][16] = $document[0]['Document']['id'];
            }
            if (count($document) > 1) {
                $message['text'] = __(count($document).' '.'Documents found.', true);
                $message['status'] = 'more';
                $message['class'] = 'hint';
            }
        } elseif (empty($document)) {
            $message['text'] = __('Document not found', true);
            $message['class'] = 'error';
        }

        $this->set('message', $message);
        $this->set('document', $document);
    }

    public function save($id = null)
    {
        // Die IDs des Projektes und des Auftrages werden getestet

        $this->layout = 'blank';

        //		$testingmethod_id = $this->Sicherheit->Numeric($this->request->params['pass']['15']);


        $this_id = $this->request->data['this_id'];
        $orginal_data = $this->request->data['orginal_data'];
        $save_data = array();

        unset($this->request->data['orginal_data']);

        foreach ($this->request->data as $_key => $_data) {
            if (!is_array($_data)) {
                unset($this->request->data[$_key]);
            }
        }

        $Model = key($this->request->data);
        //if(empty($Model)) $Model = 'Document';
        // !!!!!!!!!!!!!!!!!!!!!!!!!!!!!
        // Unbedingt noch eine Modelüberprüfung einbauen
        // !!!!!!!!!!!!!!!!!!!!!!!!!!!!!

        if ($Model == 'Document') {
            $id = $this->Sicherheit->Numeric($this->request->params['pass']['16']);
        }
        if ($Model == 'DocumentCertificate') {
            $id = $this->Sicherheit->Numeric($this->request->params['pass']['17']);
        }
        if ($Model == 'DocumentCertificateData') {
            $id = $this->Sicherheit->Numeric($this->request->params['pass']['18']);
        }

        if (!isset($id)) {
            $this->set('error', __('The value could not be saved. Wrong parameters!'));
            $this->render();
            return;
        }

        if (!$this->loadModel($Model)) {
            $this->loadModel($Model);
        }

        if (!$this->$Model->exists($id)) {
            throw new NotFoundException(__('Invalid ID'));
        }

        $field_key = key($this->request->data[key($this->request->data)]);

        if ($field_key == '0') {
            $field_key = key($this->request->data[$Model][0]);
            $this_value = $this->request->data[$Model][0][$field_key];
        } else {
            $this_value = $this->request->data[$Model][$field_key];
        }

        foreach ($orginal_data as $_key => $_orginal_data) {
            foreach ($_orginal_data as $__key => $__orginal_data) {
                if ($__key == '0') {
                    $orginal_data[$_key][key($__orginal_data)] = $__orginal_data[key($__orginal_data)];
                    unset($orginal_data[$_key][0]);
                }
                if (is_array($__orginal_data)) {
                    if (is_numeric(key($__orginal_data))) {
                        $this_value_array = explode(',', str_replace(' ', '', $this_value));
                    }
                }
            }
        }

        $save_data[$Model]['id'] = $id;
        $save_data[$Model]['user_id'] = $this->Auth->user('id');

        if (isset($orginal_data[$Model][$field_key]) && is_array($orginal_data[$Model][$field_key])) {
            $save_data[$Model][$field_key] = $this_value_array;
        } else {
            $save_data[$Model][$field_key] = $this_value;
        }

        $orginalData = $this->$Model->find('first', array('conditions' => array($Model.'.id' => $id)));

        $last_value = $this_value;
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

        if ($this->$Model->save($save_data)) {
            $this->Autorisierung->Logger($id, $orginal_data);
            $this->set('success', __('The value has been saved.'));
        } else {
            $this->set('error', __('The value could not be saved. Please try again!'));
        }
    }

    public function index()
    {
        $this->Navigation->ResetAllSessionForPaging();

        if (isset($this->request->data['landig_page']) && $this->request->data['landig_page'] == 1) {
            $landig_page = 1;
        }
        if (isset($this->request->data['landig_page_large']) && $this->request->data['landig_page_large'] == 1) {
            $landig_page_large = 1;
        }

        $methods = array();
        $this->Session->delete('searchdocument');

        if ($this->request->projectvars['VarsArray'][1] > 0) {
            $this->Document->recursive = 1;
            $_dev = $this->Document->find('first', array('conditions'=>array('Document.id'=>$this->request->projectvars['VarsArray'][1])));

            $methods = Hash::extract($_dev, 'DocumentTestingmethod.{n}.id');
        }

        $documents_testingmethods = $this->Document->DocumentTestingmethod->find(
            'all',
            array(
                'conditions' => array_filter(array(
                        'DocumentTestingmethod.id' => $methods
                )),
                'contain' => array(
                    'Document' => array(
                        'fields' => array('id'),
                        'conditions' => array(
                            'Document.testingcomp_id' => $this->Auth->user('testingcomp_id')
                        )
                    )
                )
            )
        );

        foreach ($documents_testingmethods as $_key => $_documents_testingmethods) {
            $iddevices = array();
            $iddevices = $this->Data->BelongsToManySelected($_documents_testingmethods, 'DocumentTestingmethod', 'Document', array('TestingmethodsDocuments','document_id','testingmethod_id'));

            $iddevices = $iddevices ['Document']['selected'];
            $devices = $this->Document->find('all', array('conditions'=>array('Document.id' =>$iddevices))) ;

            $documents_testingmethods[$_key]['Document'] = $devices;
            //			pr($documents_testingmethods);
            if (count($devices) == 0) {
                unset($documents_testingmethods[$_key]);
            } else {
                //unset($documents_testingmethods[$_key]['Document']);
            }
        }

        $SettingsArray = array();
        //		$SettingsArray['addlink'] = array('discription' => __('Add document',true), 'controller' => 'documents','action' => 'add', 'terms' => null);

        if (Configure::read('CertifcateManager') == true) {
            $SettingsArray['examinerlink'] = array('discription' => __('Examiners administration', true), 'controller' => 'examiners','action' => 'index', 'terms' => null);
        }

        if (Configure::read('DeviceManager') == true) {
            $SettingsArray['devicelink'] = array('discription' => __('Devices administration', true), 'controller' => 'devices','action' => 'index', 'terms' => null);
        }

        if (Configure::read('search.examiner') == true) {
            //			$SettingsArray['addsearching'] = array('discription' => __('Search examiner', true), 'controller' => 'testingcomps', 'action'=>'search', 'terms'=>null, 'rel'=>'documents');
        }


        if (Configure::read('search.documents') == true) {
            $SettingsArray['addsearching'] = array('discription' => __('Search document', true), 'controller' => 'documents', 'action'=>'search', 'terms'=>null, 'rel'=>'documents');
        }


        $breads = $this->Navigation->Breads(null);

        $breads[1]['discription'] = __('Documents', true);
        $breads[1]['controller'] = 'documents';
        $breads[1]['action'] = 'index';
        $breads[1]['pass'] = null;

        $this->set('breads', $breads);
        $this->set('SettingsArray', $SettingsArray);
        $this->set('documents_testingmethods', $documents_testingmethods);

        if (isset($landig_page_large) && $landig_page_large == 1) {
            $this->render('landingpage_large', 'blank');
            return;
        }
    }

    public function overview()
    {
        $this->Navigation->GetSessionForPaging();

        if ($this->request->projectvars['VarsArray'][15] > 0) {
            $testingmethod_id = $this->request->projectvars['VarsArray'][15];
        } else {
            if (isset($this->request->data['QuickDocumentsearch'])) {
                $document_id = ($this->Sicherheit->Numeric($this->request->data['document_id']));

                if ($document_id == 0) {
                    throw new NotFoundException(__('Invalid value'));
                }
            } else {
                throw new NotFoundException(__('Invalid value'));
            }
        }

        $this->Document->recursive = 1;
        $this->Document->DocumentTestingmethod->recursive = -1;
        if (isset($testingmethod_id)) {
            $testingmethod = $this->Document->DocumentTestingmethod->find('first', array('conditions' => array('DocumentTestingmethod.id' => $testingmethod_id)));
        }

        $OrderBy = array(
            'Document.document_type' => 'desc',
            'Document.name' => 'asc'
            );

        if (!isset($document_id)) {
            $this->paginate = array(
                'order' => $OrderBy,
                'limit' => 10,
                'conditions' => array(
                        'Document.deleted' => 0,
                        'Document.testingcomp_id' => $this->Auth->user('testingcomp_id')
                    ),
                'joins' => array(
                    array(
                        'table' => 'documents_testingmethods_documents',
                        'alias' => 'DocumentTestingmethod',
                        'type' 	=> 'INNER',
                        'conditions' => array(
                            'DocumentTestingmethod.testingmethod_id' => $testingmethod_id,
                            'DocumentTestingmethod.document_id = Document.id'
                        )
                    )
                )
            );
            $documents = $this->paginate('Document');
        } elseif (isset($document_id)) {
            $this->paginate = array(
                'order' => $OrderBy,
                'limit' => 1,
                'conditions' => array(
                    'Document.id' => $document_id
                )
            );
            $documents = $this->paginate('Document');

            if (count($documents) == 0) {
                $this->render();
                return;
            } elseif (count($documents) > 0) {
                $this->request->projectvars['VarsArray'][15] = $testingmethod_id;
            }

            // Die Url für das Actionatribut im Quicksearchform muss angepasst werden
            // Wenn das Eingabefeld geleert wird muss die aktuelle Seite mit Parametern aufgerufen werden
            // sonst kommt ein Error
            $this->request->here = $this->request->here.'/'.implode('/', $this->request->projectvars['VarsArray']);
        }

        foreach ($documents as $_key => $_documents) {
            $path = Configure::read('document_folder') .$testingmethod_id . DS . $_documents['Document']['id']. DS;

            if (!empty($_documents['Document']['certified_file']) && file_exists($path .$_documents['Document']['certified_file'])) {
                $documents[$_key]['Document']['certified_file_valide'] = 1;
            } else {
                $documents[$_key]['Document']['certified_file_valide'] = 0;
            }
        }

        if (isset($testingmethod)) {
            $this->set('testingmethod', $testingmethod);
        }

        $SettingsArray = array();

        if (Configure::read('CertifcateManager') == true) {
            $SettingsArray['examinerlink'] = array('discription' => __('Examiners administration', true), 'controller' => 'examiners','action' => 'index', 'terms' => null);
        }

        if (Configure::read('DeviceManager') == true) {
            $SettingsArray['devicelink'] = array('discription' => __('Devices administration', true), 'controller' => 'devices','action' => 'index', 'terms' => null);
        }

        $breads = $this->Navigation->Breads(null);

        $breads[1]['discription'] = __('Documents', true);
        $breads[1]['controller'] = 'documents';
        $breads[1]['action'] = 'index';
        $breads[1]['pass'] = null;

        $breads[2]['discription'] = $testingmethod['DocumentTestingmethod']['verfahren'];
        $breads[2]['controller'] = 'documents';
        $breads[2]['action'] = 'overview';
        $breads[2]['pass'] = $this->request->projectvars['VarsArray'];

        $this->set('breads', $breads);
        $this->set('SettingsArray', $SettingsArray);
        $this->set('documents', $documents);

        $this->Navigation->SetSessionForPaging();

        // Infinite scroll
        if (Configure::check('InfiniteScroll') && Configure::read('InfiniteScroll') == true) {
            $this->Navigation->InfiniteScrollPaging('overview_infinite');
        }
    }

    public function summary()
    {
        $this->layout = 'blank';
        $certificates = array();

        $arrayData = $this->Xml->DatafromXml('DocumentCertificate', 'file', null);

        //das muss noch geändert werden, die Daten müssen aus der Datenbank kommen
        $certificat_types = $arrayData['settings']->DocumentCertificate->certificat->select->static->value;
        $summary = array();

        $summary = array(
                        'futurenow' => array(),
                        'future' => array(),
                        'errors' => array(),
                        'warnings' => array(),
                        'hints' => array(),
                        'deactive' => array()
                    );

        foreach ($summary as $_key => $_summary) {
            if (count($_summary) == 0) {
                continue;
            }

            foreach ($certificat_types as $_certificat_types) {
                $summary[$_key][trim($_certificat_types)] = array();
            }
        }

        $certificat_types = array();
        $models = array('top'=>'Document','main'=>'DocumentCertificate','sub'=>'DocumentCertificateData');


        $certificates_options = array(
                                    'order' => array('certificat'),
                                    'conditions' => array(
                                                    'DocumentCertificate.testingcomp_id' => $this->Autorisierung->ConditionsTestinccomps(),
                                                    'DocumentCertificate.active' => 1,
                                                    'DocumentCertificate.deleted' => 0,
                                                )
                                            );


        $certificate = $this->Document->DocumentCertificate->find('all', $certificates_options);
        $this->Document->DocumentCertificate->DocumentCertificateData->recursive = -1;
        $this->Document->recursive = 1;
        foreach ($certificate as $_key => $_certificate) {
            $certificates_options = array(
                                    'order' => array('id desc'),
                                    'conditions' => array(
                                                    'DocumentCertificateData.testingcomp_id' => $this->Autorisierung->ConditionsTestinccomps(),
                                                    'DocumentCertificateData.document_certificate_id' => $_certificate['DocumentCertificate']['id'],
                                                    'DocumentCertificateData.active' => 1,
                                                    'DocumentCertificateData.deleted' => 0,
                                                )
                                            );

            $testingmethod_options = array(
                                    'conditions' => array(
                                                    'Document.testingcomp_id' => $this->Autorisierung->ConditionsTestinccomps(),
                                                    'Document.id' => $_certificate['Document']['id'],
                                                )
                                            );

            $DocumentCertificateData = $this->Document->DocumentCertificate->DocumentCertificateData->find('first', $certificates_options);
            $testingmethod = $this->Document->find('first', $testingmethod_options);

            if (isset($testingmethod['DocumentTestingmethod'][0])) {
                $certificate[$_key]['DocumentTestingmethod'] = $testingmethod['DocumentTestingmethod'][0];
            } else {
                $certificate[$_key]['DocumentTestingmethod'] = array();
            }

            $certificate[$_key]['DocumentCertificateData'] = $DocumentCertificateData['DocumentCertificateData'];
            $certificate[$_key] = $this->Qualification->DocumentCertification($certificate[$_key], $models);

            $certificat_types[$certificate[$_key]['DocumentCertificate']['certificat']][$certificate[$_key]['Document']['id']][$certificate[$_key]['DocumentCertificate']['id']] = $certificate[$_key];
            $summary = $this->Qualification->DocumentCertificationSummary($summary, $certificate[$_key], $models);
        }

        $summary_desc = array(
                    'futurenow' => array(
                            0 =>__('First-time certification reached', true),
                            1 =>__('First-time certifications reached', true),
                            ),

                    'future' => array(
                            0 =>__('First-time certification', true),
                            1 =>__('First-time certifications', true),
                            ),
                    'errors' => array(
                            0 =>__('Irregularity', true),
                            1 =>__('Irregularities', true),
                            ),
                    'warnings' => array(
                            0 => __('Warning', true),
                            1 => __('Warnings', true)
                            ),
                    'hints' => array(
                            0 => __('Hint', true),
                            1 => __('Hints', true)
                            ),
                    'deactive' => array(
                            0 => __('Deactive', true),
                            1 => __('Deactive', true)
                            )
                        );

        $this->set('summary', $summary);
        $this->set('summary_desc', $summary_desc);
    }

    public function view()
    {
        $this->__view();
    }

    protected function __view()
    {
        if ($this->request->projectvars['VarsArray'][16] == 0 && isset($this->request->data['document_id']) && $this->request->data['document_id'] > 0) {
            $this->request->projectvars['VarsArray'][16] = $this->request->data['document_id'];
            $document_id =  $this->request->data['document_id'];
        }

        if ($this->request->projectvars['VarsArray'][16] > 0) {
            $document_id = $this->request->projectvars['VarsArray'][16];
        } else {
            throw new NotFoundException(__('Invalid value 2'));
        }

        $options = array(
                        'conditions' => array(
                                            'Document.deleted' => 0,
                                            'Document.id' => $document_id,
                                            'Document.testingcomp_id' => $this->Autorisierung->ConditionsTestinccomps()
                                        )
                        );

        $document = $this->Document->find('first', $options);



        $idtestmethod = array();
        $idtestmethod = $this->Data->BelongsToManySelected($document, 'Document', 'DocumentTestingmethod', array('TestingmethodsDocuments','testingmethod_id','document_id'));
        $idtestmethod = $idtestmethod ['DocumentTestingmethod']['selected'];
        $testingmethod = $this->Document->DocumentTestingmethod->find('all', array('conditions' =>array('DocumentTestingmethod.id' =>$idtestmethod )));

        $document['DocumentTestingmethod'][0] = $testingmethod[0] ['DocumentTestingmethod'];
        if ($this->request->projectvars['VarsArray'][15] == 0) {
            $this->request->projectvars['VarsArray'][15] = $document['DocumentTestingmethod'][0]['id'];
        }

        //		$testingmethod_id = $this->request->projectvars['VarsArray'][15];
        $testingmethod_id = $document['DocumentTestingmethod'][0]['id'];

        $path = Configure::read('document_folder') . $testingmethod_id . DS . $document_id. DS;

        if (!empty($document['Document']['certified_file']) && file_exists($path .$document['Document']['certified_file'])) {
            $document['Document']['certified_file_valide'] = 1;
        }

        $this->request->data = $document;

        $options = array(
                        'conditions' => array(
                                            'DocumentCertificate.deleted' => 0,
                                            'DocumentCertificate.document_id' => $document_id,
                                            'DocumentCertificate.testingcomp_id' => $this->Autorisierung->ConditionsTestinccomps()
                                        )
                        );

        $this->Document->DocumentCertificate->recursive = -1;
        $DocumentCertificate = $this->Document->DocumentCertificate->find('first', $options);

        if (count($DocumentCertificate) == 1) {
            $document['DocumentCertificate'] =
            $DocumentCertificate['DocumentCertificate'];
        }

        $arrayData = $this->Xml->DatafromXml('DocumentEdit', 'file', null);

        $this->request->data = $this->Data->GeneralDropdownData(array('Document'), $arrayData, $this->request->data);

        $this->Document->DocumentTestingmethod->recursive = -1;
        $testingmethod = $this->Document->DocumentTestingmethod->find('first', array('conditions' => array('DocumentTestingmethod.id' => $testingmethod_id)));

        if (count($testingmethod) == 0) {
            $this->Session->setFlash(__('Das Gerät ist keiner Kategorie zugeordnet.'));
            return;
        }

        $SettingsArray = array();

        if (Configure::read('CertifcateManager') == true) {
            $SettingsArray['examinerlink'] = array('discription' => __('Examiners administration', true), 'controller' => 'examiners','action' => 'index', 'terms' => null);
        }

        if (Configure::read('DeviceManager') == true) {
            $SettingsArray['devicelink'] = array('discription' => __('Devices administration', true), 'controller' => 'devices','action' => 'index', 'terms' => null);
        }

        $this->request->projectvars['VarsArray'][16] = $document_id;

        $breads = $this->Navigation->Breads(null);

        $breads[1]['discription'] = __('Documents', true);
        $breads[1]['controller'] = 'documents';
        $breads[1]['action'] = 'index';
        $breads[1]['pass'] = null;

        $breads[2]['discription'] = $testingmethod['DocumentTestingmethod']['verfahren'];
        $breads[2]['controller'] = 'documents';
        $breads[2]['action'] = 'overview';
        $breads[2]['pass'] = $this->request->projectvars['VarsArray'];

        $breads[3]['discription'] = $document['Document']['document_type'] . '/' . $document['Document']['name'];
        $breads[3]['controller'] = 'documents';
        $breads[3]['action'] = 'view';
        $breads[3]['pass'] = $this->request->projectvars['VarsArray'];

        $this->set('breads', $breads);
        $this->set('SettingsArray', $SettingsArray);
        $this->set('document', $document);
        $this->set('testingmethod', $testingmethod);
        $this->set('arrayData', $arrayData);
        $this->render('view');
    }

    public function files()
    {
        $this->layout = 'modal';

        $models['top'] = 'Document';
        $models['main'] = null;
        $models['sub'] = null;
        $models['file'] = 'Documentfile';
        $certificatefile_id = 0;
        $this->loadModel($models['file']);

        $testingmethod_id = $this->Sicherheit->Numeric($this->request->params['pass']['15']);
        $document_id = $this->Sicherheit->Numeric($this->request->params['pass']['16']);

        if (!$this->Document->exists($document_id)) {
            throw new NotFoundException(__('Invalid document'));
        }


        $path = Configure::read('document_folder') . $document_id. DS . 'documents' . DS;
        $this->__files($path, 0, $document_id, $certificatefile_id, $models);

        $SettingsArray = array();
        //		$SettingsArray['documentlink'] = array('discription' => __('Back to overview',true), 'controller' => 'documents','action' => 'index', 'terms' => null);
        //		$SettingsArray['backlink'] = array('discription' => __('Back',true), 'controller' => 'documents','action' => 'view', 'terms' => $this->request->projectvars['VarsArray']);

        $this->set('SettingsArray', $SettingsArray);
    }

    protected function __files($path, $examiner_id, $certificate_id, $certificatefiles_id, $models)
    {
        $outputarray = $this->Qualification->DokuFiles($path, $examiner_id, $certificate_id, $certificatefiles_id, $models);

        $this->set('_data', $outputarray['_data']);
        $this->set('_files', $outputarray['_files']);
        $this->set('models', $outputarray['models']);
    }

    public function monitorings()
    {
        if ($this->request->projectvars['VarsArray'][15] > 0) {
            $testingmethod_id = $this->request->projectvars['VarsArray'][15];
        } else {
            throw new NotFoundException(__('Invalid value 1'));
        }
        if ($this->request->projectvars['VarsArray'][16] > 0) {
            $document_id = $this->request->projectvars['VarsArray'][16];
        } else {
            throw new NotFoundException(__('Invalid value 2'));
        }

        $arrayData = $this->Xml->DatafromXml('Document', 'file', null);

        $options = array(
                        'conditions' => array(
                                            'Document.id' => $document_id,
                                            'Document.testingcomp_id' => $this->Autorisierung->ConditionsTestinccomps(),
                                            'Document.deleted' => 0
                                        )
                        );

        $model = array('top' => 'Document','main' => 'DocumentCertificate','sub' => 'DocumentCertificateData');
        $document = $this->Document->find('first', $options);
        $this->request->data = $document;

        $this->request->data = $this->Data->GeneralDropdownData(array('Document'), $arrayData, $this->request->data);

        $testingmethodes = $document['DocumentTestingmethod'];

        $options = array(
                        'order' => array('DocumentCertificate.certificat ASC'),
                        'conditions' => array(
                                            'DocumentCertificate.document_id' => $document_id,
                                            'DocumentCertificate.testingcomp_id' => $this->Autorisierung->ConditionsTestinccomps(),
                                            'DocumentCertificate.deleted' => 0
                                        )
                        );

        $document = $this->Document->DocumentCertificate->find('all', $options);

        $this->Document->DocumentTestingmethod->recursive = -1;
        $this->Document->DocumentCertificate->DocumentCertificateData->recursive = -1;
        $testingmethod = $this->Document->DocumentTestingmethod->find('first', array('conditions' => array('DocumentTestingmethod.id' => $testingmethod_id)));

        $options = array(
                        'order' => array('DocumentCertificateData.document_id ASC'),
                        'conditions' => array(
                                            'DocumentCertificateData.document_id' => $document_id,
                                            'DocumentCertificateData.testingcomp_id' => $this->Autorisierung->ConditionsTestinccomps(),
                                            'DocumentCertificateData.deleted' => 0
                                        )
                        );
        $option['order'] = 'DocumentCertificateData.id ASC';
        $option['conditions']['DocumentCertificateData.testingcomp_id'] = $this->Autorisierung->ConditionsTestinccomps();

        $testingmethods = $this->Document->find('first', array('conditions'=>array('Document.id' => $document_id,'Document.testingcomp_id' => $this->Autorisierung->ConditionsTestinccomps())));

        if (count($document) > 0) {
            foreach ($document as $_key => $_document) {
                $option['conditions']['DocumentCertificateData.document_certificate_id'] = $_document['DocumentCertificate']['id'];
                //				$option['conditions']['DocumentCertificateData.active'] = 1;
                $option['conditions']['DocumentCertificateData.deleted'] = 0;
                $option['order'] = array('DocumentCertificateData.id DESC');
                $DocumentCertificateData = $this->Document->DocumentCertificate->DocumentCertificateData->find('first', $option);

                $document[$_key] = array_merge($document[$_key], $testingmethod);
                $document[$_key]['DocumentCertificateData'] = array_merge($document[$_key]['DocumentCertificateData'], $DocumentCertificateData['DocumentCertificateData']);

                $document[$_key]['Testingmethods'] = $testingmethods['DocumentTestingmethod'];

                $document[$_key] = $this->Qualification->DocumentCertification($document[$_key], $model);
            }
        }


        $SettingsArray = array();
        //		$SettingsArray['documentlink'] = array('discription' => __('Back to start',true), 'controller' => 'documents','action' => 'index', 'terms' => null);
        //		$SettingsArray['backlink'] = array('discription' => __('Back to document',true) . ' ' . $document[0]['Document']['name'], 'controller' => 'documents','action' => 'view', 'terms' => $this->request->projectvars['VarsArray']);
        //		$SettingsArray['addlink'] = array('discription' => __('Add monitoring',true), 'controller' => 'documents','action' => 'addmonitoring', 'terms' => $this->request->projectvars['VarsArray']);
        if (Configure::read('CertifcateManager') == true) {
            $SettingsArray['examinerlink'] = array('discription' => __('Examiners administration', true), 'controller' => 'examiners','action' => 'index', 'terms' => null);
        }

        if (Configure::read('DocumentManager') == true) {
            //			$SettingsArray['documentlink'] = array('discription' => __('Documents administration',true), 'controller' => 'documents','action' => 'index', 'terms' => null);
        }

        $breads = $this->Navigation->Breads(null);

        $breads[1]['discription'] = __('Documents', true);
        $breads[1]['controller'] = 'documents';
        $breads[1]['action'] = 'index';
        $breads[1]['pass'] = null;

        $breads[2]['discription'] = $testingmethod['DocumentTestingmethod']['verfahren'];
        $breads[2]['controller'] = 'documents';
        $breads[2]['action'] = 'overview';
        $breads[2]['pass'] = $this->request->projectvars['VarsArray'];

        $breads[3]['discription'] = $document[0]['Document']['document_type'] . '/' . $document[0]['Document']['name'] . ' (' . $document[0]['Document']['registration_no'] . ')';
        $breads[3]['controller'] = 'documents';
        $breads[3]['action'] = 'view';
        $breads[3]['pass'] = $this->request->projectvars['VarsArray'];

        $breads[4]['discription'] = __('Monitorings', true);
        $breads[4]['controller'] = 'documents';
        $breads[4]['action'] = 'monitorings';
        $breads[4]['pass'] = $this->request->projectvars['VarsArray'];

        $this->set('breads', $breads);

        $this->set('arrayData', $arrayData);
        $this->set('SettingsArray', $SettingsArray);
        $this->set('document', $document);
        $this->set('testingmethod', $testingmethod);
    }

    public function monitoring()
    {
        $this->layout = 'modal';

        $testingmethod_id = $this->Sicherheit->Numeric($this->request->params['pass']['15']);
        $document_id = $this->Sicherheit->Numeric($this->request->params['pass']['16']);
        $certificate_id = $this->Sicherheit->Numeric($this->request->params['pass']['17']);
        $certificate_data_id = $this->Sicherheit->Numeric($this->request->params['pass']['18']);
        $edit_monitoring = false;

        if (isset($this->request->data['edit_monitoring']) && $this->request->data['edit_monitoring'] == true) {
            $edit_monitoring = true;
        }

        $certificate_data_option = array(
                                    'order' => array('DocumentCertificateData.id DESC'),
                                    'conditions' => array(
                                        'DocumentCertificateData.id' => $certificate_data_id,
                                        'DocumentCertificateData.deleted' => 0,
                                        )
                                    );

        $certificate_data = $this->Document->DocumentCertificate->DocumentCertificateData->find('first', $certificate_data_option);

        $this->request->data = $certificate_data;

        if ($edit_monitoring == true) {
            $this->request->data['edit_monitoring'] = true;
        }
        if ($edit_monitoring == false) {
            $this->request->data['edit_monitoring'] = false;
        }

        $model = array('top' => 'Document','main' => 'DocumentCertificate','sub' => 'DocumentCertificateData');

        $certificate_data = $this->Qualification->DocumentCertification($certificate_data, $model);


        $arrayData = $this->Xml->DatafromXml('DocumentCertificateEdit', 'file', null);
        $this->request->data = $this->Data->GeneralDropdownData(array('Document'), $arrayData, $this->request->data);

        $SettingsArray = array();
        //		$SettingsArray['documentlink'] = array('discription' => __('Back to list',true), 'controller' => 'documents','action' => 'index', 'terms' => null);
        //		$SettingsArray['backlink'] = array('discription' => __('Back to monitorings of document',true) . ' ' . $certificate_data['Document']['name'], 'controller' => 'documents','action' => 'monitorings', 'terms' => $this->request->projectvars['VarsArray']);
        //		$SettingsArray['addlink'] = array('discription' => __('Add monitoring',true), 'controller' => 'documents','action' => 'addmonitoring', 'terms' => $this->request->projectvars['VarsArray']);

        $this->request->projectvars['VarsArray'][17] = $certificate_id;
        $this->request->projectvars['VarsArray'][18] = $certificate_data_id;

        $this->set('SettingsArray', $SettingsArray);
        $this->set('arrayData', $arrayData);
        $this->set('certificate_data', $certificate_data);
        $this->set('settings', $arrayData['headoutput']);
    }

    public function delmonitoring()
    {
        $this->layout = 'modal';

        $testingmethod_id = $this->Sicherheit->Numeric($this->request->params['pass']['15']);
        $document_id = $this->Sicherheit->Numeric($this->request->params['pass']['16']);
        $document_certifificate_id = $this->Sicherheit->Numeric($this->request->params['pass']['17']);
        $document_certifificate_data_id = $this->Sicherheit->Numeric($this->request->params['pass']['18']);

        $this->request->projectvars['VarsArray'][17] = $document_certifificate_id;
        $this->request->projectvars['VarsArray'][18] = $document_certifificate_data_id;

        // Überprüfung ob Gerät, Benutzer, Firma zusammenpassen

        if (!$this->Document->exists($document_id)) {
            throw new NotFoundException(__('Invalid id'));
        }

        if (!$this->Document->DocumentCertificate->DocumentCertificateData->exists($document_certifificate_data_id)) {
            throw new NotFoundException(__('Invalid id'));
        }

        $certificate_data_option = array(
                                    'order' => array('DocumentCertificateData.id DESC'),
                                    'conditions' => array(
                                        'DocumentCertificateData.id' => $document_certifificate_data_id,
                                        'DocumentCertificateData.deleted' => 0,
                                        )
                                    );

        $certificate_data = $this->Document->DocumentCertificate->DocumentCertificateData->find('first', $certificate_data_option);


        if (isset($this->request->data['DocumentCertificate']) && ($this->request->is('post') || $this->request->is('put'))) {
            if ($document_certifificate_id != $this->request->data['DocumentCertificate']['id']) {
                $this->Flash->error(__('The monitoring infos could not be deleted.'), array('key' => 'error'));

                return;
            }

            unset($this->request->data['Order']);
            $this->request->data['DocumentCertificate']['id'] = $document_certifificate_id;
            $this->request->data['DocumentCertificate']['user_id'] = $this->Auth->user('id');
            $this->request->data['DocumentCertificate']['testingcomp_id'] = $this->Auth->user('testingcomp_id');
            $this->request->data['DocumentCertificate']['recertification_in_year'] = $certificate_data['DocumentCertificate']['recertification_in_year'];
            $this->request->data['DocumentCertificate']['horizon'] = $certificate_data['DocumentCertificate']['horizon'];
            $this->request->data['DocumentCertificate']['deleted'] = 1;

            if ($this->Document->DocumentCertificate->save($this->request->data)) {
                $this->Document->DocumentCertificate->DocumentCertificateData->updateAll(
                    array(
                        'DocumentCertificateData.deleted' => 1,
                        ),
                    array(
                        'DocumentCertificateData.document_certificate_id' => $document_certifificate_id,
                        )
                );

                $this->Flash->success(__('The document has been deleted'), array('key' => 'success'));

                $this->Autorisierung->Logger($document_certifificate_data_id, $this->request->data['DocumentCertificate']);

                unset($this->request->params['pass']['17']);
                unset($this->request->params['pass']['18']);

                unset($this->request->projectvars['VarsArray']['17']);
                unset($this->request->projectvars['VarsArray']['18']);
                /*
                                $this->request->params['action'] = 'monitorings';

                                $request_url = explode('/',$this->request->url);
                                $request_here = explode('/',$this->request->here);

                                $request_url[1] = 'monitoring';
                                $request_here[3] = 'monitoring';

                                $this->request->url = implode('/',$request_url);
                                $this->request->here = implode('/',$request_here);
                */
                $FormName['controller'] = 'documents';
                $FormName['action'] = 'view';
                $FormName['terms'] = implode('/', $this->request->projectvars['VarsArray']);

                $this->Flash->warning(__('The monitoring infos has been be deleted. Please, try again.'), array('key' => 'warning'));


                $this->set('saveOK', 1);
                $this->set('FormName', $FormName);
            } else {
                $this->Flash->warning(__('The monitoring infos could not be deleted. Please, try again.'), array('key' => 'warning'));
            }
        }

        $certificate_data_option = array(
                                    'order' => array('DocumentCertificateData.id DESC'),
                                    'conditions' => array(
                                        'DocumentCertificateData.id' => $document_certifificate_data_id,
                                        'DocumentCertificateData.deleted' => 0,
                                        )
                                    );

        $certificate_data = $this->Document->DocumentCertificate->DocumentCertificateData->find('first', $certificate_data_option);

        $this->request->data = $certificate_data;

        $arrayData = $this->Xml->DatafromXml('DocumentCertificateEdit', 'file', null);

        $model = array('top' => 'Document','main' => 'DocumentCertificate','sub' => 'DocumentCertificateData');
        $this->Qualification->SingleCertificateSummary($certificate_data, $model);

        $this->set('certificate_data', $certificate_data);
        $this->set('settings', $arrayData['headoutput']);

        $this->Flash->warning(__('Are you sure you want to delete this monitoring?'), array('key' => 'warning'));
    }

    public function addmonitoring()
    {
        $this->layout = 'modal';

        if ($this->request->projectvars['VarsArray'][16] > 0) {
            $document_id = $this->request->projectvars['VarsArray'][16];
        } else {
            throw new NotFoundException(__('Invalid value 2'));
        }

        $options = array(
                        'conditions' => array(
                                            'Document.deleted' => 0,
                                            'Document.id' => $document_id,
                                            'Document.testingcomp_id' => $this->Autorisierung->ConditionsTestinccomps()
                                        )
                        );

        $arrayData = $this->Xml->DatafromXml('DocumentCertificate', 'file', null);


        if (isset($this->request->data['DocumentCertificate']) && ($this->request->is('post') || $this->request->is('put'))) {
            $this->request->data['DocumentCertificate']['document_id'] = $document_id;
            $this->request->data['DocumentCertificate']['testingcomp_id'] = $this->Auth->user('testingcomp_id');
            $this->request->data['DocumentCertificate']['user_id'] = $this->Auth->user('id');

            // muss noch im Helper gefixt werden
            //			$this->request->data['DocumentCertificate']['certificat'] = $this->request->data['DocumentCertificate'][0]['certificat'];
            //			unset($this->request->data['DocumentCertificate'][0]);

            $this->request->data = $this->Data->GeneralDropdownData(array('DocumentCertificate'), $arrayData, $this->request->data);
            $this->request->data = $this->Data->ChangeDropdownData($this->request->data, $arrayData, array('DocumentCertificate'));

            // Filter einfügen, keine doppelte Überwachungen zulassen
            // z.B. E-Checks für ein Gerät
            $DocumentCertificateExist = $this->Document->DocumentCertificate->find(
                'first',
                array(
                                                        'conditions'=>array(
                                                            'DocumentCertificate.deleted' => 0,
                                                            'DocumentCertificate.document_id' => $document_id,
                                                            'DocumentCertificate.certificat' => $this->request->data['DocumentCertificate']['certificat'],

                                                            )
                                                        )
            );
            if (count($DocumentCertificateExist) > 0) {
                $this->Flash->warning(__('The monitoring infos could not be saved. The following monitoring exists for this document').': '.$this->request->data['DocumentCertificate']['certificat'], array('key' => 'warning'));
            }

            if (count($DocumentCertificateExist) == 0) {
                $this->Document->DocumentCertificate->create();

                if ($this->Document->DocumentCertificate->save($this->request->data['DocumentCertificate'])) {
                    $this->request->data['DocumentCertificateData']['user_id'] = $this->Auth->user('id');
                    $this->request->data['DocumentCertificateData']['testingcomp_id'] = $this->Auth->user('testingcomp_id');
                    $this->request->data['DocumentCertificateData']['document_id'] = $document_id;
                    $this->request->data['DocumentCertificateData']['document_certificate_id'] = $this->Document->DocumentCertificate->getLastInsertId();
                    $this->request->data['DocumentCertificateData']['testingcomp_id'] = $this->Auth->user('testingcomp_id');
                    $this->request->data['DocumentCertificateData']['first_certification'] = 0;
                    $this->request->data['DocumentCertificateData']['certified'] = 1;
                    $this->request->data['DocumentCertificateData']['renewal_in_year'] = 0;
                    $this->request->data['DocumentCertificateData']['recertification_in_year'] = $this->request->data['DocumentCertificate']['recertification_in_year'];
                    $this->request->data['DocumentCertificateData']['horizon'] = $this->request->data['DocumentCertificate']['horizon'];
                    $this->request->data['DocumentCertificateData']['first_registration'] = $this->request->data['DocumentCertificate']['first_registration'];
                    $this->request->data['DocumentCertificateData']['certified_date'] = $this->request->data['DocumentCertificate']['first_registration'];
                    $this->request->data['DocumentCertificateData']['apply_for_recertification'] = 0;
                    $this->request->data['DocumentCertificateData']['deleted'] = 0;
                    $this->request->data['DocumentCertificateData']['active'] = 1;

                    $this->Document->DocumentCertificate->DocumentCertificateData->create();

                    if ($this->Document->DocumentCertificate->DocumentCertificateData->save($this->request->data['DocumentCertificateData'])) {
                        $this->Flash->success(__('The monitoring infos has been saved.'), array('key' => 'success'));


                        $this->request->params['pass']['17'] = $this->Document->DocumentCertificate->getLastInsertId();
                        $this->request->params['pass']['18'] = $this->Document->DocumentCertificate->DocumentCertificateData->getLastInsertId();

                        $this->request->params['action'] = 'monitoring';
                        $request_url = explode('/', $this->request->url);
                        $request_here = explode('/', $this->request->here);
                        $request_url[1] = 'monitoring';
                        $request_here[3] = 'monitoring';
                        $this->request->url = implode('/', $request_url);
                        $this->request->here = implode('/', $request_here);

                        $FormName['controller'] = 'documents';
                        $FormName['action'] = 'monitorings';
                        $FormName['terms'] = implode('/', $this->request->projectvars['VarsArray']);

                        $this->set('saveOK', 1);
                        $this->set('FormName', $FormName);
                    } else {
                        $this->Document->DocumentCertificate->delete($this->Document->DocumentCertificate->getLastInsertId());
                        $this->Flash->warning(__('The monitoring infos could not be saved. Please, try again.'), array('key' => 'warning'));
                    }
                } else {
                    $this->Flash->warning(__('The monitoring infos could not be saved. Please, try again.'), array('key' => 'warning'));
                }
            }
        }

        $document = $this->Document->find('first', $options);

        $SettingsArray = array();
        //		$SettingsArray['backlink'] = array('discription' => __('Back to overview',true), 'controller' => 'documents','action' => 'monitorings', 'terms' => $this->request->projectvars['VarsArray']);

        foreach ($arrayData['settings']->Document->children() as $_key => $_settings) {
            $this->request->data[trim($_settings->model)][trim($_settings->key)] = null;
        }

        $this->request->data = $this->Data->GeneralDropdownData(array('DocumentCertificate'), $arrayData, $this->request->data);
        $this->request->data['DocumentCertificate']['file'] = 0;
        $this->request->data['DocumentCertificate']['active'] = 1;

        $this->set('arrayData', $arrayData);
        $this->set('document', $document);
        $this->set('SettingsArray', $SettingsArray);
        $this->set('settings', $arrayData['orderoutput']);
    }

    public function editmonitoring()
    {
        $this->layout = 'modal';

        $testingmethod_id = $this->Sicherheit->Numeric($this->request->params['pass']['15']);
        $document_id = $this->Sicherheit->Numeric($this->request->params['pass']['16']);
        $document_certifificate_id = $this->Sicherheit->Numeric($this->request->params['pass']['17']);
        $document_certifificate_data_id = $this->Sicherheit->Numeric($this->request->params['pass']['18']);

        $this->request->projectvars['VarsArray'][17] = $document_certifificate_id;
        $this->request->projectvars['VarsArray'][18] = $document_certifificate_data_id;

        // Überprüfung ob Gerät, Benutzer, Firma zusammenpassen

        if (!$this->Document->exists($document_id)) {
            throw new NotFoundException(__('Invalid id'));
        }

        if (!$this->Document->DocumentCertificate->DocumentCertificateData->exists($document_certifificate_data_id)) {
            throw new NotFoundException(__('Invalid id'));
        }


        if (isset($this->request->data['DocumentCertificateData']) && ($this->request->is('post') || $this->request->is('put'))) {
            unset($this->request->data['Order']);
            $this->request->data['DocumentCertificate']['id'] = $document_certifificate_id;
            $this->request->data['DocumentCertificate']['user_id'] = $this->Auth->user('id');

            $this->request->data['DocumentCertificateData']['id'] = $document_certifificate_data_id;
            $this->request->data['DocumentCertificateData']['user_id'] = $this->Auth->user('id');

            if ($this->Document->DocumentCertificate->DocumentCertificateData->save($this->request->data['DocumentCertificateData'])) {
                $this->Document->DocumentCertificate->save($this->request->data['DocumentCertificate']);
                $this->Flash->success(__('The monitoring infos has been saved'), array('key' => 'success'));

                $this->Autorisierung->Logger($document_certifificate_data_id, $this->request->data['DocumentCertificateData']);
            } else {
                $this->Flash->warning(__('The monitoring infos could not be saved. Please, try again.'), array('key' => 'warning'));
            }

            $FormName['controller'] = 'documents';
            $FormName['action'] = 'monitorings';
            $FormName['terms'] = implode('/', $this->request->projectvars['VarsArray']);

            $this->set('FormName', $FormName);
        }

        $certificate_data_option = array(
                                    'order' => array('DocumentCertificateData.id DESC'),
                                    'conditions' => array(
                                        'DocumentCertificateData.id' => $document_certifificate_data_id,
                                        'DocumentCertificateData.deleted' => 0,
                                        )
                                    );

        $certificate_data = $this->Document->DocumentCertificate->DocumentCertificateData->find('first', $certificate_data_option);

        if (isset($this->request->data['back']) && $this->request->data['back'] == 1) {
            $certificateinfo = 1;
        }

        $this->request->data = $certificate_data;

        $arrayData = $this->Xml->DatafromXml('DocumentCertificateEdit', 'file', null);

        $model = array('top' => 'Document','main' => 'DocumentCertificate','sub' => 'DocumentCertificateData');
        $this->Qualification->SingleCertificateSummary($certificate_data, $model);

        $SettingsArray = array();

        $SettingsArray['backlink'] = array('discription' => __('Back', true) . ' ' . $certificate_data['Document']['name'], 'controller' => 'documents','action' => 'monitoring', 'terms' => $this->request->projectvars['VarsArray']);

        $this->set('SettingsArray', $SettingsArray);
        $this->set('certificate_data', $certificate_data);
        $this->set('settings', $arrayData['headoutput']);

        if (isset($certificateinfo) && $certificateinfo == 1) {
            //			$this->set('certificate_data', $certificate_data);
            //			$this->render('certificateinfo','blank');
        }
    }

    public function replacemonitoring()
    {
        $this->layout = 'modal';

        $testingmethod_id = $this->Sicherheit->Numeric($this->request->params['pass'][15]);
        $document_id = $this->Sicherheit->Numeric($this->request->params['pass'][16]);
        $document_certifificate_id = $this->Sicherheit->Numeric($this->request->params['pass'][17]);
        $document_certifificate_data_id = $this->Sicherheit->Numeric($this->request->params['pass'][18]);

        $this->request->projectvars['VarsArray'][17] = $document_certifificate_id;
        $this->request->projectvars['VarsArray'][18] = $document_certifificate_data_id;

        if (!$this->Document->exists($document_id)) {
            throw new NotFoundException(__('Invalid id 1'));
        }

        if (!$this->Document->DocumentCertificate->exists($document_certifificate_id)) {
            throw new NotFoundException(__('Invalid id 2'));
        }

        if (!$this->Document->DocumentCertificate->DocumentCertificateData->exists($document_certifificate_data_id)) {
            throw new NotFoundException(__('Invalid id 3'));
        }

        $certificate_data_option = array(
                                    'order' => array('DocumentCertificateData.id DESC'),
                                    'conditions' => array(
                                        'DocumentCertificateData.id' => $document_certifificate_data_id,
                                        'DocumentCertificateData.deleted' => 0,
                                        )
                                    );

        $certificate_data = $this->Document->DocumentCertificate->DocumentCertificateData->find('first', $certificate_data_option);

        if (isset($this->request->data['DocumentCertificateData']) && ($this->request->is('post') || $this->request->is('put'))) {
            $this->Document->DocumentCertificate->DocumentCertificateData->updateAll(
                array(
                    'DocumentCertificateData.active' => 0,
                    ),
                array(
                    'DocumentCertificateData.document_certificate_id' => $certificate_data['DocumentCertificate']['id'],
                    'DocumentCertificateData.deleted' => 0,
                    )
            );

            $this->request->data['DocumentCertificateData']['document_id'] = $document_id;
            $this->request->data['DocumentCertificateData']['document_certificate_id'] = $document_certifificate_id;
            $this->request->data['DocumentCertificateData']['first_registration'] = $certificate_data['DocumentCertificateData']['first_registration'];
            $this->request->data['DocumentCertificateData']['testingcomp_id'] = $this->Auth->user('testingcomp_id');
            $this->request->data['DocumentCertificateData']['user_id'] = $this->Auth->user('id');

            $this->Document->DocumentCertificate->DocumentCertificateData->create();

            if ($this->Document->DocumentCertificate->DocumentCertificateData->save($this->request->data)) {
                $certificate_data_option = array(
                                    'order' => array('DocumentCertificateData.id DESC'),
                                    'conditions' => array(
                                        'DocumentCertificateData.id' => $this->Document->DocumentCertificate->DocumentCertificateData->getLastInsertId(),
                                        'DocumentCertificateData.deleted' => 0,
                                        )
                                    );

                $certificate_data = $this->Document->DocumentCertificate->DocumentCertificateData->find('first', $certificate_data_option);

                $this->Flash->success(__('The monitoring infos has been saved.'), array('key' => 'success'));


                $this->request->params['pass']['18'] = $this->Document->DocumentCertificate->DocumentCertificateData->getLastInsertId();

                $this->request->params['controller'] = 'monitoring';

                $FormName['controller'] = 'documents';
                $FormName['action'] = 'monitorings';
                $FormName['terms'] = implode('/', $this->request->projectvars['VarsArray']);

                $this->set('FormName', $FormName);

                return;
            } else {
                $this->Flash->warning(__('The monitoring infos could not be saved. Please, try again.'), array('key' => 'warning'));
            }
        }

        $arrayData = $this->Xml->DatafromXml('DocumentCertificateEdit', 'file', null);

        $this->request->data = $certificate_data;
        unset($this->request->data['DocumentCertificateData']['certified_date']);

        $model = array('top' => 'Document','main' => 'DocumentCertificate','sub' => 'DocumentCertificateData');
        $this->Qualification->SingleCertificateSummary($certificate_data, $model);

        $SettingsArray = array();

        $SettingsArray['backlink'] = array('discription' => __('Back', true) . ' ' . $certificate_data['Document']['name'], 'controller' => 'documents','action' => 'monitoring', 'terms' => $this->request->projectvars['VarsArray']);

        $this->set('SettingsArray', $SettingsArray);
        $this->set('settings', $arrayData['headoutput']);
    }
/*
    public function echecks() {

        if($this->request->projectvars['VarsArray'][15] > 0){
            $testingmethod_id = $this->request->projectvars['VarsArray'][15];
        }
        else {
            throw new NotFoundException(__('Invalid value 1'));
        }
        if($this->request->projectvars['VarsArray'][16] > 0){
            $document_id = $this->request->projectvars['VarsArray'][16];
        }
        else {
            throw new NotFoundException(__('Invalid value 2'));
        }

        $options = array(
                        'conditions' => array(
                                            'Document.id' => $document_id,
                                            'Document.testingmethod_id' => $testingmethod_id,
                                            'Document.testingcomp_id' => $this->Autorisierung->ConditionsTestinccomps()
                                        )
                        );

        $document = $this->Document->find('first',$options);

        $SettingsArray = array();
        $SettingsArray['backlink'] = array('discription' => __('back',true), 'controller' => 'documents','action' => 'view', 'terms' => $this->request->projectvars['VarsArray']);


        $this->set('SettingsArray', $SettingsArray);
        $this->set('document', $document);
    }
*/
    public function add()
    {
        $this->layout = 'modal';
        $locale = $this->Lang->Discription();

        $arrayData['settings'] = $this->Xml->CollectXMLFromFile('DocumentAdd');

        if (isset($this->request->data['Document']) && ($this->request->is('post') || $this->request->is('put'))) {
            $this->Document->set($this->request->data);
            $errors = array();

            if ($this->Document->validates()) {
            } else {
                $errors = $this->Document->validationErrors;
            }

            $this->Document->create();

            $this->request->data['Document']['user_id'] = $this->Auth->user('id');

            if (empty($this->request->data['DocumentTestingmethod']['DocumentTestingmethod'])) {
                $this->Flash->warning(__('Das Gerät konnte nicht gespeichert werden, wählen Sie eine Kategorie'), array('key' => 'warning'));
            }

            $this->request->data = $this->Data->ChangeDropdownData($this->request->data, $arrayData, 'Document');
            $this->request->data['Document']['testingcomp_id'] = $this->Auth->user('testingcomp_id');
            if (isset($this->request->data['DocumentManagerSearchAutocomplete'])) {
                unset($this->request->data['DocumentManagerSearchAutocomplete']);
            }

            if ($this->Document->save($this->request->data) && !empty($this->request->data['DocumentTestingmethod']['DocumentTestingmethod'])) {
                $this->Flash->success(__('The document has been saved'), array('key' => 'success'));
                $this->Autorisierung->Logger($this->Document->getLastInsertID(), $this->request->data['Document']);
                $last_document_id = $this->Document->getLastInsertID();

                $document = $this->Document->find('first', array('conditions' => array('Document.id' => $last_document_id)));
                pr($document);
                $this->request->projectvars['VarsArray'][15] = $this->request->data['DocumentTestingmethod']['DocumentTestingmethod']['id'];
                $this->request->projectvars['VarsArray'][16] = $document['Document']['id'];

                $FormName['controller'] = 'documents';
                $FormName['action'] = 'view';
                $FormName['terms'] = implode('/', $this->request->projectvars['VarsArray']);

                $this->set('FormName', $FormName);
                $this->set('saveOK', 1);
            } else {
                $this->Flash->warning(__('Das Gerät konnte nicht gespeichert werden, wählen Sie eine Kategorie'), array('key' => 'warning'));
            }
        }

        //$this->Document->Dropdown->recursive = -1;
        //  $device_dropdowns = $this->Device->Dropdown->find('list',array('fields'=>array($locale), 'order' =>'deu ASC'));

        $this->Document->DocumentTestingmethod->recursive = -1;
        $testingmethods = $this->Document->DocumentTestingmethod->find('list', array('fields' => array('id','verfahren')));

        $this->loadModel('Testingcomp');
        $this->Testingcomp->recursive = -1;
        $testingcomps = $this->Testingcomp->find(
            'list',
            array(
                                                'fields' => array('id','firmenname'),
                                                'conditions' => array(
                                                    'Testingcomp.id' => $this->Autorisierung->ConditionsTestinccomps()
                                                    )
                                                )
        );

        if (!isset($this->request->data['Document'])) {
            foreach ($arrayData['settings']->Document->children() as $_key => $_settings) {
                $this->request->data[trim($_settings->model)][trim($_settings->key)] = null;
            }
        }

        //		$this->Document->Examiner->recursive = -1;
        //		$Examiner = $this->Document->Examiner->find('list',array('fields'=>array('first_name','name','id'),'conditions'=>array('Examiner.testingcomp_id'=>$this->Autorisierung->ConditionsTestinccomps(),'Examiner.deleted' => 0)));

        //		$ExaminerOption = array();

        //		foreach($Examiner as $_key => $_examiner){
        //			$ExaminerOption[$_key] = $_examiner[key($_examiner)]. ' ' . key($_examiner);
        //		}

        $this->request->data = $this->Data->DropdownData(array('Document'), $arrayData, $this->request->data);
        $this->request->data['Document']['active'] = 1;

        $SettingsArray = array();
        //		$SettingsArray['backlink'] = array('discription' => __('back',true), 'controller' => 'documents','action' => 'index', 'terms' => null);

        $this->set('SettingsArray', $SettingsArray);
        $this->set('settings', $arrayData['settings']);
        $this->set('testingmethods', $testingmethods);
        $this->set('testingcomps', $testingcomps);
        //		$this->set('ExaminerOption', $ExaminerOption);
    }

    public function duplicate()
    {
        if ($this->request->projectvars['VarsArray'][16] > 0) {
            $document_id = $this->request->projectvars['VarsArray'][16];
        } else {
            throw new NotFoundException(__('Invalid value 2'));
        }

        if (isset($this->request->data['Document']) && ($this->request->is('post') || $this->request->is('put'))) {
            $row = $this->Document->findById($document_id);
            $_testingmethods = array();

            if (isset($row['DocumentTestingmethod']) && is_array($row['DocumentTestingmethod'])) {
                foreach ($row['DocumentTestingmethod'] as $_testingmethod) {
                    $_testingmethods['DocumentTestingmethod'][] = $_testingmethod['id'];
                }
            }

            unset($row['Document']['id']);
            unset($row['DocumentTestingmethod']);


            $row['Document']['name'] = __('Copy of', true) . ' ' . $row['Document']['name'];
            $row['Document']['user_id'] = $this->Auth->user('id');
            $row['DocumentTestingmethod'] = $_testingmethods;
            $this->Document->create();

            if ($this->Document->save($row)) {
                $this->Flash->success(__('The document has been saved'), array('key' => 'success'));

                $this->Autorisierung->Logger($this->Document->getLastInsertID(), $this->request->data['Document']);
                $document = $this->Document->find('first', array('conditions' => array('Document.id' => $this->Document->getLastInsertID())));

                $this->request->projectvars['VarsArray'][15] = $document['DocumentTestingmethod'][0]['id'];
                $this->request->projectvars['VarsArray'][16] = $document['Document']['id'];

                $this->__view();
            } else {
                $this->Flash->warning(__('The document could not be saved. Please, try again.'), array('key' => 'warning'));
            }
        }

        $options = array(
                        'conditions' => array(
                                            'Document.id' => $document_id,
                                            'Document.testingcomp_id' => $this->Auth->user('testingcomp_id')
                                        )
                        );

        $document = $this->Document->find('first', $options);

        $this->request->data = $document;

        $lastModalURLArray['controller'] = 'documents';
        $lastModalURLArray['action'] = 'edit';
        $lastModalURLArray['pass'] = $this->request->projectvars['VarsArray'];
        $lastModal = $this->Session->read('lastModalURLArray');

        if (is_array($lastModal) && count($lastModal) == 3) {
            $lastModalURLArray['controller'] = $this->Session->read('lastModalURLArray.controller');
            $lastModalURLArray['action'] = $this->Session->read('lastModalURLArray.action');
            $lastModalURLArray['pass'] = $this->Session->read('lastModalURLArray.pass');
        }

        $SettingsArray = array();
        $SettingsArray['backlink'] = array('discription' => __('back', true), 'controller' => $lastModalURLArray['controller'],'action' => $lastModalURLArray['action'], 'terms' => $lastModalURLArray['pass']);

        $this->set('SettingsArray', $SettingsArray);
        $this->set('document', $document);
    }

    public function edit()
    {
        $locale = $this->Lang->Discription();

        $this->layout = 'modal';

        $arrayData['settings'] = $this->Xml->CollectXMLFromFile('DocumentEdit');

        $document_testingmethod = $this->request->projectvars['VarsArray'][15];
        if ($this->request->projectvars['VarsArray'][16] > 0) {
            $document_id = $this->request->projectvars['VarsArray'][16];
        } else {
            throw new NotFoundException(__('Invalid value 2'));
        }

        if (isset($this->request->data['Document']) && ($this->request->is('post') || $this->request->is('put'))) {
            if (isset($this->request->data['DocumentManagerSearchAutocomplete'])) {
                unset($this->request->data['DocumentManagerSearchAutocomplete']);
            }

            $this->request->data['Document']['user_id'] = $this->Auth->user('id');

            if (empty($this->request->data['DocumentTestingmethod']['DocumentTestingmethod'])) {
                $this->Flash->warning(__('Das Gerät konnte nicht gespeichert werden, wählen Sie eine Kategorie'), array('key' => 'warning'));
            }

            $this->request->data = $this->Data->ChangeDropdownData($this->request->data, $arrayData, 'Document');

            if ($this->Document->save($this->request->data) && !empty($this->request->data['DocumentTestingmethod']['DocumentTestingmethod'])) {
                $this->Flash->success(__('The document has been saved'), array('key' => 'success'));

                $this->Autorisierung->Logger($document_id, $this->request->data['Document']);

                $document = $this->Document->find('first', array('conditions' => array('Document.id' => $document_id)));

                $this->request->projectvars['VarsArray'][15] = $document_testingmethod;

                $FormName['controller'] = 'documents';
                $FormName['action'] = 'view';
                $FormName['terms'] = implode('/', $this->request->projectvars['VarsArray']);

                $this->set('FormName', $FormName);
                $this->set('saveOK', 1);
            } else {
                $this->Flash->warning(__('Das Gerät konnte nicht gespeichert werden, wählen Sie eine Kategorie'), array('key' => 'warning'));
            }
        }

        $options = array(
                        'conditions' => array(
                                            'Document.id' => $document_id,
                                            'Document.testingcomp_id' => $this->Autorisierung->ConditionsTestinccomps()
                                        )
                        );
        //$this->Document->Dropdown->recursive = -1;
        //  $device_dropdowns = $this->Device->Dropdown->find('list',array('fields'=>array($locale), 'order' =>'deu ASC'));

        $testingmethods = $this->Document->DocumentTestingmethod->find('list', array('fields' => array('id','verfahren')));
        $document = $this->Document->find('first', $options);






        $this->request->data = $document;

        $this->request->data = $this->Data->GeneralDropdownData(array('Document'), $arrayData, $this->request->data);

        $SettingsArray = array();
        $SettingsArray['dellink'] = array('discription' => __('Delete this document', true), 'controller' => 'documents','action' => 'del', 'terms' => $this->request->projectvars['VarsArray']);
        //		$SettingsArray['backlink'] = array('discription' => __('back',true), 'controller' => 'documents','action' => 'view', 'terms' => $this->request->projectvars['VarsArray']);
        //		$SettingsArray['addlink'] = array('discription' => __('Add document',true), 'controller' => 'documents','action' => 'add', 'terms' => null);
        //		$SettingsArray['documentlink'] = array('discription' => __('back',true), 'controller' => 'documents','action' => 'index', 'terms' => null);
        //		$SettingsArray['filelink'] = array('discription' => __('Show or upload documents',true), 'controller' => 'documents','action' => 'files', 'terms' => $this->request->projectvars['VarsArray']);


        $this->set('SettingsArray', $SettingsArray);
        $this->set('document', $document);
        $this->set('settings', $arrayData['settings']);
        $this->set('testingmethods', $testingmethods);
    }

    public function del()
    {
        $this->layout = 'modal';

        if ($this->request->projectvars['VarsArray'][16] > 0) {
            $document_id = $this->request->projectvars['VarsArray'][16];
        } else {
            throw new NotFoundException(__('Invalid value 2'));
        }

        $options = array(
                        'conditions' => array(
                                'Document.id' => $document_id,
                                'Document.testingcomp_id' => $this->Autorisierung->ConditionsTestinccomps()
                            )
                        );

        $document = $this->Document->find('first', $options);
        $idtestmethod = array();
        $idtestmethod = $this->Data->BelongsToManySelected($document, 'Document', 'DocumentTestingmethod', array('TestingmethodsDocuments','testingmethod_id','document_id'));
        $idtestmethod = $idtestmethod ['DocumentTestingmethod']['selected'];
        $testingmethod = $this->Document->DocumentTestingmethod->find('all', array('conditions' =>array('DocumentTestingmethod.id' =>$idtestmethod )));

        $document['DocumentTestingmethod'][0] = $testingmethod[0] ['DocumentTestingmethod'];
        if (isset($this->request->data['Document']) && ($this->request->is('post') || $this->request->is('put'))) {
            if (isset($this->request->data['DocumentManagerSearchAutocomplete'])) {
                unset($this->request->data['DocumentManagerSearchAutocomplete']);
            }

            //			unset($document['DocumentCertificateData']);
            //			unset($document['DocumentTestingmethod']);
            //			unset($document['Document']['created']);
            //			unset($document['Document']['modified']);

            // vermeidet den Validationerror
            $document['DocumentTestingmethod']['DocumentTestingmethod'][] = $document['DocumentTestingmethod'];

            $document['Document']['deleted'] = 1;
            $document['Document']['active'] = "0";
            $document['Document']['user_id'] = $this->Auth->user('id');

            if ($this->Document->save($document['Document'])) {
                $this->Flash->success(__('The document has been deleted'), array('key' => 'success'));

                $this->Autorisierung->Logger($this->Document->getLastInsertID(), $document['Document']);

                $FormName['controller'] = 'documents';
                $FormName['action'] = 'overview';
                $FormName['terms'] = implode('/', $this->request->projectvars['VarsArray']);

                $this->set('FormName', $FormName);
                $this->set('saveOK', 1);
            } else {
                $this->Flash->warning(__('The document could not be deleted. Please, try again.'), array('key' => 'warning'));
            }
        }

        $this->request->data = $document;

        $SettingsArray = array();
        $SettingsArray['backlink'] = array('discription' => __('back', true), 'controller' => 'documents','action' => 'edit', 'terms' => $this->request->projectvars['VarsArray']);

        $this->set('SettingsArray', $SettingsArray);
        $this->set('document', $document);
    }

    public function documentfilesdescription()
    {
        $this->layout = 'modal';

        $models['top'] = 'Document';
        $models['main'] = null;
        $models['sub'] = null;
        $models['file'] = 'Documentsfile';

        $this->__filesdescription($models, 'files');

        $SettingsArray = array();
        //		$SettingsArray['documentlink'] = array('discription' => __('Back to overview',true), 'controller' => 'documents','action' => 'index', 'terms' => null);
        $SettingsArray['backlink'] = array('discription' => __('Back', true), 'controller' => 'documents','action' => 'files', 'terms' => $this->request->projectvars['VarsArray']);

        $this->set('SettingsArray', $SettingsArray);
    }

    protected function __filesdescription($models, $successtarget)
    {
        $testingmethod_id = $this->Sicherheit->Numeric($this->request->params['pass']['15']);
        $document_id = $this->Sicherheit->Numeric($this->request->params['pass']['16']);
        $file_id = 0;

        if (isset($this->request->params['pass']['17']) && $this->Sicherheit->Numeric($this->request->params['pass']['17']) > 0) {
            $file_id = $this->Sicherheit->Numeric($this->request->params['pass']['17']);
        }

        if (!$this->$models['top']->exists($document_id)) {
            throw new NotFoundException(__('Invalid document'));
        }

        $this->loadModel($models['file']);

        if (isset($this->request->data[$models['file']]) && ($this->request->is('post') || $this->request->is('put'))) {
            if ($this->$models['file']->save($this->request->data)) {
                $this->Session->setFlash(__('The value has been saved'));
                $this->Autorisierung->Logger($this->request->data[$models['file']]['id'], $this->request->data[$models['file']]);


                if ($models['file'] == 'Documentsfile') {
                    $_here = explode('/', $this->request->here);
                    $_here[3] = 'files';
                    $this->request->here = implode('/', $_here);
                    $this->files();
                }
                $this->render($successtarget);
            } else {
                $this->Session->setFlash(__('The value could not be saved. Please, try again.'));
            }
        }

        if ($models['top'] != null && $models['main'] != null && $models['sub'] != null) {
            $certificate_data_option = array(
                                    'order' => array($models['sub'].'.id DESC'),
                                    'conditions' => array(
                                        $models['sub'].'.certificate_id' => $certificate_id,
                                        $models['sub'].'.deleted' => 0,
                                        )
                                    );

            $certificate_data = $this->$models['top']->$models['main']->$models['sub']->find('first', $certificate_data_option);
        }

        if ($models['top'] != null && $models['main'] == null && $models['sub'] == null) {
            $certificate_data_option = array(
                                    'order' => array($models['top'].'.id DESC'),
                                    'conditions' => array(
                                        $models['top'].'.id' => $document_id,
                                        $models['top'].'.deleted' => 0,
                                        )
                                    );
            $certificate_data = $this->$models['top']->find('first', $certificate_data_option);
        }

        if ($file_id == 0) {
            $certificate_data_option = array(
                                    'order' => array($models['file'].'.id DESC'),
                                    'conditions' => array(
                                        $models['file'].'.parent_id' => $document_id,
                                        $models['file'].'.user_id' => $this->Auth->user('id'),
                                        $models['file'].'.testingcomp_id' => $this->Autorisierung->ConditionsTestinccomps(),
                                        )
                                    );
        } elseif ($file_id > 0) {
            $certificate_data_option = array(
                                    'conditions' => array(
                                        $models['file'].'.id' => $file_id,
                                        $models['file'].'.testingcomp_id' => $this->Autorisierung->ConditionsTestinccomps(),
                                        )
                                    );
        }

        $certificate_files = $this->$models['file']->find('first', $certificate_data_option);

        $this->request->data = $certificate_files;


        $this->set('certificate_files', $certificate_files);
        $this->set('certificate_data', $certificate_data);
        $this->set('models', $models);
    }

    public function getfiles()
    {
        $testingmethod_id = $this->Sicherheit->Numeric($this->request->params['pass']['15']);
        $document_id = $this->Sicherheit->Numeric($this->request->params['pass']['16']);
        $file_id = $this->Sicherheit->Numeric($this->request->params['pass']['17']);

        $models = array('main' => 'Documentfile','sub' => null);
        $path = Configure::read('document_folder') . $document_id. DS . 'documents' . DS;

        $this->loadModel($models['main']);
        $this->__getfiles($path, $models);
    }

    protected function __getfiles($path, $models)
    {
        $this->layout = 'blank';
        $examiner_id = $this->Sicherheit->Numeric($this->request->params['pass']['15']);
        $certificate_id = $this->Sicherheit->Numeric($this->request->params['pass']['16']);
        $file_id = $this->Sicherheit->Numeric($this->request->params['pass']['17']);

        $certificate_data_option = array(
                                    'conditions' => array(
                                        $models['main'].'.testingcomp_id' => $this->Autorisierung->ConditionsTestinccomps(),
                                        $models['main'].'.id' => $file_id
                                        )
                                    );

        $certificate_data = $this->$models['main']->find('first', $certificate_data_option);

        if (count($certificate_data) == 0) {
            $this->Session->setFlash(__('The file does not exist.'));
            $this->redirect(array_merge(array('controller' => $this->Session->read('lastURLArray.controller'), 'action' => $this->Session->read('lastURLArray.action')), $this->request->projectvars['VarsArray']));
        } elseif (count($certificate_data) == 1) {
            if (file_exists($path . $certificate_data[$models['main']]['name'])) {
                $this->set('path', $path . $certificate_data[$models['main']]['name']);
            } else {
                $this->Session->setFlash(__('The file cannot be found.'));
                $this->redirect(array_merge(array('controller' => $this->Session->read('lastURLArray.controller'), 'action' => $this->Session->read('lastURLArray.action')), $this->request->projectvars['VarsArray']));
            }
        }
    }

    protected function __delfiles($models, $successtarget)
    {
        $testingmethod_id = $this->Sicherheit->Numeric($this->request->params['pass']['15']);
        $document_id = $this->Sicherheit->Numeric($this->request->params['pass']['16']);
        $file_id = $this->Sicherheit->Numeric($this->request->params['pass']['17']);

        if (!$this->$models['top']->exists($document_id)) {
            throw new NotFoundException(__('Invalid value'));
        }

        $this->loadModel($models['file']);

        if (!$this->$models['file']->exists($file_id)) {
            throw new NotFoundException(__('Invalid file'));
        }

        if (isset($this->request->data[$models['file']]) && ($this->request->is('post') || $this->request->is('put'))) {
            $this->request->data[$models['file']]['deleted'] = 1;

            if ($this->$models['file']->save($this->request->data)) {
                $this->Session->setFlash(__('The file has been deleted'));
                $this->Autorisierung->Logger($this->request->data[$models['file']]['id'], $this->request->data[$models['file']]);


                $_here = explode('/', $this->request->here);
                $_here[3] = 'files';
                $this->request->here = implode('/', $_here);
                $this->files();
                $this->render($successtarget);
            } else {
                $this->Session->setFlash(__('The file could not be deleted. Please, try again.'));
            }
        }

        $certificate_data_option = array(
                                    'order' => array($models['top'].'.id DESC'),
                                    'conditions' => array(
                                        $models['top'].'.id' => $document_id,
                                        $models['top'].'.deleted' => 0,
                                        )
                                    );

        $certificate_data = $this->$models['top']->find('first', $certificate_data_option);

        $certificate_data_option = array(
                                'conditions' => array(
                                    $models['file'].'.id' => $file_id,
                                    $models['file'].'.testingcomp_id' => $this->Autorisierung->ConditionsTestinccomps(),
                                    )
                                );

        $certificate_files = $this->$models['file']->find('first', $certificate_data_option);

        $this->request->data = $certificate_files;

        $this->set('models', $models);
        $this->set('certificate_data', $certificate_data);
        $this->set('certificate_files', $certificate_files);
    }

    public function deldocumentfiles()
    {
        $this->layout = 'modal';

        $models['top'] = 'Document';
        $models['main'] = null;
        $models['sub'] = null;
        $models['file'] = 'Documentsfile';

        $this->__delfiles($models, 'files');

        $lastModal = array(
                        'controller' => 'examiners',
                        'action' => 'certificatesfiles',
                        'terms' => $this->request->projectvars['VarsArray']
                        );

        if ($this->Session->check('lastModalURLArray')) {
            $lastModal = array(
                        'controller' => $this->Session->read('lastModalURLArray.controller'),
                        'action' => $this->Session->read('lastModalURLArray.action'),
                        'terms' => $this->Session->read('lastModalURLArray.pass')
                        );
        }

        $SettingsArray = array();
        $SettingsArray['backlink'] = array('discription' => __('Back', true), 'controller' => 'documents','action' => 'files', 'terms' => $lastModal['terms']);

        $this->set('SettingsArray', $SettingsArray);
    }

    public function certificatefile()
    {
        $testingmethod_id = $this->Sicherheit->Numeric($this->request->params['pass']['15']);
        $document_id = $this->Sicherheit->Numeric($this->request->params['pass']['16']);

        $models = array('top' => 'Document','main' => 'DocumentCertificate','sub' => 'DocumentCertificateData');
        $path = Configure::read('document_folder') . $testingmethod_id . DS . $document_id. DS;

        $this->fileupload($path, $models);
        if (isset($this->request->data['close_reload']) && $this->request->data['close_reload'] == 1) {
            $FormName['controller'] = 'documents';
            $FormName['action'] = 'view';
            $FormName['terms'] = implode('/', $this->request->projectvars['VarsArray']);
            $this->set('message_class', 'success');

            $this->set('FormName', $FormName);
        }
        $SettingsArray = array();

        $this->set('SettingsArray', $SettingsArray);
    }

    protected function fileupload($path, $models)
    {
        $this->layout = 'modal';
        if (isset($this->request->data['DocumentManagerSearchAutocomplete'])) {
            unset($this->request->data['DocumentManagerSearchAutocomplete']);
        }

        $top = $models['top'];
        $testingmethod_id = $this->Sicherheit->Numeric($this->request->params['pass']['15']);
        $document_id = $this->Sicherheit->Numeric($this->request->params['pass']['16']);


        $_data_option = array(
                'conditions' => array(
                    $models['top'] . '.id' => $document_id,
                    $models['top'] . '.deleted' => 0,
                    )
                );

        $_data = $this->$top->find('first', $_data_option);

        if ($_data[$models['top']]['certified_file'] != '' && file_exists($path . $_data[$models['top']]['certified_file'])) {
            $this->set('hint', __('Do you want to overwrite the existing file?', true));
        }

        $this->set('data', $_data);
        $this->set('models', $models);

        $Extension = array('pdf','jpeg','png','avi','bmp','svg','doc','docx','odt','mp4','mkv','xlsx','xls','ppt','csv','txt','jpg');

        if (isset($_FILES) && count($_FILES) > 0) {
            // Infos zur Datei

            $fileinfo = pathinfo($_FILES['file']['name']);
            $filename_new = $document_id.'_'.time().'.'.$fileinfo['extension'];

            foreach ($Extension as $exkey => $exvalue) {
                // code...

                if ($exvalue == strtolower($fileinfo['extension'])) {
                    if (isset($_FILES['error']['file'])) {
                        $this->Session->write('FileUploadErrors', $_FILES['error']['file']);
                    }

                    // Vorhandene Dateien werden gelöscht
                    // Vorhandene Dateien werden gelöscht
                    if ($_FILES['file']['error'] == 0) {
                        $folder = new Folder($path);
                        $folder->delete();

                        if (!file_exists($path)) {
                            $dir = new Folder($path, true, 0755);
                        }
                    }

                    move_uploaded_file($_FILES['file']["tmp_name"], $path. DS .$filename_new);

                    $dataFiles = array(
                            'id' => $document_id,
                            'certified_file' => $filename_new,
                            'user_id' => $this->Auth->user('id')
                        );
                    $this->request->data['Document'] = $dataFiles;
                    $this->request->data['DocumentTestingmethod']['DocumentTestingmethod'] = $testingmethod_id;
                    //	$this->request->data['Document'] = $dataFiles;
                    if ($this->Document->save($this->request->data)) {
                    } else {
//                        var_dump($this->Document->validationErrors);
                    }

                    break;
                }
            }
        }
    }

    public function getcertificatefile()
    {
        $testingmethod_id = $this->Sicherheit->Numeric($this->request->params['pass']['15']);
        $document_id = $this->Sicherheit->Numeric($this->request->params['pass']['16']);
        $models = array('top' => 'Document','main' => 'DocumentCertificate','sub' => 'DocumentCertificateData');
        $path = Configure::read('document_folder') . $testingmethod_id. DS . $document_id . DS;
        $this->getfile($path, $models);
    }

    protected function getfile($path, $models)
    {
        $this->layout = 'blank';
        $top = $models['top'];
        $testingmethod_id = $this->Sicherheit->Numeric($this->request->params['pass']['15']);
        $document_id = $this->Sicherheit->Numeric($this->request->params['pass']['16']);

        $certificate_data_option = array(
                                    'conditions' => array(
                                        $models['top'].'.id' => $document_id
                                        )
                                    );

        $certificate_data = $this->$top->find('first', $certificate_data_option);

        $this->set('path', $path . $certificate_data[$models['top']]['certified_file']);
    }

    public function delcertificatefile()
    {
        $SettingsArray = array();
        $SettingsArray['backlink'] = array('discription' => __('Back', true) . ' ' . $certificate_data['Document']['name'], 'controller' => 'documents','action' => 'monitoring', 'terms' => $this->request->params['pass']);

        $this->set('SettingsArray', $SettingsArray);

        $this->_delfile(array('top' => 'Document','main' => 'DocumentCertificate','sub' => 'DocumentCertificateData'), 'document_folder', 'monitoring');
    }

    protected function _delfile($model, $folder, $target)
    {
        $this->layout = 'modal';

        $document_id = $this->Sicherheit->Numeric($this->request->params['pass']['16']);
        $certificate_id = $this->Sicherheit->Numeric($this->request->params['pass']['17']);
        $certificate_data_id = $this->Sicherheit->Numeric($this->request->params['pass']['18']);

        $this->request->projectvars['VarsArray'][17] = $certificate_id;
        $this->request->projectvars['VarsArray'][18] = $certificate_data_id;

        if (!$this->$model['top']->exists($document_id)) {
            throw new NotFoundException(__('Invalid document'));
        }

        if (!$this->$model['top']->$model['main']->exists($certificate_id)) {
            throw new NotFoundException(__('Invalid monitoring'));
        }

        if (!$this->$model['top']->$model['main']->$model['sub']->exists($certificate_data_id)) {
            throw new NotFoundException(__('Invalid monitoring info'));
        }

        $this->set('hint', __('Will you delete this file?', true));
        $this->set('del_button', __('Delete', true));

        if (isset($this->request->data[$model['sub']]) && ($this->request->is('post') || $this->request->is('put'))) {
            //			unset($this->request->data['Order']);

            $this->request->data[$model['sub']]['user_id'] = $this->Auth->user('id');

            if ($this->$model['top']->$model['main']->$model['sub']->save($this->request->data)) {
                $path = Configure::read($folder) . $document_id. DS . $certificate_id . DS . $certificate_data_id . DS;
                $folder = new Folder($path);
                $folder->delete();

                $this->set('hint', __('The file has been delete', true));
                $this->Autorisierung->Logger($certificate_data_id, $this->request->data[$model['sub']]);
                $this->set('del_button', null);
            } else {
                $this->set('hint', __('The file could not be delete. Please, try again.', true));
            }

            $FormName['controller'] = 'documents';
            $FormName['action'] = 'monitorings';
            $FormName['terms'] = implode('/', $this->request->projectvars['VarsArray']);

            $this->set('FormName', $FormName);
        }

        $certificate_data_option = array(
                                    'order' => array('certified_date DESC'),
                                    'conditions' => array(
                                        $model['sub'].'.id' => $certificate_data_id
                                        )
                                    );

        $certificate_data = $this->$model['top']->$model['main']->$model['sub']->find('first', $certificate_data_option);

        if (isset($this->request->data['back']) && $this->request->data['back'] == 1) {
            $certificateinfo = 1;
        }

        $this->request->data = $certificate_data;

        $arrayData = $this->Xml->DatafromXml('DocumentCertificateEdit', 'file', null);

        $this->set('certificate_data', $certificate_data);
        $this->set('settings', $arrayData['headoutput']);
    }

    public function email_certificate()
    {
        $this->layout = 'modal';

        $message  = null;
        $message .= __('Eine Zusammenfassung von anstehenden und fehlerhaften Überwachungen, wird an die eingegebene E-Mail-Adresse versand.');
        $message .= '<br>';
        $message .= __('Sie können Meldungen abwählen in dem Sie die Checkbox der Meldung deaktivieren.');

        $this->set('message', $message);

        $_ids = array();

        $arrayData = $this->Xml->DatafromXml('DocumentCertificate', 'file', null);
        $certificat_types = $arrayData['settings']->DocumentCertificate->certificat->select->static->value;

        $certificat_types_summary = array();


        if (isset($this->request->data['Document']) && ($this->request->is('post') || $this->request->is('put'))) {
            $emailto = $this->request->data['Document']['email'];
            $isValid = Validation::email($emailto);

            if ($isValid === true) {
                $_id = array();
                foreach ($this->request->data['Document'] as $_key => $_data) {
                    if (is_numeric($_key)) {
                        if ($_data == 1) {
                            $_id[] = $_key;
                        }
                    }
                }

                $_ids = array('DocumentCertificate.id' => $_id);
            }
        }

        $certificates_options = array(
                                    'order' => array('DocumentCertificateData.id DESC'),
                                    'conditions' => array(
                                                    'Document.testingcomp_id' => $this->Autorisierung->ConditionsTestinccomps(),
                                                    'Document.deleted' => 0,
                                                    'DocumentCertificateData.active' => 1,
                                                    'DocumentCertificateData.deleted' => 0,
                                                    $_ids
                                                )
                                            );

        $certificate = $this->Document->DocumentCertificate->DocumentCertificateData->find('all', $certificates_options);

        $certficates = array();

        $summary = array(
                        'errors' => array(),
                        'warnings' => array(),
                        'futurenow' => array(),
                        'future' => array(),
                        'hints' => array(),
                        'deactive' => array()
                    );

        $summary_desc = array(
                    'futurenow' => array(
                            0 =>__('First-time certification reached', true),
                            1 =>__('First-time certifications reached', true),
                            ),

                    'future' => array(
                            0 =>__('First-time certification', true),
                            1 =>__('First-time certifications', true),
                            ),
                    'errors' => array(
                            0 =>__('Irregularity', true),
                            1 =>__('Irregularities', true),
                            ),
                    'warnings' => array(
                            0 => __('Warning', true),
                            1 => __('Warnings', true)
                            ),
                    'hints' => array(
                            0 => __('Hint', true),
                            1 => __('Hints', true)
                            ),
                    'deactive' => array(
                            0 => __('Deactive', true),
                            1 => __('Deactive', true)
                            )
                        );

        foreach ($certificate as $_key => $_certificates) {
            $certificate[$_key] = $this->Qualification->DocumentCertification($_certificates, array('top'=>'Document','main'=>'DocumentCertificate','sub'=>'DocumentCertificateData'));
            foreach ($certificat_types as $_certificat_types) {
                if ($_certificates['DocumentCertificate']['certificat'] != trim($_certificat_types)) {
                    continue;
                }
                $summary = $this->Qualification->DocumentCertificationSummary($summary, $certificate[$_key], array('top'=>'Document','main'=>'DocumentCertificate','sub'=>'DocumentCertificateData'), trim($_certificat_types));
            }
        }

        //pr($summary);
        foreach ($summary_desc as $__key => $__summary_desc) {
            if (count($summary[$__key]) > 0) {
                foreach ($summary[$__key] as $___key => $___summary) {
                    foreach ($certificat_types as $_key => $_certificat_types) {
                    }
                }
            }
        }


        //pr($certificat_types_summary);
        /*

                    // Nach Fehler/Hinweisarten sortieren
                    foreach($summary as $__key => $_summary){
                        if(isset($___certificates['summary'][$__key]) && count($___certificates['summary'][$__key]) > 0){
                            $summary[$__key][$_certificates['Examiner']['id']] = $___certificates['summary'][$__key];
                        }
                    }
        */

        $SettingsArray = array();
        //		$SettingsArray['documentlink'] = array('discription' => __('Back to overview',true), 'controller' => 'documents','action' => 'index', 'terms' => null);
        //		$SettingsArray['backlink'] = array('discription' => __('Back',true), 'controller' => 'documents','action' => 'view', 'terms' => $this->request->projectvars['VarsArray']);

        $this->set('SettingsArray', $SettingsArray);

        $this->__email($summary, $summary_desc, 'summary_monitoring', 'Monitoringinfos');
    }

    protected function __email($summary, $summary_desc, $mailview, $subject)
    {
        $lastModalURLArray['controller'] = 'documents';
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

        if (isset($this->request->data['Document']) && ($this->request->is('post') || $this->request->is('put'))) {
            $emailto = $this->request->data['Document']['email'];
            $isValid = Validation::email($emailto);
            $url['controller'] = $this->request->data['url']['controller'];
            $url['action'] = $this->request->data['url']['action'];

            if (!isset($this->request->data['url']['pass'])) {
                $url['pass'] = array();
            } else {
                $url['pass'] = $this->request->data['url']['pass'];
            }

            if ($isValid === true) {
                App::uses('CakeEmail', 'Network/Email');

                $commentar = null;

                if ($this->request->data['Document']['comment'] != '') {
                    App::uses('Sanitize', 'Utility');
                    $commentar = Sanitize::stripScripts($this->request->data['Document']['comment']);
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

                $SettingsArray = array();
                $SettingsArray['backlink'] = array('discription' => __('Back', true), 'controller' => 'documents','action' => 'email_certificate', 'terms' => $this->request->projectvars['VarsArray']);

                $this->set('SettingsArray', $SettingsArray);
                $this->set('lastModalURLArray', $url);
                $this->set('message', __('E-Mail versendet an '.$emailto, true));
                $this->render('email_successful_send', 'modal');
            } elseif ($isValid === false) {
                $this->set('message', __('E-Mail konte nicht versendet werden. Sie haben keine gültige E-Mail-Adresse angegeben.', true));
                $this->set('lastModalURLArray', $url);
            }
        }
    }

        public function search()
        {
            isset($this->request->data['delsession']) && $this->request->data['delsession'] > 0 ? $this->Session->delete('searchdocument') : '';
            unset($this->Document->validate);
            $this->layout = 'modal';
            $arrayData = $this->Xml->DatafromXml('searchdocument', 'file', null);
            $settings = $arrayData['settings'];

            $locale = $this->Lang->Discription();

            $this->Document->recursive = 1;
            //$period = array(0,1,2,3);


            foreach ($settings->Document->children() as $_settings => $value) {
                if ($value->fieldtype == 'select' && (!empty($value->key))) {
                    $selects[$_settings] ['discription'] = trim($value->discription->$locale);
                    $selects[$_settings] ['values'] = $this->Document->find(
                        'list',
                        array(
                'fields' => array($_settings,$_settings),
                                'order'  => array($_settings => 'asc'),
                                    'conditions' => array(
                                        'Document.testingcomp_id' => $this->Autorisierung->ConditionsTestinccomps(),
                                        'Document.deleted' => 0
                                    )
                )
                    );
                    $selects[$_settings] ['values'] = array_unique($selects[$_settings] ['values']);
                }
            }

            if (isset($this->request->data['Document']) && !empty($this->request->data)&& ($this->request->is('post'))) {
                $this->request->data['User']['user_id'] = $this->Auth->user('id');
                $this->request->data['User']['testingcomp_id'] = $this->Auth->user('testingcomp_id');
                $this->request->data = $this->Data->ChangeDropdownData($this->request->data, $arrayData, 'Document');

                $this->Session->write('searchdocument.params', $this->request->data);
                $this->Session->write('searchdocument.fields', $this->request->data);
                //pr($this->Session->read('searchdevice.params'));
                $searchparams = '';
                //$testingmethod_id = $this->request->data['DeviceTestingmethod'] ['DeviceTestingmethod'];

                //  isset($testingmethod_id) ? $testingmethod = $this->Device->DeviceTestingmethod->find('first',array('conditions' => array('DeviceTestingmethod.id' => $testingmethod_id))): '';
                foreach ($settings->Document->children() as $_settings => $value) {
                    //Sortierung aus der XML holen---------------------------------
                    if (isset($value->order)) {
                        $i= intval($value->order->priority);
                        $dir = $value->order->direction;
                        $OrderBy[$i]['Document.'.$_settings] = trim($dir);
                    }

                    //Suchkriterien erzeugen -----------------------------------

                    // empty($this->request->data['DeviceCertificateData']['next_certification_in']) ? $this->request->data['DeviceCertificateData']['period'] = '' : '';
                    $searchoutput = Hash::extract($this->request->data, '{s}.'.$_settings);
                    /*  if(!empty($testingmethod)&& ($value->key == 'testingmethod')) {
                          $searchparams = $searchparams.' '.$value->discription->$locale.': '.$testingmethod ['DeviceTestingmethod'] ['verfahren'].' | ';
                      }*/

                    if (isset($value->fieldtype) && ($value->fieldtype == 'select') && (empty($value->key))) {
                        if (!empty($searchoutput[0])) {
                            $opt = intval($searchoutput[0]);
                            $searchparams = $searchparams.' '.$value->discription->$locale.': '. $value->options->value[$opt]->$locale.' | ';
                        }
                    } else {
                        !empty($searchoutput[0]) ? $searchparams = $searchparams.' '.$value->discription->$locale.': '. $searchoutput[0].' | ' : '';
                    }
                }

                //Sortierung für die Ausgabe----------------------------------------
                ksort($OrderBy);
                $nOrderBy = array();
                foreach ($OrderBy as $ob_key => $ob_val) {
                    $nOrderBy [key($ob_val)] =  $OrderBy [$ob_key] [key($ob_val)];
                }

                //Conditions aus den abschickten Daten erzeugen------------------------
                $documentsearch = $this->request->data['Document'] ;
                foreach ($documentsearch as $ds_key => $value) {
                    if (isset($value)&& $value <> '') {
                        if ($ds_key == 'active') {
                            $conditions['Document.'.$ds_key] = $value;
                        } else {
                            $conditions['Document.'.$ds_key.' LIKE'] = '%'.$value.'%';
                        }
                    }
                }


                $conditions ['Document.deleted'] = 0;
                $conditions['Document.testingcomp_id'] = $this->Auth->user('testingcomp_id');

                //Daten holen----------------------------------------------
                $this->Document->recursive= 1;
                $documents = $this->Document->find('all', array(
                'order' => $nOrderBy,
                'conditions' =>  isset($conditions) ? $conditions : '',

                 ));

                $result = $documents;
                if (isset($result)) {
                    foreach ($result as $_key => $_documents) {
                        $path = Configure::read('document_folder') . $_documents['DocumentTestingmethod'][0]['id'] . DS . $_documents['Document']['id']. DS;

                        if (!empty($_documents['Document']['certified_file']) && file_exists($path .$_documents['Document']['certified_file'])) {
                            $result[$_key]['Document']['certified_file_valide'] = 1;
                        } else {
                            $result[$_key]['Document']['certified_file_valide'] = 0;
                        }
                    }

                    $this->set('documents', $result);
                }

                if (isset($result)) {
                    $countresult = count($result);
                } else {
                    $countresult = 0;
                }
                $this->Session->write('searchdocument.countresult', $countresult);
                if (!isset($this->request->data['shownoresults'])) {
                    $lastUrl = $this->Session->read('lastmodalArrayURL');

                    isset($result) ? $this->Session->write('searchdocument.documents', $result) : '';
                    $this->Session->write('searchdocument.params', $searchparams);
                    $this->Session->write('searchdocument.searching', 1);



                    $SettingsArray = array();
                    $SettingsArray['backlink'] = array('discription' => __('back to search', true), 'controller' => 'documents','action' => 'search', 'terms' => null);
                    // $SettingsArray['printlink'] = array('discription' => __('Print device list',true), 'controller' => 'documents','action' => 'pdfinv', 'terms' => $this->request->projectvars['VarsArray']);
                    $xml = $this->Xml->DatafromXml('display_document', 'file', null);

                    if (!is_object($xml['settings'])) {
                        pr('Einstellungen konnten nicht geladen werden.');
                        return;
                    } else {
                        $this->set('xml', $xml['settings']);
                    }
                    $this->set('SettingsArray', $SettingsArray);
                    $this->render('result', 'modal');
                }
            }


            $this->loadModel('Testingcomp');
            $this->Testingcomp->recursive = -1;
            $testingcomps = $this->Testingcomp->find(
                'list',
                array(
              'fields' => array('id','firmenname'),
                                  'conditions' => array(
                                      'Testingcomp.id' => $this->Autorisierung->ConditionsTestinccomps()
                                  )
              )
            );

            //   $this->Session->write('Device.form', $this->request->data);

            if ($this->Session->check('searchdocument.fields')) {
                $this->request->data = $this->Session->read('searchdocument.fields');
                if ($this->Session->check('searchdocument.countresult')) {
                    $countresult = $this->Session->read('searchdocument.countresult');
                }
                if ($this->Session->check('searchdocument.searching')) {
                    $this->request->data['searching'] = $this->Session->read('searchdocument.searching');
                }
            }



            // $this->Data->SessionFormload();

            $this->set('selects', $selects);
            $this->set('settings', $arrayData['headoutput']);
            $this->set('locale', $locale);
            $this->set('testingcomps', $testingcomps);
            isset($countresult) ? $this->set('countresult', $countresult) : '';
            //  $this->set('testingmethods', $testingmethods);
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

               $Source = $this->Document->getDataSource('columns');
               $table = $Source->describe('documents');

               if (!isset($table[$field])) {
                   $this->set('test', json_encode(array()));
                   return;
               }

               $options = array(
                               'limit' => 20,
                               'fields' => array($field),
                               'group' => array($field),
                               'conditions' => array(
                                   'Document.deleted' => 0,
                                   'Document.active' => 1,
                                   'Document.' . $field . ' LIKE' => '%' . $data . '%'
                                   )
                               );

               $result = $this->Document->find('list', $options);

               //$this->Autorisierung->WriteToLog($result);

               $this->set('test', json_encode($result));
           }
}
