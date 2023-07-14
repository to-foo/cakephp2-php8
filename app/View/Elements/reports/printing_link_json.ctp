<?php
echo '<div class="hint_box">';
echo $this->Form->create('ReportnumberPrinting',array('class' => 'modalform'));
echo '<h4>' . __('Report printing',true) . '</h4>';

$PrintMessages = $this->ViewData->CollectPrintMessages($reportnumber,$errors);

echo $this->element('reports/additionally_logos');

echo $this->element('reports/collect_print_unevaluated');
echo $this->element('reports/collect_print_messages',array('PrintMessages' => $PrintMessages));

echo '<p>';

echo $this->element('reports/printing_proof_link_json');

if (count($errors) == 0) {
  
  if ($reportnumber['Reportnumber']['status'] == 0 || $reportnumber['Reportnumber']['print'] == 0) {
    $print_description = __('Print orginal report', true);
    $download_description = __('Download orginal report', true);
  }

  if ($reportnumber['Reportnumber']['print'] > 0 && $reportnumber['Reportnumber']['status'] > 0) {
    $print_description = __('Print duplicate of report', true);
    $download_description = __('Download duplicate of report', true);
  }

  echo $this->Html->link($download_description,
    array_merge(
      array('action' => 'pdf'),
        $this->request->projectvars['VarsArray']
      ),
      array(
        'class'=>'round close_after_printing',
        'target' => '_blank',
        'title' => $download_description,
        'disabled'=>(isset($this->request->data['prevent']) && intval($this->request->data['prevent'])==1)
      )
    );
}

echo '</p>';
echo $this->Form->end();
echo '</div>';
?>
