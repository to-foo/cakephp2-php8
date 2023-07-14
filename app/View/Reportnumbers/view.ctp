<div class="quicksearch">
<?php echo $this->element('searching/search_quick_reportnumber',array('action' => 'quickreportsearch','minLength' => 1,'discription' => __('Pr-Nr. (YYYY-NN)')));?>
<?php echo $this->element('barcode_search');?>
</div>
<div class="reportnumbers view detail">
	<h2><?php echo $this->Pdf->ConstructReportName($reportnumber,3) ?></h2>
	<?php echo $this->element('Flash/_messages');?>
	<div class="clear edit">
	<?php echo $this->element('navigation/report_menue',array('ReportMenue' => $ReportMenue,'data' => $reportnumber,'settings' => $settings));?>
	</div>
	<?php
	echo '<div class="pagin_links pagin_links_top">';

	if(count($reportBefore) == 1){
		$desc_before = __('Vorhergehender Prüfbericht') . ' (' . $this->Pdf->ConstructReportNamePdf($reportBefore,2,true) . ')';
		echo $this->Html->Link($desc_before,array('action' => 'edit',$this->request->projectvars['projectID'],$this->request->projectvars['cascadeID'],$this->request->projectvars['orderID'],$this->request->projectvars['reportID'],$reportBefore['Reportnumber']['id']),array('class' => 'icon icon_prev ajax','title' => $desc_before));
	}
	if(count($reportNext) == 1){
		$desc_next = __('Nächster Prüfbericht') . ' (' . $this->Pdf->ConstructReportNamePdf($reportNext,2,true) . ')';
		echo $this->Html->Link($desc_next,array('action' => 'edit',$this->request->projectvars['projectID'],$this->request->projectvars['cascadeID'],$this->request->projectvars['orderID'],$this->request->projectvars['reportID'],$reportNext['Reportnumber']['id']),array('class' => 'icon icon_next ajax','title' => $desc_next));
	}
	echo '<div class="clear"></div>';
	echo '</div>';
	?>
	<div class="">
		<h3><?php echo __('Report Penetration Generallies'); ?></h3>
		<?php echo $this->element('reports/report_view_data',array('table' => 'Generally','setting' => $generaloutput));?>
		<h3><?php echo __('Report Penetration Specifics'); ?></h3>
		<?php echo $this->element('reports/report_view_data',array('table' => 'Specific','setting' => $specificoutput));?>
		<h3><?php echo __('Report Penetration Evaluations'); ?></h3>
        <?php
	if(count($evaluationoutput) > 0){
		$this->set('replaceHeaderSetting', null);
		if(isset($settings->{'Report'.ucfirst($verfahren).'Pdf'}->settings->use_first_evaluation_as_header)) {
			$this->set('replaceHeaderSetting', strtolower(trim($settings->{'Report'.ucfirst($verfahren).'Pdf'}->settings->use_first_evaluation_as_header)));
		}
//		echo $this->ViewData->ViewEvaluation($welds,$evaluationoutput,$locale);

		echo $this->element('reports/report_view_evaluationdata');

	}
	?>
	</div>
	<div class="clear" id="testdiv"></div>
</div>
<?php
$CurrentUrl = $this->Html->url(array_merge(array('controller'=>'reportnumbers','action'=> $this->request->params['action']),$this->request->projectvars['VarsArray']));
echo $this->Form->input('CurrentUrl',array('value' => $CurrentUrl,'type' => 'hidden'));

echo $this->JqueryScripte->LeftMenueHeight();
?>
<script>
$(function() {

	$(".accordion").accordion({
		heightStyle: "content"
		});

	var OutputHeight = 0;
	$("p.output").each(function(){
			if($(this).height() > OutputHeight) {
				OutputHeight = $(this).height();
			}
		});

	var AccordionH3 = 0;
	$(".ui-accordion h3").each(function(){
			if($(this).width() > AccordionH3) {
				AccordionH3 = $(this).width();
			}
		});

	$("p.output").css("height",OutputHeight+"px");
	$(".ui-accordion h3").css("width",AccordionH3+"px");

});
</script>
