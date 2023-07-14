<?php

App::uses('Folder', 'Utility');
App::uses('File', 'Utility');

class DataComponent extends Component
{
    protected $_controller = null;

    public function initialize(Controller $controller)
    {
        $this->_controller = $controller;
    }

    // wird ersetzt durch SavaData Componente
    public function SanitizeInput($data)
    {
        //		$data = Sanitize::html($data, array('remove' => true));
        $data = html_entity_decode($data);

        return $data;
    }

    public function CheckDate()
    {
        $ReportModel = key($this->_controller->request->data);
        $Field = key($this->_controller->request->data[$ReportModel]);
        $Value = $this->_controller->request->data[$ReportModel][$Field];
        $Lang = $this->_controller->request->lang;
        $Schema = $this->_controller->$ReportModel->schema($Field);

        // Quick and Diry
        // Muss in der Datenbank korrigiert werden
        if ($Schema['type'] != 'date') {
            return true;
        }

        if (Configure::check('Dateformat') === false) {
            $Test = $this->__StandardCheckDate($Value);

            if ($Test === true) {
                return true;
            } else {
                return false;
            }
        }

        $Dateformat = Configure::read('Dateformat');

        if (isset($Dateformat[$Lang])) {
            switch ($Lang) {
                case 'deu':
                    $Test = $this->__GermanCheckDate($Value);
                    if ($Test === true) {
                        return true;
                    } else {
                        return false;
                    }
                    break;

                case 'eng':
                    $Test = $this->__StandardCheckDate($Value);
                    if ($Test === true) {
                        return true;
                    } else {
                        return false;
                    }
                    break;

                default:
                    $Test = $this->__StandardCheckDate($Value);
                    if ($Test === true) {
                        return true;
                    } else {
                        return false;
                    }
                    break;
            }
        } else {
            $Test = $this->__StandardCheckDate($Value);

            if ($Test === true) {
                return true;
            } else {
                return false;
            }
        }

        return false;
    }

    protected function __StandardCheckDate($Value)
    {
        $DateArray = explode('-', $Value);

        if (count($DateArray) == 1) {
            if (empty($DateArray[0])) {
                return true;
            }
        }

        if (isset($DateArray[0]) && isset($DateArray[1]) && isset($DateArray[2])) {
            $Test = checkdate(intval($DateArray[1]), intval($DateArray[2]), intval($DateArray[0]));
        } else {
            $Test = false;
        }

        if ($Test === true) {
            return true;
        } else {
            return false;
        }
    }

    protected function __GermanCheckDate($Value)
    {
        $DateArray = explode('.', $Value);
        $Test = checkdate($DateArray[1], $DateArray[0], $DateArray[2]);

        if ($Test === true) {
            return true;
        } else {
            return false;
        }
    }

    public function Save()
    {
    }

    public function SortAllDropdownArraysNatural($data)
    {
        if (!isset($data)) {
            return $data;
        }
        if (!is_array($data)) {
            return $data;
        }
        if (count($data) == 0) {
            return $data;
        }
        if (!isset($data['Dropdowns'])) {
            return $data;
        }
        if (count($data['Dropdowns']) == 0) {
            return $data;
        }

        foreach ($data['Dropdowns'] as $key => $value) {
            foreach ($value as $_key => $_value) {
                natsort($data['Dropdowns'][$key][$_key]);
            }
        }

        return $data;
    }

    public function SortthisArray($data, $Model, $field, $desc, $type)
    {
        if (!is_array($data)) {
            return $data;
        }
        if (count($data) < 2) {
            return $data;
        }
        if (!isset($data)) {
            return $data;
        }
        if (empty($desc)) {
            return $data;
        }
        if (empty($type)) {
            return $data;
        }
        if (!isset($data[$Model])) {
            return $data;
        }
        if (count($data[$Model]) == 0) {
            return $data;
        }

        $data = Hash::sort($data, '{n}.' . $Model . '.' . $field, $desc, $type);

        return $data;
    }

    public function SortAllArrays($data, $Model, $desc, $type)
    {
        if (!isset($data)) {
            return $data;
        }
        if (!is_array($data)) {
            return $data;
        }
        if (count($data) < 2) {
            return $data;
        }
        if (empty($desc)) {
            return $data;
        }
        if (empty($type)) {
            return $data;
        }
        if (!isset($data[$Model])) {
            return $data;
        }
        if (count($data[$Model]) == 0) {
            return $data;
        }

        foreach ($data[$Model] as $key => $value) {
            foreach ($value as $_key => $_value) {
                $data[$Model][$key][$_key] = Hash::sort($data[$Model][$key][$_key], '{n}', $desc, $type);
            }
        }

        return $data;
    }

    public function ConditionsExaminer()
    {
 //       $options['Testingcomp.id'] = $this->_controller->Auth->user('testingcomp_id');
        $options['Examiner.deleted'] = 0;
        $options['Examiner.active'] = 1;

        if ($this->_controller->Session->check('ConditionsExaminer') === true) {
            $ConditionsExaminer = $this->_controller->Session->read('ConditionsExaminer');

            if (count($ConditionsExaminer) > 0) {
                $options = array();
                $options = $this->_controller->Session->read('ConditionsExaminer');
            }
        }

        if (isset($this->_controller->request->data['examiner_id']) && $this->_controller->request->data['examiner_id'] > 0) {
            $examiner_id = $this->_controller->Sicherheit->Numeric($this->request->data['examiner_id']);
            $options['Examiner.id'] = $examiner_id;
        }

        if (isset($this->_controller->request->data['Examiner']['active'])) {
            $options['Examiner.active'] = $this->_controller->request->data['Examiner']['active'];
        }

        if (isset($this->_controller->request->data['Examiner']['ExaminerTestingcomp']) && !empty($this->_controller->request->data['Examiner']['ExaminerTestingcomp'])) {

            $ExaminersTestingcomp = $this->_controller->Examiner->ExaminersTestingcomp->find('all',array(
                'conditions' => array(
                    'ExaminersTestingcomp.testingcomp_id' => $this->_controller->request->data['Examiner']['ExaminerTestingcomp']
                    )
                )
            );

            $ExaminersTestingcomp = Hash::extract($ExaminersTestingcomp, '{n}.ExaminersTestingcomp.examiner_id');

            $options['Examiner.id'] = $ExaminersTestingcomp;
        }   
        if (isset($this->_controller->request->data['Examiner']['ExaminerTestingcomp']) && empty($this->_controller->request->data['Examiner']['ExaminerTestingcomp'])) {
            unset($options['Examiner.id']);
        }


        $this->_controller->Session->write('ConditionsExaminer', $options);

        return $options;
    }

    public function SortDevicesTable()
    {
        $Model = 'Device';
        $order = array('Device.name' => 'asc');

        if (isset($this->_controller->request->query['is_infinite'])) {
            $order = array();
            $order = $this->_controller->Session->read('SortDeviceTable');
            return $order;
        }

        if (!isset($this->_controller->request->data['SortDevice'])) {
            if ($this->_controller->Session->check('SortDeviceTable') === true) {
                $SortExaminerTable = $this->_controller->Session->read('SortDeviceTable');

                if (count($SortExaminerTable) > 0) {
                    $order = array();
                    $order = $this->_controller->Session->read('SortDeviceTable');
                    return $order;
                }
            }

            $order = array();

            // Default Einstellungen
            $order[$Model . '.working_place'] = 'asc';
            $order[$Model . '.name'] = 'asc';
            //	$order[$Model . '.first_name'] = 'asc';

            if (Configure::check('SortDeviceTable') == true) {
                $order = array();
                $order = Configure::read('SortDeviceTable');
            }

            $this->_controller->set('order', $order);
            return $order;
        }


        if (count($this->_controller->request->data['SortDevice']) == 0) {
            $this->_controller->set('order', $order);
            return $order;
        }

        $Schema = $this->_controller->$Model->schema();

        $this->_controller->Session->delete('SortDeviceTable');

        $Sort = array(1 => 'asc', 2 => 'desc');

        $order = array();

        foreach ($this->_controller->request->data['SortDevice'] as $key => $value) {
            if (!isset($Schema[$key])) {
                continue;
            }
            if ($value == 0) {
                continue;
            }

            $order[$Model . '.' . $key] = $Sort[$value];
        }

        $this->_controller->Session->write('SortDeviceTable', $order);

        $this->_controller->set('order', $order);
        return $order;
    }

    public function SortExaminerTable()
    {
        $Model = 'Examiner';
        $order = array('Examiner.name' => 'asc','Examiner.first_name' => 'asc');

        if (isset($this->_controller->request->query['is_infinite'])) {
            $order = array();
            $order = $this->_controller->Session->read('SortExaminerTable');
            return $order;
        }

        if (!isset($this->_controller->request->data['Examiner'])) {
            if ($this->_controller->Session->check('SortExaminerTable') === true) {
                $SortExaminerTable = $this->_controller->Session->read('SortExaminerTable');

                if (count($SortExaminerTable) > 0) {
                    $order = array();
                    $order = $this->_controller->Session->read('SortExaminerTable');
                    return $order;
                }
            }

            $order = array();

            // Default Einstellungen
            $order[$Model . '.working_place'] = 'asc';
            $order[$Model . '.name'] = 'asc';
            $order[$Model . '.first_name'] = 'asc';

            if (Configure::check('SortExaminerTable') == true) {
                $order = array();
                $order = Configure::read('SortExaminerTable');
            }

            $this->_controller->set('order', $order);
            return $order;
        }

        if (isset($this->_controller->request->data['Examiner']['email'])) {
            $order = array();

            // Default Einstellungen
            $order[$Model . '.working_place'] = 'asc';
            $order[$Model . '.name'] = 'asc';
            $order[$Model . '.first_name'] = 'asc';

            if (Configure::check('SortExaminerTable') == true) {
                $order = array();
                $order = Configure::read('SortExaminerTable');
            }

            return $order;
        }

        if (count($this->_controller->request->data['Examiner']) == 0) {
            $this->_controller->set('order', $order);
            return $order;
        }

        $Model = 'Examiner';
        $Schema = $this->_controller->$Model->schema();

        $this->_controller->Session->delete('SortExaminerTable');

        $Sort = array(1 => 'asc', 2 => 'desc');

        $order = array();

        foreach ($this->_controller->request->data['Examiner'] as $key => $value) {
            if (!isset($Schema[$key])) {
                continue;
            }
            if ($value == 0) {
                continue;
            }

            $order[$Model . '.' . $key] = $Sort[$value];

            if ($key == 'name') {
                $order[$Model . '.first_name'] = $Sort[$value];
            }
        }

        $this->_controller->Session->write('SortExaminerTable', $order);

        $this->_controller->set('order', $order);
        return $order;
    }

    public function EnforceParentReportnumber($reportnumber)
    {
        $indices = range('a', 'z');

        if (is_array($reportnumber)) {
            if (!$this->_controller->Reportnumber->exists($reportnumber['Reportnumber']['id'])) {
                return $reportnumber;
            }
            if (empty($reportnumber['Reportnumber']['parent_id'])) {
                return $reportnumber;
            }

            $this->_controller->Reportnumber->recursive = -1;

            $parent = $this->_controller->Reportnumber->find('first', array('conditions'=>array('Reportnumber.id'=>$reportnumber['Reportnumber']['parent_id'])));
            $list = array_values($this->_controller->Reportnumber->find('list', array('fields'=>array('Reportnumber.id'), 'conditions'=>array('Reportnumber.parent_id'=>$reportnumber['Reportnumber']['parent_id']))));

            if (!empty($parent['Reportnumber']['enforce_number'])) {
                $reportnumber['Reportnumber']['_number'] = $parent['Reportnumber']['number'].(isset($indices[$reportnumber['Reportnumber']['subindex']]) ? $indices[$reportnumber['Reportnumber']['subindex']] : ('-'.$reportnumber['Reportnumber']['subindex'] + 1));
            }
        } elseif (is_object($reportnumber)) {
            if (!$this->_controller->Reportnumber->exists(intval($reportnumber->Reportnumber->id))) {
                return $reportnumber;
            }
            if (intval($reportnumber->Reportnumber->parent_id)==0) {
                return $reportnumber;
            }

            $parent = $this->_controller->Reportnumber->find('first', array('conditions'=>array('Reportnumber.id'=>intval($reportnumber->Reportnumber->parent_id))));
            $list = array_values($this->_controller->Reportnumber->find('list', array('fields'=>array('Reportnumber.id'), 'conditions'=>array('Reportnumber.parent_id'=>intval($reportnumber->Reportnumber->parent_id)))));

            if (!empty($parent['Reportnumber']['enforce_number'])) {
                $reportnumber->Reportnumber->_number = $parent['Reportnumber']['number'].(isset($indices[$reportnumber->Reportnumber->subindex]) ? $indices[$reportnumber->Reportnumber->subindex] : ('-'.$reportnumber->Reportnumber->subindex + 1));
            }
        }
        return $reportnumber;
    }

    public function EquipmentTypesIndex()
    {
        $this->_controller->layout = 'modal';
        $this->_controller->EquipmentType->recursive = 0;
        $breads = $this->_controller->Navigation->Breads(null);

        $options['EquipmentType.topproject_id'] = $this->_controller->request->projectvars['VarsArray'][0];
        $options['EquipmentType.status'] = 0;

        $this->_controller->paginate = array(
            'conditions' => array($options),
            'order' => array('id' => 'desc'),
            'limit' => 25
        );

        $equipmentTypes = $this->_controller->paginate('EquipmentType');

        $SettingsArray = array();
        $SettingsArray['addlink'] = array('discription' => __('Add new equipment type', true), 'controller' => 'equipmenttypes','action' => 'add', 'terms' => $this->_controller->request->projectvars['VarsArray']);

        $FormName = array('controller' => 'equipmenttypes', 'action' => 'overview');
        $FormName['terms'] = $this->_controller->request->projectvars['VarsArray'][0];

        $this->_controller->set('reportnumberID', 1);
        $this->_controller->set('evalutionID', 0);
        $this->_controller->set('FormName', $FormName);

        $this->_controller->set('SettingsArray', $SettingsArray);
        $this->_controller->set('breads', $breads);
        $this->_controller->set('equipmentTypes', $equipmentTypes);
        $this->_controller->render('index');
    }

    public function EquipmentIndex()
    {
        $this->_controller->layout = 'modal';
        //		$this->_controller->Equipment->recursive = 0;
        $breads = $this->_controller->Navigation->Breads(null);

        $options['Equipment.topproject_id'] = $this->_controller->request->projectvars['VarsArray'][0];
        $options['Equipment.equipment_type_id'] = $this->_controller->request->projectvars['VarsArray'][1];
        $options['Equipment.status'] = 0;

        $this->_controller->paginate = array(
            'conditions' => array($options),
            'order' => array('discription' => 'asc'),
            'limit' => 25
        );

        $equipments = $this->_controller->paginate('Equipment');

        $SettingsArray = array();
        $SettingsArray['addlink'] = array('discription' => __('Add new equipment', true), 'controller' => 'equipments','action' => 'add', 'terms' => $this->_controller->request->projectvars['VarsArray']);

        $this->_controller->loadModel('EquipmentType');
        $options = array('conditions' => array('EquipmentType.' . $this->_controller->EquipmentType->primaryKey => $this->_controller->request->projectvars['VarsArray'][1]));
        $equipmentType = $this->_controller->EquipmentType->find('first', $options);

        $FormName = array('controller' => 'equipmenttypes', 'action' => 'view');
        $FormName['terms'] = $this->_controller->request->projectvars['VarsArray'][0].'/'.$this->_controller->request->projectvars['VarsArray'][1];

        $this->_controller->set('reportnumberID', 1);
        $this->_controller->set('evalutionID', 0);
        $this->_controller->set('FormName', $FormName);

        $this->_controller->set('Headline', $equipmentType['Topproject']['projektname'] . ' / ' . $equipmentType['EquipmentType']['discription']);
        $this->_controller->set('SettingsArray', $SettingsArray);
        $this->_controller->set('breads', $breads);
        $this->_controller->set('equipment', $equipments);
        $this->_controller->render('index');
    }

    public function SearchHasValues($value)
    {
        if (count($value) == 0) {
            return false;
        }

        $hasValues = array_reduce(
            $value,
            function ($prev, $curr) {
                $curr = array_filter($curr);
                return $prev || !empty($curr);
            }
        );

        if ($hasValues) {
            return true;
        } else {
            return false;
        }
    }

    public function ReportNumberCorrection($reportnumber)
    {
        $numberArray = explode('-', $reportnumber);

        if (count($numberArray) == 0) {
            return false;
        }
        if (count($numberArray) == 1) {
            $this->_controller->set('SearchReportnumber', $reportnumber);
            return $numberArray[0];
        }
        if (count($numberArray) == 2) {
            $this->_controller->set('SearchReportnumber', $reportnumber);
            return $numberArray[1];
        }
    }

    public function SearchAdditional($testingmethods, $foundReports)
    {
        $thisModelIDs['IDs'] = array();
        $thisModelIDs['autocomplete'] = array();

        // Spezifische Suchfunktione
        // Die gesuchten Prüfverfahren werden durchlaufen
        // und mit den Vorhandenen verglichen
        // Die benötigten Models werden geladen

        $thisModel = null;
        $thisModelResults = array();

        $thisModeloptions['autocomplete'] = array();
        $thisModeloptions['dropdowns'] = array();
        $thisAutocompleteIDs = array();
        $thisDropdownIDs = array();

        $this->_controller->request->data['Reportnumber']['number'] = ($this->_controller->Data->ReportNumberCorrection($this->_controller->request->data['Reportnumber']['number']));

        foreach ($testingmethods as $_key => $_testingmethods) {
            foreach ($foundReports as $__key => $__foundReports) {
                if ($_testingmethods['Testingmethod']['id'] == $__foundReports) {
                    // für die Autocompletefelder
                    if (isset($this->_controller->request->data['Reports'])) {
                        foreach ($this->_controller->request->data['Reports'] as $___key => $___search) {
                            $thisModel = 'Report'.ucfirst($_testingmethods['Testingmethod']['value']).$___key;

                            if ($this->_controller->modelExists($thisModel)) {
                                $thisModeloptions['autocomplete'][$thisModel] = array();

                                foreach ($___search as $____key => $____search) {
                                    $laenge = strlen($____search);
                                    if (trim($laenge) > 0) {
                                        $this->_controller->loadModel($thisModel);
                                        if ($this->_controller->$thisModel->hasField($____key)) {
                                            $thisModeloptions['autocomplete'][$thisModel][$____key] = $____search;
                                            //											$thisModeloptions['autocomplete'][$thisModel]['topproject_id'] = $this->_controller->Session->read('projectURL');
                                        }
                                    }
                                }
                            }
                        }
                    }
                }
            }
        }

        // für die Dropdownfelder
        foreach ($this->_controller->request->data['Reportnumber'] as $_key => $_search) {
            if ($_search != null) {
                $_thisModeloptions = array(
                    $_key => $_search,'topproject_id' => $this->_controller->request->projectID
                );

                $thisModelResults = $this->_controller->Reportnumber->find('list', array('fields' => array('id'), 'conditions' => $_thisModeloptions));

                if (count($thisModelResults) > 0) {
                    foreach ($thisModelResults as $_thisModelResults) {
                        $thisDropdownIDs[$_key][$_thisModelResults] = $_thisModelResults;
                    }
                }
            }
        }

        foreach ($thisModeloptions['autocomplete'] as $_key => $_thisModeloptions) {
            if (count($_thisModeloptions) > 0) {
                $thisModelResults = $this->_controller->$_key->find('list', array('fields' => array('reportnumber_id'), 'conditions' => $_thisModeloptions));
                if (count($thisModelResults) > 0) {
                    foreach ($thisModelResults as $_thisModelResults) {
                        $thisAutocompleteIDs['fake'][$_thisModelResults] = $_thisModelResults;
                    }
                }
            }
        }

        $allIDs = array_merge($thisDropdownIDs, $thisAutocompleteIDs);

        // Werte die nicht in alle Unterarray vorkommen werden gelöscht

        foreach ($allIDs as $_key =>$_allIDs) {
            foreach ($_allIDs as $__key => $__allIDs) {
                foreach ($allIDs as $___key => $___allIDs) {
                    if (isset($allIDs[$___key][$__key])) {
                    } else {
                        unset($allIDs[$_key][$__key]);
                    }
                }
            }
        }
        foreach ($allIDs as $_key => $_allIDs) {
            foreach ($_allIDs as $__key => $__allIDs) {
                $thisModelIDs['IDs'][$__key] = $__allIDs;
            }
        }

        if (!isset($allIDs['fake']) && $this->_controller->Data->SearchHasValues($this->_controller->request->data['Reports']) == 1) {
            $thisModelIDs['IDs'] = 0;
        }

        // Falsche Projekte aussortieren
        if (is_array($thisModelIDs['IDs']) && count($thisModelIDs['IDs']) > 0) {
            foreach ($thisModelIDs['IDs'] as $_key => $_IDs) {
            }
        }

        return $thisModelIDs;
    }

    public function SearchAutocomplets($testingmethods, $fieldsArray)
    {
        $reportID = $this->_controller->Sicherheit->Numeric($this->_controller->request->params['pass'][0]);

        $thisModel = null;
        $thisAutocomplets = array();
        $thisAutocompletsJSON = array();

        if (!$this->_controller->modelExists('Reportnumber')) {
            $this->_controller->loadModel('Reportnumber');
        }

        $this->_controller->Reportnumber->recursive = -1;


        // Vorhandene Prüfverfahren werden durchlaufen
        foreach ($testingmethods as $_key => $_testingmethods) {
            // die im Formular vorhandenen Autcompletfelder werden durchlaufen
            foreach ($fieldsArray['autocompletes'] as $__key => $__fields) {
                $thisModel = 'Report'.ucfirst($_testingmethods['Testingmethod']['value']).$__fields['Model'];

                if (!$this->_controller->modelExists($thisModel)) {
                    continue;
                }
                $this->_controller->loadModel($thisModel);

                if (!$this->_controller->$thisModel->hasField($__fields['Key'])) {
                    continue;
                }

                // die Conditions für die Datenbankabfrage werden erstellt
                $thisConditions = array(
                    'fields' => array(
                        ' DISTINCT ' . $thisModel.'.'.$__fields['Key'],
                        'reportnumber_id'
                    ),
                    'conditions' => array(
                        $thisModel.'.'.$__fields['Key'] . ' != ' => ''
                    )
                );

                //echo '<pre>'; var_dump($thisConditions); echo '</pre>';

                // die gefundenen Werte werden durchlaufen
                foreach ($this->_controller->$thisModel->find('all', $thisConditions) as $_thisFinds) {
                    // und in ein Array geschrieben
                    $ProjectTest = $this->_controller->Reportnumber->find(
                        'count',
                        array(

                            'conditions' => array(
                                'id' => $_thisFinds[$thisModel]['reportnumber_id'],
                                'topproject_id' => $this->_controller->request->projectID
                            )
                        )
                    )
                    ;
                    if ($ProjectTest == 1) {
                        $thisAutocomplets[$__fields['Model'].'_'.$__fields['Key']][] = $_thisFinds[$thisModel][$__fields['Key']];
                    }
                }
                //echo '<pre>'; var_dump($thisAutocomplets); echo '</pre>';

                // doppelte Einträge werden gelöscht
                if (isset($thisAutocomplets[$__fields['Model'].'_'.$__fields['Key']]) && count($thisAutocomplets[$__fields['Model'].'_'.$__fields['Key']]) > 0) {
                    $thisAutocomplets[$__fields['Model'].'_'.$__fields['Key']] = array_unique($thisAutocomplets[$__fields['Model'].'_'.$__fields['Key']]);
                } else {
                    unset($thisAutocomplets[$__fields['Model'].'_'.$__fields['Key']]);
                }
            }
        }

        // Die Autocomplets werden in JSON und gleich in den View geschrieben

        foreach ($thisAutocomplets as $_key => $_thisAutocomplets) {
            $_thisAutocomplets = array_values($_thisAutocomplets);

            $NewKey = array_reduce(explode('_', $_key), function ($prev, $item) {
                return $prev.ucfirst($item);
            }, 'Reports');
            $thisAutocompletsJSON[$NewKey] = json_encode($_thisAutocomplets);
        }

        $this->_controller->set('thisAutocompletsJSON', $thisAutocompletsJSON);
        return $thisAutocomplets;
    }

