<?php
App::uses('AppController', 'Controller');

class TechnicalplacesController extends AppController
{
    public $components = array('Auth','Acl','Autorisierung','Cookie','Navigation','Lang','Sicherheit','Xml','Pdf','RequestHandler','Search','Data','Csv','Image','ExpeditingTool');
    public $helpers = array('Lang','Navigation','JqueryScripte','ViewData');
    public $layout = 'ajax';

    public function beforeFilter()
    {
        App::import('Vendor', 'Authorize');
        // Es wird das in der Auswahl gewählte Project in einer Session gespeichert
        $this->Session->write('orderURL', null);

        //		if($this->request->params['action'] == 'index') {
        $this->Session->write('projectURL', $this->request->params['pass'][0]);
        //		}

        $this->Navigation->ReportVars();

        $this->Autorisierung->ConditionsTopprojectsTest($this->request->projectID);

        $noAjaxIs = 0;
        $noAjax = array('pdf','test','image');

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

        $this->loadModel('User');
        $this->loadModel('Topproject');
        $this->Autorisierung->Protect();
        //$this->Navigation->ajaxURL();
        $this->Lang->Choice();
        $this->Lang->Change();

        $this->set('lang', $this->Lang->Choice());
        $this->set('selected', $this->Lang->Selected());
        $this->set('login_info', $this->Navigation->loggedUser());
        $this->set('lang_choise', $this->Lang->Choice());
        $this->set('previous_url', $this->base.'/'.$this->Session->read('lastURL'));
        $this->set('locale', $this->Lang->Discription());

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
        $projectID = $this->request->projectID;
        $cascadeID = $this->request->cascadeID;
        $projectID = $this->request->projectvars['VarsArray'][0];
        $cascadeID = $this->request->projectvars['VarsArray'][1];
        $variantID = $this->request->projectvars['VarsArray'][2];

        $arrayData = $this->Xml->DatafromXml('display_tp', 'file', null);
        $this->set('xml', $arrayData['settings']);

        $this->loadmodel('Technicalplacevariant');
        $tpvariant = $this->Technicalplacevariant->find('first', array('conditions'=>array('Technicalplacevariant.id'=>$variantID)));
        $technicalplaces = $this->Technicalplace->find('all', array('conditions'=> array('Technicalplace.deleted' => 0,'Technicalplace.technicalplace_variant_id'=>$variantID )));

        $this->loadmodel('Cascade');
        $CascadeCurrent = $this->Cascade->find('first', array('conditions' => array('Cascade.id' => $cascadeID)));
        $this->Cascade->recursive = -1;
        $CascadeChild = $this->Cascade->find('first', array('fields' => array('id'), 'conditions' => array('Cascade.parent' => $CascadeCurrent['Cascade']['id'])));
        $CascadeParent = $this->Cascade->find('first', array('conditions' => array('Cascade.id' => $CascadeCurrent['Cascade']['parent'])));
        $CascadeChildrenList = $this->Navigation->CascadeGetChildrenList($CascadeCurrent['Cascade']['id']);
        $Cascade = array('current' => $CascadeCurrent,'child' => $CascadeChild,'parent' => $CascadeParent);
        $Cascade = array('current' => $CascadeCurrent,'child' => $CascadeChild,'parent' => $CascadeParent);

        $SettingsArray = array();
        $SettingsArray['addsearching'] = array('discription' => __('Searching', true), 'controller' => 'searchings','action' => 'search', 'terms' => $this->request->projectvars['VarsArray'],);

        $breads= array();

        $technicalplaces = $this->paginate = array('conditions'=> array('Technicalplace.technicalplace_variant_id'=>$variantID,'Technicalplace.deleted' => 0));

        $technicalplaces = $this->paginate('Technicalplace');
        $this->set('technicalplaces', $technicalplaces);
        $breads = $this->Navigation->Breads(null);
        $CascadeForBread[] = $Cascade['current'];
        $CascadeForBread = $this->Navigation->CascadeGetBreads($CascadeForBread);
        $breads = $this->Navigation->CascadeCreateBreadcrumblist($breads, $CascadeForBread);


        $breads[5]['controller'] = 'technicalplaces';
        $breads[5]['discription'] = $tpvariant['Technicalplacevariant']['name'];

        $breads[5]['action'] = 'index';
        $breads[5]['pass'] = $this->request->projectvars['VarsArray'];

        $SettingsArray = array();

        $this->set('SettingsArray', $SettingsArray);
        $this->set('breads', $breads);
        $this->set('headline', $tpvariant['Technicalplacevariant']['name']);
    }

