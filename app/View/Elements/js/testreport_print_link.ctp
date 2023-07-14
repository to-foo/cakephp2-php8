<?php $ContainerURL = str_replace("%2C", "/", $this->Html->url(array('controller' => $FormName['controller'], 'action' => $FormName['action'].'/'.$FormName['terms'])));?>
<input type="hidden" id="ReloadContainerID" value="<?php echo $ContainerURL;?>" />

<script type="text/javascript">
$(document).ready(function() {

	function CloseReloadAfterPrinting() {

		var data = new Array();
		data.push({name: "ajax_true", value: 1});

		if(($("#DataAreaModal").length > 0)) data.push({name: "data_area", value: $("#DataAreaModal").val()});

		$.ajax({
			type	: "POST",
			cache	: false,
			url		: $("#ReloadContainerID").val(),
			data	: data,
			success: function(data) {
				$("#container").html(data);
				$("#container").show();
				$("#AjaxSvgLoader").hide();
				}
		});

		$("#dialog").dialog().dialog("close");

	}

	$("a.printlink").click(function() {
		setTimeout(CloseReloadAfterPrinting, 3000);
		window.open(
		  $(this).attr("href"),
		  "_blank"
		);
		return false;
	});

	$("a.proofprintlink").click(function() {
		window.open(
		  $(this).attr("href"),
		  "_blank"
		);
		return false;
	});
});
</script>