    public function SearchUniversal($search)
    {
        $projectID = $this->_controller->request->projectvars['VarsArray'][0];
        $cascadeID = $this->_controller->request->projectvars['VarsArray'][1];
        $orderID = $this->_controller->request->projectvars['VarsArray'][2];
        $reportID = $this->_controller->request->projectvars['VarsArray'][3];
        $reportnumberID = $this->_controller->request->projectvars['VarsArray'][4];
        $evalId = $this->_controller->request->projectvars['VarsArray'][5];
        $weldedit = $this->_controller->request->projectvars['VarsArray'][6];

        $searchFields = $this->_controller->Xml->XmlToArray($search, 'file', null);

        $fields = array('autocompletes'=>array(), 'dropdowns'=>array());

        $thisModel = $this->_controller->modelClass;
        if ($thisModel == 'Topproject') {
            $thisModel = 'Order';
        }
        $testingreportsCount = 0;
        $limit = 15;

        if (!isset($this->_controller->request->params['named']['page'])) {
            /* Florian Zumpe 13.09.2017 - Area-Einträge noch aus der XML auslesen */
            foreach ($searchFields as $field) {
                if (trim($field->autocomplete) == 1) {
                    $add = array('Model'=>trim($field->model), 'Key'=>trim($field->key), 'Caption'=>trim($field->value), 'Break'=>trim($field->break), 'Area'=>trim($field->area));
                    if (isset($field->url)) {
                        $add = array_merge($add, array('urlController'=>trim($field->url->controller), 'urlAction' => trim($field->url->action)));
                    }
                    array_push(
                        $fields['autocompletes'],
                        $add
                    );
                } elseif (trim($field->model) != '') {
                    $fields['dropdowns'][trim($field->key)] = array(
                        'Model' => trim($field->model),
                        'OptionField' => trim($field->option),
                        'OutputField' => trim($field->output),
                        'Value' => $field->value,
                        'Break' => trim($field->break),
                        'Area' => trim($field->area)
                    );
                }
            }
            /* Florian Zumpe 13.09.2017 */

            $this->_controller->Data->SearchUniversalAutocomplets($fields);

            $thisModel = $this->_controller->modelClass;
            if ($thisModel == 'Topproject') {
                $thisModel = 'Order';
            }

            if ($thisModel == 'Testingcomp') {
                $this->_controller->loadModel('Testingcomp');
                $this->_controller->Testingcomp->recursive = -1;
                $testingcomps = $this->_controller->Testingcomp->find('list', array('fields'=>array('id', 'firmenname'), 'conditions'=>array('Testingcomp.id'=>$this->_controller->Autorisierung->ConditionsTestinccomps())));

                $fields['dropdowns']['testingcomp_id'] = array(
                    'Model' => 'Testingcomp',
                    'OptionField' => 'testingcomp_id',
                    'Result' => $testingcomps,
                    'Break' => '1'
                );
            } elseif ($thisModel == 'Order') {
                if (!isset($this->_controller->request->data[$thisModel]['topproject_id'])) {
                    $this->_controller->request->data[$thisModel]['topproject_id'] = $projectID;
                }

                $projectID = $this->_controller->Autorisierung->ConditionsTopprojects();

                $this->_controller->loadModel('Topproject');
                $this->_controller->Topproject->recursive = -1;

                $fields['dropdowns']['topproject_id'] = array(
                    'Model' => 'Order',
                    'OptionField' => 'topproject_id',
                    'Result' => $this->_controller->Topproject->find(
                        'list',
                        array(
                            'fields'=>array('id', 'projektname'),
                            'order'=>array('projektname' => 'ASC'),
                            'conditions'=>array($this->_controller->Topproject->primaryKey=>$projectID)
                        )
                    ),
                    'Break' => '1'
                );

                if (
                    isset($this->_controller->request['data'][$thisModel]['topproject_id']) &&
                    $this->_controller->request['data'][$thisModel]['topproject_id'] != ''
                ) {
                    if (!isset($this->_controller->request->data[$thisModel]['equipment_type_id'])) {
                        $this->_controller->request->data[$thisModel]['equipment_type_id'] = $equipmentTypeID;
                    }

                    $this->_controller->loadModel('EquipmentType');
                    $this->_controller->EquipmentType->recursive = -1;
                    $projectID = $this->_controller->Autorisierung->ConditionsTopprojects();

                    $options['topproject_id'] = $projectID;

                    if ((
                        isset($this->_controller->request->data[$thisModel]['topproject_id'])
                    ) &&
                        $this->_controller->request->data[$thisModel]['topproject_id'] != '' &&
                        $this->_controller->$thisModel->hasField('topproject_id')
                    ) {
                        $options['topproject_id'] = $this->_controller->Sicherheit->Numeric($this->_controller->request->data[$thisModel]['topproject_id']);
                    }

                    $Results = $this->_controller->EquipmentType->find(
                        'list',
                        array(
                            'fields'=>array('id', 'discription'),
                            'order'=>array('discription' => 'ASC'),
                            'conditions' => $options
                        )
                    );

                    if (count($Results) > 0) {
                        $fields['dropdowns']['equipment_type_id'] = array(
                            'Model' => 'Order',
                            'OptionField' => 'topproject_id',
                            'Result' => $Results,
                            'Break' => ''
                        );
                    }
                }

                if (
                    isset($this->_controller->request['data'][$thisModel]['topproject_id']) &&
                    $this->_controller->request['data'][$thisModel]['topproject_id'] != '' &&
                    isset($this->_controller->request['data'][$thisModel]['equipment_type_id']) &&
                    $this->_controller->request['data'][$thisModel]['equipment_type_id'] != ''
                ) {
                    if (!isset($this->_controller->request->data[$thisModel]['equipment_id'])) {
                        $this->_controller->request->data[$thisModel]['equipment_id'] = $equipmentID;
                    }

                    $this->_controller->loadModel('Equipment');
                    $this->_controller->Equipment->recursive = -1;
                    $projectID = $this->_controller->Autorisierung->ConditionsTopprojects();

                    $options['topproject_id'] = $projectID;

                    if ((
                        isset($this->_controller->request->data[$thisModel]['topproject_id'])
                    ) &&
                        $this->_controller->request->data[$thisModel]['topproject_id'] != '' &&
                        $this->_controller->$thisModel->hasField('topproject_id')) {
                        $options['topproject_id'] = $this->_controller->Sicherheit->Numeric($this->_controller->request->data[$thisModel]['topproject_id']);
                    }

                    if ((
                        isset($this->_controller->request->data[$thisModel]['equipment_type_id'])
                    ) &&
                        $this->_controller->request->data[$thisModel]['equipment_type_id'] != '' &&
                        $this->_controller->$thisModel->hasField('equipment_type_id')) {
                        $options['equipment_type_id'] = $this->_controller->Sicherheit->Numeric($this->_controller->request->data[$thisModel]['equipment_type_id']);
                    }

                    $Results = $this->_controller->Equipment->find(
                        'list',
                        array(
                            'fields'=>array('id', 'discription'),
                            'order'=>array('discription' => 'ASC'),
                            'conditions' => $options
                        )
                    );

                    if (count($Results) > 0) {
                        $fields['dropdowns']['equipment_id'] = array(
                            'Model' => 'Order',
                            'OptionField' => 'topproject_id',
                            'Result' => $Results,
                            'Break' => ''
                        );
                    }
                }
            }
        }
        if (isset($this->_controller->request->data[$thisModel]) || isset($this->_controller->request->params['named']['page'])) {
            $thisConditions = array();


            if (isset($this->_controller->request->data[$thisModel])) {
                if (isset($this->_controller->request->data[$thisModel])) {
                    foreach ($this->_controller->request->data[$thisModel] as $_key => $_request) {
                        if ($_request != null) {
                            if (!$this->_controller->$thisModel->hasField($_key)) {
                                continue;
                            }

                            $thisConditions['conditions'][$thisModel . '.' . $_key] = $_request;
                        }
                    }
                }

                if (isset($this->_controller->request->data['Time']['starttime']) && $this->_controller->request->data['Time']['starttime'] != null) {
                    $thisConditions['conditions'][$thisModel . '.modified >='] = $this->_controller->request->data['Time']['starttime']. ' 00:00:01';
                }

                if (isset($this->_controller->request->data['Time']['endtime']) && $this->_controller->request->data['Time']['endtime'] != null) {
                    $thisConditions['conditions'][$thisModel . '.modified <='] = $this->_controller->request->data['Time']['endtime']. ' 23:59:59';
                }

                if (isset($thisConditions['conditions']) && count($thisConditions['conditions']) > 0) {
                    if (!isset($thisConditions['conditions'][$thisModel . '.topproject_id'])) {
                        $thisConditions['conditions'][$thisModel . '.topproject_id'] = $projectID;
                    }
                    if ($this->_controller->$thisModel->hasField('deleted')) {
                        $thisConditions['conditions']['deleted'] = 0;
                    }

                    $this->_controller->set('testingreportsCount', $this->_controller->$thisModel->find('count', $thisConditions));
                }
            }
            if ((isset($this->_controller->request->data['showsearch']) && $this->_controller->request->data['showsearch'] == 1) || isset($this->_controller->request->params['named']['page'])) {
                if (!isset($thisConditions['conditions']) && isset($this->_controller->request->params['named']['page'])) {
                    $thisConditions = $this->_controller->Session->read('SearchthisConditions');
                } elseif (isset($thisConditions['conditions']) && !isset($this->_controller->request->params['named']['page'])) {
                    $this->_controller->Session->delete('SearchthisConditions');
                }

                $this->_controller->loadModel($thisModel);
                if ($this->_controller->$thisModel->hasField('deleted')) {
                    $thisConditions['conditions']['deleted'] = 0;
                }

                $this->_controller->paginate = array(
                    'conditions' => @$thisConditions['conditions'],
                    'limit' => $limit,
                    'order' => array('id' => 'desc')
                );

                $this->_controller->$thisModel->recursive = 2;
                $Result = $this->_controller->paginate($thisModel);
                $countOrder = $this->_controller->$thisModel->find(
                    'count',
                    array(
                    'conditions' => @$thisConditions['conditions']
                )
                );

                if ($countOrder > $limit && isset($thisConditions['conditions'])) {
                    $this->_controller->Session->write('SearchthisConditions', $thisConditions);
                }

                $SettingsArray = array();
                $SettingsArray['backlink'] = array('discription' => __('Back', true), 'controller' => $this->_controller->request->params['controller'],'action' => 'search', 'terms' => $this->_controller->request->projectvars['VarsArray']);

                $SettingsArray['printlink'] = array('discription' => __('Export result list', true), 'controller' => 'reportnumbers','action' => 'printresult', 'terms' => array());


                $this->_controller->Session->write('search.results', $Result);

                $this->_controller->set('SettingsArray', $SettingsArray);
                $this->_controller->set('orderKat', 5);
                $this->_controller->set('results', $Result);
                $this->_controller->render('results');
            }
        }


        $this->_controller->set('SearchFields', $fields);
        return $fields;
    }

    public function SearchUniversalAutocomplets($fieldsArray)
    {
        $projectID = $this->_controller->request->projectvars['VarsArray'][0];

        if ($projectID == 0 && isset($this->_controller->request->data['Order']['topproject_id'])) {
            $projectID = $this->_controller->request->data['Order']['topproject_id'];
        }
        if ($projectID == 0) {
            $projectID = $this->_controller->Autorisierung->ConditionsTopprojects();
        }

        $thisModel = null;
        $thisAutocomplets = array();
        $thisAutocompletsJSON = array();

        foreach ($fieldsArray as $_key => $_fieldsArray) {
            if (isset($_fieldsArray) && count($_fieldsArray) > 0) {
                foreach ($_fieldsArray as $__key => $__fieldsArray) {
                    if (!isset($__fieldsArray['Key'])) {
                        continue;
                    }

                    if ($__fieldsArray['Model'] != $this->_controller->modelClass) {
                        if (!$this->_controller->modelExists($__fieldsArray['Model'])) {
                            continue;
                        }
                        $this->_controller->loadModel($__fieldsArray['Model']);
                    }

                    if (!$this->_controller->$__fieldsArray['Model']->hasField($__fieldsArray['Key'])) {
                        continue;
                    }

                    $thisConditions = array(
                        'fields' =>
                        array(' DISTINCT ' . $__fieldsArray['Model'] . '.' . $__fieldsArray['Key']),
                        'conditions' =>
                        array(
                            $__fieldsArray['Model'] . '.' . $__fieldsArray['Key'] . ' != ' => '',
                        )
                    );

                    if ($this->_controller->$__fieldsArray['Model']->hasField('topproject_id')) {
                        $thisConditions['conditions'][$__fieldsArray['Model'] . '.topproject_id'] = $projectID;
                    }

                    if ($this->_controller->$__fieldsArray['Model']->hasField('deleted')) {
                        $thisConditions['conditions'][$__fieldsArray['Model'].'.deleted'] = 0;
                    }
                    $this->_controller->$__fieldsArray['Model']->recursive = -1;

                    foreach ($this->_controller->$__fieldsArray['Model']->find('all', $thisConditions) as $_thisFinds) {
                        $thisAutocomplets[$__fieldsArray['Model'].'_'.$__fieldsArray['Key']][] = $_thisFinds[$__fieldsArray['Model']][$__fieldsArray['Key']];
                    }
                }
            }

            // doppelte Einträge werden gelöscht
            if (isset($__fieldsArray['Key'])) {
                if (isset($thisAutocomplets[$__fieldsArray['Model'].'_'.$__fieldsArray['Key']]) && count($thisAutocomplets[$__fieldsArray['Model'].'_'.$__fieldsArray['Key']]) > 0) {
                    $thisAutocomplets[$__fieldsArray['Model'].'_'.$__fieldsArray['Key']] = array_unique($thisAutocomplets[$__fieldsArray['Model'].'_'.$__fieldsArray['Key']]);
                } else {
                    unset($thisAutocomplets[$__fieldsArray['Model'].'_'.$__fieldsArray['Key']]);
                }
            }
        }

        // Die Autocomplets werden in JSON und gleich in den View geschrieben
        foreach ($thisAutocomplets as $_key => $_thisAutocomplets) {
            $_thisAutocomplets = array_values($_thisAutocomplets);

            $NewKey = array_reduce(explode('_', $_key), function ($prev, $item) {
                return $prev.ucfirst($item);
            });
            $thisAutocompletsJSON[$NewKey] = json_encode($_thisAutocomplets);
        }

        $this->_controller->set('thisAutocompletsJSON', $thisAutocompletsJSON);

        return $thisAutocomplets;
    }

    public function Archiv($id, $Verfahren)
    {
        if (!isset($Verfahren)) {
            return;
        }
        if (empty($Verfahren)) {
            return;
        }

        $ReportArray = array();

        // Die Modelnamen bereitstellen
        $Report['Report'.$Verfahren.'Generally'] = 'Report'.$Verfahren.'Generally';
        $Report['Report'.$Verfahren.'Specific'] = 'Report'.$Verfahren.'Specific';
        $Report['Report'.$Verfahren.'Evaluation'] = 'Report'.$Verfahren.'Evaluation';
        $Report['Report'.$Verfahren.'Archiv'] = 'Report'.$Verfahren.'Archiv';

        // Die Condition für die Abfragen bereitstellen
        $optionsReport = array('conditions' => array('reportnumber_id' => $id));
        $optionsArchiv = array('conditions' => array('Reportnumber.id' => $id));

        $this->_controller->Reportnumber->recursive = 1;
        $reportnumber = $this->_controller->Reportnumber->find('first', $optionsArchiv);
        $reportnumber = $this->EnforceParentReportnumber($reportnumber);
        if (isset($reportnumber['Reportnumber']['_number'])) {
            $reportnumber['Reportnumber']['number'] = $reportnumber['Reportnumber']['_number'];
        }
        $this->_controller->Reportnumber->recursive = 1;

        $TestArray = array();

        foreach ($Report as $_key => $_Report) {
            $this->_controller->loadModel($_Report);

            if ($_key == 'Report'.$Verfahren.'Evaluation') {
                $Limit = 'all';
                foreach ($this->_controller->$_Report->find($Limit, array_merge_recursive($optionsReport, array(
                    'conditions'=>array(
                        $_Report.'.deleted'=>0),
                        'order'=>array(
                            $_key.'.sorting'=>'asc',
                            $_key.'.description'=>'asc',
                            $_key.'.id'=>'asc'
                        )
                    ))) as $__key => $__Report) {
                    $TestArray[$_key][][$_key] = $__Report[$_key];
                }

                if (isset($TestArray[$_key])) {
                    foreach ($TestArray[$_key] as $__key => $_Evaluation) {
                        $ReportArray[$_key][$__key] ['Report'.$Verfahren.'Evaluation']= $_Evaluation['Report'.$Verfahren.'Evaluation'];
                    }
                }


                if (isset($ReportArray[$_key])) {
                    $ReportArray[$_key] = $this->WeldSorting($ReportArray[$_key], 'archiv');
                } else {
                    $ReportArray[$_key] = array();
                }
            } else {
                $Limit = 'first';
                foreach ($this->_controller->$_Report->find($Limit, $optionsReport) as $__key => $__Report) {
                    $ReportArray[$_key] = $__Report;
                }
            }
        }

        $archiv = array_merge($reportnumber, $ReportArray);

        // und in der Archivierungstabelle speichern
        $ArchivTable = $Report['Report'.$Verfahren.'Archiv'];
        $additionaly_logos =  $this->_controller->Image->SelectAdditionalyLogoForArchiv($archiv, $ArchivTable);

        unset($archiv['Report'.$Verfahren.'Archiv']['data']);
        unset($archiv['Report'.$Verfahren.'Setting']);

        $archiv =  $this->_controller->Image->PutAdditionalyLogoForArchiv($archiv, $additionaly_logos, $ArchivTable);

        $data = array(
            'archiv' => $archiv
        );

        $xmlObject = Xml::fromArray($data, array('format' => 'tags'));
        $xmlString = $xmlObject->asXML();

        $archiv['Report'.$Verfahren.'Archiv']['data'] = htmlentities($xmlString);

        // Wenn schon ein Eintrag vorhanden ist, wird dieser erneuert
        if (isset($ReportArray['Report'.$Verfahren.'Archiv']['id'])) {
            $dataArchiv = array(
                'id' => $ReportArray['Report'.$Verfahren.'Archiv']['id'],
                'data' => $archiv['Report'.$Verfahren.'Archiv']['data']
            );
        }
        // Wenn kein Eintrag vorhanden ist, wird ein neuer erstellt
        else {
            $dataArchiv = array(
                'reportnumber_id' => $id,
                'data' => $archiv['Report'.$Verfahren.'Archiv']['data']
            );
        }

        $this->_controller->$ArchivTable->save($dataArchiv);
    }

public function MultiselectData($ReportArray, $arrayData, $reportnumbers)
{
    foreach ($ReportArray as $reportType) {
        foreach ($arrayData['settings']->$reportType as $fields) {
            foreach ($fields as $key=>$values) {
                if (!isset($values->multiselect)) {
                    continue;
                }

                // Radiovalues holen, wenn Datenfeld kein Ausweahlfeld ist
                if (trim($values->fieldtype) != 'radio') {
                    if (isset($values->radiooption->value)) {
                        foreach ($values->radiooption->value as $radiovalue) {
                            $reportnumbers['Multiselects'][$reportType][$key][trim($radiovalue)] = trim($radiovalue);
                        }
                    }
                }

                if (isset($values->multiselect->values) && $values->multiselect->values->count() > 0) {
                    foreach ($values->multiselect->values->children() as $val_k=>$val_v) {
                        if (trim($val_k) == 'rawdata') {
                            $val_k = trim($val_v);
                        }
                        $reportnumbers['Multiselects'][$reportType][$key][trim($val_k)] = trim($val_v);
                    }
                }

                if (isset($values->multiselect->model) && !empty($values->multiselect->model)) {
                    $this->_controller->loadModel(trim($values->multiselect->model));
                    if (!ClassRegistry::isKeySet(trim($values->multiselect->model))) {
                        continue;
                    }

                    $Model = trim($values->multiselect->model);

                    switch ($Model) {
                        case 'Dropdown':

                            $ThisModel = trim($values->model);
                            $ThisField = trim($values->key);
                            break;

                        default:

                            /* versuche, Feldnamen zu erraten oder benutze Standardwerte
                            *
                            *	key		-> gegebener Wert in multiselect->key oder Primärkey des Models
                            *  text	-> gegebener Wert in multiselect->text oder (der Reihenfolge nach, wenn in Datenbank vorhanden) title, caption, text, value
                            * 			   Sollte danach noch kein Feldname zutreffend sein, wird der key auch als Anzeigetext verwendet
                            */
                            $field_key = isset($values->multiselect->key) ? trim($values->multiselect->key) : '';
                            if (empty($field_key)) {
                                $field_key = $this->_controller->{trim($values->multiselect->model)}->primaryKey;
                            }

                            $field_text = isset($values->multiselect->display) ? trim($values->multiselect->display) : '';
                            if (empty($field_text)) {
                                foreach (array('title', 'caption', 'text', 'value', $field_key) as $testfield) {
                                    if ($this->_controller->{trim($values->multiselect->model)}->hasField($testfield)) {
                                        $field_text = $testfield;
                                        break;
                                    }
                                }
                            }

                            // Falls der Feldkey nicht exakt zutrifft, per Regex alle Entsprechungen holen
                            if (!$this->_controller->{trim($values->multiselect->model)}->hasField($field_key)) {
                                $field_key = array_keys(preg_grep_key('/'.$field_key.'/', $this->_controller->{trim($values->multiselect->model)}->schema()));
                            } else {
                                $field_key = array($field_key);
                            }

                            // Datenpaare aus der Datenbank holen
                            $results = array();
                            foreach ($field_key as $_field_key) {
                                $options = array(
                                    'fields' => array($_field_key, $field_text)
                                );
                                if (isset($values->multiselect->condition->field) && isset($values->multiselect->condition->condition)) {
                                    $options['conditions'] = array(trim($values->multiselect->model).'.'.trim($values->multiselect->condition->field)=>trim($values->multiselect->condition->condition));
                                }
                                if (isset($values->multiselect->condition->field) && isset($values->multiselect->condition->value)) {
                                    $condField = explode('.', trim($values->multiselect->condition->value));
                                    if (count($condField) == 2) {
                                        $options['conditions'] = array(
                                            trim($values->multiselect->model).'.'.trim($values->multiselect->condition->field) => $reportnumbers[$condField[0]][$condField[1]]
                                        );
                                    }
                                }

                                $options['order'] = array_map(function ($key) use ($values) {
                                    return trim($values->multiselect->model).'.'.$key.' ASC';
                                }, $field_key);
                                $results = array_merge($results, $this->_controller->{trim($values->multiselect->model)}->find('all', $options));
                            }

                            // Datenpaare so in $reportnumbers sortieren, dass die Struktur [Key]=>[Text] zustande kommt
                            foreach ($results as $result) {
                                foreach ($field_key as $_field_key) {
                                    if (isset($result[trim($values->multiselect->model)][$_field_key])) {
                                        if (!empty($field_text)) {
                                            $val = $result[trim($values->multiselect->model)][$field_text];
                                        } else {
                                            $val = reset($result[trim($values->multiselect->model)]);
                                        }

                                        if (!empty($val)) {
                                            $reportnumbers['Multiselects'][$reportType][$key][$result[trim($values->multiselect->model)][$_field_key]] = $val;
                                        }
                                    }
                                }
                            }

                            break;
                    }
                }

                unset($results);
            }
        }
    }

    return $reportnumbers;
}

public function ChangeDropdownData($ReportArray, $arrayData, $Model)
{
    $this->_controller->loadModel('DropdownsValue');

    if (!is_array($Model)) {
        $Model = array($Model);
    }

    foreach ($Model as $_Model) {
        if (!isset($ReportArray[$_Model])) {
            continue;
        }
        foreach ($ReportArray[$_Model] as $key => $value) {
            if (isset($ReportArray['Dropdowns'])) {
                if (isset($ReportArray['Dropdowns'][$_Model][$key])) {
                    if (array_search($value, $ReportArray['Dropdowns'][$_Model][$key]) !== false) {
                        $_key = array_search($value, $ReportArray['Dropdowns'][$_Model][$key]);
                        if (isset($ReportArray['Dropdowns'][$_Model][$key][$_key])) {
                            $ReportArray[$_Model][$key] = $ReportArray['Dropdowns'][$_Model][$key][$_key];
                            continue;
                        }
                    }
                    if (array_search($value, $ReportArray['Dropdowns'][$_Model][$key]) === false) {
                        continue;
                    }
                }
            }

            // bei leerem Wert und Feld, welches kein Dropdown ist, nicht weiter suchen
            if (empty($value) || !is_numeric($value)) {
                continue;
            }

            if (!isset($arrayData['settings']->$_Model->$key->select->model) || empty($arrayData['settings']->$_Model->$key->select->model)) {
                continue;
            }

            try {
                $this->_controller->DropdownsValue->recursive = 0;
                $value = $this->_controller->DropdownsValue->find('first', array(
                    'conditions'=>array(
                        'DropdownsValue.id' => $value
                    )
                ));

                // Falls das Dropdown nicht selbst zum Feld gehört, testen, ob es verlinkt ist
                if (isset($value['Dropdown'])) {
                    if ($value['Dropdown']['model'] != $_Model || $value['Dropdown']['field'] != $key) {
                        $this->_controller->DropdownsValue->Dropdown->recursive = -1;
                        if ($this->_controller->DropdownsValue->Dropdown->find('count', array(
                            'conditions'=>array(
                                'Dropdown.linking'=>$value['Dropdown']['id'],
                                'Dropdown.model' => $_Model,
                                'Dropdown.field' => $key
                            )
                        )) == 0) {
                            continue;
                        }
                    }
                }

                if (!empty($value)) {
                    $ReportArray[$_Model][$key] = $value['DropdownsValue']['discription'];
                }
            } catch(Exception $ex) {
                $log = $this->_controller->DropdownsValue->getDatasource()->getLog();
                $log = end($log['log']);
                $this->_controller->log($log['query']);
                $this->_controller->log($ex->getMessage());
            }
        }
    }

    return $ReportArray;
}

public function GeneralDropdownData($ReportArray, $arrayData, $reportnumbers=array())
{
    foreach ($ReportArray as $_ReportArray) {
        foreach ($arrayData['settings']->$_ReportArray as $settings) {
            foreach ($settings as $_settings) {
                $dropdownArrayHelper = array();

                // Dropdown Daten aus der Datenbank holen
                if (isset($_settings->select->model) && trim($_settings->select->model) != '') {
                    $options = array(
                        'conditions' => array(
                            'Dropdown.testingcomp_id' => $this->_controller->Autorisierung->ConditionsTestinccomps(),
                            'ODropdown.model' => $_settings->model,
                            'ODropdown.field' => $_settings->key
                        ),
                        'fields'=>array('Dropdown.id', 'Dropdown.linking', 'Dropdown.model', 'Dropdown.field'),
                        'joins' =>array(
                            array(
                                'table' => 'dropdowns',
                                'alias' => 'ODropdown',
                                'conditions' => array(
                                    'Dropdown.model = ODropdown.model',
                                    'Dropdown.field = ODropdown.field',
                                )
                            )
                        )
                    );

                    $this->_controller->loadModel('Dropdown');
                    $dropdown = $this->_controller->Dropdown->find('all', $options);
                    $this->_controller->loadModel('DropdownsValue');
                    $this->_controller->DropdownsValue->recursive = -1;
                    $values = $this->_controller->DropdownsValue->find('all', array('conditions'=>array('DropdownsValue.dropdown_id'=>Hash::extract($dropdown, '{n}.Dropdown.id')),'order' =>'DropdownsValue.discription ASC'));

                    foreach ($values as $value) {
                        $dropdownArrayHelper[$value['DropdownsValue']['id']] = $value['DropdownsValue']['discription'];
                        $dropdown['DropdownsValue'][] = $value;
                    }
                }

                if (array_search(strtolower(trim($_settings->select->custom_field)), array('1','yes')) !== false) {
                    if ($_settings->xpath('select/custom_field_roll/value[contains(text(), "'.$this->_controller->Session->read('Auth.User.Roll.id').'")]')) {
                        $reportnumbers['CustomDropdownFields'][] = $_ReportArray.'0'.ucfirst(Inflector::camelize($_settings->key));
                    }
                }

                if (count($values = $_settings->xpath('select/static/value'))) {
                    $values = array_map('trim', $values);

                    if (isset($reportnumbers[trim($_ReportArray)][trim($_settings->key)])) {
                        $values[$reportnumbers[trim($_ReportArray)][trim($_settings->key)]] = $reportnumbers[trim($_ReportArray)][trim($_settings->key)];
                    }

                    foreach ($values as $val) {
                        if (!isset($dropdownArrayHelper[$val])) {
                            $dropdownArrayHelper[$val] = $val;
                        }
                    }

                    $dropdownArrayHelper = array_unique($dropdownArrayHelper);
                }

                $reportnumbers['JSON'][trim($_settings->model)][trim($_settings->key)] = json_encode($dropdownArrayHelper);
                $reportnumbers['Dropdowns'][trim($_settings->model)][trim($_settings->key)] = $dropdownArrayHelper;
                if (isset($dropdown)) {
                    $reportnumbers['DropdownInfo'][trim($_settings->model)][trim($_settings->key)] = $dropdown;
                }
            }
        }
    }

    return $reportnumbers;
}

// hier wird die Datenhashfunktion für die Dropdownfelder in eine Funktion gepackt
public function DropdownData($ReportArray, $arrayData, $reportnumbers)
{
    $Verfahren = null;
    if (isset($reportnumbers['Testingmethod']['value'])) {
        $Verfahren = ucfirst($reportnumbers['Testingmethod']['value']);
    }

    $this->_controller->loadModel('DropdownsValue');
    $this->_controller->loadModel('Dropdown');
    $this->_controller->DropdownsValue->recursive = -1;
    $this->_controller->Dropdown->recursive = -1;

    if ($this->_controller->Auth->user('Roll.id') == 1) {
        $this->_controller->loadModel('Testingcomp');
        $this->_controller->Testingcomp->recursive = -1;
        $ProjectMember = $this->_controller->Autorisierung->ProjectMember($this->_controller->request->projectvars['VarsArray'][0]);
        $testingcomps = $this->_controller->Testingcomp->find('all', array('conditions' => array('Testingcomp.id' => $ProjectMember,'Testingcomp.roll_id >' => 4)));
    }

    $reportnumbers['CustomDropdownFields'] = array();

    foreach ($ReportArray as $_ReportArray) {
        $TestingCompOptionDropdownsValue = null;
        $GlobalOptionDropdownsValue = null;
        $TestingCompOptionDropdowns = null;

        if (AuthComponent::user('Testingcomp.roll_id') > 4) {
            $TestingCompOptionDropdownsValue['DropdownsValue.testingcomp_id'] = AuthComponent::user('Testingcomp.id');
            $GlobalOptionDropdownsValue['DropdownsValue.global'] = 1;
            $TestingCompOptionDropdowns['Dropdown.testingcomp_id'] = AuthComponent::user('Testingcomp.id');
        } else {
            $TestingCompOptionDropdownsValue = null;
            $GlobalOptionDropdownsValue = null;
            //			$TestingCompOptionDropdowns = null;
            $TestingCompOptionDropdowns['Dropdown.testingcomp_id'] = AuthComponent::user('Testingcomp.id');
        }

        foreach ($arrayData['settings']->$_ReportArray as $settings) {
            $dropdownArrayHelper = array();

            // das brauchen wir um das Array mit den Bewertungen rauszufiltern
            if ($Verfahren != null) {
                $StopForeachArray = explode($Verfahren, $_ReportArray);
            }

            foreach ($settings as $_settings) {
                $dropdownArrayHelper= array();
                if (isset($_settings->fieldtype) && trim($_settings->fieldtype) == 'checkbox') {
                    $reportnumbers['Dropdowns'][trim($_settings->model)][trim($_settings->key)] = array('', trim($_settings->discription->{trim($this->_controller->Lang->Discription())}));
                    $reportnumbers['JSON'][trim($_settings->model)][trim($_settings->key)] = json_encode($reportnumbers['Dropdowns'][trim($_settings->model)][trim($_settings->key)]);
                }
                if (isset($_settings->fieldtype) && trim($_settings->fieldtype) == 'radio') {
                    $reportnumbers['Dropdowns'][trim($_settings->model)][trim($_settings->key)] = $this->_controller->radiodefault;
                    if (isset($_settings->radiooption->value)) {
                        $reportnumbers['Dropdowns'][trim($_settings->model)][trim($_settings->key)] = array();
                        foreach ($_settings->radiooption->value as $val) {
                            array_push($reportnumbers['Dropdowns'][trim($_settings->model)][trim($_settings->key)], $val);
                        }
                    }
                    $reportnumbers['JSON'][trim($_settings->model)][trim($_settings->key)] = json_encode($reportnumbers['Dropdowns'][trim($_settings->model)][trim($_settings->key)]);
                } elseif ($_settings->select->model != '') {
                    if (array_search(strtolower(trim($_settings->select->custom_field)), array('1','yes')) !== false) {
                        if ($_settings->xpath('select/custom_field_roll/value[contains(text(), "'.$this->_controller->Session->read('Auth.User.Roll.id').'")]')) {
                            $reportnumbers['CustomDropdownFields'][] = $_ReportArray.'0'.ucfirst(Inflector::camelize($_settings->key));
                        }
                    }

                    if (count($values = $_settings->xpath('select/static/value'))) {
                        $values = array_map('trim', $values);
                        $values[$reportnumbers[trim($_ReportArray)][trim($_settings->key)]] = $reportnumbers[trim($_ReportArray)][trim($_settings->key)];

                        $dropdownArrayHelper = array_unique(array_combine($values, $values));

                        $reportnumbers['JSON'][trim($_settings->model)][trim($_settings->key)] = json_encode($dropdownArrayHelper);
                        $reportnumbers['Dropdowns'][trim($_settings->model)][trim($_settings->key)] = $dropdownArrayHelper;
                        continue;
                    }

                    // die ID des Dropdowns aus der Dropdown-Tabelle holen
                    // Wichtig ist die Suchreihenfolge, da immer der aktuellste


                    // Falls ein Bentuzer Zugriff auf mehrere Testingcomps hat, könnte die falsche ausgewählt werden
                    // Daher nur Testingcomp nehmen, die auch in der Datenbank beim Benutzer steht
                    $dropdownCondition = trim($_settings->select->condition);
                    $option = array(
                        'conditions' => array(
                            // Testingcomp weglassen sonst können Benutzer ohne Recht, ein Dropdown anzulegen keine Werte sehen
                            //'Dropdown.testingcomp_id' => $this->_controller->Autorisierung->ConditionsTestinccomps(),
                            'Dropdown.report_id' => $this->_controller->request->reportID,
                            'Dropdown.model LIKE ' => trim($_settings->model),
                            'Dropdown.field LIKE ' => trim($_settings->key),
                            $TestingCompOptionDropdowns
                        ),
                        'fields' => array($this->_controller->Dropdown->primaryKey, 'linking'),
                        'order' => array('Dropdown.id DESC')
                    );
                    $dropdown = $this->_controller->Dropdown->find('first', $option);

                    // Wenn ein Dropdownfeld nur von einem Hauptadmin befüllt werden kann
                    // und ein niedrigerer Nutzer noch keinen Dropdowneintrag für das Feld hat
                    // wird der Eintrag hier erstellt
                    if (count($dropdown) == 0 && !empty($_settings->select->roll->edit->value)) {
                        $addDropdownOption['dropdownid'] = trim($_settings->model) . '0' . trim($_settings->key);
                        $addDropdownOption['model'] = trim($_settings->model);
                        $addDropdownOption['field'] = trim($_settings->key);
                        $addDropdownOption['linking'] = 0;
                        $addDropdownOption['eng'] = trim($_settings->discription->eng);
                        $addDropdownOption['deu'] = trim($_settings->discription->deu);
                        $addDropdownOption['testingcomp_id'] = AuthComponent::user('Testingcomp.id');
                        $addDropdownOption['report_id'] = $this->_controller->request->projectvars['reportID'];
                        $addDropdownOption['testingmethod_id'] = $reportnumbers['Testingmethod']['id'];

                        $this->_controller->Dropdown->create();
                        $this->_controller->Dropdown->save($addDropdownOption);
                        $dropdown = $this->_controller->Dropdown->find('first', array('conditions' => array('Dropdown.id' => $this->_controller->Dropdown->getInsertID())));
                    }

                    // wenn ein Eintrag exisitiert
                    if (count($dropdown) > 0) {
                        $options = array(
                            'conditions' => array(
                                'DropdownsValue.dropdown_id' => $dropdown['Dropdown']['id'],//['Dropdown']['id'],
                                'DropdownsValue.report_id' => $this->_controller->request->reportID,
                            ),
                            'order' => array('DropdownsValue.discription ASC')
                        );

                        $dropdowns = $this->_controller->DropdownsValue->find('all', $options);

                        if (count($dropdowns) > 0) {
                            $reportnumbers['DropdownInfo'][trim($_settings->model)][trim($_settings->key)] = $dropdowns;
                        } elseif (count($dropdowns) == 0) {
                            $reportnumbers['DropdownInfo'][trim($_settings->model)][trim($_settings->key)][0] = array('DropdownsValue' => array('dropdown_id' => $dropdown['Dropdown']['id']));
                        }

                        // Die Dropdownwerte für das Selectfeld nutzbar machen
                        $dropdownArrayHelper = array();
                        foreach ($dropdowns as $_dropdowns) {
                            $dropdownArrayHelper[$_dropdowns['DropdownsValue']['id']] = $_dropdowns['DropdownsValue']['discription'];
                        }

                        // Test ob der gespeicherte Wert bereits in der Dropdownlist enthalten ist
                        $AddThis = 0;

                        foreach ($dropdownArrayHelper as $_dropdownArrayHelper) {
                            if (!isset($StopForeachArray)) {
                                continue;
                            }

                            if ($StopForeachArray[1] != 'Evaluation' && isset($reportnumbers[$_ReportArray][trim($_settings->key)])) {
                                if (trim($_dropdownArrayHelper) == trim($reportnumbers[$_ReportArray][trim($_settings->key)])) {
                                    break;
                                    $AddThis = 1;
                                }

                                // Wenn der Wert noch nicht vorhanden ist wird er hier hinzugefügt
                                $multiselect = isset($_settings->multiselect);
                                if ($AddThis == 0 && !$multiselect && trim($reportnumbers[$_ReportArray][trim($_settings->key)]) != '') {
                                    $dropdownArrayHelper[trim($reportnumbers[$_ReportArray][trim($_settings->key)])] = trim($reportnumbers[$_ReportArray][trim($_settings->key)]);
                                }
                            }

                            if ($StopForeachArray[1] == 'Evaluation' && isset($reportnumbers[$_ReportArray]) && !empty($reportnumbers[$_ReportArray])) {
                                foreach ($reportnumbers[$_ReportArray] as $_key => $_evalutions) {
                                    if (isset($reportnumbers[$_ReportArray][trim($_settings->key)])) {
                                        $dropdownArrayHelper[$reportnumbers[$_ReportArray][trim($_settings->key)]] = $reportnumbers[$_ReportArray][trim($_settings->key)];
                                    }
                                }
                            }
                        }

                        $AddThis = 0;
                        if (isset($dropdownArrayHelper)) {
                            $dropdownArrayHelper = array_unique($dropdownArrayHelper);
                        } // Doppelte Werte herausfiltern und numerische Indices bevorzugen
                        elseif (!isset($dropdownArrayHelper)) {
                            $dropdownArrayHelper = array();
                        }

                        $reportnumbers['JSON'][trim($_settings->model)][trim($_settings->key)] = json_encode($dropdownArrayHelper);

                        $reportnumbers['Dropdowns'][trim($_settings->model)][trim($_settings->key)] = $dropdownArrayHelper;
                    } else {
                        // 							$reportnumbers['JSON'][trim($_settings->model)][trim($_settings->key)] = array();
                        // 							$reportnumbers['Dropdowns'][trim($_settings->model)][trim($_settings->key)] = array();
                        //							$reportnumbers['DropdownInfo'][trim($_settings->model)][trim($_settings->key)] = array();
                    }
                }
            }

            unset($dropdownArrayHelper);
        }
    }

    // Die Globale  Werte hinzufügen
    if (isset($reportnumbers['Dropdowns']) && count($reportnumbers['Dropdowns']) > 0) {
        foreach ($reportnumbers['Dropdowns'] as $_key => $_dropdowns) {
            foreach ($_dropdowns as $__key => $__dropdowns) {
                $options = array(
                    'order' => array('DropdownsValue.discription ASC'),
                    'conditions' => array(
                        'DropdownsValue.model' => $_key,
                        'DropdownsValue.field' => $__key,
                        'DropdownsValue.report_id' => $this->_controller->request->reportID,
                        $GlobalOptionDropdownsValue,
                        //							$TestingCompOptionDropdownsValue,
                    )
                );

                $dropdowns = $this->_controller->DropdownsValue->find('all', $options);

                if (is_array($dropdowns)) {
                    // Wenn der Schlüssel nicht vorhanden ist
                    if (!isset($reportnumbers['DropdownInfo'][$_key][$__key])) {
                        continue;
                    }

                    // Wenn der Schlüssel vorhanden ist, aber keine Einträge vorhanden sind
                    if (!isset($reportnumbers['DropdownInfo'][$_key][$__key][0]['DropdownsValue']['id'])) {
                        unset($reportnumbers['DropdownInfo'][$_key][$__key][0]);
                    }

                    if (isset($reportnumbers['DropdownInfo'][$_key][$__key])) {
                        $reportnumbers['DropdownInfo'][$_key][$__key] = array_merge($reportnumbers['DropdownInfo'][$_key][$__key], $dropdowns);
                    }

                    foreach ($dropdowns as $___dropdowns) {
                        $reportnumbers['Dropdowns'][$_key][$__key][$___dropdowns['DropdownsValue']['id']] = $___dropdowns['DropdownsValue']['discription'];
                        $dropdownArrayHelper[$___dropdowns['DropdownsValue']['id']] = $___dropdowns['DropdownsValue']['discription'];
                    }

                    if (!isset($dropdownArrayHelper)) {
                        $dropdownArrayHelper = array();
                    }

                    $dropdownArrayHelper = array_unique($dropdownArrayHelper);

                    ksort($dropdownArrayHelper);

                    $reportnumbers['Dropdowns'][$_key][$__key] = $dropdownArrayHelper;
                    $reportnumbers['JSON'][$_key][$__key] = json_encode($dropdownArrayHelper);

                    $dropdownArrayHelper = array();
                } else {
                    $reportnumbers['DropdownInfo'][$_key][$__key][0]['DropdownsValue']['dropdown_id'] = 'XXX';
                }

                asort($reportnumbers['Dropdowns'][$_key][$__key]);
            }
        }
    }
    //das muss erstmal raus da er sonst die ID in den Schlüsseln überschreibt und die ahängigen Werte nicht gehen.
    //	if(!empty($reportnumbers['Dropdowns']))$reportnumbers['Dropdowns'] = $this->_controller->Drops->SortDropdowns($reportnumbers['Dropdowns'],'asc','natural');

    return $reportnumbers;
}

public function DeleteTest($data)
{
    if ($data['Reportnumber']['delete'] == 1) {
        die('The Testingreport was delete.');
    }
}
public function StatusTest($data)
{
    if (Configure::read('RefreshReport') == true && $data['Reportnumber']['status'] == 0) {
        if (Configure::check('CloseMethode') && Configure::read('CloseMethode') == 'showReportVerificationByTime') {
            if (time() >= $data['Reportnumber']['print'] + Configure::read('CloseMethodeTime') && $data['Reportnumber']['status'] == 0 && $data['Reportnumber']['print'] > 0) {
                $data['Reportnumber']['status'] = 2;
                $this->_controller->Reportnumber->save($data);
            } else {
            }
        }
    }

    return $data;
}

public function WeldSortingDiscription($Evaluation, $ReportEvaluation, $description)
{
    // Das Array nach der Nahtbezeichnung ordnen
    $WeldSortArray = array();
    $EvaluationNew = array();

    foreach ($Evaluation as $_key => $_Evaluation) {
        $WeldSortArray[] = trim($_Evaluation[$ReportEvaluation][$description]) . '[|*+||+*|]' . $_key;
    }

    natcasesort($WeldSortArray);
    $WeldSortArraySet = array();
    foreach ($WeldSortArray as $_WeldSortArray) {
        $WeldSortArraySet[] = $_WeldSortArray;
    }

    foreach ($WeldSortArraySet as $_key => $_WeldSortArraySet) {
        $_WeldSortArraySetExplode = explode('[|*+||+*|]', $_WeldSortArraySet);
        $EvaluationNew[][$ReportEvaluation] = $Evaluation[$_WeldSortArraySetExplode[1]][$ReportEvaluation];
    }

    return $EvaluationNew;
}

public function WeldSorting($data, $modus = null)
{
    if (count($data) == 0) {
        return array();
    }
    //	var_dump($data);
    $datasort = Hash::extract($data, '{n}.{s}[sorting>0]');
    //		if (!empty($datasort)) {var_dump($data);return $data;}
    //var_dump($datasort);
    // Daten für den Auswertungsberich vorbereiten
    // damit die Schleife eine neue Naht erkennt
    // wird ein Array mit den Nahtbezeichnungen erstellt
    $weldDiscription = array();
    $welds = array();

    // Wenn nix da ist Script abbrechen
    if (!isset($data)) {
        return $welds;
    }

    foreach ($data as $dataWeld) {
        if (isset($dataWeld[key($dataWeld)]['description'])) {
            $weldDiscription[] = $dataWeld[key($dataWeld)]['description'];
        }
        $Model = key($dataWeld);
    }

    // doppelte Werte werden entfernt und es wird neu sortiert
    $weldDisc = array_unique($weldDiscription);
    ksort($weldDisc);

    foreach ($weldDisc as $key => $_weldDisc) {
        $welds[$key][$Model]['discription'] = $_weldDisc;
        foreach ($data as $_data) {
            if ($_data[$Model]['description'] == $_weldDisc) {
                $welds[$key][$Model]['id'] = $_data[$Model]['id'];
                if (Configure::check('SortSheetNumber') && Configure::read('SortSheetNumber') == true) {
                    isset($_data[$Model]['sheet_no'])&& !empty($_data[$Model]['sheet_no']) ? $welds[$key][$Model]['sheet_no'] =  $_data[$Model]['sheet_no'] : $_data[$Model]['sheet_no']='/';
                }
                $welds[$key][$Model]['reportnumber_id'] = $_data[$Model]['reportnumber_id'];
                $welds[$key][$Model]['weld'][] = $_data[$Model];
            }
        }
    }

    if (!empty($datasort)) {
        if (!empty($modus) && $modus == 'archiv') {
            $returnarray = array();
            $weldsarchiv = Hash::extract($welds, '{n}.{s}.weld');
            foreach ($weldsarchiv as $wakey => $wavalue) {
                $returnarray = array_merge($returnarray, $wavalue);
            }
            return $returnarray;
        } else {
            return $welds;
        }
    } else {
        //Standart nach Naht und Bereich sorieren, ich denke das kann fest so bleiben da dies eigentlich immer die Schlüsselfelder sind im Auswertungsbereich
        $welds =	Hash::sort($welds, '{n}.{s}.discription', 'asc', 'natural');
        foreach ($welds as $weldkey => $weldvalue) {
            $wa = Hash::extract($weldvalue, '{s}.weld');
            $wa = $wa[0];
            $check = Hash::check($wa, '{n}.position');
            if ($check == true) {
                $neworder = 	Hash::sort($wa, '{n}.position', 'asc', 'natural');
                $welds[$weldkey][$Model] ['weld'] = $neworder;
            }
        }
    }
    //var_dump($welds); die();
    // Blattnummer sortieren, dass habe ich extra gelassen um fehler und Konfigurationsaufwand zu minimieren
    if (Configure::check('SortSheetNumber') && Configure::read('SortSheetNumber') == true) {
        $weldsnew = array();
        foreach ($welds as $wk => $wv) {
            if (!isset($wv[$Model]['sheet_no'])) {
                $wv[$Model]['sheet_no'] = "/";
            }
            if (empty($wv[$Model]['sheet_no'])) {
                $wv[$Model]['sheet_no']= "/";
            }

            $weldsheets[$wv[$Model]['sheet_no']][] = $wv;
        }

        if (!isset($weldsheets)) {
            return $welds;
        }
        if (!is_array($weldsheets)) {
            return $welds;
        }
        if (count($weldsheets) == 0) {
            return $welds;
        }

        ksort($weldsheets);

        foreach ($weldsheets as $wsk => $wsv) {
            foreach ($wsv as $_wsvk => $_wsvv) {
                $weldsnew[] =  $_wsvv;
            }
        }

        if (!empty($weldsnew)) {
            $welds = array();
            $welds = $weldsnew;
        }
    }//Wenn die PDF gedruckt werden soll oder archiviert wird->Daten dafür aufbereiten im geerigenten Fortmat

    if (!empty($modus) && $modus == 'archiv') {
        $returnarray = array();
        $weldsarchiv = Hash::extract($welds, '{n}.{s}.weld');
        foreach ($weldsarchiv as $wakey => $wavalue) {
            $returnarray = array_merge($returnarray, $wavalue);
        }
        return $returnarray;
    }
    return $welds;
}

public function MiddleValueHt($id, $ReportEvaluation, $Report, $arrayId, $value, $arrayData)
{
    if ($arrayId[1] == 'sorting') {
        $dataArray = array(
            'id' => $id,
            'sorting' => $value);

        if ($this->_controller->$ReportEvaluation->save($dataArray)) {
            $this->_controller->set('value', $value);
        }
        return;
    }

    $this->_controller->loadModel($ReportEvaluation);
    $this->_controller->loadModel('DropdownsValue');

    //$arrayId = explode('_',$this->_controller->request->data['id']);
    $dataArray = array();
    $werte = array();
    $row = null;
    //$id = $arrayId[count($arrayId) - 1];

    $row = $arrayId;
    array_pop($row);
    $row = join('_', array_slice($row, 1));

    if (isset($arrayData['settings']->$ReportEvaluation->$row->select->model) && trim($arrayData['settings']->$ReportEvaluation->$row->select->model) != '') {
        $thisDropdownsValue = ($this->_controller->DropdownsValue->find('first', array('conditions' => array('DropdownsValue.id' => $value))));

        if (count($thisDropdownsValue) > 0 && $thisDropdownsValue['DropdownsValue']['discription'] != '') {
            $value = $thisDropdownsValue['DropdownsValue']['discription'];
        }
    }

    $dataArray = array(
        'id' => $id,
        $row => $value
    );

    $TransportArray['model'] = $ReportEvaluation;
    $TransportArray['row'] = $row;
    $TransportArray['action'] = 'editevaluation';
    $TransportArray['this_value'] = $value;
    $TransportArray['last_id_for_radio'] = null;
    $TransportArray['table_id'] = $id;
    $TransportArray['last_value'] = $Report[$ReportEvaluation][$row];

    if ($reportnumber['Reportnumber']['revision_progress'] > 0) {
        $SaveRevisionIds = $this->SaveRevision($reportnumber, $TransportArray, false, false);
    }

    $logArray = $dataArray;
    $this->_controller->set('value', $value);

    if ($arrayData['settings']->$ReportEvaluation->$row->radiooption) {
        $radiooptions_array = array();

        foreach ($arrayData['settings']->$ReportEvaluation->$row->radiooption->value as $_key => $_value) {
            $radiooptions_array[] = trim($_value);
        }

        $this->_controller->set('value', $radiooptions_array[$value]);
        $logArray[$row] = $radiooptions_array[$value];
    }
    if ($this->_controller->$ReportEvaluation->save($dataArray)) {
        $dataArray['reportnumber_id']=$Report[$ReportEvaluation]['reportnumber_id'];
        $this->_controller->Autorisierung->Logger($Report[$ReportEvaluation]['reportnumber_id'], $logArray);

        if (isset($dataArray['result'])) {
            if ($dataArray['result'] == 2) {
                $reportnumber['Reportnumber']['result'] = 2;
            } else {
                $reportnumber['Reportnumber']['result'] = 1;
            }

            unset($reportnumber['Reportnumber']['modified']);
            unset($reportnumber['Reportnumber']['created']);

            $this->_controller->Reportnumber->save($reportnumber);
        }
    } else {
        $this->_controller->set('value', 'Error');
    }
}