    public function overview()
    {
        $projectID = $this->request->projectID;
        $cascadeID = $this->request->cascadeID;
        $projectID = $this->request->projectvars['VarsArray'][0];
        $cascadeID = $this->request->projectvars['VarsArray'][1];
        $variantID = $this->request->projectvars['VarsArray'][2];
        $tpid = $this->request->projectvars['VarsArray'][16];

        $this->loadmodel('Technicalplacevariant');
        $tpvariant = $this->Technicalplacevariant->find('first', array('conditions'=>array('Technicalplacevariant.id'=>$variantID)));

        $technicalplace = $this->Technicalplace->find('first', array('conditions'=> array('Technicalplace.deleted' => 0,'Technicalplace.id'=>$tpid )));

        $this->loadmodel('Cascade');
        $CascadeCurrent = $this->Cascade->find('first', array('conditions' => array('Cascade.id' => $cascadeID)));
        $this->Cascade->recursive = -1;
        $CascadeChild = $this->Cascade->find('first', array('fields' => array('id'), 'conditions' => array('Cascade.parent' => $CascadeCurrent['Cascade']['id'])));
        $CascadeParent = $this->Cascade->find('first', array('conditions' => array('Cascade.id' => $CascadeCurrent['Cascade']['parent'])));
        $CascadeChildrenList = $this->Navigation->CascadeGetChildrenList($CascadeCurrent['Cascade']['id']);
        $Cascade = array('current' => $CascadeCurrent,'child' => $CascadeChild,'parent' => $CascadeParent);
        $Cascade = array('current' => $CascadeCurrent,'child' => $CascadeChild,'parent' => $CascadeParent);
        $CascadeForBread[] = $Cascade['current'];
        $CascadeForBread = $this->Navigation->CascadeGetBreads($CascadeForBread);

        $breads = $this->Navigation->Breads(null);
        $breads = $this->Navigation->CascadeCreateBreadcrumblist($breads, $CascadeForBread);

        $breads[] = array('controller' => 'technicalplaces','discription' => $tpvariant['Technicalplacevariant']['name'],'action' => 'index','pass' => $this->request->projectvars['VarsArray']);
        $breads[] = array('controller' => 'technicalplaces','discription' => $technicalplace['Technicalplace']['description'],'action' => 'overview','pass' => $this->request->projectvars['VarsArray']);

        $SettingsArray = array();

        $this->set('SettingsArray', $SettingsArray);
        $this->set('technicalplace', $technicalplace);
        $this->set('breads', $breads);
    }

    public function status($id = null)
    {
        $this->layout = 'modal';

        // Variablen überprüfen
        $projectID = $this->request->projectvars['VarsArray'][0];
        $cascadeID = $this->request->projectvars['VarsArray'][1];
        $orderID = $this->request->projectvars['VarsArray'][2];
        $id = $this->request->projectvars['VarsArray'][2];

        $options = array('conditions' => array('Order.' . $this->Order->primaryKey => $orderID));
        $this->Order->recursive = 1;
        $orders = $this->Order->find('first', $options);

        if (isset($this->request->data['Order']) && $this->request->is('put')) {
            if ($orders['Order']['status'] == 1) {
                $status = '0';
            } else {
                $status = 1;
            }

            $updateArray['Order']['id'] = $orderID;
            $updateArray['Order']['status'] = $status;

            // Wenn der Auftrag wieder geöffnet wird, werden alle Abrechnungen gelöscht
            if ($status == 0) {
                $updateArray['Order']['invoices'] = null;
            }

            $updateArray['Order']['id'] = $orderID;
            $updateArray['Order']['status'] = $status;
            $updateArray['Order']['invoices'] = null;

            if ($this->Order->save($updateArray)) {
                $this->Order->Reportnumber->updateAll(
                    array(
                                    'Reportnumber.status' => 2
                                ),
                    array(
                                    'Reportnumber.order_id' => $orders['Order']['id']
                                )
                );

                if ($status != 0) {
                    $this->Session->setFlash(__('The order has been locked.'));
                    $logArray = array('status' => 'Auftrag geschlossen');
                    $this->Autorisierung->Logger($id, $logArray);
                } elseif ($status == 0) {
                    $this->Session->setFlash(__('The order has been reopened.'));
                    $logArray = array('status' => 'Auftrag  erneut geöffnet');
                    $this->Autorisierung->Logger($id, $logArray);
                }
            }

            $orders = $this->Order->find('first', $options);

            $forwarding = array_pop($this->request->params['pass']);

            if ($forwarding == 1) {
                $FormName = array('controller' => 'reportnumbers', 'action' => 'index', 'terms' => implode('/', $this->request->projectvars['VarsArray']));
            }
            if ($forwarding == 2) {
                $FormName = array('controller' => 'developments', 'action' => 'orders', 'terms' => $orders['Development'][0]['id']);
            }
            $this->set('FormName', $FormName);
            $this->set('afterChange', 1);
        }

        $this->request->data = $orders;

        if ($orders['Order']['status'] == 1) {
            $message = __('Will you you change the state to open.', true);
        }
        if ($orders['Order']['status'] == 0) {
            $message = __('Will you you change the state to close.', true);
        }


        $SettingsArray = array();

        $this->set('SettingsArray', $SettingsArray);
        $this->set('message', $message);
    }

