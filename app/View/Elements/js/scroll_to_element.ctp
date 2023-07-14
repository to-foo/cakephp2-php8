<?php
echo $this->Form->input('ElementClass',array('type' => 'hidden','value' => $element));
echo $this->Form->input('ElementTime',array('type' => 'hidden','value' => $time));
?>

<script type="text/javascript">
$(document).ready(function(){

	$("html, body").animate({
		scrollTop: $($("#ElementClass").val()).offset().top
	}, $("#ElementTime").val());

});
</script>
