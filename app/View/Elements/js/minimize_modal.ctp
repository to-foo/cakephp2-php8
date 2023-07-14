<script type="text/javascript">
$(document).ready(function(){
	$("a#minimizethismodal").click(function() {
		$(".ui-dialog").hide();
		$("div.memory_navigation a.maximizemodal").css("display","block");
		$("div.memory_navigation a.maximizemodal").css("visibility","visible");
		$("div.memory_navigation a.maximizemodal").attr("title",$("a.maximizemodal").text() + " - " + $("div#dialog h2").text());
		return false;
	});
});
</script>