    public function MiddleValueGeneral($id, $ReportEvaluation, $DataEvaluation, $arrayId, $value, $arrayData)
    {

        $row = join('_', array_slice($arrayId, 1, -1));

        if ($row == 'sorting') {
 
            $dataArray = array(
                'id' => $id,
                'sorting' => $value
            );

            $this->_controller->$ReportEvaluation->save($dataArray);

        } 

        return;          

    }

            public function MiddleValueRt($id, $ReportEvaluation, $Report, $arrayId, $value, $arrayData, $reportnumber = array())
            {
                if ($arrayId[1] == 'sorting') {
                    $dataArray = array(
                        'id' => $id,
                        'sorting' => $value);

                    if ($this->_controller->$ReportEvaluation->save($dataArray)) {
                        $this->_controller->set('value', $value);
                    }
                    return;
                }

                $this->_controller->loadModel($ReportEvaluation);
                $this->_controller->loadModel('DropdownsValue');

                //$arrayId = explode('_',$this->_controller->request->data['id']);
                $dataArray = array();
                $werte = array();
                $row = null;
                //$id = $arrayId[count($arrayId) - 1];

                $row = $arrayId;
                array_pop($row);
                $row = join('_', array_slice($row, 1));

                if (isset($arrayData['settings']->$ReportEvaluation->$row->select->model) && trim($arrayData['settings']->$ReportEvaluation->$row->select->model) != '') {
                    $thisDropdownsValue = ($this->_controller->DropdownsValue->find('first', array('conditions' => array('DropdownsValue.id' => $value))));

                    if (count($thisDropdownsValue) > 0 && $thisDropdownsValue['DropdownsValue']['discription'] != '') {
                        $value = $thisDropdownsValue['DropdownsValue']['discription'];
                    }
                }

                $dataArray = array(
                    'id' => $id,
                    $row => $value
                );

                $TransportArray['model'] = $ReportEvaluation;
                $TransportArray['row'] = $row;
                $TransportArray['action'] = 'editevaluation';
                $TransportArray['this_value'] = $value;
                $TransportArray['last_id_for_radio'] = null;
                $TransportArray['table_id'] = $id;
                $TransportArray['last_value'] = $Report[$ReportEvaluation][$row];

                if ($reportnumber['Reportnumber']['revision_progress'] > 0) {
                    $SaveRevisionIds = $this->SaveRevision($reportnumber, $TransportArray, false, false);
                }

                $logArray = $dataArray;
                $this->_controller->set('value', $value);

                if ($arrayData['settings']->$ReportEvaluation->$row->radiooption) {
                    $radiooptions_array = array();

                    foreach ($arrayData['settings']->$ReportEvaluation->$row->radiooption->value as $_key => $_value) {
                        $radiooptions_array[] = trim($_value);
                    }

                    $this->_controller->set('value', $radiooptions_array[$value]);
                    $logArray[$row] = $radiooptions_array[$value];
                }
                if ($this->_controller->$ReportEvaluation->save($dataArray)) {
                    $dataArray['reportnumber_id'] = $Report[$ReportEvaluation]['reportnumber_id'];
                    $this->_controller->Autorisierung->Logger($Report[$ReportEvaluation]['reportnumber_id'], $logArray);

                    if (isset($dataArray['result'])) {
                        $TestRep = $this->_controller->$ReportEvaluation->find(
                            'first',
                            array(
                            'conditions' => array(
                                $ReportEvaluation.'.reportnumber_id' => $Report[$ReportEvaluation]['reportnumber_id'],
                                $ReportEvaluation.'.result' => 2,
                            )
                        )
                        );

                        if (count($TestRep) > 0) {
                            $reportnumber['Reportnumber']['result'] = 2;
                        } else {
                            $reportnumber['Reportnumber']['result'] = 1;
                        }

                        unset($reportnumber['Reportnumber']['modified']);
                        unset($reportnumber['Reportnumber']['created']);

                        $this->_controller->Reportnumber->save($reportnumber);
                    }
                } else {
                    $this->_controller->set('value', 'Error');
                }
            }

            public function MiddleValueHt1($id, $ReportEvaluation, $Report, $arrayId, $value, $arrayData)
            {
                $this->_controller->loadModel($ReportEvaluation);

                $dataArray = array();

                // Wenn nur der Kommentar geändert wird
                if ($arrayId[1] == 'comment') {
                    $dataArray = array(
                        'id' => $id,
                        'comment' => $value);

                    if ($this->_controller->$ReportEvaluation->save($dataArray)) {
                        $this->_controller->Autorisierung->Logger($Report[$ReportEvaluation]['reportnumber_id'], $dataArray);
                        $this->_controller->set('value', $value);
                    }
                    return;
                } elseif ($arrayId[1] == 'sorting') {
                    $dataArray = array(
                        'id' => $id,
                        'sorting' => $value);

                    if ($this->_controller->$ReportEvaluation->save($dataArray)) {
                        $dataArray['reportnumber_id']=$Report[$ReportEvaluation]['reportnumber_id'];
                        $this->_controller->Autorisierung->Logger($Report[$ReportEvaluation]['reportnumber_id'], $dataArray);
                        $this->_controller->set('value', $value);
                    }
                    return;
                }

                $werte = array();
                $teiler = 0;

                if (!is_numeric($value)) {
                    $value = 0;
                }

                if ($Report[$ReportEvaluation]['wert1'] != '') {
                    $werte['wert1'] = $Report[$ReportEvaluation]['wert1'];
                } else {
                    $werte['wert1'] = 0;
                }
                if ($Report[$ReportEvaluation]['wert2'] != '') {
                    $werte['wert2'] = $Report[$ReportEvaluation]['wert2'];
                } else {
                    $werte['wert2'] = 0;
                }
                if ($Report[$ReportEvaluation]['wert3'] != '') {
                    $werte['wert3'] = $Report[$ReportEvaluation]['wert3'];
                } else {
                    $werte['wert3'] = 0;
                }

                settype($werte['wert1'], "integer");
                settype($werte['wert2'], "integer");
                settype($werte['wert3'], "integer");

                $werte[$arrayId[1]] =	$value;
                if ($value == '') {
                    $value = 0;
                }

                // nach Nullwerten suchen
                foreach ($werte as $_key => $_werte) {
                    if ($_werte < 1) {
                    } else {
                        $teiler++;
                    }
                }

                $wertgesamt = round(($werte['wert1'] + $werte['wert2'] + $werte['wert3']) / $teiler);

                $dataArray = array(
                    'id' => $id,
                    'wert1' => $werte['wert1'],
                    'wert2' => $werte['wert2'],
                    'wert3' => $werte['wert3'],
                    'wert_total' => $wertgesamt,
                    'haerte_hv' => $wertgesamt);

                if ($this->_controller->$ReportEvaluation->save($dataArray)) {
                    $dataArray['reportnumber_id']=$Report[$ReportEvaluation]['reportnumber_id'];
                    $this->_controller->Autorisierung->Logger($Report[$ReportEvaluation]['reportnumber_id'], $dataArray);

                    //Dropdown Value ersetzen
                    if (trim($arrayData['settings']->$ReportEvaluation->{join('_', array_slice($arrayId, 1, -1))}->select->model) != '') {
                        $this->_controller->loadModel('DropdownsValue');
                        $val = $this->_controller->DropdownsValue->find('list', array('fields'=>array('id', 'discription'), 'conditions'=>array('DropdownsValue.id'=>$value)));
                        if (!empty($val)) {
                            $value = reset($val);
                        }
                    }
                    $this->_controller->set('value', $value);
                } else {
                    $this->_controller->set('value', 'Error');
                }
            }
                        public function RollbackInvoice($reportnumbers)
                        {
                            if ($reportnumbers['Reportnumber']['status'] != 3) {
                                return;
                            } elseif ($reportnumbers['Reportnumber']['status'] == 3) {
                                $this->_controller->loadModel('Statisticsadditional');
                                $this->_controller->loadModel('Statisticsstandard');
                                $this->_controller->loadModel('Statisticsgenerall');

                                $Statisticsgenerall = $this->_controller->Statisticsgenerall->find('first', array('conditions' => array('reportnumber_id' => $reportnumbers['Reportnumber']['id'])));

                                $this->_controller->Statisticsgenerall->delete($Statisticsgenerall['Statisticsgenerall']['id']);

                                $Statisticsstandard = $this->_controller->Statisticsstandard->find(
                                    'all',
                                    array(
                                        'conditions' => array(
                                            'laufende_nr' => $Statisticsgenerall['Statisticsgenerall']['laufende_nr'],
                                            'topproject_id' => $reportnumbers['Reportnumber']['topproject_id'],
                                            'testingmethod_id' => $reportnumbers['Reportnumber']['testingmethod_id'],
                                            'order_id' => $reportnumbers['Reportnumber']['order_id']
                                        )
                                    )
                                );

                                // alle Einträge mit der betroffeen ReportID herausfischen
                                foreach ($Statisticsstandard as $_Statisticsstandard) {
                                    $IDs = explode(' ', $_Statisticsstandard['Statisticsstandard']['ids']);
                                    $Number = $_Statisticsstandard['Statisticsstandard']['number'];
                                    foreach ($IDs as $_key => $_IDs) {
                                        if ($_IDs == $reportnumbers['Reportnumber']['id']) {
                                            unset($IDs[$_key]);
                                            $Number--;
                                        }
                                    }
                                    $IDsString = implode(' ', $IDs);
                                    $_Statisticsstandard['Statisticsstandard']['number'] = $Number;
                                    $_Statisticsstandard['Statisticsstandard']['ids'] = $IDsString;
                                    if ($Number == 0) {
                                        $this->_controller->Statisticsstandard->delete($_Statisticsstandard['Statisticsstandard']['id']);
                                    } elseif ($Number > 0) {
                                        unset($_Statisticsstandard['Statisticsstandard']['modified']);
                                        $this->_controller->Statisticsstandard->save($_Statisticsstandard);
                                    }
                                }
                            }
                        }

