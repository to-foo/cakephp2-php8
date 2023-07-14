<script>
$(document).ready(function(){
	$("span.for_hasmenu1").contextmenu({
		delegate: ".hasmenu1",
		autoFocus: true,
		preventContextMenuForPopup: true,
		preventSelect: true,
		taphold: true,
		menu: [
			{
			title: "<?php echo __('Edit image discription');?>",
			cmd: "edit",
			action :	function(event, ui) {

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

						$("#dialog").load("reportnumbers/imagediscription/" + ui.target.attr("rev"), {
								"ajax_true": 1
							});
						$("#dialog").dialog("open");
						},
			uiIcon: "qm_edit",
			disabled: false
			},
			{
				title: "----"
			},
			{
			title: "<?php echo __('Show image');?>",
			cmd: "edit",
			action :	function(event, ui) {

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

						$("#dialog").load("reportnumbers/image/" + ui.target.attr("rev"), {
								"ajax_true": 1
							});
						$("#dialog").dialog("open");
						},
			uiIcon: "qm_show",
			disabled: false
			},
			{
				title: "----"
			},
			{
			title: "<?php echo __('Delete image');?>",
			cmd: "delete",
			action :	function(event, ui) {
							checkDuplicate = confirm("<?php echo __('Soll dieses Bild gelöscht werden?');?>");
							if (checkDuplicate == false) {
								return false;
							}

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

						$("#dialog").load("reportnumbers/imagediscription/" + ui.target.attr("rev"), {
								"ajax_true": 1,
								"delete_image": 1,
								"data[Reportimage][id]": ui.target.attr("rel")
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
