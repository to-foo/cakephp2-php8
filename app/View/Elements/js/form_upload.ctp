<!-- Veraltet wird gelöscht -->
<script type="text/javascript">
$(function(){
	$('.fileUploadForm').fileUploader({
		'limit': 1,
		'selectFileLabel': '<?php echo $FileLabel; ?>',
		'allowedExtension': '<?php echo $Extension; ?>',
		'onFileChange': function(e){$("#dialog").scrollTop(3000);},
		'afterUpload': function(e){
							var data = $(".fakeform").serializeArray();
							data.push({name: "ajax_true", value: 1});
							data.push({name: "edit_description", value: 1});
							$.ajax({
								type	: "POST",
								cache	: true,
								url		: '<?php echo $url; ?>',
								data	: data,
								success: function(data) {
		    					$("#dialog").html(data);
		    					$("#dialog").show();
								}
							});
							return false;
						}
			}
		);
	});
</script>
