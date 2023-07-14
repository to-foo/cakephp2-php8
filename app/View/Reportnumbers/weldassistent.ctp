<div class="modalarea">
<h2><?php echo __('Weld assistent', true); ?></h2> 
<?php echo $this->Form->create('weldassistent'); ?>
<fieldset class="extraarea weldassistent">
<?php echo $this->ViewData->ShowWeldAssistentData($settings);?>
</fieldset>
<?php echo $this->Form->end(__('Vorschau', true));?>
<div class="clear" id="testdiv"></div>
</div>
<script type="text/javascript">
	$(document).ready(function(){		

		$("#closethismodal").click(function() {
			$("#dialog").dialog("close");
			return false;	
		});

		$("input[type=checkbox]").button();

		$("#weldassistentWeldassistentForm").bind("submit", function() {
							
			var data = $(this).serializeArray();
			data.push({name: "ajax_true", value: 1});
			data.push({name: "dialog", value: 1});

			$.ajax({
				type	: "POST",
				cache	: true,
				url		: $(this).attr("action"),
				data	: data,
				success: function(data) {
		    		$("#dialog").html(data);
		    		$("#dialog").show();
				}
			});
			return false;
		});
	});
</script>
