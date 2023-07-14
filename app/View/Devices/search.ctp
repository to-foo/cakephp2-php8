<div class="">
<h2><?php echo __('Search device',true); ?></h2>
<div class="quicksearch">
<?php echo $this->element('barcode_scanner');?>
<div class="clear"></div>
</div>
<div id="message_wrapper"><?php echo $this->Session->flash(); ?></div>
<?php echo $this->Form->create('Device',array('class'=>'search_form')); ?>
<?php echo $this->element('searching/search_standard_fields',array('SearchFieldsStandard' => $SearchFieldsStandard));?></fieldset>
<?php echo $this->Form->button(__('Search devices',true),array('type' => 'submit','id' => 'SendThisDeviceForm')); ?>
<?php //echo $this->Form->button(__('Reset',true),array('type' => 'button','id' => 'ResetThisForm')); ?>
<?php echo $this->Form->end(); ?>
</div>
<?php
  if(count($HistorySearch) > 0) echo $this->element('search_history_window_device', array('HistorySearch' => $HistorySearch));
?>
<?php echo $this->element('js/form_multiple_fields');?>
<?php echo $this->element('js/form_send_by_change',array('FormId' => 'DeviceSearchForm'));?>
<?php echo $this->element('js/form_send',array('FormId' => 'DeviceSearchForm'));?>
<?php echo $this->element('js/ajax_modal_link');?>
<?php echo $this->element('js/ajax_breadcrumb_link');?>
<?php echo $this->element('js/ajax_link');?>
<?php echo $this->element('js/device_autocomplete');?>
<?php echo $this->element('js/devices_bread_search_combined', array('hideElements' => true)); ?>


<script type="text/javascript">
$(document).ready(function(){

    $("a.search_history_result").click(function() {

  		var data = new Array();
  		data.push({name: "ajax_true", value: 1});
  		data.push({name: "show_result", value: 1});
  		data.push({name: "history", value: $(this).attr("rev")});

  		$.ajax({
  			type	: "POST",
  			cache	: false,
  			url		: "<?php echo Router::url(array_merge(array('action'=>'search'), $this->request->projectvars['VarsArray']));?>",
  			data	: data,
  			success: function(data) {
  				$("#container").html(data);
  				$("#container").show();
  				//DeleteFormElements(searchdata);
  				//searchdata.length = 0;
  				$("#AjaxSvgLoader").hide();
  				}
  			});
  		return false;
  	});
  });
</script>
