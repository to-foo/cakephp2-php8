<?php
if(isset($evaluationoutput) && count($evaluationoutput) > 0){
	$this->set('replaceHeaderSetting', null);
	if(isset($settings->{'Report'.ucfirst($verfahren).'Pdf'}->settings->use_first_evaluation_as_header)) {
		$this->set('replaceHeaderSetting', strtolower(trim($settings->{'Report'.ucfirst($verfahren).'Pdf'}->settings->use_first_evaluation_as_header)));
	}
	if(isset($settings->{'Report'.ucfirst($verfahren).'Pdf'}->settings->use_data_for_header)) {
		$this->set('replaceheaderdata', strtolower(trim($settings->{'Report'.ucfirst($verfahren).'Pdf'}->settings->use_data_for_header)));
	}

	echo $this->element('data/evaluation_data');

}

echo $this->element('templates/js/select_dropdown_evaluation',array('id' => 0));
echo $this->element('js/json_request_animation');
echo $this->element('js/show_pdf_link');
echo $this->element('js/ajax_link');
echo $this->element('js/ajax_modal_link');
?>
