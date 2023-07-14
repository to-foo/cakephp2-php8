<div class="modalarea">
<?php
if(isset($FormName) && count($FormName) > 0){
	echo $this->element('js/reload_container',array('FormName' => $FormName));
	echo $this->element('js/ajax_stop_loader');
	echo $this->element('js/close_modal_auto');
	echo '</div>';
	return;
	}
?>

<h2><?php echo __('Welders'); ?> - <?php echo $this->request->data['Welder']['name'];?> </h2>
<div id="message_wrapper"><?php echo $this->Session->flash();?></div>
<div class="hint">
<p>
<?php
echo '<strong>'.$this->request->data['WelderMonitoring']['certificat'].'</strong><br>';
echo __('Welders') . ': ';
echo $this->request->data['Welder']['name'] ;
echo '</p><p>';
echo __('Next scheduled monitoring',true). ': ';
echo '<span class="date_next_certifcation" id="date_next_certifcation" title="';
echo __('Use this date for certification',true);
echo '">';
echo $certificate_data_old_infos['WelderMonitoringData']['next_certification'];
echo '</span>';
?>
</p>
<p>
<?php echo ($certificate_data_old_infos['WelderMonitoringData']['time_to_next_certification']);?>
</p></div>
<?php echo $this->Form->create('WelderMonitoring', array('class' => 'login')); ?>
<fieldset>
<?php echo $this->Form->input('WelderMonitoring.id');?>
</fieldset>
<?php echo $this->element('form_submit_button',array('action' => 'close','description' => __('Delete',true)));?>
</div>
<?php
$back_url = $this->Html->url(array_merge(array('action' => 'monitorings'),$this->request->projectvars['VarsArray']));
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

<?php
echo $this->element('js/form_send_modal',array('FormId' => 'WelderMonitoringDelmonitoringForm'));
echo $this->element('js/ajax_modal_link_global',array('name' => 'a.dropdown'));
echo $this->element('js/form_button_set');
echo $this->element('js/form_datefield');
echo $this->element('js/ajax_mymodal_link');
echo $this->element('js/close_modal');
echo $this->element('js/minimize_modal');
echo $this->element('js/maximize_modal');
echo $this->element('js/scroll_modal_top');
?>
