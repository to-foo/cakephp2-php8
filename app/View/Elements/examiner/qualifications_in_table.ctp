<?php
if(Configure::check('ExaminerManagerTableQualification') == false) return;
if(Configure::read('ExaminerManagerTableQualification') == false) return;
?>

<td>
<span class="discription_mobil">
<?php echo __('Qualifications'); ?>:
</span>
<span class="summary_span">
<?php
//pr($summary[$_examiner['Examiner']['id']]);

if(isset($summary[$_examiner['Examiner']['id']]['summary']['qualifications'])){

	$thissummary = array();

	foreach($summary[$_examiner['Examiner']['id']]['summary']['qualifications'] as $_key => $_qualifications){

		$Quali = explode('_',$_qualifications['class']);

		if($_qualifications['active'] == 0) continue;

		foreach($summary[$_examiner['Examiner']['id']]['summary']['summary'] as $__key => $_summary){
			if(count($_summary) > 0){
				$thissummary[$_qualifications['certificate_id']] = $this->Quality->CertificatSummarySingle($summary[$_examiner['Examiner']['id']],$_qualifications['certificate_id'],$_qualifications['certificate_data_id']);
			}
		}
		
		if(isset($thissummary[$_qualifications['certificate_id']]) && $thissummary[$_qualifications['certificate_id']] != false){
			echo '<div class="container_summary_single container_summary_single_'.$_qualifications['certificate_id'].'">';
			echo $thissummary[$_qualifications['certificate_id']];
			echo '</div>';
		}
		else {
			echo '<div class="container_summary_single container_summary_single_'.$_qualifications['certificate_id'].'">';
			if(isset($_qualifications['qualification'])) echo $_qualifications['qualification'];
			else echo __('Eine Zertifizierung ist für diesen Prüfer in diesem Verfahren nicht vorgesehen.',true);
			echo '</div>';
		}
		echo '<a href="examiners/qualifications/'.
		$_qualifications['termlink'].'" title="'.
		$_qualifications['certificat'].'" rel="'.
		$_qualifications['certificate_id'].'" class="summary_tooltip ajax icon '.
		$_qualifications['class'].' ';
		if(isset($_qualifications['status_class'])) echo $_qualifications['status_class'];
//				$Quali[3] .
		echo '">'. ucfirst($Quali[1]) . ' ' . ucfirst($Quali[2]) . ' (' . ucfirst($Quali[0]).')'.
		'</a>';
	}
}
?>
</td>
