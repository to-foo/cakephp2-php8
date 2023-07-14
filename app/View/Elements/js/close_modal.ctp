<script type="text/javascript">
$(() => {
	$("a#closethismodal").click( _ => {
		if($(".ui-dialog").is(":visible")){
			$("#dialog").dialog().dialog("close");
			$("#dialog").empty();
			$("#dialog").css("overflow","inherit");
			$("#dialog").dialog("destroy");
		} else {

		}
		return false;
	});
});
</script>
