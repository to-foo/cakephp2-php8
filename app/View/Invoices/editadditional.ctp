<div class="modalarea"> 
<h2><?php echo __('Statisticsadditional', true);?>
</h2> 
	<div class="form inhalt">
		<?php echo $this->Form->create('Statisticsadditional'); ?>
		<fieldset>
		<?php 
 			echo $this->Form->input('id');
			echo $this->Form->input('laufende_nr', array('type' => 'hidden'));
			echo $this->Form->input('kurztext', array(
													'options' => $kurztext, 
													'empty' => '', 
													'selected' => @$this->request->data['Statisticsadditional']['position']
													)
												);
			echo $this->Form->input('number');
			echo $this->Form->input('examinierer_1', array('options' => $examinierers, 'empty' => __('choose one', true), 'selected' => @$this->request->data['Statisticsadditional']['examinierer_1']));
			echo $this->Form->input('examinierer_2', array('options' => $examinierers, 'empty' => __('choose one', true), 'selected' => @$this->request->data['Statisticsadditional']['examinierer_2']));
			echo $this->Form->input('date');
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

		$(".modalarea a").click(function() {
			$("#dialog").load($(this).attr("href")+"/1", {"ajax_true": 1, "dialog": 1})
			return false;
		});

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

<?php echo $this->JqueryScripte->ModalFunctions(); ?>
