<?php
echo $this->Form->input('ThisUploadContainer', array('type' => 'hidden','value' => $container));
echo $this->Form->input('ThisFildLabel', array('type' => 'hidden','value' => $FileLabel));
?>

<script type="text/javascript">
	Dropzone.autoDiscover = false;

	$(document).ready(function() {

		var drop = new Dropzone(".dropzone", {
			paramName: "file",
			maxFilesize: $("#ThisMaxFileSize").val(),
			acceptedFiles: $("#ThisAcceptedFiles").val(),
			autoProcessQueue: false,
			parallelUploads: 100,
			maxFiles: 1,
			addRemoveLinks: true,
			url: $("#ThisUploadUrl").val(),
			dictDefaultMessage: $("#ThisFildLabel").val(),
			init: function() {

				var totalFiles = 0,
					completeFiles = 0;
				this.on("addedfile", function(file) {
					totalFiles += 1;
				});
				this.on("removed file", function(file) {
					totalFiles -= 1;
				});
				this.on("complete", function(file) {
					completeFiles += 1;
					if (completeFiles === totalFiles) {

						$("#AjaxSvgLoader").show();

						var container = $("#ThisUploadContainer").val();

						var data = new Array();
						data.push({
							name: "ajax_true",
							value: 1
						});
						data.push({
							name: "close_reload",
							value: 1
						});

						$.ajax({
							type: "POST",
							cache: false,
							url: $("#ThisUploadUrl").val(),
							data: data,
							success: function(data) {
								$(container).html(data);
								$(container).show();
								$("#AjaxSvgLoader").hide();
							}
						});
					}
				});
			}
		});

		$("#UploadFormSendButton").click(function() {
			drop.processQueue();
			return false;
		});
	});
</script>