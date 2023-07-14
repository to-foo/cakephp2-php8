<div class="modalarea">
<h2>
<?php  
echo __('Welder') . ' ' . 
$certificate_data['Welder']['name'] . ' ' . 
__('history') . ' ' .
$certificate_data['WelderCertificate']['third_part'] . '/' .
$certificate_data['WelderCertificate']['sector'] . '/' .
$certificate_data['WelderCertificate']['certificat'] . '/' .
ucfirst($certificate_data['WelderCertificate']['weldingmethod'])
;
?>
</h2>
<div id="dialogform_content">
<div id="message_wrapper"><?php echo $this->Session->flash();?></div>
<ul class="listemax certificates">
<?php
echo '<li>';
echo $this->Html->link(__('Qualification created on') . ' ' . $certificate_data['WelderCertificateData']['first_registration'], 'javascript:');
echo '</li>';

foreach($certificate_datas as $_certificate_datas){
	$this->request->projectvars['VarsArray'][17] = $_certificate_datas['WelderCertificateData']['id'];
	echo '<li>';
	if(isset($_certificate_datas['WelderCertificateData']['file']) && $_certificate_datas['WelderCertificateData']['file'] == true){
		echo $this->Html->link(__('Qualification certificated on') . ' ' . $_certificate_datas['WelderCertificateData']['certified_date'], array_merge(array('action' => 'getcertificatefile'), $this->request->projectvars['VarsArray']), array('target' => '_blank'));
	}
	else {
		echo $this->Html->link(__('No certificate file found'), 'javascript:');
	}
	echo '</li>';
}
?>
<ul>
<?php
//pr($certificate_data);		
//pr($certificate_datas);		
?>
</div>

<script type="text/javascript">
$(document).ready(function(){
	$("a.editcertificate").click(function() {
		$("#certification").load($(this).attr("href"), {
			"ajax_true": 1,
		})
		return false;
	});	
	$("a.editwelder").click(function() {
		$("#dialogform_content").load($(this).attr("href"), {
			"ajax_true": 1,
		})
		return false;
	});		
	$("a.refreshwelder").click(function() {
		$("#dialog").load($(this).attr("href"), {
			"ajax_true": 1,
		})
		return false;
	});		
});
</script>
</div>
<div class="clear"></div>
<?php echo $this->JqueryScripte->ModalFunctions(); ?>
