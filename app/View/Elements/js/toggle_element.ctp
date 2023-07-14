
<?php
echo $this->Form->input('ToggleElementButton',array('type' => 'hidden','value' => $Button));
echo $this->Form->input('ToggleElementElement',array('type' => 'hidden','value' => $Element));
?>

<script type="text/javascript">
	$(document).ready(function(){

		button = $("#ToggleElementButton").val();
		element = $("#ToggleElementElement").val();

		$(button).click(function(){
		      $(element).toggle("slow");
					$(button).toggleClass('icon_hide_infos icon_show_infos');
		  });
	});
</script>
