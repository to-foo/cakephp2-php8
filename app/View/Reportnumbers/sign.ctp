<div class="quicksearch">
<?php echo $this->element('searching/search_quick_reportnumber', array('action' => 'quickreportsearch','minLength' => 1,'discription' => __('Pr-Nr. (YYYY-NN)')));?>
<?php echo $this->element('barcode_search');?>
</div>
<div class="reportnumbers detail">
<?php echo $this->element('Flash/_messages');?>
<?php
if (isset($writeprotection) && $writeprotection) {
    echo '<div class="error">';
    echo $this->Html->tag('p', __('Report is writeprotected - changes will not be saved.'));
    echo '</div">';
}
?>
<h2><?php echo $this->Pdf->ConstructReportName($reportnumber, 3) ?></h2>
<div class="clear edit">
<?php echo $this->element('navigation/report_menue',array('ReportMenue' => $ReportMenue,'data' => $reportnumber,'settings' => $settings));?>
<div id="refresh_revision_container"></div>
</div>
<?php

if (isset($errors) && count($errors) >  0) {
    echo '<div class="hint">';
    echo $this->Html->tag('p', __('You can not sign the report until all required fields have been completed.'));
    echo '</div">';
}

$attribut_disabled = false;
if ($reportnumber['Reportnumber']['status'] > 0) {
    $attribut_disabled = true;
}
if (isset($reportnumber['Reportnumber']['revision_write']) && $reportnumber['Reportnumber']['revision_write'] == 1) {
    $attribut_disabled = false;
}

$x = 0;

foreach ($signature as $_key => $_signature) {

    if ($x == Configure::read('SignatoryCountofSigns')) {
        break;
    }

    if (isset($errors) && count($errors) >  0) {
        break;
    }

    echo '<div class="signs">';
    echo '<h3>' . $_signature['Discription'] . '</h3>';

    if (isset($error_array[$_key])) {
        echo '<div class="error_occurred">';
        echo '<div class="errormessage">';
        echo '<p>';
        echo $error_array[$_key];
        echo '</p>';
        echo '</div>';
        echo '</div>';
        if ($attribut_disabled === false) {
            echo $this->html->link(
                __('Create signature for', true) .  ' ' . $_signature['Discription'],
                array_merge(array('action' => $_signature['action']), $this->request->projectvars['VarsArray']),
                array(
                                'class' => 'ajax round',
                                'disabled' => isset($writeprotection) && $writeprotection
                                )
            );
        }

        echo '</div>';
        continue;
    }

    if (isset($_signature['Data'])) {
        echo '<img src="data:image/png;base64,' . $_signature['Data'] . '" alt="' . $_signature['Discription'] . '">';
    } else {
        if ($attribut_disabled === false) {
            echo $this->html->link(
                __('Create signature for', true) . ' ' . $_signature['Discription'],
                array_merge(array('action' => $_signature['Action']), $this->request->projectvars['VarsArray']),
                array(
                                'class' => 'ajax round',
                                'disabled' => isset($writeprotection) && $writeprotection
                                )
            );
        } else {
            echo $this->html->link(
                __('Create signature for', true) . ' ' . $_signature['Discription'],
                array_merge(array('action' => $_signature['Action']), $this->request->projectvars['VarsArray']),
                array(
                                'class' => 'ajax round',
                                'disabled' => isset($writeprotection) && $writeprotection
                                )
            );
        }
    }


    if (isset($_signature['Info']) && isset($_signature['User']) && isset($_signature['Testingcomp'])) {
        echo '<div class="info">';
        echo __('Created') . ': ' . $_signature['Sign']['created'];
        echo '<br>';
        echo __('Logged user account') . ': ' . $_signature['User']['name'];
        echo '<br>';
        echo $_signature['Testingcomp']['name'];
        echo '<br>';
        echo '</div>';
    }
    echo '</div>';
    $x++;
}

echo '<div class="clear">';

foreach($signature as $_key => $_signature){
if(isset($errors) && count($errors) >  0) break;
if(isset($_signature['Data'])){
  $show_del = true;
  break;
}
}


if(isset($show_del) && $show_del == true && $reportnumber['Reportnumber']['print'] <= 1){
  echo '<div class="hint"><p>';
  echo $this->html->link(
        __('Remove this signatures'),
            array_merge(array('action' => 'removeSign'),$this->request->projectvars['VarsArray']),
            array(
              'class' => 'modal round',
              'disabled' => isset($writeprotection) && $writeprotection
              )
          );
  echo '</p></div>';
}

echo '</div>';


    if (isset($errors)) {
        $this->ViewData->showValidationErrors($errors);
    }

?>

<?php
$CurrentUrl = $this->Html->url(array_merge(array('controller'=>'reportnumbers','action'=> $this->request->params['action']),$this->request->projectvars['VarsArray']));
echo $this->Form->input('CurrentUrl',array('value' => $CurrentUrl,'type' => 'hidden'));

if (Configure::read('RefreshReport') == true && $reportnumber['Reportnumber']['status'] == 0) {
    echo $this->element('refresh_report');
}
if (isset($reportnumber['Reportnumber']['revision_write']) && $reportnumber['Reportnumber']['revision_write'] == 1) {
    echo $this->element('refresh_revision');
}
?>

<?php
echo $this->element('js/ajax_breadcrumb_link');
echo $this->element('js/ajax_link');
echo $this->element('js/ajax_modal_link');
echo $this->element('js/testreport_erros_skip');
?>
