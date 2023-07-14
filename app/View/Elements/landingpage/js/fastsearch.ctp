<script type="text/javascript">
$(document).ready(function(){

	var SearchFormName = "<?php echo $SearchFormName;?>";

	$(SearchFormName).on('keyup keypress', function(e) {

		activeObj = document.activeElement;

		if(activeObj.tagName == "TEXTAREA"){
			// do nothing
		} else {
			var keyCode = e.keyCode || e.which;
			if(keyCode === 13) {
				e.preventDefault();
				return false;
			}
		}
	});

	$(SearchFormName + " button#SendThisReportForm").click(function(){

		var data = new Array();

		data.push({name: "ajax_true", value: 1});
		data.push({name: "show_search_result", value: 1});

		$("#fast_searchform_container").hide();
		$(".large_window").css("background-image","url(img/indicator.gif)");

		$.ajax({
			type: "POST",
			url: $("#AutoUrl").val(),
			data: data,
			success:
				function(data) {

					$("#fast_searchresult_container").html(data);
					$("#fast_searchresult_container").show();
					$(".large_window").css("background-image","none");

				},
			});
	});

	$(SearchFormName + " button#ResetThisForm").click(function(){

		$("form" + SearchFormName).find("input[type='hidden']").val(0);
		$("form" + SearchFormName).find("input.autocompletion").val("");

		var text = $("#SendThisReportForm").attr("data-desc");

		$("#SendThisReportForm").text(text);

	});

	$(SearchFormName + " select.dropdown").each(function(key,value){


		$(this).change(function() {

			var item = $(this).val();
			var name = $(this).prop("name");
			var id = $(this).prop("id");
			var HiddenSearchField = "#Searching" + event.currentTarget.attributes.id.nodeValue;

			if(item == null){
				$("#SendThisReportForm").attr("disabled", "disabled");
				$(HiddenSearchField).val(0);
			} else {
				$(HiddenSearchField).val(item);
			}

				var data = $(SearchFormName).serializeArray();

				data.push({name: "ajax_true", value: 1});
				data.push({name: "update_search_result", value: 1});

				var ButtonWidth = $("#SendThisReportForm").width();

				$("#SendThisReportForm").text("Seaching...");
				$("#SendThisReportForm").width(ButtonWidth);

				$(SearchFormName + " input").prop("disabled", true);

				$.ajax({
					type: "POST",
					url: $("#AutoUrl").val(),
					dataType: "json",
					data: data,
					success:
						function(data) {
console.log(data);
							
							$("#SendThisReportForm").css("width","inherit");

							var text = $("#SendThisReportForm").attr("data-desc");

							$("#SendThisReportForm").text(data.count + " " + text);
							$(SearchFormName + " input").prop("disabled", false);

							if(data.count > 0){
								$("#SendThisReportForm").prop("disabled", false);
							} else {
								$("#SendThisReportForm").attr("disabled", "disabled");
							}
							
						},
					});
					
		});

	});

	$(SearchFormName + " input.autocompletion").each(function(key,value){

	var name = $(this).prop("name");
	var id = $(this).prop("id");

	$(this).autocomplete({
	minLength: 2,
	delay: 4,
	source:
		function(request,response) {

			$("#" + id).css("background-image","url(img/indicator.gif)");

			value = name + "[" + request.term + "]"

			var data = new Array();

			data.push({name: name, value: request.term});
			data.push({name: "landig_page_large", value: 1});
			data.push({name: "ajax_true", value: 1});

			$.ajax({
				type: "POST",
				url: $("#AutoUrl").val(),
				dataType: "json",
				data: data,
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
			  },
				success:
					function(data) {
						response(data);
					},
				});
			},
		close: function(event,ui) {

			var HiddenSearchField = "#Searching" + event.target.attributes.id.nodeValue;
			var SearchField = "#" + event.target.attributes.id.nodeValue;

			$(SearchField).css("background-image","none");

			if($(HiddenSearchField).val() == 0){

				$(SearchField).val("");

			}
		},
		select: function(event,ui) {

				var HiddenSearchField = "#" + ui.item.field;
				var HiddenSearchValue = ui.item.key;

				$(HiddenSearchField).val(HiddenSearchValue);

			},
		response: function( event, ui ) {

			var SearchField = "#" + event.target.attributes.id.nodeValue;
			$(SearchField).css("background-image","none");

		},
		change: function( event, ui ) {

				if(ui.item == null){
					$("#SendThisReportForm").attr("disabled", "disabled");
					var HiddenSearchField = "#Searching" + event.currentTarget.attributes.id.nodeValue;
					$(HiddenSearchField).val(0);
				}

				var data = $(SearchFormName).serializeArray();

				data.push({name: "ajax_true", value: 1});
				data.push({name: "update_search_result", value: 1});

				var ButtonWidth = $("#SendThisReportForm").width();

				$("#SendThisReportForm").text("Seaching...");
				$("#SendThisReportForm").width(ButtonWidth);

				$(SearchFormName + " input").prop("disabled", true);

				$.ajax({
					type: "POST",
					url: $("#AutoUrl").val(),
					dataType: "json",
					data: data,
					success:
						function(data) {

							$("#SendThisReportForm").css("width","inherit");

							var text = $("#SendThisReportForm").attr("data-desc");

							$("#SendThisReportForm").text(data.count + " " + text);
							$(SearchFormName + " input").prop("disabled", false);

							if(data.count > 0){
								$("#SendThisReportForm").prop("disabled", false);
							} else {
								$("#SendThisReportForm").attr("disabled", "disabled");
							}
						},
					});
			},
		});
	});

});
</script>
