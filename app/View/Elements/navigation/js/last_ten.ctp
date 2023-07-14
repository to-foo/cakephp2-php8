<script>
$(function() {

	$('table#last_reports_table tbody').hide();

	$(".show_reports").click(function() {
		$('ul#last_reports_menue').hide();
		$('table#last_reports_table tbody#' + $(this).attr("rel")).show();
	});
	$(".icon_back").click(function() {
		$('ul#last_reports_menue').show();
		$('table#last_reports_table tbody').hide();
	});
});
</script>
