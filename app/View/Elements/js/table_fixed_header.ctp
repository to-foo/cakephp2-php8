<script type="text/javascript">
$(document).ready(function(){

	var thead_th_width = new Array();

	$x = 0;

//	$(".table_fixed_header thead tr th").each(function(){
	$(".table_fixed_header tbody tr td").each(function(){

		thead_th_width.push({name: $x, value: $(this).width()});
		$x++;

	});


	var tableOffset = $(".table_fixed_header").offset().top;
	var header = $(".table_fixed_header > thead").clone();
//	var form = $("form#SortExamierTable").clone();
	var quicksearch = $("div.quicksearch").clone();

	$("#fixed_quicksearch").append(quicksearch);
//	$("#fixed_form").append(form);
	$("#table_header_fixed").append(header);

	$("div.fixed_quicksearch a.modal").click(function() {

	$("#AjaxSvgLoader").show();

	$(".ui-dialog").show();
	$("#dialog").dialog().dialog("close");
	$("#maximizethismodal").hide();

	var data = new Array;
	data.push({name: "ajax_true", value: 1});
	data.push({name: "dialog", value: 1});

	$("#dialog").empty();

	$.ajax({
		type	: "POST",
		cache	: false,
		url		: $(this).attr("href"),
		data	: data,
		success: function(data) {
			$("#dialog").html(data);
			$("#dialog").dialog("open");
			$("#dialog").show();
			$("#dialog").css('overflow','scroll');
			$("#AjaxSvgLoader").hide();
		},
		statusCode: {
	    404: function() {
	      alert( "page not found" );
				location.reload();
	    }
	  },
		statusCode: {
	    403: function() {
	      alert( "page blocked" );
				location.reload();
	    }
	  }
		});

		return false;
	});

	form_id = $("#FormId").val();
	min_lenght = $("#MinLength").val();
	json_url = $("#JsonUrl").val();
	target_controller = $("#TargetController").val();
	target_action = $("#TargetAction").val();
	target_result = $("#TargetResult").val();

	$("form#" + form_id).on("keyup keypress", function(e) {
		var keyCode = e.keyCode || e.which;
		if(keyCode === 13) {
			e.preventDefault();
			return false;
		}
	});

	var i = 0;

	$("div#fixed_quicksearch #" + form_id + " input.autocompletion").each(function(key,value){

		$(this).autocomplete({
			minLength: min_lenght,
			delay: 4,
			source: function(request,response) {
				$.ajax({
					type	: "POST",
					url: json_url,
					dataType: "json",
					data: {
						term : request.term,
						targetcontroller : target_controller,
						targetation : target_action,
					},

			success: function(data) {
				response(data);
				},
			});
			},
			select: function(event,ui) {

				$("#QuicksearchOrderSearchingAutocomplet").val(ui.item.value);
				$("#QuicksearchOrderThisId").val(ui.item.key);

				var data = $("#QuicksearchOrderForm").serializeArray();
				data.push({name: "ajax_true", value: 1});
				data.push({name: "this_id", value: ui.item.key});

				$.ajax({
					type	: "POST",
					cache	: false,
					url		: target_result + "/" + ui.item.key,
					data	: data,
					success: function(data) {
						$("#container").html(data);
						$("#container").show();
					}
				});
				return false;

			}
		});
		i++;
	});

	$("div#fixed_quicksearch #QuicksearchOrderSearchingAutocomplet").change(function() {
		if($("#QuicksearchOrderSearchingAutocomplet").val() == "" && $("#QuicksearchOrderThisId").val() == 0){
			$("#container").load($("#QuicksearchOrderForm").attr("action"), {"ajax_true": 1});
		}
	});

	$("div#fixed_quicksearch form#QuicksearchOrderForm").bind("submit", function() {
		if($("#this_id").val() == 0){
			return false;
		}

		var data = $(this).serializeArray();
		data.push({name: "ajax_true", value: 1});
		$.ajax({
			type	: "POST",
			cache	: false,
			url		: this.getAttribute("action"),
			data	: data,
			success: function(data) {
				$("#container").html(data);
				$("#container").show();
			}
		});
		return false;
	});

	$x = 0;

	$("#table_header_fixed thead tr th").each(function(){
		if(thead_th_width[$x] != undefined){
			$(this).width(thead_th_width[$x].value);
		}
		$x++;

	});

	$x = 0;

	$(".table_fixed_header tbody tr td").each(function(){

		$(this).width(thead_th_width[$x].value);
		$x++;

	});


	$(window).scroll(function() {

	    var offset = $(this).scrollTop();

	    if (offset >= tableOffset && $("div.fixed_element").is(":hidden")) {
	        $("div.fixed_element").show();
	    }
	    else if (offset < tableOffset) {
	        $("div.fixed_element").hide();
	    }
	});

});
</script>
