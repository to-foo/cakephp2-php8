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

	$(SearchFormName + " input.autocompletion").each(function(key,value){

	var name = $(this).prop("name");
	var id = $(this).prop("id");

	$(this).autocomplete({
		minLength: 2,
		delay: 4,
		source: function(request,response) {

			$("#" + id).css("background-image","url(img/indicator.gif)");
			$("#" + id).css("background-repeat","none");

			value = name + "[" + request.term + "]"

			var data = new Array();

			data.push({name: name, value: request.term});
			data.push({name: "json_true", value: 1});
			data.push({name: "ajax_true", value: 1});

		$.ajax({
			type: "POST",
			url: $("#QuicksearchJsonUrl").val(),
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
		response: function( event, ui ) {

			var SearchField = "#" + event.target.attributes.id.nodeValue;
			$(SearchField).css("background-image","none");

		},
		select: function(event,ui) {

			var HiddenSearchField = "#" + ui.item.field;
			var HiddenSearchValue = ui.item.key;

			$(HiddenSearchField).val(HiddenSearchValue);

			if(ui.item == null){
				return false;
			}

			let url = $("#QuicksearchRequestUrl").val() + "/" + ui.item.project + "/" + ui.item.cascade + "/" + ui.item.value;
			let data = new Array();

			data.push({name: "ajax_true", value: 1});

			$("#AjaxSvgLoader").show();

        	$.ajax({
				type	: "POST",
				cache	: false,
				url		: url,
				data	: data,
				success: function(data) {
					$("#container").html(data);
					$("#container").show();
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
		},
		});
	});

});
</script>
