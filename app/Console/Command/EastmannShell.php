<?php

class EastmannShell extends AppShell
{
  public $uses = array(
    'EastmannImport',
    'Cascade',
    'CascadesOrder',
    'CascadegroupsCascade',
    'Order'
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

  protected function _Models($key = false)
  {
    $Models = array(
      'EastmannImport'
    );

    if($key === false) return $Models;

    $element = --$key;

    return $Models[$element];
  }

  protected function _ImportFileFromSpool(){

    $spooldir = new Folder(Configure::read('root_folder') . DS . 'import' . DS . 'spool' . DS);

    $files = $spooldir->find('.*\.csv');

    $indir = new Folder(Configure::read('root_folder') . DS . 'import' . DS . 'in' . DS);

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

  protected function __UpdateTables($mod)
  {

    App::uses('Folder', 'Utility');
    App::uses('File', 'Utility');

    if($mod == 'delete') $this->out(h('Datenbanktabellen werden geleert.'));

    $models = $this->_Models();

    $dir = new Folder(Configure::read('root_folder') . DS . 'import' . DS . 'sql' . DS . $mod . DS);

    $files = $dir->find('.*\.sql');

    foreach ($models as $key =>$file) {
      if (file_exists($dir->path . $file . '.sql')) {
        $Query = file_get_contents($dir->path . $file . '.sql');
        $this->{$file}->query($Query);
      }
    }

    if($mod == 'delete') $this->out(h('Datenbanktabellen geleert.'));

  }

  protected function __InsertRawData($Data)
  {
    $models = $this->_Models();

    $Row = 'COL_';
    $Insert = array();

    foreach ($Data as $key => $value) {
      $Insert[$Row . $key] = h($value);
    }

    foreach ($models as $key => $value) {

      $schema = $this->$value->schema();
      unset($schema['id']);

      $schema = array_keys($schema);
      $schema = array_flip($schema);

      $Insert = array();

      foreach ($schema as $_key => $_value) {

        if(isset($Data[$_value])) $Insert[$_key] = trim($Data[$_value]);
      }

      $this->{$value}->create();
pr($Insert);
      if($this->{$value}->save($Insert)){
      } else {
        $this->out('ERROR ' . $value . ' ' . pr($Insert));
      }
    }
  }

  public function ImportTechicalPlaces(){

    $Datas = $this->EastmannImport->find('list');

    foreach($Datas as $key => $value){

      $Check = $this->_ImportTechicalPlaces($value,$key);

    }
  }

  protected function _ImportTechicalPlaces($id,$key){

    $Model = 'EastmannImport';
    $Datas = $this->EastmannImport->find('first',array('conditions' => array($Model.'.id' => $id)));

    $Platz = trim($Datas[$Model]['technischer_platz']);

    $Platz = preg_replace("/\s\s+/","",$Platz);

    if(empty($Platz)) return;

    $Platz = explode('-',$Platz);

    if(count($Platz) == 0) return;

    $Test = $this->_ImportTechicalPlacesCheckCascadeExists($Platz,$key);

  }

  protected function _ImportTechicalPlacesCheckCascadeExists($data,$id){

    $Model = 'EastmannImport';
    $this->Cascade->recursive = -1;

    foreach($data as $key => $value){

      $conditions = array(
        'conditions' => array(
          'Cascade.topproject_id' => 15,
          'Cascade.level' => $key,
          'Cascade.discription' => $value,
        )
      );

      $Datas = $this->Cascade->find('first',$conditions);

      $first = array_shift($data);

      if(count($Datas) == 0){
        $Test = $this->_ImportTechicalPlacesAdd($first,$id);
      }
    }

    return true;
  }

  protected function _ImportTechicalPlacesAdd($elm,$id){

    $Model = 'EastmannImport';
    $Datas = $this->EastmannImport->find('first',array('conditions' => array($Model.'.id' => $id)));

    pr($Datas);

  }

  public function ColumImport1()
  {
    App::uses('Folder', 'Utility');
    App::uses('File', 'Utility');

    $Test = $this->_ImportFileFromSpool();
//    if($Test === false) return;

    $this->__UpdateTables('delete');

    $dir = new Folder(Configure::read('root_folder') . DS . 'import' . DS . 'in' . DS);
    $files = $dir->find('.*\.csv');

    foreach ($files as $file) {
      $file = new File($dir->pwd() . DS . $file);
      $Row = 0;

      $this->out(h('Importdatei gefunden.'));
      $this->out(h('Datenimport gestartet...'));

      if (($handle = fopen($file->path, "r")) !== false) {

        while (($data = fgetcsv($handle, 0, ";")) !== false) {

          $Test = $this->__InsertRawData($data);

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

  public function ColumCorrectur()
  {
    $models = $this->_Models();

    foreach ($models as $key => $value) {

      $Options = array(
        'fields' => array('id','tech_platz'),
        'conditions' => array('id <' => 4)
      );

      $Datas = $this->{$value}->find('all',$Options);

      foreach ($Datas as $_key => $_value) {

        if(empty($_value[$value]['tech_platz'])) return false;
        if($_value[$value]['tech_platz'] == '??') return false;

        $TechPlace = explode('-',$_value[$value]['tech_platz']);

        $ParentId = $this->__AddCascades($TechPlace,0,0);
        $ParentId = $this->__AddCascades($TechPlace,1,$ParentId);
        $ParentId = $this->__AddCascades($TechPlace,2,$ParentId);
        $ParentId = $this->__AddCascades($TechPlace,3,$ParentId);
        $ParentId = $this->__AddLastCascades($TechPlace,4,$ParentId);

      }
    }
  }

  protected function __InsertLastCascades($value,$Insert,$Model){

    $Insert['Cascade']['discription'] = $value[$Model]['ausruestung'] . ' ' . $value[$Model]['ausruestung_detail'];

    $Options = array();

    $Options['conditions']['Cascade.topproject_id'] = $Insert['Cascade']['topproject_id'];
    $Options['conditions']['Cascade.discription'] = $value[$Model]['ausruestung'] . ' ' . $value[$Model]['ausruestung_detail'];
    $Options['conditions']['Cascade.level'] = $Insert['Cascade']['level'];
    $Options['conditions']['Cascade.parent'] = $Insert['Cascade']['parent'];

    $Cascade = $this->Cascade->find('first',$Options);

    if(count($Cascade) > 0){

      $Insert['Cascade'] = $Cascade ['Cascade'];

       return $Insert;

    }

    $this->Cascade->create();
    $Cascade = $this->Cascade->save($Insert);
    $CascadeId = $this->Cascade->getLastInsertID();

    $Cascade = $this->Cascade->find('first',array('conditions' => array('Cascade.id' => $CascadeId)));

    $Insert['Cascade'] = $Cascade ['Cascade'];

    return $Insert;

  }

  protected function __InsertLastCascadegroupsCascade($value,$Insert,$Model){

    $CascadeGroupId = 18;

    $CascadegroupsCascade = $this->CascadegroupsCascade->find('first',array(
        'conditions' => array(
          'CascadegroupsCascade.cascade_group_id' => $CascadeGroupId,
          'CascadegroupsCascade.cascade_id' => $Insert['Cascade']['id']
        )
      )
    );

    if(count($CascadegroupsCascade) > 0) return $Insert;

    $Insert['CascadegroupsCascade']['cascade_group_id'] = $CascadeGroupId;
    $Insert['CascadegroupsCascade']['cascade_id'] = $Insert['Cascade']['id'];

    $this->CascadegroupsCascade->create();
    $this->CascadegroupsCascade->save($Insert);

    return $Insert;

  }

  protected function __InsertLastOrder($value,$Insert,$Model){

    $Options['conditions']['Order.topproject_id'] = $Insert['Cascade']['topproject_id'];
    $Options['conditions']['Order.cascade_id'] = $Insert['Cascade']['id'];

    $Order = $this->Order->find('first',$Options);

    if(count($Order) > 0){

      $Insert['Order'] = $Order['Order'];

      return $Insert;

    }

    $Insert['Order']['topproject_id'] = $Insert['Cascade']['topproject_id'];
    $Insert['Order']['cascade_id'] = $Insert['Cascade']['id'];
    $Insert['Order']['auftrags_nr'] = $value[$Model]['ausruestung'] . ' ' . $value[$Model]['ausruestung_detail'];

    $Insert = $this->__AddOrderData($Insert,$value[$Model]);

    $this->Order->create();
    $this->Order->save($Insert['Order']);
    $OrderId = $this->Order->getLastInsertID();
    $Order = $this->Order->find('first',array('conditions' => array('Order.id' => $OrderId)));

    $Insert['Order'] = $Order['Order'];

    return $Insert;

  }

  protected function __InsertLastOrdersTestingcomp($value,$Insert,$Model){

    $OrdersTestingcomp = $this->Order->OrdersTestingcomp->find('first',array(
      'conditions' => array(
        'OrdersTestingcomp.testingcomp_id' => 34,
        'OrdersTestingcomp.order_id' => $Insert['Order']['id']
        )
      )
    );

    if(count($OrdersTestingcomp) > 0) return $Insert;

    $Insert['OrdersTestingcomp']['testingcomp_id'] = 34;
    $Insert['OrdersTestingcomp']['order_id'] = $Insert['Order']['id'];

    $this->Order->OrdersTestingcomp->create();
    $this->Order->OrdersTestingcomp->save($Insert);

    return $Insert;

  }

  protected function __InsertLastCascadesOrder($value,$Insert,$Model){

    $CascadesOrder = $this->CascadesOrder->find('first',array(
      'conditions' => array(
        'CascadesOrder.cascade_id' => $Insert['Cascade']['id'],
        'CascadesOrder.order_id' => $Insert['Order']['id']
        )
      )
    );

    if(count($CascadesOrder) > 0) return $Insert;

    $Insert['CascadesOrder']['cascade_id'] = $Insert['Cascade']['id'];
    $Insert['CascadesOrder']['order_id'] = $Insert['Order']['id'];

    $this->CascadesOrder->create();
    $this->CascadesOrder->save($Insert);

    return $Insert;

  }

  protected function __AddLastCascades($Data,$Key,$parent){

    if($Key != 4) return;

    $models = $this->_Models();
    $Model = $models[0];

    $CascadeGroupId = 18;

    $this->Cascade->recursive = -1;


    $TopprojectId = 15;

    $InsertTop = array();

    $InsertTop['Cascade']['topproject_id'] = $TopprojectId;
    $InsertTop['Cascade']['status'] = 1;
    $InsertTop['Cascade']['level'] = $Key;
    $InsertTop['Cascade']['parent'] = $parent;
    $InsertTop['Cascade']['orders'] = 1;
    $InsertTop['Cascade']['child'] = 0;

    $Options = array();

    $Options['conditions']['Cascade.topproject_id'] = $TopprojectId;
    $Options['conditions']['Cascade.discription'] = $Data[3];
    $Options['conditions']['Cascade.level'] = 3;

    $DataString = implode('-',$Data);

    $ImportData = $this->{$Model}->find('all',array(
        'conditions' => array($Model . '.tech_platz' => $DataString),
//        'fields' => array('tech_platz','ausruestung','ausruestung_detail')
      )
    );

    if(count($ImportData) == 0) return;

    foreach ($ImportData as $key => $value) {

      $Insert = array();

      $Insert['Cascade'] = $InsertTop['Cascade'];

      $Insert = $this->__InsertLastCascades($value,$Insert,$Model);
      $Insert = $this->__InsertLastCascadegroupsCascade($value,$Insert,$Model);
      $Insert = $this->__InsertLastOrder($value,$Insert,$Model);
      $Insert = $this->__InsertLastOrdersTestingcomp($value,$Insert,$Model);
      $Insert = $this->__InsertLastCascadesOrder($value,$Insert,$Model);

//pr($Insert);
/*

      $CascadesOrder = $this->CascadesOrder->find('first',array(
        'conditions' => array(
          'CascadesOrder.cascade_id' => $CascadeId,
          'CascadesOrder.order_id' => $OrderId
          )
        )
      );

      if(count($CascadesOrder) == 0){

        $Insert = array();
        $Insert['cascade_id'] = $CascadeId;
        $Insert['order_id'] = $OrderId;

        $this->CascadesOrder->create();
        $this->CascadesOrder->save($Insert);

      }
*/
    }
  }

  protected function __AddCascades($Data,$key,$parent){

    if($key == 5) return;

    $this->Cascade->recursive = -1;

    $Insert['topproject_id'] = 15;
    $Insert['status'] = 1;
    $Insert['discription'] = $Data[$key];
    $Insert['level'] = $key;
    $Insert['parent'] = $parent;
    $Insert['orders'] = 0;
    $Insert['child'] = 0;

    if($key > 0){

      $Options = array();

      $ParentKey = $key;
      $ParentLevel = $Insert['level'];

      $Options['conditions']['Cascade.topproject_id'] = $Insert['topproject_id'];
      $Options['conditions']['Cascade.discription'] = $Data[--$ParentKey];
      $Options['conditions']['Cascade.level'] = --$ParentLevel;

      $Parent = $this->Cascade->find('first',$Options);

      $Parent = $Parent['Cascade']['id'];

    } else {
      $Parent = 0;
    }

    $Options = array();

    $Options['conditions']['Cascade.topproject_id'] = $Insert['topproject_id'];
    $Options['conditions']['Cascade.discription'] = $Insert['discription'];
    $Options['conditions']['Cascade.level'] = $Insert['level'];
    $Options['conditions']['Cascade.parent'] = $Parent;

    $Cascade = $this->Cascade->find('first',$Options);

    if(count($Cascade) == 1){

      return $Cascade['Cascade']['id'];

    } else {

      $this->Cascade->create();
      $Cascade = $this->Cascade->save($Insert);
      $Id = $this->Cascade->getLastInsertID();

      return $Id;

    }
  }

  public function AddCascadeGroups(){

    $Insert['topproject_id'] = 15;
    $CascadeGroupId = 18;

     $this->Cascade->recursive = -1;

    $Cascades = $this->Cascade->find('list',array('conditions' => array('Cascade.topproject_id' => $Insert['topproject_id'])));

    foreach ($Cascades as $key => $value) {

      $Insert = array();
      $Insert['cascade_group_id'] = $CascadeGroupId;
      $Insert['cascade_id'] = $value;

      $Options['conditions']['CascadegroupsCascade.cascade_group_id'] = $CascadeGroupId;
      $Options['conditions']['CascadegroupsCascade.cascade_id'] = $value;

      $CascadegroupsCascade = $this->Cascade->CascadegroupsCascade->find('first',$Options);

      if(count($CascadegroupsCascade) == 1) continue;

      $this->Cascade->CascadegroupsCascade->create();
      $this->Cascade->CascadegroupsCascade->save($Insert);
    }
  }

  protected function __AddOrderData($Insert,$Value){

    $Insert['Order']['class'] = $Value['rohrklasse'];
    $Insert['Order']['location_to'] = $Value['verlauf_nach'];
    $Insert['Order']['location_from'] = $Value['verlauf_von'];
    $Insert['Order']['medium'] = $Value['medium'];

    return $Insert;

  }

  protected function __TechicalPlaceNoWordWrap($Data){

    $Data['tech_platz'] = str_replace(' ', '', $Data['tech_platz']);

    return $Data;
  }
}
