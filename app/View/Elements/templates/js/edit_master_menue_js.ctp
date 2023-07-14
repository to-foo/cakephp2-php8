<?php
if(isset($this->request->data['data_area'])){
  echo $this->Form->input('DataArea',array('value' => $this->request->data['data_area'],'type' => 'hidden'));
}
?>
<script type="text/javascript">
$(document).ready(function() {

	current_area = "main_area";
	current_area_link = "link_main_area";

	if(($("#DataArea").length > 0)){
		current_area = $("#DataArea").val();
		current_area_link = "link_" + $("#DataArea").val();
	}

	$("div.areas").hide();
	$("div#" + current_area).show();
	$("ul.editmenue li").removeClass("active");
	$("ul.editmenue li").addClass("deactive");
	$("ul.editmenue li#" + current_area_link).addClass("active");
  $("ul.editmenue li#" + current_area_link).removeClass("deactive");

/*
	if($("#DropdownsMasterField").val().length == 0){
    $("#link_data_area").hide();
    $("#DropdownsMasterField").addClass("error");
  }
*/

  $("ul.editmenue li a").on('click', function (e) {

		id = $(this).attr('rel');
		linkid = "link_" + id;

		$("div.areas").hide();
		$("ul.editmenue li").removeClass("active");
		$("ul.editmenue li").addClass("deactive");
		$("div#" + id).show();
		$("ul.editmenue li#" + linkid).removeClass("deactive");
		$("ul.editmenue li#" + linkid).addClass("active");
	});

/*
  $("a.modal_post").click(function() {

		$("#AjaxSvgLoader").show();

		$(".ui-dialog").show();
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

		data = new Array();
		data.push({name: "ajax_true", value: 1});
		data.push({name: "data_area", value: $("ul.editmenue li.active a").attr("rel")});

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
				$("#AjaxSvgLoader").hide();
				}
			});

		return false;

	});
*/
});
</script>
