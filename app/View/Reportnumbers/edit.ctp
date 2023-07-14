<div class="quicksearch">
    <?php	echo $this->element('navigation/change_modul_ndt');?>
    <?php echo $this->element('searching/search_quick_reportnumber',array('action' => 'quickreportsearch','minLength' => 1,'discription' => __('Pr-Nr. (YYYY-NN)')));?>
    <?php echo $this->element('barcode_search');?>
</div>
<div class="reportnumbers detail">

    <?php if(isset($writeprotection) && $writeprotection) echo $this->Html->tag('p', __('Report is writeprotected - changes will not be saved.'), array('class'=>'error')); ?>
    <h2><?php echo $this->Pdf->ConstructReportName($reportnumber,3) ?></h2>
    <?php echo $this->element('Flash/_messages');?>
    <div class="clear edit">
        <?php echo $this->element('navigation/report_menue',array('ReportMenue' => $ReportMenue,'data' => $reportnumber,'settings' => $settings));?>
        <div id="refresh_revision_container"></div>
        <?php if(Configure::check('TestinginstructionManager') == true && Configure::read('TestinginstructionManager') == true)  echo $this->element('testinstruction/testinginstruction');?>
    </div>
    <?php
$reportname_short = $this->Pdf->ConstructReportNamePdf($reportnumber,2,true);
if(isset($editmenue)) echo $this->Navigation->EditMenue($editmenue);

echo $this->element('webservice/search_quick_field',array(
	'controller' =>'soapservices',
	'action' => 'abacusfindodre',
	'minLength' => 4,
	'discription' => __('Auftrags-Nr. für Import eingeben'),
	'placeholder' => __('Auftrags-Nr. (ABACUS)'),
	)
);

echo $this->Form->create('Reportnumber', array('class' => 'editreport'));
echo '<div class="pagin_links pagin_links_top">';
$desc_top = __('Zurück zur Übersicht') . ' ' . $this->request->data['Report']['name'];
echo $this->Html->Link($desc_top,array('action' => 'show',$this->request->projectvars['projectID'],$this->request->projectvars['cascadeID'],$this->request->projectvars['orderID'],$this->request->projectvars['reportID']),array('class' => 'icon icon_top ajax','title' => $desc_top));

if(count($reportBefore) == 1){
	$desc_before = __('Vorhergehender Prüfbericht') . ' (' . $testingmethods[$reportBefore['Reportnumber']['testingmethod_id']] . '/' . $reportBefore['Reportnumber']['number'] . ')';
	echo $this->Html->Link($desc_before,array('action' => 'edit',$this->request->projectvars['projectID'],$this->request->projectvars['cascadeID'],$this->request->projectvars['orderID'],$this->request->projectvars['reportID'],$reportBefore['Reportnumber']['id']),array('class' => 'icon icon_prev ajax','title' => $desc_before));
}
if(count($reportNext) == 1){
	$desc_next = __('Nächster Prüfbericht') . ' (' . $this->Pdf->ConstructReportNamePdf($reportNext,2,true) . ')';
	echo $this->Html->Link($desc_next,array('action' => 'edit',$this->request->projectvars['projectID'],$this->request->projectvars['cascadeID'],$this->request->projectvars['orderID'],$this->request->projectvars['reportID'],$reportNext['Reportnumber']['id']),array('class' => 'icon icon_next ajax','title' => $desc_next));
}
echo '<div class="clear"></div>';
echo '</div>';

echo $this->Form->input('id');
echo $this->Form->input('rel_menue',array('value' => isset($rel_menue) ? $rel_menue : null,'type' => 'hidden'));
echo $this->element('form/reportform',array('data' => $reportnumber,'setting' => $generaloutput,'lang' => $locale,'step' => 'General'));
echo $this->element('form/reportform',array('data' => $reportnumber,'setting' => $specificoutput,'lang' => $locale,'step' => 'Specify'));
//echo $this->ViewData->EditGeneralyData($reportnumber,$generaloutput,$locale,'General');
//echo $this->ViewData->EditGeneralyData($reportnumber,$specificoutput,$locale,'Specify');
echo $this->Additions->Show($settings);

echo '<div class="buttons">';
echo $this->Form->end();
echo '</div>';
?>
    <div class="clear"></div>
    <div class="TestingArea" id="EvaluationArea"></div>

</div>
<div class="clear" id="testdiv"></div>
<div class="reportnumbers detail inhalt">
</div>
<?php
$EvaluationUrl = $this->Html->url(array_merge(array('controller'=>'reportnumbers','action'=>'evaluation'),$this->request->projectvars['VarsArray']));
$SessionUrl = $this->Html->url(array('controller'=>'reportnumbers','action'=>'editsession'));

echo $this->Form->input('CurrentEvaluationUrl',array('value' => $EvaluationUrl,'type' => 'hidden'));
echo $this->Form->input('SessionUrl',array('value' => $SessionUrl,'type' => 'hidden'));

$CurrentUrl = $this->Html->url(array_merge(array('controller'=>'reportnumbers','action'=> $this->request->params['action']),$this->request->projectvars['VarsArray']));
echo $this->Form->input('CurrentUrl',array('value' => $CurrentUrl,'type' => 'hidden'));

$DependenciesUrl = $this->Html->url(array_merge(array('controller'=>'dependencies','action'=> 'get'),$this->request->projectvars['VarsArray']));
echo $this->Form->input('DependenciesUrl',array('value' => $DependenciesUrl,'type' => 'hidden'));

if (Configure::read('RefreshReport') == true && $reportnumber['Reportnumber']['status'] == 0) echo $this->element('refresh_report');
// Test für Git
echo $this->element('js/ajax_breadcrumb_link');
echo $this->element('js/ajax_link');
echo $this->element('js/ajax_modal_link');
echo $this->element('js/revisions_link');
echo $this->element('js/form_button_set');
echo $this->element('js/form_multiple_fields');
echo $this->element('js/custom_dropdown');
echo $this->element('js/edit_report_tab_menue');
echo $this->element('js/edit_report_specialcharacter');
echo $this->element('modul_child_data');
if(isset($reportnumber['Testingmethod']['value'])) echo $this->element('fast_save_report_js', array('testingmethod' => $reportnumber['Testingmethod']['value']));
else echo $this->element('fast_save_report_js');
echo $this->element('js/dropdown_depentency');
echo $this->element('examiner/js/stamp_id');

//if(isset($afterEDIT)) echo $afterEDIT;
?>