<?php

    App::uses('ComponentCollection', 'Controller');
    App::uses('NavigationComponent', 'Controller/Component');
    App::uses('XmlComponent', 'Controller/Component');

    class RklShell extends AppShell
    {
        public $uses = array(
          'Topprojects',
          'ReportsTopprojects',
          'TestingmethodsReports',
          'Testingmethod',
          'Cascade',
          'Deliverynumber',
          'Rkl',
          'RklNumber',
          'RklsAnsiFluid',
          'RklsAnsiWallThicknessNoteWt',
          'RklsAnsiWallThickness',
          'RklsAnsiReducersTableId',
          'RklsAnsiPressureTemp',
          'RklsAnsiPipingClassTorquesNote',
          'RklsAnsiPipingClassTorque',
          'RklsAnsiPipingClassPart',
          'RklsAnsiPipingClassNote',
          'RklsAnsiPipingClassBasicData',
          'RklsAnsiFlangedPipesLenght',
          'RklsAnsiBranchTypAB',
          'RklsAnsiBranchHeadData',
          'RklsAnsiBranchConnection',
      );

        public function main()
        {
            $this->out('Hello world.');
        }

        public function TestRkls() {

          $Test = $this->RklNumber->find('first',array('conditions' => array('RklNumber.id' => 22)));

          pr(count($Test));

        }

        public function SeparadeRkls() {

          $Models = array(
            'RklsAnsiFluid',
            'RklsAnsiWallThicknessNoteWt',
            'RklsAnsiWallThickness',
            'RklsAnsiReducersTableId',
            'RklsAnsiPressureTemp',
            'RklsAnsiPipingClassTorquesNote',
            'RklsAnsiPipingClassTorque',
            'RklsAnsiPipingClassPart',
            'RklsAnsiPipingClassNote',
            'RklsAnsiPipingClassBasicData',
            'RklsAnsiFlangedPipesLenght',
            'RklsAnsiBranchTypAB',
            'RklsAnsiBranchHeadData',
            'RklsAnsiBranchConnection',
          );

          foreach ($Models as $key => $value) {

            $Test = $this->{$value}->find('all',array('fields' => array('rkl_nr DISTINCT')));
            $results = Hash::extract($Test, '{n}.'.$value.'.rkl_nr');
            if(count($results) == 0) continue;
            $this->_CheckUpdateTable($results,$value);

          }
        }

        private function _CheckUpdateTable($data,$table) {

          if(count($data) == 0) return;

          foreach ($data as $key => $value) {

            if(empty($value)) continue;
            if($value == '') continue;

            $Test = $this->RklNumber->find('first',array('conditions' => array('RklNumber.description' => $value)));

            if(count($Test) == 1){

              $TableUpdate = $this->{$table}->find('all',array('conditions' => array($table.'.rkl_nr' => $value)));

              if(count($TableUpdate) == 0) continue;

              $this->_TableUpdate($table,$TableUpdate,$Test['RklNumber']['id']);

            }
            if(count($Test) == 0){
              $insert = array('description' => $value);
              $this->RklNumber->create();
              $this->RklNumber->save($insert);
              $Rkl_id = $this->RklNumber->getLastInsertId();

              $TableUpdate = $this->{$table}->find('all',array('conditions' => array($table.'.rkl_nr' => $value)));

              if($Rkl_id == 0) continue;
              if(count($TableUpdate) == 0) continue;

              $this->_TableUpdate($table,$TableUpdate,$Rkl_id);

            }
          }
        }

        private function _TableUpdate($table,$TableUpdate,$id) {

          foreach ($TableUpdate as $key => $value) {

            $value[key($value)]['rkl_number_id'] = $id;
            $this->{$table}->save($value[key($value)]);

          }
        }

        public function import() {


        //  if(count($this->args) < 4) return;



          App::uses('Folder', 'Utility');
          App::uses('File', 'Utility');
          App::uses('CakeEmail', 'Network/Email');
          App::uses('Sanitize', 'Utility');



          $collection = new ComponentCollection();
          $this->Xml = new XmlComponent($collection);
/*
          $fields = array(
                      0 => 'Rkl_no',
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
            0 => 'crb',
            1 => 'unit',
            2 => 'line_item',
            3 => 'diameter_in',
            4 => 'free1',
            5 => 'class',
            6 => 'location_from',
            7 => 'location_to',
            8 => 'fluid',
            9 => 'pressbarg',
            10 => 'temp',
            11 => 'pressbarg_dc',
            12 => 'temp_dc',
            13 =>'mm',
            14 => 'free2',
            15 => 'lm',
            16 => 'paint',
            17 => 'minbarg',
            18 => 'maxbarg',
            19 => 'group',
            20 => 'color_new',
          );

          $xml_folder = Configure::read('xml_folder');
        //  $xml_folder = $xml_folder . 'Rkls' . DS . 'projects' . DS . $projectID . DS;
          $folder = new Folder($xml_folder);
    			$xmlfile = $folder->findRecursive('Rkl.xml');
          $value = $xml_folder.basename(reset($xmlfile));
          $xml = Xml::build($value);

          $pfad = Configure::read('root_folder');
          $phat = Configure::read('root_folder') . 'import' . DS;
          $archiv = Configure::read('root_folder') . 'import_archiv' . DS;

          $dir = new Folder($phat);

          $files = $dir->find('.*\.csv', true);

          if(count($files) == 0) return;

          $emailcontent .= "Es wurden neue Einträge bereitgestellt.\n";
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

            //unset($import[0]);
        //    unset($import[1]);

            foreach ($import as $_key => $_value) {
//var_dump($import); die();
              $Insert = array();


              foreach ($fields as $__key => $__value) {
                $Insert[$__value] =  ($_value[$__key]);
                $Insert[$__value] = Sanitize::escape($Insert[$__value]);
              }

              $Test = $this->Rkl->find('first',array('conditions' => array('Rkl.crb' => $Insert['crb'])));

              if(count($Test) > 0){
                $InsertcountDouble++;
                $emailcontent .= "Der folgende Eintrag ist bereits vorhanden und wird nicht importiert: " . $_value[5] . "\n";
                continue;
              }

              $this->Rkl->create();

              if($this->Rkl->save($Insert)){
                pr('Rkl eingefügt');

                $InsertCountOkay++;

                $Rkl_id = $this->Rkl->getLastInsertId();

                $allRklIDS[] = $Rkl_id;


                }



            }

            $emailcontent .= $InsertCountOkay . " " . "Datensätze eingefügt\n";




          }


    }

}
