<?php
echo $this->Form->input('AccordionStep',array('type' => 'hidden','value' => $step));
?>
<script>
$(function() {
    $(".expediting_form").accordion({
		  heightStyle: "content",
      active: $('#AccordionStep').val() - 1
	});
});
</script>
