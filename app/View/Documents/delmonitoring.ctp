<div class="modalarea">
<h2><?php echo __('Documents'); ?> - <?php echo $this->request->data['Document']['name'];?> (<?php echo $this->request->data['Document']['registration_no'];?>)</h2>
<?php echo $this->element('Flash/_messages');?>
<div class="hint">
<p>
<?php
echo __('Documents') . ': ';
echo $this->request->data['Document']['name'] . ' (' . $this->request->data['Document']['registration_no'].') ';
echo '<strong>'.$this->request->data['DocumentCertificate']['certificat'].'</strong>';
echo '</p><p>';
echo __('Next scheduled monitoring',true). ': ';
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
<?php echo $this->Form->create('Document', array('class' => 'dialogsubform')); ?>
<fieldset>
<?php echo $this->Form->input('DocumentCertificate.id');?>
</fieldset>
<?php echo $this->Form->end(__('Delete', true));?>
</div>
<?php
$back_url = $this->Html->url(array_merge(array('action' => 'monitorings'),$this->request->projectvars['VarsArray']));
?>
<?php
if(isset($FormName) && count($FormName) > 0){
	echo $this->JqueryScripte->RefreshAfterDialog(0,0,$FormName);
	}

if(isset($saveOK) && $saveOK  == 1){
	echo $this->JqueryScripte->DialogClose();
	}
?>

<script type="text/javascript">
$(document).ready(function(){

	$("button.back_button").click(function() {
		var url = $(this).closest('form').attr("action");
		$("#dialog").load("<?php echo $back_url;?>", {
			"ajax_true": 1,
		})
		return false;
	});

	$("form.dialogsubform").bind("submit", function() {

		var data = $(this).serializeArray();
		data.push({name: "ajax_true", value: 1});

		$.ajax({
			type	: "POST",
			cache	: false,
			url		: $(this).attr("action"),
			data	: data,
			success: function(data) {
		    	$("#dialog").html(data);
		    	$("#dialog").show();
			}
		});
		return false;
	});
});
</script>

<?php echo $this->JqueryScripte->ModalFunctions(); ?>
