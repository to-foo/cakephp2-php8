<script type="text/javascript">
$(document).ready(function(){

$("#CascadeAddForm,#CascadeEditForm,.dialogform,.login").bind("submit", function() {


var modalheight = Math.ceil(($(window).height() * 90) / 100);
var modalwidth = Math.ceil(($(window).width() * 90) / 100);

var dialogOpts = {
	modal: false,
	width: modalwidth,
	height: modalheight,
	autoOpen: false,
	draggable: true,
	resizeable: true
};

$("#dialog").dialog(dialogOpts);

var data = $(this).serializeArray();
data.push({name: "ajax_true", value: 1});
data.push({name: "dialog", value: 1});

$("#AjaxSvgLoader").show();

$.ajax({
	type	: "POST",
	cache	: false,
	url		: $(this).attr("action"),
	data	: data,
	success: function(data) {
		$("#AjaxSvgLoader").hide();
		$("#dialog").html(data);
		$("#dialog").show();
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
});
</script>
