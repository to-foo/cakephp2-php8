<div class="modalarea detail">
<h2><?php echo __('Testing areas') . ' ' . $this->Pdf->ConstructReportName($reportnumber)?></h2>
<?php
	if(count($evaluationoutput) > 0){
		$this->set('replaceHeaderSetting', null);
		if(isset($settings->{'Report'.ucfirst($verfahren).'Pdf'}->settings->use_first_evaluation_as_header)) {
			$this->set('replaceHeaderSetting', strtolower(trim($settings->{'Report'.ucfirst($verfahren).'Pdf'}->settings->use_first_evaluation_as_header)));
		}
		echo $this->ViewData->ViewEvaluationData($welds,$settings->$ReportEvaluation,$locale);
	}
?>
</div>
<?php 
echo $this->element('js/ajax_stop_loader');
echo $this->element('js/searchresult_link_close');
echo $this->element('js/searchresult_link',array('url' => $result_url));
echo $this->element('js/ajax_link');
echo $this->element('js/close_modal');
echo $this->element('js/minimize_modal');
echo $this->element('js/maximize_modal');
?> 
