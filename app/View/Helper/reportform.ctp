<?php
if(empty($setting)) return false;
if(empty($data)) return false;
?>
<div class="<?php echo $step;?> formcontainer">

<?php
$fieldset_count = 0;

echo '<fieldset class="fieldset'.trim($setting[0]->model).'" id="fieldset'.trim($setting[0]->model).'_'.$fieldset_count.'">';
echo $this->Form->input(trim($setting[0]->model).'.0.reportnumber_id',array('type' => 'hidden','value' => $data['Reportnumber']['id']));

$x = 0;

foreach ($setting as $_setting) {

  if(Configure::check('FieldsSignForm') && Configure::read('FieldsSignForm') == true) {
    if (isset($_setting->showinsignform) && $_setting->showinsignform == 1) continue;
  }

  $x++;
  $model = trim($_setting->model);
  $discription = null;
  $input = null;

  $revisionlink = $this->element('form/revisionslink',array('data' => $data,'_setting' => $_setting));
  $disabled = false;

  if (isset($data['Reportnumber']['status']) && $data['Reportnumber']['status'] != 0) {

    $disabled = true;

    if (isset($data['Reportnumber']['revision_write']) && $data['Reportnumber']['revision_write'] == 1) {

      $disabled = false;

      if (Configure::check('NotAllowRevision') && $data['Reportnumber']['status'] >= Configure::read('NotAllowRevision')) {

     $disabled = true;

        if(isset($_setting->allowrevision) && trim($_setting->allowrevision) > 0) $disabled = false;
      }
    }
  }

  $attribut_array = array();
  $attribut_array['tabindex'] = 0;

  if($disabled == true) $attribut_array['disabled'] = "disabled";

  // Die Werte aus der Datenbank in das Formularfeld eintragen
  if (isset($data[$model][trim($_setting->key)])) {
      $attribut_array['value'] = $data[$model][trim($_setting->key)];
  }

  // Leerzeichen im Labeltag müssen gegen geschützte Leerzeichen getauscht werden

      if (isset($replaceheaderdata)&&($replaceheaderdata == '1' || $replaceheaderdata == 'true') && isset($_setting->headerfrom) &&  isset($_setting->headerfrom->key)&&isset($_setting->headerfrom->model)) {
        $headmodel = trim($_setting->headerfrom->model);
        $headkey =  trim($_setting->headerfrom->key);

        if(!empty($data[$headmodel][$headkey])) {
          $discription = str_replace(' ', '&nbsp;',$data[$headmodel][$headkey]);
        }else{
          $discription = str_replace(' ', '&nbsp;', $_setting->discription->$lang);
        }
      }else{
        $discription = str_replace(' ', '&nbsp;', $_setting->discription->$lang);
      }



  if ($discription != null) {
      $attribut_array['label'] = $discription;
  }

  if (!empty($_setting->pdf->measure)) {
      $attribut_array['label'] .= ' (' . $_setting->pdf->measure . ')';
  }

  if (isset($_setting->validate->notempty)) {
      $attribut_array['div']['required'] = 'required';
      $attribut_array['label'] .= ' *';
  }
  if (isset($_setting->validate->error)) {
      $attribut_array['class'] = 'error';
  }

  // Marker, ob das Feld im PDF Ausdruck ausgeblendet werden kann
  $hidable = false;
  $hideBox = null;

  if (trim($_setting->pdf->hidable) == '1' || strtolower(trim($_setting->pdf->hidable)) == 'x' || strtolower(trim($_setting->pdf->hidable)) == 'true') {

    $hidable = true;
    $val = 1;

    foreach ($data['HiddenField'] as $field) {
      if ($field['model'] == trim($_setting->model) && $field['field'] == trim($_setting->key)) {
        $val = 0;
        break;
      }
    }

    $hideBox = $this->Form->input('hide-'.trim($_setting->model).'0'.trim($_setting->key),
      array(
        'checked'=>$val,
        'id'=>'HiddenField'.trim($_setting->model).'0'.trim($_setting->key),
        'name'=>'data[HiddenField]['.trim($_setting->model).'][0]['.trim($_setting->key).']',
        'type'=>'checkbox',
        'hiddenField'=>false,
        'div'=>false,
        'label'=>false,
        'class'=>'hide_box',
        'style'=>'position: absolute; top: 1px; right: 1px; display: inline-block;'
      )
    );

    // falls ein spezielles Format für das Inputfeld angegeben ist
    if (trim($_setting->fieldtype) > '0') {

      $attribut_array['type'] = trim($_setting->fieldtype);

      if (trim($_setting->fieldtype) == 'radio') {
      }

      if (trim($_setting->fieldtype) == 'checkbox') {
        if (Configure::check('SkipTabindexOnLinks') && Configure::read('SkipTabindexOnLinks')) $attribut_array['tabindex'] = '-1';
      }
    }
  }

  if ($_setting->fieldset != '' && $_setting->fieldset == 1) {

      $fieldset_count++;
      $fieldset_class = 'fieldset'.trim($setting[0]->model).' ';

      if(isset($_setting->multiselect) && trim($_setting->key) != 'error') $fieldset_class .= 'multiple_field';
      if(isset($_setting->multiselect) && trim($_setting->key) == 'error') $fieldset_class .= 'multiple_field_error';

      echo '</fieldset><fieldset class="' . $fieldset_class . '" id="fieldset'.trim($setting[0]->model).'_'.$fieldset_count.'">';
  }

  if (isset($_setting->legend->$lang) && $_setting->legend->$lang != '') {
      echo '<legend class="headline">'.$_setting->legend->$lang.'</legend>';
  }

  // bei einer speziellen Datenformatierung
  $attribut_array['class'] = null;

  if (trim($_setting->format) > '0') $attribut_array['class'] .= trim($_setting->format) . ' ';
  if (isset($_setting->validate->error)) $attribut_array['class'] .= ' error';

  if (isset($data[$model][trim($_setting->key)]) && $data[$model][trim($_setting->key)] == 1 && trim($_setting->fieldtype) == 'checkbox') {
    $attribut_array['checked'] = 'checked';

    if ($hidable) $attribut_array['between'] = $hideBox;
  }

  $attribut_array['before'] = $revisionlink;

  echo $this->ViewForm->CreateInputForReport($data,$_setting,$setting,$attribut_array,$hidable,$disabled,$x);
  echo $this->ViewForm->CreateDropdownForReport($data,$_setting,$setting,$attribut_array,$hidable,$disabled,$x);
  echo $this->ViewForm->CreateRadioForReport($data,$_setting,$setting,$attribut_array,$hidable,$disabled,$x);
  echo $this->ViewForm->CreateMultiselectForReport($data,$_setting,$setting,$attribut_array,$hidable,$disabled,$x,$hideBox);
  echo $this->ViewForm->CreateMultiErrorselectForReport($data,$_setting,$setting,$attribut_array,$hidable,$disabled,$x,$hideBox);
  echo $this->ViewForm->CreateModulInputForReport($data,$_setting,$setting,$attribut_array,$hidable,$disabled,$x);
  if(trim($_setting->format) == 'date') echo $this->ViewForm->CreateDateInputForReport($data,$_setting,$setting,$attribut_array,$hidable,$disabled,$x);
}
?>

</div>
<script type="text/javascript">
$(document).ready(function(){

$(".date").datetimepicker({ format: "Y-m-d", timepicker:false, lang:"de", scrollInput: false});
$(".datetime").datetimepicker({ lang:"de", format: "Y-m-d H:i", scrollInput: false});

});
</script>
