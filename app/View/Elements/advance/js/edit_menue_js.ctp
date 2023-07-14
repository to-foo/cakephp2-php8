<?php
echo $this->Form->input('CurrentUrl',array('value' => $this->request->here,'type' => 'hidden'));
if(isset($this->request->data['data_area'])){
  echo $this->Form->input('DataArea',array('value' => $this->request->data['data_area'],'type' => 'hidden'));
}
?>
<script type="text/javascript">
$(document).ready(function() {

  current_area = "advance_area";

  $("div.areas").hide();
  $("div#" + current_area).show();

/*
  current_area_detail = "advance_tablecontent";
	current_area_link = "link_advance_area";

  $("div#" + current_area_detail).show();

  $("div#advance_diagrammcontent").hide();

	$("ul.editmenue li").removeClass("active");
	$("ul.editmenue li").addClass("deactive");
	$("ul.editmenue li#" + current_area_link).addClass("active");
*/

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

});
</script>
