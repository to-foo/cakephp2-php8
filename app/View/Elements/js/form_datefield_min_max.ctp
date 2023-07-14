<?php
if(!isset($MinDate)) return;
if(!isset($MaxDate)) return;
echo $this->Form->input('ThisStartDateMin',array('type' => 'hidden','value' => $MinDate));
echo $this->Form->input('ThisEndDateMax',array('type' => 'hidden','value' => $MaxDate));
?>
<script>
$(function() {
	$(".date").datetimepicker({
		timepicker:false,
		lang:"de",
		minDate: new Date($("#ThisStartDateMin").val()),
		maxDate: new Date($("#ThisEndDateMax").val()),
		format: "Y-m-d",
		scrollInput: false
	});
});
</script>