    public function quicksearch()
    {
        // Wenn ein Prüfbericht über die Direktsuche aufgerufen wird

        if (isset($this->request->data['Order']['search']) && $this->request->data['Order']['search'] != '') {
            $this->loadModel('Reportnumber');
            $this->Reportnumber->recursive = 0;

            $orderKat = null;
            $projectID = null;
            $orderID = null;
            $message = null;
            $reportnumber = array();
            $number = array();
            $termNum = array();

            // Wenn der Term eine Nummer ist, wird versucht eine Protok0llnummer zufinden
            if (is_numeric($this->request->data['Order']['search'])) {
                $options = array('conditions' => array(
                                                    'Reportnumber.number' => $this->request->data['Order']['search'],
                                                    'Reportnumber.topproject_id' => $this->request->projectID
                                                )
                                            );
                $reportnumber[0] = $this->Reportnumber->find('first', $options);
            }
            // Wenn der Term keine Nummer ist
            else {
                // Wird erst geschaut ob der Term aus der Autocomplett kommt
                // Stimmt das Suchmuster wird der Prüfbericht gesuchr
                $term = explode('-', $this->request->data['Order']['search']);

                if (is_numeric(trim($term[0]))) {
                    $number[0] = $term[0];
                    $termNum = explode(' ', $term[1]);
                }
                if (isset($termNum[0]) && is_numeric(trim($termNum[0]))) {
                    $number[1] = $termNum[0];
                }
                if (count($number) == 2) {
                    $options = array('conditions' => array(
                                                        'Reportnumber.number' => $number[1],
                                                        'Reportnumber.year' => $number[0],
                                                        'Reportnumber.topproject_id' => $this->request->projectID
                                                    )
                                                );
                    $reportnumber[0] = $this->Reportnumber->find('first', $options);
                } else {
                    $menue = $this->Navigation->NaviMenue(null);
                    $breads = $this->Navigation->Breads(null);
                    $message = '<p class="error">'. __('Please enter the searched report`s running number', true) .'</p>';

                    $this->set('message', $message);
                    $this->set('reportnumbers', $reportnumber);
                    $this->set('orderKat', $orderKat);
                    $this->set('projectID', $projectID);
                    $this->set('orderID', $orderID);
                    $this->set('menues', $menue);
                    $this->set('breads', $breads);

                    return;
                }
            }

            if ($this->Reportnumber->find('count', $options) > 0) {
                // die Variablen für den View
                if ($reportnumber[0]['Order']['revision'] == 1) {
                    $orderKat = 2;
                } elseif ($reportnumber[0]['Order']['laufender_betrieb'] == 1) {
                    $orderKat = 1;
                }

                $this->Session->write('orderKat', $orderKat);
                $projectID = $reportnumber[0]['Reportnumber']['topproject_id'];
                $orderID = $reportnumber[0]['Reportnumber']['order_id'];
            }
        }

        $menue = $this->Navigation->NaviMenue(null);
        $breads = $this->Navigation->Breads(null);

        $this->set('message', $message);
        $this->set('reportnumbers', $reportnumber);
        $this->set('orderKat', $orderKat);
        $this->set('projectID', $projectID);
        $this->set('orderID', $orderID);
        $this->set('menues', $menue);
        $this->set('breads', $breads);
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
        $this->layout = 'modal';

        $projectID = $this->request->projectvars['VarsArray'][0];
        $equipmentType = $this->request->projectvars['VarsArray'][1];
        $equipment = $this->request->projectvars['VarsArray'][2];
        $orderID = $this->request->projectvars['VarsArray'][3];
        $id = $this->request->projectvars['VarsArray'][3];

        $this->loadModel('Equipment');
        $this->Equipment->recursive = 2;
        $optionsEquipment = array('conditions' => array('Equipment.id' => $equipment));
        $equipments = $this->Equipment->find('first', $optionsEquipment);

        if (!$this->Order->exists($id)) {
            throw new NotFoundException(__('Invalid order'));
        }

        $this->Session->write('orderURL', $id);
        $options = array('conditions' => array('Order.' . $this->Order->primaryKey => $id));
        //		$this->Order->recursive = 2;
        $orders = $this->Order->find('first', $options);

        $headline = $equipments['EquipmentType']['Topproject']['projektname'].' > '.$equipments['EquipmentType']['discription'].' > '.$equipments['Equipment']['discription'].' > '.$orders['Deliverynumber']['topproject_id'].$orders['Deliverynumber']['equipment_id'].$orders['Deliverynumber']['equipment_type_id'].$orders['Deliverynumber']['order_id'];

        $options = array(
            'conditions' => array(
                                'Reportnumber.topproject_id' => $projectID,
                                'Reportnumber.order_id' => $id
                            )
                        );

        $optionsPaginate['Reportnumber.order_id'] = $id;
        $optionsPaginate['Reportnumber.topproject_id'] = $projectID;

        $this->paginate = array(
            'conditions' => array($optionsPaginate),
            'order' => array('id' => 'desc'),
            'limit' => 25
        );

        $this->Order->Reportnumber->recursive = 0;

        $testingreports = $this->paginate('Reportnumber');

        $this->request->data = $orders;
        $arrayData = $this->Xml->DatafromXml('Order', 'file', null);

        $SettingsArray = array();
        $SettingsArray['addlink'] = array('discription' => __('Add', true), 'controller' => 'orders','action' => 'add', 'terms' => $this->request->projectvars['VarsArray']);
        $SettingsArray['upload'] = array('discription' => __('Upload new order', true), 'controller' => 'orders','action' => 'create', 'terms' => $this->request->projectvars['VarsArray']);
        $SettingsArray['editlink'] = array('discription' => __('Edit', true), 'controller' => 'orders','action' => 'edit', 'terms' => $this->request->projectvars['VarsArray']);
        $SettingsArray['addsearching'] = array('discription' => __('Searching for order', true), 'controller' => 'orders','action' => 'search', 'terms' => $this->request->projectvars['VarsArray']);

        $this->set('headline', $headline);
        $this->set('SettingsArray', $SettingsArray);
        $this->set('testingreports', $testingreports);
        $this->set('settings', $arrayData['settings']);
        $this->set('locale', $this->Lang->Discription());
        $this->set('orders', $orders);
    }

