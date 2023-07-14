<?php

    App::uses('ComponentCollection', 'Controller');
    App::uses('NavigationComponent', 'Controller/Component');
    App::uses('XmlComponent', 'Controller/Component');

    class ImportShell extends AppShell
    {
        public $uses = array(
          'Topprojects',
          'ReportsTopprojects',
          'TestingmethodsReports',
          'Testingmethod',
          'Cascade',
          'CascadesOrder',
          'Deliverynumber',
          'Order',
          'OrdersDevelopments',
          'Development',
          'OrdersTestingcomps',
          'ReportsTopprojects',
          'TestingcompsTopprojects',
          'ImportSchreiber',
          'ImportExaminer',
          'TestingcompsCascades',
          'Supplier',
          'Expediting',
          'ExpeditingEvent',
          'DropdownsMastersData',
          'DropdownsMastersDependency',
          'OsiImport'
      );

        public function main()
        {
            $this->out('Hello world.');
        }

        public function OsiColumFirstLine(){

          $Osi = $this->OsiImport->find('first');
          pr($Osi);
        }
        
        public function OsiColumTest(){

          App::uses('Folder', 'Utility');
          App::uses('File', 'Utility');

          $dir = new Folder(Configure::read('document_folder'));
          $files = $dir->find('.*\.csv');

          foreach ($files as $file) {

              $file = new File($dir->pwd() . DS . $file);
              $Row = 0;

              if (($handle = fopen($file->path, "r")) !== FALSE) {
                  while (($data = fgetcsv($handle, 0, ";")) !== FALSE) {

                    $Test = $this->__OsiInsertRawData($data);

                    if($Test === false){
                      $this->out('Fehler beim Einfügen.');
                      return;
                    } else {
                      $Row++;
                    }

                }
                  fclose($handle);
              }

              $file->close();

          }

          $this->out($Row . ' ' . h('Datensätze eingefügt'));

        }

        protected function __OsiInsertRawData($Data){

          $Row = 'COL_';
          $Insert = array();
          foreach ($Data as $key => $value) {
            $Insert[$Row . $key] = h($value);
          }

          $this->OsiImport->create();
          if($this->OsiImport->save($Insert) === false) return false;
          else return true;

        }


        public function AddEventTotal(){

          $Supplier = $this->Supplier->find('all');

          $in_okay = 0;
          $in_not_okay = 0;

          foreach ($Supplier as $key => $value) {

            $SupplierId = $value['Supplier']['id'];

            $Expediting = $this->Expediting->find('all',array('conditions' => array('Expediting.supplier_id' => $SupplierId)));

            foreach ($Expediting as $_key => $_value) {

              $Insert['ExpeditingEvent'] = array();

              $Insert['ExpeditingEvent']['topproject_id'] = $value['Supplier']['topproject_id'];
              $Insert['ExpeditingEvent']['cascade_id'] = $value['Supplier']['cascade_id'];
              $Insert['ExpeditingEvent']['order_id'] = $value['Supplier']['order_id'];
              $Insert['ExpeditingEvent']['supplier_id'] = $value['Supplier']['id'];
              $Insert['ExpeditingEvent']['testingcomp_id'] = $value['Supplier']['testingcomp_id'];
              $Insert['ExpeditingEvent']['user_id']	= 1;
              $Insert['ExpeditingEvent']['expediting_id'] = $_value['Expediting']['id'];
              $Insert['ExpeditingEvent']['sequence'] = $_value['Expediting']['sequence'];
              $Insert['ExpeditingEvent']['date_soll'] = $_value['Expediting']['date_soll'];
              $Insert['ExpeditingEvent']['date_ist'] = $_value['Expediting']['date_ist'];
              $Insert['ExpeditingEvent']['status'] = $_value['Expediting']['status'];
              $Insert['ExpeditingEvent']['karenz'] = $_value['Expediting']['karenz'];
              $Insert['ExpeditingEvent']['discription'] = $_value['Expediting']['description'];
              $Insert['ExpeditingEvent']['remark'] = $_value['Expediting']['remark'];

              if(empty($Insert['ExpeditingEvent']['date_soll'])) continue;

              $this->ExpeditingEvent->create();

              if($this->ExpeditingEvent->save($Insert)){
                $in_okay++;
              } else {
                $in_not_okay++;
              }
            }
            $this->out($in_okay . ' Datensätze erstellt.');
            $this->out($in_not_okay . ' konten nicht erstellt werden.');

          }
        }

        public function ImportGP()
        {

          if(count($this->args) < 4) return;

          $mailconfig = 'gmail';
//          $mailconfig = 'default';
          $mailadresses = array('torsten.foth@mbq-gmbh.de');

          $projectID  = intval($this->args[0]);
          $cascadeID = intval($this->args[1]);
          $testingcompID = intval($this->args[2]);

          if($projectID == 0) return;
          if($cascadeID == 0) return;
          if($testingcompID == 0) return;

          if(isset($this->args[3]) && $this->args[3] != NULL){
            if(filter_var($this->args[3], FILTER_VALIDATE_EMAIL)){
              $mailadresses = array($this->args[3]);
            }
          }

          App::uses('Folder', 'Utility');
          App::uses('File', 'Utility');
          App::uses('CakeEmail', 'Network/Email');
          App::uses('Sanitize', 'Utility');

          $email = new CakeEmail();
          $emailcontent = NULL;
          $email->config($mailconfig);
          $email->from('info@zfp-datenbank.de');
          $email->to($mailadresses);
          $email->subject('Dateiimport German Pipe');
          $mailview ='default';
          $email->template($mailview,'default');
  				$email->emailFormat('both');

          $collection = new ComponentCollection();
          $this->Xml = new XmlComponent($collection);
/*
          $fields = array(
                      0 => 'order_no',
                      1 => 'delivery_date',
                      2 => 'delivery_amount',
                      5 => 'auftrags_nr',
                      8 => 'item_number',
                      9 => 'examination_object',
                      10 => 'examination_object_long',
                      11 => 'referenz_no',
                      15 => 'count_welds',
                    );
*/
          $fields = array(
            0 => 'order_no',
            1 => 'auftrags_nr',
            2 => 'auftraggeber',
            3 => 'auftraggeber_adress',
            4 => 'examination_object',
            5 => 'referenz_no',
            6 => 'count_welds',
            7 => 'material',
            8 => 'drawing_no',
            9 => 'weldingprocess',
            10 => 'heattreatment',
          );

          $xml_folder = Configure::read('xml_folder');
          $xml_folder = $xml_folder . 'orders' . DS . 'projects' . DS . $projectID . DS;
          $folder = new Folder($xml_folder);
    			$xmlfile = $folder->findRecursive('Order.xml');
          $value = $xml_folder.basename(reset($xmlfile));
          $xml = Xml::build($value);

          $pfad = Configure::read('root_folder');
          $phat = Configure::read('root_folder') . 'import' . DS;
          $archiv = Configure::read('root_folder') . 'import_archiv' . DS;

          $dir = new Folder($phat);

          $files = $dir->find('.*\.csv', true);

          if(count($files) == 0) return;

          $emailcontent .= "Es wurden neue Auftragsdaten bereitgestellt.\n";
          $emailcontent .= "Folgende Dateien werden importiert.\n\n";
          $emailcontent .= implode(", ",$files) . "\n";

          foreach ($files as $key => $value) {

            $file = new File($phat . $value);
            $fileinfo = $file->info();

            if($fileinfo['extension'] != 'csv') continue;

            $NewName =  time() . '_' . $key . '.csv';

            rename($phat . $value, $phat . $NewName);

            $file = new File($phat . $NewName);
            $file->copy($archiv . $NewName,true);
            $file->delete();

            $file = new File($archiv . $NewName);
            $openfile = $file->open('r',false);

            if($openfile === true){
              $content = $file->read(true, 'r');
              $content = iconv('windows-1252', 'utf-8', $content);
              $lines = explode(PHP_EOL, $content);
              $import = array();
              foreach($lines as $_key => $_value){
                $import[] = str_getcsv($_value, ';');
              }
            }

            $emailcontent .= "##################################################\n";
            $emailcontent .= "Datei" . " " . $value . "\n";
            $emailcontent .= count($import) . " " . "Datensätze gefunden\n";

            $file->close();

            $InsertCountOkay = 0;
            $InsertcountError = 0;
            $InsertcountDouble = 0;

            unset($import[0]);
            unset($import[1]);

            foreach ($import as $_key => $_value) {

              $Insert = array();

              $Insert['topproject_id'] = $projectID;
              $Insert['cascade_id'] = $cascadeID;
              $Insert['testingcomp_id'] = $testingcompID;
              $Insert['development_id'] = 1;
              $Insert['status'] = 0;

              foreach ($fields as $__key => $__value) {
                $Insert[$__value] =  ($_value[$__key]);
                $Insert[$__value] = Sanitize::escape($Insert[$__value]);
              }

              if($Insert['heattreatment'] == 'Nein'){
                $Insert['heattreatment'] = 2;
              } elseif($Insert['heattreatment'] == 'Ja') {
                $Insert['heattreatment'] = 1;
              } else {
                $Insert['heattreatment'] = 0;
              }

              $Insert['manufacturer'] = $Insert['auftraggeber'];
/*
              $DateCheck = explode('.', $Insert['delivery_date']);

              if(count($DateCheck) != 3) continue;

              $Check = checkdate($DateCheck[1], $DateCheck[0], $DateCheck[2]);

              if($Check === false) continue;

              unset($DateCheck);
*/
              if($Insert['count_welds'] > 0){
                $CountWelds = $Insert['count_welds'];
              } else {
                $CountWelds = 0;
              }

              $Test = $this->Order->find('first',array('conditions' => array('Order.auftrags_nr' => $Insert['auftrags_nr'])));

              if(count($Test) > 0){
                $InsertcountDouble++;
                $emailcontent .= "Der folgende Auftrag ist bereits vorhanden und wird nicht importiert: " . $_value[5] . "\n";
                continue;
              }

              $this->Order->create();

              if($this->Order->save($Insert)){
                pr('Order eingefügt');

                $InsertCountOkay++;

                $order_id = $this->Order->getLastInsertId();

                $allOrderIDS[] = $order_id;

                $Insert = array();
                $Insert['cascade_id'] = $cascadeID;
                $Insert['order_id'] = $order_id;

                $this->CascadesOrder->create();

                if($this->CascadesOrder->save($Insert)){
                  pr('CascadesOrder eingefügt');
                } else {
                  $emailcontent .= "\nBeim Datenimport ist ein Fehler aufgetreten, Fehlercode 001.\n";
                }

                $Insert = array();
                $Insert['testingcomp_id'] = $testingcompID;
                $Insert['order_id'] = $order_id;

                $this->OrdersTestingcomps->create();

                if($this->OrdersTestingcomps->save($Insert)){
                  pr('OrdersTestingcomps eingefügt');
                } else {
                  $emailcontent .= "\nBeim Datenimport ist ein Fehler aufgetreten, Fehlercode 002.\n";
                }

                $Insert = array();
                $Insert['testingcomp_id'] = $testingcompID;
                $Insert['order_id'] = $order_id;
                $Insert['topproject_id'] = $projectID;

                $this->Deliverynumber->create();

                if($this->Deliverynumber->save($Insert)){
                  pr('Deliverynumber eingefügt');
                } else {
                  $emailcontent .= "\nBeim Datenimport ist ein Fehler aufgetreten, Fehlercode 003.\n";
                }

                if($CountWelds > 0){

                  $Insert = array();
                  $Insert['development_id'] = 1;
                  $Insert['equipment_type_id'] = 0;
                  $Insert['equipment_id'] = 0;
                  $Insert['order_id'] = $order_id;
                  $Insert['topproject_id'] = $projectID;
                  $Insert['testing_method'] = 45;
                  $Insert['evaluation_id'] = 0;
                  $Insert['equipment_discription'] = 'Naht';
                  $Insert['result'] = 0;
                  $Insert['inital'] = 1;

                  for($i = 0; $i < $CountWelds; ++$i) {
                    $this->Development->DevelopmentData->create();
                    $this->Development->DevelopmentData->save($Insert);
                  }

                  $Insert = array();
                  $Insert['development_id'] = 1;
                  $Insert['order_id'] = $order_id;

                  $this->OrdersDevelopments->create();
                  $this->OrdersDevelopments->save($Insert);
                  pr('OrdersDevelopments eingefügt');

                }
              } else {
                $emailcontent .= "\nBeim Datenimport ist ein Fehler aufgetreten, Fehlercode 004.\n";
              }
            }

            $emailcontent .= $InsertCountOkay . " " . "Datensätze eingefügt\n";

          }

  				$email->viewVars(array('content' => $emailcontent));
          $email->send();

        }

        public function ImportSupervisor(){

          if(!isset($this->args[0])) return;
          if(!isset($this->args[1])) return;
          if(!isset($this->args[2])) return;

          $Testincomp  = intval($this->args[0]);
          $DropdownId = intval($this->args[1]);
          $Verfahren = $this->args[2];

          $HelpArray['level_2'] = $Verfahren . '_2';
          $HelpArray['level_2_certificate'] = 'Zertifikats_Nr_'.$Verfahren.'_2';
          $HelpArray['level_3'] = $Verfahren . '_3';
          $HelpArray['level_3_certificate'] = 'Zertifikats_Nr_'.$Verfahren.'_3';

          $Fields['fields'] = array('Name','Pruefaufsicht','Zertifikats_Nr_'.$Verfahren.'_2',$Verfahren.'_2','Zertifikats_Nr_'.$Verfahren.'_3',$Verfahren.'_3');

          $ImportExaminer = $this->ImportExaminer->find('all',$Fields);

          $Option['conditions']['dropdowns_masters_id'] = $DropdownId;

          foreach ($ImportExaminer as $key => $value) {


            $insert = array();

            if(empty($value['ImportExaminer']['Pruefaufsicht'])) continue;
            if(empty($value['ImportExaminer'][$HelpArray['level_2']]) && empty($value['ImportExaminer'][$HelpArray['level_3']])) continue;

            $Option['conditions']['dropdowns_masters_id'] = $DropdownId;
            $Option['conditions']['value'] = trim($value['ImportExaminer']['Name']);

            $DropdownsMastersData = $this->DropdownsMastersData->find('first',$Option);

            if(count($DropdownsMastersData) == 0){

              $insert['dropdowns_masters_id'] = $DropdownId;
              $insert['value'] = trim($value['ImportExaminer']['Name']);
              $insert['status'] = 0;

              $this->DropdownsMastersData->create();
              $this->DropdownsMastersData->save($insert);
              $insert_masterdropdown_id = $this->DropdownsMastersData->getLastInsertId();

              $DropdownsMastersData = $this->DropdownsMastersData->find('first',array('conditions' => array('DropdownsMastersData.id' => $insert_masterdropdown_id)));

            }

            $level = null;
            $cert = null;

            if(!empty($value['ImportExaminer'][$HelpArray['level_3']]) && !empty($value['ImportExaminer'][$HelpArray['level_3_certificate']])){
              $level = $value['ImportExaminer'][$HelpArray['level_3']];
              $cert = $value['ImportExaminer'][$HelpArray['level_3_certificate']];
              $this->__UpdateSupervisor($DropdownsMastersData,$value,$level,$cert,3);
              continue;
            }

            if(!empty($value['ImportExaminer'][$HelpArray['level_2']]) && !empty($value['ImportExaminer'][$HelpArray['level_2_certificate']])){
              $level = $value['ImportExaminer'][$HelpArray['level_2']];
              $cert = $value['ImportExaminer'][$HelpArray['level_2_certificate']];
              $this->__UpdateSupervisor($DropdownsMastersData,$value,$level,$cert,2);
              continue;
            }

            if(!empty($value['ImportExaminer'][$HelpArray['level_1']]) && !empty($value['ImportExaminer'][$HelpArray['level_1_certificate']])){
              $level = $value['ImportExaminer'][$HelpArray['level_1']];
              $cert = $value['ImportExaminer'][$HelpArray['level_1_certificate']];
              $this->__UpdateSupervisor($DropdownsMastersData,$value,$level,$cert,1);
              continue;
            }
          }
        }

        public function ImportExaminer(){

          $DropdownId = 4;
          $Testincomp = 1;
          $Verfahren = 'PT';

          if(!isset($this->args[0])) return;
          if(!isset($this->args[1])) return;
          if(!isset($this->args[2])) return;

          $Testincomp  = intval($this->args[0]);
          $DropdownId = intval($this->args[1]);
          $Verfahren = $this->args[2];

          $HelpArray['level_1'] = $Verfahren . '_1';
          $HelpArray['level_1_certificate'] = 'Zertifikats_Nr_'.$Verfahren.'_1';
          $HelpArray['level_2'] = $Verfahren . '_2';
          $HelpArray['level_2_certificate'] = 'Zertifikats_Nr_'.$Verfahren.'_2';
          $HelpArray['level_3'] = $Verfahren . '_3';
          $HelpArray['level_3_certificate'] = 'Zertifikats_Nr_'.$Verfahren.'_3';

          $Fields['fields'] = array('Name','Zertifikats_Nr_'.$Verfahren.'_1',$Verfahren.'_1','Zertifikats_Nr_'.$Verfahren.'_2',$Verfahren.'_2','Zertifikats_Nr_'.$Verfahren.'_3',$Verfahren.'_3');

          $ImportExaminer = $this->ImportExaminer->find('all',$Fields);

          $Option['conditions']['dropdowns_masters_id'] = $DropdownId;

          foreach ($ImportExaminer as $key => $value) {

            $insert = array();

            if(empty($value['ImportExaminer'][$HelpArray['level_1']]) && empty($value['ImportExaminer'][$HelpArray['level_2']]) && empty($value['ImportExaminer'][$HelpArray['level_3']])){
              continue;
            }

            $Option['conditions']['dropdowns_masters_id'] = $DropdownId;
            $Option['conditions']['value'] = trim($value['ImportExaminer']['Name']);

            $DropdownsMastersData = $this->DropdownsMastersData->find('first',$Option);

            if(count($DropdownsMastersData) == 0){

              $insert['dropdowns_masters_id'] = $DropdownId;
              $insert['value'] = trim($value['ImportExaminer']['Name']);
              $insert['status'] = 0;

              $this->DropdownsMastersData->create();
              $this->DropdownsMastersData->save($insert);
              $insert_masterdropdown_id = $this->DropdownsMastersData->getLastInsertId();

              $DropdownsMastersData = $this->DropdownsMastersData->find('first',array('conditions' => array('DropdownsMastersData.id' => $insert_masterdropdown_id)));

            }

              $level = null;
              $cert = null;

              if(!empty($value['ImportExaminer'][$HelpArray['level_3']]) && !empty($value['ImportExaminer'][$HelpArray['level_3_certificate']])){
                $level = $value['ImportExaminer'][$HelpArray['level_3']];
                $cert = $value['ImportExaminer'][$HelpArray['level_3_certificate']];
                $this->__UpdateExaminer($DropdownsMastersData,$value,$level,$cert,3);
                continue;
              }

              if(!empty($value['ImportExaminer'][$HelpArray['level_2']]) && !empty($value['ImportExaminer'][$HelpArray['level_2_certificate']])){
                $level = $value['ImportExaminer'][$HelpArray['level_2']];
                $cert = $value['ImportExaminer'][$HelpArray['level_2_certificate']];
                $this->__UpdateExaminer($DropdownsMastersData,$value,$level,$cert,2);
                continue;
              }

              if(!empty($value['ImportExaminer'][$HelpArray['level_1']]) && !empty($value['ImportExaminer'][$HelpArray['level_1_certificate']])){
                $level = $value['ImportExaminer'][$HelpArray['level_1']];
                $cert = $value['ImportExaminer'][$HelpArray['level_1_certificate']];
                $this->__UpdateExaminer($DropdownsMastersData,$value,$level,$cert,1);
                continue;
              }

            }
        }

        protected function __UpdateExaminer($DropdownsMastersData,$value,$leveldata,$cert,$level){

          $Testincomp  = intval($this->args[0]);
          $DropdownId = intval($this->args[1]);
          $Verfahren = $this->args[2];

          $Insert = array();

          $this->out($value['ImportExaminer']['Name']);

          $Insert['testingcomp_id'] = $Testincomp;
          $Insert['dropdowns_masters_id'] = $DropdownId;
          $Insert['dropdowns_masters_data_id'] = $DropdownsMastersData['DropdownsMastersData']['id'];
          $Insert['field'] = 'examiner_certificat';
          $Insert['value'] = $cert;
          $Insert['global'] = 0;

          $this->DropdownsMastersDependency->create();
          $this->DropdownsMastersDependency->save($Insert);

          $Insert['field'] = 'examiner_certificat_level';
          $Insert['value'] = $level;

          $this->DropdownsMastersDependency->create();
          $this->DropdownsMastersDependency->save($Insert);

          $Insert = array();

        }

        protected function __UpdateSupervisor($DropdownsMastersData,$value,$leveldata,$cert,$level){

          $Testincomp  = intval($this->args[0]);
          $DropdownId = intval($this->args[1]);
          $Verfahren = $this->args[2];

          $Insert = array();

          $this->out($value['ImportExaminer']['Name']);

          $Insert['testingcomp_id'] = $Testincomp;
          $Insert['dropdowns_masters_id'] = $DropdownId;
          $Insert['dropdowns_masters_data_id'] = $DropdownsMastersData['DropdownsMastersData']['id'];
          $Insert['field'] = 'supervisor_certificat';
          $Insert['value'] = $cert;
          $Insert['global'] = 0;

          pr($Insert);
          $this->DropdownsMastersDependency->create();
          $this->DropdownsMastersDependency->save($Insert);

          $Insert['field'] = 'supervisor_certificat_level';
          $Insert['value'] = $level;
          pr($Insert);
          $this->DropdownsMastersDependency->create();
          $this->DropdownsMastersDependency->save($Insert);

          $Insert = array();

        }

        public function CreateTopprojectAll()
        {

          $testingcomp_id = 1;

          $ImportSchreiber = $this->ImportSchreiber->find('all',array('fields' => array('kunde DISTINCT')));


          foreach ($ImportSchreiber as $key => $value) {

            $Insert = array();
            $Insert['projektname'] = $value['ImportSchreiber']['kunde'];
            $Insert['identification'] = $key;
            $Insert['projektbeschreibung'] = '-';
            $Insert['subdivision'] = 0;
            $Insert['status'] = 0;

            $this->Topprojects->create();
            if($this->Topprojects->save($Insert)){

              $topproject_id = $this->Topprojects->getLastInsertId();
              $this->out($Insert['projektname'] . ' insert ' . $topproject_id);

              $Insert = array();
              $Insert['topproject_id'] = $topproject_id;
              $Insert['report_id'] = 1;

              $this->ReportsTopprojects->create();

              if($this->ReportsTopprojects->save($Insert)){
                $this->out('ReportsTopprojects okay');
              } else {
                $this->out('ReportsTopprojects error');
              }

              $Insert = array();
              $Insert['topproject_id'] = $topproject_id;
              $Insert['testingcomp_id'] = $testingcomp_id;

              $this->TestingcompsTopprojects->create();

              if($this->TestingcompsTopprojects->save($Insert)){
                $this->out('TestingcompsTopprojects okay');
              } else {
                $this->out('TestingcompsTopprojects error');
              }

              $Insert = array();
              $Insert['topproject_id'] = $topproject_id;
              $Insert['discription'] = $value['ImportSchreiber']['kunde'];
              $Insert['status'] = 1;
              $Insert['level'] = 0;
              $Insert['parent'] = 0;
              $Insert['orders'] = 1;
              $Insert['child'] = 0;

              $this->Cascade->create();

              if($this->Cascade->save($Insert)){

                $this->out('Cascade okay');
                $cascade_id = $this->Cascade->getLastInsertId();

                $Insert = array();
                $Insert['cascade_id'] = $cascade_id;
                $Insert['testingcomp_id'] = $testingcomp_id;

                $this->TestingcompsCascades->create();

                if($this->TestingcompsCascades->save($Insert)){
                  $this->out('TestingcompsCascades okay');
                } else {
                  $this->out('TestingcompsCascades error');
                }

              } else {
                $this->out('Cascade error');
              }

            } else {
              $this->out($Insert['projektname'] . ' error');
            }
          }
        }

        public function CreateOrdersAll()
        {
          $testingcomp_id = 1;

          $Topprojects = $this->Topprojects->find('all');

          foreach ($Topprojects as $key => $value) {

            $Cascades = $this->Cascade->find('first',array('conditions' => array('Cascade.topproject_id' => $value['Topprojects']['id'])));
            $cascade_id = $Cascades['Cascade']['id'];
            $topproject_id = $value['Topprojects']['id'];
//            pr($value['Topprojects']['projektname']);
            $ImportSchreiber = $this->ImportSchreiber->find('all',array('conditions' => array('ImportSchreiber.kunde' => $value['Topprojects']['projektname'])));
            foreach ($ImportSchreiber as $_key => $_value) {

              $Insert = array();
              $Insert['cascade_id'] = $cascade_id;
              $Insert['testingcomp_id'] = $testingcomp_id;
              $Insert['topproject_id'] = $topproject_id;
              $Insert['auftrags_nr'] = $_value['ImportSchreiber']['kostenstelle'];
              $Insert['auftraggeber'] = $_value['ImportSchreiber']['kunde'];
              $Insert['auftraggeber_adress'] = $_value['ImportSchreiber']['strasse'] . '; ' . $_value['ImportSchreiber']['plz'] . ' ' . $_value['ImportSchreiber']['ort'];
              $Insert['project_name'] = $_value['ImportSchreiber']['project'];

              $this->Order->create();

              if($this->Order->save($Insert)){
                $this->out('Order okay');

                $order_id = $this->Order->getLastInsertId();

                $Insert = array();
                $Insert['cascade_id'] = $cascade_id;
                $Insert['order_id'] = $order_id;

                $this->CascadesOrder->create();

                if($this->CascadesOrder->save($Insert)){
                  $this->out('CascadesOrder okay');
                } else {
                  $this->out('CascadesOrder error');
                }

                $Insert = array();
                $Insert['testingcomp_id'] = $testingcomp_id;
                $Insert['order_id'] = $order_id;

                $this->OrdersTestingcomps->create();

                if($this->OrdersTestingcomps->save($Insert)){
                  $this->out('OrdersTestingcomps okay');
                } else {
                  $this->out('OrdersTestingcomps error');
                }

                $Insert = array();
                $Insert['testingcomp_id'] = $testingcomp_id;
                $Insert['order_id'] = $order_id;
                $Insert['topproject_id'] = $topproject_id;

                $this->Deliverynumber->create();

                if($this->Deliverynumber->save($Insert)){
                  $this->out('Deliverynumber okay');
                } else {
                  $this->out('Deliverynumber error');
                }

              } else {
                $this->out('Order error');
              }
            }
          }
        }


    public function dateoftest() {
    $this->loadModel('Testingmethod');
  $this->loadModel('Reportnumber');
    $methods = $this->Testingmethod->find('all');

    foreach ($methods as $key => $value) {

      $generally = "Report".ucfirst($value['Testingmethod']['value']).'Generally';
      $this->loadModel($generally);
      $generallydatas = $this->$generally->find('list',array('fields'=>array('reportnumber_id', 'date_of_test')));
      foreach ($generallydatas as $gkey => $gvalue) {
        if ($this->validateDate($gvalue, 'Y-m-d') == true) {
          $reportnumber = $this->Reportnumber->find('first',array('conditions' => array('Reportnumber.id' => $gkey)));
          $reportnumber ['Reportnumber']['date_of_test'] = $gvalue;
          $this->Reportnumber->save($reportnumber ['Reportnumber']);
        }
      }

      // code...
    }

  }

    function validateDate($date, $format = 'Y-m-d H:i:s')
    {
    $d = DateTime::createFromFormat($format, $date);
    return $d && $d->format($format) == $date;
  }

  function addfieldsql (){// fieldname = name, $model = "Generally"

    $fieldname =  $this->args[0];
    $model = $this->args[1];
    $this->loadModel('Testingmethod');
    $methods = $this->Testingmethod->find('all');

    foreach ($methods as $key => $value) {

      $importmodel = "Report".ucfirst($value['Testingmethod']['value']).$model;
      $this->loadModel($importmodel);
      $this->$importmodel->query("ALTER TABLE "."report_".$value['Testingmethod']['value']."_".lcfirst($model)." ADD ".$fieldname." VARCHAR(200) NULL; ");
    }
  }
    }
