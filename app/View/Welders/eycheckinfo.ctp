<div class="message_wrapper"><?php echo $this->Session->flash(); ?></div>
<?php
$this->request->projectvars['VarsArray'][15] = $eyecheck_data['Examiner']['id'];
$this->request->projectvars['VarsArray'][16] = $eyecheck_data['Eyecheck']['id'];
$this->request->projectvars['VarsArray'][17] = $eyecheck_data['EyecheckData']['id'];

echo '<div class="hint certification ' .  $eyecheck_data['EyecheckData']['valid_class'] . '">';
if($eyecheck_data['EyecheckData']['certified'] == 1){
	echo '<p>';
	echo __('This certificate is still valid for',true) . ': ';
	echo $eyecheck_data['EyecheckData']['time_to_next_certification']; 
	echo '</p><p>';
	echo __('To recertificate they are reminded in',true) . ': ';
	echo $eyecheck_data['EyecheckData']['time_to_next_horizon'] . ' '; 
//	echo $certificate_data['Certificate']['horizon'] . ' ' . __('months',true) . ' ' . __('before',true) . ')'; 
	echo '</p>';
}
if($eyecheck_data['EyecheckData']['certified'] == 2){
	echo '<p>';
	echo __('This certificate has the status',true) . ' "' . __('not certificated',true) . '"';
	echo '</p>';
}
if($eyecheck_data['EyecheckData']['certified'] == 0){
	echo '<p>';
	echo __('This certificate has no status',true);
	echo '</p>';
}
echo '<p>';

if(!empty($eyecheck_data['EyecheckData']['certified_file'])){
	echo $this->Html->link(__('Show certificate',true), array_merge(array('action' => 'geteyecheckfile'), $this->request->projectvars['VarsArray']), array('class' => 'round','target' => '_blank'));
}

echo $this->Html->link(__('Edit this information',true), array_merge(array('action' => 'editeyecheck'), $this->request->projectvars['VarsArray']), array('class' => 'round editcertificate'));

echo $this->Html->link(__('Delete this information',true), array_merge(array('action' => 'deleyecheck'), $this->request->projectvars['VarsArray']), array('class' => 'round editcertificate'));

$this->request->projectvars['VarsArray'][17] = 0;

echo $this->Html->link(__('Replace this information',true), array_merge(array('action' => 'replaceeyeckeck'), $this->request->projectvars['VarsArray']), array('class' => 'round editcertificate'));

echo '</p>';
echo '</div>';
?>
<script type="text/javascript">
$(document).ready(function(){
	$("a.editcertificate").click(function() {
		$("#certification").load($(this).attr("href"), {
			"ajax_true": 1,
		})
		return false;
	});	
});
</script>