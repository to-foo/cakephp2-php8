<div class="qualifications form inhalt">
<?php echo $this->Form->create('Qualification');?>
	<fieldset>
		<legend><?php echo __('Delete qualification'); ?></legend>
		<div class="error">
		<?php 
		echo '<p>';
		echo __('Really delete this qualification?', true);
		echo '</p>';
		echo '<p>';
		echo $this->request->data['Testingmethod']['verfahren'];
		echo ' - ';
		echo $this->request->data['Qualification']['certification-number'];
		echo '</p>';
		echo $this->Form->input('id');
		?>
		</div>
	</fieldset>
<?php 
//echo $this->Form->button(__('Cancel', true), array('id' => 'back', 'type' => 'button', 'value' => $previous_url));
echo $this->Form->end(__('Delete', true));
?>
</div>
<div class="clear" id="testdiv"></div>

<script type="text/javascript">
	$(document).ready(function(){		


		<?php 
		if(isset($close) && $close == 1){
			echo '
									
			var data = $("#fakeform").serializeArray();
			data.push({name: "ajax_true", value: 1});

			$.ajax({
				type	: "POST",
				cache	: true,
				url		: "' . Router::url(array('controller' => 'dropdowns', 'action' => 'dropdownedit', $dropdownID, $examiniererID)) . '",
				data	: data,
				success: function(data) {
					$("#container").html(data);
					$("#container").show();
					}
				});
			';
			echo '$("#dialog").dialog("close");';
		}
		?>

		$("div.checkbox input").button();

		// allgemeines Formular
		$("form").bind("submit", function() {
							
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
