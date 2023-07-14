<?php

echo $this->Form->create('ExaminersStamp', array('class' => 'dropzone'));
echo $this->Form->input('ajax_true', array('name' => 'ajax_true','type' => 'hidden', 'value' => 1));
echo $this->Form->input('ajax_true', array('name' => 'examiner_id','type' => 'hidden', 'value' => $examiner_id));

echo '<div class="submit clear">';
if($show_submit) {
  echo '<button type="submit" id="UploadFormSendButton" class="btn btn-primary">' . __('Submit',true) . '</button>';
}
echo '</div>';
echo $this->Form->end();
?>

<?php
/*echo $this->Form->input('ThisUploadContainer',array('type' => 'hidden','value' => $container));
echo $this->Form->input('ThisFildLabel',array('type' => 'hidden','value' => $FileLabel));*/
?>

<script type="text/javascript">
$(() => {
  $(".dropzone").css("width", 200);
  $(".dropzone").css("height", 190);

  Dropzone.autoDiscover = false;
  let uploadUrl = this.location.origin;
  uploadUrl = uploadUrl + '/examiners/uploadstamp';
  let editurl = this.location.origin + '/examiners/stamps/' + '<?php echo $projectsvars; ?>';
  let examiner_id = '<?php echo $examiner_id;?>';

	const drop = new Dropzone(".dropzone", {
    paramName: "file",
    acceptedFiles: 'image/svg+xml',
		autoProcessQueue: false,
		parallelUploads: 100,
		maxFiles: 1,
		addRemoveLinks: true,
		url: uploadUrl,
		dictDefaultMessage: "<?php echo __("Add stamp"); ?>",
		init: function (e) {

			var totalFiles = 0,
			completeFiles = 0;

      this.on("maxfilesexceeded", function(file) {
        this.removeAllFiles();
        this.addFile(file);
      });
			this.on("addedfile", function (file) {
					totalFiles += 1;
			});
			this.on("removed file", function (file) {
					totalFiles -= 1;
			});
			this.on("complete", function (file) {
          completeFiles += 1;
					if (completeFiles === totalFiles) {

						$("#AjaxSvgLoader").show();

						var container = $("#ThisUploadContainer").val();

						var data = new Array();
            var data = $("#ExaminersStampStampsForm").serializeArray();

						data.push({name: "ajax_true", value: 1});
            data.push({name: "save_examiner_file", value: 1});
            data.push({name: "examiner_id", value: examiner_id});

						//data.push({name: "close_reload", value: 1});

						$.ajax({
							type	: "POST",
							cache	: false,
							url		: uploadUrl,
							data	: data,
							success: function(data) {
                var data = new Array();

                data.push({name: "ajax_true", value: 1});
              	data.push({name: "dialog", value: 1});

                $.ajax({
                  type	: "POST",
                  cache	: false,
                  url		: editurl,
                  data	: data,
                  success: function(data) {
                    let new_stamp_container = $(data).find('#stamp_container');
                    $('#stamp_container').html(new_stamp_container);
                    $("#AjaxSvgLoader").hide();
                  }
                });
							}
						});
					}
			});
		}
	});

	$("#ExaminersStampStampsForm").click(function(){
   	drop.processQueue();
		return false;
	});
});
</script>
