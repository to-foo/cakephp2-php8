<script type="text/javascript">
$(function(){
				
		$("span.for_hasmenu1").contextmenu({
			delegate: ".hasmenu1",
			autoFocus: true,
			preventContextMenuForPopup: true,
			preventSelect: true,
			taphold: true,
			menu: [
				{
				title: "<?php echo __('Edit file description');?>", 
				cmd: "status", 
				action :	function(event, ui) {
							$("#dialog").load("examiners/examinerfilesdescription/" + ui.target.attr("rev"), {
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
				title: "<?php echo __('Delete file');?>", 
				cmd: "status", 
				action :	function(event, ui) {
							$("#dialog").load("examiners/delexaminerfiles/" + ui.target.attr("rev"), {
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
