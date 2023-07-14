
<script type="text/javascript">
$(document).ready(function() {

	$("span.for_hasmenu1").contextmenu({
		delegate: ".hasmenu1",
		autoFocus: true,
		preventContextMenuForPopup: true,
		preventSelect: true,
		taphold: true,
		menu: [
			{
			title: "<?php echo __('Delete file');?>",
			cmd: "delete",
			action :	function(event, ui) {
							checkDuplicate = confirm("<?php echo __('Soll diese Datei gelöscht werden?');?>");
							if (checkDuplicate == false) {
								return false;
							}

						$("#dialog").load("reportnumbers/delfile/" + ui.target.attr("rev"), {
								"ajax_true": 1
							});
						$("#dialog").dialog("open");
						},
			uiIcon: "qm_delete",
			disabled: false
			}
			],

		select: function(event, ui) {},
	});

});
</script>
