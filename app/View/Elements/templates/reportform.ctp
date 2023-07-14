<?php
if(empty($setting)) return false;
if(empty($data)) return false;
?>
<div class="<?php echo $step;?> formcontainer">

<?php
$fieldset_count = 0;

echo '<fieldset class="flex fieldset'.trim($setting[0]->model).'" id="fieldset'.trim($setting[0]->model).'_'.$fieldset_count.'">';

$x = 0;

foreach ($setting as $_key => $_setting) {

  if(trim($_setting->key) == 'id') continue;
  if(trim($_setting->key) == 'reportnumber_id') continue;
  if(trim($_setting->key) == 'created') continue;
  if(trim($_setting->key) == 'modified') continue;

  $model = trim($_setting->model);
  $field = trim($_setting->key);

  $attention_class = null;

//  if(isset($data['Attention'][$model][$field])) $attention_class = 'attention_field';

  if(!isset($data[$model][$field])) continue;

  $x++;

  $discription = null;
  $input = null;

  $disabled = false;

  $attribut_array = array();
  $attribut_array['tabindex'] = 0;

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

  if ($_setting->fieldset != '' && $_setting->fieldset == 1) {

      $fieldset_count++;
      $fieldset_class = 'fieldset'.trim($setting[0]->model).' ';

      if(isset($_setting->multiselect) && trim($_setting->key) != 'error') $fieldset_class .= 'multiple_field';
      if(isset($_setting->multiselect) && trim($_setting->key) == 'error') $fieldset_class .= 'multiple_field_error';

      echo '</fieldset><fieldset class="flex ' . $fieldset_class . '" id="fieldset'.trim($setting[0]->model).'_'.$fieldset_count.'">';
  }

  if (isset($_setting->legend->$lang) && $_setting->legend->$lang != '') {
      echo '<legend class="headline">'.$_setting->legend->$lang.'</legend>';
  }

  // bei einer speziellen Datenformatierung
  $attribut_array['class'] = null;
  $attribut_array['before'] = null;

  if(trim($_setting->format) > '0') $attribut_array['class'] .= trim($_setting->format) . ' ';
  if(isset($_setting->validate->error)) $attribut_array['class'] .= ' error';

  $attribut_array['attention'] = $attention_class;

  if(isset($data[$model][trim($_setting->key)]) && $data[$model][trim($_setting->key)] == 1 && trim($_setting->fieldtype) == 'checkbox') {
    $attribut_array['checked'] = 'checked';
  }

  echo $this->ViewForm->CreateInputForReport($data,$_setting,$setting,$attribut_array,$hidable,$disabled,$x);
  echo $this->ViewForm->CreateDropdownForReport($data,$_setting,$setting,$attribut_array,$hidable,$disabled,$x);
  echo $this->ViewForm->CreateRadioForReportTemplate($data,$_setting,$setting,$attribut_array,$hidable,$disabled,$x);
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
