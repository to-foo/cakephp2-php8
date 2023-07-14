<script type="text/javascript">

$("#revision_in_report").removeClass();
$("#revision_in_report").addClass("<?php echo $class;?> hidden");

<?php
if($class == 'versionize_progress' || $class == 'versionize'){
	echo '$("#container").load("' . $url . '", {"ajax_true": 1});';
}
?>
</script>
<?php
echo $this->element('js/form_send_modal',array('FormId' => 'ReportnumberRevisionForm'));
echo $this->element('js/ajax_modal_link_global',array('name' => 'a.dropdown'));
echo $this->element('js/form_button_set');
?>
