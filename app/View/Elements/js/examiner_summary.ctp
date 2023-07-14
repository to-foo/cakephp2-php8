<script type="text/javascript">

$(document).ready(function(){

			$("div.container_summary_single").hide();
			$("div.container_eyechecksummary_single").hide();
			$("div.container_monsummary_single").hide();

			$("a.summary_tooltip").tooltip({
				content: function () {
					var output = $(".container_summary_single_" + $(this).prop('rev') + "_" +$(this).prop('rel')).html();
					if(!output){
						output = $(".container_summary_single_" + $(this).prop('rel')).html();
					}
					return output;
				}
			});

			$("a.summaryeyecheck_tooltip").tooltip({
				content: function () {
					var output = $(".container_summaryeyecheck_single_" + $(this).prop('rel')).html();
					return output;
				}
			});

      $("a.summarymon_tooltip").tooltip({
				content: function () {
					var output = $(".container_summarymon_single_" + $(this).prop('rel')).html();
					return output;
				}
			});

			$("span.for_hasmenu1").contextmenu({
				delegate: ".hasmenu1",
				autoFocus: true,
				preventContextMenuForPopup: true,
				preventSelect: true,
				taphold: true,
				menu: [
					{
					title: "<?php echo __('Edit');?>",
					cmd: "status",
					action :	function(event, ui) {
							$("#dialog").load("examiners/edit/" + ui.target.attr("rev"), {
									"ajax_true": 1
								});
							$("#dialog").dialog("open");
							},
					uiIcon: "qm_edit",
					disabled: false
					},
					{
					title: "<?php echo __('Qualifications');?>",
					cmd: "status",
					action :	function(event, ui) {
							$("#container").load("examiners/certificates/" + ui.target.attr("rev"), {
									"ajax_true": 1
								});
//							$("#dialog").dialog("open");
							},
					uiIcon: "qm_certificate",
					disabled: false
					},
					{
					title: "<?php echo __('Vision tests');?>",
					cmd: "status",
					action :	function(event, ui) {
							$("#dialog").load("examiners/eyechecks/" + ui.target.attr("rev"), {
									"ajax_true": 1
								});
							$("#dialog").dialog("open");
							},
					uiIcon: "qm_eyecheck",
					disabled: false
					},
					{
						title: "----"
					},
					{
					title: "<?php echo __('Delete');?>",
					cmd: "status",
					action :	function(event, ui) {
							$("#dialog").load("examiners/delete/" + ui.target.attr("rev"), {
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

	    $(".table_resizable th").resizable();
	    $(".table_resizable tr").resizable();
	    $(".table_resizable td").resizable();

});

</script>
