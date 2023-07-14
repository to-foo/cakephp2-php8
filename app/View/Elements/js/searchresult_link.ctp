<script type="text/javascript">
$(document).ready(function(){
	$("a.testingreportlink").click(function() {
		$("#maximizethismodal").hide();
		$("#searchresultlink").show();
		$("#searchresultlink").css("visibility","visible");

		$("#searchresultlink").attr("rel",$("form#ReportsOrdersCase input#search_type").val());
		$("#searchresultlink").attr("rev",$("form#ReportsOrdersCase input#history").val());

		$("#dialog").dialog().dialog("close");
		$("#searchresultlink").attr("href","<?php echo $url;?>");
		return false;
	});
});
</script>
