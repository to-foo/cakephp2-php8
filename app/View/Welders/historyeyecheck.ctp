<div class="modalarea">
<h2>
<?php  
echo __('Examiner') . ' ' . 
$certificate_data['Examiner']['name'] . ' - ' . 
__('history') . ' ' .
$certificate_data['Eyecheck']['certificat'];
?>
</h2>
<div id="dialogform_content">
<div id="message_wrapper"><?php echo $this->Session->flash();?></div>
<ul class="listemax certificates">
<?php
echo '<li>';
echo $this->Html->link($certificate_data['Eyecheck']['certificat'] . ' ' . __('created on') . ' ' . $certificate_data['Eyecheck']['first_registration'], 'javascript:');
echo '</li>';

foreach($certificate_datas as $_certificate_datas){
	$this->request->projectvars['VarsArray'][17] = $_certificate_datas['EyecheckData']['id'];
	echo '<li>';
	if(isset($_certificate_datas['EyecheckData']['file']) && $_certificate_datas['EyecheckData']['file'] == true){
		echo $this->Html->link($certificate_data['Eyecheck']['certificat'] . ' ' . __('renewed on') . ' ' . $_certificate_datas['EyecheckData']['certified_date'], array_merge(array('action' => 'geteyecheckfile'), $this->request->projectvars['VarsArray']), array('target' => '_blank'));
	}
	else {
		echo $this->Html->link(__('No certificate file found'), 'javascript:');
	}
	echo '</li>';
}
?>
<ul>
</div>

<script type="text/javascript">
$(document).ready(function(){
});
</script>
</div>
<div class="clear"></div>
<?php echo $this->JqueryScripte->ModalFunctions(); ?>
