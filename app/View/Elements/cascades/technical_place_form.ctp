<?php
if(empty($data)) return false;
?>
<div class="<?php echo $step;?> formcontainer">

  <?php
  $lang = $this->request->lang;
  $fieldset_count = 0;
  $Xml = $data['Xml']['settings']->Technicalplace;

  echo '<fieldset class="fieldset_technicalplace_" id="fieldset_technicalplace_'.$fieldset_count.'">';
//  echo $this->Form->input(trim($setting[0]->model).'.0.id',array('type' => 'hidden','value' => $data[trim($setting[0]->model)]['id']));

  $x = 0;

  foreach ($Xml->children() as $_setting) {

    $x++;
    $model = trim($_setting->model);
    $field = trim($_setting->key);
    $discription = null;
    $disabled = false;
    $input = null;
    $hidable = false;
    $hideBox = null;

    $attribut_array = array();
    $attribut_array['tabindex'] = 0;

    if($disabled == true) $attribut_array['disabled'] = "disabled";

    // Die Werte aus der Datenbank in das Formularfeld eintragen
    if (isset($data[$model][trim($_setting->key)])) {
      $attribut_array['value'] = $data[$model][trim($_setting->key)];
    } else  {
      $data[$model][trim($_setting->key)] = '';
      $attribut_array['value'] = $data[$model][trim($_setting->key)];
    }

    // Leerzeichen im Labeltag müssen gegen geschützte Leerzeichen getauscht werden
    $discription = str_replace(' ', '&nbsp;', $_setting->discription->$lang);

    if ($discription != null) $attribut_array['label'] = $discription;
    else $attribut_array['label'] = '';

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

    if ($_setting->fieldset != '' && $_setting->fieldset == 1) {

      $fieldset_count++;
      $fieldset_class = 'fieldset_technicalplace_';

      if(isset($_setting->multiselect) && trim($_setting->key) != 'error') $fieldset_class .= 'multiple_field';
      if(isset($_setting->multiselect) && trim($_setting->key) == 'error') $fieldset_class .= 'multiple_field_error';

      echo '</fieldset><fieldset class="' . $fieldset_class . '" id="fieldset_technicalplace_'.$fieldset_count.'">';
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

    echo $this->ViewForm->CreateInputForReport($data,$_setting,$Xml,$attribut_array,$hidable,$disabled,$x);
    echo $this->ViewForm->CreateDropdownForReport($data,$_setting,$Xml,$attribut_array,$hidable,$disabled,$x);
    echo $this->ViewForm->CreateRadioForReport($data,$_setting,$Xml,$attribut_array,$hidable,$disabled,$x);
    echo $this->ViewForm->CreateMultiselectForReport($data,$_setting,$Xml,$attribut_array,$hidable,$disabled,$x,$hideBox);
    echo $this->ViewForm->CreateMultiErrorselectForReport($data,$_setting,$Xml,$attribut_array,$hidable,$disabled,$x,$hideBox);
    echo $this->ViewForm->CreateModulInputForReport($data,$_setting,$Xml,$attribut_array,$hidable,$disabled,$x);
    if(trim($_setting->format) == 'date') echo $this->ViewForm->CreateDateInputForReport($data,$_setting,$Xml,$attribut_array,$hidable,$disabled,$x);

}
?>

</div>
<script type="text/javascript">
$(document).ready(function(){

  $(".date").datetimepicker({ format: "Y-m-d", timepicker:false, lang:"de", scrollInput: false});
  $(".datetime").datetimepicker({ lang:"de", format: "Y-m-d H:i", scrollInput: false});

});
</script>
