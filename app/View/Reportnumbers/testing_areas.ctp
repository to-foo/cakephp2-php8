<div class="modalarea detail">
<?php
if(isset($shutdown) && $shutdown  == true){
	echo $this->element('js/reload_container',array('FormName' => $FormName));
	echo $this->element('js/ajax_stop_loader');
	echo $this->element('js/close_modal_auto');
	echo '</div>';
	return;
	}
?>

<h2><?php echo __('Testing areas') . ' ' . $this->Pdf->ConstructReportName($reportnumber)?></h2>
<?php
	if(count($evaluationoutput) > 0){
		$this->set('replaceHeaderSetting', null);
		if(isset($settings->{'Report'.ucfirst($verfahren).'Pdf'}->settings->use_first_evaluation_as_header)) {
			$this->set('replaceHeaderSetting', strtolower(trim($settings->{'Report'.ucfirst($verfahren).'Pdf'}->settings->use_first_evaluation_as_header)));
		}
		echo $this->element('reports/report_view_evaluationdata');
		//echo $this->element('data/evaluation_data');

	}
?>
</div>
<?php
if(isset($saveOK) && $saveOK  == 1){
	echo $this->JqueryScripte->RefreshAfterDialog($reportnumberID,$evalutionID,$FormName);
	echo $this->JqueryScripte->DialogClose(1.5);
}

echo $this->element('js/scroll_modal_top');
?>
