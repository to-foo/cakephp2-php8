<div id="refresh_container">
<script type="text/javascript">
var refreshTimer;

$(document).ready(function() {
	$("#refresh_revision_container").load("<?php echo Router::url(array_merge(array('action' => 'refreshrevision'), $this->request->projectvars['VarsArray'])); ?>", {
		"ajax_true": 1
	})
});
</script>
</div>
<script type="text/javascript">
var refreshTimer;
clearInterval(refreshTimer);

$(document).ready(function() {

      function refreshFunction(){
		$("#refresh_revision_container").load("<?php echo Router::url(array_merge(array('action' => 'refreshrevision'), $this->request->projectvars['VarsArray'])); ?>", {
			"ajax_true": 1
		})
      }
      refreshTimer = setInterval(refreshFunction, <?php echo Configure::read('RefreshReportTime');?>);
});
</script>