                    /*	public function ResetNumberByYear($value) {

                        $this->_controller->loadModel('Reportnumber');
                        $this->_controller->Reportnumber->recursive = -1;

                        if(Configure::read('GlobalReportNumbers') == true){
                        $options = array(
                        'conditions' => array(),
                        'order' => 'Reportnumber.id DESC',
                        'limit' => 1
                );
            }
            if(Configure::read('GlobalReportNumbers') == false){
            $options = array(
            'conditions' => array('topproject_id' => $this->_controller->request->projectvars['VarsArray'][0]),
            'order' => 'Reportnumber.id DESC',
            'limit' => 1
        );
    }

    $reportnumber  = $this->_controller->Reportnumber->find('first',$options);

    if(Configure::read('ResetNumberByYear') == true){
    if(date("Y",time()) > $value['Reportnumber']['year']){
    $number = 1;
}
else {
$number = $reportnumber['Reportnumber']['number']+1;
}
}
else {
$number = $reportnumber['Reportnumber']['number']+1;
}

$timestamp = time();
$year = date("Y",$timestamp);

// Daten für den neuen Eintrag vorbereiten
$data = array(
'number' =>	$number,
'year' => $year,
'topproject_id' => $this->_controller->request->projectID,
'equipment_type_id' => $this->_controller->request->equipmentType,
'equipment_id' => $this->_controller->request->equipment,
'report_id' => $this->_controller->request->reportID,
'order_id' => $this->_controller->request->orderID,
'testingcomp_id' => $this->_controller->Auth->user('testingcomp_id'),
'modified_user_id' => $this->_controller->Auth->user('id'),
'user_id' => $this->_controller->Auth->user('id')
);

// neue Protokollnummer anlegen
// und die ID holen
$this->_controller->Reportnumber->create();
$this->_controller->Reportnumber->save($data);
$id = $this->_controller->Reportnumber->getInsertID();

return $id;
}
*/

public function ResetNumberByYear($value = null)
{
    $this->_controller->loadModel('Reportnumber');
    $this->_controller->Reportnumber->recursive = -1;

    $projectID = $this->_controller->request->projectID;
    $cascadeID = $this->_controller->request->cascadeID;
    $equipmentType = $this->_controller->request->equipmentType;
    $equipment = $this->_controller->request->equipment;
    $orderID = $this->_controller->request->orderID;
    $reportID = $this->_controller->request->reportID;

    $GlobalReportNumbers = array();
    $topproject = $this->_controller->Reportnumber->Topproject->find('all', array('conditions'=>array('Topproject.id'=>$projectID)));
    //		$topproject = $this->_controller->Navigation->Subdivisions($topproject);
    $GlobalReportNumbers['Reportnumber.number >'] = 0;

    if (Configure::check('GlobalReportNumbers')) {
        switch(strtolower(Configure::read('GlobalReportNumbers'))) {
            case 'report':
                $GlobalReportNumbers['Reportnumber.report_id'] = $reportID;

                // no break
            case 'order':
                /**/if ($topproject[0]['Topproject']['subdivision'] != 1) { /**/$GlobalReportNumbers['Reportnumber.order_id'] = $orderID;
                }

                // no break
            case 'topproject':
                $GlobalReportNumbers['Reportnumber.topproject_id'] = $projectID;
        }
    }

    $options = array(
        'conditions' => $GlobalReportNumbers,
        'order' => 'Reportnumber.id DESC',
        'limit' => 1
    );

    $reportnumber  = $this->_controller->Reportnumber->find('first', $options);

    $minOptions = array_merge($options, array('fields'=>array('MIN(equipment_type_id), MIN(equipment_id), MIN(order_id), MIN(report_id)')));

    $minEquipmentType = $this->_controller->Reportnumber->EquipmentType->find('first', array('conditions'=>array('EquipmentType.topproject_id'=>$projectID)));
    $minEquipment = $this->_controller->Reportnumber->Equipment->find('first', array('conditions'=>array('Equipment.topproject_id'=>$projectID)));
    $minOrder = $this->_controller->Reportnumber->Order->find('first', array('conditions'=>array('Order.topproject_id'=>$projectID)));

    $min = $this->_controller->Reportnumber->find('first', $minOptions);
    $min = reset($min);

    if (isset($minEquipmentType['EquipmentType']['id'])) {
        if ($min['MIN(equipment_type_id)'] == 0) {
            $min['MIN(equipment_type_id)'] = $minEquipmentType['EquipmentType']['id'];
        }
    }

    if (isset($minEquipment['Equipment']['id'])) {
        if ($min['MIN(equipment_id)'] == 0) {
            $min['MIN(equipment_id)'] = $minEquipment['Equipment']['id'];
        }
    }

    if (isset($minOrder['Order'])) {
        if ($min['MIN(order_id)'] == 0) {
            $min['MIN(order_id)'] = $minOrder['Order']['id'];
        }
    }

    if (Configure::read('ResetNumberByYear') == true) {
        if (date("Y", time()) > $reportnumber['Reportnumber']['year']) {
            $number = 1;
        } else {
            $number = isset($reportnumber['Reportnumber']['number']) ? $reportnumber['Reportnumber']['number'] + 1 : 1;
        }
    } else {
        $number = $reportnumber['Reportnumber']['number']+1;
    }

    $timestamp = time();
    $year = date("Y", $timestamp);

    // Daten für den neuen Eintrag vorbereiten
    $data = array(
        'number' =>	intval($number),
        'year' => intval($year),
        'topproject_id' => intval($this->_controller->request->projectID),
        'equipment_type_id' => intval($this->_controller->request->equipmentType == 0 ? $min['MIN(equipment_type_id)'] : $this->_controller->request->equipmentType),
        'equipment_id' => intval($this->_controller->request->equipment == 0 ? $min['MIN(equipment_id)'] : $this->_controller->request->equipment),
        'cascade_id' => intval($cascadeID),
        'order_id' => intval($orderID),
        'report_id' => intval($this->_controller->request->reportID == 0 ? $min['MIN(report_id)'] : $this->_controller->request->reportID),
        'testingcomp_id' => intval($this->_controller->Auth->user('testingcomp_id')),
        'modified_user_id' => intval($this->_controller->Auth->user('id')),
        'user_id' => intval($this->_controller->Auth->user('id')),
    );

    // neue Protokollnummer anlegen
    // und die ID holen
    // testen ob das Array vollständig ist
    foreach ($data as $_key => $_data) {
        if ($_data == 0) {
            //				return false;
        }
        if (is_int($_data) == false) {
            return false;
        }
    }

    $this->_controller->Reportnumber->create();

    $this->_controller->Reportnumber->save($data);
    $id = $this->_controller->Reportnumber->getInsertID();

    return $id;
}

public function En1435()
{
    $zaehler_en_1435 = '0';
    $en_1435[$zaehler_en_1435]['nummer'] = "1";
    $en_1435[$zaehler_en_1435]['erklaerung'] = "Aufnahmeanordnung f&uuml;r ebene Schwei&szlig;n&auml;hte und einwandige Durchstrahlung";
    $en_1435[$zaehler_en_1435]['bild'] = "en_1435_bild_1.gif";
    $zaehler_en_1435++;
    $en_1435[$zaehler_en_1435]['nummer'] = "2";
    $en_1435[$zaehler_en_1435]['erklaerung'] = "Aufnahmeanordung f&uuml;r einwandige Durchstrahlung gekr&uuml;mmter Pr&uuml;fgegenst&auml;nde";
    $en_1435[$zaehler_en_1435]['bild'] = "en_1435_bild_2.gif";
    $zaehler_en_1435++;
    $en_1435[$zaehler_en_1435]['nummer'] = "3";
    $en_1435[$zaehler_en_1435]['erklaerung'] = "Aufnahmeanordnung f&uuml;r einwandige Durchstrahlung gekr&uuml;mmter Pr&uuml;fgegenst&auml;nde (eingesetzte Schwei&szlig;n&auml;hte)";
    $en_1435[$zaehler_en_1435]['bild'] = "en_1435_bild_3.gif";
    $zaehler_en_1435++;
    $en_1435[$zaehler_en_1435]['nummer'] = "4";
    $en_1435[$zaehler_en_1435]['erklaerung'] = "Aufnahmeanordnung f&uuml;r einwandige Durchstrahlung gekr&uuml;mmter Pr&uuml;fgegenst&auml;nde (aufgesetzte Schwei&szlig;n&auml;hte)";
    $en_1435[$zaehler_en_1435]['bild'] = "en_1435_bild_4.gif";
    $zaehler_en_1435++;
    $en_1435[$zaehler_en_1435]['nummer'] = "5";
    $en_1435[$zaehler_en_1435]['erklaerung'] = "Aufnahmeanordnung f&uuml;r einwandige Durchstrahlung gekr&uuml;mmter Pr&uuml;fgegenst&auml;nde";
    $en_1435[$zaehler_en_1435]['bild'] = "en_1435_bild_5.gif";
    $zaehler_en_1435++;
    $en_1435[$zaehler_en_1435]['nummer'] = "6";
    $en_1435[$zaehler_en_1435]['erklaerung'] = "Aufnahmeanordnung f&uuml;r einwandige Durchstrahlung gekr&uuml;mmter Pr&uuml;fgegenst&auml;nde (eingesetzte Schwei&szlig;naht)";
    $en_1435[$zaehler_en_1435]['bild'] = "en_1435_bild_6.gif";
    $zaehler_en_1435++;
    $en_1435[$zaehler_en_1435]['nummer'] = "7";
    $en_1435[$zaehler_en_1435]['erklaerung'] = "Aufnahmeanordnung f&uuml;r einwandige Durchstrahlung gekr&uuml;mmter Pr&uuml;fgegenst&auml;nde (aufgesetzte Schwei&szlig;naht)";
    $en_1435[$zaehler_en_1435]['bild'] = "en_1435_bild_7.gif";
    $zaehler_en_1435++;
    $en_1435[$zaehler_en_1435]['nummer'] = "8";
    $en_1435[$zaehler_en_1435]['erklaerung'] = "Aufnahmeanordnung f&uuml;r einwandige Durchstrahlung gekr&uuml;mmter Pr&uuml;fgegenst&auml;nde";
    $en_1435[$zaehler_en_1435]['bild'] = "en_1435_bild_8.gif";
    $zaehler_en_1435++;
    $en_1435[$zaehler_en_1435]['nummer'] = "9";
    $en_1435[$zaehler_en_1435]['erklaerung'] = "Aufnahmeanordnung f&uuml;r einwandige Durchstrahlung gekr&uuml;mmter Pr&uuml;fgegenst&auml;nde (eingesetzte Schwei&szlig;naht)";
    $en_1435[$zaehler_en_1435]['bild'] = "en_1435_bild_9.gif";
    $zaehler_en_1435++;
    $en_1435[$zaehler_en_1435]['nummer'] = "10";
    $en_1435[$zaehler_en_1435]['erklaerung'] = "Aufnahmeanordnung f&uuml;r einwandige Durchstrahlung gekr&uuml;mmter Pr&uuml;fgegenst&auml;nde (aufgesetzte Schwei&szlig;naht)";
    $en_1435[$zaehler_en_1435]['bild'] = "en_1435_bild_10.gif";
    $zaehler_en_1435++;
    $en_1435[$zaehler_en_1435]['nummer'] = "11";
    $en_1435[$zaehler_en_1435]['erklaerung'] = "Aufnahmeanordnung f&uuml;r doppelwandige Durchstrahlung (Doppelbild) gekr&uuml;mmter Pr&uuml;fgegenst&auml;nde zur Auswertung beider W&auml;nde (Strahlenquelle und Film au&szlig;erhalb des Pr&uuml;fgegenstandes)";
    $en_1435[$zaehler_en_1435]['bild'] = "en_1435_bild_11.gif";
    $zaehler_en_1435++;
    $en_1435[$zaehler_en_1435]['nummer'] = "12";
    $en_1435[$zaehler_en_1435]['erklaerung'] = "Aufnahmeanordnung f&uuml;r doppelwandige Durchstrahlung (Doppelbild) gekr&uuml;mmter Pr&uuml;fgegenst&auml;nde zur Auswertung beider W&auml;nde (Strahlenquelle und Film au&szlig;erhalb des Pr&uuml;fgegenstandes)";
    $en_1435[$zaehler_en_1435]['bild'] = "en_1435_bild_12.gif";
    $zaehler_en_1435++;
    $en_1435[$zaehler_en_1435]['nummer'] = "13";
    $en_1435[$zaehler_en_1435]['erklaerung'] = "Aufnahmeanordnung f&uuml;r doppelwandige Durchstrahlung (Einbild) gekr&uuml;mmter Pr&uuml;fgegenst&auml;nde zur Auswertung der filmnahen Wand. Der BPK ist filmnah anzulegen";
    $en_1435[$zaehler_en_1435]['bild'] = "en_1435_bild_13.gif";
    $zaehler_en_1435++;
    $en_1435[$zaehler_en_1435]['nummer'] = "14";
    $en_1435[$zaehler_en_1435]['erklaerung'] = "Aufnahmeanordnung f&uuml;r doppelwandige Durchstrahlung (Einbild)";
    $en_1435[$zaehler_en_1435]['bild'] = "en_1435_bild_14.gif";
    $zaehler_en_1435++;
    $en_1435[$zaehler_en_1435]['nummer'] = "15";
    $en_1435[$zaehler_en_1435]['erklaerung'] = "Aufnahmeanordnung f&uuml;r doppelwandige Durchstrahlung (Einbild) geradliniger Schwei&szlig;n&auml;hte";
    $en_1435[$zaehler_en_1435]['bild'] = "en_1435_bild_15.gif";
    $zaehler_en_1435++;
    $en_1435[$zaehler_en_1435]['nummer'] = "16";
    $en_1435[$zaehler_en_1435]['erklaerung'] = "Aufnahmeanordnung f&uuml;r doppelwandige Durchstrahlung (Einbild) gekr&uuml;mmter Pr&uuml;fgegenst&auml;nde zur Auswertung der filmnahen Wand";
    $en_1435[$zaehler_en_1435]['bild'] = "en_1435_bild_16.gif";
    $zaehler_en_1435++;
    $en_1435[$zaehler_en_1435]['nummer'] = "17";
    $en_1435[$zaehler_en_1435]['erklaerung'] = "Aufnahmeanordnung zur Durchstrahlung von Kehln&auml;hten";
    $en_1435[$zaehler_en_1435]['bild'] = "en_1435_bild_17.gif";
    $zaehler_en_1435++;
    $en_1435[$zaehler_en_1435]['nummer'] = "18";
    $en_1435[$zaehler_en_1435]['erklaerung'] = "Aufnahmeanordnung f&uuml;r die Durchstrahlung von Kehln&auml;hten";
    $en_1435[$zaehler_en_1435]['bild'] = "en_1435_bild_18.gif";
    $zaehler_en_1435++;
    $en_1435[$zaehler_en_1435]['nummer'] = "19";
    $en_1435[$zaehler_en_1435]['erklaerung'] = "Mehrfilmtechnik";
    $en_1435[$zaehler_en_1435]['bild'] = "en_1435_bild_19.gif";

    return $en_1435;
}

public function GetRevisionChanges($id = 0)
{
    $this->_controller->loadModel('Reportnumber');
    $db = $this->_controller->Reportnumber->getDataSource();

    $this->_controller->Reportnumber->recursive  = -1;
    $reports = $this->_controller->Reportnumber->find('all', array(
        'fields'=>array('id', 'testingmethod_id'),
        'order'=>array('id'=>'asc'),
        'conditions'=>array('or'=>array('Reportnumber.id'=>$id,'Reportnumber.new_version_id'=>$id))));

    if (count($reports) < 2) {
        return array();
    }

    $diffs = array();

    $this->_controller->Reportnumber->Testingmethod->recursive = -1;
    $method = $this->_controller->Reportnumber->Testingmethod->find('list', array('fields'=>array('value'), 'conditions'=>array('Testingmethod.id'=>$reports[0]['Reportnumber']['testingmethod_id'])));

    $method = ucfirst(reset($method));

    $reports = Hash::extract($reports, '{n}.Reportnumber.id');

    foreach (array('Generally','Specific') as $model) {
        $model = 'Report'.$method.$model;

        $this->_controller->loadModel($model);

        $keys = $this->_controller->$model->schema();
        unset($keys['id']);
        unset($keys['reportnumber_id']);
        unset($keys['created']);
        unset($keys['modified']);

        $keys = array_keys($keys);

        $_model = $this->_controller->$model;
        $data = array_map(
            function ($report) use ($_model, $keys) {
                $data = $_model->find('first', array(
                    'fields'=>$keys,
                    'conditions'=>array('reportnumber_id'=>$report)
                ));

                return array_map(function ($elem) {
                    return empty($elem) ? '' : strval($elem);
                }, reset($data));
            },
            $reports
        );

        $_diff = array();
        foreach (array_keys($data[0]) as $key) {
            if (empty($data[0][$key]) && empty($data[1][$key])) {
                continue;
            }
            if ($data[0][$key] == $data[1][$key]) {
                continue;
            }

            $_diff[$model][$key] = array($data[0][$key], $data[1][$key]);
        }

        if (!empty($_diff)) {
            $diffs = array_merge($diffs, $_diff);
        }
    }

    $model = 'Report'.$method.'Evaluation';
    $this->_controller->loadModel($model);

    $keys = $this->_controller->$model->schema();
    unset($keys['id']);
    unset($keys['reportnumber_id']);
    unset($keys['created']);
    unset($keys['modified']);

    $keys = array_keys($keys);

    $_model = $this->_controller->$model;
    $data = array_map(function ($id) use ($_model) {
        return $_model->find('all', array('conditions'=>array('reportnumber_id'=>$id, 'deleted'=>0)));
    }, $reports);
    //$offset = Hash::extract($data[1], '0.'.$model.'.id') - Hash::extract($data[1], '0.'.$model.'.id');
    $startID = HasH::extract($data, '{n}.0.'.$model.'.id');

    $offset = 0;

    if (count($startID) > 0) {
        $offset = $startID[1]-$startID[0];
    }

    for ($i=0; $i<count($data[1]); ++$i) {
        if (!isset($data[0][$i])) {
            if ($data[1][$i][$model]['deleted'] == 0) {
                $diffs[$model][] = array(
                    array(),
                    array_diff_key($data[1][$i][$model], array_flip(array('id','reportnumber_id','created','modified'))),
                    array_diff_key($data[1][$i][$model], array_flip(array('id','reportnumber_id','created','modified')))
                );
            }
        } else {
            if (
                array_diff_key($data[0][$i][$model], array_flip(array('id','reportnumber_id','created','modified'))) !=
                array_diff_key($data[1][$i][$model], array_flip(array('id','reportnumber_id','created','modified')))
            ) {
                $diffs[$model][] = array(
                    $data[0][$i][$model],
                    $data[1][$i][$model],
                    // Foth 20.09.2017, geändert in array_div_assoc, sonst kein Ergebniss bei string(0) zu string(irgendwas)
                    array_diff_assoc(
                        array_diff_key($data[0][$i][$model], array_flip(array('id','reportnumber_id','created','modified'))),
                        array_diff_key($data[1][$i][$model], array_flip(array('id','reportnumber_id','created','modified')))
                    )
                );
            }
        }
    }

    // Foth 20.09.2017, wenn ein Nahtabschnitt gelöscht wurde
    if (isset($diffs[$model])) {
        $i = count($diffs[$model]);
    } else {
        $i = 1;
    }

    if ($data[0] > $data[1]) {
        foreach ($data[0] as $_key => $_data) {
            if (array_key_exists($_key, $data[1]) == true) {
            } else {
                $diffs[$model][$i][0] = $data[0][$_key][$model];
                $text  = $data[0][$_key][$model]['description'] . ' ';
                if (isset($data[0][$_key][$model]['position'])) {
                    $text .= $data[0][$_key][$model]['position'] . ' ';
                }
                $text .= __('deleted', true);
                $diffs[$model][$i][1] = array('description' => $text);
                if (isset($data[0][$_key][$model]['position'])) {
                    $diffs[$model][$i][1]['position'] =  $data[0][$_key][$model]['position'];
                    $diffs[$model][$i][2]['description'] =  $data[0][$_key][$model]['description'] . ' ' . $data[0][$_key][$model]['position'];
                }
                $diffs[$model][$i][3] = array('deleted_in_revision' => true);
                $text = null;
                $i++;
            }
        }
    }

    if ($data[0] < $data[1]) {
    }

    if (count($diffs) > 0) {
        $this->_controller->request->RevisionOldReport = $this->_controller->Reportnumber->find('first', array('conditions'=>array('Reportnumber.new_version_id' =>$this->_controller->request->projectvars['VarsArray'][5])));
        $this->_controller->request->RevisionNewReport = $this->_controller->Reportnumber->find('first', array('conditions'=>array('Reportnumber.id' =>$this->_controller->request->projectvars['VarsArray'][5])));
    }

    return $diffs;
}

    public function DevelopmentReset($orderID, $evalutionAllId)
    {
        if (Configure::read('DevelopmentsEnabled') == true) {
            $this->_controller->loadModel('Development');
            $this->_controller->loadModel('Order');
            $order = $this->_controller->Order->find('first', array('conditions' => array('Order.id' => $orderID)));

            if (!isset($order['Development'])) {
                return;
            }

            if (count($order['Development']) > 0) {
                $evalutionAllIdOption = array('DevelopmentData.evaluation_id' => $evalutionAllId);

                $development = $this->_controller->Development->DevelopmentData->find(
                    'first',
                    array(
                        'conditions' =>
                        array(
                            'DevelopmentData.evaluation_id' => $evalutionAllId
                        )
                    )
                );

                if (count($development) > 0) {
                    $this->_controller->Development->DevelopmentData->updateAll(
                        array(
                            'DevelopmentData.evaluation_id' => 0,
                            'DevelopmentData.result' => 0,
                        ),
                        $evalutionAllIdOption
                    );
                }
            }
        }
    }

    public function AddDevelopmentData($Order)
    {
        foreach ($Order as $key => $value) {
            $DevelopmentData = $this->_controller->Order->Development->DevelopmentData->find('all', array('conditions' => array('DevelopmentData.order_id' => $value['Order']['id'])));
            $progress = array('all' => 0, 'ok' => 0, 'rep' => 0, 'open' => 0, 'prozent_or' => 0, 'prozent_mr' => 0);

            if (count($DevelopmentData) == 0) {
                continue;
            }

            foreach ($DevelopmentData as $_key => $_value) {
                $progress['all']++;
                if ($_value['DevelopmentData']['result'] == 0) {
                    $progress['open']++;
                }
                if ($_value['DevelopmentData']['result'] == 1) {
                    $progress['rep']++;
                }
                if ($_value['DevelopmentData']['result'] == 2) {
                    $progress['ok']++;
                }
            }

            $Order[$key]['Development']['progress']['all'] = $progress['all'];
            $Order[$key]['Development']['progress']['open'] = $progress['open'];
            $Order[$key]['Development']['progress']['rep'] = $progress['rep'];
            $Order[$key]['Development']['progress']['ok'] = $progress['ok'];
            $Order[$key]['Development']['progress']['prozent_or'] = @round(100 * $progress['ok'] / $progress['all'], 2);
            $Order[$key]['Development']['progress']['prozent_mr'] = @round(100 * ($progress['ok'] + $progress['rep']) / $progress['all'], 2);
        }

        return $Order;
    }

