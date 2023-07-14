<?php echo $this->Form->input('ClassIdNameModal',array('type' => 'hidden','value' => $name));?>

<script type="text/javascript">
$(document).ready(function(){

var AjaxModalLinkGlobal = function(name){

	$(name).click(function() {

//	$("#AjaxSvgLoader").show();

//	$(".ui-dialog").show();
	$("#dialog").dialog().dialog("close");
	$("#maximizethismodal").hide();

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

	var data = new Array();
	data.push({name: "ajax_true", value: 1});
	data.push({name: "dialog", value: 1});

	if($(this).hasClass("dropdown")) {

		var ModelFild = $(this).siblings("input[type=\'text\'],select,textarea").first().attr("name").match(/data\[(.*?)\](\[0\])*\[(.*?)\]/);

		data.push({name: "dropdownid", value: ModelFild[1] + "0" + ModelFild[3]});

	}

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

};
/*
function AjaxModalLinkGlobal(name){

	$(name).click(function() {

	$("#AjaxSvgLoader").show();

//	$(".ui-dialog").show();
	$("#dialog").dialog().dialog("close");
	$("#maximizethismodal").hide();

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

	var data = new Array();
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
};
*/
AjaxModalLinkGlobal($("#ClassIdNameModal").val());

});
</script>
