<script>
$(document).ready(function(){

		var modalheight = Math.ceil(($(window).height() * 90) / 100);
		var modalwidth = Math.ceil(($(window).width() * 90) / 100);

		var dialogOpts = {
			modal: false,
			width: modalwidth,
			height: modalheight,
			autoOpen: false,
			draggable: true,
			resizeable: true
			};

		$("#dialog").dialog(dialogOpts);

		<?php echo $this->Navigation->ContextMenue('hasmenu',$SubMenueArray);?>
});
</script>
