<table cellpadding = "0" cellspacing = "0">
<?php

$AllCounts = count($reportnumbers);
$ActiveCounts = $AllCounts;

$MassSelectetIDs = explode(',',$this->request->data['Reportnumber']['MassSelectetIDs']);
$MassSelectetIDs = array_flip($MassSelectetIDs);

foreach ($reportnumbers as $key => $value) {

  $Class = '';

  if(isset($value['ValidationErrors']) && count($value['ValidationErrors']) > 0) $Class = 'deactive';
  if(isset($value['ClosingErrors']) && $value['ClosingErrors'] == 1) $Class = 'deactive';
  if(isset($value['SignErrors'])) $Class = 'deactive';

  if($Class == 'deactive'){
    --$ActiveCounts;
    unset($MassSelectetIDs[$value['Reportnumber']['id']]);
  }

  echo '<tr class="' . $Class . '">';
  echo '<td>' . $value['Testingmethod']['verfahren'] . '</td>';
  echo '<td>' . $this->Pdf->ConstructReportName($value) . '</td>';
  echo '<td>';
  if(isset($value['ValidationErrors']) && count($value['ValidationErrors']) > 0) echo __('Es wurden nicht alle Pflichtfelder ausgefüllt, Bericht kann nicht geschlossen werden.',true);
  if(isset($value['ClosingErrors']) && $value['ClosingErrors'] == 1) echo ' ' . __('Dieser Bericht ist bereits geschlossen.',true);
  if(isset($value['SignErrors'])) echo ' ' . $value['SignErrors'];
  echo '</td>';
  echo '</tr>';
}

$MassSelectetIDs = array_flip($MassSelectetIDs);
$this->request->data['Reportnumber']['MassSelectetIDs'] = implode(',',$MassSelectetIDs);

?>
</table>
<?php if($ActiveCounts == 0) return;?>
<?php echo $this->Form->create('Reportnumber', array('class' => 'dialogform')); ?>
<?php echo $this->Form->input('action',array('type' => 'hidden','value' => 1));?>
<?php echo $this->Form->input('MassSelectetIDs',array('type' => 'hidden'));?>
<?php echo $this->Form->input('MassSelect',array('type' => 'hidden'));?>
<?php echo $this->Form->end(__('Submit')); ?>
