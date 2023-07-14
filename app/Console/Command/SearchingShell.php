<?php

    App::uses('ComponentCollection', 'Controller');
    App::uses('XmlComponent', 'Controller/Component');
    App::uses('Folder', 'Utility');
    App::uses('File', 'Utility');

    class SearchingShell extends AppShell
    {
        public $uses = array(
          'Topproject',
          'Searching',
          'ReportsTopprojects',
          'TestingmethodsReports',
          'Testingmethod',
          'Reportnumber',
          'Report',
          'ReportRtArchiv',
          'ReportMtArchiv',
          'ReportPtArchiv',
          'ReportUtwdArchiv',
          'ReportUtArchiv',
          'ReportHtArchiv',
          'ReportRtwdArchiv',
          'ReportVtArchiv',
          'ReportLtArchiv',
          'ReportRtasmeArchiv',
          'ReportPmiArchiv',
          'ReportUtrswArchiv',
          'ReportRtsArchiv',
          'ReportUthicArchiv',
          'ReportPtasmeArchiv',
          'ReportMtasmeArchiv',
          'ReportPtdArchiv',
          'ReportUtdArchiv',
          'ReportRtgpArchiv',
          'ReportRtabnArchiv',
          'ReportUtrsw1Archiv',
          'ReportUtrsw2Archiv',
          'ReportUtrsw3Archiv',
          'ReportUtrsw4Archiv',
          'ReportRtgpArchiv',
          'ReportRtgpArchiv',
          'ReportRtgpArchiv',
          'ReportRtgpArchiv',
          'ReportVtbkArchiv',
          'ReportMtgpArchiv',
          'ReportMtbArchiv',
          'ReportRtipArchiv',
          'ReportRtdwArchiv',
          'ReportMtdArchiv',
          'ReportRtEvaluation',
          'ReportMtEvaluation',
          'ReportPtEvaluation',
          'ReportUtwdEvaluation',
          'ReportUtEvaluation',
          'ReportHtEvaluation',
          'ReportRtwdEvaluation',
          'ReportVtEvaluation',
          'ReportLtEvaluation',
          'ReportRtasmeEvaluation',
          'ReportPmiEvaluation',
          'ReportUtrswEvaluation',
          'ReportRtsEvaluation',
          'ReportUthicEvaluation',
          'ReportPtasmeEvaluation',
          'ReportMtasmeEvaluation',
          'ReportPtdEvaluation',
          'ReportUtdEvaluation',
          'ReportRtgpEvaluation',
          'ReportRtabnEvaluation',
          'ReportUtrsw1Evaluation',
          'ReportUtrsw2Evaluation',
          'ReportUtrsw3Evaluation',
          'ReportUtrsw4Evaluation',
          'ReportRtgpEvaluation',
          'ReportRtgpEvaluation',
          'ReportRtgpEvaluation',
          'ReportRtgpEvaluation',
          'ReportVtbkEvaluation',
          'ReportMtgpEvaluation',
          'ReportMtbEvaluation',
          'ReportRtipEvaluation',
          'ReportRtdwEvaluation',
          'ReportMtdEvaluation',
        );

        public function main()
        {
            $this->out('Hello world.');
        }

        protected function __CreateSearchingEntrys() {

          // Argument 0 ist die ProjektID
          // Argument 1 bestimmt ob vorhande Daten gelöscht werden sollen, 0 = nicht löschen 1 = löschen

            if (count($this->args) == 0) {
                $this->out('Script stoped, no argument for project');
                return;
            }

            $deleteData = 0;

            $projectID = $this->args[0];
            if (isset($this->args[1])) {
                $deleteData = $this->args[1];
            }

            // Anfang löschen
            if ($deleteData == 1) {

              $this->out('Existing data will be deleted');

                $options = array(
                  'fields' => array('topproject_id'),
                  'conditions' => array(
                    'Searching.topproject_id' => $projectID,
                  )
                );

                $Searchings = $this->Searching->find('list', $options);

                if (count($Searchings) == 0) {
//                    $this->out('No data available.');
//                    return;
                }

                $x1 = 0;
                $x2 = 0;

                foreach ($Searchings as $key => $value) {

                    $options = array('conditions' => array('SearchingValue.searching_id' => $key));
                    $Counts = $this->Searching->SearchingValue->find('count', $options);

                    if ($this->Searching->SearchingValue->deleteAll(array('SearchingValue.searching_id' => $key), false)) {
                        $x2 += $Counts;
                    } else {
                        $this->out('record in table Searching with id' . ' ' . $value . ' ' . 'could not be deleted');
                    }

                    $Counts = 0;

                    if ($this->Searching->delete($key)) {
                        $x1++;
                    } else {
                        $this->out('record in table SearchingValue with searching_id' . ' ' . $key . ' ' . 'could not be deleted');
                    }
                }

                $this->out($x1 . ' ' . 'records deleted from table Searching');
                $this->out($x2 . ' ' . 'records deleted from table SearchingValue');
            }
            // Ende löschen

            $this->out('Read data started');

            $fileName = 'search_additional';

            $collection = new ComponentCollection();
            $this->XmlComponent = $collection->load('Xml');

            $xml_folder = Configure::read('xml_folder');

            if (file_exists($xml_folder . 'search' . DS . 'projects' . DS . $projectID . DS . 'search_additional.xml') === true) {
                $xml_folder = $xml_folder . 'search' . DS . 'projects' . DS . $projectID . DS;
            } else {
                $xml_folder = $xml_folder . 'search' . DS . 'default' . DS;
            }

            $folder = new Folder($xml_folder);
            $file = $folder->findRecursive($fileName . '.xml');

            if (is_array($file) && !empty($file)) {
                $value = $xml_folder.basename(reset($file));
            }

            if (isset($value)) {
                $SearchFieldsAdditional = Xml::build($value);
            } else {
                $this->out('Can\'t load xml');
                return;
            }

            $ReportsTopprojects = $this->ReportsTopprojects->find('list', array('fields' => array('report_id','report_id'), 'conditions' => array('ReportsTopprojects.topproject_id' => $projectID)));
            $TestingmethodsReports = $this->TestingmethodsReports->find('list', array('fields' => array('testingmethod_id','testingmethod_id'), 'conditions' => array('TestingmethodsReports.report_id' => $ReportsTopprojects)));
            $Testingmethod = $this->Testingmethod->find('all', array('conditions' => array('Testingmethod.id' => $TestingmethodsReports)));

            $Testingmethods = array();
            $InsertSchema = array();

            foreach ($Testingmethod as $_reports) {
                $Testingmethods[$_reports['Testingmethod']['value']] = true;
            }

            foreach ($SearchFieldsAdditional->fields->children() as $_key => $_children) {
                $field = trim($_children->key);
                foreach ($Testingmethods as $__key => $__testingmethods) {
                    $Model = 'Report' . ucfirst($__key) . trim($_children->model);
                    $this->loadModel($Model);
                    $Schema = $this->$Model->schema();
                    if (isset($Schema[$field])) {
                        array_push($InsertSchema, array('Model' => $Model, 'Field' => $field));
                    }
                }
            }

            $SearchInsertData = $InsertSchema;
//            $CurrentElement = array_pop($SearchInsertData);

            $count = count($SearchInsertData);

            foreach ($SearchInsertData as $key => $value) {

                $CurrentElement = $value;

                $model = $CurrentElement['Model'];
                $field = $CurrentElement['Field'];

                $this->out('Table' . ' ' . $model . ' ' . 'field' . ' ' . $field . ' ' . 'in progress');

                $model_array =  explode('_', Inflector::underscore($model));

                $this->Reportnumber->recursive = -1;
                $reportnumber = $this->Reportnumber->find('list', array('conditions'=>array('Reportnumber.topproject_id' => $projectID)));

                $this->{$model} = ClassRegistry::init($model);

                $model_data = $this->{$model}->find('all', array( 'conditions' => array($model.'.reportnumber_id' => $reportnumber), 'fields' => array('reportnumber_id',$field)));

                unset($reportnumber);

                $output = array();
                $input_search = array();
                $input_search_data = array();
                $numbers = array();

                foreach ($model_data as $_key => $_model_data) {
                    if ($_model_data[$model][$field] != '') {
                        $output[($_model_data[$model][$field])] = $_model_data[$model]['reportnumber_id'];
                        $numbers[strtolower($_model_data[$model][$field])][$_model_data[$model]['reportnumber_id']] = $_model_data[$model]['reportnumber_id'];
                    }
                }

                $AllInsertIds = array();
                $AllUpdateIds = array();

                $x = 0;

                foreach ($output as $_key => $_output) {


                    if(strlen($_key) > 254) continue;

                    $reportnumber = $this->Reportnumber->find('first', array('conditions'=>array('Reportnumber.id' => $_output, 'Reportnumber.topproject_id' => $projectID)));

                    if (count($reportnumber) == 0) {
                        continue;
                    }

                    $SearchingData = array(
                                  'model' => ucfirst($model_array[2]),
                                  'field' => $field,
                                  'value' => $_key,
                                  'topproject_id' => $reportnumber['Reportnumber']['topproject_id']
                              );

                    $Searching = $this->Searching->find(
                        'first',
                        array('conditions'=>array(
                                'Searching.model' => ucfirst($model_array[2]),
                                'Searching.field' => $field,
                                'Searching.value' => $_key,
                                'Searching.topproject_id' => $projectID,
                              )
                            )
                          );



                    if (count($Searching) == 0) {
                        $this->Searching->create();
                        $this->Searching->save($SearchingData);
                        $x++;
                        $AllInsertIds[] = $this->Searching->getLastInsertID();
                    } elseif (count($Searching) > 0) {
                        $AllUpdateIds[] = $Searching['Searching']['id'];
                    }
                }

                $this->out($x . ' ' . 'new entrys in table searching');

                $x = 0;

                $AllIds = array_merge($AllUpdateIds, $AllInsertIds);
                $Searching = $this->Searching->find('all', array('conditions' => array('Searching.id' => $AllIds)));

                foreach ($Searching as $_key => $_Searching) {

                    if (!isset($numbers[strtolower($_Searching['Searching']['value'])])) continue;

                    foreach ($numbers[strtolower($_Searching['Searching']['value'])] as $__key => $__numbers) {

                        $reportnumber = $this->Reportnumber->find('first', array('conditions'=>array('Reportnumber.id'=>$__numbers,'Reportnumber.topproject_id' => $projectID)));

                        if (count($reportnumber) == 0) continue;

                        $option_search = array(
                                  'SearchingValue.searching_id' => $_Searching['Searching']['id'],
                                  'SearchingValue.reportnumber_id' => $reportnumber['Reportnumber']['id'],
                              );
                        $SearchingValue = $this->Searching->SearchingValue->find('first', array('conditions' => $option_search));

                        if (count($SearchingValue) == 0) {
                            $input_search = array(
                                  'searching_id' => $_Searching['Searching']['id'],
                                  'reportnumber_id' => $reportnumber['Reportnumber']['id'],
                                  'topproject_id' => $reportnumber['Reportnumber']['topproject_id'],
                                  'cascade_id' => $reportnumber['Reportnumber']['cascade_id'],
                                  'order_id' => $reportnumber['Reportnumber']['order_id'],
                                  'report_id' => $reportnumber['Reportnumber']['report_id'],
                                  'testingmethod_id' => $reportnumber['Reportnumber']['testingmethod_id'],
                                  'testingcomp_id' => $reportnumber['Reportnumber']['testingcomp_id'],
                              );
                            $this->Searching->SearchingValue->create();
                            $this->Searching->SearchingValue->save($input_search);
                            $x++;
                        }
                    }
                }

                $count--;

                $this->out($x . ' ' . 'new entrys in table searchingvalue');
                $this->out($count . ' ' . 'from' . ' ' . count($SearchInsertData) . ' ' . 'left');
                $this->out('--------');

            }

            $this->out('end and okay');

        }

        public function DateCorrectureInSQLStep2(){

          $Field = 'date_of_test';

          $Models = array(
            'ReportRtGenerally',
            'ReportMtGenerally',
            'ReportPtGenerally',
            'ReportVtGenerally',
            'ReportUtGenerally',
            'ReportUtdGenerally',
            'ReportTtGenerally',
            'ReportAlgGenerally',
            'ReportHtGenerally',
            'ReportVt1Generally',
            'ReportVtstGenerally',
            'ReportVtrGenerally',
            'ReportRtnunGenerally',
            'ReportMtnunGenerally',
            'ReportPtnunGenerally',
            'ReportVtnunGenerally',
            'ReportUtnunGenerally',
            'ReportHtnunGenerally',
            'ReportRtdGenerally',
            'ReportUtwdGenerally',
          );

          foreach($Models as $Model){

            $this->loadModel($Model);

            $options = array(
              'fields' => array('id',$Field),
            );

            $Searchings = $this->$Model->find('all', $options);

            if(count($Searchings) == 0) continue;

            foreach ($Searchings as $key => $value) {

              $Save = array();
              $Array = array();

              if($value[$Model][$Field] == '1970-01-01') {

                $Save['id'] = $value[$Model]['id'];
                $Save[$Field] = "NULL";
                $this->$Model->save($Save);

              }
            }
          }
        }

        public function DateCorrectureInSQL(){

          $Field = 'date_of_test';

          $Models = array(
            'ReportRtGenerally',
            'ReportMtGenerally',
            'ReportPtGenerally',
            'ReportVtGenerally',
            'ReportUtGenerally',
            'ReportUtdGenerally',
            'ReportTtGenerally',
            'ReportAlgGenerally',
            'ReportHtGenerally',
            'ReportVt1Generally',
            'ReportVtstGenerally',
            'ReportVtrGenerally',
            'ReportRtnunGenerally',
            'ReportMtnunGenerally',
            'ReportPtnunGenerally',
            'ReportVtnunGenerally',
            'ReportUtnunGenerally',
            'ReportHtnunGenerally',
            'ReportRtdGenerally',
            'ReportUtwdGenerally',
          );

          foreach($Models as $Model){

            $this->loadModel($Model);

            $options = array(
              'fields' => array('id',$Field),
            );

            $Searchings = $this->$Model->find('all', $options);

            if(count($Searchings) == 0) continue;

            foreach ($Searchings as $key => $value) {

              $Save = array();
              $Array = array();

              if($value[$Model][$Field] == '') {

                $Save['id'] = $value[$Model]['id'];
                $Save[$Field] = '1970-01-01';
                $this->$Model->save($Save);

                continue;

              }

              $Array = explode('-',$value[$Model][$Field]);

              if(count($Array) == 0) continue;

              if(count($Array) == 3){

                $Test = checkdate ($Array[1] , $Array[2] , $Array[0]);

                if($Test === true){

                }
                elseif($Test === false){
                  pr($Model);
                  pr($value);
                  pr('-----');
                }
              }

              if(count($Array) == 1){

                $Array = explode('.',$value[$Model][$Field]);

                if(count($Array) == 3){

                  $Save['id'] = $value[$Model]['id'];
                  $Save[$Field] = $Array[2] . '-' . $Array[1] . '-' . $Array[0];
                  $this->$Model->save($Save);

                  continue;

                }

                $Array = explode('/',$value[$Model][$Field]);
                if(count($Array) > 1){
                  pr($value);
                  pr($Array);
                }
              }
            }
          }
        }

        public function CreateSearchingEntrys(){

          // Wenn eine ID geliefert wird
          // werden die Sucheinträge des Projekts erstellt
          if($this->args[0] > 0) {

            $this->__CreateSearchingEntrys();

          } else {
            // Wenn eine 0 geliefert wird
            // werden alle Projekte in der Daten upgedatet

            $Searchings = $this->Topproject->find('list',array('conditions' => array('Topproject.status' => 0)));

            if(count($Searchings) > 0){

               foreach ($Searchings as $key => $value) {

                 $this->args[0] = $value;
                 $this->__CreateSearchingEntrys();

               }
            }
          }
        }

        public function DeleteByProject()
        {
            if (count($this->args) == 0) {
                $this->out('Script stoped, no argument for project');
                return;
            }

            $projectID = $this->args[0];

            $options = array(
            'fields' => array('topproject_id'),
            'conditions' => array(
              'Searching.topproject_id' => $projectID,
              )
            );

            $Searchings = $this->Searching->find('list', $options);

            if (count($Searchings) == 0) {
                $this->out('No data available.');
                return;
            }

            $x1 = 0;
            $x2 = 0;

            foreach ($Searchings as $key => $value) {
                $options = array('conditions' => array('SearchingValue.searching_id' => $key));
                $Counts = $this->Searching->SearchingValue->find('count', $options);

                if ($this->Searching->SearchingValue->deleteAll(array('SearchingValue.searching_id' => $key), false)) {
                    $x2 += $Counts;
                } else {
                    $this->out('record in table Searching with id' . ' ' . $value . ' ' . 'could not be deleted');
                }

                $Counts = 0;

                if ($this->Searching->delete($key)) {
                    $x1++;
                } else {
                    $this->out('record in table SearchingValue with searching_id' . ' ' . $key . ' ' . 'could not be deleted');
                }
            }

            $this->out($x1 . ' ' . 'records deleted from table Searching');
            $this->out($x2 . ' ' . 'records deleted from table SearchingValue');
        }

        public function ReplaceInReceiver(){

          $projectID = $this->args[0];
          $fileName = 'correcture';


          $collection = new ComponentCollection();
          $this->XmlComponent = $collection->load('Xml');

          $xml_folder = Configure::read('xml_folder');


          if (file_exists($xml_folder . 'correcture' . DS . 'projects' . DS . $projectID . DS . 'correcture.xml') === true) {
              $xml_folder = $xml_folder . 'correcture' . DS . 'projects' . DS . $projectID . DS;
          } else {
              return;
          }

          $folder = new Folder($xml_folder);

          $file = $folder->findRecursive($fileName . '.xml');

          if (is_array($file) && !empty($file)) {
              $value = $xml_folder.basename(reset($file));
          }

          if (isset($value)) {
              $SearchFieldsAdditional = Xml::build($value);
          } else {
              $this->out('none xml');
              return;
          }

          $ReportsTopprojects = $this->ReportsTopprojects->find('list', array('fields' => array('report_id','report_id'), 'conditions' => array('ReportsTopprojects.topproject_id' => $projectID)));
          $TestingmethodsReports = $this->TestingmethodsReports->find('list', array('fields' => array('testingmethod_id','testingmethod_id'), 'conditions' => array('TestingmethodsReports.report_id' => $ReportsTopprojects)));
          $Testingmethod = $this->Testingmethod->find('all', array('conditions' => array('Testingmethod.id' => $TestingmethodsReports)));

          $Testingmethods = array();
          $InsertSchema = array();

          foreach ($Testingmethod as $_reports) {
              $Testingmethods[$_reports['Testingmethod']['value']] = true;
          }

          foreach ($SearchFieldsAdditional->fields->children() as $_key => $_children) {
              $field = trim($_children->key);
              foreach ($Testingmethods as $__key => $__testingmethods) {
                  $Model = 'Report' . ucfirst($__key) . trim($_children->model);
                  $this->loadModel($Model);
                  $Schema = $this->$Model->schema();
                  if (isset($Schema[$field])) {
                      array_push($InsertSchema, array('Model' => $Model, 'Field' => $field));
                  }
              }
          }

          $SearchInsertData = $InsertSchema;

          $count = count($SearchInsertData);

          $this->Dropdown = ClassRegistry::init('Dropdown');
          $this->DropdownsValue = ClassRegistry::init('DropdownsValue');
            $this->DropdownsValue->recursive = -1;

          foreach ($SearchInsertData as $key => $value) {

              $CurrentElement = $value;

              $model = $CurrentElement['Model'];
              $field = $CurrentElement['Field'];

              $dropdownoption = array(
                'conditions' => array(
                  'Dropdown.model' => $model,
                  'Dropdown.field' => $field,
                )
              );

              $Dropdowns = $this->Dropdown->find('list',$dropdownoption);

              if(count($Dropdowns) == 0) continue;
              $this->out('Table' . ' ' . $model . ' ' . 'field' . ' ' . $field . ' ' . 'in progress');

              $DropdownsValues = $this->DropdownsValue->find('list',array('fields' => array('discription'),'conditions' => array('DropdownsValue.dropdown_id' => $Dropdowns)));

              $this->{$model} = ClassRegistry::init($model);

              $model_data = $this->{$model}->find('list', array(
                'conditions' => array(
                  $model.'.reportnumber_id >' => 53149,
                  $model.'.receivers LIKE' => "%,%",
                  $model.'.receivers NOT LIKE' => "%\n%"
                ),
                'fields' => array('id',$field)
                )
              );

              if(count($model_data) > 0){

                foreach ($model_data as $_key => $_value) {

                  $test = explode(',',$_value);

                  foreach($test as $__key => $__value){
                    if($__value[0] == ' '){
                      $before_key = $__key - 1;
                      $test[$before_key] = trim($test[$before_key]) . ', ' . trim($test[$__key]);
                      unset($test[$__key]);

                    }
                  }

                  $New = implode("\n",$test);

                  pr($_key);
                  pr($model_data[$_key]);
                  pr($New);

                  $model_data_id = $this->{$model}->find('first', array(
                    'conditions' => array(
                      $model.'.id' => $_key,
                    ),
                    'fields' => array('reportnumber_id')
                    )
                  );

                  pr($model_data_id[$model]['reportnumber_id']);

                  $insertArray = array();
                  $insertArray[$model . '.id'] = $_key;
                  $insertArray[$model . '.reportnumber_id'] = $model_data_id[$model]['reportnumber_id'];
                  $insertArray[$model . '.receivers'] = $New;

                  pr($insertArray);
//                  $this->{$model}->save($insertArray);
                  $this->{$model}->updateAll(
                    array($model.'.receivers' => "'" . $New . "'"),
                    array($model.'.id' => $_key)
                  );
                  pr('-------');

                }

//pr($model_data);
                // Werte die in der DropdownValue und der Datentabelle gleich sind
                // müssen nicht beachtet werden und fliegen raus

            }
          }

          $this->out('Ende');

        }

        public function SearchForCorruptReports() {

          if (count($this->args) == 0) {
            $this->out('Script stoped, no argument for project');
            return;
          }

          $projectID = $this->args[0];
          $Output = null;

          $Topproject = $this->Topproject->find('first',array('conditions'=>array('Topproject.id' => $projectID)));

          $Output .= $Topproject['Topproject']['projektname'] . "\n\n";

          $Testingmethods = $this->Testingmethod->find('list',array('fields'=>array('id','value')));
          $this->Reportnumber->recursive = -1;

          $collection = new ComponentCollection();
          $this->XmlComponent = $collection->load('Xml');

          foreach ($Testingmethods as $key => $value) {

            $Testingmethod = $this->Testingmethod->find('first',array('conditions'=>array('Testingmethod.id' => $key)));

            $option = array(
              'fields'=>array('version'),
              'conditions'=>array(
                'Reportnumber.topproject_id' => $projectID,
                'Reportnumber.status' => 2,
                'Reportnumber.year' => 2020,
                'Reportnumber.delete' => 0,
                'Reportnumber.testingmethod_id' => $key,
                'Reportnumber.topproject_id' => $projectID,
              )
            );

            $version = $this->Reportnumber->find('first',$option);
            $reportnumbers = $this->Reportnumber->find('list',$option);

            if(count($reportnumbers) == 0) continue;

            $Output .= "\n\n" . $Testingmethod['Testingmethod']['verfahren'] . "\n\n";

            $verfahren = $value;
            $Verfahren = Inflector::camelize($value);

            $xml_folder = Configure::read('xml_folder') . 'testingmethod' . DS . $verfahren . DS . $version['Reportnumber']['version'] . DS;

            $folder = new Folder($xml_folder);
        		$file = $folder->findRecursive($verfahren . '.xml');

            if(empty($file)) continue;

            $xml_string = $xml_folder . basename(reset($file));
            $xml = Xml::build($xml_string);

            $ReportArchiv = 'Report' .$Verfahren . 'Archiv';
            $ReportGenerally = 'Report' .$Verfahren . 'Generally';
            $ReportSpecific = 'Report' .$Verfahren . 'Specific';
            $ReportEvaluation = 'Report' .$Verfahren . 'Evaluation';
//              pr($xml->$ReportGenerally);

            foreach ($reportnumbers as $_key => $_value) {

              $Archiv = $this->{$ReportArchiv}->find('first',array('conditions' => array($ReportArchiv . '.reportnumber_id' => $_key)));
              $data = utf8_decode($Archiv[$ReportArchiv]['data']);
        	 		$data = Xml::build(html_entity_decode($data));
              $reportnumber = $this->Reportnumber->find('first',array('fields' => array('number','year'),'conditions' => array('Reportnumber.id' => $_key)));

              $NumberHeader = "\n" . $reportnumber['Reportnumber']['year'] . "-" . $reportnumber['Reportnumber']['number'] . "\n";

              $output_part = null;

              foreach ($data->$ReportGenerally->children() as $__key => $__value) {

                if(empty($xml->$ReportGenerally->$__key->output->screen)) continue;
                if(!isset($xml->$ReportGenerally->$__key->validate)) continue;
                if(empty($xml->$ReportGenerally->$__key->validate)) continue;
//                if(empty($xml->$ReportGenerally->$__key->validate->notempty)) continue;
                if(trim($xml->$ReportGenerally->$__key->validate->notempty) != 1) continue;
                if(!empty($__value)) continue;

                $output_part .=  "\n" . trim($xml->$ReportGenerally->$__key->discription->deu) . " (Pflichtfeld) nicht ausgefüllt\n";

              }

              foreach ($data->$ReportSpecific->children() as $__key => $__value) {

                if(empty($xml->$ReportSpecific->$__key->output->screen)) continue;
                if(!isset($xml->$ReportSpecific->$__key->validate)) continue;
                if(empty($xml->$ReportSpecific->$__key->validate)) continue;
                if(empty($xml->$ReportSpecific->$__key->validate->notempty)) continue;
                if(!empty($__value)) continue;

                $output_part .=  "\n" . trim($xml->$ReportSpecific->$__key->discription->deu) . " (Pflichtfeld) nicht ausgefüllt\n";

              }

              $Evaluation = $this->$ReportEvaluation->find('all',array('conditions' => array('reportnumber_id' => $_key)));

              $evaluaten_header = "\n\n\n Nicht ausgewertete Prüfbereiche\n\n";
              $evaluaten_part = null;

              if(count($Evaluation) == 0) continue;
              if(!isset($xml->$ReportEvaluation->result)) continue;
              if(empty($xml->$ReportEvaluation->result->output->screen)) continue;

              foreach ($Evaluation as $__key => $__value) {

                if($__value[$ReportEvaluation]['deleted'] == 1) continue;
                if(!isset($__value[$ReportEvaluation]['result'])) continue;
                if($__value[$ReportEvaluation]['result'] == 1) continue;
                if($__value[$ReportEvaluation]['result'] == 2) continue;

                $evaluaten_part .= "\n" . $__value[$ReportEvaluation]['description'] . " nicht ausgewertet\n";

              }

              if($output_part != null || $evaluaten_part != null) $Output .= $NumberHeader;
              if($output_part != null) $Output .= $output_part;
              if($evaluaten_part != null) $Output .= $evaluaten_part;

            }
//break;
          }

          $dir = new Folder(Configure::read('xml_folder') . 'test');
          $file = new File($dir->pwd() . DS . 'output.txt');
          $file->write($Output);
          $file->close();

//$file = new File($dir->pwd() . DS . 'output_txt');
//          $this->out($Output);
        }
    }
