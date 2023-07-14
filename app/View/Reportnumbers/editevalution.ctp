<div class="quicksearch">
<?php echo $this->element('searching/search_quick_reportnumber', array('action' => 'quickreportsearch','minLength' => 1,'discription' => __('Pr-Nr. (YYYY-NN)')));?>
<?php echo $this->element('barcode_search');?>
</div>
<div class="reportnumbers detail">
<h2><?php echo $this->Pdf->ConstructReportName($reportnumber, 3) ?></h2>
<div class="clear edit">
<?php echo $this->element('navigation/report_menue',array('ReportMenue' => $ReportMenue,'data' => $reportnumber,'settings' => $settings));?>
<?php if (Configure::check('TestinginstructionManager') == true && Configure::read('TestinginstructionManager') == true) echo $this->element('testinstruction/testinginstruction');?>
</div>
<?php

if(isset($settings->{'Report'.ucfirst($verfahren).'Pdf'}->settings->use_data_for_header)) {
  $this->set('replaceheaderdata', strtolower(trim($settings->{'Report'.ucfirst($verfahren).'Pdf'}->settings->use_data_for_header)));
}

echo $this->element('reports/edit_menue');

echo $this->Form->create('Reportnumber', array('class' => 'editreport'));
echo $this->element('menue/editevaluation_menue');
echo $this->Form->input('id');
echo $this->element('form/reportform',array('data' => $reportnumber,'setting' => $evaluationoutput,'lang' => $locale,'step' => 'Evaluation'));

echo '<div class="buttons">';
echo $this->Form->end();
echo '</div>';
?>
<div id="en1435" style="display:none"></div>
</div>
<div class="clear" id="testdiv"></div>



<div class="clear" id="testdiv"></div>
<?php
$EvaluationUrl = $this->Html->url(array_merge(array('controller'=>'reportnumbers','action'=>'evaluation'), $this->request->projectvars['VarsArray']));
$CurrentUrl = $this->Html->url(array_merge(array('controller'=>'reportnumbers','action'=>'edit'), $this->request->projectvars['VarsArray']));
$SessionUrl = $this->Html->url(array('controller'=>'reportnumbers','action'=>'editsession'));

echo $this->Form->input('CurrentUrl',array('value' => $CurrentUrl,'type' => 'hidden'));
echo $this->Form->input('SessionUrl',array('value' => $SessionUrl,'type' => 'hidden'));
echo $this->Form->input('CurrentEvaluationUrl',array('value' => $EvaluationUrl,'type' => 'hidden'));
echo $this->Form->input('CurrentEditEvaluationUrl',array('value' => $this->request->here,'type' => 'hidden'));
echo $this->Form->input('CurrentEditEvaluationId',array('value' => $this->request->projectvars['VarsArray'][5],'type' => 'hidden'));
echo $this->Form->input('CurrentModel',array('value' => $this->request->data['CurrentTable'],'type' => 'hidden'));

if (Configure::read('RefreshReport') == true && $reportnumber['Reportnumber']['status'] == 0) echo $this->element('refresh_report');
echo $this->element('js/multi_errorselect_report');
echo $this->element('data/evaluation_dublicate_evaluaten');
echo $this->element('js/editevaluation_report_tab_menue');
echo $this->element('js/ajax_breadcrumb_link');
echo $this->element('js/ajax_link');
echo $this->element('js/ajax_modal_link');
echo $this->element('js/revisions_link');
echo $this->element('js/form_button_set');
echo $this->element('js/form_multiple_fields');
echo $this->element('js/form_multiple_error_field');
//echo $this->element('js/custom_dropdown');
echo $this->element('js/dropdown_depentency');
echo $this->element('js/edit_report_specialcharacter');
echo $this->element('js/json_request_animation');
echo $this->element('modul_child_data');
echo $this->element('js/show_pdf_link');
echo $this->element('templates/js/select_dropdown_evaluation_edit');

if (isset($afterEDIT)) echo $afterEDIT;
?>
