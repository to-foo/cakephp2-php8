<div class="quicksearch">
<?php //echo $this->Navigation->quickSearching('quicksearch',1,__('Pr-Nr. (YYYY-NN)', true)); ?>
<?php echo $this->element('subdevisions_form');?>
<?php echo $this->element('search_case');?>
</div>
<div class="pagin_links">
</div>
<div class="clear"></div>
<div class="reportnumbers index inhalt">

<h2><?php echo __('Statistic result'); ?></h2>
<div id="statistic_container" class="statistic_container">
<div class=" statisticsearch" id="statistic_1"></div>
<div class="imageoutput" id="diagramm_1"></div>
<!--
<div class="statistic_container" id="diagramm_2"></div>
-->
</div>
</div>
<div class="clear" id="testdiv"></div>
<script type="text/javascript">

$(document).ready(function(){

	function CssContainerShow(id) {
		$("#" + id).css("min-height","inherit");
		$("#" + id).css("background","inherit");
	}

	function CssContainerWait(id) {
		$("#" + id).css("min-height","40px");
		$("#" + id).css("background-image","url(img/indicator.gif)");
		$("#" + id).css("background-repeat","no-repeat");
		$("#" + id).css("background-position","center center");
		$("#" + id).css("background-size","auto 15px");
	}

	CssContainerWait("diagramm_1");
	CssContainerWait("diagramm_2");
	CssContainerWait("statistic_1");

	var data = new Array();
	data.push({name: "ajax_true", value: 1});
	data.push({name: "search_typ", value: 4});
	data.push({name: "statistic_typ", value: 1});

	$.ajax({
		type	: "POST",
		data	: data,
		cache	: false,
		url		: "<?php echo Router::url(array_merge(array('action'=>'results'), $this->request->projectvars['VarsArray']));?>",
		dataType: "html",
		success: function(data) {
			$("#statistic_1").html(data);
			$("#statistic_1").show();
			CssContainerShow("statistic_1");
		},
	});

	var data = new Array();
	data.push({name: "ajax_true", value: 1});
	data.push({name: "search_typ", value: 4});
	data.push({name: "statistic_typ", value: 2});

	$.ajax({
		type	: "POST",
		data	: data,
		cache	: false,
		url		: "<?php echo Router::url(array_merge(array('action'=>'results'), $this->request->projectvars['VarsArray']));?>",
		dataType: "html",
		success: function(data) {
			$("#diagramm_1").html(data);
			$("#diagramm_1").show();
			CssContainerShow("diagramm_1");
			$("html, body").animate({ scrollTop: $(document).height() }, "slow");
			$("button#WeldingOverview").prop("disabled", true);
			$("button#WeldingOverview").addClass("inactive");
		},
	});
});
</script>

<?php echo $this->JqueryScripte->LeftMenueHeight(); ?>
