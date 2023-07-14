<script>
$(function() {
	$(".time").datetimepicker({ format: "H:i", datepicker: false, scrollInput: false});
	$(".date").datetimepicker({ format: "Y-m-d", timepicker:false, lang:"de", scrollInput: false});
	$(".datetime").datetimepicker({
		lang:"de",
		format: "Y-m-d H:i",
		scrollInput: false
	});
});
</script>
