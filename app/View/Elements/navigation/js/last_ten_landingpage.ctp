<script>
$(document).ready(function() {

$("ul#last_reports_menue li").tooltip();

$('table#last_reports_tables tbody').hide();
$('ul#last_reports_icon_back').hide();

$(".show_reports").click(function() {

	var tableheight = $(".last_infos").height();
	var tablewidth = $(".last_infos").width();

	var thisheight = $(this).closest("div").height();
	var thiswidth = $(this).closest("div").width();

	$(this).closest("div").height(tableheight);
	$(this).closest("div").width(tablewidth);
	$(this).closest("div").css("position","absolute");

	$('ul#last_reports_icon_back').show();
	$('ul#last_reports_menue').hide();
	$('table#last_reports_tables tbody#' + $(this).attr("rel")).show();

	$(".icon_back").click(function() {

		$(this).closest("div").height(thisheight);
		$(this).closest("div").width(thiswidth);
		$(this).closest("div").css("position","inherit");

		$('ul#last_reports_icon_back').hide();
		$('ul#last_reports_menue').show();
		$('table#last_reports_tables tbody').hide();
	});

});
});
</script>