    public function DevelopmentMenue($order, $reportsArrayContainer)
    {
        if (count($order['Development']) == 0 && $order['Order']['development_id'] == 0) {
            return $reportsArrayContainer;
        }

        if ($order['Order']['development_id'] > 0) {
            $progress = array('inital' => 0,'additional' => 0, 'all' => 0, 'ok' => 0, 'rep' => 0, 'open' => 0, 'prozent_or' => 0, 'prozent_mr' => 0);

            $this->_controller->Order->Development->recursive = -1;
            $Development = $this->_controller->Order->Development->find('all', array('conditions' => array('Development.id' => $order['Order']['development_id'])));
            $order['Development'] = $Development;

            $DevelopmentData = $this->_controller->Order->Development->DevelopmentData->find(
                'all',
                array(
                'conditions' => array(
                    'Development.id' => $order['Order']['development_id'],
                    'DevelopmentData.order_id' => $order['Order']['id']
                ),
                'fields' => array('DevelopmentData.result','DevelopmentData.inital')
            )
            );

            foreach ($DevelopmentData as $_DevelopmentData) {
                $progress['all']++;
                if ($_DevelopmentData['DevelopmentData']['inital'] == 0) {
                    $progress['additional']++;
                }
                if ($_DevelopmentData['DevelopmentData']['inital'] == 1) {
                    $progress['inital']++;
                }
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

            $order['Order']['Development']['progress']['inital'] = $progress['inital'];
            $order['Order']['Development']['progress']['additional'] = $progress['additional'];
            $order['Order']['Development']['progress']['all'] = $progress['all'];
            $order['Order']['Development']['progress']['open'] = $progress['open'];
            $order['Order']['Development']['progress']['rep'] = $progress['rep'];
            $order['Order']['Development']['progress']['ok'] = $progress['ok'];
            $order['Order']['Development']['progress']['prozent_or'] = @round(100 * $progress['ok'] / $progress['all'], 2);
            $order['Order']['Development']['progress']['prozent_mr'] = @round(100 * ($progress['ok'] + $progress['rep']) / $progress['all'], 2);
        }

        if (count($order['Development']) > 0) {
            if (count($order['Development']) > 0) {
                foreach ($order['Development'] as $_development);
                {
                    $this->_controller->request->projectvars['VarsArray'][3] = $_development['Development']['id'];
                    $this->_controller->request->projectvars['VarsArray'][4] = 3;

                    $order['Order']['Development']['links'][] = array(
                        'haedline' => __('Testing progress', true),
                        'class' => 'icon_progress ',
                        'link_class' => ' modal',
                        'params' => array(),
                        'controller' => 'developments',
                        'discription' => __('Bearbeiten von Prüfpunkten', true) . ' ' . __('für Fortschritt von', true) . ' ' . $_development['Development']['name'],
                        'action' => 'progress/'. implode('/', $this->_controller->request->projectvars['VarsArray']),
                    );

                    $order['Order']['Development']['links'][] = array(
                        'class' => 'icon_progress ',
                        'link_class' => ' modal',
                        'params' => array(),
                        'controller' => 'developments',
                        'discription' => __('Übersicht von Prüfpunkten', true) . ' ' . __('für Fortschritt von', true) . ' ' . $_development['Development']['name'],
                        'action' => 'orderdetails/'. $_development['Development']['id'] . '/' . $order['Order']['id'] . '/3',
                    );
                }
            }

            return $order;
        }
    }

public function writePngChunk($image, $daten, $compression = 0)
{
    // Let php write the png file...
    ob_start();
    imagepng($image, null, $compression, null);
    $content = ob_get_clean();

    // Split result to get a slot to insert our pHYs chunk...
    list($first, $second) = explode('IDAT', $content, 2);

    // This way we split the IDAT chunk between its length and type code,
    // so we have to repair it...
    $idatlen = substr($first, strlen($first)-4, 4);
    $first = substr($first, 0, strlen($first)-4);
    $second = $idatlen . 'IDAT' . $second;
    $chunk = null;

    foreach ($daten as $_daten) {
        $chunkdata = $_daten['keyword'] . "\0" . $_daten['content'];
        $crc = pack("N", crc32($_daten['key'] . $chunkdata));
        $len = pack("N", strlen($chunkdata));
        $chunk .= $len .  $_daten['key']  . $chunkdata . $crc;
    }

    $content = $first . $second . $chunk;

    return $content;
}

// zum löschen verurteilt
public function SignReport()
{
    // Die IDs des Projektes und des Auftrages werden getestet
    $projectID = $this->_controller->request->projectvars['VarsArray'][0];
    $cascadeID = $this->_controller->request->projectvars['VarsArray'][1];
    $orderID = $this->_controller->request->projectvars['VarsArray'][2];
    $reportID = $this->_controller->request->projectvars['VarsArray'][3];
    $id = $this->_controller->request->projectvars['VarsArray'][4];

    $this->_controller->Reportnumber->recursive = 0;
    $reportnumbers = $this->_controller->Reportnumber->find('first', array('conditions' => array('Reportnumber.id' => $id)));

    $reportnumbers = $this->EnforceParentReportnumber($reportnumbers);
    if (isset($reportnumbers['Reportnumber']['_number'])) {
        $reportnumbers['Reportnumber']['number'] = $reportnumbers['Reportnumber']['_number'].' ('.$reportnumbers['Reportnumber']['number'].')';
    }

    $reportnumbers = $this->RevisionCheckTime($reportnumbers);

    $reportnumbers = $this->GetReportData($reportnumbers, array('Generally','Specific','Evaluation'));

    $verfahren = $this->_controller->request->verfahren;
    $Verfahren = $this->_controller->request->Verfahren ;

    $ReportGenerally = $this->_controller->request->tablenames[0];
    $ReportSpecific = $this->_controller->request->tablenames[1];
    $ReportEvaluation = $this->_controller->request->tablenames[2];
    $ReportArchiv = $this->_controller->request->tablenames[3];
    $ReportSettings = $this->_controller->request->tablenames[4];
    $ReportPdf = $this->_controller->request->tablenames[5];

    $ReportArray = array($ReportGenerally,$ReportSpecific,$ReportEvaluation);

    $arrayData = $this->_controller->Xml->DatafromXml($verfahren, 'file', $Verfahren);

    $this->_controller->loadModel($ReportEvaluation);

    $evaluations = $this->_controller->$ReportEvaluation->find(
        'all',
        array(
        'conditions' =>
        array(
            'reportnumber_id' => $id,
            'deleted' => 0
        )
    )
    );

    unset($reportnumbers[$ReportEvaluation]);

    $reportnumbers[$ReportEvaluation] = $evaluations;

    if (Configure::check('RepairManager') && Configure::read('RepairManager') == true) {
        $reportnumbers = $this->_controller->Repairtracking->GetRepairReport($reportnumbers);
        $reportnumbers = $this->_controller->Repairtracking->GetRepairStatus($reportnumbers, $ReportEvaluation);
    }

    $arrayData = $this->_controller->Xml->DatafromXml($verfahren, 'file', ucfirst($verfahren));

    $errors = $this->_controller->Reportnumber->getValidationErrors($arrayData, $reportnumbers, $verfahren);

    if (count($errors) > 0) {
        $this->_controller->set('errors', $errors);
        $this->_controller->set('xml', $arrayData);
    }

    $src['examiner']['Discription'] = __('Examinierer');
    $src['examiner']['Action'] = 'signExaminer';
    $src['supervision']['Discription'] = __('Supervisor');
    $src['supervision']['Action'] = 'signSupervisor';
    $src['third_part']['Discription'] = __('Third part');
    $src['third_part']['Action'] = 'signThirdPart';
    $src['four_part']['Discription'] = __('Four part');
    $src['four_part']['Action'] = 'signFourPart';

    $this->_controller->loadModel('User');
    $this->_controller->loadModel('Sign');
    $this->_controller->User->recursive = 0;

    //		$this->_controller->loadModel('SingsHistory');

    $Signatories = $this->_controller->Sign->find('all', array('conditions' => array('Sign.reportnumber_id' => $id)));

    $error_array = array();
    $openImage = array();

    foreach ($Signatories as $_Signatories) {
        $output = $this->_controller->Image->setSignImage($reportnumbers, $_Signatories, $_Signatories['Sign']['signatory']);
        $user = $this->_controller->User->find('first', array('conditions' => array('User.id' => $_Signatories['Sign']['user_id'])));

        $openImage = $output['image'];

        if ($_Signatories['Sign']['signatory'] == 1) {
            $src['examiner']['Data'] = $openImage;
            $src['examiner']['Info'] = $output['data']['SingsHistory'];
            $src['examiner']['Sign'] = $_Signatories['Sign'];
            $src['examiner'] = array_merge($src['examiner'], $user);
            unset($src['examiner']['Sign']['signature']);
        }
        if ($_Signatories['Sign']['signatory'] == 2) {
            $src['supervision']['Data'] = $openImage;
            $src['supervision']['Info'] = $output['data']['SingsHistory'];
            $src['supervision']['Sign'] = $_Signatories['Sign'];
            $src['supervision'] = array_merge($src['supervision'], $user);
            unset($src['supervision']['Sign']['signature']);
        }
        if ($_Signatories['Sign']['signatory'] == 3) {
            $src['third_part']['Data'] = $openImage;
            $src['third_part']['Info'] = $output['data']['SingsHistory'];
            $src['third_part']['Sign'] = $_Signatories['Sign'];
            $src['third_part'] = array_merge($src['third_part'], $user);
            unset($src['third_part']['Sign']['signature']);
        }
        if ($_Signatories['Sign']['signatory'] == 4) {
            $src['four_part']['Data'] = $openImage;
            $src['four_part']['Info'] = $output['data']['SingsHistory'];
            $src['four_part']['Sign'] = $_Signatories['Sign'];
            $src['four_part'] = array_merge($src['four_part'], $user);
            unset($src['four_part']['Sign']['signature']);
        }
    }

    // Wenn es keinen XML-Knoten gibt wird die Unterschrift nicht angezeigt
    // kommt erst wenn das Speichern der examiner_id richtig klappt
    /*
    foreach($src as $key => $value){

        if(empty($arrayData['settings']->{$ReportGenerally}->{$key})){
            unset($src[$key]);
        }
    }
    */

    $ReportSettings = 'Report'.$Verfahren.'Setting';
    $ReportPdf = 'Report'.$Verfahren.'Pdf';

    $Settings = null;

    $menue = $this->_controller->Navigation->NaviMenue(null);
    $breads = $this->_controller->Navigation->BreadForReport($reportnumbers, 'sign');

    // Das Array für das Settingsmenü
    $SettingsArray = array();
    $SettingsArray['addlink'] = array('discription' => __('Add', true), 'controller' => 'testingmethods','action' => 'listing', 'terms' => $this->_controller->request->projectvars['VarsArray']);
    $SettingsArray['addsearching'] = array('discription' => __('Searching', true), 'controller' => 'searchings','action' => 'search', 'terms' => $this->_controller->request->projectvars['VarsArray']);
    $breads = $this->_controller->Navigation->SubdivisionBreads($breads);

    if (isset($arrayData['settings'])) {
        $ReportMenue = $this->_controller->Navigation->createReportMenue($reportnumbers, $arrayData['settings']);
        $this->_controller->set('ReportMenue', $ReportMenue);
    }

    $ReportMenue = $this->_controller->Navigation->createReportMenue($reportnumbers, $arrayData['settings']);
    $SettingsArray = $this->_controller->Autorisierung->AclCheckLinks($SettingsArray);

    $this->_controller->set('error_array', $error_array);
    $this->_controller->set('signature', $src);
    $this->_controller->set('Signatories', $Signatories);
    $this->_controller->set('settings', $Settings);
    $this->_controller->set('SettingsArray', $SettingsArray);
    $this->_controller->set('reportnumber', $reportnumbers);
    $this->_controller->set('breads', $breads);
    $this->_controller->set('menues', $menue);

    if ($this->_controller->Autorisierung->IsThisMyReport($id) === false) {
        return;
    }

    $output = array('Signatories' => $Signatories,'src' => $src);

    return $output;
}

public function RemoveSign($type)
{
    $this->_controller->layout = 'modal';

    // Die IDs des Projektes und des Auftrages werden getestet
    $projectID = $this->_controller->request->projectvars['VarsArray'][0];
    $cascadeID = $this->_controller->request->projectvars['VarsArray'][1];
    $orderID = $this->_controller->request->projectvars['VarsArray'][2];
    $reportID = $this->_controller->request->projectvars['VarsArray'][3];
    $id = $this->_controller->request->projectvars['VarsArray'][4];

    $this->_controller->Reportnumber->recursive = 0;
    $reportnumbers = $this->_controller->Reportnumber->find('first', array('conditions' => array('Reportnumber.id' => $id)));

    $Verfahren = ucfirst($reportnumbers['Testingmethod']['value']);
    $this->_controller->request->verfahren = $Verfahren;
    $verfahren = $reportnumbers['Testingmethod']['value'];

    $ReportSettings = 'Report'.$Verfahren.'Setting';
    $ReportPdf = 'Report'.$Verfahren.'Pdf';

    //		$this->_controller->loadModel($ReportSettings);
    //		$optionsSettings = array('conditions' => array($ReportSettings.'.reportnumber_id' => $id));
    //		$Settings = $this->_controller->$ReportSettings->find('first', $optionsSettings);

    // Das Array für das Settingsmenü
    $SettingsArray = array();

    $this->_controller->Data->SignReport();

    $this->_controller->set('Settings', array());
    $this->_controller->set('thisSign', $type);
    $this->_controller->set('SettingsArray', $SettingsArray);
    $this->_controller->set('reportnumber', $reportnumbers);
    $this->_controller->render('remove_sign');
}

public function ProjectStatus($TopprojectId, $status, $row)
{
    $del = $status;
    $this->_controller->loadModel('Reportnumber');
    $this->_controller->Reportnumber->recursive = -1;
    $this->_controller->Reportnumber->EquipmentType->recursive = -1;
    $this->_controller->Reportnumber->Equipment->recursive = -1;
    $this->_controller->Reportnumber->Order->recursive = -1;

    $this->_controller->Reportnumber->EquipmentType->updateAll(
        array('EquipmentType.status' => $del),
        array('EquipmentType.topproject_id' => $TopprojectId)
    );

    $this->_controller->Reportnumber->Equipment->updateAll(
        array('Equipment.status' => $del),
        array('Equipment.topproject_id' => $TopprojectId)
    );

    $this->_controller->Reportnumber->Order->updateAll(
        array('Order.deleted' => $del),
        array('Order.topproject_id' => $TopprojectId)
    );

    $this->_controller->Reportnumber->updateAll(
        array('Reportnumber.' . $row => $del),
        array('Reportnumber.topproject_id' => $TopprojectId)
    );
}


public function Sessionstoraging($modelname = null)
{
    if ($modelname == null) {
        $modelname = $this->_controller->modelClass;
    }
    $this->_controller->layout = 'ajax';
    if ($this->_controller->Session->check($modelname.'.form') > 0) {
        $this->_controller->Session->delete($modelname.'.form');
    }
    if (isset($this->_controller->request->data) && ($this->_controller->request->is('post') || $this->_controller->request->is('put'))) {
        $this->_controller->Session->write($modelname.'.form', $this->_controller->request->data[$modelname]);
        $timestamp = time();
        $this->_controller->Session->write($modelname.'.timestamp', $timestamp);
    }
}

public function SessionFormload($modelname = null)
{
    if ($modelname == null) {
        $modelname = $this->_controller->modelClass;
    }
    $sessiontimestamp = $this->_controller->Session->read($modelname.'.timestamp');
    $now = time();
    if ($this->_controller->Session->check($modelname.'.form') > 0  && ($now-$sessiontimestamp < 90)) {
        $this->_controller->request->data[$modelname] = $this->_controller->Session->read($modelname.'.form');
    } else {
        $this->_controller->Session->delete($modelname.'.form');
    }
}

public function GetWeldEvaluation($_methods, $testingreports)
{
    $output = array();

    foreach ($_methods as $_method) {
        $xml = $this->_controller->Xml->XmltoArray($_method, 'file', ucfirst($_method));
        // In der Statistik nur Prüfverfahren berücksichtigen, wenn dessen Nahtbereiche ein Ergebnis haben
        if (count($xml->xpath('Report'.ucfirst($_method).'Evaluation/*[key="result"]')) == 0) {
            continue;
        }

        foreach (array('Generally', 'Specific', 'Evaluation') as $part) {
            $rpart = 'Report'.ucfirst($_method).$part;
            $this->_controller->loadModel($rpart);

            // Bei Evaluation wird auch nach deleted gefiltert
            $thisconditions =  array($rpart.'.reportnumber_id'=>array_keys($testingreports));
            if ($part == 'Evaluation') {
                $thisconditions[$rpart.'.deleted'] = 0;
            }

            $options = array('conditions' => $thisconditions);
            $_part = $this->_controller->$rpart->find('all', $options);
            $evals = array();

            if ($part == 'Evaluation') {
                if ($rpart == 'ReportRtEvaluation') {
                    $welderrors = array();
                    $welderrors = $this->_controller->Data->GetWeldErrors($_part, $rpart, $this->_controller->errorNumbers);
                }

                foreach (Hash::extract($_part, '{n}.{s}') as $__part) {
                    $description =  null;
                    $welder =  null;

                    if (strlen($__part['description']) > 0) {
                        $description = $__part['description'];
                    } else {
                        $description = $__part['id'];
                    }

                    if (isset($__part['welder'])) {
                        if (strlen($__part['welder']) > 0) {
                            $welder = $__part['welder'];
                        } else {
                            $welder = '-';
                        }
                    } else {
                        $welder = '-';
                    }


                    $welds['evals'][$__part['reportnumber_id']][$description]['welder'] = $welder;
                    $welds['evals'][$__part['reportnumber_id']]['date'] = date_parse($testingreports[$__part['reportnumber_id']]['Reportnumber']['created']);

                    if (isset($__part['position'])) {
                        if (strlen($__part['position']) > 0) {
                            $welds['evals'][$__part['reportnumber_id']][$description]['position'][$__part['position']] = $__part['result'];
                        } else {
                            $welds['evals'][$__part['reportnumber_id']][$description]['position'][] = $__part['result'];
                        }
                    } else {
                        $welds['evals'][$__part['reportnumber_id']][$description]['position'][$description] = $__part['result'];
                    }
                }
            } else {
                foreach ($_part as $__part) {
                    $__part = reset($__part);
                    $testingreports[$__part['reportnumber_id']][$part] = $__part;
                }
            }
        }
    }

    $output['testingreports'] = array();
    $output['welds'] = array();
    $output['welderrors'] = array();

    if (isset($testingreports)) {
        $output['testingreports'] = $testingreports;
    }
    if (isset($welds)) {
        $output['welds'] = $welds;
    }
    if (isset($welderrors)) {
        $output['welderrors'] = $welderrors;
    }

    return $output;
}

public function GetWeldStatistic($welds)
{
    $output = array();
    $welder  = array();
    $welders  = array();
    $weldbyTime = array();
    $year_count = array();
    $month_count = array();
    $welds['statistics'] = array(
        'all' => array(
            'reports' => 0,
            'all' => 0,
            'e' => 0,
            'ne' => 0,
            '-' => 0
        ),
        'year' => array(),
        'month' => array(),
        'day' => array()
    );

    if (isset($welds['evals'])) {
        foreach ($welds['evals'] as $_key => $_welds) {
            if (!isset($welds['statistics']['year'][$_welds['date']['year']]['all'])) {
                $welds['statistics']['year'][$_welds['date']['year']]['all'] = 0;
                $welds['statistics']['year'][$_welds['date']['year']]['-'] = 0;
                $welds['statistics']['year'][$_welds['date']['year']]['e'] = 0;
                $welds['statistics']['year'][$_welds['date']['year']]['ne'] = 0;
            }
            if (!isset($welds['statistics']['month'][$_welds['date']['year'].'.'.$_welds['date']['month']]['all'])) {
                $welds['statistics']['month'][$_welds['date']['year'].'.'.$_welds['date']['month']]['all'] = 0;
                $welds['statistics']['month'][$_welds['date']['year'].'.'.$_welds['date']['month']]['-'] = 0;
                $welds['statistics']['month'][$_welds['date']['year'].'.'.$_welds['date']['month']]['e'] = 0;
                $welds['statistics']['month'][$_welds['date']['year'].'.'.$_welds['date']['month']]['ne'] = 0;
            }
            if (!isset($welds['statistics']['day'][$_welds['date']['year'].'.'.$_welds['date']['month'].'.'.$_welds['date']['day']]['all'])) {
                $welds['statistics']['day'][$_welds['date']['year'].'.'.$_welds['date']['month'].'.'.$_welds['date']['day']]['all'] = 0;
                $welds['statistics']['day'][$_welds['date']['year'].'.'.$_welds['date']['month'].'.'.$_welds['date']['day']]['-'] = 0;
                $welds['statistics']['day'][$_welds['date']['year'].'.'.$_welds['date']['month'].'.'.$_welds['date']['day']]['e'] = 0;
                $welds['statistics']['day'][$_welds['date']['year'].'.'.$_welds['date']['month'].'.'.$_welds['date']['day']]['ne'] = 0;
            }

            $weldnumbers = 0;
            $weldnumbers_n = 0;
            $weldnumbers_e = 0;
            $weldnumbers_ne = 0;

            foreach ($_welds as $__key => $__welds) {
                if ($__key == 'created') {
                    continue;
                }
                if ($__key == 'date') {
                    continue;
                }


                $welds['evals'][$_key][$__key]['result'] = max($__welds['position']);
                $welds['statistics']['all']['all']++;

                if ($welds['evals'][$_key][$__key]['result'] == 0) {
                    $welds['statistics']['all']['-']++;
                    $weldnumbers_n++;
                }
                if ($welds['evals'][$_key][$__key]['result'] == 1) {
                    $welds['statistics']['all']['e']++;
                    $weldnumbers_e++;
                }
                if ($welds['evals'][$_key][$__key]['result'] == 2) {
                    $welds['statistics']['all']['ne']++;
                    $weldnumbers_ne++;
                }
                $weldnumbers++;

                $welder = null;
                if ($__welds['welder'] == '') {
                    $welder = __('no welder', true);
                }
                if ($__welds['welder'] != '') {
                    $welder = $__welds['welder'];
                }


                if (!isset($welders[$welder])) {
                    $welders[$welder] = array('all' => 0,'-' => 0, 'e' => 0, 'ne' => 0);
                }
                if ($welder != null) {
                    $welders[$welder]['all'] = $welders[$welder]['all'] + 1;

                    if ($welds['evals'][$_key][$__key]['result'] == 0) {
                        $welders[$welder]['-'] = $welders[$welder]['-'] + 1;
                    }
                    if ($welds['evals'][$_key][$__key]['result'] == 1) {
                        $welders[$welder]['e'] = $welders[$welder]['e'] + 1;
                    }
                    if ($welds['evals'][$_key][$__key]['result'] == 2) {
                        $welders[$welder]['ne'] = $welders[$welder]['ne'] + 1;
                    }
                }
            }

            $welds['statistics']['day'][$_welds['date']['year'].'.'.$_welds['date']['month'].'.'.$_welds['date']['day']]['all'] = $welds['statistics']['day'][$_welds['date']['year'].'.'.$_welds['date']['month'].'.'.$_welds['date']['day']]['all'] + $weldnumbers;
            $welds['statistics']['day'][$_welds['date']['year'].'.'.$_welds['date']['month'].'.'.$_welds['date']['day']]['-'] = $welds['statistics']['day'][$_welds['date']['year'].'.'.$_welds['date']['month'].'.'.$_welds['date']['day']]['-'] + $weldnumbers_n;
            $welds['statistics']['day'][$_welds['date']['year'].'.'.$_welds['date']['month'].'.'.$_welds['date']['day']]['e'] = $welds['statistics']['day'][$_welds['date']['year'].'.'.$_welds['date']['month'].'.'.$_welds['date']['day']]['e'] + $weldnumbers_e;
            $welds['statistics']['day'][$_welds['date']['year'].'.'.$_welds['date']['month'].'.'.$_welds['date']['day']]['ne'] = $welds['statistics']['day'][$_welds['date']['year'].'.'.$_welds['date']['month'].'.'.$_welds['date']['day']]['ne'] + $weldnumbers_ne;


            $welds['statistics']['month'][$_welds['date']['year'].'.'.$_welds['date']['month']]['all'] = $welds['statistics']['month'][$_welds['date']['year'].'.'.$_welds['date']['month']]['all'] + $weldnumbers;
            $welds['statistics']['month'][$_welds['date']['year'].'.'.$_welds['date']['month']]['-'] = $welds['statistics']['month'][$_welds['date']['year'].'.'.$_welds['date']['month']]['-'] + $weldnumbers_n;
            $welds['statistics']['month'][$_welds['date']['year'].'.'.$_welds['date']['month']]['e'] = $welds['statistics']['month'][$_welds['date']['year'].'.'.$_welds['date']['month']]['e'] + $weldnumbers_e;
            $welds['statistics']['month'][$_welds['date']['year'].'.'.$_welds['date']['month']]['ne'] = $welds['statistics']['month'][$_welds['date']['year'].'.'.$_welds['date']['month']]['ne'] + $weldnumbers_ne;

            $welds['statistics']['year'][$_welds['date']['year']]['all'] = $welds['statistics']['year'][$_welds['date']['year']]['all'] + $weldnumbers;
            $welds['statistics']['year'][$_welds['date']['year']]['-'] = $welds['statistics']['year'][$_welds['date']['year']]['-'] + $weldnumbers_n;
            $welds['statistics']['year'][$_welds['date']['year']]['e'] = $welds['statistics']['year'][$_welds['date']['year']]['e'] + $weldnumbers_e;
            $welds['statistics']['year'][$_welds['date']['year']]['ne'] = $welds['statistics']['year'][$_welds['date']['year']]['ne'] + $weldnumbers_ne;

            if (!isset($weldbyTime[$_welds['date']['year']][$_welds['date']['month']][$_welds['date']['day']])) {
                $weldbyTime[$_welds['date']['year']][$_welds['date']['month']][$_welds['date']['day']][$_key] = array();
            }

            $weldbyTime[$_welds['date']['year']][$_welds['date']['month']][$_welds['date']['day']][$_key] = $welds['evals'][$_key];

            $year_count[$_welds['date']['year']] = $_welds['date']['year'];
            $month_count[$_welds['date']['month']] = $_welds['date']['month'];
            $welds['statistics']['all']['reports']++;
        }
    }


    ksort($welders);

    $output['welds'] = $welds;
    $output['welders'] = $welders;
    $output['weldbyTime'] = $weldbyTime;
    $output['year_count'] = $year_count;
    $output['month_count'] = $month_count;

    return $output;
}

public function GetWeldErrors($_part, $rpart, $errorNumbers)
{
    $errorcodes['all'] = array();
    $errorcodes['ne'] = array();

    foreach ($_part as $__key => $__part) {
        // In der Wackerdatenbank muss noch das Feld error_1 in error umbenannt werden
        if (!isset($__part[$rpart]['error']) && isset($__part[$rpart]['error_1'])) {
            $__part[$rpart]['error'] = $__part[$rpart]['error_1'];
        }

        if (isset($__part[$rpart]['error'])) {
            if (!isset($__part[$rpart]['error_array'])) {
                //					$__part[$rpart]['error'] = preg_replace(PHP_EOL, ',', $__part[$rpart]['error']);
                $__part[$rpart]['error'] = preg_replace('/\\r/', ',', $__part[$rpart]['error']);
                $__part[$rpart]['error'] = preg_replace('/\\n/', ',', $__part[$rpart]['error']);
                //					$__part[$rpart]['error'] = str_replace(' ', ',', $__part[$rpart]['error']);

                $_part[$__key][$rpart]['error_array'] = explode(',', $__part[$rpart]['error']);
            } else {
                $_part[$__key][$rpart]['error_array'] = array_merge($_part[$__key][$rpart]['error_array'], explode(',', $__part[$rpart]['error']));
            }

            foreach ($_part[$__key][$rpart]['error_array'] as $error_array) {
                if (empty($error_array)) {
                    continue;
                }

                if ($__part[$rpart]['result'] == 2) {
                    if (!isset($errorcodes['ne'][trim($error_array)])) {
                        $errorcodes['ne'][trim($error_array)] = 1;
                    } else {
                        $errorcodes['ne'][trim($error_array)]++;
                    }
                }

                if (!isset($errorcodes['all'][trim($error_array)])) {
                    $errorcodes['all'][trim($error_array)] = 1;
                } else {
                    $errorcodes['all'][trim($error_array)]++;
                }
            }
        }
    }

    unset($errorcodes['all']['FF']);
    unset($errorcodes['ne']['FF']);
    ksort($errorcodes['all']);
    ksort($errorcodes['ne']);

    $welderrors['all'][0] = array('code' => 'undefinierte Fehler','value' => 0);
    $welderrors['ne'][0] = array('code' => 'undefinierte Fehler','value' => 0);
    foreach ($errorcodes['all'] as $__key => $__errorcodes) {
        if (isset($errorNumbers[$__key])) {
            $welderrors['all'][$__key] = array('code' => $errorNumbers[$__key] . ' (' . $__errorcodes . ')','value' => $__errorcodes);

            if (isset($errorcodes['ne'][$__key])) {
                $welderrors['ne'][$__key] = array('code' => $errorNumbers[$__key] . ' (' . $errorcodes['ne'][$__key] . ')','value' => $errorcodes['ne'][$__key]);
            }
        } else {
            $welderrors['all'][0]['value']++;

            if (isset($errorcodes['ne'][$__key])) {
                $welderrors['ne'][0]['value']++;
            }
        }
    }

    $welderrors['all'][0]['code'] .= ' (' . $welderrors['all'][0]['value'] . ')';

    if ($welderrors['all'][0]['value'] == 0) {
        unset($welderrors['all'][0]);
    }

    return $welderrors;
}

public function GetSearchFields($_methods)
{
    if ($this->_controller->Session->check('GetSearchFields')) {
        $this->_controller->Session->delete('GetSearchFields');
    }

    // Bezeichnungen der Suchefelder aus den XML-Dateien holen
    $xml = array();
    $searchfields = array();

    // XML-Dateien der betroffenen Verfahren laden
    // und Sucharray anlegen
    foreach ($_methods as $_key => $__methods) {
        $searchfields[ucfirst($__methods)] = array();
        $xml[ucfirst($__methods)] = $this->_controller->Xml->XmltoArray($__methods, 'file', ucfirst($__methods));
    }

    $model = null;

    $fieldhelparray['Reportnumber'] = array(
        'report_id' => array(
            'deu' => 'Prüfberichte',
            'eng' => 'testreports'
        ),
        'testingmethod_id' => array(
            'deu' => 'Prüfverfahren',
            'eng' => 'testingmethod'
        ),
        'year' => array(
            'deu' => 'Jahr',
            'eng' => 'year'
        ),
        'created_from' => array(
            'deu' => 'Datum erstellt von',
            'eng' => 'date created from'
        ),
        'created_to' => array(
            'deu' => 'Datum erstellt bis',
            'eng' => 'date created to'
        ),
        'test_location' => array(
            'deu' => 'Prüfort',
            'eng' => 'test location'
        ),
        'welding_company' => array(
            'deu' => 'Schweißfirma',
            'eng' => 'welding company'
        ),
        'material' => array(
            'deu' => 'Material',
            'eng' => 'material'
        ),
        'welding_method' => array(
            'deu' => 'Schweißverfahren',
            'eng' => 'welding method'
        ),
        'examiner' => array(
            'deu' => 'Prüfer',
            'eng' => 'examiner'
        ),
        'auftraggeber' => array(
            'deu' => 'Auftraggeber',
            'eng' => 'customer'
        ),
        'welder' => array(
            'deu' => 'Schweißer',
            'eng' => 'welder'
        ),
    );

    $local = $this->_controller->Lang->Discription();
    $searchoutput = array();

    if (isset($this->_controller->request->data['Reportnumber'])) {
        foreach ($this->_controller->request->data['Reportnumber'] as $_key => $_reportnumber) {
            if (isset($fieldhelparray['Reportnumber'][$_key][$local])) {
                if ($_key == 'report_id') {
                    $this->_controller->Reportnumber->Report->recursive = -1;
                    $report_name = $this->_controller->Reportnumber->Report->find('first', array('fields'=>array('name'),'conditions'=>array('Report.id'=>$_reportnumber)));
                    if (count($report_name) > 0) {
                        $_reportnumber = $report_name['Report']['name'];
                    }
                }
                if ($_key == 'testingmethod_id') {
                    $_reportnumber = ucfirst($_methods[$_reportnumber]);
                }

                $searchoutput[$_key]['field'] = $fieldhelparray['Reportnumber'][$_key][$local];
                $searchoutput[$_key]['value'] = $_reportnumber;
            }
        }
    }
    if (isset($this->_controller->request->data['Reports'])) {
        foreach (preg_grep_key('/(Generally|Specific|Evaluation)$/', $this->_controller->request->data['Reports']) as $model => $values) {
            foreach ($searchfields as $_key => $_searchfields) {
                $Model = 'Report'.$_key.$model;
                foreach ($values as $__key => $__values) {
                    if (isset($xml[$_key]->$Model->$__key)) {
                        $searchoutput[$__key] = array('field' => null, 'value' => null);
                        if (trim($xml[$_key]->$Model->$__key->discription->$local)) {
                            $searchoutput[$__key]['field'] = trim($xml[$_key]->$Model->$__key->discription->$local);
                            $searchoutput[$__key]['value'] = $__values;
                        }
                    }
                }
            }
        }
    }

    $this->_controller->Session->write('GetSearchFields', $searchoutput);

    return $searchoutput;
}

public function CheckDropdownRights($dropdowns, $action)
{
    if (count($dropdowns) == 0) {
        return;
    }

    $TestingmetodArray = explode('_', Inflector::underscore($dropdowns['Dropdown']['model']));
    $verfahren = $TestingmetodArray[1];
    $Verfahren = ucfirst($verfahren);
    $xml = $this->_controller->Xml->DatafromXml($verfahren, 'file', $Verfahren);

    if (!empty($xml['settings']->$dropdowns['Dropdown']['model']->$dropdowns['Dropdown']['field']->select->roll->$action)) {
        foreach ($xml['settings']->$dropdowns['Dropdown']['model']->$dropdowns['Dropdown']['field']->select->roll->$action->children() as $_children) {
            if (trim($_children) == AuthComponent::user('Roll.id')) {
                return true;
                break;
            } else {
                return false;
            }
        }
    } else {
        return true;
    }
}



public function HandlingTime($report)
{
    $report['Reportnumber']['handling_time'] = 0;
    $report['Reportnumber']['handling_time_measure']= __('min', true);

    if ($report['Reportnumber']['print'] > 0) {
        $report['Reportnumber']['created_timestamp'] = strtotime($report['Reportnumber']['created']);
        $report['Reportnumber']['handling_time'] = round(($report['Reportnumber']['print'] - $report['Reportnumber']['created_timestamp']) / 60, 2);

        if ($report['Reportnumber']['handling_time'] < 1) {
            $report['Reportnumber']['handling_time'] = round($report['Reportnumber']['handling_time'] * 60, 2);
            $report['Reportnumber']['handling_time_measure']= __('sec', true);
        }
        if ($report['Reportnumber']['handling_time'] > 60 && $report['Reportnumber']['handling_time'] <= 1440) {
            $report['Reportnumber']['handling_time'] = round($report['Reportnumber']['handling_time'] / 60, 2);
            $report['Reportnumber']['handling_time_measure']= __('h', true);
        }
        if ($report['Reportnumber']['handling_time'] > 1440) {
            $report['Reportnumber']['handling_time'] = round($report['Reportnumber']['handling_time'] / 60 / 24, 2);
            $report['Reportnumber']['handling_time_measure']= __('days', true);
        }
    }

    return $report;
}

public function GetModulData($arrayData, $modelsarray, $reportnumbers)
{
    $xml = $arrayData['settings'];

    foreach ($modelsarray as $m_key => $m_value) {
        foreach ($xml->$m_value->children() as $xml_key => $xml_value) {
            if (!isset($xml_value->select)) {
                continue;
            }
            if (!isset($xml_value->select->moduldata)) {
                continue;
            }
            $moduldataxml = $xml_value->select->moduldata;
            $model = trim($moduldataxml->model);
            $field = trim($moduldataxml->field);

            $this->_controller->loadmodel($model);
            $this->_controller->$model->contain();
            $this->_controller->$model->recursive = 1;

            $conditions = array();

            if (isset($moduldataxml->condition)) {
                $condition = $moduldataxml->condition;
                foreach ($condition->children() as $k_c => $v_c) {
                    $condition = explode(',', $v_c);
                    if ($k_c == 'arrayvalue') {
                        $conditions[$condition[0]] = $reportnumbers['Reportnumber'][$condition[1]];
                    } else {
                        $conditions[$condition[0]] = $condition[1];
                    }
                }
            }
            if (isset($moduldataxml->join->hasbelongtomany) && $moduldataxml->join->hasbelongtomany == 1) {
                $joinmodel =trim($moduldataxml->join->model);
                $manymodel =trim($moduldataxml->join->manymodel);

                $manymodelfielto = trim($moduldataxml->join->manymodelfieldto);

                $manymodelfrom = trim($moduldataxml->join->manymodelfrom);

                $result = $this->_controller->$model->$joinmodel->find('first', array(
                    'conditions' => $conditions,
                    'fields' => array($joinmodel.'.id'),
                ));

                $test = $this->BelongsToManySelected($result, $joinmodel, $model, array($manymodel,$manymodelfielto,$manymodelfrom));

                $values = $this ->_controller->$model->find('all', array('fields'=>array('id',$field),'order'=> $field,'conditions'=>array($model.'.id'=>$test[$model]['selected'])));

                $results [$m_value][$xml_key] =  Hash::extract($values, '{n}.'.$model);
            } else {
                $conditions[$model.'.'.$field.' <>'] = '';
                $this->_controller->$model->recursive = 1;
                $result = $this->_controller->$model->find('all', array('conditions' => $conditions,
                'fields' => array($model.'.id',$model.'.'.$field),'order' => array($model.'.'.$field.' ASC')));
                !empty($result) ? $results [$m_value][$xml_key] = Hash::extract($result, '{n}.'.$model) : '';
            }
        }
    }


    if (!isset($results)) {
        return json_encode(array());
    }
    $results = json_encode($results);
    return $results;
}

public function HistoryData($logs, $lang)
{
    $output = array();

    $id = $this->_controller->request->projectvars['VarsArray'][4];

    $options = array('conditions' => array('Reportnumber.' . $this->_controller->Reportnumber->primaryKey => $id));
    $this->_controller->Reportnumber->recursive = 0;
    $reportnumbers = $this->_controller->Reportnumber->find('first', $options);

    // verschieden Varablen bereitstellen
    $Verfahren = ucfirst($reportnumbers['Testingmethod']['value']);
    $verfahren = $reportnumbers['Testingmethod']['value'];
    $ReportEvaluation = 'Report' . $Verfahren . 'Evaluation';

    $arrayData = $this->_controller->Xml->DatafromXml($verfahren, 'file', $Verfahren);

    $this->_controller->loadModel($ReportEvaluation);
    $Evaluation = $this->_controller->$ReportEvaluation->find('list', array('fields' => array('description','position','id'), 'conditions' => array($ReportEvaluation.'.reportnumber_id' => $id)));

    foreach ($logs as $_key => $_logs) {
        $logs[$_key]['Log']['message_formatiert'] = null;

        switch($_logs['Log']['action']) {
            case 'files':
                $MessageObject = Xml::build($_logs['Log']['message']);
                $logs[$_key]['Log']['message_formatiert'] .= __('File uploaded', true) . ': ' . trim($MessageObject->nbasename);
                break;

            case 'delfile':
                $MessageObject = Xml::build($_logs['Log']['message']);
                $MessageObject = Xml::build($_logs['Log']['message']);
                $logs[$_key]['Log']['message_formatiert'] .= __('File deleted', true) . ': ' . trim($MessageObject->nReportfile->basename);
                break;

            case 'images':
                $MessageObject = Xml::build($_logs['Log']['message']);
                $logs[$_key]['Log']['message_formatiert'] .= __('Image uploaded', true) . ': ' . trim($MessageObject->nbasename);
                break;

            case 'imagediscription':
                $MessageObject = Xml::build($_logs['Log']['message']);
                if (!empty($MessageObject->ndelmessage)) {
                    $logs[$_key]['Log']['message_formatiert'] .= isset($MessageObject->ndelmessage->Reportimage->basename) ? __('Image delete', true) . ': ' . trim($MessageObject->ndelmessage->Reportimage->basename) : __('Image delete', true);
                }
                if (!empty($MessageObject->ndiscription)) {
                    $logs[$_key]['Log']['message_formatiert'] .= __('Image discription', true) . ': ' . trim($MessageObject->ndiscription);
                }
                break;

            case 'add':
                $MessageObject = Xml::build($_logs['Log']['message']);
                $logs[$_key]['Log']['message_formatiert'] .= __('Report created', true) . ' ' . __('on') . ' ' . trim($MessageObject->nmodified);
                break;

            case 'view':
                $MessageObject = Xml::build($_logs['Log']['message']);
                $logs[$_key]['Log']['message_formatiert'] .= __('Template loaded', true);
                break;

            case 'edit':
                $MessageObject = Xml::build($_logs['Log']['message']);
                $__key = key($MessageObject);
                $MessageModel = substr($__key, 1);
                $MessageKey = key($MessageObject->$__key);
                $Message = null;
                $Area = null;

                if (strpos($MessageModel, 'Generally') == true) {
                    $Area = __('Order Data', true) . ' - ';
                }
                if (strpos($MessageModel, 'Specific') == true) {
                    $Area = __('Testing Data', true) . ' - ';
                }

                if (empty($MessageObject->$__key->$MessageKey)) {
                    $logs[$_key]['Log']['message_formatiert'] .= $Area . trim($arrayData['settings']->$MessageModel->$MessageKey->discription->$lang) . ' ' . __('deleted', true);
                }

                if (!empty($MessageObject->$__key->$MessageKey)) {
                    $Message = trim($MessageObject->$__key->$MessageKey);

                    if (trim($arrayData['settings']->$MessageModel->$MessageKey->fieldtype) == 'radio') {
                        $Message = trim($arrayData['settings']->$MessageModel->$MessageKey->radiooption->value[intval($Message)]);
                    }

                    if (isset($arrayData['settings']->$MessageModel->$MessageKey->discription->$lang)) {
                        $logs[$_key]['Log']['message_formatiert'] .= $Area . trim($arrayData['settings']->$MessageModel->$MessageKey->discription->$lang) . ': ' . $Message;
                    }
                }
                break;

            case 'emailreport':
                $MessageObject = Xml::build($_logs['Log']['message']);
                $s_mails = '';
                $mails = array();
                isset($MessageObject->ndata->Reportnumber->Mails) ? $mails = $MessageObject->ndata->Reportnumber->Mails : '';
                if (!empty($mails)) {
                    foreach ($mails as $key => $value) {
                        $s_mails.= $value.' ';
                    }
                    $logs[$_key]['Log']['message_formatiert'] .= __('report sent', true).': '.$s_mails;
                }
                break;

            case 'printrevisions':
                $MessageObject = Xml::build($_logs['Log']['message']);
                $logs[$_key]['Log']['message_formatiert'] .= __('revision history printed', true);
                break;

            case 'revision':
                $MessageObject = Xml::build($_logs['Log']['message']);
                $logs[$_key]['Log']['message_formatiert'] .= __('revision started', true);
                break;

            case 'pdf':
                $MessageObject = Xml::build($_logs['Log']['message']);
                switch (trim($MessageObject->nprint)) {
                    case '1':
                        $logs[$_key]['Log']['message_formatiert'] .= __('Report orginal printed', true);
                        break;
                    case '2':
                        $logs[$_key]['Log']['message_formatiert'] .= __('Report dublicate printed', true);
                        break;
                    case '3':
                        $logs[$_key]['Log']['message_formatiert'] .= __('Report proof printed', true);
                        break;

                    default:
                        $logs[$_key]['Log']['message_formatiert'] .= __('Report printed', true);
                        break;
                }
                break;
            case 'duplicat':
                $MessageObject = Xml::build($_logs['Log']['message']);
                if (trim($MessageObject->nthisId) == trim($MessageObject->nnewId)) {
                    $oldReport = $this->_controller->Reportnumber->find('first', array('conditions' => array('Reportnumber.id' => trim($MessageObject->noldId))));
                    $logs[$_key]['Log']['message_formatiert'] .= __('Duplicated from Report') . ' :' . $oldReport['Reportnumber']['year'] . '-' . $oldReport['Reportnumber']['number'];
                }
                if (trim($MessageObject->nthisId) == trim($MessageObject->noldId)) {
                    $newReport = $this->_controller->Reportnumber->find('first', array('conditions' => array('Reportnumber.id' => trim($MessageObject->nnewId))));
                    $logs[$_key]['Log']['message_formatiert'] .= __('Duplicated in Report') . ' :' . $newReport['Reportnumber']['year'] . '-' . $newReport['Reportnumber']['number'];
                }
                break;
            case 'versionize':

                $oldReport = $this->_controller->Reportnumber->find('first', array('conditions' => array('Reportnumber.id' => $_logs['Log']['current_id'])));

                if ($oldReport['Reportnumber']['new_version_id'] > 0) {
                    $newReport = $this->_controller->Reportnumber->find('first', array('conditions' => array('Reportnumber.id' => $oldReport['Reportnumber']['new_version_id'])));
                    $new = $newReport['Reportnumber']['year'] . "-" . $newReport['Reportnumber']['number'];
                    $old = $oldReport['Reportnumber']['year'] . "-" . $oldReport['Reportnumber']['number'];
                    $logs[$_key]['Log']['message_formatiert'] .= __('A new version of the following report has been created.') . ' ' . $old . ' > ' . $new;
                } elseif ($oldReport['Reportnumber']['old_version_id'] > 0) {
                    $newReport = $this->_controller->Reportnumber->find('first', array('conditions' => array('Reportnumber.id' => $oldReport['Reportnumber']['old_version_id'])));
                    $new = $newReport['Reportnumber']['year'] . "-" . $newReport['Reportnumber']['number'];
                    $old = $oldReport['Reportnumber']['year'] . "-" . $oldReport['Reportnumber']['number'];
                    $logs[$_key]['Log']['message_formatiert'] .= __('This report is an new version of the following report.') . ' ' . $new;
                }

                unset($oldReport);
                unset($newReport);
                unset($new);
                unset($old);

                break;
            case 'delete':
                $logs[$_key]['Log']['message_formatiert'] .= __('Report deleted');
                break;
            case 'editevalution':
                $MessageObject = Xml::build($_logs['Log']['message']);
                $MessageKey = key($MessageObject);
                $MessageModelForm = substr($MessageKey, 1);

                $evaluation_id = null;
                $evaluation_discription = array('description' => null, 'position' => null);
                if (isset($MessageObject->nid) && !empty($MessageObject->nid)) {
                    $evaluation_id = trim($MessageObject->nid);
                    if (is_array($Evaluation[$evaluation_id])) {
                        $evaluation_discription['description'] = key($Evaluation[$evaluation_id]);
                        $evaluation_discription['position'] = '/' . $Evaluation[$evaluation_id][key($Evaluation[$evaluation_id])];
                    }
                }

                $Message = null;

                if (key($MessageObject) == 'ndescription') {
                    $Message = trim($MessageObject->$MessageKey);
                    $logs[$_key]['Log']['message_formatiert'] .= __('Evaluations', true) . ' - ' . trim($arrayData['settings']->$ReportEvaluation->$MessageModelForm->discription->$lang) . ' ' . __('created/edited') . ': ' . $Message;
                } else {
                    $Message = trim($MessageObject->$MessageKey);
                    if (trim($arrayData['settings']->$ReportEvaluation->$MessageModelForm->fieldtype) == 'radio') {
                        $Message = trim($arrayData['settings']->$ReportEvaluation->$MessageModelForm->radiooption->value[intval($Message)]);
                    }

                    $logs[$_key]['Log']['message_formatiert'] .= __('Evaluations', true) . ' - ' . $evaluation_discription['description'] . $evaluation_discription['position'] . ' ' . trim($arrayData['settings']->$ReportEvaluation->$MessageModelForm->discription->$lang) . ': ' . $Message;
                }
                break;
            case 'duplicatevalution':
                $MessageObject = Xml::build($_logs['Log']['message']);
                $MessageKey = key($MessageObject);
                $Message = null;

                if (strpos(trim($MessageObject->$MessageKey->position), ' (') == true) {
                    $Message  = __('Evaluations', true) . ' - ' . trim($MessageObject->$MessageKey->description) . '/' . trim($MessageObject->$MessageKey->position) . ' ' . __('duplicated');
                } else {
                    $Message  = __('Evaluations', true) . ' - ' . trim($MessageObject->$MessageKey->description) . ' ' . __('duplicated');
                }

                $logs[$_key]['Log']['message_formatiert'] .= $Message;
                break;
            case 'deleteevalution':
                $MessageObject = Xml::build($_logs['Log']['message']);
                $Message = null;
                if (!empty($MessageObject->nwelddelete)) {
                    if (isset($MessageObject->nid) && !empty($MessageObject->nid)) {
                        $Message = __('Evaluations', true) . ' - ' . key($Evaluation[trim($MessageObject->nid)]) . ' ' . __('deleted', true);
                    }
                } else {
                    $Message = __('Evaluations', true) . ' - ' . key($Evaluation[trim($MessageObject->nid)]) . '/' .$Evaluation[trim($MessageObject->nid)][key($Evaluation[trim($MessageObject->nid)])] . ' ' . __('deleted', true);
                }
                $logs[$_key]['Log']['message_formatiert'] .= $Message;
                break;
            case 'editableUp':
                $MessageObject = Xml::build($_logs['Log']['message']);
                $evaluation_discription = array('description' => null, 'position' => null);
                if (!empty($MessageObject->nid)) {
                    $evaluation_discription['description'] = key($Evaluation[trim($MessageObject->nid)]);
                    $evaluation_discription['position'] = '/' . $Evaluation[trim($MessageObject->nid)][key($Evaluation[trim($MessageObject->nid)])];
                }
                unset($MessageObject->nid);

                $MessageFieldN = key($MessageObject);
                $MessageField = substr(key($MessageObject), 1);

                if (is_object(trim($arrayData['settings']))) {
                    $Message = __('Evaluations', true) . ' - ' . $evaluation_discription['description'] . $evaluation_discription['position'] . ' ' . trim($arrayData['settings']->$ReportEvaluation->$MessageField->discription->$lang) . ': ' . trim($MessageObject->$MessageFieldN);
                }

                $logs[$_key]['Log']['message_formatiert'] .= $Message;
                break;
            case 'massActions':
                $MessageObject = Xml::build($_logs['Log']['message']);
                $Message = null;

                if (!empty($MessageObject->nmassAction)) {
                    switch($MessageObject->nmassAction) {
                        case 'duplicatevalution':
                            $MessageObjectKey = key($MessageObject->n0);
                            $Message = __('Evaluations', true) . ' - ' . trim($MessageObject->n0->$MessageObjectKey->description) . ' ' . __('duplicated');
                            break;
                        case 'deleteevalution':
                            $FieldKey = key($MessageObject);
                            $Message = __('Evaluations', true) . ' - ' . trim($MessageObject->$FieldKey);
                            break;
                        case 'result_e':
                            $Message = __('Evaluations', true) . ' - ';
                            foreach ($MessageObject as $__key => $__MessageObject) {
                                if ($__key == 'nmassAction') {
                                    continue;
                                }
                                $Message .= trim($__MessageObject) . ' ';
                            }
                            break;
                        case 'result_ne':
                            $Message = __('Evaluations', true) . ' - ';
                            foreach ($MessageObject as $__key => $__MessageObject) {
                                if ($__key == 'nmassAction') {
                                    continue;
                                }
                                $Message .= trim($__MessageObject) . ' ';
                            }
                            break;
                        case 'result_no':
                            $Message = __('Evaluations', true) . ' - ';
                            foreach ($MessageObject as $__key => $__MessageObject) {
                                if ($__key == 'nmassAction') {
                                    continue;
                                }
                                $Message .= trim($__MessageObject) . ' ';
                            }
                            break;
                        case 'errors_remove':
                            $Message = __('Evaluations', true) . ' - ' . __('all error codes removed', true);
                            break;
                    }
                } else {
                    $Message = __('Evaluations', true) . ' - ' . __('Mass action performed', true);
                }
                $logs[$_key]['Log']['message_formatiert'] .= $Message;
                break;
            case 'signtransport':
                $message = Xml::build($_logs['Log']['message']);
                if (trim($message->n0) == 1) {
                    $logs[$_key]['Log']['message_formatiert'] .= __('Signature', true) . ' ' . __('Examiner', true);
                }
                if (trim($message->n0) == 2) {
                    $logs[$_key]['Log']['message_formatiert'] .= __('Signature', true) . ' ' . __('Supervisor', true);
                }
                if (trim($message->n0) == 3) {
                    $logs[$_key]['Log']['message_formatiert'] .= __('Signature', true) . ' ' . __('Third part', true);
                }
                break;
            case 'removeSign':
                $logs[$_key]['Log']['message_formatiert'] .= __('Signatures', true) . ' ' . __('removed', true) . ', ' . __('Report', true) . ' ' . __('reopened', true);
                break;
            case 'status1':
                $message = Xml::build($_logs['Log']['message']);
                if (trim($message->nReportnumber->status) == 1) {
                    $logs[$_key]['Log']['message_formatiert'] .= __('Status', true) . ' 1 ' . __('closed', true);
                }

                break;
            case 'status2':
                $message = Xml::build($_logs['Log']['message']);
                $message = Xml::build($_logs['Log']['message']);
                if (trim($message->nReportnumber->status) == 2) {
                    $logs[$_key]['Log']['message_formatiert'] .= __('Status', true) . ' 2 ' . __('closed', true);
                }
                if (trim($message->nReportnumber->status) == 1) {
                    $logs[$_key]['Log']['message_formatiert'] .= __('Status', true) . ' 2 ' . __('reopened', true);
                }
                if (trim($message->nReportnumber->status) == 0) {
                    $logs[$_key]['Log']['message_formatiert'] .= __('Status', true) . ' 1 ' . __('reopened', true);
                }

                break;
        }
    }

    $output['logs'] = $logs;
    $output['arrayData'] = $arrayData;

    return $output;
}

public function MakeSeed()
{
    list($usec, $sec) = explode(' ', microtime());
    return $sec + $usec * 1000000;
}

public function GetUserInfos($data)
{
    App::uses('CakeTime', 'Utility');

    $max_fail_login = 3; // Anzahl ungültiger Loginversuche
    $max_unlogged_time = 7776000; // maximale Zeit zwischen zwei Logins (in diesem Fall 90 Tage)

    if (Configure::check('MaxFailLogin') == true) {
        $max_fail_login = Configure::read('MaxFailLogin');
    }
    if (Configure::check('MaxUnloggedTime')) {
        $max_unlogged_time = Configure::read('MaxUnloggedTime');
    }

    foreach ($data as $_key => $_data) {
        // Wenn nur ein User im Array steht
        if (is_string($_key) === true) {
            if ($data['User']['counter_fail'] > $max_fail_login) {
                $data['User']['counter_blocked'] = 1;
            } else {
                $data['User']['counter_blocked'] = 0;
            }
            if ((time() - $max_unlogged_time) > $data['User']['lastlogin']) {
                $data['User']['time_blocked'] = 1;
            } else {
                $data['User']['time_blocked'] = 0;
            }
            return $data;
            break;
        } else {
            $data[$_key]['User']['nice_time'] = CakeTime::nice($_data['User']['lastlogin'], $timezone = null, '%d.%m.%Y');

            if ($_data['User']['counter_fail'] > $max_fail_login) {
                $data[$_key]['User']['counter_blocked'] = 1;
            } else {
                $data[$_key]['User']['counter_blocked'] = 0;
            }
            if ((time() - $max_unlogged_time) > $_data['User']['lastlogin']) {
                $data[$_key]['User']['time_blocked'] = 1;
            } else {
                $data[$_key]['User']['time_blocked'] = 0;
            }
        }
    }

    return $data;
}

public function EditOnlyBy($data)
{
    $request = $this->_controller->request->data;

    unset($request['orginal_data']);

    foreach ($request as $_key => $_data) {
        if (!is_array($_data)) {
            unset($request[$_key]);
        }
    }

    $ReportModel = key($request);
    $report_model = explode('_', Inflector::underscore($ReportModel));
    $verfahren = $report_model[1];
    $Verfahren = Inflector::camelize($report_model[1]);

    foreach ($request as $key => $value) {
    }

    $field = key($value);

    $xml = $this->_controller->Xml->DatafromXml($verfahren, 'file', $Verfahren);

    if (empty($xml['settings']->{$ReportModel})) {
        return $data;
    }
    if (empty($xml['settings']->{$ReportModel}->{$field})) {
        return $data;
    }
    if (empty($xml['settings']->{$ReportModel}->{$field}->edit_only_by)) {
        return $data;
    }
    if (empty($xml['settings']->{$ReportModel}->{$field}->edit_only_by->roll)) {
        return $data;
    }

    $Access = false;

    foreach ($xml['settings']->{$ReportModel}->{$field}->edit_only_by->roll->children() as $key => $value) {
        if (AuthComponent::user('roll_id') == trim($value)) {
            $Access = true;
            break;
        }
    }

    if ($Access === false) {
        $data['Reportnumber']['no_rights'] = 1;
    }

    unset($xml);

    return $data;
}

public function EditAfterClosing($data)
{
    if (!isset($this->_controller->request->data['edit_after_closing'])) {
        return $data;
    }
    if ($this->_controller->request->data['edit_after_closing'] != 1) {
        return $data;
    }

    $request = $this->_controller->request->data;

    unset($request['orginal_data']);

    foreach ($request as $_key => $_data) {
        if (!is_array($_data)) {
            unset($request[$_key]);
        }
    }

    $ReportModel = key($request);
    $field = key($request[$ReportModel]);
    $report_model = explode('_', Inflector::underscore($ReportModel));
    $verfahren = $report_model[1];
    $Verfahren = Inflector::camelize($report_model[1]);

    $xml = $this->_controller->Xml->DatafromXml($verfahren, 'file', $Verfahren);

    if (empty($xml['settings']->{$ReportModel})) {
        return $data;
    }
    if (empty($xml['settings']->{$ReportModel}->{$field})) {
        return $data;
    }
    if (empty($xml['settings']->{$ReportModel}->{$field}->edit_after_closing)) {
        return $data;
    }
    if (empty($xml['settings']->{$ReportModel}->{$field}->edit_after_closing->status)) {
        return $data;
    }
    if (empty($xml['settings']->{$ReportModel}->{$field}->edit_after_closing->roll)) {
        return $data;
    }

    $Access = false;

    foreach ($xml['settings']->{$ReportModel}->{$field}->edit_after_closing->roll->children() as $key => $value) {
        if (AuthComponent::user('roll_id') == trim($value)) {
            $Access = true;
            break;
        }
    }

    if ($Access === false) {
        return $data;
    }

    $Access = false;

    foreach ($xml['settings']->{$ReportModel}->{$field}->edit_after_closing->status->children() as $key => $value) {
        if ($data['Reportnumber']['status'] == trim($value)) {
            $Access = true;
            break;
        }
    }

    if ($Access === false) {
        return $data;
    }

    $data['Reportnumber']['status'] = 0;

    unset($xml);

    return $data;
}

public function RevisionCheckTime($data)
{
    if (!Configure::check('RevisionInReport') || Configure::read('RevisionInReport') == false) {
        return $data;
    }

    $id = $data['Reportnumber']['id'];

    if ($data['Reportnumber']['revision_progress'] > 0) {
        if ($this->_controller->Session->check('revision.' . $id) == false) {
            $revisiondata['Reportnumber']['id'] = $id;
            $revisiondata['Reportnumber']['revision_progress'] = 0;
            $this->_controller->Reportnumber->save($revisiondata);
            $data['Reportnumber']['revision_progress'] = 0;
        }

        if ($this->_controller->Session->check('revision.' . $id) == true && time() - Configure::read('RevisionInReportTime') > $data['Reportnumber']['revision_progress']) {
            $revisiondata['Reportnumber']['id'] = $id;
            $revisiondata['Reportnumber']['revision_progress'] = 0;
            $this->_controller->Reportnumber->save($revisiondata);
            $data['Reportnumber']['revision_progress'] = 0;
            $this->_controller->Session->delete('revision.' . $id);
        }
    }

    if ($this->_controller->Session->check('revision')) {
        if ($this->_controller->Session->check('revision.' . $id)) {
            $data['Reportnumber']['revision_write'] = 1;
        } else {
            $data['Reportnumber']['revision_progress'] = 0;
        }
    }
    return $data;
}

public function GetRevisionLinkClass($data)
{
    if (!Configure::check('RevisionInReport') || Configure::read('RevisionInReport') == false) {
        return null;
    }

    $output['class'] = 'versionize_progress';
    $output['title'] = __('Revision is in progress.');

    $id = $data['Reportnumber']['id'];

    if ($this->_controller->Session->check('revision.' . $id) && $data['Reportnumber']['revision_progress'] == $this->_controller->Session->read('revision.' . $id)) {
        $output['title'] = __('Create a revision without duplicating');
        $output['class'] = 'versionize';
    }

    if ($data['Reportnumber']['revision_progress'] > 0 && !$this->_controller->Session->check('revision.' . $id)) {
        if (time() - Configure::read('RevisionInReportTime') > $data['Reportnumber']['revision_progress']) {
            $current_time = time();
            $this->_controller->Session->write('revision.' . $id, $current_time);
            $revisiondata['Reportnumber']['id'] = $id;
            $revisiondata['Reportnumber']['revision_progress'] = $current_time;
            $this->_controller->Reportnumber->save($revisiondata);
            //	 			$data = $this->_controller->Reportnumber->find('first',array('conditions' => array('Reportnumber.id' => $id)));
            $output['class'] = 'versionize';
            $output['title'] = __('Create a revision without duplicating');
            return $output;
        }


        $output['class'] = 'versionize_blocked';
        $output['title'] = __('This function is blocked by another user.');
    }

    if ($data['Reportnumber']['revision_progress'] > 0 && $this->_controller->Session->check('revision.' . $id)) {
        $output['class'] = 'versionize_progress';
        $output['title'] = __('Revision is in progress.');
    }

    if ($data['Reportnumber']['revision_progress'] == 0 && !$this->_controller->Session->check('revision.' . $id)) {
        $output['class'] = 'versionize';
        $output['title'] = __('Create a revision without duplicating');
    }
    return $output;
}

public function CorrectionRevisionTimeout($data)
{
    if ($data['Reportnumber']['revision_progress'] > 0) {
        if ($this->_controller->Session->check('revision.' . $data['Reportnumber']['id']) == true) {
            $data['Reportnumber']['revision_write'] = 1;
        }
        if ($this->_controller->Session->check('revision.' . $data['Reportnumber']['id']) == false) {
            if (time() - $data['Reportnumber']['revision_progress'] > Configure::read('RevisionInReportTime')) {
                $data['Reportnumber']['revision_progress'] = 0;
                $this->_controller->Reportnumber->id = $data['Reportnumber']['id'];
                $this->_controller->Reportnumber->saveField('revision_progress', '0');
                $this->_controller->Reportnumber->id = false;
            }
        }
    }

    return $data;
}

public function SaveRevision($data, $TransportArray, $action, $massaction = false, $value = false)
{
    $output = array();

    if (!Configure::check('RevisionInReport') || Configure::read('RevisionInReport') == false) {
        return;
    }

    $this->_controller->loadModel('Revision');
    $input = array_merge($TransportArray, $data['Reportnumber']);
    $id = $input['id'];
    $input['reportnumber_id'] = $id;
    if ($action != false) {
        $input['action'] = $action;
    }
    $input['user_id'] = $this->_controller->Auth->user('id');

    unset($input['id']);
    unset($input['created']);
    unset($input['modified']);

    if ($massaction != false) {
        $id_array = explode(',', $input['last_value']);
        $Model = 'Report' . ucfirst($data['Testingmethod']['value']) . 'Evaluation';

        // Array wird sortiert, um (falls erforderlich) die erzeugten Datensätze
        // mit den vorhandenen zuordnen zu können
        asort($id_array);

        foreach ($id_array as $_id_array) {
            $options = array('conditions' => array($Model . '.id' => trim($_id_array)));
            $LastValue = $this->_controller->$Model->find('first', $options);
            
            if (count($LastValue) > 0) {

                if (isset($LastValue[$Model][$massaction])) {
                    $input['last_value'] = $LastValue[$Model][$massaction];
                    $input['table_id'] = $LastValue[$Model]['id'];
                    $input['row'] = $massaction;
                    $input['this_value'] = $value;
                } else {
                    $input['last_value'] = $LastValue[$Model]['id'];
                    $input['table_id'] = 0;
                    $input['row'] = '';
                    $input['this_value'] = '';
                }

                if(
                    $this->_controller->request->data['Reportnumber']['MassSelect'] == 'deleteevalution' || 
                    $this->_controller->request->data['Reportnumber']['MassSelect'] == 'duplicatevalution' 
                ){
                    $input['this_value'] = $LastValue[$Model]['description'] . '/' . $LastValue[$Model]['position'];
                }
    
                if ($input['last_value'] != $input['this_value']) {
                    $this->_controller->Revision->create();
                    if ($this->_controller->Revision->save($input)) {
                        $output[] = $this->_controller->Revision->getLastInsertId();
                    }
                }
            }
        }
    } elseif ($massaction == false) {
        $this->_controller->Revision->create();
        $this->_controller->Revision->save($input);
    }

    return $output;
}

public function UpdateRevision($RevisiosID, $UpdateID, $Action)
{
    if (!Configure::check('RevisionInReport') || Configure::read('RevisionInReport') == false) {
        return;
    }

    if (!is_array($RevisiosID)) {
        return;
    }
    if (!is_array($UpdateID)) {
        return;
    }

    if (count($RevisiosID) == 0) {
        return;
    }
    if (count($UpdateID) == 0) {
        return;
    }

    $this->_controller->loadModel('Revision');

    foreach ($RevisiosID as $_key => $_RevisiosID) {
        if ($_RevisiosID == 0) {
            continue;
        }

        $this->_controller->Revision->updateAll(
            array('Revision.table_id' => $UpdateID[$_key]),
            array('Revision.id' => $_RevisiosID)
        );
    }
}

public function CheckRevisionValues($reportnumbers, $modelsarray, $settings = null, $tbid = null)
{
    $revisions = array();

    $this->_controller->loadModel('Revision');
    $this->_controller->Revision->recursive = -1;
    foreach ($modelsarray as $model => $value) {
        if (!empty($settings)) {
            $ReportSetting = $settings->$value;

            foreach ($ReportSetting->children() as $field => $fieldvalue) {
                if ($fieldvalue->model == $value) {
                    $options = array(
                        'conditions' => array(
                            'Revision.reportnumber_id' => $reportnumbers['Reportnumber'] ['id'],
                            'Revision.order_id' => $reportnumbers['Reportnumber']['order_id'],
                            'Revision.topproject_id' =>$reportnumbers['Reportnumber']['topproject_id'],
                            'Revision.report_id' =>$reportnumbers['Reportnumber']['report_id'],
                            'Revision.model' => $value,

                            'Revision.row' => trim($fieldvalue->key)

                        )
                    );
                    if (!empty($tbid)) {
                        $options['conditions'] ['Revision.table_id'] = $tbid;
                    }
                    $revision = $this->_controller->Revision->find('all', $options);

                    foreach ($revision as $_rkey => $_rvalue) {
                        $revisions[$value][$_rvalue['Revision']['table_id']][trim($fieldvalue->key)] = 1 ;
                    }
                }
            }
        } else {
            $options = array(
                'conditions' => array(
                    'Revision.reportnumber_id' => $reportnumbers['Reportnumber'] ['id'],
                    'Revision.order_id' => $reportnumbers['Reportnumber']['order_id'],
                    'Revision.topproject_id' =>$reportnumbers['Reportnumber']['topproject_id'],
                    'Revision.report_id' =>$reportnumbers['Reportnumber']['report_id'],
                    'Revision.model' => $value,



                )
            );

            if (!empty($tbid)) {
                $options['conditions'] ['Revision.table_id'] = $tbid;
            }
            $revision = $this->_controller->Revision->find('all', $options);

            foreach ($revision as $_rkey => $_rvalue) {
                $revisions [$value] [$_rvalue['Revision']['table_id']] = 1 ;
            }
        }
    }

    return $revisions;
}

public function CreateRevisionsList($reportnumber, $data, $xml)
{
    $output = array();

    $this->_controller->loadModel('Revision');
    $this->_controller->Revision->recursive = -1;
    $Revision = $this->_controller->Revision->find('all', array('conditions'=>array('Revision.reportnumber_id' => $reportnumber['Reportnumber']['id'])));

    foreach ($Revision as $_key => $_Revision) {
        $model = trim($_Revision['Revision']['model']);
        $row = trim($_Revision['Revision']['row']);
        $revision = $_Revision['Revision']['revision'];
        $action = trim($_Revision['Revision']['action']);
        $output['overview'][$model][$row] = true;
        $output['list'][$revision][$model][$row] = array();

        switch($action) {
            case 'edit':

                $last_value =$_Revision['Revision']['last_value'];
                $this_value =$_Revision['Revision']['this_value'];

                if (!empty($xml->$model->$row->fieldtype)) {
                    if (!empty($xml->$model->$row->radiooption)) {
                        $last_value = intval($last_value);
                        $this_value = intval($this_value);
                        $last_value = trim($xml->$model->$row->radiooption->value[$last_value]);
                        $this_value = trim($xml->$model->$row->radiooption->value[$this_value]);
                    } else {
                        if ($this_value == 0) {
                            $this_value = 'no/nein';
                        }
                        if ($this_value == 1) {
                            $this_value = 'ja/yes';
                        }
                    }
                }

                $output['list'][$revision][$model][$row][$action]['last_value'] = $last_value;
                $output['list'][$revision][$model][$row][$action]['this_value'] = $this_value;
                break;
            case 'duplicatevalution/weld':
                $output['list'][$revision][$model][$action]['this_value'] = __('Weld duplicated', true);
                break;
        }
    }

    $this->_controller->set('RevisionsList', $output);

    return $output;
}

public function RevisionDeleteSignatory($reportnumber)
{
    if (Configure::check('WriteSignatory') == false) {
        return false;
    }
    if (Configure::check('RevisionSignaturDelete') == false) {
        return false;
    }
    if (Configure::read('RevisionSignaturDelete') == false) {
        return false;
    }
    if (Configure::read('WriteSignatory') == false) {
        return false;
    }

    $id = $this->_controller->request->projectvars['VarsArray'][4];

    $revisiondata['Reportnumber']['id'] = $id;
    $revisiondata['Reportnumber']['print'] = 0;

    if (!$this->_controller->Reportnumber->save($revisiondata)) {
        return false;
    }

    $report_id_chiper = bin2hex(Security::cipher($reportnumber['Reportnumber']['id'], Configure::read('SignatoryHash')));
    $project_id_chiper = bin2hex(Security::cipher($reportnumber['Reportnumber']['topproject_id'], Configure::read('SignatoryHash')));
    $path = Configure::read('SignatoryPfad') . $project_id_chiper . DS . $report_id_chiper . DS;
    $folder = new Folder($path);

    $this->_controller->loadModel('Signs');
    $Signs = $this->_controller->Signs->find('all', array('conditions'=>array('Signs.reportnumber_id'=>$reportnumber['Reportnumber']['id'])));

    if (count($Signs) == 0) {
        return false;
    }

    if ($this->_controller->Signs->deleteAll(array('Signs.reportnumber_id' => $reportnumber['Reportnumber']['id']))) {
        if ($folder->delete()) {
            return true;
        } else {
            return false;
        }
    } else {
        return false;
    }
}

public function RequiredFieldDeleteSignatory($reportnumber, $field_for_saveField)
{
    if (Configure::read('WriteSignatory') == false) {
        return false;
    }
    if (Configure::read('SignatoryDeleteRequired') == false) {
        return false;
    }
    $fieldsarray = array();
    $fields = Configure::read('SignatoryDeleteRequiredFields');
    $fieldsarray = explode('.', $fields);

    foreach ($fieldsarray as $fa => $fa_v) {
        if ($field_for_saveField == $fa_v) {
            $id = $this->_controller->request->projectvars['VarsArray'][4];

            $report_id_chiper = bin2hex(Security::cipher($reportnumber['Reportnumber']['id'], Configure::read('SignatoryHash')));
            $project_id_chiper = bin2hex(Security::cipher($reportnumber['Reportnumber']['topproject_id'], Configure::read('SignatoryHash')));
            $path = Configure::read('SignatoryPfad') . $project_id_chiper . DS . $report_id_chiper . DS;
            $folder = new Folder($path);

            $this->_controller->loadModel('Signs');
            $Signs = $this->_controller->Signs->find('all', array('conditions'=>array('Signs.reportnumber_id'=>$reportnumber['Reportnumber']['id'])));

            if (count($Signs) == 0) {
                return false;
            }

            if ($this->_controller->Signs->deleteAll(array('Signs.reportnumber_id' => $reportnumber['Reportnumber']['id']))) {
                if ($folder->delete()) {
                    return true;
                } else {
                    return false;
                }
            } else {
                return false;
            }
        } else {
            return false;
        }
    }
}


public function TestincompCascadeCheck($Data)
{
    $orderID = $Data['Order']['id'];

    if ($orderID == 0) {
        return $Data;
    }

    $this->_controller->loadModel('OrdersTestingcomps');
    $this->_controller->loadModel('Testingcomp');

    $this->_controller->Testingcomp->recursive = -1;

    $TestingcompIds = $this->_controller->OrdersTestingcomps->find('list', array('fields' => array('testingcomp_id','testingcomp_id'),'conditions' => array('OrdersTestingcomps.order_id' => $orderID)));

    if (count($TestingcompIds) == 0) {
        return $Data;
    }

    $Testingcomps = $this->_controller->Testingcomp->find('list', array('fields' => array('id','name'),'conditions' => array('Testingcomp.id' => $TestingcompIds)));

    if (count($Testingcomps) == 0) {
        return $Data;
    }

    $Data['Testingcomp'] = $Testingcomps;

    return $Data;
}

public function BelongsToManySelected($Data, $MainModel, $FromModel, $ManyModelArray)
{
    $ManyModel = $ManyModelArray[0];
    $ManyFieldTo = $ManyModelArray[1];
    $ManyModelFrom = $ManyModelArray[2];

    if (!isset($Data[$MainModel]['id'])) {
        return $Data;
    }

    $ID = $Data[$MainModel]['id'];

    if ($ID == 0) {
        return $Data;
    }

    $this->_controller->loadModel($ManyModel);
    $this->_controller->loadModel($FromModel);

    $this->_controller->$FromModel->recursive = -1;

    $TestingcompIds = $this->_controller->$ManyModel->find('list', array('fields' => array($ManyFieldTo,$ManyFieldTo),'conditions' => array($ManyModel.'.'.$ManyModelFrom => $ID)));

    if (count($TestingcompIds) == 0) {
        $Data[$FromModel]['selected'] = array();
        return $Data;
    }

    $Testingcomps = $this->_controller->$FromModel->find('list', array('fields' => array('id','id'),'conditions' => array($FromModel.'.id' => $TestingcompIds)));
    if (count($Testingcomps) == 0) {
        $Data[$FromModel]['selected'] = array();

        return $Data;
    }

    $Data[$FromModel]['selected'] = $Testingcomps;
    return $Data;
}

public function TestincompSelected($Data)
{
    $orderID = $Data['Order']['id'];

    if ($orderID == 0) {
        return $Data;
    }

    $this->_controller->loadModel('OrdersTestingcomps');
    $this->_controller->loadModel('Testingcomp');

    $this->_controller->Testingcomp->recursive = -1;

    $TestingcompIds = $this->_controller->OrdersTestingcomps->find('list', array('fields' => array('testingcomp_id','testingcomp_id'),'conditions' => array('OrdersTestingcomps.order_id' => $orderID)));

    if (count($TestingcompIds) == 0) {
        $Data['Testingcomp']['selected'] = array();
        return $Data;
    }

    $Testingcomps = $this->_controller->Testingcomp->find('list', array('fields' => array('id','id'),'conditions' => array('Testingcomp.id' => $TestingcompIds)));

    if (count($Testingcomps) == 0) {
        $Data['Testingcomp']['selected'] = array();
        return $Data;
    }

    $Data['Testingcomp']['selected'] = $Testingcomps;

    return $Data;
}

public function SelectOrderByTestingcomp()
{
    $this->_controller->loadModel('Order');
    $this->_controller->loadModel('OrdersTestingcomps');
    $this->_controller->Order->recursive = -1;
    $OrdersTestingcomps = $this->_controller->OrdersTestingcomps->find('all', array('conditions' => array('OrdersTestingcomps.testingcomp_id' => $this->_controller->Auth->user('testingcomp_id'))));

    if (count($OrdersTestingcomps) == 0) {
        return array();
    }

    $OrdersTestingcomps = Hash::extract($OrdersTestingcomps, '{n}.OrdersTestingcomps.order_id');

    $Orders = $this->_controller->Order->find('list', array('conditions' => array('Order.id' => $OrdersTestingcomps)));

    if (count($Orders) == 0) {
        return array();
    }

    return $Orders;
}

// soll durch Component/ReportComponent GetReportData ersetzt werden
public function GetReportData($reportnumber, $Models)
{
    $verfahren = $reportnumber['Testingmethod']['value'];
    $Verfahren = ucfirst($verfahren);
    $this->_controller->request->verfahren = $verfahren;
    $this->_controller->request->Verfahren = $Verfahren;
    $ReportGenerally = 'Report'.$Verfahren.'Generally';
    $ReportSpecific = 'Report'.$Verfahren.'Specific';
    $ReportEvaluation = 'Report'.$Verfahren.'Evaluation';
    $ReportArchiv = 'Report'.$Verfahren.'Archiv';
    $ReportSettings = 'Report'.$Verfahren.'Setting';
    $ReportPdf = 'Report'.$Verfahren.'Pdf';

    $ReportArray = array($ReportGenerally,$ReportSpecific,$ReportEvaluation,$ReportArchiv,$ReportSettings,$ReportPdf);
    $this->_controller->request->tablenames = $ReportArray;

    $ReportArray = array($ReportGenerally,$ReportSpecific,$ReportArchiv,$ReportEvaluation);

    foreach ($ReportArray as $key => $value) {
        $this->_controller->loadModel($value);
    }

    $optionsGenerally = array('conditions' => array($ReportGenerally.'.reportnumber_id' => $reportnumber['Reportnumber']['id']));
    $Generally = $this->_controller->$ReportGenerally->find('first', $optionsGenerally);

    $optionsSpecific = array('conditions' => array($ReportSpecific.'.reportnumber_id' => $reportnumber['Reportnumber']['id']));
    $Specific = $this->_controller->$ReportSpecific->find('first', $optionsSpecific);

    $optionsArchiv = array('conditions' => array($ReportArchiv.'.reportnumber_id' => $reportnumber['Reportnumber']['id']));
    $Archiv = $this->_controller->$ReportArchiv->find('first', $optionsArchiv);

    if (!empty($Archiv[$ReportArchiv]['data'])) {
        $Archiv[$ReportArchiv]['data'] = $this->_controller->Xml->XmltoArray($Archiv[$ReportArchiv]['data'], 'string', null, null);
    } else {
        $Archiv[$ReportArchiv]['data'] = array();
    }

    $Archiv[$ReportArchiv]['data'] =	 json_encode($Archiv[$ReportArchiv]['data']);
    $Archiv[$ReportArchiv]['data'] =	 json_decode($Archiv[$ReportArchiv]['data'], true);

    $optionsEvaluation = array('conditions' => array($ReportEvaluation.'.reportnumber_id' => $reportnumber['Reportnumber']['id'],$ReportEvaluation.'.result' => array(0,2)));
    $Evaluation = $this->_controller->$ReportEvaluation->find('all', $optionsEvaluation);

    $reportnumber = array_merge($reportnumber, $Generally, $Specific, $Archiv);
    $reportnumber[$ReportEvaluation] = $Evaluation;

    return $reportnumber;
}

public function ModulChildData($settings, $reportmodel, $requestdata, $moduldataxml, $model, $field, $value)
{
    $this->_controller->loadModel($model);
    $returnarray =  $this->_controller->$model->find('first', array('conditions'=>array($model.'.id' => $value)));
    $returnarray = $returnarray [$model];
    if (isset($moduldataxml->childs)) {
        foreach ($moduldataxml->childs->value as $childkey => $childvalue) {
            $childvalue = trim($childvalue);
            $childvaluefield = Inflector::camelize($childvalue);
            $requestdata[$reportmodel][$childvaluefield]['value'] = $returnarray [trim($childvalue)];

            if (isset($settings->$reportmodel->$childvalue->radiooption)) {
                $requestdata[$reportmodel][$childvaluefield]['value'] = trim($settings->$reportmodel->$childvalue->radiooption->value[$returnarray [trim($childvalue)]]);
            }
        }
    }
    return $requestdata;
}

public function GetWelderTest($optionscont, $testingmethods, $projectID, $cascadeID, $orderID, $reportID)
{
    $testingreportsvtst = array();
    foreach ($testingmethods as $tm => $tv) {
        if ($tv ['Testingmethod']['value'] == 'vtst') {
            $options = array(
                'Reportnumber.topproject_id' => $projectID,
                'Reportnumber.cascade_id' => $cascadeID,
                'Reportnumber.order_id' => $orderID,
                'Reportnumber.report_id' => $reportID,
                'Reportnumber.testingmethod_id' => $tv['Testingmethod']['id'],
            );
            break;
        }
    }

    if (empty($options)) {
        return;
    }


    $this->_controller->Reportnumber->recursive = 0;

    $testingreports = $this->_controller->Reportnumber->find('all', array('conditions'=>array($optionscont)));


    $testingreportsvtst = $this->_controller->Reportnumber->find('all', array('conditions'=>array($options)));
    $testingreportsmerge =  array_merge($testingreports, $testingreportsvtst);
    $reports = Hash::extract($testingreportsmerge, '{n}.Reportnumber.id');
    $this->_controller->paginate = array(
        'conditions' => array('Reportnumber.id'=>$reports),
        'order' => array('id' => 'desc'),
        'limit' => 25
    );

    $this->_controller->Reportnumber->recursive = 0;
    $testingreportsall = $this->_controller->paginate('Reportnumber');

    return $testingreportsall;
}

public function WeldTypes($testingreport, $xml)
{
    // Wenn ein Prüfbericht fehlerfrei angelegt wurde,
    // sollte dieser Wert niemals leer sein
    if (empty($testingreport['Testingmethod']['value'])) {
        return $testingreport;
    }

    if (Configure::read('WeldManager.show') && !isset($xml[$testingreport['Testingmethod']['value']])) {
        $xml[$testingreport['Testingmethod']['value']] = $this->_controller->Xml->DatafromXml($testingreport['Testingmethod']['value'], 'file', ucfirst($testingreport['Testingmethod']['value']));
    }
    $_model = 'Report'.ucfirst($testingreport['Testingmethod']['value']).'Generally';
    $_modelspecific = 'Report'.ucfirst($testingreport['Testingmethod']['value']).'Specific';
    if (!$this->_controller->loadModel($_model)) {
        return $testingreport;
    }
    if (!$this->_controller->loadModel($_modelspecific)) {
        return $testingreport;
    }
    $_model = $this->_controller->$_model->find('first', array('conditions'=>array($_model.'.reportnumber_id'=>$testingreport['Reportnumber']['id'])));
    $_modelspecific = $this->_controller->$_modelspecific->find('first', array('conditions'=>array($_modelspecific.'.reportnumber_id'=>$testingreport['Reportnumber']['id'])));
    $testingreport['Generally'] = reset($_model);
    $testingreport['Specific'] = reset($_modelspecific);


    $_model = 'Report'.ucfirst($testingreport['Testingmethod']['value']).'Evaluation';

    $this->_controller->loadModel($_model);

    if ($this->_controller->$_model->hasField('result')) {
        $result_array = array(0,1,2);

        foreach ($result_array as $_result_array) {
            $result = $this->_controller->$_model->find(
                'list',
                array(
                'limit' => 1,
                'fields' => array($_model.'.result'),
                'conditions' => array(
                    $_model.'.reportnumber_id' => $testingreport['Reportnumber']['id'],
                    $_model.'.result' => $_result_array,
                    $_model.'.deleted' => 0
                )
            )
            );

            $testingreport['weld_types'][$_result_array] = count($result);
        }
    } else {
        $testingreport['weld_types'] = array();
    }

    return $testingreport;
}

public function SetDateFromSignatory($reportnumberid, $verfahren, $Signatory)
{
    if (!empty($reportnumberid) && !empty($verfahren)) {
        $GenModel = 'Report'.$verfahren.'Generally';

        $this->_controller->loadmodel($GenModel);

        $reportGenerally = $this->_controller->$GenModel->find('first', array('conditions' => array($GenModel.'.reportnumber_id'=>$reportnumberid)));

        $timestamp = time();
        $datenow = date("Y-m-d", $timestamp);

        if ($Signatory['Signatory'] == 1) {
            $reportGenerally[$GenModel] ['examiner_date'] = $datenow;

            $this->_controller->$GenModel->id =$reportGenerally[$GenModel]['id'];
            if ($this->_controller->$GenModel->saveField('examiner_date', $datenow)) {
            } else {
                echo'Der Wert konnte nicht gespeichert werden!';
                return;
            }
        } elseif ($Signatory['Signatory'] == 2) {
            $this->_controller->$GenModel->id =$reportGenerally[$GenModel]['id'];
            if ($this->_controller->$GenModel->saveField('supervisor_date', $datenow)) {
            } else {
                echo'Der Wert konnte nicht gespeichert werden!';
                return;
            }
        } elseif ($Signatory['Signatory'] == 3) {
            $this->_controller->$GenModel->id =$reportGenerally[$GenModel]['id'];
            if ($this->_controller->$GenModel->saveField('supervisor_company_date', $datenow)) {
            } else {
                echo'Der Wert konnte nicht gespeichert werden!';
                return;
            }
        } elseif ($Signatory['Signatory'] == 4) {
            $this->_controller->$GenModel->id =$reportGenerally[$GenModel]['id'];
            if ($this->_controller->$GenModel->saveField('third_part_date', $datenow)) {
            } else {
                echo'Der Wert konnte nicht gespeichert werden!';
                return;
            }
        }
    }

    $this->Archiv($reportnumberid, $verfahren);
}

public function SetDataForWelderTest($welder_id, $reportnumber, $xml, $verfahren)
{
    $data = array();

    if (!isset($this->_controller->request->data['welder_id'])) {
        return $data;
    }
    if ($this->_controller->request->data['welder_id'] == 0) {
        return $data;
    }

    $welder_id = $this->_controller->Sicherheit->Numeric($this->_controller->request->data['welder_id']);

    if ($welder_id == 0) {
        return $data;
    }

    $Model = 'Report' .  ucfirst($verfahren). 'Generally';
    $ModelValue = $this->_controller->$Model->find('first', array('conditions' => array($Model . '.reportnumber_id' => $reportnumber['Reportnumber']['id'])));
    if (count($ModelValue) == 0) {
        return $data;
    }

    $data['id'] = $ModelValue[$Model]['id'];

    if (empty($xml->$Model->welder->select->moduldata->childs->value)) {
        return $data;
    }
    if (count($xml->$Model->welder->select->moduldata->childs->value) == 0) {
        return $data;
    }

    $fields = $xml->$Model->welder->select->moduldata->childs->value;

    $this->_controller->loadModel('Welder');
    $Welder = $this->_controller->Welder->find('first', array('conditions' => array('Welder.id' => $welder_id)));

    if (count($Welder) == 0) {
        return $data;
    }

    foreach ($fields as $_key => $_data) {
        $field = trim($_data);
        $value = $Welder['Welder'][$field];

        if (!empty($xml->$Model->$field->radiooption->value)) {
            $radiooption = $xml->$Model->$field->radiooption->value;
            $value = trim($radiooption[$value]);
        }

        $data[$field] = $value;
    }

    $data['welding_company'] = $Welder['Testingcomp']['name'];
    $data['welder'] = $Welder['Welder']['identification'];

    if ($this->_controller->$Model->save($data)) {
        return $data;
    } else {
        return array();
    }
}

public function GetWeldPagination($data, $ReportEvaluation, $weldedit)
{
    // Die zugehörigen Prüfergebnisse werden aus der Datenbank geholt
    $pagination_links = array();

    $projectID = $this->_controller->request->projectvars['VarsArray'][0];
    $cascadeID = $this->_controller->request->projectvars['VarsArray'][1];
    $orderID = $this->_controller->request->projectvars['VarsArray'][2];
    $reportID = $this->_controller->request->projectvars['VarsArray'][3];
    $id = $this->_controller->request->projectvars['VarsArray'][4];

    if ($weldedit == 0) {
        $optionsSorting = array(
            'conditions' => array($ReportEvaluation.'.reportnumber_id' => $id, 'deleted'=>0),
            'order' => array(
                $ReportEvaluation.'.sorting' => 'asc',
                $ReportEvaluation.'.description' => 'asc',
                $this->_controller->$ReportEvaluation->primaryKey=>'asc'
            )
        );

        $pagination_links['next']['desc'] = __('Next testing area', true);
        $pagination_links['prev']['desc'] = __('Previous test area', true);
    }

    if ($weldedit == 1) {
        $optionsSorting = array(
            'fields' => array('id','description'),
            'conditions' => array(
                $ReportEvaluation.'.reportnumber_id' => $id,
                'deleted'=>0
            ),
            'order' => array(
                $ReportEvaluation.'.sorting' => 'asc',
                $ReportEvaluation.'.description' => 'asc',
                $this->_controller->$ReportEvaluation->primaryKey=>'asc'
            )
        );

        $pagination_links['next']['desc'] = __('Next weld', true);
        $pagination_links['prev']['desc'] = __('Previous weld', true);
    }

    $pagination_links['next']['id'] = 0;
    $pagination_links['prev']['id'] = 0;

    $pagination = $this->_controller->$ReportEvaluation->find('list', $optionsSorting);

    if (isset($data[$ReportEvaluation]['position'])) {
        $hasPositionColum = true;

        $this->_controller->set('hasPositionColum', true);

        $optionsSortingOverview = array(
            'fields' => array('id','position','description'),
            'conditions' => array(
                $ReportEvaluation.'.reportnumber_id' => $id,
                'deleted'=>0
            ),
            'order' => array(
                $ReportEvaluation.'.sorting' => 'asc',
                $ReportEvaluation.'.description' => 'asc',
                $ReportEvaluation.'.position' => 'asc',
                $this->_controller->$ReportEvaluation->primaryKey=>'asc'
            )
        );
    } else {
        $hasPositionColum = false;

        $this->_controller->set('hasPositionColum', false);

        $optionsSortingOverview = array(
            'fields' => array('description','id'),
            'conditions' => array(
                $ReportEvaluation.'.reportnumber_id' => $id,
                'deleted'=>0
            ),
            'order' => array(
                $ReportEvaluation.'.sorting' => 'asc',
                $ReportEvaluation.'.description' => 'asc',
                $this->_controller->$ReportEvaluation->primaryKey=>'asc'
            )
        );
    }

    $evaluation = $this->_controller->$ReportEvaluation->find('list', $optionsSortingOverview);

    if (count($evaluation) == 0) {
        return array();
    }

    $paginationOverview = array();

    unset($optionsSortingOverview['fields']);
    unset($optionsSortingOverview['order']);

    $optionsSortingOverview['fields'] = array('id','result');

    $paginationOverview['results'] = array();

    foreach ($evaluation as $key => $value) {
        if (count($value) == 0) {
            continue;
        }

        foreach ($value as $_key => $_value) {
            $optionsSortingOverview['conditions'][$ReportEvaluation . '.description'] = $key;
            if ($hasPositionColum === true) {
                $optionsSortingOverview['conditions'][$ReportEvaluation . '.position'] = $_value;
            }

            $Result = $this->_controller->$ReportEvaluation->find('list', $optionsSortingOverview);

            if (count($Result) != 1) {
                continue;
            }

            $paginationOverview['results']['position'][$key][key($Result)] = $Result[key($Result)];

            unset($optionsSortingOverview['conditions'][$ReportEvaluation . '.description']);
            if ($hasPositionColum === true) {
                unset($optionsSortingOverview['conditions'][$ReportEvaluation . '.position']);
            }
        }
        if (is_array($paginationOverview['results']['position'][$key])) {
            if (array_search(2, $paginationOverview['results']['position'][$key]) !== false) {
                $paginationOverview['results']['discription'][$key] = 2;
                continue;
            }
            if (array_search(0, $paginationOverview['results']['position'][$key]) !== false) {
                $paginationOverview['results']['discription'][$key] = 0;
                continue;
            }
            if (array_search(1, $paginationOverview['results']['position'][$key]) !== false) {
                $paginationOverview['results']['discription'][$key] = 1;
            }
        }
    }
    $paginationOverview['real_struktur'] = $evaluation;

    if (isset($data[$ReportEvaluation]['position'])) {
        $paginationOverview['current_weld'][$data[$ReportEvaluation]['description']] = $data[$ReportEvaluation]['id'];
        $paginationOverview['current_position'][$data[$ReportEvaluation]['id']] = $data[$ReportEvaluation]['position'];

        $prev_array = array();
        $next_array = array();

        foreach ($paginationOverview['real_struktur'] as $_key => $_paginationOverview) {
            if (is_array($_paginationOverview)) {
                $_positions = $_paginationOverview;
                $position_ = array_splice($_paginationOverview, 1);
                $paginationOverview['weld_struktur'][$_key] = $_paginationOverview[key($_paginationOverview)];
                $paginationOverview['weld_struktur_id'][$_key] = key($paginationOverview['real_struktur'][$_key]);
                $paginationOverview['paging_weld'][$paginationOverview['weld_struktur_id'][$_key]]['current_weld'] = array($paginationOverview['weld_struktur_id'][$_key] => $_key);
                if (isset($_prevkey)) {
                    $paginationOverview['paging_weld'][$paginationOverview['weld_struktur_id'][$_key]]['prev_weld'] = $prev_array;
                    $paginationOverview['paging_weld'][$_key_for_next]['next_weld'] = array($paginationOverview['weld_struktur_id'][$_key] => $_key);
                }
                $prev_array = array($paginationOverview['weld_struktur_id'][$_key] => $_key);
                foreach ($_positions as $__key => $__paginationOverview) {
                    $paginationOverview['position_struktur'][$__key] = $__paginationOverview;
                    $paginationOverview['position_struktur_id'][] = $__key;
                    $paginationOverview['position_struktur_weld'][$__key] = array($_key,$__paginationOverview);
                    $paginationOverview['paging_position'][$__key]['current_position'] = array($__key => $__paginationOverview);

                    if (isset($_prevvalue_position)) {
                        $paginationOverview['paging_position'][$__key]['prev_position'] = array($_prevkey_position => $_prevvalue_position);
                    }
                    if (isset($_prevvalue_position)) {
                        $paginationOverview['paging_position'][$_prevkey_position]['next_position'] = array($__key => $__paginationOverview);
                    }
                    $_prevvalue_position = $__paginationOverview;
                    $_prevkey_position = $__key;
                }
            }

            $_key_for_next = $paginationOverview['weld_struktur_id'][$_key];
            $_prevvalue = $paginationOverview['weld_struktur_id'][$_key];
            $_prevkey = $_key;
        }
    } elseif (!isset($data[$ReportEvaluation]['position'])) {
        if (isset($data[$ReportEvaluation])) {
            $paginationOverview['current_weld'][$data[$ReportEvaluation]['description']] = isset($data[$ReportEvaluation]['id']) && !empty($data[$ReportEvaluation]['id']) ? $data[$ReportEvaluation]['id'] : '';
        }

        $prev_array = array();
        $next_array = array();
        $paginationOverview['weld_struktur_id'] = array_flip($paginationOverview['real_struktur']);
        $paginationOverview['paging_weld'] = array();

        foreach ($paginationOverview['real_struktur'] as $_key => $_paginationOverview) {
            $paginationOverview['paging_weld'][$_paginationOverview]['current_weld'] = array($_paginationOverview => $_key);
            if (isset($_prevkey)) {
                $paginationOverview['paging_weld'][$_paginationOverview]['prev_weld'] = array($_prevvalue => $_prevkey);
            }
            if (isset($_prevkey)) {
                $paginationOverview['paging_weld'][$_prevvalue]['next_weld'] = array($_paginationOverview => $_key);
            }
            $_prevvalue = $_paginationOverview;
            $_prevkey = $_key;
        }
    }

    return $paginationOverview;
}

public function CheckFileExists($data, $savePath)
{
    if (!is_array($data)) {
        return $data;
    }
    if (count($data) == 0) {
        return $data;
    }

    $projectID = $this->_controller->request->projectvars['VarsArray'][0];
    $cascadeID = $this->_controller->request->projectvars['VarsArray'][1];
    $orderID = $this->_controller->request->projectvars['VarsArray'][2];
    $reportID = $this->_controller->request->projectvars['VarsArray'][3];
    $id = $this->_controller->request->projectvars['VarsArray'][4];

    if ($projectID == 0) {
        return $data;
    }
    if ($id == 0) {
        return $data;
    }

    foreach ($data as $key => $value) {
        $Model = key($value);
        if (file_exists($savePath . $value[$Model]['name']) === true) {
            $data[$key][$Model]['file_exists'] = 1;
        } else {
            $data[$key][$Model]['file_exists'] = 0;
        }
    }

    return $data;
}

public function getReportSigns($xmlData, $reportnumber)
{
    $Lang = $this->_controller->request->lang;
    $Output = array();

    $verfahren = $reportnumber['Testingmethod']['value'];

    $Verfahren = ucfirst($verfahren);
    $WithList = array('Generally','Specific','Evaluation');

    $settings = $xmlData['settings'];

    foreach ($settings->children() as $key => $setting) {
        $Continue = 0;

        foreach ($WithList as $_key => $_value) {
            $KeyTest = strstr($key, $_value, true);

            if ($KeyTest !== false) {
                $Continue = 1;
                break;
            }
        }

        if ($Continue == 0) {
            continue;
        }

        foreach ($setting as $_key => $_value) {
            if (empty($_value->pdf->signatory)) {
                continue;
            }

            $Output['sings_found'][] = trim($_value->discription->$Lang);
        }
    }

    if (count($Output) == 0) {
        return;
    }

    if (ClassRegistry::isKeySet('Sign') === false) {
        $this->_controller->loadModel('Sign');
    }
    $Signs = $this->_controller->Sign->find('list', array('conditions' => array('Sign.reportnumber_id' => $reportnumber['Reportnumber']['id'])));

    $Output['sings_okay'] = $Signs;

    // if(count($Output['sings_found']) == count($Output['sings_okay'])) $this->_controller->Flash->success(__('Advance signatures') . ': ' . count($Output['sings_okay']), array('key' => 'success'));
    // else $this->_controller->Flash->warning(__('Not all of the signatures may have been given'), array('key' => 'warning'));
}

public function OverwriteEvaluationOrder($data, $ReportEvaluation, $type)
{
    if (Configure::check('EvaluationOrder') === false) {
        return $data;
    }

    $EvaluationOrder = Configure::read('EvaluationOrder');
    $direction = 'asc';

    if (!isset($EvaluationOrder[$type])) {
        return $data;
    }
    if (!is_array($EvaluationOrder[$type])) {
        return $data;
    }
    if (!isset($EvaluationOrder[$type]['fields'])) {
        return $data;
    }
    if (!is_array($EvaluationOrder[$type]['fields'])) {
        return $data;
    }
    if (count($EvaluationOrder[$type]['fields']) == 0) {
        return $data;
    }

    unset($data['order']);

    $data['order'][$ReportEvaluation . '.sorting'] = 'asc';

    foreach ($EvaluationOrder[$type]['fields'] as $key => $value) {
        if (isset($EvaluationOrder[$type]['direction'][$key])) {
            $data['order'][$ReportEvaluation . '.' . trim($value)] = trim($EvaluationOrder[$type]['direction'][$key]);
        } else {
            $data['order'][$ReportEvaluation . '.' . trim($value)] = $direction;
        }
    }

    return $data;
}

public function AskMassReportAction($reportnumber)
{
    if (empty($this->_controller->request->data['Reportnumber']['MassSelectetIDs'])) {
        return false;
    }

    switch ($this->_controller->request->data['Reportnumber']['MassSelect']) {
        case 'close_1':
        case 'close_2':
        case 'close_3':
            $request = $this->__AskMassReportActionClosing($reportnumber);
            break;

        case 'sign':
            $request = $this->__AskMassReportActionSigning($reportnumber);
            break;

        default:
            $this->_controller->Flash->error('No data send.', array('key' => 'error'));
            return;
            break;
    }

    return $request;
}

protected function __AskMassReportActionSigning($reportnumber)
{
    $MassSelectetIDs = explode(',', $this->_controller->request->data['Reportnumber']['MassSelectetIDs']);
    $MassSelect = explode('_', $this->_controller->request->data['Reportnumber']['MassSelect']);
    //		$Step = $MassSelect[1];
    $Step = 3;
    if (Configure::check('MassActionSignLevel')) {
        $Step = Configure::read('MassActionSignLevel');
    }


    // Funktioniert im Augenblick nur für Wacker Nünchritz
    // bei Bericht schließen mit Stufe 2
    $AclCheck = $this->_controller->Autorisierung->AclCheck(array(0 => 'reportnumbers',1 => 'signSupervisor'));

    if ($AclCheck === false) {
        return false;
    }

    if (count($MassSelectetIDs) == 0) {
        return false;
    }
    if (count($MassSelectetIDs) > 25) {
        return false;
    }

    $reportnumbers = $this->__CollectTestReports($MassSelectetIDs, $Step);
    $reportnumbers = $this->__TestForSigning($reportnumbers);

    if (isset($this->_controller->request->data['Reportnumber']['action']) && $this->_controller->request->data['Reportnumber']['action'] == 1) {
        $this->__DoSignMassReportAction($reportnumbers);
        return $reportnumbers;
    }

    if ($this->_controller->request->data['Reportnumber']['MassSelect'] == 'sign') {
        $Message = __('Should these reports be signed?', true);
    }

    $this->_controller->Flash->warning($Message, array('key' => 'warning'));

    return $reportnumbers;
}

protected function __TestForSigning($reportnumbers)
{
    if (Configure::check('SignatoryCascading') === false) {
        return $reportnumbers;
    }
    if (Configure::check('SignatoryCountofSigns') === false) {
        return $reportnumbers;
    }
    if (Configure::read('SignatoryCascading') != 1) {
        return $reportnumbers;
    }

    $Step = 3;
    if (Configure::check('MassActionSignLevel')) {
        $Step = Configure::read('MassActionSignLevel');
    }

    $MinStatus = --$Step;

    foreach ($reportnumbers as $key => $value) {
        if ($value['Reportnumber']['status'] < $MinStatus) {
            if (Configure::check('SignatoryKeepOpen') && Configure::read('SignatoryKeepOpen') <> 0) {
                $this->_controller->loadModel('Sign');

                $signmax = $this->_controller->Sign->find('first', array('fields'=>array('MAX(Sign.signatory) AS signatory'),'conditions' => array('Sign.reportnumber_id' => $value['Reportnumber']['id'])));

                if ($signmax[0]['signatory'] <> $MinStatus) {
                    $reportnumbers[$key]['SignErrors'] = __('Subordinate signatures are missing', true);
                    continue;
                }
            } else {
                $reportnumbers[$key]['SignErrors'] = __('Subordinate signatures are missing', true);
                continue;
            }
        }

        if (isset($signmax) && !empty($signmax)) {
            if ($signmax[0]['signatory'] == $MinStatus) {
                continue;
            } else {
                $reportnumbers[$key]['SignErrors'] = __('Signature available ', true);
                continue;
            }
        }

        if ($value['Reportnumber']['status'] == $MinStatus) {
        } else {
            $reportnumbers[$key]['SignErrors'] = __('Signature available ', true);
            continue;
        }
    }
    return $reportnumbers;
}

protected function __AskMassReportActionClosing($reportnumber)
{
    $MassSelectetIDs = explode(',', $this->_controller->request->data['Reportnumber']['MassSelectetIDs']);
    $MassSelect = explode('_', $this->_controller->request->data['Reportnumber']['MassSelect']);
    //		$Step = $MassSelect[1];
    $Step = 3;

    // Funktioniert im Augenblick nur für Wacker Nünchritz
    // bei Bericht schließen mit Stufe 2
    $AclCheck = $this->_controller->Autorisierung->AclCheck(array(0 => 'reportnumbers',1 => 'status2'));

    if ($AclCheck === false) {
        return false;
    }

    if (count($MassSelectetIDs) == 0) {
        return false;
    }
    if (count($MassSelectetIDs) > 25) {
        return false;
    }

    $reportnumbers = $this->__CollectTestReports($MassSelectetIDs, $Step);

    if (isset($this->_controller->request->data['Reportnumber']['action']) && $this->_controller->request->data['Reportnumber']['action'] == 1) {
        $reportnumbers = $this->__DoMassReportActionClosing($reportnumbers);
        return $reportnumbers;
    }

    if ($this->_controller->request->data['Reportnumber']['MassSelect'] == 'close_1') {
        $Message = __('Should the following reports be closed?', true);
    }
    if ($this->_controller->request->data['Reportnumber']['MassSelect'] == 'close_2') {
        $Message = __('Should the following reports be closed?', true);
    }
    if ($this->_controller->request->data['Reportnumber']['MassSelect'] == 'close_3') {
        $Message = __('Should the following reports be closed?', true);
    }

    $this->_controller->Flash->warning($Message, array('key' => 'warning'));

    return $reportnumbers;
}

protected function __DoMassReportActionClosing($reportnumber)
{
    $MassSelectetIDs = explode(',', $this->_controller->request->data['Reportnumber']['MassSelectetIDs']);
    $MassSelect = explode('_', $this->_controller->request->data['Reportnumber']['MassSelect']);
    //		$Step = $MassSelect[1];
    $Step = 3;

    foreach ($reportnumber as $key => $value) {
        if (isset($value['ValidationErrors'])) {
            continue;
        }
        if (isset($value['ClosingErrors'])) {
            continue;
        }

        $Update = array(
            'id' => $value['Reportnumber']['id'],
            'status' => $Step,
        );

        if ($this->_controller->Reportnumber->save($Update)) {
            $this->_controller->Autorisierung->Logger($value['Reportnumber']['id'], $Update);
        } else {
            $this->_controller->Flash->error(__('The changes could not be saved. Please try again.', true), array('key' => 'error'));

            return $reportnumber;
        }
    }

    $FormNameArray = $this->_controller->Session->read('lastArrayURL');

    if (!empty($FormNameArray) && is_array($FormNameArray) && count($FormNameArray) == 4) {
        $FormName['controller'] = $FormNameArray['controller'];
        $FormName['action'] = $FormNameArray['action'];
        $FormName['terms'] = implode('/', $FormNameArray['pass']);
        $this->_controller->set('FormName', $FormName);
    }

    $this->_controller->Flash->success(__('The changes have been saved', true), array('key' => 'success'));

    $this->_controller->Email->SendMassCollectPdf($MassSelectetIDs);

    return $reportnumber;
}

protected function __CollectTestReports($MassSelectetIDs, $Step)
{
    if (count($MassSelectetIDs) == 0) {
        return array();
    }

    $options = array(
        'conditions' => array(
            'Reportnumber.id' => $MassSelectetIDs,
            'Reportnumber.delete' => 0
        ),
        'order' => array('Reportnumber.id DESC'),
    );

    $this->_controller->Reportnumber->recursive = 0;
    $reportnumbers = $this->_controller->Reportnumber->find('all', $options);

    if (count($reportnumbers) == 0) {
        return array();
    }

    foreach ($reportnumbers as $key => $value) {
        $verfahren = $value['Testingmethod']['value'];
        $Verfahren = ucfirst($verfahren);

        $xml_folder = Configure::read('xml_folder') . 'testingmethod' . DS . $verfahren . DS . (isset($value['Reportnumber']['version']) && intval($value['Reportnumber']['version']) > 1 ? $value['Reportnumber']['version'] : 1) . DS;
        $folder = new Folder($xml_folder);
        $file = $folder->findRecursive($verfahren.'.xml');

        if (is_array($file) && !empty($file)) {
            $xml_value = $xml_folder . basename(reset($file));
            $xml['settings'] = Xml::build($xml_value);
        }

        $reportnumbers[$key] = $this->_controller->Data->GetReportData($value, array('Generally','Specific','Evaluation'));
        $ValidationErrors = $this->_controller->Reportnumber->getValidationErrors($xml, $reportnumbers[$key]);

        if (count($ValidationErrors) > 0) {
            $reportnumbers[$key]['ValidationErrors'] = $ValidationErrors;
        }

        if ($value['Reportnumber']['status'] >= $Step) {
            $reportnumbers[$key]['ClosingErrors'] = 1;
        }
        //		if($value['Reportnumber']['print'] > 0) $reportnumbers[$key]['ClosingErrors'] = 1;
    }

    return $reportnumbers;
}

protected function __DoSignMassReportAction($reportnumbers)
{
    $SignatoryCascading = true;
    $SignatoryAfterPrinting = true;
    $SignatoryClosing = true;

    if (Configure::check('SignatoryCascading')) {
        $SignatoryCascading = Configure::read('SignatoryCascading');
    }
    if (Configure::check('SignatoryAfterPrinting')) {
        $SignatoryAfterPrinting = Configure::read('SignatoryAfterPrinting');
    }
    if (Configure::check('SignatoryClosing')) {
        $SignatoryClosing = Configure::read('SignatoryClosing');
    }

    $this->_controller->set('SignatoryClosing', $SignatoryClosing);
    $this->_controller->set('SignatoryAfterPrinting', $SignatoryAfterPrinting);
    $this->_controller->set('SignatoryCascading', $SignatoryCascading);
}

public function SigntransportMassAction($Data, $Step, $SignLevel)
{
    if ($Step == 1) {
        $this->__SigntransportMassActionStepOne($SignLevel);
    }
    if ($Step == 2) {
        $this->__SigntransportMassActionStepTwo($Data);
    }
}


protected function __SigntransportMassActionStepTwo($Data)
{

	if ($this->_controller->Session->check('Sign.Signature') === false) {
        return false;
    }
    if (!isset($this->_controller->request->data['Reportnumber']['MassSelectetIDs'])) {
        return false;
    }
    if (empty($this->_controller->request->data['Reportnumber']['MassSelectetIDs'])) {
        return false;
    }

    // nacheinander unterschreiben als Standard
    $SignatoryCascading = true;
    $SignatoryAfterPrinting = true;
    $SignatoryClosing = true;

    if (Configure::check('SignatoryCascading')) {
        $SignatoryCascading = Configure::read('SignatoryCascading');
    }
    if (Configure::check('SignatoryAfterPrinting')) {
        $SignatoryAfterPrinting = Configure::read('SignatoryAfterPrinting');
    }
    if (Configure::check('SignatoryClosing')) {
        $SignatoryClosing = Configure::read('SignatoryClosing');
    }

    $color = 'rgb(0, 0, 0)';

    if (isset($this->_controller->request->data['image'])) {
        $this->_controller->Session->write('Sign.Image', $this->_controller->request->data['image']);
    }
    if (isset($this->_controller->request->data['color']) && !empty($this->_controller->request->data['color'])) {
        $this->_controller->Session->write('Sign.Color', $this->_controller->request->data['color']);
    }
    if (isset($this->_controller->request->data['Reportnumber']['MassSelectetIDs']) && !empty($this->_controller->request->data['Reportnumber']['MassSelectetIDs'])) {
        $this->_controller->Session->write('Sign.MassSelectetIDs', $this->_controller->request->data['Reportnumber']['MassSelectetIDs']);
    }
    //
    $Signatory = $this->_controller->Session->read('Sign');

    $colored_image_array = $this->__SigntransportMassActionCreateImage();

    if ($colored_image_array === false) {
        return false;
    }

    $colored_image = $colored_image_array['colored_image'];
    $im_width = $colored_image_array['im_width'];
    $im_height = $colored_image_array['im_height'];

    $MassSelectetIDs = explode(',', $Signatory['MassSelectetIDs']);

    if (count($MassSelectetIDs) == 0) {
        return false;
    }
    if (count($MassSelectetIDs) != count($Data)) {
        return false;
    }

    $this->_controller->loadModel('Sign');

    foreach ($Data as $key => $value) {

        if (isset($value['ValidationErrors'])) {
            continue;
        }
        if (isset($value['ClosingErrors'])) {
            continue;
        }

        $Sign = $this->_controller->Sign->find(
            'first',
            array('conditions' => array(
          	  'Sign.reportnumber_id' => $value['Reportnumber']['id'],
          	  'Sign.signatory' => $Signatory['Signatory']
        		)
    		)
        );

        $secretImage = Security::cipher($Signatory['Image'], Configure::read('SignatoryHash'));
        $secretColoredImage = Security::cipher(base64_encode($colored_image), Configure::read('SignatoryHash'));

        $data = $value;


        // Beginn bei speichern in Datei
        if ((Configure::check('SignatorySaveMethode') && Configure::read('SignatorySaveMethode') == 'file') || !Configure::check('SignatorySaveMethode')) {
            $report_id_chiper = bin2hex(Security::cipher($value['Reportnumber']['id'], Configure::read('SignatoryHash')));
            $project_id_chiper = bin2hex(Security::cipher($value['Reportnumber']['topproject_id'], Configure::read('SignatoryHash')));

            $path = Configure::read('SignatoryPfad') . $project_id_chiper . DS . $report_id_chiper . DS . $Signatory['Signatory'] . DS;


            // Wenn nicht vorhanden Ordner erzeugen
            if (!file_exists($path)) {
                $dir_orginal = new Folder($path . 'orginal', true, 0755);
                $dir_colored = new Folder($path . 'colored', true, 0755);

                $file_orginal = new File($path . 'orginal' . DS . $report_id_chiper);
                $file_colored = new File($path . 'colored' . DS . $report_id_chiper);

                $file_orginal->write($secretImage);
                $file_orginal->close();

                $file_colored->write($secretColoredImage);
                $file_colored->close();
            } else {
                //							$this->_controller->Flash->error(__('Die Signatur konnte nicht gespeichert werden. Eine vorhandene Unterschrift konnte nicht gelöscht werden, wenden sie sich an einen Administrator.',true), array('key' => 'error'));
                //							return;
            }
        }

        // Beginn bei speichern in Dateinbank
        if (Configure::check('SignatorySaveMethode') && Configure::read('SignatorySaveMethode') == 'data') {
            $data['Reportnumber']['image_orginal'] = $secretImage;
            $data['Reportnumber']['image'] = $secretColoredImage;
        }

        $secretSignatur = $Signatory['Signature'];

        $data['Reportnumber']['width'] = $im_width;
        $data['Reportnumber']['height'] = $im_height;
        $data['Reportnumber']['reportnumber_id'] = $data['Reportnumber']['id'];
        $data['Reportnumber']['signatory'] = $Signatory['Signatory'];
        $data['Reportnumber']['user_id'] = $this->_controller->Auth->user('id');

        $data_status['Reportnumber']['id'] = $data['Reportnumber']['id'];

        if ($SignatoryClosing == true) {
            $data_status['Reportnumber']['status'] = $Signatory['Signatory'];
            $data_status['Reportnumber']['revision_progress'] = 0;
            $this->_controller->Session->delete('revision.' . $value['Reportnumber']['id']);
        }

        if (isset($value['Reportnumber']['revision_write']) && $value['Reportnumber']['revision_write'] == 1) {
            $data_status['Reportnumber']['revision_progress'] = 0;
            $data_status['Reportnumber']['print'] = 0;
            $this->_controller->Session->delete('revision.' . $value['Reportnumber']['id']);
        }

        unset($data['Reportnumber']['id']);
        unset($data['Reportnumber']['created']);
        unset($data['Reportnumber']['modified']);

        $this->_controller->Sign->create();

        if ($this->_controller->Sign->save($data['Reportnumber'])) {
            $this->_controller->Autorisierung->Logger($value['Reportnumber']['id'], array($Signatory['Signatory']));
            $SignId = $this->_controller->Sign->getInsertID();
            $Reportnumber = $this->_controller->Reportnumber->save($data_status);

            // diese Array wird der Revison übergeben, wenn nötig
            $TransportArray['model'] = 'Sign';
            $TransportArray['row'] = null;
            $TransportArray['this_value'] = $data['Reportnumber']['signatory'];
            $TransportArray['last_id_for_radio'] = null;
            $TransportArray['table_id'] = $SignId;
            $TransportArray['last_value'] = $data['Reportnumber']['signatory'];

            $Reportnumber['Reportnumber'] ['revision_progress'] > 0 ? $this->_controller->Data->SaveRevision($Reportnumber, $TransportArray, 'sign/add') : '';

            $this->_controller->Reportnumber->recursive= 1;
            $reportnumberForArchiv =  $this->_controller->Reportnumber->find('first', array('conditions' => array('Reportnumber.id' => $value['Reportnumber']['id'])));
            $reportsarchiv = $reportnumberForArchiv;


            $Verfahren = ucfirst($value['Testingmethod']['value']);

            // solange der Prüfbericht nicht geschlossen ist wird das Archiv aktualisiert
            $this->_controller->Data->Archiv($value['Reportnumber']['id'], $Verfahren);

            //Unterschriftendatum für das entsprechende Feld
            if (Configure::check('SetDateFromSignatory') && (Configure::read('SetDateFromSignatory') == true)) {
                $this->_controller->Data->SetDateFromSignatory($value['Reportnumber']['id'], $Verfahren, $Signatory);
            }
        } else {
            $this->_controller->Flash->error(__('Die Signatur konnte nicht gespeichert werden. Eine vorhandene Unterschrift konnte nicht gelöscht werden, wenden sie sich an einen Administrator.', true), array('key' => 'error'));
            return;
        }
    }

    $this->_controller->Email->SendMassCollectPdf($MassSelectetIDs);

    $this->_controller->Flash->success(__('Die Signatur wurden gespeichert.', true), array('key' => 'success'));

    $FormNameArray = $this->_controller->Session->read('lastArrayURL');

    if (!empty($FormNameArray) && is_array($FormNameArray) && count($FormNameArray) == 4) {
        $FormName['controller'] = $FormNameArray['controller'];
        $FormName['action'] = $FormNameArray['action'];
        $FormName['terms'] = implode('/', $FormNameArray['pass']);
        $this->_controller->set('FormName', $FormName);
    }
}

protected function __SigntransportMassActionStepOne($Step)
{
    // nacheinander unterschreiben als Standard
    $SignatoryCascading = true;
    $SignatoryAfterPrinting = true;
    $SignatoryClosing = true;

    if (Configure::check('SignatoryCascading')) {
        $SignatoryCascading = Configure::read('SignatoryCascading');
    }
    if (Configure::check('SignatoryAfterPrinting')) {
        $SignatoryAfterPrinting = Configure::read('SignatoryAfterPrinting');
    }
    if (Configure::check('SignatoryClosing')) {
        $SignatoryClosing = Configure::read('SignatoryClosing');
    }

    if (isset($this->_controller->request->data['signature'])) {
        $this->_controller->Session->write('Sign.Signature', $this->_controller->request->data['signature']);
    }
    $this->_controller->Session->write('Sign.Signatory', $Step);

    $this->_controller->autoRender = false;
    return;
}

protected function __SigntransportMassActionCreateImage()
{
    if ($this->_controller->Session->check('Sign') === false) {
        return false;
    }

    $Signatory = $this->_controller->Session->read('Sign');

    $color = $this->_controller->Session->read('Sign.Color');

    // Die Grafik wird eingefärbt
    $color = str_replace('rgb(', '', $color);
    $color = str_replace(')', '', $color);
    $color = explode(',', $color);

    if (count($color) == 3) {
        $ncolor['r'] = trim($color[0]);
        $ncolor['g'] = trim($color[1]);
        $ncolor['b'] = trim($color[2]);
    } else {
        $ncolor['r'] = 0;
        $ncolor['g'] = 0;
        $ncolor['g'] = 0;
    }

    $data = base64_decode($Signatory['Image']);
    $im = imagecreatefromstring($data);

    $im_width = imagesx($im);
    $im_height = imagesy($im);

    imagealphablending($im, false);

    for ($x = imagesx($im); $x--;) {
        for ($y = imagesy($im); $y--;) {
            $c = imagecolorat($im, $x, $y);
            $rgb['r'] = ($c >> 16) & 0xFF;
            $rgb['g'] = ($c >> 8) & 0xFF;
            $rgb['b'] = ($c & 0xFF);
            $rgb['t'] = ($c >> 24) & 0x7F;
            if ($rgb['t'] < 127) {
                $colorB = imagecolorallocatealpha($im, $ncolor['r'], $ncolor['g'], $ncolor['b'], $rgb['t']);
                imagesetpixel($im, $x, $y, $colorB);
            }
        }
    }

    imagesavealpha($im, true);

    ob_start();
    imagepng($im);
    $colored_image =  ob_get_contents();
    ob_end_clean();
    imagedestroy($im);

    $Output = array('colored_image' => $colored_image,'im_width' => $im_width,'im_height' => $im_height);

    return $Output;
}

public function SetResultReportnumber($id, $model)
{
    $weldsne = $this->_controller->$model->find('all', array('conditions'=>array($model.'.reportnumber_id'=>$id, $model.'.result'=>2)));
    $reportnumber = $this->_controller->Reportnumber->find('first', array('conditions'=>array('Reportnumber.id'=>$id)));
    if (count($weldsne) > 0) {
        $reportnumber['Reportnumber']['result'] = 2;
        $this->_controller->Reportnumber->save($reportnumber['Reportnumber']);
        return;
    } else {
        $welds = $this->_controller->$model->find('all', array('conditions'=>array($model.'.reportnumber_id'=>$id)));
        if (empty($welds)) {
            $reportnumber['Reportnumber']['result'] = 0;
            $this->_controller->Reportnumber->save($reportnumber['Reportnumber']);
            return;
        }
        foreach ($welds as $key => $value) {
            if ($value[$model]['result'] == 0) {
                $uev = $value[$model]['result'];
                $reportnumber['Reportnumber']['result'] = 0;
                $this->_controller->Reportnumber->save($reportnumber['Reportnumber']);
                return;
            }
            $reportnumber['Reportnumber']['result'] = 1;
            $this->_controller->Reportnumber->save($reportnumber['Reportnumber']);
            return;
        }
    }
}

    public function UpdateDateOfTest($id)
    {
        $reportnumber = $this->_controller->Reportnumber->find('first', array('conditions'=>array('Reportnumber.id'=>$id)));
        $this->_controller->loadModel('Testingmethod');
        $methods = $this->_controller->Testingmethod->find('first', array('conditions'=>array('id' =>$reportnumber['Reportnumber']['testingmethod_id'])));

        $generally = "Report".ucfirst($methods['Testingmethod']['value']).'Generally';
        $this->_controller->loadModel($generally);
        $generallydatas = $this->_controller->$generally->find('first', array('conditions'=>array('reportnumber_id'=> $id),'fields'=>array('reportnumber_id', 'date_of_test')));
        if (empty($generallydatas)) {
            return;
        }
        $reportnumber ['Reportnumber']['date_of_test'] = $generallydatas[$generally]['date_of_test'];
        $this->_controller->Reportnumber->save($reportnumber ['Reportnumber']);

        return;
    }
}
