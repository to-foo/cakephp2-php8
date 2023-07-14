<span id="sessionspan"></span>
<script type="text/javascript">
$(document).ready(function(){

	var SaveQuest = 0;
	var rel_menue = null;

	if($("#Li_TestingArea").hasClass("active")){

		$("#AjaxSvgLoader").show();

		var url = $("#CurrentEvaluationUrl").val();
		var data = new Array();

		data.push({name: "ajax_true", value: 1});

		$.ajax({
			type	: "POST",
			cache	: false,
			url		: url,
			data	: data,
			success: function(data) {
		    	$("#EvaluationArea").html(data);
		    	$("#EvaluationArea").show();
					$("#AjaxSvgLoader").hide();
			}
		});

	}

	$("a.TestingArea").one("click", function() {

		$("#AjaxSvgLoader").show();

		var url = $("#CurrentEvaluationUrl").val();
		var data = new Array();
		data.push({name: "ajax_true", value: 1});

		$.ajax({
			type	: "POST",
			cache	: false,
			url		: url,
			data	: data,
			success: function(data) {
		    	$("#EvaluationArea").html(data);
		    	$("#EvaluationArea").show();
					$("#AjaxSvgLoader").hide();
			}
		});
	});

	$("div.reportnumbers a, div.buttons input").click(function() {
		$("ul.editmenue li a").each(function(){
			if($(this).hasClass("active")){
			}
		});
	});

	$("form.editreport input.hide_box").on('change', function() {
		data = [{'name': 'ajax_true', 'value': 1}, {'name': 'hideField', 'value': 'hideField' }, {'name': 'field', 'value': $(this).attr('id') }, {'name': 'value', 'value': $(this).prop('checked') }];

		$.ajax({
			'url': $("#CurrentUrl").val(),
			'type': 'post',
			'data': data,
			'success': function(data) {
			}
		});
	});

	$("ul.editmenue a").click(function() {

		$("#content .reportnumbers .edit .specialchars").attr("rel", "");

		$("#ReportnumberRelMenue").val($(this).attr("rel"));

		$("ul.editmenue li").removeClass("active").addClass("deaktive");
		$("ul.editmenue li a").removeClass("active").addClass("deaktive");
		$("div.formcontainer").hide();
		$("div.TestingArea").hide();
		$("div.addition").hide();
		$(".buttons").show();

		if($(this).attr("id") == "TestingArea"){
			$("div.buttons div.submit input").hide();
			$("div.buttons div.reset input#ReportnumberZurücksetzen").hide();
		}
		else {
			$("div.buttons div.submit input").show();
			$("div.buttons div.reset input#ReportnumberZurücksetzen").show();
		}

		$("." + $(this).attr("rel")).show();
		$(this).parent("li").removeClass('deaktive').addClass("active");
		$(this).parent("li a").removeClass('deaktive').addClass("active");

		$("#sessionspan").load($("#SessionUrl").val(), {
				"id": $("#ReportnumberId").val(),
				"ajax_true": 1,
				"editmenue": $(this).attr("rel")
				}
			);
		return false;
	});

	$( "ul.editmenue li" ).each(function() {

		Id = $(this).attr("id");
		Rel = "General";

		if($("#" + Id + " a").hasClass("General")){Rel = "General";}
		if($("#" + Id + " a").hasClass("Specify")){Rel = "Specify";}
		if($("#" + Id + " a").hasClass("TestingArea")){Rel = "TestingArea";}

		if($(this).hasClass("active")){
			$("#container #content .detail div." + Rel).show();
		} else {
			$("#container #content .detail div." + Rel).hide();
		}
	});
});
</script>
