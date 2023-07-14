<?php
$ReportPdf  = $this->request->data['Tablenames']['Pdf'];
$setting = $evaluationoutput;
$lang = $locale;

echo $this->Form->create('Reportnumber',array(
  'id' => 'ReportnumberMassFunktion',
  'class' => 'mass_function'
  )
);

$attribut_disabled = false;

if (
  $this->data['Reportnumber']['status'] > 0 ||
  $this->data['Reportnumber']['deactive'] > 0 ||
  $this->data['Reportnumber']['settled'] > 0 ||
  $this->data['Reportnumber']['delete'] > 0
) {
  $attribut_disabled = true;
}

if (isset($reportnumber['Reportnumber']['revision_write']) && $reportnumber['Reportnumber']['revision_write'] == 1) {
  $attribut_disabled = false;
}
if ($attribut_disabled == true) {

  $optionsMassSelect = array(0 => __('Massenaktion wählen'));
  $optionsMassSelect['weldlabel'] = __('Print weld label');

  echo $this->Form->input('MassSelect',
    array(
      'class' => 'mass_funktion_select',
      'options' => $optionsMassSelect,
      'defaut' => 0,
      'label' => false,
      'div' => false
      )
    );

} elseif ($attribut_disabled == false) {

  if (isset($this->request->data['has_result'])) {

    $errors_remove = array();

    if (isset($this->request->data['has_error'])) $errors_remove = array('errors_remove' => __('Fehler entfernen'));

    $optionsMassSelect = array(
      0 => __('Massenaktion wählen'),
      'template' => __('Apply template'),
      'duplicatevalution' => __('Duplizieren'),
      'deleteevalution' => __('Löschen'),
      'result_e' => __('Mit e bewerten'),
      'result_ne' => __('Mit ne bewerten'),
      'result_no' => __('Bewertungen entfernen')
    );

    $optionsMassSelect = array_merge($optionsMassSelect, $errors_remove);

  } else {

    $optionsMassSelect = array(
      0 => __('Massenaktion wählen'),
      'duplicatevalution' => __('Duplizieren'),
      'deleteevalution' => __('Löschen')
    );
  }

  if (isset($settings->$ReportPdf->settings->QM_WELDLABEL->items) && count($settings->$ReportPdf->settings->QM_WELDLABEL->items) > 0) {
    $optionsMassSelect['weldlabel'] = __('Print weld label');
  }

  if ($attribut_disabled == false) {

    echo $this->Form->input('MassSelect',
      array(
        'class' => 'mass_funktion_select',
        'options' => $optionsMassSelect,
        'defaut' => 0,
        'label' => false,
        'div' => false
      )
    );

    echo $this->Form->input('okay',
      array(
        'type' => 'hidden',
        'value' => 0
      )
    );
  }

  if (count($setting) > 0) {

    echo $this->Html->link(__('Reset evaluation sorting', true),
      array_merge(
        array('controller'=>'reportnumbers', 'action'=>'resetevaluation'),
        $this->viewVars['VarsArray']
      ),
      array('class' => 'modal round', 'title'=>__('Reset evaluation sorting', true)
      )
    );

    echo $this->Html->link(__('New Evaluation Area', true),
      array("action" => 'editevalution',
      $this->request->projectvars['projectID'],
      $this->request->projectvars['cascadeID'],
      $this->request->projectvars['orderID'],
      $this->request->projectvars['reportID'],
      $this->request->projectvars['reportnumberID'],
      0,
      1
      ),
      array(
        'class' => 'ajax round',
        'title' => __('New Evaluation Area', true)
      )
    );
  }

  if(Configure::check('TemplateManagement') && Configure::read('TemplateManagement') == true) echo $this->element('templates/select_dropdown_evaluations');

}
?>
