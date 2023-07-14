<div class="modalarea">
<h2><?php echo __('Edit image settings')?></h2>
<?php echo $this->element('Flash/_messages');?>
<?php
echo $this->element('js/ajax_stop_loader');

if($this->request->data['Reportnumber']['status'] > 0 && $this->request->data['Reportnumber']['revision_progress'] == 0) {

	echo $this->element('js/close_modal');
	echo $this->element('js/minimize_modal');
	echo $this->element('js/maximize_modal');
	echo '</div>';
	return;

}

if(isset($FormName) && count($FormName) > 0){
	echo $this->element('js/reload_container',array('FormName' => $FormName));
	echo $this->element('js/ajax_stop_loader');
	echo $this->element('js/close_modal_auto');
	echo '</div>';
	return;
	}
?>
<?php echo $this->Form->create('Reportimage',array('class' => 'login')); ?>
<div class="flex">
<div class="item">
<fieldset>
<?php
echo $this->Form->input('id');
echo $this->Form->input('print',array('label' => 'test test test','type' => 'radio','options' => array(0 => 'nein',1 => 'ja')));
echo $this->Form->input('discription');
?>
<fieldset class="hint">
<p><?php echo __('The following settings only have an effect if one image per page has been selected for the output.');?></p>
</fieldset>
<?php
echo $this->Form->input('size',array(
	'label' => __('Image size in percent (%)'),
	'div' => 'slider',
	'class' => 'slider',
	'after' => '<div class="slider_box" id="slider"><div id="custom-handle" class="ui-slider-handle"></div></div>'
	)
);
?>
<script>
$(function() {

	var handle = $("#custom-handle");

	$("#slider").width($("#ReportimageSize").width());

	$("#slider").slider({
		value: $("#ReportimageSize").val(),
		min: 10,
		max: 100,
		step: 10,
		create: function() {
			handle.text($(this).slider("value"));
		},
		slide: function(event,ui) {
			$("#ReportimageSize").val(ui.value);
			handle.text(ui.value);
		}
	});

	$("#ReportimageSize").attr("type","hidden");

});
</script>

<?php
echo $this->Form->input('max_size',array('legend' => __('Enlarge image to fit page') . ' ' . __('Only if the images are too small '),'type' => 'radio','options' => array(0 => 'nein',1 => 'ja')));
echo $this->Form->input('border',array('type' => 'radio','options' => array(0 => 'nein',1 => 'ja')));
echo $this->Form->input('position',array('type' => 'radio','options' => array(0 => __('left'),1 => __('center'),2 => __('right'))));
?>
</fieldset>
<?php echo $this->element('form_submit_button',array('action' => 'reset','description' => __('Save')));?>
</div>
<div class="item image_item"><?php echo '<img src="' . $this->request->data['Reportimage']['imagedata'] . ' " />';?></div>
</div>
</div>
<?php
echo $this->element('js/form_send_modal',array('FormId' => 'ReportimageImagediscriptionForm'));
echo $this->element('js/form_button_set');
?>
