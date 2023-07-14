<?php
echo $this->Form->input('ThisUploadContainer',array('type' => 'hidden','value' => $container));
echo $this->Form->input('ThisFildLabel',array('type' => 'hidden','value' => $FileLabel));
?>

<script type="text/javascript">

Dropzone.autoDiscover = false;

$(document).ready(function() {

	var drop = new Dropzone(".dropzone", {
		paramName: "file",
		maxFilesize: $("#ThisMaxFileSize").val(),
		acceptedFiles: $("#ThisAcceptedFiles").val(),
		autoProcessQueue: false,
		parallelUploads: 10,
		addRemoveLinks: true,
		url: $("#ThisUploadUrl").val(),
		dictDefaultMessage: $("#ThisFildLabel").val(),
		init: function () {

			this.element.querySelector("button[type=submit]").addEventListener("click", function(e)	{

				e.preventDefault();
				e.stopPropagation();
				drop.processQueue();

			});

			var totalFiles = 0,
			completeFiles = 0;

			this.on("addedfile", function (file) {
					totalFiles += 1;
			});
			this.on("removed file", function (file) {
					totalFiles -= 1;
			});
			this.on("complete", function (file) {

				var maxsize = 200000000;
			 	var filesize = file.width * file.height * 3;
				if($("#ThisAcceptedFiles").val() == 'application/pdf') {
					filesize = 0;
				}
				if(maxsize > filesize){
					$(file.previewElement).addClass("dz-success");
					drop.removeFile(file);
				} else {
					$(file.previewElement).removeClass("dz-success");
					$(file.previewElement).addClass("dz-error").find('.dz-error-message').text("File to big!");
				}

				completeFiles += 1;

				if (completeFiles === totalFiles) {

				$("#AjaxSvgLoader").show();

				var container = $("#ThisUploadContainer").val();
				var data = new Array();

				data.push({name: "ajax_true", value: 1});

				$.ajax({
					type	: "POST",
					cache	: false,
					url		: $("#ThisUploadUrl").val(),
					data	: data,
					success: function(data) {
	//					$("#dialog").dialog("close");
						$("#AjaxSvgLoader").hide();
						$(container).html(data);
						$(container).show();
						}
					});
				}
			});
		}
	});

	$("#UploadFormSendButton").click(function(){
		return false;
	});

});
</script>
