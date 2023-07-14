<?php 
if(isset($stop_login)) {
	echo '<div class="message_wrapper"><div class="message">';
	echo $stop_login;
	echo '</div></div>'; 
	return;
}
?>

<div class="users form login" >
<?php //var_dump(Security::hash('test', null, true)); ?>
<?php echo $this->Form->create('Extern');?>
	<fieldset>
 		<legend><?php __('Please log in to your account'); ?></legend>
		<?php echo $this->Session->flash(); ?>
	<?php
		echo $this->Form->input('username');
		echo $this->Form->input('password');
		echo $this->Form->input('Language.beschreibung', array('label' => __('Language', true), 'selected' => $selected, 'options' => $lang));
	?>

    <div id="load_form"><div class="load_form"><?php echo __('Sie werden sicher eingeloggt.',true);?></div></div>

	</fieldset>
    
    
<?php echo $this->Form->end(__('Login', true)); ?>
<?php  ?>
</div>

<script type="text/javascript">
	$(document).ready(function(){
		
		$('#UserUsername').focus();

		$("form#ExternIncommingForm").bind("submit", function() {

				var data = $("form#ExternIncommingForm").serializeArray();

				if($("#UserUsername").val() == "") {
					$("#UserUsername").css("background-color","#ecb5a2");
					return false;
					}
				if($("#UserPassword").val() == "") {
					$("#UserPassword").css("background-color","#ecb5a2");
					return false;
					}

				$("#load_form").show();

			});
		});
</script>
