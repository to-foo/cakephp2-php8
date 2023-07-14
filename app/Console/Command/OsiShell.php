<?php

    class OsiShell extends AppShell
    {
        public $uses = array(
          'OsiImport',
          'OsiImportMp',
          'OsiImportFutureMp',
          'OsiImportRest',
      );

        public function main()
        {
            $this->out('Hello world.');
        }

        protected function __Messages($Data)
        {

          return;

          $this->out($Data);

        }

        protected function __Models($key = false)
        {
            $Models = array(
            'OsiImport',
            'OsiImportMp',
            'OsiImportFutureMp',
            'OsiImportRest');

            if($key === false) return $Models;

            $element = --$key;

            return $Models[$element];
        }

        protected function __CorrectionLage($Term){

          $model = $this->__Models(1);

          $Conditions = array(
              'group' => array('COL_1'),
              'fields' => array('COL_0','COL_1','id'),
              'conditions' => array(
                $model . '.id >' => 1,
                $model . '.COL_5' => 'US',
                $model . '.COL_2 LIKE' => '%' . $Term . '%',
              )
            );

          $Find = $this->{$model}->find('list',$Conditions);

          if(count($Find) == 0) return;

          foreach ($Find as $key => $value) {

            $Conditions = array(
                'group' => array('COL_6'),
                'fields' => array('COL_6'),
                'conditions' => array(
                  $model . '.id >' => 1,
                  $model . '.COL_0' => key($value),
                  $model . '.COL_1' => $value[key($value)],
                  $model . '.COL_2 LIKE' => '%' . $Term . '%',
                  $model . '.COL_5' => 'US',
                )
              );

            $FindMP = $this->{$model}->find('list',$Conditions);

            if(count($FindMP) == 0) continue;

            foreach ($FindMP as $_key => $_value) {

              $Conditions = array('conditions' => array(
                $model . '.id >' => 1,
                $model . '.COL_0' => key($value),
                $model . '.COL_1' => $value[key($value)],
                $model . '.COL_2 LIKE' => '%' . $Term . '%',
                $model . '.COL_5' => 'US',
                $model . '.COL_6' => $_value,
                )
              );

              $FindLage = $this->{$model}->find('all',$Conditions);

              $this->__ChangeLageValue($FindLage);

            }
          }
        }

        protected function __ChangeLageValue($Data)
        {

          if(count($Data) == 0) return;

          $Round = 1;
          $Start = 1;
          $Max = 8;

          foreach ($Data as $key => $value) {

            if($value[key($value)]['COL_7'] != $Round.'-'.$Start){

              if(empty($Data[$key][key($value)]['COL_16'])) $Data[$key][key($value)]['COL_16'] = $Data[$key][key($value)]['COL_7'];
              else $Data[$key][key($value)]['COL_16'] .= '; ' . $Data[$key][key($value)]['COL_7'];

              $Data[$key][key($value)]['COL_7'] = $Round.'-'.$Start;
            }

            ++$Start;

            if($Start > $Max){
                $Start = 1;
                ++$Round;
            }

            if($this->{key($value)}->save($Data[$key][key($value)])){

            } else {

              $this->__Messages($Data[$key][key($value)]['id'] . ' Fehler beim speichern');

            }
          }
        }

        protected function __UpdateTables($mod)
        {

          App::uses('Folder', 'Utility');
          App::uses('File', 'Utility');

            if($mod == 'delete') $this->out(h('Datenbanktabellen werden geleert.'));

            $models = $this->__Models();
            $dir = new Folder(Configure::read('document_folder') . DS . 'sql' . DS . $mod . DS);
            $files = $dir->find('.*\.sql');

            foreach ($models as $key =>$file) {
                if (file_exists($dir->path . $file . '.sql')) {
                    $Query = file_get_contents($dir->path . $file . '.sql');
                    $this->{$file}->query($Query);
                }
            }

            if($mod == 'delete') $this->out(h('Datenbanktabellen geleert.'));

        }

        protected function __OsiInsertRawData($Data)
        {
            $models = $this->__Models();

            $Row = 'COL_';
            $Insert = array();
            foreach ($Data as $key => $value) {
                $Insert[$Row . $key] = h($value);
            }

            foreach ($models as $key => $value) {
                $this->{$value}->create();
                if($this->{$value}->save($Insert)){
//                  $this->out($value . ' ' . $this->{$value}->getLastInsertID());
                } else {
                  $this->out($value . ' ' . pr($Insert));
                }
            }
        }

        protected function __ChangeBackColumNames(){

          $Output = array();

          $models = $this->__Models();

          foreach ($models as $key => $value) {

            $Find = $this->{$value}->find('first',array('conditions' => array($value . '.id' => 1)));

            if(count($Find) == 0) return false;

            foreach ($Find[$value] as $_key => $_value) {

              $Output[$value][] = $_key;
            }
          }

          return $Output;

        }

        protected function __FirstRowColumName()
        {

          $Output = array();

          $models = $this->__Models();

          foreach ($models as $key => $value) {
            $Find = $this->{$value}->find('first',array('conditions' => array($value . '.id' => 1)));

            if(count($Find) == 0) return false;

            $x = 1;

            foreach ($Find[$value] as $_key => $_value) {

              if($_key == 'id'){
                $Output[$value]['id'] = 'id';
                continue;
              }

              if(empty($_value)){

                $Output[$value][$_key] = 'Free_' . $x;
                $x++;

              } else {
                $Output[$value][$_key] = $_value;

              }
            }
          }

          return $Output;
        }

        protected function __DescriptionRowColumName($Data){


          App::uses('Folder', 'Utility');
          App::uses('File', 'Utility');

          $descriptiondir = new Folder(Configure::read('document_folder') . DS . 'description' . DS);
          $files = $descriptiondir->find('.*\.csv');
          $Output = array();

          if(count($files) == 0) return false;

          foreach ($Data as $key => $value) {
            $Output[$key][] = $value;
          }

          foreach ($files as $key => $value) {

            $file = new File($descriptiondir->pwd() . DS . $value);
            $Model = explode('.',$file->name);

            unset($Output[$Model[0]][0]['id']);

            if (($handle = fopen($file->path, "r")) !== false) {
              while (($data = fgetcsv($handle, 0, ";")) !== false) {

                $Row = array();

                foreach ($data as $_key => $_value) {
                  $Row[$_key] = $_value;
                }

                $x = 0;
                $RowCOL = array();

                foreach ($Output[$Model[0]][0] as $_key => $_value) {

                  $RowCOL[$_key] = $Row[$x];

                  $x++;
                }

                $Output[$Model[0]][] = $RowCOL;
              }
            }
          }
          return $Output;

        }

        protected function __DescriptionMpRowColumName($Data){

          $Model1 = $this->__Models(2);

          $Output = array();
          $Output[0] = array();

          $Check = array_flip($Data[$Model1][0]);

          foreach ($Data[$Model1] as $key => $value) {
            foreach($value as $_key => $_value){

              $pos = strpos($_value, 'Messung');

              if($pos === false) continue;

              $Field = array('Messung',' ');
              $Output[0][$_key] = $Field ;


            }
          }

          $SecoutRow = array();

          foreach ($Output[0] as $key => $value) {

            $val = explode(' ',trim($Data[$Model1][0][$key]));

            if(count($val) != 2) return false;

            $Testdata = explode(' ',trim($Data[$Model1][0][$key]));

            if(isset($Check['Bemerkung ' . $Testdata[1]])){
              $Field = array($val[1],'Bemerkung');
            } else {
              $Field = array($val[1]);
              unset($Output[0][$key][1]);
            }

            $Output[1][$key] = $Field;

          }

          $Data[$Model1][1] = $Output[0];
          $Data[$Model1][2] = $Output[1];

          $Model2 = $this->__Models(3);

          $Output = array();
          $Output[0] = array();

          foreach ($Data[$Model2] as $key => $value) {
            foreach($value as $_key => $_value){

              if($_key == 'id') continue;

              $Output[1][$_key] = $_value;
              $Output[2][$_key] = ' ';
            }
          }

          $Data[$Model2][1] = $Output[1];
          $Data[$Model2][2] = $Output[2];

          $MPs = array();

          foreach($Data[$Model1][1] as $key => $value){

            if(count($value) == 1) {
              $MPs[1][$key] = $value[0];
            } elseif(count($value) == 2) {

              $KeyArray = explode('_',$key);
              $MPs[1][$key] = $value[0];
              $MPs[1]['COL_' . ++$KeyArray[1] ] = $value[1];
            }
          }

          foreach($Data[$Model1][2] as $key => $value){

            if(count($value) == 1) {
              $MPs[2][$key] = $value[0];
            } elseif(count($value) == 2) {

              $KeyArray = explode('_',$key);
              $MPs[2][$key] = $value[0];
              $MPs[2]['COL_' . ++$KeyArray[1] ] = $value[1];
            }
          }

          $Data[$Model1][1] = $MPs[1];
          $Data[$Model1][2] = $MPs[2];

          foreach($Data as $key => $value){

            foreach ($value as $_key => $_value) {
              if(isset($Data[$key][$_key]['id'])) unset($Data[$key][$_key]['id']);
            }

            unset($Data[$key][0]);

          }

          return $Data;

        }

        protected function __ChangeColumName($Find,$FirstRow,$models){

          $Output = array();

          foreach ($models as $key => $value) {
            foreach ($Find[$value] as $_key => $_value) {
              foreach ($_value[$value] as $__key => $__value) {
                $Output[$value][$_key][$value][$FirstRow[$value][$__key]] = $__value;
              }
            }
          }

          return $Output;
        }

        protected function __FindData($Mode){

          $Output = array();

          $models = $this->__Models();

          foreach ($models as $key => $value) {

//            $Find = $this->{$value}->find('all',array('limit' => 2,'conditions' => array($value . '.id >' => 1)));
            $Find = $this->{$value}->find('all',array('conditions' => array($value . '.id >' => 1)));
            $Output[$value] = $Find;

          }

          if($Mode == 2) return $Output;

          $FirstRow = $this->__FirstRowColumName();

          $Output = $this->__ChangeColumName($Output,$FirstRow,$models);

          return $Output;

        }

        protected function __CheckMPNumeric($Data,$Rows){

          $Model = key($Data);

          foreach ($Rows as $key => $value) {
            $KeyM = 'Messung ' . $key;
            $KeyB = 'Bemerkung ' . $key;

            if(isset($Data[$Model][$KeyM])) $Rows[$key][$KeyM] = $Data[$Model][$KeyM];
            if(isset($Data[$Model][$KeyB])) $Rows[$key][$KeyB] = $Data[$Model][$KeyB];

          }

          foreach ($Rows as $key => $value) {

            if(empty($value['Messung ' . $key])){

              if(isset($Data[$Model]['Bemerkung ' . $key])) $Data[$Model]['Bemerkung ' . $key] = '';

            } else {

              $Val = str_replace(",", ".", $value['Messung ' . $key]);

              if(is_numeric($Val) == false){

                $Data[$Model]['Messung ' . $key] = '';
                $Data[$Model]['Bemerkung ' . $key] = '';

                continue;
              }
            }
          }

          return $Data;
        }

        protected function __CorrectionMPNumbers($Data){
//537
          $Model = $this->__Models(2);

          if(!isset($Data[$Model][0])) return $Data;

          $Rows = array();

          foreach ($Data[$Model][0] as $key => $value) {

            foreach ($value as $key => $value) {

              if($key == 'id') continue;

              $TrimKey = trim($key);

              $pos = strpos($TrimKey, 'Messung');

              if($pos === false) continue;

              $KeyArray = explode(' ',$TrimKey);

              if(count($KeyArray) != 2) return false;

              $Rows[$KeyArray[1]] = array('Messung ' . $KeyArray[1] => 0,'Bemerkung ' . $KeyArray[1] => 0);

            }
          }

          foreach ($Data[$Model] as $key => $value) {
            $Data[$Model][$key] = $this->__CheckMPNumeric($value,$Rows);
          }

          return $Data;
        }

        protected function __OsiImportMpFunctions($Data){

          $Data = $this->__CorrectionMPNumbers($Data);


          return $Data;
        }

        protected function __OsiImportFunctions($Data){

          $Data = $this->__OsiImportFunctionsCheckEmptyFields($Data);
          $Data = $this->__OsiImportFunctionsCheckDevValue($Data);


          return $Data;
        }

        // Es sind hier nur x zulässig, x-SD,xy,zx usw. zu x ändern
        protected function __OsiImportFutureFunctionsCheckVal($Data,$Placeholder){

          $Model = $this->__Models(3);

          foreach ($Data[$Model] as $key => $value) {
            foreach ($value[$Model] as $_key => $_value) {

              if($_key ==  'id') continue;
              if(empty($_value)) continue;

              $Data[$Model][$key][$Model][$_key] = $Placeholder;

            }
          }

          return $Data;
        }

        // Planungskreuze (x, x-SD, xy, zx) aus „früher als 2022“ löschen,
        // falls ein x (oder xy,xz,x-SD usw) in 2022 oder später vorhanden ist
        // Falls nicht: x in 2022 schreiben
        // Ziel: jeder MP muss ein Planungskreuz in 2022 oder später aufweisen
        protected function __OsiImportFutureFunctionsDeletePast($Data,$Field){

          $Model = $this->__Models(3);

          foreach ($Data[$Model] as $key => $value) {

            // Alles vor $Field löschen
            foreach ($value[$Model] as $_key => $_value) {

              if($_key ==  'id') continue;
              if($_key >=  $Field) continue;

              $Data[$Model][$key][$Model][$_key] = '';

            }

            $HasValue = false;

            foreach ($value[$Model] as $_key => $_value) {

              if($_key ==  'id') continue;
              if($_key <  $Field) continue;

              if(!empty($_value)) $HasValue = true;

            }

            if($HasValue === false){
              $Data[$Model][$key][$Model][$Field] = 'x';
            }
          }

          return $Data;
        }

        // Wenn in Planungsspalte eine „Auftragsnummer“ zB 20AT831-370 steht,
        // dann diese durch ein x ersetzen UND die Auftragsnummer in
        // die Bemerkungsspalte des zugehörigen Messergebnisses eintragen
        /*

        Geht natürlich nur, wenn für den Jahreswert eine Spalte existiert

        */
        protected function __OsiImportFutureFunctionsReplaceLogTerm($Data,$Count){

          $Model1 = $this->__Models(3);
          $Model2 = $this->__Models(2);

          foreach ($Data[$Model1] as $key => $value) {
            foreach ($value[$Model1] as $_key => $_value) {

              if(strlen($_value) < $Count) continue;
              if(!isset($Data[$Model2][$key][$Model2]['Bemerkung ' . $_key])) continue;

              if(strlen($Data[$Model2][$key][$Model2]['Bemerkung ' . $_key]) > 0) $Data[$Model2][$key][$Model2]['Bemerkung ' . $_key] .= '; ' . $_value;
              else $Data[$Model2][$key][$Model2]['Bemerkung ' . $_key] = $_value;

              $Data[$Model1][$key][$Model1][$_key] = '';

            }
          }

          return $Data;
        }

        // „Kommentare“ aus einigen Zellen sollen übertragen werden
        // in die Zelle „Bemerkung“ der einzelnen Messung, BSP:
        protected function __OsiImportFutureFunctionsMoveComments($Data){

          return $Data;
        }

        protected function __OsiImportMpFutureFunctions($Data){

          $Data = $this->__OsiImportFutureFunctionsReplaceLogTerm($Data,5);
          $Data = $this->__OsiImportFutureFunctionsCheckVal($Data,'x');
          $Data = $this->__OsiImportFutureFunctionsDeletePast($Data,'2022');
          $Data = $this->__OsiImportFutureFunctionsMoveComments($Data);

          return $Data;

        }

        protected function __OsiImportFunctionsCheckEmptyFields($Data){

          $Model = $this->__Models(1);
          foreach ($Data[$Model] as $key => $value) {

            $Data[$Model][$key][$Model] = $this->__CheckEmptyFields($Data[$Model][$key][$Model],'Unit','?');
            $Data[$Model][$key][$Model] = $this->__CheckEmptyFields($Data[$Model][$key][$Model],'MP_neu','?');
            $Data[$Model][$key][$Model] = $this->__CheckEmptyFields($Data[$Model][$key][$Model],'Lage','?');
            $Data[$Model][$key][$Model] = $this->__CheckEmptyFields($Data[$Model][$key][$Model],'Wanddicke','Fehler');
            $Data[$Model][$key][$Model] = $this->__CheckEmptyFields($Data[$Model][$key][$Model],'Abmessung','Fehler');
            $Data[$Model][$key][$Model] = $this->__CheckNumericFields($Data[$Model][$key][$Model],'Wanddicke');

          }

          return $Data;
        }

        protected function __OsiImportFunctionsCheckDevValue($Data){

          $Model = $this->__Models(1);

          foreach ($Data[$Model] as $key => $value) {
            $Data[$Model][$key][$Model] = $this->__ReplaceIfNotEmpty($Data[$Model][$key][$Model],'Tote_Enden','X','x');
            $Data[$Model][$key][$Model] = $this->__ChangeThicknesByDimension($Data[$Model][$key][$Model]);
          }

          return $Data;
        }

        protected function __ChangeThicknesByDimension($Data){

          if(empty($Data['Abmessung'])) return $Data;

          $Abmessung = str_replace(" ", "", $Data['Abmessung']);
          $AbmessungArray = explode("/", $Abmessung);

          if(count($AbmessungArray) == 0) return $Data;

          $thickness = array();

          foreach ($AbmessungArray as $key => $value) {
            $ValueArray = explode('x',$value);

            if(count($ValueArray ) != 2) continue;

            $thickness[] = $ValueArray[1];

          }

          if(count($thickness) > 0) $Data['Wanddicke'] = max($thickness);

          return $Data;
        }

        protected function __ReplaceIfNotEmpty($Data,$Field,$Old,$New){

          if(empty($Data[$Field])) return $Data;
          if($Data[$Field] == $New) return $Data;

          if($Data[$Field] == $Old){
            $Data[$Field] = $New;
            return $Data;
          }

          if($Data[$Field] != $Old){
            $Data[$Field] = $New;
            $this->__Messages($Data['id'] . ' falscher Wert ' . $Field);
          }

          return $Data;
        }

        protected function __CheckEmptyFields($Data,$Field,$Placeholder){

          if(!isset($Data[$Field])){
            $this->__Messages($Data['id'] . ' keine Spalte ' . $Field);
            return $Data;
          }

          if(empty($Data[$Field])){
            $this->__Messages($Data['id'] . ' kein Wert ' . $Field);
            $Data[$Field] = $Placeholder;
          }

          return $Data;
        }

        protected function __CheckNumericFields($Data,$Field){

          if(!isset($Data[$Field])){
            $this->__Messages($Data['id'] . ' keine Spalte ' . $Field);
            return $Data;
          }

          $Thicknes = str_replace(",", ".", $Data[$Field]);

          if(is_numeric($Thicknes) === false){
            $this->__Messages($Data['id'] . ' keine Zahl ' . $Field . ' ' . $Data[$Field]);
            $Data[$Field] = 'Fehler';
          }

          return $Data;
        }

        protected function __OsiSaveData($Data){

          $Models = $this->__Models();

          $Rows = $this->__ChangeBackColumNames();

          unset($Models[3]);

          foreach ($Models as $key => $value) {

            if(!isset($Data[$value])) continue;

            foreach ($Data[$value] as $_key => $_value) {

              if(count($Rows[$value]) != count($_value[$value])){

                $this->__Messages($value . ' ' . $_value[$value]['id'] . ' Spaltenanzahl ungleich');
                return;

              }

              $x = 0;
              $Save = array();

              foreach ($_value[$value] as $__key => $__value) {

                $Save[$Rows[$value][$x]] = $__value;
                $x++;

              }

              $Test = $this->{$value}->save($Save);
              if($Test === false){

                $this->__Messages($value . ' ' . $_value[$value]['id'] . ' Fehler beim speichern');

              } else {

              }
            }
          }

          return true;
        }

        protected function __OsiResponseFirstRows(){


          $FirstRow = $this->__FirstRowColumName();
          $FirstRow = $this->__DescriptionRowColumName($FirstRow );
          $FirstRow = $this->__DescriptionMpRowColumName($FirstRow );

          $column = array();
          $row = array();

          foreach ($FirstRow as $key => $value) {

            foreach ($value[1] as $_key => $_value) {
              $row[] = $_value;
            }
          }

          $column[] = $row;

          $row = array();

          foreach ($FirstRow as $key => $value) {

            foreach ($value[2] as $_key => $_value) {
              $row[] = $_value;
            }
          }

          $column[] = $row;

          return $column;
      }

        protected function __OsiResponseData($Okay){

          if($Okay === false) return false;

          $Data = $this->__FindData(2);
          $Models = $this->__Models();

          foreach ($Data as $key => $value) {
            foreach ($value as $_key => $_value) {
              unset($Data[$key][$_key][$key]['id']);
            }
          }

          $column = $this->__OsiResponseFirstRows();

          foreach ($Data[$Models[1]] as $key => $value) {

            $row = array();

            foreach ($Models as $_key => $_value) {
              $row = array_merge($row,$Data[$_value][$key][$_value]);
            }

            $column[] = $row;

          }

          $Test = $this->__OsiResponseFile($column);

          return $Test;
        }

        protected function __OsiResponseFile($column){

          App::uses('Folder', 'Utility');
          App::uses('File', 'Utility');

          $outdir = new Folder(Configure::read('document_folder') .  'out' . DS);
          $olddir = new Folder(Configure::read('document_folder') .  'old' . DS);
          $indir = new Folder(Configure::read('document_folder') .  'in' . DS);
          $files = $indir->find('.*\.csv');

          if(count($files) == 0){

            $this->out(h('Keine Datei gefunden.'));
            return false;

          }

          foreach ($files as $file) {

            $file = new File($indir->pwd() . DS . $file);
            $exportname = $file->name;

            if($file->copy($olddir->path . $file->name)){

              $file->delete();
              $this->out($file->name . h(' in den Archivorder verschoben.'));

            } else {
              return false;
            }

            break;
          }

          $exportfile = new File($outdir->path . $exportname, true, 0644);

          $export = fopen($outdir->path . $exportname,"w");

          foreach ($column as $value) {
            fputcsv($export, $value);
          }

          fclose($export);

          $this->out($file->name . h(' in ') . $outdir->path . h(' gespeichert.'));


          return true;
        }

        protected function _OsiImportFileFromSpool(){

          $spooldir = new Folder(Configure::read('document_folder') . DS . 'spool' . DS);

          $files = $spooldir->find('.*\.csv');

          $indir = new Folder(Configure::read('document_folder') . DS . 'in' . DS);

          if(count($files) == 0){

            $this->out(h('Keine Datei gefunden.'));
            return false;

          }

          foreach ($files as $file) {

            $file = new File($spooldir->pwd() . DS . $file);

            if($file->copy($indir->path . $file->name)){

              $file->delete();
              $this->out($file->name . h(' in den Importorder verschoben.'));

            } else {
              return false;
            }

            break;
          }

          return true;
        }

        public function OsiColumImport1()
        {
            App::uses('Folder', 'Utility');
            App::uses('File', 'Utility');

            $Test = $this->_OsiImportFileFromSpool();

            if($Test === false) return;

            $this->__UpdateTables('delete');

            $dir = new Folder(Configure::read('document_folder') . 'in' . DS);
            $files = $dir->find('.*\.csv');

            foreach ($files as $file) {
                $file = new File($dir->pwd() . DS . $file);
                $Row = 0;

                $this->out(h('Importdatei gefunden.'));
                $this->out(h('Datenimport gestartet...'));

                if (($handle = fopen($file->path, "r")) !== false) {

                    while (($data = fgetcsv($handle, 0, ";")) !== false) {

                        $Test = $this->__OsiInsertRawData($data);

                        if ($Test === false) {
                            $this->__Messages('Fehler beim Einfügen.');
                            return;
                        } else {
                            $Row++;
                        }
                    }
                    fclose($handle);
                }

                $file->close();

                $this->out(h('... Datenimport beendet.'));

                break;
            }

            $this->out($Row . ' ' . h('Datensätze eingefügt'));
        }

        public function OsiTableCorrectur2(){

          $this->__UpdateTables('drop');

        }

        public function OsiColumCorrectur3()
        {

          $this->__CorrectionLage('Reduzier');
          $this->__CorrectionLage('Stück');

          $Data = $this->__FindData(1);

          $Data = $this->__OsiImportFunctions($Data);
          $Data = $this->__OsiImportMpFunctions($Data);
          $Data = $this->__OsiImportMpFutureFunctions($Data);
          $Data = $this->__OsiSaveData($Data);
          $Data = $this->__OsiResponseData($Data);

        }
    }
