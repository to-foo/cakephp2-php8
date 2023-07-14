<div class="modalarea">
<h2><?php echo __('Monitoring') . ' - ' . $certificate_data['DocumentCertificate']['certificat'];?></h2>
<?php echo $this->element('Flash/_messages');?>
<div class="hint">
<p>
<?php
echo __('Next scheduled certification',true). ': ';
echo '<span class="date_next_certifcation" id="date_next_certifcation" title="';
echo __('Use this date for certification',true);
echo '">';
echo $certificate_data_old_infos['DocumentCertificateData']['next_certification'];
echo '</span>';
?>
</p>
<p>
<?php echo ($certificate_data_old_infos['DocumentCertificateData']['time_to_next_certification']);?>
</p></div>
<?php
if(isset($FormName) && count($FormName) > 0){
	echo $this->JqueryScripte->RefreshAfterDialog(0,0,$FormName);
	echo $this->JqueryScripte->DialogClose();
	echo $this->JqueryScripte->ModalFunctions();
	echo '</div>';
	return;
	}

?>
<?php echo $this->Form->create('DocumentCertificateData', array('class' => 'dialogform')); ?>
<fieldset>
<?php echo $this->ViewData->EditOrderData($this->request->data,$settings,$locale,'DocumentCertificateData');?>
</fieldset>
<?php echo $this->Form->end(__('Submit', true));?>
</div>
<?php
if(isset($FormName) && count($FormName) > 0){
	echo $this->JqueryScripte->RefreshAfterDialog(0,0,$FormName);
	echo $this->JqueryScripte->DialogClose();
	}

$back_url = $this->Html->url(array_merge(array('action' => 'monitoring'),$this->request->projectvars['VarsArray']));
?>
<script type="text/javascript">
$(document).ready(function(){
	$("span#date_next_certifcation").click(function() {

		check = confirm("<?php echo __('Soll dieses Datum als Zertifizierungszeitpunkt verwendet werden?');?>");

		if (check == false) {
			return false;
		}

		$("#DocumentCertificateDataCertifiedDate").val($(this).text());
	});
});
</script>
<?php echo $this->JqueryScripte->ModalFunctions(); ?>
