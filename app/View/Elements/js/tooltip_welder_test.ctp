<script type="text/javascript">
$(document).ready(function(){

	$("div.welder_test_tooltip").hide();
	
	$("a.weldertest_tooltip").tooltip({
		content: function() {
			var element = $(this).attr("rev");
			var content = $("div." + element).html();
			return content;
		}
	});
});
</script>