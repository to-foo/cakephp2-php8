<div class="modalarea">
<div class="error"><p>
<?php echo $hint;?>
</p></div>
<?php echo $this->Form->create('WelderCertificateData', array('class' => 'dialogsubform')); ?>
<?php echo $this->Form->input('id'); ?>
<?php echo $this->Form->button(__('Back'),array('type' => 'button','class' => 'back_button')); ?>
<?php echo $this->Form->end(__('Delete', true));?>
</div>

<script type="text/javascript">
$(document).ready(function(){

	$("div.checkbox input").button();
	$("div.radio:not(.ui-buttonset)").buttonset();

	$("button.back_button").click(function() {
		var url = $(this).closest('form').attr("action");
		$("#certification").load(url, {
			"ajax_true": 1,
			"back": 1,
		})
		return false;
	});	
	
	$("form.dialogsubform").bind("submit", function() {

		var data = $(this).serializeArray();
		data.push({name: "ajax_true", value: 1});
		data.push({name: "back", value: 1});

		$.ajax({
			type	: "POST",
			cache	: false,
			url		: $(this).attr("action"),
			data	: data,
			success: function(data) {
		    	$("#certification").html(data);
		    	$("#certification").show();
			}
		});
		return false;
	});	
});
</script>