    public function select()
    {
        $this->set('select', array(1 => 'Allgemeiner Auftrag'));
        $this->render(null, 'modal');
    }

    public function create()
    {
        $projectID = $this->request->projectvars['VarsArray'][0];
        $equipmentType = $this->request->projectvars['VarsArray'][1];
        $equipment = $this->request->projectvars['VarsArray'][2];

        $this->layout = 'modal';

        if ($this->Session->check('testedCsvArray')) {
            $testedCsvArray = $this->Session->read('testedCsvArray');

            foreach ($testedCsvArray['Order'] as $_key => $_order) {
                pr($_order);
                $this->Order->create();

                $_order['topproject_id'] = $projectID;
                $_order['testingcomp_id'] = $this->Auth->user('testingcomp_id');

                if ($this->Order->save($_order)) {
                    $this->loadModel('Deliverynumber');
                    $this->request->data['Deliverynumber']['topproject_id'] = $this->request->projectvars['VarsArray'][0];
                    $this->request->data['Deliverynumber']['equipment_type_id'] = $this->request->projectvars['VarsArray'][1];
                    $this->request->data['Deliverynumber']['equipment_id'] = $this->request->projectvars['VarsArray'][2];
                    $this->request->data['Deliverynumber']['order_id'] = $this->Order->getInsertID();
                    $this->request->data['Deliverynumber']['testingcomp_id'] = $this->Auth->user('testingcomp_id');
                    $this->Deliverynumber->create();
                    $this->Deliverynumber->save($this->request->data['Deliverynumber']);

                    $deliverynumber['Order']['id'] = $this->Order->getInsertID();
                    $deliverynumber['Order']['equipment_type_id'] = $equipmentType;
                    $deliverynumber['Order']['equipment_id'] = $equipment;
                    $deliverynumber['Order']['deliverynumber'] = $this->request->projectvars['VarsArray'][0].$this->request->projectvars['VarsArray'][2].$this->request->projectvars['VarsArray'][1].$this->Order->getInsertID();
                    $this->Order->save($deliverynumber);

                    $this->Autorisierung->Logger($this->Order->getInsertID(), $_order);
                    $this->Session->setFlash(__('The order has been saved'));

                    $FormName['controller'] = 'equipments';
                    $FormName['action'] = 'view';
                    $Terms =
                        $projectID.'/'.
                        $equipmentType.'/'.
                        $equipment;

                    $FormName['terms'] = $Terms;

                    $this->set('FormName', $FormName);
                    $this->set('saveOK', 1);
                    $this->set('reportnumberID', null);
                    $this->set('evalutionID', null);
                } else {
                    $this->Session->setFlash(__('The order could not be saved. Please, try again.'));
                }
            }

            /*


                        if ($this->Order->save($this->Session->read('testedCsvArray'))) {

                            $this->loadModel('Deliverynumber');
                            $this->request->data['Deliverynumber']['topproject_id'] = $this->request->projectvars['VarsArray'][0];
                            $this->request->data['Deliverynumber']['equipment_type_id'] = $this->request->projectvars['VarsArray'][1];
                            $this->request->data['Deliverynumber']['equipment_id'] = $this->request->projectvars['VarsArray'][2];
                            $this->request->data['Deliverynumber']['order_id'] = $this->Order->getInsertID();
                            $this->request->data['Deliverynumber']['testingcomp_id'] = $this->Auth->user('testingcomp_id');
            //				$this->request->data['Deliverynumbers']['number'];
                            $this->Deliverynumber->create();
                            $this->Deliverynumber->save($this->request->data['Deliverynumber']);

                            // Die Dekranummer wird zu Suchzwecken mit eingefügt
                            $deliverynumber['Order']['id'] = $this->Order->getInsertID();
                            $deliverynumber['Order']['equipment_type_id'] = $equipmentType;
                            $deliverynumber['Order']['equipment_id'] = $equipment;
                            $deliverynumber['Order']['deliverynumber'] = $this->request->projectvars['VarsArray'][0].$this->request->projectvars['VarsArray'][2].$this->request->projectvars['VarsArray'][1].$this->Order->getInsertID();
                            $this->Order->save($deliverynumber);

                            $this->Session->write('orderURL', $this->Order->getInsertID());
                            $this->Autorisierung->Logger($this->Order->getInsertID(),$this->request->data['Order']);
                            $this->Session->setFlash(__('The order has been saved'));

                            $FormName['controller'] = 'equipments';
                            $FormName['action'] = 'view';
                            $Terms =
                                $projectID.'/'.
                                $equipmentType.'/'.
                                $equipment;

                            $FormName['terms'] = $Terms;

                            $this->set('FormName',$FormName);
                            $this->set('saveOK', 1);
                            $this->set('reportnumberID', null);
                            $this->set('evalutionID', null);
                        } else {
                            $this->Session->setFlash(__('The order could not be saved. Please, try again.'));
                        }
            */
            $this->Session->delete('testedCsvArray');
        }

        if (isset($this->request->data['Order']['file'])) {
            $mimetype = $this->request->data['Order']['file']['type'];

            switch ($mimetype) {
                case 'application/pdf':
                    $this->loadModel('OrderPdf');

                    $text = $this->OrderPdf->getText($this->request->data['Order']['file']['tmp_name']);
                    break;
                case 'application/vnd.ms-excel':

                    $CsvArray = $this->Csv->getUploadExcelCsv($this->request->data['Order']['file']['tmp_name']);

                    if (count($CsvArray) > 100) {
                        break;
                    }

                    if (isset($CsvArray) && count($CsvArray) > 0) {
                        $this->Session->write('uploadetCsvArray', $CsvArray);
                    }
                    break;
            }
        }


        if (isset($this->request->data['show_data'])) {
            $arrayData = $this->Xml->DatafromXml($projectID, 'file', null, 'orders' . DS . 'templates' . DS . 'upload' . DS);

            if ($this->Session->check('uploadetCsvArray')) {
                $uploadetCsvArray = $this->Session->read('uploadetCsvArray');
                $head = array_shift($uploadetCsvArray);
                // Leerzeichen entfernen
                // damit die Werte mit denen aus der XML übereinstimmen
                $orders = array();
                $deleted_orders = 0;

                foreach ($head as $_key => $_head) {
                    $head[$_key] = trim(str_replace(' ', '', $_head));
                }
                foreach ($uploadetCsvArray as $_key => $_uploadetCsvArray) {
                    $this->Order->recursive = -1;
                    $existOrder = $this->Order->find('first', array('conditions' => array('Order.auftrags_nr' => $_uploadetCsvArray['auftrags_nr'])));
                    if (count($existOrder) > 0) {
                        unset($orders['Order'][$_key]);
                        $deleted_orders++;
                    }

                    $orders['Order'][$_key] = $_uploadetCsvArray;
                }

                $orders_kompakt = array();

                foreach ($orders['Order'] as $_key => $_orders) {
                    //					$orders_kompakt[$_orders['auftrags_nr']] = $_orders;
                    foreach ($_orders as $__key => $__orders) {
                        if ($__orders != '') {
                            @$orders_kompakt['Order'][$_orders['auftrags_nr']][$__key] = $orders_kompakt['Order'][$_orders['auftrags_nr']][$__key] . '; ' . $__orders;
                        }
                    }
                }


                $this->set('deleted_orders', $deleted_orders);
                $this->set('orders', $orders);
                $this->set('arrayData', $arrayData);
                $this->set('uploadetCsvArray', array('head' => $head,'body' => $uploadetCsvArray));
                $this->Session->delete('uploadetCsvArray');
                $this->Session->write('testedCsvArray', $orders);
            } else {
                $this->Session->setFlash(__('The uploaded file is not compatible.'));
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
        $locale = $this->Lang->Discription();
        $projectID = $this->request->projectvars['VarsArray'][0];
        $cascadeID = $this->request->projectvars['VarsArray'][1];
        $variantId = $this->request->projectvars['VarsArray'][2];
        $this->loadmodel('Technicalplacevariant');
        $tpvariant = $this->Technicalplacevariant->find('first', array('conditions'=>array('Technicalplacevariant.id'=>$variantId)));

        // Daten einlesen
        $this->set('tpvariant', $tpvariant['Technicalplacevariant']);
        $arrayData = $this->Xml->DatafromXml('Technicalplace'.$tpvariant['Technicalplacevariant']['value'], 'file', 'TechnicalPlace');


        $this->loadModel('Cascade');
        $this->loadModel('Testingcomp');


        $optionsCascade = array('conditions' => array('Cascade.id' => $cascadeID));
        $cascade = $this->Cascade->find('first', $optionsCascade);

        if (isset($this->request->data['Technicalplace']) && ($this->request->is('post') || $this->request->is('put'))) {
            $this->Technicalplace->set($this->request->data);


            if ($this->Technicalplace->validates()) {
            } else {
                $errors = $this->Technicalplace->validationErrors;
            }

            $this->Technicalplace->create();


            $this->request->data['Technicalplace']['user_id'] = $this->Auth->user('id');
            $this->request->data['Technicalplace']['technicalplace_variant_id'] = $variantId;
            $this->request->data['Technicalplace']['topproject_id'] = $projectID;
            $this->request->data['Technicalplace']['cascade_id'] = $cascadeID;
            $this->request->data['Technicalplace']['testingcomp_id'] = $this->Auth->user('testingcomp_id');

            if (empty($tpvariant)) {
                $this->Session->setFlash(__('Das Equipment konnte nicht gespeichert werden, wählen Sie eine Kategorie'));
            }

            $this->request->data = $this->Data->ChangeDropdownData($this->request->data, $arrayData, 'Technicalplace');

            if ($this->Technicalplace->save($this->request->data)) {
                $this->request->data['Technicalplace']['user_id'] = $this->Auth->user('id');
                $this->request->data['Technicalplace']['testingcomp_id'] = $this->Auth->user('testingcomp_id');
                $this->Session->setFlash(__('The equipment has been saved'));
                $this->Autorisierung->Logger($this->Technicalplace->getLastInsertID(), $this->request->data['Technicalplace']);
                $last_id = $this->Technicalplace->getLastInsertID();
                $tp = $this->Technicalplace->find('first', array('conditions' => array('Technicalplace.id' => $last_id)));
                $this->request->projectvars['VarsArray'][16] = $tp['Technicalplace']['id'];

                $FormName['controller'] = 'technicalplaces';
                $FormName['action'] = 'index';
                $Terms = implode('/', $this->request->projectvars['VarsArray']);
                $FormName['terms'] = $Terms;
                $this->set('FormName', $FormName);
            } else {
                $this->Session->setFlash(__('Das Equipment konnte nicht gespeichert werden, wählen Sie eine Kategorie'));
            }
        }

        $this->loadModel('Testingcomp');
        $this->Testingcomp->recursive = -1;
        $testingcomps = $this->Testingcomp->find(
            'list',
            array(
                                                'fields' => array('id','firmenname'),
                                                'conditions' => array(
                                                    'Testingcomp.id' => $this->Auth->user('testingcomp_id')
                                                    )
                                                )
        );

        if (!isset($this->request->data['Technicalplace'])) {
            foreach ($arrayData['settings']->Technicalplace->children() as $_key => $_settings) {
                $this->request->data[trim($_settings->model)][trim($_settings->key)] = null;
            }
        }


        $this->request->data = $this->Data->DropdownData(array('Technicalplace'), $arrayData, $this->request->data);

        //$this->request->data['Technicalplace']['active'] = 1;

        $SettingsArray = array();


        $this->set('SettingsArray', $SettingsArray);
        $this->set('settings', $arrayData['headoutput']);
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
        $this->layout = 'modal';
        $locale = $this->Lang->Discription();
        $projectID = $this->request->projectvars['VarsArray'][0];
        $cascadeID = $this->request->projectvars['VarsArray'][1];
        $variantId = $this->request->projectvars['VarsArray'][2];
        $tpid = $this->request->projectvars['VarsArray'][16];
        $this->loadmodel('Technicalplacevariant');
        $tpvariant = $this->Technicalplacevariant->find('first', array('conditions'=>array('Technicalplacevariant.id'=>$variantId)));

        // Daten einlesen
        $this->set('tpvariant', $tpvariant['Technicalplacevariant']);
        $arrayData = $this->Xml->DatafromXml('Technicalplace'.$tpvariant['Technicalplacevariant']['value'], 'file', 'TechnicalPlace');


        $this->loadModel('Cascade');
        $this->loadModel('Testingcomp');


        $optionsCascade = array('conditions' => array('Cascade.id' => $cascadeID));
        $cascade = $this->Cascade->find('first', $optionsCascade);



        if (isset($this->request->data['Technicalplace']) && ($this->request->is('post') || $this->request->is('put'))) {
            $this->Technicalplace->set($this->request->data);
            if ($this->Technicalplace->validates()) {
            } else {
                $errors = $this->Technicalplace->validationErrors;
            }


            $this->request->data['Technicalplace']['user_id'] = $this->Auth->user('id');
            $this->request->data['Technicalplace']['technicalplace_variant_id'] = $variantId;
            $this->request->data['Technicalplace']['topproject_id'] = $projectID;
            $this->request->data['Technicalplace']['cascade_id'] = $cascadeID;
            $this->request->data['Technicalplace']['testingcomp_id'] = $this->Auth->user('testingcompid');
            if (empty($tpvariant)) {
                $this->Session->setFlash(__('Das Equipment konnte nicht gespeichert werden, w�hlen Sie eine Kategorie'));
            }

            $this->request->data = $this->Data->ChangeDropdownData($this->request->data, $arrayData, 'Technicalplace');
            if ($this->Technicalplace->save($this->request->data)) {
                $this->request->data['Technicalplace']['user_id'] = $this->Auth->user('id');
                $this->request->data['Technicalplace']['testingcomp_id'] = $this->Auth->user('testingcomp_id');
                $this->Session->setFlash(__('The equipment has been saved'));
                $this->Autorisierung->Logger($this->Technicalplace->getLastInsertID(), $this->request->data['Technicalplace']);
                $last_id = $this->Technicalplace->getLastInsertID();

                $tp = $this->Technicalplace->find('first', array('conditions' => array('Technicalplace.id' => $tpid)));



                $this->request->projectvars['VarsArray'][16] = $tp['Technicalplace']['id'];

                $FormName['controller'] = 'devices';
                $FormName['action'] = 'view';
                $FormName['terms'] = implode('/', $this->request->projectvars['VarsArray']);

                $this->set('FormName', $FormName);
                $this->set('saveOK', 1);
            } else {
                $this->Session->setFlash(__('Das equipment konnte nicht gespeichert werden, w�hlen Sie eine Kategorie'));
            }
        }
        $this->request->data = $this->Technicalplace->find('first', array('conditions'=>array('Technicalplace.id'=>$tpid)));
        $this->loadModel('Testingcomp');
        $this->Testingcomp->recursive = -1;
        $testingcomps = $this->Testingcomp->find(
            'list',
            array(
                                                'fields' => array('id','firmenname'),
                                                'conditions' => array(
                                                    'Testingcomp.id' => $this->Auth->user('testingcomp_id')
                                                    )
                                                )
        );

        if (!isset($this->request->data['Technicalplace'])) {
            foreach ($arrayData['settings']->Technicalplace->children() as $_key => $_settings) {
                $this->request->data[trim($_settings->model)][trim($_settings->key)] = null;
            }
        }


        $this->request->data = $this->Data->DropdownData(array('Technicalplace'), $arrayData, $this->request->data);

        //$this->request->data['Technicalplace']['active'] = 1;

        $SettingsArray = array();

        $SettingsArray['dellink'] = array('discription' => __('Delete this technical place', true), 'controller' => 'technicalplaces','action' => 'delete', 'terms' => $this->request->projectvars['VarsArray']);

        $this->set('SettingsArray', $SettingsArray);
        $this->set('settings', $arrayData['headoutput']);
    }


    public function delete($id = null)
    {
        $this->layout = 'modal';

        $locale = $this->Lang->Discription();
        $projectID = $this->request->projectvars['VarsArray'][0];
        $cascadeID = $this->request->projectvars['VarsArray'][1];
        $variantID = $this->request->projectvars['VarsArray'][2];
        $tpid = $this->request->projectvars['VarsArray'][16];



        $this->Technicalplace->id = $tpid;
        if (!$this->Technicalplace->exists()) {
            throw new NotFoundException(__('Invalid technical place'));
        }

        $options = array('conditions' => array('Technicalplace.id' => $tpid));
        $this->Technicalplace->recursive = 0;
        $tp = $this->Technicalplace->find('first', $options);
        $tp['Technicalplace']['deleted'] = 1;
        $this->request->data = $tp;

        $headline = $tp['Technicalplace']['description'];
        $SettingsArray = array();

        $this->set('headline', $headline);
        $this->set('SettingsArray', null);
        $this->set('message', __('Will you delete this technical place?', true));
        $this->set('SettingsArray', $SettingsArray);


        if (isset($this->request->data['Technicalplace']) && $this->request->is('put')) {
            if ($this->Technicalplace->save($this->request->data)) {
                $this->Session->setFlash(__('The technical place has been delete.'));
                $logArray = array('delete' => 'Technischer Platz gel�scht');


                //$this->Autorisierung->Logger($tpid,$logArray);

                $FormName = array('controller' => 'technicalplaces', 'action' => 'index', 'terms' => $projectID.'/'.$cascadeID.'/'.$variantID);

                $this->set('FormName', $FormName);
                $this->set('afterChange', 1);
            } else {
                $this->Session->setFlash(__('Technical place was not deleted.'));
                $logArray = array('delete' => 'Fehler beim löschen');
                //$this->Autorisierung->Logger($id,$logArray);
            }
        }
    }
}
