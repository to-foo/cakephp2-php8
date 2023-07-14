<div class="modalarea welders index inhalt">
<h2><?php echo __('Certificate') . ' - ' . $certificate_data_old_infos['WelderMonitoring']['certificat'];?></h2>
<?php echo $this->element('Flash/_messages');?>
<?php
if(isset($FormName) && count($FormName) > 0){
	echo $this->element('js/reload_container',array('FormName' => $FormName));
	echo $this->element('js/ajax_stop_loader');
	echo $this->element('js/close_modal_auto');
	echo '</div>';
	return;
	}
?>

<div class="hint">
<p>
<?php
echo __('Next scheduled certificate',true). ': ';
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

<?php echo $this->Form->create('WelderMonitoringData', array('class' => 'login')); ?>
<fieldset>
	<?php echo $this->element('form/modulform',array('data' => $this->request->data,'setting' => $settings,'lang' => $locale,'step' => 'WelderMonitoringData','testingmethods' => false));?>
</fieldset>
<?php echo $this->element('form_submit_button',array('action' => 'close','description' => __('Submit',true)));?>
</div>
<script type="text/javascript">
$(document).ready(function(){

	$("span#date_next_certifcation").click(function() {
		$("#WelderMonitoringDataCertifiedDate").val($(this).text());
	});

});
</script>
<?php
echo $this->element('js/form_send_modal',array('FormId' => 'WelderMonitoringDataReplacemonitoringForm'));
echo $this->element('js/ajax_modal_link_global',array('name' => 'a.dropdown'));
echo $this->element('js/form_button_set');
echo $this->element('js/form_datefield');
echo $this->element('js/ajax_mymodal_link');
echo $this->element('js/close_modal');
echo $this->element('js/minimize_modal');
echo $this->element('js/maximize_modal');
echo $this->element('js/scroll_modal_top');
?>
