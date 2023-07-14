<script type="text/javascript">
$(() => {

	if($(".ui-dialog").is(":visible")){
		$("#dialog").dialog().dialog("close");
		$("#dialog").empty();
		$("#dialog").css("overflow","inherit");
		$("#dialog").dialog("destroy");
	} 
	
    return false;

});
</script>
