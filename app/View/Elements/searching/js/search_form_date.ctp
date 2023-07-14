<script>
$(document).ready(function(){
	$("iput.date").prop({"readOnly": false}).datetimepicker({
		timepicker:	false,
		format:			$("#DateForPicker").val(),
		lang:				$("#GenerallyLanguageForPicker").val(),
	});
});
</script>
