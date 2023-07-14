<?php
if(Configure::check('ExaminerManagerTableEyecheck') == false) return;
if(Configure::read('ExaminerManagerTableEyecheck') == false) return;
?>

<td>
<span class="discription_mobil">
<?php echo __('Eye checks'); ?>:
</span>
		<span class="summary_span">
<?php
if(isset($summary[$_examiner['Examiner']['id']]['eyecheck']['qualifications']) && count($summary[$_examiner['Examiner']['id']]['eyecheck']['qualifications']) > 0){

	$thissummary = array();

	foreach($summary[$_examiner['Examiner']['id']]['eyecheck']['qualifications'] as $_key => $_eyecheck){
		foreach($summary[$_examiner['Examiner']['id']]['eyecheck']['summary'] as $__key => $__summary){
			if(count($__summary) > 0){
				$thisdate = $this->Quality->EyecheckDateSingle($summary[$_examiner['Examiner']['id']],$_eyecheck['certificate_id'],$_eyecheck['certificate_data_id']);
				$thissummary[$_eyecheck['certificate_id']] = $this->Quality->EyecheckSummarySingle($summary[$_examiner['Examiner']['id']],$_eyecheck['certificate_id'],$_eyecheck['certificate_data_id']);
			}
		}
		if(isset($thissummary[$_eyecheck['certificate_id']]) && $thissummary[$_eyecheck['certificate_id']] != false){
			echo '<div class="container_eyechecksummary_single container_summaryeyecheck_single_'.$_eyecheck['certificate_id'].'">';
			echo $thissummary[$_eyecheck['certificate_id']];
			echo '</div>';
		}
		else {
			echo '<div class="container_eyechecksummary_single container_summaryeyecheck_single_'.$_eyecheck['certificate_id'].'">';
			echo $_eyecheck['certificat'];
			echo '</div>';
		}
	}

	if(isset($_eyecheck['termlink']))
		echo '<a href="examiners/eyechecks/'.$_eyecheck['termlink'].'" title="'.$_eyecheck['certificat'].'" rel="'.$_eyecheck['certificate_id'].'" class="summaryeyecheck_tooltip ajax icon '.$_eyecheck['class'].'">'.$thisdate.'</a>';

}
?></span>
</td>
