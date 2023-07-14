<?php
if(count($evaluationoutput) > 0){
	$this->set('replaceHeaderSetting', null);
	if(isset($settings->{'Report'.ucfirst($verfahren).'Pdf'}->settings->use_first_evaluation_as_header)) {
		$this->set('replaceHeaderSetting', strtolower(trim($settings->{'Report'.ucfirst($verfahren).'Pdf'}->settings->use_first_evaluation_as_header)));
	}

	echo $this->ViewData->ShowEvaluationData($welds,$evaluationoutput,$locale,$dropdowns,null,$settings);
}
?>
<script type="text/javascript">
$(document).ready(function(){
	$("#EvaluationArea").css("background-image","none");	
});
</script>
<?php // echo $this->JqueryScripte->LeftMenueHeight($reportnumber); ?>