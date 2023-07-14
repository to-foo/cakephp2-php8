<div class="modalarea">
<h2>
<?php echo __('Source', true) . ' ' . $this->request->data['Statisticsstandard']['source']; ?> 
<?php echo __('Dimension', true) . ' ' . $this->request->data['Statisticsstandard']['dimension']; ?> 
<?php echo __('Picture-No.', true) . ' ' . $this->request->data['Statisticsstandard']['picture_no']; ?> 
</h2> 
	<div class="form inhalt">
		<?php echo $this->Form->create('Statisticsstandard'); ?>
		<fieldset>
		<?php
			echo $this->Form->input('id');
			echo $this->Form->input('position', array('options' => $invoicespositions, 'empty' => '', 'selected' => $selected));
			echo $this->Form->input('deaktiv',array('label' => 'Deaktivieren'));
	?>
		</fieldset>
		<div class="clear">
		<?php echo $this->Form->end(__('Submit')); ?>
		</div>
	</div>
	<div class="clear" id="message_wrapper"><?php echo $this->Session->flash(); ?></div>	
</div>
<script type="text/javascript">
	$(document).ready(function(){		

		$("div.checkbox input").button();
/*		
		$(".modalarea a").click(function() {
			$("#dialog").load($(this).attr("href")+"/1", {"ajax_true": 1, "dialog": 1})
			return false;
		});
*/
		// allgemeines Formular
		$("#StatisticsstandardEditstandardForm").bind("submit", function() {
							
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

<?php echo $this->JqueryScripte->ModalFunctions(); ?>
